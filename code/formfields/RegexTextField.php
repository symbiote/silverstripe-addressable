<?php
/**
 * A text field that validates that its contents match a regular expression.
 *
 * @package silverstripe-addressable
 */
class RegexTextField extends TextField {

	protected $regex;

	/**
	 * @return string
	 */
	public function getRegex() {
		return $this->regex;
	}

	/**
	 * @param string $regex
	 */
	public function setRegex($regex) {
		$this->regex = $regex;
	}

	public function validate($validator) {
		if($this->value && $this->regex) {
			if(!preg_match($this->regex, $this->value)) {
				$message = _t('Addressable.SUBURB', 'Please enter a valid format for "%s".');
				$validator->validationError($this->name, sprintf($message, $this->name), 'validation');
				return false;
			}
		}

		return true;
	}

}