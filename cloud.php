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
        message(" error. {$response->errors[0]->message}", true, false);
        file_put_contents("zone_errors.csv", "{$domain}\t{$response->errors[0]->message}", FILE_APPEND);
        continue;
    }
    $zone_id = $response->result->id;
    message(" success. Zone ID: {$zone_id}", true, false);
    message("Adding DNS records for domain '{$domain}'...", false);
    $dns = new Cloudflare\Zone\Dns($cloud);
    $response = $dns->create($zone_id, 'A', $domain, $config['server_ip']);
    if ($response->success === false) {
        message(" error. {$response->errors[0]->message}", true, false);
        file_put_contents("dns_errors.csv", "{$domain}\tA\t{$response->errors[0]->message}", FILE_APPEND);
        continue;
    }
    if ($config['wildcard']) {
        $response = $dns->create($zone_id, 'A', '*', $config['server_ip']);
        if ($response->success === false) {
            message(" error. {$response->errors[0]->message}", true, false);
            file_put_contents("dns_errors.csv", "{$domain}\twildcard\t{$response->errors[0]->message}", FILE_APPEND);
            continue;
        }
    }
    message(" success.", true, false);
    file_put_contents("success.csv", "{$domain}\t{$zone_id}\t{$config['server_ip']}", FILE_APPEND);
}

message("Work done");