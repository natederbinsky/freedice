<?php
	// terminal vs. web
	define( 'EXEC_WEB', !isset( $argc ) );
	
	if ( EXEC_WEB )
	{
		session_start();
	}
	
	// required libraries
	require_once 'db.inc.php';
	require_once 'param.inc.php';
	require_once 'auth.inc.php';
	require_once 'codes.inc.php';
	require_once 'game.inc.php';
	
	// basic function
	function shorten( $str, $len )
	{
		if ( strlen( $str ) > $len )
			$str = ( substr( $str, 0, ( $len - 3 ) ) . '...' );
			
		return $str;
	}
	
	// jquery
	function jquery_tabs( $id )
	{
		return '<script type="text/javascript">$(function() {$("#' . $id . '").tabs(); });</script>';
	}
	
	function jquery_button( $id )
	{
		return '<script type="text/javascript">$("#' . $id . '").button();</script>';
	}
	
	// constants
	$sys_config = array(
		'SYSTEM_URL' => '',
		'ENABLE_REGISTRATION' => false,
	);
	@include( 'start-config.inc.php' );
	
	foreach ( $sys_config as $name => $val )
	{
		define( $name, $val );
	}
	
	$page_info = array();
	$page_info['system'] = SYSTEM_URL;
	
	$page_info['title'] = '';
	$page_info['align'] = 'left';
	$page_info['head'] = '';
	
	// top link
	if ( $user_info['id'] == -1 )
	{
		$page_info['user'] = 'Hello - ' . ( ( ENABLE_REGISTRATION )?( '<a href="register.php">register</a> - ' ):('') ) . '<a href="rules.php">rules</a>';
	}
	else
	{
		$page_info['user'] = 'Hello ' . htmlentities( $user_info['name'] ) . ' - <a href="rules.php">rules</a> | <a href="new_game.php">new game</a> | <a href="' . SYSTEM_URL . 'index.php?logout=now">logout</a>';
	}
	
	// currently supported: full, iphone, blank
	$force_blank = ( get_param( 'blank' ) === 'Y' );
	
	if ( !$force_blank )
	{
		$page_info['type'] = ( ( ( stripos( $_SERVER['HTTP_USER_AGENT'], 'iphone' ) !== FALSE ) || ( stripos( $_SERVER['HTTP_USER_AGENT'], 'ipod' ) !== FALSE ) || ( stripos( $_SERVER['HTTP_USER_AGENT'], 'Mobile' ) !== FALSE ) )?('iphone'):('full') );
	}
	else
	{
		$page_info['type'] = 'blank';
	}
	
	ob_start();
?>
