/**
 * This file is loaded on the Change Themes page. It adds all the "Stage" buttons for the themes
 * and handles other staging functions on this page.
 *
 * @summary Add staging to themes page.
 *
 * @since 1.2.3
 */

/* globals BoldGridStagingThemes, ajaxurl */

var IMHWPB = IMHWPB || {};

IMHWPB.StagingThemes = function( $ ) {
	var self = this, unstageButton, $themes, $wrap;

	/**
	 * Staging stylesheet.
	 *
	 * @since 1.2.3
	 */
	self.stagedTheme = BoldGridStagingThemes.stagingStylesheet;

	/**
	 * The Active theme container.
	 *
	 * @since 1.2.3
	 */
	self.$activeTheme = null;

	/**
	 * Active stylesheet.
	 *
	 * @since 1.2.3
	 */
	self.activeTheme = null;

	$(function() {
		self.init();
	});

	/**
	 * @summary Add a "Stage" button to a theme container.
	 *
	 * @since 1.2.3
	 */
	this.addStageButton = function( $theme ) {
		// Get the stylesheet name by scanning the "Activate" url.
		var stylesheet = $theme.attr( 'data-slug' ),
			// Create the "Stage" button and add it to the theme.
			stageButton = self.createStageButton( stylesheet ),
			// Get any existing stage button on this theme.
			existingStageButton = $theme.find( 'a.stage' );

		// If this theme is not already staged AND doesn't have a "Stage" button, add one.
		if( self.stagedTheme !== stylesheet && 0 === existingStageButton.length ) {
				$theme.find( '.activate' ).after( stageButton );
		}
	};

	/**
	 * @summary Create "Stage" button.
	 *
	 * @since 1.2.3
	 */
	this.createStageButton = function( stylesheet ) {
		return "<a class='button button-secondary stage' data-stylesheet='"	+ stylesheet + "'>" +
			BoldGridStagingThemes.Stage + "</a>";
	};

	/**
	 * @summary Init the staging theme's container.
	 *
	 * For example, we need to add the active class to it and move it directly after the active theme.
	 *
	 * @since 1.2.3
	 */
	this.initStagedContainer = function() {
		var $stagedThemeContainer = $( '.theme[data-slug="' + self.stagedTheme + '"]' ),
			// The text, "Active & Staged".
			activeAndStaged = BoldGridStagingThemes.Active + " & " + BoldGridStagingThemes.Staged;

		unstageButton = "<a class='button button-secondary unstage'>" + BoldGridStagingThemes.Unstage + "</a>";

		if ( self.activeTheme === self.stagedTheme ) {
			// Change the text from "Active" to "Active & Staged".
			self.$activeTheme.find( '.theme-name span:first' ).html( activeAndStaged );

			// Add "Unstage" button before "Customize" button.
			self.$activeTheme.find( '.customize' ).before( unstageButton );
		} else {
			// Add the active class.
			$stagedThemeContainer.addClass( 'active' );

			// Add 'Staged:' before the theme name
			$stagedThemeContainer
				.children('.theme-name').prepend( '<span>' + BoldGridStagingThemes.Staged + ':</span> ');

			// Add our "unstage" button.
			$stagedThemeContainer.find( '.activate' ).after( unstageButton );
		}
	};

	/**
	 * @summary Actions to take upon clicking Theme Details.
	 *
	 * Display stage / unstage buttons.
	 *
	 * @since 1.3.9
	 */
	this.onThemeDetails = function() {
		var $theme = $( this ).closest( '.theme' ),
			stylesheet = $theme.attr( 'data-slug' ),
			isStaged = $theme.find( 'a.unstage' ).length > 0,
			$actions = $( '.theme-actions' ),
			button = isStaged ? unstageButton : self.createStageButton( stylesheet );

		$actions.find( '.active-theme a' ).first().before( button );
		$actions.find( '.inactive-theme a' ).first().after( button );
	};

	/**
	 * @summary Remove "Deploy Staging" from theme actions.
	 *
	 * Why is it added in the first place? Because of wp-admin/themes.php. Essentially in that file
	 * WP scans the menu items under the Appearance tab, and adds certain links to theme actions.
	 *
	 * @since 1.2.6
	 */
	this.removeDeployStaging = function() {
		// Grab the "Deploy Staging" text from the menu.
		var deployStaging = $( 'li.wp-menu-open' ).find( 'a[href*="boldgrid-staging"]' ).text();

		// Find the button based off that text and remove it.
		$( '.theme-actions .active-theme a:contains("' + deployStaging + '")').remove();
	};

	/**
	 * @summary Init.
	 *
	 * @since 1.2.5
	 */
	this.init = function() {
		self.$activeTheme = $( '.theme.active' );

		self.activeTheme = self.$activeTheme.attr( 'data-slug' );

		$themes = $( '.themes' );

		$wrap = $( '.wrap' );

		self.initStagedContainer();

		/*
		 * Remove "Deploy Staging" from theme actions.
		 *
		 * We remove it on page load and when the user clicks to see theme details of the active
		 * theme. We remove it on page load because if you're looking at theme details and refresh
		 * the page, the modal pops up when the page reloads.
		 */
		self.removeDeployStaging();
		self.$activeTheme.on( 'click', function() {
			self.removeDeployStaging();
		});

		// If the active theme is not also staged theme, add a "Stage" button to the active theme.
		if ( self.stagedTheme != self.activeTheme ) {
			self.$activeTheme.find( '.customize' ).before( self.createStageButton( self.activeTheme ) );
		}

		// When mousing over a theme, add a "Stage" button.
		$themes.on( 'mouseover', '.theme', function() {
			self.addStageButton( $( this ) );
		});

		// When clicking a "Stage" button, stage the theme.
		$wrap.on( 'click', '.stage', function() {
			self.stageTheme( $( this ).attr( 'data-stylesheet' ) );
		});

		// When clicking an "Unstage" button, unstage the theme.
		$wrap.on( 'click', '.unstage', self.unstageTheme );

		// On clicking "Theme Details".
		$themes.on( 'click', '.theme-screenshot, .more-details', self.onThemeDetails );
	};

	/**
	 * @summary Stage a theme.
	 *
	 * @since 1.2.3
	 *
	 * @param string stylesheet
	 */
	this.stageTheme = function( stylesheet ) {
		var data = {
			'action' : 'set_staged_theme',
			'stylesheet' : stylesheet
		};

		jQuery.post( ajaxurl + '?staging=1', data, function( response ) {
			if ( 'success' === response ) {
				window.location.href = BoldGridStagingThemes.themesUrl;
			} else {
				alert( BoldGridStagingThemes.errorStagingTheme );
			}
		});
	};

	/**
	 * @summary Unstage a theme.
	 *
	 * @since 1.2.3
	 */
	this.unstageTheme = function() {
		var data = {
			'action' : 'unstage_theme',
		};

		jQuery.post( ajaxurl, data, function(response) {
			if ( 'success' === response ) {
				window.location.href = BoldGridStagingThemes.themesUrl;
			} else {
				alert( BoldGridStagingThemes.errorStagingTheme );
			}
		});
	};
};

IMHWPB.StagingThemes( jQuery );
