<?php
/**
 * Attachments subclass for Crunchmail API
 *
 * Usage:
 *
 * $Client->attachments->join($id, $path);
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
 * Crunchmail\Client subclass Attachments
 */
class Attachments extends Client
{
    /**
     * Add an attachment to the given message
     *
     * @param string $id Message url id
     * @param array $post
     *
     * @todo test file error
     */
    public function join($id, $path)
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

            $response = $this->request('POST', '', [
                'multipart' => [
                    [
                        'name'     => 'file',
                        'contents' => $body
                    ],
                    [
                        'name'     => 'message',
                        'contents' => $id
                    ]
                ]
            ]);

            return json_decode($response->getBody());
        }
        catch (\Exception $e)
        {
            self::catchGuzzleException($e);
        }

    }

}
