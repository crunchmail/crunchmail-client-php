

Client :

__construct
__get
createResource
apiRequest
mapPath
getToken(array $params)
getTokenFromCredentials($login, $pass)
catchGuzzleException($ex)


private toCamelCase


// auto to json / decode :
$this->messages->get();
$this->messages->post($array);

$params = ['content-type' => 'html'];
$this->messages->get($params);
$this->messages->post($array, $params);

// raw
$this->client->get($url);

// json
$params = [
    'url'     => '', // default
    'filters' => $filters,
    'values'  => $values
];
$this->client->apiRequest($method, $params);

