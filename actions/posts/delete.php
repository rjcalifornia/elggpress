<?php
/**
 * Delete blog entity
 *
 * @package Blog
 */

$blog_guid = get_input('guid');
$blog = get_entity($blog_guid);

if (elgg_instanceof($blog, 'object', 'posts') && $blog->canEdit()) {
	$container = get_entity($blog->container_guid);
	if ($blog->delete()) {
		system_message(elgg_echo('elggpress:message:deleted_post'));
		if (elgg_instanceof($container, 'group')) {
			forward("posts/group/$container->guid/all");
		} else {
			forward("posts/owner/$container->username");
		}
	} else {
		register_error(elgg_echo('elggpress:error:cannot_delete_post'));
	}
} else {
	register_error(elgg_echo('elggpress:error:post_not_found'));
}

forward(REFERER);