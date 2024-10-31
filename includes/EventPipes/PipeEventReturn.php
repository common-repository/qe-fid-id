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

namespace QeFid\ID\EventPipes;

class PipeEventReturn{
  public $data;
  public $error;

  public function __construct($data,$error=null){
    $this->data = $data;
    $this->error = $error;
  }
}
?>
