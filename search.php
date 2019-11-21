<?php
include_once( "utilities.php" );

$user = BlogUser::currentUser();
$blog = Blog::currentBlog();

include_once( "html/page-header-heading.php" );

if( $searchForm->submitted == false || $txtSearch->value == "" )
{
	redirect( URL_ROOT );
}

include_once( "html/page-header-body.php" );
?>

<main class="container py-5">

	<div class="row">
		<div class="col-md-1">

			<?php include( "html/social-media-sidebar.php" ) ?>

		</div>
		<div class="col-md-7">

			<h2 class="mb-4">Results for: "<?= $txtSearch->value ?>"</h2>

			<div>
				<ul id="results-list" class="list-unstyled">

					<?php
					$pagination = new Pagination( "search-results-page", $blog->totalSearchPosts( GET( "search" ) ), Blog::POSTS_PER_PAGE, GET( "page" ) );

					$pagination->page( function( ) {
						$posts = Blog::currentBlog()->search( GET( "page" ), GET( "search" ) );

						foreach( $posts as $post )
						{
							?>

							<li class="mb-4">
								<div class="h4"><a href="<?= URL_ROOT ?>post?id=<?= $post[ "BLOG_POST_ID" ] ?>"><?= $post[ "TITLE" ] ?></a></div>
								<span class="small text-muted">
									<a href="#"><?= $post[ "AUTHOR_FIRST_NAME" ] . " " . $post[ "AUTHOR_LAST_NAME" ] ?></a> - <?= $post[ "DATE_POSTED" ] ?>
									<div class="text-muted"><?= $post[ "PREVIEW" ] ?></div>
								</span>
							</li>

							<?php
						}
					} );
					?>

				</ul>
			</div>

			<div class="row">

				<div class="col d-flex justify-content-center">

					<button <?= $pagination->loadMoreButton() ?> class="btn btn-primary" type="button">Load More</button>

				</div>
			</div>
		</div>


		<aside class="col-md-4">
			<?php include( "html/posts-sidebar.php" ) ?>

		</aside>
	</div>

</main>



<?php
include_once( "html/page-footer.php" );
?>