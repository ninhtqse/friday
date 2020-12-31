<?php
//require_once("dbAPI.php");	//	del ookawara 2011/10/17
require_once(LOG_DIR . "api/dbApi.php");	//	add ookawara 2011/10/17

// エラーコード
define('SUCCESS',					'0');
define('ERR_TEST_GROUP_ID',			'-100');
define('ERR_TEST_GROUP_NAME',		'-101');
define('ERR_TEST_GKNN',				'-102');
define('ERR_KKN_FROM',				'-103');
define('ERR_KKN_TO',				'-104');
define('ERR_DISP_SORT',				'-105');
define('ERR_MSKM_KKN_FROM',			'-106');
define('ERR_MSKM_KKN_TO',			'-107');
define('ERR_JYKO_CRS_CD',			'-108');
define('ERR_SYSI_CRS_CD',			'-109');
define('ERR_DISPLAY',				'-110');
define('ERR_USR_BKO',				'-111');
define('ERR_MANAGER_ID',			'-112');
define('ERR_DUP',					'-800');
define('ERR_TEST_GROUP_MASTER',		'-801');
define('ERR_TEST_GROUP_ID_DELETE',	'-802');
define('ERR_OTHER',					'-900');

define('REQUIRED',			'1');
define('DIGITS',			'2');
define('INPUT_FORMAT',		'3');
define('TIME',				'4');
define('INPUT_CD',			'5');
define('NOT_SET',			'6');

// アイコン有無
define('JYKO_CRS_CD',			'GT');
define('SYSI_CRS_CD',			'2GT');

define('SCH_YEAR',		'12');
define('C48_ONCE',		'2');


function addTestGroup($test_group_name, $test_gknn, $kkn_from, $kkn_to, $disp_sort, $mskm_kkn_from, $mskm_kkn_to, $jyko_crs_cd, $sysi_crs_cd, $display, $usr_bko, $maneger_id, &$test_group_id) {

	$test_group_id = null;
	$parameters = array();
	$parameters['test_group_name'] = $test_group_name;
	$parameters['test_gknn'] = $test_gknn;
	$parameters['kkn_from'] = $kkn_from;
	$parameters['kkn_to'] = $kkn_to;
	$parameters['disp_sort'] = $disp_sort;
	$parameters['mskm_kkn_from'] = $mskm_kkn_from;
	$parameters['mskm_kkn_to'] = $mskm_kkn_to;
	$parameters['jyko_crs_cd'] = $jyko_crs_cd;
	$parameters['sysi_crs_cd'] = $sysi_crs_cd;
	$parameters['display'] = $display;
	$parameters['usr_bko'] = $usr_bko;
	$parameters['ins_tts_id'] = $maneger_id;

	if (($ret = checkTestGroupParam($parameters)) < 0) {
		return $ret;
	}

	$handles = array();
	$db = new apiDB();
	if (!($db->getDBConnectTogo())) {
		return ERR_OTHER;
	}

	// 学年チェック
	$sql = checkGknn($parameters['test_gknn']);
	if (($results = $db->query($sql)) === false) {
echo "1<br>\n";
		return ERR_OTHER;
	}
	if ($results[0]['cnt'] == '0' && strpos($parameters['test_gknn'], 'R') === false) {
		return ERR_TEST_GKNN.INPUT_CD;
	}

	if ($parameters['jyko_crs_cd'] != null && $parameters['sysi_crs_cd'] != null) {
		// 受講コースコードチェック
		$sql = checkJykoCrscd($parameters['jyko_crs_cd']);
		if (($results = $db->query($sql)) === false) {
echo "2<br>\n";
			return ERR_OTHER;
		}
		if ($results[0]['cnt'] == '0') {
			return ERR_JYKO_CRS_CD.INPUT_CD;
		}

		// 詳細コースコードチェック
		$sql = checkSysiCrscd($parameters['jyko_crs_cd'], $parameters['sysi_crs_cd']);
		if (($results = $db->query($sql)) === false) {
echo "3<br>\n";
			return ERR_OTHER;
		}
		if ($results[0]['cnt'] == '0') {
			return ERR_SYSI_CRS_CD.INPUT_CD;
		}
	}

	$ms_test_group = new ms_test_group($parameters);

	$sql = $ms_test_group->getSqlDupcheck();
	if (($results = $db->query($sql)) === false) {
echo "4<br>\n";
		return ERR_OTHER;
	}
	if ($results[0]['cnt'] != '0') {
		return ERR_DUP;
	}

	if (!($db->getDBConnectBnsn())) {
echo "5<br>\n";
		return ERR_OTHER;
	}

	if (!($db->startTransaction())) {
echo "6<br>\n";
		return ERR_OTHER;
	}

	$sql = $ms_test_group->getInsertSql();
echo "sql<>".$sql."<br>\n";
echo "test_group_id<>".$test_group_id."<br>\n";
	if (!($db->auto_incr_insert($sql, $test_group_id))) {
		$db->rollback();
echo "7<br>\n";
		return ERR_OTHER;
	}

	$db->commit();
	$db->logout();

	return SUCCESS;
}

function updTestGroup($test_group_id, $test_group_name, $test_gknn, $kkn_from, $kkn_to, $disp_sort, $mskm_kkn_from, $mskm_kkn_to, $jyko_crs_cd, $sysi_crs_cd, $display, $usr_bko, $maneger_id) {

	$parameters = array();
	$parameters['test_group_id'] = $test_group_id;
	$parameters['test_group_name'] = $test_group_name;
	$parameters['test_gknn'] = $test_gknn;
	$parameters['kkn_from'] = $kkn_from;
	$parameters['kkn_to'] = $kkn_to;
	$parameters['disp_sort'] = $disp_sort;
	$parameters['mskm_kkn_from'] = $mskm_kkn_from;
	$parameters['mskm_kkn_to'] = $mskm_kkn_to;
	$parameters['jyko_crs_cd'] = $jyko_crs_cd;
	$parameters['sysi_crs_cd'] = $sysi_crs_cd;
	$parameters['display'] = $display;
	$parameters['usr_bko'] = $usr_bko;
	$parameters['upd_tts_id'] = $maneger_id;

	if (($ret = checkTestGroupParam($parameters, true)) < 0) {
		return $ret;
	}

	$handles = array();
	$db = new apiDB();
	if (!($db->getDBConnectTogo())) {
		return ERR_OTHER;
	}

	// テストグループIDチェック
	$ms_test_group = new ms_test_group($parameters);
	$sql = $ms_test_group->checkTestGroupId($parameters['test_group_id']);
	if (($results = $db->query($sql)) === false) {
		return ERR_OTHER;
	}
	if ($results[0]['cnt'] == '0') {
		return ERR_TEST_GROUP_MASTER;
	}

	// 学年チェック
	$sql = checkGknn($parameters['test_gknn']);
	if (($results = $db->query($sql)) === false) {
		return ERR_OTHER;
	}
	if ($results[0]['cnt'] == '0' && strpos($parameters['test_gknn'], 'R') === false) {
		return ERR_TEST_GKNN.INPUT_CD;
	}

	if ($parameters['jyko_crs_cd'] != null && $parameters['sysi_crs_cd'] != null) {
	// 受講コースコードチェック
		$sql = checkJykoCrscd($parameters['jyko_crs_cd']);
		if (($results = $db->query($sql)) === false) {
			return ERR_OTHER;
		}
		if ($results[0]['cnt'] == '0') {
			return ERR_JYKO_CRS_CD.INPUT_CD;
		}

		// 詳細コースコードチェック
		$sql = checkSysiCrscd($parameters['jyko_crs_cd'], $parameters['sysi_crs_cd']);
		if (($results = $db->query($sql)) === false) {
			return ERR_OTHER;
		}
		if ($results[0]['cnt'] == '0') {
			return ERR_SYSi_CRS_CD.INPUT_CD;
		}
	}

	if (!($db->getDBConnectBnsn())) {
		return ERR_OTHER;
	}

	if (!($db->startTransaction())) {
		return ERR_OTHER;
	}

	$sql = $ms_test_group->getUpdateSql(true);
	if (!($db->execute($sql))) {
		$db->rollback();
		return ERR_OTHER;
	}

	$db->commit();
	$db->logout();

	return SUCCESS;
}

function delTestGroup($test_group_id, $maneger_id) {

	$parameters = array();
	$parameters['test_group_id'] = $test_group_id;
	$parameters['mk_tts_id'] = $maneger_id;

	if (($ret = checkDelTestGroupParam($parameters)) < 0) {
		return $ret;
	}

	$handles = array();
	$db = new apiDB();
	if (!($db->getDBConnectTogo())) {
		return ERR_OTHER;
	}

	// テストグループIDチェック
	$ms_test_group = new ms_test_group($parameters);
	$sql = $ms_test_group->checkTestGroupId($parameters['test_group_id']);
	if (($results = $db->query($sql)) === false) {
		return ERR_OTHER;
	}
	if ($results[0]['cnt'] == '0') {
		return ERR_TEST_GROUP_MASTER;
	}

	// 受講生徒存在チェック
	$sql = checkJykoTestGroupId($parameters['test_group_id']);
	if (($results = $db->query($sql)) === false) {
		return ERR_OTHER;
	}
	if ($results[0]['cnt'] > '0') {
		return ERR_TEST_GROUP_ID_DELETE;
	}

	if (!($db->getDBConnectBnsn())) {
		return ERR_OTHER;
	}

	if (!($db->startTransaction())) {
		return ERR_OTHER;
	}

	$sql = $ms_test_group->getUpdateSql();
	if (!($db->execute($sql))) {
		$db->rollback();
		return ERR_OTHER;
	}

	$db->commit();
	$db->logout();

	return SUCCESS;

}

function checkTestGroupParam(&$parameters, $is_upd = false) {

	// テストグループID
	if ($is_upd) {
		if ($parameters['test_group_id'] == null) {
			return ERR_TEST_GROUP_ID.REQUIRED;
		}
	}

	// テストグループ名
	if ($parameters['test_group_name'] == null) {
		return ERR_TEST_GROUP_NAME.REQUIRED;
	}

	if (mb_strlen($parameters['test_group_name']) > 80) {
		return ERR_TEST_GROUP_NAME.DIGITS;
	}

	// 学年
	if ($parameters['test_gknn'] == null) {
		return ERR_TEST_GKNN.REQUIRED;
	}

	if (mb_strlen($parameters['test_gknn']) > 10) {
		return ERR_TEST_GKNN.DIGITS;
	}

	// 受験期間（FROM）
 	if ($parameters['kkn_from'] == null) {
		return ERR_KKN_FROM.REQUIRED;
	}

	if (!replaceDateCheck($parameters['kkn_from'])) {
		return ERR_KKN_FROM.INPUT_FORMAT;
	}

	if ($parameters['kkn_from'] != null && !validateDate($parameters['kkn_from'])) {
		return ERR_KKN_FROM.INPUT_FORMAT;
	}

	// 受験期間（TO）
	if (!replaceDateCheck($parameters['kkn_to'])) {
		return ERR_KKN_TO.INPUT_FORMAT;
	}

	if ($parameters['kkn_to'] != null && !validateDate($parameters['kkn_to'])) {
		return ERR_KKN_TO.INPUT_FORMAT;
	}

	if ($parameters['kkn_from'] != null && $parameters['kkn_to'] != null) {
		if (strtotime($parameters['kkn_to']) < strtotime($parameters['kkn_from'])) {
			return ERR_KKN_TO.TIME;
		}
	}

	// ソート順
	if ($parameters['disp_sort'] == null) {
		return ERR_DISP_SORT.REQUIRED;
	}

	if (!validateSuji($parameters['disp_sort'])){
			return ERR_DISP_SORT.INPUT_FORMAT;
	}

	if (!replaceDateCheck($parameters['mskm_kkn_from'])) {
		return ERR_MSKM_KKN_FROM.INPUT_FORMAT;
	}

	if ($parameters['mskm_kkn_from'] != null && !validateDate($parameters['mskm_kkn_from'])) {
		return ERR_MSKM_KKN_FROM.INPUT_FORMAT;
	}

	if ($parameters['mskm_kkn_from'] == null) {
		// 受講コースCD詳細コースCD 指定不可
		if ($parameters['jyko_crs_cd'] != null) {
			return ERR_JYKO_CRS_CD.NOT_SET;
		}
		if ($parameters['sysi_crs_cd'] != null) {
			return ERR_SYSI_CRS_CD.NOT_SET;
		}
		// 申込期間（TO）指定不可
		if ($parameters['mskm_kkn_to'] != null) {
			return ERR_MSKM_KKN_TO.NOT_SET;
		}
	} else {
		// 受講コースCD/詳細コースCD必須チェック
		if ($parameters['jyko_crs_cd'] == null) {
			return ERR_JYKO_CRS_CD.REQUIRED;
		}
		if ($parameters['sysi_crs_cd'] == null) {
			return ERR_SYSI_CRS_CD.REQUIRED;
		}
	}

	// 申込期間（TO）
	if (!replaceDateCheck($parameters['mskm_kkn_to'])) {
		return ERR_MSKM_KKN_TO.INPUT_FORMAT;
	}

	if ($parameters['mskm_kkn_to'] != null && !validateDate($parameters['mskm_kkn_to'])) {
		return ERR_MSKM_KKN_TO.INPUT_FORMAT;
	}

	if ($parameters['mskm_kkn_from'] != null && $parameters['mskm_kkn_to'] != null) {
		if (strtotime($parameters['mskm_kkn_to']) < strtotime($parameters['mskm_kkn_from'])) {
			return ERR_MSKM_KKN_TO.TIME;
		}
	}

	// 表示・非表示
	if ($parameters['display'] != null) {
		if ($parameters['display'] != '1' && $parameters['display'] != '2') {
			return ERR_DISPLAY.INPUT_FORMAT;
		}
	} else {
		$parameters['display'] = '2';
	}

	// ユーザー備考
	if (mb_strlen($parameters['usr_bko']) > 80) {
		return ERR_USR_BKO.DIGITS;
	}

	// 本部管理ID
	if ($is_upd) {
		if (mb_strlen($parameters['upd_tts_id']) > 10) {
			return ERR_MANAGER_ID.DIGITS;
		}
	} else {
		if (mb_strlen($parameters['ins_tts_id']) > 10) {
			return ERR_MANAGER_ID.DIGITS;
		}
	}

	//受験期間がnullの場合セット
	if ($parameters['kkn_from'] == null) {
		$parameters['kkn_from'] = '0-0-0';
	}

	if ($parameters['kkn_to'] == null) {
		$parameters['kkn_to'] = '0-0-0';
	}

	return SUCCESS;
}

function checkDelTestGroupParam($parameters) {

	// テストグループID
	if ($parameters['test_group_id'] == null) {
		return ERR_TEST_GROUP_ID.REQUIRED;
	}

	// 本部管理ID
	if (mb_strlen($parameters['mk_tts_id']) > 10) {
		return ERR_MANAGER_ID.DIGITS;
	}

	return SUCCESS;
}

function checkGknn($test_gknn) {

	$test_gknn = mysql_real_escape_string($test_gknn);

	$sql = "select count(*) cnt from ms_cd_nm".
			" where bnr_cd = '".SCH_YEAR."' and komk_cd = '$test_gknn' and mk_flg='0'";

	return $sql;
}

function checkJykoCrscd($jyko_crs_cd) {

	$jyko_crs_cd = mysql_real_escape_string($jyko_crs_cd);

	$sql = "select count(*) cnt from ms_jyko_crs where jyko_crs_cd = '$jyko_crs_cd' and jyko_crs_kb= '".C48_ONCE."' and mk_flg='0'";

	return $sql;

}

function checkSysiCrscd($jyko_crs_cd, $sysi_crs_cd) {

	$jyko_crs_cd = mysql_real_escape_string($jyko_crs_cd);
	$sysi_crs_cd = mysql_real_escape_string($sysi_crs_cd);

	$sql = "select count(*) cnt from ms_jyko_crs_ucwk u".
			" where jyko_crs_cd = '$jyko_crs_cd'".
			" and sysi_crs_cd = '$sysi_crs_cd'".
			" and mk_flg=0";

	return $sql;
}

function checkJykoTestGroupId($test_group_id) {

	$test_group_id = mysql_real_escape_string($test_group_id);
	$sql = "select count(*) cnt from tb_stu_gtest_mskm where mk_flg='0'";
	$sql .= " and test_group_id='$test_group_id'";

	return $sql;
}

function validateSuji($data) {
	$pattern = "/^([0-9])+$/u";

	if (preg_match($pattern, $data)){
		return true;
	}

	return false;
}

function validateDate(&$val) {

	list($year, $momth, $day) = preg_split("/\-|\//", $val);
    // 日付妥当性チェック
    if (!checkdate($momth, $day, $year)) {
		return false;
    }
	$val = $year.'-'.$momth.'-'.$day;

	return true;
}

function replaceDateCheck(&$val) {
	$val = trim($val);

	if ($val != null) {
	    if (!preg_match('/^[0-9]{1,4}(\/|\-)[0-9]{1,2}(\/|\-)[0-9]{1,2}$/', $val)) {
			return false;
	    }

		list($year, $momth, $day) = preg_split("/\-|\//", $val);
		if((int)$year == 0 && (int)$momth == 0 && (int)$day == 0) {
			$val = null;
		}
	}
	return true;
}

class ms_test_group {
	var $_params;
	var $_tablename;

	function ms_test_group($parameters) {
		$this->_params = $parameters;
		$this->_tablename = 'ms_test_group';

	}

	// 重複
	function getSqlDupcheck() {
		$test_group_name = mysql_real_escape_string($this->_params['test_group_name']);
		$test_gknn = mysql_real_escape_string($this->_params['test_gknn']);
		$sql = "select count(*) cnt from {$this->_tablename} where mk_flg='0'";
		$sql .= " and test_group_name='{$test_group_name}'";
		$sql .= " and test_gknn='{$test_gknn}'";
		return $sql;
	}

	// 存在チェック
	function checkTestGroupId() {
		$test_group_id = mysql_real_escape_string($this->_params['test_group_id']);
		$sql = "select count(*) cnt from {$this->_tablename} where mk_flg='0'";
		$sql .= " and test_group_id='{$test_group_id}'";
		return $sql;
	}

	// 登録
	function getInsertSql() {
		$cols = '';
		$values = '';
		$this->_params['ins_syr_id'] = 'EntryAPI';
		foreach ($this->_params as $key => $value) {
			if ($value != null) {
				$value = mysql_real_escape_string($value);
				if (!empty($cols)) $cols .= ',';
				if (!empty($values)) $values .= ',';
				$cols .= $key;
				$values .= "'".$value."'";
			}
		}
		if (!empty($cols)) $cols .= ',';
		if (!empty($values)) $values .= ',';
		$cols .= 'ins_date';
		$values .= "now()";

		$sql = "insert into {$this->_tablename} ({$cols}) values({$values})";
		return $sql;
	}

	// 更新
	function getUpdateSql($is_upd = false) {
		$cols = '';
		$id = '';
		if ($is_upd) {
			$this->_params['upd_syr_id'] = 'EntryAPI';
		} else {
			$this->_params['mk_flg'] = '1';
		}

		foreach ($this->_params as $key => $value) {
			$value = mysql_real_escape_string($value);
			if (!empty($cols)) $cols .= ',';
			if ($key != 'test_group_id') {
				if ($value != null) {
					$cols .= "$key='".$value."'";
				} else {
					$cols .= "$key=null";
				}
			} else {
				$id = "$key='".$value."'";
			}
		}
		if (!empty($cols)) $cols .= ',';
		if (!empty($values)) $values .= ',';
		if ($is_upd) {
			$cols .= "upd_date=now()";
		} else {
			$cols .= "mk_date=now()";
		}
		$sql = "update {$this->_tablename} set {$cols} where {$id}";
		return $sql;
	}

}
?>
