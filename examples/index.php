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

use Crunchmail\Client;
use Crunchmail\Exception\ApiException;

// require composer deps
require 'vendor/autoload.php';

// remember to copy and edit config.example.php
require 'config.php';

// a fake post
$post = array(
    'name'          => 'A subject for testing : crunch',
    'subject'       => 'Crunch crunch!',
    'sender_name'   => 'Bob Crunchmail',
    'sender_email'  => $demo['sender'],
    'html'          => '<p>fantastic!</p>',
    'track_open'    => true,
    'track_clicks'  => true
);

// post a new client using the config
$cli = new Client($config);

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

$msg = $cli->messages->post($post);
//FIXME
//$msg->sendPreview($msg->url, $yourmail);

// Confirm sending
$msg->addRecipients($yourmail);
$msg->send();

*/

echo '<h2>Messages</h2>';

try
{
    // post a message
    echo '<h3>Creating…</h3>';

    // create a message
    $coolMsg = $cli->messages->post($post);
}
catch (ApiException $e)
{
    echo '<h2>Error</h2>';
    var_dump($e->getDetail());
    exit;
}

echo '<h3>Attachment…</h3>';
// add an attachment to it
$file = realpath(__DIR__ . '/test.png');
// return an attachment entity
$res = $coolMsg->addAttachment($file);
var_dump($res->getBody());

echo '<h3>Retrieve…</h3>';
// retrieve a message from it's id
$other = $cli->messages->get($coolMsg->url);
echo "Subject is: " . $other->subject;

// FIXME: api format correct?
/*
echo '<h3>Verify attachment is there:</h3>';
$other = $msg->attachments->get();
var_dump($other);
var_dump($other->getBody());
 */

echo '<h3>Verify domain…</h3>';
// verify a domain
$verify = $cli->domains->verify('fakedomain.com');
var_dump($verify);
$verify = $cli->domains->verify('verifieddomain.fr');
var_dump($verify);

echo '<h3>Search domain…</h3>';
// search a domain
$search = $cli->domains->search('fakedomain.com');
var_dump($search->current());
$search = $cli->domains->search($demo['sender']);
var_dump($search->current()[0]->getBody());

// add a recipient
// if the result is empty, the domain is probably not configured
echo '<h3>Add a recipient…</h3>';
$adding = $coolMsg->addRecipients('tintin@' . $demo['domain']);

// add several recipients
$recipients = [
    'milou@' . $demo['domain'],
    'archibald@' . $demo['domain'],
    'oops!'
    ];

echo '<h3>Add several recipients…</h3>';
$adding = $coolMsg->addRecipients($recipients);

// retrieve message recipients
echo '<h3>Retrieve recipients…</h3>';
$recipients = $coolMsg->recipients->get();
var_dump($recipients->current());

// ready?
if ($coolMsg->isReady())
{
    echo '<p><strong>Message is ready to be sent!</strong></p>';
}

echo "<h2>Other stuff…</h2>";
var_dump($coolMsg->bounces->get()->current());
var_dump($coolMsg->html());
var_dump($coolMsg->txt());

/******************************************************************************
 * ERRORS EXAMPLES
 *
 * You should always catch exceptions like this:
 *****************************************************************************/

echo '<h2>Error handling</h2>';

echo '<h3>Creation error</h3>';
// post a message with an invalid domain
try
{
    $errPost = $post;
    $errPost['sender_email'] = '';
    $cli->messages->post($errPost);
}
catch (ApiException $e)
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

echo '<h3>Creation error on domain</h3>';
// post a message with an invalid domain
try
{
    $errPost = $post;
    $errPost['sender_email'] = 'sender@fake.fakeext';
    $cli->messages->post($errPost);
}
catch (ApiException $e)
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
// post a message with an invalid domain
try
{
    // accessing an unknow resource
    $other = $cli->invalid->get('fake');
}
catch (ApiException $e)
{
    echo '<h4>Error <em>' . htmlentities($e->getMessage()) . '</em></h4>';
}

// post a message with issues (html is empty)
echo '<h3>Creating a message without HTML…</h3>';
$errPost = $post;
$errPost['html'] = '';
$msg = $cli->messages->post($errPost);
//var_dump($msg);

// check status
echo "<p>Status is : " . $msg->status . '<p>';
echo "<p>isReady() returns : </p>";
var_dump($msg->isReady());
echo "<p>hasIssue() returns : </p>";
var_dump($msg->hasIssue());

$msg->delete();

/******************************************************************************
 * OTHER EXAMPLES
 *****************************************************************************/

// list of messages
echo '<h2>List of messages</h2>';
$list = $cli->messages->get();

echo '<ul>';
foreach ($list->current() as $m)
{
    echo '<li>';
    echo '<h3>' . $m->name . '</h3>';
    echo '<p class="cm-status">' . $m->readableStatus() . '</p>';
    echo '</li>';
}
echo '</ul>';


$page2 = $list->next();

if (!is_null($page2))
{
    echo '<h2>List of messages, page 2</h2>';
    echo '<ul>';
    foreach ($page2->current() as $m)
    {
        echo '<li>';
        echo '<h3>' . $m->name . '</h3>';
        echo '<p class="cm-status">' . $m->readableStatus() . '</p>';
        echo '</li>';
    }
    echo '</ul>';
}

// list of messages
echo '<h2>List of filtered messages</h2>';
$filter = [
    'sender_email' => $demo['sender']
];
$list = $cli->messages->filter($filter)->get();

echo '<ul>';
foreach ($list->current() as $msg)
{
    echo '<li>';
    echo '<h3>' . $msg->name . '</h3>';
    echo '<p class="cm-status">' . $msg->readableStatus() . '</p>';
    echo '</li>';
}
echo '</ul>';

$coolMsg->delete();

