/**
 * Require Dependencies
 */
var gulp = require( 'gulp' ),
	sass = require( 'gulp-ruby-sass' ),
	autoprefixer = require( 'gulp-autoprefixer' ),
	minifycss = require( 'gulp-minify-css' ),
	rtlcss  = require('gulp-rtlcss'),
	rename = require( 'gulp-rename' ),
	include = require( 'gulp-include' ),
	uglify = require( 'gulp-uglify' ),
	notify = require( 'gulp-notify' ),
	growl = require( 'gulp-notify-growl' ),
	jscs = require('gulp-jscs'),
	jshint = require( 'gulp-jshint' ),
	svgstore = require('gulp-svgstore'),
	svgmin = require('gulp-svgmin');


gulp.task('svgstore', function () {
    return gulp
        .src('_private/svg/*.svg')
        .pipe(svgmin())
        .pipe(svgstore())
        .pipe(gulp.dest('assets/svg'));
});

/**
 * JSCS
 * Runs on 'Build'
 * Runs "pretty" js code checks
 */
gulp.task('jscs', function() {
    gulp.src( '_private/js/app/*.js' )
        .pipe( jscs() )
        .on('error',notify.onError({
			message: '<%= error.message %>',
			sound: 'Funk',
			title: 'JSCS Error'
        } ) );

        /* Alternatively for Windows:
        .on('error',notify.onError({
        	title: 'JSCS',
			message: '<%= error.message %>',
			sound: true,
			notifier: growlNotifier
        } ) );
        */
});

/**
 * Lint
 * Runs js linter checks
 */
gulp.task('lint', function() {
    gulp.src('_private/js/app/*.js')
        .pipe(jshint('.jshintrc'))
        .pipe(jshint.reporter('jshint-stylish'))
        .pipe(jshint.reporter('fail'))
        .on('error',notify.onError({
			message: '<%= error.message %>',
			sound: 'Funk',
			title: 'JS Hint Error'
        }));

        /* Alternatively for Windows:
        .on('error',notify.onError({
        	title: 'JSLint',
			message: '<%= error.message %>',
			sound: true,
			notifier: growlNotifier
        } ) );
        */
});

/**
 * JS build
 * Runs JSCS and Linter before process-scripts
 */
gulp.task('build', ['jscs', 'lint'], function() {

    gulp.src('/')
        //pipe through other tasks such as sass or coffee compile tasks
        .pipe(notify({
            title: 'Task Builder',
            message: 'Successfully built application'
        }))

});

/**
 * Rebuild task to do everything in one fell swoop
 */
gulp.task('rebuild',['process-scripts','process-frontend-styles','process-frontend-certificates-styles','process-admin-styles','process-builder-styles','process-rtl-styles'],function(){});

/**
 * Compile front end SASS files
 */
gulp.task( 'process-frontend-styles', function () {

	return sass( '_private/scss/lifterlms.scss', {
		cacheLocation: '_private/scss/.sass-cache',
		style: 'expanded'
		})
		.pipe( autoprefixer( 'last 2 version' ) )
		.pipe( gulp.dest( 'assets/css/' ) )
		.pipe( rename( { suffix: '.min' } ) )
		.pipe( minifycss() )
		.pipe( gulp.dest( 'assets/css/') )
		.pipe(notify({
            title: 'Front End Styles',
            message: 'Successfully Built Front End Styles'
        }));

});

/**
 * Compile certificates SASS files
 */
gulp.task( 'process-frontend-certificates-styles', function () {

	return sass( '_private/scss/frontend/certificates.scss', {
		cacheLocation: '_private/scss/.sass-cache',
		style: 'expanded'
	})
			.pipe( autoprefixer( 'last 2 version' ) )
			.pipe( gulp.dest( 'assets/css/' ) )
			.pipe( rename( { suffix: '.min' } ) )
			.pipe( minifycss() )
			.pipe( gulp.dest( 'assets/css/') )
			.pipe(notify({
				title: 'Front End Certificates Styles',
				message: 'Successfully Built Front End Certificates Styles'
			}));

});

/**
 * Compile admin SASS files
 */
gulp.task( 'process-admin-styles', function () {

	return sass( '_private/scss/admin*.scss', {
		cacheLocation: '_private/scss/.sass-cache',
		style: 'expanded'
		})
		.pipe( autoprefixer( 'last 2 version' ) )
		.pipe( gulp.dest( 'assets/css/' ) )
		.pipe( rename( { suffix: '.min' } ) )
		.pipe( minifycss() )
		.pipe( gulp.dest( 'assets/css/') )
		.pipe(notify({
            title: 'Admin Styles',
            message: 'Successfully Built Admin Styles'
        }));

});

/**
 * Compile builder SCSS file
 */
gulp.task( 'process-builder-styles', function () {

	return sass( '_private/scss/builder.scss', {
		cacheLocation: '_private/scss/.sass-cache',
		style: 'expanded'
		})
		.pipe( autoprefixer( 'last 2 version' ) )
		.pipe( gulp.dest( 'assets/css/' ) )
		.pipe( rename( { suffix: '.min' } ) )
		.pipe( minifycss() )
		.pipe( gulp.dest( 'assets/css/') )
		.pipe(notify({
            title: 'Admin Styles',
            message: 'Successfully Built Admin Styles'
        }));

});


gulp.task( 'process-rtl-styles', function() {

	return gulp.src( ['assets/css/*.css'] )
		.pipe( rtlcss() )
		.pipe( rename( { suffix: '-rtl' } ) )
		.pipe( gulp.dest( 'assets/css/' ) )
		.pipe( notify({
			title: 'RTL Styles',
            message: 'Successfully Built RTL Styles'
		}));
});

/**
 * Minify JS files
 */
gulp.task( 'process-scripts', function () {

	return gulp.src( '_private/js/*.js' )
		.pipe( include() )
		.pipe( rename( { suffix: '.min' } ) )
		.pipe( uglify().on('error',notify.onError({
			message: '<%= error.message %>',
			sound: 'Funk',
			title: 'JS Uglify Error'
        } ) ) )
		.pipe( gulp.dest( 'assets/js/' ) );

});

/**
 * Gulp Watch command
 * Runs Build on js file change
 * Compiles js scripts if build passes
 *
 * Compiles Admin and Frontend scripts
 */
gulp.task( 'watch', function () {

	gulp.watch( 'assets/js/builder/**/*.js', [ 'js:builder', 'pot:js' ] );
	gulp.watch( '_private/js/**/*.js', [ 'build', 'process-scripts', 'pot:js' ] );
	gulp.watch( '_private/**/*.scss', [ 'process-admin-styles' ] );
	gulp.watch( '_private/**/*.scss', [ 'process-frontend-styles' ] );
	gulp.watch( '_private/**/*.scss', [ 'process-builder-styles' ] );


});



gulp.task('default', ['rebuild']);
