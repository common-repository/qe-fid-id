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

class VerifyRequest
{
  public string $domain;
  public string $user_pin;

  private function __construct()
  {
  }

  public static function fromValues(string $domain, string $user_pin): VerifyRequest
  {
    $request = new VerifyRequest();
    $request->domain = $domain;
    $request->user_pin = $user_pin;
    return $request;
  }

  public static function fromJson($json): VerifyRequest
  {
    $request = new VerifyRequest();
    $request->domain = $json['domain'];
    $request->user_pin = $json['user_pin'];
    return $request;
  }

  public function toJson(): string
  {
    return json_encode($this);
  }
}
