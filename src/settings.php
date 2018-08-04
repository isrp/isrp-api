<?php
return [
	'settings' => [
		'displayErrorDetails' => true, // set to false in production
		'addContentLengthHeader' => false, // Allow the web server to send the content-length header Renderer settings
		'renderer' => [
			'template_path' => __DIR__ . '/../templates/'
		],
		// Monolog settings
		'logger' => [
			'name' => 'isrp-api',
			'type' => 'syslog'
		],
		'dragon-club-url' => getenv('DRAGON_CLUB_URL'),
	]
];
