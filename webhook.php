<?php

ini_set( 'display_errors', '0' );
error_reporting( E_ALL );

require 'config.php';
require 'TelegramBotApi.php';

$api = new TelegramBotApi( TOKEN ); 

$action = $_REQUEST['action'] ?? '';

header( 'Content-type: application/json' );

if ( $action == 'setWebhook' || $action == 'getWebhook' )
{
    $webhookInfo = $api->getWebhookInfo();

    if ( $action == 'getWebhook' || isset( $webhookInfo['error_code'] ) )
    {
        die( json_encode( $webhookInfo ) );
    }

	$url = $webhookInfo['result']['url'] ?? '';
	
    if ( $url <> WEBHOOK )
    {
        $result = $api->setWebhook([ 
			'url' => WEBHOOK,
			'allowed_updates' => json_encode([ 
				'message',
				//'chat_join_request',
				//'chat_member',
				'callback_query',
				//'my_chat_member'
			]),
			'secret_token' => SECRET
		]);

		die( json_encode( $result ) );
    }

    die( json_encode( [ 'ok' => true, 'result' => true, 'description' => 'Webhook update is not required!' ] ) );
}
else if ( $action == 'update' )
{
	// get_headers()['X-Telegram-Bot-Api-Secret-Token'] 
	$secret = $_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'] ?? '';
	
	$update = json_decode( file_get_contents( 'php://input' ), true );
	
	$chatId = $update['message']['chat']['id']
		?? $update['message']['from']['id'] 
		?? $update['callback_query']['from']['id']
		?? '';
		
	if ( $secret <> SECRET || $chatId <> USER_ID )
	{
		$api->sendMessage([ 
			'chat_id' => USER_ID, 
			'text' => "<b>WARNING</b>: Unauthorized access!\n\n<b>Update</b>: " . print_r( $update, true ), 
			'parse_mode' => 'html'
		]);
		
		die( json_encode( [ 'ok' => false, 'result' => false, 'description' => 'Unauthorized access!' ] ) );
	}
	
	$messageId = $update['message']['message_id'] 
		?? $update['callback_query']['message']['message_id'] 
		?? '';
	
	$messageText = $update['message']['text'] 
		?? $update['callback_query']['data'] 
		?? '';

	$command = explode( ' ', $messageText );

	if ( $command[0] == '/start' )
	{
		$api->sendMessage([ 
			'chat_id' => USER_ID, 
			'text' => '/help - Command list', 
			'parse_mode' => 'html'
		]);
	}
	else if ( $command[0] == '/help' )
	{
		$text = '<b>Сommand list</b>';
		$text .= "\n\n/add_item {char name} {item id} {item count}";
		
		$api->sendMessage([ 
			'chat_id' => USER_ID, 
			'text' => $text,
			'parse_mode' => 'html'
		]);
	}
	else if ( in_array( $command[0], [ '/add_item' ] ) ) 
	{
		require 'db.class.php';
		
		try
		{
			$db = new DB( $config );
			
			if ( $command[0] == '/add_item' )
			{
				$result = 'Syntax error!';
				
				if ( count( $command ) == 4 )
				{
					if ( $char = $db->fetch( "SELECT obj_Id FROM characters WHERE char_name = ?", [ $command[1] ] ) )
					{
						$sql = "INSERT INTO `items_delayed` ( `owner_id`, `item_id`, `count`, `payment_status`, `description` ) VALUES ( ?, ?, ?, 0, 'Telegram Bot' )";
						
						if ( $db->prepareAndExecute( $sql, [ $char['obj_Id'], $command[2], $command[3] ] )->rowCount() )
						{
							$result = 'Successfully!';
						}
					}
					else
					{
						$result = "Character {$command[1]} not found!";
					}
				}
				
				$api->sendMessage([ 
					'chat_id' => USER_ID, 
					'text' => $result, 
					'parse_mode' => 'html'
				]);
			}
		}
		catch ( \PDOException $e )
		{
			$api->sendMessage([ 
				'chat_id' => USER_ID, 
				'text' => 'Exception: ' . $e->getMessage(), 
				'parse_mode' => 'html'
			]);
		}
	}
	/*else
	{
		$api->sendMessage([ 
			'chat_id' => USER_ID, 
			'text' => "<b>DEBUG</b>: Unauthorized command!\n\n<b>Update</b>: " . print_r( $update, true ), 
			'parse_mode' => 'html'
		]);
	}*/
}

echo json_encode( [] );

?>