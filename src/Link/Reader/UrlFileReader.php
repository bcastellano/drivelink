<?php

namespace DriveLink\Link\Reader;

use DriveLink\Link\LinkFileReaderInterface;

/**
 * Class UrlFileReader
 * Manage "*.url" files
 */
class UrlFileReader implements LinkFileReaderInterface
{
    private $contents;

    /**
     * @param string $contents
     */
    public function setFileContents($contents)
    {
        $this->contents = $contents;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        $matches = [];

        preg_match('/URL=([^\n]*)/mi', $this->contents, $matches);

        return (isset($matches[1]) ? $matches[1] : null);
    }
}