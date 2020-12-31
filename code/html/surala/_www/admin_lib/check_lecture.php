<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * レクチャーページ確認表示（計算マスター用）
 *
 * @author Azet
 */


/**
 * GETパラメター判断して、HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @return string HTML
 */
function start() {


//	if ($_GET['param'] == "end") {	// upd hasegawa 2016/05/16 小学生低学年版対応
//		$html = end_html();	// 下に新規で記述
//	} else {
//		$html = lecture_html();
//	}

	if ($_GET['erea'] == "lecture") {
		$html = lecture_erea_html();
	} elseif ($_GET['erea'] == "page") {
		$html = page_erea_html();
	} elseif ($_GET['erea'] == "end") {
		$html = end_html();
	} else {
		$html = frame_html();
	}

	return $html;
}

// --- add start hasegawa 2016/05/16 小学生低学年版対応 --- //

/**
 * フレーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function frame_html() {

	unset($_SESSION['CHECK_FLASH']);

	if ($_POST) {
		foreach ($_POST AS $key => $val) {
//			$val = mb_convert_kana($val,"as","UTF-8");		// del oda 2018/01/10 レクチャーの解答確認の時は変換しません。
			$_SESSION['CHECK_FLASH'][$key] = $val;
		}
	}

	$INPUTS[FLASHEREA] = array('result'=>'plane','value'=>$_SERVER[PHP_SELF]."?erea=lecture");
	$INPUTS[PAGEEREA]  = array('result'=>'plane','value'=>$_SERVER[PHP_SELF]."?erea=page");

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(PRACTICE_CHECK_FLASH_FRAME);
	$make_html->set_rep_cmd($INPUTS);
	$html = $make_html->replace();

	return $html;
}

/**
 * HTML5レクチャー表示領域
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function lecture_erea_html() {

//	$type = $_SESSION['CHECK_FLASH']['type'];			// del hasegawa 2016/05/18
	$course_num = $_SESSION['CHECK_FLASH']['course_num'];
	$stage_num = $_SESSION['CHECK_FLASH']['stage_num'];
	$lesson_num = $_SESSION['CHECK_FLASH']['lesson_num'];
	$unit_num = $_SESSION['CHECK_FLASH']['unit_num'];
//	$lesson_page = $_SESSION['CHECK_FLASH']['lesson_page'];		// del hasegawa 2016/05/18

	// html5レクチャーのパス
	$read_path = MATERIAL_FLASH_DIR.$course_num."/".$stage_num."/".$lesson_num."/".$unit_num."/index.html";

	//レクチャー起動時に、Flashに送信する内容
	$log_url = ADMIN_URL.PRACTICE_FLASHLOG_FILE;								// ログを送るためのＵＲＬ
	$next_url = BASE_URL.$_SERVER[PHP_SELF].urlencode("?erea=end");						// html5レクチャー終了時の遷移先URL
	$status = "true";											// 「未学習有り」か「学習完了」かを示す
	$current_page = $_SESSION['CHECK_FLASH']['current_page'];						// 表示ページ
	$lecture_path = "/material/flash/".$course_num."/".$stage_num."/".$lesson_num."/".$unit_num."/";	// html5レクチャー表示用のパス。
	$indicator_flag = "true";										// インジケータの表示/非表示
	$study_log_url = ADMIN_URL.PRACTICE_FLASH_STUDY_LOG_FILE;						// 学習ログを送る為のＵＲＬ

	if (file_exists($read_path) && $course_num == "4") {

		$html  = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">";
		$html  .= "<html lang=\"ja\">";
		$html  .= "<head>";
		$html  .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">";
		$html  .= "<meta http-equiv=\"Content-Style-Type\" content=\"text/css\">";
		$html  .= "<title>レクチャー動作確認</title>";
		$html  .= "</head>";
		$html  .= "<body onload=\"window.document.lecture_form.submit();\">";
		$html  .= "<form name=\"lecture_form\" action=\"".$read_path."\" method=\"POST\" name=\"form\">";
		$html  .= "<input type=\"hidden\" name=\"log_url\" value=\"".$log_url."\">";
		$html  .= "<input type=\"hidden\" name=\"next_sco_url\" value=\"".$next_url."\">";
		$html  .= "<input type=\"hidden\" name=\"status\" value=\"".$status."\">";
		$html  .= "<input type=\"hidden\" name=\"current_page\" value=\"".$current_page."\">";
		$html  .= "<input type=\"hidden\" name=\"url_path\" value=\"".$lecture_path."\">";
		$html  .= "<input type=\"hidden\" name=\"indicator_flag\" value=\"".$indicator_flag."\">";
		$html  .= "<input type=\"hidden\" name=\"study_log_url\" value=\"".$study_log_url."\">";
		$html  .= "</form>";
		$html  .= "</body>";
		$html  .= "</html>";

	} else {
		$html  = "<br>\n";
		$html .= "　htmlのレクチャー(計算マスター用)はアップロードされておりません。<br>\n";
		$html .= "<br>\n";
		$html .= "<form>　<input type=\"button\" value=\"閉じる\" OnClick=\"parent.window.close();\"></form>\n";
	}

	$INPUTS[FLASH]  = array('result'=>'plane','value'=>$html);

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(PRACTICE_TEMP_LESSON);
	$make_html->set_rep_cmd($INPUTS);
	$html = $make_html->replace();

	return $html;

}

/**
 * ページ情報表示領域
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function page_erea_html() {

	$html  = "<a name=\"top\"></a>\n";
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"GET\" >\n";
	$html .= "<input type=\"hidden\" name=\"erea\" value=\"page\">\n";
	$html .= "<input type=\"submit\" value=\"更新する\">\n";
	$html .= "</form>\n";

	if ($_SESSION['CHECK_FLASH']['log']) {
		$max = count($_SESSION['CHECK_FLASH']['log']) - 1;
		for ($i=$max; $i>=0; $i--) {
			$html .= $i." : ページ => ".$_SESSION['CHECK_FLASH']['log'][$i][display_problem_num];

			if ($_SESSION['CHECK_FLASH']['log'][$i][answer]) {
				$html .= " (まとめプリント)";
			}
			$html .= " [".$_SESSION['CHECK_FLASH']['log'][$i][regist_time]."]　　<a href=\"#top\">上へ</a>\n";

			// フラッシュ内問題数		// add start hasegawa 2016/05/13 小学生低学年版対応
			if($_SESSION['CHECK_FLASH']['log'][$i]['flash_problem_num_list']) {
				$problem_list = explode(',',$_SESSION['CHECK_FLASH']['log'][$i]['flash_problem_num_list']);
				$html .= "<br>　　問題数 => 全".count($problem_list)."問";
			}
			// フラッシュ解答時
			if($_SESSION['CHECK_FLASH']['log'][$i]['flash_problem_num']) {
				if($_SESSION['CHECK_FLASH']['log'][$i]['flash_success'] == 1 ){
					$judge = "正解";
				} else {
					$judge = "不正解";
				}
				$html .= "<br>　　解答情報 => 問".$_SESSION['CHECK_FLASH']['log'][$i]['flash_problem_num'];
				$html .= "　　【".$judge."】".$_SESSION['CHECK_FLASH']['log'][$i]['flash_answer'];
			}				// add end hasegawa 2016/05/13

			$html .= "<hr>\n";
		}

	} else {
		$html .= <<<EOT
ログが記録されておりません。<br>

EOT;
	}

	$INPUTS[FLASH]  = array('result'=>'plane','value'=>$html);

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(PRACTICE_TEMP_LESSON);
	$make_html->set_rep_cmd($INPUTS);
	$html = $make_html->replace();

	return $html;

}

// --- add end hasegawa 2016/05/16 小学生低学年版対応 --- //


// -- del start hasegawa 2016/05/16 小学生低学年版対応
/**
 * lecture表示
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @return string HTML
 */
/*
function lecture_html() {

	$html = array();

	// path 編集
	$read_path = MATERIAL_FLASH_DIR.$_POST['course_num']."/".$_POST['stage_num']."/".
										$_POST['lesson_num']."/".$_POST['unit_num']."/index.html";

	// htmlファイル読み込み
	if (file_exists($read_path)) {
		$html[0] = $read_path."?param=admin";

	} else {
		$html[1]  = "<br>\n";
		$html[1] .= "　htmlのレクチャーはアップロードされておりません。<br>\n";
		$html[1] .= "<br>\n";
		$html[1] .= "<form>　<input type=\"button\" value=\"閉じる\" OnClick=\"parent.window.close();\"></form>\n";
	}

	return $html;
}
*/ // -- del end hasegawa 2016/05/16

/**
 * lecture終了
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @return string HTML
 */
function end_html() {

	$html = array();

	$html_string  = "<br>\n";
	$html_string .= "　レクチャー終了<br>\n";
	$html_string .= "<br>\n";
	$html_string .= "<form>　<input type=\"button\" value=\"閉じる\" OnClick=\"parent.window.close();\"></form>\n";

	$INPUTS[FLASH]  = array('result'=>'plane','value'=>$html_string);

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(PRACTICE_TEMP_LESSON);
	$make_html->set_rep_cmd($INPUTS);
//	$html[1] = $make_html->replace();	// del hasegawa 2016/05/16 小学生低学年版対応
	$html = $make_html->replace();		// add hasegawa 2016/05/16 小学生低学年版対応

	return $html;
}

?>
