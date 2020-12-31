<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * 保護者・生徒管理　生徒管理
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
		elseif (ACTION == "course") { $ERROR = course_change(); }
		elseif (ACTION == "skip_set") { $ERROR = skip_set(); }
		elseif (ACTION == "skip_set_all") { $ERROR = skip_set_all(); }
		elseif (ACTION == "skip_set_point") { $ERROR = skip_set_point(); }
		elseif (ACTION == "getlesson") { get_lesson(); }
		elseif (ACTION == "getunit") { get_unit(); }
		elseif (ACTION == "make_csv") { $ERROR = make_csv(); }
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
	} elseif (MODE == "course") {
		$html .= course_list($ERROR);
	} elseif (MODE == "member_list") { $html .= member_list(); }
	return $html;
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

	global $L_ORDER,$L_DESC,$L_PAGE_VIEW,$L_SELECT_LOG_DB;
	//	add 2015/01/07 yoshizawa 課題要望一覧No.400対応
	global $L_EXP_CHA_CODE;
	//-------------------------------------------------

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	foreach ($L_SELECT_LOG_DB as $key => $val){
		if ($_SESSION['select_db'] == $val) { $sel = " selected"; } else { $sel = ""; }
		$s_db_html .= "<option value=\"".$key."\"".$sel.">".$val['NAME']."</option>\n";
	}

	if (!MODE || MODE == "member_list" || MODE == "del_enterprise") {
//		if ($_SESSION[set_enterprise][enterprise_id]) { $where = " AND enterprise_id='".$_SESSION[set_enterprise][enterprise_id]."'"; }
		if ($_SESSION['set_enterprise']['school_id']) { $where .= " AND school_id='".$_SESSION['set_enterprise']['school_id']."'"; }

		if ($_SESSION['select_db']) {
			//	DB接続
			$connect_db = new connect_db();
			$connect_db->set_db($_SESSION['select_db']);
			$ERROR = $connect_db->set_connect_db();
			if ($ERROR) {
				$html .= ERROR($ERROR);
			}
//			$sql = "SELECT school_name FROM " . T_STUDENT .
			$sql = "SELECT ". convertDecryptField('school_name') ." FROM " . T_STUDENT . // kaopiz 2020/08/20 Encoding
//knry_end_flg追加　2009/11/05　hirano
				" WHERE mk_flg='0' AND move_flg='0' AND knry_end_flg='0'".
				$where.
				" GROUP BY school_name";
			if ($result = $connect_db->query($sql)) {
				$max = $connect_db->num_rows($result);
				if ($max) {
					$L_SCHOOL[] = "学校";
					while ($list = $connect_db->fetch_assoc($result)) {
						if ($list['school_name']) {
							$L_SCHOOL[] = $list['school_name'];
						}
					}
				}
			}
		}

/*
		//	del ookawara 2010/03/03
		for ($i=7;$i<=18;$i++) {
			if ($i > 6 && $i <=12) {
				$L_GRADE[$i] = "小".($i-6);
			} elseif ($i >= 13 && $i <=15) {
				$L_GRADE[$i] = "中".($i-12);
			} elseif ($i >= 16 && $i <=18) {
				$L_GRADE[$i] = "高".($i-15);
			} elseif ($i >= 19) {
				$L_GRADE[$i] = "大".($i-18);
			}
		}
*/

		//	ソート用のプルダウン作成配列
		//	add ookawara 2010/03/03
		$L_GRADE = array(
			'E1' => '小学１年生',
			'E2' => '小学２年生',
			'E3' => '小学３年生',
			'E4' => '小学４年生',
			'E5' => '小学５年生',
			'E6' => '小学６年生',
			'J1' => '中学１年生',
			'J2' => '中学２年生',
			'J3' => '中学３年生',
			'H1' => '高校１年生',
			'H2' => '高校２年生',
			'H3' => '高校３年生'
		);


		$s_grade_html = "<option value=\"0\">学年</option>\n";
		// upd start hasegawa 2019/11/27 理社対応
		// if ($_SESSION['sub_session']['s_course'] == 1) { $sel1 = " selected"; $sel2 = ""; $sel3 = ""; }
		// if ($_SESSION['sub_session']['s_course'] == 2) { $sel1 = ""; $sel2 = " selected"; $sel3 = ""; }
		// if ($_SESSION['sub_session']['s_course'] == 3) { $sel1 = ""; $sel2 = ""; $sel3 = " selected"; }
		if ($_SESSION['sub_session']['s_course'] == 1) { $sel1 = " selected"; $sel2 = ""; $sel3 = ""; $sel15 = ""; $sel16 = ""; }
		if ($_SESSION['sub_session']['s_course'] == 2) { $sel1 = ""; $sel2 = " selected"; $sel3 = ""; $sel15 = ""; $sel16 = ""; }
		if ($_SESSION['sub_session']['s_course'] == 3) { $sel1 = ""; $sel2 = ""; $sel3 = " selected"; $sel15 = ""; $sel16 = ""; }
		if ($_SESSION['sub_session']['s_course'] == 15) { $sel1 = ""; $sel2 = ""; $sel3 = ""; $sel15 = " selected"; $sel16 = ""; }
		if ($_SESSION['sub_session']['s_course'] == 16) { $sel1 = ""; $sel2 = ""; $sel3 = ""; $sel15 = ""; $sel16 = " selected"; }
		// upd end hasegawa 2019/11/27

		$s_course_html = "<option value=\"0\">受講科目</option>\n";
		$s_course_html .= "<option value=\"1\"$sel1>英語</option>\n";
		$s_course_html .= "<option value=\"2\"$sel2>国語</option>\n";
		$s_course_html .= "<option value=\"3\"$sel3>数学</option>\n";
		$s_course_html .= "<option value=\"15\"$sel15>理科</option>\n";	// add hasegawa 2019/11/27 理社対応
		$s_course_html .= "<option value=\"16\"$sel16>社会</option>\n";	// add hasegawa 2019/11/27 理社対応

		if ($L_SCHOOL) {
			foreach ($L_SCHOOL as $key => $val){
				if(mb_strlen($val) > 8){
					$stu_school_name = mb_substr($val,0,6,"UTF-8")."...";
				} else { $stu_school_name = $val; }
				if ($_SESSION['sub_session']['s_school'] == $val) { $sel = " selected"; } else { $sel = ""; }
				$s_school_html .= "<option value=\"".$val."\"".$sel.">".$stu_school_name."</option>\n";
			}
		}

		foreach ($L_GRADE as $key => $val){
			if ($_SESSION['sub_session']['s_grade'] == $key) { $sel = " selected"; } else { $sel = ""; }
			$s_grade_html .= "<option value=\"".$key."\"".$sel.">".$val."</option>\n";
		}
		foreach ($L_ORDER as $key => $val){
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
		$sub_session_html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" style=\"float:left;margin-left:10px;\">\n";
		$sub_session_html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
		$sub_session_html .= "<td>\n";
		$sub_session_html .= "<span style=\"font-size:12px;\">絞り込み<select name=\"s_school\">\n";
		if ($L_SCHOOL) { $sub_session_html .= $s_school_html."</select><select name=\"s_grade\">\n"; }
		$sub_session_html .= $s_grade_html."</select><select name=\"s_course\">\n";
		$sub_session_html .= $s_course_html."</select>\n";
		$sub_session_html .= "ソート<select name=\"s_order\">\n";
		$sub_session_html .= $s_order_html."</select><select name=\"s_desc\">\n";
		$sub_session_html .= $s_desc_html."</select>表示数<select name=\"s_page_view\">\n";
		$sub_session_html .= $s_page_view_html."</select><input type=\"submit\" value=\"Set\">\n";
		$sub_session_html .= "</span>\n";
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
	$html .= "</form>\n";

	if ($_SESSION['select_db']) {
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"member_list\">\n";
		$html .= "<td><input type=\"submit\" value=\"一覧表示\"></td>\n";
		$html .= "</form>\n";
		//	del 2015/01/07 yoshizawa 課題要望一覧No.400対応 下に新規で作成
		//$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
		//$html .= "<input type=\"hidden\" name=\"action\" value=\"make_csv\">\n";
		//$html .= "<td><input type=\"submit\" value=\"CSVエクスポート\"></td>\n";
		//$html .= "</form>\n";
		//----------------------------------------------------------------

		if ($_SESSION['set_enterprise']===NULL) {
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"set_enterprise\">\n";
			$html .= "<td><input type=\"submit\" value=\"法人指定\"></td>\n";
			$html .= "</form>\n";
		}

/*		if ($_SESSION['set_enterprise']['school_id']!==NULL) {
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
	$html .= "</table>\n";

	//	add 2015/01/07 yoshizawa 課題要望一覧No.400対応
	if ($_SESSION['select_db']) {
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"make_csv\">\n";
		//	プルダウンを作成
		$expList = "";
		if ( is_array($L_EXP_CHA_CODE) ) {
			$expList .= "海外版の場合は、出力形式について[Unicode]選択して、CSVエクスポートボタンをクリックしてください。<br />\n";
			$expList .= "<b>出力形式：</b>";
			$expList .= "<select name=\"exp_list\">";
			foreach( $L_EXP_CHA_CODE as $key => $val ){
				$expList .= "<option value=\"".$key."\">".$val."</option>";
			}
			$expList .= "</select>";

			$html .= $expList;
		}
		$html .= "<input type=\"submit\" value=\"CSVエクスポート\">\n";
		$html .= "</form>\n";
		$html .= "<br />\n";
	}
	//-------------------------------------------------

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
 * SESSION情報設定
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
 * SESSIONから校舎の情報を削除する機能
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
 * メンバーのリストのHTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function member_list() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_PAGE_VIEW,$L_STUDENT_STATUS,$L_ACCOUNT,$L_USE_AREA;

	if (!$_SESSION['select_db']) { return; }

	//	DB接続
	$connect_db = new connect_db();
	$connect_db->set_db($_SESSION['select_db']);
	$ERROR = $connect_db->set_connect_db();
	if ($ERROR) {
		$html .= ERROR($ERROR);
	}

	//	コースコード一時テーブル作成
	tmp_jyko_crs_cd($connect_db);	//	add ookawara 2012/10/31

	//	add ookawara 2010/03/03	start
	//	学年ソート対応の為、一時TABLE作成
	$sql  = "CREATE TEMPORARY TABLE student_list".
//		" SELECT student.student_id, student.student_myj, student.student_nme, student.birthday, student.school_name,".
//		//"tb_stu_jyko_jyotai.jyko_crs_cd,".	//	del ookawara 2012/10/31
//		"tjccl.jyko_crs_cd,".	//	add ookawara 2012/10/31
//		" guardian.prf, guardian.city, guardian.add1, guardian.add2, guardian.tel,".
		// kaopiz 2020/08/20 Encoding start
			" SELECT student.student_id, ". convertDecryptField('student.student_myj') .", ". convertDecryptField('student.student_nme')
				.", student.birthday, ". convertDecryptField('student.school_name') .",". "tjccl.jyko_crs_cd, ".
			convertDecryptField('guardian.prf') .", ". convertDecryptField('guardian.city') .", ". convertDecryptField('guardian.add1') .", ".
			convertDecryptField('guardian.add2') .", ". convertDecryptField('guardian.tel') .",".
		// kaopiz 2020/08/20 Encoding end
			" student.gknn,".
			" CASE".
				" WHEN student.gknn like 'E%' THEN '1'".
				" WHEN student.gknn like 'J%' THEN '2'".
				" WHEN student.gknn like 'H%' THEN '3'".
				" WHEN student.gknn like 'U%' THEN '4'".
				" ELSE '5'".
			" END gknn_sort,".
//			" student.student_myj_kn, student.student_nme_kn,".
			" ". convertDecryptField('student.student_myj_kn') .", ". convertDecryptField('student.student_nme_kn') .",". // kaopiz 2020/08/20 Encoding
			" school.school_name AS c_school_name, school.school_id," .
			" enterprise.enterprise_name, enterprise.enterprise_id".
			" FROM " .
			//T_STUDENT . " student, " .	//	del ookawara 2012/10/31
			//T_GUARDIAN . " guardian, " .	//	del ookawara 2012/10/31
			//T_TB_STU_JYKO_JYOTAI . " tb_stu_jyko_jyotai, " .	//	del ookawara 2012/10/31
			//T_ENTERPRISE . " enterprise, " .	//	del ookawara 2012/10/31
			//T_SCHOOL . " school" .	//	del ookawara 2012/10/31
			//	add ookawara 2012/10/31 start
			T_STUDENT . " student " .
			" INNER JOIN ".T_GUARDIAN." guardian ON student.guardian_id=guardian.guardian_id".
			" INNER JOIN ".T_TB_STU_JYKO_JYOTAI." tb_stu_jyko_jyotai ON student.student_id=tb_stu_jyko_jyotai.student_id".
			" INNER JOIN ".T_SCHOOL." school ON school.school_id=student.school_id".
			" INNER JOIN ".T_ENTERPRISE." enterprise ON enterprise.enterprise_id=school.enterprise_id".
			" INNER JOIN tmp_jyko_crs_cd_list tjccl ON tjccl.student_id=student.student_id".
			//	add ookawara 2012/10/31 end
			" WHERE student.mk_flg='0'".
			" AND student.move_flg='0'" .
			" AND student.knry_end_flg='0'".
			" AND guardian.mk_flg='0'".
			" AND tb_stu_jyko_jyotai.mk_flg='0'" .
			" AND enterprise.mk_flg='0'".
			" AND enterprise.move_flg='0'".
			" AND school.mk_flg='0'" .
			//" AND enterprise.enterprise_id=school.enterprise_id" .	//	del ookawara 2012/10/31
			//" AND school.school_id=student.school_id" .	//	del ookawara 2012/10/31
			//" AND student.student_id=tb_stu_jyko_jyotai.student_id" .	//	del ookawara 2012/10/31
			//" AND student.guardian_id=guardian.guardian_id";	//	del ookawara 2012/10/31
			";";	//	add ookawara 2012/10/31
	//$connect_db->exec_query($sql);
	$ret = $connect_db->exec_query($sql);
	if (!$ret) {
		echo "ret=".$ret." sql error no=".$connect_db->error_no()." message=".$connect_db->error_message();
	}

	$sql  = "SELECT student_id, student_myj, student_nme, birthday, school_name,".
			" jyko_crs_cd, prf, city, add1, add2, tel, gknn, gknn_sort,".
			" c_school_name, enterprise_name".
			" FROM student_list";
//echo $sql."<br>\n";

	$where = "";
	if ($_SESSION['set_enterprise']===NULL) {

	} elseif ($_SESSION['set_enterprise']['school_id']===NULL) {
		$html .= "<p style=\"font-size:12px\">現在、法人[<strong style=\"color:#ff0000;\">".$_SESSION['set_enterprise']['enterprise_name']."</strong>]にセットされています。</p>\n";

		if ($where) { $where .= "AND"; } else { $where .= " WHERE"; }
		$where .= " enterprise_id='".$_SESSION['set_enterprise']['enterprise_id']."'";
	} else {
		$html .= "<p style=\"font-size:12px\">現在、法人[<strong style=\"color:#ff0000;\">".$_SESSION['set_enterprise']['enterprise_name']."</strong>]/校舎名[<strong style=\"color:#ff0000;\">".$_SESSION['set_enterprise']['school_name'] . "</strong>]にセットされています。</p>\n";

		if ($where) { $where .= "AND"; } else { $where .= " WHERE"; }
		$where .= " school_id='".$_SESSION['set_enterprise']['school_id']."'";
	}

	//	学校絞込
	if ($_SESSION['sub_session']['s_school'] && $_SESSION['sub_session']['s_school'] != "学校") {
		if ($where) { $where .= "AND"; } else { $where .= " WHERE"; }
		$where .= " school_name='".$_SESSION['sub_session']['s_school']."'";
	}

	//	学年絞込
	if ($_SESSION['sub_session']['s_grade'] && $_SESSION['sub_session']['s_grade'] != "all") {
		if ($where) { $where .= "AND"; } else { $where .= " WHERE"; }
		$where .= " gknn='".$_SESSION['sub_session']['s_grade']."'";
	}

	//	受講科目絞込
	if ($_SESSION['sub_session']['s_course']) {
		if ($where) { $where .= "AND"; } else { $where .= " WHERE"; }
		$where .= " (" .
			"(jyko_crs_cd IS NULL" .
			") OR (";

		// upd start hasegawa 2019/11/27 理社対応
		// if ($_SESSION['sub_session']['s_course'] == "1") {
		// 	$where .= " (jyko_crs_cd LIKE '1E%'" .
		// 		" OR (jyko_crs_cd='30J'))";
		// } elseif ($_SESSION['sub_session']['s_course'] == "2") {
		// 	 $where .= " (jyko_crs_cd LIKE '1K%'" .
		// 		" OR jyko_crs_cd='30J')";
		// } elseif ($_SESSION['sub_session']['s_course'] == "3") {
		// 	 $where .= " (jyko_crs_cd LIKE '1S%'" .
		// 		" OR jyko_crs_cd='30J')";
		// }
		if ($_SESSION['sub_session']['s_course'] == "1") {
		       $where .= " ("
			       . "jyko_crs_cd LIKE '%E%'"
			       . " OR (jyko_crs_cd LIKE '%30%')"
			       . " OR (jyko_crs_cd LIKE '%50%')"
			       . ")";
		} elseif ($_SESSION['sub_session']['s_course'] == "2") {
		       $where .= " ("
			       . "jyko_crs_cd LIKE '%K%'"
			       . " OR (jyko_crs_cd LIKE '%30%')"
			       . " OR (jyko_crs_cd LIKE '%50%')"
			       . ")";
		} elseif ($_SESSION['sub_session']['s_course'] == "3") {
		       $where .= " ("
			       . "jyko_crs_cd LIKE '%S%'"
			       . " OR (jyko_crs_cd LIKE '%30%')"
			       . " OR (jyko_crs_cd LIKE '%50%')"
			       . ")";
		} elseif ($_SESSION['sub_session']['s_course'] == "15") {
		       $where .= " ("
			       . "jyko_crs_cd LIKE '%B%'"
			       . " OR (jyko_crs_cd LIKE '%20%')"
			       . " OR (jyko_crs_cd LIKE '%50%')"
			       . ")";
		} elseif ($_SESSION['sub_session']['s_course'] == "16") {
		       $where .= " ("
			       . "jyko_crs_cd LIKE '%C%'"
			       . " OR (jyko_crs_cd LIKE '%20%')"
			       . " OR (jyko_crs_cd LIKE '%50%')"
			       . ")";		       
		}
		// upd end hasegawa 2019/11/27
		$where .= "))";
	}
	
	$sql .= $where;
	if ($result = $connect_db->query($sql)) {
		$student_count = $connect_db->num_rows($result);
	}

	if ($_SESSION['sub_session']['s_desc']) {
		$desc = " DESC";
	}else{
		$desc = "";
	}
	if ($_SESSION['sub_session']['s_order'] == 1) {
		//	名前
//		$sql .= " ORDER BY student_myj_kn".$desc.", student_nme_kn".$desc.", student_id".$desc;
		$sql .= " ORDER BY ". convertDecryptField('student_myj_kn', false) ."".$desc.", ". convertDecryptField('student_nme_kn', false) ."".$desc.", student_id".$desc; // kaopiz 2020/08/20 Encoding
	} elseif ($_SESSION['sub_session']['s_order'] == 2) {
		//	学校
//		$sql .= " ORDER BY school_name".$desc.", student_myj_kn".$desc.", student_nme_kn".$desc.", student_id".$desc;
		$sql .= " ORDER BY school_name".$desc.", ". convertDecryptField('student_myj_kn', false) .$desc.", ". convertDecryptField('student_nme_kn', false) ."".$desc.", student_id".$desc; // kaopiz 2020/08/20 Encoding
	} elseif ($_SESSION['sub_session']['s_order'] == 3) {
		//	学年
//		$sql .= " ORDER BY gknn_sort".$desc.", gknn ".$desc.", student_myj_kn".$desc.", student_nme_kn".$desc.", student_id".$desc;
		$sql .= " ORDER BY gknn_sort".$desc.", gknn ".$desc.", ". convertDecryptField('student_myj_kn', false) .$desc.", ". convertDecryptField('student_nme_kn', false) ."".$desc.", student_id".$desc; // kaopiz 2020/08/20 Encoding
	} else {
		//	ID
		$sql .= " ORDER BY student_id".$desc;
	}
	//	add ookawara 2010/03/03	end

	if ($L_PAGE_VIEW[$_SESSION['sub_session']['s_page_view']]) { $page_view = $L_PAGE_VIEW[$_SESSION['sub_session']['s_page_view']]; }
	else { $page_view = $L_PAGE_VIEW[0]; }
	$max_page = ceil($student_count/$page_view);
	if ($_SESSION['sub_session']['s_page']) { $page = $_SESSION['sub_session']['s_page']; }
	else { $page = 1; }
	$start = ($page - 1) * $page_view;
	$next = $page + 1;
	$back = $page - 1;

	$sql .= " LIMIT ".$start.",".$page_view.";";

	if ($result = $connect_db->query($sql)) {
		$max = $connect_db->num_rows($result);
	}
	if (!$max) {
		$html .= "現在、登録生徒は存在しません。";
		return $html;
	}
	$html .= "<div style=\"float:left;\">登録生徒総数(".$student_count."):PAGE[".$page."/".$max_page."]</div>\n";

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
	$html .= "<td rowspan=\"2\">ID</td>\n";
	$html .= "<td rowspan=\"2\">名前</td>\n";
	$html .= "<td rowspan=\"2\">学校</td>\n";
	$html .= "<td rowspan=\"2\">教室名</td>\n";
	$html .= "<td rowspan=\"2\">学年</td>\n";
	// $html .= "<td colspan=\"3\">選択科目</td>\n";	// upd hasegawa 2019/11/27 理社対応
	$html .= "<td colspan=\"5\">選択科目</td>\n";
	$html .= "<td rowspan=\"2\">詳細</td>\n";
	$html .= "</tr>\n";
	$html .= "<tr class=\"member_form_menu\">\n";
	$html .= "<td>英語</td>\n";
	$html .= "<td>国語</td>\n";
	$html .= "<td>数学</td>\n";
	$html .= "<td>理科</td>\n";	// add hasegawa 2019/11/27 理社対応
	$html .= "<td>社会</td>\n";	// add hasegawa 2019/11/27 理社対応
	$html .= "</tr>\n";
	while ($list = $connect_db->fetch_assoc($result)) {
		foreach($list as $key => $val) { $list[$key] = replace_decode($val); }
		// list($eng,$jap,$math) = check_use_course($list['jyko_crs_cd']);	// upd hasegawa 2019/11/27 理社対応
		$list['jyko_crs_cd'] = crs_cd_filter($list['jyko_crs_cd']); //add kimura 2020/04/02 理社対応_202004
		list($eng,$jap,$math,$sc,$so) = check_use_course($list['jyko_crs_cd']);
//		$grade = check_grade($list['birthday']);	//	del ookawara 2010/03/03

		// add start yoshizawa 2015/10/27 02_作業要件/34_数学検定/数学検定
		$OPTION_L = array();
		// SQL文生成
		$sql = " SELECT " .
				"  mc.sysi_crs_cd " .
				" FROM " . T_TB_STU_MSKM_CRS . " mc " .
				" WHERE TO_DAYS(mc.jyko_start_ymd) <= TO_DAYS(now()) " .
				"   AND mc.mk_flg = 0 " .
				// "   AND mc.srvc_cd != 'GTEST' " .		// del 2020/09/17 hau テスト標準化開発
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

		//	学年表示設定	//	add ookawara 2010/03/02
		$grade = "";
		if ($list['gknn']) {
			$grade = check_grade_gknn($list['gknn']);
		}

		$html .= "<tr class=\"member_form_cell\">\n";
		$html .= "<td>".$list['student_id']."</td>\n";
		$html .= "<td>".$list['student_myj']." ".$list['student_nme']."</td>\n";
		$html .= "<td>".$list['school_name']."</td>\n";
		$html .= "<td>".$list['c_school_name']."</td>\n";
		$html .= "<td>".$grade."</td>\n";
		$html .= "<td style=\"text-align: center;\">".$eng."</td>\n";
		$html .= "<td style=\"text-align: center;\">".$jap."</td>\n";
		$html .= "<td style=\"text-align: center;\">".$math."</td>\n";
		$html .= "<td style=\"text-align: center;\">".$sc."</td>\n";	// add hasegawa 2019/11/27 理社対応
		$html .= "<td style=\"text-align: center;\">".$so."</td>\n";	// add hasegawa 2019/11/27 理社対応
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"view\">\n";
		$html .= "<input type=\"hidden\" name=\"student_id\" value=\"".$list['student_id']."\">\n";
		$html .= "<input type=\"hidden\" name=\"enterprise_name\" value=\"".$list['enterprise_name']."\">\n";
		$html .= "<input type=\"hidden\" name=\"enterprise_id\" value=\"".$list['enterprise_id']."\">\n";
		$html .= "<input type=\"hidden\" name=\"c_school_name\" value=\"".$list['c_school_name']."\">\n";
		$html .= "<input type=\"hidden\" name=\"school_id\" value=\"".$list['school_id']."\">\n";
		$html .= "<td><input type=\"submit\" value=\"詳細\"></td>\n";
		$html .= "</form>\n";
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
	global $L_PRF,$L_MENU,$L_DO,$L_STUDENT_STATUS,$L_ACCOUNT,$L_USE_AREA;

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
	$make_html->set_file(STUDENT_TEMP_MEMBER_FORM);

//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	$INPUTS['CSCHOOLNAME'] = array('result'=>'plane','value'=>$_SESSION['set_enterprise']['school_name']);
	$INPUTS['SCHOOLID'] = array('result'=>'plane','value'=>$_SESSION['set_enterprise']['school_id']);
	$INPUTS['ENTERPRISENAME'] = array('result'=>'plane','value'=>$_SESSION['set_enterprise']['enterprise_name']);
	$INPUTS['ENTERPRISEID'] = array('result'=>'plane','value'=>$_SESSION['set_enterprise']['enterprise_id']);

	$INPUTS['STUDENTMYJ'] = array('type'=>'text','name'=>'student_myj','value'=>$_POST['student_myj']);
	$INPUTS['STUDENTNME'] = array('type'=>'text','name'=>'student_nme','value'=>$_POST['student_nme']);
	$INPUTS['STUDENTMYJKANA'] = array('type'=>'text','name'=>'student_myj_kn','value'=>$_POST['student_myj_kn']);
	$INPUTS['STUDENTNMEKANA'] = array('type'=>'text','name'=>'student_nme_kn','value'=>$_POST['student_nme_kn']);
	$INPUTS['STUDENTID'] = array('type'=>'text','name'=>'student_id','value'=>$_POST['student_id']);
	$INPUTS['SCHOOLNAME'] = array('type'=>'text','name'=>'school_name','value'=>$_POST['school_name']);
	$INPUTS['STUDENTEMAIL'] = array('type'=>'text','name'=>'student_email','value'=>$_POST['student_email']);
	$INPUTS['STUDENTMOBILEEMAIL'] = array('type'=>'text','name'=>'student_mobile_email','value'=>$_POST['student_mobile_email']);

	$INPUTS['NEED'] = array('result'=>'plane','value'=>"<span style=\"font-size:80%;color:#ff0000;\">必須</span>");

	$newform = new form_parts();
	$newform->set_form_type("text");
	$newform->set_form_name("birthday");
	$newform->set_form_value($_POST['birthday']);
	$birthday_html = $newform->make();
	$birthday_html .= "(YYYY/MM/DD)";
	$INPUTS['BIRTHDAY'] = array('result'=>'plane','value'=>$birthday_html);

	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("student_sex");
	$newform->set_form_id("male");
	$newform->set_form_check($_POST['student_sex']);
	$newform->set_form_value('1');
	$male = $newform->make();
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("student_sex");
	$newform->set_form_id("female");
	$newform->set_form_check($_POST['student_sex']);
	$newform->set_form_value('2');
	$female = $newform->make();
	$sex = $male . "<label for=\"male\">男</label> / " . $female . "<label for=\"female\">女</label>";
	$INPUTS['STUDENTSEX'] = array('result'=>'plane','value'=>$sex);

	$newform = new form_parts();
	$newform->set_form_type("text");
	$newform->set_form_name("admissionday");
	$newform->set_form_value($_POST['admissionday']);
	$admissionday_html = $newform->make();
	$admissionday_html .= "(YYYY/MM/DD)";
	$INPUTS['ADMISSIONDAY'] = array('result'=>'plane','value'=>$admissionday_html);

	$newform = new form_parts();
	$newform->set_form_type("text");
	$newform->set_form_name("secessionday");
	$newform->set_form_value($_POST['secessionday']);
	$secessionday_html = $newform->make();
	$secessionday_html .= "(YYYY/MM/DD)";
	$INPUTS['SECESSIONDAY'] = array('result'=>'plane','value'=>$secessionday_html);

	$make_html->set_rep_cmd($INPUTS);
	$html .= $make_html->replace();

	if ($_POST['guardian_id']) {
		$html .= "保護者情報<br>\n";
		$html .= "<span style=\"color:#cc0000;font-size:12px;\">(※保護者IDが決まっている場合は入力後決定ボタンをクリックしてください。)</span><br>";
		$html .= "<table class=\"member_form\">\n";
		$html .= "<tr>\n";
		$html .= "<td class=\"member_form_menu\">保護者ID<span style=\"font-size:80%;color:#ff0000;\">必須</span></td>\n";
		$html .= "<td class=\"member_form_cell\"><input type=\"text\" name=\"guardian_id\" id=\"guardian_id\"><input type=\"button\" value=\"決定\" onclick=\"guardian_check();\"></td>\n";
		$html .= "</tr>\n";
		$html .= "</table>\n";
		$html .= "<div id=\"guardian_area\">\n";
		$html .= "</div>\n";

		$sql = "SELECT * FROM " . T_GUARDIAN .
			" WHERE guardian_id='".$_POST['guardian_id']."' AND mk_flg!='1' LIMIT 1;";
		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);

		foreach ($list as $key => $val) {
			if ($key == "student_pass") { continue; }
			if ($key == "guardian_pass") { continue; }
			$$key = replace_decode($val);
		}

		$make_html = new read_html();
		$make_html->set_dir(ADMIN_TEMP_DIR);
		$make_html->set_file(GUARDIAN_TEMP_MEMBER_FORM);

		$INPUTS['GUARDIANMYJ'] = array('result'=>'plane','value'=>$guardian_myj);
		$INPUTS['GUARDIANNME'] = array('result'=>'plane','value'=>$guardian_nme);
		$INPUTS['GUARDIANMYJKANA'] = array('result'=>'plane','value'=>$guardian_myj_kn);
		$INPUTS['GUARDIANNMEKANA'] = array('result'=>'plane','value'=>$guardian_nme_kn);
		$INPUTS['GUARDIANID'] = array('result'=>'plane','value'=>$guardian_id);
		$INPUTS['GUARDIANEMAIL'] = array('result'=>'plane','value'=>$guardian_email);
		$INPUTS['GUARDIANMOBILEEMAIL'] = array('result'=>'plane','value'=>$guardian_mobile_email);
		$INPUTS['ZIP'] = array('result'=>'plane','value'=>$zip);
		$INPUTS['COUNTRY'] = array('result'=>'plane','value'=>$country);
		$INPUTS['PRF'] = array('result'=>'plane','value'=>$L_PRF[$prf]);
		$INPUTS['CITY'] = array('result'=>'plane','value'=>$city);
		$INPUTS['ADD1'] = array('result'=>'plane','value'=>$add1);
		$INPUTS['ADD2'] = array('result'=>'plane','value'=>$add2);
		$INPUTS['TEL'] = array('result'=>'plane','value'=>$tel);
		$INPUTS['MOBILE'] = array('result'=>'plane','value'=>$mobile);
		$INPUTS['FAX'] = array('result'=>'plane','value'=>$fax);
		$INPUTS['EMERGENCYCONTACT'] = array('result'=>'plane','value'=>$emergency_contact);
		$INPUTS['EMERGENCYTEL'] = array('result'=>'plane','value'=>$emergency_tel);
		$INPUTS['LOGINEMAIL'] = array('result'=>'plane','value'=>$L_DO[$login_email]);
		$INPUTS['REMARKS'] = array('result'=>'plane','cols'=>'50','rows'=>'5','value'=>$remarks);

		$make_html->set_rep_cmd($INPUTS);
		$html .= $make_html->replace();
		$html .= "<input type=\"hidden\" name=\"guardian_id\" value=\"".$guardian_id."\">\n";
	} else {
		$html .= "保護者情報<br>\n";
		$html .= "<span style=\"color:#cc0000;font-size:12px;\">(※保護者IDが決まっている場合は入力後決定ボタンをクリックしてください。)</span><br>";
		$html .= "<table class=\"member_form\">\n";
		$html .= "<tr>\n";
		$html .= "<td class=\"member_form_menu\">保護者ID<span style=\"font-size:80%;color:#ff0000;\">必須</span></td>\n";
		$html .= "<td class=\"member_form_cell\"><input type=\"text\" name=\"guardian_id\" id=\"guardian_id\"><input type=\"button\" value=\"決定\" onclick=\"guardian_check();\"></td>\n";
		$html .= "</tr>\n";
		$html .= "</table>\n";
		$html .= "<div id=\"guardian_area\">\n";
		$html .= "</div>\n";
	}

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
 * @return array エラーならば
 */
function check() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (!$_POST['student_myj']) { $ERROR[] = "名前(姓)が未入力です。"; }
	if (!$_POST['student_nme']) { $ERROR[] = "名前(名)が未入力です。"; }
	if (!$_POST['student_myj_kn']) { $ERROR[] = "名前カナ(姓)が未入力です。"; }
	if (!$_POST['student_nme_kn']) { $ERROR[] = "名前カナ(名)が未入力です。"; }
	if (!$_POST['student_id']) { $ERROR[] = "IDが未入力です。"; }
	elseif (strlen($_POST['student_id']) > 20) {$ERROR[] = "保護者IDは20文字以内にしてください。";}
	if ($_POST['student_id'] && MODE == "addform") {
		$sql = "SELECT student_id FROM ".T_STUDENT." WHERE student_id='".$_POST['student_id']."' LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$max = $cdb->num_rows($result);
		}
		if ($max) { $ERROR[] = "入力されたIDは既に利用されています。変更してください。"; }
	}

	if (!$_POST['guardian_id']) { $ERROR[] = "保護者を決定してください。"; }

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
	global $L_SEX,$L_PRF,$L_MENU,$L_DO,$L_STUDENT_STATUS,$L_ACCOUNT,$L_USE_AREA;

	if (!$_SESSION['select_db']) { return; }

	//	DB接続
	$connect_db = new connect_db();
	$connect_db->set_db($_SESSION['select_db']);
	$ERROR = $connect_db->set_connect_db();
	if ($ERROR) {
		$html .= ERROR($ERROR);
	}

	$action = ACTION;

	if ($action) {
		foreach ($_POST as $key => $val) { $$key = $val; }
	} else {
		foreach ($_POST as $key => $val) { $$key = $val; }

		//	コースコード一時テーブル作成
		tmp_jyko_crs_cd($connect_db);	//	add ookawara 2012/10/31
//		$sql = "SELECT student.student_id,student.student_myj,student.student_nme,student.student_myj_kn,student.student_nme_kn," .
//			"student.birthday,".
//			//"tb_stu_jyko_jyotai.jyko_crs_cd,".	//	del ookawara 2012/10/31
//			"tjccl.jyko_crs_cd,".	//	add ookawara 2012/10/31
//			"student.student_sex,student.school_name,student.student_email," .
//			"student.student_mobile_email,student.admissionday,student.secessionday," .
//			"tb_stu_jyko_jyotai.sito_zksi,tb_stu_jyko_jyotai.sito_jyti," .
//			"guardian.guardian_id,guardian.guardian_myj,guardian.guardian_nme," .
//			"guardian.guardian_myj_kn,guardian.guardian_nme_kn,guardian.guardian_email,guardian.guardian_mobile_email," .
//			"guardian.zip,guardian.country,guardian.prf,guardian.city,guardian.add1,guardian.add2,guardian.tel,guardian.fax," .
//			"guardian.emergency_contact,guardian.emergency_tel,guardian.login_email,guardian.usr_bko" .
		// kaopiz 2020/08/20 start
		$sql = "SELECT student.student_id,". convertDecryptField('student.student_myj') .",". convertDecryptField('student.student_nme') .",".
			convertDecryptField('student.student_myj_kn') .",". convertDecryptField('student.student_nme_kn') ."," .
			"student.birthday,".
			//"tb_stu_jyko_jyotai.jyko_crs_cd,".	//	del ookawara 2012/10/31
			"tjccl.jyko_crs_cd,".	//	add ookawara 2012/10/31
			"student.student_sex,". convertDecryptField('student.school_name') .",". convertDecryptField('student.student_email') ."," .
			convertDecryptField('student.student_mobile_email') .",student.admissionday,student.secessionday," .
			"tb_stu_jyko_jyotai.sito_zksi,tb_stu_jyko_jyotai.sito_jyti," .
			"guardian.guardian_id,". convertDecryptField('guardian.guardian_myj') .",". convertDecryptField('guardian.guardian_nme') ."," .
			convertDecryptField('guardian.guardian_myj_kn') .",". convertDecryptField('guardian.guardian_nme_kn') .",". convertDecryptField('guardian.guardian_email') .",".
			convertDecryptField('guardian.guardian_mobile_email') ."," . convertDecryptField('guardian.zip') .",". convertDecryptField('guardian.country') .",".
			convertDecryptField('guardian.prf') .",". convertDecryptField('guardian.city') .",". convertDecryptField('guardian.add1') .",".
			convertDecryptField('guardian.add2') .",". convertDecryptField('guardian.tel') ."," . convertDecryptField('guardian.fax') ."," .
			convertDecryptField('guardian.emergency_contact') .",guardian.emergency_tel,guardian.login_email,guardian.usr_bko" .
			// kaopiz 2020/08/20 Encoding end
			" FROM " .
			//T_STUDENT . " " . T_STUDENT . "," .
			//T_TB_STU_JYKO_JYOTAI . " " . T_TB_STU_JYKO_JYOTAI . "," .
			//T_GUARDIAN . " " . T_GUARDIAN .
			//	add ookawara 2012/10/31 start
			T_STUDENT . " student " .
			" INNER JOIN ".T_TB_STU_JYKO_JYOTAI." tb_stu_jyko_jyotai ON student.student_id=tb_stu_jyko_jyotai.student_id".
				" AND student.school_id=tb_stu_jyko_jyotai.school_id".
			" INNER JOIN ".T_GUARDIAN." guardian ON student.guardian_id=guardian.guardian_id".
			" INNER JOIN tmp_jyko_crs_cd_list tjccl ON tjccl.student_id=student.student_id".
				" AND student.guardian_id=guardian.guardian_id".
			//	add ookawara 2012/10/31 end
			" WHERE student.mk_flg='0'" .
			" AND student.move_flg='0'" .
//knry_end_flg追加　2009/11/05　hirano
			" AND student.knry_end_flg='0'".
			//" AND student.student_id=tb_stu_jyko_jyotai.student_id" .	//	del ookawara 2012/10/31
			//" AND student.school_id=tb_stu_jyko_jyotai.school_id" .	//	del ookawara 2012/10/31
			" AND student.student_id='".$_POST['student_id']."'" .
			//" AND student.school_id=guardian.school_id" .	//	del ookawara 2012/10/31
			//" AND student.guardian_id=guardian.guardian_id" .	//	del ookawara 2012/10/31
			" AND guardian.mk_flg='0'";
//echo $sql."<br>\n";
		$result = $connect_db->query($sql);
		$list = $connect_db->fetch_assoc($result);
		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}
		foreach ($list as $key => $val) {
			$$key = replace_decode($val);
		}
		if ($birthday == "0000-00-00") { $birthday = ""; }
		else { $birthday = ereg_replace("-","/",$birthday); }
		if ($admissionday == "0000-00-00") { $admissionday = ""; }
		else { $admissionday = ereg_replace("-","/",$admissionday); }
		if ($secessionday == "0000-00-00") { $secessionday = ""; }
		else { $secessionday = ereg_replace("-","/",$secessionday); }
		foreach ($_POST as $key => $val) { $$key = $val; }
	}

	$html = "詳細画面";

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";
	$html .= "<input type=\"hidden\" name=\"student_num\" value=\"$student_num\">\n";
	$html .= "<input type=\"hidden\" name=\"guardian_num\" value=\"$guardian_num\">\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(STUDENT_TEMP_MEMBER_FORM);

	$INPUTS['CSCHOOLNAME'] = array('result'=>'plane','value'=>$_POST['c_school_name']);
	$INPUTS['SCHOOLID'] = array('result'=>'plane','value'=>$_POST['school_id']);
	$INPUTS['ENTERPRISENAME'] = array('result'=>'plane','value'=>$_POST['enterprise_name']);
	$INPUTS['ENTERPRISEID'] = array('result'=>'plane','value'=>$_POST['enterprise_id']);

	$INPUTS['STUDENTMYJ'] = array('result'=>'plane','value'=>$student_myj);
	$INPUTS['STUDENTNME'] = array('result'=>'plane','value'=>$student_nme);
	$INPUTS['STUDENTMYJKANA'] = array('result'=>'plane','value'=>$student_myj_kn);
	$INPUTS['STUDENTNMEKANA'] = array('result'=>'plane','value'=>$student_nme_kn);
	$INPUTS['STUDENTID'] = array('result'=>'plane','value'=>$student_id);
	$INPUTS['SCHOOLNAME'] = array('result'=>'plane','value'=>$school_name);
	$INPUTS['STUDENTEMAIL'] = array('result'=>'plane','value'=>$student_email);
	$INPUTS['STUDENTMOBILEEMAIL'] = array('result'=>'plane','value'=>$student_mobile_email);

	$INPUTS['BIRTHDAY'] = array('result'=>'plane','value'=>$birthday);
	$INPUTS['STUDENTSEX'] = array('result'=>'plane','value'=>$L_SEX[$student_sex]);
	$INPUTS['ADMISSIONDAY'] = array('result'=>'plane','value'=>$admissionday);
	$INPUTS['SECESSIONDAY'] = array('result'=>'plane','value'=>$secessionday);

	$make_html->set_rep_cmd($INPUTS);
	$html .= $make_html->replace();

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(GUARDIAN_TEMP_MEMBER_FORM);

	$INPUTS['SCHOOLNAME'] = array('result'=>'plane','value'=>$_POST['c_school_name']);

	$INPUTS['GUARDIANMYJ'] = array('result'=>'plane','value'=>$guardian_myj);
	$INPUTS['GUARDIANNME'] = array('result'=>'plane','value'=>$guardian_nme);
	$INPUTS['GUARDIANMYJKANA'] = array('result'=>'plane','value'=>$guardian_myj_kn);
	$INPUTS['GUARDIANNMEKANA'] = array('result'=>'plane','value'=>$guardian_nme_kn);
	$INPUTS['GUARDIANID'] = array('result'=>'plane','value'=>$guardian_id);
	$INPUTS['GUARDIANEMAIL'] = array('result'=>'plane','value'=>$guardian_email);
	$INPUTS['GUARDIANMOBILEEMAIL'] = array('result'=>'plane','value'=>$guardian_mobile_email);
	$INPUTS['ZIP'] = array('result'=>'plane','value'=>$zip);
	$INPUTS['COUNTRY'] = array('result'=>'plane','value'=>$country);
	$INPUTS['PRF'] = array('result'=>'plane','value'=>$prf);
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
	$html .= "<div id=\"guardian_area\">\n";
	$html .= $make_html->replace();
	$html .= "<input type=\"hidden\" name=\"guardian_num\" value=\"$guardian_num\">\n";
	$html .= "<input type=\"hidden\" name=\"guardian_id\" value=\"$guardian_id\">\n";
	$html .= "</div>\n";

	$html .= "</form>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"member_list\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}

/**
 * 確認フォームを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function check_html() {

	global $L_PRF,$L_MENU,$L_SEX,$L_DO,$L_ADMISSIONROOT,$L_STUDENT_STATUS,$L_ACCOUNT,$L_USE_AREA;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$TABLE = T_STUDENT;
	$action = ACTION;

	if ($_POST) {
		foreach ($_POST as $key => $val) {
			if ($key == "action") {
				if (MODE == "addform") { $val = "add"; }
				elseif (MODE == "view") { $val = "change"; }
			}
			$HIDDEN .= "<input type=\"hidden\" name=\"$key\" value=\"$val\">\n";
		}
	}

	if ($action) {
		foreach ($_POST as $key => $val) { $$key = $val; }
	} else {
		$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"change\">\n";
		$sql  = "CREATE TEMPORARY TABLE account " .
			"SELECT student_num,account FROM " .
			T_STUDENT_ACCOUNT .
			" WHERE student_num='".$_POST['student_num']."'" .
			" AND state='0'";
		if (!$cdb->exec_query($sql)) { echo $sql."<br><br>"; }

		$sql = "SELECT * FROM " . T_STUDENT . " student," . T_GUARDIAN . " guardian" .
			" LEFT JOIN account account ON account.student_num=student.student_num" .
			" WHERE student.student_num='".$_POST['student_num']."'" .
			" AND student.guardian_num=guardian.guardian_num" .
			" AND student.state!='1' AND guardian.state!='1' LIMIT 1;";
		$result = $cdb->query($sql);
		$list=$cdb->fetch_assoc($result);
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
		$HIDDEN .= "<input type=\"hidden\" name=\"guardian_num\" value=\"".$list['guardian_num']."\">\n";
	}

	if (MODE != "del") { $button = "登録"; } else { $button = "削除"; }
	$html = "確認画面：以下の内容で{$button}してもよろしければ{$button}ボタンをクリックしてください。<br>";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(STUDENT_TEMP_MEMBER_FORM);

//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	$INPUTS['CSCHOOLNAME'] = array('result'=>'plane','value'=>$_SESSION['set_enterprise']['school_name']);
	$INPUTS['SCHOOLID'] = array('result'=>'plane','value'=>$_SESSION['set_enterprise']['school_id']);
	$INPUTS['ENTERPRISENAME'] = array('result'=>'plane','value'=>$_SESSION['set_enterprise']['enterprise_name']);
	$INPUTS['ENTERPRISEID'] = array('result'=>'plane','value'=>$_SESSION['set_enterprise']['enterprise_id']);

	$INPUTS['STUDENTMYJ'] = array('result'=>'plane','value'=>$student_myj);
	$INPUTS['STUDENTNME'] = array('result'=>'plane','value'=>$student_nme);
	$INPUTS['STUDENTMYJKANA'] = array('result'=>'plane','value'=>$student_myj_kn);
	$INPUTS['STUDENTNMEKANA'] = array('result'=>'plane','value'=>$student_nme_kn);
	$INPUTS['STUDENTID'] = array('result'=>'plane','value'=>$student_id);
	$INPUTS['SCHOOLNAME'] = array('result'=>'plane','value'=>$school_name);
	$INPUTS['STUDENTEMAIL'] = array('result'=>'plane','value'=>$student_email);
	$INPUTS['STUDENTMOBILEEMAIL'] = array('result'=>'plane','value'=>$student_mobile_email);

	$INPUTS['BIRTHDAY'] = array('result'=>'plane','value'=>$birthday);
	$INPUTS['STUDENTSEX'] = array('result'=>'plane','value'=>$L_SEX[$student_sex]);
	$INPUTS['ADMISSIONDAY'] = array('result'=>'plane','value'=>$admissionday);
	$INPUTS['SECESSIONDAY'] = array('result'=>'plane','value'=>$secessionday);

	$make_html->set_rep_cmd($INPUTS);
	$html .= $make_html->replace();

	if ($guardian_id) {
		$sql = "SELECT * FROM " . T_GUARDIAN .
			" WHERE guardian_id='".$guardian_id."' AND mk_flg!='1' LIMIT 1;";

		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);

		foreach ($list as $key => $val) {
			if ($key == "student_pass") { continue; }
			if ($key == "guardian_pass") { continue; }
			$$key = replace_decode($val);
		}

		$make_html = new read_html();
		$make_html->set_dir(ADMIN_TEMP_DIR);
		$make_html->set_file(GUARDIAN_TEMP_MEMBER_FORM);

		$INPUTS['SCHOOLNAME'] = array('result'=>'plane','value'=>$_SESSION['set_enterprise']['school_name']);

		$INPUTS['GUARDIANMYJ'] = array('result'=>'plane','value'=>$guardian_myj);
		$INPUTS['GUARDIANNME'] = array('result'=>'plane','value'=>$guardian_nme);
		$INPUTS['GUARDIANMYJKANA'] = array('result'=>'plane','value'=>$guardian_myj_kn);
		$INPUTS['GUARDIANNMEKANA'] = array('result'=>'plane','value'=>$guardian_nme_kn);
		$INPUTS['GUARDIANID'] = array('result'=>'plane','value'=>$guardian_id);
		$INPUTS['GUARDIANEMAIL'] = array('result'=>'plane','value'=>$guardian_email);
		$INPUTS['GUARDIANMOBILEEMAIL'] = array('result'=>'plane','value'=>$guardian_mobile_email);
		$INPUTS['ZIP'] = array('result'=>'plane','value'=>$zip);
		$INPUTS['COUNTRY'] = array('result'=>'plane','value'=>$country);
		$INPUTS['PRF'] = array('result'=>'plane','value'=>$L_PRF[$prf]);
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
	}

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
 * T_ANNOUNCEMENTテブルに追加する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーがならば
 */
function add() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	foreach ($_POST as $key => $val) {
		if ($key == "action") { continue; }
		elseif ($key == "student_pass2") { continue; }
		elseif ($key == "birthday") { $val = ereg_replace("/","-",$val); }
		elseif ($key == "admissionday") { $val = ereg_replace("/","-",$val); }

		$INSERT_DATA[$key] = "$val";
	}

	if (!$ERROR) {
		$INSERT_DATA['school_id'] = $_SESSION['set_enterprise']['school_id'];

		$ERROR = $cdb->insert(T_STUDENT,$INSERT_DATA);
		$student_num = $cdb->insert_id();
	}

	if (!$ERROR) { $_SESSION['select_menu'] = MAIN . "<>" . SUB . "<><><>"; }
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

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	if (MODE == "view") {
		foreach ($_POST as $key => $val) {
			if ($key == "action") { continue; }
			elseif ($key == "student_pass2") { continue; }
			elseif ($key == "guardian_pass2") { continue; }
			elseif ($key == "cram_school_name") { continue; }
			elseif ($key == "school_code") { continue; }
			elseif ($key == "enterprise_name") { continue; }
			elseif ($key == "enterprise_code") { continue; }
			elseif ($key == "student_num") { continue; }
			elseif ($key == "student_id") { continue; }
			elseif ($key == "student_pass" && $val == "") { continue; }
			elseif ($key == "student_pass") { $INSERT_DATA['def'] = $val; $val = md5($val); }
			elseif ($key == "guardian_pass" && $val == "") { continue; }
			elseif ($key == "guardian_pass") { $val = md5($val); }
			elseif ($key == "birthday") { $val = ereg_replace("/","-",$val); }
			elseif ($key == "admissionday") { $val = ereg_replace("/","-",$val); }
			elseif ($key == "guardian_id") { continue; }
			elseif ($key == "account") { $INSERT_DATA2[$key] = $val; continue; }
			elseif ($key == "use_area") { $INSERT_DATA2[$key] = $val; continue; }

			$INSERT_DATA[$key] = "$val";
		}
//		$INSERT_DATA[student_pass] = md5($INSERT_DATA[def]);
		$INSERT_DATA['s_update_date'] = "now()";

		$where = " WHERE student_num='".$_POST['student_num']."' LIMIT 1;";

		$ERROR = $cdb->update(T_STUDENT,$INSERT_DATA,$where);

		$RESET_DATA['state'] = 1;
		$where = " WHERE student_num='".$_POST['student_num']."' AND state='0';";

		$ERROR = $cdb->update(T_STUDENT_ACCOUNT,$RESET_DATA,$where);

		$INSERT_DATA2['student_num'] = $_POST['student_num'];
		$INSERT_DATA2['regist_time'] = "now()";

		$ERROR = $cdb->insert(T_STUDENT_ACCOUNT,$INSERT_DATA2);

	} elseif (MODE == "del") {
		$INSERT_DATA['state'] = "1";
		$INSERT_DATA['s_update_date'] = "now()";
		$where = " WHERE student_num='".$_POST['student_num']."' LIMIT 1;";

		$ERROR = $cdb->update(T_STUDENT,$INSERT_DATA,$where);

		$RESET_DATA['state'] = 1;
		$where = " WHERE student_num='".$_POST['student_num']."' AND state='0';";

		$ERROR = $cdb->update(T_STUDENT_ACCOUNT,$RESET_DATA,$where);
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
 * @return string
 */
function check_use_course($jyko_crs_cd) {

	$eng = "×";
	$jap = "×";
	$math = "×";
	$sc = "×";	// add hasegawa 2019/11/27 理社対応
	$so = "×";	// add hasegawa 2019/11/27 理社対応
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
	// upd end hasegawa 2019/11/27

}

/**
 * 生徒の記念日から学年を計算する
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param string $birthday
 * @return string
 */
function check_grade($birthday) {
	list($year,$month,$day) = explode("-",$birthday);

	$age = 0;
	if ($year > 1950) {
		$age = date('Y') - $year;
		if ($month < 4) { $age++; }
	}

	if ($age > 6 && $age <=12) {
		$grade = "小".($age-6);
	} elseif ($age >= 13 && $age <=15) {
		$grade = "中".($age-12);
	} elseif ($age >= 16 && $age <=18) {
		$grade = "高".($age-15);
	} elseif ($age >= 19) {
		$grade = "大".($age-18);
	} else {
		$grade = "--";
	}

	return $grade;
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
 * CSVファイルを作る機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function make_csv() {
	global $L_SITO_ZKSI,$L_SITO_JYTI;

	$ERROR = sub_session();
	if ($ERROR) { return $ERROR; }

	if (!$_SESSION['select_db']) { return; }

	//	DB接続
	$connect_db = new connect_db();
	$connect_db->set_db($_SESSION['select_db']);
	$ERROR = $connect_db->set_connect_db();
	if ($ERROR) {
		$html .= ERROR($ERROR);
	}

	$filename = "student_report.txt";

	//	コースコード一時テーブル作成
	tmp_jyko_crs_cd($connect_db);	//	add ookawara 2012/10/31

	if ($_SESSION['set_enterprise']===NULL) {
//echo "check1<br>\n";
//		$sql = "SELECT student.student_id, CONCAT(student.student_myj,student.student_nme) AS student_name,".
		$sql = "SELECT student.student_id, CONCAT(". convertDecryptField('student.student_myj', false) .",". // kaopiz 2020/08/20 Encoding
			convertDecryptField('student.student_nme', false) .") AS student_name,". // kaopiz 2020/08/20 Encoding
			"DATE(student.ins_date) AS s_regist_date,student_para.first_login,student_para.last_login," .
			//"tb_stu_jyko_jyotai.jyko_crs_cd,".	//	del ookawara 2012/10/31
			"tjccl.jyko_crs_cd,".	//	add ookawara 2012/10/31
			"tb_stu_jyko_jyotai.sito_zksi,tb_stu_jyko_jyotai.sito_jyti," .
			"enterprise.enterprise_id,enterprise.enterprise_name,school.school_id,school.school_name," .
//			"guardian.guardian_id,guardian.guardian_myj,guardian.guardian_nme," .
//			"guardian.prf,guardian.city,guardian.add1,guardian.add2,guardian.tel,guardian.guardian_email," .
//			"guardian.guardian_mobile_email" .
			// kaopiz 2020/08/20 Encoding start
			"guardian.guardian_id,". convertDecryptField('guardian.guardian_myj') .",". convertDecryptField('guardian.guardian_nme') ."," .
			convertDecryptField('guardian.prf') .",". convertDecryptField('guardian.city') .",". convertDecryptField('guardian.add1') .",".
			convertDecryptField('guardian.add2') .",". convertDecryptField('guardian.tel') .",". convertDecryptField('guardian.guardian_email') ."," .
			convertDecryptField('guardian.guardian_mobile_email') .
			// kaopiz 2020/08/20 Encoding end
			" FROM " .
			//T_ENTERPRISE . " enterprise,".	//	del ookawara 2012/10/31
			//T_SCHOOL . " school," .	//	del ookawara 2012/10/31
			//T_TB_STU_JYKO_JYOTAI . " tb_stu_jyko_jyotai,".	//	del ookawara 2012/10/31
			//T_STUDENT . " student" .	//	del ookawara 2012/10/31
			//	add ookawara 2012/10/31 start
			T_ENTERPRISE . " enterprise".
			" INNER JOIN ".T_SCHOOL." school ON enterprise.enterprise_id=school.enterprise_id".
			" INNER JOIN ".T_STUDENT." student ON school.school_id=student.school_id".
			" INNER JOIN ".T_TB_STU_JYKO_JYOTAI." tb_stu_jyko_jyotai ON student.student_id=tb_stu_jyko_jyotai.student_id".
			" INNER JOIN tmp_jyko_crs_cd_list tjccl ON tjccl.student_id=student.student_id".
			//	add ookawara 2012/10/31 end
			" LEFT JOIN " . T_GUARDIAN . " guardian ON guardian.guardian_id=student.guardian_id AND guardian.mk_flg='0'" .
			" LEFT JOIN " . T_STUDENT_PARA . " student_para ON student_para.student_id=student.student_id" .
			" WHERE student.mk_flg='0' AND student.move_flg='0'" .
//knry_end_flg追加　2009/11/05　hirano
			" AND student.knry_end_flg='0'".
			" AND enterprise.mk_flg='0'".
			" AND enterprise.move_flg='0'".
			" AND school.mk_flg='0'" .
			" AND tb_stu_jyko_jyotai.mk_flg='0'" .
			//" AND enterprise.enterprise_id=school.enterprise_id" .
			//" AND school.school_id=student.school_id" .
			//" AND student.student_id=tb_stu_jyko_jyotai.student_id";
			"";	//	add ookawara 2012/10/31
	} elseif ($_SESSION['set_enterprise']['school_id']===NULL) {
//echo "check2<br>\n";
		$html .= "<p style=\"font-size:12px\">現在、法人[<strong style=\"color:#ff0000;\">".$_SESSION['set_enterprise']['enterprise_name']."</strong>]にセットされています。</p>\n";
//		$sql = "SELECT student.student_id,CONCAT(student.student_myj,student.student_nme) AS student_name,".
		$sql = "SELECT student.student_id,CONCAT(". convertDecryptField('student.student_myj', false) .",". convertDecryptField('student.student_nme', false) .") AS student_name,". // kaopiz 2020/08/20 Encoding
			"DATE(student.ins_date) AS s_regist_date,student_para.first_login,student_para.last_login," .
			//"tb_stu_jyko_jyotai.jyko_crs_cd,".	//	del ookawara 2012/10/31
			"tjccl.jyko_crs_cd,".	//	add ookawara 2012/10/31
			"tb_stu_jyko_jyotai.sito_zksi,tb_stu_jyko_jyotai.sito_jyti," .
			"enterprise.enterprise_id,enterprise.enterprise_name,school.school_id,school.school_name," .
//			"guardian.guardian_id,guardian.guardian_myj,guardian.guardian_nme," .
//			"guardian.prf,guardian.city,guardian.add1,guardian.add2,guardian.tel,guardian.guardian_email," .
//			"guardian.guardian_mobile_email" .
			// kaopiz 2020/08/20 Encoding start
			"guardian.guardian_id,". convertDecryptField('guardian.guardian_myj') .",". convertDecryptField('guardian.guardian_nme') ."," .
			convertDecryptField('guardian.prf') .",". convertDecryptField('guardian.city') .",". convertDecryptField('guardian.add1') .",". convertDecryptField('guardian.add2') .",". convertDecryptField('guardian.tel') .",". convertDecryptField('guardian.guardian_email') ."," .
			convertDecryptField('guardian.guardian_mobile_email') .
			// kaopiz 2020/08/20 Encoding end
			" FROM " .
			//T_ENTERPRISE . " enterprise,".	//	del ookawara 2012/10/31
			//T_SCHOOL . " school," .	//	del ookawara 2012/10/31
			//T_TB_STU_JYKO_JYOTAI . " tb_stu_jyko_jyotai,".	//	del ookawara 2012/10/31
			//T_STUDENT . " student" .	//	del ookawara 2012/10/31
			//	add ookawara 2012/10/31 start
			T_ENTERPRISE . " enterprise".
			" INNER JOIN ".T_SCHOOL." school ON enterprise.enterprise_id=school.enterprise_id".
			" INNER JOIN ".T_STUDENT." student ON school.school_id=student.school_id".
			" INNER JOIN ".T_TB_STU_JYKO_JYOTAI." tb_stu_jyko_jyotai ON student.student_id=tb_stu_jyko_jyotai.student_id".
			" INNER JOIN tmp_jyko_crs_cd_list tjccl ON tjccl.student_id=student.student_id".
			//	add ookawara 2012/10/31 end
			" LEFT JOIN " . T_GUARDIAN . " guardian ON guardian.guardian_id=student.guardian_id AND guardian.mk_flg='0'" .
			" LEFT JOIN " . T_STUDENT_PARA . " student_para ON student_para.student_id=student.student_id" .
			" WHERE student.mk_flg='0' AND student.move_flg='0'" .
//knry_end_flg追加　2009/11/05　hirano
			" AND student.knry_end_flg='0'".
			" AND enterprise.mk_flg='0' AND enterprise.move_flg='0' AND school.mk_flg='0'" .
			" AND tb_stu_jyko_jyotai.mk_flg='0'" .
			" AND enterprise.enterprise_id='" . $_SESSION['set_enterprise']['enterprise_id'] . "'" .
			//" AND enterprise.enterprise_id=school.enterprise_id" .	//	del ookawara 2012/10/31
			//" AND school.school_id=student.school_id" .	//	del ookawara 2012/10/31
			//" AND student.student_id=tb_stu_jyko_jyotai.student_id";	//	del ookawara 2012/10/31
			"";	//	add ookawara 2012/10/31
	} else {
//echo "check3<br>\n";
		$html .= "<p style=\"font-size:12px\">現在、法人[<strong style=\"color:#ff0000;\">".$_SESSION['set_enterprise']['enterprise_name']."</strong>]/校舎名[<strong style=\"color:#ff0000;\">" .
			$_SESSION['set_enterprise']['school_name'] . "</strong>]にセットされています。</p>\n";
//		$sql = "SELECT student.student_id,CONCAT(student.student_myj,student.student_nme) AS student_name,".
		$sql = "SELECT student.student_id,CONCAT(". convertDecryptField('student.student_myj', false) .",". convertDecryptField('student.student_nme', false) .") AS student_name,". // kaopiz 2020/08/20 Encoding
			"DATE(student.ins_date) AS s_regist_date,student_para.first_login,student_para.last_login," .
			//"tb_stu_jyko_jyotai.jyko_crs_cd,".	//	del ookawara 2012/10/31
			"tjccl.jyko_crs_cd,".	//	add ookawara 2012/10/31
			"tb_stu_jyko_jyotai.sito_zksi,tb_stu_jyko_jyotai.sito_jyti," .
			"enterprise.enterprise_id,enterprise.enterprise_name,school.school_id,school.school_name," .
//			"guardian.guardian_id,guardian.guardian_myj,guardian.guardian_nme," .
//			"guardian.prf,guardian.city,guardian.add1,guardian.add2,guardian.tel,guardian.guardian_email," .
//			"guardian.guardian_mobile_email" .
			// kaopiz 2020/08/20 Encoding start
			"guardian.guardian_id,". convertDecryptField('guardian.guardian_myj') .",". convertDecryptField('guardian.guardian_nme') ."," .
			convertDecryptField('guardian.prf') .",". convertDecryptField('guardian.city') .",". convertDecryptField('guardian.add1') .",". convertDecryptField('guardian.add2') .",". convertDecryptField('guardian.tel') .",". convertDecryptField('guardian.guardian_email') ."," .
			convertDecryptField('guardian.guardian_mobile_email') .
			// kaopiz 2020/08/20 Encoding end
			" FROM " .
			//T_ENTERPRISE . " enterprise,".	//	del ookawara 2012/10/31
			//T_SCHOOL . " school," .	//	del ookawara 2012/10/31
			//T_TB_STU_JYKO_JYOTAI . " tb_stu_jyko_jyotai,".	//	del ookawara 2012/10/31
			//T_STUDENT . " student" .	//	del ookawara 2012/10/31
			//	add ookawara 2012/10/31 start
			T_ENTERPRISE . " enterprise".
			" INNER JOIN ".T_SCHOOL." school ON enterprise.enterprise_id=school.enterprise_id".
			" INNER JOIN ".T_STUDENT." student ON school.school_id=student.school_id".
			" INNER JOIN ".T_TB_STU_JYKO_JYOTAI." tb_stu_jyko_jyotai ON student.student_id=tb_stu_jyko_jyotai.student_id".
			" INNER JOIN tmp_jyko_crs_cd_list tjccl ON tjccl.student_id=student.student_id".
			//	add ookawara 2012/10/31 end
			" LEFT JOIN " . T_GUARDIAN . " guardian ON guardian.guardian_id=student.guardian_id AND guardian.mk_flg='0'" .
			" LEFT JOIN " . T_STUDENT_PARA . " student_para ON student_para.student_id=student.student_id" .
			" WHERE student.mk_flg='0' AND student.move_flg='0'" .
//knry_end_flg追加　2009/11/05　hirano
			" AND student.knry_end_flg='0'".
			" AND enterprise.mk_flg='0' AND enterprise.move_flg='0' AND school.mk_flg='0'" .
			" AND tb_stu_jyko_jyotai.mk_flg='0'" .
			" AND school.school_id='" . $_SESSION['set_enterprise']['school_id'] . "'" .
			//" AND enterprise.enterprise_id=school.enterprise_id" .	//	del ookawara 2012/10/31
			//" AND school.school_id=student.school_id" .	//	del ookawara 2012/10/31
			//" AND student.student_id=tb_stu_jyko_jyotai.student_id";	//	del ookawara 2012/10/31
			"";	//	add ookawara 2012/10/31
	}

	if ($_SESSION['sub_session']['s_school'] && $_SESSION['sub_session']['s_school'] != "学校") {
//		$sql .= " AND student.school_name='".$_SESSION['sub_session']['s_school']."'";
		$sql .= " AND ". convertDecryptField('student.school_name') ."='".$_SESSION['sub_session']['s_school']."'"; // kaopiz 2020/08/20 Encoding
	}
	if ($_SESSION['sub_session']['s_grade']) {
		if (date('m') < 4) {
			$start_grade = date('Y')-1-$_SESSION['sub_session']['s_grade'];
			$end_grade = date('Y')-$_SESSION['sub_session']['s_grade'];
		} else {
			$start_grade = date('Y')-$_SESSION['sub_session']['s_grade'];
			$end_grade = date('Y')+1-$_SESSION['sub_session']['s_grade'];
		}
		$sql .= " AND student.birthday>='".$start_grade."-04-01' AND student.birthday<='".$end_grade."-03-31'";
	}
	if ($_SESSION['sub_session']['s_course']) {

		//	add ookawara 2012/10/31 start
		$sql .= " AND (" .
			"(tjccl.jyko_crs_cd IS NULL" .
			") OR (";
		
		// upd start hasegawa 2019/11/27 理社対応
		// if ($_SESSION['sub_session']['s_course'] == "1") {
		// 	$sql .= " (tjccl.jyko_crs_cd LIKE '1E%'" .
		// 		" OR (tjccl.jyko_crs_cd='30J'))";
		// } elseif ($_SESSION['sub_session']['s_course'] == "2") {
		// 	 $sql .= " (tjccl.jyko_crs_cd LIKE '1K%'" .
		// 		" OR tjccl.jyko_crs_cd='30J')";
		// } elseif ($_SESSION['sub_session']['s_course'] == "3") {
		// 	 $sql .= " (tjccl.jyko_crs_cd LIKE '1S%'" .
		// 		" OR tjccl.jyko_crs_cd='30J')";
		// }
		// //	add ookawara 2012/10/31 end
		if ($_SESSION['sub_session']['s_course'] == "1") {
		       $sql .= " ("
			       . "tjccl.jyko_crs_cd LIKE '%E%'" 
			       . " OR (tjccl.jyko_crs_cd LIKE '%30%')"
			       . " OR (tjccl.jyko_crs_cd LIKE '%50%')"
			       . ")";
		} elseif ($_SESSION['sub_session']['s_course'] == "2") {
		       $sql .= " ("
			       . "tjccl.jyko_crs_cd LIKE '%K%'" 
			       . " OR (tjccl.jyko_crs_cd LIKE '%30%')"
			       . " OR (tjccl.jyko_crs_cd LIKE '%50%')"
			       . ")";
		} elseif ($_SESSION['sub_session']['s_course'] == "3") {
		       $sql .= " ("
			       . "tjccl.jyko_crs_cd LIKE '%S%'" 
			       . " OR (tjccl.jyko_crs_cd LIKE '%30%')"
			       . " OR (tjccl.jyko_crs_cd LIKE '%50%')"
			       . ")";
		} elseif ($_SESSION['sub_session']['s_course'] == "15") {
		       $sql .= " ("
			       . "tjccl.jyko_crs_cd LIKE '%B%'" 
			       . " OR (tjccl.jyko_crs_cd LIKE '%20%')"
			       . " OR (tjccl.jyko_crs_cd LIKE '%50%')"
			       . ")";
		} elseif ($_SESSION['sub_session']['s_course'] == "16") {
		       $sql .= " ("
			       . "tjccl.jyko_crs_cd LIKE '%C%'" 
			       . " OR (tjccl.jyko_crs_cd LIKE '%20%')"
			       . " OR (tjccl.jyko_crs_cd LIKE '%50%')"
			       . ")";
		}
		// upd end hasegawa 2019/11/27 理社対応

		$sql .= "))";
	}
	if ($_SESSION['sub_session']['s_desc']) {
		$desc = " DESC";
	}else{
		$desc = "";
	}
	if ($_SESSION['sub_session']['s_order'] == 1) {
//		$sql .= " ORDER BY student.student_myj_kn".$desc.",student.student_nme_kn".$desc;
		$sql .= " ORDER BY ". convertDecryptField('student.student_myj_kn', false) ."".$desc.",". convertDecryptField('student.student_nme_kn', false) ."".$desc; // kaopiz 2020/08/20 Encoding
	} elseif ($_SESSION['sub_session']['s_order'] == 2) {
//		$sql .= " ORDER BY student.school_name".$desc;
		$sql .= " ORDER BY ". convertDecryptField('student.school_name', false) ."".$desc; // kaopiz 2020/08/20 Encoding
	} elseif ($_SESSION['sub_session']['s_order'] == 3) {
		$sql .= " ORDER BY student.birthday".$desc;
	} else {
		$sql .= " ORDER BY student.student_id".$desc;
	}
//echo $sql."<br>\n";
	header("Cache-Control: public");
	header("Pragma: public");
	header("Content-disposition: attachment;filename=$filename");
	if (stristr($HTTP_USER_AGENT, "MSIE")) {
		header("Content-Type: text/octet-stream");
	} else {
		header("Content-Type: application/octet-stream;");
	}

	//	head line (一行目)
	$csv_head .= "\"生徒ID\"\t";
	$csv_head .= "\"生徒名\"\t";
	$csv_head .= "\"ID発行日\"\t";
	$csv_head .= "\"初回ログイン日\"\t";
	$csv_head .= "\"最終ログイン日\"\t";
	$csv_head .= "\"生徒属性\"\t";
	$csv_head .= "\"生徒状態\"\t";
	$csv_head .= "\"英語\"\t";
	$csv_head .= "\"国語\"\t";
	$csv_head .= "\"数学\"\t";
	$csv_head .= "\"理科\"\t";	// add hasegawa 2019/11/27 理社対応
	$csv_head .= "\"社会\"\t";	// add hasegawa 2019/11/27 理社対応
	$csv_head .= "\"法人名\"\t";
	$csv_head .= "\"法人ID\"\t";
	$csv_head .= "\"教室名\"\t";
	$csv_head .= "\"教室ID\"\t";
	$csv_head .= "\"保護者ID\"\t";
	$csv_head .= "\"保護者名\"\t";
	$csv_head .= "\"保護者PCメール\"\t";
	$csv_head .= "\"保護者携帯メール\"\t";
	$csv_head .= "\"住所\"\t";
	$csv_head .= "\"TEL\"\t\n";

	if ($result = $connect_db->query($sql)) {
		while ($list = $connect_db->fetch_assoc($result)) {
			foreach($list as $key => $val) { $list[$key] = replace_decode($val); }
			// list($eng,$jap,$math) = check_use_course($list['jyko_crs_cd']);	// upd hasegawa 2019/11/27 理社対応
			$list['jyko_crs_cd'] = crs_cd_filter($list['jyko_crs_cd']); //add kimura 2020/04/02 理社対応_202004
			list($eng,$jap,$math,$sc,$so) = check_use_course($list['jyko_crs_cd']);
			$guardian_name = $list['guardian_myj']."".$list['guardian_nme'];

			$csv_head .= "\"".$list['student_id']."\"\t";
			$csv_head .= "\"".$list['student_name']."\"\t";
			$csv_head .= "\"".str_replace("-","/",$list['s_regist_date'])."\"\t";
			$csv_head .= "\"".str_replace("-","/",$list['first_login'])."\"\t";
			$csv_head .= "\"".str_replace("-","/",$list['last_login'])."\"\t";
			$csv_head .= "\"".$L_SITO_ZKSI[$list['sito_zksi']]."\"\t";
			$csv_head .= "\"".$L_SITO_JYTI[$list['sito_jyti']]."\"\t";
			$csv_head .= "\"".$eng."\"\t";
			$csv_head .= "\"".$jap."\"\t";
			$csv_head .= "\"".$math."\"\t";
			$csv_head .= "\"".$sc."\"\t";	// add hasegawa 2019/11/27 理社対応
			$csv_head .= "\"".$so."\"\t";	// add hasegawa 2019/11/27 理社対応
			$csv_head .= "\"".$list['enterprise_name']."\"\t";
			$csv_head .= "\"".$list['enterprise_id']."\"\t";
			$csv_head .= "\"".$list['school_name']."\"\t";
			$csv_head .= "\"".$list['school_id']."\"\t";
			$csv_head .= "\"".$list['guardian_id']."\"\t";
			$csv_head .= "\"".$guardian_name."\"\t";
			$csv_head .= "\"".$list['guardian_email']."\"\t";
			$csv_head .= "\"".$list['guardian_mobile_email']."\"\t";
			$csv_head .= "\"".$list['prf']." ".$list['city']." ".$list['add1']." ".$list['add2']."\"\t";
			$csv_head .= "\"".$list['tel']."\"\t\n";
		}
	}

	//	del 2015/01/07 yoshizawa 課題要望一覧No.400対応 下に新規で作成
	//$csv_head = mb_convert_encoding($csv_head,"sjis-win","UTF-8");
	//$csv_line = mb_convert_encoding($csv_line,"sjis-win","UTF-8");
	//----------------------------------------------------------------

	//	add 2015/01/07 yoshizawa 課題要望一覧No.400対応
		//++++++++++++++++++++++//
		//	$_POST['exp_list']	//
		//	1 => SJIS			//
		//	2 => Unicode		//
		//++++++++++++++++++++++//
	//	utf-8で出力
	if ( $_POST['exp_list'] == 2 ) {
		//	Unicode選択時には変換せずに出力

	//	SJISで出力
	} else {
		$csv_head = mb_convert_encoding($csv_head,"sjis-win","UTF-8");
		$csv_line = mb_convert_encoding($csv_line,"sjis-win","UTF-8");

	}
	//-------------------------------------------------

	echo $csv_head;
	echo $csv_line;

	exit;
}


/**
 * コースコード一時テーブル作成
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param object $cdb ＤＢコネクションオブジェクト
 */
function tmp_jyko_crs_cd($cdb) {
	//	add ookawara 2012/10/31

	// del 2015/02/27 yoshizawa ------------------------------------------------------------------
	//$sql  = "CREATE TEMPORARY TABLE tmp_jyko_crs_cd_list".
	//		" SELECT student.student_id,".
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
	//		" WHERE student.mk_flg='0';";
	//--------------------------------------------------------------------------------------------

	// SQL文生成
	$sql = getJykoCrsCdQueryTempJykoList(); // add 2015/02/27 yoshizawa 小学校高学年対応

	$cdb->exec_query($sql);
}
?>
