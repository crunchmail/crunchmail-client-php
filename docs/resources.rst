
.. _resources:

=================
Resources objects
=================

The resources classes
======================


GenericResource
===============

All resources extends the GenericResource class, and therefore have access to
all its public methods.


GET request
-----------

:Method: ``get($uri)``
:Summary: Send a ``GET`` request to the API using the current resource uri, or
          the uri given in parameter.
:Parameters: ``String $uri`` : forced url
:Return: Collection of entities or entity of the matching type

.. code-block:: php

    $collection = $client->messages->get();
    $message = $client->messages->get($message_uri);


PATCH request
-------------

:Method: ``patch()``
:Summary: Send a ``PATCH`` request to the API using the current resource uri.
:Parameters:
    - ``Array  $values`` : associative array of values to post
    - ``String $format`` : multipart or json (default)
:Return: Entity of the same type

.. code-block:: php

    $entity = $client->resource_name->patch($values);


POST request
------------

:Method: ``post()``
:Summary: Send a ``POST`` request to the API using the current resource uri.
:Parameters:
    - ``Array  $values`` : associative array of values to post
    - ``String $format`` : multipart or json (default)
:Return: Entity of the same type

.. code-block:: php

    $message = $client->messages->post($values);


PUT request
-----------

:Method: ``put()``
:Summary: Send a ``PUT`` request to the API using the current resource uri.
:Parameters:
    - ``Array  $values`` : associative array of values to put
    - ``String $format`` : multipart or json (default)
:Return: Entity of the same type


DELETE Request
--------------

:Method: ``delete()``
:Summary: Send a DELETE request to the API to delete the current resource uri.

.. warning::

    Be very careful as you can delete several API resources using this
    method!


Filtering the resource
----------------------

:Method: ``filter($filters)``
:Summary: Register a filter. The filters are passed to the API in the query
          string parameters.
:Parameters:
    - ``Array $filters`` : list of filters, associative array
:Return: Current resource with registered filters

.. code-block:: php

    $resource = $client->messages->filter(['sender_name' => 'tintin@crunchmail.net']);
    $collection = $resource->get();


Direct acces to a page
----------------------

:Method: ``page($page)``
:Summary: Access directly to the requested page. This is a shortcut to
          ``get()`` method with the ``page`` filter applied.
:Parameters:
    - ``int $page`` : page number
:Return: Collection of the matching type

.. code-block:: php

    $collection = $client->messages->page(3);


DomainsResource
===============

Searching for a domain
----------------------

:Method: ``search($query)``
:Summary: Search for the domain
:Parameters:
    - ``String $query`` : search string
:Returns: GenericCollection

.. code-block:: php

    // search for domain
    $collection = $client->domains->search('crunchmail.net');

    if ($collection->count() > 0)
    {
        $current = $collection->current();
        // the is one result
        $domain = $current[0];
    }


Verifying  a domain
--------------------

:Method: ``verify($query)``
:Summary: Verify the domain
:Parameters:
    - ``String $query`` : search string
:Returns: GenericCollection

.. code-block:: php

    // search for domain
    if ($client->domains->verify('crunchmail.net'))
    {
        echo "Domain verified";
    }

    if ($client->domains->verify('contact@crunchmail.net'))
    {
        echo "Domain verified";
    }


PreviewSendResource
===================

:Method: ``send($recipients)``
:Summary: Send the preview to the recipient(s)
:Parameters:
    - ``mixed $recipients`` : string or array of recipients
:Returns: GenericEntity

.. code-block:: php

    $messageEntity->preview_send->send('ilove@crunchmail.net');


Sub-resources
=============

You can also access sub-resources directly using the recursive syntax.
This is useful when using, for example, the contact lists features.

.. code-block:: php

    // accessing /contacts/lists/ end-point
    $contactListsCollection  = $client->contacts->lists->get();

    // accessing /contacts/queues/ end-point
    $contactQueuesCollection = $client->contacts->queues->get();
