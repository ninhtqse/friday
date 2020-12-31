<?
/**
 * ベンチャー・リンク　すらら
 *
 * テスト用プラクティス管理　数学検定単元管理
 *
 * 履歴
 * 2015/9/14 初期設定
 *
 * @author Azet
 */

//hasegawa 2015/9/10 02_作業要件/34_数学検定/数学検定

/**
 * HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * インポート・エクスポートにlms単元の項目追加
 *
 * @author Azet
 * @return string HTML
 */
function start() {

	if (ACTION == "check") { $ERROR = check(); }
	elseif (ACTION == "unit_check") { $ERROR = unit_check(); }

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
			if (!$ERROR) { $html .= math_unit_list($ERROR); }
			else { $html .= addform($ERROR); }
		} else {
			$html .= addform($ERROR);
		}
	} elseif (MODE == "view") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= viewform($ERROR); }
		} elseif (ACTION == "change") {
			if (!$ERROR) { $html .= math_unit_list($ERROR); }
			else { $html .= viewform($ERROR); }
		} else {
			$html .= viewform($ERROR);
		}
	} elseif (MODE == "delete") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= viewform($ERROR); }
		} elseif (ACTION == "change") {
			if (!$ERROR) { $html .= math_unit_list($ERROR); }
			else { $html .= viewform($ERROR); }
		} else {
			$html .= check_html();
		}
	} else {
		if ($_SESSION['t_practice']['class_id'] != 'select'){
			$html .= math_unit_list($ERROR);
		}
	}

	return $html;
}


/**
 * 級選択
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function select_unit_view() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// global $L_MATH_TEST_CLASS; // del 2020/09/15 phong テスト標準化開発
	// add start 2020/09/15 phong テスト標準化開発
	$test_type5 = new TestStdCfgType5($cdb);
	// upd start hirose 2020/09/18 テスト標準化開発
	// $L_MATH_TEST_CLASS = $test_type5->getClassNames();
	$L_MATH_TEST_CLASS = ['select'=>'選択して下さい']+$test_type5->getClassNamesAdmin();
	// upd end hirose 2020/09/18 テスト標準化開発
	// add end 2020/09/15 phong テスト標準化開発


	//級
	foreach ($L_MATH_TEST_CLASS as $class_id => $class_name) {
		if ($class_name == "") {
			continue;
		}
		$selected = "";
		if ($_SESSION['t_practice']['class_id'] == $class_id) {
			$_SESSION['t_practice']['class_name'] =$class_name;
			$selected = "selected";
		}
		$class_html .= "<option value=\"".$class_id."\" ".$selected.">".$class_name."</option>\n";
	}

	$html = "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"menu\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"set_unit_view\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"view_session\">\n";
	$html .= "<table class=\"unit_form\">\n";
	$html .= "<tr class=\"unit_form_menu\">\n";
	$html .= "<td>級</td>\n";
	$html .= "</tr>\n";
	$html .= "<tr class=\"unit_form_cell\">\n";
	$html .= "<td>\n";
	$html .= "<select name=\"class_id\" onchange=\"submit();\">".$class_html."</select>\n";
	$html .= "</td>\n";
	$html .= "</tr>\n";
	$html .= "</table>\n";
	$html .= "</form>\n";
	$html .= "<br>\n";

	if (!$_SESSION['t_practice']['class_id'] || $_SESSION['t_practice']['class_id'] == 'select') {
		$html .= "<br>\n";
		$html .= "単元を設定する級を選択してください。<br>\n";
	}

	return $html;
}


/**
 * 単元表示メニューセッション操作
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function view_session() {

	if (strlen($_POST['class_id'])) { $_SESSION['t_practice']['class_id'] = $_POST['class_id']; }
	return;
}


/**
 * 単元一覧
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function math_unit_list($ERROR) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// global $L_EXP_CHA_CODE,$L_PAGE_VIEW,$L_MATH_TEST_CLASS,$L_DISPLAY; // del 2020/09/15 phong テスト標準化開発
	// add start 2020/09/15 phong テスト標準化開発  
	global $L_EXP_CHA_CODE,$L_PAGE_VIEW,$L_DISPLAY; 
	$test_type5 = new TestStdCfgType5($cdb);
	// upd start hirose 2020/09/18 テスト標準化開発
	// $L_MATH_TEST_CLASS = $test_type5->getClassNames();
	$L_MATH_TEST_CLASS = $test_type5->getClassNamesAdmin();
	// upd end hirose 2020/09/18 テスト標準化開発
	// add end 2020/09/15 phong テスト標準化開発

	// 級が未選択の場合は一覧を表示しない
	if (!$_SESSION['t_practice']['class_id'] || $_SESSION['t_practice']['class_id'] == 'select') {
		return;
	}

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}
	$html .= "<br>\n";
	$html .= "<span style=\"font-weight:bold;\">級 ： ".$_SESSION['t_practice']['class_name']."</span>";
	$html .= "<br>\n";
	$html .= "インポートする場合は、単元csvファイル（S-JIS）を指定しCSVインポートボタンを押してください。<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" enctype=\"multipart/form-data\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"import\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\" \">\n";
	$html .= "<input type=\"file\" size=\"40\" name=\"import_file\"><br>\n";
	$html .= "<input type=\"submit\" value=\"CSVインポート\" style=\"float:left;\">\n";
	$html .= "</form>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"export\">\n";
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

	$html .= "<input type=\"submit\" value=\"CSVエクスポート\">\n";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"add\">\n";
	$html .= "<input type=\"submit\" value=\"単元新規登録\">\n";
	$html .= "</form>\n";
	$sql  = "SELECT bu.book_unit_id, bu.book_unit_name, bu.display".
		" FROM ".T_MS_BOOK_UNIT." bu".
		" INNER JOIN ".T_MATH_TEST_BOOK_UNIT_LIST." mtbul ON bu.book_unit_id = mtbul.book_unit_id AND mtbul.mk_flg = '0'".
		" AND mtbul.class_id = '".$_SESSION['t_practice']['class_id']."'".
		" WHERE bu.mk_flg = '0' ".
		" ORDER BY  bu.disp_sort ";// >>>disp_sort実装

	if ($result = $cdb->query($sql)) {
		$book_unit_count = $cdb->num_rows($result);
	}
	if (!$book_unit_count) {
		$html .= "<br>\n";
		$html .= "今現在登録されている単元は有りません。<br>\n";
		return $html;
	}
	$html .= "<br>\n";
	$html .= "<table class=\"course_form\">\n";
	$html .= "<tr class=\"course_form_menu\">\n";
	// >>>disp_sort実装
	if (!ereg("practice__view",$authority)
		&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__sort",$_SESSION['authority'])===FALSE))
	) {
		$html .= "<th>↑</th>\n";
		$html .= "<th>↓</th>\n";
	}

	$html .= "<th>単元ID</th>\n";
	$html .= "<th>単元名</th>\n";
	$html .= "<th>表示・非表示</th>\n";
	$html .= "<th>変更</th>\n";
	$html .= "<th>削除</th>\n";
	$html .= "</tr>\n";

	$i = 0;	// >>>disp_sort実装
	while ($list = $cdb->fetch_assoc($result)) {

		// >>>disp_sort実装
		$i++;
		$up_submit = $down_submit = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		if ($i != 1) { $up_submit = "<input type=\"submit\" name=\"action\" value=\"↑\">\n"; }
		if ($i < $book_unit_count) { $down_submit = "<input type=\"submit\" name=\"action\" value=\"↓\">\n"; }

		//単元名タグ解除
		$list['book_unit_name'] = str_replace("&lt;","<",$list['book_unit_name']);
		$list['book_unit_name'] = str_replace("&gt;",">",$list['book_unit_name']);

		$html .= "<tr class=\"course_form_cell\">\n";

		// >>>disp_sort実装
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"book_unit_id\" value=\"".$list['book_unit_id']."\">\n";
		if (!ereg("practice__view",$authority)
			&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__sort",$_SESSION['authority'])===FALSE))
		) {
			$html .= "<td>".$up_submit."</td>\n";
			$html .= "<td>".$down_submit."</td>\n";
		}
		$html .= "</form>\n";

		$html .= "<td>".$list['book_unit_id']."</td>\n";
		$html .= "<td style=\"width:350px;\">".$list['book_unit_name']."</td>\n";
		$html .= "<td>".$L_DISPLAY[$list['display']]."</td>\n";
		$html .= "<td>\n";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"view\">\n";
		$html .= "<input type=\"hidden\" name=\"book_id\" value=\"".$list['book_id']."\">\n";
		$html .= "<input type=\"hidden\" name=\"book_unit_id\" value=\"".$list['book_unit_id']."\">\n";
		$html .= "<input type=\"hidden\" name=\"parent_book_unit_id\" value=\"".$parent_book_unit_id."\">\n";
		$html .= "<input type=\"submit\" value=\"変更\">\n";
		$html .= "</form>\n";
		$html .= "</td>\n";
		$html .= "<td>\n";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"delete\">\n";
		$html .= "<input type=\"hidden\" name=\"book_id\" value=\"".$list['book_id']."\">\n";
		$html .= "<input type=\"hidden\" name=\"book_unit_id\" value=\"".$list['book_unit_id']."\">\n";
		$html .= "<input type=\"hidden\" name=\"parent_book_unit_id\" value=\"".$parent_book_unit_id."\">\n";
		$html .= "<input type=\"submit\" value=\"削除\">\n";
		$html .= "</form>\n";
		$html .= "</td>\n";
		$html .= "</tr>\n";
	}

	$html .= "</table>\n";

	return $html;
}


/**
 * マスタ単元　新規登録フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function addform($ERROR) {
	global $L_DISPLAY;

	$html .= "<br>\n";
	$html .= "新規登録フォーム<br>\n";

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";
	if ($_SESSION['t_practice']['class_id']) {
		$html .= "<input type=\"hidden\" name=\"book_unit_id\" value=\"".$_POST['book_unit_id']."\">\n";
	}
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(MATH_TEST_UNIT_FORM);

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
	$INPUTS[BOOKUNITNAME] 	= array('type'=>'text','name'=>'book_unit_name','size'=>'50','value'=>$_POST['book_unit_name']);
	$INPUTS[PARENTBOOKUNITID] = array('type'=>'text','name'=>'parent_book_unit_id','size'=>'5','value'=>$_POST['parent_book_unit_id']);
	$INPUTS[USRBKO] 		= array('type'=>'text','name'=>'usr_bko','size'=>'50','value'=>$_POST['usr_bko']);
	$INPUTS[DISPLAY] 		= array('result'=>'plane','value'=>$display_html);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"追加確認\">\n";
	$html .= "<input type=\"reset\" value=\"クリア\">\n";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"book_unit_list\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";
	return $html;
}


/**
 * マスタ単元　修正フォーム
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
		$sql  = "SELECT *" . " FROM " . T_MS_BOOK_UNIT . " WHERE mk_flg='0' AND book_unit_id='".$_POST['book_unit_id']."' LIMIT 1;";
		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);
		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			$html .= "<br>\n";
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"book_unit_list\">\n";
			$html .= "<input type=\"hidden\" name=\"book_id\" value=\"".$_POST['book_id']."\">\n";
			$html .= "<input type=\"submit\" value=\"戻る\">\n";
			$html .= "</form>\n";
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

	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";
	$html .= "<input type=\"hidden\" name=\"book_unit_id\" value=\"".$_POST['book_unit_id']."\">\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(MATH_TEST_UNIT_FORM);

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
	$INPUTS[BOOKUNITNAME] 	= array('type'=>'text','name'=>'book_unit_name','size'=>'50','value'=>$book_unit_name);
	$INPUTS[USRBKO] 		= array('type'=>'text','name'=>'usr_bko','size'=>'50','value'=>$usr_bko);
	$INPUTS[DISPLAY] 		= array('result'=>'plane','value'=>$display_html);

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
 * マスタ単元　新規登録・修正　必須項目チェック
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function check() {

	if (!$_POST['book_unit_name']) { $ERROR[] = "単元名が未入力です。"; }
	if (!$_POST['display']) { $ERROR[] = "表示・非表示が未選択です。"; }

	if (mb_strlen($_POST['usr_bko'], 'UTF-8') > 255) { $ERROR[] = "備考が不正です。255文字以内で記述して下さい。"; }

	return $ERROR;
}


/**
 * マスタ単元　新規登録・修正・削除　確認フォーム
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
				elseif (MODE == "view") { $val = "change"; }
			}
			$HIDDEN .= "<input type=\"hidden\" name=\"".$key."\" value=\"".$val."\">\n";
		}
	}

	if (ACTION) {
		foreach ($_POST as $key => $val) { $$key = $val; }
	} else {
		//削除用処理
		$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"change\">\n";
		$sql = "SELECT * FROM " . T_MS_BOOK_UNIT . " WHERE book_unit_id='".$_POST['book_unit_id']."' AND mk_flg='0' LIMIT 1;";
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

	if (MODE != "delete") { $button = "登録"; } else { $button = "削除"; }

	// 問題との関連をチェックし、問題が関連している場合は、削除させない
	if (MODE == "delete" && check_problem($_POST['book_unit_id'])) {
		$html = "<br>\n";
		$html .= "該当の単元に問題が登録されております。<br>関連する問題の出題単元項目を修正後に削除して下さい。<br>\n";

		$make_html = new read_html();
		$make_html->set_dir(ADMIN_TEMP_DIR);
		$make_html->set_file(MATH_TEST_UNIT_FORM);

		//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
		$INPUTS[BOOKUNITNAME] 	= array('result'=>'plane','value'=>$book_unit_name);
		$INPUTS[USRBKO] 		= array('result'=>'plane','value'=>$usr_bko);
		$INPUTS[DISPLAY] 		= array('result'=>'plane','value'=>$L_DISPLAY[$display]);


		$make_html->set_rep_cmd($INPUTS);

		$html .= $make_html->replace();
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
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"book_unit_list\">\n";
		}
		$html .= "<input type=\"submit\" value=\"戻る\">\n";
		$html .= "</form>\n";

	} else {
		$html = "<br>\n";
		$html .= "確認画面：以下の内容で".$button."してもよろしければ".$button."ボタンをクリックしてください。<br>\n";

		$make_html = new read_html();
		$make_html->set_dir(ADMIN_TEMP_DIR);
		$make_html->set_file(MATH_TEST_UNIT_FORM);

		//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
		$INPUTS[BOOKUNITNAME] 	= array('result'=>'plane','value'=>$book_unit_name);
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
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"book_unit_list\">\n";
		}
		$html .= "<input type=\"submit\" value=\"戻る\">\n";
		$html .= "</form>\n";
	}

	return $html;
}


/**
 * マスタ単元　新規登録処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function add() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//book_unit_idを生成する部分（最大値に+1）
	$sql = "SELECT MAX(book_unit_id) AS max_id FROM " . T_MS_BOOK_UNIT . ";";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
	}
	if ($list['max_id']) { $book_unit_id = $list['max_id'] + 1; } else { $book_unit_id = 1; }

	//級に紐づく単元の最大disp_sort値取得
	$sql  = "SELECT MAX(bu.disp_sort) AS max_sort".
		" FROM ".T_MS_BOOK_UNIT." bu".
		" INNER JOIN ".T_MATH_TEST_BOOK_UNIT_LIST." mtbul ON bu.book_unit_id = mtbul.book_unit_id ".
		" AND mtbul.class_id = '".$_SESSION['t_practice']['class_id']."'";
		// disp_sortは重複を防ぐためにマックスの値を入れる
		//" WHERE bu.mk_flg = '0';";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
	}
	$max_sort = $list['max_sort'];
	$disp_sort = $max_sort + 1;

	$INSERT_DATA[book_unit_id] 		= $book_unit_id;
	$INSERT_DATA[book_id]		 	= 0;
	$INSERT_DATA[book_unit_name] 		= $_POST['book_unit_name'];
	$INSERT_DATA[unit_end_flg] 		= 1;
	$INSERT_DATA[disp_sort] 		= $disp_sort;
	$INSERT_DATA[usr_bko] 			= $_POST['usr_bko'];
	$INSERT_DATA[display] 			= $_POST['display'];
	$INSERT_DATA[ins_tts_id] 		= $_SESSION['myid']['id'];
	$INSERT_DATA[ins_date] 			= "now()";
	$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];
	$INSERT_DATA[upd_date] 			= "now()";

	$ERROR = $cdb->insert(T_MS_BOOK_UNIT,$INSERT_DATA);
	if ($ERROR) { return $ERROR; }

	//math_test_book_unit(級と単元のJOINテーブル)を更新↓
	$sql = "SELECT MAX(disp_sort) AS max_sort2 FROM " . T_MATH_TEST_BOOK_UNIT_LIST . ";";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
	}
	if (!$max_sort2) { $max_sort2 = $list['max_sort2']; }
	$disp_sort2 = $max_sort2 + 1;

	$INSERT_DATA2[class_id] 		= $_SESSION['t_practice']['class_id'];
	$INSERT_DATA2[book_unit_id] 		= $book_unit_id;
	$INSERT_DATA2[disp_sort] 		= $disp_sort2;
	$INSERT_DATA2[ins_tts_id] 		= $_SESSION['myid']['id'];
	$INSERT_DATA2[ins_date] 			= "now()";
	$INSERT_DATA2[upd_tts_id] 		= $_SESSION['myid']['id'];
	$INSERT_DATA2[upd_date] 			= "now()";

	$ERROR = $cdb->insert(T_MATH_TEST_BOOK_UNIT_LIST,$INSERT_DATA2);
	if ($ERROR) { return $ERROR; }

	return $ERROR;
}


/**
 * マスタ単元　修正・削除処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function change() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (MODE == "view") {

		$INSERT_DATA[book_unit_name] 		= $_POST['book_unit_name'];
		$INSERT_DATA[usr_bko] 			= $_POST['usr_bko'];
		$INSERT_DATA[display] 			= $_POST['display'];
		$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];
		$INSERT_DATA[upd_date] 			= "now()";

	} elseif (MODE == "delete") {

		$INSERT_DATA[mk_flg] 			= 1;
		$INSERT_DATA[mk_tts_id] 		= $_SESSION['myid']['id'];
		$INSERT_DATA[mk_date] 			= "now()";
	}
	$where = " WHERE book_unit_id='".$_POST['book_unit_id']."' LIMIT 1;";
	$ERROR = $cdb->update(T_MS_BOOK_UNIT,$INSERT_DATA,$where);
	if (MODE == "delete") {
		$ERROR = $cdb->update(T_MATH_TEST_BOOK_UNIT_LIST,$INSERT_DATA,$where);
	}

	return $ERROR;
}


/**
 * disp_sortを上げる
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function up() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql  = "SELECT * FROM " . T_MS_BOOK_UNIT .
			" WHERE book_unit_id='".$_POST['book_unit_id']."'".
			" LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$m_book_unit_id = $list['book_unit_id'];
		$m_disp_sort = $list['disp_sort'];
	}
	if (!$m_book_unit_id || !$m_disp_sort) { $ERROR[] = "移動するグループ情報が取得できません。"; }

	if (!$ERROR) {
		//級に紐づく単元の最大disp_sort値取得
		$sql  = "SELECT bu.* ".
			" FROM ".T_MS_BOOK_UNIT." bu".
			" INNER JOIN ".T_MATH_TEST_BOOK_UNIT_LIST." mtbul ON bu.book_unit_id = mtbul.book_unit_id ".
			" AND mtbul.class_id = '".$_SESSION['t_practice']['class_id']."'".
			" WHERE 1 ".
			" AND bu.mk_flg='0'".
			" AND bu.disp_sort<'".$m_disp_sort."'".
			" ORDER BY bu.disp_sort DESC LIMIT 1";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$c_book_unit_id = $list['book_unit_id'];
			$c_disp_sort = $list['disp_sort'];
		}
	}
	if (!$c_book_unit_id || !$c_disp_sort) { $ERROR[] = "移動されるグループ情報が取得できません。"; }
	// 「↑」ボタンを押した採点単元を更新
	if (!$ERROR) {
		$INSERT_DATA['disp_sort'] 	= $c_disp_sort;
		$INSERT_DATA['upd_tts_id'] 	= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date'] 		= "now()";
		$where = " WHERE book_unit_id='".$m_book_unit_id."' LIMIT 1;";
		$ERROR = $cdb->update(T_MS_BOOK_UNIT,$INSERT_DATA,$where);
	}
	// 並び順を交換する対象の採点単元を更新
	if (!$ERROR) {
		$INSERT_DATA['disp_sort'] 	= $m_disp_sort;
		$INSERT_DATA['upd_tts_id'] 	= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date'] 		= "now()";
		$where = " WHERE book_unit_id='".$c_book_unit_id."' LIMIT 1;";
		$ERROR = $cdb->update(T_MS_BOOK_UNIT,$INSERT_DATA,$where);
	}

	return $ERROR;
}

/**
 * disp_sortを下げる
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function down() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql  = "SELECT * FROM " . T_MS_BOOK_UNIT .
			" WHERE book_unit_id='".$_POST['book_unit_id']."'".
			" LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$m_book_unit_id = $list['book_unit_id'];
		$m_disp_sort = $list['disp_sort'];
	}
	if (!$m_book_unit_id || !$m_disp_sort) { $ERROR[] = "移動するグループ情報が取得できません。"; }

	if (!$ERROR) {
		//級に紐づく単元の最大disp_sort値取得
		$sql  = "SELECT bu.* ".
			" FROM ".T_MS_BOOK_UNIT." bu".
			" INNER JOIN ".T_MATH_TEST_BOOK_UNIT_LIST." mtbul ON bu.book_unit_id = mtbul.book_unit_id ".
			" AND mtbul.class_id = '".$_SESSION['t_practice']['class_id']."'".
			" WHERE 1 ".
			" AND bu.mk_flg='0'".
			" AND bu.disp_sort>'".$m_disp_sort."'".
			" ORDER BY bu.disp_sort LIMIT 1";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$c_book_unit_id = $list['book_unit_id'];
			$c_disp_sort = $list['disp_sort'];
		}
	}
	if (!$c_book_unit_id || !$c_disp_sort) { $ERROR[] = "移動されるグループ情報が取得できません。"; }

	if (!$ERROR) {
		$INSERT_DATA['disp_sort'] = $c_disp_sort;
		$INSERT_DATA['upd_tts_id'] 	= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date'] 		= "now()";
		$where = " WHERE book_unit_id='".$m_book_unit_id."' LIMIT 1;";
		$ERROR = $cdb->update(T_MS_BOOK_UNIT,$INSERT_DATA,$where);
	}

	if (!$ERROR) {
		$INSERT_DATA['disp_sort'] = $m_disp_sort;
		$INSERT_DATA['upd_tts_id'] 	= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date'] 		= "now()";
		$where = " WHERE book_unit_id='".$c_book_unit_id."' LIMIT 1;";
		$ERROR = $cdb->update(T_MS_BOOK_UNIT,$INSERT_DATA,$where);
	}

	return $ERROR;
}

/**
 * マスタ単元　csvエクスポート
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function csv_export() {

	global $L_CSV_COLUMN;

	list($csv_line,$ERROR) = make_csv($L_CSV_COLUMN['math_test_unit'],1,1);
	if ($ERROR) { return $ERROR; }

	$filename = "math_test_unit_".$_SESSION['t_practice']['class_id'].".csv";

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
 * マスタ単元　csv出力情報整形
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * 先頭出力文字
 * head_mode	1 カラム名
 * 				2 コメント名
 * 出力範囲
 * csv_mode	1 マスタ単位
 * 			2 単元全て
 *
 * @author Azet
 * @param array $L_CSV_COLUMN
 * @param integer $head_mode='1'
 * @param integer $csv_mode='1'
 * @return array
 */
function make_csv($L_CSV_COLUMN,$head_mode='1',$csv_mode='1') {

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
		$csv_line .= "\"".$head_name."\",";
	}

	$csv_line .= "\n";

	$sql = "SELECT ".
			" mtbul.class_id,".
			" bu.book_unit_id,".
			" bu.book_unit_name,".
			" bu.disp_sort,".// >>>disp_sort実装
			" bu.display".
			" FROM ".T_MS_BOOK_UNIT." bu".
			" INNER JOIN ".T_MATH_TEST_BOOK_UNIT_LIST." mtbul ON bu.book_unit_id = mtbul.book_unit_id".
			" AND mtbul.mk_flg = '0'".
			" WHERE bu.mk_flg = '0'".
			//update start kimura 2017/12/19 AWS移設 ソートなし → ORDER BY句追加
			//" AND mtbul.class_id = '".$_SESSION['t_practice']['class_id']."'";
			" AND mtbul.class_id = '".$_SESSION['t_practice']['class_id']."'".
			" ORDER BY bu.disp_sort". //表示順にソート
			" ;";
			//update end   kimura 2017/12/19
	$L_EXPORT_LIST = array();
	$book_unit_id_html = "";

	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			foreach ($L_CSV_COLUMN as $key => $val) {
				$csv_line .= "\"".$list[$key]."\",";
			}
			$csv_line .= "\n";
		}
		$cdb->free_result($result);
	}

	if ( $_POST['exp_list'] == 2 ) {
		//	Unicode選択時には特殊文字のみ変換
		$csv_line = replace_decode($csv_line);
	} else {
		//	SJISで出力
		$csv_line = mb_convert_encoding($csv_line,"sjis-win","UTF-8");
		$csv_line = replace_decode_sjis($csv_line);

	}
	return array($csv_line,$ERROR);
}


/**
 * マスタ単元　csvインポート
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
		$ERROR[] = "単元ファイルが指定されておりません。";
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

	// add start hasegawa 2015/10/27
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
	// add end hasegawa 2015/10/27

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
//		$L_VALUE = explode(",",$import_line);
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

		$sql = "SELECT mbu.book_unit_id FROM ". T_MS_BOOK_UNIT ." mbu".
			 " LEFT JOIN ".T_MATH_TEST_BOOK_UNIT_LIST ." mtbul ON mtbul.book_unit_id = mbu.book_unit_id".
			 " WHERE mbu.book_unit_id='".$CHECK_DATA['book_unit_id']."' AND book_id = '0' AND mbu.mk_flg='0' LIMIT 1;";

		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
		}

		if ($list['book_unit_id']) {
			if($list['book_unit_id'] == $CHECK_DATA['book_unit_id']){
				$ins_mode = "upd";
			} else {
				$ERROR[] = $i."行目の単元IDが不正なのでスキップしました。<br>";
				continue;
			}
		} else {
			$sql2 = "SELECT book_unit_id FROM ". T_MS_BOOK_UNIT .
			 " WHERE book_unit_id='".$CHECK_DATA['book_unit_id']."' AND mk_flg='0' LIMIT 1;";

			if ($result2 = $cdb->query($sql2)) {
				$list2 = $cdb->fetch_assoc($result2);
			}
			if($list2['book_unit_id']){
				$ERROR[] = $i."行目の単元IDはすでに使用されています。<br>";
			} else {
				$ins_mode = "add";
			}
		}

		if (!$CHECK_DATA['display']) {
			$CHECK_DATA['display'] = 1;
		}

		//データチェック
		$DATA_ERROR[$i] = check_data($CHECK_DATA,$ins_mode,$i);
		if ($DATA_ERROR[$i]) { continue; }
		$DISP_SORT[$CHECK_DATA['book_unit_id']]++;
		$INSERT_DATA = $CHECK_DATA;
		//レコードがあればアップデート、無ければインサート
		if ($ins_mode == "add") {
			if(!$INSERT_DATA['book_unit_id']){
				$sql = "SELECT MAX(book_unit_id) AS max_id FROM " . T_MS_BOOK_UNIT . ";";
				if ($result = $cdb->query($sql)) {
					$list = $cdb->fetch_assoc($result);
				}
				if ($list['max_id']) {
					$INSERT_DATA['book_unit_id'] = $list['max_id'] + 1;
				} else {
					$INSERT_DATA['book_unit_id'] = 1;
				}
			}
			//級に紐づく単元の最大disp_sort値取得
			$sql  = "SELECT MAX(bu.disp_sort) AS max_sort".
				" FROM ".T_MS_BOOK_UNIT." bu".
				" INNER JOIN ".T_MATH_TEST_BOOK_UNIT_LIST." mtbul ON bu.book_unit_id = mtbul.book_unit_id ".
				" AND mtbul.class_id = '".$_SESSION['t_practice']['class_id']."'";
				// disp_sortは重複を防ぐためにマックスの値を入れる
				//" WHERE bu.mk_flg = '0';";
			if ($result = $cdb->query($sql)) {
				$list = $cdb->fetch_assoc($result);
			}
			$max_sort = $list['max_sort'];
			$disp_sort = $max_sort + 1;

			$INSERT_DATA[book_id]		 	= 0;
			$INSERT_DATA[unit_end_flg] 		= 1;
			$INSERT_DATA[disp_sort] 		= $disp_sort;
			$INSERT_DATA[ins_tts_id] 		= "System";
			$INSERT_DATA[ins_date] 			= "now()";
			$INSERT_DATA[upd_tts_id] 		= "System";
			$INSERT_DATA[upd_date] 			= "now()";

			//math_test_book_unit(級と単元のJOINテーブル)を更新↓
			$sql = "SELECT MAX(disp_sort) AS max_sort2 FROM " . T_MATH_TEST_BOOK_UNIT_LIST . ";";
			if ($result = $cdb->query($sql)) {
				$list = $cdb->fetch_assoc($result);
			}
			if (!$max_sort2) { $max_sort2 = $list['max_sort2']; }
			$disp_sort2 = $max_sort2 + 1;
			$INSERT_DATA2[class_id] 		= $INSERT_DATA[class_id];
			$INSERT_DATA2[book_unit_id] 	= $INSERT_DATA[book_unit_id];
			$INSERT_DATA2[disp_sort] 		= $disp_sort2;
			$INSERT_DATA2[ins_tts_id] 		= "System";
			$INSERT_DATA2[ins_date] 		= "now()";
			$INSERT_DATA2[upd_tts_id] 		= "System";
			$INSERT_DATA2[upd_date] 		= "now()";

			unset($INSERT_DATA['class_id']);
			$SYS_ERROR[$i] = $cdb->insert(T_MS_BOOK_UNIT,$INSERT_DATA);
			$SYS_ERROR[$i] = $cdb->insert(T_MATH_TEST_BOOK_UNIT_LIST,$INSERT_DATA2);
			$new_book_unit_id = $cdb->insert_id();
			$L_ID_NUM[$book_unit_num] = $new_book_unit_id;
		} else {
			$INSERT_DATA[upd_tts_id] 		= "System";
			$INSERT_DATA[upd_date] 			= "now()";
			$where = " WHERE book_unit_id='".$INSERT_DATA['book_unit_id']."' LIMIT 1;";
			unset($INSERT_DATA['class_id']);
			unset($INSERT_DATA['book_unit_id']);
			$SYS_ERROR[$i] = $cdb->update(T_MS_BOOK_UNIT,$INSERT_DATA,$where);
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

	if ($CHECK_DATA['book_unit_id']){

		if (preg_match("/[^0-9]/",$CHECK_DATA['book_unit_id'])) {
			$ERROR[] = $line_num."行目 単元IDは数字以外の指定はできません。";
		} elseif ($CHECK_DATA['book_unit_id'] < 1) {
			$ERROR[] = $line_num."行目 単元IDは1以下の指定はできません。";
		}
	}
	if (!$CHECK_DATA['book_unit_name']) {
		$ERROR[] = $line_num."行目 単元名が未入力です。";
	}

	// 級IDチェック
	if (!$CHECK_DATA['class_id']) {
		$ERROR[] = $line_num."行目 級IDが未入力です。";
	} elseif ($CHECK_DATA['class_id'] != $_SESSION['t_practice']['class_id']) {
		$ERROR[] = $line_num."行目 選択した級と登録する級IDが異なります。";
	}

	// ソート順が未設定の場合はエラーとする (修正モードの時のみ判断。登録の時は、MAX+1を設定している為)
	if ($ins_mode == "upd") {
		if (!$CHECK_DATA['disp_sort']) {
			$ERROR[] = $line_num."行目 ソート順が未入力です。";
		} else if (preg_match("/[^0-9]/",$CHECK_DATA['disp_sort'])) {
			$ERROR[] = $line_num."行目 ソート順は数字以外の指定はできません。";
		}
	} else {
 		if ($CHECK_DATA['disp_sort']) {
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
 * マスタ単元　問題関連チェック
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function check_problem($book_unit_id) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$exist_flag = false;

	if ($book_unit_id) {
		// 問題に紐づいているかチェックするcontrol_unit_id
		$sql = "SELECT book_unit_id FROM " . T_BOOK_UNIT_TEST_PROBLEM . " WHERE book_unit_id = '".$book_unit_id."' LIMIT 1;";

		if ($result = $cdb->query($sql)) {
			while ($list = $cdb->fetch_assoc($result)) {
				$exist_flag = true;
				break;
			}
		}
	}

	return $exist_flag;
}

?>
