<?php

namespace PlzDontShare\CloudFlareImport\Console;

use PlzDontShare\CloudFlareImport\Version;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class VersionCommand extends BaseCommand
{
    protected static $defaultName = 'version';
    
    protected function configure()
    {
        $this->setDescription("Show current version");
    }
    
    protected function process(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(sprintf("Current Version: %s", Version::VERSION));
    }
}