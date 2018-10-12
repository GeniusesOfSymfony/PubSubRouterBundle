<?php

namespace Gos\Bundle\PubSubRouterBundle\Router;

class CompiledRoute implements \Serializable
{
    private $staticPrefix;
    private $regex;
    private $tokens;
    private $variables;

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

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(
            [
                'staticPrefix' => $this->staticPrefix,
                'regex' => $this->regex,
                'tokens' => $this->tokens,
                'variables' => $this->variables,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);

        $this->staticPrefix = $data['staticPrefix'];
        $this->regex = $data['regex'];
        $this->tokens = $data['tokens'];
        $this->variables = $data['variables'];
    }

    /**
     * @return string
     */
    public function getStaticPrefix()
    {
        return $this->staticPrefix;
    }

    /**
     * @return string
     */
    public function getRegex()
    {
        return $this->regex;
    }

    /**
     * @return array
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    /**
     * @return array
     */
    public function getVariables()
    {
        return $this->variables;
    }
}
