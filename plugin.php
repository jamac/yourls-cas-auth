<?php
/*
Plugin Name:	CAS Authentication
Plugin URI:		https://github.com/jamac/yourls-cas-auth
Description:	This plugin enables use of CAS for authentication
Version:			0.1
Author:				jamac
Author URI:		https://github.com/jamac/
*/

// No direct call
if(!defined('YOURLS_ABSPATH')) die();


/**
	*	Ensure Plugin Environment is prepared
	*	Include phpCAS, add hooks and filters if everything is fine
	*/
function casauth_preflight() {

	$ok = true;

	// Definitions that must be set
	$requiredDefinitions = array(
		'CASAUTH_CAS_PATH',
		'CASAUTH_CAS_HOST',
		'CASAUTH_CAS_CONTEXT',
	);

	foreach($requiredDefinitions as $definition) {

		if(!defined($definition) || !constant($definition)) {

			$ok = false;
			$message = $definition . ' not defined for ' . yourls_plugin_basename(__FILE__);
			error_log($message);
			// TODO: Find a way to only show to longed in users.
			// May not be trivial. I fear this happens before auth.
			yourls_add_notice($message, 'error');

		}

	}

	// Include phpCAS Now that it's path is verified
	if($ok && !include_once(CASAUTH_CAS_PATH)) {

		$ok = false;
		$message = "Error including ".CASAUTH_CAS_PATH;
		error_log($message);
		yourls_add_notice($message, 'error');

	}

	if($ok) {

		// If no user list specified allow everyone to log in
		global $casauth_users;
		if(!isset($casauth_users)) $casauth_users = array();

		// Ensure everything else if defined and assign default values
		if(!defined('CASAUTH_CAS_VERSION'))		define('CASAUTH_CAS_VERSION', CAS_VERSION_2_0);
		if(!defined('CASAUTH_CAS_PORT')) 			define('CASAUTH_CAS_PORT', 443);
		if(!defined('CASAUTH_CAS_CACERT'))		define('CASAUTH_CAS_CACERT', false);

	}

	// If everything is OK up to this point start adding our hooks and filters
	if($ok) {

	}

	return $ok;

}

yourls_add_action('plugins_loaded', 'casauth_preflight');
