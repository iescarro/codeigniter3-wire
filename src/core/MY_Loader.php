<?php

defined('BASEPATH') or exit('No direct script access allowed');

class MY_Loader extends CI_Loader
{
  var $CI;

  function __construct()
  {
    $this->CI = &get_instance();
  }

  public function component($component_name)
  {
    $component_path = APPPATH . 'components/' . $component_name . '.php';

    if (file_exists($component_path)) {
      include_once($component_path);

      if (class_exists($component_name)) {
        $component = new $component_name();
        $component->render();
        $this->CI->$component_name = $component;
      } else {
        show_error("Component class not found: " . $component_name);
      }
    } else {
      show_error("Component file not found: " . $component_name);
    }
  }
}
