<?php

namespace App;

class DBConnect extends Factory
{
	protected static $instance;

	var $_fromTable;
	var $error         = '';
	var $debug         = true;
	var $die_on_error  = true;
	var $affected_rows = 0;

	protected $table_prefix;
	protected $con;
	protected $_mysqli;
	private   $_last_query = '';
	private   $_query;
	private   $_limit;
	private   $_offset;
	private   $_result;
	private   $_executed   = false;
	private   $executed    = false;
	private   $_delete     = false;
	private   $_distinct   = false;

	/**
	 * Arrays
	 */
	var $array_where   = [];
	var $array_likeor  = [];
	var $array_select  = [];
	var $array_wherein = [];
	var $array_groupby = [];
	var $array_having  = [];
	var $array_orderby = [];
	var $array_join    = [];

	/**
	 * Loading the database connect as an instance
	 */
	public static function getInstance()
	{
		if ( ! isset(DBConnect::$instance))
		{
			DBConnect::$instance = new DBConnect;

			return DBConnect::$instance;
		}

		return DBConnect::$instance;
	}

	/**
	 * DBConnect constructor.
	 */
	private function __construct()
	{
		// Loading the database credentials
		$db = $this->getCredentials();

		// Connecting the mysql database
		$this->con = mysqli_connect($db['host'], $db['user'], $db['pass'], $db['db']);

		// Setting proper charset.
		mysqli_set_charset($this->con, "utf8");

		if (mysqli_connect_errno())
		{
			exit('Helaas, kan niet verbinden met de databeest');
		}
	}

	private function getCredentials()
	{
		return [
			'host' => 'localhost',
			'user' => 'root',
			'pass' => '',
			'db'   => 'cursus',
		];
	}

	/**
	 * Reset the database values to nothng.
	 */
	public function reset()
	{
		unset($this->_query);
		unset($this->_limit);
		unset($this->_offset);

		$this->_delete       = false;
		$this->_distinct     = false;
		$this->array_where   = [];
		$this->array_select  = [];
		$this->array_wherein = [];
		$this->array_groupby = [];
		$this->array_having  = [];
		$this->array_orderby = [];
		$this->array_join    = [];
		$this->array_likeor  = [];
	}

	public function insert($table, $data)
	{
		$table_array = $this->getTableColumns($table);

		// Loop trough the sent data
		foreach ($data as $key => $value)
		{
			if (in_array($key, $table_array))
			{
				$keys[] = "`$key`";

				if (strpos($value, '()') == true)
				{
					$values[] = "" . addslashes($value) . "";
				}
				else
				{
					$values[] = "'" . addslashes($value) . "'";
				}
			}
		}

		// Insert the data into the DB and return the inserted ID.
		$this->_query = "INSERT INTO " . $table . " (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $values) . ");";

		$this->execute();

		return $this->con->insert_id;
	}

	/**
	 * Making it possible to use insert or update.
	 *
	 * @param       $table
	 * @param array $data
	 * @param array $updates
	 * @param bool  $update_record
	 *
	 * @return mixed
	 */
	public function insertOnDuplicate($table, $data = [], $updates = [], $update_record = true)
	{
		$query  = mysqli_query($this->con, 'SHOW FIELDS FROM ' . $table);
		$update = '';

		$table_array = [];

		while ($row = mysqli_fetch_assoc($query))
		{
			$table_array[] = $row["Field"];
		}

		foreach ($data as $key => $value)
		{
			if (in_array($key, $table_array))
			{
				$keys[]   = "`$key`";
				$values[] = "'" . addslashes($value) . "'";
			}
		}

		foreach ($updates as $key => $value)
		{
			if ($key === 'uid')
				continue;

			$value = is_numeric($value) ? $value : '"' . addslashes($value) . '"';

			$update .= '`' . $key . '`' . ' = ' . $value . ', ';
		}

		if ($update_record)
		{
			$update .= 'updated=updated+1';
		}

		$update = rtrim($update, ', ');

		$this->_query = "INSERT INTO " . $table . " (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $values) . ") ON DUPLICATE KEY UPDATE " . $update;

		$this->execute();

		return $this->con->insert_id;
	}

	public function update($table, $data)
	{
		$table_array = $this->getTableColumns($table);

		foreach ($data as $key => $val)
		{
			if (in_array($key, $table_array))
			{
				if (strpos($val, '()') == true)
				{
					$valstr[] = "`$key`" . " = " . addslashes($val) . "";
				}
				else
				{
					$valstr[] = "`$key`" . " = '" . addslashes($val) . "'";
				}
			}
		}

		$this->_query = "UPDATE " . $table . " SET " . implode(', ', $valstr);

		if (count($this->array_where) > 0)
		{
			$this->_query .= " WHERE ";
			$this->_query .= implode(" ", $this->array_where);
		}

		$this->execute();

		return $this;
	}

	private function getTableColumns($table)
	{
		$query = mysqli_query($this->con, 'SHOW FIELDS FROM ' . $table);

		$table_array = [];

		while ($row = mysqli_fetch_assoc($query))
		{
			$table_array[] = $row["Field"];
		}

		return $table_array;
	}

	public function where($key, $value = null, $operator = '=')
	{
		return $this->_where($key, $value, $operator, 'AND ');
	}

	protected function _where($key, $value, $operator, $type = 'AND ')
	{
		/**
		 * If user provided custom where() clauses then we do not need to process it
		 */
		if ( ! is_array($key) AND is_null($value))
		{
			$this->array_where[0] = $key;

			return $this;
		}

		$prefix = (count($this->array_where) == 0) ? '' : $type;
		$value  = $this->escape($value);

		if ($this->isReservedWord($key) == true)
		{
			$this->array_where[] = "$prefix `$key` " . $operator . " '$value'";
		}
		else
		{
			if (is_numeric($value))
			{
				$this->array_where[] = "$prefix $key " . $operator . " $value";
			}
			else
			{
				$this->array_where[] = "$prefix $key " . $operator . " '$value'";
			}
		}

		return $this;
	}

	public function where_or($values, $reversed = false)
	{
		$prefix = (count($this->array_where) == 0) ? '' : "AND ";

		$or_statements = [];

		if ( ! is_array($values))
		{
			return $this;
		}

		foreach ($values as $key => $value)
		{
			if ($reversed)
			{
				$or_statements[] = "$value = '$key'";
			}
			else
			{
				$or_statements[] = "$key = '$value'";
			}
		}

		$this->array_where[] = " $prefix (" . implode(" OR ", $or_statements) . ")";

		return $this;
	}

	/**
	 * Makes an IN clause in SQL: WHERE column_name IN (value1,value2,...);
	 * Required: key: column to be used for searchin
	 * Required: array: $cids = array(‘2’, ‘3’, ‘10’);
	 * @return $this.
	 */
	public function where_in($key, $value)
	{
		$prefix = (count($this->array_where) == 0) ? '' : "AND";

		if (empty($key))
		{
			return $this;
		}

		if ( ! is_array($value))
		{
			$this->array_where[] = "$prefix `$key` IN ('$value')";

			return $this;
		}

		if ( ! $this->integerOnly($value))
		{
			$this->array_where[] = "$prefix $key IN ('" . implode("', '", $value) . "')";
		}
		else
		{
			$this->array_where[] = "$prefix $key IN (" . implode(", ", $value) . ")";
		}

		return $this;
	}

	/**
	 * Is checking if we need quotation or not for the where_in queries..
	 *
	 * @param array $items
	 *
	 * @return bool
	 */
	private function integerOnly($items = [])
	{
		foreach ($items as $item)
		{
			if ( ! is_numeric($item))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Get total records from executed query
	 *
	 * @return int
	 */
	public function getTotal()
	{
		return $this->affected_rows;
	}

	/**
	 * Sets a custom query
	 *
	 * @return true/false.
	 */
	public function setPaginationQuery($query, $offset = null, $limit = null)
	{

		$append = '';

		if (isset($offset) || isset($limit))
		{
			$append = ' LIMIT ';
		}

		if (isset($offset))
		{
			$append .= $offset . ',';
		}

		if (isset($limit))
		{
			$append .= $limit;
		}

		$this->_query = $query . $append;

		return true;
	}

	public function getPaginationQuery()
	{
		return $this->_last_query;
	}

	/**
	 * Sets a custom query
	 * @return true/false.
	 */
	public function setQuery($query)
	{
		$this->_query = $query;

		return true;
	}

	/**
	 * Get fields and return
	 * Example: SHOW FIELDS FROM stats_cs_avaya
	 *
	 * @param $from
	 *
	 * @return $this
	 */
	public function showFields($from)
	{
		$this->_query = "SHOW FIELDS FROM " . $from;

		return $this;
	}

	public function getQuery()
	{
		$this->_query = isset($this->_query) ? $this->_query : null;

		return $this->_query;
	}

	/**
	 * Fetches the result of an execution.
	 *
	 * @return array Returns an Associate Array of results.
	 *
	 * @deprecated
	 */
	public function fetch()
	{

		if ($this->executed == false || ! $this->_query)
		{
			$this->execute();
		}

		if (is_object($this->_result))
		{
			$this->_executed = false;

			if (method_exists('mysqli_result', 'fetch_all'))
			{
				$results = $this->_result->fetch_all(MYSQLI_ASSOC);
			}
			else
			{
				for ($results = []; $tmp = $this->_result->fetch_array(MYSQLI_ASSOC);)
					$results[] = $tmp;
			}

			return $results;
		}
		else
		{
			$this->oops('Unable to perform fetch()');
		}
	}

	// New functions
	function loadResult()
	{
		$result = $this->fetch();

		return ! empty($result[0]) ? (object) $result[0] : [];
	}

	// New functions
	function loadResultList($query = null, $start = 0, $limit = 25)
	{

		if ($query)
		{
			$this->setPaginationQuery($query, $start, $limit);

			return (object) $this->fetch();
		}

		return (object) $this->fetch();
	}

	/**
	 * @return array|mixed
	 * @deprecated
	 */
	public function fetchRow()
	{

		$result = $this->fetch();

		return ! empty($result[0]) ? $result[0] : [];
	}

	public function escape($string)
	{

		if (get_magic_quotes_runtime())
		{
			$string = stripslashes($string);
		}

		return @$this->con->real_escape_string($string);
	}

	public function show()
	{
		return '<pre><h4>Some Query Shizzle:</h4>' . $this->queryShizzle . '</pre>';
	}

	public function quoteName($val)
	{
		return '`' . $val . '`';
	}

	public function execute()
	{
		$this->prepare();

		$this->queryShizzle = $this->_query;

		$this->_result = $this->con->query($this->_query);

		if ( ! $this->_result)
		{
			exit('Query failed, please contact the administrator.');
		}

		$this->affected_rows = $this->con->affected_rows;
		$this->_last_query   = $this->_query;

		$this->query_info = $this;

		$this->reset();

		return $this;
	}

	public function getQueryInformation()
	{
		return $this->query_info;
	}

	/**
	 * The SELECT portion of the query.
	 *
	 * @param $select Can either be a string or an array containing the columns to be
	 *                selected. If none provided, * will be assigned by default
	 *
	 * @uses $db->select("id, email, password") ;
	 * @uses $db->select(array('id', 'email', 'password')) ;
	 * @return $this
	 */
	public function select($select = '*')
	{
		if (is_string($select))
		{
			$select = explode(',', $select);
		}
		foreach ($select as $val)
		{
			$val = trim($val);

			if ($val != '')
			{
				if ($this->isReservedWord($val))
				{
					$this->array_select[] = "`$val`";
				}
				else
				{
					$this->array_select[] = "$val";
				}
			}
		}

		return $this;
	}

	/**
	 * Sets the table to query
	 */
	public function from($table, $as = null)
	{
		if (empty($as))
		{
			$this->_fromTable = $table;
		}
		else
		{
			$this->_fromTable = $table . ' as ' . $as;
		}

		return $this;
	}

	/**
	 * Sets a limit and offset clause. Offset is optional
	 *
	 * @uses $db->limit(0,12); // Will list the first 12 rows
	 * @uses $db->limit(1); // Will list the first 1 row.
	 */

	public function limit($limit, $offset = null)
	{

		$this->_limit = (int) $limit;
		if ($offset)
		{
			$this->_offset = (int) $offset;
		}

		return $this;
	}

	/**
	 * SELECT_SUM Portion of the query
	 *
	 * Writes a "SELECT SUM(field)" portion for your query. You can optionally
	 * include a second parameter to rename the resulting field.
	 */

	public function select_sum($field, $name = null)
	{

		if ($name == null)
		{
			$name = $field;
		}
		if ($this->isReservedWord($field))

		{
			$this->array_select[0] = "SUM(`$field`) AS $name ";
		}
		else
		{
			$this->array_select[0] = "SUM($field) AS $name ";
		}

		return $this;
	}

	/**
	 * SELECT_COUNT Portion of the query
	 *
	 * Writes a "SELECT SUM(field)" portion for your query. You can optionally
	 * include a second parameter to rename the resulting field.
	 *
	 * @param      $field
	 * @param null $name
	 *
	 * @return $this
	 */
	public function select_count($field, $name = null)
	{

		if ($name == null)
		{
			$name = $field;
		}
		if ($this->isReservedWord($field))

		{
			$this->array_select[0] = "COUNT(`$field`) AS $name ";
		}
		else
		{
			$this->array_select[0] = "COUNT($field) AS $name ";
		}

		return $this;
	}

	/**
	 * Sets the HAVING value
	 *
	 * Separates multiple calls with AND
	 *
	 * @param    string
	 * @param    string
	 *
	 * @return    object
	 */
	public function having($key, $value = '')
	{

		return $this->_having($key, $value, 'AND ');
	}

	// --------------------------------------------------------------------

	/**
	 * Sets the OR HAVING value
	 *
	 * Separates multiple calls with OR
	 *
	 * @param    string
	 * @param    string
	 *
	 * @return    object
	 */
	public function or_having($key, $value = '')
	{

		return $this->_having($key, $value, 'OR ');
	}

	// --------------------------------------------------------------------

	/**
	 * Sets the HAVING values
	 *
	 * Called by having() or or_having()
	 *
	 * @param    string
	 * @param    string
	 *
	 * @return    object
	 */
	protected function _having($key, $value = '', $type = 'AND ')
	{

		if ( ! is_array($key))
		{
			$key = [$key => $value];
		}

		foreach ($key as $k => $v)
		{
			$prefix = (count($this->array_having) == 0) ? '' : $type;

			if ($v != '')
			{
				$v = " = '" . $this->escape($v) . "'";
			}

			if ($this->isReservedWord($k))
			{
				$this->array_having[] = $prefix . "`$k`" . $v;
			}
			else
			{
				$this->array_having[] = $prefix . "$k" . $v;
			}
		}

		return $this;
	}

	/**
	 * ORDER By clause
	 */

	public function order($orderby, $direction = null)
	{

		// If custom order by is given
		if ( ! is_array($orderby) AND is_null($direction))
		{
			$this->array_orderby[0] = $orderby;

			return $this;
		}
		// If $orderby is an array the we ignore the value of $direction

		if (is_array($orderby))
		{
			foreach ($orderby as $key => $value)
			{
				$this->order($key, $value);
			}
		}
		else
		{
			$direction = strtoupper($direction);
			if ($this->isReservedWord($orderby))
			{
				$this->array_orderby[] = "`$orderby` $direction";
			}
			else
			{
				$this->array_orderby[] = "$orderby $direction";
			}
		}

		return $this;
	}

	/**
	 * @param      $orderby
	 * @param null $direction
	 *
	 * @return $this
	 * @deprecated wordt order
	 */
	public function order_by($orderby, $direction = null)
	{

		// If custom order by is given
		if ( ! is_array($orderby) AND is_null($direction))
		{
			$this->array_orderby[0] = $orderby;

			return $this;
		}
		// If $orderby is an array the we ignore the value of $direction

		if (is_array($orderby))
		{
			foreach ($orderby as $key => $value)
			{
				$this->order_by($key, $value);
			}
		}
		else
		{
			$direction = strtoupper($direction);
			if ($this->isReservedWord($orderby))
			{
				$this->array_orderby[] = "`$orderby` $direction";
			}
			else
			{
				$this->array_orderby[] = "$orderby $direction";
			}
		}

		return $this;
	}

	/**
	 * Delete function
	 *
	 * @param string $table Name of the table from where the values to be deleted. It
	 *                      is optional. If value is not given then the value set by from() will be taken
	 *
	 * @return $this
	 */

	public function delete($table = null)
	{

		if ($table)
		{
			$this->from($table);
		}
		$this->_delete = true;

		return $this;
	}

	/**
	 * Join
	 *
	 * Generates the JOIN portion of the query
	 *
	 * @param    string $table     Table for joining
	 * @param    string $condition Condition of join
	 * @param    string $type      Type of join. Example 'LEFT', 'RIGHT', 'OUTER', 'INNER',
	 *                             'LEFT OUTER', 'RIGHT OUTER'
	 *
	 * @return $this
	 */
	public function join($table, $condition, $type = null)
	{

		if ($type == null)
		{
			$type = 'LEFT';
		}

		## Default is left join
		$type = strtoupper($type);
		$join = $type . ' JOIN ' . $table . ' ON ' . $condition;

		$this->array_join[] = $join;

		return $this;
	}

	/** Writing the query for usage.
	 *
	 * @return $this
	 */
	private function prepare()
	{
		if ( ! isset($this->_query))
		{
			if ( ! empty($this->array_select))
			{
				$this->_query = ( ! $this->_distinct) ? 'SELECT ' : 'SELECT DISTINCT ';

				if ($this->array_select == '*' || count($this->array_select) == 0)
				{
					$this->_query .= '*';
				}
				else
				{
					$this->_query .= implode(",", $this->array_select);
				}
			}

			if ($this->_delete == true)
			{
				if ($this->_fromTable == null)
				{
					exit('No table given --> please try agin.');
				}
				$this->_query = 'DELETE';
			}

			## If select() is not called but the call is a SELECT statement
			if ($this->_delete == false && empty($this->array_select))
			{
				$this->_query = ( ! $this->_distinct) ? 'SELECT * ' : 'SELECT DISTINCT * ';
			}

			$this->_delete = false;
			## unset delete flag

			## Write the "FROM" portion of the query
			if (isset($this->_fromTable))
			{
				if ($this->isReservedWord($this->_fromTable))
				{
					$this->_query .= "\nFROM `$this->_fromTable` ";
				}
				else
				{
					$this->_query .= "\nFROM $this->_fromTable ";
				}
			}

			## Write the "JOIN" portion of the query
			if (count($this->array_join) > 0)
			{
				$this->_query .= " ";
				$this->_query .= implode("\n", $this->array_join);
			}

			## Write the "WHERE" portion of the query
			if (count($this->array_where) > 0)
			{
				for ($i = 0; $i < count($this->array_where); $i++)
				{
					if ( ! $this->_has_operator($this->array_where[$i]))
					{
						$this->array_where[$i] = $this->array_where[$i] . " = ''";
					}
				}
				$this->_query .= "\nWHERE ";
				$this->_query .= implode("\n", $this->array_where);
			}
		}

		## Write the "GROUP BY" portion of the query
		if ( ! empty($this->array_groupby))
		{
			$this->_query .= "\nGROUP BY ";
			$this->_query .= implode(', ', $this->array_groupby);
		}

		## Write the "HAVING" portion of the query
		if ( ! empty($this->array_having))
		{
			$this->_query .= "\nHAVING ";
			$this->_query .= implode("\n", $this->array_having);
		}

		## Write the "ORDER BY" portion of the query
		if ( ! empty($this->array_orderby))
		{
			$this->_query .= "\nORDER BY ";
			$this->_query .= implode(', ', $this->array_orderby);
		}

		## Write the "LIMIT" portion of the query
		if (isset($this->_limit))
		{
			$this->_query .= "\nLIMIT " . $this->_limit;
		}

		return $this;
	}

	/**
	 * Permits to write the LIKE portion of the query using the connector AND
	 *
	 * @param mixed  $title string or array Can either be a string or array. This is the
	 *                      title portion of LIKE
	 * @param        $match string Required only if $title is a string. This is the matching
	 *                      portion
	 * @param        $place string This enables you to control where the wildcard (%) is
	 *                      placed. Options are "both", "before", and "after". Default is "both"
	 * @param string $type  Default is "AND"
	 *
	 * @return $this
	 */
	public function like($title, $match = null, $place = 'both', $type = 'AND ')
	{
		$this->_like($title, $match, $place, $type);

		return $this;
	}

	/**
	 * Different from the function 'like'
	 * Adds '(' and ')' on the beginning and the end of the values from given array
	 * With this function you will be able to use other WHERE statements as well
	 *
	 * @param        $title
	 * @param        $match
	 * @param string $place
	 * @param        $type
	 *
	 * @return $this
	 */
	public function like_or($title, $match, $place = 'both', $type = 'OR ')
	{
		$this->_like($title, $match, $place, $type);

		// The value of the first key needs to be changed
		$this->array_where[0] = '(' . $this->array_where[0];

		// Grab the last key, use it to get the value and change it
		end($this->array_where);
		$last_key                     = key($this->array_where);
		$this->array_where[$last_key] = $this->array_where[$last_key] . ')';

		return $this;
	}

	/**
	 * @param        $title
	 * @param        $match
	 * @param string $place
	 * @param        $type
	 *
	 * @return $this
	 */
	protected function _like($title, $match, $place = 'both', $type)
	{
		// If $title is an array, we need to process it
		if (is_array($title))
		{
			foreach ($title as $key => $value)
			{
				$this->_like($key, $value, $place, $type);
			}
		}
		else
		{
			$prefix = (count($this->array_where) == 0) ? '' : $type;
			$match  = $this->escape($match);

			if ($place == 'both')
			{
				if ($this->isReservedWord($title))
				{
					$this->array_where[] = "$prefix`$title` LIKE '%$match%'";
				}
				else
				{
					$this->array_where[] = "$prefix$title LIKE '%$match%'";
				}
			}

			if ($place == 'before')
			{
				if ($this->isReservedWord($title))
				{
					$this->array_where[] = "$prefix`$title` LIKE '%$match'";
				}
				else
				{
					$this->array_where[] = "$prefix$title LIKE '%$match'";
				}
			}

			if ($place == 'after')
			{
				if ($this->isReservedWord($title))
				{
					$this->array_where[] = "$prefix`$title` LIKE '$match%'";
				}
				else
				{
					$this->array_where[] = "$prefix$title LIKE '$match%'";
				}
			}

			if ($place == 'none')
			{
				if ($this->isReservedWord($title))
				{
					$this->array_where[] = "$prefix`$title` LIKE '$match'";
				}
				else
				{
					$this->array_where[] = "$prefix$title LIKE '$match'";
				}
			}

			return $this;
		}
	}

	/**
	 * Group by
	 *
	 * @param string or array $by Either an arry
	 *
	 * @deprecated Use the group method.
	 * @return $this
	 */
	public function group_by($by)
	{
		if (is_string($by))
		{
			$by = explode(',', $by);
		}

		foreach ($by as $val)
		{
			$val = trim($val);

			if ($val != '')
			{
				if ($this->isReservedWord($val))
				{
					$this->array_groupby[] = "`$val`";
				}
				else
				{
					$this->array_groupby[] = "$val";
				}
			}
		}

		return $this;
	}

	/**
	 * Group by
	 *
	 * @param $by
	 *
	 * @return $this
	 */
	public function group($by)
	{
		if (is_string($by))
		{
			$by = explode(',', $by);
		}

		foreach ($by as $val)
		{
			$val = trim($val);

			if ($val != '')
			{
				if ($this->isReservedWord($val))
				{
					$this->array_groupby[] = "`$val`";
				}
				else
				{
					$this->array_groupby[] = "$val";
				}
			}
		}

		return $this;
	}

	/**
	 * @param $str
	 *
	 * @return bool
	 */
	function _has_operator($str)
	{
		$str = trim($str);

		if ( ! preg_match("/(\s|<|>|!|=|is null|is not null)/i", $str))
		{
			return false;
		}

		return true;
	}

	/**
	 * @param $word
	 *
	 * @return bool
	 */
	private function isReservedWord($word)
	{

		$words = [
			"ACCESSIBLE",
			"ADD",
			"ALL",
			"ALTER",
			"ANALYZE",
			"AS",
			"ASC",
			"ASENSITIVE",
			"BEFORE",
			"BETWEEN",
			"BIGINT",
			"BINARY",
			"BLOB",
			"BOTH",
			"BY",
			"CALL",
			"CASCADE",
			"CASE",
			"CHANGE",
			"CHAR",
			"CHARACTER",
			"CHECK",
			"COLLATE",
			"COLUMN",
			"CONDITION",
			"CONSTRAINT",
			"CONTINUE",
			"CONVERT",
			"CREATE",
			"CROSS",
			"CURRENT_TIME",
			"CURRENT_TIMESTAMP",
			"CURRENT_USER",
			"CURSOR",
			"DATABASE",
			"DATABASES",
			"DEC",
			"DECIMAL",
			"DECLARE",
			"DEFAULT",
			"DELAYED",
			"DELETE",
			"DESC",
			"DESCRIBE",
			"DETERMINISTIC",
			"DISTINCT",
			"DISTINCTROW",
			"DIV",
			"DOUBLE",
			"DROP",
			"DUAL",
			"EACH",
			"ELSE",
			"ELSE",
			"ELSEIF",
			"ENCLOSED",
			"ESCAPED",
			"EXISTS",
			"EXIT",
			"EXPLAIN",
			"FALSE",
			"FETCH",
			"FLOAT",
			"FLOAT4",
			"FLOAT8",
			"FOR",
			"FORCE",
			"FOREIGN",
			"FULLTEXT",
			"GENERAL[a]",
			"GRANT",
			"GROUP",
			"HAVING",
			"HIGH_PRIORITY",
			"HOUR_MICROSECOND",
			"HOUR_MINUTE",
			"HOUR_SECOND",
			"IF",
			"IGNORE",
			"IGNORE_SERVER_IDS[b]",
			"INDEX",
			"INFILE",
			"INNER",
			"INOUT",
			"INSENSITIVE",
			"INSERT",
			"INT",
			"INT1",
			"INT2",
			"INT3",
			"INT4",
			"INT8",
			"INTEGER",
			"INTO",
			"IS",
			"ITERATE",
			"JOIN",
			"KEY",
			"KEYS",
			"KILL",
			"LEADING",
			"LEAVE",
			"LEFT",
			"LIKE",
			"LIMIT",
			"LINEAR",
			"LINES",
			"LOAD",
			"LOCALTIME",
			"LOCALTIMESTAMP",
			"LOCK",
			"LONG",
			"LONGBLOB",
			"LONGTEXT",
			"LOOP",
			"LOW_PRIORITY",
			"MASTER_HEARTBEAT_PERIOD[c]",
			"MASTER_SSL_VERIFY_SERVER_CERT",
			"MATCH",
			"MAXVALUE",
			"MEDIUMBLOB",
			"MEDIUMINT",
			"MEDIUMTEXT",
			"MIDDLEINT",
			"MINUTE_MICROSECOND",
			"MINUTE_SECOND",
			"MOD",
			"MODIFIES",
			"NATURAL",
			"NO_WRITE_TO_BINLOG",
			"NULL",
			"NUMERIC",
			"ON",
			"OPTIMIZE",
			"OPTION",
			"OPTIONALLY",
			"ORDER",
			"OUT",
			"OUTER",
			"OUTFILE",
			"PRECISION",
			"PRIMARY",
			"PROCEDURE",
			"PURGE",
			"RANGE",
			"READ",
			"READS",
			"READ_WRITE",
			"REAL",
			"REFERENCES",
			"REGEXP",
			"RELEASE",
			"RENAME",
			"REPEAT",
			"REPLACE",
			"REQUIRE",
			"RESIGNAL",
			"RESTRICT",
			"RETURN",
			"REVOKE",
			"RIGHT",
			"RLIKE",
			"SCHEMA",
			"SCHEMAS",
			"SECOND_MICROSECOND",
			"SENSITIVE",
			"SEPARATOR",
			"SET",
			"SHOW",
			"SIGNAL",
			"SLOW[d]",
			"SMALLINT",
			"SPATIAL",
			"SPECIFIC",
			"SQL",
			"SQLEXCEPTION",
			"SQLSTATE",
			"SQLWARNING",
			"SQL_BIG_RESULT",
			"SQL_CALC_FOUND_ROWS",
			"SQL_SMALL_RESULT",
			"SSL",
			"STARTING",
			"STRAIGHT_JOIN",
			"TABLE",
			"TERMINATED",
			"THEN",
			"TINYBLOB",
			"TINYINT",
			"TINYTEXT",
			"TO",
			"TRAILING",
			"TRIGGER",
			"TRUE",
			"UNDO",
			"UNION",
			"UNIQUE",
			"UNLOCK",
			"UNSIGNED",
			"UPDATE",
			"USAGE",
			"USE",
			"USING",
			"UTC_DATE",
			"UTC_TIME",
			"UTC_TIMESTAMP",
			"VALUES",
			"VARBINARY",
			"VARCHAR",
			"VARCHARACTER",
			"VARYING",
			"WHILE",
			"WITH",
			"WRITE",
			"XOR",
			"YEAR_MONTH",
			"ZEROFILL",
		];

		$word = strtoupper(trim($word));

		if (in_array($word, $words))
		{
			return true;
		}

		return false;
	}
}