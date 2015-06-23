<?php

namespace Gos\Bundle\PubSubRouterBundle\Tokenizer;

class Token
{
    /**
     * @var bool
     */
    protected $isParameter;

    /**
     * @var string
     */
    protected $expression;

    /**
     * @var Array
     */
    protected $requirements;

    public function __construct()
    {
        $this->isParameter = false;
        $this->requirements = [];
    }

    /**
     * @param array $data
     *
     * @return Token
     */
    public static function __set_state(array $data)
    {
        $token = new self();

        $token->setParameter($data['isParameter']);
        $token->setRequirements($data['requirements']);
        $token->setExpression($data['expression']);

        return $token;
    }

    /**
     * @param bool $bool
     */
    public function setParameter($bool = true)
    {
        $this->isParameter = $bool;
    }

    /**
     * @return bool
     */
    public function isParameter()
    {
        return $this->isParameter;
    }

    /**
     * @return string
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * @param string $expression
     */
    public function setExpression($expression)
    {
        $this->expression = $expression;
    }

    /**
     * @param array $requirements
     */
    public function setRequirements(Array $requirements)
    {
        $this->requirements = $requirements;
    }

    /**
     * @return Array
     */
    public function getRequirements()
    {
        return $this->requirements;
    }
}
