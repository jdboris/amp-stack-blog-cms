<?php

include_once( "utilities.php" );

class Blog
{

	const POSTS_PER_PAGE = 4;
	const COMMENTS_PER_PAGE = 10;
	const USERS_PER_PAGE = 10;

	public $totalPosts = 0;
	public $totalUsers = 0;
	private static $currentBlog = null;

	// ------------------------------------------------------------------------
	// Function: currentBlog
	// Abstract: Get the current user. If there is none, create a new instance
	// ------------------------------------------------------------------------
	public static function currentBlog()
	{
		if( self::$currentBlog == null )
		{
			self::$currentBlog = new self( );
		}

		/**
		 * @return Blog The current Blog.
		 */
		return self::$currentBlog;
	}

	// ------------------------------------------------------------------------
	// Function: __construct
	// Abstract:
	// ------------------------------------------------------------------------
	public function __construct()
	{
		$TBP = new SQL\Tables\blog_posts;
		$TUA = new SQL\Tables\user_accounts;

		SQL::query( "SELECT count( * ) FROM $TBP WHERE $TBP->active = TRUE" );
		$this->totalPosts = SQL::fetchColumn();
		SQL::query( "SELECT count( * ) FROM $TUA " );
		$this->totalUsers = SQL::fetchColumn();
	}

	// ------------------------------------------------------------------------
	// Function: getPosts
	// Abstract: Returns all blog posts from the database, or false if error
	// ------------------------------------------------------------------------
	public function getPosts( $page, string $authorEmailAddress = "", $postsPerPage = self::POSTS_PER_PAGE )
	{
		if( $page == null )
			$page = 1;
		$result = [];

		$TBP = new SQL\Tables\blog_posts( );
		$TUA = new SQL\Tables\user_accounts( );

		$query = "
			SELECT

				$TBP->blog_post_id	AS BLOG_POST_ID,
				$TBP->author_id		AS AUTHOR_ID,
				$TBP->title			AS TITLE,
				$TBP->author_alias	AS AUTHOR_ALIAS,
				$TBP->date_posted	AS DATE_POSTED,
				$TBP->preview		AS PREVIEW,
				$TBP->body_text		AS BODY_TEXT,
				$TBP->body_html		AS BODY_HTML,
				$TUA->first_name	AS AUTHOR_FIRST_NAME,
				$TUA->last_name		AS AUTHOR_LAST_NAME
			FROM
				$TBP INNER JOIN $TUA
				ON( $TBP->author_id = $TUA->user_account_id )
			WHERE
				(? = ''
			OR	$TUA->email_address = ?)
			AND	$TBP->active = TRUE
				ORDER BY
				$TBP->date_posted DESC
			LIMIT
				" . ( ( $page - 1 ) * $postsPerPage ) . "," . $postsPerPage . "
		";

		if( !SQL::query( $query, $authorEmailAddress, $authorEmailAddress ) )
		{
			$result = false;
		}
		else
		{
			while( $row = SQL::fetch() )
			{
				// TODO: Don't do this here
				$row[ "DATE_POSTED" ] = DateTime::createFromFormat( "Y-m-d H:i:s", $row[ "DATE_POSTED" ] )->format( "M j, Y g:i A" );

				array_push( $result, $row );
			}
		}

		return $result;
	}

	// ------------------------------------------------------------------------
	// Function: getPost
	// Abstract: Returns the post with the specified ID
	// ------------------------------------------------------------------------
	public function getPost( int $id )
	{
		$row = false;

		$TBP = new SQL\Tables\blog_posts( );
		$TUA = new SQL\Tables\user_accounts( );
		$TBPC = new SQL\Tables\blog_post_comments( );

		$query = "
			SELECT
				$TBP->blog_post_id						AS BLOG_POST_ID,
				$TBP->author_id							AS AUTHOR_ID,
				$TBP->title								AS TITLE,
				$TBP->author_alias						AS AUTHOR_ALIAS,
				$TBP->date_posted						AS DATE_POSTED,
				$TBP->preview							AS PREVIEW,
				$TBP->body_text							AS BODY_TEXT,
				$TBP->body_html							AS BODY_HTML,
				$TUA->first_name						AS AUTHOR_FIRST_NAME,
				$TUA->last_name							AS AUTHOR_LAST_NAME,
				COUNT( $TBPC->blog_post_comment_id )	AS COMMENT_COUNT
			FROM
				$TBP INNER JOIN $TUA
				ON( $TBP->author_id = $TUA->user_account_id )
				LEFT JOIN $TBPC
				ON( $TBP->blog_post_id = $TBPC->blog_post_id && $TBPC->active = TRUE )
			WHERE
				$TBP->blog_post_id = ?
			AND	$TBP->active = TRUE
			GROUP BY
				$TBP->blog_post_id
		";

		if( !SQL::query( $query, $id ) )
		{
			error_log( "Error: Post select query failed." );
		}
		else
		{
			if( $row = SQL::fetch() )
			{
				// TODO: Don't do this here
				$row[ "DATE_POSTED" ] = DateTime::createFromFormat( "Y-m-d H:i:s", $row[ "DATE_POSTED" ] )->format( "M j, Y g:i A" );
			}
		}

		return $row;
	}

	// ------------------------------------------------------------------------
	// Function: getPostComments
	// Abstract: Returns the comments on the post with the specified ID
	// ------------------------------------------------------------------------
	public function getPostComments( int $id, $page, int $commentsPerPage = self::COMMENTS_PER_PAGE )
	{
		$rows = [];

		if( $page == null || $page < 1 )
		{
			$page = 1;
		}

		$TBP = new SQL\Tables\blog_posts( );
		$TUA = new SQL\Tables\user_accounts( );
		$TBPC = new SQL\Tables\blog_post_comments( );

		$query = "
			SELECT
				$TBPC->author_id			AS AUTHOR_ID,
				$TBPC->blog_post_comment_id	AS BLOG_POST_COMMENT_ID,
				$TBPC->comment_date			AS COMMENT_DATE,
				$TBPC->body					AS BODY,
				$TUA->first_name			AS AUTHOR_FIRST_NAME,
				$TUA->last_name				AS AUTHOR_LAST_NAME
			FROM
				$TBPC INNER JOIN $TBP
				ON( $TBPC->blog_post_id = $TBP->blog_post_id )
				INNER JOIN $TUA
				ON( $TBPC->author_id = $TUA->user_account_id )
			WHERE
				$TBP->blog_post_id = ?
			AND	$TBPC->active = TRUE
			ORDER BY
				COMMENT_DATE DESC
			LIMIT
				" . ( ( $page - 1 ) * $commentsPerPage ) . "," . $commentsPerPage . "
		";

		if( SQL::query( $query, $id ) )
		{
			while( $row = SQL::fetch() )
			{
				array_push( $rows, $row );
			}
		}

		return $rows;
	}

	// ------------------------------------------------------------------------
	// Function: submitComment
	// Abstract: Submits a comment on the specified blog post
	// ------------------------------------------------------------------------
	public function submitComment( int $postId, int $authorId, $comment )
	{
		$result = false;

		$TBPC = new SQL\Tables\blog_post_comments( );

		$query = "
			INSERT INTO $TBPC( $TBPC->blog_post_id, $TBPC->author_id, $TBPC->comment_date, $TBPC->body, $TBPC->active )
			VALUES( ?, ?, '" . date( "Y-m-d H:i:s" ) . "', ?, TRUE )
		";

		$result = SQL::query( $query, $postId, $authorId, $comment );

		return $result;
	}

	// ------------------------------------------------------------------------
	// Function: deleteComment
	// Abstract: Deletes a comment with the specified ID
	// ------------------------------------------------------------------------
	public function deleteComment( int $commentId )
	{
		$result = false;

		$TBPC = new SQL\Tables\blog_post_comments( );

		$query = "
			UPDATE
				$TBPC
			SET
				$TBPC->active = FALSE
			WHERE
				$TBPC->blog_post_comment_id = ?
		";

		$result = SQL::query( $query, $commentId );

		return $result;
	}

	// ------------------------------------------------------------------------
	// Function: search
	// Abstract: Returns results for the specified search term
	// ------------------------------------------------------------------------
	public function search( $page, String $search )
	{
		$result = [];

		if( $page == null || $page < 1 )
		{
			$page = 1;
		}

		$TBP = new SQL\Tables\blog_posts( );
		$TUA = new SQL\Tables\user_accounts( );

		$query = "
			SELECT
				$TBP->blog_post_id	AS BLOG_POST_ID,
				$TBP->author_id		AS AUTHOR_ID,
				$TBP->title			AS TITLE,
				$TBP->author_alias	AS AUTHOR_ALIAS,
				$TBP->date_posted	AS DATE_POSTED,
				$TBP->preview		AS PREVIEW,
				$TBP->body_text		AS BODY_TEXT,
				$TBP->body_html		AS BODY_HTML,
				$TUA->first_name	AS AUTHOR_FIRST_NAME,
				$TUA->last_name		AS AUTHOR_LAST_NAME,
				SUM( MATCH( $TBP->body_text ) AGAINST( ? IN BOOLEAN MODE ) ) AS SCORE
			FROM
				$TBP INNER JOIN $TUA
				ON( $TBP->author_id = $TUA->user_account_id )
			WHERE
				MATCH( $TBP->body_text ) AGAINST( ? IN BOOLEAN MODE )
			AND	$TBP->active = TRUE
			GROUP BY
				$TBP->blog_post_id
			ORDER BY
				SCORE DESC
			LIMIT
				" . ( ( $page - 1 ) * self::POSTS_PER_PAGE ) . "," . self::POSTS_PER_PAGE . "
		";

		if( !SQL::query( $query, $search, $search ) )
		{
			$result = false;
		}
		else
		{
			while( $row = SQL::fetch() )
			{
				// TODO: Don't do this here
				$row[ "DATE_POSTED" ] = DateTime::createFromFormat( "Y-m-d H:i:s", $row[ "DATE_POSTED" ] )->format( "M j, Y g:i A" );

				array_push( $result, $row );
			}
		}

		return $result;
	}

	// ------------------------------------------------------------------------
	// Function: totalSearchPosts
	// Abstract: Returns the number of results returned from the specified search
	// ------------------------------------------------------------------------
	public function totalSearchPosts( String $search )
	{
		$TBP = new SQL\Tables\blog_posts( );

		$query = "
			SELECT
				COUNT( * )
			FROM
				$TBP
			WHERE
				MATCH( $TBP->body_text ) AGAINST( ? IN BOOLEAN MODE )
			AND	$TBP->active = TRUE
		";

		if( !SQL::query( $query, $search ) )
		{
			$result = false;
		}
		else
		{
			$result = SQL::fetchColumn();
		}

		return $result;
	}

	// ------------------------------------------------------------------------
	// Function: userSearch
	// Abstract: Returns user results for the specified search term
	// ------------------------------------------------------------------------
	public function userSearch( $page, $search )
	{
		$result = [];

		if( $search == null )
			$search = "";

		if( $page == null || $page < 1 )
		{
			$page = 1;
		}

		$TUA = new SQL\Tables\user_accounts( );

		$query = "
			SELECT
				$TUA->user_account_id	AS USER_ID,
				$TUA->first_name		AS FIRST_NAME,
				$TUA->middle_name		AS MIDDLE_NAME,
				$TUA->last_name			AS LAST_NAME,
				$TUA->email_address		AS EMAIL_ADDRESS,
				$TUA->join_date			AS JOIN_DATE,
				$TUA->power_level		AS POWER_LEVEL
			FROM
				$TUA
			WHERE
				$TUA->email_address LIKE ?
			OR	$TUA->first_name LIKE ?
			OR	$TUA->middle_name LIKE ?
			OR	$TUA->last_name LIKE ?
			GROUP BY
				$TUA->user_account_id
			ORDER BY
				$TUA->join_date DESC
			LIMIT
				" . ( ( $page - 1 ) * self::USERS_PER_PAGE ) . "," . self::USERS_PER_PAGE . "
		";


		if( !SQL::query( $query, "%$search%", "%$search%", "%$search%", "%$search%" ) )
		{
			$result = false;
		}
		else
		{
			while( $row = SQL::fetch() )
			{
				// TODO: Don't do this here
				$row[ "JOIN_DATE" ] = DateTime::createFromFormat( "Y-m-d H:i:s", $row[ "JOIN_DATE" ] )->format( "M j, Y g:i A" );

				array_push( $result, $row );
			}
		}

		return $result;
	}

}

?>