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

    public function autoComplete($query, $area = null,$city = null,$bangla=null)
    {
        $params = [
            'api_key' => $this->apiKey,
            'q' => $query,
            'area' => $area,
            'city' => $city,
            'bangla' => $bangla
        ];
        $this->addParams($params);
        return $this->get(self::ENDPOINT_SEARCH. "/autocomplete/place");
    }

    public function reverseGeocode($latitude, $longitude, $area = null, $union = null, $pauroshova = null, $sub_district = null, $district = null, $country = null, $division = null, $location_type = null, $address = null, $bangla = null,$post_code = null)
    {
        // Implement reverse geocoding API request
        $params = [
            'api_key' => $this->apiKey,
            'longitude' => $longitude,
            'latitude' => $latitude,
            'district' => $district,
            'post_code' => $post_code,
            'country' => $country,
            'sub_district' => $sub_district,
            'union' => $union,
            'pauroshova' => $pauroshova,
            'location_type' => $location_type,
            'division' => $division,
            'address' => $address,
            'area' => $area,
            'bangla' => $bangla
        ];
        $this->addParams($params);
        return $this->get(self::ENDPOINT_SEARCH. "/reverse/geocode");
    }

    public function nearbyPlaces($latitude, $longitude, $radius, $limit)
    {
        // Implement nearby places API request
        $params = [
            'api_key' => $this->apiKey,
            'longitude' => $longitude,
            'latitude' => $latitude
        ];
        $this->addParams($params);
        return $this->get(self::ENDPOINT_SEARCH. "/nearby/".$radius."/".$limit);
    }

    public function rupantor($query, $thana = null, $district = null, $bangla = null)
    {
        // Implement nearby places API request
        $params = [
            'api_key' => $this->apiKey,
            'q' => $query,
            'district' => $district,
            'thana' => $thana,
            'bangla' => $bangla
        ];
        $this->addParams($params);
        return $this->post(self::ENDPOINT_SEARCH. "/rupantor/geocode");
    }

    public function post($endPoint) {
        if($this->requestAsync === true) {
            $promise = $this->client->postAsync(self::API_URL . $endPoint, $this->headers);
            return (is_callable($this->requestCallback) ? $promise->then($this->requestCallback) : $promise);
        }
        $data = $this->client->post(self::API_URL . $endPoint, ["form_params" => $this->additionalParams, $this->headers]);
        $data = json_decode($data->getBody()->getContents());
        return $data;
    }

    public function put($endPoint) {
        if($this->requestAsync === true) {
            $promise = $this->client->putAsync(self::API_URL . $endPoint, $this->headers);
            return (is_callable($this->requestCallback) ? $promise->then($this->requestCallback) : $promise);
        }
        return $this->client->put(self::API_URL . $endPoint, $this->headers);
    }

    public function get($endPoint) {
        $data = $this->client->get(self::API_URL . $endPoint,["query" => $this->additionalParams, $this->headers]);
        $data = json_decode($data->getBody()->getContents());
        return $data;
    }

    public function delete($endPoint) {
        if($this->requestAsync === true) {
            $promise = $this->client->deleteAsync(self::API_URL . $endPoint, $this->headers);
            return (is_callable($this->requestCallback) ? $promise->then($this->requestCallback) : $promise);
        }
        return $this->client->delete(self::API_URL . $endPoint, $this->headers);
    }
}
