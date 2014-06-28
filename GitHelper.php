<?php
/**
 * 
 * 
 * @author Carsten Brandt <mail@cebe.cc>
 */

namespace cebe\composer\bower;


use Composer\Package\Version\VersionParser;

class GitHelper
{
    // get git tags
    // git ls-remote --tags git://github.com/git/git.git
    //	$tags = [
    //		// branch name, composer version, git reference
    //		['master', 'dev-master', '2c9fa5290854466d5059b44efaaf4db1014a4442'],
    //		['1.1.0', '1.1.0', '71f5106115e0641a3c4a0c7bc78b504ecaa85dfa'],
    //	];
    public static function getTags($repo)
    {
        $gitTags = static::runProcess('git ls-remote --tags --heads ' . escapeshellarg($repo));

        $versionParser = new VersionParser();

        $tags = [];
        foreach(explode("\n", trim($gitTags)) as $tagLine) {
            list($pointer, $ref) = preg_split('/\s+/', $tagLine);
            if (strncmp('refs/heads/', $ref, 11) === 0) {
                $composerVersion = 'dev-' . substr($ref, 11);
            } elseif (strncmp('refs/tags/', $ref, 10) === 0) {
                $composerVersion = substr($ref, 10);
            } else {
                continue;
            }
            $tags[] = [$ref, $composerVersion, $pointer, $versionParser->normalize($composerVersion)];
        }
        return $tags;
    }

    protected static function runProcess($cmd)
    {
        $descriptor = array(
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w'),
        );
        $pipes = array();
        $resource = proc_open($cmd, $descriptor, $pipes);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        foreach ($pipes as $pipe) {
            fclose($pipe);
        }
        if (proc_close($resource) !== 0) {
            throw new \Exception('Git failed: ' . $stderr);
        }
        return $stdout;
    }

} 