<?
/**
 * ベンチャー・リンク　すらら
 *
 * 旺文社・ＴＯＥＩＣ管理　サービス管理
 *
 * 履歴
 * 2012/05/28 初期設定
 *
 * @author Azet
 */

// add oda

/**
 * HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function start() {

	// 処理アクションにより、フローを制御
	// チェック処理
	if (ACTION == "check") {
		$ERROR = check();
	}

	if (MODE == "course_form" && ACTION == "確認") {
		$ERROR = course_check();
	}

	// 登録・修正・削除
	if (!$ERROR) {
		if (ACTION == "add") { $ERROR = add(); }
		elseif (ACTION == "change") { $ERROR = change(); }
		elseif (ACTION == "del") { $ERROR = change(); }
		elseif (ACTION == "course_add") { $ERROR = course_add(); }
		elseif (ACTION == "course_change") { $ERROR = course_change(); }
	}

	// 登録処理
	if (MODE == "add") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= addform($ERROR); }
		} elseif (ACTION == "add") {
			if (!$ERROR) { $html .= service_list($ERROR); }
			else { $html .= addform($ERROR); }
		} else {
			$html .= addform($ERROR);
		}

	// 詳細画面遷移
	} elseif (MODE == "詳細") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= viewform($ERROR); }
		} elseif (ACTION == "change") {
			if (!$ERROR) { $html .= service_list($ERROR); }
			else { $html .= viewform($ERROR); }
		} else {
			$html .= viewform($ERROR);
		}

	// 削除処理
	} elseif (MODE == "削除") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= viewform($ERROR); }
		} elseif (ACTION == "change") {
			if (!$ERROR) { $html .= service_list($ERROR); }
			else { $html .= viewform($ERROR); }
		} else {
			$html .= check_html();
		}
	} elseif (ACTION == "service_up") {
		$ERROR = up();
		$html .= service_list($ERROR);
	} elseif (ACTION == "service_down") {
		$ERROR = down();
		$html .= service_list($ERROR);
	} elseif (MODE == "course_form") {
		if (ACTION == "course_check" || ACTION == "確認") {
			if (!$ERROR) {
				$html .= course_check_html();
			} else {
				$html .= course_form($ERROR);
			}
		} elseif (ACTION == "course_add") {
			if (!$ERROR) {
				$html .= viewform($ERROR);
			} else {
				$html .= course_form($ERROR);
			}
		} elseif (ACTION == "course_change") {
			if (!$ERROR) {
				$html .= viewform($ERROR);
			} else {
				$html .= course_form($ERROR);
			}
		} else {
			$html .= course_form($ERROR);
		}
	} elseif (MODE == "course_delete") {
		if (ACTION == "course_change") {
			if (!$ERROR) {
				$html .= viewform($ERROR);
			} else {
				$html .= course_check_html();
			}
		} else {
			$html .= course_check_html();
		}
	// 一覧表示
	} else {
		$html .= service_list($ERROR);
	}

	return $html;
}


/**
 * 一覧表示処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 */
function service_list($ERROR) {

	global $L_SETUP_TYPE,$L_SETUP_TYPE_SUB,$L_COURSE_TYPE,$L_DISPLAY;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// セション情報取得
	//list($me_num,$onetime,$manager_level,$belong_num,$authority) = explode("<>",$_SESSION[myid]);

	// 権限取得
	if ($authority) { $L_AUTHORITY = explode("::",$authority); }

	// エラーメッセージが存在する場合、メッセージ表示
	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	// 登録権限が有るＩＤの時は、新規作成ボタン表示
	if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__add",$_SESSION['authority'])===FALSE)) {
		$html .= "<br>\n";
		$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"add\">\n";
		$html .= "<input type=\"submit\" value=\"サービス新規登録\">\n";
		$html .= "</form>\n";
	}

	// サービス取得SQL作成
	$sql  = " SELECT " .
			"  se.service_num, " .
			"  se.service_name, " .
			"  se.setup_type, " .
			"  se.setup_type_sub, " .
			"  se.display " .
			" FROM " . T_SERVICE . " se " .
			" WHERE se.mk_flg ='0' " .
			" ORDER BY se.list_num ";

	// サービス件数取得
	$service_count = 0;
	if ($result = $cdb->query($sql)) {
		$service_count = $cdb->num_rows($result);
	}

	// データ読み込み
	if ($result = $cdb->query($sql)) {

		// 取得データ件数が０の場合
		if (!$service_count) {
			$html .= "現在、登録サービスは存在しません。";
			return $html;
		}

		// 一覧表示開始
		$html .= "<br>\n";
		$html .= "修正する場合は、修正するサービスの詳細ボタンを押してください。<br>\n";

		$html .= "<br>\n";
		$html .= "<table class=\"service_form\">\n";
		$html .= "<tr class=\"service_form_menu\">\n";

		if (!ereg("practice__view",$authority)
				&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__sort",$_SESSION['authority'])===FALSE))
		) {
			$html .= "<th>↑</th>\n";
			$html .= "<th>↓</th>\n";
		}

		// リストタイトル表示
		$html .= "<th>登録番号</th>\n";
		$html .= "<th>サービス名</th>\n";
		$html .= "<th>設定画面</th>\n";
		$html .= "<th>設定画面サブ</th>\n";
		$html .= "<th>設定コースコード</th>\n";
		$html .= "<th>設定コース</th>\n";
		$html .= "<th>表示・非表示</th>\n";
		$html .= "<th></th>\n";
		$html .= "</tr>\n";

		// 明細表示
		$i = 1;
		while ($list = $cdb->fetch_assoc($result)) {

			$html .= "<tr class=\"service_form_cell\">\n";

			$up_submit = $down_submit = "&nbsp;";
			if ($i != 1) {
				$up_submit = "<input type=\"submit\" value=\"↑\">\n";
			}
			if ($i < $service_count) {
				$down_submit = "<input type=\"submit\" value=\"↓\">\n";
			}

			// 明細表示
			if (!ereg("practice__view",$authority)
				&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__sort",$_SESSION['authority'])===FALSE))
			) {
				$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
				$html .= "<input type=\"hidden\" name=\"action\" value=\"service_up\">\n";
				$html .= "<input type=\"hidden\" name=\"service_num\" value=\"".$list['service_num']."\">\n";
				$html .= "<td>$up_submit</td>\n";
				$html .= "</form>\n";

				$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
				$html .= "<input type=\"hidden\" name=\"action\" value=\"service_down\">\n";
				$html .= "<input type=\"hidden\" name=\"service_num\" value=\"".$list['service_num']."\">\n";
				$html .= "<td>$down_submit</td>\n";
				$html .= "</form>\n";
			}
			$html .= "<td>".$list['service_num']."</td>\n";
			$html .= "<td>".$list['service_name']."</td>\n";
			$html .= "<td>".$L_SETUP_TYPE[$list['setup_type']]."</td>\n";
			$html .= "<td>".$L_SETUP_TYPE_SUB[$list['setup_type_sub']]."</td>\n";

			$sql  = " SELECT " .
					"  scl.course_type, " .
					"  scl.course_num " .
					" FROM " . T_SERVICE_COURSE_LIST . " scl " .
					" WHERE scl.mk_flg ='0' " .
					// "   AND scl.display ='1' " . // del start 2018/10/05 yoshizawa すらら英単語追加 // 非表示のマスタ名が表示されないので表示フラグは見ない
					"   AND scl.service_num ='".$list['service_num']."' " .
					" ORDER BY scl.course_type,scl.course_num ";

			$course_info1 = "";
			$course_info2 = "";
			$course_type_break = "";
			if ($course_result = $cdb->query($sql)) {
				while ($course_list = $cdb->fetch_assoc($course_result)) {
					// コース名取得
					$course_name = "";
					if ($course_list['course_type'] == 1) {
						$course_name = get_practice_course_name($course_list['course_num']);
					} elseif ($course_list['course_type'] == 2) {
						$course_name = get_package_course_name($course_list['course_num']);
					} elseif ($course_list['course_type'] == 3) {
						$course_name = get_test_course_name($course_list['course_num']);
					}
					// add start 2018/10/05 yoshizawa すらら英単語追加
					elseif ($course_list['course_type'] == 4) {
						$course_name = get_test_type_name($course_list['course_num']);
					}
					// add end 2018/10/05 yoshizawa すらら英単語追加

					// コースタイプが異なる場合は改行
					if ($course_type_break && $course_type_break != $course_list['course_type']) {
						$course_info1 .= "<br>";
						$course_info2 .= "<br>";
					}

					// コース番号編集
					if (!$course_info1 || $course_type_break != $course_list['course_type']) {
						$course_info1 .= $L_COURSE_TYPE[$course_list['course_type']]."：".$course_list['course_num'];
					} else {
						$course_info1 .= ",".$course_list['course_num'];
					}

					// コース名編集
					if (!$course_info2 || $course_type_break != $course_list['course_type']) {
						$course_info2 .= $L_COURSE_TYPE[$course_list['course_type']]."：".$course_name;
					} else {
						$course_info2 .= ",".$course_name;
					}

					$course_type_break = $course_list['course_type'];
				}
			}

			$html .= "<td>".$course_info1."</td>\n";
			$html .= "<td>".$course_info2."</td>\n";
			$html .= "<td>".$L_DISPLAY[$list['display']]."</td>\n";

			$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"service_num\" value=\"".$list['service_num']."\">\n";
			$html .= "<td>";

			// 表示権限チェック（権限が有る場合は、ボタン表示）
			if (!ereg("practice__view",$authority)
				&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__view",$_SESSION['authority'])===FALSE))
			) {
				$html .= "<input type=\"submit\" name=\"mode\" value=\"詳細\">\n";
			}

			// 削除権限チェック（権限が有る場合は、ボタン表示）
			if (!ereg("practice__del",$authority)
				&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__del",$_SESSION['authority'])===FALSE))
			) {
				$html .= "<input type=\"submit\" name=\"mode\" value=\"削除\">\n";
			}
			$html .= "</td>\n";
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

	global $L_SETUP_TYPE,$L_SETUP_TYPE_SUB,$L_DISPLAY,$L_ONOFF_TYPE,$L_CHANGE_TYPE;		// update oda 2013/11/15 $L_CHANGE_TYPE追加

	$html .= "<br>\n";
	$html .= "新規登録フォーム<br>\n";

	// エラーメッセージ表示
	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	// 入力画面表示
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";

	// htmlクラス生成
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(SERVICE_FORM);

	//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	$INPUTS['SERVICENUM']   = array('result'=>'plane','value'=>"---");
	$INPUTS['SERVICENAME']  = array('type'=>'text','name'=>'service_name','value'=>$_POST['service_name']);
	$INPUTS['SETUPTYPE']    = array('type'=>'select','name'=>'setup_type','array'=>$L_SETUP_TYPE,'check'=>$_POST['setup_type']);
	$INPUTS['SETUPTYPESUB'] = array('type'=>'select','name'=>'setup_type_sub','array'=>$L_SETUP_TYPE_SUB,'check'=>$_POST['setup_type_sub']);
	$INPUTS['QUESTIONFLG']  = array('type'=>'select','name'=>'question_flg','array'=>$L_ONOFF_TYPE,'check'=>$_POST['question_flg']);
	$INPUTS['STUDYTIMETHRESHOLD']  = array('type'=>'text','name'=>'study_time_threshold','value'=>$_POST['study_time_threshold']);				// add oda 2014/05/01 課題要望一覧No210 閾値追加

	//del start kimura 2018/11/14 漢字学習コンテンツ_書写ドリル対応(コア側改修)
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
	// $INPUTS['SIGNBOARDFLG'] = array('result'=>'plane','value'=>$signboard_value);

	// $INPUTS['SIGNBOARDNAME']  = array('type'=>'text','name'=>'signboard_name','value'=>$_POST['signboard_name']);
	// add end oda 2013/11/15
	//del end   kimura 2018/11/14 漢字学習コンテンツ_書写ドリル対応(コア側改修)

	$INPUTS['REMARKS']      = array('type'=>'textarea','name'=>'remarks','cols'=>'50','rows'=>'5','value'=>$_POST['remarks']);

	// 表示区分ラジオボタン生成
	if ($_POST['display'] == "") {
		$_POST['display'] = "1";
	}
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("display");
	$newform->set_form_id("display");
	$newform->set_form_check($_POST['display']);
	$newform->set_form_value('1');
	$radio1 = $newform->make();

	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("display");
	$newform->set_form_id("undisplay");
	$newform->set_form_check($_POST['display']);
	$newform->set_form_value('2');
	$radio2 = $newform->make();

	$display_value = $radio1 . "<label for=\"display\">".$L_DISPLAY[1]."</label> / " . $radio2 . "<label for=\"undisplay\">".$L_DISPLAY[2]."</label>";
	$INPUTS['DISPLAY'] = array('result'=>'plane','value'=>$display_value);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();

	// 制御ボタン定義
	$html .= "<input type=\"submit\" value=\"追加確認\">\n";
	$html .= "<input type=\"reset\" value=\"クリア\">\n";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"service_list\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}


/**
 * 詳細情報表示
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function viewform($ERROR) {

	global $L_SETUP_TYPE,$L_SETUP_TYPE_SUB,$L_COURSE_TYPE,$L_DISPLAY,$L_ONOFF_TYPE,$L_CHANGE_TYPE;		// update oda 2013/11/15 $L_CHANGE_TYPE追加

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (ACTION && ACTION != "course_add" && ACTION != "course_change") {
		foreach ($_POST as $key => $val) { $$key = $val; }
	} else {
		$sql = "SELECT * FROM " . T_SERVICE .
			" WHERE service_num='".$_POST[service_num]."' AND mk_flg='0' LIMIT 1;";
		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);
		if (!$list) {
			$html .= "<br>既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}
		foreach ($list as $key => $val) {
			$$key = replace_decode($val);
		}
	}

	// 画面表示
	$html = "<br>\n";
	$html .= "詳細画面<br>\n";

	// エラーメッセージ表示
	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	// 入力画面表示
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";
	$html .= "<input type=\"hidden\" name=\"service_num\" value=\"$service_num\">\n";

	// htmlクラス生成
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(SERVICE_FORM);

	//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	if (!$service_num) { $service_num = "---"; }
	$INPUTS['SERVICENUM']   = array('result'=>'plane','value'=>$service_num);
	$INPUTS['SERVICENAME']  = array('type'=>'text','name'=>'service_name','value'=>$service_name);
	$INPUTS['SETUPTYPE']    = array('type'=>'select','name'=>'setup_type','array'=>$L_SETUP_TYPE,'check'=>$setup_type);
	$INPUTS['SETUPTYPESUB'] = array('type'=>'select','name'=>'setup_type_sub','array'=>$L_SETUP_TYPE_SUB,'check'=>$setup_type_sub);
	$INPUTS['QUESTIONFLG']  = array('type'=>'select','name'=>'question_flg','array'=>$L_ONOFF_TYPE,'check'=>$question_flg);
	$INPUTS['STUDYTIMETHRESHOLD']  = array('type'=>'text','name'=>'study_time_threshold','value'=>$study_time_threshold);			// add oda 2014/05/01 課題要望一覧No210 閾値追加

	//del start kimura 2018/11/14 漢字学習コンテンツ_書写ドリル対応(コア側改修)
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
	// $INPUTS['SIGNBOARDFLG'] = array('result'=>'plane','value'=>$signboard_value);

	// $INPUTS['SIGNBOARDNAME']  = array('type'=>'text','name'=>'signboard_name','value'=>$signboard_name);
	// add end oda 2013/11/15
	//del end   kimura 2018/11/14 漢字学習コンテンツ_書写ドリル対応(コア側改修)

	$INPUTS['REMARKS']      = array('type'=>'textarea','name'=>'remarks','cols'=>'50','rows'=>'5','value'=>$remarks);

	// 表示区分ラジオボタン生成
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("display");
	$newform->set_form_id("display");
	$newform->set_form_check($display);
	$newform->set_form_value('1');
	$radio1 = $newform->make();

	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("display");
	$newform->set_form_id("undisplay");
	$newform->set_form_check($display);
	$newform->set_form_value('2');
	$radio2 = $newform->make();

	$display_value = $radio1 . "<label for=\"display\">".$L_DISPLAY[1]."</label> / " . $radio2 . "<label for=\"updisplay\">".$L_DISPLAY[2]."</label>";
	$INPUTS['DISPLAY'] = array('result'=>'plane','value'=>$display_value);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();

	// 制御ボタン定義
	$html .= "<input type=\"submit\" value=\"変更確認\">";
	$html .= "<input type=\"reset\" value=\"クリア\">";
	$html .= "</form>\n";
	$html .= "<br>\n";

	$html .= "<hr>\n";
	$html .= "サービス関連コース<br>\n";

	if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__add",$_SESSION['authority'])===FALSE)) {
		$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"course_form\">\n";
		$html .= "<input type=\"hidden\" name=\"service_num\" value=\"".$_POST['service_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"service_course_addform\">\n";
		$html .= "<input type=\"submit\" value=\"コース新規登録\">\n";
		$html .= "</form><br>\n";
	}

	$sql = "SELECT scl.* FROM " . T_SERVICE_COURSE_LIST . " scl " .
			" WHERE scl.service_num='".$_POST['service_num']."'".
			"   AND scl.mk_flg = '0'";


	if ($result = $cdb->query($sql)) {
		$service_course_count = $cdb->num_rows($result);
		while ($list = $cdb->fetch_assoc($result)) {
			$L_SERVICE_COURSE[$list['service_course_list_num']] = $list;
		}
	}

	if ($service_course_count == 0) {
		$html .= "現在、コース情報の登録はありません。<br>\n";
	} else {
		$html .= "登録コース<br>\n";
		$html .= "<table class=\"service_form\">\n";
		$html .= "<tr class=\"service_form_menu\">\n";
		$html .= "<th>管理番号</th>\n";
		$html .= "<th>コースタイプ</th>\n";
		// update start 2018/10/09 yoshizawa すらら英単語追加
		// $html .= "<th>コース番号</th>\n";
		if($service_num == '17'){
			$html .= "<th>すらら英単語種類管理番号</th>\n";
		} else {
			$html .= "<th>コース番号</th>\n";
		}
		// update end 2018/10/09 yoshizawa すらら英単語追加
		$html .= "<th>コース名</th>\n";
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
		foreach ($L_SERVICE_COURSE as $key => $service_line) {
			$html .= "<tr class=\"service_form_cell\">\n";
			$html .= "<td>".$service_line['service_course_list_num']."</td>\n";
			$html .= "<td>".$L_COURSE_TYPE[$service_line['course_type']]."</td>\n";
			$html .= "<td>".$service_line['course_num']."</td>\n";

			$course_name = "";
			if ($service_line['course_type'] == 1) {
				$course_name = get_practice_course_name($service_line['course_num']);
			} elseif ($service_line['course_type'] == 2) {
				$course_name = get_package_course_name($service_line['course_num']);
			} elseif ($service_line['course_type'] == 3) {
				$course_name = get_test_course_name($service_line['course_num']);
			}
			// add start 2018/10/05 yoshizawa すらら英単語追加
			elseif ($service_line['course_type'] == 4) {
				$course_name = get_test_type_name($service_line['course_num']);
			}
			// add end 2018/10/05 yoshizawa すらら英単語追加

			$html .= "<td>".$course_name."</td>\n";
			$html .= "<td>".$L_DISPLAY[$service_line['display']]."</td>\n";

			if (!ereg("practice__view",$authority)
					&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__view",$_SESSION['authority'])===FALSE))
			) {
				$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
				$html .= "<input type=\"hidden\" name=\"mode\" value=\"course_form\">\n";
				$html .= "<input type=\"hidden\" name=\"service_course_list_num\" value=\"".$service_line['service_course_list_num']."\">\n";
				$html .= "<input type=\"hidden\" name=\"service_num\" value=\"".$service_line['service_num']."\">\n";
				$html .= "<input type=\"hidden\" name=\"course_type\" value=\"".$service_line['course_type']."\">\n";
				$html .= "<input type=\"hidden\" name=\"course_contract_type\" value=\"".$service_line['course_contract_type']."\">\n"; //add kimura 2018/11/15 すらら英単語
				$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$service_line['course_num']."\">\n";
				$html .= "<input type=\"hidden\" name=\"srvc_cd\" value=\"".$service_line['srvc_cd']."\">\n"; //add kimura 2018/11/15 すらら英単語
				$html .= "<input type=\"hidden\" name=\"sysi_crs_cd\" value=\"".$service_line['sysi_crs_cd']."\">\n"; //add kimura 2018/11/15 すらら英単語
				$html .= "<input type=\"hidden\" name=\"display\" value=\"".$service_line['display']."\">\n";
				$html .= "<td><input type=\"submit\" value=\"詳細\"></td>\n";
				$html .= "</form>\n";
			}
			if (!ereg("practice__del",$authority)
					&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__del",$_SESSION['authority'])===FALSE))
			) {
				$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
				$html .= "<input type=\"hidden\" name=\"mode\" value=\"course_delete\">\n";
				$html .= "<input type=\"hidden\" name=\"service_course_list_num\" value=\"".$service_line['service_course_list_num']."\">\n";
				$html .= "<input type=\"hidden\" name=\"service_num\" value=\"".$service_line['service_num']."\">\n";
				$html .= "<input type=\"hidden\" name=\"course_type\" value=\"".$service_line['course_type']."\">\n";
				$html .= "<input type=\"hidden\" name=\"course_contract_type\" value=\"".$service_line['course_contract_type']."\">\n"; //add kimura 2018/11/15 すらら英単語
				$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$service_line['course_num']."\">\n";
				$html .= "<input type=\"hidden\" name=\"srvc_cd\" value=\"".$service_line['srvc_cd']."\">\n"; //add kimura 2018/11/15 すらら英単語
				$html .= "<input type=\"hidden\" name=\"sysi_crs_cd\" value=\"".$service_line['sysi_crs_cd']."\">\n"; //add kimura 2018/11/15 すらら英単語
				$html .= "<input type=\"hidden\" name=\"display\" value=\"".$service_line['display']."\">\n";
				$html .= "<td><input type=\"submit\" value=\"削除\"></td>\n";
				$html .= "</form>\n";
			}
			$html .= "</tr>\n";
			$i++;
		}
		$html .= "</table><br>\n";
	}

	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"service_list\">\n";
	$html .= "<input type=\"submit\" value=\"サービス一覧に戻る\">\n";
	$html .= "</form>\n";

	return $html;
}


/**
 * 入力項目チェック
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function check() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// 未入力チェック
	if (!$_POST['service_name']) {
		$ERROR[] = "サービス名が未入力です。";
	}
	if (!$_POST['setup_type']) {
		$ERROR[] = "設定画面が未選択です。";
	}
	if (!$_POST['setup_type_sub']) {
		if ($_POST['setup_type'] == "1") {
			$ERROR[] = "設定画面サブが未選択です。";
		}
	}
	if (!$_POST['question_flg']) {
		if ($_POST['setup_type'] == "1" || $_POST['setup_type'] == "3") {
			$ERROR[] = "質問表示区分が未選択です。";
		}
	}
	// add start oda 2014/05/01 課題要望一覧No210 閾値追加作業
	if ($_POST['study_time_threshold'] == "") {
		//$ERROR[] = "平均値算出閾値が未入力です。";
		$_POST['study_time_threshold'] = "0";		// add oda 2014/06/25 未設定の場合は0とする（エラーにしない）
	}
	// add end oda 2014/05/01
	if ($_POST['signboard_flg'] == "1" && !$_POST['signboard_name']) {			// add oda 2013/11/15
		$ERROR[] = "生徒画面の看板表示内容が未入力です。";
	}
	if (!$_POST['display']) {
		$ERROR[] = "表示区分が未入力です。";
	}

	// 重複チェック
	if (MODE == "add") {
		$sql  = "SELECT * FROM " .T_SERVICE.
				" WHERE mk_flg = '0' AND service_num = '".$_POST['service_num']."'";
	} else {
		$sql  = "SELECT * FROM " .T_SERVICE.
				" WHERE mk_flg = '0' AND service_num != '".$_POST['service_num']."' AND service_name = '".$_POST['service_name']."'";
	}
	// SQL実行
	if ($result = $cdb->query($sql)) {
		$count = $cdb->num_rows($result);
	}
	if ($count > 0) { $ERROR[] = "入力されたサービス名は既に登録されております。"; }

	return $ERROR;
}


/**
 * 入力確認画面表示
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function check_html() {

	global $L_SETUP_TYPE,$L_SETUP_TYPE_SUB,$L_DISPLAY,$L_ONOFF_TYPE,$L_CHANGE_TYPE;		// update oda 2013/11/15 $L_CHANGE_TYPE追加

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$service_num = "";
	$service_name = "";
	$setup_type = "";
	$setup_type_sub = "";
	$question_flg = "";
	$study_time_threshold = "";
	$signboard_flg = "";
	$signboard_name = "";
	$display = "";
	$remarks = "";

	// アクション情報をhidden項目に設定
	if ($_POST) {
		foreach ($_POST as $key => $val) {
			if ($key == "action") {
				if (MODE == "add") {
					$val = "add";
				} elseif (MODE == "詳細") {
					$val = "change";
				}
			}
			$HIDDEN .= "<input type=\"hidden\" name=\"$key\" value=\"$val\">\n";
		}
	}

	// 他の人が削除したかチェック
	if (ACTION) {
		foreach ($_POST as $key => $val) { $$key = $val; }
	} else {
		$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"change\">\n";
		$sql = "SELECT * FROM ".T_SERVICE.
			" WHERE service_num='".$_POST['service_num']."' AND mk_flg='0' LIMIT 1;";
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

	// ボタン表示文言判定
	if (MODE != "削除") { $button = "登録"; } else { $button = "削除"; }

	// 入力確認画面表示
	$html = "<br>\n";
	$html .= "確認画面：以下の内容で".$button."してもよろしければ".$button."ボタンをクリックしてください。<br>\n";

	// htmlクラス生成
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(SERVICE_FORM);

	//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	if (!$service_num) { $service_num = "---"; }
	$INPUTS['SERVICENUM']   = array('result'=>'plane','value'=>$service_num);
	$INPUTS['SERVICENAME']  = array('result'=>'plane','value'=>$service_name);
	$INPUTS['SETUPTYPE']    = array('result'=>'plane','value'=>$L_SETUP_TYPE[$setup_type]);
	$INPUTS['SETUPTYPESUB'] = array('result'=>'plane','value'=>$L_SETUP_TYPE_SUB[$setup_type_sub]);
	$INPUTS['QUESTIONFLG']  = array('result'=>'plane','value'=>$L_ONOFF_TYPE[$question_flg]);
	$INPUTS['STUDYTIMETHRESHOLD']  = array('result'=>'plane','value'=>$study_time_threshold);				// add oda 2014/05/01 課題要望一覧No210 閾値追加
	$INPUTS['SIGNBOARDFLG']  = array('result'=>'plane','value'=>$L_CHANGE_TYPE[$signboard_flg]);			// add oda 2013/11/15
	$INPUTS['SIGNBOARDNAME'] = array('result'=>'plane','value'=>$signboard_name);							// add oda 2013/11/15
	$INPUTS['DISPLAY']      = array('result'=>'plane','value'=>$L_DISPLAY[$display]);
	$INPUTS['REMARKS']  = array('result'=>'plane','value'=>$remarks);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();

	// 制御ボタン定義
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" style=\"float:left\">\n";
	$html .= $HIDDEN;

	$html .= "<input type=\"submit\" value=\"$button\">\n";
	$html .= "</form>";
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";

	if (ACTION) {
		$HIDDEN2 = explode("\n",$HIDDEN);
		foreach ($HIDDEN2 as $key => $val) {
			if (ereg("name=\"action\"",$val)) {
				$HIDDEN2[$key] = "<input type=\"hidden\" name=\"action\" value=\"back\">";
				break;
			}
		}
		$HIDDEN3 = implode("\n",$HIDDEN2);
		$html .= $HIDDEN3;
	} else {
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"service_list\">\n";
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

	// 登録項目設定
	foreach ($_POST as $key => $val) {
		if ($key == "action") { continue; }
		$INSERT_DATA[$key] = "$val";
	}
	$INSERT_DATA['ins_syr_id'] = "add";
	$INSERT_DATA['ins_date']   = "now()";
	$INSERT_DATA['ins_tts_id'] = $_SESSION['myid']['id'];
	$INSERT_DATA['upd_syr_id'] = "add";
	$INSERT_DATA['upd_date']   = "now()";
	$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];

	// ＤＢ追加処理
	$ERROR = $cdb->insert(T_SERVICE,$INSERT_DATA);

	if (!$ERROR) {
		$service_num = $cdb->insert_id();
		$INSERT_DATA['list_num'] = $service_num;
		$where = " WHERE service_num='$service_num' LIMIT 1;";
		$ERROR = $cdb->update(T_SERVICE,$INSERT_DATA,$where);
	}

	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}

/**
 * DB更新・削除 処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function change() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// 更新処理
	if (MODE == "詳細") {
		$INSERT_DATA['service_name']   = $_POST['service_name'];
		$INSERT_DATA['setup_type']     = $_POST['setup_type'];
		$INSERT_DATA['setup_type_sub'] = $_POST['setup_type_sub'];
		$INSERT_DATA['question_flg']   = $_POST['question_flg'];
		$INSERT_DATA['study_time_threshold']   = $_POST['study_time_threshold'];		// add oda 2014/05/01 課題要望一覧No210 閾値追加
		$INSERT_DATA['remarks']        = $_POST['remarks'];
		$INSERT_DATA['signboard_flg']  = $_POST['signboard_flg'];		// add oda 2013/11/15
		$INSERT_DATA['signboard_name'] = $_POST['signboard_name'];		// add oda 2013/11/15
		$INSERT_DATA['display']        = $_POST['display'];
		$INSERT_DATA['upd_syr_id']     = "update";
		$INSERT_DATA['upd_date']       = "now()";
		$INSERT_DATA['upd_tts_id']     = $_SESSION['myid']['id'];

		$where = " WHERE service_num='".$_POST['service_num']."' LIMIT 1;";
		$ERROR = $cdb->update(T_SERVICE,$INSERT_DATA,$where);

	// 削除処理
	} elseif (MODE == "削除") {
		$INSERT_DATA['display']   = 2;
		$INSERT_DATA['mk_flg']    = 1;
		$INSERT_DATA['mk_tts_id'] = $_SESSION['myid']['id'];
		$INSERT_DATA['mk_date']   = "now()";
		$INSERT_DATA['upd_syr_id']   = "del";									// 2012/11/05 add oda
		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];					// 2012/11/05 add oda
		$INSERT_DATA['upd_date']   = "now()";									// 2012/11/05 add oda
		$where = " WHERE service_num='".$_POST['service_num']."' LIMIT 1;";
		$ERROR = $cdb->update(T_SERVICE,$INSERT_DATA,$where);

		// サービスコースリストテーブル削除
		$INSERT_DATA['display']   = 2;
		$INSERT_DATA['mk_flg']    = 1;
		$INSERT_DATA['mk_tts_id'] = $_SESSION['myid']['id'];
		$INSERT_DATA['mk_date']   = "now()";
		$INSERT_DATA['upd_syr_id']   = "del";									// 2012/11/05 add oda
		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];					// 2012/11/05 add oda
		$INSERT_DATA['upd_date']   = "now()";									// 2012/11/05 add oda
		$where = " WHERE service_num='".$_POST['service_num']."';";
		$ERROR = $cdb->update(T_SERVICE_COURSE_LIST,$INSERT_DATA,$where);
	}

	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}

/**
 * SERVICEを上がる機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function up() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	$sql  = "SELECT * FROM ".T_SERVICE.
			" WHERE service_num = '".$_POST['service_num']."' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$m_service_num = $list['service_num'];
		$m_list_num    = $list['list_num'];
	}
	if (!$m_service_num || !$m_list_num) {
		$ERROR[] = "移動するサービス情報が取得できません。";
	}

	if (!$ERROR) {
		$sql  = "SELECT * FROM ".T_SERVICE.
				" WHERE mk_flg = '0' AND list_num < '".$m_list_num."'" .
				" ORDER BY list_num DESC LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$c_service_num = $list['service_num'];
			$c_list_num    = $list['list_num'];
		}
	}
	if (!$c_service_num || !$c_list_num) {
		$ERROR[] = "移動されるサービス情報が取得できません。";
	}

	if (!$ERROR) {
		$INSERT_DATA = array();
		$INSERT_DATA['list_num']   = $c_list_num;
		$INSERT_DATA['upd_syr_id'] = "up";
		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date']   = "now()";
		$where = " WHERE service_num = '".$m_service_num."' LIMIT 1;";

		$ERROR = $cdb->update(T_SERVICE,$INSERT_DATA,$where);
	}

	if (!$ERROR) {
		$INSERT_DATA = array();
		$INSERT_DATA['list_num']   = $m_list_num;
		$INSERT_DATA['upd_syr_id'] = "up";
		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date']   = "now()";
		$where = " WHERE service_num = '".$c_service_num."' LIMIT 1;";

		$ERROR = $cdb->update(T_SERVICE,$INSERT_DATA,$where);
	}

	return $ERROR;
}

/**
 * SERVICEを下がる機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function down() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	$sql  = "SELECT * FROM ".T_SERVICE.
			" WHERE service_num='".$_POST['service_num']."' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$m_service_num = $list['service_num'];
		$m_list_num    = $list['list_num'];
	}
	if (!$m_service_num || !$m_list_num) {
		$ERROR[] = "移動するサービス情報が取得できません。";
	}

	if (!$ERROR) {
		$sql  = "SELECT * FROM ".T_SERVICE.
				" WHERE mk_flg = '0' AND list_num > '".$m_list_num."'" .
				" ORDER BY list_num LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$c_service_num = $list['service_num'];
			$c_list_num    = $list['list_num'];
		}
	}
	if (!$c_service_num || !$c_list_num) {
		$ERROR[] = "移動されるサービス情報が取得できません。";
	}

	if (!$ERROR) {
		$INSERT_DATA = array();
		$INSERT_DATA['list_num']   = $c_list_num;
		$INSERT_DATA['upd_syr_id'] = "down";
		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date']   = "now()";
		$where = " WHERE service_num = '".$m_service_num."' LIMIT 1;";

		$ERROR = $cdb->update(T_SERVICE,$INSERT_DATA,$where);
	}

	if (!$ERROR) {
		$INSERT_DATA = array();
		$INSERT_DATA['list_num']   = $m_list_num;
		$INSERT_DATA['upd_syr_id'] = "down";
		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date']   = "now()";
		$where = " WHERE service_num = '".$c_service_num."' LIMIT 1;";

		$ERROR = $cdb->update(T_SERVICE,$INSERT_DATA,$where);
	}

	return $ERROR;
}


/**
 * コース登録フォーム表示
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function course_form($ERROR) {

	global $L_COURSE_TYPE,$L_DISPLAY;
	global $L_COURSE_CONTRACT_TYPE; //add kimura 2018/11/14 すらら英単語 //基本orオプションサービス

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$html .= "<br>\n";

	if ($_POST['service_course_list_num']) {
		$html .= "コース変更登録フォーム<br>\n";
	} else {
		$html .= "コース新規登録フォーム<br>\n";
	}

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	if (ACTION) {
		foreach ($_POST as $key => $val) {
			$$key = $val;
		}
	} else {
		if ($_POST['service_course_list_num']) {
			$sql = "SELECT * FROM " . T_SERVICE_COURSE_LIST .
					" WHERE service_course_list_num = '".$_POST['service_course_list_num']."'".
					"   AND mk_flg = '0' LIMIT 1;";
			$result = $cdb->query($sql);
			$list = $cdb->fetch_assoc($result);
			if (!$list) {
				$html .= "既に削除されているか、不正な情報が混ざっています。$sql";
				return $html;
			}
			foreach ($list as $key => $val) {
				$$key = replace_decode($val);
			}
		}
	}

	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"course_form\">\n";
	$html .= "<input type=\"hidden\" name=\"service_num\" value=\"".$service_num."\">\n";
	$html .= "<input type=\"hidden\" name=\"service_course_list_num\" value=\"".$service_course_list_num."\">\n";

	if (!$service_course_list_num) {
		$new_num = "---";
	} else {
		$new_num = $service_course_list_num;
	}

	$INPUTS['SERVICECOURSELISTNUM'] = array('result'=>'plane','value'=>$new_num);

	$course_type_select = "<select name=\"course_type\" onChange=\"submit();\">\n";
	if ($L_COURSE_TYPE) {
		foreach ($L_COURSE_TYPE AS $key => $val) {
			if ($course_type == $key) { $selected = "selected"; }
			else { $selected = ""; }
			$course_type_select .= "<option value=\"{$key}\" $selected>{$val}</option>\n";
		}
	}
	$block_type_select .= "</select>\n";
	$INPUTS['COURSETYPE'] = array('result'=>'plane','value'=>$course_type_select);

	$COURSE_LIST = array();

	// プラクティスコース情報
	if ($course_type == 1) {
		$sql = "SELECT * FROM " . T_COURSE .
				" WHERE display = '1' AND state = '0'".
				" ORDER BY list_num;";
		$course_result = $cdb->query($sql);
		while ($course_list = $cdb->fetch_assoc($course_result)) {
			$COURSE_LIST[$course_list['course_num']] = $course_list['course_name'];
		}
	// 速習コース情報
	} elseif ($course_type == 2) {
		$sql = "SELECT * FROM " . T_PACKAGE_COURSE .
				" WHERE display = '1' AND mk_flg = '0'".
				" ORDER BY list_num;";
		$course_result = $cdb->query($sql);
		while ($course_list = $cdb->fetch_assoc($course_result)) {
			$COURSE_LIST[$course_list['pk_course_num']] = $course_list['pk_course_name'];
		}
	// テスト情報
	} elseif ($course_type == 3) {
		$COURSE_LIST[1] = "小テスト";
		$COURSE_LIST[2] = "定期テスト";
		$COURSE_LIST[4] = "学力診断テスト";
	}
	// add start 2018/10/05 yoshizawa すらら英単語追加
	// すらら英単語テスト情報
	elseif ($course_type == 4) {
		$sql = "SELECT * FROM  ".T_MS_TEST_TYPE." WHERE mk_flg = '0' ORDER BY list_num;";
		$course_result = $cdb->query($sql);
		while ($course_list = $cdb->fetch_assoc($course_result)) {
			$COURSE_LIST[$course_list['test_type_num']] = $course_list['test_type_name'];
		}
	}
	// add end 2018/10/05 yoshizawa すらら英単語追加

	// add start 2018/10/05 yoshizawa すらら英単語追加
	$course_num_message = "";
	if($course_type == 4){ $course_num_message = "すらら英単語テストの[コース情報]は「テスト用プラクティス管理」：「すらら英単語種類管理」から選択できます。<br>\n"; }
	$INPUTS['COURSENUMMESSAGE'] = array('result'=>'plane','value'=>$course_num_message);
	// add end 2018/10/05 yoshizawa すらら英単語追加
	$INPUTS['COURSENUM'] = array('type'=>'select','name'=>'course_num','array'=>$COURSE_LIST,'check'=>$course_num);
	//add start kimura 2018/11/14 すらら英単語
	//すらら英単語 or すらら英単語テストのコースに対してのみこれを設定可能にする 2018/11/15
	if(isset($_POST['service_num']) && ($_POST['service_num'] == 16 || $_POST['service_num'] == 17)){
		$contract_form_css = "none_display"; //サービスコードと詳細コースコードの入力フォームは出さない
		if(isset($_POST['course_contract_type']) && $_POST['course_contract_type'] == 1){ //オプション契約ならコード入力フォームを出す
			$contract_form_css = "";
		}
		//基本/オプション---------------
		$contract_form_html.= "<tr>";
		$contract_form_html.= "<td class=\"service_form_menu\">コース契約タイプ</td>";
		$contract_form_html.= "<td class=\"service_form_cell\">";
		$contract_form_html.= "<input onclick=\"hide_items('course_contract_option')\" type=\"radio\" name=\"course_contract_type\" value=\"0\" id=\"course_contract_type_0\" ".($_POST['course_contract_type'] == 0 ? "checked" : "").">";
		$contract_form_html.= "<label for=\"course_contract_type_0\">".$L_COURSE_CONTRACT_TYPE[0]."</label>";
		$contract_form_html.= " / ";
		$contract_form_html.= "<input onclick=\"show_items('course_contract_option');\" type=\"radio\" name=\"course_contract_type\" value=\"1\" id=\"course_contract_type_1\" ".($_POST['course_contract_type'] == 1 ? "checked" : "").">";
		$contract_form_html.= "<label for=\"course_contract_type_1\">".$L_COURSE_CONTRACT_TYPE[1]."</label>";
		$contract_form_html.= "</td>";
		$contract_form_html.= "</tr>";
		//サービスコード---------------
		$contract_form_html.= "<tr class=\"course_contract_option {$contract_form_css}\">";
		$contract_form_html.= "<td class=\"service_form_menu\">サービスコード</td>";
		$contract_form_html.= "<td class=\"service_form_cell\"><input type=\"text\" name=\"srvc_cd\" value=\"{$_POST['srvc_cd']}\" maxlength=\"10\"></td>";
		$contract_form_html.= "</tr>";
		//詳細コースコード---------------
		$contract_form_html.= "<tr class=\"course_contract_option {$contract_form_css}\">";
		$contract_form_html.= "<td class=\"service_form_menu\">詳細コースコード</td>";
		$contract_form_html.= "<td class=\"service_form_cell\"><input type=\"text\" name=\"sysi_crs_cd\" value=\"{$_POST['sysi_crs_cd']}\" maxlength=\"10\"></td>";
		$contract_form_html.= "</tr>";
		$INPUTS['COURSECONTRACTFORM'] = array('result'=>'plane', 'value'=>$contract_form_html);
	}
	//add end   kimura 2018/11/14 すらら英単語

	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("display");
	$newform->set_form_id("display");
	$newform->set_form_check($display);
	$newform->set_form_value('1');
	$indisplay = $newform->make();

	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("display");
	$newform->set_form_id("undisplay");
	$newform->set_form_check($display);
	$newform->set_form_value('2');
	$undisplay = $newform->make();
	$display = $indisplay . "<label for=\"display\">{$L_DISPLAY[1]}</label> / " . $undisplay . "<label for=\"undisplay\">{$L_DISPLAY[2]}</label>";
	$INPUTS['DISPLAY'] = array('result'=>'plane','value'=>$display);

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(SERVICE_COURSE_FORM);
	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" name=\"action\" value=\"確認\">\n";
	$html .= "<input type=\"reset\" value=\"クリア\">\n";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"詳細\">\n";
	$html .= "<input type=\"hidden\" name=\"service_num\" value=\"".$_POST['service_num']."\">\n";
	$html .= "<input type=\"submit\" value=\"コース一覧へ戻る\">\n";
	$html .= "</form>\n";
	return $html;

}


/**
 * コース入力項目チェック
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function course_check() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// 未入力チェック
	if (!$_POST['course_type']) {
		$ERROR[] = "コースタイプが未入力です。";
	} elseif (!$_POST['course_num']) {
		$ERROR[] = "コース情報が未選択です。";
	} elseif (!$_POST['display']) {
		$ERROR[] = "表示区分が未入力です。";
	//add start kimura 2018/11/15 すらら英単語
	} elseif ($_POST['course_contract_type'] == 1){ //契約:オプションを選んだとき
		if(!$_POST['srvc_cd'] || !$_POST['sysi_crs_cd']){ //サービスコードと詳細コースコードは入力必須とする。
			$ERROR[] = "コース契約タイプに「オプション」を指定する際は、必ずサービスコードと詳細コースコードを入力してください。";
		}
		//入力チェック
		if(!preg_match("/^.{1,10}$/u", $_POST['srvc_cd'])){
			$ERROR[] = "サービスコードは10文字以内で入力してください。";
		}
		if(!preg_match("/^.{1,10}$/u", $_POST['sysi_crs_cd'])){
			$ERROR[] = "詳細コースコードは10文字以内で入力してください。";
		}
	//add end   kimura 2018/11/15 すらら英単語
		// 重複チェック
	} else {
		$sql  = "SELECT * FROM " .T_SERVICE_COURSE_LIST.
				" WHERE mk_flg = '0' " .
				"   AND service_num = '".$_POST['service_num']."'" .
				"   AND course_type = '".$_POST['course_type']."'" .
				"   AND course_num  = '".$_POST['course_num']."'";
		if ($_POST['service_course_list_num']) {
			$sql  .= "   AND service_course_list_num  != '".$_POST['service_course_list_num']."'";
		}
		// SQL実行
		if ($result = $cdb->query($sql)) {
			$count = $cdb->num_rows($result);
		}
		if ($count > 0) {
			$ERROR[] = "入力されたコースは既に登録されております。";
		}
	}

	return $ERROR;
}


/**
 * コース確認フォーム表示
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function course_check_html() {

	global $L_COURSE_TYPE,$L_DISPLAY;
	global $L_COURSE_CONTRACT_TYPE; //add kimura 2018/11/15 すらら英単語

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if ($_POST) {
		foreach ($_POST as $key => $val) {
			if ($key == "action") {
				if (ACTION == "確認" && !$_POST['service_course_list_num']) {
					$val = "course_add";
				} else {
					$val = "course_change";
				}
			}
			$val = mb_convert_kana($val,"asKV","UTF-8");
			$HIDDEN .= "<input type=\"hidden\" name=\"$key\" value=\"$val\">\n";
		}
	}

	if (ACTION) {
		foreach ($_POST as $key => $val) {
			$val = mb_convert_kana($val,"asKV","UTF-8");
			$$key = $val;
		}
	} else {
		$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"course_change\">\n";
		$sql = "SELECT * FROM " . T_SERVICE_COURSE_LIST .
				" WHERE service_course_list_num = '".$_POST['service_course_list_num']."' AND mk_flg = '0' LIMIT 1;";
		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);
		if (!$list) {
			$html .= "<br>既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}
		foreach ($list as $key => $val) {
			$$key = replace_decode($val);
		}
	}

	if (MODE != "course_delete") {
		if ($service_course_list_num) {
			$button = "更新";
		} else {
			$button = "登録";
		}
	} else {
		$button = "削除";
	}
	$html .= "<br>確認画面：以下の内容で{$button}してもよろしければ{$button}ボタンをクリックしてください。<br>\n";

	//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	if (!$service_course_list_num) {
		$service_course_list_num = "---";
	}
	$INPUTS['SERVICECOURSELISTNUM'] = array('result'=>'plane','value'=>$service_course_list_num);

	$INPUTS['COURSETYPE'] = array('result'=>'plane','value'=>$L_COURSE_TYPE[$_POST['course_type']]);

	$course_name = "";

	// コース名取得
	if ($course_type == 1) {
		$course_name = get_practice_course_name($course_num);
	} elseif ($course_type == 2) {
		$course_name = get_package_course_name($course_num);
	} elseif ($course_type == 3) {
		$course_name = get_test_course_name($course_num);
	}
	// add start 2018/10/05 yoshizawa すらら英単語追加
	elseif ($course_type == 4) {
		$course_name = get_test_type_name($course_num);
	}
	// add end 2018/10/05 yoshizawa すらら英単語追加

	$INPUTS['COURSENUM'] = array('result'=>'plane','value'=>$course_name);

	$INPUTS['DISPLAY'] = array('result'=>'plane','value'=>$L_DISPLAY[$display]);
	//add start kimura 2018/11/15 すらら英単語
	//$INPUTS['COURSECONTRACTTYPE'] = array('result'=>'plane', 'value'=>$L_COURSE_CONTRACT_TYPE[$_POST['course_contract_type']]); //コース契約タイプ
	//$INPUTS['SRVCCD'] = array('result'=>'plane', 'value'=>$_POST['srvc_cd']); //サービスコード
	//$INPUTS['SYSICRSCD'] = array('result'=>'plane', 'value'=>$_POST['sysi_crs_cd']); //詳細コースコード
	//$INPUTS['HIDDEN'] = array('result'=>'plane', 'value'=>'none_display');
	////オプション選択があればサービスコードと詳細コースコードの入力欄を表示する
	//if(isset($_POST['course_contract_type']) && $_POST['course_contract_type'] == 1){
	//	$INPUTS['HIDDEN'] = array('result'=>'plane', 'value'=>' ');
	//}

	//すらら英単語 or すらら英単語テストのコースに対してのみこれを設定可能にする 2018/11/15
	if(isset($_POST['service_num']) && ($_POST['service_num'] == 16 || $_POST['service_num'] == 17)){
		$contract_form_css = "";
		if(isset($_POST['course_contract_type']) && $_POST['course_contract_type'] == 0){ //基本契約ならコード入力フォームは出さない
			$contract_form_css = "none_display";
		}
		//基本/オプション---------------
		$contract_form_html.= "<tr>";
		$contract_form_html.= "<td class=\"service_form_menu\">コース契約タイプ</td>";
		$contract_form_html.= "<td class=\"service_form_cell\">{$L_COURSE_CONTRACT_TYPE[$_POST['course_contract_type']]}</td>";
		$contract_form_html.= "</tr>";
		//サービスコード---------------
		$contract_form_html.= "<tr class=\"course_contract_option {$contract_form_css}\">";
		$contract_form_html.= "<td class=\"service_form_menu\">サービスコード</td>";
		$contract_form_html.= "<td class=\"service_form_cell\">{$_POST['srvc_cd']}</td>";
		$contract_form_html.= "</tr>";
		//詳細コースコード---------------
		$contract_form_html.= "<tr class=\"course_contract_option {$contract_form_css}\">";
		$contract_form_html.= "<td class=\"service_form_menu\">詳細コースコード</td>";
		$contract_form_html.= "<td class=\"service_form_cell\">{$_POST['sysi_crs_cd']}</td>";
		$contract_form_html.= "</tr>";
		$INPUTS['COURSECONTRACTFORM'] = array('result'=>'plane', 'value'=>$contract_form_html);
	}
	//add end   kimura 2018/11/15 すらら英単語

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(SERVICE_COURSE_FORM);
	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();

	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" style=\"float:left\">\n";
	$html .= $HIDDEN;
	$html .= "<input type=\"submit\" value=\"$button\">\n";
	$html .= "</form>";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";

	$HIDDEN2 = explode("\n",$HIDDEN);
	foreach ($HIDDEN2 as $key => $val) {
		if (ereg("name=\"mode\"",$val)) {
			$HIDDEN2[$key] = "<input type=\"hidden\" name=\"mode\" value=\"詳細\">";
			break;
		}
		if (ereg("name=\"action\"",$val)) {
			$HIDDEN2[$key] = "<input type=\"hidden\" name=\"action\" value=\"back\">";
			break;
		}
	}
	$HIDDEN2 = implode("\n",$HIDDEN2);

	$html .= $HIDDEN2;

	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}


/**
 * コース登録
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function course_add() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	foreach ($_POST as $key => $val) {
		if ($key == "action") { continue; }
		$INSERT_DATA[$key] = "$val";
	}

	$INSERT_DATA['ins_syr_id'] = "add";
	$INSERT_DATA['ins_date']   = "now()";
	$INSERT_DATA['ins_tts_id'] = $_SESSION['myid']['id'];
	$INSERT_DATA['upd_syr_id'] = "add";
	$INSERT_DATA['upd_date']   = "now()";
	$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];

	$ERROR = $cdb->insert(T_SERVICE_COURSE_LIST,$INSERT_DATA);

	if (!$ERROR) {
		$_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>";
	}

	return $ERROR;
}


/**
 * コース変更
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function course_change() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	if (MODE == "course_form") {

		foreach ($_POST as $key => $val) {
			if ($key == "action") { continue; }
			$INSERT_DATA[$key] = "$val";
		}

		$INSERT_DATA['upd_syr_id'] = "update";
		$INSERT_DATA['upd_date']   = "now()";
		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
		//add start kimura 2018/11/15 すらら英単語 //基本コースに更新したらサービスコードと詳細コースコードの情報は消去する。
		if($INSERT_DATA['course_contract_type'] == 0){
			$INSERT_DATA['srvc_cd'] = "";
			$INSERT_DATA['sysi_crs_cd'] = "";
		}
		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
		//add end   kimura 2018/11/15 すらら英単語

		$where  = " WHERE service_course_list_num = '".$_POST['service_course_list_num']."'";
		$where .= " LIMIT 1;";

		$ERROR = $cdb->update(T_SERVICE_COURSE_LIST,$INSERT_DATA,$where);

	} elseif (MODE == "course_delete") {

		$INSERT_DATA['display']   = 2;
		$INSERT_DATA['mk_flg']    = 1;
		$INSERT_DATA['mk_tts_id'] = $_SESSION['myid']['id'];
		$INSERT_DATA['mk_date']   = "now()";
		$INSERT_DATA['upd_syr_id'] = "del";										// 2012/11/05 add oda
		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];					// 2012/11/05 add oda
		$INSERT_DATA['upd_date']   = "now()";									// 2012/11/05 add oda

		$where  = " WHERE service_course_list_num = '".$_POST['service_course_list_num']."'";
		$where .= " LIMIT 1;";

		$ERROR = $cdb->update(T_SERVICE_COURSE_LIST,$INSERT_DATA,$where);
	}

	if (!$ERROR) {
		$_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>";
	}
	return $ERROR;
}


/**
 * プラクティスコース名取得
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $course_num
 * @return string
 */
function get_practice_course_name($course_num) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$course_name = "";

	$sql = "SELECT * FROM " . T_COURSE .
			// " WHERE display = '1' AND state = '0'". // del start 2018/10/05 yoshizawa すらら英単語追加 // 非表示のマスタ名が表示されないので表示フラグは見ない
			" WHERE state = '0'".// del start 2018/10/05 yoshizawa すらら英単語追加
			"   AND course_num = '".$course_num."';";
	$course_result = $cdb->query($sql);

	while ($course_list = $cdb->fetch_assoc($course_result)) {
		$course_name = $course_list['course_name'];
	}

	return $course_name;
}


/**
 * 速習コース名取得
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $course_num
 * @return string
 */
function get_package_course_name($course_num) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$course_name = "";

	$sql = "SELECT * FROM " . T_PACKAGE_COURSE .
			// " WHERE display = '1' AND mk_flg = '0'". // del start 2018/10/05 yoshizawa すらら英単語追加 // 非表示のマスタ名が表示されないので表示フラグは見ない
			" WHERE mk_flg = '0'". // add start 2018/10/05 yoshizawa すらら英単語追加
			"   AND pk_course_num = '".$course_num."';";
	$course_result = $cdb->query($sql);

	while ($course_list = $cdb->fetch_assoc($course_result)) {
		$course_name = $course_list['pk_course_name'];
	}

	return $course_name;
}


/**
 * テストコース名取得
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $course_num
 * @return string
 */
function get_test_course_name($course_num) {

	$course_name = "";

	if ($course_num == 1) {
		$course_name = "小テスト";
	} elseif ($course_num == 2) {
		$course_name = "定期テスト";
	} elseif ($course_num == 4) {
		$course_name = "学力診断テスト";
	}

	return $course_name;
}

/**
 * プラクティスコース名取得
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $course_num
 * @return string
 */
function get_test_type_name($course_num) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$test_type_name = "";

	// $sql = "SELECT * FROM ".T_MS_TEST_TYPE." WHERE test_type_num = '1' AND display = '1' AND mk_flg = '0';";
	$sql = "SELECT * FROM ".T_MS_TEST_TYPE." WHERE test_type_num = '".$course_num."' AND mk_flg = '0';"; // 非表示のマスタ名が表示されないので表示フラグは見ない
	$course_result = $cdb->query($sql);

	while ($course_list = $cdb->fetch_assoc($course_result)) {
		$test_type_name = $course_list['test_type_name'];
	}

	return $test_type_name;
}

?>
