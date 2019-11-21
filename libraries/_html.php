<?php

namespace HTML;

include_once( "utilities.php" );

// Footer output buffer
\FooterBuffer::start();
?>
<!-- Generic Modal -->
<div class="modal fade" id="static-modal" tabindex="-1" role="dialog" aria-labelledby="failure-modal-label" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title"></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body text-center">
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="static-confirmation-modal" tabindex="-1" role="dialog" aria-labelledby="failure-modal-label" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title"></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body text-center">
			</div>
			<div class="modal-footer">
				<button type="button" class="btn no-button" data-dismiss="modal">No</button>
				<button type="button" class="btn yes-button" data-dismiss="modal">Yes</button>
			</div>
		</div>
	</div>
</div>
<?php
\FooterBuffer::end();

\FooterBuffer::onGet( function( ) {
	Modal::output();
	Form::tieAllToClient();
} );

// HTML\get( $key )
// Return the html content with the specified key from the database
function get( $key )
{
	$THC = new \SQL\Tables\html_content;

	if( !\SQL::query( "SELECT content AS CONTENT FROM $THC WHERE $THC->content_key = ?", $key ) )
	{
		error_log( "Error: HTML\get( ) failed." );
		return false;
	}
	else if( \SQL::rowCount() == 0 )
	{
		error_log( "Error: HTML\get( ) returned no results." );
		return false;
	}
	else
	{
		return \SQL::fetch()[ "CONTENT" ];
	}
}

// HTML\set( $key, $content )
// Insert/Update the specified html content in the database with the specified key
function set( $key, $content )
{
	$THC = new \SQL\Tables\html_content;

	if( !\SQL::query( "INSERT INTO $THC( $THC->content_key, $THC->content ) VALUES( ?, ? ) ON DUPLICATE KEY UPDATE $THC->content = ?", $key, $content, $content ) )
	{
		error_log( "Error: HTML\set( ) failed." );
		return false;
	}
	else
	{
		return true;
	}
}

class Element
{

	protected $attributes = [];

	public function setAttribute( $attribute, $value = "" )
	{
		$this->attributes[ $attribute ] = $value;
	}

	public function getAttribute( $attribute )
	{
		return $this->attributes[ $attribute ];
	}

	public function removeAttribute( $attribute )
	{
		unset( $this->attributes[ $attribute ] );
	}

	public function getAttributes()
	{
		$attributeString = " ";

		foreach( $this->attributes as $attribute => $value )
		{
			$valueString = "";

			if( $value != "" )
			{
				$valueString = "='$value'";
			}

			$attributeString .= " " . $attribute . $valueString;
		}

		return $attributeString;
	}

	public function __toString()
	{
		return $this->getAttributes();
	}

}

class Control extends Element
{

	public $form = null;
	public $name = "";
	public $value = null;

	public function __construct( $name, $form )
	{
		$this->name = $name;
		$this->form = $form;
	}

	public function copy( $source )
	{
		$this->form = $source->form;
		$this->name = $source->name;
		$this->value = $source->value;
		$this->attributes = $source->attributes;
	}

	public function getAttributes()
	{
		return " name='$this->name' value='$this->value' " . Element::getAttributes();
	}

	public function tieToClient()
	{
		echo "";
	}

}

// Class: Form
// Abstract: A class used to represent a form on this page, and to handle data validation on the client and server.
//			 Server: Call Form::textbox( ), Form::textarea, Form::button( ), etc, to represent controls,
//					 and call Field::pattern( ) to validate the data in the textboxes (if any). Etc...
//			 Client: Call Form::tieToClient( ) somewhere <script> tags are valid
// WARNING: Use this class (not markup) to set the following attributes:
//			-id
//			-method

class Form extends Element
{

	public static $allowResubmit = false;
	private static $forms = [];
	private $controls = [];
	// NOTE: This will be set to false when accessing a field that was no present in the GET/POST data
	public $submitted = true;
	public $valid = true;
	public $dataArray = [];

	public static function tieAllToClient()
	{
		foreach( self::$forms as $form )
		{
			$form->tieToClient();
		}
	}

	public function __construct( $id, $method = "GET" )
	{
		$this->setAttribute( "id", $id );
		$this->setAttribute( "method", $method );

		if( strtolower( trim( $method ) ) == "get" )
		{
			$this->dataArray = $_GET;
		}
		else if( strtolower( trim( $method ) ) == "post" )
		{
			$this->dataArray = $_POST;
		}

		array_push( self::$forms, $this );
	}

	// Clear all the data from the form controls (except buttons)
	public function reset()
	{
		foreach( $this->controls as $control )
		{
			if( !( $control instanceof Button ) && is_array( $control ) == false )
			{
				$control->value = "";
			}
		}
	}

	// Return a reference to the textarea with the specified "name" attribute.
	// If it doesn't exist, create it and indicate that this form hasn't been submitted.
	public function textarea( $name )
	{
		if( !array_key_exists( $name, $this->controls ) )
		{
			$this->controls[ $name ] = new Textarea( $name, $this );
		}

		if( !array_key_exists( $name, $this->dataArray ) )
		{
			$this->submitted = false;
		}
		else
		{
			$this->controls[ $name ]->provided = true;
			$this->controls[ $name ]->value = $this->dataArray[ $name ];
		}

		/**
		 * @return HTML\Textarea The field with the specified name
		 */
		return $this->controls[ $name ];
	}

	// Return a reference to the input field (generic) with the specified "name" attribute.
	// If it doesn't exist, create it and indicate that this form hasn't been submitted.
	public function field( $name )
	{
		if( !array_key_exists( $name, $this->controls ) )
		{
			$this->controls[ $name ] = new Textbox( $name, $this );
		}

		if( !array_key_exists( $name, $this->dataArray ) )
		{
			$this->submitted = false;
		}
		else
		{
			$this->controls[ $name ]->provided = true;
			$this->controls[ $name ]->value = $this->dataArray[ $name ];
		}

		/**
		 * @return HTML\Textbox The field with the specified name
		 */
		return $this->controls[ $name ];
	}

	// Return a reference to the button with the specified "name" attribute.
	// If it doesn't exist, create it and indicate that this form hasn't been submitted.
	public function button( $name, $value )
	{
		// controls[ name ][ value ] = Button
		// If there is no button with this name/value yet
		if( !array_key_exists( $name, $this->controls ) )
		{
			$this->controls[ $name ] = [];
		}

		if( !array_key_exists( $value, $this->controls[ $name ] ) )
		{
			$this->controls[ $name ][ $value ] = new Button( $name, $value, $this );
		}

		if( !array_key_exists( $name, $this->dataArray ) )
		{
			$this->submitted = false;
		}
		else if( $this->controls[ $name ][ $value ]->value === $this->dataArray[ $name ] )
		{
			$this->controls[ $name ][ $value ]->clicked = true;
		}

		/**
		 * @return HTML\Button The button with the specified name
		 */
		return $this->controls[ $name ][ $value ];
	}

	// Return a reference to the hidden input with the specified "name" attribute.
	// If it doesn't exist, create it and indicate that this form hasn't been submitted.
	public function hidden( $name )
	{
		if( !array_key_exists( $name, $this->controls ) )
		{
			$this->controls[ $name ] = new Hidden( $name, $this );
		}

		if( !array_key_exists( $name, $this->dataArray ) )
		{
			$this->submitted = false;
		}
		else
		{
			$this->controls[ $name ]->provided = true;
			$this->controls[ $name ]->value = $this->dataArray[ $name ];
		}

		/**
		 * @return HTML\Hidden The field with the specified name
		 */
		return $this->controls[ $name ];
	}

	public function tieToClient()
	{
		foreach( $this->controls as $field )
		{
			if( is_array( $field ) == false )
			{
				$field->tieToClient();
			}
			else
			{
				foreach( $field as $subField )
				{
					$subField->tieToClient();
				}
			}
		}
	}

}

class Textbox extends Control
{

	public $provided = false;
	public $patterns = null;
	public $matchField = null;
	public $required = "";
	public $valid = true;
	public $invalidMessage = "";

	public function pattern( array $patterns, $invalidMessage = "Value is not valid." )
	{
		$this->patterns = $patterns;
		$this->invalidMessage = $invalidMessage;

		if( count( $patterns ) > 0 )
		{
			$this->required = "required";
		}

		$this->validateValue();
	}

	public function match( $field, $invalidMessage = "Values must match." )
	{
		$this->invalidMessage = $invalidMessage;

		$this->matchField = $field;

		$this->validateValue();
	}

	// Expects the pattern to be set prior to calling (if any)
	public function validateValue( $value = "" )
	{
		if( $value == "" )
		{
			$value = $this->value;
		}

		if( $this->patterns != null )
		{
			$result = preg_match( $this->patterns[ \RegEx\PHP ], $value );

			if( $result === false )
			{
				error_log( "Error in Field::validateValue( ): preg error " . preg_last_error() );
				exit();
			}
			else if( $result === 0 )
			{
				$this->form->valid = false;
				$this->valid = false;
				return false;
			}
		}

		if( $this->matchField != null && $this->value != $this->matchField->value )
		{
			$this->form->valid = false;
			$this->valid = false;
			return false;
		}

		return true;
	}

	public function tieToClient()
	{
		?>
		<script>
			( function( ) {
				var form = document.getElementById( "<?= $this->form->getAttribute( "id" ) ?>" );
				var fields;
				var element;
				var invalidInputElement;
				if( form != null )
				{
					fields = form.querySelectorAll( "[name='<?= $this->name ?>']" );

					if( fields.length > 0 )
					{
						element = null;
						fields.forEach( function( field ) {
							element = field;
		<?php
		if( $this->provided == true && $this->valid == false )
		{
			?>
								field.className += " is-invalid";
			<?php
		}

		if( $this->matchField != null )
		{
			?>
								var matchField = form.querySelector( "[name='<?= $this->matchField->name ?>']" );
								function validateMatch( )
								{
									if( field.value != matchField.value )
									{
										field.setCustomValidity( "<?= $this->invalidMessage ?>" );
									}else
									{
										field.setCustomValidity( "" );
									}
								}

								matchField.onchange = validateMatch;
								field.onkeyup = validateMatch;
			<?php
		}
		?>

						} );
						invalidInputElement = document.createElement( "DIV" );
						invalidInputElement.className = "invalid-feedback";
						invalidInputElement.innerText = "<?= $this->invalidMessage ?>";
						element.parentNode.insertBefore( invalidInputElement, element.nextSibling );
					}
				}
			} )( );
		</script>
		<?php
	}

	public function getAttributes()
	{
		$attributes = " name='$this->name' value='" . htmlentities( preg_replace( "/\s+/", " ", $this->value ), ENT_QUOTES, "UTF-8" ) . "' $this->required " . Element::getAttributes();

		if( $this->patterns != null && $this->patterns[ \RegEx\JS ] != "" )
		{
			$attributes .= " pattern = '" . $this->patterns[ \RegEx\JS ] . "'";
		}

		return $attributes;
	}

}

class Textarea extends Textbox
{

	public function getAttributes()
	{

		$attributes = " name='$this->name' $this->required " . Element::getAttributes();

		if( $this->patterns != null && $this->patterns[ \RegEx\JS ] != "" )
		{
			$attributes .= " pattern = '" . $this->patterns[ \RegEx\JS ] . "'";
		}

		return $attributes;
	}

}

class Button extends Control
{

	public $clicked = false;

	public function __construct( $name, $value, $form )
	{
		parent::__construct( $name, $form );
		$this->value = $value;
	}

}

class Hidden extends Control
{

	public function __construct( $name, $form )
	{
		parent::__construct( $name, $form );
	}

}

class Modal extends Element
{

	private static $modal = null;
	protected $title = "";
	protected $body = "";
	protected $classes = "";

	public function __construct( $title, $body, $classes = "" )
	{
		$this->title = $title;
		$this->body = $body;
		$this->classes = $classes;
	}

	public static function show( $title, $body, $classes = "" )
	{
		Modal::$modal = new Modal( $title, $body, $classes );

		if( AJAX == true )
		{
			echo Modal::$modal;
		}
	}

	public static function showWarning( $title, $body, $classes = "alert alert-warning mb-0" )
	{
		Modal::$modal = new Modal( $title, $body, $classes );
	}

	public static function showDanger( $title, $body, $classes = "alert alert-danger mb-0" )
	{
		Modal::$modal = new Modal( $title, $body, $classes );
	}

	public static function output()
	{
		echo Modal::$modal;
	}

	public function __toString()
	{
		ob_start();
		?>
		<script>
			$( document ).ready( function( ) {
				let modal = new Libraries.Modal( "<?= $this->title ?>", "<?= $this->body ?>", "<?= $this->classes ?>" );
				modal.show( );
			} );
		</script>

		<?php
		return ob_get_clean();
	}

}
?>