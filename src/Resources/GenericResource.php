<?php
/**
 * Generic resource class
 *
 * @author    Yannick Huerre <dev@sheoak.fr>
 * @copyright 2015 (c) Oasiswork
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace Crunchmail\Resources;

use Crunchmail\Client;

/**
 * Generic resource class
 */
class GenericResource
{
    /**
     * The client object
     *
     * @var Crunchmail\client
     */
    public $client;

    /**
     * Path to resource
     *
     * @var string
     */
    protected $path;

    /**
     * Forced url to resource
     *
     * @var string
     */
    protected $url;

    /**
     * Applied filters
     *
     * @var array
     */
    private $filters = [];

    /**
     * Instanciate a new resource
     *
     * @param Crunchmail\Client               $client Client object
     * @param string                          $path resource path
     * @param string                          $url forced url
     */
    public function __construct($client, $path, $url = '')
    {
        $this->client = $client;
        $this->path   = $path;
        $this->url    = $url;
    }

    /**
     * Return resource default path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Transform url depending on context
     *
     * @param string url
     *
     * @return string
     */
    private function prepareUrl($url = null)
    {
        if (!is_null($url) && strpos($url, 'http') !== 0)
        {
            throw new \RuntimeException('Only absolute URI are allowed');
        }

        // default url is the relative path
        $result = $this->path . '/';

        // url was forced on call
        if (!is_null($url))
        {
            $result = $url;
        }
        // url was predefined on construction as an absolute url
        elseif (!empty($this->url))
        {
            $result = $this->url;
        }

        return $result;
    }

    /**
     * Registers request filters
     *
     * Ex: $client->messages->filter($filter)->get()
     *
     * @param array $filters
     *
     * @return Crunchmail\Resources\GenericResource
     */
    public function filter(array $filters)
    {
        $this->filters = $filters;
        return $this;
    }

    /**
     * Direct acces to a specific page (shortcut)
     *
     * @param int $page page number
     *
     * @return Crunchmail\Collection\GenericCollection
     */
    public function page($page)
    {
        if (!is_numeric($page) || $page < 0)
        {
            throw new \RuntimeException('Invalid page number');
        }

        $this->filters['page'] = (int) $page;
        return $this->get();
    }

    /**
     * Execute a client request and return an entity or a collection of
     * entities
     *
     * @param string $method get, post, put…
     * @param string $url forced url
     * @param array $values data
     *
     * @return mixed
     */
    private function request($method, $url = null, $values = [], $format = 'json')
    {
        // handle different cases with url
        $url = $this->prepareUrl($url);

        // guzzle call to the api, including the applied filters
        // for the current collection
        $data = $this->client->apiRequest(
            $method, $url, $values, $this->filters, $format
        );

        // if the response has a results field, we create a collection
        // otherwise we create an entity
        $method = isset($data->results) ? 'getCollectionClass' : 'getEntityClass';
        $class  = $this->$method();

        return new $class($this, $data);
    }

    /**
     * Return the class name for collection, depending on resource path
     *
     * @return string
     */
    private function getCollectionClass()
    {
        return $this->getClass($this->path, 'Collections', 'Collection');
    }

    /**
     * Return the class name for the entity, depending on resource path
     *
     * @return string
     */
    private function getEntityClass()
    {
        if (empty(Client::$entities[$this->path]))
        {
            throw new \RuntimeException('Unknow entity for  ' . $this->path);
        }

        $path = Client::$entities[$this->path];
        return $this->getClass($path, 'Entities', 'Entity');
    }

    /**
     * Return the class name for the given type, group and suffix
     *
     * @param string $type          class type
     * @param string $group         class group
     * @param string $classSuffix   class suffix
     *
     * @return string
     */
    private function getClass($type, $classGroup, $classSuffix)
    {
        $classPrefix = '\\Crunchmail\\' . $classGroup . '\\';
        $className   = ucfirst($type) . $classSuffix;

        if (!class_exists($classPrefix . $className))
        {
            $className = 'Generic' . $classSuffix;
        }

        return $classPrefix . $className;
    }

    /**
     * Get method is different that post, patch… because the first parameter
     * is an url (or null)
     *
     * @param string $url resource url
     *
     * @return mixed
     */
    public function get($url = null)
    {
        return $this->request('get', $url);
    }

    /**
     * Catch post, put… methods but no get
     *
     * Ex: $this->messages->post($values)
     *
     * @param string $name method name
     * @param array $args arguments
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (!in_array($method, Client::$methods))
        {
            throw new \RuntimeException("Unknow method: $method");
        }

        // null url
        array_unshift($args, null);

        // method is the first parameter
        array_unshift($args, $method);

        return call_user_func_array([$this, 'request'], $args);
    }
}
