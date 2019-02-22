<?php

namespace PlzDontShare\CloudFlareImport\Console;

use PlzDontShare\CloudFlareImport\Models\DnsRecord;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AddSubdomainsCommand extends BaseCommand
{
    protected static $defaultName = 'add-subdomains';
    
    protected function configure()
    {
        $this->setDescription("Add subdomains to existing domains");
        $this->addArgument('filename', InputArgument::OPTIONAL, 'Path to file with domains', 'domains.txt');
        $this->addOption('ip', null, InputOption::VALUE_REQUIRED, 'New IP');
        $this->addOption('enable-proxy', 'p', InputOption::VALUE_NONE, 'Enable CloudFlare proxy for DNS records');
    }
    
    protected function process(InputInterface $input, OutputInterface $output)
    {
        $domains_file = $input->getArgument('filename');
        $ip = $input->getOption('ip');
        $proxy = (bool)$input->getOption('enable-proxy');
    
        $domains = $this->app->readDomains($domains_file);
    
        $count = count($domains);
        $output->writeln("Found {$count} domains");
        
        foreach ($domains as $domain) {
            try
            {
                if (empty($domain->subdomains)) {
                    $output->writeln("No subdomanis found for domain '{$domain->domain}'. Skipping...");
                }
                $output->write("Adding subdomains: [" . implode(',', $domain->subdomains) . "] for domain '{$domain->domain}' ... ");
                $domain->ip = empty($domain->ip) ? $ip : $domain->ip;
                $this->app->addSubdomains($domain, $proxy);
                $output->writeln("success");
            } catch (\Exception $e) {
                $output->writeln("error. Message: " . $e->getMessage());
            }
        }
    }
}