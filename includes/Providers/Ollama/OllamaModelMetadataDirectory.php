<?php
/**
 * Ollama Model Metadata Directory implementation.
 *
 * @package wp-ollama-model-provider
 */

declare(strict_types=1);

namespace WpOllamaModelProvider\Providers\Ollama;

use WordPress\AiClient\Messages\Enums\ModalityEnum;
use WordPress\AiClient\Providers\ApiBasedImplementation\AbstractApiBasedModelMetadataDirectory;
use WordPress\AiClient\Providers\Http\DTO\Request;
use WordPress\AiClient\Providers\Http\DTO\Response;
use WordPress\AiClient\Providers\Http\Enums\HttpMethodEnum;
use WordPress\AiClient\Providers\Http\Exception\ResponseException;
use WordPress\AiClient\Providers\Models\DTO\ModelMetadata;
use WordPress\AiClient\Providers\Models\DTO\SupportedOption;
use WordPress\AiClient\Providers\Models\Enums\CapabilityEnum;
use WordPress\AiClient\Providers\Models\Enums\OptionEnum;

/**
 * Class for the Ollama model metadata directory.
 *
 * Discovers models available on the local Ollama server via the /api/tags endpoint.
 *
 * @since 1.0.0
 *
 * @phpstan-type OllamaModelsResponseData array{
 *     models: list<array{
 *         model: string,
 *         name: string,
 *         size?: int,
 *         digest?: string,
 *         modified_at?: string
 *     }>
 * }
 */
class OllamaModelMetadataDirectory extends AbstractApiBasedModelMetadataDirectory {

	/**
	 * Sends the API request to list models from Ollama.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, ModelMetadata> Map of model ID to model metadata.
	 */
	protected function sendListModelsRequest(): array {
		$http_transporter = $this->getHttpTransporter();

		// Create request to Ollama's /api/tags endpoint.
		$request = new Request(
			HttpMethodEnum::GET(),
			OllamaProvider::url( $this->getModelsApiPath() ),
			array(),
			null
		);

		// Add authentication (none for Ollama, but required by the interface).
		$request = $this->getRequestAuthentication()->authenticateRequest( $request );

		// Send the request.
		$response = $http_transporter->send( $request );

		// Parse the response.
		$models_metadata = $this->parseResponseToModelMetadataList( $response );

		// Convert to map.
		$model_metadata_map = array();
		foreach ( $models_metadata as $model_metadata ) {
			$model_metadata_map[ $model_metadata->getId() ] = $model_metadata;
		}

		return $model_metadata_map;
	}

	/**
	 * Parse the Ollama API response to a list of model metadata.
	 *
	 * @since 1.0.0
	 *
	 * @param Response $response HTTP response from Ollama.
	 * @return ModelMetadata[] List of model metadata.
	 * @throws ResponseException If response is invalid.
	 */
	protected function parseResponseToModelMetadataList( Response $response ): array {
		/** @var OllamaModelsResponseData $response_data */
		$response_data = $response->getData();

		if ( ! isset( $response_data['models'] ) || ! $response_data['models'] ) {
			throw ResponseException::fromMissingData( 'Ollama', 'models' );
		}

		// Ollama supports text generation for all models.
		$ollama_capabilities = array(
			CapabilityEnum::textGeneration(),
			CapabilityEnum::chatHistory(),
		);

		// Define supported options for Ollama models.
		$ollama_options = array(
			new SupportedOption( OptionEnum::systemInstruction() ),
			new SupportedOption( OptionEnum::maxTokens() ),
			new SupportedOption( OptionEnum::temperature() ),
			new SupportedOption( OptionEnum::topP() ),
			new SupportedOption( OptionEnum::stopSequences() ),
			new SupportedOption( OptionEnum::customOptions() ),
			new SupportedOption( OptionEnum::inputModalities(), array( array( ModalityEnum::text() ) ) ),
			new SupportedOption( OptionEnum::outputModalities(), array( array( ModalityEnum::text() ) ) ),
		);

		$models_metadata = array();
		foreach ( $response_data['models'] as $model ) {
			$model_id   = $model['name'];
			$model_name = $model['name'];

			$models_metadata[] = new ModelMetadata(
				$model_id,
				$model_name,
				$ollama_capabilities,
				$ollama_options
			);
		}

		return $models_metadata;
	}

	/**
	 * Get the API path for listing models.
	 *
	 * @since 1.0.0
	 *
	 * @return string API path.
	 */
	protected function getModelsApiPath(): string {
		return '/api/tags';
	}
}
