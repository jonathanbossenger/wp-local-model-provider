# WP Ollama Model Provider

[![Latest Stable Version](https://poser.pugx.org/jonathanbossenger/wp-ollama-model-provider/v/stable)](https://packagist.org/packages/jonathanbossenger/wp-ollama-model-provider)
[![Total Downloads](https://poser.pugx.org/jonathanbossenger/wp-ollama-model-provider/downloads)](https://packagist.org/packages/jonathanbossenger/wp-ollama-model-provider)
[![License](https://poser.pugx.org/jonathanbossenger/wp-ollama-model-provider/license)](https://packagist.org/packages/jonathanbossenger/wp-ollama-model-provider)

A WordPress plugin that provides local and cloud AI model support (Ollama) for the WordPress AI Client.

## Description

WP Ollama Model Provider enables WordPress to use AI models through Ollama, supporting both local installations and Ollama Cloud. This plugin acts as a provider for the [WordPress AI Client](https://github.com/WordPress/wordpress-ai-client), making Ollama models accessible to any WordPress plugin that uses the AI Client.

### Features

- **Flexible Deployment**: Support for both local Ollama installations and Ollama Cloud
- **Local AI Models**: Run AI models locally with Ollama - no cloud API keys required
- **Cloud Integration**: Use Ollama Cloud for easy access without local installation
- **Automatic Model Detection**: Discovers all available Ollama models on your system or cloud account
- **Simple Configuration**: Easy settings page at Settings > Ollama AI Models
- **Model Selection**: Choose which Ollama model to use from a dropdown
- **Model Caching**: Efficient 5-minute cache for model discovery
- **Public API**: Other plugins can easily check for and use your selected model
- **Privacy-First**: Local mode ensures text generation happens entirely on your machine

### Supported Providers

- **Ollama** (current)
  - Local installation
  - Ollama Cloud
- Future: LocalAI, LM Studio, and other local providers

## Requirements

- **PHP**: 8.0 or higher
- **WordPress**: 6.0 or higher
- **Dependencies**:
  - `wordpress/wp-ai-client` ^0.2.1
  - For local mode: [Ollama](https://ollama.com) installed and running locally
  - For cloud mode: Ollama Cloud API key

## Installation

### Via Composer (Recommended)

```bash
# Install in your WordPress project
composer require jonathanbossenger/wp-ollama-model-provider
```

The plugin will be installed to `wp-content/plugins/wp-ollama-model-provider/` (or `web/app/plugins/` for Bedrock).
Activate it through the WordPress admin.

**For Bedrock/Roots.io users:**
The package will automatically install to the correct plugins directory.

**For traditional WordPress installations:**
Run composer from your WordPress root with proper installer-paths configured, or run it directly in `wp-content/plugins/`.

### Manual Installation

1. Download the latest release from [GitHub Releases](https://github.com/jonathanbossenger/wp-ollama-model-provider/releases)
2. Upload to `/wp-content/plugins/wp-ollama-model-provider/`
3. Run `composer install --no-dev` in the plugin directory
4. Activate the plugin through the WordPress admin

### Setup Ollama

#### Local Setup

1. Install Ollama from https://ollama.com
2. Pull at least one model:
   ```bash
   ollama pull llama3.2
   ```
3. Verify Ollama is running:
   ```bash
   curl http://localhost:11434/api/tags
   ```

#### Ollama Cloud Setup

1. Sign up for Ollama Cloud at https://ollama.com/cloud
2. Get your API key from the Ollama Cloud dashboard

## Configuration

### For Local Ollama:

1. Navigate to **Settings > Ollama AI Models** in WordPress admin
2. Select **Local** as the deployment mode
3. Select your preferred Ollama model from the dropdown
4. Click **Save Settings**

### For Ollama Cloud:

1. Navigate to **Settings > Ollama AI Models** in WordPress admin
2. Select **Ollama Cloud** as the deployment mode
3. Enter your Ollama Cloud API key
4. Select your preferred model from the dropdown
5. Click **Save Settings**

Your selected model is now available to all WordPress plugins that use the AI Client.

## Usage

### For End Users

Once configured, the plugin runs automatically in the background. Any WordPress plugin that uses the WordPress AI Client can detect and use your selected Ollama model.

### For Plugin Developers

#### Check for Selected Model

```php
if ( function_exists( 'wp_ollama_model_provider_get_selected_model' ) ) {
    $selected_model = wp_ollama_model_provider_get_selected_model( 'ollama' );

    if ( ! empty( $selected_model ) ) {
        // Use the selected Ollama model
    }
}
```

#### Use the Selected Model

```php
// Check for selected Ollama model from wp-ollama-model-provider
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

// Fall back to automatic provider/model selection
return \WordPress\AI_Client\AI_Client::prompt( $prompt )->generate_text();
```

#### Public API Functions

**wp_ollama_model_provider_is_provider_registered( $provider_slug )**
- Check if a provider (e.g., 'ollama') is registered
- Returns: `bool`

**wp_ollama_model_provider_has_settings_page()**
- Check if the settings page is available
- Returns: `bool`

**wp_ollama_model_provider_get_selected_model( $provider_slug )**
- Get the selected model for a provider (e.g., 'ollama')
- Returns: `string` (model ID) or empty string if none selected

## Filters

### wp_ai_client_ollama_base_url

Change the Ollama base URL (default: `http://localhost:11434`).

```php
add_filter( 'wp_ai_client_ollama_base_url', function() {
    return 'http://192.168.1.100:11434'; // Remote Ollama server
} );
```

### wp_ai_client_default_request_timeout

Adjust timeout for slower models (default: varies by plugin).

```php
add_filter( 'wp_ai_client_default_request_timeout', function() {
    return 120; // 2 minutes
} );
```

## Documentation

- **[Quick Start Guide](docs/QUICK-START.md)** - Get up and running in 5 minutes
- **[Ollama Models Reference](docs/OLLAMA-MODELS.md)** - Model recommendations and comparisons
- **[Integration Guide](docs/OLLAMA-INTEGRATION.md)** - Technical integration details

## Troubleshooting

### No models appearing in dropdown

1. Verify Ollama is running:
   ```bash
   ollama list
   ```

2. Pull a model if needed:
   ```bash
   ollama pull llama3.2
   ```

3. Use the "Refresh Model List" button on the settings page

### Cannot connect to Ollama

1. Check if Ollama is running:
   ```bash
   curl http://localhost:11434/api/tags
   ```

2. Start Ollama if needed:
   ```bash
   ollama serve
   ```

### Slow generation times

- Use a smaller model (e.g., `llama3.2:1b` instead of `llama2:13b`)
- Increase the timeout using the `wp_ai_client_default_request_timeout` filter
- See [OLLAMA-MODELS.md](docs/OLLAMA-MODELS.md) for model recommendations

### Enable debug logging

```php
// In wp-config.php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
```

Check `wp-content/debug.log` for error messages.

## Limitations

- **Text Generation Only**: Ollama only supports text generation, not image generation
- **Local Models**: Requires Ollama to be installed and running locally (or on your network)
- **Resource Intensive**: Larger models require significant RAM and CPU/GPU resources

## Contributing

Contributions are welcome! Please feel free to submit issues or pull requests.

### Development Setup

1. Clone the repository
2. Run `composer install`
3. Ensure Ollama is installed and running
4. Activate the plugin in a WordPress development environment

### Coding Standards

This plugin follows WordPress Coding Standards. Check your code:

```bash
vendor/bin/phpcs --standard=WordPress wp-ollama-model-provider.php includes/
```

## License

GPL-2.0-or-later

## Support

- **Issues**: [GitHub Issues](https://github.com/jonathanbossenger/wp-ollama-model-provider/issues)
- **Documentation**: See `docs/` directory
- **Ollama**: https://ollama.com

## Credits

Created by Jonathan Bossenger

Built on top of:
- [WordPress AI Client](https://github.com/WordPress/wordpress-ai-client)
- [Ollama](https://ollama.com)
