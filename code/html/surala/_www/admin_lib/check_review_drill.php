<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * 診断復習ドリル表示
 *
 * ログ関係	エリアなど削除
 * テーブル名　T_CHECK_STUDY_RECODEへ変更
 * $_SESSION['managerid']['manager_num']を$_SESSION['managerid']['manager_num']変更
 * 問題正解率変更コメント化
 * study_record_numをcheck_study_record_num
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
 * defineの設定する機能
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
	$base_block_type = $_SESSION['course']['block_type'];
	unset($_SESSION['course']['block_type']);

	if ($_SESSION['course']['review_unit_num']) {
		$review_skill_num = $_SESSION['course']['review_unit_num'];
	} elseif ($_POST['skill_num']) {
		$review_skill_num = $_POST['skill_num'];
	}

	//	元block_type
	if(!$base_block_type) {
		$sql  = "SELECT block_type FROM ".T_BLOCK.
				" WHERE block_num='".$block_num."'".
				" AND course_num='".$course_num."'".
				" AND unit_num='".$unit_num."'".
				" LIMIT 1;";
		if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			define("base_block_type",$list['block_type']);
		}
	} else {
		define("base_block_type",$base_block_type);
	}

	//	横・縦書き設定
	$sql  = "SELECT write_type, math_align FROM ".T_COURSE.	//	add , math_align ookawara 2012/08/28
			" WHERE course_num='".$course_num."'".
			" LIMIT 1;";
	if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		define("write_type",$list['write_type']);
		define("math_align",$list['math_align']);	//	add ookawara 2012/08/28
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

	//	復習スキル取得
	if ($_SESSION['course']['review'] >= 2) {
		if (base_block_type == 2) {
			$class_m = 202020300;
		} elseif (base_block_type == 3) {
			$class_m = 202030300;
		}
	} else {
		if (base_block_type == 2) {
			$class_m = 102020300;
		} elseif (base_block_type == 3) {
			$class_m = 102030300;
		}
	}

//	regist_time
	$sql  = " SELECT block_num, review_unit_num FROM ".T_CHECK_STUDY_RECODE.
			" WHERE student_id='".$student_num."'".
			" AND class_m='".$class_m."'".
			" AND course_num='".$course_num."'".
			" AND block_num='".$block_num."'".
//			" AND DATE(regist_time)=CURDATE()".
			" AND regist_time>'".$_SESSION['course']['finish_time']."'".
			" ORDER BY check_study_record_num DESC".
			" LIMIT 1;";
//			" AND DATE_FORMAT(regist_time,'%Y-%m-%d')=DATE_FORMAT(now(),'%Y-%m-%d')".
	if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$block_num = $list[block_num];
		$skill_num = $list[review_unit_num];
	}
	define("skill_num",$skill_num);

	//	ブロック情報取得
	if ($_SESSION['course']['block_num']) { $block_num = $_SESSION['course']['block_num']; }
	define("block_num",$block_num);

	//	診断復習設定情報取得
	$sql  = "SELECT * FROM ".T_REVIEW_SETTING.
			" WHERE course_num='".$course_num."'".
			" AND block_num='".$block_num."'" .
			" AND skill_num='".$skill_num."'".
			" AND display='1'" .
			" AND state!='1'" .
			" LIMIT 1;";
	if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		foreach ($list AS $key => $val) {
			if ($val) { define("$key",$val); }
		}
	}

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

	//	有効問題有無チェック
	unset($count);
	$flag = 0;
	$sql  = "SELECT count(problem_num) AS count FROM ".T_PROBLEM.
			" WHERE course_num='".$course_num."'".
			" AND block_num='".$block_num."'".
			" AND problem_type='3'".
			" AND parameter like '%[".$skill_num."]%'".
			" AND display='1'".
			" AND state!='1';";
	if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$count = $list['count'];
	}
	$lowest_study_number = lowest_study_number;
	if ($count >= $lowest_study_number) { $flag = 1; }
	if ($flag != 1) {
		unset($_SESSION['course']['block_type']);
//		header("Location: ./check_unit_comp.php");	//	del ookawara 2011/08/21
		header("Location: check_review_drill_comp.php");	//	add ookawara 2011/08/21

		exit;
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

	if ($_SESSION['course']['review'] >= 2) {
		if (base_block_type == 2) {
			$class_m = 202020400;
		} elseif (base_block_type == 3) {
			$class_m = 202030400;
		}
	} else {
		if (base_block_type == 2) {
			$class_m = 102020400;
		} elseif (base_block_type == 3) {
			$class_m = 102030400;
		}
	}
	define("class_m","$class_m");

	if ($_POST['action'] == "check") {
		$ERROR = check_answer();
//		if ($ERROR && !$_SESSION['record']['answer']) { unset($_POST['action']); }	//	del ookawara 2009/08/10
		if ($ERROR && (!$_SESSION['record']['answer'] && $_SESSION['record']['answer'] != "0")) { unset($_POST['action']); }	//	add ookawara 2009/08/10
	}

	if ($_POST['action'] == "check") {	//	解答チェック
		$html = make_check_html($ERROR);
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

	// 生徒固有情報を取得
	$sql = "select * from ".T_STUDENT_PARA;
	$sql.= " where student_id='".$_SESSION["myid"]["id"]."'";
	if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
	if ($result = $cdb->query($sql)) {
		if ($cdb->num_rows($result)){
			$list = $cdb->fetch_assoc($result);
		} else {
			$ERROR[] = "生徒固有情報がありません。";
		}
	}

	if ($list["study_block_num"]==block_num){
		$study_block_num = $list["study_block_num"];
	}else{
		$study_block_num = 0;
	}

	if ($study_block_num){
		// 学習中（２問目以降）
		$pool_check = 0;
		$pool_num = $list["pool_size"];
		$POOL_PRB_NUM = explode(",",$list["pool"]);
		$SET_PRO_NUM = $POOL_PRB_NUM;
		$pool_up_num = count($POOL_PRB_NUM);
	}else{
		// ブロック開始時（１問目）
		$pool_check = 1;
		$pool_num = pool;
		$pool_up_num = 0;
		$POOL_PRB_NUM = array();
		$SET_PRO_NUM = array();

		$UPD_DATA["study_block_num"] = block_num;
		$UPD_DATA["pool_size"] = pool;
		$UPD_DATA["pool"] = "";
		$UPD_DATA["clear"] = "";
		$where = " WHERE student_id='".$_SESSION["myid"]["id"]."'";

		$ERROR = $cdb->update(T_STUDENT_PARA,$UPD_DATA,$where);
	}

	//	登録されている数より、プールサイズが大きければ追加
	if ($pool_up_num < $pool_num) {
		$clear_str = "";
		if (!$pool_check) {
			$CLEAR_PRB_NUM = explode(",",$list["clear"]);

			foreach($CLEAR_PRB_NUM as $val){
				if (!in_array($val,$SET_PRO_NUM)){
					$SET_PRO_NUM[] = $val;
				}
			}
		}
		//	ユニット内問題抽出
		//	問題難易度選択
		$new_prb_check = 0;
		$block_num = block_num;

		if (difficulty_type == 1) { $difficulty = "auto_difficulty"; } else { $difficulty = "set_difficulty"; }
		$sql  = "SELECT problem_num, display_problem_num, set_difficulty FROM ".T_PROBLEM.
				" WHERE course_num='".$_SESSION['course']['course_num']."'".
				" AND block_num='".$block_num."'".
				" AND problem_type='3'".
				" AND parameter like '%[".skill_num."]%'".
				" AND display='1'".
				" AND state!='1'".
				" ORDER BY ".$difficulty.";";
		if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
		if ($result = $cdb->query($sql)) {
			while ($list = $cdb->fetch_assoc($result)) {

				if (!in_array($list["problem_num"],$SET_PRO_NUM)){
					if (!in_array($list["problem_num"],$POOL_PRB_NUM)){
						$POOL_PRB_NUM[] = $list["problem_num"];
					}
				}
				if (count($POOL_PRB_NUM) >= $pool_num) { break; }
			}
			$cdb->free_result($result);
		}

		//	問題が足りなくてもクリアーした問題を持ってこないようにする。
		if (count($POOL_PRB_NUM) <= 3 && count($CLEAR_PRB_NUM)) {
			srand((double)microtime()*10000000);
			$key = array_rand($CLEAR_PRB_NUM);

			if (!in_array($CLEAR_PRB_NUM[$key],$POOL_PRB_NUM)){
				$POOL_PRB_NUM[] = $CLEAR_PRB_NUM[$key];
			}
			$tmp_str = "";
			foreach($CLEAR_PRB_NUM as $val){
				if (strcmp($CLEAR_PRB_NUM[$key],$val)){
					if ($tmp_str)	$tmp_str .= ",";
					$tmp_str .= $val;
				}
			}
			$clear_str = $tmp_str;
		}

		// プールデータを更新
		unset($UPD_DATA);
		$UPD_DATA["pool"] = implode(",", $POOL_PRB_NUM);
		if ($clear_str){
			$UPD_DATA["clear"] = $clear_str;
		}
		$where = " WHERE student_id='".$_SESSION["myid"]["id"]."'";

		$ERROR = $cdb->update(T_STUDENT_PARA,$UPD_DATA,$where);
	}

	//	問題番号設定
	if ($POOL_PRB_NUM) {
		$count_pool_prb_num = count($POOL_PRB_NUM);
		srand((double)microtime()*10000000);
		if ($last_problem_num > 0 && $count_pool_prb_num > 1) {
			$rstAry = array_rand($POOL_PRB_NUM,$count_pool_prb_num);
			foreach ($rstAry AS $val) {
				if ($last_problem_num != $POOL_PRB_NUM[$val]) {
					$problem_num = $POOL_PRB_NUM[$val];
					break;
				}
			}
		} else {
			$key = array_rand($POOL_PRB_NUM);
			$problem_num = $POOL_PRB_NUM[$key];
		}
	} else {
		$ERROR[] = "学習できる問題がありません。";
	}

	//	動作確認用表示問題番号設定
//	$check_display_problem_num = 2;
	if ($check_display_problem_num) {
		$block_num = block_num;
		$sql  = "SELECT problem_num FROM ".T_PROBLEM.
				" WHERE course_num='".$_SESSION['course']['course_num']."'".
				" AND block_num='".$block_num."'" .
				" AND display_problem_num='".$check_display_problem_num."'".
				" AND display='1'".
				" AND state!='1'".
				" LIMIT 1;";
		if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$problem_num = $list['problem_num'];
		}
	}
	$_SESSION['record']['problem_num'] = $problem_num;

	//	問題読み込み＆設定
	if (!$ERROR) {
		$sql  = "SELECT problem_num, display_problem_num, problem_type, sub_display_problem_num, form_type,".
				" question, problem, voice_data, hint, explanation,".
				" answer_time, parameter, set_difficulty, hint_number, correct_number,".
				" clear_number, first_problem, latter_problem, selection_words, correct,".
				" option1, option2, option3, option4, option5".
				" FROM ".T_PROBLEM.
				" WHERE problem_num='$problem_num'".
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
	}

	//	問題形式ファイル読み込み
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
		$ONLOAD_BODY = "<body onload=\"TimerStartUp()\">";
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
	$INSERT_DATA['review_unit_num'] = skill_num;
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
 * @return array エラーがならば
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
//		if ($_POST['answer']) {
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
			if ($val) { define("$key",$val); }
		}
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
			if (preg_match("/^t::/",$LIST_ANSWER[$KEY])) {
				$tf = "t";
				$correct = preg_replace("/^t::/","",$LIST_ANSWER[$KEY]);
			} else {
				$tf = "f";
				$correct = preg_replace("/^f::/","",$LIST_ANSWER[$KEY]);
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

	//	プールサイズ&問題クリアーチェック&ユニットクリアー
	$passing = 0;
	$unit_comp = 0;
//	if ($_SESSION['record']['again'] == 1) {	//	del ookawara 2011/08/11
	if (correct_number <= $_SESSION['record']['again'] || $_SESSION['record']['success'] == 1) {	//	add ookawara 2011/08/11
/*
		//	del ookawara 2011/08/11
		//	直近の生徒の正解率
		$pool_rate_number = pool_rate_number;
		$clear_rate_number = clear_rate_number;
		if (pool_rate_number > clear_rate_number) {
			$big_number = pool_rate_number - 1;
		} else {
			$big_number = clear_rate_number - 1;
		}
*/
		//	add ookawara 2011/08/11
		$pool_rate_number = pool_rate_number;
		$clear_rate_number = clear_rate_number;
		$clear_number = clear_number;

		$student_par = 0;
		$pool_true_num = $_SESSION['record']['success'];
		$clear_true_num = $_SESSION['record']['success'];
		$total_num = 1;
		$pool_mrate = 0;
		$clear_mrate = 0;
		$passing_check = 0;	//	add ookawara 2011/08/11
//		$sql  = "SELECT success FROM ".T_CHECK_STUDY_RECODE.	//	del ookawara 2011/08/11
		$sql  = "SELECT problem_num, success, again FROM ".T_CHECK_STUDY_RECODE.	//	add ookawara 2011/08/11
				" WHERE student_id='".$_SESSION["myid"]["id"]."'" .
				" AND class_m='".$_SESSION['record']['class_m']."'".
				" AND course_num='".$_SESSION['course']['course_num']."'".
				" AND unit_num='".$_SESSION['course']['unit_num']."'".
				" AND block_num='".$_SESSION['course']['block_num']."'" .
//				" AND again='1'".	//	del ookawara 2011/08/11
				" AND again>'0'".	//	add ookawara 2011/08/11
//				" AND DATE(regist_time)=CURDATE()".
				" AND regist_time>'".$_SESSION['course']['finish_time']."'".
				" ORDER BY check_study_record_num DESC".
				";";	//	add ookawara 2011/08/11
//				" LIMIT $big_number;";	//	del ookawara 2011/08/11
//				" AND DATE_FORMAT(regist_time,'%Y-%m-%d')=DATE_FORMAT(now(),'%Y-%m-%d')" .
		if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
		if ($result = $cdb->query($sql)) {
			$last_problem_num = $_SESSION['record']['problem_num'];	//	add ookawara 2011/08/11
			$last_again = $_SESSION['record']['again'];	//	add ookawara 2011/08/11
			$check_flg = 0;	//	add ookawara 2011/08/11
			$pool_rate_flg = 0;	//	add ookawara 2011/08/11
			$clear_rate_flg = 0;	//	add ookawara 2011/08/11
			$clear_flg = 0;	//	add ookawara 2011/08/11

			while ($list = $cdb->fetch_assoc($result)) {

				//	add ookawara 2011/08/11	start
				$problem_num = $list['problem_num'];
				$success = $list['success'];
				$again = $list['again'];

				//	解答数＆正解数
				if ($last_problem_num != $problem_num) {
					$check_flg = 1;
				} elseif ($last_again < $again) {	//	連続して同じ問題が出題された場合
					$check_flg = 1;
				} elseif ($last_again == 1 && $again == 1) {	//	連続して同じ問題で、解答回数が1回の場合
					$check_flg = 1;
				}

				if ($check_flg == 1) {
					//	記録前に集計している事を忘れずに！
					if ($pool_rate_number > $total_num) {
						$pool_true_num += $list['success'];
					} else {
						$pool_rate_flg = 1;
					}
					if ($clear_rate_number > $total_num) {
						$clear_true_num += $list['success'];
					} else {
						$clear_rate_flg = 1;
					}
					if ($clear_number > $total_num) {
						$passing_check += $list['success'];
					} else {
						$clear_flg = 1;
					}
					$total_num++;
					$check_flg = 0;
				}

				$last_problem_num = $problem_num;
				$last_again = $again;

				//	必要数チェックした場合集計終了
				if ($pool_rate_flg == 1 && $clear_rate_flg == 1 && $clear_flg == 1) {
					break;
				}
				//	add ookawara 2011/08/11	end

//				if (pool_rate_number > $total_num) { $pool_true_num += $list['success']; }	//	del ookawara 2011/08/11
//				if (clear_rate_number > $total_num) { $clear_true_num += $list['success']; }	//	del ookawara 2011/08/11
//				++$total_num;	//	del ookawara 2011/08/11
			}
		}
		//	プール正解率
		if ($pool_true_num && $total_num) {
			if ($total_num < pool_rate_number) {
				$pool_rate_number = $total_num;
			} else {
				$pool_rate_number = pool_rate_number;
			}
			$pool_mrate = ($pool_true_num / $pool_rate_number) * 100;
		}
		//	ドリルクリア正解率
		if ($clear_true_num && $total_num) {
			if ($total_num < clear_rate_number) {
				$clear_rate_number = $total_num;
			} else {
				$clear_rate_number = clear_rate_number;
			}
			$clear_mrate = ($clear_true_num / $clear_rate_number) * 100;
		}

		$pool_num = 0;
		$pool_str = "";
		$clear_str = "";
		$sql = "select * from ".T_STUDENT_PARA;
		$sql.= " where student_id='".$_SESSION["myid"]["id"]."'";
		if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
		if ($result = $cdb->query($sql)) {
			if ($cdb->num_rows($result)){
				$list = $cdb->fetch_assoc($result);
				$pool_num = $list["pool_size"];
				$pool_str = $list["pool"];
				$clear_str = $list["clear"];
			}
		}

		$pool_check = 0;
		if ($_SESSION['record']['success'] == 1) {
			//	プールサイズ変更 増
			if ($pool_mrate > rate) {
				$pool_num++;
				if (max_pool >= $pool_num) { $pool_check = 1; }
			}

			//	正解しているので+1する。最新ログは記録していない為
			$passing_check += 1;	//	add ookawara 2011/08/11

			//	規定回数クリアーしていたらプールフォルダーから抜き出し
			if ($passing_check >= $clear_number) {
				$passing = 1;

				// クリアデータへ追加
				$w_ary = explode(",",$clear_str);
				if (!in_array($_SESSION['record']['problem_num'],$w_ary)){
					if ($clear_str)		$clear_str .= ",";
					$clear_str .= $_SESSION['record']['problem_num'];
				}

				// プールデータ削除
				$w_ary = explode(",",$pool_str);
				$tmp_str = "";
				foreach($w_ary as $val){
					if (strcmp($_SESSION['record']['problem_num'],$val)){
						if ($tmp_str)	$tmp_str .= ",";
						$tmp_str .= $val;
					}
				}
				$pool_str = $tmp_str;
			}
		} else {
			//	プールサイズ変更 減
			if ($pool_mrate < rate) {
				$pool_num--;
				if (min_pool <= $pool_num) { $pool_check = 1; }
			}
		}

		//	プールサイズとプールとクリアデータを更新
		if ($pool_check == 1) {
			$UPD_DATA["pool_size"] = $pool_num;
		}
		$UPD_DATA["pool"] = $pool_str;
		$UPD_DATA["clear"] = $clear_str;
		$where = " WHERE student_id='".$_SESSION["myid"]["id"]."'";

		$ERROR = $cdb->update(T_STUDENT_PARA,$UPD_DATA,$where);
	}
	if ($ERROR) { return $ERROR; }

	//	ログ記録
	unset($INSERT_DATA);
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
	$INSERT_DATA['review_unit_num'] = skill_num;
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

	//ドリルクリアーチェック
//	if ($_SESSION['record']['again'] == 1) {	//	del ookawara 2011/08/11
	if (correct_number <= $_SESSION['record']['again'] || $_SESSION['record']['success'] == 1) {	//	add ookawara 2011/08/11
//		$sql  = "SELECT count( DISTINCT problem_num) AS lowest_study_number FROM ".T_CHECK_STUDY_RECODE.	//	del ookawara 2011/08/17
		$sql  = "SELECT count( problem_num ) AS lowest_study_number FROM ".T_CHECK_STUDY_RECODE.	//	add ookawara 2011/08/17
				" WHERE student_id='".$_SESSION["myid"]["id"]."'" .
				" AND class_m='".$_SESSION['record']['class_m']."'".
				" AND course_num='".$_SESSION['course']['course_num']."'".
				" AND unit_num='".$_SESSION['course']['unit_num']."'".
				" AND block_num='".$_SESSION['course']['block_num']."'".
				" AND review_unit_num='".skill_num."'" .
//				" AND passing='1'".	//	del ookawara 2011/08/17
				" AND again='1'".	//	add ookawara 2011/08/17
//				" AND DATE(regist_time)=CURDATE();";
				" AND regist_time>'".$_SESSION['course']['finish_time']."';";
//				" AND DATE_FORMAT(regist_time,'%Y-%m-%d')=DATE_FORMAT(now(),'%Y-%m-%d')" .
		if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$lowest_study_number = $list['lowest_study_number'];
		}

		if (clear_rate < $clear_mrate && lowest_study_number <= $lowest_study_number) {
			$_SESSION['record']['unit_comp'] = 1;
		}
	}
	if ($ERROR) { return $ERROR; }
/*
	//	問題正解率
	if ($_SESSION['course']['review'] != 1) {
		//	問題番号：$_SESSION['record']['problem_num']
//		$ERROR = auto_difficulty($_SESSION['record']['problem_num']);
		$ERROR = auto_difficulty2($_SESSION['record']['problem_num'],$_SESSION['record']['success']);
	}
*/

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
		$send_question = $display_problem->send_question_check();
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
		$display_problem = new display_problem();
		$display_problem->set_success($_SESSION['record']['success']);
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
		$send_question = $display_problem->send_question_check();
		$LESSONUNIT = stage_name . " " . lesson_name . " ". unit_name;
		$succorfail = "<p style=\"font-weight : 900; color:#0033ff;\">不正解</p>\n";

		//	ヒント表示
		if (hint_number > 0 && $_SESSION['record']['again'] >= hint_number && $_SESSION['record']['again'] < correct_number) {
			$hint = $display_problem->hint_check();
		}

		//	解答を見るボタン
		if (correct_number > 0 && $_SESSION['record']['again'] >= correct_number) {
			$explanation_button = $display_problem->explanation_button();
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

	//	ギブアップチェック
	review_giveup_check();

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

	if (file_exists(LOG_DIR . "problem_lib/form_type_".form_type.".php")) {
		require_once(LOG_DIR . "problem_lib/form_type_" . form_type . ".php");
		require_once(LOG_DIR . "problem_lib/display_problem.php");
	}

	//	ログ記録
	if ($_SESSION['course']['review'] >= 2) {
		if (base_block_type == 2) {
			$class_m = 202020412;
		} elseif (base_block_type == 3) {
			$class_m = 202030412;
		}
	} else {
		if (base_block_type == 2) {
			$class_m = 102020412;
		} elseif (base_block_type == 3) {
			$class_m = 102030412;
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
	$INSERT_DATA['review_unit_num'] = skill_num;
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
	if ($_SESSION['record']['giveup'] == 1) {	//	ギブアップなら
		$sub_space = $display_problem->display_area3("giveup");
	} else {
		$sub_space = $display_problem->display_area3("start");
	}

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
//	if ($_SESSION['record']['unit_comp'] == 1) {
//		$sub_space = $display_problem->display_area3("block_clear");
//	}

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


/**
 * ギブアップチェック
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * SESSIONに設定する機能
 * @author Azet
 */
function review_giveup_check() {

	if (!defined('giveup_time') && !defined('giveup_ans_num')) {
		if (giveup_time < 1 && giveup_ans_num < 1) {
			return;
		}
	}

	//	ログデーター取得
	//	学習当日かつ最後に学習クリアーした時間以降のログをチェックする。
	$check_giveup_time = 0;
	$check_giveup_ans_num = 0;
	$finish_giveup_ans_num = 0;
	$sql  = "SELECT display_problem_num, answer_time FROM ".T_CHECK_STUDY_RECODE.
			" WHERE student_id='".$_SESSION["myid"]["id"]."'".
			" AND class_m='".class_m."'".
			" AND course_num='".$_SESSION['course']['course_num']."'".
			" AND unit_num='".$_SESSION['course']['unit_num']."'".
			" AND block_num='".$_SESSION['course']['block_num']."'".
			" AND review_unit_num='".skill_num."'" .
			" AND answer_time>'0'".
//			" AND DATE(regist_time)=CURDATE()".
			" AND regist_time>'".$_SESSION['course']['finish_time']."'".
			" ORDER BY regist_time;";
	if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$display_problem_num_ = $list['display_problem_num'];
			$answer_time_ = $list['answer_time'];
			if ($finish_giveup_ans_num != $display_problem_num_) {
				$check_giveup_ans_num++;
			}
			$check_giveup_time += $answer_time_;
			$finish_giveup_ans_num = $display_problem_num_;
		}
	}

	$giveup = 0;
	//	ギブアップ時間チェック
	if (defined('giveup_time') && giveup_time > 0) {
		$giveup_time_sec = giveup_time * 60;
		if ($giveup_time_sec <= $check_giveup_time) { $giveup = 1; }
	}
	//	ギブアップ解答回数チェック
	if (defined('giveup_ans_num') && giveup_ans_num > 0) {
		if (giveup_ans_num <= $check_giveup_ans_num) { $giveup = 1; }
	}

	if ($giveup == 1) {
		$_SESSION['record']['unit_comp'] = 1;
		$_SESSION['record']['giveup'] = 1;
	}

}
?>