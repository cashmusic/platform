# CASH Platform ORM basics

The idea here is to make data more interactive and accessible. ORM works on a very basic level, with some added shortcuts to the Doctrine/ORM package.

All Entity (model) classes extend EntityBase which gives us some shortcuts to do common operations. At the time of this writing, more complex queries will be done directly through the Doctrine QueryBuilder.

EntityBase uses magic getter and setter methods so that we can get properties with the normal `$object->property` approach, but the class also make it easy to add custom getters and setters with the `get{foo}Attribute` and `set{foo}Attribute` methods.

So if you wanted to override the `$user->password` setter property you could do this on the People entity:

```
public function setPasswordAttribute($value) {
     $this->password = md5($value);
 }
```

## $fillable

Every entity class should have a protected property `$fillable`, which the magic setter method checks against. The magic setter property will drop any fields not in `$fillable`, and will not try to write them in any sort of set operation. This is both to filter out garbage but also to give control over whether or not folks can write over special database fields like `creation_date`.

# Shortcut functions from EntityBase

## {EntityName}::find($id)

Find a model by unique id.

`$asset = Asset::find(5);`

## {EntityName}::all($limit=null, $order_by=null, $offset=null)

Find all instances of a model.

`$assets = Asset::all();`

## {EntityName}::create()

Accepts an array of key=>values. `

``` `

$user = People::create([ 'email_address' => 'dev@cashmusic.org', 'username' => "example", 'password'=> "covfefe" ]);
```

## {EntityName}::findWhere($array, $limit=null, $order_by=null, $offset=null)

Find multiple results by multiple fields. Accepts an array of key=>values.

```
$user = People::findWhere([
    'email_address' => "dev@cashmusic.org",
    'creation_date' => "1494279214"
    ]);
```

## {EntityName}::delete($id)

Delete a model by unique id.

`Asset::delete(5);`

## $entity->save()

Save and persist changes to a loaded entity to the database.

```
$asset = Asset::find(5);

$asset->property = "foo";
$asset->save();
```

# Relationships

Doctrine ORM was a jerk with having Entity classes extend a base class, so we made our own sort of rudimentary relationships, kind of based off of Eloquent's approach. It's for very basic relationship queries without any filtering, at the moment.

You create a rough relationship by creating a new public method and calling the `hasOne`, `hasMany`, or `belongsTo` methods inside.

```
public function lists($conditions=false) {
    // target entity name, current entity primary key, foreign key
    return $this->hasMany("PeopleList", "id", "user_id");
}
```

Then you can get the related objects when you call a related entity:

```
$user = People::find(1);

foreach($user->lists() as $list) {
  echo $list->name;
}
```

The returned result is an array of Entity objects, so you can modify objects that are returned like you would above.
