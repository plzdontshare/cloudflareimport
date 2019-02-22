<?php

namespace PlzDontShare\CloudFlareImport\Models;

class Domain
{
    /**
     * @var string
     */
    public $domain;
    /**
     * @var string|null
     */
    public $ip;
    /**
     * @var array
     */
    public $subdomains;
    
    /**
     * Domain constructor.
     *
     * @param string $domain
     * @param string|null $ip
     * @param array $subdomains
     */
    public function __construct($domain, $ip = null, array $subdomains = [])
    {
        $this->domain = $domain;
        $this->ip = $ip;
        $this->subdomains = $subdomains;
    }
    
    /**
     * Create instance from line
     *
     * @param string $line
     *
     * @return static
     */
    public static function createFromLine($line)
    {
        $parts = explode('|', $line);
        
        if (count($parts) === 1) {
            return new static($parts[0]);
        }
        
        $domain = $parts[0];
        $ip = $parts[1];
        $subdomains = [];
        
        if (isset($parts[2])) {
            $subdomains = explode(',', $parts[2]);
            $subdomains = array_map('trim', $subdomains);
        }
        
        return new static($domain, $ip, $subdomains);
    }
}