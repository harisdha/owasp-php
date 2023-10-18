<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Zap\Zapv2;

class HomeController extends Controller
{
    //
    public function index()
    {
        $api_key = "";
        $target = "https://www.inticipta.com/";

        $zap = new Zapv2('tcp://localhost:9099', $api_key);

        $version = @$zap->core->version();

        if (is_null($version))
        {
            echo "PHP API error<br/>";
            exit();
        }
        else
        {
            echo "version: ${version}<br/>";
        }

        echo "Spidering target ${target}<br/>";

        // Response JSON looks like {"scan":"1"}
        $scan_id = $zap->spider->scan($target, $api_key);

        $count = 0;

        while (true) {

            if ($count > 10) exit();

            // Response JSON looks like {"status":"50"}
            $progress = intval($zap->spider->status($scan_id));

            printf("Spider progress %d<br/>", $progress);

            if ($progress >= 100) 
                break;

            sleep(2);
            $count++;

        }

        echo "Spider completed<br/>";

        // Give the passive scanner a chance to finish
        sleep(5);


        // Report the results
        echo "Hosts: " . implode(",", $zap->core->hosts()) . "<br/>";
        $alerts = $zap->core->alerts($target, "", "");
        echo "Alerts (" . count($alerts) . "):<br/>";
        print_r($alerts);

        return view('home/index');
        
    }
}
