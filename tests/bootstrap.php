<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

DG\BypassFinals::enable();

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
