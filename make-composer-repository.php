<?php

use cebe\composer\bower\GitHelper;
use Composer\Package\Version\VersionParser;

// Send all errors to stderr
ini_set('display_errors', 'stderr');

require(__DIR__ . '/GitHelper.php');
require(__DIR__ . '/vendor/autoload.php');

$bowerRegistry = "http://bower.herokuapp.com/packages";
$bowerJson = json_decode(file_get_contents($bowerRegistry), true);

//print_r($bowerJson);

$composerPackages = [];

$limit = 10;
foreach($bowerJson as $package) {
    try {
        if (strpos($package['name'], '/') !== false) {
            continue; // / not supported yet
        }
    //	$package['url']

        $name = 'bower-bower/' . $package['name'];

        // get git tags
        $tags = GitHelper::getTags($package['url']);

        foreach($tags as $tag) {
            $composerPackages[$name][$tag[1]] = [
                "name" => $name,
                "version" => $tag[1],
                "version_normalized" => $tag[3],
                "type" => "bower-package",
                "source" => [
                    "url" => $package["url"],
                    "type" => "git",
                    "reference" => $tag[2]
                ],
    //           "dist" : {
    //              "shasum" : "",
    //              "reference" : "2c9fa5290854466d5059b44efaaf4db1014a4442",
    //              "url" : "https://api.github.com/repos/Umisoft/umi-framework/zipball/2c9fa5290854466d5059b44efaaf4db1014a4442",
    //              "type" : "zip"
    //           },
                "authors" => [], // TODO
                "require" => [], // TODO
            ];
        }
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


