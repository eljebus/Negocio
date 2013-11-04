$(document).on("ready", function(){

	   $('#menuInterno').change(direccionar);
		 $('#contact').click(dialog);
       });
  
  function dialog()
  {
      $.Dialog({
            'title'       : 'Contactanos',
            'content'     : "<div style='width:300px'><p>Contactanos para mas información</p><form id='formulario'><div class='input-control text'><input type='text' placeholder='Nombre'name='nombre' required /><button class='btn-clear'></button><input type='mail' placeholder='Mail' name='mail' required /><button class='btn-clear'></button></div><div class='input-control textarea' required><textarea></textarea></div><input type='submit' value='Enviar'/></form><br><center><address><p><strong>Mind On Cloud</strong><br>Iturbide #345 Jamay, Jalisco<br>Teléfono: 392 92 40798<br><small><a href='https://maps.google.com.mx/maps?q=itrbide+345+jamay+jalisco&hl=es-419&ll=20.293355,-102.704594&spn=0.011774,0.021136&sll=19.442802,-99.246163&sspn=0.023674,0.042272&hnear=Iturbide+345,+Jamay,+Jalisco&t=m&z=16'target='blank'>Ver Mapa</a></small></p></address></center></div>",
            'draggable'   : true,
            'overlay'     : true,
            'closeButton' : true,
            'buttonsAlign': 'right',
            'keepOpened'  : false,
            'position'    : {
                'zone'    : 'center'
            },
            'buttons'     : ''
           });
  }
	function direccionar()
  {

    if($(this).val()!='Contacto')
       window.location = $(this).val();
    else
      dialog();

  }


		window.___gcfg = {lang: 'es-419'};

  (function() {
    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
    po.src = 'https://apis.google.com/js/plusone.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
  })();

