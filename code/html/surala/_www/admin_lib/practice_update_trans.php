<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * プラクティスステージ管理　プラクティスアップデートプログラム
 * 	サブプログラム	翻訳情報アップデート
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

	//	アップデート
	if (ACTION == "update") {
		$ERROR = update();

	} elseif (ACTION == "db_session") {
		$ERROR = select_database();
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

	global $L_WRITE_TYPE,$L_DISPLAY;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if ($ERROR) {
		$html  = ERROR($ERROR);
		$html .= "<br>\n";
	}

	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" name=\"del_form_all\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"".$_POST['mode']."\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"db_session\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
	$html .= select_db_menu();
	$html .= "</form>\n";

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

	//	翻訳ワード＆ページ関連情報
	$sql  = "SELECT max(upd_date) AS upd_date FROM ".T_TRANS_LINKS.";";
	//	翻訳ページ管理情報
	$sql_2  = "SELECT max(upd_date) AS upd_date FROM ".T_TRANS_PAGES.";";
	//	翻訳ワード管理情報
	$sql_3  = "SELECT max(upd_date) AS upd_date FROM ".T_TRANS_WORDS.";";

	//	ローカルサーバー
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$local_time = $list['upd_date'];
	}
	if ($local_time) {
		$local_html = $local_time;
	} else {
		$local_html = "データーがありません。";
	}
	if ($result = $cdb->query($sql_2)) {
		$list = $cdb->fetch_assoc($result);
		$local_time_2 = $list['upd_date'];
	}
	if ($local_time_2) {
		$local_html_2 = $local_time_2;
	} else {
		$local_html_2 = "データーがありません。";
	}
	if ($result = $cdb->query($sql_3)) {
		$list = $cdb->fetch_assoc($result);
		$local_time_3 = $list['upd_date'];
	}
	if ($local_time_3) {
		$local_html_3 = $local_time_3;
	} else {
		$local_html_3 = "データーがありません。";
	}

	// -- 閲覧DB
	unset($update_time);
	if ($result = $connect_db->query($sql)) {
		$list = $connect_db->fetch_assoc($result);
		$remote_time = $list['upd_date'];
	}
	if ($remote_time) {
		$remote_html = $remote_time;
	} else {
		$remote_html = "データーがありません。";
	}
	if ($result = $connect_db->query($sql_2)) {
		$list = $connect_db->fetch_assoc($result);
		$remote_time_2 = $list['upd_date'];
	}
	if ($remote_time_2) {
		$remote_html_2 = $remote_time_2;
	} else {
		$remote_html_2 = "データーがありません。";
	}
	if ($result = $connect_db->query($sql_3)) {
		$list = $connect_db->fetch_assoc($result);
		$remote_time_3 = $list['upd_date'];
	}
	if ($remote_time_3) {
		$remote_html_3 = $remote_time_3;
	} else {
		$remote_html_3 = "データーがありません。";
	}

	if ($ERROR) {
		$html  = ERROR($ERROR);
		$html .= "<br>\n";
	}

	if ( $local_time || $remote_time || $local_time_2 || $remote_time_2 || $local_time_3 || $remote_time_3 ) {
		$submit_msg = "翻訳情報を検証へアップしますがよろしいですか？";

		$html .= "翻訳情報をアップする場合は、「アップする」ボタンを押してください。<br>\n";
		$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" name=\"del_form_all\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"".$_POST['mode']."\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"update\">\n";
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
		$html .= "<input type=\"submit\" value=\"アップする\" onClick=\"return confirm('".$submit_msg."')\"><br>\n";
		$html .= "<table border=\"0\" cellspacing=\"1\" bgcolor=\"#666666\" cellpadding=\"3\">\n";
		$html .= "<tr bgcolor=\"#cccccc\">\n";
		$html .= "<th>テーブル名</th>\n";
		$html .= "<th>テストサーバー最新更新日</th>\n";
		$html .= "<th>".$_SESSION['select_db']['NAME']."最新更新日</th>\n";
		$html .= "</tr>\n";
		$html .= "<tr valign=\"top\" bgcolor=\"#ffffff\" align=\"center\">\n";
		$html .= "<td>\n";
		$html .= T_TRANS_LINKS."\n";
		$html .= "</td>\n";
		$html .= "<td>\n";
		$html .= $local_html;
		$html .= "</td>\n";
		$html .= "<td>\n";
		$html .= $remote_html;
		$html .= "</td>\n";
		$html .= "</tr>\n";
		$html .= "<tr valign=\"top\" bgcolor=\"#ffffff\" align=\"center\">\n";
		$html .= "<td>\n";
		$html .= T_TRANS_PAGES."\n";
		$html .= "</td>\n";
		$html .= "<td>\n";
		$html .= $local_html_2;
		$html .= "</td>\n";
		$html .= "<td>\n";
		$html .= $remote_html_2;
		$html .= "</td>\n";
		$html .= "</tr>\n";
		$html .= "<tr valign=\"top\" bgcolor=\"#ffffff\" align=\"center\">\n";
		$html .= "<td>\n";
		$html .= T_TRANS_WORDS."\n";
		$html .= "</td>\n";
		$html .= "<td>\n";
		$html .= $local_html_3;
		$html .= "</td>\n";
		$html .= "<td>\n";
		$html .= $remote_html_3;
		$html .= "</td>\n";
		$html .= "</tr>\n";
		$html .= "</table>\n";
		$html .= "<input type=\"submit\" value=\"アップする\" onClick=\"return confirm('".$submit_msg."')\"><br>\n";
		$html .= "</form>\n";
	} else {
		$html .= "翻訳情報が設定されておりません。<br>\n";
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
	//	更新情報を取得
	//	レコードのカラム名と値を保持します
	//	翻訳ワード＆ページ関連情報
	$sql  = "SELECT * FROM ".T_TRANS_LINKS.";";
	if ($result = $cdb->query($sql)) {
		make_insert_query($result, T_TRANS_LINKS, $INSERT_NAME, $INSERT_VALUE);
	}
	//	翻訳ページ管理情報
	$sql  = "SELECT * FROM ".T_TRANS_PAGES.";";
	if ($result = $cdb->query($sql)) {
		make_insert_query($result, T_TRANS_PAGES, $INSERT_NAME, $INSERT_VALUE);
	}
	//	翻訳ワード管理情報
	$sql  = "SELECT * FROM ".T_TRANS_WORDS.";";
	if ($result = $cdb->query($sql)) {
		make_insert_query($result, T_TRANS_WORDS, $INSERT_NAME, $INSERT_VALUE);
	}

	//	検証バッチDBデーター削除クエリー
	//	翻訳ワード＆ページ関連情報
	$sql = "TRUNCATE TABLE ".T_TRANS_LINKS.";";
	$DELETE_SQL[T_TRANS_LINKS] = $sql;
	//	翻訳ページ管理情報
	$sql = "TRUNCATE TABLE ".T_TRANS_PAGES.";";
	$DELETE_SQL[T_TRANS_PAGES] = $sql;
	//	翻訳ワード管理情報
	$sql = "TRUNCATE TABLE ".T_TRANS_WORDS.";";
	$DELETE_SQL[T_TRANS_WORDS] = $sql;

	//	トランザクション開始
	$sql  = "BEGIN";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL BEGIN ERROR";
		$connect_db->close();
		return $ERROR;
	}

	//	削除
	if ($DELETE_SQL) {
		$err_flg = 0;
		foreach ($DELETE_SQL AS $table_name => $sql) {
//echo $sql."<br>";
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
//echo $sql."<br>";
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
		return $ERROR;
	}

	//	テーブル最適化
	//	翻訳ワード＆ページ関連情報
	$sql = "OPTIMIZE TABLE ".T_TRANS_LINKS.";";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
	}
	//	翻訳ページ管理情報
	$sql = "OPTIMIZE TABLE ".T_TRANS_PAGES.";";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
	}
	//	翻訳ワード管理情報
	$sql = "OPTIMIZE TABLE ".T_TRANS_WORDS.";";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
	}

	//	検証バッチDB切断
	$connect_db->close();
//echo "batch_start<br>";

	//	検証バッチから検証webへ
	// $command = "ssh suralacore01@srlbtw21 ./CONTENTSUP.cgi '2' '".MODE."'"; // del 2018/03/20 yoshizawa AWSプラクティスアップデート
	$command = "/usr/bin/php ".BASE_DIR."/_www/batch/CONTENTSUP.cgi '2' '".MODE."'"; // add 2018/03/20 yoshizawa AWSプラクティスアップデート

	//upd start 2017/11/27 yamaguchi AWS移設
	//exec($command,&$LIST);
	exec($command,$LIST);
	//upd end 2017/11/27 yamaguchi

	//	ログ保存 --
	unset($mate_upd_log_num);
	$sql  = "SELECT mate_upd_log_num FROM mate_upd_log".
			" WHERE update_mode='".MODE."'".
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

	$html  = "翻訳情報のアップが完了致しました。<br>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"back\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\"><br>\n";
	$html .= "</form>\n";

	return $html;
}
?>