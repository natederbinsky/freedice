<?php

	require 'common/private/lib/start.inc.php';
	$page_info['title'] = 'Start Game';
	
	if ( $user_info['id'] == -1 )
	{
		echo auth_form();
	}
	else
	{
		$game_id = intval( get_param('game_id') );
		$status = get_codes('status');
		
		
		$game_name = NULL;
		$op_result = NULL;
		$good_result = false;
		
		$sql = 'SELECT game_status, game_name, game_admin, game_emails, dice_start FROM games WHERE game_status=' . $status['Awaiting Players'] . ' AND game_id=' . $game_id;
		$result = @mysql_query( $sql, $db );
		if ( $result && ( mysql_num_rows( $result ) == 1 ) )
		{
			$row = mysql_fetch_assoc( $result );
			
			$game_name = $row['game_name'];
			
			if ( intval( $row['game_admin'] ) != $user_info['id'] )
			{
				$op_result = 'You cannot start this game.';
			}
			else
			{
				$players_sql = 'SELECT gp.player_id, p.player_email FROM game_players gp INNER JOIN players p ON gp.player_id=p.player_id WHERE gp.game_id=' . $game_id;
				$players_result = @mysql_query( $players_sql, $db );
				if ( mysql_num_rows( $players_result ) < 2 )
				{
					$op_result = 'You need at least 2 players to start a game.';
				}
				else
				{
					$orders = array();
					while ( $p_row = mysql_fetch_assoc( $players_result ) )
					{
						$roll = -1;
						
						do
						{
							$roll = mt_rand();
						} while ( isset( $orders[ $roll ] ) );
						$orders[ $roll ] = intval( $p_row['player_id'] );
						
						if ( $row['game_emails'] == 'Y' )			
						{
							mail( $p_row['player_email'], ( 'Dice Game (' . $row['game_name'] . '): Start!' ), 'Start dicing!' );						
						}
					}
					
					ksort( $orders );
					$counter = 1;
					foreach ( $orders as $order )
					{
						$cup = implode( '', roll_dice( intval( $row['dice_start'] ) ) );
						
						$cup_sql = 'UPDATE game_players SET cup=' . quote_smart( $cup, $db ) . ', play_order=' . ( $counter++ ) . ' WHERE game_id=' . $game_id . ' AND player_id=' . $order;
						$cup_result = @mysql_query( $cup_sql, $db );
					}
					
					$start_sql = 'UPDATE games SET game_status=' . $status['In Progress'] . ' WHERE game_id=' . $game_id;
					$start_result = @mysql_query( $start_sql, $db );
					
					$start_sql = 'INSERT INTO rounds (game_id) VALUES (' . $game_id . ')';
					$start_result = @mysql_query( $start_sql, $db );
					
					$good_result = true;
					
					$op_result = 'Game started.';
				}
			}
		}
		else
		{
			$op_result = 'Cannot find game.';
		}
		
		if ( $page_info['type'] != 'blank' )
		{
			echo '<div class="section">';
			echo '<div class="body">';
			
			if ( !is_null( $game_name ) )
			{
				echo '<h2>' . htmlentities( $game_name ) . '</h2>';
			}
			
			if ( $page_info['type'] == 'iphone' )
			{
				echo ( '<ul><li>' . $op_result . '</li></ul>' );
			}
			else
			{
				echo ( '<p class="ui-state-' . (($good_result)?('highlight'):('error')) . '">' . $op_result . '</p>' );
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
			$temp[] = ( 'page="action_start_game"' );
			
			if ( $good_result )
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
