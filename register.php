<?php

	require 'common/private/lib/start.inc.php';
	$page_info['title'] = 'Register an Account';
	
	echo '<form method="POST" action="action_register.php">';
		echo '<table>';			
			echo '<tr valign="top">';
				echo '<td width="150px">Name:</td>';
				echo '<td width="10px"></td>';
				echo '<td>';
					echo '<input type="text" size="50" maxlength="250" name="name" />';
				echo '</td>';
			echo '</tr>';
			
			echo '<tr><td colspan="3">&nbsp;</td></tr>';
			
			echo '<tr valign="top">';
				echo '<td>E-Mail:</td>';
				echo '<td></td>';
				echo '<td>';
					echo '<input type="text" size="50" maxlength="250" name="email" />';
				echo '</td>';
			echo '</tr>';
			
			echo '<tr><td colspan="3">&nbsp;</td></tr>';
			
			echo '<tr valign="top">';
				echo '<td>Password:</td>';
				echo '<td></td>';
				echo '<td>';
					echo '<input type="password" size="20" maxlength="50" name="pw" />';
				echo '</td>';
			echo '</tr>';
			
			echo '<tr><td colspan="3">&nbsp;</td></tr>';
			
			echo '<tr valign="top">';
				echo '<td>Confirm:</td>';
				echo '<td></td>';
				echo '<td>';
					echo '<input type="password" size="20" maxlength="50" name="pw_confirm" />';
				echo '</td>';
			echo '</tr>';
			
			echo '<tr><td colspan="3">&nbsp;</td></tr>';
			
			echo '<tr valign="top">';
				echo '<td></td>';
				echo '<td></td>';
				echo '<td>';
					echo '<input id="send-register" type="submit" value="register" />';
					echo ' ';
					echo '<input id="send-cancel" type="button" value="cancel" onclick="location.href=\'index.php\';" />';
	
					echo jquery_button('send-register');
					echo jquery_button('send-cancel');
				echo '</td>';
			echo '</tr>';	
					
		echo '</table>';
	echo '</form>';
	
	require 'common/private/lib/end.inc.php';
	
?>
