<?php

namespace srag\JasperReport;

use Exception;

/**
 * Class JasperReportException
 *
 * @package srag\JasperReport
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 */
class JasperReportException extends Exception {

	/**
	 * @var array
	 */
	protected $errors = array();


	/**
	 * JasperReportException constructor
	 *
	 * @param string $message
	 */
	public function __construct(/*string*/
		$message) {
		parent::__construct($message, 0, NULL);
	}


	/**
	 * @param array $errors
	 */
	public function setErrors(array $errors)/*: void*/ {
		$this->errors = $errors;
	}


	/**
	 * @return array
	 */
	public function getErrors()/*: array*/ {
		return $this->errors;
	}
}
