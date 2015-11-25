<?php
/**
 * Handle crunchmail REST API in php
 *
 * Usage:
 *
 * $Client = new Client($dbConfig);
 * $object = $Client->retrieve($url_ressource);
 *
 * $result = $Client->remove($url_ressource);
 *
 * You can use get/post/put/delete, but in that case you will handle
 * directly the Guzzle Client, with a more complex format. You should
 * probably only use the custom crunchmail methods:
 *
 * for get:     retrieve($url)
 * for post:    create($values)
 * for put:     update($url, $values)
 * for delete:  remove($url)
 *
 * You can use create() on ressources properties and avoid using an url:
 *
 * $Client->messages->create($values)
 * $bool = $Client->domains->verify($myDomain);
 * $Client->mails->push($url, $emails);
 *
 * @license MIT
 * @copyright (C) 2015 Oasis Work
 * @author Yannick Huerre <dev@sheoak.fr>
 *
 * @link https://github.com/crunchmail/crunchmail-client-php
 * @link http://docs.guzzlephp.org/en/latest/
 *
 * @todo propagation of guzzle exceptions?
 */

namespace Crunchmail;

/**
 * Crunchmail\Client main class
 */
class Client extends \GuzzleHttp\Client
{
    /**
     * Allowed ressources
     * @var array
     */
    private static $ressources = [ 'domains', 'messages', 'mails',
        'attachments' ];

    /**
     * Last exception object
     * @var Mixed
     */
    private static $error = null;

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
     * Create an object when accessing a sub-ressource
     *
     * If a specific class exists for this type of ressource (ie: domain)
     * then it will be used instead of crunchmailClient
     *
     * If an object is created, it will be returned
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (!in_array($name, self::$ressources))
        {
            throw new \RuntimeException('Unknow property: ' . $name);
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
     * Post or put values
     *
     * @param string $method post or put or patch
     * @param array  $values values to post
     * @param string $url resource id
     * @return stdClass result
     */
    public function createOrUpdate($method, array $values, $url='')
    {
        try
        {
            // post or put
            $result = $this->$method($url, [ 'json' => $values ] );
        }
        catch (\Exception $e)
        {
            self::catchGuzzleException($e);
        }

        return json_decode($result->getBody());
    }

    /**
     * Create a new record
     *
     * @param array $post values
     * @param string $url resource id
     * @return stdClass result
     */
    public function create(array $post, $url='')
    {
        return $this->createOrUpdate('post', $post, $url);
    }

    /**
     * Update existing record
     *
     * @param array $post values
     * @param string $url resource id
     * @return stdClass result
     */
    public function update(array $post, $url='')
    {
        return $this->createOrUpdate('put', $post, $url);
    }

    /**
     * Retrieve a record
     *
     * @param string $url url id
     * @return stdClass result
     */
    public function retrieve($url='')
    {
        try
        {
            $result = $this->get($url);
        }
        catch (\Exception $e)
        {
            self::catchGuzzleException($e);
        }

        return json_decode($result->getBody());
    }

    /**
     * Delete a record
     *
     * @param string $url resource id
     * @return stdClass result
     */
    public function remove($url)
    {
        try
        {
            $result = $this->delete($url);
        }
        catch (\Exception $e)
        {
            self::catchGuzzleException($e);
        }

        return json_decode($result->getBody());
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
     * Format a body response as a unique HTML string
     * This is mainly a debugging function, you should probably generate your
     * own HTML output.
     *
     * @param object $body Guzzle Response
     * @param boolean $showErrorKey show error keys in output
     * @return string
     *
     * @todo add string sanitize
     */
    protected static function formatResponseOutput($body, $showErrorKey=true)
    {
        if (!is_object($body) || get_class($body) !== 'stdClass')
        {
            throw new \RuntimeException('Invalid error format');
        }

        // build a string from the complex response
        $out = "";
        foreach ((array) $body as $k => $v)
        {
            // list of error fields with error messages
            $out .= '<p>';
            if ($showErrorKey)
            {
               $out .= $k . ' : ';
            }
            foreach ($v as $str)
            {
                $out .= htmlentities($str) . "<br>";
            }
            $out .= '</p>';
        }

        $out = empty($out) ? 'Unknow error' : $out;

        return $out;
    }


    /**
     * Return the last error as an html string
     *
     * @param boolean $showErrorKey Show the key of each error
     * @return string
     */
    public static function getLastErrorHTML($showErrorKey=false)
    {
        $body = self::getLastError();

        if (false === $body)
        {
            return $body;
        }

        return self::formatResponseOutput($body, $showErrorKey);
    }

    /**
     * Return the last error code
     *
     * @return int
     */
    public static function getLastErrorCode()
    {
        return (int) (empty(self::$error) ? -1 : self::$error->getCode());
    }

    /**
     * Return the last error object or string
     *
     * @return stdClass
     */
    public static function getLastError()
    {
        $e = self::$error;

        // no exception was registered
        if (empty($e))
        {
            return false;
        }

        // in case we have a response, we try to format it as a string
        if ($e->hasResponse())
        {
            $Response = $e->getResponse();
            $Body     = $Response->getBody();
            $msg      = json_decode($Response->getBody());
        }

        // if body was empty, we need to return the exception message instead
        if (!isset($msg) || count( (array) $msg ) === 0)
        {
            $msg = new \stdClass();
            $msg->error = [$e->getMessage()];
        }

        return $msg;
    }

    /**
     * Catch all guzzle exception types and execute proper action
     *
     * @param mixed $e
     */
    protected static function catchGuzzleException($e)
    {
        switch (get_class($e))
        {
            case 'GuzzleHttp\Exception\RequestException':
            case 'GuzzleHttp\Exception\ClientException':
            case 'GuzzleHttp\Exception\ServerException':
            case 'GuzzleHttp\Exception\ConnectException':

                self::handleGuzzleException($e);

                break;

            default:

                 throw new \Exception('Unexpected exception! ' . get_class($e));
        }
    }

    /**
     * Simplify the handling of guzzle exceptions
     *
     * @param object $e Exception
     */
    protected static function handleGuzzleException($e)
    {
        $code = 500;
        $msg = 'API Request failed';

        // in case we have a response, we try to format it as a string
        if ($e->hasResponse())
        {
            $Response = $e->getResponse();
            $code     = $Response->getStatusCode();
        }

        // save last error
        self::$error = $e;

        throw new Exception\ApiException($msg, $code);
    }
}

