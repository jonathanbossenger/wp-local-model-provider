# Cloud Support Implementation

This document details the implementation of Ollama Cloud support in the WP Ollama Model Provider plugin.

## Overview

The plugin now supports two deployment modes:
1. **Local**: Uses a local Ollama installation (default, unchanged behavior)
2. **Cloud**: Uses Ollama Cloud with API key authentication

## Changes Made

### 1. New Files

#### `includes/Providers/Ollama/ApiKeyRequestAuthentication.php`
- New authentication class for cloud mode
- Implements `RequestAuthenticationInterface`
- Adds `Authorization: Bearer {api_key}` header to requests

### 2. Modified Files

#### `wp-ollama-model-provider.php`
- Added deployment mode setting (`wp_ollama_model_provider_ollama_deployment_mode`)
- Added API key setting (`wp_ollama_model_provider_ollama_api_key`)
- Updated `wp_ollama_model_provider_register_ollama()` to use appropriate authentication based on mode
- Added new settings fields for deployment mode and API key
- Added JavaScript for conditional field display and dynamic model list refresh
- Added AJAX endpoint `wp_ollama_model_provider_ajax_get_models()` for fetching models
- Updated model fetching to support cloud authentication
- Updated settings page info card with cloud instructions
- Model list now refreshes automatically when deployment mode changes

#### `includes/Providers/Ollama/OllamaProvider.php`
- Updated `baseUrl()` method to return different URL based on deployment mode
- Local: `http://localhost:11434` (default)
- Cloud: `https://ollama.com` (default, filterable)

#### `README.md`
- Updated description to mention cloud support
- Added cloud setup instructions
- Separated configuration steps for local vs cloud

## Database Options

The plugin stores three settings in WordPress options:

1. `wp_ollama_model_provider_ollama_deployment_mode` (string, default: 'local')
   - Values: 'local' or 'cloud'

2. `wp_ollama_model_provider_ollama_api_key` (string, default: '')
   - Only used when deployment mode is 'cloud'

3. `wp_ollama_model_provider_ollama_model` (string, default: '')
   - Selected model ID (unchanged)

## Filters

### New Filter
- `wp_ai_client_ollama_cloud_base_url` - Customize Ollama Cloud base URL
  - Default: `https://ollama.com`

### Existing Filter
- `wp_ai_client_ollama_base_url` - Customize local Ollama base URL
  - Default: `http://localhost:11434`

## User Interface

### Settings Page (Settings > Ollama AI Models)

The settings page now includes:

1. **Deployment Mode** dropdown
   - Local (http://localhost:11434)
   - Ollama Cloud

2. **Ollama Cloud API Key** field (conditional)
   - Only shown when Cloud mode is selected
   - Password input type for security
   - Includes help text

3. **Select Ollama Model** dropdown
   - Dynamically populated based on selected mode
   - **Automatically refreshes when deployment mode changes** (AJAX-powered)
   - Shows loading state during refresh
   - Error messages adapt to deployment mode

4. **Info Card**
   - Separate setup instructions for Local and Cloud
   - Links to Ollama Cloud signup

## Backward Compatibility

- Default mode is 'local' - existing installations continue to work unchanged
- No database migrations required
- Existing model selections are preserved
- Cloud mode is opt-in

## Security Considerations

- API key stored in WordPress options (same as other API keys in WordPress)
- API key field uses password input type
- Authentication only applied to cloud mode
- No changes to local mode security model (no authentication)
- AJAX endpoint secured with nonce verification
- AJAX endpoint restricted to users with `manage_options` capability

## Testing Recommendations

### Local Mode Testing
1. Set deployment mode to 'Local'
2. Verify model list loads from local Ollama
3. **Switch to Cloud mode and back to Local - verify model list refreshes** ⭐
4. Select a model and save
5. Verify model selection persists
6. Confirm no API key is required or used

### Cloud Mode Testing
1. Set deployment mode to 'Ollama Cloud'
2. **Verify model list refreshes automatically** ⭐
3. Enter API key
4. **Verify model list refreshes again with cloud models** ⭐
5. Select a cloud model and save
6. Verify model selection persists
7. Test with invalid API key (should show error)
8. Test without API key (should show error)

### Switching Modes
1. Start in local mode with model selected
2. Switch to cloud mode
3. **Model list should refresh automatically showing cloud models** ⭐
4. Switch back to local
5. **Model list should refresh automatically showing local models** ⭐
6. Verify no page reload is required for any mode switch

## Known Limitations

1. Cloud URL endpoint assumption: The implementation assumes Ollama Cloud uses `/api/tags` endpoint like local Ollama. This should be verified against actual Ollama Cloud API documentation.

2. Authentication format assumption: The implementation uses `Authorization: Bearer {api_key}` format. This should be verified against Ollama Cloud API documentation.

3. No validation of API key format: The plugin accepts any string as an API key and only validates by attempting to fetch models.

## Future Enhancements

1. Add API key validation before saving
2. Add connection test button
3. Cache model lists separately for local and cloud
4. Add clear/remove API key button
5. Support for custom cloud endpoints
6. Better error messages with troubleshooting steps
