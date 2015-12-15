<?php
namespace Crunchmail\PHPUnit;

class IsGenericCollectionConstraint extends \PHPUnit_Framework_Constraint_IsInstanceOf
{
    public function __construct()
    {
        $name = '\Crunchmail\Collections\GenericCollection';
        parent::__construct($name);
    }
}
