
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
:Summary: send a ``GET`` request to the API using the current resource uri, or
          the uri given in parameter.
:Parameters: ``String $uri`` : forced url
:Return: Collection of Entities or Entitiy of the matching type


PATCH request
-------------

:Method: ``patch()``
:Summary: send a ``PATCH`` request to the API using the current resource uri.
:Parameters:
    - ``Array  $values`` : associative array of values to post
    - ``String $format`` : multipart or json (default)
:Return: Entity of the same type


POST request
------------

:Method: ``post()``
:Summary: send a ``POST`` request to the API using the current resource uri.
:Parameters:
    - ``Array  $values`` : associative array of values to post
    - ``String $format`` : multipart or json (default)
:Return: Entity of the same type


PUT request
-----------

:Method: ``put()``
:Summary: send a ``PUT`` request to the API using the current resource uri.
:Parameters:
    - ``Array  $values`` : associative array of values to put
    - ``String $format`` : multipart or json (default)
:Return: Entity of the same type


DELETE Request
--------------

:Method: ``delete()``
:Summary: send a DELETE request to the API to delete the current resource uri.
:Note: Be very careful as you can delete several API resources using this
       method!


Filtering the resource
----------------------

:Method: ``filter($filters)``
:Summary: register a filter. The filters are passed to the API in the query
          string parameters.
:Parameters:
    - ``Array $filters`` : list of filters, associative array
:Return: current resource with registered filters

.. code-block:: php

    $client->messages->filter(['sender_name' => 'tintin@crunchmail.net']);


Direct acces to a page
----------------------

:Method: ``page($page)``
:Summary: access directly to the requested page. This is a shortcut to
          ``get()`` method with the ``page`` filter applied.
:Parameters:
    - ``int $page`` : page number
:Return: collection of the matching type

.. code-block:: php

    $collection = $client->messages->page(3);
