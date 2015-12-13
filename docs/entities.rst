
========
Entities
========

The entities class
==================

The requests you send via the Crunchmail PHP Client will either return
collections of entities or a single entities.

The object returned might be a generic one (class Entities\GenericEntity) or
specific, like Entities\MessageEntity for the messages. Specific entities will
have special methods that abstract the use of the API.

You will be able to access the fields returned by the API directly, using the
magic properties of the entitiy:

.. code-block:: php

    echo "Sent by " . $messages->sender_name;


MessageEntity
=============

MessageEntity is the main Entity and probably the one you will use the most.
It is returned when you request the resource 'messages' and the result is a
single object.

.. code-block:: php

    $message = $client->messages->get($message_uri);

It is also accessible in collections of messages (see Collections).


send()
------

:Summary: The ``send()`` method ask the API to send the message (PATCH).
:Return: MessageEntity

.. code-block:: php

    $message->send();


addRecipients()
---------------

:Summary: The ``addRecipients()`` method ask the API to add the recipient(s) to
         the list of message's recipients.
:Parameters:
    - mixed $recipient either a string or an array of recipients
:Return: RecipientsCollection

.. code-block:: php

    $message->addRecipients('hello@validdomain.td');
    $message->addRecipients(['hello@validdomain.td', 'second@otherdomain.td']);

:Note: If one or several emails are invalid, the valid emails are still added.


addAttachment()
---------------

:Summary: The ``addAttachment()`` method adds an attachement to the message.
:Parameters:
    - string $filepath path to the file
:Returns: AttachmentEntity

.. code-block:: php

    $message->addAttachment('/path/to/my/file.jpg');


readableStatus()
----------------

:Summary: Returns the status of the message in a human readable form.
:Returns: String


html()
------

:Summary: Returns the message html content.
:Returns: string


txt()
-----

:Summary: Returns the message text content.
:Returns: string


isReady()
---------

:Summary: Returns true if the message is ready to be sent, false otherwise.
:Returns: boolean


hasIssue()
----------

:Summary: Returns true if the message has issues, false otherwise.
:Returns: boolean


hasbeensent()
-------------

:Summary: Returns true if the message has been sent, false otherwise.
:Returns: boolean


isSending()
-------------

:Summary: Returns true if the message is currently sending, false otherwise.
:Returns: boolean


AttachmentEntity
================

AttachmentEntity correspond to the messages attachments:

.. code-block:: php

    $attachment = $client->attachements->get($attachment_uri);


