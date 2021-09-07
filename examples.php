<?php

require_once('sdk.php');

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
