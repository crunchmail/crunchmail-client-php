<?php
/**
 * Generic entity
 *
 * @author    Yannick Huerre <dev@sheoak.fr>
 * @copyright 2015 (c) Oasiswork
 * @license   https://opensource.org/licenses/MIT MIT
 */
namespace Crunchmail\Entities;

use Crunchmail\Client;
use Crunchmail\Resources\GenericResource;

/**
 * Generic entity class
 */
class GenericEntity
{
    /**
     * Caller resource
     *
     * @var object
     */
    protected $_resource;

    /**
     * Entity body
     *
     * @var stdClass
     */
    protected $_body;

    /**
     * Links remapping
     *
     * @var array
     */
    private static $links = [
        'recipients'   => 'mails'
    ];

    /**
     * Some links could lead to confusion, because they would not return
     * a proper resource: let's blacklist them
     *
     * Untested resources are also here
     *
     * @var array
     */
    private static $blacklistLinks = [
        'preview.html',
        'preview.txt',
        'archive_url',
        'spam_details',
        'opt_outs'
    ];

    /**
     * Create a new entity
     *
     * @param GenericResource $resource caller resource
     * @param stdClass $data entity data
     *
     * @return Crunchmail\Entity\GenericEntity
     */
    public function __construct(GenericResource $resource, $data)
    {
        $this->_resource = $resource;
        $this->_body     = $data;
    }

    /**
     * Return Entity body
     *
     * @return stdClass
     */
    public function getBody()
    {
        $copy = clone $this->_body;
        unset($copy->_links);
        return $copy;
    }

    /**
     * Catch get, post, put… methods
     *
     * @param string $name method name
     * @param array $args arguments
     *
     * @return Crunchmail\Entity\GenericEntity
     */
    public function __call($method, $args)
    {
        if (!isset($this->_body->url))
        {
            throw new \RuntimeException('Entity has no url');
        }
        return $this->_resource->callRequest($method, $args, $this->url);
    }

    /**
     * Access entity or resources with object properties
     *
     * Note that this technic could lead to conflict if a resource and a body
     * field have the same name
     *
     * Ex:
     * echo $message->title
     * $arr = $message->recipients->current();
     *
     * @param string $name resource name
     *
     * @return mixed resource
     */
    public function __get($name)
    {
        // forbidden resource
        if (in_array($name, self::$blacklistLinks))
        {
            throw new \RuntimeException('Direct access to ' . $name . ' is
                prohibited');
        }

        // a subresource was found, create and return it
        if ($url = $this->getLink($name))
        {
            // save it, no need to create a new one each time
            $this->$name = $this->_resource->client->createResource($name, $url);
            return $this->$name;
        }

        // shortcut to body fields, when no resource was found
        if (is_object($this->_body) && property_exists($this->_body, $name))
        {
            return $this->_body->$name;
        }

        throw new \RuntimeException('Entity has no resource "' . $name . '"');
    }

    /**
     * Get the content of the links attribute, mapping the name first
     *
     * @param string $name name of the field
     *
     * @return string
     */
    public function getLink($name)
    {
        // access to collections
        $map = isset(self::$links[$name]) ? self::$links[$name] : $name;

        return isset($this->_body->_links->$map) ?
            $this->_body->_links->$map->href : false;
    }
}
