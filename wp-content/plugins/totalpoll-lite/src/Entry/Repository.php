<?php

namespace TotalPoll\Entry;

use TotalPoll\Contracts\Entry\Repository as RepositoryContract;
use TotalPollVendors\TotalCore\Contracts\Http\Request;
use TotalPollVendors\TotalCore\Helpers\Arrays;
use TotalPollVendors\TotalCore\Helpers\Sql;

/**
 * Entry Repository.
 *
 * @package TotalPoll\Entry
 * @since   1.0.0
 */
class Repository implements RepositoryContract {
	/**
	 * @var Request $request
	 */
	protected $request;
	/**
	 * @var \wpdb $database
	 */
	protected $database;
	/**
	 * @var array $env
	 */
	protected $env;

	/**
	 * Repository constructor.
	 *
	 * @param Request $request
	 * @param \wpdb   $database
	 * @param         $env
	 */
	public function __construct( Request $request, \wpdb $database, $env ) {
		$this->request  = $request;
		$this->database = $database;
		$this->env      = $env;
	}

	/**
	 * Get entries.
	 *
	 * @param $query
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	public function get( $query ) {
		$args = Arrays::parse( $query, [
			'conditions'     => [],
			'page'           => 1,
			'perPage'        => 10,
			'orderBy'        => 'date',
			'orderDirection' => 'DESC',
		] );
		// Models
		$entryModels = [];

		/**
		 * Filters the list of arguments used for get entries query.
		 *
		 * @param array $args  Arguments.
		 * @param array $query Query.
		 *
		 * @return array
		 * @since 4.0.0
		 */
		$args = apply_filters( 'totalpoll/filters/entries/get/query', $args, $query );
		// Where clause
		$where = Sql::generateWhereClause( $args['conditions'] );
		// Order
		$order = Sql::generateOrderClause( $args['orderBy'], $args['orderDirection'] );
		// Limit clause
		$limit = $args['perPage'] === - 1 ? '' : Sql::generateLimitClause( $args['page'], $args['perPage'] );

		// Finally our fancy SQL query
		$query = "SELECT * FROM `{$this->env['db.tables.entries']}` {$where} {$order} {$limit}";

		// Get results
		$entries = (array) $this->database->get_results( $query, ARRAY_A );
		// Iterate and convert each row to entry model
		foreach ( $entries as $entry ):
			$entryModels[] = new Model( $entry );
		endforeach;

		/**
		 * Filters the results of get entries query.
		 *
		 * @param \TotalPoll\Contracts\Entry\Model[] $entryModels Entries models.
		 * @param array                              $args        Arguments.
		 * @param array                              $query       Query.
		 *
		 * @return array
		 * @since 4.0.0
		 */
		$entryModels = apply_filters( 'totalpoll/filters/entries/get/results', $entryModels, $args, $query );

		// Return models
		return $entryModels;
	}

	/**
	 * Get entry by id.
	 *
	 * @param $entryId
	 *
	 * @return \TotalPoll\Contracts\Entry\Model|null
	 * @since 4.0.8
	 */
	public function getById( $entryId ) {
		$result = $this->get( [ 'conditions' => [ 'id' => (int) $entryId ] ] );

		return empty( $result ) ? null : $result[0];
	}

	/**
	 * Get entry by id.
	 *
	 * @param $logId
	 *
	 * @return \TotalPoll\Contracts\Entry\Model|null
	 * @since 4.0.8
	 */
	public function getByLogId( $logId ) {
		$result = $this->get( [ 'conditions' => [ 'log_id' => (int) $logId ] ] );

		return empty( $result ) ? null : $result[0];
	}

	/**
	 * Create an entry.
	 *
	 * @param $attributes
	 *
	 * @return \TotalPoll\Contracts\Entry\Model|\WP_Error
	 * @since 1.0.0
	 */
	public function create( $attributes ) {

		$attributes = Arrays::parse(
			$attributes,
			[
				'user_id' => get_current_user_id(),
				'date'    => TotalPoll( 'datetime' ),
				'fields'  => [],
				'details' => [],
			]
		);

		/**
		 * Filters the attributes of an entry model used for insertion.
		 *
		 * @param array $attributes Entry attributes.
		 *
		 * @return array
		 * @since 4.0.0
		 */
		$attributes = apply_filters( 'totalpoll/filters/entries/insert/attributes', $attributes );

		if ( empty( $attributes['poll_id'] ) || empty( $attributes['fields'] ) ):
			return new \WP_Error( 'missing_fields', esc_html__( 'poll_id and fields are required' ) );
		endif;

		$entryModelAttributes = [
			'date'    => $attributes['date']->format( 'Y-m-d H:i:s' ),
			'user_id' => (int) $attributes['user_id'],
			'poll_id' => (int) $attributes['poll_id'],
			'log_id'  => $attributes['log_id'],
			'fields'  => json_encode( (array) $attributes['fields'] ),
			'details' => json_encode( (array) $attributes['details'] ),
		];

		$inserted = $this->database->insert( $this->env['db.tables.entries'], $entryModelAttributes );

		if ( ! $inserted ):
			return new \WP_Error( 'insert_fail', esc_html__( 'Unable to insert the entry.', 'totalpoll' ) );
		endif;

		$entryModelAttributes['id'] = $this->database->insert_id;


		/**
		 * Filters the entry model attributes after insertion.
		 *
		 * @param array $entryModelAttributes Entry attributes.
		 * @param array $attributes           Original insertion attributes.
		 *
		 * @return array
		 * @since 4.0.0
		 */
		$entryModelAttributes = apply_filters( 'totalpoll/filters/entries/insert/model', $entryModelAttributes, $attributes );

		return new Model( $entryModelAttributes );
	}

	/**
	 * Delete entries.
	 *
	 * @param $query array
	 *
	 * @return bool|\WP_Error
	 * @since 1.0.0
	 */

	public function delete( $query ) {
		$where = Sql::generateWhereClause( $query );

		if ( empty( $where ) ):
			return new \WP_Error( 'no_conditions', esc_html__( 'No conditions were specified', 'totalpoll' ) );
		endif;

		$query = "DELETE FROM `{$this->env['db.tables.entries']}` {$where}";

		return (bool) $this->database->query( $query );

	}

	/**
	 * Purge entries of a poll.
	 *
	 * @param int $poll
	 *
	 * @return bool|WP_Error
	 * @since 1.0.0
	 */
	public function purge( $poll ) {

		if ( $poll ) {
			$query = "DELETE FROM `{$this->env['db.tables.entries']}` WHERE poll_id = %d";
			$query = $this->database->prepare( $query, $poll );
		}

		return $this->database->query( $query ) !== false;

	}

	/**
	 * Purge all entries.
	 * @return bool|WP_Error
	 * @since 1.0.0
	 */
	public function purgeAllEntries() {

		$query = "DELETE FROM `{$this->env['db.tables.entries']}`";
		return $this->database->query( $query ) !== false;
	}


	/**
	 * Count entries.
	 *
	 * @param $query
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	public function count( $query ) {
		$args = Arrays::parse( $query, [ 'conditions' => [], ] );

		/**
		 * Filters the list of arguments used for count entries query.
		 *
		 * @param array $args  Arguments.
		 * @param array $query Query.
		 *
		 * @return array
		 * @since 4.0.0
		 */
		$args = apply_filters( 'totalpoll/filters/entries/count/query', $args, $query );

		// Where clause
		$where = Sql::generateWhereClause( $args['conditions'] );
		// Finally our fancy SQL query
		$query = "SELECT COUNT(*) FROM `{$this->env['db.tables.entries']}` {$where}";

		// Get count
		return (int) $this->database->get_var( $query );
	}

	/**
	 * Anonymize entries.
	 *
	 * @param $query
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	public function anonymize( $query ) {
		$args = Arrays::parse( $query, [
			'conditions' => [],
		] );

		/**
		 * Filters the list of arguments used for anonymize entries query.
		 *
		 * @param array $args  Arguments.
		 * @param array $query Query.
		 *
		 * @return array
		 * @since 4.0.0
		 */
		$args = apply_filters( 'totalpoll/filters/entries/anonymize/query', $args, $query );

		// Where clause
		$where = Sql::generateWhereClause( $args['conditions'] );

		if ( empty( $where ) ):
			return new \WP_Error( 'no_conditions', esc_html__( 'No conditions were specified', 'totalpoll' ) );
		endif;

		// Finally our fancy SQL query
		$query = "UPDATE `{$this->env['db.tables.entries']}` SET `user_id` = 0, `log_id` = 0, `fields` = '[]', `details` = '{\"anonymized\":true}' {$where}";

		// Get results
		return (bool) $this->database->query( $query );

	}
}
