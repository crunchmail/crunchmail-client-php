<?php
/**
 * Attachments entity
 *
 * @license MIT
 * @copyright (C) 2015 Oasis Work
 * @author Yannick Huerre <dev@sheoak.fr>
 */
namespace Crunchmail\Entities;

/**
 * Attachments entity class
 */
class AttachmentEntity extends \Crunchmail\Entities\GenericEntity
{
    public function __toString()
    {
        return $this->body->file;
    }
}


