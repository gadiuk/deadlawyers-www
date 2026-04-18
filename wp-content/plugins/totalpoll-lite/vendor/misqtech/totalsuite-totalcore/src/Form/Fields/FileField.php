<?php

namespace TotalPollVendors\TotalCore\Form\Fields;


use TotalPollVendors\TotalCore\Form\Field as FieldAbstract;
use TotalPollVendors\TotalCore\Helpers\Html;
use TotalPollVendors\TotalCore\Http\File;

/**
 * Class FileField
 * @package TotalPollVendors\TotalCore\Form\Fields
 */
class FileField extends FieldAbstract {

	/**
	 * @return Html
	 */
	public function getInputHtmlElement() {
		/**
		 * @var Html $field
		 */
		$field = new Html( 'input', $this->getAttributes() );
		$field->setAttribute( 'type', 'file' );
		$field->appendToAttribute( 'class', \TotalPollVendors\TotalCore\Application::getInstance()->env( 'slug' ) . '-form-field-input' );

		return $field;
	}

	/**
	 * @return null|File
	 */
	public function getValue() {
		return $this->value;
	}
}
