<?php

namespace TotalPoll\Modules\Extensions\Troubleshooter\Checks;

use TotalPollVendors\TotalCore\Contracts\Helpers\Arrayable;

/**
 * Class Checkup
 * @package TotalPoll\Modules\Extensions\Troubleshooter\Checks
 */
abstract class Checkup implements Arrayable, \JsonSerializable {
	const CONTEXT_CHECK = 'check';
	const CONTEXT_FIX = 'fix';

	/**
	 * @var \WP_Error
	 */
	protected $bag;

	/**+
	 * Checkup constructor.
	 */
	public function __construct() {
		$this->bag = new \WP_Error();

		add_action( 'wp_ajax_' . $this->getHookName( self::CONTEXT_CHECK ), [ $this, 'run' ] );
		add_action( 'wp_ajax_' . $this->getHookName( self::CONTEXT_FIX ), [ $this, 'run' ] );
	}

	/**
	 * Get checkup id.
	 *
	 * @return string
	 */
	public function getId() {
		$basename = basename( str_replace( '\\', '/', get_class( $this ) ) );
		$basename = preg_replace( '/(?<!^)[A-Z]/', '_$0', $basename );

		return sanitize_title_with_dashes( $basename );
	}

	/**
	 * Get hook name.
	 *
	 * @param string $context
	 *
	 * @return string
	 */
	public function getHookName( $context = self::CONTEXT_CHECK ) {
		return "totalpoll_checkup_{$context}_{$this->getId()}";
	}

	/**
	 * Get endpoint name.
	 *
	 * @param string $context
	 *
	 * @return string
	 */
	public function getEndpoint( $context = self::CONTEXT_CHECK ) {
		$params = [
			'action' => $this->getHookName( $context ),
		];

		return add_query_arg( $params, admin_url( 'admin-ajax.php' ) );
	}

	/**
	 * @param string $message
	 *
	 * @return void
	 */
	public function addError( $message ) {
		$this->bag->add( 'error', $message );
	}

	/**
	 * @param string $warning
	 *
	 * @return void
	 */
	public function addWarning( $warning ) {
		$this->bag->add( 'warning', $warning );
	}

	/**
	 * @return bool
	 */
	public function isFixable() {
		return false;
	}

	/**
	 * @return void
	 */
	public function fix() {

	}

	/**
	 * @return void
	 */
	public function run() {
		if ( doing_action( 'wp_ajax_' . $this->getHookName( self::CONTEXT_CHECK ) ) ) {
			$this->check();
		} elseif ( doing_action( 'wp_ajax_' . $this->getHookName( self::CONTEXT_FIX ) ) ) {
			$this->fix();
		}

		$wrapper = function ( $text ) {
			return "<p>{$text}</p>";
		};

		$errors = array_map( $wrapper, $this->bag->get_error_messages( 'error' ) );
		if ( ! empty( $errors ) ):
			wp_send_json_error( [ 'errors' => implode( "\r\n", $errors ), 'fixable' => $this->isFixable() ], 422 );
		endif;

		$warnings = array_map( $wrapper, $this->bag->get_error_messages( 'warning' ) );
		if ( ! empty( $warnings ) ):
			wp_send_json_error( [ 'warnings' => implode( "\r\n", $warnings ) ], 422 );
		endif;

		wp_send_json_success();
	}

	/**
	 * Cast to array.
	 *
	 * @return array
	 */
	public function toArray() {
		return [
			'id'                => $this->getId(),
			'name'              => $this->getName(),
			'description'       => $this->getDescription(),
			self::CONTEXT_CHECK => $this->getHookName( self::CONTEXT_CHECK ),
			self::CONTEXT_FIX   => $this->getHookName( self::CONTEXT_FIX ),
		];
	}

	/**
	 * Cast to JSON.
	 *
	 * @return array
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return $this->toArray();
	}

	/**
	 * Get checkup name.
	 *
	 * @return string
	 */
	abstract public function getName();

	/**
	 * Get checkup description.
	 *
	 * @return string
	 */
	abstract public function getDescription();

	/**
	 * @return void
	 */
	abstract public function check();
}
