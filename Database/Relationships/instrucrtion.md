# Instruction on Relationship Usage

## For BelongsToMany Model

``` <?php

namespace App\Models;

use celionatti\Bolt\Database\Model\DatabaseModel;

class User extends DatabaseModel
{
    protected $fillable = ['name', 'email'];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
    }
}

class Role extends DatabaseModel
{
    protected $fillable = ['name'];

    // No need to add relationships in this class for this example
}

```

### Using the relationship for BelongsToMany

``` $user = (new User())->find(1);
$roles = $user->roles()->get();

foreach ($roles as $role) {
    echo $role->name . "\n";
}

```

## For HasOne Model

``` <?php

namespace App\Models;

use celionatti\Bolt\Database\Model\DatabaseModel;

class User extends DatabaseModel
{
    protected $fillable = ['name', 'email'];

    public function profile()
    {
        return $this->hasOne(Profile::class, 'user_id', 'id');
    }
}

class Profile extends DatabaseModel
{
    protected $fillable = ['bio', 'user_id'];

    // No need to add relationships in this class for this example
}
```

### Using the relationship for HasOne

``` $user = (new User())->find(1);
$profile = $user->profile()->get();

if ($profile) {
    echo $profile->bio;
}
```

## For HasMany and BelongsTo

``` <?php

namespace App\Models;

use celionatti\Bolt\Database\Model\DatabaseModel;

class User extends DatabaseModel
{
    protected $fillable = ['name', 'email'];

    public function posts()
    {
        return $this->hasMany(Post::class, 'user_id', 'id');
    }
}

class Post extends DatabaseModel
{
    protected $fillable = ['title', 'content', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
```

### Using the relationship of the BelongsTo and HasMany

``` $user = (new User())->find(1);
$posts = $user->posts()->get();

foreach ($posts as $post) {
    echo $post->title . "\n";
}

$post = (new Post())->find(1);
$user = $post->user()->get();

echo $user->name;
```
