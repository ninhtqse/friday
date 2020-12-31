<?php
/**
 * ベンチャー・リンク　すらら
 *
 * e-learning system 問題確認プログラム
 *
 * eラーニングシステム管理者用画面
 * プラクティスステージ管理→問題追加・修正・削除→確認ボタン押下
 *
 * @author Azet
 */


/**
 * HTMLコンテンツを作成する機能
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @return string HTML
 */
function display_problem() {
	
	if ($_POST['action'] == "check") {
		// kaopiz 2020/09/15 speech start
		if($_SESSION['problem']['form_type'] == 18){
			unset($_POST['action']);
			return 'check_vocab_problem';
		}
		// kaopiz 2020/09/15 speech end
		// 解答確認処理
		$ERROR = check_answer();
		if ($ERROR && (!$_SESSION['record']['answer'] && $_SESSION['record']['answer'] != "0")) { unset($_POST['action']); }
	}
	if ($_POST['action'] == "check") {			//	解答チェック
		$html = make_check_html($ERROR);
	} elseif ($_POST['action'] == "answer") {		//	解答表示
		$html = make_answer_html();
	} else {									//	問題出題
		$html = make_default_html($ERROR);
	}
	return $html;

}

/**
 * 画面枠表示
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


		//	サービス情報読み込み
		$sql  = "SELECT sc.question_flg ".
				" FROM ".T_SERVICE_COURSE_LIST. " scl ".
				" INNER JOIN ".T_SERVICE." sc ON scl.service_num = sc.service_num ".
				" WHERE scl.course_num='".$LINE['course_num']."'".
				"   AND scl.course_type = '1' ".
				"   AND scl.display = '1' ".
				"   AND scl.mk_flg  = '0' ".
				" LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$LINE['question_flg'] = $list['question_flg'];
		}

		//	横・縦書き設定
		$sql  = "SELECT write_type, math_align, language_type, course_name FROM ".T_COURSE.
				" WHERE course_num='".$LINE['course_num']."'".
				" LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$LINE['write_type'] = $list['write_type'];
			$LINE['math_align'] = $list['math_align'];
			$LINE['language_type'] = $list['language_type'];
			$LINE['course_name'] = $list['course_name'];
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
		$sql  = "SELECT stage.stage_name, lesson.lesson_name ".
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
					" , course.math_align".
					" , course.language_type".
					" , course.course_name".
					" , sc.question_flg".
					" FROM ".T_PROBLEM." ".T_PROBLEM.
					" INNER JOIN ".T_COURSE." ".T_COURSE." ON problem.course_num=course.course_num".
					" INNER JOIN ".T_BLOCK." ".T_BLOCK." ON problem.block_num=block.block_num".
					" INNER JOIN ".T_UNIT." ".T_UNIT." ON block.unit_num=unit.unit_num".
					" INNER JOIN ".T_LESSON." ".T_LESSON." ON block.lesson_num=lesson.lesson_num".
					" INNER JOIN ".T_STAGE." ".T_STAGE." ON block.stage_num=stage.stage_num".
					" LEFT  JOIN ".T_SERVICE_COURSE_LIST." scl ON problem.course_num = scl.course_num AND scl.course_type = '1' AND scl.display = '1' AND scl.mk_flg  = '0'  ".
					" LEFT  JOIN ".T_SERVICE." sc ON scl.service_num = sc.service_num AND sc.display = '1' AND sc.mk_flg  = '0' ".
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
			$val = stripslashes($val);						// add oda 2014/09/02 課題要望一覧No349 制御文字が入っているので、正常に表示できていなかった。
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
	$display_problem_num = $display_problem->set_display_problem_num();
	$voice_button = $display_problem->set_voice_button();
	$question = $display_problem->set_question();
	$problem = $display_problem->set_problem();
	if(form_type == 18) { // kaopiz 2020/09/15 speech
		$set_form = $display_problem->speech_button(); // kaopiz 2020/09/15 speech
	}else{ // kaopiz 2020/09/15 speech
		$set_form = $display_problem->set_form();
	} // kaopiz 2020/09/15 speech
	$LESSONUNIT = stage_name . " " . lesson_name . " " . unit_name;

	//	ヒント表示
	if (hint_number == 99) {
		$hint = $display_problem->hint_check();
	}

	//	サブスペース(area3)
	//upd start hirose 2018/12/04 すらら英単語
//	$sub_space = $display_problem->display_area3("start");
//if(form_type == 16 || form_type == 17){
	if(form_type == 16 || form_type == 17 || form_type == 18){ // kaopiz 2020/09/15 speech
		$sub_space = "";
	}else{
		$sub_space = $display_problem->display_area3("start");
	}
	//upd end hirose 2018/12/04 すらら英単語

	// 画面サイズに合わせる
	$size_check = "zoom_all(".$_SESSION['course']['course_num'].");";
	if (form_type == 5) {
		$MORE_SCRIPT = $display_problem->set_more_script();
		$answer_info = $display_problem->set_answer_info();
	} elseif (form_type == 8) {
		$MORE_SCRIPT = $display_problem->set_more_script();
	// } elseif (form_type == 3 || form_type == 4 || form_type == 10) {	// upd hasegawa 2018/03/09 百マス計算
	} elseif (form_type == 3 || form_type == 4 || form_type == 10 || form_type == 14) {
		$tabindex = $display_problem->set_more_script();
		$MORE_SCRIPT = $display_problem->set_more_script_hand(); // add 2016/06/02 yoshizawa 手書き認識
	} elseif (form_type == 11) {
		$MORE_SCRIPT = $display_problem->set_more_script();
		$FUNCTIONKEY = $display_problem->fnkey();
	} elseif (form_type == 13) {									// add oda 2016/02/02 作図ツール
		$MORE_SCRIPT = $display_problem->set_more_script();
	//add start kimura 2018/09/13 漢字学習コンテンツ_書写ドリル対応 //書写
	} elseif (form_type == 15) {
		$MORE_SCRIPT = $display_problem->set_more_script();
		$display_problem->delete_syosya_log(); // add 2019/01/29 yoshizawa 手書きV2書写 1年以上前のsyosya_logテーブルの生ログを削除します。
	//add end   kimura 2018/10/02 漢字学習コンテンツ_書写ドリル対応
	} elseif (form_type == 18) { // kaopiz 2020/09/15 speech
		$MORE_SCRIPT = $display_problem->set_more_script(); // kaopiz 2020/09/15 speech
	} else {
		$MORE_SCRIPT = "";
	}

	$form_start = $display_problem->form_start($set_form);
	$form_end = $display_problem->form_end($set_form);

	// タブレット時の音声対応		// add oda 2014/12/15
	if (is_tablet()) {
		$sound_string = "play_sound_ajax('sound_ajax', '/student/images/drill/silent3sec.mp3', '', '');";				// update oda 2014/12/15 第4パラメータ追加
		$form_end = preg_replace("/Form_Check_radio_ajax/", "".$sound_string."Form_Check_radio_ajax", $form_end);
		$form_end = preg_replace("/Form_Check_checkbox_ajax/", "".$sound_string."Form_Check_checkbox_ajax", $form_end);
		$form_end = preg_replace("/Form_Check_textbox_ajax/", "".$sound_string."Form_Check_textbox_ajax", $form_end);
		$form_end = preg_replace("/Form_type8_ajax/", "".$sound_string."Form_type8_ajax", $form_end);
		$form_end = preg_replace("/SubmitAnswerAjax/", "".$sound_string."SubmitAnswerAjax", $form_end);
		$form_end = preg_replace("/SubmitAnswerDrawingAjax/", "".$sound_string."SubmitAnswerDrawingAjax", $form_end);// add 2017/09/29 yoshizawa 作図ツール音声対応
		$form_end = preg_replace("/SubmitAnswerSyosya/", "".$sound_string."SubmitAnswerSyosya", $form_end);//add kimura 2018/10/05 漢字学習コンテンツ_書写ドリル対応
		$form_end = preg_replace("/Form_type16_ajax/", "".$sound_string."Form_type16_ajax", $form_end);//add hirose 2018/12/07 すらら英単語
		// ※並び替えはselect.js（SetInitialPositions()内）でやっています
	}
	//add start hirose 2018/11/21 すらら英単語
	if(form_type == 16){
		$form_end = '';
	}
	//add end hirose 2018/11/21 すらら英単語

	// 画面表示時に音を止める	// add oda 2014/12/17
	$check_voice = <<<EOT
<script type="text/javascript">
allSoundStop = 1;
if (audioTagSupport) {
var obj = document.getElementById('sound_ajax');
if (obj) { try{obj.pause();} catch (e) {} }
} else {
var obj = document.getElementById('sound_ajax_flash');
if (obj) { niftyplayer('sound_ajax_flash').pause(); }
}
</script>
EOT;
	
	if (setting_type == 1) { unset($display_problem_num); }
	$make_html = new read_html();
	$make_html->set_dir(STUDENT_TEMP_DIR.course_num."/");
	if (block_type == 4 || block_type == 5 || block_type == 6) {
		// del start oda 2014/07/31 課題要望一覧No270 CSSはAjaxの親で設定しているので不要
		//// すららスタンダード以外のコースでは$MORE_SCRIPTでcssを読み込んでいます。
		//// CSS読み込み用
		//$MORE_SCRIPT .= "<link type=\"text/css\" charset=\"utf-8\" rel=\"stylesheet\" href=\"".MATERIAL_TEMP_DIR.course_num."/".stage_num."/course.css\" />\n";		// update oda 2014/07/31 課題要望一覧No270 IE8でcharsetが必要な為、追加
		//// レッスン毎のCSSを反映させる処理 		下記ディレクトリにCSSファイルがある時のみ反映します
		//$css_path = MATERIAL_TEMP_DIR.course_num."/".stage_num."/".lesson_num."_course.css";
		//if (file_exists($css_path)) {
		//	$MORE_SCRIPT .= "<link type=\"text/css\" charset=\"utf-8\" rel=\"stylesheet\" href=\"".$css_path."\" />\n";		// update oda 2014/07/31 課題要望一覧No270 IE8でcharsetが必要な為、追加
		//}
		// del start oda 2014/07/31
		// テンプレート読み込み
		$make_html->set_file("study_block_type5_drill.htm");
	// add start hasegawa 2018/02/26 百マス計算
	} elseif(block_type == 7){
		$make_html->set_file("study_block_type7_drill.htm");
	// add end hasegawa 2018/02/26
	// add start hirose 2018/11/20 すらら英単語
	} elseif(block_type == 8){
		$make_html->set_file("study_block_type8_drill.htm");
	// add end hirose 2018/11/20 すらら英単語
	} else {
		// テンプレート読み込み
		$make_html->set_file("drill_ajax.htm");
	}
	
	//add start hirose 2018/12/06 すらら英単語
	//開発では、動的に動かなくてよいので、固定を入れる
//if(form_type == 16 || form_type == 17){
	if(form_type == 16 || form_type == 17 || form_type == 18){  // kaopiz 2020/09/15 speech
		$paging = "<span class=\"paging table-cell\">0/0</span>";
		$time = "<span class=\"time table-cell\"></span>";//ドリルでは残り時間はいらないので、タグのみ追加
		$INPUTS['PAGING'] = array('result'=>'plane','value'=>$paging);
		$INPUTS['TIME'] = array('result'=>'plane','value'=>$time);
	}
	//add end hirose 2018/12/06 すらら英単語

	// add start 2019/02/19 yoshizawa すらら英単語
	// form_type=17（書く）の場合、問題表示時に音声ボタンを非表示、自動再生もしません。
	if(form_type == 17){
		$voice_button = "";
	}
	// add end 2019/02/19 yoshizawa すらら英単語

	//---------------------------------------------------------------------------------------------
	// ドリルタイトル名用のコース、ステージ、レッスン、ユニットの名称取得
	//---------------------------------------------------------------------------------------------
	$DRILL_TITLE = get_drill_title_name($LINE['unit_num']);

	//
	$css_file = MATERIAL_TEMP_DIR.course_num."/".stage_num."/course.css";
	if (file_exists(MATERIAL_TEMP_DIR.course_num."/".stage_num."/".lesson_num."_course.css")) {
		$css_file = MATERIAL_TEMP_DIR.course_num."/".stage_num."/".lesson_num."_course.css";
	}
	$INPUTS['CSSPATH'] = array('result'=>'plane','value'=>$css_file);

	$INPUTS['MORESCRIPT'] = array('result'=>'plane','value'=>$MORE_SCRIPT);
	$INPUTS['LESSONUNIT'] = array('result'=>'plane','value'=>$LESSONUNIT);
	$INPUTS['DISPLAYPROBLEMNUM'] = array('result'=>'plane','value'=>$display_problem_num);
	$INPUTS['VOICE'] = array('result'=>'plane','value'=>$voice_button);
	$INPUTS['QUESTION'] = array('result'=>'plane','value'=>$question);
	$INPUTS['PROBLEM'] = array('result'=>'plane','value'=>$problem);
	$INPUTS['ANSWERINFO'] = array('result'=>'plane','value'=>$answer_info);
	$INPUTS['ANSWERCOLUMN'] = array('result'=>'plane','value'=>$set_form);
	$INPUTS['FORMSTART'] = array('result'=>'plane','value'=>$form_start);
	$INPUTS['FORMEND'] = array('result'=>'plane','value'=>$form_end);
	$INPUTS['HINT'] = array('result'=>'plane','value'=>$hint);
	$INPUTS['FUNCTIONKEY'] = array('result'=>'plane','value'=>$FUNCTIONKEY);
	$INPUTS['ERROR'] = array('result'=>'plane','value'=>$errors);
	$INPUTS['CHECKVOICE'] = array('result'=>'plane','value'=>$check_voice);				// 音声		// add oda 2014/12/17

	//	○×画像とゲージを切り替える
	$level_gage  = "\n<!--LEVELGAGEHTML-->\n";
	$level_gage .= $sub_space;
	$INPUTS['LEVELGAGE'] = array('result'=>'plane','value'=>$level_gage);

	// ドリルタイトル用
	$INPUTS['COURSENAME']		= array('result'=>'plane','value'=>$DRILL_TITLE['course_name']);
	$INPUTS['STAGENAME']		= array('result'=>'plane','value'=>$DRILL_TITLE['stage_name']);
	$INPUTS['STAGENAME2']		= array('result'=>'plane','value'=>$DRILL_TITLE['stage_name2']);
	$INPUTS['LESSONNAME']		= array('result'=>'plane','value'=>$DRILL_TITLE['lesson_name']);
	$INPUTS['LESSONNAME2']	= array('result'=>'plane','value'=>$DRILL_TITLE['lesson_name2']);
	$INPUTS['UNITNAME']		= array('result'=>'plane','value'=>$DRILL_TITLE['unit_name']);
	$INPUTS['UNITNAME2']		= array('result'=>'plane','value'=>$DRILL_TITLE['unit_name2']);
	
	

	// if (block_type == 4 || block_type == 5 || block_type == 6) {	// upd hasegawa 2018/03/09 百マス計算
	if (block_type == 4 || block_type == 5 || block_type == 6 || block_type == 7) {
		if (get_service_type(course_num) == "3") {
			$INPUTS['COURSENO'] = array('result'=>'plane','value'=>substr(course_name,0,3));
			$unit_image = "";
			if (unit_name == "Grammar") {
				$unit_image = "<img src=\"/student/images/toeic/name_grammar.gif\" width=\"186\" height=\"37\" alt=\"Grammar\" class=\"mgn_btm_20\" />";
			} elseif (unit_name == "Vocabulary") {
				$unit_image = "<img src=\"/student/images/toeic/name_vocabulary.gif\" width=\"186\" height=\"37\" alt=\"Vocabulary\" class=\"mgn_btm_20\" />";
			} elseif (unit_name == "Listening/Reading") {
				$unit_image = "<img src=\"/student/images/toeic/name_listening_reading.gif\" width=\"274\" height=\"37\" alt=\"Listening/Reading\" class=\"mgn_btm_20\" />";
			}
			$INPUTS['UNITIMAGE'] = array('result'=>'plane','value'=>$unit_image);
			$lesson_unit_name = substr(lesson_name,4);
			$INPUTS['UNITCOUNT'] = array('result'=>'plane','value'=>$lesson_unit_name);

			$INPUTS['CLOSEURL'] = array('result'=>'plane','value'=>"window.close();");
		}
	}

	$make_html->set_rep_cmd($INPUTS);
	$html = $make_html->replace();

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
	} elseif ($_SESSION['problem']['form_type'] == 13) {	// add hasegawa 2016/04/14 作図ツール
		if ($_POST['answer']) {
			if($_POST['judgment'] == "true") {
				$_POST['answer'][0] = "t::".$_POST['answer'][0];
			} else {
				$_POST['answer'][0] = "f::".$_POST['answer'][0];
			}
		}
	//add start hirose 2018/12/05 すらら英単語(form_type16)
	//配列にしないと今後の処理が通らないため、配列に直す
	} elseif ($_SESSION['problem']['form_type'] == 16) {
		$put_text = $_POST['answer'];
		unset($_POST['answer']);
		$_POST['answer'][0] = $put_text;
	//add end hirose 2018/12/05 すらら英単語(form_type16)
	//add start hirose 2018/10/1 すらら英単語(form_type17)
	} elseif ($_SESSION['problem']['form_type'] == 17) {
		$put_text = implode($_POST['answer']);
		unset($_POST['answer']);
		$_POST['answer'][0] = $put_text;
	//add end hirose 2018/09/13 すらら英単語(form_type17)
	} else {
		if ($_SESSION['problem']['form_type'] == 10 && (!$_POST['answer'] && array_search("0",$_POST['answer']) === FALSE) && ($_SESSION['record']['answer'] || $_SESSION['record']['answer'] == "0")) { $flag = 1; }
		if ($_SESSION['problem']['form_type'] == 14 && (!$_POST['answer'] && array_search("0",$_POST['answer']) === FALSE) && ($_SESSION['record']['answer'] || $_SESSION['record']['answer'] == "0")) { $flag = 1; }	// add hasegawa 2018/03/09 百マス計算

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
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function make_check_html($ERROR) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

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

	// 変数クリア＆設定		// add oda 2014/12/17
	$check_voice = "";
	$pre = ".mp3";
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	if (preg_match("/Firefox/i", $user_agent) || preg_match("/Opera/i", $user_agent) || preg_match("/OPR/i", $user_agent)) { $pre = ".ogg"; }
	$sql  = "SELECT ".
			" sc.question_flg".
			" FROM ".T_SERVICE_COURSE_LIST." scl ".
			" INNER JOIN ".T_SERVICE." sc ON scl.service_num = sc.service_num ".
			" WHERE scl.course_num='".$_SESSION['course']['course_num']."'".
			"   AND scl.course_type = '1' ".
			"   AND scl.display = '1' ".
			"   AND scl.mk_flg  = '0' ".
			" LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$question_flg = $list['question_flg'];
		if (!defined('question_flg')) {
			define('question_flg', $question_flg);
		}
	}

	$html5_flag = false;
	$voice_file = "";
	$display_timerstartup = 0;

	// add start hasegawa 2018/03/09 百マス計算
	$CHECK_SUCCESS = 1;
	if (form_type == 14) {
		$CORRECT_WORDS = array();
		if ((defined("correct") || correct == "0") || (defined("option1") || option1 == "0")) {
			if (defined("correct") || correct == "0") { $CORRECT = explode("<>", correct); }
			if (defined("option1") || option1 == "0") { $BUD = explode("<>", option1); }

			$correct_max = count($CORRECT);
			$bud_max = count($BUD);
			if ($correct_max > $bud_max) {
				$max = $correct_max;
			} else {
				$max = $bud_max;
			}
			for ($i=0; $i<$max; $i++) {
				if ($BUD[$i] || $BUD[$i] == "0") {
					$CORRECT_WORDS[$i] = $BUD[$i];
				} else {
					$CORRECT_WORDS[$i] = $CORRECT[$i];
				}
			}
		}
		$CHECK_SUCCESS = count($CORRECT_WORDS);
	}
	// add end hasegawa 2018/03/09

	// if ($_SESSION['record']['success'] == 1) {	//	正解	// upd hasegawa 2018/03/09 百マス計算
	if ($_SESSION['record']['success'] == $CHECK_SUCCESS) {

		$display_problem = new display_problem();
		$display_problem->set_ajax();
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

		// add oda 2014/12/17 音声対応
		$correct_voice_file = "/student/images/drill/true".$pre;

		//	サブスペース(area3)
		// if (block_type == 4 || block_type == 5 || block_type == 6) {	// upd hasegawa 2018/03/09 百マス計算
		if (block_type == 4 || block_type == 5 || block_type == 6 || block_type == 7) {
			$html5_flag = true;
			$display_problem->html5_page_set($html5_flag);
		}
		//add start hirose 2018/11/21 すらら英単語
		elseif(block_type == 8){
			//ドリルタイプEではゲージを使わないため何もしない
		}
		//add end hirose 2018/11/21 すらら英単語
		$sub_space = $display_problem->display_area3("true");

		$display_timerstartup = 1;

		$pre = ".mp3";
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		if (preg_match("/Firefox/i", $user_agent)) {
			$pre = ".ogg";
		}
		$voice_file = "/student/images/drill/true".$pre;

	} else {	//	不正解

		$success = $_SESSION['record']['success'];
		if (block_type > 1) {
			if ($_SESSION['record']['again'] >= correct_number) {
				// $success = 1;		// upd hasegawa 2018/03/09 百マス計算
				$success = $CHECK_SUCCESS;
			}
		}
		$display_problem = new display_problem();
		$display_problem->set_ajax();
		$display_problem->set_success($success);
		$display_problem->set_answers($_SESSION['record']['answer']);
		$display_problem->set_again($_SESSION['record']['again']);
		$display_problem_num = $display_problem->set_display_problem_num();
		$voice_button = $display_problem->set_voice_button();
		$question = $display_problem->set_question();
		$problem = $display_problem->set_problem();
		$answer_info = $display_problem->set_answer_info($_SESSION['record']['answer_info']);
		// add oda 2014/12/17  音声対応
		$correct_voice_file = "/student/images/drill/false".$pre;

		if (correct_number > 0 && $_SESSION['record']['again'] >= correct_number) {
			if (form_type == 5) { $display_problem->set_end_flg(); }
		} else {
			$set_retry_msg = $display_problem->set_retry_msg();
			$set_form = $display_problem->set_form();
		}
		$send_question = $display_problem->send_question_check();
		$LESSONUNIT = stage_name . " " . lesson_name . " ". unit_name;

		//	ヒント表示
		if (hint_number > 0 && ($_SESSION['record']['again'] >= hint_number || hint_number == 99) && $_SESSION['record']['again'] < correct_number) {	// update oda 2014/05/08 課題要望一覧No230 99の時も表示する
			$hint = $display_problem->hint_check();
		}

		//	正解を見るボタン
		$type = "true";
		if (block_type == 1) {
			if (correct_number > 0 && $_SESSION['record']['again'] >= correct_number) {
				$explanation_button = $display_problem->explanation_button();
				$display_timerstartup = 1;
			}
		} elseif (block_type == 2) {
			if (correct_number > 0 && $_SESSION['record']['again'] >= correct_number) {
				$explanation_button = $display_problem->explanation_button();
				unset($form_end);
				$form_end_check = 1;
				$display_timerstartup = 1;
			}
		} elseif (block_type == 3) {
			if (correct_number > 0 && $_SESSION['record']['again'] >= correct_number) {
				$explanation_button = $display_problem->explanation_button();
				unset($form_end);
				$form_end_check = 1;
				$display_timerstartup = 1;
			}
		}
		elseif (block_type == 4) {
			if (correct_number > 0 && $_SESSION['record']['again'] >= correct_number) {
				$explanation_button = $display_problem->explanation_button();
				unset($form_end);
				$form_end_check = 1;
				$display_timerstartup = 1;
			}
		}
		elseif (block_type == 5) {
			if (correct_number > 0 && $_SESSION['record']['again'] >= correct_number) {
				$explanation_button = $display_problem->explanation_button();
				unset($form_end);
				$form_end_check = 1;
				$display_timerstartup = 1;
			}
		}
		elseif (block_type == 6) {
			if (correct_number > 0 && $_SESSION['record']['again'] >= correct_number) {
				$explanation_button = $display_problem->explanation_button();
				unset($form_end);
				$form_end_check = 1;
				$display_timerstartup = 1;
			}
		}
		// add start hasegawa 2018/03/09 百マス計算
		elseif (block_type == 7) {
			if (correct_number > 0 && $_SESSION['record']['again'] >= correct_number) {
				$explanation_button = $display_problem->explanation_button();
				unset($form_end);
				$form_end_check = 1;
				$display_timerstartup = 1;
			}
		}
		// add end hasegawa 2018/03/09
		// add start hirose 2018/11/21 すらら英単語
		elseif (block_type == 8) {
			if (correct_number > 0 && $_SESSION['record']['again'] >= correct_number) {
				$explanation_button = $display_problem->explanation_button();
				unset($form_end);
				$form_end_check = 1;
				$display_timerstartup = 1;
			}
		}
		// add end hirose 2018/11/21 すらら英単語
		// add start hirose 2018/09/20 英単語マスター機能追加対応(form_type17)
		if(form_type == 17){
			$explanation_button = '';
		}
		// add end hirose 2018/09/20 英単語マスター機能追加対応(form_type17)

		//	サブスペース(area3)
		// if (block_type == 4 || block_type == 5 || block_type == 6) {	// upd hasegawa 2018/03/09 百マス計算
		if (block_type == 4 || block_type == 5 || block_type == 6 || block_type == 7) {
			$html5_flag = true;
			$display_problem->html5_page_set($html5_flag);
		}
		//add start hirose 2018/11/21 すらら英単語
		elseif(block_type == 8){
			//ドリルEの時はゲージが無いので何もしない。
		}
		//add end hirose 2018/11/21 すらら英単語
		$sub_space = $display_problem->display_area3("false");

		$pre = ".mp3";
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		if (preg_match("/Firefox/i", $user_agent)) { $pre = ".ogg"; }
		$voice_file = "/student/images/drill/false".$pre;
	}
	

	// update start oda 2014/12/17 音声対応の為、再作成
	//// TOEICサービスの時、正解／不正解音を鳴らす
	//$check_voice = "";
	////if ((get_service_type($_SESSION['course']['course_num']) == "3" || $html5_flag) && $voice_file != "") {
	//if ((get_service_type($_SESSION['course']['course_num']) == "3") && $voice_file != "") {						// update oda 2014/07/06 TOEICの時のみ出力
	//	$http = "http://";
	//	if($_SERVER['HTTPS']=='on'){
	//		$http = "https://";
	//	}
	//	$check_voice = <<<EOT
	//<!-- SOUND START-->
	//<script language="JavaScript" type="text/javascript">
	//setTimeout(
	//	function() {
	//		play_sound('correct');
	//	}, 500);
	//</script>
	//<audio src="{$voice_file}" id="correct">
	//  <object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="{$http}fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" width="0" height="0" id="correct_flash">
	//  <param name="movie" value="/student/images/drill/play_sound.swf?file={$voice_file}" />
	//  <embed src="/student/images/drill/play_sound.swf?file={$voice_file}" width="0" height="0" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="{$http}www.adobe.com/go/getflash">
	//  </embed>
	//  </object>
	//</audio>
	//<!-- SOUND END-->
	//EOT;
	//}

	// 問題文や質問に音声が有った場合は、結果の時に自動再生する
	// 音声データが有った場合は、結果の時に自動再生する
	$check_voice = "";
	$word_voice_file = "";
	// if ($_SESSION['record']['success'] == 1) {					// 正解の時のみ解説の音声を流すこととする // upd hasegawa 2018/03/09 百マス計算
	if ($_SESSION['record']['success'] == $CHECK_SUCCESS) {
		if ($voice_button != "") {
			$work_voice = voice_data;
			if (preg_match("/Firefox/i", $user_agent) || preg_match("/Opera/i", $user_agent) || preg_match("/OPR/i", $user_agent)) {
				$work_voice = preg_replace("/.mp3/i", ".ogg", $work_voice);
			}
			$word_voice_file = MATERIAL_VOICE_DIR.course_num."/".stage_num."/".lesson_num."/".unit_num."/".block_num."/".$work_voice;
		}
		// update oda 2015/08/19 課題要望一覧No478 解説の音声を優先して出す様に変更
		if (defined('explanation')) {
			preg_match_all("|\[!VOICE=(.*)!\]|U",explanation,$VOICE);
			if ($VOICE) {
				foreach ($VOICE[1] AS $key => $VAL) {
					$work_voice = $VAL;
					if (preg_match("/Firefox/i", $user_agent) || preg_match("/Opera/i", $user_agent) || preg_match("/OPR/i", $user_agent)) {
						$work_voice = preg_replace("/.mp3/i", ".ogg", $work_voice);
					}
					$word_voice_file = MATERIAL_VOICE_DIR.course_num."/".stage_num."/".lesson_num."/".unit_num."/".block_num."/". $work_voice;
				}
			}
		}
	}
	// add start 2019/02/19 yoshizawa すらら英単語
	if(form_type == 16){
		// form_type=16（意味）の時は正解と不正解のどちらでも、正誤判定時には音声は出さない。
		$word_voice_file = "";
	} else if(form_type == 17){
		// form_type=17（書く）の時は正解と不正解のどちらでも、正誤判定時に音声を鳴らします。
		if ($voice_button != "") {
			$work_voice = voice_data;
			if (preg_match("/Firefox/i", $user_agent) || preg_match("/Opera/i", $user_agent) || preg_match("/OPR/i", $user_agent)) {
				$work_voice = preg_replace("/.mp3/i", ".ogg", $work_voice);
			}
			$word_voice_file = MATERIAL_VOICE_DIR.course_num."/".stage_num."/".lesson_num."/".unit_num."/".block_num."/".$work_voice;
		}
	}
	// add end 2019/02/19 yoshizawa すらら英単語

	// add start oda 2015/01/08
	// 音声が鳴り終わらないうちに画面遷移し、解答すると、意図しない場面で音声が鳴ってしまうので、チェック用の変数を設定する
	$base_id1 = "";
	$base_id2 = "";
	if ($correct_voice_file) {
		$base_id1 = $correct_voice_file;
	}
	if ($word_voice_file) {
		$base_id2 = $word_voice_file;
	}
	// add end oda 2015/01/08
	
//add start hirose 2018/09/26 英単語マスター機能追加対応(音声自動再生)
//以下のタイプは、画面起動時に音声を鳴らしているため、呼び出し元を変更。
//子ウィンドウ側でaudioタグを設置してplay_sound_ajax()を使うと親ウィンドウのid='sound_ajax'とぶつかるためです。
if(form_type == 16 || form_type == 17){
	$check_voice = <<<EOT
<script type="text/javascript">
allSoundStop = 0;
checkFileName1 = '{$base_id1}';
checkFileName2 = '{$base_id2}';
checkFileName3 = '';
setTimeout(function() { opener_play_sound_ajax('sound_ajax','{$correct_voice_file}','{$word_voice_file}','');sound_stop_preparation();}, 4);
document.body.onbeforeunload = function() { opener_play_sound_ajax('sound_ajax', '/student/images/drill/silent3sec.mp3', '', ''); }
</script>
EOT;
}else{
//add end hirose 2018/09/26 英単語マスター機能追加対応(音声自動再生)
	$check_voice = <<<EOT
<script type="text/javascript">
allSoundStop = 0;
checkFileName1 = '{$base_id1}';
checkFileName2 = '{$base_id2}';
checkFileName3 = '';
setTimeout(function() { play_sound_ajax('sound_ajax','{$correct_voice_file}','{$word_voice_file}','');}, 4);
</script>
EOT;
	// add end oda 2014/12/17
}//add hirose 2018/09/26 英単語マスター機能追加対応(音声自動再生)

	// 画面サイズに合わせる
	$size_check = "zoom_all(".$_SESSION['course']['course_num'].");";

	if (form_type == 5) {
		if (correct_number > 0 && $_SESSION['record']['again'] < correct_number && $_SESSION['record']['success'] != 1) {
			$MORE_SCRIPT = $display_problem->set_more_script();
		}
	} elseif (form_type == 8) {
		if (correct_number > 0 && $_SESSION['record']['again'] < correct_number && $_SESSION['record']['success'] != 1) {
			$MORE_SCRIPT = $display_problem->set_more_script();
		}
	// } elseif (form_type == 3 || form_type == 4 || form_type == 10) {	// upd hasegawa 2018/03/09 百マス計算
	} elseif (form_type == 3 || form_type == 4 || form_type == 10 || form_type == 14) {
		$tabindex = $display_problem->set_more_script();
		$MORE_SCRIPT = $display_problem->set_more_script_hand(); // add 2016/06/02 yoshizawa 手書き認識
	} elseif (form_type == 11) {
		$MORE_SCRIPT = $display_problem->set_more_script();
		$FUNCTIONKEY = $display_problem->fnkey();
	} elseif (form_type == 13) {	// add oda 2016/02/02 作図ツール	// upd hasegawa2016/04/15
		$MORE_SCRIPT = $display_problem->set_more_script();
	//add start kimura 2018/09/13 漢字学習コンテンツ_書写ドリル対応 //書写
	} elseif (form_type == 15) {
		$MORE_SCRIPT = $display_problem->set_more_script();
	//add end   kimura 2018/09/13 漢字学習コンテンツ_書写ドリル対応
	} else {
		$MORE_SCRIPT = "";
	}
	// kaopiz 2020/10/14 speech start
	if(in_array(form_type, [1, 2, 3, 4, 5, 10])) {
		$MORE_SCRIPT .= "<script type=\"text/javascript\" src=\"https://sdk.cloud.chivox.com/chivoxsdk-js/v6.0/chivox.min.js\"></script>\n";
		$MORE_SCRIPT .= "<script type=\"text/javascript\" src=\"/javascript/chivox-button.js\"></script>\n";
		$MORE_SCRIPT .= "<link rel=\"stylesheet\" href=\"/css/chivox-button.css\">\n";
	}
	// kaopiz 2020/10/14 speech end

	if (block_type == 1) {
		$form_start = $display_problem->form_start($set_form);
		$form_end = $display_problem->form_end($set_form);
	} elseif (block_type == 2) {
		$form_start = $display_problem->form_start($set_form);
		if ($form_end_check == 0) {
			$form_end = $display_problem->form_end($set_form);
		} else {
			$form_end = "<input type=\"hidden\" id=\"dsp_idx\" value=\"".$_SESSION['dsp_idx']."\">\n";
			$form_end.= "</form>\n";
		}
	} elseif (block_type == 3) {
		$form_start = $display_problem->form_start($set_form);
		if ($form_end_check == 0) {
			$form_end = $display_problem->form_end($set_form);
		} else {
			$form_end = "<input type=\"hidden\" id=\"dsp_idx\" value=\"".$_SESSION['dsp_idx']."\">\n";
			$form_end.= "</form>\n";
		}
	}
	elseif (block_type == 4) {
		$form_start = $display_problem->form_start($set_form);
		if ($form_end_check == 0) {
			$form_end = $display_problem->form_end($set_form);
		} else {
			$form_end = "<input type=\"hidden\" id=\"dsp_idx\" value=\"".$_SESSION['dsp_idx']."\">\n";
			$form_end.= "</form>\n";
		}
	}
	elseif (block_type == 5) {
		$form_start = $display_problem->form_start($set_form);
		if ($form_end_check == 0) {
			$form_end = $display_problem->form_end($set_form);
		} else {
			$form_end = "<input type=\"hidden\" id=\"dsp_idx\" value=\"".$_SESSION['dsp_idx']."\">\n";
			$form_end.= "</form>\n";
		}
	}
	elseif (block_type == 6) {
		$form_start = $display_problem->form_start($set_form);
		if ($form_end_check == 0) {
			$form_end = $display_problem->form_end($set_form);
		} else {
			$form_end = "<input type=\"hidden\" id=\"dsp_idx\" value=\"".$_SESSION['dsp_idx']."\">\n";
			$form_end.= "</form>\n";
		}
	}
	// add start hasegawa 2018/03/09 百マス計算
	elseif (block_type == 7) {
		$form_start = $display_problem->form_start($set_form);
		if ($form_end_check == 0) {
			$form_end = $display_problem->form_end($set_form);
		} else {
			$form_end = "<input type=\"hidden\" id=\"dsp_idx\" value=\"".$_SESSION['dsp_idx']."\">\n";
			$form_end.= "</form>\n";
		}
	}
	// add end hasegawa 2018/03/09
	// add start hirose 2018/11/21 すらら英単語
	elseif (block_type == 8) {
		$form_start = $display_problem->form_start($set_form);
		if ($form_end_check == 0) {
			$form_end = $display_problem->form_end($set_form);
		} else {
			$form_end = "<div class=\"button-list\">\n";
			$form_end.= "</div>\n";
			$form_end.= "<input type=\"hidden\" id=\"dsp_idx\" value=\"".$_SESSION['dsp_idx']."\">\n";
			$form_end.= "</form>\n";
		}
	}
	// add end hirose 2018/11/21 すらら英単語
	//add start hirose 2018/12/06 すらら英単語
	//開発では、動的に動かなくてよいので、固定を入れる
	if(form_type == 16 || form_type == 17){
		$answer_img = "";
		if(isset($_SESSION['record']['success'])){
			$answer_img = $display_problem->get_answer_img($_SESSION['record']['success']);
		}
		$INPUTS['ANSWERIMG'] = array('result'=>'plane','value'=>$answer_img);
		$paging = "<span class=\"paging table-cell\">0/0</span>";
		$time = "<span class=\"time table-cell\"></span>";//ドリルでは残り時間はいらないので、タグのみ追加
		$INPUTS['PAGING'] = array('result'=>'plane','value'=>$paging);
		$INPUTS['TIME'] = array('result'=>'plane','value'=>$time);
	}
	//add end hirose 2018/12/06 すらら英単語

	
	// if (block_type == 4 || block_type == 5 || block_type == 6) {	// upd hasegawa 2018/03/09 百マス計算
	if (block_type == 4 || block_type == 5 || block_type == 6 || block_type == 7) {
		if (get_service_type(course_num) == "3") {
			$INPUTS['COURSENO'] = array('result'=>'plane','value'=>substr(course_name,0,3));
			$unit_image = "";
			if (unit_name == "Grammar") {
				$unit_image = "<img src=\"/student/images/toeic/name_grammar.gif\" width=\"186\" height=\"37\" alt=\"Grammar\" class=\"mgn_btm_20\" />";
			} elseif (unit_name == "Vocabulary") {
				$unit_image = "<img src=\"/student/images/toeic/name_vocabulary.gif\" width=\"186\" height=\"37\" alt=\"Vocabulary\" class=\"mgn_btm_20\" />";
			} elseif (unit_name == "Listening/Reading") {
				$unit_image = "<img src=\"/student/images/toeic/name_listening_reading.gif\" width=\"274\" height=\"37\" alt=\"Listening/Reading\" class=\"mgn_btm_20\" />";
			}
			$INPUTS['UNITIMAGE'] = array('result'=>'plane','value'=>$unit_image);
			$lesson_unit_name = substr(lesson_name,4);
			$INPUTS['UNITCOUNT'] = array('result'=>'plane','value'=>$lesson_unit_name);

			$INPUTS['CLOSEURL'] = array('result'=>'plane','value'=>"window.close();");

			// 解答欄の添え字タグは不要
			if ($answer_info) {
				$answer_info = preg_replace("/<sup>/i", "", $answer_info);
				$answer_info = preg_replace("/<\/sup>/i", "", $answer_info);
			}
		}
	}

	//---------------------------------------------------------------------------------------------
	// ドリルタイトル名用のコース、ステージ、レッスン、ユニットの名称取得	add koyama 2013/11/06
	//---------------------------------------------------------------------------------------------
	$DRILL_TITLE = get_drill_title_name($_SESSION['problem']['unit_num']);


	$css_path = MATERIAL_TEMP_DIR.course_num."/".stage_num."/course.css";
	$make_html = new read_html();
	$make_html->set_dir(STUDENT_TEMP_DIR.course_num."/");
	if (block_type == 4 || block_type == 5 || block_type == 6) {
		// del start oda 2014/07/31 課題要望一覧No270 CSSはAjaxの親で設定しているので不要
		//// すららスタンダード以外のコースでは$MORE_SCRIPTでcssを読み込んでいます。
		//// CSS読み込み用
		//$MORE_SCRIPT .= "<link type=\"text/css\" charset=\"utf-8\" rel=\"stylesheet\" href='".MATERIAL_TEMP_DIR.course_num."/".stage_num."/course.css' />\n";		// update oda 2014/07/31 課題要望一覧No270 IE8でcharsetが必要な為、追加
		//// レッスン毎のCSSを反映させる処理 		下記ディレクトリにCSSファイルがある時のみ反映します
		//$css_path = MATERIAL_TEMP_DIR.course_num."/".stage_num."/".lesson_num."_course.css";
		//if (file_exists($css_path)) {
		//	$MORE_SCRIPT .= "<link type=\"text/css\" charset=\"utf-8\" rel=\"stylesheet\" href=\"$css_path\" />\n";		// update oda 2014/07/31 課題要望一覧No270 IE8でcharsetが必要な為、追加
		//}
		// del end oda 2014/07/31
		//	テンプレート読み込み
		$make_html->set_file("study_block_type5_drill.htm");
	// add start hasegawa 2018/03/09 百マス計算
	} elseif (block_type == 7) {
		$make_html->set_file("study_block_type7_drill.htm");
	// add end hasegawa
	// add start hirose 2018/11/20 英単語マスター
	} elseif (block_type == 8) {
		$make_html->set_file("study_block_type8_drill.htm");
	// add end hirose 2018/11/20 英単語マスター
	} else {
		//	テンプレート読み込み
		$make_html->set_file("drill_ajax.htm");
	}
	$css_file = MATERIAL_TEMP_DIR.course_num."/".stage_num."/course.css";
	if (file_exists(MATERIAL_TEMP_DIR.course_num."/".stage_num."/".lesson_num."_course.css")) {
		$css_file = MATERIAL_TEMP_DIR.course_num."/".stage_num."/".lesson_num."_course.css";
	}
	$INPUTS['CSSPATH'] = array('result'=>'plane','value'=>$css_file);
	$INPUTS['MORESCRIPT'] = array('result'=>'plane','value'=>$MORE_SCRIPT);
	$INPUTS['LESSONUNIT'] = array('result'=>'plane','value'=>$LESSONUNIT);
	$INPUTS['DISPLAYPROBLEMNUM'] = array('result'=>'plane','value'=>$display_problem_num);
	$INPUTS['VOICE'] = array('result'=>'plane','value'=>$voice_button);
	$INPUTS['QUESTION'] = array('result'=>'plane','value'=>$question);
	$INPUTS['PROBLEM'] = array('result'=>'plane','value'=>$problem);
	$INPUTS['EXPLANATION'] = array('result'=>'plane','value'=>$explanation);
	$INPUTS['ANSWERINFO'] = array('result'=>'plane','value'=>$answer_info);
	$INPUTS['RETRYMSG'] = array('result'=>'plane','value'=>$set_retry_msg);
	$INPUTS['ANSWERCOLUMN'] = array('result'=>'plane','value'=>$set_form);
	$INPUTS['FORMSTART'] = array('result'=>'plane','value'=>$form_start);
	$INPUTS['FORMEND'] = array('result'=>'plane','value'=>$form_end);
	$INPUTS['SENDQUESTION'] = array('result'=>'plane','value'=>$send_question);
	$INPUTS['HINT'] = array('result'=>'plane','value'=>$hint);
	$INPUTS['EXPLBUTTON'] = array('result'=>'plane','value'=>$explanation_button);
	$INPUTS['FUNCTIONKEY'] = array('result'=>'plane','value'=>$FUNCTIONKEY);
	$INPUTS['ERROR'] = array('result'=>'plane','value'=>$errors);
	$INPUTS['CHECKVOICE'] = array('result'=>'plane','value'=>$check_voice);

	//	○×画像とゲージを切り替える
	$level_gage  = "\n<!--LEVELGAGEHTML-->\n";
	$level_gage .= $sub_space;
	$INPUTS['LEVELGAGE'] = array('result'=>'plane','value'=>$level_gage);

	// ドリルタイトル用
	$INPUTS['COURSENAME']		= array('result'=>'plane','value'=>$DRILL_TITLE['course_name']);
	$INPUTS['STAGENAME']		= array('result'=>'plane','value'=>$DRILL_TITLE['stage_name']);
	$INPUTS['STAGENAME2']		= array('result'=>'plane','value'=>$DRILL_TITLE['stage_name2']);
	$INPUTS['LESSONNAME']		= array('result'=>'plane','value'=>$DRILL_TITLE['lesson_name']);
	$INPUTS['LESSONNAME2']	= array('result'=>'plane','value'=>$DRILL_TITLE['lesson_name2']);
	$INPUTS['UNITNAME']		= array('result'=>'plane','value'=>$DRILL_TITLE['unit_name']);
	$INPUTS['UNITNAME2']		= array('result'=>'plane','value'=>$DRILL_TITLE['unit_name2']);

	$make_html->set_rep_cmd($INPUTS);
	$html = $make_html->replace();

	return $html;
}


/**
 * 解答表示(正解を見る押下後)
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

	$sql  = "SELECT ".
			" sc.question_flg".
			" FROM ".T_SERVICE_COURSE_LIST." scl ".
			" INNER JOIN ".T_SERVICE." sc ON scl.service_num = sc.service_num ".
			" WHERE scl.course_num='".$_SESSION['course']['course_num']."'".
			"   AND scl.course_type = '1' ".
			"   AND scl.display = '1' ".
			"   AND scl.mk_flg  = '0' ".
			" LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$question_flg = $list['question_flg'];
		if (!defined('question_flg')) {
			define('question_flg', $question_flg);
		}
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

	// add start hasegawa 2018/03/09 百マス計算
	$CHECK_SUCCESS = 1;

	if (form_type == 14) {
		$CORRECT_WORDS = array();
		if ((defined("correct") || correct == "0") || (defined("option1") || option1 == "0")) {
			if (defined("correct") || correct == "0") { $CORRECT = explode("<>", correct); }
			if (defined("option1") || option1 == "0") { $BUD = explode("<>", option1); }

			$correct_max = count($CORRECT);
			$bud_max = count($BUD);
			if ($correct_max > $bud_max) {
				$max = $correct_max;
			} else {
				$max = $bud_max;
			}
			for ($i=0; $i<$max; $i++) {
				if ($BUD[$i] || $BUD[$i] == "0") {
					$CORRECT_WORDS[$i] = $BUD[$i];
				} else {
					$CORRECT_WORDS[$i] = $CORRECT[$i];
				}
			}
		}
		$CHECK_SUCCESS = count($CORRECT_WORDS);
	}
	// add end hasegawa 2018/03/09

	//	解説ページ作成
	$display_problem = new display_problem();
	$display_problem->set_ajax();
	// $display_problem->set_success("1");	// upd hasegawa 2018/03/09 百マス計算
	$display_problem->set_success($CHECK_SUCCESS);
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
	//  誤答の場合、最終画面でも×イメージを表示する
	// if (block_type == 4 || block_type == 5 || block_type == 6) {	// upd hasegawa 2018/03/09 百マス計算
	if (block_type == 4 || block_type == 5 || block_type == 6 || block_type == 7) {
		$display_problem->html5_page_set(true);
	}
	// if ($_SESSION['record']['success'] == 1) {	// upd hasegawa 2018/03/09 百マス計算
	//add start hirose 2018/11/21 すらら英単語
	elseif(block_type == 8){
		//ドリルタイプEはゲージが無いため何もしない
	}
	//add end hirose 2018/11/21 すらら英単語
	
	if ($_SESSION['record']['success'] == $CHECK_SUCCESS) {
		$sub_space = $display_problem->display_area3("start");
	} else {
		$sub_space = $display_problem->display_area3("false");
	}


	// 画面サイズに合わせる
	$size_check = "zoom_all(".$_SESSION['course']['course_num'].");";

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
	// } elseif (form_type == 3 || form_type == 4 || form_type == 10) {	// upd hasegawa 2018/03/09 百マス計算
	} elseif (form_type == 3 || form_type == 4 || form_type == 10 || form_type == 14) {
		$tabindex = $display_problem->set_more_script();
		if (form_type != 4) {
			$answer_info = $display_problem->set_answer_info($_SESSION['record']['answer']);
		} else {
			$answer_info = $display_problem->set_answer_info($_SESSION['record']['answer_info']);
		}
	} elseif (form_type == 11) {
		$MORE_SCRIPT = $display_problem->set_more_script();
		$answer_info = $display_problem->set_answer_info(correct);
	} elseif (form_type == 13) {									// add oda 2016/02/02 作図ツール	// upd hasegawa 2016/04/15
		$MORE_SCRIPT = $display_problem->set_more_script();
		$answer_info = $display_problem->set_answer_info();
	} elseif (form_type == 1 || form_type == 2) {
		$answer_info = $display_problem->set_answer_info();
	//add start kimura 2018/09/13 漢字学習コンテンツ_書写ドリル対応 //書写
	} elseif (form_type == 15) {
		$MORE_SCRIPT = $display_problem->set_more_script();
	//add end   kimura 2018/10/02 漢字学習コンテンツ_書写ドリル対応
	} else {
		$MORE_SCRIPT = "";
	}

	$form_start = $display_problem->form_start($set_form);
	$form_end = $display_problem->form_end($set_form);

	$pre = ".mp3";
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	if (preg_match("/Firefox/i", $user_agent)) { $pre = ".ogg"; }
	$correct_voice_file = "/student/images/drill/false".$pre;

	$make_html = new read_html();
	$make_html->set_dir(STUDENT_TEMP_DIR.course_num."/");
	if (block_type == 4 || block_type == 5 || block_type == 6) {
		// del start oda 2014/07/31 課題要望一覧No270 CSSはAjaxの親で設定しているので不要
		//// すららスタンダード以外のコースでは$MORE_SCRIPTでcssを読み込んでいます。
		//// CSS読み込み用
		//$MORE_SCRIPT .= "<link type=\"text/css\" charset=\"utf-8\" rel=\"stylesheet\" href='".MATERIAL_TEMP_DIR.course_num."/".stage_num."/course.css' />\n";		// update oda 2014/07/31 課題要望一覧No270 IE8でcharsetが必要な為、追加
		//// レッスン毎のCSSを反映させる処理 		下記ディレクトリにCSSファイルがある時のみ反映します
		//$css_path = MATERIAL_TEMP_DIR.course_num."/".stage_num."/".lesson_num."_course.css";
		//if (file_exists($css_path)) {
		//	$MORE_SCRIPT .= "<link type=\"text/css\" charset=\"utf-8\" rel=\"stylesheet\" href=\"$css_path\" />\n";		// update oda 2014/07/31 課題要望一覧No270 IE8でcharsetが必要な為、追加
		//}
		// del end oda 2014/07/31
		//	テンプレート読み込み
		$make_html->set_file("study_block_type5_drill.htm");
	// add start hasegawa 2018/02/26 百マス計算
	} elseif (block_type == 7) {
		$make_html->set_file("study_block_type7_drill.htm");
	// add end hasegawa 2018/02/26
	// add start hirose 2018/11/21 すらら英単語
	} elseif (block_type == 8) {
		$make_html->set_file("study_block_type8_drill.htm");
	// add end hirose 2018/11/21 すらら英単語
	} else {
		//	テンプレート読み込み
		$make_html->set_file("drill_ajax.htm");
	}

	// if (block_type == 4 || block_type == 5 || block_type == 6) {	// upd hasegawa 2018/03/09 百マス計算
	if (block_type == 4 || block_type == 5 || block_type == 6 || block_type == 7) {
		if (get_service_type(course_num) == "3") {
			$INPUTS['COURSENO'] = array('result'=>'plane','value'=>substr(course_name,0,3));
			$unit_image = "";
			if (unit_name == "Grammar") {
				$unit_image = "<img src=\"/student/images/toeic/name_grammar.gif\" width=\"186\" height=\"37\" alt=\"Grammar\" class=\"mgn_btm_20\" />";
			} elseif (unit_name == "Vocabulary") {
				$unit_image = "<img src=\"/student/images/toeic/name_vocabulary.gif\" width=\"186\" height=\"37\" alt=\"Vocabulary\" class=\"mgn_btm_20\" />";
			} elseif (unit_name == "Listening/Reading") {
				$unit_image = "<img src=\"/student/images/toeic/name_listening_reading.gif\" width=\"274\" height=\"37\" alt=\"Listening/Reading\" class=\"mgn_btm_20\" />";
			}
			$INPUTS['UNITIMAGE'] = array('result'=>'plane','value'=>$unit_image);

			$lesson_unit_name = substr(lesson_name,4);
			$INPUTS['UNITCOUNT'] = array('result'=>'plane','value'=>$lesson_unit_name);

			$INPUTS['CLOSEURL'] = array('result'=>'plane','value'=>"window.close();");

			//  解答欄の添え字タグは不要
			if ($answer_info) {
				$answer_info = preg_replace("/<sup>/i", "", $answer_info);
				$answer_info = preg_replace("/<\/sup>/i", "", $answer_info);
			}
		}
	}

	// add start oda 2014/12/17 音声対応
	// 問題文や質問に音声が有った場合は、結果の時に自動再生する
	// 音声データが有った場合は、結果の時に自動再生する
	$check_voice = "";
	$word_voice_file = "";
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	if (defined('explanation')) {
		preg_match_all("|\[!VOICE=(.*)!\]|U",explanation,$VOICE);
		if ($VOICE) {
			foreach ($VOICE[1] AS $key => $VAL) {
				$work_voice = $VAL;
				if (preg_match("/Firefox/i", $user_agent) || preg_match("/Opera/i", $user_agent) || preg_match("/OPR/i", $user_agent)) {
					$work_voice = preg_replace("/.mp3/i", ".ogg", $work_voice);
				}
				$word_voice_file = MATERIAL_VOICE_DIR.course_num."/".stage_num."/".lesson_num."/".unit_num."/".block_num."/". $work_voice;
			}
		}
	}

	// add start oda 2015/01/08
	// 音声が鳴り終わらないうちに画面遷移し、解答すると、意図しない場面で音声が鳴ってしまうので、チェック用の変数を設定する
	$base_id1 = "";
	$base_id2 = "";
	if ($correct_voice_file) {
		$base_id1 = $correct_voice_file;
	}
	if ($word_voice_file) {
		$base_id2 = $word_voice_file;
	}
	// add end oda 2015/01/08

	$check_voice = <<<EOT
<script type="text/javascript">
allSoundStop = 0;
checkFileName1 = '{$base_id1}';
checkFileName2 = '{$base_id2}';
checkFileName3 = '';
setTimeout(function() { play_sound_ajax('sound_ajax', '{$correct_voice_file}', '{$word_voice_file}', ''); }, 4);
</script>
EOT;
	// add end oda 2014/12/17 音声対応

	//---------------------------------------------------------------------------------------------
	// ドリルタイトル名用のコース、ステージ、レッスン、ユニットの名称取得
	//---------------------------------------------------------------------------------------------
	$DRILL_TITLE = get_drill_title_name($_SESSION['problem']['unit_num']);

	$css_file = MATERIAL_TEMP_DIR.course_num."/".stage_num."/course.css";
	if (file_exists(MATERIAL_TEMP_DIR.course_num."/".stage_num."/".lesson_num."_course.css")) {
		$css_file = MATERIAL_TEMP_DIR.course_num."/".stage_num."/".lesson_num."_course.css";
	}
	$INPUTS['CSSPATH'] = array('result'=>'plane','value'=>$css_file);
	$INPUTS['MORESCRIPT'] = array('result'=>'plane','value'=>$MORE_SCRIPT);
	$INPUTS['LESSONUNIT'] = array('result'=>'plane','value'=>$LESSONUNIT);
	$INPUTS['DISPLAYPROBLEMNUM'] = array('result'=>'plane','value'=>$display_problem_num);
	$INPUTS['VOICE'] = array('result'=>'plane','value'=>$voice_button);
	$INPUTS['QUESTION'] = array('result'=>'plane','value'=>$question);
	$INPUTS['PROBLEM'] = array('result'=>'plane','value'=>$problem);
	$INPUTS['EXPLANATION'] = array('result'=>'plane','value'=>$explanation);
	$INPUTS['ANSWERINFO'] = array('result'=>'plane','value'=>$answer_info);
	$INPUTS['FORMSTART'] = array('result'=>'plane','value'=>$form_start);
	$INPUTS['FORMEND'] = array('result'=>'plane','value'=>$form_end);
	$INPUTS['SENDQUESTION'] = array('result'=>'plane','value'=>$send_question);
	$INPUTS['HINT'] = array('result'=>'plane','value'=>$hint);
	$INPUTS['CORRECT'] = array('result'=>'plane','value'=>$set_correct_html);
	$INPUTS['EXPLBUTTON'] = array('result'=>'plane','value'=>$explanation_button);
	$INPUTS['CHECKVOICE'] = array('result'=>'plane','value'=>$check_voice);

	//	○×画像とゲージを切り替える
	$level_gage  = "\n<!--LEVELGAGEHTML-->\n";
	$level_gage .= $sub_space;
	$INPUTS['LEVELGAGE'] = array('result'=>'plane','value'=>$level_gage);

	// ドリルタイトル用
	$INPUTS['COURSENAME']		= array('result'=>'plane','value'=>$DRILL_TITLE['course_name']);
	$INPUTS['STAGENAME']		= array('result'=>'plane','value'=>$DRILL_TITLE['stage_name']);
	$INPUTS['STAGENAME2']		= array('result'=>'plane','value'=>$DRILL_TITLE['stage_name2']);
	$INPUTS['LESSONNAME']		= array('result'=>'plane','value'=>$DRILL_TITLE['lesson_name']);
	$INPUTS['LESSONNAME2']	= array('result'=>'plane','value'=>$DRILL_TITLE['lesson_name2']);
	$INPUTS['UNITNAME']		= array('result'=>'plane','value'=>$DRILL_TITLE['unit_name']);
	$INPUTS['UNITNAME2']		= array('result'=>'plane','value'=>$DRILL_TITLE['unit_name2']);

	$make_html->set_rep_cmd($INPUTS);
	$html = $make_html->replace();

	return $html;

}

/**
 * サービス情報取得
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @param integer $course_num
 * @return integer
 */
function get_service_type($course_num) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$service_type = "";

	$sql  = "SELECT ";
	$sql .= "  sc.setup_type ";
	$sql .= " FROM ".T_SERVICE_COURSE_LIST. " scl ";
	$sql .= " INNER JOIN ".T_SERVICE. " sc ON scl.service_num = sc.service_num ";
	$sql .= " WHERE scl.display = '1' ";
	$sql .= "   AND scl.mk_flg = '0' ";
	$sql .= "   AND scl.course_num = '".$course_num."' ";
	$sql .= "   AND scl.course_type  = '1' ";

	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$service_type = $list['setup_type'];
		}
	}

	return $service_type;
}
?>
