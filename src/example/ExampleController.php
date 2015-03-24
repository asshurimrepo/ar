<?php
use Illuminate\Support\Facades\Input;
use Heonozis\AR\Aweber;
use Heonozis\AR\AweberSettings;
use Heonozis\AR\GetResponse;
use Heonozis\AR\GetResponseSettings;
use Heonozis\AR\MailChimp;
use Heonozis\AR\MailChimpSettings;
use \Controller;



class ExampleController extends Controller
{

    public function indexAweber()
    {
        $page_info = array(
            'title' => 'Settings',
            'description' => 'Aweber Settings'
        );

        $settings = AweberSettings::getSettings();
        $lists = Aweber::lists();

        if($lists == false) {
            $lists = array('' => 'No lists');
        }

        return view('admin.settings.autoresponders.aweber', array(
            'settings' => $settings,
            'page_info' => $page_info,
            'lists' => $lists
        ));

    }

    public function indexGetresponse()
    {
        $page_info = array(
            'title' => 'Settings',
            'description' => 'GetResponse Settings'
        );

        $settings = GetResponseSettings::getSettings();
        $campaigns = GetResponse::campaigns();

        if($campaigns == false) {
            $campaigns = array('' => 'No campaigns');
        }

        if(!is_array($campaigns)){
            $campaigns = array('' => 'No campaigns');
        }

        return view('admin.settings.autoresponders.getresponse', array(
            'settings' => $settings,
            'page_info' => $page_info,
            'campaigns' => $campaigns
        ));

    }

    /**
     * @return \Illuminate\View\View
     * @throws \Exception
     */
    public function indexMailchimp()
    {
        $page_info = array(
            'title' => 'Settings',
            'description' => 'MailChimp Settings'
        );

        $settings = MailChimpSettings::getSettings();
        $lists = MailChimp::lists();

        if($lists == false) {
            $lists = array('' => 'No lists');
        }

        return view('admin.settings.autoresponders.mailchimp', array(
            'settings' => $settings,
            'page_info' => $page_info,
            'lists' => $lists
        ));

    }

     /**
     * Update the specified resource in storage.
     * @return Response
     * @internal param int $id
     */
    public function updateAweber()
    {
        try {

            $input = Input::all();
            $settings = array();
            $settings['customer_key'] = $input['customer_key'];
            $settings['customer_secret'] = $input['customer_secret'];

            AweberSettings::saveSettings($settings);

            //Every time settings (key/secret) changes we need new access key/secret
            //that's why we need repeat authorization process every time settings saves
            $aweber = Aweber::make($input['customer_key'], $input['customer_secret']);

            //this page you'll be redirected with $oauth_token and $oauth_verifier
            // after authentication on AWeber side
            $callbackUrl = route('admin.settings.aweber.authorize');

            //save request token on cookies
            list($requestKey, $requesSecret) = $aweber->getRequestToken($callbackUrl);
            $authorizeUrl = $aweber->getAuthorizeUrl();
            setcookie('request_secret', $requesSecret);

            //redirect to AWeber authorization page
            return redirect()->away($authorizeUrl);


        } catch (\Exception $e) {

            return redirect()->back()->with('error', 'Woops! There was an error... Check your settings');

        }

        return redirect()->back()->with('success', 'Updated successfully');
    }

    //Aweber authorization via oAuth and getting access key
    public function authorizeAweber()
    {

        //oaut_token and oauth_verifier from AWeber authorization page
        if(Input::has('oauth_token', 'oauth_verifier')) {
            try {
                //get access token/secret
                $access = Aweber::getAuthorize(Input::get('oauth_token'), Input::get('oauth_verifier'));

                //save access token/secret
                AweberSettings::saveSettings(array(
                    'access_key' => $access->token,
                    'access_secret' => $access->secret
                ));

                return redirect()->route('admin.settings.aweber')->with('success', 'Updated successfully');
            }catch (\Exception $e) {
                return redirect()->route('admin.settings.aweber')->with('error', 'Woops, there was an error! Check your settings...');
            }

        }
        return redirect()->route('admin.settings.aweber')->with('error', 'Woops, there was an error! Check your settings...');
    }


    /**
     * Update the specified resource in storage.
     * @return Response
     * @internal param int $id
     */
    public function updateGetresponse()
    {
        try {
            $input = Input::all();
            $settings = array();
            $settings['api_key'] = $input['api_key'];
            $settings['campaign_name'] = $input['campaign_name'];


            GetResponseSettings::saveSettings($settings);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Woops! There was an error... Try again later');

        }
        return redirect()->back()->with('success', 'Updated successfully');
    }

    /**
     * Update the specified resource in storage.
     * @return Response
     * @internal param int $id
     */
    public function updateMailchimp()
    {
        try {
            $input = Input::all();
            $settings = array();
            $settings['api_key'] = $input['api_key'];
            $settings['list_name'] = $input['list_name'];

            MailChimpSettings::saveSettings($settings);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Woops! There was an error... Try again later');

        }
        return redirect()->back()->with('success', 'Updated successfully');
    }
}
