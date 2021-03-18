<?php

namespace Gos\Bundle\PubSubRouterBundle\Console\Descriptor;

use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;
use Symfony\Component\Console\Descriptor\DescriptorInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Descriptor implements DescriptorInterface
{
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * Describes an object if supported.
     *
     * @param object $object
     */
    public function describe(OutputInterface $output, $object, array $options = []): void
    {
        switch (true) {
            case $object instanceof RouteCollection:
                $this->describeRouteCollection($object, $options);

                break;

            default:
                throw new \InvalidArgumentException(sprintf('Object of type "%s" is not describable.', get_debug_type($object)));
        }
    }

    protected function getOutput(): OutputInterface
    {
        return $this->output;
    }

    protected function write(string $content, bool $decorated = false): void
    {
        $this->output->write($content, false, $decorated ? OutputInterface::OUTPUT_NORMAL : OutputInterface::OUTPUT_RAW);
    }

    abstract protected function describeRouteCollection(RouteCollection $routes, array $options = []): void;
}
