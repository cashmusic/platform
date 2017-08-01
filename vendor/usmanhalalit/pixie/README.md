# Pixie Query Builder [![Build Status](https://travis-ci.org/usmanhalalit/pixie.png?branch=master)](https://travis-ci.org/usmanhalalit/pixie)
A lightweight, expressive, framework agnostic query builder for PHP it can also be referred as a Database Abstraction Layer. Pixie supports MySQL, SQLite and PostgreSQL and it takes care of query sanitization, table prefixing and many other things with a unified API. At least PHP 5.3 is required.

It has some advanced features like:

 - Query Events
 - Nested Criteria
 - Sub Queries
 - Nested Queries
 - Multiple Database Connections.

The syntax is quite similar to Laravel's query builder.

## Example
```PHP
// Make sure you have Composer's autoload file included
require 'vendor/autoload.php';

// Create a connection, once only.
$config = array(
            'driver'    => 'mysql', // Db driver
            'host'      => 'localhost',
            'database'  => 'your-database',
            'username'  => 'root',
            'password'  => 'your-password',
            'charset'   => 'utf8', // Optional
            'collation' => 'utf8_unicode_ci', // Optional
            'prefix'    => 'cb_', // Table prefix, optional
            'options'   => array( // PDO constructor options, optional
                PDO::ATTR_TIMEOUT => 5,
                PDO::ATTR_EMULATE_PREPARES => false,
            ),
        );

new \Pixie\Connection('mysql', $config, 'QB');
```

**Simple Query:**

The query below returns the row where id = 3, null if no rows.
```PHP
$row = QB::table('my_table')->find(3);
```

**Full Queries:**

```PHP
$query = QB::table('my_table')->where('name', '=', 'Sana');

// Get result
$query->get();
```

**Query Events:**

After the code below, every time a select query occurs on `users` table, it will add this where criteria, so banned users don't get access.

```PHP
QB::registerEvent('before-select', 'users', function($qb)
{
    $qb->where('status', '!=', 'banned');
});
```


There are many advanced options which are documented below. Sold? Let's install.

## Installation

Pixie uses [Composer](http://getcomposer.org/doc/00-intro.md#installation-nix) to make things easy.

Learn to use composer and add this to require section (in your composer.json):

    "usmanhalalit/pixie": "2.*@dev"

And run:

    composer update

Library on [Packagist](https://packagist.org/packages/usmanhalalit/pixie).

## Full Usage API

### Table of Contents

 - [Connection](#connection)
    - [Alias](#alias)
    - [Multiple Connection](#alias)
    - [SQLite and PostgreSQL Config Sample](sqlite-and-postgresql-config-sample)
 - [Query](#query)
 - [**Select**](#select)
    - [Get Easily](#get-easily)
    - [Multiple Selects](#multiple-selects)
    - [Select Distinct](#select-distinct)
    - [Get All](#get-all)
    - [Get First Row](#get-first-row)
    - [Get Rows Count](#get-rows-count)
 - [**Where**](#where)
    - [Where In](#where-in)
    - [Where Between](#where-between)
    - [Where Null](#where-null)
    - [Grouped Where](#grouped-where)
 - [Group By and Order By](#group-by-and-order-by)
 - [Having](#having)
 - [Limit and Offset](#limit-and-offset)
 - [Join](#join)
    - [Multiple Join Criteria](#multiple-join-criteria)
 - [Raw Query](#raw-query)
    - [Raw Expressions](#raw-expressions)
 - [**Insert**](#insert)
    - [Batch Insert](#batch-insert)
    - [Insert with ON DUPLICATE KEY statement](#insert-with-on-duplicate-key-statement)
 - [**Update**](#update)
 - [**Delete**](#delete)
 - [Transactions](#transactions)
 - [Get Built Query](#get-built-query)
 - [Sub Queries and Nested Queries](#sub-queries-and-nested-queries)
 - [Get PDO Instance](#get-pdo-instance)
 - [Fetch results as objects of specified class](#fetch-results-as-objects-of-specified-class)
 - [Query Events](#query-events)
    - [Available Events](#available-events)
    - [Registering Events](#registering-events)
    - [Removing Events](#removing-events)
    - [Some Use Cases](#some-use-cases)
    - [Notes](#notes)

___

## Connection
Pixie supports three database drivers, MySQL, SQLite and PostgreSQL. You can specify the driver during connection and the associated configuration when creating a new connection. You can also create multiple connections, but you can use alias for only one connection at a time.;
```PHP
// Make sure you have Composer's autoload file included
require 'vendor/autoload.php';

$config = array(
            'driver'    => 'mysql', // Db driver
            'host'      => 'localhost',
            'database'  => 'your-database',
            'username'  => 'root',
            'password'  => 'your-password',
            'charset'   => 'utf8', // Optional
            'collation' => 'utf8_unicode_ci', // Optional
            'prefix'    => 'cb_', // Table prefix, optional
        );

new \Pixie\Connection('mysql', $config, 'QB');

// Run query
$query = QB::table('my_table')->where('name', '=', 'Sana');
```

### Alias
When you create a connection:
```PHP
new \Pixie\Connection('mysql', $config, 'MyAlias');
```
`MyAlias` is the name for the class alias you want to use (like `MyAlias::table(...)`), you can use whatever name (with Namespace also, `MyNamespace\\MyClass`) you like or you may skip it if you don't need an alias. Alias gives you the ability to easily access the QueryBuilder class across your application.

When not using an alias you can instantiate the QueryBuilder handler separately, helpful for Dependency Injection and Testing.

```PHP
$connection = new \Pixie\Connection('mysql', $config));
$qb = new \Pixie\QueryBuilder\QueryBuilderHandler($connection);

$query = $qb->table('my_table')->where('name', '=', 'Sana');

var_dump($query->get());
```

`$connection` here is optional, if not given it will always associate itself to the first connection, but it can be useful when you have multiple database connections.

### SQLite and PostgreSQL Config Sample
```PHP
new \Pixie\Connection('sqlite', array(
                'driver'   => 'sqlite',
			    'database' => 'your-file.sqlite',
			    'prefix'   => 'cb_',
		    ), 'QB');
```

```PHP
new \Pixie\Connection('pgsql', array(
                    'driver'   => 'pgsql',
                    'host'     => 'localhost',
                    'database' => 'your-database',
                    'username' => 'postgres',
                    'password' => 'your-password',
                    'charset'  => 'utf8',
                    'prefix'   => 'cb_',
                    'schema'   => 'public',
                ), 'QB');
```

## Query
You **must** use `table()` method before every query, except raw `query()`.
To select from multiple tables just pass an array.
```PHP
QB::table(array('mytable1', 'mytable2'));
```


### Get Easily
The query below returns the (first) row where id = 3, null if no rows.
```PHP
$row = QB::table('my_table')->find(3);
```
Access your row like, `echo $row->name`. If your field name is not `id` then pass the field name as second parameter `QB::table('my_table')->find(3, 'person_id');`.

The query below returns the all rows where name = 'Sana', null if no rows.
```PHP
$result = QB::table('my_table')->findAll('name', 'Sana');
```


### Select
```PHP
$query = QB::table('my_table')->select('*');
```

#### Multiple Selects
```PHP
->select(array('mytable.myfield1', 'mytable.myfield2', 'another_table.myfield3'));
```

Using select method multiple times `select('a')->select('b')` will also select `a` and `b`. Can be useful if you want to do conditional selects (within a PHP `if`).


#### Select Distinct
```PHP
->selectDistinct(array('mytable.myfield1', 'mytable.myfield2'));
```


#### Get All
Return an array.
```PHP
$query = QB::table('my_table')->where('name', '=', 'Sana');
$result = $query->get();
```
You can loop through it like:
```PHP
foreach ($result as $row) {
    echo $row->name;
}
```

#### Get First Row
```PHP
$query = QB::table('my_table')->where('name', '=', 'Sana');
$row = $query->first();
```
Returns the first row, or null if there is no record. Using this method you can also make sure if a record exists. Access these like `echo $row->name`.


#### Get Rows Count
```PHP
$query = QB::table('my_table')->where('name', '=', 'Sana');
$query->count();
```

### Where
Basic syntax is `(fieldname, operator, value)`, if you give two parameters then `=` operator is assumed. So `where('name', 'usman')` and `where('name', '=', 'usman')` is the same.

```PHP
QB::table('my_table')
    ->where('name', '=', 'usman')
    ->whereNot('age', '>', 25)
    ->orWhere('type', '=', 'admin')
    ->orWhereNot('description', 'LIKE', '%query%')
    ;
```


#### Where In
```PHP
QB::table('my_table')
    ->whereIn('name', array('usman', 'sana'))
    ->orWhereIn('name', array('heera', 'dalim'))
    ;

QB::table('my_table')
    ->whereNotIn('name', array('heera', 'dalim'))
    ->orWhereNotIn('name', array('usman', 'sana'))
    ;
```

#### Where Between
```PHP
QB::table('my_table')
    ->whereBetween('id', 10, 100)
    ->orWhereBetween('status', 5, 8);
```

#### Where Null
```PHP
QB::table('my_table')
    ->whereNull('modified')
    ->orWhereNull('field2')
    ->whereNotNull('field3')
    ->orWhereNotNull('field4');
```

#### Grouped Where
Sometimes queries get complex, where you need grouped criteria, for example `WHERE age = 10 and (name like '%usman%' or description LIKE '%usman%')`.

Pixie allows you to do so, you can nest as many closures as you need, like below.
```PHP
QB::table('my_table')
            ->where('my_table.age', 10)
            ->where(function($q)
                {
                    $q->where('name', 'LIKE', '%usman%');
                    // You can provide a closure on these wheres too, to nest further.
                    $q->orWhere('description', 'LIKE', '%usman%');
                });
```

### Group By and Order By
```PHP
$query = QB::table('my_table')->groupBy('age')->orderBy('created_at', 'ASC');
```

#### Multiple Group By
```PHP
->groupBy(array('mytable.myfield1', 'mytable.myfield2', 'another_table.myfield3'));

->orderBy(array('mytable.myfield1', 'mytable.myfield2', 'another_table.myfield3'));
```

Using `groupBy()` or `orderBy()` methods multiple times `groupBy('a')->groupBy('b')` will also group by first `a` and than `b`. Can be useful if you want to do conditional grouping (within a PHP `if`). Same applies to `orderBy()`.

### Having
```PHP
->having('total_count', '>', 2)
->orHaving('type', '=', 'admin');
```

### Limit and Offset
```PHP
->limit(30);

->offset(10);
```

### Join
```PHP
QB::table('my_table')
    ->join('another_table', 'another_table.person_id', '=', 'my_table.id')

```

Available methods,

 - join() or innerJoin
 - leftJoin()
 - rightJoin()

If you need `FULL OUTER` join or any other join, just pass it as 5th parameter of `join` method.
```PHP
->join('another_table', 'another_table.person_id', '=', 'my_table.id', 'FULL OUTER')
```

#### Multiple Join Criteria
If you need more than one criterion to join a table then pass a closure as second parameter.

```PHP
->join('another_table', function($table)
    {
        $table->on('another_table.person_id', '=', 'my_table.id');
        $table->on('another_table.person_id2', '=', 'my_table.id2');
        $table->orOn('another_table.age', '>', QB::raw(1));
    })
```

### Raw Query
You can always use raw queries if you need,
```PHP
$query = QB::query('select * from cb_my_table where age = 12');

var_dump($query->get());
```

You can also pass your bindings
```PHP
QB::query('select * from cb_my_table where age = ? and name = ?', array(10, 'usman'));
```

#### Raw Expressions

When you wrap an expression with `raw()` method, Pixie doesn't try to sanitize these.
```PHP
QB::table('my_table')
            ->select(QB::raw('count(cb_my_table.id) as tot'))
            ->where('value', '=', 'Ifrah')
            ->where(QB::raw('DATE(?)', 'now'))
```


___
**NOTE:** Queries that run through `query()` method are not sanitized until you pass all values through bindings. Queries that run through `raw()` method are not sanitized either, you have to do it yourself. And of course these don't add table prefix too, but you can use the `addTablePrefix()` method.

### Insert
```PHP
$data = array(
    'name' => 'Sana',
    'description' => 'Blah'
);
$insertId = QB::table('my_table')->insert($data);
```

`insert()` method returns the insert id.

#### Batch Insert
```PHP
$data = array(
    array(
        'name'        => 'Sana',
        'description' => 'Blah'
    ),
    array(
        'name'        => 'Usman',
        'description' => 'Blah'
    ),
);
$insertIds = QB::table('my_table')->insert($data);
```

In case of batch insert, it will return an array of insert ids.

#### Insert with ON DUPLICATE KEY statement
```PHP
$data = array(
    'name'    => 'Sana',
    'counter' => 1
);
$dataUpdate = array(
    'name'    => 'Sana',
    'counter' => 2
);
$insertId = QB::table('my_table')->onDuplicateKeyUpdate($dataUpdate)->insert($data);
```

### Update
```PHP
$data = array(
    'name'        => 'Sana',
    'description' => 'Blah'
);

QB::table('my_table')->where('id', 5)->update($data);
```

Will update the name field to Sana and description field to Blah where id = 5.

### Delete
```PHP
QB::table('my_table')->where('id', '>', 5)->delete();
```
Will delete all the rows where id is greater than 5.

### Transactions

Pixie has the ability to run database "transactions", in which all database
changes are not saved until committed. That way, if something goes wrong or
differently then you intend, the database changes are not saved and no changes
are made.

Here's a basic transaction:

```PHP
QB::transaction(function ($qb) {
    $qb->table('my_table')->insert(array(
        'name' => 'Test',
        'url' => 'example.com'
    ));

    $qb->table('my_table')->insert(array(
        'name' => 'Test2',
        'url' => 'example.com'
    ));
});
```

If this were to cause any errors (such as a duplicate name or some other such
error), neither data set would show up in the database. If not, the changes would
be successfully saved.

If you wish to manually commit or rollback your changes, you can use the
`commit()` and `rollback()` methods accordingly:

```PHP
QB::transaction(function (qb) {
    $qb->table('my_table')->insert(array(/* data... */));

    $qb->commit(); // to commit the changes (data would be saved)
    $qb->rollback(); // to rollback the changes (data would be rejected)
});
```

### Get Built Query
Sometimes you may need to get the query string, its possible.
```PHP
$query = QB::table('my_table')->where('id', '=', 3);
$queryObj = $query->getQuery();
```
`getQuery()` will return a query object, from this you can get sql, bindings or raw sql.


```PHP
$queryObj->getSql();
// Returns: SELECT * FROM my_table where `id` = ?
```
```PHP
$queryObj->getBindings();
// Returns: array(3)
```

```PHP
$queryObj->getRawSql();
// Returns: SELECT * FROM my_table where `id` = 3
```

### Sub Queries and Nested Queries
Rarely but you may need to do sub queries or nested queries. Pixie is powerful enough to do this for you. You can create different query objects and use the `QB::subQuery()` method.

```PHP
$subQuery = QB::table('person_details')->select('details')->where('person_id', '=', 3);


$query = QB::table('my_table')
            ->select('my_table.*')
            ->select(QB::subQuery($subQuery, 'table_alias1'));

$nestedQuery = QB::table(QB::subQuery($query, 'table_alias2'))->select('*');
$nestedQuery->get();
```

This will produce a query like this:

    SELECT * FROM (SELECT `cb_my_table`.*, (SELECT `details` FROM `cb_person_details` WHERE `person_id` = 3) as table_alias1 FROM `cb_my_table`) as table_alias2

**NOTE:** Pixie doesn't use bindings for sub queries and nested queries. It quotes values with PDO's `quote()` method.

### Get PDO Instance
If you need to get the PDO instance you can do so.

```PHP
QB::pdo();
```

### Fetch results as objects of specified class
Simply call `asObject` query's method.

```PHP
QB::table('my_table')->asObject('SomeClass', array('ctor', 'args'))->first();
```

Furthermore, you may fine-tune fetching mode by calling `setFetchMode` method.

```PHP
QB::table('my_table')->setFetchMode(PDO::FETCH_COLUMN|PDO::FETCH_UNIQUE)->get();
```

### Query Events
Pixie comes with powerful query events to supercharge your application. These events are like database triggers, you can perform some actions when an event occurs, for example you can hook `after-delete` event of a table and delete related data from another table.

#### Available Events

 - before-select
 - after-select
 - before-insert
 - after-insert
 - before-update
 - after-update
 - before-delete
 - after-delete

#### Registering Events

```PHP
QB::registerEvent('before-select', 'users', function($qb)
{
    $qb->where('status', '!=', 'banned');
});
```
Now every time a select query occurs on `users` table, it will add this where criteria, so banned users don't get access.

The syntax is `registerEvent('event type', 'table name', action in a closure)`.

If you want the event to be performed when **any table is being queried**, provide `':any'` as table name.

**Other examples:**

After inserting data into `my_table`, details will be inserted into another table
```PHP
QB::registerEvent('after-insert', 'my_table', function($queryBuilder, $insertId)
{
    $data = array('person_id' => $insertId, 'details' => 'Meh', 'age' => 5);
    $queryBuilder->table('person_details')->insert($data);
});
```

Whenever data is inserted into `person_details` table, set the timestamp field `created_at`, so we don't have to specify it everywhere:
```PHP
QB::registerEvent('after-insert', 'person_details', function($queryBuilder, $insertId)
{
    $queryBuilder->table('person_details')->where('id', $insertId)->update(array('created_at' => date('Y-m-d H:i:s')));
});
```

After deleting from `my_table` delete the relations:
```PHP
QB::registerEvent('after-delete', 'my_table', function($queryBuilder, $queryObject)
{
    $bindings = $queryObject->getBindings();
    $queryBuilder->table('person_details')->where('person_id', $binding[0])->delete();
});
```



Pixie passes the current instance of query builder as first parameter of your closure so you can build queries with this object, you can do anything like usual query builder (`QB`).

If something other than `null` is returned from the `before-*` query handler, the value will be result of execution and DB will not be actually queried (and thus, corresponding `after-*` handler will not be called ether).

Only on `after-*` events you get three parameters: **first** is the query builder, **third** is the execution time as float and **the second** varies:

 - On `after-select` you get the `results` obtained from `select`.
 - On `after-insert` you get the insert id (or array of ids in case of batch insert)
 - On `after-delete` you get the [query object](#get-built-query) (same as what you get from `getQuery()`), from it you can get SQL and Bindings.
 - On `after-update` you get the [query object](#get-built-query) like `after-delete`.

#### Removing Events
```PHP
QB::removeEvent('event-name', 'table-name');
```

#### Some Use Cases

Here are some cases where Query Events can be extremely helpful:

 - Restrict banned users.
 - Get only `deleted = 0` records.
 - Implement caching of all queries.
 - Trigger user notification after every entry.
 - Delete relationship data after a delete query.
 - Insert relationship data after an insert query.
 - Keep records of modification after each update query.
 - Add/edit created_at and updated _at data after each entry.

#### Notes
 - Query Events are set as per connection basis so multiple database connection don't create any problem, and creating new query builder instance preserves your events.
 - Query Events go recursively, for example after inserting into `table_a` your event inserts into `table_b`, now you can have another event registered with `table_b` which inserts into `table_c`.
 - Of course Query Events don't work with raw queries.

___
If you find any typo then please edit and send a pull request.

&copy; 2016 [Muhammad Usman](http://usman.it/). Licensed under MIT license.
