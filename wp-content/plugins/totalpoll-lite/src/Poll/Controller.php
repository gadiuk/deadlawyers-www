<?php

namespace TotalPoll\Poll;


use TotalPoll\Contracts\Poll\Repository;
use TotalPollVendors\TotalCore\Contracts\Http\Request;

/**
 * Poll Controller.
 *
 * @package TotalPoll\Poll
 */
class Controller {
	/**
	 * @var Request $request
	 */
	protected $request;
	/**
	 * @var Repository $repository
	 */
	protected $repository;
	/**
	 * @var null|\TotalPoll\Contracts\Poll\Model $poll
	 */
	protected $poll;

	/**
	 * Controller constructor.
	 *
	 * @param  Request  $request
	 * @param  Repository  $repository
	 */
	public function __construct( Request $request, Repository $repository ) {
		$this->request    = $request;
		$this->repository = $repository;

		$pollId     = (int) $this->request->request( 'totalpoll.pollId' );
		$this->poll = $repository->getById( $pollId );

		if ( $this->poll ):
			add_action( 'totalpoll/actions/request/welcome', [ $this, 'welcome' ] );
			add_action( 'totalpoll/actions/request/vote', [ $this, 'vote' ] );
			add_action( 'totalpoll/actions/request/thankyou', [ $this, 'thankyou' ] );
			add_action( 'totalpoll/actions/request/results', [ $this, 'results' ] );
			add_action( 'totalpoll/actions/request/view', [ $this, 'view' ] );
			add_action( 'totalpoll/actions/ajax-request', function () {
				echo $this->poll->render();
				wp_die();
			} );
		endif;
	}

	/**
	 * Welcome.
	 */
	public function welcome() {
		$this->poll->setScreen( 'vote' );
	}

	/**
	 * Vote.
	 */
	public function vote() {
		$this->poll->setScreen( 'vote' );

		if ( $this->poll->getForm()->validate() ):
			$countVote = false;

			if ( $this->poll->isAcceptingVotes() ):
				$countVote = TotalPoll( 'polls.commands.vote.count', [ $this->poll ] )->execute();
				if ( is_wp_error( $countVote ) ):
					$this->poll->setError( $countVote );
				endif;
			endif;

			$log = TotalPoll( 'polls.commands.vote.log', [ $this->poll ] )->execute();

			if ( $countVote && ! is_wp_error( $countVote ) ):
				$this->poll->getRestrictions()->apply();
				$this->poll->setScreen( 'thankyou' );

				if ( ! is_wp_error( $log ) ):
					TotalPoll( 'polls.commands.vote.entry', [ $this->poll ] )->execute();
					TotalPoll( 'polls.commands.vote.notify.newVote', [ $this->poll ] )->execute();
					TotalPoll( 'polls.commands.vote.notify.newChoice', [ $this->poll ] )->execute();
				endif;
			endif;
		endif;
	}

	/**
	 * Thank you.
	 */
	public function thankyou() {
		$this->poll->setScreen( 'results' );
	}

	/**
	 * Results.
	 */
	public function results() {
		$this->poll->setScreen( 'results' );
	}

	public function view() {
		$screen = esc_html( $this->request->query( 'totalpoll.screen' ) );
		$this->poll->setScreen( $screen );
	}

}
