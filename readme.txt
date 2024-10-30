=== Limit taxonomy term count ===
Contributors: dahousecatz
Tags: taxonomy, taxonomy-term,
Requires at least: 4.7
Tested up to: 4.9.8
Requires PHP: 5.6
Stable tag: 1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Allows passing a "limit" attribute to the register_taxonomy() function. This makes it possible to limit the number of
taxonomy terms users are allowed to add to a particular post.

== Description ==

By default Wordpress does not allow you to limit how many taxonomy terms can be attached to a post.
Enabling this plugin allows the use of the "limit" attribute when initially calling register_taxonomy().
If the limit is set to 1 then only one term can be added to the post. If the limit is greater than one then that many
terms may be added to the post. If the limit is attribute is not set, or it's set to -1 this plugin has no effect.

Example code showing the use of the limit attribute:

    register_taxonomy('artists', 'art_work', [
        'label'             => 'Artists',
        'show_ui'           => true,
        'limit'             => 1,
    ]);

This example shows the use of a custom taxonomy called "artists" and the post type is "art_work".

Here is another example but this time it's editing the previously registered taxonomy "post_tag":

    // First load the taxonomy
    $taxonomy = get_taxonomy('post_tag');

    // Edit it to only allow 2 tags to be added per post
    $taxonomy->limit = 2;

    // Re-save it with our updated settings (note casting the object to an array before saving)
    register_taxonomy('post_tag', 'post', (array)$taxonomy);

== Installation ==

The easiest way to install this plugin is to go to Add New in the Plugins section of your blog admin and search for
"Limit taxonomy term count." On the far right side of the search results, click "Install."

If the automatic process above fails, follow these simple steps to do a manual install:

1. Extract the contents of the zip file into your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Can I edit settings passed to select2? =

Yes, you can use the filter limit_taxonomy_term_count_js_data to make any changes to settings.

E.g.

    add_filter('limit_taxonomy_term_count_js_data', 'my_plugin_limit_taxonomy_term_count_js_data');
    function my_plugin_limit_taxonomy_term_count_js_data($data) {
        foreach($data as $taxonomy_name => &$settings) {
            if($taxonomy_name == 'this_is_the_taxonomy_your_looking_for') {
                // This is the array of settings passed to select2:
                print_r($settings['select2_options']);
            }
        }
        return $data;
    }

== Screenshots ==

1. Shows selecting a tag when limit is set to 1.
2. Shows selecting a tag when limit is set to 2.

== Changelog ==

= 1.0 =
* First version released.

== Upgrade Notice ==

None yet.
