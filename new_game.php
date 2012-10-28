<?php

	require 'common/private/lib/start.inc.php';
	$page_info['title'] = 'Create a New Game';
	
	if ( $user_info['id'] == -1 )
	{
		echo auth_form();
	}
	else
	{
		$colors = get_codes('color');
		
		if ( $page_info['type'] == 'iphone' )
		{
			echo '<form method="POST" action="action_new_game.php">';
			
				echo '<h2><label for="game_name">Game Name</label></h2>';
				echo '<input type="text" autocorrect="off" autocapitalize="off" maxlength="250" name="game_name" value="" />';
				
				echo '<h2><label for="game_pw">Game Password</label></h2>';
				echo '<input type="password" autocorrect="off" autocapitalize="off" maxlength="50" name="game_pw" value="" />';
			
				echo '<h2><label for="dice_start">Number of Dice</label></h2>';
				echo '<input type="number" autocorrect="off" autocapitalize="off" name="dice_start" value="5" style="width: 50px" />';
			
				echo '<h2><label for="game_emails">Send E-Mails</label></h2>';
				echo '<select name="game_emails" style="width: 100px"><option value="N">No</option><option value="Y">Yes</option></select>';
			
			
				echo '<h2><label for="my_color">Your Color</label></h2>';
				echo '<select name="my_color" style="width: 150px">';
					foreach ( $colors as $key => $val )
					{
						if ( is_numeric( $key ) )
						{
							echo '<option value="' . htmlentities( $key ) . '">' . htmlentities( $val ) . '</option>';
						}
					}
				echo '</select>';
			
				echo '<br /><br />';
				echo '<input type="submit" value="create" />';
			
			echo '</form>';
		}
		else if ( $page_info['type'] == 'blank' )
		{
			$temp = array();
			
			$temp[] = ( '[content]' );
			$temp[] = ( 'page="new_game"' );
			$temp[] = ( 'method="POST"' );
			$temp[] = ( 'action="' . ( SYSTEM_URL . 'action_new_game.php' ) . '"' );
			$temp[] = ( 'namekey="game_name"' );
			$temp[] = ( 'pwkey="game_pw"' );
			$temp[] = ( 'dicekey="dice_start"' );
			$temp[] = ( 'colorkey="my_color"' );
			$temp[] = ( 'emailkey="game_emails"' );
			
			$a_colors = array();
			foreach ( $colors as $key => $color )
			{
				if ( is_numeric( $key ) )
				{
					$a_colors[] = $key;
				}
			}
			$temp[] = ( 'colors="' . implode( ',', $a_colors ) . '"' );
			
			echo implode( "\n", $temp );
		}
		else
		{		
			echo '<form method="POST" action="action_new_game.php">';
			
				echo '<table>';
				
					echo '<tr valign="top">';
						echo '<td width="150px">Game Name:</td>';
						echo '<td width="10px"></td>';
						echo '<td>';
							echo '<input type="text" size="50" maxlength="250" name="game_name" />';
						echo '</td>';
					echo '</tr>';
					
					echo '<tr><td colspan="3">&nbsp;</td></tr>';
					
					echo '<tr valign="top">';
						echo '<td>Game Password:<br />(optional)</td>';
						echo '<td></td>';
						echo '<td>';
							echo '<input type="password" size="20" maxlength="50" name="game_pw" />';
						echo '</td>';
					echo '</tr>';
					
					echo '<tr><td colspan="3">&nbsp;</td></tr>';
					
					echo '<tr valign="top">';
						echo '<td>Number of Dice:</td>';
						echo '<td></td>';
						echo '<td>';
							echo '<input type="text" size="2" name="dice_start" value="5" />';
						echo '</td>';
					echo '</tr>';
					
					echo '<tr><td colspan="3">&nbsp;</td></tr>';
					
					echo '<tr valign="top">';
						echo '<td>Send E-Mails:</td>';
						echo '<td></td>';
						echo '<td>';
							echo '<select name="game_emails">';
								echo '<option value="N">No</option>';
								echo '<option value="Y">Yes</option>';
							echo '</select>';
						echo '</td>';
					echo '</tr>';
					
					echo '<tr><td colspan="3">&nbsp;</td></tr>';
					
					echo '<tr valign="top">';
						echo '<td>Your Color:</td>';
						echo '<td></td>';
						echo '<td>';
							echo '<select name="my_color">';
								foreach ( $colors as $key => $val )
								{
									if ( is_numeric( $key ) )
									{
										echo '<option value="' . htmlentities( $key ) . '">' . htmlentities( $val ) . '</option>';
									}
								}
							echo '</select>';
						echo '</td>';
					echo '</tr>';
					
					echo '<tr><td colspan="3">&nbsp;</td></tr>';
					
					echo '<tr valign="top">';
						echo '<td></td>';
						echo '<td></td>';
						echo '<td>';
							echo '<input id="send-submit" type="submit" value="create" />';
							echo ' ';
							echo '<input id="send-cancel" type="button" value="cancel" onclick="location.href=\'index.php\';" />';
			
							echo jquery_button('send-submit');
							echo jquery_button('send-cancel');
						echo '</td>';
					echo '</tr>';
				
				echo '</table>';
			echo '</form>';
		}
	}
	
	require 'common/private/lib/end.inc.php';
	
?>
