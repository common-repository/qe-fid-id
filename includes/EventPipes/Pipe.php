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

use Exception;
use QeFid\ID\Constants\PipeStatus;

class Pipe {
  private array $events;
  private array $completed_events;
  private int $status;

  public function __construct(array $events, array $event_train){
    if(count($events) === 0||count($event_train) === 0||count($events)< count($event_train)){
      throw new \Exception("Invalid events :".count($events).", for required event train :".count($event_train));
    }

    foreach ($event_train as $key) {
      if(!array_key_exists($key, $events)){
        throw new \Exception("Invalid event train, missing event :".$key);
      }
    }
    $this->events = $events;
    $this->completed_events = array();
    $this->status = PipeStatus::$NotStarted;
  }

  // This function will be used to run the event train
  // run events in order and move from event queue to completed events when done
  // if an event fails then the event train will stop and the status will be set to the
  // index of the event that failed
  public function run_pipe($data){
    $this->reset_pipe_state();
    $this->status = PipeStatus::$Running;
    $entry_data = $data;
    foreach ($this->events as $key => $value) {
      try {

        $result =$value->run($entry_data);
        if($result->error!==null){
          $this->status = PipeStatus::$Error;
          return array("event"=>$key,"data"=>$entry_data,"result"=>$result,"status"=>"error");
        }
        $entry_data = $result->data;
        $this->add_completed_events($key, $result);
      }catch (Exception $e) {
        return array("event"=>$key,"data"=>$entry_data,"result"=>"exception","status"=>"error","exception"=>$e);
      }
    } 
    return array("event"=>"success","data"=>$entry_data,"result"=>"success","status"=>"success");
  }

  public function get_completed_events(){
    return $this->completed_events;
  }

  public function get_event_count(){
    return count($this->events);
  }

  public function get_status(){
    return $this->status;
  }

  private function add_completed_events($event_key,$event_result){
    $this->completed_events[$event_key] = $event_result;
    if(count($this->events)===count($this->completed_events)) $this->status = PipeStatus::$Completed;
  }

  private function reset_pipe_state(){
    $this->status=PipeStatus::$NotStarted;
    $this->completed_events = array();
  }
}

?>
