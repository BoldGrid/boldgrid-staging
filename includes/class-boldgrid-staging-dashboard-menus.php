<?php
/**
 * BoldGrid Source Code
 *
 * @package Boldgrid_Staging_Dashboard_Menus
 * @copyright BoldGrid.com
 * @version $Id$
 * @author BoldGrid.com <wpb@boldgrid.com>
 */

// Prevent direct calls
if ( ! defined( 'WPINC' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

/**
 * BoldGrid Staging DashBoard Menus class
 */
class Boldgrid_Staging_Dashboard_Menus {
	/**
	 * Add hooks
	 */
	public function add_hooks() {
		if ( is_admin() ) {
			add_action( 'admin_menu', array (
				$this,
				'register_staging_menu_page' 
			), 1000 );
		}
	}
	
	/**
	 */
	public function add_active_site_under_customize() {
		add_theme_page( 
			// We will want to make sure that we keep our menu items
			// translatable in the future,
			// so we will need to add the text domain for Page Title and Menu
			// Title, like this:
			// __( 'Active Site', 'boldgrid-staging' ),
			
			// Page Title
			__( 'Active Site' ), 
			
			// Menu Title
			__( 'Active Site' ), 
			
			// Give users access to this feature if they are capable of editing
			// theme options
			'edit_theme_options', 
			
			// Properly escape the URL we generate to avoid any easy XSS
			// attacks.
			esc_url( 
				/**
				 * Build the return path.
				 * This is important for the return path for when user closes
				 * the staged
				 * customizer, so we don't inconveinece them by sending them
				 * back to the same static page each time.
				 *
				 * @urlencode: This function is convenient when encoding a
				 * string to be used in a query
				 * part of a URL, as a way to pass variables to the next page
				 * that will work properly with browsers.
				 *
				 * Since it needs to be secure and escaped, we will remove the
				 * slashes properly with WP.
				 *
				 * @see : https://codex.wordpress.org/Function_Reference/wp_unslash
				 *     
				 * @since v0.9
				 */
				
				add_query_arg( 'return', urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ), 
					
					// Root page to apply our query to ( ie the WordPress
					// Customizer ).
					'customize.php' ) ) );
	}
	
	/**
	 */
	public function add_customize_active_to_inspirations() {
		add_submenu_page( 
			// The single menu item is under the Inspirations parent slug.
			'boldgrid-inspirations', 
			
			// We will want to make sure that we keep our menu items
			// translatable in the future,
			// so we will need to add the text domain for Page Title and Menu
			// Title, like this:
			// __( 'Staged Site', 'boldgrid-staging' ),
			
			// Page Title
			__( 'Customize Active' ), 
			
			// Menu Title
			__( 'Customize Active' ), 
			
			// Give users access to this feature if they are capable of editing
			// theme options
			'edit_theme_options', 
			
			// Properly escape the URL we generate to avoid any easy XSS
			// attacks.
			esc_url( 
				/**
				 * Build the return path.
				 * This is important for the return path for when user closes
				 * the staged
				 * customizer, so we don't inconveinece them by sending them
				 * back to the same static page each time.
				 *
				 * @urlencode: This function is convenient when encoding a
				 * string to be used in a query
				 * part of a URL, as a way to pass variables to the next page
				 * that will work properly with browsers.
				 *
				 * Since it needs to be secure and escaped, we will remove the
				 * slashes properly with WP.
				 *
				 * @see : https://codex.wordpress.org/Function_Reference/wp_unslash
				 *     
				 * @since v0.9
				 */
				
				add_query_arg( 'return', urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ), 
					// Root page to apply our query to ( ie the WordPress
					// Customizer ).
					'customize.php' ) ) );
	}
	
	/**
	 */
	public function add_customize_active_to_appearance() {
		add_theme_page( 
			// We will want to make sure that we keep our menu items
			// translatable in the future,
			// so we will need to add the text domain for Page Title and Menu
			// Title, like this:
			// __( 'Staged Site', 'boldgrid-staging' ),
			
			// Page Title
			__( 'Customize Active' ), 
			
			// Menu Title
			__( 'Customize Active' ), 
			
			// Give users access to this feature if they are capable of editing
			// theme options
			'edit_theme_options', 
			
			// Properly escape the URL we generate to avoid any easy XSS
			// attacks.
			esc_url( 
				/**
				 * Build the return path.
				 * This is important for the return path for when user closes
				 * the staged
				 * customizer, so we don't inconveinece them by sending them
				 * back to the same static page each time.
				 *
				 * @urlencode: This function is convenient when encoding a
				 * string to be used in a query
				 * part of a URL, as a way to pass variables to the next page
				 * that will work properly with browsers.
				 *
				 * Since it needs to be secure and escaped, we will remove the
				 * slashes properly with WP.
				 *
				 * @see : https://codex.wordpress.org/Function_Reference/wp_unslash
				 *     
				 * @since v0.9
				 */
				
				add_query_arg( 'return', urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ), 
					// Root page to apply our query to ( ie the WordPress
					// Customizer ).
					'customize.php' ) ) );
	}
	
	/**
	 */
	public function add_customize_staging_to_appearance() {
		add_theme_page( 
			// We will want to make sure that we keep our menu items
			// translatable in the future,
			// so we will need to add the text domain for Page Title and Menu
			// Title, like this:
			// __( 'Customize Staged', 'boldgrid-staging' ),
			
			// Page Title
			__( 'Customize Staged' ), 
			
			// Menu Title
			__( 'Customize Staged' ), 
			
			// Give users access to this feature if they are capable of editing
			// theme options
			'edit_theme_options', 
			
			// Properly escape the URL we generate to avoid any easy XSS
			// attacks.
			esc_url( 
				
				// Build our query for this generated URL
				add_query_arg( 
					
					// It's got a couple of things needed, so we will pack an
					// array up.
					array (
						/**
						 * Build the return path.
						 * This is important for the return path for when user
						 * closes the staged
						 * customizer, so we don't inconveinece them by sending
						 * them back to the same static page each time.
						 *
						 * @urlencode: This function is convenient when encoding
						 * a string to be used in a query
						 * part of a URL, as a way to pass variables to the next
						 * page that will work properly with browsers.
						 *
						 * Since it needs to be secure and escaped, we will
						 * remove the slashes properly with WP.
						 *
						 * @see : https://codex.wordpress.org/Function_Reference/wp_unslash
						 *     
						 * @since v0.9
						 */
						'return' => urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ),
						
						// Since the staging customizer is flagged with
						// &staging=1 in the URL we will add this to our query
						'staging' => '1' 
					), 
					
					// Root page to apply our query to ( ie the WordPress
					// Customizer ).
					'customize.php' ) ) );
	}
	
	/**
	 */
	public function add_customize_staging_to_inspirations() {
		add_submenu_page( 
			// The single menu item is under the Inspirations parent slug.
			'boldgrid-inspirations', 
			
			// We will want to make sure that we keep our menu items
			// translatable in the future,
			// so we will need to add the text domain for Page Title and Menu
			// Title, like this:
			// __( 'Customize Staged', 'boldgrid-staging' ),
			
			// Page Title
			__( 'Customize Staged' ), 
			
			// Menu Title
			__( 'Customize Staged' ), 
			
			// Give users access to this feature if they are capable of editing
			// theme options
			'edit_theme_options', 
			
			// Properly escape the URL we generate to avoid any easy XSS
			// attacks.
			esc_url( 
				
				// Build our query for this generated URL
				add_query_arg( 
					
					// It's got a couple of things needed, so we will pack an
					// array up.
					array (
						/**
						 * Build the return path.
						 * This is important for the return path for when user
						 * closes the staged
						 * customizer, so we don't inconveinece them by sending
						 * them back to the same static page each time.
						 *
						 * @urlencode: This function is convenient when encoding
						 * a string to be used in a query
						 * part of a URL, as a way to pass variables to the next
						 * page that will work properly with browsers.
						 *
						 * Since it needs to be secure and escaped, we will
						 * remove the slashes properly with WP.
						 *
						 * @see : https://codex.wordpress.org/Function_Reference/wp_unslash
						 *     
						 * @since v0.9
						 */
						'return' => urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ),
						
						// Since the staging customizer is flagged with
						// &staging=1 in the URL we will add this to our query
						'staging' => '1' 
					), 
					
					// Root page to apply our query to ( ie the WordPress
					// Customizer ).
					'customize.php' ) ) );
	}
	
	/**
	 */
	public function add_deploy_staging_to_appearance() {
		add_theme_page( 
			// When we make our plugin translatable, we need to add text domain.
			// Replace page name and menu name with commented out code.
			
			// The Page's Name.
			
			// __( 'Deploy Staging', 'boldgrid-staging' ),
			__( 'Deploy Staging' ), 
			
			// The Menu Item's Name.
			
			// __( 'Deploy Staging', 'boldgrid-staging' ),
			__( 'Deploy Staging' ), 
			
			// Give users access to this feature if they are capable of managing
			// options ( applicable to admin/superadmin user roles )
			'manage_options', 
			
			// menu item slug for page we create
			'boldgrid-staging', 
			
			// Callback to our function that creates the page we want to display
			// for this menu item.
			array (
				$this,
				'display_staging_menu_page' 
			) );
	}
	
	/**
	 */
	public function add_deploy_staging_to_inspirations() {
		add_submenu_page( 
			
			// The single menu item is under the Inspirations parent slug.
			'boldgrid-inspirations', 
			
			// When we make our plugin translatable add text domain - replace
			// page name
			// and menu name with commented out code below.
			
			// The Page's Name.
			// __( 'Deploy Staging', 'boldgrid-staging' ),
			__( 'Deploy Staging' ), 
			
			// The Menu Item's Name.
			// __( 'Deploy Staging', 'boldgrid-staging' ),
			__( 'Deploy Staging' ), 
			
			// Give users access to this feature if they are capable of managing
			// options (admin/superadmin).
			'manage_options', 
			
			// Menu item slug name for the page we create.
			'boldgrid-staging', 
			
			// Callback to our function that creates the page we want to display
			// for this menu item.
			array (
				$this,
				'display_staging_menu_page' 
			) );
	}
	
	/**
	 */
	public function add_staged_site_under_customize() {
		add_theme_page( 
			
			// Make sure that we keep our menu items translatable in the future
			// We will need to add the text domain in here like so:
			// __( 'Staged Site', 'boldgrid-staging' ),
			
			// Page Title
			__( 'Staged Site' ), 
			
			// Menu Title
			__( 'Staged Site' ), 

			/**
			 * // Give users access to this feature if they are capable of
			 * editing theme options
			 *
			 * We may want to change this to allow editors since they can manage
			 * some of the theme use.
			 * Would need to add capability along the lines of something like
			 * this:
			 *
			 * $editor = get_role('editor');
			 * $editor->add_cap('edit_theme_options');
			 *
			 * This gives editors the ability to edit theme options.
			 *
			 * Currently I implemented edit_theme_options, as this is what is
			 * generally used for privs on using
			 * customizer options like Background and Header submenu items being
			 * displayed. Ultimately we would
			 * probably want to setup custom roles for our plugins and then add
			 * the capabilities necessary for
			 * each aspect we're modifying to help with security and
			 * organization. Perhaps add a settings ability
			 * for admin/superadmin to give specific users access to our plugins
			 * and functions.
			 */
			
			'edit_theme_options', 
			
			// Properly escape the URL we generate to avoid any easy XSS
			// attacks.
			esc_url( 
				
				// Build our query for this generated URL
				add_query_arg( 
					
					// It's got a couple of things needed, so we will pack an
					// array up.
					array (
						/**
						 * Build the return path.
						 * This is important for the return path for when user
						 * closes the staged
						 * customizer, so we don't inconveinece them by sending
						 * them back to the same static page each time.
						 *
						 * @urlencode: This function is convenient when encoding
						 * a string to be used in a query
						 * part of a URL, as a way to pass variables to the next
						 * page that will work properly with browsers.
						 *
						 * Since it needs to be secure and escaped, we will
						 * remove the slashes properly with WP.
						 *
						 * @see : https://codex.wordpress.org/Function_Reference/wp_unslash
						 *     
						 * @since v0.9
						 */
						'return' => urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ),
						
						// Since the staging customizer is flagged with
						// &staging=1 in the URL we will add this to our query
						'staging' => '1' 
					), 
					// End of our query's array.
					
					// Root page to apply our query to ( ie the WordPress
					// Customizer ).
					'customize.php' ) ) );
	}
	
	/**
	 */
	public function display_staging_menu_page() {
		$boldgrid_staging_deployment = new Boldgrid_Staging_Deployment();
		
		$boldgrid_staging_deployment->display_staging_menu_page();
	}
	
	/**
	 * Register dashboard menu items:
	 *
	 * 1. Deploy Staging
	 * 2. Customize Active
	 * 3. Customize Staging
	 */
	public function register_staging_menu_page() {
		// Grab the BoldGrid core plugin's settings/options array for proper
		// menu order based on user's preference:
		$boldgrid_menu_options = get_option( 'boldgrid_settings' );
		
		// Check to see if a staging stylesheet is available
		$boldgrid_detect_staging = get_option( 'boldgrid_staging_stylesheet' );
		
		$boldgrid_inspirations_is_active = is_plugin_active( 
			'boldgrid-inspirations/boldgrid-inspirations.php' );
		
		/**
		 * SCENARIO #1
		 *
		 * BoldGrid Inspirations IS INSTALLED and custom BoldGrid Menu IS ENABLED:
		 *
		 * CUSTOMIZE > Active Site
		 * CUSTOMIZE > Staged Site (If applicable)
		 * CUSTOMIZE > Deploy Staging
		 */
		if ( $boldgrid_inspirations_is_active && 1 == $boldgrid_menu_options['boldgrid_menu_option'] ) {
			$this->add_active_site_under_customize();
			
			if ( ! empty( $boldgrid_detect_staging ) ) {
				$this->add_staged_site_under_customize();
			}
			
			$this->add_deploy_staging_to_appearance();
			
			return;
		}
		
		/**
		 * SCENARIO #2
		 *
		 * IF BoldGrid Inspirations IS INSTALLED and custom BoldGrid Menu IS DISABLED:
		 *
		 * BOLDGRID > Customize Active
		 * BOLDGRID > Customize Staging (If applicable)
		 * BOLDGRID > Deploy Staging
		 */
		if ( $boldgrid_inspirations_is_active && 1 != $boldgrid_menu_options['boldgrid_menu_option'] ) {
			$this->add_customize_active_to_inspirations();
			
			if ( ! empty( $boldgrid_detect_staging ) ) {
				$this->add_customize_staging_to_inspirations();
			}
			
			$this->add_deploy_staging_to_inspirations();
			
			return;
		}
		
		/**
		 * SCENARIO #3
		 *
		 * IF BoldGrid Inspirations IS NOT ACTIVE:
		 *
		 * APPEARANCE > Deploy Staging
		 * APPEARANCE > Customize Active Site
		 * APPEARANCE > Customize Staging Site
		 */
		$this->add_deploy_staging_to_appearance();
		$this->add_customize_active_to_appearance();
		if ( ! empty( $boldgrid_detect_staging ) ) {
			$this->add_customize_staging_to_appearance();
		}
	}
}
?>