<?php
/**
 * Attachments entity
 *
 * @author    Yannick Huerre <dev@sheoak.fr>
 * @copyright 2015 (c) Oasiswork
 * @license   https://opensource.org/licenses/MIT MIT
 */
namespace Crunchmail\Entities;

/**
 * Attachments entity class
 */
class AttachmentEntity extends \Crunchmail\Entities\GenericEntity
{
    /**
     * To string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->file;
    }
}
