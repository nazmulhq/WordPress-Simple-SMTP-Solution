<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class SC_Wordsmtp_Autoloader {
	
	public function __construct() {		
		new SC_Wordsmtp();
		new SC_Wordsmtp_Ajaxhandler();
		//new Caaps_Amazon_Shop_Posttype();
		//new Caaps_Amazon_Addproducts();
		//new Caaps_Amazon_Ajax_Handler();		
		//new Caaps_Amazon_Response_Process();
		//new Caaps_Template_Helper();
		//new Caaps_Amazon_Helper();
		//new Caaps_Widgets();
		//new Caaps_Post_Products();
		//new Caaps_Amazon_Shortcode();				
	}
}
?>