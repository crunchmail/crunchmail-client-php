<?php
/**
 * Domains entity
 *
 * @author    Yannick Huerre <dev@sheoak.fr>
 * @copyright 2015 (c) Oasiswork
 * @license   https://opensource.org/licenses/MIT MIT
 */
namespace Crunchmail\Entities;

/**
 * Domains entity class for Crunchmail API
 */
class DomainEntity extends GenericEntity
{
    /**
     * To string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * Check mx status
     *
     * @return boolean
     */
    public function checkMx()
    {
        return ('ok' === $this->mx_status);
    }

    /**
     * Check dkim status
     *
     * @return boolean
     */
    public function checkDkim()
    {
        return ('ok' === $this->dkim_status);
    }

    /**
     * Check both mx and dkim status
     *
     * @return boolean
     */
    public function verify()
    {
        return $this->checkMx() && $this->checkDkim();
    }
}
