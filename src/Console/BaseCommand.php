<?php

namespace PlzDontShare\CloudFlareImport\Console;

use PlzDontShare\CloudFlareImport\App;
use PlzDontShare\CloudFlareImport\Version;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class BaseCommand extends Command
{
    /**
     * @var App
     */
    protected $app;
    
    /**
     * BaseCommand constructor.
     *
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        
        parent::__construct();
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(sprintf("\t\t\tCloudFlare Domain Import %s", Version::VERSION));
        $output->writeln("\t\t\t\tAuthor:  NoHate");
        $output->writeln("\t\t\t\tContact: @NoHate");
        $output->writeln('');
        
        $this->process($input, $output);
    }
    
    abstract protected function process(InputInterface $input, OutputInterface $output);
}