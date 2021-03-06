<?php
	
	// db connection information
	$db_info = array(
		'DB_SERVER' => '',
		'DB_USER' => '',
		'DB_PASS' => '',
		'DB_NAME' => '',
	);
	@include( 'db-config.inc.php' );

	// attempt connection
	$db = @mysqli_connect( $db_info['DB_SERVER'], $db_info['DB_USER'], $db_info['DB_PASS'], $db_info['DB_NAME'] );

	// check result
	if ( !$db )
		exit( 'error connecting to the database' );
	
	// remove db connection information
	unset( $db_info );
	
	//////////////////////////////
	// Useful DB functions
	//////////////////////////////
	
	// security function to secure "text" that is passed to sql queries
	function quote_smart( $value, $db, $override = false )
	{
		// strip slashes
		if ( get_magic_quotes_gpc() )
			$value = stripslashes( $value );
			
		// quote if not integer
		if ( $override || !is_numeric( $value ) ) 
			$value = "'" . mysqli_real_escape_string( $db, $value ) . "'";
  
  		return $value;
	}
   
?>
