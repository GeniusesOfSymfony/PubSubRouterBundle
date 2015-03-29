<?php

namespace Gos\Bundle\PubSubRouterBundle\Dumper;

interface DumperInterface
{
    /**
     * $extras looks like:.
     *
     * $extras = array('route_name' => array('param1' => 'value', ...));
     *
     * @param string $tokenSeparator
     * @param array  $extras
     *
     * @return array
     */
    public function dump($tokenSeparator = ':', Array $extras = []);
}
