<?php
/**
 * Domains entity
 *
 * @license MIT
 * @copyright (C) 2015 Oasis Work
 * @author Yannick Huerre <dev@sheoak.fr>
 */
namespace Crunchmail\Entities;

/**
 * Domains entity class for Crunchmail API
 */
class DomainEntity extends GenericEntity
{
    public function __toString()
    {
        return $this->body->name;
    }
}
