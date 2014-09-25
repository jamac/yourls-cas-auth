<?php
/*
Plugin Name:    CAS Authentication
Plugin URI:     https://github.com/jamac/yourls-cas-auth
Description:    This plugin enables basic use of CAS for authentication
Version:        1.0
Author:         jamac
Author URI:     https://github.com/jamac/
*/

// No direct call
if(!defined('YOURLS_ABSPATH')) die();


/**
 * Check if user is validated via CAS
 * @return bool user is valid
 */
function casauth_is_valid_user() {

    // Check CAS for validation
    $auth = phpCAS::checkAuthentication();

    if($auth) {
        $username = phpCAS::getUser();
        if(casauth_is_user_allowed($username)) {
            return true;
        }
    }

    return false;

} // casauth_is_valid_user


/**
 * Send user to CAS for authentication
 */
function casauth_require_auth() {

    // Sent to CAS for authentication
    phpCAS::forceAuthentication();

} // function casauth_require_auth


/**
 * Authentication is successful
 * Convince YOURLS that everything is OK.
 */
function casauth_login() {

    global $yourls_user_passwords;

    $username = phpCAS::getUser();

    if($username) {
        $yourls_user_passwords[$username] = uniqid("",true);
        yourls_set_user($username);
    }

}


/**
 * Handle failed logins
 */
function casauth_login_failed() {

    casauth_void_session();
    yourls_die(yourls__("You are not permitted to be here."), yourls__("Unauthorized"), 403);

} // function casauth_login_failed


/**
 * Send user to CAS for logout
 */
function casauth_logout() {

    // CAS will halt page load and redirect. Destroy what we can here.
    casauth_void_session();
    phpCAS::logoutWithRedirectService(yourls_site_url());

}


/**
 * Check username against plugin whitelist
 * @param string $username name to check in whitelist
 * @return bool user is allowed
 */
function casauth_is_user_allowed($username) {

    global $casauth_user_whitelist;

    // Create a whitelist if none exists
    if(!isset($casauth_user_whitelist) || !is_array($casauth_user_whitelist)) {
        $casauth_user_whitelist = array();
    }

    // if $casauth_user_whitelist is empty assume all are allowed
    if(count((array) $casauth_user_whitelist)==0) {
        return true;
    }

    // Users in whitelist are allowed
    if($username && in_array($username, $casauth_user_whitelist, true)) {
        return true;
    }

    return false;

} // casauth_is_user_allowed


/**
 * Void any cookies that may be set
 */
function casauth_void_session() {
	setcookie('PHPSESSID', '', 0, '/');
	yourls_store_cookie(null);
}

/**
 * Ensure casauth plugin environment is prepared
 * Include phpCAS, add hooks and filters if everything is fine
 * @return bool preflight successful
 */
function casauth_preflight() {

    $ok = true;

    // Quick exit conditions
    if(yourls_is_API()) return $ok;
    if(!yourls_is_private()) return $ok;
    if(!yourls_is_admin()) return $ok;

    // Definitions that must be set
    $requiredDefinitions = array(
        'CASAUTH_CAS_PATH',
        'CASAUTH_CAS_HOST',
        'CASAUTH_CAS_URI',
    );

    foreach($requiredDefinitions as $definition) {

        if(!defined($definition) || !constant($definition)) {

            $ok = false;
            yourls_add_notice($definition . yourls__(' not defined for ') . yourls_plugin_basename(__FILE__), 'error');

        }

    }

    // Include phpCAS Now that it's path is verified
    if($ok) {

        // Prepend YOURLS_ABSPATH if CASAUTH_CAS_PATH is relative
        $cas_path = CASAUTH_CAS_PATH;
        if($cas_path{0} !== "/") $cas_path = YOURLS_ABSPATH.'/'.CASAUTH_CAS_PATH;

        if(!include_once($cas_path)) {

            $ok = false;
            yourls_add_notice(yourls__("Error including ") . CASAUTH_CAS_PATH, 'error');

        }

    }

    if($ok) {

        // Ensure everything else is defined and assign default values
        if(!defined('CASAUTH_CAS_VERSION'))     define('CASAUTH_CAS_VERSION', CAS_VERSION_2_0);
        if(!defined('CASAUTH_CAS_PORT'))        define('CASAUTH_CAS_PORT', 443);
        if(!defined('CASAUTH_CAS_CACERT'))      define('CASAUTH_CAS_CACERT', false);
        if(!defined('YOURLS_NO_HASH_PASSWORD')) define('YOURLS_NO_HASH_PASSWORD', true);

        // Connect to CAS server
        phpCAS::client(CASAUTH_CAS_VERSION, CASAUTH_CAS_HOST, CASAUTH_CAS_PORT, CASAUTH_CAS_URI);
        if(CASAUTH_CAS_CACERT) {
            phpCAS::setCasServerCACert(CASAUTH_CAS_CACERT);
        } else {
            phpCAS::setNoCasServerValidation();
        }

        // Start adding our hooks and filters
        yourls_add_filter('is_valid_user', 'casauth_is_valid_user');
        yourls_add_action('require_auth', 'casauth_require_auth');
        yourls_add_action('login', 'casauth_login');
        yourls_add_action('login_failed', 'casauth_login_failed');
        yourls_add_action('logout', 'casauth_logout');

    }

    return $ok;

} // casauth_preflight


// Let's get this ball rolling
yourls_add_action('plugins_loaded', 'casauth_preflight');
