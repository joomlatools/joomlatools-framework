<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * Path Filter
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ComFilesFilterPath extends KFilterAbstract implements KFilterTraversable
{
    protected static $_safepath_pattern = array('#(\.){2,}/#', '#^\.#');

    protected static $_special_chars = array(
        "?", "[", "]", "\\", "=", "<", ">", ":", ";", "'", "\"",
        "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}"
    );

    /**
     * Normalize the path against different encodings in the filesystem
     * @param $path
     * @return mixed
     */
    public static function normalizePath($path)
    {
        $replacement = [
            '#a\x{0300}#u' => "à",
            '#a\x{0301}#u' => "á",
            '#a\x{0302}#u' => "â",
            '#a\x{0308}#u' => "ä",
            '#e\x{0300}#u' => "è",
            '#e\x{0301}#u' => "é",
            '#e\x{0302}#u' => "ê",
            '#e\x{0308}#u' => "ë",
            '#i\x{0300}#u' => "ì",
            '#i\x{0301}#u' => "í",
            '#i\x{0302}#u' => "î",
            '#i\x{0308}#u' => "ï",
            '#o\x{0300}#u' => "ò",
            '#o\x{0301}#u' => "ó",
            '#o\x{0302}#u' => 'ô',
            '#o\x{0308}#u' => 'ö',
            '#u\x{0300}#u' => "ù",
            '#u\x{0301}#u' => 'ú',
            '#u\x{0302}#u' => "û",
            '#u\x{0308}#u' => "ü",
            '#A\x{0300}#u' => "À",
            '#A\x{0301}#u' => "Á",
            '#A\x{0302}#u' => "Â",
            '#A\x{0308}#u' => "Ä",
            '#E\x{0300}#u' => "È",
            '#E\x{0301}#u' => "É",
            '#E\x{0302}#u' => "Ê",
            '#E\x{0308}#u' => "Ë",
            '#I\x{0300}#u' => "Ì",
            '#I\x{0301}#u' => "Í",
            '#I\x{0302}#u' => "Î",
            '#I\x{0308}#u' => "Ï",
            '#O\x{0300}#u' => "Ò",
            '#O\x{0301}#u' => "Ó",
            '#O\x{0302}#u' => "Ô",
            '#O\x{0308}#u' => "Ö",
            '#U\x{0300}#u' => "Ù",
            '#U\x{0301}#u' => "Ú",
            '#U\x{0302}#u' => "Û",
            '#U\x{0308}#u' => "Ü"
        ];

        $path = preg_replace(array_keys($replacement), array_values($replacement), $path);

        return $path;
    }

    /**
     * Validate a value
     *
     * @param	mixed	$value Value to be validated
     * @return	bool	True when the variable is valid
     */
    public function validate($value)
    {
        $value = trim(str_replace('\\', '/', $value));
        $sanitized = $this->sanitize($value);
        return (is_string($value) && $sanitized == $value);
    }

    /**
     * Sanitize a value
     *
     * @param	mixed	$value Value to be sanitized
     * @return	string
     */
    public function sanitize($value)
    {
        $value = trim(str_replace('\\', '/', $value));
        $value = preg_replace(self::$_safepath_pattern, '', $value);

        return $value;

        $value = str_replace(self::$_special_chars, '', $value);

        return $value;
    }

    /**
     * Encode a value
     *
     * @param mixed $value Value to be encoded
     * @return string
     */
    public function encode($value)
    {
        $value = $this->sanitize($value);

        $parts = explode('/', $value);

        foreach ($parts as &$part) {
            $part = rawurlencode($part);
        }

        return implode('/', $parts);
    }
}
