<?php

namespace TotalPoll\Poll\Commands;

use ErrorException;
use Exception;
use TotalPoll\Contracts\Poll\Model;
use TotalPoll\Contracts\Poll\Repository;
use TotalPollVendors\TotalCore\Contracts\Http\Request;
use TotalPollVendors\TotalCore\Helpers\Arrays;
use TotalPollVendors\TotalCore\Helpers\Command;
use TotalPollVendors\TotalCore\Helpers\Strings;
use TotalPollVendors\TotalCore\Traits\Cookies;
use WP_Error;

/**
 * Class CountVote
 * @package TotalPoll\Poll\Commands
 */
class CountVote extends Command {
	use Cookies;

	/**
	 * @var Model $poll
	 */
	protected $poll;
	/**
	 * @var Request $request
	 */
	protected $request;
	/**
	 * @var Repository $repository
	 */
	protected $repository;

	/**
	 * CountVote constructor.
	 *
	 * @param Model $poll
	 * @param Request $request
	 * @param Repository $repository
	 */
	public function __construct( Model $poll, Request $request, Repository $repository ) {
		$this->poll       = $poll;
		$this->request    = $request;
		$this->repository = $repository;
	}

	/**
	 * Count vote logic.
	 *
	 * @return mixed
	 */
	protected function handle() {
		try {
			// Checking submitted choices against poll's questions. All questions must be present.
			$questions   = $this->poll->getQuestions();
			$userChoices = array_filter( (array) $this->request->request( 'totalpoll.choices', [] ) );
			// Cleanup
			foreach ( $userChoices as $questionUid => $choices ):
				$userChoices[ $questionUid ] = array_filter( (array) $choices );
			endforeach;

			$customChoices = [];
			$choicesUids   = [];

			/**
			 * Fires before processing vote request.
			 *
			 * @param array $userChoices Submitted choices (questionUID => choiceUID[]).
			 * @param Model $poll Poll model object.
			 *
			 * @since 4.0.0
			 */
			do_action( 'totalpoll/actions/before/poll/command/vote', $userChoices, $this->poll );

			foreach ( $questions as $questionUid => $question ):
				$other = null;

				// Prepare other choice
				if ( isset( $userChoices[ $questionUid ]['other'] ) ):
					$other = trim( sanitize_text_field( $userChoices[ $questionUid ]['other'] ) );
					unset( $userChoices[ $questionUid ]['other'] );
				endif;

				// Are custom choices allowed
				$allowCustomChoice = Arrays::getDotNotation( $question, 'settings.allowCustomChoice' );

				// Start processing if there is content in other field and custom choices are allowed
				if ( ! empty( $other ) && $allowCustomChoice ):
					$customChoice = false;

					// Search for matches in choices
					foreach ( $question['choices'] as $choiceUid => $choice ):
						if ( mb_strtolower( $choice['label'] ) === mb_strtolower( $other ) ):
							$customChoice = $choice;
							break;
						endif;
					endforeach;

					// No matches? let's insert it then
					if ( $customChoice ):
						// Set visibility to true temporarily
						$this->poll->setChoice( $customChoice['uid'], [ 'visibility' => true ], false );
					else:
						// Insert the choice.
						$customChoice = $this->poll->addChoice(
							[
								'label'      => $other,
								'visibility' => $allowCustomChoice !== 'hidden',
								'via'        => 'other-field',
							],
							$questionUid
						);

						// Set a cookie to alter visibility for the current user
						if ( ! $customChoice['visibility'] ):
							$customChoice['visibilityCookie'] = true;
							$this->poll->setChoice( $customChoice['uid'], [ 'visibility' => true ], false );
						endif;

					endif;

					// Add it to submitted choices.
					$userChoices[ $questionUid ][]         = $customChoice['uid'];
					$customChoices[ $customChoice['uid'] ] = $customChoice;
				endif;

				// Allow zero vote

				$minSelected = Arrays::getDotNotation( $question, 'settings.selection.minimum', 0 );
				$maxSelected = Arrays::getDotNotation( $question, 'settings.selection.maximum', 1 );
				$choices     = isset( $userChoices[ $questionUid ] ) ? (array) $userChoices[ $questionUid ] : [];

				if ( count( $choices ) < $minSelected ):
					$message = _n( 'You must vote for at least {{minimum}} choice.', 'You must vote for at least {{minimum}} choices.', $minSelected, 'totalpoll' );
					throw new ErrorException( Strings::template( $message, [ 'minimum' => $minSelected ] ) );
				elseif ( count( $choices ) > $maxSelected ):
					$message = _n( 'You can vote for up to {{maximum}} choice.', 'You can vote for up to {{maximum}} choices.', $maxSelected, 'totalpoll' );
					throw new ErrorException( Strings::template( $message, [ 'maximum' => $maxSelected ] ) );
				endif;

				// Check choices
				foreach ( $choices as $choiceUid ):
					$choice = $this->poll->getChoice( $choiceUid );
					// Choice doesn't exist or question uid is not correct,
					if ( ! $choice || $choice['questionUid'] !== $questionUid || ! $choice['visibility'] ) :
						throw new ErrorException( esc_html__( 'Unknown choice. Please try again.', 'totalpoll' ) );
					endif;

					$choiceCounter = apply_filters(
						'totalpoll/filters/poll/choice/count',
						[ 'uid' => $choiceUid, 'votes' => 1 ],
						$choice,
						$this->poll
					);

					$choicesUids[] = $choiceCounter;

					$this->poll->incrementChoiceVotes( $choiceCounter['uid'], $choiceCounter['votes'] );
				endforeach;
			endforeach;

			$choicesUids = apply_filters( 'totalpoll/filters/poll/choices/count', $choicesUids, $this->poll );

			if ( $this->repository->incrementVotes( $this->poll->getId(), $choicesUids ) === false ):
				throw new ErrorException( esc_html__( 'Something went wrong. Please try again.', 'totalpoll' ) );
			endif;


			if ( ! empty( $customChoices ) ):
				// Set visibility cookie
				foreach ( $customChoices as $customChoice ):
					if ( ! empty( $customChoice['visibilityCookie'] ) ):
						$this->setCookie( $this->poll->getPrefix( $customChoice['uid'] ), true, 0 );
					endif;
				endforeach;
				// Save the changes.
				$this->poll->save();
			endif;

			self::share('customChoices', $customChoices );
			/**
			 * Fires after processing vote request successfully.
			 *
			 * @param array $choicesUids Submitted choices UIDs and votes (uid => choiceUID, votes => castedVotes).
			 * @param array $customChoices Custom choices submitted through form (other field).
			 * @param Model $poll Poll model object.
			 *
			 * @since 4.0.0
			 */
			do_action( 'totalpoll/actions/after/poll/command/vote', $choicesUids, $customChoices, $this->poll );

			return true;
		} catch ( Exception $exception ) {
			foreach ( $customChoices as $choiceUid => $choice ):
				$this->poll->removeChoice( $choiceUid, false );
			endforeach;

			return new WP_Error( 'vote', $exception->getMessage() );
		}
	}
}
