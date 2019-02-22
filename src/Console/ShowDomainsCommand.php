<?php

namespace PlzDontShare\CloudFlareImport\Console;

use PlzDontShare\CloudFlareImport\FileSystem\FileWriter;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ShowDomainsCommand extends BaseCommand
{
    protected static $defaultName = 'show-domains';
    
    protected function configure()
    {
        $this->setDescription("Show all domains for your account");
        $this->addOption('save-to', 's', InputOption::VALUE_REQUIRED, 'Filename where to save fetched domains');
    }
    
    protected function process(InputInterface $input, OutputInterface $output)
    {
        $domains = $this->app->getCFDomains();
        $save_to = $input->getOption('save-to');
        
        if (empty($domains)) {
            return $output->writeln("No domains found for this account");
        }
    
        $headers = array_keys($domains[0]);
        $headers = array_map('ucfirst', $headers);
        $domains = array_map(function ($domain) {
            $domain['nameservers'] = implode(', ', $domain['nameservers']);
            
            return $domain;
        }, $domains);
        
        $file_writer = new FileWriter;
        if (!empty($save_to)) {
            $full_path = $file_writer->makeFullPath($save_to);
            $file_writer->saveCSV($full_path, $domains);
            $output->writeln("Saved results to '{$full_path}'");
        }
    
        $table = new Table($output);
        $table->setStyle('borderless');
        $table->setHeaders($headers);
        $table->setRows($domains);
        $table->render();
    }
}