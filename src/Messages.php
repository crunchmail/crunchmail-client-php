<?php
/**
 * Messages subclass for Crunchmail API
 *
 * @license MIT
 * @copyright (C) 2015 Oasis Work
 * @author Yannick Huerre <dev@sheoak.fr>
 *
 * @link https://github.com/crunchmail/crunchmail-client-php
 * @link http://docs.guzzlephp.org/en/latest/
 */
namespace Crunchmail;

/**
 * Crunchmail\Client subclass Messages
 */
class Messages extends Client
{

    /**
     * Sending message via crunchmail API
     *
     * @param string $id Message url id
     * @return mixed
     *
     * @todo create a shortcut for patch
     */
    public function sendMessage($id)
    {
        try
        {
            return $this->patch($id, ['json' => ['status' => 'sending' ] ] );
        }
        catch (Exception $e)
        {
            $this->catchGuzzleException($e);
        }
    }

    /**
     * Return the message preview url after an api request
     *
     * @param string $id Message url id
     * @return string
     */
    public function getPreviewUrl($id)
    {
        $response = $this->retrieve($id);
        return $response->_links->preview_send->href;
    }

    /**
     * Send the preview for the message to the given recipients
     *
     * @param string $id Message url id
     * @param array $recipients list of recipients for the test
     * @return mixed
     */
    public function sendPreview($id, $recipients)
    {
        $recipients = is_array($recipients) ? $recipients : [ $recipients ];

        $url = $this->getPreviewUrl($id);

        // sending the preview via crunchmail API
        return $this->create(['to' => implode(',', $recipients) ], $url);
    }

    /**
     * Add an attachment to the given message
     *
     * @param string $id Message url id
     * @param array $post
     * @return object
     */
    public function getAttachments($id)
    {
        $url = $id . 'attachments/';
        return $this->retrieve($url);
    }

    /**
     * Check if the givem message is valid, or throw an exception
     *
     * @param object $msg
     */
    protected static function checkMessage($msg)
    {
        if (!isset($msg->status))
        {
            throw new \RuntimeException('Invalid message');
        }
    }

    /**
     * Return true if the message status is message_ok
     *
     * @param object $msg Message
     */
    public static function hasIssue($msg)
    {
        self::checkMessage($msg);
        return $msg->status === 'message_issues';
    }

    /**
     * Return true if the message status is message_ok
     *
     * @param object $msg Message
     */
    public static function isReady($msg)
    {
        self::checkMessage($msg);
        return $msg->status === 'message_ok';
    }

    /**
     * Return true if the message is being sent
     *
     * @param object $msg Message
     */
    public static function isSending($msg)
    {
        self::checkMessage($msg);
        return $msg->status === 'sending';
    }

    /**
     * Return true if the message has been sent
     *
     * @param object $msg Message
     */
    public static function hasBeenSent($msg)
    {
        self::checkMessage($msg);
        return $msg->status === 'sent';
    }
}
