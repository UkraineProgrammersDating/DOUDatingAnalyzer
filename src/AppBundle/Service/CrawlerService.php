<?php

namespace AppBundle\Service;

class CrawlerService
{
    /**
     * @var CacheService
     */
    private $cacheService;

    /**
     * CrawlerService constructor.
     * @param CacheService $cacheService
     */
    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    public function get()
    {
        $this->cacheService->get('https://dou.ua/forums/topic/16490/');
    }
}
