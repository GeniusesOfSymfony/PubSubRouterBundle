<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Console\Descriptor;

use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Descriptor\DescriptorInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class AbstractDescriptorTestCase extends TestCase
{
    /**
     * @dataProvider getDescribeRouteCollectionTestData
     */
    public function testDescribeRouteCollection(RouteCollection $routes, string $expectedDescription, string $file): void
    {
        $this->assertDescription($expectedDescription, $routes, $file);
    }

    public function getDescribeRouteCollectionTestData(): array
    {
        return $this->getDescriptionTestData(DescriptorProvider::getRouteCollections());
    }

    /**
     * @dataProvider getDescribeRouteTestData
     */
    public function testDescribeRoute(Route $route, string $expectedDescription, string $file): void
    {
        $this->assertDescription($expectedDescription, $route, $file);
    }

    public function getDescribeRouteTestData(): array
    {
        return $this->getDescriptionTestData(DescriptorProvider::getRoutes());
    }

    abstract protected function getDescriptor(): DescriptorInterface;

    abstract protected function getFormat(): string;

    private function assertDescription(string $expectedDescription, object $describedObject, string $file, array $options = []): void
    {
        $options['is_debug'] = false;
        $options['raw_output'] = true;
        $options['raw_text'] = true;
        $output = new BufferedOutput(BufferedOutput::VERBOSITY_NORMAL, true);

        if ('txt' === $this->getFormat()) {
            $options['output'] = new SymfonyStyle(new ArrayInput([]), $output);
        }

        $this->getDescriptor()->describe($output, $describedObject, $options);

        if ('json' === $this->getFormat()) {
            $this->assertEquals(json_encode(json_decode($expectedDescription), \JSON_PRETTY_PRINT), json_encode(json_decode($output->fetch()), \JSON_PRETTY_PRINT));
        } else {
            $this->assertStringMatchesFormatFile(__DIR__.'/../../Fixtures/descriptor/'.$file, $output->fetch());
        }
    }

    private function getDescriptionTestData(array $objects): array
    {
        $data = [];

        foreach ($objects as $name => $object) {
            $file = sprintf('%s.%s', trim($name, '.'), $this->getFormat());
            $description = file_get_contents(__DIR__.'/../../Fixtures/descriptor/'.$file);
            $data[] = [$object, $description, $file];
        }

        return $data;
    }
}
