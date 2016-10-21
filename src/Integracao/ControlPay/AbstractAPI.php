<?php
namespace Integracao\ControlPay;

use GuzzleHttp;
use Integracao\ControlPay\Constants\ControlPayParameter;
use Integracao\ControlPay\Impl\KeyQueryStringAuthentication;

/**
 * Class AbstractAPI
 * @package Integracao\NTKPedido
 */
abstract class AbstractAPI
{
    /**
     * @var Client
     */
    protected $_client;

    /**
     * @var Curl
     */
    protected $_httpClient;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $endpoint;

    /**
     * @var GuzzleHttp\Query
     */
    protected $query;

    /**
     * @var array
     */
    protected $headers = [
        'Content-Type' => 'application/json; charset=utf-8',
        'Cache-Control' => 'no-cache',
    ];

    /**
     * AbstractAPI constructor.
     * @param $endpoint
     * @param Client|null $client
     */
    protected function __construct($endpoint, Client $client = null)
    {
        $this->endpoint = $endpoint;
        $this->_client = $client;

        if(is_null($this->_client))
            $this->_client = new Client();

        $this->query = new GuzzleHttp\Query();
        $this->query->setEncodingType(false);


        switch ($this->_client->getParameter(ControlPayParameter::CONTROLPAY_OAUTH_TYPE))
        {
            case KeyQueryStringAuthentication::class:
                $this->query->set('key', $this->_client->getAuthentication());
                break;
            default:
                $this->headers['Authorization'] = $this->_client->getAuthentication();
                break;
        }

        $this->_httpClient = new GuzzleHttp\Client([
            'base_url' => sprintf("%s/%s/",
                $client->getParameter(ControlPayParameter::CONTROLPAY_HOST),
                $this->endpoint
            ),
            'timeout' => $client->getParameter(ControlPayParameter::CONTROLPAY_TIMEOUT),
            'verify' => false,
            'defaults' => [
                'headers' => $this->headers,
                'query' => $this->query
            ]
        ]);
    }

    /**
     * Adiciona parâmetros mantendo os já inicializados como padrão
     *
     * @param array $params
     * @return string
     */
    protected function addQueryAdditionalParameters(array $params)
    {
        $this->query = new GuzzleHttp\Query();

        if(isset($this->_httpClient->getDefaultOption()['query']))
            $this->query = $this->_httpClient->getDefaultOption()['query'];

        foreach ($params as $key => $value)
            $this->query->set($key, $value);

        return $this->query;
    }



}