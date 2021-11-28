<?php

namespace Gos\Bundle\PubSubRouterBundle\Loader;

use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;
use Symfony\Component\Config\Loader\FileLoader;

/**
 * @final
 */
class GlobFileLoader extends FileLoader
{
    public function load(mixed $resource, string $type = null): RouteCollection
    {
        $collection = new RouteCollection();

        foreach ($this->glob($resource, false, $globResource) as $path => $info) {
            $collection->addCollection($this->import($path));
        }

        $collection->addResource($globResource);

        return $collection;
    }

    public function supports(mixed $resource, string $type = null): bool
    {
        return 'glob' === $type;
    }
}
