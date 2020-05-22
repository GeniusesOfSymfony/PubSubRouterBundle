<?php

namespace Gos\Bundle\PubSubRouterBundle\CacheWarmer;

use Symfony\Component\DependencyInjection\ServiceSubscriberInterface as ComponentServiceSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface as ContractServiceSubscriberInterface;

if (interface_exists(ComponentServiceSubscriberInterface::class)) {
    /**
     * Compatibility file loader for Symfony 4.4 and earlier.
     *
     * @internal To be removed when dropping support for Symfony 4.2 and earlier
     */
	interface CompatibilityServiceSubscriberInterface extends ComponentServiceSubscriberInterface
	{
	}
} else {
    /**
     * Compatibility interface loader for Symfony 5.0 and later.
     *
     * @internal To be removed when dropping support for Symfony 4.2 and earlier
     */
	interface CompatibilityServiceSubscriberInterface extends ContractServiceSubscriberInterface
	{
	}
}
