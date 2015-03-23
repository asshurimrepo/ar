<?php
/**
 * Created by IntelliJ IDEA.
 * User: platon
 * Date: 19.03.15
 * Time: 19:48
 */
namespace Heonozis\AR;


class GetResponse {

    public static function subscribe($name, $email) {
        $campaignNeme = GetResponseSettings::getSettings('campaign_name');
        $api_url = 'http://api2.getresponse.com';
        $api_key = GetResponseSettings::getSettings('api_key');

        $client = new jsonRPCClient($api_url);

        $campaigns = (array)$client->get_campaigns($api_key, array(
            'name' => array(
                'EQUALS' => $campaignNeme
            )));
        $campaignID = array_keys($campaigns)[0];

        $contact = $client->get_contacts($api_key, array(
            'campaigns' => array($campaignID),
            'email' => array('EQUALS' => $email)
        ));

        $contact_arr = array_values($contact);

            $ret = $client->add_contact($api_key, array(
                'campaign' => $campaignID,
                'name' => $name,
                'email' => $email
            ));

    }

    public static function campaigns() {
        $api_url = 'http://api2.getresponse.com';
        $client = new jsonRPCClient($api_url);
        $api_key = GetResponseSettings::getSettings('api_key');

        if($api_key != '')
        {
            $campaigns = (array)$client->get_campaigns($api_key);

            $campaigns_names = array();
            foreach($campaigns as $campaign) {
                $campaigns_names[$campaign['name']] = $campaign['name'];
            }

            return $campaigns_names;
        }

    }

    public static function getSettings($name = null) {

        GetResponseSettings::getSettings($name);

    }

    public static function saveSettings($array) {

        GetResponseSettings::getSettings($array);

    }

}