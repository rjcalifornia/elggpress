<?php
/**
 * Save blog entity
 *
 * Can be called by clicking save button or preview button. If preview button,
 * we automatically save as draft. The preview button is only available for
 * non-published drafts.
 *
 * Drafts are saved with the access set to private.
 *
 * @package Blog
 */

// start a new sticky form session in case of failure
elgg_make_sticky_form('blog');

// save or preview
$save = (bool)get_input('save');

// store errors to pass along
$error = FALSE;
$error_forward_url = REFERER;
$user = elgg_get_logged_in_user_entity();

// edit or create a new entity
$guid = get_input('guid');

if ($guid) {
	$entity = get_entity($guid);
	if (elgg_instanceof($entity, 'object', 'posts') && $entity->canEdit()) {
		$blog = $entity;
	} else {
		register_error(elgg_echo('blog:error:post_not_found'));
		forward($error_forward_url);
	}

	// save some data for revisions once we save the new edit
	$revision_text = $blog->description;
	$new_post = $blog->new_post;
} else {
	$blog = new ElggBlog();
	$blog->subtype = 'posts';
	$new_post = TRUE;
}

// set the previous status for the hooks to update the time_created and river entries
$old_status = $blog->status;

// set defaults and required values.
$values = array(
	'title' => '',
	'description' => '',
	'status' => 'published',
	'access_id' => ACCESS_DEFAULT,
	'comments_on' => 'On',
	'excerpt' => '',
	'tags' => '',
	'container_guid' => (int)get_input('container_guid'),
);

// fail if a required entity isn't set
$required = array('title', 'description');

$uploaded_files = elgg_get_uploaded_files('featured_image');
if (!$uploaded_files) {
    if (!$guid) {
        register_error("No file was uploaded");
        forward(REFERER);
    }
}

if ($uploaded_files) {
$uploaded_file = array_shift($uploaded_files);
if (!$uploaded_file->isValid()) {
        $error = elgg_get_friendly_upload_error($uploaded_file->getError());
        register_error($error);
        forward(REFERER);
}
}
/*
$supported_mimes = [
        'image/jpeg',
        'image/png',
        'image/gif',
];

$mime_type = ElggFile::detectMimeType($uploaded_file->getPathname(), $uploaded_file->getClientMimeType());
if (!in_array($mime_type, $supported_mimes)) {
        register_error("$mime_type is not supported");
        forward(REFERER);
}*/

// load from POST and do sanity and access checking
foreach ($values as $name => $default) {
	if ($name === 'title') {
		$value = htmlspecialchars(get_input('title', $default, false), ENT_QUOTES, 'UTF-8');
	} else {
		$value = get_input($name, $default);
	}

	if (in_array($name, $required) && empty($value)) {
		$error = elgg_echo("blog:error:missing:$name");
	}

	if ($error) {
		break;
	}

	switch ($name) {
		case 'tags':
			$values[$name] = string_to_tag_array($value);
			break;

		case 'excerpt':
			if ($value) {
				$values[$name] = elgg_get_excerpt($value);
			}
			break;

		case 'container_guid':
			// this can't be empty or saving the base entity fails
			if (!empty($value)) {
				$container = get_entity($value);
				if ($container && $container->canWriteToContainer(0, 'object', 'posts')) {
					$values[$name] = $value;
				} else {
					$error = elgg_echo("blog:error:cannot_write_to_container");
				}
			} else {
				unset($values[$name]);
			}
			break;

		default:
			$values[$name] = $value;
			break;
	}
}

// if preview, force status to be draft
if ($save == false) {
	$values['status'] = 'draft';
}

// if draft, set access to private and cache the future access
if ($values['status'] == 'draft') {
	$values['future_access'] = $values['access_id'];
	$values['access_id'] = ACCESS_PRIVATE;
}

// assign values to the entity, stopping on error.
if (!$error) {
	foreach ($values as $name => $value) {
		$blog->$name = $value;
	}
}

// only try to save base entity if no errors
if (!$error) {
	if ($blog->save()) {
		// remove sticky form entries
		elgg_clear_sticky_form('blog');

		// remove autosave draft if exists
		$blog->deleteAnnotations('blog_auto_save');

		// no longer a brand new post.
		$blog->deleteMetadata('new_post');
                
if($uploaded_file)  
{
$file = new ElggFile();
$file->title = $file->getFilename();
$file->subtype = "file";
$file->category = "featured";
$file->owner_guid = $blog->getGUID();
$file->access_id = 2;
//$file->thumbnail = $file->getIcon('small')->getFilename();
//$file->smallthumb = $file->getIcon('medium')->getFilename();
//$file->largethumb = $file->getIcon('large')->getFilename();
if ($file->acceptUploadedFile($uploaded_file)) {
        $guid = $file->save(); 
        $file->thumbnail = $file->getIcon('small')->getFilename();
		$file->smallthumb = $file->getIcon('medium')->getFilename();
		$file->largethumb = $file->getIcon('large')->getFilename();
                $file->largethumb = $file->getIcon('master')->getFilename();
        $file->save();
        
        
		
	
}
        }

		// if this was an edit, create a revision annotation
		if (!$new_post && $revision_text) {
			$blog->annotate('blog_revision', $revision_text);
		}

		system_message(elgg_echo('blog:message:saved'));

		$status = $blog->status;

		// add to river if changing status or published, regardless of new post
		// because we remove it for drafts.
		if (($new_post || $old_status == 'draft') && $status == 'published') {
			elgg_create_river_item(array(
				'view' => 'river/object/posts/create',
				'action_type' => 'create',
				'subject_guid' => $blog->owner_guid,
				'object_guid' => $blog->getGUID(),
			));

			elgg_trigger_event('publish', 'object', $blog);

			// reset the creation time for posts that move from draft to published
			if ($guid) {
				$blog->time_created = time();
				$blog->save();
			}
		} elseif ($old_status == 'published' && $status == 'draft') {
			_elgg_delete_river(array(
				'object_guid' => $blog->guid,
				'action_type' => 'create',
			));
		}

		if ($blog->status == 'published' || $save == false) {
			forward($blog->getURL());
		} else {
			forward("posts/edit/$blog->guid");
		}
	} else {
		register_error(elgg_echo('blog:error:cannot_save'));
		forward($error_forward_url);
	}
} else {
	register_error($error);
	forward($error_forward_url);
}
