<?php

include_once( "utilities.php" );

// TODO: Move this to more specific scopes if need be (to avoid connecting to the database when not necessary)
SQL::connect();

// PDO wrapper class
class CPDO
{

	public $SQL = null;
	protected $statement = null;
	protected $row = null;

	public function __construct( $host, $user, $password, $database )
	{
		try
		{
			$this->SQL = new PDO( "mysql:dbname=" . $database . ";host=" . $host . ";CharSet=utf8mb4;", $user, $password );
			$this->SQL->setAttribute( PDO::ATTR_EMULATE_PREPARES, false );
			$this->SQL->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		} catch( PDOException $e )
		{
			// If the database doesn't exist yet
			if( DEVELOPMENT == true && $e->getCode() == 1049 )
			{
				$this->SQL = new PDO( "mysql:host=" . $host . ";CharSet=utf8mb4;", $user, $password );
				$this->SQL->setAttribute( PDO::ATTR_EMULATE_PREPARES, false );
				$this->SQL->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

				// CREATE USER '$user'@'localhost' IDENTIFIED BY '$password';
				$this->SQL->exec( "
				CREATE DATABASE `$database`;
                GRANT ALL ON `$database`.* TO '$user'@'localhost';
				FLUSH PRIVILEGES;
				" ) 
        		or die(error_log($this->SQL->errorInfo(), true));
			}
			else
			{
				error_log( "Error: " . $e->getMessage() );
				die();
			}
		}
	}

	public function query( $query, $parameters )
	{
		$result = false;

		try
		{
			$parameterCount = count( $parameters );

			if( $this->statement != null )
			{
				$this->statement->closeCursor();
			}

			// If there are query parameters
			if( $parameterCount > 0 )
			{
				$this->statement = $this->SQL->prepare( $query );

				if( $this->statement == false )
				{
					error_log( "Error: " . $e->getMessage() );
					error_log( "Query:\n " . $query );
				}

				for( $index = 0; $index < $parameterCount; $index += 1 )
				{
					$this->statement->bindParam( $index + 1, $parameters[ $index ] );
				}

				$this->statement->execute();

				//print_r( $this->Statement->errorInfo( ) );

				$result = true;
			}
			else
			{
				$this->statement = $this->SQL->query( $query );

				$result = true;
			}
		} catch( PDOException $e )
		{
			error_log( "Error: " . $e->getMessage() );
			error_log( "Query:\n " . $query );
			exit();
		}

		return $result;
	}

	public function fetch( $style = PDO::FETCH_ASSOC )
	{
		try
		{
			$this->row = $this->statement->fetch( $style );
		} catch( PDOException $e )
		{
			error_log( "Error: " . $e->getMessage() );
			die();
		}

		return $this->row;
	}

	public function fetchColumn( $columnNumber = 0 )
	{
		try
		{
			$this->row = $this->statement->fetchColumn( $columnNumber );
		} catch( PDOException $e )
		{
			error_log( "Error: " . $e->getMessage() );
			die();
		}

		return $this->row;
	}

	public function decodeFetch( $style = PDO::FETCH_ASSOC )
	{
		$this->row = $this->statement->fetch( $style );

		$aNew = array();

		for( $index = 0; $index < count( $this->row ); $index += 1 )
		{
			$aNew[ $index ] = rawurldecode( $this->row[ $index ] );
		}

		return $aNew;
	}

	public function getRow()
	{
		return $this->row;
	}

	public function rowCount()
	{
		return $this->statement->rowCount();
	}

	public function lastInsertID()
	{
		return $this->SQL->lastInsertId();
	}

	public function __destruct()
	{
		if( $this->statement != null )
			$this->statement->closeCursor();
	}

}

// Static class
class SQL
{

	public static $SQL;

	public static function connect( $host = DATABASE_HOST, $username = DATABASE_USER, $password = DATABASE_PASSWORD, $database = DATABASE_NAME )
	{
		SQL::$SQL = new CPDO( $host, $username, $password, $database );
	}

	public static function query( $query )
	{
		if( SQL::$SQL == null )
		{
			error_log( "Error: PDO Connection was not initialized" );
			exit();
		}

		$parameters = func_get_args();

		// If the query parameters were passed in via an array
		if( isset( $parameters[ 1 ] ) == true &&
				is_array( $parameters[ 1 ] ) == true )
		{
			$parameters = $parameters[ 1 ];
		}
		else
		{
			// Create an array from the query parameters
			$parameters = array_slice( func_get_args(), 1 );
		}

		return SQL::$SQL->query( $query, $parameters );
	}

	public static function fetch( $style = PDO::FETCH_ASSOC )
	{
		return SQL::$SQL->fetch( $style );
	}

	public static function fetchColumn()
	{
		return SQL::$SQL->fetchColumn();
	}

	public static function decodeFetch( $style = PDO::FETCH_ASSOC )
	{
		return SQL::$SQL->decodeFetch( $style );
	}

	public static function getRow()
	{
		return SQL::$SQL->getRow();
	}

	public static function rowCount()
	{
		return SQL::$SQL->rowCount();
	}

	public static function lastInsertID()
	{
		return SQL::$SQL->lastInsertID();
	}

	public static function createSchema()
	{
		if( !SQL::importSqlFile(DATABASE_NAME . ".sql") )
		{
			error_log( "Error: Failed to create schema from file " . DATABASE_NAME . ".sql" );
			exit();
		}
	}

	public static function generateSchemaModels()
	{
		/* show tables */
		if( !SQL::query( "SHOW TABLES" ) )
		{
			error_log( "Error: cannot show tables." );
			exit();
		}

		$tableNames = [];

		while( $tableName = SQL::fetch( PDO::FETCH_BOTH ) )
		{
			array_push( $tableNames, $tableName );
		}

		// If there's no backup yet, or the old backup deletes successfully
		if( !file_exists( "database-schema-backup.php" ) || unlink( "database-schema-backup.php" ) )
		{
			if( file_exists( "database-schema.php" ) )
			{
				rename( "database-schema.php", "database-schema-backup.php" );
			}
		}

		$databaseSchemaFile = fopen( "database-schema.php", "w" );

		if( !$databaseSchemaFile )
		{
			error_log( "Error: unable to create/open file database-schema.php." );
			exit();
		}

		$text = ""
				. "<?php\n\n"
				. "// WARNING: Before updating the file, use your editor's refectoring (if any) to update references first\n\n"
				. ""
				. "namespace SQL\Tables\n"
				. "{\n\n"
				. "";

		foreach( $tableNames as $tableName )
		{
			$table = $tableName[ 0 ];


			$text .= ""
					. "\tclass $table\n"
					. "\t{\n\n"
					. "";

			if( !SQL::query( "SHOW COLUMNS FROM " . $table ) )
			{
				error_log( "Error: cannot show columns from " . $table );
				exit();
			}

			if( SQL::rowCount() > 0 )
			{
				while( $row = SQL::fetch() )
				{
					$text .= ""
							. "\t\tpublic $" . $row[ "Field" ] . " = \"$table." . $row[ "Field" ] . "\";\n";
				}
			}

			$text .= ""
					. "\n"
					. "\t\tpublic function __toString()\n"
					. "\t\t{\n"
					. "\t\t\treturn \"$table\";\n"
					. "\t\t}\n\n"
					. ""
					. "\t}\n\n";
		}

		$text .= ""
				. "}\n"
				. "?>";

		fwrite( $databaseSchemaFile, utf8_encode( $text ) );
		fclose( $databaseSchemaFile );
	}

	public static function showSchema()
	{
		/* show tables */
		if( !SQL::query( "SHOW TABLES" ) )
		{
			error_log( "Error: cannot show tables." );
			exit();
		}

		$tableNames = [];

		while( $tableName = SQL::fetch( PDO::FETCH_BOTH ) )
		{
			array_push( $tableNames, $tableName );
		}

		echo "<h1>", DATABASE_NAME, "</h1><br/>";

		foreach( $tableNames as $tableName )
		{
			$table = $tableName[ 0 ];

			echo "<h3>", $table, "</h3>";
			SQL::query( "SHOW COLUMNS FROM " . $table ) or die( "cannot show columns from " . $table );

			if( SQL::rowCount() > 0 )
			{
				echo "<table cellpadding='0' cellspacing='0' class='db-table'>";
				echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default<th>Extra</th></tr>";
				while( $row = SQL::fetch() )
				{
					echo "<tr>";
					foreach( $row as $key => $value )
					{
						echo "<td>", $value, "</td>";
					}
					echo "</tr>";
				}
				echo "</table><br />";
			}
		}
	}

	/**
	* Import SQL File
	*
	* @param $sqlFile
	* @param $pdo
	* @param null $tablePrefix
	* @param null $InFilePath
	* @return bool
	*/
	public static function importSqlFile($sqlFile, $pdo = null, $tablePrefix = null, $InFilePath = null)
	{
		try {
			if( $pdo == null )
			{
				$pdo = SQL::$SQL->SQL;
			}

			set_time_limit ( 0 );
			
			// Enable LOAD LOCAL INFILE
			$pdo->setAttribute(\PDO::MYSQL_ATTR_LOCAL_INFILE, true);
			
			$errorDetect = false;
			
			// Temporary variable, used to store current query
			$tmpLine = '';
			
			// Read in entire file
			$lines = file($sqlFile);

			// Loop through each line
			foreach ($lines as $line) {
				// Skip it if it's a comment
				if (substr($line, 0, 2) == '--' || trim($line) == '') {
					continue;
				}
				
				// Read & replace prefix
				$line = str_replace(['<<prefix>>', '<<InFilePath>>'], [$tablePrefix, $InFilePath], $line);
				
				// Add this line to the current segment
				$tmpLine .= $line;
				
				// If it has a semicolon at the end, it's the end of the query
				if (substr(trim($line), -1, 1) == ';') {
					try {
						// Perform the Query
						$pdo->exec($tmpLine);
					} catch (\PDOException $e) {
						echo "<br><pre>Error performing Query: '<strong>" . $tmpLine . "</strong>': " . $e->getMessage() . "</pre>\n";
						$errorDetect = true;
					}
					
					// Reset temp variable to empty
					$tmpLine = '';
				}
			}
			
			// Check if error is detected
			if ($errorDetect) {
				return false;
			}
			
		} catch (\Exception $e) {
			echo "<br><pre>Exception => " . $e->getMessage() . "</pre>\n";
			return false;
		}
		
		return true;
	}

}




?>