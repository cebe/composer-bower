<?php

use cebe\composer\bower\Bower2Composer;
use cebe\composer\bower\GitHelper;
use Composer\Package\Version\VersionParser;

// Send all errors to stderr
ini_set('display_errors', 'stderr');

require(__DIR__ . '/GitHelper.php');
require(__DIR__ . '/Bower2Composer.php');
require(__DIR__ . '/vendor/autoload.php');

$bowerRegistry = "http://bower.herokuapp.com/packages";

if (is_file($f = __DIR__ . '/cache/' . sha1($bowerRegistry))) {
    $c = file_get_contents($f);
} else {
    $c = file_get_contents($bowerRegistry);
    file_put_contents($f, $c);
}
$bowerJson = json_decode($c, true);

//print_r($bowerJson);

$converter = new Bower2Composer();

$composerPackages = [];

$limit = 10;
foreach($bowerJson as $package) {
    try {

        list($name, $packages) = $converter->convert($package);
        $composerPackages[$name] = $packages;

    } catch(\Exception $e) {
        fwrite(STDERR, 'failed to convert package: ' . $package['name']);
        fwrite(STDERR, $e->getMessage() . "\n");
        continue;
    }

	if ($limit-- <= 0) {
		break;
	}
}

//print_r($composerPackages);

echo json_encode(['packages' => $composerPackages]);


