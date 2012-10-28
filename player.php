<?php

	require 'common/private/lib/start.inc.php';
	
	if ( $user_info['id'] == -1 )
	{
		echo auth_form();
	}
	else
	{
		$player_id = intval( get_param('player_id') );
		
		$sql = 'SELECT player_name, player_email FROM players WHERE player_id=' . $player_id;
		$result = @mysql_query( $sql, $db );
		if ( $result && ( mysql_num_rows( $result ) == 1 ) )
		{
			$row = mysql_fetch_assoc( $result );
			
			$page_info['title'] = $row['player_name'];	
			
			echo '<div class="section">';
				echo '<div class="title">Info</div>';
				echo '<div class="body">';
					echo '<p>E-Mail: <a href="mailto:' . htmlentities( $row['player_email'] ) . '">' . htmlentities( $row['player_email'] ) . '</a></p>';
				echo '</div>';
			echo '</div>';
			
			// stats
			$games = array();
			$challenges = array();
			$exacts = array();
			{
				$g_sql = 'SELECT played.player_id, people.player_name, played.total_games, won.wins, (won.wins / played.total_games) AS record FROM ((SELECT p.player_id, tt.total AS total_games FROM players p LEFT JOIN (SELECT t.player_id, COUNT(t.player_id) AS total FROM (SELECT gp.player_id FROM (game_players gp INNER JOIN games g ON gp.game_id=g.game_id) INNER JOIN game_statuses gs ON g.game_status=gs.game_status_id WHERE gs.game_status_name=\'Finished\') t GROUP BY t.player_id) tt ON p.player_id=tt.player_id) played LEFT JOIN (SELECT p.player_id, COUNT(p.player_id) AS wins FROM players p INNER JOIN (SELECT gp.player_id FROM (games g INNER JOIN game_statuses gs ON g.game_status=gs.game_status_id) INNER JOIN game_players gp ON g.game_id=gp.game_id WHERE gs.game_status_name=\'Finished\' AND CHAR_LENGTH(gp.cup)) w WHERE p.player_id=w.player_id GROUP BY w.player_id) won ON played.player_id=won.player_id) INNER JOIN players people ON played.player_id=people.player_id where played.player_id=' . $player_id;
				$g_result = @mysql_query( $g_sql );
				$games = mysql_fetch_assoc( $g_result );
				
				$g_sql = 'SELECT p.player_name, c.* FROM players p LEFT JOIN (SELECT a.player_id, a.total, b.success, (b.success/a.total) AS rate FROM (SELECT al.player_id, COUNT(*) AS total FROM action_logs al INNER JOIN actions a ON al.action_id=a.action_id WHERE a.action_name=\'challenge\' GROUP BY al.player_id) a LEFT JOIN (SELECT al.player_id, COUNT(*) AS success FROM action_logs al INNER JOIN actions a ON al.action_id=a.action_id WHERE a.action_name=\'challenge\' AND al.result=\'1\' GROUP BY al.player_id) b ON a.player_id=b.player_id) c ON p.player_id=c.player_id WHERE p.player_id=' . $player_id;
				$g_result = @mysql_query( $g_sql );
				$challenges = mysql_fetch_assoc( $g_result );
				
				$g_sql = 'SELECT p.player_name, c.* FROM players p LEFT JOIN (SELECT a.player_id, a.total, b.success, (b.success/a.total) AS rate FROM (SELECT al.player_id, COUNT(*) AS total FROM action_logs al INNER JOIN actions a ON al.action_id=a.action_id WHERE a.action_name=\'exact\' GROUP BY al.player_id) a LEFT JOIN (SELECT al.player_id, COUNT(*) AS success FROM action_logs al INNER JOIN actions a ON al.action_id=a.action_id WHERE a.action_name=\'exact\' AND al.result=\'1\' GROUP BY al.player_id) b ON a.player_id=b.player_id) c ON p.player_id=c.player_id WHERE p.player_id=' . $player_id;
				$g_result = @mysql_query( $g_sql );
				$exacts = mysql_fetch_assoc( $g_result );
			}
			
			echo '<div class="section">';
				echo '<div class="title">Stats</div>';
				echo '<div class="body">';
					echo '<ul>';
						echo '<li>Games: ' . intval( $games['wins'] ) . '/' . intval( $games['total_games'] ) . ' (' . round( 100*$games['record'] ) . '%)' . '</li>';
						echo '<li>Challenges: ' . intval( $challenges['success'] ) . '/' . intval( $challenges['total'] ) . ' (' . round( 100*$challenges['rate'] ) . '%)' . '</li>';
						echo '<li>Exacts: ' . intval( $exacts['success'] ) . '/' . intval( $exacts['total'] ) . ' (' . round( 100*$exacts['rate'] ) . '%)' . '</li>';
					echo '</ul>';
				echo '</div>';
			echo '</div>';
			
			echo '<div class="section">';
				echo '<div class="title">Games</div>';
				echo '<div class="body">';
					$old_status = '';
					$history_sql = 'SELECT gp.game_id, g.game_name, gs.game_status_name, (SELECT CHAR_LENGTH(rgp.cup) FROM game_players rgp WHERE rgp.game_id=gp.game_id AND rgp.player_id=' . $player_id . ') AS my_remaining FROM (game_players gp INNER JOIN games g ON gp.game_id=g.game_id) INNER JOIN game_statuses gs ON g.game_status=gs.game_status_id WHERE gp.player_id=' . $player_id . ' AND gs.game_status_id<>1 ORDER BY g.game_status ASC, g.game_id DESC';
					$history_result = @mysql_query( $history_sql, $db );
					while ( $history_row = mysql_fetch_assoc( $history_result ) )
					{
						if ( $history_row['game_status_name'] != $old_status )
						{
							if ( $old_status != '' )
							{
								echo '</ul>';
								echo '</div>';
								echo '</div>';
							}
							
							echo '<div class="section">';
								echo '<div class="title">' . htmlentities( $history_row['game_status_name'] ) . '</div>';
								echo '<div class="body">';
								echo '<ul>';
							
							
							
							$old_status = $history_row['game_status_name'];
						}
						
						echo '<li style="' . ( (($history_row['game_status_name']=='Finished') && (intval($history_row['my_remaining'])>0))?('font-weight: bold'):('') ) . '"><a href="play.php?game_id=' . htmlentities( intval( $history_row['game_id'] ) ) . '">' . htmlentities( $history_row['game_name'] ) . '</a></li>';
					}
					if ( $old_status != '' )
					{
						echo '</ul>';
						echo '</div>';
						echo '</div>';
					} 
				echo '</div>';
			echo '</div>';
		}
		else
		{
			echo '<div class="section">';
				echo '<div class="body">';
					echo '<p>Cannot find player.</p>';
				echo '</div>';
			echo '</div>';
		}
		
		echo '<div class="section">';
			echo '<div class="body">';
				echo '<input id="send-home" type="button" value="home" onclick="location.href=\'index.php\';" />';
				echo jquery_button('send-home');
			echo '</div>';
		echo '</div>';
		
	}
	
	require 'common/private/lib/end.inc.php';
	
?>
