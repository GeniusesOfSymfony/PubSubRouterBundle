<?php

namespace Gos\Bundle\PubSubRouterBundle\Matcher\Dumper;

use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;

class CompiledMatcherDumper extends MatcherDumper
{
    /**
     * @var \Exception|null
     */
    private $signalingException = null;

    public function dump(array $options = []): string
    {
        return <<<EOF
<?php
/**
 * This file has been auto-generated
 * by the PubSubRouterBundle.
 */
return [
{$this->generateCompiledRoutes()}];
EOF;
    }

    public function getCompiledRoutes(bool $forDump = false): array
    {
        // Group hosts by same-suffix, re-order when possible
        $routes = new StaticPrefixCollection();

        foreach ($this->getRoutes()->all() as $name => $route) {
            $routes->addRoute('/(.*)', [$name, $route]);
        }

        $routes = $this->getRoutes();

        [$staticRoutes, $dynamicRoutes] = $this->groupStaticRoutes($routes);

        $compiledRoutes = [$this->compileStaticRoutes($staticRoutes)];
        $chunkLimit = \count($dynamicRoutes);

        while (true) {
            try {
                $this->signalingException = new \RuntimeException('Compilation failed: regular expression is too large');
                $compiledRoutes = array_merge($compiledRoutes, $this->compileDynamicRoutes($dynamicRoutes, $chunkLimit));

                break;
            } catch (\Exception $e) {
                if (1 < $chunkLimit && $this->signalingException === $e) {
                    $chunkLimit = 1 + ($chunkLimit >> 1);

                    continue;
                }

                throw $e;
            }
        }

        if ($forDump) {
            $compiledRoutes[1] = $compiledRoutes[3];
        }

        return $compiledRoutes;
    }

    private function generateCompiledRoutes(): string
    {
        [$staticRoutes, $regexpCode, $dynamicRoutes] = $this->getCompiledRoutes(true);

        $code = '[ // $staticRoutes'."\n";

        foreach ($staticRoutes as $path => $routes) {
            $code .= sprintf("    %s => [\n", self::export($path));

            foreach ($routes as $route) {
                $code .= sprintf("        [%s, %s, %s, %s, %s, %s, %s],\n", ...array_map([self::class, 'export'], $route));
            }

            $code .= "    ],\n";
        }

        $code .= "],\n";

        $code .= sprintf("[ // \$regexpList%s\n],\n", $regexpCode);

        $code .= '[ // $dynamicRoutes'."\n";

        foreach ($dynamicRoutes as $path => $routes) {
            $code .= sprintf("    %s => [\n", self::export($path));

            foreach ($routes as $route) {
                $code .= sprintf("        [%s, %s, %s, %s, %s, %s, %s],\n", ...array_map([self::class, 'export'], $route));
            }

            $code .= "    ],\n";
        }

        $code .= "],\n";
        $code = preg_replace('/ => \[\n        (\[.+?),\n    \],/', ' => [$1],', $code);

        return $this->indent($code, 1);
    }

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
    private function compileStaticRoutes(array $staticRoutes): array
    {
        if (!$staticRoutes) {
            return [];
        }

        $compiledRoutes = [];

        foreach ($staticRoutes as $url => $routes) {
            $compiledRoutes[$url] = [];

            foreach ($routes as $name => $route) {
                $compiledRoutes[$url][] = $this->compileRoute($route, $name, []);
            }
        }

        return $compiledRoutes;
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
    private function compileDynamicRoutes(RouteCollection $collection, int $chunkLimit): array
    {
        if (!$collection->all()) {
            return [[], [], ''];
        }

        $regexpList = [];
        $code = '';
        $state = (object) [
            'regexMark' => 0,
            'regex' => [],
            'routes' => [],
            'mark' => 0,
            'markTail' => 0,
            'vars' => [],
        ];
        $state->getVars = static function ($m) use ($state) {
            if ('_route' === $m[1]) {
                return '?:';
            }

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
            $code .= "\n    {$state->mark} => ".self::export($rx);
            $startingMark = $state->mark;
            $state->mark += \strlen($rx);
            $state->regex = $rx;

            $tree = new StaticPrefixCollection();

            foreach ($routes->all() as $name => $route) {
                preg_match('#^.\^(.*)\$.[a-zA-Z]*$#', $route->compile()->getRegex(), $rx);

                $state->vars = [];
                $regex = preg_replace_callback('#\?P<([^>]++)>#', $state->getVars, $rx[1]);

                if ('/' !== $regex && '/' === $regex[-1]) {
                    $regex = substr($regex, 0, -1);
                }

                $tree->addRoute($regex, [$name, $regex, $state->vars, $route]);
            }

            $code .= $this->compileStaticPrefixCollection($tree, $state, 0);

            $rx = ")/?$}{$modifiers}";
            $code .= "\n        .'{$rx}',";
            $state->regex .= $rx;
            $state->markTail = 0;

            // if the regex is too large, throw a signaling exception to recompute with smaller chunk size
            set_error_handler(function ($type, $message): void { throw false !== strpos($message, $this->signalingException->getMessage()) ? $this->signalingException : new \ErrorException($message); });

            try {
                preg_match($state->regex, '');
            } finally {
                restore_error_handler();
            }

            $regexpList[$startingMark] = $state->regex;
        }

        $state->routes[$state->mark][] = [null, null, null, null, false, false, 0];
        unset($state->getVars);

        return [$regexpList, $state->routes, $code];
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
                $code .= "\n            .".self::export($rx);
                $state->regex .= $rx;
                $code .= $this->indent($this->compileStaticPrefixCollection($route, $state, $prefixLen + \strlen($prefix)));
                $code .= "\n            .')'";
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
                $state->routes[$state->mark][] = $this->compileRoute($route, $name, $vars);

                continue;
            }

            $state->mark += 3 + $state->markTail + \strlen($regex) - $prefixLen;
            $state->markTail = 2 + \strlen((string) $state->mark);
            $rx = sprintf('|%s(*:%s)', substr($regex, $prefixLen), $state->mark);
            $code .= "\n            .".self::export($rx);
            $state->regex .= $rx;

            $prevRegex = $compiledRoute->getRegex();
            $state->routes[$state->mark] = [$this->compileRoute($route, $name, $vars)];
        }

        return $code;
    }

    /**
     * Compiles a single Route to PHP code used to match it against the path info.
     */
    private function compileRoute(Route $route, string $name, array $vars): array
    {
        return [
            $name,
            $vars,
            $route->getPattern(),
            $route->getCallback(),
            $route->getDefaults(),
            $route->getRequirements(),
            $route->getOptions(),
        ];
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
            return '[]';
        }

        $i = 0;
        $export = '[';

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

        return substr_replace($export, ']', -2);
    }
}
