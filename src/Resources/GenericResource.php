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
     * @var Client
     */
    public $client;

    /**
     * Path to resource
     *
     * @var string
     */
    protected $_path;

    /**
     * Forced url to resource
     *
     * @var string
     */
    protected $_url;

    /**
     * Applied filters
     *
     * @var array
     */
    private $_filters = [];

    /**
     * Instanciate a new resource
     *
     * @param Client $client Client object
     * @param string $path resource path
     * @param string $url forced url
     */
    public function __construct(Client $client, $path, $url = '')
    {
        $this->client = $client;
        $this->_path  = $path;
        $this->_url   = empty($url) ? $this->client->mapPath($path) . '/' : $url;
    }

    /**
     * Return resource default path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->_path;
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
        $this->_filters = $filters;
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
        $this->_filters['page'] = (int) $page;
        return $this->get();
    }

    /**
     * Return the class name for collection, depending on resource path
     *
     * @return string
     */
    public function getCollectionClass()
    {
        return $this->getClass($this->_path, 'Collections', 'Collection');
    }

    /**
     * Map the resource name to the entity name by default we remove the
     * trailing "s", but Client::$entities can contains special cases:
     *
     * @return string
     */
    public function getEntityName()
    {
        return !empty(Client::$entities[$this->_path]) ?
            Client::$entities[$this->_path] :
            preg_replace('/(s)$/', '', $this->_path);
    }

    /**
     * Return the class name for the entity, depending on resource path
     *
     * @return string
     */
    public function getEntityClass()
    {
        $path = $this->getEntityName();
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
     * Execute a client request and return an entity or a collection of
     * entities
     *
     * @param string $method get, post, put…
     * @param string $url forced url
     * @param array  $values data
     * @param string $format json or multipart
     *
     * @return mixed
     *
     * message->get(url)
     * message->put($values, 'multipart', url)
     * message->post($values, $options=['url' => force, 'format' => 'json])
     */
    public function request($method, $url = null, $values = [], $format = 'json')
    {
        // forced url, or resource url
        $url = is_null($url) ? $this->_url : $url;

        // guzzle call to the api, including the applied filters
        // for the current collection
        $data = $this->client->apiRequest(
            $method, $url, $values, $this->_filters, $format
        );

        // if the response has a results field, we create a collection
        // otherwise we create an entity
        $method = isset($data->results) ? 'getCollectionClass' : 'getEntityClass';
        $class  = $this->$method();

        return new $class($this, $data);
    }

    /**
     * Call the request() with the given method and arguments
     *
     * @param string $method method name
     * @param array  $args   arguments (values, format)
     * @param string $url    force url
     *
     * @return mixed
     */
    public function callRequest($method, $args, $url = null)
    {
        if (!in_array($method, Client::$methods))
        {
            throw new \RuntimeException("Unknow method: $method");
        }

        // url
        array_unshift($args, $url);

        // method is the first parameter
        array_unshift($args, $method);

        return call_user_func_array([$this, 'request'], $args);
    }

    /**
     * Get method is different that post, patch… because the first parameter
     * is an url (or null)
     *
     * Ex: $message->get($url);
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
     * Change resource content-type and returns it
     *
     * @param string $type Content Type
     *
     * @return GenericResource
     */
    public function contentType($type)
    {
        $that = clone $this;
        $that->client->headers['Content-Type'] = $type;
        return $that;
    }

    /**
     * Change ressource format to multipart and returns it
     *
     * @return GenericResource
     */
    public function multipart()
    {
        $that = clone $this;
        $that->client->format = 'multipart';
        return $that;
    }

    /**
     * Catch post, put… methods but no get
     *
     * Ex: $cli->messages->post($values)
     * Ex: $cli->messages->post($values, 'multipart')
     *
     * @param string $name method name
     * @param array $args arguments
     *
     * @return mixed
     *
     * @method mixed post()  post(array $values, string $format='json')   post values
     * @method mixed put()   put(array $values, string $format='json')    put values
     * @method mixed patch() patch(array $values, string $format='json') patch values
     */
    public function __call($method, $args)
    {
        return $this->callRequest($method, $args);
    }

    /**
     * Create a resource when accessing resource properties and returns it
     *
     * Example:
     * $client->contacts->lists
     *
     * @param string $name property
     *
     * @return Crunchmail\Resources\GenericResource
     */
    public function __get($name)
    {
        $path = $this->_url . $this->client->mapPath($name) . '/';
        return $this->$name = $this->client->createResource($name, $path);
    }
}
