<?php

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
    print_pre($this->get_properties_and_methods());

    list($this->component_name, $this->properties, $this->methods) = $this->get_properties_and_methods();

    echo $this->load_scripts();
    $this->CI->load->view($view, $data);
    echo $this->load_component_scripts();
  }

  function load_scripts()
  {
    return '<script src="https://unpkg.com/@hotwired/stimulus@3.0.1/dist/stimulus.umd.js"></script>';
  }

  function load_component_scripts()
  {
    $targets = [];
    foreach ($this->properties as $property) {
      $targets[] = $property->name;
    }
    $javascript_methods = '';
    foreach ($this->methods as $method) {
      $method_name = $method->name;
      $javascript_methods .= "
        {$method_name}() {
          fetch('http://localhost:8888/iceithq/backlog/component_api/request', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ 
                controller: '{$this->component_name}', 
                action: '{$method_name}'
              })
            })
            .then(response => response.json())
            .then(data => {
              this.counterTarget.textContent = JSON.stringify(data);
            })
            .catch(error => {
              this.outputTarget.textContent = 'Error fetching data';
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
$javascript_methods
  });
</script>";
  }
}
