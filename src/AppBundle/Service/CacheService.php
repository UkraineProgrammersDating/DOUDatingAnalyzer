<?php

namespace AppBundle\Service;

class CacheService
{
    private $directory;

    /**
     * CacheService constructor.
     * @param $directory
     */
    public function __construct($directory)
    {
        $this->directory = $directory;
    }

    public function get($url)
    {

    }

    public function set($url)
    {

    }
}
