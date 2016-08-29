<?php

namespace DriveLink\Link;

use DriveLink\Link\Reader\PlainFileReader;
use DriveLink\Link\Reader\UrlFileReader;
use DriveLink\Link\Reader\WeblocFileReader;

class LinkReader
{
    public function getUrlFromFile(\Google_Service_Drive_DriveFile $metadata, $contents)
    {
        // from extension create correct reader
        switch ($metadata->getFileExtension()) {
            case 'url':
                $linkFile = new UrlFileReader();
                break;

            case 'webloc':
                $linkFile = new WeblocFileReader();
                break;

            default:
                $linkFile = new PlainFileReader();
                break;
        }

        // ser file contents to read
        $linkFile->setFileContents($contents);

        // return url in file
        return $linkFile->getUrl();
    }
}