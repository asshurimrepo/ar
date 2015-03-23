<?php

namespace Heonozis\AR;

use ArrayAccess;
use Iterator;
use Countable;

/**
 * AWeberServiceProvider
 *
 * Provides specific AWeber information or implementing OAuth.
 * @uses OAuthServiceProvider
 * @package
 * @version $id$
 */
class AWeberServiceProvider implements OAuthServiceProvider {

    /**
     * @var String Location for API calls
     */
    public $baseUri = 'https://api.aweber.com/1.0';

    /**
     * @var String Location to request an access token
     */
    public $accessTokenUrl = 'https://auth.aweber.com/1.0/oauth/access_token';

    /**
     * @var String Location to authorize an Application
     */
    public $authorizeUrl = 'https://auth.aweber.com/1.0/oauth/authorize';

    /**
     * @var String Location to request a request token
     */
    public $requestTokenUrl = 'https://auth.aweber.com/1.0/oauth/request_token';


    public function getBaseUri() {
        return $this->baseUri;
    }

    public function removeBaseUri($url) {
        return str_replace($this->getBaseUri(), '', $url);
    }

    public function getAccessTokenUrl() {
        return $this->accessTokenUrl;
    }

    public function getAuthorizeUrl() {
        return $this->authorizeUrl;
    }

    public function getRequestTokenUrl() {
        return $this->requestTokenUrl;
    }

    public function getAuthTokenFromUrl() { return ''; }
    public function getUserData() { return ''; }

}

/**
 * AWeberAPIBase
 *
 * Base object that all AWeberAPI objects inherit from.  Allows specific pieces
 * of functionality to be shared across any object in the API, such as the
 * ability to introspect the collections map.
 *
 * @package
 * @version $id$
 */
class AWeberAPIBase {

    /**
     * Maintains data about what children collections a given object type
     * contains.
     */
    static protected $_collectionMap = array(
        'account'              => array('lists', 'integrations'),
        'broadcast_campaign'   => array('links', 'messages', 'stats'),
        'followup_campaign'    => array('links', 'messages', 'stats'),
        'link'                 => array('clicks'),
        'list'                 => array('campaigns', 'custom_fields', 'subscribers',
                                        'web_forms', 'web_form_split_tests'),
        'web_form'             => array(),
        'web_form_split_test'  => array('components'),
    );

    /**
     * loadFromUrl
     *
     * Creates an object, either collection or entry, based on the given
     * URL.
     *
     * @param mixed $url    URL for this request
     * @access public
     * @return AWeberEntry or AWeberCollection
     */
    public function loadFromUrl($url) {
        $data = $this->adapter->request('GET', $url);
        return $this->readResponse($data, $url);
    }

    protected function _cleanUrl($url) {
        return str_replace($this->adapter->app->getBaseUri(), '', $url);
    }

    /**
     * readResponse
     *
     * Interprets a response, and creates the appropriate object from it.
     * @param mixed $response   Data returned from a request to the AWeberAPI
     * @param mixed $url        URL that this data was requested from
     * @access protected
     * @return mixed
     */
    protected function readResponse($response, $url) {
        $this->adapter->parseAsError($response);
        if (!empty($response['id'])) {
            return new AWeberEntry($response, $url, $this->adapter);
        } else if (array_key_exists('entries', $response)) {
            return new AWeberCollection($response, $url, $this->adapter);
        }
        return false;
    }
}


use PhpSpec\Exception\Exception;

class AWeberException extends Exception { }

/**
 * Thrown when the API returns an error. (HTTP status >= 400)
 *
 *
 * @uses AWeberException
 * @package
 * @version $id$
 */
class AWeberAPIException extends AWeberException {

    public $type;
    public $status;
    public $message;
    public $documentation_url;
    public $url;

    public function __construct($error, $url) {
        // record specific details of the API exception for processing
        $this->url = $url;
        $this->type = $error['type'];
        $this->status = array_key_exists('status', $error) ? $error['status'] : '';
        $this->message = $error['message'];
        $this->documentation_url = $error['documentation_url'];

        parent::__construct($this->message);
    }
}

/**
 * Thrown when attempting to use a resource that is not implemented.
 *
 * @uses AWeberException
 * @package
 * @version $id$
 */
class AWeberResourceNotImplemented extends AWeberException {

    public function __construct($object, $value) {
        $this->object = $object;
        $this->value = $value;
        parent::__construct("Resource \"{$value}\" is not implemented on this resource.");
    }
}

/**
 * AWeberMethodNotImplemented
 *
 * Thrown when attempting to call a method that is not implemented for a resource
 * / collection.  Differs from standard method not defined errors, as this will
 * be thrown when the method is infact implemented on the base class, but the
 * current resource type does not provide access to that method (ie calling
 * getByMessageNumber on a web_forms collection).
 *
 * @uses AWeberException
 * @package
 * @version $id$
 */
class AWeberMethodNotImplemented extends AWeberException {

    public function __construct($object) {
        $this->object = $object;
        parent::__construct("This method is not implemented by the current resource.");

    }
}

/**
 * AWeberOAuthException
 *
 * OAuth exception, as generated by an API JSON error response
 * @uses AWeberException
 * @package
 * @version $id$
 */
class AWeberOAuthException extends AWeberException {

    public function __construct($type, $message) {
        $this->type = $type;
        $this->message = $message;
        parent::__construct("{$type}: {$message}");
    }
}

/**
 * AWeberOAuthDataMissing
 *
 * Used when a specific piece or pieces of data was not found in the
 * response. This differs from the exception that might be thrown as
 * an AWeberOAuthException when parameters are not provided because
 * it is not the servers' expectations that were not met, but rather
 * the expecations of the client were not met by the server.
 *
 * @uses AWeberException
 * @package
 * @version $id$
 */
class AWeberOAuthDataMissing extends AWeberException {

    public function __construct($missing) {
        if (!is_array($missing)) $missing = array($missing);
        $this->missing = $missing;
        $required = join(', ', $this->missing);
        parent::__construct("OAuthDataMissing: Response was expected to contain: {$required}");

    }
}

/**
 * AWeberResponseError
 *
 * This is raised when the server returns a non-JSON response. This
 * should only occur when there is a server or some type of connectivity
 * issue.
 *
 * @uses AWeberException
 * @package
 * @version $id$
 */
class AWeberResponseError extends AWeberException {

    public function __construct($uri) {
        $this->uri = $uri;
        parent::__construct("Request for {$uri} did not respond properly.");
    }

}

interface AWeberOAuthAdapter {

    public function request($method, $uri, $data = array());
    public function getRequestToken($callbackUrl=false);

}


class CurlResponse
{
    public $body = '';
    public $headers = array();

    public function __construct($response)
    {
        # Extract headers from response
        $pattern = '#HTTP/\d\.\d.*?$.*?\r\n\r\n#ims';
        preg_match_all($pattern, $response, $matches);
        $headers = explode("\r\n", str_replace("\r\n\r\n", '', array_pop($matches[0])));

        # Extract the version and status from the first header
        $version_and_status = array_shift($headers);
        preg_match('#HTTP/(\d\.\d)\s(\d\d\d)\s(.*)#', $version_and_status, $matches);
        $this->headers['Http-Version'] = $matches[1];
        $this->headers['Status-Code'] = $matches[2];
        $this->headers['Status'] = $matches[2].' '.$matches[3];

        # Convert headers into an associative array
        foreach ($headers as $header) {
            preg_match('#(.*?)\:\s(.*)#', $header, $matches);
            $this->headers[$matches[1]] = $matches[2];
        }

        # Remove the headers from the response body
        $this->body = preg_replace($pattern, '', $response);
    }

    public function __toString()
    {
        return $this->body;
    }

    public function headers(){
        return $this->headers;
    }
}


/**
 * OAuthServiceProvider
 *
 * Represents the service provider in the OAuth authentication model.
 * The class that implements the service provider will contain the
 * specific knowledge about the API we are interfacing with, and
 * provide useful methods for interfacing with its API.
 *
 * For example, an OAuthServiceProvider would know the URLs necessary
 * to perform specific actions, the type of data that the API calls
 * would return, and would be responsible for manipulating the results
 * into a useful manner.
 *
 * It should be noted that the methods enforced by the OAuthServiceProvider
 * interface are made so that it can interact with our OAuthApplication
 * cleanly, rather than from a general use perspective, though some
 * methods for those purposes do exists (such as getUserData).
 *
 * @package
 * @version $id$
 */
interface OAuthServiceProvider {

    public function getAccessTokenUrl();
    public function getAuthorizeUrl();
    public function getRequestTokenUrl();
    public function getAuthTokenFromUrl();
    public function getBaseUri();
    public function getUserData();

}

/**
 * OAuthApplication
 *
 * Base class to represent an OAuthConsumer application.  This class is
 * intended to be extended and modified for each ServiceProvider. Each
 * OAuthServiceProvider should have a complementary OAuthApplication
 *
 * The OAuthApplication class should contain any details on preparing
 * requires that is unique or specific to that specific service provider's
 * implementation of the OAuth model.
 *
 * This base class is based on OAuth 1.0, designed with AWeber's implementation
 * as a model.  An OAuthApplication built to work with a different service
 * provider (especially an OAuth2.0 Application) may alter or bypass portions
 * of the logic in this class to meet the needs of the service provider it
 * is designed to interface with.
 *
 * @package
 * @version $id$
 */
class OAuthApplication implements AWeberOAuthAdapter {
    public $debug = false;

    public $userAgent = 'AWeber OAuth Consumer Application 1.0 - https://labs.aweber.com/';

    public $format = false;

    public $requiresTokenSecret = true;

    public $signatureMethod = 'HMAC-SHA1';
    public $version         = '1.0';

    public $curl = false;

    /**
     * @var OAuthUser User currently interacting with the service provider
     */
    public $user = false;

    // Data binding this OAuthApplication to the consumer application it is acting
    // as a proxy for
    public $consumerKey = false;
    public $consumerSecret = false;

    /**
     * __construct
     *
     * Create a new OAuthApplication, based on an OAuthServiceProvider
     * @access public
     * @return void
     */
    public function __construct($parentApp = false) {
        if ($parentApp) {
            if (!is_a($parentApp, 'OAuthServiceProvider')) {
                throw new Exception('Parent App must be a valid OAuthServiceProvider!');
            }
            $this->app = $parentApp;
        }
        $this->user = new OAuthUser();
        $this->curl = new CurlObject();
    }

    /**
     * request
     *
     * Implemented for a standard OAuth adapter interface
     * @param mixed $method
     * @param mixed $uri
     * @param array $data
     * @param array $options
     * @access public
     * @return void
     */
    public function request($method, $uri, $data = array(), $options = array()) {
        $uri = $this->app->removeBaseUri($uri);
        $url = $this->app->getBaseUri() . $uri;

        # WAweberNING: non-primative items in data must be json serialized in GET and POST.
        if ($method == 'POST' or $method == 'GET') {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $data[$key] = json_encode($value);
                }
            }
        }

        $response = $this->makeRequest($method, $url, $data);
        if (!empty($options['return'])) {
            if ($options['return'] == 'status') {
                return $response->headers['Status-Code'];
            }
            if ($options['return'] == 'headers') {
                return $response->headers;
            }
            if ($options['return'] == 'integer') {
                return intval($response->body);
            }
        }

        $data = json_decode($response->body, true);

        if (empty($options['allow_empty']) && !isset($data)) {
            throw new AWeberResponseError($uri);
        }
        return $data;
    }

    /**
     * getRequestToken
     *
     * Gets a new request token / secret for this user.
     * @access public
     * @return void
     */
    public function getRequestToken($callbackUrl=false) {
        $data = ($callbackUrl)? array('oauth_callback' => $callbackUrl) : array();
        $resp = $this->makeRequest('POST', $this->app->getRequestTokenUrl(), $data);
        $data = $this->parseResponse($resp);
        $this->requiredFromResponse($data, array('oauth_token', 'oauth_token_secret'));
        $this->user->requestToken = $data['oauth_token'];
        $this->user->tokenSecret  = $data['oauth_token_secret'];
        return $data['oauth_token'];
    }

    /**
     * getAccessToken
     *
     * Makes a request for access tokens.  Requires that the current user has an authorized
     * token and token secret.
     *
     * @access public
     * @return void
     */
    public function getAccessToken() {
        $resp = $this->makeRequest('POST', $this->app->getAccessTokenUrl(),
            array('oauth_verifier' => $this->user->verifier)
        );
        $data = $this->parseResponse($resp);
        $this->requiredFromResponse($data, array('oauth_token', 'oauth_token_secret'));

        if (empty($data['oauth_token'])) {
            throw new AWeberOAuthDataMissing('oauth_token');
        }

        $this->user->accessToken = $data['oauth_token'];
        $this->user->tokenSecret = $data['oauth_token_secret'];
        return array($data['oauth_token'], $data['oauth_token_secret']);
    }

    /**
     * parseAsError
     *
     * Checks if response is an error.  If it is, raise an appropriately
     * configured exception.
     *
     * @param mixed $response   Data returned from the server, in array form
     * @access public
     * @throws AWeberOAuthException
     * @return void
     */
    public function parseAsError($response) {
        if (!empty($response['error'])) {
            throw new AWeberOAuthException($response['error']['type'],
                $response['error']['message']);
        }
    }

    /**
     * requiredFromResponse
     *
     * Enforce that all the fields in requiredFields are present and not
     * empty in data.  If a required field is empty, throw an exception.
     *
     * @param mixed $data               Array of data
     * @param mixed $requiredFields     Array of required field names.
     * @access protected
     * @return void
     */
    protected function requiredFromResponse($data, $requiredFields) {
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new AWeberOAuthDataMissing($field);
            }
        }
    }

    /**
     * get
     *
     * Make a get request.  Used to exchange user tokens with serice provider.
     * @param mixed $url        URL to make a get request from.
     * @param array $data       Data for the request.
     * @access protected
     * @return void
     */
    protected function get($url, $data) {
        $url = $this->_addParametersToUrl($url, $data);
        $handle = $this->curl->init($url);
        $resp = $this->_sendRequest($handle);
        return $resp;
    }

    /**
     * _addParametersToUrl
     *
     * Adds the parameters in associative array $data to the
     * given URL
     * @param String $url       URL
     * @param array $data       Parameters to be added as a query string to
     *      the URL provided
     * @access protected
     * @return void
     */
    protected function _addParametersToUrl($url, $data) {
        if (!empty($data)) {
            if (strpos($url, '?') === false) {
                $url .= '?'.$this->buildData($data);
            } else {
                $url .= '&'.$this->buildData($data);
            }
        }
        return $url;
    }

    /**
     * generateNonce
     *
     * Generates a 'nonce', which is a unique request id based on the
     * timestamp.  If no timestamp is provided, generate one.
     * @param mixed $timestamp Either a timestamp (epoch seconds) or false,
     *  in which case it will generate a timestamp.
     * @access public
     * @return string   Returns a unique nonce
     */
    public function generateNonce($timestamp = false) {
        if (!$timestamp) $timestamp = $this->generateTimestamp();
        return md5($timestamp.'-'.rand(10000,99999).'-'.uniqid());
    }

    /**
     * generateTimestamp
     *
     * Generates a timestamp, in seconds
     * @access public
     * @return int Timestamp, in epoch seconds
     */
    public function generateTimestamp() {
        return time();
    }

    /**
     * createSignature
     *
     * Creates a signature on the signature base and the signature key
     * @param mixed $sigBase    Base string of data to sign
     * @param mixed $sigKey     Key to sign the data with
     * @access public
     * @return string   The signature
     */
    public function createSignature($sigBase, $sigKey) {
        switch ($this->signatureMethod) {
            case 'HMAC-SHA1':
            default:
                return base64_encode(hash_hmac('sha1', $sigBase, $sigKey, true));
        }
    }

    /**
     * encode
     *
     * Short-cut for utf8_encode / rawurlencode
     * @param mixed $data   Data to encode
     * @access protected
     * @return void         Encoded data
     */
    protected function encode($data) {
        return rawurlencode($data);
    }

    /**
     * createSignatureKey
     *
     * Creates a key that will be used to sign our signature.  Signatures
     * are signed with the consumerSecret for this consumer application and
     * the token secret of the user that the application is acting on behalf
     * of.
     * @access public
     * @return void
     */
    public function createSignatureKey() {
        return $this->consumerSecret.'&'.$this->user->tokenSecret;
    }

    /**
     * getOAuthRequestData
     *
     * Get all the pre-signature, OAuth specific parameters for a request.
     * @access public
     * @return void
     */
    public function getOAuthRequestData() {
        $token = $this->user->getHighestPriorityToken();
        $ts = $this->generateTimestamp();
        $nonce = $this->generateNonce($ts);
        return array(
            'oauth_token' => $token,
            'oauth_consumer_key' => $this->consumerKey,
            'oauth_version' => $this->version,
            'oauth_timestamp' => $ts,
            'oauth_signature_method' => $this->signatureMethod,
            'oauth_nonce' => $nonce);
    }


    /**
     * mergeOAuthData
     *
     * @param mixed $requestData
     * @access public
     * @return void
     */
    public function mergeOAuthData($requestData) {
        $oauthData = $this->getOAuthRequestData();
        return array_merge($requestData, $oauthData);
    }

    /**
     * createSignatureBase
     *
     * @param mixed $method     String name of HTTP method, such as "GET"
     * @param mixed $url        URL where this request will go
     * @param mixed $data       Array of params for this request. This should
     *      include ALL oauth properties except for the signature.
     * @access public
     * @return void
     */
    public function createSignatureBase($method, $url, $data) {
        $method = $this->encode(strtoupper($method));
        $query = parse_url($url, PHP_URL_QUERY);
        if ($query) {
            $parts = explode('?', $url, 2);
            $url = array_shift($parts);
            $items = explode('&', $query);
            foreach ($items as $item) {
                list($key, $value) = explode('=', $item);
                $data[rawurldecode($key)] = rawurldecode($value);
            }
        }
        $url = $this->encode($url);
        $data = $this->encode($this->collapseDataForSignature($data));

        return $method.'&'.$url.'&'.$data;
    }

    /**
     * collapseDataForSignature
     *
     * Turns an array of request data into a string, as used by the oauth
     * signature
     * @param mixed $data
     * @access public
     * @return void
     */
    public function collapseDataForSignature($data) {
        ksort($data);
        $collapse = '';
        foreach ($data as $key => $val) {
            if (!empty($collapse)) $collapse .= '&';
            $collapse .= $key.'='.$this->encode($val);
        }
        return $collapse;
    }

    /**
     * signRequest
     *
     * Signs the request.
     *
     * @param mixed $method     HTTP method
     * @param mixed $url        URL for the request
     * @param mixed $data       The data to be signed
     * @access public
     * @return array            The data, with the signature.
     */
    public function signRequest($method, $url, $data) {
        $base = $this->createSignatureBase($method, $url, $data);
        $key  = $this->createSignatureKey();
        $data['oauth_signature'] = $this->createSignature($base, $key);
        ksort($data);
        return $data;
    }


    /**
     * makeRequest
     *
     * Public facing function to make a request
     *
     * @param mixed $method
     * @param mixed $url  - Reserved characters in query params MUST be escaped
     * @param mixed $data - Reserved characters in values MUST NOT be escaped
     * @access public
     * @return void
     */
    public function makeRequest($method, $url, $data=array()) {

        if ($this->debug) echo "\n** {$method}: $url\n";

        switch (strtoupper($method)) {
            case 'POST':
                $oauth = $this->prepareRequest($method, $url, $data);
                $resp = $this->post($url, $oauth);
                break;

            case 'GET':
                $oauth = $this->prepareRequest($method, $url, $data);
                $resp = $this->get($url, $oauth, $data);
                break;

            case 'DELETE':
                $oauth = $this->prepareRequest($method, $url, $data);
                $resp = $this->delete($url, $oauth);
                break;

            case 'PATCH':
                $oauth = $this->prepareRequest($method, $url, array());
                $resp  = $this->patch($url, $oauth, $data);
                break;
        }

        // enable debug output
        if ($this->debug) {
            echo "<pre>";
            print_r($oauth);
            echo " --> Status: {$resp->headers['Status-Code']}\n";
            echo " --> Body: {$resp->body}";
            echo "</pre>";
        }

        if (!$resp) {
            $msg  = 'Unable to connect to the AWeber API.  (' . $this->error . ')';
            $error = array('message' => $msg, 'type' => 'APIUnreachableError',
                'documentation_url' => 'https://labs.aweber.com/docs/troubleshooting');
            throw new AWeberAPIException($error, $url);
        }

        if($resp->headers['Status-Code'] >= 400) {
            $data = json_decode($resp->body, true);
            throw new AWeberAPIException($data['error'], $url);
        }

        return $resp;
    }

    /**
     * put
     *
     * Prepare an OAuth put method.
     *
     * @param mixed $url    URL where we are making the request to
     * @param mixed $data   Data that is used to make the request
     * @access protected
     * @return void
     */
    protected function patch($url, $oauth, $data) {
        $url = $this->_addParametersToUrl($url, $oauth);
        $handle = $this->curl->init($url);
        $this->curl->setopt($handle, CURLOPT_CUSTOMREQUEST, 'PATCH');
        $this->curl->setopt($handle, CURLOPT_POSTFIELDS, json_encode($data));
        $resp = $this->_sendRequest($handle, array('Expect:', 'Content-Type: application/json'));
        return $resp;
    }

    /**
     * post
     *
     * Prepare an OAuth post method.
     *
     * @param mixed $url    URL where we are making the request to
     * @param mixed $data   Data that is used to make the request
     * @access protected
     * @return void
     */
    protected function post($url, $oauth) {
        $handle = $this->curl->init($url);
        $postData = $this->buildData($oauth);
        $this->curl->setopt($handle, CURLOPT_POST, true);
        $this->curl->setopt($handle, CURLOPT_POSTFIELDS, $postData);
        $resp = $this->_sendRequest($handle);
        return $resp;
    }

    /**
     * delete
     *
     * Makes a DELETE request
     * @param mixed $url        URL where we are making the request to
     * @param mixed $data       Data that is used in the request
     * @access protected
     * @return void
     */
    protected function delete($url, $data) {
        $url = $this->_addParametersToUrl($url, $data);
        $handle = $this->curl->init($url);
        $this->curl->setopt($handle, CURLOPT_CUSTOMREQUEST, 'DELETE');
        $resp = $this->_sendRequest($handle);
        return $resp;
    }

    /**
     * buildData
     *
     * Creates a string of data for either post or get requests.
     * @param mixed $data       Array of key value pairs
     * @access public
     * @return void
     */
    public function buildData($data) {
        ksort($data);
        $params = array();
        foreach ($data as $key => $value) {
            $params[] = $key.'='.$this->encode($value);
        }
        return implode('&', $params);
    }

    /**
     * _sendRequest
     *
     * Actually makes a request.
     * @param mixed $handle     Curl handle
     * @param array $headers    Additional headers needed for request
     * @access private
     * @return void
     */
    private function _sendRequest($handle, $headers = array('Expect:')) {
        $this->curl->setopt($handle, CURLOPT_RETURNTRANSFER, true);
        $this->curl->setopt($handle, CURLOPT_HEADER, true);
        $this->curl->setopt($handle, CURLOPT_HTTPHEADER, $headers);
        $this->curl->setopt($handle, CURLOPT_USERAGENT, $this->userAgent);
        $this->curl->setopt($handle, CURLOPT_SSL_VERIFYPEER, FALSE);
        $this->curl->setopt($handle, CURLOPT_VERBOSE, FALSE);
        $this->curl->setopt($handle, CURLOPT_CONNECTTIMEOUT, 10);
        $this->curl->setopt($handle, CURLOPT_TIMEOUT, 90);
        $resp = $this->curl->execute($handle);
        if ($resp) {
            return new CurlResponse($resp);
        }
        $this->error = $this->curl->errno($handle) . ' - ' .
            $this->curl->error($handle);
        return false;
    }

    /**
     * prepareRequest
     *
     * @param mixed $method     HTTP method
     * @param mixed $url        URL for the request
     * @param mixed $data       The data to generate oauth data and be signed
     * @access public
     * @return void             The data, with all its OAuth variables and signature
     */
    public function prepareRequest($method, $url, $data) {
        $data = $this->mergeOAuthData($data);
        $data = $this->signRequest($method, $url, $data);
        return $data;
    }

    /**
     * parseResponse
     *
     * Parses the body of the response into an array
     * @param mixed $string     The body of a response
     * @access public
     * @return void
     */
    public function parseResponse($resp) {
        $data = array();

        if (!$resp) {       return $data; }
        if (empty($resp)) { return $data; }
        if (empty($resp->body)) { return $data; }

        switch ($this->format) {
            case 'json':
                $data = json_decode($resp->body);
                break;
            default:
                parse_str($resp->body, $data);
        }
        $this->parseAsError($data);
        return $data;
    }

}

/**
 * OAuthUser
 *
 * Simple data class representing the user in an OAuth application.
 * @package
 * @version $id$
 */
class OAuthUser {

    public $authorizedToken = false;
    public $requestToken = false;
    public $verifier = false;
    public $tokenSecret = false;
    public $accessToken = false;

    /**
     * isAuthorized
     *
     * Checks if this user is authorized.
     * @access public
     * @return void
     */
    public function isAuthorized() {
        if (empty($this->authorizedToken) && empty($this->accessToken)) {
            return false;
        }
        return true;
    }


    /**
     * getHighestPriorityToken
     *
     * Returns highest priority token - used to define authorization
     * state for a given OAuthUser
     * @access public
     * @return void
     */
    public function getHighestPriorityToken() {
        if (!empty($this->accessToken)) return $this->accessToken;
        if (!empty($this->authorizedToken)) return $this->authorizedToken;
        if (!empty($this->requestToken)) return $this->requestToken;

        // Return no token, new user
        return '';
    }

}


/**
 * CurlInterface
 *
 * An object-oriented shim that wraps the standard PHP cURL library.
 *
 * This interface has been created so that cURL functionality can be stubbed
 * out for unit testing, or swapped for an alternative library.
 *
 * @see curl
 * @package
 * @version $id$
 */
interface CurlInterface {

    /**
     * errNo
     *
     * Encapsulates curl_errno - Returns the last error number
     * @param resource $ch - A cURL handle returned by init.
     * @access public
     * @return the error number or 0 if no error occured.
     */
    public function errno($ch);

    /**
     * error
     *
     * Encapsulates curl_error - Return last error string
     * @param resource $ch - A cURL handle returned by init.
     * @access public
     * @return the error messge or '' if no error occured.
     */
    public function error($ch);

    /**
     * execute
     *
     * Encapsulates curl_exec - Perform a cURL session.
     * @param resource $ch - A cURL handle returned by init.
     * @access public
     * @return TRUE on success, FALSE on failure.
     */
    public function execute($ch);

    /**
     * init
     *
     * Encapsulates curl_init - Initialize a cURL session.
     * @param string $url - url to use.
     * @access public
     * @return cURL handle on success, FALSE on failure.
     */
    public function init($url);

    /**
     * setopt
     *
     * Encapsulates curl_setopt - Set an option for cURL transfer.
     * @param resource $ch - A cURL handle returned by init.
     * @param int $opt - The CURLOPT to set.
     * @param mixed $value - The value to set.
     * @access public
     * @return True on success, FALSE on failure.
     */
    public function setopt ($ch , $option , $value);
}


/**
 * CurlObject
 *
 * A concrete implementation of CurlInterface using the PHP cURL library.
 *
 * @package
 * @version $id$
 */
class CurlObject implements CurlInterface {

    public function errno($ch) {
        return curl_errno($ch);
    }

    public function error($ch) {
        return curl_error($ch);
    }

    public function execute($ch) {
        return curl_exec($ch);
    }

    public function init($url) {
        return curl_init($url);
    }

    public function setopt ($ch , $option , $value) {
        return curl_setopt($ch, $option, $value);
    }

}


class AWeberCollection extends AWeberResponse implements \ArrayAccess, \Iterator, \Countable
{

    protected $pageSize = 100;
    protected $pageStart = 0;

    protected function _updatePageSize()
    {

        # grab the url, or prev and next url and pull ws.size from it
        $url = $this->url;
        if (array_key_exists('next_collection_link', $this->data)) {
            $url = $this->data['next_collection_link'];

        } elseif (array_key_exists('prev_collection_link', $this->data)) {
            $url = $this->data['prev_collection_link'];
        }

        # scan querystring for ws_size
        $url_parts = parse_url($url);

        # we have a query string
        if (array_key_exists('query', $url_parts)) {
            parse_str($url_parts['query'], $params);

            # we have a ws_size
            if (array_key_exists('ws_size', $params)) {

                # set pageSize
                $this->pageSize = $params['ws_size'];
                return;
            }
        }

        # we dont have one, just count the # of entries
        $this->pageSize = count($this->data['entries']);
    }

    public function __construct($response, $url, $adapter)
    {
        parent::__construct($response, $url, $adapter);
        $this->_updatePageSize();
    }

    /**
     * @var array Holds list of keys that are not publicly accessible
     */
    protected $_privateData = array(
        'entries',
        'start',
        'next_collection_link',
    );

    /**
     * getById
     *
     * Gets an entry object of this collection type with the given id
     * @param mixed $id ID of the entry you are requesting
     * @access public
     * @return AWeberEntry
     */
    public function getById($id)
    {
        $data = $this->adapter->request('GET', "{$this->url}/{$id}");
        $url = "{$this->url}/{$id}";
        return new AWeberEntry($data, $url, $this->adapter);
    }

    /** getParentEntry
     *
     * Gets an entry's parent entry
     * Returns NULL if no parent entry
     */
    public function getParentEntry()
    {
        $url_parts = explode('/', $this->url);
        $size = count($url_parts);

        # Remove collection id and slash from end of url
        $url = substr($this->url, 0, -strlen($url_parts[$size - 1]) - 1);

        try {
            $data = $this->adapter->request('GET', $url);
            return new AWeberEntry($data, $url, $this->adapter);
        } catch (Exception $e) {
            return NULL;
        }
    }

    /**
     * _type
     *
     * Interpret what type of resources are held in this collection by
     * analyzing the URL
     *
     * @access protected
     * @return void
     */
    protected function _type()
    {
        $urlParts = explode('/', $this->url);
        $type = array_pop($urlParts);
        return $type;
    }

    /**
     * create
     *
     * Invoke the API method to CREATE a new entry resource.
     *
     * Note: Not all entry resources are eligible to be created, please
     *       refer to the AWeber API Reference Documentation at
     *       https://labs.aweber.com/docs/reference/1.0 for more
     *       details on which entry resources may be created and what
     *       attributes are required for creating resources.
     *
     * @access public
     * @param params mixed  associtative array of key/value pairs.
     * @return AWeberEntry(Resource) The new resource created
     */
    public function create($kv_pairs)
    {
        # Create Resource
        $params = array_merge(array('ws.op' => 'create'), $kv_pairs);
        $data = $this->adapter->request('POST', $this->url, $params, array('return' => 'headers'));

        # Return new Resource
        $url = $data['Location'];
        $resource_data = $this->adapter->request('GET', $url);
        return new AWeberEntry($resource_data, $url, $this->adapter);
    }

    /**
     * find
     *
     * Invoke the API 'find' operation on a collection to return a subset
     * of that collection.  Not all collections support the 'find' operation.
     * refer to https://labs.aweber.com/docs/reference/1.0 for more information.
     *
     * @param mixed $search_data Associative array of key/value pairs used as search filters
     *                             * refer to https://labs.aweber.com/docs/reference/1.0 for a
     *                               complete list of valid search filters.
     *                             * filtering on attributes that require additional permissions to
     *                               display requires an app authorized with those additional permissions.
     * @access public
     * @return AWeberCollection
     */
    public function find($search_data)
    {
        # invoke find operation
        $params = array_merge($search_data, array('ws.op' => 'find'));
        $data = $this->adapter->request('GET', $this->url, $params);

        # get total size
        $ts_params = array_merge($params, array('ws.show' => 'total_size'));
        $total_size = $this->adapter->request('GET', $this->url, $ts_params, array('return' => 'integer'));
        $data['total_size'] = $total_size;

        # return collection
        return $this->readResponse($data, $this->url);
    }

    /*
     * ArrayAccess Functions
     *
     * Allows this object to be accessed via bracket notation (ie $obj[$x])
     * http://php.net/manual/en/class.arrayaccess.php
     */

    public function offsetSet($offset, $value)
    {
    }

    public function offsetUnset($offset)
    {
    }

    public function offsetExists($offset)
    {

        if ($offset >= 0 && $offset < $this->total_size) {
            return true;
        }
        return false;
    }

    protected function _fetchCollectionData($offset)
    {

        # we dont have a next page, we're done
        if (!array_key_exists('next_collection_link', $this->data)) {
            return null;
        }

        # snag query string args from collection
        $parsed = parse_url($this->data['next_collection_link']);

        # parse the query string to get params
        $pairs = explode('&', $parsed['query']);
        foreach ($pairs as $pair) {
            list($key, $val) = explode('=', $pair);
            $params[$key] = $val;
        }

        # calculate new args
        $limit = $params['ws.size'];
        $pagination_offset = intval($offset / $limit) * $limit;
        $params['ws.start'] = $pagination_offset;

        # fetch data, exclude query string
        $url_parts = explode('?', $this->url);
        $data = $this->adapter->request('GET', $url_parts[0], $params);
        $this->pageStart = $params['ws.start'];
        $this->pageSize = $params['ws.size'];

        $collection_data = array('entries', 'next_collection_link', 'prev_collection_link', 'ws.start');

        foreach ($collection_data as $item) {
            if (!array_key_exists($item, $this->data)) {
                continue;
            }
            if (!array_key_exists($item, $data)) {
                continue;
            }
            $this->data[$item] = $data[$item];
        }
    }

    public function offsetGet($offset)
    {

        if (!$this->offsetExists($offset)) {
            return null;
        }

        $limit = $this->pageSize;
        $pagination_offset = intval($offset / $limit) * $limit;

        # load collection page if needed
        if ($pagination_offset !== $this->pageStart) {
            $this->_fetchCollectionData($offset);
        }

        $entry = $this->data['entries'][$offset - $pagination_offset];

        # we have an entry, cast it to an AWeberEntry and return it
        $entry_url = $this->adapter->app->removeBaseUri($entry['self_link']);
        return new AWeberEntry($entry, $entry_url, $this->adapter);
    }

    /*
     * Iterator
     */
    protected $_iterationKey = 0;

    public function current()
    {
        return $this->offsetGet($this->_iterationKey);
    }

    public function key()
    {
        return $this->_iterationKey;
    }

    public function next()
    {
        $this->_iterationKey++;
    }

    public function rewind()
    {
        $this->_iterationKey = 0;
    }

    public function valid()
    {
        return $this->offsetExists($this->key());
    }

    /*
     * Countable interface methods
     * Allows PHP's count() and sizeOf() functions to act on this object
     * http://www.php.net/manual/en/class.countable.php
     */

    public function count()
    {
        return $this->total_size;
    }
}


class AWeberEntry extends AWeberResponse
{

    /**
     * @var array Holds list of data keys that are not publicly accessible
     */
    protected $_privateData = array(
        'resource_type_link',
        'http_etag',
    );

    /**
     * @var array   Stores local modifications that have not been saved
     */
    protected $_localDiff = array();

    /**
     * @var array Holds AWeberCollection objects already instantiated, keyed by
     *      their resource name (plural)
     */
    protected $_collections = array();

    /**
     * attrs
     *
     * Provides a simple array of all the available data (and collections) available
     * in this entry.
     *
     * @access public
     * @return array
     */
    public function attrs()
    {
        $attrs = array();
        foreach ($this->data as $key => $value) {
            if (!in_array($key, $this->_privateData) && !strpos($key, 'collection_link')) {
                $attrs[$key] = $value;
            }
        }
        if (!empty(AWeberAPI::$_collectionMap[$this->type])) {
            foreach (AWeberAPI::$_collectionMap[$this->type] as $child) {
                $attrs[$child] = 'collection';
            }
        }
        return $attrs;
    }

    /**
     * _type
     *
     * Used to pull the name of this resource from its resource_type_link
     * @access protected
     * @return String
     */
    protected function _type()
    {
        if (empty($this->type)) {
            $typeLink = $this->data['resource_type_link'];
            if (empty($typeLink)) return null;
            list($url, $type) = explode('#', $typeLink);
            $this->type = $type;
        }
        return $this->type;
    }

    /**
     * delete
     *
     * Delete this object from the AWeber system.  May not be supported
     * by all entry types.
     * @access public
     * @return boolean  Returns true if it is successfully deleted, false
     *      if the delete request failed.
     */
    public function delete()
    {
        $this->adapter->request('DELETE', $this->url, array(), array('return' => 'status'));
        return true;
    }

    /**
     * move
     *
     * Invoke the API method to MOVE an entry resource to a different List.
     *
     * Note: Not all entry resources are eligible to be moved, please
     *       refer to the AWeber API Reference Documentation at
     *       https://labs.aweber.com/docs/reference/1.0 for more
     *       details on which entry resources may be moved and if there
     *       are any requirements for moving that resource.
     *
     * @access public
     * @param AWeberEntry(List) List to move Resource (this) too.
     * @return mixed AWeberEntry(Resource) Resource created on List ($list)
     *                                     or False if resource was not created.
     */
    public function move($list, $last_followup_message_number_sent = NULL)
    {
        # Move Resource
        $params = array(
            'ws.op' => 'move',
            'list_link' => $list->self_link
        );
        if (isset($last_followup_message_number_sent)) {
            $params['last_followup_message_number_sent'] = $last_followup_message_number_sent;
        }

        $data = $this->adapter->request('POST', $this->url, $params, array('return' => 'headers'));

        # Return new Resource
        $url = $data['Location'];
        $resource_data = $this->adapter->request('GET', $url);
        return new AWeberEntry($resource_data, $url, $this->adapter);
    }

    /**
     * save
     *
     * Saves the current state of this object if it has been changed.
     * @access public
     * @return void
     */
    public function save()
    {
        if (!empty($this->_localDiff)) {
            $data = $this->adapter->request('PATCH', $this->url, $this->_localDiff, array('return' => 'status'));
        }
        $this->_localDiff = array();
        return true;

    }

    /**
     * __get
     *
     * Used to look up items in data, and special properties like type and
     * child collections dynamically.
     *
     * @param String $value Attribute being accessed
     * @access public
     * @throws AWeberResourceNotImplemented
     * @return mixed
     */
    public function __get($value)
    {
        if (in_array($value, $this->_privateData)) {
            return null;
        }
        if (!empty($this->data) && array_key_exists($value, $this->data)) {
            if (is_array($this->data[$value])) {
                $array = new AWeberEntryDataArray($this->data[$value], $value, $this);
                $this->data[$value] = $array;
            }
            return $this->data[$value];
        }
        if ($value == 'type') return $this->_type();
        if ($this->_isChildCollection($value)) {
            return $this->_getCollection($value);
        }
        throw new AWeberResourceNotImplemented($this, $value);
    }

    /**
     * __set
     *
     * If the key provided is part of the data array, then update it in the
     * data array.  Otherwise, use the default __set() behavior.
     *
     * @param mixed $key Key of the attr being set
     * @param mixed $value Value being set to the $key attr
     * @access public
     */
    public function __set($key, $value)
    {
        if (array_key_exists($key, $this->data)) {
            $this->_localDiff[$key] = $value;
            return $this->data[$key] = $value;
        } else {
            return parent::__set($key, $value);
        }
    }

    /**
     * findSubscribers
     *
     * Looks through all lists for subscribers
     * that match the given filter
     * @access public
     * @return AWeberCollection
     */
    public function findSubscribers($search_data)
    {
        $this->_methodFor(array('account'));
        $params = array_merge($search_data, array('ws.op' => 'findSubscribers'));
        $data = $this->adapter->request('GET', $this->url, $params);

        $ts_params = array_merge($params, array('ws.show' => 'total_size'));
        $total_size = $this->adapter->request('GET', $this->url, $ts_params, array('return' => 'integer'));

        # return collection
        $data['total_size'] = $total_size;
        $url = $this->url . '?' . http_build_query($params);
        return new AWeberCollection($data, $url, $this->adapter);
    }

    /**
     * getActivity
     *
     * Returns analytics activity for a given subscriber
     * @access public
     * @return AWeberCollection
     */
    public function getActivity()
    {
        $this->_methodFor(array('subscriber'));
        $params = array('ws.op' => 'getActivity');
        $data = $this->adapter->request('GET', $this->url, $params);

        $ts_params = array_merge($params, array('ws.show' => 'total_size'));
        $total_size = $this->adapter->request('GET', $this->url, $ts_params, array('return' => 'integer'));

        # return collection
        $data['total_size'] = $total_size;
        $url = $this->url . '?' . http_build_query($params);
        return new AWeberCollection($data, $url, $this->adapter);
    }

    /** getParentEntry
     *
     * Gets an entry's parent entry
     * Returns NULL if no parent entry
     */
    public function getParentEntry()
    {
        $url_parts = explode('/', $this->url);
        $size = count($url_parts);

        #Remove entry id and slash from end of url
        $url = substr($this->url, 0, -strlen($url_parts[$size - 1]) - 1);

        #Remove collection name and slash from end of url
        $url = substr($url, 0, -strlen($url_parts[$size - 2]) - 1);

        try {
            $data = $this->adapter->request('GET', $url);
            return new AWeberEntry($data, $url, $this->adapter);
        } catch (Exception $e) {
            return NULL;
        }
    }

    /**
     * getWebForms
     *
     * Gets all web_forms for this account
     * @access public
     * @return array
     */
    public function getWebForms()
    {
        $this->_methodFor(array('account'));
        $data = $this->adapter->request('GET', $this->url . '?ws.op=getWebForms', array(),
            array('allow_empty' => true));
        return $this->_parseNamedOperation($data);
    }


    /**
     * getWebFormSplitTests
     *
     * Gets all web_form split tests for this account
     * @access public
     * @return array
     */
    public function getWebFormSplitTests()
    {
        $this->_methodFor(array('account'));
        $data = $this->adapter->request('GET', $this->url . '?ws.op=getWebFormSplitTests', array(),
            array('allow_empty' => true));
        return $this->_parseNamedOperation($data);
    }

    /**
     * _parseNamedOperation
     *
     * Turns a dumb array of json into an array of Entries.  This is NOT
     * a collection, but simply an array of entries, as returned from a
     * named operation.
     *
     * @param array $data
     * @access protected
     * @return array
     */
    protected function _parseNamedOperation($data)
    {
        $results = array();
        foreach ($data as $entryData) {
            $results[] = new AWeberEntry($entryData, str_replace($this->adapter->app->getBaseUri(), '',
                $entryData['self_link']), $this->adapter);
        }
        return $results;
    }

    /**
     * _methodFor
     *
     * Raises exception if $this->type is not in array entryTypes.
     * Used to restrict methods to specific entry type(s).
     * @param mixed $entryTypes Array of entry types as strings, ie array('account')
     * @access protected
     * @return void
     */
    protected function _methodFor($entryTypes)
    {
        if (in_array($this->type, $entryTypes)) return true;
        throw new AWeberMethodNotImplemented($this);
    }

    /**
     * _getCollection
     *
     * Returns the AWeberCollection object representing the given
     * collection name, relative to this entry.
     *
     * @param String $value The name of the sub-collection
     * @access protected
     * @return AWeberCollection
     */
    protected function _getCollection($value)
    {
        if (empty($this->_collections[$value])) {
            $url = "{$this->url}/{$value}";
            $data = $this->adapter->request('GET', $url);
            $this->_collections[$value] = new AWeberCollection($data, $url, $this->adapter);
        }
        return $this->_collections[$value];
    }


    /**
     * _isChildCollection
     *
     * Is the given name of a collection a child collection of this entry?
     *
     * @param String $value The name of the collection we are looking for
     * @access protected
     * @return boolean
     * @throws AWeberResourceNotImplemented
     */
    protected function _isChildCollection($value)
    {
        $this->_type();
        if (!empty(AWeberAPI::$_collectionMap[$this->type]) &&
            in_array($value, AWeberAPI::$_collectionMap[$this->type])
        ) return true;
        return false;
    }
}



class AWeberEntryDataArray implements ArrayAccess, Countable, Iterator  {
    private $counter = 0;

    protected $data;
    protected $keys;
    protected $name;
    protected $parent;

    public function __construct($data, $name, $parent) {
        $this->data = $data;
        $this->keys = array_keys($data);
        $this->name = $name;
        $this->parent = $parent;
    }

    public function count() {
        return sizeOf($this->data);
    }

    public function offsetExists($offset) {
        return (isset($this->data[$offset]));
    }

    public function offsetGet($offset) {
        return $this->data[$offset];
    }

    public function offsetSet($offset, $value) {
        $this->data[$offset] = $value;
        $this->parent->{$this->name} = $this->data;
        return $value;
    }

    public function offsetUnset($offset) {
        unset($this->data[$offset]);
    }

    public function rewind() {
        $this->counter = 0;
    }

    public function current() {
        return $this->data[$this->key()];
    }

    public function key() {
        return $this->keys[$this->counter];
    }

    public function next() {
        $this->counter++;
    }

    public function valid() {
        if ($this->counter >= sizeOf($this->data)) {
            return false;
        }
        return true;
    }


}


/**
 * AWeberResponse
 *
 * Base class for objects that represent a response from the AWeberAPI.
 * Responses will exist as one of the two AWeberResponse subclasses:
 *  - AWeberEntry - a single instance of an AWeber resource
 *  - AWeberCollection - a collection of AWeber resources
 * @uses AWeberAPIBase
 * @package
 * @version $id$
 */
class AWeberResponse extends AWeberAPIBase {

    public $adapter = false;
    public $data = array();
    public $_dynamicData = array();

    /**
     * __construct
     *
     * Creates a new AWeberRespones
     *
     * @param mixed $response Data returned by the API servers
     * @param mixed $url URL we hit to get the data
     * @param mixed $adapter OAuth adapter used for future interactions
     * @access public
     */
    public function __construct($response, $url, $adapter) {
        $this->adapter = $adapter;
        $this->url     = $url;
        $this->data    = $response;
    }

    /**
     * __set
     *
     * Manual re-implementation of __set, allows sub classes to access
     * the default behavior by using the parent:: format.
     *
     * @param mixed $key        Key of the attr being set
     * @param mixed $value      Value being set to the attr
     * @access public
     */
    public function __set($key, $value) {
        $this->{$key} = $value;
    }

    /**
     * __get
     *
     * PHP "MagicMethod" to allow for dynamic objects.  Defers first to the
     * data in $this->data.
     *
     * @param String $value  Name of the attribute requested
     * @access public
     * @return mixed
     */
    public function __get($value) {
        if (in_array($value, $this->_privateData)) {
            return null;
        }
        if (array_key_exists($value, $this->data)) {
            return $this->data[$value];
        }
        if ($value == 'type') return $this->_type();
    }

}
