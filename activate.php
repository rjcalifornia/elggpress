<?php
/**
 * Register the ElggBlog class for the object/blog subtype
 */

if (get_subtype_id('object', 'posts')) {
	update_subtype('object', 'posts', 'ElggBlog');
} else {
	add_subtype('object', 'posts', 'ElggBlog');
}
