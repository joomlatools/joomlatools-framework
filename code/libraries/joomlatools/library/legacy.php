<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */


/**
 * PHP5.4 compatibility
 *
 * @link http://nikic.github.io/2012/01/28/htmlspecialchars-improvements-in-PHP-5-4.html
 */
if (!defined('ENT_SUBSTITUTE')) {
    define('ENT_SUBSTITUTE', ENT_IGNORE); //PHP 5.3 behavior
}

/**
 * mbstring compatibility
 *
 * @link http://php.net/manual/en/book.mbstring.php
 */

if (!function_exists('mb_strlen'))
{
    function mb_strlen($str)
    {
        return strlen(utf8_decode($str));
    }
}

if (!function_exists('mb_substr'))
{
    /*
     * Joomla checks if mb_substr exists to determine the availability of mbstring extension
     * Loading JString before providing the replacement function makes sure everything works
     */
    if (class_exists('JLoader') && is_callable(array('JLoader', 'import')))
    {
        JLoader::import('joomla.string.string');
        JLoader::load('JString');
    }

    function mb_substr($str, $offset, $length = NULL)
    {
        // generates E_NOTICE
        // for PHP4 objects, but not PHP5 objects
        $str = (string)$str;
        $offset = (int)$offset;
        if (!is_null($length)) $length = (int)$length;

        // handle trivial cases
        if ($length === 0) return '';
        if ($offset < 0 && $length < 0 && $length < $offset)
            return '';

        // normalise negative offsets (we could use a tail
        // anchored pattern, but they are horribly slow!)
        if ($offset < 0) {

            // see notes
            $strlen = strlen(utf8_decode($str));
            $offset = $strlen + $offset;
            if ($offset < 0) $offset = 0;

        }

        $Op = '';
        $Lp = '';

        // establish a pattern for offset, a
        // non-captured group equal in length to offset
        if ($offset > 0) {

            $Ox = (int)($offset/65535);
            $Oy = $offset%65535;

            if ($Ox) {
                $Op = '(?:.{65535}){'.$Ox.'}';
            }

            $Op = '^(?:'.$Op.'.{'.$Oy.'})';

        } else {

            // offset == 0; just anchor the pattern
            $Op = '^';

        }

        // establish a pattern for length
        if (is_null($length)) {

            // the rest of the string
            $Lp = '(.*)$';

        } else {

            if (!isset($strlen)) {
                // see notes
                $strlen = strlen(utf8_decode($str));
            }

            // another trivial case
            if ($offset > $strlen) return '';

            if ($length > 0) {

                // reduce any length that would
                // go passed the end of the string
                $length = min($strlen-$offset, $length);

                $Lx = (int)( $length / 65535 );
                $Ly = $length % 65535;

                // negative length requires a captured group
                // of length characters
                if ($Lx) $Lp = '(?:.{65535}){'.$Lx.'}';
                $Lp = '('.$Lp.'.{'.$Ly.'})';

            } else if ($length < 0) {

                if ( $length < ($offset - $strlen) ) {
                    return '';
                }

                $Lx = (int)((-$length)/65535);
                $Ly = (-$length)%65535;

                // negative length requires ... capture everything
                // except a group of  -length characters
                // anchored at the tail-end of the string
                if ($Lx) $Lp = '(?:.{65535}){'.$Lx.'}';
                $Lp = '(.*)(?:'.$Lp.'.{'.$Ly.'})$';

            }

        }

        if (!preg_match( '#'.$Op.$Lp.'#us',$str, $match )) {
            return '';
        }

        return $match[1];

    }
}

/**
 * uri template support compatibility
 *
 * @link https://tools.ietf.org/html/rfc6570
 * @link https://pecl.php.net/package/uri_template
 *
 * Based on https://github.com/seebz/uri-template/blob/master/src/functions.php
 */
if(!function_exists('uri_template'))
{
    function uri_template($template, array $variables = array())
    {
        // Expression replacement
        $expr_callback = function ($match) use ($variables)
        {
            list(, $operator, $variable_list) = $match;

            $separators = array(
                ''  => ',',
                '+' => ',',
                '#' => ',',
                '.' => '.',
                '/' => '/',
                ';' => ';',
                '?' => '&',
                '&' => '&',
            );
            $separator = $separators[$operator];

            $prefixes = array(
                ''  => '',
                '+' => '',
                '#' => '#',
                '.' => '.',
                '/' => '/',
                ';' => ';',
                '?' => '?',
                '&' => '&',
            );
            $prefix = $prefixes[$operator];


            // Callbacks
            $encode = function($value) use ($operator)
            {
                $value = rawurlencode($value);
                $value = str_replace('+', '%20', $value);

                if ($operator == '+' or $operator == '#')
                {
                    // Reserved chars are now allowed
                    $reserved = array(
                        ':' => '%3A',
                        '/' => '%2F',
                        '?' => '%3F',
                        '#' => '%23',
                        '[' => '%5B',
                        ']' => '%5D',
                        '@' => '%40',
                        '!' => '%21',
                        '$' => '%24',
                        '&' => '%26',
                        "'" => '%27',
                        '(' => '%28',
                        ')' => '%29',
                        '*' => '%2A',
                        '+' => '%2B',
                        ',' => '%2C',
                        ';' => '%3B',
                        '=' => '%3D',
                    );
                    $value = str_replace(
                        $reserved,
                        array_keys($reserved),
                        $value
                    );

                    // pct-encoded chars are allowed
                    $value = preg_replace('`%25([0-9]{2})`', '%\\1', $value);
                }

                return $value;
            };

            $add_key = function ($key, $value) use ($operator)
            {
                if (empty($value) and $operator == ';')
                {
                    $value = $key;
                }
                elseif ($operator == ';' or $operator == '?' or $operator == '&')
                {
                    $value = $key . '=' . $value;
                }

                return $value;
            };

            // Scalar values
            $format_scalars = function ($key, $value, $modifier = null, $modifier_option = null)
            use ($encode, $add_key)
            {
                if ($modifier == ':' and $modifier_option)
                {
                    $value = substr($value, 0, $modifier_option);
                }

                $value = $encode($value);
                $value = $add_key($key, $value);

                return $value;
            };

            // List-type array
            $format_lists = function ($key, $value, $modifier = null)
            use ($separator, $encode, $add_key)
            {
                if ($modifier == '*')
                {
                    foreach($value as $k => $v)
                    {
                        $v = $encode($v);
                        $v = $add_key($key, $v);
                        $value[$k] = $v;
                    }
                    $value = implode($separator, $value);
                }
                else
                {
                    $value = array_map($encode, $value);
                    $value = implode(',', $value);
                    $value = $add_key($key, $value);
                }

                return $value;
            };

            // Key-type array
            $format_keys = function ($key, $value, $modifier = null, $modifier_option = null)
            use ($operator, $separator, $encode, $add_key)
            {
                if ($modifier == '*')
                {
                    foreach($value as $k => $v)
                    {
                        $v = $k . '=' . $encode($v);
                        $value[$k] = $v;
                    }
                    $value = implode($separator, $value);
                }
                else
                {
                    foreach($value as $k => $v)
                    {
                        $v = $k . ',' . $encode($v);
                        $value[$k] = $v;
                    }
                    $value = implode(',', $value);
                    $value = $add_key($key, $value);
                }

                return $value;
            };


            // The loop
            foreach(explode(',', $variable_list) as $variable_key)
            {
                preg_match('`^([^:\*]+)(:([1-9][0-9]*)|\*)?$`', $variable_key, $m);
                $key = $m[1];
                $modifier        = count($m) > 2 ? $m[2][0] : null;
                $modifier_option = count($m) > 3 ? $m[3] : null;

                if (isset($variables[$key]))
                {
                    $value = $variables[$key];

                    if (is_scalar($value))
                    {
                        $format_func = $format_scalars;
                    }
                    elseif (empty($value))
                    {
                        continue;
                    }
                    elseif (array_values($value) === $value)
                    {
                        $format_func = $format_lists;
                    }
                    else
                    {
                        $format_func = $format_keys;
                    }
                    $founds[] = $format_func($key, $value, $modifier, $modifier_option);
                }
            }

            return empty($founds) ? '' : $prefix . implode($separator, $founds);
        };

        $expr_pattern = '`\{'
            . '(&|\?|;|/|\.|#|\+|)' // operator
            . '([^\}]+)'            // variable_list
            . '\}`';

        return preg_replace_callback($expr_pattern, $expr_callback, $template);
    }
}

/**
 * is_countable polyfill
 *
 * @link https://www.php.net/manual/en/function.is-countable.php
 *
 * Based on https://github.com/Ayesh/is_countable-polyfill
 */
if (!function_exists('is_countable')) {
    /**
     * Verify that the content of a variable is an array or an object
     * implementing Countable
     *
     * @param mixed $var The value to check.
     * @return bool Returns TRUE if var is countable, FALSE otherwise.
     */
    function is_countable($var) {
        return is_array($var)
            || $var instanceof \Countable
            || $var instanceof \SimpleXMLElement
            || $var instanceof \ResourceBundle;
    }
}

/**
 * New string functions from PHP 8
 */
if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle) {
        return 0 === strncmp($haystack, $needle, strlen($needle));
    }
}

if (!function_exists('str_ends_with')) {
    function str_ends_with($haystack, $needle) {
        return '' === $needle || ('' !== $haystack && 0 === substr_compare($haystack, $needle, -strlen($needle)));
    }
}

if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle) {
        return '' === $needle || false !== strpos($haystack, $needle);
    }
}

