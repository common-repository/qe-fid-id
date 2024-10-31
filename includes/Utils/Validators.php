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
// namespaces to be injected in the build file, excluded for testing
// requires Makefile to strip the tag
use WP_User;
use WP_Error;

class Validators{

  public static function validate_user_id(string $user_id): bool{
    return preg_match("/^[0-9]{1,9}$/si", $user_id);
  }

  public static function validate_user_pin(string $user_pin): bool{
    return preg_match("/^[A-Z0-9]{4}$/si", $user_pin);
  }

  public static function validate_user_id_pin(string $user_id, string $user_pin): bool{
    return Validators::validate_user_id($user_id) && Validators::validate_user_pin($user_pin);
  }

  public static function validate_plugin_nonce(string $nonce): bool{
    return preg_match("/^#[A-Fa-f0-9]{8}$/", $nonce);
  }

  public static function is_wp_user($thing): bool{
    return $thing instanceof WP_User;
  }

  public static function is_wp_error($thing): bool{
    return $thing instanceof WP_Error;
  }
}
?>
