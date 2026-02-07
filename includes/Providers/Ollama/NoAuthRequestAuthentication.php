<?php
/**
 * No-Authentication Request Authentication implementation for Ollama.
 *
 * @package wp-ollama-model-provider
 */

declare(strict_types=1);

namespace WpOllamaModelProvider\Providers\Ollama;

use WordPress\AiClient\Providers\Http\Contracts\RequestAuthenticationInterface;
use WordPress\AiClient\Providers\Http\DTO\Request;

/**
 * Class for HTTP requests that don't require authentication.
 *
 * This is used for local providers like Ollama that don't require API keys
 * or other authentication mechanisms.
 *
 * @since 1.0.0
 */
class NoAuthRequestAuthentication implements RequestAuthenticationInterface {

	/**
	 * Authenticates an HTTP request by doing nothing.
	 *
	 * Since Ollama doesn't require authentication, this method simply
	 * returns the request unchanged.
	 *
	 * @since 1.0.0
	 *
	 * @param Request $request The request to authenticate.
	 * @return Request The unchanged request.
	 */
	public function authenticateRequest( Request $request ): Request {
		// No authentication needed for Ollama - return request unchanged.
		return $request;
	}

	/**
	 * Get the JSON schema for this authentication method.
	 *
	 * Since Ollama doesn't require authentication, this returns an empty schema.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, mixed> Empty JSON schema.
	 */
	public static function getJsonSchema(): array {
		return array(
			'type'       => 'object',
			'properties' => array(),
		);
	}
}
