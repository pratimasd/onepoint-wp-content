<?php
/**
 * Must-use plugin: log fatal errors to wp-content/fatal-error.log so you can read the real error when wp-admin shows "critical error".
 * Remove this file or the mu-plugins folder after fixing the issue.
 */
if ( ! defined( 'ABSPATH' ) ) {
	return;
}
register_shutdown_function( function () {
	$err = error_get_last();
	if ( $err && in_array( $err['type'], array( E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR ), true ) ) {
		$log = WP_CONTENT_DIR . '/fatal-error.log';
		$msg = date( 'Y-m-d H:i:s' ) . ' [' . $err['type'] . '] ' . $err['message'] . ' in ' . $err['file'] . ' on line ' . $err['line'] . "\n";
		file_put_contents( $log, $msg, FILE_APPEND | LOCK_EX );
	}
});
