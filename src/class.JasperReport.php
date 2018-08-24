<?php

namespace srag\JasperReport;

use Exception;
use ilUtil;
use srag\DIC\DICTrait;

/**
 * class JasperReport
 *
 * @package srag\JasperReport
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 */
class JasperReport {

	use DICTrait;
	const DATASOURCE_EMPTY = 0;
	const DATASOURCE_DB = 1;
	const DATASOURCE_CSV = 2;
	/**
	 * Choose a Locale from your unix-system: locale -a
	 * The Locale should support all characters you need, such as German-Umlauts.
	 *
	 * @var string
	 */
	protected $locale = 'de_DE.UTF-8';
	/**
	 * The filename of the output file that is generated
	 *
	 * @var string
	 */
	protected $output_name = '';
	/**
	 * Corresponds to parameter "-f" of jasperstarter
	 *
	 * @see http://jasperstarter.cenote.de/usage.html
	 *
	 * @var string
	 */
	protected $output_mode = 'pdf';
	/**
	 * @var string
	 */
	protected $tmpdir = '';
	/**
	 * @var array
	 */
	protected $parameters = array();
	/**
	 * @var string
	 */
	protected $template = '';
	/**
	 * @var string
	 */
	protected $encoding = 'UTF-8';
	/**
	 * @var int
	 */
	protected $data_source = self::DATASOURCE_DB;
	/**
	 * Path to the CSV file that is used as source if mode = CSV
	 *
	 * @var string
	 */
	protected $csv_file = '';
	/**
	 * Separator for fields if mode = CSV
	 *
	 * @var string
	 */
	protected $csv_field_delimiter = ',';
	/**
	 * True if first row of csv file describes columns/variables
	 *
	 * @var bool
	 */
	protected $csv_first_row = true;
	/**
	 * Columns of csv, if not taken from first row
	 *
	 * @var array
	 */
	protected $csv_columns = array();
	/**
	 * @var string
	 */
	protected $csv_charset = 'UTF-8';
	/**
	 * @var string
	 */
	protected $csv_record_delimiter = '\n';
	/**
	 * @var string
	 */
	protected $path_java = '/usr/bin/java';
	/**
	 * @var string
	 */
	protected $output_file = '';
	/**
	 * @var bool
	 */
	protected $generated = false;


	/**
	 * JasperReport constructor
	 *
	 * @param string $template    Path and filename of the xml template for the report
	 * @param string $output_name Filename of the generated pdf
	 */
	function __construct($template, $output_name = 'myreport') {
		$this->template = $template;
		$this->setOutputName($output_name);
		$this->makeTempDir();
	}


	/**
	 *
	 */
	protected function makeTempDir() {
		$tmpdir = ilUtil::ilTempnam();
		ilUtil::makeDir($tmpdir);
		$this->setTmpdir($tmpdir);
	}


	/**
	 * Build parameters passed to jasperstarter jar
	 *
	 * @return string
	 */
	protected function buildParameters() {
		$return = '';
		if (count($this->parameters)) {
			$return = ' -P ';
			foreach ($this->parameters as $k => $v) {
				$return .= ' ' . $k . '=' . $this->quote($v);
			}
		}

		return $return;
	}


	/**
	 * @param string $str
	 *
	 * @return string
	 */
	protected function quote($str) {
		return '"' . str_replace('"', '\"', $str) . '"';
	}


	/**
	 * Delete temp directory
	 */
	public function __destruct() {
		ilUtil::delDir($this->getTmpdir());
	}


	/**
	 * Tries to generate the file file with JasperReport.
	 * On success, this method returns the path to the generated file (inside a temporary ILIAS directory)
	 * On failure, an JapserReportException is thrown, containing the errors reported back from jasperstarter as array
	 *
	 * @return string
	 * @throws Exception
	 */
	public function generateOutput() {
		if (!$this->generated) {
			$this->output_file = $this->getTmpdir() . DIRECTORY_SEPARATOR . $this->getOutputName();
			// Build Execution Statement
			$exec = 'export LC_ALL="' . $this->getLocale() . '"; ';
			$exec .= $this->getPathJava();
			$exec .= ' -jar ' . $this->getRoot() . '/../../rdpascua/jasperstarter/lib/jasperstarter.jar pr';
			$exec .= ' ' . $this->template;
			$exec .= ' -f ' . $this->getOutputMode() . ' ';
			$exec .= ' -o ' . $this->getOutputFile();
			$exec .= $this->buildParameters();
			// Add Options depending on Datasource (DB/CSV/NONE)
			switch ($this->getDataSource()) {
				case self::DATASOURCE_DB:
					$exec .= ' -t ' . self::dic()->database()->getDBType();
					$exec .= ' -u ' . self::dic()->database()->getDBUser();
					$exec .= ' -H ' . self::dic()->database()->getDBHost();
					$exec .= ' -n ' . self::dic()->database()->getDBName();
					$exec .= ' -p ' . self::dic()->database()->getDBPassword();
					break;
				case self::DATASOURCE_CSV:
					$exec .= ' -t csv --data-file ' . $this->getCsvFile();
					$exec .= ' --csv-field-del=' . $this->quote($this->getCsvFieldDelimiter());
					$exec .= ' --csv-record-del=' . $this->quote($this->getCsvRecordDelimiter());
					$exec .= ' --csv-charset=' . $this->getCsvCharset();
					if ($this->getCsvFirstRow()) {
						$exec .= ' --csv-first-row';
					} else {
						if (count($this->getCsvColumns())) {
							$exec .= ' --csv-columns ' . implode(',', $this->getCsvColumns());
						}
					}
					break;
			}
			// Redirect stderr to stdout because PHP's exec() only returns stdout
			// Note: If Jasperstarter one day returns anything on the stdout, we must use another function, e.g. proc_open()
			$exec .= ' 2>&1';
			$errors = array();
			exec($exec, $errors);
			if (count($errors)) {
				$exception = new JasperReportException("Jasperstarter failed to generate output filed: '" . implode("', '", $errors) . "'");
				$exception->setErrors($errors);
				throw $exception;
			}

			$this->generated = true;
		}

		return $this->getOutputFile();
	}


	/**
	 * @param bool $exit_after
	 */
	public function downloadFile($exit_after = true) {
		$output_file = $this->generateOutput();

		ilUtil::deliverFile($output_file . '.pdf', basename($output_file . '.pdf'), '', true, true, $exit_after);
	}


	/**
	 * Add a parameter
	 *
	 * @param $key
	 * @param $value
	 */
	public function addParameter($key, $value) {
		$this->parameters[$key] = $value;
	}


	/**
	 * @param string $output_name
	 */
	public function setOutputName($output_name) {
		$this->output_name = $output_name;
	}


	/**
	 * @return string
	 */
	public function getOutputName() {
		return $this->output_name;
	}


	/**
	 * @return string
	 */
	private function getRoot() {
		return __DIR__ . "/..";
	}


	/**
	 * @param string $tmpdir
	 */
	private function setTmpdir($tmpdir) {
		$this->tmpdir = $tmpdir;
	}


	/**
	 * @return string
	 */
	private function getTmpdir() {
		return $this->tmpdir;
	}


	/**
	 * @param array $parameters
	 */
	public function setParameters($parameters) {
		$this->parameters = $parameters;
	}


	/**
	 * @return array
	 */
	public function getParameters() {
		return $this->parameters;
	}


	/**
	 * @param string $encoding
	 */
	public function setEncoding($encoding) {
		$this->encoding = $encoding;
	}


	/**
	 * @return string
	 */
	public function getEncoding() {
		return $this->encoding;
	}


	/**
	 * @param int $data_source
	 */
	public function setDataSource($data_source) {
		$this->data_source = $data_source;
	}


	/**
	 * @return int
	 */
	public function getDataSource() {
		return $this->data_source;
	}


	/**
	 * @param string $csv_file
	 */
	public function setCsvFile($csv_file) {
		$this->csv_file = $csv_file;
	}


	/**
	 * @return string
	 */
	public function getCsvFile() {
		return $this->csv_file;
	}


	/**
	 * @param string $csv_charset
	 */
	public function setCsvCharset($csv_charset) {
		$this->csv_charset = $csv_charset;
	}


	/**
	 * @return string
	 */
	public function getCsvCharset() {
		return $this->csv_charset;
	}


	/**
	 * @param array $csv_columns
	 */
	public function setCsvColumns($csv_columns) {
		$this->csv_columns = $csv_columns;
	}


	/**
	 * @return array
	 */
	public function getCsvColumns() {
		return $this->csv_columns;
	}


	/**
	 * @param string $csv_field_delimiter
	 */
	public function setCsvFieldDelimiter($csv_field_delimiter) {
		$this->csv_field_delimiter = $csv_field_delimiter;
	}


	/**
	 * @return string
	 */
	public function getCsvFieldDelimiter() {
		return $this->csv_field_delimiter;
	}


	/**
	 * @param boolean $csv_first_row
	 */
	public function setCsvFirstRow($csv_first_row) {
		$this->csv_first_row = $csv_first_row;
	}


	/**
	 * @return boolean
	 */
	public function getCsvFirstRow() {
		return $this->csv_first_row;
	}


	/**
	 * @param string $csv_record_delimiter
	 */
	public function setCsvRecordDelimiter($csv_record_delimiter) {
		$this->csv_record_delimiter = $csv_record_delimiter;
	}


	/**
	 * @return string
	 */
	public function getCsvRecordDelimiter() {
		return $this->csv_record_delimiter;
	}


	/**
	 * @param string $path_java
	 */
	public function setPathJava($path_java) {
		$this->path_java = $path_java;
	}


	/**
	 * @return string
	 */
	public function getPathJava() {
		return $this->path_java;
	}


	/**
	 * @return string
	 */
	public function getLocale() {
		return $this->locale;
	}


	/**
	 * @param string $locale
	 */
	public function setLocale($locale) {
		$this->locale = $locale;
	}


	/**
	 * @return string
	 */
	public function getOutputMode() {
		return $this->output_mode;
	}


	/**
	 * @param string $output_mode
	 */
	public function setOutputMode($output_mode) {
		$this->output_mode = $output_mode;
	}


	/**
	 * @return string
	 */
	public function getOutputFile() {
		return $this->output_file;
	}
}
