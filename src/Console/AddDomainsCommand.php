<?php

namespace PlzDontShare\CloudFlareImport\Console;

use Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AddDomainsCommand extends BaseCommand
{
    protected static $defaultName = 'add-domains';
    
    protected function configure()
    {
        $this->setDescription('Add domains');
        $this->addArgument('filename', InputArgument::OPTIONAL, 'Path to file with domains', 'domains.txt');
        $this->addOption('ip', null, InputOption::VALUE_REQUIRED, 'Specify default IP for all domains');
        $this->addOption('wildcard', 'w', InputOption::VALUE_NONE, 'If specified wildcard dns record will be created');
        $this->addOption('skip-existing', 's', InputOption::VALUE_NONE, 'Skip existing domains');
        $this->addOption('enable-proxy', 'p', InputOption::VALUE_NONE, 'Enable CloudFlare proxy for DNS records');
        $this->addOption('enable-always-online', null, InputOption::VALUE_NONE, 'Skip disabling Always Online');
        $this->addOption('enable-https', null, InputOption::VALUE_NONE, 'Enable "Always use HTTPS" option');
        $this->addOption('ssl-mode', null, InputOption::VALUE_REQUIRED, 'SSL mode (off, flexible, full, strict)');
        $this->addOption('security-level', null, InputOption::VALUE_REQUIRED, 'Set Security Level (essentially_off, low, medium, high, under_attack)');
    }
    
    protected function process(InputInterface $input, OutputInterface $output)
    {
        $domains_file = $input->getArgument('filename');
        $ip = $input->getOption('ip');
        $wildcard = (bool)$input->getOption('wildcard');
        $skip = (bool)$input->getOption('skip-existing');
        $proxy = (bool)$input->getOption('enable-proxy');
        $always_online = (bool)$input->getOption('enable-always-online');
        $enable_https = (bool)$input->getOption('enable-https');
        $ssl_mode = $input->getOption('ssl-mode');
        $security_level = $input->getOption('security-level');
        
        $domains = $this->app->readDomains($domains_file);
        
        if (empty($domains)) {
            return $output->writeln("No domains found in '{$domains_file}'");
        }
        
        $domains_count = count($domains);
        $output->writeln("Found {$domains_count} domains.");
        
        foreach ($domains as $domain) {
            try
            {
                $output->write("Adding domain '{$domain->domain}' to CF account... ");
                $domain_info = $this->app->addDomain($domain, $skip);
                $output->writeln("success");
                
                $output->write("Adding 'A' DNS record for domain '{$domain->domain}' ... ");
                $domain_ip = empty($domain->ip) ? $ip : $domain->ip;
                $this->app->addDnsRecord($domain_info['id'], $domain_ip, 'A', $domain->domain, $proxy, $skip);
                $output->writeln('success');
                
                if ($wildcard) {
                    $output->write("Adding wildcard record for '{$domain->domain}' ... ");
                    $this->app->addDnsRecord($domain_info['id'], $domain_ip, 'A', '*', false, $skip);
                    $output->writeln('success');
                }
                
                if ($always_online === false) {
                    $output->write("Disabling AlwayOnline for '{$domain->domain}' ... ");
                    $this->app->setAlwaysOnlineEnabled($domain_info['id'], "off");
                    $output->writeln("success");
                }
                
                if ($enable_https) {
                    $output->write("Enabling \"Always use HTTPS\" option for '{$domain->domain}' ... ");
                    $this->app->setAlwaysUseHttpsEnabled($domain_info['id'], "on");
                    $output->writeln("success");
                }
    
                if (!empty($ssl_mode)) {
                    $output->writeln("Changing SSL Mode to {$ssl_mode} ... ");
                    $this->app->setSSLMode($domain_info['id'], $ssl_mode);
                    $output->writeln("success");
                }
                
                if (!empty($security_level)) {
                    $output->writeln("Changing Security Level to {$security_level} ... ");
                    $this->app->setSecurityLevel($domain_info['id'], $security_level);
                    $output->writeln("success");
                }
                
                $output->writeln("===============================");
            } catch (Exception $e) {
                $output->writeln("error. Message: " . $e->getMessage());
                die;
            }
        }
        
        $output->writeln("Finished");
    }
}