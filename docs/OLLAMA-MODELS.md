# Ollama Models Reference

Quick reference for trying different Ollama models with WordPress.

## How to Pull Models

```bash
# Pull a model
ollama pull <model-name>

# List all pulled models
ollama list

# Remove a model
ollama rm <model-name>
```

## Recommended Models for Content Generation

### General Purpose Content

**llama3.2** (Best balance)
```bash
ollama pull llama3.2
```
- **Size**: ~2GB (3B) or ~5GB (9B)
- **Speed**: Fast
- **Quality**: Excellent for blog posts
- **Best for**: General content, articles, descriptions

**llama2** (Stable, widely used)
```bash
ollama pull llama2
```
- **Size**: ~3.8GB (7B)
- **Speed**: Medium
- **Quality**: Very good
- **Best for**: General content, balanced performance

### Creative Writing

**mistral** (Creative, detailed)
```bash
ollama pull mistral
```
- **Size**: ~4.1GB (7B)
- **Speed**: Medium
- **Quality**: Excellent creativity
- **Best for**: Blog posts, stories, engaging content

**gemma2:9b** (Google's model)
```bash
ollama pull gemma2:9b
```
- **Size**: ~5.4GB (9B)
- **Speed**: Medium
- **Quality**: Very good
- **Best for**: Detailed articles, technical content

### Fast & Lightweight

**phi** (Microsoft, small but capable)
```bash
ollama pull phi
```
- **Size**: ~1.6GB (3B)
- **Speed**: Very fast
- **Quality**: Good for short content
- **Best for**: Quick posts, testing, low-resource systems

**llama3.2:1b** (Smallest)
```bash
ollama pull llama3.2:1b
```
- **Size**: ~1.3GB (1B)
- **Speed**: Extremely fast
- **Quality**: Basic but usable
- **Best for**: Testing, very fast generation

### Technical/Code Content

**codellama** (Code-focused)
```bash
ollama pull codellama
```
- **Size**: ~3.8GB (7B)
- **Speed**: Medium
- **Quality**: Excellent for technical content
- **Best for**: Tutorial posts, code examples, technical documentation

## Model Size Variants

Many models come in different sizes. Larger = better quality but slower:

```bash
# Llama 3.2 variants
ollama pull llama3.2:1b   # 1.3GB - Very fast, basic quality
ollama pull llama3.2:3b   # 2.0GB - Fast, good quality
ollama pull llama3.2      # Uses default size (usually 3B)

# Llama 2 variants
ollama pull llama2:7b     # 3.8GB - Default
ollama pull llama2:13b    # 7.4GB - Better quality, slower
ollama pull llama2:70b    # 39GB  - Best quality, very slow (needs powerful GPU)

# Mistral variants
ollama pull mistral:7b    # 4.1GB - Default
```

## Checking Available Models

### Via Command Line
```bash
# List pulled models
ollama list

# Example output:
# NAME                    ID              SIZE      MODIFIED
# llama3.2:latest         a80c4f17acd5    2.0 GB    2 days ago
# mistral:latest          61e88e884507    4.1 GB    1 week ago
# codellama:latest        8fdf8f752f6e    3.8 GB    2 weeks ago
```

### Via API
```bash
curl http://localhost:11434/api/tags
```

### In WordPress
1. Go to **Settings > Local AI Models**
2. All pulled models appear in the model dropdown
3. Select your preferred model and save

## Switching Models in WordPress

1. Pull the model you want to try (if not already pulled)
2. Go to **Settings > Local AI Models** in WordPress admin
3. Select the model from dropdown
4. Click **Save Settings**
5. Your WordPress plugins will now use the selected model

## Performance Tips

### For Development/Testing
Use smaller, faster models:
```bash
ollama pull llama3.2:1b   # Fastest
ollama pull phi           # Fast and good quality
```

### For Production/Quality Content
Use larger models:
```bash
ollama pull llama3.2:3b   # Good balance
ollama pull mistral       # Great creativity
ollama pull llama2:13b    # High quality (requires more RAM)
```

### Hardware Considerations

**4GB RAM**: Use 1B-3B models
```bash
ollama pull llama3.2:1b
ollama pull phi
```

**8GB RAM**: Use 3B-7B models
```bash
ollama pull llama3.2:3b
ollama pull llama2:7b
ollama pull mistral
```

**16GB+ RAM**: Use 7B-13B models
```bash
ollama pull llama2:13b
ollama pull codellama:13b
```

**32GB+ RAM with GPU**: Use 13B-70B models
```bash
ollama pull llama2:70b
```

## Adjusting Timeout for Larger Models

Larger models take longer to generate text. If you're experiencing timeouts, add this filter to your theme or plugin:

```php
add_filter( 'wp_ai_client_default_request_timeout', function() {
    // 60 seconds - Good for 3B-7B models
    return 60;

    // 120 seconds - Better for 7B-13B models
    // return 120;

    // 300 seconds - For 13B-70B models
    // return 300;
} );
```

## Troubleshooting

### Model Not Appearing in Dropdown

1. Verify model is pulled:
   ```bash
   ollama list
   ```

2. Restart Ollama:
   ```bash
   # Stop Ollama
   pkill ollama

   # Start again
   ollama serve
   ```

3. Use the Refresh Model List button on **Settings > Local AI Models**

### Model Running Slow

- Use a smaller variant (e.g., `llama3.2:1b` instead of `llama2:13b`)
- Increase timeout using the filter above
- Close other applications using RAM/GPU
- Consider using GPU acceleration (if available)

### Out of Memory Errors

- Pull a smaller model
- Close other applications
- Check available RAM: `free -h` (Linux) or Activity Monitor (Mac)

## Testing Different Models

Quick test workflow to compare models:

```bash
# Pull test models
ollama pull llama3.2:1b   # Fast
ollama pull llama3.2:3b   # Balanced
ollama pull mistral       # Creative

# Test each one:
# 1. Go to Settings > Local AI Models
# 2. Select model and save
# 3. Test content generation with your WordPress plugin
# 4. Compare quality vs speed
```

## Current Model Information

To see which model is currently active:

1. Go to **Settings > Local AI Models**
2. Check the selected model in the dropdown
3. This model will be used automatically by all WordPress plugins that use wp-local-model-provider
