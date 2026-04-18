<?php

namespace TotalPoll\Poll;

use TotalPoll\Contracts\Poll\Model as ModelContract;
use TotalPollVendors\TotalCore\Contracts\Form\Form;
use TotalPollVendors\TotalCore\Contracts\Helpers\Renderable;
use TotalPollVendors\TotalCore\Helpers\Arrays;
use TotalPollVendors\TotalCore\Helpers\Misc;
use TotalPollVendors\TotalCore\Helpers\Strings;
use TotalPollVendors\TotalCore\Traits\Cookies;
use TotalPollVendors\TotalCore\Traits\Metadata;
use TotalSuite\Foundation\Database\Query\Expression;

/**
 * Poll Model
 *
 * @package TotalPoll\Poll
 * @since   1.0.0
 */
class Model implements Renderable, ModelContract {
	use Cookies;
	use Metadata;

	const WELCOME_SCREEN  = 'welcome';
	const VOTE_SCREEN     = 'vote';
	const RESULTS_SCREEN  = 'results';
	const THANKYOU_SCREEN = 'thankyou';

	const WELCOME_ACTION  = 'welcome';
	const VIEW_ACTION     = 'view';
	const VOTE_ACTION     = 'vote';
	const RESULTS_ACTION  = 'results';
	const THANKYOU_ACTION = 'thankyou';

	const SORT_BY_POSITION = 'position';
	const SORT_BY_LABEL    = 'label';
	const SORT_BY_VOTES    = 'votes';
	const SORT_BY_RAND     = 'random';

	const SORT_ASC  = 'asc';
	const SORT_DESC = 'desc';

	/**
	 * Poll ID.
	 *
	 * @var int|null
	 * @since 1.0.0
	 */
	protected $id = null;

	/**
	 * Poll UID.
	 *
	 * @var int|null
	 * @since 4.1.3
	 */
	protected $uid = null;

	/**
	 * Poll attributes.
	 *
	 * @var array
	 * @since 1.0.0
	 */
	protected $attributes = [];

	/**
	 * Poll settings.
	 *
	 * @var array|null
	 * @since 1.0.0
	 */
	protected $settings = null;

	/**
	 * Poll seo attributes.
	 *
	 * @var array|null
	 * @since 1.0.0
	 */
	protected $seo = null;

	/**
	 * Poll WordPress post.
	 *
	 * @var array|null|\WP_Post
	 * @since 1.0.0
	 */
	protected $pollPost = null;

	/**
	 * Poll questions.
	 *
	 * @var array
	 * @since 1.0.0
	 */
	protected $questions = [];

	/**
	 * Poll choices map (choice uid => question uid).
	 *
	 * @var array
	 * @since 1.0.0
	 */
	protected $choicesMap = [];

	/**
	 * Poll total votes.
	 *
	 * @var int
	 * @since 1.0.0
	 */
	protected $totalVotes = 0;

	/**
	 * Poll choices count.
	 *
	 * @var int
	 * @since 1.0.0
	 */
	protected $choicesCount = null;

	/**
	 * Choice per page.
	 *
	 * @var int
	 * @since 1.0.0
	 */
	protected $choicesPerPage = 10;

	/**
	 * Poll total pages.
	 *
	 * @var int|null
	 * @since 1.0.0
	 */
	protected $pagesCount = 0;

	/**
	 * Poll current page.
	 *
	 * @var int|null
	 * @since 1.0.0
	 */
	protected $currentPage = 1;

	/**
	 * Poll upload form.
	 *
	 * @var \TotalPollVendors\TotalCore\Contracts\Form\Form $form
	 * @since 1.0.0
	 */
	protected $form = null;

	/**
	 * Poll current screen
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $screen = self::WELCOME_SCREEN;

	/**
	 * Poll current action
	 *
	 * @var string
	 * @since 4.0.0
	 */
	protected $action = self::VIEW_ACTION;

	/**
	 * Sort choices by field.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $sortChoicesBy = self::SORT_BY_POSITION;

	/**
	 * Sort choices direction.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $sortChoicesDirection = self::SORT_DESC;

	/**
	 * Sort results by field.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $sortResultsBy = self::SORT_BY_POSITION;

	/**
	 * Sort results direction.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $sortResultsDirection = self::SORT_DESC;

	/**
	 * Limitations
	 *
	 * @var \TotalPollVendors\TotalCore\Contracts\Limitations\Bag
	 * @since 1.0.0
	 */
	protected $limitations;

	/**
	 * Restrictions
	 *
	 * @var \TotalPollVendors\TotalCore\Contracts\Restrictions\Bag
	 * @since 1.0.0
	 */
	protected $restrictions;

	/**
	 * Error.
	 *
	 * @var null|\WP_Error
	 * @since 1.0.0
	 */
	protected $error;

	/**
	 * @var mixed
	 */
	protected static $originalExpressions;

	/**
	 * Model constructor.
	 *
	 * @param $attributes array Poll attributes.
	 *
	 * @since 1.0.0
	 */
	public function __construct( $attributes ) {
		/**
		 * Filters the poll attributes.
		 *
		 * @param  array  $attributes  Poll model attributes.
		 * @param  ModelContract  $poll  Poll model object.
		 *
		 * @return array
		 * @since 4.0.0
		 */
		$attributes       = apply_filters( 'totalpoll/filters/poll/attributes', $attributes, $this );
		$this->attributes = $attributes;
		$this->id         = $attributes['id'];
		$this->action     = (string) $attributes['action'] ?: $this->action;
		$this->pollPost   = $attributes['post'];
		// Parse settings JSON.
		$this->settings = (array) json_decode( $this->pollPost->post_content, true );

		/**
		 * Filters the poll attributes.
		 *
		 * @param  array  $settings  Poll settings.
		 * @param  ModelContract  $poll  Poll model object.
		 *
		 * @return array
		 * @since 4.0.0
		 */
		$this->settings = apply_filters( 'totalpoll/filters/poll/settings', $this->settings, $this );

		// UID
		$this->uid = $this->getSettingsItem( 'uid' );

		// Locale
		$locale = get_locale();

		// Questions
		$questions                  = $this->getSettingsItem( 'questions', [] );
		$receivedChoicesCookieName  = $this->generateCookieName( 'received_' . $this->getId() . $this->getUid() );
		$receivedChoicesCookieValue = (string) $this->getCookie( $receivedChoicesCookieName );
		$receivedChoices            = explode( '__', $receivedChoicesCookieValue );

		foreach ( $questions as $questionIndex => $question ) :
			// Translation
			if ( ! empty( $question['translations'][ $locale ]['content'] ) ) :
				$question['content'] = $question['translations'][ $locale ]['content'];
			endif;

			// Move to questions array
			$this->questions[ $question['uid'] ]                  = $question;
			$this->questions[ $question['uid'] ]['index']         = $questionIndex;
			$this->questions[ $question['uid'] ]['votes']         = 0;
			$this->questions[ $question['uid'] ]['receivedVotes'] = 0;

			// Choices
			$choices                                        = (array) $this->questions[ $question['uid'] ]['choices'];
			$this->questions[ $question['uid'] ]['choices'] = [];

			foreach ( $choices as $choiceIndex => $choice ) :
				// Translation
				if ( ! empty( $choice['translations'][ $locale ]['label'] ) ) :
					$choice['label'] = $choice['translations'][ $locale ]['label'];
				endif;

				// Add votes property
				$choice['votes'] = empty( $attributes['votes'][ $choice['uid'] ] ) ? 0 : $attributes['votes'][ $choice['uid'] ];

				// Add to choices map
				$this->choicesMap[ $choice['uid'] ] = $question['uid'];
				$choice['index']                    = $choiceIndex;
				$choice['questionUid']              = $question['uid'];
				$choice['receivedVotes']            = 0;

				if ( in_array( $choice['uid'], $receivedChoices ) ) {
					$choice['receivedVotes'] = 1;
				}

				// Alter visibility for users with cookie set to choice UID (Check CountVote command for more details)
				if ( $this->getCookie( $this->getPrefix( $choice['uid'] ) ) ) :
					$choice['visibility'] = true;
				endif;

				// Cumulative of votes for current question
				$this->questions[ $question['uid'] ]['votes']                     += $choice['votes'];
				$this->totalVotes                                                 += $choice['votes'];
				$this->questions[ $question['uid'] ]['choices'][ $choice['uid'] ] = $choice;
			endforeach;

			// Calculate ranking of choices
			$questionChoices = $this->questions[ $question['uid'] ]['choices'];
			uasort( $questionChoices, [ $this, 'orderByVotes' ] );
			$questionChoices = array_reverse( $questionChoices, true );
			$rankedChoices   = array_keys( $questionChoices );
			foreach ( $rankedChoices as $index => $choiceUid ) :
				$this->questions[ $question['uid'] ]['choices'][ $choiceUid ]['rank'] = $index + 1;
			endforeach;
		endforeach;

		// Fields
		$fields = $this->getSettingsItem( 'fields', [] );
		foreach ( $fields as &$field ) :
			// Translation
			if ( ! empty( $field['translations'][ $locale ]['label'] ) ) :
				$field['label'] = $field['translations'][ $locale ]['label'];
			endif;
			// Translation
			if ( ! empty( $field['translations'][ $locale ]['options'] ) ) :
				$field['options'] = $field['translations'][ $locale ]['options'];
			endif;

		endforeach;
		$this->setSettingsItem( 'fields', $fields );

		// Current page
		$this->currentPage = empty( $attributes['currentPage'] ) ? $this->currentPage : (int) $attributes['currentPage'];

		// Choice per page
		$this->choicesPerPage = (int) $this->getSettingsItem( 'design.pagination.perPage' ) ?: 10;

		// Sort choices
		$this->sortChoicesBy        = (string) $this->getSettingsItem( 'choices.sort.field', 'position' );
		$this->sortChoicesDirection = (string) $this->getSettingsItem( 'choices.sort.direction', 'desc' );

		// Sort results
		$this->sortResultsBy        = (string) $this->getSettingsItem( 'results.sort.field', 'position' );
		$this->sortResultsDirection = (string) $this->getSettingsItem( 'results.sort.direction', self::SORT_DESC );

		/**
		 * Filters the poll questions.
		 *
		 * @param  array  $questions  Questions array.
		 * @param  Model  $model  Poll model.
		 *
		 * @return array
		 * @since 4.0.1
		 */
		$this->questions = apply_filters( 'totalpoll/filters/poll/questions', $this->questions, $this );

		/**
		 * Filters the poll total votes.
		 *
		 * @param  int  $votes  Total votes.
		 * @param  Model  $model  Poll model.
		 *
		 * @return array
		 * @since 4.0.1
		 */
		$this->totalVotes = apply_filters( 'totalpoll/filters/poll/total-votes', $this->totalVotes, $this );

		// Limitations
		$this->limitations = new \TotalPollVendors\TotalCore\Limitations\Bag();

		// Period
		$periodArgs = $this->getSettingsItem( 'vote.limitations.period', [] );

		if ( ! empty( $periodArgs['enabled'] ) ) :
			$this->limitations->add( 'period', new \TotalPoll\Limitations\Period( $periodArgs ) );
		endif;

		// Membership
		$membershipArgs = $this->getSettingsItem( 'vote.limitations.membership', [] );
		if ( ! empty( $membershipArgs['enabled'] ) ) :
			$this->limitations->add( 'membership', new \TotalPoll\Limitations\Membership( $membershipArgs ) );
		endif;

		// Quota
		$quotaArgs = $this->getSettingsItem( 'vote.limitations.quota', [] );
		if ( ! empty( $quotaArgs['enabled'] ) ) :
			$quotaArgs['currentValue'] = $this->totalVotes;
			$this->limitations->add( 'quota', new \TotalPoll\Limitations\Quota( $quotaArgs ) );
		endif;

		// Region
		$regionArgs = $this->getSettingsItem( 'vote.limitations.region', [] );
		if ( ! empty( $regionArgs['enabled'] ) ) :
			$regionArgs['ip'] = $attributes['ip'];
			$this->limitations->add( 'region', new \TotalPoll\Limitations\Region( $regionArgs ) );
		endif;
		/**
		 * Fires after limitations setup.
		 *
		 * @param  ModelContract  $poll  Poll model object.
		 *
		 * @since 4.0.0
		 */
		do_action( 'totalpoll/actions/poll/limitations', $this );

		// Restrictions
		$this->restrictions = new \TotalPollVendors\TotalCore\Restrictions\Bag();

		$frequencyArgs              = $this->getSettingsItem( 'vote.frequency', [ 'timeout' => 3600 ] );
		$frequencyArgs['uid']       = $this->getUid();
		$frequencyArgs['poll']      = $this;
		$frequencyArgs['action']    = 'vote';
		$frequencyArgs['fullCheck'] = TotalPoll()->option( 'performance.fullChecks.enabled' );

		$frequencyArgs = apply_filters( 'totalpoll/filters/poll/vote/frequency', $frequencyArgs, $this );

		if ( ! empty( $frequencyArgs['cookies']['enabled'] ) ) :
			$this->restrictions->add( 'cookies', new \TotalPoll\Restrictions\Cookies( $frequencyArgs ) );
		endif;

		if ( ! empty( $frequencyArgs['ip']['enabled'] ) ) :
			$this->restrictions->add( 'ip', new \TotalPoll\Restrictions\IPAddress( $frequencyArgs ) );
		endif;

		if ( ! empty( $frequencyArgs['user']['enabled'] ) ) :
			$this->restrictions->add( 'user', new \TotalPoll\Restrictions\LoggedInUser( $frequencyArgs ) );
		endif;

		/**
		 * Fires after restrictions setup.
		 *
		 * @param  ModelContract  $poll  Poll model object.
		 * @param  array  $frequencyArgs  Frequency arguments.
		 *
		 * @since 4.0.0
		 */
		do_action( 'totalpoll/actions/poll/restrictions', $this, $frequencyArgs );

		// Screen
		if ( $this->hasVoted() || ! $this->isAcceptingVotes() ) :
			$this->screen = self::RESULTS_SCREEN;
		endif;

		/**
		 * Fires after first screen setup.
		 *
		 * @param  ModelContract  $poll  Poll model object.
		 *
		 * @since 4.0.0
		 */
		do_action( 'totalpoll/actions/poll/screen', $this );

		// Translation
		$this->setSettingsItem(
			'seo.poll.title',
			$this->getSettingsItem(
				"seo.poll.translations.{$locale}.title",
				$this->getSettingsItem( 'seo.poll.title' )
			)
		);

		$this->setSettingsItem(
			'seo.poll.description',
			$this->getSettingsItem(
				"seo.poll.translations.{$locale}.description",
				$this->getSettingsItem( 'seo.poll.description' )
			)
		);

		$this->setSettingsItem(
			'content.welcome.content',
			$this->getSettingsItem(
				"content.welcome.translations.{$locale}.content",
				$this->getSettingsItem( 'content.welcome.content' )
			)
		);

		$this->setSettingsItem(
			'content.vote.above',
			$this->getSettingsItem(
				"content.vote.translations.{$locale}.above",
				$this->getSettingsItem( 'content.vote.above' )
			)
		);

		$this->setSettingsItem(
			'content.vote.below',
			$this->getSettingsItem(
				"content.vote.translations.{$locale}.below",
				$this->getSettingsItem( 'content.vote.below' )
			)
		);

		$this->setSettingsItem(
			'content.thankyou.content',
			$this->getSettingsItem(
				"content.thankyou.translations.{$locale}.content",
				$this->getSettingsItem( 'content.thankyou.content' )
			)
		);

		$this->setSettingsItem(
			'content.results.above',
			$this->getSettingsItem(
				"content.results.translations.{$locale}.above",
				$this->getSettingsItem( 'content.results.above' )
			)
		);

		$this->setSettingsItem(
			'content.results.below',
			$this->getSettingsItem(
				"content.results.translations.{$locale}.below",
				$this->getSettingsItem( 'content.results.below' )
			)
		);

		$this->setSettingsItem(
			'results.message',
			$this->getSettingsItem(
				"results.translations.{$locale}.message",
				$this->getSettingsItem( 'results.message' )
			)
		);

		/**
		 * Fires after poll model setup is completed.
		 *
		 * @param  ModelContract  $poll  Poll model object.
		 *
		 * @since 4.0.0
		 */
		do_action( 'totalpoll/actions/poll/setup', $this );
	}

	/**
	 * Get settings section or item.
	 *
	 * @param  bool  $section  Settings section.
	 * @param  bool  $args  Path to setting.
	 *
	 * @return mixed|array|null
	 * @since 1.0.0
	 */
	public function getSettings( $section = false, $args = false ) {
		// Deep selection.
		if ( $args !== false && $section && isset( $this->settings[ $section ] ) ) :
			$paths = func_get_args();
			unset( $paths[0] );

			return Arrays::getDeep( $this->settings[ $section ], $paths );
		endif;

		// Return specific settings section, otherwise, return all settings.
		if ( $section ) :
			return isset( $this->settings[ $section ] ) ? $this->settings[ $section ] : null;
		endif;

		return $this->settings;
	}

	/**
	 * @param  array  $settings
	 */
	public function setSettings( array $settings = [] ) {
		$this->settings = $settings;
	}

	/**
	 * Get settings item.
	 *
	 * @param  bool  $needle  Settings name.
	 * @param  bool  $default  Default value.
	 *
	 * @return mixed|array|null
	 * @since 1.0.0
	 */
	public function getSettingsItem( $needle, $default = null ) {
		/**
		 * Filters the poll settings item.
		 *
		 * @param  array  $settings  Poll settings.
		 * @param  string  $default  Default value.
		 * @param  ModelContract  $poll  Poll model object.
		 *
		 * @return mixed
		 * @since 4.0.0
		 */
		return apply_filters(
			"totalpoll/filters/poll/settings-item/{$needle}",
			Arrays::getDotNotation( $this->settings, $needle, $default ),
			$this->settings,
			$default,
			$this
		);
	}

	/**
	 * Get poll post object.
	 *
	 * @return array|mixed|null|\WP_Post
	 */
	public function getPollPost() {
		return $this->pollPost;
	}

	/**
	 * Get poll id.
	 *
	 * @return int
	 * @since 1.0.0
	 */
	public function getId() {
		return (int) $this->id;
	}

	/**
	 * Get poll uid.
	 *
	 * @return int
	 * @since 4.1.3
	 */
	public function getUid() {
		return (string) $this->uid;
	}

	/**
	 * Get poll title.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function getTitle() {
		return $this->pollPost->post_title;
	}

	/**
	 * Get seo attributes.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function getSeoAttributes() {
		if ( $this->seo === null ) :
			$bindings = [
				'title'    => $this->getTitle(),
				'sitename' => get_bloginfo( 'name' ),
			];

			$this->seo = [
				'title'       => Strings::template( $this->getSettingsItem( 'seo.poll.title' ), $bindings ),
				'description' => Strings::template( $this->getSettingsItem( 'seo.poll.description' ), $bindings ),
			];
		endif;

		/**
		 * Filters the poll seo attributes.
		 *
		 * @param  array  $seo  SEO attributes.
		 * @param  ModelContract  $poll  Poll model object.
		 *
		 * @return array
		 * @since 4.0.0
		 */
		return apply_filters( 'totalpoll/filters/poll/seo', $this->seo, $this );
	}

	/**
	 * Get share attributes.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function getShareAttributes() {
		$websites     = array_filter( (array) TotalPoll()->option( 'share.websites', [] ) );
		$websitesUrls = [
			'facebook'  => 'https://www.facebook.com/sharer.php?u={{url}}',
			'twitter'   => 'https://twitter.com/intent/tweet?url={{url}}',
			'pinterest' => 'https://pinterest.com/pin/create/bookmarklet/?url={{url}}',
		];
		$shareUrlArgs = [
			'utm_source'   => '',
			'utm_medium'   => 'poll-share-button',
			'utm_campaign' => $this->getTitle(),
		];

		foreach ( $websitesUrls as $service => $websitesUrl ) :
			$shareUrlArgs['utm_source'] = $service;
			$shareUrl                   = add_query_arg( $shareUrlArgs, $this->getUrl() );
			$websitesUrls[ $service ]   = Strings::template( $websitesUrl, [ 'url' => urlencode( $shareUrl ) ] );
		endforeach;

		/**
		 * Filters the poll sharing attributes.
		 *
		 * @param  array  $attributes  Sharing attributes.
		 * @param  ModelContract  $poll  Poll model object.
		 *
		 * @return array
		 * @since 4.0.0
		 */
		return apply_filters( 'totalpoll/filters/poll/share', array_intersect_key( $websitesUrls, $websites ), $this );
	}

	/**
	 * Get poll thumbnail.
	 *
	 * @return false|string
	 * @since 1.0.0
	 */
	public function getThumbnail() {
		$thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id( $this->id ), 'post-thumbnail' );

		$thumbnail = empty( $thumbnail[0] ) ? TotalPoll()->env( 'url' ) . 'assets/dist/images/poll/no-preview.png' : $thumbnail[0];

		/**
		 * Filters the poll thumbnail.
		 *
		 * @param  array  $attributes  Poll model attributes.
		 * @param  ModelContract  $poll  Poll model object.
		 *
		 * @return string
		 * @since 4.0.0
		 */
		return apply_filters( 'totalpoll/filters/poll/thumbnail', $thumbnail, $this );
	}

	/**
	 * Get time left to start.
	 *
	 * @return int|\DateInterval
	 * @since 1.0.0
	 */
	public function getTimeLeftToStart() {
		$startDate = $this->getSettingsItem( 'vote.limitations.period.start' );
		$startDate = $startDate ? TotalPoll( 'datetime', [ $startDate ] ) : false;

		if ( $startDate && $startDate->getTimestamp() > current_time( 'timestamp' ) ) :
			$now = TotalPoll( 'datetime' );

			return $startDate->diff( $now, true );
		endif;

		return 0;
	}

	/**
	 * Get time left to end.
	 *
	 * @return int|\DateInterval
	 * @since 1.0.0
	 */
	public function getTimeLeftToEnd() {
		$endDate = $this->getSettingsItem( 'vote.limitations.period.end' );
		$endDate = $endDate ? TotalPoll( 'datetime', [ $endDate ] ) : false;

		if ( $endDate && $endDate->getTimestamp() > current_time( 'timestamp' ) ) :
			$now = TotalPoll( 'datetime' );

			return $endDate->diff( $now, true );
		endif;

		return 0;
	}

	/**
	 * Get form.
	 *
	 * @return Form Form object
	 * @since 1.0.0
	 */
	public function getForm() {
		if ( $this->form === null ) :
			$this->form = TotalPoll( 'polls.form', [ $this ] );
		endif;

		return $this->form;
	}

	/**
	 * Get URL.
	 *
	 * @param  array  $args
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function getUrl( $args = [] ) {
		$base = $this->getPermalink();

		if ( ! is_singular( TP_POLL_CPT_NAME ) ) {
			$base = wp_get_referer() ?: TotalPoll( 'http.request' )->request( 'totalpoll.referer' ) ?: $base;
		}

		$url = esc_url( add_query_arg( $args, $base ) );

		/**
		 * Filters the poll urls.
		 *
		 * @param  string  $url  URL.
		 * @param  array  $args  Arguments.
		 * @param  ModelContract  $poll  Poll model object.
		 *
		 * @return string
		 * @since 4.0.0
		 */
		return apply_filters( 'totalpoll/filters/poll/url', $url, $args, $this );
	}

	/**
	 * Get action.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function getAction() {
		return $this->action;
	}

	/**
	 * Get AJAX url.
	 *
	 * @param  array  $args
	 *
	 * @return string
	 */
	public function getAjaxUrl( $args = [] ) {
		$args          = Arrays::parse(
			$args,
			[
				'pollId' => $this->id,
				'action' => $this->getAction(),
				'screen' => $this->getScreen(),
			]
		);
		$base          = admin_url( 'admin-ajax.php' );
		$urlParameters = [ 'action' => 'totalpoll', 'totalpoll' => $args ];

		$url = esc_url( add_query_arg( $urlParameters, $base ) );

		/**
		 * Filters the poll AJAX urls.
		 *
		 * @param  string  $url  URL.
		 * @param  array  $args  Arguments.
		 * @param  ModelContract  $poll  Poll model object.
		 *
		 * @return string
		 * @since 4.0.0
		 */
		return apply_filters( 'totalpoll/filters/poll/ajax-url', $url, $args, $this );
	}

	/**
	 * Get poll permalink.
	 *
	 * @return false|string
	 */
	public function getPermalink() {
		return get_permalink( $this->pollPost );
	}

	/**
	 * Get poll total votes.
	 *
	 * @return int
	 * @since 1.0.0
	 */
	public function getTotalVotes() {
		return $this->totalVotes;
	}

	/**
	 * @return string
	 */
	public function getTotalVotesNumber() {
		return number_format( $this->totalVotes );
	}

	/**
	 * @return string
	 */
	public function getTotalVotesWithLabel() {
		return sprintf(
			_n( '%s Vote', '%s Votes', $this->totalVotes, 'totalpoll' ),
			number_format( $this->totalVotes )
		);
	}

	/**
	 * @param  null  $orderBy
	 * @param  string  $direction
	 *
	 * @return array
	 */
	public function getQuestions( $orderBy = null, $direction = self::SORT_ASC ) {
		if ( $orderBy ) :
			foreach ( $this->questions as &$question ) :
				uasort( $question['choices'], [ $this, 'orderBy' . ucfirst( $orderBy ) ] );

				if ( $direction && strtolower( $direction ) === self::SORT_DESC ) :
					$question['choices'] = array_reverse( $question['choices'], true );
				endif;
			endforeach;
		endif;

		return $this->questions;
	}

	/**
	 * @return array
	 */
	public function getQuestionsForVote() {
		/**
		 * Filters the poll questions (for vote).
		 *
		 * @param  array  $questions  Questions array.
		 * @param  Model  $model  Poll model.
		 *
		 * @return array
		 * @since 4.0.1
		 */
		return apply_filters(
			'totalpoll/filters/poll/questions-for-vote',
			$this->getQuestions( $this->sortChoicesBy, $this->sortChoicesDirection ),
			$this
		);
	}

	/**
	 * @return array
	 */
	public function getQuestionsForResults() {
		/**
		 * Filters the poll questions (for results).
		 *
		 * @param  array  $questions  Questions array.
		 * @param  Model  $model  Poll model.
		 *
		 * @return array
		 * @since 4.0.1
		 */
		return apply_filters(
			'totalpoll/filters/poll/questions-for-results',
			$this->getQuestions( $this->sortResultsBy, $this->sortResultsDirection ),
			$this
		);
	}

	/**
	 * @return int
	 */
	public function getQuestionsCount() {
		return count( $this->questions );
	}

	/**
	 * @return int
	 */
	public function getChoicesCount() {
		return count( $this->choicesMap );
	}

	/**
	 * @param $questionUid
	 *
	 * @return int
	 */
	public function getQuestionChoicesCount( $questionUid ) {
		return empty( $this->questions[ $questionUid ] ) ? 0 : count( $this->questions[ $questionUid ]['choices'] );
	}

	/**
	 * @param $questionUid
	 *
	 * @return array|mixed|null
	 */
	public function getQuestion( $questionUid ) {
		return empty( $this->questions[ $questionUid ] ) ? null : $this->questions[ $questionUid ];
	}

	/**
	 * @param $questionUid
	 *
	 * @return array
	 */
	public function getQuestionChoices( $questionUid ) {
		return empty( $this->questions[ $questionUid ] ) ? [] : $this->questions[ $questionUid ]['choices'];
	}

	/**
	 * @param $questionUid
	 *
	 * @return int
	 */
	public function getQuestionVotes( $questionUid ) {
		return empty( $this->questions[ $questionUid ] ) ? 0 : $this->questions[ $questionUid ]['votes'];
	}

	/**
	 * @param $questionUid
	 *
	 * @return string
	 */
	public function getQuestionVotesWithLabel( $questionUid ) {
		$votes = $this->getQuestionVotes( $questionUid );

		return sprintf( _n( '%s Vote', '%s Votes', $votes, 'totalpoll' ), number_format( $votes ) );
	}

	/**
	 * @param $choiceUid
	 *
	 * @return array|mixed|null
	 */
	public function getQuestionUidByChoiceUid( $choiceUid ) {
		return empty( $this->choicesMap[ $choiceUid ] ) ? null : $this->choicesMap[ $choiceUid ];
	}

	/**
	 * @param $choiceUid
	 *
	 * @return array|null
	 */
	public function getChoice( $choiceUid ) {
		return empty( $this->choicesMap[ $choiceUid ] ) ? null : $this->questions[ $this->choicesMap[ $choiceUid ] ]['choices'][ $choiceUid ];
	}

	/**
	 * @param $choiceUid
	 *
	 * @return int|null
	 */
	public function getChoiceVotes( $choiceUid ) {
		if ( ! isset( $this->choicesMap[ $choiceUid ] ) ) :
			return null;
		endif;

		return $this->questions[ $this->choicesMap[ $choiceUid ] ]['choices'][ $choiceUid ]['votes'];
	}

	/**
	 * @param $choiceUid
	 *
	 * @return null|string
	 */
	public function getChoiceVotesNumber( $choiceUid ) {
		if ( ! isset( $this->choicesMap[ $choiceUid ] ) ) :
			return null;
		endif;

		$votes = $this->questions[ $this->choicesMap[ $choiceUid ] ]['choices'][ $choiceUid ]['votes'];

		return number_format( $votes );
	}

	/**
	 * @param $choiceUid
	 *
	 * @return null|string
	 */
	public function getChoiceVotesWithLabel( $choiceUid ) {
		if ( ! isset( $this->choicesMap[ $choiceUid ] ) ) :
			return null;
		endif;

		$votes = $this->questions[ $this->choicesMap[ $choiceUid ] ]['choices'][ $choiceUid ]['votes'];

		return sprintf( _n( '%s Vote', '%s Votes', $votes, 'totalpoll' ), number_format( $votes ) );
	}

	/**
	 * @param $choiceUid
	 *
	 * @return null|string
	 */
	public function getChoiceVotesPercentage( $choiceUid ) {
		if ( ! isset( $this->choicesMap[ $choiceUid ] ) ) :
			return null;
		endif;

		$votes      = $this->questions[ $this->choicesMap[ $choiceUid ] ]['choices'][ $choiceUid ]['votes'];
		$totalVotes = $this->questions[ $this->choicesMap[ $choiceUid ] ]['votes'];

		$percentage = $totalVotes ? number_format( ( $votes / $totalVotes ) * 100, 2 ) : '0.00';

		return apply_filters(
			'totalpoll/filters/poll/choice/percentage',
			$percentage,
			$votes,
			$totalVotes,
			$choiceUid,
			$this
		);
	}

	/**
	 * @param $choiceUid
	 *
	 * @return string
	 */
	public function getChoiceVotesPercentageWithLabel( $choiceUid ) {
		return $this->getChoiceVotesPercentage( $choiceUid ) . '%';
	}

	/**
	 * @param $choiceUid
	 *
	 * @return string
	 */
	public function getChoiceVotesFormatted( $choiceUid ) {
		$format = apply_filters(
			'totalpoll/filters/poll/choice/votes/format',
			$this->getSettingsItem( 'results.format', '{{votesWithLabel}}' ),
			$choiceUid,
			$this
		);
		$vars   = apply_filters(
			'totalpoll/filters/poll/choice/votes/args',
			[
				'votes'               => $this->getChoiceVotesNumber( $choiceUid ),
				'votesPercentage'     => $this->getChoiceVotesPercentageWithLabel( $choiceUid ),
				'votesWithLabel'      => $this->getChoiceVotesWithLabel( $choiceUid ),
				'votesTotal'          => $this->getQuestionVotes( $this->getQuestionUidByChoiceUid( $choiceUid ) ),
				'votesTotalWithLabel' => $this->getQuestionVotesWithLabel( $this->getQuestionUidByChoiceUid( $choiceUid ) ),
			],
			$choiceUid,
			$this
		);

		return Strings::template( $format, $vars );
	}

	/**
	 * @return array
	 */
	public function getChoices() {
		$choices = [];
		foreach ( $this->choicesMap as $choiceUid => $questionUid ) :
			$choices[ $choiceUid ] = $this->questions[ $questionUid ]['choices'][ $choiceUid ];
		endforeach;

		return $choices;
	}

	/**
	 * @param  array  $args
	 *
	 * @return array
	 */
	public function getChoicesRows( $args = [] ) {
		$perRow  = $this->getSettingsItem( 'design.layout.columns', 4 );
		$choices = $this->getChoices();

		return $perRow === 0 ? [ $choices ] : array_chunk( $choices, $perRow, true );
	}

	/**
	 * @return float|int|string
	 */
	public function getColumnWidth() {
		$perRow = $this->getSettingsItem( 'design.layout.columns', 4 );

		return 100 / $perRow;
	}

	/**
	 * @return null|\WP_Error
	 */
	public function getError() {
		return $this->error;
	}

	/**
	 * @return null|string
	 */
	public function getErrorMessage() {
		return is_wp_error( $this->error ) ? $this->error->get_error_message() : null;
	}

	/**
	 * @return \TotalPollVendors\TotalCore\Contracts\Limitations\Bag|\TotalPollVendors\TotalCore\Limitations\Bag
	 */
	public function getLimitations() {
		return $this->limitations;
	}

	/**
	 * @return \TotalPollVendors\TotalCore\Contracts\Restrictions\Bag|\TotalPollVendors\TotalCore\Restrictions\Bag
	 */
	public function getRestrictions() {
		return $this->restrictions;
	}

	/**
	 * @return array
	 */
	public function getFields() {
		$fields = $this->getSettingsItem( 'fields', [] );
		foreach ( $fields as $fieldIndex => $field ) :
			$fields[ $field['uid'] ] = [
				'type'         => $field['type'],
				'label'        => $field['label'],
				'name'         => $field['name'],
				'defaultValue' => $field['defaultValue'],
				'options'      => $field['options'],
				'required'     => ! empty( $field['validations']['filled']['enabled'] ),
			];
			unset( $fields[ $fieldIndex ] );
		endforeach;

		/**
		 * Filters the poll custom fields.
		 *
		 * @param  array  $fields  Poll custom fields.
		 * @param  ModelContract  $poll  Poll model object.
		 *
		 * @return array
		 * @since 4.0.0
		 */
		return apply_filters( 'totalpoll/filters/poll/fields', $fields, $this );
	}


	/**
	 * Get prefix.
	 *
	 * @param  string  $append
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function getPrefix( $append = '' ) {
		/**
		 * Filters poll prefix.
		 *
		 * @param  string  $prefix  Poll prefix.
		 * @param  string  $append  Appended value.
		 * @param  ModelContract  $poll  Poll model object.
		 *
		 * @return array
		 * @since 4.0.3
		 */
		return apply_filters( 'totalpoll/filters/poll/prefix', "tp_{$this->id}_{$append}", $append, $this );
	}

	/**
	 * Get current screen.
	 *
	 * @return string Current screen.
	 * @since 1.0.0
	 */
	public function getScreen() {
		return $this->screen;
	}

	/**
	 * @return string
	 */
	public function getHeader() {
		$header = '';

		if ( $this->isVoteScreen() ) :
			$header .= $this->getAboveVoteContent();
		elseif ( $this->isResultsScreen() ) :
			$header .= $this->getAboveResultsContent();
		endif;

		return do_shortcode( $header );
	}

	/**
	 * @return string
	 */
	public function getFooter() {
		$footer = '';

		if ( $this->isVoteScreen() ) :
			$footer .= $this->getBelowVoteContent();
		elseif ( $this->isResultsScreen() ) :
			$footer .= $this->getBelowResultsContent();
		endif;

		return do_shortcode( $footer );
	}

	/**
	 * @return string
	 */
	public function getWelcomeContent() {
		return $this->getSettingsItem( 'content.welcome.content' );
	}

	/**
	 * @return string
	 */
	public function getThankyouContent() {
		return $this->getSettingsItem( 'content.thankyou.content' );
	}

	/**
	 * @return string
	 */
	public function getAboveVoteContent() {
		return $this->getSettingsItem( 'content.vote.above' );
	}

	/**
	 * @return string
	 */
	public function getBelowVoteContent() {
		return $this->getSettingsItem( 'content.vote.below' );
	}

	/**
	 * @return string
	 */
	public function getAboveResultsContent() {
		return $this->getSettingsItem( 'content.results.above' );
	}

	/**
	 * @return string
	 */
	public function getBelowResultsContent() {
		return $this->getSettingsItem( 'content.results.below' );
	}

	/**
	 * @return string
	 */
	public function getHiddenResultsContent() {
		return apply_filters(
			'totalpoll/filters/poll/results-hidden/message',
			$this->hasVoted() ? $this->getSettingsItem( 'results.message' ) : '',
			$this
		);
	}

	/**
	 * @return string
	 */
	public function getTemplateId() {
		return $this->getSettingsItem( 'design.template', 'basic-template' );
	}

	/**
	 * @return string
	 */
	public function getPresetUid() {
		return $this->getSettingsItem( 'presetUid', md5( $this->getId() ) );
	}

	/**
	 * Edit link in WordPress dashboard.
	 *
	 * @return string
	 */
	public function getAdminEditLink() {
		return admin_url( "post.php?post={$this->getId()}&action=edit" );
	}

	/**
	 * @param  string  $format
	 *
	 * @return string|void
	 */
	public function getAdminExportAsLink( $format = 'csv' ) {
		$url = sprintf(
			'admin-ajax.php?action=%s&poll=%d&format=%s',
			'totalpoll_insights_download',
			$this->getId(),
			strtolower( (string) $format )
		);

		return admin_url( $url );
	}

	/**
	 * Set/Override settings item value.
	 *
	 * @param $needle
	 * @param $value
	 *
	 * @return void
	 */
	public function setSettingsItem( $needle, $value ) {
		$this->settings = Arrays::setDotNotation( $this->settings, $needle, $value );
	}

	/**
	 * Set current screen.
	 *
	 * @param $screen string Screen name.
	 *
	 * @return $this
	 * @since 1.0.0
	 */
	public function setScreen( $screen ) {
		$this->screen = esc_html( (string) $screen );

		return $this;
	}

	/**
	 * Set action.
	 *
	 * @param  string  $action
	 */
	public function setAction( $action ) {
		$this->action = sanitize_text_field( (string) $action );
	}

	/**
	 * Get received choices.
	 *
	 * @return array
	 */
	public function getReceivedChoices() {
		$choices = [];
		foreach ( $this->choicesMap as $choiceId => $questionUid ) :
			$choice = $this->questions[ $questionUid ]['choices'][ $choiceId ];
			if ( ! empty( $choice['receivedVotes'] ) ) :
				$choices[ $choiceId ] = $choice;
			endif;
		endforeach;

		return $choices;
	}

	public function getReceivedQuestions() {
		$questions = [];
		foreach ( $this->choicesMap as $choiceId => $questionUid ) :
			$choice = $this->questions[ $questionUid ]['choices'][ $choiceId ];
			if ( ! empty( $choice['receivedVotes'] ) ) :
				$questions[ $questionUid ] = true;
			endif;
		endforeach;

		return array_keys( $questions );
	}

	/**
	 * Set form.
	 *
	 * @param  Form  $form
	 *
	 * @return Form Form object
	 * @since 1.0.0
	 */
	public function setForm( Form $form ) {
		return $this->form = $form;
	}

	/**
	 * Set an error.
	 *
	 * @param  \WP_Error  $error
	 *
	 * @since 1.0.0
	 */
	public function setError( $error ) {
		$this->error = $error;
	}

	/**
	 * @return bool
	 */
	public function hasError() {
		return ! empty( $this->error );
	}

	/**
	 * @return bool
	 */
	public function hasVoted() {
		/**
		 * Filters whether current user has voted before.
		 *
		 * @param  bool  $hasVoted  Whether current user has voted or not.
		 * @param  ModelContract  $poll  Poll model object.
		 *
		 * @return array
		 * @since 4.0.0
		 */
		return apply_filters(
			'totalpoll/filters/poll/has-voted',
			is_wp_error(
				$this->getRestrictions()
				     ->check()
			) || $this->getRestrictions()
			          ->isApplied() || did_action( 'totalpoll/actions/after/poll/command/vote' ),
			$this
		);
	}

	/**
	 * @return bool
	 */
	public function hasWelcomeContent() {
		return ! empty( $this->settings['content']['welcome']['content'] );
	}

	/**
	 * @return bool
	 */
	public function hasThankyouContent() {
		return ! empty( $this->settings['content']['thankyou']['content'] );
	}

	/**
	 * @return bool
	 */
	public function hasAboveVoteContent() {
		return ! empty( $this->settings['content']['vote']['above'] );
	}

	/**
	 * @return bool
	 */
	public function hasBelowVoteContent() {
		return ! empty( $this->settings['content']['vote']['below'] );
	}

	/**
	 * @return bool
	 */
	public function hasAboveResultsContent() {
		return ! empty( $this->settings['content']['results']['above'] );
	}

	/**
	 * @return bool
	 */
	public function hasBelowResultsContent() {
		return ! empty( $this->settings['content']['results']['below'] );
	}

	/**
	 * Is poll accepting votes.
	 *
	 * @return bool
	 */
	public function isAcceptingVotes() {
		$limited = $this->getLimitations()
		                ->check();
		if ( is_wp_error( $limited ) ) :
			$this->error = $limited;
		else :
			$restricted = $this->getRestrictions()
			                   ->check();
			if ( is_wp_error( $restricted ) ) :
				$this->error = $restricted;
			endif;
		endif;

		/**
		 * Filters whether the poll is accepting new votes or not.
		 *
		 * @param  bool  $acceptVotes  True when new votes are accepted otherwise false.
		 * @param  ModelContract  $poll  Poll model object.
		 *
		 * @return bool
		 * @since 4.0.0
		 */
		return apply_filters( 'totalpoll/filters/poll/accept-vote', ! is_wp_error( $this->error ), $this );
	}

	/**
	 * Is poll results hidden.
	 *
	 * @return bool
	 */
	public function isResultsHidden() {
		$visibility = $this->getSettingsItem( 'results.visibility' );

		
		$hidden = $visibility === 'voters' && !$this->hasVoted();
		

		


		/**
		 * Filters the poll results visibility.
		 *
		 * @param  bool  $hidden  True when hidden otherwise false.
		 * @param  ModelContract  $poll  Poll model object.
		 *
		 * @return bool
		 * @since 4.0.0
		 */
		return apply_filters( 'totalpoll/filters/poll/results-hidden', $hidden, $this );
	}

	/**
	 * @return bool
	 */
	public function isPaginated() {
		return ! empty( $this->choicesPerPage );
	}

	/**
	 * @param     $choiceUid
	 * @param  int  $by
	 *
	 * @return bool|null
	 */
	public function incrementChoiceVotes( $choiceUid, $by = 1 ) {
		if ( ! isset( $this->choicesMap[ $choiceUid ] ) ) :
			return null;
		endif;

		$this->questions[ $this->choicesMap[ $choiceUid ] ]['votes']                                  += $by;
		$this->questions[ $this->choicesMap[ $choiceUid ] ]['choices'][ $choiceUid ]['votes']         += $by;
		$this->questions[ $this->choicesMap[ $choiceUid ] ]['choices'][ $choiceUid ]['receivedVotes'] += $by;
		$this->questions[ $this->choicesMap[ $choiceUid ] ]['receivedVotes']                          += $by;

		return true;
	}

	/**
	 * @return bool
	 */
	public function isPasswordProtected() {
		return post_password_required( $this->pollPost );
	}

	/**
	 * @return bool
	 */
	public function isMigrated() {
		return get_post_meta( $this->id, '_migrated', true ) || ! get_post_meta( $this->id, '_preset_id', true );
	}

	/*
	 * Load and apply user-provided expressions.
	 *
	 * @return void
	 */
	public function applyPollExpressions() {
		$locale         = get_locale();
		$localeFallback = substr( $locale, 0, 2 );

		if ( ! is_admin() || Misc::isDoingAjax() ) :
			// Get expressions from the model
			// Check if there are expressions using the full locale (i.e. en_US, ar_AR, etc..)
			// If not, we fall back to short locale (en, ar, etc..), otherwise an empty array
			$expressions = Arrays::getDotNotation(
				$this->settings,
				"expressions.{$locale}",
				Arrays::getDotNotation( $this->settings, "expressions.{$localeFallback}", [] )
			);

			if ( ! empty( $expressions ) && ! empty( $GLOBALS['l10n']['totalpoll'] ) ):
				$domain                    = $GLOBALS['l10n']['totalpoll'];
				self::$originalExpressions = unserialize( serialize( $domain ) );

				foreach ( $expressions as $expression => $expressionContent ) :
					if ( empty( $expressionContent['translations'] ) || empty( $expressionContent['translations'][0] ) ) :
						continue;
					endif;

					if ( empty( $domain->entries[ $expression ] ) ) :
						$entry = new \Translation_Entry(
							[
								'singular'     => $expression,
								'translations' => $expressionContent['translations'],
							]
						);
						$domain->add_entry( $entry );
					else :
						$domain->entries[ $expression ]->translations = $expressionContent['translations'];
					endif;
				endforeach;
			endif;
		endif;
	}

	/*
	 * Restore global poll expressions to avoid affecting other polls.
	 *
	 * @return void
	 */
	protected function restorePollExpressions() {
		if ( self::$originalExpressions ):
			$GLOBALS['l10n']['totalpoll'] = self::$originalExpressions;
		endif;
	}

	/**
	 * Render poll.
	 *
	 * @return string
	 * @since 1.0.0
	 */

	public function render() {
		if ( $this->isPasswordProtected() ) :
			return '';
		endif;

		$this->applyPollExpressions();
		$renderedPoll = TotalPoll( 'polls.renderer', [ $this ] )->render();
		$this->restorePollExpressions();

		return $renderedPoll;
	}

	/**
	 * JSON representation of poll.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return $this->toArray();
	}

	/**
	 * Get the instance as an array.
	 *
	 * @return array
	 */
	public function toArray() {
		$poll = [
			'id'            => $this->getId(),
			'title'         => $this->getTitle(),
			'permalink'     => $this->getPermalink(),
			'questions'     => $this->getQuestions(),
			'fields'        => $this->getFields(),
			'receivedVotes' => $this->getReceivedChoices(),
		];

		if ( is_admin() ) :
			$poll['admin'] = [
				'editLink' => $this->getAdminEditLink(),
			];
		endif;

		return $poll;
	}

	/**
	 * @param  mixed  $offset
	 *
	 * @return bool
	 */
	#[\ReturnTypeWillChange]
	public function offsetExists( $offset ) {
		return isset( $this->{$offset} );
	}

	/**
	 * @param  mixed  $offset
	 *
	 * @return mixed
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet( $offset ) {
		return isset( $this->{$offset} ) ? $this->{$offset} : null;
	}

	/**
	 * @param  mixed  $offset
	 * @param  mixed  $value
	 */
	#[\ReturnTypeWillChange]
	public function offsetSet( $offset, $value ) {
	}

	/**
	 * @param  mixed  $offset
	 */
	#[\ReturnTypeWillChange]
	public function offsetUnset( $offset ) {
	}

	/**
	 * Order by votes.
	 *
	 * @param $current
	 * @param $next
	 *
	 * @return int
	 * @since 1.0.0
	 */
	private function orderByVotes( $current, $next ) {
		if ( $current['votes'] === $next['votes'] ) :
			return 0;
		elseif ( $current['votes'] < $next['votes'] ) :
			return - 1;
		else :
			return 1;
		endif;
	}

	/**
	 * Order by label.
	 *
	 * @param $current
	 * @param $next
	 *
	 * @return int
	 * @since 1.0.0
	 */
	private function orderByLabel( $current, $next ) {
		
		return 0;
		

		
	}

	/**
	 * Shuffle.
	 *
	 * @param $current
	 * @param $next
	 *
	 * @return int
	 * @since 1.0.0
	 */
	private function orderByRandom( $current, $next ) {
		
		return 0;
		

		
	}

	/**
	 * Order by original position.
	 *
	 * @param $current
	 * @param $next
	 *
	 * @return int
	 * @since 1.0.0
	 */
	private function orderByPosition( $current, $next ) {
		if ( $current['index'] === $next['index'] ) :
			return 0;
		elseif ( $current['index'] < $next['index'] ) :
			return - 1;
		else :
			return 1;
		endif;
	}

	/**
	 * @param  array  $question
	 * @param  array  $choices
	 *
	 * @return mixed|void
	 */
	public function addQuestion( $question, $choices = [] ) {
		// TODO: Implement addQuestion() method.
	}

	/**
	 * @param  array  $choice
	 * @param  string  $questionUid
	 * @param  bool  $persistent
	 *
	 * @return array|bool
	 */
	public function addChoice( $choice, $questionUid, $persistent = true ) {
		$question = $this->getQuestion( $questionUid );
		$choice   = Arrays::parse(
			$choice,
			[
				'uid'           => 'custom-' . Misc::generateUid(),
				'type'          => 'text',
				'label'         => '',
				'visibility'    => true,
				'votes'         => 0,
				'votesOverride' => 0,
			]
		);

		if ( $question ) :
			if ( $persistent ) :
				$this->settings['questions'][ $question['index'] ]['choices'][] = $choice;
			endif;

			// Add to choices map
			$this->choicesMap[ $choice['uid'] ] = $question['uid'];
			$choice['index']                    = count( $this->settings['questions'][ $question['index'] ]['choices'] ) - 1;
			$choice['questionUid']              = $question['uid'];
			$choice['receivedVotes']            = 0;

			// Cumulative of votes for question
			$this->questions[ $question['uid'] ]['votes']                     += $choice['votes'];
			$this->totalVotes                                                 += $choice['votes'];
			$this->questions[ $question['uid'] ]['choices'][ $choice['uid'] ] = $choice;
		else :
			return false;
		endif;

		return $choice;
	}

	/**
	 * @inheritDoc
	 */
	public function save() {
		// Remove WP filters
		if ( function_exists( 'wp_remove_targeted_link_rel_filters' ) ) {
			wp_remove_targeted_link_rel_filters();
		}

		return ! is_wp_error(
			wp_update_post(
				[
					'ID'           => $this->getId(),
					'post_content' => wp_slash( json_encode( $this->settings, JSON_UNESCAPED_SLASHES ) ),
				]
			)
		);
	}

	/**
	 * @inheritDoc
	 */
	public function refreshUid() {
		$this->setSettingsItem( 'uid', Misc::generateUid() );

		return $this->save();
	}

	/**
	 * @param $criteria
	 *
	 * @return array
	 */
	public function getQuestionsWhere( $criteria ) {
		return array_filter(
			$this->questions,
			function ( $question ) use ( $criteria ) {
				foreach ( $criteria as $key => $value ) :
					if ( ! isset( $question[ $key ] ) || $question[ $key ] != $value ) :
						return false;
					endif;
				endforeach;

				return true;
			}
		);
	}

	/**
	 * @param $criteria
	 *
	 * @return array
	 */
	public function getChoicesWhere( $criteria ) {
		return array_filter(
			$this->choicesMap,
			function ( $question ) use ( $criteria ) {
				foreach ( $criteria as $key => $value ) :
					if ( ! isset( $question[ $key ] ) || $question[ $key ] != $value ) :
						return false;
					endif;
				endforeach;

				return true;
			}
		);
	}

	/**
	 * Set question.
	 *
	 * @param  string  $questionUid
	 * @param  array  $override
	 * @param  bool  $persistent
	 *
	 * @return bool
	 */
	public function setQuestion( $questionUid, $override = [], $persistent = true ) {
		$question = $this->getQuestion( $questionUid );
		if ( $question ) :
			$this->questions[ $question['uid'] ] = Arrays::parse( $override, $question );

			if ( $persistent ) :
				$this->settings['questions'][ $question['index'] ] = Arrays::parse(
					$override,
					$this->settings['questions'][ $question['index'] ]
				);
			endif;

			return true;
		endif;

		return false;
	}

	/**
	 * Set choice.
	 *
	 * @param  string  $choiceUid
	 * @param  array  $override
	 * @param  bool  $persistent
	 *
	 * @return bool
	 */
	public function setChoice( $choiceUid, $override = [], $persistent = true ) {
		$choice = $this->getChoice( $choiceUid );
		if ( $choice ) :
			$question = $this->getQuestion( $choice['questionUid'] );

			$this->questions[ $question['uid'] ]['choices'][ $choice['uid'] ] = Arrays::parse( $override, $choice );

			if ( $persistent ) :
				$this->settings['questions'][ $question['index'] ]['choices'][ $choice['index'] ] = Arrays::parse(
					$override,
					$this->settings['questions'][ $question['index'] ]['choices'][ $choice['index'] ]
				);
			endif;

			return true;
		endif;

		return false;
	}

	/**
	 * Remove question.
	 *
	 * @param  string  $questionUid
	 * @param  bool  $persistent
	 *
	 * @return bool
	 */
	public function removeQuestion( $questionUid, $persistent = true ) {
		$question = $this->getQuestion( $questionUid );
		if ( $question ) :
			foreach ( $question['choices'] as $choiceUid => $choice ) :
				$this->removeChoice( $choiceUid, $persistent );
			endforeach;

			if ( $persistent ) :
				unset( $this->settings['questions'][ $question['index'] ] );
			endif;

			return true;
		endif;

		return false;
	}

	/**
	 * Remove choice.
	 *
	 * @param  string  $choiceUid
	 * @param  bool  $persistent
	 *
	 * @return bool
	 */
	public function removeChoice( $choiceUid, $persistent = true ) {
		$choice = $this->getChoice( $choiceUid );
		if ( $choice ) :
			$question = $this->getQuestion( $choice['questionUid'] );

			unset( $this->questions[ $question['uid'] ]['choices'][ $choice['uid'] ], $this->choicesMap[ $choice['uid'] ] );

			if ( $persistent ) :
				unset( $this->settings['questions'][ $question['index'] ]['choices'][ $choice['index'] ] );
			endif;

			return true;
		endif;

		return false;
	}

	/**
	 * @param  string  $screen
	 *
	 * @return bool
	 */
	public function isScreen( $screen ) {
		return $this->screen === $screen;
	}

	/**
	 * @return bool
	 */
	public function isWelcomeScreen() {
		return $this->isScreen( self::WELCOME_SCREEN );
	}

	/**
	 * @return bool
	 */
	public function isVoteScreen() {
		return $this->isScreen( self::VOTE_ACTION );
	}

	/**
	 * @return bool
	 */
	public function isThankYouScreen() {
		return $this->isScreen( self::THANKYOU_SCREEN );
	}

	/**
	 * @return bool
	 */
	public function isResultsScreen() {
		return $this->isScreen( self::RESULTS_SCREEN );
	}

	/**
	 * @return array
	 */
	public function getViewVariables() {
		$variables = [
			'totalVotes'          => number_format_i18n( $this->getTotalVotes() ),
			'totalVotesWithLabel' => sprintf(
				_n( '%s vote', '%s votes', $this->getTotalVotes(), 'totalpoll' ),
				number_format_i18n( $this->getTotalVotes() )
			),
		];

		$questions = $this->getQuestions();

		$questionIndex = 0;

		foreach ( $questions as $question ) :
			$questionIndex ++;

			$votes                                                       = $this->getQuestionVotes( $question['uid'] );
			$variables["totalVotesForQuestion{$questionIndex}"]          = number_format_i18n( $votes );
			$variables["totalVotesWithLabelForQuestion{$questionIndex}"] = sprintf(
				_n(
					'%s vote',
					'%s votes',
					$votes,
					'totalpoll'
				),
				number_format_i18n( $votes )
			);
		endforeach;

		return apply_filters( 'totalpoll/filters/poll/view-variables', $variables, $this );
	}


	/**
	 * Check if choice was checked.
	 *
	 * @param $choiceUid
	 *
	 * @return bool
	 */
	public function isChoiceChecked( $choiceUid ) {
		return !empty($this->getChoice($choiceUid)['receivedVotes']);
	}
}
