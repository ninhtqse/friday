<?
/**
 * ベンチャー・リンク　すらら
 *
 * 教室長リストエクスポート
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

	if (ACTION == "check") {
		$ERROR = check();
	}
	if (!$ERROR) {
		if (ACTION == "sub_session") { $ERROR = sub_session(); }
		elseif (ACTION == "skip_reset") { $ERROR = sub_session(); }
		elseif (ACTION == "like_reset") { $ERROR = sub_session(); }
		elseif (ACTION == "make_csv") { $ERROR = make_csv(); }
		elseif (ACTION == "set_enterprise") { $ERROR = set_enterprise(); }
		elseif (ACTION == "del_enterprise") { $ERROR = del_enterprise(); }
	}

	$html .= mode_menu();
	if (!MODE) { $html .= member_list(); }
	elseif (MODE == "member_list") { $html .= member_list(); }
	elseif (MODE == "set_enterprise") {
		$html .= select_enterprise();
	} elseif (MODE == "del_enterprise") {
		$html .= member_list();
	}
	return $html;
}


/**
 * 会社選択フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function select_enterprise() {

	global $L_PAGE_VIEW;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql = "SELECT enterprise_id,enterprise_name FROM " . T_ENTERPRISE .
		" WHERE mk_flg!='1'" .
		" ORDER BY enterprise_id;";
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
	}
	if (!$max) {
		$html = "現在登録加盟店情報が存在しません。<br>\n";
		return $html;
	}
	if ($_SESSION[set_enterprise]) {
		$html .= "<p>現在、加盟店[<strong style=\"color:#ff0000;\">".$_SESSION[set_enterprise][enterprise_name]."</strong>]にセットされています。</p>\n";
	}

	$html .= "<table class=\"member_form\">\n";
	$html .= "<tr>\n";
	$html .= "<td class=\"member_form_menu\">加盟店名</td>\n";
	$html .= "<td class=\"member_form_menu\">セット</td>\n";
	$html .= "</tr>";
	while ($list = $cdb->fetch_assoc($result)) {
		$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" name=\"menu\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"set_enterprise\">\n";
		$html .= "<input type=\"hidden\" name=\"enterprise_id\" value=\"".$list[enterprise_id]."\">\n";
		$html .= "<input type=\"hidden\" name=\"enterprise_name\" value=\"".$list[enterprise_name]."\">\n";
		$html .= "<tr>\n";
		$html .= "<td class=\"member_form_cell\">".$list[enterprise_name]."</td>\n";
		$html .= "<td class=\"member_form_cell\"><input type=\"submit\" value=\"セット\"></td>\n";
		$html .= "</tr>\n";
		$html .= "</form>\n";
	}
	$html .= "</table><br>\n";
	$html .= "設定する加盟店を選択してください。<br>\n";

	return $html;
}


/**
 * 会社設定
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function set_enterprise() {
	$_SESSION[set_enterprise][enterprise_id] = $_POST[enterprise_id];
	$_SESSION[set_enterprise][enterprise_name] = $_POST[enterprise_name];
	$_SESSION[sub_session][s_page] = 1;
	return;
}

/**
 * 未設定
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function del_enterprise() {
	unset($_SESSION[set_enterprise]);
	return;
}

/**
 * モードを選択メニューを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function mode_menu() {
	global $L_SEARCH_SCHOOL,$L_ORDER_SCHOOL,$L_DESC,$L_PAGE_VIEW;

	if (!MODE || MODE == "member_list" || MODE == "del_enterprise") {
		foreach ($L_ORDER_SCHOOL as $key => $val){
			if ($_SESSION[sub_session][s_order] == $key) { $sel = " selected"; } else { $sel = ""; }
			$s_order_html .= "<option value=\"$key\"$sel>$val</option>\n";
		}
		foreach ($L_DESC as $key => $val){
			if ($_SESSION[sub_session][s_desc] == $key) { $sel = " selected"; } else { $sel = ""; }
			$s_desc_html .= "<option value=\"$key\"$sel>$val</option>\n";
		}
		foreach ($L_PAGE_VIEW as $key => $val){
			if ($_SESSION[sub_session][s_page_view] == $key) { $sel = " selected"; } else { $sel = ""; }
			$s_page_view_html .= "<option value=\"$key\"$sel>$val</option>\n";
		}
		foreach ($L_SEARCH_SCHOOL as $key => $val){
			if ($_SESSION[sub_session][s_like] == $key) { $sel = " selected"; } else { $sel = ""; }
			$s_like_html .= "<option value=\"$key\"$sel>$val</option>\n";
		}
		$sub_session_html .= "<td style=\"width:20px;\">&nbsp;</td>\n";
		$sub_session_html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
		$sub_session_html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
		$sub_session_html .= "<td>\n";
		$sub_session_html .= "ソート<select name=\"s_order\">\n";
		$sub_session_html .= "$s_order_html</select><select name=\"s_desc\">\n";
		$sub_session_html .= "$s_desc_html</select>表示数<select name=\"s_page_view\">\n";
		$sub_session_html .= "$s_page_view_html</select><input type=\"submit\" value=\"Set\">\n";
		$sub_session_html .= "</td>\n";
		$sub_session_html .= "</form>\n";
		$sub_session_html .= "<td style=\"width:20px;\">&nbsp;</td>\n";
		$sub_session_html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
		$sub_session_html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
		$sub_session_html .= "<td>\n";
		$sub_session_html .= "検索 加盟店名\n";
		$sub_session_html .= "<input type=\"text\" name=\"s_enterprise_name\" value=\"".$_SESSION[sub_session][s_enterprise_name]."\">";
		$sub_session_html .= "教室名\n";
		$sub_session_html .= "<input type=\"text\" name=\"s_school_name\" value=\"".$_SESSION[sub_session][s_school_name]."\">";
		$sub_session_html .= "<input type=\"submit\" value=\"Search\">\n";
		$sub_session_html .= "</td>\n";
		$sub_session_html .= "</form>\n";
		$sub_session_html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
		$sub_session_html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
		$sub_session_html .= "<input type=\"hidden\" name=\"s_enterprise_name\" value=\"\">\n";
		$sub_session_html .= "<input type=\"hidden\" name=\"s_school_name\" value=\"\">\n";
		$sub_session_html .= "<td><input type=\"submit\" value=\"Reset\"></td>\n";
		$sub_session_html .= "</form>\n";
	}

	$html .= "<div id=\"mode_menu\">\n";
	$html .= "<table cellpadding=0 cellspacing=0>\n";
	$html .= "<tr>\n";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"member_list\">\n";
	$html .= "<td><input type=\"submit\" value=\"一覧表示\"></td>\n";
	$html .= "</form>\n";

	if (!$_SESSION[set_enterprise]) {
		$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"set_enterprise\">\n";
		$html .= "<td><input type=\"submit\" value=\"加盟店指定\"></td>\n";
		$html .= "</form>\n";
	}

	if ($_SESSION[set_enterprise]) {
		$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"set_enterprise\">\n";
		$html .= "<td><input type=\"submit\" value=\"加盟店再指定\"></td>\n";
		$html .= "</form>\n";
		$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"del_enterprise\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"del_enterprise\">\n";
		$html .= "<td><input type=\"submit\" value=\"加盟店指定解除\"></td>\n";
		$html .= "</form>\n";
	}

	$html .= "$sub_session_html";
	$html .= "</tr>\n";
	$html .= "</table>\n";
	$html .= "</div><br>\n";

	return $html;
}


/**
 * SESSION設定
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function sub_session() {
	if (strlen($_POST[s_order])) { $_SESSION[sub_session][s_order] = $_POST[s_order]; }
	if (strlen($_POST[s_desc])) { $_SESSION[sub_session][s_desc] = $_POST[s_desc]; }
	if (strlen($_POST[s_page_view])) { $_SESSION[sub_session][s_page_view] = $_POST[s_page_view]; }
	if (strlen($_POST[s_order])&&strlen($_POST[s_desc])&&strlen($_POST[s_page_view])) { unset($_SESSION[sub_session][s_page]); }
	if (strlen($_POST[s_page])) { $_SESSION[sub_session][s_page] = $_POST[s_page]; }
	if (strlen($_POST[s_like])) { $_SESSION[sub_session][s_like] = $_POST[s_like]; }
	if (strlen($_POST[s_word])) { $_SESSION[sub_session][s_word] = $_POST[s_word]; }
	if (ACTION != "make_csv") {
		if (isset($_POST[s_enterprise_name])) { $_SESSION[sub_session][s_enterprise_name] = $_POST[s_enterprise_name]; }
		if (isset($_POST[s_school_name])) { $_SESSION[sub_session][s_school_name] = $_POST[s_school_name]; }
	}
	if (ACTION == "skip_reset") { unset($_SESSION[skip_list]); return; }

	if (is_array($_POST[skip_list])) {
		if (is_array($_SESSION[skip_list])) {
			foreach ($_POST[target_list] as $val) {
				$hits = array_search($val,$_SESSION[skip_list]);
				if ($hits !== FALSE) {
					if (array_search($val,$_POST[skip_list]) === FALSE) {
						unset($_SESSION[skip_list][$hits]);
					}
				} else {
					$hits_post = array_search($val,$_POST[skip_list]);
					if ($hits_post !== FALSE) {
						$_SESSION[skip_list][] = $val;
					}
				}
			}
		} else {
			$_SESSION[skip_list] = $_POST[skip_list];
		}
	} else {
		if ($_SESSION[skip_list]&&$_POST[target_list]) {
			foreach ($_POST[target_list] as $val) {
				$hits = array_search($val,$_SESSION[skip_list]);
				if ($hits !== FALSE) {
					unset($_SESSION[skip_list][$hits]);
				}
			}
		}
	}
	if (!$_SESSION[skip_list]) { unset($_SESSION[skip_list]); }
	return;
}

/**
 * メンバー一覧作成
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function member_list() {

	global $L_PAGE_VIEW;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql = "CREATE TEMPORARY TABLE kari " .
		"SELECT manager_id AS school_id,regist_time AS kari_time FROM " . T_LOGINLOG .
		" WHERE manager_level='5'" .
		" AND action_data='1'" .
		" AND clients='schooltool'" .
		" AND status='0'" .
		" GROUP BY manager_id" .
		" ORDER BY regist_time" ;
	$cdb->exec_query($sql);
	$sql = "CREATE TEMPORARY TABLE hon " .
		"SELECT manager_id AS school_id,regist_time AS hon_time FROM " . T_LOGINLOG .
		" WHERE manager_level='5'" .
		" AND action_data='1'" .
		" AND clients='schooltool'" .
		" AND status='1'" .
		" GROUP BY manager_id" .
		" ORDER BY regist_time" ;
	$cdb->exec_query($sql);
	if ($_SESSION[set_enterprise]) {
		$html .= "<p>現在、加盟店[<strong style=\"color:#ff0000;\">".$_SESSION[set_enterprise][enterprise_name]."</strong>]にセットされています。</p>\n";
		$sql = "SELECT school.school_id,enterprise.enterprise_name,school.school_name,kari.kari_time,hon.hon_time FROM " .
			T_ENTERPRISE . " enterprise," .  T_SCHOOL . " school" .
				" LEFT JOIN kari" .
				" ON kari.school_id=school.school_id" .
				" LEFT JOIN hon" .
				" ON hon.school_id=school.school_id" .
			" WHERE enterprise.mk_flg='0' AND enterprise.move_flg='0' AND school.mk_flg='0' AND school.move_flg='0'" .
			" AND enterprise.enterprise_id=" . $_SESSION[set_enterprise][enterprise_id] . "" .
			" AND enterprise.enterprise_id=school.enterprise_id";
	} else {
		$sql = "SELECT school.school_id,enterprise.enterprise_name,school.school_name,kari.kari_time,hon.hon_time FROM " .
			T_ENTERPRISE . " enterprise," .  T_SCHOOL . " school" .
				" LEFT JOIN kari" .
				" ON kari.school_id=school.school_id" .
				" LEFT JOIN hon" .
				" ON hon.school_id=school.school_id" .
			" WHERE enterprise.mk_flg='0' AND enterprise.move_flg='0' AND school.mk_flg='0' AND school.move_flg='0'" .
			" AND enterprise.enterprise_id=school.enterprise_id";
	}

	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"button\" value=\"ダウンロード\" onclick=\"exports($page);\">";
	$html .= "</form>\n";

	if ($_SESSION['sub_session']['s_word']) {
		if ($_SESSION['sub_session']['s_like'] == "id") { $like_name = "manager.manager_id"; }
		elseif ($_SESSION['sub_session']['s_like'] == "enterprise") { $like_name = "enterprise.enterprise_name"; }
		elseif ($_SESSION['sub_session']['s_like'] == "school") { $like_name = "school.cram_school_name"; }
		$sql .= " AND " . $like_name . " LIKE '%".$_SESSION['sub_session']['s_word']."%'";
	}
	if ($_SESSION['sub_session']['s_enterprise_name']) {
		$sql .= " AND enterprise.enterprise_name LIKE '%".$_SESSION['sub_session']['s_enterprise_name']."%'";
	}
	if ($_SESSION['sub_session']['s_school_name']) {
		$sql .= " AND school.school_name LIKE '%".$_SESSION['sub_session']['s_school_name']."%'";
	}
	if ($result = $cdb->query($sql)) {
		$list_count = $cdb->num_rows($result);
	}
	if ($_SESSION[sub_session][s_order] == 1) {
		$sql .= " ORDER BY school.school_id";
	} elseif ($_SESSION[sub_session][s_order] == 2) {
		$sql .= " ORDER BY school.school_kana";
	} else {
		$sql .= " ORDER BY school.school_id";
	}
	if ($_SESSION[sub_session][s_desc]) {
		$sql .= " DESC";
	}

	if ($L_PAGE_VIEW[$_SESSION[sub_session][s_page_view]]) { $page_view = $L_PAGE_VIEW[$_SESSION[sub_session][s_page_view]]; }
	else { $page_view = $L_PAGE_VIEW[0]; }
	$max_page = ceil($list_count/$page_view);
	if ($_SESSION[sub_session][s_page]) { $page = $_SESSION[sub_session][s_page]; }
	else { $page = 1; }
	if ($page <= 0) { $page = 1; }
	$start = ($page - 1) * $page_view;
	$next = $page + 1;
	$back = $page - 1;

	$sql .= " LIMIT $start,$page_view;";

	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
		if ($max) {
			$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" name=\"form_skip\">\n";
			$html .= "<div style=\"float:left;\">教室長数($list_count):PAGE[$page/$max_page]</div>\n";
			$html .= "<input type=\"hidden\" name=\"s_page\" value=\"$page\">\n";
			$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";

			if ($back > 0) {
				$html .= "<input type=\"button\" value=\"前のページ\" onclick=\"export_back($back);\">";
			}
			if ($page < $max_page) {
				$html .= "<input type=\"button\" value=\"次のページ\" onclick=\"export_next($next);\">\n";
			}

			$html .= "<br style=\"clear:left;\">";
			$html .= "<table class=\"member_form\" style=\"float:left;\">\n";
			$html .= "<tr class=\"member_form_menu\">\n";
			$html .= "<td style=\"height:40px;\">除外</td>\n";
			$html .= "<td>ID</td>\n";
			$html .= "<td>加盟店名</td>\n";
			$html .= "<td>教室名</td>\n";
			$html .= "<td>仮契約後初回ログイン日時</td>\n";
			$html .= "<td>本契約後初回ログイン日時</td>\n";
			$html .= "</tr>\n";

			while ($list = $cdb->fetch_assoc($result)) {
				foreach($list as $key => $val) { $list[$key] = replace_decode($val); }
				if (is_array($_SESSION[skip_list])) {
					if (array_search($list[school_num],$_SESSION[skip_list]) !== FALSE) {
						$checked = " checked";
					} else {
						$checked = "";
					}
				} else {
					$checked = "";
				}
				$html .= "<tr class=\"member_form_cell\">\n";
				$html .= "<td style=\"height:26px;\"><input type=\"hidden\" name=\"target_list[]\" value=\"".$list[school_id]."\">";
				$html .= "<input type=\"checkbox\" name=\"skip_list[]\" value=\"".$list[school_id]."\"$checked></td>\n";
				$html .= "<td>".$list[school_id]."</td>\n";
				$html .= "<td>".$list[enterprise_name]."</td>\n";
				$html .= "<td>".$list[school_name]."</td>\n";
				$html .= "<td>".$list[kari_time]."</td>\n";
				$html .= "<td>".$list[hon_time]."</td>\n";
				$html .= "</tr>\n";
			}
			$html .= "</table>\n";
			$html .= "<br style=\"clear:left;\">";
			$html .= "<input type=\"button\" value=\"ダウンロード\" onclick=\"exports($page);\">";
			$html .= "<input type=\"button\" value=\"リセット\" onclick=\"skip_reset();\">\n";
			$html .= "</form>";
		} else {
			$html .= "現在、登録教室長は存在しません。";
		}
	}

	return $html;
}

/**
 * CSV出力機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function make_csv() {

	global $L_PAY_TYPE,$L_PRF,$L_CONTRACT_TYPE;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$ERROR = sub_session();
	if ($ERROR) { return $ERROR; }
	$filename = "school_list.txt";

	header("Cache-Control: public");
	header("Pragma: public");
	header("Content-disposition: attachment;filename=$filename");
	if (stristr($HTTP_USER_AGENT, "MSIE")) {
		header("Content-Type: text/octet-stream");
	} else {
		header("Content-Type: application/octet-stream;");
	}

	//	head line (一行目)
	$csv_head = "\"教室名\"\t";
	$csv_head .= "\"支払い方法\"\t";
	$csv_head .= "\"法人名\"\t";
	$csv_head .= "\"法人ID\"\t";
	$csv_head .= "\"教室長氏名\"\t";
	$csv_head .= "\"教室長ふりがな\"\t";
	$csv_head .= "\"電話番号\"\t";
	$csv_head .= "\"FAX番号\"\t";
	$csv_head .= "\"住所\"\t";
	$csv_head .= "\"教室長ID\"\t";
	$csv_head .= "\"教室長仮契約後初回ログイン日時\"\t";
	$csv_head .= "\"教室長本契約後初回ログイン日時\"\n";

	if ($_SESSION[skip_list]) {
		foreach ($_SESSION[skip_list] as $val) {
			$where .= " AND school.school_id!='$val'";
		}
	}
	if ($_SESSION['sub_session']['s_word']) {
		if ($_SESSION['sub_session']['s_like'] == "id") { $like_name = "manager.manager_id"; }
		elseif ($_SESSION['sub_session']['s_like'] == "enterprise") { $like_name = "enterprise.enterprise_name"; }
		elseif ($_SESSION['sub_session']['s_like'] == "school") { $like_name = "school.cram_school_name"; }
		$where .= " AND " . $like_name . " LIKE '%".$_SESSION['sub_session']['s_word']."%'";
	}
	if ($_SESSION['sub_session']['s_enterprise_name']) {
		$where .= " AND enterprise.enterprise_name LIKE '%".$_SESSION['sub_session']['s_enterprise_name']."%'";
	}
	if ($_SESSION['sub_session']['s_school_name']) {
		$where .= " AND school.school_name LIKE '%".$_SESSION['sub_session']['s_school_name']."%'";
	}
	if ($_SESSION[set_enterprise]) {
		$sql = "SELECT school.school_id,school.school_name," .
			"school.school_tts_name,school.school_tts_kana,school.zip,school.prf," .
			"school.city,school.add1,school.add2,school.tel,school.fax," .
			"enterprise.enterprise_name,enterprise.enterprise_id,school.pay_type" .
			" FROM " . T_ENTERPRISE . " enterprise," . T_SCHOOL . " school" .
			" WHERE enterprise.mk_flg='0' AND enterprise.move_flg='0' AND school.mk_flg='0' AND school.move_flg='0'" .
			" AND enterprise.enterprise_id=" . $_SESSION[set_enterprise][enterprise_id] . "" .
			" AND enterprise.enterprise_id=school.enterprise_id";
	} else {
		$sql = "SELECT school.school_id,school.school_name," .
			"school.school_tts_name,school.school_tts_kana,school.zip,school.prf," .
			"school.city,school.add1,school.add2,school.tel,school.fax," .
			"enterprise.enterprise_name,enterprise.enterprise_id,school.pay_type" .
			" FROM " . T_ENTERPRISE . " enterprise," . T_SCHOOL . " school" .
			" WHERE enterprise.mk_flg='0' AND enterprise.move_flg='0' AND school.mk_flg='0' AND school.move_flg='0'" .
			" AND enterprise.enterprise_id=school.enterprise_id";
	}
	$sql .= $where;
	if ($_SESSION[sub_session][s_order] == 1) {
		$sql .= " ORDER BY school.school_id";
	} elseif ($_SESSION[sub_session][s_order] == 2) {
		$sql .= " ORDER BY school.school_name";
	} else {
		$sql .= " ORDER BY school.school_id";
	}
	if ($_SESSION[sub_session][s_desc]) {
		$sql .= " DESC";
	}

	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {

			$address = $list[zip];
			$address .= "　";
			$address .= $list[prf];
			$address .= "　";
			$address .= $list[city];
			$address .= "　";
			$address .= $list[add1];
			if ($list[add2]) { $address .= "　"; $address .= $list[add2]; }

			$csv_line .= "\"$list[school_name]\"\t";
			$csv_line .= "\"".$L_PAY_TYPE[$list[pay_type]]."\"\t";
			$csv_line .= "\"$list[enterprise_name]\"\t";
			$csv_line .= "\"$list[enterprise_id]\"\t";
			$csv_line .= "\"$list[school_tts_name]\"\t";
			$csv_line .= "\"$list[school_tts_kana]\"\t";
			$csv_line .= "\"$list[tel]\"\t";
			$csv_line .= "\"$list[fax]\"\t";
			$csv_line .= "\"$address\"\t";
			$csv_line .= "\"$list[school_id]\"\t";
			$csv_line .= "\"$kari\"\t";
			$csv_line .= "\"$hon\"\t";
			$csv_line .= "\n";
		}
	}

	$csv_head = mb_convert_encoding($csv_head,"SJIS","UTF-8");
	$csv_line = mb_convert_encoding($csv_line,"SJIS","UTF-8");

	echo $csv_head;
	echo $csv_line;

	exit;
}

?>