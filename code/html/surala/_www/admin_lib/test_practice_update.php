<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * テスト用プラクティスステージ管理
 * 	プラクティスアップデートプログラム
 *
 * @author Azet
 */

//	mode種類
$L_TEST_UPDATE_MODE = array(
							'default' => '----------',
							'publishing' => '出版社',
							'book' => '教科書',
							'book_unit' => '教科書単元',
							'lms_problem_time' => '小テスト回答目安時間',				// update oda 2016/11/11 課題要望一覧No566 文言修正
							'standard_category' => '標準化カテゴリ',				// add okabe 2020/09/04 テスト標準化
							'book_trial_list' => '学力診断テストマスタ',
							'book_trial_group' => '学力診断テストグループ',
							'book_trial' => '学力診断テスト単元マスタ',
							'book_trial_unit' => '学力診断テスト単元',
							'hantei_test_master' => '判定テストマスタ',				// add okabe 2013/06/24
							'math_test_book_info' => '数学検定情報',					// add okabe 2020/09/04 テスト標準化
							'math_test_group' => '数学検定グループ',					// add yoshizawa 2015/09/16 02_作業要件/34_数学検定/数学検定
							'math_test_control_unit' => '数学検定出題単元',			// add yoshizawa 2015/09/16 02_作業要件/34_数学検定/数学検定
							'math_test_unit' => '数学検定採点単元',					// add yoshizawa 2015/09/16 02_作業要件/34_数学検定/数学検定
							'vocabulary_test_type' => 'すらら英単語種類',				// add yoshizawa 2018/11/15 すらら英単語 // del 2018/12/01 yoshizawa 運用開始までは一時コメントにします。 // update 2019/04/02 yoshizawa プラクティスアップデートを開放
							'vocabulary_test_category1' => 'すらら英単語種別1',		// add yoshizawa 2018/11/15 すらら英単語 // del 2018/12/01 yoshizawa 運用開始までは一時コメントにします。 // update 2019/04/02 yoshizawa プラクティスアップデートを開放
							'vocabulary_test_category2' => 'すらら英単語種別2',		// add yoshizawa 2018/11/15 すらら英単語 // del 2018/12/01 yoshizawa 運用開始までは一時コメントにします。 // update 2019/04/02 yoshizawa プラクティスアップデートを開放
							'english_word' => 'ワード管理',							// add 2019/05/21 yoshizawa すらら英単語テストワード管理
							'problem' => '問題設定(定期テスト)',
							'problem_trial' => '問題設定(学力診断テスト)',
							'problem_hantei_test' => '問題設定(判定テスト)',			// add okabe 2013/06/24
							'problem_math' => '問題設定(数学検定)',					// add yoshizawa 2015/09/16 02_作業要件/34_数学検定/数学検定
							'problem_vocabulary' => '問題設定(すらら英単語テスト)',		// add yoshizawa 2018/11/15 すらら英単語 // del 2018/12/01 yoshizawa 運用開始までは一時コメントにします。 // update 2019/04/02 yoshizawa プラクティスアップデートを開放
							'ms_core_code' => 'コードマスター',
							'upnavi' => '学力Upナビ単元'								// add ookawara 2012/07/23
						);

/**
 * HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function start() {
//if ($_SESSION['myid']['id'] == "az-okabe") { echo ", MODE=".MODE; }	//test add okabe 2020/09/04 //@@@

	$html = "";

	//	アップロードモード選択
	$html .= select_mode();

	//	モード読込設定
	$include_file = LOG_DIR."admin_lib/".MAIN."_".SUB."_".MODE.".php";
	if (file_exists($include_file)) {
		include($include_file);
		$html .= action();
	} else {
		$html .= "<div style=\"margin-top:10px;\">\n";
		$html .= "アップデートコンテンツを選択してください。<br />\n";
		$html .= "<br />\n";
		$html .= "</div>\n";
	}

	return $html;
}


/**
 * 	アップロードモード選択
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function select_mode() {
	global $L_TEST_UPDATE_MODE;

	$html = "";

	$mode = "default";
	if (defined("MODE")) {
		$mode = MODE;
	}

	$html .= "<div style=\"margin-top:10px;\">\n";
	$html .= "<form action=\"\" method=\"POST\">\n";
	$html .= "アップデートコンテンツ：\n";
	$html .= "<select name=\"mode\" OnChange=\"submit();\">\n";
	foreach ($L_TEST_UPDATE_MODE AS $key => $val) {
		$selected = "";
		if ($mode == $key) { $selected = "selected"; }
		$html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
	}
	$html .= "</select>\n";
	$html .= "</form>\n";
	$html .= "</div>\n";
	$html .= "<br />\n";

	return $html;
}


/**
 * 閲覧DB選択メニュー
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function select_db_menu() {
	global $L_CONTENTS_DB;

	$html = "";

	foreach ($L_CONTENTS_DB AS $key => $val){
		$sel = "";
		if ($_SESSION['select_db'] == $val) {
			$sel = " selected";
		}
		$s_db_html .= "<option value=\"".$key."\"$sel>".$val['NAME']."</option>\n";
	}

	$html = "閲覧DB：<select name=\"s_db_sel\" onchange=\"submit();\">\n";
	$html .= $s_db_html;
	$html .= "</select>\n";

	return $html;
}


/**
 * SESSIONにDBの情報を設定する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function select_database() {
	global $L_CONTENTS_DB;

	if (strlen($_POST['s_db_sel'])) {
		$_SESSION['select_db'] = $L_CONTENTS_DB[$_POST['s_db_sel']];
	}

	if ($_POST['s_db_sel'] == '0') {
		unset($_SESSION['select_db']);
	}

	return;
}


/**
 * 閲覧web選択メニュー
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function select_web_menu() {

	global $L_CONTENTS_WEB;

	$html = "";

	foreach ($L_CONTENTS_WEB AS $key => $val){
		$sel = "";
		if ($_SESSION['select_web'] == $val) {
			$sel = " selected";
		}
		$s_web_html .= "<option value=\"".$key."\"$sel>".$val['NAME']."</option>\n";
	}

	$html  = "閲覧Web：<select name=\"s_web_sel\" onchange=\"submit();\">\n";
	$html .= $s_web_html;
	$html .= "</select>\n";

	return $html;
}


/**
 * 閲覧web設定
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function select_web() {
	global $L_CONTENTS_WEB;

	if (strlen($_POST['s_web_sel'])) {
		$_SESSION['select_web'] = $L_CONTENTS_WEB[$_POST['s_web_sel']];
	}

	if ($_POST['s_web_sel'] == '0') {
		unset($_SESSION['select_web']);
	}

	return;
}




/**
 * ローカルサーバーファイル情報取得
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $PROBLEM_LIST
 * @param array &$LOCAL_FILES
 * @param integer &$last_local_time
 * @param mixed $dir (未使用)
 */
function test_local_glob($PROBLEM_LIST, &$LOCAL_FILES, &$last_local_time, $dir) {

	if (!$PROBLEM_LIST) { return; }

	foreach ($PROBLEM_LIST AS $problem_num) {
		$dir_num = (floor($problem_num / 100) * 100) + 1;
		//	add ookawara 2012/12/07 start
		$amari = $problem_num % 100;
		if ($amari < 1) {
			$dir_num -= 100;
		}
		//	add ookawara 2012/12/07 end
		$dir_num = sprintf("%07d",$dir_num);
		$dir_name = $dir.$dir_num."/";
		$dir_name_ = preg_replace("/\//", "\/", $dir_name);
		$set_file_name = $dir_name.$problem_num."_*.*";
		foreach (glob("$set_file_name") AS $file_name) {
			$file_stamp = filemtime($file_name);
			if ($last_local_time < $file_stamp) {
				$last_local_time = $file_stamp;
			}
			$name = preg_replace("/$dir_name_/", "", $file_name);
			$LOCAL_FILES['f'][$name] = $file_stamp;
			$LOCAL_FILES['df'][$dir_num][] = $name;
		}
	}

}


/**
 * 閲覧サーバーファイル情報取得
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $LOCAL_FILES
 * @param integer &$last_remote_time
 * @param integer &$last_remote_cnt
 * @param array $select_web
 * @param string $dir
 */
function test_remote_dir_time($LOCAL_FILES, &$last_remote_time, &$last_remote_cnt, $select_web, $dir) {
	global $L_CONTENTS_WEB;

	if (!$LOCAL_FILES) { return; }

	foreach ($LOCAL_FILES['df'] AS $dir_num => $name) {
		$LIST = array();
		$server_name = $select_web['SERVERNAME'];
		$dir_type = $select_web['DIRTYPE'];

		// update start 2018/04/05 yoshizawa AWS対応
		// if ($dir_type == "2") {
		// 	$set_dir = KBAT_DIR.$dir.$dir_num."/";
		// 	//upd start 2017/11/27 yamaguchi AWS移設
		// 	//exec("ssh suralacore01@srlbtw21 ./ACREADDIR2 '".$set_dir."'",&$LIST);
		// 	exec("ssh suralacore01@srlbtw21 ./ACREADDIR2 '".$set_dir."'",$LIST);
		// 	//upd end 2017/11/27 yamaguchi
		// } elseif ($dir_type == "3") {
		// 	$set_dir = HBAT_DIR.$dir.$dir_num."/";
		// 	//upd start 2017/11/27 yamaguchi AWS移設
		// 	//exec("ssh suralacore01@srlbtw21 ssh suralacore01@".$server_name." ./ACREADDIR2 '".$set_dir."'",&$LIST);
		// 	exec("ssh suralacore01@srlbtw21 ssh suralacore01@".$server_name." ./ACREADDIR2 '".$set_dir."'",$LIST);
		// 	//upd end 2017/11/27 yamaguchi
		// } else {
		// 	$set_dir = REMOTE_BASE_DIR.$dir.$dir_num."/";
		// 	//upd start 2017/11/27 yamaguchi AWS移設
		// 	//exec("ssh suralacore01@srlbtw21 ssh suralacore01@".$server_name." ./ACREADDIR2 '".$set_dir."'",&$LIST);
		// 	exec("ssh suralacore01@srlbtw21 ssh suralacore01@".$server_name." ./ACREADDIR2 '".$set_dir."'",$LIST);
		// 	//upd end 2017/11/27 yamaguchi
		// }
		// 
		if ($dir_type == "2") { // リリースフォルダー
			$set_dir = KBAT_DIR.$dir.$dir_num."/";
			exec("/data/home/_www/batch/ACREADDIR2 '".$set_dir."'",$LIST);
		} elseif ($dir_type == "3") {// バッチフォルダー本番 // 未使用
			$set_dir = HBAT_DIR.$dir.$dir_num."/";
			exec("ssh suralacore01@".$server_name." /data/home/_www/batch/ACREADDIR2 '".$set_dir."'",$LIST);
		} else { // リモートサーバー
			$set_dir = REMOTE_BASE_DIR.$dir.$dir_num."/";
			exec("ssh suralacore01@".$server_name." /data/home/_www/batch/ACREADDIR2 '".$set_dir."'",$LIST);
		}
		// update end 2018/04/05 yoshizawa AWS対応

		if (!$LIST) { continue; }

		$hiku_time = 0;
		if ($L_CONTENTS_WEB['TIME'] != "0") {
			$hiku_time = $L_CONTENTS_WEB['TIME'] * 60 * 60;
		}
		mb_convert_variables("UTF-8", "sjis-win", $LIST);
		foreach ($LIST AS $LINE) {
			$LINE = preg_replace("/\s\s+/"," ",$LINE);
			$DATA_LIST = explode(" ",$LINE);

			$file_num = $DATA_LIST[1];
			$file_type = "f";
			if ($file_num > 1) {
				continue;
			}

			$file_name = $DATA_LIST[8];
			if (count($DATA_LIST) > 9) {
				$last_num = count($DATA_LIST);
				for ($i=9; $i<$last_num; $i++) {
					$file_name .= " ".$DATA_LIST[$i];
				}
			}
			if (preg_match("/\*$/", $file_name) || preg_match("/\@$/", $file_name)) {
				continue;
			} elseif (array_search($file_name, $LOCAL_FILES['df'][$dir_num]) === FALSE) {
				continue;
			}

			$file_time_day = $DATA_LIST[5];
			$file_time_hour = $DATA_LIST[6];
			list($year, $mon, $day) = split("-", $file_time_day);
			list($hour, $min, $sec) = split(":", $file_time_hour);
			$file_stamp = mktime($hour, $min, $sec, $mon, $day, $year) - $hiku_time;

			if ($last_remote_time < $file_stamp) {
				$last_remote_time = $file_stamp;
			}
			$last_remote_cnt += 1;
		}
	}
}



/**
 * リモートサーバーフォルダー作成
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param string $dir
 * @param array $FILE_NAME
 * @param array &$ERROR
 */
function test_remote_set_dir($dir, $FILE_NAME, &$ERROR) {

	//	作成
	if ($FILE_NAME) {
		foreach ($FILE_NAME['df'] AS $key => $val) {
			if ($key == "") {
				countinue;
			}
			$make_dir = $dir.$key;
			//$command = "ssh suralacore01@srlbtw21 mkdir -p ".$make_dir; // del 2018/03/20 yoshizawa AWSプラクティスアップデート
			$command = "mkdir -p ".$make_dir; // add 2018/03/20 yoshizawa AWSプラクティスアップデート
			exec("$command", $LIST);
		}
	}

	return;
}


/**
 * リモートサーバーファイルアップロード
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param string $local_dir
 * @param string $remote_dir
 * @param array $FILE_NAME
 * @param array &$ERROR
 */
function test_remote_set_file($local_dir, $remote_dir, $FILE_NAME, &$ERROR) {

	//	ファイルアップロード
	if ($FILE_NAME['df']) {
		$COM_LIST = array();
		foreach ($FILE_NAME['df'] AS $key => $FILE_LIST) {
			if ($FILE_LIST) {
				$i = 0;
				$com_line = "";
				foreach ($FILE_LIST AS $file_name) {
					$local_file = $local_dir.$key."/".$file_name;
					$remote_file_dir = $remote_dir.$key."/";
					//$com_line .= "scp -rp '".$local_file."' suralacore01@srlbtw21:'".$remote_file_dir."'\n"; // del 2018/03/20 yoshizawa AWSプラクティスアップデート
					$com_line .= "cp -rp '".$local_file."' '".$remote_file_dir."'\n"; // add 2018/03/20 yoshizawa AWSプラクティスアップデート
					$i++;
					if ($i >= 20) {
						$COM_LIST[] = $com_line;
						$com_line = "";
						$i = 0;
					}
				}
				if ($com_line) {
					$COM_LIST[] = $com_line;
				}
			}
		}
		if (count($COM_LIST) > 0) {
			foreach ($COM_LIST AS $com_line) {

				//	add ookawara 2012/08/20
				echo "・";
				flush();

				//upd start 2017/11/27 yamaguchi AWS移設
				//exec("$com_line", &$LIST);
				exec("$com_line", $LIST);
				//upd end 2017/11/27 yamaguchi
			}

			//	add ookawara 2012/08/20
			echo "<br>\n";
			flush();

		}
	}

	return;
}


/**
 * インサートクエリー作成
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param object $result
 * @param string $table_name
 * @param array &$INSERT_NAME
 * @param array &$INSERT_VALUE
 */
function make_insert_query($result, $table_name, &$INSERT_NAME, &$INSERT_VALUE) {

	// 分散DB接続オブジェクト
	// ※プラクティスアップするデータをCONTENT(160)から取得します。
	$cdb = $GLOBALS['cdb'];

	$flag = 0;
	$i = 0;
	$num = 0;
	$insert_name = "";
	$insert_value = "";
	while ($list=$cdb->fetch_assoc($result)) {
		$value = "";
		if ($insert_value) { $insert_value .= ", "; }

		foreach ($list AS $key => $val) {
			if ($flag != 1) {
				if ($insert_name) { $insert_name .= ","; }
				$insert_name .= $key;
			}
			if ($value) { $value .= ","; }
			$value .= "'".addslashes($val)."'";
		}
		if ($value) {
			$insert_value .= "(".$value.")";
			$i++;
		}
		if ($insert_name) { $flag = 1; }
		if ($i == 50) {
			$INSERT_VALUE[$table_name][] = $insert_value;
			$num++;
			$i = 1;
			$insert_value = "";
		}
	}
	if ($insert_name) { $INSERT_NAME[$table_name] = $insert_name; }
	if ($insert_value) { $INSERT_VALUE[$table_name][] = $insert_value; }

}


/**
 * 閲覧webサーバーフォルダー内情報読み込み
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $select_web
 * @param string $dir
 * @return string
 */
function remote_read_dir($select_web,$dir) {
	global $L_CONTENTS_WEB;

	unset($LIST);
	$server_name = $select_web['SERVERNAME'];
	$dir_type = $select_web['DIRTYPE'];
	// update start 2018/04/05 yoshizawa AWS対応
	// if ($dir_type == "2") {
	// 	$set_dir = KBAT_DIR.$dir;
	// 	//upd start 2017/11/27 yamaguchi AWS移設
	// 	//exec("ssh suralacore01@srlbtw21 ./ACREADDIR2 '".$set_dir."'",&$LIST);
	// 	exec("ssh suralacore01@srlbtw21 ./ACREADDIR2 '".$set_dir."'",$LIST);
	// 	//upd end 2017/11/27 yamaguchi
	// } elseif ($dir_type == "3") {
	// 	$set_dir = HBAT_DIR.$dir;
	// 	//upd start 2017/11/27 yamaguchi AWS移設
	// 	//exec("ssh suralacore01@srlbtw21 ssh suralacore01@".$server_name." ./ACREADDIR2 '".$set_dir."'",&$LIST);
	// 	exec("ssh suralacore01@srlbtw21 ssh suralacore01@".$server_name." ./ACREADDIR2 '".$set_dir."'",$LIST);
	// 	//upd end 2017/11/27 yamaguchi
	// } else {
	// 	$set_dir = REMOTE_BASE_DIR.$dir;
	// 	//upd start 2017/11/27 yamaguchi AWS移設
	// 	//exec("ssh suralacore01@srlbtw21 ssh suralacore01@".$server_name." ./ACREADDIR2 '".$set_dir."'",&$LIST);
	// 	exec("ssh suralacore01@srlbtw21 ssh suralacore01@".$server_name." ./ACREADDIR2 '".$set_dir."'",$LIST);
	// 	//upd end 2017/11/27 yamaguchi
	// }
	// 
	if ($dir_type == "2") {
		$set_dir = KBAT_DIR.$dir;
		exec("/data/home/_www/batch/ACREADDIR2 '".$set_dir."'",$LIST);
	} elseif ($dir_type == "3") {
		$set_dir = HBAT_DIR.$dir;
		exec("ssh suralacore01@".$server_name." /data/home/_www/batch/ACREADDIR2 '".$set_dir."'",$LIST);
	} else {
		$set_dir = REMOTE_BASE_DIR.$dir;
		exec("ssh suralacore01@".$server_name." /data/home/_www/batch/ACREADDIR2 '".$set_dir."'",$LIST);
	}
	// update end 2018/04/05 yoshizawa AWS対応

	mb_convert_variables("UTF-8", "sjis-win", $LIST);
	if ($LIST) {
		$hiku_time = 0;
		if ($L_CONTENTS_WEB['TIME'] != "0") {
			$hiku_time = $L_CONTENTS_WEB['TIME'] * 60 * 60;
		}
		foreach ($LIST AS $LINE) {
			$LINE = preg_replace("/\s\s+/"," ",$LINE);
			$DATA_LIST = explode(" ",$LINE);

			$file_num = $DATA_LIST[1];
			$file_type = "f";
			if ($file_num > 1) {
				$file_type = "d";
			}

			$file_name = $DATA_LIST[8];
			if (preg_match("/\*$/", $file_name) || preg_match("/\@$/", $file_name)) {
				continue;
			}
			if ($file_type == "d") {
				$file_name = preg_replace("/\/$/", "", $file_name);
			}

			$file_time_day = $DATA_LIST[5];
			$file_time_hour = $DATA_LIST[6];
			list($year, $mon, $day) = split("-", $file_time_day);
			list($hour, $min, $sec) = split(":", $file_time_hour);
			$file_stamp = mktime($hour, $min, $sec, $mon, $day, $year) - $hiku_time;

			$FILE_NAME[$file_type][$file_name] = $file_stamp;
		}
	}

	return $FILE_NAME;
}


/*
// -- ローカルサーバーフォルダー内情報読み込み
function local_read_dir($dir) {

	if (file_exists($dir)) {
		$strDir = opendir($dir);
		while($strFle=readdir($strDir)) {
			if (ereg("^\.$|^\.\.$",$strFle)) { continue; }
			$file_name = $strFle;
			$file_type = filetype($dir.$strFle);
			if ($file_type == "file") {
				$file_type = "f";
			} elseif ($file_type == "dir") {
				$file_type = "d";
			}
			$file_stamp = filemtime($dir.$strFle);
			$file_name = mb_convert_encoding($file_name,"UTF-8","sjis-win");
			$LOCAL_FILE_NAME[$file_type][$file_name] = $file_stamp;
		}
	}

	return $LOCAL_FILE_NAME;
}


// -- リモートサーバーフォルダー内最新ファイル時間
function remote_read_dir_time($select_web,$dir) {

	$FILE_NAME = remote_read_dir($select_web,$dir);
	if ($FILE_NAME['f']) {
		foreach ($FILE_NAME['f'] AS $key => $val) {
			if ($remote_time < $val) { $remote_time = $val; }
		}
	}

	if ($FILE_NAME['d']) {
		foreach ($FILE_NAME['d'] AS $key => $val) {
			if ($key == "") { continue; }
			$sub_dir = $dir.$key."/";
			$sub_remote_time = remote_read_dir_time($select_web,$sub_dir);
		}
		if ($remote_time < $sub_remote_time) { $remote_time = $sub_remote_time; }
	}

	return $remote_time;
}


// -- ローカルサーバーフォルダー内最新ファイル時間
function local_read_dir_time($dir) {

	$LOCAL_FILE_NAME = local_read_dir($dir);
	if ($LOCAL_FILE_NAME['f']) {
		foreach ($LOCAL_FILE_NAME['f'] AS $key => $val) {
			if ($local_time < $val) { $local_time = $val; }
		}
	}

	if ($LOCAL_FILE_NAME['d']) {
		foreach ($LOCAL_FILE_NAME['d'] AS $key => $val) {
			if ($key == "") { continue; }
			$sub_dir = $dir.$key."/";
			$sub_local_time = local_read_dir_time($sub_dir);
		}
		if ($local_time < $sub_local_time) { $local_time = $sub_local_time; }
	}

	return $local_time;
}


// -- リモートサーバーフォルダー内ファイルアップロード
function remote_set_dir_file($ftp,$dir_name,$LIST_NUM,$local_dir,$remote_dir) {

	//	リモートサーバーフォルダー確認＆作成
	unset($FILE_NAME);
	//	リモートサーバーファイルチェック
	$FILE_NAME = remote_read_dir($ftp,$remote_dir);
	//	リモートサーバーフォルダー作成＆削除
	list($ERROR) = remote_set_dir($ftp,$remote_dir,$LIST_NUM,$FILE_NAME);
	if ($ERROR) { return $ERROR; }
	//	ファイルアップロード
	unset($FILE_NAME);
	$set_local_dir = $local_dir.$dir_name."/";
	$set_remote_dir = $remote_dir.$dir_name."/";

	//	リモートサーバーフォルダー内情報読み込み
	$FILE_NAME = remote_read_dir($ftp,$set_remote_dir);

	//	リモートサーバーフォルダー内情報読み込みファイルアップロード＆削除
	list($ERROR,$LOCAL_FILE_NAME) = remote_set_file($ftp,$set_local_dir,$set_remote_dir,$FILE_NAME);
	if ($ERROR) { return $ERROR; }

	//	アップフォルダー内にフォルダーが存在した場合
	if ($LOCAL_FILE_NAME['d']) {
		foreach ($LOCAL_FILE_NAME['d'] AS $key => $val) {
			$sub_dir_name = $key;
			$sub_local_dir = $set_local_dir;
			$sub_remote_dir = $set_remote_dir;
			$ERROR = remote_set_dir_file($ftp,$sub_dir_name,$LOCAL_FILE_NAME['d'],$sub_local_dir,$sub_remote_dir);
			if ($ERROR) { return $ERROR; }
		}
	}

	return $ERROR;
}

//	ローカルサーバーファイルリスト作成
function local_file_make_list($LOCAL_FILE_NAME,$local_dir,$add_dir) {

	if ($LOCAL_FILE_NAME[f]) {
		@ksort($LOCAL_FILE_NAME["f"],SORT_STRING);
		foreach ($LOCAL_FILE_NAME["f"] AS $key => $val) {
			$html .= "<tr class=\"course_form_cell\">\n";
			$html .= "<td>".$add_dir.$key."</td>\n";
			$html .= "<td>".date("Y/m/d H:i",$val)."</td>\n";
			$html .= "</tr>\n";
		}
	}

	if ($LOCAL_FILE_NAME[d]) {
		@ksort($LOCAL_FILE_NAME["d"],SORT_STRING);
		foreach ($LOCAL_FILE_NAME["d"] AS $key => $val) {
			if ($key == "") { continue; }
			$html .= "<tr class=\"course_form_cell\">\n";
			$html .= "<td>".$add_dir.$key."/</td>\n";
			$html .= "<td>".date("Y/m/d H:i",$val)."</td>\n";
			$html .= "</tr>\n";

			$next_local_dir = $local_dir.$add_dir.$key."/";
			$NEXT_LOCAL_FILE_NAME = local_read_dir($next_local_dir);
			$html .= local_file_make_list($NEXT_LOCAL_FILE_NAME,$local_dir,$add_dir.$key."/");
		}
	}

	return $html;
}


//	リモートサーバーファイルリスト作成
function remote_file_make_list($select_web,$FILE_NAME,$remote_dir,$add_dir) {

	if ($FILE_NAME['f']) {
		@ksort($FILE_NAME['f'],SORT_STRING);
		foreach ($FILE_NAME['f'] AS $key => $val) {
			$html .= "<tr class=\"course_form_cell\">\n";
			$html .= "<td>".$add_dir.$key."</td>\n";
			$html .= "<td>".date("Y/m/d H:i",$val)."</td>\n";
			$html .= "</tr>\n";
		}
	}

	if ($FILE_NAME['d']) {
		@ksort($FILE_NAME['d'],SORT_STRING);
		foreach ($FILE_NAME['d'] AS $key => $val) {
			if ($key == "") { continue; }
			$html .= "<tr class=\"course_form_cell\">\n";
			$html .= "<td>".$add_dir.$key."/</td>\n";
			$html .= "<td>".date("Y/m/d H:i",$val)."</td>\n";
			$html .= "</tr>\n";

			$next_remote_dir = $remote_dir.$add_dir.$key."/";
			$NEXT_FILE_NAME = remote_read_dir($select_web,$next_remote_dir);
			$html .= remote_file_make_list($select_web,$NEXT_FILE_NAME,$remote_dir,$add_dir.$key."/");
		}
	}

	return $html;
}
*/
?>
