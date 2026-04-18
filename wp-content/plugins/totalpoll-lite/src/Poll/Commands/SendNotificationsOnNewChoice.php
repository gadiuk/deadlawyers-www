<?php

namespace TotalPoll\Poll\Commands;

use TotalPoll\Contracts\Poll\Model;
use TotalPollVendors\TotalCore\Helpers\Strings;

/**
 * Class SendNotifications
 *
 * @package TotalPoll\Poll\Commands
 */
class SendNotificationsOnNewChoice extends SendNotifications {

	/**
	 * @var array $customChoices
	 */
	protected $customChoices;


	/**
	 * SendNotifications constructor.
	 *
	 * @param  Model  $poll
	 */
	public function __construct( Model $poll ) {
		parent::__construct( $poll );
		$this->customChoices = static::getShared( 'customChoices', null );
		$labels              = [];

		if ( ! empty( $this->customChoices ) ):

			foreach ( $this->poll->getFields() as $field ) {
				$labels[ $field['name'] ] = empty( $field['label'] ) ? $field['name'] : $field['label'];
			}

			$this->templateVars = [
				'title'      => $this->poll->getTitle(),
				'ip'         => $this->log->getIp(),
				'browser'    => $this->log->getUseragent(),
				'user'       => $this->log->getId() ? $this->log->getUser()->user_login : 'anonymous',
				'date'       => $this->log->getDate(),
				'poll'       => $this->poll->toArray(),
				'linkToPoll' => sprintf( '<a href="%s">%s</a>',
				                         esc_attr( $this->poll->getAdminEditLink() ),
				                         $this->poll->getTitle() ),
				'fields'     => '',
				'choices'    => implode( ' & ',
				                         array_map(
					                         function ( $choice ) {
						                         $questionContent = $this->poll->getQuestion( $choice['questionUid'] )['content'];

						                         return sprintf(
							                         '<strong>%s</strong> added to "%s"',
							                         esc_html( $choice['label'] ),
							                         $questionContent
						                         );
					                         },
					                         $this->customChoices
				                         ) ),
				'log'        => $this->log->toArray(),
				'deactivate' => esc_url( admin_url( "post.php?post={$this->poll->getId()}&action=edit&tab=editor>settings>general>notifications" ) ),
			];

			if ( $this->entry ) {
				$this->templateVars['fields'] = implode( PHP_EOL,
				                                         array_map(
					                                         function ( $key ) use ( $labels ) {
						                                         $label = isset( $labels[ $key ] ) ? $labels[ $key ] : ucfirst( $key );

						                                         return $label . ' : ' . implode( ' , ',
						                                                                          (array) $this->entry->getField( $key ) );
					                                         },
					                                         array_keys( $this->entry->getFields() )
				                                         ) );
			}

			$this->templateVars['fieldsHTML'] = nl2br( $this->templateVars['fields'] );
		endif;
	}

	protected function handle() {
		$email = $this->poll->getSettingsItem( 'notifications.email', [] );

		/**
		 * Fires before sending notifications.
		 *
		 * @param  Model  $poll  WebHook settings.
		 * @param  array  $settings  Notifications settings.
		 * @param  array  $log  Log entry.
		 *
		 * @since 4.0.0
		 */
		do_action( 'totalpoll/actions/before/poll/command/notify', $this->poll, [
			'email' => $email,
		],         $this->log->toArray() );


		if ( ! empty( $this->customChoices ) && ! empty( $email['on']['newChoice'] ) && ! empty( $email['recipient'] ) ):
			$this->sendEmail( $email['recipient'], $this->getTitle(), $this->getTemplate() );
		endif;


		/**
		 * Fires after sending notifications.
		 *
		 * @param  \TotalPoll\Contracts\Poll\Model  $poll  Poll model object.
		 *
		 * @since 4.0.0
		 */
		do_action( 'totalpoll/actions/after/poll/command/notify', $this->poll );

		return true;
	}


	/**
	 * @return string
	 */
	public function getTitle() {
		$template = TotalPoll()->option( 'notifications.title' ) ?: 'New choice(s) added to your poll "{{poll.title}}"';

		return Strings::template( $template, $this->templateVars );
	}

	/**
	 * @return string
	 */
	public function getBody() {
		$template = TotalPoll()->option( 'notifications.body' ) ?: 'Someone just added a new choice to {{poll.title}}';

		return Strings::template( $template, $this->templateVars );
	}

	/**
	 * @return string
	 */
	public function getTemplate() {
		$template = TotalPoll()->option( 'notifications.template' ) ?: file_get_contents( __DIR__ . '/views/notifications/new-choice.php' );

		return Strings::template( $template, $this->templateVars );
	}
}
