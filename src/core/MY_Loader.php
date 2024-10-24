<?php

/**
 * CodeIgniter3
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2024, CodeIgniter3 Team
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter3
 * @author	CodeIgniter3 Team
 * @copyright	Copyright (c) 2024, CodeIgniter3 Team (https://github.com/iescarro/codeigniter3-wire)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://github.com/iescarro/codeigniter3-wire
 * @since	Version 1.0.0
 * @filesource
 */
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

  function components()
  {
    // Define the path to the jobs directory
    $components_path = APPPATH . 'components/';

    // Check if the jobs directory exists
    if (is_dir($components_path)) {
      // Scan the jobs directory for PHP files
      $files = scandir($components_path);

      // Loop through each file and include it
      foreach ($files as $file) {
        // Ensure we only include PHP files
        if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
          require_once $components_path . $file;
        }
      }
    }
  }
}
