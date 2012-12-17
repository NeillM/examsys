<?php

function do_admin_login($browser) {
  $browser->open("/staff/");
  $browser->type("name=ROGO_USER", "selenium");
  $browser->type("name=ROGO_PW", "srh*63Hh");
  $browser->click("name=rogo-login-form-std");
  $browser->waitForPageToLoad("30000");
}

function do_staff_login($browser) {
  $browser->open("/staff/");
  $browser->type("name=ROGO_USER", "teststaff");
  $browser->type("name=ROGO_PW", "lxn&98X21");
  $browser->click("name=rogo-login-form-std");
  $browser->waitForPageToLoad("30000");
}

function do_student_login($browser, $username, $password) {
  $browser->open("/staff/");
  $browser->type("name=ROGO_USER", $username);
  $browser->type("name=ROGO_PW", $password);
  $browser->click("name=rogo-login-form-std");
  $browser->waitForPageToLoad("30000");
}