<?php
/**
 * ベンチャー・リンク　すらら
 *
 * TOEICログインの為のAPI
 *
 * @author Azet
 */

/**
 * ユーザの情報を読み込む
 *
 * AC:[C]共通 UC1:[C01]ログイン.
 *
 * @author Azet
 * @param string $loginID
 * @param string $passWord
 * @param array &$userInfo
 * @return mixed
 */
function getUserLoginInfo($loginID, $passWord, &$userInfo) {

	$userInfo = array();

	// 統合ＤＢへのログイン
	$handle = null;
	if (($ret = loginApiDbLogin($handle)) < 0 ) {
		return $ret;
	}

	if ($handle == null) return $ret;

	// ログインユーザ情報の取得
	$loginInfo = array();
	if( ($ret = LoginApiGetonlyLoginInfo($handle, $loginID, $loginInfo)) < 0) {
		return $ret;
	}

	// DBログオフ
	mysqli_close($handle);

	// ログインID , パスワードの正当性チェック
	if ($loginInfo == null) {
		return ERR_WRONG_ID_PASSWD;
	}
	if ($loginInfo['pass'] != $passWord) {
		return ERR_WRONG_ID_PASSWD;
	}

	$userInfo['DBHOST'] = $loginInfo['db_host'];
	$userInfo['DBNAME'] = $loginInfo['db_name'];
	$userInfo['DBUSER'] = $loginInfo['db_user'];
	$userInfo['DBPASSWD'] = $loginInfo['db_pass'];
	$userInfo['DBPORT'] = $loginInfo['db_port'];

	$userInfo['COURSE'] = null;
	$userInfo['OPTION'] = null;
	$userInfo['KIYKHNI'] = null;
	$userInfo['RSYCRS'] = null;
	$userInfo['TEST_GROUP_IDS'] = null;

	return $ret;
}

/**
 * ユーザのログイン情報を読み込む
 *
 * AC:[C]共通 UC1:[C01]ログイン.
 *
 * @author Azet
 * @param string $loginID
 * @param string $passWord
 * @param array &$userInfo
 * @return boolean
 */
function LoginApiGetonlyLoginInfo($handle, $loginID, &$loginInfo) {

	$sql = loginApiGetStudentInfoSql($handle, $loginID);
	if (!($resource = mysqli_query($handle, $sql))) {
		return ERR_OTHER;
	}

	$loginInfo = array();
	if (mysqli_num_rows($resource) > 0) {
		$loginInfo =  mysqli_fetch_assoc($resource);
	}
	mysqli_free_result($resource);

	if (count($loginInfo) == 0) return ERR_WRONG_ID_PASSWD;

	// 生徒状態のチェック
	if ($loginInfo['sito_jyti'] == C14_KYUUSHI || $loginInfo['sito_jyti'] == 9) {
		return ERR_STOPPING;
	}

	return SUCCESS;
}
?>
