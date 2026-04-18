<?php

namespace TotalPoll\Admin\Privacy;

use TotalPoll\Contracts\Entry\Repository as EntryRepository;
use TotalPoll\Contracts\Log\Repository as LogRepository;
use TotalPollVendors\TotalCore\Contracts\Foundation\Environment;
use TotalPollVendors\TotalCore\Helpers\Tracking;

/**
 * Class Policy
 * @package TotalPoll\Admin\Privacy
 */
class Policy {
	/**
	 * @var Environment $env
	 */
	protected $env;
	/**
	 * @var LogRepository $logRepository
	 */
	protected $logRepository;
	/**
	 * @var EntryRepository $entryRepository
	 */
	protected $entryRepository;

	/**
	 * Policy constructor.
	 *
	 * @param Environment     $env
	 * @param EntryRepository $entryRepository
	 * @param LogRepository   $logRepository
	 */
	public function __construct( Environment $env, EntryRepository $entryRepository, LogRepository $logRepository ) {
		$this->env             = $env;
		$this->entryRepository = $entryRepository;
		$this->logRepository   = $logRepository;

		add_action( 'admin_init', [ $this, 'suggestion' ] );
		add_filter( 'wp_privacy_personal_data_exporters', [ $this, 'registerExporter' ] );
		add_filter( 'wp_privacy_personal_data_erasers', [ $this, 'registerEraser' ] );
	}

	/**
	 * @param $exporters
	 *
	 * @return mixed
	 */
	public function registerExporter( $exporters ) {
		$exporters[ $this->env['slug'] ] = [
			'exporter_friendly_name' => $this->env['name'],
			'callback'               => [ $this, 'exporter' ],
		];

		return $exporters;
	}

	/**
	 * @param $erasers
	 *
	 * @return mixed
	 */
	public function registerEraser( $erasers ) {
		$erasers[ $this->env['slug'] ] = [
			'eraser_friendly_name' => $this->env['name'],
			'callback'             => [ $this, 'eraser' ],
		];

		return $erasers;
	}

	/**
	 * @param     $email
	 * @param int $page
	 *
	 * @return array
	 */
	public function exporter( $email, $page = 1 ) {
		$perRequest = 200; // Limit us to avoid timing out
		$page       = (int) $page;

		$exported = [];

		$user = get_user_by( 'email', $email );
		if ( $user ):
			$log = $this->logRepository->get( [
				'perPage'    => $perRequest,
				'page'       => $page,
				'conditions' => [
					'user_id' => $user->ID,
				],
			] );

			foreach ( $log as $entry ):
				$exported[] = [
					'group_id'    => 'votes',
					'group_label' => esc_html__( 'Votes', 'totalpoll' ),
					'item_id'     => $entry->getId(),
					'data'        => [
						[
							'name'  => esc_html__( 'IP', 'totalpoll' ),
							'value' => $entry->getIp(),
						],
						[
							'name'  => esc_html__( 'Useragent', 'totalpoll' ),
							'value' => $entry->getUseragent(),
						],
						[
							'name'  => esc_html__( 'Status', 'totalpoll' ),
							'value' => $entry->getStatus(),
						],
						[
							'name'  => esc_html__( 'Date', 'totalpoll' ),
							'value' => $entry->getDate(),
						],
					],
				];
			endforeach;

		endif;

		$entries = $this->entryRepository->get( [
			'perPage'    => $perRequest,
			'page'       => $page,
			'conditions' => [
				'fields' => [
					[
						'operator' => 'LIKE',
						'value'    => "%{$email}%",
					],
				],
			],
		] );

		foreach ( $entries as $entry ):
			$data = [];
			foreach ( $entry->getFields() as $name => $value ):
				$data[] = [
					'name'  => $name,
					'value' => $value,
				];
			endforeach;

			$data[] = [
				'name'  => 'Date',
				'value' => $entry->getDate(),
			];

			$exported[] = [
				'group_id'    => 'entries',
				'group_label' => esc_html__( 'Entries', 'totalpoll' ),
				'item_id'     => $entry->getId(),
				'data'        => $data,
			];
		endforeach;

		return [
			'data' => $exported,
			'done' => ( count( $entries ) + count( $log ) ) < $perRequest,
		];
	}

	/**
	 * @param     $email
	 * @param int $page
	 *
	 * @return array
	 */
	public function eraser( $email, $page = 1 ) {
		$user = get_user_by( 'email', $email );

		if ( $user ):
			$logQuery = [
				'conditions' => [
					'user_id' => $user->ID,
				],
			];

			$itemsRetained = $this->logRepository->anonymize( $logQuery );
		endif;

		$entriesQuery = [
			'conditions' => [
				'fields' => [
					[
						'operator' => 'LIKE',
						'value'    => "%{$email}%",
					],
				],
			],
		];

		if ( $user ):
			$entriesQuery['conditions'][]          = 'OR';
			$entriesQuery['conditions']['user_id'] = $user->ID;
		endif;

		$itemsRetained = $this->entryRepository->anonymize( $entriesQuery );

		$entriesCount = $this->entryRepository->count( $entriesQuery );
		$logCount     = $user ? $this->logRepository->count( $logQuery ) : 0;

		return [
			'items_removed'  => false,
			'items_retained' => $itemsRetained,
			'messages'       => [],
			'done'           => ( $entriesCount + $logCount ) === 0,
		];
	}

	/**
	 * Suggestion.
	 */
	public function suggestion() {
		if ( ! function_exists( 'wp_add_privacy_policy_content' ) ):
			return;
		endif;
		ob_start();
		include __DIR__ . '/views/privacy-policy.php';
		$content = ob_get_clean();

		wp_add_privacy_policy_content(
			$this->env['name'],
			wp_kses_post( wpautop( $content, false ) )
		);
	}
}
