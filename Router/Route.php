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
    protected $pattern = '';

    /**
     * @var callable|string
     */
    protected $callback;

    /**
     * @var array
     */
    protected $requirements = [];

    /**
     * @param string          $pattern
     * @param callable|string $callback
     * @param array           $requirements
     */
    public function __construct($pattern, $callback, array $requirements = [])
    {
        $this->pattern = $pattern;
        $this->callback = $callback;
        $this->requirements = $requirements;
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
                'requirements' => $this->requirements,
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
        $this->requirements = $data['requirements'];
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
}
