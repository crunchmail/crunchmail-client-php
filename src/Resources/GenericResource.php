<?php
/**
 * Generic resource class
 *
 * @author Yannick Huerre <dev@sheoak.fr>
 * @copyright (C) 2015 Oasiswork
 * @license MIT
 *
 * TODO: description
 */

namespace Crunchmail\Resources;

/**
 * Crunchmail\Client main class
 */
class GenericResource
{
    /**
     * The client object
     * @var Crunchmail\client
     */
    public $client;

    /**
     * Path to resource
     * @var string
     */
    public $path;

    /**
     * Forced url to resource
     * @var string
     */
    public $url;

    /**
     * Parent Entity object, if url has been forced
     * @var mixed
     */
    public $parent;

    /**
     * Applied filters
     * @var array
     */
    private $filters = [];

    /**
     * Instanciate a new resource
     *
     * @param Crunchmail\Client $client Client object
     * @param string            $path resource path
     * @param string            $url forced url
     * @param mixed             $parent parent entity
     */
    public function __construct($Client, $path, $url='', $parent=null)
    {
        $this->client = $Client;
        $this->path   = $path;

        $this->url    = $url;
        $this->parent = $parent;
    }

    /**
     * Return a collection or a resource classname
     *
     * @param boolean $isCollection return a collection
     * @return string
     */
    private function getResultClass($isCollection=true)
    {
        $classPrefix = '\\Crunchmail\\';

        // collection have a "results" field
        if ($isCollection)
        {
            $classPrefix .= 'Collections';
            $classPath    = $this->path;
            $classType    = 'Collection';
        }
        // entities otherwise
        else
        {
            $classPrefix .= 'Entities';
            $classPath    = \Crunchmail\client::$entities[$this->path];
            $classType    = 'Entity';
        }


        $classPrefix .= '\\';
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
    private function prepareUrl($url=null)
    {
        if (!is_null($url) && strpos($url, 'http') !== 0)
        {
            throw new \RuntimeException('Only absolute URI are allowed');
        }

        $result = $this->path . '/';

        if (!is_null($url))
        {
            $result = $url;
        }
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
            $class = $this->getResultClass();
            return new $class($this, $data);
        }
        // entities otherwise
        else
        {
            $class = $this->getResultClass(false);
            return new $class($this->client, $data);
        }
    }

    /**
     * Registers request filters
     *
     * @param array $filters
     */
    public function filter(array $filters)
    {
        $this->filters = $filters;
        return $this;
    }

    /**
     * Execute a client request and return an entity or a collection of
     * entities
     *
     * @param string $method get, post, put…
     * @param string $url forced url
     * @param array $values data
     * @return mixed
     */
    public function request($method, $url=null, $values=[], $multipart=false)
    {
        if (!in_array($method, \Crunchmail\Client::$methods))
        {
            throw new \RuntimeException("Unknow method: $method");
        }

        $url = $this->prepareUrl($url);
        $data = $this->client->apiRequest($method, $url, $values,
            $this->filters, $multipart);
        return $this->dataToObject($data);
    }

    /**
     * Catch get, post, put… methods
     *
     * @param string $name method name
     * @param array $args arguments
     * @return mixed
     */
    public function __call($name, $args)
    {
        if ('get' !== $name)
        {
            array_unshift($args, null);
        }

        // method is the first parameter
        array_unshift($args, $name);

        return call_user_func_array([$this, 'request'], $args);
    }
}
