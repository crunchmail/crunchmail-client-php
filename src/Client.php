<?php
/**
 * Handle crunchmail REST API in php
 *
 * PHP version 5.5+
 *
 * @author    Yannick Huerre <dev@sheoak.fr>
 * @copyright 2015 (c) Oasiswork
 * @license   https://opensource.org/licenses/MIT MIT
 *
 * @link https://github.com/crunchmail/crunchmail-client-php
 * @link http://docs.guzzlephp.org/en/latest/
 *
 * @todo check $message->bounces (bounce resource)
 * @todo check $message->spam (spam resource)
 * @todo check $message->stats (stat resource)
 * @todo implements $message->archive (archive_url resource)
 * @todo implements forbidden resources list for entities
 */

namespace Crunchmail;

/**
 * Crunchmail\Client main class
 */
class Client extends \GuzzleHttp\Client
{
    /**
     * Allowed paths and mapping to api resource path
     * ex: $client->recipients will access path /mails
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
     * This is used to generate class name that need singular form
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
     * List of authorized methods on client.
     * ex: $client->get($url);
     *
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
      *
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
     * Create a resource when accessing client properties and returns it
     *
     * Example:
     * $client->messages
     * $messageEntity->recipients
     *
     * @param string $name property
     *
     * @return Crunchmail\Resources\GenericResource
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
     * Forcing an url is usefull when creating a sub-resource from an
     * entity object, because the base url is then specific
     *
     * @param string $name name of the resource (ie: attachments)
     * @param string $url  force an url for the resource
     *
     * @return mixed
     */
    public function createResource($name, $url = '')
    {
        // TODO: find a way to make namespace "use" works with this
        $classPrefix = '\\Crunchmail\\Resources\\';
        $className = $classPrefix . ucfirst($name) . 'Resource';

        if (!class_exists($className))
        {
            $className = $classPrefix . 'GenericResource';
        }

        return new $className($this, $name, $url);
    }

    /**
     * Request the API with the given method and params.
     *
     * This will execute a guzzle call and catch any guzzle exception.
     * Default mode is json, but you can specify a multipart format.
     * In that case the values must be in the format expected from guzzle
     *
     * @param string  $method    method to test
     * @param string  $url       url id
     * @param array   $values    data
     * @param array   $filters   filters to apply
     * @param string  $format    change default json format
     *
     * @return stdClass
     *
     * @link http://docs.guzzlephp.org/en/latest/quickstart.html?highlight=multipart#sending-form-files
     * @link http://docs.guzzlephp.org/en/latest/request-options.html?highlight=query#query
     */
    public function apiRequest($method, $url = '', $values = [], $filters = [], $format = 'json')
    {
        $parse = parse_url($url);

        // if url contains a query string, we have to merge it to avoid
        // any conflict with filters
        if (isset($parse['query']))
        {
            $query = $parse['query'];
            parse_str($query, $output);
            $filters = array_merge($filters, $output);
        }

        try
        {
            // making the guzzle call, json or multipart
            $result = $this->$method($url, [
                $format => $values,
                'query' => $filters
            ]);
        }
        catch (\Exception $e)
        {
            $this->catchGuzzleException($e);
        }

        return json_decode($result->getBody());
    }

    /**
     * Catch all guzzle exception types and execute proper action
     *
     * @param mixed $e guzzle exception
     *
     * @return null
     */
    protected function catchGuzzleException($exc)
    {
        // not a guzzle exception
        if (strpos(get_class($exc), 'GuzzleHttp\\') !== 0)
        {
            throw $exc;
        }

        // guzzle exceptions
        throw new Exception\ApiException($exc->getMessage(), $exc->getCode(), $exc);
    }
}
