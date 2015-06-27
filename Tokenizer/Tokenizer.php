<?php

namespace Gos\Bundle\PubSubRouterBundle\Tokenizer;

use Gos\Bundle\PubSubRouterBundle\Router\RouteInterface;

class Tokenizer implements TokenizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function tokenize($stringOrRoute, $separator)
    {
        if ($stringOrRoute instanceof RouteInterface) {
            $pattern = $stringOrRoute->getPattern();
            $requirements = $stringOrRoute->getRequirements();
        } else {
            $pattern = $stringOrRoute;
        }

        if (false === strpos($pattern, $separator)) {
            return [];
        }

        $rawTokens = explode($separator, $pattern);
        $tokens = [];
        $requirementsSeen = [];
        $parametersSeen = [];

        foreach ($rawTokens as $i => $rawToken) {
            $token = new Token();
            $split = str_split($rawToken);
            reset($split);

            if (current($split) === '{' && end($split) === '}') {
                $token->setParameter();
                unset($split[0], $split[count($split)]);
            }

            $token->setExpression(implode($split));

            if ($token->isParameter()) {
                $parametersSeen[] = $token->getExpression();
            }

            if (
                $stringOrRoute instanceof RouteInterface &&
                count($stringOrRoute->getRequirements()) >= 1 &&
                isset($requirements[$token->getExpression()])
            ) {
                $requirementsSeen[] = $token->getExpression();
                $token->setRequirements($requirements[$token->getExpression()]);
            }

            $tokens[$i] = $token;
        }

        return $tokens;
    }
}
