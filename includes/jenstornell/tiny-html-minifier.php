<?php
/**
 * Class TinyHtmlMinifier
 * @author https://github.com/jenstornell/tiny-html-minifier
 * Source version: https://github.com/jenstornell/tiny-html-minifier/blob/a803ea2ad4b817fe713a80e7d6cc397c6b952433/tiny-html-minifier.php
 */

class TinyHtmlMinifier
{
    function __construct($options)
    {
        $this->options  = $options;
        $this->output   = '';
        $this->build    = [];
        $this->skip     = 0;
        $this->skipName = '';
        $this->head     = false;
        $this->elements = [
            'skip'   => [
                'code',
                'pre',
                'textarea',
                'script'
            ],
            'inline' => [
                'b',
                'big',
                'i',
                'small',
                'tt',
                'abbr',
                'acronym',
                'cite',
                'code',
                'dfn',
                'em',
                'kbd',
                'strong',
                'samp',
                'var',
                'a',
                'bdo',
                'br',
                'img',
                'map',
                'object',
                'q',
                'span',
                'sub',
                'sup',
            ],
            'hard'   => [
                '!doctype',
                'body',
                'html',
            ]
        ];
    }

    // Run minifier
    function minify($html)
    {
        $html = $this->removeComments($html);

        $rest = $html;

        while ( ! empty($rest)) :

            $parts = explode('<', $rest, 2);

            $this->walk($parts[0]);

            $rest = (isset($parts[1])) ? $parts[1] : '';

        endwhile;

        return $this->output;
    }

    // Walk trough html

    function removeComments($content = '')
    {
        return preg_replace('/<!--(.|\s)*?-->/', '', $content);
    }

    // Remove comments

    function walk(&$part)
    {

        $tag_parts   = explode('>', $part);
        $tag_content = $tag_parts[0];

        if ( ! empty($tag_content)) {
            $name    = $this->findName($tag_content);
            $element = $this->toElement($tag_content, $part, $name);
            $type    = $this->toType($element);

            if ($name == 'head') {
                $this->head = ($type == 'open') ? true : false;
            }

            $this->build[] = [
                'name'    => $name,
                'content' => $element,
                'type'    => $type
            ];

            $this->setSkip($name, $type);

            if ( ! empty($tag_content)) {
                $content = (isset($tag_parts[1])) ? $tag_parts[1] : '';
                if ($content !== '') {
                    $this->build[] = [
                        'content' => $this->compact($content, $name, $element),
                        'type'    => 'content'
                    ];
                }
            }

            $this->buildHtml();
        }
    }

    // Check if string contains string

    function findName($part)
    {
        $name_cut = explode(" ", $part, 2)[0];
        $name_cut = explode(">", $name_cut, 2)[0];
        $name_cut = explode("\n", $name_cut, 2)[0];
        $name_cut = preg_replace('/\s+/', '', $name_cut);
        $name_cut = strtolower(str_replace('/', '', $name_cut));

        return $name_cut;
    }

    // Return type of element

    function toElement($element, $noll, $name)
    {
        $element = $this->stripWhitespace($element);
        $element = $this->addChevrons($element, $noll);
        $element = $this->removeSelfSlash($element);
        $element = $this->removeMeta($element, $name);

        return $element;
    }

    // Create element

    function stripWhitespace($element)
    {
        if ($this->skip == 0) {
            $element = preg_replace('/\s+/', ' ', $element);
        }

        return $element;
    }

    // Remove unneeded element meta

    function addChevrons($element, $noll)
    {
        $char    = ($this->contains('>', $noll)) ? '>' : '';
        $element = '<' . $element . $char;

        return $element;
    }

    // Strip whitespace from element

    function contains($needle, $haystack)
    {
        return strpos($haystack, $needle) !== false;
    }

    // Add chevrons around element

    function removeSelfSlash($element)
    {
        if (substr($element, -3) == ' />') {
            $element = substr($element, 0, -3) . '>';
        }

        return $element;
    }

    // Remove unneeded self slash

    function removeMeta($element, $name)
    {
        if ($name == 'style') {
            $element = str_replace([
                ' type="text/css"',
                "' type='text/css'"
            ],
                ['', ''], $element);
        } elseif ($name == 'script') {
            $element = str_replace([
                ' type="text/javascript"',
                " type='text/javascript'"
            ],
                ['', ''], $element);
        }

        return $element;
    }

    // Compact content

    function toType($element)
    {
        $type = (substr($element, 1, 1) == '/') ? 'close' : 'open';

        return $type;
    }

    function setSkip($name, $type)
    {
        foreach ($this->elements['skip'] as $element) {
            if ($element == $name && $this->skip == 0) {
                $this->skipName = $name;
            }
        }
        if (in_array($name, $this->elements['skip'])) {
            if ($type == 'open') {
                $this->skip++;
            }
            if ($type == 'close') {
                $this->skip--;
            }
        }
    }

    // Build html

    function compact($content, $name, $element)
    {
        if ($this->skip != 0) {
            $name = $this->skipName;
        } else {
            $content = preg_replace('/\s+/', ' ', $content);
        }

        if (
            $this->isSchema($name, $element) &&
            ! empty($this->options['collapse_json_ld'])
        ) {
            return json_encode(json_decode($content));
        }
        if (in_array($name, $this->elements['skip'])) {
            return $content;
        } elseif (
            in_array($name, $this->elements['hard']) ||
            $this->head
        ) {
            return $this->minifyHard($content);
        } else {
            return $this->minifyKeepSpaces($content);
        }
    }

    // Find name by part

    function isSchema($name, $element)
    {
        if ($name != 'script') {
            return false;
        }

        $element = strtolower($element);
        if ($this->contains('application/ld+json', $element)) {
            return true;
        }

        return false;
    }

    // Set skip if elements are blocked from minification

    function minifyHard($element)
    {
        $element = preg_replace('!\s+!', ' ', $element);
        $element = trim($element);

        return trim($element);
    }

    // Minify all, even spaces between elements

    function minifyKeepSpaces($element)
    {
        return preg_replace('!\s+!', ' ', $element);
    }

    // Strip but keep one space

    function buildHtml()
    {
        foreach ($this->build as $build) {

            if ( ! empty($this->options['collapse_whitespace'])) {

                if (strlen(trim($build['content'])) == 0) {
                    continue;
                } elseif ($build['type'] != 'content' && ! in_array($build['name'], $this->elements['inline'])) {
                    trim($build['content']);
                }

            }

            $this->output .= $build['content'];
        }

        $this->build = [];
    }
}

class TinyMinify
{
    static function html($html, $options = [])
    {
        $minifier = new TinyHtmlMinifier($options);

        return $minifier->minify($html);
    }
}