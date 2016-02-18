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
     * ex: $client->optouts will access path /opt-outs
     *
     * @var array
     */
    public static $paths = [
        "optouts"     => 'opt-outs'
    ];

    /**
     * Plural / Singular names of entites
     * This is used to generate class name that need singular form
     *
     * @var array
     */
    public static $entities = [
        'categories'   => 'category',
        'preview'      => 'preview',
        'lists'        => 'contactList'
    ];

    /**
     * @var array
     */
    private $config;

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
     * Default request format
     *
     * @param string
     */
    public $format = 'json';

    /**
     * Default headers
     *
     * @param array
     */
    public $headers = [];

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

        if (!isset($config['token_uri']))
        {
            throw new \RuntimeException('token_uri is missing in configuration');
        }

        $this->config = $config;

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
        return $this->$name = $this->createResource($name);
    }

    /**
     * Translate resource to class name, camelcased
     *
     * @param string $str resource name
     *
     * @return string
     */
    private function toCamelCase($str)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $str)));
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
        $camelCase = $this->toCamelCase($name);

        // TODO: find a way to make namespace "use" works with this
        $classPrefix = '\\Crunchmail\\Resources\\';
        $className = $classPrefix . $camelCase . 'Resource';

        if (!class_exists($className))
        {
            $className = $classPrefix . 'GenericResource';
        }

        return new $className($this, $name, $url);
    }

    /**
     * Convert resource path if map is found, path otherwise
     *
     * @param string $path
     *
     * @return string
     */
    public function mapPath($path)
    {
        return isset(self::$paths[$path]) ? self::$paths[$path] : $path;
    }

    /**
     * Request the API with the given method and params.
     *
     * This will execute a guzzle call and catch any guzzle exception.
     * In that case the values must be in the format expected from guzzle
     *
     * @param string  $method    method to test
     * @param string  $url       url id
     * @param array   $values    data
     * @param array   $filters   filters to apply
     *
     * @return stdClass
     *
     * @link http://docs.guzzlephp.org/en/latest/quickstart.html?highlight=multipart#sending-form-files
     * @link http://docs.guzzlephp.org/en/latest/request-options.html?highlight=query#query
     *
     * @todo Refactor to match guzzle format ( request() )
     */
    //public function apiRequest($method, $url = '', $values = [], $filters = [])
    public function apiRequest($method, $url = '', $values = [], $filters = [])
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
            // TODO: merge headers or use guzzle default on client?
            // making the guzzle call, json or multipart
            $result = $this->$method($url, [
                $this->format   => $values,
                'query'         => $filters,
                'headers'       => $this->headers
            ]);
        }
        catch (\Exception $e)
        {
            $this->catchGuzzleException($e);
        }

        // TODO: handle non JSON response
        return json_decode((string) $result->getBody());
    }

    /**
     * Return an auth token from credentials
     *
     * @param string $identifier login
     * @param string $password   password
     * @return string
     */
    public function getTokenFromCredentials($identifier, $password)
    {
        return $this->getToken([
            'identifier' => $identifier,
            'password'   => $password
        ]);
    }

    /**
     * Return an auth token from given parameters
     *
     * @param string $params parameters to post
     * @return string
     */
    public function getToken(array $params = null)
    {
        if (is_null($params))
        {
            if (!isset($this->config['auth']) || count($this->config['auth']) < 2)
            {
                throw new \RuntimeException('auth parameters are missing');
            }

            $params = ['api_key' => $this->config['auth'][1] ];
        }

        $result = $this->apiRequest('post', $this->config['token_uri'], $params);
        return isset($result->token) ? $result->token : null;
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
