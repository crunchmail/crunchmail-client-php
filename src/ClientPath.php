<?php
/**
 * Short description for ClientPath.php
 *
 * @author Yannick Huerre <dev@sheoak.fr>
 * @copyright (C) 2015 Oasiswork
 * @license MIT
 *
 * TODO: ClientPath -> GenericResource, PreviewResource
 *
 * $Message->preview->send() : PreviewResource
 * $Message->attachment->upload($file)
 * $Message->domains->verify($x)
 *
 * extends resource :
 *
 * $Message->preview->get() -> forbidden
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

    private static $pathEntities = [
        'categories'  => 'category',
        'messages'    => 'message',
        'domains'     => 'domain',
        'attachments' => 'attachments',
        'recipients'  => 'recipient'
    ];

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
            $classPath    = self::$pathEntities[$this->path];
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

    private function prepareUrl($url=null)
    {
        if (!is_null($url) && strpos($url, 'http') !== 0)
        {
            throw new Exception('Only absolute URI are allowed');
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

    private function toEntity($data)
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

    public function request($method, $url=null, $values=[])
    {
        $url = $this->prepareUrl($url);
        echo "prepared url = $url\n";
        $data = $this->client->apiRequest($method, $url, $values);

        var_dump($data);

        return $this->toEntity($data);
    }

    public function get($url=null)
    {
        echo "get url = $url\n";
        return $this->request('get', $url);
    }

    public function put($values)
    {
        return $this->request('put', null, $values);
    }

    public function patch($values)
    {
        return $this->request('patch', null, $values);
    }

    public function post($values, $multipart=false)
    {
        return $this->request('post', null, $values, $multipart);
    }
}
