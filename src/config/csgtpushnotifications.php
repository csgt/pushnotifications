<?php
return [
	/*
	|--------------------------------------------------------------------------
	| iOS
	|--------------------------------------------------------------------------
	|
	| Gateway, certificado y credenciales para push notifications
	|
	*/

  'ios' => [
		'environment'             => env('NOTIFICATIONS_DEBUG', 'development'), // development, production
		'port'                    => env('NOTIFICATIONS_PORT', 2195),
		'certificate'							=> env('NOTIFICATIONS_CERTIFICATE', app_path() . '/Certificates/apns-dev-cert.pem'),
		'certificatepassword'     => env('NOTIFICATIONS_CERTIFICATE_PASSWORD', ''),
  ],
];