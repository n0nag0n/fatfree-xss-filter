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

use n0nag0n\Xss_Filter;

// Filter the POST globals on the hive of a FatFree object
$post = Xss_Filter::filter('POST');

// Filter whatever you'd like
$input_from_form = Xss_Filter::filterScalar($input_from_form_raw);
```

# Thanks
Originally created by Dabcorp and put on github with permission.