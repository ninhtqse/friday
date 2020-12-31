<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * テスト用プラクティスステージ管理
 * 	プラクティスアップデートプログラム
 * 		小テスト回答目安時間 アップデート
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
function action() {
	global $L_TEST_UPDATE_MODE;

	$html = "";

	if (ACTION == "update") {
		update($ERROR);
	} elseif (ACTION == "db_session") {
		select_database();
	} elseif (ACTION == "view_session") {
		view_set_session();
	} elseif (ACTION == "") {
		unset($_SESSION['view_session']);
	}

	if (!$ERROR && ACTION == "update") {
		$html .= update_end_html();
	} else {
		$html .= select_unit_view($ERROR);
	}

	return $html;
}


/**
 * コース
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function select_unit_view($ERROR) {
	global $L_WRITE_TYPE;

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];//add hirose 2020/07/15 学習管理画面リニューアル　その他サービスの小テスト対応

	$html = "";

	if ($ERROR) {
		$html .= ERROR($ERROR);
		$html .= "<br>\n";
	}

	//コース
	$course_count = "";
	$couse_html  = "";
	$couse_html .= "<option value=\"0\">選択して下さい</option>\n";
	//upd start hirose 2020/07/15 学習管理画面リニューアル　その他サービスの小テスト対応
// 	foreach ($L_WRITE_TYPE AS $course_num => $course_name) {

// 		// del start oda 2020/02/27 理社対応
// // 		// 理科社会プラクティスアップ無効化
// // 		if($course_num == 15 || $course_num == 16){ continue; } // add 2018/05/18 yoshizawa 理科社会対応
// 		// del end oda 2020/02/27 理社対応

// 		if ($course_name == "") {
// 			continue;
// 		}
// 		$selected = "";
// 		if ($_SESSION['view_session']['course_num'] == $course_num) {
// 			$selected = "selected";
// 		}
// 		$couse_html .= "<option value=\"".$course_num."\" ".$selected.">".$course_name."</option>\n";
// 	}

	// //add start kimura 2019/09/19 漢字学習コンテンツFB対応 {{{
	// 	$kanji_course = get_course_names_by_service(array("15"));
	// 	if(!empty($kanji_course["15"])){
	// 		foreach($kanji_course['15'] as $course_num => $course_name){
	// 			$couse_html .= "<option value=\"".$course_num."\"".($_SESSION['view_session']['course_num'] == $course_num ? "selected" : "").">".$course_name."</option>\n";
	// 		}
	// 	}
	// //add end   kimura 2019/09/19 漢字学習コンテンツFB対応 }}}

	$sql = " SELECT c.course_num, c.course_name";
	$sql.= " FROM ".T_SERVICE." s";
	$sql.= " INNER JOIN ".T_SERVICE_COURSE_LIST." sc ON s.service_num = sc.service_num";
	$sql.= " INNER JOIN ".T_COURSE." c ON sc.course_num = c.course_num";
	$sql.= " WHERE s.mk_flg = 0";
	$sql.= " AND sc.mk_flg = 0";
	$sql.= " AND s.setup_type = 1";
	$sql.= " AND(";
	$sql.= "  (s.setup_type_sub = 1 AND sc.course_type = 1)";
	$sql.= "  OR (s.setup_type_sub = 2 AND sc.course_type = 1)";
	$sql.= " )";

	if ($result = $cdb->query($sql)) {
		while($list = $cdb->fetch_assoc($result)){
			$couse_html .= "<option value=\"".$list['course_num']."\"".($_SESSION['view_session']['course_num'] == $list['course_num'] ? "selected" : "").">".$list['course_name']."</option>\n";
		}
	}
	//upd end hirose 2020/07/15 学習管理画面リニューアル　その他サービスの小テスト対応

	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"menu\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"view_session\">\n";
	$html .= "<table class=\"unit_form\">\n";
	$html .= "<tr class=\"unit_form_menu\">\n";
	$html .= "<td>コース</td>\n";
	$html .= "</tr>\n";
	$html .= "<tr class=\"unit_form_cell\">\n";
	$html .= "<td>\n";
	$html .= "<select name=\"course_num\" onchange=\"submit();\">\n".$couse_html."</select>\n";
	$html .= "</td>\n";
	$html .= "</tr>\n";
	$html .= "</table>\n";
	$html .= "</form>\n";
	$html .= "<br />\n";

	if (!$_SESSION['view_session']['course_num']) {
		$html .= "小テスト回答目安時間を設定するコースを選択してください。<br>\n";		// update oda 2016/11/11 課題要望一覧No566 文言修正
		$html .= "<br />\n";
	} else {
		$html .= default_html($ERROR);
	}

	return $html;
}



/**
 * コース、ステージ、Lesson、ユニット選択セッションセット
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function view_set_session() {

	$course_num = $_SESSION['view_session']['course_num'];
	unset($_SESSION['view_session']);

	if ($_POST['course_num'] != "") {
		$_SESSION['view_session']['course_num'] = $_POST['course_num'];
	} else {
		return;
	}

}


/**
 * デフォルトページ
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function default_html($ERROR) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$html = "";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"".$_POST['mode']."\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"db_session\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
	$html .= select_db_menu();
	$html .= "</form>\n";
	$html .= "<br />\n";

	unset($BASE_DATA);
	unset($MAIN_DATA);
	//サーバー情報取得
	if (!$_SESSION['select_db']) { return $html; }

	//	閲覧DB接続
	$connect_db = new connect_db();
	$connect_db->set_db($_SESSION['select_db']);
	$ERROR = $connect_db->set_connect_db();
	if ($ERROR) {
		$html .= ERROR($ERROR);
	}

	//	情報取得クエリー
	$sql  = "SELECT MAX(ms_problem_attribute.upd_date) AS upd_date FROM ".T_MS_PROBLEM_ATTRIBUTE." ms_problem_attribute".
			" WHERE ms_problem_attribute.course_num='".$_SESSION['view_session']['course_num']."'".
			";";
	$sql_cnt  = "SELECT DISTINCT ms_problem_attribute.* FROM ".T_MS_PROBLEM_ATTRIBUTE." ms_problem_attribute".
				" WHERE ms_problem_attribute.course_num='".$_SESSION['view_session']['course_num']."'".
				";";

	//	ローカルサーバー
	$local_html = "";
	$local_time = "";
	$cnt = 0;
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$local_time = $list['upd_date'];
	}
	if ($result = $cdb->query($sql_cnt)) {
		$cnt = $cdb->num_rows($result);
	}
	if ($local_time) {
		$local_html = $local_time." (".$cnt.")";
	} else {
		$local_html = "データーがありません。";
	}

	// -- 閲覧DB
	$remote_html = "";
	$remote_time = "";
	$cnt = 0;
	if ($result = $connect_db->query($sql)) {
		$list = $connect_db->fetch_assoc($result);
		$remote_time = $list['upd_date'];
	}
	if ($result = $connect_db->query($sql_cnt)) {
		$cnt = $connect_db->num_rows($result);
	}
	if ($remote_time) {
		$remote_html = $remote_time." (".$cnt.")";
	} else {
		$remote_html = "データーがありません。";
	}

	if ($local_time || $remote_time) {
		$submit_msg = "小テスト回答目安時間情報を検証へアップしますがよろしいですか？";								// update oda 2016/11/11 課題要望一覧No566 文言修正

		$html .= "小テスト回答目安時間情報をアップする場合は、「アップする」ボタンを押してください。<br>\n";		// update oda 2016/11/11 課題要望一覧No566 文言修正
		$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"update\">\n";
		$html .= "<input type=\"submit\" value=\"アップする\" onClick=\"return confirm('".$submit_msg."')\"><br>\n";
		$html .= "<table border=\"0\" cellspacing=\"1\" bgcolor=\"#666666\" cellpadding=\"3\">\n";
		$html .= "<tr bgcolor=\"#cccccc\">\n";
		$html .= "<th>テストサーバー最新更新日</th>\n";
		$html .= "<th>".$_SESSION['select_db']['NAME']."最新更新日</th>\n";
		$html .= "</tr>\n";
		$html .= "<tr valign=\"top\" bgcolor=\"#ffffff\" align=\"center\">\n";
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
		$html .= "小テスト回答目安時間情報が設定されておりません。<br>\n";											// update oda 2016/11/11 課題要望一覧No566 文言修正
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
 * @param array &$ERROR
 */
function update(&$ERROR) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_CONTENTS_DB;

	//	検証バッチDB接続
	$connect_db = new connect_db();
	$connect_db->set_db($L_CONTENTS_DB['92']);
	$ERROR = $connect_db->set_connect_db();
	if ($ERROR) {
		$html .= ERROR($ERROR);
	}

	//	データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();

	//	更新情報クエリー
	$sql  = "SELECT * FROM ".T_MS_PROBLEM_ATTRIBUTE.
			" WHERE course_num='".$_SESSION['view_session']['course_num']."'".
			";";
	if ($result = $cdb->query($sql)) {
		make_insert_query($result, T_MS_PROBLEM_ATTRIBUTE, $INSERT_NAME, $INSERT_VALUE);
	}

	//	検証バッチDBデーター削除クエリー
	$sql  = "DELETE FROM ".T_MS_PROBLEM_ATTRIBUTE.
			" WHERE course_num='".$_SESSION['view_session']['course_num']."'".
			";";
	$DELETE_SQL[] = $sql;

	//	トランザクション開始
	$sql  = "BEGIN";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL BEGIN ERROR";
		$connect_db->close();
		return ;
	}

	if ($DELETE_SQL) {
		$err_flg = 0;
		foreach ($DELETE_SQL AS $sql) {
			if (!$connect_db->exec_query($sql)) {
				// update start 2016/04/12 yoshizawa プラクティスアップデートエラー対応
				//$ERROR[] = "SQL DELETE ERROR<br>$sql";
				// トランザクション中は対象のレコードがロックします。
				// プラクティスアップデートが同時に実行された場合にはエラーメッセージを返します。
				global $L_TRANSACTION_ERROR_MESSAGE;
				$error_no = $connect_db->error_no_func();
				if($error_no == 1213){
					$ERROR[] = $L_TRANSACTION_ERROR_MESSAGE[$error_no];
				} else {
					$ERROR[] = "SQL DELETE ERROR<br>$sql";
				}
				// update end 2016/04/12
				$err_flg = 1;
			}
		}
		if ($err_flg == 1) {
			$sql  = "ROLLBACK";
			if (!$connect_db->exec_query($sql)) {
				$ERROR[] = "SQL ROLLBACK ERROR";
			}
			$connect_db->close();
			return $ERROR;
		}
	}

	//	検証バッチDBデーター追加
	if (count($INSERT_NAME) && count($INSERT_VALUE)) {
		foreach ($INSERT_NAME AS $table_name => $insert_name) {
			if ($INSERT_VALUE[$table_name]) {
				foreach ($INSERT_VALUE[$table_name] AS $values) {
					$sql  = "INSERT INTO ".$table_name.
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
	}

	//	トランザクションコミット
	$sql  = "COMMIT";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL COMMIT ERROR";
		$connect_db->close();
		return ;
	}

	//	テーブル最適化
	$sql = "OPTIMIZE TABLE ".T_MS_PROBLEM_ATTRIBUTE.";";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
	}

	//	検証バッチDB切断
	$connect_db->close();

	//	検証バッチから検証webへ
	$send_data  = " '".$_SESSION['view_session']['course_num']."'";
	// $command = "ssh suralacore01@srlbtw21 ./TESTCONTENTSUP.cgi '2' 'test_".MODE."'".$send_data; // del 2018/03/20 yoshizawa AWSプラクティスアップデート
	$command = "/usr/bin/php ".BASE_DIR."/_www/batch/TESTCONTENTSUP.cgi '2' 'test_".MODE."'".$send_data; // add 2018/03/20 yoshizawa AWSプラクティスアップデート

	//upd start 2017/11/27 yamaguchi AWS移設
	//exec($command,&$LIST);
	exec($command,$LIST);
	//upd end 2017/11/27 yamaguchi

	//	ログ保存 --
	$test_mate_upd_log_num = "";
	$SEND_DATA_LOG = $_SESSION['view_session'];
	$send_data_log = serialize($SEND_DATA_LOG);
	$send_data_log = addslashes($send_data_log);
	$sql  = "SELECT test_mate_upd_log_num FROM ".T_TEST_MATE_UPD_LOG.
			" WHERE update_mode='".MODE."'".
			" AND state='1'".
			" AND course_num='".$_SESSION['view_session']['course_num']."'".
			" AND send_data='".$send_data_log."'".
			" ORDER BY regist_time DESC".
			" LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$test_mate_upd_log_num = $list['test_mate_upd_log_num'];
	}

	if ($test_mate_upd_log_num) {
		unset($INSERT_DATA);
		$INSERT_DATA['state'] = 1;
		$INSERT_DATA['regist_time'] = "now()";
		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
		$where = " WHERE test_mate_upd_log_num='".$test_mate_upd_log_num."'";

		$ERROR = $cdb->update(T_TEST_MATE_UPD_LOG, $INSERT_DATA,$where);
	} else {
		unset($INSERT_DATA);
		$INSERT_DATA['update_mode'] = MODE;
		$INSERT_DATA['course_num'] = $_SESSION['view_session']['course_num'];
		$INSERT_DATA['send_data'] = $send_data_log;
		$INSERT_DATA['regist_time'] = "now()";
		$INSERT_DATA['state'] = 1;
		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];

		$ERROR = $cdb->insert(T_TEST_MATE_UPD_LOG, $INSERT_DATA);
	}

}


/**
 * 反映終了
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function update_end_html() {

	$html  = "小テスト回答目安時間情報のアップが完了致しました。<br>\n";										// update oda 2016/11/11 課題要望一覧No566 文言修正
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"back\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\"><br>\n";
	$html .= "</form>\n";

	return $html;
}
?>
