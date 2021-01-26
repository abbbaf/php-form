<?php

class InputSchema { 


  private $validate = [];
  private $utf8;
  private $field_name;
  private $is_file = false;
  private $is_optional = false;
  private $label = '';
  private $error_if_required;

  public function __construct($field_name,$error_if_required='[:label:] is required.') {
    $this->field_name = $field_name;
    $this->error_if_required = $error_if_required;
    $this->label = $field_name;
  }

  public function __get($prop) {
    if ($prop === 'field_name') return $this->$prop;
  }


  /*
   *
   * @param $value Input field value.
   *
   * @return Array of all errors (or null).
   *
   */

  public function validate($value) {
    if (!$value && $this->is_optional) return null;
    $result = array_map(fn($func) => $func($value),$this->validate);
    $errors = array_filter($result,fn($error) => $error !== true);
    if (!$value) $errors[] = $this->error_if_required;
    return $errors ?? null;
  }


  public function label(string $label) {
    $this->label = $label;
    return $this;
  }


  public function optional() {
    $this->is_optional = true;
    return $this;
  }


  public function utf8() {
    $this->utf8 = true;
    return $this;
  }

  private function validate_func($val,$error,$func) {
    $error = str_replace('[:param:]',strval($val),$error);
    return fn($value) => 
      ($func($value) ? true : null) 
      ?? str_replace('[:label:]',$this->label,$error);
  }

  public function max(int $max_value,string $error='[:label:] must be less than [:param:] characters.') {
    $this->validate[] = $this->validate_func($max_value,$error,
      fn($str) =>
         ($this->utf8() ? mb_strlen($str) : strlen($str)) <= $max_value   
    );
    return $this;
  }

  public function min(int $min_value,string $error='[:label:] must be min of [:param:] characters.') {
    $this->validate[] = $this->validate_func($min_value,$error,
      fn($str) =>
        ($this->utf8() ? mb_strlen($str) : strlen($str)) >= $min_value  
    );
    return $this;
  }

  public function email(string $error='Must be a valid email') {
    $this->validate[] = $this->validate_func(null,$error,
      fn($str) => filter_var($str,FILTER_VALIDATE_EMAIL)
    );
    return $this;
  }

  public function file(string $error='No file uploaded') {
    $field_name = $this->field_name;
    $this->validate[] = $this->validate_func(null,$error,
        fn($value) => @$_FILES[$field_name]['error'] !== 0
    );
    return $this;
  }

  public function max_size(int $max_size,string $error='File size must be less than [:param:] bytes.') {
    $field_name = $this->field_name;
    $this->validate[] = $this->validate_func($max_size,$error,
      fn($value) => @$_FILES[$field_name] 
                    && $_FILES[$field_name]['size'] <= $max_size
    );
  }

  public function ext(array $extentions,string $error='File extension not supported.') {
    $this->file();
    $field_name = $this->field_name;
    $this->validate[] = $this->validate_func($extentions,$error,
        fn() => @$_FILES[$field_name] && in_array(
            pathinfo($_FILES[$field_name]['name'])['extension']
            ,$extensions)
    );
  }


}
