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

class RestConfig{
  public string $api_nonce;
  public string $api_url;
  public string $asset_url;
  public string $redirect;
  public $settings;

  public function __construct(
    $api_nonce,
    $api_url,
    $asset_url,
    $redirect="",
    $settings=null
  ){
    $this->api_nonce = $api_nonce;
    $this->api_url = $api_url;
    $this->asset_url = $asset_url;
    $this->redirect = $redirect;
    $this->settings = $settings;
  }

  public function toMap(){
    return (array)$this;
  }

}
?>
