<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Home extends CI_Controller {

  function __construct() {
		parent::__construct();

		// Your app key from Klout.  Put this in a CI config file.
		$klout_key	= '';

		// Prep the config values to be passed to the Klout library
		$config = array(
					'key' => $klout_key,
					'format' => 'JSON',
					'version' => 2
					);

		// Load the Klout library and pass it the config values
		$this->load->library('Klout', $config);
	}


	/**
	 * 
	 * 
	 */
	public function index()
	{

		$twname = 'fpinzin';		// <-- Seed Twitter screen name
		$twid = '18012493';			// <-- Seed Twitter ID
		$ksid = '40532401411456547';	// <-- Seed Klout ID
	
		if(!empty($twname)) {
			$data['twname'] = $twname;
		}

//		if ($this->klout->setTWid($twid)){			// <-- seeding a Twitter ID
//		if ($this->klout->setKSID($ksid)){			// <-- seeding a Klout ID
		if ($this->klout->setTWName($twname)){		// <-- seeding a Twitter screen name
			$data['ksid'] = $this->klout->getksid();
			if($this->klout->fetch_score()){
				$resp = $this->klout->getscore();
				$data['score'] = $resp['score'];
				$data['delta'] = $resp['scoreDelta'];
			} 
			if($this->klout->fetch_topics()){
				$resp = $this->klout->gettopics();
				$data['topics'] = $resp;
			}
			if($this->klout->fetch_influence()){
				$resp = $this->klout->getinfluence();
				$data['inflr'] = $resp['myInfluencers'];
				$data['infle'] = $resp['myInfluencees'];
				$data['inflrc'] = $resp['myInfluencersCount'];
				$data['inflec'] = $resp['myInfluenceesCount'];
			}

		}
	/* 
	 * displayresponse() will return the response from the last library call.
	 * This should either be the formatted response object from Klout (i.e. JSON) or
	 * it may contain the Klout error response (e.g. indicating a problem with their 
	 * service or the function call).  Toss this in if you're trying to troubleshoot
	 * issues.
	 *
	 */
// echo 'Response: ' . $this->klout->displayresponse() . '<BR>';

		$this->load->view('home',$data);

	}
}
