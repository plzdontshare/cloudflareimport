<?php

require_once 'init.php';
require 'vendor/autoload.php';

$config = include './config.php';
$cloud = new Cloudflare\Api($config['cloudflare']['email'], $config['cloudflare']['key']);
$app = new \PlzDontShare\CloudFlareImport\App($cloud, $config);

$console = new \Symfony\Component\Console\Application;
$console->add(new \PlzDontShare\CloudFlareImport\Console\VersionCommand($app));
$console->add(new \PlzDontShare\CloudFlareImport\Console\ChangeIPCommand($app));
$console->add(new \PlzDontShare\CloudFlareImport\Console\ShowDomainsCommand($app));
$console->add(new \PlzDontShare\CloudFlareImport\Console\AddDomainsCommand($app));
$console->add(new \PlzDontShare\CloudFlareImport\Console\AddSubdomainsCommand($app));
$console->add(new \PlzDontShare\CloudFlareImport\Console\RemoveDomainsCommand($app));

$console->run();