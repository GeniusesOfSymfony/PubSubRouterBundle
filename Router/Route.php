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
    protected $pattern;

    /**
     * @var string[]
     */
    protected $pushers;

    /**
     * @var array
     */
    protected $requirements;

    /**
     * @param string   $pattern
     * @param string[] $pushers
     * @param array    $requirements
     */
    public function __construct($pattern, Array $pushers = array(), Array $requirements = array())
    {
        $this->pattern = $pattern;
        $this->pushers = $pushers;
        $this->requirements = $requirements;
    }

    /**
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * @return \string[]
     */
    public function getPushers()
    {
        return $this->pushers;
    }

    /**
     * @return array
     */
    public function getRequirements()
    {
        return $this->requirements;
    }
}
