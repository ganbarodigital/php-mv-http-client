<?php

/**
 * Copyright (c) 2016-present Ganbaro Digital Ltd
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the names of the copyright holders nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category  Libraries
 * @package   HttpClient/Clients
 * @author    Stuart Herbert <stuherbert@ganbarodigital.com>
 * @copyright 2016-present Ganbaro Digital Ltd www.ganbarodigital.com
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://code.ganbarodigital.com/php-mv-http-client
 */

namespace GanbaroDigital\HttpClient\V1\Clients;

use GanbaroDigital\HttpClient\V1\Exceptions\HttpCallFailed;
use GanbaroDigital\HttpClient\V1\Urls\BuildUrl;
use GanbaroDigital\JsonParser\V1\Decoders\DecodeJson;
use Zend\Http\Client;
use Zend\Http\Request;
use Zend\Http\Response;

/**
 * a wrapper around the Zend HTTP client
 */
class ZendHttpClient implements HttpClient
{
    /**
     * where are we making API calls to?
     *
     * @var string
     */
    private $baseUrl;

    /**
     * keep track of the last API request made, in case we need it to help
     * us report a useful error
     *
     * @var Request
     */
    private $lastRequest;

    /**
     * our constructor
     *
     * @param string $baseUrl
     *        the URL where our API server is
     * @param array $proxySettings
     *        a list of config settings required for talking through
     *        any local HTTP proxy
     */
    public function __construct($baseUrl)
    {
        // remember this!
        $this->baseUrl = $baseUrl;
    }

    /**
     * make a HTTP GET request to the API
     *
     * @param  string $path
     *         the application route to call
     * @param  array $queryStringParams
     *         any query string parameters we want to use
     * @param  array $headers
     *         any headers that we need to pass
     * @param  int $timeout
     *         how long before we abandon this request?
     * @return array
     *         the request we built and sent, and the response from the HTTP client
     */
    public function httpGet($path, $queryStringParams = [], $headers = [], $timeout = 45)
    {
        // where are we connecting to?
        $url = BuildUrl::from($this->baseUrl, $path, $queryStringParams);

        // build the HTTP request
        $request = new Request();
        $request->setMethod(Request::METHOD_GET);
        $request->setUri($url);
        if (count($headers) > 0) {
            $request->getHeaders()->addHeaders($headers);
        }

        // make the call
        return $this->httpCall($request, $timeout);
    }

    /**
     * make a HTTP POST request to the API
     *
     * @param  string $path
     *         the application route to call
     * @param  string $contentType
     *         what kind of data are we uploading?
     * @param  array $queryStringParams
     *         any query string parameters we want to use
     * @param  array|string $payload
     *         the data to POST
     * @param  array $headers
     *         any headers that we need to pass
     * @param  int $timeout
     *         how long before we abandon this request?
     * @return array
     *         the request we built and sent, and the response from the HTTP client
     */
    public function httpPost($path, $contentType, $queryStringParams = [], $payload = [], $headers = [], $timeout = 45)
    {
        // where are we connecting to?
        $url = BuildUrl::from($this->baseUrl, $path, $queryStringParams);

        // build the HTTP request
        $request = new Request();
        $request->setMethod(Request::METHOD_POST);
        $request->setUri($url);
        $headers['Content-Type'] = $contentType;
        if (count($headers) > 0) {
            $request->getHeaders()->addHeaders($headers);
        }

        // inject our payload
        if (is_array($payload)) {
            foreach($payload as $key => $value) {
                $request->getPost()->set($key, $value);
            }
        }
        else {
            $request->setContent((string)$payload);
        }

        // make the call
        return $this->httpCall($request, $timeout);
    }

    /**
     * make a HTTP PUT request to the API
     *
     * @param  string $path
     *         the application route to call
     * @param  string $contentType
     *         what kind of data are we uploading?
     * @param  array $queryStringParams
     *         any query string parameters we want to use
     * @param  array|string $payload
     *         the data to PUT
     * @param  array $headers
     *         any headers that we need to pass
     * @param  int $timeout
     *         how long before we abandon this request?
     * @return array
     *         the request we built and sent, and the response from the HTTP client
     */
    public function httpPut($path, $contentType, $queryStringParams = [], $payload = [], $headers = [], $timeout = 45)
    {
        // add in any extra headers that we need
        $contentTypeExtension = '';
        if (!empty($payload)) {
            $contentTypeExtension = '+json';
        }
        $headers['Content-Type'] = $contentType;

        // where are we connecting to?
        $url = BuildUrl::from($this->baseUrl, $path, $queryStringParams);

        // build the HTTP request
        $request = new Request();
        $request->setMethod(Request::METHOD_PUT);
        $request->setUri($url);
        if (count($headers) > 0) {
            $request->getHeaders()->addHeaders($headers);
        }

        // inject our payload
        if (is_array($payload)) {
            foreach($payload as $key => $value) {
                $request->getPost()->set($key, $value);
            }
        }
        else if (is_object($payload)) {
            $request->setContent(json_encode($payload));
        }
        else {
            $request->setContent((string)$payload);
        }

        // make the call
        return $this->httpCall($request, $timeout);
    }

    /**
     * make a HTTP DELETE request to the API
     *
     * @param  string $path
     *         the application route to call
     * @param  array $queryStringParams
     *         any query string parameters we want to use
     * @param  array $headers
     *         any headers that we need to pass
     * @param  int $timeout
     *         how long before we abandon this request?
     * @return array
     *         the request we built and sent, and the response from the HTTP client
     */
    public function httpDelete($path, $queryStringParams = [], $headers = [], $timeout = 45)
    {
        // where are we connecting to?
        $url = BuildUrl::from($this->baseUrl, $path, $queryStringParams);

        // build the HTTP request
        $request = new Request();
        $request->setMethod(Request::METHOD_DELETE);
        $request->setUri($url);
        if (count($headers) > 0) {
            $request->getHeaders()->addHeaders($headers);
        }

        // make the call
        return $this->httpCall($request, $timeout);
    }

    /**
     * make a HTTP call
     *
     * @param  Request $request
     *         the request that has already been prepared
     * @param  integer $timeout
     *         how long before we timeout the request?
     * @return object
     *         what we get back
     */
    private function httpCall(Request $request, $timeout = 45)
    {
        // these are the default options for our client
        $clientOptions = [
            'timeout' => $timeout,
            'adapter' => 'Zend\Http\Client\Adapter\Curl',
        ];
        $clientOptions = $this->buildHttpClientConfig($clientOptions);

        $client = new Client();
        $client->setOptions($clientOptions);

        // set a default header
        if ($request->getHeader('Accept') === false) {
            $request->getHeaders()->addHeaders(['Accept' => 'application/json']);
        }

        // remember the request
        $this->lastRequest = $request;

        // make the call
        return $client->send($request);
    }

    /**
     * convert the HTTP response (which could be of any type) into an array
     * of values
     *
     * @param  mixed $response
     * @return array
     */
    public function extractResponsePayload($response)
    {
        // special case - an error occurred
        if (!$response->isSuccess()) {
            throw HttpCallFailed::newFromVar(
                $response, 'response', [
                    'request' => $this->lastRequest->__toString(),
                    'response' => [
                        'statusCode' => $response->getStatusCode(),
                        'body' => $response->getBody()
                    ]
                ]
            );
        }

        // we should have a payload to decode and return
        if ($response->getStatusCode() === 204) {
            // no content
            return [];
        }

        // turn the payload into an array
        $payload = $response->getBody();
        return DecodeJson::from($payload);
    }
}