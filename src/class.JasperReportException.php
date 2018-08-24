<?php

namespace srag\JasperReport;

use Exception;

/**
 * Class JasperReportException
 *
 * @package srag\JasperReport
 *
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
	public function __construct($message) {
		parent::__construct($message, 0, NULL);
	}


	/**
	 * @param array $errors
	 */
	public function setErrors(array $errors) {
		$this->errors = $errors;
	}


	/**
	 * @return array
	 */
	public function getErrors() {
		return $this->errors;
	}
}
