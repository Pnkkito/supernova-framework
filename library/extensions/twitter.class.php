<?php
/**
 * Supernova Framework
 */
/**
 * Twitter handler
 *
 * @package MVC_Controller_Twitter
 */

// Twitter just can do this 150 times per hour. API limitation
class Twitter{
	/**
	 * Get twitter timeline from username
	 *
	 * @param string $username Username in twitter
	 * @return mixed $rss Array with twitter data or false on error
	 */
	function raise($username){
		$url = 'http://twitter.com/statuses/user_timeline/'.$username.'.rss';
		$contents = @file_get_contents($url);
		if ($contents){
			$tweets = simplexml_load_string($contents);
			$rss = $tweets->channel;
			return $rss;
		}else{
			return false;
		}
	}
}