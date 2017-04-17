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
        $topics = ['https://dou.ua/forums/topic/16490/'];
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

            $commentatorCommentsMap = [];

            /* @var $commentator Crawler */
            $commentatorCount = $commentators->count();
            for ($i = 1; $i < $commentatorCount; ++$i) {
                $commentator = $commentators->eq($i);
                $url = $commentator->attr('href');

                if (isset($socialProfiles[$url])) {
                    try {
                        $commentatorCommentsMap[$url][] = $commentator
                            ->parents()
                            ->eq(1)
                            ->filter('.text p')
                            ->html();
                    } catch (\Exception $e) {
                    }
                }
            }

            $rows = [];
            foreach ($commentatorCommentsMap as $url => $comments) {
                $vk = $socialProfiles[$url]['vk'];
                foreach ($comments as $comment) {
                    $rows[] = [
                        'vk' => $vk,
                        'comment' => $comment,
                    ];
                }
            }

            $table = new Table($output);
            $table
                ->setHeaders(['vk', 'comment'])
                ->setRows($rows);
            $table->render();

            $output->writeln('<info>Complete</info>');
        }
    }
}
