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
     * @var array
     */
    private $cookies;

    /**
     * @var resource
     */
    private $context;

    /**
     * CrawlerService constructor.
     * @param CacheService $cacheService
     * @param array $cookies
     */
    public function __construct(CacheService $cacheService, array $cookies)
    {
        $this->cacheService = $cacheService;
        $this->cookies = $cookies;
    }

    public function parse($url)
    {
        $html = $this->cacheService->get($url);

        if (empty($html)) {
            $html = file_get_contents($url, false, $this->getStreamContext());

            $this->cacheService->set($url, $html);
        }

        return new Crawler($html);
    }

    private function getStreamContext()
    {
        if (empty($this->context)) {
            $this->context = stream_context_create([
                'http' => [
                    'header' => 'Cookie: ' . key($this->cookies) . '=' . current($this->cookies),
                ],
            ]);
        }

        return $this->context;
    }
}
