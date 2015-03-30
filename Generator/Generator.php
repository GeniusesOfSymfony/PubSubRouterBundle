<?php

namespace Gos\Bundle\PubSubRouterBundle\Generator;

use Gos\Bundle\PubSubRouterBundle\Exception\InvalidArgumentException;
use Gos\Bundle\PubSubRouterBundle\Router\RouteCollection;
use Gos\Bundle\PubSubRouterBundle\Tokenizer\Token;
use Gos\Bundle\PubSubRouterBundle\Tokenizer\TokenizerInterface;

class Generator implements GeneratorInterface
{
    /**
     * @var TokenizerInterface
     */
    protected $tokenizer;

    /**
     * @var RouteCollection
     */
    protected $routeCollection;

    /**
     * @param TokenizerInterface $tokenizer
     */
    public function __construct(RouteCollection $routeCollection, TokenizerInterface $tokenizer)
    {
        $this->routeCollection = $routeCollection;
        $this->tokenizer = $tokenizer;
    }

    /**
     * {@inheritdoc}
     */
    public function generate($routeName, Array $parameters = [], $tokenSeparator)
    {
        $route = $this->routeCollection->get($routeName);

        $tokens = $this->tokenizer->tokenize($route, $tokenSeparator);

        return $this->generateFromTokens($tokens, $parameters, $tokenSeparator);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFromTokens(Array $tokens, Array $parameters = [], $tokenSeparator)
    {
        $graph = [];

        /** @var Token $token */
        foreach ($tokens as $token) {
            if ($token->isParameter()) {
                if (!isset($parameters[$token->getExpression()])) {
                    throw new InvalidArgumentException(sprintf('Missing parameter %s', $token->getExpression()));
                }

                $value = $parameters[$token->getExpression()];
                $requirements = $token->getRequirements();

                if (isset($requirements['wildcard']) && true == $requirements['wildcard']) {
                    if ($value === '*' || $value === 'all') {
                        $graph[] = $value;

                        continue; //next token
                    }
                }

                if (isset($requirements['pattern'])) {
                    $pattern = $requirements['pattern'];

                    if (1 === preg_match("#^$pattern#i", $value)) {
                        $graph[] = $value;
                        continue; //next token
                    } else {
                        throw new InvalidArgumentException(sprintf(
                            'Invalid parameters %s, must match %s',
                            $token->getExpression(),
                            $pattern
                        ));
                    }
                }
            } else {
                $graph[] = $token->getExpression();
            }
        }

        return implode($tokenSeparator, $graph);
    }
}
