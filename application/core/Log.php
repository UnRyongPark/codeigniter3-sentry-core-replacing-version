<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2016, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package    CodeIgniter
 * @author    EllisLab Dev Team
 * @copyright    Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright    Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license    http://opensource.org/licenses/MIT   MIT License
 * @link    https://codeigniter.com
 * @since    Version 1.0.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Logging Class
 *
 * @package        CodeIgniter
 * @subpackage    Libraries
 * @category    Logging
 * @author        EllisLab Dev Team
 * @link        https://codeigniter.com/user_guide/general/errors.html
 */
class CI_Log
{

    /**
     * Path to save log files
     *
     * @var string
     */
    protected $_log_path;

    /**
     * File permissions
     *
     * @var    int
     */
    protected $_file_permissions = 0644;

    /**
     * Level of logging
     *
     * @var int
     */
    protected $_threshold = 1;

    /**
     * Array of threshold levels to log
     *
     * @var array
     */
    protected $_threshold_array = array();

    /**
     * Format of timestamp for log files
     *
     * @var string
     */
    protected $_date_fmt = 'Y-m-d H:i:s';

    /**
     * Filename extension
     *
     * @var    string
     */
    protected $_file_ext;

    /**
     * Whether or not the logger can write to the log files
     *
     * @var bool
     */
    protected $_enabled = true;

    /**
     * Predefined logging levels
     *
     * @var array
     */
    protected $_levels = array('ERROR' => 1, 'DEBUG' => 2, 'INFO' => 3, 'ALL' => 4);

    /**
     * mbstring.func_override flag
     *
     * @var    bool
     */
    protected static $func_override;

    /**
     * Sentry Options
     */
    protected $_sentry_options = false;
    protected $_sentry_client = false;

    // --------------------------------------------------------------------

    /**
     * Class constructor
     *
     * @return    void
     */
    public function __construct()
    {
        $config =& get_config();

        isset(self::$func_override) OR self::$func_override = (extension_loaded('mbstring') && ini_get('mbstring.func_override'));

        $this->_log_path = ($config['log_path'] !== '') ? $config['log_path'] : APPPATH . 'logs/';
        $this->_file_ext = (isset($config['log_file_extension']) && $config['log_file_extension'] !== '')
            ? ltrim($config['log_file_extension'], '.') : 'php';

        file_exists($this->_log_path) OR mkdir($this->_log_path, 0755, true);

        if (!is_dir($this->_log_path) OR !is_really_writable($this->_log_path)) {
            $this->_enabled = false;
        }

        if (is_numeric($config['log_threshold'])) {
            $this->_threshold = (int)$config['log_threshold'];
        } elseif (is_array($config['log_threshold'])) {
            $this->_threshold = 0;
            $this->_threshold_array = array_flip($config['log_threshold']);
        }

        if (!empty($config['log_date_format'])) {
            $this->_date_fmt = $config['log_date_format'];
        }

        if (!empty($config['log_file_permissions']) && is_int($config['log_file_permissions'])) {
            $this->_file_permissions = $config['log_file_permissions'];
        }
        if ($this->_log_path === 'sentry') {
            try {
                // If Raven_Client isn't already defined, include the autoloader
                if (!class_exists('Raven_Client')) {
                    require_once $config['sentry_path'];
                    Raven_Autoloader::register();
                }

                if (empty($config['sentry_config'])) {
                    $this->_sentry_client = new Raven_Client($config['sentry_client']);
                } else {
                    $this->_sentry_client = new Raven_Client($config['sentry_client'], $config['sentry_config']);
                }

                $error_handler = new Raven_ErrorHandler($this->_sentry_client);
                $error_handler->registerExceptionHandler();
                $error_handler->registerErrorHandler();
                $error_handler->registerShutdownFunction();

                $this->_sentry_options = array(
                    'sentry_log_threshold' => $config['sentry_log_threshold'],
                    'sentry_logging_levels' => $config['sentry_logging_levels'],
                    'sentry_logging_level_names' => array_flip($config['sentry_logging_levels'])
                );
            } catch (Exception $e) {
                $this->_sentry_client = false;
                $this->_sentry_options = false;

                // Do nothing, since we don't want to stop loading of the site due
                // to a Sentry miss configuration or error.
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Write Log File
     *
     * Generally this function will be called using the global log_message() function
     *
     * @param    string $level The error level: 'error', 'debug' or 'info'
     * @param    string $msg The error message
     * @return    bool
     */
    public function write_log($level, $msg)
    {
        // Check Log path
        if ($this->_log_path === 'sentry' && $this->_sentry_client !== false && $this->_sentry_options !== false) {
            if ($this->_enabled === false) {
                return false;
            }
            // make upper case
            $level_upper = strtoupper($level);

            // check logging level
            if (in_array($level_upper,
                    $this->_sentry_options['sentry_logging_levels']) === false OR $this->_sentry_options['sentry_logging_level_names'][$level_upper] < $this->_sentry_options['sentry_logging_level_names'][$this->_sentry_options['sentry_log_threshold']]
            ) {
                return false;
            } else {
                $this->_sentry_client->captureMessage($msg, array(), $level, true);
            }
        } else {
            if ($this->_enabled === false) {
                return false;
            }

            $level = strtoupper($level);

            if ((!isset($this->_levels[$level]) OR ($this->_levels[$level] > $this->_threshold))
                && !isset($this->_threshold_array[$this->_levels[$level]])
            ) {
                return false;
            }

            $filepath = $this->_log_path . 'log-' . date('Y-m-d') . '.' . $this->_file_ext;
            $message = '';

            if (!file_exists($filepath)) {
                $newfile = true;
                // Only add protection to php files
                if ($this->_file_ext === 'php') {
                    $message .= "<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>\n\n";
                }
            }

            if (!$fp = @fopen($filepath, 'ab')) {
                return false;
            }

            flock($fp, LOCK_EX);

            // Instantiating DateTime with microseconds appended to initial date is needed for proper support of this format
            if (strpos($this->_date_fmt, 'u') !== false) {
                $microtime_full = microtime(true);
                $microtime_short = sprintf("%06d", ($microtime_full - floor($microtime_full)) * 1000000);
                $date = new DateTime(date('Y-m-d H:i:s.' . $microtime_short, $microtime_full));
                $date = $date->format($this->_date_fmt);
            } else {
                $date = date($this->_date_fmt);
            }

            $message .= $this->_format_line($level, $date, $msg);

            for ($written = 0, $length = self::strlen($message); $written < $length; $written += $result) {
                if (($result = fwrite($fp, self::substr($message, $written))) === false) {
                    break;
                }
            }

            flock($fp, LOCK_UN);
            fclose($fp);

            if (isset($newfile) && $newfile === true) {
                chmod($filepath, $this->_file_permissions);
            }

            return is_int($result);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Format the log line.
     *
     * This is for extensibility of log formatting
     * If you want to change the log format, extend the CI_Log class and override this method
     *
     * @param    string $level The error level
     * @param    string $date Formatted date string
     * @param    string $message The log message
     * @return    string    Formatted log line with a new line character '\n' at the end
     */
    protected function _format_line($level, $date, $message)
    {
        return $level . ' - ' . $date . ' --> ' . $message . "\n";
    }

    // --------------------------------------------------------------------

    /**
     * Byte-safe strlen()
     *
     * @param    string $str
     * @return    int
     */
    protected static function strlen($str)
    {
        return (self::$func_override)
            ? mb_strlen($str, '8bit')
            : strlen($str);
    }

    // --------------------------------------------------------------------

    /**
     * Byte-safe substr()
     *
     * @param    string $str
     * @param    int $start
     * @param    int $length
     * @return    string
     */
    protected static function substr($str, $start, $length = null)
    {
        if (self::$func_override) {
            // mb_substr($str, $start, null, '8bit') returns an empty
            // string on PHP 5.3
            isset($length) OR $length = ($start >= 0 ? self::strlen($str) - $start : -$start);
            return mb_substr($str, $start, $length, '8bit');
        }

        return isset($length)
            ? substr($str, $start, $length)
            : substr($str, $start);
    }
}
