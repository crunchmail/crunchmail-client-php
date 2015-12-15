<?php
namespace Crunchmail\PHPUnit;

class IsResourceConstraint extends \PHPUnit_Framework_Constraint_IsInstanceOf
{
    public function __construct($name)
    {
        $name = '\Crunchmail\Resources\\' . $name . 'Resource';
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
