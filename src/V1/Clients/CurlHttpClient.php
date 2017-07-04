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

/**
 * a wrapper around Curl
 */
class CurlHttpClient implements HttpClient
{
    /**
     * where are we making API calls to?
     *
     * @var string
     */
    private $baseUrl;

    /**
     * our reusable curl handle
     *
     * @var resource
     */
    private $curlHandle;

    /**
     * our constructor
     *
     * @param string $baseUrl
     *        the URL where our API server is
     */
    public function __construct($baseUrl)
    {
        // remember this!
        $this->baseUrl = $baseUrl;

        // create our curl handle
        $this->curlHandle = curl_init();
    }

    /**
     * convert the request headers we get into the structure that curl
     * requires
     *
     * @param  array $headers
     *         the request headers to be transformed
     * @return array
     *         the headers that curl will accept
     */
    protected function buildHeaders($headers)
    {
        $retval = [];

        foreach ($headers as $key => $value) {
            $retval[] = "{$key}: {$value}";
        }

        return $retval;
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
        curl_setopt($this->curlHandle, CURLOPT_URL, $url);
        curl_setopt($this->curlHandle, CURLOPT_HTTPGET, true);

        // remember what we're up to
        $request = [
            'url' => $url,
            'method' => 'GET'
        ];

        // make the call
        return $this->httpCall($request, $headers, $timeout);
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

        // what are we sending?
        $headers['Content-Type'] = $contentType;

        // build the HTTP request
        curl_setopt($this->curlHandle, CURLOPT_URL, $url);
        curl_setopt($this->curlHandle, CURLOPT_POST, true);
        curl_setopt($this->curlHandle, CURLOPT_POSTFIELDS, $payload);

        // remember what we're sending
        $request = [
            'url' => $url,
            'method' => 'POST',
            'payload' => $payload
        ];

        // make the call
        return $this->httpCall($request, $headers, $timeout);
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
        // where are we connecting to?
        $url = BuildUrl::from($this->baseUrl, $path, $queryStringParams);

        // what are we sending?
        $headers['Content-Type'] = $contentType;

        // build the HTTP request
        curl_setopt($this->curlHandle, CURLOPT_URL, $url);
        curl_setopt($this->curlHandle, CURLOPT_CUSTOMREQUEST, 'PUT');
        if (!is_array($payload)) {
            $payload = (string)$payload;
        }
        curl_setopt($this->curlHandle, CURLOPT_POSTFIELDS, $payload);

        // remember what we're sending
        $request = [
            'url' => $url,
            'method' => 'PUT',
            'payload' => $payload
        ];

        // make the call
        return $this->httpCall($request, $headers, $timeout);
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
        curl_setopt($this->curlHandle, CURLOPT_URL, $url);
        curl_setopt($this->curlHandle, CURLOPT_CUSTOMREQUEST, 'DELETE');

        // remember what we're sending
        $request = [
            'url' => $url,
            'method' => 'DELETE',
        ];

        // make the call
        return $this->httpCall($request, $headers, $timeout);
    }

    /**
     * make a HTTP call
     *
     * @param  array $request
     *         the request that has already been prepared
     * @param  array $headers
     *         the headers to use in the HTTP call
     * @param  integer $timeout
     *         how long before we timeout the request?
     * @return object
     *         what we get back
     */
    private function httpCall($request, $headers, $timeout = 45)
    {
        // add some useful default headers
        $defaultHeaders = [
            'Accept' => 'application/json',
            'User-Agent' => 'GanbaroDigital/HttpClient/V1/CurlHttpClient',
            'Keep-Alive' => '300',
        ];
        foreach ($defaultHeaders as $key => $value) {
            if (!isset($headers[$key])) {
                $headers[$key] = $value;
            }
        }

        // add them to our request tracker, for posterity!
        $request['headers'] = $headers;

        // make sure we're sending the right verb
        curl_setopt($this->curlHandle, CURLOPT_CUSTOMREQUEST, $request['method']);

        // what are the headers we are going to send?
        $curlHeaders = $this->buildHeaders($headers);
        curl_setopt($this->curlHandle, CURLOPT_HTTPHEADER, $curlHeaders);

        // enforce the timeout
        curl_setopt($this->curlHandle, CURLOPT_TIMEOUT, $timeout);

        // we want to see what comes back
        curl_setopt($this->curlHandle, CURLOPT_HEADER, true);
        curl_setopt($this->curlHandle, CURLOPT_RETURNTRANSFER, true);

        // make the call
        $rawResponse = curl_exec($this->curlHandle);
        if ($rawResponse === false) {
            // something went badly wrong
            throw HttpCallFailed::newFromVar(
                false, 'curl_exec()', [
                    'error' => curl_error($this->curlHandle)
                ]
            );
        }

        // parse the rawResponse *NOW*, whilst the information is still fresh!
        $headerSize = curl_getinfo($this->curlHandle, CURLINFO_HEADER_SIZE);
        $response = [
            'statusCode' => curl_getinfo($this->curlHandle, CURLINFO_HTTP_CODE),
            'rawHeader' => substr($rawResponse, $headerSize),
            'rawResponse' => $rawResponse,
            'body' => substr($rawResponse, $headerSize),
        ];

        // convert the raw header into the array we all know and love
        $headerLines = explode("\r\n", $response['rawHeader']);
        foreach ($headerLines as $headerLine) {
            $separatorLoc = strpos($headerLine, ':');
            $response['headers'][substr($headerLine, 0, $separatorLoc)] = trim(substr($headerLine, $separatorLoc + 1));
        }

        // all done
        return [$request, $response];
    }

    /**
     * extract (and decode) the payload from the HTTP response
     *
     * clients that implement this method *MAY* throw an exception if the
     * request failed
     *
     * @param  mixed $request
     *         the request returned from one of the httpXXX calls above
     * @param  mixed $response
     *         the response returned from one of the httpXXX calls above
     * @return mixed
     */
    public function extractResponsePayload($request, $response)
    {
        // what can we learn from this curl request?
        if ($response['statusCode'] > 399) {
            // special case - an error occurred
            throw HttpCallFailed::newFromVar(
                $response, 'response', [
                    'request' => $request,
                    'response' => [
                        'statusCode' => $response['statusCode'],
                        'body' => $response['body']
                    ]
                ]
            );
        }

        // we should have a payload to decode and return
        if ($response['statusCode'] === 204) {
            // no content
            return [];
        }

        // turn the payload into an array
        return DecodeJson::from($response['body']);
    }
}