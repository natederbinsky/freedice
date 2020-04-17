<?php

	require 'common/private/lib/start.inc.php';
	$page_info['title'] = 'Join a Game';
	
	if ( $user_info['id'] == -1 )
	{
		echo auth_form();
	}
	else
	{
		$game_id = intval( get_param('game_id') );
		$game_pw = get_param('game_pw');
		$color = intval( get_param('my_color') );
		
		$colors = get_codes('color');
		$status = get_codes('status');
		
		$good = false;
		
		$join_result = NULL;
		
		// check valid game
		$sql = 'SELECT game_pw, game_status FROM games WHERE game_id=' . $game_id;
		$result = @mysqli_query( $db, $sql );
		if ( $result && ( mysqli_num_rows( $result ) == 1 ) )
		{
			$row = mysqli_fetch_assoc( $result );
			$good = true;
			
			// check pw of game
			if ( !empty( $row['game_pw'] ) )
			{
				if ( md5( $game_pw ) != $row['game_pw'] )
				{
					$good = false;
				}
			}
			
			if ( $good )
			{
				// check status of game
				if ( $status[ intval( $row['game_status'] ) ] != 'Awaiting Players' )
				{
					$good = false;
				}
					
				if ( $good )
				{
					// check if i'm in game
					// check available game color
					
					$sql = 'SELECT player_id, dice_color FROM game_players WHERE game_id=' . $game_id;
					$result = @mysqli_query( $db, $sql );
					while ( $row = mysqli_fetch_assoc( $result ) )
					{
						if ( ( intval( $row['player_id'] ) == $user_info['id'] ) || ( intval( $row['dice_color'] ) == $color ) )
						{
							$good = false;
						}
					}
					
					if ( !isset( $colors[ $color ] ) )
					{
						$good = false;
					}
				}
			}
		}
		
		if ( !$good )
		{
			$join_result = 'Cannot join game.';
		}
		else
		{
			$player_sql = 'INSERT INTO game_players (game_id, player_id, dice_color, cup, shown, play_order, exact_used) VALUES (' . $game_id . ', ' . $user_info['id'] . ', ' . $color . ', ' . quote_smart( '', $db ) . ', ' . quote_smart( '', $db ) . ', ' . ( -roll_dice() ) . ', ' . quote_smart( 'N', $db ) . ')';
			$player_result = @mysqli_query( $db, $player_sql );
			
			$join_result = 'Joined game.';
		}
		
		if ( $page_info['type'] != 'blank' )
		{
			echo '<div class="section">';
			echo '<div class="body">';
			
			if ( $page_info['type'] == 'iphone' )
			{
				echo '<ul>';
				echo '<li>' . $join_result . '</li>';
				echo '</ul>';
			}
			else
			{
				echo ( '<p class="ui-state-' . (($good)?('highlight'):('error')) . '">' . $join_result . '</p>' );
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
			$temp[] = ( 'page="action_join_game"' );
			
			if ( $good )
			{
				$temp[] = ( 'action="true"' );
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
