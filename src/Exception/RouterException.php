<?php

namespace Gos\Bundle\PubSubRouterBundle\Exception;

trigger_deprecation('gos/pubsub-router-bundle', '2.4', 'The "%s" class is deprecated and will be removed in 3.0, exceptions should implement "%s" instead.', RouterException::class, PubSubRouterException::class);

/**
 * @deprecated to be removed in 3.0, exceptions should implement `Gos\Bundle\PubSubRouterBundle\Exception\PubSubRouterException` instead
 */
class RouterException extends \Exception implements PubSubRouterException
{
}
