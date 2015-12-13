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
    protected $resource;

    /**
     * Entity body
     *
     * @var stdClass
     */
    protected $body;

    /**
     * Links remapping
     *
     * @var array
     */
    private static $links = [
        'recipients'   => 'mails',
//        'preview'      => 'preview_send'
    ];

    /**
     * Some links could lead to confusion, because they would not return
     * a proper resource: let's blacklist them
     *
     * @var array
     */
    private static $blacklistLinks = [
        'preview.html', 'preview.txt', 'archive_url', 'opt_outs',
        'spam_details'
    ];

    /**
     * Create a new entity
     *
     * @param Crunchmail\Resource\GenericResource $resource caller resource
     * @param stdClass $data entity data
     *
     * @return Crunchmail\Entity\GenericEntity
     */
    public function __construct(GenericResource $resource, $data)
    {
        $this->resource = $resource;
        return $this->body = $data;
    }

    /**
     * Return Entity body
     *
     * @return stdClass
     */
    public function getBody()
    {
        return $this->body;
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
        if (!in_array($method, Client::$methods))
        {
            throw new \RuntimeException("Unknow method: $method");
        }

        // registered url is the first parameter
        array_unshift($args, $this->url);
        array_unshift($args, $method);

        return call_user_func_array([$this->resource, 'request'], $args);
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
        // access to collections
        $map = isset(self::$links[$name]) ? self::$links[$name] : $name;

        // forbidden resource
        if (in_array($map, self::$blacklistLinks))
        {
            throw new \RuntimeException('Direct access to ' . $map . ' is
                prohibited');
        }

        // a subresource was found, create and return it
        if (isset($this->body->_links->$map))
        {
            $url = $this->body->_links->$map->href;
            return $this->resource->client->createResource($name, $url);
        }

        // shortcut to body fields, when no resource was found
        if (property_exists($this->body, $name))
        {
            return $this->body->$name;
        }

        throw new \RuntimeException('Entity has no resource "' . $name . '"');
    }
}
