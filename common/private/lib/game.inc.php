<?php

	function roll_dice( $num = 1, $convert_on_one = true )
	{
		$return_val = array();
		
		for ( $i=0; $i<$num; $i++ )
			$return_val[] = mt_rand( 1, 6 );
		
		if ( ( $num == 1 ) && $convert_on_one )
			return $return_val[0];
		else
			return $return_val;
	}
	
	function get_players( $game_id )
	{
		global $db;
		$return_val = array();
		
		$sql = 'SELECT p.player_id, p.player_name, p.player_email, gp.dice_color, gp.cup, gp.shown, gp.exact_used, p.auto_refresh FROM game_players gp INNER JOIN players p ON gp.player_id=p.player_id WHERE gp.game_id=' . $game_id . ' ORDER BY gp.play_order ASC';
		$result = @mysql_query( $sql, $db );
		
		while ( $row = mysql_fetch_assoc( $result ) )
		{
			$cup = array();
			$shown = array();
			for ( $i=0; $i<strlen( $row['cup'] ); $i++ )
			{
				$cup[] = substr( $row['cup'], $i, 1 );
			}
			sort( $cup );
			for ( $i=0; $i<strlen( $row['shown'] ); $i++ )
			{
				$shown[] = substr( $row['shown'], $i, 1 );
			}
			sort( $shown );
			
			$return_val[] = array(
				'player_name' => ( $row['player_name'] ),
				'player_email' => ( $row['player_email'] ),	
				'player_id' => intval( $row['player_id'] ),
				'auto_refresh' => intval( $row['auto_refresh'] ),
				'dice_color' => intval( $row['dice_color'] ),
				'cup' => $cup,
				'shown' => $shown,
				'exact_used' => ( $row['exact_used'] == 'Y' ),
			);
		}
		
		return $return_val;
	}
	
	function get_game( $game_id )
	{		
		global $db;
		$return_val = NULL;
		
		$sql = 'SELECT game_id, game_name, game_status, dice_start, game_emails, (SELECT max(round_id) FROM rounds WHERE game_id=' . $game_id . ') AS game_round, (SELECT special_rules FROM rounds WHERE round_id=game_round) AS special_rules FROM games WHERE game_id=' . $game_id;
		$result = @mysql_query( $sql, $db );
		
		if ( $row = mysql_fetch_assoc( $result ) )
		{			
			$return_val['game_id'] = intval( $row['game_id'] );
			$return_val['game_status'] = intval( $row['game_status'] );
			$return_val['dice_start'] = intval( $row['dice_start'] );
			$return_val['game_name'] = ( $row['game_name'] );
			$return_val['game_round'] = intval( $row['game_round'] );
			$return_val['special_rules'] = ( $row['special_rules'] != 'N' );
			$return_val['game_emails'] = ( $row['game_emails'] == 'Y' );
		}
		
		return $return_val;
	}
	
	function get_round_logs( $round_id )
	{
		global $db;
		$return_val = array();
		
		$sql = 'SELECT player_id, action_id, value, result FROM action_logs WHERE round_id=' . $round_id . ' ORDER BY log_id DESC';
		
		$result = @mysql_query( $sql, $db );
		
		while ( $row = mysql_fetch_assoc( $result ) )
		{			
			$return_val[] = array(
				'player_id' => intval( $row['player_id'] ),
				'action_id' => intval( $row['action_id'] ),
				'value' => ( $row['value'] ),
				'result' => ( $row['result'] ),
			);
		}
		
		return $return_val;
	}
	
	function get_game_logs( $game_id )
	{
		global $db;
		$return_val = array();
		
		$sql = 'SELECT * FROM action_logs a WHERE a.round_id IN (SELECT r.round_id FROM rounds r WHERE r.game_id=' . quote_smart( $game_id, $db ) . ') AND CHAR_LENGTH(a.result) ORDER BY a.round_id DESC';
		
		$result = @mysql_query( $sql, $db );
		
		while ( $row = mysql_fetch_assoc( $result ) )
		{			
			$return_val[] = array(
				'player_id' => intval( $row['player_id'] ),
				'action_id' => intval( $row['action_id'] ),
				'value' => ( $row['value'] ),
				'result' => ( $row['result'] ),
				'extra' => ( $row['extra'] ),
			);
		}
		
		return $return_val;
	}
	
	function get_game_msgs( $game_id )
	{
		global $db;
		$return_val = array();
		
		$sql = 'SELECT p.player_name, m.msg FROM msgs m INNER JOIN players p ON m.player_id=p.player_id WHERE game_id=' . $game_id . ' ORDER BY msg_id DESC LIMIT 5';		
		$result = @mysql_query( $sql, $db );
		
		while ( $row = mysql_fetch_assoc( $result ) )
		{			
			$return_val[] = array(
				'player_name' => ( $row['player_name'] ),
				'msg' => ( $row['msg'] ),
			);
		}
		
		return $return_val;
	}
	
	function elaborate_state( &$logs, &$players, &$actions, &$game )
	{
		global $user_info;
		
		$player_count = count( $players );
		$reverse_players = array();
		foreach ( $players as $key => $val )
			$reverse_players[ $val['player_id'] ] = $key;
		
		$return_val = array( 
			'bid'=>NULL,
			'wait'=>false,
			'current_id' => 0,
			'current' => &$players[0],
			'me_id' => -1,
			'me' => NULL,
			'avail_actions' => array(),
			'count' => 0,
		);
		
		// find me
		foreach ( $players as $key => $val )
		{
			if ( ( count( $val['cup'] ) || count( $val['shown'] ) ) )
			{
				$return_val['count']++;
				
				if ( ( $user_info['id'] == $val['player_id'] ) )
				{
					$return_val['me'] =& $players[ $key ];
					$return_val['me_id'] = $key;
				}
			}
		}
			
		$pass = -1;
		$push = false;
		$bidder = 0;		
		$my_pass = false;
		$diff = 0;
		
		$accepters = array();
		$stop = false;
		
		if ( !empty( $logs ) )
		{
			// find last bid
			while ( $logs[ $bidder ]['action_id'] != $actions['bid'] )
			{				
				if ( ( $pass == -1 ) && ( $logs[ $bidder ]['action_id'] == $actions['pass'] ) )
					$pass = $reverse_players[ $logs[ $bidder ]['player_id'] ];
								
				if ( $logs[ $bidder ]['action_id'] == $actions['push'] )
					$push = true;
					
				if ( ( $logs[ $bidder ]['action_id'] == $actions['exact'] ) || ( $logs[ $bidder ]['action_id'] == $actions['challenge'] ) )
					$stop = true;
					
				if ( $logs[ $bidder ]['action_id'] == $actions['accept'] )
					$accepters[] = $logs[ $bidder ]['player_id'];
				
				$bidder++;
			}
			$return_val['bid'] = explode( ',', $logs[ $bidder ]['value'] );
			$return_val['bid'][0] = intval( $return_val['bid'][0] );
			$return_val['bid'][1] = intval( $return_val['bid'][1] );
			$bidder = $reverse_players[ $logs[ $bidder ]['player_id'] ];
			
			// are we waiting?
			if ( $stop )
			     $return_val['wait'] = true;
			     
			// find current player
			if ( $return_val['wait'] )
				$return_val['current_id'] = $reverse_players[ $logs[0]['player_id'] ];			
			else
				$return_val['current_id'] =  (( $reverse_players[ $logs[0]['player_id'] ] + 1 ) % $player_count );
			
			while ( !count( $players[ $return_val['current_id'] ]['cup'] ) && !count( $players[ $return_val['current_id'] ]['shown'] ) )
			{
				$diff++;
				$return_val['current_id'] = ( ( $return_val['current_id'] + 1 ) % $player_count );
			}
			$return_val['current'] =& $players[ $return_val['current_id'] ];

			//
			
			if ( !$return_val['wait'] )
			{
				// find my pass
				$counter = 0;
				while ( isset( $logs[ $counter ] ) && !( ( $logs[ $counter ]['player_id'] == $return_val['me']['player_id'] ) && ( $logs[ $counter ]['action_id'] == $actions['pass'] ) ) )
					$counter++;
				if ( isset( $logs[ $counter ] ) )
					$my_pass = true;
			}
		}
		else
		{
			global $db;
			
			$sql = 'SELECT MAX(round_id) AS prev_round FROM rounds WHERE game_id=' . $game['game_id'] . ' AND round_id<' . $game['game_round'];
			$result = @mysql_query( $sql, $db );
		
			if ( $row = mysql_fetch_assoc( $result ) )
			{			
				if ( !is_null( $row['prev_round'] ) )
				{
					$last_logs = get_round_logs( intval( $row['prev_round'] ) );
					
					$last_non_accept = 1;
					while ( $last_logs[ $last_non_accept ]['action_id'] == $actions['accept'] )
						$last_non_accept++;
					
					// losers start
					if ( $last_logs[ $last_non_accept ]['action_id'] == $actions['exact'] )
						$return_val['current_id'] = $reverse_players[ $last_logs[ $last_non_accept ]['player_id'] ];
					else
					{					
						if ( $last_logs[ $last_non_accept ]['result'] == '0' )
							$return_val['current_id'] = $reverse_players[ $last_logs[ $last_non_accept ]['player_id'] ];
						else
							$return_val['current_id'] = $reverse_players[ intval( $last_logs[ $last_non_accept ]['value'] ) ];
					}
					while ( !count( $players[ $return_val['current_id'] ]['cup'] ) && !count( $players[ $return_val['current_id'] ]['shown'] ) )
						$return_val['current_id'] = ( ( $return_val['current_id'] + 1 ) % $player_count );					
				}
				$return_val['current'] =& $players[ $return_val['current_id'] ];
			}			
		}
		
		if ( !is_null( $return_val['me'] ) )
		{
			// my turn
			if ( $return_val['current_id'] == $return_val['me_id'] )
			{
				if ( !$return_val['wait'] )
				{
					$return_val['avail_actions']['bid'] = true;
					
					if ( !is_null( $return_val['bid'] ) )
					{
						$challenge = array();
						
						if ( $players[ $bidder ]['player_id'] != $return_val['me']['player_id'] )
							$challenge[] = $bidder;
						if ( ( $pass != -1 ) && ( $players[ $pass ]['player_id'] != $return_val['me']['player_id'] ) )
							$challenge[] = $pass;
						if ( !empty( $challenge ) )
							$return_val['avail_actions']['challenge'] = array_unique( $challenge );
							
						if ( !$return_val['me']['exact_used'] )
							$return_val['avail_actions']['exact'] = true;
						
						if ( !$my_pass && ( ( count( $return_val['me']['cup'] ) + count( $return_val['me']['shown'] ) ) > 1 ) )
							$return_val['avail_actions']['pass'] = true;
					}
				}
				else
				{
					if ( $logs[0]['player_id'] == $return_val['me']['player_id'] )
					//if ( !in_array( $return_val['me']['player_id'], $accepters ) )
						$return_val['avail_actions']['accept'] = true;
				}
			}
			// directly after me
			else if ( ( ( $return_val['me_id'] + 1 + $diff ) % $player_count ) == $return_val['current_id'] )
			{
				if ( !$return_val['wait'] )
				{
					if ( !is_null( $return_val['bid'] ) && ( count( $return_val['me']['cup'] ) > 1 ) && !$push && ( $players[ $bidder ]['player_id'] == $return_val['me']['player_id'] ) )
						$return_val['avail_actions']['push'] = true;
				}
			}
			else
			{
				if ( !$return_val['wait'] )
				{
					if ( !is_null( $return_val['bid'] ) && ( $bidder != $return_val['me_id'] ) )
					{
						$return_val['avail_actions']['challenge'] = array( $bidder );
					}
				}
			}
		}
		
		return $return_val;
	}
	
	function easy_actions( &$logs, &$players, &$actions, $li = true, $split = false )
	{
		$return_val = array();
		$temp = NULL;
		
		$reverse_players = array();
		foreach ( $players as $key => $val )
			$reverse_players[ $val['player_id'] ] = $key;
		
		foreach ( $logs as $log )
		{
			if ( !$split )
			{
				switch ( $actions[ $log['action_id'] ] )
				{
					case 'bid':
						$temp = explode( ',', $log['value'] );
						$return_val[] = ( ( $li )?( '<li>' ):('') ) . htmlentities( $players[ $reverse_players[ $log['player_id'] ] ]['player_name'] ) . ' <b>bid</b> ' . $temp[0] . ' ' . $temp[1] . ( ( $temp[0] != 1 )?( "'s" ):('') ) . ( ( $li )?( '</li>' ):('') );
						break;
						
					case 'push':
						$return_val[] = ( ( $li )?( '<li>' ):('') ) . htmlentities( $players[ $reverse_players[ $log['player_id'] ] ]['player_name'] ) . ' <b>pushed</b>' . ( ( $li )?( '</li>' ):('') );
						break;
						
					case 'pass':
						$return_val[] = ( ( $li )?( '<li>' ):('') ) . htmlentities( $players[ $reverse_players[ $log['player_id'] ] ]['player_name'] ) . ' <b>passed</b>' . ( ( $li )?( '</li>' ):('') );
						break;
						
					case 'exact':
						$return_val[] = ( ( $li )?( '<li>' ):('') ) . htmlentities( $players[ $reverse_players[ $log['player_id'] ] ]['player_name'] ) . ' <b>exacted</b> and <b>' . htmlentities( ( $log['result'] == 1 )?('succeeded'):('failed') ) . '</b>' . ( ( $li )?( '</li>' ):('') );
						break;
						
					case 'challenge':
						$return_val[] = ( ( $li )?( '<li>' ):('') ) . htmlentities( $players[ $reverse_players[ $log['player_id'] ] ]['player_name'] ) . ' <b>challenged</b> <b>' . htmlentities( $players[ $reverse_players[ $log['value'] ] ]['player_name'] ) . '</b> and <b>' . htmlentities( ( $log['result'] == 1 )?('succeeded'):('failed') ) . '</b>' . ( ( $li )?( '</li>' ):('') );
						break;
						
					case 'accept':
						$return_val[] = ( ( $li )?( '<li>' ):('') ) . htmlentities( $players[ $reverse_players[ $log['player_id'] ] ]['player_name'] ) . ' <b>accepted</b>' . ( ( $li )?( '</li>' ):('') );
						break;
				}
			}
			else
			{
				switch ( $actions[ $log['action_id'] ] )
				{
					case 'bid':
						$temp = explode( ',', $log['value'] );
						$return_val[] = array( $players[ $reverse_players[ $log['player_id'] ] ]['player_name'], ( 'bid ' . $temp[0] . ' ' . $temp[1] . ( ( $temp[0] != 1 )?( "'s" ):('') ) ) );
						break;
						
					case 'push':
						$return_val[] = array( $players[ $reverse_players[ $log['player_id'] ] ]['player_name'], ( 'pushed' ) );
						break;
						
					case 'pass':
						$return_val[] = array( $players[ $reverse_players[ $log['player_id'] ] ]['player_name'], ( 'passed' ) );
						break;
						
					case 'exact':
						$return_val[] = array( $players[ $reverse_players[ $log['player_id'] ] ]['player_name'], ( 'exacted and ' . ( ( $log['result'] == 1 )?('succeeded'):('failed') ) ) );
						break;
						
					case 'challenge':
						$return_val[] = array( $players[ $reverse_players[ $log['player_id'] ] ]['player_name'], ( 'challenged ' . $players[ $reverse_players[ $log['value'] ] ]['player_name'] . ' and ' . ( ( $log['result'] == 1 )?('succeeded'):('failed') ) ) );
						break;
						
					case 'accept':
						$return_val[] = array( $players[ $reverse_players[ $log['player_id'] ] ]['player_name'], ( 'accepted' ) );
						break;
				}
			}
		}
		
		return $return_val;
	}
	
?>
