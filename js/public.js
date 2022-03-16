/*

	Plugin Name: Acuarel Slider de Noticias
	Plugin URI: http://www.acuarel.es
	Description: JS Front End
	Author: Daniel Prol ( aka Cancrexo ) para Acuarel
	Version: 1.0
	Author URI: http://www.acuarel.es


*/

var ACUAREL_SLIDER_NOTICIAS = window.ACUAREL_SLIDER_NOTICIAS || {};



ACUAREL_SLIDER_NOTICIAS.log = function(str){
	if (!window.console);
	else	console.log(str);
}


/*	Check para evitar errores en navegadores sin consola ou que a teñan deshabilitada*/
if( typeof console === "undefined" ) {
    console = {
        log: ACUAREL_SLIDER_NOTICIAS.log,
        debug: ACUAREL_SLIDER_NOTICIAS.log,
     };
}

// Sacado de stackoverflow e de http://jsfiddle.net/4mTMw/8/
ACUAREL_SLIDER_NOTICIAS.marquesina = function(){
	jQuery(function($){

		if( ! $('div.marquesina'))return;

		var marquee = $('div.marquesina');

		marquee.each(function() {
			var mar = $(this),indent = mar.width();
			mar.marquee = function() {
				indent--;
				mar.css( 'text-indent', indent );
				if ( indent < -1 * mar.children( 'div.marquesina-texto' ).width() ) {
					indent = mar.width();
				}
			};
			mar.data( 'interval',setInterval( mar.marquee, ACUAREL_SLIDER_VARS.velocidad));
		});

	});
}


/*
	Evento onload
*/
jQuery( document ).ready( function( $ ) {


	ACUAREL_SLIDER_NOTICIAS.marquesina ();


});

