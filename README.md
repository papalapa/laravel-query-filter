### QueryFilter based on "Laravel Query Builder"

What if we need to build a query string in "safe" mode? 🤔

Or do we have multiple routes to get the sibling lists of models? 🙇

For example: 

```php
/**
 * @property $id int
 * @property $firstname string
 * @property $lastname string
 * @property $role string|null
 * @property $created_at Carbon
 */
class User {}

// ...

Route::get('/users', [UserController::class, 'users']);
Route::get('/users/superusers', [UserController::class, 'superusers']);
Route::get('/users/all', [UserController::class, 'all']);

// ...

class UserController {
    public function users() {
        return User::query()->whereNull('role')->get();
    }
    
    public function superusers() {
        return User::query()->whereNotNull('role')->get();
    }
    
    public function all() {
        return User::query()->orderBy('username')->get();
    }
}
```

Instead of this we can build query through query params! 🙋 

Also, we can change pagination page and limit, query sort direction or use filter conditions. 🧐 

Let's create DataProvider for model User:

```php
use Papalapa\Laravel\QueryFilter\BaseDataProvider;

final class UserDataProvider extends BaseDataProvider
{
    /**
     * Default sorting columns, when other not set 
     */
    protected array $defaultSort = [
        'id' => 'asc',
    ];
    
    /**
     * Final sorting columns, which use always 
     */
    protected array $finalSort = [
        'created_at' => 'desc',
    ];
    
    /**
     * Safe-attributes to use in filtration 
     */
    protected array $allowedFilter = [
        'name' => ['lastname', 'firstname'], // alias of two columns
        'role',
    ];

    /**
     *  Safe-attributes to use in sorting
     */
    protected array $allowedSort = [
        'name' => ['lastname', 'firstname'], // alias of two columns
        'datetime' => 'created_at',
    ];

    protected function makeBuilder() : EloquentBuilder
    {
        return User::query()
            ->select([
                'id',
                'lastname',
                'firstname',                
                'role',
            ]);
    }
}
```

Ok, now we are ready to refactor our routes and controller:

```php
Route::get('/users', [UserController::class, 'users']);

class UserController {
    public function users(UserDataProvider $dataProvider) {
        return $dataProvider->paginate();
    }
}
```

That is all! 💥 But how it works? ❓

Our new request must contain special query attributes to control query building: ⚡

```
https://domain.local/users
    ?_filter={"name": "^John", "or": [ {"role": "admin"}, {"role": "manager"} ], "and": [ {"datetime": ">=2021-01-01"}, {"datetime": "<=2021-02-01"} ]}
    &_sort=datetime,-name
    &_order=desc
    &_page=2
    &_limit=50
```

Built SQL-query will be: ✨

```sql
SELECT id, firstname, lastname, role
FROM users
WHERE
      ((lastname LIKE 'John%') OR (firstname LIKE 'John%'))
      AND
      ((role = 'admin') OR (role = 'manager'))
      AND
      ((created_at >= '2021-01-01') AND (created_at <= '2021-02-01'))
ORDER BY created_at ASC, lastname DESC, firstname DESC 
LIMIT 50 OFFSET 50
```

Addition conditions:

```
<> >= != <= > = <

! === NOT LIKE '%xxx%'
* === LIKE '%xxx%'
^ === LIKE '%xxx'
$ === LIKE 'xxx%'
```

What with NULL:

```
{"role": null} === role IS NULL
{"role": "~"} === role IS NOT NULL
or
{"is null": "role"}
{"is not null": "role"}

```

Easy! 🙂
