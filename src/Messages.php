<?php
/**
 * Messages subclass for Crunchmail API
 *
 * @license MIT
 * @copyright (C) 2015 Oasis Work
 * @author Yannick Huerre <dev@sheoak.fr>
 */
namespace Crunchmail;

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
        catch (ClientException $e)
        {
            self::handleGuzzleException($e);
        }
        catch (RequestException $e)
        {
            self::handleGuzzleException($e);
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
     * Return true if the message status is message_ok
     *
     * @param object $msg Message
     */
    public static function hasIssue($msg)
    {
        return isset($msg->status) && $msg->status === 'message_issues';
    }

    /**
     * Return true if the message status is message_ok
     *
     * @param object $msg Message
     */
    public static function isReady($msg)
    {
        return isset($msg->status) && $msg->status === 'message_ok';
    }

    /**
     * Return true if the message is being sent
     *
     * @param object $msg Message
     */
    public static function isSending($msg)
    {
        return isset($msg->status) && $msg->status === 'sending';
    }

    /**
     * Return true if the message has been sent
     *
     * @param object $msg Message
     */
    public static function hasBeenSent($msg)
    {
        return isset($msg->status) && $msg->status === 'sent';
    }
}
