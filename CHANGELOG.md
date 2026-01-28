# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-01-28

### Added
- Initial release
- Ollama provider integration with WordPress AI Client
- Admin settings page for model selection
- Automatic model detection from local Ollama instance
- Public API functions for theme/plugin developers
- Caching for model discovery (5-minute cache)
- Filter hooks for customization
- Support for local AI model execution without API keys

### Documentation
- Quick start guide
- Ollama models reference
- Integration guide for developers
- Comprehensive README with installation and usage instructions

### Features
- Automatic Ollama model discovery
- Model selection dropdown in WordPress admin
- Settings page at Settings > Local AI Models
- Public API: `wp_local_model_provider_get_selected_model()`
- Public API: `wp_local_model_provider_is_provider_registered()`
- Public API: `wp_local_model_provider_has_settings_page()`
- Filter: `wp_ai_client_ollama_base_url` for custom Ollama URLs
- Filter: `wp_ai_client_default_request_timeout` for timeout adjustments

[1.0.0]: https://github.com/jonathanbossenger/wp-local-model-provider/releases/tag/v1.0.0
