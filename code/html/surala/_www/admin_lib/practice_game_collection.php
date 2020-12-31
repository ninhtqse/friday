<?
/**
 * ゲーム収集要素作成管理
 *
 * 履歴
 * 2016/10/20 初期設定
 *
 * @author Azet
 */

// add hasegawa 小学生低学年版2次開発


/**
 * メイン処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */

function start() {

	// サブセション取得
	$ERROR = sub_session();

	// コース情報取得
	$html .= select_course();

	if ($_SESSION['sub_session']['course_num'] && $_SESSION['sub_session']['gknn']) {
		// チェック処理
		if (ACTION == "check") {
			$ERROR = check();
		}

		// 登録・修正・削除
		if (!$ERROR) {
			if (ACTION == "add") { $ERROR = add(); }
			elseif (ACTION == "change") { $ERROR = change(); }
			elseif (ACTION == "del") { $ERROR = change(); }
			elseif (ACTION == "↑") { $ERROR = up(); }
			elseif (ACTION == "↓") { $ERROR = down(); }
		}

		// 登録処理
		if (MODE == "add") {
			if (ACTION == "check") {
				if (!$ERROR) { $html .= check_html(); }
				else { $html .= addform($ERROR); }
			} elseif (ACTION == "add") {
				if (!$ERROR) { $html .= game_collection_list($ERROR); }
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
				if (!$ERROR) { $html .= game_collection_list($ERROR); }
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
				if (!$ERROR) { $html .= game_collection_list($ERROR); }
				else { $html .= viewform($ERROR); }
			} else {
				$html .= check_html();
			}
		// インポート処理
		} elseif (MODE == "game_collection_import") {
			$ERROR = game_collection_import();
			$html .= game_collection_list($ERROR);
		// 一覧表示
		} else {
			$html .= game_collection_list($ERROR);
		}
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
 * @return string HTML
 */
function game_collection_list($ERROR) {

	// グローバル変数
	global $L_EXP_CHA_CODE,$L_DISPLAY,$L_WRITE_TYPE;
	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];


	// セション情報取得
	//list($me_num,$onetime,$manager_level,$belong_num,$authority) = explode("<>",$_SESSION[myid]);

	// 権限取得
	if ($authority) { $L_AUTHORITY = explode("::",$authority); }

	$html .= "<b>コース ： ".$L_WRITE_TYPE[$_SESSION['sub_session']['course_num']]."</b><br><br>";

	// エラーメッセージが存在する場合、メッセージ表示
	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}
	// 権限をチェックコースを選択してください。
	if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__add",$_SESSION['authority'])===FALSE)) {

		$html .= "インポートする場合は収集要素txtファイル(S-JIS)を指定しCSVインポートを押してください<br>";			// update oda Mac対応 csv -> txt
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" enctype=\"multipart/form-data\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"game_collection_import\">\n";
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_SESSION['sub_session']['course_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"gknn\" value=\"".$_SESSION['sub_session']['gknn']."\">\n";
		$html .= "<input type=\"file\" size=\"40\" name=\"game_collection_file\">\n";
		$html .= "<input type=\"submit\" value=\"CSVインポート\">\n";
		$html .= "</form>\n";
		$html .= "<br />\n";
		$html .= "<form action=\"/admin/game_collection_make_csv.php\" method=\"POST\">";
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_SESSION['sub_session']['course_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"gknn\" value=\"".$_SESSION['sub_session']['gknn']."\">\n";
		$expList = "";
		if ( is_array($L_EXP_CHA_CODE) ) {
			$expList .= "海外版の場合は、出力形式について[Unicode]選択して、ダウンロードボタンをクリックしてください。<br />\n";
			$expList .= "<b>出力形式：</b>";
			$expList .= "<select name=\"exp_list\">";
			foreach( $L_EXP_CHA_CODE as $key => $val ){
				$expList .= "<option value=\"".$key."\">".$val."</option>";
			}
			$expList .= "</select>";
			$html .= $expList;
		}
		$html .= "<input type=\"submit\" value=\"CSVエクスポート\">";
		$html .= "</form>";
		$html .= "<br /><br /><br />\n";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"add\">\n";
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_SESSION['sub_session']['course_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"gknn\" value=\"".$_SESSION['sub_session']['gknn']."\">\n";
		$html .= "<input type=\"submit\" value=\"収集要素新規登録\">\n";
		$html .= "</form>\n";
		$html .= "<br />\n";
	}

	// 一覧取得SQL作成
	$sql  = "SELECT * FROM " . T_GAME_COLLECTION .
			" WHERE mk_flg ='0' ".
			"   AND course_num = '".$_SESSION['sub_session']['course_num']."'".
			"   AND gknn = '".$_SESSION['sub_session']['gknn']."'".
			"   ORDER BY list_num ";

	// データ読み込み
	if ($result = $cdb->query($sql)) {

		$max = $cdb->num_rows($result);
		if (!$max) {
			$html .= "今現在登録されているゲーム収集要素はありません。<br>\n";
			return $html;
		}

		$html .= "<table class=\"game_collection_form\">\n";
		$html .= "<tr class=\"game_collection_form_menu\">\n";

		// リストタイトル表示
		// 権限をチェック
		if (!ereg("practice__view",$authority)
			&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__sort",$_SESSION['authority'])===FALSE))
		) {
			$html .= "<th>↑</th>\n";
			$html .= "<th>↓</th>\n";
		}
		$html .= "<th>ID</th>\n";
		$html .= "<th style=\"padding:0 50px;\">収集要素名</th>\n";
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

		// 明細表示
		$i = 1;
		while ($list = $cdb->fetch_assoc($result)) {

			$html .= "<tr class=\"game_collection_form_cell\">\n";
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_SESSION['sub_session']['course_num']."\">\n";
			$html .= "<input type=\"hidden\" name=\"gknn\" value=\"".$_SESSION['sub_session']['gknn']."\">\n";
			$html .= "<input type=\"hidden\" name=\"game_collection_id\" value=\"".$list['game_collection_id']."\">\n";
			$up_submit = $down_submit = "&nbsp;";
			if ($i != 1) { $up_submit = "<input type=\"submit\" name=\"action\" value=\"↑\">\n"; }
			if ($i != $max) { $down_submit = "<input type=\"submit\" name=\"action\" value=\"↓\">\n"; }

			// 明細表示
			if (!ereg("practice__view",$authority)
				&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__sort",$_SESSION['authority'])===FALSE))
			) {
				$html .= "<td>".$up_submit."</td>\n";
				$html .= "<td>".$down_submit."</td>\n";
			}
			$html .= "<td>".$list['game_collection_id']."</td>\n";
			$game_collection_name = $list['game_collection_name'];
			$html .= "<td>".$game_collection_name."</td>\n";
			$html .= "<td>".$L_DISPLAY[$list['display']]."</td>\n";
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

	// グローバル変数
	global $L_GAME_COLLECTION_CLEAR_STATUS,$L_DISPLAY;

	$html .= "新規登録フォーム<br>\n";

	// エラーメッセージ表示
	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	// 入力画面表示
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"gknn\" value=\"".$_POST['gknn']."\">\n";

	// htmlクラス生成
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(GAME_COLLECTION_FORM);

	// $配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	$INPUTS['COLLECTIONNAME'] = array('type'=>'text','name'=>'game_collection_name','size'=>'60','value'=>$_POST['game_collection_name']);
	$INPUTS['UNIT'] = array('type'=>'text','name'=>'unit_num','size'=>'60','value'=>$_POST['unit_num']);
	$unit_att = "<br><span style=\"color: red;\">※複数設定する場合は、「 :: 」区切りで入力して下さい。</span>";
	$INPUTS['UNITATT'] = array('result'=>'plane','value'=>$unit_att);

	$INPUTS['REMARKS'] = array('type'=>'textarea','name'=>'usr_bko','rows'=>'5','cols'=>'50','value'=>$_POST['usr_bko']);

	// 表示区分ラジオボタン生成
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

	$display_value = $radio1 . "<label for=\"display\">".$L_DISPLAY['1']."</label> / " . $radio2 . "<label for=\"undisplay\">".$L_DISPLAY['2']."</label>";
	$INPUTS['DISPLAY'] = array('result'=>'plane','value'=>$display_value);

	$make_html->set_rep_cmd($INPUTS);
	$html .= $make_html->replace();

	// 制御ボタン定義
	$html .= "<input type=\"submit\" value=\"追加確認\">\n";
	$html .= "<input type=\"reset\" value=\"クリア\">\n";
	$html .= "</form>\n";
	$html .= "<br>\n";
	// 一覧に戻る
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"game_collection_list\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"gknn\" value=\"".$_POST['gknn']."\">\n";
	$html .= "<input type=\"submit\" value=\"一覧に戻る\">\n";
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

	// グローバル変数
	global $L_GAME_COLLECTION_CLEAR_STATUS,$L_DISPLAY;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (ACTION) {
		foreach ($_POST as $key => $val) { $$key = $val; }
	} else {
		$sql = "SELECT * FROM ".T_GAME_COLLECTION.
			" WHERE game_collection_id='".$_POST['game_collection_id']."' AND mk_flg='0' LIMIT 1;";
		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);
		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}
		foreach ($list as $key => $val) {
			$$key = replace_decode($val);
			$$key = ereg_replace("\"","&quot;",$$key);
		}
	}

	// 画面表示
	$html .= "詳細画面<br>\n";

	// エラーメッセージ表示
	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	// 入力画面表示
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"詳細\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$course_num."\">\n";
	$html .= "<input type=\"hidden\" name=\"gknn\" value=\"".$gknn."\">\n";
	$html .= "<input type=\"hidden\" name=\"game_collection_id\" value=\"".$game_collection_id."\">\n";

	// htmlクラス生成
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(GAME_COLLECTION_FORM);

	// 単元を取得
	if (!$unit_num) {													// add oda 2017/10/26 Mac対応 戻った時、入力値が消えてしまう為、判断追加。
		$unit_num = get_game_collection_unit($game_collection_id);
	}

	// $配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	$INPUTS['COLLECTIONNAME'] = array('type'=>'text','name'=>'game_collection_name','size'=>'60','value'=>$game_collection_name);
	$unit_att = "<br><span style=\"color: red;\">※複数設定する場合は、「 :: 」区切りで入力して下さい。</span>";
	$INPUTS['UNITATT'] = array('result'=>'plane','value'=>$unit_att);
	$INPUTS['UNIT'] = array('type'=>'text','name'=>'unit_num','size'=>'60','value'=>$unit_num);
	$INPUTS['REMARKS'] = array('type'=>'textarea','name'=>'usr_bko','rows'=>'5','cols'=>'50','value'=>$usr_bko);

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

	$display_value = $radio1 . "<label for=\"display\">".$L_DISPLAY['1']."</label> / " . $radio2 . "<label for=\"undisplay\">".$L_DISPLAY['2']."</label>";
	$INPUTS['DISPLAY'] = array('result'=>'plane','value'=>$display_value);

	$make_html->set_rep_cmd($INPUTS);
	$html .= $make_html->replace();

	// 制御ボタン定義
	$html .= "<input type=\"submit\" value=\"変更確認\">";
	$html .= "<input type=\"reset\" value=\"クリア\">";
	$html .= "</form><br>\n";
	// 一覧に戻る
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"game_collection_list\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"gknn\" value=\"".$_POST['gknn']."\">\n";
	$html .= "<input type=\"submit\" value=\"一覧に戻る\">\n";
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
	if (!$_POST['game_collection_name']) {
		$ERROR[] = "収集要素名が未入力です。";
	}
	if ($_POST['display'] == "1") {					// add oda 2017/03/31 ユニットが全て登録されていないので、非表示の時は、ユニットのチェックは行わない様に修正
		if (!$_POST['unit_num']) {
			$ERROR[] = "単元が未入力です。";
		} else {
			$arr_unit = explode("::",$_POST['unit_num']);

			// ユニットに数値以外の値が入っていないかチェック
			$error_flag = false;
			for ($j=0; $j<count($arr_unit); $j++) {
				if (trim($arr_unit[$j]) != "" && is_numeric($arr_unit[$j]) == false) {
					$ERROR[] = "単元に指定したユニット番号は、不正な情報が混ざっています。";
					$error_flag = true;
				}
			}

			// そのユニットが学年に存在するか調べる
			if (!$error_flag) {
				$unit_count = 0;
				$where = setTargetQueryKey( "unit", $_POST['gknn'] );

				$sql = "SELECT * FROM ".T_UNIT. " unit".
					" WHERE unit.course_num='".$_POST['course_num']."' AND unit.state='0'".
					$where.
					" AND unit.unit_num IN ('". implode("','", $arr_unit)."');";
				if ($result = $cdb->query($sql)) {
					$unit_count = $cdb->num_rows($result);
				}

				if (count($unit_count) > 0) {
					if ($unit_count != count($arr_unit)) {
						$ERROR[] = "単元に指定したユニット番号は、選択しているコース・学年には存在しません。";
					}
				}
			}

		}

	} else {
		// 単元の数値チェックのみ行う
		if ($_POST['unit_num']) {
			$arr_unit = explode("::",$_POST['unit_num']);

			// ユニットに数値以外の値が入っていないかチェック
			$error_flag = false;
			for ($j=0; $j<count($arr_unit); $j++) {
				if (trim($arr_unit[$j]) != "" && is_numeric($arr_unit[$j]) == false) {
					$ERROR[] = "単元に指定したユニット番号は、不正な情報が混ざっています。";
					$error_flag = true;
				}
			}
		}
	}

	if (!$_POST['display']) {
		$ERROR[] = "表示・非表示が未入力です。";
	}

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

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];


	global $L_GAME_COLLECTION_CLEAR_STATUS,$L_DISPLAY;

	// アクション情報をhidden項目に設定
	if ($_POST) {
		foreach ($_POST as $key => $val) {
			$val = str_replace(array("\r\n", "\r", "\n"), '', $val);
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

	if (ACTION) {
		foreach ($_POST as $key => $val) { $$key = $val; }

	} else {
		$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"change\">\n";
		$sql = "SELECT * FROM ".T_GAME_COLLECTION.
			" WHERE game_collection_id='".$_POST['game_collection_id']."' AND mk_flg='0' LIMIT 1;";

		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);

		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}
		foreach ($list as $key => $val) {
			$$key = replace_decode($val);
			$$key = ereg_replace("\"","&quot;",$$key);
		}

		// 単元を取得
		$unit_num = get_game_collection_unit($game_collection_id);
	}

	// ボタン表示文言判定
	if (MODE != "削除") { $button = "登録"; } else { $button = "削除"; }

	// 入力確認画面表示
	$html = "<br>\n";
	$html .= "確認画面：以下の内容で".$button."してもよろしければ".$button."ボタンをクリックしてください。<br>\n";

	// htmlクラス生成
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(GAME_COLLECTION_FORM);

	// $配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	if (!$game_collection_id) { $game_collection_id = "---"; }
	$INPUTS['COLLECTIONNAME'] = array('result'=>'plane','value'=>$game_collection_name);
	$INPUTS['UNIT'] = array('result'=>'plane','value'=>$unit_num);
	$INPUTS['REMARKS'] = array('result'=>'plane','value'=>$usr_bko);
	$INPUTS['DISPLAY'] = array('result'=>'plane','value'=>$L_DISPLAY[$display]);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();

	// 制御ボタン定義
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" style=\"float:left\">\n";
	$html .= $HIDDEN;

	$html .= "<input type=\"submit\" value=\"".$button."\">\n";
	$html .= "</form>";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";

	if (ACTION) {
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
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"game_collection_list\">\n";
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"gknn\" value=\"".$_POST['gknn']."\">\n";
	}
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}


/**
 * ＤＢ登録処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function add() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$unit_num = "";

	// 登録項目設定
	foreach ($_POST as $key => $val) {
		if ($key == "action") { continue; }
		if ($key == "unit_num") {
			$unit_num = $val;
			continue;
		}
		$INSERT_DATA[$key] = "$val";
	}
	$INSERT_DATA['course_num']		= $_SESSION['sub_session']['course_num'];
	$INSERT_DATA['gknn']			= $_SESSION['sub_session']['gknn'];
	$INSERT_DATA['ins_syr_id']		= "add";
	$INSERT_DATA['ins_date']		= "now()";
	$INSERT_DATA['ins_tts_id']		= $_SESSION['myid']['id'];
	$INSERT_DATA['upd_syr_id']		= "add";
	$INSERT_DATA['upd_date']		= "now()";
	$INSERT_DATA['upd_tts_id']		= $_SESSION['myid']['id'];

	// DB追加処理
	$ERROR = $cdb->insert(T_GAME_COLLECTION,$INSERT_DATA);

	if (!$ERROR) {
		$game_collection_id = $cdb->insert_id();
		// 表示順登録
		$INSERT_DATA2['list_num'] = $game_collection_id;
		$where = " WHERE game_collection_id='".$game_collection_id."' LIMIT 1;";

		$ERROR = $cdb->update(T_GAME_COLLECTION,$INSERT_DATA2,$where);
		// 収集要素関連テーブルを更新
		$ERROR = update_game_collection_unit($game_collection_id, $unit_num);
	}

	if (!$ERROR) { $_SESSION['select_menu'] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}


/**
 * ＤＢ変更処理
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

		$INSERT_DATA['game_collection_name']		= $_POST['game_collection_name'];
		$INSERT_DATA['usr_bko']				= $_POST['usr_bko'];
		$INSERT_DATA['display']				= $_POST['display'];
		$INSERT_DATA['upd_syr_id']			= "update";
		$INSERT_DATA['upd_tts_id']			= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date']			= "now()";

		$where = " WHERE game_collection_id = '".$_POST['game_collection_id']."' LIMIT 1;";

		$ERROR = $cdb->update(T_GAME_COLLECTION,$INSERT_DATA,$where);

		// 収集要素とユニットの関連テーブル更新
		$ERROR = update_game_collection_unit($_POST['game_collection_id'], $_POST['unit_num']);

	// 削除処理
	} elseif (MODE == "削除") {
		$INSERT_DATA['display']				= "2";
		$INSERT_DATA['mk_flg']				= "1";
		$INSERT_DATA['mk_tts_id']			= $_SESSION['myid']['id'];
		$INSERT_DATA['mk_date']				= "now()";
		$INSERT_DATA['upd_syr_id']			= "del";
		$INSERT_DATA['upd_tts_id']			= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date']			= "now()";
		$where = " WHERE game_collection_id = '".$_POST['game_collection_id']."' LIMIT 1;";

		$ERROR = $cdb->update(T_GAME_COLLECTION,$INSERT_DATA,$where);

		// 収集要素とユニットの関連テーブル削除
		$ERROR = delete_game_collection_unit($_POST['game_collection_id']);
	}

	if (!$ERROR) { $_SESSION['select_menu'] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}


/**
 * サブセションをPOSTから設定
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * (POST項目では、画面再描画の際、パラメータの受け渡しができない為）
 * @author Azet
 */
function sub_session() {

	// コースをPOSTから取得し、セションに格納
	if (strlen($_POST['course_num'])) {
		$_SESSION['sub_session']['course_num'] = $_POST['course_num'];
	}

	// 学年をPOSTから取得し、セションに格納
	if (strlen($_POST['gknn'])) {
		$_SESSION['sub_session']['gknn'] = $_POST['gknn'];
	}

	return;
}



/**
 * コース選択
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function select_course() {

	global $L_WRITE_TYPE,$L_GAME_SCHOOL_CATEGORY ;

	//コース
	$couse_html = "<option value=\"\">選択して下さい</option>\n";
	foreach ($L_WRITE_TYPE AS $course_num => $course_name) {
		if ($course_name == "") {
			continue;
		}
		$selected = "";
		if ($_SESSION['sub_session']['course_num'] == $course_num) { $selected = "selected"; }

		$couse_html .= "<option value=\"".$course_num."\" ".$selected.">".$course_name."</option>\n";
	}


	if($_POST['course_num'] && !$_POST['gknn']) { $_SESSION['sub_session']['gknn'] = ""; }
	//学年
	$gknn_html = "<option value=\"\">選択して下さい</option>\n";
	foreach ($L_GAME_SCHOOL_CATEGORY AS $gknn => $gknn_name) {
		if ($course_name == "") {
			continue;
		}
		$selected = "";
		if ($_SESSION['sub_session']['gknn'] == $gknn) { $selected = "selected"; }

		$gknn_html .= "<option value=\"".$gknn."\" ".$selected.">".$gknn_name."</option>\n";
	}

	// 抽出条件表示
	$html .= "<br>\n";
	$html .= "<table class=\"unit_form\">\n";
	$html .= "<tr class=\"unit_form_menu\">\n";
	$html .= "<td>コース</td>\n";
	$html .= "<td>学年</td>\n";
	$html .= "</tr>\n";
	$html .= "<tr class=\"unit_form_cell\">\n";
	$html .= "<td>";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"menu\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"set_course\">\n";
	$html .= "<select name=\"course_num\" onchange=\"submit();\">\n";
	$html .= $couse_html;
	$html .= "</select>\n";
	$html .= "</form>\n";
	$html .= "</td>\n";
	$html .= "<td>";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"menu\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"set_gknn\">\n";
	$html .= "<select name=\"gknn\" onchange=\"submit();\">\n";
	$html .= $gknn_html;
	$html .= "</select>\n";
	$html .= "</form>\n";
	$html .= "</td>\n";
	$html .= "</tr>\n";
	$html .= "</table><br>\n";

	// コース未選択メッセージ
	if (!$_SESSION['sub_session']['course_num']) {
		$html .= "コースを選択してください。<br>\n";
	}
	// 学年未選択メッセージ
	if ($_SESSION['sub_session']['course_num'] && !$_SESSION['sub_session']['gknn']) {
		$html .= "学年を選択してください。<br>\n";
	}

	return $html;
}


/**
 * アップロードファイル取り込み処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function game_collection_import() {

	global $L_GAME_COLLECTION_CLEAR_STATUS;

	if (!$_POST['course_num']) {
		$ERROR[] = "コースが確認できません。";
	}

	if (!$_POST['gknn']) {
		$ERROR[] = "学年が確認できません。";
	}


	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$file_name = $_FILES['game_collection_file']['name'];
	$file_tmp_name = $_FILES['game_collection_file']['tmp_name'];
	$file_error = $_FILES['game_collection_file']['error'];

	if (!$file_tmp_name) {
		$ERROR[] = "収集要素txtファイルが指定されておりません。";
	} elseif (!eregi("(.txt)$",$file_name)) {
		$ERROR[] = "ファイルの拡張子が不正です。(txt)";
	} elseif ($file_error == 1) {
		$ERROR[] = "アップロードできるファイルの容量が設定範囲を超えてます。サーバー管理者へ相談してください。";
	} elseif ($file_error == 3) {
		$ERROR[] = "ファイルの一部分のみしかアップロードされませんでした。";
	} elseif ($file_error == 4) {
		$ERROR[] = "ファイルがアップロードされませんでした。";
	}
	if ($ERROR) {
		if ($file_tmp_name && file_exists($file_tmp_name)) {
			unlink($file_tmp_name);
		}
		return $ERROR;
	}

	$LIST = file($file_tmp_name);
	if ($LIST) {
		$i = 0;
		foreach ($LIST AS $VAL) {
			if ($i == 0) {
				$i++;  continue;
			}
			unset($LINE);
			$VAL = trim($VAL);
			if (!$VAL || !ereg("\t",$VAL)) {
				continue;
			}
			$file_data = explode("\t",$VAL);

			// 項目が全て設定されている場合
			if (count($file_data) == 6) {
				$LINE['game_collection_id'] 	= $file_data[0];
				$LINE['game_collection_name'] 	= $file_data[1];
				$LINE['unit_num'] 		= $file_data[2];
				$LINE['usr_bko'] 		= $file_data[3];
				$LINE['display'] 		= $file_data[4];
				$LINE['mk_flg'] 		= $file_data[5];
			// 管理番号が未設定の場合は、格納配列が１つ前にずれる
			} elseif (count($file_data) == 5) {
				$LINE['game_collection_id'] 	= '0';
				$LINE['game_collection_name'] 	= $file_data[0];
				$LINE['unit_num'] 		= $file_data[1];
				$LINE['usr_bko'] 		= $file_data[2];
				$LINE['display'] 		= $file_data[3];
				$LINE['mk_flg'] 		= $file_data[4];
			} else {
				$ERROR[] = ($i+1)."行目　未入力の項目があるためスキップしました。";
				continue;
			}

			if ($LINE) {
				foreach ($LINE AS $key => $val) {
					if ($val) {
						$code = judgeCharacterCode ( $val );
						if ( $code != 'UTF-8' ) {
							$val = mb_convert_encoding($val,"UTF-8","sjis-win");
						}
						$LINE[$key] = replace_encode($val);
					}
				}
			}
			/* ============================== エラーチェック ============================== */

			$error_flg = false;
			// 収集要素名
			if (!$LINE['game_collection_name']) {
				$ERROR[] = ($i+1)."行目　収集要素名が未入力です。";
				$error_flg = true;
			}

			// 単元
			$arr_unit = array();
			$unit_error_flag = false;
			if ($LINE['display'] == "1") {							// add oda 2017/03/31 ユニットが全て登録されていないので、非表示の時は、ユニットのチェックは行わない様に修正
				if (!$LINE['unit_num']) {
					$ERROR[] = ($i+1)."行目　単元が未入力です。";
					$unit_error_flag = true;
				} else {
					$unit_count = 0;
					$arr_unit = explode("::",$LINE['unit_num']);

					// 単元に数値以外の値が入っていないかチェック
					for ($j=0; $j<count($arr_unit); $j++) {
						if (trim($arr_unit[$j]) != "" && is_numeric($arr_unit[$j]) == false) {
							$ERROR[] = ($i+1)."行目　単元に指定したユニット番号は、不正な情報が混ざっています。";
							$unit_error_flag = true;
						}
					}

					// 単元が存在するかチェック
					if (!$unit_error_flag) {

						$where = setTargetQueryKey( "unit", $_POST['gknn'] );

						$sql = "SELECT * FROM ".T_UNIT.
							" WHERE course_num='".$_POST['course_num']."' AND state='0'".
							$where.
							" AND unit_num IN ('". implode("','", $arr_unit)."');";

						if ($result = $cdb->query($sql)) {
							$unit_count = $cdb->num_rows($result);
						}
						if (count($unit_count) > 0) {
							if ($unit_count != count($arr_unit)) {
								$ERROR[] = ($i+1)."行目　単元に指定したユニット番号は、既に削除されているか、選択しているコース・学年には存在しません。ユニット番号=".$LINE['unit_num'];
								$unit_error_flag = true;
							}
						}
					}
				}

			}

			// 単元の項目がエラーだったら空にする
			if($unit_error_flag) {
				$LINE['unit_num'] = "";
				$error_flg = true;
			}

			// 表示／非表示
			if (!$LINE['display']) {
				$ERROR[] = ($i+1)."行目　表示区分が未入力です。";
				$error_flg = true;
			} else {
				if(is_numeric($LINE['display']) == false) {
					$ERROR[] = ($i+1)."行目　表示区分に不正な情報が混ざっています。";
					$error_flg = true;
				}
			}
			// 無効フラグ
			if (!$LINE['mk_flg'] || is_numeric($LINE['mk_flg']) == false) {
				$LINE['mk_flg'] = "0";
			}

			/* ============================================================================ */


			// エラーの時は強制的に非表示とする。
			if ($error_flg) {
				$LINE['display'] = "2";
			}

			// データ存在チェック
			$sql  = "SELECT * FROM " .T_GAME_COLLECTION.
					" WHERE game_collection_id = '".$LINE['game_collection_id']."';";
			$game_collection_count = 0;
			if ($result = $cdb->query($sql)) {
				$game_collection_count = $cdb->num_rows($result);
			}

			// 登録処理
			if ($game_collection_count == 0) {

				$INSERT_DATA = array();

				// 新規登録の際は、auto_incrementのIDを使うので$LINE['game_collection_id']は登録しない。

				$INSERT_DATA['game_collection_name']			= $LINE['game_collection_name'];
				$INSERT_DATA['course_num']				= $_POST['course_num'];
				$INSERT_DATA['gknn']					= $_POST['gknn'];
				$INSERT_DATA['usr_bko']					= $LINE['usr_bko'];
				$INSERT_DATA['display']					= $LINE['display'];
				$INSERT_DATA['mk_flg']					= $LINE['mk_flg'];
				$INSERT_DATA['ins_syr_id']				= "add";
				$INSERT_DATA['ins_date']				= "now()";
				$INSERT_DATA['ins_tts_id']				= $_SESSION['myid']['id'];
				$INSERT_DATA['upd_syr_id']				= "add";
				$INSERT_DATA['upd_date']				= "now()";
				$INSERT_DATA['upd_tts_id']				= $_SESSION['myid']['id'];

				$INSERT_ERROR = $cdb->insert(T_GAME_COLLECTION,$INSERT_DATA);
				if(count($INSERT_ERROR) > 0) {
					foreach ($INSERT_ERROR AS $VAL) {
						$ERROR[] = $VAL;
					}
				}

				if(!$INSERT_ERROR) {
					$game_collection_id = $cdb->insert_id();
					// 表示順登録
					$INSERT_DATA2['list_num'] = $game_collection_id;
					$where = " WHERE game_collection_id='".$game_collection_id."' LIMIT 1;";

					$INSERT_ERROR = $cdb->update(T_GAME_COLLECTION,$INSERT_DATA2,$where);
					if(count($INSERT_ERROR) > 0) {
						foreach ($INSERT_ERROR AS $VAL) {
							$ERROR[] = $VAL;
						}
					}
				}

				if (!$ERROR) {
					// 収集要素関連テーブルを更新
					$INSERT_ERROR = update_game_collection_unit($game_collection_id, $LINE['unit_num'], $LINE['display']);
					if(count($INSERT_ERROR) > 0) {
						foreach ($INSERT_ERROR AS $VAL) {
							$ERROR[] = $VAL;
						}
					}
				}

			// 更新処理・削除処理
			} else {
				// 更新
				$INSERT_DATA = array();

				if ($LINE['mk_flg'] == "0") {

					$INSERT_DATA['game_collection_id']		= $LINE['game_collection_id'];
					$INSERT_DATA['game_collection_name']		= $LINE['game_collection_name'];
					$INSERT_DATA['course_num']			= $_POST['course_num'];
					$INSERT_DATA['gknn']				= $_POST['gknn'];
					$INSERT_DATA['usr_bko']				= $LINE['usr_bko'];
					$INSERT_DATA['display']				= $LINE['display'];
					$INSERT_DATA['mk_flg']				= "0";
					$INSERT_DATA['mk_tts_id']			= "NULL";
					$INSERT_DATA['mk_date']				= "NULL";
					$INSERT_DATA['upd_syr_id']			= "update";
					$INSERT_DATA['upd_date']			= "now()";
					$INSERT_DATA['upd_tts_id']			= $_SESSION['myid']['id'];
					// 収集要素関連テーブルを更新
					$INSERT_ERROR = update_game_collection_unit($LINE['game_collection_id'], $LINE['unit_num'],$LINE['display']);
					if(count($INSERT_ERROR) > 0) {
						foreach ($INSERT_ERROR AS $VAL) {
							$ERROR[] = $VAL;
						}
					}
				// 削除
				} else {

					$INSERT_DATA['display']				= "2";
					$INSERT_DATA['mk_flg']				= "1";
					$INSERT_DATA['mk_tts_id']			= $_SESSION['myid']['id'];
					$INSERT_DATA['mk_date']				= "now()";
					$INSERT_DATA['upd_syr_id']			 = "del";
					$INSERT_DATA['upd_tts_id']			= $_SESSION['myid']['id'];
					$INSERT_DATA['upd_date']			= "now()";

					$INSERT_ERROR = delete_game_collection_unit($LINE['game_collection_id']);
					if(count($INSERT_ERROR) > 0) {
						foreach ($INSERT_ERROR AS $VAL) {
							$ERROR[] = $VAL;
						}
					}
				}

				$where = " WHERE game_collection_id = '".$LINE['game_collection_id']."' LIMIT 1;";

				$INSERT_ERROR = $cdb->update(T_GAME_COLLECTION,$INSERT_DATA,$where);
				if(count($INSERT_ERROR) > 0) {
					foreach ($INSERT_ERROR AS $VAL) {
						$ERROR[] = $VAL;
					}
				}
			}
			$i++;
		}
	}

	// アップロードファイル削除
	if ($file_tmp_name && file_exists($file_tmp_name)) {
		unlink($file_tmp_name);
	}

	return $ERROR;
}

/**
 * ゲーム収集要素マスタに紐付くユニットを取得
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $game_collection_id
 * @return string
 */
function get_game_collection_unit($game_collection_id) {

	$game_collection_unit_string = "";

	if (!$game_collection_id) { return $game_collection_unit_string; }

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql = "SELECT * FROM ".T_GAME_COLLECTION_UNIT.
			//update start kimura 2017/12/19 AWS移設 ソートなし → ORDER BY句追加
			//" WHERE game_collection_id='".$game_collection_id."' AND mk_flg='0';";
			" WHERE game_collection_id='".$game_collection_id."' AND mk_flg='0'".
			// " ORDER BY game_collection_id,unit_num".	// upd hasegawa 2017/12/27 AWS移設 ソート条件追加
			" ORDER BY unit_num".
			" ;";
			//update end   kimura 2017/12/19

	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			if ($game_collection_unit_string) { $game_collection_unit_string .= "::"; }
			$game_collection_unit_string .= $list['unit_num'];
		}
	}
	return $game_collection_unit_string;
}


/**
 * DB変更処理（収集要素とユニットの関連テーブル）
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $game_collection_id
 * @param integer $unit_num
 * @param integer $display (インポート時のみ使用)
 * @return array エラーの場合
 */
function update_game_collection_unit($game_collection_id, $unit_num, $display='0') {

	$UNIT_LIST = array();

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql = "SELECT * FROM ".T_GAME_COLLECTION_UNIT.
			" WHERE game_collection_id='".$game_collection_id."';";

	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$UNIT_LIST[] = $list['unit_num'];
		}
	}
	// 単元が未入力の場合はリターン
	if (count($UNIT_LIST) == 0 && $unit_num == "") { return $ERROR; }

	$STRING_UNIT_LIST = explode("::", $unit_num);

	$ADD_LIST = array();
	$UPDATE_LIST = array();
	$DELETE_LIST = array();

	// アップデート対象のunit_numを配列に格納
	foreach ($UNIT_LIST as $db_key => $db_val) {
		foreach ($STRING_UNIT_LIST as $string_key => $string_val) {
			if ($db_val == $string_val) {
				$UPDATE_LIST[] = $string_val;
				unset($UNIT_LIST[$db_key]);
				unset($STRING_UNIT_LIST[$string_key]);
			}
		}
	}

	// 削除対象のunit_numを配列に格納
	foreach ($UNIT_LIST as $db_key => $db_val) {
		if (trim($db_val) != "") {
			$DELETE_LIST[] = $db_val;
		}
	}

	// 登録対象のunit_numを配列に格納
	foreach ($STRING_UNIT_LIST as $string_key => $string_val) {
		if (trim($string_val) != "") {
			$ADD_LIST[] = $string_val;
		}
	}

	if($display == '0') {	$display = $_POST['display'];	}

	// 追加処理
	$INSERT_DATA = "";
	if (count($ADD_LIST) > 0) {
		foreach ($ADD_LIST as $key => $val) {
			$INSERT_DATA['game_collection_id']		= $game_collection_id;
			$INSERT_DATA['course_num']			= $_POST['course_num'];
			$INSERT_DATA['unit_num']			= $val;
			$INSERT_DATA['display']				= $display;
			$INSERT_DATA['upd_syr_id']			= "add";
			$INSERT_DATA['upd_tts_id']			= $_SESSION['myid']['id'];
			$INSERT_DATA['upd_date']			= "now()";
			$INSERT_DATA['ins_syr_id']			= "add";
			$INSERT_DATA['ins_tts_id']			= $_SESSION['myid']['id'];
			$INSERT_DATA['ins_date']			= "now()";

			$ERROR = $cdb->insert(T_GAME_COLLECTION_UNIT,$INSERT_DATA);
		}
	}

	// 更新処理
	$INSERT_DATA = "";
	if (count($UPDATE_LIST) > 0) {
		foreach ($UPDATE_LIST as $key => $val) {
			$INSERT_DATA['display']				= $display;
			$INSERT_DATA['mk_flg']				= "0";
			$INSERT_DATA['mk_tts_id']			= "NULL";
			$INSERT_DATA['mk_date']				= "NULL";
			$INSERT_DATA['upd_syr_id']			= "update";
			$INSERT_DATA['upd_date']			= "now()";
			$INSERT_DATA['upd_tts_id']			= $_SESSION['myid']['id'];

			$where  = " WHERE game_collection_id = '".$game_collection_id."'";
			$where .= "   AND unit_num = '".$val."' LIMIT 1;";

			$ERROR = $cdb->update(T_GAME_COLLECTION_UNIT,$INSERT_DATA,$where);
		}
	}

	// 削除処理
	$INSERT_DATA = "";
	if (count($DELETE_LIST) > 0) {
		foreach ($DELETE_LIST as $key => $val) {
			$INSERT_DATA['display']				= "2";
			$INSERT_DATA['mk_flg']				= "1";
			$INSERT_DATA['mk_tts_id']			= $_SESSION['myid']['id'];
			$INSERT_DATA['mk_date']				= "now()";
			$INSERT_DATA['upd_syr_id']			= "del";
			$INSERT_DATA['upd_tts_id']			= $_SESSION['myid']['id'];
			$INSERT_DATA['upd_date']			= "now()";
			$where  = " WHERE game_collection_id = '".$game_collection_id."'";
			$where .= "   AND unit_num = '".$val."' LIMIT 1;";

			$ERROR = $cdb->update(T_GAME_COLLECTION_UNIT,$INSERT_DATA,$where);
		}
	}

	if (!$ERROR) {
		$_SESSION['select_menu'] = MAIN . "<>" . SUB . "<><><>";
	}
	return $ERROR;
}


/**
 * DB変更処理（削除）（収集要素とユニットの関連テーブル）
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $game_collection_id
 * @return array エラーの場合
 */
function delete_game_collection_unit($game_collection_id) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// 削除処理
	if ($game_collection_id) {
		$INSERT_DATA['display']				= "2";
		$INSERT_DATA['mk_flg']				= "1";
		$INSERT_DATA['mk_tts_id']			= $_SESSION['myid']['id'];
		$INSERT_DATA['mk_date']				= "now()";
		$INSERT_DATA['upd_syr_id']			= "del";
		$INSERT_DATA['upd_tts_id']			= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date']			= "now()";

		$where  = " WHERE game_collection_id = '".$game_collection_id."';";

		$ERROR = $cdb->update(T_GAME_COLLECTION_UNIT,$INSERT_DATA,$where);
	}

	if (!$ERROR) {
		$_SESSION['select_menu'] = MAIN . "<>" . SUB . "<><><>";
	}
	return $ERROR;
}


/**
 * 表示順を上がる機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function up() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql  = "SELECT * FROM ".T_GAME_COLLECTION.
			" WHERE game_collection_id='".$_POST['game_collection_id']."' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$m_game_collection_id = $list['game_collection_id'];
		$m_list_num = $list['list_num'];
	}
	if (!$m_game_collection_id || !$m_list_num) { $ERROR[] = "移動するゲーム収集要素情報が取得できません。"; }

	if (!$ERROR) {
		$sql  = "SELECT * FROM ".T_GAME_COLLECTION.
				" WHERE mk_flg='0' AND course_num='".$_POST['course_num']."' ".
				" AND gknn='".$_POST['gknn']."' ".
				" AND list_num < '".$m_list_num."' ORDER BY list_num DESC LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$c_game_collection_id = $list['game_collection_id'];
			$c_list_num = $list['list_num'];
		}
	}
	if (!$c_game_collection_id || !$c_list_num) { $ERROR[] = "移動されるゲーム収集要素情報が取得できません。"; }

	$INSERT_DATA = "";
	if (!$ERROR) {
		$INSERT_DATA['list_num'] = $c_list_num;
		$INSERT_DATA['upd_date'] = "now()";
		$where = " WHERE game_collection_id='".$m_game_collection_id."' LIMIT 1;";

		$ERROR = $cdb->update(T_GAME_COLLECTION,$INSERT_DATA,$where);
	}

	$INSERT_DATA = "";
	if (!$ERROR) {
		$INSERT_DATA['list_num'] = $m_list_num;
		$INSERT_DATA['upd_date'] = "now()";
		$where = " WHERE game_collection_id='".$c_game_collection_id."' LIMIT 1;";

		$ERROR = $cdb->update(T_GAME_COLLECTION,$INSERT_DATA,$where);
	}

	return $ERROR;
}

/**
 * 表示順を下がる機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function down() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql  = "SELECT * FROM ".T_GAME_COLLECTION.
			" WHERE game_collection_id='".$_POST['game_collection_id']."' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$m_game_collection_id = $list['game_collection_id'];
		$m_list_num = $list['list_num'];
	}
	if (!$m_game_collection_id || !$m_list_num) { $ERROR[] = "移動するゲーム収集要素情報が取得できません。"; }

	if (!$ERROR) {
		$sql  = "SELECT * FROM ".T_GAME_COLLECTION.
				" WHERE mk_flg='0' AND course_num='".$_POST['course_num']."' ".
				" AND gknn='".$_POST['gknn']."' ".
				" AND list_num > '".$m_list_num."' ORDER BY list_num LIMIT 1;";

		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$c_game_collection_id = $list['game_collection_id'];
			$c_list_num = $list['list_num'];
		}
	}
	if (!$c_game_collection_id || !$c_list_num) { $ERROR[] = "移動されるゲーム収集要素情報が取得できません。"; }

	$INSERT_DATA = "";
	if (!$ERROR) {
		$INSERT_DATA['list_num'] = $c_list_num;
		$INSERT_DATA['upd_date'] = "now()";
		$where = " WHERE game_collection_id='".$m_game_collection_id."' LIMIT 1;";

		$ERROR = $cdb->update(T_GAME_COLLECTION,$INSERT_DATA,$where);
	}

	$INSERT_DATA = "";
	if (!$ERROR) {
		$INSERT_DATA['list_num'] = $m_list_num;
		$INSERT_DATA['upd_date'] = "now()";
		$where = " WHERE game_collection_id='".$c_game_collection_id."' LIMIT 1;";

		$ERROR = $cdb->update(T_GAME_COLLECTION,$INSERT_DATA,$where);
	}

	return $ERROR;
}
?>
