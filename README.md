# KVSQLite3 - Simple Key Value Store in SQLite3

Nothing Relational to Relational Databases.

```php
<?php declare(strict_types=1);

require_once(__DIR__ . '/../vendor/autoload.php');

try {
    // Create storage.
    $db = new \KEINOS\KVSQLite3('sample.db');

    // Set a values.
    $db->set('foo', 'bar');
    $db->set('baz', ['qux', 'quux']);

    // Get value.
    echo $db->get('foo'), PHP_EOL;

    // Get a value with default return value.
    echo $db->get('hoge', '"hoge" does not exist.'), PHP_EOL;

    // Delete a value.
    $db->delete('foo');

    // Dump data as SQL queries to a file.
    $db->dump('sample.sql');
} catch (\RuntimeException $e) {
    $msg = 'ERROR:' . $e->getMessage() . PHP_EOL;
    echo $msg;
}

```

```php
<?php declare(strict_types=1);

require_once(__DIR__ . '/../vendor/autoload.php');

$db = new \KEINOS\KVSQLite3('sample.db');

// Set values
$data = [
  'foo'    => 'bar',
  'baz'    => 'qux',
  'quux'   => 'corge',
  'grault' => 'garply',
];
$db->setMultiple($data);

// Get values
$keys = [
  'foo',
  'baz',
];
$result = $db->getMultiple($data);
print_r($result);

// Delete values
$keys = [
  'quux',
  'grault',
];
$db->deleteMultiple($keys);

// Is the key set?
echo $db->has('baz') ? 'baz is set' : 'baz not set', PHP_EOL;

// Clear data
$db->clear();
```

## Methods

- Basic
  - `set($key, $value, [$ttl])`: Stores the value with the given key. If the key already exists then the value will be overwritten. If the `$ttl` is set, then the data will be expired after the given seconds.
  - `get($key)`: Returns the stored value of the given key.
  - `delete($key)`: Deletes the stored value of the given key.
  - `dump($name_file)`: Dumps the stored data in SQL query.
- Advanced
  - `clear()`: Deletes all the data stored.
  - `has($key)`: Determines whether an item is present in the storage.
  - `setMultiple($data)`: Stores multiple values at once.
  - `getMultiple($keys)`: Returns the stored values of the given keys.
  - `deleteMultiple($keys)`: Deletes the stored values of the given keys.

## Note

We DO NOT recommend to store objects.
