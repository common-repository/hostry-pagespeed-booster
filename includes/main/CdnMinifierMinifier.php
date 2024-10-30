<?php use MatthiasMullie\Minify;

/**
 * Class CdnMinifierMinifier
 */
class CdnMinifierMinifier
{
    /**
     * @var string
     */
    protected static $path;

    /**
     * @var array
     */
    protected static $css_files = array();

    /**
     * @var array
     */
    protected static $js_files_header = array();

    /**
     * @var array
     */
    protected static $js_files_footer = array();

    /**
     *
     */
    public static function clear()
    {
        $path  = CdnMinifierMinifier::getPath('*');
        $files = glob($path);
        if (count($files)) {
            foreach ($files as $file) {
                unlink($file);
            }
        }
    }

    /**
     * @param null $file
     *
     * @return string
     */
    public static function getPath($file = null)
    {
        if (empty(self::$path)) {
            $upload_dir = wp_get_upload_dir();
            self::$path = $upload_dir['basedir'] . '/cdn-minifier/';
        }

        return self::$path . $file;
    }

    /**
     *
     */
    public static function init()
    {
        /**
         * Create path if not exists
         */
        $path = self::getPath();
        if ( ! file_exists($path)) {
            mkdir($path, 0777, true);
        }
    }

    /**
     *
     */
    public static function parse()
    {
        if ( ! is_feed()) {
            ob_start(array(
                __CLASS__,
                'html_run'
            ));
        }
    }

    /**
     * @param $html
     * @param $handle
     * @param $href
     * @param $media
     *
     * @return null
     */
    public static function loaderCSS($html, $handle, $href, $media)
    {
        $styles_registered = wp_styles()->registered;

        /**
         * If exist condition, returning html code
         */
        if (array_key_exists($handle, $styles_registered) && array_key_exists('conditional',
                $styles_registered[$handle]->extra)) {
            return $html;
        }

        // todo: testing if RTL scripts (double) and setted replace

        self::$css_files[] = array($href, $html);

        return null;
    }

    /**
     * @param array $files
     * @param $minFileCss
     *
     * @return bool|int
     */
    public static function minifyCSS($files, $minFileCss)
    {
        $minFileVersion = time();

        $minifier = new Minify\CSS();
        foreach ($files as $file) {
            $minifier->add($_SERVER['DOCUMENT_ROOT'] . $file);
        }
        $minifier->minify($minFileCss);

        if (file_exists($minFileCss)) {
            $minFileVersion = filemtime($minFileCss);
        }

        return $minFileVersion;
    }

    /**
     * @param array $files
     * @param $minFileJS
     *
     * @return bool|int
     */
    public static function minifyJS($files, $minFileJS)
    {
        $minFileVersion = time();

        $minifier = new Minify\JS();
        foreach ($files as $file) {
            $minifier->add($_SERVER['DOCUMENT_ROOT'] . $file);
        }
        $minifier->minify($minFileJS);

        if (file_exists($minFileJS)) {
            $minFileVersion = filemtime($minFileJS);
        }

        return $minFileVersion;
    }

    /**
     *
     */
    public static function enqueueScripts()
    {
        /**
         * Include minified CSS files
         */
        if (count(self::$css_files)) {
            $ext     = '.css';
            $tagHTML = "<link rel='stylesheet' id='cdn-minifier-css' href='%s?ver=%s' type='text/css' media='all' />";
            self::generateMinify(self::$css_files, $ext, array(
                __CLASS__,
                'minifyCSS'
            ), $tagHTML);
        }

        /**
         * Include minified JS file
         */
        if (count(self::$js_files_header)) {
            $ext     = '-header.js';
            $tagHTML = "<script type='text/javascript' src='%s?ver=%s'></script>";
            self::generateMinify(self::$js_files_header, $ext, array(
                __CLASS__,
                'minifyJS'
            ), $tagHTML);
        }
    }

    /**
     * @param array $src_files
     * @param string $ext
     * @param callable $callbackMinify
     * @param string $tagHTML
     *
     * @return bool
     */
    protected static function generateMinify($src_files, $ext, $callbackMinify, $tagHTML)
    {
        if ( ! is_array($src_files) || count($src_files) === 0) {
            return false;
        }
        $indexHost       = self::getIndexCache($homeURL);
        $homeURLHostOnly = preg_replace('|(https?:\/\/.[^\/]*).*|', '$1', $homeURL);

        $fail           = false;
        $minFileNameSrc = self::getPath($indexHost . $ext);

        /**
         * Verify files exist in header, and verify filemtime
         */
        $files      = array();
        $filesIndex = array();
        foreach ($src_files as $k => $file) {
            $srcFile = preg_replace('|^' . preg_quote($homeURLHostOnly, '|') . '(.[^\?]*).*|', '$1', $file[0], -1,
                $cnt);
            if ((int)$cnt === 0) {
                /**
                 * Generate HTML view
                 */
                echo $file[1];
                unset($src_files[$k]);
            } else {
                $files[]      = $srcFile;
                $filesIndex[] = $file[0] . filemtime($_SERVER['DOCUMENT_ROOT'] . $srcFile);
            }
        }

        /**
         *
         */
        if ( ! $fail) {
            /**
             *
             */
            $statPluginFile = self::getPath('index' . $ext . (is_user_logged_in() ? 1 : '') . '.json');
            $fileCache      = md5(implode('|', $filesIndex));
            $reCache        = false;

            if ( ! file_exists($statPluginFile)) {
                $reCache = true;
            } else {
                $statObj = json_decode(file_get_contents($statPluginFile));
                if ($statObj->fileCache !== $fileCache) {
                    $reCache = true;
                }

                if ( ! file_exists($minFileNameSrc) || filemtime($minFileNameSrc) !== $statObj->ver) {
                    $reCache = true;
                }
            }

            /**
             *
             */
            if ($reCache) {

                try {
                    $minFileVersion = $callbackMinify($files, $minFileNameSrc);

                    /**
                     *
                     */
                    $status = file_put_contents($statPluginFile, json_encode(array(
                        'fileCache' => $fileCache,
                        'ver'       => $minFileVersion
                    )));
                    if ($status === false) {
                        new ErrorException('Error save data in file ' . $statPluginFile);
                    }
                } catch (Exception $e) {
                    $fail = true;
                }
            }


            /**
             *
             */
            if ( ! $fail && file_exists($minFileNameSrc)) {
                $urlMinifiedFile = preg_replace('|.*(\/wp-content\/uploads\/.*)$|', '$1', $minFileNameSrc);
                $urlMinifiedFile = rtrim($homeURL, '/') . $urlMinifiedFile;

                echo sprintf($tagHTML, $urlMinifiedFile, filemtime($minFileNameSrc)) . "\n";
            } else {
                $fail = true;
            }
        }

        /**
         *
         */
        if ($fail) {
            /**
             *
             */
            echo implode("\n", array_map(function ($el) {
                return $el[1];
            }, $src_files));
        }

        return true;
    }

    /**
     * @param null $homeURL
     *
     * @return string
     */
    public static function getIndexCache(&$homeURL = null)
    {
        $homeURL  = get_option('home');
        $md5_el   = array();
        $md5_el[] = $_SERVER['SERVER_NAME'];
        $md5_el[] = $homeURL;
        $md5_el[] = get_template();
        $md5_el[] = is_user_logged_in() ? 'OnLine' : 'OffLine';

        return md5(implode('+', $md5_el));
    }

    /**
     *
     */
    public static function enqueueScriptsFooter()
    {
        /**
         * Include minified JS file
         */
        if (count(self::$js_files_footer)) {
            $ext     = '-footer.js';
            $tagHTML = "<script type='text/javascript' src='%s?ver=%s'></script>";
            self::generateMinify(self::$js_files_footer, $ext, array(
                __CLASS__,
                'minifyJS'
            ), $tagHTML);
        }
    }

    /**
     * @param $tag
     * @param $handle
     * @param $src
     *
     * @return null
     */
    public static function loaderJS($tag, $handle, $src)
    {
        global $wp_scripts;

        $scripts_registered = $wp_scripts->registered;

        /**
         * If exist condition, returning html code
         */
        if (array_key_exists($handle, $scripts_registered) && array_key_exists('conditional',
                $scripts_registered[$handle]->extra)) {
            return $tag;
        }

        if (in_array($handle, $wp_scripts->in_footer)) {
            self::$js_files_footer[] = array($src, $tag);
        } else {
            self::$js_files_header[] = array($src, $tag);
        }


        return null;
    }

    /**
     * @param $html
     *
     * @return string
     */
    public static function html_run($html)
    {
        $existClassDomDocument = class_exists('DOMDocument');

        if ($existClassDomDocument) {
            $phpversion = phpversion();
            if (version_compare($phpversion, '7.0.0') >= 0) {
                $htmlMin = new \voku\helper\HtmlMin();

                $htmlMin->doRemoveWhitespaceAroundTags();             // remove whitespace around tags (depends on "doOptimizeViaHtmlDomParser(true)")
                $htmlMin->doRemoveHttpPrefixFromAttributes();         // remove optional "http:"-prefix from attributes (depends on "doOptimizeAttributes(true)")
                $htmlMin->doRemoveDefaultAttributes();                // remove defaults (depends on "doOptimizeAttributes(true)" | disabled by default)
                $htmlMin->doRemoveSpacesBetweenTags();                // remove more (aggressive) spaces in the dom (disabled by default)

                $html = $htmlMin->minify($html);
            } else if (version_compare($phpversion, '5.4.0') >= 0) {
//            $html = TinyMinify::html($html, [
//                'collapse_whitespace' => true
//            ]);
                $html = \PHPWee\HtmlMin::minify($html);
            }
        }

        return $html;
    }
}