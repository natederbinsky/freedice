<?php

	require 'common/private/lib/start.inc.php';
	$page_info['title'] = 'Register an Account';
	
	$name = get_param('name');
	$pw = get_param('pw');
	$confirm = get_param('pw_confirm');
	$email = get_param('email');
	
	echo '<div class="section">';
	echo '<div class="body">';
	
	if ( empty( $name ) || empty( $pw ) || ( $pw != $confirm ) || empty( $email ) )
	{
		echo '<p class="ui-state-error">Invalid account.</p>';
	}
	else
	{
		$check_sql = 'SELECT player_id FROM players WHERE player_email=' . quote_smart( $email, $db );
		$result = @mysql_query( $check_sql, $db );
		if ( mysql_num_rows( $result ) )
		{
			echo '<p class="ui-state-error">Invalid e-mail.</p>';
		}
		else
		{
			if ( ENABLE_REGISTRATION )
			{
				$sql = 'INSERT INTO players (player_name, player_pw, player_email, auto_refresh) VALUES (' . quote_smart( $name, $db ) . ',' . quote_smart( md5( $pw ), $db ) . ',' . quote_smart( $email, $db ) . ',' . 10 . ')';
				$result = @mysql_query( $sql, $db );
				
				echo '<p class="ui-state-highlight">Account added.</p>';
			}
			else
			{
				echo '<p class="ui-state-highlight">Contact Administrator to enable new account registration.</p>';
			}
		}
	}
	
	echo '</div>';
	echo '</div>';
	
	echo '<div class="section">';
	echo '<div class="body">';
	echo '<input id="send-home" type="button" value="home" onclick="location.href=\'index.php\';" />';
	echo jquery_button('send-home');
	echo '</div>';
	echo '</div>';
	
	require 'common/private/lib/end.inc.php';
	
?>
