<?
/**
 * ベンチャー・リンク　すらら
 *
 * 生徒管理　グループ管理
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
		elseif (ACTION == "add") { $ERROR = add(); }
		elseif (ACTION == "change") { $ERROR = change(); }
		elseif (ACTION == "plus") { $ERROR = plus(); }
		elseif (ACTION == "minus") { $ERROR = minus(); }
		elseif (ACTION == "set_enterprise") { $ERROR = set_enterprise(); }
		elseif (ACTION == "del_enterprise") { $ERROR = del_enterprise(); }
	}

	$html .= mode_menu();
	if (!MODE) { $html .= member_list(); }
	elseif (MODE == "set_enterprise") {
		$html .= select_enterprise();
	} elseif (MODE == "del_enterprise") {
		$html .= member_list();
	} elseif (MODE == "addform") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= addform($ERROR); }
		} elseif (ACTION == "add") {
			if (!$ERROR) { $html .= member_list(); }
			else { $html .= addform($ERROR); }
		} else {
			$html .= addform($ERROR);
		}
	} elseif (MODE == "view") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= viewform($ERROR); }
		} elseif (ACTION == "change") {
			if (!$ERROR) { $html .= member_list(); }
			else { $html .= viewform($ERROR); }
		} else {
			$html .= viewform($ERROR);
		}
	} elseif (MODE == "del") {
		if (ACTION == "change") {
			if (!$ERROR) { $html .= member_list(); }
			else { $html .= check_html(); }
		} else {
			$html .= check_html();
		}
	} elseif (MODE == "member_add") {
		if (ACTION == "plus") {  }
		$html .= member_add($ERROR);
	} elseif (MODE == "member_del") {
		if (ACTION == "minus") { $html .= viewform($ERROR); }
		else { $html .= member_del($ERROR); }
	} elseif (MODE == "member_list") { $html .= member_list(); }
	return $html;
}


/**
 * モードメニュー作成
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function mode_menu() {
	global $L_SEARCH_GROUP,$L_DESC,$L_PAGE_VIEW;

	if (!MODE || MODE == "member_list" || MODE == "del_enterprise") {
		foreach ($L_DESC as $key => $val){
			if ($_SESSION[sub_session][s_desc] == $key) { $sel = " selected"; } else { $sel = ""; }
			$s_desc_html .= "<option value=\"$key\"$sel>$val</option>\n";
		}
		foreach ($L_PAGE_VIEW as $key => $val){
			if ($_SESSION[sub_session][s_page_view] == $key) { $sel = " selected"; } else { $sel = ""; }
			$s_page_view_html .= "<option value=\"$key\"$sel>$val</option>\n";
		}
		foreach ($L_SEARCH_GROUP as $key => $val){
			if ($_SESSION[sub_session][s_like] == $key) { $sel = " selected"; } else { $sel = ""; }
			$s_like_html .= "<option value=\"$key\"$sel>$val</option>\n";
		}
		$sub_session_html .= "<td style=\"width:20px;\">&nbsp;</td>\n";
		$sub_session_html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
		$sub_session_html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
		$sub_session_html .= "<td>\n";
		$sub_session_html .= "ソート<select name=\"s_desc\">\n";
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
		$sub_session_html .= "グループ名\n";
		$sub_session_html .= "<input type=\"text\" name=\"s_group_name\" value=\"".$_SESSION[sub_session][s_group_name]."\">";
		$sub_session_html .= "<input type=\"submit\" value=\"Search\">\n";
		$sub_session_html .= "</td>\n";
		$sub_session_html .= "</form>\n";
		$sub_session_html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
		$sub_session_html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
		$sub_session_html .= "<input type=\"hidden\" name=\"s_enterprise_name\" value=\"\">\n";
		$sub_session_html .= "<input type=\"hidden\" name=\"s_school_name\" value=\"\">\n";
		$sub_session_html .= "<input type=\"hidden\" name=\"s_group_name\" value=\"\">\n";
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

	if ($_SESSION[set_enterprise]===NULL) {
		$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"set_enterprise\">\n";
		$html .= "<td><input type=\"submit\" value=\"加盟店指定\"></td>\n";
		$html .= "</form>\n";
	}

	if ($_SESSION[set_enterprise][school_num]!==NULL) {
		$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"addform\">\n";
		$html .= "<td><input type=\"submit\" value=\"新規登録\"></td>\n";
		$html .= "</form>\n";
	}
	if ($_SESSION[set_enterprise]!==NULL) {
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

	$html .= $sub_session_html;
	$html .= "</tr>\n";
	$html .= "</table><br>\n";
	$html .= "</div>\n";

	return $html;
}

/**
 * SESSION情報設定
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function sub_session() {
	if (strlen($_POST[s_desc])) { $_SESSION[sub_session][s_desc] = $_POST[s_desc]; }
	if (strlen($_POST[s_page_view])) { $_SESSION[sub_session][s_page_view] = $_POST[s_page_view]; }
	if (strlen($_POST[s_order])&&strlen($_POST[s_desc])&&strlen($_POST[s_page_view])) { unset($_SESSION[sub_session][s_page]); }
	if (strlen($_POST[s_page])) { $_SESSION[sub_session][s_page] = $_POST[s_page]; }
	if (strlen($_POST[s_like])) { $_SESSION[sub_session][s_like] = $_POST[s_like]; } else { unset($_SESSION[sub_session][s_like]); }
	if (strlen($_POST[s_word])) { $_SESSION[sub_session][s_word] = $_POST[s_word]; } else { unset($_SESSION[sub_session][s_word]); }
	if (isset($_POST[s_enterprise_name])) { $_SESSION[sub_session][s_enterprise_name] = $_POST[s_enterprise_name]; }
	if (isset($_POST[s_school_name])) { $_SESSION[sub_session][s_school_name] = $_POST[s_school_name]; }
	if (isset($_POST[s_group_name])) { $_SESSION[sub_session][s_group_name] = $_POST[s_group_name]; }

	return;
}

/**
 * 会社一覧を作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function select_enterprise() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if ($_SESSION[set_enterprise]===NULL) {
		$sql = "SELECT enterprise_num,enterprise_name,enterprise_code FROM " . T_ENTERPRISE .
			" WHERE state!='1'" .
			" ORDER BY list_num,enterprise_kana;";
		$case = 1;
	} elseif ($_SESSION[set_enterprise][school_num]===NULL) {
		$html .= "<p style=\"font-size:12px\">現在、加盟店[<strong style=\"color:#ff0000;\">".$_SESSION[set_enterprise][enterprise_name]."</strong>]にセットされています。</p>\n";
		$sql = "SELECT school_num,cram_school_name,school_code FROM " . T_SCHOOL .
			" WHERE state!='1'" .
			" AND enterprise_num='".$_SESSION[set_enterprise][enterprise_num]."'" .
			" ORDER BY list_num;";
		$case = 2;
	} else {
		$html .= "<p style=\"font-size:12px\">現在、加盟店[<strong style=\"color:#ff0000;\">".$_SESSION[set_enterprise][enterprise_name]."</strong>]/教室名[<strong style=\"color:#ff0000;\">".
			$_SESSION[set_enterprise][school_name]."</strong>]にセットされています。</p>\n";
		$sql = "SELECT school_num,cram_school_name,school_code FROM " . T_SCHOOL .
			" WHERE state!='1'" .
			" AND enterprise_num='".$_SESSION[set_enterprise][enterprise_num]."'" .
			" ORDER BY list_num;";
		$case = 3;
	}

	if ($result = $cdb->query($sql)) {
		$max2 = $cdb->num_rows($result);
		if (!$max2) {
			$html = "現在登録加盟店情報が存在しません。<br>\n";
			return $html;
		}

		$html .= "<table class=\"member_form\">\n";
		$html .= "<tr>\n";
		$html .= "<td class=\"member_form_menu\">加盟店名</td>\n";
		if ($case > 1) {
			$html .= "<td class=\"member_form_menu\">教室名</td>\n";
			$i=0;
		}
		$html .= "<td class=\"member_form_menu\">セット</td>\n";
		$html .= "</tr>";

		while ($list = $cdb->fetch_assoc($result)) {
			$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" name=\"menu\">\n";
			$html .= "<input type=\"hidden\" name=\"action\" value=\"set_enterprise\">\n";
			$html .= "<tr>\n";
			if ($case == 1) {
				$html .= "<input type=\"hidden\" name=\"enterprise_num\" value=\"$list[enterprise_num]\">\n";
				$html .= "<input type=\"hidden\" name=\"enterprise_name\" value=\"$list[enterprise_name]\">\n";
				$html .= "<input type=\"hidden\" name=\"enterprise_code\" value=\"$list[enterprise_code]\">\n";
				$html .= "<td class=\"member_form_cell\">$list[enterprise_name]</td>\n";
				$html .= "<td class=\"member_form_cell\"><input type=\"submit\" value=\"セット\"></td>\n";
			} elseif ($case > 1) {
				$html .= "<input type=\"hidden\" name=\"school_num\" value=\"$list[school_num]\">\n";
				$html .= "<input type=\"hidden\" name=\"school_name\" value=\"$list[cram_school_name]\">\n";
				$html .= "<input type=\"hidden\" name=\"school_code\" value=\"$list[school_code]\">\n";
				if ($i == 0) {
					$html .= "<td class=\"member_form_cell\" rowspan=\"$max2\">".$_SESSION[set_enterprise][enterprise_name]."</td>\n";
					$i = 1;
				}

				$html .= "<td class=\"member_form_cell\">$list[cram_school_name]</td>\n";
				$html .= "<td class=\"member_form_cell\"><input type=\"submit\" value=\"セット\"></td>\n";
			}
			$html .= "</tr>\n";
			$html .= "</form>\n";
		}
	}

	$html .= "</table><br>\n";
	$html .= "設定する加盟店を選択してください。<br>\n";

	return $html;
}

/**
 * SESSIONに校舎IDを登録する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function set_enterprise() {
	if ($_POST[enterprise_num]!==NULL) { $_SESSION[set_enterprise][enterprise_num] = $_POST[enterprise_num]; }
	if ($_POST[enterprise_name]!==NULL) { $_SESSION[set_enterprise][enterprise_name] = $_POST[enterprise_name]; }
	if ($_POST[enterprise_code]!==NULL) { $_SESSION[set_enterprise][enterprise_code] = $_POST[enterprise_code]; }
	if ($_POST[school_num]!==NULL) { $_SESSION[set_enterprise][school_num] = $_POST[school_num]; }
	if ($_POST[school_name]!==NULL) { $_SESSION[set_enterprise][school_name] = $_POST[school_name]; }
	if ($_POST[school_code]!==NULL) { $_SESSION[set_enterprise][school_code] = $_POST[school_code]; }
	unset($_SESSION[sub_session]);
	return;
}

/**
 * SESSIONから校舎の情報を削除する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function del_enterprise() {
	unset($_SESSION[sub_session]);
	unset($_SESSION[set_enterprise]);
	return;
}

/**
 * メンバーのリストコンテンツのHTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function member_list() {

	global $L_PAGE_VIEW,$L_DISPLAY;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if ($_SESSION[set_enterprise]===NULL) {
		$sql = "SELECT group_list.sgl_num,group_list.group_name,group_list.display,group_list.remarks FROM " . T_STUDENT_GROUP_LIST ." group_list," . T_SCHOOL . " school," . T_ENTERPRISE . " enterprise" .
			" WHERE group_list.school_num=school.school_num" .
			" AND school.enterprise_num=enterprise.enterprise_num" .
			" AND group_list.state!='1' AND school.state!='1'";
	} elseif ($_SESSION[set_enterprise][school_num]===NULL) {
		$html .= "<p style=\"font-size:12px\">現在、加盟店[<strong style=\"color:#ff0000;\">".$_SESSION[set_enterprise][enterprise_name]."</strong>]にセットされています。</p>\n";
		$sql = "SELECT group_list.sgl_num,group_list.group_name,group_list.display,group_list.remarks FROM " . T_STUDENT_GROUP_LIST ." group_list," . T_SCHOOL . " school," . T_ENTERPRISE . " enterprise" .
			" WHERE enterprise.enterprise_num='" . $_SESSION[set_enterprise][enterprise_num] . "'" .
			" AND school.enterprise_num=enterprise.enterprise_num" .
			" AND group_list.school_num=school.school_num" .
			" AND group_list.state!='1' AND school.state!='1' AND enterprise.state!='1'";
	} else {
		$html .= "<p style=\"font-size:12px\">現在、加盟店[<strong style=\"color:#ff0000;\">".$_SESSION[set_enterprise][enterprise_name]."</strong>]/教室名[<strong style=\"color:#ff0000;\">" .
			$_SESSION[set_enterprise][school_name] . "</strong>]にセットされています。</p>\n";
		$sql = "SELECT group_list.sgl_num,group_list.group_name,group_list.display,group_list.remarks FROM " . T_STUDENT_GROUP_LIST ." group_list," . T_SCHOOL . " school," . T_ENTERPRISE . " enterprise" .
			" WHERE group_list.school_num='" . $_SESSION[set_enterprise][school_num] . "'" .
			" AND school.enterprise_num=enterprise.enterprise_num" .
			" AND group_list.school_num=school.school_num" .
			" AND group_list.state!='1' AND school.state!='1'";
	}

	if ($_SESSION['sub_session']['s_word']) {
		if ($_SESSION['sub_session']['s_like'] == "id") { $like_name = "manager.manager_id"; }
		elseif ($_SESSION['sub_session']['s_like'] == "enterprise") { $like_name = "enterprise.enterprise_name"; }
		elseif ($_SESSION['sub_session']['s_like'] == "school") { $like_name = "school.cram_school_name"; }
		elseif ($_SESSION['sub_session']['s_like'] == "group") { $like_name = "group_list.group_name"; }
		$sql .= " AND " . $like_name . " LIKE '%".$_SESSION['sub_session']['s_word']."%'";
	}
	if ($_SESSION['sub_session']['s_enterprise_name']) {
		$sql .= " AND enterprise.enterprise_name LIKE '%".$_SESSION['sub_session']['s_enterprise_name']."%'";
	}
	if ($_SESSION['sub_session']['s_school_name']) {
		$sql .= " AND school.cram_school_name LIKE '%".$_SESSION['sub_session']['s_school_name']."%'";
	}
	if ($_SESSION['sub_session']['s_group_name']) {
		$sql .= " AND group_list.group_name LIKE '%".$_SESSION['sub_session']['s_group_name']."%'";
	}
	if ($result = $cdb->query($sql)) {
		$list_count = $cdb->num_rows($result);
	}
	$sql .= " ORDER BY group_list.update_date";
	if ($_SESSION[sub_session][s_desc]) {
		$sql .= " DESC";
	}

	if ($L_PAGE_VIEW[$_SESSION[sub_session][s_page_view]]) { $page_view = $L_PAGE_VIEW[$_SESSION[sub_session][s_page_view]]; }
	else { $page_view = $L_PAGE_VIEW[0]; }
	$max_page = ceil($list_count/$page_view);
	if ($_SESSION[sub_session][s_page]) { $page = $_SESSION[sub_session][s_page]; }
	else { $page = 1; }
	$start = ($page - 1) * $page_view;
	$next = $page + 1;
	$back = $page - 1;

	$sql .= " LIMIT $start,$page_view;";

	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
		if (!$max) {
			$html .= "現在、登録グループは存在しません。";
			return $html;
		}
		$html .= "<div style=\"float:left;\">グループ数($list_count):PAGE[$page/$max_page]</div>\n";
		if ($back > 0) {
			$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" style=\"float:left;\">";
			$html .= "<input type=\"submit\" value=\"前のページ\">";
			$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
			$html .= "<input type=\"hidden\" name=\"s_page\" value=\"$back\">\n";
			$html .= "</form>";
		}
		if ($page < $max_page) {
			$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" style=\"float:left;\">";
			$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
			$html .= "<input type=\"hidden\" name=\"s_page\" value=\"$next\">\n";
			$html .= "<input type=\"submit\" value=\"次のページ\">";
			$html .= "</form>";
		}
		$html .= "<br style=\"clear:left;\">";
		$html .= "<table class=\"member_form\">\n";
		$html .= "<tr class=\"member_form_menu\">\n";
		$html .= "<td>状態</td>\n";
		$html .= "<td>グループ名</td>\n";
		$html .= "<td>備考</td>\n";
		$html .= "<td>&nbsp;</td>\n";
		$html .= "<td>&nbsp;</td>\n";
		$html .= "</tr>\n";
		while ($list = $cdb->fetch_assoc($result)) {
			foreach($list as $key => $val) { $list[$key] = replace_decode($val); }
			$html .= "<tr class=\"member_form_cell\">\n";
			$html .= "<td>{$L_DISPLAY[$list[display]]}</td>\n";
			$html .= "<td>$list[group_name]</td>\n";
			$html .= "<td>$list[remarks]</td>\n";
			$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"view\">\n";
			$html .= "<input type=\"hidden\" name=\"sgl_num\" value=\"$list[sgl_num]\">\n";
			$html .= "<td><input type=\"submit\" value=\"詳細\"></td>\n";
			$html .= "</form>\n";
			$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"del\">\n";
			$html .= "<input type=\"hidden\" name=\"sgl_num\" value=\"$list[sgl_num]\">\n";
			$html .= "<td><input type=\"submit\" value=\"削除\"></td>\n";
			$html .= "</form>\n";
			$html .= "</tr>\n";
		}
		$html .= "</table>\n";
	}
	return $html;
}

/**
 * 新規登録フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function addform($ERROR) {

	$html = "新規登録フォーム";

	if ($_POST) { foreach($_POST as $key => $val) { $$key = $val; } }

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(TEMP_GROUP_FORM);

//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	$INPUTS[GROUPNAME] = array('type'=>'text','name'=>'group_name','value'=>$group_name);
	$INPUTS[REMARKS] = array('type'=>'textarea','name'=>'remarks','cols'=>'50','rows'=>'5','value'=>$remarks);

	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("display");
	$newform->set_form_id("open");
	$newform->set_form_check($display);
	$newform->set_form_value('1');
	$open = $newform->make();
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("display");
	$newform->set_form_id("close");
	$newform->set_form_check($display);
	$newform->set_form_value('2');
	$close = $newform->make();
	$view = $open . "<label for=\"open\">表示</label> / " . $close . "<label for=\"close\">非表示</label>";
	$INPUTS[DISPLAY] = array('result'=>'plane','value'=>$view);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"追加確認\">";
	$html .= "<input type=\"reset\" value=\"クリア\">";
	$html .= "</form>\n";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"member_list\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}

/**
 * 確認する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーならば
 */
function check() {
	if (!$_POST[group_name]) { $ERROR[] = "グループ名が未入力です。"; }
	if (!$_POST[display]) { $ERROR[] = "表示モードが未入力です。"; }

	return $ERROR;
}

/**
 * 詳細画面
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function viewform($ERROR) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$A_TABLE = T_STUDENT_GROUP_LIST;
	$B_TABLE = T_STUDENT_GROUP;
	$C_TABLE = T_STUDENT;
	$action = ACTION;

	if ($action) {
		foreach ($_POST as $key => $val) {
			if ($key == "authority") { continue; }
			$$key = replace_decode($val);
		}
		if ($_POST[authority]) { $authority = $_POST[authority]; }
	} else {
		$sql = "SELECT * FROM $A_TABLE" .
			" WHERE sgl_num='$_POST[sgl_num]' AND state!='1' LIMIT 1;";
		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);
		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}
		foreach ($list as $key => $val) {
			$$key = replace_decode($val);
		}
		$L_AUTHORITY = explode("::",$authority);
		foreach ($L_AUTHORITY as $auth) {
			$new_authority[$auth] = $auth;
		}
		$authority = $new_authority;
	}

	$html = "詳細画面";

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";
	$html .= "<input type=\"hidden\" name=\"sgl_num\" value=\"$sgl_num\">\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(TEMP_GROUP_FORM);

	$INPUTS[GROUPNAME] = array('type'=>'text','name'=>'group_name','value'=>$group_name);
	$INPUTS[REMARKS] = array('type'=>'textarea','name'=>'remarks','cols'=>'50','rows'=>'5','value'=>$remarks);

	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("display");
	$newform->set_form_id("open");
	$newform->set_form_check($display);
	$newform->set_form_value('1');
	$open = $newform->make();
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("display");
	$newform->set_form_id("close");
	$newform->set_form_check($display);
	$newform->set_form_value('2');
	$close = $newform->make();
	$view = $open . "<label for=\"open\">表示</label> / " . $close . "<label for=\"close\">非表示</label>";
	$INPUTS[DISPLAY] = array('result'=>'plane','value'=>$view);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"変更確認\">";
	$html .= "<input type=\"reset\" value=\"クリア\">";
	$html .= "</form>\n";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"member_list\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	$sql = "SELECT * FROM $A_TABLE a,$B_TABLE b,$C_TABLE c" .
		" WHERE a.sgl_num=b.sgl_num AND b.sgl_num='$_POST[sgl_num]' AND b.student_num=c.student_num AND b.state!=1";
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
		$html .= "<hr>\n";
		$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"member_add\">\n";
		$html .= "<input type=\"hidden\" name=\"sgl_num\" value=\"$sgl_num\">\n";
		$html .= "<input type=\"hidden\" name=\"school_num\" value=\"$school_num\">\n";
		$html .= "<input type=\"submit\" value=\"グループにメンバーを追加する。\">\n";
		$html .= "</form>\n";
		$html .= "<hr>\n";
	}
	if ($max) {
		$html .= "<table class=\"member_form\">\n";
		$html .= "<tr class=\"member_form_menu\">\n";
		$html .= "<td>メンバー名</td>\n";
		$html .= "<td>&nbsp;</td>\n";
		$html .= "</tr>\n";

		while ($list = $cdb->fetch_assoc($result)) {
			$html .= "<tr class=\"member_form_cell\">\n";
			$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"member_del\">\n";
			$html .= "<input type=\"hidden\" name=\"sgl_num\" value=\"$list[sgl_num]\">\n";
			$html .= "<input type=\"hidden\" name=\"sg_num\" value=\"$list[sg_num]\">\n";
			$html .= "<td>$list[student_name]</td>\n";
			$html .= "<td><input type=\"submit\" value=\"削除\"></td>\n";
			$html .= "</form>\n";
			$html .= "</tr>\n";
		}
		$html .= "</table>\n";
	} else {
		$html .= "現在グループに属している生徒はいません。\n";
	}

	return $html;
}

/**
 * メンバー追加フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function member_add($ERROR) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$A_TABLE = T_STUDENT;
	$action = ACTION;
	foreach ($_POST as $key => $val) {
		if ($key == "authority") { continue; }
		$$key = replace_decode($val);
	}

	$sql = "SELECT * FROM $A_TABLE WHERE school_num='$school_num' AND state!='1'";
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
		if ($max) {
			$L_STUDENT[0] = "選択してください";
			while ($list = $cdb->fetch_assoc($result)) {
				$L_STUDENT[$list[student_num]] = $list[student_name];
			}
			$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" style=\"float:left;\">\n";
			$html .= "<input type=\"hidden\" name=\"action\" value=\"plus\">\n";
			$html .= "<input type=\"hidden\" name=\"sgl_num\" value=\"$sgl_num\">\n";
			$newform = new form_parts();
			$newform->set_form_type("select");
			$newform->set_form_name("student_num");
			$newform->set_form_array($L_STUDENT);
			$html .= $newform->make();
			$html .= "<input type=\"submit\" value=\"追加\">\n";
			$html .= "</form>\n";
		} else {
			$L_STUDENT[0] = "追加可能な生徒情報がありません。";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"member_add\">\n";
			$html .= "<input type=\"hidden\" name=\"sgl_num\" value=\"$sgl_num\">\n";
			$newform = new form_parts();
			$newform->set_form_type("select");
			$newform->set_form_name("student_num");
			$newform->set_form_array($L_STUDENT);
			$html .= $newform->make();
			$html .= "<input type=\"submit\" value=\"追加\">\n";
			$html .= "</form>\n";
		}
	}
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"view\">\n";
	$html .= "<input type=\"hidden\" name=\"sgl_num\" value=\"$sgl_num\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	$C_TABLE = T_STUDENT_GROUP_LIST;
	$D_TABLE = T_STUDENT_GROUP;
	$sql = "SELECT * FROM $C_TABLE a,$D_TABLE b,$A_TABLE c WHERE a.sgl_num=b.sgl_num AND b.sgl_num='$_POST[sgl_num]' AND b.student_num=c.student_num AND b.state!=1";
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
		$html .= "<hr>\n";
	}
	if ($max) {
		$html .= "<table class=\"member_form\">\n";
		$html .= "<tr class=\"member_form_menu\">\n";
		$html .= "<td>メンバー名</td>\n";
		$html .= "<td>&nbsp;</td>\n";
		$html .= "</tr>\n";

		while ($list = $cdb->fetch_assoc($result)) {
			$html .= "<tr class=\"member_form_cell\">\n";
			$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"member_del\">\n";
			$html .= "<input type=\"hidden\" name=\"sgl_num\" value=\"$list[sgl_num]\">\n";
			$html .= "<input type=\"hidden\" name=\"sg_num\" value=\"$list[sg_num]\">\n";
			$html .= "<td>$list[student_name]</td>\n";
			$html .= "<td><input type=\"submit\" value=\"削除\"></td>\n";
			$html .= "</form>\n";
			$html .= "</tr>\n";
		}
		$html .= "</table>\n";
	} else {
		$html .= "現在グループに属している生徒はいません。\n";
	}

	return $html;
}

/**
 * メンバー削除フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function member_del() {

	global $L_DISPLAY;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$A_TABLE = T_STUDENT_GROUP;
	$B_TABLE = T_STUDENT;

	$sql = "SELECT * FROM $A_TABLE a,$B_TABLE b WHERE a.student_num=b.student_num AND a.sg_num='$_POST[sg_num]' AND a.state!='1' LIMIT 1;";
	$result = $cdb->query($sql);
	$list = $cdb->fetch_assoc($result);

	$name = $list[student_name];
	$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"minus\">\n";
	$HIDDEN .= "<input type=\"hidden\" name=\"sgl_num\" value=\"$_POST[sgl_num]\">\n";
	$HIDDEN .= "<input type=\"hidden\" name=\"sg_num\" value=\"$_POST[sg_num]\">\n";

	if (MODE != "member_del") { $button = "登録"; } else { $button = "削除"; }
	$html = "確認画面：以下の内容で{$button}してもよろしければ{$button}ボタンをクリックしてください。";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(TEMP_GROUP_MEMBER_FORM);

//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	$INPUTS[NAME] = array('result'=>'plane','value'=>$name);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" style=\"float:left\">\n";
	$html .= $HIDDEN;
	$html .= "<input type=\"submit\" value=\"$button\">\n";
	$html .= "</form>";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"view\">\n";
	$html .= "<input type=\"hidden\" name=\"sgl_num\" value=\"$_POST[sgl_num]\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}

/**
 * DB新規登録
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function add() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$TABLE = T_STUDENT_GROUP_LIST;
	$action = ACTION;

	foreach ($_POST as $key => $val) {
		if ($key == "action") { continue; }
		$INSERT_DATA[$key] = "$val";
	}
	$INSERT_DATA[school_num] = $_SESSION[set_enterprise][school_num];
	$INSERT_DATA[state] = "0";
	$INSERT_DATA[regist_date] = "now()";
	$INSERT_DATA[update_date] = "now()";

	$ERROR = $cdb->insert($TABLE,$INSERT_DATA);

	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
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
function plus() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (!$_POST[student_num]) { return $ERROR; }

	$TABLE = T_STUDENT_GROUP;

	foreach ($_POST as $key => $val) {
		if ($key == "action") { continue; }
		$INSERT_DATA[$key] = "$val";
	}
	$INSERT_DATA[state] = "0";

	$sql = "SELECT * FROM $TABLE WHERE sgl_num='$_POST[sgl_num]' AND student_num='$_POST[student_num]'";
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
	}

	if ($max) {
		$where = " WHERE sgl_num='$_POST[sgl_num]' AND student_num='$_POST[student_num]'";
		$ERROR = $cdb->update($TABLE,$INSERT_DATA,$where);
	} else {
		$ERROR = $cdb->insert($TABLE,$INSERT_DATA);
	}
	return $ERROR;
}

/**
 *
 * @author Azet
 * @return array エラーの場合
 */
function minus() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (!$_POST[sg_num]) { return $ERROR; }

	$TABLE = T_STUDENT_GROUP;

	$INSERT_DATA[state] = "1";

	$where = " WHERE sg_num='$_POST[sg_num]'";
	$ERROR = $cdb->update($TABLE,$INSERT_DATA,$where);
	return $ERROR;
}

/**
 * 確認フォームを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTMLフォーム
 */
function check_html() {

	global $L_DISPLAY;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$TABLE = T_STUDENT_GROUP_LIST;
	$action = ACTION;

	if ($action) {
		if ($_POST) {
			foreach ($_POST as $key => $val) {
				if ($key == "action") {
					if (MODE == "addform") { $val = "add"; }
					elseif (MODE == "view") { $val = "change"; }
				}
				$HIDDEN .= "<input type=\"hidden\" name=\"$key\" value=\"$val\">\n";
				$val = replace_decode($val);
				$$key = $val;
			}
		}
	} else {
		$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"change\">\n";
		$HIDDEN .= "<input type=\"hidden\" name=\"sgl_num\" value=\"$_POST[sgl_num]\">\n";
		$sql = "SELECT * FROM $TABLE" .
			" WHERE sgl_num='$_POST[sgl_num]' AND state!='1' LIMIT 1;";

		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);
		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}
		foreach ($list as $key => $val) {
			$$key = replace_decode($val);
		}
	}

	if (MODE != "del") { $button = "登録"; } else { $button = "削除"; }
	$html = "確認画面：以下の内容で{$button}してもよろしければ{$button}ボタンをクリックしてください。";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(TEMP_GROUP_FORM);

//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	$INPUTS[GROUPNAME] = array('result'=>'plane','value'=>$group_name);
	$INPUTS[REMARKS] = array('result'=>'plane','value'=>$remarks);
	$INPUTS[DISPLAY] = array('result'=>'plane','value'=>$L_DISPLAY[$display]);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" style=\"float:left\">\n";
	$html .= $HIDDEN;
	$html .= "<input type=\"submit\" value=\"$button\">\n";
	$html .= "</form>";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";

	if ($action) {
		$HIDDEN2 = explode("\n",$HIDDEN);
		foreach ($HIDDEN2 as $key => $val) {
			if (ereg("name=\"action\"",$val)) {
				$HIDDEN2[$key] = "<input type=\"hidden\" name=\"action\" value=\"back\">";
				break;
			}
		}
		$HIDDEN2 = implode("\n",$HIDDEN2);

		$html .= $HIDDEN2;
	} else {
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"member_list\">\n";
	}
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
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

	$TABLE = T_STUDENT_GROUP_LIST;
	$action = ACTION;

	if (MODE == "view") {
		foreach ($_POST as $key => $val) {
			if ($key == "action") { continue; }
			if ($key == "sgl_num") { continue; }
			$INSERT_DATA[$key] = "$val";
		}
		$INSERT_DATA[update_date] = "now()";

		$where = " WHERE sgl_num='$_POST[sgl_num]' LIMIT 1;";

		$ERROR = $cdb->update($TABLE,$INSERT_DATA,$where);
	} elseif (MODE == "del") {
		$INSERT_DATA[state] = "1";
		$INSERT_DATA[update_date] = "now()";
		$where = " WHERE sgl_num='$_POST[sgl_num]' LIMIT 1;";

		$ERROR = $cdb->update($TABLE,$INSERT_DATA,$where);
	}

	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}
?>