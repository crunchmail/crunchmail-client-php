<?php
/**
 *
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
}
