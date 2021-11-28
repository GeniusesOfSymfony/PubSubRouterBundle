<?php

namespace Gos\Bundle\PubSubRouterBundle\Router;

final class CompiledRoute
{
    /**
     * @param string $staticPrefix The static prefix of the compiled route
     * @param string $regex        The regular expression to use to match this route
     * @param array  $tokens       An array of tokens to use to generate URL for this route
     * @param array  $variables    An array of variables
     */
    public function __construct(
        public readonly string $staticPrefix,
        public readonly string $regex,
        public readonly array $tokens,
        public readonly array $variables
    ) {
    }

    public function __serialize(): array
    {
        return [
            'staticPrefix' => $this->staticPrefix,
            'regex' => $this->regex,
            'tokens' => $this->tokens,
            'variables' => $this->variables,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->staticPrefix = $data['staticPrefix'];
        $this->regex = $data['regex'];
        $this->tokens = $data['tokens'];
        $this->variables = $data['variables'];
    }

    /**
     * @deprecated to be removed in 4.0, read the static prefix from the `$staticPrefix` property instead
     */
    public function getStaticPrefix(): string
    {
        trigger_deprecation('gos/pubsub-router-bundle', '3.0', 'The %s() method is deprecated and will be removed in 4.0. Read the static prefix from the $staticPrefix property instead.', __METHOD__);

        return $this->staticPrefix;
    }

    /**
     * @deprecated to be removed in 4.0, read the regex from the `$regex` property instead
     */
    public function getRegex(): string
    {
        trigger_deprecation('gos/pubsub-router-bundle', '3.0', 'The %s() method is deprecated and will be removed in 4.0. Read the regex from the $regex property instead.', __METHOD__);

        return $this->regex;
    }

    /**
     * @deprecated to be removed in 4.0, read the tokens from the `$tokens` property instead
     */
    public function getTokens(): array
    {
        trigger_deprecation('gos/pubsub-router-bundle', '3.0', 'The %s() method is deprecated and will be removed in 4.0. Read the tokens from the $tokens property instead.', __METHOD__);

        return $this->tokens;
    }

    /**
     * @deprecated to be removed in 4.0, read the variables from the `$variables` property instead
     */
    public function getVariables(): array
    {
        trigger_deprecation('gos/pubsub-router-bundle', '3.0', 'The %s() method is deprecated and will be removed in 4.0. Read the variables from the $variables property instead.', __METHOD__);

        return $this->variables;
    }
}
