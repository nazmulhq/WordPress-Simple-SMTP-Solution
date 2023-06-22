<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class SC_Wordsmtp {
	private static $initiated = false;
	
	public function __construct() {
		if ( ! self::$initiated ) {
			self::initiate_hooks();
		}
	}
	
	private static function initiate_hooks() {			    				
	    add_action( 'admin_init', array( __CLASS__, 'add_scwordsmtp_settings_data' ) );		
		add_action( 'admin_menu', array( __CLASS__, 'add_scwordsmtp_submenus' ) );		
		add_action( 'admin_notices', array( __CLASS__, 'scwordsmtp_admin_notices' ) );		
		add_action( 'plugins_loaded', array( __CLASS__, 'scwordsmtp_load_textdomain') );
		add_filter( 'plugin_row_meta',     array( __CLASS__, 'scwordsmtp_row_link'), 10, 2 );
		self::$initiated = true;
	}
			
	public static function activate() {
		self::check_preactivation_requirements();
		flush_rewrite_rules( true );
		
	}
	
	public static function check_preactivation_requirements() {				
		if ( version_compare( PHP_VERSION, SCWORDSMTP_MINIMUM_PHP_VERSION, '<' ) ) {
			wp_die('Minimum PHP Version required: ' . SCWORDSMTP_MINIMUM_PHP_VERSION );
		}
        global $wp_version;
		if ( version_compare( $wp_version, SCWORDSMTP_MINIMUM_WP_VERSION, '<' ) ) {
			wp_die('Minimum Wordpress Version required: ' . SCWORDSMTP_MINIMUM_WP_VERSION );
		}
	}
	
	public static function scwordsmtp_load_textdomain() {
		load_plugin_textdomain( 'wordsmtp-wordpress-simple-smtp', false, SCWORDSMTP_PLUGIN_DIR . 'languages/' ); 
	}
		
	public static function add_scwordsmtp_settings_data() {
		register_setting( 'scwordsmtp-settings', 'scwordsmtp-settings' );
		add_settings_section( 'scwordsmtp-settings-section', __( 'WordSMTP Settings' ), array( __CLASS__, 'settings_section_callback' ), 'scwordsmtp-settings' );
		// SMTP Host Field
		add_settings_field( 'scwordsmtp-settings-host-field', __( 'SMTP Host/Server', 'wordsmtp-wordpress-simple-smtp' ), array( __CLASS__, 'settings_section_fields_callback' ), 'scwordsmtp-settings', 'scwordsmtp-settings-section', $args = array( 'fieldname' => 'smtphost', 'label_for' => 'scwordsmtp-settings-field-smtphost' ) );

		// SMTP User Field
		add_settings_field( 'scwordsmtp-settings-user-field', __( 'SMTP User/Login', 'wordsmtp-wordpress-simple-smtp' ), array( __CLASS__, 'settings_section_fields_callback' ), 'scwordsmtp-settings', 'scwordsmtp-settings-section', $args = array( 'fieldname' => 'smtpuser', 'label_for' => 'scwordsmtp-settings-field-smtpuser' ) );

		// SMTP Password Field
		add_settings_field( 'scwordsmtp-settings-password-field', __( 'SMTP Password/Secret', 'wordsmtp-wordpress-simple-smtp' ), array( __CLASS__, 'settings_section_fields_callback' ), 'scwordsmtp-settings', 'scwordsmtp-settings-section', $args = array( 'fieldname' => 'smtppassword', 'label_for' => 'scwordsmtp-settings-field-smtppassword' ) );

		// SMTP encryption checkbox
		add_settings_field( 'scwordsmtp-settings-encryption-field', __( 'Encryption', 'wordsmtp-wordpress-simple-smtp' ), array( __CLASS__, 'settings_section_fields_callback' ), 'scwordsmtp-settings', 'scwordsmtp-settings-section', $args = array( 'fieldname' => 'encryption', 'label_for' => 'scwordsmtp-settings-field-encryption' ) );

		// SMTP Port
		add_settings_field( 'scwordsmtp-settings-smtpport-field', __( 'SMTP Port', 'wordsmtp-wordpress-simple-smtp' ), array( __CLASS__, 'settings_section_fields_callback' ), 'scwordsmtp-settings', 'scwordsmtp-settings-section', $args = array( 'fieldname' => 'smtpport', 'label_for' => 'scwordsmtp-settings-field-smtpport' ) );
								
	}

		
			
	public static function settings_section_callback() {
		include_once( SCWORDSMTP_PLUGIN_DIR . 'admin/views/settings-section-callback-page.php');
	}
	
	public static function settings_section_fields_callback( $args = null ) {		
		$options = get_option('scwordsmtp-settings');
		//print_r($options);
		switch ($args['fieldname']) {
			case 'smtphost':
				$value = isset( $options[$args['label_for']] )? $options[$args['label_for']] : '';
				echo '<input type="text" id="'.$args['label_for'].'" name="scwordsmtp-settings['.esc_attr($args['label_for']).']" value="'.$value.'" size="100" placeholder="SMTP HOST" />';
			break;
				
			case 'smtpuser':
				$value = isset( $options[$args['label_for']] )? $options[$args['label_for']] : '';
				echo '<input type="text" id="'.$args['label_for'].'" name="scwordsmtp-settings['.esc_attr($args['label_for']).']" value="'.$value.'" size="100" placeholder="SMTP USER" />';
			break;

			case 'smtppassword':
				$value = isset( $options[$args['label_for']] )? $options[$args['label_for']] : '';
				echo '<input type="password" id="'.$args['label_for'].'" name="scwordsmtp-settings['.esc_attr($args['label_for']).']" value="'.$value.'" size="100" placeholder="SMTP PASSWORD" />';
			break;

			case 'encryption':
				$value = isset( $options[ $args['label_for'] ] ) && ! empty( $options[ $args['label_for'] ])? $options[ $args['label_for'] ] : 'tls';				
				echo '<input type="radio" id="'.$args['label_for'].'" class="encryption-radio" name="scwordsmtp-settings['.esc_attr($args['label_for']).']" value="none" ' . checked( $value, 'none', false ) . ' /> None ';
				echo '<input type="radio" id="'.$args['label_for'].'" class="encryption-radio" name="scwordsmtp-settings['.esc_attr($args['label_for']).']" value="ssl" ' . checked( $value, 'ssl', false ) . ' /> SSL ';
				echo '<input type="radio" id="'.$args['label_for'].'" class="encryption-radio" name="scwordsmtp-settings['.esc_attr($args['label_for']).']" value="tls" ' . checked( $value, 'tls', false ) . ' /> TLS (<small>Recommended</small>)';	
			break;
				
			case 'smtpport':
				$value = isset( $options[$args['label_for']] )? $options[$args['label_for']] : 587;
				echo '<input type="number" id="'.$args['label_for'].'" class="smtp-port" name="scwordsmtp-settings['.esc_attr($args['label_for']).']" value="'.$value.'" size="10" placeholder="SMTP PORT" />';
			break;
		   	
		}
	}
	
	
	public static function add_scwordsmtp_submenus() {
		
		// Top Menu|Parent Menu - WordSMTP
		add_menu_page( __( 'WordSMTP - Wordpress Simple SMTP', 'wordsmtp-wordpress-simple-smtp' ), 'WordSMTP', 'manage_options', 'word-smtp-topmenu', '', 'dashicons-email-alt2', 6 );
		
		// Submenu - Settings - top level menu slug used as same 'word-smtp-topmenu' for the first submenu slug to avoid show the top level menu name as submenu   
		add_submenu_page(
		    'word-smtp-topmenu',
        __( 'Word SMTP', 'wordsmtp-wordpress-simple-smtp' ),
        __( 'Settings', 'wordsmtp-wordpress-simple-smtp' ),
            'manage_options',
            'word-smtp-topmenu',
			array( __CLASS__, 'add_scwordsmtp_submenus_settings_callback' )        
          );
								  
	}	
								
	public static function add_scwordsmtp_submenus_settings_callback() {
		// check user capabilities
		if ( !current_user_can('manage_options' ) ) {
			return;
		}		
		include_once SCWORDSMTP_PLUGIN_DIR . 'admin/views/add_scwordsmtp_submenus_settings_callback.php';		
	}
	
		
	public static function scwordsmtp_admin_notices() {
		$admin_notice 			= false;		
		$options 				= get_option('scwordsmtp-settings');		
		if ( $options ) {
			$smtphost 			= $options['scwordsmtp-settings-field-smtphost'];
			$smtpuser 			= $options['scwordsmtp-settings-field-smtpuser'];
			$smtppassword 		= $options['scwordsmtp-settings-field-smtppassword'];		    			
			if ( isset( $smtphost ) && ! empty( $smtphost ) 
				   && isset( $smtpuser ) && ! empty( $smtpuser )
					&& isset( $smtppassword ) && ! empty( $smtppassword )
			   ) {
				$admin_notice = true;
			}
		}
							
		if ( ! $admin_notice ) {			
			$url = admin_url('admin.php?page=word-smtp-topmenu');
			$alink = '<a href="'.$url.'">Click to add SMTP settings.</a>';
			printf('<div class="notice notice-warning is-dismissible">');
		    printf('<div class="scwordsmtp-notice-wrapper"><h3><span class="dashicons dashicons-email-alt2"></span> WordSMTP:</h3> <h4>SMTP configuration required to deliver your emails through wordSMTP in menu WordSMTP ' .$alink.'</h4></div>');
	        printf('</div>');
		}
		
	}
	
	public static function scwordsmtp_row_link( $actions, $plugin_file ) {
		$wordsmtp_plugin 	= plugin_basename( SCWORDSMTP_PLUGIN_DIR );
		$plugin_name 		= basename($plugin_file, '.php');
		if ( $wordsmtp_plugin == $plugin_name ) {
			//$doclink[] 		= '<a href="https://softcoy.com/wordsmtp" title="WordSMTP - Docs" target="_blank">WordSMTP Docs</a>';	
			//$doclink[] 		= '<a href="https://softcoy.com/wordsmtp" title="WordSMTP Support" target="_blank">Support</a>';	
			//return array_merge( $actions, $doclink );
		}
		return $actions;
	}	
	
} // End class