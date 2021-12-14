<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Object Config Json
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Object\Config
 */
class KObjectConfigJson extends KObjectConfigFormat
{
    /**
     * The format
     *
     * @var string
     */
    protected static $_media_type = 'application/json';

    /**
     * Read from a string and create an array
     *
     * @param  string $string
     * @param  bool    $object  If TRUE return a ConfigObject, if FALSE return an array. Default TRUE.
     * @throws DomainException  If the JSON cannot be decoded or if the encoded data is deeper than the recursion limit.
     * @return KObjectConfigJson|array
     */
    public function fromString($string, $object = true)
    {
        $data = array();

        if(!empty($string))
        {
            $data = json_decode($string, true);

            if (JSON_ERROR_NONE !== json_last_error())
            {
                throw new InvalidArgumentException(
                    'Cannot decode data from JSON string: ' . json_last_error_msg()
                );
            }
        }

        return $object ? $this->merge($data) : $data;
    }

    /**
     * Write a config object to a string.
     *
     * @return string|false    Returns a JSON encoded string on success. False on failure.
     * @throws DomainException Object could not be encoded to valid JSON.
     */
    public function toString()
    {
        $data = $this->toArray();

        // Root should be JSON object, not array
        if (count($data) === 0) {
            $data = new ArrayObject();
        }

        // Encode <, >, ', &, and " for RFC4627-compliant JSON, which may also be embedded into HTML.
        $data = json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION);

        if (JSON_ERROR_NONE !== json_last_error())
        {
            throw new InvalidArgumentException(
                'Cannot encode data to JSON string: ' . json_last_error_msg()
            );
        }

        return $data;
    }
}