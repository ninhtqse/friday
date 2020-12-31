<?PHP
// 2017/07/21 yoshizawa 未使用のファイルです。

/**
 * ベンチャー・リンク　すらら
 *
 * 確認学習	診断テストA	block_type = 2
 *
 * ログ関係	エリアなど削除
 * 			テーブル名　T_CHECK_STUDY_RECODEへ変更
 * 			$_SESSION['studentid']['student_num']を$_SESSION['managerid']['manager_num']変更
 * 問題正解率変更コメント化
 * study_record_numをcheck_study_record_num
 * send_question() を　send_question_check()
 *
 * @@@ 新ＤＢ対応
 * 	$_SESSION['manager']['manager_num']を$_SESSION["myid"]["id"]に変更
 * 	カラム名の変更
 *
 * @author Azet
 */


/**
 * 判断して、HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @return string HTML
 */
function start() {

	if ($_SESSION['course']['review'] >= 2) {
		$class_m = 202020000;
	} else {
		$class_m = 102020000;
	}
	define("class_m","$class_m");

	if ($_POST['action'] == "check") {
		$ERROR = check_answer();
//		if ($ERROR && !$_SESSION['record']['answer']) { unset($_POST['action']); }	//	del ookawara 2009/08/10
		if ($ERROR && (!$_SESSION['record']['answer'] && $_SESSION['record']['answer'] != "0")) { unset($_POST['action']); }	//	add ookawara 2009/08/10
	}

	if ($_POST['action'] == "check") {	//	解答チェック
		$html = send_page_html();
	} elseif ($_POST['action'] == "answer") {	//	解答表示
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
 * @return string HTML
 */
function make_default_html($ERROR) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if ($ERROR) {
		$errors = ERROR($ERROR)."<br>\n";
		unset($ERROR);
	}

	//	前回解答問題番号セット
	if ($_SESSION['record']['problem_num']) {
		$last_problem_num = $_SESSION['record']['problem_num'];
	}

	//	解答ログリセット
	unset($_SESSION['record']);
	$_SESSION['record']['class_m'] = class_m;

	//	最終回答問題チェック
	$block_num = block_num;
	$sql  = "SELECT * FROM ".T_CHECK_STUDY_RECODE.
			" WHERE student_id='".$_SESSION["myid"]["id"]."'".
			" AND class_m='".$_SESSION['record']['class_m']."'".
			" AND course_num='".$_SESSION['course']['course_num']."'".
			" AND unit_num='".$_SESSION['course']['unit_num']."'".
			" AND block_num='".$block_num."'".
			" AND again>='1'".
//			" AND DATE(regist_time)=CURDATE()".
			" AND regist_time>'".$_SESSION['course']['finish_time']."'".
			" ORDER BY check_study_record_num DESC" .
			" LIMIT 1 ";
	if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$last_problem_num = $list['problem_num'];
		$last_again = $list['again'];
		$last_success = $list['success'];
	}

	//	最終学習問題表示番号取得
	$sql  = "SELECT display_problem_num, clear_number FROM ".T_PROBLEM.
			" WHERE problem_num='".$last_problem_num."'".
			" AND course_num='".$_SESSION['course']['course_num']."'".
			" AND block_num='".$block_num."'".
			" AND display='1'".
			" AND state!='1'".
			" LIMIT 1;";
	if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$last_display_problem_num = $list['display_problem_num'];
		$last_clear_number = $list['clear_number'];
	}

	unset($problem_num);
	//	最終解答問題終了チェック
	if ($last_success != 1 && $last_again < $last_clear_number) {	//	復活
		$_SESSION['record']['again'] = $last_again;
		$problem_num = $last_problem_num;
	}

	//	問題番号決定
	//	問題情報取得
	if (!$problem_num) {	//	次の問題に移行
		$sql  = "SELECT problem_num, display_problem_num, problem_type, sub_display_problem_num, form_type,".
				" question, problem, voice_data, hint, explanation,".
				" answer_time, parameter, set_difficulty, hint_number, correct_number,".
				" clear_number, first_problem, latter_problem, selection_words, correct,".
				" option1, option2, option3, option4, option5".
				" FROM ".T_PROBLEM.
				" WHERE course_num='".$_SESSION['course']['course_num']."'".
				" AND block_num='".$block_num."'".
				" AND display_problem_num>'".$last_display_problem_num."'".
				" AND problem_type='1'".
				" AND display='1'".
				" AND state!='1'".
				" ORDER BY display_problem_num".
				" LIMIT 1;";
		if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
		if ($result = $cdb->query($sql)) {
			$PROBLEM_LIST = $list = $cdb->fetch_assoc($result);
			$problem_num = $list['problem_num'];
		}
	} else {
		$sql  = "SELECT problem_num, display_problem_num, problem_type, sub_display_problem_num, form_type,".
				" question, problem, voice_data, hint, explanation,".
				" answer_time, parameter, set_difficulty, hint_number, correct_number,".
				" clear_number, first_problem, latter_problem, selection_words, correct,".
				" option1, option2, option3, option4, option5".
				" FROM ".T_PROBLEM.
				" WHERE course_num='".$_SESSION['course']['course_num']."'".
				" AND block_num='".$block_num."'".
				" AND display_problem_num='".$last_display_problem_num."'".
				" AND problem_type='1'".
				" AND display='1'".
				" AND state!='1'".
				" ORDER BY display_problem_num".
				" LIMIT 1;";
		if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
		if ($result = $cdb->query($sql)) {
			$PROBLEM_LIST = $list = $cdb->fetch_assoc($result);
		}
	}

	//	最終問題が終了していたら（問題が設定されなかったら。）診断結果へ
	if ($last_display_problem_num && !$problem_num) {
		$STUDENT_RESULT_FILE = STUDENT_RESULT_FILE;
		header ("Location: $STUDENT_RESULT_FILE\n\n");
		exit;
	}

	//	動作確認用表示問題番号設定
//	$check_display_problem_num = 2;
	if ($check_display_problem_num) {
		$block_num = block_num;
		$sql  = "SELECT problem_num, display_problem_num, problem_type, sub_display_problem_num, form_type,".
				" question, problem, voice_data, hint, explanation,".
				" answer_time, parameter, set_difficulty, hint_number, correct_number,".
				" clear_number, first_problem, latter_problem, selection_words, correct,".
				" option1, option2, option3, option4, option5".
				" FROM ".T_PROBLEM.
				" WHERE course_num='".$_SESSION['course']['course_num']."'".
				" AND block_num='".$block_num."'".
				" AND display_problem_num='".$check_display_problem_num."'".
				" AND display='1'".
				" AND state!='1'".
				" LIMIT 1;";
		if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
		if ($result = $cdb->query($sql)) {
			$PROBLEM_LIST = $list = $cdb->fetch_assoc($result);
			$problem_num = $list['problem_num'];
		}
	}
	$_SESSION['record']['problem_num'] = $problem_num;

	//	問題設定
	if (!$ERROR && $PROBLEM_LIST) {
		foreach ($PROBLEM_LIST AS $key => $val) {
			$val = replace_decode($val);
			if ($val !== "" && $val !== NULL) { define($key,$val); }
		}
	}

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
	if (hint_number == 99) {
		$hint = $display_problem->hint_check();
	}

	//	サブスペース(area3)
	$sub_space = $display_problem->display_area3("start");

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

	if ($ONLOAD_BODY == "" && $size_check){
		$ONLOAD_BODY = "<body onLoad=\"".$size_check."\">";
	}

	//	ログ情報登録
	$INSERT_DATA['class_m'] = $_SESSION['record']['class_m'];
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
	$INPUTS[SUBSPACE] = array('result'=>'plane','value'=>$sub_space);
	$INPUTS[FUNCTIONKEY] = array('result'=>'plane','value'=>$FUNCTIONKEY);
	$INPUTS[ERROR] = array('result'=>'plane','value'=>$errors);

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

	if (!$ERROR && ($_POST['answer'] ||  array_search("0",$_POST['answer']) !== FALSE)) {	//	add oz 2009/08/13
		$_SESSION['record']['again']++;
	}

	//	問題読み込み＆設定
	if ($PROBLEM_LIST && $_SESSION['record']['again']) {
		foreach ($PROBLEM_LIST AS $key => $val) {
			$val = replace_decode($val);
			if ($val !== "" && $val !== NULL) { define($key,$val); }
		}
	}
	if ($ERROR) { return $ERROR; }

	if (file_exists(LOG_DIR . "problem_lib/form_type_".form_type.".php")) {
		require_once(LOG_DIR . "problem_lib/form_type_" . form_type . ".php");
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

	//	ログ保存
	$INSERT_DATA['class_m'] = $_SESSION['record']['class_m'];
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
	if ($ERROR) { return $ERROR; }

	if ($_SESSION['record']['again'] == 1 && $_SESSION['course']['review'] != 1) {
		//	問題正解率
		//	問題番号：$_SESSION['record']['problem_num']
//		$ERROR = auto_difficulty($_SESSION['record']['problem_num']);
//		$ERROR = auto_difficulty2($_SESSION['record']['problem_num'],$_SESSION['record']['success']);
	}
	if ($ERROR) { return $ERROR; }

	//	最終問題クリアーチェック
	$block_num = block_num;
	$display_problem_num = display_problem_num;
	//	基本問題の最後の問題番号取得
	$sql  = "SELECT display_problem_num FROM ".T_PROBLEM.
			" WHERE course_num='".$_SESSION['course']['course_num']."'".
			" AND block_num='".$block_num."'".
			" AND problem_type='1'".
			" AND display='1'".
			" AND state!='1'".
			" ORDER BY display_problem_num DESC".
			" LIMIT 1;";
	if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$last_display_problem_num = $list['display_problem_num'];
	}
	//	基本問題の最後を取得、問題番号と一致していたら終了
	if ($display_problem_num == $last_display_problem_num) { $_SESSION['record']['unit_comp'] = 1; }

	return $ERROR;
}



/**
 * 診断問題解答後処理
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @return string HTML
 */
function send_page_html() {

	$_SESSION["dsp_idx"]=0;

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
		//	add oz 2009/09/04
		if ($_SESSION['record']['unit_comp'] == 1) {
			$type = "block_clear";
		} else {
			$type = "true";
		}

		$display_problem = new display_problem();
		$display_problem->set_success($_SESSION['record']['success']);
		$display_problem->set_unit_comp($_SESSION['record']['unit_comp']);
		$display_problem->set_again($_SESSION['record']['again']);
		$display_problem->set_answers($_SESSION['record']['answer']);
		$display_problem_num = $display_problem->set_display_problem_num();
		$voice_button = $display_problem->set_voice_button();
		$question = $display_problem->set_question();
		$problem = $display_problem->set_problem();
		$explanation = $display_problem->set_explanation($type);
		$answer_info = $display_problem->set_answer_info($_SESSION['record']['answer_info']);
		$set_form = $display_problem->set_form();
		$form_start = $display_problem->form_start($set_form);
		$form_end = $display_problem->form_end($set_form);
		$send_question = $display_problem->send_question_check();	//	コメント解除	change ookawara 2010/01/07
		$LESSONUNIT = stage_name . " " . lesson_name . " ". unit_name;
		$succorfail = "<p style=\"font-weight : 900; color:#ff0000;\">正解</p>\n";

		//	サブスペース(area3)
		if ($_SESSION['record']['unit_comp'] == 1) {
			$sub_space = $display_problem->display_area3("block_clear");
		} else {
			$sub_space = $display_problem->display_area3("true");
		}

		$display_timerstartup = 1;

	} else {	//	不正解
		if ($_SESSION['record']['again'] >= correct_number) { $success = 1; }
		else { $success = $_SESSION['record']['success']; }
		$display_problem = new display_problem();
		$display_problem->set_success($success);
		$display_problem->set_answers($_SESSION['record']['answer']);
		$display_problem->set_again($_SESSION['record']['again']);
		$display_problem_num = $display_problem->set_display_problem_num();
		$voice_button = $display_problem->set_voice_button();
		$question = $display_problem->set_question();
		$problem = $display_problem->set_problem();
		$answer_info = $display_problem->set_answer_info($_SESSION['record']['answer_info']);
		$set_form = $display_problem->set_form();
		$form_start = $display_problem->form_start($set_form);
		$form_end = $display_problem->form_end($set_form);
		$send_question = $display_problem->send_question_check();	//	コメント解除	change ookawara 2010/01/07
		$LESSONUNIT = stage_name . " " . lesson_name . " ". unit_name;
		$succorfail = "<p style=\"font-weight : 900; color:#0033ff;\">不正解</p>\n";

		//	解答を見るボタン	サブ問題時
		if (correct_number > 0 && $_SESSION['record']['again'] >= correct_number) {
			$explanation_button = $display_problem->explanation_button();
			unset($form_end);

			$form_end = "<input type=\"hidden\" name=\"dsp_idx\" value=\"".$_SESSION["dsp_idx"]."\">\n";
			$form_end.= "</form>\n";

			$display_timerstartup = 1;
		}

		//	サブスペース(area3)
		$sub_space = $display_problem->display_area3("false");
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
		$list = $cdb->fetch_assoc($result);
		if ($list) {
			foreach ($list AS $key => $val) {
//				if ($key == "block_num" || $key == "course_num") { continue; }
				$val = replace_decode($val);
				if ($val !== "" && $val !== NULL) { define($key,$val); }
			}
		}
	}

	if (file_exists(LOG_DIR . "problem_lib/form_type_".form_type.".php")) {
		require_once(LOG_DIR . "problem_lib/form_type_" . form_type . ".php");
		require_once(LOG_DIR . "problem_lib/display_problem.php");
	}

	//	ログ記録
	if ($_SESSION['course']['review'] >= 2) {
		$class_m = 202020012;
	} else {
		$class_m = 102020012;
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

	//	add oz 2009/09/04
	if ($_SESSION['record']['giveup'] == 1) {	//	ギブアップなら
		$type = "giveup";
	} else {
		$type = "start";
	}

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
	$explanation = $display_problem->set_explanation($type);
	$set_form = $display_problem->set_form();
	$form_start = $display_problem->form_start($set_form);
	$form_end = $display_problem->form_end($set_form);
	$send_question = $display_problem->send_question_check();
	$LESSONUNIT = stage_name . " " . lesson_name . " ". unit_name;

	//	サブスペース(area3)
	$sub_space = $display_problem->display_area3("start");

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

	//	ユニットクリアーならば
	if ($_SESSION['record']['unit_comp'] == 1) {
		$sub_space = $display_problem->display_area3("block_clear");
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

	$make_html->set_rep_cmd($INPUTS);
	$html = $make_html->replace();
	if ($ONLOAD_BODY) { $html = ereg_replace("<body>",$ONLOAD_BODY,$html); }

	return $html;
}
?>