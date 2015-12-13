
========
Entities
========

The Entities classes
====================

The requests you send via the Crunchmail PHP Client will either return
collections of entities or a single entities.

The object returned might be a generic one (class Entities\GenericEntity) or
specific, like Entities\MessageEntity for the messages. Specific entities will
have special methods that abstract the use of the API.

You will be able to access the fields returned by the API directly, using the
magic properties of the entitiy:

.. code-block:: php

    echo "Sent by " . $messages->sender_name;


GenericEntity
=============

All entity classes extends GenericEntity and therefore have access to some
methods in common.


GET request
-----------

:Method: ``get()``
:Summary: send a ``GET`` request to the API using the current entity uri.
:Return: Entity of the same type


PATCH request
-------------

:Method: ``patch()``
:Summary: send a ``PATCH`` request to the API using the current entity uri.
:Parameters:
    - Array  $values : associative array of values to post
    - String $format : multipart or json (default)
:Return: Entity of the same type


POST request
------------

:Method: ``post()``
:Summary: send a ``POST`` request to the API using the current entity uri.
:Parameters:
    - Array  $values : associative array of values to post
    - String $format : multipart or json (default)
:Return: Entity of the same type


PUT request
-----------

:Method: ``put()``
:Summary: send a ``PUT`` request to the API using the current entity uri.
:Parameters:
    - Array  $values : associative array of values to put
    - String $format : multipart or json (default)
:Return: Entity of the same type


DELETE Request
--------------

:Method: ``delete()``
:Summary: send a DELETE request to the API to delete the current entity.


MessageEntity
=============

MessageEntity is the main Entity and probably the one you will use the most.
It is returned when you request the resource 'messages' and the result is a
single object.

.. code-block:: php

    $message = $client->messages->get($message_uri);

It is also accessible in collections of messages (see Collections).


Send a message
--------------

:Method: ``send()``
:Summary: ask the API to send the message using a ``PATCH`` request.
:Return: MessageEntity

.. code-block:: php

    $message->send();


Adding recipients
-----------------

:Method: ``addRecipients($recipient)``
:Summary: ask the API to add the recipient(s) to the list of message's
          recipients.
:Parameters:
    - mixed $recipient either a string or an array of recipients
:Return: RecipientsCollection

.. code-block:: php

    $message->addRecipients('hello@validdomain.td');
    $message->addRecipients(['hello@validdomain.td', 'second@otherdomain.td']);

:Note: If one or several emails are invalid, the valid emails are still added.


Adding an attachment
--------------------

:Method: ``addAttachment($filepath)``
:Summary: adds the given attachement to the message.
:Parameters:
    - string $filepath path to the file
:Returns: AttachmentEntity

.. code-block:: php

    $message->addAttachment('/path/to/my/file.jpg');


Preview HTML
------------

:Method: ``html()``
:Summary: Returns the message html content.
:Returns: string


Preview TXT
-----------

:Method: ``txt()``
:Summary: Returns the message text content.
:Returns: string


Is the message ready?
---------------------

:Method: ``isReady()``
:Summary: Returns true if the message is ready to be sent, false otherwise.
:Returns: boolean


Has the message issues?
-----------------------

:Method: ``hasIssue()``
:Summary: Returns true if the message has issues, false otherwise.
:Returns: boolean


Has the message been sent?
--------------------------

:Method: ``hasbeensent()``
:Summary: Returns true if the message has been sent, false otherwise.
:Returns: boolean


Is the message being sent?
--------------------------

:Method: ``isSending()``
:Summary: Returns true if the message is currently sending, false otherwise.
:Returns: boolean


DomainEntity
=============

DomainEntity correspond to the registered domains:

Searching for a domain
----------------------

:Method: ``search($query)``
:Summary: Search for the domain
:Parameters:
    - string $query: search string
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


:Method: ``verify($query)``
:Summary: Verify the domain
:Parameters:
    - string $query: search string
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


