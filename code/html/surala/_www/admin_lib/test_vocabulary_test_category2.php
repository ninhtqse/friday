<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * テスト用プラクティス管理　すらら英単語種別２
 *
 *
 * 履歴
 * 2018/09/28 初期設定
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
		elseif (ACTION == "export") { $ERROR = csv_export(); }
		elseif (ACTION == "import") { list($html,$ERROR) = csv_import(); }
	}

	if (MODE == "add") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= addform($ERROR); }
		} elseif (ACTION == "add") {
			if (!$ERROR) { $html .= category_list($ERROR); }
			else { $html .= addform($ERROR); }
		} else {
			$html .= addform($ERROR);
		}
	} elseif (MODE == "詳細") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= viewform($ERROR); }
		} elseif (ACTION == "change") {
			if (!$ERROR) { $html .= category_list($ERROR); }
			else { $html .= viewform($ERROR); }
		} else {
			$html .= viewform($ERROR);
		}
	} elseif (MODE == "削除") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= viewform($ERROR); }
		} elseif (ACTION == "change") {
			if (!$ERROR) { $html .= category_list($ERROR); }
			else { $html .= viewform($ERROR); }
		} else {
			$html .= check_html();
		}
	} else {
		$html .= category_list($ERROR);
	}

	return $html;
}


/**
 * テスト種別２　絞り込みメニュー
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function select_menu() {
	
	//タイプリスト
	$L_TYPE_LIST = type_list();
	
	$L_CATEGORY1_LIST = category1_list();
	
	$type_html = "";
	foreach ($L_TYPE_LIST AS $test_type_num => $test_type_name) {
		if ($test_type_name == "") {
			continue;
		}
		$selected = "";
		if ($_SESSION['sub_session']['test_type_num'] == $test_type_num) {
			$selected = "selected";
		}
		$type_html .= "<option value=\"".$test_type_num."\" ".$selected.">".$test_type_name."</option>\n";
	}

	$category1_html = "";
	foreach ($L_CATEGORY1_LIST AS $test_category1_num => $test_category1_name) {
		if ($test_category1_name == "") {
			continue;
		}
		$selected = "";
		if ($_SESSION['sub_session']['test_category1_num'] == $test_category1_num) {
			$selected = "selected";
		}
		$category1_html .= "<option value=\"".$test_category1_num."\" ".$selected.">".$test_category1_name."</option>\n";
	}
	
	$sub_session_html = "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$sub_session_html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
	$sub_session_html .= "<td>\n";
	$sub_session_html .= "テスト種類 <select name=\"test_type_num\" onchange = \"submit();\">\n".$type_html."</select>\n";

	$sub_session_html .= "テスト種別１ <select name=\"test_category1_num\" onchange = \"submit();\">\n".$category1_html."</select>\n";

	//$sub_session_html .= "<input type=\"submit\" value=\"Set\">\n";
	$sub_session_html .= "</td>\n";
	$sub_session_html .= "</form>\n";

	$html .= "<br><div id=\"mode_menu\">\n";
	$html .= "<table cellpadding=0 cellspacing=0>\n";
	$html .= "<tr>\n";
	$html .= $sub_session_html;
	$html .= "</tr>\n";
	$html .= "</table>\n";
	$html .= "</div>\n";

	if (!$_SESSION['sub_session']['test_type_num'] ) {
		$html .= "<br>\n";
		$html .= "テスト種別２を設定するすらら英単語種類を選択してください。<br>\n";
	}
	if ($_SESSION['sub_session']['test_type_num'] && !$_SESSION['sub_session']['test_category1_num'] ) {
		$html .= "<br>\n";
		$html .= "テスト種別２を設定するテスト種別１を選択してください。<br>\n";
	}

	return $html;
}

/**
 * SESSION情報設定
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * テスト種別２　絞り込みメニューセッション操作
 * @author Azet
 */
function sub_session() {
	if (strlen($_POST['test_type_num'])) { $_SESSION['sub_session']['test_type_num'] = $_POST['test_type_num']; }
	if (strlen($_POST['test_category1_num'])) { $_SESSION['sub_session']['test_category1_num'] = $_POST['test_category1_num']; }
	if (strlen($_POST['s_page'])) { $_SESSION['sub_session']['s_page'] = $_POST['s_page']; }

	return;
}


/**
 * テスト種別２一覧
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function category_list($ERROR) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_DISPLAY,$L_EXP_CHA_CODE;

	$L_TYPE_LIST = type_list();
	
	$L_CATEGORY1_LIST = category1_list();

	define ( 'VIEW_LIST_ROW' , 20 );
	
	if ($authority) { $L_AUTHORITY = explode("::",$authority); }

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}
	$html .= select_menu();	
	
	// すらら英単語種類が未選択の場合は一覧を表示しない
	if (!$_SESSION['sub_session']['test_type_num']) {
		return $html;
	}
	// テスト種別１が未選択の場合は一覧を表示しない
	if (!$_SESSION['sub_session']['test_category1_num']) {
		return $html;
	}

	
	$html .= "<br>\n";
	$html .= "インポートする場合は、テスト種別２csvファイル（S-JIS）を指定しCSVインポートボタンを押してください。<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" enctype=\"multipart/form-data\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"import\">\n";
	$html .= "<input type=\"file\" size=\"40\" name=\"import_file\"><br>\n";
	$html .= "<input type=\"submit\" value=\"CSVインポート\" style=\"float:left;\">\n";
	$html .= "</form>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"export\">\n";

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
		$html .= "<input type=\"hidden\" name=\"test_type_num\" value=\"".$_SESSION['sub_session']['test_type_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"test_category1_num\" value=\"".$_SESSION['sub_session']['test_category1_num']."\">\n";
		$html .= "<input type=\"submit\" value=\"テスト種別２新規登録\">\n";
		$html .= "</form>\n";
	}

	if ($_SESSION['sub_session']['test_type_num']) {
//		$where .= " AND test_type_num='".$_SESSION['sub_session']['test_type_num']."'";
		$where .= " AND tc2.test_type_num='".$_SESSION['sub_session']['test_type_num']."'"; //upd hirose 2018/11/26 すらら英単語　ディレクトリ作成
	}
	
	if ($_SESSION['sub_session']['test_category1_num']) {
//		$where .= " AND test_category1_num='".$_SESSION['sub_session']['test_category1_num']."'";
		$where .= " AND tc2.test_category1_num='".$_SESSION['sub_session']['test_category1_num']."'"; //upd hirose 2018/11/26 すらら英単語　ディレクトリ作成
	}

	//upd start hirose 2018/11/26 すらら英単語　ディレクトリ作成
//	$sql  = "SELECT * FROM " . T_MS_TEST_CATEGORY2 . 
//			" WHERE mk_flg='0' ".$where;
	$sql  = "SELECT tc2.*,ttn.write_type FROM " . T_MS_TEST_CATEGORY2 ." tc2" .
			" INNER JOIN ".T_MS_TEST_CATEGORY1." tc1 ON tc1.test_type_num = tc2.test_type_num AND tc1.test_category1_num =tc2.test_category1_num".
			" INNER JOIN ".T_MS_TEST_TYPE." ttn ON tc2.test_type_num = ttn.test_type_num".
			" WHERE tc2.mk_flg='0' ".$where; 
//	print $sql;
	//upd end hirose 2018/11/26 すらら英単語　ディレクトリ作成
	if ($result = $cdb->query($sql)) {
		$test_count = $cdb->num_rows($result);
	}

	if (!$test_count) {
		$html .= "<br>\n";
		$html .= "今現在登録されているテスト種別２は有りません。<br>\n";
		return $html;
	}

//	$orderby = " ORDER BY list_num ";
	$orderby = " ORDER BY tc2.list_num "; //upd hirose 2018/11/26 すらら英単語　ディレクトリ作成

	$page_view = VIEW_LIST_ROW;
	$max_page = ceil($test_count/$page_view);
	if ($_SESSION['sub_session']['s_page']) { $page = $_SESSION['sub_session']['s_page']; }
	else { $page = 1; }
	$start = ($page - 1) * $page_view;
	$next = $page + 1;
	$back = $page - 1;

	$sql .= $orderby." LIMIT ".$start.",".$page_view.";";

	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);

		$html .= "<br>\n";
		$html .= "修正する場合は、修正するテスト種別２の詳細ボタンを押してください。<br>\n";
		$html .= "<div style=\"float:left;\">登録テスト種別２総数(".$test_count."):PAGE[".$page."/".$max_page."]</div>\n";
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
		$html .= "<th>テスト種類名</th>\n";
		$html .= "<th>テスト種別１名</th>\n";
		$html .= "<th>テスト種別２ID</th>\n";
		$html .= "<th>テスト種別２名</th>\n";
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
		
			//add start hirose 2018/11/26 すらら英単語　ディレクトリ作成
			$temp_path = STUDENT_VOCABLARY_TEMP_DIR . $list['write_type'] . "/". $list['test_type_num'] . "/". $list['test_category1_num'] . "/". $list['test_category2_num'] . "/";
			
			if (!file_exists($temp_path)) {
					@mkdir($temp_path, 0777);
					@chmod($temp_path, 0777);
				}
			$material_temp_path = MATERIAL_TEMP_DIR . "vocabulary/". $list['write_type'] ."/". $list['test_type_num'] . "/". $list['test_category1_num'] . "/";
				if (!file_exists($material_temp_path)) {
					@mkdir($material_temp_path,0777);
					@chmod($material_temp_path,0777);
				}
			$material_temp_path2 = MATERIAL_TEMP_DIR . "vocabulary/". $list['write_type'] ."/". $list['test_type_num'] . "/". $list['test_category1_num'] . "/". $list['test_category2_num'] . "/";
				if (!file_exists($material_temp_path2)) {
					@mkdir($material_temp_path2,0777);
					@chmod($material_temp_path2,0777);
				}
			$button_path = "../student/images/button/vocabulary/". $list['write_type'] ."/". $list['test_type_num'] . "/". $list['test_category1_num'] . "/";
			if (!file_exists($button_path)) {
					@mkdir($button_path,0777);
					@chmod($button_path,0777);
				}
			$button_path2 = "../student/images/button/vocabulary/". $list['write_type'] ."/". $list['test_type_num'] . "/". $list['test_category1_num'] . "/". $list['test_category2_num'] . "/";
			if (!file_exists($button_path2)) {
					@mkdir($button_path2,0777);
					@chmod($button_path2,0777);
				}
			//add end hirose 2018/11/26 すらら英単語　ディレクトリ作成
		
			$up_submit = $down_submit = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			if ($i != 1 || $page != 1) { $up_submit = "<input type=\"submit\" name=\"action\" value=\"↑\">\n"; }
			if ($i != $max || $page != $max_page) { $down_submit = "<input type=\"submit\" name=\"action\" value=\"↓\">\n"; }

			$html .= "<tr class=\"course_form_cell\">\n";
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"test_category2_num\" value=\"".$list['test_category2_num']."\">\n";
			$html .= "<input type=\"hidden\" name=\"test_type_num\" value=\"".$_SESSION['sub_session']['test_type_num']."\">\n";
			$html .= "<input type=\"hidden\" name=\"test_category1_num\" value=\"".$_SESSION['sub_session']['test_category1_num']."\">\n";
			if (!ereg("practice__view",$authority)
				&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__sort",$_SESSION['authority'])===FALSE))
			) {
				$html .= "<td>".$up_submit."</td>\n";
				$html .= "<td>".$down_submit."</td>\n";
			}
			$html .= "<td>".$L_TYPE_LIST[$list['test_type_num']]."</td>\n";
			$html .= "<td>".$L_CATEGORY1_LIST[$list['test_category1_num']]."</td>\n";
			$html .= "<td>".$list['test_category2_num']."</td>\n";
			$html .= "<td>".$list['test_category2_name']."</td>\n";
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
 * テスト種別２　新規登録フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function addform($ERROR) {

	global $L_DISPLAY;

	//タイプリスト
	$L_TYPE_LIST = type_list();
	$L_CATEGORY1_LIST = category1_list();

	$html .= "<br>\n";
	$html .= "新規登録フォーム<br>\n";

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";
	$html .= "<input type=\"hidden\" name=\"test_type_num\" value=\"".$_POST['test_type_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"test_category1_num\" value=\"".$_POST['test_category1_num']."\">\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(TANGO_TEST_CATEGORY2_FORM);

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
	$INPUTS['TESTTYPENAME'] 	= array('result'=>'plane','value'=> $L_TYPE_LIST[$_POST['test_type_num']]);
	$INPUTS['CATEGORY1NAME'] 	= array('result'=>'plane','value'=> $L_CATEGORY1_LIST[$_POST['test_category1_num']]);
	$INPUTS['CATEGORY2ID'] 		= array('result'=>'plane','value'=>"---");
	$INPUTS['CATEGORY2NAME'] 		= array('type'=>'text','name'=>'test_category2_name','size'=>'50','value'=>$_POST['test_category2_name']);
	$INPUTS['PROBLEMCOUNT'] 		= array('type'=>'text','name'=>'test_problem_count','size'=>'50','value'=>$_POST['test_problem_count']);
	$INPUTS['LIMITTIME'] 		= array('type'=>'text','name'=>'test_limit_time','size'=>'50','value'=>$_POST['test_limit_time']);
	$INPUTS['SWITCHINGTIME'] = array('type'=>'text','name'=>'switching_time','size'=>'6','value'=>$_POST['switching_time']); // add 2018/12/20 yoshizawa すらら英単語
	$INPUTS['REMARKS'] 		= array('result'=>'form', 'type'=>'textarea','name'=>'remarks', 'cols'=>40, 'rows'=>4, 'value'=>$_POST['remarks']); //update kimura 2018/10/31 すらら英単語 usr_bko -> remarks, text -> textarea
	$INPUTS['DISPLAY'] 		= array('result'=>'plane','value'=>$display_html);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"追加確認\">\n";
	$html .= "<input type=\"reset\" value=\"クリア\">\n";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"category_list\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;

}


/**
 * テスト種別２　修正フォーム
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

	if (ACTION) {
		foreach ($_POST as $key => $val) { $$key = $val; }
	} else {
		$sql  = "SELECT * FROM " . T_MS_TEST_CATEGORY2 . " ".
				"WHERE test_category2_num='".$_POST['test_category2_num']."' ".
				"AND test_type_num='".$_POST['test_type_num']."' ".
				"AND test_category1_num='".$_POST['test_category1_num']."' ".
				"AND mk_flg='0' ".
				"LIMIT 1;";
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

	$L_TYPE_LIST = type_list();
	$L_CATEGORY1_LIST = category1_list();

	$html = "<br>\n";
	$html .= "詳細画面<br>\n";

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";
	$html .= "<input type=\"hidden\" name=\"test_category2_num\" value=\"".$test_category2_num."\">\n";
	$html .= "<input type=\"hidden\" name=\"test_category1_num\" value=\"".$test_category1_num."\">\n";
	$html .= "<input type=\"hidden\" name=\"test_type_num\" value=\"".$test_type_num."\">\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(TANGO_TEST_CATEGORY2_FORM);

	if (!$test_category1_num) { $test_category2_num = "---"; }
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
	$INPUTS['TESTTYPENAME'] 	= array('result'=>'plane','value'=> $L_TYPE_LIST[$test_type_num]);
	$INPUTS['CATEGORY1NAME'] 	= array('result'=>'plane','value'=> $L_CATEGORY1_LIST[$test_category1_num]);
	$INPUTS['CATEGORY2ID'] 		= array('result'=>'plane','value'=>$test_category2_num);
	$INPUTS['CATEGORY2NAME'] 	= array('type'=>'text','name'=>'test_category2_name','size'=>'50','value'=>$test_category2_name);
	$INPUTS['PROBLEMCOUNT'] 	= array('type'=>'text','name'=>'test_problem_count','size'=>'50','value'=>$test_problem_count);
	$INPUTS['LIMITTIME'] 		= array('type'=>'text','name'=>'test_limit_time','size'=>'50','value'=>$test_limit_time);
	$INPUTS['SWITCHINGTIME']	= array('type'=>'text','name'=>'switching_time','size'=>'6','value'=>$switching_time); // add 2018/12/20 yoshizawa すらら英単語
	$INPUTS['REMARKS'] 			= array('result'=>'form', 'type'=>'textarea','name'=>'remarks', 'cols'=>40, 'rows'=>4, 'value'=>$remarks); //update kimura 2018/10/31 すらら英単語 usr_bko -> remarks, text -> textarea
	$INPUTS['DISPLAY'] 			= array('result'=>'plane','value'=>$display_html);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"変更確認\">";
	$html .= "<input type=\"reset\" value=\"クリア\">";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"category_list\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}


/**
 * テスト種別２　新規登録・修正　必須項目チェック
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function check() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (!$_POST['test_category2_name']) { $ERROR[] = "テスト種別２名が未入力です。"; }
	else {
		if (MODE == "add") {
			$sql  = "SELECT * FROM " . T_MS_TEST_CATEGORY2 . " WHERE mk_flg='0'".
				" AND test_type_num='".$_POST['test_type_num']."'".
				" AND test_category1_num='".$_POST['test_category1_num']."'".
				" AND test_category2_name='".$_POST['test_category2_name']."'";

		} else {
			$sql  = "SELECT * FROM " . T_MS_TEST_CATEGORY2 . " WHERE mk_flg='0'".
				" AND test_type_num='".$_POST['test_type_num']."'".
				" AND test_category1_num='".$_POST['test_category1_num']."'".
				" AND test_category2_num!='".$_POST['test_category2_num']."' AND test_category2_name='".$_POST['test_category2_name']."'";

		}
		if ($result = $cdb->query($sql)) {
			$count = $cdb->num_rows($result);
		}
		if ($count > 0) { $ERROR[] = "入力されたテスト種別２名は既に登録されております。"; }
	}
	if (!$_POST['test_problem_count']) { 
		$ERROR[] = "テスト問題出題数が未入力です。"; 
	} else if (preg_match("/[^0-9]/",$_POST['test_problem_count'])) {
		$ERROR[] = "テスト問題出題数は数字以外の指定はできません。";
	}
	
	if (!$_POST['test_limit_time']) { 
		$ERROR[] = "テスト制限時間が未入力です。";
	} else if (preg_match("/[^0-9]/",$_POST['test_limit_time'])) {
		$ERROR[] = "テスト制限時間は数字以外の指定はできません。";
	}

	// >>> add 2018/12/20 yoshizawa すらら英単語追加
	// 次問題への移行時間をチェック
	if (!$_POST['switching_time']) {
		$ERROR[] = "次問題への移行時間が未入力です。";
	} else if( !preg_match('/^([1-9]\d*|0)(\.\d+)?$/',$_POST['switching_time']) ) {
		$ERROR[] = "次問題への移行時間に不正な値が含まれています。";
	}
	// <<<

	if (!$_POST['display']) { $ERROR[] = "表示・非表示が未選択です。"; }

	// if (mb_strlen($_POST['usr_bko'], 'UTF-8') > 255) { $ERROR[] = "備考が不正です。255文字以内で記述して下さい。"; } //del kimura 2018/10/31 すらら英単語

	return $ERROR;
}


/**
 * テスト種別２　新規登録・修正・削除　確認フォーム
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

	if ($_POST) {
		foreach ($_POST as $key => $val) {
			if ($key == "action") {
				if (MODE == "add") { $val = "add"; }
				elseif (MODE == "詳細") { $val = "change"; }
			}
			//add start hirose 2018/11/20 すらら英単語 改行コード\nを<br>に変換
			if($key == "remarks"){
				$val = replace_word($val);
			}
			//add end hirose 2018/11/20 すらら英単語 改行コード\nを<br>に変換
			$HIDDEN .= "<input type=\"hidden\" name=\"".$key."\" value=\"".$val."\">\n";
		}
	}

	if (ACTION) {
		foreach ($_POST as $key => $val) { $$key = $val; }
	} else {
		$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"change\">\n";
		$sql  = "SELECT * FROM " . T_MS_TEST_CATEGORY2 . " ".
				"WHERE test_category2_num='".$_POST['test_category2_num']."' ".
				"AND test_type_num='".$_POST['test_type_num']."' ".
				"AND test_category1_num='".$_POST['test_category1_num']."' ".
				"AND mk_flg='0' ".
				"LIMIT 1;";
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

	$L_TYPE_LIST = type_list();
	$L_CATEGORY1_LIST = category1_list();

	if (MODE != "削除") { $button = "登録"; } else { $button = "削除"; }
	$html = "<br>\n";
	$html .= "確認画面：以下の内容で".$button."してもよろしければ".$button."ボタンをクリックしてください。<br>\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(TANGO_TEST_CATEGORY2_FORM);

	//add start hirose 2018/11/20 すらら英単語 改行コード\nを<br>に変換
	$remarks = replace_word($remarks);
	//add end hirose 2018/11/20 すらら英単語 改行コード\nを<br>に変換

	// $配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	if (!$test_category2_num) { $test_category2_num = "---"; }
	$INPUTS['TESTTYPENAME'] 		= array('result'=>'plane','value'=>$L_TYPE_LIST[$test_type_num]);
	$INPUTS['CATEGORY1NAME'] 		= array('result'=>'plane','value'=>$L_CATEGORY1_LIST[$test_category1_num]);
	$INPUTS['CATEGORY2ID'] 		= array('result'=>'plane','value'=>$test_category2_num);
	$INPUTS['CATEGORY2NAME'] 		= array('result'=>'plane','value'=>$test_category2_name);
	$INPUTS['PROBLEMCOUNT'] 		= array('result'=>'plane','value'=>$test_problem_count);
	$INPUTS['LIMITTIME'] 			= array('result'=>'plane','value'=>$test_limit_time);
	$INPUTS['SWITCHINGTIME'] 	= array('result'=>'plane','value'=>$switching_time); // add 2018/12/20 yoshizawa すらら英単語
	$INPUTS['REMARKS'] 			= array('result'=>'plane','value'=>$remarks); //update kimura 2018/10/31 すらら英単語 usr_bko -> remarks
	$INPUTS['DISPLAY'] 			= array('result'=>'plane','value'=>$L_DISPLAY[$display]);

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
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"category_list\">\n";
	}
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}


/**
 * テスト種別２　新規登録処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function add() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql = "SELECT MAX(test_category2_num) AS max_id FROM " . T_MS_TEST_CATEGORY2 .";";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
	}
	if ($list['max_id']) { $add_id = $list['max_id'] + 1; } else { $add_id = 1; }

	//各種類に紐づく種別１の最大disp_sort値取得
	$sql  = "SELECT MAX(list_num) AS max_sort".
			" FROM ".T_MS_TEST_CATEGORY2.
			" WHERE test_type_num = '".$_POST['test_type_num']."' ".
 			" AND test_category1_num='".$_POST['test_category1_num']."' ;";
 			if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
	}
	if ($list['max_sort']) { $disp_sort = $list['max_sort'] + 1; } else { $disp_sort = 1; }	
	
	$INSERT_DATA['test_type_num'] 	= $_POST['test_type_num'];
	$INSERT_DATA['test_category1_num'] 	= $_POST['test_category1_num'];
	$INSERT_DATA['test_category2_num'] 	= $add_id;
	$INSERT_DATA['test_category2_name'] 	= $_POST['test_category2_name'];
	$INSERT_DATA['test_problem_count'] 	= $_POST['test_problem_count'];
	$INSERT_DATA['test_limit_time'] 	= $_POST['test_limit_time'];
	$INSERT_DATA['list_num'] 		= $disp_sort;
	$INSERT_DATA['switching_time'] 		= $_POST['switching_time']; // add 2018/12/20 yoshizawa すらら英単語
	$INSERT_DATA['remarks'] 		= $_POST['remarks']; //update kimura 2018/10/31 すらら英単語 usr_bko -> remarks
	$INSERT_DATA['display'] 		= $_POST['display'];
	$INSERT_DATA['ins_tts_id'] 		= $_SESSION['myid']['id'];
	$INSERT_DATA['ins_date'] 			= "now()";
	$INSERT_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];
	$INSERT_DATA['upd_date'] 			= "now()";

	$ERROR = $cdb->insert(T_MS_TEST_CATEGORY2,$INSERT_DATA);

	if (!$ERROR) { $_SESSION['select_menu'] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}


/**
 * テスト種別２　修正・削除処理
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
		$INSERT_DATA['test_category2_name'] = $_POST['test_category2_name'];
		$INSERT_DATA['test_problem_count'] 	= $_POST['test_problem_count'];
		$INSERT_DATA['test_limit_time'] 	= $_POST['test_limit_time'];
		$INSERT_DATA['switching_time'] 		= $_POST['switching_time']; // add 2018/12/20 yoshizawa すらら英単語
		$INSERT_DATA['remarks'] 			= $_POST['remarks']; //update kimura 2018/10/31 すらら英単語 usr_bko -> remarks
		$INSERT_DATA['display'] 			= $_POST['display'];
		$INSERT_DATA['upd_tts_id'] 			= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date'] 			= "now()";
	} elseif (MODE == "削除") {
		$INSERT_DATA['mk_flg'] 				= 1;
		$INSERT_DATA['mk_tts_id'] 			= $_SESSION['myid']['id'];
		$INSERT_DATA['mk_date'] 			= "now()";
	}
	//upd start hirose 2018/11/27
//	$where = " WHERE test_category2_num='".$_POST['test_category2_num']."' LIMIT 1;";
	$where = " WHERE test_type_num = '".$_POST['test_type_num']."'"
			. " AND test_category1_num='".$_POST['test_category1_num']
			."' AND test_category2_num='".$_POST['test_category2_num']
			."' AND mk_flg = 0"
			." LIMIT 1;";
	//upd end hirose 2018/11/27
	//print $where;
	$ERROR = $cdb->update(T_MS_TEST_CATEGORY2,$INSERT_DATA,$where);

	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}


/**
 * テスト種別２　表示順上昇処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function up() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql  = "SELECT * FROM " . T_MS_TEST_CATEGORY2 . " WHERE test_category2_num='".$_POST['test_category2_num']."' AND test_category1_num='".$_POST['test_category1_num']."' AND test_type_num='".$_POST['test_type_num']."' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$m_test_category2_num = $list['test_category2_num'];
		$m_list_num = $list['list_num'];
	}
	if (!$m_test_category2_num || !$m_list_num) { $ERROR[] = "移動するテスト種別２情報が取得できません。"; }

	if (!$ERROR) {
		$sql  = "SELECT * FROM " . T_MS_TEST_CATEGORY2 . " WHERE mk_flg='0'".
			" AND list_num < '".$m_list_num."' AND test_type_num='".$_POST['test_type_num']."' AND test_category1_num='".$_POST['test_category1_num']."' ORDER BY list_num DESC LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$c_test_category2_num = $list['test_category2_num'];
			$c_list_num = $list['list_num'];
		}
	}
	if (!$c_test_category2_num || !$c_list_num) { $ERROR[] = "移動されるテスト種別２情報が取得できません。"; }

	if (!$ERROR) {
		$INSERT_DATA['list_num'] 	= $c_list_num;
		$INSERT_DATA['upd_tts_id'] 	= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date'] 		= "now()";
		//upd start hirose 2018/11/29 すらら英単語
//		$where = " WHERE test_category2_num='".$m_test_category2_num."' LIMIT 1;";
		$where = " WHERE test_type_num='".$_POST['test_type_num']."' AND test_category1_num='".$_POST['test_category1_num']."' AND test_category2_num='".$m_test_category2_num."' LIMIT 1;";
		//upd end hirose 2018/11/29 すらら英単語
		$ERROR = $cdb->update(T_MS_TEST_CATEGORY2,$INSERT_DATA,$where);
	}

	if (!$ERROR) {
		$INSERT_DATA['list_num'] 	= $m_list_num;
		$INSERT_DATA['upd_tts_id'] 	= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date'] 		= "now()";
		//upd start hirose 2018/11/29 すらら英単語
//		$where = " WHERE test_category2_num='".$c_test_category2_num."' LIMIT 1;";
		$where = " WHERE test_type_num='".$_POST['test_type_num']."' AND test_category1_num='".$_POST['test_category1_num']."' AND test_category2_num='".$c_test_category2_num."' LIMIT 1;";
		//upd end hirose 2018/11/29 すらら英単語
		$ERROR = $cdb->update(T_MS_TEST_CATEGORY2,$INSERT_DATA,$where);
	}

	return $ERROR;
}


/**
 * テスト種別２　表示順下降処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function down() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql  = "SELECT * FROM " . T_MS_TEST_CATEGORY2 . " WHERE test_category2_num='".$_POST['test_category2_num']."' AND test_category1_num='".$_POST['test_category1_num']."' AND test_type_num='".$_POST['test_type_num']."' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$m_test_category2_num = $list['test_category2_num'];
		$m_list_num = $list['list_num'];
	}
	if (!$m_test_category2_num || !$m_list_num) { $ERROR[] = "移動するテスト種別２情報が取得できません。"; }

	if (!$ERROR) {
		$sql  = "SELECT * FROM " . T_MS_TEST_CATEGORY2 . " WHERE mk_flg='0'".
			" AND list_num >'".$m_list_num."' AND test_type_num='".$_POST['test_type_num']."' AND test_category1_num='".$_POST['test_category1_num']."' ORDER BY list_num LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$c_test_category2_num = $list['test_category2_num'];
			$c_list_num = $list['list_num'];
		}
	}
	if (!$c_test_category2_num || !$c_list_num) { $ERROR[] = "移動されるテスト種別２情報が取得できません。"; }

	//対象データを1つ次の大きいlist_numに入れ替える
	if (!$ERROR) {
		$INSERT_DATA['list_num'] 	= $c_list_num;
		$INSERT_DATA['upd_tts_id'] 	= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date'] 		= "now()";
		//upd start hirose 2018/11/29 すらら英単語
//		$where = " WHERE test_category2_num='".$m_test_category2_num."' LIMIT 1;";
		$where = " WHERE test_type_num='".$_POST['test_type_num']."' AND test_category1_num='".$_POST['test_category1_num']."' AND test_category2_num='".$m_test_category2_num."' LIMIT 1;";
		//upd end hirose 2018/11/29 すらら英単語
		$ERROR = $cdb->update(T_MS_TEST_CATEGORY2,$INSERT_DATA,$where);
	}

	//対象データより大きいデータを対象データのlist_numに入れ替える
	if (!$ERROR) {
		$INSERT_DATA['list_num'] 	= $m_list_num;
		$INSERT_DATA['upd_tts_id'] 	= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date'] 		= "now()";
		//add start hirose 2018/11/29 すらら英単語
//		$where = " WHERE test_category2_num='".$c_test_category2_num."' LIMIT 1;";
		$where = " WHERE test_type_num='".$_POST['test_type_num']."' AND test_category1_num='".$_POST['test_category1_num']."' AND test_category2_num='".$c_test_category2_num."' LIMIT 1;";
		//add end hirose 2018/11/29 すらら英単語
		$ERROR = $cdb->update(T_MS_TEST_CATEGORY2,$INSERT_DATA,$where);
	}

	return $ERROR;
}

/**
 * テスト種別２　csvエクスポート
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function csv_export() {

	global $L_CSV_COLUMN;

	list($csv_line,$ERROR) = make_csv($L_CSV_COLUMN['test_word_test_category2'],1);
	if ($ERROR) { return $ERROR; }

	$filename = "test_word_test_category2_type".$_SESSION['sub_session']['test_type_num']."_".$_SESSION['sub_session']['test_category1_num'].".csv";

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
 * テスト種別２　csv出力情報整形
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
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
		if ($key == "test_type_num" || $key == "test_category1_num"){ continue; } //add kimura 2018/11/26 すらら英単語 _admin このカラムは扱わない
		if ($head_mode == 1) { $head_name = $key; }
		elseif ($head_mode == 2) { $head_name = $val; }
		$csv_line .= $head_name.",";
	}
	$csv_line .= "\n";

	$sql  = "SELECT * FROM " . T_MS_TEST_CATEGORY2 .
		" WHERE mk_flg='0' AND test_type_num='".$_SESSION['sub_session']['test_type_num']."' AND test_category1_num='".$_SESSION['sub_session']['test_category1_num']."' ORDER BY list_num";
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			foreach ($L_CSV_COLUMN as $key => $val) {
			if ($key == "test_type_num" || $key == "test_category1_num"){ continue; } //add kimura 2018/11/26 すらら英単語 _admin このカラムは扱わない
				$csv_line .= $list[$key].",";
			}
			$csv_line .= "\n";
		}
		$cdb->free_result($result);
	}

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
 * テスト種別２　csvインポート
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array
 */
function csv_import() {

	$ERROR = array();

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$file_name = $_FILES['import_file']['name'];
	$file_tmp_name = $_FILES['import_file']['tmp_name'];
	$file_error = $_FILES['import_file']['error'];
	if (!$file_tmp_name) {
		$ERROR[] = "テスト種別２ファイルが指定されておりません。";
	} elseif (!eregi("(.csv)$",$file_name)) {
		$ERROR[] = "ファイルの拡張子が不正です。";
	} elseif ($file_error == 1) {
		$ERROR[] = "アップロードできるファイルの容量が設定範囲を超えてます。サーバー管理者へ相談してください。";
	} elseif ($file_error == 3) {
		$ERROR[] = "マスタ単元ファイルの一部分のみしかアップロードされませんでした。";
	} elseif ($file_error == 4) {
		$ERROR[] = "マスタ単元ファイルがアップロードされませんでした。";
	}
	if ($ERROR) {
		if ($file_tmp_name && file_exists($file_tmp_name)) { unlink($file_tmp_name); }
		return $ERROR;
	}

	$ERROR = array();

	//登録単元読込
	$handle = fopen($file_tmp_name,"r");

	// php5の不具合でfgetcsvで日本語が文字化けしてしまう。
	// setlocaleでロケールを設定して対応する。
	$line1 = "";
	$judgeCharacterCode = "";
	$judge_handle = fopen($file_tmp_name,"r");
	while(!feof($judge_handle)){
    	$line1 = fgets($judge_handle,1000);
		// １行目は無視する
		if ($j > 0) {
			// 1バイト文字のみの場合には”ASCII”と判定されます。
			$judgeCharacterCode = mb_detect_encoding($line1);
			if($judgeCharacterCode == 'SJIS'){
				setlocale(LC_ALL, 'ja_JP.SJIS');
				break;
			} else if($judgeCharacterCode == 'UTF-8') {
				setlocale(LC_ALL, 'ja_JP.UTF-8');
				break;
			}
		}
		$j++;
	}

	$i = 0;
	while(!feof($handle)){
    	$str = fgetcsv($handle,10000);
		if ($i == 0) {
			$L_LIST_NAME = $str;
		} else {
			$L_IMPORT_LINE[$i] = $str;
		}
		$i++;
	}

	//読込んだら一時ファイル破棄
	unlink($file_tmp_name);

	//２行目以降＝登録データを形成
	for ($i = 1; $i < count($L_IMPORT_LINE);$i++) {
		unset($L_VALUE);
		unset($CHECK_DATA);
		unset($INSERT_DATA);
		$empty_check = preg_replace("/,/","",$L_IMPORT_LINE[$i]);
		if (count($L_IMPORT_LINE[$i]) == 0) {
			$ERROR[] = $i."行目は空なのでスキップしました。<br>";
			continue;
		}
		$L_VALUE = explode(",",$import_line);
		if (!is_array($L_VALUE)) {
			$ERROR[] = $i."行目のcsv入力値が不正なのでスキップしました。<br>";
			continue;
		}
		foreach ($L_IMPORT_LINE[$i] as $key => $val) {
			if ($L_LIST_NAME[$key] === "") { continue; }
			$val = trim($val);
			$val = ereg_replace("\"","&quot;",$val);

			//	データの文字コードがUTF-8だったら変換処理をしない
			$code = judgeCharacterCode ( $val );
			if ( $code != 'UTF-8' ) {
				$val = replace_encode_sjis($val);
				$val = mb_convert_encoding($val,"UTF-8","sjis-win");
			}
			else {
				$val = replace_encode($val);

			}
			//カナ変換
			$val = mb_convert_kana($val,"asKVn","UTF-8");
			if ($val == "&quot;") { $val = ""; }
			$val = addslashes($val);
			$CHECK_DATA[$L_LIST_NAME[$key]] = $val;
		}

		//レコードがあれば更新なければ新規

		//upd start hirose 2018/11/29 すらら英単語 list_numの修正
//		$sql = "SELECT test_category2_num FROM ". T_MS_TEST_CATEGORY2 .
		$sql = "SELECT test_category2_num,list_num FROM ". T_MS_TEST_CATEGORY2 .
		//upd end hirose 2018/11/29 すらら英単語 list_numの修正
			 // " WHERE test_category1_num='".$CHECK_DATA['test_category1_num']."' AND test_category2_num='".$CHECK_DATA['test_category2_num']."' AND test_type_num = '".$CHECK_DATA['test_type_num']."' AND mk_flg='0' LIMIT 1;"; //del kimura 2018/11/26 すらら英単語 _admin
			//add start kimura 2018/11/26 すらら英単語 _admin
			" WHERE test_category1_num = '".$_SESSION['sub_session']['test_category1_num']."'".
			" AND test_category2_num = '".$CHECK_DATA['test_category2_num']."'".
			" AND test_type_num = '".$_SESSION['sub_session']['test_type_num']."'".
			//" AND mk_flg='0'". //del hirose 2018/11/27 すらら英単語　既に削除されていた場合、プライマリーキーでインサートエラーになるため、条件除外
			" LIMIT 1;";
			//add end   kimura 2018/11/26 すらら英単語 _admin
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
		}

		if ($list['test_category2_num']) {
			if($list['test_category2_num'] == $CHECK_DATA['test_category2_num']){
				//upd start hirose 2018/11/29 すらら英単語　list_numのチェック
//				$ins_mode = "upd";
				$return_error = check_list_num($_SESSION['sub_session']['test_type_num'],$_SESSION['sub_session']['test_category1_num'],$CHECK_DATA['list_num'],$CHECK_DATA['test_category2_num'],$i);
				if($return_error){
					$ERROR[] = $return_error;
					continue;
				}else{
					$ins_mode = "upd";
				}
				//upd end hirose 2018/11/29 すらら英単語　list_numのチェック
			} else {
				$ERROR[] = $i."行目のテスト種別２IDが不正なのでスキップしました。<br>";
				continue;
			}
		} else {
			//del start hirose すらら英単語 複合キーなので、test_category2_numがプライマリーにならなくてもよいため削除
//			$sql2 = "SELECT test_category2_num FROM ". T_MS_TEST_CATEGORY2 .
//			 " WHERE test_category2_num='".$CHECK_DATA['test_category2_num']."' AND mk_flg='0' LIMIT 1;";
//
//			if ($result2 = $cdb->query($sql2)) {
//				$list2 = $cdb->fetch_assoc($result2);
//			}
////			if($list2['test_category1_num']){
//			if($list2['test_category2_num']){ //apd hirose 2018/11/27 すらら英単語 
//				$ERROR[] = $i."行目のテスト種別２IDはすでに使用されています。<br>";
//			} else {
//				$ins_mode = "add";
//			}
			//del end hirose すらら英単語 複合キーなので、test_category2_numがプライマリーにならなくてもよいため削除
			$ins_mode = "add";
		}

		//データチェック
		$DATA_ERROR[$i] = check_data($CHECK_DATA,$ins_mode,$i);
		if ($DATA_ERROR[$i]) { continue; }
		$DISP_SORT[$CHECK_DATA['test_category1_num']]++;
		$INSERT_DATA = $CHECK_DATA;
		$INSERT_DATA['test_type_num'] = $_SESSION['sub_session']['test_type_num']; //add kimura 2018/11/26 すらら英単語 _admin
		$INSERT_DATA['test_category1_num'] = $_SESSION['sub_session']['test_category1_num']; //add kimura 2018/11/26 すらら英単語 _admin
		// $INSERT_DATA['test_category1_num']= ""; //add kimura 2018/11/26 すらら英単語 _admin
		//レコードがあればアップデート、無ければインサート
		if ($ins_mode == "add") {
			if(!$INSERT_DATA['test_category2_num']){
				$sql = "SELECT MAX(test_category2_num) AS max_id FROM " . T_MS_TEST_CATEGORY2 . ";";
				if ($result = $cdb->query($sql)) {
					$list = $cdb->fetch_assoc($result);
				}
				if ($list['max_id']) {
					$INSERT_DATA['test_category2_num'] = $list['max_id'] + 1;
				} else {
					$INSERT_DATA['test_category2_num'] = 1;
				}
			}
			//級に紐づく単元の最大disp_sort値取得
			$sql  = "SELECT MAX(list_num) AS max_sort".
				// " FROM ".T_MS_TEST_CATEGORY1. //del kimura 2018/11/26 すらら英単語 _admin
				" FROM ".T_MS_TEST_CATEGORY2. //add kimura 2018/11/26 すらら英単語 _admin
				" WHERE test_type_num = '".$_SESSION['sub_session']['test_type_num']."'"
				." AND test_category1_num = '".$_SESSION['sub_session']['test_category1_num']."';";//add hirose 2018/11/29 すらら英単語
			if ($result = $cdb->query($sql)) {
				$list = $cdb->fetch_assoc($result);
			}
			if ($list['max_sort']) { $disp_sort = $list['max_sort'] + 1; } else { $disp_sort = 1; }	

			$INSERT_DATA['list_num'] 			= $disp_sort;
			$INSERT_DATA['ins_tts_id'] 			= "System";
			$INSERT_DATA['ins_date'] 			= "now()";
			$INSERT_DATA['upd_tts_id'] 			= "System";
			$INSERT_DATA['upd_date'] 			= "now()";

			$SYS_ERROR[$i] = $cdb->insert(T_MS_TEST_CATEGORY2,$INSERT_DATA);
			$new_test_cate2_num = $cdb->insert_id();
			$L_ID_NUM[$test_category2_num] = $new_test_cate2_num;
		} else {
			$INSERT_DATA['upd_tts_id'] 			= "System";
			$INSERT_DATA['upd_date'] 			= "now()";
			$INSERT_DATA['mk_flg'] 			= 0;//add hirose 2018/11/27 すらら英単語　削除されているものもあるので、mk_flgを0に直す
			//upd start hirose 2018/11/27 すらら英単語　
			//プライマリーでマッチするように条件追加
//			$where = " WHERE test_category2_num='".$INSERT_DATA['test_category2_num']."' LIMIT 1;";
			$where = " WHERE "
					. "test_type_num = '".$_SESSION['sub_session']['test_type_num']."' AND test_category1_num = '".$_SESSION['sub_session']['test_category1_num']."' AND test_category2_num='".$INSERT_DATA['test_category2_num']."' LIMIT 1;";
			//upd end hirose 2018/11/27 すらら英単語
			unset($INSERT_DATA['class_id']);
			unset($INSERT_DATA['test_category1_num']);
			$SYS_ERROR[$i] = $cdb->update(T_MS_TEST_CATEGORY2,$INSERT_DATA,$where);
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
 * マスタ単元　csvインポートチェック
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param arrat &$CHECK_DATA
 * @param mixed $ins_mode 使用していません
 * @param integer $line_num
 * @return array エラーの場合
 */
function check_data(&$CHECK_DATA,$ins_mode,$line_num) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if ($CHECK_DATA['test_category2_num']){
		if (preg_match("/[^0-9]/",$CHECK_DATA['test_category2_num'])) {
			$ERROR[] = $line_num."行目 テスト種別２IDは数字以外の指定はできません。";
		}elseif ($CHECK_DATA['test_category2_num'] < 1) {
			$ERROR[] = $line_num."行目 テスト種別２IDは0以下の指定はできません。";
		}
	} 
	if (!$CHECK_DATA['test_category2_name']) {
		$ERROR[] = $line_num."行目 テスト種別２名が未入力です。";
	}
	//add start kimura 2018/11/26 すらら英単語 _admin //同一テスト種別2名が存在しないことの確認
	else {
		$sql = "SELECT *";
		$sql.= " FROM " . T_MS_TEST_CATEGORY2;
		$sql.= " WHERE mk_flg='0'";
		$sql.= "  AND test_type_num = '".$_SESSION['sub_session']['test_type_num']."'";
		$sql.= "  AND test_category1_num = '".$_SESSION['sub_session']['test_category1_num']."'";
		$sql.= "  AND test_category2_name = '".$CHECK_DATA['test_category2_name']."'";
		if($ins_mode == "upd"){ //更新時は自身以外に同一名がないか見る
			$sql.= "  AND test_category2_num != '".$CHECK_DATA['test_category2_num']."'";
		}
		$c = 0;
		if ($result = $cdb->query($sql)) {
			$c = $cdb->num_rows($result);
		}
		if ($c > 0) {
			$ERROR[] = $line_num."行目 入力されたテスト種別２名は既に登録されております。";
		}
	}
	//add end   kimura 2018/11/26 すらら英単語 _admin

	if (!$CHECK_DATA['test_problem_count']) {
		$ERROR[] = $line_num."行目 テスト問題出題数が未入力です。";
	} elseif (preg_match("/[^0-9]/",$CHECK_DATA['test_problem_count'])) {
		$ERROR[] = $line_num."行目 テスト問題出題数は数字以外の指定はできません。";
	} elseif ($CHECK_DATA['test_problem_count'] < 1) {
		$ERROR[] = $line_num."行目 テスト問題出題数は0以下の指定はできません。";
	}
	
	if (!$CHECK_DATA['test_limit_time']) {
		$ERROR[] = $line_num."行目 テスト制限時間が未入力です。";
	} elseif (preg_match("/[^0-9]/",$CHECK_DATA['test_limit_time'])) {
		$ERROR[] = $line_num."行目 テスト制限時間は数字以外の指定はできません。";
	} elseif ($CHECK_DATA['test_limit_time'] < 1) {
		$ERROR[] = $line_num."行目 テスト制限時間は0以下の指定はできません。";
	}
	
	//del start kimura 2018/11/26 すらら英単語 _admin //これらのカラムは扱わない
	// if (!$CHECK_DATA['test_type_num']) {
	// 	$ERROR[] = $line_num."行目 テスト種類IDが未入力です。";
	// } elseif ($CHECK_DATA['test_type_num'] != $_SESSION['sub_session']['test_type_num']) {
	// 	$ERROR[] = $line_num."行目 選択したテスト種類と登録するテスト種類IDが異なります。";
	// }

// 	if (!$CHECK_DATA['test_category1_num']) {
// 		$ERROR[] = $line_num."行目 テスト種別１IDが未入力です。";
// 	} elseif ($CHECK_DATA['test_category1_num'] != $_SESSION['sub_session']['test_category1_num']) {
// 		$ERROR[] = $line_num."行目 選択したテスト種別１と登録するテスト種別１IDが異なります。";
// 	}
	//del end   kimura 2018/11/26 すらら英単語 _admin

	// ソート順が未設定の場合はエラーとする (修正モードの時のみ判断。登録の時は、MAX+1を設定している為)
	if ($ins_mode == "upd") {
		if (!$CHECK_DATA['list_num']) {
			$ERROR[] = $line_num."行目 表示順が未入力です。";
		} else if (preg_match("/[^0-9]/",$CHECK_DATA['list_num'])) {
			$ERROR[] = $line_num."行目 表示順は数字以外の指定はできません。";
		}elseif ($CHECK_DATA['list_num'] < 1) {
			$ERROR[] = $line_num."行目 表示順は1以上の数字を指定してください。";
		} else {
			//del start hirose 2018/11/29 すらら英単語
			//飛び番号でも、プライマリーになっていればよいので削除
//			$max = 0;
//			$sql  = "SELECT MAX(test_category1_num) AS max FROM ".T_MS_TEST_CATEGORY1.
//					" WHERE mk_flg='0'".
//					" AND test_type_num = '".$_SESSION['sub_session']['test_type_num']."';";
//			if ($result = $cdb->query($sql)) {
//				$list = $cdb->fetch_assoc($result);
//				$max = $list['max'];
//			}
//			if ($max < $CHECK_DATA['list_num']) {
//				$ERROR[] = $line_num."行目 表示順の最大値は、".$max."以下で設定してください。";
//			}
			//del end hirose 2018/11/29 すらら英単語
		}
	} else {
 		if ($CHECK_DATA['list_num']) {
			$ERROR[] = $line_num."行目 表示順は新規登録時、値の指定をしないでください。";
		}
	}

	if (!$CHECK_DATA['display']) {
		$ERROR[] = $line_num."行目 表示・非表示が未入力です。";
	} else {
		if (preg_match("/[^0-9]/",$CHECK_DATA['display'])) {
			$ERROR[] = $line_num."行目 表示・非表示は数字以外の指定はできません。";
		} elseif ($CHECK_DATA['display'] < 1 || $CHECK_DATA['display'] > 2) {
			$ERROR[] = $line_num."行目 表示・非表示は1（表示）か2（非表示）の数字以外の指定はできません。";
		}
	}
	if ($ERROR) {
		$ERROR[] = $line_num."行目 上記入力エラーでスキップしました。<br>";
	}
	return $ERROR;
}



/**
 * 一覧を作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array
 */
function publishing_list() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$L_PUBLISHING_LIST = array();
	$L_PUBLISHING_LIST[] = "選択して下さい";
	$sql  = "SELECT * FROM ".T_MS_PUBLISHING . " WHERE mk_flg='0' AND publishing_id!='0' ORDER BY disp_sort;";
	if ($result = $cdb->query($sql)) {
		while ($list=$cdb->fetch_assoc($result)) {
			$L_PUBLISHING_LIST[$list['publishing_id']] = $list['publishing_name'];
		}
	}
	return $L_PUBLISHING_LIST;
}

/**
 * 一覧を作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array
 */
function type_list() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$L_TYPE_LIST = array();
	$L_TYPE_LIST[] = "選択して下さい";
	$sql  = "SELECT * FROM ".T_MS_TEST_TYPE. " WHERE mk_flg='0' ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		while ($list=$cdb->fetch_assoc($result)) {
			$L_TYPE_LIST[$list['test_type_num']] = $list['test_type_name'];
		}
	}
	return $L_TYPE_LIST;
}

/**
 * 一覧を作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array
 */
function category1_list() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$L_CATEGORY1_LIST = array();
	$L_CATEGORY1_LIST[] = "選択して下さい";
	$sql  = "SELECT * FROM ".T_MS_TEST_CATEGORY1. " WHERE mk_flg='0'AND test_type_num='".$_SESSION['sub_session']['test_type_num']."' ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		while ($list=$cdb->fetch_assoc($result)) {
			$L_CATEGORY1_LIST[$list['test_category1_num']] = $list['test_category1_name'];
		}
	}
	return $L_CATEGORY1_LIST;
}

//add start hirose 2018/11/20 すらら英単語 改行コード\nを<br>に変換
/**
 * ファイル問題登録時、文字変換
 *
 * AC:[C]共通 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @param string $word
 * @return string
 */
function replace_word($word) {
	$word = mb_convert_kana($word,"asKV","UTF-8");
	$word = preg_replace("/<>/","&lt;&gt;",$word);
	$word = trim($word);
	$word = preg_replace("/\r/","",$word);
	$word = preg_replace("/\n/","<br>",$word);
	$word = replace_encode($word);

	return $word;
}
//add end hirose 2018/11/20 すらら英単語 改行コード\nを<br>に変換
//add start hirose 2018/11/29 すらら英単語 list_numの修正
/**
 * list_numがすでに使用されているかのチェック
 *
 *
 * @author Azet
 * @param string $word
 * @return string
 */
function check_list_num($test_type_num,$test_category1_num,$list_num,$test_category2_num,$i){
	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
	
	$ERROR = "";
	$list_array = array();
	$sql = "SELECT test_category2_num,list_num FROM " . T_MS_TEST_CATEGORY2
			. "	WHERE test_type_num = '" . $test_type_num . "' "
			. " AND test_category1_num = '" . $test_category1_num . "' "
			. " AND list_num = '" . $list_num . "' "
			. "AND mk_flg='0';";
	$c = 0;
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$c++;
			//test_type_num・test_category1_numで検索してるから、test_category2_numだけでプライマリー
			$list_array[$list['test_category2_num']] = $list['list_num'];
		}
	}else{
		$ERROR = $i . "行目のSQLエラーです";
	}
//	pre($list_array);
	if(!$ERROR){
		//同じtest_type_num・test_category1_numの中でlist_numが使用されていなかったらアップデート
		if ($c == 0) {
			//アップデートしてよいので何もしない
		//同じtest_type_num・test_category1_numの中でlist_numが1つあった場合、それが今チェックしているデータのものか確認
		} elseif ($c == 1) {
			if (isset($list_array[$test_category2_num])) {
				//アップデートしてよいので何もしない
			} else {
				$ERROR = $i . "行目のlist_numはすでに使用されているためスキップしました。<br>";
			}
		} else {
			$ERROR = $i . "行目のlist_numはすでに使用されているためスキップしました。<br>";
		}
	}
	return $ERROR;
}

//add end hirose 2018/11/29 すらら英単語 list_numの修正
?>
