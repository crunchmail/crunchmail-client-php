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
 *
 * @todo propagation of guzzle exceptions?
 */
namespace Crunchmail;

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
     * @return object
     */
    public function createOrUpdate($method, $values, $url='')
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
     * @return object
     */
    public function create($post, $url='')
    {
        return $this->createOrUpdate('post', $post, $url);
    }

    /**
     * Update existing record
     *
     * @param array $post values
     * @return object
     */
    public function update($post, $url='')
    {
        return $this->createOrUpdate('put', $post, $url);
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
        catch (\Exception $e)
        {
            self::catchGuzzleException($e);
        }

        return json_decode($result->getBody());
    }

    /**
     * Delete a record
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
     *
     * @param object $body Guzzle Response
     */
    protected static function formatResponseOutput($body, $showErrorKey=false)
    {
        // build a string from the complex response
        $out = "";
        foreach ($body as $k => $v)
        {
            // list of error fields with error messages
            if (is_array($v))
            {

                $out .= '<p>';
                if ($showErrorKey)
                {
                   $out .= $k . ' : ';
                }
                foreach ($v as $str)
                {
                    $out .=  $str . "<br>";
                }
                $out .= '</p>';
            }
            // string error
            else
            {
                $out .= '<p>' . $v . '</p>';
            }
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
    public static function getLastError($showErrorKey=false)
    {
        $body = self::getLastErrorObject();

        if (false === $body || is_string($body))
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
        return (empty($e) ? -1 : $e->getCode());
    }

    /**
     * Return the last error object or string
     *
     * @return stdClass
     */
    public static function getLastErrorObject()
    {
        $e = self::$error;

        // in case we have a response, we try to format it as a string
        if (!empty($e) && $e->hasResponse())
        {
            $Response = $e->getResponse();
            $code     = $Response->getStatusCode();
            $body     = json_decode($Response->getBody());

            // if the body is empty, the error is the ReasonPhrase
            if (empty($body))
            {
                $body = $code . ': ' . $Response->getReasonPhrase();
            }

            return $body;
        }

        return false;
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

                self::handleGuzzleException($e);

                break;

            default:

                 throw new \Exception('Unexpected exception!');
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

