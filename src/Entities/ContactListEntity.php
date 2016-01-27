<?php
/**
 * ContactList entity
 *
 * @author    Yannick Huerre <dev@sheoak.fr>
 * @copyright 2015 (c) Oasiswork
 * @license   https://opensource.org/licenses/MIT MIT
 */
namespace Crunchmail\Entities;

/**
 * Message entity class
 */
class ContactListEntity extends \Crunchmail\Entities\GenericEntity
{
    /**
     * Resource mapping
     *
     * @var array
     */
    protected static $resources = [
        'merge'   => 'ContactList'
    ];

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
     * Merge contact list
     *
     * @param array $list list of urls or entities to merge
     *
     * @return ContactListEntity
     */
    public function merge(array $list)
    {
        $ids = [];

        foreach ($list as $row)
        {
            // array of urls or entities
            $ids[] = is_string($row) ? $row : $row->url;
        }

        return $this->merge->post($ids);
    }

    /**
     * Import CSV
     * By default, keep all fields
     * If fields is specified, only keep those ones
     *
     * @param string $content CSV content
     * @param array  $fields  Keep only thoses fields
     *
     * @return ContactListEntity
     */
    public function import($content, array $fields = null)
    {
        $resource = $this->mails;

        // keep only specified fields
        // ?fields=[a,b,c]
        if (!is_null($fields))
        {
            $fields   = '[' . implode(',', $fields) . ']';
            $resource = $resource->filter(['fields' => $fields]);
        }

        return $resource->post($content);
    }
}
