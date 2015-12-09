<?php
/**
 *
 * @license MIT
 * @copyright (C) 2015 Oasiswork
 * @author Yannick Huerre <dev@sheoak.fr>
 */
namespace Crunchmail\Resources;

/**
 * Crunchmail\Client subclass Messages
 */
class GenericResource
{
    private $client;
    private $resource;

    private static $links = [
        'messages'   => 'messages',
        'recipients' => 'mails',
        'domains'    => 'domains',
        'categories' => 'categories',
        'preview'    => 'preview_send'
    ];

    public function __construct(\Crunchmail\Client $Client, \stdClass $response)
    {
        $this->client = $Client;
        $this->resource = $response;
    }

    private function toResource($result)
    {
        return new self($this->client, json_decode($result->getBody()));
    }

    public function delete()
    {
        return $this->toResource($this->client->delete($this->url));
    }

    public function post($values)
    {
        return $this->toResource($this->client->post($this->url, $values));
    }

    public function patch($values)
    {
        return $this->toResource($this->client->patch($this->url, $values));
    }

    public function get()
    {
        return $this->toResource($this->client->get($this->url));
    }

    public function put($values)
    {
        return $this->toResource($this->client->put($this->url, $values));
    }

    public function __get($name)
    {
        if (isset($this->resource->$name))
        {
            return $this->resource->$name;
        }

        $map = isset(self::$links[$name]) ? self::$links[$name] : $name;

        if (isset($this->resource->_links->$map))
        {
            $url = $this->resource->_links->$map->href;
            return new \Crunchmail\ClientPath($this->client, $name, $url);
        }

        //throw new Exception('Unknow field name: ' . $name);
    }

    public function toObject()
    {
        $result = $this->resource;
        unset($result->_links);
        return $result;
    }
}
