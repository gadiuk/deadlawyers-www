<?php

namespace TotalPoll\Admin\Poll;

use TotalPoll\Contracts\Entry\Repository as EntryRepository;
use TotalPoll\Contracts\Log\Repository as LogRepository;
use TotalPoll\Contracts\Poll\Repository as PollRepository;
use TotalPollVendors\TotalCore\Foundation\Environment;
use TotalPollVendors\TotalCore\Helpers\Tracking;

/**
 * Class Listing
 * @package TotalPoll\Admin\Poll
 */
class Listing {
	/**
	 * @var PollRepository
	 */
	protected $pollRepository;
	/**
	 * @var EntryRepository
	 */
	protected $entryRepository;
	/**
	 * @var EntryRepository
	 */
	protected $logRepository;

	/**
	 * @var Environment
	 */
	private $env;

	/**
	 * Listing constructor.
	 *
	 * @param PollRepository $pollRepository
	 * @param EntryRepository $entryRepository
	 * @param LogRepository $logRepository
	 * @param $env
	 */
	public function __construct( PollRepository $pollRepository, EntryRepository $entryRepository, LogRepository $logRepository, $env ) {
		$this->pollRepository    = $pollRepository;
		$this->entryRepository   = $entryRepository;
		$this->logRepository     = $logRepository;
		$this->env               = $env;

		// Assets
		add_action( 'admin_enqueue_scripts', [ $this, 'assets' ] );

		// States
		add_filter( 'display_post_states', [ $this, 'states' ], 10, 2 );

		// Columns
		add_filter( 'manage_poll_posts_columns', [ $this, 'columns' ] );

		// Columns content
		add_action( 'manage_poll_posts_custom_column', [ $this, 'columnsContent' ], 10, 2 );
		add_filter( 'manage_edit-poll_sortable_columns', [ $this, 'columnsSortable' ], 10, 1 );

		// Actions
		add_filter( 'post_row_actions', [ $this, 'actions' ], 10, 2 );

		// Scope
		add_filter( 'pre_get_posts', [ $this, 'scope' ] );
	}

	/**
	 * Page assets.
	 */
	public function assets() {
		/**
		 * @asset-style totalpoll-admin-poll-listing
		 */
		wp_enqueue_style( 'totalpoll-admin-poll-listing' );
	}

	/**
	 * Columns.
	 *
	 * @param array $originalColumns
	 *
	 * @filter-callback manage_poll_posts_columns
	 * @return array
	 */
	public function columns( $originalColumns ) {

		/**
		 * Filters the list of columns in polls listing.
		 *
		 * @param array $columns Array of columns.
		 * @param array $originalColumns Array of original columns.
		 *
		 * @return array
		 * @since 4.0.0
		 */

		$columns = apply_filters( 'totalpoll/filters/admin/listing/columns', [
			'cb'      => '<input type="checkbox" />',
			'title'   => esc_html__( 'Title' ),
			'votes'   => esc_html__( 'Votes', 'totalpoll' ),
			'entries' => esc_html__( 'Entries', 'totalpoll' ),
			'log'     => esc_html__( 'Log', 'totalpoll' ),
			'date'    => esc_html__( 'Date' ),
		], $originalColumns );

		if ( ! current_user_can( 'manage_options' ) ):
			unset( $columns['log'] );
		endif;

		if ( ! current_user_can( 'edit_polls' ) ):
			unset( $columns['entries'] );
		endif;


		return $columns;
	}

	/**
	 * Columns content.
	 *
	 * @param $column
	 * @param $id
	 *
	 * @action-callback manage_poll_posts_custom_column
	 * @return void
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function columnsContent( $column, $id ) {

		// Votes column
		add_filter( 'totalpoll/filters/admin/listing/columns-content/votes', function ( $content, $id ) {
			return number_format_i18n( array_sum( $this->pollRepository->getVotes( $id ) ) );
		}, 10, 2 );

		// Entries column
		add_filter( 'totalpoll/filters/admin/listing/columns-content/entries', function ( $content, $id ) {
			return number_format_i18n( $this->entryRepository->count( [ 'conditions' => [ 'poll_id' => $id ] ] ) );
		}, 10, 2 );

		// Log column
		add_filter( 'totalpoll/filters/admin/listing/columns-content/log', function ( $content, $id ) {
			return number_format_i18n( $this->logRepository->count( [ 'conditions' => [ 'poll_id' => $id ] ] ) );
		}, 10, 2 );

		/**
		 * Filters the content of a column in polls listing.
		 *
		 * @param array $content Column content.
		 * @param array $id Poll post ID.
		 *
		 * @return string
		 * @since 4.0.0
		 */
		echo apply_filters( "totalpoll/filters/admin/listing/columns-content/{$column}", null, $id );
	}

	public function columnsSortable( $columns ) {
		$columns['id']    = 'id';
		$columns['votes'] = 'votes';

		return $columns;
	}

	/**
	 * Inline actions.
	 *
	 * @param $actions
	 * @param $post
	 *
	 * @filter-callback post_row_actions
	 * @return array
	 */
	public function actions( $actions, $post ) {
		$pollPostType = TP_POLL_CPT_NAME;

		if ( current_user_can( 'edit_poll', $post->ID ) ):
			$actions['entries']  = sprintf( '<a href="%s">%s</a>', esc_attr( wp_nonce_url( admin_url( "edit.php?post_type={$pollPostType}&page=entries&poll={$post->ID}" ) ) ), esc_html( esc_html__( 'Entries', 'totalpoll' ) ) );
			$actions['insights'] = sprintf( '<a href="%s">%s</a>', esc_attr( wp_nonce_url( admin_url( "edit.php?post_type={$pollPostType}&page=insights&poll={$post->ID}" ) ) ), esc_html( esc_html__( 'Insights', 'totalpoll' ) ) );
		endif;

		if ( current_user_can( 'manage_options' ) ):
			$actions['log'] = sprintf( '<a href="%s">%s</a>', esc_attr( wp_nonce_url( admin_url( "edit.php?post_type={$pollPostType}&page=log&poll={$post->ID}" ) ) ), esc_html( esc_html__( 'Log', 'totalpoll' ) ) );
		endif;

		if ( current_user_can( 'manage_options' ) ) :
			$url              = admin_url( "admin.php?action=reset_poll&post_type={$pollPostType}&poll={$post->ID}" );
			$confirm          = sprintf( "return prompt('%s') === 'confirm';", esc_js( esc_html__( 'Are you sure? This will remove votes, log entries and form entries. Please type "confirm" to continue', 'totalpoll' ) ) );
			$actions['reset'] = sprintf( '<a href="%s" onclick="%s" style="color: #a00">%s</a>',
				esc_attr( wp_nonce_url( $url, 'reset_poll' ) ),
				$confirm,
				esc_html( esc_html__( 'Reset Poll', 'totalpoll' ) )
			);
		endif;

		if ( current_user_can( 'manage_options' ) ) :
			$primaryItem = esc_html__( 'Export results', 'totalpoll' );

			$items = [];

			foreach ( [ 'csv', 'json', 'html' ] as $format ):
				$items[] = [
					'label' => sprintf( esc_html__( 'Export as %s', 'totalpoll' ), strtoupper( $format ) ),
					'url'   => wp_nonce_url(
						admin_url(
							sprintf( 'admin-ajax.php?action=%s&poll=%d&format=%s', 'totalpoll_insights_download', $post->ID, $format )
						),
						'totalpoll'
					)
				];
			endforeach;

			ob_start();
			include 'views/quick-action-menu.php';

			$actions['export-results'] = ob_get_clean();
		endif;

		/**
		 * Filters the list of available actions in polls listing (under each poll).
		 *
		 * @param array $actions Array of actions [id => url].
		 * @param \WP_Post $post Poll post.
		 *
		 * @return array
		 * @since 4.0.0
		 */
		return apply_filters( 'totalpoll/filters/admin/listing/actions', $actions, $post );
	}

	/**
	 * @param $states
	 * @param $post
	 *
	 * @return array
	 */
	public function states( $states, $post ) {
		if ( $post->post_status === 'publish' ):
			$states[] = esc_html__( 'Live', 'totalpoll' );
		else:
			$states[] = esc_html__( 'Offline', 'totalpoll' );
		endif;

		/**
		 * Filters the list of states actions in polls listing (beside each title).
		 *
		 * @param array $states Array of states [id => label].
		 *
		 * @return array
		 * @since 4.0.0
		 */
		return apply_filters( 'totalpoll/filters/admin/listing/states', $states, $post );
	}

	/**
	 * @param $query
	 *
	 * @return mixed
	 */
	public function scope( $query ) {
		if ( ! current_user_can( 'edit_others_polls' ) ):
			$query->set( 'author', get_current_user_id() );
		endif;


		$orderBy = $query->get( 'orderby' );

		if ( $orderBy == 'votes' ) {
			$order = $query->get( 'order', 'desc' ) === 'asc' ? 'ASC' : 'DESC';

			add_filter( 'posts_fields', function ( $fields ) {
				return "$fields, COALESCE((SELECT SUM(votes) FROM {$this->env['db.tables.votes']} WHERE {$GLOBALS['wpdb']->posts}.ID = {$this->env['db.tables.votes']}.poll_id), 0) as votes";
			} );

			add_filter( 'posts_orderby', function ( $orderBy ) use ( $order ) {
				return "votes {$order}";
			} );
		}

		return $query;
	}

}
