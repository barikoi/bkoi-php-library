<?php

namespace Barikoi\BarikoiApis;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request as Psr7Request;
use GuzzleHttp\Psr7\Response as Psr7Response;

class BarikoiApiClient
{
    const API_URL = "https://barikoi.xyz/v2/api";
    const ENDPOINT_SEARCH = "/search";

    protected $client;
    protected $headers;
    protected $appId;
    protected $apiKey;
    protected $userAuthKey;
    protected $additionalParams;

    /**
     * @var bool
     */
    public $requestAsync = false;

    /**
     * @var int
     */
    public $maxRetries = 2;

    /**
     * @var int
     */
    public $retryDelay = 500;

    /**
     * @var Callable
     */
    private $requestCallback;

    /**
     * Turn on, turn off async requests
     *
     * @param bool $on
     * @return $this
     */
    public function async($on = true)
    {
        $this->requestAsync = $on;
        return $this;
    }

    /**
     * Callback to execute after OneSignal returns the response
     * @param Callable $requestCallback
     * @return $this
     */
    public function callback(Callable $requestCallback)
    {
        $this->requestCallback = $requestCallback;
        return $this;
    }

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;

        $this->client = new Client();
        $this->headers = ['headers' => []];
        $this->additionalParams = [];
    }

    private function createGuzzleHandler() {
        return tap(HandlerStack::create(new CurlHandler()), function (HandlerStack $handlerStack) {
            $handlerStack->push(Middleware::retry(function ($retries, Psr7Request $request, Psr7Response $response = null, RequestException $exception = null) {
                if ($retries >= $this->maxRetries) {
                    return false;
                }

                if ($exception instanceof ConnectException) {
                    return true;
                }

                if ($response && $response->getStatusCode() >= 500) {
                    return true;
                }

                return false;
            }), $this->retryDelay);
        });
    }

    public function testCredentials() {
        return "API KEY: ".$this->apiKey;
    }

    private function requiresAuth() {
        $this->headers['headers']['Authorization'] = 'Basic '.$this->restApiKey;
    }

    private function requiresUserAuth() {
        $this->headers['headers']['Authorization'] = 'Basic '.$this->userAuthKey;
    }

    private function usesJSON() {
        $this->headers['headers']['Content-Type'] = 'application/json';
    }

    public function addParams($params = [])
    {
        $this->additionalParams = $params;

        return $this;
    }

    public function setParam($key, $value)
    {
        $this->additionalParams[$key] = $value;

        return $this;
    }
    
    public function getGeocode($place_id) {

        return $this->get(self::ENDPOINT_SEARCH."/geocode/".$this->apiKey."/place/".$place_id);
    }

    public function autoComplete($query, $area = null,$city = null,$bangla=null)
    {
        $params = [
            'api_key' => $this->apiKey,
            'q' => $query,
            'area' => $area,
            'city' => $city,
            'bangla' => $bangla
        ];
        $params = array_merge($params, $this->additionalParams);
        return $this->get(self::ENDPOINT_SEARCH. "/autocomplete/place", $params);
    }

    public function reverseGeocode($latitude, $longitude)
    {
        // Implement reverse geocoding API request
    }

    public function nearbyPlaces($latitude, $longitude)
    {
        // Implement nearby places API request
    }

    public function post($endPoint) {
        if($this->requestAsync === true) {
            $promise = $this->client->postAsync(self::API_URL . $endPoint, $this->headers);
            return (is_callable($this->requestCallback) ? $promise->then($this->requestCallback) : $promise);
        }
        return $this->client->post(self::API_URL . $endPoint, $this->headers);
    }

    public function put($endPoint) {
        if($this->requestAsync === true) {
            $promise = $this->client->putAsync(self::API_URL . $endPoint, $this->headers);
            return (is_callable($this->requestCallback) ? $promise->then($this->requestCallback) : $promise);
        }
        return $this->client->put(self::API_URL . $endPoint, $this->headers);
    }

    public function get($endPoint) {
        return $this->client->get(self::API_URL . $endPoint, $this->headers);
    }

    public function delete($endPoint) {
        if($this->requestAsync === true) {
            $promise = $this->client->deleteAsync(self::API_URL . $endPoint, $this->headers);
            return (is_callable($this->requestCallback) ? $promise->then($this->requestCallback) : $promise);
        }
        return $this->client->delete(self::API_URL . $endPoint, $this->headers);
    }
}
