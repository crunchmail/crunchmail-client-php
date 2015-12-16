
.. _entities:

================
Entities objects
================

The Entities classes
====================

The requests you send via the Crunchmail PHP Client will either return
collections of entities or a single entities.

In case of entities, the object returned might be a generic one (class
``Crunchmail\Entities\GenericEntity``) or a specific, like
``Crunchmail\Entities\MessageEntity`` for the messages. Specific entities will have
special methods that abstract the use of the API.

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
:Summary: Send a ``GET`` request to the API using the current entity uri.
:Return: Entity of the same type

.. code-block:: php

    // refresh the message
    $messages = $message->get();


PATCH request
-------------

:Method: ``patch()``
:Summary: Send a ``PATCH`` request to the API using the current entity uri.
:Parameters:
    - ``Array  $values`` : associative array of values to post
    - ``String $format`` : multipart or json (default)
:Return: Entity of the same type


POST request
------------

:Method: ``post()``
:Summary: Send a ``POST`` request to the API using the current entity uri.
:Parameters:
    - ``Array  $values`` : associative array of values to post
    - ``String $format`` : multipart or json (default)
:Return: Entity of the same type


PUT request
-----------

:Method: ``put()``
:Summary: Send a ``PUT`` request to the API using the current entity uri.
:Parameters:
    - ``Array  $values`` : associative array of values to put
    - ``String $format`` : multipart or json (default)
:Return: Entity of the same type

.. code-block:: php

    // edit the sender_name field
    $values = ['sender_name' => 'Edited'];
    $messages = $message->put($values);


DELETE Request
--------------

:Method: ``delete()``
:Summary: Send a DELETE request to the API to delete the current entity.

.. code-block:: php

    // edit the sender_name field
    $messages = $message->delete();


MessageEntity
=============

MessageEntity is the main Entity and probably the one you will use the most.
It is returned when you request the resource 'messages' and the result is a
single object.

.. code-block:: php

    $message = $client->messages->get($message_uri);

It is also accessible in collections of messages (see :ref:`collections`).


Sending a message
-----------------

:Method: ``send()``
:Summary: Ask the API to send the message using a ``PATCH`` request.
:Return: MessageEntity

.. code-block:: php

    $message->send();


Adding recipients
-----------------

:Method: ``addRecipients($recipient)``
:Summary: Ask the API to add the recipient(s) to the list of message's
          recipients.
:Parameters:
    - ``Mixed $recipient`` either a string or an array of recipients
:Return: RecipientsCollection

.. code-block:: php

    $message->addRecipients('hello@validdomain.td');
    $message->addRecipients(['hello@validdomain.td', 'second@otherdomain.td']);

.. note::

    If one or several emails are invalid, the valid emails are still added.


Adding an attachment
--------------------

:Method: ``addAttachment($filepath)``
:Summary: Adds the given attachement to the message.
:Parameters:
    - ``String $filepath`` path to the file
:Returns: AttachmentEntity

.. code-block:: php

    $message->addAttachment('/path/to/my/file.jpg');


Sending the preview
-------------------

:Method: ``previewSend($recipients)``
:Summary: Send the preview to the recipient(s)
:Parameters:
    - ``mixed$recipients`` string or array of recipients
:Returns: GenericEntitiy

.. code-block:: php

    $message->previewSend('ilove@crunchmail.net');

.. note::

    This is a shortcut to $message->preview_send->send() method.


Is the message ready?
---------------------

:Method: ``isReady()``
:Summary: Returns true if the message is ready to be sent, false otherwise.
:Returns: boolean

.. code-block:: php

    if ($message->isReady())
    {
        // do something
    }


Has the message issues?
-----------------------

:Method: ``hasIssue()``
:Summary: Returns true if the message has issues, false otherwise.
:Returns: boolean

.. code-block:: php

    if ($message->hasIssue())
    {
        // do something
    }


Has the message been sent?
--------------------------

:Method: ``hasbeensent()``
:Summary: Returns true if the message has been sent, false otherwise.
:Returns: boolean

.. code-block:: php

    if ($message->hasBeenSent())
    {
        // do something
    }


Is the message being sent?
--------------------------

:Method: ``isSending()``
:Summary: Returns true if the message is currently sending, false otherwise.
:Returns: boolean

.. code-block:: php

    if ($message->isSending())
    {
        // do something
    }


DomainEntity
=============

DomainEntity correspond to the registered domains:


Verifying  a domain
--------------------

:Method: ``verify($query)``
:Summary: Verify the domain
:Parameters:
    - ``String $query`` : search string
:Returns: GenericCollection

.. code-block:: php

    $bool = $domainEntity->verify('contact@crunchmail.net');

.. note::

    You can use the shortcut in the DomainsResource:
    $client->domains->verify($domain);


