<?php
namespace OutlineApiClient;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use OutlineApiClient\Exceptions\OutlineApiException;

class OutlineApiClient
{
    protected string $serverUrl = '';
    protected array $errorContext = [];

    /**
     * @throws OutlineApiException
     */
    public function __construct($url)
    {
        if (empty($url)) {
            throw new OutlineApiException('Please pass Outline server address');
        }

        $this->serverUrl = $url;
    }

    protected function makeRequestUrl($uri): string
    {
        return $this->serverUrl . $uri;
    }

    /**
     * @throws OutlineApiException
     */
    protected function request($uri, $method = 'GET', $data = []): \Psr\Http\Message\ResponseInterface
    {
        $requestUrl = $this->makeRequestUrl($uri);
        try {

            $client = new Client([
                'verify' => false
            ]);

            $requestData = [];

            if ($method === 'GET') {
                $requestData['query'] = $data;
            } else {
                if (!empty($data)) {
                    $requestData = [
                        RequestOptions::JSON => $data
                    ];
                }
            }

            return $client->request($method, $requestUrl, $requestData);

        } catch (ClientException|GuzzleException $e) {
            throw new OutlineApiException('Error sending request. Detail: ' . $e->getMessage());
        }
    }

    /**
     * @throws OutlineApiException
     */
    public function getKeys()
    {
        $response = $this->request('/access-keys/');

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @throws OutlineApiException
     */
    public function create()
    {
        $response = $this->request('/access-keys/', 'POST');

        if ($response->getStatusCode() === 201) {
            return json_decode($response->getBody()->getContents(), true);
        } else {
            return false;
        }
    }

    /**
     * @throws OutlineApiException
     */
    public function delete($keyId): bool
    {
        $response = $this->request("/access-keys/{$keyId}", 'DELETE');

        return $response->getStatusCode() === 204;
    }

    /**
     * @throws OutlineApiException
     */
    public function metricsTransfer()
    {
        $response = $this->request('/metrics/transfer');

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @throws OutlineApiException
     */
    public function setName($keyId, $name): bool
    {
        $response = $this->request("/access-keys/{$keyId}/name", 'PUT', ['name' => $name]);

        return $response->getStatusCode() === 204;
    }

    /**
     * @throws OutlineApiException
     */
    public function setLimit($keyId, $limit = 0): bool
    {
        $response = $this->request("/access-keys/{$keyId}/data-limit", 'PUT', ['limit' => ['bytes' => $limit]]);

        return $response->getStatusCode() === 204;
    }

    /**
     * @throws OutlineApiException
     */
    public function deleteLimit($keyId): bool
    {
        $response = $this->request("/access-keys/{$keyId}/data-limit", 'DELETE');
        return $response->getStatusCode() === 204;
    }
}
