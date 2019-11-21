<?php
include_once( "html/page-header.php" );

$user = BlogUser::currentUser();

$blog = Blog::currentBlog();

$posts = $blog->getPosts( 1 );

if( $posts === false )
{
	HTML\Modal::showWarning( "", Modal\GENERAL_ERROR );
}
else if( count( $posts ) > 0 )
{
	$firstPost = $posts[ 0 ];
	array_splice( $posts, 0, 1 );
}

// Get the first image in the main page splash image "current" folder
$splashImage = "images/main-page/splash/current/" . array_values( array_diff( scandir( "images/main-page/splash/current/" ), array( '..', '.' ) ) )[ 0 ];
?>


<div class="jumbotron jumbotron-dark jumbotron-full d-flex align-items-center" style="background-image: url('<?= $splashImage ?>');">
	<div class="container">
		<h1 class="display-4"><?= WEBSITE_TITLE ?></h1>
		<p class="lead">We create new worlds and experiences for you and your friends to explore and enjoy.</p>
		<hr class="my-4">
		<p>Follow the development of our games and stay up-to-date on the latest news.</p>
		<p class="lead">
			<a class="btn btn-primary btn-lg" href="#" role="button">Learn more</a>
		</p>
	</div>
</div>

<?php
if( count( $posts ) == 0 )
{
	?>
<h1>No posts to show!</h1>
	<?php
	exit( );
}
?>
<main class="container">

	<div class="row">
		<div class="col-md-1">

			<?php include( "html/social-media-sidebar.php" ) ?>

		</div>
		<div class="col-md-7">
			<div class="row">
				<div class="col">
					<?php
					$pagination = new Pagination( "homepage-posts-page", $blog->totalPosts, 1, GET( "page" ) );

					$pagination->page( function( ) {

						include_once( "UserPowerLevels.php" );
						$user = BlogUser::currentUser();
						$blog = Blog::currentBlog();
						$posts = $blog->getPosts( GET( "page" ), "", 1 );

						if( count( $posts ) > 0 )
						{
							$post = $posts[ 0 ];
							$comments = $blog->getPostComments( $post[ "BLOG_POST_ID" ], 1, 1 );
							?>

							<div class="row">
								<div class="blog-post col p-4 mb-3">
									<h1>
										<a href="<?= URL_ROOT ?>post?id=<?= $post[ "BLOG_POST_ID" ] ?>"><?= $post[ "TITLE" ] ?></a>

										<?php
										if( $user->powerLevel >= UserPowerLevels\MODERATOR )
										{
											?>
											<a href="<?= URL_ROOT ?>create-or-edit-post?id=<?= $post[ "BLOG_POST_ID" ] ?>"><i class="fas fa-edit"></i></a>
											<?php
										}
										?>
									</h1>
									<div class="mb-2">
										<small class="text-muted">
											<a href="#"><?= $post[ "AUTHOR_ALIAS" ] ?></a> - <?= $post[ "DATE_POSTED" ] ?>
										</small>
									</div>
									<div class="post-body">
										<?= $post[ "BODY_HTML" ] ?>
									</div>

									<?php
									if( count( $comments ) > 0 )
									{
										?>
										<div class="post-comments mt-3">
											<h2 class="border-bottom pb-2">Comments</h2>
											<?php
											foreach( $comments as $comment )
											{
												?>
												<div class="comment pt-1 pb-1">
													<small>
														<a href="#"><?= $comment[ "AUTHOR_FIRST_NAME" ] . " " . $comment[ "AUTHOR_LAST_NAME" ] ?></a>
														<span class="text-muted"> • <?= timeSince( $comment[ "COMMENT_DATE" ], "Y-m-d H:i:s" ) ?></span>
													</small>
													<div class="comment-body mb-2">
														<?= nl2br( htmlspecialchars( $comment[ "BODY" ], ENT_QUOTES, "UTF-8" ) ) ?>
													</div>
												</div>
												<?php
											}
											?>
											<div>

												<a class="btn btn-block btn-light" href="<?= URL_ROOT ?>post?id=<?= $post[ "BLOG_POST_ID" ] ?>#comment-section"><i class="fas fa-chevron-down text-muted"></i></a>

											</div>
										</div>
										<?php
									}
									?>
								</div>
							</div>
							<?php
						}
					} );
					?>
				</div>

			</div>

			<div class="row">
				<div class="col p-3">

					<div class="col d-flex justify-content-center">

						<button <?= $pagination->loadMoreButton() ?> class="btn btn-primary" type="button">More</button>

					</div>
				</div>
			</div>
		</div>

		<aside class="col-md-4">

			<div id="custom-sidebar-content">
				<?= HTML\get( "#custom-sidebar-content" ) ?>
			</div>

			<?php
			if( $user->powerLevel >= UserPowerLevels\MODERATOR )
			{
				?>
				<script>
					Libraries.HTML.makeEditable( "#custom-sidebar-content" );
				</script>
				<?php
			}
			?>

			<div>
				<?php include( "html/posts-sidebar.php" ) ?>
			</div>
		</aside>
	</div>


</main>

<?php
include_once( "html/page-footer.php" );
?>