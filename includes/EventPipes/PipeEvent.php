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
 * A single event should only be concerned with a single task
 * It should only care that its argument requirements are met 
 * and it's return data satisfies everything it wanted to return;
 *
 * If the return data is not what is expected then the event should
 * retrun an error
 *
 * 100% ok or 0% ok
 *
 * for example:
 * if the event is supposed to get an array with a key "user_id" and value "123" or some combination of that
 * then the event should check for that and return an error if it is not found.
 *
 * The idea is to make the events as modular as possible so that they can be reused in other event trains
 * And to ensure a single allowed path of execution as opposed to a bunch of if statements that try to handle
 * every possible non intended case.
 *
 * @author  FiD Access
 * @version 1.1.0
 * @since   1.1.0
 * 
 *
 * */
interface PipeEvent {
  public function run($data):PipeEventReturn;
}
?>
