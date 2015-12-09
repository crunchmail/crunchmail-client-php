<?php
/**
 * Messages subclass for Crunchmail API
 *
 * @license MIT
 * @copyright (C) 2015 Oasiswork
 * @author Yannick Huerre <dev@sheoak.fr>
 *
 * @link https://github.com/crunchmail/crunchmail-client-php
 * @link http://docs.guzzlephp.org/en/latest/
 */
namespace Crunchmail\Resources;

/**
 * Crunchmail\Client subclass Messages
 */
class MessageResource extends \Crunchmail\Resources\GenericResource
{
    /**
     * Add an attachment to the given message
     *
     * @param string $id Message url id
     * @param string $path File path
     * @return stdClass
     */
    public function addAttachment($path)
    {
        if (!file_exists($path))
        {
            throw new \RuntimeException('File not found');
        }

        if (!is_readable($path))
        {
            throw new \RuntimeException('File not readable');
        }

        try
        {
            $body = fopen($path, 'r');

            $response=$this->request('POST','', ['multipart'=> [
                    ['name'=>'file','contents'=>$body],
                    ['name'=>'message','contents'=>$this->url]
            ]]);

            return json_decode($response->getBody());
        }
        catch (\Exception $e)
        {
            $this->catchGuzzleException($e);
        }
    }

    /**
     * Return a human readable status from int status
     *
     * @param int $status
     * @return string
     *
     * @todo translation system
     */
    public function readableStatus()
    {
        $status = $this->status;
        $match = [

            'message_ok'      => "En attente d'envoi",
            'message_issues'  => "Le message contient des erreurs",
            'sent'            => "Le message a été envoyé",
            'sending'         => "En cours d'envoi…"
        ];

        return isset($match[$status]) ? $match[$status] : $status;
    }

    /**
     * Sending message via crunchmail API
     *
     * @return mixed
     */
    public function send()
    {
        return $this->patch(['status' => 'sending']);
    }

    /**
     * Return the message preview url after an api request
     *
     * @param string $id Message url id
     * @return string
    private function getPreviewUrl()
    {
        return $this->_links->preview_send->href;
    }
     */

    /**
     * Send the preview for the message to the given recipients
     *
     * @param array $recipients list of recipients for the test
     * @return mixed
     * TODO: move to PreviewResource
    public function sendPreview($recipients)
    {
        $recipients = is_array($recipients) ? $recipients : [ $recipients ];

        $url = $this->getPreviewUrl();

        // sending the preview via crunchmail API
        return $this->post(['to' => implode(',', $recipients) ], $url);
    }
     */

    /**
     * Check if the givem message is valid, or throw an exception
     */
    protected static function checkMessage()
    {
        if (!isset($this->status))
        {
            throw new \RuntimeException('Invalid message');
        }
    }

    /**
     * Return true if the message status is message_ok
     */
    public static function hasIssue()
    {
        self::checkMessage();
        return $this->status === 'message_issues';
    }

    /**
     * Return true if the message status is message_ok
     */
    public static function isReady()
    {
        self::checkMessage();
        return $this->status === 'message_ok';
    }

    /**
     * Return true if the message is being sent
     *
     * @param object $msg Message
     */
    public static function isSending()
    {
        self::checkMessage();
        return $this->status === 'sending';
    }

    /**
     * Return true if the message has been sent
     */
    public static function hasBeenSent()
    {
        self::checkMessage();
        return $this->status === 'sent';
    }
}
