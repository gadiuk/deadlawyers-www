<?php

namespace TotalPoll\Poll;

use TotalPollVendors\TotalCore\Contracts\Form\Factory;
use TotalPollVendors\TotalCore\Contracts\Form\Field;
use TotalPollVendors\TotalCore\Contracts\Http\Request;
use TotalPollVendors\TotalCore\Helpers\Arrays;

/**
 * Poll Form.
 *
 * @package TotalPoll\Poll
 */
class Form extends \TotalPollVendors\TotalCore\Form\Form {
	/**
	 * @var Request $request
	 */
	protected $request;
	/**
	 * @var Factory $formFactory
	 */
	protected $formFactory;
	/**
	 * @var \TotalPoll\Contracts\Poll\Model $poll
	 */
	protected $poll;

	/**
	 * Form constructor.
	 *
	 * @param  Request  $request
	 * @param                                 $formFactory
	 * @param  \TotalPoll\Contracts\Poll\Model  $poll
	 */
	public function __construct( Request $request, $formFactory, \TotalPoll\Contracts\Poll\Model $poll ) {
		parent::__construct();
		$this->request     = $request;
		$this->formFactory = $formFactory;
		$this->poll        = $poll;

		$hiddenFieldsPage = $this->formFactory->makePage();

		$screenField = $this->formFactory->makeTextField();
		$screenField->setName( 'screen' );
		$screenField->setOptions( [ 'type' => 'hidden', 'name' => 'totalpoll[screen]', 'label' => false ] );
		$screenField->setValue( $this->poll->getScreen() );

		$pollIdField = $this->formFactory->makeTextField();
		$pollIdField->setName( 'pollId' );
		$pollIdField->setOptions(
			[
				'type'  => 'hidden',
				'name'  => 'totalpoll[pollId]',
				'label' => false,
			]
		);
		$pollIdField->setValue( $this->poll->getId() );

		$actionField = $this->formFactory->makeTextField();
		$actionField->setName( 'action' );
		$actionField->setOptions(
			[
				'type'  => 'hidden',
				'name'  => 'totalpoll[action]',
				'label' => false,
			]
		);

		$hiddenFieldsPage[] = $screenField;
		$hiddenFieldsPage[] = $pollIdField;
		$hiddenFieldsPage[] = $actionField;

		$this->pages['hiddenFields'] = $hiddenFieldsPage;

		$customFieldsPage = $this->formFactory->makePage();

		$fields = apply_filters( 'totalpoll/filters/form/fields',
		                         (array) $this->poll->getSettingsItem( 'fields', [] ) );

		// @TODO: Move this
		$uniqueCallback = function ( Field $field, $args = [] ) {
			$value         = $field->getValue();
			$database      = TotalPoll( 'database' );
			$databaseTable = TotalPoll()->env( 'db.tables.entries' );
			$search        = sprintf( '%%"%s":"%s"%%', $field->getName(), $field->getValue() );
			$sql           = "SELECT count(id) FROM {$databaseTable} WHERE poll_id = %d AND fields LIKE %s";
			$query         = $database->prepare( $sql, $args['pollId'], $search );

			if ( ! empty( $value ) && $database->get_var( $query ) ):
				return esc_html__( '{{label}} has been used before.', 'totalpoll' );
			endif;

			return true;
		};

		foreach ( $fields as $fieldSettings ):
			
			$field = call_user_func( [ $this->formFactory, 'makeTextField' ] );
			

			

			$field = apply_filters( "totalpoll/filters/form/field/{$fieldSettings['type']}", $field, $fieldSettings );

			if ( ! $field instanceof Field ) :
				continue;
			endif;

			if ( ! empty( $fieldSettings['validations']['unique']['enabled'] ) ):
				$fieldSettings['validations']['unique']['pollId']   = $this->poll->getId();
				$fieldSettings['validations']['unique']['callback'] = $uniqueCallback;
			endif;
			$multiple = ! empty( $fieldSettings['attributes']['multiple'] ) || $fieldSettings['type'] === 'checkbox';

			$field->setName( $fieldSettings['name'] );
			$field->setOptions(
				[
					'id'          => "totalpoll-fields-{$fieldSettings['name']}",
					'name'        => "totalpoll[fields][{$fieldSettings['name']}]" . ( $multiple ? '[]' : '' ),
					'default'     => empty( $fieldSettings['defaultValue'] ) ? null : $fieldSettings['defaultValue'],
					'placeholder' => isset( $fieldSettings['placeholder'] ) ? $fieldSettings['placeholder'] : '',
					'label'       => isset( $fieldSettings['label'] ) ? $fieldSettings['label'] : false,
					'validations' => isset( $fieldSettings['validations'] ) ? $fieldSettings['validations'] : [],
					'options'     => isset( $fieldSettings['options'] ) ? $fieldSettings['options'] : [],
					'attributes'  => isset( $fieldSettings['attributes'] ) ? $fieldSettings['attributes'] : [],
					'template'    => isset( $fieldSettings['template'] ) ? $fieldSettings['template'] : false,
				]
			);
			$fieldValue = null;
			$fieldValue = $this->request->request( "totalpoll.fields.{$fieldSettings['name']}",
			                                       empty( $_POST['totalpoll']['fields'] ) ? empty( $fieldSettings['defaultValue'] ) ? null : $fieldSettings['defaultValue'] : '' );
			if ( is_array( $fieldValue ) ) {
				$fieldValue = Arrays::apply(
					$fieldValue,
					function ( $value ) {
						return is_array( $value ) ? Arrays::apply( $value, 'esc_html' ) : esc_html( $value );
					}
				);
			} else {
				$fieldValue = esc_html( $fieldValue );
			}

			$field->setValue( $fieldValue );
			$customFieldsPage[] = $field;
		endforeach;

		
		$this->pages['fields'] = $customFieldsPage;

		/**
		 * Filters the form pages.
		 *
		 * @param  array  $pages  Form pages.
		 * @param  \TotalPoll\Contracts\Poll\Model  $poll  Poll model object.
		 * @param  \TotalPollVendors\TotalCore\Contracts\Form\Form  $form  Form object.
		 *
		 * @return array
		 * @since 4.0.0
		 */
		$this->pages = apply_filters( 'totalpoll/filters/form/pages', $this->pages, $this->poll, $this );
	}

	/**
	 * Open tag.
	 *
	 * @return string
	 */
	public function open() {
		return $this->getFormElement()->appendToAttribute( 'novalidate', 'novalidate' )->getOpenTag();
	}

	/**
	 * Close tag.
	 *
	 * @return string
	 */
	public function close() {
		return $this->getFormElement()->getCloseTag();
	}

	/**
	 * Hidden fields.
	 *
	 * @return mixed
	 */
	public function hiddenFields() {
		return $this->pages['hiddenFields']->render();
	}

	/**
	 * Fields.
	 *
	 * @return null
	 */
	public function fields() {
		$screen = $this->poll->getScreen();
		if ( $screen !== Model::VOTE_SCREEN ):
			return null;
		endif;

		return $this->pages['fields']->render();
	}

	/**
	 * Buttons.
	 *
	 * @return string
	 */
	public function buttons() {
		$buttons = [];

		if ( $this->poll->isWelcomeScreen() ):
			$buttons[] = new \TotalPollVendors\TotalCore\Helpers\Html(
				'button',
				[
					'type'  => 'submit',
					'name'  => 'totalpoll[action]',
					'value' => Model::WELCOME_ACTION,
					'class' => 'totalpoll-button totalpoll-button-primary totalpoll-buttons-continue',
				],
				esc_html__( 'Continue to vote', 'totalpoll' )
			);
		elseif ( $this->poll->isThankYouScreen() ):
			if ( ! $this->poll->isResultsHidden() || ! empty( $this->poll->getHiddenResultsContent() ) ):
				$buttons[] = new \TotalPollVendors\TotalCore\Helpers\Html(
					'button',
					[
						'type'  => 'submit',
						'name'  => 'totalpoll[action]',
						'value' => Model::THANKYOU_ACTION,
						'class' => 'totalpoll-button totalpoll-button-primary totalpoll-buttons-continue',
					],
					esc_html__( 'Continue to results', 'totalpoll' )
				);
			endif;
		elseif ( $this->poll->isVoteScreen() ):
			if ( $this->poll->getSettingsItem( 'results.visibility' ) === 'all' ):
				$buttons[] = new \TotalPollVendors\TotalCore\Helpers\Html(
					'button',
					[
						'type'  => 'submit',
						'name'  => 'totalpoll[action]',
						'value' => Model::RESULTS_ACTION,
						'class' => 'totalpoll-button totalpoll-buttons-results',
					],
					esc_html__( 'Results', 'totalpoll' )
				);
			endif;

			$buttons[] = new \TotalPollVendors\TotalCore\Helpers\Html(
				'button',
				[
					'type'  => 'submit',
					'name'  => 'totalpoll[action]',
					'value' => Model::VOTE_ACTION,
					'class' => 'totalpoll-button totalpoll-button-primary totalpoll-buttons-vote',
				],
				esc_html__( 'Vote', 'totalpoll' )
			);
		elseif ( $this->poll->isResultsScreen() ):
			if ( $this->poll->isAcceptingVotes() && ! $this->poll->getRestrictions()->isApplied() ):
				$buttons[] = new \TotalPollVendors\TotalCore\Helpers\Html(
					'button',
					[
						'type'  => 'submit',
						'name'  => 'totalpoll[action]',
						'value' => Model::WELCOME_ACTION,
						'class' => 'totalpoll-button totalpoll-buttons-back',
					],
					esc_html__( 'Back to vote', 'totalpoll' )
				);
			endif;
		endif;

		/**
		 * Filters the form buttons.
		 *
		 * @param  array  $buttons  Form buttons.
		 * @param  \TotalPoll\Contracts\Poll\Model  $poll  Poll model object.
		 * @param  \TotalPollVendors\TotalCore\Contracts\Form\Form  $form  Form object.
		 *
		 * @return array
		 * @since 4.0.0
		 */
		$buttons = apply_filters( 'totalpoll/filters/form/buttons', $buttons, $this->poll, $this );

		return implode( '', $buttons );
	}

	/**
	 * Choice input.
	 *
	 * @param $choice
	 * @param $question
	 *
	 * @return \TotalPollVendors\TotalCore\Helpers\Html
	 */
	public function getChoiceInput( $choice, $question ) {
		$input = new \TotalPollVendors\TotalCore\Helpers\Html(
			'input',
			[
				'type'    => $this->isQuestionSupportMultipleSelection( $question ) ? 'checkbox' : 'radio',
				'id'      => sprintf( 'choice-%s-selector', $choice['uid'] ),
				'name'    => sprintf( 'totalpoll[choices][%s][]', esc_attr( $question['uid'] ) ),
				'value'   => esc_attr( $choice['uid'] ),
				'checked' => $this->isChoiceChecked( $choice, $question ),
			]
		);

		return $input;
	}

	/**
	 * Check if question is multi-selection question.
	 *
	 * @param $question
	 *
	 * @return bool
	 */
	public function isQuestionSupportMultipleSelection( $question ) {
		$maxSelected = Arrays::getDotNotation( $question, 'settings.selection.maximum', 1 );

		return $maxSelected > 1;
	}

	/**
	 * @param $question
	 *
	 * @return \TotalPollVendors\TotalCore\Helpers\Html
	 */
	public function getOtherChoiceInput( $question ) {
		$maxSelected = Arrays::getDotNotation( $question, 'settings.selection.maximum', 1 );
		$input       = new \TotalPollVendors\TotalCore\Helpers\Html(
			'input',
			[
				'type'    => $maxSelected > 1 ? 'checkbox' : 'radio',
				'id'      => sprintf( 'choice-other-selector-%s', esc_attr( $question['uid'] ) ),
				'name'    => sprintf( 'totalpoll[choices][%s][]', esc_attr( $question['uid'] ) ),
				'value'   => '',
				'checked' => (bool) $this->request->request( "totalpoll.choices.{$question['uid']}.other" ),
			]
		);

		return $input;
	}

	/**
	 * @param $question
	 *
	 * @return \TotalPollVendors\TotalCore\Helpers\Html
	 */
	public function getOtherContentInput( $question ) {
		$input = new \TotalPollVendors\TotalCore\Helpers\Html(
			'input',
			[
				'type'        => 'text',
				'id'          => 'choice-other-content-' . esc_attr( $question['uid'] ),
				'placeholder' => esc_html__( 'Other', 'totalpoll' ),
				'name'        => sprintf( 'totalpoll[choices][%s][other]', esc_attr( $question['uid'] ) ),
				'value'       => esc_attr( $this->request->request( "totalpoll.choices.{$question['uid']}.other" ) ),
			]
		);

		return $input;
	}

	/**
	 * Check if choice was checked.
	 *
	 * @param $choice
	 * @param $question
	 *
	 * @return bool
	 */
	public function isChoiceChecked( $choice, $question ) {
		return in_array( $choice['uid'],
		                 $this->request->request( 'totalpoll.choices.' . esc_attr( $question['uid'] ), [] ),
		                 true );
	}

	/**
	 * Check if there are custom fields.
	 *
	 * @return bool
	 */
	public function haveCustomFields() {
		return count( $this->pages['fields'] ) > 0;
	}
}
