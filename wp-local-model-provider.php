<?php
/**
 * Plugin Name: WP Local Model Provider
 * Description: Provides local and cloud AI model support (Ollama) for WordPress AI Client.
 * Version: 1.1.0
 * Author: Jonathan Bossenger
 * Plugin URI: https://github.com/jonathanbossenger/wp-local-model-provider
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * License: GPL-2.0-or-later
 *
 * @package wp-local-model-provider
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'WP_LOCAL_MODEL_PROVIDER_VERSION', '1.1.0' );
define( 'WP_LOCAL_MODEL_PROVIDER_PATH', plugin_dir_path( __FILE__ ) );
define( 'WP_LOCAL_MODEL_PROVIDER_URL', plugin_dir_url( __FILE__ ) );

// Include the Composer autoloader.
if ( file_exists( WP_LOCAL_MODEL_PROVIDER_PATH . 'vendor/autoload.php' ) ) {
	require_once WP_LOCAL_MODEL_PROVIDER_PATH . 'vendor/autoload.php';
}

// Initialize the plugin when WordPress initializes.
add_action( 'init', 'wp_local_model_provider_init' );

/**
 * Initialize plugin functionality.
 *
 * @return void
 */
function wp_local_model_provider_init() {
	if ( class_exists( 'WordPress\AI_Client\AI_Client' ) ) {
		\WordPress\AI_Client\AI_Client::init();

		// Register the Ollama provider.
		wp_local_model_provider_register_ollama();
	}
}

/**
 * Register the Ollama provider with the AI Client.
 *
 * @return void
 */
function wp_local_model_provider_register_ollama() {
	// Check if the Ollama provider class exists.
	if ( ! class_exists( 'WpLocalModelProvider\Providers\Ollama\OllamaProvider' ) ) {
		return;
	}

	try {
		// Get the provider registry from the PHP AI Client.
		$registry = \WordPress\AiClient\AiClient::defaultRegistry();

		// Register the Ollama provider.
		$registry->registerProvider( \WpLocalModelProvider\Providers\Ollama\OllamaProvider::class );

		// Set authentication based on deployment mode.
		$deployment_mode = get_option( 'wp_local_model_provider_ollama_deployment_mode', 'local' );

		if ( 'cloud' === $deployment_mode ) {
			$api_key = get_option( 'wp_local_model_provider_ollama_api_key', '' );
			if ( ! empty( $api_key ) ) {
				$auth = new \WpLocalModelProvider\Providers\Ollama\ApiKeyRequestAuthentication( $api_key );
				$registry->setProviderRequestAuthentication( 'ollama', $auth );
			}
		} else {
			// Local mode: no authentication needed.
			$no_auth = new \WpLocalModelProvider\Providers\Ollama\NoAuthRequestAuthentication();
			$registry->setProviderRequestAuthentication( 'ollama', $no_auth );
		}
	} catch ( Exception $e ) {
		// Log error if registration fails.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'Failed to register Ollama provider: ' . $e->getMessage() );
		}
	}
}

add_action( 'admin_menu', 'wp_local_model_provider_register_settings' );

/**
 * Register the Local AI Model settings page.
 *
 * @return void
 */
function wp_local_model_provider_register_settings() {
	add_options_page(
		__( 'Local AI Models', 'wp-local-model-provider' ),
		__( 'Local AI Models', 'wp-local-model-provider' ),
		'manage_options',
		'wp-local-model-provider',
		'wp_local_model_provider_settings_page'
	);
}

add_action( 'admin_init', 'wp_local_model_provider_register_settings_fields' );

/**
 * Register settings fields for Local AI Model.
 *
 * @return void
 */
function wp_local_model_provider_register_settings_fields() {
	// Register deployment mode setting.
	register_setting(
		'wp_local_model_provider',
		'wp_local_model_provider_ollama_deployment_mode',
		array(
			'type'              => 'string',
			'default'           => 'local',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	// Register API key setting.
	register_setting(
		'wp_local_model_provider',
		'wp_local_model_provider_ollama_api_key',
		array(
			'type'              => 'string',
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	// Register model selection setting.
	register_setting(
		'wp_local_model_provider',
		'wp_local_model_provider_ollama_model',
		array(
			'type'              => 'string',
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	add_settings_section(
		'wp_local_model_provider_section',
		__( 'Ollama Model Selection', 'wp-local-model-provider' ),
		'wp_local_model_provider_section_callback',
		'wp-local-model-provider'
	);

	add_settings_field(
		'wp_local_model_provider_ollama_deployment_mode',
		__( 'Deployment Mode', 'wp-local-model-provider' ),
		'wp_local_model_provider_ollama_deployment_mode_field',
		'wp-local-model-provider',
		'wp_local_model_provider_section'
	);

	add_settings_field(
		'wp_local_model_provider_ollama_api_key',
		__( 'Ollama Cloud API Key', 'wp-local-model-provider' ),
		'wp_local_model_provider_ollama_api_key_field',
		'wp-local-model-provider',
		'wp_local_model_provider_section'
	);

	add_settings_field(
		'wp_local_model_provider_ollama_model',
		__( 'Select Ollama Model', 'wp-local-model-provider' ),
		'wp_local_model_provider_ollama_model_field',
		'wp-local-model-provider',
		'wp_local_model_provider_section'
	);
}

/**
 * Settings section callback.
 *
 * @return void
 */
function wp_local_model_provider_section_callback() {
	echo '<p>' . esc_html__( 'Configure your Ollama deployment mode and select which model to use for AI-powered content generation.', 'wp-local-model-provider' ) . '</p>';
}

/**
 * Ollama deployment mode field callback.
 *
 * @return void
 */
function wp_local_model_provider_ollama_deployment_mode_field() {
	$deployment_mode = get_option( 'wp_local_model_provider_ollama_deployment_mode', 'local' );
	?>
	<select id="wp_local_model_provider_ollama_deployment_mode" name="wp_local_model_provider_ollama_deployment_mode">
		<option value="local" <?php selected( $deployment_mode, 'local' ); ?>><?php esc_html_e( 'Local (http://localhost:11434)', 'wp-local-model-provider' ); ?></option>
		<option value="cloud" <?php selected( $deployment_mode, 'cloud' ); ?>><?php esc_html_e( 'Ollama Cloud', 'wp-local-model-provider' ); ?></option>
	</select>
	<p class="description"><?php esc_html_e( 'Select whether to use a local Ollama server or Ollama Cloud.', 'wp-local-model-provider' ); ?></p>
	<?php
}

/**
 * Ollama API key field callback.
 *
 * @return void
 */
function wp_local_model_provider_ollama_api_key_field() {
	$api_key         = get_option( 'wp_local_model_provider_ollama_api_key', '' );
	$deployment_mode = get_option( 'wp_local_model_provider_ollama_deployment_mode', 'local' );
	$display_style   = 'cloud' === $deployment_mode ? '' : 'display: none;';
	?>
	<div id="wp_local_model_provider_api_key_wrapper" style="<?php echo esc_attr( $display_style ); ?>">
		<input type="password" id="wp_local_model_provider_ollama_api_key" name="wp_local_model_provider_ollama_api_key" value="<?php echo esc_attr( $api_key ); ?>" class="regular-text" />
		<p class="description"><?php esc_html_e( 'Enter your Ollama Cloud API key. Only required when using Ollama Cloud.', 'wp-local-model-provider' ); ?></p>
	</div>
	<script>
		document.addEventListener('DOMContentLoaded', function() {
			var modeSelect = document.getElementById('wp_local_model_provider_ollama_deployment_mode');
			var apiKeyWrapper = document.getElementById('wp_local_model_provider_api_key_wrapper');
			
			if (modeSelect && apiKeyWrapper) {
				modeSelect.addEventListener('change', function() {
					if (this.value === 'cloud') {
						apiKeyWrapper.style.display = '';
					} else {
						apiKeyWrapper.style.display = 'none';
					}
				});
			}
		});
	</script>
	<?php
}

/**
 * Ollama model field callback.
 *
 * @return void
 */
function wp_local_model_provider_ollama_model_field() {
	$selected_model  = get_option( 'wp_local_model_provider_ollama_model', '' );
	$deployment_mode = get_option( 'wp_local_model_provider_ollama_deployment_mode', 'local' );
	$models          = wp_local_model_provider_get_ollama_models();

	if ( is_wp_error( $models ) ) {
		echo '<p class="description" style="color: #d63638;">';
		echo '<strong>' . esc_html__( 'Error:', 'wp-local-model-provider' ) . '</strong> ';
		echo esc_html( $models->get_error_message() );
		echo '</p>';

		if ( 'cloud' === $deployment_mode ) {
			echo '<p class="description">' . esc_html__( 'Please ensure you have entered a valid Ollama Cloud API key.', 'wp-local-model-provider' ) . '</p>';
		} else {
			echo '<p class="description">' . esc_html__( 'Please ensure Ollama is running on http://localhost:11434', 'wp-local-model-provider' ) . '</p>';
		}
		return;
	}

	if ( empty( $models ) ) {
		if ( 'cloud' === $deployment_mode ) {
			echo '<p class="description">' . esc_html__( 'No Ollama Cloud models found. Please check your API key.', 'wp-local-model-provider' ) . '</p>';
		} else {
			echo '<p class="description">' . esc_html__( 'No Ollama models found. Please pull at least one model using: ollama pull llama3.2', 'wp-local-model-provider' ) . '</p>';
		}
		return;
	}

	echo '<select id="wp_local_model_provider_ollama_model" name="wp_local_model_provider_ollama_model">';
	echo '<option value="">' . esc_html__( '-- Select a model --', 'wp-local-model-provider' ) . '</option>';

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
		echo '<p class="description">' . esc_html__( 'Select which Ollama Cloud model to use for text generation.', 'wp-local-model-provider' ) . '</p>';
	} else {
		echo '<p class="description">' . esc_html__( 'Select which local Ollama model to use for text generation.', 'wp-local-model-provider' ) . '</p>';
	}
}

/**
 * Get available Ollama models.
 *
 * @return array|WP_Error Array of models or WP_Error on failure.
 */
function wp_local_model_provider_get_ollama_models() {
	// Try to get cached models first (cache for 5 minutes).
	$cached_models = get_transient( 'wp_local_model_provider_ollama_models' );
	if ( false !== $cached_models ) {
		return $cached_models;
	}

	// Get deployment mode and construct base URL accordingly.
	$deployment_mode = get_option( 'wp_local_model_provider_ollama_deployment_mode', 'local' );

	if ( 'cloud' === $deployment_mode ) {
		$base_url = apply_filters( 'wp_ai_client_ollama_cloud_base_url', 'https://api.ollama.ai' );
	} else {
		$base_url = apply_filters( 'wp_ai_client_ollama_base_url', 'http://localhost:11434' );
	}

	// Prepare request arguments.
	$args = array(
		'timeout' => 30,
	);

	// Add authorization header for cloud mode.
	if ( 'cloud' === $deployment_mode ) {
		$api_key = get_option( 'wp_local_model_provider_ollama_api_key', '' );
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
			__( 'Cannot connect to Ollama. Please ensure it is running.', 'wp-local-model-provider' )
		);
	}

	$response_code = wp_remote_retrieve_response_code( $response );
	if ( 200 !== $response_code ) {
		return new WP_Error(
			'ollama_api_error',
			sprintf(
				/* translators: %d: HTTP response code */
				__( 'Ollama API returned error code: %d', 'wp-local-model-provider' ),
				$response_code
			)
		);
	}

	$body = wp_remote_retrieve_body( $response );
	$data = json_decode( $body, true );

	if ( ! isset( $data['models'] ) || ! is_array( $data['models'] ) ) {
		return new WP_Error(
			'ollama_invalid_response',
			__( 'Invalid response from Ollama API.', 'wp-local-model-provider' )
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
	set_transient( 'wp_local_model_provider_ollama_models', $models, 5 * MINUTE_IN_SECONDS );

	return $models;
}

/**
 * Render the Local AI Model settings page.
 *
 * @return void
 */
function wp_local_model_provider_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Handle refresh action.
	if ( isset( $_GET['action'] ) && 'refresh' === $_GET['action'] && check_admin_referer( 'wp_local_model_provider_refresh_models' ) ) {
		delete_transient( 'wp_local_model_provider_ollama_models' );
		add_settings_error(
			'wp_local_model_provider_messages',
			'wp_local_model_provider_message',
			__( 'Model list refreshed successfully.', 'wp-local-model-provider' ),
			'success'
		);
	}

	// Check if settings were updated.
	if ( isset( $_GET['settings-updated'] ) ) {
		add_settings_error(
			'wp_local_model_provider_messages',
			'wp_local_model_provider_message',
			__( 'Settings saved successfully.', 'wp-local-model-provider' ),
			'success'
		);
	}

	settings_errors( 'wp_local_model_provider_messages' );
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

		<form method="post" action="options.php">
			<?php
			settings_fields( 'wp_local_model_provider' );
			do_settings_sections( 'wp-local-model-provider' );
			submit_button( __( 'Save Settings', 'wp-local-model-provider' ) );
			?>
		</form>

		<div class="card">
			<h2><?php esc_html_e( 'About Ollama Models', 'wp-local-model-provider' ); ?></h2>
			<p><?php esc_html_e( 'Ollama allows you to run large language models either locally on your computer or through Ollama Cloud.', 'wp-local-model-provider' ); ?></p>

			<h3><?php esc_html_e( 'Local Setup:', 'wp-local-model-provider' ); ?></h3>
			<ol>
				<li><?php esc_html_e( 'Install Ollama from https://ollama.com', 'wp-local-model-provider' ); ?></li>
				<li><?php esc_html_e( 'Pull a model: ollama pull llama3.2', 'wp-local-model-provider' ); ?></li>
				<li><?php esc_html_e( 'Ensure Ollama is running (it starts automatically on most systems)', 'wp-local-model-provider' ); ?></li>
				<li><?php esc_html_e( 'Select "Local" as deployment mode and choose your model', 'wp-local-model-provider' ); ?></li>
			</ol>

			<h3><?php esc_html_e( 'Ollama Cloud Setup:', 'wp-local-model-provider' ); ?></h3>
			<ol>
				<li><?php esc_html_e( 'Sign up for Ollama Cloud at https://ollama.com/cloud', 'wp-local-model-provider' ); ?></li>
				<li><?php esc_html_e( 'Get your API key from the Ollama Cloud dashboard', 'wp-local-model-provider' ); ?></li>
				<li><?php esc_html_e( 'Select "Ollama Cloud" as deployment mode and enter your API key', 'wp-local-model-provider' ); ?></li>
				<li><?php esc_html_e( 'Choose your preferred model from the available cloud models', 'wp-local-model-provider' ); ?></li>
			</ol>

			<h3><?php esc_html_e( 'Recommended Models:', 'wp-local-model-provider' ); ?></h3>
			<ul>
				<li><strong>llama3.2</strong> - <?php esc_html_e( 'Best balance of speed and quality', 'wp-local-model-provider' ); ?></li>
				<li><strong>mistral</strong> - <?php esc_html_e( 'Great for creative content', 'wp-local-model-provider' ); ?></li>
				<li><strong>codellama</strong> - <?php esc_html_e( 'Optimized for technical content', 'wp-local-model-provider' ); ?></li>
				<li><strong>phi</strong> - <?php esc_html_e( 'Fastest, good for testing', 'wp-local-model-provider' ); ?></li>
			</ul>

			<p>
				<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'options-general.php?page=wp-local-model-provider&action=refresh' ), 'wp_local_model_provider_refresh_models' ) ); ?>" class="button">
					<?php esc_html_e( 'Refresh Model List', 'wp-local-model-provider' ); ?>
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
function wp_local_model_provider_is_provider_registered( $provider_slug ) {
	if ( ! class_exists( 'WordPress\AiClient\AiClient' ) ) {
		return false;
	}

	try {
		$registry = \WordPress\AiClient\AiClient::defaultRegistry();
		// Try to get the provider metadata directory to check if registered.
		$provider_class = 'WpLocalModelProvider\Providers\\' . ucfirst( $provider_slug ) . '\\' . ucfirst( $provider_slug ) . 'Provider';
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
function wp_local_model_provider_has_settings_page() {
	return true;
}

/**
 * Get the selected model for a provider.
 *
 * @param string $provider_slug The provider slug (e.g., 'ollama').
 * @return string The selected model ID, or empty string if none selected.
 */
function wp_local_model_provider_get_selected_model( $provider_slug ) {
	$option_name = 'wp_local_model_provider_' . $provider_slug . '_model';
	return get_option( $option_name, '' );
}
