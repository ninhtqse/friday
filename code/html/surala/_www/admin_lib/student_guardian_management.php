<?php 
/**
 * ベンチャー・リンク　すらら
 *
 * 保護者・生徒管理　保護者管理
 *
 * @author Azet
 */

include_once("/data/home/www/decryt_field_db.php"); //add kaopiz 2020/25/06 load encrypt file
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
		elseif (ACTION == "set_enterprise") { $ERROR = set_enterprise(); }
		elseif (ACTION == "del_enterprise") { $ERROR = del_enterprise(); }
		elseif (ACTION == "db_session") { $ERROR = select_database(); }
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
	global $L_GUARDIAN_ORDER,$L_DESC,$L_PAGE_VIEW,$L_SELECT_LOG_DB;

	foreach ($L_SELECT_LOG_DB as $key => $val){
		if ($_SESSION['select_db'] == $val) { $sel = " selected"; } else { $sel = ""; }
		$s_db_html .= "<option value=\"".$key."\"".$sel.">".$val['NAME']."</option>\n";
	}

	if (!MODE || MODE == "member_list" || MODE == "del_enterprise") {

		foreach ($L_GUARDIAN_ORDER as $key => $val){
			if ($_SESSION['sub_session']['s_order'] == $key) { $sel = " selected"; } else { $sel = ""; }
			$s_order_html .= "<option value=\"".$key."\"".$sel.">".$val."</option>\n";
		}
		foreach ($L_DESC as $key => $val){
			if ($_SESSION['sub_session']['s_desc'] == $key) { $sel = " selected"; } else { $sel = ""; }
			$s_desc_html .= "<option value=\"".$key."\"".$sel.">".$val."</option>\n";
		}
		foreach ($L_PAGE_VIEW as $key => $val){
			if ($_SESSION['sub_session']['s_page_view'] == $key) { $sel = " selected"; } else { $sel = ""; }
			$s_page_view_html .= "<option value=\"".$key."\"".$sel.">".$val."</option>\n";
		}

		$sub_session_html .= "<td style=\"width:20px;\">&nbsp;</td>\n";
		$sub_session_html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
		$sub_session_html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
/*
		$sub_session_html .= "<span style=\"font-size:12px;\">絞り込み<select name=\"s_school\">\n";
		$sub_session_html .= "$s_school_html</select><select name=\"s_grade\">\n";
		$sub_session_html .= "$s_grade_html</select><select name=\"s_course\">\n";
		$sub_session_html .= "$s_course_html</select>\n";
*/
		$sub_session_html .= "<td>\n";
		$sub_session_html .= "ソート<select name=\"s_order\">\n";
		$sub_session_html .= $s_order_html."</select><select name=\"s_desc\">\n";
		$sub_session_html .= $s_desc_html."</select>表示数<select name=\"s_page_view\">\n";
		$sub_session_html .= $s_page_view_html."</select><input type=\"submit\" value=\"Set\">\n";
//		$sub_session_html .= "</span>\n";
		$sub_session_html .= "</td>\n";
		$sub_session_html .= "</form>\n";
	}

	$html .= "<div id=\"mode_menu\">\n";
	$html .= "<table cellpadding=0 cellspacing=0>\n";
	$html .= "<tr>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"member_list\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"db_session\">\n";
	$html .= "<td>閲覧DB：<select name=\"s_db_sel\" onchange=\"submit();\">".$s_db_html."</select></td>\n";

	if ($_SESSION['select_db']) {
		$html .= "</form>\n";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"member_list\">\n";
		$html .= "<td><input type=\"submit\" value=\"一覧表示\" style=\"float:left;\"></td>\n";
		$html .= "</form>\n";

		if ($_SESSION['set_enterprise']===NULL) {
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"set_enterprise\">\n";
			$html .= "<td><input type=\"submit\" value=\"法人指定\"></td>\n";
			$html .= "</form>\n";
		}

/*		if ($_SESSION['set_enterprise']['school_id']!==NULL
			&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__add",$_SESSION['authority'])===FALSE))
		) {
			$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"addform\">\n";
			$html .= "<td><input type=\"submit\" value=\"新規登録\"></td>\n";
			$html .= "</form>\n";
		}
*/		if ($_SESSION['set_enterprise']!==NULL) {
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"set_enterprise\">\n";
			$html .= "<td><input type=\"submit\" value=\"法人再指定\"></td>\n";
			$html .= "</form>\n";
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"del_enterprise\">\n";
			$html .= "<input type=\"hidden\" name=\"action\" value=\"del_enterprise\">\n";
			$html .= "<td><input type=\"submit\" value=\"法人指定解除\"></td>\n";
			$html .= "</form>\n";
		}

		$html .= $sub_session_html;
	}
	$html .= "</tr>\n";
	$html .= "</table><br>\n";
	$html .= "</div>\n";

	return $html;
}

/**
 * SESSIONに対してDBを選択
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function select_database() {
	global $L_SELECT_LOG_DB;

	unset($_SESSION['set_enterprise']);
	if (strlen($_POST['s_db_sel'])) { $_SESSION['select_db'] = $L_SELECT_LOG_DB[$_POST['s_db_sel']]; }

	if ($_POST['s_db_sel'] == '0') { unset($_SESSION['select_db']); }

	return;
}

/**
 * POSTに対してSESSION設定する
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function sub_session() {
	if (strlen($_POST['s_school'])) { $_SESSION['sub_session']['s_school'] = $_POST['s_school']; }
	if (strlen($_POST['s_grade'])) { $_SESSION['sub_session']['s_grade'] = $_POST['s_grade']; }
	if (strlen($_POST['s_course'])) { $_SESSION['sub_session']['s_course'] = $_POST['s_course']; }
	if (strlen($_POST['s_order'])) { $_SESSION['sub_session']['s_order'] = $_POST['s_order']; }
	if (strlen($_POST['s_desc'])) { $_SESSION['sub_session']['s_desc'] = $_POST['s_desc']; }
	if (strlen($_POST['s_page_view'])) { $_SESSION['sub_session']['s_page_view'] = $_POST['s_page_view']; }
	if (strlen($_POST['s_order'])&&strlen($_POST['s_desc'])&&strlen($_POST['s_page_view'])) { unset($_SESSION['sub_session']['s_page']); }
	if (strlen($_POST['s_page'])) { $_SESSION['sub_session']['s_page'] = $_POST['s_page']; }

	return;
}

/**
 * 会社選択メニュー作成
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function select_enterprise() {

	if (!$_SESSION['select_db']) { return; }

	//	DB接続
	$connect_db = new connect_db();
	$connect_db->set_db($_SESSION['select_db']);
	$ERROR = $connect_db->set_connect_db();
	if ($ERROR) {
		$html .= ERROR($ERROR);
	}

	if ($_SESSION['set_enterprise']===NULL) {
		$sql = "SELECT enterprise_id,enterprise_name FROM " . T_ENTERPRISE .
			" WHERE mk_flg!='1'" .
			" ORDER BY enterprise_id;";
		$case = 1;
	} elseif ($_SESSION['set_enterprise']['school_id']===NULL) {
		$html .= "<p style=\"font-size:12px\">現在、法人[<strong style=\"color:#ff0000;\">".$_SESSION['set_enterprise']['enterprise_name']."</strong>]にセットされています。</p>\n";
		$sql = "SELECT school_id,school_name FROM " . T_SCHOOL .
			" WHERE mk_flg!='1'" .
			" AND enterprise_id='".$_SESSION['set_enterprise']['enterprise_id']."'" .
			" ORDER BY school_id;";
		$case = 2;
	} else {
		$html .= "<p style=\"font-size:12px\">現在、法人[<strong style=\"color:#ff0000;\">".$_SESSION['set_enterprise']['enterprise_name']."</strong>]/校舎名[<strong style=\"color:#ff0000;\">".
			$_SESSION['set_enterprise']['school_name']."</strong>]にセットされています。</p>\n";
		$sql = "SELECT school_id,school_name FROM " . T_SCHOOL .
			" WHERE mk_flg!='1'" .
			" AND enterprise_id='".$_SESSION['set_enterprise']['enterprise_id']."'" .
			" ORDER BY school_id;";
		$case = 3;
	}

	if ($result = $connect_db->query($sql)) {
		$max = $connect_db->num_rows($result);
	}
	if (!$max) {
		$html = "現在登録法人情報が存在しません。<br>\n";
		return $html;
	}

	$html .= "<table class=\"member_form\">\n";
	$html .= "<tr>\n";
	$html .= "<td class=\"member_form_menu\">法人名</td>\n";
	if ($case > 1) {
		$html .= "<td class=\"member_form_menu\">校舎名</td>\n";
		$i=0;
	}
	$html .= "<td class=\"member_form_menu\">セット</td>\n";
	$html .= "</tr>";
	while ($list = $connect_db->fetch_assoc($result)) {
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"menu\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"set_enterprise\">\n";
		$html .= "<tr>\n";
		if ($case == 1) {
			$html .= "<input type=\"hidden\" name=\"enterprise_id\" value=\"".$list['enterprise_id']."\">\n";
			$html .= "<input type=\"hidden\" name=\"enterprise_name\" value=\"".$list['enterprise_name']."\">\n";
			$html .= "<td class=\"member_form_cell\">".$list['enterprise_name']."</td>\n";
			$html .= "<td class=\"member_form_cell\"><input type=\"submit\" value=\"セット\"></td>\n";
		} elseif ($case > 1) {
			$html .= "<input type=\"hidden\" name=\"school_id\" value=\"".$list['school_id']."\">\n";
			$html .= "<input type=\"hidden\" name=\"school_name\" value=\"".$list['school_name']."\">\n";
			if ($i == 0) {
				$html .= "<td class=\"member_form_cell\" rowspan=\"$max\">".$_SESSION['set_enterprise']['enterprise_name']."</td>\n";
				$i = 1;
			}

			$html .= "<td class=\"member_form_cell\">".$list['school_name']."</td>\n";
			$html .= "<td class=\"member_form_cell\"><input type=\"submit\" value=\"セット\"></td>\n";
		}
		$html .= "</tr>\n";
		$html .= "</form>\n";
	}
	$html .= "</table><br>\n";
	$html .= "設定する法人を選択してください。<br>\n";

	$connect_db->close();

	return $html;
}

/**
 * POSTに対してSESSIONに設定セうる
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function set_enterprise() {
	if ($_POST['enterprise_id']!==NULL) { $_SESSION['set_enterprise']['enterprise_id'] = $_POST['enterprise_id']; }
	if ($_POST['enterprise_name']!==NULL) { $_SESSION['set_enterprise']['enterprise_name'] = $_POST['enterprise_name']; }
	if ($_POST['enterprise_code']!==NULL) { $_SESSION['set_enterprise']['enterprise_code'] = $_POST['enterprise_code']; }
	if ($_POST['school_id']!==NULL) { $_SESSION['set_enterprise']['school_id'] = $_POST['school_id']; }
	if ($_POST['school_name']!==NULL) { $_SESSION['set_enterprise']['school_name'] = $_POST['school_name']; }
	if ($_POST['school_code']!==NULL) { $_SESSION['set_enterprise']['school_code'] = $_POST['school_code']; }
	unset($_SESSION['sub_session']);
	return;
}

/**
 * SESSIONから会社未設定
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function del_enterprise() {
	unset($_SESSION['sub_session']);
	unset($_SESSION['set_enterprise']);
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
function member_list() {

	global $L_PAGE_VIEW;

	if (!$_SESSION['select_db']) { return; }

	//	DB接続
	$connect_db = new connect_db();
	$connect_db->set_db($_SESSION['select_db']);
	$ERROR = $connect_db->set_connect_db();
	if ($ERROR) {
		$html .= ERROR($ERROR);
	}

	if ($_SESSION['set_enterprise']===NULL) {
		$sql = "SELECT count(*) AS guardian_count FROM " .
//			T_GUARDIAN . " " . T_GUARDIAN .	//	del ookawara 2009/11/19
			T_GUARDIAN . " guardian".		//	add ookawara 2009/11/19
			" LEFT JOIN ".T_STUDENT." student ON student.guardian_id=guardian.guardian_id".	//	add ookawara 2009/11/19
			" WHERE guardian.mk_flg='0'".
			" AND student.mk_flg='0'".			//	add ookawara 2009/11/19
			" AND student.move_flg='0'".		//	add ookawara 2009/11/19
			" AND student.knry_end_flg='0'";	//	add ookawara 2009/11/19
		$in_sql = "SELECT guardian.guardian_id FROM " .
//			T_GUARDIAN . " " . T_GUARDIAN .	//	del ookawara 2009/11/19
			T_GUARDIAN . " guardian".		//	add ookawara 2009/11/19
			" LEFT JOIN ".T_STUDENT." student ON student.guardian_id=guardian.guardian_id".	//	add ookawara 2009/11/19
			" WHERE guardian.mk_flg='0'".
			" AND student.mk_flg='0'".			//	add ookawara 2009/11/19
			" AND student.move_flg='0'".		//	add ookawara 2009/11/19
			" AND student.knry_end_flg='0'";	//	add ookawara 2009/11/19
	} elseif ($_SESSION['set_enterprise']['school_id']===NULL) {
		$html .= "<p style=\"font-size:12px\">現在、法人[<strong style=\"color:#ff0000;\">".$_SESSION[set_enterprise][enterprise_name]."</strong>]にセットされています。</p>\n";
		$sql = "SELECT count(*) AS guardian_count FROM " .
//			T_GUARDIAN . " " . T_GUARDIAN . "," . T_ENTERPRISE . " " . T_ENTERPRISE . "," .  T_SCHOOL . " " . T_SCHOOL .	//	del ookawara 2009/11/19
			T_GUARDIAN." guardian".	//	add ookawara 2009/11/19
			" LEFT JOIN ".T_STUDENT." student ON student.guardian_id=guardian.guardian_id".	//	add ookawara 2009/11/19
			" LEFT JOIN ".T_SCHOOL . " school ON school.school_id=student.school_id".	//	add ookawara 2009/11/19
			" LEFT JOIN ".T_ENTERPRISE." enterprise ON enterprise.enterprise_id=school.enterprise_id".	//	add ookawara 2009/11/19
			" WHERE guardian.mk_flg='0'".
			" AND enterprise.mk_flg='0'".
			" AND enterprise.move_flg='0'".
			" AND school.mk_flg='0'".
//			" AND school.move_flg='0'" .	//	del ookawara 2009/11/19
			" AND enterprise.enterprise_id='" . $_SESSION['set_enterprise']['enterprise_id'] . "'" .
//			" AND enterprise.enterprise_id=school.enterprise_id" .	//	del ookawara 2009/11/19
//			" AND guardian.school_id=school.school_id".	//	del ookawara 2009/11/19
			" AND student.mk_flg='0'".			//	add ookawara 2009/11/19
			" AND student.move_flg='0'".		//	add ookawara 2009/11/19
			" AND student.knry_end_flg='0'";	//	add ookawara 2009/11/19
		$in_sql = "SELECT guardian.guardian_id FROM " .
//			T_GUARDIAN . " " . T_GUARDIAN . "," . T_ENTERPRISE . " " . T_ENTERPRISE . "," .  T_SCHOOL . " " . T_SCHOOL .	//	del ookawara 2009/11/19
			T_GUARDIAN." guardian".	//	add ookawara 2009/11/19
			" LEFT JOIN ".T_STUDENT." student ON student.guardian_id=guardian.guardian_id".	//	add ookawara 2009/11/19
			" LEFT JOIN ".T_SCHOOL . " school ON school.school_id=student.school_id".	//	add ookawara 2009/11/19
			" LEFT JOIN ".T_ENTERPRISE." enterprise ON enterprise.enterprise_id=school.enterprise_id".	//	add ookawara 2009/11/19
			" WHERE guardian.mk_flg='0'".
			" AND enterprise.mk_flg='0'".
			" AND enterprise.move_flg='0'".
			" AND school.mk_flg='0'".
//			" AND school.move_flg='0'" .	//	del ookawara 2009/11/19
			" AND enterprise.enterprise_id='" . $_SESSION['set_enterprise']['enterprise_id'] . "'" .
//			" AND enterprise.enterprise_id=school.enterprise_id" .	//	del ookawara 2009/11/19
//			" AND guardian.school_id=school.school_id".	//	del ookawara 2009/11/19
			" AND student.mk_flg='0'".			//	add ookawara 2009/11/19
			" AND student.move_flg='0'".		//	add ookawara 2009/11/19
			" AND student.knry_end_flg='0'";	//	add ookawara 2009/11/19
	} else {
		$html .= "<p style=\"font-size:12px\">現在、法人[<strong style=\"color:#ff0000;\">".$_SESSION['set_enterprise']['enterprise_name']."</strong>]/校舎名[<strong style=\"color:#ff0000;\">" .
			$_SESSION['set_enterprise']['school_name'] . "</strong>]にセットされています。</p>\n";
		$sql = "SELECT count(*) AS guardian_count FROM " .
//			T_GUARDIAN . " " . T_GUARDIAN .	//	del ookawara 2009/11/19
			T_GUARDIAN . " guardian".		//	add ookawara 2009/11/19
			" LEFT JOIN ".T_STUDENT." student ON student.guardian_id=guardian.guardian_id".	//	add ookawara 2009/11/19
			" WHERE guardian.mk_flg='0'".
			" AND guardian.school_id='" . $_SESSION['set_enterprise']['school_id'] . "'".
			" AND student.mk_flg='0'".			//	add ookawara 2009/11/19
			" AND student.move_flg='0'".		//	add ookawara 2009/11/19
			" AND student.knry_end_flg='0'";	//	add ookawara 2009/11/19
		$in_sql = "SELECT guardian.guardian_id FROM " .
//			T_GUARDIAN . " " . T_GUARDIAN .	//	del ookawara 2009/11/19
			T_GUARDIAN . " guardian".		//	add ookawara 2009/11/19
			" LEFT JOIN ".T_STUDENT." student ON student.guardian_id=guardian.guardian_id".	//	add ookawara 2009/11/19
			" WHERE guardian.mk_flg='0'".
			" AND guardian.school_id='" . $_SESSION['set_enterprise']['school_id'] . "'".
			" AND student.mk_flg='0'".			//	add ookawara 2009/11/19
			" AND student.move_flg='0'".		//	add ookawara 2009/11/19
			" AND student.knry_end_flg='0'";	//	add ookawara 2009/11/19
	}

	if ($result = $connect_db->query($sql)) {
		$list=$connect_db->fetch_assoc($result);
		$guardian_count = $list['guardian_count'];
	}

	if ($_SESSION['sub_session']['s_order'] == 1) {
//		$sql .= " ORDER BY guardian.guardian_myj_kn";
//		$in_sql .= " ORDER BY guardian.guardian_myj_kn";
		$sql .= " ORDER BY ". convertDecryptField('guardian.guardian_myj_kn', false); // kaopiz 2020/08/20 Encoding
		$in_sql .= " ORDER BY ". convertDecryptField('guardian.guardian_myj_kn', false); // kaopiz 2020/08/20 Encoding start
	} else {
		$sql .= " ORDER BY guardian.guardian_id";
		$in_sql .= " ORDER BY guardian.guardian_id";
	}
	if ($_SESSION['sub_session']['s_desc']) {
		$sql .= " DESC";
		$in_sql .= " DESC";
	}
	if ($L_PAGE_VIEW[$_SESSION['sub_session']['s_page_view']]) { $page_view = $L_PAGE_VIEW[$_SESSION['sub_session']['s_page_view']]; }
	else { $page_view = $L_PAGE_VIEW[0]; }
	$max_page = ceil($guardian_count/$page_view);
	if ($_SESSION['sub_session']['s_page']) { $page = $_SESSION['sub_session']['s_page']; }
	else { $page = 1; }
	$start = ($page - 1) * $page_view;
	$next = $page + 1;
	$back = $page - 1;

	$sql .= " LIMIT ".$start.",".$page_view.";";
	$in_sql .= " LIMIT ".$start.",".$page_view.";";
	if ($result = $connect_db->query($in_sql)) {
		while ($list=$connect_db->fetch_assoc($result)) {
			$L_IN_ID[] = $list['guardian_id'];
		}
	}
	if ($L_IN_ID) { $IN = implode(",",$L_IN_ID); }

	$sql  = "CREATE TEMPORARY TABLE student_count " .
		"SELECT guardian_id,COUNT(student_id) AS student_count FROM " . T_STUDENT .
//knry_end_flg追加　2009/11/05　hirano
		" WHERE mk_flg='0'".
		" AND student.knry_end_flg='0'".
		" AND student.guardian_id IN (".$IN.")".
		" GROUP BY student.guardian_id;";
	$connect_db->exec_query($sql);
	$join = " LEFT JOIN student_count student_count ON guardian.guardian_id=student_count.guardian_id";
	if ($_SESSION['set_enterprise']===NULL) {
//		$sql = "SELECT guardian.guardian_id,guardian.guardian_myj,guardian.guardian_nme,guardian.city,guardian.add1,guardian.add2,guardian.tel,student_count.student_count FROM " .
		$sql = "SELECT guardian.guardian_id,". convertDecryptField('guardian.guardian_myj') .",". convertDecryptField('guardian.guardian_nme') .",". convertDecryptField('guardian.city') .",". convertDecryptField('guardian.add1') .",". convertDecryptField('guardian.add2') ."," // kaopiz 2020/08/20 Encoding start
			. convertDecryptField('guardian.tel') .",student_count.student_count FROM " . // kaopiz 2020/08/20 Encoding start
//			T_GUARDIAN . " " . T_GUARDIAN .	//	del ookawara 2009/11/19
			T_GUARDIAN . " guardian".		//	add ookawara 2009/11/19
			$join .
			" WHERE guardian.mk_flg='0'".
			" AND guardian.guardian_id IN (".$IN.")";
	} elseif ($_SESSION['set_enterprise']['school_id']===NULL) {
//		$sql = "SELECT guardian.guardian_id,guardian.guardian_myj,guardian.guardian_nme,guardian.city,guardian.add1,guardian.add2,guardian.tel,student_count.student_count FROM " .
		$sql = "SELECT guardian.guardian_id,". convertDecryptField('guardian.guardian_myj') .",". convertDecryptField('guardian.guardian_nme') .",". convertDecryptField('guardian.city') .",". convertDecryptField('guardian.add1') .",". convertDecryptField('guardian.add2') ."," // kaopiz 2020/08/20 Encoding start
			. convertDecryptField('guardian.tel') .",student_count.student_count FROM " . // kaopiz 2020/08/20 Encoding start
//			T_GUARDIAN . " " . T_GUARDIAN . "," . T_ENTERPRISE . " " . T_ENTERPRISE . "," .  T_SCHOOL . " " . T_SCHOOL .	//	del ookawara 2009/11/19
			T_GUARDIAN." guardian".	//	add ookawara 2009/11/19
			" LEFT JOIN ".T_STUDENT." student ON student.guardian_id=guardian.guardian_id".	//	add ookawara 2009/11/19
			" LEFT JOIN ".T_SCHOOL . " school ON school.school_id=student.school_id".	//	add ookawara 2009/11/19
			" LEFT JOIN ".T_ENTERPRISE." enterprise ON enterprise.enterprise_id=school.enterprise_id".	//	add ookawara 2009/11/19
			$join .
			" WHERE guardian.mk_flg='0'".
			" AND enterprise.mk_flg='0'".
			" AND enterprise.move_flg='0'".
			" AND school.mk_flg='0'".
//			" AND school.move_flg='0'" .	//	del ookawara 2009/11/19
			" AND enterprise.enterprise_id='" . $_SESSION['set_enterprise']['enterprise_id'] . "'" .
			" AND enterprise.enterprise_id=school.enterprise_id" .
			" AND guardian.school_id=school.school_id".
			" AND guardian.guardian_id IN (".$IN.")";
	} else {
//		$sql = "SELECT guardian.guardian_id,guardian.guardian_myj,guardian.guardian_nme,guardian.city,guardian.add1,guardian.add2,guardian.tel,student_count.student_count FROM " .
		$sql = "SELECT guardian.guardian_id,". convertDecryptField('guardian.guardian_myj') .",". convertDecryptField('guardian.guardian_nme') .",". convertDecryptField('guardian.city') .",". convertDecryptField('guardian.add1') .",". convertDecryptField('guardian.add2') ."," // kaopiz 2020/08/20 Encoding start
			. convertDecryptField('guardian.tel') .",student_count.student_count FROM " . // kaopiz 2020/08/20 Encoding start
//			T_GUARDIAN . " " . T_GUARDIAN .	//	del ookawara 2009/11/19
			T_GUARDIAN . " guardian".		//	add ookawara 2009/11/19
			$join .
			" WHERE guardian.mk_flg='0'".
			" AND guardian.guardian_id IN (".$IN.")";
			" AND guardian.school_id='" . $_SESSION['set_enterprise']['school_id'] . "'";
	}
	if ($_SESSION['sub_session']['s_order'] == 1) {
		if ($_SESSION['sub_session']['s_desc']) {
			$desc = " DESC";
		}
//		$sql .= " ORDER BY guardian.guardian_myj_kn".$desc.",guardian.guardian_nme_kn".$desc;	//	del ookawara 2010/03/09
//		$sql .= " ORDER BY guardian.guardian_myj_kn".$desc.",guardian.guardian_nme_kn".$desc.", guardian.guardian_id".$desc;	//	add ookawara 2010/03/09
		$sql .= " ORDER BY ". convertDecryptField('guardian.guardian_myj_kn', false) ."".$desc.",". convertDecryptField('guardian.guardian_nme_kn', false) ."".$desc.", guardian.guardian_id".$desc;	// kaopiz 2020/08/20 Encoding start
	} else {
		$sql .= " ORDER BY guardian.guardian_id";
		if ($_SESSION['sub_session']['s_desc']) {
			$sql .= " DESC";
		}
	}

	if ($result = $connect_db->query($sql)) {
		$max = $connect_db->num_rows($result);
	}
	if (!$max) {
		$html .= "現在、登録保護者は存在しません。";
		return $html;
	}
	$html .= "<div style=\"float:left;\">登録保護者総数(".$guardian_count."):PAGE[".$page."/".$max_page."]</div>\n";
	if ($back > 0) {
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" style=\"float:left;\">";
		$html .= "<input type=\"submit\" value=\"前のページ\">";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
		$html .= "<input type=\"hidden\" name=\"s_page\" value=\"".$back."\">\n";
		$html .= "</form>";
	}
	if ($page < $max_page) {
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" style=\"float:left;\">";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
		$html .= "<input type=\"hidden\" name=\"s_page\" value=\"".$next."\">\n";
		$html .= "<input type=\"submit\" value=\"次のページ\">";
		$html .= "</form>";
	}

	$html .= "<br style=\"clear:left;\">";
	$html .= "<table class=\"member_form\">\n";
	$html .= "<tr class=\"member_form_menu\">\n";
	$html .= "<td>保護者ID</td>\n";
	$html .= "<td>保護者名</td>\n";
	$html .= "<td>住所</td>\n";
	$html .= "<td>電話番号</td>\n";
	$html .= "<td>生徒数</td>\n";
	if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__view",$_SESSION['authority'])===FALSE)) {
		$html .= "<td>詳細</td>\n";
	}
	$html .= "</tr>\n";
	while ($list=$connect_db->fetch_assoc($result)) {
		foreach($list as $key => $val) { $list[$key] = replace_decode($val); }

		if (!$list['student_count']) {
			$list['student_count'] = 0;
		}
		$student_count = $list['student_count'] . "人";

		$html .= "<tr class=\"member_form_cell\">\n";
		$html .= "<td>".$list['guardian_id']."</td>\n";
		$html .= "<td>".$list['guardian_myj']." ".$list['guardian_nme']."</td>\n";
		$html .= "<td>".$list['pref']." ".$list['city']." ".$list['add1']." ".$list['add2']."</td>\n";
		$html .= "<td>".$list['tel']."</td>\n";
		$html .= "<td>".$student_count."</td>\n";
		if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__view",$_SESSION['authority'])===FALSE)) {
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"view\">\n";
			$html .= "<input type=\"hidden\" name=\"guardian_id\" value=\"".$list['guardian_id']."\">\n";
			$html .= "<td><input type=\"submit\" value=\"詳細\"></td>\n";
			$html .= "</form>\n";
		}
		$html .= "</tr>\n";
	}
	$html .= "</table>\n";

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
	global $L_PRF,$L_MENU;

	$html = "新規登録フォーム";

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(GUARDIAN_TEMP_MEMBER_FORM);

	$INPUTS['SCHOOLNAME'] = array('result'=>'plane','value'=>$_SESSION['set_enterprise']['school_name']);
	$INPUTS['SCHOOLID'] = array('result'=>'plane','value'=>$_SESSION['set_enterprise']['school_id']);
	$INPUTS['ENTERPRISENAME'] = array('result'=>'plane','value'=>$_SESSION['set_enterprise']['enterprise_name']);
	$INPUTS['ENTERPRISEID'] = array('result'=>'plane','value'=>$_SESSION['set_enterprise']['enterprise_id']);

	$INPUTS['GUARDIANMYJ'] = array('type'=>'text','name'=>'guardian_myj','value'=>$_POST['guardian_myj']);
	$INPUTS['GUARDIANNME'] = array('type'=>'text','name'=>'guardian_nme','value'=>$_POST['guardian_nme']);
	$INPUTS['GUARDIANMYJKANA'] = array('type'=>'text','name'=>'guardian_myj_kn','value'=>$_POST['guardian_myj_kn']);
	$INPUTS['GUARDIANNMEKANA'] = array('type'=>'text','name'=>'guardian_nme_kn','value'=>$_POST['guardian_nme_kn']);
	$INPUTS['GUARDIANID'] = array('type'=>'text','name'=>'guardian_id','value'=>$_POST['guardian_id']);
	$INPUTS['GUARDIANEMAIL'] = array('type'=>'text','name'=>'guardian_email','value'=>$_POST['guardian_email']);
	$INPUTS['GUARDIANMOBILEEMAIL'] = array('type'=>'text','name'=>'guardian_mobile_email','value'=>$_POST['guardian_mobile_email']);
	$INPUTS['ZIP'] = array('type'=>'text','name'=>'zip','size'=>'8','value'=>$_POST['zip']);
	$INPUTS['COUNTRY'] = array('type'=>'text','name'=>'country','value'=>$_POST['country']);
	$INPUTS['PRF'] = array('type'=>'select','name'=>'area_cd','array'=>$L_PRF,'check'=>$_POST['area_cd']);
	$INPUTS['CITY'] = array('type'=>'text','name'=>'city','value'=>$_POST['city']);
	$INPUTS['ADD1'] = array('type'=>'text','name'=>'add1','size'=>'50','value'=>$_POST['add1']);
	$INPUTS['ADD2'] = array('type'=>'text','name'=>'add2','size'=>'50','value'=>$_POST['add2']);
	$INPUTS['TEL'] = array('type'=>'text','name'=>'tel','value'=>$_POST['tel']);
	$INPUTS['MOBILE'] = array('type'=>'text','name'=>'mobile','value'=>$_POST['mobile']);
	$INPUTS['FAX'] = array('type'=>'text','name'=>'fax','value'=>$_POST['fax']);
	$INPUTS['EMERGENCYCONTACT'] = array('type'=>'text','name'=>'emergency_contact','value'=>$_POST['emergency_contact']);
	$INPUTS['EMERGENCYTEL'] = array('type'=>'text','name'=>'emergency_tel','value'=>$_POST['emergency_tel']);
	$INPUTS['REMARKS'] = array('type'=>'textarea','name'=>'usr_bko','cols'=>'50','rows'=>'5','value'=>$_POST['usr_bko']);

	$INPUTS['NEED'] = array('result'=>'plane','value'=>"<span style=\"font-size:80%;color:#ff0000;\">必須</span>");

	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("login_email");
	$newform->set_form_id("login_email_yes");
	$newform->set_form_check($_POST['login_email']);
	$newform->set_form_value('1');
	$yes = $newform->make();
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("login_email");
	$newform->set_form_id("login_email_no");
	$newform->set_form_check($_POST['login_email']);
	$newform->set_form_value('2');
	$no = $newform->make();
	$login = $yes . "<label for=\"login_email_yes\">する</label> / " . $no . "<label for=\"login_email_no\">しない</label>";
	$INPUTS['LOGINEMAIL'] = array('result'=>'plane','value'=>$login);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"追加確認\">";
	$html .= "<input type=\"reset\" value=\"クリア\">";
	$html .= "</form>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
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
 * @return array エラーの場合
 */
function check() {

	// DB接続オブジェクト
	$connect_db = new connect_db();
	$connect_db->set_db($_SESSION['select_db']);
	$ERROR = $connect_db->set_connect_db();
	if ($ERROR) {
		$html .= ERROR($ERROR);
	}

	if (!$_POST['guardian_myj']) { $ERROR[] = "保護者名(姓)が未入力です"; }
	if (!$_POST['guardian_nme']) { $ERROR[] = "保護者名(名)が未入力です"; }
	if (!$_POST['guardian_myj_kana']) { $ERROR[] = "保護者名カナ(姓)が未入力です"; }
	if (!$_POST['guardian_nme_kana']) { $ERROR[] = "保護者名カナ(名)が未入力です"; }
	if (!$_POST['guardian_id']) { $ERROR[] = "保護者IDが未入力です"; }
	elseif (strlen($_POST['guardian_id']) > 20) {$ERROR[] = "保護者IDは20文字以内にしてください。";}
	if ($_POST['guardian_id'] && MODE == "addform") {
		$sql = "SELECT guardian_id FROM ".T_GUARDIAN." WHERE guardian_id='".$_POST['guardian_id']."' LIMIT 1;";
		if ($result = $connect_db->query($sql)) {
			$max = $connect_db->num_rows($result);
		}
		if ($max) { $ERROR[] = "入力されたIDは既に利用されています。変更してください。"; }
	}
/*
	if (!$_POST[zip]) { $ERROR[] = "郵便番号が未入力です。"; }
	if (!$_POST[country]) { $ERROR[] = "国名が未入力です。"; }
	if (!$_POST[area_cd]) { $ERROR[] = "都道府県が選択されていません。"; }
	if (!$_POST[city]) { $ERROR[] = "住所１が未入力です。"; }
	if (!$_POST[tel]) { $ERROR[] = "電話番号が未入力です。"; }
*/
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

	global $L_PRF,$L_MENU,$L_DO;

	$action = ACTION;
	if ($action) {
		foreach ($_POST as $key => $val) { $$key = $val; }
	} else {
		if (!$_SESSION['select_db']) { return; }

		//	DB接続
		$connect_db = new connect_db();
		$connect_db->set_db($_SESSION['select_db']);
		$ERROR = $connect_db->set_connect_db();
		if ($ERROR) {
			$html .= ERROR($ERROR);
		}
//		$sql = "SELECT guardian.guardian_id,guardian.guardian_myj,guardian.guardian_nme,guardian.guardian_myj_kn,guardian.guardian_nme_kn," .
//			"guardian.guardian_email,guardian.guardian_mobile_email,guardian.zip,guardian.country,guardian.area_cd,guardian.city,guardian.add1,guardian.add2," .
//			"guardian.tel,guardian.mobile,guardian.fax,guardian.emergency_contact,guardian.emergency_tel,guardian.login_email,guardian.usr_bko," .
		// kaopiz 2020/08/20 Encoding start
		$sql = "SELECT guardian.guardian_id,". convertDecryptField('guardian.guardian_myj') .",". convertDecryptField('guardian.guardian_nme') .",".
			convertDecryptField('guardian.guardian_myj_kn') .",". convertDecryptField('guardian.guardian_nme_kn') ."," .
			convertDecryptField('guardian.guardian_email') .",". convertDecryptField('guardian.guardian_mobile_email') .",". convertDecryptField('guardian.zip') .",".
			convertDecryptField('guardian.country') .",guardian.area_cd,".
			convertDecryptField('guardian.city') .",". convertDecryptField('guardian.add1') .",". convertDecryptField('guardian.add2') ."," .
			convertDecryptField('guardian.tel') .",". convertDecryptField('guardian.mobile') .",". convertDecryptField('guardian.fax') .",".
			convertDecryptField('guardian.emergency_contact') .",". convertDecryptField('guardian.emergency_tel') .",guardian.login_email,guardian.usr_bko," .
		// kaopiz 2020/08/20 Encoding end
			"enterprise.enterprise_id,enterprise.enterprise_name,school.school_id,school.school_name FROM " .
			T_GUARDIAN . " guardian," . T_ENTERPRISE . " enterprise," . T_SCHOOL . " school".
			" WHERE guardian_id='".$_POST['guardian_id']."'" .
			" AND guardian.school_id=school.school_id" .
			" AND school.enterprise_id=enterprise.enterprise_id" .
			" AND guardian.mk_flg='0' AND school.mk_flg='0'" .
			" AND enterprise.mk_flg='0' AND enterprise.move_flg='0' LIMIT 1;";
		$result = $connect_db->query($sql);
		$list=$connect_db->fetch_assoc($result);
		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}
		foreach ($list as $key => $val) {
			$$key = replace_decode($val);
		}
		$guardian_pass = "";
	}

	$html = "詳細画面";

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

//	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
//	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";
//	$html .= "<input type=\"hidden\" name=\"guardian_num\" value=\"".$guardian_num."\">\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(GUARDIAN_TEMP_MEMBER_FORM);

	$INPUTS['SCHOOLNAME'] = array('result'=>'plane','value'=>$school_name);
	$INPUTS['SCHOOLID'] = array('result'=>'plane','value'=>$school_id);
	$INPUTS['ENTERPRISENAME'] = array('result'=>'plane','value'=>$enterprise_name);
	$INPUTS['ENTERPRISEID'] = array('result'=>'plane','value'=>$enterprise_id);

	$INPUTS['GUARDIANMYJ'] = array('result'=>'plane','value'=>$guardian_myj);
	$INPUTS['GUARDIANNME'] = array('result'=>'plane','value'=>$guardian_nme);
	$INPUTS['GUARDIANMYJKANA'] = array('result'=>'plane','value'=>$guardian_myj_kn);
	$INPUTS['GUARDIANNMEKANA'] = array('result'=>'plane','value'=>$guardian_nme_kn);
	$INPUTS['GUARDIANID'] = array('result'=>'plane','value'=>$guardian_id);
	$INPUTS['GUARDIANEMAIL'] = array('result'=>'plane','value'=>$guardian_email);
	$INPUTS['GUARDIANMOBILEEMAIL'] = array('result'=>'plane','value'=>$guardian_mobile_email);
	$INPUTS['ZIP'] = array('result'=>'plane','value'=>$zip);
	$INPUTS['COUNTRY'] = array('result'=>'plane','value'=>$country);
	$INPUTS['PRF'] = array('result'=>'plane','value'=>$L_PRF[$area_cd]);
	$INPUTS['CITY'] = array('result'=>'plane','value'=>$city);
	$INPUTS['ADD1'] = array('result'=>'plane','value'=>$add1);
	$INPUTS['ADD2'] = array('result'=>'plane','value'=>$add2);
	$INPUTS['TEL'] = array('result'=>'plane','value'=>$tel);
	$INPUTS['MOBILE'] = array('result'=>'plane','value'=>$mobile);
	$INPUTS['FAX'] = array('result'=>'plane','value'=>$fax);
	$INPUTS['EMERGENCYCONTACT'] = array('result'=>'plane','value'=>$emergency_contact);
	$INPUTS['EMERGENCYTEL'] = array('result'=>'plane','value'=>$emergency_tel);
	$INPUTS['LOGINEMAIL'] = array('result'=>'plane','value'=>$L_DO[$login_email]);
	$INPUTS['REMARKS'] = array('result'=>'plane','cols'=>'50','rows'=>'5','value'=>$usr_bko);

	$make_html->set_rep_cmd($INPUTS);
	$html .= $make_html->replace();
//	$sql  = "SELECT student.student_id,CONCAT(student.student_myj,student.student_nme) AS student_name,".
//		"student.birthday,student.school_name," .
	$sql  = "SELECT student.student_id,CONCAT(". convertDecryptField('student.student_myj', false) .",". convertDecryptField('student.student_nme', false) .") AS student_name,". // kaopiz 2020/08/20 Encoding
			"student.birthday,". convertDecryptField('student.school_name') ."," . // kaopiz 2020/08/20 Encoding start
//			"tb_stu_jyko_jyotai.jyko_crs_cd FROM " .
	 		" tb_stu_jyko_jyotai.jyko_crs_cd," .
			" student.gknn".	//	add ookawara 2010/03/02
//			" FROM ".T_STUDENT . " " . T_STUDENT . "," .	//	del ookawara 2010/03/03
//			T_TB_STU_JYKO_JYOTAI . " " . T_TB_STU_JYKO_JYOTAI .	//	del ookawara 2010/03/03
			" FROM ".T_STUDENT . " student," .	//	add ookawara 2010/03/03
			T_TB_STU_JYKO_JYOTAI . " tb_stu_jyko_jyotai" .	//	add ookawara 2010/03/03
			" WHERE student.guardian_id='".$guardian_id."' AND student.mk_flg='0'" .
//knry_end_flg追加　2009/11/05　hirano
			" AND student.knry_end_flg='0'".
			" AND student.student_id=tb_stu_jyko_jyotai.student_id" .
			" AND student.school_id=tb_stu_jyko_jyotai.school_id" .
			" ORDER BY student.student_id";
	if ($result = $connect_db->query($sql)) {
		$max = $connect_db->num_rows($result);
		if ($max) {
			$html .= "<br style=\"clear:left;\">";
			$html .= "<table class=\"member_form\">\n";
			$html .= "<tr class=\"member_form_menu\">\n";
			$html .= "<td rowspan=\"2\">ID</td>\n";
			$html .= "<td rowspan=\"2\">名前</td>\n";
			$html .= "<td rowspan=\"2\">学校</td>\n";
			$html .= "<td rowspan=\"2\">学年</td>\n";
			// $html .= "<td colspan=\"3\">選択科目</td>\n";	// upd hasegawa 2019/11/27 理社対応
			$html .= "<td colspan=\"5\">選択科目</td>\n";
			$html .= "</tr>\n";
			$html .= "<tr class=\"member_form_menu\">\n";
			$html .= "<td>英語</td>\n";
			$html .= "<td>国語</td>\n";
			$html .= "<td>数学</td>\n";
			$html .= "<td>理科</td>\n";	// add hasegawa 2019/11/27 理社対応
			$html .= "<td>社会</td>\n";	// add hasegawa 2019/11/27 理社対応
			$html .= "</tr>\n";
			while ($list=$connect_db->fetch_assoc($result)) {
				foreach($list as $key => $val) { $list[$key] = replace_decode($val); }
				//list($eng,$jap,$math) = check_use_course($list['jyko_crs_cd']);	//	del ookawara 2012/10/31
				//	jyko_crs_cd取得
				$jyko_crs_cd = get_jyko_crs_cd($list['student_id'],$select_dbname);	//	add ookawara 2012/10/31  // update $select_dbnameを追加 2015/02/27 yoshizawa 小学校高学年対応
				// list($eng,$jap,$math) = check_use_course($jyko_crs_cd);	//	add ookawara 2012/10/31 // upd hasegawa 2019/11/27 理社対応
				list($eng,$jap,$math,$sc,$so) = check_use_course($jyko_crs_cd);

				// add start yoshizawa 2015/10/27 02_作業要件/34_数学検定/数学検定
				$OPTION_L = array();
				// SQL文生成
				$sql = " SELECT " .
						"  mc.sysi_crs_cd " .
						" FROM " . T_TB_STU_MSKM_CRS . " mc " .
						" WHERE TO_DAYS(mc.jyko_start_ymd) <= TO_DAYS(now()) " .
						"   AND mc.mk_flg = 0 " .
						// "   AND mc.srvc_cd != 'GTEST' " .	// del 2020/09/17 hau テスト標準化開発
						"   AND mc.student_id = '" .$list['student_id']. "' " .
						"  GROUP BY mc.sysi_crs_cd".	  // add 2020/09/17 hau テスト標準化開発
						"  ORDER BY NULL".				  // add 2020/09/17 hau テスト標準化開発
						";";
				//echo $sql."<br>";
				if ($result2 = $connect_db->query($sql)) {
					while ($list2 = $connect_db->fetch_assoc($result2)) {
						$OPTION_L[] = trim($list2['sysi_crs_cd']);
					}
				}
				// 数学検定受講者はすらら数学を受講可能とします。
				if( array_search('1SA' , $OPTION_L) !== FALSE  ){ $math = "○"; }
				// add end yoshizawa 2015/10/27

//				$grade = check_grade($list['birthday']);	//	del ookawara 2010/03/03

				//	学年表示設定	//	add ookawara 2010/03/02
				$grade = "";
				if ($list['gknn']) {
					$grade = check_grade_gknn($list['gknn']);
				}
				// del 2015/02/27 yoshizawa 小学校高学年対応-------
				// 取得した教科を上書きしてしまうので修正する。
				//if ($eng) { $eng = "○"; } else { $eng = "--"; }
				//if ($jap) { $jap = "○"; } else { $jap = "--"; }
				//if ($math) { $math = "○"; } else { $math = "--"; }
				//-------------------------------------------------
				// add 2015/02/27 yoshizawa 小学校高学年対応-------
				if (!$eng) { $eng = "--"; }
				if (!$jap) { $jap = "--"; }
				if (!$math) { $math = "--"; }
				if (!$sc) { $sc = "--"; }	// add hasegawa 2019/11/27 理社対応
				if (!$so) { $so = "--"; }	// add hasegawa 2019/11/27 理社対応
				//-------------------------------------------------

				$html .= "<tr class=\"member_form_cell\">\n";
				$html .= "<td>".$list['student_id']."</td>\n";
				$html .= "<td>".$list['student_name']."</td>\n";
				$html .= "<td>".$list['school_name']."</td>\n";
				$html .= "<td style=\"text-align: center;\">".$grade."</td>\n";
				$html .= "<td style=\"text-align: center;\">".$eng."</td>\n";
				$html .= "<td style=\"text-align: center;\">".$jap."</td>\n";
				$html .= "<td style=\"text-align: center;\">".$math."</td>\n";
				$html .= "<td style=\"text-align: center;\">".$sc."</td>\n";	// add hasegawa 2019/11/27 理社対応
				$html .= "<td style=\"text-align: center;\">".$so."</td>\n";	// add hasegawa 2019/11/27 理社対応
				$html .= "</tr>\n";
			}
			$html .= "</table>\n";
		}
	}

//	$html .= "<input type=\"submit\" value=\"変更確認\">";
//	$html .= "<input type=\"reset\" value=\"クリア\">";
//	$html .= "</form>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"member_list\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}

/**
 * 確認フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function check_html() {

	global $L_PRF,$L_MENU,$L_SEX,$L_DO,$L_ADMISSIONROOT;

	// DB接続オブジェクト
	$connect_db = new connect_db();
	$connect_db->set_db($_SESSION['select_db']);
	$ERROR = $connect_db->set_connect_db();
	if ($ERROR) {
		$html .= ERROR($ERROR);
	}

	if ($_POST) {
		foreach ($_POST as $key => $val) {
			if ($key == "action") {
				if (MODE == "addform") { $val = "add"; }
				elseif (MODE == "view") { $val = "change"; }
			}
			$HIDDEN .= "<input type=\"hidden\" name=\"$key\" value=\"$val\">\n";
		}
	}

	$action = ACTION;
	if ($action) {
		foreach ($_POST as $key => $val) { $$key = $val; }
	} else {
		$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"change\">\n";
		$sql = "SELECT * FROM ". T_GUARDIAN . " guardian" .
			" WHERE guardian.guardian_num='$_POST[guardian_num]'" .
			" AND guardian.state!='1' LIMIT 1;";
		$result = $connect_db->query($sql);
		$list = $$connect_db->fetch_assoc($result);
		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}
		foreach ($list as $key => $val) {
			if ($key == "student_pass") { continue; }
			if ($key == "guardian_pass") { continue; }
			$$key = replace_decode($val);
		}
		if ($birthday == "0000-00-00") { $birthday = ""; }
		else { $birthday = ereg_replace("-","/",$birthday); }
		if ($application == "0000-00-00") { $application = ""; }
		else { $application = ereg_replace("-","/",$application); }
		if ($admissionday == "0000-00-00") { $admissionday = ""; }
		else { $admissionday = ereg_replace("-","/",$admissionday); }
		$HIDDEN .= "<input type=\"hidden\" name=\"guardian_num\" value=\"$list[guardian_num]\">\n";
	}

	if (MODE != "del") { $button = "登録"; } else { $button = "削除"; }
	$html = "確認画面：以下の内容で{$button}してもよろしければ{$button}ボタンをクリックしてください。";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	if (MODE == "addform"||MODE == "view"||MODE == "del") {
		$make_html->set_file(GUARDIAN_TEMP_MEMBER_FORM);
	} elseif (MODE == "student_addform") {
		$make_html->set_file(STUDENT_TEMP_MEMBER_FORM);
	}

//	if (!$student_id) { $student_id = make_id(); }
	if (MODE == "addform") { $student_id = "自動発行"; }

	$INPUTS['SCHOOLNAME'] = array('result'=>'plane','value'=>$_SESSION['set_enterprise']['school_name']);
	$INPUTS['SCHOOLID'] = array('result'=>'plane','value'=>$_SESSION['set_enterprise']['school_id']);
	$INPUTS['ENTERPRISENAME'] = array('result'=>'plane','value'=>$_SESSION['set_enterprise']['enterprise_name']);
	$INPUTS['ENTERPRISEID'] = array('result'=>'plane','value'=>$_SESSION['set_enterprise']['enterprise_id']);

	$INPUTS['GUARDIANMYJ'] = array('result'=>'plane','value'=>$guardian_myj);
	$INPUTS['GUARDIANNME'] = array('result'=>'plane','value'=>$guardian_nme);
	$INPUTS['GUARDIANMYJKANA'] = array('result'=>'plane','value'=>$guardian_myj_kn);
	$INPUTS['GUARDIANNMEKANA'] = array('result'=>'plane','value'=>$guardian_nme_kn);
	$INPUTS['GUARDIANID'] = array('result'=>'plane','value'=>$guardian_id);
	$INPUTS['GUARDIANEMAIL'] = array('result'=>'plane','value'=>$guardian_email);
	$INPUTS['GUARDIANMOBILEEMAIL'] = array('result'=>'plane','value'=>$guardian_mobile_email);
	$INPUTS['ZIP'] = array('result'=>'plane','value'=>$zip);
	$INPUTS['COUNTRY'] = array('result'=>'plane','value'=>$country);
	$INPUTS['PRF'] = array('result'=>'plane','value'=>$L_PRF[$area_cd]);
	$INPUTS['CITY'] = array('result'=>'plane','value'=>$city);
	$INPUTS['ADD1'] = array('result'=>'plane','value'=>$add1);
	$INPUTS['ADD2'] = array('result'=>'plane','value'=>$add2);
	$INPUTS['TEL'] = array('result'=>'plane','value'=>$tel);
	$INPUTS['MOBILE'] = array('result'=>'plane','value'=>$mobile);
	$INPUTS['FAX'] = array('result'=>'plane','value'=>$fax);
	$INPUTS['EMERGENCYCONTACT'] = array('result'=>'plane','value'=>$emergency_contact);
	$INPUTS['EMERGENCYTEL'] = array('result'=>'plane','value'=>$emergency_tel);
	$INPUTS['LOGINEMAIL'] = array('result'=>'plane','value'=>$L_DO[$login_email]);
	$INPUTS['REMARKS'] = array('result'=>'plane','cols'=>'50','rows'=>'5','value'=>$usr_bko);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" style=\"float:left\">\n";
	$html .= $HIDDEN;
	$html .= "<input type=\"submit\" value=\"$button\">\n";
	$html .= "</form>";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";

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
 * DB新規登録
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function add() {

	global $L_PRF;

	// DB接続オブジェクト
	$connect_db = new connect_db();
	$connect_db->set_db($_SESSION['select_db']);
	$ERROR = $connect_db->set_connect_db();
	if ($ERROR) {
		$html .= ERROR($ERROR);
	}

	foreach ($_POST as $key => $val) {
		if ($key == "action") { continue; }
		elseif ($key == "area_cd") { $INSERT_DATA[prf] = $L_PRF[$val]; }

		$INSERT_DATA[$key] = "$val";
	}

	$INSERT_DATA['school_id'] = $_SESSION['set_enterprise']['school_id'];
	$ERROR = $connect_db->insert(T_GUARDIAN,$INSERT_DATA);

	if (!$ERROR) { $_SESSION['select_menu'] = MAIN . "<>" . SUB . "<><><>"; }
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
	$connect_db = new connect_db();
	$connect_db->set_db($_SESSION['select_db']);
	$ERROR = $connect_db->set_connect_db();
	if ($ERROR) {
		$html .= ERROR($ERROR);
	}

	if (MODE == "view") {
		foreach ($_POST as $key => $val) {
			if ($key == "action") { continue; }
			elseif ($key == "student_pass2") { continue; }
			elseif ($key == "guardian_pass2") { continue; }
			if ($key == "school_name") { continue; }
			if ($key == "school_code") { continue; }
			if ($key == "enterprise_name") { continue; }
			if ($key == "enterprise_code") { continue; }
			if ($key == "student_num") { continue; }
			if ($key == "student_id") { continue; }
			if ($key == "guardian_num") { continue; }
			if ($key == "student_pass" && $val == "") { continue; }
			if ($key == "student_pass") { $val = md5($val); }
			if ($key == "guardian_pass" && $val == "") { continue; }
			if ($key == "guardian_pass") { $INSERT_DATA['def'] = $val; $val = md5($val); }
			if ($key == "birthday") { $val = ereg_replace("/","-",$val); }
			if ($key == "admissionday") { $val = ereg_replace("/","-",$val); }

			if ($key == "student_name"||$key == "student_kana"||$key == "student_pass"||$key == "student_email"||$key == "student_mobile_email"||
				$key == "birthday"||$key == "student_sex"||$key == "school_name"||$key == "brother_num"||$key == "model_num"||
				$key == "font_size"||$key == "student_remarks"||$key == "zip"||$key == "prf"||$key == "city"||
				$key == "add1"||$key == "add2"||$key == "tel"||$key == "fax"||$key == "application"||
				$key == "admissionday"||$key == "admissionroot") {
				$INSERT_DATA[$key] = "$val";
			} else {
				$INSERT_DATA2[$key] = "$val";
			}
		}
		$INSERT_DATA['s_update_date'] = "now()";
		$INSERT_DATA2['g_update_date'] = "now()";

		$where2 = " WHERE guardian_num='".$_POST['guardian_num']."' LIMIT 1;";

		$ERROR = $connect_db->update(T_GUARDIAN,$INSERT_DATA2,$where2);
	} elseif (MODE == "del") {
		$INSERT_DATA2['state'] = "1";
		$INSERT_DATA2['g_update_date'] = "now()";
		$where = " WHERE guardian_num='".$_POST['guardian_num']."' LIMIT 1;";

		$ERROR = $connect_db->update(T_GUARDIAN,$INSERT_DATA2,$where);

		$sql = "SELECT student_num FROM " . T_STUDENT .
			" WHERE guardian_num='".$_POST['guardian_num']."'" .
			" AND state='0'";
		if ($result = $connect_db->query($sql)) {
			$max = $connect_db->num_rows($result);
		}
		if ($max) {
			$INSERT_DATA['state'] = "1";
			$INSERT_DATA['s_update_date'] = "now()";
			$where = " WHERE guardian_num='".$_POST['guardian_num']."'";

			$ERROR = $connect_db->update(T_STUDENT,$INSERT_DATA,$where);
		}
	}

	if (!$ERROR) { $_SESSION['select_menu'] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}

/**
 * コースを使えるかどうか
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param string $jyko_crs_cd
 * @return array
 */
function check_use_course($jyko_crs_cd) {

	$eng = "×";
	$jap = "×";
	$math = "×";
	$sc = "×";	// 理科 // add hasegawa 2019/11/27 理社対応
	$so = "×";	// 社会 // add hasegawa 2019/11/27 理社対応
	
	// upd start hasegawa 2019/11/27 理社対応	
	// if ($jyko_crs_cd) {
	// 	if (substr($jyko_crs_cd,1,1) === "0") {
	// 		$eng = "○";
	// 		$jap = "○";
	// 		$math = "○";
	// 	} elseif (substr($jyko_crs_cd,1,1) == "E") {
	// 		$eng = "○";
	// 	} elseif (substr($jyko_crs_cd,1,1) == "K") {
	// 		$jap = "○";
	// 	} elseif (substr($jyko_crs_cd,1,1) == "S") {
	// 		$math = "○";
	// 	}
	// } else {
	// 	$eng = "○";
	// 	$jap = "○";
	// 	$math = "○";
	// }
	// return array($eng,$jap,$math);

	if ($jyko_crs_cd) {
		
		$authority = getAuthorityCourseGknn($jyko_crs_cd);		
		if ($authority[1]) { $eng = "○"; }
		if ($authority[2]) { $math = "○"; }
		if ($authority[3]) { $jap = "○"; }
		if ($authority[15]) { $sc = "○"; }
		if ($authority[16]) { $so = "○"; }
	} else {
		
		$eng = "○";
		$jap = "○";
		$math = "○";
		$sc = "○";
		$so = "○";
	}
	
	return array($eng,$jap,$math,$sc,$so);
	// upd end hasegawa 2019/11/27 理社対応
}

/**
 * 学年表示設定
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @date 2010/03/02
 * @param string $gknn
 * @return string
 */
function check_grade_gknn($gknn) {
	//	add ookawara 2010/03/02
	if (preg_match("/E/i",$gknn)) {
		$grade = "小".preg_replace("/[^0-9]/","",$gknn);
	} elseif (preg_match("/J/i",$gknn)) {
		$grade = "中".preg_replace("/[^0-9]/","",$gknn);
	} elseif (preg_match("/H/i",$gknn)) {
		$grade = "高".preg_replace("/[^0-9]/","",$gknn);
	} elseif (preg_match("/U/i",$gknn)) {
		$grade = "大".preg_replace("/[^0-9]/","",$gknn);
	} else {
		$grade = "";
	}

	return $grade;
}


/**
 * jyko_crs_cd取得処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $student_id
 * @param string $select_dbname
 * @return mixed
 */
function get_jyko_crs_cd($student_id,$select_dbname) { // update $select_dbnameを追加 2015/02/27 yoshizawa 小学校高学年対応
	// add ookawara 2012/10/31

	// DB接続オブジェクト
	$connect_db = new connect_db();
	$connect_db->set_db($_SESSION['select_db']);
	$ERROR = $connect_db->set_connect_db();
	if ($ERROR) {
		$html .= ERROR($ERROR);
	}

	// 変数初期化
	$jyko_crs_cd = NULL;

	// del 2015/02/27 yoshizawa ------------------------------------------------------------------
	//// SQL文生成
	//$sql  = "SELECT".
	//		"	 IF (tsjj.sito_jyti='1' AND tsmc.sysi_crs_cd<>'', tsmc.sysi_crs_cd,".
	//		"		 IF ((tsjj.sito_jyti='0' AND student.secessionday>=CURRENT_DATE())".
	//		"				 OR".
	//		"			 (tsjj.sito_jyti='1' AND tsjj.srl_yk_ymd>=CURRENT_DATE()), '30JH', NULL)".
	//		"		) AS jyko_crs_cd".
	//		" FROM ".T_STUDENT." student".
	//		" LEFT JOIN ".T_TB_STU_MSKM_CRS." tsmc ON student.student_id=tsmc.student_id".
	//		"		 AND tsmc.srvc_cd='SRL'".
	//		"		 AND tsmc.jyko_start_ymd<=CURRENT_DATE()".
	//		"		 AND tsmc.mk_flg='0'".
	//		"		 AND (tsmc.sysi_crs_cd like '%J%' OR tsmc.sysi_crs_cd like '%H%')".
	//		" LEFT JOIN ".T_TB_STU_JYKO_JYOTAI." tsjj ON student.student_id=tsjj.student_id".
	//		"	 AND tsjj.mk_flg='0'".
	//		" WHERE student.student_id='".$student_id."'".
	//		" AND student.mk_flg='0';";
	//--------------------------------------------------------------------------------------------

	// SQL文生成
	$sql = getJykoCrsCdQuery($student_id); 					 // add 2015/02/27 yoshizawa 小学校高学年対応

	// SQL実行
	if ($result = $connect_db->query($sql)) {	 // add 2015/02/27 yoshizawa 小学校高学年対応

		$list = $connect_db->fetch_assoc($result);
		$jyko_crs_cd = $list['jyko_crs_cd'];
		$jyko_crs_cd = crs_cd_filter($jyko_crs_cd); //add kimura 2020/04/02 理社対応_202004
	}

	return $jyko_crs_cd;
}
?>
