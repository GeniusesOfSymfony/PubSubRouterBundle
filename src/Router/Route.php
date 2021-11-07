<?php

namespace Gos\Bundle\PubSubRouterBundle\Router;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 * @final
 */
class Route implements \Serializable
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
     * @param string          $pattern      The path pattern to match
     * @param callable|string $callback     A callable function that handles this route or a string to be used with a service locator
     * @param array           $defaults     An array of default parameter values
     * @param array           $requirements An array of requirements for parameters (regexes)
     * @param array           $options      An array of options
     */
    public function __construct(string $pattern, $callback, array $defaults = [], array $requirements = [], array $options = [])
    {
        $this->setPattern($pattern);
        $this->setCallback($callback);
        $this->addDefaults($defaults);
        $this->addRequirements($requirements);
        $this->setOptions($options);
    }

    public function serialize()
    {
        return serialize($this->__serialize());
    }

    public function __serialize(): array
    {
        return [
            'pattern' => $this->pattern,
            'callback' => $this->callback,
            'defaults' => $this->defaults,
            'requirements' => $this->requirements,
            'options' => $this->options,
            'compiled' => $this->compiled,
        ];
    }

    public function unserialize($serialized): void
    {
        $this->__unserialize(unserialize($serialized));
    }

    public function __unserialize(array $data): void
    {
        $this->pattern = $data['pattern'];
        $this->callback = $data['callback'];
        $this->defaults = $data['defaults'];
        $this->requirements = $data['requirements'];
        $this->options = $data['options'];
        $this->compiled = $data['compiled'];
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }

    public function setPattern(string $pattern): self
    {
        if (false !== strpbrk($pattern, '?<')) {
            $pattern = preg_replace_callback('#\{(\w++)(<.*?>)?(\?[^\}]*+)?\}#', function ($m): string {
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
     * @return callable|string
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * @param callable|string $callback
     *
     * @throws \InvalidArgumentException if the callback is not a valid type
     */
    public function setCallback($callback): self
    {
        if (!\is_callable($callback) && !\is_string($callback)) {
            throw new \InvalidArgumentException(sprintf('The callback for a route must be a PHP callable or a string, a "%s" was given.', \gettype($callback)));
        }

        $this->callback = $callback;

        return $this;
    }

    public function getDefaults(): array
    {
        return $this->defaults;
    }

    public function setDefaults(array $defaults): self
    {
        $this->defaults = [];

        return $this->addDefaults($defaults);
    }

    public function addDefaults(array $defaults): self
    {
        foreach ($defaults as $name => $default) {
            $this->defaults[$name] = $default;
        }

        $this->compiled = null;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDefault(string $name)
    {
        return $this->defaults[$name] ?? null;
    }

    public function hasDefault(string $name): bool
    {
        return isset($this->defaults[$name]);
    }

    /**
     * @param mixed $default
     */
    public function setDefault(string $name, $default): self
    {
        $this->defaults[$name] = $default;
        $this->compiled = null;

        return $this;
    }

    public function getRequirements(): array
    {
        return $this->requirements;
    }

    public function setRequirements(array $requirements): self
    {
        $this->requirements = [];

        return $this->addRequirements($requirements);
    }

    public function addRequirements(array $requirements): self
    {
        foreach ($requirements as $key => $regex) {
            $this->requirements[$key] = $this->sanitizeRequirement($key, $regex);
        }

        $this->compiled = null;

        return $this;
    }

    public function getRequirement(string $key): ?string
    {
        return $this->requirements[$key] ?? null;
    }

    public function hasRequirement(string $key): bool
    {
        return isset($this->requirements[$key]);
    }

    public function setRequirement(string $key, string $regex): self
    {
        $this->requirements[$key] = $this->sanitizeRequirement($key, $regex);
        $this->compiled = null;

        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): self
    {
        $this->options = [
            'compiler_class' => RouteCompiler::class,
        ];

        return $this->addOptions($options);
    }

    public function addOptions(array $options): self
    {
        foreach ($options as $name => $option) {
            $this->options[$name] = $option;
        }

        $this->compiled = null;

        return $this;
    }

    /**
     * @param mixed $value
     */
    public function setOption(string $name, $value): self
    {
        $this->options[$name] = $value;
        $this->compiled = null;

        return $this;
    }

    /**
     * @return mixed The option value or null when not given
     */
    public function getOption(string $name)
    {
        return $this->options[$name] ?? null;
    }

    public function hasOption(string $name): bool
    {
        return isset($this->options[$name]);
    }

    /**
     * @throws \LogicException if the Route cannot be compiled because the pattern is invalid
     */
    public function compile(): CompiledRoute
    {
        if (null !== $this->compiled) {
            return $this->compiled;
        }

        $class = $this->getOption('compiler_class');

        return $this->compiled = $class::compile($this);
    }

    /**
     * @throws \InvalidArgumentException if a requirement value is empty
     */
    private function sanitizeRequirement(string $key, string $regex): string
    {
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
