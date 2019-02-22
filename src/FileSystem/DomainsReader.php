<?php

namespace PlzDontShare\CloudFlareImport\FileSystem;

use PlzDontShare\CloudFlareImport\Models\Domain;
use RuntimeException;

class DomainsReader
{
    /**
     * @var FileReader
     */
    private $fileReader;
    
    /**
     * DomainsReader constructor.
     *
     * @param FileReader $fileReader
     */
    public function __construct(FileReader $fileReader)
    {
        $this->fileReader = $fileReader;
    }
    
    /**
     * Read domains from file
     *
     * @param string $filename
     *
     * @return array
     */
    public function readDomains($filename)
    {
        $full_path = $this->fileReader->makeFullPath($filename);
        if (!$this->fileReader->exists($full_path)) {
            throw new RuntimeException("File '{$full_path}' does not exists!");
        }
        
        $lines = $this->fileReader->readLines($filename);
        
        $domains = [];
        
        foreach ($lines as $line) {
            if (empty($line)) {
                continue;
            }
            $domains[] = Domain::createFromLine($line);
        }
        
        return $domains;
    }
}