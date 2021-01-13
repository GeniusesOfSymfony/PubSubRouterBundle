<?php

use Gos\Bundle\PubSubRouterBundle\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->collection()
        ->add('user_chat', 'chat/{user}', 'strlen')
            ->defaults(['user' => 42])
            ->requirements(['user' => '\d+'])
            ->options(['foo' => 'bar']);

    $routes->import('php_dsl_import.php');
};
