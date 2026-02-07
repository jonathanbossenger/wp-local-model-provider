<?php
/**
 * API Key Request Authentication implementation for Ollama Cloud.
 *
 * @package wp-local-model-provider
 */

declare(strict_types=1);

namespace WpLocalModelProvider\Providers\Ollama;

use WordPress\AiClient\Providers\Http\Contracts\RequestAuthenticationInterface;
use WordPress\AiClient\Providers\Http\DTO\Request;

/**
 * Class for HTTP requests that require API key authentication.
 *
 * This is used for Ollama Cloud that requires API key authentication.
 *
 * @since 1.1.0
 */
class ApiKeyRequestAuthentication implements RequestAuthenticationInterface {

	/**
	 * The API key for authentication.
	 *
	 * @var string
	 */
	private string $api_key;

	/**
	 * Constructor.
	 *
	 * @since 1.1.0
	 *
	 * @param string $api_key The API key for authentication.
	 */
	public function __construct( string $api_key ) {
		$this->api_key = $api_key;
	}

	/**
	 * Authenticates an HTTP request by adding the API key header.
	 *
	 * @since 1.1.0
	 *
	 * @param Request $request The request to authenticate.
	 * @return Request The authenticated request.
	 */
	public function authenticateRequest( Request $request ): Request {
		// Add Authorization header with API key.
		$headers                  = $request->getHeaders();
		$headers['Authorization'] = 'Bearer ' . $this->api_key;

		return new Request(
			$request->getUrl(),
			$request->getMethod(),
			$headers,
			$request->getBody()
		);
	}

	/**
	 * Get the JSON schema for this authentication method.
	 *
	 * @since 1.1.0
	 *
	 * @return array<string, mixed> JSON schema for API key authentication.
	 */
	public static function getJsonSchema(): array {
		return array(
			'type'       => 'object',
			'properties' => array(
				'api_key' => array(
					'type'        => 'string',
					'description' => 'API key for Ollama Cloud authentication',
				),
			),
			'required'   => array( 'api_key' ),
		);
	}
}
