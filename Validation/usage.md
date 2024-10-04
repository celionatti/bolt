# Validator Usage

## Below is an example User model demonstrating how to use the Validator class, including the unique validation rule

``` namespace celionatti\Bolt\Models;

use celionatti\Bolt\Database\Model\DatabaseModel;

class User extends DatabaseModel
{
    protected $table = 'users';
    protected $fillable = ['name', 'email', 'password'];
    protected $rules = [
        'name' => 'required|string|min:3|max:50',
        'email' => "required|email|unique:users.email,user_id!=$id",
        'password' => 'required|string|min:6'
    ];
}
```

## Example Usage

### Here is an example of how to create a new user while leveraging the Validator

``` use celionatti\Bolt\Models\User;

$user = new User();

try {
    $user->create([
        'name' => 'John Doe',
        'email' => 'johndoe@example.com',
        'password' => 'securepassword'
    ]);
} catch (BoltException $e) {
    // Handle validation errors
    $errors = $e->getErrors();
    print_r($errors);
}
```
