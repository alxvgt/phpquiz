<?php

namespace AppBundle\Twitter;


use Abraham\TwitterOAuth\TwitterOAuth;

class TwitterService
{

    /**
     * @var TwitterOAuth
     */
    private $client;

    /**
     * TwitterService constructor.
     * @param string $twitterKey
     * @param string $twitterKeySecret
     * @param string $twitterAccessToken
     * @param string $twitterAcessTokenSecret
     */
    public function __construct(string $twitterKey, string $twitterKeySecret, string $twitterAccessToken, string $twitterAcessTokenSecret)
    {
        $this->client = new TwitterOAuth(
            $twitterKey,
            $twitterKeySecret,
            $twitterAccessToken,
            $twitterAcessTokenSecret,
        );
    }

    /**
     * @param string $status
     * @return array|object
     */
    public function postTweet(string $status)
    {
        return $this->client->post("statuses/update", ['status' => $status]);
    }

}