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

/**
 * a generic HTTP client for making API calls
 */
interface HttpClient
{
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
    public function httpGet($path, $queryStringParams = [], $headers = [], $timeout = 45);

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
    public function httpPost($path, $contentType, $queryStringParams = [], $payload = [], $headers = [], $timeout = 45);

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
    public function httpPut($path, $contentType, $queryStringParams = [], $payload = [], $headers = [], $timeout = 45);

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
    public function httpDelete($path, $queryStringParams = [], $headers = [], $timeout = 45);

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
    public function extractResponsePayload($request, $response);
}