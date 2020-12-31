<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * e-learning system 問題確認プログラム
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

	/*	del start 2014/03/31 yoshizawa 02_作業要件/03_molee対応/03_ドリル仕様変更対応/09_修正内容(管理画面問題確認Ajax化)
	if ($_POST['action'] == "check") {
		$ERROR = check_answer();
		//if ($ERROR && !$_SESSION['record']['answer']) { unset($_POST['action']); }	//	del ookawara 2009/08/10
		if ($ERROR && (!$_SESSION['record']['answer'] && $_SESSION['record']['answer'] != "0")) { unset($_POST['action']); }	//	add ookawara 2009/08/10
	}

	if ($_POST['action'] == "check") {	//	解答チェック
		$html = make_check_html($ERROR);
	} elseif ($_POST['action'] == "answer") {	//	解答表示
		$html = make_answer_html();
	} else {	//	問題出題
		$html = make_default_html($ERROR);
	}
		del end 2014/03/31 yoshizawa 02_作業要件/03_molee対応/03_ドリル仕様変更対応/09_修正内容(管理画面問題確認Ajax化)	*/

	//	add 2014/03/31 yoshizawa 02_作業要件/03_molee対応/03_ドリル仕様変更対応/09_修正内容(管理画面問題確認Ajax化)
	$html = make_default_html($ERROR);	//	問題出題

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

//$LINE = array();

	//	解答ログリセット
	unset($_SESSION['record']);

	if (isset($_POST['problem_num'])) {
		unset($_SESSION['problem']);
		foreach($_POST as $key => $val) {
			$val = urldecode($val);
			$LINE[$key] = replace_word($val);
		}
		if ($LINE[problem_type] == 1 || $LINE[problem_type] == 2) {
			$LINE[block_type] = 2;
		} else {
			$LINE[block_type] = 1;
		}

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
			if ($_POST['problem_type'] == 1) {
				$sql  = "SELECT problem.problem_num, problem.display_problem_num, problem.problem_type, problem.sub_display_problem_num, problem.form_type,".
						" problem.question, problem.problem, problem.voice_data, problem.hint, problem.explanation,".
						" problem.answer_time, problem.parameter, problem.set_difficulty, problem.hint_number, problem.correct_number,".
						" problem.clear_number,problem. first_problem, problem.latter_problem, problem.selection_words, problem.correct,".
						" problem.option1, problem.option2, problem.option3, problem.option4, problem.option5,".
//						"course.course_num,course.write_type,stage.stage_num,stage.stage_name,lesson.lesson_name,lesson.lesson_num,unit.unit_num,unit.unit_name,block.block_num".
						"course.course_num,course.write_type,stage.stage_num,stage.stage_name,lesson.lesson_name,lesson.lesson_num,unit.unit_num,unit.unit_name,unit.remarks,block.block_num".//upd hirose 2018/12/17 すらら英単語　unit.remarksを追加
						" FROM ".T_PROBLEM." ".T_PROBLEM.
						" INNER JOIN ".T_COURSE." ".T_COURSE." ON problem.course_num=course.course_num".
						" INNER JOIN ".T_BLOCK." ".T_BLOCK." ON problem.block_num=block.block_num".
						" INNER JOIN ".T_UNIT." ".T_UNIT." ON block.unit_num=unit.unit_num".
						" INNER JOIN ".T_LESSON." ".T_LESSON." ON block.lesson_num=lesson.lesson_num".
						" INNER JOIN ".T_STAGE." ".T_STAGE." ON block.stage_num=stage.stage_num".
						" WHERE problem.problem_num='".$_POST['direct_problem_num']."'".
						" AND problem.state!='1'".
						" LIMIT 1;";

				if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
				if ($result = $cdb->query($sql)) {
					$PROBLEM_LIST = $list = $cdb->fetch_assoc($result);
					if ($PROBLEM_LIST) {
						foreach ($PROBLEM_LIST AS $key => $val) {
							$val = replace_decode($val);
							$val = ereg_replace("\n","//",$val);
							$val = ereg_replace("&nbsp;"," ",$val);
							$val = replace_decode($val);

							//$LINE[$key] = replace_word($val);		//del okabe 2018/08/24 理科 問題表示文字調整「，＋ー」
							//add start okabe 2018/08/24 理科 問題表示文字調整「，＋ー」
							//テスト用プラクティス管理→問題設定 または 問題検証 で "すらら"問題の [確認] ボタンを押したとき。
							if (intval($list['write_type']) == 15) {	//理科の場合
								$argsx = array('val'=>$val, 'key'=>$key, 'form_type'=>$PROBLEM_LIST['form_type']);
								$LINE[$key] = filter_science_datasx($argsx);
							} else {
								$LINE[$key] = replace_word($val);
							}
							//add end okabe 2018/08/24

							//$LINE[$key] = $val;
						}
					}
				}
			}
			if ($_POST['problem_type'] == 2) {
				$sql  = "SELECT mtp.problem_num, mtp.problem_type, mtp.form_type,".
						" mtp.question, mtp.problem, mtp.voice_data, mtp.hint, mtp.explanation,".
						" mtp.course_num, mtp.standard_time, mtp.parameter,".
						" mtp.first_problem, mtp.latter_problem, mtp.selection_words, mtp.correct,".
						" mtp.option1, mtp.option2, mtp.option3, mtp.option4, mtp.option5,".
						"course.write_type".
						" FROM ".T_MS_TEST_PROBLEM." mtp".
						" INNER JOIN ".T_COURSE." ".T_COURSE." ON mtp.course_num=course.course_num".
						" WHERE mtp.problem_num='".$_POST['direct_problem_num']."'".
						" AND mtp.mk_flg='0'".
						" LIMIT 1;";

				if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
				if ($result = $cdb->query($sql)) {
					$PROBLEM_LIST = $list = $cdb->fetch_assoc($result);
					if ($PROBLEM_LIST) {
						foreach ($PROBLEM_LIST AS $key => $val) {
							$val = replace_decode($val);
							$val = ereg_replace("\n","//",$val);
							$val = ereg_replace("&nbsp;"," ",$val);
							$val = replace_decode($val);
							//$LINE[$key] = replace_word($val);
							$LINE[$key] = $val;
						}
						$LINE['problem_table_type'] = $_POST['problem_type'];
						// del start hirose 2020/09/01 テスト標準化開発
						// if ($LINE['course_num'] == 1) {
						// 	$LINE['stage_num'] = 40;
						// } elseif ($LINE['course_num'] == 2) {
						// 	$LINE['stage_num'] = 31;
						// } elseif ($LINE['course_num'] == 3) {
						// 	$LINE['stage_num'] = 45;
						// // add start 2018/05/18 yoshizawa 理科社会対応
						// // テスト用のディレクトリ「test」を作成してcourse.cssを設置します。
						// // 本番反映する場合は「プラクティスステージ管理」→「ステージ基本情報」でコースでディレクトリ以下を纏めてアップ。
						// } elseif ($LINE['course_num'] == 15) {
						// 	$LINE['stage_num'] = 'test';
						// } elseif ($LINE['course_num'] == 16) {
						// 	$LINE['stage_num'] = 'test';
						// // add end 2018/05/18 yoshizawa 理科社会対応
						// //add start hirose 2020/08/27 テスト標準化開発
						// } elseif ($LINE['course_num'] == 11) {
						// 	$LINE['stage_num'] = 'test';
						// } elseif ($LINE['course_num'] == 12) {
						// 	$LINE['stage_num'] = 'test';
						// } elseif ($LINE['course_num'] == 13) {
						// 	$LINE['stage_num'] = 'test';
						// } elseif ($LINE['course_num'] == 14) {
						// 	$LINE['stage_num'] = 'test';
						// //add end hirose 2020/08/27 テスト標準化開発
						// }
						// del end hirose 2020/09/01 テスト標準化開発
						//テスト専用問題はtestディレクトリを作成するようにする
						$LINE['stage_num'] = 'test';// add hirose 2020/09/01 テスト標準化開発
						$LINE['correct_number'] = 1;
						$LINE['stage_name'] = "テスト専用問題確認：".$list['problem_num'];
						$LINE['lesson_name'] = "&nbsp;";
						$LINE['unit_name'] = "&nbsp;";
					}
				}
			}

		}
	} else {
		foreach ($_SESSION['problem'] AS $key => $val) {
			$LINE[$key] = $val;
		}
	}
	// add start hirose 2020/10/01 テスト標準化開発
	//学力診断テスト・数学検定の時は、course_numをdisplay専用にする
	if($_POST['test_type'] == '4' ){
		$display_id = get_course_num_ms_test_default($_POST['display_id']);
		$LINE['stage_num'] = 'test';
	}elseif($_POST['test_type'] == '5'){
		$display_id = get_course_num_math_test_book_info($_POST['display_id']);
		$LINE['stage_num'] = 'test';
	// add start hirose 2020/12/15 テスト標準化開発 定期テスト
	}elseif($_POST['test_type'] == '1'){
		$display_id = $_POST['display_id'];
		$LINE['stage_num'] = 'test';
	// add end hirose 2020/12/15 テスト標準化開発 定期テスト
	}else{
		$display_id = $LINE['course_num'];
	}
	// add end hirose 2020/10/01 テスト標準化開発
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
	$display_problem_num = $display_problem->set_display_problem_num();
	$voice_button = $display_problem->set_voice_button();
	$question = $display_problem->set_question();
	$problem = $display_problem->set_problem();
	$set_form = $display_problem->set_form();
	//$form_start = $display_problem->form_start($set_form);	//	del ookawara 2010/02/01
	//$form_end = $display_problem->form_end($set_form);		//	del ookawara 2010/02/01
	//$LESSONUNIT = stage_name . " " . lesson_name . " " . unit_name; //del koyama 2013/11/06

	// ドリルタイトル用 add koyama 2013/11/06
	$stage_name = stage_name . " ";
	$lesson_name = lesson_name . " ";
	$unit_name = unit_name . " ";

	//	ヒント表示
	//	add ookawara 2012/08/24
	if (hint_number == 99) {
		$hint = $display_problem->hint_check();
	}

	//	サブスペース(area3)
	$sub_space = $display_problem->display_area3("start");

	//--------------------------------------------------------------------------------------------------------------------------------
	//  add 2014/03/31 yoshizawa 02_作業要件/03_molee対応/03_ドリル仕様変更対応/09_修正内容(管理画面問題確認Ajax化)
	//--------------------------------------------------------------------------------------------------------------------------------
	//add start hirose 2019/02/27 すらら英単語
	//core側ではSTART画面を挟んでおり、drill_start()とともにCSSを切り替えているため。
	$v_js = "";
	if (form_type == 16 || form_type == 17 ) {
		$v_js = "disp_header();";
	}
	//add end hirose 2019/02/27 すらら英単語
	$start_button  = "<div id=\"start_set\">\n";
	$start_button .= "<div id=\"drill_start_button\">\n";
	$start_button .= "</div>\n";
	$start_button .= "</div>\n";
	//$start_button .= "<script type=\"text/javascript\">drill_start('admin_check_test_problem');</script>";
	$start_button .= "<script type=\"text/javascript\">".$v_js."drill_start('admin_check_test_problem');</script>";//upd hirose 2019/02/27 すらら英単語

	// BG MUSIC  Firefox対応
	$pre = ".mp3";
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	if (preg_match("/Firefox/i", $user_agent) || preg_match("/Opera/i", $user_agent) || preg_match("/OPR/i", $user_agent)) { $pre = ".ogg"; }

	// del start oda 2014/12/15 正解/不正解の音声は、surala_sound_ajaxを利用する
	//// judge_soundにてaudioタグが使用できないブラウザを対応
	//$sub_judge_sound  = "<object classid=\"clsid:d27cdb6e-ae6d-11cf-96b8-444553540000\" codebase=\"http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0\" width=\"0\" height=\"0\" id=\"judge_sound_ok_flash\">";
	//$sub_judge_sound .= "<param name=\"movie\" value=\"/student/images/drill/play_sound.swf?file=/student/images/drill/true".$pre."\" />";
	//$sub_judge_sound .= "<embed src=\"/student/images/drill/play_sound.swf?file=/student/images/drill/true".$pre."\" width=\"0\" height=\"0\" allowScriptAccess=\"sameDomain\" type=\"application/x-shockwave-flash\" pluginspage=\"http://www.adobe.com/go/getflash\">";
	//$sub_judge_sound .= "</embed></object>";
	//$sub_judge_sound .= "<object classid=\"clsid:d27cdb6e-ae6d-11cf-96b8-444553540000\" codebase=\"http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0\" width=\"0\" height=\"0\" id=\"judge_sound_off_flash\">";
	//$sub_judge_sound .= "<param name=\"movie\" value=\"/student/images/drill/play_sound.swf?file=/student/images/drill/false".$pre."\" />";
	//$sub_judge_sound .= "<embed src=\"/student/images/drill/play_sound.swf?file=/student/images/drill/false".$pre."\" width=\"0\" height=\"0\" allowScriptAccess=\"sameDomain\" type=\"application/x-shockwave-flash\" pluginspage=\"http://www.adobe.com/go/getflash\">";
	//$sub_judge_sound .= "</embed></object>";
	// del end oda 2014/12/15

	$bg_music = "";
	$music_file = MATERIAL_TEMP_DIR.course_num."/drill_bg_music".$pre;
	if (file_exists($music_file) && $list['bgm_set'] == '1') {//(1=BGM有り,2=BGM無し）
		$bg_music .= "<audio id=\"bgm\" src=\"".$music_file."\" loop onended=\"this.play()\"></audio>\n";
		//$bg_music .= "<audio id=\"judge_sound\" src=\"/student/images/drill/silent3sec".$pre."\">".$sub_judge_sound."</audio>\n";	// del start oda 2014/12/15 正解/不正解の音声は、sound_ajaxを利用する
	} else {// BGM無しの時は無音BGMをセットし3秒間空ける
		//無音BGMが無いとIPADで正解不正解の音が鳴らないので追加
		$bg_music .= "<audio id=\"bgm\" src=\"/student/images/drill/silent3sec".$pre."\" loop onended=\"this.play()\"></audio>\n";
		//$bg_music .= "<audio id=\"judge_sound\" src=\"/student/images/drill/silent3sec".$pre."\">".$sub_judge_sound."</audio>\n";	// del start oda 2014/12/15 正解/不正解の音声は、sound_ajaxを利用する
	}
	//--------------------------------------------------------------------------------------------------------------------------------

	// 画面サイズに合わせる
	//$size_check = "check_size(1,".$_SESSION['course']['course_num'].");";	//	del ookawara 2010/10/05
	$size_check = "zoom_all(".$_SESSION['course']['course_num'].");";	//	add ookawara 2010/10/05

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
	// uod start hirose 2020/09/18 テスト標準化開発
	// } elseif (form_type == 3 || form_type == 4 || form_type == 10) {
	} elseif (form_type == 3 || form_type == 4 || form_type == 10 || form_type == 14) {
	// uod end hirose 2020/09/18 テスト標準化開発
		$tabindex = $display_problem->set_more_script();
		if ($tabindex > 0) {
			// edit simon 2014-11-14
			//$ONLOAD_BODY = "<body onload=\"".$size_check."document.getElementById('tabindex1').focus();\">";
			$ONLOAD_BODY = "<body onload=\"".$size_check."\">";
		} else {
			$ONLOAD_BODY = "";
		}

	// 数式パレット式　（html作成）の問題
	} elseif (form_type == 11) {
		$MORE_SCRIPT = $display_problem->set_more_script();
		$FUNCTIONKEY = $display_problem->fnkey();
		//$size_check = "";	//	del ookawara 2010/10/05
		//$GETEXCALCTAG = $display_problem->getExCalcTag(); // add 2014/05/29 yoshizawa		// del oda 2014/06/27
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
	

	//----- ドキュメントモード変更 add 2014/03/31 yoshizawa 02_作業要件/03_molee対応/03_ドリル仕様変更対応/09_修正内容(管理画面問題確認Ajax化)--------
	//      現在、ajax化を行った後にIE8以下のブラウザで画面ズレが起きているので
	//		ドキュメントモードを指定してあげる必要がある
	// IE8 or IE7
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	if (preg_match('/MSIE 8/',$user_agent) || preg_match('/MSIE 7/',$user_agent)) {
		$docment = "<meta http-equiv=\"X-UA-Compatible\" content=\"IE=5\">\n";
		$INPUTS['DOCUMENTMODE'] = array('result'=>'plane','value'=>$docment);
	}
	//------------------------------------------------------------------------------------------------------------------------------------------------

	//----- 数式パレット/音声準備 add oda 2014/12/24 --------
	//      IE8またはIE7の場合は、旧数式パレットを使用するので、数式パレットのscriptタグは出力しない
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
		//update start kimura 2018/05/29 数式パレット新UI設定
		//$exec_calc_tag1 = "<script type='text/javascript' src='/student/javascript/ex_calc.min.js'></script>\n";
		$exec_calc_tag1 = "<script type='text/javascript' src='/javascript/userAgent.js'></script>\n"; // kaopiz add 2020/08/15 ipados13
		$exec_calc_tag1 .= ex_calc_js($_SESSION['TEGAKI_FLAG']);
		//update end   kimura 2018/05/29 数式パレット新UI設定
		$exec_calc_tag1 .= "<script type='text/javascript' src='/javascript/html5_tools.js'></script>\n";
		$exec_calc_tag1 .= "<script type='text/javascript' src='/student/javascript/html5_palette.js'></script>\n";

		$exec_calc_tag2 = "<div class=\"ExCalcHtmlKeyboard\" style=\"position:absolute;top:0;left:1070px;z-index:999;\"></div>\n";
		$exec_calc_tag2 .= "<script type='text/javascript'>repositionCanvasPalette()</script>\n";

		// $sound_object = "<audio src=\"\" id=\"sound_ajax\" style=\"display:none;\"></audio>";										// del karasawa 2019/11/18 課題要望800
		$sound_object = "<audio src=\"/student/images/drill/silent3sec".$pre."\" id=\"sound_ajax\" style=\"display:none;\"></audio>";	// add karasawa 2019/11/18 課題要望800
	}
	//---------------------------------------------------------------------------------------------------------------
	//add start hirose 2020/08/27 テスト標準化開発
	if (course_num == 11 || course_num == 13 || course_num == 14) {
		// すららスタンダード以外のコースでは$MORE_SCRIPTでcssを読み込んでいます。
		// upd start hirose 2020/10/02 テスト標準化開発
		// // CSS読み込み用
		// $MORE_SCRIPT .= "<link type=\"text/css\" charset=\"utf-8\" rel=\"stylesheet\" href='".MATERIAL_TEMP_DIR.course_num."/".stage_num."/course.css' />\n";		// update oda 2014/07/31 課題要望一覧No270 IE8でcharsetが必要な為、追加
		// // レッスン毎のCSSを反映させる処理 		下記ディレクトリにCSSファイルがある時のみ反映します
		// $css_path = MATERIAL_TEMP_DIR.course_num."/".stage_num."/".lesson_num."_course.css";
		// if (file_exists($css_path)) {
		// 	$MORE_SCRIPT .= "<link type=\"text/css\" charset=\"utf-8\" rel=\"stylesheet\" href=\"$css_path\" />\n";		// update oda 2014/07/31 課題要望一覧No270 IE8でcharsetが必要な為、追加
		// }
		// CSS読み込み用
		$MORE_SCRIPT .= "<link type=\"text/css\" charset=\"utf-8\" rel=\"stylesheet\" href='".MATERIAL_TEMP_DIR.$display_id."/".stage_num."/course.css' />\n";		// update oda 2014/07/31 課題要望一覧No270 IE8でcharsetが必要な為、追加
		// レッスン毎のCSSを反映させる処理 		下記ディレクトリにCSSファイルがある時のみ反映します
		$css_path = MATERIAL_TEMP_DIR.$display_id."/".stage_num."/".lesson_num."_course.css";
		if (file_exists($css_path)) {
			$MORE_SCRIPT .= "<link type=\"text/css\" charset=\"utf-8\" rel=\"stylesheet\" href=\"$css_path\" />\n";		// update oda 2014/07/31 課題要望一覧No270 IE8でcharsetが必要な為、追加
		}
		// upd end hirose 2020/10/02 テスト標準化開発
	}
	//add end hirose 2020/08/27 テスト標準化開発
	//add start hirose 2018/12/05 すらら英単語
	$stop_form ="";
	$header_area ="";
	if(form_type == 16 || form_type == 17){
		//$stop_form = $display_problem ->stop_form(); //del hirose 2019/1/7 すらら英単語
		//$header_area = $display_problem ->header_area('0');
		$header_area = $display_problem ->header_area('course',course_num);//upd hirose 209/02/27 すらら英単語
		//add start hirose 2018/12/17 すらら英単語
		if(defined('remarks')){
			$remarks = "<p>".remarks."</p>";
			$INPUTS['REMARKS']	= array('result'=>'plane','value'=>$remarks);
		}
		//add end hirose 2018/12/17 すらら英単語
		$word_voice_file = MATERIAL_VOICE_DIR.course_num."/".stage_num."/".lesson_num."/".unit_num."/".block_num."/".voice_data;
		// update 2019/09/02 yoshizawa id="sound_ajax"が消えない様に「=」→「.=」に変更いたします。
		// (すらら英単語テストの問題では本プログラムは未使用です/_www/admin_lib/check_vocabulary_test_problem.phpを使用して出題します。)
		$sound_object .= <<<EOT
<script type="text/javascript">
setTimeout(function() { opener_play_sound_ajax('sound_ajax', '{$word_voice_file}', '', '');sound_stop_preparation(); }, 4);
document.body.onbeforeunload = function() { opener_play_sound_ajax('sound_ajax', '/student/images/drill/silent3sec.mp3', '', ''); }
</script>
EOT;
	}
	
	//ページによってIDが違うため分岐
	if(form_type == 16){
		$INPUTS['PARENTID'] = array('result'=>'plane','value'=>'unit');
		$INPUTS['DESCAREACSS'] = array('result'=>'plane','value'=>'base2');
	}elseif(form_type == 17){
		$INPUTS['PARENTID'] = array('result'=>'plane','value'=>'unit-translation');
		$INPUTS['DESCAREACSS'] = array('result'=>'plane','value'=>'base3');
	}
	//add end hirose 2018/12/05 すらら英単語

	//----- add 2014/07/31 oda
	//IE10以降のバージョンだとformがHeightを取ってしまいレイアウトに影響があるため変更する
	$ajax_form .= "<div id=\"ajax_course_num\" style=\"display:none;\">\n";
	$ajax_form .= course_num;
	$ajax_form .= "</div>\n";
	//-----

	//	add ookawara 2010/02/01	上記から移動
	$form_start = $display_problem->form_start($set_form);
	$form_end = $display_problem->form_end($set_form);

	if (setting_type == 1) { unset($display_problem_num); }
	$make_html = new read_html();
	$make_html->set_dir(STUDENT_TEMP_DIR.course_num."/");
	$make_html->set_file("drill.htm");

	// upd start hasegawa 2017/04/07 タグごと置換するように修正
	// $INPUTS['CSSPATH'] = array('result'=>'plane','value'=>MATERIAL_TEMP_DIR.course_num."/".stage_num."/course.css");
	// upd start hirose 2020/10/02 テスト標準化開発
	// $css_file = MATERIAL_TEMP_DIR.course_num."/".stage_num."/course.css";
	$css_file = MATERIAL_TEMP_DIR.$display_id."/".stage_num."/course.css";
	// upd end hirose 2020/10/02 テスト標準化開発
	$css_file = "<link rel=\"stylesheet\" href=\"".$css_file."\" type=\"text/css\">";
	$INPUTS['CSSPATH'] = array('result'=>'plane','value'=>$css_file);
	// upd end hasegawa 2017/04/07

	$INPUTS['MORESCRIPT'] = array('result'=>'plane','value'=>$MORE_SCRIPT);
	//$INPUTS[LESSONUNIT] = array('result'=>'plane','value'=>$LESSONUNIT); //del koyama 2013/11/06
	$INPUTS['DISPLAYPROBLEMNUM'] = array('result'=>'plane','value'=>$display_problem_num);
	$INPUTS['VOICE'] = array('result'=>'plane','value'=>$voice_button);
	$INPUTS['QUESTION'] = array('result'=>'plane','value'=>$question);
	$INPUTS['PROBLEM'] = array('result'=>'plane','value'=>$problem);
	$INPUTS['ANSWERINFO'] = array('result'=>'plane','value'=>$answer_info);
	$INPUTS['ANSWERCOLUMN'] = array('result'=>'plane','value'=>$set_form);
	$INPUTS['FORMSTART'] = array('result'=>'plane','value'=>$form_start);
	$INPUTS['FORMEND'] = array('result'=>'plane','value'=>$form_end);
	$INPUTS['HINT'] = array('result'=>'plane','value'=>$hint);	//	add ookawara 2012/08/24
	//$INPUTS[SUBSPACE] = array('result'=>'plane','value'=>$sub_space);	//	del 2014/03/31 yoshizawa 02_作業要件/03_molee対応/03_ドリル仕様変更対応/09_修正内容(管理画面問題確認Ajax化)
	$INPUTS['FUNCTIONKEY'] = array('result'=>'plane','value'=>$FUNCTIONKEY);
	$INPUTS['ERROR'] = array('result'=>'plane','value'=>$errors);
	$INPUTS['EXECCALC1'] = array('result'=>'plane','value'=>$exec_calc_tag1);		// add oda 2014/12/24
	$INPUTS['EXECCALC2'] = array('result'=>'plane','value'=>$exec_calc_tag2);		// add oda 2014/12/24
	$INPUTS['SOUNDOBJ'] = array('result'=>'plane','value'=>$sound_object);			// add oda 2014/12/24

	// ドリルタイトル用 add koyama 2013/11/06
	$INPUTS['STAGENAME']		= array('result'=>'plane','value'=>$stage_name);
	$INPUTS['LESSONNAME']		= array('result'=>'plane','value'=>$lesson_name);
	$INPUTS['UNITNAME']		= array('result'=>'plane','value'=>$unit_name);

	// -----add 2014/03/31 yoshizawa 02_作業要件/03_molee対応/03_ドリル仕様変更対応/09_修正内容(管理画面問題確認Ajax化)
	$INPUTS['STARTBUTTON'] = array('result'=>'plane','value'=>$start_button);
	$INPUTS['BGMUSIC'] = array('result'=>'plane','value'=>$bg_music);
	$INPUTS['AJAXFORM'] = array('result'=>'plane','value'=>$ajax_form);
	// -----

	//----- add 2014/05/19 yoshizawa JS版数式パレット設置		// del oda 2014/06/27
	//$INPUTS['GETEXCALCTAG'] = array('result'=>'plane','value'=>$GETEXCALCTAG);
	//-----
	
	$INPUTS['STOPFORM'] = array('result'=>'plane','value'=>$stop_form); //add hirose 2018/12/04 すらら英単語
	$INPUTS['HEADERAREA'] = array('result'=>'plane','value'=>$header_area); //add hirose 2018/12/04 すらら英単語

	$make_html->set_rep_cmd($INPUTS);
	$html = $make_html->replace();
	if ($ONLOAD_BODY) { $html = ereg_replace("<body>",$ONLOAD_BODY,$html); }

	return $html;

}
// add start hirose 2020/10/02 テスト標準化開発
function get_course_num_math_test_book_info($class_id){

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql  = "SELECT course_num FROM ".T_MATH_TEST_BOOK_INFO.
				" WHERE class_id='".$class_id."'".
				" AND mk_flg='0'".
				" LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
	}

	$course_num = "";
	if(!empty($list['course_num'])){
		$course_num = $list['course_num'];
	}
	return $course_num;

}
function get_course_num_ms_test_default($default_test_num){
	//test_group_id_default_test_numの形で来ることがあるので、必要なものだけ取得
	if(preg_match('/^[0-9]*_([0-9]*)$/',$default_test_num,$m)){
		$default_test_num = $m[1];
	}
	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql  = "SELECT course_num FROM ".T_MS_TEST_DEFAULT.
				" WHERE default_test_num='".$default_test_num."'".
				" AND mk_flg='0'".
				" LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
	}

	$course_num = "";
	if(!empty($list['course_num'])){
		$course_num = $list['course_num'];
	}
	return $course_num;

}
// add end hirose 2020/10/02 テスト標準化開発
?>
