<?php

namespace Plugins\Khanza\Jasper;

/**
 * Class JasperPHP
 * @category Class
 * @package  JasperPHP
 * @author   Davide Caruso <davide.caruso93@gmail.com>
 * @link     https://github.com/davidecaruso/jasper-php
 * @license  MIT
 */
class JasperPHP
{
    protected $executable;
    protected $command;
    protected $formats = [
        'pdf',
        'rtf',
        'xls',
        'xlsx',
        'docx',
        'odt',
        'ods',
        'pptx',
        'csv',
        'html',
        'xhtml',
        'xml',
        'jrprint'
    ];

    /**
     * JasperPHP constructor.
     *
     * @param $executable
     */
    public function __construct(?string $executable = null)
    {
        if (!is_null($executable) && is_file($executable)) {
            $this->executable = $executable;
        } else {
            $this->executable = __DIR__ . '/lib/jasperstarter/bin/jasperstarter';
        }
    }

    /**
     * Compile a jrxml file into a jasper file
     *
     * @param string      $input  Path of jrxml file (e.g. /path/to/input.jrxml).
     * @param null|string $output Path of jasper file (e.g. /path/to/otuput).
     *
     * @return \JasperPHP\JasperPHP
     */
    public function compile(string $input, ?string $output = null): JasperPHP
    {
        $this->command = "{$this->executable} compile \"{$input}\"" . ($output ? " -o \"{$output}\"" : '');
        return $this;
    }

    /**
     * Generate a Jasper Report.
     *
     * @param string      $input      Path of jasper file (e.g. /path/to/input.jasper).
     * @param null|string $output     Path of output file (e.g. /path/to/otuput).
     * @param array       $formats    Formats of output files
     *                                (e.g. pdf, rtf, xls, xlsx, docx, odt, ods, pptx, csv, html, xhtml, xml, jrprint).
     * @param array       $parameters Additional command parameter (key => value).
     * @param array       $connection Database, JSON connection.
     * @param null|string $password   Password string.
     *
     * @return \JasperPHP\JasperPHP
     */
    public function process(
        string $input,
        ?string $output = null,
        array $formats = [],
        array $parameters = [],
        array $connection = [],
        ?string $password = null
    ): JasperPHP {
        try {
            foreach ($formats as $format) {
                if (!in_array($format, $this->formats)) {
                    throw new \Exception('Invalid format: it should by one of ' . implode(',', $this->formats), 1);
                }
            }

            $this->command = "{$this->executable} process \"{$input}\"" . ($output ? " -o \"{$output}\"" : '');
            $this->command .= ' -f ' . implode(' ', $formats);

            if (count($parameters) > 0) {
                $this->command .= ' -P ';
                foreach ($parameters as $key => $value) {
                    $this->command .= " {$key}=\"{$value}\" ";
                }

            }

            if (!empty($connection)) {
                // Set connection driver
                $this->command .= " -t {$connection['driver']}";

                // Username
                if ($this->is($connection['username'])) {
                    $this->command .= " -u {$connection['username']}";
                }

                // Password
                if ($this->is($connection['password'])) {
                    $this->command .= " -p {$connection['password']}";
                }

                // Host
                if ($this->is($connection['host'])) {
                    $this->command .= " -H {$connection['host']}";
                }

                // Database
                if ($this->is($connection['database'])) {
                    $this->command .= " -n {$connection['database']}";
                }

                // Port
                if ($this->is($connection['port'])) {
                    $this->command .= " --db-port {$connection['port']}";
                }

                // JDBC driver
                if ($this->is($connection['jdbc_driver'])) {
                    $this->command .= " --db-driver {$connection['jdbc_driver']}";
                }

                // JDBC url
                if ($this->is($connection['jdbc_url'])) {
                    $this->command .= " --db-url {$connection['jdbc_url']}";
                }

                // JDBC directory
                if ($this->is($connection['jdbc_dir'])) {
                    $this->command .= " --jdbc-dir {$connection['jdbc_dir']}";
                }

                // Database sid
                if ($this->is($connection['db_sid'])) {
                    $this->command .= " --db-sid {$connection['db_sid']}";
                }

                // XML xpath
                if ($this->is($connection['xml_xpath'])) {
                    $this->command .= " --xml-xpath {$connection['xml_xpath']}";
                }

                // Data file
                if ($this->is($connection['data_file'])) {
                    $this->command .= " --data-file {$connection['data_file']}";
                }

                // JSON query
                if ($this->is($connection['json_query'])) {
                    $this->command .= " --json-query {$connection['json_query']}";
                }
            }

            // Password
            if (!is_null($password)) {
                $this->command .= " --password {$password}";
            }
        } catch (\Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
        }

        return $this;
    }

    /**
     * Return the command that is going to be executed.
     * @return mixed
     */
    public function output()
    {
        return $this->command;
    }


    /**
     * Execute Jasper Starter command.
     * @return array The otuput of the executed command
     */
    public function execute(): array
    {
        $output = [];
        try {
            $returnVar = 0;

            // Exec the command
            exec($this->command, $output, $returnVar);

            if (!is_null($returnVar) && $returnVar > 0) {
                throw new \Exception('There was an error, please check the command: ' . $this->command, $returnVar);
            }

        } catch (\Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
        }
        return $output;
    }

    /**
     * Return true if a variable is setted and filled.
     * @param $var mixed The variable.
     *
     * @return bool
     */
    private function is(&$var)
    {
        return isset($var) && !empty($var);
    }
}
