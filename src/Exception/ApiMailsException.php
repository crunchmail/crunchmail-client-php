<?php

namespace Crunchmail\Exception;

class ApiMailsException extends \Exception
{
    private $invalidEmails = array();

    public function __construct($message, $code = 0, $invalidEmails, Exception $previous = null)
    {
        $this->invalidEmails = $invalidEmails;

        // assurez-vous que tout a été assigné proprement
        parent::__construct($message, $code, $previous);
  }

    // chaîne personnalisée représentant l'objet
    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

    public function getInvalidEmails()
    {
        return $this->invalidEmails;
    }


}
