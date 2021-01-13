<?php

use Gos\Bundle\PubSubRouterBundle\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->collection()
        ->add('user_read', 'users/{user}', 'strlen')
            ->defaults(['user' => 42])
            ->requirements(['user' => '\d+'])
            ->options(['foo' => 'bar']);
};
