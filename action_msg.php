<?php

	require 'common/private/lib/start.inc.php';
	$page_info['title'] = 'Send a Message';
	
	if ( $user_info['id'] == -1 )
	{
		echo auth_form();
	}
	else
	{
		$status = get_codes('status');
		$actions = get_codes('action');
		
		echo '<div class="section">';
		echo '<div class="body">';
		
		$game = get_game( intval( get_param('game_id') ) );
		if ( is_null( $game ) || ( $status[ $game['game_status'] ] != 'In Progress' ) )
		{
			$response = 'Invalid game.';
			if ( $page_info['type'] != 'iphone' )
			{
				echo '<p class="ui-state-error">' . htmlentities( $response ) . '</p>';
			}
			else
			{
				echo '<ul>';
					echo '<li>' . htmlentities( $response ) . '</li>';
				echo '</ul>';
			}
		}
		else
		{
			$players = get_players( $game['game_id'] );
			$logs = get_round_logs( $game['game_round'] );
			$elaborations = elaborate_state( $logs, $players, $actions, $game );
			
			if ( !is_null( $elaborations['me'] ) )
			{
				$msg = get_param('msg');
				if ( strlen( $msg ) )
				{
					$sql = 'INSERT INTO msgs (game_id, player_id, msg) VALUES (' . $game['game_id'] . ',' . $user_info['id'] . ',' . quote_smart( $msg, $db ) . ')';
					$result = @mysqli_query( $db, $sql );
					
					if ( $game['game_emails'] )
					{ 
						foreach ( $players as $player )
						{ 
							if ( count( $player['cup'] ) || count( $player['shown'] ) )
							{
								mail( $player['player_email'], ( 'Dice Game (' . $game['game_name'] . '): Message Sent' ), ( 'Author: ' . $elaborations['me']['player_name'] . "\n" . $msg ) );
							}
						}
					}
					
					$response = 'Message sent';
					if ( $page_info['type'] != 'iphone' )
					{
						echo '<p class="ui-state-highlight">' . htmlentities( $response ) . '</p>';
					}
					else
					{
						echo '<ul>';
							echo '<li>' . htmlentities( $response ) . '</li>';
						echo '</ul>';
					}
				}	
				else
				{
					$response = 'Seriously?  No message?';
					if ( $page_info['type'] != 'iphone' )
					{
						echo '<p class="ui-state-error">' . htmlentities( $response ) . '</p>';
					}
					else
					{
						echo '<ul>';
							echo '<li>' . htmlentities( $response ) . '</li>';
						echo '</ul>';
					}
				}
			}
			else
			{
				$response = 'You are not allowed to send messages!';
				if ( $page_info['type'] != 'iphone' )
				{
					echo '<p class="ui-state-error">' . htmlentities( $response ) . '</p>';
				}
				else
				{
					echo '<ul>';
						echo '<li>' . htmlentities( $response ) . '</li>';
					echo '</ul>';
				}
			}
		}
		
		echo '</div>';
		echo '</div>';	
		
		echo '<div class="section">';
		echo '<div class="body">';
		echo '<input id="send-back" type="button" value="back to the game" onclick="location.href=\'play.php?game_id=' . htmlentities( $game['game_id'] ) . '\';" />';
		echo jquery_button('send-back');
		echo '</div>';
		echo '</div>';
	}
	
	require 'common/private/lib/end.inc.php';
	
?>
