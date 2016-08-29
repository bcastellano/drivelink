<?php

namespace DriveLink\Link;

interface LinkFileReaderInterface
{
    /**
     * @param string $contents
     */
    public function setFileContents($contents);

    /**
     * @return string
     */
    public function getUrl();
}