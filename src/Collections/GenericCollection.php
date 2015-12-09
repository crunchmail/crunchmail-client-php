<?php
/**
 * Messages collection for Crunchmail API
 *
 * @license MIT
 * @copyright (C) 2015 Oasis Work
 * @author Yannick Huerre <dev@sheoak.fr>
 */
namespace Crunchmail\Collections;

/**
 * Crunchmail\Client subclass Messages
 */
class GenericCollection
{
    private $clientPath;
    private $page = 1;
    private $collection = [];
    private $response;
    private $guzzleResponse;

    private static $resources = [
        'messages'   => 'message',
        'recipients' => 'recipient',
        'domains'    => 'domain',
        'categories' => 'category',
        'preview'    => 'preview'
    ];

    /**
      * Initilialize the collection
      *
      * @param array $config API configuration
      * @return object
     */
    public function __construct(\Crunchmail\ClientPath $ClientPath, $data)
    {
        $this->clientPath = $ClientPath;
        $this->response   = $data;
        $this->setCollection();
    }

    /**
     */
    private function setCollection()
    {
        foreach ($this->response->results as $row)
        {
            $class = '';

            if (isset(self::$resources[$this->clientPath->path]))
            {
                $name = self::$resources[$this->clientPath->path];
                $class = '\\Crunchmail\\Entities\\' . ucfirst($name) . 'Entity';
            }

            if (empty($class) || !class_exists($class))
            {
                $class = '\\Crunchmail\\Entities\\GenericEntity';
            }

            $this->collection[] = new $class($this->clientPath->client, $row);
        }
    }

    public function count()
    {
        return (int) $this->response->count;
    }

    public function pageCount()
    {
        return (int) $this->response->page_count;
    }

    /**
     * @return GenericCollection
     */
    public function next()
    {
        return $this->clientPath->get($this->response->next);
    }

    public function current()
    {
        return $this->collection;
    }

    /**
     * @return GenericCollection
     */
    public function previous()
    {
        return $this->clientPath->get($this->response->previous);
    }

    public function refresh()
    {
        //return $this->clientPath->get();
    }
}
