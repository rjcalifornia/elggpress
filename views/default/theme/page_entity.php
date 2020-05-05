<?php

global $page_entity;
if($vars['full_view']) {
	$page_entity = elgg_extract('entity', $vars);
}