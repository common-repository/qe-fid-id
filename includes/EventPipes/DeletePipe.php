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
 * "send_to_api",
 * "delete_from_roster",
 * "reinstate_user_credentials",
 * 
 */
final class DeletePipe extends Pipe {
  public function __construct(array $events){
    parent::__construct($events, array(
      "send_to_api",
      "delete_from_roster",
      "reinstate_user_credentials",
    ));
  }
}
?>
