<?
/**
 * ベンチャー・リンク　すらら
 *
 * アナウンス管理　生徒向け
 *
 * @author Azet
 */


/**
 * HTMLコンテンツを作成する機能
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
		if (ACTION == "add") { $ERROR = add(); }
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
 * メニュを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function mode_menu() {
	global $L_SELECT_LOG_DB;

	foreach ($L_SELECT_LOG_DB as $key => $val){
		if ($_SESSION['select_db'] == $val) { $sel = " selected"; } else { $sel = ""; }
		$s_db_html .= "<option value=\"".$key."\"".$sel.">".$val['NAME']."</option>\n";
	}

	$html .= "<div id=\"mode_menu\">\n";
	$html .= "<table cellpadding=0 cellspacing=0>\n";
	$html .= "<tr>\n";
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"member_list\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"db_session\">\n";
	$html .= "<td>閲覧DB：<select name=\"s_db_sel\" onchange=\"submit();\">".$s_db_html."</select></td>\n";

	if ($_SESSION['select_db']) {
		$html .= "</form>\n";
		$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"member_list\">\n";
		$html .= "<td><input type=\"submit\" value=\"一覧表示\" style=\"float:left;\"></td>\n";
		$html .= "</form>\n";

		if ($_SESSION[set_enterprise]===NULL) {
			$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"set_enterprise\">\n";
			$html .= "<td><input type=\"submit\" value=\"法人指定\"></td>\n";
			$html .= "</form>\n";
		}

		if ($_SESSION[set_enterprise][school_id]!==NULL
			&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__add",$_SESSION['authority'])===FALSE))
		) {
			$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"addform\">\n";
			$html .= "<td><input type=\"submit\" value=\"新規登録\"></td>\n";
			$html .= "</form>\n";
		}
		if ($_SESSION[set_enterprise]!==NULL) {
			$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"set_enterprise\">\n";
			$html .= "<td><input type=\"submit\" value=\"法人再指定\"></td>\n";
			$html .= "</form>\n";
			$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"del_enterprise\">\n";
			$html .= "<input type=\"hidden\" name=\"action\" value=\"del_enterprise\">\n";
			$html .= "<td><input type=\"submit\" value=\"法人指定解除\"></td>\n";
			$html .= "</form>\n";
		}
	}
	$html .= "</tr>\n";
	$html .= "</table><br>\n";
	$html .= "</div>\n";

	return $html;
}

/**
 * SESSIONにDBの情報を設定する機能
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
 * 会社一覧を作成する機能
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

	if ($_SESSION[set_enterprise]===NULL) {
		$sql = "SELECT enterprise_id,enterprise_name FROM " . T_ENTERPRISE .
			" WHERE mk_flg!='1'" .
			" ORDER BY enterprise_id;";
		$case = 1;
	} elseif ($_SESSION[set_enterprise][school_id]===NULL) {
		$html .= "<p style=\"font-size:12px\">現在、法人[<strong style=\"color:#ff0000;\">".$_SESSION[set_enterprise][enterprise_name]."</strong>]にセットされています。</p>\n";
		$sql = "SELECT school_id,school_name FROM " . T_SCHOOL .
			" WHERE mk_flg!='1'" .
			" AND enterprise_id='".$_SESSION[set_enterprise][enterprise_id]."'" .
			" ORDER BY school_id;";
		$case = 2;
	} else {
		$html .= "<p style=\"font-size:12px\">現在、法人[<strong style=\"color:#ff0000;\">".$_SESSION[set_enterprise][enterprise_name]."</strong>]/校舎名[<strong style=\"color:#ff0000;\">".
			$_SESSION[set_enterprise][school_name]."</strong>]にセットされています。</p>\n";
		$sql = "SELECT school_id,school_name FROM " . T_SCHOOL .
			" WHERE mk_flg!='1'" .
			" AND enterprise_id='".$_SESSION[set_enterprise][enterprise_id]."'" .
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
		$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" name=\"menu\">\n";
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
				$html .= "<td class=\"member_form_cell\" rowspan=\"$max\">".$_SESSION[set_enterprise][enterprise_name]."</td>\n";
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

	if ($connect_db) {
		$connect_db->close();
	}

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
	if ($_POST[enterprise_id]!==NULL) { $_SESSION[set_enterprise][enterprise_id] = $_POST[enterprise_id]; }
	if ($_POST[enterprise_name]!==NULL) { $_SESSION[set_enterprise][enterprise_name] = $_POST[enterprise_name]; }
	if ($_POST[enterprise_code]!==NULL) { $_SESSION[set_enterprise][enterprise_code] = $_POST[enterprise_code]; }
	if ($_POST[school_id]!==NULL) { $_SESSION[set_enterprise][school_id] = $_POST[school_id]; }
	if ($_POST[school_name]!==NULL) { $_SESSION[set_enterprise][school_name] = $_POST[school_name]; }
	if ($_POST[school_code]!==NULL) { $_SESSION[set_enterprise][school_code] = $_POST[school_code]; }
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
	unset($_SESSION[set_enterprise]);
	return;
}

/**
 * メンバーのリストのHTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function member_list() {
	global $L_GROUP,$L_DISPLAY;

	if (!$_SESSION['select_db']) { return; }

	//	DB接続
	$connect_db = new connect_db();
	$connect_db->set_db($_SESSION['select_db']);
	$ERROR = $connect_db->set_connect_db();
	if ($ERROR) {
		$html .= ERROR($ERROR);
	}

	if ($_SESSION[set_enterprise]===NULL) {
		$sql = "SELECT announcement.announcement_num,".
			"announcement.user_level,".
			"announcement.subject,".
			"announcement.display,".
			"announcement.regist_date," .
			"announcement.update_date,".
			" CASE " .
//            " when announcement.user_level='5' then teacher.teacher_name " .
			" when announcement.user_level='5' then " . convertDecryptField('teacher.teacher_name') . // kaopiz 2020/08/20 Encoding
			" when announcement.user_level='1' then '管理者' " .
			" END user_name " .
			" FROM " .
			T_ANNOUNCEMENT . " " . T_ANNOUNCEMENT .
			" LEFT JOIN ".T_TEACHER. " " .T_TEACHER." ON teacher.teacher_id=announcement.user_id".
			" WHERE announcement.address_level='6' AND announcement.state='0'".
			" ORDER BY announcement.update_date DESC";
	} elseif ($_SESSION[set_enterprise][school_id]===NULL) {
		$html .= "<p style=\"font-size:12px\">現在、法人[<strong style=\"color:#ff0000;\">".$_SESSION[set_enterprise][enterprise_name]."</strong>]にセットされています。</p>\n";
			$_SESSION[set_enterprise][school_name] . "</strong>]にセットされています。</p>\n";
		// 生徒リスト
		$any_sql_1 = "SELECT student.student_id FROM " .
			T_STUDENT . " " . T_STUDENT . "," . T_SCHOOL . " " . T_SCHOOL . "," . T_ENTERPRISE . " " . T_ENTERPRISE .
			" WHERE student.mk_flg='0' AND school.mk_flg='0' AND enterprise.mk_flg='0'" .
//knry_end_flg追加　2009/11/05　hirano
			" AND student.knry_end_flg='0'".
			" AND enterprise.enterprise_id='" . $_SESSION['set_enterprise']['enterprise_id'] . "'" .
			" AND student.school_id=school.school_id" .
			" AND enterprise.enterprise_id=school.enterprise_id";
		// グループリスト
		$any_sql_2 = "SELECT group_list.sgl_id FROM " .
			T_STUDENT_GROUP_LIST ." group_list," . T_SCHOOL . " " . T_SCHOOL . "," . T_ENTERPRISE . " " . T_ENTERPRISE .
			" WHERE enterprise.enterprise_id='" . $_SESSION['set_enterprise']['enterprise_id'] . "'" .
			" AND enterprise.enterprise_id=school.enterprise_id" .
			" AND group_list.school_id=school.school_id" .
			" AND group_list.state='0' AND school.mk_flg='0' AND enterprise.mk_flg='0'";
		//校舎リスト
		$any_sql_3 = "SELECT school_id FROM " .
			T_SCHOOL . " " . T_SCHOOL . "," . T_ENTERPRISE . " " . T_ENTERPRISE .
			" WHERE enterprise.enterprise_id='" . $_SESSION['set_enterprise']['enterprise_id'] . "'" .
			" AND enterprise.enterprise_id=school.enterprise_id" .
			" AND school.mk_flg!='1' AND enterprise.mk_flg!='1'";
		$sql = "SELECT announcement.announcement_num,".
			"announcement.user_level,".
			"announcement.subject,".
			"announcement.display,".
			"announcement.regist_date," .
			"announcement.update_date,".
			" CASE " .
//            " when announcement.user_level='5' then teacher.teacher_name " .
			" when announcement.user_level='5' then " . convertDecryptField('teacher.teacher_name') . // kaopiz 2020/08/20 Encoding
			" when announcement.user_level='1' then '管理者' " .
			" END user_name " .
			" FROM " .
			T_ANNOUNCEMENT . " " . T_ANNOUNCEMENT .
			" LEFT JOIN ".T_TEACHER. " " .T_TEACHER." ON teacher.teacher_id=announcement.user_id".
			" WHERE announcement.address_level='6' AND announcement.state='0'" .
			" AND ((announcement.address_type='1' AND announcement.address_id=ANY($any_sql_3))" .
			" OR (announcement.address_type='2' AND announcement.address_id=ANY($any_sql_2))" .
			" OR (announcement.address_type='3' AND announcement.address_id=ANY($any_sql_1)))" .
			" ORDER BY announcement.update_date DESC";
	} else {
		$html .= "<p style=\"font-size:12px\">現在、法人[<strong style=\"color:#ff0000;\">".$_SESSION[set_enterprise][enterprise_name]."</strong>]/校舎名[<strong style=\"color:#ff0000;\">" .
			$_SESSION[set_enterprise][school_name] . "</strong>]にセットされています。</p>\n";
		// 生徒リスト
		$any_sql_1 = "SELECT student.student_id FROM " .
			T_STUDENT . " " . T_STUDENT . "," . T_SCHOOL . " " . T_SCHOOL . "," . T_ENTERPRISE . " " . T_ENTERPRISE .
			" WHERE student.mk_flg!='1' AND school.mk_flg!='1' AND enterprise.mk_flg!='1'" .
//knry_end_flg追加　2009/11/05　hirano
			" AND student.knry_end_flg='0'".
			" AND student.school_id='" . $_SESSION[set_enterprise][school_id] . "'" .
			" AND student.school_id=school.school_id" .
			" AND enterprise.enterprise_id=school.enterprise_id";
		// グループリスト
		$any_sql_2 = "SELECT group_list.sgl_id FROM " .
			T_STUDENT_GROUP_LIST ." group_list," . T_SCHOOL . " " . T_SCHOOL .
			" WHERE group_list.school_id='" . $_SESSION[set_enterprise][school_id] . "'" .
			" AND group_list.school_id=school.school_id" .
			" AND group_list.state!='1' AND school.mk_flg!='1'";
		$sql = "SELECT announcement.announcement_num,".
			"announcement.user_level,".
			"announcement.subject,".
			"announcement.display,".
			"announcement.regist_date," .
			"announcement.update_date,".
			" CASE " .
//            " when announcement.user_level='5' then teacher.teacher_name " .
			" when announcement.user_level='5' then " . convertDecryptField('teacher.teacher_name') . // kaopiz 2020/08/20 Encoding
			" when announcement.user_level='1' then '管理者' " .
			" END user_name " .
			" FROM " .
			T_ANNOUNCEMENT . " " . T_ANNOUNCEMENT .
			" LEFT JOIN ".T_TEACHER. " " .T_TEACHER." ON teacher.teacher_id=announcement.user_id".
			" WHERE announcement.address_level='6' AND announcement.state!='1'" .
			" AND ((announcement.address_type='1' AND announcement.address_id='" . $_SESSION[set_enterprise][school_id] . "')" .
			" OR (announcement.address_type='2' AND announcement.address_id=ANY($any_sql_2))" .
			" OR (announcement.address_type='3' AND announcement.address_id=ANY($any_sql_1)))" .
			" ORDER BY announcement.update_date DESC";
	}
	if ($result = $connect_db->query($sql)) {
		$max = $connect_db->num_rows($result);
	}
	if (!$max) {
		$html .= "現在、アナウンス情報は存在しません。";
		return $html;
	}
	$html .= "<table class=\"member_form\">\n";
	$html .= "<tr class=\"member_form_menu\">\n";
	$html .= "<td>発信権限</td>\n";
	$html .= "<td>発信者</td>\n";
	$html .= "<td>状態</td>\n";
	$html .= "<td>件名</td>\n";
	$html .= "<td>作成日</td>\n";
	$html .= "<td>更新日</td>\n";
	if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__view",$_SESSION['authority'])===FALSE)) {
		$html .= "<td>詳細</td>\n";
	}
	if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__del",$_SESSION['authority'])===FALSE)) {
		$html .= "<td>削除</td>\n";
	}
	$html .= "</tr>\n";
	while ($list = $connect_db->fetch_assoc($result)) {
		foreach($list as $key => $val) { $list[$key] = replace_decode($val); }

		if ($list['user_level'] == 1) {
			$from = "管理者";
		} elseif ($list['user_level'] == 2) {
			$from = "エリア長";
		} elseif ($list['user_level'] == 3) {
			$from = "法人";
		} elseif ($list['user_level'] == 4) {
			$from = "教室長";
		} elseif ($list['user_level'] == 5) {
			$from = "先生";
		}
		if ($list['subject']) { $subject = $list['subject']; }
		else { $subject = "--"; }
		$html .= "<tr class=\"member_form_cell\">\n";
		$html .= "<td>$from</td>\n";
		$html .= "<td>".$list['user_name']."</td>\n";
		$html .= "<td>".$L_DISPLAY[$list['display']]."</td>\n";
		$html .= "<td>".$subject."</td>\n";
		$html .= "<td>".$list['regist_date']."</td>\n";
		$html .= "<td>".$list['update_date']."</td>\n";
		if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__view",$_SESSION['authority'])===FALSE)) {
			$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"view\">\n";
			$html .= "<input type=\"hidden\" name=\"announcement_num\" value=\"".$list['announcement_num']."\">\n";
			$html .= "<td><input type=\"submit\" value=\"詳細\"></td>\n";
			$html .= "</form>\n";
		}
		if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__del",$_SESSION['authority'])===FALSE)) {
			$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"del\">\n";
			$html .= "<input type=\"hidden\" name=\"announcement_num\" value=\"".$list['announcement_num']."\">\n";
			$html .= "<td><input type=\"submit\" value=\"削除\"></td>\n";
			$html .= "</form>\n";
		}
		$html .= "</tr>\n";
	}
	$html .= "</table>\n";

	if ($connect_db) {
		$connect_db->close();
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
	global $L_GROUP,$L_DISPLAY;

	if (!$_SESSION['select_db']) { return; }

	//	DB接続
	$connect_db = new connect_db();
	$connect_db->set_db($_SESSION['select_db']);
	$ERROR = $connect_db->set_connect_db();
	if ($ERROR) {
		$html .= ERROR($ERROR);
	}

	$html = "新規登録フォーム";

	if ($_POST) { foreach($_POST as $key => $val) { $$key = $val; } }

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" name=\"form\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(TEMP_ANNOUNCE_FORM);

	if (!$address_type) {
		$INPUTS[ADDRESSID] = array('result'=>'plane','value'=>'受信タイプを選択してください。');
	} elseif ($address_type == 1) {
		$INPUTS[ADDRESSID] = array('result'=>'plane','value'=>'生徒全員にアナウンスします。');
	} elseif ($address_type == 2) {
		$sql = "SELECT group_list.sgl_id,group_list.group_name FROM " .
			T_STUDENT_GROUP_LIST . " group_list," . T_SCHOOL . " " . T_SCHOOL .
			" WHERE group_list.school_id='" . $_SESSION[set_enterprise][school_id] . "'" .
			" AND group_list.school_id=school.school_id" .
			" AND group_list.state!='1' AND school.mk_flg!='1' AND group_list.display='1'";
		if ($result = $connect_db->query($sql)) {
			$max = $connect_db->num_rows($result);
		}
		if ($max) {
			$L_STUDENT[0] = "選択してください。";
			while ($list = $connect_db->fetch_assoc($result)) {
				$group_name = $list['group_name'];
				$group_name = mb_strimwidth($group_name,0,20,"...","utf-8");
				$L_STUDENT[$list['sgl_id']] = $group_name;
			}
			$INPUTS[ADDRESSID] = array('type'=>'select','name'=>'address_id','array'=>$L_STUDENT,'check'=>$address_id);
		} else {
			$INPUTS[ADDRESSID] = array('result'=>'plane','value'=>'<span style="color:#cc0000;">グループは設定されておりません。</span>');
		}
	} elseif ($address_type == 3) {
//        $sql = "SELECT student.student_id,student.student_myj,student.student_nme FROM " .
		$sql = "SELECT student.student_id,". convertDecryptField('student.student_myj') .",". convertDecryptField('student.student_myj') ." FROM " . // kaopiz 2020/08/20 Encoding
			T_STUDENT . " " . T_STUDENT . "," . T_SCHOOL . " " . T_SCHOOL . "," . T_ENTERPRISE . " " . T_ENTERPRISE .
			" WHERE student.mk_flg!='1' AND school.mk_flg!='1' AND enterprise.mk_flg!='1'" .
//knry_end_flg追加　2009/11/05　hirano
			" AND student.knry_end_flg='0'".
			" AND student.school_id='" . $_SESSION[set_enterprise][school_id] . "'" .
			" AND student.school_id=school.school_id" .
			" AND enterprise.enterprise_id=school.enterprise_id";
		if ($result = $connect_db->query($sql)) {
			$max = $connect_db->num_rows($result);
		}
		if ($max) {
			$L_STUDENT[0] = "選択してください。";
			while ($list = $connect_db->fetch_assoc($result)) {
				$student_name = $list['student_myj']." ".$list['student_nme'];
				$student_name = mb_strimwidth($student_name,0,30,"...","utf-8");
				$L_STUDENT[$list['student_id']] = $student_name;
			}
			$INPUTS[ADDRESSID] = array('type'=>'select','name'=>'address_id','array'=>$L_STUDENT,'check'=>$address_id);
		} else {
			$INPUTS[ADDRESSID] = array('result'=>'plane','value'=>'<span style="color:#cc0000;">生徒は設定されておりません。</span>');
		}
	}

	$INPUTS[ADDRESSTYPE] = array('type'=>'select','name'=>'address_type','array'=>$L_GROUP,'check'=>$address_type,'action'=>'onchange="submit_mode(this.form);"');
	$INPUTS[SUBJECT] = array('type'=>'text','name'=>'subject','size'=>'50','value'=>$subject);
	$INPUTS[MESSAGE] = array('type'=>'textarea','name'=>'message','cols'=>'50','rows'=>'5','value'=>$message);

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
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"member_list\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	if ($connect_db) {
		$connect_db->close();
	}

	return $html;
}

/**
 *
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーならば
 */
function check() {

	//	DB接続
	$connect_db = new connect_db();
	$connect_db->set_db($_SESSION['select_db']);
	$ERROR = $connect_db->set_connect_db();
	if ($ERROR) {
		$html .= ERROR($ERROR);
	}

	if (!$_POST['address_type']) { $ERROR[] = "受信タイプが未入力です。"; }
	if (!$_POST['address_id']) {
		if ($_POST['address_type'] != 1) {
			$ERROR[] = "受信先が未入力です。";
		}
	}
	if ($_POST['announcement_num']) {
		$sql = "SELECT user_level FROM " .
			T_ANNOUNCEMENT .
			" WHERE announcement_num='".$_POST['announcement_num']."' AND state!='1' LIMIT 1;";

		$result = $connect_db->query($sql);
		$list = $connect_db->fetch_assoc($result);
		if ($list['user_level'] != 5) {
			if (!$_POST['subject']) { $ERROR[] = "件名が未入力です。"; }
		}
	} else {
		if (!$_POST['subject']) { $ERROR[] = "件名が未入力です。"; }
	}
	if (!$_POST['message']) { $ERROR[] = "メッセージが未入力です。"; }
	if (!$_POST['display']) { $ERROR[] = "表示モードが未入力です。"; }

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

	global $L_GROUP,$L_DISPLAY;

	if (!$_SESSION['select_db']) { return; }

	//	DB接続
	$connect_db = new connect_db();
	$connect_db->set_db($_SESSION['select_db']);
	$ERROR = $connect_db->set_connect_db();
	if ($ERROR) {
		$html .= ERROR($ERROR);
	}

	$sql = "SELECT user_level FROM " . T_ANNOUNCEMENT .
		" WHERE announcement_num='".$_POST['announcement_num']."' AND state!='1' LIMIT 1;";

	$result = $connect_db->query($sql);
	$list = $connect_db->fetch_assoc($result);
	if ($list['user_level'] == 5) { $no_subject = 1; }

	$action = ACTION;
	if ($action) {
		foreach ($_POST as $key => $val) { $$key = $val; }
	} else {
		$sql = "SELECT announcement.announcement_num,announcement.address_type,announcement.address_id," .
			"announcement.subject,announcement.message,announcement.display FROM " .
			T_ANNOUNCEMENT .
			" WHERE announcement_num='".$_POST['announcement_num']."' AND state!='1' LIMIT 1;";
		$result = $connect_db->query($sql);
		$list = $connect_db->fetch_assoc($result);
		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}
		foreach ($list as $key => $val) {
			$$key = replace_decode($val);
		}
	}

	$html = "詳細画面";

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" name=\"form\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";
	$html .= "<input type=\"hidden\" name=\"announcement_num\" value=\"".$announcement_num."\">\n";

	$make_html = new read_html();
	if (!$no_subject) {
		$make_html->set_dir(ADMIN_TEMP_DIR);
		$make_html->set_file(TEMP_ANNOUNCE_FORM);
	} else {
		$make_html->set_dir(TEACHER_TEMP_DIR);
		$make_html->set_file(TEMP_ANNOUNCE_FORM);
	}

	if (!$address_type) {
		$INPUTS[ADDRESSID] = array('result'=>'plane','value'=>'受信タイプを選択してください。');
	} elseif ($address_type == 1) {
		$INPUTS[ADDRESSID] = array('result'=>'plane','value'=>'生徒全員にアナウンスします。');
	} elseif ($address_type == 2) {
		$sql = "SELECT group_list.sgl_id,group_list.group_name FROM " .
			T_STUDENT_GROUP_LIST . " group_list" .
			" WHERE group_list.state!='1' AND group_list.display='1'";
		if ($result = $connect_db->query($sql)) {
			$max = $connect_db->num_rows($result);
		}
		if ($max) {
			$L_STUDENT[0] = "選択してください。";
			while ($list = $connect_db->fetch_assoc($result)) {
				$group_name = $list['group_name'];
				$group_name = mb_strimwidth($group_name,0,20,"...","utf-8");
				$L_STUDENT[$list['sgl_id']] = $group_name;
			}
			$INPUTS[ADDRESSID] = array('type'=>'select','name'=>'address_id','array'=>$L_STUDENT,'check'=>$address_id);
		} else {
			$INPUTS[ADDRESSID] = array('result'=>'plane','value'=>'<span style="color:#cc0000;">グループは設定されておりません。</span>');
		}
	} elseif ($address_type == 3) {
//        $sql = "SELECT student.student_id,student.student_myj,student.student_nme FROM " .
		$sql = "SELECT student.student_id,". convertDecryptField('student.student_myj') .",". convertDecryptField('student.student_nme') ." FROM " . // kaopiz 2020/08/20 Encoding
			T_STUDENT . " " . T_STUDENT .
//knry_end_flg追加　2009/11/05　hirano
			" WHERE student.mk_flg!='1' AND student.knry_end_flg='0'";
		if ($result = $connect_db->query($sql)) {
			$max = $connect_db->num_rows($result);
		}
		if ($max) {
			$L_STUDENT[0] = "選択してください。";
			while ($list = $connect_db->fetch_assoc($result)) {
				$student_name = $list['student_myj']." ".$list['student_nme'];
				$student_name = mb_strimwidth($student_name,0,30,"...","utf-8");
				$L_STUDENT[$list['student_id']] = $student_name;
			}
			$INPUTS[ADDRESSID] = array('type'=>'select','name'=>'address_id','array'=>$L_STUDENT,'check'=>$address_id);
		} else {
			$INPUTS[ADDRESSID] = array('result'=>'plane','value'=>'<span style="color:#cc0000;">生徒は設定されておりません。</span>');
		}
	}

	$INPUTS[ADDRESSTYPE] = array('type'=>'select','name'=>'address_type','array'=>$L_GROUP,'check'=>$address_type,'action'=>'onchange="submit_mode(this.form);"');
	$INPUTS[SUBJECT] = array('type'=>'text','name'=>'subject','size'=>'50','value'=>$subject);
	$INPUTS[MESSAGE] = array('type'=>'textarea','name'=>'message','cols'=>'50','rows'=>'5','value'=>$message);

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
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"member_list\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	if ($connect_db) {
		$connect_db->close();
	}

	return $html;
}

/**
 *
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function check_html() {
	global $L_GROUP,$L_DISPLAY;

	if (!$_SESSION['select_db']) { return; }

	//	DB接続
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

	if ($_POST['announcement_num']) {
		$sql = "SELECT user_level FROM " .
			T_ANNOUNCEMENT .
			" WHERE announcement_num='".$_POST['announcement_num']."' AND state!='1' LIMIT 1;";

		$result = $connect_db->query($sql);
		$list = $connect_db->fetch_assoc($result);
		if ($list['user_level'] == 5) { $no_subject = 1; }
	}

	$action = ACTION;
	if ($action) {
		foreach ($_POST as $key => $val) { $$key = $val; }
	} else {
		$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"change\">\n";
		$sql = "SELECT announcement.announcement_num,announcement.address_type,announcement.address_id," .
			"announcement.subject,announcement.message,announcement.display FROM " .
			T_ANNOUNCEMENT .
			" WHERE announcement_num='".$_POST['announcement_num']."' AND state!='1' LIMIT 1;";

		$result = $connect_db->query($sql);
		$list = $connect_db->fetch_assoc($result);
		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}
		foreach ($list as $key => $val) {
			$$key = replace_decode($val);
		}
	}
	if ($address_id) {
		if ($address_type == 1) {
			$name = "生徒全員にアナウンスします。";
		} elseif ($address_type == 2) {
			$sql = "SELECT group_name FROM " .T_STUDENT_GROUP_LIST .
					" WHERE sgl_id='$address_id' AND state!='1'";
			$result = $connect_db->query($sql);
			$list = $connect_db->fetch_assoc($result);
			$group_name = $list['group_name'];
			$group_name = mb_strimwidth($group_name,0,20,"...","utf-8");
			$name = $group_name;
		} elseif ($address_type == 3) {
//            $sql = "SELECT student_myj,student_nme FROM " . T_STUDENT .
			$sql = "SELECT ". convertDecryptField('student_myj') .",". convertDecryptField('student_nme') ." FROM " . T_STUDENT . // kaopiz 2020/08/20 Encoding
//knry_end_flg追加　2009/11/05　hirano
				" WHERE student_id='$address_id' AND mk_flg!='1' AND student.knry_end_flg='0' LIMIT 1;";
			$result = $connect_db->query($sql);
			$list = $connect_db->fetch_assoc($result);
			$student_name = $list['student_myj']." ".$list['student_nme'];
			$student_name = mb_strimwidth($student_name,0,30,"...","utf-8");
			$name = $student_name;
		}
	} else {
		if ($address_type == 1) {
			$name = "生徒全員にアナウンスします。";
		}
	}

	if (MODE != "del") { $button = "登録"; } else { $button = "削除"; }
	$html = "確認画面：以下の内容で{$button}してもよろしければ{$button}ボタンをクリックしてください。";

	$make_html = new read_html();
	if (!$no_subject) {
		$make_html->set_dir(ADMIN_TEMP_DIR);
		$make_html->set_file(TEMP_ANNOUNCE_FORM);
	} else {
		$make_html->set_dir(TEACHER_TEMP_DIR);
		$make_html->set_file(TEMP_ANNOUNCE_FORM);
	}

//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	$INPUTS[ADDRESSTYPE] = array('result'=>'plane','value'=>$L_GROUP[$address_type]);
	$INPUTS[ADDRESSID] = array('result'=>'plane','value'=>$name);
	$INPUTS[SUBJECT] = array('result'=>'plane','value'=>$subject);
	$INPUTS[MESSAGE] = array('result'=>'plane','value'=>$message);
	$INPUTS[DISPLAY] = array('result'=>'plane','value'=>$L_DISPLAY[$display]);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" style=\"float:left\">\n";
	$html .= $HIDDEN;
	$html .= "<input type=\"submit\" value=\"$button\">\n";
	$html .= "</form>";
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
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

	if ($connect_db) {
		$connect_db->close();
	}

	return $html;
}

/**
 * T_ANNOUNCEMENTテブルに追加する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーがならば
 */
function add() {

	//list($me_id,$manager_level,$onetime) = explode("<>",$_SESSION[myid]);

	foreach ($_POST as $key => $val) {
		if ($key == "action") { continue; }
		$INSERT_DATA[$key] = "$val";
	}
	$INSERT_DATA['school_id'] = $_SESSION['set_enterprise']['school_id'];
	if ($_POST[address_type] == 1) {
		$INSERT_DATA['address_id'] = $_SESSION['set_enterprise']['school_id'];
	}
	$INSERT_DATA['user_id'] = $_SESSION['myid']['id'];
	$INSERT_DATA['user_level'] = $_SESSION['myid']['level'];
	$INSERT_DATA['address_level'] = "6";
	$INSERT_DATA['regist_date'] = "now()";
	$INSERT_DATA['update_date'] = "now()";

	//	DB接続
	$connect_db = new connect_db();
	$connect_db->set_db($_SESSION['select_db']);
	$ERROR = $connect_db->set_connect_db();
	if ($ERROR) {
		$html .= ERROR($ERROR);
	}

	$ERROR = $connect_db->insert(T_ANNOUNCEMENT,$INSERT_DATA);

	if (!$ERROR) { $_SESSION['select_menu'] = MAIN . "<>" . SUB . "<><><>"; }

	if ($connect_db) {
		$connect_db->close();
	}

	return $ERROR;
}

/**
 * T_ANNOUNCEMENTテブルに変更する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーがならば
 */
function change() {

	//	DB接続
	$connect_db = new connect_db();
	$connect_db->set_db($_SESSION['select_db']);
	$ERROR = $connect_db->set_connect_db();
	if ($ERROR) {
		return $ERROR;
	}

	if (MODE == "view") {
		foreach ($_POST as $key => $val) {
			if ($key == "action") { continue; }
			$INSERT_DATA[$key] = "$val";
		}
		$INSERT_DATA['update_date'] = "now()";

		$where = " WHERE announcement_num='".$_POST['announcement_num']."' LIMIT 1;";
		$ERROR = $connect_db->update(T_ANNOUNCEMENT,$INSERT_DATA,$where);

	} elseif (MODE == "del") {
		$INSERT_DATA[state] = "1";
		$INSERT_DATA[update_date] = "now()";
		$where = " WHERE announcement_num='".$_POST['announcement_num']."' LIMIT 1;";
		$ERROR = $connect_db->update(T_ANNOUNCEMENT,$INSERT_DATA,$where);

	}

	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }

	if ($connect_db) {
		$connect_db->close();
	}

	return $ERROR;
}
?>