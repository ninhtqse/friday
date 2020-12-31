<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * テスト用プラクティスステージ管理
 * 	プラクティスアップデートプログラム
 * 		問題設定(判定テスト) アップデート
 *
 * 履歴
 * 2013/06/24 初期設定
 *
 * @author Azet
 */


/*
	履歴
	okabe
		変更テーブルは 2 テーブルとなり以下の手順で処理をしていく
			(問題マスタ)	※画像情報もここで取得する。
			コピー先の「hantei_ms_problem」の該当する情報を削除
			コピー元の「hantei_ms_problem」の該当する情報を作成し、コピー先にインサートする
			(問題関連付けテーブル)
			コピー先の「hantei_ms_default_problem」の該当する情報を削除
			コピー元の「hantei_ms_default_problem」の該当する情報を作成し、コピー先にインサートする
*/


/**
 * HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[T02]スタート判定テスト.
 *
 * @author Azet
 * @return string HTML
 */
function action() {
	global $L_TEST_UPDATE_MODE;
/*
echo "MAIN=".MAIN.", SUB=".SUB.", MODE=".MODE.", ACTION=".ACTION;
echo "<hr><pre>";
print_r($_SESSION['view_session']);echo "</pre><br/>";

*/
	$html = "";

	if (ACTION == "update") {
		set_time_limit(0);
		echo "更新開始\n";
		echo str_pad(" ",4096)."<br>\n";
		update($ERROR);
	} elseif (ACTION == "db_session") {
		select_database();
		select_web();
	} elseif (ACTION == "view_session") {
		view_set_session();
	} elseif (ACTION == "") {
		unset($_SESSION['view_session']);
	}

	if (!$ERROR && ACTION == "update") {
		$html .= update_end_html();
	} else {
		$html = select_hanteimei($ERROR);
	}

	return $html;
}


/**
 * サービス、コース、判定テスト名の選択
 *
 * AC:[A]管理者 UC1:[T02]スタート判定テスト.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function select_hanteimei($ERROR) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_HANTEI_TYPE;

	$html = "";
	$last_select_flg = 0;
	$TABLE_LIST = array();

	if ($ERROR) {
		$html .= ERROR($ERROR);
		$html .= "<br>\n";
	}

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
			$service_num = $VALUES['service_num'];
			$hantei_type = $VALUES['hantei_type'];
			$course_num = $VALUES['course_num'];
			$hantei_default_num = $VALUES['hantei_default_num'];

			if ($service_num < 1) {
				continue;
			} elseif ($hantei_type < 1) {
				$PMUL[$service_num] = 1;
			} elseif ($hantei_type == 1 && $hantei_default_num < 1) {
				$PMUL[$service_num][$hantei_type] = 1;
			} elseif ($hantei_type == 2 && $course_num < 1) {
				$PMUL[$service_num][$hantei_type] = 1;
			} elseif ($hantei_type == 2 && $course_num > 0 && $hantei_default_num < 1) {
				$PMUL[$service_num][$hantei_type][$course_num] = 1;
			}
		}
	}

	//	サービス
	$service_html  = "";
	$chk_flg = 0;
	$sql  = "SELECT service_num, service_name FROM ".T_SERVICE.
			" WHERE mk_flg='0'".
			" ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		$selected = "";
		if ($s_service_num < 1) { $selected = "selected"; }
		$service_html .= "<option value=\"0\" ".$selected.">選択して下さい</option>\n";
		while ($list = $cdb->fetch_assoc($result)) {
			$selected = "";
			if ($_SESSION['view_session']['service_num'] == $list['service_num']) {
				$selected = "selected";
			}
			$service_html .= "<option value=\"".$list['service_num']."\" ".$selected.">".$list['service_name']."</option>\n";
			$chk_flg = 1;
		}
	}
	if ($chk_flg == 0) {
		$service_html = "<option value=\"0\">サービスが登録されておりません</option>\n";
	}
	$service_num = $_SESSION['view_session']['service_num'];
	if ($PMUL[$service_num] == 1) {
		$last_select_flg = 1;
	}
	$TABLE_LIST['service_num']['title'] = "サービス";
	$TABLE_LIST['service_num']['html'] = $service_html;


	//	判定タイプ
	$hantei_type_html  = "";
	if ($service_num > 0) {
		if ($last_select_flg == 1) {
			$hantei_type_html .= "<option value=\"\">アップデート中の為選択出来ません</option>\n";
		} else {
			if (count($L_HANTEI_TYPE) > 1) {
				foreach ($L_HANTEI_TYPE AS $key => $val) {
					$selected = "";
					if ($_SESSION['view_session']['hantei_type'] == $key) {
						$selected = "selected";
					}
					$hantei_type_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
				}
			}
		}
	}
	if ($hantei_type_html == "") {
		$hantei_type_html = "<option value=\"0\">判定タイプが登録されておりません</option>\n";
	}
	$hantei_type = $_SESSION['view_session']['hantei_type'];
	if ($PMUL[$service_num][$hantei_type] == 1) {
		$last_select_flg = 1;
	}
	if ($service_num > 0) {
		$TABLE_LIST['hantei_type']['title'] = "判定タイプ";
		$TABLE_LIST['hantei_type']['html'] = $hantei_type_html;
	}


	//コース
	//判定タイプが コース内判定 の場合は、コース選択可能にする
	$couse_html = "";
	$chk_flg = 0;
	if ($hantei_type == 2) {
		if ($last_select_flg == 1) {	//サービスと判定タイプでアップロードされていた場合
			$couse_html .= "<option value=\"\">アップデート中の為選択出来ません</option>\n";
			$chk_flg = 1;
		} else {
			$couse_html .= "<option value=\"0\">選択して下さい</option>\n";
			$sql = "SELECT course.course_num, course.course_name".
				" FROM ".T_SERVICE_COURSE_LIST." service_course_list, " .T_COURSE. " course ".
				" WHERE course.course_num = service_course_list.course_num".
				" AND course.state = 0".
				" AND service_course_list.mk_flg = 0".
				" AND service_course_list.service_num ='".$service_num."'".
				" ORDER BY course.list_num;";
			if ($result = $cdb->query($sql)) {
				while ($list = $cdb->fetch_assoc($result)) {
					$selected = "";
					if ($_SESSION['view_session']['course_num'] == $list['course_num']) {
						$selected = "selected";
					}
					$couse_html .= "<option value=\"".$list['course_num']."\" ".$selected.">".$list['course_name']."</option>\n";
					$chk_flg = 1;
				}
			}
		}
		if ($chk_flg == 0) {
			$couse_html = "<option value=\"0\">コースが登録されておりません</option>\n";
		}
		$TABLE_LIST['course_num']['title'] = "コース";
		$TABLE_LIST['course_num']['html'] = $couse_html;
	}

	$course_num = $_SESSION['view_session']['course_num'];
	if ($PMUL[$service_num][$hantei_type][$course_num] == 1 && $course_num > 0) {
		$last_select_flg = 1;
	}


	//判定名
	$hanteimei_html = "";
	$chk_flg = 0;
	if ($hantei_type == 1 || ($hantei_type == 2 && $course_num > 0)) {
		if ($last_select_flg == 1) {
			$hanteimei_html .= "<option value=\"\">アップデート中の為選択出来ません</option>\n";
			$chk_flg = 1;
		} else {
			$hanteimei_html .= "<option value=\"0\">選択して下さい</option>\n";

			//判定タイプが コース内判定 の場合は、コース選択可能にする
			$sql = "SELECT hantei_ms_default.hantei_default_num, hantei_ms_default.hantei_name".
				" FROM ".T_HANTEI_MS_DEFAULT." hantei_ms_default ".
				" WHERE hantei_ms_default.mk_flg = '0'".
				" AND hantei_ms_default.service_num = '".$service_num."'".
				" AND hantei_ms_default.hantei_type = '".$hantei_type."'";
			if ($hantei_type == "2") { $sql .= " AND hantei_ms_default.course_num = '".$course_num."'"; }
			$sql .= " ORDER BY hantei_ms_default.list_num;";
			if ($result = $cdb->query($sql)) {
				$hantei_default_num_count = $cdb->num_rows($result);
				if ($hantei_default_num_count > 0) {
					$chk_flg = 0;
					while ($list = $cdb->fetch_assoc($result)) {
						$selected = "";
						if ($_SESSION['view_session']['hantei_default_num'] == $list['hantei_default_num']) {
							$selected = "selected";
						}
						$hanteimei_html .= "<option value=\"".$list['hantei_default_num']."\" ".$selected.">".$list['hantei_name']."</option>\n";
						$chk_flg = 1;
					}
				}
			}
		}
		if ($chk_flg == 0) {
			$hanteimei_html = "<option value=\"0\">判定テストが登録されておりません</option>\n";
		}
		$TABLE_LIST['hantei_default_num']['title'] = "判定テスト";
		$TABLE_LIST['hantei_default_num']['html'] = $hanteimei_html;
	}

	if ($TABLE_LIST) {
		$title_html = "";
		$option_html = "";

		foreach ($TABLE_LIST AS $name => $VALUE) {
			$title_val = $VALUE['title'];
			$option_val = $VALUE['html'];
			$title_html  .= "<td>".$title_val."</td>\n";
			$option_html .= "<td>\n";
			$option_html .= "<select name=\"".$name."\" onchange=\"submit();\">\n";
			$option_html .= $option_val;
			$option_html .= "</select>\n";
			$option_html .= "</td>\n";
		}

		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"menu\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"view_session\">\n";
		$html .= "<table class=\"unit_form\">\n";
		$html .= "<tr class=\"unit_form_menu\">\n";
		$html .= $title_html;
		$html .= "</tr>\n";
		$html .= "<tr class=\"unit_form_cell\">\n";
		$html .= $option_html;
		$html .= "</tr>\n";
		$html .= "</table>\n";
		$html .= "</form>\n";
		$html .= "<br />\n";
	}

	if ($_SESSION['view_session']['service_num'] < 1) {
		$html .= "サービス名を選択してください。<br>\n";
		$html .= "<br />\n";
	} else {
		$html .= default_html($ERROR);
	}
	return $html;
}


/**
 * サービス、判定タイプ、コース、判定テスト選択セッションセット
 *
 * AC:[A]管理者 UC1:[T02]スタート判定テスト.
 *
 * @author Azet
 */
function view_set_session() {

	$service_num = $_SESSION['view_session']['service_num'];
	$hantei_type = $_SESSION['view_session']['hantei_type'];
	$course_num = $_SESSION['view_session']['course_num'];
	$hantei_default_num = $_SESSION['view_session']['hantei_default_num'];

	unset($_SESSION['view_session']);

	if ($_POST['service_num'] > 0) {
		$_SESSION['view_session']['service_num'] = $_POST['service_num'];
	} else {
		return;
	}

	if ($_POST['service_num'] == $service_num && $_POST['hantei_type'] > 0) {
		$_SESSION['view_session']['hantei_type'] = $_POST['hantei_type'];
	} else {
		return;
	}

	if ($_POST['hantei_type'] == $hantei_type && $_POST['hantei_type'] == 2 && $_POST['course_num'] > 0) {
		$_SESSION['view_session']['course_num'] = $_POST['course_num'];
	} elseif ($_POST['hantei_type'] == $hantei_type && $_POST['hantei_type'] != 1) {
		return;
	}

	if ($_POST['hantei_type'] == $hantei_type && $_POST['hantei_type'] == 1 && $_POST['hantei_default_num'] > 0) {
		$_SESSION['view_session']['hantei_default_num'] = $_POST['hantei_default_num'];
	} elseif ($_POST['course_num'] == $course_num && $_POST['hantei_type'] == 2 && $_POST['hantei_default_num'] > 0) {
		$_SESSION['view_session']['hantei_default_num'] = $_POST['hantei_default_num'];
	} else {
		return;
	}

}


/**
 * デフォルトページ
 *
 * AC:[A]管理者 UC1:[T02]スタート判定テスト.
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
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"db_session\">\n";
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
	$where = "";
	$where .= " WHERE hmdp.service_num='".$_SESSION['view_session']['service_num']."'";
	if ($_SESSION['view_session']['hantei_type'] > 0) {
		$where .= " AND hmdp.hantei_type='".$_SESSION['view_session']['hantei_type']."'";
	}
	if ($_SESSION['view_session']['hantei_type'] == 2 && $_SESSION['view_session']['course_num'] > 0) {
		$where .= " AND hmdp.course_num='".$_SESSION['view_session']['course_num']."'";
	}
	if ($_SESSION['view_session']['hantei_default_num'] > 0) {
		$where .= " AND hmdp.hantei_default_num='".$_SESSION['view_session']['hantei_default_num']."'";
	}

	//	情報取得クエリー
	//	hantei_ms_default_problem
	$sql  = "SELECT MAX(hmdp.upd_date) AS upd_date FROM ".T_HANTEI_MS_DEFAULT_PROBLEM." hmdp".
			$where.";";
	$sql_cnt  = "SELECT DISTINCT hmdp.* FROM ".T_HANTEI_MS_DEFAULT_PROBLEM." hmdp".
				$where.";";

	//	hantei_ms_problem
	$sql2 = "SELECT MAX(hmp.upd_date) AS upd_date FROM ".T_HANTEI_MS_PROBLEM." hmp".
			" LEFT JOIN ".T_HANTEI_MS_DEFAULT_PROBLEM." hmdp ON hmdp.problem_num=hmp.problem_num".
				" AND hmdp.problem_table_type='2'".
			$where.";";
	$sql_cnt2 = "SELECT DISTINCT hmp.* FROM ".T_HANTEI_MS_PROBLEM." hmp".
				" LEFT JOIN ".T_HANTEI_MS_DEFAULT_PROBLEM." hmdp ON hmdp.problem_num=hmp.problem_num".
					" AND hmdp.problem_table_type='2'".
				$where.";";

	//	ローカルサーバー
	$dsp_flg = 0;
	//	hantei_ms_default_problem
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
		$dsp_flg = 1;
		$local_html = $local_time." (".$cnt.")";
	} else {
		$local_html = "データーがありません。";
	}

	//	hantei_ms_problem
	$local2_html = "";
	$local2_time = "";
	$cnt = 0;
	if ($result = $cdb->query($sql2)) {
		$list = $cdb->fetch_assoc($result);
		$local2_time = $list['upd_date'];
	}
	if ($result = $cdb->query($sql_cnt2)) {
		$cnt = $cdb->num_rows($result);
	}
	if ($local2_time) {
		$dsp_flg = 1;
		$local2_html = $local2_time." (".$cnt.")";
	} else {
		$local2_html = "データーがありません。";
	}


	// -- 閲覧DB
	//	hantei_ms_default_problem
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
		$dsp_flg = 1;
		$remote_html = $remote_time." (".$cnt.")";
	} else {
		$remote_html = "データーがありません。";
	}

	//	hantei_ms_problem
	$remote2_html = "";
	$remote2_time = "";
	$cnt = 0;
	if ($result = $connect_db->query($sql2)) {
		$list = $connect_db->fetch_assoc($result);
		$remote2_time = $list['upd_date'];
	}
	if ($result = $connect_db->query($sql_cnt2)) {
		$cnt = $connect_db->num_rows($result);
	}
	if ($remote2_time) {
		$dsp_flg = 1;
		$remote2_html = $remote2_time." (".$cnt.")";
	} else {
		$remote2_html = "データーがありません。";
	}


	//	ファイル更新情報取得
	//	問題ファイル取得
	$PROBLEM_LIST = array();
	$sql  = "SELECT DISTINCT hmp.problem_num FROM ".T_HANTEI_MS_PROBLEM." hmp".
			" LEFT JOIN ".T_HANTEI_MS_DEFAULT_PROBLEM." hmdp ON hmdp.problem_num=hmp.problem_num".
				" AND hmdp.problem_table_type='2'".
			$where.
			" AND hmp.mk_flg='0'".
			" ORDER BY hmp.problem_num;";
	if ($result = $cdb->query($sql)) {
		while ($list=$cdb->fetch_assoc($result)) {
			$problem_num = $list['problem_num'];
			$PROBLEM_LIST[] = $problem_num;
		}
	}


	//	画像ファイル
	$LOCAL_FILES = array();
	$img_last_local_time = 0;
	$img_last_local_cnt = 0;
	$img_last_remote_time = 0;
	$img_last_remote_cnt = 0;

	//	ローカルサーバー
	$dir = MATERIAL_HANTEI_IMG_DIR;
	test_local_glob($PROBLEM_LIST, $LOCAL_FILES, $img_last_local_time, $dir);
	$img_last_local_cnt = count($LOCAL_FILES['f']);

	//	閲覧サーバー
	$dir = REMOTE_MATERIAL_HANTEI_IMG_DIR;
	test_remote_dir_time($LOCAL_FILES, $img_last_remote_time, $img_last_remote_cnt, $_SESSION['select_web'], $dir);

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


	//	音声ファイル
	$LOCAL_FILES = array();
	$voice_last_local_time = 0;
	$voice_last_local_cnt = 0;
	$voice_last_remote_time = 0;
	$voice_last_remote_cnt = 0;

	//	ローカルサーバー
	$dir = MATERIAL_HANTEI_VOICE_DIR;
	test_local_glob($PROBLEM_LIST, $LOCAL_FILES, $voice_last_local_time, $dir);
	$voice_last_local_cnt = count($LOCAL_FILES['f']);

	//	閲覧サーバー
	$dir = REMOTE_MATERIAL_HANTEI_VOICE_DIR;
	test_remote_dir_time($LOCAL_FILES, $voice_last_remote_time, $voice_last_remote_cnt, $_SESSION['select_web'], $dir);

	$voice_local_time = "データーがありません。";
	if ($voice_last_local_time > 0) {
		$voice_local_time = date("Y-m-d H:i:s", $voice_last_local_time);
	}
	$voice_remote_time = "データーがありません。";
	if ($voice_last_remote_time > 0) {
		$voice_remote_time = date("Y-m-d H:i:s", $voice_last_remote_time);
	}
	$local_voice_html = $voice_local_time;
	if ($voice_last_local_cnt > 0) {
		$local_voice_html .= " (".$voice_last_local_cnt.")";
	}
	$remote_voice_html = $voice_remote_time;
	if ($voice_last_remote_cnt > 0) {
		$remote_voice_html .= " (".$voice_last_remote_cnt.")";
	}

	if ($ERROR) {
		$html  = ERROR($ERROR);
		$html .= "<br />\n";
	}


	if ($dsp_flg == 1) {
		$submit_msg = "問題設定(判定テスト)情報を検証へアップしますがよろしいですか？";

		$html .= "問題設定(判定テスト)情報をアップする場合は、「アップする」ボタンを押してください。<br>\n";
		$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" name=\"pupform\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"update\">\n";
		$html .= "<input type=\"submit\" value=\"アップする\" onClick=\"return confirm('".$submit_msg."')\">\n";
		$html .= " 音声・画像もUPする：<input type=\"checkbox\" name=\"fileup\" value=\"1\" OnClick=\"chk_checkbox('box1');\" id=\"box1\" /><br />\n";		// update oda 2013/10/16 文言変更
		$html .= "<table border=\"0\" cellspacing=\"1\" bgcolor=\"#666666\" cellpadding=\"3\">\n";
		$html .= "<tr bgcolor=\"#cccccc\">\n";
		$html .= "<th>&nbsp;</th>\n";
		$html .= "<th>テストサーバー最新更新日</th>\n";
		$html .= "<th>".$_SESSION['select_db']['NAME']."最新更新日</th>\n";
		$html .= "</tr>\n";

		$html .= "<tr valign=\"top\" bgcolor=\"#ffffff\" align=\"center\">\n";
		$html .= "<td>問題：".T_HANTEI_MS_DEFAULT_PROBLEM."</td>\n";
		$html .= "<td>\n";
		$html .= $local_html;
		$html .= "</td>\n";
		$html .= "<td>\n";
		$html .= $remote_html;
		$html .= "</td>\n";

		$html .= "</tr>\n";
		$html .= "<tr valign=\"top\" bgcolor=\"#ffffff\" align=\"center\">\n";
		$html .= "<td>問題マスタ：".T_HANTEI_MS_PROBLEM."</td>\n";
		$html .= "<td>\n";
		$html .= $local2_html;
		$html .= "</td>\n";
		$html .= "<td>\n";
		$html .= $remote2_html;
		$html .= "</td>\n";
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

		$html .= "<tr valign=\"top\" bgcolor=\"#ffffff\" align=\"center\">\n";
		$html .= "<td>音声ファイル</td>\n";
		$html .= "<td>\n";
		$html .= $local_voice_html;
		$html .= "</td>\n";
		$html .= "<td>\n";
		$html .= $remote_voice_html;
		$html .= "</td>\n";
		$html .= "</tr>\n";

		$html .= "</table>\n";
		//$html .= "<input type=\"submit\" value=\"アップする\" onClick=\"return confirm('".$submit_msg."')\"><br>\n";	//	del ookawara 2012/08/08
		$html .= "<input type=\"submit\" value=\"アップする\" onClick=\"return confirm('".$submit_msg."')\">\n";	//	add ookawara 2012/08/08
		$html .= " 音声・画像もUPする：<input type=\"checkbox\" name=\"fileup\" value=\"1\" OnClick=\"chk_checkbox('box2');\" id=\"box2\" /><br />\n";		// update oda 2013/10/16 文言変更
		$html .= "</form>\n";
		$html .= "※閲覧DB：検証バッチWeb、閲覧Web：検証バッチWebのデーターやファイルは、<br>\n　アップデートリストで本番にアップや削除をしてもデーターが消えないのでご注意ください。<br>\n";
	} else {
		$html .= "問題設定(診断テスト)情報が設定されておりません。<br>\n";
	}

	//	閲覧DB切断
	$connect_db->close();

	return $html;
}


/**
 * 反映
 *
 * AC:[A]管理者 UC1:[T02]スタート判定テスト.
 *
 * @author Azet
 * @param array &$ERROR
 */
function update(&$ERROR) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_CONTENTS_DB, $L_DB;

	//	変更テーブルは 2 テーブルとなり以下の手順で処理をしていく
	//		(問題)	※画像情報もここで取得する。
	//		コピー先の「hantei_ms_default_problem」の該当する情報を削除
	//		コピー元の「hantei_ms_default_problem」の該当する情報を作成し、コピー先にインサートする
	//		(問題マスタ)
	//		コピー先の「hantei_ms_problem」の該当する情報を削除
	//		コピー元の「hantei_ms_problem」の該当する情報を作成し、コピー先にインサートする


	//	時間がかかる為、表示ヘッダ表示
	flush();	//	add ookawara 2012/08/20

	$db_data = $L_CONTENTS_DB['92'];
	//$db_data['DBNAME'] = "SRLBS99";				// 2015/11/10 oda デバッグ用テーブル切り替え

	//	検証バッチDB接続
	$connect_db = new connect_db();
	//$connect_db->set_db($L_CONTENTS_DB['92']);
	$connect_db->set_db($db_data);
	$ERROR = $connect_db->set_connect_db();
	if ($ERROR) {
		$html .= ERROR($ERROR);
	}

	//	データーベース更新
	$INSERT_NAME = array();
	$INSERT_VALUE = array();
	$DELETE_SQL = array();

	//対象データ群の指定

	$where = "";
	$where .= " WHERE hmdp.service_num='".$_SESSION['view_session']['service_num']."'";
	if ($_SESSION['view_session']['hantei_type'] > 0) {
		$where .= " AND hmdp.hantei_type='".$_SESSION['view_session']['hantei_type']."'";
	}
	if ($_SESSION['view_session']['hantei_type'] == 2 && $_SESSION['view_session']['course_num'] > 0) {
		$where .= " AND hmdp.course_num='".$_SESSION['view_session']['course_num']."'";
	}
	if ($_SESSION['view_session']['hantei_default_num'] > 0) {
		$where .= " AND hmdp.hantei_default_num='".$_SESSION['view_session']['hantei_default_num']."'";
	}


	//	更新情報クエリー
	//$where = "";
	$and = "";
	$dtn_where = "";
	$cn_where = "";

	echo "<br>\n";
	echo "db情報取得中<br>\n";
	flush();

	//	更新情報クエリー(結合テーブルを先に処理しないと何回も検証にアップできない)
	//	hantei_ms_default_problem	削除クエリー
	$sql  = "DELETE hmdp FROM ".T_HANTEI_MS_DEFAULT_PROBLEM." hmdp".
			$where.";";
	$DELETE_SQL['02_hantei_ms_default_problem'] = $sql;

	//	hantei_ms_default_problem
	$sql  = "SELECT hmdp.* FROM ".T_HANTEI_MS_DEFAULT_PROBLEM." hmdp".
			$where.";";
	if ($result = $cdb->query($sql)) {
		make_insert_query($result, T_HANTEI_MS_DEFAULT_PROBLEM, $INSERT_NAME, $INSERT_VALUE);
	}

	//	トランザクション開始
	$sql  = "BEGIN";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL BEGIN ERROR";
		$connect_db->close();
		return;
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
		return;
	}

	//	削除
	if ($DELETE_SQL) {
		echo "<br>\n";
		echo "db情報削除中<br>\n";
		flush();

		$err_flg = 0;
		foreach ($DELETE_SQL AS $table_name => $sql) {
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
			return;
		}
	}


	//	検証バッチDBデーター追加
	echo "<br>\n";
	echo "db情報更新中<br>\n";
	flush();

	$last_table_name = "";
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
						return;
					}
				}
			}
		}
	}

	$INSERT_VALUE = array();
	$INSERT_NAME = array();
	$DELETE_SQL = array();

	//	hantei_ms_problem	hantei_ms_default_problemの情報を元に削除する為先にクエリー作成
	$sql  = "SELECT DISTINCT hmp.* FROM ".T_HANTEI_MS_PROBLEM." hmp".
			" LEFT JOIN ".T_HANTEI_MS_DEFAULT_PROBLEM." hmdp ON hmdp.problem_num=hmp.problem_num".
				" AND hmdp.problem_table_type='2'".
			$where.";";
	if ($result = $cdb->query($sql)) {
		make_insert_query($result, T_HANTEI_MS_PROBLEM, $INSERT_NAME, $INSERT_VALUE);
	}

	//	hantei_ms_problem	削除クエリー
	$sql  = "DELETE hmp FROM ".T_HANTEI_MS_PROBLEM." hmp".
			" LEFT JOIN ".T_HANTEI_MS_DEFAULT_PROBLEM." hmdp ON hmdp.problem_num=hmp.problem_num".
				" AND hmdp.problem_table_type='2'".
			$where.";";
	$DELETE_SQL['01_hantei_ms_problem'] = $sql;

	//	削除
	if ($DELETE_SQL) {
		echo "<br>\n";
		echo "db情報削除中<br>\n";
		flush();

		$err_flg = 0;
		foreach ($DELETE_SQL AS $table_name => $sql) {
			if (!$connect_db->exec_query($sql)) {
				$ERROR[] = "SQL DELETE ERROR<br>$sql";
				$err_flg = 1;
			}
		}
		if ($err_flg == 1) {
			$sql  = "ROLLBACK";
			if (!$connect_db->exec_query($sql)) {
				$ERROR[] = "SQL ROLLBACK ERROR";
			}
			$connect_db->close();
			return;
		}
	}


	//	検証バッチDBデーター追加
	echo "<br>\n";
	echo "db情報更新中<br>\n";
	flush();

	$last_table_name = "";
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
						return;
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
		return;
	}

	//	トランザクションコミット
	$sql  = "COMMIT";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL COMMIT ERROR";
		$connect_db->close();
		return;
	}

	echo "<br>\n";
	echo "db最適化中<br>\n";
	flush();

	//	テーブル最適化
	$sql = "OPTIMIZE TABLE ".T_HANTEI_MS_DEFAULT_PROBLEM.";";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
	}

	//	テーブル最適化
	$sql = "OPTIMIZE TABLE ".T_HANTEI_MS_PROBLEM.";";
	if (!$connect_db->exec_query($sql)) {
		$ERROR[] = "SQL OPTIMIZE ERROR<br>$sql";
	}

	//	ファイルアップロード
	if ($_POST['fileup'] == 1) {

		echo "<br>\n";
		echo "ファイルアップロード開始<br>\n";
		flush();

		//	問題ファイル取得
		$PROBLEM_LIST = array();
		$sql  = "SELECT DISTINCT hmp.problem_num FROM ".T_HANTEI_MS_PROBLEM." hmp".
				" LEFT JOIN ".T_HANTEI_MS_DEFAULT_PROBLEM." hmdp ON hmdp.problem_num=hmp.problem_num".
					" AND hmdp.problem_table_type='2'".
				$where.
				" AND hmp.mk_flg='0'".
				" ORDER BY hmp.problem_num;";
		if ($result = $cdb->query($sql)) {
			while ($list=$cdb->fetch_assoc($result)) {
				$problem_num = $list['problem_num'];
				$PROBLEM_LIST[] = $problem_num;
			}
		}

		echo "<br>\n";
		echo "画像ファイルアップロード開始<br>\n";
		flush();

		//	画像ファイル
		$LOCAL_FILES = array();
		$dir = MATERIAL_HANTEI_IMG_DIR;
		test_local_glob($PROBLEM_LIST, $LOCAL_FILES, $last_local_time, $dir);

		//	フォルダー作成
		$remote_dir = KBAT_DIR.REMOTE_MATERIAL_HANTEI_IMG_DIR;
		test_remote_set_dir($remote_dir, $LOCAL_FILES, $ERROR);

		//	ファイルアップ
		$local_dir = BASE_DIR."/www".preg_replace("/^..\\//","/",MATERIAL_HANTEI_IMG_DIR);
		$remote_dir = KBAT_DIR.REMOTE_MATERIAL_HANTEI_IMG_DIR;
		test_remote_set_file($local_dir, $remote_dir, $LOCAL_FILES, $ERROR);

		echo "<br>\n";
		echo "音声ファイルアップロード開始<br>\n";
		flush();

		//	音声ファイル
		$LOCAL_FILES = array();
		$dir = MATERIAL_HANTEI_VOICE_DIR;
		test_local_glob($PROBLEM_LIST, $LOCAL_FILES, $last_local_time, $dir);

		//	フォルダー作成
		$remote_dir = KBAT_DIR.REMOTE_MATERIAL_HANTEI_VOICE_DIR;
		test_remote_set_dir($remote_dir, $LOCAL_FILES, $ERROR);

		//	ファイルアップ
		$local_dir = BASE_DIR."/www".preg_replace("/^..\\//","/",MATERIAL_HANTEI_VOICE_DIR);
		$remote_dir = KBAT_DIR.REMOTE_MATERIAL_HANTEI_VOICE_DIR;
		test_remote_set_file($local_dir, $remote_dir, $LOCAL_FILES, $ERROR);
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
	$service_num = 0;
	if ($_SESSION['view_session']['service_num'] > 0)		{ $service_num = $_SESSION['view_session']['service_num']; }
	$hantei_type = 0;
	if ($_SESSION['view_session']['hantei_type'] > 0)		{ $hantei_type = $_SESSION['view_session']['hantei_type']; }
	$course_num = 0;
	if ($_SESSION['view_session']['course_num'] > 0)		{ $course_num = $_SESSION['view_session']['course_num']; }
	$hantei_default_num = 0;
	if ($_SESSION['view_session']['hantei_default_num'] > 0){ $hantei_default_num = $_SESSION['view_session']['hantei_default_num']; }
	$fileup = 0;
	$_SESSION['view_session']['fileup'] = $_POST['fileup'];
	if ($_SESSION['view_session']['fileup'] > 0)			{ $fileup = $_SESSION['view_session']['fileup']; }
	$send_data = " '".$service_num."'".
				 " '".$hantei_type."'".
				 " '".$course_num."'".
				 " '".$hantei_default_num."'".
				 " '".$fileup."'".
				 " '".$update_num."'";
	$send_data .= " '".$_SESSION['myid']['id']."'";
	//$command = "ssh suralacore01@srlbtw21 /home/suralacore01/batch_test/HANTEITESTCONTENTSUP.php '2' 'test_".MODE."'".$send_data;		// add oda 2015/11/10 プラクティスアップデート不具合対応(デバッグ用)
	// $command = "ssh suralacore01@srlbtw21 ./HANTEITESTCONTENTSUP.cgi '2' 'test_".MODE."'".$send_data; // del 2018/03/20 yoshizawa AWSプラクティスアップデート
	$command = "/usr/bin/php ".BASE_DIR."/_www/batch/HANTEITESTCONTENTSUP.cgi '2' 'test_".MODE."'".$send_data; // add 2018/03/20 yoshizawa AWSプラクティスアップデート

	exec($command);
	//echo "command: ".$command."<br/>\n";

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


	/* del start oda 2015/11/10 プラクティスアップデート不具合修正 ログ更新はcgiにて行う
// 	//	ログ保存 --
// 	$test_mate_upd_log_num = "";
// 	$SEND_DATA_LOG = $_SESSION['view_session'];
// 	$send_data_log = serialize($SEND_DATA_LOG);
// 	$send_data_log = addslashes($send_data_log);
// 	$sql  = "SELECT test_mate_upd_log_num FROM ".T_TEST_MATE_UPD_LOG.
// 			" WHERE update_mode='".MODE."'".
// 			" AND state='1'".
// 			" AND service_num='".$_SESSION['view_session']['service_num']."'";
// 	if ($_SESSION['view_session']['hantei_type'] > 0) {
// 		$sql .= " AND course_num='".$_SESSION['view_session']['hantei_type']."'";
// 	} else {
// 		$sql .= " AND course_num IS NULL";
// 	}
// 	if ($_SESSION['view_session']['hantei_type'] == 2 && $_SESSION['view_session']['course_num'] > 0) {
// 		$sql .= " AND stage_num='".$_SESSION['view_session']['course_num']."'";
// 	} else {
// 		$sql .= " AND stage_num IS NULL";
// 	}
// 	if ($_SESSION['view_session']['hantei_default_num'] > 0) {
// 		$sql .= " AND lesson_num='".$_SESSION['view_session']['hantei_default_num']."'";
// 	} else {
// 		$sql .= " AND lesson_num IS NULL";
// 	}
// 	$sql .=	" ORDER BY regist_time DESC".
// 			" LIMIT 1;";
// 	if ($result = $cdb->query($sql)) {
// 		$list = $cdb->fetch_assoc($result);
// 		$test_mate_upd_log_num = $list['test_mate_upd_log_num'];
// 	}

// 	if ($test_mate_upd_log_num < 1) {
// 		unset($INSERT_DATA);
// 		$INSERT_DATA['state'] = 0;
// 		$INSERT_DATA['regist_time'] = "now()";
// 		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
// 		$where = " WHERE update_mode='".MODE."'".
// 				 " AND state!='0'".
// 				 " AND service_num='".$_SESSION['view_session']['service_num']."'";
// 		if ($_SESSION['view_session']['hantei_type'] > 0) {
// 			$where .= " AND course_num='".$_SESSION['view_session']['hantei_type']."'";
// 		}
// 		if ($_SESSION['view_session']['course_num'] > 0) {
// 			$where .= " AND stage_num='".$_SESSION['view_session']['course_num']."'";
// 		}
// 		if ($_SESSION['view_session']['hantei_default_num'] > 0) {
// 			$where .= " AND lesson_num='".$_SESSION['view_session']['hantei_default_num']."'";
// 		}

// 		$ERROR = $cdb->update(T_TEST_MATE_UPD_LOG, $INSERT_DATA,$where);
// 	}

// 	if ($test_mate_upd_log_num) {
// 		unset($INSERT_DATA);
// 		$INSERT_DATA['send_data'] = $send_data_log;
// 		$INSERT_DATA['regist_time'] = "now()";
// 		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
// 		$where = " WHERE test_mate_upd_log_num='".$test_mate_upd_log_num."'";

// 		$ERROR = $cdb->update(T_TEST_MATE_UPD_LOG, $INSERT_DATA,$where);
// 	} else {
// 		unset($INSERT_DATA);
// 		$INSERT_DATA['update_mode'] = MODE;
// 		$INSERT_DATA['service_num'] = $_SESSION['view_session']['service_num'];
// 		if ($_SESSION['view_session']['hantei_type'] > 0) {
// 			$INSERT_DATA['course_num'] = $_SESSION['view_session']['hantei_type'];
// 		}
// 		if ($_SESSION['view_session']['course_num'] > 0) {
// 			$INSERT_DATA['stage_num'] = $_SESSION['view_session']['course_num'];
// 		}
// 		if ($_SESSION['view_session']['hantei_default_num'] > 0) {
// 			$INSERT_DATA['lesson_num'] = $_SESSION['view_session']['hantei_default_num'];
// 		}
// 		$INSERT_DATA['send_data'] = $send_data_log;
// 		$INSERT_DATA['regist_time'] = "now()";
// 		$INSERT_DATA['state'] = 1;
// 		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];

// 		$ERROR = $cdb->insert(T_TEST_MATE_UPD_LOG, $INSERT_DATA);
// 	}
	*/

	//	検証バッチDB切断
	$connect_db->close();
}


/**
 * 反映終了
 *
 * AC:[A]管理者 UC1:[T02]スタート判定テスト.
 *
 * @author Azet
 * @return string HTML
 */
function update_end_html() {

	$html  = "問題設定(判定テスト)情報のアップが完了致しました。<br>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"back\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\"><br>\n";
	$html .= "</form>\n";

	return $html;
}
?>
