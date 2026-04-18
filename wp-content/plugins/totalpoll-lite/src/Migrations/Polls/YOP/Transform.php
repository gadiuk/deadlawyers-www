<?php

namespace TotalPoll\Migrations\Polls\YOP;

use TotalPoll\Contracts\Migrations\Poll\Transform as TransformContract;
use TotalPoll\Migrations\Polls\Templates\LogEntry;
use TotalPoll\Migrations\Polls\Templates\Options;
use TotalPoll\Migrations\Polls\Templates\Poll;
use TotalPoll\Migrations\Polls\Templates\Submission;
use TotalPollVendors\TotalCore\Helpers\Misc;

class Transform implements TransformContract {
	public function transformPoll( $raw ) {
		// Create template
		$template = new Poll();

		// Title
		if ( ! empty( $raw['poll_title'] ) ):
			$template->setTitle( $raw['poll_title'] );
		endif;

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

		if ( ! empty( $raw['options']['allow_multiple_answers'] ) ):
			$question['settings']['selection']['minimum'] = absint( $raw['options']['allow_multiple_answers_min_number'] );
			$question['settings']['selection']['maximum'] = absint( $raw['options']['allow_multiple_answers_number'] );
		endif;

		foreach ( $raw['choices'] as $choice ):
			$question['choices'][] = [
				'uid'           => Misc::generateUid(),
				'votes'         => (int) $choice['votes'],
				'votesOverride' => (int) $choice['votes'],
				'visibility'    => $choice['answer_status'] === 'active',
				'type'          => 'text',
				'label'         => $choice['answer'],
			];
		endforeach;

		// Fields
		if ( ! empty( $raw['fields'] ) ):
			foreach ( $raw['fields'] as $field ):
				if ( $field['status'] !== 'active' || empty( $field['name'] ) ):
					continue;
				endif;

				$template->addField( [
					'uid'         => Misc::generateUid(),
					'type'        => 'text',
					'name'        => sanitize_title_with_dashes( $field['name'] ),
					'label'       => $field['name'],
					'class'       => null,
					'validations' => [
						'filled' => $field['required'] === 'yes' ? [ 'enabled' => true ] : [],
						'email'  => stripos( $field['name'], 'email' ) !== false ? [ 'enabled' => true ] : [],
					],
					'attributes'  => null,
				] );
			endforeach;
		endif;

		// Settings
		if ( $raw['options']['blocking_voters_interval_unit'] === 'days' ):
			$blockingUnit = 1440 * 60;
		elseif ( $raw['options']['blocking_voters_interval_unit'] === 'hours' ):
			$blockingUnit = 60 * 60;
		else:
			$blockingUnit = 60;
		endif;

		if ( ! empty( $raw['options']['blocking_voters'] ) && in_array( 'cookie', $raw['options']['blocking_voters'] ) ):
			$template->addSettings( 'vote.frequency.cookies.enabled', true );
			$template->addSettings( 'vote.frequency.timeout', absint( $raw['options']['blocking_voters_interval_value'] ) * $blockingUnit );
		endif;

		if ( ! empty( $raw['options']['blocking_voters'] ) && in_array( 'ip', $raw['options']['blocking_voters'] ) ):
			$template->addSettings( 'vote.frequency.ip.enabled', true );
			$template->addSettings( 'vote.frequency.timeout', absint( $raw['options']['blocking_voters_interval_value'] ) * $blockingUnit );
		endif;

		if ( ! empty( $raw['options']['add_other_answers_to_default_answers'] ) && $raw['options']['add_other_answers_to_default_answers'] === 'yes' ):
			if ( ! empty( $raw['options']['choices']['other']['enabled'] ) ):
				$question['settings']['allowCustomChoice'] = 'visible';

				if ( ! empty( $raw['options']['choices']['other']['moderation'] ) ):
					$question['settings']['allowCustomChoice'] = 'hidden';
				endif;
			endif;
		endif;

		if ( empty( $raw['options']['view_results_link'] ) ):
			$template->addSettings( 'results.visibility', 'voters' );
		endif;

		if ( ! empty( $raw['options']['poll_start_date'] ) ):
			$template->addSettings( 'vote.limitations.period.enabled', true );
			$template->addSettings( 'vote.limitations.period.start', strtotime( $raw['options']['poll_start_date'] ) );
		endif;

		if ( ! empty( $raw['options']['poll_end_date'] ) ):
			$template->addSettings( 'vote.limitations.period.enabled', true );
			$template->addSettings( 'vote.limitations.period.end', strtotime( $raw['options']['poll_end_date'] ) );
		endif;

		if ( ! empty( $raw['options']['view_results_type'] ) ):
			$votesExpression = '';
			if ( $raw['options']['view_results_type'] === 'votes-number' || $raw['options']['view_results_type'] === 'votes-number-and-percentages' ):
				$votesExpression = '{{votesWithLabel}} ';
			endif;
			if ( $raw['options']['view_results_type'] === 'percentages' || $raw['options']['view_results_type'] === 'votes-number-and-percentages' ):
				$votesExpression .= '({{votesPercentage}})';
			endif;

			$template->addSettings( 'results.votes.format', $votesExpression );
		endif;

		if ( ! empty( $raw['options']['vote_permisions'] ) && in_array( 'registered', $raw['options']['vote_permisions'] ) ):
			$roles = [];
			foreach ( get_editable_roles() as $role => $details ):
				$roles[] = $role;
			endforeach;

			$template->addSettings( 'vote.limitations.membership.enabled', true );
			$template->addSettings( 'vote.limitations.membership.roles', $roles );
		endif;

		if ( ! empty( $raw['options']['sorting_results'] ) ):
			$template->addSettings( 'choices.sort.direction', $raw['options']['sorting_results_direction'] );

			if ( $raw['options']['sorting_results'] === 'as_defined' ):
				$template->addSettings( 'choices.sort.field', 'position' );
			elseif ( $raw['options']['sorting_results'] === 'alphabetical' ):
				$template->addSettings( 'choices.sort.field', 'label' );
			elseif ( $raw['options']['sorting_results'] === 'votes' ):
				$template->addSettings( 'choices.sort.field', 'votes' );
			endif;
		endif;

		// Design
		$template->addSettings( 'design.template', 'basic-template' );
		$defaults = TotalPoll( 'modules.repository' )->getDefaults( 'basic-template' ) ?: [];
		$template->addSettings( 'design.custom', $defaults );

		if ( ! empty( $raw['options']['bar_background'] ) ):
			$template->addSettings( 'design.custom.votesbar.colors.start', '#' . $raw['options']['bar_background'] );
			$template->addSettings( 'design.custom.votesbar.colors.end', '#' . $raw['options']['bar_background'] );
		endif;

		$template->addQuestion( $question );


		return $template;
	}

	/**
	 * @param $raw
	 *
	 * @return mixed|void
	 */
	public function transformOptions( $raw ) {
		$template = new Options();

		return $template;
	}

	/**
	 * @param $raw
	 *
	 * @return mixed|void
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
