<?php

// Prevent direct calls
if ( ! defined( 'WPINC' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

$url_to_all_pages = get_admin_url( null, 'edit.php?post_type=page' );
$url_to_site = get_site_url();

// Change to active site.
$_SESSION['wp_staging_view_version'] = 'production';

?>

<style>
.spinner {
	visibility: visible;
	float: none;
	margin: 0px;
	vertical-align: top;
}

h1 .dashicons.dashicons-yes {
	color: green;
	font-size: 30px;
	padding-right: 15px;
}
</style>

<div class="wrap">

	<h1 class="deploying clear">Deploying your staging site</h1>

	<p>
		<strong>Deployment log</strong>: <a class="toggle-log pointer">show /
			hide log</a> (<em class="deploy_log_line_count"></em>) <span
			class="spinner"></span>
	</p>

	<div
		class="plugin-card installation-log hidden col-xs-12 col-sm-8 col-md-8 col-lg-6">
		<div class="plugin-card-top"></div>
	</div>

</div>

<div class="wrap">

	<div
		class="plugin-card stop-and-explain hidden col-xs-12 col-sm-8 col-md-8 col-lg-6">

		<div class="plugin-card-top">
			<h3>Staging deployed successfully!</h3>

			<p>Your Staged Site has been made your Active Site. Your previously
				Active Pages have been moved into Staging and your previously Active
				Theme has been set as your Staged Theme.</p>

			<p>
				<b>Go to your <a href='<?php echo $url_to_site; ?>' target='_blank'>Active
						Site</a> to verify your site is working as you expected.
				</b>
			</p>

			<p>
				If you need to roll back your whole site, you can use Deploy Staging
				again. If you need to move some individual pages from Staging to
				Active, use the "Copy to Active" link in <a
					href='<?php echo $url_to_all_pages; ?>'>All Pages</a>.
			</p>

			<p>
				It is also good website management to eventually put any unneeded
				pages in the Trash for eventual deletion. This is also done from <a
					href='<?php echo $url_to_all_pages; ?>'>All Pages</a>.
			</p>
		</div>

		<div class="plugin-card-bottom">
			<div class="column-updated">
				<a class="button" href="<?php echo get_site_url(); ?>">Visit Your
					Site</a> <a class="button button-primary"
					href="<?php echo get_admin_url(); ?>">Continue to your Dashboard</a>
			</div>
		</div>
	</div>

</div>

<script type="text/javascript">
	$installation_log = jQuery(".installation-log");
	$deploy_log_line_count = jQuery(".deploy_log_line_count");
	$link_toggle_log = jQuery(".toggle-log");
	$spinner = jQuery(".spinner");
			
	// As new lines are added to the deploy_log, update the line count.
	function update_deploy_log_line_count() { 
		var line_count = $installation_log.find(".plugin-card-top").find("li").length;
		$deploy_log_line_count.html(line_count);
	}
			
	// Toggle the log as the user clicks "show / hide"
	$link_toggle_log.on("click",function() {
		$installation_log.slideToggle();
	});
</script>
