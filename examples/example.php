<?php
require_once dirname(__DIR__) . '/src/String/Filter.php';
$list = array(
    'rules' => array(
        'http://[A-Za-z0-9_\-\~\.\%\?\#\@/]+' => function($url) {
            $url = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
            return sprintf('<a href="%s">%s</a>', $url, $url);
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
                $ret     = sprintf(
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
echo $sf->filter('@kazuho @kazuho foo@bar http://hello.com/ yesyes <b> #hash') . PHP_EOL;
//// @<a href="http://twitter.com/kazuho">kazuho</a> @<a href="http://twitter.com/kazuho">kazuho</a> foo@bar <a href="http://hello.com/">http://hello.com/</a> yesyes &lt;b&gt; <a href="http://twitter.com/search?q=%23hash"><b>#hash</b></a>'

// You can also write addRule()
$sf = new \String\Filter(array(
    'defaultRule' => function($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
));
$sf->addRule(array(
    'http://[A-Za-z0-9_\-\~\.\%\?\#\@/]+' => function($url) {
        $url = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
        return sprintf('<a href="%s">%s</a>', $url, $url);
    }
));

$sf->addRule(array(
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
    }
));

$sf->addRule(array(
    '(?:^|\s)#[A-Za-z0-9_]+' => function($value) {
        if (preg_match('/^(.?)(#.*)$/', $value, $_)) {
            $prefix  = $_[1];
            $hashtag = $_[2];
            $ret     = sprintf(
                '%s<a href="http://twitter.com/search?q=%s"><b>%s</b></a>',
                $prefix,
                htmlspecialchars(urlencode($hashtag), ENT_QUOTES, 'UTF-8'),
                $hashtag
            );
            return $ret;
        }
    }
));
echo $sf->filter('@kazuho @kazuho foo@bar http://hello.com/ yesyes <b> #hash') . PHP_EOL;


