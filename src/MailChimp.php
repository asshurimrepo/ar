<?php
/**
 * Created by IntelliJ IDEA.
 * User: platon
 * Date: 20.03.15
 * Time: 16:28
 */

namespace Heonozis\AR;

/**
 * Class MailChimp
 * @package Heonozis\AR
 */
class MailChimp extends MailChimpAPI
{

    /**
     * Subscribe user
     *
     * @param $email
     * @param $name
     * @return bool
     */
    public static function subscribe($email, $name)
    {

        try {
            $api_key = MailChimpSettings::getSettings('api_key');
            $list_name = MailChimpSettings::getSettings('list_name');

	if($list_name == ''){
	return 1;	
}
            $mc = new self($api_key);

            $list_id = $mc->call('lists/list', array(
                'name' => $list_name
            ))['data'][0]['id'];

            $subscriber = $mc->call('lists/subscribe', array(
                'id' => $list_id,
                'email' => array('email' => $email),
                'merge_vars' => array(
                    'FNAME' => $name
                ),
                'send_welcome' => false,
		'double_optin' => false
            ));


        } catch (Exception $e) {

            //uncoment to enable error messages
            //throw $e;

            return false;

        }

    }

    /**
     * Returns list of subscribers lists of account
     *
     * @return array
     * @throws \Exception
     */
    public static function lists()
    {

        try {
            $api_key = MailChimpSettings::getSettings('api_key');

            $mc = new self($api_key);

            $lists = $mc->call('lists/list');

            $lists_names = array();

            foreach ($lists['data'] as $list) {
                $lists_names[$list['name']] = $list['name'];
            }

            return $lists_names;

        } catch (Exception $e) {

            //uncoment to enable error messages
            //throw $e;

            return false;
        }

    }

    /**
     * Get MailChimp settings from DB
     *
     * You can specify name of settings (if null - all settings)
     * @param null $name
     * @return array
     */
    public static function getSettings($name = null)
    {

        return MailChimpSettings::getSettings($name);

    }

    /**
     * Save array of settings to DB
     * @param $array
     */
    public static function saveSettings($array)
    {

        MailChimpSettings::getSettings($array);

    }

}
