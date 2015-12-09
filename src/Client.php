<?php
/**
 * Handle crunchmail REST API in php
 *
 * Usage:
 *
 * $Client = new Client($dbConfig);
 * $object = $Client->retrieve($url_ressource);
 *
 * $result = $Client->remove($url_ressource);
 *
 * You can use get/post/put/delete, but in that case you will handle
 * directly the Guzzle Client, with a more complex format. You should
 * probably only use the custom crunchmail methods:
 *
 * for get:     retrieve($url)
 * for post:    create($values)
 * for put:     update($url, $values)
 * for delete:  remove($url)
 *
 * You can use create() on collections properties and avoid using an url:
 *
 * $Client->messages->create($values)
 * $bool = $Client->domains->verify($myDomain);
 * $Client->mails->push($url, $emails);
 *
 * @license MIT
 * @copyright (C) 2015 Oasiswork
 * @author Yannick Huerre <dev@sheoak.fr>
 *
 * @link https://github.com/crunchmail/crunchmail-client-php
 * @link http://docs.guzzlephp.org/en/latest/
 */

namespace Crunchmail;

/**
 * Crunchmail\Client main class
 */
class Client extends \GuzzleHttp\Client
{
    /**
     * Allowed paths
     * @var array
     */
    private static $paths = [
        'domains'     => 'domains',
        'messages'    => 'messages',
        'recipients'  => 'mails',
        'attachments' => 'attachments'
    ];

    /**
      * Initilialize the client, extends guzzle constructor
      *
      * @param array $config API configuration
      * @return object
     */
    public function __construct(array $config = [])
    {
        if (!isset($config['base_uri']))
        {
            throw new \RuntimeException('base_uri is missing in configuration');
        }

        //$this->base_uri = $config['base_uri'];
        return parent::__construct($config);
    }

    /**
     * Create an object when accessing a sub-ressource
     *
     * If a specific class exists for this type of ressource (ie: domain)
     * then it will be instanciated and stored instead of crunchmailClient
     *
     * If an object is created, it will be returned
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        //echo "Accessing property $name ...\n";
        if (!in_array($name, self::$paths))
        {
            throw new \RuntimeException('Unknow path: ' . $name);
        }
        return new ClientPath($this, self::$paths[$name]);
    }

    /**
     * Request the API with the given method and params
     *
     * @param string $method    method to test
     * @param string $url       url id
     * @param array  $values    data
     * @return stdClass
     */
    public function apiRequest($method, $url='', $values=array())
    {
        try
        {
            $result = $this->$method($url, [ 'json' => $values ] );
        }
        catch (\Exception $e)
        {
            $this->catchGuzzleException($e);
        }

        //echo $result->getBody();
        return $result;
        //return json_decode($result->getBody());
    }

    /**
     * Create a new record
     *
     * @param array $post values
     * @param string $url resource id
     * @return stdClass result
     */
    public function create(array $post, $url='')
    {
        return $this->apiRequest('post', $url, $post);
    }

    /**
     * Update existing record
     *
     * @param array $post values
     * @param string $url resource id
     * @return stdClass result
     */
    public function update(array $post, $url='')
    {
        return $this->apiRequest('put', $url, $post);
    }

    /**
     * Retrieve a record
     *
     * @param string $url url id
     * @return stdClass result
     */
    public function retrieve($url='')
    {
        return $this->apiRequest('get', $url);
    }

    /**
     * Delete a record
     *
     * @param string $url resource id
     * @return stdClass result
     */
    public function remove($url)
    {
        return $this->apiRequest('delete', $url);
    }

    /**
     * Catch all guzzle exception types and execute proper action
     *
     * @param mixed $e
     */
    protected function catchGuzzleException($e)
    {
        // not a guzzle exception
        if (strpos(get_class($e), 'GuzzleHttp\\') !== 0)
        {
            throw $e;
        }

        // guzzle exceptions
        throw new Exception\ApiException($e->getMessage(), $e->getCode(), $e);
    }
}

