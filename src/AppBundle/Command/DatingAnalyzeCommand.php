<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
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
        $topics = [
            'https://dou.ua/forums/topic/16490/',
            'https://dou.ua/forums/topic/15063/',
            'https://dou.ua/forums/topic/14836/',
            'https://dou.ua/forums/topic/12622/',
            'https://dou.ua/forums/topic/10453/',
        ];

        $visitedProfiles = [];

        foreach ($topics as $topic) {
            $crawlerService = $this
                ->getContainer()
                ->get('dating.crawler');

            $topicCrawler = $crawlerService->parse($topic);

            $output->writeln($topicCrawler->filter('title')->html());

            $commentators = $topicCrawler->filter('a.avatar');

            $allProfiles = $commentators->each(function (Crawler $profile) {
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

            $profiles = array_diff_key($profiles, $visitedProfiles);
            $count = count($profiles);
            $output->writeln("<info>Found new profiles: $count</info>");

            $socialProfiles = [];
            foreach ($profiles as $profile) {
                $profileCrawler = $crawlerService->parse($profile['url']);

                $socials = $profileCrawler->filter('a.verified')->each(function (Crawler $crawler) {
                    return $crawler->attr('href');
                });

                foreach ($socials as $url) {
                    if (strpos($url, 'vk.com')) {
                        $socialProfiles[$profile['url']] = [
                            'name' => $profile['name'],
                            'url' => $profile['url'],
                            'vk' => $url,
                        ];
                    }
                }
            }

            $count = count($socialProfiles);
            $output->writeln("<info>Found social profiles: $count</info>");

            $table = new Table($output);
            $table
                ->setHeaders(['name', 'url', 'vk'])
                ->setRows($socialProfiles);
            $table->render();

            $visitedProfiles = array_merge(
                $visitedProfiles,
                array_fill_keys(array_keys($profiles), true)
            );

            $output->writeln("<info>Complete</info>: $topic");
        }
    }
}
