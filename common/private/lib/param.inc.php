<?php
	
	function get_param( $name )
	{
		$return_val = '';
		
		if ( EXEC_WEB )
		{
			if ( isset( $_GET[ $name ] ) )
			{
				$return_val = $_GET[ $name ];
			}
			else if ( isset( $_POST[ $name ] ) )
			{
				$return_val = $_POST[ $name ];
			}
		}
		else
		{
			static $inputs = null;
			
			if ( is_null( $inputs ) )
			{
				$inputs = array();
				
				global $argc;
				global $argv;
				
				for ( $i=1; $i<$argc; $i+=2 )
				{
					if ( isset( $argv[ $i + 1 ] ) )
					{
						if ( substr( $argv[ $i ], -2 ) == '[]' )
						{
							if ( !isset( $inputs[ $argv[ $i ] ] ) )
							{
								$inputs[ $argv[ $i ] ] = array();
							}
							
							$inputs[ $argv[ $i ] ][] = $argv[ $i + 1 ];
						}
						else
						{
							$inputs[ $argv[ $i ] ] = $argv[ $i + 1 ];
						}
					}
				}
			}
			
			if ( isset( $inputs[ $name ] ) )
			{
				$return_val = $inputs[ $name ];
			}
		}
		
		if ( is_string( $return_val ) )
		{
			$return_val = trim( $return_val );
			
			if ( EXEC_WEB && get_magic_quotes_gpc() )
			{
				$return_val = stripslashes( $return_val );
			}
		}
		
		return $return_val;
	}

?>
