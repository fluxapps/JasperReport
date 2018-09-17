<?php

namespace srag\JasperReport;

use Exception;
use ilUtil;
use srag\DIC\DICTrait;

/**
 * Class JasperReport
 *
 * @package srag\JasperReport
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
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
	function __construct(/*string*/
		$template, /*string*/
		$output_name = 'myreport') {
		$this->template = $template;
		$this->setOutputName($output_name);
		$this->makeTempDir();
	}


	/**
	 *
	 */
	protected function makeTempDir()/*: void*/ {
		$tmpdir = ilUtil::ilTempnam();
		ilUtil::makeDir($tmpdir);
		$this->setTmpdir($tmpdir);
	}


	/**
	 * Build parameters passed to jasperstarter jar
	 *
	 * @return string
	 */
	protected function buildParameters()/*: string*/ {
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
	protected function quote(/*string*/
		$str)/*: string*/ {
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
	public function generateOutput()/*: string*/ {
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
					$exec .= ' -u ' . self::dic()->clientIni()->readVariable("db", "user");
					$exec .= ' -H ' . self::dic()->clientIni()->readVariable("db", "host");
					$exec .= ' -n ' . self::dic()->clientIni()->readVariable("db", "name");
					$exec .= ' -p ' . self::dic()->clientIni()->readVariable("db", "pass");
					//TODO: self::dic()->clientIni()->readVariable("db", "port");
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
	public function downloadFile(/*bool*/
		$exit_after = true)/*: void*/ {
		$output_file = $this->generateOutput();

		ilUtil::deliverFile($output_file . '.pdf', basename($output_file . '.pdf'), '', true, true, $exit_after);
	}


	/**
	 * Add a parameter
	 *
	 * @param string $key
	 * @param string $value
	 */
	public function addParameter(/*string*/
		$key, /*string*/
		$value)/*: void*/ {
		$this->parameters[$key] = $value;
	}


	/**
	 * @return string
	 */
	public function getOutputName()/*: string*/ {
		return $this->output_name;
	}


	/**
	 * @param string $output_name
	 */
	public function setOutputName(/*string*/
		$output_name)/*: void*/ {
		$this->output_name = $output_name;
	}


	/**
	 * @return string
	 */
	private function getRoot()/*: string*/ {
		return __DIR__ . "/..";
	}


	/**
	 * @return string
	 */
	private function getTmpdir()/*: string*/ {
		return $this->tmpdir;
	}


	/**
	 * @param string $tmpdir
	 */
	private function setTmpdir(/*string*/
		$tmpdir)/*: void*/ {
		$this->tmpdir = $tmpdir;
	}


	/**
	 * @return array
	 */
	public function getParameters()/*: array*/ {
		return $this->parameters;
	}


	/**
	 * @param array $parameters
	 */
	public function setParameters(array $parameters)/*: void*/ {
		$this->parameters = $parameters;
	}


	/**
	 * @return string
	 */
	public function getEncoding()/*: string*/ {
		return $this->encoding;
	}


	/**
	 * @param string $encoding
	 */
	public function setEncoding(/*string*/
		$encoding)/*: void*/ {
		$this->encoding = $encoding;
	}


	/**
	 * @return int
	 */
	public function getDataSource()/*: int*/ {
		return $this->data_source;
	}


	/**
	 * @param int $data_source
	 */
	public function setDataSource(/*int*/
		$data_source)/*: void*/ {
		$this->data_source = $data_source;
	}


	/**
	 * @return string
	 */
	public function getCsvFile()/*: string*/ {
		return $this->csv_file;
	}


	/**
	 * @param string $csv_file
	 */
	public function setCsvFile(/*string*/
		$csv_file)/*: void*/ {
		$this->csv_file = $csv_file;
	}


	/**
	 * @return string
	 */
	public function getCsvCharset()/*: string*/ {
		return $this->csv_charset;
	}


	/**
	 * @param string $csv_charset
	 */
	public function setCsvCharset(/*string*/
		$csv_charset)/*: void*/ {
		$this->csv_charset = $csv_charset;
	}


	/**
	 * @return array
	 */
	public function getCsvColumns()/*: string*/ {
		return $this->csv_columns;
	}


	/**
	 * @param array $csv_columns
	 */
	public function setCsvColumns(/*string*/
		$csv_columns)/*: void*/ {
		$this->csv_columns = $csv_columns;
	}


	/**
	 * @return string
	 */
	public function getCsvFieldDelimiter()/*: string*/ {
		return $this->csv_field_delimiter;
	}


	/**
	 * @param string $csv_field_delimiter
	 */
	public function setCsvFieldDelimiter(/*string*/
		$csv_field_delimiter)/*: void*/ {
		$this->csv_field_delimiter = $csv_field_delimiter;
	}


	/**
	 * @return boolean
	 */
	public function getCsvFirstRow()/*: bool*/ {
		return $this->csv_first_row;
	}


	/**
	 * @param boolean $csv_first_row
	 */
	public function setCsvFirstRow(/*string*/
		$csv_first_row)/*: void*/ {
		$this->csv_first_row = $csv_first_row;
	}


	/**
	 * @return string
	 */
	public function getCsvRecordDelimiter()/*: string*/ {
		return $this->csv_record_delimiter;
	}


	/**
	 * @param string $csv_record_delimiter
	 */
	public function setCsvRecordDelimiter(/*string*/
		$csv_record_delimiter)/*: void*/ {
		$this->csv_record_delimiter = $csv_record_delimiter;
	}


	/**
	 * @return string
	 */
	public function getPathJava()/*: string*/ {
		return $this->path_java;
	}


	/**
	 * @param string $path_java
	 */
	public function setPathJava(/*string*/
		$path_java)/*: void*/ {
		$this->path_java = $path_java;
	}


	/**
	 * @return string
	 */
	public function getLocale()/*: string*/ {
		return $this->locale;
	}


	/**
	 * @param string $locale
	 */
	public function setLocale(/*string*/
		$locale)/*: void*/ {
		$this->locale = $locale;
	}


	/**
	 * @return string
	 */
	public function getOutputMode()/*: string*/ {
		return $this->output_mode;
	}


	/**
	 * @param string $output_mode
	 */
	public function setOutputMode(/*string*/
		$output_mode)/*: void*/ {
		$this->output_mode = $output_mode;
	}


	/**
	 * @return string
	 */
	public function getOutputFile()/*: string*/ {
		return $this->output_file;
	}
}
