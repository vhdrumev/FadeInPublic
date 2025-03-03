<?php

function parseMarkdown($text): string {

    $headers = [
        '######' => 'h6',
        '#####'  => 'h5',
        '####'   => 'h4',
        '###'    => 'h3',
        '##'     => 'h2',
        '#'      => 'h1'
    ];

    foreach ($headers as $markdown => $html) {
        $text = preg_replace_callback('/^' . preg_quote($markdown, '/') . ' (.*?)$/m', function($matches) use ($html) {
            return "<$html>" . $matches[1] . "</$html>";
        }, $text);
    }

    $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text); // **bold**
    $text = preg_replace('/__(.*?)__/', '<strong>$1</strong>', $text); // __bold__

    $text = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $text); // *italic*
    $text = preg_replace('/_(.*?)_/', '<em>$1</em>', $text); // _italic_

    $text = preg_replace_callback('/(?:\[(.*?)\]\((.*?)\)|https?:\/\/[^\s]+)/', function($matches) {
        if (isset($matches[2])) {
            return '<a target="_blank" href="' . $matches[2] . '">' . $matches[1] . '</a>';
        } else {
            return '<a target="_blank" href="' . $matches[0] . '">' . $matches[0] . '</a>';
        }
    }, $text);

    $text = preg_replace('/^[\*\-]\s(.*?)$/m', '<ul><li>$1</li></ul>', $text);
    $text = preg_replace('/^\d+\.\s(.*?)$/m', '<ol><li>$1</li></ol>', $text);
    $text = preg_replace('/^> (.*?)$/m', '<blockquote>$1</blockquote>', $text);
    $text = preg_replace('/```(.*?)```/s', '<pre><code>$1</code></pre>', $text);
    $text = preg_replace('/`(.*?)`/', '<code>$1</code>', $text);

    $text = nl2br($text);

    $text = trim($text);

    // links [test](url) and url into anchor
    $text = preg_replace('/\[(.*?)\]\((.*?)\)/', '<a target="_blank" href="$2">$1</a>', $text);
    $text = preg_replace('/\[(.*?)\]\(https:\/\/([a-zA-Z0-9\-\.]+(?:\/[^\)]*)?)\)/', '<a target="_blank" href="https://$2">$1</a>', $text);

    return $text;
}

