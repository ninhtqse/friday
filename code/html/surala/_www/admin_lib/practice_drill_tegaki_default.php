<?
/**
 * 手書きデフォルト設定
 *
 * 履歴
 * 2018/05/02
 *
 * @author Azet
 */


/**
 * メイン処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */

function start() {

	// チェック処理
	if (ACTION == "check") {
		$ERROR = check();
	}

	// 登録・修正・削除
	if (!$ERROR) {
		if (ACTION == "add") { $ERROR = add(); }
		elseif (ACTION == "change") { $ERROR = change(); }
		elseif (ACTION == "del") { $ERROR = change(); }
	}

	// 登録処理
	if (MODE == "add") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= addform($ERROR); }
		} elseif (ACTION == "add") {
			if (!$ERROR) { $html .= drill_tegaki_default_list($ERROR); }
			else { $html .= addform($ERROR); }
		} else {
			$html .= addform($ERROR);
		}

	// 詳細画面遷移
	} elseif (MODE == "詳細") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= viewform($ERROR); }
		} elseif (ACTION == "change") {
			if (!$ERROR) { $html .= drill_tegaki_default_list($ERROR); }
			else { $html .= viewform($ERROR); }
		} else {
			$html .= viewform($ERROR);
		}

	// 削除処理
	} elseif (MODE == "削除") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= viewform($ERROR); }
		} elseif (ACTION == "change") {
			if (!$ERROR) { $html .= drill_tegaki_default_list($ERROR); }
			else { $html .= viewform($ERROR); }
		} else {
			$html .= check_html();
		}
	// 一覧表示
	} else {
		$html .= drill_tegaki_default_list($ERROR);
	}

	return $html;
}


/**
 * 一覧表示処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function drill_tegaki_default_list($ERROR) {

	// グローバル変数
	global $L_TEGAKI_PARAM;
	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// エラーメッセージが存在する場合、メッセージ表示
	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}
	// 権限をチェック
	if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__add",$_SESSION['authority'])===FALSE)) {

		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"add\">\n";
		$html .= "<input type=\"submit\" value=\"新規登録\">\n";
		$html .= "</form>\n";
		$html .= "<br />\n";
	}

	// 一覧取得SQL作成
	$sql  = "SELECT td.*, c.course_name FROM " . T_DRILL_TEGAKI_DEFAULT ." td".
			" INNER JOIN ".T_COURSE ." c ON c.course_num = td.course_num" .
			"   AND c.display = '1'" .
			"   AND c.state = '0'" .
			" WHERE td.mk_flg ='0' ".
			"   ORDER BY td.default_tegaki_num;";

	// データ読み込み
	if ($result = $cdb->query($sql)) {

		$max = $cdb->num_rows($result);
		if (!$max) {
			$html .= "今現在登録されている手書きデフォルト設定はありません。<br>\n";
			return $html;
		}

		$html .= "<table class=\"drill_tegaki_default_form\">\n";
		$html .= "<tr class=\"drill_tegaki_default_form_menu\">\n";

		$html .= "<th>登録番号</th>\n";
		$html .= "<th style=\"padding:0 50px;\">コース名</th>\n";
		$html .= "<th>学年</th>\n";
		$html .= "<th>ドリル手書きVer</th>\n";
		$html .= "<th>縦横</th>\n";
		$html .= "<th>文字認識</th>\n";
		$html .= "<th>表示位置</th>\n";
		$html .= "<th>文字認識タイプ</th>\n";
		$html .= "<th>ソート・スムージング</th>\n";
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

		// 明細表示
		$i = 1;
		while ($list = $cdb->fetch_assoc($result)) {

			$gknn = "--";
			$version = "　";
			$view_mode = "　";
			$single_mode = "--";
			$pos_type = "　";
			$recog_type = "　";
			$sort_smooth = "--";

			if ($list['version'] > 0) {
				$gknn = $L_TEGAKI_PARAM['gknn'][$list['gknn']];
				$version = $L_TEGAKI_PARAM['version'][$list['version']];
				$view_mode = $L_TEGAKI_PARAM['view_mode'][$list['view_mode']];
				if ($list['version'] == 2) {
					$single_mode = $L_TEGAKI_PARAM['single_mode'][$list['single_mode']];
				}
				$pos_type = $L_TEGAKI_PARAM['pos_type'][$list['view_mode']][$list['pos_type']];
				$recog_type = $L_TEGAKI_PARAM['recog_type'][$list['version']][$list['recog_type']];
				if ($list['version'] == 3) {
					$sort_smooth = $L_TEGAKI_PARAM['sort_flg'][$list['sort_flg']]."　".$L_TEGAKI_PARAM['smooth_flg'][$list['smooth_flg']];
				}
			}

			$html .= "<tr class=\"drill_tegaki_default_cell\">\n";
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"default_tegaki_num\" value=\"".$list['default_tegaki_num']."\">\n";
			$html .= "<td>".$list['default_tegaki_num']."</td>\n";
			$html .= "<td>".$list['course_name']."</td>\n";
			$html .= "<td>".$gknn."</td>\n";
			$html .= "<td>".$version."</td>\n";
			$html .= "<td>".$view_mode."</td>\n";
			$html .= "<td>".$single_mode."</td>\n";
			$html .= "<td>".$pos_type."</td>\n";
			$html .= "<td>".$recog_type."</td>\n";
			$html .= "<td>".$sort_smooth."</td>\n";

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
 * 新規登録フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function addform($ERROR) {

	// グローバル変数
	global $L_TEGAKI_PARAM;
	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$html .= "新規登録フォーム<br>\n";

	// エラーメッセージ表示
	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$L_COURSE = array();
	$L_COURSE[0] = "選択してください。";

	$sql = " SELECT * FROM ".T_COURSE." WHERE display='1' AND state='0' ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		while($list = $cdb->fetch_assoc($result)) {
			$L_COURSE[$list['course_num']] = $list['course_name'];
		}
	}

	// 入力画面表示
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" name=\"addform\" id=\"addform\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"".MODE."\">\n";

	// htmlクラス生成
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(DRILL_TEGAKI_DEFAULT_FORM);


	$course_num = 1;
	$gknn = "J";
	$version = 1;
	$view_mode = 2;
	$single_mode = 0;
	$pos_type = 1;
	$recog_type = 0;
	$sort_flg = 0;
	$smooth_flg = 0;
	$surala = array(1, 2, 3, 15, 16); //add kimura 2019/10/31 Core_理社対応
	if ($_POST['course_num'] != "") { $course_num = $_POST['course_num']; }
	if ($_POST['gknn'] != "") {
		$gknn = $_POST['gknn'];
		if($course_num == 1 && ($gknn == "L" || $gknn == "P")) {
			unset($_POST['gknn']);
			$gknn = "J";
		}
	}
	if ($_POST['version'] != "") { $version = $_POST['version']; }
	if ($_POST['view_mode'] != "") { $view_mode = $_POST['view_mode']; }
	if ($_POST['single_mode'] != "") { $single_mode = $_POST['single_mode']; }
	if ($_POST['pos_type'] != "") { $pos_type = $_POST['pos_type']; }
	if ($_POST['recog_type'] != "") { $recog_type = $_POST['recog_type']; }
	if ($_POST['sort_flg'] != "") { $sort_flg = $_POST['sort_flg']; }
	if ($_POST['smooth_flg'] != "") { $smooth_flg = $_POST['smooth_flg']; }

	if($course_num == 1) {
		unset($L_TEGAKI_PARAM['gknn']['L']);
		unset($L_TEGAKI_PARAM['gknn']['P']);
	} 
	if ($version == 1 || $version == 2) {
		unset($_POST['sort_flg']);
		unset($_POST['smooth_flg']);
	}
	if ($version != 2) {
		unset($_POST['single_mode']);
	}


	$INPUTS['DEFAULTTEGAKINUM'] = array('result'=>'plane','value'=>"---");
	$INPUTS['COURSENAME'] = array('type'=>'select','name'=>'course_num','array'=>$L_COURSE, 'check'=>$course_num,'action'=>'onchange="select_change_submit(\'addform\', \''.MODE.'\', \'\');return false;"');
	// if ($course_num == 1 || $course_num == 2 || $course_num == 3) { //del kimura 2019/10/31 Core_理社対応
	if (in_array($course_num, $surala)) { //add kimura 2019/10/31 Core_理社対応
		$INPUTS['GKNN'] = array('type'=>'select','name'=>'gknn','array'=>$L_TEGAKI_PARAM['gknn'],'check'=>$gknn);
	} else {
		// すらら以外は学年を選択させない
		$INPUTS['GKNN'] = array('result'=>'plane','value'=>"---");
	}
	$INPUTS['VERSION'] = array('type'=>'select','name'=>'version','array'=>$L_TEGAKI_PARAM['version'], 'check'=>$version,'action'=>'onchange="select_change_submit(\'addform\', \''.MODE.'\',  \'\');return false;"');
	$INPUTS['VIEWMODE'] = array('type'=>'select','name'=>'view_mode','array'=>$L_TEGAKI_PARAM['view_mode'], 'check'=>$view_mode,'action'=>'onchange="select_change_submit(\'addform\', \''.MODE.'\',  \'\');return false;"');

	if ($version == 2) {
		$INPUTS['SINGLEMODE'] = array('type'=>'select','name'=>'single_mode','array'=>$L_TEGAKI_PARAM['single_mode'], 'check'=>$single_mode);
	} else {
		$INPUTS['SINGLEMODE'] = array('result'=>'plane','value'=>"---");
	}

	$INPUTS['POSTYPE'] = array('type'=>'select','name'=>'pos_type','array'=>$L_TEGAKI_PARAM['pos_type'][$view_mode], 'check'=>$pos_type);
	$INPUTS['RECOGTYPE'] = array('type'=>'select','name'=>'recog_type','array'=>$L_TEGAKI_PARAM['recog_type'][$version], 'check'=>$recog_type);

	$sort_value = "---";
	$smooth_value = "---";

	if ($version == 3) {
		// ソートラジオボタン生成
		$newform = new form_parts();
		$newform->set_form_type("radio");
		$newform->set_form_name("sort_flg");
		$newform->set_form_id("sort_true");
		$newform->set_form_check($sort_flg);
		$newform->set_form_value('1');
		$radio1 = $newform->make();

		$newform = new form_parts();
		$newform->set_form_type("radio");
		$newform->set_form_name("sort_flg");
		$newform->set_form_id("sort_false");
		$newform->set_form_check($sort_flg);
		$newform->set_form_value('0');
		$radio2 = $newform->make();

		$sort_value = $radio1 . "<label for=\"sort_true\">".$L_TEGAKI_PARAM['sort_flg'][1]."</label> / " . $radio2 . "<label for=\"sort_false\">".$L_TEGAKI_PARAM['sort_flg'][0]."</label>";

		// スムージングラジオボタン生成
		$newform = new form_parts();
		$newform->set_form_type("radio");
		$newform->set_form_name("smooth_flg");
		$newform->set_form_id("smooth_true");
		$newform->set_form_check($smooth_flg);
		$newform->set_form_value('1');
		$radio1 = $newform->make();

		$newform = new form_parts();
		$newform->set_form_type("radio");
		$newform->set_form_name("smooth_flg");
		$newform->set_form_id("smooth_false");
		$newform->set_form_check($smooth_flg);
		$newform->set_form_value('0');
		$radio2 = $newform->make();

		$smooth_value = $radio1 . "<label for=\"smooth_true\">".$L_TEGAKI_PARAM['smooth_flg'][1]."</label> / " . $radio2 . "<label for=\"smooth_false\">".$L_TEGAKI_PARAM['smooth_flg'][0]."</label>";
	}

	$INPUTS['SORT'] = array('result'=>'plane','value'=>$sort_value);
	$INPUTS['SMOOTH'] = array('result'=>'plane','value'=>$smooth_value);
	$INPUTS['REMARKS'] = array('type'=>'textarea','name'=>'usr_bko','rows'=>'5','cols'=>'50','value'=>$_POST['usr_bko']);

	$make_html->set_rep_cmd($INPUTS);
	$html .= $make_html->replace();

	// 制御ボタン定義
	$html .= "<input type=\"submit\" value=\"追加確認\">\n";
//	$html .= "<input type=\"reset\" value=\"クリア\">\n";
	$html .= "</form>\n";
	$html .= "<br>\n";
	// 一覧に戻る
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"drill_tegaki_default_list\">\n";
	$html .= "<input type=\"submit\" value=\"一覧に戻る\">\n";
	$html .= "</form>\n";
	return $html;
}


/**
 * 詳細情報表示
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function viewform($ERROR) {

	// グローバル変数
	global $L_TEGAKI_PARAM;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (ACTION) {
		foreach ($_POST as $key => $val) { $$key = $val; }
	} else {

		$sql = "SELECT * FROM ".T_DRILL_TEGAKI_DEFAULT.
			" WHERE default_tegaki_num='".$_POST['default_tegaki_num']."' AND mk_flg='0' LIMIT 1;";
		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);
		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}
		foreach ($list as $key => $val) {
			$$key = replace_decode($val);
			$$key = ereg_replace("\"","&quot;",$$key);
		}

		if ($_POST['course_num']) { $course_num = $_POST['course_num']; }
		if ($_POST['version']) { $version = $_POST['version']; }
		if ($_POST['view_mode']) { $view_mode = $_POST['view_mode']; }
	}



	if($course_num == 1 && ($gknn == "L" || $gknn == "P")) {
		$gknn = "J";
	}
	if($course_num == 1) {
		unset($L_TEGAKI_PARAM['gknn']['L']);
		unset($L_TEGAKI_PARAM['gknn']['P']);
	} 

	$L_COURSE = array();
	$L_COURSE[0] = "選択してください。";

	$sql = " SELECT * FROM ".T_COURSE." WHERE display='1' AND state='0' ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		while($list = $cdb->fetch_assoc($result)) {
			$L_COURSE[$list['course_num']] = $list['course_name'];
		}
	}

	// 画面表示
	$html .= "詳細画面<br>\n";

	// エラーメッセージ表示
	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	// 入力画面表示
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" name=\"change_form\" id=\"change_form\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"詳細\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";
	$html .= "<input type=\"hidden\" name=\"default_tegaki_num\" value=\"".$default_tegaki_num."\">\n";

	// htmlクラス生成
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(DRILL_TEGAKI_DEFAULT_FORM);

	$INPUTS['DEFAULTTEGAKINUM'] = array('result'=>'plane','value'=>$default_tegaki_num);
	$INPUTS['COURSENAME'] = array('type'=>'select','name'=>'course_num','array'=>$L_COURSE, 'check'=>$course_num,'action'=>'onchange="select_change_submit(\'change_form\', \''.MODE.'\',  \'\');return false;"');
	if ($course_num == 1 || $course_num == 2 || $course_num == 3) {
		$INPUTS['GKNN'] = array('type'=>'select','name'=>'gknn','array'=>$L_TEGAKI_PARAM['gknn'],'check'=>$gknn);
	} else {
		// すらら以外は学年を選択させない
		$INPUTS['GKNN'] = array('result'=>'plane','value'=>"---");
	}
	$INPUTS['VERSION'] = array('type'=>'select','name'=>'version','array'=>$L_TEGAKI_PARAM['version'], 'check'=>$version,'action'=>'onchange="select_change_submit(\'change_form\', \''.MODE.'\',  \'\');return false;"');
	$INPUTS['VIEWMODE'] = array('type'=>'select','name'=>'view_mode','array'=>$L_TEGAKI_PARAM['view_mode'], 'check'=>$view_mode,'action'=>'onchange="select_change_submit(\'change_form\', \''.MODE.'\',  \'\');return false;"');
	if ($version == 2) {
		$INPUTS['SINGLEMODE'] = array('type'=>'select','name'=>'single_mode','array'=>$L_TEGAKI_PARAM['single_mode'], 'check'=>$single_mode);
	} else {
		$INPUTS['SINGLEMODE'] = array('result'=>'plane','value'=>"---");
	}

	$INPUTS['POSTYPE'] = array('type'=>'select','name'=>'pos_type','array'=>$L_TEGAKI_PARAM['pos_type'][$view_mode], 'check'=>$pos_type);
	$INPUTS['RECOGTYPE'] = array('type'=>'select','name'=>'recog_type','array'=>$L_TEGAKI_PARAM['recog_type'][$version], 'check'=>$recog_type);

	$sort_value = "---";
	$smooth_value = "---";

	if ($version == 3) {
		// ソートラジオボタン生成
		$newform = new form_parts();
		$newform->set_form_type("radio");
		$newform->set_form_name("sort_flg");
		$newform->set_form_id("sort_true");
		$newform->set_form_check($sort_flg);
		$newform->set_form_value('1');
		$radio1 = $newform->make();

		$newform = new form_parts();
		$newform->set_form_type("radio");
		$newform->set_form_name("sort_flg");
		$newform->set_form_id("sort_false");
		$newform->set_form_check($sort_flg);
		$newform->set_form_value('0');
		$radio2 = $newform->make();

		$sort_value = $radio1 . "<label for=\"sort_true\">".$L_TEGAKI_PARAM['sort_flg'][1]."</label> / " . $radio2 . "<label for=\"sort_false\">".$L_TEGAKI_PARAM['sort_flg'][0]."</label>";

		// スムージングラジオボタン生成
		$newform = new form_parts();
		$newform->set_form_type("radio");
		$newform->set_form_name("smooth_flg");
		$newform->set_form_id("smooth_true");
		$newform->set_form_check($smooth_flg);
		$newform->set_form_value('1');
		$radio1 = $newform->make();

		$newform = new form_parts();
		$newform->set_form_type("radio");
		$newform->set_form_name("smooth_flg");
		$newform->set_form_id("smooth_false");
		$newform->set_form_check($smooth_flg);
		$newform->set_form_value('0');
		$radio2 = $newform->make();

		$smooth_value = $radio1 . "<label for=\"smooth_true\">".$L_TEGAKI_PARAM['smooth_flg'][1]."</label> / " . $radio2 . "<label for=\"smooth_false\">".$L_TEGAKI_PARAM['smooth_flg'][0]."</label>";
	}

	$INPUTS['SORT'] = array('result'=>'plane','value'=>$sort_value);
	$INPUTS['SMOOTH'] = array('result'=>'plane','value'=>$smooth_value);
	$INPUTS['REMARKS'] = array('type'=>'textarea','name'=>'usr_bko','rows'=>'5','cols'=>'50','value'=>$usr_bko);

	$make_html->set_rep_cmd($INPUTS);
	$html .= $make_html->replace();

	// 制御ボタン定義
	$html .= "<input type=\"submit\" value=\"変更確認\">";
//	$html .= "<input type=\"reset\" value=\"クリア\">";
	$html .= "</form><br>\n";
	// 一覧に戻る
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"drill_tegaki_default_list\">\n";
	$html .= "<input type=\"submit\" value=\"一覧に戻る\">\n";
	$html .= "</form>\n";

	return $html;
}


/**
 * 入力項目チェック
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function check() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// 未入力チェック
	if (!$_POST['course_num']) {
		$ERROR[] = "コースを選択してください。";
	}

	if (!$_POST['gknn'] && ($_POST['course_num'] == 1 || $_POST['course_num'] == 2 || $_POST['course_num'] == 3)) {
		$ERROR[] = "学年を選択してください。";
	}

	if (!$_POST['version']) {
		$ERROR[] = "ドリル手書きVerを選択してください。";
	}

	if ($_POST['course_num']) {
		$where = "";
		if ($_POST['gknn']) {
			$where .= " AND gknn = '".$_POST['gknn']."'";
		}
		if ($_POST['default_tegaki_num']) {
			$where .= " AND default_tegaki_num != '".$_POST['default_tegaki_num']."'";
		}

		$sql = "SELECT * FROM ".T_DRILL_TEGAKI_DEFAULT.
			" WHERE course_num='".$_POST['course_num']."' AND mk_flg='0'".$where.";";

		if($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
		}
		if ($list) {
			$ERROR[] = "指定したコース・学年は既に設定されております。";
		}
	}


	return $ERROR;
}


/**
 * 入力確認画面表示
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function check_html() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];


	global $L_TEGAKI_PARAM;

	// アクション情報をhidden項目に設定
	if ($_POST) {
		foreach ($_POST as $key => $val) {
			$val = str_replace(array("\r\n", "\r", "\n"), '', $val);
			if ($key == "action") {
				if (MODE == "add") {
					$val = "add";
				} elseif (MODE == "詳細") {
					$val = "change";
				}
			}
			$HIDDEN .= "<input type=\"hidden\" name=\"$key\" value=\"$val\">\n";
		}
	}

	if (ACTION) {
		foreach ($_POST as $key => $val) { $$key = $val; }

	} else {
		$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"change\">\n";

		$sql = "SELECT * FROM ".T_DRILL_TEGAKI_DEFAULT.
			" WHERE default_tegaki_num='".$_POST['default_tegaki_num']."' AND mk_flg='0' LIMIT 1;";

		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);

		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}
		foreach ($list as $key => $val) {
			$$key = replace_decode($val);
			$$key = ereg_replace("\"","&quot;",$$key);
		}
	}

	// ボタン表示文言判定
	if (MODE != "削除") { $button = "登録"; } else { $button = "削除"; }

	$course_name = "";
	$sql = " SELECT * FROM ".T_COURSE." WHERE display='1' AND state='0' AND course_num ='".$course_num."';";
	if ($result = $cdb->query($sql)) {
		while($list = $cdb->fetch_assoc($result)) {
			$course_name = $list['course_name'];
		}
	}

	// 入力確認画面表示
	$html = "<br>\n";
	$html .= "確認画面：以下の内容で".$button."してもよろしければ".$button."ボタンをクリックしてください。<br>\n";

	// htmlクラス生成
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(DRILL_TEGAKI_DEFAULT_FORM);

	// $配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	if (!$default_tegaki_num) { $default_tegaki_num = "---"; }
	$INPUTS['DEFAULTTEGAKINUM'] = array('result'=>'plane','value'=>$default_tegaki_num);
	$INPUTS['COURSENAME'] = array('result'=>'plane','value'=>$course_name);
	if ($course_num == 1 || $course_num == 2 || $course_num == 3) {
		$INPUTS['GKNN'] = array('result'=>'plane','value'=>$L_TEGAKI_PARAM['gknn'][$gknn]);
	} else {
		// すらら以外は学年を選択させない
		$INPUTS['GKNN'] = array('result'=>'plane','value'=>"---");
	}
	$INPUTS['VERSION'] = array('result'=>'plane','value'=>$L_TEGAKI_PARAM['version'][$version]);
	$INPUTS['VIEWMODE'] = array('result'=>'plane','value'=>$L_TEGAKI_PARAM['view_mode'][$view_mode]);
	if ($version == 2) {
		$INPUTS['SINGLEMODE'] = array('result'=>'plane','value'=>$L_TEGAKI_PARAM['single_mode'][$single_mode]);
	} else {
		$INPUTS['SINGLEMODE'] = array('result'=>'plane','value'=>"---");
	}

	$INPUTS['POSTYPE'] = array('result'=>'plane','value'=>$L_TEGAKI_PARAM['pos_type'][$view_mode][$pos_type]);
	$INPUTS['RECOGTYPE'] = array('result'=>'plane','value'=>$L_TEGAKI_PARAM['recog_type'][$version][$recog_type]);
	$sort_value = "---";
	$smooth_value = "---";
	if ($version == 3) {
		$sort_value = $L_TEGAKI_PARAM['sort_flg'][$sort_flg];
		$smooth_value = $L_TEGAKI_PARAM['smooth_flg'][$smooth_flg];
	}
	$INPUTS['SORT'] = array('result'=>'plane','value'=>$sort_value);
	$INPUTS['SMOOTH'] = array('result'=>'plane','value'=>$smooth_value);
	$INPUTS['REMARKS'] = array('result'=>'plane','value'=>$usr_bko);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();

	// 制御ボタン定義
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
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"drill_tegaki_default_list\">\n";
	}
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}


/**
 * ＤＢ登録処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function add() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$unit_num = "";

	// 登録項目設定
	if (is_array($_POST)) {
		foreach ($_POST as $key => $val) {
			if ($key == "action") { continue; }
			if ($key == "mode") { continue; }
			if ($key == 'usr_bko') { $val = $cdb->real_escape($val); }
			$INSERT_DATA[$key] = $val;
		}
	}

	$INSERT_DATA['ins_syr_id']		= "add";
	$INSERT_DATA['ins_date']		= "now()";
	$INSERT_DATA['ins_tts_id']		= $_SESSION['myid']['id'];
	$INSERT_DATA['upd_syr_id']		= "add";
	$INSERT_DATA['upd_date']		= "now()";
	$INSERT_DATA['upd_tts_id']		= $_SESSION['myid']['id'];

	// DB追加処理
	$ERROR = $cdb->insert(T_DRILL_TEGAKI_DEFAULT,$INSERT_DATA);

	if (!$ERROR) { $_SESSION['select_menu'] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}


/**
 * ＤＢ変更処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function change() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// 更新処理
	if (MODE == "詳細") {
		if (is_array($_POST)) {
			foreach ($_POST as $key => $val) {
				if ($key == "action") { continue; }
				if ($key == "mode") { continue; }
				if ($key == 'usr_bko') { $val = $cdb->real_escape($val); }
				$$key = $val;
				$INSERT_DATA[$key] = $val;
			}

			if ($course_num != 1 && $course_num != 2 && $course_num != 3) {
				$INSERT_DATA['gknn'] = "NULL";
			}
			if ($version != 3) {
				$INSERT_DATA['sort_flg'] = "0";
				$INSERT_DATA['smooth_flg'] = "0";
			}
		}

		$INSERT_DATA['upd_syr_id']			= "update";
		$INSERT_DATA['upd_tts_id']			= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date']			= "now()";

		$where = " WHERE default_tegaki_num = '".$_POST['default_tegaki_num']."' LIMIT 1;";
		$ERROR = $cdb->update(T_DRILL_TEGAKI_DEFAULT,$INSERT_DATA,$where);

	// 削除処理
	} elseif (MODE == "削除") {

		$INSERT_DATA['mk_flg']				= "1";
		$INSERT_DATA['mk_tts_id']			= $_SESSION['myid']['id'];
		$INSERT_DATA['mk_date']				= "now()";
		$INSERT_DATA['upd_syr_id']			= "del";
		$INSERT_DATA['upd_tts_id']			= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date']			= "now()";

		$where = " WHERE default_tegaki_num = '".$_POST['default_tegaki_num']."' LIMIT 1;";
		$ERROR = $cdb->update(T_DRILL_TEGAKI_DEFAULT,$INSERT_DATA,$where);
	}

	if (!$ERROR) { $_SESSION['select_menu'] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}

?>
