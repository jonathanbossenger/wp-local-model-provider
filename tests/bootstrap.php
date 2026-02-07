<?php
/**
 * PHPUnit bootstrap file for WP Ollama Model Provider tests.
 *
 * @package wp-ollama-model-provider
 */

// Load Composer autoloader.
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Define test environment constants.
if ( ! defined( 'WP_OLLAMA_MODEL_PROVIDER_TEST' ) ) {
	define( 'WP_OLLAMA_MODEL_PROVIDER_TEST', true );
}

// For WordPress integration tests, you would typically load the WordPress test library here.
// This would require setting up wp-tests-lib and configuring the test database.
// For now, we provide basic autoloading for unit tests of the plugin classes.
