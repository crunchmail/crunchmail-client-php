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

    protected static $exposeLinks = [];

    /**
     * Links remapping
     *
     * @var array
     */
    protected static $links = [
        'recipients'   => 'mails'
    ];

    /**
     * Resource mapping
     *
     * @var array
     */
    protected static $resources = [];

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
        'preview.txt'
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
     * Generic conversion to string
     *
     * @return string
     */
    public function __toString()
    {
        return isset($this->_body->url) ? $this->url : '';
    }

    /**
     * Return Entity body
     *
     * @return stdClass
     */
    public function getBody()
    {
        if (empty((array) $this->_body))
        {
            return null;
        }

        $copy = clone $this->_body;
        unset($copy->_links);

        foreach (static::$exposeLinks as $key)
        {
            $copy->$key = $this->getLink($key);
        }
        return $copy;
    }

    /**
     * Catch get, post, put… methods
     *
     * @param string $name method name
     * @param array $args arguments
     *
     * @return Crunchmail\Entity\GenericEntity
     *
     * @method mixed get()    get() get entity (refresh)
     * @method mixed delete() delete() delete entity
     * @method mixed post()   post(array $values, string $format='json')   post values
     * @method mixed put()    put(array $values, string $format='json')    put values
     * @method mixed patch()  patch(array $values, string $format='json')  patch values
     */
    public function __call($method, $args)
    {
        if (!isset($this->_body->url))
        {
            throw new \RuntimeException('Entity has no url');
        }

        // allow use of rest actions, if a link is actually an action
        // and not a classic rest resource.
        // ex: $queue->consume() instead of $queue->consume->post()
        if (isset($this->_body->_links) &&
            array_key_exists($method, (array) $this->_body->_links))
        {
            return call_user_func_array([$this->$method, 'post'], $args);
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
        // forbidden resource ie. : $entity->forbidden->post();
        if (in_array($name, self::$blacklistLinks))
        {
            throw new \RuntimeException('Direct access to ' . $name . ' is
                prohibited');
        }

        $body = $this->getBody();

        if (is_null($body))
        {
            throw new \RuntimeException('Entity body is empty');
        }

        // shortcut to body fields, when no resource was found
        // ex: echo $entity->fielname;
        if (property_exists($body, $name))
        {
            return $body->$name;
        }

        // a subresource was found, create and return it
        // assign url to the subresource url (may need mapping)
        if ($url = $this->getLink($name))
        {
            $resourceName = $this->getResourceName($name);
            // save it to $this->$name, no need to create a new one each time
            $this->$name = $this->_resource->client->createResource(
                $resourceName,
                $url
            );
            return $this->$name;
        }

        throw new \RuntimeException('Entity has no resource "' . $name . '"');
    }

    /**
     * Check if the resource name is registered has belonging to a special
     * resource class, ie. 'ContactList'
     *
     * @param string $name resource name
     * @return string
     */
    private function getResourceName($name)
    {
        return isset(static::$resources[$name]) ? static::$resources[$name] : $name;
    }

    /**
     * Allow use of isset on _body fields
     *
     * @param string $key key to check
     *
     * @return boolean
     */
    public function __isset($key)
    {
        return isset($this->_body->$key);
    }

    /**
     * Allow use of isset on _body fields
     *
     * @param string $key key to check
     *
     * @return boolean
     */
    public function __unset($key)
    {
        throw new \RuntimeException('unset() is disabled');
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
            $this->_body->_links->$map->href : null;
    }
}
