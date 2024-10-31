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

namespace QeFid\ID\Utils;

use QeFid\ID\Constants\Options;

class Networking{
  public static function post_to_url_with_image($url, $file_path, $form_data){
    $api_access_key = get_option(Options::$API_KEY_ACCESS);
    $boundary = wp_generate_password( 24, false );
    $headers  = array(
      'X-Api-Key' => $api_access_key,
      'content-type' => 'multipart/form-data; boundary=' . $boundary,
    );

    $payload = '';
    foreach ( $form_data as $name => $value ) {
      $payload .= '--' . $boundary;
      $payload .= "\r\n";
      $payload .= 'Content-Disposition: form-data; name="' . $name .
        '"' . "\r\n\r\n";
      $payload .= $value;
      $payload .= "\r\n";
    }

    // Upload the file
    if ( $file_path ) {
      $payload .= '--' . $boundary;
      $payload .= "\r\n";
      $payload .= 'Content-Disposition: form-data; name="' . 'file' .
        '"; filename="' . basename( $file_path ) . '"' . "\r\n";
      //        $payload .= 'Content-Type: image/jpeg' . "\r\n";
      $payload .= "\r\n";
      $payload .= file_get_contents( $file_path );
      $payload .= "\r\n";
    }

    $payload .= '--' . $boundary . '--';
    $response = wp_remote_post( $url,
      array(
        'headers'    => $headers,
        'body'       => $payload,
        'timeout'    => 20,
      )
    );
    return $response;
  }

  public static function post_to_url($url, $body){
    $response = self::post_to_url_with_image($url,null,$body);
    return $response;
  }

}
?>
