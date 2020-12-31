<?
/**
 * ベンチャー・リンク　すらら
 *
 * テスト用プラクティス管理　教科書単元管理
 *
 * 履歴
 * 2010/12/06 初期設定
 *
 * @author Azet
 */

// hirano

/*
インポート・エクスポートにlms単元の項目追加
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
			if (!$ERROR) { $html .= book_unit_list($ERROR); }
			else { $html .= addform($ERROR); }
		} else {
			$html .= addform($ERROR);
		}
	} elseif (MODE == "view") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= viewform($ERROR); }
		} elseif (ACTION == "change") {
			if (!$ERROR) { $html .= book_unit_list($ERROR); }
			else { $html .= viewform($ERROR); }
		} else {
			$html .= viewform($ERROR);
		}
	} elseif (MODE == "delete") {
		if (ACTION == "change") {
			if (!$ERROR) { $html .= book_unit_list($ERROR); }
			else { $html .= check_html(); }
		} else {
			$html .= check_html();
		}
	} else {
		if ($_SESSION['t_practice']['book_id']) {
			$html .= book_unit_list($ERROR);
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

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// global $L_GKNN_LIST_TYPE1; // update $L_GKNN_LIST => $L_GKNN_LIST_TYPE1 2015/02/12 yoshizawa // del karasawa 2020/12/22 定期テスト学年追加開発
	// add start karasawa 2020/12/22 定期テスト学年追加開発
	global $L_GKNN_LIST_TYPE2;
	$posted_course_num = 0;
	if($_POST['course_num']){$posted_course_num = $_POST['course_num'];}
	if($_SESSION['t_practice']['gknn']){$posted_course_num = $_SESSION['t_practice']['course_num'] ;}
	// add end karasawa 2020/12/22 定期テスト学年追加開発
	// global $L_WRITE_TYPE;	   // add ookawara 2012/07/29 // del hirose 2020/11/06 テスト標準化開発 定期テスト
	// add start hirose 2020/11/06 テスト標準化開発 定期テスト
	$test_type1 = new TestStdCfgType1($cdb);
	$L_WRITE_TYPE = $test_type1->getTestUseCourseAdmin();
	// add end hirose 2020/11/06 テスト標準化開発 定期テスト	

	//コース
	//	add ookawara 2012/07/29 start
	$couse_html = "<option value=\"0\">選択して下さい</option>\n";
	foreach ($L_WRITE_TYPE AS $course_num => $course_name) {
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

	//出版社
	$sql  = "SELECT * FROM " . T_MS_PUBLISHING . " WHERE mk_flg='0' AND publishing_id!='0' ORDER BY disp_sort";
	if ($result = $cdb->query($sql)) {
		$publishing_count = $cdb->num_rows($result);
	}
	if (!$publishing_count) {
		$html = "<br>\n";
		$html .= "出版社が存在しません。設定してからご利用下さい。";
		return $html;
	}
	$publishing_html = "<option value=\"0\">選択して下さい</option>\n";
	while ($list = $cdb->fetch_assoc($result)) {
		if ($_SESSION['t_practice']['publishing_id'] == $list['publishing_id']) { $selected = "selected"; } else { $selected = ""; }
		$publishing_html .= "<option value=\"".$list['publishing_id']."\" ".$selected.">".$list['publishing_name']."</option>\n";
	}
	//学年
	// foreach($L_GKNN_LIST_TYPE1 as $key => $val) { // update $L_GKNN_LIST => $L_GKNN_LIST_TYPE1 2015/02/12 yoshizawa	// del karasawa 2020/12/22 定期テスト学年追加開発
	foreach($L_GKNN_LIST_TYPE2[$posted_course_num] as $key => $val) { // add karasawa 2020/12/22 定期テスト学年追加開発
		if ($_SESSION['t_practice']['gknn'] == $key) { $selected = "selected"; } else { $selected = ""; }
		$gknn_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
	}
	//教科書
	if ($_SESSION['t_practice']['course_num'] && $_SESSION['t_practice']['publishing_id'] && $_SESSION['t_practice']['gknn']) {
		$sql  = "SELECT * FROM " . T_MS_BOOK . " ms_book" .
			" WHERE publishing_id='".$_SESSION['t_practice']['publishing_id']."'".
			" AND course_num='".$_SESSION['t_practice']['course_num']."'".
			" AND gknn='".$_SESSION['t_practice']['gknn']."'".
			" AND mk_flg='0' ORDER BY disp_sort";
		if ($result = $cdb->query($sql)) {
			$book_count = $cdb->num_rows($result);
		}
		if (!$book_count) {
			unset($_SESSION['t_practice']['book_id']);
			$book_html = "<option value=\"0\">設定されていません</option>\n";
		} else {
			$book_html = "<option value=\"0\">選択して下さい</option>\n";
			while ($list = $cdb->fetch_assoc($result)) {
				if ($_SESSION['t_practice']['book_id'] == $list['book_id']) { $selected = "selected"; } else { $selected = ""; }
				$book_html .= "<option value=\"".$list['book_id']."\" ".$selected.">".$list['book_name']."</option>\n";
			}
		}
	} else {
		unset($_SESSION['t_practice']['book_id']);
		$book_html .= "<option value=\"\">--------</option>\n";
	}

	$html = "<br>\n";
	 // add start karasawa 2020/04/06 社会定期テスト対応開発
	if($_SESSION['t_practice']['course_num'] == 16){
		$html .= "<span style = \"font-weight: bold;\">【社会のコースを登録する際の諸注意】</span><br>";		// add oda 2020/04/23 社会定期テスト対応開発
		$html .= "<span>※地理を登録/選択する場合は中学1年生、歴史を登録/選択する場合は中学2年生、公民を登録/選択する場合は中学3年生から登録/選択ください</span>";
	}
	 // add end karasawa 2020/04/06 社会定期テスト対応開発
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"menu\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"set_unit_view\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"view_session\">\n";
	$html .= "<table class=\"unit_form\">\n";
	$html .= "<tr class=\"unit_form_menu\">\n";
	$html .= "<td>コース</td>\n";
	$html .= "<td>出版社</td>\n";
	$html .= "<td>学年</td>\n";
	$html .= "<td>教科書</td>\n";
	$html .= "</tr>\n";
	$html .= "<tr class=\"unit_form_cell\">\n";
	$html .= "<td>\n";
	$html .= "<select name=\"course_num\" onchange=\"submit_course();\">".$couse_html."</select>\n";
	$html .= "</td>\n";
	$html .= "<td>\n";
	$html .= "<select name=\"publishing_id\" onchange=\"submit_stage();\">".$publishing_html."</select>\n";
	$html .= "</td>\n";
	$html .= "<td>\n";
	$html .= "<select name=\"gknn\" onchange=\"submit_lesson();\">".$gknn_html."</select>\n";
	$html .= "</td>\n";
	$html .= "<td>\n";
	$html .= "<select name=\"book_id\" onchange=\"submit();\">".$book_html."</select>";
	$html .= "</td>\n";
	$html .= "</tr>\n";
	$html .= "</table>\n";
	$html .= "</form>\n";
	$html .= "<br>\n";

	if (!$_SESSION['t_practice']['course_num'] || !$_SESSION['t_practice']['publishing_id'] || !$_SESSION['t_practice']['gknn']) {
		$html .= "<br>\n";
		$html .= "単元を設定するコース、出版社、学年を選択してください。<br>\n";
	} elseif (!$_SESSION['t_practice']['book_id']) {
		$html .= "<br>\n";
		$html .= "単元を設定する教科書を選択してください。<br>\n";
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

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (($_SESSION['t_practice']['course_num'] != $_POST['course_num'])
	|| ($_SESSION['t_practice']['publishing_id'] != $_POST['publishing_id'])
	|| ($_SESSION['t_practice']['gknn'] != $_POST['gknn'])) {
		unset($_SESSION['t_practice']['book_id']);
		unset($_SESSION['t_practice']['book_name']);
	} elseif(strlen($_POST['book_id'])) {
		$_SESSION['t_practice']['book_id'] = $_POST['book_id'];
		$sql  = "SELECT * FROM " . T_MS_BOOK . " ms_book" . " WHERE book_id='".$_POST['book_id']."' AND mk_flg='0'";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
		}
		$_SESSION['t_practice']['book_name'] = $list['book_name'];
	}

	if (strlen($_POST['course_num'])) { $_SESSION['t_practice']['course_num'] = $_POST['course_num']; }
	if (strlen($_POST['publishing_id'])) { $_SESSION['t_practice']['publishing_id'] = $_POST['publishing_id']; }
	if (strlen($_POST['gknn'])) { $_SESSION['t_practice']['gknn'] = $_POST['gknn']; }

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
function book_unit_list($ERROR) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_PAGE_VIEW,$L_DISPLAY; // del $L_GKNN_LIST 使用していないので 2015/02/12 yoshizawa
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
	$html .= "<span style=\"font-weight:bold;\">教科書 ： ".$_SESSION['t_practice']['book_name']."</span>";

	$html .= "<br>\n";
	$html .= "インポートする場合は、教科書単元csvファイル（S-JIS）を指定しCSVインポートボタンを押してください。<br>\n";
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
		$html .= "<input type=\"hidden\" name=\"book_id\" value=\"".$list['book_id']."\">\n";
		$html .= "<input type=\"submit\" value=\"単元新規登録\">\n";
		$html .= "</form>\n";
	}

	$sql  = "SELECT *" . " FROM " . T_MS_BOOK_UNIT . " WHERE mk_flg='0' AND book_id='".$_SESSION['t_practice']['book_id']."'";
	if ($result = $cdb->query($sql)) {
		$book_unit_count = $cdb->num_rows($result);
	}
	if (!$book_unit_count) {
		$html .= "<br>\n";
		$html .= "今現在登録されている単元は有りません。<br>\n";
		return $html;
	}

	//コードリスト
	$L_CORE_CODE = core_code_list();

	//紐付くLMS単元を取得
	$sql  = "SELECT ".
		"mbu.book_unit_id," .
		"unit.unit_name2," .
		"unit.unit_key" .
		" FROM " .
		T_BOOK_UNIT_LMS_UNIT . " bulu".
		" LEFT JOIN ".T_MS_BOOK_UNIT." mbu ON mbu.book_unit_id=bulu.book_unit_id".
		" AND mbu.mk_flg=0".
		" LEFT JOIN ".T_UNIT." unit ON unit.unit_num=bulu.unit_num".
		" AND unit.state=0".
		" WHERE bulu.mk_flg='0' AND mbu.book_id='".$_SESSION['t_practice']['book_id']."'";
	if ($result = $cdb->query($sql)) {
		$L_LMS_UNIT = array();
		while ($list = $cdb->fetch_assoc($result)) {
			if ($list['unit_key']) { $L_LMS_UNIT[$list['book_unit_id']][] .= $list['unit_key']; }
		}
	}

	$sql  = "SELECT *," .
		"IF(parent_book_unit_id_1 IS NOT NULL," .
		"IF(parent_book_unit_id_2 IS NOT NULL," .
		"IF(parent_book_unit_id_3 IS NOT NULL," .
		"IF(parent_book_unit_id_4 IS NOT NULL," .
		"IF(parent_book_unit_id_5 IS NOT NULL," .
		"IF(parent_book_unit_id_6 IS NOT NULL,6,5)" .
		",4)" .
		",3)" .
		",2)" .
		",1)" .
		",0) AS parent_end_num" .
		" FROM " . T_MS_BOOK_UNIT . " WHERE mk_flg='0' AND book_id='".$_SESSION['t_practice']['book_id']."'".
		" ORDER BY disp_sort;";
	if ($result = $cdb->query($sql)) {
		$html .= "<br>\n";
		$html .= "<table class=\"course_form\">\n";
		$html .= "<tr class=\"course_form_menu\">\n";
		$html .= "<th>教科書単元ID</th>\n";
		$html .= "<th>単元名</th>\n";
		$html .= "<th>3学期制時期</th>\n";
		$html .= "<th>2学期制時期</th>\n";
		$html .= "<th>ページ開始</th>\n";
		$html .= "<th>ページ終了</th>\n";
//		$html .= "<th>LMS単元</th>\n";
		$html .= "<th>表示・非表示</th>\n";
		if (!ereg("practice__view",$authority)
			&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__add",$_SESSION['authority'])===FALSE))
		) {
			$html .= "<th>新規登録</th>\n";
		}
		if (!ereg("practice__view",$authority)
			&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__view",$_SESSION['authority'])===FALSE))
		) {
			$html .= "<th>変更</th>\n";
		}
		if (!ereg("practice__del",$authority)
			&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__del",$_SESSION['authority'])===FALSE))
		) {
			$html .= "<th>削除</th>\n";
		}
		$html .= "</tr>\n";
		while ($list = $cdb->fetch_assoc($result)) {

			//単元名タグ解除
			$list['book_unit_name'] = str_replace("&lt;","<",$list['book_unit_name']);
			$list['book_unit_name'] = str_replace("&gt;",">",$list['book_unit_name']);
			//単元名のパディング計算
			$name_padding = 20 * $list['parent_end_num'];
			//parent_book_unit_idを結合
			$parent_book_unit_id = "";
			for($i = 1;$i <= 6;$i++) {
				if ($list['parent_book_unit_id_'.$i]) {
				//	if ($i != 1) { $parent_book_unit_id .= "_"; }
				//	$parent_book_unit_id .= $list['parent_book_unit_id_'.$i];
				//	$parent_book_unit_id = $list['parent_book_unit_id_'.$i];
				} else {
					break;
				}
			}
			$parent_book_unit_id = $list['book_unit_id'];
/*
			//lms単元の表示形成
			if ($list['unit_end_flg']) {
				if (is_array($L_LMS_UNIT[$list['book_unit_id']])) {
					$lms_unit = implode("<br>",$L_LMS_UNIT[$list['book_unit_id']]);
				} else {
					$lms_unit = "-----";
				}
			} else {
				$lms_unit = "設定できません";
			}
*/
			$term3_kmk_name = "----";
			$term2_kmk_name = "----";
			foreach($L_CORE_CODE as $key => $val) {
				$bnr_num = preg_replace("/[^0-9]/","",$L_CORE_CODE[$key]['bnr_nm']);
				$kmk_name = "term".$bnr_num."_kmk_name";
				$kmk_ccd = "term".$bnr_num."_kmk_ccd";
				$$kmk_name = $L_CORE_CODE[$key]['kmk_nm'][$list[$kmk_ccd]];
			}

			$html .= "<tr class=\"course_form_cell\">\n";
			$html .= "<td>".$list['book_unit_id']."</td>\n";
			$html .= "<td style=\"width:350px; padding-left:".$name_padding.";\">".$list['book_unit_name']."</td>\n";
			$html .= "<td>".$term3_kmk_name."</td>\n";
			$html .= "<td>".$term2_kmk_name."</td>\n";
			$html .= "<td>".$list['page_from']."ページ</td>\n";
			$html .= "<td>".$list['page_to']."ページ</td>\n";
//			$html .= "<td>".$lms_unit."</td>\n";
			$html .= "<td>".$L_DISPLAY[$list['display']]."</td>\n";
			if (!ereg("practice__view",$authority)
				&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__add",$_SESSION['authority'])===FALSE))
			) {
				if ($list['parent_end_num'] < 6) {
					$html .= "<td>\n";
					$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
					$html .= "<input type=\"hidden\" name=\"mode\" value=\"add\">\n";
					$html .= "<input type=\"hidden\" name=\"book_id\" value=\"".$list['book_id']."\">\n";
					$html .= "<input type=\"hidden\" name=\"book_unit_id\" value=\"".$list['book_unit_id']."\">\n";
					$html .= "<input type=\"hidden\" name=\"parent_book_unit_id\" value=\"".$parent_book_unit_id."\">\n";
					$html .= "<input type=\"submit\" value=\"新規登録\">\n";
					$html .= "</form>\n";
					$html .= "</td>\n";
				} else {
					$html .= "<td>&nbsp;</td>\n";
				}
			}
			if (!ereg("practice__view",$authority)
				&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__view",$_SESSION['authority'])===FALSE))
			) {
				$html .= "<td>\n";
				$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
				$html .= "<input type=\"hidden\" name=\"mode\" value=\"view\">\n";
				$html .= "<input type=\"hidden\" name=\"book_id\" value=\"".$list['book_id']."\">\n";
				$html .= "<input type=\"hidden\" name=\"book_unit_id\" value=\"".$list['book_unit_id']."\">\n";
				$html .= "<input type=\"hidden\" name=\"parent_book_unit_id\" value=\"".$parent_book_unit_id."\">\n";
				$html .= "<input type=\"submit\" value=\"変更\">\n";
				$html .= "</form>\n";
				$html .= "</td>\n";
			}
			if (!ereg("practice__del",$authority)
				&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__del",$_SESSION['authority'])===FALSE))
			) {
				$html .= "<td>\n";
				$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
				$html .= "<input type=\"hidden\" name=\"mode\" value=\"delete\">\n";
				$html .= "<input type=\"hidden\" name=\"book_id\" value=\"".$list['book_id']."\">\n";
				$html .= "<input type=\"hidden\" name=\"book_unit_id\" value=\"".$list['book_unit_id']."\">\n";
				$html .= "<input type=\"hidden\" name=\"parent_book_unit_id\" value=\"".$parent_book_unit_id."\">\n";
				$html .= "<input type=\"submit\" value=\"削除\">\n";
				$html .= "</form>\n";
				$html .= "</td>\n";
			}
			$html .= "</tr>\n";
		}
		$html .= "</table>\n";
	}

	return $html;
}


/**
 * 単元　新規登録フォーム
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
	$html .= "<input type=\"hidden\" name=\"book_id\" value=\"".$_POST['book_id']."\">\n";
	$html .= "<input type=\"hidden\" name=\"unit_end_flg\" value=\"1\">\n";
	if (isset($_POST['book_unit_id'])) {
		$html .= "<input type=\"hidden\" name=\"book_unit_id\" value=\"".$_POST['book_unit_id']."\">\n";
	//	$html .= "<input type=\"hidden\" name=\"parent_book_unit_id\" value=\"".$_POST['parent_book_unit_id']."\">\n";
	}
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(TEST_BOOK_UNIT_FORM);

	//コードリスト
	$L_CORE_CODE = core_code_list();

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
	$unit_att = "<br><span style=\"color:red;\">※設定したい単元のユニットキーを入力して下さい。";
	$unit_att .= "<br>※複数設定する場合は、「 <> 」区切りで入力して下さい。</span>";
//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	$INPUTS[BOOKUNITNAME] 	= array('type'=>'text','name'=>'book_unit_name','size'=>'50','value'=>$_POST['book_unit_name']);
	$INPUTS[PAGEFROM] 		= array('type'=>'text','name'=>'page_from','size'=>'5','value'=>$_POST['page_from']);
	$INPUTS[PAGETO] 		= array('type'=>'text','name'=>'page_to','size'=>'5','value'=>$_POST['page_to']);
//	$INPUTS[UNITKEY] 		= array('type'=>'text','name'=>'unit_key','size'=>'50','value'=>$_POST['unit_key']);
//	$INPUTS[UNITNUMATT] 	= array('result'=>'plane','value'=>$unit_att);
	$INPUTS[PARENTBOOKUNITID] = array('type'=>'text','name'=>'parent_book_unit_id','size'=>'5','value'=>$_POST['parent_book_unit_id']);
	$INPUTS[USRBKO] 		= array('type'=>'text','name'=>'usr_bko','size'=>'50','value'=>$_POST['usr_bko']);
	$INPUTS[DISPLAY] 		= array('result'=>'plane','value'=>$display_html);
	foreach($L_CORE_CODE as $key => $val) {
		$bnr_num = preg_replace("/[^0-9]/","",$L_CORE_CODE[$key]['bnr_nm']);
		$html .= "<input type=\"hidden\" name=\"term".$bnr_num."_bnr_ccd\" value=\"".$key."\">\n";
		$INPUTS[$bnr_num.'KMKCCD'] 		= array('type'=>'select','name'=>'term'.$bnr_num.'_kmk_ccd','array'=>$L_CORE_CODE[$key]['kmk_nm'],'check'=>$_POST['term'.$bnr_num.'_kmk_ccd']);
	}

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"追加確認\">\n";
	$html .= "<input type=\"reset\" value=\"クリア\">\n";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"book_unit_list\">\n";
	$html .= "<input type=\"hidden\" name=\"book_id\" value=\"".$_POST['book_id']."\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";
	return $html;
}


/**
 * 単元　修正フォーム
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
		for($j = 1;$j <= 6;$j++) {
			if ($list['parent_book_unit_id_'.$j]) {
				$parent_book_unit_id = $list['parent_book_unit_id_'.$j];
			} else {
				break;
			}
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
	$html .= "<input type=\"hidden\" name=\"book_id\" value=\"".$_POST['book_id']."\">\n";
	$html .= "<input type=\"hidden\" name=\"book_unit_id\" value=\"".$_POST['book_unit_id']."\">\n";
	$html .= "<input type=\"hidden\" name=\"parent_book_unit_id\" value=\"".$_POST['parent_book_unit_id']."\">\n";
	$html .= "<input type=\"hidden\" name=\"unit_end_flg\" value=\"".$unit_end_flg."\">\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(TEST_BOOK_UNIT_FORM);

	//コードリスト
	$L_CORE_CODE = core_code_list();

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
	$unit_att = "設定できません。";
	if ($unit_end_flg) {
		$unit_att = "<br><span style=\"color:red;\">※設定したい単元のユニットキーを入力して下さい。";
		$unit_att .= "<br>※複数設定する場合は、「 <> 」区切りで入力して下さい。</span>";
//		$INPUTS[UNITKEY] 		= array('type'=>'text','name'=>'unit_key','size'=>'50','value'=>$unit_key);
	}
	$INPUTS[BOOKUNITNAME] 	= array('type'=>'text','name'=>'book_unit_name','size'=>'50','value'=>$book_unit_name);
	$INPUTS[PAGEFROM] 		= array('type'=>'text','name'=>'page_from','size'=>'5','value'=>$page_from);
	$INPUTS[PAGETO] 		= array('type'=>'text','name'=>'page_to','size'=>'5','value'=>$page_to);
//	$INPUTS[UNITNUMATT] 	= array('result'=>'plane','value'=>$unit_att);
	$INPUTS[PARENTBOOKUNITID] = array('type'=>'text','name'=>'parent_book_unit_id','size'=>'5','value'=>$parent_book_unit_id);
	$INPUTS[USRBKO] 		= array('type'=>'text','name'=>'usr_bko','size'=>'50','value'=>$usr_bko);
	$INPUTS[DISPLAY] 		= array('result'=>'plane','value'=>$display_html);
	foreach($L_CORE_CODE as $key => $val) {
		$bnr_num = preg_replace("/[^0-9]/","",$L_CORE_CODE[$key]['bnr_nm']);
		$html .= "<input type=\"hidden\" name=\"term".$bnr_num."_bnr_ccd\" value=\"".$key."\">\n";
		$kmk_ccd = "term".$bnr_num."_kmk_ccd";
		$INPUTS[$bnr_num.'KMKCCD'] 		= array('type'=>'select','name'=>'term'.$bnr_num.'_kmk_ccd','array'=>$L_CORE_CODE[$key]['kmk_nm'],'check'=>$$kmk_ccd);
	}

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"変更確認\">";
	$html .= "<input type=\"reset\" value=\"クリア\">";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"book_unit_list\">\n";
	$html .= "<input type=\"hidden\" name=\"book_id\" value=\"".$_POST['book_id']."\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}


/**
 * 単元　新規登録・修正　必須項目チェック
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function check() {

	if (!$_POST['book_unit_name']) { $ERROR[] = "教科書単元名が未入力です。"; }
	if (preg_match("/[^0-9]/",$_POST['page_from'])) {
		$ERROR[] = "教科書開始ページは半角数字で入力してください。";
	} elseif ($_POST['page_from'] < 0) {
		$ERROR[] = "教科書開始ページは0以下の指定はできません。";
	}
	if (preg_match("/[^0-9]/",$_POST['page_to'])) {
		$ERROR[] = "教科書終了ページは半角数字で入力してください。";
	} elseif ($_POST['page_to'] < 0) {
		$ERROR[] = "教科書終了ページは0以下の指定はできません。";
	}
	if (!$_POST['display']) { $ERROR[] = "表示・非表示が未選択です。"; }

	if (mb_strlen($_POST['usr_bko'], 'UTF-8') > 255) { $ERROR[] = "備考が不正です。255文字以内で記述して下さい。"; }

	return $ERROR;
}


/**
 * 単元　新規登録・修正・削除　確認フォーム
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
	//コードリスト
	$L_CORE_CODE = core_code_list();

	if (MODE != "delete") { $button = "登録"; } else { $button = "削除"; }
	$html = "<br>\n";
	$html .= "確認画面：以下の内容で".$button."してもよろしければ".$button."ボタンをクリックしてください。<br>\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(TEST_BOOK_UNIT_FORM);

//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	$unit_att = "設定できません。";
	if ($unit_end_flg) {
		$unit_att = "";
//		$INPUTS[UNITKEY] 		= array('result'=>'plane','value'=>$unit_key);
	}
	$INPUTS[BOOKUNITNAME] 	= array('result'=>'plane','value'=>$book_unit_name);
	$INPUTS[PAGEFROM] 		= array('result'=>'plane','value'=>$page_from);
	$INPUTS[PAGETO] 		= array('result'=>'plane','value'=>$page_to);
//	$INPUTS[UNITNUMATT] 	= array('result'=>'plane','value'=>$unit_att);
	$INPUTS[PARENTBOOKUNITID] = array('result'=>'plane','value'=>$parent_book_unit_id);
	$INPUTS[USRBKO] 		= array('result'=>'plane','value'=>$usr_bko);
	$INPUTS[DISPLAY] 		= array('result'=>'plane','value'=>$L_DISPLAY[$display]);
	foreach($L_CORE_CODE as $key => $val) {
		$bnr_num = preg_replace("/[^0-9]/","",$L_CORE_CODE[$key]['bnr_nm']);
		$html .= "<input type=\"hidden\" name=\"term".$bnr_num."_bnr_ccd\" value=\"".$key."\">\n";
		$kmk_ccd = "term".$bnr_num."_kmk_ccd";
		$INPUTS[$bnr_num.'KMKCCD'] 		= array('result'=>'plane','value'=>$L_CORE_CODE[$key]['kmk_nm'][$$kmk_ccd]);
	}

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

	return $html;
}


/**
 * 単元　新規登録処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function add() {
	//book_unit_idが無い場合 = 完全新規
	//book_unit_idが有る場合 = 単元内新規

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//新規用book_unit_id
	//最大book_unit_idを取得して、+1
	$sql = "SELECT MAX(book_unit_id) AS max_id FROM " . T_MS_BOOK_UNIT . ";";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
	}
	if ($list['max_id']) { $book_unit_id = $list['max_id'] + 1; } else { $book_unit_id = 1; }

	//単元内新規の場合
	if ($_POST['book_unit_id']) {
		//親単元エンドフラグ処理
		$ERROR = add_change_end_flg($_POST['book_unit_id']);
		if ($ERROR) { return $ERROR; }
		//parent_book_unit_id+book_unit_idの値を形成
		$sql = "SELECT ".
		"parent_book_unit_id_1,".
		"parent_book_unit_id_2,".
		"parent_book_unit_id_3,".
		"parent_book_unit_id_4,".
		"parent_book_unit_id_5,".
		"parent_book_unit_id_6".
		" FROM " . T_MS_BOOK_UNIT .
		" WHERE mk_flg='0' AND book_id='".$_SESSION['t_practice']['book_id']."'".
		" AND book_unit_id='".$_POST['parent_book_unit_id']."';";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
		}
		if ($list) {
			for($i = 1;$i <= 6;$i++) {
				if ($list['parent_book_unit_id_'.$i]) {
					$L_PARENT_ID[] = $list['parent_book_unit_id_'.$i];
				} else {
					break;
				}
			}
		}
		$L_PARENT_ID[] = $_POST['book_unit_id'];
		$i = 1;
		//形成した値で最大ソート値取得
		foreach($L_PARENT_ID as $val) {
			if (!$val) { continue; }
			$INSERT_DATA['parent_book_unit_id_'.$i] = $val;
			$where .= " AND parent_book_unit_id_".$i."='".$val."'";
			$i++;
		}
		//まず「parent_book_unit_id + book_unit_id」で最大disp_sort値を取得して、取得できればその値がソート値、
		//できなければ下層単元がないので「book_unit_id」のdisp_sort値を取得して、その値がソート値になる
		$sql = "SELECT MAX(disp_sort) AS max_sort FROM " . T_MS_BOOK_UNIT .
			" WHERE mk_flg='0' AND book_id='".$_SESSION['t_practice']['book_id']."'".$where;
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
		}
		if ($list['max_sort']) {
			$max_sort = $list['max_sort'];
		} else {
			$sql = "SELECT disp_sort AS max_sort FROM " . T_MS_BOOK_UNIT .
				" WHERE mk_flg='0' AND book_unit_id='".$_POST['book_unit_id']."'";
			if ($result = $cdb->query($sql)) {
				$list = $cdb->fetch_assoc($result);
			}
			$max_sort = $list['max_sort'];
		}
	}
	//教科書内単元の最大disp_sort値取得
	$sql = "SELECT MAX(disp_sort) AS max_sort FROM " . T_MS_BOOK_UNIT .
		" WHERE mk_flg='0' AND book_id='".$_SESSION['t_practice']['book_id']."'";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
	}
	if (!$max_sort) { $max_sort = $list['max_sort']; }
	$disp_sort = $max_sort + 1;
	//disp_sort更新処理
	$ERROR = up_disp_sort($disp_sort);
	if ($ERROR) { return $ERROR; }

	$INSERT_DATA[book_unit_id] 		= $book_unit_id;
	$INSERT_DATA[book_id]		 	= $_SESSION['t_practice']['book_id'];
	$INSERT_DATA[book_unit_name] 	= $_POST['book_unit_name'];
	$INSERT_DATA[unit_end_flg] 		= 1;
	$INSERT_DATA[disp_sort] 		= $disp_sort;
	$INSERT_DATA[term3_bnr_ccd] 	= $_POST['term3_bnr_ccd'];
	$INSERT_DATA[term3_kmk_ccd] 	= $_POST['term3_kmk_ccd'];
	$INSERT_DATA[term2_bnr_ccd] 	= $_POST['term2_bnr_ccd'];
	$INSERT_DATA[term2_kmk_ccd] 	= $_POST['term2_kmk_ccd'];
	$INSERT_DATA[page_from] 		= $_POST['page_from'];
	$INSERT_DATA[page_to] 			= $_POST['page_to'];
	$INSERT_DATA[usr_bko] 			= $_POST['usr_bko'];
	$INSERT_DATA[display] 			= $_POST['display'];
//	$INSERT_DATA[ins_syr_id] 		= ;
	$INSERT_DATA[ins_tts_id] 		= $_SESSION['myid']['id'];
	$INSERT_DATA[ins_date] 			= "now()";
	$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];
	$INSERT_DATA[upd_date] 			= "now()";

	$ERROR = $cdb->insert(T_MS_BOOK_UNIT,$INSERT_DATA);
	if ($ERROR) { return $ERROR; }

	//教科書単元とLMS単元の紐付け登録
//	$ERROR = ins_lms_unit($book_unit_id,$_POST['unit_key']);

	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}


/**
 * 単元　修正・削除処理
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
		// 新子　親単元ヘッド抽出
		$sql = "SELECT ".
			"parent_book_unit_id_1,".
			"parent_book_unit_id_2,".
			"parent_book_unit_id_3,".
			"parent_book_unit_id_4,".
			"parent_book_unit_id_5,".
			"parent_book_unit_id_6,".
			"disp_sort".
			" FROM " . T_MS_BOOK_UNIT .
			" WHERE mk_flg='0' AND book_id='".$_POST['book_id']."'".
			" AND book_unit_id='".$_POST['parent_book_unit_id']."';";
//		echo $sql."<hr>";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
		}
		for($i=1;$i<=6;$i++) {
			if ($list['parent_book_unit_id_'.$i]) {
				$L_NEW_PARENT_ID[$i] = $list['parent_book_unit_id_'.$i];
			} else {
				$L_NEW_PARENT_ID[$i] = $_POST['parent_book_unit_id'];
				break;
			}
		}
		$i = 1;
		//形成した値で最大ソート値取得
		for($i=1;$i<=6;$i++) {
			if ($L_NEW_PARENT_ID[$i]) {
				$INSERT_DATA['parent_book_unit_id_'.$i] = $L_NEW_PARENT_ID[$i];
			} else {
				$INSERT_DATA['parent_book_unit_id_'.$i] = "NULL";
			}
		}

		//parent_book_unit_id+book_unit_idの値を形成
		$sql = "SELECT ".
		"parent_book_unit_id_1,".
		"parent_book_unit_id_2,".
		"parent_book_unit_id_3,".
		"parent_book_unit_id_4,".
		"parent_book_unit_id_5,".
		"parent_book_unit_id_6".
		" FROM " . T_MS_BOOK_UNIT .
		" WHERE mk_flg='0' AND book_id='".$_SESSION['t_practice']['book_id']."'".
		" AND book_unit_id='".$_POST['book_unit_id']."';";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
		}
		if ($list) {
			for($i = 1;$i <= 6;$i++) {
				if ($list['parent_book_unit_id_'.$i]) {
					$old_parent_book_unit_id = $list['parent_book_unit_id_'.$i];
				} else {
					break;
				}
			}
		}
		$L_UPD_CHILD_BOOK_UNIT_NUM = array();
		if ($old_parent_book_unit_id != $_POST['parent_book_unit_id']) {
			// 移動先末端book_unit_id抽出
			if ($_POST['parent_book_unit_id']) {
				$sql = "SELECT ".
					"book_unit_id,".
					"disp_sort".
					" FROM " . T_MS_BOOK_UNIT .
					" WHERE mk_flg='0' AND book_id='".$_POST['book_id']."'".
					" AND (parent_book_unit_id_1='".$_POST['parent_book_unit_id']."'".
					" OR parent_book_unit_id_2='".$_POST['parent_book_unit_id']."'".
					" OR parent_book_unit_id_3='".$_POST['parent_book_unit_id']."'".
					" OR parent_book_unit_id_4='".$_POST['parent_book_unit_id']."'".
					" OR parent_book_unit_id_5='".$_POST['parent_book_unit_id']."'".
					" OR parent_book_unit_id_6='".$_POST['parent_book_unit_id']."')".
					" ORDER BY disp_sort DESC LIMIT 1;";
			} else {
				$sql = "SELECT ".
					"book_unit_id,".
					"disp_sort".
					" FROM " . T_MS_BOOK_UNIT .
					" WHERE mk_flg='0' AND book_id='".$_POST['book_id']."'".
					" ORDER BY disp_sort DESC LIMIT 1;";
			}
			if ($result = $cdb->query($sql)) {
				$list = $cdb->fetch_assoc($result);
			}
			if ($list['book_unit_id']) {
				$ins_flg = $list['book_unit_id'];
			}

			// 新parent作成
			$L_UPD_CHILD_BOOK_UNIT_NUM = change_parent($_POST['parent_book_unit_id'],$_POST['book_unit_id'],$_POST['book_id']);
			if (!$L_UPD_CHILD_BOOK_UNIT_NUM) { $L_UPD_CHILD_BOOK_UNIT_NUM = array(); }

			$L_BOOK_UNIT = array();
			// 新disp_sort作成
			$sql = "SELECT ".
				"book_unit_id,".
				"parent_book_unit_id_1,".
				"parent_book_unit_id_2,".
				"parent_book_unit_id_3,".
				"parent_book_unit_id_4,".
				"parent_book_unit_id_5,".
				"parent_book_unit_id_6,".
				"disp_sort".
				" FROM " . T_MS_BOOK_UNIT .
				" WHERE mk_flg='0' AND book_id='".$_POST['book_id']."'".
				" ORDER BY disp_sort;";
			if ($result = $cdb->query($sql)) {
				$i=0;
				while ($list = $cdb->fetch_assoc($result)) {
					$L_BOOK_UNIT_OLD[$list['book_unit_id']] = $list['disp_sort'];
					if($_POST['book_unit_id'] == $list['book_unit_id']) { continue; }
					elseif (array_search($list['book_unit_id'],$L_UPD_CHILD_BOOK_UNIT_NUM) !== false) { continue; }

					$i++;
					$L_BOOK_UNIT[$i] = $list['book_unit_id'];
					if ($ins_flg == $list['book_unit_id']) {
						$i++;
						$L_BOOK_UNIT[$i] = $_POST['book_unit_id'];
						foreach ($L_UPD_CHILD_BOOK_UNIT_NUM as $val)  {
							$i++;
							$L_BOOK_UNIT[$i] = $val;
						}
					}
				}
			}
			// disp_sort更新レコード
			foreach ($L_BOOK_UNIT as $key => $val) {
				if ($key != $L_BOOK_UNIT_OLD[$val]) {
					$L_UPD_DISP_SORT[$val] = $key;
					$UPDATE_DATA['disp_sort']		= $key;
					$UPDATE_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];
					$UPDATE_DATA['upd_date'] 		= "now()";
					$where = " WHERE book_unit_id='".$val."' LIMIT 1;";
					$ERROR = $cdb->update(T_MS_BOOK_UNIT,$UPDATE_DATA,$where);
				}
			}
		} else {
			// parent_unit_id disp_sort更新なし
		}

/*
pre("---- L_BOOK_UNIT_OLD ----");
pre($L_BOOK_UNIT_OLD);
pre("---- L_UPD_CHILD_BOOK_UNIT_NUM ----");
pre($L_UPD_CHILD_BOOK_UNIT_NUM);
pre("---- L_BOOK_UNIT ----");
pre($L_BOOK_UNIT);
pre(count($L_BOOK_UNIT));
pre("---- L_UPD_DISP_SORT ----");
pre($L_UPD_DISP_SORT);
pre(count($L_UPD_DISP_SORT));
*/

		$INSERT_DATA['book_unit_name'] 	= $_POST['book_unit_name'];
		$INSERT_DATA['term3_bnr_ccd'] 	= $_POST['term3_bnr_ccd'];
		$INSERT_DATA['term3_kmk_ccd'] 	= $_POST['term3_kmk_ccd'];
		$INSERT_DATA['term2_bnr_ccd'] 	= $_POST['term2_bnr_ccd'];
		$INSERT_DATA['term2_kmk_ccd'] 	= $_POST['term2_kmk_ccd'];
		$INSERT_DATA['page_from'] 		= $_POST['page_from'];
		$INSERT_DATA['page_to'] 			= $_POST['page_to'];
		$INSERT_DATA['usr_bko'] 			= $_POST['usr_bko'];
		$INSERT_DATA['display'] 			= $_POST['display'];
//		$INSERT_DATA['upd_syr_id'] 		= ;
		$INSERT_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date'] 			= "now()";

		//教科書単元とLMS単元の紐付け更新
//		$ERROR = upd_lms_unit($_POST['book_unit_id'],$_POST['unit_key']);
		if ($ERROR) { return $ERROR; }
	} elseif (MODE == "delete") {
		$ERROR = del_child_book_unit($_POST['parent_book_unit_id'],$_POST['book_unit_id']);
		if ($ERROR) { return $ERROR; }
		$ERROR = del_change_end_flg($_POST['parent_book_unit_id'],$_POST['book_unit_id']);
		if ($ERROR) { return $ERROR; }

		$sql = "SELECT * FROM " . T_MS_BOOK_UNIT . " WHERE book_unit_id='".$_POST['book_unit_id']."' AND mk_flg='0' LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
		}
		$ERROR = down_disp_sort($list['disp_sort']);
		if ($ERROR) { return $ERROR; }

//		$ERROR = del_lms_unit($_POST['book_unit_id']);
		$ERROR = del_lms_unit($_POST['book_unit_id']);		// 2012/07/27 Revival oda
		if ($ERROR) { return $ERROR; }

		$INSERT_DATA[disp_sort] 		= 0;
		$INSERT_DATA[mk_flg] 			= 1;
		$INSERT_DATA[mk_tts_id] 		= $_SESSION['myid']['id'];
		$INSERT_DATA[mk_date] 			= "now()";
	}
	$where = " WHERE book_unit_id='".$_POST['book_unit_id']."' LIMIT 1;";
	$ERROR = $cdb->update(T_MS_BOOK_UNIT,$INSERT_DATA,$where);

	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}


/**
 * 単元　単元登録親単元エンドフラグ処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $book_unit_id
 * @return array エラーの場合
 */
function add_change_end_flg($book_unit_id) {
	//登録する単元の親単元を取得

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql = "SELECT unit_end_flg FROM " . T_MS_BOOK_UNIT . " WHERE book_unit_id=".$book_unit_id." LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
	}
	//unit_end_flgが1であれば0にする
	if ($list['unit_end_flg'] == 0) { return; }

	$INSERT_DATA[unit_end_flg] 		= 0;

	$where = " WHERE book_unit_id='".$book_unit_id."';";
	$ERROR = $cdb->update(T_MS_BOOK_UNIT,$INSERT_DATA,$where);
	if ($ERROR) { return $ERROR; }

	//親単元に紐付くLMS単元を無効化する
//	$ERROR = del_lms_unit($book_unit_id);		// ここは復活しちゃいけない 2012/07/27 oda

	return $ERROR;
}


/**
 * 単元　単元削除親単元エンドフラグ処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $parent_book_unit_id
 * @param integer $book_unit_id
 * @return array エラーの場合
 */
function del_change_end_flg($parent_book_unit_id,$book_unit_id) {
	//親単元に紐付く単元が削除した単元のみであればエンドフラグ更新

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$L_PARENT_ID = explode("_",$parent_book_unit_id);
	$i = 1;
	//形成した値で最大ソート値取得
	foreach($L_PARENT_ID as $val) {
		if (!$val) { continue; }
		$where .= " AND parent_book_unit_id_".$i."='".$val."'";
		$i++;
	}
	$sql = "SELECT COUNT(*) AS unit_count FROM " . T_MS_BOOK_UNIT .
		" WHERE mk_flg='0' AND book_id='".$_SESSION['t_practice']['book_id']."'".
		" AND book_unit_id!='".$book_unit_id."'".$where;
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
	}
	//他にも紐付く単元がある場合は更新しない
	if ($list['unit_count']) { return $ERROR; }

	$INSERT_DATA[unit_end_flg] 		= 1;

	$change_unit_id = end($L_PARENT_ID);
	$where = " WHERE book_unit_id='".$change_unit_id."';";
	$ERROR = $cdb->update(T_MS_BOOK_UNIT,$INSERT_DATA,$where);

	return $ERROR;
}


/**
 * 単元　教科書単元とLMS単元の紐付け登録処理(未使用　2012/07/27）
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $book_unit_id
 * @param mixed $unit_key
 * @return array エラーの場合
 */
function ins_lms_unit($book_unit_id,$unit_key) {
	if (!$_POST['unit_key']) { return; }

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$L_LMS_UNIT = explode("&lt;&gt;",$unit_key);
	$in_unit_key = "'".implode("','",$L_LMS_UNIT)."'";
	$sql  = "SELECT * FROM " . T_UNIT .
		" WHERE state='0' AND unit_key IN (".$in_unit_key.") AND course_num='".$_SESSION['t_practice']['course_num']."'";
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			unset($INSERT_DATA);
			$INSERT_DATA[book_unit_id] 		= $book_unit_id;
			$INSERT_DATA[unit_num] 			= $list['unit_num'];
//			$INSERT_DATA[ins_syr_id] 		= ;
			$INSERT_DATA[ins_tts_id] 		= $_SESSION['myid']['id'];
			$INSERT_DATA[ins_date] 			= "now()";
			$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];
			$INSERT_DATA[upd_date] 			= "now()";

			$ERROR = $cdb->insert(T_BOOK_UNIT_LMS_UNIT,$INSERT_DATA);
			if ($ERROR) { return $ERROR; }
		}
	}

	return $ERROR;
}



/**
 * 単元　教科書単元とLMS単元の紐付け削除処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $book_unit_id
 * @return array エラーの場合
 */
function del_lms_unit($book_unit_id) {
	// (2012/07/27 remodeling oda)

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if ($book_unit_id) {
		// 教科書単元が削除された場合、関連するtest_problemを削除する
		$INSERT_DATA = array();
		$INSERT_DATA['mk_flg'] 			= 1;
		$INSERT_DATA['mk_tts_id'] 		= $_SESSION['myid']['id'];
		$INSERT_DATA['mk_date'] 		= "now()";

		$where = " WHERE book_unit_id = '".$book_unit_id."'";
		$ERROR = $cdb->update(T_BOOK_UNIT_TEST_PROBLEM,$INSERT_DATA,$where);
		if ($ERROR) { return $ERROR; }

		// 教科書単元が削除された場合、関連するlms_unitを削除する
		$INSERT_DATA = array();
		$INSERT_DATA['mk_flg'] 			= 1;
		$INSERT_DATA['mk_tts_id'] 		= $_SESSION['myid']['id'];
		$INSERT_DATA['mk_date'] 		= "now()";

		$where = " WHERE book_unit_id = '".$book_unit_id."'";
		$ERROR = $cdb->update(T_BOOK_UNIT_LMS_UNIT,$INSERT_DATA,$where);
		if ($ERROR) { return $ERROR; }
	}

	return $ERROR;
}


/**
 * 単元　子単元の削除処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $parent_book_unit_id
 * @param integer $book_unit_id
 * @return array エラーの場合
 */
function del_child_book_unit($parent_book_unit_id,$book_unit_id) {
	//下位単元を取得
	//下位単元削除+その単元に紐付くlms単元の削除
	//削除した単元の表示順を0にして表示順下降処理

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//parent_book_unit_id+book_unit_idの値を形成
	$L_PARENT_ID = explode("_",$parent_book_unit_id);
	$L_PARENT_ID[] = $book_unit_id;
	$i = 1;
	//形成した値で最大ソート値取得
	foreach($L_PARENT_ID as $val) {
		if (!$val) { continue; }
		$where .= " AND parent_book_unit_id_".$i."='".$val."'";
		$i++;
	}
	$sql = "SELECT * FROM " . T_MS_BOOK_UNIT .
		" WHERE mk_flg='0' AND book_id='".$_SESSION['t_practice']['book_id']."'".$where;
	if ($result = $cdb->query($sql)) {
		$INSERT_DATA[disp_sort] 		= 0;
		$INSERT_DATA[mk_flg] 			= 1;
		$INSERT_DATA[mk_tts_id] 		= $_SESSION['myid']['id'];
		$INSERT_DATA[mk_date] 			= "now()";
		while ($list = $cdb->fetch_assoc($result)) {
			$ERROR = down_disp_sort($list['disp_sort']);
			if ($ERROR) { return $ERROR; }

			$where = " WHERE book_unit_id='".$list['book_unit_id']."' LIMIT 1;";
			$ERROR = $cdb->update(T_MS_BOOK_UNIT,$INSERT_DATA,$where);
			if ($ERROR) { return $ERROR; }

//			$ERROR = del_lms_unit($list['book_unit_id']);
			$ERROR = del_lms_unit($list['book_unit_id']);	// 2012/07/27 revival oda
			if ($ERROR) { return $ERROR; }
		}
	}

	return $ERROR;
}


/**
 * 単元　表示順上昇処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param string $disp_sort
 * @return array エラーの場合
 */
function up_disp_sort($disp_sort) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql = "UPDATE ".T_MS_BOOK_UNIT.
		" SET disp_sort=disp_sort + 1".
		" WHERE mk_flg='0'" .
		" AND book_id='".$_SESSION['t_practice']['book_id']."'".
		" AND disp_sort>='".$disp_sort."';";
	if (!$cdb->query($sql)) {
		$ERROR[] = "SQL UPDATE ERROR<br>$sql";
	}

	return $ERROR;
}


/**
 * 単元　表示順下降処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param string $disp_sort
 * @return array エラーの場合
 */
function down_disp_sort($disp_sort) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql = "UPDATE ".T_MS_BOOK_UNIT.
		" SET disp_sort=disp_sort - 1".
		" WHERE mk_flg='0'" .
		" AND book_id='".$_SESSION['t_practice']['book_id']."'".
		" AND disp_sort>='".$disp_sort."';";
	if (!$cdb->query($sql)) {
		$ERROR[] = "SQL UPDATE ERROR<br>$sql";
	}

	return $ERROR;
}


/**
 * 単元　紐づけ移動(子)
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $new_parent_book_unit_id
 * @param integer $book_unit_id
 * @param integer $book_id
 * @return array 単元の配列
 */
function change_parent($new_parent_book_unit_id,$book_unit_id,$book_id) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (!$new_parent_book_unit_id) {
		$L_NEW_CHILD_PARENT_ID[1] = $book_unit_id;
	} else {
		// 新子　親単元ヘッド抽出
		$sql = "SELECT ".
			"parent_book_unit_id_1,".
			"parent_book_unit_id_2,".
			"parent_book_unit_id_3,".
			"parent_book_unit_id_4,".
			"parent_book_unit_id_5,".
			"parent_book_unit_id_6,".
			"disp_sort".
			" FROM " . T_MS_BOOK_UNIT .
			" WHERE mk_flg='0' AND book_id='".$book_id."'".
			" AND book_unit_id='".$new_parent_book_unit_id."';";
//		echo $sql."<hr>";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
		}
		if ($list) {
			for($i=1;$i<=6;$i++) {
				if ($list['parent_book_unit_id_'.$i]) {
					$L_NEW_PARENT_ID[$i] = $list['parent_book_unit_id_'.$i];
				} else {
					$L_NEW_PARENT_ID[$i] = $new_parent_book_unit_id;
					break;
				}
			}
			$L_NEW_CHILD_PARENT_ID = $L_NEW_PARENT_ID;
			$L_NEW_CHILD_PARENT_ID[] = $book_unit_id;
		}
	}

	// 子リスト抽出
	$sql = "SELECT ".
		"book_unit_id,".
		"parent_book_unit_id_1,".
		"parent_book_unit_id_2,".
		"parent_book_unit_id_3,".
		"parent_book_unit_id_4,".
		"parent_book_unit_id_5,".
		"parent_book_unit_id_6,".
		"disp_sort".
		" FROM " . T_MS_BOOK_UNIT .
		" WHERE mk_flg='0' AND book_id='".$book_id."'".
		" AND (parent_book_unit_id_1='".$book_unit_id."'".
		" OR parent_book_unit_id_2='".$book_unit_id."'".
		" OR parent_book_unit_id_3='".$book_unit_id."'".
		" OR parent_book_unit_id_4='".$book_unit_id."'".
		" OR parent_book_unit_id_5='".$book_unit_id."'".
		" OR parent_book_unit_id_6='".$book_unit_id."')".
		" ORDER BY disp_sort;";
//	echo $sql."<br><br>";
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$L_NEW_CHILD_PARENT_ID_RESULT = $L_NEW_CHILD_PARENT_ID;
			$j = count($L_NEW_CHILD_PARENT_ID_RESULT);
			$flg = 0;
			for($i=1;$i<=6;$i++) {
				if ($list['parent_book_unit_id_'.$i] == $book_unit_id) {
					$flg = 1;
					continue;
				}
				if ($flg != 1) { continue; }
				if (!$list['parent_book_unit_id_'.$i]) { break; }
				$j++;
				$L_NEW_CHILD_PARENT_ID_RESULT[$j] = $list['parent_book_unit_id_'.$i];
			}
			for($i=1;$i<=6;$i++) {
				if ($L_NEW_CHILD_PARENT_ID_RESULT[$i]) {
					$UPDATE_DATA['parent_book_unit_id_'.$i] = $L_NEW_CHILD_PARENT_ID_RESULT[$i];
				} else {
					$UPDATE_DATA['parent_book_unit_id_'.$i] = "NULL";
				}
			}
			$UPDATE_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];
			$UPDATE_DATA['upd_date'] 		= "now()";
			$where = " WHERE book_unit_id='".$list['book_unit_id']."' LIMIT 1;";
			$ERROR = $cdb->update(T_MS_BOOK_UNIT,$UPDATE_DATA,$where);

			/*
			pre("---- MOVE CHILD LIST  ----");
			echo "<b style=\"color:red;\">".$list['book_unit_id']."</b><br>";
			echo $list['parent_book_unit_id_1']."<br>";
			echo $list['parent_book_unit_id_2']."<br>";
			echo $list['parent_book_unit_id_3']."<br>";
			echo $list['parent_book_unit_id_4']."<br>";
			echo $list['parent_book_unit_id_5']."<br>";
			echo $list['parent_book_unit_id_6']."<br>";
			echo "<b style=\"color:blue;\">".$list['disp_sort']."</b><br>";
			echo "<b style=\"color:blue;\">".$max_sort."</b><br>";
			$end_disp_sort = $list['disp_sort'];
			pre($L_NEW_CHILD_PARENT_ID_RESULT);
			*/
			$L_UPD_CHILD_BOOK_UNIT_NUM[] = $list['book_unit_id'];
		}
	}

/*
	pre("---- NEW PARENT ----");
	pre($L_NEW_PARENT_ID);
	pre("---- L_UPD_CHILD_BOOK_UNIT_NUM ----");
	pre($L_UPD_CHILD_BOOK_UNIT_NUM);
	pre("---- INSERT_DATA ----");
	pre($INSERT_DATA);
*/

	return $L_UPD_CHILD_BOOK_UNIT_NUM;
}

/**
 * 単元　csvエクスポート
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function csv_export() {
	global $L_CSV_COLUMN;

	list($csv_line,$ERROR) = make_csv($L_CSV_COLUMN['book_unit'],1,1);
	if ($ERROR) { return $ERROR; }

	$filename = "ms_book_unit_".$_SESSION['t_practice']['book_id'].".csv";

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
 * 単元　csv出力情報整形
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * 先頭出力文字
 * head_mode	1 カラム名
 * 			2 コメント名
 * 出力範囲
 * csv_mode	1 教科書単位
 * 			2 単元全て
 *
 * @author Azet
 * @param array $L_CSV_COLUMN
 * @param mixed $head_mode='1'
 * @param mixed $csv_mode='1'
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

	$where = "";
	$L_EXPORT_LIST = array();
	if ($csv_mode == 1) { $where = " AND book_id='".$_SESSION['t_practice']['book_id']."'"; }
	$sql  = "SELECT * FROM " . T_MS_BOOK_UNIT .
		" WHERE mk_flg='0'".$where." ORDER BY book_id,disp_sort";
	if ($result = $cdb->query($sql)) {
		$i = 0;
		while ($list = $cdb->fetch_assoc($result)) {
			$i++;
			foreach ($L_CSV_COLUMN as $key => $val) {
				if ($key == "unit_key") {
					continue;
				} elseif ($key == "book_unit_num") {
					$L_EXPORT_LIST[$list['book_unit_id']]['book_unit_num'] = $i;
					$L_PARENT[$list['book_unit_id']] = $i;
					continue;
				} elseif ($key == "disp_sort") {
					$L_EXPORT_LIST[$list['book_unit_id']][$key] = $i;
					continue;
				}
				if ($key == "parent_book_unit_id") {
					for($j = 1;$j <= 6;$j++) {
						if ($list['parent_book_unit_id_'.$j]) {
							$list[$key] = $list['parent_book_unit_id_'.$j];
							if ($L_PARENT[$list['parent_book_unit_id_'.$j]]) {
								$L_EXPORT_LIST[$list['book_unit_id']]['parent_book_unit_num'] = $L_PARENT[$list['parent_book_unit_id_'.$j]];
							}
						} else {
							break;
						}
					}
				}
				$L_EXPORT_LIST[$list['book_unit_id']][$key] = $list[$key];
			}
		}
		$cdb->free_result($result);
	}
	foreach ($L_EXPORT_LIST as $book_unit_id => $array_val) {
		foreach ($L_EXPORT_LIST[$book_unit_id] as $key => $val) {
			$csv_line .= "\"".$val."\",";
		}
		$csv_line .= "\n";
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
 * 単元　csvインポート
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
		$ERROR[] = "教科書単元ファイルが指定されておりません。";
	} elseif (!eregi("(.csv)$",$file_name)) {
		$ERROR[] = "ファイルの拡張子が不正です。";
	} elseif ($file_error == 1) {
		$ERROR[] = "アップロードできるファイルの容量が設定範囲を超えてます。サーバー管理者へ相談してください。";
	} elseif ($file_error == 3) {
		$ERROR[] = "教科書単元ファイルの一部分のみしかアップロードされませんでした。";
	} elseif ($file_error == 4) {
		$ERROR[] = "教科書単元ファイルがアップロードされませんでした。";
	}
	if ($ERROR) {
		if ($file_tmp_name && file_exists($file_tmp_name)) { unlink($file_tmp_name); }
		return $ERROR;
	}

	$ERROR = array();
	//登録教科書読込
//	$L_IMPORT_LINE = file($file_tmp_name);
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

/*
	//１行目＝登録カラム
	$L_LIST_NAME = explode(",",trim($L_IMPORT_LINE[0]));
	if (is_array($L_LIST_NAME)) {
		foreach ($L_LIST_NAME as $key => $val) {
			$L_LIST_NAME[$key] = trim($val);
			$L_LIST_NAME[$key] = replace_encode_sjis($L_LIST_NAME[$key]);
			$L_LIST_NAME[$key] = mb_convert_encoding($L_LIST_NAME[$key],"UTF-8","sjis-win");
		}
	}
*/
	//２行目以降＝登録データを形成
	for ($i = 1; $i < count($L_IMPORT_LINE);$i++) {
		unset($L_VALUE);
		unset($CHECK_DATA);
		unset($INSERT_DATA);

//		$import_line = trim($L_IMPORT_LINE[$i]);

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
//			if ($val === "") { continue; }
			if ($L_LIST_NAME[$key] === "") { continue; }
		//	$val = preg_replace("/^\"|\"$/","",$val);
			$val = trim($val);
			$val = ereg_replace("\"","&quot;",$val);
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
			if ($val == "&quot;") { $val = ""; }
			$val = addslashes($val);
			$CHECK_DATA[$L_LIST_NAME[$key]] = $val;
		}
		if (!isset($CHECK_DATA['book_id'])) { $CHECK_DATA['book_id'] = $_SESSION['t_practice']['book_id']; }
//		if (!$DISP_SORT[$CHECK_DATA['book_id']]) { $DISP_SORT[$CHECK_DATA['book_id']] = 1; }
		$L_ID_NUM[$CHECK_DATA['book_unit_num']] = $CHECK_DATA['book_unit_id'];
//		$CHECK_DATA['disp_sort'] = $DISP_SORT[$CHECK_DATA['book_id']];
/*
		if (!$CHECK_DATA['parent_book_unit_id']) { $PARENT_BOOK_UNIT_ID[$CHECK_DATA['book_unit_id']] = $CHECK_DATA['book_unit_id']; }
		else {
			if ($PARENT_BOOK_UNIT_ID[$CHECK_DATA['parent_book_unit_id']]) {
				$L_PARENT_ID = explode("_",$PARENT_BOOK_UNIT_ID[$CHECK_DATA['parent_book_unit_id']]);
				$j = 1;
				foreach($L_PARENT_ID as $val) {
					if (!$val) { continue; }
					$CHECK_DATA['parent_book_unit_id_'.$j] = $val;
					$j++;
				}
				$PARENT_BOOK_UNIT_ID[$CHECK_DATA['book_unit_id']] = $PARENT_BOOK_UNIT_ID[$CHECK_DATA['parent_book_unit_id']]."_".$CHECK_DATA['book_unit_id'];
			}
		}
*/
		if (!$CHECK_DATA['parent_book_unit_num']) { $PARENT_BOOK_UNIT_NUM[$CHECK_DATA['book_unit_num']] = $CHECK_DATA['book_unit_num']; }
		else {
			if ($PARENT_BOOK_UNIT_NUM[$CHECK_DATA['parent_book_unit_num']]) {
				$L_PARENT_ID = explode("_",$PARENT_BOOK_UNIT_NUM[$CHECK_DATA['parent_book_unit_num']]);
				$j = 1;
				foreach($L_PARENT_ID as $val) {
					if (!$val) { continue; }
					if (!$L_ID_NUM[$val]) {
						$ERROR[] = $i."行目の教科書単元numが不正なのでスキップしました。<br>";
						continue;
					}
					$CHECK_DATA['parent_book_unit_id_'.$j] = $L_ID_NUM[$val];
					$j++;
				}
				$PARENT_BOOK_UNIT_NUM[$CHECK_DATA['book_unit_num']] = $PARENT_BOOK_UNIT_NUM[$CHECK_DATA['parent_book_unit_num']]."_".$CHECK_DATA['book_unit_num'];
			}
		}
//pre("--PARENT_BOOK_UNIT_NUM--");
//pre($PARENT_BOOK_UNIT_ID);
//pre($PARENT_BOOK_UNIT_NUM);
		//レコードがあれば更新なければ新規
		$sql = "SELECT book_id FROM ". T_MS_BOOK_UNIT .
			 " WHERE book_unit_id='".$CHECK_DATA['book_unit_id']."' AND mk_flg='0' LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
		}
		if ($list['book_id']) {
			if ($list['book_id'] == $CHECK_DATA['book_id']) {
				$ins_mode = "upd";
			} else {
				$ERROR[] = $i."行目の教科書単元IDか教科書IDが不正なのでスキップしました。<br>";
				continue;
			}
		} else { $ins_mode = "add"; }
		if (!$CHECK_DATA['display']) {
			$CHECK_DATA['display'] = 1;
		}

		//データチェック
		$DATA_ERROR[$i] = check_data($CHECK_DATA,$ins_mode,$i);
		if ($DATA_ERROR[$i]) { continue; }
		$DISP_SORT[$CHECK_DATA['book_id']]++;
		unset($CHECK_DATA['parent_book_unit_id']);
		unset($CHECK_DATA['parent_book_unit_num']);

		//教科書単元とLMS単元の紐付け更新
//		$SYS_ERROR[$i] = upd_lms_unit($CHECK_DATA['book_unit_id'],$CHECK_DATA['unit_key']);
//		unset($CHECK_DATA['unit_key']);
//pre($CHECK_DATA);

		$INSERT_DATA = $CHECK_DATA;
		//レコードがあればアップデート、無ければインサート
		if ($ins_mode == "add") {
			$sql = "SELECT MAX(book_unit_id) AS max_id FROM " . T_MS_BOOK_UNIT . ";";
			if ($result = $cdb->query($sql)) {
				$list = $cdb->fetch_assoc($result);
			}
			if ($list['max_id']) { $INSERT_DATA['book_unit_id'] = $list['max_id'] + 1; } else { $INSERT_DATA['book_unit_id'] = 1; }

//			$INSERT_DATA[ins_syr_id] 		= ;
			$INSERT_DATA[ins_tts_id] 		= "System";
			$INSERT_DATA[ins_date] 			= "now()";
			$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];
			$INSERT_DATA[upd_date] 			= "now()";

			$book_unit_num = $CHECK_DATA['book_unit_num'];
			unset($INSERT_DATA['book_unit_num']);
			$SYS_ERROR[$i] = $cdb->insert(T_MS_BOOK_UNIT,$INSERT_DATA);
			$new_book_unit_id = $cdb->insert_id();
			$L_ID_NUM[$book_unit_num] = $new_book_unit_id;
		} else {
//			$INSERT_DATA[upd_syr_id] 		= ;
			$INSERT_DATA[upd_tts_id] 		= "System";
			$INSERT_DATA[upd_date] 			= "now()";

			$where = " WHERE book_unit_id='".$INSERT_DATA['book_unit_id']."' LIMIT 1;";
			unset($INSERT_DATA['book_unit_id']);
			unset($INSERT_DATA['book_unit_num']);
			$SYS_ERROR[$i] = $cdb->update(T_MS_BOOK_UNIT,$INSERT_DATA,$where);
		}
		if ($SYS_ERROR[$i]) { $SYS_ERROR[$i][] = $i."行目 上記システムエラーによりスキップしました。<br>"; }
	}
//pre($L_ID_NUM);
//pre($DISP_SORT);
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
 * 単元　csvインポートチェック
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
	$L_CORE_CODE = core_code_list();
	if (!$CHECK_DATA['book_unit_id']) {
		if (!$CHECK_DATA['book_unit_num']) {
			$ERROR[] = $line_num."行目 教科書単元numが未入力です。";
		}
	} else {
		if (preg_match("/[^0-9]/",$CHECK_DATA['book_unit_id'])) {
			$ERROR[] = $line_num."行目 教科書単元IDは数字以外の指定はできません。";
		} elseif ($CHECK_DATA['book_unit_id'] < 1) {
			$ERROR[] = $line_num."行目 教科書単元IDは1以下の指定はできません。";
		}
	}
	if (!$CHECK_DATA['book_id']) { $ERROR[] = $line_num."行目 教科書IDが未入力です。"; }
	else {
		if ($CHECK_DATA['book_id'] != $_SESSION['t_practice']['book_id']) {
			$ERROR[] = $line_num."行目 教科書IDは選択教科書のID「".$_SESSION['t_practice']['book_id']."」以外の指定はできません。";
		}
	}
	if (!$CHECK_DATA['book_unit_name']) { $ERROR[] = $line_num."行目 教科書単元名が未入力です。"; }
	if (!isset($CHECK_DATA['unit_end_flg'])) { $ERROR[] = $line_num."行目 末端フラグが未入力です。"; }
	else {
		if (preg_match("/[^0-9]/",$CHECK_DATA['unit_end_flg'])) {
			$ERROR[] = $line_num."行目 末端フラグは数字以外の指定はできません。";
		} elseif ($CHECK_DATA['unit_end_flg'] < 0 || $CHECK_DATA['unit_end_flg'] > 1) {
			$ERROR[] = $line_num."行目 末端フラグは0（中間）か1（末端）の数字以外の指定はできません。";
		}
	}
/*
	if ($CHECK_DATA['parent_book_unit_id'] && !$CHECK_DATA['parent_book_unit_id_1']) {
		$ERROR[] = $line_num."行目 親単元IDが不正です。".$CHECK_DATA['parent_book_unit_id']."---".$CHECK_DATA['parent_book_unit_id_1'];
	}
*/
	if ($CHECK_DATA['term3_kmk_ccd']) {
		if (preg_match("/[^0-9]/",$CHECK_DATA['term3_kmk_ccd'])) {
			$ERROR[] = $line_num."行目 3学期制時期コードは数字以外の指定はできません。";
		} elseif (array_search($CHECK_DATA['term3_kmk_ccd'],array_keys($L_CORE_CODE['C000000001']['kmk_nm'])) === FALSE) {
			$ERROR[] = $line_num."行目 3学期制時期コードの入力値が不正です。";
		}
	}
	if ($CHECK_DATA['term2_kmk_ccd']) {
		if (preg_match("/[^0-9]/",$CHECK_DATA['term2_kmk_ccd'])) {
			$ERROR[] = $line_num."行目 2学期制時期コードは数字以外の指定はできません。";
		} elseif (array_search($CHECK_DATA['term2_kmk_ccd'],array_keys($L_CORE_CODE['C000000002']['kmk_nm'])) === FALSE) {
			$ERROR[] = $line_num."行目 2学期制時期コードの入力値が不正です。";
		}
	}
	if ($CHECK_DATA['page_from']) {
		if (preg_match("/[^0-9]/",$CHECK_DATA['page_from'])) {
			$ERROR[] = $line_num."行目 教科書開始ページは数字以外の指定はできません。";
		} elseif ($CHECK_DATA['page_from'] < 0) {
			$ERROR[] = $line_num."行目 教科書開始ページは0以下の指定はできません。";
		}
	}
	if ($CHECK_DATA['page_to']) {
		if (preg_match("/[^0-9]/",$CHECK_DATA['page_to'])) {
			$ERROR[] = $line_num."行目 教科書終了ページは数字以外の指定はできません。";
		} elseif ($CHECK_DATA['page_to'] < 0) {
			$ERROR[] = $line_num."行目 教科書終了ページは0以下の指定はできません。";
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
	$CHECK_DATA['disp_sort'] = $line_num;

	if ($ERROR) { $ERROR[] = $line_num."行目 上記入力エラーでスキップしました。<br>"; }
	return $ERROR;
}


/**
 * コアコード一覧
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array
 */
function core_code_list() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$L_CORE_CODE = array();
	$sql  = "SELECT * FROM ".T_MS_CORE_CODE . " WHERE mk_flg='0';";
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			if (!isset($L_CORE_CODE[$list['bnr_cd']]['kmk_nm'])) {
				$L_CORE_CODE[$list['bnr_cd']]['kmk_nm'][] = "選択して下さい";
			}
			$L_CORE_CODE[$list['bnr_cd']]['bnr_nm'] = $list['bnr_nm'];
			$L_CORE_CODE[$list['bnr_cd']]['kmk_nm'][$list['kmk_cd']] = $list['kmk_nm'];
		}
	}
	return $L_CORE_CODE;
}
?>
