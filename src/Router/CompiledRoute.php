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

    public function getStaticPrefix(): string
    {
        return $this->staticPrefix;
    }

    public function getRegex(): string
    {
        return $this->regex;
    }

    public function getTokens(): array
    {
        return $this->tokens;
    }

    public function getVariables(): array
    {
        return $this->variables;
    }
}
