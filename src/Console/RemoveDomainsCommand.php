<?php

namespace PlzDontShare\CloudFlareImport\Console;

use PlzDontShare\CloudFlareImport\Models\DnsRecord;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveDomainsCommand extends BaseCommand
{
    protected static $defaultName = 'remove-domains';
    
    protected function configure()
    {
        $this->setDescription("Remove domains from CloudFlare account");
        $this->addArgument('filename', InputArgument::OPTIONAL, 'Path to file with domains', 'domains.txt');
        $this->addOption('all', 'a', InputOption::VALUE_NONE, 'Force update to all domains on account');
    }
    
    protected function process(InputInterface $input, OutputInterface $output)
    {
        $all = (bool)$input->getOption('all');
        
        if ($all) {
            $this->removeAllDomains($input, $output);
        } else {
            $this->removeDomainsFromFile($input, $output);
        }
    }
    
    private function removeAllDomains(InputInterface $input, OutputInterface $output)
    {
        // TODO: implement
        throw new \RuntimeException("Not Implemented");
    }
    
    private function removeDomainsFromFile(InputInterface $input, OutputInterface $output)
    {
        $domains_file = $input->getArgument('filename');
    
        $domains = $this->app->readDomains($domains_file);
        
        $count = count($domains);
        $output->writeln("Found {$count} domains");
    
        foreach ($domains as $domain) {
            try
            {
                $output->write("Removing domain '{$domain->domain}' ... ");
                $this->app->removeDomain($domain);
                $output->writeln("success");
            } catch (\Exception $e) {
                $output->writeln("error. Message: " . $e->getMessage());
            }
        }
    }
}