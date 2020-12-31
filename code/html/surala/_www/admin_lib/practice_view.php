<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * プラクティスステージ管理　問題修正削除
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

	// kaopiz 2020/09/15 speech start
	$speechDefault = '';
	$externalUnit = findExternalUnitNotDelete($_POST['unit_num']);
	if ($externalUnit && $externalUnit['option1']) {
		$speechDefault = $externalUnit['option1'];
	} else {
		$externalCourse = findExternalCourseNotDelete($_POST['course_num']);
		if ($externalCourse && $externalCourse['option1']) {
			$speechDefault = $externalCourse['option1'];
		}
	}
	$GLOBALS['speechDefault'] = $speechDefault;
	// kaopiz 2020/09/15 speech end
	if (ACTION == "add") {
		include("../../_www/problem_lib/problem_regist.php");
		$ERROR = add();
	} elseif (ACTION == "change") {
		include("../../_www/problem_lib/problem_regist.php");
		$ERROR = change();
	} elseif (ACTION == "delete") {
		$ERROR = delete();
	} elseif (ACTION == "del") {
		$ERROR = del();
	}

	$html .= select_course();
	if (MODE == "view") {
		$html .= view($ERROR);
	} elseif (MODE == "add" || MODE == "drawing_type") {		// update oda 2016/02/01 作図ツール changeの判断を追加
		$html .= addform($ERROR);
	} else {
		$html .= problem_list($ERROR);
	}

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

	// add start oda 2014/10/08
	// 使用禁止のコースが存在するかチェックし、抽出条件を作成する
	$course_list = "";
	if (is_array($_SESSION['authority']) && count($_SESSION['authority']) > 0) {
		foreach ($_SESSION['authority'] as $key => $value) {
			if (!$value) { continue; }
			// 使用禁止のコースが存在した場合
			if (substr($value,0,23) == "practice__view__course_") {
				if ($course_list) { $course_list .= ","; }
				$course_list .= substr($value,23);						// コース番号を取得する
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
	if ($_POST[block_num]) {
		$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" style=\"float:left;\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"set_course\">\n";
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"$_POST[course_num]\">\n";
		$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"$_POST[stage_num]\">\n";
		$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"$_POST[lesson_num]\">\n";
		$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"$_POST[unit_num]\">\n";
		$html .= "<input type=\"hidden\" name=\"block_num\" value=\"$_POST[block_num]\">\n";
		$html .= "<input type=\"submit\" value=\"一覧表示\">";
		$html .= "</form>\n";
		if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__add",$_SESSION['authority'])===FALSE)) {
			$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" style=\"float:left;\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"add\">\n";
			$html .= "<input type=\"hidden\" name=\"course_num\" value=\"$_POST[course_num]\">\n";
			$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"$_POST[stage_num]\">\n";
			$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"$_POST[lesson_num]\">\n";
			$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"$_POST[unit_num]\">\n";
			$html .= "<input type=\"hidden\" name=\"block_num\" value=\"$_POST[block_num]\">\n";
			$html .= "<input type=\"submit\" value=\"新規登録\">";
			$html .= "</form>\n";
			if (MODE == "view") {

				// サービスタイプ取得		// add oda 2014/06/30
				$service_type = get_service_type($_POST['course_num']);

				$html .= "<form style=\"float:left;\">\n";
				$html .= "<input type=\"button\" value=\"修正\" onclick=\"javascript:problem_form.submit()\">";
				//$html .= "<input type=\"button\" value=\"確認\" onclick=\"check_problem_win_open()\">";						// del oda 2014/06/30
				$html .= "<input type=\"button\" value=\"確認\" onclick=\"check_problem_win_open('".$service_type."')\">";		// update oda 2014/06/30 $service_type追加
				$html .= "</form>\n";
			}
		}
	}
	$html .= "<br style=\"clear:left;\">\n";
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

	// add start karasawa 2019/11/19 課題要望800
	// BG MUSIC  Firefox対応
	$pre = ".mp3";
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	if (preg_match("/Firefox/i", $user_agent) || preg_match("/Opera/i", $user_agent) || preg_match("/OPR/i", $user_agent)) { $pre = ".ogg"; }
	// add end karasawa 2019/11/19 課題要望800

	//add start hirose 2018/04/26 管理画面手書き切り替え機能追加
	if(isset($_COOKIE["tegaki_flag"])){
		$_SESSION['TEGAKI_FLAG'] = $_COOKIE["tegaki_flag"];
	}else{
		$_SESSION['TEGAKI_FLAG'] = 1;
	}
	//add end hirose 2018/04/26 管理画面手書き切り替え機能追加
	//	ファイルアップロード用仮設定
	$flash_ftp = FTP_URL."flash/".$_POST[course_num]."/".$_POST[stage_num]."/".$_POST[lesson_num]."/".$_POST[unit_num]."/";
	$img_ftp = FTP_URL."prob_img/".$_POST[course_num]."/".$_POST[stage_num]."/".$_POST[lesson_num]."/".$_POST[unit_num]."/".$_POST[block_num]."/";
	$voice_ftp = FTP_URL."voice/".$_POST[course_num]."/".$_POST[stage_num]."/".$_POST[lesson_num]."/".$_POST[unit_num]."/".$_POST[block_num]."/";
	$print_ftp = FTP_URL."print/".$_POST[course_num]."/";
	$tmp_ftp = FTP_URL."template/".$_POST[course_num]."/".$_POST[stage_num]."/";
	$chart_l_ftp = FTP_URL."chart/l/".$_POST[course_num]."/";	// add 低学年2次対応 2016/12/08 yoshizawa
	$chart_p_ftp = FTP_URL."chart/p/".$_POST[course_num]."/";	// add 小学校高学年対応 2014/08/01 yoshizawa
	$chart_j_ftp = FTP_URL."chart/j/".$_POST[course_num]."/";
	$chart_h_ftp = FTP_URL."chart/h/".$_POST[course_num]."/";

	//add start hirose 2018/04/23 FTPをブラウザからのエクスプローラーで開けない不具合対応
	if($_POST[course_num]){
		$html .= "<br>\n";
		$html .= FTP_EXPLORER_MESSAGE;
	}
	//add end hirose 2018/04/23 FTPをブラウザからのエクスプローラーで開けない不具合対応
	if ($_POST[block_num]) {
		$html .= "<br>\n";
		$html .= "<a href=\"".$flash_ftp."\" target=\"_blank\">FLASHフォルダー($flash_ftp)</a><br>\n";
		$html .= "<a href=\"".$img_ftp."\" target=\"_blank\">画像フォルダー($img_ftp)</a><br>\n";
		$html .= "<a href=\"".$voice_ftp."\" target=\"_blank\">音声フォルダー($voice_ftp)</a><br>\n";
		$html .= "<a href=\"".$tmp_ftp."\" target=\"_blank\">テンプレートフォルダー($tmp_ftp)</a><br>\n";
		$html .= "<a href=\"".$print_ftp."\" target=\"_blank\">まとめプリントフォルダー($print_ftp)</a><br>\n";
		// del 小学校高学年対応 2014/08/01 yoshizawa 下に新規で作成 ---------------------------------------------------
		//$html .= "<a href=\"".$chart_ftp."\" target=\"_blank\">体系図（中学生版）フォルダー($chart_j_ftp)</a><br>\n";
		//$html .= "<a href=\"".$chart_ftp."\" target=\"_blank\">体系図（高校生版）フォルダー($chart_h_ftp)</a><br>\n";
		//-------------------------------------------------------------------------------------------------------------
		// add 小学校高学年対応 2014/08/01 yoshizawa ------------------------------------------------------------------
		$html .= "<a href=\"".$chart_l_ftp."\" target=\"_blank\">体系図（小学生低学年版）フォルダー($chart_l_ftp)</a><br>\n"; // add 低学年2次対応 2016/12/08 yoshizawa
		$html .= "<a href=\"".$chart_p_ftp."\" target=\"_blank\">体系図（小学生高学年版）フォルダー($chart_p_ftp)</a><br>\n"; // update 低学年2次対応 2016/12/08 yoshizawa 小学生版 → 小学生高学年版
		$html .= "<a href=\"".$chart_j_ftp."\" target=\"_blank\">体系図（中学生版）フォルダー($chart_j_ftp)</a><br>\n";
		$html .= "<a href=\"".$chart_h_ftp."\" target=\"_blank\">体系図（高校生版）フォルダー($chart_h_ftp)</a><br>\n";
		//-------------------------------------------------------------------------------------------------------------
	} elseif ($_POST[unit_num]) {
		$html .= "<br>\n";
		$html .= "<a href=\"".$flash_ftp."\" target=\"_blank\">FLASHフォルダー($flash_ftp)</a><br>\n";
		$html .= "<a href=\"".$tmp_ftp."\" target=\"_blank\">テンプレートフォルダー($tmp_ftp)</a><br>\n";
		$html .= "<a href=\"".$print_ftp."\" target=\"_blank\">まとめプリントフォルダー($print_ftp)</a><br>\n";
		// del 小学校高学年対応 2014/08/01 yoshizawa 下に新規で作成 ---------------------------------------------------
		//$html .= "<a href=\"".$chart_ftp."\" target=\"_blank\">体系図（中学生版）フォルダー($chart_j_ftp)</a><br>\n";
		//$html .= "<a href=\"".$chart_ftp."\" target=\"_blank\">体系図（高校生版）フォルダー($chart_h_ftp)</a><br>\n";
		//-------------------------------------------------------------------------------------------------------------
		// add 小学校高学年対応 2014/08/01 yoshizawa ------------------------------------------------------------------
		$html .= "<a href=\"".$chart_l_ftp."\" target=\"_blank\">体系図（小学生低学年版）フォルダー($chart_l_ftp)</a><br>\n"; // add 低学年2次対応 2016/12/08 yoshizawa
		$html .= "<a href=\"".$chart_p_ftp."\" target=\"_blank\">体系図（小学生高学年版）フォルダー($chart_p_ftp)</a><br>\n"; // update 低学年2次対応 2016/12/08 yoshizawa 小学生版 → 小学生高学年版
		$html .= "<a href=\"".$chart_j_ftp."\" target=\"_blank\">体系図（中学生版）フォルダー($chart_j_ftp)</a><br>\n";
		$html .= "<a href=\"".$chart_h_ftp."\" target=\"_blank\">体系図（高校生版）フォルダー($chart_h_ftp)</a><br>\n";
		//-------------------------------------------------------------------------------------------------------------
	} elseif ($_POST[course_num]) {
		$html .= "<br>\n";
		$html .= "<a href=\"".$tmp_ftp."\" target=\"_blank\">テンプレートフォルダー($tmp_ftp)</a><br>\n";
		$html .= "<a href=\"".$print_ftp."\" target=\"_blank\">まとめプリントフォルダー($print_ftp)</a><br>\n";
		// del 小学校高学年対応 2014/08/01 yoshizawa 下に新規で作成 ---------------------------------------------------
		//$html .= "<a href=\"".$chart_ftp."\" target=\"_blank\">体系図（中学生版）フォルダー($chart_j_ftp)</a><br>\n";
		//$html .= "<a href=\"".$chart_ftp."\" target=\"_blank\">体系図（高校生版）フォルダー($chart_h_ftp)</a><br>\n";
		//-------------------------------------------------------------------------------------------------------------
		// add 小学校高学年対応 2014/08/01 yoshizawa ------------------------------------------------------------------
		$html .= "<a href=\"".$chart_l_ftp."\" target=\"_blank\">体系図（小学生低学年版）フォルダー($chart_l_ftp)</a><br>\n"; // add 低学年2次対応 2016/12/08 yoshizawa
		$html .= "<a href=\"".$chart_p_ftp."\" target=\"_blank\">体系図（小学生高学年版）フォルダー($chart_p_ftp)</a><br>\n"; // update 低学年2次対応 2016/12/08 yoshizawa 小学生版 → 小学生高学年版
		$html .= "<a href=\"".$chart_j_ftp."\" target=\"_blank\">体系図（中学生版）フォルダー($chart_j_ftp)</a><br>\n";
		$html .= "<a href=\"".$chart_h_ftp."\" target=\"_blank\">体系図（高校生版）フォルダー($chart_h_ftp)</a><br>\n";
		//-------------------------------------------------------------------------------------------------------------
	}

	if (!$_POST['course_num'] || !$_POST['stage_num'] || !$_POST['lesson_num'] || !$_POST['unit_num'] || !$_POST['block_num']) {
		return $html;
	}

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= "<br>\n";

	//add start hirose 2018/04/26 管理画面手書き切り替え機能追加
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
	//add end hirose 2018/04/26 管理画面手書き切り替え機能追加
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
		$where2 .= " AND p.form_type='$view_form_type'";		// add hasegawa 2018/04/03 問題のトランザクション値切り分け
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

	$html .= $drill_button;
	$html .= $max."問登録があります。<br>";
	//add start hirose 2018/09/26 英単語マスター機能追加対応(音声自動再生)
	$html .= "<input type=\"button\" value=\"sound_check\" Onclick=\"window_check()\" id=\"sound_stop_preparation\" style=\"display:none;\" >\n";
	// $html .= "<audio src=\"\" id=\"sound_ajax\" style=\"display:none;\"></audio>\n";											// del karasawa 2019/11/19 課題要望800
	$html .= "<audio src=\"/student/images/drill/silent3sec".$pre."\" id=\"sound_ajax\" style=\"display:none;\"></audio>\n";	// add karasawa 2019/11/19 課題要望800
	//add end hirose 2018/09/26 英単語マスター機能追加対応(音声自動再生)
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
	if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__view",$_SESSION['authority'])===FALSE)) {
		$html .= "<td>修正</td>\n";
	}
	if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__del",$_SESSION['authority'])===FALSE)) {
		$html .= "<td>削除</td>\n";
	}
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
	$sql .= " ORDER BY p.display_problem_num $limit_num";
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
			//add start hirose 2018/09/26 英単語マスター機能追加対応(音声自動再生)
			if ($list[form_type] == 16 || $list[form_type] == 17){
			$html .= "<td><input type=\"button\" value=\"確認\" onclick=\"play_sound_ajax_admin('sound_ajax', '/student/images/drill/silent3sec.mp3', '', '');check_problem_win_open_2('".$service_type."', '".$list['problem_num']."')\"></td>\n";		// update oda 2014/06/30 $service_type追加
			}else{
			//add end hirose 2018/09/26 英単語マスター機能追加対応(音声自動再生)
			$html .= "<td><input type=\"button\" value=\"確認\" onclick=\"check_problem_win_open_2('".$service_type."', '".$list['problem_num']."')\"></td>\n";		// update oda 2014/06/30 $service_type追加
			}//add hirose 2018/09/26 英単語マスター機能追加対応(音声自動再生)
			$html .= "</form>\n";
			if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__view",$_SESSION['authority'])===FALSE)) {
				$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
				$html .= "<input type=\"hidden\" name=\"mode\" value=\"view\">\n";
				$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"$list[problem_num]\">\n";
				$html .= "<input type=\"hidden\" name=\"course_num\" value=\"$_POST[course_num]\">\n";
				$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"$_POST[stage_num]\">\n";
				$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"$_POST[lesson_num]\">\n";
				$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"$_POST[unit_num]\">\n";
				$html .= "<input type=\"hidden\" name=\"block_num\" value=\"$_POST[block_num]\">\n";
				$html .= "<td><input type=\"submit\" value=\"修正\"></td>\n";
				$html .= "</form>\n";
			}
			if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__del",$_SESSION['authority'])===FALSE)) {
				$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" name=\"del_form_$list[problem_num]\">\n";
				$html .= "<input type=\"hidden\" name=\"action\" value=\"delete\">\n";
				$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"$list[problem_num]\">\n";
				$html .= "<input type=\"hidden\" name=\"course_num\" value=\"$_POST[course_num]\">\n";
				$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"$_POST[stage_num]\">\n";
				$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"$_POST[lesson_num]\">\n";
				$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"$_POST[unit_num]\">\n";
				$html .= "<input type=\"hidden\" name=\"block_num\" value=\"$_POST[block_num]\">\n";
				$html .= "<td><input type=\"button\" value=\"削除\" onclick=\"del_problem(this.form);\"></td>\n";
				$html .= "</form>\n";
				$html .= "</tr>\n";
			}
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

	//	block内問題全消し
	if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__del",$_SESSION['authority'])===FALSE)) {
		$html .= "<br><br>\n";
		$html .= "----------------------------------------------------------------------------------<br>\n";
		$html .= "<br><br><br>\n";
		$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" name=\"del_form_all\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"del\">\n";
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"$_POST[course_num]\">\n";
		$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"$_POST[stage_num]\">\n";
		$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"$_POST[lesson_num]\">\n";
		$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"$_POST[unit_num]\">\n";
		$html .= "<input type=\"hidden\" name=\"block_num\" value=\"$_POST[block_num]\">\n";
		$html .= "<font color=\"#ff0000\">\n";
		$html .= "Block内登録問題全削除：<input type=\"button\" value=\"削除\" onclick=\"del_problem(this.form);\"><br>\n";
		$html .= "よく考えて削除をご利用ください。<br>\n";
		$html .= "</font></form>\n";
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
 * 登録フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function addform($ERROR) {

	global $L_PROBLEM_TYPE,$L_FORM_TYPE,$L_DRAWING_TYPE;	//add hasegawa 2016/01/06 作図ツール $L_DRAWING_TYPE追加
	global $L_BLOCK_TYPE_SYOSYA_ENABLE; //add kimura 2018/10/05 漢字学習コンテンツ_書写ドリル対応 //書写登録可能ドリルタイプ
	global  $L_SPEECH_EVALUATION; // kaopiz 2020/08/25 speech

    // kaopiz 2020/08/25 speech end
	// add start hasegawa 2016/01/06 作図ツール
	// 作図の問題は問題種類のセレクトボックスを表示する
	$sel_drawing_html = "";
//  kaopiz 2020/08/25 del
//	if($_POST['form_type'] == 13){
    if ($_POST['form_type'] == 13 || ($_POST['form_type'] == 18)) { //    kaopiz 2020/08/25 speech
        $sel_drawing_html = "<tr>\n";
		$sel_drawing_html .= "<td class=\"unit_form_menu\">問題種類</td>\n";
		$sel_drawing_html .= "<td class=\"unit_form_cell\">\n";
		$sel_drawing_html .= "<select name=\"drawing_type\">\n";
        if($_POST['form_type'] == 13) {    // kaopiz 2020/08/25 speech
            foreach ($L_DRAWING_TYPE as $key2 => $val2){
                // update start 2016/08/30 yoshizawa 作図ツール
                //if ($_POST['drawing_type'] == $key2) { $sel2 = " selected"; } else { $sel2 = ""; }
                //$sel_drawing_html .= "<option value=\"$key2\"$sel2>$val2</option>\n";
                //
                if( $key2 == 1 || $key2 == 2 ){ $disabled = "disabled=\"disabled\""; }else{ $disabled = ""; }
                if ($_POST['drawing_type'] == $key2) { $sel2 = " selected"; } else { $sel2 = ""; }
                $sel_drawing_html .= "<option value=\"$key2\" ".$sel2." ".$disabled.">".$val2."</option>\n";
                // update end 2016/08/30 yoshizawa 作図ツール
            }
//      kaopiz 2020/08/25 speech start
        } elseif ($_POST['form_type'] == 18) {
            foreach ($L_SPEECH_EVALUATION as $key => $value) {
            	if($key !== '') {
					if ($key == $GLOBALS['speechDefault']) {
						$sel_drawing_html .= "<option value=\"$key\" selected=\"$GLOBALS[speechDefault]\">" . $value . "</option>\n";
					} else {
						$sel_drawing_html .= "<option value=\"$key\">" . $value . "</option>\n";
					}
				}
            }
        }
//      kaopiz 2020/08/25 speech end
		$sel_drawing_html .= "</select>\n";
		$sel_drawing_html .= "</td>\n";
		$sel_drawing_html .= "</tr>\n";
	}
	// add end hasegawa 2016/01/06

	if (!$_POST['form_type'] || $_POST['mode'] == "drawing_type") {												// update oda 2016/02/01 作図ツール modeチェック追加
		//add start hirose 2018/11/19 すらら英単語　出題形式登録制御
		$select_service_num = "";
		if($_POST['course_num']){
			$select_service_num = get_service_num($_POST['course_num']);
		}
		//add end hirose 2018/11/19 すらら英単語　出題形式登録制御

		$html .= "<br>登録する出題形式を選択し、決定を押してください。<br>";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"problem_form_type\">\n";		// update oda 2016/02/01 作図ツール name属性追加
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"add\">\n";
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$_POST['stage_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"".$_POST['unit_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"block_num\" value=\"".$_POST['block_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"block_type\" value=\"".block_type."\">\n";
		$html .= "<table class=\"unit_form\">\n";
		$html .= "<tr>\n";
		$html .= "<td class=\"unit_form_menu\">出題形式</td>\n";
		$html .= "<td class=\"unit_form_cell\">\n";
		$html .= "<select name=\"form_type\" onchange=\"document.problem_form_type.mode.value = 'drawing_type'; document.problem_form_type.submit();\">\n";	// update oda 2016/02/01 作図ツール onchangeイベント追加

		foreach ($L_FORM_TYPE as $key => $val){
			// add start hasegawa 2018/03/09 百マス計算
			//update start kimura 2018/10/05 漢字学習コンテンツ_書写ドリル対応 書写(form_type15)は特定のドリルタイプにしか登録できなくする。
			//if((block_type == 7 && $key != 14) || (block_type != 7 && $key == 14)) {
			if((block_type == 7 && $key != 14) || (block_type != 7 && $key == 14) || ($key == 15 && !in_array(block_type, $L_BLOCK_TYPE_SYOSYA_ENABLE))) {
			//update end   kimura 2018/10/05 漢字学習コンテンツ_書写ドリル対応
				continue;
			}
			// add end hasegawa 2018/03/09
			//add start hirose 2018/11/19 すらら英単語　出題形式登録制御
//			if($select_service_num == 16 && $key != 16 && $key != 17 ){
			if($select_service_num == 16 && $key != 16 && $key != 17 && $key != 18 ){ // kaopiz 2020/09/15 speech
				continue;
//			}elseif($select_service_num != 16 && ($key == 16 || $key == 17)){
			}elseif($select_service_num != 16 && ($key == 16 || $key == 17 || $key == 18)){ // kaopiz 2020/09/15 speech
				continue;
			}
			//add end hirose 2018/11/19 すらら英単語　出題形式登録制御
			if ($_POST[form_type] == $key) { $sel = " selected"; } else { $sel = ""; }
			$html .= "<option value=\"$key\"$sel>$val</option>\n";
		}

		$html .= "</select>\n";
		$html .= "</td>\n";
		$html .= "</tr>\n";
		$html .= $sel_drawing_html;		// add hasegawa 2016/01/06 作図ツール
		$html .= "</table>\n";
		$html .= "<input type=\"submit\" value=\"決定\">";
		$html .= "</form>\n";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"problem_list\">\n";
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$_POST['stage_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"".$_POST['unit_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"block_num\" value=\"".$_POST['block_num']."\">\n";
		$html .= "<input type=\"submit\" value=\"戻る\">\n";
		$html .= "</form>\n";

	} else{
		$html .= view($ERROR);
	}

	return $html;
}

/**
 * 表示の準備
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR エラーメッセージ（配列）
 * @return string HTML
 */
function view($ERROR) {

	global $L_PROBLEM_TYPE,$L_FORM_TYPE,$L_DISPLAY,$L_SENTENCE_FLAG,$L_DRAWING_TYPE;		// add oda 作図ツール $L_DRAWING_TYPE追加
	global $BUD_SELECT_LIST; // add karasawa 2019/07/23 BUD英語解析開発
	global  $L_SPEECH_EVALUATION; // kaopiz 2020/08/25 speech

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if ($ERROR) {
		$html .= "<br>\n";
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	} elseif (ACTION == "add") {
		$html .= "<div class=\"small_error\">\n";
		$html .= "<br><strong>問題情報が追加されました。</strong>\n";
		$html .= "</div>\n";
	} elseif (ACTION == "change") {
		$html .= "<div class=\"small_error\">\n";
		$html .= "<br><strong>問題情報が更新されました。</strong>\n";
		$html .= "</div>\n";
	}

	if (MODE == "view") {
		$sql  = "SELECT * FROM ".T_PROBLEM.
				" WHERE problem_num='".$_POST['problem_num']."' LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
		}
		if ($list) {
			foreach ($list as $key => $val) {
				//$val = ereg_replace("\n","//",$val);
				//$val = ereg_replace("&nbsp;"," ",$val);
				$val = str_replace("\n","//",$val);
				$val = str_replace("&nbsp;"," ",$val);
				$$key = $val;
			}
		//	add koike 2012/06/29 start
//		$sql = "SELECT tplu.`unit_num` FROM " .			// 2012/08/06 del oda
		$sql = "SELECT " .								// 2012/08/06 update oda
				" plu.surala_unit_num ".				// 2012/08/06 add oda
				" FROM ".T_PROBLEM_LMS_UNIT . " plu ".
				" WHERE plu.problem_num='".$_POST['problem_num']."'".
				"   AND plu.course_num='".$_POST['course_num']."'".		// 2012/08/06 add oda
				"   AND plu.stage_num='".$_POST['stage_num']."'".		// 2012/08/06 add oda
				"   AND plu.lesson_num='".$_POST['lesson_num']."'".		// 2012/08/06 add oda
				"   AND plu.unit_num='".$_POST['unit_num']."'".			// 2012/08/06 add oda
				"   AND plu.block_num='".$_POST['block_num']."'".		// 2012/08/06 add oda
				"   AND plu.one_point_num='0'".							// add oda 2013/08/05
				"   AND plu.mk_flg = '0';";
			if ($result = $cdb->query($sql)) {
				$L_UNIT_NUM = array();
				while ($list = $cdb->fetch_assoc($result)) {
//					if($list['unit_num']) {$L_UNIT_NUM[] .= $list['unit_num'];}						// 2012/08/06 del oda
					if($list['surala_unit_num']) {$L_UNIT_NUM[] .= $list['surala_unit_num'];}		// 2012/08/06 update oda
				}
			}
			$unit_id = "";
			if ($L_UNIT_NUM) {
				$unit_id = implode("::",$L_UNIT_NUM);
			}
		}
		$unit_id = change_unit_num_to_key_all($unit_id);		// 2012/09/27 add oda
		// kaopiz 2020/09/15 speech start
		$external_problem = findExternalProblemNotDelete($_POST['problem_num']);
		if($external_problem) {
			$speech_type  = $external_problem['option1'];
			$drawing_type = $external_problem['option1'];
			$model_voice  = $external_problem['option2'];
			$model_voice_result  = $external_problem['option3'];
			$voice_sentence  = $external_problem['option4'];
		}
		// kaopiz 2020/09/15 speech end
		//	add koike 2012/06/29 end

		$action = "change";
		$button = "修正";
	} else {
		if ($_POST) {
			foreach ($_POST as $key => $val) {
				$val = stripslashes($val);
				//$val = ereg_replace("\n","//",$val);
				//$val = ereg_replace("&nbsp;"," ",$val);
				$val = str_replace("\n","//",$val);
				$val = str_replace("&nbsp;"," ",$val);
				$$key = $val;
			}
		}
		if ($problem_num) {
			$action = "change";
			$button = "修正";
			$mode_msg = "<input type=\"hidden\" name=\"mode\" value=\"view\">\n";
		} else {
			$action = "add";
			$button = "追加";
		}
	}

	//	エラー作成
	if ($error_msg) {
		$ERROR_LIST = explode("//",$error_msg);
		if ($ERROR_LIST) {
			foreach ($ERROR_LIST AS $val) {
				list($key,$value) = explode("::",$val);
				if ($key == "" || !$value) { continue; }
				$ERROR[$key] = $value;
			}
		}
	}

	if ($ERROR) {
		foreach ($ERROR AS $key => $val) {
			if (!$key) { continue; }
			$key = strtoupper($key);
			$key_name = "ERROR".ereg_replace("[^A-Z1-9]","",$key);
			$val = "　<span class=\"small_error\">&lt;&lt;".$val."</span>\n";
			$INPUTS[$key_name] = array('result'=>'plane','value'=>$val);
		}
		if ($display == 1) {
			$val = "　<span class=\"small_error\">&lt;&lt;エラーがあるため表示にできません。</span>\n";
			$INPUTS['ERRORDISPLAY'] = array('result'=>'plane','value'=>$val);
			$display = 2;
		}
	}
	$INPUTS['PROBLEMNUM'] = array('type'=>'text','name'=>'display_problem_num','size'=>'10','value'=>$display_problem_num);
	if (block_type == 1) {
		unset($L_PROBLEM_TYPE[1]);
		unset($L_PROBLEM_TYPE[2]);
		unset($L_PROBLEM_TYPE[3]);
	} elseif (block_type == 2) {
		unset($L_PROBLEM_TYPE[0]);
		unset($L_PROBLEM_TYPE[2]);
	} elseif (block_type == 3) {
		unset($L_PROBLEM_TYPE[0]);
	}
	$INPUTS['PROBLEMTYPE'] = array('type'=>'select','name'=>'problem_type','array'=>$L_PROBLEM_TYPE,'check'=>$problem_type);
//	$INPUTS[PROBLEMTYPE] = array('result'=>'plane','value'=>$L_PROBLEM_TYPE[$problem_type]);
	$INPUTS['SUBDISPLAYPROBLEMNUM'] = array('type'=>'text','name'=>'sub_display_problem_num','value'=>$sub_display_problem_num);
	// add start karasawa 2019/11/20 課題要望800 _www\template\admin\practice_form_type16&17.htmの<audio src="" id="sound_ajax" style="display:none;"></audio>を削除して<!--AUDIO-->を追加しました。
	// BG MUSIC  Firefox対応
	$pre = ".mp3";
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	if (preg_match("/Firefox/i", $user_agent) || preg_match("/Opera/i", $user_agent) || preg_match("/OPR/i", $user_agent)) { $pre = ".ogg"; }
	$audiofile = "<audio id=\"sound_ajax\" style=\"display:none;\" src=\"/student/images/drill/silent3sec".$pre."\"></audio>\n";
	$INPUTS["AUDIO"]   = array('result'=>'plane', 'value'=>$audiofile);
	// add end karasawa 2019/11/20 課題要望800


	if ($form_type) {
		$INPUTS['FORMTYPE'] = array('result'=>'plane','value'=>$L_FORM_TYPE[$form_type]);
	} else {
		$INPUTS['FORMTYPE'] = array('type'=>'select','name'=>'form_type','array'=>$L_FORM_TYPE,'check'=>$form_type);
	}

	// add start oda 2016/02/01 作図ツール 問題種類(表示項目)追加
	$drawing_html="";
	if ($form_type == 13) {
		if ($option1) { $drawing_type = $option1; } // 修正モードの場合はoption1に格納している
		if ($drawing_type) {
			$INPUTS['DRAWINGTYPE'] = array('result'=>'plane','value'=>$L_DRAWING_TYPE[$drawing_type]);
		} else {
			$INPUTS['DRAWINGTYPE'] = array('type'=>'select','name'=>'option1','array'=>$L_DRAWING_TYPE,'check'=>$drawing_type);
		}
		$drawing_html="<input type=\"hidden\" name=\"option1\" value=\"".$drawing_type."\">\n";
	}
	// add start oda 2016/02/01 作図ツール 問題種類(表示項目)追加
//    kaopiz 2020/08/25 speech start
	$course = getCourseByNum($_POST['course_num']);
	$speechSetting = ($course && $course['write_type'] == 1) && ($form_type != 11 && $form_type !=13 && $form_type != 16 && $form_type != 17);
	if ($speechSetting == true) {
		if (!$external_problem && MODE == 'add') {
			$speech_type = $GLOBALS['speechDefault'];
		}
		if($form_type == 18) {
			$INPUTS['SPEECHTYPE'] = array('result' => 'plane', 'value' => $L_SPEECH_EVALUATION[$drawing_type]);
			$drawing_html="<input type=\"hidden\" name=\"speech_type\" value=\"".$drawing_type."\">\n";
		} else {
			$INPUTS['SPEECHTYPE'] = array('type' => 'select', 'name' => 'speech_type', 'array' => $L_SPEECH_EVALUATION, 'check' => $speech_type);
		}
		$INPUTS['VOICESENTENCE'] = array('type'=>'text','name'=>'voice_sentence','size'=>'50','value'=>$voice_sentence);
		$INPUTS['MODELVOICE'] = array('type'=>'textarea','name'=>'model_voice','cols'=>'50','rows'=>'5','value'=>$model_voice);
		$INPUTS['MODELVOICERESULT'] = array('type'=>'textarea','name'=>'model_voice_result','cols'=>'50','rows'=>'5','value'=>$model_voice_result);
	}
//	kaopiz 2020/08/25 speech end

	$INPUTS['QUESTION'] = array('type'=>'textarea','name'=>'question','cols'=>'50','rows'=>'5','value'=>$question);
	$INPUTS['PROBLEM'] = array('type'=>'textarea','name'=>'problem','cols'=>'50','rows'=>'5','value'=>$problem);
	$INPUTS['VOICEDATA'] = array('type'=>'text','name'=>'voice_data','value'=>$voice_data,'size'=>'50');
	$INPUTS['HINT'] = array('type'=>'textarea','name'=>'hint','cols'=>'50','rows'=>'5','value'=>$hint);
	$INPUTS['EXPLANATION'] = array('type'=>'textarea','name'=>'explanation','cols'=>'50','rows'=>'5','value'=>$explanation);
	$INPUTS['ANSWERTIME'] = array('type'=>'text','name'=>'answer_time','value'=>$answer_time);
	$INPUTS['PARAMETER'] = array('type'=>'text','name'=>'parameter','value'=>$parameter,'size'=>'50');
	$INPUTS['SETDIFFICULTY'] = array('type'=>'text','name'=>'set_difficulty','value'=>$set_difficulty);
	$INPUTS['HINTNUMBER'] = array('type'=>'text','name'=>'hint_number','value'=>$hint_number);
	$INPUTS['CORRECTNUMBER'] = array('type'=>'text','name'=>'correct_number','value'=>$correct_number);
	$INPUTS['CLEARNUMBER'] = array('type'=>'text','name'=>'clear_number','value'=>$clear_number);
	$INPUTS['FIRSTPROBLEM'] = array('type'=>'textarea','name'=>'first_problem','cols'=>'50','rows'=>'5','value'=>$first_problem);
	$INPUTS['LATTERPROBLEM'] = array('type'=>'textarea','name'=>'latter_problem','cols'=>'50','rows'=>'5','value'=>$latter_problem);
	// if ($form_type !=4 && $form_type != 8 && $form_type != 10) {	// upd hasegawa 2018/03/09 百マス計算
	//update start kimura 2018/09/19 漢字学習コンテンツ_書写ドリル対応
	//if ($form_type !=4 && $form_type != 8 && $form_type != 10 && $form_type != 14) {
	if ($form_type !=4 && $form_type != 8 && $form_type != 10 && $form_type != 14 && $form_type != 15) {
	//update end   kimura 2018/09/19 漢字学習コンテンツ_書写ドリル対応
		$INPUTS['SELECTIONWORDS'] = array('type'=>'text','name'=>'selection_words','value'=>$selection_words,'size'=>'50');
	} else {
		$INPUTS['SELECTIONWORDS'] = array('type'=>'textarea','name'=>'selection_words','cols'=>'50','rows'=>'5','value'=>$selection_words);
	}
	$INPUTS['CORRECT'] = array('type'=>'text','name'=>'correct','value'=>$correct,'size'=>'50');
	$INPUTS['OPTION1'] = array('type'=>'text','name'=>'option1','value'=>$option1,'size'=>'50');
//	$INPUTS['OPTION2'] = array('type'=>'text','name'=>'option2','value'=>$option2,'size'=>'50');	// del hasegawa 2016/10/25 入力フォームサイズ指定項目追加 下に移動
	$INPUTS['OPTION4'] = array('type'=>'text','name'=>'option4','value'=>$option4,'size'=>'50');
//	$INPUTS['OPTION5'] = array('type'=>'text','name'=>'option5','value'=>$option5,'size'=>'50');	// del hasegawa 2016/09/28 国語文字数カウント 下に移動

	//update oda 2015/06/12 JICAインド数学
	//$INPUTS[OPTION3] = array('type'=>'text','name'=>'option3','value'=>$option3,'size'=>'50');
	// テキスト入力の問題の場合は、「解答を英語解析する」を表示する ※ラジオボタンにすると確認画面にパラメータがうまく渡らなかった。。。
	$input_setting_html = "";

	if ($form_type == 3) {
		// ---- add start hasegawa 2016/10/25 入力フォームサイズ指定項目追加
		// 解答欄行数と解答欄サイズの項目はoption2に<>区切りで格納されています
		if($option2) {
			list($input_row,$input_size) = get_option2($option2);
		}

		$INPUTS['INPUTROW'] = array('type'=>'text','name'=>'input_row','value'=>$input_row,'size'=>'50');
		$INPUTS['INPUTSIZE'] = array('type'=>'text','name'=>'input_size','value'=>$input_size,'size'=>'50');
		$input_size_att = "<br><span style=\"color:red;\">※指定する場合は、最大40までの値を設定してください。</span>";
		$INPUTS['INPUTSIZEATT'] = array('result'=>'plane','value'=>$input_size_att);
		// ---- add end hasegawa 2016/10/25 入力フォームサイズ指定項目追加

		if ($option3 == "") { $option3 = "0"; }
		// $BUD_SELECT_LIST = array('0'=>'解析しない', '1'=>'解析する'); // del karasawa 2019/07/23 BUD英語解析開発
		$INPUTS['OPTION3'] = array('type'=>'select','name'=>'option3','array'=>$BUD_SELECT_LIST,'check'=>$option3);
		$option3_att = "<br><span style=\"color:red;\">※数学コースで英語の解答を使用する場合は、「解析する」を設定して下さい。</span>";
		$INPUTS['OPTION3ATT'] 	= array('result'=>'plane','value'=>$option3_att);

		// add start hasegawa 2016/09/28 国語文字数カウント
		// courseよりwrite_typeを取得し、2の場合はOPTION5をCOUNTSETTINGとして出力
		$write_type = "";
		$sql = "SELECT write_type FROM ".T_COURSE.
			" WHERE course_num = '".$_POST['course_num']."' AND display='1' AND state='0' LIMIT 1;";

		if ($result = $cdb->query($sql)) {
			if ($list = $cdb->fetch_assoc($result)) {
				$write_type = $list['write_type'];
			}
		}
		// if($write_type == 2) {					// del 2018/05/14 yoshizawa 理科社会対応
		if($write_type == 2 || $write_type == 16) {	// add 2018/05/14 yoshizawa 理科社会対応

			if ($option5 == "") { $option5 = "false"; }
			$newform = new form_parts();
			$newform->set_form_type("radio");
			$newform->set_form_name("option5");
			$newform->set_form_id("count_set_off");
			$newform->set_form_check($option5);
			$newform->set_form_value("false");
			$count_set_off = $newform->make();
			$newform = new form_parts();
			$newform->set_form_type("radio");
			$newform->set_form_name("option5");
			$newform->set_form_id("count_set_on");
			$newform->set_form_check($option5);
			$newform->set_form_value("true");
			$count_set_on = $newform->make();

			$count_html = "<tr>\n";
			$count_html .= "<td class=\"member_form_menu\">文字数カウント設定</td>\n";
			$count_html .= "<td class=\"member_form_cell\">\n";
			$count_html .= $count_set_off . "<label for=\"count_set_off\">設定しない</label> / " . $count_set_on . "<label for=\"count_set_on\">設定する</label>";
			$count_html .= "</td>\n";
			$count_html .= "</tr>\n";
			$INPUTS['COUNTSETTING'] = array('result'=>'plane','value'=>$count_html);
		}
		// add end hasegawa 2016/09/28

	} else {
		$INPUTS['OPTION2'] = array('type'=>'text','name'=>'option2','value'=>$option2,'size'=>'50');	// add hasegawa 2016/10/25 入力フォームサイズ指定項目追加
		$INPUTS['OPTION3'] = array('type'=>'text','name'=>'option3','value'=>$option3,'size'=>'50');
		$INPUTS['OPTION5'] = array('type'=>'text','name'=>'option5','value'=>$option5,'size'=>'50');	// add hasegawa 2016/09/28 国語文字数カウント
		// add karasawa 2019/07/20 BUD英語解析開発
		if ($form_type ==  4 || $form_type == 10 || $form_type == 14) {
			if ($option3 == "") { $option3 = "0"; }
			$INPUTS['OPTION3'] = array('type'=>'select','name'=>'option3','array'=>$BUD_SELECT_LIST,'check'=>$option3);
			$option3_att = "<br><span style=\"color:red;\">※数学コースで英語の解答を使用する場合は、「解析する」を設定して下さい。</span>";
			$INPUTS['OPTION3ATT'] 	= array('result'=>'plane','value'=>$option3_att);
		}
		// add end karasawa 2019/07/20
	}
	// update oda 2015/06/12 JICAインド数学

	// add start oda 2012/12/26
	if ($sentence_flag == "") { $sentence_flag = "1"; }		// update oda 2013/01/17 1 -> 0
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("sentence_flag");
	$newform->set_form_id("sentence_flag");
	$newform->set_form_check($sentence_flag);
	$newform->set_form_value('0');
	$male = $newform->make();
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("sentence_flag");
	$newform->set_form_id("unsentence_flag");
	$newform->set_form_check($sentence_flag);
	$newform->set_form_value('1');
	$female = $newform->make();
	$sentence_flag = $male . "<label for=\"sentence_flag\">{$L_SENTENCE_FLAG[0]}</label> / " . $female . "<label for=\"unsentence_flag\">{$L_SENTENCE_FLAG[1]}</label>";
	$INPUTS[SENTENCEFLAG] = array('result'=>'plane','value'=>$sentence_flag);
	// add end oda 2012/12/26

//	$unit_num_att = "<br><span style=\"color:red;\">※設定したいLMS単元のＩＤを入力して下さい。";	//	add koike 2012/06/29	// del oda 2012/08/22
    $upper_check_s = get_surala_unit($_POST['course_num'], $_POST['stage_num'], 0, 0, 0, 0, 0);
    $upper_check_l = get_surala_unit($_POST['course_num'], $_POST['stage_num'], $_POST['lesson_num'], 0, 0, 0, 0);
    $upper_check_u = get_surala_unit($_POST['course_num'], $_POST['stage_num'], $_POST['lesson_num'], $_POST['unit_num'], 0, 0, 0);
    $upper_check_b = get_surala_unit($_POST['course_num'], $_POST['stage_num'], $_POST['lesson_num'], $_POST['unit_num'], $_POST['block_num'], 0, 0);
    if (strlen($upper_check_s) == 0 && strlen($upper_check_l) == 0 && strlen($upper_check_u) == 0 && strlen($upper_check_b) == 0) {
		$INPUTS[LMS] = array('type'=>'text','name'=>'unit_id','value'=>$unit_id,'size'=>'50');	//	add koike 2012/06/29
    	$unit_num_att = "<br><span style=\"color:red;\">※すららで復習するコース番号＋ユニットキーを入力して下さい。";		// add oda 2012/08/22
		$unit_num_att .= "<br>※複数設定する場合は、「 :: 」区切りで入力して下さい。</span>";	//	add koike 2012/06/29
		$INPUTS[LMSATT] 	= array('result'=>'plane','value'=>$unit_num_att);	//	add koike 2012/06/29
	} elseif (strlen($upper_check_s) > 0) {
		$INPUTS[LMS] = array('type'=>'hidden','name'=>'unit_id','value'=>"");
		$unit_num_att = "<span style=\"color:red;\">上位階層（Stage）にて設定済です</span>";
		$INPUTS[LMSATT] 	= array('result'=>'plane','value'=>$unit_num_att);
	} elseif (strlen($upper_check_l) > 0) {
		$INPUTS[LMS] = array('type'=>'hidden','name'=>'unit_id','value'=>"");
		$unit_num_att = "<span style=\"color:red;\">上位階層（Lesson）にて設定済です</span>";
		$INPUTS[LMSATT] 	= array('result'=>'plane','value'=>$unit_num_att);
	} elseif (strlen($upper_check_u) > 0) {
		$INPUTS[LMS] = array('type'=>'hidden','name'=>'unit_id','value'=>"");
		$unit_num_att = "<span style=\"color:red;\">上位階層（Unit）にて設定済です</span>";
		$INPUTS[LMSATT] 	= array('result'=>'plane','value'=>$unit_num_att);
	} elseif (strlen($upper_check_b) > 0) {
		$INPUTS[LMS] = array('type'=>'hidden','name'=>'unit_id','value'=>"");
		$unit_num_att = "<span style=\"color:red;\">上位階層（ドリル）にて設定済です</span>";
		$INPUTS[LMSATT] 	= array('result'=>'plane','value'=>$unit_num_att);
	}

	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("display");
	$newform->set_form_id("display");
	$newform->set_form_check($display);
	$newform->set_form_value('1');
	$male = $newform->make();
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("display");
	$newform->set_form_id("undisplay");
	$newform->set_form_check($display);
	$newform->set_form_value('2');
	$female = $newform->make();
	$display = $male . "<label for=\"display\">{$L_DISPLAY[1]}</label> / " . $female . "<label for=\"undisplay\">{$L_DISPLAY[2]}</label>";
	$INPUTS[DISPLAY] = array('result'=>'plane','value'=>$display);

	//add start kimura 2018/10/19 漢字学習コンテンツ_書写ドリル対応
	$problem_tegaki_flg = get_problem_tegaki_flg($problem_num);

	$f = new form_parts();
	$f->set_form_type("radio");
	$f->set_form_name("problem_tegaki_flg");
	$f->set_form_id("prblem_tegaki_flg_on");
	$f->set_form_check($problem_tegaki_flg);
	$f->set_form_value("1");
	$on = $f->make();
	$f = null;

	$f = new form_parts();
	$f->set_form_type("radio");
	$f->set_form_name("problem_tegaki_flg");
	$f->set_form_id("problem_tegaki_flg_off");
	$f->set_form_check($problem_tegaki_flg);
	$f->set_form_value("0");
	$off = $f->make();

	$problem_tegaki_flg_html = $on . "<label for=\"problem_tegaki_flg_on\">ON</label> / " . $off . "<label for=\"problem_tegaki_flg_off\">OFF</label>";
	$INPUTS['PROBLEMTEGAKIFLG'] = array('result'=>'plane','value'=>$problem_tegaki_flg_html);
	//add end   kimura 2018/10/19 漢字学習コンテンツ_書写ドリル対応

	// サービスタイプ取得		// add oda 2014/06/30
	$service_type = get_service_type($_POST['course_num']);
//	$inc_file = "practice_form_type_" . $form_type . ".htm";
	// kaopiz 2020/09/15 speech start
	 if($speechSetting == true) {
		$inc_file = "practice_form_type_speech_" . $form_type . ".htm";
	 } else {
		$inc_file = "practice_form_type_" . $form_type . ".htm";
	 }
	// kaopiz 2020/09/15 speech end
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file("$inc_file");
	$make_html->set_rep_cmd($INPUTS);


	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"problem_form\">\n";
	$html .= $mode_msg;
	$html .= "<input type=\"hidden\" name=\"action\" value=\"".$action."\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$_POST['stage_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"".$_POST['unit_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"block_num\" value=\"".$_POST['block_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"".$problem_num."\">\n";
	$html .= "<input type=\"hidden\" name=\"form_type\" value=\"".$form_type."\">\n";
	$html .= $drawing_html;													// add oda 2016/02/01 作図ツール drawing_type追加 // upd hasgeawa 2016/05/31 作図ツール
	$html .= "<input type=\"hidden\" name=\"block_type\" value=\"".block_type."\">\n";
	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"".$button."\">";
	//$html .= "<input type=\"button\" value=\"確認\" onclick=\"check_problem_win_open()\">";						// del oda 2014/06/30
	//add start hirose 2018/09/21 英単語マスター機能追加対応(音声自動再生)
	if($form_type == 16 || $form_type == 17){
	$html .= "<input type=\"button\" value=\"確認\" onclick=\"play_sound_ajax_admin('sound_ajax', '/student/images/drill/silent3sec.mp3', '', '');check_problem_win_open('".$service_type."')\">";		// update oda 2014/06/30 $service_type追加
	}else{
	//add end hirose 2018/09/21 英単語マスター機能追加対応(音声自動再生)
	$html .= "<input type=\"button\" value=\"確認\" onclick=\"check_problem_win_open('".$service_type."')\">";		// update oda 2014/06/30 $service_type追加
	} //add hirose 2018/09/21 英単語マスター機能追加対応(音声自動再生)
	$html .= "</form>\n";

	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"problem_list\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$_POST['stage_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"".$_POST['unit_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"block_num\" value=\"".$_POST['block_num']."\">\n";
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

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	foreach($_POST as $key => $val) {
		if ($key == "action") { continue; }
		elseif ($key == "problem_num") { continue; }
		elseif ($key == "stage_num") { continue; }
		elseif ($key == "lesson_num") { continue; }
		elseif ($key == "unit_num") { continue; }
//		elseif ($key == "unit_id") { continue; }		//	add koike 2012/06/29
		elseif ($key == "unit_id") {				// 2012/09/27 add oda
			$val = change_unit_key_to_num_all($val);	// 2012/09/27 add oda
		} elseif ($key == "block_type") {
			define("block_type", $val);			//	add ookawara 2012/06/11
			continue;
		} elseif ($key == "input_row" || $key == "input_size") {	// add hasegawa 2016/10/25 入力フォームサイズ指定項目追加
			$$key = $val;
			continue;
		}
		//$LINE[$key] = replace_word($val);		// del okabe 2018/08/07 理科 問題表示文字調整「，＋ー」
		// add start okabe 2018/08/27 理科 問題表示文字調整「，＋ー」
		$tmpx_course_num = intval($_POST['course_num']);
		$sql = "SELECT write_type FROM ".T_COURSE." WHERE course_num='".$tmpx_course_num."' LIMIT 1;";
		$tmpx_write_type = 0;
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$tmpx_write_type = $list['write_type'];
		}
		if ($tmpx_write_type == 15) {	//理科の場合
			$argsx = array('val'=>$val, 'key'=>$key, 'form_type'=>$_POST['form_type']);
			$LINE[$key] = filter_science_datasx($argsx);
		} else {
			$LINE[$key] = replace_word($val);
		}
		// add end okabe 2018/08/27
	}

	// add start hasegawa 2016/10/25 入力フォームサイズ指定項目追加
	if($LINE['form_type'] == 3 && !$LINE['option2']) {
		$LINE['option2'] = make_option2($input_row,$input_size);
		$LINE['option2'] = replace_word($LINE['option2']);
	}
	// add end hasegawa 2016/10/25 入力フォームサイズ指定項目追加

	unset($C_ERROR);
	unset($S_ERROR);
	$regist_problem = new regist_problem();
	$C_ERROR = $regist_problem->check_data($LINE);

	$sql = "SELECT * FROM " . T_PROBLEM .
		" WHERE block_num='$LINE[block_num]'".
		" AND course_num='".$_POST['course_num']."'".	//	add ookawara 2010/01/07
		" AND display_problem_num='$LINE[display_problem_num]' AND state!='1'";
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
	}
	if ($max) {
		$C_ERROR['display_problem_num'] = "入力された問題番号は既に利用されています。";
	}

	//	エラー記録処理
	unset($error_msg);
	unset($LINE['error_msg']);
	if ($C_ERROR) {
		foreach ($C_ERROR AS $key => $val) {
			$LINE['error_msg'] .= $key."::".$val."\n";
			$_POST['error_msg'] .= $key."::".$val."\n";
		}
		$LINE['display'] = 2;
	}

	$regist_problem->set_reggist_type($_POST[problem_num]);
	$S_ERROR = $regist_problem->reggist_data($LINE);
	$_POST['problem_num'] = $cdb->insert_id();
	if ($S_ERROR) {
		foreach ($S_ERROR AS $key => $val) {
			if (!$val) { continue; }
			$ERROR[] = $val;
		}
	}
	return $ERROR;
}

/**
 * DB変更
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function change() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	foreach($_POST as $key => $val) {
		if ($key == "mode") { continue; }
		elseif ($key == "action") { continue; }
		elseif ($key == "problem_num") { continue; }
		elseif ($key == "stage_num") { continue; }
		elseif ($key == "lesson_num") { continue; }
		elseif ($key == "unit_num") { continue; }
//		elseif ($key == "unit_id") { continue; }	//	add koike 2012/06/29
		elseif ($key == "unit_id") {					// 2012/09/27 add oda
			$val = change_unit_key_to_num_all($val);	// 2012/09/27 add oda
		} elseif ($key == "block_type") {
			define("block_type", $val);	//	add ookawara 2012/06/11
			continue;
		} elseif ($key == "input_row" || $key == "input_size") {	// add hasegawa 2016/10/25 入力フォームサイズ指定項目追加
			$$key = $val;
			continue;
		}
		//$LINE[$key] = replace_word($val);		// del okabe 2018/08/07 理科 問題表示文字調整「，＋ー」
		// add start okabe 2018/08/27 理科 問題表示文字調整「，＋ー」
		$tmpx_course_num = intval($_POST['course_num']);
		$sql = "SELECT write_type FROM ".T_COURSE." WHERE course_num='".$tmpx_course_num."' LIMIT 1;";
		$tmpx_write_type = 0;
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$tmpx_write_type = $list['write_type'];
		}
		if ($tmpx_write_type == 15) {	//理科の場合
			$argsx = array('val'=>$val, 'key'=>$key, 'form_type'=>$_POST['form_type']);
			$LINE[$key] = filter_science_datasx($argsx);
		} else {
			$LINE[$key] = replace_word($val);
		}
		// add end okabe 2018/08/27
	}

	// add start hasegawa 2016/10/25 入力フォームサイズ指定項目追加
	if($LINE['form_type'] == 3 && !$LINE['option2']) {
		$LINE['option2'] = make_option2($input_row,$input_size);
		$LINE['option2'] = replace_word($LINE['option2']);
	}
	// add end hasegawa 2016/10/25 入力フォームサイズ指定項目追加

	unset($C_ERROR);
	unset($S_ERROR);

	$regist_problem = new regist_problem();
	$C_ERROR = $regist_problem->check_data($LINE);
	$sql = "SELECT * FROM " . T_PROBLEM .
		" WHERE problem_num!='$_POST[problem_num]' AND block_num='$LINE[block_num]'".
		" AND course_num='".$_POST['course_num']."'".	//	add ookawara 2010/01/07
		" AND display_problem_num='$LINE[display_problem_num]' AND state!='1'";
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
	}
	if ($max) {
		$C_ERROR[display_problem_num] = "入力された問題番号は既に利用されています。";
	}
	//	エラー記録処理
	unset($error_msg);
	unset($LINE['error_msg']);
	if ($C_ERROR) {
		foreach ($C_ERROR AS $key => $val) {
			$LINE['error_msg'] .= $key."::".$val."\n";
		}
		$LINE['display'] = 2;
	} else {
		$LINE['error_msg'] = "";
	}

	$regist_problem->set_reggist_type($_POST[problem_num]);
	$S_ERROR = $regist_problem->reggist_data($LINE);
	if ($S_ERROR) {
		foreach ($S_ERROR AS $key => $val) {
			if (!$val) { continue; }
			$ERROR[] = $val;
		}
	}
	return $ERROR;
}

/**
 * problemはDB削除
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function delete() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (!$_POST[problem_num]) { $ERROR[] = "DELETE ERROR : NUM"; }

	if (!$ERROR) {
		$INSERT_DATA[display] = 2;
		$INSERT_DATA[state] = 1;
		$INSERT_DATA[update_time] = "now()";
		$where = " WHERE problem_num='$_POST[problem_num]'";

		$ERROR = $cdb->update(T_PROBLEM,$INSERT_DATA,$where);
	}
	// kaopiz 2020/09/15 speech start
	$externalProblem = findExternalProblem($_POST['problem_num']);
	if(!$ERROR && $externalProblem) {
		$UPDATE_DATA['move_flg'] = 1;
		$UPDATE_DATA['move_tts_id'] = $_SESSION['myid']['id'];
		$UPDATE_DATA['move_date'] = "now()";

		$where = " WHERE problem_num='$_POST[problem_num]'";
		$ERROR = $cdb->update(T_EXTERNAL_PROBLEM, $UPDATE_DATA, $where);
	}
	// kaopiz 2020/09/15 speech end

	//LMS単元削除
//	lms_unit_id_del ($_POST['problem_num']);		// 2012/08/06 del oda
	lms_unit_id_del ($_POST['course_num'], $_POST['stage_num'], $_POST['lesson_num'], $_POST['unit_num'], $_POST['block_num'], $_POST['problem_num']);	// 2012/08/06 update oda

	return $ERROR;
}

/**
 * blockはDB削除
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function del() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (!$_POST[block_num]) { $ERROR[] = "削除するBlock情報が確認できません。"; }

	//LMS単元削除処理(Block単位)
	block_lms_unit_del($_POST['block_num']);	//	add koike 2012/07/02

	if (!$ERROR) {
		$INSERT_DATA[state] = 1;
		$INSERT_DATA[update_time] = "now()";
		$where = " WHERE block_num='$_POST[block_num]'";

		$ERROR = $cdb->update(T_PROBLEM,$INSERT_DATA,$where);
	}
	return $ERROR;
}


// 	add koike 2012/07/02 start

/**
 * MS単元削除処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $course_num
 * @param integer $stage_num
 * @param integer $lesson_num
 * @param integer $unit_num
 * @param integer $block_num
 * @param integer $problem_num
 * @return array エラーの場合
 */
function lms_unit_id_del ($course_num, $stage_num, $lesson_num, $unit_num, $block_num, $problem_num) {	// 2012/08/06 update oda
//function lms_unit_id_del ($problem_num) {																// 2012/08/06 del oda

	if ($ERROR) { return; }

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$DEL_DATA = array();

//	$sql = "SELECT unit_num FROM " .				// 2012/08/06 del oda
	$sql = "SELECT " .								// 2012/08/06 update oda
			" plu.surala_unit_num ".
			" FROM ".T_PROBLEM_LMS_UNIT . " plu ".
			" WHERE plu.problem_num='".$problem_num."'".
			"   AND plu.course_num='".$course_num."'".	// 2012/08/06 add oda
			"   AND plu.stage_num='".$stage_num."'".	// 2012/08/06 add oda
			"   AND plu.lesson_num='".$lesson_num."'".	// 2012/08/06 add oda
			"   AND plu.unit_num='".$unit_num."'".		// 2012/08/06 add oda
			"   AND plu.block_num='".$block_num."'".	// 2012/08/06 add oda
			"   AND plu.one_point_num='0'".				// add oda 2013/08/05
			"   AND plu.mk_flg = '0' ;";
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
//			$DEL_DATA[] = $list['unit_num'];			// 2012/08/06 del oda
			$DEL_DATA[] = $list['surala_unit_num'];		// 2012/08/06 update oda
		}
	}

	if (!$ERROR) {
		foreach ($DEL_DATA AS $val) {

			$INSERT_DATA['mk_flg'] = 1;
			$INSERT_DATA['mk_tts_id'] = $_SESSION['myid']['id'];
			$INSERT_DATA['mk_date'] = "now()";
			$INSERT_DATA['upd_syr_id']   = "del";									// 2012/11/05 add oda
			$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];					// 2012/11/05 add oda
			$INSERT_DATA['upd_date']   = "now()";									// 2012/11/05 add oda

			$where = " WHERE mk_flg='0' ".
					 " AND course_num='".$course_num."'".		// 2012/08/06 add oda
					 " AND stage_num='".$stage_num."'".			// 2012/08/06 add oda
					 " AND lesson_num='".$lesson_num."'".		// 2012/08/06 add oda
					 " AND unit_num='".$unit_num."'".			// 2012/08/06 add oda
					 " AND block_num='".$block_num."'".			// 2012/08/06 add oda
					 " AND problem_num='".$problem_num."'".
					 " AND one_point_num='0'".					// add oda 2013/08/05
//					 " AND unit_num='".$val."'";				// 2012/08/06 del oda
					 " AND surala_unit_num='".$val."'";			// 2012/08/06 update oda


			$ERROR = $cdb->update(T_PROBLEM_LMS_UNIT,$INSERT_DATA,$where);
		}
	}
	return $ERROR;
}


/**
 * MS単元Block単位削除
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $block_num
 * @return array エラーの場合
 */
function block_lms_unit_del($block_num){

	if ($ERROR) { return; }

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

// 2012/08/12 update start oda
//	$sql = "SELECT plu.unit_num,".
//			" tp.problem_num".
//			" FROM ".T_PROBLEM." tp".
//			" LEFT JOIN ".T_PROBLEM_LMS_UNIT." plu".
//			" ON tp.problem_num = plu.problem_num".
//			" AND plu.mk_flg != '1'".
//			" WHERE tp.block_num = '".$block_num."'".
//			" AND tp.state != '1';";
	$sql = "SELECT plu.surala_unit_num, ".
			"      plu.course_num,".
			"      plu.stage_num,".
			"      plu.lesson_num,".
			"      plu.unit_num,".
			"      plu.block_num,".
			"      plu.problem_num ".
			" FROM ".T_PROBLEM_LMS_UNIT." plu ".
			" WHERE plu.block_num = '".$block_num."'".
			"   AND plu.one_point_num='0'".				// add oda 2013/08/05
			"   AND plu.mk_flg = '0';";
// 2012/08/12 update end oda

	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			if (!$ERROR) {
				$INSERT_DATA['mk_flg'] = 1;
				$INSERT_DATA['mk_tts_id'] = $_SESSION['myid']['id'];
				$INSERT_DATA['mk_date'] = "now()";
				$INSERT_DATA['upd_syr_id']   = "del";								// 2012/11/05 add oda
				$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];				// 2012/11/05 add oda
				$INSERT_DATA['upd_date']   = "now()";								// 2012/11/05 add oda
				$where = " WHERE mk_flg='0' ".
						 " AND problem_num='".$list['problem_num']."'".
						 " AND course_num='".$list['course_num']."'".				// 2012/08/06 add oda
						 " AND stage_num='".$list['stage_num']."'".					// 2012/08/06 add oda
						 " AND lesson_num='".$list['lesson_num']."'".				// 2012/08/06 add oda
						 " AND unit_num='".$list['unit_num']."'".					// 2012/08/06 add oda
						 " AND block_num='".$list['block_num']."'".					// 2012/08/06 add oda
						 " AND one_point_num='0'".									// add oda 2013/08/05
//						 " AND unit_num='".$list['unit_num']."'";					// 2012/08/06 del oda
						 " AND surala_unit_num='".$list['surala_unit_num']."'";		// 2012/08/06 update oda

				$ERROR = $cdb->update(T_PROBLEM_LMS_UNIT,$INSERT_DATA,$where);
			}
		}
	}
	return $ERROR;
}
//	add koike 2012/07/02 end


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
	//upd start hirose すらら英単語
	//非表示のサービスタイプも、確認画面で表示させるため
//	$sql .= " WHERE scl.display = '1' ";
//	$sql .= "   AND scl.mk_flg = '0' ";
	$sql .= " WHERE scl.mk_flg = '0' ";
	//upd end hirose すらら英単語
	$sql .= "   AND scl.course_num = '".$course_num."' ";
	$sql .= "   AND scl.course_type  = '1' ";

//	echo $sql."<br>";

	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$service_type = $list['setup_type'];
		}
	}

	return $service_type;
}

/**
 * 解答欄行数と解答欄サイズをoption2として出力する
 * (form_type3で使用)
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author azet
 * @param string $input_row 	解答欄行数
 * @param string $input_size	解答欄サイズ
 * @return string $option2
 */

function make_option2($input_row,$input_size) {	// add hasegawa 2016/10/26 入力フォームサイズ指定項目追加

	$option2 = "";

	if($input_row) {
		$option2 = $input_row;
	}
	if($input_size) {
		$option2 .= "&lt;&gt;".$input_size;
	}
	return $option2;
}
/**
 * optionから2解答欄行数と解答欄サイズを取得する
 * (form_type3で使用)
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author azet

 * @return string $option2
 * @return array 		解答欄行数,解答欄サイズ
 */

function get_option2($option2) {	// add hasegawa 2016/10/26 入力フォームサイズ指定項目追加

	$input_row = "";
	$input_size = "";

	$option2 = str_replace("&lt;","<",$option2);
	$option2 = str_replace("&gt;",">",$option2);

	if (preg_match("/<>/",$option2)) {
		list($input_row,$input_size) = explode("<>",$option2);
	} else {
		$input_row = $option2;
	}

	return array($input_row, $input_size);
}

?>
