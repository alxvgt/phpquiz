<?php

namespace AppBundle\Tokenizer;

class Tokenizer
{
    /**
     * @param string $text
     * @param string $tokenKey
     *
     * @return mixed
     */
    public static function getOuterTokenText(string $text, string $tokenKey)
    {
        $matches = static::findMatches($text, $tokenKey);

        return isset($matches[0]) ? reset($matches[0]) : false;
    }

    /**
     * @param string $text
     * @param string $tokenKey
     *
     * @return mixed
     */
    public static function getTokenText(string $text, string $tokenKey)
    {
        $matches = static::findMatches($text, $tokenKey);

        return isset($matches[1]) ? reset($matches[1]) : false;
    }

    /**
     * @param string $text
     * @param string $tokenKey
     *
     * @return mixed
     */
    private static function findMatches(string $text, string $tokenKey)
    {
        $regex = '/'.preg_quote($tokenKey, '/').'(.+)'.preg_quote($tokenKey, '/').'/smi';
        preg_match_all($regex, $text, $matches);

        return $matches;
    }
}
