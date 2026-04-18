<?php

namespace TotalPoll\Modules;

use TotalPoll\Contracts\Poll\Model;
use TotalPollVendors\TotalCore\Helpers\Html;

/**
 * Template.
 * @package TotalPoll\Modules
 */
abstract class Template extends \TotalPollVendors\TotalCore\Modules\Template {
	protected $locale;

	/**
	 * Template constructor.
	 *
	 * @param array $options
	 */
	public function __construct( $options = [] ) {
		parent::__construct( $options );
		$this->locale = get_locale();
		add_action( 'totalpoll/actions/render', [ $this, 'assets' ] );
	}

	/**
	 * Enqueue template related assets.
	 *
	 * @since 4.0.3
	 */
	public function assets() {

	}

	/**
	 * Get poll instance.
	 *
	 * @since 4.0.3
	 * @return Model
	 */
	public function getPoll() {
		return $this->getOption( 'poll' );
	}

	/**
	 * Format question content.
	 *
	 * @param $question
	 *
	 * @return string
	 */
	public function questionContent( $question ) {
		$content = wpautop( do_shortcode( $question['content'] ) );

		/**
		 * Filters the content of a question.
		 *
		 * @param string $content  Question content.
		 * @param array  $question Question.
		 *
		 * @since 4.0.0
		 * @return string
		 */
		return apply_filters( 'totalpoll/filters/template/content/question', $content, $question );
	}

	/**
	 * Format choice label.
	 *
	 * @param $choice
	 *
	 * @return string
	 */
	public function choiceLabel( $choice ) {
		$label = do_shortcode( $choice['label'] );

		/**
		 * Filters the content of a choice label.
		 *
		 * @param string $label  Choice label content.
		 * @param array  $choice Choice.
		 *
		 * @since 4.0.0
		 * @return string
		 */
		return apply_filters( 'totalpoll/filters/template/content/choice', $label, $choice );
	}

	/**
	 * Format user content.
	 *
	 * @param $content
	 *
	 * @return string
	 */
	public function userContent( $content ) {
		$content = wpautop( do_shortcode( $content ) );

		/**
		 * Filters the content of user-defined fragments.
		 *
		 * @param string $content Fragment content.
		 *
		 * @since 4.0.0
		 * @return string
		 */
		return apply_filters( 'totalpoll/filters/template/content/user', $content );
	}

	/**
	 * @param $question
	 *
	 * @return string
	 */
	public function questionAttributes( $question ) {
		/**
		 * Filters the HTML attributes of question container.
		 *
		 * @param array $attributes Attributes [name => value].
		 * @param array $question   Question.
		 *
		 * @since 4.0.0
		 * @return array
		 */
		$attributes = apply_filters( 'totalpoll/filters/template/attributes/question/container', [], $question );

		return Html::attributesToHtml( $attributes );
	}

	/**
	 * @param $question
	 *
	 * @return string
	 */
	public function questionContentAttributes( $question ) {
		/**
		 * Filters the HTML attributes of question content.
		 *
		 * @param array $attributes Attributes [name => value].
		 * @param array $question   Question.
		 *
		 * @since 4.0.0
		 * @return array
		 */
		$attributes = apply_filters( 'totalpoll/filters/template/attributes/question/content', [], $question );

		return Html::attributesToHtml( $attributes );
	}

	/**
	 * @param $choice
	 *
	 * @return string
	 */
	public function choiceAttributes( $choice ) {
		/**
		 * Filters the HTML attributes of choice container.
		 *
		 * @param array $attributes Attributes [name => value].
		 * @param array $question   Choice.
		 *
		 * @since 4.0.0
		 * @return array
		 */
		$attributes = apply_filters( 'totalpoll/filters/template/attributes/choice/container', [], $choice );

		return Html::attributesToHtml( $attributes );
	}

	/**
	 * @param $choice
	 *
	 * @return string
	 */
	public function choiceLabelAttributes( $choice ) {
		/**
		 * Filters the HTML attributes of choice label.
		 *
		 * @param array $attributes Attributes [name => value].
		 * @param array $question   Choice.
		 *
		 * @since 4.0.0
		 * @return array
		 */
		$attributes = apply_filters( 'totalpoll/filters/template/attributes/choice/label', [], $choice );

		return Html::attributesToHtml( $attributes );
	}
}