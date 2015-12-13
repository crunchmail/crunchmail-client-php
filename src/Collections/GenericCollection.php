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
      * Initilialize the collection by populating the collection as an array of
      * entities
      *
      * @param GenericResource $resource parent resource
      * @param array $config API configuration
      *
      * @return object
     */
    public function __construct(GenericResource $resource, $data)
    {
        $this->resource = $resource;
        $this->response = $data;

        $class = $this->resource->getEntityClass();

        foreach ($this->response->results as $row)
        {
            // add the new entity to collection
            $this->collection[] = new $class($this->resource, $row);
        }
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
     *
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
