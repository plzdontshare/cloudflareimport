<?php

namespace PlzDontShare\CloudFlareImport\Console;

use PlzDontShare\CloudFlareImport\Models\DnsRecord;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ChangeIPCommand extends BaseCommand
{
    protected static $defaultName = 'change-ip';
    
    protected function configure()
    {
        $this->setDescription("Change IP for existing domains");
        $this->addArgument('filename', InputArgument::OPTIONAL, 'Path to file with domains', 'domains.txt');
        $this->addOption('all', 'a', InputOption::VALUE_NONE, 'Force update to all domains on account');
        $this->addOption('ip', null, InputOption::VALUE_REQUIRED, 'New IP');
    }
    
    protected function process(InputInterface $input, OutputInterface $output)
    {
        $all = (bool)$input->getOption('all');
        
        if ($all) {
            $this->updateAllDomains($input, $output);
        } else {
            $this->updateDomainsFromFile($input, $output);
        }
    }
    
    private function updateAllDomains(InputInterface $input, OutputInterface $output)
    {
        // TODO: implement
        throw new \RuntimeException("Not Implemented");
    }
    
    private function updateDomainsFromFile(InputInterface $input, OutputInterface $output)
    {
        $domains_file = $input->getArgument('filename');
        $ip = $input->getOption('ip');
    
        $domains = $this->app->readDomains($domains_file);
        
        $count = count($domains);
        $output->writeln("Found {$count} domains");
    
        foreach ($domains as $domain) {
            $domain_ip = empty($domain->ip) ? $ip : $domain->ip;
            
            if (empty($domain_ip)) {
                $output->writeln("Missing IP for domain '{$domain->domain}'. Please specify default IP or add IP to '{$domains_file}' file");
                continue;
            }
            
            $output->writeln("Changing IP to '{$domain_ip}' for '{$domain->domain}'");
            
            try
            {
                $zone = $this->app->getCFDomainInfo($domain);
                $dns_records = $this->app->getDnsRecordsForDomain($zone['id']);
                $dns_records = array_map(function ($record) {
                    return DnsRecord::createFromStdClass($record);
                }, $dns_records);
                
                $dns_records_count = count($dns_records);
                $output->writeln("Found {$dns_records_count} DNS records for domain '{$domain->domain}'");
            
                foreach ($dns_records as $dns_record) {
                    $output->write("Changing DNS record {$dns_record->name} ({$dns_record->type}) for domain '{$domain->domain}' ... ");
                    $this->app->changeIP($dns_record, $ip);
                    $output->writeln("success");
                }
            } catch (\Exception $e) {
                $output->writeln("error. Message: " . $e->getMessage());
            }
        }
    }
}