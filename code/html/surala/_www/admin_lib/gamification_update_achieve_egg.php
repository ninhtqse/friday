<?PHP
/**
 * すらら
 *
 * ゲーミフィケーション管理　プラクティスアップデートプログラム
 * 	サブプログラム	アチーブエッグ情報アップデート
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
function sub_start() {

	if (ACTION == "update") {
		$ERROR = update();
	} elseif (ACTION == "db_session") {
		$ERROR = select_database();
		$ERROR = select_web();
	}

	if (!$ERROR && ACTION == "update") {
		$html = update_end_html();
	} else {
		$html = default_html($ERROR);
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

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
	
	$sel_achieve_egg = 0;
	if ($_POST['sel_achieve_egg']) {
		$sel_achieve_egg = $_POST['sel_achieve_egg'];		
	}	
		
	$L_ACHIEVE_EGG = array();
	
	// 登録済アチーブエッグ情報取得
	$sql  = "SELECT achieve_egg_id, achieve_egg_name FROM ".T_GAMIFICATION_ACHIEVE_EGG.";";
	if ($result = $cdb->query($sql)) {
		while($list = $cdb->fetch_assoc($result)) {
			$L_ACHIEVE_EGG[$list['achieve_egg_id']] = $list['achieve_egg_id'].":".$list['achieve_egg_name'];			
		}
	}	

	$achieve_egg_select_html = '';
	if (count($L_ACHIEVE_EGG) == 0) {
		return "データが登録されていません。";
	} else {
		$achieve_egg_select_html = "<select name=\"sel_achieve_egg\" onchange=\"submit();\">";
		$achieve_egg_select_html .= "<option value=\"0\">すべてアップする</option>";
		foreach ($L_ACHIEVE_EGG as $k => $v) {
			$selected = "";
			if ($sel_achieve_egg == $k) {
				$selected = " selected";
			}
			$achieve_egg_select_html .= "<option value=\"".$k."\"".$selected.">".$v."</option>";
		}
		$achieve_egg_select_html .= "</select>";
	}

	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" name=\"del_form_all\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"".$_POST['mode']."\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"db_session\">\n";
// 	$html .= "対象データを選択してください：".$achieve_egg_select_html;
	$html .= "<br>";
	$html .= select_db_menu();
	$html .= select_web_menu();
	$html .= "</form>\n";

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
	//-----------------------------------------------------------------
	// アチーブエッグデータ取得
	//-----------------------------------------------------------------
	//	アチーブエッグデータ情報
	$sql  = "SELECT max(upd_date) AS upd_date FROM ".T_GAMIFICATION_ACHIEVE_EGG." WHERE 1";
	if ($sel_achieve_egg) {
		$sql  .= " AND achieve_egg_id = '".$sel_achieve_egg."'";
	}
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
	
	// -- 閲覧DB
	if ($result = $connect_db->query($sql)) {
		$list = $connect_db->fetch_assoc($result);
		$remote_time = $list['upd_date'];
	}
	if ($remote_time) {
		$remote_html = $remote_time;
	} else {
		$remote_html = "データーがありません。";
	}

	if ($ERROR) {
		$html  = ERROR($ERROR);
		$html .= "<br>\n";
	}
	
	//-----------------------------------------------------------------
	//	ファイル更新情報取得
	//-----------------------------------------------------------------
	// 画像ファイル >>>>>>>
	//	ローカルデータ取得 ---------------------------
	$LOCAL_FILES = null;
	$LOCAL_FILES = local_read_dir(MATERIAL_GAM_ACHIEVE_EGG_DIR);
	$local_max = count($LOCAL_FILES["f"]);
	if ($local_max) {
		
		if ($sel_achieve_egg >0) {
			foreach($LOCAL_FILES["f"] as $k => $v) {
				if (!preg_match('/achieve_egg_'.$sel_achieve_egg.'\.'
					. '|achieve_egg_'.$sel_achieve_egg.'_set\.'
					. '|achieve_egg_'.$sel_achieve_egg.'_motion\.'
					. '|achieve_egg_'.$sel_achieve_egg.'_set_state1\.'
					. '|achieve_egg_'.$sel_achieve_egg.'_set_state2\.'
					. '|achieve_egg_'.$sel_achieve_egg.'_set_motion\.'
					. '|achieve_egg_'.$sel_achieve_egg.'_born_motion\.'
					. '/', $k)) {
					unset($LOCAL_FILES["f"][$k]);
				}
			}
		}		
				
		$LOCAL_TIME = $LOCAL_FILES;
		@rsort($LOCAL_TIME[f]);
		$local_new_time = $LOCAL_TIME[f][0];
		$local_img_html .= "最新更新時間：".date("Y/m/d H:i:s", $local_new_time);
		$local_img_html .= "<table class=\"course_form\">\n";
		$local_img_html .= "<tr class=\"course_form_menu\">\n";
		$local_img_html .= "<th>ファイル名</th>\n";
		$local_img_html .= "<th>更新時間</th>\n";
		$local_img_html .= "</tr>\n";
		@ksort($LOCAL_FILES["f"],SORT_STRING);
		foreach ($LOCAL_FILES["f"] AS $key => $val) {
			$local_img_html .= "<tr class=\"course_form_cell\">\n";
			$local_img_html .= "<td>".$key."</td>\n";
			$local_img_html .= "<td>".date("Y/m/d H:i:s",$val)."</td>\n";
			$local_img_html .= "</tr>\n";
		}
		$local_img_html .= "</table>\n";
	} else {
		$local_img_html = "登録されておりません。";
	}
	
	//	リモートデータ取得 ---------------------------
	$REMOTE_FILES = null;
	$REMOTE_FILES = remote_read_dir($_SESSION['select_web'], REMOTE_MATERIAL_GAM_ACHIEVE_EGG_DIR);
	$remote_max = count($REMOTE_FILES["f"]);
	if ($remote_max) {
		
		if ($sel_achieve_egg >0) {
			foreach($REMOTE_FILES["f"] as $k => $v) {
				if (!preg_match('/achieve_egg_'.$sel_achieve_egg.'\.'
					. '|achieve_egg_'.$sel_achieve_egg.'_set\.'
					. '|achieve_egg_'.$sel_achieve_egg.'_motion\.'
					. '|achieve_egg_'.$sel_achieve_egg.'_set_state1\.'
					. '|achieve_egg_'.$sel_achieve_egg.'_set_state2\.'
					. '|achieve_egg_'.$sel_achieve_egg.'_set_motion\.'
					. '|achieve_egg_'.$sel_achieve_egg.'_born_motion\.'
					. '/', $k)) {
					unset($REMOTE_FILES["f"][$k]);
				}
			}
		}
		
		$REMOTE_TIME = $REMOTE_FILES;
		@rsort($REMOTE_TIME[f]);
		$remote_new_time = $REMOTE_TIME[f][0];
		$remote_img_html .= "最新更新時間：".date("Y/m/d H:i:s", $remote_new_time);
		$remote_img_html .= "<table class=\"course_form\">\n";
		$remote_img_html .= "<tr class=\"course_form_menu\">\n";
		$remote_img_html .= "<th>ファイル名</th>\n";
		$remote_img_html .= "<th>更新時間</th>\n";
		$remote_img_html .= "</tr>\n";
		@ksort($REMOTE_FILES["f"],SORT_STRING);
		foreach ($REMOTE_FILES["f"] AS $key => $val) {
			$remote_img_html .= "<tr class=\"course_form_cell\">\n";
			$remote_img_html .= "<td>".$key."</td>\n";
			$remote_img_html .= "<td>".date("Y/m/d H:i:s",$val)."</td>\n";
			$remote_img_html .= "</tr>\n";
		}
		$remote_img_html .= "</table>\n";
	} else {
		$remote_img_html = "登録されておりません。";
	}
	
	if ($ERROR) {
		$html  = ERROR($ERROR);
		$html .= "<br />\n";
	}
	
	if ($ERROR) {
		$html  = ERROR($ERROR);
		$html .= "<br />\n";
	}
	
	if ($local_time || $remote_time) {
		$submit_msg = "アチーブエッグ情報を検証へアップしますがよろしいですか？";

		$html .= "アチーブエッグ情報をアップする場合は、「アップする」ボタンを押してください。<br>\n";
		$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" name=\"pupform\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"update\">\n";
		$html .= "<input type=\"hidden\" name=\"sel_achieve_egg\" value=\"".$sel_achieve_egg."\">\n";
		$html .= "<input type=\"submit\" value=\"アップする\" onClick=\"return confirm('".$submit_msg."')\">\n";
		$html .= " 画像もUPする：<input type=\"checkbox\" name=\"fileup\" value=\"1\" OnClick=\"chk_checkbox('box1');\" id=\"box1\" /><br />\n";
		$html .= "<table border=\"0\" cellspacing=\"1\" bgcolor=\"#666666\" cellpadding=\"3\">\n";
		$html .= "<tr bgcolor=\"#cccccc\">\n";
		$html .= "<th>テーブル名</th>\n";
		$html .= "<th>テストサーバー最新更新日</th>\n";
		$html .= "<th>".$_SESSION['select_db']['NAME']."最新更新日</th>\n";
		$html .= "</tr>\n";
		$html .= "<tr valign=\"top\" bgcolor=\"#ffffff\" align=\"center\">\n";
		$html .= "<td>\n";
		$html .= T_GAMIFICATION_ACHIEVE_EGG."\n";
		$html .= "</td>\n";
		$html .= "<td>\n";
		$html .= $local_html;
		$html .= "</td>\n";
		$html .= "<td>\n";
		$html .= $remote_html;
		$html .= "</td>\n";
		$html .= "<tr bgcolor=\"#cccccc\">\n";
		$html .= "<th></th>\n";
		$html .= "<th>テストサーバー最新更新日</th>\n";
		$html .= "<th>".$_SESSION['select_web']['NAME']."最新更新日</th>\n";
		$html .= "</tr>\n";		
		$html .= "<tr valign=\"top\" bgcolor=\"#ffffff\" align=\"center\">\n";
		$html .= "<td>画像ファイル</td>\n";
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
		$html .= "アチーブエッグ情報が設定されておりません。<br>\n";
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
	
	$sel_achieve_egg = 0;
	if ($_POST['sel_achieve_egg']) {
		$sel_achieve_egg = $_POST['sel_achieve_egg'];		
	}	
	
	// 検証バッチDB接続
	$connect_db = new connect_db();
	$connect_db->set_db($L_CONTENTS_DB['92']);
	$ERROR = $connect_db->set_connect_db();
	if ($ERROR) {
		$html .= ERROR($ERROR);
	}

	// データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();

	// アチーブエッグ情報
	$sql  = "SELECT * FROM ".T_GAMIFICATION_ACHIEVE_EGG." WHERE 1";
	if ($sel_achieve_egg) {
		$sql  .= " AND achieve_egg_id = '".$sel_achieve_egg."'";
	}
	if ($result = $cdb->query($sql)) {
		make_insert_query($result, T_GAMIFICATION_ACHIEVE_EGG, $INSERT_NAME, $INSERT_VALUE);
	}

	// 検証バッチDBデーター削除クエリー
	$sql  = "DELETE FROM ".T_GAMIFICATION_ACHIEVE_EGG." WHERE 1";
	if ($sel_achieve_egg) {
		$sql  .= " AND achieve_egg_id = '".$sel_achieve_egg."'";
	}
	$DELETE_SQL['gamification_character'] = $sql;

	// トランザクション開始
	$sql  = "BEGIN";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL BEGIN ERROR";
		$connect_db->close();
		return $ERROR;
	}

	// 削除
	if ($DELETE_SQL) {
		echo "<br>\n";
		echo "db情報削除中<br>\n";
		flush();

		$err_flg = 0;
		foreach ($DELETE_SQL AS $table_name => $sql) {
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

	echo "<br>\n";
	echo "db情報更新中<br>\n";
	flush();

	$last_table_name = "";
	if (count($INSERT_NAME) && count($INSERT_VALUE)) {
		foreach ($INSERT_NAME AS $table_name => $insert_name) {
			if ($INSERT_VALUE[$table_name]) {
				if ($last_table_name != $table_name) {
					echo "<br>\n";
					echo $table_name." 情報更新中<br>\n";
					flush();
				}

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
			$last_table_name = $table_name;
		}
	}

	//	トランザクションコミット
	$sql  = "COMMIT";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL COMMIT ERROR";
		$connect_db->close();
		return $ERROR;
	}

	echo "<br>\n";
	echo "db最適化中<br>\n";
	flush();

	//	テーブル最適化
	$sql = "OPTIMIZE TABLE ".T_GAMIFICATION_ACHIEVE_EGG.";";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
	}
	// ファイルアップロード開始 >>>>>>>>>>>>>>>>>>>>>>>>>>>>>

	if ($_POST['fileup'] == 1) {
		//	画像ファイル
		echo "<br>\n";
		echo "画像ファイルアップロード開始<br>\n";
		flush();

		$local_dir = BASE_DIR.REMOTE_MATERIAL_GAM_ACHIEVE_EGG_DIR;
		$remote_dir = KBAT_DIR.REMOTE_MATERIAL_GAM_DIR;
		
		if ($sel_achieve_egg > 0) {

			// アップ用フォルダ作成
			$remote_dir = $remote_dir."/".$sel_achieve_egg."/";			
			$command = "mkdir -p ".$remote_dir;
			exec("$command",$LIST);
			
			$LOCAL_FILES = local_read_dir($local_dir);
			$local_max = count($LOCAL_FILES["f"]);
			if ($local_max) {
				foreach($LOCAL_FILES["f"] as $k => $v) {
					if (!preg_match('/achieve_egg_'.$sel_achieve_egg.'\.'
						. '|achieve_egg_'.$sel_achieve_egg.'_set\.'
						. '|achieve_egg_'.$sel_achieve_egg.'_motion\.'
						. '|achieve_egg_'.$sel_achieve_egg.'_set_state1\.'
						. '|achieve_egg_'.$sel_achieve_egg.'_set_state2\.'
						. '|achieve_egg_'.$sel_achieve_egg.'_set_motion\.'
						. '|achieve_egg_'.$sel_achieve_egg.'_born_motion\.'
						. '/', $k)) {
						unset($LOCAL_FILES["f"][$k]);
					}
				}

				if (is_array($LOCAL_FILES["f"])) {
					foreach ($LOCAL_FILES["f"] as $k => $v) {
						$command = "cp -rp ".$local_dir.$k." ".$remote_dir;
						exec("$command",$LIST);
					}
				}
			}
			
		} else {
			$command = "cp -rp ".$local_dir." ".$remote_dir;
			exec("$command",$LIST);
		}
//  echo $command."<br>";
	}


	echo "<br>\n";
	echo "検証サーバーへデーター反映中<br>\n";
	flush();


	//	検証サーバー反映処理記録
	$update_num = 0;
	$sql  = "INSERT INTO test_update_check".
			" (type, start_time)".
			" VALUE('2', now());";
	if ($connect_db->exec_query($sql)) {
		$update_num = $connect_db->insert_id();
	}

	
	//	検証バッチから検証webへ
	if ($_POST['fileup'] != 1) { $_POST['fileup'] = "0"; }
	$_SESSION['view_session']['fileup'] = $_POST['fileup'];
	$fileup = $_SESSION['view_session']['fileup'];
	if (!$fileup) { $fileup = "0"; }
	$send_data = " '".$sel_achieve_egg."' '".$fileup."' '".$update_num."'";
	$command = "/usr/bin/php ".BASE_DIR."/_www/batch/GAMIFICATION_CONTENTSUP.cgi '2' '".MODE."'".$send_data;
	$command .= " > /dev/null &";

	
// echo $command."<br>";
// exit();
	exec($command);

	if ($update_num > 0) {
		$end_flg = 1;
		for ($i=0; $i<=600; $i++) {
			$sql  = "SELECT state FROM test_update_check".
					" WHERE update_num='".$update_num."';";
			if ($result = $connect_db->query($sql)) {
				$list = $connect_db->fetch_assoc($result);
				$state = $list['state'];
			}

			if ($state != 0) {
				$end_flg = 0;
				break;
			}

			echo "・";
			flush();
			sleep(2);
		}

		echo "<br>\n";
		flush();
	}

	if ($end_flg == 1) {
		echo "<br>\n";
		echo "反映処理が完了しておりませんがタイムアウト防止の為次の処理に進みます。<br>\n";
		flush();
	}


	echo "<br>\n";
	echo "検証サーバーへデーター反映終了<br>\n";
	flush();

	//	ログ保存 --
	unset($mate_upd_log_num);
	$sql  = "SELECT gamification_mate_upd_log FROM mate_upd_log".
			" WHERE update_mode='".MODE."'".
			" AND master_id='".$sel_achieve_egg."'".
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

		$ERROR = $cdb->update('gamification_mate_upd_log',$INSERT_DATA,$where);
	} else {
		unset($INSERT_DATA);
		$INSERT_DATA['update_mode'] = MODE;
		$INSERT_DATA['master_id'] = $sel_achieve_egg;
		$INSERT_DATA['regist_time'] = "now()";
		$INSERT_DATA['state'] = 1;
		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];

		$ERROR = $cdb->insert('gamification_mate_upd_log',$INSERT_DATA);
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
 * @return string HTML
 */
function update_end_html() {

	$html  = "アチーブエッグ情報のアップが完了致しました。<br>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"back\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\"><br>\n";
	$html .= "</form>\n";

	return $html;
}
?>