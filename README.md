# fatfree-xss-filter
XSS Filter to properly clean your request data from XSS related attacks.

# Install
Use composer to get this guy up and running
```
composer require n0nag0n/fatfree-xss-filter
```

# Usage
Pretty simple to use really
```php
<?php
// public/index.php for example. Wherever your framework entrypoint is.

use n0nag0n\Xss_Filter;

$f3 = Base::instance();

// Filter the POST globals on the hive of a FatFree object
$post = Xss_Filter::filter('POST');

// define routes, services, etc.

$f3->run();


// Additionally, filter whatever you'd like in controllers and such.
// $input_from_form = Xss_Filter::filterScalar($input_from_form_raw);
```

# Thanks
Originally created by @dabcorp and put on github with permission.
