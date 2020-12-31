<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * 速習用プラクティスステージ管理　プラクティスアップデート
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

	$include_file = LOG_DIR . "admin_lib/package_update_package_data.php";
	include($include_file);
	$html .= sub_start($L_NAME);

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

	$html  = "<br>\n";
	$html .= "閲覧DB：<select name=\"s_db_sel\" onchange=\"submit();\">\n";
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