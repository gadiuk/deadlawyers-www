<?php

namespace TotalPoll\Admin\Ajax;

use TotalPoll\Contracts\Entry\Repository as EntryRepository;
use TotalPoll\Contracts\Poll\Model;
use TotalPoll\Contracts\Poll\Repository as PollRepository;
use TotalPollVendors\TotalCore\Contracts\Admin\Account;
use TotalPollVendors\TotalCore\Contracts\Admin\Activation;
use TotalPollVendors\TotalCore\Contracts\Http\Request;

/**
 * Class Dashboard
 * @package TotalPoll\Admin\Ajax
 * @since   1.0.0
 */
class Dashboard {
	/**
	 * @var Request $request
	 */
	protected $request;
	/**
	 * @var Activation $activation
	 */
	protected $activation;
	/**
	 * @var Account $account
	 */
	protected $account;
	/**
	 * @var PollRepository $pollRepository
	 */
	private $pollRepository;
	/**
	 * @var EntryRepository $entryRepository
	 */
	private $entryRepository;

	/**
	 * Dashboard constructor.
	 *
	 * @param Request $request
	 * @param Activation $activation
	 * @param Account $account
	 * @param PollRepository $pollRepository
	 * @param EntryRepository $entryRepository
	 */
	public function __construct(
		Request $request,
		Activation $activation,
		Account $account,
		PollRepository $pollRepository,
		EntryRepository $entryRepository
	) {
		$this->request         = $request;
		$this->activation      = $activation;
		$this->account         = $account;
		$this->pollRepository  = $pollRepository;
		$this->entryRepository = $entryRepository;
	}

	/**
	 * Activation AJAX endpoint.
	 * @action-callback wp_ajax_totalpoll_dashboard_activate
	 */
	public function activate() {
		
	}

	/**
	 * Deactivation AJAX endpoint.
	 * @action-callback wp_ajax_totalpoll_dashboard_deactivate
	 */
	public function deactivate() {

		try{
			$this->activation->setLicenseKey( "" );
			$this->activation->setLicenseEmail( "" );
			$this->activation->setLicenseStatus( false );
			wp_send_json_success( 'Unlinked license!' );
		}
		catch(\Exception $e){
			wp_send_json_error( "Error occurred when unlinking your license!" );

		}

	}


	/**
	 * Get polls AJAX endpoint.
	 * @action-callback wp_ajax_totalpoll_dashboard_polls_overview
	 */
	public function polls() {
		$polls = array_map(
			function ( $poll ) {
				/**
				 * Filters the poll object sent to dashboard.
				 *
				 * @param array $pollRepresentation The representation of a poll.
				 * @param Model $poll Poll model object.
				 *
				 * @return array
				 * @since 4.0.0
				 */
				return apply_filters(
					'totalpoll/filters/admin/dashboard/poll',
					[
						'id'         => $poll->getId(),
						'title'      => $poll->getTitle(),
						'status'     => get_post_status( $poll->getPollPost() ),
						'permalink'  => $poll->getPermalink(),
						'editLink'   => admin_url( 'post.php?post=' . $poll->getId() . '&action=edit' ),
						'statistics' => [
							'votes'   => $poll->getTotalVotes(),
							'entries' => $this->entryRepository->count( [ 'conditions' => [ 'poll_id' => $poll->getId() ] ] ),
						],
					],
					$poll,
					$this
				);
			},
			$this->pollRepository->get( [ 'status' => 'any', 'perPage' => 100 ] )
		);

		/**
		 * Filters the polls list sent to dashboard.
		 *
		 * @param Model[] $polls Array of poll models.
		 *
		 * @return array
		 * @since 4.0.0
		 */
		$polls = apply_filters( 'totalpoll/filters/admin/dashboard/polls', $polls );

		wp_send_json( $polls );
	}

	/**
	 * TotalSuite Account AJAX endpoint.
	 */
	public function account() {
		
	}

	/**
	 * TotalSuite Blog AJAX endpoint.
	 */
	public function blog() {
		// Retrieve from cache first
		$blogFeedEndPoint = TotalPoll()->env( 'api.blogFeed' );
		$cacheKey         = md5( $blogFeedEndPoint );
		if ( $cached = get_transient( $cacheKey ) ):
			return wp_send_json( $cached );
		endif;

		// Fetch
		$request = wp_remote_get( $blogFeedEndPoint );

		// Decode response
		$response  = json_decode( wp_remote_retrieve_body( $request ), true ) ?: [];
		$blogPosts = [];

		if ( ! empty( $response ) ):
			$blogPosts = $response;

			// Cache
			set_transient( $cacheKey, $blogPosts, DAY_IN_SECONDS * 2 );
		endif;

		wp_send_json( $blogPosts );
	}
}
