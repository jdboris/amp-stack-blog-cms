<?php

// WARNING: Before updating the file, use your editor's refectoring (if any) to update references first

namespace SQL\Tables
{

	class blog_post_comments
	{

		public $blog_post_comment_id = "blog_post_comments.blog_post_comment_id";
		public $blog_post_id = "blog_post_comments.blog_post_id";
		public $author_id = "blog_post_comments.author_id";
		public $comment_date = "blog_post_comments.comment_date";
		public $body = "blog_post_comments.body";
		public $active = "blog_post_comments.active";

		public function __toString()
		{
			return "blog_post_comments";
		}

	}

	class blog_posts
	{

		public $blog_post_id = "blog_posts.blog_post_id";
		public $author_id = "blog_posts.author_id";
		public $title = "blog_posts.title";
		public $author_alias = "blog_posts.author_alias";
		public $preview = "blog_posts.preview";
		public $body_text = "blog_posts.body_text";
		public $body_html = "blog_posts.body_html";
		public $date_posted = "blog_posts.date_posted";
		public $active = "blog_posts.active";

		public function __toString()
		{
			return "blog_posts";
		}

	}

	class email_verification_tokens
	{

		public $email_verification_token_id = "email_verification_tokens.email_verification_token_id";
		public $user_account_id = "email_verification_tokens.user_account_id";
		public $email_verification_token = "email_verification_tokens.email_verification_token";
		public $expiration_date = "email_verification_tokens.expiration_date";

		public function __toString()
		{
			return "email_verification_tokens";
		}

	}

	class html_content
	{

		public $html_content_id = "html_content.html_content_id";
		public $content_key = "html_content.content_key";
		public $content = "html_content.content";

		public function __toString()
		{
			return "html_content";
		}

	}

	class password_change_tokens
	{

		public $password_change_token_id = "password_change_tokens.password_change_token_id";
		public $user_account_id = "password_change_tokens.user_account_id";
		public $password_change_token = "password_change_tokens.password_change_token";
		public $expiration_date = "password_change_tokens.expiration_date";

		public function __toString()
		{
			return "password_change_tokens";
		}

	}

	class user_account_status_history
	{

		public $user_account_status_history_id = "user_account_status_history.user_account_status_history_id";
		public $user_account_id = "user_account_status_history.user_account_id";
		public $user_account_status_id = "user_account_status_history.user_account_status_id";
		public $date = "user_account_status_history.date";

		public function __toString()
		{
			return "user_account_status_history";
		}

	}

	class user_account_statuses
	{

		public $user_account_status_id = "user_account_statuses.user_account_status_id";
		public $user_account_status = "user_account_statuses.user_account_status";

		public function __toString()
		{
			return "user_account_statuses";
		}

	}

	class user_accounts
	{

		public $user_account_id = "user_accounts.user_account_id";
		public $email_address = "user_accounts.email_address";
		public $password_hash = "user_accounts.password_hash";
		public $access_hash = "user_accounts.access_hash";
		public $power_level = "user_accounts.power_level";
		public $first_name = "user_accounts.first_name";
		public $middle_name = "user_accounts.middle_name";
		public $last_name = "user_accounts.last_name";
		public $phone_number = "user_accounts.phone_number";
		public $join_date = "user_accounts.join_date";
		public $status_id = "user_accounts.status_id";
		public $email_verified = "user_accounts.email_verified";

		public function __toString()
		{
			return "user_accounts";
		}

	}

	class user_login_history
	{

		public $login_id = "user_login_history.login_id";
		public $user_account_id = "user_login_history.user_account_id";
		public $login_date = "user_login_history.login_date";
		public $ip_address = "user_login_history.ip_address";

		public function __toString()
		{
			return "user_login_history";
		}

	}

}
?>