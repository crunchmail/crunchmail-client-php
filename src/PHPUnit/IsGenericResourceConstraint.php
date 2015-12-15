<?php
namespace Crunchmail\PHPUnit;

class IsGenericResourceConstraint extends \PHPUnit_Framework_Constraint_IsInstanceOf
{
    public function __construct()
    {
        $name = '\Crunchmail\Resources\GenericResource';
        parent::__construct($name);
    }
}
