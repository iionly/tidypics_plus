<?php

/**
 * Most recently uploaded images - logged in user's images
 *
 */

elgg_gatekeeper();

$owner = elgg_get_logged_in_user_entity();

// set up breadcrumbs
elgg_push_breadcrumb(elgg_echo('photos'), 'photos/siteimagesall');
elgg_push_breadcrumb($owner->name, "photos/siteimagesfriends/$owner->username");
elgg_push_breadcrumb(elgg_echo('friends'));

$offset = (int)get_input('offset', 0);
$limit = (int)get_input('limit', 16);

if ($friends = $owner->getFriends(array('limit' => false))) {
	$friendguids = array();
	foreach ($friends as $friend) {
		$friendguids[] = $friend->getGUID();
	}
	$result = elgg_list_entities(array(
		'type' => 'object',
		'subtype' => 'image',
		'owner_guids' => $friendguids,
		'limit' => $limit,
		'offset' => $offset,
		'full_view' => false,
		'pagination' => true,
		'list_type' => 'gallery',
		'list_type_toggle' => false,
		'gallery_class' => 'tidypics-gallery tidypics-image-list',
	  'list_class' => 'elgg-list elgg-list-entity',
	));

	if (!empty($result)) {
		$area2 = $result;
	} else {
		$area2 = elgg_echo("tidypics:siteimagesfriends:nosuccess");
	}
} else {
	$area2 = elgg_echo("friends:none:you");
}

$title = elgg_echo('tidypics:siteimagesfriends');

elgg_load_css('slick');
elgg_load_css('slick-theme');
elgg_load_css('elgg.slick');
if ('yes' == elgg_get_plugin_setting('justified_gallery_list', 'tidypics_plus'))
{
	elgg_require_js('justifiedGallery');
	if (elgg_is_active_plugin('hypeLists'))
		elgg_require_js('init_justifiedGallery/init_justifiedGallery-hypeList');
	else
		elgg_require_js('init_justifiedGallery/init_justifiedGallery');
	elgg_load_css('justified-gallery-on');
}
elgg_require_js('tidypics/tidypics');
elgg_require_js('elgg/lightbox');
elgg_load_css('lightbox');
elgg_require_js('tidypics_plus/tidypics_plus');

$owner_guid = elgg_get_logged_in_user_guid();
elgg_register_menu_item('title', array(
	'name' => 'addphotos',
	'href' => "ajax/view/photos/selectalbum/?owner_guid=$owner_guid",
	'text' => elgg_echo("photos:addphotos"),
	'link_class' => 'elgg-button elgg-button-action elgg-lightbox'
));

// only show slideshow link if slideshow is enabled in plugin settings and there are images
if (elgg_get_plugin_setting('slideshow', 'tidypics') && !empty($result)) {
	elgg_require_js('tidypics/slideshow');
	$url = elgg_get_site_url() . "photos/siteimagesfriends/$owner->username?limit=64&offset=$offset&view=rss";
	$url = elgg_format_url($url);
	elgg_register_menu_item('title', array(
		'name' => 'slideshow',
		'id' => 'slideshow',
		'data-slideshowurl' => $url,
		'href' => '#',
		'text' => "<img src=\"" . elgg_get_simplecache_url("tidypics/slideshow.png") . "\" alt=\"".elgg_echo('album:slideshow')."\">",
		'title' => elgg_echo('album:slideshow'),
		'link_class' => 'elgg-button elgg-button-action'
	));
}

$body = elgg_view_layout('content', array(
	'filter_override' => elgg_view('filter_override/siteimages', array('selected' => 'friends')),
	'content' => $area2,
	'title' => $title,
	'sidebar' => elgg_view('photos/sidebar_im', array('page' => 'friends')),
));

// Draw it
echo elgg_view_page($title, $body);
