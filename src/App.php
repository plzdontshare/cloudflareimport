<?php

namespace PlzDontShare\CloudFlareImport;

use Cloudflare\Api;
use Cloudflare\Zone;
use Exception;
use PlzDontShare\CloudFlareImport\FileSystem\DomainsReader;
use PlzDontShare\CloudFlareImport\FileSystem\FileReader;
use PlzDontShare\CloudFlareImport\Models\DnsRecord;
use PlzDontShare\CloudFlareImport\Models\Domain;
use RuntimeException;
use stdClass;

class App
{
    /**
     * @var Api
     */
    private $api;
    /**
     * @var array
     */
    private $config;
    
    /**
     * App constructor.
     *
     * @param Api $api
     * @param array $config
     */
    public function __construct(Api $api, array $config)
    {
        $this->api = $api;
        $this->config = $config;
    }
    
    /**
     * List zones (permission needed: #zone:read)
     * List, search, sort, and filter your zones
     *
     * @param string|null $name      A domain name
     * @param string|null $status    Status of the zone (active, pending, initializing, moved, deleted)
     * @param int|null $page         Page number of paginated results
     * @param int|null $per_page     Number of zones per page
     * @param string|null $order     Field to order zones by (name, status, email)
     * @param string|null $direction Direction to order zones (asc, desc)
     * @param string|null $match     Whether to match all search requirements or at least one (any) (any, all)
     *
     * @return array
     */
    public function getCFDomains($name = null, $status = null, $page = 1, $per_page = 50, $order = null, $direction = null, $match = null)
    {
        $zoneApi = new Zone($this->api);
        $domains = [];
        
        do {
            $zones = $zoneApi->zones($name, $status, $page, $per_page, $order, $direction, $match);
            $this->checkSuccessApiResponse($zones);
            $total_count = (int)$zones->result_info->total_count;
            $total_pages = ceil($total_count / $per_page);
            
            foreach ($zones->result as $zone) {
                $domains[] = [
                    'domain'      => $zone->name,
                    'id'          => $zone->id,
                    'nameservers' => $zone->name_servers,
                    'status'      => $zone->status,
                ];
            }
            
            $page++;
        } while ($page <= $total_pages);
        
        return $domains;
    }
    
    /**
     * @param $zones
     */
    private function checkSuccessApiResponse($zones)
    {
        if ($zones->success !== true) {
            throw new RuntimeException($zones->errors[0]->message);
        }
    }
    
    /**
     * Read domains from $filename
     *
     * @param string $filename Path to file with domains
     *
     * @return array
     */
    public function readDomains($filename)
    {
        $reader = new DomainsReader(new FileReader);
        
        return $reader->readDomains($filename);
    }
    
    /**
     * Add domain to CloudFlare
     *
     * @param Domain $domain
     * @param bool $skip_existing
     *
     * @return array
     * @throws Exception
     */
    public function addDomain(Domain $domain, $skip_existing)
    {
        $zone = new Zone($this->api);
        
        try {
            $response = $zone->create($domain->domain);
            $domain_info = null;
            $this->checkSuccessApiResponse($response);
            $result = $response->result;
            $domain_info = [
                'id'          => $result->id,
                'nameservers' => (array)$result->name_servers,
            ];
        } catch (Exception $e) {
            if ($skip_existing === true && $response->errors[0]->code === APIResponseCodes::DOMAIN_ALREADY_EXISTS) {
                $domain_info = $this->getCFDomainInfo($domain);
            } else {
                throw $e;
            }
        }
        
        return $domain_info;
    }
    
    /**
     * @param Domain $domain
     *
     * @return array
     */
    public function getCFDomainInfo(Domain $domain)
    {
        $zone = new Zone($this->api);
        
        $zones_info = $zone->zones($domain->domain);
        $this->checkSuccessApiResponse($zones_info);
        $zone = $zones_info->result[0];
        
        return [
            'domain'      => $zone->name,
            'id'          => $zone->id,
            'nameservers' => $zone->name_servers,
            'status'      => $zone->status,
        ];
    }
    
    /**
     * @param string $zone_id
     *
     * @return mixed
     */
    public function getCFZoneInfo($zone_id)
    {
        $zone = new Zone($this->api);
        $zone_info = $zone->zone($zone_id);
        $this->checkSuccessApiResponse($zone_info);
        
        return $zone_info->result;
    }
    
    /**
     * @param string $zone_id
     * @param string $ip
     * @param string $zone_type
     * @param string $zone_name
     * @param bool $enable_proxy
     * @param bool $skip_existing
     *
     * @return stdClass
     * @throws Exception
     */
    public function addDnsRecord($zone_id, $ip, $zone_type, $zone_name, $enable_proxy, $skip_existing)
    {
        $result = null;
        
        try
        {
            $dns = new Zone\Dns($this->api);
    
            $response = $dns->create($zone_id, $zone_type, $zone_name, $ip, null, $enable_proxy);
            $this->checkSuccessApiResponse($response);
    
            $result = $response->result;
        } catch (Exception $e) {
            if ($skip_existing === true && $response->errors[0]->code === APIResponseCodes::RECORD_ALREADY_EXISTS) {
                $result = $this->getCFZoneInfo($zone_id);
            } else {
                throw $e;
            }
        }
    
        return $result;
    }
    
    /**
     * @param string $zone_id
     * @param string $dns_id
     * @param string $ip
     * @param string $zone_type
     * @param string $zone_name
     * @param bool $enable_proxy
     *
     * @return stdClass
     */
    public function updateDnsRecord($zone_id, $dns_id, $ip, $zone_type, $zone_name, $enable_proxy)
    {
        $dns = new Zone\Dns($this->api);
        $response = $dns->update($zone_id, $dns_id, $zone_type, $zone_name, $ip, null, $enable_proxy);
        $this->checkSuccessApiResponse($response);
    
        return $response->result;
    }
    
    /**
     * @param string $zone_id
     *
     * @return array
     */
    public function getDnsRecordsForDomain($zone_id)
    {
        $dns = new Zone\Dns($this->api);
        
        $response = $dns->list_records($zone_id);
        $this->checkSuccessApiResponse($response);
        
        return $response->result;
    }
    
    /**
     * @param DnsRecord $dnsRecord
     * @param string $new_ip
     *
     * @return stdClass
     */
    public function changeIP(DnsRecord $dnsRecord, $new_ip)
    {
        return $this->updateDnsRecord($dnsRecord->zone_id, $dnsRecord->dns_id, $new_ip, $dnsRecord->type, $dnsRecord->name, $dnsRecord->proxied);
    }
    
    /**
     * @param string $zone_id
     * @param string $enabled "on" or "off"
     *
     * @return stdClass
     */
    public function setAlwaysOnlineEnabled($zone_id, $enabled)
    {
        $zone = new Zone\Settings($this->api);
        
        $response = $zone->change_always_on($zone_id, $enabled);
        $this->checkSuccessApiResponse($response);
        
        return $response->result;
    }
    
    /**
     * @param string $zone_id
     * @param string $enabled "on" or "off"
     *
     * @return stdClass
     */
    public function setAlwaysUseHttpsEnabled($zone_id, $enabled)
    {
        $zone = new Zone\Settings($this->api);
        
        $response = $zone->change_always_use_https($zone_id, $enabled);
        $this->checkSuccessApiResponse($response);
        
        return $response->result;
    }
    
    /**
     * Change SSL Mode
     *
     * @param string $zone_id
     * @param string $mode Values: off, flexible, full, strict
     *
     * @return mixed
     */
    public function setSSLMode($zone_id, $mode)
    {
        $zone = new Zone\Settings($this->api);
    
        $response = $zone->change_ssl($zone_id, $mode);
        $this->checkSuccessApiResponse($response);
    
        return $response->result;
    }
    
    public function setSecurityLevel($zone_id, $value)
    {
        $zone = new Zone\Settings($this->api);
    
        $response = $zone->change_security_level($zone_id, $value);
        $this->checkSuccessApiResponse($response);
    
        return $response->result;
    }
    
    /**
     * @param Domain $domain
     * @param bool $proxy
     *
     * @return array
     */
    public function addSubdomains(Domain $domain, $proxy)
    {
        $subdomains = [];
        $zone = $this->getCFDomainInfo($domain);
        
        foreach ($domain->subdomains as $subdomain) {
            try
            {
                $subdomains[] = $this->addDnsRecord($zone['id'], $domain->ip, 'A', $subdomain, $proxy);
            } catch (Exception $e) {}
        }
        
        return $subdomains;
    }
    
    /**
     * @param Domain $domain
     */
    public function removeDomain(Domain $domain)
    {
        $alldomains = $this->getCFDomains();
        
        $zone = new Zone($this->api);
        foreach ($alldomains as $d) {
            if ($d['domain'] === $domain->domain) {
                $zone->delete_zone($d['id']);
            }
        }
    }
}