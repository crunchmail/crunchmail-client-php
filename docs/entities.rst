
========
Entities
========

The entities class
==================

Request you send via the Crunchmail PHP Client will either return collections
of entities or a single entitiy.

The object returned might be a generic one (class Entities\GenericEntity) or
specific, like Entities\MessageEntity for the messages. Specific entities will
have special methods that abstract the use of the API.

You will be able to access the fields return by the API directly, using the
magic properties of the entitiy.

.. code-block:: php

    echo "Sent by " . $messages->sender_name;


MessageEntity
=============

MessageEntity is the main Entity and probably the one you will use the most.
It is returned when you request the resource 'messages' and the result is a
single object.

.. code-block:: php

    $message = $client->messages->get($message_uri);


readableStatus()
----------------

Returns the status of the message in a human readable form.
Returns: string


send()
------

Send the message.
Returns: MessageEntity.


addAttachment(string $filepath)
-------------------------------

Add an attachement to the message.
Returns: AttachmentEntity.

- $filepath : path to the file to include, must be readable by the server


html()
------

Returns the message html content.

Returns: string


txt()
-----

Returns the message text content.

Returns: string


addRecipients(mixed $recipient)
-------------------------------

Add the recipient(s) to the list of message recipients.
This can be either a string or an array

Returns: RecipientsCollection

isReady()
---------

returns true if the message is ready to be sent, false otherwise.

returns: boolean


hasIssue()
----------

Returns true if the message has issues, false otherwise.

Returns: boolean


hasbeensent()
-------------

returns true if the message has been sent, false otherwise.

returns: boolean


isSending()
-------------

returns true if the message is currently sending, false otherwise.

returns: boolean


AttachmentEntity
================

AttachmentEntity correspond to the messages attachments:

.. code-block:: php

    $attachment = $client->attachements->get($attachment_uri);


