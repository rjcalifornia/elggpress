<?php
/**
 * Register the ElggBlog class for the object/blog subtype
 */

if (get_subtype_id('object', 'elggpress')) {
	update_subtype('object', 'elggpress', 'ElggBlog');
} else {
	add_subtype('object', 'elggpress', 'ElggBlog');
}
