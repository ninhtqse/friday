<?PHP
/**
 * learning system 問題確認プログラム [単語サービス用]
 *
 * ラーニングシステム管理者用画面
 * テスト用プラクティス管理→問題検証→確認ボタン押下
 *
 * @author Azet
 */

/**
 * 判断して、HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[L07]テストを受ける.
 *
 * @author Azet
 * @return string HTML
 */
function display_problem() {
	if ($_POST['action'] == "check") {
		$ERROR = check_answer();
		if ($ERROR && (!$_SESSION['record']['answer'] && $_SESSION['record']['answer'] != "0")) { unset($_POST['action']); }
	}

	if ($_POST['action'] == "check") {//解答チェック
		$html = make_check_html($ERROR);
	} elseif ($_POST['action'] == "answer") {//解答表示
		$html = make_answer_html();
	} else {//問題出題
		$html = make_default_html($ERROR);
	}
	return $html;
}

/**
 * 問題表示
 *
 * AC:[A]管理者 UC1:[L07]テストを受ける.
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

	#TODO kimura 2018/10/29 10:25 すらら英単語
	if (isset($_POST['problem_num'])) {
		unset($_SESSION['problem']);
	}

/****************************************/
//POSTされるのがaction,judgement,answerだけだから$_POST['direct_problem_num'],$_POST['problem_num']の判断不要?
/****************************************/
	//-------------------------
	//問題読み込み＆設定
	//-------------------------
	if ($_POST['direct_problem_num']) {
		// unset($_SESSION['problem']);
		$sql = "";
		$sql.= " SELECT";
		$sql.= " mtp.problem_num, mtp.problem_type, mtp.form_type";
		$sql.= " ,mtp.question, mtp.problem, mtp.voice_data, mtp.hint, mtp.explanation";
		$sql.= " ,mtp.course_num, mtp.standard_time, mtp.parameter";
		$sql.= " ,mtp.first_problem, mtp.latter_problem, mtp.selection_words, mtp.correct";
		$sql.= " ,mtp.option1, mtp.option2, mtp.option3, mtp.option4, mtp.option5";
		$sql.= " ,mtt.write_type";
		$sql.= " ,mtt.test_type_num";
		// $sql.= " ,scl.service_num";
		$sql.= " ,mc2p.test_category1_num";
		$sql.= " ,mc2p.test_category2_num";
		$sql.= " FROM ".T_MS_TEST_CATEGORY2_PROBLEM." mc2p";
		$sql.= " INNER JOIN ".T_MS_TEST_TYPE." mtt";
		$sql.= "  ON mc2p.test_type_num = mtt.test_type_num";
		$sql.= " INNER JOIN ".T_MS_TEST_PROBLEM." mtp";
		$sql.= "  ON mc2p.problem_num = mtp.problem_num AND mtp.mk_flg = '0'";
		// $sql.= " INNER JOIN ".T_SERVICE_COURSE_LIST." scl"; //サービスを登録しなくても問題確認ができるように、コメントｱｳﾄします。
		// $sql.= "  ON mtt.test_type_num = scl.course_num AND course_type = '4'"; //service_course_listテーブルのコースタイプ4が英単語テスト(course_numフィールドはtest_type_numの意味もある!)
		$sql.= " WHERE mc2p.problem_num = '".$_POST['direct_problem_num']."'";

		if ($result = $cdb->query($sql)) {
			$PROBLEM_LIST = $list = $cdb->fetch_assoc($result);
			if ($PROBLEM_LIST) {
				foreach ($PROBLEM_LIST AS $key => $val) {
					$val = replace_decode($val);
					$val = ereg_replace("\n","//",$val);
					$val = ereg_replace("&nbsp;"," ",$val);
					$val = replace_decode($val);
					$LINE[$key] = $val;
				}
				$LINE['problem_table_type'] = $_POST['problem_type'];
				// upd start hirose 2020/09/05 テスト標準化開発
				// if ($LINE['course_num'] == 1) {
				// 	$LINE['stage_num'] = 40;
				// } elseif ($LINE['course_num'] == 2) {
				// 	$LINE['stage_num'] = 31;
				// } elseif ($LINE['course_num'] == 3) {
				// 	$LINE['stage_num'] = 45;
				// 	// テスト用のディレクトリ「test」を作成してcourse.cssを設置します。
				// 	// 本番反映する場合は「プラクティスステージ管理」→「ステージ基本情報」でコースでディレクトリ以下を纏めてアップ。
				// } elseif ($LINE['course_num'] == 15) {
				// 	$LINE['stage_num'] = 'test';
				// } elseif ($LINE['course_num'] == 16) {
				// 	$LINE['stage_num'] = 'test';
				// }
				$LINE['stage_num'] = 'test';
				// upd end hirose 2020/09/05 テスト標準化開発
				$LINE['correct_number'] = 1;
				$LINE['stage_name'] = "テスト専用問題確認：".$list['problem_num'];
				$LINE['lesson_name'] = "&nbsp;";
				$LINE['unit_name'] = "&nbsp;";
			}
		}
		//-------------------------
		// ???
		//-------------------------
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
	$display_problem_num = $display_problem->set_display_problem_num();
	$voice_button = $display_problem->set_voice_button();
	$question = $display_problem->set_question();
	$problem = $display_problem->set_problem();
	$set_form = $display_problem->set_form();

	// ドリルタイトル用
	$stage_name = stage_name . " ";
	$lesson_name = lesson_name . " ";
	$unit_name = unit_name . " ";

	//	ヒント表示
	if (hint_number == 99) {
		$hint = $display_problem->hint_check();
	}

	//	サブスペース(area3)
	$sub_space = $display_problem->display_area3("start");

	if (form_type == 5) {
		$MORE_SCRIPT = $display_problem->set_more_script();
		$answer_info = $display_problem->set_answer_info();
	} elseif (form_type == 8) {
		$MORE_SCRIPT = $display_problem->set_more_script();
	} elseif (form_type == 3 || form_type == 4 || form_type == 10) {
		$MORE_SCRIPT = $display_problem->set_more_script_hand(); //手書き認識
	} elseif (form_type == 11) {
		$MORE_SCRIPT = $display_problem->set_more_script();
		$FUNCTIONKEY = $display_problem->fnkey();

		//02_作業要件/03_molee対応/03_ドリル仕様変更対応/09_修正内容(管理画面問題確認Ajax化)
		//管理画面の問題確認画面がテストかどうかを判別する為の空タグです。
		$MORE_SCRIPT .= "<div id=\"admin_test\" ></div>";
		//作図ツール
	} elseif (form_type == 13) {
		$MORE_SCRIPT = $display_problem->set_more_script();
	} else {
		$MORE_SCRIPT = "";
	}

	$form_start = $display_problem->form_start($set_form);
	$form_end = $display_problem->form_end($set_form);

	// タブレット時の音声対応
	if (is_tablet()) {
		$sound_string = "play_sound_ajax('sound_ajax', '/student/images/drill/silent3sec.mp3', '', '');";
		$form_end = preg_replace("/Form_Check_radio_ajax/", "".$sound_string."Form_Check_radio_ajax", $form_end);
		$form_end = preg_replace("/Form_Check_checkbox_ajax/", "".$sound_string."Form_Check_checkbox_ajax", $form_end);
		$form_end = preg_replace("/Form_Check_textbox_ajax/", "".$sound_string."Form_Check_textbox_ajax", $form_end);
		$form_end = preg_replace("/Form_type8_ajax/", "".$sound_string."Form_type8_ajax", $form_end);
		$form_end = preg_replace("/SubmitAnswerAjax/", "".$sound_string."SubmitAnswerAjax", $form_end);
		$form_end = preg_replace("/SubmitAnswerAjax/", "".$sound_string."SubmitAnswerAjax", $form_end);
		$form_end = preg_replace("/SubmitAnswerDrawingAjax/", "".$sound_string."SubmitAnswerDrawingAjax", $form_end);//作図ツール音声対応
		$form_end = preg_replace("/Form_type16_ajax/", "".$sound_string."Form_type16_ajax", $form_end);//add hirose 2018/12/07 すらら英単語
		// ※並び替えはselect.js（SetInitialPositions()内）でやっています
	}
	
	//add start hirose 2018/11/28 すらら英単語(form_type17)
	if(form_type == 16){
		$form_end = '';
	}
	//add end hirose 2018/11/28 すらら英単語(form_type17)

	// 画面表示時に音を止める
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
	
	//add start hirose 2018/12/06 すらら英単語
	//開発では、動的に動かなくてよいので、固定を入れる
	if(form_type == 16 || form_type == 17){
		$paging = "<span class=\"paging table-cell\">0/0</span>";
		$time = "<span class=\"time table-cell\">残り時間 <span class=\"one-word\">00 : 00 : 00</span></span>";
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


	if (setting_type == 1) { unset($display_problem_num); }
	$make_html = new read_html();

	//読み込むdrill_ajax.htmを設定
	$tpl = get_deepest_file_vocabulary(write_type, test_type_num, test_category1_num, test_category2_num, "drill_ajax.htm");
	$make_html->set_dir($tpl[1]);
	$make_html->set_file("drill_ajax.htm");

	//読み込むcourse.cssを設定
	$css_path = get_deepest_file_vocabulary(write_type, test_type_num, test_category1_num, test_category2_num, "course.css");
	$css_file = "<link rel=\"stylesheet\" href=\"".$css_path[0]."\" type=\"text/css\">";
	$INPUTS['CSSPATH'] = array('result'=>'plane','value'=>$css_file);

	$INPUTS['MORESCRIPT'] = array('result'=>'plane','value'=>$MORE_SCRIPT);
	$INPUTS['DISPLAYPROBLEMNUM'] = array('result'=>'plane','value'=>$display_problem_num);
	$INPUTS['VOICE'] = array('result'=>'plane','value'=>$voice_button);
	$INPUTS['QUESTION'] = array('result'=>'plane','value'=>$question);
	$INPUTS['PROBLEM'] = array('result'=>'plane','value'=>$problem);
	$INPUTS['ANSWERINFO'] = array('result'=>'plane','value'=>$answer_info);
	$INPUTS['ANSWERCOLUMN'] = array('result'=>'plane','value'=>$set_form);
	$INPUTS['FORMSTART'] = array('result'=>'plane','value'=>$form_start);
	$INPUTS['FORMEND'] = array('result'=>'plane','value'=>$form_end);
	$INPUTS['HINT'] = array('result'=>'plane','value'=>$hint);
	$INPUTS['SUBSPACE'] = array('result'=>'plane','value'=>$sub_space);
	$INPUTS['FUNCTIONKEY'] = array('result'=>'plane','value'=>$FUNCTIONKEY);
	$INPUTS['ERROR'] = array('result'=>'plane','value'=>$errors);
	$INPUTS['CHECKVOICE'] = array('result'=>'plane','value'=>$check_voice);//音声

	//ドリルタイトル用
	$INPUTS['STAGENAME']= array('result'=>'plane','value'=>$stage_name);
	$INPUTS['LESSONNAME'] = array('result'=>'plane','value'=>$lesson_name);
	$INPUTS['UNITNAME'] = array('result'=>'plane','value'=>$unit_name);
	//○×画像とゲージを切り替える
	$level_gage  = "\n<!--LEVELGAGEHTML-->\n";
	$level_gage .= $sub_space;
	$INPUTS['LEVELGAGE'] = array('result'=>'plane','value'=>$level_gage);

	$make_html->set_rep_cmd($INPUTS);
	$html = $make_html->replace();

	return $html;
}

/**
 * 解答確認処理
 *
 * AC:[A]管理者 UC1:[L07]テストを受ける.
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
	//作図ツール
	} elseif ($_SESSION['problem']['form_type'] == 13) {
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
	//add start hirose 2018/11/28 すらら英単語(form_type17)
	} elseif ($_SESSION['problem']['form_type'] == 17) {
		$put_text = implode($_POST['answer']);
		unset($_POST['answer']);
		$_POST['answer'][0] = $put_text;
	//add end hirose 2018/11/28 すらら英単語(form_type17)
	} else {
		if ($_SESSION['problem']['form_type'] == 10 && (!$_POST['answer'] && array_search("0",$_POST['answer']) === FALSE) && ($_SESSION['record']['answer'] || $_SESSION['record']['answer'] == "0")) { $flag = 1; }
		if ($flag != 1 && (!$_POST['answer'] && array_search("0",$_POST['answer']) === FALSE)) {
			$ERROR[] = "解答が確認できません。";
		}
	}

	if (!$ERROR && ($_POST['answer'] ||  array_search("0",$_POST['answer']) !== FALSE)) {
		$_SESSION['record']['again']++;
	}

	//問題読み込み＆設定
	if ($_SESSION['problem']) {
		foreach ($_SESSION['problem'] AS $key => $val) {
			if ($val !== "" && $val !== NULL) { define($key,$val); }
		}
	} else {
		$ERROR[] = "問題データーが確認できません。";
	}
	if ($ERROR) { return $ERROR; }

	//問題形式ファイル読み込み
	if (file_exists(LOG_DIR . "problem_lib/form_type_".form_type.".php")) {
		require_once(LOG_DIR . "problem_lib/form_type_".form_type.".php");
		require_once(LOG_DIR . "problem_lib/display_problem.php");
		require_once(LOG_DIR . "problem_lib/correction_problem.php");
	}
	//セッションに解答があれば解答設定
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
 * AC:[A]管理者 UC1:[L07]テストを受ける.
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

	// 変数クリア＆設定
	$check_voice = "";
	$pre = ".mp3";
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	if (preg_match("/Firefox/i", $user_agent) || preg_match("/Opera/i", $user_agent) || preg_match("/OPR/i", $user_agent)) { $pre = ".ogg"; }
	

	$display_timerstartup = 0;
	if ($_SESSION['record']['success'] == 1) {//正解

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

		//音声対応
		$correct_voice_file = "/student/images/drill/true".$pre;

		//サブスペース(area3)
		$sub_space = $display_problem->display_area3("true");

		$display_timerstartup = 1;

	} else {	//	不正解
		$display_problem = new display_problem();
		$display_problem->set_ajax();
		$display_problem->set_success($_SESSION['record']['success']);
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

		//音声対応
		$correct_voice_file = "/student/images/drill/false".$pre;

		//ドリルタイトル用
		$stage_name = stage_name . " ";
		$lesson_name = lesson_name . " ";
		$unit_name = unit_name . " ";

		//	ヒント表示
		if (hint_number > 0 && ($_SESSION['record']['again'] >= hint_number || hint_number == 99) && $_SESSION['record']['again'] < correct_number) {	// update oda 2014/05/08 課題要望一覧No230 99の時も表示する
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
	$size_check = "zoom_all('vocabulary');";

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
		$MORE_SCRIPT = $display_problem->set_more_script_hand(); // add 2016/06/02 yoshizawa 手書き認識
	} elseif (form_type == 11) {
		$MORE_SCRIPT = $display_problem->set_more_script();
		$FUNCTIONKEY = $display_problem->fnkey();
		// add start hasegawa 2016/06/01 作図ツール
	} elseif (form_type == 13) {
		$MORE_SCRIPT = $display_problem->set_more_script();
		// add end hasegawa 2016/06/01
	} else {
		$MORE_SCRIPT = "";
	}


	$form_start = $display_problem->form_start($set_form);
	$form_end = $display_problem->form_end($set_form);
	
	// 問題文や質問に音声が有った場合は、結果の時に自動再生する
	// 音声データが有った場合は、結果の時に自動再生する
	$check_voice = "";
	$word_voice_file = "";
	if ($_SESSION['record']['success'] == 1) {					// 正解の時のみ解説の音声を流すこととする
		if ($voice_button != "") {
			$work_voice = voice_data;
			if (preg_match("/Firefox/i", $user_agent) || preg_match("/Opera/i", $user_agent) || preg_match("/OPR/i", $user_agent)) {
				$work_voice = preg_replace("/.mp3/i", ".ogg", $work_voice);
			}
			//upd start hirose 2018/12/07 すらら英単語(音声自動再生)
//			$word_voice_file = MATERIAL_VOICE_DIR.course_num."/".stage_num."/".lesson_num."/".unit_num."/".block_num."/".$work_voice;
			$word_voice_file = MATERIAL_TEST_VOICE_DIR.change_path(problem_num).$work_voice;
			//upd end hirose 2018/12/07 すらら英単語(音声自動再生)
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
			$word_voice_file = MATERIAL_TEST_VOICE_DIR.change_path(problem_num).$work_voice;
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

//add start hirose 2018/12/07 すらら英単語(音声自動再生)
//以下のタイプは、画面起動時に音声を鳴らしているため、呼び出し元を変更。
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
//add end hirose 2018/12/07 すらら英単語(音声自動再生)
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
}//add hirose 2018/12/07 すらら英単語(音声自動再生)

	//add start hirose 2018/11/28 すらら英単語(form_type17)
	if(form_type == 17 || form_type == 16){
		//add start hirose 2018/12/06 すらら英単語
		//開発では、動的に動かなくてよいので、固定を入れる
		$answer_img = "";
		if(isset($_SESSION['record']['success'])){
			$answer_img = $display_problem->get_answer_img($_SESSION['record']['success']);
		}
		$INPUTS['ANSWERIMG'] = array('result'=>'plane','value'=>$answer_img);
		$paging = "<span class=\"paging table-cell\">0/0</span>";
		$time = "<span class=\"time table-cell\">残り時間 <span class=\"one-word\">00 : 00 : 00</span></span>";
		$INPUTS['PAGING'] = array('result'=>'plane','value'=>$paging);
		$INPUTS['TIME'] = array('result'=>'plane','value'=>$time);
		//add end hirose 2018/12/06 すらら英単語
	}
	//add end hirose 2018/11/28 すらら英単語(form_type17)

	$make_html = new read_html();
	//読み込むdrill_ajax.htmを設定
	$tpl = get_deepest_file_vocabulary(write_type, test_type_num, test_category1_num, test_category2_num, "drill_ajax.htm");
	$make_html->set_dir($tpl[1]);
	$make_html->set_file("drill_ajax.htm");

	//読み込むcourse.cssを設定
	$css_path = get_deepest_file_vocabulary(write_type, test_type_num, test_category1_num, test_category2_num, "course.css");
	$css_file = "<link rel=\"stylesheet\" href=\"".$css_path[0]."\" type=\"text/css\">";
	$INPUTS['CSSPATH'] = array('result'=>'plane','value'=>$css_file);
	$INPUTS['MORESCRIPT'] = array('result'=>'plane','value'=>$MORE_SCRIPT);
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
	$INPUTS['HINT'] = array('result'=>'plane','value'=>$hint);
	$INPUTS['EXPLBUTTON'] = array('result'=>'plane','value'=>$explanation_button);
	$INPUTS['SUBSPACE'] = array('result'=>'plane','value'=>$sub_space);
	$INPUTS['FUNCTIONKEY'] = array('result'=>'plane','value'=>$FUNCTIONKEY);
	$INPUTS['ERROR'] = array('result'=>'plane','value'=>$errors);
	$INPUTS['CHECKVOICE'] = array('result'=>'plane','value'=>$check_voice);

	// ドリルタイトル用
	$INPUTS['STAGENAME']		= array('result'=>'plane','value'=>$stage_name);
	$INPUTS['LESSONNAME']		= array('result'=>'plane','value'=>$lesson_name);
	$INPUTS['UNITNAME']		= array('result'=>'plane','value'=>$unit_name);
	//	○×画像とゲージを切り替える
	$level_gage  = "\n<!--LEVELGAGEHTML-->\n";
	$level_gage .= $sub_space;
	$INPUTS['LEVELGAGE'] = array('result'=>'plane','value'=>$level_gage);

	$make_html->set_rep_cmd($INPUTS);
	$html = $make_html->replace();


	return $html;
}


/**
 * 正解表示
 *
 * AC:[A]管理者 UC1:[L07]テストを受ける.
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

	//	解説ページ作成
	$display_problem = new display_problem();
	$display_problem->set_ajax();
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

	// ドリルタイトル用
	$stage_name = stage_name . " ";
	$lesson_name = lesson_name . " ";
	$unit_name = unit_name . " ";

	//	サブスペース(area3)
	//  誤答の場合、最終画面でも×イメージを表示する
	if ($_SESSION['record']['success'] == 1) {
		$sub_space = $display_problem->display_area3("start");
	} else {
		$sub_space = $display_problem->display_area3("false");
	}

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
		//作図ツール
	} elseif (form_type == 13) {
		$MORE_SCRIPT = $display_problem->set_more_script();
		$answer_info = $display_problem->set_answer_info();
	} elseif (form_type == 1 || form_type == 2) {
		$answer_info = $display_problem->set_answer_info();
	} else {
		$MORE_SCRIPT = "";
	}

	$form_start = $display_problem->form_start($set_form);
	$form_end = $display_problem->form_end($set_form);

	$pre = ".mp3";
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	if (preg_match("/Firefox/i", $user_agent)) { $pre = ".ogg"; }
	$correct_voice_file = "/student/images/drill/false".$pre;

	//音声対応
	//問題文や質問に音声が有った場合は、結果の時に自動再生する
	//音声データが有った場合は、結果の時に自動再生する
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

	// 音声が鳴り終わらないうちに画面遷移し、解答すると、意図しない場面で音声が鳴ってしまうので、チェック用の変数を設定する
	$base_id1 = "";
	$base_id2 = "";
	if ($correct_voice_file) {
		$base_id1 = $correct_voice_file;
	}
	if ($word_voice_file) {
		$base_id2 = $word_voice_file;
	}

	$check_voice = <<<EOT
<script type="text/javascript">
allSoundStop = 0;
checkFileName1 = '{$base_id1}';
checkFileName2 = '{$base_id2}';
checkFileName3 = '';
setTimeout(function() { play_sound_ajax('sound_ajax', '{$correct_voice_file}', '{$word_voice_file}', ''); }, 4);
</script>
EOT;

	$make_html = new read_html();
	//読み込むdrill_ajax.htmを設定
	$tpl = get_deepest_file_vocabulary(write_type, test_type_num, test_category1_num, test_category2_num, "drill_ajax.htm");
	$make_html->set_dir($tpl[1]);
	$make_html->set_file("drill_ajax.htm");

	//読み込むcourse.cssを設定
	$css_path = get_deepest_file_vocabulary(write_type, test_type_num, test_category1_num, test_category2_num, "course.css");
	$css_file = "<link rel=\"stylesheet\" href=\"".$css_path[0]."\" type=\"text/css\">";
	$INPUTS['CSSPATH'] = array('result'=>'plane','value'=>$css_file);
	$INPUTS['MORESCRIPT'] = array('result'=>'plane','value'=>$MORE_SCRIPT);
	$INPUTS['DISPLAYPROBLEMNUM'] = array('result'=>'plane','value'=>$display_problem_num);
	$INPUTS['VOICE'] = array('result'=>'plane','value'=>$voice_button);
	$INPUTS['QUESTION'] = array('result'=>'plane','value'=>$question);
	$INPUTS['PROBLEM'] = array('result'=>'plane','value'=>$problem);
	$INPUTS['EXPLANATION'] = array('result'=>'plane','value'=>$explanation);
	$INPUTS['ANSWERINFO'] = array('result'=>'plane','value'=>$answer_info);
	$INPUTS['FORMSTART'] = array('result'=>'plane','value'=>$form_start);
	$INPUTS['FORMEND'] = array('result'=>'plane','value'=>$form_end);
	$INPUTS['CORRECT'] = array('result'=>'plane','value'=>$set_correct_html);
	$INPUTS['SUBSPACE'] = array('result'=>'plane','value'=>$sub_space);
	$INPUTS['CHECKVOICE'] = array('result'=>'plane','value'=>$check_voice);

	//ドリルタイトル用
	$INPUTS['STAGENAME']		= array('result'=>'plane','value'=>$stage_name);
	$INPUTS['LESSONNAME']		= array('result'=>'plane','value'=>$lesson_name);
	$INPUTS['UNITNAME']		= array('result'=>'plane','value'=>$unit_name);
	//○×画像とゲージを切り替える
	$level_gage  = "\n<!--LEVELGAGEHTML-->\n";
	$level_gage .= $sub_space;
	$INPUTS['LEVELGAGE'] = array('result'=>'plane','value'=>$level_gage);

	$make_html->set_rep_cmd($INPUTS);
	$html = $make_html->replace();

	return $html;
}
?>
