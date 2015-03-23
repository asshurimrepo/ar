<?php
/**
 * Created by IntelliJ IDEA.
 * User: platon
 * Date: 19.03.15
 * Time: 14:03
 */
namespace Heonozis\AR;

use Illuminate\Support\Facades\Request;

require_once('Aweber/aweber.php');
use AWeberAPI;

class Aweber {

    //get authorize by oAuth token
    public static function getAuthorize($oauth_token, $oauth_verifier) {

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

    }

    //subscribe user
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

        } catch(\Exception $e) {

        }

    }

    //make instance of AWeber
    public static function make($customer_key, $customer_secret) {

        return $aweber = new AWeberAPI($customer_key, $customer_secret);

    }

    //get lists
    public static function lists(){

        $customer_key = AweberSettings::getSettings('customer_key');
        $customer_secret = AweberSettings::getSettings('customer_secret');
        $access_key = AweberSettings::getSettings('access_key');
        $access_secret = AweberSettings::getSettings('access_secret');

        $aweber = self::make($customer_key, $customer_secret);

        $account = $aweber->getAccount($access_key, $access_secret);

        $lists = $account->lists->data['entries'];

        $lists_names = array();

        foreach($lists as $list) {
            $lists_names[$list['name']] = $list['name'];
        }

        return $lists_names;    }

}