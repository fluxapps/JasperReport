<?php

/**
 * Class JasperReportException
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class JasperReportException extends Exception
{

    /**
     * @var array
     */
    protected $errors = array();


    public function __construct($message)
    {
        parent::__construct($message, 0, null);
    }


    /**
     * @param array $errors
     */
    public function setErrors(array $errors)
    {
        $this->errors = $errors;
    }


    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

}