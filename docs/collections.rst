
.. _collections:

===================
Collections objects
===================

The collection classes
======================

The requests you send via the Crunchmail PHP Client will either return
collections of entities or a single entities.

In case of collection, the object will always be generic, of the class
``Collections\GenericCollection`` (this might change in the future).

GenericCollection
=================

When accessing a resource that returns multiple objects, there might be several
pages to show. The collection object helps you to easily browse the results.


Get the number of total results
-------------------------------

:Method: ``count()``
:Summary: Returns the total number of results, including other pages
:Return: Int

.. code-block:: php

    $messages = $client->messages->get();
    echo "Total = " . $messages->count();


Get the number of pages
-----------------------

:Method: ``pageCount()``
:Summary: Returns the total number of pages
:Return: Int

.. code-block:: php

    $messages = $client->messages->get();
    echo "Total pages = " . $messages->pageCount();


Getting the current values
--------------------------

:Method: ``current()``
:Summary: Return the current loaded set of entities
:Return: Array of entities matching the collection type

.. code-block:: php

    $messages = $client->messages->filter(['sender_name' => 'Bad Guy'])->get();
    // delete all the messages matching
    foreach ($messages->current() as $message)
    {
        $message->send();
    }


Getting the next set
--------------------

:Method: ``next()``
:Summary: Request the next page to the API and returns the collection.
:Return: Array of entities matching the collection type, null if empty

.. code-block:: php

    $messages = $client->messages->filter()->get();
    $page2    = $messages->next();


Getting the previous set
------------------------

:Method: ``previous()``
:Summary: Request the next page to the API and returns the collection.
:Return: Array of entities matching the collection type, null if empty

.. code-block:: php

    $messages = $client->messages->filter()->get();
    $page2    = $messages->next();
    $page1    = $page2->previous();


Refreshing the current set
--------------------------

:Method: ``refresh()``
:Summary: Request the current page and returns it
:Return: Array of entities matching the collection type

.. code-block:: php

    $messages = $client->messages->get();

    // do some stuff here…

    $messages  = $messages->refresh();


Retrieve the Guzzle response
----------------------------

:Method: ``getResponse()``
:Summary: Return the Guzzle Response object
:Return: Guzzle Object

.. code-block:: php

    $messages = $client->messages->get();
    $guzzleResponse = $messages->getResponse();
