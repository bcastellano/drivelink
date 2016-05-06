<?php

namespace DriveLink;

class GoogleDrive
{
    public function __construct(GoogleClient $googleClient)
    {
        $this->googleClient = $googleClient;

        $this->drive = new \Google_Service_Drive($googleClient->getClient());
    }

    public function getDrive()
    {
        return $this->drive;
    }
}