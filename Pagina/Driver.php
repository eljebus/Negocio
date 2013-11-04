<?session_start();

	class Driver
	{

		public static $modelo;
		public static $vista;
		public static $url;

		


		function render()
		{
			/*$this->url = explode("/", $_SERVER['REQUEST_URI']);
			$this->url = explode("d",$this->url[2]);
			

			$file=$this->url[1].".php";
			*/
			if(isset($_GET['vista']))
			{
				
				$file=$_GET['vista'].".html";
				$this->modelo="Modelos/".$file;
				$this->vista="views/".$file;
				if(file_exists($this->vista))
				{
					//include($this->modelo);
					$vista_interna=$this->vista;
					$hoja=$_GET['vista'];

				}
					
				else
				{
					header("HTTP/1.0 404 Not Found");
					include('noencontrado.html');
					exit(); 
				}
					
			}
			
			if($_GET['vista'])
				include("template.php");


			
				else
				{
					header("HTTP/1.0 404 Not Found");
					include('noencontrado.html');
					exit(); 
				}


					
			}

			

			

			// if(isset($_GET['vista']))
			// {
			// 	$file=$_GET['vista'].".php";
			// 	$this->vista="Vistas/".$file;
				
			// }
			
			// include($this->vista);
		
	


		
	}

	$driver=new Driver();
	$driver->render();

?>