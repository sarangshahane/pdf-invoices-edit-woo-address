module.exports = function ( grunt ) {
	// Project configuration.
	grunt.initConfig( {
		pkg: grunt.file.readJSON( 'package.json' ),

		// Tasks for creating a clean zip.
		copy: {
			main: {
				options: {
					mode: true,
				},
				src: [
					'**',
					'!.git/**',
					'!.gitignore',
					'!.gitattributes',
					'!*.sh',
					'!*.zip',
					'!eslintrc.json',
					'!README.md',
					'!Gruntfile.js',
					'!package.json',
					'!package-lock.json',
					'!composer.json',
					'!composer.lock',
					'!phpcs.xml',
					'!phpcs.xml.dist',
					'!phpunit.xml.dist',
					'!node_modules/**',
					'!vendor/**',
					'!tests/**',
					'!scripts/**',
					'!config/**',
					'!tests/**',
					'!bin/**',
					'!artifact',
					'!phpstan.neon',
					'!phpstan-baseline.neon',
				],
				dest: 'pdf-invoices-edit-woo-address/',
			},
		},
		compress: {
			main: {
				options: {
					archive: 'pdf-invoices-edit-woo-address-<%= pkg.version %>.zip',
					mode: 'zip',
				},
				files: [
					{
						src: [ './pdf-invoices-edit-woo-address/**' ],
					},
				],
			},
		},
		clean: {
			main: [ 'pdf-invoices-edit-woo-address' ],
			zip: [ '*.zip' ],
		},
		// Tasks for creating a clean zip.

		// Tasks to replace the version in the plugin's files.
		bumpup: {
			options: {
				updateProps: {
					pkg: 'package.json',
				},
			},
			file: 'package.json',
		},
		replace: {
			plugin_main: {
				src: [ 'pdf-invoices-edit-woo-address.php' ],
				overwrite: true,
				replacements: [
					{
						from: /Version: \bv?(?:0|[1-9]\d*)\.(?:0|[1-9]\d*)\.(?:0|[1-9]\d*)(?:-[\da-z-A-Z-]+(?:\.[\da-z-A-Z-]+)*)?(?:\+[\da-z-A-Z-]+(?:\.[\da-z-A-Z-]+)*)?\b/g,
						to: 'Version: <%= pkg.version %>',
					},
				],
			},
			plugin_const: {
				src: [ 'classes/class-pdf-iewa-loader-loader.php' ],
				overwrite: true,
				replacements: [
					{
						from: /PDF_IEWA_VER', '.*?'/g,
						to: "PDF_IEWA_VER', '<%= pkg.version %>'",
					},
				],
			},
			stable_tag: {
				src: [ 'readme.md' ],
				overwrite: true,
				replacements: [
					{
						from: /Stable tag:\ .*/g,
						to: 'Stable tag: <%= pkg.version %>',
					},
				],
			},
			plugin_function_comment: {
				src: [
					'*.php',
					'**/*.php',
					'!node_modules/**',
					'!php-tests/**',
					'!bin/**',
				],
				overwrite: true,
				replacements: [
					{
						from: 'x.x.x',
						to: '<%=pkg.version %>',
					},
				],
			},
		},
		// Tasks to replace the version in the plugin's files.

		// Tasks to replace the text-domains and create a .POT file.
		makepot: {
			target: {
				options: {
					domainPath: '/',
					mainFile: 'pdf-invoices-edit-woo-address.php',
					potFilename: 'languages/pdf-invoices-edit-woo-address.pot',
					exclude: [ 'node_modules/.*' ],
					potHeaders: {
						poedit: true,
						'x-poedit-keywordslist': true,
					},
					type: 'wp-plugin',
					updateTimestamp: true,
				},
			},
		},
		addtextdomain: {
			options: {
				textdomain: 'pdf-invoices-edit-woo-address',
				updateDomains: true,
			},
			target: {
				files: {
					src: [
						'*.php',
						'**/*.php',
						'!node_modules/**',
						'!vendor/**',
						'!php-tests/**',
						'!bin/**',
					],
				},
			},
		},
		// Tasks to replace the text-domains and create a .POT file.

	} );

	/* Load Tasks */
	grunt.loadNpmTasks( 'grunt-contrib-copy' );
	grunt.loadNpmTasks( 'grunt-contrib-compress' );
	grunt.loadNpmTasks( 'grunt-contrib-clean' );

	grunt.loadNpmTasks( 'grunt-bumpup' );
	grunt.loadNpmTasks( 'grunt-text-replace' );

	grunt.loadNpmTasks( 'grunt-wp-i18n' );
	grunt.registerTask( 'textdomain', [ 'addtextdomain' ] );
	grunt.registerTask( 'i18n', [ 'addtextdomain', 'makepot' ] );

	/* Register task started */
	grunt.registerTask( 'release', [
		'clean:zip',
		'copy',
		'compress',
		'clean:main',
	] );

	// Bump Version - `grunt version-bump --ver=<version-number>`
	grunt.registerTask( 'version-bump', function ( ver ) {
		var newVersion = grunt.option( 'ver' );
		if ( newVersion ) {
			newVersion = newVersion ? newVersion : 'patch';

			grunt.task.run( 'bumpup:' + newVersion );
			grunt.task.run( 'replace' );
		}
	} );
};
