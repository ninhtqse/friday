<?PHP
/**
 * すららネット
 *
 * プラクティスステージ管理　プラクティスアップデートプログラム
 * 	サブプログラム	レクチャーhtml5共通部品アップデート
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
	} elseif (ACTION == "web_session") {
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
 * ローカルサーバーファイルリスト作成
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $LOCAL_FILE_NAME ファイル情報:配列f->ファイル 配列d->フォルダ
 * @param string $local_dir テストサーバーの参照フォルダパス
 * @param string $add_dir フォルダアップパス
 * @return string HTML
 */
function local_file_make_list_private($LOCAL_FILE_NAME,$local_dir,$add_dir) {

	// 変数初期化
	$RESULT = array();
	$first_hierarchy = array();

	// ファイルの日時とファイル名を格納
	if ($LOCAL_FILE_NAME['f']) {
		@ksort($LOCAL_FILE_NAME["f"],SORT_STRING);
		foreach ($LOCAL_FILE_NAME["f"] AS $key => $val) {
			$RESULT['f::'.$key] = $val;
		}
	}

	// フォルダの日時とファイル名を格納
	if ($LOCAL_FILE_NAME['d']) {
		@ksort($LOCAL_FILE_NAME["d"],SORT_STRING);
		foreach ($LOCAL_FILE_NAME["d"] AS $key => $val) {
			if ($key == "") { continue; }

			$first_hierarchy[$key] = local_read_dir($local_dir.$key.'/');
			//２階層目にファイルもフォルダもなかったら、配列からなくす。
			if(!$first_hierarchy[$key]){
				unset($first_hierarchy[$key]);
			}
		}
		foreach ($first_hierarchy as $name => $info) {
			if($info['f']){
				foreach($info['f'] as $next_dir => $time ){
					$dir = $name."/".$next_dir;
					$RESULT['f::'.$dir] = $time;
				}
			}
			if($info['d']){
				foreach($info['d'] as $next_dir => $time ){
					$dir = $name."/".$next_dir;
					//3階層目にファイルもフォルダもなかったら、配列からなくす。
					$kara_check = local_read_dir($local_dir.$dir.'/');
					if($kara_check){
						$RESULT['d::'.$dir] = local_read_dir_time($local_dir.$dir.'/');
					}
				}
			}
		}

	}
	ksort($RESULT);
	$i = 0;
	$html = "";
	foreach ($RESULT as $dir => $time) {
		$file = '';
		$match = array();
		if(preg_match('/^([fd]{1})::(.*)/',$dir,$match)){
			$file = $match[1].'_';
			$dir = $match[2];
		}
		$html .= "<tr class=\"course_form_cell\">\n";
		$html .= "<td><INPUT type = \"checkbox\" name=\"update_dir[".$file.$i++."]\" value=\"$dir\"></td>\n";
		$html .= "<td>".$add_dir.$dir."</td>\n";
		$html .= "<td>".date("Y/m/d H:i",$time)."</td>\n";
		$html .= "</tr>\n";
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

	//確認するディレクトリ名の配列
	$add_dir = 'flash/common/';

	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"del_form_all\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"".$_POST['mode']."\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"web_session\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
	$html .= select_web_menu();
	$html .= "</form>\n";

	//サーバー情報取得
	if (!$_SESSION['select_web']) { return $html; }

	//	ローカルサーバー
	$local_dir = MATERIAL_FLASH_DIR."common/";
//	$local_dir = MATERIAL_FLASH_DIR."common_test/";		//debug用後で戻す！
	$LOCAL_FILE_NAME = local_read_dir($local_dir);

	$local_max = count($LOCAL_FILE_NAME);
	if ($local_max) {
		//	ローカルサーバーフォルダー内最新ファイル時間
		$local_time = local_read_dir_time($local_dir);

		$local_html .= "最新更新時間：".date("Y/m/d H:i:s",$local_time);
		$local_html .= "<table class=\"course_form common-up-list\">\n";
		$local_html .= "<tr class=\"course_form_menu\">\n";
		$local_html .= "<th></th>\n";
		$local_html .= "<th>ファイル名</th>\n";
		$local_html .= "<th>更新時間</th>\n";
		$local_html .= "</tr>\n";

		//	ローカルサーバーファイルリスト作成
		$local_html .= local_file_make_list_private($LOCAL_FILE_NAME,$local_dir,$add_dir);

		$local_html .= "</table>\n";
	} else {
		$local_html = "ファイルがアップロードされておりません。";
	}


	// -- リモートサーバー
	$remote_dir = REMOTE_MATERIAL_FLASH_DIR."common/";
//	$remote_dir = REMOTE_MATERIAL_FLASH_DIR."common_test/";		//debug用後で戻す！
	$FILE_NAME = remote_read_dir($_SESSION['select_web'],$remote_dir);
	$remote_max = count($FILE_NAME);
	if ($remote_max) {
		// -- リモートサーバーフォルダー内最新ファイル時間
		list ($html_table, $max_time) = remote_file_make_list_two_hierarchy($_SESSION['select_web'],$FILE_NAME,$remote_dir,$add_dir);

		$remote_html .= "最新更新時間：".date("Y/m/d H:i:s",$max_time);
		$remote_html .= "<table class=\"course_form common-up-list\">\n";
		$remote_html .= "<tr class=\"course_form_menu\">\n";
		$remote_html .= "<th>ファイル名</th>\n";
		$remote_html .= "<th>更新時間</th>\n";
		$remote_html .= "</tr>\n";

		//	リモートサーバーファイルリスト作成
		$remote_html .= $html_table;

		$remote_html .= "</table>\n";
	} else {
		$remote_html = "ファイルがアップロードされておりません。";
	}

	if ($ERROR) {
		$html  = ERROR($ERROR);
		$html .= "<br>\n";
	}


	if ($local_html || $remote_html) {
		$submit_msg = "レクチャーcommonフォルダを検証へアップしますがよろしいですか？";

		$html .= "レクチャーcommonフォルダをアップする場合は、対象のフォルダをチェック後、「アップする」ボタンを押してください。<br>\n";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"del_form_all\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"update\">\n";
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"common_dir\" value=\"".$add_dir."\">\n";
		$html .= "<input type=\"submit\" value=\"アップする\" onClick=\"return confirm('".$submit_msg."')\"><br>\n";
		$html .= "<table border=\"0\" cellspacing=\"1\" bgcolor=\"#666666\" cellpadding=\"3\">\n";
		$html .= "<tr bgcolor=\"#cccccc\">\n";
		$html .= "<th>テストサーバー</th>\n";
		$html .= "<th>".$_SESSION['select_web']['NAME']."</th>\n";
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
		$html .= "レクチャーcommonフォルダがアップロードされておりません。<br>\n";
	}

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

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$LIST = array();

	if($_POST['update_dir'] && $_POST['common_dir']){
		foreach ($_POST['update_dir'] as $key => $update_dir) {
			//アップするのがファイルかフォルダか判断
			$file_type = '';
			$match = array();
			if(preg_match('/(^[fd]{1})_.*/',$key,$match)){
				$file_type = $match[1];
			}

			$INSERT_DATA = array();
			//コピーコマンドはコピーするファイルの一つ上の階層までを指定するため、1階層目のみを取得する。
			$update_dir_remote = "";
			$upd_dir_remote = explode("/", $update_dir);
			if($upd_dir_remote[1]){
				$update_dir_remote = $upd_dir_remote[0].'/';
			}

			$remove_dir = KBAT_DIR.REMOTE_MATERIAL_FLASH_DIR."common/".$update_dir;
//			$remove_dir = KBAT_DIR.REMOTE_MATERIAL_FLASH_DIR."common_test/".$update_dir;
			$local_dir = BASE_DIR."/www".preg_replace("/^..\//","/",MATERIAL_FLASH_DIR)."common/".$update_dir;
//			$local_dir = BASE_DIR."/www".preg_replace("/^..\//","/",MATERIAL_FLASH_DIR)."common_test/".$update_dir;
			$remote_dir = KBAT_DIR.REMOTE_MATERIAL_FLASH_DIR."common/".$update_dir_remote;
//			$remote_dir = KBAT_DIR.REMOTE_MATERIAL_FLASH_DIR."common_test/".$update_dir_remote;

			//コピーするものが、ファイルだったらコピーコマンドのみ実行
			if($file_type == 'f'){
				$command1 = "mkdir -p ".$remote_dir;
				$command2 = "cp -p ".$local_dir." ".$remote_dir;
// echo "command1=".$command1."<br>";
// echo "command2=".$command2."<br>";
				exec("$command1",$LIST);
				exec("$command2",$LIST);
			}elseif($file_type == 'd'){
				$remove_command = "rm -rf ".$remove_dir;
				$command1 = "mkdir -p ".$remote_dir;
				$command2 = "cp -rp ".$local_dir." ".$remote_dir;
// echo "remove_command=".$remove_command."<br>";
// echo "command1=".$command1."<br>";
// echo "command2=".$command2."<br>";

				// 反映先のレクチャーを削除してからローカルのレクチャーをアップする。
				exec("$remove_command",$LIST);
				exec("$command1",$LIST);
				exec("$command2",$LIST);
			}


			//	検証バッチから検証webへ
			$command = "/usr/bin/php ".BASE_DIR."/_www/batch/CONTENTSUP.cgi '2' '".MODE."' '".$update_dir."' '".$update_dir_remote."' '".$file_type."'";
// echo "command=".$command."<br>";

			exec($command,$LIST);

			$send_data = array('dir' => $_POST['common_dir'].$update_dir,'file_type' => $file_type );

			$serialize_dir = serialize($send_data);
			//	ログ保存 --
			$mate_upd_log_num = 0;
			$sql  = "SELECT mate_upd_log_num FROM mate_upd_log".
					" WHERE update_mode='".MODE."'".
					" AND course_num='".$_POST['course_num']."'".
					" AND send_data='".$serialize_dir."'".
					" AND state='1'".
					" ORDER BY regist_time DESC LIMIT 1;";
			if ($result = $cdb->query($sql)) {
				if($list = $cdb->fetch_assoc($result)){
					$mate_upd_log_num = $list['mate_upd_log_num'];
				}
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
				$INSERT_DATA['send_data'] = $serialize_dir;
				$INSERT_DATA['regist_time'] = "now()";
				$INSERT_DATA['state'] = 1;
				$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];

				$ERROR = $cdb->insert('mate_upd_log',$INSERT_DATA);
			}
		}
		//pre($INSERT_DATA);
	} else{
		$ERROR = 'アップするcommonフォルダを選択してください。';
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
function update_end_html() {

	$html  = "レクチャーcommonフォルダのアップが完了致しました。<br>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"back\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\"><br>\n";
	$html .= "</form>\n";

	return $html;
}
?>