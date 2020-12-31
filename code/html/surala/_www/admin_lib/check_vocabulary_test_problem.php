<?PHP
/**
 *
 * e-learning system 問題確認プログラム 単語サービス用
 *
 * @author Azet
 */

/**
 * HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[L07]テストを受ける.
 *
 * @author Azet
 * @return string HTML
 */
function display_problem() {
	return make_default_html(); //問題出題
}

/**
 * 問題表示
 *
 * AC:[A]管理者 UC1:[L07]テストを受ける.
 *
 * @author Azet
 * @return string HTML
 */
function make_default_html() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
	$LINE = array();

	//	解答ログリセット
	unset($_SESSION['record']);
	//-------------------------
	//問題読み込み＆設定
	//-------------------------
	if ($_POST['direct_problem_num']) {

		unset($_SESSION['problem']);

		// update start 2018/12/18 yoshizawa SQLの条件不足と不要なロジックを精査いたします。
		// //************************************************************//
		// //ここでSELECTしたフィールド名がそのまま定数として使用されます
		// //************************************************************//
		// $sql = "";
		// $sql.= " SELECT";
		// $sql.= " mtp.problem_num, mtp.problem_type, mtp.form_type";
		// $sql.= " ,mtp.question, mtp.problem, mtp.voice_data, mtp.hint, mtp.explanation";
		// $sql.= " ,mtp.course_num, mtp.standard_time, mtp.parameter";
		// $sql.= " ,mtp.first_problem, mtp.latter_problem, mtp.selection_words, mtp.correct";
		// $sql.= " ,mtp.option1, mtp.option2, mtp.option3, mtp.option4, mtp.option5";
		// $sql.= " ,mtt.write_type";
		// $sql.= " ,mtt.test_type_num";
		// // $sql.= " ,scl.service_num";
		// $sql.= " ,mc2p.test_category1_num";
		// $sql.= " ,mc2p.test_category2_num";
		// $sql.= " ,mc2p.list_num";//add hirose 2018/12/17 すらら英単語
		// $sql.= " ,mc2.test_category2_name";//add hirose 2018/12/17 すらら英単語
		// $sql.= " ,mc2.remarks";//add hirose 2018/12/17 すらら英単語
		// $sql.= " FROM ".T_MS_TEST_CATEGORY2_PROBLEM." mc2p";
		// $sql.= " INNER JOIN ".T_MS_TEST_TYPE." mtt";
		// $sql.= "  ON mc2p.test_type_num = mtt.test_type_num";
		// $sql.= " INNER JOIN ".T_MS_TEST_PROBLEM." mtp";
		// $sql.= "  ON mc2p.problem_num = mtp.problem_num AND mtp.mk_flg = '0'";
		// // $sql.= " INNER JOIN ".T_SERVICE_COURSE_LIST." scl"; //サービスを登録しなくても問題確認ができるように、コメントｱｳﾄします。
		// // $sql.= "  ON mtt.test_type_num = scl.course_num AND course_type = '4'"; //service_course_listテーブルのコースタイプ4が英単語テスト(course_numフィールドはtest_type_numの意味もある!)
		// //add start hirose 2018/12/17 すらら英単語
		// $sql.= " INNER JOIN ".T_MS_TEST_CATEGORY2." mc2";
		// $sql.= "  ON mc2.test_category2_num = mc2p.test_category2_num AND mc2p.mk_flg = '0'";
		// //add end hirose 2018/12/17 すらら英単語
		// $sql.= " WHERE mc2p.problem_num = '".$_POST['direct_problem_num']."'";
		// 
		// if ($result = $cdb->query($sql)) {
		// 	$PROBLEM_LIST = $list = $cdb->fetch_assoc($result);
		// 	if ($PROBLEM_LIST) {
		// 		foreach ($PROBLEM_LIST AS $key => $val) {
		// 			$val = replace_decode($val);
		// 			$val = ereg_replace("\n","//",$val);
		// 			$val = ereg_replace("&nbsp;"," ",$val);
		// 			$val = replace_decode($val);
		// 			$LINE[$key] = $val;
		// 		}
		// 		$LINE['problem_table_type'] = $_POST['problem_type'];
		// 		if ($LINE['course_num'] == 1) {
		// 			$LINE['stage_num'] = 40;
		// 		} elseif ($LINE['course_num'] == 2) {
		// 			$LINE['stage_num'] = 31;
		// 		} elseif ($LINE['course_num'] == 3) {
		// 			$LINE['stage_num'] = 45;
		// 			// テスト用のディレクトリ「test」を作成してcourse.cssを設置します。
		// 			// 本番反映する場合は「プラクティスステージ管理」→「ステージ基本情報」でコースでディレクトリ以下を纏めてアップ。
		// 		} elseif ($LINE['course_num'] == 15) {
		// 			$LINE['stage_num'] = 'test';
		// 		} elseif ($LINE['course_num'] == 16) {
		// 			$LINE['stage_num'] = 'test';
		// 		}
		// 		$LINE['correct_number'] = 1;
		// 		//upd start hirose 2018/12/17 すらら英単語
		// 		// $LINE['stage_name'] = "テスト専用問題確認：".$list['problem_num'];
		// 		$LINE['stage_name'] = $list['test_category2_name'];
		// 		//upd end hirose 2018/12/17 すらら英単語
		// 		$LINE['lesson_name'] = "&nbsp;";
		// 		$LINE['unit_name'] = "&nbsp;";
		// 		$LINE['remarks'] = $list['remarks']; //add hirose 2018/12/17 すらら英単語
		// 	}
		// }
		// 
		//************************************************************//
		//ここでSELECTしたフィールド名がそのまま定数として使用されます
		//************************************************************//
		$sql = "";
		$sql.= " SELECT";
		$sql.= " mtp.problem_num, mtp.problem_type, mtp.form_type";
		$sql.= " ,mtp.question, mtp.problem, mtp.voice_data, mtp.hint, mtp.explanation";
		$sql.= " ,mtp.course_num, mtp.standard_time, mtp.parameter";
		$sql.= " ,mtp.first_problem, mtp.latter_problem, mtp.selection_words, mtp.correct";
		$sql.= " ,mtp.option1, mtp.option2, mtp.option3, mtp.option4, mtp.option5";
		$sql.= " ,mtt.write_type";
		$sql.= " ,mtt.test_type_num";
		$sql.= " ,mc2p.test_category1_num";
		$sql.= " ,mc2p.test_category2_num";
		$sql.= " ,mc2p.list_num";
		$sql.= " ,mc2.test_category2_name";
		$sql.= " ,mc2.remarks";
		$sql.= " FROM ".T_MS_TEST_PROBLEM." mtp";
		$sql.= " INNER JOIN ".T_MS_TEST_CATEGORY2_PROBLEM." mc2p";
		$sql.= "  ON mtp.problem_num = mc2p.problem_num";
		$sql.= "  AND mc2p.problem_table_type = '3'";	// すらら英単語テスト専用問題
		$sql.= " INNER JOIN ".T_MS_TEST_CATEGORY2." mc2";
		$sql.= "  ON mc2p.test_type_num = mc2.test_type_num";
		$sql.= "  AND mc2p.test_category1_num = mc2.test_category1_num";
		$sql.= "  AND mc2p.test_category2_num = mc2.test_category2_num";
		$sql.= " INNER JOIN ".T_MS_TEST_CATEGORY1." mc1";
		$sql.= "  ON mc2.test_type_num = mc1.test_type_num";
		$sql.= "  AND mc2.test_category1_num = mc1.test_category1_num";
		$sql.= " INNER JOIN ".T_MS_TEST_TYPE." mtt";
		$sql.= "  ON mc1.test_type_num = mtt.test_type_num";
		$sql.= " WHERE mtp.mk_flg = '0' ";
		$sql.= " AND mc2p.problem_num = '".$_POST['direct_problem_num']."'";
		$sql.= " AND mc2p.mk_flg = '0'";
		$sql.= " AND mc2.test_category2_num = '".$_POST['test_category2_num']."'";
		$sql.= " AND mc2.mk_flg = '0'";
		$sql.= " AND mc1.test_category1_num = '".$_POST['test_category1_num']."'";
		$sql.= " AND mc1.mk_flg = '0'";
		$sql.= " AND mtt.test_type_num = '".$_POST['test_type_num']."'";
		$sql.= " AND mtt.mk_flg = '0'";
		$sql.= ";";
		
		if ($result = $cdb->query($sql)) {
			$PROBLEM_LIST = $list = $cdb->fetch_assoc($result);
			if ($PROBLEM_LIST) {
				foreach ($PROBLEM_LIST AS $key => $val) {
					$val = replace_decode($val);
					$val = preg_replace("/\n/","//",$val);
					$val = preg_replace("/&nbsp;/"," ",$val);
					$val = replace_decode($val);
					$LINE[$key] = $val;
				}
				$LINE['problem_table_type'] = $_POST['problem_type'];
				$LINE['correct_number'] = 1;
				$LINE['stage_name'] = $list['test_category2_name'];
				$LINE['lesson_name'] = "&nbsp;";
				$LINE['unit_name'] = "&nbsp;";
				$LINE['remarks'] = $list['remarks'];
			}
		}
		// update end 2018/12/18 yoshizawa

	//-------------------------
	// ???
	//-------------------------
	} else {
		foreach ($_SESSION['problem'] AS $key => $val) {
			$LINE[$key] = $val;
		}
	}
	foreach ($LINE AS $key => $val) {
		$val = replace_decode($val);
		if ($key == "selection_words" || $key == "correct" || $key == "option1") {
			$val = preg_replace("/\/\//","\n",$val);
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
	
	//	問題設定
	$_SESSION['record']['set_time'] = time();
	$display_problem = new display_problem();
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
	$display_problem->display_area3("start");

	$start_button  = "<div id=\"start_set\">\n";
	$start_button .= "<div id=\"drill_start_button\">\n";
	$start_button .= "</div>\n";
	$start_button .= "</div>\n";
	//upd start hirose 2019/02/27 すらら英単語
	//core側ではSTART画面を挟んでおり、drill_start()とともにCSSを切り替えているため。
	//$start_button .= "<script type=\"text/javascript\">drill_start('admin_vocabulary_check_test_problem');</script>";
	$start_button .= "<script type=\"text/javascript\">disp_header();drill_start('admin_vocabulary_check_test_problem');</script>";
	//upd end hirose 2019/02/27 すらら英単語

	// BG MUSIC  Firefox対応
	$pre = ".mp3";
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	if (preg_match("/Firefox/i", $user_agent) || preg_match("/Opera/i", $user_agent) || preg_match("/OPR/i", $user_agent)) { $pre = ".ogg"; }

	$bg_music = "";
	$music_file = MATERIAL_TEMP_DIR.course_num."/drill_bg_music".$pre;
	if (file_exists($music_file) && $list['bgm_set'] == '1') {//(1=BGM有り,2=BGM無し）
		$bg_music .= "<audio id=\"bgm\" src=\"".$music_file."\" loop onended=\"this.play()\"></audio>\n";
	} else {// BGM無しの時は無音BGMをセットし3秒間空ける
		//無音BGMが無いとIPADで正解不正解の音が鳴らないので追加
		$bg_music .= "<audio id=\"bgm\" src=\"/student/images/drill/silent3sec".$pre."\" loop onended=\"this.play()\"></audio>\n";
	}

	// 画面サイズに合わせる
	$size_check = "zoom_all('vocabulary');";

	$ONLOAD_BODY = "";

	// 並べ替えの問題
	if (form_type == 5) {
		$MORE_SCRIPT = $display_problem->set_more_script();
		$answer_info = $display_problem->set_answer_info();
		$ONLOAD_BODY = "<body onload=\"".$size_check."\">";

		// ドラッグ＆クリックの問題
	} elseif (form_type == 8) {
		$MORE_SCRIPT = $display_problem->set_more_script();
		$ONLOAD_BODY = "<body onload=\"make_ele();".$size_check."\">";

		//	テキストボックス＆複数テキストボックス＆html作成
	} elseif (form_type == 3 || form_type == 4 || form_type == 10) {
		$tabindex = $display_problem->set_more_script();
		if ($tabindex > 0) {
			$ONLOAD_BODY = "<body onload=\"".$size_check."\">";
		} else {
			$ONLOAD_BODY = "";
		}

		// 数式パレット式　（html作成）の問題
	} elseif (form_type == 11) {
		$MORE_SCRIPT = $display_problem->set_more_script();
		$FUNCTIONKEY = $display_problem->fnkey();
	//add start hirose 2018/12/07 すらら英単語
	} elseif (form_type == 16) {
		$size_check= "";
	//add end hirose 2018/12/07 すらら英単語
	//add start hirose 2018/11/22 すらら英単語
	} elseif (form_type == 17) {
		$size_check= "";//add hirose 2018/12/07 すらら英単語
		$MORE_SCRIPT= $display_problem->set_more_script();
		//add start hirose 2018/12/19 すらら英単語
		$ONLOAD_BODY = "";
		if(apple_check()){
			$ONLOAD_BODY = "<body class=\"apple-dv\">";
		}
		//add end hirose 2018/12/19 すらら英単語
	//add end hirose 2018/11/22 すらら英単語
	} else {
		$MORE_SCRIPT = "";
		$ONLOAD_BODY = "";
	}
	
	
	if ($ONLOAD_BODY == "" && $size_check){
		$ONLOAD_BODY = "<body onLoad=\"".$size_check."\">";
	}

	// 現在、ajax化を行った後にIE8以下のブラウザで画面ズレが起きているので
	// ドキュメントモードを指定してあげる必要がある
	// IE8 or IE7
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	if (preg_match('/MSIE 8/',$user_agent) || preg_match('/MSIE 7/',$user_agent)) {
		$docment = "<meta http-equiv=\"X-UA-Compatible\" content=\"IE=5\">\n";
		$INPUTS['DOCUMENTMODE'] = array('result'=>'plane','value'=>$docment);
	}

	//----- 数式パレット/音声準備 --------
	// IE8またはIE7の場合は、旧数式パレットを使用するので、数式パレットのscriptタグは出力しない
	// IE8 or IE7
	if (preg_match('/MSIE 8/',$user_agent) || preg_match('/MSIE 7/',$user_agent)) {
		$exec_calc_tag1 = "";
		$exec_calc_tag2 = "";

		$sound_object = "<object classid=\"clsid:d27cdb6e-ae6d-11cf-96b8-444553540000\" codebase=\"http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0\" width=\"0\" height=\"0\" id=\"sound_ajax_flash\">";
		$sound_object .= "<param name=\"movie\" value=\"/student/images/drill/play_sound.swf?file=/student/images/drill/silent3sec.mp3\" />";
		$sound_object .= "<embed src=\"/student/images/drill/play_sound.swf?file=/student/images/drill/silent3sec.mp3\" width=\"0\" height=\"0\" allowScriptAccess=\"sameDomain\" type=\"application/x-shockwave-flash\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" >";
		$sound_object .= "</embed>";
		$sound_object .= "</object>";
	} else {
		$exec_calc_tag1 = "<script type='text/javascript' src='/javascript/userAgent.js'></script>\n"; // kaopiz add 2020/08/15 ipados13
		$exec_calc_tag1 .= ex_calc_js($_SESSION['TEGAKI_FLAG']);
		$exec_calc_tag1 .= "<script type='text/javascript' src='/javascript/html5_tools.js'></script>\n";
		$exec_calc_tag1 .= "<script type='text/javascript' src='/student/javascript/html5_palette.js'></script>\n";

		$exec_calc_tag2 = "<div class=\"ExCalcHtmlKeyboard\" style=\"position:absolute;top:0;left:1070px;z-index:999;\"></div>\n";
		$exec_calc_tag2 .= "<script type='text/javascript'>repositionCanvasPalette()</script>\n";

		// $sound_object = "<audio src=\"\" id=\"sound_ajax\" style=\"display:none;\"></audio>";															// del karasawa 2019/11/18 課題要望800
		$sound_object = "<audio src=\"/student/images/drill/silent3sec".$pre."\" id=\"sound_ajax\" id=\"sound_ajax\" style=\"display:none;\"></audio>";		// add karasawa 2019/11/18 課題要望800
	}
	//---------------------------------------------------------------------------------------------------------------
	//add start hirose 2018/12/06 すらら英単語
	$stop_form ="";
	$header_area ="";
	if(form_type == 16 || form_type == 17){
		//$stop_form = $display_problem ->stop_form(); //del hirose 2019/1/7 すらら英単語
		//$header_area = $display_problem ->header_area('1',true);
		$header_area = $display_problem ->header_area('test',$_POST['test_type_num']);//upd hirose 2019/02/27 すらら英単語
		$voice_path = change_path(problem_num);
		$word_voice_file = MATERIAL_TEST_VOICE_DIR.$voice_path.voice_data;
		//add start hirose 2018/12/17 すらら英単語
		if(defined('remarks')){
			$remarks = "<p>".remarks."</p>";
			$INPUTS['REMARKS'] = array('result'=>'plane','value'=>$remarks);
		}
		//add end hirose 2018/12/17 すらら英単語
		// add start 2019/02/19 yoshizawa すらら英単語
		// form_type=17（書く）の場合、問題表示時に音声ボタンを非表示、自動再生もしません。
		if(form_type == 17){
			$word_voice_file = "";
			$voice_button = "";
		}
		// add end 2019/02/19 yoshizawa すらら英単語
		// update 2019/09/02 yoshizawa id="sound_ajax"が消えない様に「=」→「.=」に変更いたします。
		$sound_object .= <<<EOT
<script type="text/javascript">
setTimeout(function() { opener_play_sound_ajax('sound_ajax', '{$word_voice_file}', '', '');sound_stop_preparation(); }, 4);
document.body.onbeforeunload = function() { opener_play_sound_ajax('sound_ajax', '/student/images/drill/silent3sec.mp3', '', ''); }
</script>
EOT;
	}
	//ページごとに、親クラスを切り替える
	if(form_type == 16){
		$INPUTS['PARENTID'] = array('result'=>'plane','value'=>'unit');
		$INPUTS['DESCAREACSS'] = array('result'=>'plane','value'=>'base2');
	}elseif(form_type == 17){
		$INPUTS['PARENTID'] = array('result'=>'plane','value'=>'unit-translation');
		$INPUTS['DESCAREACSS'] = array('result'=>'plane','value'=>'base3');
	}
	//add end hirose 2018/12/06 すらら英単語

	//IE10以降のバージョンだとformがHeightを取ってしまいレイアウトに影響があるため変更する
	$ajax_form .= "<div id=\"ajax_course_num\" style=\"display:none;\">\n";
	$ajax_form .= course_num;
	$ajax_form .= "</div>\n";
	//-----

	$form_start = $display_problem->form_start($set_form);
	$form_end = $display_problem->form_end($set_form);
	
	
	if (setting_type == 1) { unset($display_problem_num); }
	$make_html = new read_html();

	//読み込むdrill_ajax.htmを設定
	$tpl = get_deepest_file_vocabulary(write_type, test_type_num, test_category1_num, test_category2_num, "drill.htm");
	$make_html->set_dir($tpl[1]);
	$make_html->set_file("drill.htm");

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
	$INPUTS['FUNCTIONKEY'] = array('result'=>'plane','value'=>$FUNCTIONKEY);
	$INPUTS['EXECCALC1'] = array('result'=>'plane','value'=>$exec_calc_tag1);
	$INPUTS['EXECCALC2'] = array('result'=>'plane','value'=>$exec_calc_tag2);
	$INPUTS['SOUNDOBJ'] = array('result'=>'plane','value'=>$sound_object);

	// ドリルタイトル用
	$INPUTS['STAGENAME']		= array('result'=>'plane','value'=>$stage_name);
	$INPUTS['LESSONNAME']		= array('result'=>'plane','value'=>$lesson_name);
	$INPUTS['UNITNAME']		= array('result'=>'plane','value'=>$unit_name);

	$INPUTS['STARTBUTTON'] = array('result'=>'plane','value'=>$start_button);
	$INPUTS['BGMUSIC'] = array('result'=>'plane','value'=>$bg_music);
	$INPUTS['AJAXFORM'] = array('result'=>'plane','value'=>$ajax_form);
	// -----
	$INPUTS['STOPFORM'] = array('result'=>'plane','value'=>$stop_form); //add hirose 2018/12/04 すらら英単語
	$INPUTS['HEADERAREA'] = array('result'=>'plane','value'=>$header_area); //add hirose 2018/12/04 すらら英単語

	$make_html->set_rep_cmd($INPUTS);
	$html = $make_html->replace();
	if ($ONLOAD_BODY) { $html = preg_replace("/<body>/",$ONLOAD_BODY,$html); }

	return $html;

}
?>
