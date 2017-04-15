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
        $crawler = $this
            ->getContainer()
            ->get('dating.crawler')
            ->parse('https://dou.ua/forums/topic/16490/');

        $output->writeln($crawler->filter('title')->html());

        $allProfiles = $crawler->filter('a.avatar')->each(function (Crawler $profile) {
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

        $output->writeln('<info>Complete</info>');
    }
}
