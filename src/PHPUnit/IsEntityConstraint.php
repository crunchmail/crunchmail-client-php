<?php
namespace Crunchmail\PHPUnit;

class IsEntityConstraint extends \PHPUnit_Framework_Constraint_IsInstanceOf
{
    public function __construct($name)
    {
        $name = '\Crunchmail\Entities\\' . $name . 'Entity';
        parent::__construct($name);
    }

    /**
     * Make it public
     */
    public function matches($other)
    {
        return parent::matches($other);
    }
}
