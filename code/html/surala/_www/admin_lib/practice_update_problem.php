<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * プラクティスステージ管理　プラクティスアップデートプログラム
 * 	サブプログラム	問題情報アップデート
 *
 * @author Azet
 */

/**
 * HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $L_NAME
 * @return string HTML
 */
function sub_start($L_NAME) {
	if (ACTION == "update") {
		$ERROR = update();
	} elseif (ACTION == "db_session") {
		$ERROR = select_database();
		$ERROR = select_web();
	}

	if (!$ERROR && ACTION == "update") {
		$html = update_end_html($L_NAME);
	} else {
		$html = default_html($L_NAME,$ERROR);
	}
	return $html;
}


/**
 * デフォルトページ
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $L_NAME
 * @param array $ERROR
 * @return string HTML
 */
function default_html($L_NAME,$ERROR) {

	global $L_WRITE_TYPE,$L_DISPLAY,$L_PROBLEM_TYPE,$L_FORM_TYPE;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"".$_POST['mode']."\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"db_session\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$_POST['stage_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"".$_POST['unit_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"block_num\" value=\"".$_POST['block_num']."\">\n";
	$html .= select_db_menu();
	$html .= select_web_menu();
	$html .= "</form>\n";

	//サーバー情報取得
	if (!$_SESSION['select_db']) { return $html; }
	if (!$_SESSION['select_web']) { return $html; }

	unset($BASE_DATA);
	unset($MAIN_DATA);

	// -- 閲覧DB
	$connect_db = new connect_db();
	$connect_db->set_db($_SESSION['select_db']);
	$ERROR = $connect_db->set_connect_db();
	if ($ERROR) {
		$html .= ERROR($ERROR);
	}

	$submit_db		= "<input type=\"button\" value=\"データ\" OnClick=\"practice_update_action('db');\">";
	$submit_img		= "<input type=\"button\" value=\"画像\" OnClick=\"practice_update_action('img');\">";
	$submit_voice	= "<input type=\"button\" value=\"音声\" OnClick=\"practice_update_action('voice');\">";

	if (ACTION == "img") {	//	問題画像ファイル
		$submit_img = "画像";

		//	ローカルサーバー
		unset($local_max);
		$local_dir = MATERIAL_PROB_DIR.$_POST['course_num']."/".$_POST['stage_num'].
						"/".$_POST['lesson_num']."/".$_POST['unit_num']."/".$_POST['block_num']."/";
		$LOCAL_FILE_NAME = local_read_dir($local_dir);
		$local_max = count($LOCAL_FILE_NAME["f"]);
		if ($local_max) {
			$LOCAL_TIME = $LOCAL_FILE_NAME;
			@rsort($LOCAL_TIME[f]);
			$local_new_time = $LOCAL_TIME[f][0];
			$local_html .= "最新更新時間：".date("Y/m/d H:i:s",$local_new_time);	//	add :sookawara 2012/08/01
			$local_html .= "<table class=\"course_form\">\n";
			$local_html .= "<tr class=\"course_form_menu\">\n";
			$local_html .= "<th>ファイル名</th>\n";
			$local_html .= "<th>更新時間</th>\n";
			$local_html .= "</tr>\n";
			@ksort($LOCAL_FILE_NAME["f"],SORT_STRING);
			foreach ($LOCAL_FILE_NAME["f"] AS $key => $val) {
				$local_html .= "<tr class=\"course_form_cell\">\n";
				$local_html .= "<td>".$key."</td>\n";
				$local_html .= "<td>".date("Y/m/d H:i:s",$val)."</td>\n";	//	add :sookawara 2012/08/01
				$local_html .= "</tr>\n";
			}
			$local_html .= "</table>\n";
		} else {
			$local_html = "登録されておりません。";
		}

		//	閲覧サーバー
		unset($remote_max);
		$remote_dir = REMOTE_MATERIAL_PROB_DIR.$_POST['course_num']."/".$_POST['stage_num'].
						"/".$_POST['lesson_num']."/".$_POST['unit_num']."/".$_POST['block_num']."/";
		$FILE_NAME = remote_read_dir($_SESSION['select_web'],$remote_dir);
		$remote_max = count($FILE_NAME["f"]);
		if ($remote_max) {
			$REMOTE_TIME = $FILE_NAME;
			@rsort($REMOTE_TIME[f]);
			$remote_new_time = $REMOTE_TIME[f][0];
			$remote_html .= "最新更新時間：".date("Y/m/d H:i:s",$remote_new_time);	//	add :sookawara 2012/08/01
			$remote_html .= "<table class=\"course_form\">\n";
			$remote_html .= "<tr class=\"course_form_menu\">\n";
			$remote_html .= "<th>ファイル名</th>\n";
			$remote_html .= "<th>更新時間</th>\n";
			$remote_html .= "</tr>\n";
			@ksort($FILE_NAME["f"],SORT_STRING);
			foreach ($FILE_NAME["f"] AS $key => $val) {
				$remote_html .= "<tr class=\"course_form_cell\">\n";
				$remote_html .= "<td>".$key."</td>\n";
				$remote_html .= "<td>".date("Y/m/d H:i:s",$val)."</td>\n";	//	add :sookawara 2012/08/01
				$remote_html .= "</tr>\n";
			}
			$remote_html .= "</table>\n";
		} else {
			$remote_html = "登録されておりません。";
		}
	} elseif (ACTION == "voice") {	//	問題音声ファイル
		$submit_voice = "音声";

		//	ローカルサーバー
		unset($local_max);
		$local_dir = MATERIAL_VOICE_DIR.$_POST['course_num']."/".$_POST['stage_num'].
						"/".$_POST['lesson_num']."/".$_POST['unit_num']."/".$_POST['block_num']."/";
		$LOCAL_FILE_NAME = local_read_dir($local_dir);
		$local_max = count($LOCAL_FILE_NAME["f"]);
		if ($local_max) {
			$LOCAL_TIME = $LOCAL_FILE_NAME;
			@rsort($LOCAL_TIME[f]);
			$local_new_time = $LOCAL_TIME[f][0];
			$local_html .= "最新更新時間：".date("Y/m/d H:i:s",$local_new_time);	//	add :sookawara 2012/08/01
			$local_html .= "<table class=\"course_form\">\n";
			$local_html .= "<tr class=\"course_form_menu\">\n";
			$local_html .= "<th>ファイル名</th>\n";
			$local_html .= "<th>更新時間</th>\n";
			$local_html .= "</tr>\n";
			@ksort($LOCAL_FILE_NAME["f"],SORT_STRING);
			foreach ($LOCAL_FILE_NAME["f"] AS $key => $val) {
				$local_html .= "<tr class=\"course_form_cell\">\n";
				$local_html .= "<td>".$key."</td>\n";
				$local_html .= "<td>".date("Y/m/d H:i:s",$val)."</td>\n";	//	add :sookawara 2012/08/01
				$local_html .= "</tr>\n";
			}
			$local_html .= "</table>\n";
		} else {
			$local_html = "登録されておりません。";
		}

		//	閲覧サーバー
		unset($remote_max);
		$remote_dir = REMOTE_MATERIAL_VOICE_DIR.$_POST['course_num']."/".$_POST['stage_num'].
						"/".$_POST['lesson_num']."/".$_POST['unit_num']."/".$_POST['block_num']."/";
		$FILE_NAME = remote_read_dir($_SESSION['select_web'],$remote_dir);
		$remote_max = count($FILE_NAME["f"]);
		if ($remote_max) {
			$REMOTE_TIME = $FILE_NAME;
			@rsort($REMOTE_TIME[f]);
			$remote_new_time = $REMOTE_TIME[f][0];
			$remote_html .= "最新更新時間：".date("Y/m/d H:i:s",$remote_new_time);	//	add :sookawara 2012/08/01
			$remote_html .= "<table class=\"course_form\">\n";
			$remote_html .= "<tr class=\"course_form_menu\">\n";
			$remote_html .= "<th>ファイル名</th>\n";
			$remote_html .= "<th>更新時間</th>\n";
			$remote_html .= "</tr>\n";
			@ksort($FILE_NAME["f"],SORT_STRING);
			foreach ($FILE_NAME["f"] AS $key => $val) {
				$remote_html .= "<tr class=\"course_form_cell\">\n";
				$remote_html .= "<td>".$key."</td>\n";
				$remote_html .= "<td>".date("Y/m/d H:i:s",$val)."</td>\n";	//	add :sookawara 2012/08/01
				$remote_html .= "</tr>\n";
			}
			$remote_html .= "</table>\n";
		} else {
			$remote_html = "登録されておりません。";
		}
	} else {
		$submit_db = "データ";
		//サーバー情報取得
		$sql  = "SELECT problem_num, display_problem_num, problem_type, form_type, display, update_time".
				" FROM ".T_PROBLEM.
				" WHERE state!='1' AND block_num='".$_POST['block_num']."'".
				" ORDER BY display_problem_num;";

		$sql2 = "SELECT update_time FROM ".T_PROBLEM.
				" WHERE block_num='".$_POST['block_num']."'".
				" ORDER BY update_time DESC LIMIT 1;";

		//	ローカルDB
		unset($local_max);
		if ($result = $cdb->query($sql)) {
			$local_max = $cdb->num_rows($result);
		}
		if ($local_max) {
			if ($result2 = $cdb->query($sql2)) {
				$list2 = $cdb->fetch_assoc($result2);
				$update_time = $list2['update_time'];
			}
			$local_html .= "最新更新時間：".$update_time;
			$local_html .= "<table class=\"course_form\">\n";
			$local_html .= "<tr class=\"course_form_menu\">\n";
			$local_html .= "<th>登録<br>番号</th>\n";
			$local_html .= "<th>表示<br>番号</th>\n";
			$local_html .= "<th>問題タイプ</th>\n";
			$local_html .= "<th>出題形式</th>\n";
			$local_html .= "<th>表示</th>\n";
			$local_html .= "<th>更新時間</th>\n";
			$local_html .= "</tr>\n";
			while ($list = $cdb->fetch_assoc($result)) {
				$local_html .= "<tr class=\"course_form_cell\">\n";
				$local_html .= "<td>".$list['problem_num']."</td>\n";
				$local_html .= "<td>".$list['display_problem_num']."</td>\n";
				$local_html .= "<td>".$L_PROBLEM_TYPE[$list['problem_type']]."</td>\n";
				$local_html .= "<td>".$L_FORM_TYPE[$list['form_type']]."</td>\n";
				$local_html .= "<td>".$L_DISPLAY[$list['display']]."</td>\n";
				$local_html .= "<td>".$list['update_time']."</td>\n";
				$local_html .= "</tr>\n";
			}
			$local_html .= "</table>\n";
		} else {
			$local_html = "登録されておりません。";
		}

		//	閲覧DB
		unset($remote_max);
		if ($result = $connect_db->query($sql)) {
			$remote_max = $connect_db->num_rows($result);
		}
		if ($remote_max) {
			if ($result2 = $connect_db->query($sql2)) {
				$list2 = $connect_db->fetch_assoc($result2);
				$update_time = $list2['update_time'];
			}
			$remote_html .= "最新更新時間：".$update_time;
			$remote_html .= "<table class=\"course_form\">\n";
			$remote_html .= "<tr class=\"course_form_menu\">\n";
			$remote_html .= "<th>登録<br>番号</th>\n";
			$remote_html .= "<th>登録<br>番号</th>\n";
			$remote_html .= "<th>表示<br>番号</th>\n";
			$remote_html .= "<th>問題タイプ</th>\n";
			$remote_html .= "<th>出題形式</th>\n";
			$remote_html .= "<th>更新時間</th>\n";
			$remote_html .= "</tr>\n";
			while ($list = $cdb->fetch_assoc($result)) {
				$remote_html .= "<tr class=\"course_form_cell\">\n";
				$remote_html .= "<td>".$list['problem_num']."</td>\n";
				$remote_html .= "<td>".$list['display_problem_num']."</td>\n";
				$remote_html .= "<td>".$L_PROBLEM_TYPE[$list['problem_type']]."</td>\n";
				$remote_html .= "<td>".$L_FORM_TYPE[$list['form_type']]."</td>\n";
				$remote_html .= "<td>".$L_DISPLAY[$list['display']]."</td>\n";
				$remote_html .= "<td>".$list['update_time']."</td>\n";
				$remote_html .= "</tr>\n";
			}
			$remote_html .= "</table>\n";
		} else {
			$remote_html = "登録されておりません。";
		}
	}

	if ($ERROR) {
		$html  = ERROR($ERROR);
		$html .= "<br>\n";
	}

	if ($local_html || $remote_html) {
		$submit_msg = $L_NAME['course_name']." / ".$L_NAME['stage_name']." / ".$L_NAME['lesson_name']." / ".$L_NAME['unit_name']." / ".$L_NAME['block_name']." の問題情報情報を検証へアップしますがよろしいですか？";

		$html .= "問題情報情報をアップする場合は、「アップする」ボタンを押してください。<br>\n";
		$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" name=\"del_form_all\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"update\">\n";
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$_POST['stage_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"".$_POST['unit_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"block_num\" value=\"".$_POST['block_num']."\">\n";
		$html .= "<input type=\"submit\" value=\"アップする\" onClick=\"return confirm('".$submit_msg."')\"><br>\n";
		$html .= "<table border=\"0\" cellspacing=\"1\" bgcolor=\"#666666\" cellpadding=\"3\">\n";
		$html .= "<tr bgcolor=\"#cccccc\">\n";
		$html .= "<th colspan=\"2\">".$L_NAME['course_name']." / ".$L_NAME['stage_name']." / ".$L_NAME['lesson_name']." / ".$L_NAME['unit_name']." / ".$L_NAME['block_name']."</th>\n";
		$html .= "</tr>\n";
		$html .= "<tr bgcolor=\"#cccccc\">\n";
		$html .= "<th colspan=\"2\">".$submit_db." / ".$submit_img." / ".$submit_voice."</th>\n";
		$html .= "</tr>\n";
		$html .= "<tr bgcolor=\"#cccccc\">\n";
		$html .= "<th>テストサーバー</th>\n";
		if (ACTION != "img" && ACTION != "voice") {
			$html .= "<th>".$_SESSION['select_db']['NAME']."</th>\n";
		} else {
			$html .= "<th>".$_SESSION['select_web']['NAME']."</th>\n";
		}
		$html .= "</tr>\n";
		$html .= "<tr valign=\"top\" bgcolor=\"#ffffff\">\n";
		$html .= "<td>\n";
		$html .= $local_html;
		$html .= "</td>\n";
		$html .= "<td>\n";
		$html .= $remote_html;
		$html .= "</td>\n";
		$html .= "</table>\n";
		$html .= "<input type=\"submit\" value=\"アップする\" onClick=\"return confirm('".$submit_msg."')\"><br>\n";
		$html .= "</form>\n";
	} else {
		$html .= "問題情報が設定されておりません。<br>\n";
	}

	//	閲覧DB切断
	$connect_db->close();

	return $html;
}


/**
 * 反映
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function update() {

	global $L_CONTENTS_DB;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$db_data = $L_CONTENTS_DB['92'];
	// $db_data['DBNAME'] = "SRLBS99"; // 2017/06/26 yoshizawa デバッグ用テーブル切り替え

	//	検証バッチDB接続
	$connect_db = new connect_db();
	// $connect_db->set_db($L_CONTENTS_DB['92']);
	$connect_db->set_db($db_data);
	$ERROR = $connect_db->set_connect_db();
	if ($ERROR) {
		$html .= ERROR($ERROR);
	}

	//	データーベース更新
	//	ローカル情報取得
	unset($LOCAL_PROBLEM);
	//del start kimura 2018/10/22 漢字学習コンテンツ_書写ドリル対応 //problemテーブルに変更があっても今後直さなくていいようにselect *に変更
	// $sql  = "SELECT problem_num, course_num, block_num, display_problem_num, problem_type,".
	// 		" sub_display_problem_num, form_type, question, problem, voice_data,".
	// 		" hint, explanation, answer_time, parameter, set_difficulty,".
	// 		" hint_number, correct_number, clear_number, first_problem, latter_problem,".
	// 		" selection_words, correct, option1, option2, option3,".
	// 		" option4, option5, sentence_flag, error_msg, update_time, display, state".	//	add sentence_flag ookawara 2012/12/27
	// 		" FROM ".T_PROBLEM.
	// 		" WHERE block_num='".$_POST['block_num']."';";
	//del end   kimura 2018/10/22 漢字学習コンテンツ_書写ドリル対応
	$sql = "SELECT * FROM ".T_PROBLEM." WHERE block_num = '".$_POST['block_num']."'"; //add kimura 2018/10/22 漢字学習コンテンツ_書写ドリル対応
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$problem_num = $list['problem_num'];
			if ($list) {
				foreach ($list AS $key => $val) {
					$LOCAL_PROBLEM[$problem_num][$key] = $val;
				}
			}
		}
	}
 	//kaopiz 2020/11/09 CR admin external start
	unset($LOCAL_PROBLEM_EXTERNAL);
	$sql = "SELECT * FROM external_problem WHERE block_num = '".$_POST['block_num']."'"; //add kimura 2018/10/22 漢字学習コンテンツ_書写ドリル対応
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$problem_num = $list['problem_num'];
			if ($list) {
				foreach ($list AS $key => $val) {
					$LOCAL_PROBLEM_EXTERNAL[$problem_num][$key] = $val;
				}
			}
		}
	}
	//kaopiz 2020/11/09 CR admin external end

	//	トランザクション開始
	$sql  = "BEGIN";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL BEGIN ERROR";
		$connect_db->close();
		return $ERROR;
	}

	//	検証DB情報取得
	unset($REMOTE_PROBLEM);
	$sql  = "SELECT problem_num FROM ".T_PROBLEM.
			" WHERE block_num='".$_POST['block_num']."';";
	if ($result = $connect_db->query($sql)) {
		while ($list = $connect_db->fetch_assoc($result)) {
			$problem_num = $list['problem_num'];
			$REMOTE_PROBLEM[$problem_num] = $problem_num;
		}
	}
	//kaopiz 2020/11/09 CR admin external start
	unset($REMOTE_PROBLEM_EXTERNAL);
	$sql  = "SELECT problem_num FROM external_problem WHERE block_num='".$_POST['block_num']."';";
	if ($result = $connect_db->query($sql)) {
		while ($list = $connect_db->fetch_assoc($result)) {
			$problem_num = $list['problem_num'];
			$REMOTE_PROBLEM_EXTERNAL[$problem_num] = $problem_num;
		}
	}
	//kaopiz 2020/11/09 CR admin external end

	// update start 2016/04/12 yoshizawa プラクティスアップデートエラー対応
	//$ERROR[] = "SQL DELETE ERROR<br>$sql";
	// トランザクション中は対象のレコードがロックします。
	// プラクティスアップデートが同時に実行された場合にはエラーメッセージを返します。
	global $L_TRANSACTION_ERROR_MESSAGE;
	$error_no = $connect_db->error_no_func();
	if($error_no == 1213){
		$ERROR[] = $L_TRANSACTION_ERROR_MESSAGE[$error_no];
		$connect_db->close();
		return $ERROR;
	}
	// update end 2016/04/12

	//	更新
	if ($LOCAL_PROBLEM) {
		unset($insert_name);
		unset($insert_value);
		// unset($check_problem_num); //del kimura 2018/10/23 漢字学習コンテンツ_書写ドリル対応
		$flag = 0;
		//del start kimura 2018/10/22 漢字学習コンテンツ_書写ドリル対応 //REPLACEに変更
		//foreach ($LOCAL_PROBLEM AS $problem_num => $VALUES) {
		//	if ($REMOTE_PROBLEM[$problem_num]) {	//	UPDATE
		//		unset($upddate_value);
		//		if ($VALUES) {
		//			foreach ($VALUES AS $key => $val) {
		//				if ($key == "problem_num") { continue; }
		//				if ($upddate_value) { $upddate_value .= ", "; }
		//				$upddate_value .= $key."='".addslashes($val)."'";
		//			}
		//			$sql = "UPDATE ".T_PROBLEM." SET ".$upddate_value.
		//						  " WHERE problem_num='".$problem_num."' LIMIT 1;\n";
		//			if (!$connect_db->exec_query($sql)) {
		//				$ERROR[] = "SQL UPDATE ERROR<br>$sql";
		//				$sql  = "ROLLBACK";
		//				if (!$connect_db->exec_query($sql)) {
		//					$ERROR[] = "SQL ROLLBACK ERROR";
		//				}
		//				$connect_db->close();
		//				return $ERROR;
		//			}
		//		}
		//	} elseif ($problem_num > 0) {	//	INSERT
		//		if ($VALUES) {
		//			unset($value);
		//			if ($insert_value) { $insert_value .= ", "; }
		//			if ($VALUES['problem_num']) {
		//				if ($check_problem_num) { $check_problem_num .= ", "; }
		//				$check_problem_num .= $VALUES['problem_num'];
		//			}
		//			foreach ($VALUES AS $key => $val) {
		//				if ($flag != 1) {
		//					if ($insert_name) { $insert_name .= ", "; }
		//					$insert_name .= $key;
		//				}
		//				if ($value) { $value .= ", "; }
		//				$value .= "'".addslashes($val)."'";
		//			}
		//			if ($value) {
		//				$insert_value .= "(".$value.")";
		//				$i++;
		//			}
		//			if ($insert_name) { $flag = 1; }
		//		}
		//		if ($i == 50) {
		//			$INSERT_VALUE[$num] = $insert_value;
		//			$num++;
		//			$i = 1;
		//			$insert_value = "";
		//		}
		//	}
		//	unset($REMOTE_PROBLEM[$problem_num]);
		//}
		//if ($insert_value) { $INSERT_VALUE[$num] = $insert_value; }
		//del end   kimura 2018/10/22 漢字学習コンテンツ_書写ドリル対応

		//add start kimura 2018/10/22 漢字学習コンテンツ_書写ドリル対応 {{{ //REPLACEに変更
		$num = 0;

		foreach ($LOCAL_PROBLEM AS $problem_num => $VALUES) {
			if ($VALUES) {
				unset($value);
				if ($insert_value) { $insert_value .= ", "; }
				//del start kimura 2018/10/23 漢字学習コンテンツ_書写ドリル対応 //REPLACEに変更したため、前もってDELETEする必要がなくなった
				// if ($VALUES['problem_num']) {
				// 	if ($check_problem_num) { $check_problem_num .= ", "; }
				// 	$check_problem_num .= $VALUES['problem_num'];
				// }
				//del end   kimura 2018/10/23 漢字学習コンテンツ_書写ドリル対応
				foreach ($VALUES AS $key => $val) {
					if ($flag != 1) {
						if ($insert_name) { $insert_name .= ", "; }
						$insert_name .= $key;
					}
					if ($value) { $value .= ", "; }
					$value .= "'".addslashes($val)."'";
				}
				if ($value) {
					$insert_value .= "(".$value.")";
					$i++;
				}
				if ($insert_name) { $flag = 1; }
			}
			if ($i == 50) {
				$INSERT_VALUE[$num] = $insert_value;
				$num++;
				$i = 1;
				$insert_value = "";
			}
			unset($REMOTE_PROBLEM[$problem_num]);
		}
		if ($insert_value) { $INSERT_VALUE[$num] = $insert_value; }
		//add end   kimura 2018/10/22 漢字学習コンテンツ_書写ドリル対応 }}}

		//	INSERT
		if ($insert_name && count($INSERT_VALUE)) {
			//del start kimura 2018/10/23 漢字学習コンテンツ_書写ドリル対応 //REPLACEに変更したため、前もってDELETEする必要がなくなった
			//	ゴミデーター削除
			// if ($check_problem_num) {
			// 	$sql  = "DELETE FROM ".T_PROBLEM.
			// 			" WHERE problem_num IN (".$check_problem_num.")".
			// 			" AND block_num!='".$_POST['block_num']."';";
			// 	if (!$connect_db->exec_query($sql)) {
			// 		$ERROR[] = "SQL DELETE ERROR<br>$sql";
			// 		$sql  = "ROLLBACK";
			// 		if (!$connect_db->exec_query($sql)) {
			// 			$ERROR[] = "SQL ROLLBACK ERROR";
			// 		}
			// 		$connect_db->close();
			// 		return $ERROR;
			// 	}
			// }
			//del end   kimura 2018/10/23 漢字学習コンテンツ_書写ドリル対応

			foreach ($INSERT_VALUE AS $values) {
				//update start kimura 2018/10/22 漢字学習コンテンツ_書写ドリル対応
				//$sql  = "INSERT INTO ".T_PROBLEM.
				$sql  = "REPLACE INTO ".T_PROBLEM.
				//update end   kimura 2018/10/22 漢字学習コンテンツ_書写ドリル対応
						" (".$insert_name.") ".
						" VALUES".$values.";";
				if (!$connect_db->exec_query($sql)) {
					$ERROR[] = "SQL INSERT ERROR<br>$sql";
					$sql  = "ROLLBACK";
					if (!$connect_db->exec_query($sql)) {
						$ERROR[] = "SQL ROLLBACK ERROR";
					}
					$connect_db->close();
					return $ERROR;
				}
			}
		}
	}

	//kaopiz 2020/11/09 CR admin external start
	if ($LOCAL_PROBLEM_EXTERNAL) {
		unset($insert_name);
		unset($insert_value);
		unset($INSERT_VALUE);
		$flag = 0;
		$num = 0;
		$i = 0;
		foreach ($LOCAL_PROBLEM_EXTERNAL AS $problem_num => $VALUES) {
			if ($VALUES) {
				unset($value);
				if ($insert_value) { $insert_value .= ", "; }
				foreach ($VALUES AS $key => $val) {
					if ($flag != 1) {
						if ($insert_name) { $insert_name .= ", "; }
						$insert_name .= $key;
					}
					if ($value) { $value .= ", "; }
					$value .= "'".addslashes($val)."'";
				}
				if ($value) {
					$insert_value .= "(".$value.")";
					$i++;
				}
				if ($insert_name) { $flag = 1; }
			}
			if ($i == 50) {
				$INSERT_VALUE[$num] = $insert_value;
				$num++;
				$i = 1;
				$insert_value = "";
			}
			unset($REMOTE_PROBLEM_EXTERNAL[$problem_num]);
		}
		if ($insert_value) { $INSERT_VALUE[$num] = $insert_value; }

		//	INSERT
		if ($insert_name && count($INSERT_VALUE)) {
			foreach ($INSERT_VALUE AS $values) {
				//update start kimura 2018/10/22 漢字学習コンテンツ_書写ドリル対応
				//$sql  = "INSERT INTO ".T_PROBLEM.
				$sql  = "REPLACE INTO external_problem (".$insert_name.") ".
					" VALUES".$values.";";
				if (!$connect_db->exec_query($sql)) {
					$ERROR[] = "SQL INSERT ERROR<br>$sql";
					$sql  = "ROLLBACK";
					if (!$connect_db->exec_query($sql)) {
						$ERROR[] = "SQL ROLLBACK ERROR";
					}
					$connect_db->close();
					return $ERROR;
				}
			}
		}
	}
	//kaopiz 2020/11/09 CR admin external end

	//	削除
	if ($REMOTE_PROBLEM) {
		unset($del_problem_num);
		foreach ($REMOTE_PROBLEM AS $problem_num => $val) {
			if ($problem_num > 0) {
				if ($del_problem_num) { $del_problem_num .= ", "; }
				$del_problem_num .= $problem_num;
			}
		}
		if ($del_problem_num) {
			$sql  = "UPDATE ".T_PROBLEM." SET".
					" display='2',".
					" state='1',".
					" update_time=now()".
					" WHERE problem_num IN (".$del_problem_num.");";
			if (!$connect_db->exec_query($sql)) {
				$ERROR[] = "SQL INSERT ERROR<br>$sql";
				if (!$connect_db->exec_query($sql)) {
					$ERROR[] = "SQL ROLLBACK ERROR";
				}
				$connect_db->close();
				return $ERROR;
			}
		}
	}

	//kaopiz 2020/11/09 CR admin external start
	if ($REMOTE_PROBLEM_EXTERNAL) {
		unset($del_problem_num);
		foreach ($REMOTE_PROBLEM_EXTERNAL AS $problem_num => $val) {
			if ($problem_num > 0) {
				if ($del_problem_num) { $del_problem_num .= ", "; }
				$del_problem_num .= $problem_num;
			}
		}
		if ($del_problem_num) {
			$sql  = "UPDATE external_problem SET".
				" display_problem_num='2',".
				" upd_date=now()".
				" WHERE problem_num IN (".$del_problem_num.");";
			if (!$connect_db->exec_query($sql)) {
				$ERROR[] = "SQL INSERT ERROR<br>$sql";
				if (!$connect_db->exec_query($sql)) {
					$ERROR[] = "SQL ROLLBACK ERROR";
				}
				$connect_db->close();
				return $ERROR;
			}
		}
	}
	//kaopiz 2020/11/09 CR admin external end

	//	トランザクションコミット
	$sql  = "COMMIT";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL COMMIT ERROR";
		$connect_db->close();
		return $ERROR;
	}

	//	テーブル最適化	負荷がかかる為調査後利用するか決める。
	$sql = "OPTIMIZE TABLE ".T_PROBLEM.";";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
	}

	//	検証バッチDB切断
	$connect_db->close();

	//	ファイルアップ

	//	問題音声ファイル
	$local_dir = BASE_DIR."/www".ereg_replace("^../","/",MATERIAL_VOICE_DIR).$_POST['course_num']."/".$_POST['stage_num']."/".$_POST['lesson_num']."/".$_POST['unit_num']."/".$_POST['block_num'];
	$remote_dir = KBAT_DIR.REMOTE_MATERIAL_VOICE_DIR.$_POST['course_num']."/".$_POST['stage_num']."/".$_POST['lesson_num']."/".$_POST['unit_num']."/";

	//$command1 = "ssh suralacore01@srlbtw21 mkdir -p ".$remote_dir; // del 2018/03/20 yoshizawa AWSプラクティスアップデート
	//$command2 = "scp -rp ".$local_dir." suralacore01@srlbtw21:".$remote_dir; // del 2018/03/20 yoshizawa AWSプラクティスアップデート
	$command1 = "mkdir -p ".$remote_dir; // add 2018/03/20 yoshizawa AWSプラクティスアップデート
	$command2 = "cp -rp ".$local_dir." ".$remote_dir; // add 2018/03/20 yoshizawa AWSプラクティスアップデート

	//upd start 2017/11/27 yamaguchi AWS移設
	//exec("$command1",&$LIST);
	//exec("$command2",&$LIST);
	exec("$command1",$LIST);
	exec("$command2",$LIST);
	//upd end 2017/11/27 yamaguchi

	//	問題画像ファイル
	$local_dir = BASE_DIR."/www".ereg_replace("^../","/",MATERIAL_PROB_DIR).$_POST['course_num']."/".$_POST['stage_num']."/".$_POST['lesson_num']."/".$_POST['unit_num']."/".$_POST['block_num'];
	$remote_dir = KBAT_DIR.REMOTE_MATERIAL_PROB_DIR.$_POST['course_num']."/".$_POST['stage_num']."/".$_POST['lesson_num']."/".$_POST['unit_num']."/";

	//$command1 = "ssh suralacore01@srlbtw21 mkdir -p ".$remote_dir; // del 2018/03/20 yoshizawa AWSプラクティスアップデート
	//$command2 = "scp -rp ".$local_dir." suralacore01@srlbtw21:".$remote_dir; // del 2018/03/20 yoshizawa AWSプラクティスアップデート
	$command1 = "mkdir -p ".$remote_dir; // add 2018/03/20 yoshizawa AWSプラクティスアップデート
	$command2 = "cp -rp ".$local_dir." ".$remote_dir; // add 2018/03/20 yoshizawa AWSプラクティスアップデート

	//upd start 2017/11/27 yamaguchi AWS移設
	//exec("$command1",&$LIST);
	//exec("$command2",&$LIST);
	exec("$command1",$LIST);
	exec("$command2",$LIST);
	//upd end 2017/11/27 yamaguchi


	//	検証バッチから検証webへ
	// $command = "ssh suralacore01@srlbtw21 ./CONTENTSUP.cgi '2' '".MODE."' '".$_POST['course_num']."' '".$_POST['stage_num']."' '".$_POST['lesson_num']."' '".$_POST['unit_num']."' '".$_POST['block_num']."'"; // del 2018/03/20 yoshizawa AWSプラクティスアップデート
	$command = "/usr/bin/php ".BASE_DIR."/_www/batch/CONTENTSUP.cgi '2' '".MODE."' '".$_POST['course_num']."' '".$_POST['stage_num']."' '".$_POST['lesson_num']."' '".$_POST['unit_num']."' '".$_POST['block_num']."'"; // add 2018/03/20 yoshizawa AWSプラクティスアップデート

	//upd start 2017/11/27 yamaguchi AWS移設
	//exec($command,&$LIST);
	exec($command,$LIST);
	//upd end 2017/11/27 yamaguchi

	//	ログ保存 --
	unset($mate_upd_log_num);
	$sql  = "SELECT mate_upd_log_num FROM mate_upd_log".
			" WHERE update_mode='".MODE."'".
			" AND course_num='".$_POST['course_num']."'".
			" AND stage_num='".$_POST['stage_num']."'".
			" AND lesson_num='".$_POST['lesson_num']."'".
			" AND unit_num='".$_POST['unit_num']."'".
			" AND block_num='".$_POST['block_num']."'".
			" AND state='1'".
			" ORDER BY regist_time DESC LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$mate_upd_log_num = $list['mate_upd_log_num'];
	}

	if ($mate_upd_log_num) {
		unset($INSERT_DATA);
		$INSERT_DATA['regist_time'] = "now()";
		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
		$where = " WHERE mate_upd_log_num='".$mate_upd_log_num."'";

		$ERROR = $cdb->update('mate_upd_log',$INSERT_DATA,$where);
	} else {
		unset($INSERT_DATA);
		$INSERT_DATA['update_mode'] = MODE;
		$INSERT_DATA['course_num'] = $_POST['course_num'];
		$INSERT_DATA['stage_num'] = $_POST['stage_num'];
		$INSERT_DATA['lesson_num'] = $_POST['lesson_num'];
		$INSERT_DATA['unit_num'] = $_POST['unit_num'];
		$INSERT_DATA['block_num'] = $_POST['block_num'];
		$INSERT_DATA['regist_time'] = "now()";
		$INSERT_DATA['state'] = 1;
		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];

		$ERROR = $cdb->insert('mate_upd_log',$INSERT_DATA);
	}

	return $ERROR;
}


/**
 * 反映終了
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $L_NAME
 * @return string HTML
 */
function update_end_html($L_NAME) {

	$html  = $L_NAME['course_name']." / ".$L_NAME['stage_name']." / ".$L_NAME['lesson_name']." / ".$L_NAME['unit_name']." / ".$L_NAME['block_name']."の問題情報情報のアップが完了致しました。<br>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$_POST['stage_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"".$_POST['unit_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"block_num\" value=\"".$_POST['block_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"back\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\"><br>\n";
	$html .= "</form>\n";

	return $html;
}
?>
