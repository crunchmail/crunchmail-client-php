==========
Quickstart
==========

This page will help you quickly understand how the library works with inline
examples.

If you don't have installed the library yet, look at the :ref:`installation`
page.


Making a Request
================

This library helps you send requests to the crunchmail API.


Creating a Client
-----------------

.. code-block:: php

    use Crunchmail\Client;

    $config = array(
        'base_uri'    => 'https://api.crunchmail.net/v1/',
        // for the testing, you can disable the SSL verification
        // don't do it in production!
        'verify' => false,
        // Edit with your Munch API key
        'auth'        => array( 'api', 'key-supersecret' )
    );
    $client = new Client($config);

The client constructor accepts an associative array of options that matches the
Guzzle format.
`See Guzzle documentation <http://docs.guzzlephp.org/en/latest/testing.html>`_.


Sending Requests
----------------

The client allows you to quickly request the API by abstracting the REST logic
as well as some of the API's specific logic.

All of the client's request methods will return you one of this 2 types of
objects:

- An :ref:`entity <entities>`
- A :ref:`collection <collections>` of entities

You can access an api resource using the magic properties of the client object.
Magic properties will return a :ref:`resource <resources>`.

.. code-block:: php

    // no request are made, you can execute request on the resource
    // or sometimes special methods
    $messageResource = $client->messages;

    // request GET /messages/, returns a collection object
    $messageCollection = $client->messages->get();

    // request GET $uri, returns an entity object
    // $uri contains the url of a message
    $messageEntity = $client->messages->get($uri);

    // request GET /attachments/ returns a collection object
    $attachmentCollection = $client->attachments->get();

    // request POST /messages/ (create a new message)
    $values = ['subject' => 'This is the subject'];
    $messageEntity = $client->messages->post($values);

    // special methods allowed only on DomainsResource objects:
    $verifyBoolean = $client->domains->verify('hello@readthedocs.org');


Using Resources
===============

You can use resources to access the root path of the corresponding resource:

.. code-block:: php

    // all messages
    $collection = $client->messages->get();


Or to directly access a resource when you know its id:

.. code-block:: php

    // unique message by its url
    $entity = $client->messages->get('https://api.crunchmail.net/messages/1234/');


Resources can also be accessed from some entities who have sub-resources:

.. code-block:: php

    // get a message
    $message = $client->messages->get($uri);

    // all messages attachment
    $collection = $message->attachements->get();


Using Entities
==============

You can use the entities objects to handle the corresponding API resource:

.. code-block:: php

    // request PUT /message/123
    $values = ['subject' => 'This is the subject'];
    $message->put($values);

    $message->addRecipient('ilove@readthedocs.org');
    $message->addAttachment('/path/to/a/cat/picture.jpg');

    // request DELETE /messages/123/
    $message->delete();


Using Collections
=================

You can use the collections to browse the result of a request and easily
navigate to previous and next page:

.. code-block:: php

    // get all messages:
    $collection = $client->messages->get();

    // result may be null if page is empty
    $page1 = $collection->current();
    $page2 = $collection->next()->current();

    foreach ($page1 as $messageEntity)
    {
        echo "Message subject is " . $messageEntity->title . PHP_EOL;
    }


Handling errors
===============

In the previous examples, you may have notice that we do not handle errors, and
of course we should! Crunchmail PHP client simplifies the errors send by guzzle
in an unique exception of type ``Crunchmail\Exception\ApiException``.

.. code-block:: php

    try
    {
        // missing values!
        $values = ['subject' => 'Yo!'];
        $willnotwork = $client->messages->post($values);
    }
    catch (\Crunchmail\Exception\ApiException $e)
    {
        echo 'Error: ' . htmlentities($e->getMessage());
        var_dump($e->getDetail());
    }

Filtering resources
===================

One common operation is also to filter the resource, which is also easy with
the client, using the filter() method on the resource :

.. code-block:: php

    $filter = ['status' => 'sent'];

    // only sent messages
    // request GET /messages/?status=sent
    $page1Collection = $client->messages->filter($filter)->get();

    // filter is saved!
    // request GET /messages/?status=sent&page=2
    $page2Collection = $page1->next();

