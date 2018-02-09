<?php

function message($message, $endline = true, $timestamp = true)
{
    $time = date('Y-m-d H:i');
    $t = $timestamp ? "[{$time}] " : '';
    echo "{$t}{$message}", $endline ? PHP_EOL : '';
}

function echo_header()
{
    echo "\t\t\tCloudFlare Domain Import ", VERSION, PHP_EOL;
    echo "\t\t\t\tAuthor:  NoHate", PHP_EOL;
    echo "\t\t\t\tContact: <private>", PHP_EOL, PHP_EOL;
}