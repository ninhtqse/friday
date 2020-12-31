<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * テスト用プラクティス管理　数学検定情報管理
 *
 * 履歴
 * 2020/09/01 初期設定
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

	if (ACTION == "check") { $ERROR = check(); }
	if (!$ERROR) {
		if (ACTION == "add") { $ERROR = add(); }
		elseif (ACTION == "change") { $ERROR = change(); }
		elseif (ACTION == "del") { $ERROR = change(); }
		elseif (ACTION == "↑") { $ERROR = up(); }
		elseif (ACTION == "↓") { $ERROR = down(); }
		elseif (ACTION == "sub_session") { $ERROR = sub_session(); }
	}

	if (MODE == "add") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= addform($ERROR); }
		} elseif (ACTION == "add") {
			if (!$ERROR) { $html .= math_test_list($ERROR); }
			else { $html .= addform($ERROR); }
		} else {
			$html .= addform($ERROR);
		}
	} elseif (MODE == "詳細") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= viewform($ERROR); }
		} elseif (ACTION == "change") {
			if (!$ERROR) { $html .= math_test_list($ERROR); }
			else { $html .= viewform($ERROR); }
		} else {
			$html .= viewform($ERROR);
		}
	} elseif (MODE == "削除") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= viewform($ERROR); }
		} elseif (ACTION == "change") {
			if (!$ERROR) { $html .= math_test_list($ERROR); }
			else { $html .= viewform($ERROR); }
		} else {
			$html .= check_html();
		}
	} else {
		$html .= math_test_list($ERROR);
	}

	return $html;
}


/**
 * 数学検定情報管理　絞り込みメニュー
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function select_menu() {

	global $L_DESC,$L_PAGE_VIEW;

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
 * 数学検定情報管理　絞り込みメニューセッション操作
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function sub_session() {
	if (strlen($_POST['s_class'])) { $_SESSION['sub_session']['s_class'] = $_POST['s_class']; }
	if (strlen($_POST['s_desc'])) { $_SESSION['sub_session']['s_desc'] = $_POST['s_desc']; }
	if (strlen($_POST['s_page_view'])) { $_SESSION['sub_session']['s_page_view'] = $_POST['s_page_view']; }
	if (strlen($_POST['s_desc'])&&strlen($_POST['s_page_view'])) { unset($_SESSION['sub_session']['s_page']); }
	if (strlen($_POST['s_page'])) { $_SESSION['sub_session']['s_page'] = $_POST['s_page']; }

	return;
}


/**
 * 数学検定情報管理一覧
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function math_test_list($ERROR) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_PAGE_VIEW,$L_DISPLAY;

	if ($authority) { $L_AUTHORITY = explode("::",$authority); }

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= "<br>\n";
	if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__add",$_SESSION['authority'])===FALSE)) {
		$html .= "<br>\n";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"add\">\n";
		$html .= "<input type=\"submit\" value=\"数学検定情報新規登録\">\n";
		$html .= "</form>\n";
	}
	$html .= select_menu();

	$sql  = "SELECT * FROM " . T_MATH_TEST_BOOK_INFO . " ms" .
			" WHERE ms.mk_flg='0'";
	if ($result = $cdb->query($sql)) {
		$test_group_count = $cdb->num_rows($result);
	}
	if (!$test_group_count) {
		$html .= "<br>\n";
		$html .= "今現在登録されている数学検定情報は有りません。<br>\n";
		return $html;
	}

	if ($_SESSION['sub_session']['s_desc']) {
		$sort_key = " DESC";
	} else {
		$sort_key = " ASC";
	}
	$orderby = "ORDER BY ms.disp_sort ".$sort_key;
	if ($L_PAGE_VIEW[$_SESSION['sub_session']['s_page_view']]) { $page_view = $L_PAGE_VIEW[$_SESSION['sub_session']['s_page_view']]; }
	else { $page_view = $L_PAGE_VIEW[0]; }
	$max_page = ceil($test_group_count/$page_view);
	if ($_SESSION['sub_session']['s_page']) { $page = $_SESSION['sub_session']['s_page']; }
	else { $page = 1; }
	$start = ($page - 1) * $page_view;
	$next = $page + 1;
	$back = $page - 1;

	$sql .= $orderby." LIMIT ".$start.",".$page_view.";";
	// print $sql;

	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);

		$html .= "<br>\n";
		$html .= "修正する場合は、修正する数学検定情報の詳細ボタンを押してください。<br>\n";
		$html .= "<div style=\"float:left;\">登録マスタ総数(".$test_group_count."):PAGE[".$page."/".$max_page."]</div>\n";
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
		$html .= "<th>テスト種類ID</th>\n";
		$html .= "<th>テスト種類名</th>\n";
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
			if (!$_SESSION['sub_session']['s_desc']) {
				if ($i != 1 || $page != 1) { $up_submit = "<input type=\"submit\" name=\"action\" value=\"↑\">\n"; }
				if ($i != $max || $page != $max_page) { $down_submit = "<input type=\"submit\" name=\"action\" value=\"↓\">\n"; }
			}
			$html .= "<tr class=\"course_form_cell\">\n";
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"class_id\" value=\"".$list['class_id']."\">\n";
			if (!ereg("practice__view",$authority)
				&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__sort",$_SESSION['authority'])===FALSE))
			) {
				$html .= "<td>".$up_submit."</td>\n";
				$html .= "<td>".$down_submit."</td>\n";
			}
			$html .= "<td>".$list['class_id']."</td>\n";
			$html .= "<td>".$list['class_name']."</td>\n";
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
 * 数学検定情報管理　新規登録フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function addform($ERROR) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_DISPLAY;

	$test_type5 = new TestStdCfgType5($cdb);
	$l_write_type = $test_type5->getTestUseCourseAdmin();

	$INPUTS = array();

	$course_num = $_POST['course_num'];
	$course_html = "<select name=\"course_num\">";
	$course_html .= "<option value=\"0\">選択して下さい</option>\n";
	foreach ($l_write_type as $key => $val) {
		if ($val == "") {
			continue;
		}
		if ($course_num == $key) { $selected = "selected"; } else { $selected = ""; }
		$course_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
	}
	$course_html .= "</select>\n";



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
	$make_html->set_file(MATH_TEST_INFO_FORM);

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


	//$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	$INPUTS['CLASSID'] 		= array('type'=>'text','name'=>'class_id','size'=>'20','value'=>$_POST['class_id']);
	$INPUTS['COURSESELECT'] 		= array('result'=>'plane','value'=>$course_html);
	$INPUTS['CLASSNAME']		= array('type'=>'text','name'=>'class_name','size'=>'50','value'=>$_POST['class_name']);
	$INPUTS['LIMITTIME']		= array('type'=>'text','name'=>'limit_time','size'=>'20','value'=>$_POST['limit_time']);
	$INPUTS['DISPLAY'] 		= array('result'=>'plane','value'=>$display_html);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"追加確認\">\n";
	$html .= "<input type=\"reset\" value=\"クリア\">\n";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"math_list\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";
	return $html;
}


/**
 * 数学検定情報管理　修正フォーム
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

	global $L_DISPLAY;

	$test_type5 = new TestStdCfgType5($cdb);
	$l_write_type = $test_type5->getTestUseCourseAdmin();

	$INPUTS = array();

	if (ACTION) {
		foreach ($_POST as $key => $val) {
			$$key = $val;
		}
	} else {
		$sql = "SELECT * FROM " . T_MATH_TEST_BOOK_INFO .
				" WHERE class_id='".$_POST['class_id']."'".
				" AND mk_flg='0' LIMIT 1;";

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


	$html = "<br>\n";
	$html .= "詳細画面<br>\n";

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$course_html = "<select name=\"course_num\">";
	$course_html .= "<option value=\"0\">選択して下さい</option>\n";
	foreach ($l_write_type as $key => $val) {
		if ($val == "") {
			continue;
		}
		if ($course_num == $key) { $selected = "selected"; } else { $selected = ""; }
		$course_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
	}
	$course_html .= "</select>\n";

	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";
	$html .= "<input type=\"hidden\" name=\"class_id\" value=\"".$class_id."\">\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(MATH_TEST_INFO_FORM);

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
	$INPUTS['CLASSID'] 		= array('result'=>'plane','value'=>$class_id);
	$INPUTS['COURSESELECT'] 		= array('result'=>'plane','value'=>$course_html);
	$INPUTS['CLASSNAME']		= array('type'=>'text','name'=>'class_name','size'=>'50','value'=>$class_name);
	$INPUTS['LIMITTIME']		= array('type'=>'text','name'=>'limit_time','size'=>'20','value'=>$limit_time);
	$INPUTS['DISPLAY'] 		= array('result'=>'plane','value'=>$display_html);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"変更確認\">";
	$html .= "<input type=\"reset\" value=\"クリア\">";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"math_list\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}


/**
 * 数学検定情報管理　新規登録・修正　必須項目チェック
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function check() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
	$ERROR = array();

	if (!$_POST['class_id']) {
		$ERROR[] = "classIDが未入力です。";
	} elseif(!preg_match('/^[a-zA-Z0-9]+$/',$_POST['class_id'])){
		$ERROR[] = "classIDは半角英数字で記入してください";
	} elseif (strlen($_POST['class_id']) > 10) {
		$ERROR[] = "classIDが長すぎます。半角10文字以内で記述して下さい。";
	} else {
		if (MODE == "add") {
			$sql  = "SELECT * FROM " . T_MATH_TEST_BOOK_INFO ." ms ".
					" WHERE ms.mk_flg='0'".
					" AND class_id='".$_POST['class_id']."'";
			$count = 0;
			if ($result = $cdb->query($sql)) {
				$count = $cdb->num_rows($result);
			}
			if ($count > 0) { $ERROR[] = "入力されたclass IDは既に登録されております。"; }
		}
	}

	if (!$_POST['class_name']) {
		$ERROR[] = "テスト種類名が未入力です。";
	} elseif (mb_strlen($_POST['class_name'], 'UTF-8')>255) {
		$ERROR[] = "テスト種類名が長すぎます。255文字以内で記述して下さい。";
	}

	if (!$_POST['course_num']) {
		$ERROR[] = "コースが未選択です。";
	}

	if ($_POST['limit_time'] === "") {
		$ERROR[] = "制限時間が未入力です。";
	} elseif(!preg_match('/^[0-9]+$/',$_POST['limit_time'])){
		$ERROR[] = "制限時間は半角数字で記入してください";
	} elseif($_POST['limit_time']<1 || $_POST['limit_time']>90){
		$ERROR[] = "制限時間は1分以上90分以下で記入してください";
	}

	if (!$_POST['display']) {
		$ERROR[] = "表示・非表示が未選択です。";
	}

	return $ERROR;
}


/**
 * 数学検定情報管理　新規登録・修正・削除　確認フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function check_html() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_DISPLAY;

	$test_type5 = new TestStdCfgType5($cdb);
	$l_write_type = $test_type5->getTestUseCourseAdmin();

	$INPUTS = array();

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
		$sql = "SELECT * FROM " . T_MATH_TEST_BOOK_INFO .
 				" WHERE class_id='".$_POST['class_id']."'".
				" AND mk_flg='0' LIMIT 1;";
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


	if (MODE != "削除") { $button = "登録"; } else { $button = "削除"; }
	$html = "<br>\n";
	$html .= "確認画面：以下の内容で".$button."してもよろしければ".$button."ボタンをクリックしてください。<br>\n";


	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(MATH_TEST_INFO_FORM);

	//$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	$INPUTS['CLASSID'] 		= array('result'=>'plane','value'=>$class_id);
	$INPUTS['COURSESELECT'] 		= array('result'=>'plane','value'=>$l_write_type[$course_num]);
	$INPUTS['CLASSNAME']		= array('result'=>'plane','value'=>$class_name);
	$INPUTS['LIMITTIME']		= array('result'=>'plane','value'=>$limit_time);
	$INPUTS['DISPLAY'] 		= array('result'=>'plane','value'=>$L_DISPLAY[$display]);

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
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"math_list\">\n";
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

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];


	$sql  = "SELECT max(disp_sort) as disp_sort FROM " . T_MATH_TEST_BOOK_INFO . " ms" .
			" WHERE ms.mk_flg='0'";
	$disp_sort=0;
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$disp_sort = $list['disp_sort'];
	}
	$disp_sort++;

	$sql  = "SELECT * FROM " . T_MATH_TEST_BOOK_INFO . " ms" .
			" WHERE ms.class_id = '".$_POST['class_id']."'";
	$check_row=0;
	if ($result = $cdb->query($sql)) {
		$check_row = $cdb->num_rows($result);
	}

	//class_idがDB上でプライマリーになっているため、すでに削除されているクラスIDと同じIDが来た場合はアップデート
	if($check_row){

		$INSERT_DATA['class_name'] 	= $_POST['class_name'];
		$INSERT_DATA['course_num']		 	= $_POST['course_num'];
		$INSERT_DATA['disp_sort'] 		= $disp_sort;
		$INSERT_DATA['limit_time']		 	= $_POST['limit_time'];
		$INSERT_DATA['display'] 			= $_POST['display'];
		$INSERT_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date'] 			= "now()";

		$INSERT_DATA['mk_flg'] 		= 0;
		$INSERT_DATA['mk_tts_id'] 		= NULL;
		$INSERT_DATA['mk_date'] 			= NULL;

		$where = " WHERE class_id='".$_POST['class_id']."' LIMIT 1;";
		$ERROR = $cdb->update(T_MATH_TEST_BOOK_INFO,$INSERT_DATA,$where);

	}else{
		$INSERT_DATA['class_id'] 		= $_POST['class_id'];
		$INSERT_DATA['class_name'] 	= $_POST['class_name'];
		$INSERT_DATA['course_num']		 	= $_POST['course_num'];
		$INSERT_DATA['disp_sort'] 		= $disp_sort;
		$INSERT_DATA['limit_time']		 	= $_POST['limit_time'];
		$INSERT_DATA['display'] 			= $_POST['display'];
		$INSERT_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date'] 			= "now()";
		$INSERT_DATA['ins_tts_id'] 		= $_SESSION['myid']['id'];
		$INSERT_DATA['ins_date'] 			= "now()";

		$ERROR = $cdb->insert(T_MATH_TEST_BOOK_INFO,$INSERT_DATA);
	}


	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }

	return $ERROR;
}


/**
 * DB更新・削除 処理 グループ　修正・削除処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function change() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$INSERT_DATA = array();

	if (MODE == "詳細") {
		$INSERT_DATA['class_name']    = $_POST['class_name'];
		$INSERT_DATA['course_num']    = $_POST['course_num'];
		$INSERT_DATA['limit_time']    = $_POST['limit_time'];
		// $INSERT_DATA['disp_sort ']    = $_POST['disp_sort ']; // del simon 2020-09-28 テスト標準化
		$INSERT_DATA['display']       = $_POST['display'];
		$INSERT_DATA['upd_tts_id']    = $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date']      = "now()";
	} elseif (MODE == "削除") {
		$INSERT_DATA['mk_flg']        = 1;
		$INSERT_DATA['mk_tts_id']     = $_SESSION['myid']['id'];
		$INSERT_DATA['mk_date']       = "now()";
	}
	$where = " WHERE class_id='".$_POST['class_id']."' LIMIT 1;";
	$ERROR = $cdb->update(T_MATH_TEST_BOOK_INFO,$INSERT_DATA,$where);

	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}

/**
 * T_MATH_TEST_BOOK_INFOを上がる機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function up() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$INSERT_DATA = array();

	// 選択した対象の情報取得
	$sql  = "SELECT * FROM " . T_MATH_TEST_BOOK_INFO .
 			" WHERE class_id='".$_POST['class_id']."'".
			" LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$m_class_id = $list['class_id'];
		$m_disp_sort = $list['disp_sort'];
	}
	if (!$m_class_id || !$m_disp_sort) { $ERROR[] = "移動する数学検定情報が取得できません。"; }

	// 変更対象の情報取得
	if (!$ERROR) {
		$sql  = "SELECT * FROM " . T_MATH_TEST_BOOK_INFO .
				 " WHERE mk_flg='0'".
				 " AND disp_sort<'".$m_disp_sort."'".
				 " ORDER BY disp_sort DESC LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$c_class_id = $list['class_id'];
			$c_disp_sort = $list['disp_sort'];
		}
	}
	if (!$c_class_id || !$c_disp_sort) { $ERROR[] = "移動される数学検定情報が取得できません。"; }

	if (!$ERROR) {
		$INSERT_DATA['disp_sort'] 	= $c_disp_sort;
		$INSERT_DATA['upd_tts_id'] 	= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date'] 		= "now()";
		$where = " WHERE class_id='".$m_class_id."' LIMIT 1;";
		$ERROR = $cdb->update(T_MATH_TEST_BOOK_INFO,$INSERT_DATA,$where);
	}

	if (!$ERROR) {
		$INSERT_DATA['disp_sort'] 	= $m_disp_sort;
		$INSERT_DATA['upd_tts_id'] 	= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date'] 		= "now()";
		$where = " WHERE class_id='".$c_class_id."' LIMIT 1;";
		$ERROR = $cdb->update(T_MATH_TEST_BOOK_INFO,$INSERT_DATA,$where);
	}

	return $ERROR;
}

/**
 * T_MATH_TEST_BOOK_INFOを下がる機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function down() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$INSERT_DATA = array();

	$sql  = "SELECT * FROM " . T_MATH_TEST_BOOK_INFO .
 			 " WHERE class_id='".$_POST['class_id']."'".
			 " LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$m_class_id = $list['class_id'];
		$m_disp_sort = $list['disp_sort'];
	}
	if (!$m_class_id || !$m_disp_sort) { $ERROR[] = "移動する数学検定情報が取得できません。"; }

	if (!$ERROR) {
		$sql  = "SELECT * FROM " . T_MATH_TEST_BOOK_INFO .
 				 " WHERE mk_flg='0'".
				 " AND disp_sort>'".$m_disp_sort."'".
				 " ORDER BY disp_sort LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$c_class_id = $list['class_id'];
			$c_disp_sort = $list['disp_sort'];
		}
	}
	if (!$c_class_id || !$c_disp_sort) { $ERROR[] = "移動される数学検定情報が取得できません。"; }

	if (!$ERROR) {
		$INSERT_DATA['disp_sort'] = $c_disp_sort;
		$INSERT_DATA['upd_tts_id'] 	= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date'] 		= "now()";
		$where = " WHERE class_id='".$m_class_id."' LIMIT 1;";
		$ERROR = $cdb->update(T_MATH_TEST_BOOK_INFO,$INSERT_DATA,$where);
	}

	if (!$ERROR) {
		$INSERT_DATA['disp_sort'] = $m_disp_sort;
		$INSERT_DATA['upd_tts_id'] 	= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date'] 		= "now()";
		$where = " WHERE class_id='".$c_class_id."' LIMIT 1;";
		$ERROR = $cdb->update(T_MATH_TEST_BOOK_INFO,$INSERT_DATA,$where);
	}

	return $ERROR;
}




