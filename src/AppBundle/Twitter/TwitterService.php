<?php

namespace AppBundle\Twitter;

use Abraham\TwitterOAuth\TwitterOAuth;
use AppBundle\Twitter\Exception\TwitterApiError;

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
            $twitterAcessTokenSecret
        );
        $this->client->setDecodeJsonAsArray(true);
    }

    /**
     * @param string $status
     * @param array $mediaIds
     * @return array|object
     * @throws TwitterApiError
     */
    public function postTweet(string $status, array $mediaIds = [])
    {
        $parameters['status'] = $status;

        $mediaIds = array_filter(array_unique($mediaIds));
        $mediaIds = implode(',', $mediaIds);
        if ($mediaIds) {
            $parameters['media_ids'] = $mediaIds;
        }

        $result = $this->client->post("statuses/update", $parameters);
        $this->processError($result);

        return $result;
    }

    /**
     * @param string $mediaPath
     * @return array|object
     * @throws TwitterApiError
     */
    public function uploadMedia(string $mediaPath)
    {
        $result = $this->client->upload('media/upload', ['media' => $mediaPath]);
        $this->processError($result);
        return $result;
    }

    /**
     * @param $result
     * @throws TwitterApiError
     */
    private function processError($result)
    {
        if (isset($result['error'])) {
            throw  new TwitterApiError('Twitter Api Error : ['.$result['code'].'] '.$result['message']);
        }
    }
}
