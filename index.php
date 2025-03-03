<?php


header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');

include __DIR__ . '/connection.php';

require_once  __DIR__ . '/core/module/algorithm.php';

include __DIR__ . '/ntfy.php';

include __DIR__ . '/core/homepage.php';


