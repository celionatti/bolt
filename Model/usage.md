# Explanation of Model Creation and DatabaseModel

## Custom Model Creation

### Creating a New User

use App\Models\User;

``` // Create a new user
    $newUser = new User([
        'name' => 'John Doe',
        'email' => 'john.doe@example.com',
        'password' => 'secret', // Password will be hashed automatically
    ]);

    // Save the user to the database
    $newUser->save();

    echo "User created with ID: " . $newUser->id;
```

### Creating a New User with Dynamic Attributes

``` use App\Models\User;

    // Create a new user instance
    $newUser = new User();

    // Dynamically set attributes
    $newUser->name = 'John Doe';
    $newUser->email = 'john.doe@example.com';
    $newUser->password = 'secret'; // Password will be hashed automatically

    // Save the user to the database
    $newUser->save();

    echo "User created with ID: " . $newUser->id;
```

### Updating a User with Dynamic Attributes

``` use App\Models\User;

    // Find a user by ID
    $user = User::find(1);

    if ($user) {
        // Dynamically set attributes
        $user->name = 'Jane Doe';
        $user->email = 'jane.doe@example.com';
        $user->password = 'new_secret'; // Password will be hashed automatically

        // Save the updated user to the database
        $user->save();

        echo "User updated: " . $user->name;
    } else {
        echo "User not found";
    }
```

### Finding a User by ID

``` use App\Models\User;

    // Find a user by ID
    $user = User::findUser(1);

    if ($user) {
        echo "User found: " . $user->name;
    } else {
        echo "User not found";
    }
```

### Find by a different attribute

``` $foundUserByEmail = $user->findBy(['email' => 'john.smith@example.com']);
    if ($foundUserByEmail) {
        echo $foundUserByEmail->name; // Output: John Smith
    }
```

### Updating a User

``` use App\Models\User;

    // Find a user by ID
    $user = User::find(1);

    if ($user) {
        // Update user attributes
        $user->name = 'Jane Doe';
        $user->email = 'jane.doe@example.com';
        $user->save();

        echo "User updated: " . $user->name;
    } else {
        echo "User not found";
    }
```

### Deleting a User

``` use App\Models\User;

// Find a user by ID
$user = User::find(1);

if ($user) {
    // Delete the user
    $user->delete();
    echo "User deleted";
} else {
    echo "User not found";
}
```

### Listing All Users

``` use App\Models\User;

    // Get all users
    $users = User::all();

    foreach ($users as $user) {
        echo "User: " . $user->name . ", Email: " . $user->email . "\n";
    }
```

### Paginating Users

``` use App\Models\User;

// Paginate users
$page = 1;
$itemsPerPage = 10;
$pagination = User::paginate($page, $itemsPerPage);

echo "Current Page: " . $pagination['pagination']['current_page'] . "\n";
echo "Total Pages: " . $pagination['pagination']['total_pages'] . "\n";

foreach ($pagination['data'] as $user) {
    echo "User: " . $user->name . ", Email: " . $user->email . "\n";
}
```

### Using where method

``` // Using where method for more custom queries
    try {
        $user = new User();
        $user->where('email', '=', 'john.smith@example.com');
        echo $user->name; // Output: John Smith
    } catch (BoltException $e) {
        echo $e->getMessage(); // Output: Record not found
    }
```

## Explanation

### Creating a New User: Instantiate the User class with the necessary attributes and call the save method to store the user in the database. The password is automatically hashed before saving

### Dynamically Creating a New User with Dynamic Attributes: A new User instance is created without passing attributes to the constructor. Attributes are dynamically set using the __set method. Calling save will handle hashing the password and saving the user to the database

### Finding a User by ID: Use the find method to retrieve a user by their primary key. The method returns a User object if found, or null if not

### Updating a User: Retrieve the user, modify their attributes, and call save to update the record in the database

### Deleting a User: Retrieve the user and call delete to remove the record from the database

### Listing All Users: Call the all method to get all users. Iterate through the result set to display each user

### Paginating Users: Use the paginate method to get users in a paginated format. It returns an array with data and pagination information
