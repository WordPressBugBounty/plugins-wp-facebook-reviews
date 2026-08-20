=== WP Review Slider ===
Contributors: jgwhite33
Donate link: https://wpreviewslider.com/
Tags: facebook reviews, twitter, review slider, testimonials, social proof
Requires at least: 3.0.1
Requires PHP: 7.4
Tested up to: 7.0
Stable tag: 15.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Facebook reviews & X (Twitter) posts in a WordPress review slider or grid. Official Facebook API + X API v2. Saved locally for fast social proof.

== Description ==

**Show Facebook reviews and X (Twitter) posts on your WordPress site — in a beautiful review slider or grid.**

WP Review Slider helps you turn Facebook Page reviews, Facebook recommendations, and positive X (formerly Twitter) posts into on-site **testimonials** and **social proof**. Reviews are downloaded once, stored in your WordPress database, and served from your own server for fast page loads.

This plugin uses the **official Facebook API** and the **X API v2** — not scraping or crawling. That means a more reliable, compliant way to display customer feedback on your WordPress site.

### Why use WP Review Slider?

* Display **Facebook reviews** and **Facebook recommendations** with a shortcode, widget, or template function.
* Search and download **X (Twitter) posts** about your business, then show them next to your Facebook reviews.
* Build an engaging **review slider** (or a responsive grid/list) without writing code.
* Keep reviews on your server for better performance and control.
* Create multiple review templates for different pages, posts, and sidebars.

Here's a quick video demonstration of adding reviews in less than 2 minutes!

[youtube https://youtu.be/J8V3lzUHfkA]

### Facebook reviews & recommendations

Facebook switched many pages from a 1–5 star scale to Positive or Negative recommendations. WP Review Slider can save those recommendations with a rating value so you can still display them as **5-star reviews** when you want.

**How Facebook reviews work:**
1. Connect to our Facebook app with a couple of button clicks.
2. Click Retrieve Reviews to download Facebook Page reviews and recommendations into your WordPress database.
3. Create a review template and paste the shortcode into a post, page, or widget area.

### X (formerly Twitter) posts & testimonials

Search X for posts mentioning your business, download the ones you want to showcase, and display them in the same **review slider** / template system as your Facebook reviews.

**How X (Twitter) posts work:**
1. Enter your X Developer OAuth 1.0a keys (Consumer Key, Consumer Key Secret, Access Token, Access Token Secret). A Bearer Token can be used as a fallback.
2. Search with the X API v2 (recent search covers the last 7 days; full-archive search is available if your X access allows it).
3. Click the download icon next to posts you want to keep.
4. In template Filter settings, choose Review Type: Facebook, X (Twitter), or both — then pick a source and display them with your shortcode.

**Note:** X’s Free developer tier does not include post search. You need a paid X API plan (for example Pay Per Use) with Recent Search access. Spaces in search queries mean AND; use OR (example: `Yellowhammer OR Huntsville`) when you want either term.

### Feature list

* Download Facebook Page reviews and recommendations; store them locally for fast loading.
* Official Facebook API connection (no illegal scraping).
* Search and download X (Twitter) posts via X API v2 (OAuth 1.0a preferred; Bearer Token fallback).
* Create a review slider — or use a responsive grid / list layout.
* Show Facebook recommendations as stars or as positive/negative recommendations.
* Mix Facebook and X (Twitter) in one template, or filter by review type and source.
* Multiple templates for posts, pages, and widget areas.
* Shortcode, PHP template function, or WordPress widget display.
* Customize stars, dates, icons (including the X logo), avatars, border radius, colors, and fonts.
* Choose reviews per row, multiple rows, newest or random order.
* Hide reviews or posts without text.
* Live template preview with Style / General / Filter / Badge tabs.
* Optional review badge beside your slider.
* Add custom CSS for advanced styling.

### More free WP Review Slider plugins

* [Google Reviews](https://wordpress.org/plugins/wp-google-places-review-slider/)
* [Yelp Reviews](https://wordpress.org/plugins/wp-yelp-review-slider/)
* [TripAdvisor Reviews](https://wordpress.org/plugins/wp-tripadvisor-review-slider/)
* [Thumbtack Reviews](https://wordpress.org/plugins/wp-thumbtack-review-slider/)
* [WooCommerce Reviews](https://wordpress.org/plugins/review-slider-for-woocommerce/)
* [Airbnb Reviews](https://wordpress.org/plugins/wp-airbnb-review-slider/)

### Upgrade to Pro for more features

[WP Review Slider Pro](https://wpreviewslider.com/) adds:

* US-based customer support via email and forum.
* Reviews from Yelp, TripAdvisor, Google, and 80+ other review sites.
* Group reviews by language, tags, post ID, or categories.
* WooCommerce product review summary slider.
* Front-end review submission form.
* Email alerts for new low reviews.
* Hide selected reviews, CSV export, and manual review entry.
* More template styles and advanced slider controls.
* Minimum rating / word-count filters and read-more for long reviews.
* Mix review types in one slider and pick reviews per template.
* Google review snippet markup for search results.
* Future feature updates included.

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory, or install it from **Plugins → Add New** on WordPress.org.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Open the **WP Reviews** menu in your admin.
4. Download Facebook reviews and/or X (Twitter) posts, create a template, then paste the shortcode into a page or post.

== Frequently Asked Questions ==

= Are Facebook reviews stored on my server? =

Yes. After you download reviews from Facebook, they are saved in your WordPress database. Your site displays those stored reviews locally instead of calling Facebook on every page view, which helps pages load faster.

= Does this plugin use the official Facebook API? =

Yes. WP Review Slider connects through the official Facebook API. It does not use illegal scraping or crawling methods that some other review plugins rely on.

= Can I show Facebook recommendations as star ratings? =

Yes. Facebook now uses Positive and Negative recommendations instead of 1–5 stars for many pages. This plugin includes a setting to save recommendations with a rating value so you can display them with star ratings (great for a review slider or testimonials section).

= Can I display X (formerly Twitter) posts as testimonials? =

Yes. Add your X Developer OAuth 1.0a keys (Consumer Key, Consumer Key Secret, Access Token, Access Token Secret), search X, download posts, then set the template Review Type to **X (Twitter)** or **Facebook & X (Twitter)**. A Bearer Token can be used as a fallback. The plugin uses X API v2 recent search (last 7 days). Free X developer access does not include search — a paid plan with Recent Search is required. Full-archive search needs higher-tier access.

= Why don’t my X search results match what I expect? =

X search treats spaces as **AND** (all words must appear in the same post). Try a single keyword, an OR query (example: `BrandName OR City`), or an exact phrase in quotes. Recent search only covers the last 7 days.

= Can I put Facebook and X posts in the same review slider? =

Yes. In template Filter settings, set Review Type to **Facebook & X (Twitter)**, or choose one type and optionally limit by source.

= Can I filter which reviews are displayed? =

Yes. Create multiple templates with different settings, including review type, source, hide-without-text, display order, and how many reviews appear per row.

= How do I request a new feature? =

We are always looking for features to add. Post on the support forum or contact us on this [page](https://wpreviewslider.com/contact/).

== Screenshots ==

1. Create a beautiful review slider on your posts or pages! More styles available in Pro.
2. Lots of customizable options. Even input CSS if you want.
3. All your reviews show up in a list.
4. Even use it on your sidebar!
5. Create as many templates as you like.
6. Download and display posts from X (formerly Twitter)!
7. Easily pick and choose which X posts to download!

== Changelog ==

= 15.2 =
* Fix badge branding when a Facebook slider and a Google slider are used on the same page.

= 15.1 =
* Added a new Analytics page for review insights and charts.
* Added Pro feature highlights and upgrade nudges throughout the admin.

= 15.0 =
* Major X (formerly Twitter) update: search and download posts with X API v2 using OAuth 1.0a (Consumer Key, Consumer Key Secret, Access Token, Access Token Secret), with Bearer Token as an optional fallback.
* Bundled TwitterOAuth library upgraded to 4.0.1 for native X API v2 support (PHP 7.4+).
* Template Filter settings now support Review Type: Facebook, X (Twitter), or both, plus per-source filtering for X download sources.
* Front-end shortcodes and widgets display Twitter/X posts alongside Facebook reviews; X logo icon, @handles, and hashtag links included.
* Improved X search help text (AND vs OR queries, 7-day recent search window) and more reliable post saving.
* Rebranded Twitter UI/copy to X and updated profile/post links to x.com.

= 14.7 =
* X (Twitter) search can authenticate with OAuth 1.0a user-context keys, with Bearer Token kept as an optional fallback.
= 14.6 =
* Updated the bundled TwitterOAuth library to 4.0.1 (native X API v2 support and a current CA certificate bundle). Requires PHP 7.4+.
* X (Twitter) search Bearer Token (OAuth 2.0 app-only) auth option.
= 14.5 =
* Updated X (formerly Twitter) downloading to use the X API v2 (recent search and optional full-archive search), replacing the retired v1.1 endpoints.
* X search now requires your own X Developer credentials; removed the old shared/default keys.
* Rebranded the Twitter areas of the plugin to X and switched profile/post links to x.com.
* Fixed a database notice on activation for the X (Twitter) sources table.
= 14.4 =
* New Review List tools: edit a reviewer photo/date, hide/show or delete a review instantly (AJAX), and view review photos in a pop-up lightbox.
* Rebuilt the Templates editor with Style / General / Filter / Badge tabs and a live preview.
* Added Style 6 (card layout) and a live style preview that updates as you change stars, verified badge, avatar, icon, colors, and font sizes.
* Added review photo display in templates with click-to-enlarge lightbox on the front end.
* Added a review badge you can place beside your reviews (location, business image, colors, and text overrides), off by default on new templates.
* Filter a template by Facebook page, choose grid or slider with per-slider options, add a Read More link, and set equal review heights.

= 14.3 =
* duplicate review fix

= 14.2 =
* bug fix

= 14.1 =
* Updated admin styling for WordPress 7.0.
* Improved README for clarity and search.

= 14.0 =
* Fixed security issue.
* Fixed: Date handling for Facebook reviews now properly converts to MySQL datetime format
* Fixed: Improved efficiency by reusing calculated timestamp values

= 13.9 =
* freemius sdk update

= 13.8 =
* small bug fix

= 13.7 =
* added ability to hide or initial Last Name of reviewer.
* added feature to hide certain reviews

= 13.6 =
*compatible with new Google version
= 13.4 =
* small bug fix
= 13.3 =
* updated Freemius SDK
= 13.2 =
* PHP 8.2 warning notice fixed.
= 13.1 =
* download review fix
= 13.0 =
* bug fix
= 12.8 =
* bug fix
= 12.7 =
* update database tables. smoother read more.
= 12.6 =
* updated Freemius SDK
= 12.5 =
* small bug fix with transparent color not being saved sometimes.
= 12.4 =
* Freemius SDK update
= 12.3 =
* Freemius SDK update
= 12.2 =
* Freemius SDK update
= 12.1 =
* Freemius SDK update
= 12.0 =
* Freemius SDK update
= 11.9 =
* Freemius SDK update
= 11.8 =
* Freemius SDK update
= 11.7 =
* Freemius SDK update
= 11.6 =
* Freemius SDK update
= 11.5 =
* Freemius SDK update
= 11.4 =
* Freemius SDK update
= 11.3 =
* Freemius SDK update
= 11.2 =
* Freemius SDK update
= 11.1 =
* Freemius SDK update
= 11.0 =
* Freemius SDK update
= 10.9 =
* Freemius SDK update
= 10.8 =
* Freemius SDK update
= 10.7 =
* Freemius SDK update
= 10.6 =
* Freemius SDK update
= 10.5 =
* Freemius SDK update
= 10.4 =
* Freemius SDK update
= 10.3 =
* Freemius SDK update
= 10.2 =
* Freemius SDK update
= 10.1 =
* Freemius SDK update
= 10.0 =
* Freemius SDK update
= 9.9 =
* Freemius SDK update
= 9.8 =
* Freemius SDK update
= 9.7 =
* Freemius SDK update
= 9.6 =
* Freemius SDK update
= 9.5 =
* Freemius SDK update
= 9.4 =
* Freemius SDK update
= 9.3 =
* Freemius SDK update
= 9.2 =
* Freemius SDK update
= 9.1 =
* Freemius SDK update
= 9.0 =
* Freemius SDK update
= 8.9 =
* Freemius SDK update
= 8.8 =
* Freemius SDK update
= 8.7 =
* Freemius SDK update
= 8.6 =
* Freemius SDK update
= 8.5 =
* Freemius SDK update
= 8.4 =
* Freemius SDK update
= 8.3 =
* Freemius SDK update
= 8.2 =
* Freemius SDK update
= 8.1 =
* Freemius SDK update
= 8.0 =
* Freemius SDK update
= 7.9 =
* Freemius SDK update
= 7.8 =
* Freemius SDK update
= 7.7 =
* Freemius SDK update
= 7.6 =
* Freemius SDK update
= 7.5 =
* Freemius SDK update
= 7.4 =
* Freemius SDK update
= 7.3 =
* Freemius SDK update
= 7.2 =
* Freemius SDK update
= 7.1 =
* Freemius SDK update
= 7.0 =
* Freemius SDK update
= 6.9 =
* Freemius SDK update
= 6.8 =
* Freemius SDK update
= 6.7 =
* Freemius SDK update
= 6.6 =
* Freemius SDK update
= 6.5 =
* Freemius SDK update
= 6.4 =
* Freemius SDK update
= 6.3 =
* Freemius SDK update
= 6.2 =
* Freemius SDK update
= 6.1 =
* Freemius SDK update
= 6.0 =
* Freemius SDK update
= 5.9 =
* Freemius SDK update
= 5.8 =
* Freemius SDK update
= 5.7 =
* Freemius SDK update
= 5.6 =
* Freemius SDK update
= 5.5 =
* Freemius SDK update
= 5.4 =
* Freemius SDK update
= 5.3 =
* Freemius SDK update
= 5.2 =
* Freemius SDK update
= 5.1 =
* Freemius SDK update
= 5.0 =
* Freemius SDK update
= 4.9 =
* Freemius SDK update
= 4.8 =
* Freemius SDK update
= 4.7 =
* Freemius SDK update
= 4.6 =
* Freemius SDK update
= 4.5 =
* Freemius SDK update
= 4.4 =
* Freemius SDK update
= 4.3 =
* Freemius SDK update
= 4.2 =
* Freemius SDK update
= 4.1 =
* Freemius SDK update
= 4.0 =
* Freemius SDK update
= 3.9 =
* Freemius SDK update
= 3.8 =
* Freemius SDK update
= 3.7 =
* Freemius SDK update
= 3.6 =
* Freemius SDK update
= 3.5 =
* Freemius SDK update
= 3.4 =
* Freemius SDK update
= 3.3 =
* Freemius SDK update
= 3.2 =
* Freemius SDK update
= 3.1 =
* Freemius SDK update
= 3.0 =
* Freemius SDK update
= 2.9 =
* Freemius SDK update
= 2.8 =
* Freemius SDK update
= 2.7 =
* Freemius SDK update
= 2.6 =
* Freemius SDK update
= 2.5 =
* Freemius SDK update
= 2.4 =
* Freemius SDK update
= 2.3 =
* Freemius SDK update
= 2.2 =
* Freemius SDK update
= 2.1 =
* Freemius SDK update
= 2.0 =
* Freemius SDK update
= 1.9 =
* Freemius SDK update
= 1.8 =
* Freemius SDK update
= 1.7 =
* Freemius SDK update
= 1.6 =
* Freemius SDK update
= 1.5 =
* Freemius SDK update
= 1.4 =
* Freemius SDK update
= 1.3 =
* Freemius SDK update
= 1.2 =
* Freemius SDK update
= 1.1 =
* Freemius SDK update
= 1.0 =
* Initial release

== Upgrade Notice ==

= 15.2 =
Fix badge branding when a Facebook slider and a Google slider are used on the same page.

= 15.1 =
New Analytics page plus Pro feature highlights and upgrade nudges in the admin.

= 15.0 =
Major X (Twitter) update: X API v2 search with OAuth 1.0a, template filters for Facebook and/or X posts, and updated X branding. Requires PHP 7.4+.
