
==============
Error handling
==============

The Crunchmail API Client simplify the exceptions that Guzzle will throw in
different scenarios, and always throw an ApiException for API errors.

For other errors, the client will throw a RuntimeException most of the time.
This will most likely happen when using the client in a wrong way.

The ApiException Class
======================

Get error message
-----------------

:Method: ``getMessage()``
:Summary: return the http error message sent by the API
:Return: String

.. code-block:: php

    try
    {
        $client->invalidresource->get();
    }
    catch (\Crunchmail\Exceptions\ApiException $e)
    {
        echo "Error: " . $e->getMessage();
    }

.. warning::

    The message may or may not contain html tags, be careful when printing it.


Get error code
--------------

:Method: ``getCode()``
:Summary: return the http error code
:Return: String

.. code-block:: php

    try
    {
        $client->invalidresource->get();
    }
    catch (\Crunchmail\Exceptions\ApiException $e)
    {
        echo "Error code was " . $e->getCode();
    }


Get detail about an error
-------------------------

:Method: ``getDetail()``
:Summary: return details about the exception
:Return: stdClass

.. code-block:: php

    try
    {
        $client->invalidresource->get();
    }
    catch (\Crunchmail\Exceptions\ApiException $e)
    {
        var_dump($e->getDetail());
    }


Get debug output
----------------

:Method: ``toHtml()``
:Summary: return a debug string, in HTML format
:Return: String

.. code-block:: php

    try
    {
        $client->invalidresource->get();
    }
    catch (\Crunchmail\Exceptions\ApiException $e)
    {
        echo $e->toHtml();
    }

