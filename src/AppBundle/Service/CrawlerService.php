<?php

namespace AppBundle\Service;

use Symfony\Component\DomCrawler\Crawler;

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

    public function parse($url)
    {
        $html = $this->cacheService->get($url);

        if (empty($html)) {
            $html = file_get_contents($url);

            $this->cacheService->set($url, $html);
        }

        return new Crawler($html);
    }
}
