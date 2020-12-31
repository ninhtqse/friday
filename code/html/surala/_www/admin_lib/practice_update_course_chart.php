<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * プラクティスステージ管理　プラクティスアップデートプログラム
 * サブプログラム	体系図ファイルアップデート
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

	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" name=\"del_form_all\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"".$_POST['mode']."\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"web_session\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
	$html .= select_web_menu();
	$html .= "</form>\n";

	//サーバー情報取得
	if (!$_SESSION['select_web']) { return $html; }

	// -- リモートサーバーフォルダー内最新ファイル時間

	//	ローカルサーバー
	unset($local_max);
	//	add ookawara 2008/11/26	start
	//	小学生低学年
	$local_dir_l = MATERIAL_CHART_DIR."l/".$_POST['course_num']."/";											// add 2016/12/26 yoshizawa 低学年2次対応
	//	小学生高学年
	$local_dir_p = MATERIAL_CHART_DIR."p/".$_POST['course_num']."/";											// add 小学校高学年対応 2014/08/25 yoshizawa
	//	中学生
	$local_dir_j = MATERIAL_CHART_DIR."j/".$_POST['course_num']."/";
	//	高校生
	$local_dir_h = MATERIAL_CHART_DIR."h/".$_POST['course_num']."/";

	// 	ローカルサーバーフォルダー内情報読み込み
	$LOCAL_FILE_NAME['l'] = local_read_dir($local_dir_l);														// add 2016/12/26 yoshizawa 低学年2次対応
	$LOCAL_FILE_NAME['p'] = local_read_dir($local_dir_p);														// add 小学校高学年対応 2014/08/25 yoshizawa
	$LOCAL_FILE_NAME['j'] = local_read_dir($local_dir_j);
	$LOCAL_FILE_NAME['h'] = local_read_dir($local_dir_h);
	$local_max = count($LOCAL_FILE_NAME['l']) + count($LOCAL_FILE_NAME['p']) + count($LOCAL_FILE_NAME['j']) + count($LOCAL_FILE_NAME['h']);	// update 'count($LOCAL_FILE_NAME['p']) + 'を追加 小学校高学年対応 2014/08/25 yoshizawa // update 'count($LOCAL_FILE_NAME['l']) + 'を追加 2016/12/26 yoshizawa 低学年2次対応
	//	add ookawara 2008/11/26	end

	//	del ookawara 2008/11/26	start
/*
	$local_dir = MATERIAL_CHART_DIR.$_POST['course_num']."/";
	$LOCAL_FILE_NAME = local_read_dir($local_dir);
	$local_max = count($LOCAL_FILE_NAME);
*/
	//	del ookawara 2008/11/26	end

	if ($local_max) {
		//	ローカルサーバーフォルダー内最新ファイル時間
//		$local_time = local_read_dir_time($local_dir);	//	del ookawara 2008/11/26

		//----------------------------//
		//	最新更新時間を取得します。//
		//----------------------------//
		// del 小学校高学年対応 2014/08/25 yoshizawa 下に新規で作成 ----------------
		////	add ookawara 2008/11/26	start
		//$local_time_j = local_read_dir_time($local_dir_j);
		//$local_time_h = local_read_dir_time($local_dir_h);
		//if ($local_time_j > $local_time_h) {
		//	$local_time = $local_time_j;
		//} else {
		//	$local_time = $local_time_h;
		//}
		//	add ookawara 2008/11/26	end
		//--------------------------------------------------------------------------
		// add 小学校高学年対応 2014/08/25 yoshizawa -------------------------------
		$LOCAL_T = array();
		$LOCAL_T['l'] = local_read_dir_time($local_dir_l); // add 2016/12/26 yoshizawa 低学年2次対応
		$LOCAL_T['p'] = local_read_dir_time($local_dir_p);
		$LOCAL_T['j'] = local_read_dir_time($local_dir_j);
		$LOCAL_T['h'] = local_read_dir_time($local_dir_h);

		$local_time = max($LOCAL_T);
		//--------------------------------------------------------------------------

		$local_html .= "最新更新時間：".date("Y/m/d H:i:s",$local_time);	//	add :sookawara 2012/08/01
		$local_html .= "<table class=\"course_form\">\n";
		$local_html .= "<tr class=\"course_form_menu\">\n";
		$local_html .= "<th>ファイル名</th>\n";
		$local_html .= "<th>更新時間</th>\n";
		$local_html .= "</tr>\n";

		// add 2016/12/26 yoshizawa 低学年2次対応 -------------------------------
		$local_html .= "<tr class=\"course_form_menu\">\n";
		$local_html .= "<td  colspan=\"2\">小学(低学年)</td>\n";
		$local_html .= "</tr>\n";

		//	ローカルサーバーファイルリスト作成
		$local_html .= local_file_make_list($LOCAL_FILE_NAME['l'],$local_dir_l,"");
		//--------------------------------------------------------------------------

		// add 小学校高学年対応 2014/08/25 yoshizawa -------------------------------
		$local_html .= "<tr class=\"course_form_menu\">\n";
		$local_html .= "<td  colspan=\"2\">小学(高学年)</td>\n"; // update '小学'→'小学(高学年)' 2016/12/26 yoshizawa 低学年2次対応
		$local_html .= "</tr>\n";

		//	ローカルサーバーファイルリスト作成
		$local_html .= local_file_make_list($LOCAL_FILE_NAME['p'],$local_dir_p,"");
		//--------------------------------------------------------------------------

		//	add ookawara 2008/11/26	start
		$local_html .= "<tr class=\"course_form_menu\">\n";
		$local_html .= "<td  colspan=\"2\">中学</td>\n";
		$local_html .= "</tr>\n";
		//	add ookawara 2008/11/26	end

		//	ローカルサーバーファイルリスト作成
//		$local_html .= local_file_make_list($LOCAL_FILE_NAME,$local_dir,"");	//	del ookawara	2008/11/26
		$local_html .= local_file_make_list($LOCAL_FILE_NAME['j'],$local_dir_j,"");	//	add ookawara	2008/11/26

		//	add ookawara 2008/11/26	start
		$local_html .= "<tr class=\"course_form_menu\">\n";
		$local_html .= "<td  colspan=\"2\">高校</td>\n";
		$local_html .= "</tr>\n";
		//	add ookawara 2008/11/26	end

		$local_html .= local_file_make_list($LOCAL_FILE_NAME['h'],$local_dir_h,"");	//	add ookawara	2008/11/26

		$local_html .= "</table>\n";
	} else {
		$local_html = "ファイルがアップロードされておりません。";
	}

	// -- リモートサーバー
	unset($remote_max);
	//	add ookawara 2008/11/26	start
	//	小学生(低学年)
	$remote_dir_l = REMOTE_MATERIAL_CHART_DIR."l/".$_POST['course_num']."/";								// update 2016/12/26 yoshizawa 低学年2次対応
	//	小学生(高学年)
	$remote_dir_p = REMOTE_MATERIAL_CHART_DIR."p/".$_POST['course_num']."/";								// add 小学校高学年対応 2014/08/25 yoshizawa
	//	中学生
	$remote_dir_j = REMOTE_MATERIAL_CHART_DIR."j/".$_POST['course_num']."/";
	//	高校生
	$remote_dir_h = REMOTE_MATERIAL_CHART_DIR."h/".$_POST['course_num']."/";

	$FILE_NAME['l'] = remote_read_dir($_SESSION['select_web'],$remote_dir_l);								// add 2016/12/26 yoshizawa 低学年2次対応
	$FILE_NAME['p'] = remote_read_dir($_SESSION['select_web'],$remote_dir_p);								// add 小学校高学年対応 2014/08/25 yoshizawa
	$FILE_NAME['j'] = remote_read_dir($_SESSION['select_web'],$remote_dir_j);
	$FILE_NAME['h'] = remote_read_dir($_SESSION['select_web'],$remote_dir_h);
	$remote_max = count($FILE_NAME['l']['f']) + count($FILE_NAME['p']['f']) + count($FILE_NAME['j']['f']) + count($FILE_NAME['h']['f']);	// update 'count($FILE_NAME['p']['f']) + 'を追加 小学校高学年対応 2014/08/25 yoshizawa // update 'count($FILE_NAME['l']['f']) +'を追加 2016/12/26 yoshizawa 低学年2次対応
	//	add ookawara 2008/11/26	end

	//	del ookawara 2008/11/26	start
/*
	$remote_dir = REMOTE_MATERIAL_CHART_DIR.$_POST['course_num']."/";
	$FILE_NAME = remote_read_dir($_SESSION['select_web'],$remote_dir);
	$remote_max = count($FILE_NAME["f"]);
*/
	//	del ookawara 2008/11/26	end

	if ($remote_max) {
		// -- リモートサーバーフォルダー内最新ファイル時間
//		$remote_time = remote_read_dir_time($_SESSION['select_web'],$remote_dir);

		//----------------------------//
		//	最新更新時間を取得します。//
		//----------------------------//
		// del 小学校高学年対応 2014/08/25 yoshizawa 下に新規で作成 ----------------
		////	add ookawara 2008/11/26	start
		//$remote_time_j = remote_read_dir_time($_SESSION['select_web'],$remote_dir_j);
		//$remote_time_h = remote_read_dir_time($_SESSION['select_web'],$remote_dir_h);
		//if ($remote_time_j > $remote_time_h) {
		//	$remote_time = $remote_time_j;
		//} else {
		//	$remote_time = $remote_time_h;
		//}
		////	add ookawara 2008/11/26	end
		//--------------------------------------------------------------------------

		// add 小学校高学年対応 2014/08/25 yoshizawa -------------------------------
		$REMOTE_T = array();

		// update start 2017/01/10 yoshizawa
		// 階層ごとにファイル情報を取得すると時間がかかるので取得方法を変更します。
		// $REMOTE_T['l'] = remote_read_dir_time($_SESSION['select_web'],$remote_dir_l); // add 2016/12/26 yoshizawa 低学年2次対応
		// $REMOTE_T['p'] = remote_read_dir_time($_SESSION['select_web'],$remote_dir_p);
		// $REMOTE_T['j'] = remote_read_dir_time($_SESSION['select_web'],$remote_dir_j);
		// $REMOTE_T['h'] = remote_read_dir_time($_SESSION['select_web'],$remote_dir_h);
		// $remote_time = max($REMOTE_T);
		//
		$REMOTE_T['l'] = remote_read_dir_time_ls($_SESSION['select_web'],$remote_dir_l);
		$REMOTE_T['p'] = remote_read_dir_time_ls($_SESSION['select_web'],$remote_dir_p);
		$REMOTE_T['j'] = remote_read_dir_time_ls($_SESSION['select_web'],$remote_dir_j);
		$REMOTE_T['h'] = remote_read_dir_time_ls($_SESSION['select_web'],$remote_dir_h);
		$remote_time = max($REMOTE_T);
		// update end 2017/01/10 yoshizawa
		//--------------------------------------------------------------------------

		$remote_html .= "最新更新時間：".date("Y/m/d H:i:s",$remote_time);	//	add :sookawara 2012/08/01
		$remote_html .= "<table class=\"course_form\">\n";
		$remote_html .= "<tr class=\"course_form_menu\">\n";
		$remote_html .= "<th>ファイル名</th>\n";
		$remote_html .= "<th>更新時間</th>\n";
		$remote_html .= "</tr>\n";

		// add 2016/12/26 yoshizawa 低学年2次対応 -------------------------------
		$remote_html .= "<tr class=\"course_form_menu\">\n";
		$remote_html .= "<td  colspan=\"2\">小学(低学年)</td>\n";
		$remote_html .= "</tr>\n";
		//	リモートサーバーファイルリスト作成
		$remote_html .= remote_file_make_list($_SESSION['select_web'],$FILE_NAME['l'],$remote_dir_l,$add_dir_l);
		//--------------------------------------------------------------------------

		// add 小学校高学年対応 2014/08/25 yoshizawa -------------------------------
		$remote_html .= "<tr class=\"course_form_menu\">\n";
		$remote_html .= "<td  colspan=\"2\">小学(高学年)</td>\n"; // update '小学'→'小学(高学年)' 2016/12/26 yoshizawa 低学年2次対応
		$remote_html .= "</tr>\n";
		//	リモートサーバーファイルリスト作成
		$remote_html .= remote_file_make_list($_SESSION['select_web'],$FILE_NAME['p'],$remote_dir_p,$add_dir_p);
		//--------------------------------------------------------------------------

		//	add ookawara 2008/11/26	start
		$remote_html .= "<tr class=\"course_form_menu\">\n";
		$remote_html .= "<td  colspan=\"2\">中学</td>\n";
		$remote_html .= "</tr>\n";
		//	add ookawara 2008/11/26	end

		//	リモートサーバーファイルリスト作成
		// $remote_html .= remote_file_make_list($_SESSION['select_web'],$FILE_NAME,$remote_dir,$add_dir);	//	del ookawara	2008/11/26
		$remote_html .= remote_file_make_list($_SESSION['select_web'],$FILE_NAME['j'],$remote_dir_j,$add_dir_j);	//	add ookawara	2008/11/26

		//	add ookawara 2008/11/26	start
		$remote_html .= "<tr class=\"course_form_menu\">\n";
		$remote_html .= "<td  colspan=\"2\">高校</td>\n";
		$remote_html .= "</tr>\n";
		//	add ookawara 2008/11/26	end

		$remote_html .= remote_file_make_list($_SESSION['select_web'],$FILE_NAME['h'],$remote_dir_h,$add_dir_h);	//	add ookawara	2008/11/26

		$remote_html .= "</table>\n";
	} else {
		$remote_html = "ファイルがアップロードされておりません。";
	}

	if ($ERROR) {
		$html  = ERROR($ERROR);
		$html .= "<br>\n";
	}

	if ($local_time || $remote_time) {
		$submit_msg = $L_NAME['course_name']."の体系図ファイルを検証へアップしますがよろしいですか？";

		$html .= $L_NAME['course_name']."の体系図ファイルをアップする場合は、「アップする」ボタンを押してください。<br>\n";
		$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" name=\"del_form_all\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"update\">\n";
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"$_POST[course_num]\">\n";
		$html .= "<input type=\"submit\" value=\"アップする\" onClick=\"return confirm('".$submit_msg."')\"><br>\n";
		$html .= "<table border=\"0\" cellspacing=\"1\" bgcolor=\"#666666\" cellpadding=\"3\">\n";
		$html .= "<tr bgcolor=\"#cccccc\">\n";
		$html .= "<th colspan=\"2\">".$L_NAME['course_name']."</th>\n";
		$html .= "</tr>\n";
		$html .= "<tr bgcolor=\"#cccccc\">\n";
		$html .= "<th>開発サーバー</th>\n";
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
		$html .= "体系図ファイルがアップロードされておりません。<br>\n";
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

	if ($_POST['course_num'] < 1) {
		$ERROR[] = "更新するコース情報が取得できません。";
		return $ERROR;
	}

	//	体系図ファイルアップロード
	// add 2016/12/26 yoshizawa 低学年2次対応
	$local_dir_l = BASE_DIR."/www".ereg_replace("^../","/",MATERIAL_CHART_DIR)."l/".$_POST['course_num'];
	$remote_dir_l = KBAT_DIR.REMOTE_MATERIAL_CHART_DIR."l/";

	// $command7 = "ssh suralacore01@srlbtw21 mkdir -p ".$remote_dir_l; // del 2018/03/20 yoshizawa AWSプラクティスアップデート
	// $command8 = "scp -rp ".$local_dir_l." suralacore01@srlbtw21:".$remote_dir_l; // del 2018/03/20 yoshizawa AWSプラクティスアップデート
	$command7 = "mkdir -p ".$remote_dir_l; // add 2018/03/20 yoshizawa AWSプラクティスアップデート
	$command8 = "cp -rp ".$local_dir_l." ".$remote_dir_l; // add 2018/03/20 yoshizawa AWSプラクティスアップデート

	//upd start 2017/11/24 yamaguchi AWS移設
	//exec("$command7",&$LIST);
	//exec("$command8",&$LIST);
	exec("$command7",$LIST);
	exec("$command8",$LIST);
	//upd end 2017/11/24 yamaguchi
	// add 2016/12/26 yoshizawa 低学年2次対応

	//	add ookawara 2014/09/11	start
	$local_dir_p = BASE_DIR."/www".ereg_replace("^../","/",MATERIAL_CHART_DIR)."p/".$_POST['course_num'];
	$remote_dir_p = KBAT_DIR.REMOTE_MATERIAL_CHART_DIR."p/";

	// $command5 = "ssh suralacore01@srlbtw21 mkdir -p ".$remote_dir_p; // del 2018/03/20 yoshizawa AWSプラクティスアップデート
	// $command6 = "scp -rp ".$local_dir_p." suralacore01@srlbtw21:".$remote_dir_p; // del 2018/03/20 yoshizawa AWSプラクティスアップデート
	$command5 = "mkdir -p ".$remote_dir_p; // add 2018/03/20 yoshizawa AWSプラクティスアップデート
	$command6 = "cp -rp ".$local_dir_p." ".$remote_dir_p; // add 2018/03/20 yoshizawa AWSプラクティスアップデート

	//upd start 2017/11/24 yamaguchi AWS移設
	//exec("$command5",&$LIST);
	//exec("$command6",&$LIST);
	exec("$command5",$LIST);
	exec("$command6",$LIST);
	//upd end 2017/11/24 yamaguchi
	//	add ookawara 2014/09/11	end

	//	add ookawara 2008/11/26	start
	$local_dir_j = BASE_DIR."/www".ereg_replace("^../","/",MATERIAL_CHART_DIR)."j/".$_POST['course_num'];
	$remote_dir_j = KBAT_DIR.REMOTE_MATERIAL_CHART_DIR."j/";

	// $command1 = "ssh suralacore01@srlbtw21 mkdir -p ".$remote_dir_j; // del 2018/03/20 yoshizawa AWSプラクティスアップデート
	// $command2 = "scp -rp ".$local_dir_j." suralacore01@srlbtw21:".$remote_dir_j; // del 2018/03/20 yoshizawa AWSプラクティスアップデート
	$command1 = "mkdir -p ".$remote_dir_j; // add 2018/03/20 yoshizawa AWSプラクティスアップデート
	$command2 = "cp -rp ".$local_dir_j." ".$remote_dir_j; // add 2018/03/20 yoshizawa AWSプラクティスアップデート

	//upd start 2017/11/24 yamaguchi AWS移設
	//exec("$command1",&$LIST);
	//exec("$command2",&$LIST);
	exec("$command1",$LIST);
	exec("$command2",$LIST);
	//upd end 2017/11/24 yamaguchi

	$local_dir_h = BASE_DIR."/www".ereg_replace("^../","/",MATERIAL_CHART_DIR)."h/".$_POST['course_num'];
	$remote_dir_h = KBAT_DIR.REMOTE_MATERIAL_CHART_DIR."h/";

	// $command3 = "ssh suralacore01@srlbtw21 mkdir -p ".$remote_dir_h; // del 2018/03/20 yoshizawa AWSプラクティスアップデート
	// $command4 = "scp -rp ".$local_dir_h." suralacore01@srlbtw21:".$remote_dir_h; // del 2018/03/20 yoshizawa AWSプラクティスアップデート
	$command3 = "mkdir -p ".$remote_dir_h; // add 2018/03/20 yoshizawa AWSプラクティスアップデート
	$command4 = "cp -rp ".$local_dir_h." ".$remote_dir_h; // add 2018/03/20 yoshizawa AWSプラクティスアップデート

	//upd start 2017/11/24 yamaguchi AWS移設
	//exec("$command3",&$LIST);
	//exec("$command4",&$LIST);
	exec("$command3",$LIST);
	exec("$command4",$LIST);
	//upd end 2017/11/24 yamaguchi

	//	検証バッチから検証webへ
	// $command = "ssh suralacore01@srlbtw21 ./CONTENTSUP.cgi '2' '".MODE."' '".$_POST['course_num']."'"; // del 2018/03/20 yoshizawa AWSプラクティスアップデート
	$command = "/usr/bin/php ".BASE_DIR."/_www/batch/CONTENTSUP.cgi '2' '".MODE."' '".$_POST['course_num']."'"; // add 2018/03/20 yoshizawa AWSプラクティスアップデート

	//upd start 2017/11/24 yamaguchi AWS移設
	//exec($command,&$LIST);
//	exec($command,$LIST);
	//upd end 2017/11/24 yamaguchi

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

	$html  = $L_NAME['course_name']."の体系図ファイルの反映が完了致しました。<br>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"$_POST[course_num]\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"back\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\"><br>\n";
	$html .= "</form>\n";

	return $html;
}
?>