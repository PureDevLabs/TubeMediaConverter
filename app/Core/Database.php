<?php

	namespace MediaConverterPro\app\Core;

	// Database Abstraction Class
	class Database
	{
		// Private Fields
		private $_mysqli;
		private $_cache;

		#region Public Methods
		public function __construct(array $login=array())
		{
			if (isset($login['server'], $login['dbUser'], $login['dbPassword'], $login['db']))
			{
				$this->Connect($login['server'], $login['dbUser'], $login['dbPassword'], $login['db']);
			}
			if (extension_loaded('redis') && isset($login['redisServer'], $login['redisPort'], $login['redisPassword']))
			{
				$this->ConnectCache($login['redisServer'], $login['redisPort'], $login['redisPassword']);
			}
		}
		
		public function __destruct()
		{
			$this->Close();
		}		
		
		public function Connect($server, $dbUser, $dbPassword, $db)
		{
			$this->_mysqli = new \mysqli($server, $dbUser, $dbPassword, $db);
		}

		public function ConnectCache($server, $port, $password)
		{
			$redis = new \Redis();
			$success = $redis->connect($server, $port);
			if ($success)
			{
				$success = $redis->auth($password);
				if ($success)
				{
					$this->_cache = $redis;
				}
			}
		}		

		public function Close()
		{
			$this->_mysqli->close();
		}

		public function Save($table, array $data, $cacheKey='')
		{
			if (isset($data['id']))
			{
				// Update record
				$dataID = $data['id'];
				unset($data['id']);
				$query = 'UPDATE ' . $this->Sanitize($table) . ' SET ';
				$dataKeys = array_keys($data);
				foreach ($data as $field => $value)
				{
					$query .= $this->Sanitize($field) . '="' . $this->Sanitize($value) . '"';
					$query .= ($field != end($dataKeys)) ? ', ' : '';
				}
				$query .= ' WHERE id=' . $this->Sanitize($dataID);
				$this->_mysqli->query($query);
			}
			else
			{
				// Add new record
				$data = (!is_array($data[0])) ? array($data) : $data;
				$rowValues = '';
				$columns = array();
				foreach ($data as $d)
				{
					$columns = array_keys($d);
					$values = array_values($d);
					array_walk($columns, array($this, 'PrepareDataArray'), false);
					array_walk($values, array($this, 'PrepareDataArray'), true);
					$rowValues .= "(" . implode(',', $values) . ")";
					$rowValues .= ($d != end($data)) ? ", " : "";
				}
				$this->_mysqli->query("INSERT INTO " . $this->Sanitize($table) . " (" . implode(',', $columns) . ") VALUES " . $rowValues);
			}
			if (!empty($cacheKey) && !is_null($this->_cache) && $this->_cache->exists($cacheKey) > 0)
			{
				$this->_cache->del($cacheKey);
			}
			if (isset($this->_mysqli->insert_id) && !empty($this->_mysqli->insert_id)) return $this->_mysqli->insert_id;
		}
		
		public function UpdateAll($table, array $data)
		{
			$query = 'UPDATE ' . $this->Sanitize($table) . ' SET ';
			$dataKeys = array_keys($data);
			foreach ($data as $field => $value)
			{
				$query .= $this->Sanitize($field) . '="' . $this->Sanitize($value) . '"';
				$query .= ($field != end($dataKeys)) ? ', ' : '';
			}
			$this->_mysqli->query($query);			
		}

		public function Find($table, array $options, $cacheKey='', $cacheExpire=3600)
		{
			if (!empty($cacheKey) && !is_null($this->_cache) && $this->_cache->exists($cacheKey) > 0)
			{
				$resultArray = unserialize($this->_cache->get($cacheKey));
				if (is_array($resultArray)) return $resultArray;
			}
			$query = "SELECT ";
			if (isset($options['fields']))
			{
				foreach ($options['fields'] as $field)
				{
					$query .= $this->Sanitize($field);
					$query .= ($field != end($options['fields'])) ? "," : "";
				}
			}
			else
			{
				$query .= "*";
			}
			$query .= " FROM " . $this->Sanitize($table);
			if (isset($options['conditions']))
			{
				$query .= " WHERE ";
				$numConditions = 0;
				foreach ($options['conditions'] as $field => $val)
				{
					$query .= $this->Sanitize($field) . "='" . $this->Sanitize($val) . "'";
					$query .= (++$numConditions != count($options['conditions'])) ? " AND " : "";
				}
			}
			if (isset($options['conditions2']))
			{
				$query .= " WHERE ";
				$query .= $this->Sanitize($options['conditions2'][0]);
			}
			if (isset($options['order']))
			{
				$query .= " ORDER BY ";
				foreach ($options['order'] as $field)
				{
					$query .= $this->Sanitize($field);
					$query .= ($field != end($options['order'])) ? "," : "";
				}
			}
			if (isset($options['limit']))
			{
				$query .= " LIMIT ";
				$query .= $this->Sanitize($options['limit'][0]);
				$query .= (isset($options['limit'][1])) ? ", " . $this->Sanitize($options['limit'][1]) : "";
			}
			$result = $this->_mysqli->query($query);
			$resultArray = array();
			if ($this->_mysqli->field_count > 0)
			{
				while ($row = $result->fetch_assoc())
				{
					$resultArray[] = $row;
				}
			}
			$result->free();
			if (!empty($cacheKey) && !is_null($this->_cache))
			{
				$this->_cache->set($cacheKey, serialize($resultArray));
				$this->_cache->expire($cacheKey, $cacheExpire);
			}
			return $resultArray;
		}

		public function Count($table)
		{
			//$query = "SELECT COUNT(*) AS cnt FROM " . $this->Sanitize($table);
			$query = "SELECT MAX(id) AS cnt FROM " . $this->Sanitize($table);
			$result = $this->_mysqli->query($query);
			if ($this->_mysqli->field_count > 0)
			{
				while ($row = $result->fetch_assoc())
				{
					$result->free();
					return $row['cnt'];
				}
			}
		}

		public function RetrieveTableFields($table)
		{
			$result = $this->_mysqli->query("SHOW FIELDS FROM " . $this->Sanitize($table));
			$fields = array();
			if ($this->_mysqli->field_count > 0)
			{
				while ($row = $result->fetch_assoc())
				{
					$fields[] = $row['Field'];
				}
			}
			$result->free();
			return $fields;
		}
		#endregion

		#region Private "Helper" Methods
		private function Sanitize($val)
		{
			return $this->_mysqli->real_escape_string($val);
		}

		private function PrepareDataArray(&$val, $key, $isValue)
		{
			$val = ($isValue) ? "'".$this->Sanitize($val)."'" : $this->Sanitize($val);
		}
		#endregion
	}

?>