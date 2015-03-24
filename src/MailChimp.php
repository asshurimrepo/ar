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
class MailChimp extends MailChimpAPI{

    /**
     * Subscribe user
     *
     * @param $email
     * @param $name
     */
    public static function subscribe($email, $name) {

        try {
            $api_key = MailChimpSettings::getSettings('api_key');
            $list_name = MailChimpSettings::getSettings('list_name');

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
                'send_welcome' => false
            ));


        } catch(\Exception $e) {

        }

    }

    /**
     * Returns list of subscribers lists of account
     *
     * @return array
     * @throws \Exception
     */
    public static function lists(){

        try {
            $api_key = MailChimpSettings::getSettings('api_key');
            $list_name = MailChimpSettings::getSettings('list_name');

            $mc = new self($api_key);

            $lists = $mc->call('lists/list');

            $lists_names = array();

            foreach($lists['data'] as $list) {
                $lists_names[$list['name']] = $list['name'];
            }

            return $lists_names;


        } catch(\Exception $e) {
            throw $e;
        }

    }

    /**
     * Get MailChimp settings from DB
     *
     * You can specify name of settings (if null - all settings)
     * @param null $name
     */
    public static function getSettings($name = null) {

        MailChimpSettings::getSettings($name);

    }

    /**
     * Save array of settings to DB
     * @param $array
     */
    public static function saveSettings($array) {

        MailChimpSettings::getSettings($array);

    }

}