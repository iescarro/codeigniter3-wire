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

class CI_Component
{
  private $CI;
  private $component_name;
  private $properties;
  private $methods;

  function __construct()
  {
    $this->CI = &get_instance();
  }

  function assign_data($data)
  {
    foreach ($data as $key => $value) {
      if (property_exists($this, $key)) {
        $this->$key = $value;
      }
    }
  }

  function get_properties_and_methods()
  {
    $childClass = new ReflectionClass($this);
    $parentClass = $childClass->getParentClass();
    $childMethods = $childClass->getMethods();
    $parentMethods = $parentClass ? $parentClass->getMethods() : [];
    $parentMethodNames = array_map(function ($method) {
      return $method->getName();
    }, $parentMethods);

    $childClassMethodsOnly = array_filter($childMethods, function ($method) use ($parentMethodNames) {
      return !in_array($method->getName(), $parentMethodNames);
    });
    return array(lcfirst($childClass->getName()), $childClass->getProperties(), $childClassMethodsOnly);
  }

  function render()
  {
    // TODO:
  }

  function view($view, $data = null)
  {
    // print_pre($this->get_properties_and_methods());

    list($this->component_name, $this->properties, $this->methods) = $this->get_properties_and_methods();

    echo $this->load_scripts();
    $this->CI->load->view($view, $data);
    echo $this->load_component_scripts();
  }

  function load_scripts()
  {
    return "\n<script src='https://unpkg.com/@hotwired/stimulus@3.0.1/dist/stimulus.umd.js'></script>\n\n";
  }

  function load_component_scripts()
  {
    $this->CI->load->helper(['url']);
    $base_url = trim(base_url(), '/');

    $component_name = $this->component_name;
    $data_targets = '';
    $data = [];
    $javascript_properties = '';
    foreach ($this->properties as $property) {
      $property_name =  $property->name;
      $targets[] = $property_name;
      $data[$property_name] = 'this.' . $property_name;
      $javascript_properties .= "
        get {$property_name}() { return this.{$property_name}Target.textContent; }
        set {$property_name}(value) { this.{$property_name}Target.textContent = value; }";
      $data_targets = "
              _this.{$property_name} = data.{$property_name};
              console.log('Updated {$property_name}:', _this.{$property_name});
              ";
    }

    $javascript_methods = '';
    foreach ($this->methods as $method) {
      $method_name = $method->name;

      $method_parameters = '';
      $params = [];
      foreach ($method->getParameters() as $parameter) {
        $parameter_name = $parameter->getName();
        $method_parameters .= "
          const {$parameter_name} = e.currentTarget.dataset.{$parameter_name};";
        $params[$parameter_name] = $parameter_name;
      }

      $payload = array('component' => $component_name, 'action' => array('name' => $method_name, 'params' => $params), 'data' => $data);

      $javascript_methods .= "
        {$method_name}(e) {
$method_parameters
          const payload = " . stringify_payload($payload) . ";
          console.log('Payload before sending', payload);
          const _this = this;
          fetch('{$base_url}/component_api/request', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify(payload)
            })
            .then(response => {
              return response.json()
            })
            .then(data => {
              console.log('Response data', data);
{$data_targets}
            })
            .catch(error => {
              console.error(error);
            });
        }";
    }
    return "
<script type='module'>
  import {
    Application
  } from 'https://unpkg.com/@hotwired/stimulus@3.0.1/dist/stimulus.js';

  const application = Application.start();
  
  application.register('{$this->component_name}', class extends Stimulus.Controller {
    static targets = " . json_encode($targets) . "
$javascript_properties
$javascript_methods
  });
</script>";
  }
}

function stringify_payload($payload)
{
  $component_name = $payload['component'];

  $action_name = $payload['action']['name'];
  $action = "{name:'$action_name'";
  $params = $payload['action']['params'];
  if (count($params) > 0) {
    $action .= ',params:{';
    $i = 0;
    foreach ($params as $key => $value) {
      if ($i++ > 0) $action . ',';
      $action .= $key . ':' . $key;
    }
    $action .= '}';
  }
  $action .= '}';

  $data = '{';
  $i = 0;
  foreach ($payload['data'] as $key => $value) {
    if ($i++ > 0) $data .= ',';
    $data .= $key . ': this.' . $key;
  }
  $data .= '}';
  return "{component:'$component_name', action:{$action}, data:{$data}}";
}
