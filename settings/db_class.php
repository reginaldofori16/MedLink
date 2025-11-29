<?php
/**
 * Database Connection Class for MedLink
 * @author MedLink Development Team
 * @version 2.0
 * 
 * This class handles all database connections and queries for the MedLink platform
 * Uses MySQLi for database operations
 */

// Require database credentials
require_once(__DIR__ . '/db_cred.php');

class db_connection
{
	// Properties
	public $db = null;
	public $results = null;

	/**
	 * Database connection
	 * Establishes connection to the database
	 * @return boolean - true on success, false on failure
	 */
	function db_connect()
	{
		// Connection using constants from db_cred.php
		$this->db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

		// Test the connection
		if (mysqli_connect_errno()) {
			error_log("MedLink Database Connection Error: " . mysqli_connect_error());
			return false;
		} else {
			// Set charset to utf8mb4 for proper character encoding
			mysqli_set_charset($this->db, "utf8mb4");
			return true;
		}
	}

	/**
	 * Database connection (returns connection object)
	 * Alternative method that returns the connection object directly
	 * @return mysqli|false - connection object on success, false on failure
	 */
	function db_conn()
	{
		// Connection using constants from db_cred.php
		$this->db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

		// Test the connection
		if (mysqli_connect_errno()) {
			error_log("MedLink Database Connection Error: " . mysqli_connect_error());
			return false;
		} else {
			// Set charset to utf8mb4 for proper character encoding
			mysqli_set_charset($this->db, "utf8mb4");
			return $this->db;
		}
	}

	/**
	 * Execute a database query
	 * @param string $sqlQuery - SQL query to execute
	 * @return boolean - true on success, false on failure
	 */
	function db_query($sqlQuery)
	{
		// Connect if not already connected
		if (!$this->db_connect()) {
			return false;
		} elseif ($this->db == null) {
			return false;
		}

		// Run query
		$this->results = mysqli_query($this->db, $sqlQuery);

		if ($this->results == false) {
			error_log("MedLink Query Error: " . mysqli_error($this->db) . " | Query: " . $sqlQuery);
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Execute a query with mysqli real escape string
	 * Safeguards from SQL injection by escaping special characters
	 * Note: This method expects the query to already have escape_string applied
	 * For better security, use prepared statements instead
	 * @param string $sqlQuery - SQL query to execute (should be pre-escaped)
	 * @return boolean - true on success, false on failure
	 */
	function db_query_escape_string($sqlQuery)
	{
		// Ensure connection is established
		if ($this->db == null) {
			if (!$this->db_connect()) {
				return false;
			}
		}

		// Run query
		$this->results = mysqli_query($this->db, $sqlQuery);

		if ($this->results == false) {
			error_log("MedLink Query Error: " . mysqli_error($this->db) . " | Query: " . $sqlQuery);
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Escape string for safe database queries
	 * Use this to escape user input before inserting into queries
	 * @param string $string - String to escape
	 * @return string - Escaped string
	 */
	function db_escape_string($string)
	{
		// Ensure connection is established
		if ($this->db == null) {
			if (!$this->db_connect()) {
				return false;
			}
		}

		return mysqli_real_escape_string($this->db, $string);
	}

	/**
	 * Fetch a single record from database
	 * @param string $sql - SQL SELECT query
	 * @return array|false - Associative array of record on success, false on failure
	 */
	function db_fetch_one($sql)
	{
		// If executing query returns false
		if (!$this->db_query($sql)) {
			return false;
		}

		// Return a record
		return mysqli_fetch_assoc($this->results);
	}

	/**
	 * Fetch all records from database
	 * @param string $sql - SQL SELECT query
	 * @return array|false - Array of associative arrays on success, false on failure
	 */
	function db_fetch_all($sql)
	{
		// If executing query returns false
		if (!$this->db_query($sql)) {
			return false;
		}

		// Return all records
		return mysqli_fetch_all($this->results, MYSQLI_ASSOC);
	}

	/**
	 * Get count of rows from last query result
	 * @return int|false - Number of rows on success, false on failure
	 */
	function db_count()
	{
		// Check if result was set
		if ($this->results == null) {
			return false;
		} elseif ($this->results == false) {
			return false;
		}

		// Return row count
		return mysqli_num_rows($this->results);
	}

	/**
	 * Get the last inserted ID
	 * Useful after INSERT queries
	 * @return int|string - Last inserted ID
	 */
	function db_last_id()
	{
		if ($this->db == null) {
			return false;
		}
		return mysqli_insert_id($this->db);
	}

	/**
	 * Get number of affected rows from last query
	 * Useful for UPDATE, DELETE, INSERT queries
	 * @return int - Number of affected rows
	 */
	function db_affected_rows()
	{
		if ($this->db == null) {
			return false;
		}
		return mysqli_affected_rows($this->db);
	}

	/**
	 * Close database connection
	 * Frees up resources
	 * @return boolean - true on success
	 */
	function db_close()
	{
		if ($this->db != null) {
			mysqli_close($this->db);
			$this->db = null;
			$this->results = null;
			return true;
		}
		return false;
	}

	/**
	 * Get the last error message
	 * @return string - Last error message
	 */
	function db_error()
	{
		if ($this->db == null) {
			return "No database connection";
		}
		return mysqli_error($this->db);
	}
}

?>
