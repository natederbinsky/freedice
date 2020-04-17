<?php

	function get_codes( $name )
	{
		global $db;
		$return_val = NULL;
		$sql = '';
		
		if ( $name == 'status' )
		{
			$sql = 'SELECT game_status_id, game_status_name FROM game_statuses ORDER BY game_status_name ASC';
			$query_result = @mysqli_query( $db, $sql );
			if ( $query_result )
			{
				while ( $row = mysqli_fetch_assoc( $query_result ) )
				{
					$return_val[ intval( $row['game_status_id'] ) ] = $row['game_status_name'];
					$return_val[ $row['game_status_name'] ] = intval( $row['game_status_id'] );
				}
			}
		}
		else if ( $name == 'color' )
		{
			$sql = 'SELECT color_id, color_name FROM colors ORDER BY color_name ASC';
			$query_result = @mysqli_query( $db, $sql );
			if ( $query_result )
			{
				while ( $row = mysqli_fetch_assoc( $query_result ) )
				{
					$return_val[ intval( $row['color_id'] ) ] = $row['color_name'];
					$return_val[ $row['color_name'] ] = intval( $row['color_id'] );
				}
			}
		}
		else if ( $name == 'action' )
		{
			$sql = 'SELECT action_id, action_name FROM actions ORDER BY action_name ASC';
			$query_result = @mysqli_query( $db, $sql );
			if ( $query_result )
			{
				while ( $row = mysqli_fetch_assoc( $query_result ) )
				{
					$return_val[ intval( $row['action_id'] ) ] = $row['action_name'];
					$return_val[ $row['action_name'] ] = intval( $row['action_id'] );
				}
			}
		}
		
		return $return_val;
	}

?>
