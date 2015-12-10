<?php
/**
 * Generic collection for Crunchmail API
 *
 * @license MIT
 * @copyright (C) 2015 Oasis Work
 * @author Yannick Huerre <dev@sheoak.fr>
 *
 * @TODO handle pagination get: $this->messages->getPage(2)
 */
namespace Crunchmail\Collections;

/**
 * Generic collection for Crunchmail API
 */
class GenericCollection
{
    /**
     * Resource that created the collection
     * @var mixed
     */
    private $resource;

    /**
     * Current data, set of Entities
     * @var array
     */
    private $collection = [];

    /**
     * Raw collection
     */
    private $response;

    /**
      * Initilialize the collection
      *
      * @param array $config API configuration
      * @return object
     */
    public function __construct(\Crunchmail\Resources\GenericResource
        $Resource, $data)
    {
        $this->resource = $Resource;
        $this->response = $data;
        $this->setCollection();
    }

    /**
     * Populate the collection as an array of entities
     */
    private function setCollection()
    {
        $map = \Crunchmail\Client::$entities;

        foreach ($this->response->results as $row)
        {
            $class = '';

            if (isset($map[$this->resource->path]))
            {
                $name = $map[$this->resource->path];
                $class = '\\Crunchmail\\Entities\\' . ucfirst($name) . 'Entity';
            }

            if (empty($class) || !class_exists($class))
            {
                $class = '\\Crunchmail\\Entities\\GenericEntity';
            }

            $this->collection[] = new $class($this->resource->client, $row);
        }
    }

    /**
     * Return the number of results
     *
     * @return int
     */
    public function count()
    {
        return (int) $this->response->count;
    }

    /**
     * Return the number of pages
     *
     * @return int
     */
    public function pageCount()
    {
        return (int) $this->response->page_count;
    }

    /**
     * Return the current set of results
     *
     * @return array
     */
    public function current()
    {
        return $this->collection;
    }

    /**
     * Repopulate collection with next results
     *
     * @return mixed
     * @todo
     */
    public function next()
    {
        return $this->resource->get($this->response->next);
    }

    /**
     * Repopulate current collection with previous results
     *
     * @return mixed
     * @todo
     */
    public function previous()
    {
        return $this->resource->get($this->response->previous);
    }

    /**
     * Repopulate current collection with fresh data
     *
     * @return mixed
     * @todo
     */
    public function refresh()
    {
        return $this->resource->get();
    }
}
