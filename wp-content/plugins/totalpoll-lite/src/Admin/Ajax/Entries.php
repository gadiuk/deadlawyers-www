<?php

namespace TotalPoll\Admin\Ajax;

use TotalPoll\Contracts\Entry\Model;
use TotalPoll\Contracts\Entry\Repository;
use TotalPoll\Helpers\DateTime;
use TotalPollVendors\TotalCore\Contracts\Http\Request;
use TotalPollVendors\TotalCore\Export\ColumnTypes\DateColumn;
use TotalPollVendors\TotalCore\Export\ColumnTypes\TextColumn;
use TotalPollVendors\TotalCore\Export\Spreadsheet;
use TotalPollVendors\TotalCore\Export\Writer;
use TotalPollVendors\TotalCore\Export\Writers\CsvWriter;
use TotalPollVendors\TotalCore\Export\Writers\HTMLWriter;
use TotalPollVendors\TotalCore\Export\Writers\JsonWriter;
use TotalPollVendors\TotalCore\Helpers\Arrays;
use TotalPollVendors\TotalCore\Helpers\Tracking;

/**
 * Class Entries
 * @package TotalPoll\Admin\Ajax
 * @since   1.0.0
 */
class Entries {
	/**
	 * @var Request $request
	 */
	protected $request;
	/**
	 * @var Repository $entry
	 */
	protected $entry;
	/**
	 * @var array $criteria
	 */
	protected $criteria = [];

	/**
	 * Entries constructor.
	 *
	 * @param Request $request
	 * @param Repository $entry
	 */
	public function __construct( Request $request, Repository $entry ) {
		$this->request = $request;
		$this->entry   = $entry;

		$this->criteria = [
			'page'   => absint( $this->request->request( 'page', 1 ) ),
			'poll'   => $this->request->request( 'poll', null ),
			'from'   => $this->request->request( 'from', null ),
			'to'     => $this->request->request( 'to', null ),
			'format' => $this->request->request( 'format', null ),
		];
	}

	/**
	 * Get AJAX endpoint.
	 * @action-callback wp_ajax_totalpoll_entries_list
	 */
	public function fetch() {
		$args = [ 'conditions' => [ 'date' => [] ], 'page' => $this->criteria['page'] ];

		if ( $this->criteria['poll'] && current_user_can( 'edit_poll', $this->criteria['poll'] ) ):
			$args['conditions']['poll_id'] = $this->criteria['poll'];
		else:
			wp_send_json_error( esc_html__( 'Invalid Poll ID', 'totalpoll' ) );
		endif;

		if ( $this->criteria['from'] && DateTime::strptime( $this->criteria['from'], '%Y-%m-%d' ) ):
			$args['conditions']['date'][] = [ 'operator' => '>=', 'value' => "{$this->criteria['from']} 00:00:00" ];
		endif;

		if ( $this->criteria['to'] && DateTime::strptime( $this->criteria['to'], '%Y-%m-%d' ) ):
			$args['conditions']['date'][] = [ 'operator' => '<=', 'value' => "{$this->criteria['to']} 23:59:59" ];
		endif;

		$entries = $this->entry->get( $args );
		/**
		 * Filters the list of entries sent to entries browser.
		 *
		 * @param Model[] $entries Array of entries models.
		 * @param array $criteria Array of criteria.
		 *
		 * @return array
		 * @since 4.0.0
		 */
		$entries = apply_filters( 'totalpoll/filters/admin/entries/fetch', $entries, $this->criteria );

		wp_send_json( [ 'entries' => $entries, 'lastPage' => count( $entries ) === 0 || count( $entries ) < 10 ] );
	}

	/**
	 * Download AJAX endpoint.
	 * @action-callback wp_ajax_totalpoll_entries_download
	 */
	public function download() {
		$args = [ 'conditions' => [ 'date' => [] ], 'perPage' => - 1 ];

		if ( $this->criteria['poll'] ):
			$args['conditions']['poll_id'] = $this->criteria['poll'];
		endif;

		if ( $this->criteria['from'] && DateTime::strptime( $this->criteria['from'], '%Y-%m-%d' ) ):
			$args['conditions']['date'][] = [ 'operator' => '>=', 'value' => "{$this->criteria['from']} 00:00:00" ];
		endif;

		if ( $this->criteria['to'] && DateTime::strptime( $this->criteria['to'], '%Y-%m-%d' ) ):
			$args['conditions']['date'][] = [ 'operator' => '<=', 'value' => "{$this->criteria['to']} 23:59:59" ];
		endif;

		$entries = (array) $this->entry->get( $args );
		$fields  = Arrays::getDeep( json_decode( get_post( $args['conditions']['poll_id'] )->post_content, true ), [ 'fields' ], [] );
		/**
		 * Filters the list of entries to be exported.
		 *
		 * @param Model[] $entries Array of entries models.
		 *
		 * @return array
		 * @since 4.0.0
		 */
		$entries = apply_filters( 'totalpoll/filters/admin/entries/export/entries', $entries );
		/**
		 * Filters the list of fields used in exported file of entries.
		 *
		 * @param array $fields Array of form fields.
		 *
		 * @return array
		 * @since 4.0.0
		 */
		$fields = apply_filters( 'totalpoll/filters/admin/entries/export/fields', $fields );

		$export = new Spreadsheet();

		foreach ( $fields as $field ):
			$export->addColumn( new TextColumn( $field['label'] ?: $field['name'] ) );
		endforeach;

		$export->addColumn( new TextColumn( 'Choices' ) );
		$export->addColumn( new DateColumn( 'Date' ) );
		$export->addColumn( new TextColumn( 'Log ID' ) );
		$export->addColumn( new TextColumn( 'User ID' ) );
		$export->addColumn( new TextColumn( 'User login' ) );
		$export->addColumn( new TextColumn( 'User name' ) );
		$export->addColumn( new TextColumn( 'User email' ) );
		$export->addColumn( new TextColumn( 'Details' ) );

		/**
		 * Fires after setup essential columns and before populating data. Useful for define new columns.
		 *
		 * @param Spreadsheet $export Spreadsheet object.
		 * @param array $fields Array of poll's form fields.
		 * @param array $entries Array of entries.
		 *
		 * @since 4.0.0
		 */
		do_action( 'totalpoll/actions/admin/entries/export/columns', $export, $fields, $entries );

		foreach ( $entries as $entry ):
			$row = [];
			foreach ( $fields as $field ):
				$row[] = $entry->getField( $field['name'], 'N/A' );
			endforeach;

			$log   = $entry->getLog();
			$row[] = wp_strip_all_tags( empty( $log['details']['choices'] ) ? 'N/A' : implode( ', ', (array) $log['details']['choices'] ) );
			$row[] = $entry->getDate();
			$row[] = $entry->getLogId();
			$row[] = $entry->getUserId() ?: 'N/A';
			$row[] = $entry->getUser()->user_login ?: 'N/A';
			$row[] = $entry->getUser()->display_name ?: 'N/A';
			$row[] = $entry->getUser()->user_email ?: 'N/A';
			$row[] = $this->criteria['format'] !== 'json' ? json_encode( $entry->getDetails(), JSON_UNESCAPED_UNICODE ) : $entry->getDetails();
			/**
			 * Filters a row of exported entries.
			 *
			 * @param array $row Array of values.
			 * @param Model $entry Entry model.
			 *
			 * @return array
			 * @since 4.0.0
			 */
			$row = apply_filters( 'totalpoll/filters/admin/entries/export/row', $row, $entry );

			$export->addRow( $row );
		endforeach;

		if ( empty( $this->criteria['format'] ) ):
			$this->criteria['format'] = 'default';
		endif;

		$format = $this->criteria['format'];

		
		$writer = new HTMLWriter();
		

		

		/**
		 * Filters the file writer for a specific format when exporting form entries.
		 *
		 * @param Writer $writer Writer object.
		 *
		 * @return Writer
		 * @since 4.0.0
		 */
		$writer = apply_filters( "totalpoll/filters/admin/entries/export/writer/{$format}", $writer );

		$writer->includeColumnHeaders = true;

		$export->download( $writer, 'totalpoll-export-entries-' . date( 'Y-m-d H:i:s' ) );

		exit;
	}

	/**
	 * Get poll AJAX endpoint.
	 * @action-callback wp_ajax_totalpoll_entries_polls
	 */
	public function polls() {

		$queryArgs = [
			'status'  => 'any',
			'perPage' => - 1,
		];

		if ( ! current_user_can( 'edit_others_polls' ) ):
			$queryArgs['wpQuery']['author'] = get_current_user_id();
		endif;

		$polls = array_map(
			function ( $poll ) {
				/**
				 * @var \TotalPoll\Poll\Model $poll
				 */
				$fields = array_map( function ( $field ) {
					return [ 'name' => $field['name'], 'label' => $field['label'] ];
				}, $poll->getFields() );


				$columns = [
					'id'     => $poll->getId(),
					'title'  => $poll->getTitle(),
					'fields' => array_values( $fields )
				];

				/**
				 * Filters the poll object sent to entries.
				 *
				 * @param array $pollRepresentation The representation of a poll.
				 * @param Model $poll Poll model object.
				 *
				 * @return array
				 * @since 4.0.0
				 */
				return apply_filters( 'totalpoll/filters/admin/entries/poll', $columns, $poll, $this );
			},

			TotalPoll( 'polls.repository' )->get( $queryArgs )
		);

		/**
		 * Filters the polls list sent to entries browser.
		 *
		 * @param Model[] $polls Array of poll models.
		 *
		 * @return array
		 * @since 4.0.0
		 */
		$polls = apply_filters( 'totalpoll/filters/admin/entries/polls', $polls );

		wp_send_json( $polls );
	}
	/**
	 * Remove ItemAJAX endpoint.
	 * @action-callback wp_ajax_totalpoll_remove
	 */
	public function purge() {
		$poll = $id = (int)$this->request->post('poll');
		$result = $this->entry->purge($poll);

		if($result) {
			wp_send_json_success();
		}

		wp_send_json_error();
	}


	/**
	 * Remove ItemAJAX endpoint.
	 * @action-callback wp_ajax_totalpoll_remove
	 */
	public function remove() {
		$id = (int)$this->request->post('id');

		$result = $this->entry->delete([
			'id' => $id
		]);

		if($result) {
			wp_send_json_success();
		}

		wp_send_json_error();
	}
}
