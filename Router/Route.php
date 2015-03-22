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
    public function __construct($pattern, $callback, $args, Array $requirements = [])
    {
        $this->pattern = $pattern;
        $this->callback = $callback;
        $this->args = $args;
        $this->requirements = $requirements;
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
        return $this->name;
    }

    /**
     * @param $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}
