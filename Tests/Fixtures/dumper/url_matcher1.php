<?php

use Gos\Bundle\PubSubRouterBundle\Exception\ResourceNotFoundException;

/**
 * This class has been auto-generated
 * by the PubSubRouterBundle.
 */
class ProjectMatcher extends Gos\Bundle\PubSubRouterBundle\Matcher\Matcher
{
    public function match($channel)
    {
        switch ($channel) {
            case 'overridden':
                // overridden
                return array('_route' => 'overridden');
                break;
            case 'test/baz':
                // baz
                return array('_route' => 'baz');
                break;
            case 'test/baz.html':
                // baz2
                return array('_route' => 'baz2');
                break;
            case 'test/baz3/':
                // baz3
                return array('_route' => 'baz3');
                break;
            case 'foofoo':
                // foofoo
                return array('_route' => 'foofoo', 'def' => 'test');
                break;
            case 'spa ce':
                // space
                return array('_route' => 'space');
                break;
            case 'new':
                // overridden2
                return array('_route' => 'overridden2');
                break;
            case 'hey/':
                // hey
                return array('_route' => 'hey');
                break;
            case 'ababa':
                // ababa
                return array('_route' => 'ababa');
                break;
        }

        $matchedChannel = $channel;
        $regexList = array(
            0 => '{^(?'
                .')$}sD',
        );

        foreach ($regexList as $offset => $regex) {
            while (preg_match($regex, $matchedChannel, $matches)) {
                switch ($m = (int) $matches['MARK']) {
                }

                if (4 === $m) {
                    break;
                }
                $regex = substr_replace($regex, 'F', $m - $offset, 1 + strlen($m));
                $offset += strlen($m);
            }
        }

        throw new ResourceNotFoundException();
    }
}
