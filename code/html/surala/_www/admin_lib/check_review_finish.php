<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * 診断復習最終メッセージ＆ドリル
 *
 * テーブル名　T_CHECK_STUDY_RECODEへ変更
 * $_SESSION['studentid']['student_num']を$_SESSION['managerid']['manager_num']変更
 * check_study_record_numをcheck_check_study_record_num
 * ユーザーフォルダー設定変更
 * send_question() を　send_question_check()
 * hint() を hint_check()
 *
 * @@@ 新ＤＢ対応
 * 	$_SESSION['manager']['manager_num']を$_SESSION["myid"]["id"]に変更
 * 	カラム名の変更
 *
 * @author Azet
 */


/**
 * SESSIONに設定する機能
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 */
function set_unit() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$student_num = $_SESSION["myid"]["id"];
	$course_num = $_SESSION['course']['course_num'];
	$unit_num = $_SESSION['course']['unit_num'];
	$block_num = $_SESSION['course']['block_num'];

	//	横・縦書き設定
	$sql  = "SELECT * FROM ".T_COURSE.
			" WHERE course_num='$course_num' LIMIT 1;";
	if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		define("write_type",$list['write_type']);
		define("math_align",$list['math_align']);	//	add ookawara 2012/08/28
	}

	//	ブロック情報取得
	$sql  = "SELECT * FROM ".T_BLOCK.
			" WHERE block_num='".$block_num."'".
			" AND course_num='".$course_num."'".
			" AND unit_num='".$unit_num."'".
			" LIMIT 1;";
	if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		foreach ($list AS $key => $val) {
			if ($key == "block_type") {
				define("base_block_type",$val);
				$_SESSION['course']['block_type'] = $val;
			} else {
				define("$key",$val);
			}
		}
	}
	define("block_type","1");

	//	ステージ・レッスン名取得
	$lesson_num = lesson_num;
	$sql  = "SELECT stage.stage_name, lesson.lesson_name".
			" FROM ".T_STAGE." stage,".T_LESSON." lesson".
			" WHERE stage.stage_num=lesson.stage_num".
			" AND lesson.lesson_num='".$lesson_num."'".
			" LIMIT 1;";
	if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		define("stage_name",$list['stage_name']);
		define("lesson_name",$list['lesson_name']);
	}

	//	ユニット名取得
	$lesson_num = lesson_num;
	$sql  = "SELECT unit_name FROM ".T_UNIT.
			" WHERE course_num='".$course_num."'".
			" AND unit_num='".$unit_num."'".
			" LIMIT 1;";
	if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		define("unit_name",$list['unit_name']);
	}

	//	学習回数チェック
	//	$_SESSION['course']['review']には、学習回数が登録される。
	if ($_SESSION['course']['review'] == "" || $_SESSION['course']['finish_time'] == "") {
		unset($_SESSION['course']['review']);
		unset($_SESSION['course']['finish_time']);
		$sql  = "SELECT regist_time FROM ".T_FINISH_UNIT.
				" WHERE student_id='".$student_num."'".
				" AND course_num='".$course_num."'".
				" AND unit_num='".$unit_num."'".
				" AND skip!='1'".
				" AND state!='1'".
				" ORDER BY regist_time;";
		if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
		if ($result = $cdb->query($sql)) {
			while ($list = $cdb->fetch_assoc($result)) {
				$regist_time = $list['regist_time'];
				$_SESSION['course']['review']++;
				$_SESSION['course']['finish_time'] = $regist_time;
			}
		}
		$_SESSION['course']['review']++;
		//	ユニット最終クリアー時間がない場合、空の値を入れる。
		if (!$_SESSION['course']['finish_time']) {
			$_SESSION['course']['finish_time'] = "0";
		}
	}
}


/**
 * HTMLコンテンツを作成する機能
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @return string HTML
 */
function start() {

	if ($_POST['action'] == "check") {
		$ERROR = check_answer();
//		if ($ERROR && !$_SESSION['record']['answer']) { unset($_POST['action']); }	//	del ookawara 2009/08/10
		if ($ERROR && (!$_SESSION['record']['answer'] && $_SESSION['record']['answer'] != "0")) { unset($_POST['action']); }	//	add ookawara 2009/08/10
	}

	if ($_POST['action'] == "check") {	//	解答チェック
		$html = make_check_html($ERROR);
	} elseif ($_POST['action'] == "answer") {
		//	解答表示
		$html = make_answer_html();
	} else {	//	問題出題
		$html = make_default_html($ERROR);
	}

	return $html;
}


/**
 * 問題表示
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @param array $ERROR
 * @return array エラーがあれば
 */
function make_default_html($ERROR) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if ($ERROR) {
		$errors = ERROR($ERROR)."<br>\n";
		unset($ERROR);
	}

	//	解答ログリセット
	unset($_SESSION['record']);

	//	問題抽出
	//	不正解の問題
	unset($problem_num_list);
	unset($count);
	if ($_SESSION['course']['review'] >= 2) {
		if (base_block_type == 2) {
			$class_m = 202020000;
		} elseif (base_block_type == 3) {
			$class_m = 202030000;
		}
	} else {
		if (base_block_type == 2) {
			$class_m = 102020000;
		} elseif (base_block_type == 3) {
			$class_m = 102030000;
		}
	}
	$sql  = "SELECT problem_num FROM ".T_CHECK_STUDY_RECODE.
			" WHERE student_id='{$_SESSION['myid']['id']}'" .
			" AND class_m='".$class_m."'".
			" AND course_num='".$_SESSION['course']['course_num']."'".
			" AND unit_num='".$_SESSION['course']['unit_num']."'".
			" AND block_num='".$_SESSION['course']['block_num']."'".
			" AND success='0'".
//			" AND DATE(regist_time)=CURDATE()".
			" AND regist_time>'".$_SESSION['course']['finish_time']."'".
			" GROUP BY problem_num";
	if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
	if ($result = $cdb->query($sql)) {
		$count = $cdb->num_rows($result);
	}

	if ($count > 0) {
		while ($list = $cdb->fetch_assoc($result)) {
			$problem_num = $list['problem_num'];
			if ($problem_num_list) { $problem_num_list .= ","; }
			if ($problem_num > 0) { $problem_num_list .= $problem_num; }
		}
	}

	//	問題読み込み設定
	//	動作確認用表示問題番号設定
//	$problem_num_list = 136;
	if (difficulty_type == 1) { $difficulty = "auto_difficulty"; } else { $difficulty = "set_difficulty"; }
	$sql  = "SELECT problem_num, display_problem_num, problem_type, sub_display_problem_num, form_type,".
			" question, problem, voice_data, hint, explanation,".
			" answer_time, parameter, set_difficulty, hint_number, correct_number,".
			" clear_number, first_problem, latter_problem, selection_words, correct,".
			" option1, option2, option3, option4, option5".
			" FROM ".T_PROBLEM.
			" WHERE problem_num IN (".$problem_num_list.")".
			" AND course_num='".$_SESSION['course']['course_num']."'".
			" AND block_num='".$_SESSION['course']['block_num']."'".
			" AND problem_type='1'".
			" AND display='1'".
			" AND state!='1'".
			" ORDER BY ".$difficulty.
			" LIMIT 1;";
	if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
	if ($result = $cdb->query($sql)) {
		$PROBLEM_LIST = $list = $cdb->fetch_assoc($result);
		if ($PROBLEM_LIST) {
			foreach ($PROBLEM_LIST AS $key => $val) {
				if ($key == "block_num" || $key == "course_num") { continue; }
				$val = replace_decode($val);
				$$key = $val;
				if ($val) { define("$key",$val); }
			}
		}
	}
	$_SESSION['record']['problem_num'] = $problem_num;

	if (file_exists(LOG_DIR . "problem_lib/form_type_".form_type.".php")) {
		require_once(LOG_DIR . "problem_lib/form_type_" . form_type . ".php");
		require_once(LOG_DIR . "problem_lib/display_problem.php");
	}

	//	問題設定
	$_SESSION['record']['set_time'] = time();
	$display_problem = new display_problem();
	$display_problem_num = $display_problem->set_display_problem_num();
	$voice_button = $display_problem->set_voice_button();
	$question = $display_problem->set_question();
	$problem = $display_problem->set_problem();
	$set_form = $display_problem->set_form();
	$form_start = $display_problem->form_start($set_form);
	$form_end = $display_problem->form_end($set_form);
	$LESSONUNIT = stage_name . " " . lesson_name . " ". unit_name;

	//	ヒント表示
	//	add ookawara 2012/08/24
	$hint="";	// add hasegawa 2016/07/12 hintが出てしまうため
	if (hint_number == 99) {
		$hint = $display_problem->hint_check();
	}

	// 画面サイズに合わせる
	$size_check = "check_size(1,".$_SESSION['course']['course_num'].");";

	$ONLOAD_BODY = "";
	if (form_type == 5) {
		$MORE_SCRIPT = $display_problem->set_more_script();
		$answer_info = $display_problem->set_answer_info();
		$ONLOAD_BODY = "<body onload=\"TimerStartUp();".$size_check."\">";
	} elseif (form_type == 8) {
		$MORE_SCRIPT = $display_problem->set_more_script();
		$ONLOAD_BODY = "<body onload=\"make_ele();".$size_check."\">";
	} elseif (form_type == 3 || form_type == 4 || form_type == 10) {
		$tabindex = $display_problem->set_more_script();
		if ($tabindex > 0) {
			// edit simon 2014-11-14
			//$ONLOAD_BODY = "<body onload=\"".$size_check."document.getElementById('tabindex1').focus();\">";
			$ONLOAD_BODY = "<body onload=\"".$size_check."\">";
		} else {
			$ONLOAD_BODY = "";
		}
	} elseif (form_type == 11) {
		$MORE_SCRIPT = $display_problem->set_more_script();
		$FUNCTIONKEY = $display_problem->fnkey();
		$size_check = "";
	} else {
		$MORE_SCRIPT = "";
		$ONLOAD_BODY = "";
	}
	$msg = "<div id=\"last_msg\">最後にもう一問解答してみましょう！</div>\n";

	if ($ONLOAD_BODY == "" && $size_check){
		$ONLOAD_BODY = "<body onLoad=\"".$size_check."\">";
	}

	//	ログ情報登録
	if ($_SESSION['course']['review'] >= 2) {
		if (base_block_type == 2) {
			$class_m = 202020600;
		} elseif (base_block_type == 3) {
			$class_m = 202030600;
		}
	} else {
		if (base_block_type == 2) {
			$class_m = 102020600;
		} elseif (base_block_type == 3) {
			$class_m = 102030600;
		}
	}
	$INSERT_DATA['class_m'] = $class_m;
	$INSERT_DATA['student_id'] = $_SESSION["myid"]["id"];
//	$INSERT_DATA['area_num'] = $_SESSION['studentid']['area_num'];
//	$INSERT_DATA['enterprise_num'] = $_SESSION['studentid']['enterprise_num'];
//	$INSERT_DATA['school_num'] = $_SESSION['studentid']['school_num'];
//	$INSERT_DATA['cr_num'] = $_SESSION['pc']['classroom'];
//	$INSERT_DATA['seat_num'] = $_SESSION['pc']['seat'];
	$INSERT_DATA['course_num'] = $_SESSION['course']['course_num'];
	$INSERT_DATA['unit_num'] = $_SESSION['course']['unit_num'];
	$INSERT_DATA['block_num'] = $_SESSION['course']['block_num'];
	$INSERT_DATA['problem_num'] = $problem_num;
	$INSERT_DATA['display_problem_num'] = display_problem_num;
	$INSERT_DATA['regist_time'] = "now()";

	$ERROR = $cdb->insert(T_CHECK_STUDY_RECODE,$INSERT_DATA);
	if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::query-&gt;insert::T_CHECK_STUDY_RECODE"; }

//	if (setting_type == 1) { unset($display_problem_num); }
	$make_html = new read_html();
	$make_html->set_dir(STUDENT_TEMP_DIR.course_num."/".stage_num."/");
	$make_html->set_file(STUDENT_TEMP_LESSON);
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
	$INPUTS[ERROR] = array('result'=>'plane','value'=>$errors);
	$INPUTS[SUBSPACE] = array('result'=>'plane','value'=>$msg);
	$INPUTS[FUNCTIONKEY] = array('result'=>'plane','value'=>$FUNCTIONKEY);

	$make_html->set_rep_cmd($INPUTS);
	$html = $make_html->replace();
	if ($ONLOAD_BODY) { $html = ereg_replace("<body>",$ONLOAD_BODY,$html); }

	return $html;
}



/**
 * 解答確認処理
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @return array エラーがあれば
 */
function check_answer() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (!$_SESSION['record']['problem_num']) {
		$ERROR[] = "問題番号が確認できません。";
	} else {
		$sql  = "SELECT problem_num, display_problem_num, problem_type, sub_display_problem_num, form_type,".
				" question, problem, voice_data, hint, explanation,".
				" answer_time, parameter, set_difficulty, hint_number, correct_number,".
				" clear_number, first_problem, latter_problem, selection_words, correct,".
				" option1, option2, option3, option4, option5".
				" FROM ".T_PROBLEM.
				" WHERE problem_num='".$_SESSION['record']['problem_num']."'".
				" AND course_num='".$_SESSION['course']['course_num']."'".
				" AND block_num='".$_SESSION['course']['block_num']."'".
				" AND display='1'".
				" AND state!='1'".
				" LIMIT 1;";
		if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
		if ($result = $cdb->query($sql)) {
			$PROBLEM_LIST = $list = $cdb->fetch_assoc($result);
			$form_type = $list['form_type'];
			$_SESSION['problem']['form_type'] = $form_type;
		}
	}
	if (!$_SESSION['record']['set_time']) { $ERROR[] = "学習開始時間が確認できません。"; }
	if ($_SESSION['problem']['form_type'] == 11) {
//		if ($_POST['answer']) {	//	del ookawara 2009/08/10
		if ($_POST['answer'] ||  array_search("0",$_POST['answer']) !== FALSE) {	//	add ookawara 2009/08/10
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
		$flag = 0;
//		if ($form_type == 10 && !$_POST['answer'] && $_SESSION['record']['answer']) { $flag = 1; }	//	del ookawara 2009/08/10
		if ($_SESSION['problem']['form_type'] == 10 && (!$_POST['answer'] && array_search("0",$_POST['answer']) === FALSE) && ($_SESSION['record']['answer'] || $_SESSION['record']['answer'] == "0")) { $flag = 1; }	//	add ookawara 2009/08/10
//		if ($flag != 1 && !$_POST['answer']) {	//	del ookawara 2009/08/10
		if ($flag != 1 && (!$_POST['answer'] && array_search("0",$_POST['answer']) === FALSE)) {	//	add oz 2009/08/13
			$ERROR[] = "解答が確認できません。";
		}
	}

//	if (!$ERROR && $_POST['answer']) {	//	del ookawara 2009/08/10
	if (!$ERROR && ($_POST['answer'] ||  array_search("0",$_POST['answer']) !== FALSE)) {	//	add oz 2009/08/13
		$_SESSION['record']['again']++;
	}

	//	問題読み込み＆設定
	if ($PROBLEM_LIST && $_SESSION['record']['problem_num'] && $_SESSION['record']['again']) {
		foreach ($PROBLEM_LIST AS $key => $val) {
			$val = replace_decode($val);
			if ($key == "correct_number") { continue; }
			if ($key == "hint_number") {
				if ($val < 1) { $val = 1; }
				$hint_number = $val;
			}
			if ($val) { define("$key",$val); }
		}
//		$correct_number = $hint_number + 1;
		$correct_number = 5;
		define("correct_number",$correct_number);
	}
	if ($ERROR) { return $ERROR; }

	if (file_exists(LOG_DIR . "problem_lib/form_type_".form_type.".php")) {
		require_once(LOG_DIR . "problem_lib/form_type_".form_type.".php");
		require_once(LOG_DIR . "problem_lib/display_problem.php");
	}

	//	セッションに解答があれば解答設定
//	if ($_SESSION['record']['answer']) {	//	del ookawara 2009/08/10
	if ($_SESSION['record']['answer'] || $_SESSION['record']['answer'] == "0") {	//	add ookawara 2009/08/10
		$LIST_ANSWER = explode("//",$_SESSION['record']['answer']);
		foreach ($LIST_ANSWER AS $KEY => $VAL) {
			//	add ookawara 2009/07/29
//			if (preg_match("/^t::/",$this->LIST_ANSWER[$KEY])) {	//	del ookawara	2009/09/02
			if (preg_match("/^t::/",$LIST_ANSWER[$KEY])) {	//	add ookawara	2009/09/02
				$tf = "t";
//				$correct = preg_replace("/^t::/","",$this->LIST_ANSWER[$KEY]);	//	del ookawara	2009/09/02
				$correct = preg_replace("/^t::/","",$LIST_ANSWER[$KEY]);	//	add ookawara	2009/09/02
			} else {
				$tf = "f";
//				$correct = preg_replace("/^f::/","",$this->LIST_ANSWER[$KEY]);	//	del ookawara	2009/09/02
				$correct = preg_replace("/^f::/","",$LIST_ANSWER[$KEY]);	//	add ookawara	2009/09/02
			}
			//	add ookawara 2009/07/29

//			list($tf,$correct) = explode("::",$LIST_ANSWER[$KEY]);	//	del ookawara 2009/07/29
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

	//	解答時間
	$answer_time = time() - $_SESSION['record']['set_time'];

	//	ログ記録
	if ($_SESSION['course']['review'] >= 2) {
		if (base_block_type == 2) {
			$class_m = 202020700;
		} elseif (base_block_type == 3) {
			$class_m = 202030700;
		}
	} else {
		if (base_block_type == 2) {
			$class_m = 102020700;
		} elseif (base_block_type == 3) {
			$class_m = 102030700;
		}
	}
	$INSERT_DATA['class_m'] = $class_m;
	$INSERT_DATA['student_id'] = $_SESSION["myid"]["id"];
//	$INSERT_DATA['area_num'] = $_SESSION['studentid']['area_num'];
//	$INSERT_DATA['enterprise_num'] = $_SESSION['studentid']['enterprise_num'];
//	$INSERT_DATA['school_num'] = $_SESSION['studentid']['school_num'];
//	$INSERT_DATA['cr_num'] = $_SESSION['pc']['classroom'];
//	$INSERT_DATA['seat_num'] = $_SESSION['pc']['seat'];
	$INSERT_DATA['course_num'] = $_SESSION['course']['course_num'];
	$INSERT_DATA['unit_num'] = $_SESSION['course']['unit_num'];
	$INSERT_DATA['block_num'] = $_SESSION['course']['block_num'];
	$INSERT_DATA['problem_num'] = $_SESSION['record']['problem_num'];
	$INSERT_DATA['display_problem_num'] = display_problem_num;
	$INSERT_DATA['answer'] = addslashes($_SESSION['record']['answer']);
	$INSERT_DATA['success'] = $_SESSION['record']['success'];
	$INSERT_DATA['answer_time'] = $answer_time;
	$INSERT_DATA['again'] = $_SESSION['record']['again'];
	$INSERT_DATA['passing'] = $passing;
	$INSERT_DATA['regist_time'] = "now()";

	$ERROR = $cdb->insert(T_CHECK_STUDY_RECODE,$INSERT_DATA);
	if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::query-&gt;insert::T_CHECK_STUDY_RECODE"; }

	return $ERROR;
}



/**
 * 解答後表示
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
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

	$_SESSION['record']['set_time'] = time();

	if (file_exists(LOG_DIR . "problem_lib/form_type_".form_type.".php")) {
		require_once(LOG_DIR . "problem_lib/form_type_".form_type.".php");
		require_once(LOG_DIR . "problem_lib/display_problem.php");
	}

	$display_timerstartup = 0;
	if ($_SESSION['record']['success'] == 1) {	//	正解
		$display_problem = new display_problem();
		$display_problem->set_success($_SESSION['record']['success']);
		$display_problem->set_unit_comp("1");
		$display_problem->set_again($_SESSION['record']['again']);
		$display_problem->set_answers($_SESSION['record']['answer']);
		$display_problem_num = $display_problem->set_display_problem_num();
		$voice_button = $display_problem->set_voice_button();
		$question = $display_problem->set_question();
		$problem = $display_problem->set_problem();
		$explanation = $display_problem->set_explanation("block_clear");
		$answer_info = $display_problem->set_answer_info($_SESSION['record']['answer_info']);
		$set_form = $display_problem->set_form();
		$form_start = $display_problem->form_start($set_form);
		$form_end = $display_problem->form_end($set_form);
		$send_question = $display_problem->send_question_check();
		$LESSONUNIT = stage_name . " " . lesson_name . " ". unit_name;
		$succorfail = "<p style=\"font-weight : 900; color:#ff0000;\">正解</p>\n";

		//	サブスペース(area3)
		$sub_space = $display_problem->display_area3("block_clear");

		$display_timerstartup = 1;

	} else {	//	不正解
		$display_problem = new display_problem();
		$display_problem->set_success($_SESSION['record']['success']);
		$display_problem->set_answers($_SESSION['record']['answer']);
		$again = $_SESSION['record']['again'];
		if (hint_number < $again ) { $again = hint_number; }
		$display_problem->set_again($again);
		$display_problem_num = $display_problem->set_display_problem_num();
		$voice_button = $display_problem->set_voice_button();
		$question = $display_problem->set_question();
		$problem = $display_problem->set_problem();
		$answer_info = $display_problem->set_answer_info($_SESSION['record']['answer_info']);
		$set_form = $display_problem->set_form();
		$form_start = $display_problem->form_start($set_form);
		$form_end = $display_problem->form_end($set_form);
		$send_question = $display_problem->send_question_check();
		$LESSONUNIT = stage_name . " " . lesson_name . " ". unit_name;
		$succorfail = "<p style=\"font-weight : 900; color:#0033ff;\">不正解</p>\n";

		//	ヒント表示
		if ($_SESSION['record']['again'] < 5 && hint_number > 0 && $_SESSION['record']['again'] >= hint_number) {
			$hint = $display_problem->hint_check();
		}

		//	解答を見るボタン
		if ($_SESSION['record']['again'] >= 5) {
			$explanation_button = $display_problem->explanation_button();
			$display_timerstartup = 1;
		}

		//	サブスペース(area3)
		$sub_space = $display_problem->display_area3("finish_false");
	}

	// 画面サイズに合わせる
	$size_check = "check_size(1,".$_SESSION['course']['course_num'].");";

	$ONLOAD_BODY = "";
	if (form_type == 5 && $display_timerstartup != 1) {
		$MORE_SCRIPT = $display_problem->set_more_script();
		$ONLOAD_BODY = "<body onload=\"TimerStartUp();".$size_check."\">";
	} elseif (form_type == 8) {
		$MORE_SCRIPT = $display_problem->set_more_script();
		$ONLOAD_BODY = "<body onload=\"make_ele();".$size_check."\">";
		$answer_info = $display_problem->set_answer_info();
	} elseif (form_type == 3 || form_type == 4 || form_type == 10) {
		$tabindex = $display_problem->set_more_script();
		if ($tabindex > 0) {
			// edit simon 2014-11-14
			//$ONLOAD_BODY = "<body onload=\"".$size_check."document.getElementById('tabindex1').focus();\">";
			$ONLOAD_BODY = "<body onload=\"".$size_check."\">";
		} else {
			$ONLOAD_BODY = "";
		}
	} elseif (form_type == 11) {
		$MORE_SCRIPT = $display_problem->set_more_script();
		$FUNCTIONKEY = $display_problem->fnkey();
		$size_check = "";
	} else {
		$MORE_SCRIPT = "";
		$ONLOAD_BODY = "";
	}

	if ($ONLOAD_BODY == "" && $size_check){
		$ONLOAD_BODY = "<body onLoad=\"".$size_check."\">";
	}

//	if (setting_type == 1) { unset($display_problem_num); }
	$make_html = new read_html();
	$make_html->set_dir(STUDENT_TEMP_DIR.course_num."/".stage_num."/");
	$make_html->set_file(STUDENT_TEMP_LESSON);
	$INPUTS[MORESCRIPT] = array('result'=>'plane','value'=>$MORE_SCRIPT);
	$INPUTS[LESSONUNIT] = array('result'=>'plane','value'=>$LESSONUNIT);
	$INPUTS[DISPLAYPROBLEMNUM] = array('result'=>'plane','value'=>$display_problem_num);
	$INPUTS[VOICE] = array('result'=>'plane','value'=>$voice_button);
	$INPUTS[SUCCORFAIL] = array('result'=>'plane','value'=>$succorfail);
	$INPUTS[QUESTION] = array('result'=>'plane','value'=>$question);
	$INPUTS[PROBLEM] = array('result'=>'plane','value'=>$problem);
	$INPUTS[EXPLANATION] = array('result'=>'plane','value'=>$explanation);
	$INPUTS[ANSWERINFO] = array('result'=>'plane','value'=>$answer_info);
	$INPUTS[ANSWERCOLUMN] = array('result'=>'plane','value'=>$set_form);
	$INPUTS[FORMSTART] = array('result'=>'plane','value'=>$form_start);
	$INPUTS[FORMEND] = array('result'=>'plane','value'=>$form_end);
	$INPUTS[SENDQUESTION] = array('result'=>'plane','value'=>$send_question);
	$INPUTS[HINT] = array('result'=>'plane','value'=>$hint);
	$INPUTS[EXPLBUTTON] = array('result'=>'plane','value'=>$explanation_button);
	$INPUTS[SUBSPACE] = array('result'=>'plane','value'=>$sub_space);
	$INPUTS[FUNCTIONKEY] = array('result'=>'plane','value'=>$FUNCTIONKEY);
	$INPUTS[ERROR] = array('result'=>'plane','value'=>$errors);

	$make_html->set_rep_cmd($INPUTS);
	$html = $make_html->replace();
	if ($ONLOAD_BODY) { $html = ereg_replace("<body>",$ONLOAD_BODY,$html); }

	return $html;
}


/**
 * 正解表示
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @return string HTML
 */
function make_answer_html() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//ギブアップセット
	$_SESSION['record']['unit_comp'] = 1;
	$_SESSION['record']['giveup'] = 1;

	//	問題読み込み＆設定
	$sql  = "SELECT problem_num, display_problem_num, problem_type, sub_display_problem_num, form_type,".
			" question, problem, voice_data, hint, explanation,".
			" answer_time, parameter, set_difficulty, hint_number, correct_number,".
			" clear_number, first_problem, latter_problem, selection_words, correct,".
			" option1, option2, option3, option4, option5".
			" FROM ".T_PROBLEM.
			" WHERE problem_num='".$_SESSION[record][problem_num]."'".
			" AND course_num='".$_SESSION['course']['course_num']."'".
			" AND block_num='".$_SESSION['course']['block_num']."'".
			" AND display='1'".
			" AND state!='1'".
			" LIMIT 1;";
	if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
	if ($result = $cdb->query($sql)) {
		$PROBLEM_LIST = $list = $cdb->fetch_assoc($result);
		if ($PROBLEM_LIST) {
			foreach ($PROBLEM_LIST AS $key => $val) {
				$val = replace_decode($val);
				if ($val) { define("$key",$val); }
			}
		}
	}

	//	問題形式ファイル読み込み
	if (file_exists(LOG_DIR . "problem_lib/form_type_".form_type.".php")) {
		require_once(LOG_DIR . "problem_lib/form_type_" . form_type . ".php");
		require_once(LOG_DIR . "problem_lib/display_problem.php");
	}

	//	ログ記録
	if ($_SESSION['course']['review'] >= 2) {
		if (base_block_type == 2) {
			$class_m = 202020712;
		} elseif (base_block_type == 3) {
			$class_m = 202030712;
		}
	} else {
		if (base_block_type == 2) {
			$class_m = 102020712;
		} elseif (base_block_type == 3) {
			$class_m = 102030712;
		}
	}
	$INSERT_DATA['class_m'] = $class_m;
	$INSERT_DATA['student_id'] = $_SESSION["myid"]["id"];
//	$INSERT_DATA['area_num'] = $_SESSION['studentid']['area_num'];
//	$INSERT_DATA['enterprise_num'] = $_SESSION['studentid']['enterprise_num'];
//	$INSERT_DATA['school_num'] = $_SESSION['studentid']['school_num'];
//	$INSERT_DATA['cr_num'] = $_SESSION['pc']['classroom'];
//	$INSERT_DATA['seat_num'] = $_SESSION['pc']['seat'];
	$INSERT_DATA['course_num'] = $_SESSION['course']['course_num'];
	$INSERT_DATA['unit_num'] = $_SESSION['course']['unit_num'];
	$INSERT_DATA['block_num'] = $_SESSION['course']['block_num'];
	$INSERT_DATA['problem_num'] = $_SESSION['record']['problem_num'];
	$INSERT_DATA['display_problem_num'] = display_problem_num;
	$INSERT_DATA['regist_time'] = "now()";

	$ERROR = $cdb->insert(T_CHECK_STUDY_RECODE,$INSERT_DATA);
	if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::query-&gt;insert::T_CHECK_STUDY_RECODE"; }

	//	解説ページ作成
	$display_problem = new display_problem();
	$display_problem->set_success("1");
	$display_problem->set_unit_comp($_SESSION['record']['unit_comp']);
	$display_problem->set_again($_SESSION['record']['again']);
	$display_problem->set_correct();
	$display_problem_num = $display_problem->set_display_problem_num();
	$voice_button = $display_problem->set_voice_button();
	$question = $display_problem->set_question();
	$problem = $display_problem->set_problem();
	$explanation = $display_problem->set_explanation("giveup");
	$set_form = $display_problem->set_form();
	$form_start = $display_problem->form_start($set_form);
	$form_end = $display_problem->form_end($set_form);
	$send_question = $display_problem->send_question();
	$LESSONUNIT = stage_name . " " . lesson_name . " ". unit_name;

	//	サブスペース(area3)
	//	ギブアップ
	$sub_space = $display_problem->display_area3("giveup");

	// 画面サイズに合わせる
	$size_check = "check_size(1,".$_SESSION['course']['course_num'].");";

	$ONLOAD_BODY = "";
	if (form_type == 5) {
		$answer_info = $display_problem->set_answer_info(correct);
	} elseif (form_type == 8) {
		$MORE_SCRIPT = $display_problem->set_more_script();
		$ONLOAD_BODY = "<body onload=\"make_ele();".$size_check."\">";
		$answer_info = $display_problem->set_answer_info();
	} elseif (form_type == 3 || form_type == 4 || form_type == 10) {
		$tabindex = $display_problem->set_more_script();
		if ($tabindex > 0) {
			// edit simon 2014-11-14
			//$ONLOAD_BODY = "<body onload=\"".$size_check."document.getElementById('tabindex1').focus();\">";
			$ONLOAD_BODY = "<body onload=\"".$size_check."\">";
		} else {
			$ONLOAD_BODY = "";
		}
	} elseif (form_type == 11) {
		$MORE_SCRIPT = $display_problem->set_more_script();
		$size_check = "";
	} else {
		$MORE_SCRIPT = "";
		$ONLOAD_BODY = "";
	}

	if ($ONLOAD_BODY == "" && $size_check){
		$ONLOAD_BODY = "<body onLoad=\"".$size_check."\">";
	}

	//	html作成
	$make_html = new read_html();
	$make_html->set_dir(STUDENT_TEMP_DIR.course_num."/".stage_num."/");
	$make_html->set_file(STUDENT_TEMP_LESSON);
	$INPUTS[MORESCRIPT] = array('result'=>'plane','value'=>$MORE_SCRIPT);
	$INPUTS[LESSONUNIT] = array('result'=>'plane','value'=>$LESSONUNIT);
	$INPUTS[DISPLAYPROBLEMNUM] = array('result'=>'plane','value'=>$display_problem_num);
	$INPUTS[VOICE] = array('result'=>'plane','value'=>$voice_button);
	$INPUTS[SUCCORFAIL] = array('result'=>'plane','value'=>$succorfail);
	$INPUTS[QUESTION] = array('result'=>'plane','value'=>$question);
	$INPUTS[PROBLEM] = array('result'=>'plane','value'=>$problem);
	$INPUTS[EXPLANATION] = array('result'=>'plane','value'=>$explanation);
	$INPUTS[ANSWERINFO] = array('result'=>'plane','value'=>$answer_info);
	$INPUTS[ANSWERCOLUMN] = array('result'=>'plane','value'=>$set_form);
	$INPUTS[FORMSTART] = array('result'=>'plane','value'=>$form_start);
	$INPUTS[FORMEND] = array('result'=>'plane','value'=>$form_end);
	$INPUTS[SENDQUESTION] = array('result'=>'plane','value'=>$send_question);
	$INPUTS[HINT] = array('result'=>'plane','value'=>$hint);
	$INPUTS[EXPLBUTTON] = array('result'=>'plane','value'=>$explanation_button);
	$INPUTS[SUBSPACE] = array('result'=>'plane','value'=>$sub_space);

	$make_html->set_rep_cmd($INPUTS);
	$html = $make_html->replace();
	if ($ONLOAD_BODY) { $html = ereg_replace("<body>",$ONLOAD_BODY,$html); }

	//	サイズ変更処理
//	$size_check = "<body onLoad=\"check_size();\"";
//	$html = ereg_replace("<body",$size_check,$html);

	return $html;
}
?>