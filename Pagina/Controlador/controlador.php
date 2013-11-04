<?php
	abstract class Gestor
	{
		protected $resource;
		public abstract function conectar($host,$user,$pass,$dbase);
		//Obtiene el número del error  
	    public abstract function getErrorNo();  
	    //Obtiene el texto del error  
	    public abstract function getError();  
	    //Envía una consulta  
	    public abstract function query($q);  
	    //Convierte en array la fila actual y mueve el cursor  
	    public abstract function fetchArray($resource);  
	    //Comprueba si está conectado  
	    public abstract function isConnected();  
	    //Escapa los parámetros para prevenir inyección  
	    public abstract function escape($var);  
		
	}

	class MysqlGestor extends Gestor
	{
		public function conectar($host, $user, $pass, $dbname)
		{  
	        $this->conexion = new mysqli($host, $user, $pass, $dbname);  
	        if ($this->conexion->connect_errno) {
			    printf("Connect failed: %s\n", $this->conexion->connect_error);
			    exit();
			}
	        return  $this->conexion;  
    	}  
	    public function getErrorNo()
	    {  
	        return mysqli_errno($this->conexion);  
	    }  
	    public function getError()
	    {  
	        return mysqli_error($this->conexion);  
	    }  
	    public function query($query)
	    {  
	        return mysqli_query($this->conexion,$query);  
	    }  
	    public function fetchArray($resultado)
	    {  
	        return mysqli_fetch_array($resultado);  
	    }  
	    public function isConnected()
	    {  
	        return !is_null($this->conexion);  
	    }  
	    public function escape($var)
	    {  
	        return mysqli_real_escape_string($this->resource,$var);  
	    }  

	}
		
?>