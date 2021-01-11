<?php

namespace Gos\Bundle\PubSubRouterBundle\Matcher\Dumper;

use Gos\Bundle\PubSubRouterBundle\Matcher\Matcher;
use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;

trigger_deprecation('gos/pubsub-router-bundle', '2.4', 'The "%s" class is deprecated and will be removed in 3.0, use the "%s" class instead.', PhpMatcherDumper::class, CompiledMatcherDumper::class);

/**
 * @deprecated to be removed in 3.0, use the `Gos\Bundle\PubSubRouterBundle\Matcher\Dumper\CompiledMatcherDumper` class instead
 */
class PhpMatcherDumper extends MatcherDumper
{
    /**
     * @var \RuntimeException
     */
    private $signalingException;

    /**
     * Dumps a set of routes to a PHP class.
     *
     * Available options:
     *
     *  * class:      The class name
     *  * base_class: The base class name
     */
    public function dump(array $options = []): string
    {
        $options = array_replace(
            [
                'class' => 'ProjectMatcher',
                'base_class' => Matcher::class,
            ],
            $options
        );

        // trailing slash support is only enabled if we know how to redirect the user
        $interfaces = class_implements($options['base_class']);

        return <<<EOF
<?php

use Gos\Bundle\PubSubRouterBundle\Exception\ResourceNotFoundException;
use Gos\Bundle\PubSubRouterBundle\Router\Route;

/**
 * This class has been auto-generated
 * by the PubSubRouterBundle.
 */
class {$options['class']} extends {$options['base_class']}
{
    public function __construct()
    {
    }

{$this->generateMatchMethod()}
}

EOF;
    }

    /**
     * Generates the code for the match method implementing MatcherInterface.
     */
    private function generateMatchMethod(): string
    {
        // Group hosts by same-suffix, re-order when possible
        $routes = new StaticPrefixCollection();
        foreach ($this->getRoutes()->all() as $name => $route) {
            $routes->addRoute('/(.*)', [$name, $route]);
        }

        $routes = $this->getRoutes();

        $code = rtrim($this->compileRoutes($routes), "\n");

        $code = <<<EOF
    {
$code

EOF;

        return "    public function match(string \$channel): array\n".$code."\n        throw new ResourceNotFoundException();\n    }";
    }

    /**
     * Generates PHP code to match a RouteCollection with all its routes.
     */
    private function compileRoutes(RouteCollection $routes): string
    {
        [$staticRoutes, $dynamicRoutes] = $this->groupStaticRoutes($routes);

        $code = $this->compileStaticRoutes($staticRoutes);
        $chunkLimit = \count($dynamicRoutes);

        while (true) {
            try {
                $this->signalingException = new \RuntimeException('preg_match(): Compilation failed: regular expression is too large');
                $code .= $this->compileDynamicRoutes($dynamicRoutes, $chunkLimit);
                break;
            } catch (\Exception $e) {
                if (1 < $chunkLimit && $this->signalingException === $e) {
                    $chunkLimit = 1 + ($chunkLimit >> 1);
                    continue;
                }
                throw $e;
            }
        }

        return $code;
    }

    /**
     * Splits static routes from dynamic routes, so that they can be matched first, using a simple switch.
     */
    private function groupStaticRoutes(RouteCollection $collection): array
    {
        $staticRoutes = $dynamicRegex = [];
        $dynamicRoutes = new RouteCollection();

        foreach ($collection->all() as $name => $route) {
            $compiledRoute = $route->compile();
            $regex = $compiledRoute->getRegex();

            if (!$compiledRoute->getVariables()) {
                $pattern = $route->getPattern();

                foreach ($dynamicRegex as $rx) {
                    if (preg_match($rx, $pattern)) {
                        $dynamicRegex[] = $regex;
                        $dynamicRoutes->add($name, $route);
                        continue 2;
                    }
                }

                $staticRoutes[$pattern][$name] = $route;
            } else {
                $dynamicRegex[] = $regex;
                $dynamicRoutes->add($name, $route);
            }
        }

        return [$staticRoutes, $dynamicRoutes];
    }

    /**
     * Compiles static routes in a switch statement.
     *
     * Condition-less paths are put in a static array in the switch's default, with generic matching logic.
     * Paths that can match two or more routes, or have user-specified conditions are put in separate switch's cases.
     *
     * @throws \LogicException
     */
    private function compileStaticRoutes(array $staticRoutes): string
    {
        if (!$staticRoutes) {
            return '';
        }

        $code = '';

        foreach ($staticRoutes as $url => $routes) {
            if (1 === \count($routes)) {
                foreach ($routes as $name => $route) {
                }
            }

            $code .= sprintf("        case %s:\n", self::export($url));

            foreach ($routes as $name => $route) {
                $code .= $this->compileRoute($route, $name);
            }

            $code .= "            break;\n";
        }

        return sprintf("        switch (\$channel) {\n%s        }\n\n", $this->indent($code));
    }

    /**
     * Compiles a regular expression followed by a switch statement to match dynamic routes.
     *
     * The regular expression matches both the host and the pathinfo at the same time. For stellar performance,
     * it is built as a tree of patterns, with re-ordering logic to group same-prefix routes together when possible.
     *
     * Patterns are named so that we know which one matched (https://pcre.org/current/doc/html/pcre2syntax.html#SEC23).
     * This name is used to "switch" to the additional logic required to match the final route.
     *
     * Condition-less paths are put in a static array in the switch's default, with generic matching logic.
     * Paths that can match two or more routes, or have user-specified conditions are put in separate switch's cases.
     *
     * Last but not least:
     *  - Because it is not possible to mix unicode/non-unicode patterns in a single regexp, several of them can be generated.
     *  - The same regexp can be used several times when the logic in the switch rejects the match. When this happens, the
     *    matching-but-failing subpattern is excluded by replacing its name by "(*F)", which forces a failure-to-match.
     *    To ease this backlisting operation, the name of subpatterns is also the string offset where the replacement should occur.
     */
    private function compileDynamicRoutes(RouteCollection $collection, int $chunkLimit): string
    {
        if (!$collection->all()) {
            return '';
        }

        $code = '';
        $state = (object) [
            'regex' => '',
            'switch' => '',
            'default' => '',
            'mark' => 0,
            'markTail' => 0,
            'vars' => [],
        ];
        $state->getVars = static function ($m) use ($state) {
            $state->vars[] = $m[1];

            return '';
        };

        $chunkSize = 0;
        $prev = null;
        $perModifiers = [];

        foreach ($collection->all() as $name => $route) {
            preg_match('#[a-zA-Z]*$#', $route->compile()->getRegex(), $rx);

            if ($chunkLimit < ++$chunkSize || $prev !== $rx[0] && $route->compile()->getVariables()) {
                $chunkSize = 1;
                $routes = new RouteCollection();
                $perModifiers[] = [$rx[0], $routes];
                $prev = $rx[0];
            }

            if (isset($routes)) {
                $routes->add($name, $route);
            }
        }

        foreach ($perModifiers as [$modifiers, $routes]) {
            $rx = '{^(?';
            $code .= self::export($rx);
            $state->mark += \strlen($rx);
            $state->regex = $rx;

            $tree = new StaticPrefixCollection();

            foreach ($routes->all() as $name => $route) {
                preg_match('#^.\^(.*)\$.[a-zA-Z]*$#', $route->compile()->getRegex(), $rx);

                $state->vars = [];
                $regex = preg_replace_callback('#\?P<([^>]++)>#', $state->getVars, $rx[1]);
                $tree->addRoute($regex, [$name, $regex, $state->vars, $route]);
            }

            $code .= $this->compileStaticPrefixCollection($tree, $state);

            $rx = ")$}{$modifiers}";
            $code .= "\n                .'{$rx}'";
            $state->regex .= $rx;
            $state->markTail = 0;

            // if the regex is too large, throw a signaling exception to recompute with smaller chunk size
            set_error_handler(function (int $type, string $message, string $file, int $line): void { throw 0 === strpos($message, $this->signalingException->getMessage()) ? $this->signalingException : new \ErrorException($message); });
            try {
                preg_match($state->regex, '');
            } finally {
                restore_error_handler();
            }
        }

        if ($state->default) {
            $state->switch .= <<<EOF
        default:
            \$routes = array(
{$this->indent($state->default, 4)}            );

            list(\$name, \$route, \$vars) = \$routes[\$m];
{$this->compileSwitchDefault(true)}
EOF;
        }

        $matchedPathinfo = '$channel';
        unset($state->getVars);

        return <<<EOF
        \$matchedChannel = {$matchedPathinfo};
        \$regex = {$code};

        while (preg_match(\$regex, \$matchedChannel, \$matches)) {
            switch (\$m = (int) \$matches['MARK']) {
{$this->indent($state->switch, 2)}            }

            if ({$state->mark} === \$m) {
                break;
            }
            \$regex = substr_replace(\$regex, 'F', \$m - \$offset, 1 + strlen(\$m));
            \$offset += strlen(\$m);
        }

EOF;
    }

    /**
     * Compiles a regexp tree of subpatterns that matches nested same-prefix routes.
     *
     * @param \stdClass $state A simple state object that keeps track of the progress of the compilation,
     *                         and gathers the generated switch's "case" and "default" statements
     */
    private function compileStaticPrefixCollection(StaticPrefixCollection $tree, \stdClass $state, int $prefixLen = 0): string
    {
        $code = '';
        $prevRegex = null;
        $routes = $tree->getRoutes();

        foreach ($routes as $i => $route) {
            if ($route instanceof StaticPrefixCollection) {
                $prevRegex = null;
                $prefix = substr($route->getPrefix(), $prefixLen);
                $state->mark += \strlen($rx = "|{$prefix}(?");
                $code .= "\n                .".self::export($rx);
                $state->regex .= $rx;
                $code .= $this->indent($this->compileStaticPrefixCollection($route, $state, $prefixLen + \strlen($prefix)));
                $code .= "\n                .')'";
                $state->regex .= ')';
                ++$state->markTail;
                continue;
            }

            /**
             * @var string $name
             * @var string $regex
             * @var array  $vars
             * @var Route  $route
             */
            [$name, $regex, $vars, $route] = $route;
            $compiledRoute = $route->compile();

            if ($compiledRoute->getRegex() === $prevRegex) {
                $state->switch = substr_replace($state->switch, $this->compileRoute($route, $name)."\n", -19, 0);
                continue;
            }

            $state->mark += 3 + $state->markTail + \strlen($regex) - $prefixLen;
            $state->markTail = 2 + \strlen((string) $state->mark);
            $rx = sprintf('|%s(*:%s)', substr($regex, $prefixLen), $state->mark);
            $code .= "\n                    .".self::export($rx);
            $state->regex .= $rx;

            if (!\is_array($next = $routes[1 + $i] ?? null) || $regex !== $next[1]) {
                $prevRegex = null;
                $defaults = $route->getDefaults();

                $state->default .= sprintf(
                    "%s => array(%s, %s, %s),\n",
                    $state->mark,
                    self::export($name),
                    sprintf(
                        'new Route(%s, %s, %s, %s, %s)',
                        self::export($route->getPattern()),
                        self::export($route->getCallback()),
                        self::export($route->getDefaults() + $defaults),
                        self::export($route->getRequirements()),
                        self::export($route->getOptions())
                    ),
                    self::export($vars)
                );
            } else {
                $prevRegex = $compiledRoute->getRegex();
                $combine = '            $matches = array(';
                foreach ($vars as $j => $m) {
                    $combine .= sprintf('%s => $matches[%d] ?? null, ', self::export($m), 1 + $j);
                }
                $combine = $vars ? substr_replace($combine, ");\n\n", -2) : '';

                $state->switch .= <<<EOF
        case {$state->mark}:
{$combine}{$this->compileRoute($route, $name)}
            break;

EOF;
            }
        }

        return $code;
    }

    /**
     * A simple helper to compiles the switch's "default" for both static and dynamic routes.
     */
    private function compileSwitchDefault(bool $hasVars): string
    {
        if ($hasVars) {
            $code = <<<EOF

            \$attributes = array();

            foreach (\$vars as \$i => \$v) {
                if (isset(\$matches[1 + \$i])) {
                    \$attributes[\$v] = \$matches[1 + \$i];
                }
            }

            \$attributes = \$this->mergeDefaults(\$attributes, \$route->getDefaults());

EOF;
        } else {
            $code = <<<EOF

            \$attributes = \$route->getDefaults();

EOF;
        }

        $code .= <<<EOF

            return array(\$name, \$route, \$attributes);

EOF;

        return $code;
    }

    /**
     * Compiles a single Route to PHP code used to match it against the path info.
     *
     * @throws \LogicException
     */
    private function compileRoute(Route $route, string $name): string
    {
        $compiledRoute = $route->compile();
        $matches = (bool) $compiledRoute->getVariables();

        $code = "            // {$name}\n";

        $gotoname = 'not_'.preg_replace('/[^A-Za-z0-9_]/', '', $name);

        // the offset where the return value is appended below, with indendation
        $retOffset = 12 + \strlen($code);

        // optimize parameters array
        if ($matches) {
            $vars = ["array('$name')", '$matches'];

            $code .= sprintf(
                "            \$ret = array(%s, %s, \$this->mergeDefaults(%s, %s));\n",
                self::export($name),
                sprintf(
                    'new Route(%s, %s, %s, %s, %s)',
                    self::export($route->getPattern()),
                    self::export($route->getCallback()),
                    self::export($route->getDefaults()),
                    self::export($route->getRequirements()),
                    self::export($route->getOptions())
                ),
                implode(' + ', $vars),
                self::export($route->getDefaults())
            );
        } else {
            $code .= sprintf(
                "            \$ret = array(%s, %s, %s);\n",
                self::export($name),
                sprintf(
                    'new Route(%s, %s, %s, %s, %s)',
                    self::export($route->getPattern()),
                    self::export($route->getCallback()),
                    self::export($route->getDefaults()),
                    self::export($route->getRequirements()),
                    self::export($route->getOptions())
                ),
                self::export($route->getDefaults())
            );
        }

        $code = substr_replace($code, 'return', $retOffset, 6);

        return $code;
    }

    private function indent(string $code, int $level = 1): string
    {
        return preg_replace('/^./m', str_repeat('    ', $level).'$0', $code);
    }

    /**
     * @param mixed $value
     *
     * @throws \InvalidArgumentException if the value contains invalid data
     *
     * @internal
     */
    public static function export($value): string
    {
        if (null === $value) {
            return 'null';
        }

        if (!\is_array($value)) {
            if (\is_object($value)) {
                throw new \InvalidArgumentException(Route::class.' cannot contain objects.');
            }

            return str_replace("\n", '\'."\n".\'', var_export($value, true));
        }

        if (!$value) {
            return 'array()';
        }

        $i = 0;
        $export = 'array(';

        foreach ($value as $k => $v) {
            if ($i === $k) {
                ++$i;
            } else {
                $export .= self::export($k).' => ';

                if (\is_int($k) && $i < $k) {
                    $i = 1 + $k;
                }
            }

            $export .= self::export($v).', ';
        }

        return substr_replace($export, ')', -2);
    }
}
