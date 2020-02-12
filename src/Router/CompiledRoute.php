<?php

namespace Gos\Bundle\PubSubRouterBundle\Router;

/**
 * @final
 */
class CompiledRoute implements \Serializable
{
    /**
     * @var string
     */
    private $staticPrefix;

    /**
     * @var string
     */
    private $regex;

    /**
     * @var array
     */
    private $tokens = [];

    /**
     * @var array
     */
    private $variables = [];

    /**
     * @param string $staticPrefix The static prefix of the compiled route
     * @param string $regex        The regular expression to use to match this route
     * @param array  $tokens       An array of tokens to use to generate URL for this route
     * @param array  $variables    An array of variables
     */
    public function __construct($staticPrefix, $regex, array $tokens, array $variables)
    {
        $this->staticPrefix = $staticPrefix;
        $this->regex = $regex;
        $this->tokens = $tokens;
        $this->variables = $variables;
    }

    public function serialize()
    {
        return serialize($this->__serialize());
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

    public function unserialize($serialized): void
    {
        $this->__unserialize(unserialize($serialized));
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
