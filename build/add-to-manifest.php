#!/usr/bin/env php
<?php
/*
 * This file is part of the Docker Build Orchestrator project.
 *
 * (c) OneGuard <contact@oneguard.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

if (count($argv) !== 6 && count($argv) !== 7) {
    echo "Usage: php add-to-manifest.php <name> <url> <publicKey> <sha1> <version> [<manifest_file>]\n";
    exit(1);
}

$name = $argv[1];
$url = $argv[2];
$publicKey = $argv[3];
$sha1 = $argv[4];
$version = $argv[5];
$manifestFile = empty($argv[6]) ? 'manifest.json' : $argv[6];
$entry = [
    'name' => $name,
    'sha1' => $sha1,
    'url' => $url,
    'publicKey' => $publicKey,
    'version' => $version
];

$manifest = file_exists($manifestFile) ? json_decode(file_get_contents($manifestFile), true) : [];
$manifest = array_filter(
    $manifest,
    function ($e) use ($version) {
        return is_array($e) && !empty($e['version']) && $e['version'] !== $version;
    }
);
$manifest[] = $entry;
usort(
    $manifest,
    function ($e1, $e2) {
        return $e1 < $e2 ? -1 : ($e1 > $e2 ? 1 : 0);
    }
);

file_put_contents($manifestFile, json_encode($manifest, JSON_PRETTY_PRINT));
