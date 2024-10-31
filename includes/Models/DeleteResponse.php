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

class DeleteResponse
{
  public string $user_token;
  public string $date;
  public string $service;

  private function __construct()
  {
  }

  public static function fromValues(string $user_token, string $date, string $service): DeleteResponse
  {
    $response = new DeleteResponse();
    $response->user_token = $user_token;
    $response->date = $date;
    $response->service = $service;
    return $response;
  }

  public static function fromJson($json): DeleteResponse
  {
    $response = new DeleteResponse();
    $response->user_token = $json['user_token'];
    $response->date = $json['date'];
    $response->service = $json['service'];
    return $response;
  }

  public function toJson(): string
  {
    return json_encode($this);
  }
}
