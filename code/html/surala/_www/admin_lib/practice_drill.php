<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * プラクティスステージ管理　問題検証
 *
 * 履歴
 * 2010/06/21 初期設定
 *
 * @author Azet
 */

// add ookawara

/**
 * HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function start() {

	$html .= select_course();
	$html .= problem_list($ERROR);

	return $html;
}


/**
 * コース選択
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function select_course() {

	global $L_UNIT_TYPE;	//	add ookawara 2012/06/11

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// add start karasawa 2019/11/19 課題要望800
	// BG MUSIC  Firefox対応
	$pre = ".mp3";
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	if (preg_match("/Firefox/i", $user_agent) || preg_match("/Opera/i", $user_agent) || preg_match("/OPR/i", $user_agent)) { $pre = ".ogg"; }
	// add end karasawa 2019/11/19 課題要望800

	// add start oda 2014/10/08
	// 使用禁止のコースが存在するかチェックし、抽出条件を作成する
	$course_list = "";
	if (is_array($_SESSION['authority']) && count($_SESSION['authority']) > 0) {
		foreach ($_SESSION['authority'] as $key => $value) {
			if (!$value) { continue; }
			// 使用禁止のコースが存在した場合
			if (substr($value,0,24) == "practice__drill__course_") {
				if ($course_list) { $course_list .= ","; }
				$course_list .= substr($value,24);						// コース番号を取得する
			}
		}
	}
	// add end oda 2014/10/08

	// update start oda 2014/10/08 権限制御修正
	//$sql  = "SELECT course_num,course_name FROM " . T_COURSE .
	//		" WHERE state!='1' ORDER BY list_num;";
	$sql  = "SELECT course_num,course_name FROM ".T_COURSE;
	$sql .= " WHERE state!='1'";
	if ($course_list) { $sql .= " AND course_num NOT IN (".$course_list.") "; }
	$sql .= " ORDER BY list_num;";
	// update end oda 2014/10/08

	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
	}
	if (!$max) {
		$html .= "コースを設定してからご利用下さい。<br>\n";
		return $html;
	}
	if (!$_POST['course_num'] && !$_POST['stage_num'] && !$_POST['lesson_num'] && !$_POST['unit_num'] && !$_POST['block_num']) {
		$msg .= "コース、ステージ、Lesson、ユニットを選択してください。<br>\n";
	}
	if (!$_POST[course_num]) { $selected = "selected"; } else { $selected = ""; }
	$course_num_html .= "<option value=\"\" $selected>選択して下さい</option>\n";
	while ($list = $cdb->fetch_assoc($result)) {
		if ($_POST[course_num] == $list['course_num']) { $selected = "selected"; } else { $selected = ""; }
		$course_num_html .= "<option value=\"{$list['course_num']}\" $selected>{$list['course_name']}</option>\n";
		if ($selected) { $L_NAME[course_name] = $list['course_name']; }
	}

	if (!$_POST[stage_num]) { $selected = "selected"; } else { $selected = ""; }
	$stage_num_html .= "<option value=\"\" $selected>----------</option>\n";
	$sql  = "SELECT stage_num,stage_name FROM " . T_STAGE .
			" WHERE state!='1' AND course_num='$_POST[course_num]' ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
		while ($list = $cdb->fetch_assoc($result)) {
			if ($_POST[stage_num] == $list[stage_num]) { $selected = "selected"; } else { $selected = ""; }
			$stage_num_html .= "<option value=\"{$list[stage_num]}\" $selected>{$list[stage_name]}</option>\n";
			if ($selected) { $L_NAME[stage_name] = $list[stage_name]; }
		}
	}
	if (!$msg && !$max) { $msg .= "ステージが設定されておりません。<br>\n"; }
	elseif (!$msg && !$_POST[stage_num]) { $msg .= "ステージを選択してください。<br>\n"; }

	if (!$_POST[lesson_num]) { $selected = "selected"; } else { $selected = ""; }
	$lesson_num_html .= "<option value=\"\" $selected>----------</option>\n";
	$sql = "SELECT lesson_num,lesson_name FROM " . T_LESSON .
		" WHERE state!='1' AND stage_num='$_POST[stage_num]' ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
		while ($list = $cdb->fetch_assoc($result)) {
			if ($_POST[lesson_num] == $list[lesson_num]) { $selected = "selected"; } else { $selected = ""; }
			$lesson_num_html .= "<option value=\"{$list[lesson_num]}\" $selected>{$list[lesson_name]}</option>\n";
			if ($selected) { $L_NAME[lesson_name] = $list[lesson_name]; }
		}
	}
	if (!$msg && !$max) { $msg .= "Lessonが設定されておりません。<br>\n"; }
	elseif (!$msg && !$_POST[lesson_num]) { $msg .= "Lessonを選択してください。<br>\n"; }

	if (!$_POST[unit_num]) { $selected = "selected"; } else { $selected = ""; }
	$unit_html .= "<option value=\"\" $selected>----------</option>\n";
	$sql = "SELECT unit_num,unit_name FROM " . T_UNIT .
		" WHERE state!='1' AND lesson_num='$_POST[lesson_num]' ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
		while ($list = $cdb->fetch_assoc($result)) {
			if ($_POST[unit_num] == $list[unit_num]) { $selected = "selected"; } else { $selected = ""; }
			$unit_html .= "<option value=\"{$list[unit_num]}\" $selected>{$list[unit_name]}</option>\n";
			if ($selected) { $L_NAME[unit_name] = $list[unit_name]; }
		}
	}
	if (!$msg && !$max) { $msg .= "ユニットが設定されておりません。<br>\n"; }
	elseif (!$msg && !$_POST[unit_num]) { $msg .= "ユニットを選択してください。<br>\n"; }

	if (!$_POST[block_num]) { $selected = "selected"; } else { $selected = ""; }
	$block_html = "<option value=\"\" $selected>----------</option>\n";
	$sql = "SELECT block_num, block_type, display, lowest_study_number FROM " . T_BLOCK .
		" WHERE state!='1' AND unit_num='$_POST[unit_num]' ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
		while ($list = $cdb->fetch_assoc($result)) {
			if ($_POST['block_num'] == $list['block_num']) { $selected = "selected"; } else { $selected = ""; }
			$block_name = $L_UNIT_TYPE[$list['block_type']];	//	add ookawara 2012/06/11
/*
			//	add ookawara 2012/06/11
			if ($list['block_type'] == 1) {
				$block_name = "ドリル";
			} elseif ($list['block_type'] == 2) {
				$block_name = "診断A";
			} elseif ($list['block_type'] == 3) {
				$block_name = "診断B";
			}
*/
			if ($selected) {
				define("block_type",$list['block_type']);
				if ($list['lowest_study_number']) { define("lowest_study_number",$list['lowest_study_number']); }
			}
			if ($list['display'] == "2") { $block_name .= "(非表示)"; }
			$block_html .= "<option value=\"{$list['block_num']}\" $selected>{$block_name}</option>\n";
			if ($selected) { $L_NAME['block_name'] = $block_name; }
		}
	}
// 「ブロック」から「ドリル」へ表記統一　2009/11/02 hirano
	if (!$msg && !$max) { $msg .= "ドリルが設定されておりません。<br>\n"; }
	elseif (!$msg && !$_POST[block_num]) { $msg .= "ドリルを選択してください。<br>\n"; }

	$html .= "<br>\n";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" name=\"menu\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"set_course\">\n";
	$html .= "<table class=\"unit_form\">\n";
	$html .= "<tr class=\"unit_form_cell\">\n";
	$html .= "<td class=\"stage_form_menu\">コース</td>\n";
	$html .= "<td class=\"stage_form_menu\">ステージ</td>\n";
	$html .= "<td class=\"stage_form_menu\">Lesson</td>\n";
	$html .= "<td class=\"stage_form_menu\">ユニット</td>\n";
// 「ブロック」から「ドリル」へ表記統一　2009/11/02 hirano
	$html .= "<td class=\"stage_form_menu\">ドリル</td>\n";
	$html .= "</tr>\n";
	$html .= "<tr class=\"stage_form_cell\">\n";
	$html .= "<td>\n";
	$html .= "<select name=\"course_num\" onchange=\"submit_course()\">\n";
	$html .= $course_num_html;
	$html .= "</select>\n";
	$html .= "</td>\n";
	$html .= "<td>\n";
	$html .= "<select name=\"stage_num\" onchange=\"submit_stage()\">\n";
	$html .= $stage_num_html;
	$html .= "</select>\n";
	$html .= "</td>\n";
	$html .= "<td><select name=\"lesson_num\" onchange=\"submit_lesson()\">\n";
	$html .= $lesson_num_html;
	$html .= "</select></td>\n";
	$html .= "<td>\n";
	$html .= "<select name=\"unit_num\" onchange=\"submit_unit()\">\n";
	$html .= $unit_html;
	$html .= "</select>\n";
	$html .= "</td>\n";
	$html .= "<td>\n";
	$html .= "<select name=\"block_num\" onchange=\"submit()\">\n";
	$html .= $block_html;
	$html .= "</select>\n";
	$html .= "</td>\n";
	$html .= "</tr>\n";
	$html .= "</table>\n";
	$html .= "</form><br>\n";
	$html .= $msg;
	return $html;
}

/**
 * 問題一覧
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function problem_list($ERROR) {

	global $L_PROBLEM_TYPE,$L_FORM_TYPE,$L_DISPLAY,$L_UNIT_TYPE;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (!$_POST['course_num'] || !$_POST['stage_num'] || !$_POST['lesson_num'] || !$_POST['unit_num'] || !$_POST['block_num']) {
		return $html;
	}

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
		$html .= "<br />\n";
	}

	$html .= select_menu();

	// サービスタイプ取得		// add oda 2014/06/30
	$service_type = get_service_type($_POST['course_num']);

	//	絞り込みの情報取得
	if ($_SESSION['SELECT_VIEW']) {
		foreach ($_SESSION['SELECT_VIEW'] AS $key => $val) {
			$$key = $val;
		}
	}

	//	問題形式	view_form_type
	if ($view_form_type) {
		$where .= " AND form_type='$view_form_type'";
		$where2 .= " AND p.form_type='$view_form_type'";	// add hasegawa 2018/04/03 問題のトランザクション値切り分け
	}

	//	問題番号	view_display_number
	if ($view_display_number) {
		$where .= " AND display_problem_num='$view_display_number'";
		$where2 .= " AND p.display_problem_num='$view_display_number'";	// add hasegawa 2018/04/03 問題のトランザクション値切り分け
	}

	$sql = "SELECT count(*) AS max FROM ".T_PROBLEM;
	$sql .= " WHERE course_num='$_POST[course_num]' AND block_num='$_POST[block_num]' AND state!='1'";
	$sql .= " $where";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$max = $list['max'];
	}

	if (!$max) {
		$html .= "<br>\n";
		$html .= "問題は登録されておりません。<br>\n";
		return $html;
	}

	//	表示数設定
	$page_all = ceil($max / $view_num);
	if ($_POST['page']) { $page = $_POST['page']; }
	if (!$page) {
		$page = 1;
	} elseif ($page > $page_all) {
		$page = $page_all;
	}
	$_SESSION['SELECT_VIEW']['page'] = $page;
	$offset = ($page - 1) * $view_num;
	$limit = $view_num;
	$display_start = $offset + 1;
	$display_end = $display_start + $view_num;
	if ($display_end > $max) { $display_end = $max; }
	//	検索数
	$limit_num = "LIMIT {$offset},{$limit}";

	//add start hirose 2018/05/01 管理画面手書き切り替え機能追加
	if(isset($_COOKIE["tegaki_flag"])){
		$_SESSION['TEGAKI_FLAG'] = $_COOKIE["tegaki_flag"];
	}else{
		$_SESSION['TEGAKI_FLAG'] = 1;
	}

	$check = "checked";
	if($_SESSION['TEGAKI_FLAG'] == 0){
		$check = "";
	}
	$onchenge = "onclick=\"this.blur(); this.focus();\" onchange=\"update_tegaki_flg(this,'menu');\"";
	$html .= "<div class=\"tegaki-switch\">";
	$html .= "<label>";
	$html .= "<input type=\"checkbox\" name=\"tegaki_control\" ".$check." ".$onchenge." class=\"tegaki-check\"><span class=\"swith-content\"></span><span class=\"swith-button\"></span>";
	$html .= "</label>";
	$html .= "</div>";
	//add end hirose 2018/05/01 管理画面手書き切り替え機能追加

	$html .= $drill_button;
	$html .= $max."問登録があります。<br>";
	//add start hirose 2018/12/06 すらら英単語(音声自動再生)
	$html .= "<input type=\"button\" value=\"sound_check\" Onclick=\"window_check()\" id=\"sound_stop_preparation\" style=\"display:none;\" >\n";
	// $html .= "<audio src=\"\" id=\"sound_ajax\" style=\"display:none;\"></audio>\n";											// del karasawa 2019/11/19 課題要望800
	$html .= "<audio src=\"/student/images/drill/silent3sec".$pre."\" id=\"sound_ajax\" style=\"display:none;\"></audio>\n";	// add karasawa 2019/11/19 課題要望800
	//add end hirose 2018/12/06 すらら英単語(音声自動再生)
	$html .= "<table class=\"member_form\">\n";
	$html .= "<tr class=\"member_form_menu\">\n";
	$html .= "<td>no.</td>\n";
	$html .= "<td>管理番号</td>\n";					// 2012/09/27 add oda
	$html .= "<td>問題タイプ</td>\n";
	$html .= "<td>出題形式</td>\n";
	$html .= "<td>解答数</td>\n";
	$html .= "<td>不正解数</td>\n";
	$html .= "<td>正解率</td>\n";
	$html .= "<td>エラー</td>\n";
	$html .= "<td>表示・非表示</td>\n";
	$html .= "<td>確認</td>\n";
	$html .= "</tr>\n";

	// upd start hasegawa 2018/04/03 問題のトランザクション値切り分け
	// $sql  = "SELECT problem_num," .
	// 	"problem_type," .
	// 	"display_problem_num," .
	// 	"form_type," .
	// 	"number_of_answers," .
	// 	"error_msg," .
	// 	"number_of_incorrect_answers," .
	// 	"correct_answer_rate," .
	// 	"display" .
	// 	" FROM ".T_PROBLEM." ".T_PROBLEM.
	// 	" WHERE course_num='".$_POST['course_num']."'".
	// 	" AND block_num='".$_POST['block_num']."' AND state!='1'";
	// $sql .= " $where";
	// $sql .= " ORDER BY display_problem_num $limit_num";
	$sql  = "SELECT p.problem_num," .
		"p.problem_type," .
		"p.display_problem_num," .
		"p.form_type," .
		"pd.number_of_answers," .
		"p.error_msg," .
		"pd.number_of_incorrect_answers," .
		"pd.correct_answer_rate," .
		"p.display" .
		" FROM ".T_PROBLEM." p".
		" LEFT JOIN ".T_PROBLEM_DIFFICULTY." pd ON pd.problem_num = p.problem_num".
		" WHERE p.course_num='".$_POST['course_num']."'".
		" AND p.block_num='".$_POST['block_num']."' AND p.state='0'";
	$sql .= " ".$where2;
	$sql .= " ORDER BY p.display_problem_num ".$limit_num;
	// upd end hasegawa 2018/04/03

	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$html .= "<tr class=\"member_form_cell\">\n";
			$html .= "<td>$list[display_problem_num]</td>\n";
			$html .= "<td>".$list['problem_num']."</td>\n";						// 2012/09/27 add oda
			$html .= "<td>{$L_PROBLEM_TYPE[$list[problem_type]]}</td>\n";
			$html .= "<td>{$L_FORM_TYPE[$list[form_type]]}</td>\n";
			$html .= "<td>$list[number_of_answers]</td>\n";
			$html .= "<td>$list[number_of_incorrect_answers]</td>\n";
			$html .= "<td>{$list['correct_answer_rate']}%</td>\n";
			if ($list['error_msg']) { $error_msg = "エラー有"; } else { $error_msg = "----"; }
			$html .= "<td>{$error_msg}</td>\n";
			$html .= "<td>{$L_DISPLAY[$list['display']]}</td>\n";
			$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"view\">\n";
			$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"".$list['problem_num']."\">\n";
			//$html .= "<td><input type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_2('".$list['problem_num']."')\"></td>\n";							// del oda 2014/06/30
			//add start hirose 2018/12/06 すらら英単語(音声自動再生)
			if ($list[form_type] == 16 || $list[form_type] == 17){
				$html .= "<td><input type=\"button\" value=\"確認\" onclick=\"play_sound_ajax_admin('sound_ajax', '/student/images/drill/silent3sec.mp3', '', '');check_problem_win_open_2('".$service_type."', '".$list['problem_num']."')\"></td>\n";
			}else{
			//add end hirose 2018/12/06 すらら英単語(音声自動再生)
				$html .= "<td><input type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_2('".$service_type."', '".$list['problem_num']."')\"></td>\n";		// update oda 2014/06/30 $service_type追加
			}//add hirose 2018/12/06 すらら英単語(音声自動再生)
			$html .= "</form>\n";
		}
	}
	$html .= "</table>\n";

	if ($page > 1 || $page_all != 1) {
		$html .= "<br>\n";

		if ($page > 1) {
			$back_page = $page - 1;
			$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" style=\"float:left;\">";
			$html .= "<input type=\"hidden\" name=\"course_num\" value=\"$_POST[course_num]\">\n";
			$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"$_POST[stage_num]\">\n";
			$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"$_POST[lesson_num]\">\n";
			$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"$_POST[unit_num]\">\n";
			$html .= "<input type=\"hidden\" name=\"block_num\" value=\"$_POST[block_num]\">\n";
			$html .= "<input type=\"submit\" value=\"前のページ\">";
			$html .= "<input type=\"hidden\" name=\"action\" value=\"page\">\n";
			$html .= "<input type=\"hidden\" name=\"page\" value=\"$back_page\">\n";
			$html .= "</form>";
		}
		if ($page < $page_all) {
			$next_page = $page + 1;
			$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" style=\"float:left;\">";
			$html .= "<input type=\"hidden\" name=\"course_num\" value=\"$_POST[course_num]\">\n";
			$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"$_POST[stage_num]\">\n";
			$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"$_POST[lesson_num]\">\n";
			$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"$_POST[unit_num]\">\n";
			$html .= "<input type=\"hidden\" name=\"block_num\" value=\"$_POST[block_num]\">\n";
			$html .= "<input type=\"hidden\" name=\"action\" value=\"page\">\n";
			$html .= "<input type=\"hidden\" name=\"page\" value=\"$next_page\">\n";
			$html .= "<input type=\"submit\" value=\"次のページ\">";
			$html .= "</form>";
		}
	}

	return $html;
}


/**
 * 絞り込みメニュー
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function select_menu() {

	global $L_FORM_TYPE;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if ($_POST['select_reset'] || $_SESSION['SELECT_VIEW']['block_num'] != $_POST[block_num]) {
		unset($_SESSION['SELECT_VIEW']);
	} elseif ($_POST['action'] == "select") {
		foreach ($_POST AS $key => $val) {
			$$key = $val;
		}
		unset($_SESSION['SELECT_VIEW']);
	} elseif ($_SESSION['SELECT_VIEW']) {
		foreach ($_SESSION['SELECT_VIEW'] AS $key => $val) {
			$$key = $val;
		}
	}
	if ($_POST[block_num]) { $_SESSION['SELECT_VIEW']['block_num'] = $_POST[block_num]; }


	//	問題形式	view_form_type
	$l_view_form_type = "<select name=\"view_form_type\">\n";
	if ($view_form_type == "") { $selected = "selected"; } else { $selected = ""; }
	$l_view_form_type .= "<option value=\"\" $selected>----------</option>\n";
	$sql  = "SELECT form_type FROM ".T_PROBLEM.
			" WHERE course_num='$_POST[course_num]' AND block_num='$_POST[block_num]' AND state!='1'".
			" GROUP BY form_type ORDER BY form_type;";
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$form_type = $list['form_type'];
			if ($view_form_type == $form_type) { $selected = "selected"; } else { $selected = ""; }
			$l_view_form_type .= "<option value=\"$form_type\" $selected>{$L_FORM_TYPE[$form_type]}</option>\n";
		}
	}
	$l_view_unit_num .= "</select>\n";
	$_SESSION['SELECT_VIEW']['view_form_type'] = $view_form_type;
	$MENU[0][name] = "問題形式";
	$MENU[0][value] = $l_view_form_type;


	//	問題番号	view_display_number
	$_SESSION['SELECT_VIEW']['view_display_number'] = $view_display_number;
	$MENU[1][name] = "問題番号";
	$MENU[1][value] = "<input type=\"text\" size=\"10\" name=\"view_display_number\" value=\"$view_display_number\">\n";


	//	表示数	view_num
	if ($view_num == "") { $view_num = 10; }
	$l_view_num = "<select name=\"view_num\">\n";
	for ($i=1; $i<=10; $i++) {
		$val = $i * 10;
		if ($view_num == $val) { $selected = "selected"; } else { $selected = ""; }
		$l_view_num .= "<option value=\"{$val}\" $selected>{$val}</option>\n";
	}
	$l_view_num .= "</select>\n";
	$_SESSION['SELECT_VIEW']['view_num'] = $view_num;
	$MENU[2][name] = "表示数";
	$MENU[2][value] = $l_view_num;


	//	表示ページ
	if ($_POST['page']) { $page = $_POST['page']; }
	if (!$page) { $page = 1; }
	$_SESSION['SELECT_VIEW']['page'] = $page;


	//	送信ボタン
	$MENU[4][name] = "&nbsp;";
	$MENU[4][value] = "<input type=\"submit\" value=\"絞込\"> <input type=\"submit\" name=\"select_reset\" value=\"リセット\">\n";

	foreach ($MENU AS $key => $VAL) {
		$name_ = $VAL[name];
		$values_ = $VAL[value];
		if (!$values_) { continue; }
		$form_menu .= "<td>$name_</td>";
		$form_cell .= "<td>$values_</td>";
	}

	$html .= "<div id=\"mode_menu\">\n";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"select\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"$_POST[course_num]\">\n";
	$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"$_POST[stage_num]\">\n";
	$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"$_POST[lesson_num]\">\n";
	$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"$_POST[unit_num]\">\n";
	$html .= "<input type=\"hidden\" name=\"block_num\" value=\"$_POST[block_num]\">\n";
	$html .= "<table class=\"member_form\">\n";
	$html .= "<tr class=\"member_form_menu\">\n";
	$html .= $form_menu;
	$html .= "</tr>\n";
	$html .= "<tr class=\"member_form_cell\">\n";
	$html .= $form_cell;
	$html .= "</tr>\n";
	$html .= "</table>\n";
	$html .= "</form>\n";
	$html .= "</div>\n";
	$html .= "<br style=\"clear:left;\">\n";

	return $html;
}


/**
 * サービス情報取得
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $course_num
 * @return integer
 */
function get_service_type($course_num) {
	// add oda 2014/06/30 TOEIC判定用項目

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$service_type = "";

	$sql  = "SELECT ";
	$sql .= "  sc.setup_type ";
	$sql .= " FROM ".T_SERVICE_COURSE_LIST. " scl ";
	$sql .= " INNER JOIN ".T_SERVICE. " sc ON scl.service_num = sc.service_num ";
	//upd start hirose 2018/12/07 すらら英単語
	//非表示のサービスの問題確認ができないため非表示
//	$sql .= " WHERE scl.display = '1' ";
//	$sql .= "   AND scl.mk_flg = '0' ";
	$sql .= " WHERE scl.mk_flg = '0' ";
	//upd end hirose 2018/12/07 すらら英単語
	$sql .= "   AND scl.course_num = '".$course_num."' ";
	$sql .= "   AND scl.course_type  = '1' ";

	//echo $sql."<br>";

	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$service_type = $list['setup_type'];
		}
	}

	return $service_type;
}
?>
