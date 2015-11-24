<?php
/**
 * Handle crunchmail REST API in php
 *
 * Usage:
 *
 * $Client = new Client($dbConfig);
 * $object = $Client->retrieve($url_ressource);
 *
 * // not tested:
 * $result = $Client->remove($url_ressource);
 *
 * You can use get/post/put/delete, but in that case you will handle 
 * directly the Guzzle Client, with a more complex format. You should 
 * probably only use the custom crunchmail methods:
 *
 * for get:     retrieve($url)
 * for post:    create($values)
 * for put:     update($url, $values)
 * for delete:  remove($url)   // untested
 *
 * You can use create() on ressources properties and avoid using an url:
 *
 * $Client->messages->create($values)
 * $bool = $Client->domains->verify($myDomain);
 * $Client->mails->push($url, $emails);
 *
 * @author Yannick Huerre <dev@sheoak.fr>
 * @version 0.1.1
 *
 * @link http://docs.guzzlephp.org/en/latest/
 */
namespace Crunchmail;

// Crunchmail client dependencies
use Crunchmail\Exception\ApiException;
use Crunchmail\Exception\ApiMailsException;

// Crunchmail guzzle dependencies
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;

/**
 * Main class
 */
class Client extends \GuzzleHttp\Client
{
    /**
     * Allowed ressources
     * @var array
     */
    private static $ressources = [ 'domains', 'messages', 'mails' ];

    /**
      * Initilialize the client, extends guzzle constructor
      *
      * @param array $config API configuration
      * @return object
     */
    public function __construct(array $config = [])
    {
        if (!isset($config['base_uri']))
        {
            throw new \RuntimeException('base_uri is missing in configuration');
        }

        $this->base_uri = $config['base_uri'];
        return parent::__construct($config);
    }

    /**
     * Extends guzzle call to add optionnal argument
     *
     * @param string $method  method name
     * @param array  $args    args list
     * @return mixed
     */
    public function __call($method, $args)
    {
        // add optionnal url to avoid empty argument
        $args[0] = !isset($args[0]) ? '' : $args[0];
        return parent::__call($method, $args);
    }

    /**
     * Post or put values
     *
     * @param string $method post or put or patch
     * @param array  $values values to post
     * @return object
     */
    public function createOrUpdate($method, $values)
    {
        try
        {
            // post or put
            $result = $this->$method('', [ 'json' => $values ] );
        }
        catch (ClientException $e)
        {
            if ($e->hasResponse())
            {
                $Response = $e->getResponse();
                $body = json_decode($Response->getBody());
                $msg = !empty($body) ? $body : [];

                $out = "";
                foreach ($msg as $k => $v)
                {
                    $out .= '<p>' . $k . ' : ';
                    foreach ($v as $str)
                    {
                        $out .=  $str . "<br>";
                    }
                    $out .= '</p>';
                }

                $msg = $out;
            }

            self::handleGuzzleException($e, $msg);
        }
        catch (RequestException $e)
        {
            self::handleGuzzleException($e);
        }

        return json_decode($result->getBody());
    }

    /**
     * Create a new record
     *
     * @param array $post values
     * @return object
     */
    public function create($post)
    {
        return $this->createOrUpdate('post', $post);
    }

    /**
     * Update existing record
     *
     * @param array $post values
     * @return object
     */
    public function update($post)
    {
        return $this->createOrUpdate('put', $post);
    }

    /**
     * Retrieve a record
     *
     * @param string $url url id
     * @return object
     */
    public function retrieve($url='')
    {
        try
        {
            $result = $this->get($url);
        }
        catch (ClientException $e)
        {
            self::handleGuzzleException($e);
        }
        catch (RequestException $e)
        {
            self::handleGuzzleException($e);
        }

        return json_decode($result->getBody());
    }

    /**
     * Delete a record
     * @todo
     */
    public function remove($post)
    {
        die('Not implemented');
    }

    /**
     * Create an object when accessing a sub-ressource
     *
     * If a specific class exists for this type of ressource (ie: domain)
     * then it will be used instead of crunchmailClient
     *
     * If an object is created, it will be returned
     *
     * @param string $name
     * @return mixed
     *
     * @todo clean me
     * @todo optionnal class creation?
     */
    public function __get($name)
    {
        if (!in_array($name, self::$ressources))
        {
            throw new ApiException('Unknow property: ' . $name);
        }

        // DO NOT use __CLASS__ because of the recursive use
        // (we could be in a subclass already)
        $custom    = __NAMESPACE__ . '\\' . ucfirst($name);

        // add ressources to base_uri
        $config = $this->getConfig();
        $config['base_uri'] = $this->base_uri . $name . '/';

        return new $custom($config);
    }

    /**
     * Return a human readable status from int status
     *
     * @param int $status
     * @return string
     */
    public static function readableMessageStatus($status)
    {
        $match = [

            'message_ok'      => "En attente d'envoi",
            'message_issues'  => "Le message contient des erreurs",
            'sent'            => "Le message a été envoyé",
            'sending'         => "En cours d'envoi…"
        ];

        return isset($match[$status]) ? $match[$status] : $status;
    }

    /**
     * Simplify the handling of guzzle exceptions
     *
     * @param mixed $e Exception
     * @param string $msg overwrite message, if needed
     */
    protected static function handleGuzzleException($e, $forceMsg=null, $className='ApiException')
    {
        $code = -1;
        $msg = 'Unexpected API exception. (this might be bug).';

        if ($e->hasResponse())
        {
            $Response = $e->getResponse();
            $code = $Response->getStatusCode();
            $body = json_decode($Response->getBody());
            $msg = !empty($body) ? $body : $msg . ' ' . $code . ': ' . $Response->getReasonPhrase();
        }

        $msg = empty($forceMsg) ? $msg : $forceMsg;

        // arguments unpacking is only from PHP 5.6!
        // so we use Reflection class to pass an unknow number of args
        $args = array($msg, $code);
        $funcargs = func_get_args();

        // remove 3 1st arguments, we just want the additionnal ones
        $params = $args + array_splice($funcargs, -3);

        // throw the requested exception
        $r = new \ReflectionClass(__NAMESPACE__ . '\\Exception\\' . $className);
        throw $r->newInstanceArgs($params);
    }
}

