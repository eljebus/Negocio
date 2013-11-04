<?

	include('sql.php');

	$conexion=new Conexion('localhost','root','6374',"marker");
	
	$campos = array("nombre"=>"'foo'", 'apellido'=>"'bar'", 'domicilio'=>"'hallo'", 'Municipio'=>"world");
	$campos_where = array("foo"=>1, "bar"=>0);
	$conexion->Update ("Usuarios",$campos,true,$campos_where);
	$conexion->execute();


			 

?>