<?php

$bowerRegistry = "http://bower.herokuapp.com/packages";
$bowerJson = json_decode(file_get_contents($bowerRegistry), true);

//print_r($bowerJson);

$composerPackages = [];

$limit = 10;
foreach($bowerJson as $package) {
	if (strpos($package['name'], '/') !== false) {
		continue; // / not supported yet
	}
//	$package['url']

	$name = 'bower-bower/' . $package['name'];

	// TODO get git tags
	$tags = [
		// branch name, composer version, git reference
		['master', 'dev-master', '2c9fa5290854466d5059b44efaaf4db1014a4442'],
		['1.1.0', '1.1.0', '71f5106115e0641a3c4a0c7bc78b504ecaa85dfa'],
	];

	foreach($tags as $tag) {
		$composerPackages[$name][$tag[1]] = [
			"name" => $name,
			"version" => $tag[1],
			"version_normalized" => "9999999-dev", // TODO
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

	if ($limit-- <= 0) {
		break;
	}
}

print_r($composerPackages);


