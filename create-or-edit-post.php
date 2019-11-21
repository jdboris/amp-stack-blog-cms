<?php
include_once( "utilities.php" );
$user = BlogUser::currentUser();
$blog = Blog::currentBlog();

if( $user->powerLevel < UserPowerLevels\MODERATOR )
{
	header( $_SERVER[ "SERVER_PROTOCOL" ] . " 404 Not Found", true, 404 );
	exit();
}

if( $user->loggedIn == false )
{
	redirect( URL_ROOT );
}

$form = new HTML\Form( "blog-post-form", "POST" );

$txtTitle = $form->field( "title" );
$txtTitle->pattern( RegEx\ANYTHING );

$txtAuthorAlias = $form->field( "author-alias" );
$txtAuthorAlias->pattern( RegEx\ANYTHING );

$txtDate = $form->field( "date" );
$txtDate->pattern( RegEx\ANYTHING );

$txtPreview = $form->field( "preview" );
$txtPreview->pattern( RegEx\ANYTHING );

$txtBodyHTML = $form->textarea( "body-html" );

$txtBodyText = $form->textarea( "body-text" );

$hidEditPostId = $form->field( "edit-post-id" );

$btnSubmit = $form->button( "action", "Post" );

$heading = "New Post";

if( $form->submitted == false )
{
	$hidEditPostId->value = 0;
	$txtAuthorAlias->value = $user->firstName . " " . $user->lastName;
	$txtDate->value = date( "Y-m-d\TH:i" );

	if( GET( "id" ) !== null )
	{
		$heading = "Edit Post";
		$hidEditPostId->value = GET( "id" );
		$post = $blog->getPost( GET( "id" ) );
		$txtTitle->value = $post[ "TITLE" ];
		$txtAuthorAlias->value = $post[ "AUTHOR_ALIAS" ];

		$txtDate->value = DateTime::createFromFormat( "M j, Y g:i A", $post[ "DATE_POSTED" ] )->format( "Y-m-d\TH:i" );
		$txtPreview->value = $post[ "PREVIEW" ];
		$txtBodyHTML->value = $post[ "BODY_HTML" ];
		$txtBodyText->value = $post[ "BODY_TEXT" ];
		$btnSubmit->value = "Save";
	}
}
else
{
	if( GET( "id" ) == null )
	{
		$postID = $user->submitBlogPost( $txtTitle->value, $txtAuthorAlias->value, $txtDate->value, $txtBodyHTML->value, $txtBodyText->value, $txtPreview->value );

		if( $postID == false )
		{
			HTML\Modal::showWarning( "", Modal\GENERAL_ERROR );
		}
		else
		{
			redirect( URL_ROOT . "post?id=" . $postID . "&_REQUEST_LIBRARY=MODAL&modal-body=POST_CREATE_SUCCESS" );
		}
	}
	else
	{
		$post = $user->editBlogPost( $hidEditPostId->value, $txtTitle->value, $txtAuthorAlias->value, $txtDate->value, $txtBodyHTML->value, $txtBodyText->value, $txtPreview->value );

		if( $post == false )
		{
			HTML\Modal::showWarning( "", Modal\GENERAL_ERROR );
		}
		else
		{
			redirect( URL_ROOT . "post?id=" . $hidEditPostId->value . "&_REQUEST_LIBRARY=MODAL&modal-body=POST_EDIT_SUCCESS" );
		}
	}
}


include_once( "html/page-header.php" );
?>

<main class="container py-5">
	<div class="row">
		<div class="col-md">
			<h2><?= $heading ?></h2>
		</div>
	</div>
	<div class="row">
		<div class="col-md">
			<?php
			if( $user->emailVerified == false )
			{
				?>
				<div class="alert alert-warning">Please verify your email address before submitting new blog posts.</div>
				<?php
			}
			else
			{
				?>

				<form <?= $form ?> class="needs-validation pt-3" novalidate onsubmit="loadBodyValue( )">

					<input <?= $hidEditPostId ?> type="hidden" />

					<div class="form-row d-flex mb-3">
						<div class="col-md-3">
							<label>Title:</label>
							<input <?= $txtTitle ?> type="text" class="form-control" placeholder="New Blog Post">
						</div>
						<div class="col-md-3">
							<label>Author Alias:</label>
							<input <?= $txtAuthorAlias ?> type="text" class="form-control" placeholder="John Smith">
						</div>
						<div class="col-md-3">
							<label>Post Date:</label> <input <?= $txtDate ?> type="datetime-local" class="form-control ">
						</div>

					</div>


					<div class="form-row mb-3">
						<div class="col-md-12">
							<label>Preview:</label> <input <?= $txtPreview ?> class="form-control" type="text" placeholder="In this post, we'll discuss..." data-toggle="tooltip" title="Leave this blank to default to the first 200 characters of the post body."/>
						</div>
					</div>

					<div class="form-row">
						<div class="col-md-12 mb-3">
							<input <?= $txtBodyHTML ?> class="form-control" type="text" hidden>
							<div id="body-html-buffer" hidden disabled><?= $txtBodyHTML->value ?></div>

							<input <?= $txtBodyText ?> class="form-control" type="text" hidden />
							<div id="body-text-buffer" hidden disabled><?= $txtBodyText->value ?></div>

						</div>
					</div>

					<div class="form-row">
						<div class="col-md-12 mb-3">
							<?php
							if( GET( "id" ) !== null )
							{
								?>
								<input class="btn btn-danger" type="button" onclick="confirmDelete( )" value="Delete Post">
								<?php
							}
							?>
							<input <?= $btnSubmit ?> type="submit" class="btn btn-primary float-right" id="post-button">
						</div>
					</div>
				</form>

				<?php
				if( GET( "id" ) !== null )
				{
					?>
					<form id="delete-form" method="POST" action="delete-post" hidden>
						<input type="hidden" name="id" value="<?= $post[ "BLOG_POST_ID" ] ?>" />
					</form>
					<?php
				}
				?>

				<script>
					function loadBodyValue( )
					{
						let convertedText = $( $.parseHTML( $( "[name='<?= $txtBodyHTML->name ?>']" ).summernote( "code" ) ) ).text( );
						$( "[name='<?= $txtBodyHTML->name ?>']" ).prop( "value", $( "[name='<?= $txtBodyHTML->name ?>']" ).summernote( "code" ) );
						$( "[name='<?= $txtBodyText->name ?>']" ).prop( "value", convertedText );

						if( $( "[name='<?= $txtPreview->name ?>']" ).prop( "value" ) === "" )
						{
							let summary = convertedText.substring( 0, 197 ) + "...";
							$( "[name='<?= $txtPreview->name ?>']" ).prop( "value", summary );
						}
					}

					$( "[name='<?= $txtBodyHTML->name ?>']" ).summernote( {
						minHeight: 400
					} );

					$( "[name='<?= $txtBodyHTML->name ?>']" ).summernote( "code", $( "#body-html-buffer" ).html( ) );
					$( "[name='<?= $txtBodyText->name ?>']" ).html( $( "#body-html-buffer" ).text( ) );

					function confirmDelete( )
					{
						let modal = new Libraries.ConfirmationModal( "Warning", "Are you sure you want to delete this post?", Libraries.Colors.DANGER );

						modal.yesButton( "Delete", function( ) {
							$( "#delete-form" ).submit( );
						}, Libraries.Colors.DANGER );

						modal.noButton( "Cancel", function( ) {

						} );

						modal.show( );
					}
				</script>

				<?php
			}
			?>
		</div>
	</div>
</main>

<?php
include_once( "html/page-footer.php" );
?>