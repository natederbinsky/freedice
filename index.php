<?php

	require 'common/private/lib/start.inc.php';
	//$page_info['title'] = 'The DICE Game';
	
	if ( $user_info['id'] == -1 )
	{	
		echo auth_form();
	}
	else
	{
		if ( $page_info['type'] == 'full' )
		{
			$page_info['head'] = jquery_tabs( 'tabs' );
			
			$status = get_codes('status');
			$colors = get_codes('color');
			
			// get games
			$game_sql = 'SELECT g.game_id, g.game_name, (CHAR_LENGTH(g.game_pw) > 0) AS game_protected, g.game_status, g.dice_start, p.player_name, g.game_admin FROM games g INNER JOIN players p ON g.game_admin=p.player_id ORDER BY g.game_status ASC, g.game_id ASC';
			$game_result = @mysql_query( $game_sql, $db );
			$old_status = -1;
			$old_game = -1;
			
			echo '<div id="tabs">';
			echo '<ul>';
				if ( get_param('details') == 'Y' )
				{
					echo '<li><a href="#details">Details</a></li>';
				}
			
				echo '<li><a href="#games">Games</a></li>';
				echo '<li><a href="#leader">Leader Board</a></li>';
				echo '<li><a href="#challenge">Challenge Board</a></li>';
				echo '<li><a href="#survival">Survival Board</a></li>';
				echo '<li><a href="#exact">Exact Board</a></li>';
			echo '</ul>';
			
			if ( get_param('details') == 'Y' )
			{
				echo '<div id="details">';
				
				$leader_sql = 'SELECT game_name, player_id, player_name, SUM(won) AS won FROM (SELECT gp.player_id, p.player_name, g.game_id, g.game_name, CHAR_LENGTH(gp.cup)>0 AS won FROM game_players gp, games g, players p, game_statuses gs WHERE gp.game_id=g.game_id AND gp.player_id=p.player_id AND g.game_status=gs.game_status_id AND game_status_name=\'Finished\') x GROUP BY game_name, player_id ORDER BY game_name ASC, won DESC, player_name ASC';
				$leader_result = @mysql_query( $leader_sql, $db );
				
				$old_game = null;
				
				while ( $leader_row = mysql_fetch_assoc( $leader_result ) )
				{
					if ( $leader_row['game_name'] != $old_game )
					{
						if ( !is_null( $old_game ) )
						{
							echo '</tbody>';
							echo '</table>';
							echo '</div>';
							echo '</div>';
						}
						
						echo '<div class="section">';
						echo '<div class="title">' . htmlentities( $leader_row['game_name'] ) . '</div>';
						echo '<div class="body">';
						echo '<table class="perty">';
						
						echo '<thead>';
							echo '<tr>';
								echo '<td width="200px" style="text-align: left; font-weight: normal">Player Name</td>';
								echo '<td width="100px" style="text-align: center">Wins</td>';
							echo '</tr>';
						echo '</thead>';
						
						echo '<tbody>';
						
						$old_game = $leader_row['game_name'];
					}
					
					echo '<tr>';
						echo '<td><a href="player.php?player_id=' . htmlentities( $leader_row['player_id'] ) . '">' . htmlentities( $leader_row['player_name'] ) . '</a></td>';
						echo '<td style="text-align: center; font-weight: bold">' . htmlentities( intval( $leader_row['won'] ) ) . '</td>';
					echo '</tr>';
				}
				
				if ( !is_null( $old_game ) )
				{
					echo '</tbody>';
					echo '</table>';
					echo '</div>';
					echo '</div>';
				}
				
				echo '</div>';
			}
			
			echo '<div id="games">';
			$game_count = 0;
			
			if ( $game_result )
			{
				$player_sql = 'SELECT gp.game_id, p.player_id, p.player_name, p.player_email, (CHAR_LENGTH(gp.cup)+CHAR_LENGTH(gp.shown)) AS num_dice, gp.dice_color FROM (players p INNER JOIN game_players gp ON p.player_id=gp.player_id) INNER JOIN games g ON gp.game_id=g.game_id ORDER BY g.game_status ASC, gp.game_id ASC, gp.play_order ASC';
				$player_result = @mysql_query( $player_sql, $db );
				$player_row = mysql_fetch_assoc( $player_result );
				
				while ( $game_row = mysql_fetch_assoc( $game_result ) )
				{	
					if ( $status[ $game_row['game_status'] ] == 'Finished' )
						continue;
					
						if ( $old_status != intval( $game_row['game_status'] ) )
						{
							if ( $old_status != -1 )
							{
								echo '</div>';
								echo '</div>';
							}
							
							$old_status = intval( $game_row['game_status'] );
							
							echo '<div class="section">';
							echo '<div class="title">' . htmlentities( $status[ $old_status ] ) . '</div>';
							echo '<div class="body">';
						}
						
						echo '<div class="section">';
						echo '<div class="title">' . htmlentities( $game_row['game_name'] ) . ( ( ( $user_info['id'] == $game_row['game_admin'] ) && ( $status[ $game_row['game_status'] ] == 'Awaiting Players' ) )?(' (<a href="action_start_game.php?game_id=' . htmlentities( $game_row['game_id'] ) . '">start</a>)'):('') ) . '</div>';
						echo '<div class="body">';
						
						// get players
						$old_game = -1;
						$players = array();
						$good_colors = $colors;
						$in_game = false;
						
						while ( $player_row && ( $player_row['game_id'] == $game_row['game_id'] ) )
						{
							$players[ intval( $player_row['player_id'] ) ] = ( '<a href="player.php?player_id=' . htmlentities( $player_row['player_id'] ) . '">' . htmlentities( $player_row['player_name'] ) . '</a>' . ( ( $player_row['player_id'] == $game_row['game_admin'] )?( '*' ):( '' ) ) . ( ( $old_status != $status['Awaiting Players'] )?( ' (' . htmlentities( $player_row['num_dice'] ) . ')' ):('') ) );
							
							if ( intval( $player_row['player_id'] ) == $user_info['id'] )
								$in_game = true;
								
							$p_color = intval( $player_row['dice_color'] );
							unset( $good_colors[ $good_colors[ $p_color ] ] );
							unset( $good_colors[ $p_color ] );
							
							$player_row = mysql_fetch_assoc( $player_result );
						}
						
						echo 'Players: ' . implode( ', ', $players );
						echo '<br />';
						
						if ( $status[ $old_status ] == 'Awaiting Players' )
						{
							if ( !$in_game )
							{
								echo '<form method="POST" action="action_join_game.php">';
									echo '<input type="hidden" name="game_id" value="' . htmlentities( $game_row['game_id'] ) . '" />';
									
									if ( $game_row['game_protected'] == 1 )
									{
										echo '<input type="password" name="game_pw" value="" /> ';
									}
									else
									{
										echo '<input type="hidden" name="game_pw" value="" />';
									}
									
									echo '<select name="my_color">';
										foreach ( $good_colors as $key => $val )
										{
											if ( is_numeric( $key ) )
											{
												echo '<option value="' . htmlentities( $key ) . '">' . htmlentities( $val ) . '</option>';
											}
										}
									echo '</select>';
									
									echo '<input type="submit" value="join" />';
									
								echo '</form>';
							}
						}
						else if ( $status[ $old_status ] == 'In Progress' )
						{
							if ( !$in_game )
							{							
								echo '<a href="play.php?game_id=' . htmlentities( $game_row['game_id'] ) . '">watch</a>';
							}
							else
							{
								echo '<a href="play.php?game_id=' . htmlentities( $game_row['game_id'] ) . '">play</a>';
							}
						}
						else
						{
							/*
							echo '<form method="POST" action="history.php">';
								echo '<input type="hidden" name="game_id" value="' . htmlentities( $game_row['game_id'] ) . '" />';
								
								echo '<input type="submit" value="history" />';
								
							echo '</form>';
							*/
						}
						
					echo '</div>';
					echo '</div>';
					
					$game_count++;
				}
		
				if ( $old_status != -1 )
				{
					echo '</div>';
					echo '</div>';
				}
			}
			
			if ( $game_count == 0 )
			{
				echo '<div class="section">';
				echo '<div class="body">';
				echo 'No current games - why not start one?';
				echo '</div>';
				echo '</div>';
			}
			
			echo '</div>';
			
			// get leader board
			$leader_sql = 'SELECT played.player_id, people.player_name, played.total_games, won.wins, (won.wins / played.total_games) AS record FROM ((SELECT p.player_id, tt.total AS total_games FROM players p LEFT JOIN (SELECT t.player_id, COUNT(t.player_id) AS total FROM (SELECT gp.player_id FROM (game_players gp INNER JOIN games g ON gp.game_id=g.game_id) INNER JOIN game_statuses gs ON g.game_status=gs.game_status_id WHERE gs.game_status_name=\'Finished\') t GROUP BY t.player_id) tt ON p.player_id=tt.player_id) played LEFT JOIN (SELECT p.player_id, COUNT(p.player_id) AS wins FROM players p INNER JOIN (SELECT gp.player_id FROM (games g INNER JOIN game_statuses gs ON g.game_status=gs.game_status_id) INNER JOIN game_players gp ON g.game_id=gp.game_id WHERE gs.game_status_name=\'Finished\' AND CHAR_LENGTH(gp.cup)) w WHERE p.player_id=w.player_id GROUP BY w.player_id) won ON played.player_id=won.player_id) INNER JOIN players people ON played.player_id=people.player_id WHERE people.track_stats=\'Y\' AND played.total_games>0 ORDER BY won.wins DESC, record DESC, played.total_games DESC, people.player_id ASC';
			$leader_result = @mysql_query( $leader_sql, $db );
			
			echo '<div id="leader">';
			echo '<div class="section">';
			echo '<div class="body">';
			echo '<table class="perty">';
			
				echo '<thead>';
					echo '<tr>';
						echo '<td width="200px" style="text-align: left; font-weight: normal">Name</td>';
						echo '<td width="100px" style="text-align: center">Wins</td>';
						echo '<td width="100px" style="text-align: center; font-weight: normal">Games</td>';
						echo '<td width="100px" style="text-align: center; font-weight: normal">Record</td>';
					echo '</tr>';
				echo '</thead>';
			
				echo '<tbody>';
			
					while ( $leader_row = mysql_fetch_assoc( $leader_result ) )
					{
						echo '<tr>';
							echo '<td><a href="player.php?player_id=' . htmlentities( $leader_row['player_id'] ) . '">' . htmlentities( $leader_row['player_name'] ) . '</a></td>';
							echo '<td style="text-align: center; font-weight: bold">' . htmlentities( intval( $leader_row['wins'] ) ) . '</td>';
							echo '<td style="text-align: center">' . htmlentities( intval( $leader_row['total_games'] ) ) . '</td>';
							echo '<td style="text-align: center">' . htmlentities( round( 100*$leader_row['record'] ) ) . '%</td>';
						echo '</tr>';
					}
				
				echo '</tbody>';
			
			echo '</table>';
			
			echo '</div>';
			echo '</div>';
			echo '</div>';
			
			$leader_sql = 'SELECT p.player_name, p.player_id AS p_id, c.* FROM players p LEFT JOIN (SELECT a.player_id, a.total, b.success, (b.success/a.total) AS rate FROM (SELECT al.player_id, COUNT(*) AS total FROM action_logs al INNER JOIN actions a ON al.action_id=a.action_id WHERE a.action_name=\'challenge\' GROUP BY al.player_id) a LEFT JOIN (SELECT al.player_id, COUNT(*) AS success FROM action_logs al INNER JOIN actions a ON al.action_id=a.action_id WHERE a.action_name=\'challenge\' AND al.result=\'1\' GROUP BY al.player_id) b ON a.player_id=b.player_id) c ON p.player_id=c.player_id WHERE p.track_stats=\'Y\' AND total>0 ORDER BY c.rate DESC, c.total DESC, c.player_id ASC';
			$leader_result = @mysql_query( $leader_sql, $db );
			
			echo '<div id="challenge">';
			echo '<div class="section">';
			echo '<div class="body">';
			echo '<table class="perty">';
			
				echo '<thead>';
					echo '<tr>';
						echo '<td width="200px" style="text-align: left; font-weight: normal">Name</td>';
						echo '<td width="100px" style="text-align: center; font-weight: normal">Successes</td>';
						echo '<td width="100px" style="text-align: center; font-weight: normal">Challenges</td>';
						echo '<td width="100px" style="text-align: center">Record</td>';
					echo '</tr>';
				echo '</thead>';
			
				echo '<tbody>';
			
					while ( $leader_row = mysql_fetch_assoc( $leader_result ) )
					{
						echo '<tr>';
							echo '<td><a href="player.php?player_id=' . htmlentities( $leader_row['p_id'] ) . '">' . htmlentities( $leader_row['player_name'] ) . '</a></td>';
							echo '<td style="text-align: center">' . htmlentities( intval( $leader_row['success'] ) ) . '</td>';
							echo '<td style="text-align: center">' . htmlentities( intval( $leader_row['total'] ) ) . '</td>';
							echo '<td style="text-align: center; font-weight: bold">' . htmlentities( round( 100*$leader_row['rate'] ) ) . '%</td>';
						echo '</tr>';
					}
				
				echo '</tbody>';
			
			echo '</table>';
			
			echo '</div>';
			echo '</div>';
			echo '</div>';
			
			
			$leader_sql = 'SELECT *, (success/total) AS rate FROM (SELECT player_id AS p_id, player_name, (SELECT COUNT(*) AS total FROM action_logs WHERE action_id=2 AND value=p_id) AS total, (SELECT COUNT(*) FROM action_logs WHERE action_id=2 AND result=0 AND value=p_id) AS success FROM players WHERE track_stats=\'Y\') stuff WHERE total>0 ORDER BY rate DESC, total DESC';
			$leader_result = @mysql_query( $leader_sql, $db );
			
			echo '<div id="survival">';
			echo '<div class="section">';
			echo '<div class="body">';
			echo '<table class="perty">';
			
				echo '<thead>';
					echo '<tr>';
						echo '<td width="200px" style="text-align: left; font-weight: normal">Name</td>';
						echo '<td width="100px" style="text-align: center; font-weight: normal">Survivals</td>';
						echo '<td width="100px" style="text-align: center; font-weight: normal">Challenges</td>';
						echo '<td width="100px" style="text-align: center">Record</td>';
					echo '</tr>';
				echo '</thead>';
			
				echo '<tbody>';
			
					while ( $leader_row = mysql_fetch_assoc( $leader_result ) )
					{
						echo '<tr>';
							echo '<td><a href="player.php?player_id=' . htmlentities( $leader_row['p_id'] ) . '">' . htmlentities( $leader_row['player_name'] ) . '</a></td>';
							echo '<td style="text-align: center">' . htmlentities( intval( $leader_row['success'] ) ) . '</td>';
							echo '<td style="text-align: center">' . htmlentities( intval( $leader_row['total'] ) ) . '</td>';
							echo '<td style="text-align: center; font-weight: bold">' . htmlentities( round( 100*$leader_row['rate'] ) ) . '%</td>';
						echo '</tr>';
					}
				
				echo '</tbody>';
			
			echo '</table>';
			
			echo '</div>';
			echo '</div>';
			echo '</div>';
			
			$leader_sql = 'SELECT p.player_name, p.player_id AS p_id, c.*, (SELECT COUNT(*) AS my_games FROM game_players ugp, games ug WHERE ugp.player_id=p.player_id AND ugp.game_id=ug.game_id) AS my_games FROM players p LEFT JOIN (SELECT a.player_id, a.total, b.success, (b.success/a.total) AS rate FROM (SELECT al.player_id, COUNT(*) AS total FROM action_logs al INNER JOIN actions a ON al.action_id=a.action_id WHERE a.action_name=\'exact\' GROUP BY al.player_id) a LEFT JOIN (SELECT al.player_id, COUNT(*) AS success FROM action_logs al INNER JOIN actions a ON al.action_id=a.action_id WHERE a.action_name=\'exact\' AND al.result=\'1\' GROUP BY al.player_id) b ON a.player_id=b.player_id) c ON p.player_id=c.player_id WHERE p.track_stats=\'Y\' AND c.total>0 ORDER BY c.rate DESC, c.total DESC, c.player_id ASC';
			$leader_result = @mysql_query( $leader_sql, $db );
			
			echo '<div id="exact">';
			echo '<div class="section">';
			echo '<div class="body">';
			echo '<table class="perty">';
			
				echo '<thead>';
					echo '<tr>';
						echo '<td width="200px" style="text-align: left; font-weight: normal">Name</td>';
						echo '<td width="100px" style="text-align: center; font-weight: normal">Successes</td>';
						echo '<td width="100px" style="text-align: center; font-weight: normal">Exacts</td>';
						echo '<td width="100px" style="text-align: center; font-weight: normal">Games</td>';
						echo '<td width="100px" style="text-align: center">Record</td>';						
					echo '</tr>';
				echo '</thead>';
			
				echo '<tbody>';
			
					while ( $leader_row = mysql_fetch_assoc( $leader_result ) )
					{
						echo '<tr>';
							echo '<td><a href="player.php?player_id=' . htmlentities( $leader_row['p_id'] ) . '">' . htmlentities( $leader_row['player_name'] ) . '</a></td>';
							echo '<td style="text-align: center">' . htmlentities( intval( $leader_row['success'] ) ) . '</td>';
							echo '<td style="text-align: center">' . htmlentities( intval( $leader_row['total'] ) ) . '</td>';
							echo '<td style="text-align: center">' . htmlentities( round( $leader_row['my_games'] ) ) . '</td>';
							echo '<td style="text-align: center; font-weight: bold">' . htmlentities( round( 100*$leader_row['rate'] ) ) . '%</td>';							
						echo '</tr>';
					}
				
				echo '</tbody>';
			
			echo '</table>';
			
			echo '</div>';
			echo '</div>';
			echo '</div>';
			
			
			echo '</div>';
		}
		else if ( $page_info['type'] == 'iphone' )
		{
			{
				$game_sql = 'SELECT game_id, game_name FROM games g INNER JOIN game_statuses gs ON g.game_status=gs.game_status_id WHERE game_status_name=' . quote_smart( 'In Progress', $db ) . ' AND game_id IN (SELECT game_id FROM game_players WHERE player_id=' . quote_smart( $user_info['id'], $db ) . ') ORDER BY game_id ASC';
				$game_result = @mysql_query( $game_sql, $db );
				
				$first = true;
				
				echo '<h2>your games</h2>';
				echo '<ul>';
				
					while ( $game_row = mysql_fetch_assoc( $game_result ) )
					{
						$first = false;
						
						echo '<li>';
							echo '<a href="play.php?game_id=' . htmlentities( $game_row['game_id'] ) . '" class="showArrow">' . htmlentities( shorten( $game_row['game_name'], 30 ) ) . '</a>';
						echo '</li>';
					}
					
					if ( $first )
					{
						echo '<li>no games in progress</li>';
					}
				
				echo '</ul>';
			}
			
			{
				$game_sql = 'SELECT game_id, game_name FROM games g INNER JOIN game_statuses gs ON g.game_status=gs.game_status_id WHERE game_status_name=' . quote_smart( 'In Progress', $db ) . ' AND game_id NOT IN (SELECT game_id FROM game_players WHERE player_id=' . quote_smart( $user_info['id'], $db ) . ') ORDER BY game_id ASC';
				$game_result = @mysql_query( $game_sql, $db );
				
				$first = true;
				
				echo '<h2>other games</h2>';
				echo '<ul>';
				
					while ( $game_row = mysql_fetch_assoc( $game_result ) )
					{
						$first = false;
						
						echo '<li>';
							echo '<a href="play.php?game_id=' . htmlentities( $game_row['game_id'] ) . '" class="showArrow">' . htmlentities( shorten( $game_row['game_name'], 30 ) ) . '</a>';
						echo '</li>';
					}
					
					if ( $first )
					{
						echo '<li>no games in progress</li>';
					}
				
				echo '</ul>';
			}
			
			{
				$game_sql = 'SELECT game_id, game_name, (SELECT COUNT(*) FROM game_players ps WHERE ps.game_id=g.game_id) AS num_players FROM games g INNER JOIN game_statuses gs ON g.game_status=gs.game_status_id WHERE game_status_name=' . quote_smart( 'Awaiting Players', $db ) . ' AND game_admin=' . quote_smart( $user_info['id'], $db ) . ' ORDER BY game_id ASC';
				$game_result = @mysql_query( $game_sql, $db );
				
				if ( mysql_num_rows( $game_result ) )
				{
				
					echo '<h2>yours to start</h2>';
					echo '<ul>';
					
					while ( $game_row = mysql_fetch_assoc( $game_result ) )
					{					
						echo '<li>';
						echo '<a href="action_start_game.php?game_id=' . htmlentities( $game_row['game_id'] ) . '" class="showArrow">' . htmlentities( shorten( $game_row['game_name'], 30 ) ) . ' (' . intval( $game_row['num_players'] ) . ')</a>';
						echo '</li>';
					}				
					
					echo '</ul>';
				}
			}
			
			{
				$game_sql = 'SELECT game_id, game_name, (CHAR_LENGTH(g.game_pw) > 0) AS game_protected FROM games g INNER JOIN game_statuses gs ON g.game_status=gs.game_status_id WHERE game_status_name=' . quote_smart( 'Awaiting Players', $db ) . ' AND game_id NOT IN (SELECT game_id FROM game_players WHERE player_id=' . quote_smart( $user_info['id'], $db ) . ') ORDER BY game_id ASC';
				$game_result = @mysql_query( $game_sql, $db );
				
				if ( mysql_num_rows( $game_result ) )
				{
					
					$colors = get_codes('color');					
					
					echo '<br /><br />';
					
					echo '<h2>to join</h2>';
					echo '<ul>';
					
					while ( $game_row = mysql_fetch_assoc( $game_result ) )
					{					
						$remaining_colors = $colors;
						{							
							$color_sql = ( 'SELECT dice_color FROM game_players WHERE game_id=' . quote_smart( $game_row['game_id'], $db ) );
							$color_result = @mysql_query( $color_sql, $db );
							
							while ( $color_row = mysql_fetch_assoc( $color_result ) )
							{						
								unset( $remaining_colors[ $colors[ intval( $color_row['dice_color'] ) ] ] );
								unset( $remaining_colors[ intval( $color_row['dice_color'] ) ] );
							}
						}						
						
						echo '<li>';
							echo '<form method="POST" action="action_join_game.php">';
								echo '<input type="hidden" name="game_id" value="' . htmlentities( $game_row['game_id'] ) . '" />';
						echo '<span class="secondary"><input type="submit" value="join" /></span>';
							
								echo htmlentities( shorten( $game_row['game_name'], 30 ) );
								echo '<br />';
								echo '<select name="my_color" style="width: 150px">'; 
									foreach ( $remaining_colors as $r_k => $r_v )
									{
										if ( is_numeric( $r_k ) )
										{
											echo '<option value="' . htmlentities( $r_k ) . '">' . htmlentities( $r_v ) . '</option>';
										}
									}
								echo '</select>';
						
								if ( $game_row['game_protected'] == 1 )
								{
									echo '<br />';
									echo '<input type="password" autocorrect="off" autocapitalize="off" maxlength="50" name="game_pw" value="" />';
								}
								
							echo '</form>';
						echo '</li>';
					}				
					
					echo '</ul>';
				}
			}
			
			
			echo '<br /><br /><ul>';
				echo '<li><a href="' . SYSTEM_URL . 'new_game.php">new game</a></li>';
				echo '<li><a href="' . SYSTEM_URL . 'index.php?logout=now">logout</a></li>';
			echo '</ul>';
		}
		else if ( $page_info['type'] == 'blank' )
		{
			$return_val = '';
			
			$temp = array();
			
			$temp[] = ( '[content]' );
			$temp[] = ( 'page="index"' );
			$temp[] = ( 'playmethod="GET"' );
			$temp[] = ( 'playaction="' . SYSTEM_URL . 'play.php"' );
			$temp[] = ( 'gamekey="game_id"' );
			$temp[] = ( 'newmethod="GET"' );
			$temp[] = ( 'newaction="' . SYSTEM_URL . 'new_game.php"' );
			$temp[] = ( 'joinmethod="POST"' );
			$temp[] = ( 'joinaction="' . SYSTEM_URL . 'action_join_game.php"' );
			$temp[] = ( 'joinpwkey="game_pw"' );
			$temp[] = ( 'joincolorkey="my_color"' );
			$temp[] = ( 'startmethod="GET"' );
			$temp[] = ( 'startaction="' . SYSTEM_URL . 'action_start_game.php"' );
			
			$temp[] = ( '' );
			
			{
				$temp[] = ( '[your-games]' );
				
				$game_sql = 'SELECT game_id, game_name FROM games g INNER JOIN game_statuses gs ON g.game_status=gs.game_status_id WHERE game_status_name=' . quote_smart( 'In Progress', $db ) . ' AND game_id IN (SELECT game_id FROM game_players WHERE player_id=' . quote_smart( $user_info['id'], $db ) . ') ORDER BY game_id ASC';
				$game_result = @mysql_query( $game_sql, $db );
				
				while ( $game_row = mysql_fetch_assoc( $game_result ) )
				{					
					$temp[] = ( $game_row['game_id'] . '="' . $game_row['game_name'] . '"' );
				}
			}
			
			$temp[] = ( '' );
			
			{
				$temp[] = ( '[join-games]' );
				$temp[] = ( '; game_id=protected:0/1; color1,color2...' );
				
				$status = get_codes('status');
				
				$game_sql = ( 'SELECT game_id, (CHAR_LENGTH(g.game_pw) > 0) AS game_protected FROM games g, game_statuses gs WHERE g.game_status=gs.game_status_id AND gs.game_status_name=' . quote_smart( 'Awaiting Players', $db ) . ' AND game_id NOT IN (SELECT game_id FROM game_players WHERE player_id=' . $user_info['id'] . ') ORDER BY game_id ASC' );
				$game_result = @mysql_query( $game_sql, $db );
				while ( $game_row = mysql_fetch_assoc( $game_result ) )
				{
					$color_sql = ( 'SELECT color_id FROM colors WHERE color_id NOT IN (SELECT dice_color FROM game_players WHERE game_id=' . $game_row['game_id'] . ') ORDER BY color_id ASC' );
					$color_result = @mysql_query( $color_sql, $db );
					$a_colors = array();
					while ( $color_row = mysql_fetch_assoc( $color_result ) )
					{
						$a_colors[] = intval( $color_row['color_id'] );
					}
					
					if ( !empty( $a_colors ) )
					{
						$temp[] = ( $game_row['game_id'] . '=' . '"' . $game_row['game_protected'] . ';' . implode( ',', $a_colors ) . '"' );
					}
				}
				
			}
			
			$temp[] = ( '' );
			
			{
				$temp[] = ( '[start-games]' );
				$temp[] = ( '; game_id=current count' );
				
				$status = get_codes('status');
				
				$game_sql = ( 'SELECT g.game_id, (SELECT COUNT(*) FROM game_players gp WHERE gp.game_id=g.game_id) AS game_size FROM games g, game_statuses gs WHERE gs.game_status_id=g.game_status AND gs.game_status_name=' . quote_smart( 'Awaiting Players', $db ) . ' AND g.game_admin=' . $user_info['id'] . ' ORDER BY g.game_id ASC' );
				$game_result = @mysql_query( $game_sql, $db );
				while ( $game_row = mysql_fetch_assoc( $game_result ) )
				{
					$temp[] = ( $game_row['game_id'] . '=' . '"' . intval( $game_row['game_size'] ) . '"' );
				}
			}
			
			$return_val = implode( "\n", $temp );
			
			echo $return_val;
		}
	}
	
	require 'common/private/lib/end.inc.php';
	
?>
