<?php
include_once( "utilities.php" );
?>

<script>
	if( typeof ( Libraries.Pagination ) === "undefined" )
	{
		Libraries.Pagination = class Pagination
		{
			constructor( pageFileName )
			{
				this.pageFileName = pageFileName;
				this.pageListElement = null;
				this.page = 0;
				this.totalPages = 0;
			}

			loadMore( button = null, resultContainerSelector = "" )
			{
				let pagination = this;

				$.get( "?<?= http_build_query( $_GET ) ?>&_REQUEST_LIBRARY=VIRTUAL_FILE&file-name=" + this.pageFileName + "&page=" + ( this.page + 1 ), function( text ) {

					pagination.page += 1;

					if( button !== null && text === "" || pagination.page >= pagination.totalPages )
					{
						$( button ).hide( );
					}

					if( resultContainerSelector !== "" )
						$( resultContainerSelector )[ 0 ].innerHTML += text;
					else
					{
						$( pagination.pageListElement )[ 0 ].innerHTML += text;
					}
				} );
			}
		};

		Libraries.Pagination.paginations = { };
	}
</script>

<?php

class Pagination
{

	private $pageFileName = "";
	private $totalItems = 0;
	private $pageNumber = 1;
	private $totalPages = 0;
	private $itemCount = 0;

	public function __construct( string $pageFileName, int $totalItems, int $itemsPerPage, $pageNumber )
	{
		$this->pageFileName = $pageFileName;

		if( $pageNumber == null )
			$this->pageNumber = 1;
		else
			$this->pageNumber = $pageNumber;

		$this->totalItems = $totalItems;
		$this->totalPages = ceil( $totalItems / $itemsPerPage );
		?>
		<script>
			Libraries.Pagination.paginations[ "<?= $this->pageFileName ?>" ] = new Libraries.Pagination( "<?= $this->pageFileName ?>" );
		</script>
		<?php
	}

	// Captures an anonymous function to print the current "page" of items
	public function page( Closure $callback )
	{
		$result = includeVirtual( $this->pageFileName, $callback );

		$this->itemCount = SQL::rowCount();

		$scriptId = "pagination-script-" . uniqid();
		?>
		<script id="<?= $scriptId ?>">
		<?= $this ?>.page = <?= $this->pageNumber ?>;
		<?= $this ?>.totalPages = <?= $this->totalPages ?>;
		<?= $this ?>.pageListElement = $( "#<?= $scriptId ?>" )[ 0 ].parentElement;
		</script>
		<?php
		return $result;
	}

	public function loadMoreButton()
	{
		$attributes = " onclick=\"$this.loadMore( this )\" ";

		if( $this->pageNumber >= $this->totalPages )
		{
			$attributes .= " hidden";
		}

		echo $attributes;
	}

	public function __toString()
	{
		return "Libraries.Pagination.paginations[ '$this->pageFileName' ]";
	}

}
?>