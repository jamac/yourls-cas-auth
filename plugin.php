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
 * Login & Logout via CAS, bypassing YOURLS login entirely
 * @return bool user is valid
 */
function casauth_is_user_valid() {

    // Connect to CAS server
    phpCAS::client(CASAUTH_CAS_VERSION, CASAUTH_CAS_HOST, CASAUTH_CAS_PORT, CASAUTH_CAS_CONTEXT);
    if(CASAUTH_CAS_CACERT) {
        phpCAS::setCasServerCACert(CASAUTH_CAS_CACERT);
    } else {
        phpCAS::setNoCasServerValidation();
    }

    // Check for authentication
    $auth = phpCAS::checkAuthentication();

    // Not authorized
    if(!$auth) {

        // Send user to CAS login
        phpCAS::forceAuthentication();

    // Authorized
    } else {

        // Send user to CAS logout, page flow dies here
        if(isset($_GET['action']) && $_GET['action'] == 'logout') {
            phpCAS::logoutWithRedirectService(yourls_site_url());
        }

        $cas_user = phpCAS::getUser();
        if(casauth_is_user_allowed($cas_user)) {
            yourls_set_user($cas_user);
            return true;
        } else {
            yourls_die(yourls__("You have not been permitted to be here"), yourls__("Unauthorized"), 403);
        }

    }

    return false;

} // casauth_is_user_valid


/**
 * Check username against whitelist
 * @param string $username name to check in whitelist
 * @return bool user is allowed
 */
function casauth_is_user_allowed($username) {

    global $casauth_user_whitelist;

    // Create a whitelist if none exist
    if(!isset($casauth_user_whitelist) || !is_array($casauth_user_whitelist)) {
        $casauth_user_whitelist = array();
    }

    // $casauth_user_whitelist is empty assume all are allowed
    if(count((array) $casauth_user_whitelist)==0) {
        return true;
    }

    // Users in list are allowed
    if(in_array($username, $casauth_user_whitelist, true)) {
        return true;
    }

    return false;

} // casauth_is_user_allowed


/**
 * Ensure Plugin Environment is prepared
 * Include phpCAS, add hooks and filters if everything is fine
 * @return bool preflight successful
 */
function casauth_preflight() {

    $ok = true;

    // Quick exit conditions
    if(yourls_is_API()) return $ok;

    // Definitions that must be set
    $requiredDefinitions = array(
        'CASAUTH_CAS_PATH',
        'CASAUTH_CAS_HOST',
        'CASAUTH_CAS_CONTEXT',
    );

    foreach($requiredDefinitions as $definition) {

        if(!defined($definition) || !constant($definition)) {

            $ok = false;
            // TODO: Find a way to only show to longed in users.
            // May not be trivial. I fear this happens before auth.
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

        // Ensure everything else if defined and assign default values
        if(!defined('CASAUTH_CAS_VERSION'))     define('CASAUTH_CAS_VERSION', CAS_VERSION_2_0);
        if(!defined('CASAUTH_CAS_PORT'))        define('CASAUTH_CAS_PORT', 443);
        if(!defined('CASAUTH_CAS_CACERT'))      define('CASAUTH_CAS_CACERT', false);

        // Start adding our hooks and filters
        yourls_add_filter('shunt_is_valid_user', 'casauth_is_user_valid');

    }

    return $ok;

} // casauth_preflight


// Let's get this ball rolling
yourls_add_action('plugins_loaded', 'casauth_preflight');
