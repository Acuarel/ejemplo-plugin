<?php
/*

	Description: Crea un grupo Chamado OQUEQUEIRAS  no menu principal co fin de meter ahí todolos plugins dque queiras
	Author: Daniel Prol (aka Cancrexo) para Acuarel
	Version: 1.0
	Author URI: http://www.acuarel.es

*/

defined( 'ABSPATH' ) OR exit;

class Acuarel_Plugins_Menu_Group{

	public $title, $slug, $noslug, $rootHook;


	/*
		Constructor
	*/
	public function __construct(){

		/*
			Ctes variass
		*/

		if(!defined("_DS_"))
			define("_DS_", DIRECTORY_SEPARATOR);

		if(!defined("_SELECTED_"))
			define("_SELECTED_","selected=\"selected\"");

		if(!defined("_CHECKED_"))
			define("_CHECKED_", "checked=\"checked\""); //para checkboxes

		if(!defined("_DISABLED_"))
			define("_DISABLED_","disabled=\"disabled\"");

		if(!defined("_READONLY_"))
			define("_READONLY_", "readonly=\"readonly\"");

		$this->pluginpath = plugins_url( '' , __FILE__ ); //WITHOUT the trailing slash)


		$this->plugindir = plugin_dir_path( __FILE__); // WITH trailing slash

		/*
			Titulo de paxin IMPORTANTISIMO -->xenerase slug
		*/

		$this->title= "Acuarel Plugins";

		/*
			Slug de este plujin
		*/
		$this->slug = sanitize_title( $this->title); // Que será como o menu_title pero pero preparado para slug: minusculas e - en vez de espacios

		/*
			Base para variables e demáis -->reemplazar - por _ en slug
		*/
		$this->noslug =str_replace("-","_", $this->slug);



		/*
			pilas de error e exito
		*/
		$this->SJB_Errores = new WP_Error();
		$this->SJB_Mesaxes = new WP_Error();



	}// fin constructor


	/*******************************************************************************************
		Crear menu que colga de Root comprobando si existe antes
	****************************************************************************************** */
	function My_plugins_menu_check() {

		global $menu;


		$menu_slug = $this->slug;

		foreach($menu as $k => $item)
			if($item[2] == $menu_slug) return; // Xa existe a opción de menu

		// Si non existe, crease
		// add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
		// Ollo : por aljun motivo de merda, o hook a empregar apara meter os css e os js faise co texto que metas $menu_title
		$this->rootHook= add_menu_page("Acuarel Plugins", "Acuarel Plugins", "edit_pages", $menu_slug, array(&$this, "My_plugins_about"), plugins_url('../images/logo.png', __FILE__));
		//error_log("Root hook :");
		//error_log($this->rootHook);

		/*
			Para evitar que me salga repetido o nome do grupo de plugins como un plugin máis, fago
			o seguinte (sacado de : http://www.linuxhispano.net/2013/02/08/prevenir-enlace-duplicado-cuando-usas-add_menu_page-en-wordpress/)
		*/
	  	//add_submenu_page($menu_slug, '', '', 'manage_options', $menu_slug, array(&$this, "SJB_plugins_about")	);
		add_submenu_page($menu_slug, '', '', 'edit_pages', $menu_slug, array(&$this, "My_plugins_about")	);

		//error_log("Menu base creado OK");

	}


	/*******************************************************************************************
		Contido root menu (descrición)
	****************************************************************************************** */
	function My_plugins_about() {
		echo"<div class=\"wrap\">
		<h2>Acuarel Plugins V 1.00</h2><h3>Acuarel - Gestor de plugins. Data: 2015-04-17.</h3>
		<p>Perchero para Plugins By Cancrexo - Acuarel</p></div>";
	}


}
?>
