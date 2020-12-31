<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * プラクティスステージ管理　プラクティスアップデートプログラム
 * 	サブプログラム	数式パレットファイルアップデート
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
	} elseif (ACTION == "web_session") {
		$ERROR = select_web();
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

	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" name=\"del_form_all\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"".$_POST['mode']."\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"web_session\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
	$html .= select_web_menu();
	$html .= "</form>\n";

	//サーバー情報取得
	if (!$_SESSION['select_web']) { return $html; }

	// -- リモートサーバーフォルダー内最新ファイル時間

	//	ローカルサーバー
	unset($local_max);
	$local_dir = MATERIAL_PALETTE_DIR.$_POST['course_num']."/";
	$LOCAL_FILE_NAME = local_read_dir($local_dir);
	$local_max = count($LOCAL_FILE_NAME["f"]);
	if ($local_max) {
		$LOCAL_TIME = $LOCAL_FILE_NAME;
		@rsort($LOCAL_TIME[f]);
		$local_new_time = $LOCAL_TIME[f][0];
		$local_html .= "最新更新時間：".date("Y/m/d H:i:s",$local_new_time);	//	add :sookawara 2012/08/01
		$local_html .= "<table class=\"course_form\">\n";
		$local_html .= "<tr class=\"course_form_menu\">\n";
		$local_html .= "<th>ファイル名</th>\n";
		$local_html .= "<th>更新時間</th>\n";
		$local_html .= "</tr>\n";
		@ksort($LOCAL_FILE_NAME["f"],SORT_STRING);
		foreach ($LOCAL_FILE_NAME["f"] AS $key => $val) {
			$local_html .= "<tr class=\"course_form_cell\">\n";
			$local_html .= "<td>".$key."</td>\n";
			$local_html .= "<td>".date("Y/m/d H:i:s",$val)."</td>\n";	//	add :sookawara 2012/08/01
			$local_html .= "</tr>\n";
		}
		$local_html .= "</table>\n";
	} else {
		$local_html = "ファイルがアップロードされておりません。";
	}

	// -- リモートサーバー
	unset($remote_max);
	$remote_dir = REMOTE_MATERIAL_PALETTE_DIR;
	$FILE_NAME = remote_read_dir($_SESSION['select_web'],$remote_dir);
	$remote_max = count($FILE_NAME["f"]);
	if ($remote_max) {
		$REMOTE_TIME = $FILE_NAME;
		@rsort($REMOTE_TIME[f]);
		$remote_new_time = $REMOTE_TIME[f][0];
		$remote_html .= "最新更新時間：".date("Y/m/d H:i:s",$remote_new_time);	//	add :sookawara 2012/08/01
		$remote_html .= "<table class=\"course_form\">\n";
		$remote_html .= "<tr class=\"course_form_menu\">\n";
		$remote_html .= "<th>ファイル名</th>\n";
		$remote_html .= "<th>更新時間</th>\n";
		$remote_html .= "</tr>\n";
		@ksort($FILE_NAME["f"],SORT_STRING);
		foreach ($FILE_NAME["f"] AS $key => $val) {
			$remote_html .= "<tr class=\"course_form_cell\">\n";
			$remote_html .= "<td>".$key."</td>\n";
			$remote_html .= "<td>".date("Y/m/d H:i:s",$val)."</td>\n";	//	add :sookawara 2012/08/01
			$remote_html .= "</tr>\n";
		}
		$remote_html .= "</table>\n";
	} else {
		$remote_html = "ファイルがアップロードされておりません。";
	}

	if ($ERROR) {
		$html  = ERROR($ERROR);
		$html .= "<br>\n";
	}

	if ($local_html || $remote_html) {
		$submit_msg = "数式パレットファイルを検証へアップしますがよろしいですか？";

		$html .= "数式パレットファイルをアップする場合は、「アップする」ボタンを押してください。<br>\n";
		$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" name=\"del_form_all\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"update\">\n";
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"$_POST[course_num]\">\n";
		$html .= "<input type=\"submit\" value=\"アップする\" onClick=\"return confirm('".$submit_msg."')\"><br>\n";
		$html .= "<table border=\"0\" cellspacing=\"1\" bgcolor=\"#666666\" cellpadding=\"3\">\n";
		$html .= "<tr bgcolor=\"#cccccc\">\n";
		$html .= "<th>テストサーバー最新更新日</th>\n";
		$html .= "<th>".$_SESSION['select_web']['NAME']."最新更新日</th>\n";
		$html .= "</tr>\n";
		$html .= "<tr valign=\"top\" bgcolor=\"#ffffff\">\n";
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
		$html .= "数式パレットファイルがアップロードされておりません。<br>\n";
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

	//	数式パレットファイルアップロード
	$local_dir = BASE_DIR."/www".ereg_replace("^../","/",MATERIAL_PALETTE_DIR);
	$remote_dir = KBAT_DIR.REMOTE_MATERIAL_PALETTE_DIR."../";

	//$command1 = "ssh suralacore01@srlbtw21 mkdir -p ".$remote_dir; // del 2018/03/20 yoshizawa AWSプラクティスアップデート
	//$command2 = "scp -rp ".$local_dir." suralacore01@srlbtw21:".$remote_dir; // del 2018/03/20 yoshizawa AWSプラクティスアップデート
	$command1 = "mkdir -p ".$remote_dir; // add 2018/03/20 yoshizawa AWSプラクティスアップデート
	$command2 = "cp -rp ".$local_dir." ".$remote_dir; // add 2018/03/20 yoshizawa AWSプラクティスアップデート

	//upd start 2017/11/27 yamaguchi AWS移設
	//exec("$command1",&$LIST);
	//exec("$command2",&$LIST);
	exec("$command1",$LIST);
	exec("$command2",$LIST);
	//upd end 2017/11/27 yamaguchi


	//	検証バッチから検証webへ
	// $command = "ssh suralacore01@srlbtw21 ./CONTENTSUP.cgi '2' '".MODE."'"; // del 2018/03/20 yoshizawa AWSプラクティスアップデート
	$command = "/usr/bin/php ".BASE_DIR."/_www/batch/CONTENTSUP.cgi '2' '".MODE."'"; // add 2018/03/20 yoshizawa AWSプラクティスアップデート

	//upd start 2017/11/27 yamaguchi AWS移設
	//exec($command,&$LIST);
	exec($command,$LIST);
	//upd end 2017/11/27 yamaguchi


	//	ログ保存 --
	unset($mate_upd_log_num);
	$sql  = "SELECT mate_upd_log_num FROM mate_upd_log".
			" WHERE update_mode='".MODE."'".
			" AND state='1'".
			" ORDER BY regist_time DESC LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$mate_upd_log_num = $list['mate_upd_log_num'];
	}

	if ($mate_upd_log_num) {
		unset($INSERT_DATA);
		$INSERT_DATA['regist_time'] = "now()";
		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
		$where = " WHERE mate_upd_log_num='".$mate_upd_log_num."'";

		$ERROR = $cdb->update('mate_upd_log',$INSERT_DATA,$where);
	} else {
		unset($INSERT_DATA);
		$INSERT_DATA['update_mode'] = MODE;
		$INSERT_DATA['course_num'] = $_POST['course_num'];
		$INSERT_DATA['stage_num'] = $_POST['stage_num'];
		$INSERT_DATA['lesson_num'] = $_POST['lesson_num'];
		$INSERT_DATA['unit_num'] = $_POST['unit_num'];
		$INSERT_DATA['block_num'] = $_POST['block_num'];
		$INSERT_DATA['regist_time'] = "now()";
		$INSERT_DATA['state'] = 1;
		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];

		$ERROR = $cdb->insert('mate_upd_log',$INSERT_DATA);
	}

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

	$html  = "数式パレットファイルのアップが完了致しました。<br>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"$_POST[course_num]\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"back\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\"><br>\n";
	$html .= "</form>\n";

	return $html;
}
?>