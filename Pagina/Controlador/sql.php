<?php

	include('controlador.php');
	include('Consulta.php');

	class Conexion extends Consulta
	{
		private $conexion;
		function __construct($host,$user,$pass,$dbase)
		{

			
			try
			{
				$this->conexion=new MysqlGestor();
				 $this->conexion->conectar("localhost","gasolcom","gasol&gasol","gasolcom_Gasol");
				
				//$this->conexion->conectar($host,$user,$pass,$dbase);

			}

			catch(Exception $e)
			{
				echo $this->conexion->getError();
			}

		}


		

		function execute()
		{
			try
	 		{	
	 			// echo parent::$ejecucion;
	 			$consulta=parent::$ejecucion;
				$query=$this->conexion->query($consulta);
				parent::$ejecucion="";
				return $query;
	 		}
	 		catch(Exception $e)
	 		{
	 			echo $e;
	 			return false;
	 		}

		}
		function datos($consulta)
		{
			
			$renglones=$consulta->num_rows;
			if($renglones>0)
			{	

				$retorno=mysqli_fetch_array($consulta);

			}
			else
				$retorno="<p style='font-size:16px'>Ningun Registro</p>";
			
			return $retorno;
		}

	


		function render($render,$consulta)
		{

			
			$renglones=$consulta->num_rows;
			if($renglones>0)
			{
				
				while($array = $consulta->fetch_assoc())
				{
					$resultado=$render;
					foreach ($array as $clave=>$valor) 
					{
					
						
						$resultado=str_replace('{'.$clave.'}', $valor, $resultado);
						
					}
					$retorno.=$resultado;
					
				}
				


				
			}

			else
				$retorno=""
;				// $retorno="<p style='font-size:16px'>Ningun Registro</p>";
			
			return $retorno;
		}

	}

?>