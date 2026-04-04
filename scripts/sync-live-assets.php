<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$source = $root . '/vendor/vortexphp/live/resources/live.js';
$dest = $root . '/public/js/live.js';

if (! is_file($source)) {
    fwrite(STDERR, "sync-live-assets: missing {$source} (install vortexphp/live)\n");
    exit(1);
}

$dir = dirname($dest);
if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
    fwrite(STDERR, "sync-live-assets: could not create {$dir}\n");
    exit(1);
}

if (copy($source, $dest) === false) {
    fwrite(STDERR, "sync-live-assets: could not write {$dest}\n");
    exit(1);
}
