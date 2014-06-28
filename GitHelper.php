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

        $tags = [];
        foreach(explode("\n", trim($gitTags)) as $tagLine) {
            list($pointer, $ref) = preg_split('/\s+/', $tagLine);
            if (strncmp('refs/heads/', $ref, 11) === 0) {
                $composerVersion = substr($ref, 11);
            } elseif (strncmp('refs/tags/', $ref, 10) === 0) {
                $composerVersion = substr($ref, 10);
            } else {
                continue;
            }
            $tags[] = [$ref, $composerVersion, $pointer];
        }
        return $tags;
    }

    public static function getFile($repo, $tag, $file)
    {
        if (preg_match('~^(git|https)://github.com/([\w\d\-]+/[\w\d\-]+)(\.git)?$~', $repo, $matches)) {
            $url = 'https://raw.githubusercontent.com/' . $matches[2] . '/' . $tag . '/' . $file;
            if (is_file($f = __DIR__ . '/cache/' . sha1($url))) {
                return file_get_contents($f);
            } else {
                $c = file_get_contents($url);
                file_put_contents($f, $c);
                return $c;
            }
        }

        //git archive --remote=git://git.foo.com/project.git HEAD:path/to/directory filename | tar -x
        $cmd = 'git archive --remote='
            . escapeshellarg($repo) . ' '
            . escapeshellarg($tag) . ':'
            . escapeshellarg(dirname($file)) . ' '
            . escapeshellarg(basename($file)) . ' | tar -x';

        fwrite(STDERR, "$cmd\n");
        $fileConent = static::runProcess($cmd);

        return $fileConent;
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