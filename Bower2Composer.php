<?php
/**
 * 
 * 
 * @author Carsten Brandt <mail@cebe.cc>
 */

namespace cebe\composer\bower;


use Composer\Package\Version\VersionParser;

class Bower2Composer
{
    protected $versionParser;


    public function convert($package)
    {
        if ($this->versionParser === null) {
            $this->versionParser = new VersionParser();
        }

        $name = $this->convertName($package['name']);
        $packages = [];

        // get git tags
        $tags = GitHelper::getTags($package['url']);
        $limit = 10;
        foreach($tags as $tag) {

            // strip the release- prefix from tags if present
            $tag[1] = str_replace('release-', '', $tag[1]);

            if (!$parsedTag = $this->validateTag($tag[1])) {
                fwrite(STDERR, "Skipped tag $tag[1], invalid tag name.\n");
                continue;
            }

            $bowerJson = $this->getBowerJson($package['url'], $tag[1]);

            // make sure tag packages have no -dev flag
//            $data['version'] = preg_replace('{[.-]?dev$}i', '', $data['version']);
//            $data['version_normalized'] = preg_replace('{(^dev-|[.-]?dev$)}i', '', $data['version_normalized']);

            $packages[$tag[1]] = [
                "name" => $name,
                "version" => $tag[1],
                "version_normalized" => $this->versionParser->normalize($tag[1]),
                "type" => "bower-package",
                "source" => [
                    "url" => $package["url"],
                    "type" => "git",
                    "reference" => $tag[2]
                ],
                // TODO provide dist by github
    //           "dist" : {
    //              "shasum" : "",
    //              "reference" : "2c9fa5290854466d5059b44efaaf4db1014a4442",
    //              "url" : "https://api.github.com/repos/Umisoft/umi-framework/zipball/2c9fa5290854466d5059b44efaaf4db1014a4442",
    //              "type" : "zip"
    //           },
                "authors" => [], // TODO
                "require" => [], // TODO
            ];

            if (isset($bowerJson['description'])) {
                $packages[$tag[1]]['description'] = $bowerJson['description'];
            }
            if (isset($bowerJson['keywords'])) {
                $packages[$tag[1]]['keywords'] = (array)$bowerJson['keywords'];
            }
            if (isset($bowerJson['license'])) {
                $packages[$tag[1]]['license'] = (array)$bowerJson['license'];
            }
            // TODO skip if private
            // TODO authors
            // TODO homepage
            // TODO repository
            if (isset($bowerJson['main'])) {
                $packages[$tag[1]]['extra']['bower-main'] = $bowerJson['main'];
            }
            if (isset($bowerJson['ignore'])) {
                $packages[$tag[1]]['extra']['bower-ignore'] = $bowerJson['ignore'];
            }
            if (isset($bowerJson['dependencies']) && is_array($bowerJson['dependencies'])) {
                foreach($bowerJson['dependencies'] as $dep => $version) {
                    $packages[$tag[1]]['require'][$this->convertName($dep)] = $this->convertVersionConstraint($version);
                }
                // TODO resolve repo name as verion
            }
            if (isset($bowerJson['devDependencies']) && is_array($bowerJson['devDependencies'])) {
                foreach($bowerJson['devDependencies'] as $dep => $version) {
                    $packages[$tag[1]]['require-dev'][$this->convertName($dep)] = $this->convertVersionConstraint($version);
                }
                // TODO resolve repo name as verion
            }
            // TODO resolutions


            if ($limit-- <= 0) {
                break;
            }
        }

        return array($name, $packages);
    }

    public function convertName($bowerName)
    {
        return 'bower/' . str_replace('/', '_', $bowerName);
    }

    public function convertVersionConstraint($bowerVersion)
    {
        // TODO convert rules: https://github.com/isaacs/node-semver#ranges
        return $bowerVersion;
    }


    public function getBowerJson($repo, $tag)
    {
        try {
            return json_decode(Githelper::getFile($repo, $tag, 'bower.json'), true);
        } catch(\Exception $e) {
            fwrite(STDERR, $e->getMessage(). ", Skipping tag $tag.\n");
        }
        return false;
    }


    // from composer VCSRepository
    private function validateBranch($branch)
    {
        if ($this->versionParser === null) {
            $this->versionParser = new VersionParser();
        }
        try {
            return $this->versionParser->normalizeBranch($branch);
        } catch (\Exception $e) {
        }

        return false;
    }

    private function validateTag($version)
    {
        if ($this->versionParser === null) {
            $this->versionParser = new VersionParser();
        }
        try {
            return $this->versionParser->normalize($version);
        } catch (\Exception $e) {
        }

        return false;
    }

} 