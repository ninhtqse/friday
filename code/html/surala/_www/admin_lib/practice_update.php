<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * プラクティスステージ管理　プラクティスアップデートプログラム
 *
 * @author Azet
 */

/**
 * HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function start() {
	global $L_UPDATE_MODE;

	list($html,$L_NAME) = select_course();

	$check_position   = "c".$_POST['course_num']."s".$_POST['stage_num'].
						"l".$_POST['lesson_num']."u".$_POST['unit_num']."b".$_POST['block_num'];
	if (defined("MODE") && $_SESSION[CHECK_POSITION] != $check_position) {
		unset($set_filename);
	} elseif (defined("MODE") && $L_UPDATE_MODE) {
		foreach ($L_UPDATE_MODE AS $type => $VAL) {
			if (is_array($VAL)) {
				foreach ($VAL AS $key => $val) {
					if (ereg("^$key",MODE)) {
						$set_filename = $key;
						break;
					}
				}
			}
			if ($set_filename) { break; }
		}
	}

	if ($set_filename) {
		$include_file = LOG_DIR . "admin_lib/practice_update_".MODE.".php";
	}

	if ($include_file && file_exists($include_file)) {
		include($include_file);
		$html .= sub_start($L_NAME);
	}
	$_SESSION[CHECK_POSITION] = $check_position;

	return $html;
}


/**
 * コース選択
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array
 */
function select_course() {

	global $L_UNIT_TYPE;	//	add ookawara 2012/11/08

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql  = "SELECT course_num,course_name FROM " . T_COURSE .
			" WHERE state!='1' ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
	}
	if (!$max) {
		$html .= "コースが設定されておりません。<br>\n";
		return $html;
	}
	if (!$_POST['course_num']) { $selected = "selected"; } else { $selected = ""; }
	$course_num_html .= "<option value=\"\" $selected>----------</option>\n";
	while ($list = $cdb->fetch_assoc($result)) {
		// >>> del 2018/11/15 yoshizawa 漢字学習コンテンツ ラジオボタン側で制御する様に変更。
		// if($list['course_num'] == 17){ continue; } //[TODO!!] add kimura 2018/10/26 漢字学習コンテンツ_書写ドリル対応 //漢字コンテンツは表示しない
		// <<<
		if ($_POST['course_num'] == $list['course_num']) { $selected = "selected"; } else { $selected = ""; }
		$course_num_html .= "<option value=\"".$list['course_num']."\" $selected>".$list['course_name']."</option>\n";
		if ($selected) { $L_NAME[course_name] = $list['course_name']; }
	}

	if (!$_POST['stage_num']) { $selected = "selected"; } else { $selected = ""; }
	$stage_num_html .= "<option value=\"\" $selected>----------</option>\n";
	$sql  = "SELECT stage_num,stage_name FROM " . T_STAGE .
			" WHERE state!='1' AND course_num='".$_POST['course_num']."' ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
		while ($list = $cdb->fetch_assoc($result)) {
			if ($_POST['stage_num'] == $list['stage_num']) { $selected = "selected"; } else { $selected = ""; }
			$stage_num_html .= "<option value=\"".$list['stage_num']."\" $selected>".$list['stage_name']."</option>\n";
			if ($selected) { $L_NAME[stage_name] = $list['stage_name']; }
		}
	}
	if ($_POST['course_num'] && !$max) { $msg .= "ステージが設定されておりません。<br>\n"; }

	if (!$_POST['lesson_num']) { $selected = "selected"; } else { $selected = ""; }
	$lesson_num_html .= "<option value=\"\" $selected>----------</option>\n";
	$sql = "SELECT lesson_num,lesson_name FROM " . T_LESSON .
		" WHERE state!='1' AND stage_num='".$_POST['stage_num']."' ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
		while ($list = $cdb->fetch_assoc($result)) {
			if ($_POST['lesson_num'] == $list['lesson_num']) { $selected = "selected"; } else { $selected = ""; }
			$lesson_num_html .= "<option value=\"".$list['lesson_num']."\" $selected>".$list['lesson_name']."</option>\n";
			if ($selected) { $L_NAME[lesson_name] = $list['lesson_name']; }
		}
	}
	if ($_POST['stage_num'] && !$max) { $msg .= "Lessonが設定されておりません。<br>\n"; }

	if (!$_POST['unit_num']) { $selected = "selected"; } else { $selected = ""; }
	$unit_html .= "<option value=\"\" $selected>----------</option>\n";
	$sql = "SELECT unit_num,unit_name FROM " . T_UNIT .
		" WHERE state!='1' AND lesson_num='".$_POST['lesson_num']."' ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
		while ($list = $cdb->fetch_assoc($result)) {
			if ($_POST['unit_num'] == $list['unit_num']) { $selected = "selected"; } else { $selected = ""; }
			$unit_html .= "<option value=\"".$list['unit_num']."\" $selected>".$list['unit_name']."</option>\n";
			if ($selected) { $L_NAME[unit_name] = $list['unit_name']; }
		}
	}
	if ($_POST['lesson_num'] && !$max) { $msg .= "ユニットが設定されておりません。<br>\n"; }

	if (!$_POST['block_num']) { $selected = "selected"; } else { $selected = ""; }
	$block_html = "<option value=\"\" $selected>----------</option>\n";
	$sql = "SELECT block_num, block_type, display FROM " . T_BLOCK .
		" WHERE state!='1' AND unit_num='".$_POST['unit_num']."' ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
		while ($list = $cdb->fetch_assoc($result)) {
			if ($_POST['block_num'] == $list['block_num']) { $selected = "selected"; } else { $selected = ""; }

			//	add ookawara 2012/11/08 start
			$block_type = $list['block_type'];
			$block_name = $L_UNIT_TYPE[$block_type];
			//	add ookawara 2012/11/08 end

			if ($selected) { define("block_type",$list['block_type']); }
			if ($list['display'] == "2") { $block_name .= "(非表示)"; }
			$block_html .= "<option value=\"".$list['block_num']."\" $selected>".$block_name."</option>\n";
			if ($selected) { $L_NAME['block_name'] = $block_name; }
		}
	}
	//「ブロック」から「ドリル」へ表記統一　2009/11/02 hirano
	//if ($_POST['unit_num'] && !$max) { $msg .= "ドリルが設定されておりません。<br>\n"; }	//	del ookawara 2014/12/16

	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" name=\"menu\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"set_course\">\n";
	$html .= "<table class=\"unit_form\">\n";
	$html .= "<tr class=\"unit_form_cell\">\n";
	$html .= "<td class=\"stage_form_menu\">コース</td>\n";
	$html .= "<td class=\"stage_form_menu\">ステージ</td>\n";
	$html .= "<td class=\"stage_form_menu\">Lesson</td>\n";
	$html .= "<td class=\"stage_form_menu\">ユニット</td>\n";
// 「ブロック」から「ドリル」へ表記統一　2009/11/02 hirano
	$html .= "<td class=\"stage_form_menu\">ドリル</td>\n";
	$html .= "</tr>\n";
	$html .= "<tr class=\"stage_form_cell\">\n";
	$html .= "<td>\n";
	$html .= "<select name=\"course_num\" onchange=\"submit_course()\">\n";
	$html .= $course_num_html;
	$html .= "</select>\n";
	$html .= "</td>\n";
	$html .= "<td>\n";
	$html .= "<select name=\"stage_num\" onchange=\"submit_stage()\">\n";
	$html .= $stage_num_html;
	$html .= "</select>\n";
	$html .= "</td>\n";
	$html .= "<td><select name=\"lesson_num\" onchange=\"submit_lesson()\">\n";
	$html .= $lesson_num_html;
	$html .= "</select></td>\n";
	$html .= "<td>\n";
	$html .= "<select name=\"unit_num\" onchange=\"submit_unit()\">\n";
	$html .= $unit_html;
	$html .= "</select>\n";
	$html .= "</td>\n";
	$html .= "<td>\n";
	$html .= "<select name=\"block_num\" onchange=\"submit()\">\n";
	$html .= $block_html;
	$html .= "</select>\n";
	$html .= "</td>\n";
	$html .= "</tr>\n";
	$html .= "</table>\n";
	$html .= "<br style=\"clear:left;\">\n";
	if ($msg) {
		$html .= $msg;
	} else {
		$html .= select_mode();
	}
	$html .= "</form>\n";

	return array($html,$L_NAME);
}

/**
 * モード選択機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function select_mode() {

	global $L_UPDATE_MODE;

	// >>> add start 2018/11/15 yoshizawa  漢字学習コンテンツ すらら英単語
	$service_num = get_service_num($_POST['course_num']);
	// <<<

	if ($_POST['block_num']) {
		$select_mode = "problem";
	} elseif ($_POST['unit_num']) {
		$select_mode = "block";
	} elseif ($_POST['lesson_num']) {
		$select_mode = "unit";
	} elseif ($_POST['stage_num']) {
		$select_mode = "lesson";
	} elseif ($_POST['course_num']) {
		$select_mode = "stage";
	} else {
		$select_mode = "course";
	}

	$count = 0;
	if ($L_UPDATE_MODE[$select_mode]) {
		$count = count($L_UPDATE_MODE[$select_mode]);
		if ($count > 1) { $colspan = "colspan=\"$count\""; }
	} else {
		return $html;
	}

	$html .= "<table class=\"unit_form\">\n";
	$html .= "<tr class=\"unit_form_cell\">\n";
	$html .= "<td class=\"stage_form_menu\" $colspan>アップデートコンテンツ</td>\n";
	$html .= "</tr>\n";
	$html .= "<tr class=\"unit_form_cell\">\n";
	$i = 1;	//	add ookawara 2012/12/10
	$length = 5;	//	add ookawara 2012/12/10
	$cnt_data = count($L_UPDATE_MODE[$select_mode]);
	foreach ($L_UPDATE_MODE[$select_mode] AS $key => $val) {
		//	add ookawara 2012/12/10 start
		if ($i > $length) {
			$html .= "</tr>\n";
			$html .= "<tr class=\"unit_form_cell\">\n";
			$i = 1;
		}
		//	add ookawara 2012/12/10 end
		if (MODE == $key) { $checked = "checked"; } else { $checked = ""; }
		$html .= "<td>\n";
		// update start hasegawa 2018/10/17 理社レクチャーサンプル
		// $html .= "<input type=\"radio\" name=\"mode\" value=\"$key\" onclick=\"submit()\" $checked>：$val\n";
		// del start oda 2020/02/27 理社対応
// 		// 理社の場合レクチャー以外のアップは許可しない
// 		$disable = '';
// 		if (($_POST['course_num'] == 15 || $_POST['course_num'] == 16) && !($key == 'course_flash' || $key == 'course_block' || $key == 'problem')) {
// 			$checked = '';
// 			$disable=' disabled';
// 		}
		// del end oda 2020/02/27 理社対応

        // del start 2019/04/02 yoshizawa プラクティスアップデートを開放します。
		// // add start 2018/11/15 yoshizawa  漢字学習コンテンツ すらら英単語
		// // 漢字学習コンテンツサービスのコースの場合
		// if($service_num == 15){
		// 	$checked = '';
		// 	$disable=' disabled';
		// }
		// // すらら英単語サービスのコースの場合
		// if($service_num == 16){
		// 	$checked = '';
		// 	$disable=' disabled';
		// }
		// // add end 2018/11/15 yoshizawa  漢字学習コンテンツ すらら英単語
        // del end 2019/04/02 yoshizawa プラクティスアップデートを開放します。

		$html .= "<input type=\"radio\" name=\"mode\" value=\"$key\" onclick=\"submit()\" $checked$disable>：$val\n";
		// update end hasegawa 2018/10/17
		$html .= "</td>\n";
		$i++;	//	add ookawara 2012/12/10
	}
	//	add ookawara 2012/12/10 start
	$amari = ($i - 1) % $length;
	if ($amari > 0 && $cnt_data > $length) {
		$num = $length - $amari;
		for ($i=1; $i<=$num; $i++) {
			$html .= "<td>&nbsp;</td>\n";
		}
	}
	//	add ookawara 2012/12/10 end

	// del start oda 2020/02/27 理社対応
// 	// add start 2018/11/15 yoshizawa 理社レクチャーサンプル
// 	$message = "";
// 	if (($_POST['course_num'] == 15 || $_POST['course_num'] == 16) && !($key == 'course_flash' || $key == 'course_block' || $key == 'problem')) {
// 		$message  = "理科コースと社会コースのコンテンツはサービス開始前のため、運用中のサービスに影響が出ない様にプラクティスアップデートを選択不可といたします。<br>";
// 		$message .= "※[学習FLASH情報]はレクチャーサンプルページの公開に伴って解放しております。<br>";
// 		$message .= "※2019/10/01 [ドリル基本情報][問題情報]を追加で開放いたしました。<br>";
// 	}
// 	// add end 2018/11/15 yoshizawa 理社レクチャーサンプル
	// del end oda 2020/02/27

    // del start 2019/04/02 yoshizawa プラクティスアップデートを開放します。
	// // add start 2018/11/15 yoshizawa  漢字学習コンテンツ すらら英単語
	// // 漢字学習コンテンツサービスのコースの場合
	// if($service_num == 15){
	// 	$message  = "漢字学習コンテンツはサービス開始前のため、運用中のサービスに影響の出ない様にプラクティスアップデートを選択不可といたします。";
	// }
	// // すらら英単語サービスのコースの場合
	// if($service_num == 16){
	// 	$message  = "すらら英単語はサービス開始前のため、運用中のサービスに影響の出ない様にプラクティスアップデートを選択不可といたします。";
	// }
	// // add end 2018/11/15 yoshizawa  漢字学習コンテンツ すらら英単語
    // del end 2019/04/02 yoshizawa プラクティスアップデートを開放します。

	$html .= "</tr>\n";
	$html .= "</table>\n";
// 	// add start 2018/11/15 yoshizawa 理社レクチャーサンプル
// 	$html .= "<p style=\"color:red; font-weight:bold;\">";
// 	$html .= $message;
// 	$html .= "</p>";
// 	// add end 2018/11/15 yoshizawa 理社レクチャーサンプル
	$html .= "<br style=\"clear:left;\">\n";

	return $html;

}



/**
 * 閲覧DB選択メニュー
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function select_db_menu() {
	global $L_CONTENTS_DB;

	foreach ($L_CONTENTS_DB as $key => $val){
		if ($_SESSION['select_db'] == $val) { $sel = " selected"; } else { $sel = ""; }
		$s_db_html .= "<option value=\"".$key."\"$sel>".$val['NAME']."</option>\n";
	}

	$html = "閲覧DB：<select name=\"s_db_sel\" onchange=\"submit();\">\n";
	$html .= $s_db_html;
	$html .= "</select>\n";

	return $html;
}



/**
 * 閲覧DB設定
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function select_database() {
	global $L_CONTENTS_DB;

	if (strlen($_POST['s_db_sel'])) { $_SESSION['select_db'] = $L_CONTENTS_DB[$_POST['s_db_sel']]; }

	if ($_POST['s_db_sel'] == '0') { unset($_SESSION['select_db']); }

	return;
}



/**
 * 閲覧web選択メニュー
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function select_web_menu() {
	global $L_CONTENTS_WEB;

	foreach ($L_CONTENTS_WEB as $key => $val){
		if ($_SESSION['select_web'] == $val) { $sel = " selected"; } else { $sel = ""; }
		$s_web_html .= "<option value=\"".$key."\"$sel>".$val['NAME']."</option>\n";
	}

	$html = "閲覧Web：<select name=\"s_web_sel\" onchange=\"submit();\">\n";
	$html .= $s_web_html;
	$html .= "</select>\n";

	return $html;
}


/**
 * 閲覧web設定
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function select_web() {
	global $L_CONTENTS_WEB;

	if (strlen($_POST['s_web_sel'])) { $_SESSION['select_web'] = $L_CONTENTS_WEB[$_POST['s_web_sel']]; }

	if ($_POST['s_web_sel'] == '0') { unset($_SESSION['select_web']); }

	return;
}



/**
 * 閲覧webサーバーフォルダー内情報読み込み
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $select_web
 * @param string $dir
 * @return array
 */
function remote_read_dir($select_web,$dir) {
	global $L_CONTENTS_WEB;	//	add ookawara 2012/08/01

	unset($LIST);
	$server_name = $select_web['SERVERNAME'];
	$dir_type = $select_web['DIRTYPE'];
	// update start 2018/04/05 yoshizawa AWS対応
	// if ($dir_type == "2") { // リリースフォルダー
	// 	$set_dir = KBAT_DIR.$dir;
	// 	//upd start 2017/11/24 yamaguchi AWS移設
	// 	//exec("ssh suralacore01@srlbtw21 ./ACREADDIR2 '".$set_dir."'",&$LIST);	//	update ACREADDIR→ACREADDIR2 ookawara 2012/08/01
	// 	exec("ssh suralacore01@srlbtw21 ./ACREADDIR2 '".$set_dir."'",$LIST);
	// 	//upd end 2017/11/24 yamaguchi
	// } elseif ($dir_type == "3") { // バッチフォルダー本番
	// 	$set_dir = HBAT_DIR.$dir;
	// 	//upd start 2017/11/24 yamaguchi AWS移設
	// 	//exec("ssh suralacore01@srlbtw21 ssh suralacore01@".$server_name." ./ACREADDIR2 '".$set_dir."'",&$LIST);	//	update ACREADDIR→ACREADDIR2 ookawara 2012/08/01
	// 	exec("ssh suralacore01@srlbtw21 ssh suralacore01@".$server_name." ./ACREADDIR2 '".$set_dir."'",$LIST);
	// 	//upd end 2017/11/24 yamaguchi
	// } else {
	// 	$set_dir = REMOTE_BASE_DIR.$dir;
	// 	//upd start 2017/11/24 yamaguchi AWS移設
	// 	//exec("ssh suralacore01@srlbtw21 ssh suralacore01@".$server_name." ./ACREADDIR2 '".$set_dir."'",&$LIST);	//	update ACREADDIR→ACREADDIR2 ookawara 2012/08/01
	// 	exec("ssh suralacore01@srlbtw21 ssh suralacore01@".$server_name." ./ACREADDIR2 '".$set_dir."'",$LIST);
	// 	//upd end 2017/11/24 yamaguchi
	// }
	//
	if ($dir_type == "2") { // リリースフォルダー
		$set_dir = KBAT_DIR.$dir;
		exec("/data/home/_www/batch/ACREADDIR2 '".$set_dir."'",$LIST);
	} elseif ($dir_type == "3") { // バッチフォルダー本番 // 未使用
		$set_dir = HBAT_DIR.$dir;
		exec("/data/home/_www/batch/ACREADDIR2 '".$set_dir."'",$LIST);
	} else { // リモートサーバー
		$set_dir = REMOTE_BASE_DIR.$dir;
		exec("ssh suralacore01@".$server_name." /data/home/_www/batch/ACREADDIR2 '".$set_dir."'",$LIST);
	}
	// update end 2018/04/05 yoshizawa AWS対応

	//	add ookawara 2012/08/01 start
	mb_convert_variables("UTF-8", "sjis-win", $LIST);
//pre($LIST);
	if ($LIST) {
		$hiku_time = 0;
		if ($L_CONTENTS_WEB['TIME'] != "0") {
			$hiku_time = $L_CONTENTS_WEB['TIME'] * 60 * 60;
		}
		foreach ($LIST AS $LINE) {
			$LINE = preg_replace("/\s\s+/"," ",$LINE);
			$DATA_LIST = explode(" ",$LINE);

			$file_num = $DATA_LIST[1];
			$file_type = "f";
			if ($file_num > 1) {
				$file_type = "d";
			}

			$file_name = $DATA_LIST[8];

			if ($DATA_LIST[9]) {
				$file_name .= $DATA_LIST[9];
			}
			if (preg_match("/\*$/", $file_name) || preg_match("/\@$/", $file_name)) {
				continue;
			}
			if (!$file_name) {
				continue;
			}
			if ($file_type == "d") {
				$file_name = preg_replace("/\/$/", "", $file_name);
			}

			$file_time_day = $DATA_LIST[5];
			$file_time_hour = $DATA_LIST[6];
			list($year, $mon, $day) = split("-", $file_time_day);
			list($hour, $min, $sec) = split(":", $file_time_hour);

			$file_stamp = mktime($hour, $min, $sec, $mon, $day, $year) - $hiku_time;

			$FILE_NAME[$file_type][$file_name] = $file_stamp;
		}
	}
	//	add ookawara 2012/08/01 end
//pre($FILE_NAME);

	return $FILE_NAME;
}



/**
 * ローカルサーバーフォルダー内情報読み込み
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param string $dir
 * @return array
 */
function local_read_dir($dir) {

	if (file_exists($dir)) {
		$strDir = opendir($dir);
		while($strFle=readdir($strDir)) {
			if (ereg("^\.$|^\.\.$",$strFle)) { continue; }
			$file_name = $strFle;
			$file_type = filetype($dir.$strFle);
			if ($file_type == "file") {
				$file_type = "f";
			} elseif ($file_type == "dir") {
				$file_type = "d";
			}
			$file_stamp = filemtime($dir.$strFle);
			$file_name = mb_convert_encoding($file_name,"UTF-8","sjis-win");
			$LOCAL_FILE_NAME[$file_type][$file_name] = $file_stamp;
		}
	}

	return $LOCAL_FILE_NAME;
}


/**
 * リモートサーバーフォルダー内最新ファイル時間
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param string $select_web
 * @param string $dir
 * @return string
 */
function remote_read_dir_time($select_web,$dir) {

	$FILE_NAME = remote_read_dir($select_web,$dir);


	if ($FILE_NAME['f']) {
		foreach ($FILE_NAME['f'] AS $key => $val) {
			if ($remote_time < $val) { $remote_time = $val; }
		}
	}

	if ($FILE_NAME['d']) {
		foreach ($FILE_NAME['d'] AS $key => $val) {
			if ($key == "") { continue; }
			$sub_dir = $dir.$key."/";
			$sub_remote_time = remote_read_dir_time($select_web,$sub_dir);

			// トップの階層から最下層までのタイムスタンプを比較してより新しい更新日を保持する。
			if ($remote_time < $sub_remote_time) { 	$remote_time = $sub_remote_time; }	// add 2018/09/18 yoshizawa 課題要望No678

		}
		// >>> del 2018/09/18 yoshizawa 課題要望No678
		// TOP直下のディレクトリをソート順にチェックして、最後のディレクトリ以下のタイムスタンプしか考慮していないので、比較チェックをループ内で行う
		// if ($remote_time < $sub_remote_time) { $remote_time = $sub_remote_time; }
		// <<<
	}


	return $remote_time;
}


// add start 2017/01/10 yoshizawa 低学年2次対応
/**
 * リモートサーバーフォルダー内最新ファイル時間をlsコマンドで取得
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param string $select_web
 * @param string $dir
 * @return string
 */
function remote_read_dir_time_ls($select_web,$dir) {

	global $L_CONTENTS_WEB;

	unset($LIST);
	$server_name = $select_web['SERVERNAME'];
	$dir_type = $select_web['DIRTYPE'];
	if ($dir_type == "2") {
		$set_dir = KBAT_DIR.$dir;
	} elseif ($dir_type == "3") {
		$set_dir = HBAT_DIR.$dir;
	} else {
		$set_dir = REMOTE_BASE_DIR.$dir;
	}
	// $command = "ssh suralacore01@srlbtw21 ssh suralacore01@".$server_name." ls -lpR --time-style=full-iso '".$set_dir."'"; // del 2018/04/05 yoshizawa AWS対応
	$command = "ssh suralacore01@".$server_name." ls -lpR --time-style=full-iso '".$set_dir."'"; // add 2018/04/05 yoshizawa AWS対応
	exec($command,$LIST);

	mb_convert_variables("UTF-8", "sjis-win", $LIST);

	$hiku_time = 0;
	if ($L_CONTENTS_WEB['TIME'] != "0") {
		$hiku_time = $L_CONTENTS_WEB['TIME'] * 60 * 60;
	}

	$file_stamp = 0;
	$file_stamp_max = 0;
	if(is_array($LIST)){
		// 最新のファイル更新時間をタイムスタンプで返します。
		$pattern = "[0-9]{4}-[0-9]{2}-[0-9]{2}\s[0-9]{2}:[0-9]{2}:[0-9]{2}";
		foreach ($LIST as $key => $val) {
			if(preg_match("/{$pattern}/",$val,$matches)){
				$file_stamp = strtotime($matches[0]) - $hiku_time;
				if ($file_stamp_max < $file_stamp) { $file_stamp_max = $file_stamp; }
			}
		}
	}
	return $file_stamp_max;

}
// add end 2017/01/10 yoshizawa


/**
 * ローカルサーバーフォルダー内最新ファイル時間
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param string $dir
 * @return string
 */
function local_read_dir_time($dir) {
//function local_read_dir_time($dir) {	//	del ookawara 2013/01/07

	$LOCAL_FILE_NAME = local_read_dir($dir);
	if ($LOCAL_FILE_NAME['f']) {
		foreach ($LOCAL_FILE_NAME['f'] AS $key => $val) {
			if ($local_time < $val) { $local_time = $val; }
		}
	}

	if ($LOCAL_FILE_NAME['d']) {
		foreach ($LOCAL_FILE_NAME['d'] AS $key => $val) {
			if ($key == "") { continue; }
			$sub_dir = $dir.$key."/";
			$sub_local_time = local_read_dir_time($sub_dir);

			// トップの階層から最下層までのタイムスタンプを比較してより新しい更新日を保持する。
			if ($local_time < $sub_local_time) { $local_time = $sub_local_time; }	// add 2018/09/18 yoshizawa 課題要望No678

		}
		// >>> del 2018/09/18 yoshizawa 課題要望No678
		// TOP直下のディレクトリをソート順にチェックして、最後のディレクトリ以下のタイムスタンプしか考慮していないので、比較チェックをループ内で行う
		// if ($local_time < $sub_local_time) { $local_time = $sub_local_time; }
		// <<<
	}

	return $local_time;
}

//	add ookawara 2013/04/18

/**
 * ローカルフォルダー内ファイル最新時間、ファイル数取得	コマンド利用
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $select_web
 * @param string $dir
 * @param integer &$file_num
 * @param array &$file_time
 */
function get_file_data($select_web, $dir, &$file_num, &$file_time) {
	global $L_CONTENTS_WEB;

	//	フォルダー内情報取得
	unset($CNT_LIST);
	unset($TIME_LIST);
	if ($select_web) {
		$server_name = $select_web['SERVERNAME'];
		$dir_type = $select_web['DIRTYPE'];
		if ($dir_type == "2") { // リリースフォルダー
			$set_dir = KBAT_DIR.$dir;
			// exec("ssh suralacore01@srlbtw21 ./ACREADDIR3 '".$set_dir."'", $CNT_LIST); // del 2018/04/05 yoshizawa AWS対応
			exec("/data/home/_www/batch/ACREADDIR3 '".$set_dir."'", $CNT_LIST); // add 2018/04/05 yoshizawa AWS対応
			if ($CNT_LIST['0'] > 0) {
				// exec("ssh suralacore01@srlbtw21 ./ACREADDIR4 '".$set_dir."'", $TIME_LIST); // del 2018/04/05 yoshizawa AWS対応
				exec("/data/home/_www/batch/ACREADDIR4 '".$set_dir."'", $TIME_LIST); // add 2018/04/05 yoshizawa AWS対応
			}
		} elseif ($dir_type == "3") { // バッチフォルダー本番 // 未使用
			$set_dir = HBAT_DIR.$dir;
			// exec("ssh suralacore01@srlbtw21 ssh suralacore01@".$server_name." ./ACREADDIR3 '".$set_dir."'", $CNT_LIST); // del 2018/04/05 yoshizawa AWS対応
			exec("ssh suralacore01@".$server_name." /data/home/_www/batch/ACREADDIR3 '".$set_dir."'", $CNT_LIST); // add 2018/04/05 yoshizawa AWS対応
			if ($CNT_LIST['0'] > 0) {
				// exec("ssh suralacore01@srlbtw21 ssh suralacore01@".$server_name." ./ACREADDIR4 '".$set_dir."'", $TIME_LIST); // del 2018/04/05 yoshizawa AWS対応
				exec("ssh suralacore01@".$server_name." /data/home/_www/batch/ACREADDIR4 '".$set_dir."'", $TIME_LIST); // add 2018/04/05 yoshizawa AWS対応
			}
		} else { // リモートサーバー
			$set_dir = REMOTE_BASE_DIR.$dir;
			// exec("ssh suralacore01@srlbtw21 ssh suralacore01@".$server_name." ./ACREADDIR3 '".$set_dir."'", $CNT_LIST); // del 2018/04/05 yoshizawa AWS対応
			exec("ssh suralacore01@".$server_name." /data/home/_www/batch/ACREADDIR3 '".$set_dir."'", $CNT_LIST); // add 2018/04/05 yoshizawa AWS対応
			if ($CNT_LIST['0'] > 0) {
				// exec("ssh suralacore01@srlbtw21 ssh suralacore01@".$server_name." ./ACREADDIR4 '".$set_dir."'", $TIME_LIST); // del 2018/04/05 yoshizawa AWS対応
				exec("ssh suralacore01@".$server_name." /data/home/_www/batch/ACREADDIR3 '".$set_dir."'", $TIME_LIST); // add 2018/04/05 yoshizawa AWS対応
			}
		}
	} else {
		$set_dir = BASE_DIR.$dir;
		$cnt_cmd = "find ".$set_dir." -type f -print | wc -l";
		//$time_cmd = "stat `find ".$set_dir." -type f -name \"*\"` | grep ^Modify | sort -r | cut -d \" \" -f 2,3 | head -1";	//	del ookawara 2013/09/25
		$time_cmd = "stat ".$set_dir."`ls ".$set_dir." -tr | tail -1` | grep ^Modify | cut -d \" \" -f 2,3";	//	del ookawara 2013/09/25
		exec($cnt_cmd, $CNT_LIST);
		if ($CNT_LIST['0'] > 0) {
			exec($time_cmd, $TIME_LIST);
		}
	}

	$file_num = $CNT_LIST['0'];
	if ($file_num > 0) {
		$get_time = $TIME_LIST['0'];
		list($file_time) = explode(".", $get_time);
	}
}

//	add ookawara 2013/04/18
/**
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * ローカルフォルダー内ファイル最新時間、ファイル数取得	通常バージョン
 * @author Azet
 * @param array $select_web
 * @param string $dir
 * @param mixed &$file_num
 * @param mixed &$file_time
 */
function get_file_data_bak($select_web, $dir, &$file_num, &$file_time) {

	//	フォルダー内情報取得
	if ($select_web) {
		$FILE_NAME = remote_read_dir($select_web, $dir);
	} else {
		$FILE_NAME = local_read_dir($dir);
	}

	//	ファイル最新時間、ファイル数取得
	if ($FILE_NAME['f']) {
		foreach ($FILE_NAME['f'] AS $key => $val) {
			if ($file_time < $val) { $file_time = $val; }
			if ($val == "-1") {
				unset($FILE_NAME['f'][$key]);
			}
		}
		$file_num += count($FILE_NAME['f']);
	}

	//	フォルダー内情報取得
	if ($FILE_NAME['d']) {
		foreach ($FILE_NAME['d'] AS $key => $val) {
			if ($key == "") { continue; }
			$sub_dir = $dir.$key."/";
			$sub_local_time = get_file_data($select_web, $sub_dir, $file_num, $file_time);
		}
	}
}


/**
 * リモートサーバーフォルダー作成＆削除
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $select_web
 * @param string $dir
 * @param array $LIST_NUM
 * @param array $FILE_NAME
 * @return array
 */
function remote_set_dir($select_web,$dir,$LIST_NUM,$FILE_NAME) {

	//	作成
	if ($LIST_NUM) {
		foreach ($LIST_NUM AS $key => $val) {
			if ($key == "") {
				countinue;
			} elseif ($FILE_NAME['d'][$key]) {
				unset($FILE_NAME['d'][$key]);
				countinue;
			}

			$make_dir = $dir.$key;
			if (!$ftp->mkdir($make_dir,true)) {
				$ERROR[] = $dir.$key." のフォルダーが作成できませんでした。";
			}
			unset($FILE_NAME['d'][$key]);
		}
	}
	if ($ERROR) { return array($ERROR,$FILE_NAME); }

	return array($ERROR,$FILE_NAME);
}


/**
 * リモートサーバーファイルアップロード＆削除
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param object $ftp
 * @param string $local_dir
 * @param string $remote_dir
 * @param array $FILE_NAME
 * @return array
 */
function remote_set_file($ftp,$local_dir,$remote_dir,$FILE_NAME) {

	//	ローカルファイルデーター取得
	$LOCAL_FILE_NAME = local_read_dir($local_dir);
	//	ファイルアップロード
	if ($LOCAL_FILE_NAME['f']) {
		foreach ($LOCAL_FILE_NAME['f'] AS $key => $val) {
			if (!$FILE_NAME['f'][$key] || $FILE_NAME['f'][$key] < $LOCAL_FILE_NAME['f'][$key]) {
				$local_file = $local_dir.$key;
				$remote_file = $remote_dir.$key;
				if (!$ftp->put($local_file,$remote_file,true)) {
					$ERROR[] = $remote_file."ファイルがアップロードできませんでした。";
				}
			}
			unset($FILE_NAME['f'][$key]);
		}
	}
	if ($ERROR) { return array($ERROR,$FILE_NAME); }
	unset($LOCAL_FILE_NAME['f']);

	return array($ERROR,$LOCAL_FILE_NAME);
}


/**
 * リモートサーバーフォルダー内ファイルアップロード
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param object $ftp
 * @param string $dir_name
 * @param integer $LIST_NUM
 * @param string $local_dir
 * @param string $remote_dir
 * @return array エラーの場合
 */
function remote_set_dir_file($ftp,$dir_name,$LIST_NUM,$local_dir,$remote_dir) {

	//	リモートサーバーフォルダー確認＆作成
	unset($FILE_NAME);
	//	リモートサーバーファイルチェック
	$FILE_NAME = remote_read_dir($ftp,$remote_dir);
	//	リモートサーバーフォルダー作成＆削除
	list($ERROR) = remote_set_dir($ftp,$remote_dir,$LIST_NUM,$FILE_NAME);
	if ($ERROR) { return $ERROR; }
	//	ファイルアップロード
	unset($FILE_NAME);
	$set_local_dir = $local_dir.$dir_name."/";
	$set_remote_dir = $remote_dir.$dir_name."/";

	//	リモートサーバーフォルダー内情報読み込み
	$FILE_NAME = remote_read_dir($ftp,$set_remote_dir);

	//	リモートサーバーフォルダー内情報読み込みファイルアップロード＆削除
	list($ERROR,$LOCAL_FILE_NAME) = remote_set_file($ftp,$set_local_dir,$set_remote_dir,$FILE_NAME);
	if ($ERROR) { return $ERROR; }

	//	アップフォルダー内にフォルダーが存在した場合
	if ($LOCAL_FILE_NAME['d']) {
		foreach ($LOCAL_FILE_NAME['d'] AS $key => $val) {
			$sub_dir_name = $key;
			$sub_local_dir = $set_local_dir;
			$sub_remote_dir = $set_remote_dir;
			$ERROR = remote_set_dir_file($ftp,$sub_dir_name,$LOCAL_FILE_NAME['d'],$sub_local_dir,$sub_remote_dir);
			if ($ERROR) { return $ERROR; }
		}
	}

	return $ERROR;
}


/**
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
function local_file_make_list($LOCAL_FILE_NAME,$local_dir,$add_dir) {

	if ($LOCAL_FILE_NAME[f]) {
		@ksort($LOCAL_FILE_NAME["f"],SORT_STRING);
		foreach ($LOCAL_FILE_NAME["f"] AS $key => $val) {
			$html .= "<tr class=\"course_form_cell\">\n";
			$html .= "<td>".$add_dir.$key."</td>\n";
			$html .= "<td>".date("Y/m/d H:i",$val)."</td>\n";
			$html .= "</tr>\n";
		}
	}

	if ($LOCAL_FILE_NAME[d]) {
		@ksort($LOCAL_FILE_NAME["d"],SORT_STRING);
		foreach ($LOCAL_FILE_NAME["d"] AS $key => $val) {
			if ($key == "") { continue; }
			$html .= "<tr class=\"course_form_cell\">\n";
			$html .= "<td>".$add_dir.$key."/</td>\n";
			$html .= "<td>".date("Y/m/d H:i",$val)."</td>\n";
			$html .= "</tr>\n";

			$next_local_dir = $local_dir.$add_dir.$key."/";
			$NEXT_LOCAL_FILE_NAME = local_read_dir($next_local_dir);
			$html .= local_file_make_list($NEXT_LOCAL_FILE_NAME,$local_dir,$add_dir.$key."/");
		}
	}

	return $html;
}


/**
 * リモートサーバーファイルリスト作成
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $select_web
 * @param array $FILE_NAME
 * @param string $remote_dir
 * @param string $add_dir
 * @return string HTML
 */
function remote_file_make_list($select_web,$FILE_NAME,$remote_dir,$add_dir) {


	if ($FILE_NAME['f']) {
		@ksort($FILE_NAME['f'],SORT_STRING);
		foreach ($FILE_NAME['f'] AS $key => $val) {
			$html .= "<tr class=\"course_form_cell\">\n";
			$html .= "<td>".$add_dir.$key."</td>\n";
			$html .= "<td>".date("Y/m/d H:i",$val)."</td>\n";
			$html .= "</tr>\n";
		}
	}

	if ($FILE_NAME['d']) {
		@ksort($FILE_NAME['d'],SORT_STRING);
		foreach ($FILE_NAME['d'] AS $key => $val) {
			if ($key == "") { continue; }
			$html .= "<tr class=\"course_form_cell\">\n";
			$html .= "<td>".$add_dir.$key."/</td>\n";
			$html .= "<td>".date("Y/m/d H:i",$val)."</td>\n";
			$html .= "</tr>\n";

			$next_remote_dir = $remote_dir.$add_dir.$key."/";
			$NEXT_FILE_NAME = remote_read_dir($select_web,$next_remote_dir);
			$html .= remote_file_make_list($select_web,$NEXT_FILE_NAME,$remote_dir,$add_dir.$key."/");
		}
	}

	return $html;
}
// add start hirose 2018/10/04 commonフォルダアップ機能追加
/**
 * リモートサーバーファイルリスト作成
 * 該当ファイルの2階層下までを表示する。
 * 体系図html5共通部品・レクチャーhtml5共通部品で使用。
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $select_web 閲覧Webセレクトボックス
 * @param array $FILE_NAME ファイル情報:配列f->ファイル 配列d->フォルダ
 * @param string $remote_dir 閲覧Webの参照フォルダパス
 * @param string $add_dir フォルダアップパス
 * @return array (HTML,ファイル最新更新日時)
 */
function remote_file_make_list_two_hierarchy($select_web,$FILE_NAME,$remote_dir,$add_dir) {


	// 変数初期化
	$RESULT = array();
	$first_hierarchy = array();

	// ファイルの日時とファイル名を格納
	if ($FILE_NAME['f']) {
		@ksort($FILE_NAME['f'],SORT_STRING);
		foreach ($FILE_NAME['f'] AS $key => $val) {
			$RESULT['f::'.$key] = $val;
		}
	}

	// フォルダの日時とファイル名を格納
	if ($FILE_NAME['d']) {
		@ksort($FILE_NAME['d'],SORT_STRING);
		foreach ($FILE_NAME['d'] AS $key => $val) {
			if ($key == "") { continue; }
			$first_hierarchy[$key] = remote_read_dir($select_web,$remote_dir.$key);		// 下の階層を取得
		}
	}

	// 下の階層の情報を取得
	foreach ($first_hierarchy as $name => $info) {
		if($info['f']){
			foreach($info['f'] as $next_dir => $time ){
				$dir = $name."/".$next_dir;
				$RESULT['f::'.$dir] = $time;
			}
		}
		if($info['d']){
			foreach($info['d'] as $next_dir => $time ){
				$dir = $name."/".$next_dir;
				$RESULT['d::'.$dir] = remote_read_dir_time_ls($select_web,$remote_dir.$dir);	// ２階層以下の情報を全て取得する
			}
		}
	}
	//pre($RESULT);

	// 取得したフォルダ情報をhtml形式に加工する（複数行有り）。最新更新日時を変数に格納
	ksort($RESULT);
	$max_time = 0;
	$html = "";
	foreach($RESULT as $dir =>$time){
		if(preg_match('/^([fd]{1})::(.*)/',$dir,$match)){
			$dir = $match[2];
		}
		if($time>$max_time){ $max_time = $time; }
		$html .= "<tr class=\"course_form_cell\">\n";
		$html .= "<td>".$add_dir.$dir."</td>\n";
		$html .= "<td>".date("Y/m/d H:i",$time)."</td>\n";
		$html .= "</tr>\n";
	}
//	pre($first_hierarchy);
//	pre($RESULT);

	return array($html,$max_time);
}
// add end hirose 2018/10/04 commonフォルダアップ機能追加


// add okabe start 2013/11/29 シークレットイベントデータ用
/**
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * ローカルサーバーファイル情報取得 シークレットイベントデータ用
 * @author Azet
 * @param array $DIR_LIST
 * @param array &$LOCAL_FILES
 * @param string &$last_local_time
 * @param string $dir
 */
function local_glob_secretevent($DIR_LIST, &$LOCAL_FILES, &$last_local_time, $dir) {

	if (!$DIR_LIST) { return; }

	foreach ($DIR_LIST AS $each_dir_name) {
		$dir_name = $dir.$each_dir_name."/";
		$dir_name_ = preg_replace("/\//", "\/", $dir_name);
		//$set_file_name = $dir_name.$problem_num."*.*";
		$set_file_name = $dir_name."*.*";		//@@@@@
//echo "***".$set_file_name."<br>";
		foreach (glob("$set_file_name") AS $file_name) {
			$file_stamp = filemtime($file_name);
			if ($last_local_time < $file_stamp) {
				$last_local_time = $file_stamp;
			}
			$name = preg_replace("/$dir_name_/", "", $file_name);
			$LOCAL_FILES['f'][$name] = $file_stamp;
			//$LOCAL_FILES['df'][$dir_num][] = $name;
			$LOCAL_FILES['df'][$each_dir_name][] = $name;
		}
	}

}


/**
 * 閲覧サーバーファイル情報取得 シークレットイベントデータ用
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $LOCAL_FILES
 * @param string &$last_remote_time
 * @param integer &$last_remote_cnt
 * @param array $select_web
 * @param string $dir
 */
function remote_dir_time_secretevent($LOCAL_FILES, &$last_remote_time, &$last_remote_cnt, $select_web, $dir) {
	global $L_CONTENTS_WEB;

	if (!$LOCAL_FILES) { return; }

	//foreach ($LOCAL_FILES['df'] AS $dir_num => $name) {
	foreach ($LOCAL_FILES AS $each_dir_name) {
		$LIST = array();
		$server_name = $select_web['SERVERNAME'];
		$dir_type = $select_web['DIRTYPE'];

		// update start 2018/04/05 yoshizawa AWS対応
		// if ($dir_type == "2") {
		// 	$set_dir = KBAT_DIR.$dir.$each_dir_name."/";
		// 	//upd start 2017/11/24 yamaguchi AWS移設
		// 	//exec("ssh suralacore01@srlbtw21 ./ACREADDIR2 '".$set_dir."'",&$LIST);
		// 	exec("ssh suralacore01@srlbtw21 ./ACREADDIR2 '".$set_dir."'",$LIST);
		// 	//upd end 2017/11/24 yamaguchi
		// } elseif ($dir_type == "3") {
		// 	$set_dir = HBAT_DIR.$dir.$each_dir_name."/";
		// 	//upd start 2017/11/24 yamaguchi AWS移設
		// 	//exec("ssh suralacore01@srlbtw21 ssh suralacore01@".$server_name." ./ACREADDIR2 '".$set_dir."'",&$LIST);
		// 	exec("ssh suralacore01@srlbtw21 ssh suralacore01@".$server_name." ./ACREADDIR2 '".$set_dir."'",$LIST);
		// 	//upd end 2017/11/24 yamaguchi
		// } else {
		// 	$set_dir = REMOTE_BASE_DIR.$dir.$each_dir_name."/";
		// 	//upd start 2017/11/24 yamaguchi AWS移設
		// 	//exec("ssh suralacore01@srlbtw21 ssh suralacore01@".$server_name." ./ACREADDIR2 '".$set_dir."'",&$LIST);
		// 	exec("ssh suralacore01@srlbtw21 ssh suralacore01@".$server_name." ./ACREADDIR2 '".$set_dir."'",$LIST);
		// 	//upd end 2017/11/24 yamaguchi
		// }
		//
		if ($dir_type == "2") { // リリースフォルダー
			$set_dir = KBAT_DIR.$dir.$each_dir_name."/";
			exec("/data/home/_www/batch/ACREADDIR2 '".$set_dir."'",$LIST);
		} elseif ($dir_type == "3") { // バッチフォルダー本番 // 未使用
			$set_dir = HBAT_DIR.$dir.$each_dir_name."/";
			exec("ssh suralacore01@".$server_name." /data/home/_www/batch/ACREADDIR2 '".$set_dir."'",$LIST);
		} else { // リモートサーバー
			$set_dir = REMOTE_BASE_DIR.$dir.$each_dir_name."/";
			exec("ssh suralacore01@".$server_name." /data/home/_www/batch/ACREADDIR2 '".$set_dir."'",$LIST);
		}
		// update end 2018/04/05 yoshizawa AWS対応

		if (!$LIST) { continue; }

		$hiku_time = 0;
		if ($L_CONTENTS_WEB['TIME'] != "0") {
			$hiku_time = $L_CONTENTS_WEB['TIME'] * 60 * 60;
		}
		mb_convert_variables("UTF-8", "sjis-win", $LIST);
		foreach ($LIST AS $LINE) {
			$LINE = preg_replace("/\s\s+/"," ",$LINE);
			$DATA_LIST = explode(" ",$LINE);

			$file_num = $DATA_LIST[1];
			$file_type = "f";
			if ($file_num > 1) {
				continue;
			}

			$file_name = $DATA_LIST[8];
			if (count($DATA_LIST) > 9) {
				$last_num = count($DATA_LIST);
				for ($i=9; $i<$last_num; $i++) {
					$file_name .= " ".$DATA_LIST[$i];
				}
			}

			if (preg_match("/\*$/", $file_name) || preg_match("/\@$/", $file_name)) {
				continue;
			//} elseif (array_search($file_name, $LOCAL_FILES['df'][$dir_num]) === FALSE) {
			//	continue;
			}

			$file_time_day = $DATA_LIST[5];
			$file_time_hour = $DATA_LIST[6];
			list($year, $mon, $day) = split("-", $file_time_day);
			list($hour, $min, $sec) = split(":", $file_time_hour);
			$file_stamp = mktime($hour, $min, $sec, $mon, $day, $year) - $hiku_time;

			if ($last_remote_time < $file_stamp) {
				$last_remote_time = $file_stamp;
			}
			$last_remote_cnt += 1;
		}

//echo "*RESULT: last_remote_time=".$last_remote_time."*<br>";
	}
}


/**
 * リモートサーバーフォルダー作成 シークレットイベントデータ用
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param string $dir
 * @param array $FILE_NAME
 * @param array &$ERROR
 */
function remote_set_dir_secretevent($dir, $FILE_NAME, &$ERROR) {
//echo "*test_remote_set_dir<br>";
//print_r($FILE_NAME['df']); echo "<br>";

	//	作成
	if ($FILE_NAME) {
		foreach ($FILE_NAME['df'] AS $key => $val) {
//echo "*key=".$key.", val=".$val."<br>";
			if ($key == "") {
				countinue;
			}
			$make_dir = $dir.$key;
			// $command = "ssh suralacore01@srlbtw21 mkdir -p ".$make_dir;	// upd hasegawa 2018/03/29 AWS移設
			$command = "mkdir -p ".$make_dir;
			exec("$command", $LIST);
//echo "EXEC:".$command."<br>\n";
		}
	}
	return;
}


/**
 * リモートサーバーファイルアップロード シークレットイベントデータ用
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param string $local_dir
 * @param string $remote_dir
 * @param array $FILE_NAME
 * @param array &$ERROR
 */
function remote_set_file_secretevent($local_dir, $remote_dir, $FILE_NAME, &$ERROR) {

	//	ファイルアップロード
	if ($FILE_NAME['df']) {
		$COM_LIST = array();
		foreach ($FILE_NAME['df'] AS $key => $FILE_LIST) {
			if ($FILE_LIST) {
				$i = 0;
				$com_line = "";
				foreach ($FILE_LIST AS $file_name) {
					$local_file = $local_dir.$key."/".$file_name;
					$remote_file_dir = $remote_dir.$key."/";
					// $com_line .= "scp -rp '".$local_file."' suralacore01@srlbtw21:'".$remote_file_dir."'\n";	// upd hasegawa 2018/03/29 AWS移設
					$com_line .= "cp -rp '".$local_file."' '".$remote_file_dir."'\n";

					$i++;
					if ($i >= 20) {
						$COM_LIST[] = $com_line;
						$com_line = "";
						$i = 0;
					}
				}
				if ($com_line) {
					$COM_LIST[] = $com_line;
				}
			}
		}
		if (count($COM_LIST) > 0) {
			foreach ($COM_LIST AS $com_line) {

				//	add ookawara 2012/08/20
				echo "・";
				flush();

				//upd start 2017/11/24 yamaguchi AWS移設
				//exec("$com_line", &$LIST);
				exec("$com_line", $LIST);
				//upd end 2017/11/24 yamaguchi
//echo "*com_line=".$com_line."<br>";
			}

			//	add ookawara 2012/08/20
			echo "<br>\n";
			flush();

		}
	}

	return;
}
// add okabe end 2013/11/29 シークレットイベントデータ用


//	add ookawara 2012/11/01	test_practice_update.phpと同じもの

/**
 * インサートクエリー作成
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param object $result
 * @param string $table_name
 * @param array &$INSERT_NAME
 * @param array &$INSERT_VALUE
 */
function make_insert_query($result, $table_name, &$INSERT_NAME, &$INSERT_VALUE) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$flag = 0;
	$i = 0;
	$num = 0;
	$insert_name = "";
	$insert_value = "";
	while ($list = $cdb->fetch_assoc($result)) {
		$value = "";
		if ($insert_value) { $insert_value .= ", "; }

		foreach ($list AS $key => $val) {
			if ($flag != 1) {
				if ($insert_name) { $insert_name .= ","; }
				$insert_name .= $key;
			}
			if ($value) { $value .= ","; }
			$value .= "'".addslashes($val)."'";
		}
		if ($value) {
			$insert_value .= "(".$value.")";
			$i++;
		}
		if ($insert_name) { $flag = 1; }
		if ($i == 50) {
			$INSERT_VALUE[$table_name][] = $insert_value;
			$num++;
			$i = 1;
			$insert_value = "";
		}
	}
	if ($insert_name) { $INSERT_NAME[$table_name] = $insert_name; }
	if ($insert_value) { $INSERT_VALUE[$table_name][] = $insert_value; }

}
?>
