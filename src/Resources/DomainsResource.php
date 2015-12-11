<?php
/**
 * Domains resource
 *
 * @license MIT
 * @copyright (C) 2015 Oasis Work
 * @author Yannick Huerre <dev@sheoak.fr>
 */
namespace Crunchmail\Resources;

/**
 * Domains entity class for Crunchmail API
 */
class DomainsResource extends GenericResource
{
    /**
     * Search domain name
     * You can pass an email or just the domain
     *
     * @param string $domain domain to search
     * @return array
     */
    public function search($email)
    {
        // for emails
        $pos = strpos($email, '@');

        // if @ is not found, it is already a domain
        $pos = $pos === false ? 0 : $pos + 1;

        // extract domain from email
        $domain = substr($email, $pos);

        // GET /domains/?name=$domain
        $this->filter(['name' => $domain]);
        return $this->get();
    }

    /**
     * Check if domains is validated (shortcut)
     *
     * @param string $domain domain to verify
     * @return boolean
     */
    public function verify($domain)
    {
        // get a collection of domains and retrieve the array
        $l = $this->search($domain)->current();
        return (count($l) > 0 && $l[0]->verify());
    }
}
