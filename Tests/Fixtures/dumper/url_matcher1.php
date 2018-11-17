<?php

use Gos\Bundle\PubSubRouterBundle\Exception\ResourceNotFoundException;
use Gos\Bundle\PubSubRouterBundle\Router\Route;

/**
 * This class has been auto-generated
 * by the PubSubRouterBundle.
 */
class ProjectMatcher extends Gos\Bundle\PubSubRouterBundle\Matcher\Matcher
{
    public function __construct()
    {
    }

    public function match(string $channel): array
    {
        switch ($channel) {
            case 'overridden':
                // overridden
                return array('overridden', new Route('overridden', 'strlen', array(), array(), array('compiler_class' => 'Gos\\Bundle\\PubSubRouterBundle\\Router\\RouteCompiler')), array());
                break;
            case 'test/baz':
                // baz
                return array('baz', new Route('test/baz', 'strlen', array(), array(), array('compiler_class' => 'Gos\\Bundle\\PubSubRouterBundle\\Router\\RouteCompiler')), array());
                break;
            case 'test/baz.html':
                // baz2
                return array('baz2', new Route('test/baz.html', 'strlen', array(), array(), array('compiler_class' => 'Gos\\Bundle\\PubSubRouterBundle\\Router\\RouteCompiler')), array());
                break;
            case 'test/baz3/':
                // baz3
                return array('baz3', new Route('test/baz3/', 'strlen', array(), array(), array('compiler_class' => 'Gos\\Bundle\\PubSubRouterBundle\\Router\\RouteCompiler')), array());
                break;
            case 'foofoo':
                // foofoo
                return array('foofoo', new Route('foofoo', 'strlen', array('def' => 'test'), array(), array('compiler_class' => 'Gos\\Bundle\\PubSubRouterBundle\\Router\\RouteCompiler')), array('def' => 'test'));
                break;
            case 'spa ce':
                // space
                return array('space', new Route('spa ce', 'strlen', array(), array(), array('compiler_class' => 'Gos\\Bundle\\PubSubRouterBundle\\Router\\RouteCompiler')), array());
                break;
            case 'new':
                // overridden2
                return array('overridden2', new Route('new', 'strlen', array(), array(), array('compiler_class' => 'Gos\\Bundle\\PubSubRouterBundle\\Router\\RouteCompiler')), array());
                break;
            case 'hey/':
                // hey
                return array('hey', new Route('hey/', 'strlen', array(), array(), array('compiler_class' => 'Gos\\Bundle\\PubSubRouterBundle\\Router\\RouteCompiler')), array());
                break;
            case 'ababa':
                // ababa
                return array('ababa', new Route('ababa', 'strlen', array(), array(), array('compiler_class' => 'Gos\\Bundle\\PubSubRouterBundle\\Router\\RouteCompiler')), array());
                break;
        }

        $matchedChannel = $channel;
        $regex = '{^(?'
                    .'|foo/(baz|symfony)(*:24)'
                .'|test/([^/]++)/(?'
                        .'|(*:48)'
                .')'
                    .'|([\']+)(*:62)'
                    .'|hello(?:/([^/]++))?(*:88)'
                    .'|aba/([^/]++)(*:107)'
                .')$}sD';

        while (preg_match($regex, $matchedChannel, $matches)) {
            switch ($m = (int) $matches['MARK']) {
                case 48:
                    $matches = array('foo' => $matches[1] ?? null);

                    // baz4
                    return array('baz4', new Route('test/{foo}/', 'strlen', array(), array(), array('compiler_class' => 'Gos\\Bundle\\PubSubRouterBundle\\Router\\RouteCompiler')), $this->mergeDefaults(array('baz4') + $matches, array()));

                    // baz.baz5
                    return array('baz.baz5', new Route('test/{foo}/', 'strlen', array(), array(), array('compiler_class' => 'Gos\\Bundle\\PubSubRouterBundle\\Router\\RouteCompiler')), $this->mergeDefaults(array('baz.baz5') + $matches, array()));

                    break;
                default:
                    $routes = array(
                        24 => array('foo', new Route('foo/{bar}', 'strlen', array('def' => 'test'), array('bar' => 'baz|symfony'), array('compiler_class' => 'Gos\\Bundle\\PubSubRouterBundle\\Router\\RouteCompiler')), array('bar')),
                        62 => array('quoter', new Route('{quoter}', 'strlen', array(), array('quoter' => '[\']+'), array('compiler_class' => 'Gos\\Bundle\\PubSubRouterBundle\\Router\\RouteCompiler')), array('quoter')),
                        88 => array('helloWorld', new Route('hello/{who}', 'strlen', array('who' => 'World!'), array(), array('compiler_class' => 'Gos\\Bundle\\PubSubRouterBundle\\Router\\RouteCompiler')), array('who')),
                        107 => array('foo4', new Route('aba/{foo}', 'strlen', array(), array(), array('compiler_class' => 'Gos\\Bundle\\PubSubRouterBundle\\Router\\RouteCompiler')), array('foo')),
                    );

                    list($name, $route, $vars) = $routes[$m];

                    $attributes = array();

                    foreach ($vars as $i => $v) {
                        if (isset($matches[1 + $i])) {
                            $attributes[$v] = $matches[1 + $i];
                        }
                    }

                    $attributes = $this->mergeDefaults($attributes, $route->getDefaults());

                    return array($name, $route, $attributes);
            }

            if (107 === $m) {
                break;
            }
            $regex = substr_replace($regex, 'F', $m - $offset, 1 + strlen($m));
            $offset += strlen($m);
        }

        throw new ResourceNotFoundException();
    }
}
