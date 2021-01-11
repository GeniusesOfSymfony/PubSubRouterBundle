<?php
/**
 * This file has been auto-generated
 * by the PubSubRouterBundle.
 */
return [
    [ // $staticRoutes
        'overridden' => [['overridden', [], 'overridden', 'strlen', [], [], ['compiler_class' => 'Gos\\Bundle\\PubSubRouterBundle\\Router\\RouteCompiler']]],
        'test/baz' => [['baz', [], 'test/baz', 'strlen', [], [], ['compiler_class' => 'Gos\\Bundle\\PubSubRouterBundle\\Router\\RouteCompiler']]],
        'test/baz.html' => [['baz2', [], 'test/baz.html', 'strlen', [], [], ['compiler_class' => 'Gos\\Bundle\\PubSubRouterBundle\\Router\\RouteCompiler']]],
        'test/baz3/' => [['baz3', [], 'test/baz3/', 'strlen', [], [], ['compiler_class' => 'Gos\\Bundle\\PubSubRouterBundle\\Router\\RouteCompiler']]],
        'foofoo' => [['foofoo', [], 'foofoo', 'strlen', ['def' => 'test'], [], ['compiler_class' => 'Gos\\Bundle\\PubSubRouterBundle\\Router\\RouteCompiler']]],
        'spa ce' => [['space', [], 'spa ce', 'strlen', [], [], ['compiler_class' => 'Gos\\Bundle\\PubSubRouterBundle\\Router\\RouteCompiler']]],
        'new' => [['overridden2', [], 'new', 'strlen', [], [], ['compiler_class' => 'Gos\\Bundle\\PubSubRouterBundle\\Router\\RouteCompiler']]],
        'hey/' => [['hey', [], 'hey/', 'strlen', [], [], ['compiler_class' => 'Gos\\Bundle\\PubSubRouterBundle\\Router\\RouteCompiler']]],
        'ababa' => [['ababa', [], 'ababa', 'strlen', [], [], ['compiler_class' => 'Gos\\Bundle\\PubSubRouterBundle\\Router\\RouteCompiler']]],
    ],
    [ // $regexpList
        0 => '{^(?'
                .'|foo/(baz|symfony)(*:24)'
                .'|test/([^/]++)(?'
                    .'|(*:47)'
                .')'
                .'|([\']+)(*:61)'
                .'|hello(?:/([^/]++))?(*:87)'
                .'|aba/([^/]++)(*:106)'
            .')/?$}sD',
    ],
    [ // $dynamicRoutes
        24 => [['foo', ['bar'], 'foo/{bar}', 'strlen', ['def' => 'test'], ['bar' => 'baz|symfony'], ['compiler_class' => 'Gos\\Bundle\\PubSubRouterBundle\\Router\\RouteCompiler']]],
        47 => [
            ['baz4', ['foo'], 'test/{foo}/', 'strlen', [], [], ['compiler_class' => 'Gos\\Bundle\\PubSubRouterBundle\\Router\\RouteCompiler']],
            ['baz.baz5', ['foo'], 'test/{foo}/', 'strlen', [], [], ['compiler_class' => 'Gos\\Bundle\\PubSubRouterBundle\\Router\\RouteCompiler']],
        ],
        61 => [['quoter', ['quoter'], '{quoter}', 'strlen', [], ['quoter' => '[\']+'], ['compiler_class' => 'Gos\\Bundle\\PubSubRouterBundle\\Router\\RouteCompiler']]],
        87 => [['helloWorld', ['who'], 'hello/{who}', 'strlen', ['who' => 'World!'], [], ['compiler_class' => 'Gos\\Bundle\\PubSubRouterBundle\\Router\\RouteCompiler']]],
        106 => [
            ['foo4', ['foo'], 'aba/{foo}', 'strlen', [], [], ['compiler_class' => 'Gos\\Bundle\\PubSubRouterBundle\\Router\\RouteCompiler']],
            [null, null, null, null, false, false, 0],
        ],
    ],
];