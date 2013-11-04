<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="utf-8"/>
	<base href="../" />
	<meta name="author" content="powered by eljebus">

	<title>Mind On Cloud Sistemas Web</title>
	<meta name="description" content="Diseño y Desarrollo de Sistemas Web"> 
	<link rel="stylesheet" href="css/<? print($hoja)?>.css" />


</head>
<body class="metrouicss">
	<div id='content-principal' class="body-text class="page"">
		<?
			include('views/header.html') ;
		?>

		<?
			include($vista_interna) ;
		?>

		<footer class="nav-bar-inner bg-color-blue">
			 <h3 style='color:white;'>
			 	Mind On Cloud 2013<small> © Todos los derechos reservados</small>
			 </h3> 
		</footer>
	</div>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
	<script type="text/javascript" src="javascript/modernizr.custom.28468.js"></script>
	<script type="text/javascript" src="javascript/jquery.cslider.js"></script>
	<script type="text/javascript" src="javascript/input-control.js"></script>

	<script type="text/javascript" src="javascript/dialog.js"></script>

	<script type="text/javascript" src="javascript/main.js"></script>
    




	
</body>
</html>