# Ollama Provider Integration Guide

This guide explains how the Ollama provider works within the WP Ollama Model Provider plugin.

## Overview

The Ollama provider enables WordPress plugins to use local Ollama models for AI generation tasks. Ollama runs as a local server (typically at `http://localhost:11434`) and requires no authentication, making it ideal for development and self-hosted scenarios.

## Prerequisites

1. **Ollama Server Running**: Ensure Ollama is installed and running locally
   ```bash
   # Check if Ollama is running
   curl http://localhost:11434/api/tags
   ```

2. **WP Ollama Model Provider Plugin**: This plugin must be installed and activated in WordPress

## Implementation Overview

### Provider Registration

The plugin automatically registers the Ollama provider during WordPress initialization in `wp-ollama-model-provider.php`:

```php
/**
 * Initialize plugin functionality.
 *
 * @return void
 */
function wp_ollama_model_provider_init() {
if ( class_exists( 'WordPress\AI_Client\AI_Client' ) ) {
\WordPress\AI_Client\AI_Client::init();

// Register the Ollama provider
wp_ollama_model_provider_register_ollama();
}
}

/**
 * Register the Ollama provider with the AI Client.
 *
 * @return void
 */
function wp_ollama_model_provider_register_ollama() {
// Check if the Ollama provider class exists
if ( ! class_exists( 'WpOllamaModelProvider\Providers\Ollama\OllamaProvider' ) ) {
return;
}

try {
// Get the provider registry from the PHP AI Client
$registry = \WordPress\AiClient\AiClient::defaultRegistry();

// Register the Ollama provider
$registry->registerProvider( \WpOllamaModelProvider\Providers\Ollama\OllamaProvider::class );

// Set no-auth authentication for Ollama (local server doesn't need API keys)
$no_auth = new \WpOllamaModelProvider\Providers\Ollama\NoAuthRequestAuthentication();
$registry->setProviderRequestAuthentication( 'ollama', $no_auth );
} catch ( Exception $e ) {
// Log error if registration fails
if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
error_log( 'Failed to register Ollama provider: ' . $e->getMessage() );
}
}
}
```

### Configure Ollama Model Selection

The plugin provides a dedicated settings page for selecting your Ollama model:

1. Navigate to **Settings > Ollama AI Models** in WordPress admin
2. The page will automatically detect all available Ollama models on your system
3. Select your preferred model from the dropdown
4. Click **Save Settings**

**Note**: Unlike cloud providers (OpenAI, Anthropic), Ollama requires no API key since it runs locally without authentication.

**Available Features**:
- Automatic model detection from your local Ollama installation
- Model list caching (refreshed every 5 minutes)
- Manual refresh option to update the model list
- Helpful information about recommended models

### Using the Selected Model

Other plugins can use the selected Ollama model through the public API:

```php
// Check if wp-ollama-model-provider is active and has a selected model
if ( function_exists( 'wp_ollama_model_provider_get_selected_model' ) ) {
    $selected_ollama_model = wp_ollama_model_provider_get_selected_model( 'ollama' );

    if ( ! empty( $selected_ollama_model ) &&
         class_exists( 'WpOllamaModelProvider\Providers\Ollama\OllamaProvider' ) ) {
        $model = \WpOllamaModelProvider\Providers\Ollama\OllamaProvider::model( $selected_ollama_model );
        $registry = \WordPress\AiClient\AiClient::defaultRegistry();
        $registry->bindModelDependencies( $model );

        return \WordPress\AI_Client\AI_Client::prompt( $prompt )
            ->using_model( $model )
            ->generate_text();
    }
}
```

## Provider Architecture

### Ollama Provider Components

The Ollama provider consists of four main classes:

1. **OllamaProvider** (`OllamaProvider.php`)
   - Extends `AbstractApiProvider`
   - Base URL: `http://localhost:11434`
   - Authentication: None (local server)
   - Provider type: `SERVER` (not cloud-based)

2. **OllamaModelMetadataDirectory** (`OllamaModelMetadataDirectory.php`)
   - Discovers available models via `GET /api/tags`
   - Returns model metadata with text generation capability

3. **OllamaTextGenerationModel** (`OllamaTextGenerationModel.php`)
   - Extends `AbstractOpenAiCompatibleTextGenerationModel`
   - Uses `POST /v1/chat/completions` endpoint (OpenAI-compatible)
   - Formats prompts for Ollama's API format

4. **NoAuthRequestAuthentication** (`NoAuthRequestAuthentication.php`)
   - Implements `RequestAuthenticationInterface`
   - No-op authentication (returns request unchanged)

### API Endpoints

- **Model Discovery**: `GET http://localhost:11434/api/tags`
  - Returns list of available models

- **Text Generation**: `POST http://localhost:11434/v1/chat/completions`
  - OpenAI-compatible endpoint
  - Request format follows OpenAI chat completions API

### Image Generation Limitation

**Important**: The Ollama provider only supports text generation. Ollama does not support image generation.

If you need image generation, you must configure a cloud provider (OpenAI) in addition to using Ollama for text generation.

## Configuration Options

### Custom Ollama URL

If Ollama runs on a different host or port, you can configure it via a filter:

```php
add_filter( 'wp_ai_client_ollama_base_url', function() {
return 'http://192.168.1.100:11434'; // Remote Ollama server
} );
```

### Request Timeout

Ollama can be slower than cloud providers, especially with larger models. Adjust the timeout:

```php
add_filter( 'wp_ai_client_default_request_timeout', function() {
return 120; // 2 minutes for slower local models
} );
```

## Public API Functions

The plugin provides three public API functions:

### wp_ollama_model_provider_is_provider_registered( $provider_slug )

Check if a provider is registered.

```php
if ( wp_ollama_model_provider_is_provider_registered( 'ollama' ) ) {
    // Ollama provider is available
}
```

### wp_ollama_model_provider_has_settings_page()

Check if the settings page exists.

```php
if ( wp_ollama_model_provider_has_settings_page() ) {
    // Settings page is available
}
```

### wp_ollama_model_provider_get_selected_model( $provider_slug )

Get the selected model for a provider.

```php
$selected_model = wp_ollama_model_provider_get_selected_model( 'ollama' );
if ( ! empty( $selected_model ) ) {
    // Use the selected model
}
```

## Troubleshooting

### Common Issues

1. **Connection Refused**
   - **Cause**: Ollama server not running
   - **Solution**: Start Ollama: `ollama serve`

2. **Model Not Found**
   - **Cause**: Requested model not pulled
   - **Solution**: Pull model: `ollama pull llama3.2`

3. **Slow Response Times**
   - **Cause**: Large models on slower hardware
   - **Solution**: Use smaller models (e.g., `mistral:7b` instead of `llama2:70b`) or increase timeout

4. **Provider Not Available**
   - **Cause**: Plugin not activated or Ollama provider classes not loaded
   - **Solution**: Ensure wp-ollama-model-provider is activated and wp-ai-client is installed

### Debugging

Enable WordPress debug logging to see Ollama-related errors:

```php
// In wp-config.php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
```

Check logs at `wp-content/debug.log` for Ollama registration and request errors.

## Testing Ollama Integration

### Manual Testing

1. Start Ollama with a model:
   ```bash
   ollama pull llama3.2
   ollama serve
   ```

2. Test API directly:
   ```bash
   curl -X POST http://localhost:11434/v1/chat/completions \
     -H "Content-Type: application/json" \
     -d '{
       "model": "llama3.2",
       "messages": [{"role": "user", "content": "Write a short blog post about WordPress."}]
     }'
   ```

3. Test through WordPress plugin:
   - Go to Settings > Ollama AI Models
   - Select Ollama model
   - Test with another plugin that uses wp-ai-client

## Best Practices

1. **Graceful Degradation**: Always check if Ollama is available before using it
2. **User Feedback**: Provide clear error messages when Ollama is not configured properly
3. **Model Selection**: Let users choose which Ollama model to use (different models have different capabilities)
4. **Timeouts**: Set appropriate timeouts for local models (they can be slower than cloud APIs)
5. **Capability Checking**: Verify the current provider supports the required capability before attempting operations

## Security Considerations

1. **Local Only**: By default, Ollama should only be accessible from localhost
2. **No Authentication**: Ollama has no built-in authentication, so don't expose it publicly
3. **Network Configuration**: If accessing Ollama remotely, use proper network security (VPN, SSH tunnels, etc.)

## References

- Ollama Documentation: https://github.com/ollama/ollama
- WordPress AI Client: `wordpress/wp-ai-client` package documentation
