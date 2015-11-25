<?php
/**
 * Domains subclass for Crunchmail API
 *
 * Usage:
 *
 * // check a domain is configured:
 * $boolean = $Client->domains->verify($myDomain);
 *
 * // list domain matching $mySearch:
 * $list    = $Client->domains->search($mySearch);
 *
 * @license MIT
 * @copyright (C) 2015 Oasis Work
 * @author Yannick Huerre <dev@sheoak.fr>
 */
namespace Crunchmail;

class Domains extends Client
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
        $list = $this->retrieve('?name=' . $domain);

        return $list->results;
    }

    /**
     * Check if domains is validated
     *
     * @param string $domain domain to verify
     * @return boolean
     */
    public function verify($domain)
    {
        $list = $this->search($domain);

        if (0 === count($list))
        {
            return false;
        }

        $result = $list[0];

        return ('ok' === $result->mx_status && 'ok' === $result->dkim_status);
    }
}
