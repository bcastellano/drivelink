<?php

namespace DriveLink\Google\Service;

use DriveLink\Google\Client;

class Drive
{
    /**
     * @var \Google_Service_Drive
     */
    protected $drive;

    public function __construct(Client $googleClient)
    {
        $this->googleClient = $googleClient;

        $this->drive = new \Google_Service_Drive($googleClient->getClient());
    }

    public function getDrive()
    {
        return $this->drive;
    }
}