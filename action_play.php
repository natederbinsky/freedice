<?php

	require 'common/private/lib/start.inc.php';
	//$page_info['title'] = 'Play the DICE Game';
	
	function _game_logic( $game_id, &$game_over, $output_type, $action, $num, $face, $target, $pushes )
	{		
		global $db;
		
		$status = get_codes('status');
		$actions = get_codes('action');	
		
		$notification = null;
		
		$game = get_game( $game_id );
		if ( is_null( $game ) || ( $status[ $game['game_status'] ] != 'In Progress' ) )
		{
			$response = 'Invalid game.';
			if ( $output_type == 'full' )
			{
				echo '<p class="ui-state-error">' . htmlentities( $response ) . '</p>';
			}
			else if ( $output_type == 'iphone' )
			{
				echo '<ul>';
				echo '<li>' . htmlentities( $response ) . '</li>';
				echo '</ul>';
			}
			else if ( $output_type == 'blank' )
			{
				$return_val = '';
				
				$temp = array();
				$temp[] = ( '[content]' );
				$temp[] = ( 'page="action_play"' );
				$temp[] = ( 'game="false"' );
				
				$return_val = implode( "\n", $temp );
				
				echo $return_val;
			}
		}
		else
		{
			$players = get_players( $game['game_id'] );
			$logs = get_round_logs( $game['game_round'] );
			$elaborations = elaborate_state( $logs, $players, $actions, $game );
			
			if ( !is_null( $elaborations['me'] ) )
			{
				$notification = NULL;
				$mail_me = true;
				
				if ( isset( $actions[ $action ] ) && isset( $elaborations['avail_actions'][ $actions[ $action ] ] ) )
				{
					if ( $actions[ $action ] == 'bid' )
					{
						$good = false;						
						
						// good bid: num>0, 0<face<7						
						if ( ( $num > 0 ) && ( $face > 0 ) && ( $face < 7 ) )
						{
							if ( $game['special_rules'] )
							{
								// - anything if no prior
								// - if one die, anything bigger
								//  - else bigger, same face
								
								if ( is_null( $elaborations['bid'] ) )
								{
									$good = true;
								}
								else
								{
									if ( ( count( $elaborations['me']['cup'] ) + count( $elaborations['me']['shown'] ) ) == 1 )
									{
										if ( $num == $elaborations['bid'][0] )
										{
											$good = ( $face > $elaborations['bid'][1] );
										}
										else if ( $num > $elaborations['bid'][0] )
										{
											$good = true;
										}
									}
									else
									{
										$good = ( ( $face == $elaborations['bid'][1] ) && ( $num > $elaborations['bid'][0] ) );
									}
								}
							}
							else
							{								
								// - anything if no prior bid
								// - if same num, greater face
								// - if greater num, any face
								// - if face=1, >= ceiling(num/2)
								
								if ( is_null( $elaborations['bid'] ) )
								{
									$good = true;
								}
								else
								{
									if ( $face == 1 )
									{
										if ( $elaborations['bid'][1] == 1 )
											$good = ( $num > $elaborations['bid'][0] ); 
										else 
											$good = ( $num >= ceil( $elaborations['bid'][0] / 2 ) );
									}
									else
									{										
										if ( $elaborations['bid'][1] == 1 )
											$good = ( $num >= ( 2 * $elaborations['bid'][0] + 1 ) );
										else if ( $num == $elaborations['bid'][0] )
											$good = ( $face > $elaborations['bid'][1] );
										else
											$good = ( $num > $elaborations['bid'][0] );
									}
								}
							} 
						}
						
						if ( $good )
						{							
							$sql = 'INSERT INTO action_logs (round_id, player_id, action_id, value) VALUES (' . $game['game_round'] . ',' . $elaborations['me']['player_id'] . ',' . $actions['bid'] . ',' . quote_smart( ( $num . ',' . $face ), $db ) . ')';
							$result = @mysql_query( $sql, $db );
							
							$notification = ( $elaborations['me']['player_name'] . ' bid!' );
						}
						else
						{
							$response = 'Bad bid!';
							if ( $output_type == 'full' )
							{
								echo '<p class="ui-state-error">' . htmlentities( $response ) . '</p>';
							}
							else if ( $output_type == 'iphone' )
							{
								echo '<ul>';
								echo '<li>' . htmlentities( $response ) . '</li>';
								echo '</ul>';
							}
							else if ( $output_type == 'blank' )
							{
								$return_val = '';
								
								$temp = array();
								$temp[] = ( '[content]' );
								$temp[] = ( 'page="action_play"' );
								$temp[] = ( 'game="true"' );
								$temp[] = ( 'action="false"' );
								
								$return_val = implode( "\n", $temp );
								
								echo $return_val;
							}
						}
					}
					else if ( $actions[ $action ] == 'push' )
					{						
						$cup = $elaborations['me']['cup'];
						$good_pushes = array();
						
						foreach ( $pushes as $val )
						{
							$key = array_search( $val, $cup );
							if ( $key !== false )
							{
								$good_pushes[] = $val;
								unset( $cup[ $key ] );
							}
						}
						
						if ( count( $good_pushes ) && count( $cup ) && ( count( $good_pushes ) == count( $pushes ) ) )
						{
							$new_cup = implode( '', roll_dice( count( $cup ), false ) );
							
							$new_shown = $good_pushes;
							foreach ( $elaborations['me']['shown'] as $val )
								$new_shown[] = $val;
							
							$new_shown = implode( '', $new_shown );
							
							$sql = 'UPDATE game_players SET cup=' . quote_smart( $new_cup, $db ) . ', shown=' . quote_smart( $new_shown, $db ) . ' WHERE game_id=' . $game['game_id'] . ' AND player_id=' . $elaborations['me']['player_id'];
							$result = @mysql_query( $sql, $db );
							
							$sql = 'INSERT INTO action_logs (round_id, player_id, action_id) VALUES (' . $game['game_round'] . ',' . $elaborations['me']['player_id'] . ',' . $actions['push'] . ')';
							$result = @mysql_query( $sql, $db );
							
							$notification = ( $elaborations['me']['player_name'] . ' pushed!' );
						}
						else
						{
							$response = 'Bad push!';
							if ( $output_type == 'full' )
							{
								echo '<p class="ui-state-error">' . htmlentities( $response ) . '</p>';
							}
							else if ( $output_type == 'iphone' )
							{
								echo '<ul>';
								echo '<li>' . htmlentities( $response ) . '</li>';
								echo '</ul>';
							}
							else if ( $output_type == 'blank' )
							{
								$return_val = '';
								
								$temp = array();
								$temp[] = ( '[content]' );
								$temp[] = ( 'page="action_play"' );
								$temp[] = ( 'game="true"' );
								$temp[] = ( 'action="false"' );
								
								$return_val = implode( "\n", $temp );
								
								echo $return_val;
							}
						}
					}
					else if ( $actions[ $action ] == 'pass' )
					{
						$sql = 'INSERT INTO action_logs (round_id, player_id, action_id) VALUES (' . $game['game_round'] . ',' . $elaborations['me']['player_id'] . ',' . $actions['pass'] . ')';
						$result = @mysql_query( $sql, $db );
						
						$notification = ( $elaborations['me']['player_name'] . ' passed!' );
					}
					else if ( $actions[ $action ] == 'exact' )
					{
						$target_num = $elaborations['bid'][0];
						$target_face = $elaborations['bid'][1];
						
						$extra = array( 'action: exact (' . $target_num . ' ' . $target_face . ( ( $target_num == 1 )?(''):("'s") ) . ')' );
						foreach ( $players as $player )
						{
							if ( !empty( $player['cup'] ) )
							{
								$temp = $player['player_name'] . ': ';
								$temp2 = array_merge( $player['cup'], $player['shown'] );
								sort( $temp2 );
								
								$extra[] = $temp . implode( ', ', $temp2 );
							}
						}
						$extra = serialize( $extra );
						
						foreach ( $players as $player )
						{
							foreach ( $player['cup'] as $die )
								if ( ( $die == $target_face ) || ( ( !$game['special_rules'] ) && ( $die == 1 ) ) )
									$target_num--;
							
							foreach ( $player['shown'] as $die )
								if ( ( $die == $target_face ) || ( ( !$game['special_rules'] ) && ( $die == 1 ) ) )
									$target_num--;
						}
						
						$sql = 'UPDATE game_players SET exact_used=' . quote_smart( 'Y', $db ) . ' WHERE player_id=' . $elaborations['me']['player_id'] . ' AND game_id=' . $game['game_id'];
						$result = @mysql_query( $sql, $db );
						
						$sql = 'UPDATE game_players SET shown=CONCAT(cup,shown), cup=' . quote_smart( '', $db ) . ' WHERE game_id=' . $game['game_id'];
						$result = @mysql_query( $sql, $db );
						
						$sql = 'INSERT INTO action_logs (round_id, player_id, action_id, result, extra) VALUES (' . $game['game_round'] . ',' . $elaborations['me']['player_id'] . ',' . $actions['exact'] . ',' . ( ( $target_num == 0 )?(1):(0) ) . ',' . quote_smart( $extra, $db ) . ')';
						$result = @mysql_query( $sql, $db );
						
						$notification = ( $elaborations['me']['player_name'] . ' exacted!' );
					}
					else if ( $actions[ $action ] == 'challenge' )
					{						
						$targets = $elaborations['avail_actions']['challenge'];
						foreach ( $targets as $key => $val )
							$targets[ $key ] = $players[ $val ]['player_id'];
						
						if ( !in_array( $target, $targets ) )
						{
							$response = 'Bad challenge!';
							if ( $output_type == 'full' )
							{
								echo '<p class="ui-state-error">' . htmlentities( $response ) . '</p>';
							}
							else if ( $output_type == 'iphone' )
							{
								echo '<ul>';
								echo '<li>' . htmlentities( $response ) . '</li>';
								echo '</ul>';
							}
							else if ( $output_type == 'blank' )
							{
								$return_val = '';
								
								$temp = array();
								$temp[] = ( '[content]' );
								$temp[] = ( 'page="action_play"' );
								$temp[] = ( 'game="true"' );
								$temp[] = ( 'action="false"' );
								
								$return_val = implode( "\n", $temp );
								
								echo $return_val;
							}
						}
						else
						{
							$pass = false;
							$pass_target = -1;
							
							if ( ( $logs[0]['action_id'] == $actions['pass'] ) && ( $logs[0]['player_id'] == $target ) )
							{
								$pass = true;
								$pass_target = 0;
							}
							
							if ( ( $pass == false ) && ( count( $logs ) > 1 ) && ( $logs[1]['action_id'] == $actions['pass'] ) && ( $logs[1]['player_id'] == $target ) )
							{
								$pass = true;
								$pass_target = 1;
							}
							
							// challenge bid or pass
							if ( $pass )
							{																
								$extra = array( 'action: pass' );
								foreach ( $players as $player )
								{
									if ( !empty( $player['cup'] ) )
									{
										$temp = $player['player_name'] . ': ';
										$temp2 = array_merge( $player['cup'], $player['shown'] );
										sort( $temp2 );
										
										$extra[] = $temp . implode( ', ', $temp2 );
									}
								}
								$extra = serialize( $extra );
								
								$reverse_players = array();
								foreach ( $players as $key => $val )
									$reverse_players[ $val['player_id'] ] = $key;
								
								$target_face = $players[ $reverse_players[ $logs[ $pass_target ]['player_id'] ] ]['cup'][0];
								$bad_face = false;
								
								foreach ( $players[ $reverse_players[ $logs[ $pass_target ]['player_id'] ] ]['cup'] as $val )
									if ( $val != $target_face )
										$bad_face = true;
								
								foreach ( $players[ $reverse_players[ $logs[ $pass_target ]['player_id'] ] ]['shown'] as $val )
									if ( $val != $target_face )
										$bad_face = true;
								
								$sql = 'UPDATE game_players SET shown=CONCAT(cup,shown), cup=' . quote_smart( '', $db ) . ' WHERE game_id=' . $game['game_id'];
								$result = @mysql_query( $sql, $db );
								
								$sql = 'INSERT INTO action_logs (round_id, player_id, action_id, value, result, extra) VALUES (' . $game['game_round'] . ',' . $elaborations['me']['player_id'] . ',' . $actions['challenge'] . ',' . $target . ',' . ( ( $bad_face )?(1):(0) ) . ',' . quote_smart( $extra, $db ) . ')';
								$result = @mysql_query( $sql, $db );
								
								$notification = ( $elaborations['me']['player_name'] . ' challenged the pass!' );
							}
							else
							{
								$target_num = $elaborations['bid'][0];
								$target_face = $elaborations['bid'][1];
								
								$extra = array( 'action: bid (' . $target_num . ' ' . $target_face . ( ( $target_num == 1 )?(''):("'s") ) . ')' );
								foreach ( $players as $player )
								{
									if ( !empty( $player['cup'] ) )
									{
										$temp = $player['player_name'] . ': ';
										$temp2 = array_merge( $player['cup'], $player['shown'] );
										sort( $temp2 );
										
										$extra[] = $temp . implode( ', ', $temp2 );
									}
								}
								$extra = serialize( $extra );
								
								foreach ( $players as $player )
								{
									foreach ( $player['cup'] as $die )
										if ( ( $die == $target_face ) || ( ( !$game['special_rules'] ) && ( $die == 1 ) ) )
											$target_num--;
									
									foreach ( $player['shown'] as $die )
										if ( ( $die == $target_face ) || ( ( !$game['special_rules'] ) && ( $die == 1 ) ) )
											$target_num--;
								}
								
								$sql = 'UPDATE game_players SET shown=CONCAT(cup,shown), cup=' . quote_smart( '', $db ) . ' WHERE game_id=' . $game['game_id'];
								$result = @mysql_query( $sql, $db );
								
								$sql = 'INSERT INTO action_logs (round_id, player_id, action_id, value, result, extra) VALUES (' . $game['game_round'] . ',' . $elaborations['me']['player_id'] . ',' . $actions['challenge'] . ',' . $target . ',' . ( ( $target_num > 0 )?(1):(0) ) . ',' . quote_smart( $extra, $db ) . ')';
								$result = @mysql_query( $sql, $db );
								
								$notification = ( $elaborations['me']['player_name'] . ' challenged the bid!' );
							}
						}												
					}
					else if ( $actions[ $action ] == 'accept' )
					{
						$last_non_accept = 0;						
						
						$sql = 'INSERT INTO action_logs (round_id, player_id, action_id) VALUES (' . $game['game_round'] . ',' . $elaborations['me']['player_id'] . ',' . $actions['accept'] . ')';
						$result = @mysql_query( $sql, $db );
						
						{
							$new_counts = array();
							$still_going = 0;
							$down_to_one = -1;
							foreach ( $players as $val )
								$new_counts[ $val['player_id'] ] = count( $val['shown'] );
							
							if ( $logs[ $last_non_accept ]['action_id'] == $actions['exact'] )
							{							
								if ( $logs[ $last_non_accept ]['result'] == '0' )
								{
									$new_counts[ $logs[ $last_non_accept ]['player_id'] ]--;
									
									if ( $new_counts[ $logs[ $last_non_accept ]['player_id'] ] == 1 )
										$down_to_one = $logs[ $last_non_accept ]['player_id'];
								}
								else
								{								
									$new_counts[ $logs[ $last_non_accept ]['player_id'] ] = ( ( $new_counts[ $logs[ $last_non_accept ]['player_id'] ] == $game['dice_start'] )?( $new_counts[ $logs[ $last_non_accept ]['player_id'] ] ):( $new_counts[ $logs[ $last_non_accept ]['player_id'] ] + 1 ) );
								}
							}
							else if ( $logs[ $last_non_accept ]['action_id'] == $actions['challenge'] )
							{							
								$new_counts[ intval( $logs[ $last_non_accept ][ ( ( $logs[ $last_non_accept ]['result'] == '1' )?( 'value' ):( 'player_id' ) ) ] ) ]--;							
								
								if ( $new_counts[ intval( $logs[ $last_non_accept ][ ( ( $logs[ $last_non_accept ]['result'] == '1' )?( 'value' ):( 'player_id' ) ) ] ) ] == 1 )
									$down_to_one = intval( $logs[ $last_non_accept ][ ( ( $logs[ $last_non_accept ]['result'] == '1' )?( 'value' ):( 'player_id' ) ) ] );
							}
							
							foreach ( $new_counts as $count )
								if ( $count > 0 )
									$still_going++;
							
							foreach ( $new_counts as $key => $val )
							{
								$sql = 'UPDATE game_players SET cup=' . quote_smart( implode( '', roll_dice( $val, false ) ), $db ) . ', shown=' . quote_smart( '', $db ) . ' WHERE game_id=' . $game['game_id'] . ' AND player_id=' . $key;
								$result = @mysql_query( $sql, $db );
							}
							
							if ( $still_going > 1 )
							{
								if ( ( $down_to_one != -1 ) && ( $still_going > 2 ) )
								{
									$sql = 'SELECT COUNT(*) AS my_ct FROM rounds WHERE game_id=' . $game['game_id'] . ' AND special_rules=' . quote_smart( $down_to_one, $db );
									$result = @mysql_query( $sql, $db );
									$row = mysql_fetch_assoc( $result );
									
									if ( intval( $row['my_ct'] ) != 0 )
									{
										$down_to_one = -1;
									}
								}
								
								$sql = 'INSERT INTO rounds (game_id,special_rules) VALUES (' . $game['game_id'] . ',' . quote_smart( ( ( ( $down_to_one != -1 ) && ( $still_going > 2 ) )?( $down_to_one ):('N') ), $db ) . ')';
								$result = @mysql_query( $sql, $db );
								
								$notification = ( $elaborations['me']['player_name'] . ' stopped holding up science.' );
								$mail_me = false;
							}
							else
							{
								$sql = 'UPDATE games SET game_status=' . $status['Finished'] . ' WHERE game_id=' . $game['game_id'];
								$result = @mysql_query( $sql, $db );
								
								$notification = ( $elaborations['me']['player_name'] . ' ended the game.' );
								$game_over = true;
							}
						}
					}
					
					if ( !is_null( $notification ) )
					{
						$response = $notification;
						if ( $output_type == 'full' )
						{
							echo '<p class="ui-state-highlight">' . htmlentities( $response ) . '</p>';
						}
						else if ( $output_type == 'iphone' )
						{
							echo '<ul>';
							echo '<li>' . htmlentities( $response ) . '</li>';
							echo '</ul>';
						}
						else if ( $output_type == 'blank' )
						{
							$return_val = '';
							
							$temp = array();
							$temp[] = ( '[content]' );
							$temp[] = ( 'page="action_play"' );
							$temp[] = ( 'game="true"' );
							$temp[] = ( 'action="true"' );
							
							$return_val = implode( "\n", $temp );
							
							echo $return_val;
						}
						
						if ( $mail_me && $game['game_emails'] )
						{
							foreach ( $players as $player )
							{
								if ( count( $player['cup'] ) || count( $player['shown'] ) )
								{
									mail( $player['player_email'], ( 'Dice Game (' . $game['game_name'] . '): Action' ), ( 'Player: ' . $elaborations['me']['player_name'] . "\n" . $notification ) );
								}
							}
						}
					}
				}
				else
				{
					$response = 'Bad move!';
					if ( $output_type == 'full' )
					{
						echo '<p class="ui-state-error">' . htmlentities( $response ) . '</p>';
					}
					else if ( $output_type == 'iphone' )
					{
						echo '<ul>';
						echo '<li>' . htmlentities( $response ) . '</li>';
						echo '</ul>';
					}
					else if ( $output_type == 'blank' )
					{
						$return_val = '';
						
						$temp = array();
						$temp[] = ( '[content]' );
						$temp[] = ( 'page="action_play"' );
						$temp[] = ( 'game="true"' );
						$temp[] = ( 'action="false"' );
						
						$return_val = implode( "\n", $temp );
						
						echo $return_val;
					}
				}
			}
			else
			{
				$response = 'You are not allowed to play!';
				if ( $output_type == 'full' )
				{
					echo '<p class="ui-state-error">' . htmlentities( $response ) . '</p>';
				}
				else if ( $output_type == 'iphone' )
				{
					echo '<ul>';
					echo '<li>' . htmlentities( $response ) . '</li>';
					echo '</ul>';
				}
				else if ( $output_type == 'blank' )
				{
					$return_val = '';
					
					$temp = array();
					$temp[] = ( '[content]' );
					$temp[] = ( 'page="action_play"' );
					$temp[] = ( 'game="false"' );
					
					$return_val = implode( "\n", $temp );
					
					echo $return_val;
				}
			}
		}
		
		return !is_null( $notification );
	}
	
	if ( $user_info['id'] == -1 )
	{
		echo auth_form();
	}
	else
	{	
		$game_over = false;
		$output_type = $page_info['type'];
	
		$game_id = intval( get_param('game_id') );
		$action = intval( get_param( 'action' ) );
		$num = intval( get_param('num') );
		$face = intval( get_param('faces') );
		$target = intval( get_param('target') );
		
		$pushes = array();
		if ( isset( $_POST['pushes'] ) )
			$pushes = $_POST['pushes'];
		
		if ( $output_type != 'blank' )
		{
			echo '<div class="section">';
			echo '<div class="body">';
		}
		
		if ( _game_logic( $game_id, $game_over, $output_type, $action, $num, $face, $target, $pushes ) )
		{
			$actions = get_codes('action');
			
			if ( $actions[ $action ] == 'bid' )
			{
				if ( is_array( $pushes ) && !empty( $pushes ) )
				{
					_game_logic( $game_id, $game_over, $output_type, $actions['push'], $num, $face, $target, $pushes );
				}
			}
		}
		
		if ( $output_type != 'blank' )
		{
			echo '</div>';
			echo '</div>';
			
			echo '<div class="section">';
			echo '<div class="body">';
			echo '<input id="send-back" type="button" value="' . 'back to the game' . '" onclick="location.href=\'' . ( ( $game_over )?('play'):('play') ) . '.php?game_id=' . htmlentities( $game_id ) . '\';" />';
			if ( $output_type != 'iphone' )
			{
				echo jquery_button('send-back');
			}
			echo '</div>';
			echo '</div>';
		}
	}
	
	require 'common/private/lib/end.inc.php';
	
?>
