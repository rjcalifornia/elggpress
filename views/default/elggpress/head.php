<?php
/**
 * The HTML head
 *
 * @internal It's dangerous to alter this view.
 * 
 * @uses $vars['title'] The page title
 * @uses $vars['metas'] Array of meta elements
 * @uses $vars['links'] Array of links
 */

$metas = elgg_extract('metas', $vars, array());
$links = elgg_extract('links', $vars, array());
$site = elgg_get_site_entity();
$site_url = elgg_get_site_url();

$featured = elgg_get_entities(array(
	'type' => 'object',
	'subtype' => 'file',
        'category' => 'featured',
        'owner_guid' => $vars['entity']->guid,
	//'full_view' => false,
        'limit' => 1,
	'no_results' => elgg_echo("file:none"),
	'preload_owners' => true,
	'preload_containers' => true,
	'distinct' => false,
));

//var_dump($vars['testing']);
echo elgg_format_element('title', array(), $vars['title'], array('encode_text' => true));
foreach ($metas as $attributes) {
	echo elgg_format_element('meta', $attributes);
}
foreach ($links as $attributes) {
	echo elgg_format_element('link', $attributes);
}

$stylesheets = elgg_get_loaded_css();

foreach ($stylesheets as $url) {
	echo elgg_format_element('link', array('rel' => 'stylesheet', 'href' => $url));
}

// A non-empty script *must* come below the CSS links, otherwise Firefox will exhibit FOUC
// See https://github.com/Elgg/Elgg/issues/8328
?>
     

<script>
	<?php // Do not convert this to a regular function declaration. It gets redefined later. ?>
	require = function () {
		// handled in the view "elgg.js"
		_require_queue.push(arguments);
	};
	_require_queue = [];
</script>

<meta property="og:title" content="<?php echo $vars['entity']->title?>">
<meta property="og:site_name" content="<?php echo $site->name?>">
<meta property="og:description" content="<?php echo $vars['entity']->excerpt?>">
<meta property="og:type" content="blog">
<?php
      foreach ($featured as $t) {
                 $file_og = get_entity($t->guid);

                  $image_url = $file_og->getIcon('medium');
                  $icon= elgg_get_inline_url($image_url);
                  
                  
                  ?>
<meta property="og:image" content="<?php echo $icon;?>">
<?php
}
?>
<meta property="og:url" content="<?php echo $site_url;?>posts/view/<?php echo $vars['entity']->guid;?>/<?php echo str_replace(' ', '-', $vars['entity']->title);?>">

<?php
$blog = $vars['entity'];
//var_dump($blog);
//echo $blog;

?>

     

