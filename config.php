<?php

// token bot
define( 'TOKEN', 'xxxxxxxxxxxxxxxxxxxx' );

// Webhook URL
define( 'WEBHOOK', 'https://domain.com/L2TelegramBot/webhook.php?action=update' );

// telegram account id 
define( 'USER_ID', '123123123' );

// secret key that will be sent in the X-Telegram-Bot-Api-Secret-Token header from the Telegram servers
define( 'SECRET', '1234567' );

$config = [
	'driver'	=> 'mysql',
	'dbhost'	=> 'localhost',
	'dbuser'	=> 'root',
	'dbpass'	=> 'root',
	'dbname'	=> 'l2jdb',
	'dbport'	=> 3306,
	'charset'	=> 'utf8'
];

?>