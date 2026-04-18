<?php

namespace TotalPollVendors\TotalCore\Form\Fields;


use TotalPollVendors\TotalCore\Application;
use TotalPollVendors\TotalCore\Form\Field as FieldAbstract;
use TotalPollVendors\TotalCore\Helpers\Html;

/**
 * Class TextareaField
 * @package TotalPollVendors\TotalCore\Form\Fields
 */
class TextareaField extends FieldAbstract {

	/**
	 * @return Html
	 */
	public function getInputHtmlElement() {
		$field = new Html(
			'textarea',
			$this->getAttributes(),
			$this->getValue()
		);
		$field->appendToAttribute( 'class', Application::getInstance()->env( 'slug' ) . '-form-field-input' );

		return $field;
	}

	/**
	 * @return array
	 */
	public function getAttributes() {
		$attributes = array_diff_key( parent::getAttributes(), array_flip( [ 'value', 'type' ] ) );

		return $attributes;
	}

}