<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Object Config Csv
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Object\Config
 */
class KObjectConfigCsv extends KObjectConfigFormat
{
    /**
     * The format
     *
     * @var string
     */
    protected static $_format = 'text/csv';

    /**
     * Character used for enclosing fields
     *
     * @var string
     */
    public $enclosure = '"';

    /**
     * Character used for separating fields
     *
     * @var string
     */
    public $delimiter = ',';

    /**
     * The escape character
     *
     * @var string
     */
    public $escape = "\\" ;

    /**
     * Read from a CSV string and create a config object
     *
     * @param  string $string
     * @param  bool    $object  If TRUE return a ConfigObject, if FALSE return an array. Default TRUE.
     * @return KObjectConfigCsv|array
     */
    public function fromString($string, $object = true)
    {
        $data = str_getcsv($string, $this->delimiter, $this->enclosure, $this->escape);

        return $object ? $this->merge($data) : $data;
    }

    /**
     * Write a config object to a CSV string.
     *
     * @return string|false     Returns a CSV encoded string on success. False on failure.
     */
    public function toString()
    {
        $data = $this->toArray();

        return str_putcsv($data, $this->delimiter, $this->enclosure);
    }
}

if (!function_exists('str_putcsv'))
{
    function str_putcsv($input, $delimiter = ',', $enclosure = '"')
    {
        $fp = fopen('php://temp', 'r+b');
        fputcsv($fp, $input, $delimiter, $enclosure);
        rewind($fp);
        $data = rtrim(stream_get_contents($fp), "\n");
        fclose($fp);

        return $data;
    }
}