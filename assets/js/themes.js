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
	var self = this;

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
				$theme.children( '.theme-actions' ).children( '.activate' ).after( stageButton );
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
			// "Unstage" button.
			unstageThemeButton = "<a class='button button-secondary unstage'>" + BoldGridStagingThemes.Unstage + "</a>",
			// The text, "Active & Staged".
			activeAndStaged = BoldGridStagingThemes.Active + " & " + BoldGridStagingThemes.Staged;

		if ( self.activeTheme === self.stagedTheme ) {
			// Change the text from "Active" to "Active & Staged".
			self.$activeTheme.find( '.theme-name span:first' ).html( activeAndStaged );

			// Add "Unstage" button before "Customize" button.
			self.$activeTheme.find( '.customize' ).before( unstageThemeButton );
		} else {

			console.log( 'here' );

			$stagedThemeContainer
				// Add the active class.
				.addClass( 'active' )
				// Move our Staging theme container after our Active theme.
				.insertAfter( self.$activeTheme );


			$stagedThemeContainer
				// Add 'Staged:' before the theme name
				.children('.theme-name').prepend( '<span>' + BoldGridStagingThemes.Staged + ':</span> ');

			$stagedThemeContainer.find( '.activate' ).after( unstageThemeButton );
		}
	};

	/*
	 * @summary Init.
	 *
	 * @since xxx
	 */
	this.init = function() {
		self.$activeTheme = $( '.theme.active' );

		self.activeTheme = self.$activeTheme.attr( 'data-slug' );


		self.initStagedContainer();

		// If the active theme is not also staged theme, add a "Stage" button to the active theme.
		if ( self.stagedTheme != self.activeTheme ) {
			self.$activeTheme.find( '.customize' ).before( self.createStageButton( self.activeTheme ) );
		}

		// When mousing over a theme, add a "Stage" button.
		$( '.themes' ).on( 'mouseover', '.theme', function() {
			self.addStageButton( $( this ) );
		});

		// When clicking a "Stage" button, stage the theme.
		$( '.themes' ).on( 'click', '.stage', function() {
			self.stageTheme( $( this ).data( 'stylesheet' ) );
		});

		// When clicking an "Unstage" button, unstage the theme.
		$( '.themes' ).on( 'click', '.unstage', function() {
			self.unstageTheme();
		});
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
				window.location.href = window.location;
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
				window.location.href = window.location;
			} else {
				alert( BoldGridStagingThemes.errorStagingTheme );
			}
		});
	};

	self.init();
};

IMHWPB.StagingThemes( jQuery );