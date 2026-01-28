<?php
/**
 * Ollama Text Generation Model implementation.
 *
 * @package wp-local-model-provider
 */

declare(strict_types=1);

namespace WpLocalModelProvider\Providers\Ollama;

use WordPress\AiClient\Providers\Http\DTO\Request;
use WordPress\AiClient\Providers\Http\Enums\HttpMethodEnum;
use WordPress\AiClient\Providers\OpenAiCompatibleImplementation\AbstractOpenAiCompatibleTextGenerationModel;

/**
 * Class for an Ollama text generation model.
 *
 * Ollama supports OpenAI-compatible endpoints at /v1/chat/completions,
 * allowing us to use the AbstractOpenAiCompatibleTextGenerationModel base class.
 *
 * @since 1.0.0
 */
class OllamaTextGenerationModel extends AbstractOpenAiCompatibleTextGenerationModel {

	/**
	 * Create an HTTP request for the Ollama API.
	 *
	 * @since 1.0.0
	 *
	 * @param HttpMethodEnum $method HTTP method.
	 * @param string         $path API path.
	 * @param array          $headers Request headers.
	 * @param mixed          $data Request data.
	 * @return Request HTTP request.
	 */
	protected function createRequest( HttpMethodEnum $method, string $path, array $headers = array(), $data = null ): Request {
		// Ollama supports OpenAI-compatible endpoints at /v1/
		// Prepend /v1/ to the path if not already present.
		if ( ! str_starts_with( $path, '/v1/' ) && ! str_starts_with( $path, 'v1/' ) ) {
			$path = '/v1/' . ltrim( $path, '/' );
		}

		return new Request(
			$method,
			OllamaProvider::url( $path ),
			$headers,
			$data,
			$this->getRequestOptions()
		);
	}
}
