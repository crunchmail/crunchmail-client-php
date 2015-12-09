<?php
/**
 *
 * @license MIT
 * @copyright (C) 2015 Oasiswork
 * @author Yannick Huerre <dev@sheoak.fr>
 */
namespace Crunchmail\Entities;

/**
 * Crunchmail\Client subclass Messages
 */
class GenericEntity
{
    private $client;
    public  $body;

    private static $links = [
        'messages'     => 'messages',
        'recipients'   => 'mails',
        'domains'      => 'domains',
        'categories'   => 'categories',
        'preview'      => 'preview_send',
        'attachments'  => 'attachments'
    ];

    public function __construct(\Crunchmail\Client $Client, \stdClass $data)
    {
        $this->client = $Client;
        $this->body = $data;
    }

    private function toEntity($result)
    {
        return new self($this->client, json_decode($result->getBody()));
    }

    public function delete()
    {
        $this->client->delete($this->url);
    }

    public function post($values)
    {
        return $this->toEntity($this->client->post($this->url, $values));
    }

    public function patch($values)
    {
        return $this->toEntity($this->client->patch($this->url, $values));
    }

    public function get()
    {
        return $this->toEntity($this->client->get($this->url));
    }

    public function put($values)
    {
        return $this->toEntity($this->client->put($this->url, $values));
    }

    public function __get($name)
    {
        // access to collections
        $map = isset(self::$links[$name]) ? self::$links[$name] : $name;

        echo "map = $map\n";

        if (isset($this->body->_links->$map))
        {
            $url = $this->body->_links->$map->href;
            echo "url = $url\n";
            return new \Crunchmail\ClientPath($this->client, $name, $url);
        }

        // shortcut to body fields
        if (isset($this->body->$name))
        {
            return $this->body->$name;
        }

        throw new Exception('Unknow property: ' . $name);
    }

    public function toObject()
    {
        $result = $this->body;
        //unset($result->_links);
        return $result;
    }
}
