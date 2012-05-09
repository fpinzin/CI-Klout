<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Written by Frank Pinzin (@fpinzin)
 * With inspiration from:
 * Brandon Beasley (http://brandonbeasley.com) for his Klout v1 work
 * Phil Sturgeon (http://philsturgeon.co.uk/code) for his CI cURL work
 */

class Klout {

    private $CI,                    // for using CodeIgniter instance within library
            $kloutURL,              // Klout base URL (incl version number)
            $key,                   // Klout api key
            $version,               // Klout api version
            $format,                // Klout response format (XML/JSON)
            $ksid,                  // local var for Klout ID
            $twid,                  // local var for Twitter ID (as opposed to screen name, this is always numeric)
            $twname,                // local var for Twitter screen name
            $user = array(),        // local var for the Klout user response
            $score = array(),       // local var for the Klout score response
            $topics = array(),      // local var for the Klout topics response
            $influence = array(),   // local var for the Klout influence response
            $response,              // stores the klout response or error message (always accessible using displayresponse()
            $options = array(),     // HTTP options for CURL
            $session;               // CURL session object


/**
 * 
 */
    function __construct($config = array()) {
        // Setup local instance of CI object
        $this->CI = & get_instance();

        // If a config array was passed on object creation, pass it to initialize
        if (!empty($config)) $this->initialize($config);
        
    }

/**
 * 
 */
    public function initialize($config) {
        $this->key = $this->_getConfig('key', $config);
        $this->format = $this->_getConfig('format', $config, 'json');
        $this->version = $this->_getConfig('version', $config, 2);

        switch ($this->version) {
            case 1:
                // Not supported in this library
                break;
            case 2:
                $this->kloutURL = 'http://api.klout.com/v2/';
                break;
            default;
                $this->kloutURL = 'http://api.klout.com/v2/';
                break;
        }

    }

/********************************************************************************************
 *
 *  Get and set some values
 *
 *********************************************************************************************/

/**
 * Set a KloutID and fetch the corresponding twitter ID
 */
    public function setKSID($var = NULL) {
        if (empty($var)) {
            return FALSE;
        } elseif ($this->ksid == $var) {       // If the lookup Klout ID = user data that already exists in object, don't refetch.
            return TRUE;
        } else {
            $this->ksid = $var;
            return $this->_fetch_wKSID($this->ksid);
        }
    }

/**
 * Set a Twitter username and fetch the corresponsing Klout ID
 *
 * The API allows to search for ksid by tw id or tw name. 
 * This function ALWAYS assumes by Twitter screen name.
 */
   public function setTWName($var = NULL) { 
        if (empty($var)) {
            return FALSE;
        } elseif ($this->twname == $var) {       // If the lookup twitter username = user data that already exists in object, don't refetch.
            return TRUE;
        } else {
            $this->twname = $var;
            return $this->_fetch_wTWName($this->twname);
        }
    }

/**
 * Set a Twitter ID and fetch the corresponsing Klout ID
 *
 * The API allows to search for ksid by tw id or tw name. This function ALWAYS assumes by Twitter ID.
 */
    public function setTWID($var = NULL) {
        if (empty($var)) {
            return FALSE;
        } elseif ($this->twid == $var) {       // If the lookup twitter ID = user data that already exists in object, don't refetch.
            return TRUE;
        } else {
            $this->twid = $var;
            return $this->_fetch_wTWID($this->twid);
        }
    }

    public function getKSID() {
        return $this->ksid;
    }

    public function getTWID() {
        return $this->twid;
    }

    public function getTWName() {
        return $this->twname;
    }

    public function getScore() {
        return $this->score;
    }

    public function getTopics() {
        return $this->topics;
    }

    public function getInfluence() {
        return $this->influence;
    }


/**
 * Fetch the USER response from Klout.  Requires KloutID
 *
 * $lookup - numeric.  assumed to be the Klout ID
 * 
 */
    public function fetch_user() {
        $uri = 'user.json/' . $lookup . '?key=' . $this->api_key;
        return $this->_execute($uri, $this->user);
    }

/**
 * Fetch the SCORE response from Klout.  Requires KloutID
 *
 * $lookup - numeric.  assumed to be the Klout ID
 * 
 */
    public function fetch_score() {
        $uri = 'user.json/' . $this->ksid . '/score?key=' . $this->key;
        return $this->_execute($uri, $this->score);
    }

 /**
  * Fetch the TOPICS response from Klout.  Requires KloutID
  *
  * $lookup - numeric.  assumed to be the Klout ID
  * 
  */
    public function fetch_topics($lookup = NULL) {
        $uri = 'user.json/'.$this->ksid.'/topics?key='.$this->key;
        return $this->_execute($uri, $this->topics);
    }

 /**
  * Fetch the INFLUENCE response from Klout.  Requires KloutID
  *
  * $lookup - numeric.  assumed to be the Klout ID
  * 
  */
    public function fetch_influence($lookup = NULL) {
        $uri = 'user.json/'.$this->ksid.'/influence?key='.$this->key;
        return $this->_execute($uri, $this->influence);
    }

 /**
  * Returns the $response value which stores the last Klout response OR the error message if appropriate
  *
  */
    public function DisplayResponse() {
        return $this->response;
    }

/********************************************************************************************
 *
 *  Private Functions
 *
 *********************************************************************************************/

/**
 * With a Twitter username, lookup the associated Klout ID
 * $lookup - alpha/numeric.  always assumed to be the twitter username and NOT the twitter user id
 * 
 */
    private function _fetch_wTWName($lookup = NULL) {
        $lookup = (is_null($lookup) ? $this->twname : $lookup);
        $uri = 'identity.json/twitter?screenName=' . $lookup . '&key=' . $this->key;
        if ($this->_execute($uri, $tmp)){
            $this->ksid = $tmp['id'];
            return TRUE;
        } else {
            return FALSE;
        }
    }

/**
 * With a KloutID, lookup the associated Twitter ID
 * $lookup - numeric.  assumed to be the Klout ID
 * 
 */
    private function _fetch_wKSID($lookup = NULL) {
        $lookup = (is_null($lookup) ? $this->ksid : $lookup);
        $uri = 'identity.json/klout/' . $lookup . '/tw?key=' . $this->key;
        if ($this->_execute($uri, $tmp)){
            $this->twid = $tmp['id'];
            return TRUE;
        } else {
            return FALSE;
        }
    }

/**
 * With a Twitter ID, lookup the associated Klout ID
 * $lookup - numeric.  assumed to be the Twitter ID
 * 
 */
    private function _fetch_wTWID($lookup = NULL) {
        $lookup = (is_null($lookup) ? $this->ksid : $lookup);
        $uri = 'identity.json/tw/' . $lookup . '?key=' . $this->key;
        if ($this->_execute($uri, $tmp)){
            $this->ksid = $tmp['id'];
            return TRUE;
        } else {
            return FALSE;
        }
    }

/**
 * CURL method
 *
 */
    private function _execute ($uri = NULL, &$var) {

        // Fail if the URI is blank
        if (empty($uri)) {
            return FALSE;
        } else {
            $this->session = curl_init($this->kloutURL . $uri);

            // Set default options
            $this->options[CURLOPT_TIMEOUT] = 30;
            $this->options[CURLOPT_RETURNTRANSFER] = TRUE;
            $this->options[CURLOPT_FOLLOWLOCATION] = TRUE;
            $this->options[CURLOPT_FAILONERROR] = TRUE;

            // Not doing anything with headers at this point
            // $option(CURLOPT_HTTPHEADER, '');

            curl_setopt_array($this->session, $this->options);

            // Execute the request and capture response
            $this->response = curl_exec($this->session);
            $this->info = curl_getinfo($this->session);

            // Response is legitimate
            if (($this->info['http_code'] == 200) || ($this->info['http_code'] == 202)) {
                curl_close($this->session);
                $this->session = NULL;
                $var = json_decode($this->response,TRUE);
                return TRUE;
            // Response is an error from Klout
            } else {
                curl_close($this->session);
                $this->session = NULL;

                $this->response = $this->info['http_code'];
                return FALSE;
            }
        }
    }

/**
 * Extracting config values from array
 *
 */
    // Sorry for this insane statement.  I thought that it would be fun.
    private function _getConfig($needle = null, $haystack = null, $default = null) {
        return ((!empty($haystack) || !empty($needle)) ? (is_array($haystack) ? (array_key_exists($needle,$haystack) ? $haystack[$needle] : (empty($default) ? FALSE : $default)) : $haystack) : $default);
    }
}
