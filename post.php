<?php
include_once( "utilities.php" );

$user = BlogUser::currentUser();

$blog = Blog::currentBlog();

if( GET( "id" ) == null )
{
	redirect( URL_ROOT );
}

$mainPost = $blog->getPost( GET( "id" ) );

if( $mainPost == false )
{
	redirect( URL_ROOT );
}

$form = new HTML\Form( "comment-form", "POST" );

$hidPostId = $form->hidden( "post-id" );
$txtComment = $form->textarea( "comment" );
$txtComment->pattern( RegEx\ANYTHING, "Please enter a comment." );
$btnSubmit = $form->button( "action", "submit" );

if( $form->submitted == true )
{
	if( $user->loggedIn == false )
	{
		HTML\Modal::show( "", "Please login to submit a comment." );
	}
	else
	{
		$blog->submitComment( $hidPostId->value, $user->userAccountId, trim( $txtComment->value ) );
		$txtComment->value = "";
	}
}
else
{
	$hidPostId->value = $mainPost[ "BLOG_POST_ID" ];

	if( POST( "action" ) == "delete-comment" && $user->powerLevel >= UserPowerLevels\MODERATOR )
	{
		if( $blog->deleteComment( POST( "comment-id" ) ) == false )
		{
			error_log( "Error: failed to delete comment." );
		}
		else
		{
			HTML\Modal::show( "", "Success: comment deleted." );
		}
	}
}

include_once( "html/page-header.php" );
?>

<main class="container py-5">

	<div class="row">
		<div class="col-md-1">

			<?php include( "html/social-media-sidebar.php" ) ?>

		</div>
		<div class="col-md-7 p-4 blog-post">

			<h1>
				<?= $mainPost[ "TITLE" ] ?>
				<?php
				if( $user->powerLevel >= UserPowerLevels\MODERATOR )
				{
					?>
					<a href="<?= URL_ROOT ?>create-or-edit-post?id=<?= $mainPost[ "BLOG_POST_ID" ] ?>"><i class="fas fa-edit"></i></a>
					<?php
				}
				?>
			</h1>
			<div class="mb-2">
				<small class="text-muted">
					<a href="#"><?= $mainPost[ "AUTHOR_ALIAS" ] ?></a> - <?= $mainPost[ "DATE_POSTED" ] ?>
				</small>
			</div>
			<div id="post-body" class="mb-4">
				<?= $mainPost[ "BODY_HTML" ] ?>
			</div>
			<div id="comment-section" class="post-comments mt-3">
				<h2 class="border-bottom pb-2">Comments</h2>

				<div class="mt-3">
					<form <?= $form ?> class="needs-validation" novalidate>
						<input <?= $hidPostId ?> type="hidden" />
						<textarea <?= $txtComment ?> class="form-control" placeholder="Comment..."><?= $txtComment->value ?></textarea>
						<button <?= $btnSubmit ?> type="submit" class="btn btn-primary w-100 mt-1">Submit</button>
					</form>

					<script>
						autoResizeTextarea( $( "[name='comment']" )[ 0 ] );
					</script>
				</div>
				<?php
				$pagination = new Pagination( "post-comments", $mainPost[ "COMMENT_COUNT" ], Blog::COMMENTS_PER_PAGE, GET( "page" ) );

				$pagination->page( function( ) {

					$user = BlogUser::currentUser();
					$blog = Blog::currentBlog();
					$comments = $blog->getPostComments( GET( "id" ), GET( "page" ) );

					foreach( $comments as $comment )
					{
						?>
						<form action="?id=<?= GET( "id" ) ?>" method="POST" class="comment pt-1 pb-1">
							<input name="comment-id" type="hidden" value="<?= $comment[ "BLOG_POST_COMMENT_ID" ] ?>" />
							<small>
								<a href="#"><?= $comment[ "AUTHOR_FIRST_NAME" ] . " " . $comment[ "AUTHOR_LAST_NAME" ] ?></a>
								<span class="text-muted"> • <?= timeSince( $comment[ "COMMENT_DATE" ], "Y-m-d H:i:s" ) ?></span>
							</small>

							<?php
							if( $user->powerLevel >= UserPowerLevels\MODERATOR )
							{
								?>
								<button class="btn btn-link float-right" name="action" value="delete-comment" type="submit"><i class="fas fa-trash-alt"></i></button>
								<?php
							}
							?>
							<div class="comment-body mb-2">
								<?= nl2br( htmlspecialchars( $comment[ "BODY" ], ENT_QUOTES, "UTF-8" ) ) ?>
							</div>
						</form>
						<?php
					}
				} );
				?>

			</div>
			<div>

				<button <?= $pagination->loadMoreButton() ?> class="btn btn-block btn-light"><i class="fas fa-chevron-down text-muted"></i></button>

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