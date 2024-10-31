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

/**
 * This class is used to register events to the event pipe
 * 
 * @author  FiD Access
 * @version 1.1.0
 * @since   1.1.0
 *
 * "get_user_id",
 * "generate_plugin_user_id",
 * "register_to_api",
 * "compile_required_data",
 * "update_user_roster",
 * 
 */
class RegisterPipe extends Pipe {
  public function __construct(array $events){
    parent::__construct($events, array(
      "get_user_id",
      "generate_plugin_user_id",
      "register_to_api",
      "update_user_roster",
      "update_user_password"
    ));
  }
}
?>
