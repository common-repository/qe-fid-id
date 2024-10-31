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

namespace QeFid\ID\Models;

class EligibleUser 
{
  public string $id;
  public string $display_name;
  public string $email;
  public bool $is_enrolled;
  public string $last_login;
  public string $last_login_ip;
  public string $last_login_status;
  public string $last_login_user_agent;

  private function __construct() {}

  public static function fromValues(
    string $id,
    string $display_name,
    string $email,
    bool $is_enrolled=false,
    string $last_login="",
    string $last_login_ip="",
    string $last_login_status="",
    string $last_login_user_agent=""
  ): EligibleUser
  {
    $eligibleUser = new EligibleUser();
    $eligibleUser->id = $id;
    $eligibleUser->display_name = $display_name;
    $eligibleUser->email = $email;
    $eligibleUser->is_enrolled = $is_enrolled;
    $eligibleUser->last_login = $last_login;
    $eligibleUser->last_login_ip = $last_login_ip;
    $eligibleUser->last_login_status = $last_login_status;
    $eligibleUser->last_login_user_agent = $last_login_user_agent;

    return $eligibleUser;
  }

  public static function fromJson(array $data): EligibleUser
  {
    $eligibleUser = new EligibleUser();
    $eligibleUser->id = $data['id'];
    $eligibleUser->display_name = $data['display_name'];
    $eligibleUser->email = $data['email'];
    $eligibleUser->is_enrolled = $data['is_enrolled'];
    $eligibleUser->last_login = $data['last_login'];
    $eligibleUser->last_login_ip = $data['last_login_ip'];
    $eligibleUser->last_login_status = $data['last_login_status'];
    $eligibleUser->last_login_user_agent = $data['last_login_user_agent'];

    return $eligibleUser;
  }

  public function toJson():string
  {
    return json_encode($this);
  }
}
?>
