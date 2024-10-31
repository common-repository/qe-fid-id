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

namespace QeFid\ID\Users;

/**
 * Class UserRoster
 * @package QeFid\ID\Users
 *
 * The class will be used to keep track of registered users, essentially when the plugin is activated
 * it will create a new instance of this class and then add the current user to the roster.
 * The roster will keep track of the original login values set by the user so they can be re instated
 * when the plugin is uninstalled/deactivated.
 *
 * This calss should only deal with collecting user data and storing it and reinstating the data
 */
class UserRoster
{
  public array $user_roster;
  public string $roster_status;
  public string $roster_nonce;

  public static string $ROSTER_STATUS_ACTIVE = 'active';
  public static string $ROSTER_STATUS_INACTIVE = 'inactive';

  public function __construct(array $roster, string $roster_nonce)
  {
    $this->user_roster = $roster;
    $this->roster_status = self::$ROSTER_STATUS_ACTIVE;
    $this->roster_nonce = $roster_nonce;
  }

  public function add_user($user_token, FidUser $user): array
  {
    $this->user_roster[$user_token] = $user;
    return $this->user_roster;
  }

  public function get_roster(): array
  {
    return $this->user_roster;
  }

  public function remove_user(string $user_token): array
  {
    if (isset($this->user_roster[$user_token])) unset($this->user_roster[$user_token]);
    return $this->user_roster;
  }

  public function update_user(string $user_token, FidUser $user): array
  {
    $this->user_roster[$user_token] = $user;
    return $this->user_roster;
  }

  public function get_user_by(string $key, string $value): FidUser
  {
    if ($key === "id" && isset($this->user_roster[$value])) return $this->user_roster[$value];
    if ($key === "id" && !isset($this->user_roster[$value])) return FidUser::fromValues("");
    $prop = $key === 'user_id' ? intval($value) : $value;
    foreach ($this->user_roster as $roster_user) {
      if ($roster_user->$key === $prop) return $roster_user;
    }

    return FidUser::fromValues("");
  }
}
