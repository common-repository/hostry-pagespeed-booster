=== Hostry PageSpeed Booster ===
Contributors: hostry
Tags: Pagespeed, Minify, Google Search Console, SEO, CDN
Requires at least: 4.7
Tested up to: 5.6
Requires PHP: 5.3
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Speed your website up and improve SEO ranking  as well as WPO rates by using CDN and  CSS, JavaScript, HTML minifications


== Description ==

The plugin improves the indicator of webpage loading time by means of scaling statics by using **content delivery network** (CDN), and contributes to a substantial increase in productivity on account of reducing the size of CSS/JS statics and HTML content.
The simplified plugin setup does not require special knowledge.
After plugin installation and activation, you only have to provide the CDN link and activate the service.


= Page Speed and SEO – is there any connection? =

It has already become rather obvious that site speed (and consequently, page load time) is crucial for Web Search Engines page ranking algorithms. In measuring site speed the retrieval engines are even believed to be counting to first bite precisely. To top it off, the slower the page is loading, the less pages the search engines are able to crawl through due to deficient allocated crawl budget. And this is definitely no good news for your indexation.

For the overall user experience and satisfaction page speed is of no small importance too. Prolonged load time brings unsatisfactory bounce rates, users are less likely to linger on such page. Let alone the chilling effect longer load time casts over type conversions.


= HOSTRY FREE CDN service makes your website operate at a more promising speed. =

Hostry CDN (Content Delivery Network) uses Hostry’s globally-distributed edge nodes of presence to speed up data delivery for web resources and apps. For reduction of cross network delay, offloads origins and more comfortable maintenance costs there is Hostry CDN, which is always at hand for users. Page loading is considerably boosted up bringing forth more enhanced engagement.

The Hostry [FREE CDN](https://hostry.com/products/cdn/ "FREE CDN") is provided by the international hosting company INXY LTD project [HOSTRY](https://hostry.com/).


= Features =
* Replace static links with CDN links
* Setting content for uploading into the CDN network at the level of file extensions
* Set a custom CDN hostname
* HTML minification
* CSS minification and unifying styles in a single file
* JS minification and unifying scripts in a single file


= Additional Hostry CDN Features =
* HTTPS support
* Free SSL certificate or third-party certificates
* Global reach - 52 distributed edge points of presence.
* Unlimited domains on most solutions
* Push and Pull support
* Purge
* Custom rules


= SYSTEM REQUIREMENTS =
* PHP: 5.3 or higher
* WordPress: 4.7 or higher


= Author =
* [Hostry](https://hostry.com/ "Hostry.com")


== Screenshots ==

1. Hostry PageSpeed Booster - Settings page

== Installation ==
*FOR AN AUTOMATIC INSTALLATION THROUGH WORDPRESS:*
1. Select **Add New** from the WordPress **Plugins** menu in the admin area.
2. Search for **Hostry Pagespeed Booster**.
3. Click **Install Now**, then **Activate Plugin**.

*FOR MANUAL INSTALLATION VIA FTP:*
1. Upload the **hostry-pagespeed-booster** folder to the **/wp-content/plugins/directory**.
2. Activate the plugin from the **Plugins** screen in your WordPress admin area.

*TO UPLOAD THE PLUGIN THROUGH WORDPRESS, INSTEAD OF FTP:*
1. From the **Add New** plugins page in your WordPress admin area, select the **Upload Plugin** button.
2. Select the **hostry-pagespeed-booster.zip** file, click **Install Now** and **Activate Plugin**.

== Frequently Asked Questions ==

= Minification-what’s that supposed to mean? =
Basically, ‘minification’ stands for the disposing of excessive or irrelevant data. However, the process does not disturb browser’s processing of the website. For instance, code comments, shorter identifiers for variables and function symbols, false code removal, and formatting, etc.

= What Is a CDN? How Does a CDN work? =
CDN is short for **Content Distribution Network**, which is a wide-area network of Proxy servers and their data acquisition centers. The major target is achieved by spatial service distribution to the end users thus providing uninterrupted operations and improved performance.

= Do I need a Hostry Pagespeed Booster when I already have HTTP/2? =
HTTP/2 is certainly a breakthrough when we consider successful reduction of one-server multiple request impact by elegantly putting to use one and the same connection for any quantity of concurrent requests. In this case, the CSS/JS concatenation remains acute. To sum up, customize, try, reconfigure, try again, adjust and go by what is more applicable to you current situation. In some cases HTTP/2 is more than enough; in other circumstances you may need aggregation and minification as well as HTTP/2 or HTTP/2 at one stretch with minification.

= I process HTML compressing with deflate/gzip, why would I need Hostry Pagespeed Booster? =
Hostry Pagespeed Booster makes the source HTML even smaller, thus further speeding up its compression and providing better results. Moreover, bare deflate/gzip can make your HTML markup look mediocre and non-professional. But here Hostry Pagespeed Booster comes to your aid.


== Changelog ==

= 1.2.5 =
* Compatibility tested with versions of Wordpress 5.6.2
* Follow Location allowed for CDN during verification
* Minor optimizing check CDN url

= 1.2.4 =
* CSS minification set by default as off

= 1.2.3 =
* Optimizing minify for authorized/unauthorized user

= 1.2.2 =
* Added feature replace cdn link for `data-lazy-src` images
* Tested for Wordpress 5.2 compatibility

= 1.2.1 =
* Added feature replace cdn link for relative link
* Added feature replace cdn link for `srcset` images (responsive)
* Fixed for parsing the change of cdn links for the `background-image:url()`

= 1.1.3 =
* Fixed include language for version PHP <7.0.0

= 1.1.2 =
* Fixed bug minified RSS FEED
* Fixing url generate to images if version PHP <7.0.0
* Fixed minification for blogs that are in directories

= 1.1.1 =
* Added a change of links to the CDN and for third-party plug-ins, as well as the content of third-party plug-ins (for plug-ins with gallery).

= 1.1 =
* Fixed bug with compatibility with array for PHP 5.3.x.
* Implemented icons in the menu and in the plugin settings
* Frozen CSS / JS minification options if PHP version is not supported.

= 1.0.1 =
* Fixed bug when checking the CDN for blogs that are in subfolders.

= 1.0.0 =

* First version implemented
