<?php

include_once( "utilities.php" );

$user = BlogUser::currentUser();
$user->logout();
redirect( URL_ROOT );
?>