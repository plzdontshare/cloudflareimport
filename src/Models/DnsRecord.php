<?php

namespace PlzDontShare\CloudFlareImport\Models;

class DnsRecord
{
    /**
     * @var string
     */
    public $dns_id;
    /**
     * @var string
     */
    public $type;
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $content;
    /**
     * @var bool
     */
    public $proxied;
    /**
     * @var string
     */
    public $zone_id;
    
    /**
     * DnsRecord constructor.
     *
     * @param string $zone_id
     * @param string $dns_id
     * @param string $type
     * @param string $name
     * @param string $content
     * @param bool $proxied
     */
    public function __construct($zone_id, $dns_id, $type, $name, $content, $proxied)
    {
        $this->dns_id = $dns_id;
        $this->type = $type;
        $this->name = $name;
        $this->content = $content;
        $this->proxied = $proxied;
        $this->zone_id = $zone_id;
    }
    
    /**
     * @param \stdClass $record
     *
     * @return static
     */
    public static function createFromStdClass(\stdClass $record)
    {
        return new static($record->zone_id, $record->id, $record->type, $record->name, $record->content, $record->proxied);
    }
}