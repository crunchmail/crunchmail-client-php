<?php
/**
 *
 * @author Yannick Huerre <dev@sheoak.fr>
 */

namespace Crunchmail;

use Crunchmail\Exception\ApiException;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;

class Messages extends Client
{

    /**
     * Sending message via crunchmail API
     *
     * @param string $id Message url id
     * @return mixed
     */
    public function sendMessage($id)
    {
        try
        {
            return $this->patch($id, ['json' => ['status' => 'sending' ] ] );
        }
        catch (ClientException $e)
        {
            throw new ApiException($e->getMessage(), $e->getCode());
        }
        catch (RequestException $e)
        {
            throw new ApiException($e->getMessage(), $e->getCode());
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
        try
        {
            $response = $this->retrieve($id);
            return $response->_links->preview_send->href;
        }
        catch (ClientException $e)
        {
            throw new ApiException($e->getMessage(), $e->getCode());
        }
        catch (RequestException $e)
        {
            throw new ApiException($e->getMessage(), $e->getCode());
        }
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

        try
        {
            // sending the preview via crunchmail API
            return $this->post($url, ['json' => ['to' => implode(',', $recipients) ] ]);
        }
        catch (ClientException $e)
        {
            throw new ApiException($e->getMessage(), $e->getCode());
        }
        catch (RequestException $e)
        {
            throw new ApiException($e->getMessage(), $e->getCode());
        }

    }
}
