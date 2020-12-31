<?
/**
 * ベンチャー・リンク　すらら
 *
 * テスト用プラクティス管理　学力診断テスト単元マスタ管理
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
		elseif (ACTION == "export") { $ERROR = csv_export(); }
		elseif (ACTION == "import") { list($html,$ERROR) = csv_import(); }
	}

	if (MODE == "add") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= addform($ERROR); }
		} elseif (ACTION == "add") {
			if (!$ERROR) { $html .= book_list($ERROR); }
			else { $html .= addform($ERROR); }
		} else {
			$html .= addform($ERROR);
		}
	} elseif (MODE == "詳細") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= viewform($ERROR); }
		} elseif (ACTION == "change") {
			if (!$ERROR) { $html .= book_list($ERROR); }
			else { $html .= viewform($ERROR); }
		} else {
			$html .= viewform($ERROR);
		}
	} elseif (MODE == "削除") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= viewform($ERROR); }
		} elseif (ACTION == "change") {
			if (!$ERROR) { $html .= book_list($ERROR); }
			else { $html .= viewform($ERROR); }
		} else {
			$html .= check_html();
		}
	} else {
		$html .= book_list($ERROR);
	}

	return $html;
}

/**
 * 学力診断テスト単元マスタ　絞り込みメニュー
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function select_menu() {
	global $L_BOOK_ORDER,$L_DESC,$L_PAGE_VIEW,$L_GKNN_LIST;
	global $L_WRITE_TYPE;	//	add ookawara 2012/07/29

	// upd start hirose 2020/09/11 テスト標準化開発
	// $l_write_type = $L_WRITE_TYPE + get_overseas_course_name();//add hirose 2020/08/26 テスト標準化開発
	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
	
	$test_type4 = new TestStdCfgType4($cdb);
	$l_write_type = $test_type4->getTestUseCourseAdmin();
	// upd end hirose 2020/09/11 テスト標準化開発

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
		if ($_SESSION['sub_session']['course_num'] == $course_num) {
			$selected = "selected";
		}
		$couse_html .= "<option value=\"".$course_num."\" ".$selected.">".$course_name."</option>\n";
	}
	//	add ookawara 2012/07/29 end
	//学年
	foreach($L_GKNN_LIST as $key => $val) {
		if ($_SESSION['sub_session']['gknn'] == $key) { $selected = "selected"; } else { $selected = ""; }
		$gknn_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
	}
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
	$sub_session_html .= "コース <select name=\"course_num\">\n".$couse_html."</select>\n";
	$sub_session_html .= "学年 <select name=\"gknn\">\n".$gknn_html."</select>\n";
	$sub_session_html .= "ソート <select name=\"s_order\">\n".$s_order_html."</select>\n";
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
 * 　学力診断テスト単元マスタ絞り込みメニューセッション操作
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function sub_session() {
	if (strlen($_POST['course_num'])) { $_SESSION['sub_session']['course_num'] = $_POST['course_num']; }
	if (strlen($_POST['gknn'])) { $_SESSION['sub_session']['gknn'] = $_POST['gknn']; }
	if (strlen($_POST['s_order'])) { $_SESSION['sub_session']['s_order'] = $_POST['s_order']; }
	if (strlen($_POST['s_desc'])) { $_SESSION['sub_session']['s_desc'] = $_POST['s_desc']; }
	if (strlen($_POST['s_page_view'])) { $_SESSION['sub_session']['s_page_view'] = $_POST['s_page_view']; }
	if (strlen($_POST['s_order'])&&strlen($_POST['s_desc'])&&strlen($_POST['s_page_view'])) { unset($_SESSION['sub_session']['s_page']); }
	if (strlen($_POST['s_page'])) { $_SESSION['sub_session']['s_page'] = $_POST['s_page']; }

	return;
}

/**学力診断テスト単元マスタ一覧
 *
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function book_list($ERROR) {

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
	$html .= "インポートする場合は、学力診断テスト単元マスタcsvファイル（S-JIS）を指定しCSVインポートボタンを押してください。<br>\n";
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
	if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__add",$_SESSION['authority'])===FALSE)) {
		$html .= "<br>\n";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"add\">\n";
		$html .= "<input type=\"submit\" value=\"マスタ新規登録\">\n";
		$html .= "</form>\n";
	}

	if ($_SESSION['sub_session']['course_num']) {
		$where .= " AND course.course_num='".$_SESSION['sub_session']['course_num']."'";
	}
	if ($_SESSION['sub_session']['gknn']) {
		$where .= " AND ms_book.gknn='".$_SESSION['sub_session']['gknn']."'";
	}

	$sql  = "SELECT ".
			" ms_book.book_id,".
			" ms_book.course_num,".
			" course.course_name,".
			" ms_book.book_name,".
			" ms_book.gknn,".
//			" ms_test_group.test_group_name,".
			" ms_book.display".
			" FROM " . T_MS_BOOK . " ms_book" .
			" LEFT JOIN ".T_MS_PUBLISHING." ms_publishing ON ms_publishing.publishing_id=ms_book.publishing_id".
			" LEFT JOIN ".T_COURSE." course ON course.course_num=ms_book.course_num".
//			" LEFT JOIN ".T_BOOK_GROUP_LIST." test_group_list ON test_group_list.book_id=ms_book.book_id AND test_group_list.mk_flg='0'".
//			" LEFT JOIN ".T_MS_BOOK_GROUP." ms_test_group ON ms_test_group.test_group_id=test_group_list.test_group_id".
			" WHERE ms_book.mk_flg='0' AND ms_book.publishing_id='0'".$where;

	if ($result = $cdb->query($sql)) {
		$book_count = $cdb->num_rows($result);
	}
	$html .= select_menu();	//	add ookawara 2012/07/29
	if (!$book_count) {
		$html .= "<br>\n";
		$html .= "今現在登録されている学力診断テスト単元マスタは有りません。\n";
		return $html;
	}
	//$html .= select_menu();	//	del ookawara 2012/07/29

	if ($_SESSION['sub_session']['s_desc']) {
		$sort_key = " DESC";
	} else {
		$sort_key = " ASC";
	}
	if ($_SESSION['sub_session']['s_order']) {
		if ($_SESSION['sub_session']['s_order'] == 1) {
			$orderby = "ORDER BY ms_publishing.publishing_id ".$sort_key.",ms_book.disp_sort ".$sort_key;
		} elseif ($_SESSION['sub_session']['s_order'] == 2) {
			$orderby = "ORDER BY course.course_num ".$sort_key.",ms_book.disp_sort ".$sort_key;
		} elseif ($_SESSION['sub_session']['s_order'] == 3) {
			$orderby = "ORDER BY ms_book.gknn ".$sort_key.",ms_book.disp_sort ".$sort_key;
		}
	} else {
		$orderby = "ORDER BY ms_book.disp_sort ".$sort_key;
	}
	if ($L_PAGE_VIEW[$_SESSION['sub_session']['s_page_view']]) { $page_view = $L_PAGE_VIEW[$_SESSION['sub_session']['s_page_view']]; }
	else { $page_view = $L_PAGE_VIEW[0]; }
	$max_page = ceil($book_count/$page_view);
	if ($_SESSION['sub_session']['s_page']) { $page = $_SESSION['sub_session']['s_page']; }
	else { $page = 1; }
	$start = ($page - 1) * $page_view;
	$next = $page + 1;
	$back = $page - 1;

	$sql .= $orderby." LIMIT ".$start.",".$page_view.";";

	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);

		$html .= "<br>\n";
		$html .= "修正する場合は、修正する学力診断テスト単元マスタの詳細ボタンを押してください。<br>\n";
		$html .= "<div style=\"float:left;\">登録マスタ総数(".$book_count."):PAGE[".$page."/".$max_page."]</div>\n";
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
		$html .= "<th>マスタID</th>\n";
		$html .= "<th>コース</th>\n";
		$html .= "<th>マスタ名</th>\n";
		$html .= "<th>学年</th>\n";
//		$html .= "<th>グループ</th>\n";
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
			$up_submit = $down_submit = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			if (!$_SESSION['sub_session']['s_order'] && !$_SESSION['sub_session']['s_desc']) {
				if ($i != 1 || $page != 1) { $up_submit = "<input type=\"submit\" name=\"action\" value=\"↑\">\n"; }
				if ($i != $max || $page != $max_page) { $down_submit = "<input type=\"submit\" name=\"action\" value=\"↓\">\n"; }
			}
//			if (!$list['test_group_name']) { $list['test_group_name'] = "---"; }

			$html .= "<tr class=\"course_form_cell\">\n";
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"book_id\" value=\"".$list['book_id']."\">\n";
			if (!ereg("practice__view",$authority)
				&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__sort",$_SESSION['authority'])===FALSE))
			) {
				$html .= "<td>".$up_submit."</td>\n";
				$html .= "<td>".$down_submit."</td>\n";
			}
			$html .= "<td>".$list['book_id']."</td>\n";
			$html .= "<td>".$list['course_name']."</td>\n";
			$html .= "<td>".$list['book_name']."</td>\n";
			$html .= "<td>".$L_GKNN_LIST[$list['gknn']]."</td>\n";
//			$html .= "<td>".$list['test_group_name']."</td>\n";
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
 * 学力診断テスト単元マスタ　新規登録フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function addform($ERROR) {
	global $L_GKNN_LIST,$L_DISPLAY;

	//コースリスト
	$L_COURSE_LIST = course_list();

	$html .= "<br>\n";
	$html .= "新規登録フォーム<br>\n";

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(TEST_BOOK_TRIAL_FORM);

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
	$INPUTS['BOOKID'] 		= array('result'=>'plane','value'=>"---");
	$INPUTS['PUBLISHINGID'] 	= array('type'=>'hidden','name'=>'publishing_id','value'=>'0');
	$INPUTS['COURSENUM'] 		= array('type'=>'select','name'=>'course_num','array'=>$L_COURSE_LIST,'check'=>$_POST['course_num']);
	$INPUTS['GKNN'] 			= array('type'=>'select','name'=>'gknn','array'=>$L_GKNN_LIST,'check'=>$_POST['gknn']);
	$INPUTS['BOOKNAME'] 		= array('type'=>'text','name'=>'book_name','size'=>'50','value'=>$_POST['book_name']);
//	$INPUTS['BOOKGROUPID'] 	= array('type'=>'select','name'=>'test_group_id','array'=>$L_BOOK_GROUP_LIST,'check'=>$_POST['test_group_id']);
	$INPUTS['USRBKO'] 		= array('type'=>'text','name'=>'usr_bko','size'=>'50','value'=>$_POST['usr_bko']);
	$INPUTS['DISPLAY'] 		= array('result'=>'plane','value'=>$display_html);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"追加確認\">\n";
	$html .= "<input type=\"reset\" value=\"クリア\">\n";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"book_list\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";
	return $html;
}

/**
 * 学力診断テスト単元マスタ　修正フォーム
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

	global $L_GKNN_LIST,$L_DISPLAY;

	if (ACTION) {
		foreach ($_POST as $key => $val) { $$key = $val; }
	} else {
		$sql  = "SELECT ".
			" ms_book.book_id,".
			" ms_book.course_num,".
			" ms_book.book_name,".
			" ms_book.gknn,".
			" ms_book.usr_bko,".	//2011/06/13 add oz
			" ms_book.display".
			" FROM " . T_MS_BOOK . " ms_book" .
//		$sql = "SELECT * FROM " . T_MS_BOOK ." ms_book".
//			" LEFT JOIN ".T_BOOK_GROUP_LIST." test_group_list ON test_group_list.book_id=ms_book.book_id".
			" WHERE ms_book.book_id='".$_POST['book_id']."' AND ms_book.mk_flg='0' LIMIT 1;";
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
	//コースリスト
	$L_COURSE_LIST = course_list();

	$html = "<br>\n";
	$html .= "詳細画面<br>\n";

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";
	$html .= "<input type=\"hidden\" name=\"book_id\" value=\"".$book_id."\">\n";
	$html .= "<input type=\"hidden\" name=\"publishing_id\" value=\"".$publishing_id."\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$course_num."\">\n";
	$html .= "<input type=\"hidden\" name=\"gknn\" value=\"".$gknn."\">\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(TEST_BOOK_TRIAL_FORM);

	if (!$book_id) { $book_id = "---"; }

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
	$INPUTS[BOOKID] 		= array('result'=>'plane','value'=>$book_id);
	$INPUTS[PUBLISHINGID] 	= array('type'=>'hidden','name'=>'publishing_id','value'=>'0');
	$INPUTS[COURSENUM] 		= array('result'=>'plane','value'=>$L_COURSE_LIST[$course_num]);
	$INPUTS[GKNN] 			= array('result'=>'plane','value'=>$L_GKNN_LIST[$gknn]);
	$INPUTS[BOOKNAME] 		= array('type'=>'text','name'=>'book_name','size'=>'50','value'=>$book_name);
//	$INPUTS[BOOKGROUPID] 	= array('type'=>'select','name'=>'test_group_id','array'=>$L_BOOK_GROUP_LIST,'check'=>$test_group_id);
	$INPUTS[USRBKO] 		= array('type'=>'text','name'=>'usr_bko','size'=>'50','value'=>$usr_bko);
	$INPUTS[DISPLAY] 		= array('result'=>'plane','value'=>$display_html);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"変更確認\">";
	$html .= "<input type=\"reset\" value=\"クリア\">";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"book_list\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}

/**
 * 学力診断テスト単元マスタ　新規登録・修正　必須項目チェック
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラー配列
 */
function check() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (!$_POST['book_name']) { $ERROR[] = "マスタ名が未入力です。"; }
	else {
		if (MODE == "add") {
			$sql  = "SELECT * FROM " . T_MS_BOOK . " WHERE mk_flg='0'".
				" AND publishing_id='0' AND course_num='".$_POST['course_num']."'".
				" AND gknn='".$_POST['gknn']."' AND book_name='".$_POST['book_name']."'";
		} else {
			$sql  = "SELECT * FROM " . T_MS_BOOK . " WHERE mk_flg='0'".
				" AND publishing_id!='0' AND course_num='".$_POST['course_num']."'".
				" AND gknn='".$_POST['gknn']."' AND book_id!='".$_POST['book_id']."' AND book_name='".$_POST['book_name']."'";
		}
		if ($result = $cdb->query($sql)) {
			$count = $cdb->num_rows($result);
		}
		if ($count > 0) { $ERROR[] = "入力されたマスタ名は既に登録されております。"; }
	}
//	if (!$_POST['publishing_id']) { $ERROR[] = "出版社が未選択です。"; }
	if (!$_POST['course_num']) { $ERROR[] = "コースが未選択です。"; }
	if (!$_POST['gknn']) { $ERROR[] = "学年が未選択です。"; }
//	if (!$_POST['test_group_id']) { $ERROR[] = "グループが未選択です。"; }
	if (!$_POST['display']) { $ERROR[] = "表示・非表示が未選択です。"; }

	if (mb_strlen($_POST['usr_bko'], 'UTF-8') > 255) { $ERROR[] = "備考が不正です。255文字以内で記述して下さい。"; }

	return $ERROR;
}

/**
 * 学力診断テスト単元マスタ　新規登録・修正・削除　確認フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTMLフォーム
 */
function check_html() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_GKNN_LIST,$L_DISPLAY;

	if ($_POST) {
		foreach ($_POST as $key => $val) {
			if ($key == "action") {
				if (MODE == "add") { $val = "add"; }
				elseif (MODE == "詳細") { $val = "change"; }
			}
			$HIDDEN .= "<input type=\"hidden\" name=\"".$key."\" value=\"".$val."\">\n";
		}
	}

	if (ACTION) {
		foreach ($_POST as $key => $val) { $$key = $val; }
	} else {
		$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"change\">\n";
		$sql = "SELECT * FROM " . T_MS_BOOK ." ms_book".
//			" LEFT JOIN ".T_BOOK_GROUP_LIST." test_group_list ON test_group_list.book_id=ms_book.book_id".
			" WHERE ms_book.book_id='".$_POST['book_id']."' AND ms_book.mk_flg='0' LIMIT 1;";
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
	//コースリスト
	$L_COURSE_LIST = course_list();

	if (MODE != "削除") { $button = "登録"; } else { $button = "削除"; }
	$html = "<br>\n";
	$html .= "確認画面：以下の内容で".$button."してもよろしければ".$button."ボタンをクリックしてください。<br>\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(TEST_BOOK_TRIAL_FORM);

//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	if (!$book_id) { $book_id = "---"; }
	$INPUTS[BOOKID] 		= array('result'=>'plane','value'=>$book_id);
	$INPUTS[COURSENUM] 		= array('result'=>'plane','value'=>$L_COURSE_LIST[$course_num]);
	$INPUTS[GKNN] 			= array('result'=>'plane','value'=>$L_GKNN_LIST[$gknn]);
	$INPUTS[BOOKNAME] 		= array('result'=>'plane','value'=>$book_name);
//	$INPUTS[BOOKGROUPID] 		= array('result'=>'plane','value'=>$L_BOOK_GROUP_LIST[$test_group_id]);
	$INPUTS[USRBKO] 		= array('result'=>'plane','value'=>$usr_bko);
	$INPUTS[DISPLAY] 		= array('result'=>'plane','value'=>$L_DISPLAY[$display]);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" style=\"float:left\">\n";
	$html .= $HIDDEN;
	$html .= "<input type=\"submit\" value=\"".$button."\">\n";
	$html .= "</form>";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";

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
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"book_list\">\n";
	}
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}

/**
 * 学力診断テスト単元マスタ　新規登録処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function add() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql = "SELECT MAX(suffix_num) AS max_suffix FROM " . T_MS_BOOK .
//		" WHERE mk_flg='0' AND publishing_id=".$_POST['publishing_id']." AND course_num=".$_POST['course_num']." AND gknn='".$_POST['gknn']."';";	//2011/06/13 del oz
		" WHERE publishing_id=".$_POST['publishing_id']." AND course_num=".$_POST['course_num']." AND gknn='".$_POST['gknn']."';";	//2011/06/13 add oz
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
	}
	if ($list['max_suffix']) { $suffix_num = $list['max_suffix'] + 1; } else { $suffix_num = 1; }

	$sql = "SELECT MAX(disp_sort) AS max_sort FROM " . T_MS_BOOK .
		" WHERE mk_flg='0' AND publishing_id='0';";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
	}
	if ($list['max_sort']) { $disp_sort = $list['max_sort'] + 1; } else { $disp_sort = 1; }

	//book_id： ### ## ## ###: 出版社+コース+学年+枝番
	$INSERT_DATA[book_id] 			= sprintf("%03d",$_POST['publishing_id']).sprintf("%02d",$_POST['course_num']).$_POST['gknn'].sprintf("%03d",$suffix_num);
	$INSERT_DATA[publishing_id] 	= $_POST['publishing_id'];
	$INSERT_DATA[course_num] 		= $_POST['course_num'];
	$INSERT_DATA[gknn] 				= $_POST['gknn'];
	$INSERT_DATA[suffix_num] 		= $suffix_num;
	$INSERT_DATA[book_name] 		= $_POST['book_name'];
	$INSERT_DATA[disp_sort] 		= $disp_sort;
	$INSERT_DATA[usr_bko] 			= $_POST['usr_bko'];
	$INSERT_DATA[display] 			= $_POST['display'];
//	$INSERT_DATA[ins_syr_id] 		= ;
	$INSERT_DATA[ins_tts_id] 		= $_SESSION['myid']['id'];
	$INSERT_DATA[ins_date] 			= "now()";
	$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];
	$INSERT_DATA[upd_date] 			= "now()";

	$ERROR = $cdb->insert(T_MS_BOOK,$INSERT_DATA);
	unset($INSERT_DATA);

	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}

/**
 * 学力診断テスト単元マスタ　修正・削除処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function change() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (MODE == "詳細") {
		$INSERT_DATA[book_name] 		= $_POST['book_name'];
		$INSERT_DATA[usr_bko] 			= $_POST['usr_bko'];
		$INSERT_DATA[display] 			= $_POST['display'];
//		$INSERT_DATA[upd_syr_id] 		= ;
		$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];
		$INSERT_DATA[upd_date] 			= "now()";
	} elseif (MODE == "削除") {
		$INSERT_DATA[mk_flg] 			= 1;
		$INSERT_DATA[mk_tts_id] 		= $_SESSION['myid']['id'];
		$INSERT_DATA[mk_date] 			= "now()";
	}
	if ($INSERT_DATA) {
		$where = " WHERE book_id='".$_POST['book_id']."' LIMIT 1;";
		$ERROR = $cdb->update(T_MS_BOOK,$INSERT_DATA,$where);
	}

	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}

/**
 * MS_BOOKを上がる機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function up() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql  = "SELECT * FROM $TABLE" . T_MS_BOOK . " WHERE book_id='".$_POST['book_id']."' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$m_book_id = $list['book_id'];
		$m_disp_sort = $list['disp_sort'];
	}
	if (!$m_book_id || !$m_disp_sort) { $ERROR[] = "移動する学力診断テスト単元マスタ情報が取得できません。"; }

	if (!$ERROR) {
		$sql  = "SELECT * FROM " . T_MS_BOOK . " WHERE mk_flg='0' AND publishing_id='0'".
			" AND disp_sort<'".$m_disp_sort."' ORDER BY disp_sort DESC LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$c_book_id = $list['book_id'];
			$c_disp_sort = $list['disp_sort'];
		}
	}
	if (!$c_book_id || !$c_disp_sort) { $ERROR[] = "移動される学力診断テスト単元マスタ情報が取得できません。"; }

	if (!$ERROR) {
		$INSERT_DATA[disp_sort] 	= $c_disp_sort;
//		$INSERT_DATA[upd_syr_id] 	= ;
		$INSERT_DATA[upd_tts_id] 	= $_SESSION['myid']['id'];
		$INSERT_DATA[upd_date] 		= "now()";
		$where = " WHERE book_id='".$m_book_id."' LIMIT 1;";
		$ERROR = $cdb->update(T_MS_BOOK,$INSERT_DATA,$where);
	}

	if (!$ERROR) {
		$INSERT_DATA[disp_sort] 	= $m_disp_sort;
//		$INSERT_DATA[upd_syr_id] 	= ;
		$INSERT_DATA[upd_tts_id] 	= $_SESSION['myid']['id'];
		$INSERT_DATA[upd_date] 		= "now()";
		$where = " WHERE book_id='".$c_book_id."' LIMIT 1;";
		$ERROR = $cdb->update(T_MS_BOOK,$INSERT_DATA,$where);
	}

	return $ERROR;
}

/**
 * MS_BOOKを下がる機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function down() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql  = "SELECT * FROM " . T_MS_BOOK . " WHERE book_id='".$_POST['book_id']."' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$m_book_id = $list['book_id'];
		$m_disp_sort = $list['disp_sort'];
	}
	if (!$m_book_id || !$m_disp_sort) { $ERROR[] = "移動する学力診断テスト単元マスタ情報が取得できません。"; }

	if (!$ERROR) {
		$sql  = "SELECT * FROM " . T_MS_BOOK . " WHERE mk_flg='0' AND publishing_id='0'".
			" AND disp_sort>'".$m_disp_sort."' ORDER BY disp_sort LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$c_book_id = $list['book_id'];
			$c_disp_sort = $list['disp_sort'];
		}
	}
	if (!$c_book_id || !$c_disp_sort) { $ERROR[] = "移動される学力診断テスト単元マスタ情報が取得できません。"; }

	if (!$ERROR) {
		$INSERT_DATA[disp_sort] = $c_disp_sort;
//		$INSERT_DATA[upd_syr_id] 	= ;
		$INSERT_DATA[upd_tts_id] 	= $_SESSION['myid']['id'];
		$INSERT_DATA[upd_date] 		= "now()";
		$where = " WHERE book_id='".$m_book_id."' LIMIT 1;";
		$ERROR = $cdb->update(T_MS_BOOK,$INSERT_DATA,$where);
	}

	if (!$ERROR) {
		$INSERT_DATA[disp_sort] = $m_disp_sort;
//		$INSERT_DATA[upd_syr_id] 	= ;
		$INSERT_DATA[upd_tts_id] 	= $_SESSION['myid']['id'];
		$INSERT_DATA[upd_date] 		= "now()";
		$where = " WHERE book_id='".$c_book_id."' LIMIT 1;";
		$ERROR = $cdb->update(T_MS_BOOK,$INSERT_DATA,$where);
	}

	return $ERROR;
}

/**
 * csvエクスポート
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function csv_export() {
	global $L_CSV_COLUMN;

	list($csv_line,$ERROR) = make_csv($L_CSV_COLUMN['book_trial'],1);
	if ($ERROR) { return $ERROR; }

	$filename = "ms_trial.csv";

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
 * csv出力情報整形
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @param $L_CSV_COLUMN
 * @param $head_mode='1'
 * @author Azet
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

	$sql  = "SELECT * FROM " . T_MS_BOOK .
		" WHERE mk_flg='0' AND publishing_id='0' ORDER BY disp_sort";
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			foreach ($L_CSV_COLUMN as $key => $val) {
				$csv_line .= $list[$key].",";
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
 * 学力診断テスト単元マスタ　csvインポート
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array
 */
function csv_import() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
	$ERROR = array();

	$file_name = $_FILES['import_file']['name'];
	$file_tmp_name = $_FILES['import_file']['tmp_name'];
	$file_error = $_FILES['import_file']['error'];
	if (!$file_tmp_name) {
		$ERROR[] = "学力診断テスト単元マスタファイルが指定されておりません。";
	} elseif (!eregi("(.csv)$",$file_name)) {
		$ERROR[] = "ファイルの拡張子が不正です。";
	} elseif ($file_error == 1) {
		$ERROR[] = "アップロードできるファイルの容量が設定範囲を超えてます。サーバー管理者へ相談してください。";
	} elseif ($file_error == 3) {
		$ERROR[] = "学力診断テスト単元マスタファイルの一部分のみしかアップロードされませんでした。";
	} elseif ($file_error == 4) {
		$ERROR[] = "学力診断テスト単元マスタファイルがアップロードされませんでした。";
	}
	if ($ERROR) {
		if ($file_tmp_name && file_exists($file_tmp_name)) { unlink($file_tmp_name); }
		return $ERROR;
	}

	$ERROR = array();

	//登録学力診断テスト単元マスタ読込
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
		if (!$CHECK_DATA['book_id']) {
			$sql = "SELECT MAX(suffix_num) AS max_suffix FROM " . T_MS_BOOK .
				" WHERE mk_flg='0' AND publishing_id='0' AND course_num=".$CHECK_DATA['course_num']." AND gknn='".$CHECK_DATA['gknn']."';";
			if ($result = $cdb->query($sql)) {
				$list = $cdb->fetch_assoc($result);
			}
			if ($list['max_suffix']) { $CHECK_DATA['suffix_num'] = $list['max_suffix'] + 1; } else { $CHECK_DATA['suffix_num'] = 1; }
			$CHECK_DATA['book_id'] = sprintf("%03d",$CHECK_DATA['publishing_id']).sprintf("%02d",$CHECK_DATA['course_num']).$CHECK_DATA['gknn'].sprintf("%03d",$CHECK_DATA['suffix_num']);
			$ins_mode = "add";
		} else {
			$sql = "SELECT * FROM ". T_MS_BOOK .
				 " WHERE book_id='".$CHECK_DATA['book_id']."' AND mk_flg='0' LIMIT 1;";
			if ($result = $cdb->query($sql)) {
				$list = $cdb->fetch_assoc($result);
				$CHECK_DATA['suffix_num'] = $list['suffix_num'];
			}
			if ($list) { $ins_mode = "upd"; } else { $ins_mode = "add"; }
		}
		$CHECK_DATA['check_book_id'] = sprintf("%03d",$CHECK_DATA['publishing_id']).sprintf("%02d",$CHECK_DATA['course_num']).$CHECK_DATA['gknn'].sprintf("%03d",$CHECK_DATA['suffix_num']);

		//データチェック
		$DATA_ERROR[$i] = check_data($CHECK_DATA,$ins_mode,$i);
		if ($DATA_ERROR[$i]) { continue; }
		unset($CHECK_DATA['check_book_id']);

		$INSERT_DATA = $CHECK_DATA;
		$INSERT_DATA[publishing_id] 		= 0;
		//レコードがあればアップデート、無ければインサート
		if ($ins_mode == "add") {
//			$INSERT_DATA[ins_syr_id] 		= ;
			$INSERT_DATA[ins_tts_id] 		= "System";
			$INSERT_DATA[ins_date] 			= "now()";
			$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];
			$INSERT_DATA[upd_date] 			= "now()";

			$SYS_ERROR[$i] = $cdb->insert(T_MS_BOOK,$INSERT_DATA);
		} else {
//			$INSERT_DATA[upd_syr_id] 		= ;
			$INSERT_DATA[upd_tts_id] 		= "System";
			$INSERT_DATA[upd_date] 			= "now()";

			$where = " WHERE book_id='".$INSERT_DATA['book_id']."' LIMIT 1;";
			unset($INSERT_DATA['book_id']);
			$SYS_ERROR[$i] = $cdb->update(T_MS_BOOK,$INSERT_DATA,$where);
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
 * 学力診断テスト単元マスタ　csvインポートチェック
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array &$CHECK_DATA
 * @param mixed $ins_mode
 * @param integer $line_num
 * @return array エラーの場合
 */
function check_data(&$CHECK_DATA,$ins_mode,$line_num) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_GKNN_LIST;
//コース　出版社　教科書名　学年　非表示　表示順
	if (!$CHECK_DATA['book_id']) { $ERROR[] = $line_num."行目 マスタIDが未入力です。"; }
	else {
		if ($CHECK_DATA['book_id'] != $CHECK_DATA['check_book_id']) {
			$ERROR[] = $line_num."行目 マスタIDが不正です。";
		}
	}
	if (!$CHECK_DATA['course_num']) { $ERROR[] = $line_num."行目 コース番号が未入力です。"; }
	else {
		if (preg_match("/[^0-9]/",$CHECK_DATA['course_num'])) {
			$ERROR[] = $line_num."行目 コース番号は数字以外の指定はできません。";
		} else {
			$sql  = "SELECT COUNT(*) AS course_count  FROM " . T_COURSE . " WHERE state='0' AND course_num='".$CHECK_DATA['course_num']."'";
			if ($result = $cdb->query($sql)) {
				$list = $cdb->fetch_assoc($result);
			}
			if (!$list['course_count']) { $ERROR[] = $line_num."行目 入力されたコース番号は登録されていません。"; }
		}
	}
	if (!$CHECK_DATA['book_name']) { $ERROR[] = $line_num."行目 マスタ名が未入力です。"; }
	else {
		if (ins_mode == "add") {
			$sql  = "SELECT COUNT(*) AS book_count FROM " . T_MS_BOOK . " WHERE mk_flg='0'".
				" AND publishing_id='0' AND course_num='".$CHECK_DATA['course_num']."'".
				" AND gknn='".$CHECK_DATA['gknn']."' AND book_name='".$CHECK_DATA['book_name']."'";
		} else {
			$sql  = "SELECT COUNT(*) AS book_count FROM " . T_MS_BOOK . " WHERE mk_flg='0'".
				" AND publishing_id!='0' AND course_num='".$CHECK_DATA['course_num']."'".
				" AND gknn='".$CHECK_DATA['gknn']."' AND book_id!='".$CHECK_DATA['book_id']."' AND book_name='".$CHECK_DATA['book_name']."'";
		}
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
		}
		if ($list['book_count']) { $ERROR[] = $line_num."行目 入力されたマスタ名は既に登録されております。"; }
	}
	if (!$CHECK_DATA['gknn']) { $ERROR[] = $line_num."行目 学年が未入力です。"; }
	else {
		if (array_search($CHECK_DATA['gknn'],array_keys($L_GKNN_LIST)) === FALSE) {
			$ERROR[] = $line_num."行目 学年の入力値が不正です。";
		}
	}
	if (!$CHECK_DATA['display']) { $ERROR[] = $line_num."行目 表示・非表示が未入力です。"; }
	else {
		if (preg_match("/[^0-9]/",$CHECK_DATA['display'])) {
			$ERROR[] = $line_num."行目 表示・非表示は数字以外の指定はできません。";
		} elseif ($CHECK_DATA['display'] < 1 || $CHECK_DATA['display'] > 2) {
			$ERROR[] = $line_num."行目 表示・非表示は1（表示）か2（非表示）の数字以外の指定はできません。";
		}
	}
/*
	if (!$CHECK_DATA['disp_sort']) { $ERROR[] = $line_num."行目 表示順が未入力です。"; }
	else {
		if (preg_match("/[^0-9]/",$CHECK_DATA['disp_sort'])) {
			$ERROR[] = $line_num."行目 表示順は数字以外の指定はできません。";
		} elseif ($CHECK_DATA['disp_sort'] < 1) {
			$ERROR[] = $line_num."行目 表示順は1以下の数字の指定はできません。";
		}
	}
*/
	$CHECK_DATA['disp_sort'] = $line_num;

	if ($ERROR) { $ERROR[] = $line_num."行目 上記入力エラーでスキップしました。<br>"; }
	return $ERROR;
}

// update start 2018/05/14 yoshizawa 理科社会対応
// adminでここだけマスタ参照していたので他のcourse_listを同じように配列参照とする。
/**
 * コース一覧を作る機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array
 */
// function course_list() {
// 
// 	// 分散DB接続オブジェクト
// 	$cdb = $GLOBALS['cdb'];
// 
// 	$L_COURSE_LIST = array();
// 	$L_COURSE_LIST[] = "選択して下さい";
// 	$sql  = "SELECT * FROM ".T_COURSE. " WHERE state='0' AND (course_num = '1' || course_num = '2' || course_num = '3' ) ORDER BY list_num;";	// update oda 2017/10/26 Mac対応 コース情報は英語/国語/数学のみとします
// 	if ($result = $cdb->query($sql)) {
// 		while ($list=$cdb->fetch_assoc($result)) {
// 			$L_COURSE_LIST[$list['course_num']] = $list['course_name'];
// 		}
// 	}
// 	return $L_COURSE_LIST;
// }
function course_list() {

	global $L_WRITE_TYPE;
	// upd start hirose 2020/09/11 テスト標準化開発
	// $l_write_type = $L_WRITE_TYPE + get_overseas_course_name();//add hirose 2020/08/26 テスト標準化開発
	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
	
	$test_type4 = new TestStdCfgType4($cdb);
	$l_write_type = $test_type4->getTestUseCourseAdmin();
	// upd end hirose 2020/09/11 テスト標準化開発

	$L_COURSE_LIST = array();
	$L_COURSE_LIST[] = "選択して下さい";
	//upd start hirose 2020/08/26 テスト標準化開発
	// foreach ($L_WRITE_TYPE AS $course_num => $course_name) {
	foreach ($l_write_type AS $course_num => $course_name) {
	//upd end hirose 2020/08/26 テスト標準化開発
		if ($course_name == "") {
			continue;
		}
		$L_COURSE_LIST[$course_num] = $course_name;
	}
	return $L_COURSE_LIST;
}
// update end 2018/05/14 yoshizawa


?>
