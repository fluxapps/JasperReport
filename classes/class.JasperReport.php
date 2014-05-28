<?php
require_once('./Services/Component/classes/class.ilComponent.php');

/**
 * ilJasperReport
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
class JasperReport {

	const TYPE_PDF = 'pdf';
	const TYPE_HTML = 'html';
	const VERSION = '2.0.0';
	const DEBUG = false;

    const DATASOURCE_EMPTY = 0;
    const DATASOURCE_DB = 1;

    /**
	 * Choose a Locale from your unix-system: locale -a
	 * The Locale should support all characters you need, such as German-Umlauts.
	 */
	const LOCALE = 'de_DE.UTF-8';

	/** @var string  */
    protected $output_name = '';

	/** @var string  */
    protected $response = '';

    /** @var string  */
    private $root = '';

	/** @var string  */
	private $tmpdir = '';

    /** @var array  */
    protected $parameters = array();

    /** @var string  */
    protected $output_file = '';

    /** @var string  */
    protected $template = '';

	/** @var string  */
    protected $notification = '';

	/** @var string  */
    protected $encoding = 'UTF-8';

    /** @var bool  */
    protected $generated = false;

    protected $data_source = self::DATASOURCE_DB;

	/**
	 * @param string $template Path and filename of the xml template for the report
	 * @param string $output_name Filename of the generated pdf
	 */
	function __construct($template, $output_name = 'myreport') {
		global $ilDB, $ilUser, $ilCtrl, $ilLog;
		/**
		 * @var $ilDB   ilDB
		 * @var $ilUser ilObjUser
		 * @var $ilCtrl ilCtrl
		 * @var $ilLog  ilLog
		 */
		$this->db = $ilDB;
		$this->log = $ilLog;
		$this->user = $ilUser;
		$this->ctrl = $ilCtrl;
		$this->template = $template;
		$this->setOutputName($output_name);
		//Temporary Directory for saving the report.
		$tmpdir = ilUtil::ilTempnam();
        ilUtil::makeDir($tmpdir);
		$this->setTmpdir($tmpdir);
		$this->setRoot(substr(__FILE__, 0, strpos(__FILE__, 'classes/' . basename(__FILE__))));
		if (self::DEBUG) {
			$this->log->write('ilJasperReport::__construct finished');
		}
	}


	function __destruct() {
		//Delete Temporary Directory
		ilUtil::delDir($this->getTmpdir());
	}

    /**
     * Add a parameter
     * @param $key
     * @param $value
     */
    public function addParameter($key, $value) {
        $this->parameters[$key] = $value;
    }

	/**
	 * @return string
	 */
	public function generateOutput() {

		$this->setOutputFile($this->getTmpdir() . DIRECTORY_SEPARATOR . str_ireplace(' ', '_', $this->getOutputName()));
		// Build Execution Statement
		$exec  = 'export LC_ALL="' . self::LOCALE . '"; ';
		$exec .= '/usr/bin/java';
		$exec .= ' -jar ' . $this->getRoot() . 'lib/jasperstarter-' . self::VERSION . '/lib/jasperstarter.jar pr';
        $exec .= ' ' . $this->template;
        $exec .= ' -f pdf ';
		$exec .= ' -o ' . $this->getOutputFile();
		$exec .= $this->buildParameters();
		// Add DB Connection
		if ($this->getDataSource() == self::DATASOURCE_DB) {
            $exec .= ' -t ' . $this->db->getDBType();
            $exec .= ' -u ' . $this->db->getDBUser();
            $exec .= ' -H ' . $this->db->getDBHost();
            $exec .= ' -n ' . $this->db->getDBName();
            $exec .= ' -p ' . $this->db->getDBPassword();
        }
		// Execute
//		var_dump($exec); die();
        if (self::DEBUG) {
			$this->log->write('Jasperreport::generateOutput() exec: ' . $exec);
		}
		$re = array();
		exec($exec, $re);
//        var_dump($re);die();
		// Generate Messages
		if ($re) {
			if (self::DEBUG) {
				$this->log->write('Jasperreport::generateOutput() response: ' . implode(', ', $re));
			}
			$this->setResponse($re);
			if ($this->getNotification()) {
				ilUtil::sendFailure($this->getResponse());
			}

			return false;
		} else {
			if ($this->getNotification()) {
				ilUtil::sendInfo($this->getNotification(), true);
			}
			$this->generated = true;
		}

		return $this->getOutputFile();
	}


	/**
	 * @param bool $exit_after
	 */
	public function downloadFile($exit_after = true) {
        if (!$this->generated) $this->generateOutput();
		ilUtil::deliverFile($this->getOutputFile() . '.pdf', basename($this->getOutputFile()
		. '.pdf'), '', true, true, $exit_after);
	}

    /**
     * Build parameters passed to jasperstarter jar
     * @return string
     */
    private function buildParameters() {
        $return = '';
        if (self::DEBUG) {
            $this->log->write('::buildParameters started');
        }
        if (count($this->parameters)) {
            $return = ' -P ';
            foreach ($this->parameters as $k => $v) {
                $return .= ' ' . $k . '=' . $this->quote($v);
            }
        }
        if (self::DEBUG) {
            $this->log->write('ilJasperReport::buildParameters: ' . $return);
            $this->log->write('ilJasperReport::buildParameters finished');
        }
        return $return;
    }

    /**
     * @param $str
     * @return string
     */
    private function quote($str) {
        return '"' . str_replace('"', '\"', $str) . '"';
    }

    /**
     * Getter & Setter
     */

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
	 * @param string $response
	 */
	private function setResponse($response) {
		$this->response = $response;
	}


	/**
	 * @return string
	 */
	private function getResponse() {
		switch ($this->response) {
			case 127:
				$this->response = 'Jasper Starter V.' . self::VERSION . ' not found';
				break;
		}
		return $this->response;
	}


	/**
	 * @param string $root
	 */
	private function setRoot($root) {
		$this->root = $root;
	}


	/**
	 * @return string
	 */
	private function getRoot() {
		return $this->root;
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
	 * @param string $output_file
	 */
	public function setOutputFile($output_file) {
		$this->output_file = $output_file;
	}


	/**
	 * @return string
	 */
	public function getOutputFile() {
		return $this->output_file;
	}


	/**
	 * @param string $notification
	 */
	public function setNotification($notification) {
		$this->notification = $notification;
	}


	/**
	 * @return string
	 */
	public function getNotification() {
		return $this->notification;
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
    public function setDataSource($data_source)
    {
        $this->data_source = $data_source;
    }

    /**
     * @return int
     */
    public function getDataSource()
    {
        return $this->data_source;
    }


}

?>
