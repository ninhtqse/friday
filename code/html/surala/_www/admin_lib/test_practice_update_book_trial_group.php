<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * テスト用プラクティスステージ管理
 * 	プラクティスアップデートプログラム
 * 		学力診断テストグループ アップデート
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
 * 学年選択
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function select_unit_view($ERROR) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_GKNN_LIST;

	$html = "";

	if ($ERROR) {
		$html .= ERROR($ERROR);
		$html .= "<br>\n";
	}

	if (ACTION == "") {
		$test_group_id_count = 0;
		$sql  = "SELECT * FROM ".T_MS_BOOK_GROUP.
				" WHERE mk_flg='0'".
				// " AND srvc_cd = 'GTEST';"; // add yoshizawa 2015/09/29 02_作業要件/34_数学検定      // del 2020/09/14 thanh テスト標準化開発
				" AND class_id = '';";	// add 2020/09/14 thanh テスト標準化開発
		if ($result = $cdb->query($sql)) {
			$test_group_id_count = $cdb->num_rows($result);
		}
		if (!$test_group_id_count) {
			$html .= "グループが存在しません。設定してからご利用下さい。";
			return $html;
		}
	}

	$L_GKNN_LIST['0'] = "全て";

	//	検証中データー取得
	$PMUL = array();
	$send_data = "";
	$sql  = "SELECT send_data".
			" FROM ".T_TEST_MATE_UPD_LOG.
			" WHERE update_mode='".MODE."'".
			" AND state='1';";
	if ($result = $cdb->query($sql)) {
		while ($list=$cdb->fetch_assoc($result)) {
			$send_data = $list['send_data'];
			$VALUES = unserialize($send_data);
			$gknn = $VALUES['gknn'];
			$test_group_id = $VALUES['test_group_id'];

			if ($gknn === "0") {
				$PMUL[$gknn] = 1;
				$L_GKNN_LIST['0'] = "全て（アップデート中の為、他を選択出来ません）";
			} elseif ($gknn != "" && ($test_group_id == "0" || $test_group_id == "")) {
				$PMUL[$gknn] = 1;
			}
		}
	}

	//学年
	$gknn_html = "";
	foreach($L_GKNN_LIST as $key => $val) {
		if ($PMUL[0] == 1 && $key != "0" && $key != "") {
			continue;
		}

		$selected = "";
		if ($_SESSION['view_session']['gknn'] == $key) {
			$selected = "selected";
		}
		$gknn_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
	}

	$last_select_flg = 0;
	$gknn = $_SESSION['view_session']['gknn'];
	if ($PMUL[$gknn] == 1 && ($gknn === "0" || $gknn != "")) {
		$last_select_flg = 1;
	}

	//グループ名
	$test_group_id_html = "";
	if ($_SESSION['view_session']['gknn'] != "0" && $_SESSION['view_session']['gknn'] != "") {
		if ($last_select_flg == 1) {
			$test_group_id_html .= "<option value=\"\">アップデート中の為選択出来ません</option>\n";
		} else {
			$test_group_id_count = 0;
			$sql  = "SELECT * FROM ".T_MS_BOOK_GROUP.
					" WHERE mk_flg='0'".
					// " AND srvc_cd = 'GTEST' "; // add yoshizawa 2015/09/29 02_作業要件/34_数学検定     // del 2020/09/14 thanh テスト標準化開発
					" AND class_id = '' ";	// add 2020/09/14 thanh テスト標準化開発
			if ($_SESSION['view_session']['gknn'] != "0" && $_SESSION['view_session']['gknn'] != "") {
				$sql .= " AND test_gknn='".$_SESSION['view_session']['gknn']."'";
			}
			$sql .= " ORDER BY disp_sort";
			if ($result = $cdb->query($sql)) {
				$test_group_id_count = $cdb->num_rows($result);
			}
			if ($test_group_id_count < 1) {
				$test_group_id_html .= "<option value=\"0\">グループが登録されておりません。</option>\n";
			} else {
				$test_group_id_html .= "<option value=\"0\">選択して下さい</option>\n";
				while ($list = $cdb->fetch_assoc($result)) {
					$selected = "";
					if ($_SESSION['view_session']['test_group_id'] == $list['test_group_id']) {
						$selected = "selected";
					}
					$test_group_id_html .= "<option value=\"".$list['test_group_id']."\" ".$selected.">".$list['test_group_name']."</option>\n";
				}
			}
		}
	} else {
		$test_group_id_html .= "<option value=\"0\">--------</option>\n";
	}


	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"menu\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"view_session\">\n";
	$html .= "<table class=\"unit_form\">\n";
	$html .= "<tr class=\"unit_form_menu\">\n";
	$html .= "<td>学年</td>\n";
	$html .= "<td>グループ名</td>\n";
	$html .= "</tr>\n";
	$html .= "<tr class=\"unit_form_cell\">\n";
	$html .= "<td>\n";
	$html .= "<select name=\"gknn\" onchange=\"submit();\">\n".$gknn_html."</select>\n";
	$html .= "</td>\n";
	$html .= "<td>\n";
	$html .= "<select name=\"test_group_id\" onchange=\"submit();\">\n".$test_group_id_html."</select>\n";
	$html .= "</td>\n";
	$html .= "</tr>\n";
	$html .= "</table>\n";
	$html .= "</form>\n";
	$html .= "<br />\n";
	$html .= default_html($ERROR);

	return $html;
}


/**
 * 学年選択セッションセット
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function view_set_session() {

	$gknn = $_SESSION['view_session']['gknn'];
	$test_group_id = $_SESSION['view_session']['test_group_id'];
	unset($_SESSION['view_session']);

	if ($_POST['gknn'] != "") {
		$_SESSION['view_session']['gknn'] = $_POST['gknn'];
	} else {
		return;
	}

	if ($_POST['gknn'] != "0" && $_POST['gknn'] != "" && $_POST['test_group_id'] != "") {
		$_SESSION['view_session']['test_group_id'] = $_POST['test_group_id'];
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

	// DB接続オブジェクト
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
	$where = "";
	$WHERE = array();
	if ($_SESSION['view_session']['gknn'] != "" && $_SESSION['view_session']['gknn'] != "0") {
		$WHERE[] = " mbg.test_gknn='".$_SESSION['view_session']['gknn']."'";
	}
	if ($_SESSION['view_session']['test_group_id'] > 0) {
		$WHERE[] = " mbg.test_group_id='".$_SESSION['view_session']['test_group_id']."'";
	}
	// $WHERE[] = " mbg.srvc_cd = 'GTEST'"; // add yoshizawa 2015/09/29 02_作業要件/34_数学検定    // del 2020/09/14 thanh テスト標準化開発
	$WHERE[] = " mbg.class_id  = ''";   // add 2020/09/14 thanh テスト標準化開発
	
	if ($WHERE) {
		foreach ($WHERE AS $value) {
			if ($where == "") {
				$where .= " WHERE";
			} else {
				$where .= " AND";
			}
			$where .= $value;
		}
	}
	$sql  = "SELECT MAX(mbg.upd_date) AS upd_date FROM ".T_MS_BOOK_GROUP." mbg".
			$where.";";
	$sql_cnt  = "SELECT DISTINCT mbg.* FROM ".T_MS_BOOK_GROUP." mbg".
				$where.";";

	$sql2 = "SELECT MAX(bgl.upd_date) AS upd_date FROM ".T_BOOK_GROUP_LIST." bgl".
			" LEFT JOIN ".T_MS_BOOK_GROUP." mbg ON mbg.test_group_id=bgl.test_group_id".
				$where.";";
	$sql2_cnt = "SELECT DISTINCT bgl.* FROM ".T_BOOK_GROUP_LIST." bgl".
				" LEFT JOIN ".T_MS_BOOK_GROUP." mbg ON mbg.test_group_id=bgl.test_group_id".
				$where.";";

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

	$local2_html = "";
	$local2_time = "";
	$cnt = 0;
	if ($result = $cdb->query($sql2)) {
		$list = $cdb->fetch_assoc($result);
		$local2_time = $list['upd_date'];
	}
	if ($result = $cdb->query($sql2_cnt)) {
		$cnt = $cdb->num_rows($result);
	}
	if ($local2_time) {
		$local2_html = $local2_time." (".$cnt.")";
	} else {
		$local2_html = "データーがありません。";
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

	$remote2_html = "";
	$remote2_time = "";
	$cnt = 0;
	// >>> update 2016/12/01 yoshizawa
	// if ($result = $connect_db->query($sql2_cnt)) {
	if ($result = $connect_db->query($sql2)) {
	// <<<
		$list = $connect_db->fetch_assoc($result);
		$remote2_time = $list['upd_date'];
	}
	// >>> update 2016/12/01 yoshizawa
	// if ($result = $connect_db->query($sql)) {
	if ($result = $connect_db->query($sql2_cnt)) {
	// <<<
		$cnt = $connect_db->num_rows($result);
	}
	if ($remote2_time) {
		$remote2_html = $remote2_time." (".$cnt.")";
	} else {
		$remote2_html = "データーがありません。";
	}

	if ($local_time || $remote_time) {
		$submit_msg = "学力診断テストグループ情報を検証へアップしますがよろしいですか？";

		$html .= "学力診断テストグループ情報をアップする場合は、「アップする」ボタンを押してください。<br>\n";
		$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"update\">\n";
		$html .= "<input type=\"submit\" value=\"アップする\" onClick=\"return confirm('".$submit_msg."')\"><br>\n";
		$html .= "<table border=\"0\" cellspacing=\"1\" bgcolor=\"#666666\" cellpadding=\"3\">\n";
		$html .= "<tr bgcolor=\"#cccccc\">\n";
		$html .= "<th>&nbsp;</th>\n";
		$html .= "<th>テストサーバー最新更新日</th>\n";
		$html .= "<th>".$_SESSION['select_db']['NAME']."最新更新日</th>\n";
		$html .= "</tr>\n";
		$html .= "<tr valign=\"top\" bgcolor=\"#ffffff\" align=\"center\">\n";
		$html .= "<td>".T_MS_BOOK_GROUP."</td>\n";
		$html .= "<td>\n";
		$html .= $local_html;
		$html .= "</td>\n";
		$html .= "<td>\n";
		$html .= $remote_html;
		$html .= "</td>\n";
		$html .= "</tr>\n";
		$html .= "<tr valign=\"top\" bgcolor=\"#ffffff\" align=\"center\">\n";
		$html .= "<td>".T_BOOK_GROUP_LIST."</td>\n";
		$html .= "<td>\n";
		$html .= $local2_html;
		$html .= "</td>\n";
		$html .= "<td>\n";
		$html .= $remote2_html;
		$html .= "</td>\n";
		$html .= "</tr>\n";
		$html .= "</table>\n";
		$html .= "<input type=\"submit\" value=\"アップする\" onClick=\"return confirm('".$submit_msg."')\"><br>\n";
		$html .= "</form>\n";
	} else {
		$html .= "学力診断テストグループ情報が設定されておりません。<br>\n";
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
 * @return array エラーの場合
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
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();

	//	更新情報クエリー
	$where = "";
	$WHERE = array();
	if ($_SESSION['view_session']['gknn'] != "" && $_SESSION['view_session']['gknn'] != "0") {
		$WHERE[] = " mbg.test_gknn='".$_SESSION['view_session']['gknn']."'";
	}
	if ($_SESSION['view_session']['test_group_id'] > 0) {
		$WHERE[] = " mbg.test_group_id='".$_SESSION['view_session']['test_group_id']."'";
	}
	// $WHERE[] = " mbg.srvc_cd = 'GTEST'"; // add yoshizawa 2015/09/29 02_作業要件/34_数学検定    // del 2020/09/14 thanh テスト標準化開発
	$WHERE[] = " mbg.class_id = ''";  // add 2020/09/14 thanh テスト標準化開発

	if ($WHERE) {
		foreach ($WHERE AS $value) {
			if ($where == "") {
				$where .= " WHERE";
			} else {
				$where .= " AND";
			}
			$where .= $value;
		}
	}
	//	test_group_list
	$sql  = "SELECT bgl.* FROM ".T_BOOK_GROUP_LIST." bgl".
			" LEFT JOIN ".T_MS_BOOK_GROUP." mbg ON mbg.test_group_id=bgl.test_group_id".
			$where.";";
	if ($result = $cdb->query($sql)) {
		make_insert_query($result, T_BOOK_GROUP_LIST, $INSERT_NAME, $INSERT_VALUE);
	}
	//	ms_test_group
	$sql  = "SELECT * FROM ".T_MS_BOOK_GROUP." mbg".
			$where.";";
	if ($result = $cdb->query($sql)) {
		make_insert_query($result, T_MS_BOOK_GROUP, $INSERT_NAME, $INSERT_VALUE);
	}

	//	検証バッチDBデーター削除クエリー
	//	test_group_list
	$sql  = "DELETE bgl FROM ".T_BOOK_GROUP_LIST." bgl".
			" LEFT JOIN ".T_MS_BOOK_GROUP." mbg ON mbg.test_group_id=bgl.test_group_id".
			$where.";";
	$DELETE_SQL[] = $sql;
	//	ms_test_group
	$sql  = "DELETE mbg FROM ".T_MS_BOOK_GROUP." mbg".
			$where.";";
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
	$sql = "OPTIMIZE TABLE ".T_BOOK_GROUP_LIST.";";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
	}

	//	テーブル最適化
	$sql = "OPTIMIZE TABLE ".T_MS_BOOK_GROUP.";";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
	}


	//	検証バッチDB切断
	$connect_db->close();

	//	検証バッチから検証webへ
	$send_data = " '".$_SESSION['view_session']['gknn']."' '".$_SESSION['view_session']['test_group_id']."'";
	// $command = "ssh suralacore01@srlbtw21 ./TESTCONTENTSUP.cgi '2' 'test_".MODE."'".$send_data; // del 2018/03/19 yoshizawa AWSプラクティスアップデート
	$command = "/usr/bin/php ".BASE_DIR."/_www/batch/TESTCONTENTSUP.cgi '2' 'test_".MODE."'".$send_data; // add 2018/03/19 yoshizawa AWSプラクティスアップデート
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
			" AND state='1'";
	if ($_SESSION['view_session']['gknn'] != "") {
		$sql .= " AND course_num='".$_SESSION['view_session']['gknn']."'";
	} else {
		$sql .= " AND course_num IS NULL";
	}
	if ($_SESSION['view_session']['test_group_id'] > 0) {
		$sql .= " AND stage_num='".$_SESSION['view_session']['test_group_id']."'";
	} else {
		$sql .= " AND stage_num IS NULL";
	}
	$sql .= " ORDER BY regist_time DESC".
			" LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$test_mate_upd_log_num = $list['test_mate_upd_log_num'];
	}

	if ($test_mate_upd_log_num < 1) {
		unset($INSERT_DATA);
		$INSERT_DATA['state'] = 0;
		$INSERT_DATA['regist_time'] = "now()";
		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
		$where = " WHERE update_mode='".MODE."'".
				 " AND state!='0'";
		if ($_SESSION['view_session']['gknn'] != "0" && $_SESSION['view_session']['gknn'] != "") {
			$where .= " AND course_num='".$_SESSION['view_session']['gknn']."'";
		}
		if ($_SESSION['view_session']['test_group_id'] > 0) {
			$where .= " AND stage_num='".$_SESSION['view_session']['test_group_id']."'";
		}
		$ERROR = $cdb->update(T_TEST_MATE_UPD_LOG, $INSERT_DATA,$where);
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
		if ($_SESSION['view_session']['gknn'] != "") {
			$INSERT_DATA['course_num'] = $_SESSION['view_session']['gknn'];
		}
		if ($_SESSION['view_session']['test_group_id'] > 0) {
			$INSERT_DATA['stage_num'] = $_SESSION['view_session']['test_group_id'];
		}
		$INSERT_DATA['send_data'] = $send_data_log;
		$INSERT_DATA['regist_time'] = "now()";
		$INSERT_DATA['state'] = 1;
		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
		$ERROR = $cdb->insert(T_TEST_MATE_UPD_LOG, $INSERT_DATA);
	}

	return $ERROR;
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

	$html  = "学力診断テストグループ情報のアップが完了致しました。<br>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"back\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\"><br>\n";
	$html .= "</form>\n";

	return $html;
}
?>
