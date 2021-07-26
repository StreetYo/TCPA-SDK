# Make it before use

Login to your account. Generate your API keys 
[here](https://tcpalitigatorlist.com/tcpa-litigator-list-api/)
if you don't have them yet. After that, put your API credentials in the following fields:

```php
private $api_username = '';
private $api_password = '';
```

# Examples

Single number scrub

```php
tcpa_scrub_single_number('20123456789');
```

Scrub an array of phone numbers

```php
tcpa_mass_scrub(['20123456789','20123456710']);
```

Scrub TCPA only

```php
tcpa_scrub_single_number('20123456789', [
    'types' => [TCPA_API::TCPA_TYPE]
]);
```

or if you scrub an array

```php
tcpa_mass_scrub(['20123456789','20123456710'], [
    'types' => [TCPA_API::TCPA_TYPE]
]);
```
