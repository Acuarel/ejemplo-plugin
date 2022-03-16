/*

	Plugin Name: Acuarel Slider de Noticias
	Description: JS Back End
	Author: Daniel Prol ( aka Cancrexo ) para Acuarel
	Version: 1.0
	Author URI: http://www.acuarel.es


*/





var SLIDER_NOTICIAS_ADMIN = window.SLIDER_NOTICIAS_ADMIN || {};



SLIDER_NOTICIAS_ADMIN.log = function(str){
	if (!window.console);
	else	console.log(str);
}



/*	Check para evitar errores en navegadores sin consola ou que a teñan deshabilitada*/
if( typeof console === "undefined" ) {
    console = {
        log: SLIDER_NOTICIAS_ADMIN.log,
        debug: SLIDER_NOTICIAS_ADMIN.log,
     };
}


SLIDER_NOTICIAS_ADMIN.valida = function(){
jQuery(function($){

	queRadio =  parseInt( $( 'input[name=tipo]:checked' ).val(), 10 );

	if(queRadio == 1){
		// Slide de texto especifico
		if ( !$.trim( $( "#texto_slider" ).val() ) ) {
			alert("Debes introducir un texto");

		}else $("#SJB_update_form").submit();

	}else{
		// Slide de entradas
		var n = parseInt( $("#n_noticias").val(), 10);

		if(  isNaN ( n ) ||  n <=0){
			alert("Debes introducir un valor entero positivo");

		}else $("#SJB_update_form").submit();

	}


	return false; // Cascou aljo

});
}



SLIDER_NOTICIAS_ADMIN.controlFieldset = function(){

	jQuery( 'input[name=tiposcroll]' ).each(function( i, obj ){
		console.log( "i :  " + i);
		console.log( jQuery( this ).is( ":checked" ));

		if( !jQuery( this ).is( ":checked" ) )
			jQuery( this ). parent().parent().find(".contido").slideUp();
		else
			jQuery( this ). parent().parent().find(".contido").slideDown();
	});

}


jQuery( document ).ready( function( $ ) {

	//queRadio =  parseInt( $( 'input[name=tipo]:checked' ).val(), 10 );

	//$( 'input[name=tiposcroll]:checked' ).parent().parent().find(".contido").slideToggle();


	SLIDER_NOTICIAS_ADMIN.controlFieldset();

	$( 'input[name=tiposcroll]' ).click(function(){
		SLIDER_NOTICIAS_ADMIN.controlFieldset()
	});


	// jQuery UI
	 $( "#slider-velocidad" ).slider({
		  value:ACUAREL_SLIDER_VARS.velocidad,
		  min: 1,
		  max: 20,
		  slide: function( event, ui ) {
			$( "#velocidad_s" ).text( ui.value +"ms");
			$( "#velocidad" ).val( ui.value );
		  }
    });

	 $( "#slider-letra" ).slider({
		  value:ACUAREL_SLIDER_VARS.tam_fuente,
		  min: 10,
		  max: 60,
	  	  step: 1,
		  slide: function( event, ui ) {
			$( "#tam_fuente_s" ).text( ui.value +"px");
			$( "#tam_fuente" ).val( ui.value );
		  }
    });


	 $( "#slider-offset" ).slider({
		  value:ACUAREL_SLIDER_VARS.offsetslider,
		  min: 0,
		  max: 40,
	  	  step: 1,
		  slide: function( event, ui ) {
			$( "#offsetslider_s" ).text( ui.value +"px");
			$( "#offsetslider" ).val( ui.value );
		  }
    });

console.log("v:" + ACUAREL_SLIDER_VARS.velocidad);

});