<?php

function timeAgo(string $date): string {
    date_default_timezone_set('Europe/Sofia');

    $timestamp = strtotime($date);
    if (!$timestamp) {
        return "Invalid date";
    }
    $serverTime = time();

    $timeAgo = $serverTime - $timestamp;

    $units = [
        31556926 => 'year',
        2629743  => 'month',
        604800   => 'week',
        86400    => 'day',
        3600     => 'hour',
        60       => 'minute'
    ];

    foreach ($units as $seconds => $unit) {
        if ($timeAgo >= $seconds) {
            $count = floor($timeAgo / $seconds);
            return "$count $unit" . ($count > 1 ? 's' : '') . " ago";
        }
    }

    return "Just now";
}





