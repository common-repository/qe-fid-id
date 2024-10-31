<?php

/**
 * FiD Facial Recognition Admin Login
 *
 * @package   FiD Access Plugin
 * @author    FiD Access
 * @license   GPL-3.0
 * @copyright FiD Access
 * @link      https://www.fidaccess.com
 *
 */

namespace QeFid\ID\Endpoint;

use QeFid\ID;
use QeFid\ID\Constants\Options;

/**
 * Plugin admin and licensing controls
 * For Renewing, activating and deactivating licences
 */
class PluginAdmin
{
  protected static function get_owner_site_url(): string
  {
    return get_site_url();
  }

  /**
   * Returns the licensing endpoint base url
   */
  public static function get_licensing_url()
  {
    $endpoint_option = get_option(Options::$LICENSING_API_ENDPOINT);
    return $endpoint_option;
  }

  /**
   * Retrieve the url endpoint to affect the license
   */
  static function get_activate_url(bool $isActivate = true)
  {
    return PluginAdmin::get_licensing_url() . "/" . (($isActivate) ? "activate" : "deactivate");
  }

  /**
   * Retrieve the url endpoint to renew the license
   */
  static function get_renew_url()
  {
    return PluginAdmin::get_licensing_url() . "/" . "renew-license";
  }


  /**
   * Activate the license from the user activation key supplied
   */
  static function activate_license($activationToken)
  {
    // return object to be passed to plugin pages
    $return = array(
      'error' => '',
      'success' => true
    );

    $plugin = ID\Plugin::get_instance();
    $plugin_slug = $plugin->get_plugin_slug();
    // Encode the request Body
    $request_body = [
      'domain' => PluginAdmin::get_owner_site_url(),
      'activation_key' => $activationToken,
      'rest_route' => '/' . $plugin_slug . '/v1/' . Options::$FID_PLUGIN_LICENSE_WEBHOOK . '/',
    ];


    // Make the API call
    $response = ID\FiDAuthApi::postToApi($request_body, PluginAdmin::get_activate_url());

    // Check response includes errors or not
    if (strpos($response, '{"error":') !== false) {
      $resp = json_decode($response, true);
      $return['error'] = $resp;
      $return['success'] = false;

      return $return;
    }

    $return['success'] = PluginAdmin::save_auth_tokens($response);
    $plugin->activate();
    return $return;
  }


  /**
   * Renew the access Tokens from webhook
   */
  static function renew_license()
  {
    // return object to be passed to plugin pages
    $return = array(
      'error' => '',
      'success' => true
    );

    $token = get_option(Options::$API_KEY_REFRESH);
    if ($token == false) {
      $return['error'] = "No Active License";
      $return['success'] = false;
      return $return;
    }


    // Make the API call
    $response = ID\FiDAuthApi::postToApi(null, PluginAdmin::get_renew_url(), $token, true);

    // Check response includes errors or not
    if (strpos($response, '{"error":') !== false) {
      $resp = json_decode($response, true);
      $return['error'] = $resp;
      $return['success'] = false;

      return $return;
    }

    $return['success'] = PluginAdmin::save_auth_tokens($response, true);
    return $return;
  }

  /**
   * Deactivate the license by using the authenticator_token
   */
  static function deactivate_license($activationToken)
  {
    // return object to be passed to plugin pages
    $return = array(
      'error' => '',
      'success' => true
    );

    // Encode the request Body
    $request_body = array(
      'domain' => PluginAdmin::get_owner_site_url(),
    );

    // Get the JSON encoded API request body

    // Make the API call
    $response = ID\FiDAuthApi::postToApi($request_body, PluginAdmin::get_activate_url(false), $activationToken, true);

    $plugin = ID\Plugin::get_instance();
    $plugin->deactivate();

    // Check response includes errors or not
    if (strpos($response, '{"error":') !== false) {
      $resp = json_decode($response, true);
      $return['error'] = $resp;
      $return['success'] = false;
      return $return;
    }



    return $return;
  }

  /**
   * Save the received token pair to the databse for later use
   * Takes the response from the api and save the tokens under
   * two records.
   */
  public static function save_auth_tokens($tokens, $is_renew = false)
  {
    $tknArray = json_decode($tokens, true);

    if ($tknArray == null) {
      return false;
    }

    if (
      !isset($tknArray["tokens"]) ||
      $tknArray["tokens"] == null
    ) {
      return false;
    }

    if ($is_renew == false) {
      if (
        !isset($tknArray["verify_endpoint"]) ||
        $tknArray["verify_endpoint"] == null
      ) {
        return false;
      }
    }

    if (
      !isset($tknArray["tokens"]) ||
      $tknArray["tokens"]["access_token"] == "" ||
      $tknArray["tokens"]["refresh_token"] == ""
    ) {
      return false;
    }
    update_option(Options::$API_KEY_ACCESS, $tknArray["tokens"]["access_token"]);
    update_option(Options::$API_KEY_REFRESH, $tknArray["tokens"]["refresh_token"]);

    //extract base 64 encoded data from the token
    $token = explode(".", $tknArray["tokens"]["access_token"]);
    $token = json_decode(base64_decode($token[1]), true);
    update_option(Options::$FID_LICENSE_USER_LIMIT, $token["license_user_limit"]);
    if ($is_renew == false) {
      update_option(Options::$FID_API_ENDPOINT, $tknArray["verify_endpoint"]);
    }
    return true;
  }
}
