<?php

namespace TotalPollVendors\TotalCore\Form\Fields;


use TotalPollVendors\TotalCore\Form\Field as FieldAbstract;
use TotalPollVendors\TotalCore\Helpers\Html;

/**
 * Class TextField
 * @package TotalPollVendors\TotalCore\Form\Fields
 */
class TextField extends FieldAbstract {

	/**
	 * @return Html
	 */
	public function getInputHtmlElement() {
		$field = new Html( 'input', $this->getAttributes() );
		$field->appendToAttribute( 'class', \TotalPollVendors\TotalCore\Application::getInstance()->env( 'slug' ) . '-form-field-input' );

		if ( $field->getAttribute( 'type' ) === 'hidden' ):
			$this->template = '<div class="' . \TotalPollVendors\TotalCore\Application::getInstance()->env( 'slug' ) . "-form-field-hidden\">{$this->template}</div>";
		endif;

		return $field;
	}

	/**
	 * @return array
	 */
	public function getAttributes() {
		$attributes          = parent::getAttributes();
		$attributes['value'] = $this->getValue();
		$attributes['type']  = empty( $attributes['type'] ) ? 'text' : $attributes['type'];

		return $attributes;
	}
}