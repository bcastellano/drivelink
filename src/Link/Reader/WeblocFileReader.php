<?php

namespace DriveLink\Link\Reader;

use DriveLink\Link\LinkFileReaderInterface;

/**
 * Class WeblocFileReader
 * Manage "*.webloc" files
 */
class WeblocFileReader implements LinkFileReaderInterface
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
        $xml = simplexml_load_string($this->contents);

        $url = (string)$xml->dict->string;

        return ($url ? : null);
    }
}