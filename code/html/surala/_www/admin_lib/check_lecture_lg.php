<?PHP
/**
 * すららネット
 *
 * レクチャーページ確認表示（小学生低学年用）
 *
 * @author Azet
 * e-learning system admin check_lecture_lg 2016/05/16 add hasegawa
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

	// 解答確認の場合
	if($_POST['mode'] == "check_answer" || $_GET['mode'] == "check_answer"){
		if ($_GET['erea'] == "check_answer_lecture") {
			$html = check_answer_lecture_erea_html();
		} elseif ($_GET['erea'] == "check_answer_page") {
			$html = check_answer_page_erea_html();
		} else {
			$html = check_answer_frame_html();
		}

	// 動作確認の場合
	} else {
		if ($_GET['erea'] == "lecture") {
			$html = lecture_erea_html();
		} elseif ($_GET['erea'] == "page") {
			$html = page_erea_html();
		} elseif ($_GET['erea'] == "end") {
			$html = end_html();
		} else {
			$html = frame_html();
		}
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

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	unset($_SESSION['CHECK_FLASH']);
	unset($_SESSION['CHECK_GAME']);		// add hasegawa 2016/11/08
	$INPUTS = array();


	if ($_POST) {
		foreach ($_POST AS $key => $val) {
//			$val = mb_convert_kana($val,"as","UTF-8");		// del oda 2018/01/10 レクチャーの解答確認の時は変換しません。
			$_SESSION['CHECK_FLASH'][$key] = $val;
		}
	}

	// 最新の解答ログのみを保持するため、開始時に過去の解答ログを削除します。
	$id = $_SESSION['myid']['id'];
	$course_num = $_SESSION['CHECK_FLASH']['course_num'];
	$stage_num = $_SESSION['CHECK_FLASH']['stage_num'];
	$lesson_num = $_SESSION['CHECK_FLASH']['lesson_num'];
	$unit_num = $_SESSION['CHECK_FLASH']['unit_num'];
	if( $_SESSION['myid']['id'] && $course_num > 0 && $unit_num > 0 ){
		$where = "";
		$where = " WHERE school_id = '".$id."' ";
		$where .= " AND class_m = '101010020' "; // レクチャー解答（レクチャーとゲームを判別する種に指定）
		$where .= " AND course_num = '".$course_num."' ";
		$where .= " AND unit_num = '".$unit_num."' ";
		$ERROR = $cdb->delete(T_STUDY_RECODE,$where);

		// add start hasegawa 2016/11/04
		// 書写ログの方も併せて削除する(log_type=1)
		if(!$ERROR) {
			$where = "";
			$where = " WHERE log_type = '1' ";
			$where .= " AND study_log_type = 'lecture_answer' ";
			$where .= " AND record_type = '0' ";			// add hasegawa 2016/12/08
			$where .= " AND student_id = '0' ";
			$where .= " AND school_id = '".$id."' ";
			$where .= " AND course_num = '".$course_num."' ";
			$where .= " AND stage_num = '".$stage_num."' ";
			$where .= " AND lesson_num = '".$lesson_num."' ";
			$where .= " AND unit_num = '".$unit_num."' ";
			$where .= " AND review = '0'; ";

			$ERROR = $cdb->delete(T_SYOSYA_LOG,$where);
		}

		// 1年以上前のレコードが存在したら、ついでに削除する(log_type=0)
		$where = "";
		$where .= " WHERE log_type = '0' ";
		$where .= " AND study_log_type IS NULL ";
		$where .= " AND record_type = '0' ";			// add hasegawa 2016/12/08
		$where .= " AND course_num = '".$course_num."' ";
		$where .= " AND stage_num = '".$stage_num."' ";
		$where .= " AND lesson_num = '".$lesson_num."' ";
		$where .= " AND unit_num = '".$unit_num."' ";
		$where .= " AND review = '0' ";
		$where .= " AND regist_time < (NOW() - INTERVAL 1 YEAR); ";

		$ERROR = $cdb->delete(T_SYOSYA_LOG,$where);
		// add end hasegawa 2016/11/04
	}

	$script   = "";
	$script  .= "<script language=\"JavaScript\">";
	$script  .= "var log_url=\"\";";
	$script  .= "var next_sco_url=\"\";";
	$script  .= "var status=\"\";";
	$script  .= "var current_page=\"\";";
	$script  .= "var disp_page=\"\";";
	$script  .= "var url_path=\"\";";
	$script  .= "var indicator_flag=\"\";";
	$script  .= "var study_log_url=\"\";";
	$script  .= "var tegaki_flg=\"\";";	// 手書きON・OFFパラメーター追加 // add hasegawa 2017/01/17 ログイン＆TOP画面変更
	$script  .= "</script>";

	$INPUTS['ADDSCRIPT'] = array('result'=>'plane','value'=>$script);
	$INPUTS['FLASHEREA'] = array('result'=>'plane','value'=>$_SERVER['PHP_SELF']."?erea=lecture");
	$INPUTS['PAGEEREA']  = array('result'=>'plane','value'=>$_SERVER['PHP_SELF']."?erea=page");

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

	$INPUTS = array();

	$type = $_SESSION['CHECK_FLASH']['type'];
	$course_num = $_SESSION['CHECK_FLASH']['course_num'];
	$stage_num = $_SESSION['CHECK_FLASH']['stage_num'];
	$lesson_num = $_SESSION['CHECK_FLASH']['lesson_num'];
	$unit_num = $_SESSION['CHECK_FLASH']['unit_num'];
	$lesson_page = $_SESSION['CHECK_FLASH']['lesson_page'];

	// html5レクチャーのパス
	$read_path = MATERIAL_FLASH_DIR.$course_num."/".$stage_num."/".$lesson_num."/".$unit_num."/".STUDENT_FLASH_MOVIE_FILE_HTML5;			// update oda 2018/02/22 定数化

	//レクチャー起動時に、Flashに送信する内容

	// add start oda 2020/12/03 adminのhttps対応
	$admin_server = ADMIN_URL;
	if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
		$admin_server = ADMIN_SSL_URL;
	}
	// add start oda 2020/12/03 adminのhttps対応

	// update start oda 2020/12/03 adminのhttps対応
// 	$log_url = ADMIN_URL.PRACTICE_FLASHLOG_FILE;														// ログを送るためのＵＲＬ
// 	$next_url = ADMIN_URL."check_lecture_lg.php?erea=end";												// html5レクチャー終了時の遷移先URL
	$log_url = $admin_server.PRACTICE_FLASHLOG_FILE;													// ログを送るためのＵＲＬ
	$next_url = $admin_server."check_lecture_lg.php?erea=end";											// html5レクチャー終了時の遷移先URL
	// update end oda 2020/12/03 adminのhttps対応

	$status = "false";																					// 「未学習有り」か「学習完了」かを示す
	$current_page = $_SESSION['CHECK_FLASH']['current_page'];											// 表示ページ
	$lecture_path = "/material/flash/".$course_num."/".$stage_num."/".$lesson_num."/".$unit_num."/";	// html5レクチャー表示用のパス。
	$indicator_flag = "false";																			// インジケータの表示/非表示
	// update start oda 2020/12/03 adminのhttps対応
//	$study_log_url = ADMIN_URL.PRACTICE_FLASH_STUDY_LOG_FILE;											// 学習ログを送る為のＵＲＬ
	$study_log_url = $admin_server.PRACTICE_FLASH_STUDY_LOG_FILE;										// 学習ログを送る為のＵＲＬ
	// update end oda 2020/12/03 adminのhttps対応

	$tegaki_flg = true;	// 手書きON・OFFパラメーター追加 // add hasegawa 2017/01/17 ログイン＆TOP画面変更

	//add start hirose 2018/04/26 管理画面手書き切り替え機能追加
	if(isset($_SESSION['TEGAKI_FLAG'])){
		$tegaki_flg = $_SESSION['TEGAKI_FLAG'];
	}
	//add end hirose 2018/04/26 管理画面手書き切り替え機能追加

	if (file_exists($read_path) && $course_num != "4") {

		if ($type == 2) {
			$status = "true";
			$indicator_flag = "true";

		} elseif ($type == 3) {
			$status = "true";
			$indicator_flag = "true";
			$disp_page = $lesson_page;
		}

		// del start 2020/10/26 yoshizawa 海外校舎の手書き制御を開放
		// // add start oda 2017/10/20 海外校舎の時は、手書きポップアップはONにしない
		// // if ($course_num != "1" && $course_num != "2" && $course_num != "3") {											// del 2018/06/19 yoshizawa
		// if ($course_num != "1"
		// && $course_num != "2"
		// && $course_num != "3"
		// && $course_num != "12"		// add 2019/12/03 suralaNinja算数開発
		// && $course_num != "15"
		// && $course_num != "16") {	// add 2018/06/19 yoshizawa
		// 	$tegaki_flg = false;
		// }
		// // add end oda 2017/10/20
		// del end 2020/10/26 yoshizawa 海外校舎の手書き制御を開放

		$html  = "<!DOCTYPE HTML>";
		$html  .= "<html>";
		$html  .= "<head>";
		$html  .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">";
		$html  .= "<meta http-equiv=\"Content-Style-Type\" content=\"text/css\">";
		$html  .= "<title>レクチャー(html5)動作確認</title>";
		$html  .= "<script language=\"JavaScript\">";
		$html  .= "if (parent) {";
		$html  .= "parent.log_url=\"".$log_url."\";";
		$html  .= "parent.next_sco_url=\"".$next_url."\";";
		$html  .= "parent.status=\"".$status."\";";
		$html  .= "parent.current_page=\"".$current_page."\";";
		$html  .= "parent.disp_page=\"".$disp_page."\";";
		$html  .= "parent.url_path=\"".$lecture_path."\";";
		$html  .= "parent.indicator_flag=\"".$indicator_flag."\";";
		$html  .= "parent.study_log_url=\"".$study_log_url."\";";
		$html  .= "parent.tegaki_flg=\"".$tegaki_flg."\";";	// 手書きON・OFFパラメーター追加 // add hasegawa 2017/01/17 ログイン＆TOP画面変更
		$html  .= "}";
		$html  .= "</script>";
		$html  .= "</head>";
		$html  .= "<body onload=\"window.document.lecture_form.submit();\">";
		$html  .= "<form name=\"lecture_form\" action=\"".$read_path."\" method=\"POST\" name=\"form\">";
		$html  .= "</form>";
		$html  .= "</body>";
		$html  .= "</html>";

	} else {
		$html  = "<br>\n";
		$html .= "　htmlのレクチャー(小学生低学年用)はアップロードされておりません。<br>\n";
		$html .= "<br>\n";
		$html .= "<form>　<input type=\"button\" value=\"閉じる\" OnClick=\"parent.window.close();\"></form>\n";
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
 * ページ情報表示領域
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function page_erea_html() {

	$INPUTS = array();

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
			// レクチャー内問題数
			if($_SESSION['CHECK_FLASH']['log'][$i]['flash_problem_num_list']) {
				$problem_list = explode(',',$_SESSION['CHECK_FLASH']['log'][$i]['flash_problem_num_list']);
				$html .= "<br>　　問題数 => 全".count($problem_list)."問";
			}
			// レクチャー解答時
			if($_SESSION['CHECK_FLASH']['log'][$i]['flash_problem_num']) {
				if($_SESSION['CHECK_FLASH']['log'][$i]['flash_success'] == 1 ){
					$judge = "正解";
				} else {
					$judge = "不正解";
				}

				//$flash_answer = $_SESSION['CHECK_FLASH']['log'][$i]['flash_answer'];	// add hasegawa 2016/12/06 小学生低学年版2次開発 // del oda 2018/01/12
				// update start 2018/02/15 yoshizawa
				// 書写の解答情報は配列のため、配列の場合はエンコード処理を回避する
				// $flash_answer = urldecode($_SESSION['CHECK_FLASH']['log'][$i]['flash_answer']);	// add oda 2018/01/12  urlエンコードはDB格納時に行う
				if(is_array($_SESSION['CHECK_FLASH']['log'][$i]['flash_answer'])){
					$flash_answer = $_SESSION['CHECK_FLASH']['log'][$i]['flash_answer'];
				} else {
					$flash_answer = urldecode($_SESSION['CHECK_FLASH']['log'][$i]['flash_answer']);	// urlエンコードはDB格納時に行う
				}
				// update end 2018/02/15 yoshizawa

				// update start 2017/02/10 yoshizawa 低学年2次対応
				// if(is_array($_SESSION['CHECK_FLASH']['log'][$i]['flash_answer'])) {	// add hasegawa 2016/12/06 小学生低学年版2次開発
				// 	$flash_answer = "書写解答";
				// }
				$answer_syosya_flg = false;
				if(is_array($_SESSION['CHECK_FLASH']['log'][$i]['flash_answer'][0])){
					if(array_key_exists('strokes',$_SESSION['CHECK_FLASH']['log'][$i]['flash_answer'][0])){ $answer_syosya_flg = true; }
				}
				if($answer_syosya_flg == true) {
					$flash_answer = "書写解答";
				} else if(is_array($flash_answer)) {
					$flash_answer = implode(",",$flash_answer);
				}
				// update end 2017/02/10 yoshizawa 低学年2次対応

				$html .= "<br>　　解答情報 => 問".$_SESSION['CHECK_FLASH']['log'][$i]['flash_problem_num'];
				$html .= "　　【".$judge."】".$flash_answer;
			}

			$html .= "<hr>\n";
		}

	} else {
		$html .= "ログが記録されておりません。<br>";
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
 * lecture終了
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @return string HTML
 */
function end_html() {

	$html = array();
	$INPUTS = array();

	$html_string  = "<br>\n";
	$html_string .= "　レクチャー終了<br>\n";
	$html_string .= "<br>\n";
	$html_string .= "<form>　<input type=\"button\" value=\"閉じる\" OnClick=\"parent.window.close();\"></form>\n";

	$INPUTS['FLASH']  = array('result'=>'plane','value'=>$html_string);

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(PRACTICE_TEMP_LESSON);
	$make_html->set_rep_cmd($INPUTS);
	$html = $make_html->replace();

	return $html;
}

// add start 2016/10/07 yoshizawa
/**
 * フレーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function check_answer_frame_html() {

	$INPUTS = array();

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	unset($_SESSION['CHECK_FLASH']);

	if ($_POST) {
		foreach ($_POST AS $key => $val) {
			// update start 2017/02/10 yoshizawa 低学年2次対応
			// // 書写のflash_answerは配列でPOSTされます。
			// if($key == "flash_answer") {						// add hasegawa 2016/11/14
			// 	$val = urldecode($val);
			// }
			//
			// 配列の解答（flash_answer）はjsonでPOSTされます。
			if($key == "flash_answer") {
				// 書写の場合 ： JSONで再現
				$val = urldecode($val);
				// 書写以外の場合 ： javascript配列で再現
				if(!preg_match("/strokes/",$val)){
					if(is_array(json_decode($val))){
 						$val_array = json_decode($val);
						$val_temp = "";
						foreach ($val_array as $val2) {
							if($val_temp){ $val_temp .= ","; }
							$val_temp .= $val2;
						}
						$val = "[{$val_temp}]"; // javascript配列にする。
					}
				}
			}
			// update end 2017/02/10 yoshizawa 低学年2次対応

//			if(!is_array($val)){ $val = mb_convert_kana($val,"as","UTF-8"); }		// del oda 2018/01/10 レクチャーの解答確認の時は変換しません。
			$_SESSION['CHECK_FLASH'][$key] = $val;
		}
	}

	$script   = "";
	$script  .= "<script language=\"JavaScript\">";
	$script  .= "var flash_problem_num='".$_SESSION['CHECK_FLASH']['flash_problem_num']."';";
	$script  .= "var flash_answer='".$_SESSION['CHECK_FLASH']['flash_answer']."';";
	$script  .= "var flash_success='".$_SESSION['CHECK_FLASH']['flash_success']."';";
	$script  .= "var flash_count='".$_SESSION['CHECK_FLASH']['flash_count']."';";
	$script  .= "var type='".$_SESSION['CHECK_FLASH']['type']."';";
	$script  .= "</script>";
	$INPUTS['ADDSCRIPT'] = array('result'=>'plane','value'=>$script);
	$INPUTS['FLASHEREA'] = array('result'=>'plane','value'=>$_SERVER['PHP_SELF']."?mode=check_answer&erea=check_answer_lecture");
	$INPUTS['PAGEEREA']  = array('result'=>'plane','value'=>$_SERVER['PHP_SELF']."?mode=check_answer&erea=check_answer_page");

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
function check_answer_lecture_erea_html() {

	$INPUTS = array();

	// レクチャー起動時に、Flashに送信する内容
	$course_num = $_SESSION['CHECK_FLASH']['course_num'];
	$stage_num = $_SESSION['CHECK_FLASH']['stage_num'];
	$lesson_num = $_SESSION['CHECK_FLASH']['lesson_num'];
	$unit_num = $_SESSION['CHECK_FLASH']['unit_num'];
	// 解答再現時に必要なパラメーター
	$flash_problem_num = $_SESSION['CHECK_FLASH']['flash_problem_num'];
	$flash_answer = $_SESSION['CHECK_FLASH']['flash_answer'];
	$flash_success = $_SESSION['CHECK_FLASH']['flash_success'];
	$flash_count = $_SESSION['CHECK_FLASH']['flash_count'];
	$type = $_SESSION['CHECK_FLASH']['type'];

	//$set_flash_answer_script = "";
	/*	del hasegawa 2016/11/15
	if(is_array($flash_answer)){
		// 書写問題の場合
		$flash_answer_json = json_encode($flash_answer);
		$set_flash_answer_script = "parent.flash_answer = '{$flash_answer_json}';";
	} else {
		// 選択問題の場合
		$set_flash_answer_script  .= "parent.flash_answer=\"".$flash_answer."\";";
	}
	*/
	// html5レクチャーのパス
	$read_path = MATERIAL_FLASH_DIR.$course_num."/".$stage_num."/".$lesson_num."/".$unit_num."/".STUDENT_FLASH_MOVIE_FILE_HTML5;		// update oda 2018/02/22 定数化

	if (file_exists($read_path) && $course_num != "4") {

		$html  = "<!DOCTYPE HTML>";
		$html  .= "<html>";
		$html  .= "<head>";
		$html  .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">";
		$html  .= "<meta http-equiv=\"Content-Style-Type\" content=\"text/css\">";
		$html  .= "<title>レクチャー(html5)動作確認</title>";
		$html  .= "<script language=\"JavaScript\">";
		$html  .= "if (parent) {";
		$html  .= "parent.flash_problem_num='".$flash_problem_num."';";
		// $html  .= $set_flash_answer_script;						// del hasegawa 2016/11/15
		$html  .= "parent.flash_answer='".$flash_answer."';";				// add hasegawa 2016/11/15
		$html  .= "parent.flash_success='".$flash_success."';";
		$html  .= "parent.flash_count='".$flash_count."';";
		$html  .= "parent.type='".$type."';";
		$html  .= "}";
		$html  .= "</script>";
		$html  .= "</head>";
		$html  .= "<body onload=\"window.document.lecture_form.submit();\">";
		$html  .= "<form name=\"lecture_form\" action=\"".$read_path."\" method=\"POST\" name=\"form\">";
		$html  .= "</form>";
		$html  .= "</body>";
		$html  .= "</html>";

	} else {
		$html  = "<br>\n";
		$html .= "　htmlのレクチャー(小学生低学年用)はアップロードされておりません。<br>\n";
		$html .= "<br>\n";
		$html .= "<form>　<input type=\"button\" value=\"閉じる\" OnClick=\"parent.window.close();\"></form>\n";
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
 * ページ情報表示領域(解答確認用)
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function check_answer_page_erea_html() {

	$INPUTS = array();

	$course_num = $_SESSION['CHECK_FLASH']['course_num'];
	$stage_num = $_SESSION['CHECK_FLASH']['stage_num'];
	$lesson_num = $_SESSION['CHECK_FLASH']['lesson_num'];
	$unit_num = $_SESSION['CHECK_FLASH']['unit_num'];

	$html .= "<script>";
	$html .= "function back_answer_list(){";
	$html .= "	var form = document.createElement('form');";
	$html .= "	var input1 = document.createElement('input');";
	$html .= "	var input2 = document.createElement('input');";
	$html .= "	var input3 = document.createElement('input');";
	$html .= "	var input4 = document.createElement('input');";
	$html .= "	form.action = \"/admin/lecture_answer_list.php\";";
	$html .= "	form.method = 'post';";
	$html .= "	input1.name = \"course_num\";";
	$html .= "	input1.value = ".$course_num.";";
	$html .= "	input2.name = \"stage_num\";";
	$html .= "	input2.value = ".$stage_num.";";
	$html .= "	input3.name = \"lesson_num\";";
	$html .= "	input3.value = ".$lesson_num.";";
	$html .= "	input4.name = \"unit_num\";";
	$html .= "	input4.value = ".$unit_num.";";
	$html .= "	form.appendChild(input1);";
	$html .= "	form.appendChild(input2);";
	$html .= "	form.appendChild(input3);";
	$html .= "	form.appendChild(input4);";
	$html .= "	window.top.document.body.appendChild(form);";
	$html .= "	form.submit();";
	$html .= "}";
	$html .= "</script>";
	$html .= "<input type=\"button\" onClick=\"back_answer_list();\" value=\"解答一覧に戻る\">\n";

	$INPUTS['FLASH']  = array('result'=>'plane','value'=>$html);

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(PRACTICE_TEMP_LESSON);
	$make_html->set_rep_cmd($INPUTS);
	$html = $make_html->replace();

	return $html;

}
// add end 2016/10/07 yoshizawa
?>