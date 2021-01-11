<?php

namespace Gos\Bundle\PubSubRouterBundle\Generator\Dumper;

use Gos\Bundle\PubSubRouterBundle\Generator\Generator;
use Gos\Bundle\PubSubRouterBundle\Matcher\Dumper\PhpMatcherDumper;

trigger_deprecation('gos/pubsub-router-bundle', '2.4', 'The "%s" class is deprecated and will be removed in 3.0, use the "%s" class instead.', PhpGeneratorDumper::class, CompiledGeneratorDumper::class);

/**
 * @deprecated to be removed in 3.0, use the `Gos\Bundle\PubSubRouterBundle\Generator\Dumper\CompiledGeneratorDumper` class instead
 */
class PhpGeneratorDumper extends GeneratorDumper
{
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
        $options = array_merge(
            [
                'class' => 'ProjectGenerator',
                'base_class' => Generator::class,
            ],
            $options
        );

        return <<<EOF
<?php

use Gos\Bundle\PubSubRouterBundle\Exception\ResourceNotFoundException;

/**
 * This class has been auto-generated
 * by the PubSubRouterBundle.
 */
class {$options['class']} extends {$options['base_class']}
{
    private static \$declaredRoutes;

    public function __construct()
    {
        if (null === self::\$declaredRoutes) {
            self::\$declaredRoutes = {$this->generateDeclaredRoutes()};
        }
    }

{$this->generateGenerateMethod()}
}

EOF;
    }

    private function generateDeclaredRoutes(): string
    {
        $routes = "array(\n";
        foreach ($this->getRoutes()->all() as $name => $route) {
            $compiledRoute = $route->compile();

            $properties = [];
            $properties[] = $compiledRoute->getVariables();
            $properties[] = $route->getDefaults();
            $properties[] = $route->getRequirements();
            $properties[] = $compiledRoute->getTokens();

            $routes .= sprintf("        '%s' => %s,\n", $name, PhpMatcherDumper::export($properties));
        }
        $routes .= '    )';

        return $routes;
    }

    /**
     * Generates PHP code representing the `generate` method that implements the GeneratorInterface.
     */
    private function generateGenerateMethod(): string
    {
        return <<<'EOF'
    public function generate(string $name, array $parameters = []): string
    {
        if (!isset(self::$declaredRoutes[$name])) {
            throw new ResourceNotFoundException(sprintf('Unable to generate a path for the named route "%s" as such route does not exist.', $name));
        }

        list($variables, $defaults, $requirements, $tokens) = self::$declaredRoutes[$name];

        return $this->doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $name);
    }
EOF;
    }
}
