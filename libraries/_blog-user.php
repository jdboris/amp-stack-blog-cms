<?php

include_once( "utilities.php" );

class BlogUser extends User
{

	private static $currentUser = null;
	public $totalPosts = 0;

	// ------------------------------------------------------------------------
	// Function: currentUser
	// Abstract: Get the current user. If there is none, create a new instance
	// ------------------------------------------------------------------------
	public static function currentUser()
	{
		if( self::$currentUser == null )
		{
			self::$currentUser = new self( );
			self::$currentUser->checkAuthorization();

			if( self::$currentUser->loggedIn == true )
			{
				$TBP = new SQL\Tables\blog_posts;

				SQL::query( "SELECT count( * ) FROM $TBP WHERE $TBP->author_id = " . self::$currentUser->userAccountId );
				self::$currentUser->totalPosts = SQL::fetchColumn();
				error_log( "POWER LEVEL: " . self::$currentUser->powerLevel);
			}
		}

		/**
		 * @return BlogUser The current User.
		 */
		return self::$currentUser;
	}

	// ------------------------------------------------------------------------
	// Function: submitBlogPost
	// Abstract: Submits a new blog post as this user
	// ------------------------------------------------------------------------
	public function submitBlogPost( $title, $authorAlias, $date, $bodyHTML, $bodyText, $preview )
	{
		$result = false;
		error_log( $date );
		$date = DateTime::createFromFormat( "Y-m-d\TH:i", $date )->format( "Y-m-d H:i:s" );

		$TBP = new SQL\Tables\blog_posts( );

		if( $this->loggedIn == true && $this->emailVerified == true )
		{
			$query = "
				INSERT INTO $TBP( $TBP->author_id, $TBP->title, $TBP->author_alias, $TBP->date_posted, $TBP->body_html, $TBP->body_text, $TBP->preview, $TBP->active )
				VALUES( $this->userAccountId, ?, ?, ?, ?, ?, ?, TRUE )
			";

			$result = SQL::query( $query, $title, $authorAlias, $date, $bodyHTML, $bodyText, $preview );

			if( $result )
			{
				$result = SQL::lastInsertID();
			}
		}

		return $result;
	}

	// ------------------------------------------------------------------------
	// Function: editBlogPost
	// Abstract: Submits a new blog post as this user
	// ------------------------------------------------------------------------
	public function editBlogPost( $id, $title, $authorAlias, $date, $bodyHTML, $bodyText, $preview )
	{
		$result = false;
		$date = DateTime::createFromFormat( "Y-m-d\TH:i", $date )->format( "Y-m-d H:i:s" );

		$TBP = new SQL\Tables\blog_posts( );

		if( $this->loggedIn == true && $this->emailVerified == true )
		{
			$query = "
				UPDATE
					$TBP
				SET
					$TBP->title = ?,
					$TBP->author_alias = ?,
					$TBP->date_posted = ?,
					$TBP->body_html = ?,
					$TBP->body_text = ?,
					$TBP->preview = ?
				WHERE
					$TBP->blog_post_id = ?
			";

			$result = SQL::query( $query, $title, $authorAlias, $date, $bodyHTML, $bodyText, $preview, $id );
		}

		return $result;
	}

	// ------------------------------------------------------------------------
	// Function: deleteBlogPost
	// Abstract: Deletes a blog post as this user
	// ------------------------------------------------------------------------
	public function deleteBlogPost( $id )
	{
		$result = false;
		//$date = DateTime::createFromFormat( "Y-m-d\TH:i", $date )->format( "Y-m-d H:i:s" );

		$TBP = new SQL\Tables\blog_posts( );

		if( $this->loggedIn == true && $this->emailVerified == true )
		{
			$query = "
				UPDATE
					$TBP
				SET
					$TBP->active = FALSE
				WHERE
					$TBP->blog_post_id = ?
			";

			$result = SQL::query( $query, $id );
		}

		return $result;
	}

}

?>