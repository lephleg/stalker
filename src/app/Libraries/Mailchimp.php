<?php

namespace App\Libraries;

use GuzzleHttp\Client;
use Config;
use \Exception;

/**
 * Class Mailchimp
 * @package App\Libraries
 *
 * Mailchimp API v3.0 Wrapper class
 */
class Mailchimp
{
    /**
     * @var \GuzzleHttp\Client
     */
    private $client;

    /**
     * Mailchimp constructor.
     * @param string apiKey
     * @throws Exception
     */
    public function __construct(string $apiKey = null)
    {
        // if no key supplied, use the configuration set or throw an exception
        $apiKey = $apiKey ? $apiKey : Config::get('services.mailchimp.api_key');
        if (!$apiKey) throw new Exception('Mailchimp API key not found.');

        // validate key format
        if (strpos($apiKey, '-') === false) throw new Exception('Mailchimp API key invalid.');

        // separate key from data center code
        list($key, $dc) = explode('-', $apiKey);

        // use these as predefined configs for every client request
        $config = [
            'base_uri' => "https://$dc.api.mailchimp.com/3.0/",
            'auth' => ['apikey', $key],
        ];

        $this->client = new Client($config);
    }


    /**
     * Perform a Guzzle HTTP request to MailChimp API
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return \Psr\Http\Message\StreamInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request(string $method, string $uri = '', array $options = [])
    {

        $res =  $this->client->request(
            $method,
            $uri,
            $options
        );

        return $res->getBody();
    }

    /**
     * Perform a Guzzle HTTP request to MailChimp API
     * @param string $listId
     * @param array $member
     * @param array $options
     * @return \Psr\Http\Message\StreamInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function subscribe(string $listId, array $member, array $options = [])
    {

        $options = array_merge($member, $options);

        $res =  $this->client->request(
            'POST',
            "lists/{$listId}/members	",
            $options
        );

        return $res->getBody();
    }


}