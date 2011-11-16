<?php

class base_db extends util
{
	protected $dbh;

	protected $lastResult;
	protected $colInfo;
	protected $lastQuery;

	protected $insertId;
	protected $rowsAffected;

	protected $queryCount;
	protected $queryTime;

	function base_db($host, $user, $password, $name)
	{
		// Attempt to connect to the database. Trigger an error if unsuccessful.
		$this->dbh = @mysql_connect($host, $user, $password);
		if(!$this->dbh) {
			trigger_error('Error establishing a database connection!', E_USER_ERROR);
		}

		// Select the specified database.
		$this->select($name);

		// Set the counters to zero.
		$this->queryCount = 0;
		$this->queryTime = 0;
	}

	function select($db)
	{
		// Attempt to select the specified database. Trigger an error if unsuccessful.
		if(!@mysql_select_db($db, $this->dbh)) {
			trigger_error('Error selecting database!', E_USER_ERROR);
		}
	}

	function flush()
	{
		// Flush all cached values.
		$this->lastResult 	= null;
		$this->colInfo 		= null;
		$this->lastQuery 	= null;
		$this->insertId		= null;
		$this->rowsAffected	= null;
	}

	function query($query)
	{
		$startTime = microtime(true);

		// Flush cached values.
		$this->flush();

		// Keep track of the last query for debug.
		$this->lastQuery = $query;

		// Keep track of the total number of queries per instance.
		$this->queryCount++;

		// Perform the query via std mysql_query function.
		$this->result = mysql_query($query, $this->dbh);
		
		// If there was an insert, update, replace, or delete see how many rows were affected.
		$query_type = array('insert', 'delete', 'update', 'replace');

		// loop through the above array
		foreach($query_type as $word) {
			// Check if the query starts with insert, update, replace, or delete.
			if(preg_match("/^\\s*$word /i",$query)) {
				// Set number of rows affected
				$this->rowsAffected = mysql_affected_rows($this->dbh);

				// Set the insert id.
				if ( $word == 'insert' || $word == 'replace' ) {
					$this->insertId = mysql_insert_id($this->dbh);
				}

				// Reset the result.
				$this->result = null;
			}
			
		}

		// Track time used by queries.
		$endTime = microtime(true);
		$this->queryTime += ($endTime - $startTime);

		// Check for a MySQL error.
		if(mysql_error()) {
			trigger_error(mysql_error(), E_USER_ERROR);
		}
		else {
			// Check if this was a select statement.
			if($this->result) {
				// Save column information.
				$i = 0;
				while ($i < @mysql_num_fields($this->result)) {
					$this->colInfo[$i] = @mysql_fetch_field($this->result);
					$i++;
				}

				// Save results.
				$i = 0;
				while($row = @mysql_fetch_object($this->result)) {
					$this->lastResult[$i] = $row;
					$i++;
				}

				// Log number of rows the query returned
				$this->num_rows = $i;

				@mysql_free_result($this->result);

				// If there were results then return true.
				return ($i) ? true : false;
			}
			else {
				// Insert, update, replace, or delete was successful.
				return true;
			}
		}
	}

	function getVar($query = null, $x = 0, $y = 0)
	{
		// If there is a query then perform it. If not then use cached results.
		if($query) {
			$this->query($query);
		}

		// Extract var out of cached results based on x,y values
		if($this->lastResult[$y]) {
			$values = array_values(get_object_vars($this->lastResult[$y]));
		}

		// If there is a value return it. Otherwise return null.
		return(isset($values[$x]) && $values[$x] !== '') ? $values[$x] : null;
	}

	// Get a specific row from a query. $output types - OBJECT, ARRAY_A, ARRAY_N.
	function getRow($query = null, $output = 'OBJECT', $y = 0)
	{
		// If there is a query then perform it if not then use cached results.
		if($query) {
			$this->query($query);
		}
		if($output == 'OBJECT') {
			return $this->lastResult[$y] ? $this->lastResult[$y] : null;
		}
		else if($output == 'ARRAY_A') {
			return $this->lastResult[$y] ? get_object_vars($this->lastResult[$y]) : null;
		}
		else if($output == 'ARRAY_N') {
			return $this->lastResult[$y] ? array_values(get_object_vars($this->lastResult[$y])) : null;
		}
		else {
			trigger_error("\$db->getRow(string query, output type, int offset) -- Output type must be one of: OBJECT, ARRAY_A, ARRAY_N", E_USER_ERROR);
		}
	}

	// Get all the results from a single column.
	function getCol($query = null, $x = 0)
	{
		// If there is a query then perform it. If not then use cached results.
		if($query) {
			$this->query($query);
		}

		// Extract the column values.
		for($i=0; $i < count($this->lastResult); $i++) {
			$new_array[$i] = $this->get_var(null, $x, $i);
		}

		// Return the column values.
		return $new_array;
	}

	// Get a list of results from a query. $output types = OBJECT, ARRAY_A, ARRAY_N
	function getResults($query = null, $output = 'OBJECT')
	{
		// If there is a query then perform it. If not then use cached results.
		if($query) {
			$this->query($query);
		}

		// Send back an array of objects. Each row is an object.
		if($output == 'OBJECT') {
			return $this->lastResult;
		}
		elseif($output == 'ARRAY_A' || $output == 'ARRAY_N') {
			// Send back an array of arrays. Each row is an array.
			if($this->lastResult) {
				$i=0;
				foreach($this->lastResult as $row) {
					$new_array[$i] = get_object_vars($row);
					if($output == 'ARRAY_N') {
						$new_array[$i] = array_values($new_array[$i]);
					}
					$i++;
				}
				return $new_array;
			}
			else {
				return null;
			}
		}
		else {
			trigger_error("\$db->getResults(string query, output type) -- Output type must be one of: OBJECT, ARRAY_A, ARRAY_N", E_USER_ERROR);
		}
	}

	// Get a specific piece of information about a column or all columns.
	function getColInfo($info_type = 'name', $col_offset = -1)
	{
		// Make sure column information has been stored.
		if($this->colInfo) {
			// If no offset was requested, send an array of information for all columns.
			if($col_offset == -1) {
				$i = 0;
				foreach($this->colInfo as $col) {
					$new_array[$i] = $col->{$info_type};
					$i++;
				}
				return $new_array;
			}
			else {
				// Return the value for the requested offset.
				return $this->colInfo[$col_offset]->{$info_type};
			}
		}
		return false;
	}

	// Run all queries in a specified file.
	function runFile($file)
	{
		// Make sure the file exists
		if(is_file($file)) {
			// Run each query in the file, separated by a semi-colon.
			$sql = implode('', file($file));
			foreach(explode(';', $sql) as $s) {
				if(trim($s)) {
					$this->query($s);
				}
			}
		}
	}

	// Displays some debugging information about the most recent query.
	function debug()
	{
		echo "<blockquote>";
		echo "<font face=arial size=2 color=000099><b>Query --</b> ";
		echo "[<font color=000000><b>$this->lastQuery</b></font>]</font><p>";
		echo "<font face=arial size=2 color=000099><b>Query Result..</b></font>";
		echo "<blockquote>";

		if($this->colInfo) {
			echo "<table cellpadding=5 cellspacing=1 bgcolor=555555>";
			echo "<tr bgcolor=eeeeee><td nowrap valign=bottom><font color=555599 face=arial size=2><b>(row)</b></font></td>";

			for ( $i=0; $i < count($this->colInfo); $i++ ) {
				echo "<td nowrap align=left valign=top><font size=1 color=555599 face=arial>{$this->colInfo[$i]->type} {$this->colInfo[$i]->max_length}</font><br><font size=2><b>{$this->colInfo[$i]->name}</b></font></td>";
			}

			echo "</tr>";

			// Print stored results.
			if($this->lastResult) {
				$i = 0;
				foreach($this->get_results(null,'ARRAY_N') as $one_row) {
					$i++;
					echo "<tr bgcolor=ffffff><td bgcolor=eeeeee nowrap align=middle><font size=2 color=555599 face=arial>$i</font></td>";
					foreach ( $one_row as $item ) {
						echo "<td nowrap><font face=arial size=2>$item</font></td>";
					}
					echo "</tr>";
				}

			}
			else {
				// No results were stored.
				echo "<tr bgcolor=ffffff><td colspan=".(count($this->colInfo)+1)."><font face=arial size=2>No Results</font></td></tr>";
			}

			echo "</table>";

		}
		else {
			echo "<font face=arial size=2>No Results</font>";
		}

		echo "</blockquote></blockquote><hr noshade color=dddddd size=1>";
	}

	// Retrieve the insert id from the last query.
	function getLastInsertId()
	{
		if($this->insertId) {
			return $this->insertId;
		}
		else {
			return false;
		}
	}

	// Retrieve the number of rows affected by the last query.
	function getRowsAffected()
	{
		if($this->rowsAffected) {
			return $this->rowsAffected;
		}
		else {
			return false;
		}
	}

	// Retrieve some information about the total queries run for this instance.
	function getQueryTotals()
	{
		return array('count'=>$this->queryCount, 'time'=>$this->queryTime);
	}
}

