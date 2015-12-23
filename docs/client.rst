
=================
Crunchmail Client
=================

Basic usage
===========

Creating a simple client is easy:

.. code-block:: php

    use Crunchmail\Client;

    $config = array(
        'base_uri'    => 'https://api.crunchmail.net/v1/',
        'token_uri'   => 'https://api.crunchmail.net/api-token-auth',
        'auth'        => array( 'api', 'key-supersecret' )
    );
    $client = new Client($config);


The configuration uses the format used by
`Guzzle <https://github.com/guzzle/guzzle>`_, so you can use any of the
parameters that Guzzle offers. Just be careful not to use parameters that would
not be compatible with the Crunchmail API, like 'http_errors'.

Example of Guzzle additionnal parameters:

.. code-block:: php

    $config = array(
        'base_uri'    => 'https://api.crunchmail.net/v1/',
        'token_uri'   => 'https://api.crunchmail.net/api-token-auth',
        'auth'        => array( 'api', 'key-supersecret' ),
        'timeout'     => 2.0,
        // echo a bunch of logs for debug
        'debug'       => true
    );


Certificate file
================

You should never use the Client without the SSL ``verify`` parameter set to
``true`` (default).

You can specify the certificate in the configuration:

.. code-block:: php

    // add the parameter to an existing configuration
    $config['verify'] = '/path/to/certificate.pem';
    $client = new Client($config);


Raw Guzzle request
==================

At one point you may need to directlty request the API, without the abstraction
offered by the client. And it would be a shame to have to use another tool for
that. Hopefully you can request the API via the Guzzle Client registered in the
Client. (in fact the Crunchmail PHP Client extends the Guzzle\Client class).

Be careful, as you will NOT get an entity or a collection, but a raw Guzzle
object. See `Guzzle documentation <http://docs.guzzlephp.org/en/latest/>`_ for
more details about the parameters for each methods.

.. code-block:: php

    $this->client->get('/path/to/the/resource');
    $this->client->post('/path/to/the/resource', $values);
    $this->client->delete('/path/to/the/resource');


Get JWT token from API key
--------------------------

:Method: ``getToken()``
:Summary: Request a new token from the configured gAPI key
:Return: token

.. code-block:: php

    // refresh the message
    $client = new Client($config);
    $token = $client->getTokenFromApiKey('my-api-key');


Get JWT token from credentials
------------------------------

:Method: ``getTokenFromCredentials()``
:Summary: Request a new token from the given credentials
:Parameters:
    - ``String $identifier`` your login
    - ``String $password`` your password
:Return: token

.. code-block:: php

    // refresh the message
    $client = new Client($config);
    $token = $client->getTokenFromCredentials('mylogin', 'mypassword');
