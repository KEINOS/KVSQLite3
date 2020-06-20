<?php declare(strict_types=1);
/**
 *  PSR-16 defined Simple Cache Interface.
 * ============================================================================
 *
 * - @refs https://www.php-fig.org/psr/psr-16/
 * - Method names should be in lowerCamelCase.
 * - Place the methods in alphabetical order.
 */

namespace Psr\SimpleCache;

interface CacheInterface
{
    /**
     * Wipes clean the entire cache's keys.
     *
     * @return bool True on success and false on failure.
     */
    public function clear();

    /**
     * Delete an item from the cache by its unique key.
     *
     * @param string $key The unique cache key of the item to delete.
     *
     * @return bool True if the item was successfully removed. False if there was an error.
     *
     * @throws \InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function delete(string $key);

    /**
     * Deletes multiple cache items in a single operation.
     *
     * @param iterable<string,mixed> $keys A list of string-based keys to be deleted.
     *
     * @return bool True if the items were successfully removed. False if there was an error.
     *
     * @throws \InvalidArgumentException
     *   MUST be thrown if $keys is neither an array nor a Traversable,
     *   or if any of the $keys are not a legal value.
     */
    public function deleteMultiple(iterable $keys);

    /**
     * Fetches a value from the cache.
     *
     * @param string $key      The unique key of this item in the cache.
     * @param mixed  $default  Default value to return if the key does not exist. (Optional)
     *                         If this value is not set then \RuntimeException will be thrown
     *                         if the key does not exist.
     *
     * @return mixed The value of the item from the cache, or $default in case of cache miss.
     *
     * @throws \InvalidArgumentException MUST be thrown if the $key string is not a legal value.
     * @throws \RuntimeException         Throws if the key does not exist and $default parameter
     *                                   is not set.
     */
    public function get(string $key, $default = null);

    /**
     * Obtains multiple cache items by their unique keys.
     *
     * @param iterable<string,mixed> $keys     A list of keys that can obtained in a single operation.
     * @param mixed                  $default  Default value to return for keys that do not exist.
     *
     * @return iterable<string,mixed> A list of key => value pairs. Cache keys that do not exist or
     *                                are stale will have $default as value.
     *
     * @throws \InvalidArgumentException
     *   MUST be thrown if $keys is neither an array nor a Traversable,
     *   or if any of the $keys are not a legal value.
     */
    public function getMultiple(iterable $keys, $default = null);

    /**
     * Determines whether an item is present in the cache.
     *
     * NOTE: It is recommended that has() is only to be used for cache warming type purposes
     * and not to be used within your live applications operations for get/set, as this method
     * is subject to a race condition where your has() will return true and immediately after,
     * another script can remove it, making the state of your app out of date.
     *
     * @param string $key The cache item key.
     *
     * @return bool
     *
     * @throws \InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function has(string $key);

    /**
     * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
     *
     * @param string                 $key   The key of the item to store.
     * @param mixed                  $value The value of the item to store. Must be serializable.
     * @param null|int|\DateInterval $ttl   Optional. The TTL value of this item in seconds. If no value
     *                                      is sent then the driver will treat the cache as "no-expiration-
     *                                      time-specified" which is 0. If an integer or \DateInterval
     *                                      object is sent, then the driver will set the expiration time
     *                                      as "current UNIX timestamp + ttl(seconds)" in "expire" column
     *                                      of the database.
     *                                      Note that seconds will be treated as absolute value, which
     *                                      means that sending a negative value such as "-3" will be added
     *                                      to the current timestamp as "3".
     *
     * @return bool True on success and false on failure.
     *
     * @throws \InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function set(string $key, $value, $ttl = null);

    /**
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     *
     * @param iterable<string,mixed> $values  A list of key => value pairs for a multiple-set operation.
     * @param null|int|\DateInterval $ttl     Optional. The TTL value of this item. If no value is sent and
     *                                        the driver supports TTL then the library may set a default value
     *                                        for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     * @throws \InvalidArgumentException
     *   MUST be thrown if $values is neither an array nor a Traversable,
     *   or if any of the $values are not a legal value.
     */
    public function setMultiple(iterable $values, $ttl = null);
}
