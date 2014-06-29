<?php
/**
 * 
 * 
 * @author Carsten Brandt <mail@cebe.cc>
 */

namespace cebe\composer\bower\commands;

use cebe\composer\bower\components\Bower2Composer;
use yii\console\Controller;
use yii\helpers\Console;
use Yii;

class ConvertController extends Controller
{
    public $verbose = false;

    public function actionIndex($bowerRegistry = null)
    {
        $cache = Yii::$app->cache;
        if ($bowerRegistry === null) {
            $bowerRegistry = "http://bower.herokuapp.com/packages";
        }

        if (($bowerJson = $cache->get($bowerRegistry)) === false) {
            $cache->set($bowerRegistry, $bowerJson = file_get_contents($bowerRegistry));
        }
        $bowerJson = json_decode($bowerJson, true);

        //print_r($bowerJson);

        $converter = new Bower2Composer();
        $converter->out = $this;

        $composerPackages = [];

        $pCount = 0;
        $vCount = 0;
        $eCount = 0;

        $limit = 1500;
        foreach($bowerJson as $package) {
            $this->stderr("Converting {$package['name']}..." . ($this->verbose ? "\n" : ""), Console::BOLD);
            try {

                list($name, $packages) = $converter->convert($package);
                $composerPackages[$name] = $packages;

                $pCount++;
                $vCount += count($packages);

            } catch(\Exception $e) {
                $this->out('failed to convert package: ' . $package['name'] . "\n", "failed.\n", [Console::FG_RED]);
                $this->out($e->getMessage() . "\n", '');
                $eCount++;
                continue;
            }

            $this->out('', "\n");
        	if ($limit-- <= 0) {
        		break;
        	}
        }

        //print_r($composerPackages);

        echo json_encode(['packages' => $composerPackages]);

        $this->stderr("\nadded $pCount packages in $vCount versions. Failed to convert $eCount packages.\n\n");

    }

    public function out($verbose, $short, $style = [])
    {
        if ($this->verbose) {
            call_user_func_array([$this, 'stderr'], array_merge([$verbose], $style));
        } else {
            call_user_func_array([$this, 'stderr'], array_merge([$short], $style));
        }
    }

    public function options($actionId)
    {
        return array_merge(parent::options($actionId), ['verbose']);
    }
}
