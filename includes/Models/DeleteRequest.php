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

class DeleteRequest
{
  public string $bucket_id;
  public string $domain;
  public string $user_token;

  private function __construct()
  {
  }

  public static function fromValues(string $bucket_id, string $domain,  string $user_token): DeleteRequest
  {
    $request = new DeleteRequest();
    $request->bucket_id = $bucket_id;
    $request->domain = $domain;
    $request->user_token = $user_token;
    return $request;
  }

  public static function fromJson($json): DeleteRequest
  {
    $request = new DeleteRequest();
    $request->bucket_id = $json['bucket_id'];
    $request->domain = $json['domain'];
    $request->user_token = $json['user_token'];
    return $request;
  }

  public function toJson(): string
  {
    return json_encode($this);
  }
}
