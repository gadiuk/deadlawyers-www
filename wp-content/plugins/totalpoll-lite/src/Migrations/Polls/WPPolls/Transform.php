<?php

namespace TotalPoll\Migrations\Polls\WPPolls;

use TotalPoll\Contracts\Migrations\Poll\Transform as TransformContract;
use TotalPoll\Migrations\Polls\Templates\LogEntry;
use TotalPoll\Migrations\Polls\Templates\Options;
use TotalPoll\Migrations\Polls\Templates\Poll;
use TotalPoll\Migrations\Polls\Templates\Submission;
use TotalPollVendors\TotalCore\Helpers\Misc;

/**
 * Transform Poll.
 * @package TotalPoll\Migrations\Polls\WPPolls
 */
class Transform implements TransformContract {
	/**
	 * Transform poll.
	 *
	 * @param array $raw
	 *
	 * @return Poll
	 */
	public function transformPoll( $raw ) {
		// Create template
		$template = new Poll();

		// Title
		$template->setTitle( $raw['question'] );

		$template->setId( $raw['id'] );

		// Question
		$question = [
			'uid'      => Misc::generateUid(),
			'content'  => $raw['question'],
			'settings' => [
				'selection' => [
					'minimum' => 1,
					'maximum' => 1,
				],
			],
			'choices'  => [],
		];

		if ( ! empty( $raw['multiple'] ) ):
			$question['settings']['selection']['maximum'] = absint( $raw['multiple'] );
		endif;

		foreach ( $raw['choices'] as $choice ):
			$question['choices'][] = [
				'uid'           => Misc::generateUid(),
				'votes'         => $choice['votes'],
				'votesOverride' => $choice['votes'],
				'visibility'    => true,
				'type'          => 'text',
				'label'         => wp_unslash( $choice['label'] ),
			];
		endforeach;

		$template->addQuestion( $question );

		// Settings
		if ( ! empty( $raw['options']['poll_logging_method'] ) ):
			if ( ! empty( $raw['options']['poll_cookielog_expiry'] ) ):
				$template->addSettings( 'vote.frequency.timeout', absint( $raw['options']['poll_cookielog_expiry'] ) );
			endif;

			if ( $raw['options']['poll_logging_method'] == 1 || $raw['options']['poll_logging_method'] == 3 ):
				$template->addSettings( 'vote.frequency.cookies.enabled', true );
			endif;

			if ( $raw['options']['poll_logging_method'] == 2 || $raw['options']['poll_logging_method'] == 3 ):
				$template->addSettings( 'vote.frequency.ip.enabled', true );
			endif;
		endif;

		if ( ! empty( $raw['options']['poll_allowtovote'] ) && $raw['options']['poll_allowtovote'] == 1 ):
			$roles = [];
			foreach ( get_editable_roles() as $role => $details ):
				$roles[] = $role;
			endforeach;

			$template->addSettings( 'vote.limitations.membership.enabled', true );
			$template->addSettings( 'vote.limitations.membership.roles', $roles );
		endif;

		if ( ! empty( $raw['start_date'] ) ):
			$template->addSettings( 'vote.limitations.period.enabled', true );
			$template->addSettings( 'vote.limitations.period.start', $raw['start_date'] );
		endif;

		if ( ! empty( $raw['end_date'] ) ):
			$template->addSettings( 'vote.limitations.period.enabled', true );
			$template->addSettings( 'vote.limitations.period.end', $raw['end_date'] );
		endif;

		if ( ! empty( $raw['options']['poll_ans_sortby'] ) ):
			$template->addSettings( 'choices.sort.direction', $raw['options']['poll_ans_sortorder'] );

			if ( $raw['options']['poll_ans_sortby'] === 'polla_aid' ):
				$template->addSettings( 'choices.sort.field', 'position' );
			elseif ( $raw['options']['poll_ans_sortby'] === 'polla_answers' ):
				$template->addSettings( 'choices.sort.field', 'label' );
			elseif ( $raw['options']['poll_ans_sortby'] === 'RAND()' ):
				$template->addSettings( 'choices.sort.field', 'random' );
			endif;
		endif;

		if ( ! empty( $raw['options']['poll_ans_result_sortby'] ) ):
			$template->addSettings( 'results.sort.direction', $raw['options']['poll_ans_result_sortorder'] );

			if ( $raw['options']['poll_ans_result_sortby'] === 'polla_votes' ):
				$template->addSettings( 'results.sort.field', 'votes' );
			elseif ( $raw['options']['poll_ans_result_sortby'] === 'polla_aid' ):
				$template->addSettings( 'results.sort.field', 'position' );
			elseif ( $raw['options']['poll_ans_result_sortby'] === 'polla_answers' ):
				$template->addSettings( 'results.sort.field', 'label' );
			elseif ( $raw['options']['poll_ans_result_sortby'] === 'RAND()' ):
				$template->addSettings( 'results.sort.field', 'random' );
			endif;
		endif;

		// Design
		$template->addSettings( 'design.template', 'basic-template' );
		$defaults = TotalPoll( 'modules.repository' )->getDefaults( 'basic-template' ) ?: [];
		$template->addSettings( 'design.custom', $defaults );

		if ( ! empty( $raw['options']['poll_bar_bg'] ) ):
			$template->addSettings( 'design.custom.votesbar.colors.start', '#' . $raw['options']['poll_bar_bg'] );
			$template->addSettings( 'design.custom.votesbar.colors.end', '#' . $raw['options']['poll_bar_bg'] );
		endif;

		if ( ! empty( $raw['options']['poll_bar_border'] ) ):
			$template->addSettings( 'design.custom.votesbar.colors.border', '#' . $raw['options']['poll_bar_border'] );
		endif;

		return $template;
	}

	/**
	 * Transform options.
	 *
	 * @param array $raw
	 *
	 * @return Options
	 */
	public function transformOptions( $raw ) {
		// Create template
		$template = new Options();

		return $template;
	}

	/**
	 * Transform log.
	 *
	 * @param array $raw
	 *
	 * @return LogEntry
	 */
	public function transformLog( $raw ) {
		// Create template
		$template = new LogEntry();

		// Attributes
		foreach ( $raw as $key => $value ):
			$template[ $key ] = $value;
		endforeach;

		return $template;
	}

	/**
	 * @param $raw
	 *
	 * @return Submission
	 */
	public function transformSubmission( $raw ) {
		// Create template
		$template = new Submission();

		// Attributes
		foreach ( $raw as $key => $value ):
			$template[ $key ] = $value;
		endforeach;

		return $template;
	}
}
