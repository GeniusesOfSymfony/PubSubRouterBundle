<?php

namespace Gos\Bundle\PubSubRouterBundle\Router;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
final class Route implements \Serializable
{
    private string $pattern = '';

    /**
     * @var callable|string
     */
    private $callback;

    /**
     * @var array<string, mixed>
     */
    private array $defaults = [];

    /**
     * @var array<string, string>
     */
    private array $requirements = [];

    /**
     * @var array<string, mixed>
     */
    private array $options = [];

    private ?CompiledRoute $compiled = null;

    /**
     * Constructor.
     *
     * Available options:
     *
     *  * compiler_class: A class name able to compile this route instance (RouteCompiler by default)
     *  * utf8:           Whether UTF-8 matching is enforced ot not
     *
     * @param string                $pattern      The path pattern to match
     * @param callable|string       $callback     A callable function that handles this route or a string to be used with a service locator
     * @param array<string, mixed>  $defaults     An array of default parameter values
     * @param array<string, string> $requirements An array of requirements for parameters (regexes)
     * @param array<string, mixed>  $options      An array of options
     */
    public function __construct(string $pattern, callable | string $callback, array $defaults = [], array $requirements = [], array $options = [])
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
            $pattern = preg_replace_callback('#\{(!?\w++)(<.*?>)?(\?[^\}]*+)?\}#', function ($m) {
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

    public function getCallback(): callable | string
    {
        return $this->callback;
    }

    public function setCallback(callable | string $callback): self
    {
        $this->callback = $callback;

        return $this;
    }

    public function getDefaults(): array
    {
        return $this->defaults;
    }

    /**
     * @param array<string, mixed> $defaults
     */
    public function setDefaults(array $defaults): self
    {
        $this->defaults = [];

        return $this->addDefaults($defaults);
    }

    /**
     * @param array<string, mixed> $defaults
     */
    public function addDefaults(array $defaults): self
    {
        foreach ($defaults as $name => $default) {
            $this->defaults[$name] = $default;
        }

        $this->compiled = null;

        return $this;
    }

    /**
     * @return mixed The default value or null when not given
     */
    public function getDefault(string $name): mixed
    {
        return $this->defaults[$name] ?? null;
    }

    public function hasDefault(string $name): bool
    {
        return isset($this->defaults[$name]);
    }

    public function setDefault(string $name, mixed $default): self
    {
        $this->defaults[$name] = $default;
        $this->compiled = null;

        return $this;
    }

    public function getRequirements(): array
    {
        return $this->requirements;
    }

    /**
     * @param array<string, string> $requirements
     */
    public function setRequirements(array $requirements): self
    {
        $this->requirements = [];

        return $this->addRequirements($requirements);
    }

    /**
     * @param array<string, string> $requirements
     */
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

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function setOptions(array $options): self
    {
        $this->options = [
            'compiler_class' => RouteCompiler::class,
        ];

        return $this->addOptions($options);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function addOptions(array $options): self
    {
        foreach ($options as $name => $option) {
            $this->options[$name] = $option;
        }

        $this->compiled = null;

        return $this;
    }

    public function setOption(string $name, mixed $value): self
    {
        $this->options[$name] = $value;
        $this->compiled = null;

        return $this;
    }

    /**
     * @return mixed The option value or null when not given
     */
    public function getOption(string $name): mixed
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
        if ('' !== $regex) {
            if ('^' === $regex[0]) {
                $regex = substr($regex, 1);
            } elseif (str_starts_with($regex, '\\A')) {
                $regex = substr($regex, 2);
            }
        }

        if ('$' === substr($regex, -1)) {
            $regex = substr($regex, 0, -1);
        } elseif (\strlen($regex) - 2 === strpos($regex, '\\z')) {
            $regex = substr($regex, 0, -2);
        }

        if ('' === $regex) {
            throw new \InvalidArgumentException(sprintf('Routing requirement for "%s" cannot be empty.', $key));
        }

        return $regex;
    }
}
