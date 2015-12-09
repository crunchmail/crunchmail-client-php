<?php
/**
 * Recipient subclass for Crunchmail API
 *
 * @license MIT
 * @copyright (C) 2015 Oasiswork
 * @author Yannick Huerre <dev@sheoak.fr>
 */
namespace Crunchmail\Resources;

/**
 * Crunchmail\Client subclass Messages
 */
class RecipientResource extends \Crunchmail\Resources\GenericResource
{
    public function __toString()
    {
        return $this->to;
    }
}
