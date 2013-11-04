<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="utf-8"/>
	<meta name="author" content="powered by eljebus">

	<title>Mind On Cloud Sistemas Web</title>
	<meta name="description" content="Diseño y Desarrollo de Sistemas Web"> 
	 <meta name="viewport" content="width=device-width, initial-scale=1.0">
	
	
	
	<link rel="stylesheet" href="css/index.css" />
	


	
</head>
<body class="metrouicss">
	<div id='content-principal' class="body-text class="page"">
		<?
			include('views/header.html') ;
		?>

		<section id='slider' class="da-slider bg-color-gray">
			
				<div class="da-slide">
					<h2>Desarrollo Web</h2>
					<p>Nos especializamos en la creación de sitios web de alta calidad, con un diseño original y bien estructurado</p>
					<div class="da-img"><img src="images/paginas.png" alt="sitios web" /></div>
				</div>

				<div class="da-slide">
					<h2>Diseño Adaptable</h2>
					<p>adaptamos tu sitio para los mas nuevos dispositivos, tabletas, smartphones, laptops y pc's</p>
					
					<div class="da-img"><img src="images/celulares.png" alt="moviles" /></div>
				</div>

				<div class="da-slide">
					<h2>Aplicaciones </h2>
					<p>Sistemas de control y gestión para tu empresa o negocio, adaptados a tus necesidades</p>
					
					<div class="da-img"><img src="images/app.png" alt="aplicaciones" /></div>
				</div>

				
				
				
		
		</section>
		<section id='container-content'>
			<aside id='about'>
				<div class="bg-color-blue divisores ">
					<h2 class='inline 'style='color:white'>Mind  On Cloud</h2>
					<span class='icon icon-cloud-4 inline'></span>
				</div>
				<article class="body-text">
					<p>Somos un grupo de expertos que ofrecemos la asesoría y soporte para el desarrollo de herramientas para Internet, sistemas y aplicaciones que agilicen sus procesos. 
					</p>
					<p>
						Primero lo escuchamos para entender sus necesidades y ofrecer la solución ideal para el tamaño y requerimientos de su negocio.
					</p>

					<p>Ofrecemos servicios de consultoría, capacitación y desarrollo para ambientes web. </p>

					<p>Nuestra metodología de trabajo cumple al 100% con las necesidades y requerimientos de nuestros usuarios. </p>
					<p>Esto significa ahorro de tiempo y dinero. 
					Nos respalda la experiencia en el desarrollo de sistemas, a la vanguardia en tecnologías de información.</p>
				</article>
			</aside>
			<aside id='description'>
				<div class="bg-color-greenLight  divisores">
					<h2 class='inline 'style='color:white'>
						Nos Distingue
					</h2>
					<span class='icon icon-star inline'></span>
				</div>
				<article class="body-text">
					<P> Nuestro servicio y calidad nos distinguen</P>

					<ul id='distinciones'>
						<li>
							Contamos con experiencia trabajando para negocios de giros comercial, industrial, servicios y gobierno
						</li>
						<li>
							Desarrollamos herramientas ideales, sencillas y sólo lo necesario para cada cliente
						</li>
						<li>
							Nuestros proyectos llevan tecnología de última generación sin conocimientos avanzados
						</li>
						<li>
							Creamos desarrollos funcionales y con un diseño fácil, intuitivo y atractivo visualmente.
						</li>
						<li>
							Nos adaptamos a las necesidades de cada cliente, llevando cada paso del proyecto con el mayo dtalle para asegurarnos de cubrir las necesidades
						</li>
					</ul>
				</article>
			</aside>
		</section>

		<section id='container-divisors'>

			<figure  class='servicios image-container bg-color-greenLight'>
				<img src="images/int.jpg">
				<figcaption>
					<h3 style='color:black'>
						<strong>Desarrollo Integral</strong>
					</h3>
					<p>
						Nuestros servicios están orientados a satisfacer las necesidades tecnológicas de las PyMES y Micro-Empresarios
					</p>
				</figcaption>

			</figure>

			<figure  class='servicios image-container bg-color-orange' >
				<img src="images/aten.jpg">
				<figcaption >
					<h3 style='color:black'>
						<strong>Atención personalizada</strong>
					</h3>
					<p>
						Tenemos un equipo dedicado a tus necesidades, atendemos al cliente conforme lo requiera
					</p>
				</figcaption>

			</figure>

			<figure  class='servicios image-container bg-color-teal' style='margin-right:0'>
				<img src="images/ase.jpg" >
				<figcaption class="body-text">
					<h3 style='color:black'>
						<strong>Asesoría Profesional</strong>
					</h3>
					<p>
						Nuestro personal experimentado te dara siempre la mejor y más rapida solución cual sea tu caso
					</p>
					
				</figcaption>

			</figure>	

		</section>

		<footer class="nav-bar-inner bg-color-blue">
			 <h3 style='color:white;'>
			 	Mind On Cloud 2013<small> © Todos los derechos reservados</small>
			 </h3> 
		</footer>
	</div>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
	<script type="text/javascript" src="javascript/modernizr.custom.28468.js"></script>
	<script type="text/javascript" src="javascript/jquery.cslider.js"></script>
	<script type="text/javascript" src="javascript/dialog.js"></script>
	<script type="text/javascript" src="javascript/main.js"></script>
	<script type="text/javascript">
		$(function() {
		
			$('#slider').cslider({
				autoplay	: true,
				bgincrement	: 450,
				interval: 		8000

				
			});
		
		});
		$(document).on("ready", function(){
		
	
		$("#titulo").delay(4000).hide("slow");


		});
	
	</script>	
</body>
</html>