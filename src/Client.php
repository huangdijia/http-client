<?php

namespace Huangdijia\Http;

/**
 * @method static self baseUrl(string $url)
 * @method static self bodyFormat(string $format)
 * @method static self contentType(string $contentType)
 * @method static self withHeaders(array $headers)
 * @method static self withBasicAuth(string $username, string $password)
 * @method static self withDigestAuth($username, $password)
 * @method static self withToken($token, $type = 'Bearer')
 * @method static self withUserAgent($userAgent)
 * @method static self withCookies(array $cookies, string $domain)
 * @method static self withoutVerifying()
 * @method static self timeout(int $seconds)
 * @method static self retry(int $times, int $sleep = 0)
 * @method static self withOptions(array $options)
 * @method static self accept($contentType)
 * @method static self acceptJson()
 * @method static self asJson()
 * @method static self asForm()
 * @method static Huangdijia\Http\Response get(string $url, array $query = [])
 * @method static Huangdijia\Http\Response post(string $url, array $data = [])
 * @method static Huangdijia\Http\Response put(string $url, array $data = [])
 * @method static Huangdijia\Http\Response delete(string $url, array $data = [])
 * @method static Huangdijia\Http\Response head(string $url, array $data = [])
 * @method static Huangdijia\Http\Response patch(string $url, array $data = [])
 * @method static Huangdijia\Http\Response options(string $url, array $data = [])
 */
class Client
{
    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array([self::client(), $name], $arguments);
    }

    /**
     * @return Request 
     */
    public static function client()
    {
        return new Request();
    }
}
