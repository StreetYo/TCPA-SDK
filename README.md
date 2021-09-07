# Make it before use

Login to your account. Generate your API keys 
[here](https://tcpalitigatorlist.com/tcpa-litigator-list-api/)
if you don't have them yet.
# Example

Set your API login and password.
You can run this code with the GET phone or phones parameters and get their scrub statuses.   

```php
if ( isset($_GET['phone']) ){
	print_r(tcpa_scrub_single_number($_GET['phone']));
}

if ( isset($_GET['phones']) ){
	print_r(tcpa_mass_scrub(explode(',', $_GET['phones'])));
}

function tcpa_scrub_single_number($phone_number, $fields = []){
	$api_config = [
		'api_username' => 'YOUR API LOGIN',
		'api_password' => 'YOUR API PASSWORD',
		'response_format' => 'json',
		'response_type' => isset($_GET['boolean']) ? 'boolean' : 'regular',
		'check_types' => isset($_GET['type']) ? [$_GET['type']] : ['all'],
	];
    $api = new TCPA_API($api_config);
    return $api->request_single_number($phone_number, $fields);
}

function tcpa_mass_scrub($phone_numbers, $fields = []) {
    $api_config = [
        'api_username' => 'YOUR API LOGIN',
        'api_password' => 'YOUR API PASSWORD',
        'check_types' => isset($_GET['type']) ? [$_GET['type']] : ['all'],
    ];
    $api = new TCPA_API($api_config);
    return $api->mass_scrub($phone_numbers, $fields);
}
```
