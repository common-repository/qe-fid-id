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

class EnrollResponse
{
  public string $user_token;
  public string $bucket_id;
  public string $date;
  public string $service;
  public string $access_signature;

  private function __construct()
  {
  }

  public static function fromValues(string $user_token, string $bucket_id, string $date, string $service, string $access_signature): EnrollResponse
  {
    $response = new EnrollResponse();
    $response->user_token = $user_token;
    $response->bucket_id = $bucket_id;
    $response->date = $date;
    $response->service = $service;
    $response->access_signature = $access_signature;
    return $response;
  }

  public static function fromJson($json): EnrollResponse
  {
    $response = new EnrollResponse();
    $response->user_token = $json['user_token'];
    $response->bucket_id = $json['bucket_id'];
    $response->date = $json['date'];
    $response->service = $json['service'];
    $response->access_signature = $json['access_signature'];
    return $response;
  }

  public function toJson(): string
  {
    return json_encode($this);
  }
}
