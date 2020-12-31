<?PHP
/**
 * すらら
 *
 * ゲーミフィケーション管理 プラクティスアップデート
 * 2019/07/08 初期設定 生徒TOP改修
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
	global $L_UPDATE_MODE_GAMIFICATION;

// echo '★'.MODE;	
// pre($_POST);	
// pre($_SESSION);	
	
	if (defined("MODE") && $L_UPDATE_MODE_GAMIFICATION) {
		foreach ($L_UPDATE_MODE_GAMIFICATION AS $key => $val) {
			if (ereg("^$key",MODE)) {
				$set_filename = $key;
				break;
			}
			if ($set_filename) { break; }
		}
	}

	
	$html .= select_mode();
	if ($set_filename) {
		$include_file = LOG_DIR . "admin_lib/gamification_update_".MODE.".php";
	}

	if ($include_file && file_exists($include_file)) {
		include($include_file);
		$html .= sub_start();
	}

	return $html;
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

	global $L_UPDATE_MODE_GAMIFICATION;
	$html = '';	

	$count = 0;
	if ($L_UPDATE_MODE_GAMIFICATION) {
		$count = count($L_UPDATE_MODE_GAMIFICATION);
		if ($count > 1) { $colspan = "colspan=\"$count\""; }
	} else {
		return $html;
	}
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" name=\"menu\">\n";
	$html .= "<table class=\"unit_form\">\n";
	$html .= "<tr class=\"unit_form_cell\">\n";
	$html .= "<td class=\"stage_form_menu\" $colspan>アップデートコンテンツ</td>\n";
	$html .= "</tr>\n";
	$html .= "<tr class=\"unit_form_cell\">\n";
	$i = 1;
	$length = 5;
	$cnt_data = count($L_UPDATE_MODE_GAMIFICATION);
	foreach ($L_UPDATE_MODE_GAMIFICATION AS $key => $val) {
		if ($i > $length) {
			$html .= "</tr>\n";
			$html .= "<tr class=\"unit_form_cell\">\n";
			$i = 1;
		}
		if (MODE == $key) { $checked = "checked"; } else { $checked = ""; }
		$html .= "<td style=\"padding:2px;\">\n";
		$html .= "<input type=\"radio\" name=\"mode\" value=\"$key\" onclick=\"submit()\" $checked>：$val\n";
		$html .= "</td>\n";
		$i++;
	}
	$amari = ($i - 1) % $length;
	if ($amari > 0 && $cnt_data > $length) {
		$num = $length - $amari;
		for ($i=1; $i<=$num; $i++) {
			$html .= "<td>&nbsp;</td>\n";
		}
	}

	$html .= "</tr>\n";
	$html .= "</table>\n";
	$html .= "</form>\n";
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
	global $L_GAMIFICATION_DB;

	foreach ($L_GAMIFICATION_DB as $key => $val){
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
	global $L_GAMIFICATION_DB;

	if (strlen($_POST['s_db_sel'])) { $_SESSION['select_db'] = $L_GAMIFICATION_DB[$_POST['s_db_sel']]; }

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
	global $L_GAMIFICATION_WEB;

	foreach ($L_GAMIFICATION_WEB as $key => $val){
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
	global $L_GAMIFICATION_WEB;

	if (strlen($_POST['s_web_sel'])) { $_SESSION['select_web'] = $L_GAMIFICATION_WEB[$_POST['s_web_sel']]; }

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
function remote_read_dir($select_web, $dir) {
	
	global $L_GAMIFICATION_WEB;

	unset($LIST);
	
	$FILE_NAME = array();
	
	$server_name = $select_web['SERVERNAME'];
	$dir_type = $select_web['DIRTYPE'];
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
	mb_convert_variables("UTF-8", "sjis-win", $LIST);
	if ($LIST) {
		$hiku_time = 0;
		if ($L_GAMIFICATION_WEB['TIME'] != "0") {
			$hiku_time = $L_GAMIFICATION_WEB['TIME'] * 60 * 60;
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

	
	$LOCAL_FILE_NAME = array();
	
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
