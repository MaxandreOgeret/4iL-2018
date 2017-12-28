<?php
/**
 * Created by PhpStorm.
 * User: m.ogeret
 * Date: 21/12/2017
 * Time: 17:38
 */

namespace AppBundle\Manager;

class ArticleManager
{
    public $imageDirectory;

    public function __construct(string $imageDirectory)
    {
        $this->imageDirectory = $imageDirectory;
    }

    public function handleBase64Image($base64String)
    {
        $fileName = md5(uniqid()).'.jpeg';
        /** @var resource $source */
        $source = fopen($base64String, 'r');

        if ($this->imageStreamValid($source)) {
            $destination = fopen($this->imageDirectory.$fileName, 'w');
            stream_copy_to_stream($source, $destination);
            fclose($source);
            fclose($destination);

            return $fileName;
        } else {
            return null;
        }
    }

    public function imageStreamValid($stream)
    {
        $metadata = stream_get_meta_data($stream);

        if ($metadata['base64'] !== true || $metadata['mediatype'] !== "image/jpeg" || strlen($metadata['uri']) > 250000) {
            return false;
        } else {
            return true;
        }
    }
}