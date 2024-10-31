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

class GenericResponse
{
  public string $error;
  public string $message;
  public $payload;
  public int $code;

  private function __construct()
  {
  }

  public static function fromValues(string $error, string $message, $payload, int $code): GenericResponse
  {
    $response = new GenericResponse();
    $response->error = $error;
    $response->message = $message;
    $response->payload = $payload;
    $response->code = $code;
    return $response;
  }

  public static function fromJson($json): GenericResponse
  {
    $response = new GenericResponse();
    $response->error = $json['error'];
    $response->message = $json['message'];
    $response->payload = $json['payload'];
    $response->code = $json['code'];
    return $response;
  }

  public function toJson(): string
  {
    return json_encode($this);
  }
}
