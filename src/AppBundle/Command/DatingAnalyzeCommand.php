<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;

class DatingAnalyzeCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('dating:analyze');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $crawlerService = $this
            ->getContainer()
            ->get('dating.crawler');

        $topicCrawler = $crawlerService->parse('https://dou.ua/forums/topic/16490/');

        $output->writeln($topicCrawler->filter('title')->html());

        $allProfiles = $topicCrawler->filter('a.avatar')->each(function (Crawler $profile) {
            return [
                'name' => trim($profile->text()),
                'url' => $profile->attr('href'),
            ];
        });

        $count = count($allProfiles);
        $output->writeln("<info>Found all profiles: $count</info>");

        $profiles = array_column($allProfiles, null, 'url');
        $count = count($profiles);
        $output->writeln("<info>Found unique profiles: $count</info>");

        $socialProfiles = [];
        foreach ($profiles as $profile) {
            $profileCrawler = $crawlerService->parse($profile['url']);

            $socials = $profileCrawler->filter('a.verified')->each(function (Crawler $crawler) {
                return $crawler->attr('href');
            });

            foreach ($socials as $url) {
                if (strpos($url, 'vk.com')) {
                    $socialProfiles[] = $profile;
                }
            }
        }

        $count = count($socialProfiles);
        $output->writeln("<info>Found social profiles: $count</info>");

        $output->writeln('<info>Complete</info>');
    }
}
