<?php

	$user_info = array( 'id'=>-1, 'email'=>'', 'name'=>'' );
	if ( get_param('logout') == 'now' )
		auth_logout();
	auth_login();
	
	function auth_form()
	{
		global $page_info;
		$return_val = '';
		
		$page_info['align'] = 'center';
		
		$default_user = '';
		if ( isset( $_COOKIE['dice_user'] ) )
			$default_user = $_COOKIE['dice_user'];
		
		if ( $page_info['type'] == 'full' )
		{
			$return_val .= '<div class="section" style="text-align: center">';
				$return_val .= '<form method="POST" action="' . htmlentities( SYSTEM_URL ) . '">';
					
					$return_val .= 'e-mail';
					$return_val .= '<br />';
					$return_val .= '<input type="text" size="50" id="i_login" name="auth_login" value="' . htmlentities( $default_user ) . '" />';
					
					$return_val .= '<br /><br />';
					
					$return_val .= 'password';
					$return_val .= '<br />';
					$return_val .= '<input id="i_pw" type="password" size="50" name="auth_pw" />';
					
					$return_val .= '<br /><br />';
					
					$return_val .= '<input id="send-login" type="submit" value="login" />';
					$return_val .= jquery_button('send-login');
					
				$return_val .= '</form>';
			$return_val .= '</div>';
			
			$return_val .= '<script>';
				$return_val .= 'document.getElementById("' . ( ( strlen( $default_user ) )?('i_pw'):('i_login') ) . '").focus();';
			$return_val .= '</script>';
		}
		else if ( $page_info['type'] == 'iphone' )
		{
			$return_val .= '<form method="POST" action="' . htmlentities( SYSTEM_URL ) . '">';
			
				$return_val .= '<h2><label for="auth_login">e-mail</label></h2>';
				$return_val .= '<input type="email" autocorrect="off" autocapitalize="off" name="auth_login" value="' . htmlentities( $default_user ) . '"  />';
				
				$return_val .= '<h2><label for="password">password</label></h2>';
				$return_val .= '<input type="password" autocorrect="off" autocapitalize="off" name="auth_pw" />';
				
				//$return_val .= '<br /><br />';
				$return_val .= '<input type="submit" value="login" />';
			
			$return_val .= '</form>';
		}
		else if ( $page_info['type'] == 'blank' )
		{
			$temp = array();
			
			$temp[] = ( '[content]' );
			$temp[] = ( 'page="login"' );
			$temp[] = ( 'method="POST"' );
			$temp[] = ( 'action="' . SYSTEM_URL . '"' );
			$temp[] = ( 'userkey="auth_login"' );
			$temp[] = ( 'pwkey="auth_pw"' );
			
			$return_val = implode( "\n", $temp );
		}
		
		return $return_val;
	}
		
	function auth_logout()
	{
		unset( $_SESSION['user_id'] );
	}
	
	function auth_login()
	{
		global $db;
		global $user_info;
		
		if ( EXEC_WEB && isset( $_SESSION['user_id'] ) )
		{
			$sql = 'SELECT player_name, player_email FROM players WHERE player_id=' . intval( $_SESSION['user_id'] );
			$query_result = mysqli_query( $db, $sql );
		
			if ( $query_result && ( mysqli_num_rows( $query_result ) == 1 ) )
			{
				$row = mysqli_fetch_assoc( $query_result );
				
				$user_info['id'] = intval( $_SESSION['user_id'] );
				$user_info['name'] = $row['player_name'];
				$user_info['email'] = $row['player_email'];
			}
		}
		else
		{
			$email = get_param('auth_login');
			$pw = get_param('auth_pw');
			
			if ( !empty( $email ) && !empty( $pw ) )
			{
				$sql = 'SELECT player_id, player_name FROM players WHERE player_email=' . quote_smart( $email, $db ) . ' AND player_pw=' . quote_smart( md5( $pw ), $db );
				$query_result = mysqli_query( $db, $sql );
		
				if ( $query_result && ( mysqli_num_rows( $query_result ) == 1 ) )
				{
					$row = mysqli_fetch_assoc( $query_result );
					
					$user_info['id'] = intval( $row['player_id'] );
					$user_info['email'] = $email;
					$user_info['name'] = $row['player_name'];
					
					if ( EXEC_WEB )
					{
						$_SESSION['user_id'] = $user_info['id'];
					
						setcookie( 'dice_user', $email, ( 60 * 60 * 24 * 30 + time() ) ); 
					}
				}
			}
		}
	}

?>
