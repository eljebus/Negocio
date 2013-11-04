$(document).ready(function(){
	


var ancho=0;
	var posicion = new Array();

	$('#paginas .divisores').each(function(i){

		posicion[i]= ancho;
		ancho += $(this).width();

		

		if(!$(this).width())
		{
			alert("por favor dimensiona tus imagenes");
			return false;
		}
	});

	$('#paginas').width(ancho);

$('#menu1 ul li a').click(function(e){

	
		$('#menu1 ul li.menuItem').removeClass('act').addClass('inact');
		$(this).parent().addClass('act');

		var pos = $(this).parent().prevAll('.menuItem').length;
		

		$('#paginas').stop().animate({marginLeft:-posicion[pos]+'px'},500);


		e.preventDefault();
		
	});
		


		function autoAvance()
	{
		if(actual==-1) return false;
		
		$('#menu1 ul li a').eq(actual%$('#menu1 ul li a').length).click();
		actual++;
			
		
	}

});