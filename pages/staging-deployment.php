<?php

// Prevent direct calls
if ( ! defined( 'WPINC' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

$stylesheet = get_option( 'stylesheet' );
$boldgrid_staging_stylesheet = get_option( 'boldgrid_staging_stylesheet' );
?>


<style>
.plugin-card.boldgrid-staging-launch-staging, .nav-tab-wrapper.boldgrid
	{
	width: 75%;
	max-width: 700px;
	padding-left: 0px;
	padding-right: 0px;
}
</style>

<div class='wrap'>

	<h1>Deploy Staging</h1>

	<h2 class="nav-tab-wrapper boldgrid" data-container='deploy-staging'>
		<a href="#" class="nav-tab nav-tab-active" data-toggle='info'>Info</a>
		<a href="#" class="nav-tab" data-toggle='advanced'>Advanced</a>
	</h2>

	<form method='post'>

		<div class="plugin-card boldgrid-staging-launch-staging">

			<div class="plugin-card-top">

				<div class='nav-tab-deploy-staging container-info'>

					<p>When you are ready to turn your Staging Site into your Active
						Site, the following will occur:</p>

					<h2>Themes</h2>

					<p>
						Your active theme and staging theme will be switched. <strong>Your
							active site</strong> will use <em><?php echo $boldgrid_staging_stylesheet ?></em>
						instead of <em><?php echo $stylesheet; ?></em>, and vice versa.
					</p>

					<h2>Pages</h2>

					<p>
						<strong>All of your active pages</strong> will become staged, and
						vice versa.
					</p>

					<p>
						<strong>The URLs of your active pages</strong> will be appended
						with <em>-staging</em>.<br />For example, <em>about-us</em> will
						become <em>about-us<u>-staging</u></em>.
					</p>

					<p>
						<strong>The URLs of your staged pages</strong> will have their
						appended <em>-staging</em> removed.<br />For example, <em>about-us<u>-staging</u></em>
						will become <em>about-us</em>.
					</p>

					<p>
						Further control of how your pages will be changed can be found in
						the <em>Advanced</em> tab above.
					</p>

					<h2>Settings</h2>

					<p>Various options and settings will be switched between your
						active and staging sites.</p>
				</div>

				<div class='nav-tab-deploy-staging container-advanced hidden'>

					<p>When you are ready to turn your Staging Site into your Active
						Site there are a few things you may need to decide.</p>

					<h2>Replacing pages</h2>

					<p>
						First, you may be replacing existing pages with new pages. For
						example, if you have a <em>Contact Us</em> page in your Active
						Site with a URL of <strong>/contact-us</strong> it is a best
						practice to keep the URL of <strong>/contact-us</strong> when
						changing your <em>Contact Us</em> page and content.
					</p>

					<h2>Unneeded pages</h2>

					<p>Second, you may not need a page any longer and in this case the
						best practice is to redirect anyone going to that old page to a
						new page or to the homepage. An advance option is to serve the
						visitor a 404, but that is not generally recommend.</p>

					<p>Our Staging Site Deployment will help you accomplish the above
						properly.</p>

					<p>Of note, this will not delete any of the existing pages when
						they are replaced with new pages. The existing pages will be moved
						to existing-name-old-# and will be available from your "All Pages"
						listing. You can delete them there when you are sure you are
						finished with that old page.</p>

					<h2>Pages</h2>

					<p>Listed below are all of your currently published pages. Decide
						below for each published page if it should be:</p>

					<ul style='list-style-type: disc; list-style-position: inside;'>
						<li>Replaced by a Staged page.</li>
						<li>Redirected to a Staged page.</li>
						<li>Do nothing (in normal circumstances, a 404 error will be seen
							by users).</li>
					</ul>
				
				<?php echo $this->renaming_pages_and_posts(); ?>
				
			</div>

			</div>

			<div class='plugin-card-bottom'>

				<div class='column-updated'>

					<input type='hidden' name='action' id='action'
						value='launch_staging' />
					
				<?php wp_nonce_field( 'launch_staging' ); ?>
					
				<?php submit_button( 'Launch Staging!', 'primary', 'submit', false ); ?>
				
			</div>

			</div>

		</div>

	</form>

</div>


<!--
/**
 * Inline js to handle clicking of Info / Advanced tabs.
 */
-->
<script type='text/javascript'>
jQuery('.nav-tab-wrapper.boldgrid a').on('click',function(){
	var this_tab = jQuery(this);
	var to_toggle = this_tab.attr('data-toggle');
	var this_wrapper = this_tab.closest('.nav-tab-wrapper');
	var data_container = this_wrapper.attr('data-container');

	// Remove 'active' class from all tabs.
	this_wrapper.children('a.nav-tab').removeClass('nav-tab-active');

	// Add 'active' class to the tab clicked.
	this_tab.addClass('nav-tab-active');

	// Show / hide appropriate containers.
	this_wrapper.children('a.nav-tab').each(function() {
		var child_tab = jQuery(this);
		var toggle = child_tab.attr('data-toggle');

		if( child_tab.attr('data-toggle') == to_toggle ) {
			jQuery('.nav-tab-' + data_container + '.container-' + toggle).removeClass('hidden');
		} else {
			jQuery('.nav-tab-' + data_container + '.container-' + toggle).addClass('hidden');
		}
	});
});
</script>