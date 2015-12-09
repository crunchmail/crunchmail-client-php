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
    public function __construct(\Crunchmail\ClientPath $ClientPath, $result)
    {
        $this->clientPath = $ClientPath;
        $this->guzzleResponse = $result;
        $this->response   = (array) json_decode($result->getBody());

        $this->setCollection();
    }

    /**
     */
    private function setCollection()
    {
        if (!isset(self::$resources[$this->clientPath->path]))
        {
            throw new \Exception('No resource class for ' . $this->path .
                ' collections');
        }

        foreach ($this->response['results'] as $row)
        {
            $name = self::$resources[$this->clientPath->path];
            $class = '\\Crunchmail\\Resources\\' . ucfirst($name) . 'Resource';

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
