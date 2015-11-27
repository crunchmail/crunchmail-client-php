<?php
/**
 * This script is for demonstration of how the Crunchmail PHP client works.
 *
 * To test it, follow install instructions and put the content of the example
 * directory at the root directory of your project
 *
 * @link https://github.com/crunchmail/crunchmail-client-php
 *
 * @license MIT
 * @copyright (C) 2015 Oasis Work
 * @author Yannick Huerre <dev@sheoak.fr>
 */
ini_set('display_errors', '1');
error_reporting(E_ALL);

// require composer deps
require 'vendor/autoload.php';

// remember to copy and edit config.example.php
require 'config.php';

// a fake post
$post = array(
    'name'          => 'test',
    'subject'       => 'test subject',
    'sender_name'   => 'test sender',
    'sender_email'  => 'sender@verifieddomain.fr',
    'html'          => '<p>fantastic!</p>',
    'track_open'    => true,
    'track_clicks'  => true
);

// create a new client using the config
$Client = new Crunchmail\Client($config);

/******************************************************************************
 * WORKING EXAMPLES
 *
 * You should catch all exceptions, but the examples below have been simplified
 * for easy reading:
 *****************************************************************************/

/*
// Send a message to yourself as a preview
// for testing sending / preview
$yourmail = '';

if (empty($yourmail))
{
    throw new Exception('Please configure your email in <em>$youremail</em> var.');
}

$message = $Client->messages->create($post);
$Client->messages->sendPreview($message->url, $yourmail);

// Confirm sending
$Client->mails->push($message->url, $yourmail);
$Client->messages->sendMessage($message->url);

*/

echo '<h2>Messages</h2>';

// create a message
echo '<h3>Creating…</h3>';
$message = $Client->messages->create($post);
var_dump($message);

echo '<h3>Attachment…</h3>';
// add an attachment to it
$file = realpath(__DIR__ . '/test.png');
$res = $Client->attachments->upload($message->url, $file);
var_dump($res);

echo '<h3>Retrieve…</h3>';
// retrieve a message
$other = $Client->retrieve($message->url);
var_dump($other);

echo '<h3>Verify attachment is there:</h3>';
$other = $Client->messages->getAttachments($message->url);
var_dump($other);

echo '<h3>Verify domain…</h3>';
// verify a domain
$verify = $Client->domains->verify('fakedomain.com');
var_dump($verify);
$verify = $Client->domains->verify('verifieddomain.fr');
var_dump($verify);

echo '<h3>Search domain…</h3>';
// search a domain
$search = $Client->domains->search('fakedomain.com');
var_dump($search);
$search = $Client->domains->search('verifieddomain.fr');
var_dump($search[0]);

// add a recipient
echo '<h3>Add a recipient…</h3>';
$adding = $Client->mails->push($message->url, 'tintin@moulinsart.fakeext');
var_dump($adding);

// add several recipients
$recipients = [
    'milou@moulinsart.fakeext',
    'archibald@moulinsart.fakeext',
    'oops!'
    ];

echo '<h3>Add several recipients…</h3>';
$adding = $Client->mails->push($message->url, $recipients);
var_dump($adding);

// retrieve message recipients
echo '<h3>Retrieve recipients…</h3>';
$recipients = $Client->mails->retrieve($message->_links->mails->href);
var_dump($recipients);

// ready?
if (Crunchmail\Messages::isReady($message))
{
    echo '<p><strong>Message is ready to be sent!</strong></p>';
}

echo '<h3>Delete message…</h3>';
// delete it
$Client->remove($message->url);

/******************************************************************************
 * ERRORS EXAMPLES
 *
 * You should always catch exceptions like this:
 *****************************************************************************/

echo '<h2>Error handling</h2>';

echo '<h3>Creation error</h3>';
// create a message with an invalid domain
try
{
    $errPost = $post;
    $errPost['sender_email'] = '';
    $Client->messages->create($errPost);
}
catch (Crunchmail\Exception\ApiException $e)
{
    echo '<h4>Error <em>' . $e->getCode() . '</em></h4>';
    // this is for debug usage
    echo '<p>Html=</p>' . $e->toHtml();
    // this is the generic error
    echo "<p>Message=" . htmlentities($e->getMessage()) . '</p>';
    // this is for development
    var_dump($e->getDetail());
}
// this will catch unexpected error or client wrong usage
catch (Exception $e)
{
    // do smth
}

/*
 * FIXME: API bugguée, format incorrect
 */
echo '<h3>Creation error on domain</h3>';
// create a message with an invalid domain
try
{
    $errPost = $post;
    $errPost['sender_email'] = 'sender@fake.fakeext';
    $Client->messages->create($errPost);
}
catch (Crunchmail\Exception\ApiException $e)
{
    echo '<h4>Error <em>' . $e->getCode() . '</em></h4>';
    echo "<p>Message=" . htmlentities($e->getMessage()) . '</p>';
    // this is for debug usage
    // FIXME
    // echo '<p>Html=</p>' . $e->toHtml();
}
// this will catch unexpected error or client wrong usage
catch (RuntimeException $e)
{
    // do smth
}

echo '<h3>Retrieve error</h3>';
// create a message with an invalid domain
try
{
    // accessing an unknow resource
    $other = $Client->retrieve('fake');
}
catch (Crunchmail\Exception\ApiException $e)
{
    echo '<h4>Error <em>' . $e->getCode() . '</em></h4>';
    echo "<p>Message=" . htmlentities($e->getMessage()) . '</p>';
    // this is for debug usage
    echo '<p>Html=</p>' . $e->toHtml();
}

// create a message with issues (html is empty)
echo '<h3>Creating a message without HTML…</h3>';
$errPost = $post;
$errPost['html'] = '';
$message = $Client->messages->create($errPost);
var_dump($message);

// check status
echo "<p>Status is : " . $message->status . '<p>';
echo "<p>isReady() returns : </p>";
var_dump(Crunchmail\Messages::isReady($message));
echo "<p>hasIssue() returns : </p>";
var_dump(Crunchmail\Messages::hasIssue($message));


/******************************************************************************
 * OTHER EXAMPLES
 *****************************************************************************/

// list of messages
echo '<h2>List of messages</h2>';
$list = $Client->messages->retrieve();

echo '<ul>';
foreach ($list->results as $email)
{
    echo '<li>';
    echo '<h3>' . $email->name . '</h3>';
    echo '<p class="cm-status">' . 
        Crunchmail\Client::readableMessageStatus($email->status) . '</p>';
    echo '<p>PREVIEW= ' . $Client->messages->getPreviewUrl($email->url) .
        '</p>';
    echo '</li>';
}
echo '</ul>';


