<?php

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
