<?php

/**
 * Copyright Shopgate Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author    Shopgate Inc, 804 Congress Ave, Austin, Texas 78701 <interfaces@shopgate.com>
 * @copyright Shopgate Inc
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace Shopgate\ConnectSdk\Http;

use Exception;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use kamermans\OAuth2\GrantType\ClientCredentials;
use kamermans\OAuth2\OAuth2Middleware;
use kamermans\OAuth2\Persistence\TokenPersistenceInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Shopgate\ConnectSdk\Dto\Async\Factory;
use Shopgate\ConnectSdk\Dto\Base;
use Shopgate\ConnectSdk\Exception\NotFoundException;
use Shopgate\ConnectSdk\Exception\RequestException;
use Shopgate\ConnectSdk\Exception\UnknownException;
use Shopgate\ConnectSdk\Http\Persistence\EncryptedFile;
use Shopgate\ConnectSdk\ShopgateSdk;

class Client implements ClientInterface
{
    /** @var GuzzleClientInterface */
    private $guzzleClient;

    /** @var OAuth2Middleware */
    private $oAuthMiddleware;

    /** @var string */
    private $baseUri;

    /** @var string */
    private $merchantCode;

    /**
     * @param GuzzleClientInterface $guzzleClient
     * @param OAuth2Middleware      $oAuthMiddleware
     * @param string                $baseUri
     * @param string                $merchantCode
     */
    public function __construct(
        GuzzleClientInterface $guzzleClient,
        OAuth2Middleware $oAuthMiddleware,
        $baseUri,
        $merchantCode
    ) {
        $this->guzzleClient    = $guzzleClient;
        $this->oAuthMiddleware = $oAuthMiddleware;
        $this->baseUri         = rtrim($baseUri, '/');
        $this->merchantCode    = $merchantCode;
    }

    /**
     * @param string                         $clientId
     * @param string                         $clientSecret
     * @param string                         $merchantCode
     * @param string                         $baseUri
     * @param string                         $env
     * @param string                         $accessTokenPath
     * @param TokenPersistenceInterface|null $tokenPersistence
     * @param LoggerInterface|null           $logger
     * @return Client
     */
    public static function createInstance(
        $clientId,
        $clientSecret,
        $merchantCode,
        $baseUri = '',
        $env = '',
        $accessTokenPath = '',
        TokenPersistenceInterface $tokenPersistence = null,
        LoggerInterface $logger = null
    ) {
        if (empty($baseUri)) {
            $baseUri = str_replace('{env}', $env,'https://{service}.shopgate{env}.services');
        }

        if (empty($accessTokenPath)) {
            $accessTokenPath = __DIR__ . '/../access_token';
        }

        $reauthClient = new \GuzzleHttp\Client([
            'base_uri' => rtrim(str_replace('{service}', 'auth', $baseUri), '/') . '/oauth/token'
        ]);

        $oauth = new OAuth2Middleware(new ClientCredentials($reauthClient, [
            'client_id'     => $clientId,
            'client_secret' => $clientSecret
        ]));

        if (empty($tokenPersistence)) {
            $tokenPersistence = new EncryptedFile($accessTokenPath, $clientSecret);
        }
        $oauth->setTokenPersistence($tokenPersistence);

        $handlerStack = HandlerStack::create();
        $handlerStack->push($oauth);
        $client = new \GuzzleHttp\Client([
            'auth'    => 'oauth',
            'handler' => $handlerStack
        ]);

        if ($logger) {
            $handlerStack->push(Middleware::log($logger, new MessageFormatter('URL: {hostname}/{target} Method: {method} RequestBody: {req_body} ResponseBody: {res_body}')));
        }

        return new self($client, $oauth, $baseUri, $merchantCode);
    }

    /**
     * @param string $serviceName
     * @param string $path
     * @return string
     */
    public function buildServiceUrl($serviceName, $path = '')
    {
        return str_replace('{service}', $serviceName, $this->baseUri)
            . '/v1'
            . "/merchants/{$this->merchantCode}"
            . '/' . ltrim($path, '/');
    }

    /**
     * @param array $params
     *
     * @return ResponseInterface
     * @throws RequestException
     * @throws NotFoundException
     * @throws UnknownException
     */
    public function doRequest(array $params)
    {
        if (!$this->isDirect($params)) {
            return $this->triggerEvent($params);
        }

        if (isset($params['query']) && isset($params['query']['requestType'])) {
            unset($params['query']['requestType']);
        }

        $response = null;
        $body     = isset($params['body']) ? $params['body'] : [];
        try {
            $response = $this->guzzleClient->request(
                $params['method'],
                $this->buildServiceUrl($params['service'], $params['path']),
                [
                    'query' => isset($params['query'])
                        ? $this->fixBoolValuesInQuery($params['query'])
                        : [],
                    'json'  => $body instanceof Base
                        ? $body->toJson()
                        : (new Base($body))->toJson(),
                ]
            );
        } catch (GuzzleRequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 0;

            if ($statusCode == 404) {
                throw new NotFoundException(
                    $e->getResponse() && $e->getResponse()->getBody() ? $e->getResponse()->getBody()->getContents()
                        : $e->getMessage()
                );
            }

            throw new RequestException(
                $statusCode,
                $e->getResponse() && $e->getResponse()->getBody() ? $e->getResponse()->getBody()->getContents()
                    : $e->getMessage()
            );
        } catch (GuzzleException $e) {
            throw new UnknownException($e->getMessage());
        } catch (Exception $e) {
            throw new UnknownException($e->getMessage());
        }

        return $response;
    }

    /**
     * This method will convert true (bool) values to 'true' (string) and false (bool) to 'false' (string).
     * @param array $queryParameters
     * @return array
     */
    private function fixBoolValuesInQuery($queryParameters)
    {
        foreach ($queryParameters as $queryParameterKey => $queryParameterValue) {
            if (!is_bool($queryParameterValue)) {
                continue;
            }
            $queryParameters[$queryParameterKey] = !empty($queryParameterValue) ? 'true' : 'false';
        }

        return $queryParameters;
    }

    /**
     * @param array $params
     *
     * @return ResponseInterface
     * @throws RequestException
     * @throws UnknownException
     */
    private function triggerEvent(array $params)
    {
        $values = [
            isset($params['body']) ? $params['body'] : new Base(),
        ];
        if ($params['action'] === 'create') {
            $key    = array_keys($params['body'])[0];
            $values = $params['body'][$key];
        }

        $factory = new Factory();
        foreach ($values as $payload) {
            $entityId = isset($params['entityId']) ? $params['entityId'] : null;
            $factory->addEvent($params['action'], $params['entity'], $payload, $entityId);
        }

        try {
            return $this->guzzleClient->request(
                'post',
                $this->buildServiceUrl('omni-event-receiver', 'events'),
                [
                    'json'        => $factory->getRequest()->toJson(),
                    'http_errors' => false,
                ]
            );
        } catch (GuzzleRequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 0;
            throw new RequestException(
                $statusCode,
                $e->getResponse() && $e->getResponse()->getBody() ? $e->getResponse()->getBody()->getContents()
                    : $e->getMessage()
            );
        } catch (GuzzleException $e) {
            throw new UnknownException($e->getMessage());
        } catch (Exception $e) {
            throw new UnknownException($e->getMessage());
        }
    }

    /**
     * @param array $params
     *
     * @return bool
     * @todo-sg: unit tests
     */
    public function isDirect(array $params)
    {
        return
            (!isset($params['requestType']) && $params['method'] === 'get') ||
            $params['requestType'] === ShopgateSdk::REQUEST_TYPE_DIRECT;
    }
}
