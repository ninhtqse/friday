<?
/**
 * ベンチャー・リンク　すらら
 *
 * 保護者・生徒管理　転校処理
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
		elseif (ACTION == "next_step") { $ERROR = sub_session(); }
		elseif (ACTION == "set_enterprise") { $ERROR = set_enterprise(); }
		elseif (ACTION == "check") { $ERROR = check(); }
		elseif (ACTION == "change") { $ERROR = change(); }
//		elseif (ACTION == "back") { $ERROR = back(); }
	}

	$html .= mode_menu();
	if (!MODE) {
		$html .= member_list($ERROR);
	} elseif (MODE == "member_list") {
		$html .= member_list($ERROR);
	} elseif (MODE == "next_step") {
		if ($ERROR) { $html .= member_list($ERROR); }
		else { $html .= select_enterprise($ERROR); }
	} elseif (MODE == "check") {
		if ($ERROR) { $html .= select_enterprise($ERROR); }
		else { $html .= set_enterprise(); }
	} elseif (MODE == "change") {
		if ($ERROR) { $html .= set_enterprise(); }
		else { $html .= fin(); }
	}
//pre($_POST);
//pre($_SESSION);
	return $html;
}

/**
 * 会社選択メニュー作成
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function select_enterprise($ERROR) {

	if ($ERROR) { $html .= ERROR($ERROR); }
	$html .= "<table class=\"member_form\">\n";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"check\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";
	$html .= "<tr>\n";
	$html .= "<td class=\"member_form_menu\">教室長ID</td>\n";
	$html .= "<td class=\"member_form_menu\">教室コード</td>\n";
	$html .= "<td class=\"member_form_menu\">決定</td>\n";
	$html .= "<td class=\"member_form_menu\">戻る</td>\n";
	$html .= "</tr>\n";
	$html .= "<tr>\n";
	$html .= "<td class=\"member_form_cell\"><input type=\"text\" name=\"school_id\"></td>\n";
	$html .= "<td class=\"member_form_cell\"><input type=\"text\" name=\"school_code\"></td>\n";
	$html .= "<td class=\"member_form_cell\"><input type=\"submit\" value=\"決定\"></td>\n";
	$html .= "</form>\n";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"member_list\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"back\">\n";
	$html .= "<td class=\"member_form_cell\"><input type=\"submit\" value=\"戻る\"></td>\n";
	$html .= "</tr>\n";
	$html .= "</form>\n";
	$html .= "</table><br>\n";

	$html .= "転校先の教室長IDまたは教室コードを入力し、決定ボタンをクリックしてください。<br>\n";

	return $html;
}

/**
 * 会社設定フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function set_enterprise() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql = "SELECT enterprise.enterprise_num,school.school_num,school.cram_school_name,school.school_code,enterprise.enterprise_name,manager.manager_id FROM " .
		T_ENTERPRISE . " enterprise," .  T_SCHOOL . " school," . T_MANAGER . " manager," . T_AUTHORITY . " authority" .
		" WHERE enterprise.state!='1' AND school.state!='1' AND manager.state!='1' AND authority.state!='1'" .
		" AND school.school_num=authority.belong_num" .
		" AND enterprise.enterprise_num=school.enterprise_num" .
		" AND authority.manager_level='4'" .
		" AND authority.manager_num=manager.manager_num" .
		"";
	if ($_POST[school_id]) {
		$sql .= " AND manager.manager_id='".$_POST[school_id]."'";
	}
	if ($_POST[school_code]) {
		$sql .= " AND school.school_code='".$_POST[school_code]."'";
	}
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
		$list = $cdb->fetch_assoc($result);
	}

	$html .= "以下の内容で、転校処理をしてもよろしいですか？<br>\n";
	$html .= "<table class=\"member_form\">\n";
	$html .= "<tr>\n";
	$html .= "<td class=\"member_form_menu\">教室長ID</td>\n";
	$html .= "<td class=\"member_form_menu\">教室コード</td>\n";
	$html .= "<td class=\"member_form_menu\">教室名</td>\n";
	$html .= "</tr>\n";
	$html .= "<tr>\n";
	$html .= "<td class=\"member_form_cell\">".$list[manager_id]."</td>\n";
	$html .= "<td class=\"member_form_cell\">".$list[school_code]."</td>\n";
	$html .= "<td class=\"member_form_cell\">".$list[cram_school_name]."</td>\n";
	$html .= "</tr>\n";
	$html .= "</table><br>\n";
	$enterprise_num = $list[enterprise_num];
	$school_num = $list[school_num];

	if (count($_SESSION[skip_list]) > 1) {
		$in = implode(",",$_SESSION[skip_list]);
	} else {
		foreach ($_SESSION[skip_list] as $val) { $in = $val; }
	}
	$sql = "SELECT * FROM " . T_GUARDIAN . " guardian," . T_ENTERPRISE . " enterprise," . T_SCHOOL . " school" .
		" WHERE guardian.state='0' AND enterprise.state='0' AND school.state='0'" .
		" AND enterprise.enterprise_num=guardian.enterprise_num" .
		" AND school.school_num=guardian.school_num" .
		" AND guardian_num IN(".$in.")";
	if ($result = $cdb->query($sql)) {
		$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" name=\"form_skip\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"change\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"change\">\n";
		$html .= "<input type=\"hidden\" name=\"enterprise_num\" value=\"".$enterprise_num."\">\n";
		$html .= "<input type=\"hidden\" name=\"school_num\" value=\"".$school_num."\">\n";
		if ($ERROR) { $html .= ERROR($ERROR); }
		$html .= "<table class=\"member_form\" style=\"float:left;\">\n";
		$html .= "<tr class=\"member_form_menu\">\n";
		$html .= "<td>保護者ID</td>\n";
		$html .= "<td>保護者名</td>\n";
		$html .= "<td>加盟店</td>\n";
		$html .= "<td>教室</td>\n";
		$html .= "<td>生徒ID</td>\n";
		$html .= "<td>生徒名</td>\n";
		$html .= "</tr>\n";

		while ($list = $cdb->fetch_assoc($result)) {
			$sql2 = "SELECT student_id,student_name FROM " . T_STUDENT .
				" WHERE guardian_num='$list[guardian_num]' AND state!='1'";
			if ($result2 = $cdb->query($sql2)) {
				$max = $cdb->num_rows($result2);
			}
			foreach($list as $key => $val) { $list[$key] = replace_decode($val); }
			if ($max > 1) { $rowspan = " rowspan=\"".$max."\""; } else { $rowspan = ""; }
			$html .= "<tr class=\"member_form_cell\">\n";
			$html .= "<td".$rowspan.">".$list[guardian_id]."</td>\n";
			$html .= "<td".$rowspan.">".$list[guardian_name]."</td>\n";
			$html .= "<td".$rowspan.">".$list[enterprise_name]."</td>\n";
			$html .= "<td".$rowspan.">".$list[cram_school_name]."</td>\n";
			$i=0;
			while ($list2 = $cdb->fetch_assoc($result2)) {
				if ($i!=0) { $html .= "</tr>\n"; }
				if ($i!=0) { $html .= "<tr class=\"member_form_cell\">\n"; }
				$html .= "<td>".$list2[student_id]."</td>\n";
				$html .= "<td>".$list2[student_name]."</td>\n";
				if ($i!=0) { $html .= "</tr>\n"; }
				$i++;
			}
			if ($i == 0) {
				$html .= "<td>--</td>\n";
				$html .= "<td>--</td>\n";
				$html .= "</tr>\n";
			}
		}
		$html .= "</table>\n";
		$html .= "<br style=\"clear:left;\">";
		$html .= "<input type=\"submit\" value=\"はい\">";
		$html .= "</form>";
		$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"member_list\">\n";
		$html .= "<input type=\"submit\" value=\"戻る\">\n";
		$html .= "</form>";
	}
	return $html;
}

/**
 * SESSIONから会社を削除
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
	global $L_SEARCH_TEACHER,$L_ORDER_TEACHER,$L_DESC,$L_PAGE_VIEW;

	if (!MODE || MODE == "member_list") {
		foreach ($L_ORDER_TEACHER as $key => $val){
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
		foreach ($L_SEARCH_TEACHER as $key => $val){
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
		$sub_session_html .= "保護者名\n";
		$sub_session_html .= "<input type=\"text\" name=\"s_student_name\" value=\"".$_SESSION[sub_session][s_student_name]."\">";
		$sub_session_html .= "<input type=\"submit\" value=\"Search\">\n";
		$sub_session_html .= "</td>\n";
		$sub_session_html .= "</form>\n";
		$sub_session_html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
		$sub_session_html .= "<input type=\"hidden\" name=\"action\" value=\"like_reset\">\n";
		$sub_session_html .= "<input type=\"hidden\" name=\"s_enterprise_name\" value=\"\">\n";
		$sub_session_html .= "<input type=\"hidden\" name=\"s_school_name\" value=\"\">\n";
		$sub_session_html .= "<input type=\"hidden\" name=\"s_student_name\" value=\"\">\n";
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
	$html .= "$sub_session_html";
	$html .= "</tr>\n";
	$html .= "</table><br>\n";
	$html .= "</div>\n";

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
	if (strlen($_POST[s_order])&&strlen($_POST[s_desc])&&strlen($_POST[s_page_view])||strlen($_POST[s_enterprise_name])||strlen($_POST[s_school_name])||strlen($_POST[s_student_name])) { unset($_SESSION[sub_session][s_page]); }
	if (strlen($_POST[s_page])) { $_SESSION[sub_session][s_page] = $_POST[s_page]; }
	if (strlen($_POST[s_like])) { $_SESSION[sub_session][s_like] = $_POST[s_like]; }
	if (strlen($_POST[s_word])) { $_SESSION[sub_session][s_word] = $_POST[s_word]; }
	if (ACTION != "make_csv") {
		if (isset($_POST[s_enterprise_name])) { $_SESSION[sub_session][s_enterprise_name] = $_POST[s_enterprise_name]; }
		if (isset($_POST[s_school_name])) { $_SESSION[sub_session][s_school_name] = $_POST[s_school_name]; }
		if (isset($_POST[s_student_name])) { $_SESSION[sub_session][s_student_name] = $_POST[s_student_name]; }
	}
	if (ACTION == "skip_reset") { unset($_SESSION[skip_list]); return; }
	if (ACTION == "like_reset") { unset($_SESSION[sub_session][s_like]); unset($_SESSION[sub_session][s_word]); return; }

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
	if (ACTION == "next_step"&&!$_SESSION[skip_list]) { $ERROR[] = "転校処理対象が確認できません。"; return $ERROR; }
	if (!$_SESSION[skip_list]) { unset($_SESSION[skip_list]); }
	return;
}

/**
 * メンバーズ一覧作成
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function member_list($ERROR) {

	global $L_PAGE_VIEW;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if ($_SESSION[set_enterprise]===NULL) {
		$sql = "SELECT * FROM " . T_GUARDIAN . " guardian," . T_ENTERPRISE . " enterprise," . T_SCHOOL . " school" .
			" WHERE guardian.state!='1'";
		$sql .= " AND enterprise.enterprise_num=guardian.enterprise_num";
		$sql .= " AND school.school_num=guardian.school_num";
	} elseif ($_SESSION[set_enterprise][school_num]===NULL) {
		$html .= "<p style=\"font-size:12px\">現在、加盟店[<strong style=\"color:#ff0000;\">".$_SESSION[set_enterprise][enterprise_name]."</strong>]にセットされています。</p>\n";
		$sql = "SELECT * FROM " . T_GUARDIAN . " guardian," . T_SCHOOL . " school," . T_ENTERPRISE . " enterprise" .
			" WHERE guardian.state!='1' AND school.state!='1' AND enterprise.state!='1'" .
			" AND guardian.enterprise_num='" . $_SESSION[set_enterprise][enterprise_num] . "'" .
			" AND guardian.school_num=school.school_num" .
			" AND enterprise.enterprise_num=school.enterprise_num";
	} else {
		$html .= "<p style=\"font-size:12px\">現在、加盟店[<strong style=\"color:#ff0000;\">".$_SESSION[set_enterprise][enterprise_name]."</strong>]/教室名[<strong style=\"color:#ff0000;\">" .
			$_SESSION[set_enterprise][school_name] . "</strong>]にセットされています。</p>\n";
		$sql = "SELECT * FROM " . T_GUARDIAN . " guardian," . T_SCHOOL . " school," . T_ENTERPRISE . " enterprise" .
			" WHERE guardian.state!='1' AND school.state!='1' AND enterprise.state!='1'" .
			" AND guardian.school_num='" . $_SESSION[set_enterprise][school_num] . "'" .
			" AND guardian.school_num=school.school_num" .
			" AND enterprise.enterprise_num=school.enterprise_num";
	}
	if ($_SESSION['sub_session']['s_enterprise_name']) {
		$sql .= " AND enterprise.enterprise_name LIKE '%".$_SESSION['sub_session']['s_enterprise_name']."%'";
	}
	if ($_SESSION['sub_session']['s_school_name']) {
		$sql .= " AND school.cram_school_name LIKE '%".$_SESSION['sub_session']['s_school_name']."%'";
	}
	if ($_SESSION['sub_session']['s_student_name']) {
		$sql .= " AND guardian.guardian_name LIKE '%".$_SESSION['sub_session']['s_student_name']."%'";
	}
/*
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"button\" value=\"ダウンロード\" onclick=\"exports($page);\">";
	$html .= "</form>\n";
*/
	if ($_SESSION['sub_session']['s_word']) {
		if ($_SESSION['sub_session']['s_like'] == "id") { $like_name = "manager.manager_id"; }
		elseif ($_SESSION['sub_session']['s_like'] == "enterprise") { $like_name = "enterprise.enterprise_name"; }
		elseif ($_SESSION['sub_session']['s_like'] == "school") { $like_name = "school.cram_school_name"; }
		elseif ($_SESSION['sub_session']['s_like'] == "teacher") { $like_name = "manager.manager_name"; }
		$sql .= " AND " . $like_name . " LIKE '%".$_SESSION['sub_session']['s_word']."%'";
	}
	if ($result = $cdb->query($sql)) {
		$list_count = $cdb->num_rows($result);
	}
	if ($_SESSION[sub_session][s_order] == 1) {
		$sql .= " ORDER BY guardian.guardian_id";
	} elseif ($_SESSION[sub_session][s_order] == 2) {
		$sql .= " ORDER BY guardian.guardian_name";
	} else {
		$sql .= " ORDER BY guardian.guardian_num";
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
	}
	if (!$max) {
		$html .= "現在、転校可能情報は存在しません。";
		return $html;
	}
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" name=\"form_skip\">\n";
	$html .= "<div style=\"float:left;\">保護者数($list_count):PAGE[$page/$max_page]</div>\n";
	$html .= "<input type=\"hidden\" name=\"s_page\" value=\"$page\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"member_list\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";

	if ($back > 0) {
		$html .= "<input type=\"button\" value=\"前のページ\" onclick=\"export_back($back);\">";
	}
	if ($page < $max_page) {
		$html .= "<input type=\"button\" value=\"次のページ\" onclick=\"export_next($next);\">\n";
	}

	$html .= "<br style=\"clear:left;\">";
	if ($ERROR) { $html .= ERROR($ERROR); }
	$html .= "<table class=\"member_form\" style=\"float:left;\">\n";
	$html .= "<tr class=\"member_form_menu\">\n";
	$html .= "<td style=\"height:40px;\">対象</td>\n";
	$html .= "<td>保護者ID</td>\n";
	$html .= "<td>保護者名</td>\n";
	$html .= "<td>加盟店</td>\n";
	$html .= "<td>教室</td>\n";
	$html .= "<td>生徒ID</td>\n";
	$html .= "<td>生徒名</td>\n";
	$html .= "</tr>\n";

	while ($list = $cdb->fetch_assoc($result)) {
		$sql2 = "SELECT student_id,student_name FROM " . T_STUDENT .
			" WHERE guardian_num='$list[guardian_num]' AND state!='1'";
		if ($result2 = $cdb->query($sql2)) {
			$max = $cdb->num_rows($result2);
		}
		foreach($list as $key => $val) { $list[$key] = replace_decode($val); }
		if (is_array($_SESSION[skip_list])) {
			if (array_search($list[guardian_num],$_SESSION[skip_list]) !== FALSE) {
				$checked = " checked";
			} else {
				$checked = "";
			}
		} else {
			$checked = "";
		}
		if ($max > 1) { $rowspan = " rowspan=\"".$max."\""; } else { $rowspan = ""; }
		$html .= "<tr class=\"member_form_cell\">\n";
		$html .= "<td style=\"height:26px;\"".$rowspan."><input type=\"hidden\" name=\"target_list[]\" value=\"$list[guardian_num]\">";
		$html .= "<input type=\"checkbox\" name=\"skip_list[]\" value=\"$list[guardian_num]\"$checked></td>\n";
		$html .= "<td".$rowspan.">".$list[guardian_id]."</td>\n";
		$html .= "<td".$rowspan.">".$list[guardian_name]."</td>\n";
		$html .= "<td".$rowspan.">".$list[enterprise_name]."</td>\n";
		$html .= "<td".$rowspan.">".$list[cram_school_name]."</td>\n";
		$i=0;
		while ($list2 = $cdb->fetch_assoc($result2)) {
			if ($i!=0) { $html .= "</tr>\n"; }
			if ($i!=0) { $html .= "<tr class=\"member_form_cell\">\n"; }
			$html .= "<td>".$list2[student_id]."</td>\n";
			$html .= "<td>".$list2[student_name]."</td>\n";
			if ($i!=0) { $html .= "</tr>\n"; }
			$i++;
		}
		if ($i == 0) {
			$html .= "<td>--</td>\n";
			$html .= "<td>--</td>\n";
			$html .= "</tr>\n";
		}
	}
	$html .= "</table>\n";
	$html .= "<br style=\"clear:left;\">";
	$html .= "<input type=\"button\" value=\"転校対象者決定\" onclick=\"next_step($page);\">";
	$html .= "<input type=\"button\" value=\"リセット\" onclick=\"skip_reset();\">\n";
	$html .= "</form>";

	return $html;
}

/**
 * 確認する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function check() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (!$_POST[school_id] && !$_POST[school_code]) { $ERROR[] = "教室ID、教室コードが未入力です。"; }
	if ($ERROR) { return $ERROR; }

	$sql = "SELECT enterprise.enterprise_num,school.school_num,school.cram_school_name,enterprise.enterprise_name,manager.manager_id FROM " .
		T_ENTERPRISE . " enterprise," .  T_SCHOOL . " school," . T_MANAGER . " manager," . T_AUTHORITY . " authority" .
		" WHERE enterprise.state!='1' AND school.state!='1' AND manager.state!='1' AND authority.state!='1'" .
		" AND school.school_num=authority.belong_num" .
		" AND enterprise.enterprise_num=school.enterprise_num" .
		" AND authority.manager_level='4'" .
		" AND authority.manager_num=manager.manager_num" .
		"";
	if ($_POST[school_id]) {
		$sql .= " AND manager.manager_id='".$_POST[school_id]."'";
	}
	if ($_POST[school_code]) {
		$sql .= " AND school.school_code='".$_POST[school_code]."'";
	}
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
	}
	if (!$max) { $ERROR[] = "入力された、教室長IDまたは教室コードは不正です。"; }

	return $ERROR;

}

/**
 * DB更新・削除 処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function change() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (!$_SESSION[skip_list]) {
		$ERROR[] = "始めからやり直してください。";
		return $ERROR;
	}
/*
	影響範囲

	guardian
	student

	リセット
	student_group_list	→	state:1
	announce			→	state:1
	question			→	state:1


	更新
	study_record
	study_total_time
	use_course
	finish_unit
	prospect

*/

	foreach ($_SESSION[skip_list] as $val) {
		$sql = "SELECT enterprise_num,school_num FROM " . T_GUARDIAN .
			" WHERE guardian_num='".$val."'";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$LOG[guardian_num] = $val;
			$LOG[old_school_num] = $list[school_num];
			$LOG[new_school_num] = $_POST[school_num];
			$LOG[move_time] = "now()";

			$ERROR = $cdb->insert(T_MOVE_LOG,$LOG);
		}

		$in_sql = "SELECT student_num FROM " . T_STUDENT .
			" WHERE guardian_num='".$val."' AND state='0'";

		$INSERT_DATA[enterprise_num] = $_POST[enterprise_num];
		$INSERT_DATA[school_num] = $_POST[school_num];

		$where = " WHERE guardian_num='".$val."' AND state='0'";

		$ERROR = $cdb->update(T_GUARDIAN,$INSERT_DATA,$where);

		$ERROR = $cdb->update(T_STUDENT,$INSERT_DATA,$where);

		$INSERT_DATA2[enterprise_num] = $_POST[enterprise_num];
		$INSERT_DATA2[school_num] = $_POST[school_num];
		$INSERT_DATA2[state] = 1;

		$INSERT_DATA3[update_date] = "new()";
		$INSERT_DATA3[state] = 1;

		$where = " WHERE state='1' AND student_num IN(".$in_sql.")";

		$ERROR = $cdb->update(T_STUDENT_GROUP_LIST,$INSERT_DATA2,$where);
		$or = "address_level='6' AND address_type='3' AND state='0'";
		$where = " WHERE state='1' AND address_level='7' OR (".$or.") AND address_num IN(".$in_sql.")";

		$ERROR = $cdb->update(T_ANNOUNCEMENT,$INSERT_DATA3,$where);
		$where = " WHERE state='1' AND type_num='0' AND student_num IN(".$in_sql.")";
//		$where = " WHERE type_num='0' AND user_num IN(".$in_sql.")";

		$ERROR = $cdb->update(T_QUESTION,$INSERT_DATA,$where);

		$where = " WHERE student_num IN(".$in_sql.")";

		$ERROR = $cdb->update(T_STUDY_RECODE,$INSERT_DATA,$where);

		$ERROR = $cdb->update(T_STUDY_TOTAL_TIME,$INSERT_DATA,$where);
		$where = " WHERE state='0' AND student_num IN(".$in_sql.")";

		$ERROR = $cdb->update(T_USE_COURSE,$INSERT_DATA,$where);

		$ERROR = $cdb->update(T_FINISH_UNIT,$INSERT_DATA,$where);

		$ERROR = $cdb->update(T_PROSPECT,$INSERT_DATA,$where);
	}

	unset($_SESSION[skip_list]);

	return $ERROR;
}

/**
 *
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function back() {
	return $ERROR;
}

/**
 *
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string
 */
function fin() {
	$html .= "終了";
	return $html;
}

/**
 * CSVを出力する機能
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
	$filename = "teacher_list.txt";

	header("Cache-Control: public");
	header("Pragma: public");
	header("Content-disposition: attachment;filename=$filename");
	if (stristr($HTTP_USER_AGENT, "MSIE")) {
		header("Content-Type: text/octet-stream");
	} else {
		header("Content-Type: application/octet-stream;");
	}

	//	head line (一行目)
	$csv_head = "\"先生名\"\t";
	$csv_head .= "\"先生名ふりがな\"\t";
	$csv_head .= "\"先生ID\"\t";
	$csv_head .= "\"先生パスワード\"\t";
	$csv_head .= "\"教務ツール\"\t";
	$csv_head .= "\"教室名\"\t";
	$csv_head .= "\"教室コード\"\t";
	$csv_head .= "\"法人名\"\t";
	$csv_head .= "\"企業コード\"\t";
	$csv_head .= "\"住所\"\t";
	$csv_head .= "\"電話番号\"\t";
	$csv_head .= "\"メールアドレス\"\t";
	$csv_head .= "\"携帯メール\"\t";
	$csv_head .= "\"備考\"\n";

	if ($_SESSION[skip_list]) {
		foreach ($_SESSION[skip_list] as $val) {
			$where .= " AND manager.manager_num!='$val'";
		}
	}
	if ($_SESSION['sub_session']['s_enterprise_name']) {
		$where .= " AND enterprise.enterprise_name LIKE '%".$_SESSION['sub_session']['s_enterprise_name']."%'";
	}
	if ($_SESSION['sub_session']['s_school_name']) {
		$where .= " AND school.cram_school_name LIKE '%".$_SESSION['sub_session']['s_school_name']."%'";
	}
	if ($_SESSION['sub_session']['s_manager_name']) {
		$where .= " AND manager.manager_name LIKE '%".$_SESSION['sub_session']['s_manager_name']."%'";
	}
	$sql = "SELECT enterprise.enterprise_name,enterprise.enterprise_code,school.cram_school_name,school.school_code," .
		"manager.manager_num,manager.manager_id, manager.manager_name,manager.manager_kana,manager.manager_email,manager.manager_mobile_email,manager.def," .
		"manager.zip,manager.prf,manager.city,manager.add1,manager.add2,manager.contact,manager.remarks" .
		" FROM " . T_ENTERPRISE . " enterprise," . T_SCHOOL . " school," . T_MANAGER . " manager," . T_AUTHORITY . " authority" .
		" WHERE enterprise.state!='1' AND manager.state!='1' AND authority.state!='1'" .
		" AND school.school_num=authority.belong_num" .
		" AND enterprise.enterprise_num=school.enterprise_num" .
		" AND authority.manager_level='5'" .
		" AND authority.manager_num=manager.manager_num" .
		" AND enterprise.enterprise_num!='0'" .
		"";
	if ($_SESSION['sub_session']['s_word']) {
		if ($_SESSION['sub_session']['s_like'] == "id") { $like_name = "manager.manager_id"; }
		elseif ($_SESSION['sub_session']['s_like'] == "enterprise") { $like_name = "enterprise.enterprise_name"; }
		elseif ($_SESSION['sub_session']['s_like'] == "school") { $like_name = "school.cram_school_name"; }
		elseif ($_SESSION['sub_session']['s_like'] == "teacher") { $like_name = "manager.manager_name"; }
		$sql .= " AND " . $like_name . " LIKE '%".$_SESSION['sub_session']['s_word']."%'";
	}
	if ($_SESSION[set_enterprise][enterprise_num]!==NULL) {
		$sql .= " AND enterprise.enterprise_num='".$_SESSION[set_enterprise][enterprise_num]."'";
	}
	if ($_SESSION[set_enterprise][school_num]!==NULL) {
		$sql .= " AND school.school_num='".$_SESSION[set_enterprise][school_num]."'";
	}
	$sql .= $where;
	if ($_SESSION[sub_session][s_order] == 1) {
		$sql .= " ORDER BY manager.manager_id";
	} elseif ($_SESSION[sub_session][s_order] == 2) {
		$sql .= " ORDER BY manager.manager_name";
	} else {
		$sql .= " ORDER BY manager.manager_num";
	}
	if ($_SESSION[sub_session][s_desc]) {
		$sql .= " DESC";
	}


	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {

			$address = $list[zip];
			$address .= "　";
			$address .= $L_PRF[$list[prf]];
			$address .= "　";
			$address .= $list[city];
			$address .= "　";
			$address .= $list[add1];
			if ($list[add2]) { $address .= "　"; $address .= $list[add2]; }
			$list[remarks] = ereg_replace("\n","",$list[remarks]);
			$list[remarks] = ereg_replace("\r","",$list[remarks]);
/*
			$csv_line .= "\"".replace_decode($list[manager_name])."\"\t";
			$csv_line .= "\"".replace_decode($list[manager_kana])."\"\t";
			$csv_line .= "\"".replace_decode($list[manager_id])."\"\t";
			$csv_line .= "\"".replace_decode($list[def])."\"\t";
			$csv_line .= "\"".replace_decode($schooltool_status)."\"\t";
			$csv_line .= "\"".replace_decode($list[cram_school_name])."\"\t";
			$csv_line .= "\"".replace_decode($list[school_code])."\"\t";
			$csv_line .= "\"".replace_decode($list[enterprise_name])."\"\t";
			$csv_line .= "\"".replace_decode($list[enterprise_code])."\"\t";
			$csv_line .= "\"".replace_decode($address)."\"\t";
			$csv_line .= "\"".replace_decode($list[contact])."\"\t";
			$csv_line .= "\"".replace_decode($list[manager_email])."\"\t";
			$csv_line .= "\"".replace_decode($list[manager_mobile_email])."\"\t";
			$csv_line .= "\"".replace_decode($list[remarks])."\"\n";
*/
			$csv_line .= "\"".$list[manager_name]."\"\t";
			$csv_line .= "\"".$list[manager_kana]."\"\t";
			$csv_line .= "\"".$list[manager_id]."\"\t";
			$csv_line .= "\"".$list[def]."\"\t";
			$csv_line .= "\"".$schooltool_status."\"\t";
			$csv_line .= "\"".$list[cram_school_name]."\"\t";
			$csv_line .= "\"".$list[school_code]."\"\t";
			$csv_line .= "\"".$list[enterprise_name]."\"\t";
			$csv_line .= "\"".$list[enterprise_code]."\"\t";
			$csv_line .= "\"".$address."\"\t";
			$csv_line .= "\"".$list[contact]."\"\t";
			$csv_line .= "\"".$list[manager_email]."\"\t";
			$csv_line .= "\"".$list[manager_mobile_email]."\"\t";
			$csv_line .= "\"".$list[remarks]."\"\n";
		}
	}

//	echo $sql; return;
	$csv_head = mb_convert_encoding($csv_head,"SJIS","UTF-8");
	$csv_line = mb_convert_encoding($csv_line,"SJIS","UTF-8");
	$csv_head = replace_decode_sjis($csv_head);
	$csv_line = replace_decode_sjis($csv_line);
	echo $csv_head;
	echo $csv_line;

	exit;
}
?>