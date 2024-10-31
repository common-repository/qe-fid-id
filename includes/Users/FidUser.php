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

class FidUser{
  public string $user_access;
  public string $user_token;
  public string $bucket_id;
  public string $nonce;
  public int $user_id;
  public string $old_user_access;

  private function __construct(){}

  public static function fromValues(string $user_token, int $user_id=-1, string $user_access="", string $nonce="",string $bucket_id="", string $old_user_access=""): FidUser{
    $fid_user = new FidUser();
    $fid_user->user_access = $user_access;
    $fid_user->user_token = $user_token;
    $fid_user->bucket_id = $bucket_id;
    $fid_user->nonce = $nonce;
    $fid_user->user_id = $user_id;
    $fid_user->old_user_access = $old_user_access;
    return $fid_user;
  }

  public static function fromJson(array $json): FidUser{
    $fid_user = new FidUser();
    $fid_user->user_id = $json['user_id'];
    $fid_user->user_token = $json['user_token'];
    $fid_user->user_access = $json['user_access'];
    $fid_user->bucket_id = $json['bucket_id'];
    $fid_user->nonce = $json['nonce'];
    $fid_user->old_user_access = $json['old_user_access'] ?? "";
    return $fid_user;
  }

  public function toJson():string{
    return json_encode($this);
  }

  public function get_user_access():string{
    return $this->user_access;
  }

  public function get_user_token():string{
    return $this->user_token;
  }

  public function get_old_user_access():string{
    return $this->old_user_access;
  }

  public function update_old_user_access(string $access):void{
    $this->old_user_access = $access;
  }
}

?>
