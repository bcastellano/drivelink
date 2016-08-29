<?php

namespace DriveLink\Link\Reader;

use DriveLink\Link\LinkFileReaderInterface;

/**
 * Class PlainFileReader
 * Manage not defined extension files
 */
class PlainFileReader implements LinkFileReaderInterface
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

        $regex = '#((https?|ftp)://(\S*?\.\S*?))([\s)\[\]{},;"\':<]|\.\s|$)#i';

        preg_match($regex, $this->contents, $matches);

        return (isset($matches[1]) ? $matches[1] : null);
    }
}