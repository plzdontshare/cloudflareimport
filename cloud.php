<?php

define('VERSION', 'v0.1-dev');

ini_set('display_errors', true);
ini_set('max_execution_time', 0);
set_time_limit(0);
error_reporting(E_ALL);

require 'vendor/autoload.php';
require 'functions.php';

echo_header();

$config = include './config.php';
$cloud = new Cloudflare\Api($config['cloudflare']['email'], $config['cloudflare']['key']);

message("Checking API key...", false);
try
{
    $user = new Cloudflare\User($cloud);
    $user = $user->user();
    message(" success. Hello, {$user->result->email}", true, false);
} catch (Cloudflare\Exception\AuthenticationException $e) {
    message(" error", true, false);
    message("Cloudflare error message: {$e->getMessage()}");
    die;
}

message("Reading domains...", false);
$domains = file('domains.txt');
$domains_count = count($domains);
message(" done", true, false);

message("Found {$domains_count} domains.");
message("");
foreach ($domains as $domain) {
    $domain = trim($domain);
    message("Creating zone for {$domain}...", false);
    $zone = new Cloudflare\Zone($cloud);
    $response = $zone->create($domain);
    if ($response->success === false) {
        $error_code = $response->errors[0]->code;
        $skip_zone = true;
        if ($error_code == 1061 && $config['do_not_skip_existing'] === true) {
            message(". Domain {$domain} already exists, but config allow to use existing domain.", true, false);
            message("Fetching zone ID for {$domain}", false);
            $skip_zone = false;
            $zones_info = $zone->zones($domain);

            if ($zones_info->result_info->total_count !== 1) {
                message(" error. Invalid number of results: {$zones_info->result_info->total_count}", true, false);
                $skip_zone = true;
            } else {
                $result = $zones_info->result[0];
            }
        }

        if ($skip_zone) {
            message(" error. {$response->errors[0]->message}", true, false);
            file_put_contents("zone_errors.csv", "{$domain}\t{$response->errors[0]->message}\n", FILE_APPEND);
            continue;
        }
    } else {
        $result = $response->result;
    }

    $zone_id = $result->id;
    $ns_1 = $result->name_servers[0];
    $ns_2 = $result->name_servers[1];
    
    message(" success. Zone ID: {$zone_id}", true, false);
    message("Adding DNS records for domain '{$domain}'...", false);
    $dns = new Cloudflare\Zone\Dns($cloud);
    $response = $dns->create($zone_id, 'A', $domain, $config['server_ip'], null, $config['proxy']);
    if ($response->success === false) {
        message(" error. {$response->errors[0]->message}", true, false);
        file_put_contents("dns_errors.csv", "{$domain}\tA\t{$response->errors[0]->message}\n", FILE_APPEND);
        continue;
    }
    if ($config['wildcard']) {
        $response = $dns->create($zone_id, 'A', '*', $config['server_ip']);
        if ($response->success === false) {
            message(" error. {$response->errors[0]->message}", true, false);
            file_put_contents("dns_errors.csv", "{$domain}\twildcard\t{$response->errors[0]->message}\n", FILE_APPEND);
            continue;
        }
    }
    message(" success.", true, false);
    file_put_contents("success.csv", "{$domain}\t{$zone_id}\t{$config['server_ip']}\t{$ns_1},{$ns_2}\n", FILE_APPEND);
}

message("Work done");