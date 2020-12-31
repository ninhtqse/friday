<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * 診断復習レッスンページ確認表示
 *
 * @author Azet
 */

/**
 * GETパラメター判断して、HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function start() {

	if ($_GET['erea'] == "flash") {
		$html = flash_erea_html();
	} elseif ($_GET['erea'] == "page") {
		$html = page_erea_html();
	} elseif ($_GET['erea'] == "end") {
		$html = end_html();
	} else {
		$html = frame_html();
	}

	return $html;
}


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
	unset($_SESSION['CHECK_GAME']);		// add hasegawa 2016/11/04

	if ($_POST) {
		foreach ($_POST AS $key => $val) {
			$val = mb_convert_kana($val,"as","UTF-8");
			$_SESSION['CHECK_FLASH'][$key] = $val;
		}
	}

	$INPUTS['FLASHEREA'] = array('result'=>'plane','value'=>$_SERVER['PHP_SELF']."?erea=flash");
	$INPUTS['PAGEEREA']  = array('result'=>'plane','value'=>$_SERVER['PHP_SELF']."?erea=page");

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(PRACTICE_CHECK_FLASH_FRAME);
	$make_html->set_rep_cmd($INPUTS);
	$html = $make_html->replace();

	return $html;
}


/**
 * FLASH表示領域
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function flash_erea_html() {

	$type = $_SESSION['CHECK_FLASH']['type'];
	$course_num = $_SESSION['CHECK_FLASH']['course_num'];
	$stage_num = $_SESSION['CHECK_FLASH']['stage_num'];
	$lesson_num = $_SESSION['CHECK_FLASH']['lesson_num'];
	$unit_num = $_SESSION['CHECK_FLASH']['unit_num'];
	$lesson_page = $_SESSION['CHECK_FLASH']['lesson_page'];
	$current_page = $_SESSION['CHECK_FLASH']['current_page'];

	$log_url = ADMIN_URL.PRACTICE_FLASHLOG_FILE;
	$study_log_url = ADMIN_URL.PRACTICE_FLASH_STUDY_LOG_FILE;	// add hasegawa 2016/05/13 小学生低学年版対応
	$next_url = BASE_URL.$_SERVER[PHP_SELF].urlencode("?erea=end");
	$status = "false";
	$flash_file = STUDENT_FLASH_MOVIE_FILE;
	$indicator_flag = "false";

	if ($type == 1) {
		$flash_path = MATERIAL_FLASH_DIR.$course_num."/".$stage_num."/".$lesson_num."/".$unit_num."/".$flash_file;
		$send_para = "log_url=".$log_url."&status=".$status.
					"&current_page=".$current_page.
					"&url_path="."/material/flash/".$course_num."/".$stage_num."/".$lesson_num."/".$unit_num."/".
					"&next_sco_url=".$next_url.
					"&indicator_flag=".$indicator_flag.
					"&study_log_url=".$study_log_url;	// add hasegawa 2016/05/13 小学生低学年版対応
	} elseif ($type == 2) {
		$status = "true";
		$indicator_flag = "true";
		$flash_path = MATERIAL_FLASH_DIR.$course_num."/".$stage_num."/".$lesson_num."/".$unit_num."/".$flash_file;
		$send_para = "log_url=".$log_url.
					"&status=".$status.
					"&current_page=".$current_page.
					"&url_path="."/material/flash/".$course_num."/".$stage_num."/".$lesson_num."/".$unit_num."/".
					"&next_sco_url=".$next_url.
					"&indicator_flag=".$indicator_flag.
					"&study_log_url=".$study_log_url;	// add hasegawa 2016/05/13 小学生低学年版対応
	} elseif ($type == 3) {
		$status = "true";
		$indicator_flag = "true";
		$flash_file = STUDENT_REVIEW_FLASH_MOVIE_FILE;
		$add_send_para = "&disp_page=".$lesson_page;
		$flash_path = MATERIAL_FLASH_DIR.$course_num."/".$stage_num."/".$lesson_num."/".$unit_num."/".$flash_file;
		$send_para = "log_url=".$log_url.
					"&status=".$status.
					"&current_page=".$current_page.
					"&disp_page=".$lesson_page.
					"&url_path="."/material/flash/".$course_num."/".$stage_num."/".$lesson_num."/".$unit_num."/".
					"&next_sco_url=".$next_url.
					"&indicator_flag=".$indicator_flag.
					"&study_log_url=".$study_log_url;	// add hasegawa 2016/05/13 小学生低学年版対応

	} elseif ($type == 4) {
		$status = "true";
		$indicator_flag = "true";
		$flash_path = MATERIAL_FLASH_DIR.$course_num."/".$stage_num."/".$lesson_num."/".$unit_num."/".$flash_file;
		$send_para = "log_url=".$log_url.
					"&status=".$status.
					"&current_page=".$current_page.
					"&url_path="."/material/flash/".$course_num."/".$stage_num."/".$lesson_num."/".$unit_num."/".
					"&next_sco_url=".$next_url.
					"&indicator_flag=".$indicator_flag.
					"&study_log_url=".$study_log_url;	// add hasegawa 2016/05/13 小学生低学年版対応
	}

	// update start 2017/01/20 yoshizawa 低学年2次対応
// 	$html .=<<<EOT
// <script src="/javascript/readFlash.js"></script>
// <script language="javascript">
// readFlash('$flash_path','$send_para');
// </script>
//
// EOT;
//

	// main.swfとmain.htmlが混在する場合があるので先にFLASHファイルの存在をチェックしています。
	if(file_exists($flash_path)){
		$html .= "<script src=\"/javascript/readFlash.js\"></script>";
		$html .= "<script language=\"javascript\">";
		$html .= "readFlash('{$flash_path}','{$send_para}');";
		$html .= "</script>";
	} else {
		$flash_path = preg_replace("/".$flash_file."/",STUDENT_FLASH_MOVIE_FILE_HTML5,$flash_path);
		// html5レクチャーが存在する場合、htmlレクチャーを起動する
		if (file_exists($flash_path)) {
			$url_path = "/material/flash/".$course_num."/".$stage_num."/".$lesson_num."/".$unit_num."/";
			$html = lecture_html5_review($flash_path,$log_url,$next_url,$status,$url_path,$indicator_flag,$lesson_page,$current_page);
		}
	}
	// update end 2017/01/20 yoshizawa 低学年2次対応

	$INPUTS['FLASH']  = array('result'=>'plane','value'=>$html);

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(PRACTICE_TEMP_LESSON);
	$make_html->set_rep_cmd($INPUTS);
	$html = $make_html->replace();

	return $html;
}


// add start 2017/01/20 yoshizawa 低学年2次対応
/**
 * HTML5レクチャー起動HTML作成
 *
 * AC:[S]生徒 UC1:[L06]勉強をする UC2:[1]すららスタンダードコース.
 *
 * @author azet
 * @param number $flash_path 起動URL
 * @param boolean $log_url ログを送るためのＵＲＬ
 * @param string $next_url ゲームまたはドリルへ進む場合のＵＲＬ
 * @param number $status 「未学習有り」か「学習完了」かを示す
 * @param boolean $url_path 表示ページ
 * @param string $indicator_flag インジケータの表示/非表示
 * @return string htmlデータ
 */
function lecture_html5_review($flash_path,$log_url,$next_url,$status,$url_path,$indicator_flag,$lesson_page,$page) {

	$cdb = $GLOBALS['cdb'];

	$study_log_url = ADMIN_URL.PRACTICE_FLASH_STUDY_LOG_FILE; // 学習ログを送る為のＵＲＬ
	if($next_url){ $next_url = urldecode($next_url); }

	$html = "";
	$html  .= "<form name=\"lecture_form\" action=\"".$flash_path."\" method=\"POST\" name=\"form\">\n";
	$html  .= "</form>\n";
	$html .= "<script type=\"text/javascript\">\n";
	$html .= "if (parent) {\n";
	$html .= "	parent.log_url=\"".$log_url."\";\n";
	$html .= "	parent.next_sco_url=\"".$next_url."\";\n";
	$html .= "	parent.status=\"".$status."\";\n";
	$html .= "	parent.url_path=\"".$url_path."\";\n";
	$html .= "	parent.indicator_flag=\"".$indicator_flag."\";\n";
	$html .= "	parent.study_log_url=\"".$study_log_url."\";\n";
	$html .= "	parent.disp_page=\"".$lesson_page."\";\n";
	$html .= "	parent.current_page=\"".$page."\";\n";
	// $html .= "	parent.tegaki_flg=\"".$tegaki_flg."\";\n";
	$html .= "	document.lecture_form.submit();\n";
	$html .= "}\n";
	$html .= "</script>\n";
	$html .= "<script type=\"text/javascript\">\n";
	$html .= "parent.parent.foot.print_download_switch(true);\n";
	$html .= "</script>\n";

	return $html;
}
// add end 2017/01/20 yoshizawa 低学年2次対応

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
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"GET\" >\n";
	$html .= "<input type=\"hidden\" name=\"erea\" value=\"page\">\n";
	$html .= "<input type=\"submit\" value=\"更新する\">\n";
	$html .= "</form>\n";

	if ($_SESSION['CHECK_FLASH']['log']) {
		$max = count($_SESSION['CHECK_FLASH']['log']) - 1;
		for ($i=$max; $i>=0; $i--) {
			$html .= $i." : ページ => ".$_SESSION['CHECK_FLASH']['log'][$i]['display_problem_num'];

			if ($_SESSION['CHECK_FLASH']['log'][$i]['answer']) {
				$html .= " (まとめプリント)";
			}
			$html .= " [".$_SESSION['CHECK_FLASH']['log'][$i]['regist_time']."]　　<a href=\"#top\">上へ</a>\n";

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

	$INPUTS['FLASH']  = array('result'=>'plane','value'=>$html);

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(PRACTICE_TEMP_LESSON);
	$make_html->set_rep_cmd($INPUTS);
	$html = $make_html->replace();

	return $html;
}


/**
 * FLASH終了
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function end_html() {

	$html  = "<br>\n";
	$html .= "　FLASH終了<br>\n";
	$html .= "<br>\n";
	$html .= "<form>　<input type=\"button\" value=\"閉じる\" OnClick=\"parent.window.close();\"></form>\n";

	$INPUTS['FLASH']  = array('result'=>'plane','value'=>$html);

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(PRACTICE_TEMP_LESSON);
	$make_html->set_rep_cmd($INPUTS);
	$html = $make_html->replace();

	return $html;
}
?>