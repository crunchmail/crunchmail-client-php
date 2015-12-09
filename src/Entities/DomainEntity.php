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
 *
 * @link https://github.com/crunchmail/crunchmail-client-php
 * @link http://docs.guzzlephp.org/en/latest/
 */
namespace Crunchmail;

/**
 * Crunchmail\Client subclass Domains
 */
class DomainEntity extends GenericEntity
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
        $list = $this->get('?name=' . $domain);

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
        $l = $this->search($domain);
        return (count($l) > 0 && ('ok' === $l[0]->mx_status && 'ok' === 
            $l[0]->dkim_status));
    }
}
