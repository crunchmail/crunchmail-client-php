<?php
namespace Crunchmail\PHPUnit;

class IsGenericEntityConstraint extends \PHPUnit_Framework_Constraint_IsInstanceOf
{
    public function __construct()
    {
        $name = '\Crunchmail\Entities\GenericEntity';
        parent::__construct($name);
    }
}
