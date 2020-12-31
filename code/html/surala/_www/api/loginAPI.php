<?php

// エラーコード
define('SUCCESS',					'0');
define('ERR_WRONG_ID_PASSWD',		'-1');
define('ERR_EXPIRED',				'-2');
define('ERR_STOPPING',				'-3');
define('ERR_ATTENDANCE_STOP',		'-4');
define('ERR_NO_ATHOME',				'-5');
define('ERR_NO_COURSE',				'-6');
define('ERR_GIB_RNKI',				'-7');

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
define('C81_GTEST',		'GTEST');
define('C81_STEST',		'STEST');

// 受講コース区分(48)
define('C48_ONCE',		'2');

// 契約範囲項目(92)
define('C92_CHUGAKU', 'J');
define('C92_KOUKOU',  'H');

// 外部連携(95)
//del start okabe 2020/01/21 外部連携 ケイアイスター様対応
/*
define('C95_RNKI_NSHI',	'0');
define('C95_RNKI_CHIERU',	'1');
define('C95_RNKI_CLASSI',	'2');		// add oda 2019/05/07 Classi連携
*/
//del end okabe 2020/01/21 外部連携 ケイアイスター様対応 次行ファイルにて、上記の定数定義を行なっている。
@require_once("/data/home/_www/ext_lib/config_base.php");	//add okabe 2020/01/21 外部連携 ケイアイスター様対応
include_once("/data/home/www/decryt_field_db.php"); //add kaopiz 2020/25/06 load encrypt file
//@include("/data/home/_www/shared_lib/test_standard/get_services.class.php");	//add okabe 2020/09/09 テスト標準化(校舎契約情報の取得) test del okabe 2020/09/14
@include(LOG_DIR."shared_lib/test_standard/get_services.class.php");	//add okabe 2020/09/14 テスト標準化(校舎契約情報の取得)相対パス変更
// *********** デバッグ用 ***************************************
// 使い方:
//  ログしたい箇所で $GLOBALS['loginAPIlogger']->debug(変数); 指定パスにログが吐かれます。
// 注意:
//  他のincludeと衝突するとエラーになるのでデバッグするとき以外使わないこと!
//
// @require_once("/data/home/_www/shared_lib/logger.php");
// if (file_exists("/data/home/www/log/loginAPI_debug.txt")) {
// 	$loginAPIlogger = new MyLogger("/data/home/www/log/loginAPI_debug.txt");
// }
// ***************************************************************

function getUserInfo($loginID, $passWord, $iconFlg, &$userInfo) {

	$userInfo = array();

	// 統合DB接続
	$handle = null;
	if (($ret = loginApiDbLogin($handle)) < 0 ) {
		return $ret;
	}

	// ログインユーザ情報の取得
	$ret = LoginApiGetLoginInfo($handle, $loginID, $iconFlg, $loginInfo);

	//add start okabe 2020/09/10 テスト標準化
	if (strlen($loginInfo['school_id']) > 0) {
		$myobj = new TestStdServiceStudent();
		$tmp_sch_srvc_info = $myobj->changeSchoolServices($handle, $loginInfo['school_id']);
	}
	//add end okabe 2020/09/10

	$bounceMadrsAry = checkBounceMail($handle, $loginInfo);		//add okabe 2019/05/09 バウンスメール対策

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
		'TEST_GROUP_IDS' => null,
		'TEST_GROUP_IDS_'.C81_STEST => null,
	);
	if ($ret == SUCCESS) {
		$userInfo['COURSE'] = $loginInfo['jyko_surala_cd'];
		$userInfo['OPTION'] = $loginInfo['opt_kyks'];
		$userInfo['KIYKHNI'] = $loginInfo['kiyk_hni'];
		$userInfo['RSYCRS'] = $loginInfo['rsyu_crs'];
		if (count($loginInfo['test_group_ids']) > 0) {
			foreach($loginInfo['test_group_ids'] as $key => $values) {
				$userInfo[$key] = implode(',', $values);
			}
		}
		$userInfo['APPLICABLEBOUNCEMAIL'] = $bounceMadrsAry;	//add okabe 2019/05/09 バウンスメール対策

		//add start okabe 2020/09/10 テスト標準化
		if (!is_null($tmp_sch_srvc_info) && count($tmp_sch_srvc_info) > 0) {
			$userInfo['__school_services'] = $tmp_sch_srvc_info;
		}
		//add end okabe 2020/09/10 テスト標準化

	}
	// $GLOBALS['loginAPIlogger']->debug($userInfo); //DEBUG

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

	//add start okabe 2020/09/09 テスト標準化
	if (strlen($loginInfoTeacher['school_id']) > 0) {
		$myobj = new TestStdServiceTeacher();
		$tmp_sch_srvc_info = $myobj->changeSchoolServices($handle, $loginInfoTeacher['school_id']);
	}
	//add end okabe 2020/09/09

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

	// 外部連携区分のチェック
	//if ($loginInfoTeacher['gib_rnki_kb'] != C95_RNKI_NSHI) {															// debug oda 2019/05/07 Classi連携 最終時点ではこの判断に戻す
	//del start okabe 2020/01/21 外部連携 ケイアイスター様対応
	//if ($loginInfoTeacher['gib_rnki_kb'] != C95_RNKI_NSHI && $loginInfoTeacher['gib_rnki_kb'] != C95_RNKI_CLASSI) {		// debug oda 2019/05/07 Classi連携
	//	return ERR_GIB_RNKI;
	//}
	//del end okabe 2020/01/21 外部連携 ケイアイスター様対応
	//add start okabe 2020/01/21 外部連携 ケイアイスター様対応
	if (property_exists('GaibuRenkeiCls', 'eachSpecs_Core')) {
		if (count(GaibuRenkeiCls::$eachSpecs_Core['getTeacherInfo']) > 0) {
			if (!in_array($loginInfoTeacher['gib_rnki_kb'], GaibuRenkeiCls::$eachSpecs_Core['getTeacherInfo']) ) {
				return ERR_GIB_RNKI;
			}
		} else {
			return ERR_GIB_RNKI;
		}
	}
	//add end okabe 2020/01/21 外部連携 ケイアイスター様対応

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

	//add start okabe 2020/09/10 テスト標準化
	if (!is_null($tmp_sch_srvc_info) && count($tmp_sch_srvc_info) > 0) {
		$teacherInfo['__school_services'] = $tmp_sch_srvc_info;
	}
	//add end okabe 2020/09/10 テスト標準化

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

	$bounceMadrsAry = checkBounceMail($handle, $loginInfo);		//add okabe 2019/05/09 バウンスメール対策

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
		'TEST_GROUP_IDS' => null,
		'TEST_GROUP_IDS_'.C81_STEST => null,
	);
	if ($ret == SUCCESS) {
		if (count($loginInfo['test_group_ids']) > 0) {
			foreach($loginInfo['test_group_ids'] as $key => $values) {
				$userInfo[$key] = implode(',', $values);
			}
		}
		$userInfo['APPLICABLEBOUNCEMAIL'] = $bounceMadrsAry;	//add okabe 2019/05/09 バウンスメール対策
	}

	return $ret;
}

// この関数は、現在使用されていません(参照元が存在しません) 2019/05/13 okabe記 //バウンスメール対策
function getUserCourseInfo($loginID, $iconFlg, &$userInfo) {

	$userInfo = array();

	// 統合DB接続
	$handle = null;
	if (($ret = loginApiDbLogin($handle)) < 0 ) {
		return $ret;
	}

	// 生徒のすらら受講コース情報取得
	$ret = LoginApiGetLoginInfo($handle, $loginID, $iconFlg, $loginInfo, true);

	$bounceMadrsAry = checkBounceMail($handle, $loginInfo);		//add okabe 2019/05/13 バウンスメール対策

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
		$userInfo['APPLICABLEBOUNCEMAIL'] = $bounceMadrsAry;	//add okabe 2019/05/13 バウンスメール対策
	}

	return $ret;
}

// この関数は、現在使用されていません(参照元が存在しません) 2019/05/13 okabe記 //バウンスメール対策
function getUserServiceInfo($loginID, $passWord, $iconFlg, &$userInfo) {

	$userInfo = array();

	// 統合DB接続
	$handle = null;
	if (($ret = loginApiDbLogin($handle)) < 0 ) {
		return $ret;
	}

	// ログインユーザ情報の取得
	$ret = LoginApiGetLoginInfoService($handle, $loginID, $iconFlg, $loginInfo);

	$bounceMadrsAry = checkBounceMail($handle, $loginInfo);		//add okabe 2019/05/13 バウンスメール対策

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
		$userInfo['APPLICABLEBOUNCEMAIL'] = $bounceMadrsAry;	//add okabe 2019/05/13 バウンスメール対策
	}

	return $ret;
}

function getSsoUserInfo($loginID, &$userInfo) {

	$userInfo = array();

	// 統合DB接続
	$handle = null;
	if (($ret = loginApiDbLogin($handle)) < 0 ) {
		return $ret;
	}

	// ログインユーザ情報の取得
	$ret = loginApiSsoGetLoginInfo($handle, $loginID, $loginInfo);

	//add start okabe 2020/09/11 テスト標準化
	if (strlen($loginInfo['school_id']) > 0) {
		$myobj = new TestStdServiceStudent();
		$tmp_sch_srvc_info = $myobj->changeSchoolServices($handle, $loginInfo['school_id']);
	}
	//add end okabe 2020/09/11

	$bounceMadrsAry = checkBounceMail($handle, $loginInfo);		//add okabe 2019/05/13 バウンスメール対策

	mysqli_close($handle);

	if ($ret < 0) {
		return $ret;
	}

	// ログインIDの正当性チェック
	if ($loginInfo == null) {
		return ERR_WRONG_ID_PASSWD;
	}

	// ログインのチェック
	$ret = loginApiSsoCheck($loginInfo);

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
		'TEST_GROUP_IDS' => null,
		'TEST_GROUP_IDS_'.C81_STEST => null,
	);
	if ($ret == SUCCESS) {
		$userInfo['COURSE'] = $loginInfo['jyko_surala_cd'];
		$userInfo['OPTION'] = $loginInfo['opt_kyks'];
		$userInfo['KIYKHNI'] = $loginInfo['kiyk_hni'];
		$userInfo['RSYCRS'] = $loginInfo['rsyu_crs'];
		if (count($loginInfo['test_group_ids']) > 0) {
			foreach($loginInfo['test_group_ids'] as $key => $values) {
				$userInfo[$key] = implode(',', $values);
			}
		}
		$userInfo['APPLICABLEBOUNCEMAIL'] = $bounceMadrsAry;	//add okabe 2019/05/13 バウンスメール対策

		//add start okabe 2020/01/20 外部連携 ケイアイスター様対応
		if (array_key_exists('gib_rnki_kb', $loginInfo) && array_key_exists('cd_value', $loginInfo)) {
			$userInfo['GIBRNKIINFOS'] = array('gib_rnki_kb' => $loginInfo['gib_rnki_kb'], 'cd_value' => $loginInfo['cd_value']);
		}
		//add end okabe 2020/01/20 外部連携 ケイアイスター様対応

		//add start okabe 2020/09/11 テスト標準化
		if (!is_null($tmp_sch_srvc_info) && count($tmp_sch_srvc_info) > 0) {
			$userInfo['__school_services'] = $tmp_sch_srvc_info;
		}
		//add end okabe 2020/09/11 テスト標準化

	}

	return $ret;
}

//add start kimura 2019/08/28 google連携 {{{
/**
 * SSOログイン時のユーザー情報取得(外部連携区分無し 用)
 *
 * @param mixed $loginID
 * @param mixed $userInfo
 * @access public
 * @return void
 */
function getSsoUserInfoRnkiNshi($loginID, &$userInfo) {

	$userInfo = array();

	// 統合DB接続
	$handle = null;
	if (($ret = loginApiDbLogin($handle)) < 0 ) {
		return $ret;
	}

	// ログインユーザ情報の取得
	$ret = loginApiSsoGetLoginInfo($handle, $loginID, $loginInfo);

	//add start okabe 2020/09/11 テスト標準化
	if (strlen($loginInfo['school_id']) > 0) {
		$myobj = new TestStdServiceStudent();
		$tmp_sch_srvc_info = $myobj->changeSchoolServices($handle, $loginInfo['school_id']);
	}
	//add end okabe 2020/09/11

	$bounceMadrsAry = checkBounceMail($handle, $loginInfo);		//add okabe 2019/05/13 バウンスメール対策

	mysqli_close($handle);

	if ($ret < 0) {
		return $ret;
	}

	// ログインIDの正当性チェック
	if ($loginInfo == null) {
		return ERR_WRONG_ID_PASSWD;
	}

	// ログインのチェック
	$ret = loginApiSsoCheckRnkiNshi($loginInfo);

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
		'TEST_GROUP_IDS' => null,
		'TEST_GROUP_IDS_'.C81_STEST => null,
	);
	if ($ret == SUCCESS) {
		$userInfo['COURSE'] = $loginInfo['jyko_surala_cd'];
		$userInfo['OPTION'] = $loginInfo['opt_kyks'];
		$userInfo['KIYKHNI'] = $loginInfo['kiyk_hni'];
		$userInfo['RSYCRS'] = $loginInfo['rsyu_crs'];
		if (count($loginInfo['test_group_ids']) > 0) {
			foreach($loginInfo['test_group_ids'] as $key => $values) {
				$userInfo[$key] = implode(',', $values);
			}
		}
		$userInfo['APPLICABLEBOUNCEMAIL'] = $bounceMadrsAry;	//add okabe 2019/05/13 バウンスメール対策

		//add start okabe 2020/09/11 テスト標準化
		if (!is_null($tmp_sch_srvc_info) && count($tmp_sch_srvc_info) > 0) {
			$userInfo['__school_services'] = $tmp_sch_srvc_info;
		}
		//add end okabe 2020/09/11 テスト標準化
	}

	return $ret;
}
//add end   kimura 2019/08/28 google連携 }}}

function getSsoTeacherInfo($loginID, $loginType, &$teacherInfo) {

	$teacherInfo = array();

	// 統合DB接続
	$handle = null;
	if (($ret = loginApiDbLogin($handle)) < 0 ) {
		return $ret;
	}

	// ログインユーザ情報(先生)の取得
	$ret = loginApiSsoGetLoginInfoTeacher($handle, $loginID, $loginInfoTeacher);

	if ($ret < 0) {
		return $ret;
	}
	//add start okabe 2020/03/24 外部連携 ケイアイスター不動産対応
	$_teacher_gib_rnki_kb = $loginInfoTeacher['gib_rnki_kb'];
	$_teacher_cd_value = loginApiGetGibRnkiCodeValue($handle, $loginInfoTeacher['gib_rnki_kb']);
	//add end okabe 2020/03/24 外部連携 ケイアイスター不動産対応

	//add start okabe 2020/09/11 テスト標準化
	if (strlen($loginInfoTeacher['school_id']) > 0) {
		$myobj = new TestStdServiceTeacher();
		$tmp_sch_srvc_info = $myobj->changeSchoolServices($handle, $loginInfoTeacher['school_id']);
	}
	//add end okabe 2020/09/11

	mysqli_close($handle);	//test del okabe 2020/03/24 @@@@

	// ログインID , パスワードの正当性チェック
	if (!is_array($loginInfoTeacher) || (count($loginInfoTeacher) == 0)) {
		return ERR_WRONG_ID_PASSWD;
	}

	// 外部連携先のチェック
	//if ($loginInfoTeacher['gib_rnki_kb'] != C95_RNKI_CHIERU) {																// del oda 2019/05/07 Classi連携
	//del start okabe 2020/01/21 外部連携 ケイアイスター様対応
	//if ($loginInfoTeacher['gib_rnki_kb'] != C95_RNKI_CHIERU && $loginInfoTeacher['gib_rnki_kb'] != C95_RNKI_CLASSI) {			// add oda 2019/05/07 Classi連携
	//	return ERR_GIB_RNKI;
	//}
	//del end okabe 2020/01/21 外部連携 ケイアイスター様対応
	//add start okabe 2020/01/21 外部連携 ケイアイスター様対応
	if (property_exists('GaibuRenkeiCls', 'eachSpecs_Core')) {
		if (count(GaibuRenkeiCls::$eachSpecs_Core['getSsoTeacherInfo']) > 0) {
			if (!in_array($loginInfoTeacher['gib_rnki_kb'], GaibuRenkeiCls::$eachSpecs_Core['getSsoTeacherInfo']) ) {
				return ERR_GIB_RNKI;
			}
		} else {
			return ERR_GIB_RNKI;
		}
	}
	//add end okabe 2020/01/21 外部連携 ケイアイスター様対応

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

		//add start okabe 2020/03/24 外部連携 ケイアイスター不動産様
		$teacherInfo['_SSO_TEACHER_GIB_RNKI_KB'] = $_teacher_gib_rnki_kb;
		$teacherInfo['_SSO_TEACHER_CD_VALUE'] = $_teacher_cd_value;
		if (strlen($_teacher_cd_value) > 0) {
			foreach(explode("&", $_teacher_cd_value) as $__tmp_k => $__tmp_v) {
				$_tmp_sep_param = explode("=", $__tmp_v);
				$_tmp_res_ary[$_tmp_sep_param[0]] = $_tmp_sep_param[1];
			}
			$teacherInfo['_SSO_TEACHER_SOKU'] = $_tmp_res_ary["SOKU"];
		}
		//add end okabe 2020/03/24 外部連携 ケイアイスター様対応

		//add start okabe 2020/09/11 テスト標準化
		if (!is_null($tmp_sch_srvc_info) && count($tmp_sch_srvc_info) > 0) {
			$teacherInfo['__school_services'] = $tmp_sch_srvc_info;
		}
		//add end okabe 2020/09/11 テスト標準化

	}

	return SUCCESS;
}

//add start kimura 2019/08/29 google連携 {{{
function getSsoTeacherInfoRnkiNshi($loginID, $loginType, &$teacherInfo) {

	$teacherInfo = array();

	// 統合DB接続
	$handle = null;
	if (($ret = loginApiDbLogin($handle)) < 0 ) {
		return $ret;
	}

	// ログインユーザ情報(先生)の取得
	$ret = loginApiSsoGetLoginInfoTeacher($handle, $loginID, $loginInfoTeacher);

	if ($ret < 0) {
		return $ret;
	}
	//add start okabe 2020/09/11 テスト標準化
	if (strlen($loginInfoTeacher['school_id']) > 0) {
		$myobj = new TestStdServiceTeacher();
		$tmp_sch_srvc_info = $myobj->changeSchoolServices($handle, $loginInfoTeacher['school_id']);
	}
	//add end okabe 2020/09/11

	mysqli_close($handle);

	// ログインID , パスワードの正当性チェック
	if (!is_array($loginInfoTeacher) || (count($loginInfoTeacher) == 0)) {
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

	//add start okabe 2020/09/11 テスト標準化
	if (!is_null($tmp_sch_srvc_info) && count($tmp_sch_srvc_info) > 0) {
		$teacherInfo['__school_services'] = $tmp_sch_srvc_info;
	}
	//add end okabe 2020/09/11 テスト標準化

	return SUCCESS;
}
//add end   kimura 2019/08/29 google連携 }}}


// アイコン有りログインのチェック
function loginApiIconExist($loginInfo, $is_gtest = false, $is_service = false) {

	// 外部連携区分のチェック
	//if ($loginInfo['gib_rnki_kb'] != C95_RNKI_NSHI) {														// debug oda 2019/05/07 Classi連携 最終時点ではこの判断に戻す
	//del start okabe 2020/01/21 外部連携 ケイアイスター様対応
	//if ($loginInfo['gib_rnki_kb'] != C95_RNKI_NSHI && $loginInfo['gib_rnki_kb'] != C95_RNKI_CLASSI) {		// debug oda 2019/05/07 Classi連携
	//	return ERR_GIB_RNKI;
	//}
	//del end okabe 2020/01/21 外部連携 ケイアイスター様対応

	//add start okabe 2020/01/21 外部連携 ケイアイスター様対応
	if (property_exists('GaibuRenkeiCls', 'eachSpecs_Core')) {
		if (count(GaibuRenkeiCls::$eachSpecs_Core['loginApiIconExist']) > 0) {
			if (!in_array($loginInfo['gib_rnki_kb'], GaibuRenkeiCls::$eachSpecs_Core['loginApiIconExist']) ) {
				return ERR_GIB_RNKI;
			}
		} else {
			return ERR_GIB_RNKI;
		}
	}
	//add end okabe 2020/01/21 外部連携 ケイアイスター様対応

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

	// 外部連携区分のチェック
	//if ($loginInfo['gib_rnki_kb'] != C95_RNKI_NSHI) {														// debug oda 2019/05/07 Classi連携 最終時点ではこの判断に戻す
	//del start okabe 2020/01/21 外部連携 ケイアイスター様対応
	//if ($loginInfo['gib_rnki_kb'] != C95_RNKI_NSHI && $loginInfo['gib_rnki_kb'] != C95_RNKI_CLASSI) {		// debug oda 2019/05/07 Classi連携
	//	return ERR_GIB_RNKI;
	//}
	//del end okabe 2020/01/21 外部連携 ケイアイスター様対応

	//add start okabe 2020/01/21 外部連携 ケイアイスター様対応
	if (property_exists('GaibuRenkeiCls', 'eachSpecs_Core')) {
		if (count(GaibuRenkeiCls::$eachSpecs_Core['loginApiIconNo']) > 0) {
			if (!in_array($loginInfo['gib_rnki_kb'], GaibuRenkeiCls::$eachSpecs_Core['loginApiIconNo']) ) {
				return ERR_GIB_RNKI;
			}
		} else {
			return ERR_GIB_RNKI;
		}
	}
	//add end okabe 2020/01/21 外部連携 ケイアイスター様対応

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

	//add start kimura 2020/01/15 理社対応 {{{
	//校舎の契約範囲を取得 ※今まで参照していたフィールドが廃止になったため別テーブルから取得
	$schKiykInfo = null;
	if (($schKiykInfo = loginApiGetSchKiykHni($handle, $loginInfo['school_id'])) === false) {
		return ERR_OTHER;
	}
	$loginInfo['kiyk_hni'] = $schKiykInfo;
	//add end   kimura 2020/01/15 理社対応 }}}

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

	if (($loginInfo['gib_rnki_kb'] = loginApiGetGibRnkiKb($handle, $loginInfo['enterprise_id'])) === false) {
		return ERR_OTHER;
	}

	return SUCCESS;
}

function loginApiGetStudentInfoSql($handle, $loginID) {
	$loginID = mysqli_real_escape_string($handle, $loginID);
	$passSql = decryptSql($handle, $loginID);

	$sql = "select l.login_id, ". $passSql . ", TO_DAYS(s.secessionday) - TO_DAYS(now()) as secessionday, s.zitk_flg,".
		  " s.jyko_stop_flg, s.knry_end_flg, sj.sito_jyti, sj.jyko_crs_cd, sl.enterprise_id, sl.kmi_zksi,".
		  " ifnull(to_days(sj.srl_yk_ymd), 0) - to_days(now()) as srl_yk_ymd,".
		  // " to_days(sk.end_ymd) - to_days(now()) as end_ymd, sk.tb, sk.kiyk_hni, sk.kiyk_kb,". //del kimura 2020/01/15 理社対応
		  " to_days(sk.end_ymd) - to_days(now()) as end_ymd, sk.tb, sk.kiyk_kb,". //del kimura 2020/01/15 理社対応 kiyk_hniはフィールド廃止
		  " d.db_host, d.db_port, d.db_user, d.db_pass, d.db_name".
//		", s.student_email, s.student_mobile_email, g.guardian_email, g.guardian_mobile_email, sl.school_email".		//add okabe 2019/05/09 バウンスメール対策
		  ", ". convertDecryptField('s.student_email') .", ". convertDecryptField('s.student_mobile_email') .", ". // kaopiz 2020/08/20 Encoding
			convertDecryptField('g.guardian_email') .", ". convertDecryptField('g.guardian_mobile_email') .", sl.school_email".		// kaopiz 2020/08/20 Encoding
		  ", s.school_id". //add kimura 2020/01/15 理社対応 校舎ID取得
		  " from login l".
		  " inner join db_connect dc on l.enterprise_id = dc.enterprise_id and dc.mk_flg = 0".
		  " inner join db d on dc.db_id = d.db_id and d.mk_flg = 0".
		  " inner join student s on l.login_id = s.student_id and s.mk_flg = 0".
		  " inner join tb_stu_jyko_jyotai sj on l.login_id = sj.student_id and sj.mk_flg = 0".
		  " inner join school sl on s.school_id = sl.school_id and sl.mk_flg = 0".
		  " inner join ms_sch_kiyk sk on s.school_id = sk.school_id and sk.mk_flg = 0".
		  " inner join guardian g on g.guardian_id = s.guardian_id and g.mk_flg = 0".		//add okabe 2019/05/09 バウンスメール対策
		  " where l.login_id = '$loginID' and l.login_type = '1' and l.mk_flg = 0".
		  " order by sk.tb desc limit 1";
	return $sql;
}

function loginApiGetCourseInfoSql($handle, $loginID) {
	$loginID = mysqli_real_escape_string($handle, $loginID);
	$sql = "select s.student_id login_id, TO_DAYS(s.secessionday) - TO_DAYS(now()) as secessionday, s.zitk_flg,".
		  " s.jyko_stop_flg, s.knry_end_flg, sj.sito_jyti, sj.jyko_crs_cd, sl.enterprise_id, sl.kmi_zksi,".
		  " ifnull(to_days(sj.srl_yk_ymd), 0) - to_days(now()) as srl_yk_ymd,".
		  // " to_days(sk.end_ymd) - to_days(now()) as end_ymd, sk.tb, sk.kiyk_hni, sk.kiyk_kb". //del kimura 2020/01/15 理社対応
		  " to_days(sk.end_ymd) - to_days(now()) as end_ymd, sk.tb, sk.kiyk_kb". //add kimura 2020/01/15 理社対応 kiyk_hniはフィールド廃止
//		", s.student_email, s.student_mobile_email, g.guardian_email, g.guardian_mobile_email, sl.school_email".		//add okabe 2019/05/09 バウンスメール対策
		  ", ". convertDecryptField('s.student_email') .", ". convertDecryptField('s.student_mobile_email') .", ". // kaopiz 2020/08/20 Encoding
		 convertDecryptField('g.guardian_email') .", ". convertDecryptField('g.guardian_mobile_email') .", sl.school_email". // kaopiz 2020/08/20 Encoding
		  ", s.school_id". //add kimura 2020/01/15 理社対応 校舎ID取得
		  " from student s".
		  " inner join tb_stu_jyko_jyotai sj on s.student_id = sj.student_id and sj.mk_flg = 0".
		  " inner join school sl on s.school_id = sl.school_id and sl.mk_flg = 0".
		  " inner join ms_sch_kiyk sk on s.school_id = sk.school_id and sk.mk_flg = 0".
		  " inner join guardian g on g.guardian_id = s.guardian_id and g.mk_flg = 0".		//add okabe 2019/05/09 バウンスメール対策
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

		if (($loginInfo['gib_rnki_kb'] = loginApiGetGibRnkiKb($handle, $loginInfo['enterprise_id'])) === false) {
			return ERR_OTHER;
		}
	}

	return SUCCESS;
}


function loginApiGetLoginInfoTeacher($handle, $loginID, &$loginInfoTeacher) {

	$loginInfoTeacher = array();

	$loginID = mysqli_real_escape_string($handle, $loginID);
	$passSql = decryptSql($handle, $loginID);
	// $sql = "select l.login_id,". $passSql .", t.km_tool_use_flg, TO_DAYS(sk.end_ymd) - TO_DAYS(now()) as end_ymd, sk.tb, sk.kiyk_hni,". //del kimura 2020/01/16 理社対応
	$sql = "select l.login_id,". $passSql .", t.km_tool_use_flg, TO_DAYS(sk.end_ymd) - TO_DAYS(now()) as end_ymd, sk.tb,". //add kimura 2020/01/16 理社対応 kiyk_hniはフィールド廃止
		   " sk.enterprise_id, d.db_host, d.db_port, d.db_user, d.db_pass, d.db_name".
		   ",sk.school_id". //add kimura 2020/01/16 理社対応
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

	if (!is_array($loginInfoTeacher) || (count($loginInfoTeacher) == 0)) return ERR_WRONG_ID_PASSWD;

	// 外部連携区分取得
	if (($loginInfoTeacher['gib_rnki_kb'] = loginApiGetGibRnkiKb($handle, $loginInfoTeacher['enterprise_id'])) === false) {
		return ERR_OTHER;
	}

	//add start kimura 2020/01/15 理社対応 {{{
	//校舎の契約範囲を取得 ※今まで参照していたフィールドが廃止になったため別テーブルから取得
	$schKiykInfo = null;
	if (($schKiykInfo = loginApiGetSchKiykHni($handle, $loginInfoTeacher['school_id'])) === false) {
		return ERR_OTHER;
	}
	$loginInfoTeacher['kiyk_hni'] = $schKiykInfo;
	//add end   kimura 2020/01/15 理社対応 }}}

	return SUCCESS;
}

function LoginApiGetLoginInfoGuardian($handle, $loginID, &$loginInfoGua) {

	$loginInfoGua = array();

	$loginID = mysqli_real_escape_string($handle, $loginID);
	$passSql = decryptSql($handle, $loginID);
	$sql = "select l.login_id,". $passSql .", s.student_id, TO_DAYS(s.secessionday) - TO_DAYS(now()) as secessionday, s.zitk_flg,".
		  " s.jyko_stop_flg, s.knry_end_flg, sj.sito_jyti, sj.jyko_crs_cd, sl.enterprise_id, sl.kmi_zksi,".
		  // " TO_DAYS(sk.end_ymd) - TO_DAYS(now()) as end_ymd, sk.tb, sk.kiyk_hni, sk.kiyk_kb,". //del kimura 2020/01/16 理社対応
		  " TO_DAYS(sk.end_ymd) - TO_DAYS(now()) as end_ymd, sk.tb, sk.kiyk_kb,". //add kimura 2020/01/16 理社対応 kiyk_hniはフィールド廃止
		  " d.db_host, d.db_port, d.db_user, d.db_pass, d.db_name".
		  ",sl.school_id". //add kimura 2020/01/16 理社対応
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

	if (($loginInfoGua['gib_rnki_kb'] = loginApiGetGibRnkiKb($handle, $loginInfoGua['enterprise_id'])) === false) {
		return ERR_OTHER;
	}

	//add start kimura 2020/01/15 理社対応 {{{
	//校舎の契約範囲を取得 ※今まで参照していたフィールドが廃止になったため別テーブルから取得
	$schKiykInfo = null;
	if (($schKiykInfo = loginApiGetSchKiykHni($handle, $loginInfoGua['school_id'])) === false) {
		return ERR_OTHER;
	}
	$loginInfoGua['kiyk_hni'] = $schKiykInfo;
	//add end   kimura 2020/01/15 理社対応 }}}

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

		if (($loginInfo['gib_rnki_kb'] = loginApiGetGibRnkiKb($handle, $loginInfo['enterprise_id'])) === false) {
			return ERR_OTHER;
		}
	}

	return SUCCESS;
}

function loginApiSsoGetLoginInfo($handle, $loginID, &$loginInfo) {

	$loginInfo = array();

	$sql = "select l.login_id, TO_DAYS(s.secessionday) - TO_DAYS(now()) as secessionday, s.zitk_flg,".
		  " s.jyko_stop_flg, s.knry_end_flg, sj.sito_jyti, sj.jyko_crs_cd, sl.enterprise_id, sl.kmi_zksi,".
		  " ifnull(to_days(sj.srl_yk_ymd), 0) - to_days(now()) as srl_yk_ymd,".
		  // " to_days(sk.end_ymd) - to_days(now()) as end_ymd, sk.tb, sk.kiyk_hni, sk.kiyk_kb,". //del kimura 2020/01/16 理社対応
		  " to_days(sk.end_ymd) - to_days(now()) as end_ymd, sk.tb, sk.kiyk_kb,". //add kimura 2020/01/16 理社対応
		  " d.db_host, d.db_port, d.db_user, d.db_pass, d.db_name".
		//		", s.student_email, s.student_mobile_email, g.guardian_email, g.guardian_mobile_email, sl.school_email".		//add okabe 2019/05/13 バウンスメール対策
		  ", ". convertDecryptField('s.student_email') .", ". convertDecryptField('s.student_mobile_email') .", ". // kaopiz 2020/08/20 Encoding
		convertDecryptField('g.guardian_email') .", ". convertDecryptField('g.guardian_mobile_email') .", sl.school_email". // kaopiz 2020/08/20 Encoding
		  ", s.school_id". //add kimura 2020/01/16 理社対応
		  " from login l".
		  " inner join db_connect dc on l.enterprise_id = dc.enterprise_id and dc.mk_flg = 0".
		  " inner join db d on dc.db_id = d.db_id and d.mk_flg = 0".
		  " inner join student s on l.login_id = s.student_id and s.mk_flg = 0".
		  " inner join tb_stu_jyko_jyotai sj on l.login_id = sj.student_id and sj.mk_flg = 0".
		  " inner join school sl on s.school_id = sl.school_id and sl.mk_flg = 0".
		  " inner join ms_sch_kiyk sk on s.school_id = sk.school_id and sk.mk_flg = 0".
		  " inner join guardian g on g.guardian_id = s.guardian_id and g.mk_flg = 0".		//add okabe 2019/05/13 バウンスメール対策
		  " where l.login_id = '$loginID' and l.login_type = '1' and l.mk_flg = 0".
		  " order by sk.tb desc limit 1";

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

	//add start kimura 2020/01/15 理社対応 {{{
	//校舎の契約範囲を取得 ※今まで参照していたフィールドが廃止になったため別テーブルから取得
	$schKiykInfo = null;
	if (($schKiykInfo = loginApiGetSchKiykHni($handle, $loginInfo['school_id'])) === false) {
		return ERR_OTHER;
	}
	$loginInfo['kiyk_hni'] = $schKiykInfo;
	//add end   kimura 2020/01/15 理社対応 }}}

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

	// MAXのすらら詳細コースをセット
	if ($free_surala_flg) {
		$loginInfo['jyko_surala_cd'] = loginApiGetDefaultJykocrs($handle, $loginInfo['kiyk_hni']);
		$loginInfo['rsyu_crs'] = C45_A;
	}
	loginApiSetJykoCrs($loginInfo);

	if (isset($crsInfo['opt_kyks'])) {
		$loginInfo['opt_kyks'] = $crsInfo['opt_kyks'];
	}

	if (count($loginInfo) > 0) {
		$loginInfo['test_group_ids'] = loginApiGetTestGroups($handle, $loginID);

		if (($loginInfo['gib_rnki_kb'] = loginApiGetGibRnkiKb($handle, $loginInfo['enterprise_id'])) === false) {
			return ERR_OTHER;
		}

		$loginInfo['cd_value'] = loginApiGetGibRnkiCodeValue($handle, $loginInfo['gib_rnki_kb']);	//add okabe 2020/01/20 外部連携 ケイアイスター様対応

	}

	return SUCCESS;
}

function loginApiSsoGetLoginInfoTeacher($handle, $loginID, &$loginInfoTeacher) {

	$loginInfoTeacher = array();

	$loginID = mysqli_real_escape_string($handle, $loginID);
	// $sql = "select l.login_id, t.km_tool_use_flg, TO_DAYS(sk.end_ymd) - TO_DAYS(now()) as end_ymd, sk.tb, sk.kiyk_hni,". //del kimura 2020/01/16 理社対応
	$sql = "select l.login_id, t.km_tool_use_flg, TO_DAYS(sk.end_ymd) - TO_DAYS(now()) as end_ymd, sk.tb, ". //add kimura 2020/01/16 理社対応 kiyk_hniはフィールド廃止
		   " sk.enterprise_id, d.db_host, d.db_port, d.db_user, d.db_pass, d.db_name".
		   ",sk.school_id". //add kimura 2020/01/16 理社対応
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

	if (!is_array($loginInfoTeacher) || (count($loginInfoTeacher) == 0)) return ERR_WRONG_ID_PASSWD;

	// 外部連携区分取得
	if (($loginInfoTeacher['gib_rnki_kb'] = loginApiGetGibRnkiKb($handle, $loginInfoTeacher['enterprise_id'])) === false) {
		return ERR_OTHER;
	}

	//add start kimura 2020/01/15 理社対応 {{{
	//校舎の契約範囲を取得 ※今まで参照していたフィールドが廃止になったため別テーブルから取得
	$schKiykInfo = null;
	if (($schKiykInfo = loginApiGetSchKiykHni($handle, $loginInfoTeacher['school_id'])) === false) {
		return ERR_OTHER;
	}
	$loginInfoTeacher['kiyk_hni'] = $schKiykInfo;
	//add end   kimura 2020/01/15 理社対応 }}}

	return SUCCESS;
}

// SSO ログインのチェック
function loginApiSsoCheck($loginInfo) {

	// 外部連携先のチェック
	//if ($loginInfo['gib_rnki_kb'] != C95_RNKI_CHIERU) {														// del oda 2019/05/07 Classi連携
	//del start okabe 2020/01/21 外部連携 ケイアイスター様対応
	//if ($loginInfo['gib_rnki_kb'] != C95_RNKI_CHIERU && $loginInfo['gib_rnki_kb'] != C95_RNKI_CLASSI) {			// add oda 2019/05/07 Classi連携
	//	return ERR_GIB_RNKI;
	//}
	//del end okabe 2020/01/21 外部連携 ケイアイスター様対応

	//add start okabe 2020/01/21 外部連携 ケイアイスター様対応
	if (property_exists('GaibuRenkeiCls', 'eachSpecs_Core')) {
		if (count(GaibuRenkeiCls::$eachSpecs_Core['loginApiSsoCheck']) > 0) {
			if (!in_array($loginInfo['gib_rnki_kb'], GaibuRenkeiCls::$eachSpecs_Core['loginApiSsoCheck']) ) {
				return ERR_GIB_RNKI;
			}
		} else {
			return ERR_GIB_RNKI;
		}
	}
	//add end okabe 2020/01/21 外部連携 ケイアイスター様対応

	// 校舎有効期限のチェック
	if ($loginInfo['end_ymd'] < 0) {
		return ERR_EXPIRED;
	}

	// 受講停止フラグのチェック
	if ($loginInfo['jyko_stop_flg'] == C58_TEISHI) {
		return ERR_ATTENDANCE_STOP;
	}

	// 生徒・有効期限のチェック
	if ($loginInfo['secessionday'] < 0) {
		return ERR_EXPIRED;
	}

	return SUCCESS;
}

//add start kimura 2019/08/28 google連携 {{{
// SSO ログインのチェック(外部連携区分無し用)
function loginApiSsoCheckRnkiNshi($loginInfo) {

	// 外部連携先が無しであることのチェック
	//del start okabe 2020/01/21 外部連携 ケイアイスター様対応
	//if ($loginInfo['gib_rnki_kb'] != C95_RNKI_NSHI){
	//	return ERR_GIB_RNKI;
	//}
	//del end okabe 2020/01/21 外部連携 ケイアイスター様対応

	//add start okabe 2020/01/21 外部連携 ケイアイスター様対応
	if (property_exists('GaibuRenkeiCls', 'eachSpecs_Core')) {
		if (count(GaibuRenkeiCls::$eachSpecs_Core['loginApiSsoCheckRnkiNshi']) > 0) {
			if (!in_array($loginInfo['gib_rnki_kb'], GaibuRenkeiCls::$eachSpecs_Core['loginApiSsoCheckRnkiNshi']) ) {
				return ERR_GIB_RNKI;
			}
		} else {
			return ERR_GIB_RNKI;
		}
	}
	//add end okabe 2020/01/21 外部連携 ケイアイスター様対応

	// 校舎有効期限のチェック
	if ($loginInfo['end_ymd'] < 0) {
		return ERR_EXPIRED;
	}

	// 受講停止フラグのチェック
	if ($loginInfo['jyko_stop_flg'] == C58_TEISHI) {
		return ERR_ATTENDANCE_STOP;
	}

	// 生徒・有効期限のチェック
	if ($loginInfo['secessionday'] < 0) {
		return ERR_EXPIRED;
	}

	return SUCCESS;
}
//add end   kimura 2019/08/28 google連携 }}}

function loginApiGetDefaultJykocrs($handle, $riyo_hni) {

	//del start kimura 2020/01/16 理社対応 {{{
	// $riyo_hni = mysqli_real_escape_string($handle, $riyo_hni);
	// $default_cd = null;

	// $sql = "select s.sysi_crs_cd, count(*) crs_su";
	// $sql .= " from ms_sysi_crs s";
	// $sql .= " join ms_sysi_crs_kmk ck on s.sysi_crs_cd = ck.sysi_crs_cd";
	// $sql .= " join ms_kmk k on ck.kmk_cd = k.kmk_cd and k.mk_flg='0'";
	// $sql .= " where rsyu_crs is null and s.mk_flg=0 and s.riyo_hni='$riyo_hni'";
	// $sql .= " group by s.sysi_crs_cd";
	// $sql .= " order by crs_su desc limit 1";

	// if (!($resource = mysqli_query($handle, $sql))) {
	// 	return $default_cd;
	// }
	// if (mysqli_num_rows($resource) > 0) {
	// 	$ms_jyko_crs =  mysqli_fetch_assoc($resource);

	// 	$default_cd = $ms_jyko_crs['sysi_crs_cd'];
	// }
	// mysqli_free_result($resource);

	// return $default_cd;
	//del end kimura 2020/01/16 理社対応 }}}

	//今の校舎契約範囲をすべて詳細コースコードに変換し、その中で一番科目が多いものを最大の詳細コースとして取るように変更
	//[20PJ, 30LPJ, 50LPJH] ---> MAX 50LPJH (5科目)
	//add start kimura 2020/01/16 理社対応 {{{
	$default_cd = null;

	//del start kimura 2020/03/23 理社対応 fb対応 {{{
	// $sysi_crs_cds = array();

	// foreach ($riyo_hni as $kyouka_ => $hni_) {
	// 	if (strlen($kyouka_) == 1) { $kyouka_ = "1".$kyouka_; }
	// 	$sysi_crs_cds[] = $kyouka_.$hni_;
	// }
	// 最大の科目の詳細の権限を返してしまうと[50J, 30LPJH, ...]という契約だったときに
	// 50Jが返ってしまい中学しか受講できなかったので修正
	//
	// $sysi_crs_cd_in = implode("','", $sysi_crs_cds);

	// $sql = "select s.sysi_crs_cd, count(*) crs_su";
	// $sql .= " from ms_sysi_crs s";
	// $sql .= " join ms_sysi_crs_kmk ck on s.sysi_crs_cd = ck.sysi_crs_cd";
	// $sql .= " join ms_kmk k on ck.kmk_cd = k.kmk_cd and k.mk_flg='0'";
	// $sql .= " where rsyu_crs is null and s.mk_flg=0 and s.sysi_crs_cd IN ('".$sysi_crs_cd_in."')";
	// $sql .= " group by s.sysi_crs_cd";
	// $sql .= " order by crs_su desc limit 1";

	// if (!($resource = mysqli_query($handle, $sql))) {
	// 	return $default_cd;
	// }
	// if (mysqli_num_rows($resource) > 0) {
	// 	$ms_jyko_crs =  mysqli_fetch_assoc($resource);

	// 	$default_cd = $ms_jyko_crs['sysi_crs_cd'];
	// }
	// mysqli_free_result($resource);
	//del end kimura 2020/03/23 理社対応 fb対応 }}}

	//add start kimura 2020/03/24 理社対応 fb対応 {{{
	//今の校舎契約範囲の教科＋学年の範囲に指定してある範囲は全て受講できるように修正
	$KYOUKA_50 = array("E", "K", "S", "B", "C");
	$KYOUKA_30 = array("E", "K", "S");
	$KYOUKA_20 = array("B", "C");
	$KYOUKA_E = array("E");
	$KYOUKA_K = array("K");
	$KYOUKA_S = array("S");
	$KYOUKA_B = array("B");
	$KYOUKA_C = array("C");

	$kyouka_gknn_list = null;
	foreach ($riyo_hni as $kyouka => $gknn) { //key : "E" => value : "JH"
		$kyoukas = ${'KYOUKA_'.$kyouka};

		foreach ($kyoukas as $kyk) {
			$kyouka_gknn_list[$kyk][] = str_split($gknn);
		}
	}

	//別ロジックと共通化するためファンクション化
	//del start kimura 2020/04/01 理社対応_202004 ======================{{{
	//$kyouka_gknn_list = array_map("arr_flatten", $kyouka_gknn_list); //各要素を一次元配列に
	//$kyouka_gknn_list = array_map("array_unique", $kyouka_gknn_list); //ユニーク

	//////キーを学年・値を教科の形に整形
	//$kyouka_gknn_list2 = null;
	//foreach ($kyouka_gknn_list as $kyouka => $gknn_list) {
	//	usort($gknn_list, "gknnSorter"); //LPJH順に
	//	$gknn = implode("", $gknn_list); //[L, P, J] ---> "LPJ"

	//	//不要学年は削除>>>
	//	//不要な学年の受講可能情報までコアに渡してしまうと、開放していないコンテンツを選べてしまうパターンがあるため
	//	if ($kyouka == "E") { //英
	//		$gknn = str_replace(array("L", "P"), "", $gknn);
	//	} elseif ($kyouka == "B" || $kyouka == "C") { //理・社
	//		$gknn = str_replace(array("L", "H"), "", $gknn);
	//	}
	//	//<<<不要学年は削除

	//	//学年の範囲が同じ教科をキーでまとめる
	//	//[LPJ] => [K, S, ...]
	//	if ($gknn != "") { //空になっていないでない場合だけ
	//		$kyouka_gknn_list2[$gknn][] = $kyouka;
	//	}
	//}
	//unset($kyouka, $gknn_list);

	////+で連結する元要素配列
	//$default_cd_cds = array();
	//////学年でまとめた教科リストを、詳細コースコードの形に整形する
	//foreach ($kyouka_gknn_list2 as $gknn => $kyoukas) {
	//	$default_cd_cds[] = strval(count($kyoukas)).implode("", $kyoukas).$gknn; // {教科数}{教科文字の連結}{学年}の形 ex. [ [LPJH] => [K, S] ] ---> "2KSLPJH"
	//}

	////組み立てた詳細コースを+で連結する
	////学年ごとに教科をまとめてあるので、同教科同学年の情報は上書きされずユニークな情報になります。 2EJ+2EKSJなどの「EJ」が被るパターンは作られません。
	//$default_cd = implode("+", $default_cd_cds);
	////add end   kimura 2020/03/24 理社対応 fb対応 }}}

	//return $default_cd;
	////add end   kimura 2020/01/16 理社対応 }}}
	//del end kimura 2020/04/01 理社対応_202004 }}}
	return makeSysiCrsByKyoukaGknn($kyouka_gknn_list); //add kimura 2020/04/01 理社対応_202004 //詳細コースコードを返却
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

						//del start kimura 2020/04/01 理社対応_202004 ================================================{{{
						//// upd start hasegawa 2019/11/21 理社対応
						//// $crsnm_type = $kmkInfo['crs_su'];
						//// if ($crsnm_type == '2') {
						//// 	if (strpos($kmkInfo['riyo_hni'], C92_CHUGAKU) === false) {
						//// 		if (strpos($kmkInfo['riyo_hni'], C92_KOUKOU) === false) {
						//// 			$crsnm_type = '3';
						//// 		}
						//// 	}
						//// }
						////
						//// if ($crsnm_type == '3') {
						//// 	$crsInfo['surala_crs_cd'] = '30'.$kmkInfo['riyo_hni'];
						//// } else {
						//// 	$crsInfo['surala_crs_cd'] = $kmkInfo['crs_su'].$kmkInfo['jyko_kyk'].$kmkInfo['riyo_hni'];
						//// }
						//// $crsnm_type = 0; //del kimura 2020/03/26 理社対応 不要ロジックのため削除

						//if (!preg_match('/\+/', $kmkInfo[0]['sysi_crs_cd'])) {

						//	$riyo_hni = null;
						//	// $jyko_kyk = null; //del kimura 2020/03/26  理社対応 不要ロジックのため削除

						//	foreach($kmkInfo as $k => $v) {
						//		$riyo_hni = $v['riyo_hni'];
						//		// $jyko_kyk = $v['jyko_kyk']; //del kimura 2020/03/26  理社対応 不要ロジックのため削除
						//		// $crsnm_type++; //del kimura 2020/03/26 理社対応 不要ロジックのため削除
						//	}

						//	//del start kimura 2020/03/26 理社対応 不要ロジックのため削除 {{{
						//	// // 小（低）・小（高）の場合30とする処理
						//	// if ($crsnm_type == '2') {
						//	// 	if (strpos($riyo_hni, C92_CHUGAKU) === false) {
						//	// 		if (strpos($riyo_hni, C92_KOUKOU) === false) {
						//	// 			$crsnm_type = '3';
						//	// 		}
						//	// 	}
						//	// }
						//	//del end kimura 2020/03/26 理社対応 不要ロジックのため削除 }}}

						//	if (preg_match('/20/', $kmkInfo[0]['sysi_crs_cd'])) {
						//		$crsInfo['surala_crs_cd'] = '20'.$riyo_hni;
						//	} elseif (preg_match('/30/', $kmkInfo[0]['sysi_crs_cd'])) {
						//		$crsInfo['surala_crs_cd'] = '30'.$riyo_hni;
						//	} elseif (preg_match('/50/', $kmkInfo[0]['sysi_crs_cd'])) {
						//		$crsInfo['surala_crs_cd'] = '50'.$riyo_hni;
						//	} else {
						//		// $crsInfo['surala_crs_cd'] = $kmkInfo['crs_su'].$jyko_kyk.$riyo_hni; //del kimura 2020/03/10 理社対応-NG修正 ↑のforeachで最後に上書きされた値が入っているので１コースになってしまう 2ESJ→1SJ
						//		$crsInfo['surala_crs_cd'] = $kmkInfo[0]['sysi_crs_cd']; //add kimura 2020/03/10 理社対応-NG修正 詳細コースコードをそのまま使えばよい (50|30|20以外の「+」を含まないコード、1KPJH, 2ESJ, 3CKSPJ, 4BEKSJなど)
						//	}
						//} else {
						//	foreach($kmkInfo as $k => $v) {
						//		$crsInfo['surala_crs_cd'] .= $v['jyko_kyk'].$v['riyo_hni']."+";
						//	}
						//	$crsInfo['surala_crs_cd'] = rtrim($crsInfo['surala_crs_cd'], '+');
						//}
						//// upd end hasegawa 2019/11/21
						//del end kimura 2020/04/01 理社対応_202004 ================================================}}}

						//add start kimura 2020/04/01 理社対応_202004 ================================================{{{
						//↑マスタ設定によっては動作しない可能性が有るため改修します。
						$kyouka_gknn_list = array();
						//科目情報から詳細コースコードに変換するための配列を組み立てる
						foreach ($kmkInfo as $kmk) {
							//1教科 : n学年
							$kyouka_gknn_list[$kmk['jyko_kyk']][] = str_split($kmk['riyo_hni']);
						}
						$crsInfo['surala_crs_cd'] = makeSysiCrsByKyoukaGknn($kyouka_gknn_list); //詳細コースコードを返却
						//add end   kimura 2020/04/01 理社対応_202004 ================================================}}}
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
	$srvcCds = array(C81_GTEST, C81_STEST);
	$srvc_test_groups = array();

	foreach($srvcCds as $srvcCd) {

		// 申込済(受験可能または受験済)テストグループID取得
		if ($srvcCd == C81_GTEST) {	//GTEST の場合
			$skey = 'TEST_GROUP_IDS';
			$fields = 'test_gknn';

			//del start okabe 2020/09/08 テスト標準化
			//$sql = "select t.test_group_id".
			//	" from tb_stu_gtest_mskm m ".
			//	" inner join  ms_test_group t ".
			//	" on m.test_group_id=t.test_group_id and t.mk_flg=0 ".
			//	" where m.mk_flg=0 and m.student_id='$student_id'";
			//del end okabe 2020/09/08 テスト標準化

			//add start okabe 2020/09/08 テスト標準化
			$sql = "SELECT t.test_group_id ".
				" FROM tb_stu_gtest_mskm m ".
				" INNER JOIN ms_test_group t ".
				" ON m.test_group_id=t.test_group_id ".
				" AND t.mk_flg=0 ".
				" WHERE m.mk_flg=0 ".
				" AND m.student_id='".$student_id."' ".
				" AND t.class_id='';";
			//add end okabe 2020/09/08 テスト標準化

		} else {	//STEST の場合
			$skey = 'TEST_GROUP_IDS_'.$srvcCd;
			$fields = 'class_id';
			$srvcCd = mysqli_real_escape_string($handle, $srvcCd);

			//del start okabe 2020/09/08 テスト標準化
			//$sql = "select t.test_group_id".
			//	" from tb_stu_mskm_crs m ".
			//	" inner join  ms_test_group t ".
			//	" on m.test_group_id=t.test_group_id and t.mk_flg=0 ".
			//	" where m.mk_flg=0 and m.student_id='$student_id' ".
			//	" and t.srvc_cd='$srvcCd'";
			//del end okabe 2020/09/08 テスト標準化

			//add start okabe 2020/09/08 テスト標準化
			$sql = "SELECT t.test_group_id ".
				" FROM tb_stu_mskm_crs m ".
				" INNER JOIN ms_test_group t ".
				" ON m.test_group_id=t.test_group_id AND t.mk_flg=0 ".
				" WHERE m.mk_flg=0 ".
				" AND m.student_id='".$student_id."' ".
				" AND t.class_id > '';";
			//add end okabe 2020/09/08 テスト標準化
		}

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
		if (count($result) == 0) {
			continue;
		}

		// 練習コース取得(受験可のみ)
		$sql = "select test_group_id".
			" from ms_test_group ".
			" where ". $fields ." like 'R%'".
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

		if (count($result) > 0) {
			$srvc_test_groups[$skey] = array();
			foreach ($result as $key => $value) {
				$test_grp_ids[] = $value['test_group_id'];
			}
			sort($test_grp_ids);
			$srvc_test_groups[$skey] = $test_grp_ids;
		}
	}

	return $srvc_test_groups;
}

function loginApiGetServiseInfoSql($handle, $loginID) {
	$loginID = mysqli_real_escape_string($handle, $loginID);

	$sql = "select mc.sysi_crs_cd, mc.jyko_crs_kb, mc.srvc_cd, s.rsyu_crs";
	$sql .= " from tb_stu_mskm_crs mc";
	$sql .= " join ms_sysi_crs s on mc.sysi_crs_cd=s.sysi_crs_cd and s.mk_flg=0";
	$sql .= " and ((s.jyko_crs_kb = '1')";
	$sql .= " or (s.jyko_crs_kb = '2' and s.kkn_from <= curdate() and curdate() <= s.kkn_to))";
	$sql .= " where mc.jyko_start_ymd <= curdate() and mc.mk_flg='0'";
	$sql .= " and mc.test_group_id is null";
	$sql .= " and mc.student_id='$loginID'";

	return $sql;
}

function loginApiGetKmkInfo($handle, $sysi_crs_cd) {
	$sysi_crs_cd = mysqli_real_escape_string($handle, $sysi_crs_cd);

	// $sql = "select ck.sysi_crs_cd, k.kmk_cd, s.riyo_hni, k.jyko_kyk, count(*) crs_su";	// upd start hasegawa 2019/11/21 理社対応
	$sql = "select ck.sysi_crs_cd, k.kmk_cd, k.riyo_hni, k.jyko_kyk";
	$sql .= " from ms_sysi_crs_kmk ck";
	$sql .= " join ms_kmk k on ck.kmk_cd = k.kmk_cd and k.mk_flg=0";
	$sql .= " join ms_sysi_crs s on ck.sysi_crs_cd=s.sysi_crs_cd and s.mk_flg=0";
	$sql .= " where ck.sysi_crs_cd='$sysi_crs_cd'";
	// $sql .= " group by ck.sysi_crs_cd";							// del hasegawa 2019/11/21 理社対応
	if (!($resource = mysqli_query($handle, $sql))) {
		return false;
	}


	// upd start hasegawa 2019/11/21 理社対応
	// $result = null;
	// if (mysqli_num_rows($resource) > 0) {
	// 	$result =  mysqli_fetch_assoc($resource);
	// }
	// mysqli_free_result($resource);

	$result = array();
	if (mysqli_num_rows($resource) > 0) {
		while ($row = mysqli_fetch_assoc($resource)) {
			$result[] = $row;
		}
	}
	mysqli_free_result($resource);
	// upd end hasegawa 2019/11/21

	return $result;
}

function decryptSql($handle, $user_id) {

	$salt = 'h5NPaVyw';
	$salt = mysqli_real_escape_string($handle, $salt);

	$sql = "aes_decrypt(unhex(l.pass), sha1(concat(sha1('$salt'), '.', sha1('$user_id')))) as pass";

	return $sql;
}

function loginApiGetGibRnkiKb($handle, $enterprise_id) {
	$enterprise_id = mysqli_real_escape_string($handle, $enterprise_id);

	$sql = "select gib_rnki_kb from enterprise where enterprise_id = '$enterprise_id' and mk_flg = 0";
	if (!($resource = mysqli_query($handle, $sql))) {
		return false;
	}
	$result = null;
	if (mysqli_num_rows($resource) > 0) {
		$result =  mysqli_fetch_assoc($resource);
	}
	mysqli_free_result($resource);

	$gib_rnki_kb = $result['gib_rnki_kb'];

	return $gib_rnki_kb;
}


// add start okabe 2020/01/20 外部連携ケイアイスター様対応
function loginApiGetGibRnkiCodeValue($handle, $komk_cd) {
	$komk_cd = mysqli_real_escape_string($handle, $komk_cd);

	$sql = "select cd_value from ms_cd_nm where komk_cd = '$komk_cd' and bnr_cd = '95' and mk_flg = 0";
	if (!($resource = mysqli_query($handle, $sql))) {
		return false;
	}
	$result = null;
	if (mysqli_num_rows($resource) > 0) {
		$result =  mysqli_fetch_assoc($resource);
	}
	mysqli_free_result($resource);

	$cd_value = $result['cd_value'];

	return $cd_value;
}
// add end okabe 2020/01/20 外部連携ケイアイスター様対応



// add start okabe 2019/05/09 バウンスメール対策
// ログインした生徒のメールアドレス、その保護者のメールアドレス、所属校舎のメールアドレスをバウンスしたメールアドレスかチェックする。
// return: 配列 or FALSE
//         バウンスメールのチェック結果が正常に得られていれば配列を返すが、対象となるメールアドレスが１つも無い場合は、０件の配列を返す。
//         x DBエラーなどがあったら FALSE を返す ⇒  エラーがあっても０件として処理する。
function checkBounceMail($handle, $loginInfo) {
	$madrs = array(
		$loginInfo['guardian_mobile_email'],
		$loginInfo['guardian_email'],
		$loginInfo['school_email'],
		$loginInfo['student_mobile_email'],
		$loginInfo['student_email']
		);
	$madrs2 = array_unique($madrs);
	$madrs = array_values($madrs2);	//重複したものを除外する

	$res = array();	//無効メールアドレス（バウンス扱いとなっているもの）を、この配列に入れる。

//	$sql = "select distinct email from bounce_mail_log where mk_flg=0 and (";
	$sql = "select distinct ". convertDecryptField('email') ." from bounce_mail_log where mk_flg=0 and ("; // kaopiz 2020/08/20 Encoding
	$add_or = 0;
	foreach($madrs as $k => $v) {
		if (strlen($v) > 0) {
			if ($add_or == 1) { $sql .= " or "; }
			$sql .= "email='".$v."'";
			$add_or = 1;
		}
	}
	$sql .= ");";

	if (!($resource = mysqli_query($handle, $sql))) {
		//return false;
		return $res;
	}

	if ($add_or == 1) {	//検索対象がある場合のみ実行 add 2019/05/13
		if (mysqli_num_rows($resource) > 0) {
			while ($row = mysqli_fetch_assoc($resource)) {
				$res[] = $row['email'];
			}
		}
		mysqli_free_result($resource);
	}

	return $res;
}
// add end okabe 2019/05/09 バウンスメール対策

//add start kimura 2020/01/15 理社対応 {{{
function loginApiGetSchKiykHni($handle, $school_id) {

	$res = false;

	$school_id = mysqli_real_escape_string($handle, $school_id);

	$sql = " SELECT kyouka, kiyk_hni";
	$sql.= " FROM ms_sch_crs_kiyk_hni";
	$sql.= " WHERE 1";
	$sql.= "  AND school_id = '".$school_id."'";
	$sql.= "  AND mk_flg = '0'";

	if (!($resource = mysqli_query($handle, $sql))) {
		return false;
	}

	if (mysqli_num_rows($resource) > 0) {
		while ($row = mysqli_fetch_assoc($resource)) {
			$res[$row['kyouka']] = $row['kiyk_hni'];
		}
		mysqli_free_result($resource);
	} else {
		return false;
	}

	return $res;
}
//add end   kimura 2020/01/15 理社対応 }}}

//add start kimura 2020/03/23 理社対応 fb対応 {{{
//usort用 L->P->J->H順に一次元配列をソートするフィルタ関数
function gknnSorter($a, $b) {
	$order = array("L" => 0, "P" => 1, "J" => 2, "H" => 3);
	foreach	($order as $key => $val) {
		if($a == $key) { return 0; }
		if($b == $key) { return 1; }
	}
}
//配列をの次元をいっこへらして平らにする
function arr_flatten($arr){
  $v = [];
  array_walk_recursive($arr, function($e)use(&$v){$v[] = $e;});
  return $v;
}
//add end   kimura 2020/03/23 理社対応 fb対応 }}}

//add start kimura 2020/04/01 理社対応_202004 {{{
/**
 * @param array $gknn_list
 * @return array
 */
function sortGknnArrayAsc($gknn_list) {
	if (!is_array($gknn_list)) {
		return array();
	}
	usort($gknn_list, "gknnSorter");
	return $gknn_list; //LPJH順に
}

/**
 * @param array $gknn_list
 * @return string
 */
function gknnArrayToString($gknn_list) {
	if (!is_array($gknn_list)) {
		return "";
	}
	$gknn_list = sortGknnArrayAsc($gknn_list);
	return implode("", $gknn_list);
}
//add end   kimura 2020/04/01 理社対応_202004 }}}

//add start kimura 2020/04/01 理社対応_202004 {{{
/**
 * 教科と学年の連想配列から詳細コースコードを作成して返す
 * 教科に含まれるすべての学年が使える形の詳細コースコードを生成して返します。
 *
 * 入力：
 * ※校舎契約範囲/詳細コース科目関連 などをこの形にして持ってくる
 *
 * Array (
 *  [E] => Array (
 *          [0] => J
 *         )
 *  [K] => Array (
 *          [0] => P
 *          [1] => J
 *         )
 *  [S] => Array (
 *          [0] => P
 *          [1] => J
 *         )
 * )
 *
 * 出力(String): "1EJ+2KSPJ"
 *
 * @param array $kyouka_gknn_list_
 * @return string
 */
function makeSysiCrsByKyoukaGknn($kyouka_gknn_list_) {

	// $GLOBALS['loginAPIlogger']->debug("kyouka_gknn_list_");
	// $GLOBALS['loginAPIlogger']->debug(print_r($kyouka_gknn_list_,true));

	//学年ごとの未使用教科
	$GKNN_UNUSED = null;
	$GKNN_UNUSED['E'] = array("L", "P");
	$GKNN_UNUSED['B'] = array("L", "H");
	$GKNN_UNUSED['C'] = array("L", "H");

	//完成形を入れる配列
	$kyouka_gknn_list = array();

	//ワーク配列
	$kyouka_gknn_list_work = array_map("arr_flatten", $kyouka_gknn_list_); //各学年を一次元配列に
	$kyouka_gknn_list_work = array_map("array_unique", $kyouka_gknn_list_work); //ユニーク

	// $GLOBALS['loginAPIlogger']->debug("kyouka_gknn_list_work");
	// $GLOBALS['loginAPIlogger']->debug(print_r($kyouka_gknn_list_work,true));
	//キー:学年 / 値:教科 の形に整形
	foreach ($kyouka_gknn_list_work as $kyouka => $gknn_list) {
		//学年配列をLPJH順に分解して文字列連結
		$gknn_str = gknnArrayToString($gknn_list);

		//不要学年は削除>>>
		//不要な学年の受講可能情報(英語の低学年など)までコアに渡してしまうと、開放していないコンテンツを選べてしまうパターンがあるため
		if (isset($GKNN_UNUSED[$kyouka])) {
			$gknn_str = str_replace($GKNN_UNUSED[$kyouka], "", $gknn_str);
		}
		//<<<不要学年は削除

		//学年の範囲が同じ教科をキーでまとめる
		//[LPJ] => [K, S, ...]
		if ($gknn_str != "") {
			$kyouka_gknn_list[$gknn_str][] = $kyouka;
		}
	}
	unset($kyouka, $gknn_list, $gknn_str);

	//ここまでで$kyouka_gknn_listがこんな形になる
	//$kyouka_gknn_list[LPJ] => [K, S]
	//$kyouka_gknn_list[PJ] => [B]
	//$kyouka_gknn_list[H] => [E]

	// $GLOBALS['loginAPIlogger']->debug("kyouka_gknn_list ".__LINE__);
	// $GLOBALS['loginAPIlogger']->debug(print_r($kyouka_gknn_list, true));

	//配列が空だったら詳細コースコード""を返す
	if (empty($kyouka_gknn_list)) {
		return "";
	}

	//+で連結する詳細コース配列
	$sysi_crs_cds = array();
	//学年でまとめた教科リストを詳細コースコードの形に整形する
	foreach ($kyouka_gknn_list as $gknn => $kyoukas) {
		$sysi_crs_cds[] = strval(count($kyoukas)).implode("", $kyoukas).$gknn; // {教科数}{教科文字の連結}{学年}の形 ex. [ [LPJH] => [K, S] ] ---> "2KSLPJH"
	}
	//組み立てた詳細コースを+で連結する
	//学年ごとに教科をまとめてあるので、同教科同学年の情報は上書きされずユニークな情報になります。 2EJ+2EKSJなどの「EJ」が被るパターンは作られません。
	$sysi_crs_cd = implode("+", $sysi_crs_cds);

	// $GLOBALS['loginAPIlogger']->debug("sysi_crs_cd:".$sysi_crs_cd); //DEBUG
	return $sysi_crs_cd;
	//add end   kimura 2020/03/24 理社対応 fb対応 }}}
}
//add end   kimura 2020/04/01 理社対応_202004 }}}

?>
