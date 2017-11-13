<?php
/**
 * BoldGrid Source Code
 *
 * @package Boldgrid_Staging_Plugin
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
 * BoldGrid Staging Plugin class
 */
class Boldgrid_Staging_Plugin {

	/**
	 * Core object.
	 *
	 * @since 1.5.1
	 * @var   Boldgrid_Staging
	 */
	public $core;

	/**
	 * Constructor.
	 *
	 * @since unknown
	 *
	 * @param Boldgrid_Staging $core
	 */
	public function __construct( $core ) {
		$this->core = $core;
	}

	/**
	 * Add hooks
	 */
	public function add_hooks() {
		// Add actions for boldgrid_asset:
		add_action( 'pre_option_boldgrid_asset',
			array (
				$this,
				'boldgrid_asset_pre_option'
			) );

		add_action( 'pre_update_option_boldgrid_asset',
			array (
				$this,
				'boldgrid_asset_pre_option_update'
			), 10, 2 );

		// Add action for boldgrid_attribution:
		add_action( 'pre_option_boldgrid_attribution',
			array (
				$this,
				'boldgrid_attribution_pre_option'
			) );

		add_action( 'pre_update_option_boldgrid_attribution',
			array (
				$this,
				'boldgrid_attribution_pre_option_update'
			), 10, 2 );

		// Add actions for boldgrid_has_built_site:
		add_action( 'pre_option_boldgrid_has_built_site',
			array (
				$this,
				'boldgrid_has_built_site_pre_option'
			) );

		add_action( 'pre_update_option_boldgrid_has_built_site',
			array (
				$this,
				'boldgrid_has_built_site_pre_option_update'
			), 10, 2 );

		// Add actions for boldgrid_install_options:
		add_action( 'pre_option_boldgrid_install_options',
			array (
				$this,
				'boldgrid_install_options_pre_option'
			) );

		add_action( 'pre_update_option_boldgrid_install_options',
			array (
				$this,
				'boldgrid_install_options_pre_option_update'
			), 10, 2 );

		// Add actions for boldgrid_installed_page_ids:
		add_action( 'pre_option_boldgrid_installed_page_ids',
			array (
				$this,
				'boldgrid_installed_page_ids_pre_option'
			) );

		add_action( 'pre_update_option_boldgrid_installed_page_ids',
			array (
				$this,
				'boldgrid_installed_page_ids_pre_option_update'
			), 10, 2 );

		// Add actions for boldgrid_installed_pages_metadata:
		add_action( 'pre_option_boldgrid_installed_pages_metadata',
			array (
				$this,
				'boldgrid_installed_pages_metadata_pre_option'
			) );

		add_action( 'pre_update_option_boldgrid_installed_pages_metadata',
			array (
				$this,
				'boldgrid_installed_pages_metadata_pre_option_update'
			), 10, 2 );

		// Add actions for boldgrid_widgets_created:
		add_action( 'pre_option_boldgrid_widgets_created',
			array (
				$this,
				'boldgrid_widgets_created_pre_option'
			) );

		add_action( 'pre_update_option_boldgrid_widgets_created',
			array (
				$this,
				'boldgrid_widgets_created_pre_option_update'
			), 10, 2 );

		// Add actions for boldgrid_menus_created:
		add_action( 'pre_option_boldgrid_menus_created',
			array (
				$this,
				'boldgrid_menus_created_pre_option'
			) );

		add_action( 'pre_update_option_boldgrid_menus_created',
			array (
				$this,
				'boldgrid_menus_created_pre_option_update'
			), 10, 2 );

		// Add other actions:

		// Delete 'primary-staging' menu.
		add_action( 'boldgrid_options_cleanup_nav_menus',
			array (
				$this,
				'boldgrid_options_cleanup_nav_menus'
			) );

		// Tell BoldGrid to create a menu named 'primary-staging' rather than 'primary'.
		add_filter( 'boldgrid_deployment_primary_menu_name',
			array (
				$this,
				'boldgrid_deployment_primary_menu_name'
			) );

		// If used along side BoldGrid Inspirations plugin, as soon as we know the stylesheet,
		// update a few settings.
		add_action( 'boldgrid_deployment_deploy_theme_pre_return',
			array (
				$this,
				'boldgrid_deployment_deploy_theme_pre_return'
			) );

		// Modify the $post before deployment saves it.
		add_filter( 'boldgrid_deployment_pre_insert_post',
			array (
				$this,
				'boldgrid_deployment_pre_insert_post'
			) );

		// When looking for images that require attribution, search for 'staged' pages.
		add_filter( 'boldgrid_attribution_post_status_to_search',
			array (
				$this,
				'boldgrid_attribution_post_status_to_search'
			) );

		add_filter( 'boldgrid_attribution_page_ids',
			array (
				$this,
				'boldgrid_attribution_page_ids'
			) );

		// When BoldGrid Inspirations 'starts over', modify the array of page id's that are deleted.
		add_filter( 'boldgrid_inspirations_cleanup_page_ids',
			array (
				$this,
				'boldgrid_inspirations_cleanup_page_ids'
			) );

		// When creating staging menus, prepend name with "-staging".
		add_filter( 'boldgrid_theme_framework_config',
			array (
				$this,
				'boldgrid_theme_framework_config'
			) );

		add_filter( 'boldgrid_div_classes_post_submitbox_misc_actions_auto_add_to_menu',
			array (
				$this,
				'boldgrid_div_classes_post_submitbox_misc_actions_auto_add_to_menu'
			), 10, 2 );

		add_action( 'admin_enqueue_scripts', array (
			$this,
			'admin_enqueue_scripts'
		) );

		add_filter( 'boldgrid_save_post_auto_add_to_menu_abort_due_to_post_status',
			array (
				$this,
				'boldgrid_save_post_auto_add_to_menu_abort_due_to_post_status'
			), 10, 2 );

		add_action( 'boldgrid_inspirations_post_gridblock_set_create_page_callback',
			array (
				$this,
				'boldgrid_inspirations_post_gridblock_set_create_page_callback'
			) );

		add_action( 'boldgrid_inspirations_gridblock_sets_admin_post_construct',
			array (
				$this,
				'boldgrid_inspirations_gridblock_sets_admin_post_construct'
			) );

		add_filter( 'boldgrid_cart_post_status', array( $this, 'boldgrid_cart_post_status' ) );

		add_action( 'pre_set_permalinks', array( $this, 'pre_set_permalinks' ) );

		// Hooks intended for only front-end site:
		if ( ! is_admin() ) {
			// Change links from "attribution" to "attribution-staging"
			add_action( 'wp_head',
				array (
					$this,
					'boldgrid_change_links_to_attribution'
				) );

			// Help the BoldGrid Theme Framework create the proper link to the Staging Attribution
			// page.
			add_filter( 'boldgrid_attribution_filter',
				array (
					$this,
					'boldgrid_attribution_filter'
				) );
		}
	}

	/**
	 */
	public function admin_enqueue_scripts( $hook ) {
		switch ( $hook ) {
			// Page Editor.
			case 'post-new.php' :
			case 'post.php' :
				wp_enqueue_script( 'edit.php.js',
					BOLDGRID_STAGING_URL . 'assets/js/manage-menu-assignment-within-editor.js',
					array (), BOLDGRID_STAGING_VERSION, true );
				break;

			// BoldGrid Inspirations Cart.
			case 'boldgrid_page_boldgrid-cart':
			case 'transactions_page_boldgrid-cart':
				wp_enqueue_script( 'boldgrid-staging-cart',
									BOLDGRID_STAGING_URL . 'assets/js/boldgrid-staging-cart.js',
									array( 'wp-util' ),
									BOLDGRID_STAGING_VERSION,
									true
				);
				wp_enqueue_style(	'boldgrid-staging-cart',
									BOLDGRID_STAGING_URL . 'assets/css/boldgrid-staging-cart.css',
									array(),
									BOLDGRID_STAGING_VERSION
				);
				break;
		}
	}

	/**
	 * Modify a $post before BoldGrid Inspirations' deployment saves it.
	 *
	 * During BoldGrid Inspirations' deployment and specificly within deploy_page_sets(), before a
	 * post is saved, the 'boldgrid_deployment_pre_insert_post' filter is called.
	 *
	 * If the user is deploying a staged site, we'll modify the post before it is saved. For
	 * example:
	 * # Add '-staging' to the permalink
	 * # Set the post_status to 'staging' instead of 'publish'.
	 *
	 * @param array $post
	 *
	 * @return array
	 */
	public function boldgrid_deployment_pre_insert_post( $post ) {
		// Abort if necessary.
		if ( ! $this->core->base->user_should_see_staging() ) {
			return $post;
		}

		/*
		 * Adjust our page / post.
		 *
		 * Posts are currently not staged. If this is a post, set the status to private. This will
		 * prevent a "staged" post from becoming an active post and showing on the active site.
		 */
		switch( $post['post_type'] ) {
			case 'page':
				$post['post_name'] .= '-staging';
				$post['post_status'] = 'staging';
				break;
			case 'post':
				$post['post_status'] = 'private';
				break;
			case 'bg_attribution':
				$post['post_name'] .= '-staging';
				break;
		}

		return $post;
	}

	/**
	 * Tell BoldGrid to create a menu named 'primary-staging' rather than 'primary'.
	 *
	 * @param string $menu_name
	 *
	 * @return string
	 */
	public function boldgrid_deployment_primary_menu_name( $menu_name ) {
		if ( false == $this->core->base->user_should_see_staging() ) {
			return $menu_name;
		} else {
			return $menu_name . '-staging';
		}
	}

	/**
	 * Help BoldGrid Theme Framework create link to Attribution page.
	 *
	 * When the BoldGrid Theme Framework creates a link to the Attribution page, it starts out by
	 * trying to get the boldgrid_attribution option. This option contains the page id of the
	 * Attribution page.
	 *
	 * This method modifies the option name from boldgrid_attribution to
	 * boldgrid_staging_boldgrid_attribution. This new option will hold the page id of the Staging
	 * Attribution page.
	 *
	 * @since 1.0.3
	 *
	 * @param string $option
	 *        	= 'boldgrid_attribution'.
	 * @return string Either 'boldgrid_attribtuion' or 'boldgrid_staging_boldgrid_attribition'.
	 */
	public function boldgrid_attribution_filter( $option ) {
		if ( $this->core->base->user_should_see_staging() ) {
			return 'boldgrid_staging_' . $option;
		} else {
			return $option;
		}
	}

	/**
	 * Get attribution page ids
	 *
	 * @param array $attribution_page_ids
	 * @return array
	 */
	public function boldgrid_attribution_page_ids( $attribution_page_ids ) {
		$wp_options_attribution = get_option( 'boldgrid_staging_boldgrid_attribution' );

		if ( is_array( $wp_options_attribution ) &&  ! empty( $wp_options_attribution['page']['id'] ) ) {
			$attribution_page_ids[] = $wp_options_attribution['page']['id'];
		}

		return $attribution_page_ids;
	}

	/**
	 * When looking for images that require attribution, search for 'staged' pages
	 *
	 * @param string $post_status_to_search
	 *
	 * @return string
	 */
	public function boldgrid_attribution_post_status_to_search( $post_status_to_search ) {
		if ( true == $this->core->base->user_should_see_staging() ) {
			$post_status_to_search = "'staging'";
		}

		return $post_status_to_search;
	}

	/**
	 * Use javascript to change links from "attribution" to "attribution-staging"
	 */
	public function boldgrid_change_links_to_attribution() {
		// Abort if the user should not see staging.
		if ( false == $this->core->base->user_should_see_staging() ) {
			return;
		}

		?>
<!-- Update links from "attribution" to "attribution-staging" -->
<script type="text/javascript">
			jQuery(function() {
				jQuery("a[href$='attribution/']").each(function(){
					var current_link = jQuery(this).attr("href");
					jQuery(this).attr("href",current_link.replace("attribution/","attribution-staging/"));
				});
			});
			</script>
<?php
	}

	/**
	 * If used along side BoldGrid Inspirations plugin, as soon as we know the stylesheet, update a
	 * few settings.
	 *
	 * @param string $theme_folder_name
	 */
	public function boldgrid_deployment_deploy_theme_pre_return( $theme_folder_name ) {
		// Abort if the user should not see staging.
		if ( false == $this->core->base->user_should_see_staging() ) {
			return;
		}

		$previous_staging_stylesheet = $this->core->staging_stylesheet;

		// We just installed a theme via BoldGrid Inspirations and it's a staging install,
		// go ahead and update stylesheet options.
		update_option( 'boldgrid_staging_stylesheet', $theme_folder_name );
		update_option( 'boldgrid_staging_template', $theme_folder_name );

		// There are certain features that rely on whether or not there is a staging theme set.
		// We just set it, so set this option.
		$this->core->set_staging_theme();

		/*
		 * Below, we are adding to actions. We only want to add those actions once however. If the
		 * previous staging stylesheet matches the new one, then they've already been added, so
		 * abort.
		 */
		if ( $previous_staging_stylesheet != $theme_folder_name ) {
			// Add actions for widget_text:
			add_action( 'pre_option_theme_mods_' . $this->core->staging_stylesheet,
				array (
					$this->core->theme_staging,
					'theme_mods_pre_option'
				) );

			add_action( 'pre_update_option_theme_mods_' . $this->core->staging_stylesheet,
				array (
					$this->core->theme_staging,
					'theme_mods_pre_option_update'
				), 10, 2 );
		}
	}

	/**
	 * Add classes to div container for each menu.
	 *
	 * These classes will be used by JS to show / hide certain menus as the user clicks between
	 * active / staging under "Development Group".
	 *
	 * We'll add the following two classes:
	 * 1. Either 'active' or 'staging'.
	 * 2. If this is a 'primary' menu, we'll add that as well.
	 *
	 * @since 1.0.6
	 *
	 * @param array $div_classes
	 *        	The current classes to add to the div container.
	 * @param string $nav_menu_name
	 *        	The name of the menu.
	 * @return array $div_classes See above.
	 */
	public function boldgrid_div_classes_post_submitbox_misc_actions_auto_add_to_menu( $div_classes, $nav_menu_name ) {
		// Genereate some values below to make it easier to determine if this is a staging menu.
		$ends_with_staging = ( '-staging' == substr( $nav_menu_name, - 8 ) );
		$begins_with_primary = ( 'primary' == substr( strtolower( $nav_menu_name ), 0, 7 ) );

		// Does this menu name end in -staging-NUMBER?
		$ends_with_staging_and_number = false;
		$exploded_nav_menu_name = explode( '-', $nav_menu_name );
		$count = count( $exploded_nav_menu_name );
		if ( $count > 2 ) {
			$last_item_in_name_is_numeric = is_numeric( $exploded_nav_menu_name[$count - 1] );
			$second_to_last_is_staging = 'staging' == $exploded_nav_menu_name[$count - 2];

			$ends_with_staging_and_number = ( $last_item_in_name_is_numeric &&
				 $second_to_last_is_staging );
		}

		// Determine of the current page is staging and in the menu.
		// @since 1.0.8

		// Initialize $has_staging_page.
		$has_staging_page = false;

		// Get this menu's objects.
		$nav_menu_objects = wp_get_nav_menu_items( esc_attr( $nav_menu_name ) );

		// Get the current post.
		$post = get_post();

		// Get the post ID.
		$post_id = $post->ID;

		// Get the post status.
		$post_status = $post->post_status;

		// Check if any objects match the current page id/
		if ( 'staging' === $post_status && false === empty( $post_id ) &&
			 count( $nav_menu_objects ) > 0 ) {
			foreach ( $nav_menu_objects as $nav_menu_object ) {
				if ( $nav_menu_object->object_id == $post_id ) {
					$has_staging_page = true;

					break;
				}
			}
		}

		// Configure class 1/2: 'active' or 'staging'.
		$class_to_add = ( ( $has_staging_page || $ends_with_staging || $ends_with_staging_and_number ) ? 'staging' : 'active' );
		$div_classes[] = $class_to_add;

		// Configure class 2/2: 'primary', if applicable.
		if ( true == $begins_with_primary ) {
			$div_classes[] = 'primary';
		}

		return $div_classes;
	}

	/**
	 * Delete the staging nav menu
	 */
	public function boldgrid_options_cleanup_nav_menus() {
		wp_delete_nav_menu( 'primary-staging' );
	}

	/**
	 * Get WP Option for boldgrid_asset
	 *
	 * @param string $content
	 * @return string
	 */
	public function boldgrid_asset_pre_option( $content ) {

		/*
		 * If it has been flagged to force the unfiltered option, return it now.
		 *
		 * This is a hack for Boldgrid_Inspirations_Asset_Manager::get_active_assets().
		 */
		if( '1' === get_option( 'boldgrid_staging_get_unfiltered_boldgrid_asset' ) ) {
			return $content;
		}

		if ( $this->core->base->user_should_see_staging() || true === $this->is_working_with_a_staged_page() ) {
			$boldgrid_asset = get_option( 'boldgrid_staging_boldgrid_asset' );

			return $boldgrid_asset;
		}

		return $content;
	}

	/**
	 * Set WP Option for boldgrid_asset
	 *
	 * @param string $new_value
	 * @param string $old_value
	 *
	 * @return string
	 */
	public function boldgrid_asset_pre_option_update( $new_value, $old_value ) {
		if ( $this->core->base->user_should_see_staging() || true === $this->is_working_with_a_staged_page() ) {
			update_option( 'boldgrid_staging_boldgrid_asset', $new_value );

			return $old_value;
		}

		return $new_value;
	}

	/**
	 * Get WP Option for boldgrid_attribution
	 *
	 * @param array $content
	 *
	 * @return array
	 */
	public function boldgrid_attribution_pre_option( $content ) {
		if ( $this->core->base->user_should_see_staging() ) {
			$attribution = get_option( 'boldgrid_staging_boldgrid_attribution' );

			// If the option does not exist, it returns false. If we return false, then get_option
			// will ignore this filter and get boldgrid_attribution.
			if ( false == $attribution ) {
				return array ();
			} else {
				return $attribution;
			}
		}

		return $content;
	}

	/**
	 * Set WP Option for boldgrid_attribution
	 *
	 * @param array $new_value
	 * @param array $old_value
	 *
	 * @return array
	 */
	public function boldgrid_attribution_pre_option_update( $new_value, $old_value ) {
		if ( $this->core->base->user_should_see_staging() ) {
			update_option( 'boldgrid_staging_boldgrid_attribution', $new_value );

			return $old_value;
		}

		return $new_value;
	}

	/**
	 * Have BoldGrid look for watermarked images within staged pages.
	 *
	 * By default, BoldGrid will look for watermarked images within draft and published pages.
	 *
	 * @since 1.1.2
	 *
	 * @param array $post_status The default post_status to look within for watermarked images.
	 * @return array An array of post_status.
	 */
	public function boldgrid_cart_post_status( $post_status ) {
		$post_status = $this->core->base->user_should_see_staging() ? array( 'staging' ) : $post_status;

		return $post_status;
	}

	/**
	 * Get WP Option for boldgrid_has_built_site
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public function boldgrid_has_built_site_pre_option( $content ) {
		if ( $this->core->base->user_should_see_staging() ) {
			( $built_site = get_option( 'boldgrid_staging_boldgrid_has_built_site' ) ) ||
				 ( $built_site = 'no' );

			return $built_site;
		}

		return $content;
	}

	/**
	 * Set WP Option for boldgrid_has_built_site
	 *
	 * @param string $new_value
	 * @param string $old_value
	 *
	 * @return string
	 */
	public function boldgrid_has_built_site_pre_option_update( $new_value, $old_value ) {
		if ( $this->core->base->user_should_see_staging() ) {
			update_option( 'boldgrid_staging_boldgrid_has_built_site', $new_value );

			return $old_value;
		}

		return $new_value;
	}

	/**
	 * Remove private posts during 'start over'.
	 *
	 * Currently, posts are not staged. When a post is created during deployment, the status is set
	 * to 'private' instead of 'staging'.
	 *
	 * During a 'start over', private posts (like the one created above) are not deleted. In this
	 * method, we'll look for any private posts created during deployment, and add them to the array
	 * of pages to delete.
	 *
	 * @param array $page_ids
	 */
	public function boldgrid_inspirations_cleanup_page_ids( $page_ids ) {
		// Get and validate the metadata about the pages we installed during deployment.
		$installed_page_metadata = get_option( 'boldgrid_staging_boldgrid_installed_pages_metadata', array() );
		$installed_page_metadata = ( is_array( $installed_page_metadata ) ? $installed_page_metadata : array() );

		foreach ( $installed_page_metadata as $page_id => $page_data ) {
			// Was this page installed as a private post?
			$installed_as_private_post = ( 'post' == $page_data['post_type'] and
				 'private' == $page_data['post_status'] );

			// Is this page still a private post?
			if ( $installed_as_private_post ) {
				$post = get_post( $page_id );

				if ( ! empty( $post ) ) {
					$still_a_private_post = ( 'post' == $post->post_type and
						 'private' == $post->post_status );
				} else {
					$still_a_private_post = false;
				}
			}

			// If the post was installed as private and still is private, then added it to the array
			// of pages id's that need to be deleted.
			if ( $installed_as_private_post and $still_a_private_post ) {
				if ( null == $page_ids ) {
					$page_ids[] = $page_id;
				} elseif ( is_array( $page_ids ) and ! in_array( $page_id, $page_ids ) ) {
					$page_ids[] = $page_id;
				}
			}
		}

		// Get and validate our Staging attribution page.
		$attribution = get_option( 'boldgrid_staging_boldgrid_attribution' );
		$attribution = ( is_array( $attribution ) ? $attribution : false );

		if ( false !== $attribution ) {
			$page_ids[] = $attribution['page']['id'];
		}

		return $page_ids;
	}

	/**
	 * Set proper cookie when on "BoldGrid Inspirations > New From GridBlock" page.
	 *
	 * @since 1.0.7
	 */
	public function boldgrid_inspirations_gridblock_sets_admin_post_construct() {
		// Are we on the "New From GridBlocks" page?
		// wp-admin/edit.php?post_type=page&page=boldgrid-add-gridblock-sets&staging=1
		$in_new_from_gridblocks_page = ( ! empty( $_GET['post_type'] ) &&
			 'page' == $_GET['post_type'] && ! empty( $_GET['page'] ) &&
			 'boldgrid-add-gridblock-sets' == $_GET['page'] );

		if ( $in_new_from_gridblocks_page ) {
			$_SESSION['wp_staging_view_version'] = ( $this->core->base->user_should_see_staging() ? 'staging' : 'production' );
		}
	}

	/**
	 * Flag a post as being created by "Add GridBlock Sets".
	 *
	 * @since 1.0.7
	 *
	 * @param integer $page_id
	 *        	A WordPress page id.
	 */
	public function boldgrid_inspirations_post_gridblock_set_create_page_callback( $page_id ) {
		if ( $this->core->base->user_should_see_staging() ) {
			update_post_meta( $page_id, 'new_gridblock_set_staging', true );
		}
	}

	/**
	 * Get WP Option for boldgrid_install_options
	 *
	 * @param array $content
	 *
	 * @return array
	 */
	public function boldgrid_install_options_pre_option( $content ) {
		if ( $this->core->base->user_should_see_staging() ) {
			$install_options = get_option( 'boldgrid_staging_boldgrid_install_options' );

			return $install_options;
		}

		return $content;
	}

	/**
	 * Set WP Option for boldgrid_install_options
	 *
	 * @param array $new_value
	 * @param array $old_value
	 *
	 * @return array
	 */
	public function boldgrid_install_options_pre_option_update( $new_value, $old_value ) {
		if ( $this->core->base->user_should_see_staging() ) {
			update_option( 'boldgrid_staging_boldgrid_install_options', $new_value );

			return $old_value;
		}

		return $new_value;
	}

	/**
	 * Get WP Option for boldgrid_installed_page_ids
	 *
	 * @param array $content
	 *
	 * @return array
	 */
	public function boldgrid_installed_page_ids_pre_option( $content ) {
		if ( $this->core->base->user_should_see_staging() ) {
			$installed_page_ids = get_option( 'boldgrid_staging_boldgrid_installed_page_ids' );

			return $installed_page_ids;
		}

		return $content;
	}

	/**
	 * Set WP Option for boldgrid_installed_page_ids
	 *
	 * @param array $new_value
	 * @param array $old_value
	 *
	 * @return array
	 */
	public function boldgrid_installed_page_ids_pre_option_update( $new_value, $old_value ) {
		if ( $this->core->base->user_should_see_staging() ) {
			update_option( 'boldgrid_staging_boldgrid_installed_page_ids', $new_value );

			return $old_value;
		}
		return $new_value;
	}

	/**
	 * Get WP Option for boldgrid_installed_pages_metadata
	 *
	 * @param array $content
	 *
	 * @return array
	 */
	public function boldgrid_installed_pages_metadata_pre_option( $content ) {
		if ( $this->core->base->user_should_see_staging() ) {
			$installed_pages_metadata = get_option(
				'boldgrid_staging_boldgrid_installed_pages_metadata' );

			return $installed_pages_metadata;
		}

		return $content;
	}

	/**
	 * Set WP Option for boldgrid_installed_pages_metadata
	 *
	 * @param array $new_value
	 * @param array $old_value
	 *
	 * @return array
	 */
	public function boldgrid_installed_pages_metadata_pre_option_update( $new_value, $old_value ) {
		if ( $this->core->base->user_should_see_staging() ) {
			update_option( 'boldgrid_staging_boldgrid_installed_pages_metadata', $new_value );

			return $old_value;
		}

		return $new_value;
	}

	/**
	 * Get staging version of boldgrid_menus_created option.
	 *
	 * @since 1.0.4
	 *
	 * @return false If the user should not see staging, return false.
	 * @return mixed The value of the boldgrid_staging_boldgrid_menus_created option.
	 */
	public function boldgrid_menus_created_pre_option() {
		// The 'boldgrid_get_unfiltered_option' option have been available here @since 1.3.2.
		if ( $this->core->base->user_should_see_staging() && 'true' !== get_option( 'boldgrid_get_unfiltered_option' ) ) {
			return get_option( 'boldgrid_staging_boldgrid_menus_created', array () );
		} else {
			return false;
		}
	}

	/**
	 * Set staging version of boldgrid_menus_created option.
	 *
	 * @since 1.0.4
	 *
	 * @param mixed $new_value
	 *        	The new value for the boldgrid_menus_created option.
	 * @param mixed $old_value
	 *        	The old value of the boldgrid_menus_created option.
	 * @return mixed Depending on if staging, either $new_value or $old_value.
	 */
	public function boldgrid_menus_created_pre_option_update( $new_value, $old_value ) {
		if ( $this->core->base->user_should_see_staging() ) {
			update_option( 'boldgrid_staging_boldgrid_menus_created', $new_value );

			return $old_value;
		}

		return $new_value;
	}

	/**
	 * BoldGrid Inspirations Filter, only allow 'publish' and 'staging' pages to have their menus
	 * managed from the post/page editor.
	 *
	 * @since 1.0.6
	 *
	 * @param boolean $abort_due_to_post_status
	 *        	The current status of whether or not to abort.
	 * @param object $post
	 *        	Post object.
	 * @return object $post Post object.
	 */
	public function boldgrid_save_post_auto_add_to_menu_abort_due_to_post_status( $abort_due_to_post_status, $post ) {
		if ( 'publish' != $post->post_status && 'staging' != $post->post_status ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Append '-staging' to staging menus.
	 *
	 * @since 1.0.4
	 *
	 * @param array $config
	 *        	BoldGrid Theme Framework config.
	 * @return array BoldGrid Theme Framework config.
	 */
	public function boldgrid_theme_framework_config( $config ) {
		if ( $this->core->base->user_should_see_staging() ) {
			// If this theme has default menus set:
			if ( isset( $config['menu']['default-menus'] ) &&
				 is_array( $config['menu']['default-menus'] ) ) {
				// Loop through each default menu:
				foreach ( $config['menu']['default-menus'] as $menu_key => $menu_value ) {
					// Append the menu label with '-staging'.
					$config['menu']['default-menus'][$menu_key]['label'] .= '-staging';
				}
			}
		}

		return $config;
	}

	/**
	 * Get staging version of boldgrid_widgets_created option.
	 *
	 * When the BoldGrid theme framework is activated within a theme, its activation hook deletes
	 * all widgets it already created. The widgets it will delete are stored in the
	 * boldgrid_widgets_created option.
	 *
	 * It is required to stage the boldgrid_widgets_created option. If we did not, the following
	 * scenario would occur: I install an Active site, all is good. I install a Staging site. Upon
	 * staging site installation, the active site's widgets will be removed.
	 *
	 * @since 1.0.4
	 *
	 * @return false If the user should not see staging, return false.
	 * @return mixed The value of the boldgrid_staging_boldgrid_widgets_created option.
	 */
	public function boldgrid_widgets_created_pre_option() {
		if ( $this->core->base->user_should_see_staging() ) {
			return get_option( 'boldgrid_staging_boldgrid_widgets_created', array () );
		} else {
			return false;
		}
	}

	/**
	 * Get staging version of boldgrid_widgets_created option.
	 *
	 * @since 1.0.4
	 *
	 * @param mixed $new_value
	 *        	The new value for the boldgrid_widgets_created option.
	 * @param mixed $old_value
	 *        	The old value of the boldgrid_widgets_created option.
	 * @return mixed Depending on if staging, either $new_value or $old_value.
	 */
	public function boldgrid_widgets_created_pre_option_update( $new_value, $old_value ) {
		if ( $this->core->base->user_should_see_staging() ) {
			update_option( 'boldgrid_staging_boldgrid_widgets_created', $new_value );

			return $old_value;
		}

		return $new_value;
	}

	/**
	 * Check if user is working on a staging page
	 *
	 * Example: If we're downloading and attaching a new image,
	 * if this method returns true, we'll update boldgrid_staging_boldgrid_asset rather than
	 * boldgrid_asset.
	 *
	 * @return boolean
	 */
	public function is_working_with_a_staged_page() {
		// Download and insert into page
		if ( isset( $_REQUEST['action'] ) && 'download_and_insert_into_page' == $_REQUEST['action'] ) {
			if ( isset( $_REQUEST['post_id'] ) && is_numeric( $_REQUEST['post_id'] ) ) {
				$post = get_post( $_REQUEST['post_id'] );

				if ( 'staging' == $post->post_status ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Filter BoldGrid Inspiration's $set_permalinks value.
	 *
	 * This value determines whether or not to set permalinks during a deployment. If we're
	 * deploying a staging site, we don't want to adjust the permalinks as this will affect the
	 * active site.
	 *
	 * @since 1.3.6
	 *
	 * @param  bool $set_permalinks True if we should proceed with setting permalinks.
	 * @return bool
	 */
	public function pre_set_permalinks( $set_permalinks ) {
		return $this->core->base->user_should_see_staging() ? false : $set_permalinks;
	}
}
