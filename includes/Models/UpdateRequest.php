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

class UpdateRequest
{
  public string $bucket_id;
  public string $domain;
  public string $old_pin;
  public string $user_token;
  public string $new_pin;

  private function __construct()
  {
  }

  public static function fromValues(string $bucket_id, string $domain, string $old_pin, string $user_token, string $new_pin): UpdateRequest
  {
    $request = new UpdateRequest();
    $request->bucket_id = $bucket_id;
    $request->domain = $domain;
    $request->old_pin = $old_pin;
    $request->user_token = $user_token;
    $request->new_pin = $new_pin;
    return $request;
  }

  public static function fromJson($json): UpdateRequest
  {
    $request = new UpdateRequest();
    $request->bucket_id = $json['bucket_id'];
    $request->domain = $json['domain'];
    $request->old_pin = $json['old_pin'];
    $request->user_token = $json['user_token'];
    $request->new_pin = $json['new_pin'];
    return $request;
  }

  public function toJson(): string
  {
    return json_encode($this);
  }
}
