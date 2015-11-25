<?php
/**
 * Exception class for Crunchmail classes
 *
 * @license MIT
 * @copyright (C) 2015 Oasis Work
 * @author Yannick Huerre <dev@sheoak.fr>
 *
 * @link https://github.com/crunchmail/crunchmail-client-php
 */

namespace Crunchmail\Exception;

/**
 * ApiException class
 */
class ApiException extends \Exception
{
    /**
     * Output exception
     */
    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

}
