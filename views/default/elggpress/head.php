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
global $page_entity;
//var_dump($page_entity);

$entity = get_entity($page_entity->guid);

$site = elgg_get_site_entity();
$site_url = elgg_get_site_url();

$featured = elgg_get_entities(array(
	'type' => 'object',
	'subtype' => 'file',
        'category' => 'featured',
        'owner_guid' => $entity->guid,
	//'full_view' => false,
        'limit' => 1,
	'no_results' => elgg_echo("file:none"),
	'preload_owners' => true,
	'preload_containers' => true,
	'distinct' => false,
));

?>
     

<meta property="og:title" content="<?php echo $entity->title?>">
<meta property="og:site_name" content="<?php echo $site->name?>">
<meta property="og:description" content="<?php echo $vars['entity']->excerpt?>">
<meta property="og:type" content="website">
<?php
      foreach ($featured as $t) {
                 $file_og = get_entity($t->guid);
                 $icon= elgg_get_inline_url($file_og);
                  
                  
                  ?>
<meta property="og:image" content="<?php echo $icon;?>">
<meta property="og:image:height" content="320" />
<meta property="og:image:width" content="480" />
<?php
}
?>
<meta property="og:url" content="<?php echo $site_url;?>posts/view/<?php echo $entity->guid;?>/<?php echo str_replace(' ', '-', $entity->title);?>">

<?php

//var_dump($blog);
//echo $blog;

?>

     

