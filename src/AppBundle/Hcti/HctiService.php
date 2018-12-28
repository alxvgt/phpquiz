<?php

namespace AppBundle\Hcti;

use AppBundle\Hcti\Exception\HttpException;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HctiService
{
    const HCTI_API_HOST = 'https://hcti.io';
    const HCTI_API_VERSION = 'v1';
    const HCTI_API_URI_IMAGE = 'image';
    const HCTI_API_URI_PING = 'ping';

    const HCTI_API_BASE_URL = self::HCTI_API_HOST . DIRECTORY_SEPARATOR . self::HCTI_API_VERSION;

    const HCTI_IMAGE_SAVE_PATH = 'web/hcti/images';

    /**
     * @var Client
     */
    private $httpClient;
    /**
     * @var string
     */
    private $apiKey;
    /**
     * @var string
     */
    private $userId;

    /**
     * HctiService constructor.
     * @param Client $httpClient
     * @param string $hctiUserId
     * @param string $hctiApiKey
     */
    public function __construct(Client $httpClient, string $hctiUserId, string $hctiApiKey)
    {
        $this->httpClient = $httpClient;
        $this->userId = $hctiUserId;
        $this->apiKey = $hctiApiKey;

        $fileSystem = new Filesystem();
        $fileSystem->mkdir(static::HCTI_IMAGE_SAVE_PATH);
    }

    /**
     * @param string $imageId
     * @return mixed
     * @throws HttpException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function retrieveImage(string $imageId)
    {
        if (false === strpos($imageId, 'jpeg')
            && false === strpos($imageId, 'png')
            && false === strpos($imageId, 'webp')
        ) {
            $imageId = $imageId . '.jpeg';
        }

        $options = [
            'sink' => static::HCTI_IMAGE_SAVE_PATH . DIRECTORY_SEPARATOR . $imageId
        ];

        return $this->call(Request::METHOD_GET, DIRECTORY_SEPARATOR . static::HCTI_API_URI_IMAGE . DIRECTORY_SEPARATOR . $imageId, $options);
    }

    /**
     * @return mixed
     * @throws HttpException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function ping()
    {
        return $this->call(Request::METHOD_GET, DIRECTORY_SEPARATOR . static::HCTI_API_URI_PING);
    }

    /**
     * @param string $html
     * @param string|null $css
     * @param bool $downloadImage
     * @return mixed
     * @throws HttpException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createImage(string $html, string $css = null, bool $downloadImage = true)
    {
        $options = [
            RequestOptions::FORM_PARAMS => ['html' => $html, 'css' => $css],
        ];

        $result = $this->call(Request::METHOD_POST, DIRECTORY_SEPARATOR . static::HCTI_API_URI_IMAGE, $options);
        if (!$downloadImage) {
            return $result;
        }

        $imageId = $this->getImageIdFromUrl($result['url']);
        return $this->retrieveImage($imageId);
    }

    /**
     * @return array
     */
    private function getAuthenticationHeader()
    {
        return [RequestOptions::AUTH => [$this->userId, $this->apiKey]];
    }

    /**
     * @param $method
     * @param $uri
     * @param array $options
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws HttpException
     */
    private function call($method, $uri, $options = [])
    {
        $response = $this->httpClient->request(
            $method,
            static::HCTI_API_BASE_URL . $uri,
            array_merge($options, $this->getAuthenticationHeader())
        );

        $contentType = $response->getHeader('Content-Type');

        $isImage = !empty(preg_grep('/.*image\/jpeg.*/', $contentType));
        if ($isImage) {
            if (isset($options['sink']) && $response->getStatusCode() == Response::HTTP_OK) {
                return $options['sink'];
            }
            throw new HttpException("Unable to find filename within response wit h content-type image/jpeg");
        }

        $isJson = !empty(preg_grep('/.*application\/json.*/', $contentType));
        if ($isJson) {
            return json_decode($response->getBody()->getContents(), true);
        }

        return $response;
    }

    /**
     * @param $url
     * @return mixed
     */
    private function getImageIdFromUrl($url)
    {
        return str_replace(static::HCTI_API_BASE_URL . DIRECTORY_SEPARATOR . static::HCTI_API_URI_IMAGE . DIRECTORY_SEPARATOR, '', $url);
    }
}
