<?php
/**
 * Domains resource
 *
 * @author    Yannick Huerre <dev@sheoak.fr>
 * @copyright 2015 (c) Oasiswork
 * @license   https://opensource.org/licenses/MIT MIT
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
        $list = $this->search($domain)->current();
        return (count($list) > 0 && $list[0]->verify());
    }
}
