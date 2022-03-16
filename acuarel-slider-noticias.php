<?php
/*

	Plugin Name: Acuarel Slider de Noticias
	Plugin URI: http://www.acuarel.es
	Description: Crea caixa con scroll horizontal de noticias
	Author: Daniel Prol ( aka Cancrexo ) para Acuarel
	Version: 1.0
	Author URI: http://www.acuarel.es


*/

defined(  'ABSPATH'  ) OR exit;


register_activation_hook(    __FILE__, array(  'Acuarel_Slider_Noticias', 'on_activation'  )  );

register_deactivation_hook(  __FILE__, array(  'Acuarel_Slider_Noticias', 'on_deactivation'  )  );

register_uninstall_hook(     __FILE__, array(  'Acuarel_Slider_Noticias', 'on_uninstall'  )  );

add_action(  'plugins_loaded', array(  'Acuarel_Slider_Noticias', 'getInstance'  )  );



class Acuarel_Slider_Noticias{


	// Creamos instancia
    protected static $instance;

	const
		DS = DIRECTORY_SEPARATOR,
		SELECTED = 'selected="selected"',
		CHECKED  = 'checked="checked"',
		DISABLED = 'disabled="disabled"',
		READONLY = 'readonly="readonly"';


	// singleton
    public static function getInstance(  ){
		is_null(  self::$instance  ) AND self::$instance = new self;
		return self::$instance;
    }



	/*
		Constructor
	*/
	public function __construct(  ){


		//Indicamos si este plugin vai ter unha paxina configuración
		$necesitaConfig = 1;


		$this->pluginpath = plugins_url(  '' , __FILE__  ); //WITHOUT the trailing slash )


		$this->plugindir = plugin_dir_path(  __FILE__ ); // WITH trailing slash


		$this->AcuarelMenuGroup = NULL;

		/*
			Titulo de paxin IMPORTANTISIMO -->xenerase slug
		*/

		$this->title= "Acuarel Slider Noticias";

		/*
			Slug de este plujin
		*/
		$this->slug = sanitize_title(  $this->title ); // Que será como o menu_title pero pero preparado para slug: minusculas e - en vez de espacios


		/*
			Base para variables e demáis -->reemplazar - por _ en slug
		*/
		$this->noslug =str_replace( "-","_", $this->slug );


		/*
			pilas de error e exito
		*/
		$this->SJB_Errores = new WP_Error(  );
		$this->SJB_Mesaxes = new WP_Error(  );

		// Traducciones .po
		$exito = load_plugin_textdomain( $this->slug , false, $this->slug . '/languages/'   ); // en  este_plugin/languages
		//var_dump( $exito );



		/*
			Creamos punto de entrada á paxina de configuración do noso plugin, SEMPRE que a necesites
			Comproba si existe menu de plugins NOSO e creao si non o hai
			Chamamos á clase que se encarga de faser esto
		*/
		if( $necesitaConfig ){

			if( !class_exists( "Acuarel_Plugins_Menu_Group" ) &&  file_exists( dirname(  __FILE__  ).'/classes/acuarel-plugins-menu-group.php' ) )
				include_once dirname(  __FILE__  ).'/classes/acuarel-plugins-menu-group.php';

			if(  !$this->AcuarelMenuGroup || is_a( $this->AcuarelMenuGroup, 'WidgetFactory' ) ) {


				$this->AcuarelMenuGroup = new Acuarel_Plugins_Menu_Group;


				add_action( 'admin_menu', array( $this->AcuarelMenuGroup , 'My_plugins_menu_check' ) );
			}


			// Metemos o plugin colgando ONDE TOQUE: paxina de ajustes OU un menu propio
			add_action( 'admin_menu', array( &$this, 'add2Menu' ) );

			/*
				Empatamos scripts:
				Para sjb-plugins-menu toplevel_page_sjb-plugins-menu
				admin_print_scripts + ( slug de sjb root menu ) +  page_ + ( slug do metodo que se engadeu a MyMenu )
				O rollo e saber o slug do método. Ese definese en add2Menu pero collemos o slug de este plujin.OLLO!
			*/

			if( $this->AcuarelMenuGroup ){

				// Sacado de http://wordpress.stackexchange.com/questions/41207/how-do-i-enqueue-styles-scripts-on-certain-wp-admin-pages

				add_action( 'load-' . $this->AcuarelMenuGroup->slug .'_page_' .$this->slug, array( &$this, 'add_admin_scripts' ) );

			}else{
				// Na paxina de settings
				//admin_print_scripts + ( slug de sjb root menu ) +  page_ + ( slug do metodo que se engadeu a MyMenu )
				add_action( "admin_print_scripts-settings_page_".$this->slug ,array( &$this, 'add_admin_scripts' ) );
				//error_log( "La cagamos Luis" );
			}
		}


		/*
			Engade scripts e css pero só no frontend
		*/
		add_action( 'wp_enqueue_scripts', array( &$this, 'add_public_scripts' ) );



		/*
			E os shortcodes
		*/
		add_shortcode( 'acuarel-slider-noticias', array( &$this, 'shortcode' ) );
		add_shortcode( $this->slug ."-show", array( &$this, 'shortcode' ) );



		/*
			Rexistra clase widget
		*/
		//add_action( 'widgets_init', array( &$this, 'registraWidgetClass' ) );





    }// __construct





	/*******************************************************************************************
		Engade unha entrada no sistema de menus para este plugin, onde lle indiques
	*******************************************************************************************/
	public function add2Menu(  ) {

		// Engade entrada para acceder a este plugin onde lle dijas.
		$menu_slug = $this->slug;

		if( $this->AcuarelMenuGroup ){
			// A pantalla de configuracion de este plugin será unha subopción dentro do noso menu
			//error_log( "add2Menu 2a" );
			//error_log( var_export( $this->AcuarelMenuGroup, true ) );
			$perchero = $this->AcuarelMenuGroup->slug;

		}else{
			// A pantalla de configuracion de este plugin será unha subopción dentro de axustes
			// http://www.paulund.co.uk/add-menu-item-to-wordpress-admin
			//error_log( "add2Menu 2b" );
			$perchero = "options-general.php";
		}

		//error_log( "Perchero" );
		//error_log( $perchero );
		//add_submenu_page(  $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function  );
		add_submenu_page( $perchero, "Gestionar ". $this->title, $this->title, "edit_pages", $menu_slug, array( &$this,"do_peich_admin" ) );

	}




	/*******************************************************************************************
		Activacion.
		Basicamente crear opcións por defecto de plugin
	*******************************************************************************************/
    public static function on_activation(  ){

        if (  ! current_user_can(  'activate_plugins'  )  ) return;

		$plugin = isset(  $_REQUEST['plugin']  ) ? $_REQUEST['plugin'] : '';

		check_admin_referer(  "activate-plugin_{$plugin}"  );


		if(  !get_option(  "plujin_options" )  ) {
			// Creamos opcións de plugin.
			$plujin_options = array(
				"tiposcroll"		=>0,
				"id_categoria"		=> 1,
				"n_noticias"		=>3,
				"texto_slider"		=>"Bienvenido a nuestra web....",
				"velocidad"			=>5,
				"tam_fuente"		=>40,
				"offsetslider"		=> 0,
				"codyfont"			=>1
			 );

			update_option(  "plujin_options", $plujin_options ); // Serializado!
		}

    } // on_activation eof



	/*******************************************************************************************
		Desactivación
	*******************************************************************************************/
    public static function on_deactivation(  ){

        if (  ! current_user_can(  'activate_plugins'  )  )
            return;

		$plugin = isset(  $_REQUEST['plugin']  ) ? $_REQUEST['plugin'] : '';

		check_admin_referer(  "deactivate-plugin_{$plugin}"  );

        # Uncomment the following line to see the function in action
         #var_dump(  $_GET  ) ;

    }// on_deactivation eof




	/*******************************************************************************************
		Desinstalar
	*******************************************************************************************/
    public static function on_uninstall(  ){

		if (  ! current_user_can(  'activate_plugins'  )  )
			return;

		check_admin_referer(  'bulk-plugins'  );

        // Important: Check if the file is the one
        // that was registered during the uninstall hook.
        if (  __FILE__ != WP_UNINSTALL_PLUGIN  )
			return;

        # Uncomment the following line to see the function in action
        #exit(  var_dump(  $_GET  )  );

		//elete_option(  'pontenciencia_options'  );

    } // on_uninstall end






	/******************************************************************************************
		Engadimos JS e CSS an Backend ( pero só cando entramos no apartado deste plugin!!! )
	******************************************************************************************/
	function add_admin_scripts( $meuHook ){

		// Podemos meter todos os arquivos js que queiramos aquí
		//wp_enqueue_script( 'jquery' );

		// Fixate que non usamos wp_register_script, p.e. :
		//wp_register_script( 'pretyphotosjb', dirname(  get_bloginfo( 'stylesheet_url' ) )  . '/prettyPhoto/js/jquery.prettyPhoto.js', array( 'jquery' ) );

		wp_enqueue_script( $this->slug . '-admin', plugins_url( '/js/admin.js', __FILE__ ), array( 'jquery' ), '2.0', true );
		wp_enqueue_script( $this->slug . '-jqueryui', ( '//code.jquery.com/ui/1.11.4/jquery-ui.js' ), array( 'jquery' ),  '1.11.4', true );

		wp_enqueue_style( $this->slug . '-jqueryui', ( '//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css' ) );

		wp_enqueue_style( $this->slug . '-base', plugins_url( '/css/admin.css', __FILE__ ) );

		// Pasamos datos a javascript
		$plujin_options = get_option(  $this->noslug .'_options'  );   // array

 		wp_localize_script( $this->slug . '-admin', 'ACUAREL_SLIDER_VARS',
			array(
				'velocidad'		=>intval( $plujin_options["velocidad"] ),
				'tam_fuente'	=>intval(  $plujin_options["tam_fuente"] ),
				"offsetslider" => intval( $plujin_options["offsetslider"] )
			 )
			 );


	}// add_admin_scripts end




	/******************************************************************************************
		CSS e JS en parte pública. :
	******************************************************************************************/

	  function add_public_scripts(  ) {

		//wp_register_style(  $handle, $src, $deps, $ver, $media  )
		wp_register_style(  $this->slug . '-fonts', '//fonts.googleapis.com/css?family=Codystar&subset=latin,latin-ext', array(  ), '20150616', 'all'  );
		wp_register_style(  $this->slug . '-public', plugins_url( '/css/public.css', __FILE__ ), array( $this->slug . '-fonts' ), '20150417', 'all'  );
		wp_enqueue_style( $this->slug . '-fonts' );
		wp_enqueue_style( $this->slug . '-public' );

		wp_register_script(  $this->slug . '-public', plugins_url(  '/js/public.js', __FILE__  ), array( 'jquery') );
		wp_enqueue_script( $this->slug . '-public' );


		// Pasamos datos a javascript usando ametodo wp_localize
		$plujin_options = get_option(  $this->noslug .'_options'  );   // array

 		wp_localize_script( $this->slug . '-public', 'ACUAREL_SLIDER_VARS',
			array(
				'velocidad'		=>intval( $plujin_options["velocidad"] ),

				'tam_fuente'	=>intval(  $plujin_options["tam_fuente"] ),

				"offsetslider"		=> intval( $plujin_options["offsetslider"] )
			 )
			 );
	}






	/*******************************************************************************************
		Paxina principal do plujin
	*******************************************************************************************/
	public function do_peich_admin(  ){

		global $wpdb;

		$this->showAdmin(  ); //Amosa o momento sorpresa actual e da a opción de editalo ( provisional )
	}


	/*******************************************************************************************
		Cabecera html das páxinas do plugin
	*******************************************************************************************/
	private function cabeceraAdmin(  ){


		$salida = '<div class="wrap sjb ">';
		$salida .= '<h1>'.__( "Acuarel Slider Noticias", $this->slug ) .'</h1>';

	 	// Si hai erros, sacamolos por pantallla
		$pila = $this->SJB_Errores ->get_error_messages(  );
		foreach( $pila as $mesgError )$salida .= '<div class="error sjb-msgBox"><p><strong>'. $mesgError .'</strong></p></div>';

		// Idem si fas aljo ben!
		$pila = $this->SJB_Mesaxes->get_error_messages(  );
		foreach( $pila as $mesaxe )$salida .= '<div class="updated sjb-msgBox"><p><strong>'. $mesaxe .'</strong></p></div>';

		return $salida;
	}




	/*******************************************************************************************
		Footer html das páxinas do plugin COMUN
	*******************************************************************************************/
	private function footerAdmin(  ){
		$salida = '<div class="clear"></div></div>  <!-- Fin wrap -->';
		return $salida;
	}


	/*******************************************************************************************
		Amosa a paxina de xestión do plugin
	*******************************************************************************************/
	public function showAdmin(  ){

		if ( headers_sent( $filename, $linenum ) ) {
			//echo "Headers already sent in $filename on line $linenum" ;
		}
		global $wpdb;

		$wpdb->show_errors(  );


		/*
			Si estas actualizando:
		*/

		if( isset( $_GET['action'] ) && $_GET["action"] == "update"  ){


			if( wp_verify_nonce(  $_POST["slider_noticias_nonce"], "actualizar_acuarel_slider_noticias"  )  ){

				$plujin_options = array(
					"tiposcroll"			=> intval( $_POST["tiposcroll"] ),
					"id_categoria"		=> intval( $_POST["id_categoria"] ),
					"n_noticias" 		=> intval( $_POST["n_noticias"] ),
					"texto_slider"		=> sanitize_text_field( $_POST["texto_slider"] ),
					"velocidad"			=> intval( $_POST["velocidad"] ),
					"tam_fuente"		=> intval( $_POST["tam_fuente"] ),
					"codyfont"			=> intval( $_POST["codyfont"] )	,
					"offsetslider"		=> intval( $_POST["offsetslider"] )

				 );

				update_option( $this->noslug .'_options', $plujin_options ); // Serializado!

				wp_redirect( admin_url( 'admin.php?page='. $this->slug . '&update=OK' ) );

				die(  );

			}
			die( "Error de seguridad" );


		}


		if( isset( $_GET['update'] ) && $_GET["update"] == "OK" )
			$this->SJB_Mesaxes->add( 'update_ok', __( "Configuracion actualizada correctamente.", $this->slug ) );


		$salida = $this->cabeceraAdmin(  ) ;

		$salida .= '<h2>'.__( "Configuración general",$this->slug ) .'</h2>';


		// imos

		$salida .= '<form id="SJB_update_form" name="SJB_update_form" method="post"  class="ancho1 top1 sjb-form"  action="'. admin_url( 'admin.php?page='. $this->slug.'&action=update&noheader=true' ) .'">';

		// Nonce pa sejuridade
		$salida .= wp_nonce_field( "actualizar_acuarel_slider_noticias", "slider_noticias_nonce", false, false  );

		// Cargar opcions
		$plujin_options = get_option(  $this->noslug .'_options'  );  // array


		// Categorias de post das cales facer o slider
		$categorias = $this->dameCategorias();
		$select  = "";

		// Opción por defecto: scroll de entradas

		// Dar opción de coller ou ben un slider co titulo das entradas de certa categoría ou ben un texto específico
		$salida .=sprintf('<p>%s</p>', __('Puedes escoger entre hacer un scroll de texto con el título de ciertas entradas o bien con un texto específico.', $this->slug ) );



		$salida .='<fieldset><legend><em>Scroll</em> con título de  entradas. <input type="radio" name="tiposcroll"  value="0" '.( $plujin_options["tiposcroll"] != 1 ? self::CHECKED : "" ) .'/></legend><div class="contido">';



		foreach($categorias as $k=>$categoria){
			$select .='<option value="'. $categoria->term_id .'"'. ($plujin_options["id_categoria"]  ==$categoria->term_id ? self::SELECTED : '') .'>'. $categoria->name  .'</option>';
		}
		$salida .='<h3>'.__( "Categoría", $this->slug ) .'</h3>';
		$salida .= '<select name="id_categoria" id="id_categoria">'. $select .'</select>';

		$salida .='<h3>'.__( "Nº de noticias a mostrar", $this->slug ) .'</h3>';
		$salida .='<input type="text" class="numero entero" id="n_noticias" name="n_noticias" value="'.$plujin_options["n_noticias"] .'" />';

		$salida .='</div></fieldset>';


		// Scroll de texto fijo
		$salida .='<fieldset><legend><em>Scroll</em> con texto definido <input type="radio" name="tiposcroll" value="1" '.( $plujin_options["tiposcroll"] == 1 ? self::CHECKED : "" ) .'/></legend><div class="contido">';
		$salida .='<h3>'.__( "Texto", $this->slug ) .'</h3>';
		$salida .='<textarea class="texto" id="texto_slider" name="texto_slider">'. $plujin_options["texto_slider"] .'</textarea>';
		$salida .='</div></fieldset>';



		// comuns
		$salida .='<fieldset><legend>Parámetros comunes</legend>';

		$salida .='<h3><input name="codyfont" type="checkbox" value="1" '.( $plujin_options["codyfont"] == 1 ? self::CHECKED : "" ).' /> '.__( "Usar Fuente Google", $this->slug ) .'</h3><br>';


		$salida .='<h3>'.__( "Velocidad scroll", $this->slug ) .'<input name="velocidad" id="velocidad" type="hidden" value="'.$plujin_options["velocidad"].'" /> <span id="velocidad_s" >'.$plujin_options["velocidad"].' ms.</span></h3><div class="slider-acuarel" id="slider-velocidad"></div>';

		$salida .='<h3>'.__( "Tamaño de fuente", $this->slug ) .'<input name="tam_fuente" id="tam_fuente"  type="hidden" value="'.$plujin_options["tam_fuente"].'" /> <span id="tam_fuente_s" >'.$plujin_options["tam_fuente"].'px</span></h3><div class="slider-acuarel" id="slider-letra"></div>';

	$salida .='<h3>'.__( "Offset Y", $this->slug ) .'<input name="offsetslider" id="offsetslider" type="hidden" value="'.$plujin_options["offsetslider"].'" /> <span id="offsetslider_s" >'.$plujin_options["offsetslider"].' px.</span></h3><div class="slider-acuarel" id="slider-offset"></div>';



		 $salida .= '
        <!-- Botón de grabar cambios e de cancelar ( amosansense onchange ) -->
       <p class="submit" style="text-align:center; margin-top:1em;">
       <input class="botonUpdate" type="button"  value="'. __( 'Actualizar', $this->slug  ) .'" onclick="javascript:SLIDER_NOTICIAS_ADMIN.valida(  )" />
	   <input type="button"  class="botonEnvio sjb-button" value="'.__( 'Cancelar', $this->slug  ) .'" onclick="javascript:document.location.href=\''.admin_url( 'admin.php?page='.$this->slug ) .'\'"/></p> </form>';

		// Fin da paxina de adminsitracoion
   		$salida .= $this->footerAdmin(  );

		echo $salida;
		//var_dump( $dismaboc_configuracion_carta );
	}// momento_sorpresa_menu eof





	/*
		Shortcode para o slider
		@string	| html
	*/
	function shortcode( $atts,  $content = NULL ){

		//En $atts tes os posibles parametros. P.e. $salida = $this->dameNoticias();

		$plujin_options = get_option(  $this->noslug .'_options'  );  // array

		$noticias = $this->dameNoticias();

		if( $plujin_options["tiposcroll"] == 1){
			// texto fijo

			if( ($texto = $plujin_options["texto_slider"] ) && !$texto )
				$texto = __("No se ha definido un texto para el slider.", $this->slug);

		}else{
			// scroll de novas

			if( count( $noticias ) > 0){
				foreach($noticias as $i=>$noticia) $texto .= ( $texto ? " - " : "" ) .$noticia ->post_title ."  ";

			}else
				$texto = __("No se han encontrado noticias en la categoría indicada", $this->slug);

		}


		$salida = $this->faiSlider( $texto ) ;

		$salida .= '<h2>'.__( "Ultimas Noticias", $this->slug ) .'</h2>	';



		$salida .= $this->listadoNoticias($noticias); // . var_export( $noticias, true );

		echo $salida;

		//var_dump (Acuarel_WP_Imaxes :: list_thumbnail_sizes());
	}





	/*
		Fai o slider co texto que reciba (xa sexa fijo ou ben un listado de noticias
	*/
	 function faiSlider ( $texto ){
		$plujin_options = get_option(  $this->noslug .'_options'  );  // array
		$fuente = $plujin_options["tam_fuente"];
		$salida = '<div class="ofertas-top">
		<div class="marquesina" style="top:'.$plujin_options["offsetslider"].'px;">
		<div class="marquesina-texto" style="font-size:'.$fuente.'px; '. ( $plujin_options["codyfont"] == 1 ? " font-family: 'Codystar',cursive;" : "" ) .' font-weight:normal;  " >';
		// Por defecto:
		$salida .=	"                                                                    -- $texto --";
		$salida .= '</div>
		</div>
		</div>' ; //.var_export($noticias, true );
		return $salida;

	 }




	/*
		Dame os post de certa categoria
	*/
	 function dameNoticias(){
		$plujin_options = get_option(  $this->noslug .'_options'  );  // array

		$os_posts = get_posts('category='.$plujin_options["id_categoria"] .'&order=DESC&orderby=date&numberposts=' . $plujin_options["n_noticias"]);
		return $os_posts;

	 }





	/*
		Dame as categorias de post, ollo co idioma do polylang
	*/
	 function dameCategorias($n_noticias= 3, $categoria = ""){


		$args = array(
			'type'						=> 'post',
			'child_of'				=> 0,
			'parent'					=> '',
			'orderby'				=> 'name',
			'order'					=> 'ASC',
			'hide_empty'			=> 1,
			'hierarchical'			=> 1,
			'exclude'					=> '',
			'include'					=> '',
			'lang'						=>'es'
		);
		$categories = get_categories( $args );

		return $categories;
	}



	 function damePostthumbnail( $ID){

		$thumbnail_id = get_post_thumbnail_id( $ID );

		if( "" === $thumbnail_id  || intval( $thumbnail_id ) <=0 ){
			$id_es	= pll_get_post( $ID ,  'es');
			$thumbnail_id = get_post_thumbnail_id( $id_es );
		}
		if( "" !== $thumbnail_id  && intval( $thumbnail_id ) >0 ){

			$info = wp_get_attachment_image_src( $thumbnail_id, "thumbnail" ); //  (0) url, (1) width, (2) height, and (3) scale

			$img = $info[0];

		}else $img =  plugins_url( '/images', __FILE__ )  .'/noticia-default.jpg';

		return $img;
	 }


	/*
		Fai listado en vertical coas noticias
	*/
	 function listadoNoticias( $noticias ){


		if( count( $noticias ) <= 0)return;

		$texto = "";

		foreach($noticias as $i=>$noticia){




			$texto .='<li><img src="'. $this->damePostthumbnail( $noticia->ID ) .'" class="thumb"><a href="' . esc_url( get_permalink( $noticia->ID ) ) . '" rel="bookmark" title="'.__("Ver noticia", $this->slug  ).'"><h3>'.$noticia->post_title.'</h3><h4>'.get_the_date("d-m-Y", $noticia->ID).'</h4><p>'.substr(nl2br( strip_tags( $noticia->post_content ) ), 0, 250).'[...]<span class="read-more">'. __('Leer más', $this->slug).'</span></p></a></li><div class="clear"></div>';

		}
		$salida ='<ul class="bloque-noticias-home">' . $texto . '</ul> <!-- /bloque-noticias-home -->';

		return $salida;
	 }


} // Fin de clase