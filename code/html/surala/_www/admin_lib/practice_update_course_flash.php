<?PHP
/**
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
 * add okabe 2017/10/11 夜間バッチ異常終了対策用
 * ローカルサーバーファイルリスト作成
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $LOCAL_FILE_NAME
 * @param string $local_dir
 * @param string $add_dir
 * @return string HTML
 */
function local_file_make_list_private($LOCAL_FILE_NAME,$local_dir,$add_dir) {

	$result = 0;

	if ($LOCAL_FILE_NAME[f]) {
		@ksort($LOCAL_FILE_NAME["f"],SORT_STRING);
		foreach ($LOCAL_FILE_NAME["f"] AS $key => $val) {

			//空白でsplitして前半部分のファイルまたはディレクトリが存在するか確認(存在したら警告して操作不可にする)
			list($result_code, $before_one, $after_one) = txx_check_file_exist($local_dir, $add_dir, $key, "f");
			$result += $result_code;

			$html .= "<tr class=\"course_form_cell\">\n";
			$html .= "<td>".$before_one. $add_dir.$key. $after_one."</td>\n";
			$html .= "<td>".$before_one. date("Y/m/d H:i",$val). $after_one."</td>\n";
			$html .= "</tr>\n";
		}
	}

	if ($LOCAL_FILE_NAME[d]) {
		@ksort($LOCAL_FILE_NAME["d"],SORT_STRING);
		foreach ($LOCAL_FILE_NAME["d"] AS $key => $val) {

			//空白でsplitして前半部分のファイルまたはディレクトリが存在するか確認(存在したら警告して操作不可にする)
			list($result_code, $before_one, $after_one) = txx_check_file_exist($local_dir, $add_dir, $key, "d");
			$result += $result_code;

			if ($key == "") { continue; }
			$html .= "<tr class=\"course_form_cell\">\n";
			$html .= "<td>".$before_one. $add_dir.$key."/". $after_one."</td>\n";
			$html .= "<td>".$before_one. date("Y/m/d H:i",$val). $after_one."</td>\n";
			$html .= "</tr>\n";

			$next_local_dir = $local_dir.$add_dir.$key."/";
			$NEXT_LOCAL_FILE_NAME = local_read_dir($next_local_dir);
			list($html_tmp, $result_code) = local_file_make_list_private($NEXT_LOCAL_FILE_NAME,$local_dir,$add_dir.$key."/");
			$result += $result_code;
			$html .= $html_tmp;
		}
	}
	return array($html, $result);
}

// add okabe 2017/10/11 夜間バッチ異常終了対策用
function txx_check_file_exist($local_dir, $add_dir, $key, $modex) {
	$before_one = "";
	$after_one = "";
	$result_code = 0;

	$chk_spc = strpos($key, ' ');
	if ($chk_spc !== false) {	//空白が含まれている場合

		$tmp_pieces = explode(" ", $key);
		$chk_file_path = $local_dir.$add_dir.$tmp_pieces[0];

		$chk_fexist = file_exists($chk_file_path);
		if ($chk_fexist) {
			$before_one = "<span style=\"color:red;\">";
			$after_one = "</span>";
			$result_code = 1;
		}
	}
	return array($result_code, $before_one, $after_one);
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
	$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$_POST['stage_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"".$_POST['unit_num']."\">\n";
	$html .= select_web_menu();
	$html .= "</form>\n";

	//サーバー情報取得
	if (!$_SESSION['select_web']) { return $html; }

	//	ローカルサーバー
	unset($local_max);
	$local_dir = MATERIAL_FLASH_DIR.$_POST['course_num']."/".$_POST['stage_num']."/".
					$_POST['lesson_num']."/".$_POST['unit_num']."/";
	$LOCAL_FILE_NAME = local_read_dir($local_dir);

	$local_max = count($LOCAL_FILE_NAME);
	if ($local_max) {
		//	ローカルサーバーフォルダー内最新ファイル時間
		$local_time = local_read_dir_time($local_dir);

		$local_html .= "最新更新時間：".date("Y/m/d H:i:s",$local_time);	//	add :sookawara 2012/08/01
		$local_html .= "<table class=\"course_form\">\n";
		$local_html .= "<tr class=\"course_form_menu\">\n";
		$local_html .= "<th>ファイル名</th>\n";
		$local_html .= "<th>更新時間</th>\n";
		$local_html .= "</tr>\n";

		//	ローカルサーバーファイルリスト作成
		//$local_html .= local_file_make_list($LOCAL_FILE_NAME,$local_dir,"");	//del okabe 2017/10/11 夜間バッチ異常終了対策
		//add okabe start 2017/10/11 夜間バッチ異常終了対策
		list($local_html_tmp, $result_code) = local_file_make_list_private($LOCAL_FILE_NAME,$local_dir,"");
		$local_html .= $local_html_tmp;
		//add okabe end 2017/10/11 夜間バッチ異常終了対策

		$local_html .= "</table>\n";
	} else {
		$local_html = "ファイルがアップロードされておりません。";
	}


	// -- リモートサーバー
	unset($remote_max);
	$remote_dir = REMOTE_MATERIAL_FLASH_DIR.$_POST['course_num']."/".$_POST['stage_num']."/".
					$_POST['lesson_num']."/".$_POST['unit_num']."/";
	$FILE_NAME = remote_read_dir($_SESSION['select_web'],$remote_dir);
	$remote_max = count($FILE_NAME["f"]);
	if ($remote_max) {
		// -- リモートサーバーフォルダー内最新ファイル時間
		$remote_time = remote_read_dir_time($_SESSION['select_web'],$remote_dir);

		$remote_html .= "最新更新時間：".date("Y/m/d H:i:s",$remote_time);	//	add :sookawara 2012/08/01
		$remote_html .= "<table class=\"course_form\">\n";
		$remote_html .= "<tr class=\"course_form_menu\">\n";
		$remote_html .= "<th>ファイル名</th>\n";
		$remote_html .= "<th>更新時間</th>\n";
		$remote_html .= "</tr>\n";

		//	リモートサーバーファイルリスト作成
		$remote_html .= remote_file_make_list($_SESSION['select_web'],$FILE_NAME,$remote_dir,$add_dir);

		$remote_html .= "</table>\n";
	} else {
		$remote_html = "ファイルがアップロードされておりません。";
	}

	if ($ERROR) {
		$html  = ERROR($ERROR);
		$html .= "<br>\n";
	}

	if ($local_html || $remote_html) {
		$submit_msg = $L_NAME['course_name']." / ".$L_NAME['stage_name']." / ".$L_NAME['lesson_name']." / ".$L_NAME['unit_name']." の学習FLASHファイルを検証へアップしますがよろしいですか？";

		$html .= "学習FLASHファイルをアップする場合は、「アップする」ボタンを押してください。<br>\n";
		// add okabe start 2017/10/11 夜間バッチ異常終了対策
		if ($result_code > 0) {
			$html .= "<span style=\"color:red; font-weight:bold;\">不正なファイル名、またはフォルダ名が存在します。ご確認ください。</span>\n";
		}
		// add okabe end 2017/10/11 夜間バッチ異常終了対策
		$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" name=\"del_form_all\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"update\">\n";
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$_POST['stage_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"".$_POST['unit_num']."\">\n";
		//$html .= "<input type=\"submit\" value=\"アップする\" onClick=\"return confirm('".$submit_msg."')\"><br>\n";	//del okabe 2017/10/11 夜間バッチ異常終了対策
		// add okabe start 2017/10/11 夜間バッチ異常終了対策
		if ($result_code > 0) {
			$html .= "<input type=\"button\" value=\"アップする\" disabled><br>\n";
		} else {
			$html .= "<input type=\"submit\" value=\"アップする\" onClick=\"return confirm('".$submit_msg."')\"><br>\n";
		}
		// add okabe end 2017/10/11 夜間バッチ異常終了対策
		$html .= "<table border=\"0\" cellspacing=\"1\" bgcolor=\"#666666\" cellpadding=\"3\">\n";
		$html .= "<tr bgcolor=\"#cccccc\">\n";
		$html .= "<th colspan=\"2\">".$L_NAME['course_name']."</th>\n";
		$html .= "</tr>\n";
		$html .= "<tr bgcolor=\"#cccccc\">\n";
		$html .= "<th>テストサーバー</th>\n";
		$html .= "<th>".$_SESSION['select_web']['NAME']."</th>\n";
		$html .= "</tr>\n";
		$html .= "<tr valign=\"top\" bgcolor=\"#ffffff\" align=\"center\">\n";
		$html .= "<td>\n";
		$html .= $local_html;
		$html .= "</td>\n";
		$html .= "<td>\n";
		$html .= $remote_html;
		$html .= "</td>\n";
		$html .= "</table>\n";
		//$html .= "<input type=\"submit\" value=\"アップする\" onClick=\"return confirm('".$submit_msg."')\"><br>\n";	//del okabe 2017/10/11 夜間バッチ異常終了対策
		// add okabe start 2017/10/11 夜間バッチ異常終了対策
		if ($result_code > 0) {
			$html .= "<input type=\"button\" value=\"アップする\" disabled><br>\n";
		} else {
			$html .= "<input type=\"submit\" value=\"アップする\" onClick=\"return confirm('".$submit_msg."')\"><br>\n";
		}
		// add okabe end 2017/10/11 夜間バッチ異常終了対策
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

	if ($_POST['course_num'] < 1 || $_POST['stage_num'] < 1 || $_POST['lesson_num'] < 1 || $_POST['unit_num'] < 1) {
		$ERROR[] = "更新するコース情報が取得できません。";
		return $ERROR;
	}

	//	FLASHファイルアップロード
	// >>> add 2018/05/22 yoshizawa レクチャーアップ改修
	$remove_dir = KBAT_DIR.REMOTE_MATERIAL_FLASH_DIR.$_POST['course_num']."/".$_POST['stage_num']."/".$_POST['lesson_num']."/".$_POST['unit_num'];
	// <<<
	$local_dir = BASE_DIR."/www".ereg_replace("^../","/",MATERIAL_FLASH_DIR).$_POST['course_num']."/".$_POST['stage_num']."/".$_POST['lesson_num']."/".$_POST['unit_num'];
	$remote_dir = KBAT_DIR.REMOTE_MATERIAL_FLASH_DIR.$_POST['course_num']."/".$_POST['stage_num']."/".$_POST['lesson_num']."/";

	// >>> add 2018/05/22 yoshizawa レクチャーアップ改修
	$remove_command = "rm -rf ".$remove_dir;
	// <<<
	//$command1 = "ssh suralacore01@srlbtw21 mkdir -p ".$remote_dir; // del 2018/03/20 yoshizawa AWSプラクティスアップデート
	//$command2 = "scp -rp ".$local_dir." suralacore01@srlbtw21:".$remote_dir; // del 2018/03/20 yoshizawa AWSプラクティスアップデート
	$command1 = "mkdir -p ".$remote_dir; // add 2018/03/20 yoshizawa AWSプラクティスアップデート
	$command2 = "cp -rp ".$local_dir." ".$remote_dir; // add 2018/03/20 yoshizawa AWSプラクティスアップデート

	// >>> add 2018/05/22 yoshizawa レクチャーアップ改修
	// 反映先のレクチャーを削除してからローカルのレクチャーをアップする。
	exec("$remove_command",$LIST);
	// <<<
	//upd start 2017/11/24 yamaguchi AWS移設
	//exec("$command1",&$LIST);
	//exec("$command2",&$LIST);
	exec("$command1",$LIST);
	exec("$command2",$LIST);
	//upd end 2017/11/24 yamaguchi


	//	検証バッチから検証webへ
	// $command = "ssh suralacore01@srlbtw21 ./CONTENTSUP.cgi '2' '".MODE."' '".$_POST['course_num']."' '".$_POST['stage_num']."' '".$_POST['lesson_num']."' '".$_POST['unit_num']."'"; // del 2018/03/20 yoshizawa AWSプラクティスアップデート
	$command = "/usr/bin/php ".BASE_DIR."/_www/batch/CONTENTSUP.cgi '2' '".MODE."' '".$_POST['course_num']."' '".$_POST['stage_num']."' '".$_POST['lesson_num']."' '".$_POST['unit_num']."'"; // add 2018/03/20 yoshizawa AWSプラクティスアップデート

	//upd start 2017/11/24 yamaguchi AWS移設
	//exec($command,$LIST);
	exec($command,$LIST);
	//upd end 2017/11/24 yamaguchi


	//	ログ保存 --
	unset($mate_upd_log_num);
	$sql  = "SELECT mate_upd_log_num FROM mate_upd_log".
			" WHERE update_mode='".MODE."'".
			" AND course_num='".$_POST['course_num']."'".
			" AND stage_num='".$_POST['stage_num']."'".
			" AND lesson_num='".$_POST['lesson_num']."'".
			" AND unit_num='".$_POST['unit_num']."'".
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

	$html  = $L_NAME['course_name']." / ".$L_NAME['stage_name']." / ".$L_NAME['lesson_name']." / ".$L_NAME['unit_name']."の学習FLASHファイルのアップが完了致しました。<br>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$_POST['stage_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"".$_POST['unit_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"back\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\"><br>\n";
	$html .= "</form>\n";

	return $html;
}
?>