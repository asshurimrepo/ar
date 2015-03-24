# heonozis/ar

Authoresponders package for Laravel 5

##Requirements:

* [Laravel 5] - PHP MVC framework.
* CURL - PHP extencion

##Installation:

#####1. Add repository with package to your project composer.json:

```sh
	"repositories": [
	  {
		"type": "vcs",
		"url": "https://github.com/Heonozis/ar.git"
	  }
	],
```

#####2. Add package to require section of composer.json:
```
"require": {
        ...
	 	"heonozis/ar": "dev-master"
	},
```

#####3. Add ArServiceProvider to config/app.php:

```
    'providers' => [
        ...
        'Heonozis\AR\ArServiceProvider',
        ]
```

#####4. Update composer dependencies:

```
$ composer update
```



#####5. Publish migrations:

```
$ php artisan vendor:publish
```

#####6. Migrate:

```
$ php artisan migrate
```
####Done! 

##Configure:
configuration in this package done via DB.
##### MailChimp:

Fields:
- api_key - Search here for [MailChimp api key]
- list_name - subscribers list name

You can `` set`` settings via ``MailChimp::saveSettings($array)`` function,
where ``$array = array('api_key' => "YOUR API KEY", 'list_name' => "YOUR LIST NAME")``

You can ``get`` array of all settings via ``MailChimp::getSettings()`` function, or get only one field via ``MailChimp::getSettings($field_name)``

##### GetResponse:

Fields:
- api_key - Search here for [GetResponse api key]
- campaign_name - cmpaign name

You can ``set`` settings via ``GetResponse::saveSettings($array)`` function,
where ``$array = array('api_key' => "YOUR API KEY", 'campaign_name' => "YOUR CAMPAIGN NAME")``

You can ``get`` array of all settings via ``GetResponse::getSettings()`` function, or get only one field via ``GetResponse::getSettings($field_name)``

##### AWeber:

Fields:
- customer_key - [AWeber customer key]
- customer_secret - customers secret ( see [AWeber customer key] )
- access_key - access key got from oAuth process via ``Aweber::getAuthorize($oauth_token, $oauth_verifier)``. How get ``$oauth_token`` and ``$oauth_verifier`` read [here].
- access_secret - access secret got from oAuth process as access_key
- list_name - name of subscribers list you want to update

You can ``set`` settings via ``Aweber::saveSettings($array)`` function,
where ``$array = array('customer_key' => "CUSTOMER KEY",'customer_secret' => "CUSTOMER SECRET",)``

You can ``get`` array of all settings via ``Aweeber::getSettings()`` function, or get only one field via ``Aweeber::getSettings($field_name)``
##Usage (Only after full configuration):
##### MailChimp:
* Subscribe User  ```MailChimp::subscribe($email, $name)```
* Get list of lists ```MailChimp::lists()```

##### GetResponse:
* Subscribe User  ```GetResponse::subscribe($email, $name)```
* Get list of campaigns ```GetResponse::campaigns()```

##### AWeber:
* Subscribe User  ```Aweber::subscribe($email, $name)```
* Get list of campaigns ```Aweber::lists()```

[Laravel 5]:http://laravel.com/
[MailChimp api key]:https://login.mailchimp.com/?referrer=%2Faccount%2Fapi-key-popup%2F
[GetResponse api key]:http://support.getresponse.com/faq/where-i-find-api-key
[AWeber customer key]:https://labs.aweber.com/getting_started/private
[here]:https://labs.aweber.com/docs/authentication
