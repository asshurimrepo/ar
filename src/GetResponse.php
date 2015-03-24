<?php
/**
 * Created by IntelliJ IDEA.
 * User: platon
 * Date: 19.03.15
 * Time: 19:48
 */
namespace Heonozis\AR;


use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class GetResponse
 * @package Heonozis\AR
 */
class GetResponse
{


    /**
     * Function for user subscription.
     *
     * @param $email
     * @param $name
     * @return bool
     * @throws HttpException
     */
    public static function subscribe($email, $name)
    {
        try {
            $campaignNeme = GetResponseSettings::getSettings('campaign_name');
            $api_url = 'http://api2.getresponse.com';
            $api_key = GetResponseSettings::getSettings('api_key');

            $client = new jsonRPCClient($api_url);

            //Get campaign by name in settings
            $campaigns = (array)$client->get_campaigns($api_key, array(
                'name' => array(
                    'EQUALS' => $campaignNeme
                )));

            $campaignID = array_keys($campaigns)[0];

            $ret = $client->add_contact($api_key, array(
                'campaign' => $campaignID,
                'name' => $name,
                'email' => $email
            ));


        } catch (\Exception $e) {

            //uncoment to enable error messages
            //throw $e;

            return false;

        }
    }

    /**
     *Get all campaigns of user.
     *
     * @return array
     */
    public static function campaigns()
    {
        try {
            $api_url = 'http://api2.getresponse.com';
            $client = new jsonRPCClient($api_url);
            $api_key = GetResponseSettings::getSettings('api_key');


            $campaigns = (array)$client->get_campaigns($api_key);

            //if error
            if (array_key_exists('error', $campaigns) || count($campaigns) == 0) {
                return false;
            } else {
                $campaigns_names = array();

                    foreach ($campaigns as $campaign) {
                        $campaigns_names[$campaign['name']] = $campaign['name'];
                    }

                    return $campaigns_names;

            }
        } catch (\Exception $e) {

            //uncoment to enable error messages
            //throw $e;

            return false;

        }

    }

    /**
     * Get GetResponse settings from DB
     *
     * You can specify name of settings (if null - all settings)
     * @param null $name
     */
    public static function getSettings($name = null)
    {

        GetResponseSettings::getSettings($name);

    }

    /**
     * Save array of settings to DB
     *
     * @param $array
     */
    public static function saveSettings($array)
    {

        GetResponseSettings::getSettings($array);

    }

}