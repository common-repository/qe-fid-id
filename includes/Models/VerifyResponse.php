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

class VerifyResponse
{
  public string $service;
  public string $access_token;
  public string $label;
  public string $date;

  private function __construct()
  {
  }

  public static function fromValues(string $service, string $access_token, string $label, string $date): VerifyResponse
  {
    $response = new VerifyResponse();
    $response->service = $service;
    $response->access_token = $access_token;
    $response->label = $label;
    $response->date = $date;
    return $response;
  }

  public static function fromJson($json): VerifyResponse
  {
    $response = new VerifyResponse();
    $response->service = $json['service'];
    $response->access_token = $json['access_token'];
    $response->label = $json['label'];
    $response->date = $json['date'];
    return $response;
  }

  public  function toJson(): string
  {
    return json_encode($this);
  }
}
