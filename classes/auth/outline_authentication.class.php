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


  /**
   * @param $calling_object object its called from
   * @param $settings array settings options
   * @param $number int the number this is
   * @param $name string the name of it
   * @param $db object a lin kto db
   * @param $returndata object where data is stored
   * @param $form object a class with form data in
   */
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


  /**
   * set failure settings
   */
  function set_fail() {
    $this->retdata->success = FALSE;
    $this->retdata->form = 'std';
    $this->retdata->rogoid = 0;
    $this->retdata->url = '';
  }

  /**
   * @param $debugmessage string the debug message to store
   */
  function savetodebug($debugmessage) {
    $this->retdata->debug[] = $debugmessage;
  }

  /**
   * @param $section string the section to get the callback from
   * @return mixed
   */
  function get_callback($section) {
    return $this->calling_object->get_callback($section);
  }

  /**
   * @param $objid int the objectid
   * @return mixed
   */
  function get_module_debug($objid) {
    return $this->returndata[$objid]->get_new_debug_messages();
  }

  /**
   * @param $objid int the objectid
   * @return mixed
   */
  function get_module_authinfo($objid) {
    return $this->calling_object->authinfo[$objid];
  }

  /**
   * @param $callback callback routine
   * @param $section string section to register callback in
   * @param $number string the number this object is
   * @param $name string the name this object is
   * @param $insert bool to insert rather than append
   * @return bool
   */
  function register_callback($callback, $section, $number, $name, $insert = FALSE) {
    return $this->calling_object->register_callback($callback, $section, $number, $name, $insert);
  }

  /**
   *
   */
  function register_callback_routines() {
    //this is blank so that classes that dont register anything dont break
  }

  /**
   * @param $setting string the setting to return or false if it doesnt exist
   * @return mixed
   */
  function get_settings($setting) {
    if (!isset($this->settings[$setting])) {
      return FALSE;
    }

    return $this->settings[$setting];
  }

}


