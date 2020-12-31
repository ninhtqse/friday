<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * テスト用プラクティス管理　すらら英単語種類 
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
	//	elseif (ACTION == "export") { $ERROR = csv_export(); }
	//	elseif (ACTION == "import") { list($html,$ERROR) = csv_import(); }
	}

	if (MODE == "add") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= addform($ERROR); }
		} elseif (ACTION == "add") {
			if (!$ERROR) { $html .= word_test_list($ERROR); }
			else { $html .= addform($ERROR); }
		} else {
			$html .= addform($ERROR);
		}
	} elseif (MODE == "詳細") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= viewform($ERROR); }
		} elseif (ACTION == "change") {
			if (!$ERROR) { $html .= word_test_list($ERROR); }
			else { $html .= viewform($ERROR); }
		} else {
			$html .= viewform($ERROR);
		}
	} elseif (MODE == "削除") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= viewform($ERROR); }
		} elseif (ACTION == "change") {
			if (!$ERROR) { $html .= word_test_list($ERROR); }
			else { $html .= viewform($ERROR); }
		} else {
			$html .= check_html();
		}
	} else {
		$html .= word_test_list($ERROR);
	}

	return $html;
}

/**
 * SESSION情報設定
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * 教科書　絞り込みメニューセッション操作
 * @author Azet
 */
function sub_session() {

	if (strlen($_POST['s_page'])) { $_SESSION['t_practice']['s_page'] = $_POST['s_page']; }

	return;
}

/**
 * すらら英単語種類一覧
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function word_test_list($ERROR) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_WRITE_TYPE,$L_DISPLAY;
	
	define ( 'VIEW_LIST_ROW' , 20 );

	//-------------------------------------------------

	if ($authority) { $L_AUTHORITY = explode("::",$authority); }

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__add",$_SESSION['authority'])===FALSE)) {
		$html .= "<br>\n";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"add\">\n";
		$html .= "<input type=\"submit\" value=\"すらら英単語種類新規登録\">\n";
		$html .= "</form>\n";
	}

	$sql  = "SELECT * FROM " . T_MS_TEST_TYPE .
			" WHERE mk_flg='0'";
	//echo $sql;
	if ($result = $cdb->query($sql)) {
		$test_count = $cdb->num_rows($result);
	}

	if (!$test_count) {
		$html .= "<br>\n";
		$html .= "今現在登録されているすらら英単語種類は有りません。<br>\n";
		return $html;
	}
	
	$orderby = " ORDER BY list_num ";
	
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
		$html .= "修正する場合は、修正するすらら英単語種類の詳細ボタンを押してください。<br>\n";
		$html .= "<div style=\"float:left;\">登録テスト種類総数(".$test_count."):PAGE[".$page."/".$max_page."]</div>\n";
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
		
			//add start hirose 2018/11/26 すらら英単語　ディレクトリ作成
			$temp_path = STUDENT_VOCABLARY_TEMP_DIR . $list['write_type'] . "/";
			if (!file_exists($temp_path)) {
					@mkdir($temp_path, 0777);
					@chmod($temp_path, 0777);
				}
			$material_temp_path = MATERIAL_TEMP_DIR . "vocabulary/". $list['write_type'] ."/";
				if (!file_exists($material_temp_path)) {
					@mkdir($material_temp_path,0777);
					@chmod($material_temp_path,0777);
				}
			$button_path = "../student/images/button/vocabulary/". $list['write_type'] ."/";
			if (!file_exists($button_path)) {
					@mkdir($button_path,0777);
					@chmod($button_path,0777);
				}
			//add end hirose 2018/11/26 すらら英単語　ディレクトリ作成
		
		
			$up_submit = $down_submit = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			if ($i != 1 || $page != 1) { $up_submit = "<input type=\"submit\" name=\"action\" value=\"↑\">\n"; }
			if ($i != $max || $page != $max_page) { $down_submit = "<input type=\"submit\" name=\"action\" value=\"↓\">\n"; }

			$html .= "<tr class=\"course_form_cell\">\n";
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"test_type_num\" value=\"".$list['test_type_num']."\">\n";
			if (!ereg("practice__view",$authority)
				&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__sort",$_SESSION['authority'])===FALSE))
			) {
				$html .= "<td>".$up_submit."</td>\n";
				$html .= "<td>".$down_submit."</td>\n";
			}
			$html .= "<td>".$list['test_type_num']."</td>\n";
			$html .= "<td>".$list['test_type_name']."</td>\n";
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
 * すらら英単語種類　新規登録フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function addform($ERROR) {
	global $L_WRITE_TYPE,$L_DISPLAY; 
	
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
	$make_html->set_file(TANGO_TEST_TYPE_FORM);

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
	$INPUTS['TESTTYPEID'] 		= array('result'=>'plane','value'=>"---");
	$INPUTS['WRITETYPENUM'] 		= array('type'=>'select','name'=>'write_type','array'=>$L_WRITE_TYPE,'check'=>$_POST['write_type']);
	$INPUTS['TESTTYPENAME'] 		= array('type'=>'text','name'=>'test_type_name','size'=>'50','value'=>$_POST['test_type_name']);
	$INPUTS['REMARKS'] 		= array('result'=>'form', 'type'=>'textarea','name'=>'remarks', 'cols'=>40, 'rows'=>4, 'value'=>$_POST['remarks']); //update kimura 2018/10/31 すらら英単語 usr_bko -> remarks, text -> textarea
	$INPUTS['DISPLAY'] 		= array('result'=>'plane','value'=>$display_html);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"追加確認\">\n";
	$html .= "<input type=\"reset\" value=\"クリア\">\n";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"word_test_list\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";
	return $html;
}


/**
 * すらら英単語種類　修正フォーム
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

	global $L_WRITE_TYPE,$L_DISPLAY; 

	if (ACTION) {
		foreach ($_POST as $key => $val) { $$key = $val; }
	} else {
		$sql = "SELECT * FROM " . T_MS_TEST_TYPE . " WHERE test_type_num='".$_POST['test_type_num']."' AND mk_flg='0' LIMIT 1;";
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

	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";
	$html .= "<input type=\"hidden\" name=\"test_type_num\" value=\"".$test_type_num."\">\n";
	$html .= "<input type=\"hidden\" name=\"write_type\" value=\"".$write_type."\">\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(TANGO_TEST_TYPE_FORM);

	if (!$test_type_num) { $test_type_num = "---"; }
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

	$INPUTS['TESTTYPEID'] 		= array('result'=>'plane','value'=>$test_type_num);
	$INPUTS['WRITETYPENUM'] 		= array('type'=>'select','name'=>'write_type','array'=>$L_WRITE_TYPE,'check'=>$write_type);
	$INPUTS['TESTTYPENAME'] 		= array('type'=>'text','name'=>'test_type_name','size'=>'50','value'=>$test_type_name);
	$INPUTS['REMARKS'] 		= array('result'=>'form', 'type'=>'textarea','name'=>'remarks', 'cols'=>40, 'rows'=>4, 'value'=>$remarks); //update kimura 2018/10/31 すらら英単語 usr_bko -> remarks, text -> textarea
	$INPUTS['DISPLAY'] 		= array('result'=>'plane','value'=>$display_html);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"変更確認\">";
	$html .= "<input type=\"reset\" value=\"クリア\">";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"word_test_list\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}


/**
 * すらら英単語種類　新規登録・修正　必須項目チェック
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function check() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (!$_POST['test_type_name']) { $ERROR[] = "すらら英単語名が未入力です。"; }
	else {
		if (MODE == "add") {
			$sql  = "SELECT * FROM " . T_MS_TEST_TYPE . " WHERE mk_flg='0'".
				" AND write_type='".$_POST['write_type']."'".
				" AND test_type_name='".$_POST['test_type_name']."'";

		} else {
			$sql  = "SELECT * FROM " . T_MS_TEST_TYPE . " WHERE mk_flg='0'".
				" AND write_type='".$_POST['write_type']."'".
				" AND test_type_num!='".$_POST['test_type_num']."' AND test_type_name='".$_POST['test_type_name']."'";
		}
		if ($result = $cdb->query($sql)) {
			$count = $cdb->num_rows($result);
		}
		if ($count > 0) { $ERROR[] = "入力されたすらら英単語名は既に登録されております。"; }
	}
	if (!$_POST['write_type']) { $ERROR[] = "教科が未選択です。"; }
	if (!$_POST['display']) { $ERROR[] = "表示・非表示が未選択です。"; }

	// if (mb_strlen($_POST['usr_bko'], 'UTF-8') > 255) { $ERROR[] = "備考が不正です。255文字以内で記述して下さい。"; } //del kimura 2018/10/31 すらら英単語

	return $ERROR;
}


/**
 * すらら英単語種類　新規登録・修正・削除　確認フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function check_html() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_WRITE_TYPE,$L_DISPLAY;

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
		$sql = "SELECT * FROM " . T_MS_TEST_TYPE . " WHERE test_type_num='".$_POST['test_type_num']."' AND mk_flg='0' LIMIT 1;";
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
	$make_html->set_file(TANGO_TEST_TYPE_FORM);

//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	if (!$book_id) { $book_id = "---"; }
	$INPUTS['TESTTYPEID'] 		= array('result'=>'plane','value'=>$test_type_num);
	$INPUTS['WRITETYPENUM'] 		= array('result'=>'plane','value'=>$L_WRITE_TYPE[$write_type]);
	$INPUTS['TESTTYPENAME'] 		= array('result'=>'plane','value'=>$test_type_name);
	$INPUTS['REMARKS'] 		= array('result'=>'plane','value'=>$remarks); //update kimura 2018/10/31 すらら英単語 usr_bko -> remarks
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
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"word_test_list\">\n";
	}
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}


/**
 * すらら英単語種類　新規登録処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function add() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql = "SELECT MAX(test_type_num) AS max_id FROM " . T_MS_TEST_TYPE . ";";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
	}
	if ($list['max_id']) { $add_id = $list['max_id'] + 1; } else { $add_id = 1; }

	$INSERT_DATA['test_type_num'] 	= $add_id;
	$INSERT_DATA['test_type_name'] 	= $_POST['test_type_name'];
	$INSERT_DATA['write_type'] 		= $_POST['write_type'];
	$INSERT_DATA['list_num'] 		= $add_id;
	$INSERT_DATA['remarks'] 		= $_POST['remarks']; //update kimura 2018/10/31 すらら英単語 usr_bko -> remarks
	$INSERT_DATA['display'] 		= $_POST['display'];
	$INSERT_DATA['ins_tts_id'] 		= $_SESSION['myid']['id'];
	$INSERT_DATA['ins_date'] 			= "now()";
	$INSERT_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];
	$INSERT_DATA['upd_date'] 			= "now()";

	$ERROR = $cdb->insert(T_MS_TEST_TYPE,$INSERT_DATA);

	if (!$ERROR) { $_SESSION['select_menu'] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}


/**
 * すらら英単語種類　修正・削除処理
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
		$INSERT_DATA['test_type_num'] 		= $_POST['test_type_num'];
		$INSERT_DATA['test_type_name'] 		= $_POST['test_type_name'];
		$INSERT_DATA['write_type'] 			= $_POST['write_type'];
		$INSERT_DATA['remarks'] 			= $_POST['remarks']; //update kimura 2018/10/31 すらら英単語 usr_bko -> remarks
		$INSERT_DATA['display'] 			= $_POST['display'];
		$INSERT_DATA['upd_tts_id'] 			= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date'] 			= "now()";
	} elseif (MODE == "削除") {
		$INSERT_DATA['mk_flg'] 				= 1;
		$INSERT_DATA['mk_tts_id'] 		=	 $_SESSION['myid']['id'];
		$INSERT_DATA['mk_date'] 			= "now()";
	}
	$where = " WHERE test_type_num='".$_POST['test_type_num']."' LIMIT 1;";
	$ERROR = $cdb->update(T_MS_TEST_TYPE,$INSERT_DATA,$where);

	if (!$ERROR) { $_SESSION['select_menu'] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}


/**
 * すらら英単語種類　表示順上昇処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function up() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql  = "SELECT * FROM " . T_MS_TEST_TYPE . " WHERE test_type_num='".$_POST['test_type_num']."' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$m_test_type_num = $list['test_type_num'];
		$m_list_num = $list['list_num'];
	}
	if (!$m_test_type_num || !$m_list_num) { $ERROR[] = "移動するすらら英単語情報が取得できません。"; }

	if (!$ERROR) {
		$sql  = "SELECT * FROM " . T_MS_TEST_TYPE . " WHERE mk_flg='0' ".
			" AND list_num < '".$m_list_num."' ORDER BY list_num DESC LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$c_test_type_num = $list['test_type_num'];
			$c_list_num = $list['list_num'];
		}
	}
	if (!$c_test_type_num || !$c_list_num) { $ERROR[] = "移動されるすらら英単語情報が取得できません。"; }

	if (!$ERROR) {
		$INSERT_DATA['list_num'] 	= $c_list_num;
		$INSERT_DATA['upd_tts_id'] 	= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date'] 		= "now()";
		$where = " WHERE test_type_num='".$m_test_type_num."' LIMIT 1;";
		$ERROR = $cdb->update(T_MS_TEST_TYPE,$INSERT_DATA,$where);
	}

	if (!$ERROR) {
		$INSERT_DATA['list_num'] 	= $m_list_num;
		$INSERT_DATA['upd_tts_id'] 	= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date'] 		= "now()";
		$where = " WHERE test_type_num='".$c_test_type_num."' LIMIT 1;";
		$ERROR = $cdb->update(T_MS_TEST_TYPE,$INSERT_DATA,$where);
	}

	return $ERROR;
}

/**
 * すらら英単語種類　表示順下降処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function down() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql  = "SELECT * FROM " . T_MS_TEST_TYPE . " WHERE test_type_num='".$_POST['test_type_num']."' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$m_test_type_num = $list['test_type_num'];
		$m_list_num = $list['list_num'];
	}
	if (!$m_test_type_num || !$m_list_num) { $ERROR[] = "移動するすらら英単語情報が取得できません。"; }

	if (!$ERROR) {
		$sql  = "SELECT * FROM " . T_MS_TEST_TYPE . " WHERE mk_flg='0' ".
			" AND list_num > '".$m_list_num."' ORDER BY list_num LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$c_test_type_num = $list['test_type_num'];
			$c_list_num = $list['list_num'];
		}
	}
	if (!$c_test_type_num || !$c_list_num) { $ERROR[] = "移動されるすらら英単語情報が取得できません。";}
	if (!$ERROR) {
		$INSERT_DATA['list_num'] 	= $c_list_num;
		$INSERT_DATA['upd_tts_id'] 	= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date'] 		= "now()";
		$where = " WHERE test_type_num='".$m_test_type_num."' LIMIT 1;";
		$ERROR = $cdb->update(T_MS_TEST_TYPE,$INSERT_DATA,$where);
	}

	if (!$ERROR) {
		$INSERT_DATA['list_num'] 	= $m_list_num;
		$INSERT_DATA['upd_tts_id'] 	= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date'] 		= "now()";
		$where = " WHERE test_type_num='".$c_test_type_num."' LIMIT 1;";
		$ERROR = $cdb->update(T_MS_TEST_TYPE,$INSERT_DATA,$where);
	}

	return $ERROR;
}

?>
