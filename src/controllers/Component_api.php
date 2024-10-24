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

class Component_api extends CI_Controller
{
  var $session;

  function __construct()
  {
    parent::__construct();
    $this->load->helper(['common']);
    $this->load->library(['session']);
    $this->load->components();
    header('Content-Type: application/json');
    header("Access-Control-Allow-Origin: *");
  }

  function request()
  {
    $raw_json = file_get_contents('php://input');
    $request = json_decode($raw_json);
    print_pre($request);

    $component = $request->component;
    $action = $request->action;
    $data = $request->data;
    // print_pre($data);

    $c = new $component();
    $c->assign_data($data);
    print_pre(get_object_vars($c));
    $c->$action();

    $response = get_object_vars($c);
    // print_pre($c->get_properties_and_methods());

    // $number = $this->session->userdata('number');
    // $number = $number ? $number : 0;
    // $data->number = $number++;
    // $this->session->set_userdata('number', $number);
    echo json_encode($response);
  }
}
