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

	// 統合DB接続
	$handle = null;
	if (($ret = loginApiDbLogin($handle)) < 0 ) {
		return $ret;
	}

	// ログインユーザ情報の取得
	$ret = LoginApiGetLoginInfo($handle, $loginID, $iconFlg, $loginInfo);

	mysqli_close($handle);

	if ($ret < 0) {
		return $ret;
	}

	// ログインID , パスワードの正当性チェック
	if ($loginInfo == null) {
		return ERR_WRONG_ID_PASSWD;
	}
	if (!isset($loginInfo['pass']) || ($loginInfo['pass'] === '') || ($passWord === '') ) {
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

	$userInfo = array(
		'DBHOST' => $loginInfo['db_host'],
		'DBNAME' => $loginInfo['db_name'],
		'DBUSER' => $loginInfo['db_user'],
		'DBPASSWD' => $loginInfo['db_pass'],
		'DBPORT' => $loginInfo['db_port'],
		'COURSE' => null,
		'OPTION' => null,
		'KIYKHNI' => null,
		'RSYCRS' => null,
		'TEST_GROUP_IDS' => null
	);
	if ($ret == SUCCESS) {
		$userInfo['COURSE'] = $loginInfo['jyko_surala_cd'];
		$userInfo['OPTION'] = $loginInfo['opt_kyks'];
		$userInfo['KIYKHNI'] = $loginInfo['kiyk_hni'];
		$userInfo['RSYCRS'] = $loginInfo['rsyu_crs'];
		$userInfo['TEST_GROUP_IDS'] = null;
		if (count($loginInfo['test_group_ids']) > 0) {
			$userInfo['TEST_GROUP_IDS'] = implode(',', $loginInfo['test_group_ids']);
		}
	}

	return $ret;
}

function getTeacherInfo($loginID, $passWord, $loginType, &$teacherInfo) {

	$teacherInfo = array();

	// 統合DB接続
	$handle = null;
	if (($ret = loginApiDbLogin($handle)) < 0 ) {
		return $ret;
	}

	// ログインユーザ情報(先生)の取得
	$ret = loginApiGetLoginInfoTeacher($handle, $loginID, $loginInfoTeacher);

	mysqli_close($handle);

	if ($ret < 0) {
		return $ret;
	}

	// ログインID , パスワードの正当性チェック
	if ($loginInfoTeacher == null) {
		return ERR_WRONG_ID_PASSWD;
	}
	if (!isset($loginInfoTeacher['pass']) || ($loginInfoTeacher['pass'] === '') || ($loginInfoTeacher === '') ) {
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

	$teacherInfo = array(
		'DBHOST' => $loginInfoTeacher['db_host'],
		'DBNAME' => $loginInfoTeacher['db_name'],
		'DBUSER' => $loginInfoTeacher['db_user'],
		'DBPASSWD' => $loginInfoTeacher['db_pass'],
		'DBPORT' => $loginInfoTeacher['db_port'],
		'KIYKHNI' => null
	);
	if ($ret == SUCCESS) {
		$teacherInfo['KIYKHNI'] = $loginInfoTeacher['kiyk_hni'];
	}

	return SUCCESS;
}

function getGuardianInfo($loginID, $passWord, &$guardianInfo) {

	$guardianInfo = array();

	// 統合DB接続
	$handle = null;
	if (($ret = loginApiDbLogin($handle)) < 0 ) {
		return $ret;
	}

	// ログインユーザ情報の取得
	$ret = LoginApiGetLoginInfoGuardian($handle, $loginID, $loginInfoGua);

	mysqli_close($handle);

	if ($ret < 0) {
		return $ret;
	}

	// ログインID , パスワードの正当性チェック
	if ($loginInfoGua == null) {
		return ERR_WRONG_ID_PASSWD;
	}
	if (!isset($loginInfoGua['pass']) || ($loginInfoGua['pass'] === '') || ($loginInfoGua === '') ) {
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

	$guardianInfo = array(
		'DBHOST' => $loginInfoGua['db_host'],
		'DBNAME' => $loginInfoGua['db_name'],
		'DBUSER' => $loginInfoGua['db_user'],
		'DBPASSWD' => $loginInfoGua['db_pass'],
		'DBPORT' => $loginInfoGua['db_port'],
		'COURSE' => null,
		'OPTION' => null,
		'KIYKHNI' => null,
		'RSYCRS' => null
	);

	if ($ret == SUCCESS) {
		$guardianInfo['COURSE'] = $loginInfoGua['jyko_surala_cd'];
		$guardianInfo['OPTION'] = $loginInfoGua['opt_kyks'];
		$guardianInfo['KIYKHNI'] = $loginInfoGua['kiyk_hni'];
		$guardianInfo['RSYCRS'] = $loginInfoGua['rsyu_crs'];
	}

	return $ret;
}

function getUserGtestInfo($loginID, $passWord, $iconFlg, &$userInfo) {

	$userInfo = array();

	// 統合DB接続
	$handle = null;
	if (($ret = loginApiDbLogin($handle)) < 0 ) {
		return $ret;
	}

	// ログインユーザ情報の取得
	$ret = LoginApiGetLoginInfoGtest($handle, $loginID, $iconFlg, $loginInfo);
	
	mysqli_close($handle);

	if ($ret < 0) {
		return $ret;
	}

	// ログインID , パスワードの正当性チェック
	if (count($loginInfo) == 0) {
		return ERR_WRONG_ID_PASSWD;
	}
	if (!isset($loginInfo['pass']) || ($loginInfo['pass'] === '') || ($passWord === '') ) {
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
	$userInfo = array(
		'DBHOST' => $loginInfo['db_host'],
		'DBNAME' => $loginInfo['db_name'],
		'DBUSER' => $loginInfo['db_user'],
		'DBPASSWD' => $loginInfo['db_pass'],
		'DBPORT' => $loginInfo['db_port'],
		'TEST_GROUP_IDS' => null
	);

	if ($ret == SUCCESS) {
		if (count($loginInfo['test_group_ids']) > 0) {
			$userInfo['TEST_GROUP_IDS'] = implode(',', $loginInfo['test_group_ids']);
		}
	}

	return $ret;
}

function getUserCourseInfo($loginID, $iconFlg, &$userInfo) {

	$userInfo = array();

	// 統合DB接続
	$handle = null;
	if (($ret = loginApiDbLogin($handle)) < 0 ) {
		return $ret;
	}

	// 生徒のすらら受講コース情報取得
	$ret = LoginApiGetLoginInfo($handle, $loginID, $iconFlg, $loginInfo, true);

	mysqli_close($handle);

	if ($ret < 0) {
		return $ret;
	}

	// ログインIDの正当性チェックのみ
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

	$userInfo = array(
		'COURSE' => null,
		'OPTION' => null,
		'KIYKHNI' => null,
		'RSYCRS' => null,
	);

	if ($ret == SUCCESS) {
		$userInfo['COURSE'] = $loginInfo['jyko_surala_cd'];
		$userInfo['OPTION'] = $loginInfo['opt_kyks'];
		$userInfo['KIYKHNI'] = $loginInfo['kiyk_hni'];
		$userInfo['RSYCRS'] = $loginInfo['rsyu_crs'];
	}

	return $ret;
}

function getUserServiceInfo($loginID, $passWord, $iconFlg, &$userInfo) {

	$userInfo = array();

	// 統合DB接続
	$handle = null;
	if (($ret = loginApiDbLogin($handle)) < 0 ) {
		return $ret;
	}

	// ログインユーザ情報の取得
	$ret = LoginApiGetLoginInfoService($handle, $loginID, $iconFlg, $loginInfo);

	mysqli_close($handle);

	if ($ret < 0) {
		return $ret;
	}

	// ログインID , パスワードの正当性チェック
	if ($loginInfo == null) {
		return ERR_WRONG_ID_PASSWD;
	}
	if (!isset($loginInfo['pass']) || ($loginInfo['pass'] === '') || ($passWord === '') ) {
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

	$userInfo = array(
		'DBHOST' => $loginInfo['db_host'],
		'DBNAME' => $loginInfo['db_name'],
		'DBUSER' => $loginInfo['db_user'],
		'DBPASSWD' => $loginInfo['db_pass'],
		'DBPORT' => $loginInfo['db_port'],
		'OPTION' => null,
	);

	if ($ret == SUCCESS) {
		$userInfo['OPTION'] = $loginInfo['opt_kyks'];
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

	$loginInfo = array();

	if ($is_course) {
		$sql = loginApiGetCourseInfoSql($handle, $loginID);
	} else {
		$sql = loginApiGetStudentInfoSql($handle, $loginID);
	}

	if (!($resource = mysqli_query($handle, $sql))) {
		return ERR_OTHER;
	}

	$loginInfo = array();
	if (mysqli_num_rows($resource) > 0) {
		$loginInfo =  mysqli_fetch_assoc($resource);
	}

	mysqli_free_result($resource);

	if (!is_array($loginInfo) || (count($loginInfo) == 0)) return ERR_WRONG_ID_PASSWD;
	$loginInfo['opt_kyks'] = null;
	$loginInfo['jyko_surala_cd'] = null;
	$loginInfo['rsyu_crs'] = null;

	if ($loginInfo['jyko_crs_cd'] == '') {
		$loginInfo['jyko_crs_cd'] = null;
	}

	// 生徒の詳細コース情報を取得
	$crsInfo = array();
	if (($crsInfo = loginApiGetSysicrs($handle, $loginID)) === false) {
		return ERR_OTHER;
	}

	$free_surala_flg = false;
	if (isset($crsInfo['surala_crs_cd']) && ($crsInfo['surala_crs_cd'] != '')) {
		$loginInfo['jyko_surala_cd'] = $crsInfo['surala_crs_cd'];
		if ($crsInfo['rsyu_crs'] != null) $loginInfo['rsyu_crs'] = $crsInfo['rsyu_crs'];
	} else {
		if ($loginInfo['sito_jyti'] == C14_ZYUKOU) {
			// 生徒状態=受講 & すらら未申込 & すらら有効期限内はすらら受講可
			if ($loginInfo['srl_yk_ymd'] >= 0) {
				$free_surala_flg = true;
			}
		} else if ($loginInfo['secessionday'] >= 0) {
			// 生徒有効期限内
			$free_surala_flg = true;
		}
	}

	// アイコン有り&在宅フラグ指定あり
	if ($iconFlg == ICON_EXIST) {
		if ($loginInfo['zitk_flg'] == C58_GAKKONOMI || $loginInfo['zitk_flg'] == C58_ZAITAKU) {
			$free_surala_flg = true;
		}
	}

	// MAXのすらら詳細コースをセット
	if ($free_surala_flg) {
		$loginInfo['jyko_surala_cd'] = loginApiGetDefaultJykocrs($handle, $loginInfo['kiyk_hni']);
		$loginInfo['rsyu_crs'] = C45_A;
	}
	loginApiSetJykoCrs($loginInfo);

//	if (isset($loginInfo['jyko_surala_cd']) && isset($crsInfo['opt_kyks']) && ($loginInfo['zitk_flg'] != C58_GAKKONOMI)) {
	if (isset($crsInfo['opt_kyks']) && ($loginInfo['zitk_flg'] != C58_GAKKONOMI)) {
		if ($iconFlg == ICON_EXIST) {
			// アイコンあり：休止以外、または生徒有効期間切れでなければオプションを渡す
			if (($loginInfo['sito_jyti'] != C14_KYUUSHI) && ($loginInfo['secessionday'] >= 0)) {
				$loginInfo['opt_kyks'] = $crsInfo['opt_kyks'];
			}
		} else {
			$loginInfo['opt_kyks'] = $crsInfo['opt_kyks'];
		}
	}

	if (!$is_course) {
		if (count($loginInfo) > 0) {
			$loginInfo['test_group_ids'] = loginApiGetTestGroups($handle, $loginID);
		}
	}
	return SUCCESS;
}

function loginApiGetStudentInfoSql($handle, $loginID) {
	$loginID = mysqli_real_escape_string($handle, $loginID);
	$passSql = decryptSql($handle, $loginID);

	$sql = "select l.login_id, ". $passSql . ", TO_DAYS(s.secessionday) - TO_DAYS(now()) as secessionday, s.zitk_flg,".
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

function loginApiGetCourseInfoSql($handle, $loginID) {
	$loginID = mysqli_real_escape_string($handle, $loginID);
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

	$loginInfo = array();

	$sql = loginApiGetStudentInfoSql($handle, $loginID);
	if (!($resource = mysqli_query($handle, $sql))) {
		return ERR_OTHER;
	}

	$loginInfo = array();
	if (mysqli_num_rows($resource) > 0) {
		$loginInfo =  mysqli_fetch_assoc($resource);
	}
	mysqli_free_result($resource);

	if (count($loginInfo) > 0) {
		$loginInfo['test_group_ids'] = loginApiGetTestGroups($handle, $loginID);
	}

	return SUCCESS;
}


function loginApiGetLoginInfoTeacher($handle, $loginID, &$loginInfoTeacher) {

	$loginInfoTeacher = array();

	$loginID = mysqli_real_escape_string($handle, $loginID);
	$passSql = decryptSql($handle, $loginID);
	$sql = "select l.login_id,". $passSql .", t.km_tool_use_flg, TO_DAYS(sk.end_ymd) - TO_DAYS(now()) as end_ymd, sk.tb, sk.kiyk_hni,".
		   " d.db_host, d.db_port, d.db_user, d.db_pass, d.db_name".
		   " from login l".
		   " inner join db_connect dc on l.enterprise_id = dc.enterprise_id and dc.mk_flg = 0".
		   " inner join db d on dc.db_id = d.db_id and d.mk_flg = 0".
		   " inner join teacher t on l.login_id = t.teacher_id and t.mk_flg = 0".
		   " inner join ms_sch_kiyk sk on t.school_id = sk.school_id and sk.mk_flg = 0".
		   " where l.login_id = '$loginID' and l.mk_flg = 0".
		   " order by sk.tb desc limit 1";  

	if (!($resource = mysqli_query($handle, $sql))) {
		return ERR_OTHER;
	}

	$loginInfoTeacher = array();
	if (mysqli_num_rows($resource) > 0) {
		$loginInfoTeacher =  mysqli_fetch_assoc($resource);
	}
	mysqli_free_result($resource);

	return SUCCESS;
}

function LoginApiGetLoginInfoGuardian($handle, $loginID, &$loginInfoGua) {

	$loginInfoGua = array();

	$loginID = mysqli_real_escape_string($handle, $loginID);
	$passSql = decryptSql($handle, $loginID);
	$sql = "select l.login_id,". $passSql .", s.student_id, TO_DAYS(s.secessionday) - TO_DAYS(now()) as secessionday, s.zitk_flg,".
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

	if (!($resource = mysqli_query($handle, $sql))) {
		return ERR_OTHER;
	}

	$loginInfoGua = array();
	if (mysqli_num_rows($resource) > 0) {
		$loginInfoGua =  mysqli_fetch_assoc($resource);
	}

	mysqli_free_result($resource);
	if (!is_array($loginInfoGua) || (count($loginInfoGua) == 0)) return ERR_WRONG_ID_PASSWD;

	if ($loginInfoGua['jyko_crs_cd'] == '') {
		$loginInfoGua['jyko_crs_cd'] = null;
	}
	$loginInfoGua['opt_kyks'] = null;
	$loginInfoGua['jyko_surala_cd'] = null;
	$loginInfoGua['rsyu_crs'] = null;

	$crsInfo = array();
	if (($crsInfo = loginApiGetSysicrs($handle, $loginInfoGua['student_id'])) === false) {
		return ERR_OTHER;
	}

	if (isset($crsInfo['surala_crs_cd']) && ($crsInfo['surala_crs_cd'] != '')) {
		$loginInfoGua['jyko_surala_cd'] = $crsInfo['surala_crs_cd'];
		if ($crsInfo['rsyu_crs'] != null) $loginInfoGua['rsyu_crs'] = $crsInfo['rsyu_crs'];
	}
	loginApiSetJykoCrs($loginInfoGua);

	if (isset($crsInfo['opt_kyks']) && ($crsInfo['opt_kyks'] != '')) {
		$loginInfoGua['opt_kyks'] = $crsInfo['opt_kyks'];
	}

	return SUCCESS;
}

function LoginApiGetLoginInfoService($handle, $loginID, $iconFlg, &$loginInfo) {

	$loginInfo = array();

	$sql = loginApiGetStudentInfoSql($handle, $loginID);
	if (!($resource = mysqli_query($handle, $sql))) {
		return ERR_OTHER;
	}

	$loginInfo = array();
	if (mysqli_num_rows($resource) > 0) {
		$loginInfo =  mysqli_fetch_assoc($resource);
	}
	mysqli_free_result($resource);

	if (count($loginInfo) > 0) {

		$sql = loginApiGetServiseInfoSql($handle, $loginID);
		if (!($resource = mysqli_query($handle, $sql))) {
			return false;
		}

		$result = null;
		if (mysqli_num_rows($resource) > 0) {
			while ($row = mysqli_fetch_assoc($resource)) {
				$result[] = $row;
			}
		}

		mysqli_free_result($resource);
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

	$riyo_hni = mysqli_real_escape_string($handle, $riyo_hni);
	$default_cd = null;

	$sql = "select s.sysi_crs_cd, count(*) crs_su";
	$sql .= " from ms_sysi_crs s";
	$sql .= " join ms_sysi_crs_kmk ck on s.sysi_crs_cd = ck.sysi_crs_cd";
	$sql .= " join ms_kmk k on ck.kmk_cd = k.kmk_cd and k.mk_flg='0'";
	$sql .= " where rsyu_crs is null and s.mk_flg=0 and s.riyo_hni='$riyo_hni'";
	$sql .= " group by s.sysi_crs_cd";
	$sql .= " order by crs_su desc limit 1";

	if (!($resource = mysqli_query($handle, $sql))) {
		return $default_cd;
	}
	if (mysqli_num_rows($resource) > 0) {
		$ms_jyko_crs =  mysqli_fetch_assoc($resource);

		$default_cd = $ms_jyko_crs['sysi_crs_cd'];
	}
	mysqli_free_result($resource);

	return $default_cd;
}

function loginApiDbLogin(&$handle) {

	$ini_file = get_cfg_var('SURALA_ENTRY_INI');
	if (!is_file($ini_file) ) {
		return ERR_OTHER;
	}

	$arrini = parse_ini_file($ini_file, true);
	$arr = array();
	foreach($arrini as $key => $values) {
		if (is_array($values)) {
			if (count($values) > 0) {
				foreach($values as $key2 => $value2) {
					$arr[trim($key2)] = trim($value2);
				}
			}
		} else {
			$arr[trim($key)] = trim($values);
		}
	}

	$server  = (isset($arr['db_host'])) ? $arr['db_host'] : '';
	$db_name = (isset($arr['db_name'])) ? $arr['db_name'] : '';
	$db_user = (isset($arr['db_user'])) ? $arr['db_user'] : '';
	$db_pass = (isset($arr['db_pass'])) ? $arr['db_pass'] : '';
	$db_port = (isset($arr['db_port'])) ? $arr['db_port'] : '';

	if (($handle = mysqli_connect($server, $db_user, $db_pass, $db_name, $db_port)) === false) {
		return ERR_OTHER;
	}
	if ($handle == null) return ERR_OTHER;

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
	$loginID = mysqli_real_escape_string($handle, $student_id);

	$sql = loginApiGetServiseInfoSql($handle, $loginID);
	if (!($resource = mysqli_query($handle, $sql))) {
		return false;
	}

	$result = null;
	if (mysqli_num_rows($resource) > 0) {
		while ($row = mysqli_fetch_assoc($resource)) {
			$result[] = $row;
		}
	}
	mysqli_free_result($resource);

	$crsInfo = array();
	$crsInfo['surala_crs_cd'] = null;
	$crsInfo['opt_kyks'] = null;
	$crsInfo['rsyu_crs'] = null;
	$optInfo = array();
	$kmkInfo = array();
	if (is_array($result) && (count($result) > 0)) {
		foreach($result as $values) {
			if ($values['srvc_cd'] == C81_SRL) {
				$crsInfo['surala_crs_cd'] = null;
				$crsInfo['rsyu_crs'] = null;

				if ($values['rsyu_crs'] === '') {
					$crsInfo['surala_crs_cd'] = $values['sysi_crs_cd'];
				} else {
					$crsInfo['rsyu_crs'] = $values['rsyu_crs'];
					if (($kmkInfo = loginApiGetKmkInfo($handle, $values['sysi_crs_cd'])) === false) {
						return false;
		   			}
		   			if (is_array($kmkInfo) && (count($kmkInfo) > 0)) {
						if ($kmkInfo['crs_su'] == '3') {
							$crsInfo['surala_crs_cd'] = '30'.$kmkInfo['riyo_hni'];
						} else {
							$crsInfo['surala_crs_cd'] = $kmkInfo['crs_su'].$kmkInfo['jyko_kyk'].$kmkInfo['riyo_hni'];
						}
					}
				}

			} else {
				$optInfo[] = $values['sysi_crs_cd'];
			}
		}
	}
	if (count($optInfo) > 0) {
		$crsInfo['opt_kyks'] = implode(',', $optInfo);
	}
	return $crsInfo;
}

function loginApiGetTestGroups($handle, $student_id) {
	$student_id = mysqli_real_escape_string($handle, $student_id);
	
	// 申込済(受験可能または受験済)テストグループID取得
	$sql = "select t.test_group_id".
		" from tb_stu_gtest_mskm m ".
		" inner join  ms_test_group t ".
		" on m.test_group_id=t.test_group_id and t.mk_flg=0 ".
		" where m.mk_flg=0 and m.student_id='$student_id'";

	if (!($resource = mysqli_query($handle, $sql))) {
		return false;
	}

	$result = array();
	if (mysqli_num_rows($resource) > 0) {
		while ($row = mysqli_fetch_assoc($resource)) {
			$result[] = $row;
		}
	}
	mysqli_free_result($resource);
	if (count($result) == 0) return array();
	
	// 練習コース取得(受験可のみ)
	$sql = "select test_group_id".
		" from ms_test_group ".
		" where test_gknn like '%R%'".
		" and if (isnull(to_days(kkn_from)), 1, to_days(now()) - to_days(kkn_from)) >= 0".
		" and if (isnull(to_days(kkn_to)), 1, to_days(kkn_to) - to_days(now())) > 0 ".
		" and mk_flg=0";
		
	if (!($resource = mysqli_query($handle, $sql))) {
		return false;
	}

	$r_result = array();
	if (mysqli_num_rows($resource) > 0) {
		while ($row = mysqli_fetch_assoc($resource)) {
		$r_result[] = $row;
		}
	}
	mysqli_free_result($resource);
	
	$test_grp_ids = array();
	if (count($r_result) > 0) {
		$result = array_merge($result, $r_result);
	}
	foreach ($result as $key => $value) {
		$test_grp_ids[] = $value['test_group_id'];
	}
	sort($test_grp_ids);

	return $test_grp_ids;
}

function loginApiGetServiseInfoSql($handle, $loginID) {
	$loginID = mysqli_real_escape_string($handle, $loginID);

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
	$sysi_crs_cd = mysqli_real_escape_string($handle, $sysi_crs_cd);

	$sql = "select ck.sysi_crs_cd, k.kmk_cd, s.riyo_hni, k.jyko_kyk, count(*) crs_su";
	$sql .= " from ms_sysi_crs_kmk ck";
	$sql .= " join ms_kmk k on ck.kmk_cd = k.kmk_cd and k.mk_flg=0";
	$sql .= " join ms_sysi_crs s on ck.sysi_crs_cd=s.sysi_crs_cd and s.mk_flg=0";
	$sql .= " where ck.sysi_crs_cd='$sysi_crs_cd'";
	$sql .= " group by ck.sysi_crs_cd";
	if (!($resource = mysqli_query($handle, $sql))) {
		return false;
	}
	$result = null;
	if (mysqli_num_rows($resource) > 0) {
		$result =  mysqli_fetch_assoc($resource);
	}
	mysqli_free_result($resource);

	return $result;
}

function decryptSql($handle, $user_id) {

	$salt = 'h5NPaVyw';
	$salt = mysqli_real_escape_string($handle, $salt);

	$sql = "aes_decrypt(unhex(l.pass), sha1(concat(sha1('$salt'), '.', sha1('$user_id')))) as pass";

	return $sql;
}

?>
