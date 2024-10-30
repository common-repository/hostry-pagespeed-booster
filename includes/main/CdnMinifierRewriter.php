<?php

/**
 * Class CdnMinifierRewriter
 */
class CdnMinifierRewriter
{
    /**
     * @var string
     */
    protected static $blog_url;

    /**
     * @var string
     */
    protected static $cdn_url;

    /**
     * @var array
     */
    protected static $includes = array();

    /**
     * @var array
     */
    protected static $extensions = array();

    /**
     *
     */
    public static function init()
    {
        /**
         *
         */
        self::$blog_url   = rtrim(get_option('home'), '/');
        self::$cdn_url    = rtrim(CdnMinifierPlugin::getOption('cdn_url'), '/');
        self::$includes   = CdnMinifierPlugin::getOption('cdn_include');
        self::$extensions = CdnMinifierPlugin::getOption('cdn_extensions');

        /**
         *
         */
        ob_start(array(
            __CLASS__,
            'rewrite'
        ));
    }

    /**
     * @param $html
     *
     * @return string|string[]|null
     */
    public static function rewrite($html)
    {
        if (count(self::$extensions) === 0) {
            return $html;
        }

        /**
         * Extrage paths allow for replace for cdn path
         */
        $paths = array();
        if (in_array('theme', self::$includes)) {
            $paths[] = '/wp-content/themes/';
        }
        if (in_array('plugins', self::$includes)) {
            $paths[] = '/wp-content/plugins/';
        }
        if (in_array('attachments', self::$includes)) {
            $paths[] = '/wp-content/uploads/';
        }
        if (in_array('content', self::$includes)) {
            $paths[] = '/wp-content/(?!themes|plugins|uploads)';
        }
        if (in_array('includes', self::$includes)) {
            $paths[] = '/wp-includes/';
        }

        if (count($paths) === 0) {
            return $html;
        }

        /**
         * Apply regex replace CDN urls
         */
        // Start prefix detect
        $replaceRegex = '#(\s(?:href|src|data-lazy-src)=|\:\s*url)(\'|\"|\(|\(\')?';
        // Include url to site
        $replaceRegex .= self::regexUrl();
        // Include path for replace
        $replaceRegex .= '((?:' . implode('|', $paths) . ')';
        // Include extensions for replace
        $extensions   = array_map(function ($ext) {
            return preg_quote($ext, '#');
        }, self::$extensions);
        $replaceRegex .= '.[^\'\"\>\)]*\.(?:' . implode('|', $extensions) . ')(?:\?.[^\'\"\)\>]*)?)';
        // End url to static
        $replaceRegex .= '(\'|\"|\)|\)\')?#m';

        /**
         *
         */
        $html = preg_replace($replaceRegex, '$1$2' . rtrim(self::$cdn_url, '/') . '$3$4', $html);

        /**
         * Include responsive `srcset`
         */
        if (strpos($html, 'srcset') > 0) {
            $html = preg_replace_callback('#(srcset=(?:\"|\'))(.*?)(\"|\')#m', function ($matches) use ($paths) {

                $replaceRegex = self::regexUrl();
                // Include path for replace
                $replaceRegex .= '((?:' . implode('|', $paths) . ')';
                // Include extensions for replace
                $extensions   = array_map(function ($ext) {
                    return preg_quote($ext, '#');
                }, self::$extensions);
                $replaceRegex .= '.[^\s]*\.(?:' . implode('|', $extensions) . '))';
                $replaceRegex .= '(.[^\,]*)';

                $result = preg_replace('#' . $replaceRegex . '#m', rtrim(self::$cdn_url, '/') . '$1$2', $matches[2]);

                return $matches[1] . $result . $matches[3];
            }, $html);
        }

        return $html;
    }

    /**
     * @return string
     */
    protected static function regexUrl()
    {
        $pu_blog = parse_url(self::$blog_url);
        $regex   = '(?:';
        $regex   .= preg_quote(rtrim(self::$blog_url, '/'), '#') .
                    '|' .
                    '\/\/' . preg_quote($pu_blog['host'], '#');
        if (empty($pu_blog['path']) || $pu_blog['path'] === '/') {
            $regex .= '|(?=\/[^\/])';
        }
        $regex .= ')';

        return $regex;
    }
}