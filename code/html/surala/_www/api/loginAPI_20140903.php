<?php

// エラーコード
define('SUCCESS',					'0');
define('ERR_WRONG_ID_PASSWD',		'-1');
define('ERR_EXPIRED',				'-2');
define('ERR_STOPPING',				'-3');
define('ERR_ATTENDANCE_STOP',		'-4');
define('ERR_NO_ATHOME',				'-5');
define('ERR_NO_COURSE',				'-6');

define('ERR_KTOOL_NON_CONTRACT',	'-2');
define('ERR_KTOOL_EXPIRED',			'-3');

define('ERR_OTHER',					'-100');


// アイコン有無
define('ICON_EXIST',			'1');
define('ICON_NO',				'2');

// 先生のログインタイプ
define('KANRI',					'1');
define('KYOMUTOOL',				'2');


// 加盟属性(05)
define('C05_ITTO',		'1');
define('C05_MYSRLNS',	'2');
define('C05_MYSRLAR',	'3');
define('C05_GAKKO',		'4');
define('C05_VL',		'5');
define('C05_SONOTA',	'9');

// 生徒受講状態(14)
define('C14_TRIAL',		'0');
define('C14_ZYUKOU',	'1');
define('C14_KYUUSHI',	'2');
define('C14_SYUURYOU',	'3');

//教務ツール利用フラグ(55)
define('C55_MIRIYOU',	'0');
define('C55_RIYOU',		'1');

// 生徒在宅フラグ (57)
define('C58_GAKKONOMI',	'0');
define('C58_ZAITAKU',	'1');

// 受講停止フラグ(58)
define('C58_RIYOUKA',	'0');
define('C58_TEISHI',	'1');

// 校内利用終了フラグ(61)
define('C61_RIYOCHU',	'0');
define('C61_END',		'1');

// 履修コース(45)
define('C45_A',			'A');

//オプション教科フラグ(67)
define('C67_NG',		'0');
define('C67_OK',		'1');

//受講サービス(81)
define('C81_SRL',		'SRL');

// 受講コース区分(48)
define('C48_ONCE',		'2');


function getUserInfo($loginID, $passWord, $iconFlg, &$userInfo) {

	$userInfo = array();

	// 統合ＤＢへのログイン
	$handle = null;
	if (($ret = loginApiDbLogin($handle)) < 0 ) {
		return $ret;
	}

	if ($handle == null) return $ret;

	// ログインユーザ情報の取得
	$loginInfo = array();
	if( ($ret = LoginApiGetLoginInfo($handle, $loginID, $iconFlg, $loginInfo)) < 0) {
		return $ret;
	}

	// DBログオフ
	mysql_close($handle);

	// ログインID , パスワードの正当性チェック
	if ($loginInfo == null) {
		return ERR_WRONG_ID_PASSWD;
	}
	if ($loginInfo['pass'] != $passWord) {
		return ERR_WRONG_ID_PASSWD;
	}

	if ($iconFlg == ICON_NO) {
		// アイコン無しログインのチェック（在宅利用）
		$ret = loginApiIconNo($loginInfo);
	} else if ($iconFlg == ICON_EXIST) {
		// アイコン有りログインのチェック
		$ret = loginApiIconExist($loginInfo);
	} else {
		return ERR_OTHER;
	}

	$userInfo['DBHOST'] = $loginInfo['db_host'];
	$userInfo['DBNAME'] = $loginInfo['db_name'];
	$userInfo['DBUSER'] = $loginInfo['db_user'];
	$userInfo['DBPASSWD'] = $loginInfo['db_pass'];
	$userInfo['DBPORT'] = $loginInfo['db_port'];
	if ($ret == SUCCESS) {
		$userInfo['COURSE'] = $loginInfo['jyko_surala_cd'];
		$userInfo['OPTION'] = $loginInfo['opt_kyks'];
		$userInfo['KIYKHNI'] = $loginInfo['kiyk_hni'];
		$userInfo['RSYCRS'] = $loginInfo['rsyu_crs'];
		$userInfo['TEST_GROUP_IDS'] = null;
		if (count($loginInfo['test_group_ids']) > 0) {
			$userInfo['TEST_GROUP_IDS'] = implode(',', $loginInfo['test_group_ids']);
		}
	} else {
		$userInfo['COURSE'] = null;
		$userInfo['OPTION'] = null;
		$userInfo['KIYKHNI'] = null;
		$userInfo['RSYCRS'] = null;
		$userInfo['TEST_GROUP_IDS'] = null;
	}

	return $ret;
}

function getTeacherInfo ($loginID, $passWord, $loginType, &$teacherInfo) {

	$teacherInfo = array();

	// 統合ＤＢへのログイン
	$handle = null;
	if (($ret = loginApiDbLogin($handle)) < 0 ) {
		return $ret;
	}
	if ($handle == null) return $ret;

	// ログインユーザ情報(先生)の取得
	$loginInfoTeacher = array();
	if( ($ret = loginApiGetLoginInfoTeacher($handle, $loginID, $loginInfoTeacher)) < 0) {
		return $ret;
	}

	// DBログオフ
	mysql_close($handle);

	// ログインID , パスワードの正当性チェック
	if ($loginInfoTeacher == null) {
		return ERR_WRONG_ID_PASSWD;
	}
	if ($loginInfoTeacher['pass'] != $passWord) {
		return ERR_WRONG_ID_PASSWD;
	}

	// 教務ツールの契約チェック
	if ($loginType == KYOMUTOOL && $loginInfoTeacher['km_tool_use_flg'] == C55_MIRIYOU) {
		return ERR_KTOOL_NON_CONTRACT;
	}

	// 有効期限チェック
	if ($loginInfoTeacher['end_ymd'] < 0) {
		return ERR_KTOOL_EXPIRED;
	}

	$teacherInfo['DBHOST'] = $loginInfoTeacher['db_host'];
	$teacherInfo['DBNAME'] = $loginInfoTeacher['db_name'];
	$teacherInfo['DBUSER'] = $loginInfoTeacher['db_user'];
	$teacherInfo['DBPASSWD'] = $loginInfoTeacher['db_pass'];
	$teacherInfo['DBPORT'] = $loginInfoTeacher['db_port'];
	if ($ret == SUCCESS) {
		$teacherInfo['KIYKHNI'] = $loginInfoTeacher['kiyk_hni'];
	} else {
		$teacherInfo['KIYKHNI'] = null;
	}

	return SUCCESS;

}

function getGuardianInfo($loginID, $passWord, &$guardianInfo) {

	$guardianInfo = array();

	// 統合ＤＢへのログイン
	$handle = null;
	if (($ret = loginApiDbLogin($handle)) < 0 ) {
		return $ret;
	}

	if ($handle == null) return $ret;

	// ログインユーザ情報の取得
	$loginInfoGua = array();
	if( ($ret = LoginApiGetLoginInfoGuardian($handle, $loginID, $loginInfoGua)) < 0) {
		return $ret;
	}

	// DBログオフ
	mysql_close($handle);

	// ログインID , パスワードの正当性チェック
	if ($loginInfoGua == null) {
		return ERR_WRONG_ID_PASSWD;
	}
	if ($loginInfoGua['pass'] != $passWord) {
		return ERR_WRONG_ID_PASSWD;
	}

	// 生徒アイコン無しログインチェックを流用
	if (($ret = loginApiIconNo($loginInfoGua)) == SUCCESS) {
		// 履修コースのチェック
		if ($loginInfoGua['rsyu_crs'] != C45_A) {
			$ret = ERR_NO_COURSE;
		}
	}

	$guardianInfo['DBHOST'] = $loginInfoGua['db_host'];
	$guardianInfo['DBNAME'] = $loginInfoGua['db_name'];
	$guardianInfo['DBUSER'] = $loginInfoGua['db_user'];
	$guardianInfo['DBPASSWD'] = $loginInfoGua['db_pass'];
	$guardianInfo['DBPORT'] = $loginInfoGua['db_port'];
	if ($ret == SUCCESS) {
		$guardianInfo['COURSE'] = $loginInfoGua['jyko_surala_cd'];
		$guardianInfo['OPTION'] = $loginInfoGua['opt_kyks'];
		$guardianInfo['KIYKHNI'] = $loginInfoGua['kiyk_hni'];
		$guardianInfo['RSYCRS'] = $loginInfoGua['rsyu_crs'];
	} else {
		$guardianInfo['COURSE'] = null;
		$guardianInfo['OPTION'] = null;
		$guardianInfo['KIYKHNI'] = null;
		$guardianInfo['RSYCRS'] = null;
	}

	return $ret;
}

function getUserGtestInfo($loginID, $passWord, $iconFlg, &$userInfo) {

	$userInfo = array();

	// 統合ＤＢへのログイン
	$handle = null;
	if (($ret = loginApiDbLogin($handle)) < 0 ) {
		return $ret;
	}

	if ($handle == null) return $ret;

	// ログインユーザ情報の取得
	$loginInfo = array();
	if( ($ret = LoginApiGetLoginInfoGtest($handle, $loginID, $iconFlg, $loginInfo)) < 0) {
		return $ret;
	}

	// DBログオフ
	mysql_close($handle);

	// ログインID , パスワードの正当性チェック
	if (count($loginInfo) == 0) {
		return ERR_WRONG_ID_PASSWD;
	}
	if ($loginInfo['pass'] != $passWord) {
		return ERR_WRONG_ID_PASSWD;
	}

	if ($iconFlg == ICON_NO) {
		// アイコン無しログインのチェック（在宅利用）
		$ret = loginApiIconNo($loginInfo, true);
	} else if ($iconFlg == ICON_EXIST) {
		// アイコン有りログインのチェック
		$ret = loginApiIconExist($loginInfo, true);
	} else {
		return ERR_OTHER;
	}

	$userInfo['DBHOST'] = $loginInfo['db_host'];
	$userInfo['DBNAME'] = $loginInfo['db_name'];
	$userInfo['DBUSER'] = $loginInfo['db_user'];
	$userInfo['DBPASSWD'] = $loginInfo['db_pass'];
	$userInfo['DBPORT'] = $loginInfo['db_port'];
	$userInfo['TEST_GROUP_IDS'] = null;
	if ($ret == SUCCESS) {
		if (count($loginInfo['test_group_ids']) > 0) {
			$userInfo['TEST_GROUP_IDS'] = implode(',', $loginInfo['test_group_ids']);
		}
	}

	return $ret;
}

function getUserCourseInfo($loginID, $iconFlg, &$userInfo) {

	$userInfo = array();

	// 統合ＤＢへのログイン
	$handle = null;
	if (($ret = loginApiDbLogin($handle)) < 0 ) {
		return $ret;
	}

	if ($handle == null) return $ret;

	// 生徒のすらら受講コース情報取得
	$loginInfo = array();
	if( ($ret = LoginApiGetLoginInfo($handle, $loginID, $iconFlg, $loginInfo, true)) < 0) {
		return $ret;
	}

	// DBログオフ
	mysql_close($handle);

	// ログインID , パスワードの正当性チェック
	if ($loginInfo == null) {
		return ERR_WRONG_ID_PASSWD;
	}

	if ($iconFlg == ICON_NO) {
		// アイコン無しログインのチェック（在宅利用）
		$ret = loginApiIconNo($loginInfo);
	} else if ($iconFlg == ICON_EXIST) {
		// アイコン有りログインのチェック
		$ret = loginApiIconExist($loginInfo);
	} else {
		return ERR_OTHER;
	}

	if ($ret == SUCCESS) {
		$userInfo['COURSE'] = $loginInfo['jyko_surala_cd'];
		$userInfo['OPTION'] = $loginInfo['opt_kyks'];
		$userInfo['KIYKHNI'] = $loginInfo['kiyk_hni'];
		$userInfo['RSYCRS'] = $loginInfo['rsyu_crs'];
	} else {
		$userInfo['COURSE'] = null;
		$userInfo['OPTION'] = null;
		$userInfo['KIYKHNI'] = null;
		$userInfo['RSYCRS'] = null;
	}

	return $ret;
}

function getUserServiceInfo($loginID, $passWord, $iconFlg, &$userInfo) {

	$userInfo = array();

	// 統合ＤＢへのログイン
	$handle = null;
	if (($ret = loginApiDbLogin($handle)) < 0 ) {
		return $ret;
	}

	if ($handle == null) return $ret;
	// ログインユーザ情報の取得
	$loginInfo = array();
	if( ($ret = LoginApiGetLoginInfoService($handle, $loginID, $iconFlg, $loginInfo)) < 0) {
		return $ret;
	}

	// DBログオフ
	mysql_close($handle);

	// ログインID , パスワードの正当性チェック
	if ($loginInfo == null) {
		return ERR_WRONG_ID_PASSWD;
	}
	if ($loginInfo['pass'] != $passWord) {
		return ERR_WRONG_ID_PASSWD;
	}

	if ($iconFlg == ICON_NO) {
		// アイコン無しログインのチェック（在宅利用）
		$ret = loginApiIconNo($loginInfo, null, true);
	} else if ($iconFlg == ICON_EXIST) {
		// アイコン有りログインのチェック
		$ret = loginApiIconExist($loginInfo, null, true);
	} else {
		return ERR_OTHER;
	}

	$userInfo['DBHOST'] = $loginInfo['db_host'];
	$userInfo['DBNAME'] = $loginInfo['db_name'];
	$userInfo['DBUSER'] = $loginInfo['db_user'];
	$userInfo['DBPASSWD'] = $loginInfo['db_pass'];
	$userInfo['DBPORT'] = $loginInfo['db_port'];
	if ($ret == SUCCESS) {
		$userInfo['OPTION'] = $loginInfo['opt_kyks'];
	} else {
		$userInfo['OPTION'] = null;
	}

	return $ret;
}

// アイコン有りログインのチェック
function loginApiIconExist($loginInfo, $is_gtest = false, $is_service = false) {

	// 校舎有効期限のチェック
	if ($loginInfo['end_ymd'] < 0) {
		return ERR_EXPIRED;
	}

	// 生徒・校内利用終了フラグのチェック
	if ($loginInfo['knry_end_flg'] == C61_END) {
		return ERR_EXPIRED;
	}

	// 在宅フラグのチェック
	if ($loginInfo['zitk_flg'] == C58_GAKKONOMI || $loginInfo['zitk_flg'] == C58_ZAITAKU) {
		return SUCCESS;
	}

	// 生徒・受講停止フラグのチェック
	if($loginInfo['jyko_stop_flg'] == C58_TEISHI) {
		return ERR_ATTENDANCE_STOP;
	}

	// 学力診断向けチェックならこの時点でOK
	if ($is_gtest) {
		return SUCCESS;
	}

	// 生徒・有効期限のチェック
	if ($loginInfo['secessionday'] < 0) {
		return ERR_EXPIRED;
	}

	// 新コンテンツ向けの場合で、ワンタイム受講中の場合この時点でOK
	if ($is_service && $loginInfo['onetime_jyko_flg']) {
		return SUCCESS;
	}

	// 生徒状態のチェック
	if ($loginInfo['sito_jyti'] == C14_KYUUSHI) {
		return ERR_STOPPING;
	}
	return SUCCESS;
}

// アイコン無しログインのチェック（在宅利用）
function loginApiIconNo($loginInfo, $is_gtest = false, $is_service = false) {

	// 校舎有効期限のチェック
	if ($loginInfo['end_ymd'] < 0) {
		return ERR_EXPIRED;
	}

	// 在宅フラグのチェック
	if ($loginInfo['zitk_flg'] == C58_GAKKONOMI) {
		return ERR_NO_ATHOME;
	}

	// 受講停止フラグのチェック
	if ($loginInfo['jyko_stop_flg'] == C58_TEISHI) {
		return ERR_ATTENDANCE_STOP;
	}

	// 学力診断向けチェックならこの時点でOK
	if ($is_gtest) {
		return SUCCESS;
	}

	// 生徒・有効期限のチェック
	if ($loginInfo['secessionday'] < 0) {
		return ERR_EXPIRED;
	}

	// 新コンテンツ向けの場合で、ワンタイム受講中の場合この時点でOK
	if ($is_service && $loginInfo['onetime_jyko_flg']) {
		return SUCCESS;
	}

	// 生徒状態のチェック
	if ($loginInfo['sito_jyti'] == C14_KYUUSHI) {
		return ERR_STOPPING;
	}

	return SUCCESS;
}

function LoginApiGetLoginInfo($handle, $loginID, $iconFlg, &$loginInfo, $is_course = false) {

	if (!$is_course) {
		$sql = loginApiGetStudentInfoSql($loginID);
	} else {
		$sql = loginApiGetCourseInfoSql($loginID);
	}
//	$loginID = mysql_real_escape_string($loginID);
//	$sql = "select l.login_id, l.pass, TO_DAYS(s.secessionday) - TO_DAYS(now()) as secessionday, s.zitk_flg,".
//		  " s.jyko_stop_flg, s.knry_end_flg, sj.sito_jyti, sj.jyko_crs_cd, sl.kmi_zksi,".
//		  " TO_DAYS(sk.end_ymd) - TO_DAYS(now()) as end_ymd, sk.tb, sk.kiyk_hni, sk.kiyk_kb,".
//		  " d.db_host, d.db_port, d.db_user, d.db_pass, d.db_name".
//		  " from login l".
//		  " inner join db_connect dc on l.enterprise_id = dc.enterprise_id and dc.mk_flg = 0".
//		  " inner join db d on dc.db_id = d.db_id and d.mk_flg = 0".
//		  " inner join student s on l.login_id = s.student_id and s.mk_flg = 0".
//		  " inner join tb_stu_jyko_jyotai sj on l.login_id = sj.student_id and sj.mk_flg = 0".
//		  " inner join school sl on s.school_id = sl.school_id and sl.mk_flg = 0".
//		  " inner join ms_sch_kiyk sk on s.school_id = sk.school_id and sk.mk_flg = 0".
//		  " where l.login_id = '$loginID' and l.login_type = '1' and l.mk_flg = 0".  
//		  " order by sk.tb desc limit 1";  

	if (!($resource = mysql_query($sql, $handle))) {
		return ERR_OTHER;
	}

	$loginInfo = array();
	if (mysql_num_rows($resource) > 0) {
		$loginInfo =  mysql_fetch_assoc($resource);
	}

	mysql_free_result($resource);

	if (count($loginInfo) == 0) return ERR_WRONG_ID_PASSWD;
	$loginInfo['opt_kyks'] = null;

	if ($loginInfo['jyko_crs_cd'] == '') {
		$loginInfo['jyko_crs_cd'] = null;
	}

	$crsInfo = array();
	$srlyk_flg = true;

	// アイコン有りで、在宅フラグ指定ありでも、３教科の受講コースCDを渡す
	if ($iconFlg == ICON_EXIST) {
		// オプション教科は生徒受講中コースから渡す
		if (($loginInfo['zitk_flg'] == C58_ZAITAKU) && ($loginInfo['jyko_crs_cd'] != null)) {
			if ($loginInfo['sito_jyti'] == C14_ZYUKOU) {
				if (($crsInfo = loginApiGetSysicrs($handle, $loginID)) === false) {
					return ERR_OTHER;
				}
				$loginInfo['opt_kyks'] = $crsInfo['opt_kyks'];
			}
		}
		// すらら教科は３教科固定
		if ($loginInfo['zitk_flg'] == C58_GAKKONOMI || $loginInfo['zitk_flg'] == C58_ZAITAKU) {
			$loginInfo['jyko_crs_cd'] = null;
		}
	}

	if ($loginInfo['jyko_crs_cd'] != null) {
		if (($crsInfo = loginApiGetSysicrs($handle, $loginID)) === false) {
			return ERR_OTHER;
		}
		if ($crsInfo['surala_crs_cd'] != '') {
			$loginInfo['jyko_surala_cd'] = $crsInfo['surala_crs_cd'];
		} else {
			$loginInfo['jyko_surala_cd'] = null;

			// すらら未受講、各種サービス（毎月）受講時のすらら有効期限チェック
			if (($loginInfo['sito_jyti'] == C14_ZYUKOU) && ($loginInfo['srl_yk_ymd'] < 0)){
				$srlyk_flg = false;
			}
		}
		$loginInfo['opt_kyks'] = $crsInfo['opt_kyks'];
		$loginInfo['rsyu_crs'] = $crsInfo['rsyu_crs'];
	}

	if (($srlyk_flg) && ($loginInfo != null) && (!isset($loginInfo['jyko_surala_cd']) || ($loginInfo['jyko_surala_cd'] == null))) {
		$loginInfo['jyko_surala_cd'] = loginApiGetDefaultJykocrs($handle, $loginInfo['kiyk_hni']);
		$loginInfo['rsyu_crs'] = C45_A;
	}
	loginApiSetJykoCrs($loginInfo);

	if (!$is_course) {
		if (count($loginInfo) > 0) {
			$loginInfo['test_group_ids'] = loginApiGetTestGroups($handle, $loginID);
		}
	}
	return SUCCESS;
}

function loginApiGetStudentInfoSql($loginID) {
	$loginID = mysql_real_escape_string($loginID);
	$sql = "select l.login_id, l.pass, TO_DAYS(s.secessionday) - TO_DAYS(now()) as secessionday, s.zitk_flg,".
		  " s.jyko_stop_flg, s.knry_end_flg, sj.sito_jyti, sj.jyko_crs_cd, sl.kmi_zksi,".
  		  " ifnull(to_days(sj.srl_yk_ymd), 0) - to_days(now()) as srl_yk_ymd,".
		  " to_days(sk.end_ymd) - to_days(now()) as end_ymd, sk.tb, sk.kiyk_hni, sk.kiyk_kb,".
		  " d.db_host, d.db_port, d.db_user, d.db_pass, d.db_name".
		  " from login l".
		  " inner join db_connect dc on l.enterprise_id = dc.enterprise_id and dc.mk_flg = 0".
		  " inner join db d on dc.db_id = d.db_id and d.mk_flg = 0".
		  " inner join student s on l.login_id = s.student_id and s.mk_flg = 0".
		  " inner join tb_stu_jyko_jyotai sj on l.login_id = sj.student_id and sj.mk_flg = 0".
		  " inner join school sl on s.school_id = sl.school_id and sl.mk_flg = 0".
		  " inner join ms_sch_kiyk sk on s.school_id = sk.school_id and sk.mk_flg = 0".
		  " where l.login_id = '$loginID' and l.login_type = '1' and l.mk_flg = 0".  
		  " order by sk.tb desc limit 1";  
	return $sql;
}

function loginApiGetCourseInfoSql($loginID) {
	$loginID = mysql_real_escape_string($loginID);
	$sql = "select s.student_id login_id, TO_DAYS(s.secessionday) - TO_DAYS(now()) as secessionday, s.zitk_flg,".
		  " s.jyko_stop_flg, s.knry_end_flg, sj.sito_jyti, sj.jyko_crs_cd, sl.kmi_zksi,".
  		  " ifnull(to_days(sj.srl_yk_ymd), 0) - to_days(now()) as srl_yk_ymd,".
		  " to_days(sk.end_ymd) - to_days(now()) as end_ymd, sk.tb, sk.kiyk_hni, sk.kiyk_kb".
		  " from student s".
		  " inner join tb_stu_jyko_jyotai sj on s.student_id = sj.student_id and sj.mk_flg = 0".
		  " inner join school sl on s.school_id = sl.school_id and sl.mk_flg = 0".
		  " inner join ms_sch_kiyk sk on s.school_id = sk.school_id and sk.mk_flg = 0".
		  " where s.student_id = '$loginID' and s.mk_flg = 0".
		  " order by sk.tb desc limit 1";
	return $sql;
}

function LoginApiGetLoginInfoGtest($handle, $loginID, $iconFlg, &$loginInfo) {

	$sql = loginApiGetStudentInfoSql($loginID);
	if (!($resource = mysql_query($sql, $handle))) {
		return ERR_OTHER;
	}

	$loginInfo = array();
	if (mysql_num_rows($resource) > 0) {
		$loginInfo =  mysql_fetch_assoc($resource);
	}
	mysql_free_result($resource);

	if (count($loginInfo) > 0) {
		$loginInfo['test_group_ids'] = loginApiGetTestGroups($handle, $loginID);
	}

	return SUCCESS;
}


function loginApiGetLoginInfoTeacher($handle, $loginID, &$loginInfoTeacher) {

	$loginID = mysql_real_escape_string($loginID);
	$sql = "select l.login_id, l.pass, t.km_tool_use_flg, TO_DAYS(sk.end_ymd) - TO_DAYS(now()) as end_ymd, sk.tb, sk.kiyk_hni,".
		   " d.db_host, d.db_port, d.db_user, d.db_pass, d.db_name".
		   " from login l".
		   " inner join db_connect dc on l.enterprise_id = dc.enterprise_id and dc.mk_flg = 0".
		   " inner join db d on dc.db_id = d.db_id and d.mk_flg = 0".
		   " inner join teacher t on l.login_id = t.teacher_id and t.mk_flg = 0".
		   " inner join ms_sch_kiyk sk on t.school_id = sk.school_id and sk.mk_flg = 0".
		   " where l.login_id = '$loginID' and l.mk_flg = 0".
		   " order by sk.tb desc limit 1";  

	if (!($resource = mysql_query($sql, $handle))) {
		return ERR_OTHER;
	}

	$loginInfoTeacher = array();
	if (mysql_num_rows($resource) > 0) {
		$loginInfoTeacher =  mysql_fetch_assoc($resource);
	}
	mysql_free_result($resource);

	return SUCCESS;
}

function LoginApiGetLoginInfoGuardian($handle, $loginID, &$loginInfoGua) {

	$loginID = mysql_real_escape_string($loginID);
	$sql = "select l.login_id, l.pass, s.student_id, TO_DAYS(s.secessionday) - TO_DAYS(now()) as secessionday, s.zitk_flg,".
		  " s.jyko_stop_flg, s.knry_end_flg, sj.sito_jyti, sj.jyko_crs_cd, sl.kmi_zksi,".
		  " TO_DAYS(sk.end_ymd) - TO_DAYS(now()) as end_ymd, sk.tb, sk.kiyk_hni, sk.kiyk_kb,".
		  " d.db_host, d.db_port, d.db_user, d.db_pass, d.db_name".
		  " from login l".
		  " inner join db_connect dc on l.enterprise_id = dc.enterprise_id and dc.mk_flg = 0".
		  " inner join db d on dc.db_id = d.db_id and d.mk_flg = 0".
		  " inner join guardian g on l.login_id = g.guardian_id and g.mk_flg = 0".
		  " inner join student s on l.login_id = s.guardian_id and s.mk_flg = 0".
		  " inner join tb_stu_jyko_jyotai sj on s.student_id = sj.student_id and sj.mk_flg = 0".
		  " inner join school sl on s.school_id = sl.school_id and sl.mk_flg = 0".
		  " inner join ms_sch_kiyk sk on s.school_id = sk.school_id and sk.mk_flg = 0".
		  " where l.login_id = '$loginID' and l.login_type = '2' and l.mk_flg = 0".  
		  " order by sk.tb desc limit 1";  

	if (!($resource = mysql_query($sql, $handle))) {
		return ERR_OTHER;
	}

	$loginInfoGua = array();
	if (mysql_num_rows($resource) > 0) {
		$loginInfoGua =  mysql_fetch_assoc($resource);
	}

	mysql_free_result($resource);
	if (count($loginInfoGua) == 0) return ERR_WRONG_ID_PASSWD;

	if ($loginInfoGua['jyko_crs_cd'] == '') {
		$loginInfoGua['jyko_crs_cd'] = null;
	}
	$loginInfoGua['rsyu_crs'] = null;

	if ($loginInfoGua['jyko_crs_cd'] != null) {
		$crsInfo = loginApiGetSysicrs($handle, $loginInfoGua['student_id']);
		if ($crsInfo['surala_crs_cd'] != '') {
			$loginInfoGua['jyko_surala_cd'] = $crsInfo['surala_crs_cd'];
		} else {
			$loginInfoGua['jyko_surala_cd'] = null;
		}
		$loginInfoGua['opt_kyks'] = $crsInfo['opt_kyks'];
		$loginInfoGua['rsyu_crs'] = $crsInfo['rsyu_crs'];
	}

	if ($loginInfoGua != null && (!isset($loginInfoGua['jyko_surala_cd']) || ($loginInfoGua['jyko_surala_cd'] == null))) {
		$loginInfoGua['jyko_crs_cd'] = loginApiGetDefaultJykocrs($handle, $loginInfoGua['kiyk_hni']);
		if ($loginInfoGua['jyko_crs_cd'] == null) {
			return ERR_OTHER;
		}
	}

	loginApiSetJykoCrs($loginInfoGua);

	return SUCCESS;
}

function LoginApiGetLoginInfoService($handle, $loginID, $iconFlg, &$loginInfo) {

	$sql = loginApiGetStudentInfoSql($loginID);
	if (!($resource = mysql_query($sql, $handle))) {
		return ERR_OTHER;
	}

	$loginInfo = array();
	if (mysql_num_rows($resource) > 0) {
		$loginInfo =  mysql_fetch_assoc($resource);
	}
	mysql_free_result($resource);

	if (count($loginInfo) > 0) {

		$sql = loginApiGetServiseInfoSql($loginID);
		if (!($resource = mysql_query($sql, $handle))) {
			return false;
		}

		$result = null;
		if (mysql_num_rows($resource) > 0) {
			while ($row = mysql_fetch_assoc($resource)) {
				$result[] = $row;
			}
		}

		mysql_free_result($resource);
		$loginInfo['opt_kyks'] = null;
		$loginInfo['onetime_jyko_flg'] = null;
		$optInfo = array();
		$onetime_jyko_flg = false;
		if ($result != null) {
			foreach($result as $values) {
				if ($values['srvc_cd'] != C81_SRL) {
					$optInfo[] = $values['sysi_crs_cd'];
					if ($values['jyko_crs_kb'] == C48_ONCE) {
						$onetime_jyko_flg = true;
					}
				}
			}
		}
		if (count($optInfo) > 0) {
			$loginInfo['opt_kyks'] = implode(',', $optInfo);
			$loginInfo['onetime_jyko_flg'] = $onetime_jyko_flg;
		}
	}

	return SUCCESS;
}

function loginApiGetDefaultJykocrs($handle, $riyo_hni) {

	$riyo_hni = mysql_real_escape_string($riyo_hni);
	$default_cd = null;

//	$sql = "select distinct sysi_crs_cd ";
//	$sql .= " from ms_jyko_crs_ucwk s ";
//	$sql .= " join ms_jyko_crs j on s.jyko_crs_cd=j.jyko_crs_cd and j.mk_flg='0'";
//	$sql .= " where s.mk_flg='0' and kmk_su='3' and riyo_hni='$riyo_hni' and rsyu_crs is null and s.sysi_crs_cd=s.jyko_crs_cd";

	//$sql = "select jyko_crs_cd from ms_jyko_crs where kmk_su='3' and substring(jyko_crs_cd, 3,".$len.")='$tmpcrs' and mk_flg=0";

	$sql = "select s.sysi_crs_cd, count(*) crs_su";
	$sql .= " from ms_sysi_crs s";
	$sql .= " join ms_sysi_crs_kmk ck on s.sysi_crs_cd = ck.sysi_crs_cd";
	$sql .= " join ms_kmk k on ck.kmk_cd = k.kmk_cd and k.mk_flg=0 and k.riyo_hni='$riyo_hni'";
	$sql .= " where rsyu_crs is null and s.mk_flg=0";
	$sql .= " group by s.sysi_crs_cd";
	$sql .= " order by crs_su desc limit 1";

	if (!($resource = mysql_query($sql, $handle))) {
		return $default_cd;
	}
	if (mysql_num_rows($resource) > 0) {
		$ms_jyko_crs =  mysql_fetch_assoc($resource);

		$default_cd = $ms_jyko_crs['sysi_crs_cd'];
	}
	mysql_free_result($resource);

	return $default_cd;
}

function loginApiDbLogin(&$handle) {

   	$ini_file = get_cfg_var('SURALA_ENTRY_INI');    
  	if (!is_file($ini_file) ) {
		return ERR_OTHER;
   	}

	$arr = parse_ini_file($ini_file, true);

	$server = $arr['db_host'];
	if (isset($arr['db_port'])) $server .= ':'.$arr['db_port'];
	$db_name = $arr['db_name'];
	$db_user = $arr['db_user'];
	$db_pass = $arr['db_pass'];
	$db_name = $arr['db_name'];

	if (!($handle = mysql_connect($server, $db_user, $db_pass))) {
		return ERR_OTHER;
	}

	if (mysql_select_db($db_name, $handle) === FALSE) {
		return ERR_OTHER;
	}

	return SUCCESS;
}

function loginApiSetJykoCrs(&$jykocrsInfo) {
	if ($jykocrsInfo['rsyu_crs'] == null) {
		$jykocrsInfo['rsyu_crs'] = C45_A;
	}
	if ($jykocrsInfo['kiyk_kb'] == null) {
		$jykocrsInfo['rsyu_crs'] = C45_A;
	}

//	$jykocrsInfo['jyko_surala_cd'] = $jyko_surala_cd;
//	$jykocrsInfo['rsyu_crs'] = $rsyu_crs;
}

function loginApiGetSysicrs($handle, $student_id) {
	$loginID = mysql_real_escape_string($student_id);
//	$jyko_crs_cd = mysql_real_escape_string($jyko_crs_cd);

//	$sql = "select s.sysi_crs_cd, s.opt_kyk_flg, u.jyko_kyk, u.kmk_su, c.riyo_hni, c.rsyu_crs";
//	$sql .= " from tb_stu_jyko_syosai s";
//	$sql .= " join ms_jyko_crs_ucwk u on u.mk_flg=0 and u.jyko_crs_cd='$jyko_crs_cd' and s.sysi_crs_cd=u.sysi_crs_cd";
//	$sql .= " join ms_jyko_crs c on c.mk_flg=0 and u.jyko_crs_cd=c.jyko_crs_cd and u.sysi_crs_cd=s.sysi_crs_cd";
//	$sql .= " where s.mk_flg='0' and s.student_id='$student_id'";

	$sql = loginApiGetServiseInfoSql($loginID);
	if (!($resource = mysql_query($sql, $handle))) {
		return false;
	}

	$result = null;
	if (mysql_num_rows($resource) > 0) {
		while ($row = mysql_fetch_assoc($resource)) {
			$result[] = $row;
		}
	}
	mysql_free_result($resource);

	$crsInfo = array();
	$crsInfo['surala_crs_cd'] = null;
	$crsInfo['opt_kyks'] = null;
	$crsInfo['rsyu_crs'] = null;
	$optInfo = array();
	$kmkInfo = array();
	if (is_array($result)) {	//	add ookawara 2014/03/03
		foreach($result as $values) {
			if ($values['srvc_cd'] == C81_SRL) {
				$crsInfo['rsyu_crs'] = $values['rsyu_crs'];
				if (($kmkInfo = loginApiGetKmkInfo($handle, $values['sysi_crs_cd'])) === false) {
					return false;
	   			}
				if ($kmkInfo['crs_su'] == '3') {
					$crsInfo['surala_crs_cd'] = '30'.$kmkInfo['riyo_hni'];
				} else {
					$crsInfo['surala_crs_cd'] = $kmkInfo['crs_su'].$kmkInfo['jyko_kyk'].$kmkInfo['riyo_hni'];
				}
			} else {
				$optInfo[] = $values['sysi_crs_cd'];
			}

		}
	}	//	add ookawara 2014/03/03

	if (count($optInfo) > 0) {
		$crsInfo['opt_kyks'] = implode(',', $optInfo);
	}

	return $crsInfo;
}

function loginApiGetTestGroups($handle, $student_id) {
	$student_id = mysql_real_escape_string($student_id);

	$sql = "select t.test_group_id, t.test_group_name, student_id, test_gknn".
			" from ms_test_group t".
			" left join tb_stu_gtest_mskm m".
			" on t.test_group_id=m.test_group_id and m.mk_flg=0 and m.student_id='$student_id'".
			" where t.mk_flg=0".
			" and ((m.student_id is not null) or (test_gknn like '%R%'".
			" and if (isnull(to_days(kkn_from)), 1, to_days(now()) - to_days(kkn_from)) >= 0".
			" and if (isnull(to_days(kkn_to)), 1, to_days(kkn_to) - to_days(now())) > 0))";

	if (!($resource = mysql_query($sql, $handle))) {
		return false;
	}

	$test_grp_ids = array();
	$result = array();
	if (mysql_num_rows($resource) > 0) {
		while ($row = mysql_fetch_assoc($resource)) {
			$result[] = $row;
		}
	}
	mysql_free_result($resource);
	if (count($result) == 0) return $test_grp_ids;

	// 学力診断受験申込済の生徒にのみ受験可能なコースを返す
	$gtest_jyko = false;
	foreach ($result as $key => $value) {
		$test_grp_ids[] = $value['test_group_id'];
		if ($value['student_id'] == $student_id ) {
			$gtest_jyko = true;
		}
	}

	if ($gtest_jyko) {
		return $test_grp_ids;
	}
	return array();
}

function loginApiGetServiseInfoSql($loginID) {
	$loginID = mysql_real_escape_string($loginID);

	$sql = "select mc.sysi_crs_cd, mc.jyko_crs_kb, mc.srvc_cd, s.rsyu_crs";
	$sql .= " from tb_stu_mskm_crs mc";
	$sql .= " join ms_sysi_crs s on mc.sysi_crs_cd=s.sysi_crs_cd and s.mk_flg=0";
	$sql .= " and ((s.jyko_crs_kb = '1')";
	$sql .= " or (s.jyko_crs_kb = '2' and ((to_days(now()) - to_days(s.kkn_from)) >= 0) and ((to_days(s.kkn_to) - to_days(now())) > 0)))";
	$sql .= " where to_days(mc.jyko_start_ymd) <= to_days(now()) and mc.mk_flg='0' and mc.srvc_cd != 'GTEST'";
	$sql .= " and mc.student_id='$loginID'";

	return $sql;
}

function loginApiGetKmkInfo($handle, $sysi_crs_cd) {
	$sysi_crs_cd = mysql_real_escape_string($sysi_crs_cd);

	$sql = "select ck.sysi_crs_cd, k.kmk_cd, k.riyo_hni, k.jyko_kyk, count(*) crs_su";
	$sql .= " from ms_sysi_crs_kmk ck";
	$sql .= " join ms_kmk k on ck.kmk_cd = k.kmk_cd and k.mk_flg=0";
	$sql .= " where ck.sysi_crs_cd='$sysi_crs_cd'";
	$sql .= " group by ck.sysi_crs_cd";

	if (!($resource = mysql_query($sql, $handle))) {
		return false;
	}
	$result = null;
	if (mysql_num_rows($resource) > 0) {
		$result =  mysql_fetch_assoc($resource);
	}
	mysql_free_result($resource);

	return $result;

}

?>
