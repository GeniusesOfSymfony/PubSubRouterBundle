<?php

namespace Gos\Bundle\PubSubRouterBundle\Loader\Configurator;

use Gos\Bundle\PubSubRouterBundle\Loader\PhpFileLoader;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;

final class RoutingConfigurator
{
    use Traits\AddTrait;

    /**
     * @var PhpFileLoader
     */
    private $loader;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $file;

    public function __construct(RouteCollection $collection, PhpFileLoader $loader, string $path, string $file)
    {
        $this->collection = $collection;
        $this->loader = $loader;
        $this->path = $path;
        $this->file = $file;
    }

    /**
     * @param mixed                $resource
     * @param string|string[]|null $exclude  Glob patterns to exclude from the import
     */
    public function import($resource, string $type = null, bool $ignoreErrors = false, $exclude = null): ImportConfigurator
    {
        $this->loader->setCurrentDir(\dirname($this->path));

        $imported = $this->loader->import($resource, $type, $ignoreErrors, $this->file, $exclude) ?: [];

        if (!\is_array($imported)) {
            return new ImportConfigurator($this->collection, $imported);
        }

        $mergedCollection = new RouteCollection();

        foreach ($imported as $subCollection) {
            $mergedCollection->addCollection($subCollection);
        }

        return new ImportConfigurator($this->collection, $mergedCollection);
    }

    public function collection(string $name = ''): CollectionConfigurator
    {
        return new CollectionConfigurator($this->collection, $name);
    }

    public function withPath(string $path): self
    {
        $clone = clone $this;
        $clone->path = $clone->file = $path;

        return $clone;
    }
}
