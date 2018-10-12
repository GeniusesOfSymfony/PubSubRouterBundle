<?php

namespace Gos\Bundle\PubSubRouterBundle\Generator\Dumper;

use Gos\Bundle\PubSubRouterBundle\Generator\Generator;
use Symfony\Component\Routing\Matcher\Dumper\PhpMatcherDumper;

class PhpGeneratorDumper extends GeneratorDumper
{
    /**
     * Dumps a set of routes to a PHP class.
     *
     * Available options:
     *
     *  * class:      The class name
     *  * base_class: The base class name
     *
     * @param array $options An array of options
     *
     * @return string A PHP class representing the generator class
     */
    public function dump(array $options = array())
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

    /**
     * @return string
     */
    private function generateDeclaredRoutes()
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
     *
     * @return string
     */
    private function generateGenerateMethod()
    {
        return <<<'EOF'
    public function generate($name, array $parameters = [])
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
