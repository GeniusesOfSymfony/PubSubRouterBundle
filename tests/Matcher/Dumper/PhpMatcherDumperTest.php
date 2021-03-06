<?php

namespace Gos\Bundle\PubSubRouterBundle\Tests\Matcher\Dumper;

use Gos\Bundle\PubSubRouterBundle\Matcher\Dumper\PhpMatcherDumper;
use Gos\Bundle\PubSubRouterBundle\Router\Route;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;
use PHPUnit\Framework\TestCase;

class PhpMatcherDumperTest extends TestCase
{
    /**
     * @var string
     */
    private $matcherClass;

    /**
     * @var string
     */
    private $dumpPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->matcherClass = uniqid('ProjectMatcher');
        $this->dumpPath = sys_get_temp_dir().\DIRECTORY_SEPARATOR.'php_matcher.'.$this->matcherClass.'.php';
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        @unlink($this->dumpPath);
    }

    public function testMatchDumpedMatcher(): void
    {
        $basePath = __DIR__.'/../../Fixtures/dumper/';

        include $basePath.'/url_matcher1.php';

        $dumper = new \ProjectMatcher();

        list($name, $route, $attributes) = $dumper->match('overridden');

        $this->assertSame('overridden', $name, 'The matched route name is returned');
        $this->assertEquals(new Route('overridden', 'strlen'), $route, 'The Route object is in the expected state');
        $this->assertSame([], $attributes, 'The route attributes are returned after merging defaults');

        list($name, $route, $attributes) = $dumper->match('test/gos/');

        $this->assertSame('baz4', $name, 'The matched route name is returned');
        $this->assertEquals(new Route('test/{foo}/', 'strlen'), $route, 'The Route object is in the expected state');
        $this->assertSame(['foo' => 'gos'], $attributes, 'The route attributes are returned after merging defaults');

        list($name, $route, $attributes) = $dumper->match('hello/gos');

        $this->assertSame('helloWorld', $name, 'The matched route name is returned');
        $this->assertEquals(new Route('hello/{who}', 'strlen', ['who' => 'World!']), $route, 'The Route object is in the expected state');
        $this->assertSame(['who' => 'gos'], $attributes, 'The route attributes are returned after merging defaults');
    }

    /**
     * @dataProvider getRouteCollections
     */
    public function testDump(RouteCollection $collection, string $fixture, array $options = []): void
    {
        $basePath = __DIR__.'/../../Fixtures/dumper/';

        $dumper = new PhpMatcherDumper($collection);
        $this->assertStringEqualsFile($basePath.$fixture, $dumper->dump($options), '->dump() correctly dumps routes as optimized PHP code.');
    }

    public function getRouteCollections(): array
    {
        /* test case 1 */

        $collection = new RouteCollection();

        $collection->add('overridden', new Route('overridden', 'strlen'));

        // defaults and requirements
        $collection->add(
            'foo',
            new Route(
                'foo/{bar}',
                'strlen',
                ['def' => 'test'],
                ['bar' => 'baz|symfony']
            )
        );
        // simple
        $collection->add(
            'baz',
            new Route(
                'test/baz',
                'strlen'
            )
        );
        // simple with extension
        $collection->add(
            'baz2',
            new Route(
                'test/baz.html',
                'strlen'
            )
        );
        // trailing slash
        $collection->add(
            'baz3',
            new Route(
                'test/baz3/',
                'strlen'
            )
        );
        // trailing slash with variable
        $collection->add(
            'baz4',
            new Route(
                'test/{foo}/',
                'strlen'
            )
        );
        // complex name
        $collection->add(
            'baz.baz5',
            new Route(
                'test/{foo}/',
                'strlen'
            )
        );
        // defaults without variable
        $collection->add(
            'foofoo',
            new Route(
                'foofoo',
                'strlen',
                ['def' => 'test']
            )
        );
        // pattern with quotes
        $collection->add(
            'quoter',
            new Route(
                '{quoter}',
                'strlen',
                [],
                ['quoter' => '[\']+']
            )
        );
        // space in pattern
        $collection->add(
            'space',
            new Route(
                'spa ce',
                'strlen'
            )
        );

        // overridden through addCollection() and multiple sub-collections with no own prefix
        $collection1 = new RouteCollection();
        $collection1->add('overridden2', new Route('old', 'strlen'));
        $collection1->add('helloWorld', new Route('hello/{who}', 'strlen', ['who' => 'World!']));
        $collection2 = new RouteCollection();
        $collection3 = new RouteCollection();
        $collection3->add('overridden2', new Route('new', 'strlen'));
        $collection3->add('hey', new Route('hey/', 'strlen'));
        $collection2->addCollection($collection3);
        $collection1->addCollection($collection2);
        $collection->addCollection($collection1);

        // route between collections
        $collection->add('ababa', new Route('ababa', 'strlen'));

        // collection with static prefix but only one route
        $collection1 = new RouteCollection();
        $collection1->add('foo4', new Route('aba/{foo}', 'strlen'));
        $collection->addCollection($collection1);

        return [
            [new RouteCollection(), 'url_matcher0.php', []],
            [$collection, 'url_matcher1.php', []],
        ];
    }
}
