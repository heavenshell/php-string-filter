<?php
/**
 * String\Filter - a regexp-based string filter.
 *
 * PHP version 5.3
 *
 * Copyright (c) 2010-2011 Shinya Ohyanagi, All rights reserved.
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
 * <pre>
 *   The module is a regexp-based string filter,
 *   Original module is Perl's String::Filter.
 *   see @link.
 * </pre>
 *
 * @category  String
 * @package   String\Filter
 * @version   $id$
 * @copyright (c) 2010 Cybozu Labs, Inc.  Written by Kazuho Oku.
 * @copyright (c) 2010-2011 Shinya Ohyanagi
 * @author    Shinya Ohyanagi <sohyanagi@gmail.com>
 * @license   New BSD License
 * @link      http://search.cpan.org/~kazuho/String-Filter-0.01/
 */
require_once 'lime.php';
require_once dirname(__DIR__) . '/src/String/Filter.php';

$t = new lime_test(null, new lime_output_color());
$list = array(
    'rules' => array(
        'http://[A-Za-z0-9_\-\~\.\%\?\#\@/]+' => function($url) {
            $url = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
            $ret = "<a href=\"$url\">$url</a>";
            return $ret;
        },
        '(?:^|\s)\@[A-Za-z0-9_]+' => function($value) {
            if (preg_match('/^(.*?\@)(.*)$/', $value, $_)) {
                $prefix = $_[1];
                $user   = $_[2];
                $ret    = sprintf(
                    '%s<a href="http://twitter.com/%s">%s</a>',
                    $prefix,
                    htmlspecialchars($user, ENT_QUOTES, 'UTF-8'),
                    $user
                );

                return $ret;
            }
        },
        '(?:^|\s)#[A-Za-z0-9_]+' => function($value) {
            if (preg_match('/^(.?)(#.*)$/', $value, $_)) {
                $prefix  = $_[1];
                $hashtag = $_[2];
                $ret = sprintf(
                    '%s<a href="http://twitter.com/search?q=%s"><b>%s</b></a>',
                    $prefix,
                    htmlspecialchars(urlencode($hashtag), ENT_QUOTES, 'UTF-8'),
                    $hashtag
                );
                return $ret;
            }
        },
    ),
    'defaultRule' => function($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
);

$sf = new \String\Filter($list);
$t->ok(
    $sf->filter('@kazuho @kazuho foo@bar http://hello.com/ yesyes <b> #hash') ===
    '@<a href="http://twitter.com/kazuho">kazuho</a> @<a href="http://twitter.com/kazuho">kazuho</a> foo@bar <a href="http://hello.com/">http://hello.com/</a> yesyes &lt;b&gt; <a href="http://twitter.com/search?q=%23hash"><b>#hash</b></a>',
    ''
);

$t->ok(
    $sf->filter('テスト @kazuho @kazuho foo@bar http://hello.com/ yesyes <b> #hash') ===
    'テスト @<a href="http://twitter.com/kazuho">kazuho</a> @<a href="http://twitter.com/kazuho">kazuho</a> foo@bar <a href="http://hello.com/">http://hello.com/</a> yesyes &lt;b&gt; <a href="http://twitter.com/search?q=%23hash"><b>#hash</b></a>',
    ''
);

$t->ok(
    $sf->filter('テスト@foo @kazuho @kazuho foo@bar http://hello.com/ yesyes <b> #hash') ===
    'テスト@foo @<a href="http://twitter.com/kazuho">kazuho</a> @<a href="http://twitter.com/kazuho">kazuho</a> foo@bar <a href="http://hello.com/">http://hello.com/</a> yesyes &lt;b&gt; <a href="http://twitter.com/search?q=%23hash"><b>#hash</b></a>',
    ''
);
