<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * プラクティスステージ管理　プラクティスアップデートプログラム
 * 	サブプログラム	スキル情報アップデート
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

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" name=\"sel_db_form\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"".$_POST['mode']."\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"db_session\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
	$html .= select_db_menu();
	$html .= select_web_menu();
	$html .= "</form>\n";

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

	$submit_db		= "<input type=\"button\" value=\"データ\" OnClick=\"practice_update_action('db');\">";
	$submit_img		= "<input type=\"button\" value=\"画像\" OnClick=\"practice_update_action('img');\">";
	$submit_voice	= "<input type=\"button\" value=\"音声\" OnClick=\"practice_update_action('voice');\">";

	if (ACTION == "img") {	//	問題画像ファイル
		$submit_img = "画像";

		//	ローカルサーバー
		unset($local_max);
		$local_dir = MATERIAL_SKILL_IMG_DIR.$_POST['course_num']."/";
		$LOCAL_FILE_NAME = local_read_dir($local_dir);
		$local_max = count($LOCAL_FILE_NAME['f']);
		if ($local_max) {
			$LOCAL_TIME = $LOCAL_FILE_NAME;
			@rsort($LOCAL_TIME['f']);
			$local_new_time = $LOCAL_TIME['f'][0];
			$local_html .= "最新更新時間：".date("Y/m/d H:i:s",$local_new_time);	//	add :sookawara 2012/08/01
			$local_html .= "<table class=\"course_form\">\n";
			$local_html .= "<tr class=\"course_form_menu\">\n";
			$local_html .= "<th>ファイル名</th>\n";
			$local_html .= "<th>更新時間</th>\n";
			$local_html .= "</tr>\n";
			@ksort($LOCAL_FILE_NAME['f'],SORT_STRING);
			foreach ($LOCAL_FILE_NAME['f'] AS $key => $val) {
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
		$remote_dir = REMOTE_MATERIAL_SKILL_IMG_DIR.$_POST['course_num']."/";
		$FILE_NAME = remote_read_dir($_SESSION['select_web'],$remote_dir);
		$remote_max = count($FILE_NAME['f']);
		if ($remote_max) {
			$REMOTE_TIME = $FILE_NAME;
			@rsort($REMOTE_TIME['f']);
			$remote_new_time = $REMOTE_TIME['f'][0];
			$remote_html .= "最新更新時間：".date("Y/m/d H:i:s",$remote_new_time);	//	add :sookawara 2012/08/01
			$remote_html .= "<table class=\"course_form\">\n";
			$remote_html .= "<tr class=\"course_form_menu\">\n";
			$remote_html .= "<th>ファイル名</th>\n";
			$remote_html .= "<th>更新時間</th>\n";
			$remote_html .= "</tr>\n";
			@ksort($FILE_NAME['f'],SORT_STRING);
			foreach ($FILE_NAME['f'] AS $key => $val) {
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
		$local_dir = MATERIAL_SKILL_VOICE_DIR.$_POST['course_num']."/";
		$LOCAL_FILE_NAME = local_read_dir($local_dir);
		$local_max = count($LOCAL_FILE_NAME["f"]);
		if ($local_max) {
			$LOCAL_TIME = $LOCAL_FILE_NAME;
			@rsort($LOCAL_TIME['f']);
			$local_new_time = $LOCAL_TIME['f'][0];
			$local_html .= "最新更新時間：".date("Y/m/d H:i:s",$local_new_time);	//	add :sookawara 2012/08/01
			$local_html .= "<table class=\"course_form\">\n";
			$local_html .= "<tr class=\"course_form_menu\">\n";
			$local_html .= "<th>ファイル名</th>\n";
			$local_html .= "<th>更新時間</th>\n";
			$local_html .= "</tr>\n";
			@ksort($LOCAL_FILE_NAME['f'],SORT_STRING);
			foreach ($LOCAL_FILE_NAME['f'] AS $key => $val) {
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
		$remote_dir = REMOTE_MATERIAL_SKILL_VOICE_DIR.$_POST['course_num']."/";
		$FILE_NAME = remote_read_dir($_SESSION['select_web'],$remote_dir);
		$remote_max = count($FILE_NAME['f']);
		if ($remote_max) {
			$REMOTE_TIME = $FILE_NAME;
			@rsort($REMOTE_TIME['f']);
			$remote_new_time = $REMOTE_TIME['f'][0];
			$remote_html .= "最新更新時間：".date("Y/m/d H:i:s",$remote_new_time);	//	add :sookawara 2012/08/01
			$remote_html .= "<table class=\"course_form\">\n";
			$remote_html .= "<tr class=\"course_form_menu\">\n";
			$remote_html .= "<th>ファイル名</th>\n";
			$remote_html .= "<th>更新時間</th>\n";
			$remote_html .= "</tr>\n";
			@ksort($FILE_NAME['f'],SORT_STRING);
			foreach ($FILE_NAME['f'] AS $key => $val) {
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
		$sql  = "SELECT list_num, skill_name, update_time FROM ".T_SKILL.
				" WHERE state!='1' AND course_num='".$_POST['course_num']."'".
				" ORDER BY list_num;";

		$sql2 = "SELECT update_time FROM ".T_SKILL.
				" WHERE course_num='".$_POST['course_num']."'".
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
			$local_html .= "<th>スキル名</th>\n";
			$local_html .= "<th>更新時間</th>\n";
			$local_html .= "</tr>\n";
			while ($list = $cdb->fetch_assoc($result)) {
				$local_html .= "<tr class=\"course_form_cell\">\n";
				$local_html .= "<td>".$list['list_num']."</td>\n";
				$skill_name_ = $list['skill_name'];
				$skill_name_ = ereg_replace("&lt;","<",$skill_name_);
				$skill_name_ = ereg_replace("&gt;",">",$skill_name_);
				$local_html .= "<td>".$skill_name_."</td>\n";
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
			$remote_html .= "<th>スキル名</th>\n";
			$remote_html .= "<th>更新時間</th>\n";
			$remote_html .= "</tr>\n";
			while ($list = $connect_db->fetch_assoc($result)) {
				$remote_html .= "<tr class=\"course_form_cell\">\n";
				$remote_html .= "<td>".$list['list_num']."</td>\n";
				$skill_name_ = $list['skill_name'];
				$skill_name_ = str_replace("&lt;","<",$skill_name_);
				$skill_name_ = str_replace("&gt;",">",$skill_name_);
				$remote_html .= "<td>".$skill_name_."</td>\n";
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
		$submit_msg = $L_NAME['course_name']."のスキル情報を検証へアップしますがよろしいですか？";

		$html .= $L_NAME['course_name']."のスキル情報をアップする場合は、「アップする」ボタンを押してください。<br>\n";
		$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" name=\"del_form_all\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"update\">\n";
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
		$html .= "<input type=\"submit\" value=\"アップする\" onClick=\"return confirm('".$submit_msg."')\"><br>\n";
		$html .= "<table border=\"0\" cellspacing=\"1\" bgcolor=\"#666666\" cellpadding=\"3\">\n";
		$html .= "<tr bgcolor=\"#cccccc\">\n";
		$html .= "<th colspan=\"2\">".$L_NAME['course_name']."</th>\n";
		$html .= "</tr>\n";
		$html .= "<tr bgcolor=\"#cccccc\">\n";
		$html .= "<th colspan=\"2\">".$submit_db." / ".$submit_img." / ".$submit_voice."</th>\n";
		$html .= "</tr>\n";
		$html .= "<tr bgcolor=\"#cccccc\">\n";
		$html .= "<th>テストサーバー</th>\n";
		$html .= "<th>".$_SESSION['select_db']['NAME']."</th>\n";
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
		$html .= "スキルが設定されておりません。<br>\n";
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

	unset($SKILL_NUM);
	//	データーベース更新
	$sql  = "SELECT * FROM ".T_SKILL.
			" WHERE state!='1' AND course_num='".$_POST['course_num']."'".
			" ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		$flag = 0;
		while ($list = $cdb->fetch_assoc($result)) {
			if ($list) {
				if ($INSERT_VALUES) { $INSERT_VALUES .= ","; }
				unset($INSERT_VALUE);
				foreach ($list AS $key => $val) {
					if ($flag != 1) {
						if ($COLUMN_NAME) { $COLUMN_NAME .= ","; }
						$COLUMN_NAME .= $key;
					}
					if ($INSERT_VALUE) { $INSERT_VALUE .= ","; }
					$INSERT_VALUE .= "'".$val."'";
					if ($key == "skill_num") { $SKILL_NUM[$val] = $val; }
				}
				if ($COLUMN_NAME) { $flag = 1; }
				$INSERT_VALUES .= " (".$INSERT_VALUE.")";
			}
		}
	}

	//	トランザクション開始
	$sql  = "BEGIN";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL BEGIN ERROR";
		$connect_db->close();
		return $ERROR;
	}

	//	検証バッチDBデーター削除
	$sql = "DELETE FROM ".T_SKILL." WHERE course_num='".$_POST['course_num']."';";
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
		$sql  = "ROLLBACK";
		if (!$connect_db->exec_query($sql)) {
			$ERROR[] = "SQL ROLLBACK ERROR";
		}
		$connect_db->close();
		return $ERROR;
	}

	//	検証バッチDBデーター追加
	if ($COLUMN_NAME && $INSERT_VALUES) {
		$sql = "INSERT INTO ".T_SKILL." (".$COLUMN_NAME.") VALUES ".$INSERT_VALUES.";";
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

	//	トランザクションコミット
	$sql  = "COMMIT";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL COMMIT ERROR";
		$connect_db->close();
		return $ERROR;
	}

	//	テーブル最適化
	$sql = "OPTIMIZE TABLE ".T_SKILL.";";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
	}

	//	検証バッチDB切断
	$connect_db->close();


	//	スキル音声ファイル
	$local_dir = BASE_DIR."/www".ereg_replace("^../","/",MATERIAL_SKILL_VOICE_DIR).$_POST['course_num'];
	$remote_dir = KBAT_DIR.REMOTE_MATERIAL_SKILL_VOICE_DIR;

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


	//	スキル画像ファイル
	$local_dir = BASE_DIR."/www".ereg_replace("^../","/",MATERIAL_SKILL_IMG_DIR).$_POST['course_num'];
	$remote_dir = KBAT_DIR.REMOTE_MATERIAL_SKILL_IMG_DIR;

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
	// $command = "ssh suralacore01@srlbtw21 ./CONTENTSUP.cgi '2' '".MODE."' '".$_POST['course_num']."'"; // del 2018/03/20 yoshizawa AWSプラクティスアップデート
	$command = "/usr/bin/php ".BASE_DIR."/_www/batch/CONTENTSUP.cgi '2' '".MODE."' '".$_POST['course_num']."'"; // add 2018/03/20 yoshizawa AWSプラクティスアップデート

	//upd start 2017/11/27 yamaguchi AWS移設
	//exec($command,&$LIST);
	exec($command,$LIST);
	//upd end 2017/11/27 yamaguchi


	//	ログ保存 --
	unset($mate_upd_log_num);
	$sql  = "SELECT mate_upd_log_num FROM mate_upd_log".
			" WHERE update_mode='".MODE."'".
			" AND course_num='".$_POST['course_num']."'".
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

	$html  = $L_NAME['course_name']."のスキル情報のアップが完了致しました。<br>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"back\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\"><br>\n";
	$html .= "</form>\n";

	return $html;
}
?>