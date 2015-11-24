<?php

namespace Crunchmail\Exception;

class ApiException extends \Exception
{
    // chaîne personnalisée représentant l'objet
    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

}
