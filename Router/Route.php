<?php

namespace Gos\Bundle\PubSubRouterBundle\Router;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
class Route implements RouteInterface
{
    /**
     * @var string
     */
    protected $pattern;

    /**
     * @var callable|string
     */
    protected $callback;

    /**
     * @var array
     */
    protected $args;

    /**
     * @var array
     */
    protected $requirements;

    /**
     * @var string
     */
    protected $name;

    /**
     * @param string          $pattern
     * @param callable|string $callback
     * @param array           $args
     * @param array           $requirements
     */
    public function __construct($pattern, $callback, Array $args = [], Array $requirements = [])
    {
        $this->pattern = $pattern;
        $this->callback = $callback;
        $this->args = $args;
        $this->requirements = $requirements;
    }

    /**
     * @param array $data
     *
     * @return Route
     */
    public static function __set_state($data)
    {
        $route = new self($data['pattern'], $data['callback'], $data['args'], $data['requirements']);

        if (isset($data['name'])) {
            $route->setName($data['name']);
        }

        return $route;
    }

    /**
     * {@inheritdoc}
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * {@inheritdoc}
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequirements()
    {
        return $this->requirements;
    }

    /**
     * {@inheritdoc}
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (null !== $this->name) {
            return $this->name;
        }

        return spl_object_hash($this);
    }

    /**
     * @param $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}
