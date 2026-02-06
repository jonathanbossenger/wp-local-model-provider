<?php
/**
 * Ollama Provider implementation.
 *
 * @package wp-local-model-provider
 */

declare(strict_types=1);

namespace WpLocalModelProvider\Providers\Ollama;

use WordPress\AiClient\Common\Exception\RuntimeException;
use WordPress\AiClient\Providers\ApiBasedImplementation\AbstractApiProvider;
use WordPress\AiClient\Providers\ApiBasedImplementation\ListModelsApiBasedProviderAvailability;
use WordPress\AiClient\Providers\Contracts\ModelMetadataDirectoryInterface;
use WordPress\AiClient\Providers\Contracts\ProviderAvailabilityInterface;
use WordPress\AiClient\Providers\DTO\ProviderMetadata;
use WordPress\AiClient\Providers\Enums\ProviderTypeEnum;
use WordPress\AiClient\Providers\Models\Contracts\ModelInterface;
use WordPress\AiClient\Providers\Models\DTO\ModelMetadata;

/**
 * Class for the Ollama provider.
 *
 * Ollama is a local AI model server that runs models like Llama 2, Mistral, and others
 * without requiring cloud API keys.
 *
 * @since 1.0.0
 */
class OllamaProvider extends AbstractApiProvider {

	/**
	 * Get the base URL for the Ollama API.
	 *
	 * @since 1.0.0
	 *
	 * @return string Base URL.
	 */
	protected static function baseUrl(): string {
		// Check deployment mode.
		$deployment_mode = get_option( 'wp_local_model_provider_ollama_deployment_mode', 'local' );

		if ( 'cloud' === $deployment_mode ) {
			/**
			 * Filter the Ollama Cloud base URL.
			 *
			 * @since 1.1.0
			 *
			 * @param string $base_url Default Ollama Cloud base URL.
			 */
			return apply_filters( 'wp_ai_client_ollama_cloud_base_url', 'https://api.ollama.ai' );
		}

		/**
		 * Filter the Ollama base URL.
		 *
		 * @since 1.0.0
		 *
		 * @param string $base_url Default Ollama base URL.
		 */
		return apply_filters( 'wp_ai_client_ollama_base_url', 'http://localhost:11434' );
	}

	/**
	 * Create a model instance based on its metadata.
	 *
	 * @since 1.0.0
	 *
	 * @param ModelMetadata    $modelMetadata    Model metadata.
	 * @param ProviderMetadata $providerMetadata Provider metadata.
	 * @return ModelInterface Model instance.
	 * @throws RuntimeException If model capabilities are not supported.
	 */
	protected static function createModel(
		ModelMetadata $modelMetadata,
		ProviderMetadata $providerMetadata
	): ModelInterface {
		$capabilities = $modelMetadata->getSupportedCapabilities();
		foreach ( $capabilities as $capability ) {
			if ( $capability->isTextGeneration() ) {
				return new OllamaTextGenerationModel( $modelMetadata, $providerMetadata );
			}
		}

		throw new RuntimeException(
			'Unsupported model capabilities: ' . implode( ', ', $capabilities )
		);
	}

	/**
	 * Create provider metadata.
	 *
	 * @since 1.0.0
	 *
	 * @return ProviderMetadata Provider metadata.
	 */
	protected static function createProviderMetadata(): ProviderMetadata {
		return new ProviderMetadata(
			'ollama',
			'Ollama',
			ProviderTypeEnum::server(),
			null // No credentials URL needed for local server.
		);
	}

	/**
	 * Create provider availability checker.
	 *
	 * @since 1.0.0
	 *
	 * @return ProviderAvailabilityInterface Provider availability checker.
	 */
	protected static function createProviderAvailability(): ProviderAvailabilityInterface {
		return new ListModelsApiBasedProviderAvailability(
			static::modelMetadataDirectory()
		);
	}

	/**
	 * Create model metadata directory.
	 *
	 * @since 1.0.0
	 *
	 * @return ModelMetadataDirectoryInterface Model metadata directory.
	 */
	protected static function createModelMetadataDirectory(): ModelMetadataDirectoryInterface {
		return new OllamaModelMetadataDirectory();
	}
}
