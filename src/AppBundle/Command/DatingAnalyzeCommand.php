<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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

        $output->writeln('<info>Complete</info>');
    }
}
