<?php
/**
 * Created by IntelliJ IDEA.
 * User: platon
 * Date: 19.03.15
 * Time: 14:03
 */
namespace Heonozis\AR;

require_once('Aweber/aweber.php');

use AWeberAPI;

/**
 * Class Aweber
 * @package Heonozis\AR
 */
class Aweber {

    /**
     * Get authorize by token and verifier
     *
     * @param $oauth_token
     * @param $oauth_verifier
     * @return \stdClass
     */
    public static function getAuthorize($oauth_token, $oauth_verifier) {

        try {
            $customer_key = AweberSettings::getSettings('customer_key');
            $customer_secret = AweberSettings::getSettings('customer_secret');
            $aweber = Aweber::make($customer_key, $customer_secret);

            $aweber->user->requestToken = $oauth_token;
            $aweber->user->verifier = $oauth_verifier;

            $aweber->user->tokenSecret = $_COOKIE['request_secret'];

            list($accessToken, $accessTokenSecret) = $aweber->getAccessToken();

            $access = new \stdClass();
            $access->token = $accessToken;
            $access->secret = $accessTokenSecret;

            return $access;

        } catch(Exception $e) {
            return false;
        }

    }

    /**
     * Subscribe user
     *
     * @param $email
     * @param $name
     * @return bool
     */
    public static function subscribe($email, $name) {

        try {

            $customer_key = AweberSettings::getSettings('customer_key');
            $customer_secret = AweberSettings::getSettings('customer_secret');
            $access_key = AweberSettings::getSettings('access_key');
            $access_secret = AweberSettings::getSettings('access_secret');
            $list_name = AweberSettings::getSettings('list_name');

            $aweber = self::make($customer_key, $customer_secret);

            $account = $aweber->getAccount($access_key, $access_secret);


            $lists = $account->lists->find(array('name' => $list_name));
            $list = $lists[0];

            $new_subscriber = $list->subscribers->create(array(
                'name' => $name,
                'email' => $email
            ));

        } catch(Exception $e) {

            //uncoment to enable error messages
            //throw $e;

            return false;

        }

    }

    //make instance of AWeber
    /**
     * Make instance of AWeberAPI
     *
     * @param $customer_key
     * @param $customer_secret
     * @return AWeberAPI
     */
    public static function make($customer_key, $customer_secret) {

        return $aweber = new AWeberAPI($customer_key, $customer_secret);

    }

    /**
     * Returns list of subscribers lists of account
     *
     * @return array
     * @throws \Exception
     */
    public static function lists(){

        try {
            $customer_key = AweberSettings::getSettings('customer_key');
            $customer_secret = AweberSettings::getSettings('customer_secret');
            $access_key = AweberSettings::getSettings('access_key');
            $access_secret = AweberSettings::getSettings('access_secret');

            $aweber = self::make($customer_key, $customer_secret);

            $account = $aweber->getAccount($access_key, $access_secret);

            $lists = $account->lists->data['entries'];

            $lists_names = array();

            foreach ($lists as $list) {
                $lists_names[$list['name']] = $list['name'];
            }

            return $lists_names;
        } catch (Exception $e){

            //uncoment to enable error messages
            //throw $e;

            return false;

        }
    }


    /**
     * Get AWeber settings from DB
     *
     * You can specify name of settings (if null - all settings)
     * @param null $name
     * @return array
     */
    public static function getSettings($name = null) {

        return AweberSettings::getSettings($name);

    }

    /**
     * Save array of settings to DB
     * @param $array
     */
    public static function saveSettings($array) {

        AweberSettings::getSettings($array);

    }
}