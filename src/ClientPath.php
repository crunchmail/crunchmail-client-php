<?php
/**
 * Short description for ClientPath.php
 *
 * @author Yannick Huerre <dev@sheoak.fr>
 * @copyright (C) 2015 Oasiswork
 * @license MIT
 */

namespace Crunchmail;

/**
 * Crunchmail\Client main class
 */
class ClientPath
{
    public $client;
    public $path;
    public $url;

    private static $catch = ['get', 'delete', 'head', 'options', 'patch',
        'post', 'put', 'request' ];

    public function __construct($Client, $path, $url='')
    {
        $this->client = $Client;
        $this->path   = $path;
        $this->url    = $url;
    }

    /**
     * Return a collection or a resource
     */
    private function handleResult($type, $result)
    {
        if ('get' === $type)
        {
            $class = '\\Crunchmail\\Collections\\' . ucfirst($this->path) .
                'Collection';

            if (!class_exists($class))
            {
                $class = '\\Crunchmail\\Collections\\GenericCollection';
            }

            return new $class($this, $result);
        }
        else
        {
            $class = '\\Crunchmail\\Resources\\' . ucfirst($this->path) .
                'Resource';

            if (!class_exists($class))
            {
                $class = '\\Crunchmail\\Resources\\GenericResource';
            }
            $body = json_decode($result->getBody());
            return new $class($this->client, $body);
        }
    }

    public function __call($name, $args)
    {
        // if the method is found, first parameter is prefix with the
        // registerd path
        if (in_array($name, self::$catch))
        {
            $paramPosition = 0;

            // request has a different first parameters
            if ('request' === $name)
            {
                throw new \Exception('Request method not handled');
            }

            if (!empty($this->url))
            {
                array_unshift($args, $this->url);
            }
            else
            {
                array_unshift($args, $this->path . '/');
            }
        }

        // adding the method as parameter
        array_unshift($args, $name);

        $result = call_user_func_array(array($this->client, 'apiRequest'), $args);

        return $this->handleResult($name, $result);
    }
}
