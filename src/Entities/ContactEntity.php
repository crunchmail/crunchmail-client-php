<?php
/**
 * Contact entity
 *
 * @author    Yannick Huerre <dev@sheoak.fr>
 * @copyright 2015 (c) Oasiswork
 * @license   https://opensource.org/licenses/MIT MIT
 */
namespace Crunchmail\Entities;

/**
 * Message entity class
 */
class ContactEntity extends \Crunchmail\Entities\GenericEntity
{
    /**
     * To string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * Copy contact to another list
    public function copyTo($list)
    {
        //return $this->_resource->client->contacts->lists->post($list->getBody());
    }
     */
}
