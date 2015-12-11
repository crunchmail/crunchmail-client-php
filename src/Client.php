<?php
/**
 * Handle crunchmail REST API in php
 *
 * Raw Usage (guzzle client):
 *
 * $Client = new Client($apiConfig);
 * $object = $Client->get($url_ressource);
 * $result = $Client->delete($url_ressource);
 *
 * @license MIT
 * @copyright (C) 2015 Oasiswork
 * @author Yannick Huerre <dev@sheoak.fr>
 *
 * @link https://github.com/crunchmail/crunchmail-client-php
 * @link http://docs.guzzlephp.org/en/latest/
 *
 * @todo check $message->bounces (bounce resource)
 * @todo check $message->spam (spam resource)
 * @todo check $message->stats (stat resource)
 * @todo implements $message->archive (archive_url resource)
 * @todo implements forbidden resources list for entities
 * @todo implements content-type results : html, txt ($message->toHtml())
 */

namespace Crunchmail;

/**
 * Crunchmail\Client main class
 */
class Client extends \GuzzleHttp\Client
{
    /**
     * Allowed paths and mapping to api resource path
     * (only for api root)
     *
     * @var array
     */
    public static $paths = [
        'messages'    => 'messages',
        "customers"   => 'customers',
        'domains'     => 'domains',
        "categories"  => 'categories',
        'recipients'  => 'mails',
        "bounces"     => 'bounces',
        'attachments' => 'attachments',
        "optouts"     => 'opt-outs',
        "users"       => 'users'
    ];

    /**
     * Plural / Singular names of entites
     *
     * @var array
     */
    public static $entities = [
        'domains'     => 'domain',
        'messages'    => 'message',
        'recipients'  => 'recipient',
        'attachments' => 'attachment',
        "customers"   => 'customer',
        "categories"  => 'category',
        "bounces"     => 'bounce',
        "users"       => 'user',
        'preview'     => 'preview'
    ];

    /**
     * List of authorized methods
     * @var array
     */
    public static $methods = [
        'get',
        'delete',
        'head',
        'options',
        'patch',
        'post',
        'put'
        //'request' // request is disable for now, not implemented
    ];

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

        return parent::__construct($config);
    }

    /**
     * Create a resource when accessing client properties like:
     * $client->messages
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (!in_array($name, array_keys(self::$paths)))
        {
            throw new \RuntimeException('Unknow path: ' . $name);
        }

        return $this->createResource($name);

    }

    /**
     * Create a resource depending on name
     *
     * If a specific class exists for this type of ressource (ie:
     * attachmentResource) then it will be used.
     *
     * @param string $name   name of the resource (ie: attachments)
     * @param string $url    force an url for the resource
     * @param mixed  $parent parent entity, if url is specified
     */
    public function createResource($name, $url='',
        \Crunchmail\Entities\GenericEntity $parent=null)
    {
        $className = '\\Crunchmail\\Resources\\' . ucfirst($name) . 'Resource';

        if (!class_exists($className))
        {
            $className = '\\Crunchmail\\Resources\\GenericResource';
        }

        return new $className($this, $name, $url, $parent);
    }

    /**
     * Request the API with the given method and params
     *
     * @param string  $method    method to test
     * @param string  $url       url id
     * @param array   $values    data
     * @param boolean $multipart send as multipart/form-data
     * @return stdClass
     */
    public function apiRequest($method, $url='', $values=[], $filters=[],
        $multipart=false)
    {
        try
        {
            $format = $multipart ? 'multipart' : 'json';

            $parse = parse_url($url);

            // if url contains a query string, we have to merge it to avoid
            // any conflict with filters
            if (isset($parse['query']))
            {
                $query = $parse['query'];
                parse_str($query, $output);
                $filters = array_merge($filters, $output);
            }

            $result = $this->$method($url, [
                $format => $values,
                'query' => $filters
            ]);
        }
        catch (\Exception $e)
        {
            $this->catchGuzzleException($e);
        }

        //var_dump($result->getHeaders());

        //echo "\n\n" . $result->getBody() . "\n\n";
        return json_decode($result->getBody());
    }

    /**
     * Catch all guzzle exception types and execute proper action
     *
     * @param mixed $e
     */
    protected function catchGuzzleException($e)
    {
        // not a guzzle exception
        if (strpos(get_class($e), 'GuzzleHttp\\') !== 0)
        {
            throw $e;
        }

        // guzzle exceptions
        throw new Exception\ApiException($e->getMessage(), $e->getCode(), $e);
    }
}

