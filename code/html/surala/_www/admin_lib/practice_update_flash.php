<?PHP
/**
 * 未使用 2017/10/04 oda どこからも呼ばれていない
 * ベンチャー・リンク　すらら
 *
 * プラクティスステージ管理　プラクティスアップデートプログラム
 * 	サブプログラム	学習FLASHファイルアップデート
 *
 * @author Azet
 */


/**
 * HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $L_NAME
 * @return string HTML
 */
function sub_start($L_NAME) {

	if (ACTION == "update") {
		$ERROR = update();
	}

	if (!$ERROR && ACTION == "update") {
		$html = update_end_html($L_NAME);
	} else {
		$html = default_html($L_NAME,$ERROR);
	}

	return $html;
}


/**
 * デフォルトページ
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $L_NAME
 * @param array $ERROR
 * @return string HTML
 */
function default_html($L_NAME,$ERROR) {

	//	フォルダー設定
	$local_dir = MATERIAL_FLASH_DIR.$_POST['course_num']."/".$_POST['stage_num']."/".
					$_POST['lesson_num']."/".$_POST['unit_num']."/";
	$remote_dir = REMOTE_MATERIAL_FLASH_DIR.$_POST['course_num']."/".$_POST['stage_num']."/".
					$_POST['lesson_num']."/".$_POST['unit_num']."/";

	//	ローカルサーバーフォルダー内最新ファイル時間
	$local_time = local_read_dir_time($local_dir);

	// -- リモートサーバーフォルダー内最新ファイル時間
	require_once 'Net/FTP.php';
	//	ftp接続
	$ftp = new Net_FTP();
	$connect = $ftp->connect(FTP_HOST, FTP_PORT);
	$login = $ftp->login(FTP_USERID, FTP_PASSWORD);
	if (!$login) {
		$ERROR[] = "FTPログインに失敗しました";
		return $ERROR;
	}
	$ftp->setPassive();
	$remote_time = remote_read_dir_time($ftp,$remote_dir);
	//	ftp切断
	$ftp->disconnect();


	//	ローカルサーバー
	if ($local_time) {
		$local_html = date("Y/m/d H:i:s",$local_time);
	} else {
		$local_html = "ファイルがアップロードされておりません。";
	}

	// -- リモートサーバー
	if ($remote_time) {
		$remote_html = date("Y/m/d H:i:s",$remote_time);
	} else {
		$remote_html = "ファイルがアップロードされておりません。";
	}

	if ($ERROR) {
		$html  = ERROR($ERROR);
		$html .= "<br>\n";
	}

	if ($local_time || $remote_time) {
		$submit_msg = $L_NAME['course_name']." / ".$L_NAME['stage_name']." / ".$L_NAME['lesson_name']." / ".$L_NAME['unit_name']." の学習FLASHファイルを本サーバーへアップしますがよろしいですか？";

		$html .= "学習FLASHファイルをアップする場合は、「アップする」ボタンを押してください。<br>\n";
		$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" name=\"del_form_all\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"update\">\n";
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"$_POST[course_num]\">\n";
		$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"$_POST[stage_num]\">\n";
		$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"$_POST[lesson_num]\">\n";
		$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"$_POST[unit_num]\">\n";
		$html .= "<input type=\"submit\" value=\"アップする\" onClick=\"return confirm('".$submit_msg."')\"><br>\n";
		$html .= "<table border=\"0\" cellspacing=\"1\" bgcolor=\"#666666\" cellpadding=\"3\">\n";
		$html .= "<tr bgcolor=\"#cccccc\">\n";
		$html .= "<th colspan=\"2\">".$L_NAME['course_name']."</th>\n";
		$html .= "</tr>\n";
		$html .= "<tr bgcolor=\"#cccccc\">\n";
		$html .= "<th>テストサーバー最新更新日</th>\n";
		$html .= "<th>本サーバー最新更新日</th>\n";
		$html .= "</tr>\n";
		$html .= "<tr valign=\"top\" bgcolor=\"#ffffff\" align=\"center\">\n";
		$html .= "<td>\n";
		$html .= $local_html;
		$html .= "</td>\n";
		$html .= "<td>\n";
		$html .= $remote_html;
		$html .= "</td>\n";
		$html .= "</table>\n";
		$html .= "<input type=\"submit\" value=\"アップする\" onClick=\"return confirm('".$submit_msg."')\"><br>\n";
		$html .= "</form>\n";
	} else {
		$html .= "学習FLASHファイルがアップロードされておりません。<br>\n";
	}

	return $html;
}


/**
 * 反映
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function update() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//	ファイルアップロード
	 require_once 'Net/FTP.php';
	//	ftp接続
	$ftp = new Net_FTP();
	$connect = $ftp->connect(FTP_HOST, FTP_PORT);
	$login = $ftp->login(FTP_USERID, FTP_PASSWORD);
	if (!$login) {
		$ERROR[] = "FTPログインに失敗しました";
		return $ERROR;
	}
	$ftp->setPassive();

	//	フォルダーネーム取得
	unset($UNIT_NUM);
	$sql  = "SELECT unit_num FROM ".T_UNIT.
			" WHERE state!='1' AND lesson_num='".$_POST['lesson_num']."';";
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$unit_num = $list['unit_num'];
			$UNIT_NUM[$unit_num] = $unit_num;
		}
	}

	//	体系図ファイル
	$dir_name = $_POST['unit_num'];
	$local_dir = MATERIAL_FLASH_DIR.$_POST['course_num']."/".$_POST['stage_num']."/".$_POST['lesson_num']."/";
	$remote_dir = REMOTE_MATERIAL_FLASH_DIR.$_POST['course_num']."/".$_POST['stage_num']."/".$_POST['lesson_num']."/";
	$ERROR = remote_set_dir_file($ftp,$dir_name,$UNIT_NUM,$local_dir,$remote_dir);

	//	ftp切断
	$ftp->disconnect();

	//	ログ保存 --
	$connect_db = new connect_db();
	$connect_db->set_db($L_CONTENTS_DB['92']);
	$ERROR = $connect_db->set_connect_db();

	$INSERT_DATA['update_mode'] = MODE;
	$INSERT_DATA['course_num'] = $_POST['course_num'];
	$INSERT_DATA['stage_num'] = $_POST['stage_num'];
	$INSERT_DATA['lesson_num'] = $_POST['lesson_num'];
	$INSERT_DATA['unit_num'] = $_POST['unit_num'];
	$INSERT_DATA['block_num'] = $_POST['block_num'];
	$INSERT_DATA['regist_time'] = "now()";
	$INSERT_DATA['state'] = 1;
	$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];

	$ERROR = $connect_db->insert('mate_upd_log',$INSERT_DATA);
	$connect_db->close();

	return $ERROR;
}


/**
 * 反映終了
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $L_NAME
 * @return string HTML
 */
function update_end_html($L_NAME) {

	$html  = $L_NAME['course_name']." / ".$L_NAME['stage_name']." / ".$L_NAME['lesson_name']." / ".$L_NAME['unit_name']."の学習FLASHファイルのアップが完了致しました。<br>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"$_POST[course_num]\">\n";
	$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"$_POST[stage_num]\">\n";
	$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"$_POST[lesson_num]\">\n";
	$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"$_POST[unit_num]\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"back\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\"><br>\n";
	$html .= "</form>\n";

	return $html;
}
?>