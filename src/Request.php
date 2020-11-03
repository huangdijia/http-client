<?php

namespace Huangdijia\Http;

class Request
{
    /**
     * @var string
     */
    protected $baseUrl;
    /**
     * @var string
     */
    protected $bodyFormat;
    /**
     * @var array
     */
    protected $options = [];
    /**
     * @var int
     */
    protected $tries;
    /**
     * @var int
     */
    protected $retryDelay;

    public function __construct()
    {
        $this->ch = curl_init();
    }

    /**
     * Set the base URL for the pending request.
     *
     * @param  string  $url
     * @return $this
     */
    public function baseUrl(string $url)
    {
        return tap($this, function () use ($url) {
            $this->baseUrl = $url;
        });
    }

    /**
     * Specify the body format of the request.
     *
     * @param  string  $format
     * @return $this
     */
    public function bodyFormat(string $format)
    {
        return tap($this, function () use ($format) {
            $this->bodyFormat = $format;
        });
    }

    /**
     * Specify the request's content type.
     *
     * @param  string  $contentType
     * @return $this
     */
    public function contentType(string $contentType)
    {
        return $this->withHeaders(['Content-Type' => $contentType]);
    }

    /**
     * Add the given headers to the request.
     *
     * @param  array  $headers
     * @return $this
     */
    public function withHeaders(array $headers)
    {
        return tap($this, function () use ($headers) {
            $this->options[CURLOPT_HEADER] = ($this->options[CURLOPT_HEADER] ?? []) + $headers;
        });
    }

    /**
     * Specify the basic authentication username and password for the request.
     *
     * @param  string  $username
     * @param  string  $password
     * @return $this
     */
    public function withBasicAuth(string $username, string $password)
    {
        return tap($this, function () use ($username, $password) {
            $this->options[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
            $this->options[CURLOPT_USERPWD]  = "{$username}:{$password}";
        });
    }

    /**
     * Specify the digest authentication username and password for the request.
     *
     * @param  string  $username
     * @param  string  $password
     * @return $this
     */
    public function withDigestAuth($username, $password)
    {
        return tap($this, function () use ($username, $password) {
            return tap($this, function () use ($username, $password) {
                $this->options[CURLOPT_HTTPAUTH] = CURLAUTH_DIGEST;
                $this->options[CURLOPT_USERPWD]  = "{$username}:{$password}";
            });
        });
    }

    /**
     * Specify an authorization token for the request.
     *
     * @param  string  $token
     * @param  string  $type
     * @return $this
     */
    public function withToken($token, $type = 'Bearer')
    {
        return tap($this, function () use ($type, $token) {
            $this->withHeaders(['Authorization' => trim($type . ' ' . $token)]);
        });
    }

    /**
     * Specify the user agent for the request.
     *
     * @param  string  $userAgent
     * @return $this
     */
    public function withUserAgent($userAgent)
    {
        return $this->withHeaders(['User-Agent' => $userAgent]);
    }

    /**
     * Specify the cookies that should be included with the request.
     *
     * @param  array  $cookies
     * @param  string  $domain
     * @return $this
     */
    public function withCookies(array $cookies, string $domain)
    {
        return tap($this, function () use ($cookies, $domain) {
            $this->options[CURLOPT_COOKIE] = value(function () use ($cookies) {
                $cookieJar = [];

                foreach ($cookies as $key => $value) {
                    $cookieJar[] = sprintf('%s=%s', $key, $value);
                }

                return implode('; ', $cookieJar);
            });
        });
    }

    /**
     * Indicate that TLS certificates should not be verified.
     *
     * @return $this
     */
    public function withoutVerifying()
    {
        return tap($this, function () {
            $this->options[CURLOPT_SSL_VERIFYHOST] = 0;
            $this->options[CURLOPT_SSL_VERIFYPEER] = false;
        });
    }

    /**
     * @param int $seconds
     * @return $this
     */
    public function timeout(int $seconds)
    {
        return tap($this, function () use ($seconds) {
            $this->options[CURLOPT_TIMEOUT] = $seconds;
        });
    }

    /**
     * Specify the number of times the request should be attempted.
     *
     * @param  int  $times
     * @param  int  $sleep
     * @return $this
     */
    public function retry(int $times, int $sleep = 0)
    {
        return tap($this, function () use ($times, $sleep) {
            $this->tries      = $times;
            $this->retryDelay = $sleep;
        });
    }

    /**
     * Merge new options into the client.
     *
     * @param  array  $options
     * @return $this
     */
    public function withOptions(array $options)
    {
        return tap($this, function () use ($options) {
            $this->options += $options;
        });
    }

    /**
     * @param string $contentType
     * @return $this
     */
    public function accept($contentType)
    {
        return $this->withHeaders(['Accept' => $contentType]);
    }

    /**
     * Indicate that JSON should be returned by the server.
     *
     * @return $this
     */
    public function acceptJson()
    {
        return $this->accept('application/json');
    }

    /**
     * Indicate the request contains JSON.
     *
     * @return $this
     */
    public function asJson()
    {
        return $this->bodyFormat('json')->contentType('application/json');
    }

    /**
     * Indicate the request contains form parameters.
     *
     * @return $this
     */
    public function asForm()
    {
        return $this->bodyFormat('form_params')->contentType('application/x-www-form-urlencoded');
    }

    /**
     * @param string $url
     * @param array $query
     * @return Response
     */
    public function get(string $url, array $query = [])
    {
        return $this->send('GET', $this->buildUrl($url), [
            CURLOPT_HTTPGET => true,
        ]);
    }

    /**
     * @param string $url
     * @param array $data
     * @return Response
     */
    public function head(string $url, array $query = [])
    {
        return $this->send('HEAD', $this->buildUrl($url), [
            CURLOPT_NOBODY => true,
        ]);
    }

    /**
     * @param string $url
     * @param array $data
     * @return Response
     */
    public function post(string $url, array $data = [])
    {
        return $this->send('POST', $url, [
            CURLOPT_POST       => true,
            CURLOPT_POSTFIELDS => $this->parseRequestData($data),
        ]);
    }

    /**
     * @param string $url
     * @param array $data
     * @return Response
     */
    public function put(string $url, array $data = [])
    {
        return $this->send('PUT', $url, [
            CURLOPT_POSTFIELDS => $this->parseRequestData($data),
        ]);
    }

    /**
     * @param string $url
     * @param array $data
     * @return Response
     */
    public function delete(string $url, array $data = [])
    {
        return $this->send('DELETE', $url, [
            CURLOPT_POSTFIELDS => $this->parseRequestData($data),
        ]);
    }

    /**
     * @param string $url
     * @param array $data
     * @return Response
     */
    public function patch(string $url, array $data = [])
    {
        return $this->send('PATCH', $url, [
            CURLOPT_POSTFIELDS => $this->parseRequestData($data),
        ]);
    }

    /**
     * @param string $url
     * @param array $data
     * @return Response
     */
    public function options(string $url, array $data = [])
    {
        return $this->send('OPTIONS', $url);
    }

    /**
     * @param string $method
     * @param string $url
     * @return Response
     */
    protected function send(string $method, string $url, array $options = [])
    {
        $this->applyOptions($options);

        $options = $this->options + $options + [
            CURLOPT_URL            => $url,
            CURLOPT_CUSTOMREQUEST  => strtoupper($method),
            CURLOPT_RETURNTRANSFER => true, // false
            CURLOPT_HEADER         => true, // false
            CURLINFO_HEADER_OUT    => true,
            CURLOPT_CONNECTTIMEOUT => 150,
        ];

        // var_dump($options[CURLOPT_POSTFIELDS]);exit;

        foreach ($options as $key => $value) {
            if (!is_int($key)) {
                continue;
            }

            curl_setopt($this->ch, $key, $value);
        }

        return retry($this->tries ?? 1, function () {
            return new Response($this->ch);
        }, $this->retryDelay ?? 100);
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    protected function parseRequestData($data)
    {
        switch ($this->bodyFormat) {
            case 'json':
                return json_encode($data);
            case 'form_params':
                return http_build_query($data);
        }

        if (is_string($data)) {
            parse_str($data, $data);
        }

        return $data;
    }

    /**
     * @param array $options
     * @return void
     */
    protected function applyOptions(array $options = [])
    {
        // headers
        if (isset($this->options[CURLOPT_HEADER])) {
            $headers = [];

            foreach ($this->options[CURLOPT_HEADER] as $key => $value) {
                $headers[] = sprintf(
                    '%s: %s',
                    $key,
                    is_array($value) ? implode(',', $value) : $value
                );
            }

            $this->options[CURLOPT_HTTPHEADER] = $headers;
        }
    }

    /**
     * @param string $url
     * @param array $query
     * @return string
     */
    protected function buildUrl(string $url, array $query = [])
    {
        if ($this->baseUrl) {
            $url = rtrim($this->baseUrl, '/') . '/' . ltrim($url, '/');
        }

        if ($query) {
            $glue = false !== strpos($url, '?') ? '&' : '?';
            $url .= $glue . http_build_query($query);
        }

        return $url;
    }
}
