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
        $file = $this->getName($url);
        return file_exists($file)
            ? file_get_contents($file)
            : null;
    }

    public function set($url, $string)
    {
        file_put_contents($this->getName($url), $string);
    }

    private function getName($url)
    {
        return $this->directory . DIRECTORY_SEPARATOR . md5($url);
    }
}
