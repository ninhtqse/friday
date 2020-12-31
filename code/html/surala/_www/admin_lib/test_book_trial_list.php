<?
/**
 * ベンチャー・リンク　すらら
 *
 * テスト用プラクティス管理　学力診断テストマスタ管理
 *
 * 履歴
 * 2010/12/20 初期設定
 *
 * @author Azet
 */

// hirano

/**
 * HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function start() {

	if (ACTION == "check") { $ERROR = check(); }

	if (!$ERROR) {
		if (ACTION == "add") { $ERROR = add(); }
		elseif (ACTION == "change") { $ERROR = change(); }
		elseif (ACTION == "del") { $ERROR = change(); }
		elseif (ACTION == "↑") { $ERROR = up(); }
		elseif (ACTION == "↓") { $ERROR = down(); }
		elseif (ACTION == "sub_session") { $ERROR = sub_session(); }
		elseif (ACTION == "view_session") { $ERROR = view_session(); }
		elseif (ACTION == "export") { $ERROR = csv_export(); }
		elseif (ACTION == "import") { list($html,$ERROR) = csv_import(); }
	}

	$html .= select_unit_view();
	if (MODE == "add") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= addform($ERROR); }
		} elseif (ACTION == "add") {
			if (!$ERROR) { $html .= test_list($ERROR); }
			else { $html .= addform($ERROR); }
		} else {
			$html .= addform($ERROR);
		}
	} elseif (MODE == "詳細") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= viewform($ERROR); }
		} elseif (ACTION == "change") {
			if (!$ERROR) { $html .= test_list($ERROR); }
			else { $html .= viewform($ERROR); }
		} else {
			$html .= viewform($ERROR);
		}
	} elseif (MODE == "削除") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= viewform($ERROR); }
		} elseif (ACTION == "change") {
			if (!$ERROR) { $html .= test_list($ERROR); }
			else { $html .= viewform($ERROR); }
		} else {
			$html .= check_html();
		}
	} else {
		if ($_SESSION['t_practice']['course_num']) {
			$html .= test_list($ERROR);
		}
	}

	return $html;
}


/**
 * コース、学年、出版社、教科書選択
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function select_unit_view() {
	global $L_TEST_TYPE,$L_GKNN_LIST;
	global $L_WRITE_TYPE;	//	add ookawara 2012/07/29

	// upd start hirose 2020/09/10 テスト標準化開発
	// $l_write_type = $L_WRITE_TYPE + get_overseas_course_name();//add hirose 2020/08/26 テスト標準化開発
	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
	
	$test_type4 = new TestStdCfgType4($cdb);
	$l_write_type = $test_type4->getTestUseCourseAdmin();
	// upd end hirose 2020/09/10 テスト標準化開発

	//テストタイプ
	unset($L_TEST_TYPE[2]);
	unset($L_TEST_TYPE[3]);
	foreach($L_TEST_TYPE as $key => $val) {
		if ($_SESSION['t_practice']['test_type'] == $key) { $selected = "selected"; } else { $selected = ""; }
		$test_type_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
	}

	//コース
	//	add ookawara 2012/07/29 start
	$couse_html = "<option value=\"0\">選択して下さい</option>\n";
	//upd start hirose 2020/08/26 テスト標準化開発
	// foreach ($L_WRITE_TYPE AS $course_num => $course_name) {
	foreach ($l_write_type AS $course_num => $course_name) {
	//upd end hirose 2020/08/26 テスト標準化開発
		if ($course_name == "") {
			continue;
		}
		$selected = "";
		if ($_SESSION['t_practice']['course_num'] == $course_num) {
			$selected = "selected";
		}
		$couse_html .= "<option value=\"".$course_num."\" ".$selected.">".$course_name."</option>\n";
	}
	//	add ookawara 2012/07/29 end
	//学年
	foreach($L_GKNN_LIST as $key => $val) {
		if ($_SESSION['t_practice']['gknn'] == $key) { $selected = "selected"; } else { $selected = ""; }
		$gknn_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
	}
	$select_name .= "<td>コース</td>\n";

	$select_menu .= "<td>\n";
	$select_menu .= "<select name=\"course_num\" onchange=\"submit();\">".$couse_html."</select>\n";
	$select_menu .= "</td>\n";

	if (!$_POST['test_action']) {
		if (!$_SESSION['t_practice']['course_num']) {
			$msg_html .= "<span style=\"clear: both;\"><br>\n";
			$msg_html .= "コースを選択してください。</span>\n";
		} else {
			if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__add",$_SESSION['authority'])===FALSE)) {
				$msg_html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
				$msg_html .= "<input type=\"hidden\" name=\"mode\" value=\"add\">\n";
				$msg_html .= "<input type=\"hidden\" name=\"test_action\" value=\"1\">\n";
				$msg_html .= "<input type=\"submit\" value=\"新規テスト登録\" style=\"float:left;\">\n";
				$msg_html .= "</form>\n";
			}
		}
	}

	$html = "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"select_view_menu\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"set_problem_view\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"view_session\">\n";
	$html .= "<table class=\"unit_form\">\n";
	$html .= "<tr class=\"unit_form_menu\">\n";
	$html .= $select_name;
	$html .= "</tr>\n";
	$html .= "<tr class=\"unit_form_cell\">\n";
	$html .= $select_menu;
	$html .= "</tr>\n";
	$html .= "</table>\n";
	$html .= "</form>\n";
	$html .= "<br>\n";
	if ($msg_html) {
		$html .= $msg_html;
		$html .= "<br>\n";
	}

	return $html;
}


/**
 * 単元表示メニューセッション操作
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function view_session() {
	if ($_SESSION['t_practice']['default_test_num'] != $_POST['default_test_num']) {
		unset($_SESSION['sub_session']['add_type']);
	}
	if (($_SESSION['t_practice']['course_num'] != $_POST['course_num'])
	|| ($_SESSION['t_practice']['publishing_id'] != $_POST['publishing_id'])
	|| ($_SESSION['t_practice']['gknn'] != $_POST['gknn'])) {
		unset($_SESSION['t_practice']['book_id']);
		unset($_SESSION['t_practice']['default_test_num']);
	} elseif(strlen($_POST['book_id'])) {
		$_SESSION['t_practice']['book_id'] = $_POST['book_id'];
		if($_POST['book_id']) { $ERROR = auto_test_add(); }
	} elseif(strlen($_POST['default_test_num'])) {
		$_SESSION['t_practice']['default_test_num'] = $_POST['default_test_num'];
	}
	if ($_SESSION['t_practice']['book_id'] != $_POST['book_id']) {
		unset($_SESSION['t_practice']['book_unit_id']);
	} elseif(strlen($_POST['book_unit_id'])) {
		$_SESSION['t_practice']['book_unit_id'] = $_POST['book_unit_id'];
	}

	if (strlen($_POST['course_num'])) { $_SESSION['t_practice']['course_num'] = $_POST['course_num']; }
	if (strlen($_POST['core_code'])) { $_SESSION['t_practice']['core_code'] = $_POST['core_code']; }
	if (strlen($_POST['publishing_id'])) { $_SESSION['t_practice']['publishing_id'] = $_POST['publishing_id']; }
	if (strlen($_POST['gknn'])) { $_SESSION['t_practice']['gknn'] = $_POST['gknn']; }
	if ($_SESSION['t_practice']['test_type'] != $_POST['test_type']) {
		unset($_SESSION['t_practice']);
	}
	if (strlen($_POST['test_type'])) { $_SESSION['t_practice']['test_type'] = $_POST['test_type']; }

	return $ERROR;
}


/**
 * グループ　絞り込みメニュー
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function select_menu() {
	global $L_BOOK_ORDER,$L_DESC,$L_PAGE_VIEW;

	//ソート対象
	unset($L_BOOK_ORDER[1]);
	foreach ($L_BOOK_ORDER as $key => $val){
		if ($_SESSION['sub_session']['s_order'] == $key) { $sel = " selected"; } else { $sel = ""; }
		$s_order_html .= "<option value=\"".$key."\"".$sel.">".$val."</option>\n";
	}
	//昇順、降順
	foreach ($L_DESC as $key => $val){
		if ($_SESSION['sub_session']['s_desc'] == $key) { $sel = " selected"; } else { $sel = ""; }
		$s_desc_html .= "<option value=\"".$key."\"".$sel.">".$val."</option>\n";
	}
	//ページ数
	if (!isset($_SESSION['sub_session']['s_page_view'])) { $_SESSION['sub_session']['s_page_view'] = 2; }
	foreach ($L_PAGE_VIEW as $key => $val){
		if ($_SESSION['sub_session']['s_page_view'] == $key) { $sel = " selected"; } else { $sel = ""; }
		$s_page_view_html .= "<option value=\"".$key."\"".$sel.">".$val."</option>\n";
	}

	$sub_session_html = "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$sub_session_html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
	$sub_session_html .= "<td>\n";
	$sub_session_html .= "ソート \n";
	$sub_session_html .= "<select name=\"s_desc\">\n".$s_desc_html."</select>\n";
	$sub_session_html .= "表示数 <select name=\"s_page_view\">\n".$s_page_view_html."</select>\n";
	$sub_session_html .= "<input type=\"submit\" value=\"Set\">\n";
	$sub_session_html .= "</td>\n";
	$sub_session_html .= "</form>\n";

	$html .= "<br><div id=\"mode_menu\">\n";
	$html .= "<table cellpadding=0 cellspacing=0>\n";
	$html .= "<tr>\n";
	$html .= $sub_session_html;
	$html .= "</tr>\n";
	$html .= "</table>\n";
	$html .= "</div>\n";

	return $html;
}


/**
 * テスト　絞り込みメニューセッション操作
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function sub_session() {
	if (strlen($_POST['s_order'])) { $_SESSION['sub_session']['s_order'] = $_POST['s_order']; }
	if (strlen($_POST['s_desc'])) { $_SESSION['sub_session']['s_desc'] = $_POST['s_desc']; }
	if (strlen($_POST['s_page_view'])) { $_SESSION['sub_session']['s_page_view'] = $_POST['s_page_view']; }
	if (strlen($_POST['s_desc'])&&strlen($_POST['s_page_view'])) { unset($_SESSION['sub_session']['s_page']); }
	if (strlen($_POST['s_page'])) { $_SESSION['sub_session']['s_page'] = $_POST['s_page']; }

	return;
}


/**
 * テスト一覧
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function test_list($ERROR) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_PAGE_VIEW,$L_GKNN_LIST,$L_DISPLAY;
	//	add 2015/01/07 yoshizawa 課題要望一覧No.400対応
	global $L_EXP_CHA_CODE;
	//-------------------------------------------------

	//list($me_num,$onetime,$manager_level,$belong_num,$authority) = explode("<>",$_SESSION['myid']);
	if ($authority) { $L_AUTHORITY = explode("::",$authority); }

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= "<br>\n";
	$html .= "インポートする場合は、学力診断テストcsvファイル（S-JIS）を指定しCSVインポートボタンを押してください。<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" enctype=\"multipart/form-data\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"import\">\n";
	$html .= "<input type=\"file\" size=\"40\" name=\"import_file\"><br>\n";
	$html .= "<input type=\"submit\" value=\"CSVインポート\" style=\"float:left;\">\n";
	$html .= "</form>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"export\">\n";
	//	add 2015/01/07 yoshizawa 課題要望一覧No.400対応
	//	プルダウンを作成
	$expList = "";
	if ( is_array($L_EXP_CHA_CODE) ) {
		$expList .= "<br /><br />\n";
		$expList .= "海外版の場合は、出力形式について[Unicode]選択して、CSVエクスポートボタンをクリックしてください。<br />\n";
		$expList .= "<b>出力形式：</b>";
		$expList .= "<select name=\"exp_list\">";
		foreach( $L_EXP_CHA_CODE as $key => $val ){
			$expList .= "<option value=\"".$key."\">".$val."</option>";
		}
		$expList .= "</select>";
		$html .= $expList;
	}
	//-------------------------------------------------
	$html .= "<input type=\"submit\" value=\"CSVエクスポート\">\n";
	$html .= "</form>\n";

	if ($_SESSION['t_practice']['course_num']) {
		$where .= " AND ms_test_default.course_num='".$_SESSION['t_practice']['course_num']."'";
	}
	$sql  = "SELECT ".
			"ms_test_default.default_test_num,".
			"ms_test_default.course_num,".
			"course.course_name,".
//			"ms_test_group.test_group_name,".
			"ms_test_default.test_name,".
			"ms_test_default.display,".
			"ms_test_default.limit_time,".// add hirose 2020/08/31 テスト標準化開発
			"ms_test_default.disp_sort".
//			"ms_test_group_list.disp_sort".
			" FROM " . T_MS_TEST_DEFAULT . " ms_test_default" .
//			" LEFT JOIN ".T_BOOK_GROUP_LIST." ms_test_group_list ON ".
//				" ms_test_group_list.mk_flg='0'".
//				" AND ms_test_group_list.default_test_num=ms_test_default.default_test_num".
//			" LEFT JOIN ".T_MS_BOOK_GROUP." ms_test_group ON ".
//				" ms_test_group.mk_flg='0'".
//				" AND ms_test_group.test_group_id=ms_test_group_list.test_group_id".
			" LEFT JOIN ".T_COURSE." course ON course.course_num=ms_test_default.course_num".
			" WHERE ms_test_default.mk_flg='0'".
			" AND ms_test_default.test_type='4'".$where;
	if ($result = $cdb->query($sql)) {
		$ms_test_count = $cdb->num_rows($result);
	}
	if (!$ms_test_count) {
		$html .= "<br>\n";
		$html .= "今現在登録されている学力診断テストは有りません。<br>\n";
		return $html;
	}
	$html .= select_menu();

	if ($_SESSION['sub_session']['s_desc']) {
		$sort_key = " DESC";
	} else {
		$sort_key = " ASC";
	}
//	$orderby = "ORDER BY ms_test_group.disp_sort ".$sort_key.",ms_test_default.disp_sort ".$sort_key;
	$orderby = "ORDER BY ms_test_default.disp_sort ".$sort_key;
	if ($L_PAGE_VIEW[$_SESSION['sub_session']['s_page_view']]) { $page_view = $L_PAGE_VIEW[$_SESSION['sub_session']['s_page_view']]; }
	else { $page_view = $L_PAGE_VIEW[0]; }
	$max_page = ceil($ms_test_count/$page_view);
	if ($_SESSION['sub_session']['s_page']) { $page = $_SESSION['sub_session']['s_page']; }
	else { $page = 1; }
	$start = ($page - 1) * $page_view;
	$next = $page + 1;
	$back = $page - 1;

	$sql .= $orderby." LIMIT ".$start.",".$page_view.";";

	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);

		$html .= "<br>\n";
		$html .= "修正する場合は、修正する学力診断テストマスタの詳細ボタンを押してください。<br>\n";
		$html .= "<div style=\"float:left;\">登録マスタ総数(".$ms_test_count."):PAGE[".$page."/".$max_page."]</div>\n";
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
		$html .= "<table class=\"course_form\">\n";
		$html .= "<tr class=\"course_form_menu\">\n";
		if (!ereg("practice__view",$authority)
			&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__sort",$_SESSION['authority'])===FALSE))
		) {
			$html .= "<th>↑</th>\n";
			$html .= "<th>↓</th>\n";
		}
		$html .= "<th>テストID</th>\n";
		$html .= "<th>コース</th>\n";
//		$html .= "<th>テスト名(グループ名)</th>\n";
		$html .= "<th>テスト名(補助名)</th>\n";
		$html .= "<th>制限時間</th>\n";// add hirose 2020/08/31 テスト標準化開発
		$html .= "<th>表示・非表示</th>\n";
		if (!ereg("practice__view",$authority)
			&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__view",$_SESSION['authority'])===FALSE))
		) {
			$html .= "<th>詳細</th>\n";
		}
		if (!ereg("practice__del",$authority)
			&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__del",$_SESSION['authority'])===FALSE))
		) {
			$html .= "<th>削除</th>\n";
		}
		$html .= "</tr>\n";
		$i = 1;
		while ($list=$cdb->fetch_assoc($result)) {
			$default_test_id_html = "";
			unset($L_DEFAULT_TEST_NUM);
			$up_submit = $down_submit = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			if (!$_SESSION['sub_session']['s_order'] && !$_SESSION['sub_session']['s_desc']) {
				if ($i != 1 || $page != 1) { $up_submit = "<input type=\"submit\" name=\"action\" value=\"↑\">\n"; }
				if ($i != $max || $page != $max_page) { $down_submit = "<input type=\"submit\" name=\"action\" value=\"↓\">\n"; }
			}

			$html .= "<tr class=\"course_form_cell\">\n";
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"default_test_num\" value=\"".$list['default_test_num']."\">\n";
			if (!ereg("practice__view",$authority)
				&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__sort",$_SESSION['authority'])===FALSE))
			) {
				$html .= "<td>".$up_submit."</td>\n";
				$html .= "<td>".$down_submit."</td>\n";
			}
			// add start hirose 2020/08/31 テスト標準化開発
			$limit_time = 0;
			if($list['limit_time']){
				$limit_time = $list['limit_time'];
			}
			// add end hirose 2020/08/31 テスト標準化開発
			$html .= "<td>".$list['default_test_num']."</td>\n";
//			$html .= "<td>".$list['test_group_name']."</td>\n";
			$html .= "<td>".$list['course_name']."</td>\n";
			$html .= "<td>".$list['test_name']."</td>\n";
			$html .= "<td>".$limit_time."分</td>\n";// add hirose 2020/08/31 テスト標準化開発
			$html .= "<td>".$L_DISPLAY[$list['display']]."</td>\n";
			if (!ereg("practice__view",$authority)
				&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__view",$_SESSION['authority'])===FALSE))
			) {
				$html .= "<td><input type=\"submit\" name=\"mode\" value=\"詳細\"></td>\n";
			}
			if (!ereg("practice__del",$authority)
				&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__del",$_SESSION['authority'])===FALSE))
			) {
				$html .= "<td><input type=\"submit\" name=\"mode\" value=\"削除\"></td>\n";
			}
			$html .= "</form>\n";
			$html .= "</tr>\n";
			++$i;
		}
		$html .= "</table>\n";
	}
	return $html;
}


/**
 * 学力診断　新規登録フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function addform($ERROR) {
	global $L_GKNN_LIST,$L_MONTH,$L_DAY,$L_HOUR,$L_MINUTE,$L_DISPLAY;

	//年リスト
	$L_YEAR = year_list();
	//コースリスト
	$L_COURSE_LIST = course_list();
	// グループ名
	$L_BOOK_GROUP = test_group_list($_SESSION['t_practice']['course_num']);
	//テストマスタリスト
	$L_BOOK_LIST = book_list($_SESSION['t_practice']['course_num'],$_POST['gknn']);

	$html .= "<br>\n";
	$html .= "新規登録フォーム<br>\n";

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";
	$html .= "<input type=\"hidden\" name=\"test_action\" value=\"1\">\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(TEST_DEFAULT_FORM_4);

	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("from_year");
	$newform->set_form_array($L_YEAR);
	$newform->set_form_check($_POST['from_year']);
	$kkn_from .= $newform->make()." / ";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("from_month");
	$newform->set_form_array($L_MONTH);
	$newform->set_form_check($_POST['from_month']);
	$kkn_from .= $newform->make()." / ";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("from_day");
	$newform->set_form_array($L_DAY);
	$newform->set_form_check($_POST['from_day']);
	$kkn_from .= $newform->make()."　";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("from_hour");
	$newform->set_form_array($L_HOUR);
	$newform->set_form_check($_POST['from_hour']);
	$kkn_from .= $newform->make()." ： ";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("from_minute");
	$newform->set_form_array($L_MINUTE);
	$newform->set_form_check($_POST['from_minute']);
	$kkn_from .= $newform->make()." ： ";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("from_second");
	$newform->set_form_array($L_MINUTE);
	$newform->set_form_check($_POST['from_second']);
	$kkn_from .= $newform->make();

	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("to_year");
	$newform->set_form_array($L_YEAR);
	$newform->set_form_check($_POST['to_year']);
	$kkn_to .= $newform->make()." / ";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("to_month");
	$newform->set_form_array($L_MONTH);
	$newform->set_form_check($_POST['to_month']);
	$kkn_to .= $newform->make()." / ";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("to_day");
	$newform->set_form_array($L_DAY);
	$newform->set_form_check($_POST['to_day']);
	$kkn_to .= $newform->make()."　";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("to_hour");
	$newform->set_form_array($L_HOUR);
	$newform->set_form_check($_POST['to_hour']);
	$kkn_to .= $newform->make()." ： ";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("to_minute");
	$newform->set_form_array($L_MINUTE);
	$newform->set_form_check($_POST['to_minute']);
	$kkn_to .= $newform->make()." ： ";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("to_second");
	$newform->set_form_array($L_MINUTE);
	$newform->set_form_check($_POST['to_second']);
	$kkn_to .= $newform->make();

	$display_html = "";
	foreach($L_DISPLAY as $key => $val) {
		if ($val == "") { continue; }
		$newform = new form_parts();
		$newform->set_form_type("radio");
		$newform->set_form_name("display");
		$newform->set_form_id("display_".$key);
		$newform->set_form_check($_POST['display']);
		$newform->set_form_value("".$key."");
		$display_btn = $newform->make();
		if ($display_html) { $display_html .= " / "; }
		$display_html .= $display_btn."<label for=\"display_".$key."\">".$val."</label>";
	}

//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	$INPUTS[COURSENUM] 		= array('result'=>'plane','value'=>$L_COURSE_LIST[$_SESSION['t_practice']['course_num']]);
	$INPUTS[GKNN]	 		= array('type'=>'select','name'=>'test_gknn','array'=>$L_GKNN_LIST,'check'=>$_POST['test_gknn']);
	$INPUTS[BOOKGROUPNAME]	= array('type'=>'select','name'=>'test_group_id','array'=>$L_BOOK_GROUP,'check'=>$_POST['test_group_id']);
	$INPUTS[TESTNAME] 		= array('type'=>'text','name'=>'test_name','size'=>'50','value'=>$_POST['test_name']);
	$INPUTS[LIMITTIME] 		= array('type'=>'text','name'=>'limit_time','size'=>'5','value'=>$_POST['limit_time']);
	$INPUTS[BOOKID] 		= array('type'=>'select','name'=>'book_id','array'=>$L_BOOK_LIST,'check'=>$_POST['book_id']);
	$INPUTS[KKNFROM] 		= array('result'=>'plane','value'=>$kkn_from);
	$INPUTS[KKNTO] 			= array('result'=>'plane','value'=>$kkn_to);
	$INPUTS[KKNATT] 		= array('result'=>'plane','value'=>$kkn_att);
	$INPUTS[TESTBKO] 		= array('type'=>'text','name'=>'test_bko','size'=>'50','value'=>$_POST['test_bko']);
	$INPUTS[DISPLAY] 		= array('result'=>'plane','value'=>$display_html);
	$INPUTS['LIMITTIME'] = array('type'=>'text','name'=>'limit_time','size'=>'50','value'=>$_POST['limit_time']);// add hirose 2020/08/31 テスト標準化開発

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"追加確認\">\n";
	$html .= "<input type=\"reset\" value=\"クリア\">\n";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"test_book_trial_list\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";
	return $html;
}


/**
 * 学力診断　修正フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function viewform($ERROR) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_GKNN_LIST,$L_MONTH,$L_DAY,$L_HOUR,$L_MINUTE,$L_DISPLAY;

	if (ACTION) {
		foreach ($_POST as $key => $val) { $$key = $val; }
		$sql  = "SELECT mtd.*,ms_test_group_list.test_group_id" . " FROM ".
			T_MS_TEST_DEFAULT." mtd".
			" LEFT JOIN ".T_BOOK_GROUP_LIST." ms_test_group_list ON ".
				" ms_test_group_list.mk_flg='0'".
				" AND ms_test_group_list.default_test_num=mtd.default_test_num".
			" LEFT JOIN ".T_MS_BOOK_GROUP." ms_test_group ON ".
				" ms_test_group.mk_flg='0'".
				" AND ms_test_group.test_group_id=ms_test_group_list.test_group_id".
				// " AND ms_test_group.srvc_cd = 'GTEST' ". // add yoshizawa 2015/09/29 02_作業要件/34_数学検定     // del 2020/09/14 thanh テスト標準化開発
				" AND ms_test_group.class_id = '' ".	// add 2020/09/14 thanh テスト標準化開発
			" WHERE mtd.mk_flg='0' AND mtd.default_test_num='".$_POST['default_test_num']."' LIMIT 1;";
		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);
		$course_num = $list['course_num'];
	} else {
		$sql  = "SELECT mtd.*,ms_test_group_list.test_group_id" . " FROM ".
			T_MS_TEST_DEFAULT." mtd".
			" LEFT JOIN ".T_BOOK_GROUP_LIST." ms_test_group_list ON ".
				" ms_test_group_list.mk_flg='0'".
				" AND ms_test_group_list.default_test_num=mtd.default_test_num".
			" LEFT JOIN ".T_MS_BOOK_GROUP." ms_test_group ON ".
				" ms_test_group.mk_flg='0'".
				" AND ms_test_group.test_group_id=ms_test_group_list.test_group_id".
				// " AND ms_test_group.srvc_cd = 'GTEST' ". // add yoshizawa 2015/09/29 02_作業要件/34_数学検定    // del 2020/09/14 thanh テスト標準化開発
				" AND ms_test_group.class_id = '' ".	// add 2020/09/14 thanh テスト標準化開発
			" WHERE mtd.mk_flg='0' AND mtd.default_test_num='".$_POST['default_test_num']."' LIMIT 1;";
		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);
		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			$html .= "<br>\n";
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"book_unit_list\">\n";
			$html .= "<input type=\"submit\" value=\"戻る\">\n";
			$html .= "</form>\n";
			return $html;
		}
		foreach ($list as $key => $val) {
			$$key = replace_decode($val);
		}
		list($from_days,$from_time) = explode(" ",$kkn_from);
		list($from_year,$from_month,$from_day) = explode("-",$from_days);
		list($from_hour,$from_minute,$from_second) = explode(":",$from_time);
		list($to_days,$to_time) = explode(" ",$kkn_to);
		list($to_year,$to_month,$to_day) = explode("-",$to_days);
		list($to_hour,$to_minute,$to_second) = explode(":",$to_time);
	}

	$html = "<br>\n";
	$html .= "詳細画面<br>\n";

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";
	$html .= "<input type=\"hidden\" name=\"test_action\" value=\"1\">\n";
	$html .= "<input type=\"hidden\" name=\"default_test_num\" value=\"".$_POST['default_test_num']."\">\n";

	$set_file_name = TEST_DEFAULT_FORM_4;

	//
	$L_BOOK_GROUP = test_group_list($course_num);

	//年リスト
	$L_YEAR = year_list();

	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("from_year");
	$newform->set_form_array($L_YEAR);
	$newform->set_form_check($from_year);
	$kkn_from = $newform->make()." / ";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("from_month");
	$newform->set_form_array($L_MONTH);
	$newform->set_form_check($from_month);
	$kkn_from .= $newform->make()." / ";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("from_day");
	$newform->set_form_array($L_DAY);
	$newform->set_form_check($from_day);
	$kkn_from .= $newform->make()."　";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("from_hour");
	$newform->set_form_array($L_HOUR);
	$newform->set_form_check($from_hour);
	$kkn_from .= $newform->make()." ： ";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("from_minute");
	$newform->set_form_array($L_MINUTE);
	$newform->set_form_check($from_minute);
	$kkn_from .= $newform->make()." ： ";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("from_second");
	$newform->set_form_array($L_MINUTE);
	$newform->set_form_check($from_second);
	$kkn_from .= $newform->make();

	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("to_year");
	$newform->set_form_array($L_YEAR);
	$newform->set_form_check($to_year);
	$kkn_to = $newform->make()." / ";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("to_month");
	$newform->set_form_array($L_MONTH);
	$newform->set_form_check($to_month);
	$kkn_to .= $newform->make()." / ";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("to_day");
	$newform->set_form_array($L_DAY);
	$newform->set_form_check($to_day);
	$kkn_to .= $newform->make()."　";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("to_hour");
	$newform->set_form_array($L_HOUR);
	$newform->set_form_check($to_hour);
	$kkn_to .= $newform->make()." ： ";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("to_minute");
	$newform->set_form_array($L_MINUTE);
	$newform->set_form_check($to_minute);
	$kkn_to .= $newform->make()." ： ";
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("to_second");
	$newform->set_form_array($L_MINUTE);
	$newform->set_form_check($to_second);
	$kkn_to .= $newform->make();

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file($set_file_name);

	//コースリスト
	$L_COURSE_LIST = course_list();
	//テストマスタリスト
	$L_BOOK_LIST = book_list($course_num,$gknn);

	$display_html = "";
	foreach($L_DISPLAY as $key => $val) {
		if ($val == "") { continue; }
		$newform = new form_parts();
		$newform->set_form_type("radio");
		$newform->set_form_name("display");
		$newform->set_form_id("display_".$key);
		$newform->set_form_check($display);
		$newform->set_form_value("".$key."");
		$display_btn = $newform->make();
		if ($display_html) { $display_html .= " / "; }
		$display_html .= $display_btn."<label for=\"display_".$key."\">".$val."</label>";
	}

//	$INPUTS[COURSENUM] 		= array('result'=>'plane','value'=>$L_COURSE_LIST[$_SESSION['t_practice']['course_num']]);
	$INPUTS[COURSENUM] 		= array('result'=>'plane','value'=>$L_COURSE_LIST[$course_num]);
//	$INPUTS[GKNN] 			= array('type'=>'select','name'=>'test_gknn','array'=>$L_GKNN_LIST,'check'=>$test_gknn);
//	$INPUTS[BOOKGROUPNAME]	= array('type'=>'select','name'=>'test_group_id','array'=>$L_BOOK_GROUP,'check'=>$test_group_id);
	$INPUTS[TESTNAME] 		= array('type'=>'text','name'=>'test_name','size'=>'50','value'=>$test_name);
//	$INPUTS[LIMITTIME] 		= array('type'=>'text','name'=>'limit_time','size'=>'5','value'=>$limit_time);
//	$INPUTS[BOOKID] 		= array('type'=>'select','name'=>'book_id','array'=>$L_BOOK_LIST,'check'=>$book_id);
//	$INPUTS[KKNFROM] 		= array('result'=>'plane','value'=>$kkn_from);
//	$INPUTS[KKNTO] 			= array('result'=>'plane','value'=>$kkn_to);
//	$INPUTS[KKNATT] 		= array('result'=>'plane','value'=>$kkn_att);
	$INPUTS[TESTBKO] 		= array('type'=>'textarea','name'=>'test_bko','cols'=>'50','rows'=>'5','value'=>$test_bko);
	$INPUTS[DISPLAY] 		= array('result'=>'plane','value'=>$display_html);
	$INPUTS['LIMITTIME'] = array('type'=>'text','name'=>'limit_time','size'=>'50','value'=>$limit_time);// add hirose 2020/08/31 テスト標準化開発

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"変更確認\">";
	$html .= "<input type=\"reset\" value=\"クリア\">";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"book_unit_list\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}


/**
 * 学力診断　新規登録・修正　必須項目チェック
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function check() {
	global $L_MONTH,$L_DAY,$L_HOUR,$L_MINUTE;
	//年リスト
	$L_YEAR = year_list();
/*
	if (!$_POST['test_gknn']) { $ERROR[] = "学年が未選択です。"; }
	if (!$_POST['test_group_id']) { $ERROR[] = "テスト名（グループ名）が未選択です。"; }
	if (!$_POST['limit_time']) { $ERROR[] = "制限時間が未入力です。"; }
	else {
		if (preg_match("/[^0-9]/",$_POST['limit_time'])) {
			$ERROR[] = "制限時間は半角数字で入力してください。";
		} elseif ($_POST['limit_time'] < 0) {
			$ERROR[] = "制限時間は0以下の指定はできません。";
		}
	}

	if (mb_strlen($_POST['usr_bko'], 'UTF-8') > 255) { $ERROR[] = "備考が不正です。255文字以内で記述して下さい。"; }
*/
//	if (!$_POST['test_name']) { $ERROR[] = "テスト名が未入力です。"; }
//	if (!$_POST['book_id']) { $ERROR[] = "テストマスタが未選択です。"; }

/*
	if (!$_POST['from_year'] || !$_POST['from_month'] || !$_POST['from_day']) {
		$ERROR[] = "テスト期間 開始が未選択です。";
	} else {
		if (checkdate($L_MONTH[$_POST['from_month']],$L_DAY[$_POST['from_day']],$L_YEAR[$_POST['from_year']])) {
//			$from_day = date("Ymd", mktime(0,0,0,$L_MONTH[$_POST['from_month']],$L_DAY[$_POST['from_day']],$L_YEAR[$_POST['from_year']]));
//			if (date('Ymd') > $from_day) { $ERROR[] = "テスト期間 開始が不正です。入力された日付が過去になっています。"; }
		} else { $ERROR[] = "テスト期間 開始が不正です。選択された日付は存在しません。"; }
	}
	if (!$_POST['to_year'] || !$_POST['to_month'] || !$_POST['to_day']) {
		$ERROR[] = "テスト期間 終了が未選択です。";
	} else {
		if (checkdate($L_MONTH[$_POST['to_month']],$L_DAY[$_POST['to_day']],$L_YEAR[$_POST['to_year']])) {
			$to_day = date("Ymd", mktime(0,0,0,$L_MONTH[$_POST['to_month']],$L_DAY[$_POST['to_day']],$L_YEAR[$_POST['to_year']]));
			if (date('Ymd') > $to_day) { $ERROR[] = "テスト期間 終了が不正です。入力された日付が過去になっています。"; }
			elseif ($from_day > $to_day) { $ERROR[] = "テスト期間 終了が不正です。入力された日付がテスト期間 開始より過去になっています。"; }
		} else { $ERROR[] = "テスト期間 終了が不正です。選択された日付は存在しません。"; }
	}
*/
	// add start oda 2014/08/08 課題要望一覧No302 学力診断テストマスタ名が未設定の場合はエラーとする
	if (!$_POST['test_name']) {
		$ERROR[] = $line_num."テスト名(補助名)が未入力です。";
	}
	// add end oda 2014/08/08

	if (!$_POST['display']) {
		$ERROR[] = "表示・表示を選択してください。";
	}

	// add start hirose 2020/08/31 テスト標準化開発
	if ($_POST['limit_time'] === "") {
		$ERROR[] = "制限時間が未入力です。";
	}elseif(!preg_match("/^[0-9]*$/",$_POST['limit_time'])){
		$ERROR[] = "制限時間は半角数字のみで入力してください。";
	}elseif($_POST['limit_time']>90 || $_POST['limit_time']<1){
		$ERROR[] = "制限時間は1分から90分の間で指定してください。";
	}
	// add end hirose 2020/08/31 テスト標準化開発

	return $ERROR;
}


/**
 * 学力診断　新規登録・修正・削除　確認フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function check_html() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_GKNN_LIST,$L_MONTH,$L_DAY,$L_HOUR,$L_MINUTE,$L_DISPLAY;

	if ($_POST) {
		foreach ($_POST as $key => $val) {
			if ($key == "test_action") { continue; }
			if ($key == "action") {
				if (MODE == "add") { $val = "add"; }
				elseif (MODE == "詳細") { $val = "change"; }
				elseif (MODE == "削除") { $val = "change"; }
			}
			$HIDDEN .= "<input type=\"hidden\" name=\"".$key."\" value=\"".$val."\">\n";
		}
	}

	if (ACTION) {
		foreach ($_POST as $key => $val) { $$key = $val; }
	} else {
//削除用処理
		$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"change\">\n";
		$sql  = "SELECT *" . " FROM " . T_MS_TEST_DEFAULT .
			" WHERE mk_flg='0' AND default_test_num='".$_POST['default_test_num']."' LIMIT 1;";
		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);
		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}
		foreach ($list as $key => $val) {
			$$key = replace_decode($val);
		}
		list($from_days,$from_time) = explode(" ",$kkn_from);
		list($from_year,$from_month,$from_day) = explode("-",$from_days);
		list($from_hour,$from_minute,$from_second) = explode(":",$from_time);
		$from_month = sprintf("%01d",$from_month);
		$from_day = sprintf("%01d",$from_day);
		$from_hour = sprintf("%01d",$from_hour);
		$from_minute = sprintf("%01d",$from_minute);
		$from_second = sprintf("%01d",$from_second);
		list($to_days,$to_time) = explode(" ",$kkn_to);
		list($to_year,$to_month,$to_day) = explode("-",$to_days);
		list($to_hour,$to_minute,$to_second) = explode(":",$to_time);
		$to_month = sprintf("%01d",$to_month);
		$to_day = sprintf("%01d",$to_day);
		$to_hour = sprintf("%01d",$to_hour);
		$to_minute = sprintf("%01d",$to_minute);
		$to_second = sprintf("%01d",$to_second);
	}
	//コースリスト
	$L_COURSE_LIST = course_list();
	//テストマスタリスト
	$L_BOOK_LIST = book_list($_SESSION['t_practice']['course_num'],$_SESSION['t_practice']['gknn']);
	$sql  = "SELECT test_group_name FROM ".T_MS_BOOK_GROUP.
		" WHERE mk_flg='0'".
		// " AND srvc_cd = 'GTEST' ". // add yoshizawa 2015/09/29 02_作業要件/34_数学検定      // del 2020/09/14 thanh テスト標準化開発
		" AND class_id = '' ".	// add 2020/09/14 thanh テスト標準化開発
		" AND test_group_id='".$test_group_id."';";
	$result = $cdb->query($sql);
	$list = $cdb->fetch_assoc($result);
	$test_group_name = $list['test_group_name'];

	if (MODE != "削除") { $button = "登録"; } else { $button = "削除"; }
	$html = "<br>\n";
	$html .= "確認画面：以下の内容で".$button."してもよろしければ".$button."ボタンをクリックしてください。<br>\n";

	$set_file_name = TEST_DEFAULT_FORM_4;

	//年リスト
	$L_YEAR = year_list();

	$kkn_from = $L_YEAR[$from_year]." / ".$L_MONTH[$from_month]." / ".$L_DAY[$from_day];
	if ($from_hour || $from_minute || $from_second) {
		$kkn_from .= "　".$L_HOUR[$from_hour]." ： ".$L_MINUTE[$from_minute]." ： ".$L_MINUTE[$from_second];
	}
	$kkn_to = $L_YEAR[$to_year]." / ".$L_MONTH[$to_month]." / ".$L_DAY[$to_day];
	if ($to_hour || $to_minute || $to_second) {
		$kkn_to .= "　".$L_HOUR[$to_hour]." ： ".$L_MINUTE[$to_minute]." ： ".$L_MINUTE[$to_second];
	}
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file($set_file_name);

//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	$INPUTS[COURSENUM] 		= array('result'=>'plane','value'=>$L_COURSE_LIST[$_SESSION['t_practice']['course_num']]);
//	$INPUTS[GKNN] 			= array('result'=>'plane','value'=>$L_GKNN_LIST[$_POST['test_gknn']]);
//	$INPUTS[BOOKGROUPNAME]	= array('result'=>'plane','value'=>$test_group_name);
	$INPUTS[TESTNAME] 		= array('result'=>'plane','value'=>$test_name);
//	$INPUTS[LIMITTIME] 		= array('result'=>'plane','value'=>$limit_time);
//	$INPUTS[BOOKID] 		= array('result'=>'plane','value'=>$L_BOOK_LIST[$book_id]);
//	$INPUTS[KKNFROM] 		= array('result'=>'plane','value'=>$kkn_from);
//	$INPUTS[KKNTO] 			= array('result'=>'plane','value'=>$kkn_to);
	$INPUTS[TESTBKO] 		= array('result'=>'plane','value'=>nl2br($test_bko));
	$INPUTS[DISPLAY] 		= array('result'=>'plane','value'=>$L_DISPLAY[$display]);
	$INPUTS['LIMITTIME'] = array('result'=>'plane','value'=>$limit_time);// add hirose 2020/08/31 テスト標準化開発

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" style=\"float:left\">\n";
	$html .= $HIDDEN;
	$html .= "<input type=\"submit\" value=\"".$button."\">\n";
	$html .= "</form>";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	if (MODE != "del") {
		$html .= "<input type=\"hidden\" name=\"test_action\" value=\"1\">\n";
	}
	if (ACTION) {
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
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"test_book_trial_list\">\n";
	}
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}


/**
 * DB新規登録 学力診断　新規登録処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function add() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_MONTH,$L_DAY,$L_HOUR,$L_MINUTE;
	//年リスト
	$L_YEAR = year_list();

	$sql = "SELECT MAX(default_test_num) AS max_num FROM " . T_MS_TEST_DEFAULT . ";";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
	}
	if ($list['max_num']) { $default_test_num = $list['max_num'] + 1; } else { $default_test_num = 1; }

	$kkn_from = $L_YEAR[$_POST['from_year']]."-".$L_MONTH[$_POST['from_month']]."-".$L_DAY[$_POST['from_day']];
	if ($_POST['from_hour'] || $_POST['from_minute'] || $_POST['from_second']) {
		$kkn_from .= " ".$L_HOUR[$_POST['from_hour']].":".$L_MINUTE[$_POST['from_minute']].":".$L_MINUTE[$_POST['from_second']];
	}
	$kkn_to = $L_YEAR[$_POST['to_year']]."-".$L_MONTH[$_POST['to_month']]."-".$L_DAY[$_POST['to_day']];
	if ($_POST['to_hour'] || $_POST['to_minute'] || $_POST['to_second']) {
		$kkn_to .= " ".$L_HOUR[$_POST['to_hour']].":".$L_MINUTE[$_POST['to_minute']].":".$L_MINUTE[$_POST['to_second']];
	}

	$INSERT_DATA[default_test_num] 	= $default_test_num;
	$INSERT_DATA[test_type] 		= 4;
	$INSERT_DATA[course_num] 		= $_SESSION['t_practice']['course_num'];
//	$INSERT_DATA[test_gknn] 		= $_POST['gknn'];
	$INSERT_DATA[test_name]		 	= $_POST['test_name'];
	$INSERT_DATA[test_bko] 			= $_POST['test_bko'];
	$INSERT_DATA[display] 			= $_POST['display'];
	$INSERT_DATA[limit_time] 		= $_POST['limit_time'];// upd hirose 2020/08/31 テスト標準化開発 非表示状態を表示に変更
//	$INSERT_DATA[book_id]		 	= $_POST['book_id'];
//	$INSERT_DATA[kkn_from]		 	= $kkn_from;
//	$INSERT_DATA[kkn_to]		 	= $kkn_to;
	$INSERT_DATA[apply_level] 		= 1;
	$INSERT_DATA[disp_sort] 		= $default_test_num;
//	$INSERT_DATA[ins_syr_id] 		= ;
	$INSERT_DATA[ins_tts_id] 		= $_SESSION['myid']['id'];
	$INSERT_DATA[ins_date] 			= "now()";
	$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];
	$INSERT_DATA[upd_date] 			= "now()";

	$ERROR = $cdb->insert(T_MS_TEST_DEFAULT,$INSERT_DATA);

	$_SESSION['t_practice']['default_test_num'] = $default_test_num;

	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}

/**
 * DB更新・削除 処理 学力診断　修正・削除処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function change() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_MONTH,$L_DAY,$L_HOUR,$L_MINUTE;
	//年リスト
	$L_YEAR = year_list();

	if (MODE == "詳細") {
		$kkn_from = $L_YEAR[$_POST['from_year']]."-".$L_MONTH[$_POST['from_month']]."-".$L_DAY[$_POST['from_day']];
		if ($_POST['from_hour'] || $_POST['from_minute'] || $_POST['from_second']) {
			$kkn_from .= " ".$L_HOUR[$_POST['from_hour']].":".$L_MINUTE[$_POST['from_minute']].":".$L_MINUTE[$_POST['from_second']];
		}
		$kkn_to = $L_YEAR[$_POST['to_year']]."-".$L_MONTH[$_POST['to_month']]."-".$L_DAY[$_POST['to_day']];
		if ($_POST['to_hour'] || $_POST['to_minute'] || $_POST['to_second']) {
			$kkn_to .= " ".$L_HOUR[$_POST['to_hour']].":".$L_MINUTE[$_POST['to_minute']].":".$L_MINUTE[$_POST['to_second']];
		}
//		$INSERT_DATA[test_gknn]		 	= $_POST['test_gknn'];
		$INSERT_DATA[test_name]		 	= $_POST['test_name'];
//		$INSERT_DATA[book_id]		 	= $_POST['book_id'];
//		$INSERT_DATA[kkn_from]		 	= $kkn_from;
//		$INSERT_DATA[kkn_to]		 	= $kkn_to;
		$INSERT_DATA[limit_time] 		= $_POST['limit_time'];// upd hirose 2020/08/31 テスト標準化開発 非表示状態を表示に変更
		$INSERT_DATA[test_bko] 			= $_POST['test_bko'];
		$INSERT_DATA[display] 			= $_POST['display'];
//		$INSERT_DATA[upd_syr_id] 		= ;
		$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];
		$INSERT_DATA[upd_date] 			= "now()";
	} elseif (MODE == "削除") {
		$INSERT_DATA[mk_flg] 			= 1;
		$INSERT_DATA[mk_tts_id] 		= $_SESSION['myid']['id'];
		$INSERT_DATA[mk_date] 			= "now()";
	}
	$where = " WHERE default_test_num='".$_POST['default_test_num']."' LIMIT 1;";
	$ERROR = $cdb->update(T_MS_TEST_DEFAULT,$INSERT_DATA,$where);

	unset($INSERT_DATA);
	if (MODE == "詳細") {

	} elseif (MODE == "削除") {
		$INSERT_DATA[mk_flg] 			= 1;
		$INSERT_DATA[mk_tts_id] 		= $_SESSION['myid']['id'];
		$INSERT_DATA[mk_date] 			= "now()";
		$where = " WHERE default_test_num='".$_POST['default_test_num']."'";
		$ERROR = $cdb->update(T_BOOK_GROUP_LIST,$INSERT_DATA,$where);
	}

	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}

/**
 * MS_TEST_DEFAULT上がる機能 グループ　表示順上昇処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function up() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if ($_SESSION['t_practice']['course_num']) {
		$where .= " AND course_num='".$_SESSION['t_practice']['course_num']."'";
	}
	$sql  = "SELECT * FROM " . T_MS_TEST_DEFAULT . " WHERE mk_flg='0' AND test_type='4' AND default_test_num='".$_POST['default_test_num']."'".$where." LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$m_default_test_num = $list['default_test_num'];
		$m_disp_sort = $list['disp_sort'];
	}
	if (!$m_default_test_num || !$m_disp_sort) { $ERROR[] = "移動するテスト情報が取得できません。"; }

	if (!$ERROR) {
		$sql  = "SELECT * FROM " . T_MS_TEST_DEFAULT . " WHERE mk_flg='0' AND test_type='4' AND disp_sort<'".$m_disp_sort."'".$where." ORDER BY disp_sort DESC LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$c_default_test_num = $list['default_test_num'];
			$c_disp_sort = $list['disp_sort'];
		}
	}
	if (!$c_default_test_num || !$c_disp_sort) { $ERROR[] = "移動されるテスト情報が取得できません。"; }

	if (!$ERROR) {
		$INSERT_DATA[disp_sort] 	= $c_disp_sort;
//		$INSERT_DATA[upd_syr_id] 	= ;
		$INSERT_DATA[upd_tts_id] 	= $_SESSION['myid']['id'];
		$INSERT_DATA[upd_date] 		= "now()";
		$where = " WHERE default_test_num='".$m_default_test_num."' LIMIT 1;";
		$ERROR = $cdb->update(T_MS_TEST_DEFAULT,$INSERT_DATA,$where);
	}

	if (!$ERROR) {
		$INSERT_DATA[disp_sort] 	= $m_disp_sort;
//		$INSERT_DATA[upd_syr_id] 	= ;
		$INSERT_DATA[upd_tts_id] 	= $_SESSION['myid']['id'];
		$INSERT_DATA[upd_date] 		= "now()";
		$where = " WHERE default_test_num='".$c_default_test_num."' LIMIT 1;";
		$ERROR = $cdb->update(T_MS_TEST_DEFAULT,$INSERT_DATA,$where);
	}

	return $ERROR;
}

/**
 * MS_TEST_DEFAULTを下がる機能 グループ　表示順下降処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function down() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if ($_SESSION['t_practice']['course_num']) {
		$where .= " AND course_num='".$_SESSION['t_practice']['course_num']."'";
	}
	$sql  = "SELECT * FROM " . T_MS_TEST_DEFAULT . " WHERE mk_flg='0' AND test_type='4' AND default_test_num='".$_POST['default_test_num']."'".$where." LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$m_default_test_num = $list['default_test_num'];
		$m_disp_sort = $list['disp_sort'];
	}
	if (!$m_default_test_num || !$m_disp_sort) { $ERROR[] = "移動するグループ情報が取得できません。".$sql; }

	if (!$ERROR) {
		$sql  = "SELECT * FROM " . T_MS_TEST_DEFAULT . " WHERE mk_flg='0' AND test_type='4' AND mk_flg='0' AND disp_sort>'".$m_disp_sort."'".$where." ORDER BY disp_sort LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$c_default_test_num = $list['default_test_num'];
			$c_disp_sort = $list['disp_sort'];
		}
	}
	if (!$c_default_test_num || !$c_disp_sort) { $ERROR[] = "移動されるグループ情報が取得できません。"; }

	if (!$ERROR) {
		$INSERT_DATA[disp_sort] = $c_disp_sort;
//		$INSERT_DATA[upd_syr_id] 	= ;
		$INSERT_DATA[upd_tts_id] 	= $_SESSION['myid']['id'];
		$INSERT_DATA[upd_date] 		= "now()";
		$where = " WHERE default_test_num='".$m_default_test_num."' LIMIT 1;";
		$ERROR = $cdb->update(T_MS_TEST_DEFAULT,$INSERT_DATA,$where);
	}

	if (!$ERROR) {
		$INSERT_DATA[disp_sort] = $m_disp_sort;
//		$INSERT_DATA[upd_syr_id] 	= ;
		$INSERT_DATA[upd_tts_id] 	= $_SESSION['myid']['id'];
		$INSERT_DATA[upd_date] 		= "now()";
		$where = " WHERE default_test_num='".$c_default_test_num."' LIMIT 1;";
		$ERROR = $cdb->update(T_MS_TEST_DEFAULT,$INSERT_DATA,$where);
	}

	return $ERROR;
}

/**
 * テスト　csvエクスポート
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function csv_export() {
	global $L_CSV_COLUMN;

	list($csv_line,$ERROR) = make_csv($L_CSV_COLUMN['test_book_trial'],1);
	if ($ERROR) { return $ERROR; }

	$filename = "ms_test_default_".$_SESSION['t_practice']['course_num'].".csv";

	header("Cache-Control: public");
	header("Pragma: public");
	header("Content-disposition: attachment;filename=$filename");
	if (stristr($HTTP_USER_AGENT, "MSIE")) {
		header("Content-Type: text/octet-stream");
	} else {
		header("Content-Type: application/octet-stream;");
	}
	echo $csv_line;

	exit;
}


/**
 * テスト　csv出力情報整形
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param $L_CSV_COLUMN
 * @param $head_mode='1'
 * @return array
 */
function make_csv($L_CSV_COLUMN,$head_mode='1') {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$csv_line = "";

	if (!is_array($L_CSV_COLUMN)) {
		$ERROR[] = "CSV抽出項目が設定されていません。";
		return array($csv_line,$ERROR);
	}
	//	head line (一行目)
	foreach ($L_CSV_COLUMN as $key => $val) {
		if ($head_mode == 1) { $head_name = $key; }
		elseif ($head_mode == 2) { $head_name = $val; }
		$csv_line .= $head_name.",";
	}

	$csv_line .= "\n";
	if ($_SESSION['t_practice']['course_num']) {
		$where .= " AND ms_test_default.course_num='".$_SESSION['t_practice']['course_num']."'";
	}
	if ($_SESSION['sub_session']['s_desc']) {
		$sort_key = " DESC";
	} else {
		$sort_key = " ASC";
	}
	$orderby = "ORDER BY ms_test_default.disp_sort ".$sort_key;
	$sql  = "SELECT ".
			"ms_test_default.default_test_num,".
			"ms_test_default.course_num,".
			"ms_test_default.test_name,".
			"ms_test_default.test_bko,".
			"ms_test_default.display,".
			"ms_test_default.limit_time,".// add hirose 2020/08/31 テスト標準化開発
			"ms_test_default.disp_sort".
			" FROM " . T_MS_TEST_DEFAULT . " ms_test_default" .
			" WHERE ms_test_default.mk_flg='0'".
			" AND ms_test_default.test_type='4'".$where.$orderby;
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			foreach ($L_CSV_COLUMN as $key => $val) {
				$list[$key] = str_replace("\r","",$list[$key]);
				$list[$key] = str_replace("\n","<br>",$list[$key]);
				$csv_line .= "\"".str_replace("\n","",$list[$key])."\",";
			}
			$csv_line .= "\n";
		}
		$cdb->free_result($result);
	}

	//	del 2015/01/07 yoshizawa 課題要望一覧No.400対応 下に新規で作成
	//$csv_line = mb_convert_encoding($csv_line,"sjis-win","UTF-8");
	//$csv_line = replace_decode_sjis($csv_line);
	//----------------------------------------------------------------

	//	add 2015/01/07 yoshizawa 課題要望一覧No.400対応
		//++++++++++++++++++++++//
		//	$_POST['exp_list']	//
		//	1 => SJIS			//
		//	2 => Unicode		//
		//++++++++++++++++++++++//
	//	utf-8で出力
	if ( $_POST['exp_list'] == 2 ) {
		//	Unicode選択時には特殊文字のみ変換
		$csv_line = replace_decode($csv_line);

	//	SJISで出力
	} else {
		$csv_line = mb_convert_encoding($csv_line,"sjis-win","UTF-8");
		$csv_line = replace_decode_sjis($csv_line);

	}
	//-------------------------------------------------

	return array($csv_line,$ERROR);
}


/**
 * テスト　csvインポート
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array
 */
function csv_import() {


	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$file_name = $_FILES['import_file']['name'];
	$file_tmp_name = $_FILES['import_file']['tmp_name'];
	$file_error = $_FILES['import_file']['error'];
	if (!$file_tmp_name) {
		$ERROR[] = "学力診断テストファイルが指定されておりません。";
	} elseif (!eregi("(.csv)$",$file_name)) {
		$ERROR[] = "ファイルの拡張子が不正です。";
	} elseif ($file_error == 1) {
		$ERROR[] = "アップロードできるファイルの容量が設定範囲を超えてます。サーバー管理者へ相談してください。";
	} elseif ($file_error == 3) {
		$ERROR[] = "教科書ファイルの一部分のみしかアップロードされませんでした。";
	} elseif ($file_error == 4) {
		$ERROR[] = "教科書ファイルがアップロードされませんでした。";
	}
	if ($ERROR) {
		if ($file_tmp_name && file_exists($file_tmp_name)) { unlink($file_tmp_name); }
		return $ERROR;
	}
	$ERROR = array();
	//登録教科書読込
	$L_IMPORT_LINE = file($file_tmp_name);
	//読込んだら一時ファイル破棄
	unlink($file_tmp_name);

	//１行目＝登録カラム
	$L_LIST_NAME = explode(",",trim($L_IMPORT_LINE[0]));
	//２行目以降＝登録データを形成
	for ($i = 1; $i < count($L_IMPORT_LINE);$i++) {
		unset($L_VALUE);
		unset($CHECK_DATA);
		unset($INSERT_DATA);

		$import_line = trim($L_IMPORT_LINE[$i]);
		$empty_check = preg_replace("/,/","",$import_line);
		if (!$empty_check) {
			$ERROR[] = $i."行目は空なのでスキップしました。<br>";
			continue;
		}
		$L_VALUE = explode(",",$import_line);

		if (!is_array($L_VALUE)) {
			$ERROR[] = $i."行目のcsv入力値が不正です。<br>";
			continue;
		}
		foreach ($L_VALUE as $key => $val) {
			if (!$val) { continue; }
			$val = preg_replace("/^\"|\"$/","",$val);
			$val = preg_replace("/<br>|<br \/>|<BR>|<BR \/>/","\n",$val);
			$val = trim($val);
			//	del 2014/12/08 yoshizawa 課題要望一覧No.394対応 下に新規で作成
			//$val = replace_encode_sjis($val);
			//$val = mb_convert_encoding($val,"UTF-8","sjis-win");
			//----------------------------------------------------------------
			//	add 2014/12/08 yoshizawa 課題要望一覧No.394対応
			//	データの文字コードがUTF-8だったら変換処理をしない
			$code = judgeCharacterCode ( $val );
			if ( $code != 'UTF-8' ) {
				$val = replace_encode_sjis($val);
				$val = mb_convert_encoding($val,"UTF-8","sjis-win");
			}
			//	add 2015/01/09 yoshizawa 課題要望一覧No.400対応
			//	sjisファイルをインポートしても2バイト文字はutf-8で扱われる
			else {
				//	記号は特殊文字に変換します
				$val = replace_encode($val);

			}
			//--------------------------------------------------

			//カナ変換
			$val = mb_convert_kana($val,"asKVn","UTF-8");
			$CHECK_DATA[$L_LIST_NAME[$key]] = $val;
		}
		if (!$CHECK_DATA['display']) { $CHECK_DATA['display'] = 1; }
		$sql  = "SELECT ".
			"mtd.default_test_num".
			" FROM " . T_MS_TEST_DEFAULT . " mtd" .
			" WHERE mtd.default_test_num='".$CHECK_DATA['default_test_num']."' AND mtd.mk_flg='0'";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
		}
		if ($list) { $ins_mode = "upd"; } else { $ins_mode = "add"; }

		//データチェック
		$DATA_ERROR[$i] = check_data($CHECK_DATA,$ins_mode,$i);
		if ($DATA_ERROR[$i]) { continue; }

		$INSERT_DATA = $CHECK_DATA;
		//レコードがあればアップデート、無ければインサート
		if ($ins_mode == "add") {
			$sql = "SELECT MAX(default_test_num) AS max_num FROM " . T_MS_TEST_DEFAULT . ";";
			if ($result = $cdb->query($sql)) {
				$list = $cdb->fetch_assoc($result);
			}
			if (!$CHECK_DATA['default_test_num']) {
				if ($list['max_num']) { $INSERT_DATA['default_test_num'] = $list['max_num'] + 1; }
				else { $INSERT_DATA['default_test_num'] = 1; }
			}
			$INSERT_DATA['disp_sort'] = $list['max_num'] + 1;

			$INSERT_DATA[test_type] 		= 4;
			$INSERT_DATA[apply_level] 		= 1;
//			$INSERT_DATA[ins_syr_id] 		= ;
			$INSERT_DATA[ins_tts_id] 		= "System";
			$INSERT_DATA[ins_date] 			= "now()";
			$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];
			$INSERT_DATA[upd_date] 			= "now()";

			$SYS_ERROR[$i] = $cdb->insert(T_MS_TEST_DEFAULT,$INSERT_DATA);
		} else {
//			$INSERT_DATA[upd_syr_id] 		= ;
			$INSERT_DATA[upd_tts_id] 		= "System";
			$INSERT_DATA[upd_date] 			= "now()";

			$where = " WHERE default_test_num='".$CHECK_DATA['default_test_num']."' LIMIT 1;";
			$SYS_ERROR[$i] = $cdb->update(T_MS_TEST_DEFAULT,$INSERT_DATA,$where);
		}
		if ($SYS_ERROR[$i]) { $SYS_ERROR[$i][] = $i."行目 上記システムエラーによりスキップしました。<br>"; }
	}
	//各エラー結合
	if(is_array($DATA_ERROR)) {
		foreach($DATA_ERROR as $key => $val) {
			if (!$DATA_ERROR[$key]) { continue; }
			$ERROR = array_merge($ERROR,$DATA_ERROR[$key]);
		}
	}
	if(is_array($SYS_ERROR)) {
		foreach($SYS_ERROR as $key => $val) {
			if (!$SYS_ERROR[$key]) { continue; }
			$ERROR = array_merge($ERROR,$SYS_ERROR[$key]);
		}
	}
	if (!$ERROR) { $html = "<br>正常に全て登録が完了しました。"; }
	else { $html = "<br>エラーのある行数以外の登録が完了しました。"; }

	return array($html,$ERROR);
}


/**
 * 学力診断テストマスタ　csvインポートチェック
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $CHECK_DATA
 * @param mixed $ins_mode
 * @param integer $line_num
 * @return array エラーの場合
 */
function check_data($CHECK_DATA,$ins_mode,$line_num) {

	// update start oda 2014/08/08 課題要望一覧No299 画面で指定したコース以外のデータが存在する場合、エラーとする
	//if (!$CHECK_DATA['course_num']) { $ERROR[] = $line_num."行目 コース番号が未入力です。"; }
	//elseif ($CHECK_DATA['course_num'] != "1" && $CHECK_DATA['course_num'] != "2" && $CHECK_DATA['course_num'] != "3") { //2011/06/13 add oz
	//	$ERROR[] = $line_num."行目 コース番号が不正です。";
	//}
	// upd start hirose 2020/09/10 テスト標準化開発
	// $overseas_course = get_overseas_course_name();//add hirose 2020/08/27 テスト標準化開発
	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
	
	$test_type4 = new TestStdCfgType4($cdb);
	$overseas_course = $test_type4->getTestUseCourseAdmin();
	// upd end hirose 2020/09/10 テスト標準化開発
	if (!$CHECK_DATA['course_num']) {
		$ERROR[] = $line_num."行目 コース番号が未入力です。";
	// upd start hirose 2020/09/16 テスト標準化開発
	//もしも英国数理社のいずれかが外れた場合にも通ってしまうため修正
	// } elseif (  $CHECK_DATA['course_num'] != "1" 
	// 	&& $CHECK_DATA['course_num'] != "2" 
	// 	&& $CHECK_DATA['course_num'] != "3"
	// 	&& $CHECK_DATA['course_num'] != "15" // add 2018/05/18 理科社会対応
	// 	&& $CHECK_DATA['course_num'] != "16" // add 2018/05/18 理科社会対応
	// 	&& empty($overseas_course[$CHECK_DATA['course_num']]) // add hirose 2020/08/27 テスト標準化開発
	} elseif (
		empty($overseas_course[$CHECK_DATA['course_num']])
	// upd end hirose 2020/09/16 テスト標準化開発
	) {
		$ERROR[] = $line_num."行目 コース番号が不正です。";
	} elseif ($CHECK_DATA['course_num'] != $_SESSION['t_practice']['course_num']) {
		$ERROR[] = $line_num."行目 選択したコースと登録するコースが異なります。";
	}
	// update end oda 2014/08/08

	// add start oda 2014/08/08 課題要望一覧No301 学力診断テストマスタ番号に文字列を指定すると０で登録されてしまうので、エラーとする
	if (preg_match("/[^0-9]/",$CHECK_DATA['default_test_num'])) {
		$ERROR[] = $line_num."行目 学力診断テストマスタ番号は数字以外の指定はできません。";
	}
	// add end oda 2014/08/08

	// add start oda 2014/08/08 課題要望一覧No302 学力診断テストマスタ名が未設定の場合はエラーとする
	if (!$CHECK_DATA['test_name']) {
		$ERROR[] = $line_num."行目テスト名(補助名)が未入力です。";
	}
	// add end oda 2014/08/08

	if (!$CHECK_DATA['display']) { $ERROR[] = $line_num."行目 表示・非表示が未入力です。"; }
	else {
		if (preg_match("/[^0-9]/",$CHECK_DATA['display'])) {
			$ERROR[] = $line_num."行目 表示・非表示は数字以外の指定はできません。";
		} elseif ($CHECK_DATA['display'] < 1 || $CHECK_DATA['display'] > 2) {
			$ERROR[] = $line_num."行目 表示・非表示は1（表示）か2（非表示）の数字以外の指定はできません。";
		}
	}

	// add start oda 2014/08/08 課題要望一覧No304/No305 ソート順が未設定の場合はエラーとする (修正モードの時のみ判断。登録の時は、MAX+1を設定している為)
	if ($ins_mode == "upd") {
		if (!$CHECK_DATA['disp_sort']) {
			$ERROR[] = $line_num."行目 ソート順が未入力です。";
		} else if (preg_match("/[^0-9]/",$CHECK_DATA['disp_sort'])) {
			$ERROR[] = $line_num."行目 ソート順は数字以外の指定はできません。";
		}
	}
	// add end oda 2014/08/08

	// add start hirose 2020/08/31 テスト標準化開発
	if (!$CHECK_DATA['limit_time']) {
		$ERROR[] = $line_num."行目テスト制限時間が未入力です。";
	}elseif(!preg_match("/^[0-9]*$/",$CHECK_DATA['limit_time'])){
		$ERROR[] = $line_num."行目テスト制限時間は半角数字のみで入力してください。";
	}elseif($CHECK_DATA['limit_time']>90 || $CHECK_DATA['limit_time']<1){
		$ERROR[] = $line_num."行目テスト制限時間は1分から90分の間で指定してください。";
	}
	// add end hirose 2020/08/31 テスト標準化開発

	if ($ERROR) { $ERROR[] = $line_num."行目 上記入力エラーでスキップしました。<br>"; }
	return $ERROR;
}

/**
 * コース情報を取得し配列を返却する
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array
 */
function course_list() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$L_COURSE_LIST = array();
	$L_COURSE_LIST[] = "選択して下さい";
	$sql  = "SELECT * FROM ".T_COURSE. " WHERE state='0' ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		while ($list=$cdb->fetch_assoc($result)) {
			$L_COURSE_LIST[$list['course_num']] = $list['course_name'];
		}
	}
	return $L_COURSE_LIST;
}

/**
 * コースと学年に対して教科書一覧
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author azet
 * @param integer  $course_num
 * @param string $gknn
 * @return array
 */
function book_list($course_num,$gknn) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$L_BOOK_LIST = array();
	if (!$course_num) {
		$L_BOOK_LIST[0] = "------";
		return $L_BOOK_LIST;
	}
	$L_BOOK_LIST[0] = "選択して下さい";
	$sql  = "SELECT * FROM ".T_MS_BOOK.
		" WHERE mk_flg='0' AND publishing_id='0' AND course_num='".$course_num."' ORDER BY disp_sort;";
	if ($result = $cdb->query($sql)) {
		while ($list=$cdb->fetch_assoc($result)) {
			$L_BOOK_LIST[$list['book_id']] = $list['book_name'];
		}
	}
	return $L_BOOK_LIST;
}

/**
 *
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $course_num
 * @return array
 */
function test_group_list($course_num) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$L_BOOK_GROUP = array();
	if (!$course_num) {
		$L_BOOK_LIST[0] = "------";
		return $L_BOOK_LIST;
	}
	$L_BOOK_GROUP[0] = "選択して下さい";
	$sql  = "SELECT * FROM ".T_MS_BOOK_GROUP.
		" WHERE mk_flg='0'".
		// " AND srvc_cd = 'GTEST' ". // add yoshizawa 2015/09/29 02_作業要件/34_数学検定   // del 2020/09/14 thanh テスト標準化開発
		" AND class_id = '' ".	// add 2020/09/14 thanh テスト標準化開発
		" ORDER BY disp_sort;";
	if ($result = $cdb->query($sql)) {
		while ($list=$cdb->fetch_assoc($result)) {
			$L_BOOK_GROUP[$list['test_group_id']] = $list['test_group_name'];
		}
	}
	return $L_BOOK_GROUP;
}

/**
 * 年一覧
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array
 */
function year_list() {
	$L_YEAR = array();
	$L_YEAR[] = "----";
//T_MS_TEST_DEFAULTのkkn_fromの最古年数を抜き出してその年から
	for($i = 0;$i < 3; $i++) {
		$year = date("Y",mktime(0, 0, 0, 1, 1, date('Y') + $i));
		$L_YEAR[$year] = $year;
	}
	return $L_YEAR;
}

?>
