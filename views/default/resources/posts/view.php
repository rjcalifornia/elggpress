<?php
/**
 * Elgg pageshell
 * The standard HTML page shell that everything else fits into
 *
 * @package Elgg
 * @subpackage Core
 *
 * @uses $vars['head']        Parameters for the <head> element
 * @uses $vars['body_attrs']  Attributes of the <body> tag
 * @uses $vars['body']        The main content of the page
 * @uses $vars['sysmessages'] A 2d array of various message registers, passed from system_messages()
 */

$page_type = elgg_extract('page_type', $vars);
$guid = elgg_extract('guid', $vars);
elgg_entity_gatekeeper($guid, 'object', 'posts');
elgg_group_gatekeeper();

$blog = get_entity($guid);

elgg_set_page_owner_guid($blog->container_guid);

// no header or tabs for viewing an individual blog
$params = [
	'filter' => '',
	'title' => $blog->title
];

$container = $blog->getContainerEntity();
$crumbs_title = $container->name;

if (elgg_instanceof($container, 'group')) {
	elgg_push_breadcrumb($crumbs_title, "blog/group/$container->guid/all");
} else {
	elgg_push_breadcrumb($crumbs_title, "blog/owner/$container->username");
}

elgg_push_breadcrumb($blog->title);

$params['content'] = elgg_view_entity($blog, array('full_view' => true));

// check to see if we should allow comments
if ($blog->comments_on != 'Off' && $blog->status == 'published') {
	$params['content'] .= elgg_view_comments($blog);
}

$params['sidebar'] = elgg_view('elggpress/sidebar', array('page' => $page_type));

$elggpress_body = elgg_view_layout('content', $params);


// backward compatability support for plugins that are not using the new approach
// of routing through admin. See reportedcontent plugin for a simple example.
if (elgg_get_context() == 'admin' && elgg_is_admin_logged_in()) {
	_elgg_admin_add_plugin_settings_menu();
	elgg_unregister_css('elgg');
	echo elgg_view('page/admin', $vars);
	return true;
}

// render content before head so that JavaScript and CSS can be loaded. See #4032

$messages = elgg_view('page/elements/messages', array('object' => $vars['sysmessages']));

$header = elgg_view('page/elements/header', $vars);
$content = elgg_view('page/elements/body', $vars);
$footer = elgg_view('page/elements/footer', $vars);

$body = <<<__BODY
<div class="elgg-page elgg-page-default">
	<div class="elgg-page-messages">
		$messages
	</div>
__BODY;

$body .= elgg_view('page/elements/topbar_wrapper', $vars);

$body .= <<<__BODY
	<div class="elgg-page-header">
		<div class="elgg-inner">
			$header
		</div>
	</div>
	<div class="elgg-page-body">
		<div class="elgg-inner">
			$elggpress_body
		</div>
	</div>
	<div class="elgg-page-footer">
		<div class="elgg-inner">
			$footer
		</div>
	</div>
</div>
__BODY;

$body .= elgg_view('page/elements/foot');

$head = elgg_view('elggpress/head', array('entity'=>$blog));

$params = array(
	'head' => $head,
	'body' => $body,
);

if (isset($vars['body_attrs'])) {
	$params['body_attrs'] = $vars['body_attrs'];
}

echo elgg_view("page/elements/html", $params);
