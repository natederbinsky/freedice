<?php

	require 'common/private/lib/start.inc.php';
	$page_info['title'] = 'Create a New Game';
	
	if ( $user_info['id'] == -1 )
	{
		echo auth_form();
	}
	else
	{
		$name = get_param('game_name');
		$pw = get_param('game_pw');
		$dice = intval( get_param('dice_start') );
		$color = intval( get_param('my_color') );
		$emails = ( get_param('game_emails') == 'Y' );
		
		$colors = get_codes('color');	
		
		$result = NULL;		
		$good_result = false;
		$my_id = NULL;
		if ( ( empty( $name ) ) || ( $dice < 2 ) || ( !isset( $colors[ $color ] ) ) )
		{
			$result = 'Invalid game.';
		}
		else
		{
			$status = get_codes('status');
			
			$game_sql = 'INSERT INTO games (game_name, game_pw, game_status, dice_start, game_admin, game_emails) VALUES (' . quote_smart( $name, $db ) . ', ' . quote_smart( ( ( empty( $pw ) )?(''):( md5( $pw ) ) ), $db ) . ',' . $status['Awaiting Players'] . ',' . $dice . ',' . $user_info['id'] . ',' . quote_smart( ( ( $emails )?('Y'):('N') ), $db ) . ')';
			$game_result = @mysqli_query( $db, $game_sql );
			$my_id = mysqli_insert_id( $db );
			
			$player_sql = 'INSERT INTO game_players (game_id, player_id, dice_color, cup, shown, play_order, exact_used) VALUES (' . $my_id . ', ' . $user_info['id'] . ', ' . $color . ', ' . quote_smart( '', $db ) . ', ' . quote_smart( '', $db ) . ', ' . ( -roll_dice() ) . ', ' . quote_smart( 'N', $db ) . ')';
			$player_result = @mysqli_query( $db, $player_sql );
			
			if ( empty( $pw ) )
			{
				$email_sql = 'SELECT player_email FROM players';
				$email_result = @mysqli_query( $db, $email_sql );
				while ( $row = mysqli_fetch_assoc( $email_result ) )
				{
					mail( $row['player_email'], ( 'Dice Game (' . $name . '): Just Created!' ), 'Come join in!' );
				}
			}
			
			$result = 'Game added.';
			$good_result = true;
		}
		
		if ( $page_info['type'] != 'blank' )
		{
			echo '<div class="section">';
			echo '<div class="body">';
			
			if ( $page_info['type'] == 'iphone' )
			{
				echo '<ul>';
					echo '<li>' . $result . '</li>';
				echo '</ul>';
			}
			else
			{
				echo ( '<p class="ui-state-' . (($good_result)?('highlight'):('error')) . '">' . $result . '</p>' );
			}
			
			echo '</div>';
			echo '</div>';	
			
			echo '<div class="section">';
			echo '<div class="body">';
			echo '<input id="send-home" type="button" value="home" onclick="location.href=\'index.php\';" />';
			echo jquery_button('send-home');
			echo '</div>';
			echo '</div>';
		}
		else
		{
			$temp = array();
			
			$temp[] = ( '[content]' );
			$temp[] = ( 'page="action_new_game"' );
			
			if ( $good_result )
			{
				$temp[] = ( 'action="true"' );
				$temp[] = ( 'game_id="' . $my_id . '"' );
			}
			else
			{
				$temp[] = ( 'action="false"' );
			}
			
			echo implode( "\n", $temp );
		}
	}
	
	require 'common/private/lib/end.inc.php';
	
?>
