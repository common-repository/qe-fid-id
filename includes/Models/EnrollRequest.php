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

class EnrollRequest{
  public string $domain;
  public array $labels;
  public string $user_pin;
  public int $count;

  private function __construct(){}

  public static function fromValues(string $domain, array $labels, string $user_pin, int $count): EnrollRequest
  {
    $request = new EnrollRequest();
    $request->domain = $domain;
    $request->labels = $labels;
    $request->user_pin = $user_pin;
    $request->count = $count;
    return $request;
  }

  public static function fromJson($json): EnrollRequest
  {
    $request = new EnrollRequest();
    $request->domain = $json['domain'];
    $request->labels = $json['labels'];
    $request->user_pin = $json['user_pin'];
    $request->count = $json['count'];
    return $request;
  }

  public function toJson(): string
  {
    return json_encode($this);
  }
}
