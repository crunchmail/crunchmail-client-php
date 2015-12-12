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
     * Return a collection or a resource class name
     *
     * @param boolean $isCollection return a collection
     * @return string
     *
     * @fixme autoload does not work with use Crunchmail\Path\Class
     */
    private function getResultClass($classPrefix)
    {
        // default: collections
        $classPath    = $this->path;
        $classType    = 'Collection';

        // collection have a "results" field
        if ('Entities' === $classPrefix)
        {
            if (empty(Client::$entities[$this->path]))
            {
                throw new \RuntimeException('Unknow entity for  ' .
                    $this->path);
            }

            $classPath    = Client::$entities[$this->path];
            $classType    = 'Entity';
        }

        $classPrefix  = '\\Crunchmail\\' . $classPrefix . '\\';
        $className     = ucfirst($classPath) . $classType;

        if (!class_exists($classPrefix . $className))
        {
            $className = 'Generic' . $classType;
        }

        return $classPrefix . $className;
    }

    /**
     * Transform url depending on context
     *
     * @param string url
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
     * Transform data into entity, depending on resource type
     *
     * @param stdClass $data
     * @return mixed
     */
    private function dataToObject($data)
    {
        // collection have a "results" field
        if (isset($data->results))
        {
            $class = $this->getResultClass('Collections');
            return new $class($this, $data);
        }

        // entities otherwise
        $class = $this->getResultClass('Entities');
        return new $class($this->client, $data);
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
    public function request($method, $url = null, $values = [], $format = 'json')
    {
        if (!in_array($method, Client::$methods))
        {
            throw new \RuntimeException("Unknow method: $method");
        }

        // handle different cases with url
        $url = $this->prepareUrl($url);

        // guzzle call to the api, including the applied filters
        // for the current collection
        $data = $this->client->apiRequest(
            $method, $url, $values, $this->filters, $format
        );

        // collection of entity or single entity
        return $this->dataToObject($data);
    }

    /**
     * Catch get, post, put… methods
     *
     * @example $this->messages->post($values)
     *
     * @param string $name method name
     * @param array $args arguments
     * @return mixed
     */
    public function __call($name, $args)
    {
        // get first parameter is different (forced url)
        if ('get' !== $name)
        {
            array_unshift($args, null);
        }

        // method is the first parameter
        array_unshift($args, $name);

        return call_user_func_array([$this, 'request'], $args);
    }
}
