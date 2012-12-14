<?php
/**
 * Created by JetBrains PhpStorm.
 * User: cczsa1
 * Date: 14/12/12
 * Time: 14:33
 * To change this template use File | Settings | File Templates.
 */
class guestlogin_auth {

  private $name;
  private $number;
  private $returndata;
  private $retdata;
  private $form;
  private $settings;
  private $db;
  private $calling_object;
  private $updatable = FALSE;
  public $rogoid = FALSE;


  function __construct($calling_object, $settings, $number, $name, $db, &$returndata, $form) {
    $this->db = new mysqli();
    $this->db = $db;
    $this->calling_object = $calling_object;
    $this->returndata = $returndata;
    $this->number = $number;
    $this->retdata = $returndata[$number];
    $this->form = $form;
    $this->settings = $settings;
    $this->name = $name;
  }

  function register_callback_routines() {
    $this->calling_object->register_callback(array($this, 'loginbutton'), 'displaystdform', $this->number, $this->name);
  }

  function loginbutton(&$displaystdformobj) {
$this->retdata->debug[]='Button Check';
    $displaybutton=true;
    // detect if we should display login button

    if($displaybutton===true) {
      $this->retdata->debug[]='Adding New Button';
      $newbutton=new displaystdformobjbutton();
      $newbutton->type='submit';
      $newbutton->value=' Guest Login ';
      $newbutton->name='guestlogin';
      $displaystdformobj->buttons[]=$newbutton;
    }

  }

}
