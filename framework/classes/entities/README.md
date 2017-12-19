# CASH Platform ORM basics

The idea here is to make data more interactive and accessible. ORM works on a very basic level, with some added shortcuts to the Doctrine/ORM package.

All Entity (model) classes extend EntityBase which gives us some shortcuts to do common operations. At the time of this writing, more complex queries will be done through the `usmanhalalit/pixie` query builder project, due to it being easier to approach than Doctrine's built in query builder. This may change in future if we can simplify the approach to Doctrine's features.

EntityBase uses magic getter and setter methods so that we can get properties with the normal `$object->property` approach, while making it easy to add custom getters and setters with the `get{foo}Attribute` and `set{foo}Attribute` methods.

So, for example if you wanted to override the `$user->password` setter property you could add this method to the People entity class at `./framework/classes/entities/People.php`:

```
public function setPasswordAttribute($value) {
     $this->password = md5($value);
 }
```

## $fillable

Every entity class should have a protected property `$fillable`, which the magic setter method checks against. The magic setter property will drop any fields not in `$fillable`, and will not try to write them in any sort of set operation. This is both to filter out garbage but also to give control over whether or not folks can write over special database fields like `creation_date`.

# ORM wrapper methods
The ORM can be accessed via the `$this->orm` object, that's available in any class that extends directly or indirectly from the `CASHDATA` class.

## $this->orm($entity, $id)

Find a model by unique id.

`$asset = $this->orm->find(Asset::class, 5 );`

## $this->orm->all($entity, $limit=null, $order_by=null, $offset=null)

Find all instances of a model.

`$assets = $this->orm->all(Asset::class);`

## $this->orm->create($entity, $values)

Accepts an array of key=>values. Returns the created entity.

```
$user = $this->orm->create(People::class, [
    'email_address' => 'dev@cashmusic.org', 
    'username' => "example", 
    'password'=> "covfefe" 
    ]);
```

## $this->orm->findWhere($entity, $values, $force_array=false, $order_by=null, $limit=null, $offset=null)

Find multiple results by multiple fields. Accepts an array of key=>values. `$force_array` will force the results to always be contained in an array. Otherwise if it finds a single result, it will be an Entity object.

```
$user = $this->orm->findWhere(People::class, [
    'email_address' => "dev@cashmusic.org",
    'creation_date' => "1494279214"
    ]);
```

## $this->orm->delete($entity, $values)

Delete a model by a key=>value set of values.

`$this->orm->delete(Asset::class, ['id'=>15]);`

#Loaded entities
There are various methods available for working with loaded entities.
## $entity->save()

Save and persist changes to a loaded entity to the database.

```
$asset = $this->orm->find(Asset::class, 5 );

$asset->property = "foo";
$asset->save();
```

## $entity->update()

Accepts an array of key=>values. Returns the updated entity.

```

$user->update([
    'name' => 'covfefe',
    'email_address' => "dev@cashmusic.org"
  ]);
```

# Relationships

Doctrine ORM was a jerk with having Entity classes extend a base class, so we made our own sort of rudimentary relationships, kind of based off of Eloquent's approach. It's for very basic relationship queries without any filtering, at the moment. Since we've changed the initial structure of our ORM we will eventually be moving to Doctrine's more performant relationships, but for now this is how we're rolling.

You create a rough relationship by creating a new public method on the Entity class and calling the `hasOne`, `hasMany`, or `belongsTo` methods inside.

```
public function assets($where=false, $limit=false, $order_by=false) {
        return $this->hasMany("Asset", "id", "user_id", $where, $limit, $order_by);
}
```

Then you can get the related objects when you call a related entity:

```
$user = $this->orm->find(People::class, 1 );

foreach($user->assets() as $asset) {
  var_dump($asset->metadata);
}
```

The returned result is an array of Entity objects, so you can modify objects that are returned like you would above.

# Misc tools

You can easily cast a loaded entity object's properties to an array (`$object->toArray()`) or JSON (`$object->toJson()`).

# Pixie Query Builder

If you need to do something more complex, you can get a direct QueryBuilder instance via `$this->db`. [Read more on the usmanhalalit/pixie.](https://github.com/usmanhalalit/pixie)
