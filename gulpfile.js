var gulp   = require( 'gulp' ),
	inject = require( 'gulp-inject-string' ),
	readme = require( 'gulp-readme-to-markdown' );

gulp.task( 'readme', function() {
	var badges = [
		'[![License](https://img.shields.io/badge/license-GPL--2.0%2B-orange.svg)](https://raw.githubusercontent.com/BoldGrid/boldgrid-inspirations/master/LICENSE)',
		'[![PHP Version](https://img.shields.io/badge/PHP-5.3%2B-blue.svg)](https://php.net)',
	];
	gulp.src( ['readme.txt'] )
		.pipe( readme() )
		.pipe( inject.prepend( badges.join('\n') + '\n\n' ) )
		.pipe( gulp.dest( '.' ) );
});

gulp.task( 'default', ['readme'] );
