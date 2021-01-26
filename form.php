<?php 

class Form {

  private static $csrf;

  public $errors = [];
  public $fields = [];
  private $inputs_schema;
  public $internal_error;

  /*
   * @param $input_schema An array of InputSchema objects.
   *
   * @param $is_post true if form method is post otherwise method is get.
   *        default: true (post).
   */
  public function __construct(array $inputs_schema, bool $is_post = true) {
    $this->check_schema(...$inputs_schema);
    $this->inputs_schema = $inputs_schema;
    $method = $is_post ? $_POST : $_GET; 

    $add_field = fn($fields,$field) => 
                $fields + [$field => trim(($method)[$field] ?? '')];
    $func = fn($fields,$schema) => $add_field($fields,$schema->field_name);
    $this->fields = array_reduce($inputs_schema,$func,[]);
    $this->method = $method;
  }

  /*
   * This function checks if the form valid and sets the $errors array
   * for the invalid fields.  
   * $internal_error is set when csrf token is invalid or missing.
   *
   * @return true if the form is valid (including csrf token) otherwise false.
   */
  public function validate() {
    if (@$_SESSION['csrf_token'] !== @($this->method)['csrf_token'] ?? 0) {
      $this->errors[0] = "Missing or invalid csrf token! you must call the method csrf() inside your form.";
      return false;
    }
    $fields = $this->fields;

    $func = function($all_errors,$schema) use ($fields) {
      $field = $schema->field_name;
      $value = $fields[$field];
      $errors = $schema->validate($value);
      if ($errors) $all_errors[$field] = $errors;
      return $all_errors;
    };
    $this->errors = array_reduce($this->inputs_schema,$func,[]);
    return empty($this->errors);
  }


  /*
   * This method must be called if the user is logged in 
   * (otherwise the form won't validate).
   *
   * @return Hidden input field with a random csrf token.
   */
  public function csrf() {
    if (!self::$csrf) {
      $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
      $token = $_SESSION['csrf_token'];
      self::$csrf =  "<input type='hidden' name='csrf_token' value='$token'>";
    }
    return self::$csrf;
  }

  private function check_schema(InputSchema ...$inputs_schema) { }

}

