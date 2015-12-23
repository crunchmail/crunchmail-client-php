<?php
// Crunchmail configuration
// You can use any Guzzle configuration
// @link http://docs.guzzlephp.org/en/latest/request-options.html
$config = array(
    'base_uri'    => 'https://api.crunchmail.net/v1/',
    'token_uri'   => 'https://api.crunchmail.net/api-token-auth',
    'client_uri'  => '/customers/customer-id/',
    'connect_timeout' => 10,
     // 'debug' => true, // enable debug
    // SSL
    // 'verify'      => 'certificate.pem',
    // for the testing, disable the SSL verification
    // don't do it in production!
    'verify' => false,
    // Edit with your Munch API key
    'auth'        => array( 'api', 'my-super-secret-key' )
);

