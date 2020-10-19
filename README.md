# YACache: Yet Another Cache

A minimal PSR16 compatible cache library for PHP 7.4+
 
The goals of this project are  
  
- Minimalism: as little code as possible, only the barest necessary features.
- Performance: Strips out all but essential features in the main execution flow so it runs really fast. 
- (almost) Standards Compliance: [PSR-16](https://www.php-fig.org/psr/psr-16/) does not have strict typing (yet!?). 
  The `CacheInterface` is duck-type compatible with PSR-16
- adds some tiny and optional utility functions to ease API development eg `increment()` for rate limiters,
  `remember()` to remove boilerplate lines of code  
- Quality: 100% unit test coverage, phpstan max strict, strict_types=1 

Why another cache when there are already so many that are very good?  

- https://packagist.org/packages/desarrolla2/cache is lean and clean but has extra tools for Packing
- many other caches (eg https://github.com/symfony/cache) work with [PSR-6](https://www.php-fig.org/psr/psr-6/) (which is very heavy) 
  and then add adapters / wrappers for PSR-16 on top
- ... or their code has stampede protection or other clever but complicated things which are awesome for bigger projects 
  (with more traffic) but way overkill for small stuff.  

Pull requests welcome, but bear in mind the above project goals. If you have more complex needs, the other
(better written, better supported, more mature) projects mentioned above will be a better choice for you.

# Installation

```
composer require LSS\YACache
```

# How to use

```php
use LSS\YACache\RedisCache;

$redis = new \Redis('127.0.0.1');
$cache = new RedisCache($redis);
$value = $cache->remember('another key', 100, function () use ($database) { return $database->someExpensiveQueryResult(); });
```

Browse the `/src` directory for more drivers