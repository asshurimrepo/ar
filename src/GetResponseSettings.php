<?php

namespace Heonozis\AR;

use Illuminate\Database\Eloquent\Model;

class GetResponseSettings extends Model
{
    use Settings;

    protected $table = 'ar_getresponse_settings';

    protected $fillable = array('key', 'value');

    public static function campaignId() {
        $campaignName = self::getSettings('campaign_name');

        $api_key = GetResponseSettings::getSettings('api_key');
        $api_url = 'http://api2.getresponse.com';

        $client = new jsonRPCClient($api_url);

        $campaign = $client->get_campaigns($api_key, array(
            'name' => array('EQUALS' => $campaignName)
        ));

        $array = array_values($campaign);
        dd($array);
    }
}
