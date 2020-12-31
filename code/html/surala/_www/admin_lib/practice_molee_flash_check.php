<?
/**
 * ベンチャー・リンク　すらら
 *
 * molee用flash確認画面 アプリ header 部の制御
 *
 * 履歴
 * 2014/04/03 初期設定
 *
 * @author Azet
 */

// okabe

/**
 * HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function start() {

	list($html,$L_COURSE,$L_STAGE,$L_LESSON) = select_course();

	if ($_POST[course_num]&&$_POST[stage_num]&&$_POST[lesson_num]) {
		$html .= unit_list($L_COURSE,$L_STAGE,$L_LESSON);
	}

	return $html;
}


/**
 * コース選択
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array
 */
function select_course() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if ($_POST['course_num'] && $_POST['b_course_num'] && $_POST['b_course_num'] != $_POST['course_num']) { unset($_POST['stage_num']); }

	$sql  = "SELECT * FROM ".T_COURSE.
			" WHERE state!='1' ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
	}
	if (!$max) {
		$html = "<br>\n";
		$html .= "コースが存在しません。設定してからご利用下さい。";
		return array($html,$L_COURSE);
	} else {
		if (!$_POST['course_num']) { $selected = "selected"; } else { $selected = ""; }
		$couse_num_html .= "<option value=\"\" $selected>選択して下さい</option>\n";
		while ($list = $cdb->fetch_assoc($result)) {
			$course_num_ = $list['course_num'];
			$course_name_ = $list['course_name'];
			$L_COURSE[$course_num_] = $course_name_;
			if ($_POST['course_num'] == $course_num_) { $selected = "selected"; } else { $selected = ""; }
			$couse_num_html .= "<option value=\"{$course_num_}\" $selected>{$course_name_}</option>\n";
		}
	}

	if ($_POST[course_num]) {
		$sql  = "SELECT * FROM ".T_STAGE.
				" WHERE course_num='$_POST[course_num]' AND state!='1' ORDER BY list_num;";

		if ($result = $cdb->query($sql)) {
			$max = $cdb->num_rows($result);
		}
		if (!$max) {
			$stage_num_html .= "<option value=\"\">--------</option>\n";
		} else {
			if (!$_POST['course_num']) { $selected = "selected"; } else { $selected = ""; }
			$stage_num_html .= "<option value=\"\" $selected>選択して下さい</option>\n";
			while ($list = $cdb->fetch_assoc($result)) {
				$L_STAGE[$list[stage_num]] = $list[stage_name];
				if ($_POST[stage_num] == $list[stage_num]) { $selected = "selected"; } else { $selected = ""; }
				$stage_num_html .= "<option value=\"{$list[stage_num]}\" $selected>{$list[stage_name]}</option>\n";
			}
		}
	} else {
		$stage_num_html .= "<option value=\"\">--------</option>\n";
	}

	if ($_POST[stage_num]) {
		$sql  = "SELECT * FROM ".T_LESSON.
				" WHERE stage_num='$_POST[stage_num]' AND state!='1' ORDER BY list_num;";
		if ($result = $cdb->query($sql)) {
			$max = $cdb->num_rows($result);
		}
		if (!$max) {
			$lesson_num_html .= "<option value=\"\">ステージ内にLessonがありません。</option>\n";
		} else {
			if (!$_POST['lesson_num']) { $selected = "selected"; } else { $selected = ""; }
			$lesson_num_html .= "<option value=\"\" $selected>選択して下さい</option>\n";
			while ($list = $cdb->fetch_assoc($result)) {
				$L_LESSON[$list[lesson_num]] = $list[lesson_name];
				if ($_POST[lesson_num] == $list[lesson_num]) { $selected = "selected"; } else { $selected = ""; }
				$lesson_num_html .= "<option value=\"{$list[lesson_num]}\" $selected>{$list[lesson_name]}</option>\n";
			}
		}
	} else {
		$lesson_num_html .= "<option value=\"\">--------</option>\n";
	}

	$html = "";
//	$html .= "<a name=\"operation\" /><br>";
//$html .= "<a href=\"javascript:reloadWebPage2();\">Reload</a>　";
	$html .= "<br>\n";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" name=\"menu\">\n";

	$html .= "<input type=\"hidden\" name=\"mode\" value=\"set_course\">\n";
	$html .= "<table border=\"0\"><tr><td>\n";
		$html .= "<table class=\"unit_form\">\n";
		$html .= "<tr class=\"unit_form_menu\">\n";
		$html .= "<td>コース</td>\n";
		$html .= "<td>ステージ</td>\n";
		$html .= "<td>Lesson</td>\n";
		$html .= "</tr>\n";
		$html .= "<tr class=\"unit_form_cell\">\n";
		$html .= "<td><select name=\"course_num\" onchange=\"check_molee_env(); molee_admin_close(); submit_course();\" id=\"course_num_id\" disabled>\n";
		$html .= $couse_num_html;
		$html .= "</select></td>\n";
		$html .= "<td><select name=\"stage_num\" onchange=\"check_molee_env(); molee_admin_close(); submit_stage();\" id=\"stage_num_id\" disabled>\n";
		$html .= $stage_num_html;
		$html .= "</select></td>\n";
		$html .= "<td><select name=\"lesson_num\" onchange=\"check_molee_env(); molee_admin_close(); submit(); \" id=\"lesson_num_id\" disabled>\n";
		$html .= $lesson_num_html;
		$html .= "</select></td>\n";
		$html .= "</tr>\n";
		$html .= "</table>\n";
	//$html .= "</form><br>\n";
	$html .= "</td><td>";
	$html .= "&nbsp;<a href=\"javascript:reloadWebPage('header')\">再読み込み</a>\n";
	//$html .= "</td></tr></table>\n";	//del okabe 2014/08/08
	$html .= "</td><td>";	//add okabe start 2014/08/08
	$html .= "&nbsp;<a href=\"javascript:closeAdminMain()\">一覧表示を閉じる</a><br>\n";
	$html .= "</td></tr></table>\n";	//add okabe end 2014/08/08

	$html .= "</form>\n";
	$html .= "<br/>\n";

	if (!$_POST[course_num]) {
		//$html .= "<br>\n";
		$molee_flag = 0;
		if ($_COOKIE['suralaMoleeX']) {		//molee動作環境かチェック(cookieがセットされているか）
			if ($_COOKIE['suralaMoleeX'] == "x6810") {
				$molee_flag = 1;
			}
		}
		if ($molee_flag != 0) {
			$html .= "<div id=\"wait_msg\">コースを選択してください。</div><br>\n";
		} else {
			$html .= "<div id=\"wait_msg\">Moleeアプリ環境で実行してください。</div><br>\n";
		}
	} elseif ($_POST[course_num] && !$_POST[stage_num]) {
		//$html .= "<br>\n";
		$html .= "<div id=\"wait_msg\">ステージを選択してください。</div><br>\n";
	} elseif ($_POST[course_num] && $_POST[stage_num] && !$_POST[lesson_num]) {
		//$html .= "<br>\n";
		$html .= "<div id=\"wait_msg\">Lessonを選択してください。</div><br>\n";
	}
	if ($_POST[course_num] && $_POST[stage_num] && $_POST[lesson_num]) {
		$html .= "<div id=\"wait_msg\"><br><br><br><br><br>&nbsp;少々お待ちください。</div>";
	}

	return array($html,$L_COURSE,$L_STAGE,$L_LESSON);
}

/**
 * 空っぽの値をreturnする
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param $L_COURSE
 * @param $L_STAGE
 * @param $L_LESSON
 * @return string HTML
 */
function unit_list($L_COURSE,$L_STAGE,$L_LESSON) {

	$html = "";

	return $html;
}
?>