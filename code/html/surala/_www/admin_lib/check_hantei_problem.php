<?php
/**
 * ベンチャー・リンク　すらら
 *
 * 判定テスト 問題確認プログラム check_hantei_problem.php
 * ( _www/admin_lib/check_problem.php を元ファイルとして作成 )
 *
 * 履歴
 * 2013/07/11 初期設定
 *
 * @author Azet
 */

// okabe


/**
 * POSTデータ判断してHTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[T02]スタート判定テスト.
 *
 * @author Azet
 * @return string HTML
 */
function display_problem() {

		if ($_POST['action'] == "check") {
			$ERROR = check_answer();
			if ($ERROR && (!$_SESSION['record']['answer'] && $_SESSION['record']['answer'] != "0")) { unset($_POST['action']); }
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
 * AC:[A]管理者 UC1:[T02]スタート判定テスト.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function make_default_html($ERROR) {
//echo "make_default_html";

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

		$hantei_default_num = "";
		if ($_POST['hantei_default_num']) { $hantei_default_num = $_POST['hantei_default_num']; }

		//problem_num をもとに、hantei_ms_default から style_course_num と style_stage_num を取得
		$sql0 = "SELECT hmd.style_course_num, hmd.style_stage_num ".
			" FROM ".T_HANTEI_MS_DEFAULT." hmd ".
			" WHERE hmd.hantei_default_num='".$hantei_default_num."'".
			" AND hmd.mk_flg='0'".
			" LIMIT 1;";
		if ($result0 = $cdb->query($sql0)) {
			$list0 = $cdb->fetch_assoc($result0);
		}
		$style_course_num = $list0['style_course_num'];
		$style_stage_num = $list0['style_stage_num'];

		if ($_POST['problem_type'] == 1) {	//すらら問題
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
					" LEFT  JOIN ".T_SERVICE_COURSE_LIST." scl ON problem.course_num = scl.course_num AND scl.course_type = '1' AND scl.display = '1' AND scl.mk_flg = '0' ".
					" LEFT  JOIN ".T_SERVICE." sc ON scl.service_num = sc.service_num AND sc.display = '1' AND sc.mk_flg = '0' ".
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
						if ($key == "course_name") {
							$LINE['default_course_name'] = replace_word($val);
							continue;
						}
						if ($key == "stage_name") {
							$LINE['default_stage_name'] = replace_word($val);
							continue;
						}
						if ($key == "lesson_name") {
							$LINE['default_lesson_name'] = replace_word($val);
							continue;
						}
						if ($key == "unit_name") {
							$LINE['default_unit_name'] = replace_word($val);
							continue;
						}
						$LINE[$key] = replace_word($val);
					}
				}
			}
			//hantei_display_problem へ渡すデータ
			define(course_num, $style_course_num );
//			define(stage_num, $style_stage_num );
			define(default_course_num, $list['course_num'] );
			define(stage_num, $list['stage_num'] );
			define(lesson_num, $list['lesson_num']);
			define(unit_num, $list['unit_num']);
			define(block_num, $list['block_num']);

			$sql2 = "SELECT * FROM course ".
				" WHERE course_num='".$style_course_num."'".
				" AND state='0';";
			if ($result2 = $cdb->query($sql2)) {
				$list2 = $cdb->fetch_assoc($result2);
				$LINE['write_type'] = $list2['write_type'];			//courseテーブルから取得
				$LINE['math_align'] = $list2['math_align'];			//  〃
				$LINE['language_type'] = $list2['language_type'];	//  〃
			}

			$LINE['course_num'] = $style_course_num;	//判定テストマスタでの指定を使う
//			$LINE['stage_num'] = $style_stage_num; 		//  〃

			$LINE['course_name'] = $list2['course_name'];	//"000点コース";
//			$LINE['stage_num'] = $style_stage_num;
//			$LINE['stage_name'] = "判定テスト";		//"基本プラクティス";
//			$LINE['lesson_name'] = " ";		//Unit1";
//			$LINE['lesson_num'] = "0";		//"739";
//			$LINE['unit_num'] = "0";		//"1549";
//			$LINE['unit_name'] = " ";		//"Grammar";
//			$LINE['block_num'] = "0";		//"2593";

			$_SESSION['course']['problem_table_type'] = "1";
			$_SESSION['course']['problem_num'] = $LINE['problem_num'];

			$_SESSION['problem_info']['course_num'] = course_num;
			$_SESSION['problem_info']['stage_num'] = stage_num;
			$_SESSION['problem_info']['default_course_num'] = default_course_num;
			$_SESSION['problem_info']['default_stage_num'] = default_stage_num;
			$_SESSION['problem_info']['lesson_num'] = lesson_num;
			$_SESSION['problem_info']['unit_num'] = unit_num;
			$_SESSION['problem_info']['block_num'] = block_num;
		}



		if ($_POST['problem_type'] == 2) {	//判定テスト問題
			$sql  = "SELECT * ".
				" FROM hantei_ms_problem mtp".
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

					$LINE['course_num'] = $style_course_num;	//$list2['course_num'];
					$LINE['problem_table_type'] = $_POST['problem_type'];
					// upd start hirose 2020/09/05 テスト標準化開発
					//下でunsetしているので関係ないが、一応修正
					// if ($LINE['course_num'] == 1) {
					// 	$LINE['stage_num'] = 40;
					// } elseif ($LINE['course_num'] == 2) {
					// 	$LINE['stage_num'] = 31;
					// } elseif ($LINE['course_num'] == 3) {
					// 	$LINE['stage_num'] = 45;
					// }
					$LINE['stage_num'] = 'test';
					// upd end hirose 2020/09/05 テスト標準化開発
					$LINE['correct_number'] = 1;
					$LINE['stage_name'] = "判定テスト問題確認：".$list['problem_num'];
					$LINE['lesson_name'] = "&nbsp;";
					$LINE['unit_name'] = "&nbsp;";
				}
			}

			unset($LINE);
			$LINE['problem_num'] = $list['problem_num'];
			$LINE['problem_type'] = $list['problem_type'];
			$LINE['form_type'] = $list['form_type'];
			$LINE['question'] = $list['question'];
			$LINE['problem'] = $list['problem'];
			$LINE['voice_data'] = $list['voice_data'];
			$LINE['hint'] = $list['hint'];
			$LINE['explanation'] = $list['explanation'];
			$LINE['answer_time'] = $list['standard_time'];
			$LINE['parameter'] = $list['parameter'];
			$LINE['set_difficulty'] = $list['set_difficulty'];
			$LINE['hint_number'] = $list['hint_number'];
			$LINE['correct_number'] = $list['correct_number'];
			$LINE['clear_number'] = $list['clear_number'];
			$LINE['first_problem'] = $list['first_problem'];
			$LINE['latter_problem'] = $list['latter_problem'];
			$LINE['selection_words'] = $list['selection_words'];
			$LINE['correct'] = $list['correct'];
			$LINE['option1'] = $list['option1'];
			$LINE['option2'] = $list['option2'];
			$LINE['option3'] = $list['option3'];
			$LINE['option4'] = $list['option4'];
			$LINE['option5'] = $list['option5'];
			$LINE['course_num'] = $style_course_num;

			$sql2 = "SELECT * FROM course ".
				" WHERE course_num='".$style_course_num."'".
				" AND state='0';";
			if ($result2 = $cdb->query($sql2)) {
				$list2 = $cdb->fetch_assoc($result2);
				$LINE['write_type'] = $list2['write_type'];			//courseテーブルから取得
				$LINE['math_align'] = $list2['math_align'];			//  〃
				$LINE['language_type'] = $list2['language_type'];	//  〃
			}
			$LINE['course_name'] = $list2['course_name'];
			$LINE['stage_num'] = $style_stage_num;
			$LINE['stage_name'] = "判定テスト";		//"基本プラクティス";
			$LINE['lesson_name'] = " ";		//Unit1";
			$LINE['lesson_num'] = "0";		//"739";
			$LINE['unit_num'] = "0";		//"1549";
			$LINE['unit_name'] = " ";		//"Grammar";
			$LINE['block_num'] = "0";		//"2593";
			//$LINE['block_type'] = "6";		 //?????

			$sql2 = "SELECT * FROM course ".
				" WHERE course_num='".$style_course_num."'".
				" AND state='0';";
			if ($result2 = $cdb->query($sql2)) {
				$list2 = $cdb->fetch_assoc($result2);
				$LINE['write_type'] = $list2['write_type'];			//courseテーブルから取得
				$LINE['math_align'] = $list2['math_align'];			//  〃
				$LINE['language_type'] = $list2['language_type'];	//  〃
			}

			$_SESSION['course']['problem_table_type'] = "2";
			$_SESSION['course']['problem_num'] = $LINE['problem_num'];
		}
	  } else {
	  	echo "*";
	  }	//if (!$ERROR) {
	} else {
		foreach ($_SESSION['problem'] AS $key => $val) {
			$LINE[$key] = $val;
		}
	}

	// 判定名を取得
	if ($_POST['hantei_default_num']) {

		$hantei_default_num = $_POST['hantei_default_num'];

		$sql  = "SELECT ".
				" hmd.hantei_name, ".
				" hmd.hantei_type, ".
				" hmbl.course_num ".
				" FROM ".T_HANTEI_MS_DEFAULT." hmd ".
				" LEFT JOIN ".T_HANTEI_MS_BREAK_LAYER." hmbl ON hmd.hantei_default_num = hmbl.hantei_default_num AND hmbl.mk_flg = '0' ".
				" WHERE hmd.hantei_default_num='".$hantei_default_num."'".
				"   AND hmd.display = '1'".
				"   AND hmd.mk_flg = '0'".
				" LIMIT 1;";
 		// echo $sql."<br>";
 		if ($result = $cdb->query($sql)) {
 			if ($list = $cdb->fetch_assoc($result)) {
				$LINE['hantei_name'] = $list['hantei_name'];
				$LINE['hantei_type'] = $list['hantei_type'];
				$hantei_course_num = $list['course_num'];
			}
		}

		if ($hantei_course_num) {
			$sql = "SELECT ".
					" course_name ".
					" FROM ".T_COURSE.
					" WHERE course_num = '".$hantei_course_num."'".
					"   AND state='0';";
			// echo $sql."<br>";
			if ($result = $cdb->query($sql)) {
				if ($list = $cdb->fetch_assoc($result)) {
					$LINE['hantei_course_name'] = $list['course_name'];
				}
			}
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
			require_once(LOG_DIR . "problem_lib/hantei_display_problem.php");
		}
	}


	//	問題設定
	$_SESSION['record']['set_time'] = time();
	$display_problem = new hantei_display_problem();
	$display_problem_num = $display_problem->set_display_problem_num();
	$voice_button = $display_problem->set_voice_button();
	$question = $display_problem->set_question();
	$problem = $display_problem->set_problem();
	$set_form = $display_problem->set_form();
	$LESSONUNIT = "判定テスト確認";		// Windowタイトル設定
	//	ヒント表示
	if (hint_number == 99) {
		$hint = $display_problem->hint_check();
	}

	//	サブスペース(area3)
	$sub_space = $display_problem->display_area3("start");

	// 画面サイズに合わせる
	$size_check = "zoom_all(".$_SESSION['course']['course_num'].");";

	$ONLOAD_BODY = "";
	if (form_type == 5) {
		$MORE_SCRIPT = $display_problem->set_more_script();
		$answer_info = $display_problem->set_answer_info();
		$ONLOAD_BODY = "<body onload=\"TimerStartUp();".$size_check."\">";
	} elseif (form_type == 8) {
		$MORE_SCRIPT = $display_problem->set_more_script();
		$ONLOAD_BODY = "<body onload=\"make_ele();".$size_check."\">";
	} elseif (form_type == 3 || form_type == 4 || form_type == 10) {
		$MORE_SCRIPT = $display_problem->set_more_script_hand(); // add 2016/06/02 yoshizawa 手書き認識
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
	// add start hasegawa 2016/06/01 作図ツール
	} elseif (form_type == 13) {
		$MORE_SCRIPT = $display_problem->set_more_script();
	// add end hasegawa 20160/06/01
	} else {
		$MORE_SCRIPT = "";
		$ONLOAD_BODY = "";
	}

	if ($ONLOAD_BODY == "" && $size_check){
		$ONLOAD_BODY = "<body onLoad=\"".$size_check."\">";
	}

	$form_start = $display_problem->form_start($set_form);
	$form_end = $display_problem->form_end($set_form, false);

	if ($_SESSION['course']['problem_table_type'] == "2") {
		$name = $_SESSION['problem']['hantei_course_name'];
		$problem_kanri_no = "T".substr($name,0,3)."_".$_SESSION['record']['problem_num'];
	} else {
		$course_name_w = $_SESSION['problem']['default_course_name'];
		$lesson_name_w = $_SESSION['problem']['default_lesson_name'];
		$unit_name_w   = $_SESSION['problem']['default_unit_name'];
		$problem_kanri_no = "T".substr($course_name_w,0,3)."_U".substr($lesson_name_w,4)."_".substr($unit_name_w,0,1).$_SESSION['record']['problem_num'];
	}

	// TOEIC問題番号表示					// update oda 2013/09/25
	$title = "問題番号:";
	if (defined('language_type')) {
		if (language_type == "1") {
			$title = "Question:";
		}
	}
	$title = $title.$_SESSION['problem']['problem_num'];

	// 問題タイトル生成
	$display_problem_num  = "";
	$display_problem_num .= "<div class=\"hantei_problem_count\">0/0問</div>";
	$display_problem_num .= "<h2 class=\"question_title\">".$title."</h2>";
	$display_problem_num .= "<span class=\"problem_count\">&nbsp;</span>";
	$display_problem_num .= "<span class=\"problem_kanri_no\">".$problem_kanri_no."</span>\n";

	if (get_service_type(course_num) == "3") {

		$name = $_SESSION['problem']['hantei_course_name'];
		// update start oda 2014/01/28 Win7_IE11 No18
		//if ($_SESSION['problem']['hantei_type'] == "1") {
		//	$unit = "";
		//} elseif ($_SESSION['problem']['hantei_type'] == "2") {
		//	$unit = $_SESSION['problem']['hantei_name'];
		//}
		$unit = $_SESSION['problem']['hantei_name'];
		// update end oda 2014/01/28 Win7_IE11 No18

		$INPUTS[COURSENO] = array('result'=>'plane','value'=>substr($name,0,3));
		$unit_image = "";
		if (unit_name == "Grammar") {
			$unit_image = "<img src=\"/student/images/toeic/name_grammar.gif\" width=\"186\" height=\"37\" alt=\"Grammar\" class=\"mgn_btm_20\" />";
		} elseif (unit_name == "Vocabulary") {
			$unit_image = "<img src=\"/student/images/toeic/name_vocabulary.gif\" width=\"186\" height=\"37\" alt=\"Vocabulary\" class=\"mgn_btm_20\" />";
		} elseif (unit_name == "Listening/Reading") {
			$unit_image = "<img src=\"/student/images/toeic/name_listening_reading.gif\" width=\"274\" height=\"37\" alt=\"Listening/Reading\" class=\"mgn_btm_20\" />";
		}
		$INPUTS[UNITIMAGE] = array('result'=>'plane','value'=>$unit_image);
		//$lesson_unit_name = substr($unit,0,6);							// del oda 2014/01/28 Win7_IE11 No18
		$lesson_unit_name = $unit;											// add oda 2014/01/28 Win7_IE11 No18
		$INPUTS[UNITCOUNT] = array('result'=>'plane','value'=>$lesson_unit_name);

		// ヘッダ部のid値設定（背景イメージ設定）
		// update end oda 2014/01/28 Win7_IE11 No18
		//$head_tag = "box_head";
		//if ($_SESSION['problem']['hantei_type'] == "1") {
		//	$head_tag = "box_head_non";
		//}
		$head_tag = "box_head_hantei";
		// update end oda 2014/01/28 Win7_IE11 No18

		$title_img = "category_unit_test.gif";
		if ($_SESSION['problem']['hantei_type'] == "1") {
			$title_img = "category_course_test.gif";
		}

	}

	if (setting_type == 1) { unset($display_problem_num); }
	$make_html = new read_html();
//	$make_html->set_dir(STUDENT_TEMP_DIR.course_num."/");
//	$make_html->set_file("check_drill.htm");
	$make_html->set_dir(STUDENT_TEMP_DIR."/");
	$make_html->set_file("hantei_test_answer_drill.htm");

	$css_file = MATERIAL_TEMP_DIR.course_num."/".stage_num."/course.css";
	$css_path = "<link rel=\"stylesheet\" href=\"";
	if (file_exists($css_file)) {
		$css_path .= $css_file;
	} else {
		$css_path .= MATERIAL_TEMP_DIR.course_num."/course_main.css";
	}
	$css_path .= "\" type=\"text/css\">";

	$INPUTS[MORESCRIPT] = array('result'=>'plane','value'=>$MORE_SCRIPT.$css_path);
	$INPUTS[LESSONUNIT] = array('result'=>'plane','value'=>$LESSONUNIT);
	$INPUTS[DISPLAYPROBLEMNUM] = array('result'=>'plane','value'=>$display_problem_num);
	$INPUTS[VOICE] = array('result'=>'plane','value'=>$voice_button);
	$INPUTS[QUESTION] = array('result'=>'plane','value'=>$question);
	$INPUTS[PROBLEM] = array('result'=>'plane','value'=>$problem);
	$INPUTS[ANSWERINFO] = array('result'=>'plane','value'=>$answer_info);
	$INPUTS[ANSWERCOLUMN] = array('result'=>'plane','value'=>$set_form);
	$INPUTS[FORMSTART] = array('result'=>'plane','value'=>$form_start);
	$INPUTS[FORMEND] = array('result'=>'plane','value'=>$form_end);
//	$INPUTS[HINT] = array('result'=>'plane','value'=>$hint);						// del oda 2013/10/16 ヒントは表示しない
	$INPUTS[SUBSPACE] = array('result'=>'plane','value'=>$sub_space);
	$INPUTS[FUNCTIONKEY] = array('result'=>'plane','value'=>$FUNCTIONKEY);
	$INPUTS[ERROR] = array('result'=>'plane','value'=>$errors);
	$INPUTS['TITLEIMG'] = array('result'=>'plane', 'value'=>$title_img);
	$INPUTS['HEADTAG'] = array('result'=>'plane','value'=>$head_tag);
	$INPUTS['BACKURL'] = array('result'=>'plane','value'=>"window.close();");

	$make_html->set_rep_cmd($INPUTS);
	$html = $make_html->replace();
	if ($ONLOAD_BODY) { $html = ereg_replace("<body>",$ONLOAD_BODY,$html); }
	return $html;
}


/**
 * 解答確認処理
 *
 * AC:[A]管理者 UC1:[T02]スタート判定テスト.
 *
 * @author Azet
 * @return array エラーがならば
 */
function check_answer() {

	if ($_SESSION['course']['problem_table_type'] == "1") {
		$_SESSION['problem']['course_num'] = $_SESSION['problem_info']['course_num'];
		$_SESSION['problem']['stage_num'] = $_SESSION['problem_info']['stage_num'];
		$_SESSION['problem']['default_course_num'] = $_SESSION['problem_info']['default_course_num'];
		$_SESSION['problem']['default_stage_num'] = $_SESSION['problem_info']['default_stage_num'];
		$_SESSION['problem']['lesson_num'] = $_SESSION['problem_info']['lesson_num'];
		$_SESSION['problem']['unit_num'] = $_SESSION['problem_info']['unit_num'];
		$_SESSION['problem']['block_num'] = $_SESSION['problem_info']['block_num'];
	}

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
	// add start hasegawa 2016/06/02 作図ツール
	} elseif ($_SESSION['problem']['form_type'] == 13) {
		if ($_POST['answer']) {
			if($_POST['judgment'] == "true") {
				$_POST['answer'][0] = "t::".$_POST['answer'][0];
			} else {
				$_POST['answer'][0] = "f::".$_POST['answer'][0];
			}
		}
	// add end hasegawa 2016/06/02
	} else {
		if ($_SESSION['problem']['form_type'] == 10 &&
			 (!$_POST['answer'] && array_search("0",$_POST['answer']) === FALSE) &&
			 ($_SESSION['record']['answer'] || $_SESSION['record']['answer'] == "0")) {
			$flag = 1;
		}
		if ($flag != 1 && (!$_POST['answer'] && array_search("0",$_POST['answer']) === FALSE)) {
			$ERROR[] = "解答が確認できません。";
		}
	}

	if (!$ERROR && ($_POST['answer'] || array_search("0",$_POST['answer']) !== FALSE)) {
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
		require_once(LOG_DIR . "problem_lib/hantei_display_problem.php");
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
 * AC:[A]管理者 UC1:[T02]スタート判定テスト.
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
		require_once(LOG_DIR . "problem_lib/hantei_display_problem.php");
	}

	if ($_SESSION['record']['success'] == 1) {	//	正解

		$display_problem = new hantei_display_problem();
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
		$LESSONUNIT = "判定テスト確認";		// Windowタイトル設定

		//	サブスペース(area3)
		$html5_flag = true;
		$display_problem->html5_page_set($html5_flag);
		$sub_space = $display_problem->display_area3("true");

		$pre = ".mp3";
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		if (preg_match("/Firefox/i", $user_agent)) {
			$pre = ".ogg";
		}
		$voice_file = "/student/images/drill/true".$pre;

	} else {	//	不正解

		$display_problem = new hantei_display_problem();
		$display_problem->set_success($_SESSION['record']['success']);
		$display_problem->set_answers($_SESSION['record']['answer']);
		$display_problem->set_again($_SESSION['record']['again']);
		$display_problem_num = $display_problem->set_display_problem_num();
		$voice_button = $display_problem->set_voice_button();
		$question = $display_problem->set_question();
		$problem = $display_problem->set_problem();
		$answer_info = $display_problem->set_answer_info($_SESSION['record']['answer_info']);
		$set_form = "";
		if (correct_number == 0 || correct_number > 0 && $_SESSION['record']['again'] >= correct_number) {
			if (form_type == 5) {
				$display_problem->set_end_flg();
			}
		} else {
			$set_retry_msg = $display_problem->set_retry_msg();
			$set_form = $display_problem->set_form();
		}
		$LESSONUNIT = stage_name . " " . lesson_name . " ". unit_name;

		//	ヒント表示
		if (hint_number > 0 && ($_SESSION['record']['again'] >= hint_number || hint_number == 99) && $_SESSION['record']['again'] < correct_number) {	// update oda 2014/05/08 課題要望一覧No230 99の時も表示する
			$hint = $display_problem->hint_check();
		}

		//	正解を見るボタン
		$type = "true";
		if (correct_number == 0 || correct_number > 0 && $_SESSION['record']['again'] >= correct_number) {
			$explanation_button = $display_problem->explanation_button();
		//	unset($form_end);
			//$form_end_check = 1;
		}

		//	サブスペース(area3)
		$html5_flag = true;
		$display_problem->html5_page_set($html5_flag);
		$sub_space = $display_problem->display_area3("false");

		$pre = ".mp3";
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		if (preg_match("/Firefox/i", $user_agent)) { $pre = ".ogg"; }
		$voice_file = "/student/images/drill/false".$pre;
	}

	// TOEICサービスの時、正解／不正解音を鳴らす
	$check_voice = "";
	if ((get_service_type($_SESSION['course']['course_num']) == "3" || $html5_flag) && $voice_file != "") {
		$http = "http://";
		if($_SERVER['HTTPS']=='on'){
			$http = "https://";
		}
		$check_voice = <<<EOT
<!-- SOUND START-->
<script language="JavaScript" type="text/javascript">
setTimeout(
	function() {
		play_sound('correct');
	}, 500);
</script>
<audio src="{$voice_file}" id="correct">
  <object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="{$http}fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" width="0" height="0" id="correct_flash">
  <param name="movie" value="/student/images/drill/play_sound.swf?file={$voice_file}" />
  <embed src="/student/images/drill/play_sound.swf?file={$voice_file}" width="0" height="0" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="{$http}www.adobe.com/go/getflash">
  </embed>
  </object>
</audio>
<!-- SOUND END-->
EOT;
	}

	/* del oda 2013/10/08
	// 正解の時、正解音の後、音声を再生する
	if ($_SESSION['record']['success'] == 1) {
		if ($voice_button != "") {
			$check_voice .= <<<EOT
<!-- SOUND START-->
<script language="JavaScript" type="text/javascript">
setTimeout(
	function() {
		play_sound('sound');
	}, 1500);
</script>
EOT;
		}
	}
	*/

	// 画面サイズに合わせる
	$size_check = "zoom_all(".$_SESSION['course']['course_num'].");";


	$ONLOAD_BODY = "";
	if (form_type == 5) {
		if (correct_number > 0 && $_SESSION['record']['again'] < correct_number && $_SESSION['record']['success'] != 1) {
			$MORE_SCRIPT = $display_problem->set_more_script();
			$ONLOAD_BODY = "<body onload=\"TimerStartUp();".$size_check."\">";
		}
	} elseif (form_type == 8) {
		if (correct_number > 0 && $_SESSION['record']['again'] < correct_number && $_SESSION['record']['success'] != 1) {
			$MORE_SCRIPT = $display_problem->set_more_script();
			$ONLOAD_BODY = "<body onload=\"make_ele();".$size_check."\">";
		}
	} elseif (form_type == 3 || form_type == 4 || form_type == 10) {
		$MORE_SCRIPT = $display_problem->set_more_script_hand(); // add 2016/06/02 yoshizawa 手書き認識
		$tabindex = $display_problem->set_more_script();
		$ONLOAD_BODY = "";
	} elseif (form_type == 11) {
		$MORE_SCRIPT = $display_problem->set_more_script();
		$FUNCTIONKEY = $display_problem->fnkey();
	// add start hasegawa 2016/06/01 作図ツール
	} elseif (form_type == 13) {
		$MORE_SCRIPT = $display_problem->set_more_script();
	// add end hasegawa 2016/06/01
	} else {
		$MORE_SCRIPT = "";
		$ONLOAD_BODY = "";
	}

	if ($ONLOAD_BODY == "" && $size_check){
		$ONLOAD_BODY = "<body onLoad=\"".$size_check."\">";
	}

	$form_start = $display_problem->form_start($set_form);
	$form_end = $display_problem->form_end($set_form, false);

	if (get_service_type(course_num) == "3") {

		$name = $_SESSION['problem']['hantei_course_name'];
		// update start oda 2014/01/28 Win7_IE11 No18
		//if ($_SESSION['problem']['hantei_type'] == "1") {
		//	$unit = "";
		//} elseif ($_SESSION['problem']['hantei_type'] == "2") {
		//	$unit = $_SESSION['problem']['hantei_name'];
		//}
		$unit = $_SESSION['problem']['hantei_name'];
		// update end oda 2014/01/28 Win7_IE11 No18

		$INPUTS[COURSENO] = array('result'=>'plane','value'=>substr($name,0,3));
		$unit_image = "";
		if (unit_name == "Grammar") {
			$unit_image = "<img src=\"/student/images/toeic/name_grammar.gif\" width=\"186\" height=\"37\" alt=\"Grammar\" class=\"mgn_btm_20\" />";
		} elseif (unit_name == "Vocabulary") {
			$unit_image = "<img src=\"/student/images/toeic/name_vocabulary.gif\" width=\"186\" height=\"37\" alt=\"Vocabulary\" class=\"mgn_btm_20\" />";
		} elseif (unit_name == "Listening/Reading") {
			$unit_image = "<img src=\"/student/images/toeic/name_listening_reading.gif\" width=\"274\" height=\"37\" alt=\"Listening/Reading\" class=\"mgn_btm_20\" />";
		}
		$INPUTS[UNITIMAGE] = array('result'=>'plane','value'=>$unit_image);
		//$lesson_unit_name = substr($unit,0,6);							// del oda 2014/01/28 Win7_IE11 No18
		$lesson_unit_name = $unit;											// add oda 2014/01/28 Win7_IE11 No18
		$INPUTS[UNITCOUNT] = array('result'=>'plane','value'=>$lesson_unit_name);

		$INPUTS[CLOSEURL] = array('result'=>'plane','value'=>"window.close();");

		if ($_SESSION['course']['problem_table_type'] == "2") {
			$name = $_SESSION['problem']['hantei_course_name'];
			$problem_kanri_no = "T".substr($name,0,3)."_".$_SESSION['record']['problem_num'];
		} else {
			$course_name_w = $_SESSION['problem']['default_course_name'];
			$lesson_name_w = $_SESSION['problem']['default_lesson_name'];
			$unit_name_w   = $_SESSION['problem']['default_unit_name'];
			$problem_kanri_no = "T".substr($course_name_w,0,3)."_U".substr($lesson_name_w,4)."_".substr($unit_name_w,0,1).$_SESSION['record']['problem_num'];
		}

		// TOEIC問題番号表示					// update oda 2013/09/25
		$title = "問題番号:";
		if (defined('language_type')) {
			if (language_type == "1") {
				$title = "Question:";
			}
		}
		$title = $title.$_SESSION['problem']['problem_num'];

		// 問題タイトル生成
		$display_problem_num  = "";
		$display_problem_num .= "<div class=\"hantei_problem_count\">0/0問</div>";
		$display_problem_num .= "<h2 class=\"question_title\">".$title."</h2>";
		$display_problem_num .= "<span class=\"problem_count\">&nbsp;</span>";
		$display_problem_num .= "<span class=\"problem_kanri_no\">".$problem_kanri_no."</span>\n";

		// ヘッダ部のid値設定（背景イメージ設定）
		// update start oda 2014/01/28 Win7_IE11 No18
		//$head_tag = "box_head";
		//if ($_SESSION['problem']['hantei_type'] == "1") {
		//	$head_tag = "box_head_non";
		//}
		$head_tag = "box_head_hantei";
		// update end oda 2014/01/28 Win7_IE11 No18

		$title_img = "category_unit_test.gif";
		if ($_SESSION['problem']['hantei_type'] == "1") {
			$title_img = "category_course_test.gif";
		}
	}

	$make_html = new read_html();
//	$make_html->set_dir(STUDENT_TEMP_DIR.course_num."/");
//	$make_html->set_file("check_drill.htm");
	$make_html->set_dir(STUDENT_TEMP_DIR."/");
	$make_html->set_file("hantei_test_answer_drill.htm");

	$css_file = MATERIAL_TEMP_DIR.course_num."/".stage_num."/course.css";
	$css_path = "<link rel=\"stylesheet\" href=\"";
	if (file_exists($css_file)) {
		$css_path .= $css_file;
	} else {
		$css_path .= MATERIAL_TEMP_DIR.course_num."/course_main.css";
	}
	$css_path .= "\" type=\"text/css\">";

	$INPUTS[MORESCRIPT] = array('result'=>'plane','value'=>$MORE_SCRIPT.$css_path);
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
//	$INPUTS[HINT] = array('result'=>'plane','value'=>$hint);							// del oda 2013/10/16 ヒントは表示しない
	$INPUTS[EXPLBUTTON] = array('result'=>'plane','value'=>$explanation_button);
	$INPUTS[SUBSPACE] = array('result'=>'plane','value'=>$sub_space);
	$INPUTS[FUNCTIONKEY] = array('result'=>'plane','value'=>$FUNCTIONKEY);
	$INPUTS[ERROR] = array('result'=>'plane','value'=>$errors);
	$INPUTS[CHECKVOICE] = array('result'=>'plane','value'=>$check_voice);

	$INPUTS['TITLEIMG'] = array('result'=>'plane', 'value'=>$title_img);
	$INPUTS['HEADTAG'] = array('result'=>'plane','value'=>$head_tag);
	$INPUTS['BACKURL'] = array('result'=>'plane','value'=>"window.close();");

	$make_html->set_rep_cmd($INPUTS);
	$html = $make_html->replace();
	if ($ONLOAD_BODY) { $html = ereg_replace("<body>",$ONLOAD_BODY,$html); }
	return $html;
}



/**
 * 解答表示
 *
 * AC:[A]管理者 UC1:[T02]スタート判定テスト.
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
		require_once(LOG_DIR . "problem_lib/hantei_display_problem.php");
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

	//	解説ページ作成
	$display_problem = new hantei_display_problem();
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
	$LESSONUNIT = stage_name . " " . lesson_name . " ". unit_name;

	//	サブスペース(area3)
	$display_problem->html5_page_set(true);

	if ($_SESSION['record']['success'] == 1) {
		$sub_space = $display_problem->display_area3("start");
	} else {
		$sub_space = $display_problem->display_area3("false");
	}


	// 画面サイズに合わせる
	$size_check = "zoom_all(".$_SESSION['course']['course_num'].");";

	$ONLOAD_BODY = "";
	if (form_type == 5) {
		if (correct_number > 0 && $_SESSION['record']['again'] < correct_number && $_SESSION['record']['success'] != 1) {
			$MORE_SCRIPT = $display_problem->set_more_script();
			$ONLOAD_BODY = "<body onload=\"TimerStartUp();".$size_check."\">";
		}
		$answer_info = $display_problem->set_answer_info($_SESSION['record']['answer']);
	} elseif (form_type == 8) {
		if (correct_number > 0 && $_SESSION['record']['again'] < correct_number && $_SESSION['record']['success'] != 1) {
			$MORE_SCRIPT = $display_problem->set_more_script();
			$ONLOAD_BODY = "<body onload=\"make_ele();".$size_check."\">";
		}
		$answer_info = $display_problem->set_answer_info(correct);
	} elseif (form_type == 3 || form_type == 4 || form_type == 10) {
		$tabindex = $display_problem->set_more_script();
		if ($tabindex > 0) {
			// edit simon 2014-11-14
			//$ONLOAD_BODY = "<body onload=\"".$size_check."document.getElementById('tabindex1').focus();\">";
			$ONLOAD_BODY = "<body onload=\"".$size_check."\">";
		} else {
			$ONLOAD_BODY = "";
		}
		if (form_type != 4) {
			$answer_info = $display_problem->set_answer_info($_SESSION['record']['answer']);
		} else {
			$answer_info = $display_problem->set_answer_info($_SESSION['record']['answer_info']);
		}
	} elseif (form_type == 11) {
		$MORE_SCRIPT = $display_problem->set_more_script();
		$answer_info = $display_problem->set_answer_info(correct);
	// add start hasegawa 2016/06/02 作図ツール
	} elseif (form_type == 13) {
		$MORE_SCRIPT = $display_problem->set_more_script();
		$answer_info = $display_problem->set_answer_info();
	// add end hasegawa 2016/06/02

	} elseif (form_type == 1 || form_type == 2) {
		$answer_info = $display_problem->set_answer_info();
	} else {
		$MORE_SCRIPT = "";
		$ONLOAD_BODY = "";
	}

	if ($ONLOAD_BODY == "" && $size_check){
		$ONLOAD_BODY = "<body onLoad=\"".$size_check."\">";
	}

	$form_start = $display_problem->form_start($set_form);
	$form_end = $display_problem->form_end($set_form, true);

	$make_html = new read_html();
//	$make_html->set_dir(STUDENT_TEMP_DIR.course_num."/");
//	$make_html->set_file("check_drill.htm");
	$make_html->set_dir(STUDENT_TEMP_DIR."/");
	$make_html->set_file("hantei_test_answer_drill.htm");

	/* del oda 2013/10/08
	// 音声データが有った場合は、結果の時に自動再生する
	$check_voice = "";
	if ($voice_button != "") {
		$check_voice = <<<EOT
<!-- SOUND START-->
<script language="JavaScript" type="text/javascript">
setTimeout(
	function() {
		play_sound('sound');
	}, 500);
</script>
EOT;
	}
	*/

	if (get_service_type(course_num) == "3") {

		$name = $_SESSION['problem']['hantei_course_name'];
		// update start oda 2014/01/28 Win7_IE11 No18
		//if ($_SESSION['problem']['hantei_type'] == "1") {
		//	$unit = "";
		//} elseif ($_SESSION['problem']['hantei_type'] == "2") {
		//	$unit = $_SESSION['problem']['hantei_name'];
		//}
		$unit = $_SESSION['problem']['hantei_name'];
		// update end oda 2014/01/28 Win7_IE11 No18

		$INPUTS[COURSENO] = array('result'=>'plane','value'=>substr($name,0,3));
		$unit_image = "";
		if (unit_name == "Grammar") {
			$unit_image = "<img src=\"/student/images/toeic/name_grammar.gif\" width=\"186\" height=\"37\" alt=\"Grammar\" class=\"mgn_btm_20\" />";
		} elseif (unit_name == "Vocabulary") {
			$unit_image = "<img src=\"/student/images/toeic/name_vocabulary.gif\" width=\"186\" height=\"37\" alt=\"Vocabulary\" class=\"mgn_btm_20\" />";
		} elseif (unit_name == "Listening/Reading") {
			$unit_image = "<img src=\"/student/images/toeic/name_listening_reading.gif\" width=\"274\" height=\"37\" alt=\"Listening/Reading\" class=\"mgn_btm_20\" />";
		}
		$INPUTS[UNITIMAGE] = array('result'=>'plane','value'=>$unit_image);

		//$lesson_unit_name = substr($unit,0,6);			// del oda 2014/01/28 Win7_IE11 No18
		$lesson_unit_name = $unit;							// add oda 2014/01/28 Win7_IE11 No18
		$INPUTS[UNITCOUNT] = array('result'=>'plane','value'=>$lesson_unit_name);

		$INPUTS[CLOSEURL] = array('result'=>'plane','value'=>"window.close();");

		if ($answer_info) {
			$answer_info = preg_replace("/<sup>/i", "", $answer_info);
			$answer_info = preg_replace("/<\/sup>/i", "", $answer_info);
		}

		if ($_SESSION['course']['problem_table_type'] == "2") {
			$name = $_SESSION['problem']['hantei_course_name'];
			$problem_kanri_no = "T".substr($name,0,3)."_".$_SESSION['record']['problem_num'];
		} else {
			$course_name_w = $_SESSION['problem']['default_course_name'];
			$lesson_name_w = $_SESSION['problem']['default_lesson_name'];
			$unit_name_w   = $_SESSION['problem']['default_unit_name'];
			$problem_kanri_no = "T".substr($course_name_w,0,3)."_U".substr($lesson_name_w,4)."_".substr($unit_name_w,0,1).$_SESSION['record']['problem_num'];
		}

		// TOEIC問題番号表示					// update oda 2013/09/25
		$title = "問題番号:";
		if (defined('language_type')) {
			if (language_type == "1") {
				$title = "Question:";
			}
		}
		$title = $title.$_SESSION['problem']['problem_num'];

		// 問題タイトル生成
		$display_problem_num  = "";
		$display_problem_num .= "<div class=\"hantei_problem_count\">0/0問</div>";
		$display_problem_num .= "<h2 class=\"question_title\">".$title."</h2>";
		$display_problem_num .= "<span class=\"problem_count\">&nbsp;</span>";
		$display_problem_num .= "<span class=\"problem_kanri_no\">".$problem_kanri_no."</span>\n";

		// ヘッダ部のid値設定（背景イメージ設定）
		// update start oda 2014/01/28 Win7_IE11 No18
		//$head_tag = "box_head";
		//if ($_SESSION['problem']['hantei_type'] == "1") {
		//	$head_tag = "box_head_non";
		//}
		$head_tag = "box_head_hantei";
		// update end oda 2014/01/28 Win7_IE11 No18

		$title_img = "category_unit_test.gif";
		if ($_SESSION['problem']['hantei_type'] == "1") {
			$title_img = "category_course_test.gif";
		}
	}

	$css_file = MATERIAL_TEMP_DIR.course_num."/".stage_num."/course.css";
	$css_path = "<link rel=\"stylesheet\" href=\"";
	if (file_exists($css_file)) {
		$css_path .= $css_file;
	} else {
		$css_path .= MATERIAL_TEMP_DIR.course_num."/course_main.css";
	}
	$css_path .= "\" type=\"text/css\">";

	$INPUTS[MORESCRIPT] = array('result'=>'plane','value'=>$MORE_SCRIPT.$css_path);
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
//	$INPUTS[HINT] = array('result'=>'plane','value'=>$hint);								// del oda 2013/10/16 ヒントは表示しない
	$INPUTS[CORRECT] = array('result'=>'plane','value'=>$set_correct_html);
	$INPUTS[EXPLBUTTON] = array('result'=>'plane','value'=>$explanation_button);
	$INPUTS[SUBSPACE] = array('result'=>'plane','value'=>$sub_space);
	$INPUTS[CHECKVOICE] = array('result'=>'plane','value'=>$check_voice);

	$INPUTS['TITLEIMG'] = array('result'=>'plane', 'value'=>$title_img);
	$INPUTS['HEADTAG'] = array('result'=>'plane','value'=>$head_tag);
	$INPUTS['BACKURL'] = array('result'=>'plane','value'=>"window.close();");

	$make_html->set_rep_cmd($INPUTS);
	$html = $make_html->replace();
	if ($ONLOAD_BODY) { $html = ereg_replace("<body>",$ONLOAD_BODY,$html); }
	return $html;
}


/**
 * サービス情報取得
 *
 * AC:[A]管理者 UC1:[T02]スタート判定テスト.
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
