<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Dice Game {dash_title}</title>
		
		<link href="common/public/dice.css" rel="stylesheet" type="text/css" media="all" />
		<link rel="shortcut icon" href="common/public/favicon.ico" >

		<link type="text/css" href="common/public/jquery-ui-1.8.5.custom.css" rel="Stylesheet" />

		<script type="text/javascript" src="https://www.google.com/jsapi"></script>
		<script type="text/javascript">
			google.load( "jquery", "1.4" );
			google.load( "jqueryui", "1.8" );
		</script>
		
		{head}
	</head>
	
	<body>
		<div id="content">
			
			<div id="header">
				<div style="text-align: {align}"><a href="index.php"><img src="common/public/logo.png" /></a></div>
				<div style="text-align: {align}" class="nav">{user}</div>
			</div>
			
			<div id="title">
				{title}
			</div>
			<br />
		
			{content}
			
		</div>
	</body>
	
</html>
