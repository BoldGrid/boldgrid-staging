<?php

// Prevent direct calls
if ( ! defined( 'WPINC' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

$link_to_tutorial = sprintf(
	'<a href="%s" target="_blank">%s</a>',
	'https://www.boldgrid.com/support/getting-to-know-boldgrid/understanding-active-vs-staging-in-boldgrid/',
	esc_html__( 'click here', 'boldgrid-staging' )
);
?>

<div class='wrap'>

	<h1>Staging Site deployment</h1>

	<p>
		This page, <strong>Deploy Staging</strong>, helps you swap your <em>active
			site</em> and your <em>staging site</em>.
	</p>

	<p>It appears, however, that you have not configured any pages for your
		Staging site.</p>

	<p>To learn more about BoldGrid Staging, <?php echo $link_to_tutorial; ?> to view our Staging tutorials.</p>

</div>