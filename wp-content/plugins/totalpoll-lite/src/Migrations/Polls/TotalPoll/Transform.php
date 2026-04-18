<?php

namespace TotalPoll\Migrations\Polls\TotalPoll;

use TotalPoll\Contracts\Migrations\Poll\Transform as TransformContract;
use TotalPoll\Migrations\Polls\Templates\LogEntry;
use TotalPoll\Migrations\Polls\Templates\Options;
use TotalPoll\Migrations\Polls\Templates\Poll;
use TotalPoll\Migrations\Polls\Templates\Submission;
use TotalPollVendors\TotalCore\Helpers\Arrays;
use TotalPollVendors\TotalCore\Helpers\Misc;

/**
 * Transform Poll.
 * @package TotalPoll\Migrations\Polls\TotalPoll
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
		$template->setTitle( $raw['title'] );

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

		if ( ! empty( $raw['options']['limitations']['selection']['minimum'] ) ):
			$question['settings']['selection']['minimum'] = $raw['options']['limitations']['selection']['minimum'];
		endif;

		if ( ! empty( $raw['options']['limitations']['selection']['maximum'] ) ):
			$question['settings']['selection']['maximum'] = $raw['options']['limitations']['selection']['maximum'];
		endif;

		$mediaTypes = [ 'image', 'video', 'audio' ];
		foreach ( $raw['choices'] as $choice ):
			if ( in_array( $choice['content']['type'], $mediaTypes ) ):
				$choice['content'][ $choice['content']['type'] ]['full']      = $choice['content'][ $choice['content']['type'] ]['url'];
				$choice['content'][ $choice['content']['type'] ]['thumbnail'] = $choice['content']['thumbnail']['url'];
				if ( ! empty( $choice['content']['thumbnail']['id'] ) ):
					//@TODO: Check this
					$attachment                                               = wp_prepare_attachment_for_js( $choice['content']['thumbnail']['id'] );
					$choice['content'][ $choice['content']['type'] ]['sizes'] = empty( $attachment['sizes'] ) ? [] : $attachment['sizes'];
				endif;
				$choice['content'][ $choice['content']['type'] ]['html'] = TotalPoll( 'embed' )->getProviderHtml( $choice['content'][ $choice['content']['type'] ]['full'] );
				unset( $choice['content'][ $choice['content']['type'] ]['url'], $choice['content']['thumbnail'] );
			endif;

			$question['choices'][] = [
				'uid'                      => Misc::generateUid(),
				'votes'                    => (int) $choice['votes'],
				'votesOverride'            => (int) $choice['votes'],
				'visibility'               => ! empty( $choice['content']['visible'] ),
				'type'                     => $choice['content']['type'],
				'label'                    => $choice['content']['label'],
				$choice['content']['type'] => $choice['content'][ $choice['content']['type'] ],
			];
		endforeach;

		// Fields
		if ( ! empty( $raw['options']['fields'] ) ):
			foreach ( $raw['options']['fields'] as $field ):

				$filterRules = [];
				if ( ! empty( $field['validations']['filter']['list'] ) ):
					$rules = explode( "\r\n", trim( $field['validations']['filter']['list'] ) );
					foreach ( $rules as $index => $rule ):
						$rule          = ltrim( $rule );
						$filterRules[] = [
							'type'  => isset( $rule[0] ) && $rule[0] === '-' ? 'deny' : 'allow',
							'value' => isset( $rule[0] ) && ( $rule[0] === '-' || $rule[0] === '+' ) ? substr( $rule, 2 ) : $rule,
						];
					endforeach;
				endif;

				$template->addField( [
					'uid'          => Misc::generateUid(),
					'type'         => $field['type'],
					'name'         => sanitize_title_with_dashes( $field['name'] ),
					'label'        => $field['label']['content'],
					'defaultValue' => $field['default'],
					'options'      => ! empty( $field['extra']['options'] ) ? $field['extra']['options'] : '',
					'attributes'   => [
						'multiple' => ! empty( $field['extra']['multiple'] ),
						'class'    => ! empty( $field['class'] ) ? $field['class'] : '',
					],
					'template'     => str_replace(
						[ '%label%', '%field%' ],
						[ '{{label}}', '{{field}}<div class="totalpoll-form-field-errors">{{errors}}</div>' ],
						$field['template']
					),
					'validations'  => [
						'filled' => [ 'enabled' => ! empty( $field['validations']['filled']['enabled'] ) ],
						'email'  => [ 'enabled' => ! empty( $field['validations']['email']['enabled'] ) ],
						'unique' => [ 'enabled' => ! empty( $field['validations']['unique']['enabled'] ) ],
						'filter' => [
							'enabled' => ! empty( $field['validations']['filter']['enabled'] ),
							'rules'   => $filterRules,
						],
						'regex'  => [
							'enabled'      => ! empty( $field['validations']['regex']['enabled'] ),
							'type'         => ! empty( $field['validations']['regex']['type'] ) ? $field['validations']['regex']['type'] : 'match',
							'pattern'      => ! empty( $field['validations']['regex']['against'] ) ? $field['validations']['regex']['against'] : '',
							'errorMessage' => ! empty( $field['validations']['regex']['message'] ) ? str_replace( '%label%', '{{label}}', $field['validations']['regex']['message'] ) : '',
						],
					],
				] );
			endforeach;
		endif;

		// Settings
		if ( ! empty( $raw['options']['limitations']['cookies']['enabled'] ) ):
			$template->addSettings( 'vote.frequency.cookies.enabled', true );
			$template->addSettings( 'vote.frequency.timeout', absint( $raw['options']['limitations']['cookies']['timeout'] ) * 60 );
		endif;

		if ( ! empty( $raw['options']['limitations']['ip']['enabled'] ) ):
			$template->addSettings( 'vote.frequency.ip.enabled', true );
			$template->addSettings( 'vote.frequency.timeout', absint( $raw['options']['limitations']['ip']['timeout'] ) * 60 );
			$template->addSettings( 'vote.frequency.perIP', absint( $raw['options']['limitations']['ip']['votes_quota_per_ip'] ) );
		endif;

		if ( ! empty( $raw['options']['limitations']['ip']['filter'] ) ):
			$template->addSettings( 'vote.limitations.region.enabled', true );
			$filterRules = explode( "\r\n", trim( $raw['options']['limitations']['ip']['filter'] ) );
			foreach ( $filterRules as $index => $rule ):
				$rule                  = str_replace( ' ', '', $rule );
				$filterRules[ $index ] = [
					'type' => isset( $rule[0] ) && $rule[0] === '-' ? 'deny' : 'allow',
					'ip'   => isset( $rule[0] ) && ( $rule[0] === '-' || $rule[0] === '+' ) ? substr( $rule, 1 ) : $rule,
				];
			endforeach;
			$template->addSettings( 'vote.limitations.region.rules', $filterRules );
		endif;

		if ( ! empty( $raw['options']['limitations']['membership']['enabled'] ) ):
			$template->addSettings( 'vote.limitations.membership.enabled', true );
			$template->addSettings( 'vote.limitations.membership.roles', Arrays::getDotNotation( $raw, 'options.limitations.membership.type', [] ) );
		endif;

		if ( ! empty( $raw['options']['limitations']['membership']['once']['enabled'] ) ):
			$template->addSettings( 'vote.frequency.user.enabled', true );
			$template->addSettings( 'vote.frequency.perUser', 1 );
		endif;

		if ( ! empty( $raw['options']['limitations']['quota']['enabled'] ) ):
			$template->addSettings( 'vote.limitations.quota.enabled', true );
			$template->addSettings( 'vote.limitations.quota.value', Arrays::getDotNotation( $raw, 'options.limitations.quota.votes', 100 ) );
		endif;

		if ( ! empty( $raw['options']['limitations']['date']['enabled'] ) ):
			$template->addSettings( 'vote.limitations.period.enabled', true );
			// @TODO Convert this to compatible format
			$template->addSettings( 'vote.limitations.period.start', Arrays::getDotNotation( $raw, 'options.limitations.date.start' ) );
			$template->addSettings( 'vote.limitations.period.end', Arrays::getDotNotation( $raw, 'options.limitations.date.end' ) );
		endif;

		if ( ! empty( $raw['options']['limitations']['results']['require_vote']['enabled'] ) ):
			$template->addSettings( 'results.visibility', 'voters' );
		endif;

		if ( ! empty( $raw['options']['results']['order']['enabled'] ) ):
			$template->addSettings( 'results.sort.field', str_replace( [ 'date' ], [ 'position' ], Arrays::getDotNotation( $raw, 'options.limitations.results.order.by', 'votes' ) ) );
			$template->addSettings( 'results.sort.direction', Arrays::getDotNotation( $raw, 'options.limitations.results.order.direction', 'desc' ) );
		endif;

		if ( ! empty( $raw['options']['results']['hide']['enabled'] ) ):
			$template->addSettings( 'results.visibility', 'hide' );
			$template->addSettings( 'results.message', Arrays::getDotNotation( $raw, 'options.limitations.results.hide.content' ) );
			$template->addSettings( 'results.untilReaching.quota', Arrays::getDotNotation( $raw, 'options.limitations.results.hide.until.quota' ) );
			$template->addSettings( 'results.untilReaching.endDate', Arrays::getDotNotation( $raw, 'options.limitations.results.hide.until.end_date' ) );
		endif;

		$votesExpression = '';
		if ( ! empty( $raw['options']['results']['format']['votes'] ) ):
			$votesExpression = '{{votesWithLabel}} ';
		endif;
		if ( ! empty( $raw['options']['results']['format']['percentages'] ) ):
			$votesExpression .= '({{votesPercentage}})';
		endif;
		$template->addSettings( 'results.votes.format', $votesExpression );

		if ( ! empty( $raw['options']['choices']['pagination']['per_page'] ) ):
			$template->addSettings( 'design.pagination.perPage', absint( $raw['options']['choices']['pagination']['per_page'] ) );
		endif;

		if ( ! empty( $raw['options']['choices']['other']['enabled'] ) ):
			$question['settings']['allowCustomChoice'] = 'visible';

			if ( ! empty( $raw['options']['choices']['other']['moderation'] ) ):
				$question['settings']['allowCustomChoice'] = 'hidden';
			endif;
		endif;

		if ( ! empty( $raw['options']['choices']['order']['enabled'] ) ):
			$template->addSettings( 'choices.sort.field', str_replace( [ 'date' ], [ 'position' ], Arrays::getDotNotation( $raw, 'options.limitations.choices.order.by', 'votes' ) ) );
			$template->addSettings( 'choices.sort.direction', Arrays::getDotNotation( $raw, 'options.limitations.choices.order.direction', 'desc' ) );
		endif;

		if ( ! empty( $raw['options']['screens']['before_vote']['enabled'] ) ):
			$template->addSettings( 'content.welcome.content', Arrays::getDotNotation( $raw, 'options.screens.before_vote.content' ) );
		endif;

		if ( ! empty( $raw['options']['screens']['after_vote']['enabled'] ) ):
			$template->addSettings( 'content.thankyou.content', Arrays::getDotNotation( $raw, 'options.screens.after_vote.content' ) );
		endif;

		if ( ! empty( $raw['options']['screens']['above_vote']['enabled'] ) ):
			$template->addSettings( 'content.vote.above', Arrays::getDotNotation( $raw, 'options.screens.above_vote.content' ) );
		endif;

		if ( ! empty( $raw['options']['screens']['below_vote']['enabled'] ) ):
			$template->addSettings( 'content.vote.below', Arrays::getDotNotation( $raw, 'options.screens.below_vote.content' ) );
		endif;

		if ( ! empty( $raw['options']['screens']['above_results']['enabled'] ) ):
			$template->addSettings( 'content.results.above', Arrays::getDotNotation( $raw, 'options.screens.above_results.content' ) );
		endif;

		if ( ! empty( $raw['options']['screens']['below_results']['enabled'] ) ):
			$template->addSettings( 'content.results.below', Arrays::getDotNotation( $raw, 'options.screens.below_results.content' ) );
		endif;

		$template->addSettings( 'notifications.email.recipient', Arrays::getDotNotation( $raw, 'options.notifications.email' ) );

		if ( ! empty( $raw['options']['notifications']['triggers']['new_vote']['enabled'] ) ):
			$template->addSettings( 'notifications.email.on.newVote', true );
		endif;

		if ( ! empty( $raw['options']['design']['template']['name'] ) ):
			$designTemplateName     = $raw['options']['design']['template']['name'];
			$designTemplatesMapping = [
				'default'       => 'basic-template',
				'chartix'       => 'charts-template',
				'chartsome'     => 'charts-template',
				'media-contest' => 'media-contest-template',
				'facebook-like' => 'facebook-like-template',
				'twitter-like'  => 'twitter-lite-template',
				'debate'        => 'debate-template',
				'versus'        => 'versus-template',
				'opinion'       => 'opinion-template',
				'rainbow'       => 'basic-template',
			];
			$designTemplateName     = ! isset( $designTemplatesMapping[ $designTemplateName ] ) ? 'basic-template' : $designTemplatesMapping[ $designTemplateName ];

			$template->addSettings( 'design.template', $designTemplateName );
			$defaults = TotalPoll( 'modules.repository' )->getDefaults( $designTemplateName ) ?: [];
			$template->addSettings( 'design.custom', $defaults );
		endif;

		if ( ! empty( $raw['options']['design']['preset']['general']['tabs']['other']['fields']['per-row']['value'] ) ):
			$template->addSettings( 'design.layout.choicesPerRow', absint( Arrays::getDotNotation( $raw, 'options.design.preset.general.tabs.other.fields.per-row.value' ) ) );
		endif;

		if ( ! empty( $raw['options']['design']['preset']['general']['tabs']['other']['fields']['animation']['value'] ) ):
			$template->addSettings( 'design.effects.duration', absint( Arrays::getDotNotation( $raw, 'options.design.preset.general.tabs.other.fields.animation.value' ) ) );
		endif;

		if ( ! empty( $raw['options']['design']['transition']['type'] ) ):
			$template->addSettings( 'design.effects.transition', Arrays::getDotNotation( $raw, 'options.design.transition.type' ) );
		endif;

		if ( ! empty( $raw['options']['design']['one_click']['enabled'] ) ):
			$template->addSettings( 'design.behaviours.oneClick', true );
		endif;

		if ( ! empty( $raw['options']['design']['scroll']['enabled'] ) ):
			$template->addSettings( 'design.behaviours.scrollUp', true );
		endif;

		if ( ! empty( $raw['options']['design']['preset']['general']['tabs']['container']['fields']['background']['value'] ) ):
			$template->addSettings( 'design.custom.container.colors.background', Arrays::getDotNotation( $raw, 'options.design.preset.general.tabs.container.fields.background.value' ) );
		endif;

		if ( ! empty( $raw['options']['design']['preset']['general']['tabs']['container']['fields']['border']['value'] ) ):
			$template->addSettings( 'design.custom.container.colors.border', Arrays::getDotNotation( $raw, 'options.design.preset.general.tabs.container.fields.border.value' ) );
		endif;

		if ( ! empty( $raw['options']['design']['preset']['general']['tabs']['messages']['fields']['background']['value'] ) ):
			$template->addSettings( 'design.custom.message.colors.backgroundError', Arrays::getDotNotation( $raw, 'options.design.preset.general.tabs.messages.fields.background.value' ) );
		endif;

		if ( ! empty( $raw['options']['design']['preset']['general']['tabs']['messages']['fields']['border']['value'] ) ):
			$template->addSettings( 'design.custom.message.colors.borderError', Arrays::getDotNotation( $raw, 'options.design.preset.general.tabs.messages.fields.border.value' ) );
		endif;

		if ( ! empty( $raw['options']['design']['preset']['typography']['tabs']['general']['fields']['font-family']['value'] ) ):
			$template->addSettings( 'design.text.fontFamily', Arrays::getDotNotation( $raw, 'options.design.preset.typography.tabs.general.fields.font-family.value' ) );
		endif;

		if ( ! empty( $raw['options']['design']['preset']['typography']['tabs']['general']['fields']['font-size']['value'] ) ):
			$template->addSettings( 'design.text.fontSize', Arrays::getDotNotation( $raw, 'options.design.preset.typography.tabs.general.fields.font-size.value' ) );
		endif;

		if ( ! empty( $raw['options']['design']['preset']['buttons']['tabs']['default']['fields']['background:normal']['value'] ) ):
			$template->addSettings( 'design.custom.button.colors.background', Arrays::getDotNotation( $raw, 'options.design.preset.buttons.tabs.default.fields.background:normal.value' ) );
		endif;

		if ( ! empty( $raw['options']['design']['preset']['buttons']['tabs']['default']['fields']['background:hover']['value'] ) ):
			$template->addSettings( 'design.custom.button.colors.backgroundHover', Arrays::getDotNotation( $raw, 'options.design.preset.buttons.tabs.default.fields.background:hover.value' ) );
		endif;

		if ( ! empty( $raw['options']['design']['preset']['buttons']['tabs']['default']['fields']['border:normal']['value'] ) ):
			$template->addSettings( 'design.custom.button.colors.border', Arrays::getDotNotation( $raw, 'options.design.preset.buttons.tabs.default.fields.border:normal.value' ) );
		endif;

		if ( ! empty( $raw['options']['design']['preset']['buttons']['tabs']['default']['fields']['border:hover']['value'] ) ):
			$template->addSettings( 'design.custom.button.colors.borderHover', Arrays::getDotNotation( $raw, 'options.design.preset.buttons.tabs.default.fields.border:hover.value' ) );
		endif;

		if ( ! empty( $raw['options']['design']['preset']['buttons']['tabs']['default']['fields']['color:normal']['value'] ) ):
			$template->addSettings( 'design.custom.button.colors.color', Arrays::getDotNotation( $raw, 'options.design.preset.buttons.tabs.default.fields.color:normal.value' ) );
		endif;

		if ( ! empty( $raw['options']['design']['preset']['buttons']['tabs']['default']['fields']['color:hover']['value'] ) ):
			$template->addSettings( 'design.custom.button.colors.colorHover', Arrays::getDotNotation( $raw, 'options.design.preset.buttons.tabs.default.fields.color:hover.value' ) );
		endif;

		if ( ! empty( $raw['options']['design']['preset']['buttons']['tabs']['primary']['fields']['background:normal']['value'] ) ):
			$template->addSettings( 'design.custom.button.colors.backgroundPrimary', Arrays::getDotNotation( $raw, 'options.design.preset.buttons.tabs.primary.fields.background:normal.value' ) );
		endif;

		if ( ! empty( $raw['options']['design']['preset']['buttons']['tabs']['primary']['fields']['background:hover']['value'] ) ):
			$template->addSettings( 'design.custom.button.colors.backgroundPrimaryHover', Arrays::getDotNotation( $raw, 'options.design.preset.buttons.tabs.primary.fields.background:hover.value' ) );
		endif;

		if ( ! empty( $raw['options']['design']['preset']['buttons']['tabs']['primary']['fields']['border:normal']['value'] ) ):
			$template->addSettings( 'design.custom.button.colors.borderPrimary', Arrays::getDotNotation( $raw, 'options.design.preset.buttons.tabs.primary.fields.border:normal.value' ) );
		endif;

		if ( ! empty( $raw['options']['design']['preset']['buttons']['tabs']['primary']['fields']['border:hover']['value'] ) ):
			$template->addSettings( 'design.custom.button.colors.borderPrimaryHover', Arrays::getDotNotation( $raw, 'options.design.preset.buttons.tabs.primary.fields.border:hover.value' ) );
		endif;

		if ( ! empty( $raw['options']['design']['preset']['buttons']['tabs']['primary']['fields']['color:normal']['value'] ) ):
			$template->addSettings( 'design.custom.button.colors.colorPrimary', Arrays::getDotNotation( $raw, 'options.design.preset.buttons.tabs.primary.fields.color:normal.value' ) );
		endif;

		if ( ! empty( $raw['options']['design']['preset']['buttons']['tabs']['primary']['fields']['color:hover']['value'] ) ):
			$template->addSettings( 'design.custom.button.colors.colorPrimaryHover', Arrays::getDotNotation( $raw, 'options.design.preset.buttons.tabs.primary.fields.color:hover.value' ) );
		endif;

		if ( ! empty( $raw['options']['design']['preset']['votes-bar']['tabs']['bar']['fields']['color:start']['value'] ) ):
			$template->addSettings( 'design.custom.votesbar.colors.backgroundStart', Arrays::getDotNotation( $raw, 'options.design.preset.votes-bar.tabs.bar.fields.color:start.value' ) );
		endif;

		if ( ! empty( $raw['options']['design']['preset']['votes-bar']['tabs']['bar']['fields']['color:end']['value'] ) ):
			$template->addSettings( 'design.custom.votesbar.colors.backgroundEnd', Arrays::getDotNotation( $raw, 'options.design.preset.votes-bar.tabs.bar.fields.color:end.value' ) );
		endif;

		if ( ! empty( $raw['options']['design']['preset']['votes-bar']['tabs']['bar']['fields']['color']['value'] ) ):
			$template->addSettings( 'design.custom.votesbar.colors.color', Arrays::getDotNotation( $raw, 'options.design.preset.votes-bar.tabs.bar.fields.color.value' ) );
		endif;

		$template->addQuestion( $question );

		$template->setNewId($raw['id']);

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

		// Options
		if ( ! empty( $raw['recaptcha']['key'] ) ):
			$template->addOption( 'services.recaptcha.key', $raw['recaptcha']['key'] );
		endif;

		if ( ! empty( $raw['recaptcha']['secret'] ) ):
			$template->addOption( 'services.recaptcha.secret', $raw['recaptcha']['secret'] );
		endif;

		if ( ! empty( $raw['recaptcha']['key'] ) && ! empty( $raw['recaptcha']['secret'] ) ):
			$template->addOption( 'services.recaptcha.enabled', true );
		endif;

		if ( ! empty( $raw['general']['async']['enabled'] ) ):
			$template->addOption( 'performance.async.enabled', true );
		endif;

		if ( ! empty( $raw['advanced']['css_cache_alt']['enabled'] ) ):
			$template->addOption( 'advanced.inlineCss', true );
		endif;

		if ( ! empty( $raw['expressions'] ) ):
			$template->addOption( 'expressions', $raw['expressions'] );
		endif;

		if ( ! empty( $raw['sharing']['networks'] ) ):
			if ( in_array( 'facebook', $raw['sharing']['networks'] ) ):
				$template->addOption( 'share.websites.facebook', true );
			endif;
			if ( in_array( 'twitter', $raw['sharing']['networks'] ) ):
				$template->addOption( 'share.websites.twitter', true );
			endif;
			if ( in_array( 'googlePlus', $raw['sharing']['networks'] ) ):
				$template->addOption( 'share.websites.googlePlus', true );
			endif;
			if ( in_array( 'reddit', $raw['sharing']['networks'] ) ):
				$template->addOption( 'share.websites.reddit', true );
			endif;
			if ( in_array( 'linkedin', $raw['sharing']['networks'] ) ):
				$template->addOption( 'share.websites.linkedin', true );
			endif;
			if ( in_array( 'email', $raw['sharing']['networks'] ) ):
				$template->addOption( 'share.websites.email', true );
			endif;
			if ( in_array( 'whatsapp', $raw['sharing']['networks'] ) ):
				$template->addOption( 'share.websites.whatsapp', true );
			endif;
		endif;

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
