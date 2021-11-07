<?php

namespace Gos\Bundle\PubSubRouterBundle\Loader;

use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;
use Symfony\Component\Config\Exception\FileLoaderLoadException;
use Symfony\Component\Config\Loader\Loader;

// This is a rather flaky check, but it is one of the few things that exists in 4.x but not 5.x
if (class_exists(FileLoaderLoadException::class)) {
    /**
     * Compatibility file loader for Symfony 4.4 and earlier.
     *
     * @internal To be removed when dropping support for Symfony 4.4 and earlier
     */
    abstract class CompatibilityLoader extends Loader
    {
        /**
         * @param mixed       $resource
         * @param string|null $type
         */
        public function load($resource, $type = null): RouteCollection
        {
            return $this->doLoad($resource, $type);
        }

        /**
         * @param mixed $resource
         */
        abstract protected function doLoad($resource, string $type = null): RouteCollection;

        /**
         * @param mixed       $resource
         * @param string|null $type
         */
        public function supports($resource, $type = null): bool
        {
            return $this->doSupports($resource, $type);
        }

        /**
         * @param mixed $resource
         */
        abstract protected function doSupports($resource, string $type = null): bool;
    }
} else {
    /**
     * Compatibility file loader for Symfony 5.0 and later.
     *
     * @internal To be removed when dropping support for Symfony 4.4 and earlier
     */
    abstract class CompatibilityLoader extends Loader
    {
        /**
         * @param mixed $resource
         */
        public function load($resource, string $type = null): RouteCollection
        {
            return $this->doLoad($resource, $type);
        }

        /**
         * @param mixed $resource
         */
        abstract protected function doLoad($resource, string $type = null): RouteCollection;

        /**
         * @param mixed $resource
         */
        public function supports($resource, string $type = null): bool
        {
            return $this->doSupports($resource, $type);
        }

        /**
         * @param mixed $resource
         */
        abstract protected function doSupports($resource, string $type = null): bool;
    }
}
