<?

	class Consulta
	{

		public static $ejecucion;
		public static $filtro;
		public static $campos;
	

	
		
		public static $sentencias= array("Seleccionar"=>"SELECT ",
								  "donde"=>" WHERE ",
								  "insertar"=>" INSERT INTO ",
								  "ignorar"=>" INSERT IGNORE INTO ",
								  "valores"=>" VALUES ",
								  "actualizar"=>"UPDATE ",
								  "limite"=>" LIMIT ",
								  "orden"=>" ORDER BY ",
								  "join"=>" INNER JOIN ",
								  "distinto"=>"SELECT DISTINCT ",
								  "entre"=>" BETWEEN ",
								  "patron"=>" LIKE ",
								  "dentro"=>" IN ",
								  "borrar"=>"DELETE FROM "
							
									);


		

//funciones para consultas.............................................
		public function select($table,$campos,$where)
		{
			
			self::$ejecucion=self::$sentencias["Seleccionar"];
			self::separador($campos,",");
			self::$ejecucion.=$this->campos." from ".$table;
			if(is_array($where))
			{
				self::$ejecucion.=self::$sentencias["donde"];
				self::asociar($where," AND ");
			}
				
			
		}

		public function distinto($table,$campos,$where)
		{
			
			self::$ejecucion=self::$sentencias["distinto"];
			self::separador($campos,",");
			self::$ejecucion.=$this->campos." from ".$table;
			if(is_array($where))
			{
				self::$ejecucion.=self::$sentencias["donde"];
				self::asociar($where," AND ");
			}
				
			
		}

		public function date($fecha,$fecha1,$fecha2,$inicio)
		{
				if($inicio==true)
					self::$ejecucion.=self::$sentencias["donde"];
				else
					self::$ejecucion.=" AND ";
				self::$ejecucion.=$fecha;
				self::$ejecucion.=self::$sentencias["entre"];
				self::$ejecucion.=$fecha1;
				self::$ejecucion.=" AND ";
				self::$ejecucion.=$fecha2;

		}

		public function like($campo,$patron,$comienzo)
		{
			if($comienzo==true)
				self::$ejecucion.=self::$sentencias["donde"];
			else
				self::$ejecucion.=" AND ";
			self::$ejecucion.=$campo;
			self::$ejecucion.=self::$sentencias["patron"];
			self::$ejecucion.=$patron;
		}


		public function join($table,$table2,$campos,$camposjoin,$where)
		{
			
			self::$ejecucion=self::$sentencias["Seleccionar"];
			self::asociarjoin($campos);
			self::$ejecucion.=$this->campos." from ".$table.self::$sentencias["join"].$table2;
			self::$ejecucion.=" ON ".$camposjoin[0]." = ".$camposjoin[1];
			if(is_array($where))
			{
				self::$ejecucion.=self::$sentencias["donde"];
				self::asociar($where," AND ");
			}
				
			
		}

		function delete($tabla,$where)
		{
			self::$ejecucion.=self::$sentencias['borrar'];
			self::$ejecucion.=$tabla;
			if(is_array($where))
			{
				self::$ejecucion.=self::$sentencias["donde"];
				self::asociar($where," AND ");
			}

		}

		function whereINot($campo,$datos,$bandera)
		{
			if($bandera==true)
				self::$ejecucion.=self::$sentencias["donde"];
			else
				self::$ejecucion.=" AND ";	

			self::$ejecucion.=$campo;
			self::$ejecucion.=" NOT".self::$sentencias["dentro"]."(";
			self::separadorCampos($datos,",");
			self::$ejecucion.=")";

		}


		function insert($tabla,$campos)
		{
			self::$ejecucion=self::$sentencias["insertar"].$tabla." (";
			self::setinsert($campos);
			self::$ejecucion.=")";
			self::$ejecucion.=self::$sentencias["valores"]." (";
			self::separador($campos,",");
			self::$ejecucion.=")";
		}

		function insertIgnore($tabla,$campos)
		{
			self::$ejecucion=self::$sentencias["ignorar"].$tabla." (";
			self::setinsert($campos);
			self::$ejecucion.=")";
			self::$ejecucion.=self::$sentencias["valores"]." (";
			self::separador($campos,",");
			self::$ejecucion.=")";
		}

		function update($table,$campos,$where)
		{
			self::$ejecucion=self::$sentencias["actualizar"].$table." SET ";
		
			self::asociar($campos,",");
			
			if(is_array($where))
			{
				self::$ejecucion.=self::$sentencias["donde"];
				self::asociar($where," AND ");
			}
		}


		function limit($limites)
		{
			
			self::$ejecucion.=self::$sentencias["limite"];
			self::$ejecucion.=self::separador($limites,",");

		}

		function order($campos,$orden)
		{

			self::$ejecucion.=self::$sentencias["orden"];
			if(is_array($campos))
				self::$ejecucion.=self::separador($campos,",");
			else
				self::$ejecucion.=$campos;
			self::$ejecucion.=" ".$orden;

		}


		function setinsert($campos)
		{


			$ultimo = sizeof($campos);
			$contador=1;

			foreach ( $campos as $col => $valor ) 
			{
    
			    if ( $ultimo == $contador )
			    {

			       $campos_valores.=$col." ";
			    } 
			    else 
			    {
			       $campos_valores.=$col.",";
			    }
			    $contador++;
			    
			}
			self::$ejecucion.=$campos_valores;
		}



		function query($query)
		{
			
			self::$ejecucion=$query;
		}

		function whereIn($campo,$datos,$bandera)
		{
			if($bandera==true)
				self::$ejecucion.=self::$sentencias["donde"];
			else
				self::$ejecucion.=" AND ";	

			self::$ejecucion.=$campo;
			self::$ejecucion.=self::$sentencias["dentro"]."(";
			self::separadorCampos($datos,",");
			self::$ejecucion.=")";

		}

		


//fin de funciones para consultas..................................


//funciones generales...............................................
		public static function separador($campos,$separador)
		{
			$ultimo = sizeof($campos);
			$contador=1;

		
			foreach ( $campos as $col => $valor ) 
			{
    
			    if ( $ultimo == $contador )
			    {
			       $campos_comas.=$valor.' ';
			    } 
			    else 
			    {
			        $campos_comas.=$valor.$separador;
			    }
			    $contador++;
			    
			}
			self::$ejecucion.=$campos_comas;
		}

		public static function separadorCampos($campos,$separador)
		{
			$ultimo = sizeof($campos);
			$contador=1;

		
			foreach ( $campos as $col => $valor ) 
			{
    
			    if ( $ultimo == $contador )
			    {
			       $campos_comas.="'$valor'".' ';
			    } 
			    else 
			    {
			        $campos_comas.="'$valor'".$separador;
			    }
			    $contador++;
			    
			}
			self::$ejecucion.=$campos_comas;
		}


		function asociarjoin($campos)
		{
			$ultimo = sizeof($campos);
			$contador=1;

			foreach ( $campos as $col => $valor ) 
			{
    
			    if ( $ultimo == $contador )
			    {

			       $campos_valores.=$col." AS ".$valor.' ';
			    } 
			    else 
			    {
			       $campos_valores.=$col." AS ".$valor.',';
			    }
			    $contador++;
			    
			}
			self::$ejecucion.=$campos_valores;

		}




		function asociar($campos,$separador)
		{
			$ultimo = sizeof($campos);
			$contador=1;

			foreach ( $campos as $col => $valor ) 
			{
    
			    if ( $ultimo == $contador )
			    {

			       $campos_valores.=$col."=".$valor.' ';
			    } 
			    else 
			    {
			       $campos_valores.=$col."=".$valor.$separador;
			    }
			    $contador++;
			    
			}
			self::$ejecucion.=$campos_valores;

		}

//fin funciones generales.................................................



		
	}

?>