<?php

namespace TotalPoll\Contracts\Preset;

use ArrayAccess;
use DateInterval;
use JsonSerializable;
use TotalPollVendors\TotalCore\Contracts\Form\Form;
use TotalPollVendors\TotalCore\Contracts\Helpers\Arrayable;
use TotalPollVendors\TotalCore\Restrictions\Bag;
use WP_Error;
use WP_Post;

/**
 * Interface Model
 * @package TotalPoll\Contracts\Poll
 */
interface Model extends ArrayAccess, JsonSerializable, Arrayable {
	/**
	 * Get settings section or item.
	 *
	 * @param bool $section Settings section.
	 * @param bool $args Path to setting.
	 *
	 * @return mixed|array|null
	 * @since 1.0.0
	 */
	public function getSettings( $section = false, $args = false );

	/**
	 * Get settings item.
	 *
	 * @param bool $needle Settings name.
	 * @param bool $default Default value.
	 *
	 * @return mixed|array|null
	 * @since 1.0.0
	 */
	public function getSettingsItem( $needle, $default = null );

	/**
	 * Get poll post object.
	 *
	 * @return array|mixed|null|WP_Post
	 */
	public function getPresetPost();

	/**
	 * Get poll id.
	 *
	 * @return int
	 * @since 1.0.0
	 */
	public function getId();

	/**
	 * Get poll uid.
	 *
	 * @return int
	 * @since 4.1.3
	 */
	public function getUid();

	/**
	 * Get poll title.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function getTitle();


	/**
	 * Get poll thumbnail.
	 *
	 * @return false|string
	 * @since 1.0.0
	 */
	public function getThumbnail();

	/**
	 * Get time left to start.
	 *
	 * @return int|DateInterval
	 * @since 1.0.0
	 */
	public function getTimeLeftToStart();

	/**
	 * Get time left to end.
	 *
	 * @return int|DateInterval
	 * @since 1.0.0
	 */
	public function getTimeLeftToEnd();

	/**
	 * Get URL.
	 *
	 * @param array $args
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function getUrl( $args = [] );

	/**
	 * @param null $orderBy
	 * @param string $direction
	 *
	 * @return array
	 */
	public function getQuestions( $orderBy = null, $direction = 'ASC' );

	/**
	 * @param array $criteria
	 *
	 * @return array
	 */
	public function getQuestionsWhere( $criteria );

	/**
	 * @return array
	 */
	public function getQuestionsForVote();

	/**
	 * @return array
	 */
	public function getQuestionsForResults();

	/**
	 * @return int
	 */
	public function getQuestionsCount();

	/**
	 * @return int
	 */
	public function getChoicesCount();

	/**
	 * @param $questionUid
	 *
	 * @return int
	 */
	public function getQuestionChoicesCount( $questionUid );

	/**
	 * @param $questionUid
	 *
	 * @return array|null
	 */
	public function getQuestion( $questionUid );

	/**
	 * @param $questionUid
	 * @return array
*/
	public function getQuestionChoices( $questionUid );

	public function getQuestionVotes( $questionUid );

	public function getQuestionVotesWithLabel( $questionUid );

	/**
	 * @param $choiceUid
	 * @return array|null
*/
	public function getQuestionUidByChoiceUid( $choiceUid );

	/**
	 * @param $choiceUid
	 * @return array|null
*/
	public function getChoice( $choiceUid );

	/**
	 * @param $choiceUid
	 * @return int
*/
	public function getChoiceVotes( $choiceUid );

	/**
	 * @param $choiceUid
	 * @return string
*/
	public function getChoiceVotesNumber( $choiceUid );

	/**
	 * @param $choiceUid
	 * @return string
*/
	public function getChoiceVotesWithLabel( $choiceUid );

	/**
	 * @param $choiceUid
	 * @return string
*/
	public function getChoiceVotesPercentage( $choiceUid );

	/**
	 * @param $choiceUid
	 * @return string
*/
	public function getChoiceVotesPercentageWithLabel( $choiceUid );

	/**
	 * @param $choiceUid
	 * @return string
*/
	public function getChoiceVotesFormatted( $choiceUid );

	/**
	 * @return array
	 */
	public function getChoices();

	/**
	 * @param $criteria
	 *
	 * @return array
	 */
	public function getChoicesWhere( $criteria );

	/**
	 * @param array $args
	 * @return array
*/
	public function getChoicesRows( $args = [] );

	/**
	 * @return string
	 */
	public function getColumnWidth();

	/**
	 * @return WP_Error|null
	 */
	public function getError();

	/**
	 * @return string
	 */
	public function getErrorMessage();

	/**
	 * @return \TotalPollVendors\TotalCore\Contracts\Limitations\Bag
	 */
	public function getLimitations();

	/**
	 * @return Bag
	 */
	public function getRestrictions();

	/**
	 * @return array
	 */
	public function getFields();

	/**
	 * Get prefix.
	 *
	 * @param string $append
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function getPrefix( $append = '' );

	/**
	 * @return string
	 */
	public function getWelcomeContent();

	/**
	 * @return string
	 */
	public function getThankyouContent();

	/**
	 * @return string
	 */
	public function getAboveVoteContent();

	/**
	 * @return string
	 */
	public function getBelowVoteContent();

	/**
	 * @return string
	 */
	public function getAboveResultsContent();

	/**
	 * @return string
	 */
	public function getTemplateId();

	/**
	 * @return string
	 */
	public function getPresetUid();

	/**
	 * Edit link in WordPress dashboard.
	 * @return string
	 */
	public function getAdminEditLink();

	/**
	 * Set/Override settings item value.
	 *
	 * @param $needle
	 * @param $value
	 *
	 * @return void
	 */
	public function setSettingsItem( $needle, $value );

	/**
	 * @return array
	 */
	public function getReceivedChoices();

	/**
	 * @return array
	 */
	public function getReceivedQuestions();

	/**
	 * Set form.
	 *
	 * @param Form $form
	 *
	 * @return Form Form object
	 * @since 1.0.0
	 */
	public function setForm( Form $form );

	/**
	 * Set an error.
	 *
	 * @param WP_Error $error
	 *
	 * @since 1.0.0
	 */
	public function setError( $error );


	/**
	 * Is paginated.
	 *
	 * @return bool
	 */
	public function isPaginated();


	/**
	 * Is password protected.
	 *
	 * @return bool
	 */
	public function isPasswordProtected();

	/**
	 * Add question.
	 *
	 * @param array $question
	 * @param array $choices
	 *
	 * @return mixed
	 */
	public function addQuestion( $question, $choices = [] );

	/**
	 * Add choice to a question.
	 *
	 * @param array $choice
	 * @param string $questionUid
	 * @param bool $persistent
	 *
	 * @return array
	 */
	public function addChoice( $choice, $questionUid, $persistent = true );

	/**
	 * Set question.
	 *
	 * @param string $questionUid
	 * @param array $override
	 *
	 * @return bool
	 */
	public function setQuestion( $questionUid, $override = [] );

	/**
	 * Set choice.
	 *
	 * @param string $choiceUid
	 * @param array $override
	 * @param bool $persistent
	 *
	 * @return bool
	 */
	public function setChoice( $choiceUid, $override = [], $persistent = true );

	/**
	 * Remove question.
	 *
	 * @param string $questionUid
	 * @param bool $persistent
	 *
	 * @return bool
	 */
	public function removeQuestion( $questionUid, $persistent = true );

	/**
	 * Remove choice.
	 *
	 * @param string $choiceUid
	 * @param bool $persistent
	 *
	 * @return bool
	 */
	public function removeChoice( $choiceUid, $persistent = true );

	/**
	 * Save model.
	 *
	 * @return bool
	 */
	public function save();

	/**
	 * Refresh poll UID.
	 *
	 * @return bool
	 */
	public function refreshUid();
}
