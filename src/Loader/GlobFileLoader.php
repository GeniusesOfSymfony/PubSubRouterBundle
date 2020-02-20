<?php

namespace Gos\Bundle\PubSubRouterBundle\Loader;

use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;

class GlobFileLoader extends CompatibilityFileLoader
{
    /**
     * @param mixed $resource
     */
    protected function doLoad($resource, string $type = null): RouteCollection
    {
        $collection = new RouteCollection();

        foreach ($this->glob($resource, false, $globResource) as $path => $info) {
            $collection->addCollection($this->import($path));
        }

        $collection->addResource($globResource);

        return $collection;
    }

    /**
     * @param mixed $resource
     */
    protected function doSupports($resource, string $type = null): bool
    {
        return 'glob' === $type;
    }
}
