<?php
/**
 * Plugin Name: WP Ollama Model Provider
 * Description: Provides local and cloud AI model support (Ollama) for WordPress AI Client.
 * Version: 1.1.0
 * Author: Jonathan Bossenger
 * Plugin URI: https://github.com/jonathanbossenger/wp-ollama-model-provider
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * License: GPL-2.0-or-later
 *
 * @package wp-ollama-model-provider
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'WP_OLLAMA_MODEL_PROVIDER_VERSION', '1.1.0' );
define( 'WP_OLLAMA_MODEL_PROVIDER_PATH', plugin_dir_path( __FILE__ ) );
define( 'WP_OLLAMA_MODEL_PROVIDER_URL', plugin_dir_url( __FILE__ ) );

// Include the Composer autoloader.
if ( file_exists( WP_OLLAMA_MODEL_PROVIDER_PATH . 'vendor/autoload.php' ) ) {
	require_once WP_OLLAMA_MODEL_PROVIDER_PATH . 'vendor/autoload.php';
}

// Initialize the plugin when WordPress initializes.
add_action( 'init', 'wp_ollama_model_provider_init' );

/**
 * Initialize plugin functionality.
 *
 * @return void
 */
function wp_ollama_model_provider_init() {
	if ( class_exists( 'WordPress\AI_Client\AI_Client' ) ) {
		\WordPress\AI_Client\AI_Client::init();

		// Register the Ollama provider.
		wp_ollama_model_provider_register_ollama();
	}
}

/**
 * Register the Ollama provider with the AI Client.
 *
 * @return void
 */
function wp_ollama_model_provider_register_ollama() {
	// Check if the Ollama provider class exists.
	if ( ! class_exists( 'WpOllamaModelProvider\Providers\Ollama\OllamaProvider' ) ) {
		return;
	}

	try {
		// Get the provider registry from the PHP AI Client.
		$registry = \WordPress\AiClient\AiClient::defaultRegistry();

		// Register the Ollama provider.
		$registry->registerProvider( \WpOllamaModelProvider\Providers\Ollama\OllamaProvider::class );

		// Set authentication based on deployment mode.
		$deployment_mode = get_option( 'wp_ollama_model_provider_ollama_deployment_mode', 'local' );

		if ( 'cloud' === $deployment_mode ) {
			$api_key = get_option( 'wp_ollama_model_provider_ollama_api_key', '' );
			if ( ! empty( $api_key ) ) {
				$auth = new \WpOllamaModelProvider\Providers\Ollama\ApiKeyRequestAuthentication( $api_key );
				$registry->setProviderRequestAuthentication( 'ollama', $auth );
			}
		} else {
			// Local mode: no authentication needed.
			$no_auth = new \WpOllamaModelProvider\Providers\Ollama\NoAuthRequestAuthentication();
			$registry->setProviderRequestAuthentication( 'ollama', $no_auth );
		}
	} catch ( Exception $e ) {
		// Log error if registration fails.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'Failed to register Ollama provider: ' . $e->getMessage() );
		}
	}
}

add_action( 'admin_menu', 'wp_ollama_model_provider_register_settings' );

/**
 * Register the Ollama AI Model settings page.
 *
 * @return void
 */
function wp_ollama_model_provider_register_settings() {
	add_options_page(
		__( 'Ollama AI Models', 'wp-ollama-model-provider' ),
		__( 'Ollama AI Models', 'wp-ollama-model-provider' ),
		'manage_options',
		'wp-ollama-model-provider',
		'wp_ollama_model_provider_settings_page'
	);
}

add_action( 'admin_init', 'wp_ollama_model_provider_register_settings_fields' );

/**
 * Register settings fields for Ollama AI Model.
 *
 * @return void
 */
function wp_ollama_model_provider_register_settings_fields() {
	// Register deployment mode setting.
	register_setting(
		'wp_ollama_model_provider',
		'wp_ollama_model_provider_ollama_deployment_mode',
		array(
			'type'              => 'string',
			'default'           => 'local',
			'sanitize_callback' => 'wp_ollama_model_provider_sanitize_deployment_mode',
		)
	);

	// Register API key setting.
	register_setting(
		'wp_ollama_model_provider',
		'wp_ollama_model_provider_ollama_api_key',
		array(
			'type'              => 'string',
			'default'           => '',
			'sanitize_callback' => 'wp_ollama_model_provider_sanitize_api_key',
		)
	);

	// Register model selection setting.
	register_setting(
		'wp_ollama_model_provider',
		'wp_ollama_model_provider_ollama_model',
		array(
			'type'              => 'string',
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	add_settings_section(
		'wp_ollama_model_provider_section',
		__( 'Ollama Model Selection', 'wp-ollama-model-provider' ),
		'wp_ollama_model_provider_section_callback',
		'wp-ollama-model-provider'
	);

	add_settings_field(
		'wp_ollama_model_provider_ollama_deployment_mode',
		__( 'Deployment Mode', 'wp-ollama-model-provider' ),
		'wp_ollama_model_provider_ollama_deployment_mode_field',
		'wp-ollama-model-provider',
		'wp_ollama_model_provider_section'
	);

	add_settings_field(
		'wp_ollama_model_provider_ollama_api_key',
		__( 'Ollama Cloud API Key', 'wp-ollama-model-provider' ),
		'wp_ollama_model_provider_ollama_api_key_field',
		'wp-ollama-model-provider',
		'wp_ollama_model_provider_section'
	);

	add_settings_field(
		'wp_ollama_model_provider_ollama_model',
		__( 'Select Ollama Model', 'wp-ollama-model-provider' ),
		'wp_ollama_model_provider_ollama_model_field',
		'wp-ollama-model-provider',
		'wp_ollama_model_provider_section'
	);
}

/**
 * Sanitize deployment mode and clear cache when changed.
 *
 * @param string $value The value to sanitize.
 * @return string The sanitized value.
 */
function wp_ollama_model_provider_sanitize_deployment_mode( $value ) {
	$old_value = get_option( 'wp_ollama_model_provider_ollama_deployment_mode', 'local' );
	$new_value = sanitize_text_field( $value );

	// Clear model cache if deployment mode changed.
	if ( $old_value !== $new_value ) {
		delete_transient( 'wp_ollama_model_provider_ollama_models' );
	}

	return $new_value;
}

/**
 * Sanitize API key and clear cache when changed.
 *
 * @param string $value The value to sanitize.
 * @return string The sanitized value.
 */
function wp_ollama_model_provider_sanitize_api_key( $value ) {
	$old_value = get_option( 'wp_ollama_model_provider_ollama_api_key', '' );
	$new_value = sanitize_text_field( $value );

	// Clear model cache if API key changed.
	if ( $old_value !== $new_value ) {
		delete_transient( 'wp_ollama_model_provider_ollama_models' );
	}

	return $new_value;
}

/**
 * Settings section callback.
 *
 * @return void
 */
function wp_ollama_model_provider_section_callback() {
	echo '<p>' . esc_html__( 'Configure your Ollama deployment mode and select which model to use for AI-powered content generation.', 'wp-ollama-model-provider' ) . '</p>';
}

/**
 * Ollama deployment mode field callback.
 *
 * @return void
 */
function wp_ollama_model_provider_ollama_deployment_mode_field() {
	$deployment_mode = get_option( 'wp_ollama_model_provider_ollama_deployment_mode', 'local' );
	?>
<select id="wp_ollama_model_provider_ollama_deployment_mode" name="wp_ollama_model_provider_ollama_deployment_mode">
<option value="local" <?php selected( $deployment_mode, 'local' ); ?>><?php esc_html_e( 'Local (http://localhost:11434)', 'wp-ollama-model-provider' ); ?></option>
<option value="cloud" <?php selected( $deployment_mode, 'cloud' ); ?>><?php esc_html_e( 'Ollama Cloud', 'wp-ollama-model-provider' ); ?></option>
</select>
<p class="description"><?php esc_html_e( 'Select whether to use a local Ollama server or Ollama Cloud.', 'wp-ollama-model-provider' ); ?></p>
	<?php
}

/**
 * Ollama API key field callback.
 *
 * @return void
 */
function wp_ollama_model_provider_ollama_api_key_field() {
	$api_key         = get_option( 'wp_ollama_model_provider_ollama_api_key', '' );
	$deployment_mode = get_option( 'wp_ollama_model_provider_ollama_deployment_mode', 'local' );
	$display_style   = 'cloud' === $deployment_mode ? '' : 'display: none;';
	?>
<div id="wp_ollama_model_provider_api_key_wrapper" style="<?php echo esc_attr( $display_style ); ?>">
<input type="password" id="wp_ollama_model_provider_ollama_api_key" name="wp_ollama_model_provider_ollama_api_key" value="<?php echo esc_attr( $api_key ); ?>" class="regular-text" />
<p class="description"><?php esc_html_e( 'Enter your Ollama Cloud API key. Only required when using Ollama Cloud.', 'wp-ollama-model-provider' ); ?></p>
</div>
	<?php
}

add_action( 'admin_enqueue_scripts', 'wp_ollama_model_provider_enqueue_admin_scripts' );

/**
 * Enqueue admin scripts.
 *
 * @param string $hook_suffix The current admin page hook suffix.
 * @return void
 */
function wp_ollama_model_provider_enqueue_admin_scripts( $hook_suffix ) {
	// Only load on our settings page.
	if ( 'settings_page_wp-ollama-model-provider' !== $hook_suffix ) {
		return;
	}

	// Inline script to toggle API key field visibility and refresh models.
	$script = "
document.addEventListener('DOMContentLoaded', function() {
var modeSelect = document.getElementById('wp_ollama_model_provider_ollama_deployment_mode');
var apiKeyWrapper = document.getElementById('wp_ollama_model_provider_api_key_wrapper');
var apiKeyInput = document.getElementById('wp_ollama_model_provider_ollama_api_key');
var modelWrapper = document.getElementById('wp_ollama_model_provider_model_wrapper');

if (modeSelect) {
modeSelect.addEventListener('change', function() {
var isCloud = this.value === 'cloud';

// Toggle API key field visibility
if (apiKeyWrapper) {
apiKeyWrapper.style.display = isCloud ? '' : 'none';
}

// Refresh model list
if (modelWrapper) {
refreshModelList(this.value, apiKeyInput ? apiKeyInput.value : '');
}
});
}

// Helper function to escape HTML (works for both text content and attributes)
function escapeHtml(text) {
var div = document.createElement('div');
div.textContent = text;
return div.innerHTML;
}

function refreshModelList(deploymentMode, apiKey) {
// Show loading state
var originalContent = modelWrapper.innerHTML;
modelWrapper.innerHTML = '<p class=\"description\">" . esc_js( __( 'Loading models...', 'wp-ollama-model-provider' ) ) . "</p>';

// Make AJAX request
var data = new FormData();
data.append('action', 'wp_ollama_model_provider_get_models');
data.append('deployment_mode', deploymentMode);
data.append('api_key', apiKey);
data.append('nonce', '" . wp_create_nonce( 'wp_ollama_model_provider_get_models' ) . "');

fetch(ajaxurl, {
method: 'POST',
body: data
})
.then(response => response.json())
.then(result => {
if (result.success) {
// Update model select with new options
var html = '<select id=\"wp_ollama_model_provider_ollama_model\" name=\"wp_ollama_model_provider_ollama_model\">';
html += '<option value=\"\">" . esc_js( __( '-- Select a model --', 'wp-ollama-model-provider' ) ) . "</option>';

if (result.data.models && result.data.models.length > 0) {
result.data.models.forEach(function(model) {
var safeId = escapeHtml(model.id || '');
var safeName = escapeHtml(model.name || '');
html += '<option value=\"' + safeId + '\">' + safeName + '</option>';
});
}

html += '</select>';
html += '<p class=\"description\">' + escapeHtml(result.data.description || '') + '</p>';

modelWrapper.innerHTML = html;
} else {
// Show error message
var errorHtml = '<p class=\"description\" style=\"color: #d63638;\">';
errorHtml += '<strong>" . esc_js( __( 'Error:', 'wp-ollama-model-provider' ) ) . "</strong> ';
errorHtml += escapeHtml(result.data.message || '');
errorHtml += '</p>';
if (result.data.help) {
errorHtml += '<p class=\"description\">' + escapeHtml(result.data.help) + '</p>';
}

modelWrapper.innerHTML = errorHtml;
}
})
.catch(error => {
// Restore original content on error
modelWrapper.innerHTML = originalContent;
console.error('Error fetching models:', error);
});
}
});
";

	wp_add_inline_script( 'jquery', $script );
}

add_action( 'wp_ajax_wp_ollama_model_provider_get_models', 'wp_ollama_model_provider_ajax_get_models' );

/**
 * AJAX handler to get models for a specific deployment mode.
 *
 * Note: This endpoint should only be accessed over HTTPS to protect API keys.
 * WordPress admin is typically served over HTTPS in production environments.
 *
 * @return void
 */
function wp_ollama_model_provider_ajax_get_models() {
	// Verify nonce.
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wp_ollama_model_provider_get_models' ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Security check failed.', 'wp-ollama-model-provider' ),
				'help'    => '',
			)
		);
	}

	// Check user permissions.
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Insufficient permissions.', 'wp-ollama-model-provider' ),
				'help'    => '',
			)
		);
	}

	$deployment_mode = isset( $_POST['deployment_mode'] ) ? sanitize_text_field( wp_unslash( $_POST['deployment_mode'] ) ) : 'local';
	$api_key         = isset( $_POST['api_key'] ) ? trim( wp_unslash( $_POST['api_key'] ) ) : '';

	// Validate API key if cloud mode.
	if ( 'cloud' === $deployment_mode && empty( $api_key ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'API key is required for Ollama Cloud.', 'wp-ollama-model-provider' ),
				'help'    => __( 'Please enter your Ollama Cloud API key.', 'wp-ollama-model-provider' ),
			)
		);
	}

	// Get base URL based on deployment mode.
	if ( 'cloud' === $deployment_mode ) {
		$base_url = apply_filters( 'wp_ai_client_ollama_cloud_base_url', 'https://ollama.com' );
	} else {
		$base_url = apply_filters( 'wp_ai_client_ollama_base_url', 'http://localhost:11434' );
	}

	// Prepare request arguments.
	$args = array(
		'timeout' => 30,
	);

	// Add authorization header for cloud mode.
	if ( 'cloud' === $deployment_mode && ! empty( $api_key ) ) {
		$args['headers'] = array(
			'Authorization' => 'Bearer ' . $api_key,
		);
	}

	// Fetch models from Ollama API.
	$response = wp_remote_get( $base_url . '/api/tags', $args );

	if ( is_wp_error( $response ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Cannot connect to Ollama. Please ensure it is running.', 'wp-ollama-model-provider' ),
				'help'    => 'cloud' === $deployment_mode
				? __( 'Please ensure you have entered a valid Ollama Cloud API key.', 'wp-ollama-model-provider' )
				: __( 'Please ensure Ollama is running on http://localhost:11434', 'wp-ollama-model-provider' ),
			)
		);
	}

	$response_code = wp_remote_retrieve_response_code( $response );
	if ( 200 !== $response_code ) {
		wp_send_json_error(
			array(
				'message' => sprintf(
				/* translators: %d: HTTP response code */
					__( 'Ollama API returned error code: %d', 'wp-ollama-model-provider' ),
					$response_code
				),
				'help'    => 'cloud' === $deployment_mode
				? __( 'Please ensure you have entered a valid Ollama Cloud API key.', 'wp-ollama-model-provider' )
				: __( 'Please ensure Ollama is running on http://localhost:11434', 'wp-ollama-model-provider' ),
			)
		);
	}

	$body = wp_remote_retrieve_body( $response );
	$data = json_decode( $body, true );

	if ( ! isset( $data['models'] ) || ! is_array( $data['models'] ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Invalid response from Ollama API.', 'wp-ollama-model-provider' ),
				'help'    => '',
			)
		);
	}

	// Format models for display.
	$models = array();
	foreach ( $data['models'] as $model ) {
		if ( isset( $model['name'] ) ) {
			$models[] = array(
				'id'   => $model['name'],
				'name' => $model['name'],
			);
		}
	}

	if ( empty( $models ) ) {
		wp_send_json_error(
			array(
				'message' => 'cloud' === $deployment_mode
				? __( 'No Ollama Cloud models found. Please check your API key.', 'wp-ollama-model-provider' )
				: __( 'No Ollama models found. Please pull at least one model using: ollama pull llama3.2', 'wp-ollama-model-provider' ),
				'help'    => '',
			)
		);
	}

	// Send success response.
	wp_send_json_success(
		array(
			'models'      => $models,
			'description' => 'cloud' === $deployment_mode
			? __( 'Select which Ollama Cloud model to use for text generation.', 'wp-ollama-model-provider' )
			: __( 'Select which local Ollama model to use for text generation.', 'wp-ollama-model-provider' ),
		)
	);
}

/**
 * Ollama model field callback.
 *
 * @return void
 */
function wp_ollama_model_provider_ollama_model_field() {
	$selected_model  = get_option( 'wp_ollama_model_provider_ollama_model', '' );
	$deployment_mode = get_option( 'wp_ollama_model_provider_ollama_deployment_mode', 'local' );
	$models          = wp_ollama_model_provider_get_ollama_models();

	echo '<div id="wp_ollama_model_provider_model_wrapper">';

	if ( is_wp_error( $models ) ) {
		echo '<p class="description" style="color: #d63638;">';
		echo '<strong>' . esc_html__( 'Error:', 'wp-ollama-model-provider' ) . '</strong> ';
		echo esc_html( $models->get_error_message() );
		echo '</p>';

		if ( 'cloud' === $deployment_mode ) {
			echo '<p class="description">' . esc_html__( 'Please ensure you have entered a valid Ollama Cloud API key.', 'wp-ollama-model-provider' ) . '</p>';
		} else {
			echo '<p class="description">' . esc_html__( 'Please ensure Ollama is running on http://localhost:11434', 'wp-ollama-model-provider' ) . '</p>';
		}
		echo '</div>';
		return;
	}

	if ( empty( $models ) ) {
		if ( 'cloud' === $deployment_mode ) {
			echo '<p class="description">' . esc_html__( 'No Ollama Cloud models found. Please check your API key.', 'wp-ollama-model-provider' ) . '</p>';
		} else {
			echo '<p class="description">' . esc_html__( 'No Ollama models found. Please pull at least one model using: ollama pull llama3.2', 'wp-ollama-model-provider' ) . '</p>';
		}
		echo '</div>';
		return;
	}

	echo '<select id="wp_ollama_model_provider_ollama_model" name="wp_ollama_model_provider_ollama_model">';
	echo '<option value="">' . esc_html__( '-- Select a model --', 'wp-ollama-model-provider' ) . '</option>';

	foreach ( $models as $model ) {
		$selected = selected( $selected_model, $model['id'], false );
		printf(
			'<option value="%s"%s>%s</option>',
			esc_attr( $model['id'] ),
			$selected,
			esc_html( $model['name'] )
		);
	}

	echo '</select>';

	if ( 'cloud' === $deployment_mode ) {
		echo '<p class="description">' . esc_html__( 'Select which Ollama Cloud model to use for text generation.', 'wp-ollama-model-provider' ) . '</p>';
	} else {
		echo '<p class="description">' . esc_html__( 'Select which local Ollama model to use for text generation.', 'wp-ollama-model-provider' ) . '</p>';
	}

	echo '</div>';
}

/**
 * Get available Ollama models.
 *
 * @return array|WP_Error Array of models or WP_Error on failure.
 */
function wp_ollama_model_provider_get_ollama_models() {
	// Try to get cached models first (cache for 5 minutes).
	$cached_models = get_transient( 'wp_ollama_model_provider_ollama_models' );
	if ( false !== $cached_models ) {
		return $cached_models;
	}

	// Get deployment mode and construct base URL accordingly.
	$deployment_mode = get_option( 'wp_ollama_model_provider_ollama_deployment_mode', 'local' );

	if ( 'cloud' === $deployment_mode ) {
		$base_url = apply_filters( 'wp_ai_client_ollama_cloud_base_url', 'https://ollama.com' );
	} else {
		$base_url = apply_filters( 'wp_ai_client_ollama_base_url', 'http://localhost:11434' );
	}

	// Prepare request arguments.
	$args = array(
		'timeout' => 30,
	);

	// Add authorization header for cloud mode.
	if ( 'cloud' === $deployment_mode ) {
		$api_key = get_option( 'wp_ollama_model_provider_ollama_api_key', '' );
		if ( ! empty( $api_key ) ) {
			$args['headers'] = array(
				'Authorization' => 'Bearer ' . $api_key,
			);
		}
	}

	// Fetch models from Ollama API.
	$response = wp_remote_get( $base_url . '/api/tags', $args );

	if ( is_wp_error( $response ) ) {
		return new WP_Error(
			'ollama_connection_error',
			__( 'Cannot connect to Ollama. Please ensure it is running.', 'wp-ollama-model-provider' )
		);
	}

	$response_code = wp_remote_retrieve_response_code( $response );
	if ( 200 !== $response_code ) {
		return new WP_Error(
			'ollama_api_error',
			sprintf(
			/* translators: %d: HTTP response code */
				__( 'Ollama API returned error code: %d', 'wp-ollama-model-provider' ),
				$response_code
			)
		);
	}

	$body = wp_remote_retrieve_body( $response );
	$data = json_decode( $body, true );

	if ( ! isset( $data['models'] ) || ! is_array( $data['models'] ) ) {
		return new WP_Error(
			'ollama_invalid_response',
			__( 'Invalid response from Ollama API.', 'wp-ollama-model-provider' )
		);
	}

	// Format models for display.
	$models = array();
	foreach ( $data['models'] as $model ) {
		if ( isset( $model['name'] ) ) {
			$models[] = array(
				'id'   => $model['name'],
				'name' => $model['name'],
			);
		}
	}

	// Cache the results.
	set_transient( 'wp_ollama_model_provider_ollama_models', $models, 5 * MINUTE_IN_SECONDS );

	return $models;
}

/**
 * Render the Ollama AI Model settings page.
 *
 * @return void
 */
function wp_ollama_model_provider_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Handle refresh action.
	if ( isset( $_GET['action'] ) && 'refresh' === $_GET['action'] && check_admin_referer( 'wp_ollama_model_provider_refresh_models' ) ) {
		delete_transient( 'wp_ollama_model_provider_ollama_models' );
		add_settings_error(
			'wp_ollama_model_provider_messages',
			'wp_ollama_model_provider_message',
			__( 'Model list refreshed successfully.', 'wp-ollama-model-provider' ),
			'success'
		);
	}

	// Check if settings were updated.
	if ( isset( $_GET['settings-updated'] ) ) {
		add_settings_error(
			'wp_ollama_model_provider_messages',
			'wp_ollama_model_provider_message',
			__( 'Settings saved successfully.', 'wp-ollama-model-provider' ),
			'success'
		);
	}

	settings_errors( 'wp_ollama_model_provider_messages' );
	?>
<div class="wrap">
<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

<form method="post" action="options.php">
	<?php
	settings_fields( 'wp_ollama_model_provider' );
	do_settings_sections( 'wp-ollama-model-provider' );
	submit_button( __( 'Save Settings', 'wp-ollama-model-provider' ) );
	?>
</form>

<div class="card">
<h2><?php esc_html_e( 'About Ollama Models', 'wp-ollama-model-provider' ); ?></h2>
<p><?php esc_html_e( 'Ollama allows you to run large language models either locally on your computer or through Ollama Cloud.', 'wp-ollama-model-provider' ); ?></p>

<h3><?php esc_html_e( 'Local Setup:', 'wp-ollama-model-provider' ); ?></h3>
<ol>
<li><?php esc_html_e( 'Install Ollama from https://ollama.com', 'wp-ollama-model-provider' ); ?></li>
<li><?php esc_html_e( 'Pull a model: ollama pull llama3.2', 'wp-ollama-model-provider' ); ?></li>
<li><?php esc_html_e( 'Ensure Ollama is running (it starts automatically on most systems)', 'wp-ollama-model-provider' ); ?></li>
<li><?php esc_html_e( 'Select "Local" as deployment mode and choose your model', 'wp-ollama-model-provider' ); ?></li>
</ol>

<h3><?php esc_html_e( 'Ollama Cloud Setup:', 'wp-ollama-model-provider' ); ?></h3>
<ol>
<li><?php esc_html_e( 'Sign up for Ollama Cloud at https://ollama.com/cloud', 'wp-ollama-model-provider' ); ?></li>
<li><?php esc_html_e( 'Get your API key from the Ollama Cloud dashboard', 'wp-ollama-model-provider' ); ?></li>
<li><?php esc_html_e( 'Select "Ollama Cloud" as deployment mode and enter your API key', 'wp-ollama-model-provider' ); ?></li>
<li><?php esc_html_e( 'Choose your preferred model from the available cloud models', 'wp-ollama-model-provider' ); ?></li>
</ol>

<h3><?php esc_html_e( 'Recommended Models:', 'wp-ollama-model-provider' ); ?></h3>
<ul>
<li><strong>llama3.2</strong> - <?php esc_html_e( 'Best balance of speed and quality', 'wp-ollama-model-provider' ); ?></li>
<li><strong>mistral</strong> - <?php esc_html_e( 'Great for creative content', 'wp-ollama-model-provider' ); ?></li>
<li><strong>codellama</strong> - <?php esc_html_e( 'Optimized for technical content', 'wp-ollama-model-provider' ); ?></li>
<li><strong>phi</strong> - <?php esc_html_e( 'Fastest, good for testing', 'wp-ollama-model-provider' ); ?></li>
</ul>

<p>
<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'options-general.php?page=wp-ollama-model-provider&action=refresh' ), 'wp_ollama_model_provider_refresh_models' ) ); ?>" class="button">
	<?php esc_html_e( 'Refresh Model List', 'wp-ollama-model-provider' ); ?>
</a>
</p>
</div>
</div>
	<?php
}

// Public API functions.

/**
 * Check if a provider is registered.
 *
 * @param string $provider_slug The provider slug (e.g., 'ollama').
 * @return bool True if provider is registered, false otherwise.
 */
function wp_ollama_model_provider_is_provider_registered( $provider_slug ) {
	if ( ! class_exists( 'WordPress\AiClient\AiClient' ) ) {
		return false;
	}

	try {
		$registry = \WordPress\AiClient\AiClient::defaultRegistry();
		// Try to get the provider metadata directory to check if registered.
		$provider_class = 'WpOllamaModelProvider\Providers\\' . ucfirst( $provider_slug ) . '\\' . ucfirst( $provider_slug ) . 'Provider';
		return class_exists( $provider_class );
	} catch ( Exception $e ) {
		return false;
	}
}

/**
 * Check if the settings page exists.
 *
 * @return bool True if settings page exists, false otherwise.
 */
function wp_ollama_model_provider_has_settings_page() {
	return true;
}

/**
 * Get the selected model for a provider.
 *
 * @param string $provider_slug The provider slug (e.g., 'ollama').
 * @return string The selected model ID, or empty string if none selected.
 */
function wp_ollama_model_provider_get_selected_model( $provider_slug ) {
	$option_name = 'wp_ollama_model_provider_' . $provider_slug . '_model';
	return get_option( $option_name, '' );
}
