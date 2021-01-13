<?php

namespace Gos\Bundle\PubSubRouterBundle\Loader\Configurator;

use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;

final class CollectionConfigurator
{
    use Traits\AddTrait;
    use Traits\RouteTrait;

    /**
     * @var RouteCollection
     */
    private $parent;

    /**
     * @var CollectionConfigurator|null
     */
    private $parentConfigurator;

    public function __construct(RouteCollection $parent, string $name, self $parentConfigurator = null)
    {
        $this->parent = $parent;
        $this->name = $name;
        $this->collection = new RouteCollection();
        $this->route = new Route('', 'strlen');
        $this->parentConfigurator = $parentConfigurator; // for GC control
    }

    public function __sleep()
    {
        throw new \BadMethodCallException('Cannot serialize '.self::class);
    }

    public function __wakeup()
    {
        throw new \BadMethodCallException('Cannot unserialize '.self::class);
    }

    public function __destruct()
    {
        $this->parent->addCollection($this->collection);
    }

    /**
     * Creates a sub-collection.
     */
    public function collection(string $name = ''): self
    {
        return new self($this->collection, $name, $this);
    }
}
