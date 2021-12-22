<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Form Template Filter
 *
 * Filter to handle form html elements
 *
 * For forms that use a get method this
 * filter adds the action url query params as hidden fields to comply with the html form standard.
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Template\Filter
 * @see         http://www.w3.org/TR/html401/interact/forms.html#h-17.13.3.4
 */
class KTemplateFilterForm extends KTemplateFilterAbstract
{
    /**
     * Handle form replacements
     *
     * @param string
     * @return $this
     */
    public function filter(&$text)
    {
        $this->_addAction($text);
        $this->_addQueryParameters($text);

        return $this;
    }
    /**
     * Add the action if left empty
     *
     * @param string $text Template text
     * @return $this
     */
    protected function _addAction(&$text)
    {
        // All: Add the action if left empty
        if (preg_match_all('#<\s*form[^>]+action=""#si', $text, $matches, PREG_SET_ORDER))
        {
            $action = $this->getTemplate()->route();

            foreach ($matches as $match)
            {
                $str = str_replace('action=""', 'action="' . $action . '"', $match[0]);
                $text = str_replace($match[0], $str, $text);
            }
        }

        return $this;
    }

    /**
     * Add query parameters as hidden fields to the GET forms
     *
     * @param string $text Template text
     * @return $this
     */
    protected function _addQueryParameters(&$text)
    {
        $matches = array();

        if (preg_match_all('#(<\s*form[^>]+action="[^"]*?\?(.*?)"[^>]*>)(.*?)</form>#si', $text, $matches))
        {
            foreach ($matches[1] as $key => $match)
            {
                // Only deal with GET forms.
                if (strpos($match, 'method="get"') !== false)
                {
                    $query = $matches[2][$key];

                    parse_str(str_replace('&amp;', '&', $query), $query);

                    $input = '';

                    foreach ($query as $name => $value)
                    {
                        if (is_array($value)) {
                            $name = $name . '[]';
                        }

                        if (strpos($matches[3][$key], 'name="' . $name . '"') !== false) {
                            continue;
                        }

                        $name =  $this->getTemplate()->escape($name);

                        if (is_array($value))
                        {
                            foreach ($value as $k => $v)
                            {
                                if (!is_scalar($v) || !is_numeric($k)) {
                                    continue;
                                }

                                $v = $this->getTemplate()->escape($v);

                                $input .= PHP_EOL.'<input type="hidden" name="'.$name.'" value="'.$v.'" />';
                            }
                        }
                        else {
                            $value  = $this->getTemplate()->escape($value);
                            $input .= PHP_EOL.'<input type="hidden" name="'.$name.'" value="'.$value.'" />';
                        }
                    }

                    $text = str_replace($matches[3][$key], $input.$matches[3][$key], $text);
                }

            }
        }

        return $this;
    }
}
