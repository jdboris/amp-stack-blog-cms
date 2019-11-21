<?php
include_once( "utilities.php" );

$blog = Blog::currentBlog();
$posts = $blog->getPosts( 1 );

if( $posts === false )
{
	$posts = [];
}
?>

<h2>Recent Posts</h2>
<?php
foreach( $posts as $post )
{
	?>

	<!--
	<img class="card-img-top" src="..." alt="Card image cap">
	<a href="<?= URL_ROOT ?>user?id=<?= $post[ "AUTHOR_ID" ] ?>">
	-->
	<a class="card mb-3" href="<?= URL_ROOT ?>post?id=<?= $post[ "BLOG_POST_ID" ] ?>">
		<div class="card-body">
			<div class="h4 card-title"><?= $post[ "TITLE" ] ?></div>
			<small class="card-subtitle text-muted"><?= $post[ "AUTHOR_FIRST_NAME" ] . " " . $post[ "AUTHOR_LAST_NAME" ] ?> - <?= $post[ "DATE_POSTED" ] ?></small>
			<p class="card-text"><?= $post[ "PREVIEW" ] ?></p>
		</div>
	</a>

	<?php
}
?>