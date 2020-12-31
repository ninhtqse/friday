<?
/**
 * ベンチャー・リンク　すらら
 *
 * プラクティスステージ管理　コース作成
 *
 * @author Azet
 */

/**
 * HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function start() {
	if (ACTION == "check") {
		$ERROR = check();
	}
	if (!$ERROR) {
		if (ACTION == "add") { $ERROR = add(); }
		elseif (ACTION == "change") { $ERROR = change(); }
		elseif (ACTION == "del") { $ERROR = change(); }
		elseif (ACTION == "↑") { $ERROR = up(); }
		elseif (ACTION == "↓") { $ERROR = down(); }
	}

	if (MODE == "add") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= addform($ERROR); }
		} elseif (ACTION == "add") {
			if (!$ERROR) { $html .= course_list($ERROR); }
			else { $html .= addform($ERROR); }
		} else {
			$html .= addform($ERROR);
		}
	} elseif (MODE == "詳細") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= viewform($ERROR); }
		} elseif (ACTION == "change") {
			if (!$ERROR) { $html .= course_list($ERROR); }
			else { $html .= viewform($ERROR); }
		} else {
			$html .= viewform($ERROR);
		}
	} elseif (MODE == "削除") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= viewform($ERROR); }
		} elseif (ACTION == "change") {
			if (!$ERROR) { $html .= course_list($ERROR); }
			else { $html .= viewform($ERROR); }
		} else {
			$html .= check_html();
		}
	} else {
		$html .= course_list($ERROR);
	}

	return $html;
}


/**
 * コース選択一覧を作る機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function course_list($ERROR) {

	global $L_WRITE_TYPE,$L_DISPLAY;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//list($me_num,$onetime,$manager_level,$belong_num,$authority) = explode("<>",$_SESSION[myid]);

	if ($authority) { $L_AUTHORITY = explode("::",$authority); }

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__add",$_SESSION['authority'])===FALSE)) {
		$html .= "<br>\n";
		$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"add\">\n";
		$html .= "<input type=\"submit\" value=\"コース新規登録\">\n";
		$html .= "</form>\n";
	}

	$sql  = "SELECT * FROM " . T_COURSE .
			" WHERE state!='1' ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
		if (!$max) {
			$html .= "<br>\n";
			$html .= "今現在登録されているコースは有りません。<br>\n";
		}
		$html .= "<br>\n";
		$html .= "修正する場合は、修正するコースの詳細ボタンを押してください。<br>\n";
		$html .= "<table class=\"course_form\">\n";
		$html .= "<tr class=\"course_form_menu\">\n";
		if (!ereg("practice__view",$authority)
			&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__sort",$_SESSION['authority'])===FALSE))
		) {
			$html .= "<th>↑</th>\n";
			$html .= "<th>↓</th>\n";
		}
		$html .= "<th>登録番号</th>\n";
		$html .= "<th>コース名</th>\n";
		$html .= "<th>教科</th>\n";
		$html .= "<th>表示・非表示</th>\n";

		if (!ereg("practice__view",$authority)
			&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__view",$_SESSION['authority'])===FALSE))
		) {
			$html .= "<th>詳細</th>\n";
		}
		if (!ereg("practice__del",$authority)
			&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__del",$_SESSION['authority'])===FALSE))
		) {
			$html .= "<th>削除</th>\n";
		}
		$html .= "</tr>\n";
		$i = 1;
		while ($list = $cdb->fetch_assoc($result)) {
			if (!$ERROR) {
				$material_flash_path = MATERIAL_FLASH_DIR . $list['course_num'] . "/";
				if (!file_exists($material_flash_path)) {
					@mkdir($material_flash_path, 0777);
					@chmod($material_flash_path, 0777);
				}
				$temp_path = STUDENT_TEMP_DIR . $list['course_num'] . "/";
				if (!file_exists($temp_path)) {
					@mkdir($temp_path, 0777);
					@chmod($temp_path, 0777);
					$base_temp_unit_path = STUDENT_TEMP_DIR . "unit_list.htm";
					$new_temp_unit_path = STUDENT_TEMP_DIR . $list['course_num'] . "/unit_list.htm";
					@copy($base_temp_unit_path,$new_temp_unit_path);
					@chmod($new_temp_unit_path, 0666);

					$base_temp_result_path = STUDENT_TEMP_DIR . "result.htm";
					$new_temp_result_path = STUDENT_TEMP_DIR . $list['course_num'] . "/result.htm";
					@copy($base_temp_result_path,$new_temp_result_path);
					@chmod($new_temp_result_path, 0666);
				}

				$material_prob_path = MATERIAL_PROB_DIR . $list['course_num'] ."/";
				if (!file_exists($material_prob_path)) {
					@mkdir($material_prob_path,0777);
					@chmod($material_prob_path,0777);
				}

				$material_temp_path = MATERIAL_TEMP_DIR . $list['course_num'] ."/";
				if (!file_exists($material_temp_path)) {
					@mkdir($material_temp_path,0777);
					@chmod($material_temp_path,0777);
				}

				$material_voice_path = MATERIAL_VOICE_DIR . $list['course_num'] ."/";
				if (!file_exists($material_voice_path)) {
					@mkdir($material_voice_path,0777);
					@chmod($material_voice_path,0777);
				}

				// add 低学年2次対応 2016/12/08 yoshizawa -------------------------------
				// ../material/chart/l/が存在しなかったらディレクトリを作成します。
				$material_chart_p_path = MATERIAL_CHART_DIR . "l/";
				if (!file_exists($material_chart_p_path)) {
					@mkdir($material_chart_p_path,0777);
					@chmod($material_chart_p_path,0777);
				}
				//--------------------------------------------------------------------------

				// add 小学校高学年対応 2014/08/25 yoshizawa -------------------------------
				// ../material/chart/p/が存在しなかったらディレクトリを作成します。
				$material_chart_p_path = MATERIAL_CHART_DIR . "p/";
				if (!file_exists($material_chart_p_path)) {
					@mkdir($material_chart_p_path,0777);
					@chmod($material_chart_p_path,0777);
				}
				//--------------------------------------------------------------------------

				$material_chart_j_path = MATERIAL_CHART_DIR . "j/";
				if (!file_exists($material_chart_j_path)) {
					@mkdir($material_chart_j_path,0777);
					@chmod($material_chart_j_path,0777);
				}

				$material_chart_h_path = MATERIAL_CHART_DIR . "h/";
				if (!file_exists($material_chart_h_path)) {
					@mkdir($material_chart_h_path,0777);
					@chmod($material_chart_h_path,0777);
				}

				// add 低学年2次対応 2016/12/08 yoshizawa -------------------------------
				// ../material/chart/l/(コース番号)/が存在しなかったらディレクトリを作成します。
				$material_chart_pc_path = MATERIAL_CHART_DIR . "l/" . $list['course_num'] ."/";
				if (!file_exists($material_chart_pc_path)) {
					@mkdir($material_chart_pc_path,0777);
					@chmod($material_chart_pc_path,0777);
				}
				//--------------------------------------------------------------------------

				// add 小学校高学年対応 2014/08/25 yoshizawa -------------------------------
				// ../material/chart/p/(コース番号)/が存在しなかったらディレクトリを作成します。
				$material_chart_pc_path = MATERIAL_CHART_DIR . "p/" . $list['course_num'] ."/";
				if (!file_exists($material_chart_pc_path)) {
					@mkdir($material_chart_pc_path,0777);
					@chmod($material_chart_pc_path,0777);
				}
				//--------------------------------------------------------------------------

				$material_chart_jc_path = MATERIAL_CHART_DIR . "j/" . $list['course_num'] ."/";
				if (!file_exists($material_chart_jc_path)) {
					@mkdir($material_chart_jc_path,0777);
					@chmod($material_chart_jc_path,0777);
				}

				$material_chart_hc_path = MATERIAL_CHART_DIR . "h/" . $list['course_num'] ."/";
				if (!file_exists($material_chart_hc_path)) {
					@mkdir($material_chart_hc_path,0777);
					@chmod($material_chart_hc_path,0777);
				}

				$material_skill_img_path = MATERIAL_SKILL_IMG_DIR . $list['course_num'] ."/";
				if (!file_exists($material_skill_img_path)) {
					@mkdir($material_skill_img_path,0777);
					@chmod($material_skill_img_path,0777);
				}

				$material_skill_voice_path = MATERIAL_SKILL_VOICE_DIR . $list['course_num'] ."/";
				if (!file_exists($material_skill_voice_path)) {
					@mkdir($material_skill_voice_path,0777);
					@chmod($material_skill_voice_path,0777);
				}

				$material_print_path = MATERIAL_PRINT_DIR . $list['course_num'] ."/";
				if (!file_exists($material_print_path)) {
					@mkdir($material_print_path,0777);
					@chmod($material_print_path,0777);
				}
			}

			$up_submit = $down_submit = "&nbsp;";
			if ($i != 1) { $up_submit = "<input type=\"submit\" name=\"action\" value=\"↑\">\n"; }
			if ($i != $max) { $down_submit = "<input type=\"submit\" name=\"action\" value=\"↓\">\n"; }

			$html .= "<tr class=\"course_form_cell\">\n";
			$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"course_num\" value=\"{$list['course_num']}\">\n";
			if (!ereg("practice__view",$authority)
				&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__sort",$_SESSION['authority'])===FALSE))
			) {
				$html .= "<td>{$up_submit}</td>\n";
				$html .= "<td>{$down_submit}</td>\n";
			}
			$html .= "<td>{$list['course_num']}</td>\n";
			$html .= "<td>{$list['course_name']}</td>\n";
			$html .= "<td>{$L_WRITE_TYPE[$list['write_type']]}</td>\n";
			$html .= "<td>{$L_DISPLAY[$list['display']]}</td>\n";
			if (!ereg("practice__view",$authority)
				&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__view",$_SESSION['authority'])===FALSE))
			) {
				$html .= "<td><input type=\"submit\" name=\"mode\" value=\"詳細\"></td>\n";
			}
			if (!ereg("practice__del",$authority)
				&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__del",$_SESSION['authority'])===FALSE))
			) {
				$html .= "<td><input type=\"submit\" name=\"mode\" value=\"削除\"></td>\n";
			}
			$html .= "</form>\n";
			$html .= "</tr>\n";
			++$i;
		}
		$html .= "</table>\n";
	}
	return $html;
}


/**
 * 新規登録フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function addform($ERROR) {

	// global $L_WRITE_TYPE,$L_DISPLAY,$L_STATUS_SHOW_CLASS_TYPE,$L_MATH_ALIGN,$L_LANGUAGE_TYPE,$L_CHANGE_TYPE;		// update oda 2013/11/15 $L_CHANGE_TYPE追加 // del karasawa 2020/01/24 リニューアル管理画面 小テスト対応 *$L_DO_R追加　
    global $L_WRITE_TYPE,$L_DISPLAY,$L_STATUS_SHOW_CLASS_TYPE,$L_MATH_ALIGN,$L_LANGUAGE_TYPE,$L_CHANGE_TYPE,$L_DO_R;											// add karasawa 2020/01/24 リニューアル管理画面 小テスト対応 *$L_DO_R追加
    //	kaopiz 2020/08/20 speech start
    global $L_SPEECH_EVALUATION;
    $englishWriteType = 1;
    //	kaopiz 2020/08/20 speech end

	$html .= "<br>\n";
	$html .= "新規登録フォーム<br>\n";

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}
//    kaopiz 2020/08/20 del
//	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" name=\"pratice_course_add_form\">\n"; //  kaopiz 2020/08/20 speech
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
//	$make_html->set_file(PRACTICE_COURSE_FORM);
//  kaopiz 2020/08/20 speech start
    if ($_POST['write_type'] == $englishWriteType) {
        $make_html->set_file(PRACTICE_COURSE_FORM_1);
    } else {
        $make_html->set_file(PRACTICE_COURSE_FORM);
    }
//  kaopiz 2020/08/20 speech end

//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	$INPUTS[COURSENUM] = array('result'=>'plane','value'=>"---");
	$INPUTS[COURSENAME] = array('type'=>'text','name'=>'course_name','value'=>$_POST['course_name']);
//    kaopiz 2020/08/20 del
//    $INPUTS[WRITETYPE] = array('type'=>'select','name'=>'write_type','array'=>$L_WRITE_TYPE,'check'=>$_POST['write_type']);
//  kaopiz 2020/08/20 speech start
	$INPUTS[WRITETYPE] = array(
		'type'=>'select',
		'name'=>'write_type',
		'array'=>$L_WRITE_TYPE,
		'check'=>$_POST['write_type'],
		'action'=> 'onchange="document.pratice_course_add_form.action.value = \'change_write_type\'; document.pratice_course_add_form.submit();"'
	);
    //  kaopiz 2020/08/20 speech end
    $INPUTS[MATHALIGN] = array('type'=>'select','name'=>'math_align','array'=>$L_MATH_ALIGN,'check'=>$_POST['math_align']);		// add oda 2012/09/10
//	$INPUTS[DISPLAY] = array('type'=>'select','name'=>'display','array'=>$L_DISPLAY,'check'=>$_POST['display']);
	$INPUTS[REMARKS] = array('type'=>'textarea','name'=>'remarks','cols'=>'50','rows'=>'5','value'=>$_POST['remarks']);

	// 2012/10/01 add start oda
	if (!isset($_POST['language_type'])) { $_POST['language_type'] = 0; }
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("language_type");
	$newform->set_form_id("language_type_jp");
	$newform->set_form_check($_POST['language_type']);
	$newform->set_form_value('0');
	$language_type_jp = $newform->make();
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("language_type");
	$newform->set_form_id("language_type_en");
	$newform->set_form_check($_POST['language_type']);
	$newform->set_form_value('1');
	$language_type_en = $newform->make();
	$language_type = $language_type_jp . "<label for=\"language_type_jp\">{$L_LANGUAGE_TYPE[0]}</label> / " . $language_type_en . "<label for=\"language_type_en\">{$L_LANGUAGE_TYPE[1]}</label>";
	$INPUTS[LANGUAGETYPE] = array('result'=>'plane','value'=>$language_type);
	// 2012/10/01 add end oda

	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("display");
	$newform->set_form_id("display");
	$newform->set_form_check($_POST['display']);
	$newform->set_form_value('1');
	$display = $newform->make();
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("display");
	$newform->set_form_id("undisplay");
	$newform->set_form_check($_POST['display']);
	$newform->set_form_value('2');
	$undisplay = $newform->make();
	$display = $display . "<label for=\"display\">{$L_DISPLAY[1]}</label> / " . $undisplay . "<label for=\"undisplay\">{$L_DISPLAY[2]}</label>";
	$INPUTS[DISPLAY] = array('result'=>'plane','value'=>$display);
//	kaopiz 2020/08/20 speech start
    if ($_POST['write_type'] == $englishWriteType) {
        $INPUTS[OPTION1] = array('type' => 'select', 'name' => 'option1', 'array' => $L_SPEECH_EVALUATION, 'check' => $_POST['option1']);
    }
//    kaopiz 2020/08/20 speech end
	// add start karasawa 2020/01/24 リニューアル管理画面 小テスト対応
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("test_use_flg");
	$newform->set_form_id("test_use_flg_on");
	$newform->set_form_check($_POST['test_use_flg']);
	$newform->set_form_value('1');
	$test_use_flg_on = $newform->make();
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("test_use_flg");
	$newform->set_form_id("test_use_flg_off");
	$newform->set_form_check($_POST['test_use_flg']);
	$newform->set_form_value('0');
	$test_use_flg_off = $newform->make();
	$test_use_flg = $test_use_flg_on . "<label for=\"test_use_flg_on\">{$L_DO_R[1]}</label> / " . $test_use_flg_off . "<label for=\"test_use_flg_off\">{$L_DO_R[0]}</label>";
	// $test_use_flg = $test_use_flg_off . "<label for=\"test_use_flg_off\">{$L_DO_R[0]}</label> / " . $test_use_flg_on . "<label for=\"test_use_flg_on\">{$L_DO_R[1]}</label>";
	$INPUTS[TESTUSEFLG] = array('result'=>'plane','value'=>$test_use_flg);
	// add end karasawa 2020/01/24 リニューアル管理画面 小テスト対応

	// add start hirose 2020/09/10 テスト標準化開発
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("gtest_use_flg");
	$newform->set_form_id("gtest_use_flg_on");
	$newform->set_form_check($_POST['gtest_use_flg']);
	$newform->set_form_value('1');
	$gtest_use_flg_on = $newform->make();
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("gtest_use_flg");
	$newform->set_form_id("gtest_use_flg_off");
	$newform->set_form_check($_POST['gtest_use_flg']);
	$newform->set_form_value('0');
	$gtest_use_flg_off = $newform->make();
	$gtest_use_flg = $gtest_use_flg_on . "<label for=\"gtest_use_flg_on\">{$L_DO_R[1]}</label> / " . $gtest_use_flg_off . "<label for=\"gtest_use_flg_off\">{$L_DO_R[0]}</label>";
	$INPUTS[GTESTUSEFLG] = array('result'=>'plane','value'=>$gtest_use_flg);

	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("stest_use_flg");
	$newform->set_form_id("stest_use_flg_on");
	$newform->set_form_check($_POST['stest_use_flg']);
	$newform->set_form_value('1');
	$stest_use_flg_on = $newform->make();
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("stest_use_flg");
	$newform->set_form_id("stest_use_flg_off");
	$newform->set_form_check($_POST['stest_use_flg']);
	$newform->set_form_value('0');
	$stest_use_flg_off = $newform->make();
	$stest_use_flg = $stest_use_flg_on . "<label for=\"stest_use_flg_on\">{$L_DO_R[1]}</label> / " . $stest_use_flg_off . "<label for=\"stest_use_flg_off\">{$L_DO_R[0]}</label>";
	$INPUTS[STESTUSEFLG] = array('result'=>'plane','value'=>$stest_use_flg);
	// add end hirose 2020/09/10 テスト標準化開発

	// add start hirose 2020/11/05 テスト標準化開発 定期テスト
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("ttest_use_flg");
	$newform->set_form_id("ttest_use_flg_on");
	$newform->set_form_check($_POST['ttest_use_flg']);
	$newform->set_form_value('1');
	$ttest_use_flg_on = $newform->make();
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("ttest_use_flg");
	$newform->set_form_id("ttest_use_flg_off");
	$newform->set_form_check($_POST['ttest_use_flg']);
	$newform->set_form_value('0');
	$ttest_use_flg_off = $newform->make();
	$ttest_use_flg = $ttest_use_flg_on . "<label for=\"ttest_use_flg_on\">{$L_DO_R[1]}</label> / " . $ttest_use_flg_off . "<label for=\"ttest_use_flg_off\">{$L_DO_R[0]}</label>";
	$INPUTS[TTESTUSEFLG] = array('result'=>'plane','value'=>$ttest_use_flg);
	// add end hirose 2020/11/05 テスト標準化開発 定期テスト

	//del start kimura 2018/11/14 すらら英単語 //フィールド未使用につき入力フォーム削除
	// add start oda 2013/11/15
	// 生徒画面の看板表示変更ラジオボタン生成
	// if ($_POST['signboard_flg'] == "") {
	// 	$_POST['signboard_flg'] = "0";
	// }
	// $newform = new form_parts();
	// $newform->set_form_type("radio");
	// $newform->set_form_name("signboard_flg");
	// $newform->set_form_id("signboard_flg");
	// $newform->set_form_check($_POST['signboard_flg']);
	// $newform->set_form_value('1');
	// $radio1 = $newform->make();

	// $newform = new form_parts();
	// $newform->set_form_type("radio");
	// $newform->set_form_name("signboard_flg");
	// $newform->set_form_id("unsignboard_flg");
	// $newform->set_form_check($_POST['signboard_flg']);
	// $newform->set_form_value('0');
	// $radio2 = $newform->make();

	// $signboard_value = $radio1 . "<label for=\"signboard_flg_display\">".$L_CHANGE_TYPE[1]."</label> / " . $radio2 . "<label for=\"signboard_flg_updisplay\">".$L_CHANGE_TYPE[0]."</label>";
	// $INPUTS[SIGNBOARDFLG] = array('result'=>'plane','value'=>$signboard_value);

	// $INPUTS[SIGNBOARDNAME]  = array('type'=>'text','name'=>'signboard_name','value'=>$_POST['signboard_name']);
	// add end oda 2013/11/15
	//del end   kimura 2018/11/14 すらら英単語 //フィールド未使用につき入力フォーム削除

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"追加確認\">\n";
	$html .= "<input type=\"reset\" value=\"クリア\">\n";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"course_list\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";
	return $html;
}

/**
 * 表示の為のフォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function viewform($ERROR) {

	// global $L_WRITE_TYPE,$L_DISPLAY,$L_STATUS_SHOW_CLASS_TYPE,$L_MATH_ALIGN,$L_LANGUAGE_TYPE,$L_CHANGE_TYPE;		// update oda 2013/11/15 $L_CHANGE_TYPE追加		// del karasawa 2020/01/24 リニューアル管理画面 小テスト対応 *$L_DO_R追加
    global $L_WRITE_TYPE,$L_DISPLAY,$L_STATUS_SHOW_CLASS_TYPE,$L_MATH_ALIGN,$L_LANGUAGE_TYPE,$L_CHANGE_TYPE,$L_DO_R;												// add karasawa 2020/01/24 リニューアル管理画面 小テスト対応 *$L_DO_R追加
    // kaopiz 2020/08/20 speech start
    global $L_SPEECH_EVALUATION;
    $englishWriteType = 1;
	// kaopiz 2020/08/20 speech end

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$TABLE = T_COURSE;
    $TABLE2 = T_EXTERNAL_COURSE; // kaopiz 2020/08/20 speech
	$action = ACTION;

	if ($action) {
		foreach ($_POST as $key => $val) { $$key = $val; }
	} else {
		$sql = "SELECT * FROM $TABLE" .
			" WHERE course_num='$_POST[course_num]' AND state!='1' LIMIT 1;";

		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);

		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}
		foreach ($list as $key => $val) {
			$$key = replace_decode($val);
		}
	}
//    kaopiz 2020/08/20 speech start
    if (!$action || ($action && $action == 'change_write_type')) {
        $externalCourse = findExternalCourseNotDelete($_POST[course_num]);
        if ($externalCourse) {
            foreach ($externalCourse as $key => $val) {
                $$key = replace_decode($val);
            }
        }
    }
//    kaopiz 2020/08/20 speech end

	$html = "<br>\n";
	$html .= "詳細画面<br>\n";

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}
//    kaopiz 2020/08/20 del
//    $html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" name=\"pratice_course_update_form\">\n";     // kaopiz 2020/08/20 speech
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"$course_num\">\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
//    kaopiz 2020/08/20 speech start
	if ($write_type == $englishWriteType) {
		$make_html->set_file(PRACTICE_COURSE_FORM_1);
	} else {
		$make_html->set_file(PRACTICE_COURSE_FORM);
	}
//    kaopiz 2020/08/20 speech end
	if (!$course_num) { $course_num = "---"; }
	$INPUTS[COURSENUM] = array('result'=>'plane','value'=>$course_num);
	$INPUTS[COURSENAME] = array('type'=>'text','name'=>'course_name','value'=>$course_name);
//    kaopiz 2020/08/20 del
//    $INPUTS[WRITETYPE] = array('type'=>'select','name'=>'write_type','array'=>$L_WRITE_TYPE,'check'=>$write_type);
//    kaopiz 2020/08/20 speech start
    $INPUTS[WRITETYPE] = array(
        'type'=>'select',
        'name'=>'write_type',
        'array'=>$L_WRITE_TYPE,
        'check'=>$write_type,
        'action'=> 'onchange="document.pratice_course_update_form.action.value = \'change_write_type\'; document.pratice_course_update_form.submit();"'
    );
//    kaopiz 2020/08/20 speech end
	$INPUTS[MATHALIGN] = array('type'=>'select','name'=>'math_align','array'=>$L_MATH_ALIGN,'check'=>$math_align);		// add oda 2012/09/10
	$INPUTS[REMARKS] = array('type'=>'textarea','name'=>'remarks','cols'=>'50','rows'=>'5','value'=>$remarks);

	// 2012/10/01 add start oda
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("language_type");
	$newform->set_form_id("language_type_jp");
	$newform->set_form_check($language_type);
	$newform->set_form_value('0');
	$language_type_jp = $newform->make();
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("language_type");
	$newform->set_form_id("language_type_en");
	$newform->set_form_check($language_type);
	$newform->set_form_value('1');
	$language_type_en = $newform->make();
	$language_type_radio = $language_type_jp . "<label for=\"language_type_jp\">{$L_LANGUAGE_TYPE[0]}</label> / " . $language_type_en . "<label for=\"language_type_en\">{$L_LANGUAGE_TYPE[1]}</label>";
	$INPUTS[LANGUAGETYPE] = array('result'=>'plane','value'=>$language_type_radio);
	// 2012/10/01 add end oda

	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("display");
	$newform->set_form_id("display");
	$newform->set_form_check($display);
	$newform->set_form_value('1');
	$male = $newform->make();
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("display");
	$newform->set_form_id("undisplay");
	$newform->set_form_check($display);
	$newform->set_form_value('2');
	$female = $newform->make();
	$display = $male . "<label for=\"display\">{$L_DISPLAY[1]}</label> / " . $female . "<label for=\"undisplay\">{$L_DISPLAY[2]}</label>";
	$INPUTS[DISPLAY] = array('result'=>'plane','value'=>$display);
//    kaopiz 2020/08/20 speech start
    if ($write_type == $englishWriteType) {
        $INPUTS[OPTION1] = array('type' => 'select', 'name' => 'option1', 'array' => $L_SPEECH_EVALUATION, 'check' => $option1);
    }
//    kaopiz 2020/08/20 speech end
	// add start karasawa 2020/01/24 リニューアル管理画面 小テスト対応
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("test_use_flg");
	$newform->set_form_id("test_use_flg_on");
	$newform->set_form_check($test_use_flg);
	$newform->set_form_value('1');
	$test_use_flg_on = $newform->make();
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("test_use_flg");
	$newform->set_form_id("test_use_flg_off");
	$newform->set_form_check($test_use_flg);
	$newform->set_form_value('0');
	$test_use_flg_off = $newform->make();
	$test_use_flg = $test_use_flg_on . "<label for=\"test_use_flg_on\">{$L_DO_R[1]}</label> / " . $test_use_flg_off . "<label for=\"test_use_flg_off\">{$L_DO_R[0]}</label>";
	// $test_use_flg = $test_use_flg_off . "<label for=\"test_use_flg_off\">{$L_DO_R[0]}</label> / " . $test_use_flg_on . "<label for=\"test_use_flg_on\">{$L_DO_R[1]}</label>";
	$INPUTS[TESTUSEFLG] = array('result'=>'plane','value'=>$test_use_flg);
	// add end karasawa 2020/01/24 リニューアル管理画面 小テスト対応

	// add start hirose 2020/09/10 テスト標準化開発
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("gtest_use_flg");
	$newform->set_form_id("gtest_use_flg_on");
	$newform->set_form_check($gtest_use_flg);
	$newform->set_form_value('1');
	$gtest_use_flg_on = $newform->make();
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("gtest_use_flg");
	$newform->set_form_id("gtest_use_flg_off");
	$newform->set_form_check($gtest_use_flg);
	$newform->set_form_value('0');
	$gtest_use_flg_off = $newform->make();
	$gtest_use_flg = $gtest_use_flg_on . "<label for=\"gtest_use_flg_on\">{$L_DO_R[1]}</label> / " . $gtest_use_flg_off . "<label for=\"gtest_use_flg_off\">{$L_DO_R[0]}</label>";
	$INPUTS[GTESTUSEFLG] = array('result'=>'plane','value'=>$gtest_use_flg);

	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("stest_use_flg");
	$newform->set_form_id("stest_use_flg_on");
	$newform->set_form_check($stest_use_flg);
	$newform->set_form_value('1');
	$stest_use_flg_on = $newform->make();
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("stest_use_flg");
	$newform->set_form_id("stest_use_flg_off");
	$newform->set_form_check($stest_use_flg);
	$newform->set_form_value('0');
	$stest_use_flg_off = $newform->make();
	$stest_use_flg = $stest_use_flg_on . "<label for=\"stest_use_flg_on\">{$L_DO_R[1]}</label> / " . $stest_use_flg_off . "<label for=\"stest_use_flg_off\">{$L_DO_R[0]}</label>";
	$INPUTS[STESTUSEFLG] = array('result'=>'plane','value'=>$stest_use_flg);
	// add end hirose 2020/09/10 テスト標準化開発

	// add start hirose 2020/11/05 テスト標準化開発 定期テスト
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("ttest_use_flg");
	$newform->set_form_id("ttest_use_flg_on");
	$newform->set_form_check($ttest_use_flg);
	$newform->set_form_value('1');
	$ttest_use_flg_on = $newform->make();
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("ttest_use_flg");
	$newform->set_form_id("ttest_use_flg_off");
	$newform->set_form_check($ttest_use_flg);
	$newform->set_form_value('0');
	$ttest_use_flg_off = $newform->make();
	$ttest_use_flg = $ttest_use_flg_on . "<label for=\"ttest_use_flg_on\">{$L_DO_R[1]}</label> / " . $ttest_use_flg_off . "<label for=\"ttest_use_flg_off\">{$L_DO_R[0]}</label>";
	$INPUTS[TTESTUSEFLG] = array('result'=>'plane','value'=>$ttest_use_flg);
	// add end hirose 2020/11/05 テスト標準化開発 定期テスト

	//del start kimura 2018/11/14 すらら英単語 //フィールド未使用につき入力フォーム削除
	// add start oda 2013/11/15
	// 生徒画面の看板表示変更ラジオボタン生成
	// $newform = new form_parts();
	// $newform->set_form_type("radio");
	// $newform->set_form_name("signboard_flg");
	// $newform->set_form_id("signboard_flg");
	// $newform->set_form_check($signboard_flg);
	// $newform->set_form_value('1');
	// $radio1 = $newform->make();

	// $newform = new form_parts();
	// $newform->set_form_type("radio");
	// $newform->set_form_name("signboard_flg");
	// $newform->set_form_id("unsignboard_flg");
	// $newform->set_form_check($signboard_flg);
	// $newform->set_form_value('0');
	// $radio2 = $newform->make();

	// $signboard_value = $radio1 . "<label for=\"signboard_flg_display\">".$L_CHANGE_TYPE[1]."</label> / " . $radio2 . "<label for=\"signboard_flg_updisplay\">".$L_CHANGE_TYPE[0]."</label>";
	// $INPUTS[SIGNBOARDFLG] = array('result'=>'plane','value'=>$signboard_value);

	// $INPUTS[SIGNBOARDNAME]  = array('type'=>'text','name'=>'signboard_name','value'=>$signboard_name);
	// add end oda 2013/11/15
	//del end   kimura 2018/11/14 すらら英単語 //フィールド未使用につき入力フォーム削除

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"変更確認\">";
	$html .= "<input type=\"reset\" value=\"クリア\">";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"course_list\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}

/**
 * 確認する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function check() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$TABLE = T_COURSE;
	$mode = MODE;

	if (!$_POST['course_name']) { $ERROR[] = "コース名が未入力です。"; }
	else {
		if ($mode == "add") {
			$sql  = "SELECT * FROM $TABLE" .
					" WHERE state!='1' AND course_name='$_POST[course_name]'";
		} else {
			$sql  = "SELECT * FROM $TABLE" .
					" WHERE state!='1' AND course_num!='$_POST[course_num]' AND course_name='$_POST[course_name]'";
		}
		if ($result = $cdb->query($sql)) {
			$count = $cdb->num_rows($result);
		}
		if ($count > 0) { $ERROR[] = "入力されたコース名は既に登録されております。"; }
	}
	if (!$_POST['write_type']) { $ERROR[] = "教科が未選択です。"; }
	// add start oda 2012/09/10
	if ($_POST['math_align'] == 1 && $_POST['write_type'] != 3) {
		$ERROR[] = "数学フォーム値の右寄せは、教科が数学の時のみ有効です。";
	}
	// add end oda 2012/09/10

	if ($_POST['signboard_flg'] == "1" && !$_POST['signboard_name']) {			// add oda 2013/11/15
		$ERROR[] = "生徒画面の看板表示内容が未入力です。";
	}

	if (!$_POST['display']) { $ERROR[] = "表示・非表示が未選択です。"; }
	if ($_POST['test_use_flg'] == "" && !is_numeric($_POST['test_use_flg']) || ($_POST['test_use_flg'] > 1)) { $ERROR[] = "小テストで使用するが未選択です。"; }	// add karasawa 2020/01/24 リニューアル管理画面 小テスト対応
	// add start hirose 2020/09/10 テスト標準化開発
	if ($_POST['gtest_use_flg'] == "" && !is_numeric($_POST['gtest_use_flg']) || ($_POST['gtest_use_flg'] > 1)) { $ERROR[] = "学力診断テストテストで使用するが未選択です。"; }
	if ($_POST['stest_use_flg'] == "" && !is_numeric($_POST['stest_use_flg']) || ($_POST['stest_use_flg'] > 1)) { $ERROR[] = "数学検定テストで使用するが未選択です。"; }
	// add end hirose 2020/09/10 テスト標準化開発
	if ($_POST['ttest_use_flg'] == "" && !is_numeric($_POST['ttest_use_flg']) || ($_POST['ttest_use_flg'] > 1)) { $ERROR[] = "定期テストで使用するが未選択です。"; }// add hirose 2020/11/05 テスト標準化開発 定期テスト
//	if (!$_POST['remarks']) { $ERROR[] = "備考が未入力です。"; }
	return $ERROR;
}

/**
 * 確認フォーム作成
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function check_html() {

	// global $L_WRITE_TYPE,$L_DISPLAY,$L_STATUS_SHOW_CLASS_TYPE,$L_MATH_ALIGN,$L_LANGUAGE_TYPE,$L_CHANGE_TYPE;		// update oda 2013/11/15 $L_CHANGE_TYPE追加	// del karasawa 2020/01/24 リニューアル管理画面 小テスト対応 *$L_DO_R追加
    global $L_WRITE_TYPE,$L_DISPLAY,$L_STATUS_SHOW_CLASS_TYPE,$L_MATH_ALIGN,$L_LANGUAGE_TYPE,$L_CHANGE_TYPE,$L_DO_R;											// add karasawa 2020/01/24 リニューアル管理画面 小テスト対応 *$L_DO_R追加
//    kaopiz 2020/08/20 speech start
    global  $L_SPEECH_EVALUATION;
    $englishWriteType = 1;
//    kaopiz 2020/08/20 speech end
	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$TABLE = T_COURSE;
    $TABLE2 = T_EXTERNAL_COURSE;  // kaopiz 2020/08/20 speech
	$action = ACTION;

	if ($_POST) {
		foreach ($_POST as $key => $val) {
			if ($key == "action") {
				if (MODE == "add") { $val = "add"; }
				elseif (MODE == "詳細") { $val = "change"; }
			}
			$HIDDEN .= "<input type=\"hidden\" name=\"$key\" value=\"$val\">\n";
		}
	}

	if ($action) {
		foreach ($_POST as $key => $val) { $$key = $val; }
	} else {
		$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"change\">\n";
		$sql = "SELECT * FROM $TABLE" .
			" WHERE course_num='{$_POST[course_num]}' AND state!='1' LIMIT 1;";

		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);

		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}
		foreach ($list as $key => $val) {
			$$key = replace_decode($val);
		}
//        kaopiz 2020/08/20 speech start
        $externalCourse = findExternalCourseNotDelete($_POST[course_num]);
        if ($externalCourse) {
            foreach ($externalCourse as $key => $val) {
                $$key = replace_decode($val);
            }
        }
//        kaopiz 2020/08/20 speech end
	}

	if (MODE != "削除") { $button = "登録"; } else { $button = "削除"; }
	$html = "<br>\n";
	$html .= "確認画面：以下の内容で{$button}してもよろしければ{$button}ボタンをクリックしてください。<br>\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
//    kaopiz 2020/08/20 del
//    $make_html->set_file(PRACTICE_COURSE_FORM);
//    kaopiz 2020/08/20 speech start
    if (isset($option1) && $write_type == $englishWriteType) {
        $make_html->set_file(PRACTICE_COURSE_FORM_1);
    } else {
        $make_html->set_file(PRACTICE_COURSE_FORM);
    }
// kaopiz 2020/08/20 speech end
//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	if (!$course_num) { $course_num = "---"; }
	$INPUTS[COURSENUM] = array('result'=>'plane','value'=>$course_num);
	$INPUTS[COURSENAME] = array('result'=>'plane','value'=>$course_name);
	$INPUTS[WRITETYPE] = array('result'=>'plane','value'=>$L_WRITE_TYPE[$write_type]);
	$INPUTS[MATHALIGN] = array('result'=>'plane','value'=>$L_MATH_ALIGN[$math_align]);			// add oda 2012/09/10
	$INPUTS[LANGUAGETYPE] = array('result'=>'plane','value'=>$L_LANGUAGE_TYPE[$language_type]);	// add oda 2012/10/01
	$INPUTS[TESTUSEFLG] = array('result'=>'plane','value'=>$L_DO_R[$test_use_flg]);				// add karasawa 2020/01/24 リニューアル管理画面 小テスト対応
	// add start hirose 2020/09/10 テスト標準化開発
	$INPUTS[GTESTUSEFLG] = array('result'=>'plane','value'=>$L_DO_R[$gtest_use_flg]);
	$INPUTS[STESTUSEFLG] = array('result'=>'plane','value'=>$L_DO_R[$stest_use_flg]);
	// add end hirose 2020/09/10 テスト標準化開発
	$INPUTS[TTESTUSEFLG] = array('result'=>'plane','value'=>$L_DO_R[$ttest_use_flg]);// add hirose 2020/11/05 テスト標準化開発 定期テスト
	$INPUTS[DISPLAY] = array('result'=>'plane','value'=>$L_DISPLAY[$display]);
	$INPUTS[OPTION1] = array('result'=>'plane','value'=>$L_SPEECH_EVALUATION[$option1]); // kaopiz 2020/08/20 speech
	$INPUTS[SIGNBOARDFLG]  = array('result'=>'plane','value'=>$L_CHANGE_TYPE[$signboard_flg]);			// add oda 2013/11/15
	$INPUTS[SIGNBOARDNAME] = array('result'=>'plane','value'=>$signboard_name);							// add oda 2013/11/15
	$remarks_ = nl2br($remarks);
	$INPUTS[REMARKS] = array('result'=>'plane','value'=>$remarks_);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" style=\"float:left\">\n";
	$html .= $HIDDEN;
	$html .= "<input type=\"submit\" value=\"$button\">\n";
	$html .= "</form>";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";

	if ($action) {
		$HIDDEN2 = explode("\n",$HIDDEN);
		foreach ($HIDDEN2 as $key => $val) {
			if (ereg("name=\"action\"",$val)) {
				$HIDDEN2[$key] = "<input type=\"hidden\" name=\"action\" value=\"back\">";
				break;
			}
		}
		$HIDDEN2 = implode("\n",$HIDDEN2);

		$html .= $HIDDEN2;
	} else {
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"course_list\">\n";
	}
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}

/**
 * DB新規登録
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function add() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$TABLE = T_COURSE;
	$action = ACTION;

	foreach ($_POST as $key => $val) {
//        kaopiz 2020/08/20 del
//        if ($key == "action") { continue; }
		if ($key == "action" || $key == 'option1') { continue; } // kaopiz 2020/08/20 speech
		$INSERT_DATA[$key] = "$val";
	}
	$INSERT_DATA[update_time] = "now()";

	$ERROR = $cdb->insert($TABLE,$INSERT_DATA);

	if (!$ERROR) {
		$course_num = $cdb->insert_id();
		$INSERT_DATA[list_num] = $course_num;
		$where = " WHERE course_num='$course_num' LIMIT 1;";

        $ERROR = $cdb->update($TABLE,$INSERT_DATA,$where);
//        kaopiz 2020/08/20 speech start
        $option1 = isset($_POST['option1']) ? $_POST['option1'] : null;
        if (!$ERROR && $option1 !== null) {
            $ERROR = insert_external_course($course_num, $_POST['option1']);
        }
//        kaopiz 2020/08/20 speech end
	}

	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}

/**
 * DB変更
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function change() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$TABLE = T_COURSE;
	$TABLE2 = T_EXTERNAL_COURSE; // kaopiz 2020/08/20 speech
	$action = ACTION;

	if (MODE == "詳細") {
		$INSERT_DATA[course_name] = $_POST['course_name'];
		$INSERT_DATA[write_type] = $_POST['write_type'];
		$INSERT_DATA[math_align] = $_POST['math_align'];			// add oda 2012/09/10
		$INSERT_DATA[language_type] = $_POST['language_type'];		// add oda 2012/10/01
		$INSERT_DATA[test_use_flg] = $_POST['test_use_flg'];		// add karasawa 2020/01/27 リニューアル管理画面 小テスト対応
		$INSERT_DATA['gtest_use_flg'] = $_POST['gtest_use_flg'];	// add hirose 2020/09/10 テスト標準化開発
		$INSERT_DATA['stest_use_flg'] = $_POST['stest_use_flg'];	// add hirose 2020/09/10 テスト標準化開発
		$INSERT_DATA['ttest_use_flg'] = $_POST['ttest_use_flg'];	// add hirose 2020/11/05 テスト標準化開発 定期テスト
		$INSERT_DATA[display] = $_POST['display'];
		$INSERT_DATA['signboard_flg']  = $_POST['signboard_flg'];		// add oda 2013/11/15
		$INSERT_DATA['signboard_name'] = $_POST['signboard_name'];		// add oda 2013/11/15
		$INSERT_DATA[remarks] = $_POST['remarks'];
		$INSERT_DATA[update_time] = "now()";

		$where = " WHERE course_num='$_POST[course_num]' LIMIT 1;";

		$ERROR = $cdb->update($TABLE,$INSERT_DATA,$where);

	} elseif (MODE == "削除") {
		$INSERT_DATA[display] = 2;
		$INSERT_DATA[state] = 1;
		$INSERT_DATA[update_time] = "now()";
		$where = " WHERE course_num='$_POST[course_num]' LIMIT 1;";

		$ERROR = $cdb->update($TABLE,$INSERT_DATA,$where);

	}
//    kaopiz 2020/08/20 speech start
    if (!$ERROR) {
        $currentUserId = $_SESSION['myid']['id'];
        $externalCourse = findExternalCourse($_POST[course_num]);
        $option1 = isset($_POST['option1']) ? $_POST['option1'] : null;
        if (MODE == "詳細" && $option1 !== null) {
            if (!$externalCourse) {
                insert_external_course($_POST['course_num'], $option1);
            } else {
                $INSERT_DATA2['option1'] = $option1;
                $INSERT_DATA2['upd_tts_id'] = $currentUserId;
                $INSERT_DATA2['upd_date'] = "now()";
                $INSERT_DATA2['move_flg'] = 0;
                $ERROR = $cdb->update($TABLE2, $INSERT_DATA2, $where);
            }
        } elseif ($externalCourse) {
            $INSERT_DATA2['move_flg'] = 1;
            $INSERT_DATA2['move_tts_id'] = $currentUserId;
            $INSERT_DATA2['move_date'] = "now()";
            $ERROR = $cdb->update($TABLE2, $INSERT_DATA2, $where);
        }
    }
//    kaopiz 2020/08/20 speech end

	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}

/**
 * COURSEを上がる機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function up() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$TABLE = T_COURSE;
	$action = ACTION;

	$sql  = "SELECT * FROM $TABLE" .
			" WHERE course_num='$_POST[course_num]' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$m_course_num = $list['course_num'];
		$m_list_num = $list['list_num'];
	}
	if (!$m_course_num || !$m_list_num) { $ERROR[] = "移動するコース情報が取得できません。"; }

	if (!$ERROR) {
		$sql  = "SELECT * FROM $TABLE" .
				" WHERE state!='1' AND list_num<'$m_list_num' ORDER BY list_num DESC LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$c_course_num = $list['course_num'];
			$c_list_num = $list['list_num'];
		}
	}
	if (!$c_course_num || !$c_list_num) { $ERROR[] = "移動されるコース情報が取得できません。"; }

	if (!$ERROR) {
		$INSERT_DATA[list_num] = $c_list_num;
		$INSERT_DATA[update_time] = "now()";
		$where = " WHERE course_num='$m_course_num' LIMIT 1;";

		$ERROR = $cdb->update($TABLE,$INSERT_DATA,$where);
	}

	if (!$ERROR) {
		$INSERT_DATA[list_num] = $m_list_num;
		$INSERT_DATA[update_time] = "now()";
		$where = " WHERE course_num='$c_course_num' LIMIT 1;";

		$ERROR = $cdb->update($TABLE,$INSERT_DATA,$where);
	}

	return $ERROR;
}

/**
 * COURSEを下がる機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function down() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$TABLE = T_COURSE;
	$action = ACTION;

	$sql  = "SELECT * FROM $TABLE" .
			" WHERE course_num='$_POST[course_num]' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$m_course_num = $list['course_num'];
		$m_list_num = $list['list_num'];
	}
	if (!$m_course_num || !$m_list_num) { $ERROR[] = "移動するコース情報が取得できません。"; }

	if (!$ERROR) {
		$sql  = "SELECT * FROM $TABLE" .
				" WHERE state!='1' AND list_num>'$m_list_num' ORDER BY list_num LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$c_course_num = $list['course_num'];
			$c_list_num = $list['list_num'];
		}
	}
	if (!$c_course_num || !$c_list_num) { $ERROR[] = "移動されるコース情報が取得できません。"; }

	if (!$ERROR) {
		$INSERT_DATA[list_num] = $c_list_num;
		$INSERT_DATA[update_time] = "now()";
		$where = " WHERE course_num='$m_course_num' LIMIT 1;";

		$ERROR = $cdb->update($TABLE,$INSERT_DATA,$where);
	}

	if (!$ERROR) {
		$INSERT_DATA[list_num] = $m_list_num;
		$INSERT_DATA[update_time] = "now()";
		$where = " WHERE course_num='$c_course_num' LIMIT 1;";

		$ERROR = $cdb->update($TABLE,$INSERT_DATA,$where);
	}

	return $ERROR;
}
?>
