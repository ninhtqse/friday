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

	/*	del start 2014/03/28 yoshizawa add 2014/03/28 yoshizawa 02_作業要件/03_molee対応/03_ドリル仕様変更対応/09_修正内容(管理画面問題確認Ajax化)
	//if ($block_type == 5) {					//	add shoda 2012/07/04	delete shoda 2012/08/01
	//	$html = make_frame_html($ERROR);
	//} else {
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
	//}
		del end 2014/03/28 yoshizawa add 2014/03/28 yoshizawa 02_作業要件/03_molee対応/03_ドリル仕様変更対応/09_修正内容(管理画面問題確認Ajax化)	*/

	//	add 2014/03/28 yoshizawa 02_作業要件/03_molee対応/03_ドリル仕様変更対応/09_修正内容(管理画面問題確認Ajax化)
	$html = make_default_html($ERROR);

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

	// add start yoshizawa 2019/01/29 手書きV2書写
	// 書写ドリルの生ログ保存時にユニット情報が混在してしまうのでレクチャーとゲームのユニット情報を削除します。
	unset($_SESSION['CHECK_FLASH']);
	unset($_SESSION['CHECK_GAME']);
	// add end yoshizawa 2019/01/29 手書きV2書写

	if (isset($_POST['problem_num'])) {
		unset($_SESSION['problem']);
		foreach($_POST as $key => $val) {
			$val = urldecode($val);
			//$LINE[$key] = replace_word($val);		//test del okabe 2018/08/10 理科 問題表示文字調整「，＋ー」

			//add start okabe 2018/08/27 理科 問題表示文字調整「，＋ー」
			$tmpx_course_num = intval($_POST['course_num']);
			$sql  = "SELECT write_type FROM ".T_COURSE." WHERE course_num='".$tmpx_course_num."' LIMIT 1;";
			$tmpx_write_type = 0;
			if ($result = $cdb->query($sql)) {
				$list = $cdb->fetch_assoc($result);
				$tmpx_write_type = $list['write_type'];
			}
			if ($tmpx_write_type == 15) {	//理科の場合
				$argsx = array('val'=>$val, 'key'=>$key, 'form_type'=>$_POST['form_type']);
				$LINE[$key] = filter_science_datasx($argsx);
			} else {
				$LINE[$key] = replace_word($val);
			}
			//add end okabe 2018/08/27

		}
		if(!$LINE['block_type']) {		// add hasegawa 2016/09/21 解答ボタン出ない不具合対応 分岐追加
			//	ブロック情報取得
			//	add ookawara 2011/12/29
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
		}

		// 2012/10/22 add start oda
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
		// 2012/10/22 add end oda

		//	横・縦書き設定
		$sql  = "SELECT write_type, math_align, language_type, course_name FROM ".T_COURSE.	//	add , math_align ookawara 2012/08/28 add , language_type/course_name oda 2012/10/09
				" WHERE course_num='".$LINE['course_num']."'".
				" LIMIT 1;";
//echo "check2<br>\n";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
//pre($list);
			$LINE['write_type'] = $list['write_type'];
			$LINE['math_align'] = $list['math_align'];	//	add ookawara 2012/08/28
			$LINE['language_type'] = $list['language_type'];	//	add oda 2012/10/09
			$LINE['course_name'] = $list['course_name'];		//	add oda 2012/10/09
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
					" , block.block_type".	//	add ookawara 2011/12/29
					" , course.math_align".	//	add ookawara 2012/08/28
					" , course.language_type".	//	add oda 2012/10/01
					" , course.course_name".	//	add oda 2012/10/09
					" , sc.question_flg".		//  add oda 2012/10/22
					" FROM ".T_PROBLEM." ".T_PROBLEM.
					" INNER JOIN ".T_COURSE." ".T_COURSE." ON problem.course_num=course.course_num".
					" INNER JOIN ".T_BLOCK." ".T_BLOCK." ON problem.block_num=block.block_num".
					" INNER JOIN ".T_UNIT." ".T_UNIT." ON block.unit_num=unit.unit_num".
					" INNER JOIN ".T_LESSON." ".T_LESSON." ON block.lesson_num=lesson.lesson_num".
					" INNER JOIN ".T_STAGE." ".T_STAGE." ON block.stage_num=stage.stage_num".
					//" INNER JOIN ".T_SERVICE_COURSE_LIST." scl ON problem.course_num = scl.course_num ".	// 2012/10/22 add oda		// del oda 2013/09/25
					//" INNER JOIN ".T_SERVICE." sc ON scl.service_num = sc.service_num ".					// 2012/10/22 add oda		// del oda 2013/09/25
					" LEFT  JOIN ".T_SERVICE_COURSE_LIST." scl ON problem.course_num = scl.course_num AND scl.course_type = '1' AND scl.display = '1' AND scl.mk_flg  = '0'  ".	// 2013/09/25 add oda
					" LEFT  JOIN ".T_SERVICE." sc ON scl.service_num = sc.service_num AND sc.display = '1' AND sc.mk_flg  = '0' ".												// 2013/09/25 add oda
					" WHERE problem.problem_num='".$_POST['direct_problem_num']."'".
					" AND problem.state!='1'".
					//" AND scl.course_type = '1' ".															// 2012/10/22 add oda		// del oda 2013/09/25
					//" AND scl.display = '1' ".																// 2012/10/22 add oda		// del oda 2013/09/25
					//" AND scl.mk_flg  = '0' ".																// 2012/10/22 add oda		// del oda 2013/09/25
					" LIMIT 1;";
					// echo $sql; // DBG
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
						//$LINE[$key] = replace_word($val);	//test del okabe 2018/08/10 理科 問題表示文字調整「，＋ー」

						//add start okabe 2018/08/27 理科 問題表示文字調整「，＋ー」
						$tmpx_course_num = intval($PROBLEM_LIST['course_num']);
						$sql  = "SELECT write_type FROM ".T_COURSE." WHERE course_num='".$tmpx_course_num."' LIMIT 1;";
						$tmpx_write_type = 0;
						if ($result = $cdb->query($sql)) {
							$list = $cdb->fetch_assoc($result);
							$tmpx_write_type = $list['write_type'];
						}
						if ($tmpx_write_type == 15) {	//理科の場合
							$argsx = array('val'=>$val, 'key'=>$key, 'form_type'=>$PROBLEM_LIST['form_type']);
							$LINE[$key] = filter_science_datasx($argsx);
						} else {
							$LINE[$key] = replace_word($val);
						}
						//add end okabe 2018/08/27

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
				//$val = ereg_replace("//","\n",$val);
				$val = str_replace("//","\n",$val);
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
	if(form_type == 18) {// kaopiz 2020/09/15 speech
		$set_form = $display_problem->speech_button(); // kaopiz 2020/09/15 speech
	}else{ // kaopiz 2020/09/15 speech
		$set_form = $display_problem->set_form();
	} // kaopiz 2020/09/15 speech
	//$form_start = $display_problem->form_start($set_form);	//	del ookawara 2010/02/01
	//$form_end = $display_problem->form_end($set_form);		//	del ookawara 2010/02/01
	$LESSONUNIT = stage_name . " " . lesson_name . " " . unit_name;

	//	ヒント表示
	//	add ookawara 2012/08/24
	if (hint_number == 99) {
		$hint = $display_problem->hint_check();
	}

	//	サブスペース(area3)
	$sub_space = $display_problem->display_area3("start");

	//--------------------------------------------------------------------------------------------------------------------------------
	//  add 2014/03/28 yoshizawa 02_作業要件/03_molee対応/03_ドリル仕様変更対応/09_修正内容(管理画面問題確認Ajax化)
	//--------------------------------------------------------------------------------------------------------------------------------
	//add start hirose 2019/02/27 すらら英単語
	//core側ではSTART画面を挟んでおり、drill_start()とともにCSSを切り替えているため。
	$v_js = "";
//	if (form_type == 16 || form_type == 17){
	if (form_type == 16 || form_type == 17 || form_type == 18){ // kaopiz 2020/09/15 speech
		$v_js = "disp_header();";
	}
	//add end hirose 2019/02/27 すらら英単語
	$start_button  = "<div id=\"start_set\">\n";
	$start_button .= "<div id=\"drill_start_button\">\n";
	$start_button .= "</div>\n";
	$start_button .= "</div>\n";
	//$start_button .= "<script type=\"text/javascript\">drill_start('admin_check_problem');</script>";
	$start_button .= "<script type=\"text/javascript\">".$v_js."drill_start('admin_check_problem');</script>";//upd hirose 2019/02/27 すらら英単語

	$start_button .= "<script type=\"text/javascript\" src='/student/javascript/dist/ex_img.js'></script>"; //kaopiz 2020/11/26 PROBLEM_STATEMENT

	// BG MUSIC  Firefox対応
	$pre = ".mp3";
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	if (preg_match("/Firefox/i", $user_agent) || preg_match("/Opera/i", $user_agent) || preg_match("/OPR/i", $user_agent)) { $pre = ".ogg"; }

	// del start oda 2014/12/15 正解/不正解の音声は、sound_ajaxを利用する
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

	//	並べ替えの問題
	if (form_type == 5) {
		$MORE_SCRIPT = $display_problem->set_more_script();
		$answer_info = $display_problem->set_answer_info();
		$ONLOAD_BODY = "<body onload=\"".$size_check."\">";

	// ドラッグ＆クリックの問題
	} elseif (form_type == 8) {
		$MORE_SCRIPT = $display_problem->set_more_script();
		$ONLOAD_BODY = "<body onload=\"make_ele();".$size_check."\">";

	//	テキストボックス＆複数テキストボックス＆html作成
	// } elseif (form_type == 3 || form_type == 4 || form_type == 10) {	// upd hasegawa 2018/03/09 百マス計算
	} elseif (form_type == 3 || form_type == 4 || form_type == 10 || form_type == 14) {
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
		//$GETEXCALCTAG = $display_problem->getExCalcTag(); // add 2014/05/29 yoshizawa
		//del 2014/06/25 yoshizawa
	//add start hirose 2018/12/07 すらら英単語
	} elseif (form_type == 16) {
		$size_check = "";
	//add end hirose 2018/12/07 すらら英単語
	//add start hirose 2018/10/2 英単語マスター機能追加対応(form_type17)
	} elseif (form_type == 17) {
		$MORE_SCRIPT= $display_problem->set_more_script();
		//$ONLOAD_BODY = "<body onload=\"".$size_check." text_cursor();\">";
		$size_check = "";//add hirose 2018/12/07 すらら英単語
	//add end hirose 2018/10/2 英単語マスター機能追加対応(form_type17)
		//add start hirose 2018/12/19 すらら英単語
		$ONLOAD_BODY = "";
		if(apple_check()){
			$ONLOAD_BODY = "<body class=\"apple-dv\">";
		}
		//add end hirose 2018/12/19 すらら英単語
	} elseif(form_type == 18) { // kaopiz 2020/09/15 speech
		$MORE_SCRIPT = $display_problem->set_more_script(); // kaopiz 2020/09/15 speech
	} else {
		$MORE_SCRIPT = "";
		$ONLOAD_BODY = "";
	}

	// kaopiz 2020/10/21 speech start
	if(in_array(form_type, [1, 2, 3, 4, 5, 10])) {
		$MORE_SCRIPT .= "<script type=\"text/javascript\" src=\"https://sdk.cloud.chivox.com/chivoxsdk-js/v6.0/chivox.min.js\"></script>\n";
		$MORE_SCRIPT .= "<script type=\"text/javascript\" src=\"/javascript/chivox-button.js\"></script>\n";
		$MORE_SCRIPT .= "<link rel=\"stylesheet\" href=\"/css/chivox-button.css\">\n";
	}
	// kaopiz 2020/10/21 speech end

	if ($ONLOAD_BODY == "" && $size_check){
		$ONLOAD_BODY = "<body onLoad=\"".$size_check."\">";
	}

	//	add ookawara 2010/02/01	上記から移動
	$form_start = $display_problem->form_start($set_form);
	$form_end = $display_problem->form_end($set_form);

	if (setting_type == 1) { unset($display_problem_num); }
	$make_html = new read_html();
	$make_html->set_dir(STUDENT_TEMP_DIR.course_num."/");

	/*	del start 2014/04/01 yoshizawa 02_作業要件/03_molee対応/03_ドリル仕様変更対応/09_修正内容(管理画面問題確認Ajax化) 下に新規で作成しています
	if (block_type == 4 || block_type == 5 || block_type == 6) {	//	add shoda 2012/08/01	//	add  || block_type == 6 ookawara 2012/09/26//	add  || block_type == 4 oda 2012/10/18
		$make_html->set_file("check_drill.htm");
	} else {
		$make_html->set_file("drill.htm");
	}
	del end 2014/04/01 yoshizawa 02_作業要件/03_molee対応/03_ドリル仕様変更対応/09_修正内容(管理画面問題確認Ajax化)	*/

	//	add 2014/04/01 yoshizawa 02_作業要件/03_molee対応/03_ドリル仕様変更対応/09_修正内容(管理画面問題確認Ajax化)
	$make_html->set_file("drill.htm");

	// ----- add 2014/03/28 yoshizawa 02_作業要件/03_molee対応/03_ドリル仕様変更対応/09_修正内容(管理画面問題確認Ajax化)
	if ($_SESSION['course']['course_num']) {

		//----- del 2014/06/06 yoshizawa 下に新規で作成しています
		//$ajax_form  = "<form action=\"\" method=\"post\" name=\"ajax_course_num\">\n";
		//$ajax_form .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_SESSION['course']['course_num']."\">\n";
		//$ajax_form .= "</form>\n";
		//-----

		//----- add 2014/06/06 yoshizawa
		//IE10以降のバージョンだとformがHeightを取ってしまいレイアウトに影響があるため変更する
		$ajax_form .= "<div id=\"ajax_course_num\" style=\"display:none;\">\n";
		$ajax_form .= $_SESSION['course']['course_num'];
		$ajax_form .= "</div>\n";
		//-----
	}
	// -----

	//---------------------------------------------------------------------------------------------
	// ドリルタイトル名用のコース、ステージ、レッスン、ユニットの名称取得	add koyama 2013/11/06
	//---------------------------------------------------------------------------------------------
	$DRILL_TITLE = get_drill_title_name($LINE['unit_num']);
//echo "■DRILL_TITLE　<br>\n";pre($DRILL_TITLE); echo"\n\n";

	// ----- ajax処理時にコース番号を取得させる為だけのフォーム add 2014/03/28 yoshizawa 02_作業要件/03_molee対応/03_ドリル仕様変更対応/09_修正内容(管理画面問題確認Ajax化)
	preg_match("/^H/", $DRILL_TITLE['stage_name'],$high_school[1]);//高校ならHを取得
	if ($high_school[1]) {

		//----- del 2014/06/06 yoshizawa 下に新規で作成しています
		//$ajax_form .= "<form action=\"\" method=\"post\" name=\"ajax_high_school\">\n";
		//$ajax_form .= "<input type=\"hidden\" name=\"high_school\" value=\"".$high_school[1]."\">\n";
		//$ajax_form .= "</form>\n";
		//-----

		//----- add 2014/06/06 yoshizawa
		//IE10以降のバージョンだとformがHeightを取ってしまいレイアウトに影響があるため変更する
		$ajax_form .= "<span id=\"high_school\"></span>\n";
		//-----
	}
	// -----


	//----- ドキュメントモード変更 add 2014/03/28 yoshizawa 02_作業要件/03_molee対応/03_ドリル仕様変更対応/09_修正内容(管理画面問題確認Ajax化)--------
	//      現在、ajax化を行った後にIE8以下のブラウザで画面ズレが起きているので
	//		ドキュメントモードを指定してあげる必要がある
	// IE8 or IE7
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	if (preg_match('/MSIE 8/',$user_agent) || preg_match('/MSIE 7/',$user_agent)) {
		$docment = "<meta http-equiv=\"X-UA-Compatible\" content=\"IE=5\">\n";
		$INPUTS['DOCUMENTMODE'] = array('result'=>'plane','value'=>$docment);
	}
	//------------------------------------------------------------------------------------------------------------------------------------------------

	//----- 数式パレット/音声準備 add oda 2014/12/19 --------
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

	// add simon 2014-12-19 >>>
	// 縦書きのテストの為
	if(course_num==2) {
		if($_SESSION['myid']['id']==='simon' || $_SESSION['myid']['id']==='yoshizawa' ) {
			$MORE_SCRIPT .= "<script type='text/javascript' src='/javascript/tategaki-dev.jquery.js'></script>\n";
		}
	}
	// <<<

	// add start oda 2014/07/31 課題要望一覧No270 CSSはヘッダで読み込む
	// if (block_type == 4 || block_type == 5 || block_type == 6) {	// upd hasegawa 2018/03/09 百マス計算
//	if (block_type == 4 || block_type == 5 || block_type == 6 || block_type == 7) {// upd hirose 2018/11/21 すらら英単語
	if (block_type == 4 || block_type == 5 || block_type == 6 || block_type == 7 || block_type == 8) {
		// すららスタンダード以外のコースでは$MORE_SCRIPTでcssを読み込んでいます。
		// CSS読み込み用
		$MORE_SCRIPT .= "<link type=\"text/css\" charset=\"utf-8\" rel=\"stylesheet\" href='".MATERIAL_TEMP_DIR.course_num."/".stage_num."/course.css' />\n";		// update oda 2014/07/31 課題要望一覧No270 IE8でcharsetが必要な為、追加
		// レッスン毎のCSSを反映させる処理 		下記ディレクトリにCSSファイルがある時のみ反映します
		$css_path = MATERIAL_TEMP_DIR.course_num."/".stage_num."/".lesson_num."_course.css";
		if (file_exists($css_path)) {
			$MORE_SCRIPT .= "<link type=\"text/css\" charset=\"utf-8\" rel=\"stylesheet\" href=\"$css_path\" />\n";		// update oda 2014/07/31 課題要望一覧No270 IE8でcharsetが必要な為、追加
		}

		// answer_result_formにfromタグを追加
		$answer_result_form  = "<form name=\"study_result5\">\n";
		$answer_result_form .= "<input type=\"hidden\" name=\"course_num\" value=\"".course_num."\">\n";
		$answer_result_form .= "<input type=\"hidden\" name=\"stage_num\" value=\"".stage_num."\">\n";
		$answer_result_form .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".lesson_num."\">\n";
		$answer_result_form .= "</form>\n";
		$INPUTS['ANSWERRESULTFORM'] = array('result'=>'plane','value'=>$answer_result_form);
	}
	// add end oda 2014/07/31

	//add start hirose 2018/09/21 英単語マスター機能追加対応(音声自動再生)
	$stop_form =""; //add hirose 2018/12/04 すらら英単語
	$header_area =""; //add hirose 2018/12/04 すらら英単語
//	if(form_type == 16 || form_type == 17){
	if(form_type == 16 || form_type == 17 || form_type == 18){ // kaopiz 2020/09/15 speech
		//$stop_form = $display_problem ->stop_form(); //add start hirose 2018/12/04 すらら英単語//del hirose 2019/1/7 すらら英単語
		//$header_area = $display_problem ->header_area('0'); //add start hirose 2018/12/04 すらら英単語
		$header_area = $display_problem ->header_area('course',course_num); //upd hirose 2019/02/27 すらら英単語
		//add start hirose 2018/12/17 すらら英単語
		if(isset($DRILL_TITLE['remarks'])){
			$remarks = "<p>".$DRILL_TITLE['remarks']."</p>";
			$INPUTS['REMARKS']	= array('result'=>'plane','value'=>$remarks);
		}
		//add end hirose 2018/12/17 すらら英単語
		$word_voice_file = MATERIAL_VOICE_DIR.course_num."/".stage_num."/".lesson_num."/".unit_num."/".block_num."/".voice_data;
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
	//add end hirose 2018/09/21 英単語マスター機能追加対応(音声自動再生)

	// update start oda 2013/09/20 旺文社：英語対応
	//$INPUTS[CSSPATH] = array('result'=>'plane','value'=>MATERIAL_TEMP_DIR.course_num."/".stage_num."/course.css");
	$css_file = MATERIAL_TEMP_DIR.course_num."/".stage_num."/course.css";
	if (file_exists(MATERIAL_TEMP_DIR.course_num."/".stage_num."/".lesson_num."_course.css")) {
		$css_file = MATERIAL_TEMP_DIR.course_num."/".stage_num."/".lesson_num."_course.css";
	}

	//add start hirose 2018/11/21 すらら英単語
	//csspathとmorescriptで二重読込するので、削除する
	if (block_type == 8) {
		$css_file = '';
	}
	//add end hirose 2018/11/21 すらら英単語
	//add start hirose 2018/12/05 すらら英単語
//if(form_type == 16){
	if(form_type == 16 || form_type == 18){ // kaopiz 2020/09/15 speech
		$INPUTS['PARENTID'] = array('result'=>'plane','value'=>'unit');
		$INPUTS['DESCAREACSS'] = array('result'=>'plane','value'=>'base2');
	}elseif(form_type == 17){
		$INPUTS['PARENTID'] = array('result'=>'plane','value'=>'unit-translation');
		$INPUTS['DESCAREACSS'] = array('result'=>'plane','value'=>'base3');
	}
	//add end hirose 2018/12/05 すらら英単語

	$css_file = "<link rel=\"stylesheet\" href=\"".$css_file."\" type=\"text/css\">";	//add hasegawa 2017/04/07 タグごと置換するように修正

	$INPUTS['CSSPATH'] = array('result'=>'plane','value'=>$css_file);
	// update end oda 2013/09/20
	//
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
	$INPUTS['HINT'] = array('result'=>'plane','value'=>$hint);	//	add ookawara 2012/08/24
	//$INPUTS[SUBSPACE] = array('result'=>'plane','value'=>$sub_space);	//	del 2014/03/31 yoshizawa 02_作業要件/03_molee対応/03_ドリル仕様変更対応/09_修正内容(管理画面問題確認Ajax化)
	$INPUTS['EXECCALC1'] = array('result'=>'plane','value'=>$exec_calc_tag1);		// add oda 2014/12/19
	$INPUTS['EXECCALC2'] = array('result'=>'plane','value'=>$exec_calc_tag2);		// add oda 2014/12/19
	$INPUTS['SOUNDOBJ'] = array('result'=>'plane','value'=>$sound_object);			// add oda 2014/12/19
	$INPUTS['FUNCTIONKEY'] = array('result'=>'plane','value'=>$FUNCTIONKEY);
	$INPUTS['ERROR'] = array('result'=>'plane','value'=>$errors);

	//$INPUTS[H1] = array('result'=>'plane','value'=>$h1_tag);	// add koyama 2013/11/06 // del koyama 2013/12/13

	// ドリルタイトル用 add koyama 2013/11/06
	$INPUTS['COURSENAME']		= array('result'=>'plane','value'=>$DRILL_TITLE['course_name']);
	$INPUTS['STAGENAME']		= array('result'=>'plane','value'=>$DRILL_TITLE['stage_name']);
	$INPUTS['STAGENAME2']		= array('result'=>'plane','value'=>$DRILL_TITLE['stage_name2']);
	$INPUTS['LESSONNAME']		= array('result'=>'plane','value'=>$DRILL_TITLE['lesson_name']);
	$INPUTS['LESSONNAME2']	= array('result'=>'plane','value'=>$DRILL_TITLE['lesson_name2']);
	$INPUTS['UNITNAME']		= array('result'=>'plane','value'=>$DRILL_TITLE['unit_name']);
	$INPUTS['UNITNAME2']		= array('result'=>'plane','value'=>$DRILL_TITLE['unit_name2']);

	// -----add 2014/03/28 yoshizawa 02_作業要件/03_molee対応/03_ドリル仕様変更対応/09_修正内容(管理画面問題確認Ajax化)
	$INPUTS['STARTBUTTON'] = array('result'=>'plane','value'=>$start_button);
	$INPUTS['BGMUSIC'] = array('result'=>'plane','value'=>$bg_music);
	$INPUTS['AJAXFORM'] = array('result'=>'plane','value'=>$ajax_form);
	// -----
	$INPUTS['STOPFORM'] = array('result'=>'plane','value'=>$stop_form); //add hirose 2018/12/04 すらら英単語
	$INPUTS['HEADERAREA'] = array('result'=>'plane','value'=>$header_area); //add hirose 2018/12/04 すらら英単語

	// TOEICだった場合の処理
	// 2012/10/09 add start oda
	if (block_type == 4 || block_type == 5 || block_type == 6) {
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
			$lesson_unit_name = substr(lesson_name,4);										// 2012/10/26 add oda
			$INPUTS['UNITCOUNT'] = array('result'=>'plane','value'=>$lesson_unit_name);		// 2012/10/26 add oda

			$INPUTS['CLOSEURL'] = array('result'=>'plane','value'=>"window.close();");		// 2012/11/13 add oda
			$INPUTS['BACKURL'] = array('result'=>'plane','value'=>"window.close();");		// add oda 2014/06/27

			//	add 2014/04/01 yoshizawa 02_作業要件/03_molee対応/03_ドリル仕様変更対応/09_修正内容(管理画面問題確認Ajax化)
			$INPUTS['ANSWERRESULTFORM'] = array('result'=>'plane','value'=>$start_button);

		}
	}
	// 2012/10/09 add end oda

	//----- add 2014/05/29 yoshizawa JS版数式パレット設置
	//$INPUTS['GETEXCALCTAG'] = array('result'=>'plane','value'=>$GETEXCALCTAG);
	//del 2014/06/25 yoshizawa
	//-----

	$make_html->set_rep_cmd($INPUTS);
	$html = $make_html->replace();
	if ($ONLOAD_BODY) { $html = ereg_replace("<body>",$ONLOAD_BODY,$html); }

	return $html;

}

/*	del start 2014/03/28 yoshizawa 02_作業要件/03_molee対応/03_ドリル仕様変更対応/09_修正内容(管理画面問題確認Ajax化)
//	解答確認処理
	del end 2014/03/28 yoshizawa 02_作業要件/03_molee対応/03_ドリル仕様変更対応/09_修正内容(管理画面問題確認Ajax化)	*/


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

//echo $sql."<br>";

	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$service_type = $list['setup_type'];
		}
	}

	return $service_type;
}

?>
