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
     *
     * @param ContactListEntity $list List where to copy
     *
     * @return ContactEntity
     */
    public function copyTo($list)
    {
        $body = $this->getBody();

        unset($body->url);
        $body->contact_list = $list->url;

        return $this->_resource->client->contacts->post($body);
    }
}
