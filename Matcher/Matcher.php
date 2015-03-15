<?php

namespace Gos\Bundle\PubSubRouterBundle\Matcher;

class Matcher implements MatcherInterface
{
    /**
     * @param string $channel
     * @param string $tokenSeparator
     */
    public function match($channel, $tokenSeparator)
    {
        $tokens = $this->tokenize($channel, $tokenSeparator);
    }

    /**
     * @param string $string
     * @param string $separator
     *
     * @return array
     */
    protected function tokenize($string, $separator)
    {
        //Do we really need regex separator support ??
        $isRegexSeparator = preg_match("/^\/.+\/[a-z]*$/i", $separator);

        return $isRegexSeparator
            ? str_split($separator, $string)
            : explode($separator, $string);
    }
}
