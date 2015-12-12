<?php
/**
 * Generic collection for Crunchmail API
 *
 * @author    Yannick Huerre <dev@sheoak.fr>
 * @copyright 2015 (c) Oasiswork
 * @license   https://opensource.org/licenses/MIT MIT
 *
 * @todo accessing directly a page (adding filter page)
 */
namespace Crunchmail\Collections;

use Crunchmail\Resources\GenericResource;
use Crunchmail\Client;

/**
 * Generic collection for Crunchmail API
 */
class GenericCollection
{
    /**
     * Resource that created the collection
     *
     * @var mixed
     */
    private $resource;

    /**
     * Current data, set of Entities
     *
     * @var array
     */
    private $collection = [];

    /**
     * Raw collection
     *
     * @var GuzzleHttp\Psr7\Response
     */
    private $response;

    /**
      * Initilialize the collection
      *
      * @param array $config API configuration
      * @return object
     */
    public function __construct(GenericResource $resource, $data)
    {
        $this->resource = $resource;
        $this->response = $data;
        $this->setCollection();
    }

    /**
     * Returns the raw response
     *
     * @return GuzzleHttp\Psr7\Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Populate the collection as an array of entities
     */
    private function setCollection()
    {
        // mapping collection name to entity name
        $map = Client::$entities;

        foreach ($this->response->results as $row)
        {
            $class = '';

            // this resource has a mapping
            if (isset($map[$this->resource->getPath()]))
            {
                $name = $map[$this->resource->getPath()];
                $class = '\\Crunchmail\\Entities\\' . ucfirst($name) . 'Entity';
            }

            // class as not been found, use generic class
            if (empty($class) || !class_exists($class))
            {
                $class = '\\Crunchmail\\Entities\\GenericEntity';
            }

            // add the new entity to collection
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
     * @return Crunchmail\Collections\GenericCollection
     */
    public function next()
    {
        $this->getAdjacent('next');
    }

    /**
     * Repopulate current collection with previous results
     *
     * @return Crunchmail\Collections\GenericCollection
     */
    public function previous()
    {
        $this->getAdjacent('previous');
    }

    /**
     * Return next or previous page
     *
     * @param string $direction next or previous
     * @return Crunchmail\Collections\GenericCollection
     */
    public function getAdjacent($direction)
    {
        $url = $this->response->$direction;
        return !empty($url) ? $this->resource->get($url) : null;
    }

    /**
     * Repopulate current collection with fresh data
     *
     * @return Crunchmail\Collections\GenericCollection
     */
    public function refresh()
    {
        return $this->resource->get();
    }
}
