# Form with built in validation in PHP.
This module allows to easily create and validate input fields.  It also includes built in csrf prevention.

## How to use?

### 1. Create an array of InputSchema objets:

Creating one object:

`$schema = new InputSchema($input_field_name, $error (optional));`

The $error contains a string of the error message in case the value of the input field is empty.

`$input_schema_array = [ $schema.min(5].max(40) ];`

(See list of supported methods below).

### 2. Create a form object:

`$form = new Form($input_schema_array);`

If method is 'get' then the constructor must have a boolean false as a second argument.

Inside your form you must call $form->csrf

### 3. Check if the form is valid and set all errors;

`$form->validate();`

This method will return false if method any of the schemas in the $input_schema_array failed
or if **csrf token** check faield.

### 4. Create the form fields.

$form->fields - An associative array for all form fields and values.  ['field name' => 'field value'] .
$form->errors - An associative array of all errors.  ['field_name' => [errors_array] ].

### 5. CSRF protection.

`$form->csrf();`
Execute the above method inside your form:
`<form>
<?= $form->csrf(); ?>  
</form>`

If you don't the the validate metho will fail.



## InputSchema methods:

**min(int $min_length)** - Sets the minimum length of the input field.

**max(int $max_length)** - Sets the maximum length of the input field.

**email()** - The input field must be a valid email address.

**file()** - The input field must be a file.

**max_size(int $max_file_size)** - Sets maximum size for file upload.

**ext(array $extensions)** - Sets the supported file extensions.

All the above methods methods accepts an additional paramater $custom_error
which allows to create a custom error message instead of the built in one.

**optional()** - Sets form field as optional.

**utf8()** - Sets utf8 support (default ascii).

**label($label_name)** - Sets the label name which will be displayed in the error message.

### Create a custom error:

The custom error is a string which can contain two placeholders:
`[:label:]` for label sets by the label() method.
`[:param:]` which is the value of first parameter of the above methods.

An error message example of max() method.
`[:label:]` must have no more then `[:param:]` characters.











