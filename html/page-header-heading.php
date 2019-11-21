<?php

include_once( "utilities.php" );

$user = BlogUser::currentUser();

$searchForm = new HTML\Form( "search-form" );

$txtSearch = $searchForm->field( "search" );
?>