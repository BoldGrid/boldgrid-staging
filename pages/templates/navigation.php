<script id="tmpl-boldgrid-staging-navigation" type="text/template">
	<{{data.tag}} class="nav-tab-wrapper">
		<a href="{{data.active_href}}" class="nav-tab <# if( '1' != data.is_staging ) { #>nav-tab-active<# } #>">{{data.active_text}}</a>
		<a href="{{data.staging_href}}" class="nav-tab <# if( '1' == data.is_staging ) { #>nav-tab-active<# } #>">{{data.staging_text}}</a>
	</{{data.tag}}>
</script>