<?php

namespace TotalPoll\Poll\Commands;

use TotalPoll\Contracts\Poll\Model;
use TotalPoll\Notification\Mail;
use TotalPoll\Notification\Push;
use TotalPoll\Notification\WebHook;
use TotalPollVendors\TotalCore\Helpers\Command;

/**
 * Class SendNotifications
 * @package TotalPoll\Poll\Commands
 */
abstract class SendNotifications extends Command {
	/**
	 * @var Model $poll
	 */
	protected $poll;
	/**
	 * @var \TotalPoll\Contracts\Log\Model $log
	 */
	protected $log;
	/**
	 * @var \TotalPoll\Contracts\Entry\Model $entry
	 */
	protected $entry;
	/**
	 * @var array $templateVars
	 */
	protected $templateVars;


	/**
	 * SendNotifications constructor.
	 *
	 * @param Model $poll
	 */
	public function __construct( Model $poll ) {
		$this->poll  = $poll;
		$this->log   = static::getShared( 'log', null );
		$this->entry = static::getShared( 'entry', null );
	}


	/**
	 * @param $recipient
	 *
	 * @return bool|\WP_Error
	 */
	protected function sendEmail( $recipient, $subject, $body ) {
		try {
			$notification = new Mail();
			$notification->setTo( $recipient )
			             ->setSubject( $subject )
			             ->setBody( $body )
			             ->send();

			return true;
		} catch ( \Exception $e ) {
			return new \WP_Error( $e->getCode(), $e->getMessage() );
		}
	}

	protected function sendPush( $recipient, $title, $message, $args = [] ) {
		try {
			$notification = new Push();
			$notification->setTo( $recipient )
			             ->setSubject( $title )
			             ->setBody( $message )
			             ->setArgs( $args )
			             ->send();

			return true;
		} catch ( \Exception $e ) {
			return new \WP_Error( $e->getCode(), $e->getMessage() );
		}
	}

	protected function sendWebhook( $url, $payload, $userAgent = 'TotalPoll Notification' ) {
		try {
			$notification = new WebHook();
			$notification->setTo( $url )
			             ->setFrom( $userAgent )
			             ->setBody( $payload )
			             ->send();

			return true;
		} catch ( \Exception $e ) {
			return new \WP_Error( $e->getCode(), $e->getMessage() );
		}
	}

	/**
	 * @return string
	 */
	abstract public function getTitle();

	/**
	 * @return string
	 */
	abstract public function getBody();

	/**
	 * @return string
	 */
	abstract public function getTemplate();
}
