<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * シークレットイベント  プラクティスアップデート管理
 * 	プラクティスアップデートプログラム
 * 		パズル アップデート
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
//function action() {
	global $L_TEST_UPDATE_MODE;

	$html = "";

	if (ACTION == "update") {
		update($ERROR);
	} elseif (ACTION == "db_session") {
		select_database();
		select_web();
	} elseif (ACTION == "view_session") {	//add atart 2013/11/25
		view_set_session();
	} elseif (ACTION == "") {
		unset($_SESSION['view_session']);	//add end 2013/11/25
	}


	if (!$ERROR && ACTION == "update") {
		$html = update_end_html();
	} else {
		//$html = default_html($ERROR);
		$html = select_image_category($ERROR);
	}

	return $html;
}


/**
 * 公開年月の選択セッションセット
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function view_set_session() {

	$image_category = $_SESSION['view_session']['image_category'];

	unset($_SESSION['view_session']);

	if ($_POST['image_category'] > 0) {
		$_SESSION['view_session']['image_category'] = $_POST['image_category'];
	}

	return;
}


/**
 * 公開年月の選択選択
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function select_image_category($ERROR) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$html = "";

	if ($ERROR) {
		$html .= ERROR($ERROR);
		$html .= "<br>\n";
	}

	//	検証中データー取得
	$PMUL = array();
	$send_data = "";
	$image_category = "";
	$sql  = "SELECT send_data".
			" FROM mate_upd_log".
			" WHERE update_mode='".MODE."'".
			" AND state='1';";
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$send_data = $list['send_data'];
			$VALUES = unserialize($send_data);
			$image_category = $VALUES['image_category'];

			if ($image_category == "0") {	//全件指定
				$PMUL['all'] = 1;
			} else {
				$PMUL[$image_category] = 1;
			}
		}
	}

	//	登録済みの公開年月の取得
	$PUZZLE_IMAGE_LIST = array();
	$sql = "SELECT secret_puzzle_image.image_category".
		" FROM ".T_SECRET_PUZZLE_IMAGE." secret_puzzle_image".
		" WHERE secret_puzzle_image.display = 1".
		" AND secret_puzzle_image.mk_flg = 0".
		" ORDER BY secret_puzzle_image.image_category DESC;";
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$PUZZLE_IMAGE_LIST[] = $list['image_category'];
			//$selected = "";
			//if ($_SESSION['view_session']['image_category'] == $list['image_category']) {
			//	$selected = "selected";
			//}
			//$couse_html .= "<option value=\"".$list['image_category']."\" ".$selected.">".$list['image_category']."</option>\n";
		}
	}

	//	選択リスト
	$image_category_html  = "";
	$chk_data = 0;
	if (count($PUZZLE_IMAGE_LIST) > 0) {
		if ($PMUL['all'] == 1) {
			$image_category_html .= "<option value=\"\">アップデート中の為選択出来ません</option>\n";
		} else {
			$image_category_html .= "<option value=\"\">すべてをアップデートする</option>\n";
			foreach ($PUZZLE_IMAGE_LIST AS $val) {
				$selected = "";
				if ($_SESSION['view_session']['image_category'] == $val) {
					$selected = "selected";
				}
				$image_category_html .= "<option value=\"".$val."\" ".$selected.">".$val."</option>\n";
				$chk_data = 1;
			}
		}

		//選択データがあるとき、選択リストを表示する
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"menu\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"view_session\">\n";
		$html .= "対象データを選択してください：<select name=\"image_category\"";
		if ($chk_data > 0) {
			$html .= " onchange=\"submit();\"";
		}
		$html .= ">\n";
		$html .= $image_category_html;
		$html .= "</select>\n";
		$html .= "</form><br>\n";

		$html .= default_html($ERROR);

	} else {
		//$image_category_html .= "<option value=\"\">登録データがありません</option>\n";
		$html .= "登録データがありません。<br>\n";
		$html .= "<br />\n";
	}

	return $html;
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
//echo "default_html:session_image_category=".$_SESSION['view_session']['image_category']."<br>";

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$html = "";

	if ($ERROR) {
		$html .= ERROR($ERROR);
		$html .= "<br>\n";
	}

	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"".$_POST['mode']."\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"db_session\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
	$html .= select_db_menu();
	$html .= select_web_menu();
	$html .= "</form>\n";
	$html .= "<br />\n";

	unset($BASE_DATA);
	unset($MAIN_DATA);
	//サーバー情報取得
	if (!$_SESSION['select_db']) { return $html; }
	if (!$_SESSION['select_web']) { return $html; }

	//	閲覧DB接続
	$connect_db = new connect_db();
	$connect_db->set_db($_SESSION['select_db']);
	$ERROR = $connect_db->set_connect_db();
	if ($ERROR) {
		$html .= ERROR($ERROR);
	}

	//	情報取得クエリー
	$sql  = "SELECT MAX(secret_puzzle_image.upd_date) AS upd_date FROM ".T_SECRET_PUZZLE_IMAGE." secret_puzzle_image;";
	$sql_cnt  = "SELECT DISTINCT secret_puzzle_image.*  FROM ".T_SECRET_PUZZLE_IMAGE." secret_puzzle_image;";

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

	//選択データ（開示年月）、すべてのデータが対象の場合は、文字列長0。
	$image_category = "";
	if (strlen($_SESSION['view_session']['image_category']) > 0) {
		$image_category = $_SESSION['view_session']['image_category'];
	}

	//	画像ファイル
	$LOCAL_FILES = array();
	$img_last_local_time = 0;
	$img_last_local_cnt = 0;
	$img_last_remote_time = 0;
	$img_last_remote_cnt = 0;

	//	ファイル更新情報取得
	//	開示パズルデータ取得
	$PUZZLE_LIST = array();
	$sql  = "SELECT secret_puzzle_image.image_category FROM ".T_SECRET_PUZZLE_IMAGE." secret_puzzle_image".
			" WHERE secret_puzzle_image.mk_flg='0';";
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			if ($image_category == 0) {		//全件データ対象の場合
				$PUZZLE_LIST[] = $list['image_category'];
			} else {	//選択データだけの場合
				if ($image_category == $list['image_category']) {
					$PUZZLE_LIST[] = $list['image_category'];
					continue;
				}
			}
		}
	}

	//	ローカルサーバー
	$dir = MATERIAL_SECRET_DIR."puzzle/" ;		// "material/secret_event/"
	local_glob_secretevent($PUZZLE_LIST, $LOCAL_FILES, $img_last_local_time, $dir);
	$img_last_local_cnt = count($LOCAL_FILES['f']);
//print_r($PUZZLE_LIST); echo "<br>";
//print_r($LOCAL_FILES); echo "<br>";
//echo "img_last_local_cnt=".$img_last_local_cnt."<br>";
//echo "<hr>";

	//	閲覧WEBサーバー
	$dir = REMOTE_MATERIAL_SECRET_EVENT_DIR."puzzle/" ;	// "material/secret_event/"
	remote_dir_time_secretevent($PUZZLE_LIST, $img_last_remote_time, $img_last_remote_cnt, $_SESSION['select_web'], $dir);

	$img_local_time = "データーがありません。";
	if ($img_last_local_time > 0) {
		$img_local_time = date("Y-m-d H:i:s", $img_last_local_time);
	}
	$img_remote_time = "データーがありません。";
	if ($img_last_remote_time > 0) {
		$img_remote_time = date("Y-m-d H:i:s", $img_last_remote_time);
	}
	$local_img_html  = $img_local_time;
	if ($img_last_local_cnt > 0) {
		$local_img_html .= " (".$img_last_local_cnt.")";
	}
	$remote_img_html = $img_remote_time;
	if ($img_last_remote_cnt > 0) {
		$remote_img_html .= " (".$img_last_remote_cnt.")";
	}


	if ($local_time || $remote_time) {
		$submit_msg = "パズル情報を検証へアップしますがよろしいですか？";

		$html .= "パズル情報をアップする場合は、「アップする」ボタンを押してください。<br>\n";
		$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" name=\"pupform\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"update\">\n";
		$html .= "<input type=\"submit\" value=\"アップする\" onClick=\"return confirm('".$submit_msg."')\">\n";
		$html .= " 画像もUPする：<input type=\"checkbox\" name=\"fileup\" value=\"1\" OnClick=\"chk_checkbox('box1');\" id=\"box1\" /><br />\n";
		$html .= "<table border=\"0\" cellspacing=\"1\" bgcolor=\"#666666\" cellpadding=\"3\">\n";
		$html .= "<tr bgcolor=\"#cccccc\">\n";
		$html .= "<th>&nbsp;</th>\n";
		$html .= "<th>テストサーバー最新更新日</th>\n";
		$html .= "<th>".$_SESSION['select_db']['NAME']."最新更新日</th>\n";
		$html .= "</tr>\n";
		$html .= "<tr valign=\"top\" bgcolor=\"#ffffff\" align=\"center\">\n";
		$html .= "<td>".T_SECRET_PUZZLE_IMAGE."</td>\n";
		$html .= "<td>\n";
		$html .= $local_html;
		$html .= "</td>\n";
		$html .= "<td>\n";
		$html .= $remote_html;
		$html .= "</td>\n";
		$html .= "</tr>\n";

		$html .= "<tr valign=\"top\" bgcolor=\"#ffffff\" align=\"center\">\n";
		$html .= "<td>画像ファイル(壁紙zip含)</td>\n";
		$html .= "<td>\n";
		$html .= $local_img_html;
		$html .= "</td>\n";
		$html .= "<td>\n";
		$html .= $remote_img_html;
		$html .= "</td>\n";
		$html .= "</tr>\n";

		$html .= "</table>\n";
		$html .= "<input type=\"submit\" value=\"アップする\" onClick=\"return confirm('".$submit_msg."')\">\n";
		$html .= " 画像もUPする：<input type=\"checkbox\" name=\"fileup\" value=\"1\" OnClick=\"chk_checkbox('box2');\" id=\"box2\" /><br />\n";
		$html .= "</form>\n";
	} else {
		$html .= "パズル情報が設定されておりません。<br>\n";
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

	global $L_CONTENTS_DB;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//選択データ（開示年月）、すべてのデータが対象の場合は、文字列長0。
	$image_category = "";
	if (strlen($_SESSION['view_session']['image_category']) > 0) {
		$image_category = $_SESSION['view_session']['image_category'];
	}

	$PUZZLE_LIST = array();
	//対象ディレクトリを指定
	$sql  = "SELECT secret_puzzle_image.image_category FROM ".T_SECRET_PUZZLE_IMAGE." secret_puzzle_image".
			" WHERE secret_puzzle_image.mk_flg='0';";
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			if (strlen($image_category) == 0) {		//全件データ対象の場合
				$PUZZLE_LIST[] = $list['image_category'];
			} else {	//選択データだけの場合
				if ($image_category == $list['image_category']) {
					$PUZZLE_LIST[] = $list['image_category'];
					continue;
				}
			}
		}
	}

	//	検証バッチDB接続
	$connect_db = new connect_db();
	$connect_db->set_db($L_CONTENTS_DB['92']);		//'srlbtw21'(検証バッチ)
	$ERROR = $connect_db->set_connect_db();
	if ($ERROR) {
		$html .= ERROR($ERROR);
	}

	//	データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();
	//一部データの指定
	$where = "";
	if (strlen($image_category) > 0) {
		$where = " WHERE secret_puzzle_image.image_category='".$image_category."'";
	}

	//	更新情報クエリー
	$sql  = "SELECT * FROM ".T_SECRET_PUZZLE_IMAGE." secret_puzzle_image".$where.";";

	if ($result = $cdb->query($sql)) {
		make_insert_query($result, T_SECRET_PUZZLE_IMAGE, $INSERT_NAME, $INSERT_VALUE);
	}

	//	検証バッチDBデーター削除クエリー
	//$sql  = "DELETE FROM ".T_SECRET_PUZZLE_IMAGE." secret_puzzle_image".$where.";";	//	del ookawara 2013/12/01
	$sql  = "DELETE secret_puzzle_image FROM ".T_SECRET_PUZZLE_IMAGE.$where.";";	//	add ookawara 2013/12/01
	$DELETE_SQL[] = $sql;

//echo "<br>"; //echo "*update*".$tbw_db.", ".$tbw_dbname."<br>";
//echo "INSERT_NAME: "; print_r($INSERT_NAME); echo "<br><br>";
//echo "INSERT_VALUE: "; print_r($INSERT_VALUE); echo "<br><br>";
//echo "DELETE_SQL: "; print_r($DELETE_SQL); echo "<br><br>";
//echo "*fileup=".$_POST['fileup']."<br><br>";


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
	$sql = "OPTIMIZE TABLE ".T_SECRET_PUZZLE_IMAGE.";";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
	}

	//	ファイルアップロード
	if ($_POST['fileup'] == 1) {

		echo "<br>\n";
		echo "ファイルアップロード開始<br>\n";
		flush();

		//	画像ファイル
		$LOCAL_FILES = array();
		$dir = MATERIAL_SECRET_DIR."puzzle/" ;		// "material/secret_event/"
		local_glob_secretevent($PUZZLE_LIST, $LOCAL_FILES, $last_local_time, $dir);

		//	フォルダー作成
		$remote_dir = KBAT_DIR.REMOTE_MATERIAL_SECRET_EVENT_DIR."puzzle/";	// "/data/apprelease/Release/Contents/www/material/secret_event/"
		remote_set_dir_secretevent($remote_dir, $LOCAL_FILES, $ERROR);		//この中で、"開示年月"までのディレクトリ作成(既にあるなら、そのまま)

		//	ファイルアップ
		$local_dir = BASE_DIR."/www".preg_replace("/^..\\//","/", MATERIAL_SECRET_DIR."puzzle/");
		$remote_dir = KBAT_DIR.REMOTE_MATERIAL_SECRET_EVENT_DIR."puzzle/";
		remote_set_file_secretevent($local_dir, $remote_dir, $LOCAL_FILES, $ERROR);
	}

	echo "<br>\n";
	echo "検証サーバーへデーター反映中<br>\n";
	flush();


	//	検証サーバー反映処理記録
	$update_num = 0;

	//	検証バッチから検証webへ
	//開示年月である image_category を。
	if ($_SESSION['view_session']['image_category'] > 0) {
		$block_num = $_SESSION['view_session']['image_category'];
	} else {
		$block_num = "0";	//全データ指定
		$_SESSION['view_session']['image_category'] = "0";
	}
	$fileup = 0;
	$_SESSION['view_session']['fileup'] = intval($_POST['fileup']);
	if ($_SESSION['view_session']['fileup'] > 0)	{ $fileup = $_SESSION['view_session']['fileup']; }
	$send_data = " '".$block_num."'".
				 " '".$fileup."'".
				 " '".$update_num."'";
	// $command = "ssh suralacore01@srlbtw21 ./SECRETEVENTCONTENTSUP.cgi '2' '".MODE."'".$send_data; // del 2018/03/20 yoshizawa AWSプラクティスアップデート
	$command = "/usr/bin/php ".BASE_DIR."/_www/batch/SECRETEVENTCONTENTSUP.cgi '2' '".MODE."'".$send_data; // add 2018/03/20 yoshizawa AWSプラクティスアップデート

	exec($command);
//echo "command: ".$command."<br/>\n";	//@@@@@

	echo "<br>\n";
	echo "検証サーバーへデーター反映終了<br>\n";
	flush();


	//	ログ保存 --
	$mate_upd_log_num = "";
	$SEND_DATA_LOG = $_SESSION['view_session'];
	$send_data_log = serialize($SEND_DATA_LOG);
	$send_data_log = addslashes($send_data_log);
	$sql  = "SELECT mate_upd_log_num FROM mate_upd_log".
			" WHERE update_mode='".MODE."'".
			" AND state='1'".
			//" AND block_num='".$_SESSION['view_session']['image_category']."'".
			" AND block_num='".$block_num."'".		//開示年月 all or YYYYMM
			" ORDER BY regist_time DESC".
			" LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$mate_upd_log_num = $list['mate_upd_log_num'];
	}

	if ($mate_upd_log_num < 1) {
		unset($INSERT_DATA);
		$INSERT_DATA['state'] = 0;
		$INSERT_DATA['regist_time'] = "now()";
		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
		$where = " WHERE update_mode='".MODE."'".
				 " AND state!='0'".
				 //" AND block_num='".$_SESSION['view_session']['image_category']."'";
				" AND block_num='".$block_num."'";		//開示年月 all or YYYYMM

		$ERROR = $cdb->update("mate_upd_log", $INSERT_DATA, $where);
	}

	if ($mate_upd_log_num) {
		unset($INSERT_DATA);
		$INSERT_DATA['send_data'] = $send_data_log;
		$INSERT_DATA['regist_time'] = "now()";
		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
		$where = " WHERE mate_upd_log_num='".$mate_upd_log_num."'";

		$ERROR = $cdb->update("mate_upd_log", $INSERT_DATA, $where);
	} else {
		unset($INSERT_DATA);
		$INSERT_DATA['update_mode'] = MODE;
		//$INSERT_DATA['block_num'] = $_SESSION['view_session']['image_category'];
		$INSERT_DATA['course_num'] = 0;
		$INSERT_DATA['stage_num'] = 0;
		$INSERT_DATA['lesson_num'] = 0;
		$INSERT_DATA['unit_num'] = 0;
		$INSERT_DATA['block_num'] = $block_num;		//開示年月 all or YYYYMM
		$INSERT_DATA['send_data'] = $send_data_log;
		$INSERT_DATA['regist_time'] = "now()";
		$INSERT_DATA['state'] = 1;
		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];

		$ERROR = $cdb->insert("mate_upd_log", $INSERT_DATA);
	}

	//	検証バッチDB切断
	$connect_db->close();

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
function update_end_html() {

	$html  = "パズル情報のアップが完了致しました。<br>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"back\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\"><br>\n";
	$html .= "</form>\n";

	return $html;
}
?>