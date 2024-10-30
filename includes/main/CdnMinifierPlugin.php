<?php

/**
 * Class CdnMinifierPlugin
 */
class CdnMinifierPlugin
{
    /**
     * @var null|array
     */
    private static $options = null;

    /**
     *
     */
    public static function run()
    {
        self::register_languages();

        self::register_hooks();
    }

    /**
     *
     */
    protected static function register_languages()
    {
        $dir_languages = plugin_basename(dirname(dirname(__DIR__))) . '/languages';
        load_plugin_textdomain('cdn-minifier', false, $dir_languages);
    }

    /**
     *
     */
    protected static function register_hooks()
    {
        /**
         * Adding in menu
         */
        add_action(
            'admin_menu',
            function () {
                $style = array(
                    'float: left;',
                    'width: 16px',
                    'position: relative',
                    'margin-right: 4px',
                    'top: 1px'
                );
                $img   = '<img src="' . htry_plugin_url('assets/images/hostry-16x16.png') . '" style="';
                $img   .= implode('; ', $style) . '" />';

                $admin_page = add_options_page(
                    'PageSpeed Booster',
                    $img . ' PageSpeed Booster',
                    'manage_options',
                    'pagespeed-booster',
                    array(
                        __CLASS__,
                        'admin_settings_page',
                    )
                );

                /**
                 * Help sidebar
                 */
                add_action('load-' . $admin_page, array(
                    __CLASS__,
                    'helpSideBar'
                ));
            }
        );

        /**
         * Registering settings
         */
        add_action('admin_init', function () {
            register_setting(
                'cdn_minifier_hostry',
                'cdn_minifier_hostry',
                array(
                    __CLASS__,
                    'options_update',
                )
            );
        });

        /**
         *
         */
        if ( ! self::isAdminPath()) {
            /**
             * Rewrite HTML
             */
            if (self::getOption('cdn_enable')) {
                add_action(
                    'template_redirect',
                    array(
                        'CdnMinifierRewriter',
                        'init',
                    )
                );
            }

            /**
             * Adding minifier hooks for HTML
             */
            if ((int)self::getOption('minifier_html') > 0) {
                add_action(
                    'template_redirect',
                    array(
                        'CdnMinifierMinifier',
                        'parse',
                    ),
                    99
                );
            }

            /**
             * Verify version minimal
             */
            if (version_compare(phpversion(), '7.0.0') >= 0) {
                /**
                 * Adding minifier hooks for CSS
                 */
                if ((int)self::getOption('minifier_css') > 0) {
                    add_filter('style_loader_tag', array(
                        'CdnMinifierMinifier',
                        'loaderCSS'
                    ), PHP_INT_MAX, 4);
                }

                /**
                 * Adding minifier hooks for JS
                 */
                if ((int)self::getOption('minifier_js') > 0) {
                    add_filter('script_loader_tag', array(
                        'CdnMinifierMinifier',
                        'loaderJS'
                    ), PHP_INT_MAX, 3);
                }

                /**
                 * Include CSS and JS files
                 */
                add_action('wp_head', array(
                    'CdnMinifierMinifier',
                    'enqueueScripts'
                ));

                /**
                 * Include JS files in footer
                 */
                add_action('wp_footer', array(
                    'CdnMinifierMinifier',
                    'enqueueScriptsFooter'
                ));
            }
        }
    }

    /**
     * @return bool
     */
    public static function isAdminPath()
    {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        return preg_match('#\/(wp\-admin\/|wp\-login)#', $path) > 0;
    }

    /**
     * @param $name
     *
     * @return mixed|null
     */
    public static function getOption($name)
    {
        $options = self::getOptions();

        if (is_array($options) && array_key_exists($name, $options)) {
            return $options[$name];
        }

        return null;
    }

    /**
     * @return array|bool
     */
    public static function getOptions()
    {
        if (self::$options === null) {
            $options = get_option('cdn_minifier_hostry');
            if ($options === false) {
                return false;
            }
            self::$options = $options;

            /**
             * Include for version < 1.1.2, include `plugins` and `content` for default checked in cdn
             */
            if (empty(self::$options['version']) || version_compare('1.1.2', self::$options['version']) > 0) {
                self::$options['cdn_include'][] = 'plugins';
                self::$options['cdn_include'][] = 'content';
            }
        }

        return self::$options;
    }

    /**
     * @param $data
     *
     * @return array|bool
     */
    public static function options_update($data)
    {
        $options = self::getOptions();

        switch ($data['option']) {
            case 'cdn':
                /**
                 *
                 */
                $options['last_forced_disabled'] = 0;

                /**
                 *
                 */
                $options['cdn_enable'] = 0;
                if (array_key_exists('cdn_enable', $data) && (int)$data['cdn_enable'] === 1) {
                    $options['cdn_enable'] = 1;
                }

                /**
                 *
                 */
                $options['cdn_url'] = esc_url($data['cdn_url']);

                if (empty($options['cdn_url'])) {
                    $options['cdn_enable'] = 0;
                }

                /**
                 *
                 */
                if ( ! empty($options['cdn_url']) && (int)$options['cdn_enable'] > 0) {
                    /**
                     * Verify if working CDN, testing request for temporary file and comparing md5 value
                     * If file is differents, to disable CDN
                     */
                    $result = self::checkCDN($options['cdn_url']);
                    if (count($result) !== array_sum(array_map('intval', $result))) {
                        $options['cdn_enable'] = 0;

                        add_settings_error('general', 'cdn_disabled', self::getMessageErrorNotice());
                    }
                }

                /**
                 *
                 */
                $includes               = array('theme', 'plugins', 'attachments', 'content', 'includes');
                $options['cdn_include'] = array();
                foreach ($includes as $include) {
                    if (array_key_exists('cdn_include', $data) && array_key_exists($include, $data['cdn_include'])) {
                        $options['cdn_include'] [] = $include;
                    }
                }

                /**
                 *
                 */
                $options['cdn_extensions'] = array();
                if (array_key_exists('cdn_extensions', $data)) {
                    $options['cdn_extensions'] = array_filter(array_map('trim', explode(',', $data['cdn_extensions'])),
                        function ($element) {
                            return preg_match('|^[a-zA-Z0-9]+$|', $element) > 0;
                        });
                }

                break;

            case 'minifier':
                foreach (array('html', 'js', 'css') as $option) {
                    $options['minifier_' . $option] = array_key_exists('minifier_' . $option,
                        $data) && (int)$data['minifier_' . $option] === 1 ? 1 : 0;
                }

                /**
                 * Clearing cache minified
                 */
                if (isset($data['clear'])) {
                    CdnMinifierMinifier::clear();
                }
                break;
        }

        /**
         * Clear page cache for plugin 'WP Super Cache'
         */
        // todo

        /**
         * Extrage version last and saving
         */
        $dirPlugin = dirname(dirname(dirname(__FILE__)));

        $fileVersion = file_get_contents($dirPlugin . '/cdn-minifier.php');
        if (preg_match('|^Version:\s*((?:\d+\.?)+)|m', $fileVersion, $m)) {
            $options['version'] = $m[1];
        }

        return $options;
    }

    /**
     * @param $cdn_url
     * @param int $timeout
     *
     * @return array
     */
    public static function checkCDN($cdn_url, $timeout = 10)
    {
        $data = array();

        $files     = array(
            '/wp-includes/css/dashicons.min.css',
            '/wp-includes/js/jquery/jquery.js'
        );
        $curl_init = function_exists('curl_init');

        foreach ($files as $file) {
            $data[$file] = false;
            $url         = rtrim($cdn_url, '/') . $file . '?ver=' . time();
            $response    = null;

            if ($curl_init) {
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
                $response = curl_exec($ch);
                curl_close($ch);
            } else {
                $ctx = stream_context_create(array(
                    'http' => array(
                        'timeout' => $timeout
                    ),
                    'ssl'  => array(
                        'verify_peer'      => false,
                        'verify_peer_name' => false,
                    ),
                ));

                $response = file_get_contents($url, false, $ctx);
            }

            /**
             * Comparing content
             */
            $data[$file] = md5_file(get_home_path() . $file) === md5($response);
        }

        return $data;
    }

    /**
     * @return string
     */
    public static function getMessageErrorNotice()
    {
        $url = self::getOption('cdn_url');

        $message = __('In order to safely display the contents of the website pages, the CDN settings are disabled because the CDN resource',
            'cdn-minifier');
        $message .= ' <b>' . $url . '</b> ';
        $message .= __('did not send the corresponding content.',
            'cdn-minifier');

        return $message;
    }

    /**
     *
     */
    public static function hook_activation()
    {
        /**
         * Adding option default
         */
        add_option('cdn_minifier_hostry', array(
            'cdn_enable'     => false,
            'cdn_url'        => '',
            'cdn_include'    => explode(',', 'theme,plugins,attachments,content,includes'),
            'cdn_extensions' => explode(',', 'js,css,png,jpg,jpeg,gif,woff2,mp4,mp3,wav,ico'),
            'minifier_html'  => version_compare(phpversion(), '5.4.0') >= 0 && class_exists('DOMDocument'),
            'minifier_css'   => false,
            'minifier_js'    => false
        ));

        /**
         * Create path in /wp-content/uploads/cdn-minifier/
         */
        CdnMinifierMinifier::init();

        /**
         * Disable CDN (if not working CDN)
         */
        if ((int)CdnMinifierPlugin::getOption('cdn_enable') > 0) {
            $cdn_url = CdnMinifierPlugin::getOption('cdn_url');
            if ( ! empty($cdn_url)) {
                $result = self::checkCDN($cdn_url, 5);

                if (count($result) !== array_sum(array_map('intval', $result))) {
                    self::setOption('cdn_enable', false);
                    self::setOption('last_forced_disabled', true);
                }
            }
        }
    }

    /**
     * @param $name
     * @param $value
     *
     * @return bool
     */
    public static function setOption($name, $value)
    {
        self::getOptions();

        if (is_array(self::$options)) {
            self::$options[$name] = $value;

            update_option('cdn_minifier_hostry', self::$options);

            return true;
        }

        return false;
    }

    /**
     *
     */
    public static function hook_uninstall()
    {
        /**
         * Delete option
         */
        delete_option('cdn_minifier_hostry');
    }

    /**
     *
     */
    public static function helpSideBar()
    {
        /**
         * Generate HELP
         */
        $current_screen = get_current_screen();

        /**
         *
         */
        $options_help = '<h5>' . __('What is CDN service?', 'cdn-minifier') . '</h5>' .
                        '<p>' . __('CDN stands for "content delivery network". It is a distributed network of dedicated servers that perform content caching to speed up its delivery (\'output\') to the enduser. Servers are distributed all over the world in such a way that ensures minimal response time for the website users. In most cases, videos and static elements of websites are meant by the word \'content\' (such that require no server code execution or database queries, like css / js).',
                'cdn-minifier') . '</p>';

        $options_help .= '<h5>' . __('What is  Hostry FREE CDN?', 'cdn-minifier') . '</h5>';
        $options_help .= '<p>' . __('Hostry FREE CDN is a premium-class global CDN (Content Delivery Network) service, with 48 Points of Presence (PoP) around the world and available free-of-charge use.',
                'cdn-minifier') . '</p>';

        $options_help .= '<h5>' . __('How Does CDN Operate?', 'cdn-minifier') . '</h5>';
        $options_help .= '<p>' . __('User interaction: on example.com he is given an html page. On such html-page all css, js, pictures and video are linked to cdn.example.com, which means that the content is downloaded from the CDN network. When the client\'s browser accesses the CDN address, the request goes to the nearest responding server and the respond is delivered the same way by the short route.',
                'cdn-minifier') . '</p>';

        $options_help .= '<h5>' . __('How Does CDN Influence SEO?', 'cdn-minifier') . '</h5>';
        $options_help .= '<p>' . __('CDN directly affects such an aspect as website loading speed and, accordingly,  along with other aspects is considered to be a factor affecting the results of the search systems query (Google, Bing, Yandex, Baidu, etc.).',
                'cdn-minifier') . '</p>';

        $options_help .= '<h5>' . __('How do CDNs Help SEO?', 'cdn-minifier') . '</h5>';
        $options_help .= '<p>' . __('CDNs improve the speed and quality of content that is delivered to the user. CDNs should be seen as part of the solution for search ranking as it is applied to page speed and efficient content delivery, but it is not the only thing that needs to be done to increase search ranking. Think of CDNs as a way to improve upon the technical ranking factors for SEO.',
                'cdn-minifier') . '</p>';

        $current_screen->add_help_tab(
            array(
                'id'      => 'overview',
                'title'   => __('Overview', 'cdn-minifier'),
                'content' => $options_help,
            )
        );

        /**
         *
         */
        $options_help = '<h5>' . __('What Is Minification?', 'cdn-minifier') . '</h5>' .
                        '<p>' . __('Minification (minify) is a simple approach to minimization of css, js, and html file size. When being compressed, all code comments, line breaks, extra tabs and whitespaces are deleted. It helps to spare 10 ... 20% of the original file size.',
                'cdn-minifier') . '</p>' .
                        '<p>' . __('When minifying CSS / JS, merging of all the files in a single one is also used to reduce the amount of requests to the web server.',
                'cdn-minifier') . '</p>';

        $current_screen->add_help_tab(
            array(
                'id'      => 'minifer',
                'title'   => __('CSS/JS/HTML Minification', 'cdn-minifier'),
                'content' => $options_help,
            )
        );

        /**
         *
         */
        // todo: link to faq (for future
//        $current_screen->set_help_sidebar(
//            '<p><strong>' . __('For more information:', 'cdn-minifier') . '</strong></p>' .
//            '<p><a href="#">' . __('How to create a CDN service?', 'cdn-minifier') . '</a></p>' // todo: link to FAQ
//        );
    }

    /**
     *
     */
    public static function hook_deactivation()
    {
        /**
         * Clear cache for minified files
         */
        CdnMinifierMinifier::clear();
    }

    /**
     * Generate settings page for plugin
     */
    public static function admin_settings_page()
    {
        /**
         *
         */
        $path  = CdnMinifierMinifier::getPath('*');
        $files = glob($path);
        $size  = 0;
        foreach ($files as $file) {
            $size += filesize($file);
        }
        $sizeMinifiedCache = formatSizeUnits($size);

        // todo: Add statistics collection, with user permission

        /**
         * Include view
         */
        include __DIR__ . '/view_admin_settings.php';
    }
}