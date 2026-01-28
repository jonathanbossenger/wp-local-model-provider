# WP Local Model Provider

A WordPress plugin that provides local AI model support (Ollama) for the WordPress AI Client.

## Description

WP Local Model Provider enables WordPress to use local AI models through Ollama, allowing you to run AI-powered features without cloud API keys. This plugin acts as a provider for the [WordPress AI Client](https://github.com/WordPress/wordpress-ai-client), making local models accessible to any WordPress plugin that uses the AI Client.

### Features

- **Local AI Models**: Run AI models locally with Ollama - no cloud API keys required
- **Automatic Model Detection**: Discovers all available Ollama models on your system
- **Simple Configuration**: Easy settings page at Settings > Local AI Models
- **Model Selection**: Choose which Ollama model to use from a dropdown
- **Model Caching**: Efficient 5-minute cache for model discovery
- **Public API**: Other plugins can easily check for and use your selected model
- **Privacy-First**: Text generation happens entirely on your local machine

### Supported Providers

- **Ollama** (current)
- Future: LocalAI, LM Studio, and other local providers

## Requirements

- **PHP**: 8.0 or higher
- **WordPress**: 6.0 or higher
- **Dependencies**:
  - `wordpress/wp-ai-client` ^0.2.1
  - [Ollama](https://ollama.com) installed and running locally

## Installation

### Via Composer

```bash
composer require jonathanbossenger/wp-local-model-provider
```

### Manual Installation

1. Download the plugin files
2. Upload to `/wp-content/plugins/wp-local-model-provider/`
3. Run `composer install` in the plugin directory
4. Activate the plugin through the WordPress admin

### Setup Ollama

1. Install Ollama from https://ollama.com
2. Pull at least one model:
   ```bash
   ollama pull llama3.2
   ```
3. Verify Ollama is running:
   ```bash
   curl http://localhost:11434/api/tags
   ```

## Configuration

1. Navigate to **Settings > Local AI Models** in WordPress admin
2. Select your preferred Ollama model from the dropdown
3. Click **Save Settings**

Your selected model is now available to all WordPress plugins that use the AI Client.

## Usage

### For End Users

Once configured, the plugin runs automatically in the background. Any WordPress plugin that uses the WordPress AI Client can detect and use your selected Ollama model.

### For Plugin Developers

#### Check for Selected Model

```php
if ( function_exists( 'wp_local_model_provider_get_selected_model' ) ) {
    $selected_model = wp_local_model_provider_get_selected_model( 'ollama' );

    if ( ! empty( $selected_model ) ) {
        // Use the selected Ollama model
    }
}
```

#### Use the Selected Model

```php
// Check for selected Ollama model from wp-local-model-provider
if ( function_exists( 'wp_local_model_provider_get_selected_model' ) ) {
    $selected_ollama_model = wp_local_model_provider_get_selected_model( 'ollama' );

    if ( ! empty( $selected_ollama_model ) &&
         class_exists( 'WpLocalModelProvider\Providers\Ollama\OllamaProvider' ) ) {
        $model = \WpLocalModelProvider\Providers\Ollama\OllamaProvider::model( $selected_ollama_model );
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

**wp_local_model_provider_is_provider_registered( $provider_slug )**
- Check if a provider (e.g., 'ollama') is registered
- Returns: `bool`

**wp_local_model_provider_has_settings_page()**
- Check if the settings page is available
- Returns: `bool`

**wp_local_model_provider_get_selected_model( $provider_slug )**
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
vendor/bin/phpcs --standard=WordPress wp-local-model-provider.php includes/
```

## License

GPL-2.0-or-later

## Support

- **Issues**: [GitHub Issues](https://github.com/jonathanbossenger/wp-local-model-provider/issues)
- **Documentation**: See `docs/` directory
- **Ollama**: https://ollama.com

## Credits

Created by Jonathan Bossenger

Built on top of:
- [WordPress AI Client](https://github.com/WordPress/wordpress-ai-client)
- [Ollama](https://ollama.com)
