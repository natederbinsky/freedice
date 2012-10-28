<?php

	require 'common/private/lib/start.inc.php';
	//$page_info['title'] = 'Play the DICE Game'; 
	
	if ( $user_info['id'] == -1 )
	{
		echo auth_form();
	}
	else
	{
		$status = get_codes('status');
		$actions = get_codes('action');
		$colors = get_codes('color');
		
		$game = get_game( intval( get_param('game_id') ) );
		if ( is_null( $game ) || ( $status[ $game['game_status'] ] == 'Awaiting Players' ) )
		{
			if ( $page_info['type'] != 'blank' )
			{
				echo '<div class="section">';
				echo '<div class="body">';
				
				$response = ( 'This Game is ' . ( ( !is_null( $game ) )?( htmlentities( $status[ $game['game_status'] ] ) ):( 'invalid' ) ) . '.' );
				
				if ( $page_info['type'] == 'iphone' )
				{
					echo '<ul>';
					echo '<li>' . $response . '</li>';
					echo '</ul>';
				}
				else
				{
					echo '<p class="ui-state-error">' . $response . '</p>';
				}
				
				echo '</div>';
				echo '</div>';
			}
			else 
			{
				$return_val = '';
				
				$temp = array();
				$temp[] = ( '[content]' );
				$temp[] = ( 'page="play"' );
				$temp[] = ( 'game="false"' );
				
				$return_val = implode( "\n", $temp );
				
				echo $return_val;
			}
		}
		else
		{
			$players = get_players( $game['game_id'] );
			$logs = get_round_logs( $game['game_round'] );
			$game_logs = get_game_logs( $game['game_id'] );
			$msgs = get_game_msgs( $game['game_id'] );
			$elaborations = elaborate_state( $logs, $players, $actions, $game );
			$total_hidden = 0;
			
			if ( $page_info['type'] == 'full' )
			{
				echo '<div class="section">';
				echo '<div class="title">' . ( ( ( $status[ $game['game_status'] ] == 'In Progress' ) )?('Current State'):('Victor') ) . '</div>';
				echo '<div class="body">';
				echo '<table class="perty">';
					echo '<thead>';
						echo '<tr>';
							echo '<td width="35px"></td>';
							echo '<td width="200px">Name</td>';
							echo '<td width="100px">Cup</td>';
							echo '<td width="100px">Pushed</td>';
							echo '<td width="10px"></td>';
						echo '</tr>';					
					echo '</thead>';
					echo '<tbody>';
						foreach ( $players as $key => $player )
						{						
							if ( count( $player['cup'] ) || count( $player['shown'] ) )
							{
								if ( $player['player_id'] != $user_info['id'] )
									$total_hidden += count( $player['cup'] );
								
								echo '<tr>';
									echo '<td style="text-align: center">';									
										if ( $elaborations['current_id'] == $key )
											echo '<img width="20px" height="20px" src="common/public/small.png" />';
									echo '</td>';
									
									echo '<td>';									
										echo '<a href="player.php?player_id=' . intval( $player['player_id'] ) . '">' . htmlentities( $player['player_name'] ) . '</a>' . ( ( !$player['exact_used'] )?(' (&exist;)'):('') );
									echo '</td>';
									
									echo '<td>';									
										if ( $player['player_id'] == $user_info['id'] )
										{
											if ( count( $player['cup'] ) )
												echo '<span title="' . htmlentities( implode( ', ', $player['cup'] ) ) . '">' . '[' . htmlentities( count( $player['cup'] ) ) . ']' . '</span>';
										}
										else
										{
											if ( !empty( $player['cup'] ) )
												echo '(' . htmlentities( count( $player['cup'] ) ) . ')';
										}
									echo '</td>';
									
									echo '<td>';								
										echo htmlentities( implode( ', ', $player['shown'] ) );									
									echo '</td>';
									
									echo '<td>';									
										//if ( $player['player_id'] == $user_info['id'] )
										//	echo '<b>*</b>';
									echo '</td>';
									
								echo '</tr>';
							}
						}
					echo '</tbody>';
				echo '</table>';
				
				echo '<br />';
				
				if ( $status[ $game['game_status'] ] == 'In Progress' )
				{
										
					echo 'Current Bid: ';
					if ( is_null( $elaborations['bid'] ) )
						echo 'none';
					else
						echo $elaborations['bid'][0] . ' ' . $elaborations['bid'][1] . ( ( $elaborations['bid'][0] != 1 )?('\'s'):('') );
						
					echo '<br />';
					echo 'Unknown Dice: ' . htmlentities( $total_hidden );
						
					if ( $game['special_rules'] )
					{
						echo '<br />';
						echo '<b>SPECIAL RULES</b>';
					}
					
					echo '<br />';			
					echo '<script language="JavaScript">';
						echo 'function dec() { var val = parseInt(document.getElementById("counter").innerHTML); document.getElementById("counter").innerHTML = ( val - 1 ); if (val == 0) location.href="play.php?game_id=' . $game['game_id'] . '&autodrop=true"; }';
						echo 'var stopper;';
						if ( get_param('autodrop') == 'true' )
						{
							 echo 'stopper = setInterval( \'dec()\', 1000 );';
						}
					echo '</script>';
					echo 'Timer: <span id="counter">' . ( ( is_null( $elaborations['me'] ) )?(10):($elaborations['me']['auto_refresh']) ) . '</span>';
					
					if ( get_param('autodrop') == 'true' )
					{
						echo ' <span id="starter">(<a href="#" onclick="location.href=\'play.php?game_id=' . $game['game_id'] . '\'; return false;">stop</a>)</span>';
					}
					else
					{
						echo ' <span id="starter">(<a href="#" onclick="setInterval( \'dec()\', 1000 ); document.getElementById(\'starter\').style.display=\'none\'; return false;">start</a>)</span>';
					}
					
					echo '</div>';
					echo '</div>';
					
					if ( !is_null( $elaborations['me'] ) )
					{
						echo '<div class="section">';
						echo '<div class="title">Actions</div>';
						echo '<div class="body">';
						
						echo '<form method="POST" action="action_play.php" />';
							echo '<ul>';
							
								if ( isset( $elaborations['avail_actions']['bid'] ) )
								{
									echo '<li>';
										echo '<input type="radio" name="action" value="' . htmlentities( $actions['bid'] ) . '" /> Bid <input type="text" size="2" name="num" /> <input type="text" size="2" maxlength="1" name="faces" />\'s';
									
										if ( count( $elaborations['me']['cup'] ) > 1 )
										{
											echo '';
											echo '<ul><li>';
												echo 'Push (<a href="#" onclick="var hidden=(this.innerHTML==\'show\'); this.innerHTML=((hidden)?(\'hide\'):(\'show\')); document.getElementById(\'pusho\').style.display=((hidden)?(\'\'):(\'none\')); return false;">show</a>)<br />';
												echo '<select size="5" multiple="multiple" name="pushes[]" id="pusho" style="display:none">';
													foreach ( $elaborations['me']['cup'] as $val )
													{
														echo '<option value="' . htmlentities( $val ) . '">' . htmlentities( $val ) . '</option>';
													}
												echo '</select>';
											echo '</li></ul>';
											echo '<br />';
										}
									
									echo '</li>';
								}
								
								if ( isset( $elaborations['avail_actions']['challenge'] ) )
								{
									echo '<li>';
										echo '<input type="radio" name="action" value="' . htmlentities( $actions['challenge'] ) . '" /> Challenge';
										echo ' <select name="target">';
											foreach ( $elaborations['avail_actions']['challenge'] as $challenge )
											{
												echo '<option value="' . htmlentities( $players[ $challenge ]['player_id'] ) . '">' . htmlentities( $players[ $challenge ]['player_name'] ) . '</option>';
											}
										echo '</select>';
									echo '</li>';
								}
								
								if ( isset( $elaborations['avail_actions']['exact'] ) )
								{
									echo '<li>';
										echo '<input type="radio" name="action" value="' . htmlentities( $actions['exact'] ) . '" /> Exact';
									echo '</li>';
								}
								
								if ( isset( $elaborations['avail_actions']['pass'] ) )
								{
									echo '<li>';
										echo '<input type="radio" name="action" value="' . htmlentities( $actions['pass'] ) . '" /> Pass';
									echo '</li>';
								}
								
								if ( isset( $elaborations['avail_actions']['push'] ) )
								{
									echo '<li>';
										echo '<input type="radio" name="action" value="' . htmlentities( $actions['push'] ) . '" /> Push (<a href="#" onclick="var hidden=(this.innerHTML==\'show\'); this.innerHTML=((hidden)?(\'hide\'):(\'show\')); document.getElementById(\'pusho\').style.display=((hidden)?(\'\'):(\'none\')); return false;">show</a>)<br />';
										echo '<select size="5" multiple="multiple" name="pushes[]" id="pusho" style="display:none">';
											foreach ( $elaborations['me']['cup'] as $val )
											{
												echo '<option value="' . htmlentities( $val ) . '">' . htmlentities( $val ) . '</option>';
											}
										echo '</select>';
									echo '</li>';
								}	
								
								if ( isset( $elaborations['avail_actions']['accept'] ) )
								{
									echo '<li>';
										echo '<input type="radio" name="action" value="' . htmlentities( $actions['accept'] ) . '" /> Accept your fate';
									echo '</li>';
								}
											
							echo '</ul>';
							
							if ( !empty( $elaborations['avail_actions'] ) )
							{
								echo '<input type="hidden" name="game_id" value="' . htmlentities( $game['game_id'] ) . '" />';
								echo '<input id="send-doit" type="submit" value="do it!" />';
								echo jquery_button('send-doit');
							}
							
						echo '</form>';
						
						echo '</div>';
						echo '</div>';
					}
					
					echo '<div class="section">';
					echo '<div class="title">Messages</div>';
					echo '<div class="body">';
					
					if ( !is_null( $elaborations['me'] ) )
					{
						echo '<form method="POST" action="action_msg.php" />';
							echo '<input type="text" name="msg" size="50" maxlength="250" value="" /> ';
							
							echo '<input type="hidden" name="game_id" value="' . htmlentities( $game['game_id'] ) . '" />';
							echo '<input id="send-send" type="submit" value="send" />';
							echo jquery_button('send-send');
						echo '</form>';
					}
					
					echo '<ul>';				
						foreach ( $msgs as $msg )
						{
							echo '<li><b>' . htmlentities( $msg['player_name'] ) . '</b> - ' . htmlentities( $msg['msg'] ) . '</li>';
						}
					echo '</ul>';
					
					echo '</div>';
					echo '</div>';
					
					echo '<div class="section">';
					echo '<div class="title">Round History</div>';
					echo '<div class="body">';
					echo '<ul>';
						$easy_logs = easy_actions( $logs, $players, $actions );
						foreach ( $easy_logs as $easy_log )
						{
							echo $easy_log;
						}
					echo '</ul>';
					
					echo '</div>';
					echo '</div>';
				}
				
				echo '<div class="section">';
				echo '<div class="title">Game History</div>';
				echo '<div class="body">';
				echo '<ul>';
					$easy_logs = easy_actions( $game_logs, $players, $actions, false );
					foreach ( $easy_logs as $key => $easy_log )
					{
						$xtras = NULL;
						if ( !empty( $game_logs[ $key ]['extra'] ) )
							$xtras = unserialize( $game_logs[ $key ]['extra'] );
						
						echo '<li>';
							echo 'round ' . ( count( $easy_logs ) - $key ) . ': ' . $easy_log;
							if ( !is_null( $xtras ) )
							{
								echo '<br />';
								
								foreach ( $xtras as $xtra )
								{
									echo htmlentities( $xtra ) . '<br />';
								}
							}
						echo '<br /></li>';
					}
				echo '</ul>';
				
				echo '</div>';
				echo '</div>';	
			}
			else if ( $page_info['type'] == 'iphone' )
			{
				echo '<h2>Current State</h2>';
				echo '<ul>';
					foreach ( $players as $key => $player )
					{						
						if ( count( $player['cup'] ) || count( $player['shown'] ) )
						{
							if ( $player['player_id'] != $user_info['id'] )
								$total_hidden += count( $player['cup'] );
								
							echo '<li ' . ( ( $elaborations['current_id'] == $key )?('class="showDice"'):('') ) . '><span style="margin-left: 30px">';
								
								echo htmlentities( shorten( $player['player_name'], 10 ) );
								
								echo ' &nbsp;';
								
								if ( $player['player_id'] == $user_info['id'] )
								{
									echo '[';
										if ( !$player['exact_used'] )
										{
											echo '&exist;';
											if ( count( $player['cup'] ) )
												echo ', ';
										}
										
										echo htmlentities( implode( ', ', $player['cup'] ) );
									echo ']';
								}
								else
								{
									echo '(';
										if ( !$player['exact_used'] )
										{
											echo '&exist;';
											
											if ( count( $player['cup'] ) )
												echo ', ';
										}
										
										echo htmlentities( count( $player['cup'] ) );
									echo ')';
								}
								
								if ( count( $player['shown'] ) )
									echo '<span class="secondary">' . htmlentities( implode( ', ', $player['shown'] ) ) . '</span>';
								
							echo '</span></li>';
						}
					}
				echo '</ul>';
				
				echo '<ul>';
					echo '<li>Current Bid<span class="secondary">' . htmlentities( ( is_null( $elaborations['bid'] ) )?('none'):( $elaborations['bid'][0] . ' ' . $elaborations['bid'][1] . ( ( $elaborations['bid'][0] != 1 )?('\'s'):('') ) ) ) . '</span></li>';
				
					echo '<li>Unknown Dice<span class="secondary">' . htmlentities( $total_hidden ) . '</span></li>';
						
					if ( $game['special_rules'] )
					{
						echo '<li>SPECIAL RULES</li>';
					}
				echo '</ul>';
				
				echo '<h2>Actions</h2>';
				echo '<form method="POST" action="action_play.php" />';
					echo '<ul>';
					
						if ( isset( $elaborations['avail_actions']['bid'] ) )
						{
							echo '<li>';
								echo '<input type="radio" name="action" value="' . htmlentities( $actions['bid'] ) . '" /> Bid <span class="secondary"><input type="number" name="num" style="width: 50px" /> <input type="number" maxlength="1" name="faces" style="width: 50px" />\'s</span>';
							echo '</li>';
							
							if ( count( $elaborations['me']['cup'] ) > 1 )
							{
								echo '<li>';
									echo 'Push';
									echo ' <span class="secondary"><select style="width: 130px" multiple="multiple" name="pushes[]">';
										foreach ( $elaborations['me']['cup'] as $val )
										{
											echo '<option value="' . htmlentities( $val ) . '">' . htmlentities( $val ) . '</option>';
										}
									echo '</select></span>';
								echo '</li>';
							}
						}
						
						if ( isset( $elaborations['avail_actions']['challenge'] ) )
						{
							echo '<li>';
								echo '<input type="radio" name="action" value="' . htmlentities( $actions['challenge'] ) . '" /> Challenge';
								echo ' <span class="secondary"><select name="target" style="width: 130px">';
									foreach ( $elaborations['avail_actions']['challenge'] as $challenge )
									{
										echo '<option value="' . htmlentities( $players[ $challenge ]['player_id'] ) . '">' . htmlentities( $players[ $challenge ]['player_name'] ) . '</option>';
									}
								echo '</select></span>';
							echo '</li>';
						}
						
						if ( isset( $elaborations['avail_actions']['exact'] ) )
						{
							echo '<li>';
								echo '<input type="radio" name="action" value="' . htmlentities( $actions['exact'] ) . '" /> Exact';
							echo '</li>';
						}
						
						if ( isset( $elaborations['avail_actions']['pass'] ) )
						{
							echo '<li>';
								echo '<input type="radio" name="action" value="' . htmlentities( $actions['pass'] ) . '" /> Pass';
							echo '</li>';
						}
						
						if ( isset( $elaborations['avail_actions']['push'] ) )
						{
							echo '<li>';
								echo '<input type="radio" name="action" value="' . htmlentities( $actions['push'] ) . '" /> Push';
								echo ' <span class="secondary"><select style="width: 135px" multiple="multiple" name="pushes[]">';
									foreach ( $elaborations['me']['cup'] as $val )
									{
										echo '<option value="' . htmlentities( $val ) . '">' . htmlentities( $val ) . '</option>';
									}
								echo '</select></span>';
							echo '</li>';
						}	
						
						if ( isset( $elaborations['avail_actions']['accept'] ) )
						{
							echo '<li>';
								echo '<input type="radio" name="action" value="' . htmlentities( $actions['accept'] ) . '" /> Accept your fate';
							echo '</li>';
						}
									
					echo '</ul>';
					
					if ( !empty( $elaborations['avail_actions'] ) )
					{
						echo '<input type="hidden" name="game_id" value="' . htmlentities( $game['game_id'] ) . '" />';						
						echo '<input type="submit" value="do it!" />';
					}
					
				echo '</form>';
				
				echo '<h2>Messages</h2>';
				if ( !is_null( $elaborations['me'] ) )
				{
					echo '<form method="POST" action="action_msg.php" />';
						echo '<input type="text" name="msg" maxlength="250" value="" /> ';
						
						echo '<input type="hidden" name="game_id" value="' . htmlentities( $game['game_id'] ) . '" />';
						echo '<input type="submit" value="send" />';
					echo '</form>';
				}
				
				echo '<ul id="short_msg">';				
					foreach ( $msgs as $m_key => $msg )
					{
						echo '<li onclick="document.getElementById(\'long_msg\').style.display=\'\'; document.getElementById(\'short_msg\').style.display=\'none\'; document.getElementById(\'long_name\').innerHTML=document.getElementById(\'short_name_' . $m_key . '\').innerHTML; document.getElementById(\'long_txt\').innerHTML=document.getElementById(\'short_txt_' . $m_key . '\').value;"><span id="short_name_' . $m_key . '">' . htmlentities( shorten( $msg['player_name'], 10 ) ) . '</span><span class="secondary">' . htmlentities( shorten( $msg['msg'], 20 ) ) . '</span><input type="hidden" id="short_txt_' . $m_key . '" value="' . htmlentities( $msg['msg'] ) . '"/></li>';
					}
				echo '</ul>';
				
				echo '<div id="long_msg" onclick="document.getElementById(\'long_msg\').style.display=\'none\'; document.getElementById(\'short_msg\').style.display=\'\';" style="display: none; background-color: white; font-size:17px; font-family: Helvetica; width: 95%; -webkit-border-radius: 8px;">';
				echo '<div id="long_name" style="font-weight: bold; padding: 10px 10px 14px 10px;">joe bob frankfurt</div><div id="long_txt" style="padding: 0px 10px 14px 10px">very very very very very very very long message</div>';
				echo '</div>';
				
				echo '<h2>Round History</h2>';
				echo '<ul>';
					$easy_logs = easy_actions( $logs, $players, $actions, false, true );
					foreach ( $easy_logs as $easy_log )
					{
						echo '<li>' . htmlentities( shorten( $easy_log[0], 10 ) ) . '<span class="secondary">' . htmlentities( $easy_log[1] ) . '</span></li>';
					}
				echo '</ul>';
				
			}
			else if ( $page_info['type'] == 'blank' )
			{
				$return_val = '';
				
				$temp = array();
				$temp[] = ( '[content]' );
				$temp[] = ( 'page="play"' );
				$temp[] = ( 'game="true"' );
				$temp[] = ( 'method="POST"' );
				$temp[] = ( 'action="' . ( SYSTEM_URL . 'action_play.php' ) . '"' );
				$temp[] = ( 'actionkey="action"' );
				$temp[] = ( 'gamekey="game_id"' );
				$temp[] = ( 'game_id="' . $game['game_id'] . '"'  );
				$temp[] = ( '' );
				
				$temp[] = ( '[state]' );
				$temp[] = ( 'special="' . ( ( $game['special_rules'] )?('true'):('false') ) . '"'  );
				$temp[] = ( 'round="' . ( count( $game_logs ) + 1 ) . '"' );
				$temp[] = ( 'inprogress="' . ( ( ( $status[ $game['game_status'] ] == 'In Progress' ) )?('true'):('false') ) . '"' );
				$temp[] = ( '' );
				
				$temp[] = ( '[players]' );
				{
					$temp[] = ( 'me="' . $user_info['id'] . '"' );
					{
						$my_status = 'visitor';
						foreach ( $players as $key => $player )
						{
							if ( $player['player_id'] == $user_info['id'] )
							{
								if ( count( $player['cup'] ) || count( $player['shown'] ) )
								{
									if ( $status[ $game['game_status'] ] == 'In Progress' )
									{
										$my_status = 'play';
									}
									else
									{
										$my_status = 'won';
									}
								}
								else
								{
									$my_status = 'lost';
								}
							}
						}
						
						$temp[] = ( '; mystatus= visitor | play | won | lost' );
						$temp[] = ( 'mystatus="' . $my_status . '"' );
					}
					$temp[] = ( 'current="' . $players[ $elaborations['current_id'] ]['player_id'] . '"' );
					if ( $status[ $game['game_status'] ] != 'In Progress' )
					{
						$temp[] = ( 'victor="' . $players[ $elaborations['current_id'] ]['player_id'] . '"' );
					}
					
					$temp[] = ( '; player_id="player_name|exists|hidden|pushed"' );
					
					foreach ( $players as $key => $player )
					{						
						if ( count( $player['cup'] ) || count( $player['shown'] ) )
						{
							$temp[] = ( $player['player_id'] . '="' . $player['player_name'] . '|' . ( ( $player['exact_used'] )?('false'):('true') ) . '|' . ( ( $player['player_id'] == $user_info['id'] )?( implode( ',', $player['cup'] ) ):( count( $player['cup'] ) ) ) . '|' . implode( ',', $player['shown'] ) . '"' );
						}
					}
				}
				$temp[] = ( '' );
				
				$temp[] = ( '[affordances]' );
				{					
					$temp[] = ( 'challengekey="target"' );
					$temp[] = ( 'pushkey="pushes[]"' );
					$temp[] = ( 'multiplierkey="num"' );
					$temp[] = ( 'faceskey="faces"' );
					
					//
					
					$temp[] = ( 'bidval="' . $actions['bid'] . '"' );
					$temp[] = ( 'challengeval="' . $actions['challenge'] . '"' );
					$temp[] = ( 'exactval="' . $actions['exact'] . '"' );					
					$temp[] = ( 'passval="' . $actions['pass'] . '"' );
					$temp[] = ( 'pushval="' . $actions['push'] . '"' );
					$temp[] = ( 'acceptval="' . $actions['accept'] . '"' );					
					
					//
					
					$temp[] = ( 'bid="' . ( ( isset( $elaborations['avail_actions']['bid'] ) )?('true'):('false') ) . '"' );
					
					if ( isset( $elaborations['avail_actions']['challenge'] ) )
					{
						$cs = array();
						
						foreach ( $elaborations['avail_actions']['challenge'] as $challenge )
						{
							$cs[] = $players[ $challenge ]['player_id'];
						}
						
						$temp[] = ( 'challenge="true|' . implode( ',', $cs ) . '"' );
					}
					else
					{
						$temp[] = ( 'challenge="false"' );
					}
					
					$temp[] = ( 'exact="' . ( ( isset( $elaborations['avail_actions']['exact'] ) )?('true'):('false') ) . '"' );
					
					$temp[] = ( 'pass="' . ( ( isset( $elaborations['avail_actions']['pass'] ) )?('true'):('false') ) . '"' );
					
					$temp[] = ( 'push="' . ( ( isset( $elaborations['avail_actions']['push'] ) )?('true'):('false') ) . '"' );
					
					$temp[] = ( 'accept="' . ( ( isset( $elaborations['avail_actions']['accept'] ) )?('true'):('false') ) . '"' );
				}
				$temp[] = ( '' );
				
				$temp[] = ( '[history]' );
				$temp[] = ( '; logkey="player_id|action|value|result"' );
				{
					foreach ( $logs as $key => $log )
					{
						$temp[] = ( $key . '="' . ( $log['player_id'] ) . '|' . ( $actions[ $log['action_id'] ] ) . '|' . $log['value'] . '|' . $log['result'] . '"' );
					}					
				}
				$temp[] = ( '' );
				
				$temp[] = ( '[rounds]' );
				$temp[] = ( '; round_id = "player_id|action|value|result"' );
				{
					$max_round = count( $game_logs );
					
					foreach ( $game_logs as $key => $log )
					{
						$temp[] = ( ( $max_round - $key ) . '="' . ( $log['player_id'] ) . '|' . ( $actions[ $log['action_id'] ] ) . '|' . $log['value'] . '|' . $log['result'] . '"' );
					}
				}
				
				$return_val = implode( "\n", $temp );
				
				echo $return_val;
			}
		}	
	}
	
	require 'common/private/lib/end.inc.php';
	
?>
