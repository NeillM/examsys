<?php
/**
 * Created by JetBrains PhpStorm.
 * User: cczsa1
 * Date: 03/01/13
 * Time: 13:37
 * To change this template use File | Settings | File Templates.
 */
class outline_authentication {

  protected $name;
  protected $number;
  protected $returndata;
  protected $retdata;
  protected $form;
  protected $settings;
  protected $db;
  protected $calling_object;
  public $rogoid = FALSE;


  function __construct($calling_object, $settings, $number, $name, $db, &$returndata, $form) {
    $this->db = new mysqli();
    $this->db = $db;
    $this->calling_object = $calling_object;
    $this->returndata = & $returndata;
    $this->number = $number;
    $this->retdata = $returndata[$number];
    $this->form = $form;
    $this->settings = $settings;
    $this->name = $name;
  }


  function set_fail() {
    $this->retdata->success = FALSE;
    $this->retdata->form = 'std';
    $this->retdata->rogoid = 0;
    $this->retdata->url = '';
  }

  function savetodebug($debugmessage) {
    $this->retdata->debug[] = $debugmessage;
  }

  function get_callback($section) {
    return $this->calling_object->get_callback($section);
  }

  function get_module_debug($objid) {
    return $this->returndata[$objid]->get_new_debug_messages();
  }

  function get_module_authinfo($objid) {
    return $this->calling_object->authinfo[$objid];
  }

  function register_callback($callback, $section, $number, $name, $insert = FALSE) {
    return $this->calling_object->register_callback($callback, $section, $number, $name, $insert);
  }

  function register_callback_routines() {
  }

}

}
