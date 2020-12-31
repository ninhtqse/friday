<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * テスト用プラクティスステージ管理
 * 	プラクティスアップデートプログラム
 * 		すらら英単語種別1 アップデート
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
		select_database($ERROR);
	} elseif (ACTION == "view_session") {
		view_set_session();
	} elseif (ACTION == "") {
		unset($_SESSION['view_session']);
	}

	if (!$ERROR && ACTION == "update") {
		$html = update_end_html();
	} else {
		$html = select_unit_view($ERROR);
	}

	return $html;
}


/**
 * テスト種類選択
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function select_unit_view($ERROR) {

	$L_TEST_TYPE = array();
	$L_TEST_TYPE = get_test_type(); // ms_test_typeのレコードを取得

	$html = "";

	if ($ERROR) {
		$html .= ERROR($ERROR);
		$html .= "<br>\n";
	}

	// テスト種類
	$test_type_num_html  = "";
	$test_type_num_html .= "<option value=\"0\">選択して下さい</option>\n";
	foreach ($L_TEST_TYPE AS $test_type_num => $L_TEST_TYPE_INFO) {

		if ($L_TEST_TYPE_INFO['test_type_name'] == "") {
			continue;
		}

		$selected = "";
		if ($_SESSION['view_session']['test_type_num'] == $test_type_num) {
			$selected = "selected";
		}
		$test_type_num_html .= "<option value=\"".$test_type_num."\" ".$selected.">".$L_TEST_TYPE_INFO['test_type_name']."</option>\n";
	}

	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"menu\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"view_session\">\n";
	$html .= "<table class=\"unit_form\">\n";
	$html .= "<tr class=\"unit_form_menu\">\n";
	$html .= "<td>テスト種類</td>\n";
	$html .= "</tr>\n";
	$html .= "<tr class=\"unit_form_cell\">\n";
	$html .= "<td>\n";
	$html .= "<select name=\"test_type_num\" onchange=\"submit();\">\n".$test_type_num_html."</select>\n";
	$html .= "</td>\n";
	$html .= "</tr>\n";
	$html .= "</table>\n";
	$html .= "</form>\n";
	$html .= "<br />\n";

	if (!$_SESSION['view_session']['test_type_num']) {
		$html .= "テスト種類を選択してください。<br>\n";
		$html .= "<br />\n";
	} else {
		$html .= default_html($ERROR);
	}

	return $html;
}


/**
 * 選択セッションセット
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function view_set_session() {

	unset($_SESSION['view_session']);

	if ($_POST['test_type_num'] != "") {
		$_SESSION['view_session']['test_type_num'] = $_POST['test_type_num'];
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
function get_test_type() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$L_TEST_TYPE = array();

	// 本番反映後に取り下げる可能性があるので、削除フラグは条件に入れない。
	$sql = "SELECT * FROM ".T_MS_TEST_TYPE." WHERE mk_flg = '0' ORDER BY list_num ASC ;";

	if ($result = $cdb->query($sql)) {
		while($list = $cdb->fetch_assoc($result)) {
			$L_TEST_TYPE[$list['test_type_num']] = $list;
		}
	}

	return $L_TEST_TYPE;

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
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"".$_POST['mode']."\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"db_session\">\n";
	$html .= "<input type=\"hidden\" name=\"test_type_num\" value=\"".$_POST['test_type_num']."\">\n";
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
	$sql  = "SELECT MAX(ms_test_category1.upd_date) AS upd_date FROM " . T_MS_TEST_CATEGORY1 . " ms_test_category1" .
			" WHERE ms_test_category1.test_type_num='".$_SESSION['view_session']['test_type_num']."';";
	$sql_cnt  = "SELECT DISTINCT ms_test_category1.* FROM " . T_MS_TEST_CATEGORY1 . " ms_test_category1" .
				" WHERE ms_test_category1.test_type_num='".$_SESSION['view_session']['test_type_num']."';";

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
		$submit_msg = "すらら英単語種別1情報を検証へアップしますがよろしいですか？";

		$html .= "すらら英単語種別1情報をアップする場合は、「アップする」ボタンを押してください。<br>\n";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
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
		$html .= "すらら英単語種別1情報が設定されておりません。<br>\n";
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
 * @param array エラーの場合
 */
function update(&$ERROR) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_CONTENTS_DB;

	//	検証バッチDB接続
	$connect_db = new connect_db();
	$connect_db->set_db($L_CONTENTS_DB['92']); // 10.3.11.100の場合は'SRLBS99'
	$ERROR = $connect_db->set_connect_db();
	if ($ERROR) {
		$html .= ERROR($ERROR);
	}

	//	データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();

	//	更新情報クエリー
	$sql  = "SELECT * FROM ".T_MS_TEST_CATEGORY1.
			" WHERE test_type_num='".$_SESSION['view_session']['test_type_num']."';";
	if ($result = $cdb->query($sql)) {
		make_insert_query($result, T_MS_TEST_CATEGORY1, $INSERT_NAME, $INSERT_VALUE);
	}

	//	検証バッチDBデーター削除クエリー
	$sql  = "DELETE FROM ".T_MS_TEST_CATEGORY1.
			" WHERE test_type_num='".$_SESSION['view_session']['test_type_num']."';";
	$DELETE_SQL[] = $sql;

	//	トランザクション開始
	$sql  = "BEGIN";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL BEGIN ERROR";
		$connect_db->close();
		return ;
	}

	//	外部キー制約解除
	$sql  = "SET FOREIGN_KEY_CHECKS=0;";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL FOREIGN_KEY_CHECKS = 0 ERROR<br>$sql<br>$update_server_name";
		$sql  = "ROLLBACK";
		if (!$connect_db->exec_query($sql)) {
			$ERROR[] = "SQL ROLLBACK ERROR";
		}
		$connect_db->close();
		return $ERROR;
	}

	if ($DELETE_SQL) {
		$err_flg = 0;
		foreach ($DELETE_SQL AS $sql) {
			if (!$connect_db->exec_query($sql)) {
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

	//	外部キー制約設定
	$sql  = "SET FOREIGN_KEY_CHECKS=1;";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL FOREIGN_KEY_CHECKS = 1 ERROR<br>$sql<br>$update_server_name";
		$sql  = "ROLLBACK";
		if (!$connect_db->exec_query($sql)) {
			$ERROR[] = "SQL ROLLBACK ERROR";
		}
		$connect_db->close();
		return $ERROR;
	}

	//	トランザクションコミット
	$sql  = "COMMIT";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL COMMIT ERROR";
		$connect_db->close();
		return ;
	}

	//	テーブル最適化
	$sql = "OPTIMIZE TABLE ".T_MS_TEST_CATEGORY1.";";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
	}

	//	検証バッチDB切断
	$connect_db->close();

	//	ファイルアップ
	$write_type = "";
	$sql = "SELECT * FROM ".T_MS_TEST_TYPE." WHERE test_type_num = '".$_SESSION['view_session']['test_type_num']."';";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$write_type = $list['write_type'];
	}
	//	$write_typeがあればスタイルシートをアップ
	if($write_type > 0){
		// /data/home/www/material/template/vocabulary/(write_type)/
		$local_dir = BASE_DIR."/www".preg_replace("/^..\\//","/",MATERIAL_TEMP_DIR)."vocabulary/".$write_type;
		// /data/apprelease/Release/Contents/www/material/template/vocabulary/
		$remote_dir = KBAT_DIR.REMOTE_MATERIAL_TEMP_DIR."vocabulary/";

		$command1 = "mkdir -p ".$remote_dir;
		$command2 = "cp -rp ".$local_dir." ".$remote_dir;

		exec("$command1",$LIST);
		exec("$command2",$LIST);
	}

	//	検証バッチから検証webへ
	$send_data = " '".$_SESSION['view_session']['test_type_num']."' '".$write_type."'";
	$command = "/usr/bin/php ".BASE_DIR."/_www/batch/TESTCONTENTSUP.cgi '2' 'test_".MODE."'".$send_data;
	exec($command,$LIST);

	//	ログ保存 --
	$test_mate_upd_log_num = "";
	$SEND_DATA_LOG = $_SESSION['view_session'];
	$SEND_DATA_LOG['write_type'] = $write_type; // 検証バッチからファイルアップするために情報を保持します。
	$send_data_log = serialize($SEND_DATA_LOG);
	$send_data_log = addslashes($send_data_log);
	$sql  = "SELECT test_mate_upd_log_num FROM ".T_TEST_MATE_UPD_LOG.
			" WHERE update_mode='".MODE."'".
			" AND state='1'".
			" AND course_num='".$_SESSION['view_session']['test_type_num']."'".
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
		$INSERT_DATA['course_num'] = $_SESSION['view_session']['test_type_num'];
		$INSERT_DATA['stage_num'] = $write_type;
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

	$html  = "すらら英単語種別1情報のアップが完了致しました。<br>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"back\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\"><br>\n";
	$html .= "</form>\n";

	return $html;
}
?>
