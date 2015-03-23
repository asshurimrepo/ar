<?php
/**
 * Created by IntelliJ IDEA.
 * User: platon
 * Date: 18.03.15
 * Time: 16:06
 */

namespace Heonozis\AR;

use Illuminate\Support\Facades\DB;

trait Settings {

    public static function saveSettings($settings) {
        $settings_class = new self;
        foreach($settings as $k=>$s) {
            DB::table($settings_class->getTable())->where('key', '=', $k)->update(['value' => $s]);

        }
    }

    public static function getSettings($name = null) {
        $settings_class = new self;
        $settings = array();
        $setings_array =DB::table($settings_class->getTable())->get();
        foreach ($setings_array as $s) {
            $settings[$s->key] = $s->value;
        }
        if ($name == null) {
            return $settings;
        } else {
            return $settings[$name];
        }
    }



}