<?php
// ==================================================
// ==================================================
// /!\      このファイルは使用されていません      /!\
// ==================================================
// ==================================================

/**
 * ベンチャー・リンク　すらら
 *
 * 履歴
 * 2012/03/26 初期設定
 *
 * @author Azet
 */

class apiDB {
	var $_dbnames;
	var $_handles;
	var $_message;
	var $_errno;

	function apiDB() {
		$this->_message = "";
		$this->_errno = 0;
		$this->_handles = array();
		$this->_dbnames = array();
		register_shutdown_function(array($this, "logout"));
	}

	function getDBConnectTogo() {
		$ini_file = get_cfg_var('SURALA_ENTRY_INI');
		if (!is_file($ini_file) ) {
			return ERR_OTHER;
		}

		$arr = parse_ini_file($ini_file, true);

		$server = $arr['db_host'];
		if (isset($arr['db_port'])) $server .= ':'.$arr['db_port'];
		$db_user = $arr['db_user'];
		$db_pass = $arr['db_pass'];
		$db_name = $arr['db_name'];

		if (!($this->_handles[0] = mysql_connect($server, $db_user, $db_pass))) {
			return false;
		}

		if (mysql_select_db($db_name, $this->_handles[0]) === false) {
			return false;
		}
		$this->_dbnames[0] = $db_name;

		return true;
	}

	function getDBConnectBnsn($dbid = null) {

		if (count($this->_handles) == 0) {
			return false;
		}

		$sql = "select * from db where mk_flg='0'";
		if (!empty($dbid)) {
			$dbid = mysql_real_escape_string($dbid);
			$sql .= " and db_id='$dbid'";
		}

		if (!($resource = mysql_query($sql, $this->_handles[0]))) {
			$this->_message = mysql_error($this->_handles[0]);
			$this->_errno = mysql_errno($this->_handles[0]);
			return false;
		}

		$results = array();
		if (mysql_num_rows($resource) > 0) {
			while ($row = mysql_fetch_assoc($resource)) {
				$results[] = $row;
			}
		}
		mysql_free_result($resource);

		$hcnt = count($this->_handles) - 1;
		foreach ($results as $item) {
			$hcnt++;
			$server = $item['db_host'];
			if (isset($item['db_port'])) $server .= ':'.$item['db_port'];
			$db_user = $item['db_user'];
			$db_pass = $item['db_pass'];
			$db_name = $item['db_name'];

			if (!($this->_handles[$hcnt] = mysql_connect($server, $db_user, $db_pass))) {
				return false;
			}
			if (mysql_select_db($db_name, $this->_handles[$hcnt]) === false) {
				return false;
			}
			$this->_dbnames[$hcnt] = $db_name;
		}
		return true;
	}

    function query($sql, $hcnt = 0) {
		$this->_message = "";
		$this->_errno = 0;

		if (count($this->_handles) == 0) {
			return false;
		}
		if ($hcnt != 0) {
			if (!isset($this->_handles[$hcnt])) {
				return false;
			}
		}

		if (mysql_select_db($this->_dbnames[$hcnt], $this->_handles[$hcnt]) === false) {
			return false;
		}
		if (!($resource = mysql_query($sql, $this->_handles[$hcnt]))) {
			$this->_message = mysql_error($this->_handles[$hcnt]);
			$this->_errno = mysql_errno($this->_handles[$hcnt]);
			return false;
		}
		$results = array();
		if (mysql_num_rows($resource) > 0) {
			while ($row = mysql_fetch_assoc($resource)) {
				$results[] = $row;
			}
		}
		mysql_free_result($resource);
    	return $results;
    }

	function auto_incr_insert($sql, &$last_insert_id) {
		$this->_message = "";
		$this->_errno = 0;
		$last_insert_id = '';
pre($this->_handles);
		for ($idx = 0; $idx < count($this->_handles); $idx++) {
			if ($this->_handles[$idx] != null) {
				if (mysql_select_db($this->_dbnames[$idx], $this->_handles[$idx]) === false) {
					return false;
				}
echo $this->_handles[$idx]."<>".$sql."<br>\n";
				if (!mysql_query($sql, $this->_handles[$idx])) {
					$this->_message = mysql_error($this->_handles[$idx]);
					$this->_errno = mysql_errno($this->_handles[$idx]);
echo "11<br>\n";
					return false;
				}

				$sql2 = "select last_insert_id()";
				if (!($resource = mysql_query($sql2, $this->_handles[$idx]))) {
					$this->_message = mysql_error($this->_handles[$idx]);
					$this->_errno = mysql_errno($this->_handles[$idx]);
echo "22<br>\n";
					return false;
				}
				if (mysql_num_rows($resource) > 0) {
					$row = mysql_fetch_row($resource);
					$tmp_last_id = $row[0];
				}

				if ($last_insert_id === '') {
					$last_insert_id = $tmp_last_id;
				} else if ($last_insert_id != $tmp_last_id) {
					$last_insert_id = null;
echo "33<br>\n";
					return false;
				}
			}
		}
		return true;
	}

	function execute($sql) {
		$this->_message = "";
		$this->_errno = 0;

		for ($idx = 0; $idx < count($this->_handles); $idx++) {
			if ($this->_handles[$idx] != null) {
				if (mysql_select_db($this->_dbnames[$idx], $this->_handles[$idx]) === false) {
					return false;
				}

				if (!mysql_query($sql, $this->_handles[$idx])) {
					$this->_message = mysql_error($this->_handles[$idx]);
					$this->_errno = mysql_errno($this->_handles[$idx]);
					return false;
				}
			}
		}
		return true;
	}

	function startTransaction() {
		$sql = "start transaction";

		if (!$this->execute($sql)) {
			return false;
		}
		return true;
	}

	function commit() {
		$sql = "commit";

		if (!$this->execute($sql)) {
			return false;
		}

		return true;
	}

	function rollback() {

		$sql = "rollback";
		if (!$this->execute($sql)) {
			return false;
		}
		return true;
	}

	function logout() {
 		for ($idx = 0; $idx < count($this->_handles); $idx++) {
			mysql_close($this->_handles[$idx]);
		}
		$this->_handles = array();

		return true;
	}

	function getMessage() {
		return $this->_message;
	}

	function getErrno() {
		return $this->_errno;
	}

}
?>
