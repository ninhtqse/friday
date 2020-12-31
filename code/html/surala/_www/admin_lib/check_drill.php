<?php
/**
 * ベンチャー・リンク　すらら
 *
 * e-learning system 問題確認プログラム（ajax）
 *
 * @author Azet
 */


/**
 * POSTデータ判断してHTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function display_problem() {
	if ($_POST['action'] == "check") {
		$ERROR = check_answer();
		if ($ERROR && (!$_SESSION['record']['answer'] && $_SESSION['record']['answer'] != "0")) { unset($_POST['action']); }
	}

	if ($_POST['action'] == "check") {			//	解答チェック
		$html = make_check_html($ERROR);
	} elseif ($_POST['action'] == "answer") {	//	解答表示
		$html = make_answer_html();
	} else {									//	問題出題
		$html = make_default_html($ERROR);
	}

	return $html;
}

/**
 * 問題表示
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function make_default_html($ERROR) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//	解答ログリセット
	unset($_SESSION['record']);

	if (isset($_POST['problem_num'])) {
		unset($_SESSION['problem']);
		foreach($_POST as $key => $val) {
			$val = urldecode($val);
			$LINE[$key] = replace_word($val);
		}

		//	ブロック情報取得
		$sql  = "SELECT b.block_type FROM ".T_PROBLEM." p".
				" LEFT JOIN ".T_BLOCK." b ON b.block_num=p.block_num".
				" WHERE p.problem_num='".$LINE['problem_num']."'".
				" LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			if ($LINE['problem_type'] == 3) {
				$LINE['block_type'] = 1;
			} else {
				$LINE['block_type'] = $list['block_type'];
			}
		}

		// 2012/10/22 add start oda
		//	サービス情報読み込み
		$sql  = "SELECT sc.question_flg ".
				" FROM ".T_SERVICE_COURSE_LIST. " scl ".
				" INNER JOIN ".T_SERVICE." sc ON scl.service_num = sc.service_num ".
				" WHERE scl.course_num='".$LINE['course_num']."'".
				" LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$LINE['question_flg'] = $list['question_flg'];
		}
		// 2012/10/22 add end oda

		//	横・縦書き設定
		$sql  = "SELECT write_type, math_align FROM ".T_COURSE.	//	add , math_align ookawara 2012/08/28
				" WHERE course_num='".$LINE['course_num']."'".
				" LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$LINE['write_type'] = $list['write_type'];
			$LINE['math_align'] = $list['math_align'];	//	add ookawara 2012/08/28
		}

		//	ユニット情報取得
		$sql  = "SELECT * FROM ".T_UNIT.
				" WHERE unit_num='".$LINE['unit_num']."'".
				" LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			foreach ($list AS $key => $val) {
				$LINE[$key] = $val;
			}
		}

		//	ステージ・レッスン名取得
		$lesson_num = $LINE['lesson_num'];
		$sql  = "SELECT stage.stage_name, lesson.lesson_name".
				" FROM ".T_STAGE." stage,".T_LESSON." lesson".
				" WHERE stage.stage_num=lesson.stage_num".
				" AND lesson.lesson_num='".$lesson_num."'".
				" LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$LINE['stage_name'] = $list['stage_name'];
			$LINE['lesson_name'] = $list['lesson_name'];
		}
	} elseif ($_POST['direct_problem_num']) {
		unset($_SESSION['problem']);
		//	問題読み込み＆設定
		if (!$ERROR) {
			$sql  = "SELECT problem.problem_num, problem.display_problem_num, problem.problem_type, problem.sub_display_problem_num, problem.form_type,".
					" problem.question, problem.problem, problem.voice_data, problem.hint, problem.explanation,".
					" problem.answer_time, problem.parameter, problem.set_difficulty, problem.hint_number, problem.correct_number,".
					" problem.clear_number,problem. first_problem, problem.latter_problem, problem.selection_words, problem.correct,".
					" problem.option1, problem.option2, problem.option3, problem.option4, problem.option5,".
					"course.course_num,course.write_type,stage.stage_num,stage.stage_name,lesson.lesson_name,lesson.lesson_num,unit.unit_num,unit.unit_name,block.block_num".
					" , block.block_type".
					" , sc.question_flg".																	// 2012/10/22 add oda
					" FROM ".T_PROBLEM." ".T_PROBLEM.
					" INNER JOIN ".T_COURSE." ".T_COURSE." ON problem.course_num=course.course_num".
					" INNER JOIN ".T_BLOCK." ".T_BLOCK." ON problem.block_num=block.block_num".
					" INNER JOIN ".T_UNIT." ".T_UNIT." ON block.unit_num=unit.unit_num".
					" INNER JOIN ".T_LESSON." ".T_LESSON." ON block.lesson_num=lesson.lesson_num".
					" INNER JOIN ".T_STAGE." ".T_STAGE." ON block.stage_num=stage.stage_num".
					" INNER JOIN ".T_SERVICE_COURSE_LIST." scl ON problem.course_num = scl.course_num ".	// 2012/10/22 add oda
					" INNER JOIN ".T_SERVICE." sc ON scl.service_num = sc.service_num ".					// 2012/10/22 add oda
					" WHERE problem.problem_num='".$_POST['direct_problem_num']."'".
					" AND problem.state!='1'".
					" LIMIT 1;";
			if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
			if ($result = $cdb->query($sql)) {
				$PROBLEM_LIST = $list = $cdb->fetch_assoc($result);
				if ($PROBLEM_LIST) {
					if ($PROBLEM_LIST['problem_type'] == 3) {
						$PROBLEM_LIST['block_type'] = 1;
					}
					foreach ($PROBLEM_LIST AS $key => $val) {
						$val = replace_decode($val);
						$val = ereg_replace("\n","//",$val);
						$val = ereg_replace("&nbsp;"," ",$val);
						$LINE[$key] = replace_word($val);
					}
				}
			}
		}
	} else {
		foreach ($_SESSION['problem'] AS $key => $val) {
			$LINE[$key] = $val;
		}
	}
	if ($ERROR) {
		$errors = ERROR($ERROR)."<br>\n";
		unset($ERROR);
	} else {
		foreach ($LINE AS $key => $val) {
			$val = replace_decode($val);
			if ($key == "selection_words" || $key == "correct" || $key == "option1") {
				$val = ereg_replace("//","\n",$val);
			}
			if ($val !== "" && $val !== NULL) {
				$_SESSION['problem'][$key] = $val;
				define($key,$val);
			}
		}
		if ($_SESSION['problem']['course_num']) { $_SESSION['course']['course_num'] = $_SESSION['problem']['course_num']; }

		$_SESSION['record']['problem_num'] = problem_num;

		if (file_exists(LOG_DIR . "problem_lib/form_type_".form_type.".php")) {
			require_once(LOG_DIR . "problem_lib/form_type_" . form_type . ".php");
			require_once(LOG_DIR . "problem_lib/display_problem.php");
		}
	}

	//	問題設定
	$_SESSION['record']['set_time'] = time();
	$display_problem = new display_problem();
	$display_problem->set_ajax();
	$display_problem->set_problem_mode();
	$display_problem_num = $display_problem->set_display_problem_num();
	$voice_button = $display_problem->set_voice_button();
	$question = $display_problem->set_question();
	$problem = $display_problem->set_problem();
	$set_form = $display_problem->set_form();
	$LESSONUNIT = stage_name . " " . lesson_name . " " . unit_name;

	//	ヒント表示
	//	add ookawara 2012/08/24
	if (hint_number == 99) {
		$hint = $display_problem->hint_check();
	}

	//	サブスペース(area3)
	$sub_space = $display_problem->display_area3("start");

	// 画面サイズに合わせる
	$size_check = "zoom_all(".$_SESSION['course']['course_num'].");";

	if (form_type == 5) {
		$MORE_SCRIPT = $display_problem->set_more_script();
		$answer_info = $display_problem->set_answer_info();
	} elseif (form_type == 8) {
		$MORE_SCRIPT = $display_problem->set_more_script();
	} elseif (form_type == 3 || form_type == 4 || form_type == 10) {
		$tabindex = $display_problem->set_more_script();
		// del simon 2014-11-14
		//if ($tabindex > 0) {
		//	$MORE_SCRIPT = "<script type=\"text/javascript\">document.getElementById('tabindex1').focus();</script>";
		//} else {
		//	$MORE_SCRIPT = "";
		//}
	} elseif (form_type == 11) {
		$MORE_SCRIPT = $display_problem->set_more_script();
		$FUNCTIONKEY = $display_problem->fnkey();
	} else {
		$MORE_SCRIPT = "";
	}

	$form_start = $display_problem->form_start($set_form);
	$form_end = $display_problem->form_end($set_form);

	if (setting_type == 1) { unset($display_problem_num); }
	$make_html = new read_html();
	$make_html->set_dir(STUDENT_TEMP_DIR.course_num."/");
	$make_html->set_file("master_drill.htm");
	$INPUTS[MORESCRIPT] = array('result'=>'plane','value'=>$MORE_SCRIPT);
	$INPUTS[LESSONUNIT] = array('result'=>'plane','value'=>$LESSONUNIT);
	$INPUTS[DISPLAYPROBLEMNUM] = array('result'=>'plane','value'=>$display_problem_num);
	$INPUTS[VOICE] = array('result'=>'plane','value'=>$voice_button);
	$INPUTS[QUESTION] = array('result'=>'plane','value'=>$question);
	$INPUTS[PROBLEM] = array('result'=>'plane','value'=>$problem);
	$INPUTS[ANSWERINFO] = array('result'=>'plane','value'=>$answer_info);
	$INPUTS[ANSWERCOLUMN] = array('result'=>'plane','value'=>$set_form);
	$INPUTS[FORMSTART] = array('result'=>'plane','value'=>$form_start);
	$INPUTS[FORMEND] = array('result'=>'plane','value'=>$form_end);
	$INPUTS[HINT] = array('result'=>'plane','value'=>$hint);	//	add ookawara 2012/08/24

	$make_html->set_rep_cmd($INPUTS);
	$html = $make_html->replace();
	return $html;
}

/**
 * 解答確認処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーがあれば
 */
function check_answer() {

	$flag = 0;
	if ($_SESSION['problem']['form_type'] == 11) {
		if ($_POST['answer'] ||  array_search("0",$_POST['answer']) !== FALSE) {
			foreach ($_POST['answer'] AS $key => $val) {
				$judgment = $_POST['judgment'][$key];
				if ($judgment == "true") {
					$_POST['answer'][$key] = "t::".$val;
				} else {
					$_POST['answer'][$key] = "f::".$val;
				}
			}
		}
	} else {
		if ($_SESSION['problem']['form_type'] == 10 && (!$_POST['answer'] && array_search("0",$_POST['answer']) === FALSE) && ($_SESSION['record']['answer'] || $_SESSION['record']['answer'] == "0")) { $flag = 1; }
		if ($flag != 1 && (!$_POST['answer'] && array_search("0",$_POST['answer']) === FALSE)) {
			$ERROR[] = "解答が確認できません。";
		}
	}

	if (!$ERROR && ($_POST['answer'] ||  array_search("0",$_POST['answer']) !== FALSE)) {
		$_SESSION['record']['again']++;
	}

	//	問題読み込み＆設定
	if ($_SESSION['problem']) {
		foreach ($_SESSION['problem'] AS $key => $val) {
			if ($val !== "" && $val !== NULL) { define($key,$val); }
		}
	} else {
		$ERROR[] = "問題データーが確認できません。";
	}
	if ($ERROR) { return $ERROR; }

	//	問題形式ファイル読み込み
	if (file_exists(LOG_DIR . "problem_lib/form_type_".form_type.".php")) {
		require_once(LOG_DIR . "problem_lib/form_type_".form_type.".php");
		require_once(LOG_DIR . "problem_lib/display_problem.php");
		require_once(LOG_DIR . "problem_lib/correction_problem.php");
	}
	//	セッションに解答があれば解答設定
	if ($_SESSION['record']['answer'] || $_SESSION['record']['answer'] == "0") {
		$LIST_ANSWER = explode("//",$_SESSION['record']['answer']);
		foreach ($LIST_ANSWER AS $KEY => $VAL) {
			if (preg_match("/^t::/",$LIST_ANSWER[$KEY])) {
				$tf = "t";
				$correct = preg_replace("/^t::/","",$LIST_ANSWER[$KEY]);
			} else {
				$tf = "f";
				$correct = preg_replace("/^f::/","",$LIST_ANSWER[$KEY]);
			}

			if ($tf == "t") { $true_answer[$KEY] = $correct; }
		}
	}

	//	正誤判定
	$correction_problem = new correction_problem();
	$correction_problem->set_answer($_POST['answer']);
	$correction_problem->set_true_answer($true_answer);
	list($success,$answers,$answer_info) = $correction_problem->correction();
	$_SESSION['record']['success'] = $success;
	$_SESSION['record']['answer'] = $answers;
	$_SESSION['record']['answer_info'] = $answer_info;
	return $ERROR;
}


/**
 * 解答後表示
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function make_check_html($ERROR) {

	if ($ERROR) {
		$errors = ERROR($ERROR)."<br>\n";
		unset($ERROR);
	}
	if (file_exists(LOG_DIR . "problem_lib/form_type_".form_type.".php")) {
		require_once(LOG_DIR . "problem_lib/form_type_".form_type.".php");
		require_once(LOG_DIR . "problem_lib/display_problem.php");
	}

	// del start hasegawa 2018/06/08 低学年ボタン復活
	// // add yoshizawa 2017/03/08 小学生低学年版2次開発 低学年は質問ボタン表示しない
	// // unit_keyを取得
	// $sql  = "SELECT u.unit_key FROM ".T_BLOCK." b"
	// 	." INNER JOIN ".T_UNIT." u ON u.unit_num=b.unit_num"
	// 	." AND u.display='1' AND u.state='0'"
	// 	." WHERE b.display='1' AND b.state='0'"
	// 	." AND b.block_num='".$_SESSION['problem']['block_num']."'"
	// 	.";";
	// 
	// $schoolYear = "";
	// if ($result = $cdb->query($sql)) {
	// 	$list = $cdb->fetch_assoc($result);
	// 	if($list['unit_key']) {
	// 		$schoolYear = getUnitKeyInitial($list['unit_key']);
	// 	}
	// }
	// if($schoolYear == "L" && !defined('question_disable_flg')) { define("question_disable_flg",1); }
	// // add end yoshizawa 2017/03/08
	// del end hasegawa 2018/06/08

	$display_timerstartup = 0;
	if ($_SESSION['record']['success'] == 1) {	//	正解

		$display_problem = new display_problem();
		$display_problem->set_ajax();
		$display_problem->set_problem_mode();
		$display_problem->set_success($_SESSION['record']['success']);
		$display_problem->set_unit_comp($_SESSION['record']['unit_comp']);
		$display_problem->set_again($_SESSION['record']['again']);
		$display_problem->set_answers($_SESSION['record']['answer']);
		$display_problem_num = $display_problem->set_display_problem_num();
		$voice_button = $display_problem->set_voice_button();
		$question = $display_problem->set_question();
		$problem = $display_problem->set_problem();
		$explanation = $display_problem->set_explanation("true");
		$answer_info = $display_problem->set_answer_info($_SESSION['record']['answer_info']);
		if (form_type == 5) { $display_problem->set_end_flg(); }
		$send_question = $display_problem->send_question_check();
		$LESSONUNIT = stage_name . " " . lesson_name . " ". unit_name;

		//	サブスペース(area3)
		$sub_space = $display_problem->display_area3("true");

		$display_timerstartup = 1;

	} else {	//	不正解

		$success = $_SESSION['record']['success'];
		if ($_SESSION['record']['again'] >= correct_number) {
			$success = 1;
		}

		$display_problem = new display_problem();
		$display_problem->set_ajax();
		$display_problem->set_problem_mode();
		$display_problem->set_success($success);
		$display_problem->set_answers($_SESSION['record']['answer']);
		$display_problem->set_again($_SESSION['record']['again']);
		$display_problem_num = $display_problem->set_display_problem_num();
		$voice_button = $display_problem->set_voice_button();
		$question = $display_problem->set_question();
		$problem = $display_problem->set_problem();
		$answer_info = $display_problem->set_answer_info($_SESSION['record']['answer_info']);
		if (correct_number > 0 && $_SESSION['record']['again'] >= correct_number) {
			if (form_type == 5) { $display_problem->set_end_flg(); }
		} else {
			$set_retry_msg = $display_problem->set_retry_msg();
			$set_form = $display_problem->set_form();
		}
		$send_question = $display_problem->send_question_check();
		$LESSONUNIT = stage_name . " " . lesson_name . " ". unit_name;

		//	ヒント表示
		if (hint_number > 0 && $_SESSION['record']['again'] >= hint_number && $_SESSION['record']['again'] < correct_number) {
			$hint = $display_problem->hint_check();
		}

		//	正解を見るボタン
		$type = "true";
		if (correct_number > 0 && $_SESSION['record']['again'] >= correct_number) {
			$explanation_button = $display_problem->explanation_button();
			unset($form_end);
			$form_end_check = 1;
			$display_timerstartup = 1;
		}

		//	サブスペース(area3)
		$sub_space = $display_problem->display_area3("false");
	}

	// 画面サイズに合わせる
	$size_check = "zoom_all(".$_SESSION['course']['course_num'].");";

	$MORE_SCRIPT = "";
	if (form_type == 5) {
		if (correct_number > 0 && $_SESSION['record']['again'] < correct_number && $_SESSION['record']['success'] != 1) {
			$MORE_SCRIPT = $display_problem->set_more_script();
		}
	} elseif (form_type == 8) {
		if (correct_number > 0 && $_SESSION['record']['again'] < correct_number && $_SESSION['record']['success'] != 1) {
			$MORE_SCRIPT = $display_problem->set_more_script();
		}
	} elseif (form_type == 3 || form_type == 4 || form_type == 10) {
		$tabindex = $display_problem->set_more_script();
		// del simon 2014-11-14
		//if ($tabindex > 0) {
		//	$MORE_SCRIPT = "<script type=\"text/javascript\">document.getElementById('tabindex1').focus();</script>";
		//} else {
		//	$MORE_SCRIPT = "";
		//}
	} elseif (form_type == 11) {
		$MORE_SCRIPT = $display_problem->set_more_script();
		$FUNCTIONKEY = $display_problem->fnkey();
	}
	$form_start = $display_problem->form_start($set_form);
	$form_end = $display_problem->form_end($set_form);
	$css_path = MATERIAL_TEMP_DIR.course_num."/".stage_num."/course.css";
	$make_html = new read_html();
	$make_html->set_dir(STUDENT_TEMP_DIR.course_num."/");
	$make_html->set_file("master_drill.htm");
	$INPUTS[MORESCRIPT] = array('result'=>'plane','value'=>$MORE_SCRIPT);
	$INPUTS[LESSONUNIT] = array('result'=>'plane','value'=>$LESSONUNIT);
	$INPUTS[DISPLAYPROBLEMNUM] = array('result'=>'plane','value'=>$display_problem_num);
	$INPUTS[VOICE] = array('result'=>'plane','value'=>$voice_button);
	$INPUTS[QUESTION] = array('result'=>'plane','value'=>$question);
	$INPUTS[PROBLEM] = array('result'=>'plane','value'=>$problem);
	$INPUTS[EXPLANATION] = array('result'=>'plane','value'=>$explanation);
	$INPUTS[ANSWERINFO] = array('result'=>'plane','value'=>$answer_info);
	$INPUTS[RETRYMSG] = array('result'=>'plane','value'=>$set_retry_msg);
	$INPUTS[ANSWERCOLUMN] = array('result'=>'plane','value'=>$set_form);
	$INPUTS[FORMSTART] = array('result'=>'plane','value'=>$form_start);
	$INPUTS[FORMEND] = array('result'=>'plane','value'=>$form_end);
	$INPUTS[SENDQUESTION] = array('result'=>'plane','value'=>$send_question);
	$INPUTS[HINT] = array('result'=>'plane','value'=>$hint);
	$INPUTS[EXPLBUTTON] = array('result'=>'plane','value'=>$explanation_button);

	$make_html->set_rep_cmd($INPUTS);
	$html = $make_html->replace();
	return $html;
}


/**
 * 解答表示
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function make_answer_html() {

	//	問題読み込み＆設定
	if ($_SESSION['problem']) {
		foreach ($_SESSION['problem'] AS $key => $val) {
			if ($val !== "" && $val !== NULL) { define($key,$val); }
		}
	} else {
		$ERROR[] = "問題データーが確認できません。";
	}

	if (file_exists(LOG_DIR . "problem_lib/form_type_".form_type.".php")) {
		require_once(LOG_DIR . "problem_lib/form_type_" . form_type . ".php");
		require_once(LOG_DIR . "problem_lib/display_problem.php");
	}

	// del start hasegawa 2018/06/08 低学年ボタン復活
	// // add yoshizawa 2017/03/08 小学生低学年版2次開発 低学年は質問ボタン表示しない
	// // unit_keyを取得
	// $sql  = "SELECT u.unit_key FROM ".T_BLOCK." b"
	// 	." INNER JOIN ".T_UNIT." u ON u.unit_num=b.unit_num"
	// 	." AND u.display='1' AND u.state='0'"
	// 	." WHERE b.display='1' AND b.state='0'"
	// 	." AND b.block_num='".$_SESSION['problem']['block_num']."'"
	// 	.";";
	// 
	// $schoolYear = "";
	// if ($result = $cdb->query($sql)) {
	// 	$list = $cdb->fetch_assoc($result);
	// 	if($list['unit_key']) {
	// 		$schoolYear = getUnitKeyInitial($list['unit_key']);
	// 	}
	// }
	// if($schoolYear == "L" && !defined('question_disable_flg')) { define("question_disable_flg",1); }
	// // add end yoshizawa 2017/03/08
	// del end hasegawa 2018/06/08

	//	解説ページ作成
	$display_problem = new display_problem();
	$display_problem->set_ajax();
	$display_problem->set_problem_mode();
	$display_problem->set_success("1");
	$display_problem->set_unit_comp($_SESSION['record']['unit_comp']);
	$display_problem->set_again($_SESSION['record']['again']);
	$display_problem->set_correct();
	$display_problem_num = $display_problem->set_display_problem_num();
	$voice_button = $display_problem->set_voice_button();
	$question = $display_problem->set_question();
	$problem = $display_problem->set_problem();
	$explanation = $display_problem->set_explanation("start");
	if (form_type == 5) {
		$display_problem->set_end_flg();
	}
	$set_form = $display_problem->set_form();
	$set_correct_html = $display_problem->set_correct_html();
	$send_question = $display_problem->send_question_check();
	$LESSONUNIT = stage_name . " " . lesson_name . " ". unit_name;

	//	サブスペース(area3)
	$sub_space = $display_problem->display_area3("start");

	// 画面サイズに合わせる
	$size_check = "zoom_all(".$_SESSION['course']['course_num'].");";

	$MORE_SCRIPT = "";
	if (form_type == 5) {
		if (correct_number > 0 && $_SESSION['record']['again'] < correct_number && $_SESSION['record']['success'] != 1) {
			$MORE_SCRIPT = $display_problem->set_more_script();
		}
		$answer_info = $display_problem->set_answer_info($_SESSION['record']['answer']);
	} elseif (form_type == 8) {
		if (correct_number > 0 && $_SESSION['record']['again'] < correct_number && $_SESSION['record']['success'] != 1) {
			$MORE_SCRIPT = $display_problem->set_more_script();
		}
		$answer_info = $display_problem->set_answer_info(correct);
	} elseif (form_type == 3 || form_type == 4 || form_type == 10) {
		if (form_type != 4) {
			$answer_info = $display_problem->set_answer_info($_SESSION['record']['answer']);
		} else {
			$answer_info = $display_problem->set_answer_info($_SESSION['record']['answer_info']);
		}
	} elseif (form_type == 11) {
		$MORE_SCRIPT = $display_problem->set_more_script();
		$answer_info = $display_problem->set_answer_info(correct);
	} elseif (form_type == 1 || form_type == 2) {
		$answer_info = $display_problem->set_answer_info();
	}

	$form_start = $display_problem->form_start($set_form);
	$form_end = $display_problem->form_end($set_form);

	$make_html = new read_html();
	$make_html->set_dir(STUDENT_TEMP_DIR.course_num."/");
	$make_html->set_file("master_drill.htm");
	$INPUTS[MORESCRIPT] = array('result'=>'plane','value'=>$MORE_SCRIPT);
	$INPUTS[LESSONUNIT] = array('result'=>'plane','value'=>$LESSONUNIT);
	$INPUTS[DISPLAYPROBLEMNUM] = array('result'=>'plane','value'=>$display_problem_num);
	$INPUTS[VOICE] = array('result'=>'plane','value'=>$voice_button);
	$INPUTS[QUESTION] = array('result'=>'plane','value'=>$question);
	$INPUTS[PROBLEM] = array('result'=>'plane','value'=>$problem);
	$INPUTS[EXPLANATION] = array('result'=>'plane','value'=>$explanation);
	$INPUTS[ANSWERINFO] = array('result'=>'plane','value'=>$answer_info);
	$INPUTS[FORMSTART] = array('result'=>'plane','value'=>$form_start);
	$INPUTS[FORMEND] = array('result'=>'plane','value'=>$form_end);
	$INPUTS[SENDQUESTION] = array('result'=>'plane','value'=>$send_question);
	$INPUTS[HINT] = array('result'=>'plane','value'=>$hint);
	$INPUTS[CORRECT] = array('result'=>'plane','value'=>$set_correct_html);
	$INPUTS[EXPLBUTTON] = array('result'=>'plane','value'=>$explanation_button);

	$make_html->set_rep_cmd($INPUTS);
	$html = $make_html->replace();
	return $html;
}
?>
