# Settings Page Layout

This document shows the layout of the WordPress admin settings page for WP Local Model Provider with cloud support.

## Settings Page Location
**WordPress Admin → Settings → Local AI Models**

## Page Layout

```
┌─────────────────────────────────────────────────────────────────┐
│ Local AI Models                                                  │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│ Ollama Model Selection                                          │
│ Configure your Ollama deployment mode and select which model    │
│ to use for AI-powered content generation.                       │
│                                                                  │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│ Deployment Mode                                                  │
│ ┌──────────────────────────────────────┐                        │
│ │ Local (http://localhost:11434)    ▼ │  ◄─ Dropdown selector   │
│ └──────────────────────────────────────┘                        │
│ Select whether to use a local Ollama server or Ollama Cloud.    │
│                                                                  │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│ Ollama Cloud API Key                                            │
│ ┌──────────────────────────────────────┐                        │
│ │ ••••••••••••••••••••••••••••••••     │  ◄─ Password field     │
│ └──────────────────────────────────────┘     (hidden if Local)  │
│ Enter your Ollama Cloud API key. Only required when using       │
│ Ollama Cloud.                                                    │
│                                                                  │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│ Select Ollama Model                                             │
│ ┌──────────────────────────────────────┐                        │
│ │ -- Select a model --              ▼ │  ◄─ Dropdown (dynamic)  │
│ └──────────────────────────────────────┘                        │
│ Select which local Ollama model to use for text generation.     │
│ (Text changes to "cloud model" when cloud mode selected)        │
│                                                                  │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│ ┌─────────────────┐                                             │
│ │ Save Settings   │  ◄─ Save button                             │
│ └─────────────────┘                                             │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│ About Ollama Models                                             │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│ Ollama allows you to run large language models either locally   │
│ on your computer or through Ollama Cloud.                       │
│                                                                  │
│ Local Setup:                                                    │
│ 1. Install Ollama from https://ollama.com                       │
│ 2. Pull a model: ollama pull llama3.2                          │
│ 3. Ensure Ollama is running (it starts automatically on most   │
│    systems)                                                      │
│ 4. Select "Local" as deployment mode and choose your model      │
│                                                                  │
│ Ollama Cloud Setup:                                             │
│ 1. Sign up for Ollama Cloud at https://ollama.com/cloud        │
│ 2. Get your API key from the Ollama Cloud dashboard            │
│ 3. Select "Ollama Cloud" as deployment mode and enter your     │
│    API key                                                       │
│ 4. Choose your preferred model from the available cloud models  │
│                                                                  │
│ Recommended Models:                                             │
│ • llama3.2 - Best balance of speed and quality                 │
│ • mistral - Great for creative content                         │
│ • codellama - Optimized for technical content                  │
│ • phi - Fastest, good for testing                              │
│                                                                  │
│ ┌─────────────────────┐                                         │
│ │ Refresh Model List  │  ◄─ Button to refresh model list       │
│ └─────────────────────┘                                         │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

## Interactive Behavior

### When "Local" is selected:
- **Deployment Mode**: Shows "Local (http://localhost:11434)"
- **API Key Field**: Hidden (via JavaScript)
- **Model Dropdown**: Fetches from local Ollama server
- **Help Text**: Mentions "local Ollama model"

### When "Ollama Cloud" is selected:
- **Deployment Mode**: Shows "Ollama Cloud"
- **API Key Field**: Visible (via JavaScript)
- **Model Dropdown**: Fetches from Ollama Cloud API
- **Help Text**: Mentions "Ollama Cloud model"

### When switching modes:
- **Loading State**: Shows "Loading models..." message
- **AJAX Request**: Fetches models from appropriate source
- **No Page Reload**: Everything happens instantly via JavaScript
- **Model List Updates**: Dropdown repopulates with new models

### Error States

#### Local Mode - Ollama Not Running
```
┌─────────────────────────────────────────────────────────────────┐
│ Select Ollama Model                                             │
│                                                                  │
│ ⚠ Error: Cannot connect to Ollama. Please ensure it is running.│
│ Please ensure Ollama is running on http://localhost:11434       │
└─────────────────────────────────────────────────────────────────┘
```

#### Cloud Mode - Invalid API Key
```
┌─────────────────────────────────────────────────────────────────┐
│ Select Ollama Model                                             │
│                                                                  │
│ ⚠ Error: Ollama API returned error code: 401                   │
│ Please ensure you have entered a valid Ollama Cloud API key.    │
└─────────────────────────────────────────────────────────────────┘
```

#### Loading State
```
┌─────────────────────────────────────────────────────────────────┐
│ Select Ollama Model                                             │
│                                                                  │
│ Loading models...                                               │
└─────────────────────────────────────────────────────────────────┘
```

## JavaScript Behavior

When the deployment mode dropdown changes:
1. Check selected value
2. If "cloud": Show API key field
3. If "local": Hide API key field
4. **Make AJAX request to fetch models for selected mode** ⭐
5. **Show loading state while fetching** ⭐
6. **Update model dropdown with new models** ⭐
7. **Display appropriate help text** ⭐
8. Field changes happen instantly without page reload

## Database Storage

Settings are stored as WordPress options:
- `wp_local_model_provider_ollama_deployment_mode`: "local" or "cloud"
- `wp_local_model_provider_ollama_api_key`: API key string (empty for local)
- `wp_local_model_provider_ollama_model`: Selected model ID

## Model List Caching

Both local and cloud model lists are cached in the same transient:
- Transient key: `wp_local_model_provider_ollama_models`
- TTL: 5 minutes
- Cache is invalidated when "Refresh Model List" is clicked
