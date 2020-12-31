<?PHP
/**
 * すらら ゲーム確認システム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * e-learning system admin check_game 2016/05/16 add hasegawa
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

	// 解答確認の場合
	if($_POST['mode'] == "check_answer" || $_GET['mode'] == "check_answer"){
		if ($_GET['erea'] == "check_answer_game") {
			$html = check_answer_game_erea_html();
		} elseif ($_GET['erea'] == "check_answer_page") {
			$html = check_answer_page_erea_html();
		} else {
			$html = check_answer_frame_html();
		}

	// 動作確認の場合
	} else {
		if ($_GET['erea'] == "game" || $_GET['action'] == "game_restart") {
			$html = game_erea_html();
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

	if ($_POST) {
		unset($_SESSION['CHECK_GAME']);
		unset($_SESSION['CHECK_FLASH']);	// add hasegawa 2016/11/04
		foreach ($_POST AS $key => $val) {
//			$val = mb_convert_kana($val,"as","UTF-8");		// del oda 2018/01/10 ゲームの解答確認の時は変換しません。
			$_SESSION['CHECK_GAME'][$key] = $val;
		}
	}

	// 最新の解答ログのみを保持するため、開始時に過去の解答ログを削除します。
	$id = $_SESSION['myid']['id'];
	$course_num = $_SESSION['CHECK_GAME']['course_num'];
	$stage_num = $_SESSION['CHECK_GAME']['stage_num'];
	$lesson_num = $_SESSION['CHECK_GAME']['lesson_num'];
	$unit_num = $_SESSION['CHECK_GAME']['unit_num'];
	if( $_SESSION['myid']['id'] && $course_num > 0 && $unit_num > 0 ){
		$where = "";
		$where = " WHERE school_id = '".$id."' ";
		$where .= " AND class_m = '101030020' "; // ゲーム解答（レクチャーとゲームを判別する種に指定）
		$where .= " AND course_num = '".$course_num."' ";
		$where .= " AND unit_num = '".$unit_num."' ";
		$ERROR = $cdb->delete(T_STUDY_RECODE,$where);

		// add start hasegawa 2016/11/04
		// 書写ログの方も併せて削除する(log_type=1)
		if(!$ERROR) {
			$where = "";
			$where = " WHERE log_type = '1' ";
			$where .= " AND study_log_type = 'game_answer' ";
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
	$script  .= "var again_sco_url=\"\";";
	$script  .= "var tegaki_flg=\"\";";			// 手書きパラメータ追加 // add hasegawa 2017/01/17 ログイン＆TOP画面変更
	$script  .= "var display_skip_flag=\"\";";	// add yoshizawa 2017/01/26 スキップフラグ追加
	$script  .= "</script>";

	$INPUTS['ADDSCRIPT'] = array('result'=>'plane','value'=>$script);
	$INPUTS['FLASHEREA'] = array('result'=>'plane','value'=>$_SERVER[PHP_SELF]."?erea=game");
	$INPUTS['PAGEEREA']  = array('result'=>'plane','value'=>$_SERVER[PHP_SELF]."?erea=page");

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(PRACTICE_CHECK_FLASH_FRAME);
	$make_html->set_rep_cmd($INPUTS);
	$html = $make_html->replace();

	return $html;
}


/**
 * ゲーム表示領域
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function game_erea_html() {

	if($_GET['action'] == "game_restart") {	unset($_SESSION['CHECK_GAME']['log']); } // ゲーム再スタート時にはログ情報のみ削除

	$course_num = $_SESSION['CHECK_GAME']['course_num'];
	$stage_num = $_SESSION['CHECK_GAME']['stage_num'];
	$lesson_num = $_SESSION['CHECK_GAME']['lesson_num'];
	$unit_num = $_SESSION['CHECK_GAME']['unit_num'];

	// ゲームのパス
	$read_path = MATERIAL_FLASH_DIR.$course_num."/".$stage_num."/".$lesson_num."/".$unit_num."/game/".STUDENT_GAME_FILE;

	// ゲーム起動時にhtml5ゲームに送信するパラメータ
	$log_url = ADMIN_URL.PRACTICE_GAMELOG_FILE;																			// ログを送るためのＵＲＬ
	$next_url = ADMIN_URL."check_game.php?erea=end";																	// ゲーム終了時の遷移先URL
	$status = "true";																									//「クリア済」か「未クリア」かを示す
	$game_path = "/material/flash/".$course_num."/".$stage_num."/".$lesson_num."/".$unit_num."/game/".STUDENT_GAME_FILE;// html5表示用のパス(ファイル名まで)
	$lecture_path = "/material/flash/".$course_num."/".$stage_num."/".$lesson_num."/".$unit_num."/game/";				// html5ゲーム表示用のパス。
	$study_log_url = ADMIN_URL.PRACTICE_GAME_STUDY_LOG_FILE;															// 学習ログを送る為のＵＲＬ
	$again_sco_url = ADMIN_URL."check_game.php?action=game_restart";													// もういちどゲームを行う場合のＵＲＬ // upd hasegawa 2016/12/06 パラメータ追加 小学生低学年版2次開発
	$tegaki_flg = true;																									// 手書きパラメータ追加 // add hasegawa 2017/01/17 ログイン＆TOP画面変更

	// add start yoshizawa 2018/06/19 管理画面手書き切り替え機能追加
	if(isset($_SESSION['TEGAKI_FLAG'])){
		$tegaki_flg = $_SESSION['TEGAKI_FLAG'];
	}
	// add end hirose 2018/06/19 管理画面手書き切り替え機能追加

	// add yoshizawa 2017/01/26 スキップフラグ追加
	$display_skip_flag = false;
	if($_GET['action'] == "game_restart") { $display_skip_flag = true; }
	// add yoshizawa 2017/01/26

	if (file_exists($read_path)) {

		// add start oda 2017/10/20 海外校舎の時は、手書きポップアップはONにしない
		// if ($course_num != "1" && $course_num != "2" && $course_num != "3") {											// del 2018/06/19 yoshizawa
		if ($course_num != "1" 
		&& $course_num != "2" 
		&& $course_num != "3" 
		&& $course_num != "12"		// add 2019/12/03 suralaNinja算数開発
		&& $course_num != "15" 
		&& $course_num != "16") {	// add 2018/06/19 yoshizawa
			$tegaki_flg = false;
		}
		// add end oda 2017/10/20

		$html  = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">";
		$html  .= "<html lang=\"ja\">";
		$html  .= "<head>";
		$html  .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">";
		$html  .= "<meta http-equiv=\"Content-Style-Type\" content=\"text/css\">";
		$html  .= "<title>ゲーム動作確認</title>";
		$html  .= "<script language=\"JavaScript\">";
		$html  .= "if (parent) {";
		$html  .= "	parent.log_url=\"".$log_url."\";";
		$html  .= "	parent.next_sco_url=\"".$next_url."\";";
		$html  .= "	parent.status=\"".$status."\";";
		$html  .= "	parent.current_page=\"".$current_page."\";";
		$html  .= "	parent.disp_page=\"".$disp_page."\";";
		$html  .= "	parent.url_path=\"".$lecture_path."\";";
		$html  .= "	parent.indicator_flag=\"".$indicator_flag."\";";
		$html  .= "	parent.study_log_url=\"".$study_log_url."\";";
		$html  .= "	parent.again_sco_url=\"".$again_sco_url."\";";			// add hasegawa 2016/11/17 小学生低学年版2次開発
		$html  .= "	parent.tegaki_flg=\"".$tegaki_flg."\";";				// 手書きパラメータ追加 // add hasegawa 2017/01/17 ログイン＆TOP画面変更
		$html  .= "	parent.display_skip_flag=\"".$display_skip_flag."\";";	// add yoshizawa 2017/01/26 スキップフラグ追加
		$html  .= "}";
		$html  .= "</script>";
		$html  .= "</head>";
		$html  .= "<body onload=\"window.document.game_form.submit();\">";
		$html  .= "<form name=\"game_form\" action=\"".$read_path."\" method=\"POST\" name=\"form\">";
		$html  .= "<input type=\"hidden\" name=\"log_url\" value=\"".$log_url."\">";
		$html  .= "<input type=\"hidden\" name=\"next_sco_url\" value=\"".$next_url."\">";
		$html  .= "<input type=\"hidden\" name=\"status\" value=\"".$status."\">";
		$html  .= "<input type=\"hidden\" name=\"url_path\" value=\"".$game_path."\">";
		$html  .= "<input type=\"hidden\" name=\"study_log_url\" value=\"".$study_log_url."\">";
		$html  .= "<input type=\"hidden\" name=\"again_sco_url\" value=\"".$again_sco_url."\">";			// add hasegawa 2016/11/17 小学生低学年版2次開発
		$html  .= "<input type=\"hidden\" name=\"tegaki_flg\" value=\"".$tegaki_flg."\">";					// 手書きパラメータ追加 // add hasegawa 2017/01/17 ログイン＆TOP画面変更
		$html  .= "<input type=\"hidden\" name=\"display_skip_flag\" value=\"".$display_skip_flag."\">";	// add yoshizawa 2017/01/26 スキップフラグ追加
		$html  .= "</form>";
		$html  .= "</body>";
		$html  .= "</html>";

	} else {
		$html  = "<br>\n";
		$html .= "　ゲームはアップロードされておりません。<br>\n";
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

	if ($_SESSION['CHECK_GAME']['log']) {
		$max = count($_SESSION['CHECK_GAME']['log']) - 1;
		for ($i=$max; $i>=0; $i--) {
			// ページ情報 OR クリア情報
			if($_SESSION['CHECK_GAME']['log'][$i][display_problem_num] =="clear") {
				$html .= $i." : クリア情報 => ".$_SESSION['CHECK_GAME']['log'][$i]['game_clear'];
			} else {
				$html .= $i." : ページ => ".$_SESSION['CHECK_GAME']['log'][$i][display_problem_num];
			}

			$html .= " [".$_SESSION['CHECK_GAME']['log'][$i][regist_time]."]　　<a href=\"#top\">上へ</a>\n";

			// ゲーム内問題数
			if($_SESSION['CHECK_GAME']['log'][$i]['game_problem_num_list']) {
				$problem_list = explode(',',$_SESSION['CHECK_GAME']['log'][$i]['game_problem_num_list']);
				$html .= "<br>　　問題数 => 全".count($problem_list)."問";
			}
			// ゲーム解答時
			if($_SESSION['CHECK_GAME']['log'][$i]['game_problem_num']) {
				if($_SESSION['CHECK_GAME']['log'][$i]['game_success'] == 1 ){
					$judge = "正解";
				} else {
					$judge = "不正解";
				}

				//$game_answer = $_SESSION['CHECK_GAME']['log'][$i]['game_answer'];	// add hasegawa 2016/12/06 小学生低学年版2次開発	// del oda 2018/01/15
				// update start 2018/02/15 yoshizawa
				// 書写の解答情報は配列のため、配列の場合はエンコード処理を回避する
				// $game_answer = urldecode($_SESSION['CHECK_GAME']['log'][$i]['game_answer']);											// add oda 2018/01/15  urlエンコードはDB格納時に行う
				if(is_array($_SESSION['CHECK_GAME']['log'][$i]['game_answer'])){
					$game_answer = $_SESSION['CHECK_GAME']['log'][$i]['game_answer'];
				} else {
					$game_answer = urldecode($_SESSION['CHECK_GAME']['log'][$i]['game_answer']);	// urlエンコードはDB格納時に行う
				}
				// update end 2018/02/15 yoshizawa

				// update start 2017/02/13 yoshizawa 低学年2次対応
				// if(is_array($_SESSION['CHECK_GAME']['log'][$i]['game_answer'])) {	// add hasegawa 2016/12/06 小学生低学年版2次開発
				// 	$game_answer = "書写解答";
				// }
				$answer_syosya_flg = false;
				if(is_array($_SESSION['CHECK_GAME']['log'][$i]['game_answer'][0])){
					if(array_key_exists('strokes',$_SESSION['CHECK_GAME']['log'][$i]['game_answer'][0])){ $answer_syosya_flg = true; }
				}
				if($answer_syosya_flg == true) {
					$game_answer = "書写解答";
				} else if(is_array($game_answer)) {
					$game_answer = implode(",",$game_answer);
				}
				// update end 2017/02/13 yoshizawa 低学年2次対応
				$html .= "<br>　　解答情報 => 問".$_SESSION['CHECK_GAME']['log'][$i]['game_problem_num'];
				$html .= "　　【".$judge."】".$game_answer;
			}
			$html .= "<hr>\n";
		}

	} else {
		$html .= "ログが記録されておりません。<br>";
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
 * ゲーム終了
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function end_html() {

	$html  = "<br>\n";
	$html .= "　ゲーム終了<br>\n";
	$html .= "<br>\n";
	$html .= "<form>　<input type=\"button\" value=\"閉じる\" OnClick=\"parent.window.close();\"></form>\n";

	$INPUTS[FLASH]  = array('result'=>'plane','value'=>$html);

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(PRACTICE_TEMP_LESSON);
	$make_html->set_rep_cmd($INPUTS);
	$html = $make_html->replace();

	return $html;
}

// add start 2016/10/11 yoshizawa
/**
 * フレーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function check_answer_frame_html() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	unset($_SESSION['CHECK_GAME']);

	if ($_POST) {
		foreach ($_POST AS $key => $val) {
			// update start 2017/02/10 yoshizawa 低学年2次対応
			// 書写のgame_answerはurlencodeされたjsonでPOSTされます。
			// if($key == "game_answer") {
			// 	$val = urldecode($val);
			// }
			// 配列の解答（flash_answer）はjsonでPOSTされます。
			if($key == "game_answer") {
				// 書写の場合 ： JSONで再現
				$val = urldecode($val);
				// 書写以外の場合 ： javascript配列で再現
				if(!preg_match("/strokes/",$val)){
					if(is_array(json_decode($val))){
 						$val_array = json_decode($val);
						$val_temp = "";
						foreach ($val_array as $key2 => $val2) {
							if($val_temp){ $val_temp .= ","; }
							$val_temp .= $val2;
						}
						$val = "[{$val_temp}]"; // javascript配列にする。
					}
				}
			}
			// update end 2017/02/10 yoshizawa 低学年2次対応
			//			if(!is_array($val)){ $val = mb_convert_kana($val,"as","UTF-8"); }	// add hasegawa 2016/11/14		// del oda 2018/01/10 ゲームの解答確認の時は変換しません。
			//			$val = mb_convert_kana($val,"as","UTF-8");															// del oda 2018/01/10 ゲームの解答確認の時は変換しません。
			$_SESSION['CHECK_GAME'][$key] = $val;
		}
	}

	$script   = "";
	$script  .= "<script language=\"JavaScript\">";
	$script  .= "var game_problem_num='".$_SESSION['CHECK_GAME']['game_problem_num']."';";
	$script  .= "var game_answer='".$_SESSION['CHECK_GAME']['game_answer']."';";
	$script  .= "var game_success='".$_SESSION['CHECK_GAME']['game_success']."';";
	$script  .= "var game_count='".$_SESSION['CHECK_GAME']['game_count']."';";
	$script  .= "var type='".$_SESSION['CHECK_GAME']['type']."';";
	$script  .= "</script>";

	$INPUTS['ADDSCRIPT'] = array('result'=>'plane','value'=>$script);
	$INPUTS['FLASHEREA'] = array('result'=>'plane','value'=>$_SERVER[PHP_SELF]."?mode=check_answer&erea=check_answer_game");
	$INPUTS['PAGEEREA']  = array('result'=>'plane','value'=>$_SERVER[PHP_SELF]."?mode=check_answer&erea=check_answer_page");

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(PRACTICE_CHECK_FLASH_FRAME);
	$make_html->set_rep_cmd($INPUTS);
	$html = $make_html->replace();

	return $html;
}

/**
 * ゲーム表示領域
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function check_answer_game_erea_html() {

	// ゲーム起動時に、Flashに送信する内容
	$course_num = $_SESSION['CHECK_GAME']['course_num'];
	$stage_num = $_SESSION['CHECK_GAME']['stage_num'];
	$lesson_num = $_SESSION['CHECK_GAME']['lesson_num'];
	$unit_num = $_SESSION['CHECK_GAME']['unit_num'];
	// 解答再現時に必要なパラメーター
	$game_problem_num = $_SESSION['CHECK_GAME']['game_problem_num'];
	$game_answer = $_SESSION['CHECK_GAME']['game_answer'];
	$game_success = $_SESSION['CHECK_GAME']['game_success'];
	$game_count = $_SESSION['CHECK_GAME']['game_count'];
	$type = $_SESSION['CHECK_GAME']['type'];

	// del start hasegawa 2016/11/15
	// $set_game_answer_script  .= "";
	// if(is_array($game_answer)){
	// 	// 書写問題の場合
	// 	$game_answer_json = json_encode($flash_answer);
	// 	$set_game_answer_script = "parent.flash_answer = '{$game_answer_json}';";
	// } else {
	// 	// 選択問題の場合
	// 	$set_game_answer_script  .= "parent.game_answer=\"".$game_answer."\";";
	// }
	// del end hasegawa 2016/11/15

	// ゲームのパス
	$read_path = MATERIAL_FLASH_DIR.$course_num."/".$stage_num."/".$lesson_num."/".$unit_num."/game/".STUDENT_GAME_FILE;

	if (file_exists($read_path)) {

		$html  = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">";
		$html  .= "<html lang=\"ja\">";
		$html  .= "<head>";
		$html  .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">";
		$html  .= "<meta http-equiv=\"Content-Style-Type\" content=\"text/css\">";
		$html  .= "<title>ゲーム動作確認</title>";
		$html  .= "<script language=\"JavaScript\">";
		$html  .= "if (parent) {";
		$html  .= "parent.game_problem_num=\"".$game_problem_num."\";";
		// $html  .= $set_game_answer_script;							// del hasegawa 2016/11/15
		$html  .= "parent.flash_answer='".$game_answer."';";			// add hasegawa 2016/11/15
		$html  .= "parent.game_success=\"".$game_success."\";";
		$html  .= "parent.game_count=\"".$game_count."\";";
		$html  .= "parent.type=\"".$type."\";";
		$html  .= "}";
		$html  .= "</script>";
		$html  .= "</head>";
		$html  .= "<body onload=\"window.document.game_form.submit();\">";
		$html  .= "<form name=\"game_form\" action=\"".$read_path."\" method=\"POST\" name=\"form\">";
		$html  .= "</form>";
		$html  .= "</body>";
		$html  .= "</html>";

	} else {
		$html  = "<br>\n";
		$html .= "　ゲームはアップロードされておりません。<br>\n";
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
function check_answer_page_erea_html() {

	$course_num = $_SESSION['CHECK_GAME']['course_num'];
	$stage_num = $_SESSION['CHECK_GAME']['stage_num'];
	$lesson_num = $_SESSION['CHECK_GAME']['lesson_num'];
	$unit_num = $_SESSION['CHECK_GAME']['unit_num'];

	$html .= "<script>";
	$html .= "function back_answer_list(){";
	$html .= "	var form = document.createElement('form');";
	$html .= "	var input1 = document.createElement('input');";
	$html .= "	var input2 = document.createElement('input');";
	$html .= "	var input3 = document.createElement('input');";
	$html .= "	var input4 = document.createElement('input');";
	$html .= "	form.action = \"/admin/game_answer_list.php\";";
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
// add end 2016/10/11 yoshizawa
?>