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


namespace QeFid\ID;

/**
 * Security exit if normal directory traversal is bypassed.
 */
if (!defined('ABSPATH')) {
  //@inject:   exit;
} // Exit if accessed directly

// // If this file is called directly, abort.
if (!defined('WPINC')) {
  //@inject:     die;
}
/**
 * The API integration class for the FiD, handle the api calls and composing and decomposing the api responses
 */
class FiDAuthApi
{

  public static function postToApi(array $requestBody, string $url, string $token = "", bool $isToken = false): string
  {
    if ($isToken) {
      if ($token == null || $token == '') {
        return '{"error":"No Active License Found"}';
      }
    }

    $result = wp_remote_post($url, array(
      'method' => 'POST',
      'body'        => json_encode($requestBody),
      'headers'     => array(
        'content-type' => 'application/json',
        'x-auth-api-key' => $token,
      ),
      'blocking'    => true,
    ));
    if (is_wp_error($result)) {
      return '{"error":"' . $result->get_error_message() . '"}';
    }

    return $result['body'];
  }
}
