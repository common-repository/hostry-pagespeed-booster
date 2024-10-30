<?php
$options = CdnMinifierPlugin::getOptions();
?>
<style type="text/css">
    form.cdn_minifier_form {
        background-color: white;
        padding: 0px;
    }

    form.cdn_minifier_form .bloc-fields {
        background-color: white;
        padding: 16px;
    }

    form.cdn_minifier_form .header {
        background-color: #f9f9f9;
        padding: 16px;
        border-bottom: 1px solid #e8e8e8;
        margin: 0;
    }

    .wrap h1 {
        display: flex;
        align-items: center
    }

    .wrap h1 img {
        width: 48px;
        min-width: 48px;
        margin-right: 10px;
    }
</style>

<div class="wrap">

    <h1>
        <img src="<?php echo htry_plugin_url('assets/images/logo-48x48.png'); ?>" width="32">
        <?php _e('Hostry PageSpeed Booster', 'cdn-minifier'); ?>
    </h1>

    <?php if (CdnMinifierPlugin::getOption('last_forced_disabled')):
        CdnMinifierPlugin::setOption('last_forced_disabled', false);
        ?>
        <div class="notice notice-error"><p><?php echo CdnMinifierPlugin::getMessageErrorNotice(); ?></p></div>
    <?php endif; ?>

    <div class="notice notice-info">
        <p><?php _e('Use the service', 'cdn-minifier'); ?>
            <b><a href="https://hostry.com/products/cdn/?utm_source=wp-admin&utm_medium=plugins&utm_campaign=cdn-minifier&url=<?php echo urlencode(get_option('home')); ?>" target="_blank">HOSTRY FREE CDN</a></b> <?php _e('and ensure fast loading of your static content (images, CSS, JavaScript, video, etc.) significantly improving the response time of the server.',
                'cdn-minifier'); ?>.
        </p>
    </div>

    <form method="post" action="options.php" class="cdn_minifier_form" novalidate="novalidate">
        <?php settings_fields('cdn_minifier_hostry') ?>
        <input name="cdn_minifier_hostry[option]" type="hidden" value="cdn"/>

        <h4 class="header"><?php _e('CDN Settings', 'cdn-minifier'); ?></h4>

        <div class="bloc-fields">
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="cdn_enable"><?php _e('CDN Enable', 'cdn-minifier'); ?></label></th>
                    <td>
                        <input name="cdn_minifier_hostry[cdn_enable]" id="cdn_enable" type="checkbox" value="1" <?php checked(true,
                            $options['cdn_enable']); ?>/>

                        <p class="description"><?php _e('Activating the CDN module will replace the current static links with the links of the CDN resource.',
                                'cdn-minifier'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="cdn_url"><?php _e('CDN URL', 'cdn-minifier'); ?></label></th>
                    <td>
                        <input name="cdn_minifier_hostry[cdn_url]" type="text" id="cdn_url" value="<?php echo esc_attr($options['cdn_url']); ?>" class="regular-text"/>
                        <p class="description"><?php _e('Specify a link to a CDN resource to replace static links. You can get this link from your CDN provider.',
                                'cdn-minifier'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('CDN Include', 'cdn-minifier'); ?></th>
                    <td>
                        <p>
                            <label>
                                <input name="cdn_minifier_hostry[cdn_include][theme]" type="checkbox" value="1" <?php checked(true,
                                    isset($options['cdn_include']) && in_array('theme', $options['cdn_include'])); ?>/>
                                <?php _e('Theme files', 'cdn-minifier'); ?>
                            </label>
                        </p>
                        <p>
                            <label>
                                <input name="cdn_minifier_hostry[cdn_include][plugins]" type="checkbox" value="1" <?php checked(true,
                                    isset($options['cdn_include']) && in_array('plugins',
                                        $options['cdn_include'])); ?>/>
                                <?php _e('Plugins files', 'cdn-minifier'); ?>
                            </label>
                        </p>
                        <p>
                            <label>
                                <input name="cdn_minifier_hostry[cdn_include][attachments]" type="checkbox" value="1" <?php checked(true,
                                    isset($options['cdn_include']) && in_array('attachments',
                                        $options['cdn_include'])); ?>/>
                                <?php _e('Attachments (images for post, other files upload)', 'cdn-minifier'); ?>
                            </label>
                        </p>
                        <p>
                            <label>
                                <input name="cdn_minifier_hostry[cdn_include][content]" type="checkbox" value="1" <?php checked(true,
                                    isset($options['cdn_include']) && in_array('content',
                                        $options['cdn_include'])); ?>/>
                                <?php _e('Other files in /wp-content', 'cdn-minifier'); ?>
                            </label>
                        </p>
                        <p>
                            <label>
                                <input name="cdn_minifier_hostry[cdn_include][includes]" type="checkbox" value="1" <?php checked(true,
                                    isset($options['cdn_include']) && in_array('includes',
                                        $options['cdn_include'])); ?>/>
                                <?php _e('Standard WordPress files', 'cdn-minifier'); ?> <i>(/wp-includes files)</i>
                            </label>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="cdn_extensions"><?php _e('CDN extensions apply', 'cdn-minifier'); ?></label></th>
                    <td>
                        <input name="cdn_minifier_hostry[cdn_extensions]" type="text" id="cdn_extensions" value="<?php echo esc_attr(implode(', ',
                            $options['cdn_extensions'])); ?>" class="regular-text"/>
                        <p class="description"><?php _e('Specify the file extensions to download from the CDN network, using the comma separator ",". Example: css, js',
                                'cdn-minifier'); ?></p>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <input type="submit" name="submit" class="button button-primary" value="<?php _e('Save Changes',
                    'cdn-minifier'); ?>">
            </p>
        </div>


    </form>

    <p>&nbsp;</p>

    <form method="post" action="options.php" class="cdn_minifier_form" novalidate="novalidate">
        <?php settings_fields('cdn_minifier_hostry') ?>
        <input name="cdn_minifier_hostry[option]" type="hidden" value="minifier"/>

        <h4 class="header"><?php _e('Minifier HTML&CSS&JS Settings', 'cdn-minifier'); ?></h4>

        <div class="bloc-fields">
            <table class="form-table">
                <tr>
                    <?php
                    $allowMinifyHTML = version_compare(phpversion(), '5.4.0') && class_exists('DOMDocument');
                    ?>
                    <th scope="row">
                        <label for="minifier_html"><?php _e('HTML Minifier', 'cdn-minifier'); ?></label></th>
                    <td>
                        <input name="cdn_minifier_hostry[minifier_html]" id="minifier_html" type="checkbox" value="1" <?php
                        if ( ! $allowMinifyHTML) {
                            echo 'disabled ';
                            $options['minifier_html'] = false;
                        }
                        checked(true,
                            $options['minifier_html']);
                        ?>/>

                        <p class="description"><?php _e('Includes the function of minifying HTML pages. Reduces white space and comments.',
                                'cdn-minifier'); ?></p>
                        <?php if ( ! $allowMinifyHTML) : ?>
                            <p class="description" style="color: #ff00007d"><?php _e('The option is available for the PHP version of at least <b>5.4.0</b> and enabled module <b>DOMDocument</b>.',
                                    'cdn-minifier'); ?></p>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <?php
                    $allowMinifyCSS = version_compare(phpversion(), '7.0.0');
                    ?>
                    <th scope="row">
                        <label for="minifier_css"><?php _e('CSS Minifier', 'cdn-minifier'); ?></label></th>
                    <td>
                        <input name="cdn_minifier_hostry[minifier_css]" id="minifier_css" type="checkbox" value="1" <?php if ( ! $allowMinifyCSS) {
                            echo 'disabled ';
                            $options['minifier_html'] = false;
                        }
                        checked(true,
                            $options['minifier_css']); ?>/>

                        <p class="description"><?php _e('Minification CSS scripts, optimizes elements and removes spaces. Concatenation of files into one.',
                                'cdn-minifier'); ?></p>
                        <?php if ( ! $allowMinifyCSS) : ?>
                            <p class="description" style="color: #ff00007d"><?php _e('The option is available for the PHP version of at least <b>7.0.0</b>.',
                                    'cdn-minifier'); ?></p>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <?php
                    $allowMinifyJS = version_compare(phpversion(), '7.0.0');
                    ?>
                    <th scope="row">
                        <label for="minifier_js"><?php _e('JS Minifier', 'cdn-minifier'); ?> (BETA)</label></th>
                    <td>
                        <input name="cdn_minifier_hostry[minifier_js]" id="minifier_js" type="checkbox" value="1" <?php if ( ! $allowMinifyJS) {
                            echo 'disabled ';
                            $options['minifier_html'] = false;
                        }
                        checked(true,
                            $options['minifier_js']); ?>/>

                        <p class="description"><?php _e('Minification JS scripts, optimizes internal variables functions, comments and whitespace. Concatenation of files into one.',
                                'cdn-minifier'); ?></p>
                        <?php if ( ! $allowMinifyJS) : ?>
                            <p class="description" style="color: #ff00007d"><?php _e('The option is available for the PHP version of at least <b>7.0.0</b>.',
                                    'cdn-minifier'); ?></p>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <input type="submit" name="submit" class="button button-primary" value="<?php _e('Save Changes',
                    'cdn-minifier'); ?>">
                <input type="submit" name="cdn_minifier_hostry[clear]" class="button button-default" value="<?php _e('Clear cache minified',
                    'cdn-minifier'); ?> ( <?php echo $sizeMinifiedCache; ?> )" <?php if ((int)$size === 0) {
                    echo 'disabled';
                } ?>>
            </p>
        </div>


    </form>

</div>