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

class Helpers{

  public static function get_error($error){
    if (is_wp_error($error)) {
      return $error->get_error_message();
    }
  }
}

?>
