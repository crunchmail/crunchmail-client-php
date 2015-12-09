<?php
/**
 *
 * @author Yannick Huerre <dev@sheoak.fr>
 * @copyright (C) 2015 Oasiswork
 * @license MIT
 *
 */

namespace Crunchmail\Resources;

/**
 * Crunchmail\Client main class
 */
class AttachmentsResource extends GenericResource
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

        $body = fopen($path, 'r');

        // multipart post (*true* parameter)
        return $this->attachments->post([
            [
                'name' => 'file',
                'contents' => $body
            ],
            [
                'name' => 'message',
                'contents' => $this->parent->url
            ]
        ], true);
    }

}
