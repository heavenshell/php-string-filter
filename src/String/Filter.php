<?php
/**
 * String\Filter - a regexp-based string filter.
 *
 * The original module is String::Filter(Perl module)..
 *
 * PHP version 5.3
 *
 * Copyright (c) 2009-2010 Shinya Ohyanagi, All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Shinya Ohyanagi nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @uses      String
 * @category  String
 * @package   String\Filter
 * @version   $id$
 * @copyright 2009-2010 Shinya Ohyanagi
 * @author    Shinya Ohyanagi <sohyanagi@gmail.com>
 * @license   New BSD License
 * @link      http://search.cpan.org/~kazuho/String-Filter-0.01/
 */

namespace String;
use String;

/**
 * Filter
 *
 * <pre>
 *   The module is a regexp-based string filter,
 *   Original module is Perl's String::Filter.
 *   see @link.
 * </pre>
 *
 * @uses      String
 * @category  String
 * @package   String\Filter
 * @version   $id$
 * @copyright (C) 2010 Cybozu Labs, Inc.  Written by Kazuho Oku.
 * @copyright 2010 Shinya Ohyanagi
 * @author    Shinya Ohyanagi <sohyanagi@gmail.com>
 * @license   New BSD License
 * @link      http://search.cpan.org/~kazuho/String-Filter-0.01/
 */
class Filter
{
    /**
     * Version.
     */
    const VERSION = '0.0.1';

    /**
     * Default rule
     *
     * @var    mixed
     * @access private
     */
    private $_defaultRule = null;

    /**
     * Rules
     *
     * @var    array
     * @access private
     */
    private $_rules = array();

    /**
     * Regex pattern.
     *
     * @var    mixed
     * @access private
     */
    private $_ra = null;

    /**
     * Instantiates the filter object.
     *
     * @param  array $rules Rules
     * @access public
     * @return void
     */
    public function __construct(array $rules)
    {
        $this->defaultRule($rules['defaultRule']);
        if (isset($rules['rules'])) {
            $this->addRule($rules['rules']);
        }
    }

    /**
     * Setter / getter for the default conversion function.
     *
     * @param  mixed $rule
     * @access public
     * @return mixed Filtered output of the input
     */
    public function defaultRule($rule = null)
    {
        if (is_null($rule)) {
            return $this->_defaultRule;
        }
        if (!is_callable($rule)) {
            throw new Exception('Args is not callable.');
        }

        $this->_defaultRule = $rule;
        return $rule;
    }

    /**
     * Adds a conversion rule.
     *
     * @param  mixed $rule
     * @access public
     * @return \String\Filter Fluent interface
     */
    public function addRule(array $rule)
    {
        // Fixme
        foreach ($rule as $k => $v) {
            $this->_rules[$k] = $v;
        }

        $pattern = str_replace(
            '/', '\\/', implode('|', array_keys($this->_rules))
        );
        $this->_ra    = sprintf('/%s/', $pattern);

        return $this;
    }

    /**
     * Converts the input string using the given rules and returns it.
     *
     * @param  mixed $text String to convert.
     * @access public
     * @return String Converted string
     */
    public function filter($text)
    {
        // Fixme
        $token = array();
        if (preg_match_all($this->_ra, $text, $match, PREG_OFFSET_CAPTURE)) {
            $token = $match[0];
        }
        // In this case preg_split() returns unmatched token.
        $token = array_merge(
            $token,
            preg_split(
                $this->_ra, $text, null,
                PREG_SPLIT_NO_EMPTY|PREG_SPLIT_OFFSET_CAPTURE
            )
        );

        $ret = array();
        foreach ($token as $key => $val) {
            foreach ($this->_rules as $k => $v) {
                $rule = '/' . str_replace('/', '\\/', $k) . '/';
                if (preg_match($rule, $val[0], $match)) {
                    $ret[$val[1]] = $v($match[0]);
                    goto NEXT_TOKEN;
                }
            }
            $default = $this->defaultRule();
            $ret[$val[1]] = $default($val[0]);
        NEXT_TOKEN:
            ;
        }

        ksort($ret);
        return implode('', $ret);
    }
}
