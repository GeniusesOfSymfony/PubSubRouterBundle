<?php

namespace Gos\Bundle\PubSubRouterBundle\Router;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
class Route
{
    /**
     * @var string
     */
    protected $pattern = '';

    /**
     * @var callable|string
     */
    protected $callback;

    /**
     * @var array
     */
    private $defaults = [];

    /**
     * @var array
     */
    protected $requirements = [];

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var CompiledRoute|null
     */
    private $compiled;

    /**
     * Constructor.
     *
     * Available options:
     *
     *  * compiler_class: A class name able to compile this route instance (RouteCompiler by default)
     *  * utf8:           Whether UTF-8 matching is enforced ot not
     *
     * @param string   $pattern         The path pattern to match
     * @param callable $callback|string A callable function that handles this route or a string to be used with a service locator
     * @param array    $defaults        An array of default parameter values
     * @param array    $requirements    An array of requirements for parameters (regexes)
     * @param array    $options         An array of options
     */
    public function __construct($pattern, $callback, array $defaults = [], array $requirements = [], array $options = [])
    {
        $this->setPattern($pattern);
        $this->setCallback($callback);
        $this->addDefaults($defaults);
        $this->addRequirements($requirements);
        $this->setOptions($options);
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(
            [
                'pattern' => $this->pattern,
                'callback' => $this->callback,
                'defaults' => $this->defaults,
                'requirements' => $this->requirements,
                'options' => $this->options,
                'compiled' => $this->compiled,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);

        $this->pattern = $data['pattern'];
        $this->callback = $data['callback'];
        $this->defaults = $data['defaults'];
        $this->requirements = $data['requirements'];
        $this->options = $data['options'];
        $this->compiled = $data['compiled'];
    }

    /**
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * @param string $pattern
     */
    public function setPattern($pattern)
    {
        if (false !== strpbrk($pattern, '?<')) {
            $pattern = preg_replace_callback('#\{(\w++)(<.*?>)?(\?[^\}]*+)?\}#', function ($m) {
                if (isset($m[3][0])) {
                    $this->setDefault($m[1], '?' !== $m[3] ? substr($m[3], 1) : null);
                }

                if (isset($m[2][0])) {
                    $this->setRequirement($m[1], substr($m[2], 1, -1));
                }

                return '{'.$m[1].'}';
            }, $pattern);
        }

        $this->pattern = trim($pattern);
        $this->compiled = null;

        return $this;
    }

    /**
     * @return $callback|string
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * @param $callback|string $callback
     */
    public function setCallback($callback)
    {
        if (!is_callable($callback) && !is_string($callback)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The callback for a route must be a PHP callable or a string, a "%s" was given.',
                    gettype($callback)
                )
            );
        }

        $this->callback = $callback;

        return $this;
    }

    /**
     * @return array
     */
    public function getDefaults()
    {
        return $this->defaults;
    }

    /**
     * @param array $defaults
     */
    public function setDefaults(array $defaults)
    {
        $this->defaults = [];

        return $this->addDefaults($defaults);
    }

    /**
     * @param array $defaults
     */
    public function addDefaults(array $defaults)
    {
        foreach ($defaults as $name => $default) {
            $this->defaults[$name] = $default;
        }

        $this->compiled = null;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return mixed The default value or null when not given
     */
    public function getDefault($name)
    {
        return isset($this->defaults[$name]) ? $this->defaults[$name] : null;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasDefault($name)
    {
        return isset($this->defaults[$name]);
    }

    /**
     * @param string $name
     * @param mixed  $default
     */
    public function setDefault($name, $default)
    {
        $this->defaults[$name] = $default;
        $this->compiled = null;

        return $this;
    }

    /**
     * @return array
     */
    public function getRequirements()
    {
        return $this->requirements;
    }

    /**
     * @param array $requirements
     */
    public function setRequirements(array $requirements)
    {
        $this->requirements = [];

        return $this->addRequirements($requirements);
    }

    /**
     * @param array $requirements
     */
    public function addRequirements(array $requirements)
    {
        foreach ($requirements as $key => $regex) {
            $this->requirements[$key] = $this->sanitizeRequirement($key, $regex);
        }

        $this->compiled = null;

        return $this;
    }

    /**
     * @param string $key
     *
     * @return string|null
     */
    public function getRequirement($key)
    {
        return isset($this->requirements[$key]) ? $this->requirements[$key] : null;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasRequirement($key)
    {
        return isset($this->requirements[$key]);
    }

    /**
     * @param string $key
     * @param string $regex
     */
    public function setRequirement($key, $regex)
    {
        $this->requirements[$key] = $this->sanitizeRequirement($key, $regex);
        $this->compiled = null;

        return $this;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = [
            'compiler_class' => RouteCompiler::class,
        ];

        return $this->addOptions($options);
    }

    /**
     * @param array $options
     */
    public function addOptions(array $options)
    {
        foreach ($options as $name => $option) {
            $this->options[$name] = $option;
        }

        $this->compiled = null;

        return $this;
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
        $this->compiled = null;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return mixed The option value or null when not given
     */
    public function getOption($name)
    {
        return isset($this->options[$name]) ? $this->options[$name] : null;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasOption($name)
    {
        return isset($this->options[$name]);
    }

    /**
     * @return CompiledRoute
     *
     * @throws \LogicException If the Route cannot be compiled because the pattern is invalid
     */
    public function compile()
    {
        if (null !== $this->compiled) {
            return $this->compiled;
        }

        $class = $this->getOption('compiler_class');

        return $this->compiled = $class::compile($this);
    }

    private function sanitizeRequirement($key, $regex)
    {
        if (!\is_string($regex)) {
            throw new \InvalidArgumentException(sprintf('Routing requirement for "%s" must be a string.', $key));
        }

        if ('' !== $regex && '^' === $regex[0]) {
            $regex = (string) substr($regex, 1); // returns false for a single character
        }

        if ('$' === substr($regex, -1)) {
            $regex = substr($regex, 0, -1);
        }

        if ('' === $regex) {
            throw new \InvalidArgumentException(sprintf('Routing requirement for "%s" cannot be empty.', $key));
        }

        return $regex;
    }
}
