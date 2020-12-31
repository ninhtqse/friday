<?
/**
 * ベンチャー・リンク　すらら
 *
 * テスト用プラクティス管理　出版社管理
 *
 * 履歴
 * 2010/12/06 初期設定
 *
 * @author Azet
 */

// hirano

/**
 * HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
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
			if (!$ERROR) { $html .= publishing_list($ERROR); }
			else { $html .= addform($ERROR); }
		} else {
			$html .= addform($ERROR);
		}
	} elseif (MODE == "詳細") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= viewform($ERROR); }
		} elseif (ACTION == "change") {
			if (!$ERROR) { $html .= publishing_list($ERROR); }
			else { $html .= viewform($ERROR); }
		} else {
			$html .= viewform($ERROR);
		}
	} elseif (MODE == "削除") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= viewform($ERROR); }
		} elseif (ACTION == "change") {
			if (!$ERROR) { $html .= publishing_list($ERROR); }
			else { $html .= viewform($ERROR); }
		} else {
			$html .= check_html();
		}
	} else {
		$html .= publishing_list($ERROR);
	}

	return $html;
}


/**
 * 絞り込みメニュー
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
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
	$sub_session_html .= "ソート <select name=\"s_desc\">\n".$s_desc_html."</select>\n";
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
 * POSTに対してSESSION設定
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 */
function sub_session() {
	if (strlen($_POST['s_desc'])) { $_SESSION['sub_session']['s_desc'] = $_POST['s_desc']; }
	if (strlen($_POST['s_page_view'])) { $_SESSION['sub_session']['s_page_view'] = $_POST['s_page_view']; }
	if (strlen($_POST['s_desc'])&&strlen($_POST['s_page_view'])) { unset($_SESSION['sub_session']['s_page']); }
	if (strlen($_POST['s_page'])) { $_SESSION['sub_session']['s_page'] = $_POST['s_page']; }

	return;
}

/**
 * 出版社一覧
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function publishing_list($ERROR) {

	global $L_PAGE_VIEW,$L_DISPLAY;
	//	add 2015/01/07 yoshizawa 課題要望一覧No.400対応
	global $L_EXP_CHA_CODE;
	//-------------------------------------------------
	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//list($me_num,$onetime,$manager_level,$belong_num,$authority) = explode("<>",$_SESSION[myid]);
	if ($authority) { $L_AUTHORITY = explode("::",$authority); }

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= "<br>\n";
	$html .= "インポートする場合は、出版社csvファイル（S-JIS）を指定しCSVインポートボタンを押してください。<br>\n";
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
		$html .= "<input type=\"submit\" value=\"出版社新規登録\">\n";
		$html .= "</form>\n";
	}

	$sql  = "SELECT * FROM " . T_MS_PUBLISHING . " WHERE mk_flg='0' AND publishing_id!=0";
	if ($result = $cdb->query($sql)) {
		$publishing_count = $cdb->num_rows($result);
	}
	if (!$publishing_count) {
		$html .= "<br>\n";
		$html .= "今現在登録されている出版社は有りません。<br>\n";
		return $html;
	}
	$html .= select_menu();

	if ($_SESSION['sub_session']['s_desc']) {
		$sort_key = " DESC";
	} else {
		$sort_key = " ASC";
	}
	$orderby = " ORDER BY disp_sort ".$sort_key;

	if ($L_PAGE_VIEW[$_SESSION['sub_session']['s_page_view']]) { $page_view = $L_PAGE_VIEW[$_SESSION['sub_session']['s_page_view']]; }
	else { $page_view = $L_PAGE_VIEW[0]; }
	$max_page = ceil($publishing_count/$page_view);
	if ($_SESSION['sub_session']['s_page']) { $page = $_SESSION['sub_session']['s_page']; }
	else { $page = 1; }
	$start = ($page - 1) * $page_view;
	$next = $page + 1;
	$back = $page - 1;

	$sql .= $orderby." LIMIT ".$start.",".$page_view.";";

	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);

		$html .= "<br>\n";
		$html .= "修正する場合は、修正する出版社の詳細ボタンを押してください。<br>\n";
		$html .= "<div style=\"float:left;\">登録出版社総数(".$publishing_count."):PAGE[".$page."/".$max_page."]</div>\n";
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
		if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__sort",$_SESSION['authority'])===FALSE)) {
			$html .= "<th>↑</th>\n";
			$html .= "<th>↓</th>\n";
		}
		$html .= "<th>出版社ID</th>\n";
		$html .= "<th>出版社名</th>\n";
		$html .= "<th>表示・非表示</th>\n";
		if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__view",$_SESSION['authority'])===FALSE)) {
			$html .= "<th>詳細</th>\n";
		}
		if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__del",$_SESSION['authority'])===FALSE)) {
			$html .= "<th>削除</th>\n";
		}
		$html .= "</tr>\n";
		$i = 1;
		while ($list = $cdb->fetch_assoc($result)) {
			$up_submit = $down_submit = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			if (!$_SESSION['sub_session']['s_desc']) {
				if ($i != 1 || $page != 1) { $up_submit = "<input type=\"submit\" name=\"action\" value=\"↑\">\n"; }
				if ($i != $max || $page != $max_page) { $down_submit = "<input type=\"submit\" name=\"action\" value=\"↓\">\n"; }
			}

			$html .= "<tr class=\"course_form_cell\">\n";
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"publishing_id\" value=\"".$list['publishing_id']."\">\n";
			if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__sort",$_SESSION['authority'])===FALSE)) {
				$html .= "<td>".$up_submit."</td>\n";
				$html .= "<td>".$down_submit."</td>\n";
			}
			$html .= "<td>".$list['publishing_id']."</td>\n";
			$html .= "<td>".$list['publishing_name']."</td>\n";
			$html .= "<td>".$L_DISPLAY[$list['display']]."</td>\n";
			if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__view",$_SESSION['authority'])===FALSE)) {
				$html .= "<td><input type=\"submit\" name=\"mode\" value=\"詳細\"></td>\n";
			}
			if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__del",$_SESSION['authority'])===FALSE)) {
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
 * 新規登録フォーム
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
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

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(TEST_PUBLISHING_FORM);

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
	$INPUTS[PUBLISHINGID] 	= array('result'=>'plane','value'=>"---");
	$INPUTS[PUBLISHINGNAME] = array('type'=>'text','name'=>'publishing_name','size'=>'50','value'=>$_POST['publishing_name']);
	$INPUTS[USRBKO] 		= array('type'=>'text','name'=>'usr_bko','size'=>'50','value'=>$_POST['usr_bko']);
	$INPUTS[DISPLAY] 		= array('result'=>'plane','value'=>$display_html);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"追加確認\">\n";
	$html .= "<input type=\"reset\" value=\"クリア\">\n";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"publishing_list\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";
	return $html;
}


/**
 * 詳細画面
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function viewform($ERROR) {

	global $L_DISPLAY;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (ACTION) {
		foreach ($_POST as $key => $val) { $$key = $val; }
	} else {
		$sql = "SELECT * FROM ". T_MS_PUBLISHING . " WHERE publishing_id='".$_POST['publishing_id']."' AND mk_flg='0' LIMIT 1;";
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
	$html .= "<input type=\"hidden\" name=\"publishing_id\" value=\"".$publishing_id."\">\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(TEST_PUBLISHING_FORM);

	if (!$publishing_id) { $publishing_id = "---"; }
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
	$INPUTS[PUBLISHINGID] 	= array('result'=>'plane','value'=>$publishing_id);
	$INPUTS[PUBLISHINGNAME] = array('type'=>'text','name'=>'publishing_name','size'=>'50','value'=>$publishing_name);
	$INPUTS[USRBKO] 		= array('type'=>'text','name'=>'usr_bko','size'=>'50','value'=>$usr_bko);
	$INPUTS[DISPLAY] 		= array('result'=>'plane','value'=>$display_html);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"変更確認\">";
	$html .= "<input type=\"reset\" value=\"クリア\">";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"publishing_list\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}

/**
 * 確認する機能
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @return array エラーの場合
 */
function check() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (!$_POST['publishing_name']) {
		$ERROR[] = "出版社名が未入力です。";
	} else {
		if (MODE == "add") {
			$sql  = "SELECT * FROM " . T_MS_PUBLISHING . " WHERE mk_flg='0' AND publishing_name='".$_POST['publishing_name']."'";
		} else {
			$sql  = "SELECT * FROM " . T_MS_PUBLISHING . " WHERE mk_flg='0' AND publishing_id!='".$_POST['publishing_id']."' AND publishing_name='".$_POST['publishing_name']."'";
		}
		if ($result = $cdb->query($sql)) {
			$count = $cdb->num_rows($result);
		}
		if ($count > 0) { $ERROR[] = "入力された出版社名は既に登録されております。"; }
	}
	if (!$_POST['display']) { $ERROR[] = "表示・非表示が未選択です。"; }

	if (mb_strlen($_POST['usr_bko'], 'UTF-8') > 255) { $ERROR[] = "備考が不正です。255文字以内で記述して下さい。"; }

	return $ERROR;
}


/**
 * 確認フォームを作成する機能
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @return string HTMLフォーム
 */
function check_html() {

	global $L_DISPLAY;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

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
		$sql = "SELECT * FROM " . T_MS_PUBLISHING . " WHERE publishing_id='".$_POST['publishing_id']."' AND mk_flg='0' LIMIT 1;";
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
	$make_html->set_file(TEST_PUBLISHING_FORM);

//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	if (!$publishing_id) { $publishing_id = "---"; }
	$INPUTS[PUBLISHINGID] 	= array('result'=>'plane','value'=>$publishing_id);
	$INPUTS[PUBLISHINGNAME] = array('result'=>'plane','value'=>$publishing_name);
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
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"publishing_list\">\n";
	}
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}


/**
 * DB新規登録
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @return array エラーの場合
 */
function add() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql = "SELECT MAX(publishing_id) AS max_id FROM " . T_MS_PUBLISHING . ";";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
	}
	if ($list['max_id']) { $add_id = $list['max_id'] + 1; } else { $add_id = 1; }

	$INSERT_DATA[publishing_id] 	= $add_id;
	$INSERT_DATA[publishing_name] 	= $_POST['publishing_name'];
	$INSERT_DATA[disp_sort] 		= $add_id;
	$INSERT_DATA[usr_bko] 			= $_POST['usr_bko'];
	$INSERT_DATA[display] 			= $_POST['display'];
//	$INSERT_DATA[ins_syr_id] 		= ;
	$INSERT_DATA[ins_tts_id] 		= $_SESSION['myid']['id'];
	$INSERT_DATA[ins_date] 			= "now()";
	$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];
	$INSERT_DATA[upd_date] 			= "now()";

	$ERROR = $cdb->insert(T_MS_PUBLISHING,$INSERT_DATA);

	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}


/**
 * DB更新・削除 処理
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @return array エラーの場合
 */
function change() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (MODE == "詳細") {
		$INSERT_DATA[publishing_name] 	= $_POST['publishing_name'];
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
	$where = " WHERE publishing_id='".$_POST['publishing_id']."' LIMIT 1;";

	$ERROR = $cdb->update(T_MS_PUBLISHING,$INSERT_DATA,$where);

	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}


/**
 * MS_PUBLISHINGを上がる機能
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @return array エラーの場合
 */
function up() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql  = "SELECT * FROM " . T_MS_PUBLISHING . " WHERE publishing_id!='0' AND publishing_id='".$_POST['publishing_id']."' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$m_publishing_id = $list['publishing_id'];
		$m_disp_sort = $list['disp_sort'];
	}
	if (!$m_publishing_id || !$m_disp_sort) { $ERROR[] = "移動する出版社情報が取得できません。"; }

	if (!$ERROR) {
		$sql  = "SELECT * FROM " . T_MS_PUBLISHING . " WHERE mk_flg='0' AND  publishing_id!='0' AND disp_sort<'".$m_disp_sort."' ORDER BY disp_sort DESC LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$c_publishing_id = $list['publishing_id'];
			$c_disp_sort = $list['disp_sort'];
		}
	}
	if (!$c_publishing_id || !$c_disp_sort) { $ERROR[] = "移動される出版社情報が取得できません。"; }

	if (!$ERROR) {
		$INSERT_DATA[disp_sort] 	= $c_disp_sort;
//		$INSERT_DATA[upd_syr_id] 	= ;
		$INSERT_DATA[upd_tts_id] 	= $_SESSION['myid']['id'];
		$INSERT_DATA[upd_date] 		= "now()";
		$where = " WHERE publishing_id='".$m_publishing_id."' LIMIT 1;";

		$ERROR = $cdb->update(T_MS_PUBLISHING,$INSERT_DATA,$where);
	}

	if (!$ERROR) {
		$INSERT_DATA[disp_sort] 	= $m_disp_sort;
//		$INSERT_DATA[upd_syr_id] 	= ;
		$INSERT_DATA[upd_tts_id] 	= $_SESSION['myid']['id'];
		$INSERT_DATA[upd_date] 		= "now()";
		$where = " WHERE publishing_id='".$c_publishing_id."' LIMIT 1;";

		$ERROR = $cdb->update(T_MS_PUBLISHING,$INSERT_DATA,$where);
	}

	return $ERROR;
}


/**
 * MS_PUBLISHINGを下がる機能
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @return array エラーの場合
 */
function down() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql  = "SELECT * FROM " . T_MS_PUBLISHING . " WHERE publishing_id='".$_POST['publishing_id']."' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$m_publishing_id = $list['publishing_id'];
		$m_disp_sort = $list['disp_sort'];
	}
	if (!$m_publishing_id || !$m_disp_sort) { $ERROR[] = "移動する出版社情報が取得できません。"; }

	if (!$ERROR) {
		$sql  = "SELECT * FROM " . T_MS_PUBLISHING . " WHERE mk_flg='0' AND disp_sort>'".$m_disp_sort."' ORDER BY disp_sort LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$c_publishing_id = $list['publishing_id'];
			$c_disp_sort = $list['disp_sort'];
		}
	}
	if (!$c_publishing_id || !$c_disp_sort) { $ERROR[] = "移動される出版社情報が取得できません。"; }

	if (!$ERROR) {
		$INSERT_DATA[disp_sort] = $c_disp_sort;
//		$INSERT_DATA[upd_syr_id] 	= ;
		$INSERT_DATA[upd_tts_id] 	= $_SESSION['myid']['id'];
		$INSERT_DATA[upd_date] 		= "now()";
		$where = " WHERE publishing_id='".$m_publishing_id."' LIMIT 1;";

		$ERROR = $cdb->update(T_MS_PUBLISHING,$INSERT_DATA,$where);
	}

	if (!$ERROR) {
		$INSERT_DATA[disp_sort] = $m_disp_sort;
//		$INSERT_DATA[upd_syr_id] 	= ;
		$INSERT_DATA[upd_tts_id] 	= $_SESSION['myid']['id'];
		$INSERT_DATA[upd_date] 		= "now()";
		$where = " WHERE publishing_id='".$c_publishing_id."' LIMIT 1;";

		$ERROR = $cdb->update(T_MS_PUBLISHING,$INSERT_DATA,$where);
	}

	return $ERROR;
}


/**
 * csvエクスポート
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 */
function csv_export() {
	global $L_CSV_COLUMN;

	list($csv_line,$ERROR) = make_csv($L_CSV_COLUMN['publishing'],1);
	if ($ERROR) { return $ERROR; }

	$filename = "ms_publishing.csv";

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
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @param array $L_CSV_COLUMN
 * @param mixed $head_mode='1'
 * @return array
 */
function make_csv($L_CSV_COLUMN,$head_mode='1') {

	// DB接続オブジェクト
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

	$sql  = "SELECT * FROM " . T_MS_PUBLISHING .
		" WHERE mk_flg='0' AND publishing_id!=0 ORDER BY disp_sort";
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
 * csvインポート
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @return array
 */
function csv_import() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
	$ERROR = array();
	$file_name = $_FILES['import_file']['name'];
	$file_tmp_name = $_FILES['import_file']['tmp_name'];
	$file_error = $_FILES['import_file']['error'];
	if (!$file_tmp_name) {
		$ERROR[] = "出版社ファイルが指定されておりません。";
	} elseif (!eregi("(.csv)$",$file_name)) {
		$ERROR[] = "ファイルの拡張子が不正です。";
	} elseif ($file_error == 1) {
		$ERROR[] = "アップロードできるファイルの容量が設定範囲を超えてます。サーバー管理者へ相談してください。";
	} elseif ($file_error == 3) {
		$ERROR[] = "出版社ファイルの一部分のみしかアップロードされませんでした。";
	} elseif ($file_error == 4) {
		$ERROR[] = "出版社ファイルがアップロードされませんでした。";
	}
	if ($ERROR) {
		if ($file_tmp_name && file_exists($file_tmp_name)) { unlink($file_tmp_name); }
		return $ERROR;
	}

	//登録出版社読込
	$L_IMPORT_LINE = file($file_tmp_name);
	//読込んだら一時ファイル破棄
	unlink($file_tmp_name);
	$ERROR = array();
	//１行目＝登録カラム
	$L_LIST_NAME = explode(",",trim($L_IMPORT_LINE[0]));
	//２行目以降＝登録データを形成
	for ($i = 1; $i < count($L_IMPORT_LINE);$i++) {
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
		unset($L_VALUE);

		//データチェック
		//$DATA_ERROR =array();
		$DATA_ERROR[$i] = check_data($CHECK_DATA,$ins_mode,$i);
		if ($DATA_ERROR[$i]) { continue; }


		$sql = "SELECT * FROM ". T_MS_PUBLISHING .
			 " WHERE publishing_id='".$CHECK_DATA['publishing_id']."' AND mk_flg='0' LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
		}
		if ($list) { $ins_mode = "upd"; } else { $ins_mode = "add"; }

		$INSERT_DATA = $CHECK_DATA;
		unset($CHECK_DATA);
		//レコードがあればアップデート、無ければインサート
		if ($ins_mode == "add") {
//			$INSERT_DATA[ins_syr_id] 		= ;
			$INSERT_DATA[ins_tts_id] 		= "System";
			$INSERT_DATA[ins_date] 			= "now()";
			$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];
			$INSERT_DATA[upd_date] 			= "now()";

			$SYS_ERROR[$i] = $cdb->insert(T_MS_PUBLISHING,$INSERT_DATA);
		} else {
//			$INSERT_DATA[upd_syr_id] 		= ;
			$INSERT_DATA[upd_tts_id] 		= "System";
			$INSERT_DATA[upd_date] 			= "now()";

			$where = " WHERE publishing_id='".$INSERT_DATA['publishing_id']."' LIMIT 1;";
			unset($INSERT_DATA['publishing_id']);
			$SYS_ERROR[$i] = $cdb->update(T_MS_PUBLISHING,$INSERT_DATA,$where);
		}
		if ($SYS_ERROR[$i]) { $SYS_ERROR[$i][] = $i."行目 上記システムエラーによりスキップしました。<br>"; }
		unset($INSERT_DATA);
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

//pre($ERROR);

	return array($html,$ERROR);
}


/**
 * import用入力チェック
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @param array &$CHECK_DATA
 * @param mixed $ins_mode
 * @param integer $line_num
 * @return array エラーの場合
 */
function check_data(&$CHECK_DATA,$ins_mode,$line_num) {
//pre($CHECK_DATA);

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// プライマリキーチェック
	if (!$CHECK_DATA['publishing_id']) {
		// プライマリキーが未設定の場合
		// プライマリキーのMAXを取得
		$sql = "SELECT MAX(publishing_id) AS ins_publishing_id FROM ". T_MS_PUBLISHING .
			 ";";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
		}
		if ($list) {
			// MAX+1
			$CHECK_DATA['publishing_id'] = $list['ins_publishing_id'] + 1;
		} else {
			// エラー？（取得できなければ１を設定で良いのでは？）
			$ERROR[] = $line_num."行目 出版社IDの生成に失敗しました。";
		}
	} else {
		// プライマリキーが設定済の場合
		// 数値のみ指定可能
		if (preg_match("/[^0-9]/",$CHECK_DATA['publishing_id'])) {
			$ERROR[] = $line_num."行目 出版社IDは数字以外の指定はできません。";
		// ?
		} elseif ($CHECK_DATA['publishing_id'] < 1) {
			$ERROR[] = $line_num."行目 出版社IDは2以下の数字の指定はできません。";
		}
	}

	// 出版社チェック
	if (!$CHECK_DATA['publishing_name']) {
		$ERROR[] = $line_num."行目 出版社名が未入力です。";
	} else {
		// 同一名存在チェック
		if (ins_mode == "add") {
			$sql  = "SELECT * FROM " . T_MS_PUBLISHING . " WHERE mk_flg='0' AND publishing_name='".$CHECK_DATA['publishing_name']."'";
		} else {
			$sql  = "SELECT * FROM " . T_MS_PUBLISHING . " WHERE mk_flg='0' AND publishing_id!='".$CHECK_DATA['publishing_id']."' AND publishing_name='".$CHECK_DATA['publishing_name']."'";
		}
		if ($result = $cdb->query($sql)) {
			$count = $cdb->num_rows($result);
		}
		if ($count > 0) { $ERROR[] = $line_num."行目 入力された出版社名は既に登録されております。"; }
	}

	// 表示・非表示チェック
	if (!$CHECK_DATA['display']) {
		$ERROR[] = $line_num."行目 表示・非表示が未入力です。";
	} else {
		// 数値のみ指定可能
		if (preg_match("/[^0-9]/",$CHECK_DATA['display'])) {
			$ERROR[] = $line_num."行目 表示・非表示は数字以外の指定はできません。";
		// 1 or 2 以外はエラー
		} elseif ($CHECK_DATA['display'] < 1 || $CHECK_DATA['display'] > 2) {
			$ERROR[] = $line_num."行目 表示・非表示は1（表示）か2（非表示）の数字以外の指定はできません。";
		}
	}
/*
	if (!$CHECK_DATA['disp_sort']) { $ERROR[] = $line_num."行目 表示順が未入力です。"; }
	else {
		if (preg_match("/[^0-9]/",$CHECK_DATA['disp_sort'])) {
			$ERROR[] = $line_num."行目 表示順は数字以外の指定はできません。";
		} elseif ($CHECK_DATA['disp_sort'] < 2) {
			$ERROR[] = $line_num."行目 表示順は2以下の数字の指定はできません。";
		}
	}
*/
	$CHECK_DATA['disp_sort'] = $line_num;

	if ($ERROR) { $ERROR[] = $line_num."行目 上記入力エラーでスキップしました。<br>"; }
	return $ERROR;
}
?>