<?
/**
 * ベンチャー・リンク　すらら
 *
 * テスト用プラクティス管理　判定テストマスタ管理
 *
 * 履歴
 * 2013/06/24 初期設定
 *
 * @author Azet
 */

// okabe

/*
 2013/09/12
 当面は、スタイル基準コース、スタイル基準ステージ の入力欄を無くす。
 これらには、TOEIC/400点コースのスタイルとして格納する。
 本ソース中では、"スタイル基準ignore" というキーワードをコメントに含めて無効化する

 これにあわせて、入力修正画面のテンプレート _www/template/admin/test_practice_hantei_test_master.htm
 ここからも下記部分を削除(判定テスト表示番号の下)
<tr class="unit_form_menu">
<td>スタイル基準コース</td>
<td class="unit_form_cell"><!--STYLECOURSENUM--></td>
</tr>
<tr class="unit_form_menu">
<td>スタイル基準ステージ</td>
<td class="unit_form_cell"><!--STYLESTAGENUM--></td>
</tr>
 同様に下記部分も削除(判定結果の下)
<tr class="unit_form_menu">
<td>ドリルクリアメッセージ</td>
<td class="unit_form_cell"><!--CLEARMSG--></td>
</tr>
<tr class="unit_form_menu">
<td>判定結果メッセージ</td>
<td class="unit_form_cell"><!--BREAKMSG--></td>
</tr>

 "ドリルクリアメッセージ"、"判定結果メッセージ"の入力欄を無くす。
 DBには何も格納しないこととする。
 本ソース中では、"ドリルクリアメッセージignore" "判定結果メッセージignore" というキーワードをコメントに含めて無効化する

 test_check_problem_data.php 内の "スタイル基準ignore"、"ドリルクリアメッセージ"、"判定結果メッセージ" チェック処理をパススルーするように修正。
*/



/**
 * HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function start() {
	//if (ACTION != "ht_export" && ACTION != "ht_import") {
	// echo "MAIN=".MAIN.", SUB=".SUB.", MODE=".MODE.", ACTION=".ACTION;
	//print_r($_POST);
	//print_r($_SESSION);
	// echo "<hr/>";
	//}

	define(DISABLE_MSG_OUTPUT, 1);	//clear_msg, break_msg カラム処理スイッチ(=1ならば出力しない)  2013/09/17

	define ("SW_HANTEI_DEFAULT_KEY", 0);	//判定テスト管理キーの取り扱いを止める場合は、0 にして、テンプレートファイルからも項目を消す
	/* 判定テスト管理キーを 表示させるときは、test_practice_hantei_test_master.htm に下記を追加
	<tr class="unit_form_menu">
	<td>判定テスト管理キー</td>
	<td class="unit_form_cell"><!--HANTEIDEFAULTKEY--></td>
	</tr>
	*/

	if (ACTION == "check") { check($ERROR); }
	if (!$ERROR) {
		if (ACTION == "add") { $ERROR = add(); }
		elseif (ACTION == "change") { $ERROR = change(); }
		elseif (ACTION == "up_line") { up($ERROR); }			//↑指示
		elseif (ACTION == "down_line") { down($ERROR); }		//↓指示
		elseif (ACTION == "select_style_cs") { select_style($ERROR); }	//style_course_num、style_stage_num の選択
		elseif (ACTION == "ht_export") { ht_csv_export(); }		//エクスポート
		elseif (ACTION == "ht_import") {						//インポート
			include("../../_www/problem_lib/problem_regist.php");
			//list($ERROR, $PROC_RESULT) = ht_csv_import();
			list($ERROR, $PROC_RESULT) = ht_csv_master_import();	// chenged 2013/09/17
		}

	}

	if (MODE == "add") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }		//エラー無し
			else { $html .= addform($ERROR); }
		} elseif (ACTION == "add") {
			if (!$ERROR) { $html .= master_list($ERROR, $PROC_RESULT); }		//エラー無し
			else { $html .= addform($ERROR); }
		} else {
			$html .= addform($ERROR);
		}
	} elseif (MODE == "detail") {	//詳細 処理
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= viewform($ERROR); }
		} elseif (ACTION == "change") {
			if (!$ERROR) { $html .= master_list($ERROR, $PROC_RESULT); }		//エラー無し
			else { $html .= viewform($ERROR); }
		} else {
			$html .= viewform($ERROR);
		}
	} elseif (MODE == "delete") {	//削除 処理
		if (ACTION == "change") {
			if (!$ERROR) { $html .= master_list($ERROR, $PROC_RESULT); }
			else { $html .= viewform($ERROR); }
		} else {
			$html .= check_html();
		}
	} else {
		$html .= master_list($ERROR, $PROC_RESULT);
	}

	return $html;
}


/**
 * 絞り込みメニュー
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function select_menu() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_HANTEI_TYPE, $L_DESC, $L_PAGE_VIEW;

	$s_service_num = "0";
	$s_hantei_type = "0";
	$s_course_num = "0";
	$s_desc = "0";
	$s_page_view = "2";

	if (ACTION == "sub_session") {
		if (strlen($_POST['s_service_num'])) { $_SESSION['sub_session']['s_service_num'] = $_POST['s_service_num']; }
		if (strlen($_POST['s_hantei_type'])) { $_SESSION['sub_session']['s_hantei_type'] = $_POST['s_hantei_type']; }
		if (strlen($_POST['s_course_num']))	{ $_SESSION['sub_session']['s_course_num'] = $_POST['s_course_num']; }
		if (strlen($_POST['s_desc']))		 { $_SESSION['sub_session']['s_desc'] = $_POST['s_desc']; }
		if (strlen($_POST['s_page_view']))	{ $_SESSION['sub_session']['s_page_view'] = $_POST['s_page_view']; }
	}
	if ($_SESSION['sub_session']) {
		$s_service_num = $_SESSION['sub_session']['s_service_num'];
		$s_hantei_type = $_SESSION['sub_session']['s_hantei_type'];
		$s_course_num = $_SESSION['sub_session']['s_course_num'];
		$s_desc = $_SESSION['sub_session']['s_desc'];
		$s_page_view = $_SESSION['sub_session']['s_page_view'];
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
			if ($s_service_num == $list['service_num']) { $selected = "selected"; }
			$service_html .= "<option value=\"".$list['service_num']."\" ".$selected.">".$list['service_name']."</option>\n";
				$chk_flg = 1;
		}
	}
	if ($service_html == "" || $chk_flg == 0) {
		$service_html .= "<option value=\"0\">サービスが登録されておりません</option>\n";
		$s_service_num = 0;
	}


	//	判定タイプ
	$hantei_type_html  = "";
	if ($s_service_num != 0) {
		if ($s_service_num > 0 && count($L_HANTEI_TYPE) > 1) {
				foreach ($L_HANTEI_TYPE AS $key => $val) {
			$selected = "";
				if ($s_hantei_type == $key) { $selected = "selected"; }
				$hantei_type_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
			}
		}

	}
	if ($hantei_type_html == "") {
		$hantei_type_html .= "<option value=\"0\">サービスを選択してください</option>\n";
		$s_hantei_type = 0;
	}


	//コース
	$couse_html = "<option value=\"0\">選択して下さい</option>\n";
	if ($s_service_num != "0" && $s_hantei_type != "0") {	//サービスと判定タイプが選択されている場合

		if ($s_hantei_type == "1") {
			//判定タイプが コース判定 の場合は、コース選択は選択不要にする。
				$couse_html = "<option value=\"0\" selected>　-　</option>\n";

		} else {
			//判定タイプが コース内判定 の場合は、コース選択可能にする
			$sql = "SELECT course.course_num, course.course_name".
				" FROM ".T_SERVICE_COURSE_LIST." service_course_list, " .T_COURSE. " course ".
				" WHERE course.course_num = service_course_list.course_num".
				" AND course.state = 0".
				" AND service_course_list.mk_flg = 0".
				// upd start hasegawa 2017/12/25 AWS移設 ソート条件追加
				// " AND service_course_list.service_num ='".$s_service_num."';";
				" AND service_course_list.service_num ='".$s_service_num."'".
				" ORDER BY course.list_num;";
				// upd end hasegawa 2017/12/25

			if ($result = $cdb->query($sql)) {
				while ($list = $cdb->fetch_assoc($result)) {
					$selected = "";
					if ($list['course_num'] == $s_course_num) { $selected = "selected"; }
					$couse_html .= "<option value=\"".$list['course_num']."\" ".$selected.">".$list['course_name']."</option>\n";
					$chk_flg = 1;
				}
			}
		}
	}


	//昇順、降順
	foreach ($L_DESC as $key => $val){
		$selected = "";
		if ($_SESSION['sub_session']['s_desc'] == $key) { $selected = " selected"; }
		$s_desc_html .= "<option value=\"".$key."\"".$selected.">".$val."</option>\n";
	}


	//ページ数
	if ($s_page_view < 0) { $s_page_view = 2; }
	foreach ($L_PAGE_VIEW as $key => $val){
		$selected = "";
		if ($s_page_view == $key) { $selected = " selected"; }
		$s_page_view_html .= "<option value=\"".$key."\"".$selected.">".$val."</option>\n";
	}


	//メニュー組み立て情報
	$SEL_MENU_L = array();
	$SEL_MENU_L[1] = array('text'=>'サービス', 'name'=>'s_service_num', 'onchange'=>'1', 'options'=>$service_html, 'Submit'=>'0');
	$SEL_MENU_L[2] = array('text'=>'判定タイプ', 'name'=>'s_hantei_type', 'onchange'=>'1', 'options'=>$hantei_type_html, 'Submit'=>'0');
	if ($s_hantei_type != "1") {
		//判定タイプが コース判定 の場合は、コース選択非表示
		$SEL_MENU_L[3] = array('text'=>'コース', 'name'=>'s_course_num', 'onchange'=>'1', 'options'=>$couse_html, 'Submit'=>'0');
	}
	$SEL_MENU_L[4] = array('text'=>'ソート', 'name'=>'s_desc', 'onchange'=>'0', 'options'=>$s_desc_html, 'Submit'=>'0');
	$SEL_MENU_L[5] = array('text'=>'表示数', 'name'=>'s_page_view', 'onchange'=>'0', 'options'=>$s_page_view_html, 'Submit'=>'1');


	$sub_session_html = "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$sub_session_html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";

	ksort($SEL_MENU_L);

	$c_name_html = "";
	foreach ($SEL_MENU_L as $key => $SEL_MENU_ITEM_L) {
		if ($key != "5") { $c_name_html .= "<td>"; }
		$c_name_html .= $SEL_MENU_ITEM_L['text']."　";
		if ($key != "4") { $c_name_html .= "</td>\n"; }
		if ($key != "5") { $sub_session_html .= "<td>\n"; }
		$sub_session_html .= "<select name=\"".$SEL_MENU_ITEM_L['name']."\" ";
		if ($SEL_MENU_ITEM_L['onchange']) { $sub_session_html .= "onchange=\"submit();return false;\""; }
		$sub_session_html .= ">\n".$SEL_MENU_ITEM_L['options']."</select>\n";
		if ($SEL_MENU_ITEM_L['Submit']) { $sub_session_html .= "<input type=\"submit\" value=\"Set\">\n"; }	//「Set」ボタン表示
		if ($key != "4") { $sub_session_html .= "</td>\n"; }
	}
	$sub_session_html .= "</form>\n";

	$html .= "<br><div id=\"mode_menu\">\n";
	$html .= "<table class=\"unit_form\">\n";
	$html .= "<tr class=\"unit_form_menu\">\n";
	$html .= $c_name_html;
	$html .= "</tr>\n";
	$html .= "<tr class=\"unit_form_cell\">\n";
	$html .= $sub_session_html;
	$html .= "</tr>\n";
	$html .= "</table>\n";
	$html .= "</div>\n";


	$s_page_save = $_SESSION['sub_session']['s_page'];
	$_SESSION['sub_session'] = array();

	$_SESSION['sub_session']['s_service_num'] = $s_service_num;
	$_SESSION['sub_session']['s_hantei_type'] = $s_hantei_type;
	$_SESSION['sub_session']['s_course_num'] = $s_course_num;
	$_SESSION['sub_session']['s_desc'] = $s_desc;
	$_SESSION['sub_session']['s_page_view'] = $s_page_view;

	//表示ページ番号の情報は引き続き保持し、POSTされたページ番号があれば、それを代入する。
	if ($s_page_save > 0) { $_SESSION['sub_session']['s_page'] = $s_page_save; }
	if (strlen($_POST['s_page'])) { $_SESSION['sub_session']['s_page'] = $_POST['s_page']; }	//一覧のページ操作があった場合(ページ番号格納)

	return $html;
}




/**
 * マスター一覧
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @param array $PROC_RESULT
 * @return string HTML
 */
function master_list($ERROR, $PROC_RESULT) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_PAGE_VIEW, $L_DISPLAY;
	global $L_HANTEI_TYPE, $L_ONOFF_TYPE;
	//	add 2015/01/07 yoshizawa 課題要望一覧No.400対応
	global $L_EXP_CHA_CODE;
	//-------------------------------------------------

	unset($_SESSION['UPDATE']);

	//list($me_num,$onetime,$manager_level,$belong_num,$authority) = explode("<>",$_SESSION['myid']);
	if ($authority) { $L_AUTHORITY = explode("::",$authority); }

	$select_menu_html = select_menu();

	$s_service_num = "0";
	$s_hantei_type = "0";
	$s_course_num = "0";
	if ($_SESSION['sub_session']) {
		$s_service_num = $_SESSION['sub_session']['s_service_num'];
		$s_hantei_type = $_SESSION['sub_session']['s_hantei_type'];
		$s_course_num = $_SESSION['sub_session']['s_course_num'];
	}


	if ($s_service_num == "0") {
		$html .= $select_menu_html."<br/>サービスを選択してください。<br/><br/>";
		return $html;

	} else {
		if ($s_hantei_type == "0") {
			$html .= $select_menu_html."<br/>判定タイプを選択してください。<br/><br/>";
			//サービスだけが選択されていたら、インポート/エクスポート操作を出来るようにする（表示する）
			$service_name = get_service_name($s_service_num);


			if ($ERROR) {	//インポートエラー表示するために追加
				$html .= "<div class=\"small_error\">\n";
				$html .= ERROR($ERROR);
				$html .= "</div>\n";
			}


			if ($PROC_RESULT) {	//インポート結果レポート
				$html .= "<div class=\"small_error\">\n";
				$html .= REPORT($PROC_RESULT);
				$html .= "</div>\n";
			}


			//	ファイルアップロード用仮設定
			//service_num から write_type を決定して返す
			$s_write_type = get_write_type_from_svc($s_service_num);
			$html .= ftp_dir_html($s_write_type);

//	//$img_ftp = FTP_URL."hantei_img/".$s_hantei_default_num."/";
//	//$voice_ftp = FTP_URL."hantei_voice/".$s_hantei_default_num."/";
//	$img_ftp = FTP_URL."test_img/";
//	$voice_ftp = FTP_URL."test_voice/";
//$test_img_ftp = FTP_TEST_URL."test_img/?/";
//$test_voice_ftp = FTP_TEST_URL."test_voice/?/";
//
//$html .= "<br>\n";
//$html .= "<a href=\"".$img_ftp."\" target=\"_blank\">テンポラリー画像フォルダー($test_img_ftp)</a><br>\n";
//$html .= "<a href=\"".$voice_ftp."\" target=\"_blank\">テンポラリー音声フォルダー($test_voice_ftp)</a><br>\n";
//$html .= "<a href=\"".$img_ftp."\" target=\"_blank\">画像フォルダー($img_ftp)</a><br>\n";
//$html .= "<a href=\"".$voice_ftp."\" target=\"_blank\">音声フォルダー($voice_ftp)</a><br><br>\n";

			$html .= "<br><strong>サービス: ".$service_name."</strong><br>\n";
			$html .= "インポートする場合は、判定テストマスタcsvファイル（S-JIS）を指定しCSVインポートボタンを押してください。<br>\n";
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" enctype=\"multipart/form-data\">\n";
			$html .= "<input type=\"hidden\" name=\"action\" value=\"ht_import\">\n";
			$html .= "<input type=\"hidden\" name=\"service_num\" value=\"".$s_service_num."\">\n";
			$html .= "<input type=\"file\" size=\"40\" name=\"import_file\"><br>\n";
			$html .= "<input type=\"submit\" value=\"CSVインポート\" style=\"float:left;\">\n";
			$html .= "</form>\n";
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"action\" value=\"ht_export\">\n";
			//	add 2015/01/07 yoshizawa 課題要望一覧No.400対応
			//	プルダウンを作成
			$expList = "";
			if ( is_array($L_EXP_CHA_CODE) ) {
				$expList .= "<br /><br />\n";
				$expList .= "海外版の場合は、出力形式について[Unicode]選択して、CSVエクスポートボタンをクリックしてください。<br />\n";
				$expList .= "<b>出力形式：</b>";
				$expList .= "<select name=\"exp_list\">";
				foreach( $L_EXP_CHA_CODE as $key => $val ){
					$expList .= "<option value=\"".$key."\">".$val."</option>";
				}
				$expList .= "</select>";
				$html .= $expList;
			}
			//-------------------------------------------------
			$html .= "<input type=\"submit\" value=\"CSVエクスポート\">\n";
			$html .= "</form>\n";
			return $html;

		} elseif ($s_hantei_type == 2 && $s_course_num == 0) {
			$html .= $select_menu_html."<br/>コースを選択してください。<br/><br/>";
			return $html;
		}
	}



	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$where = "";
	if ($s_service_num > 0) { $where .= " AND hantei_ms_default.service_num='".$s_service_num."'"; }
	if ($s_hantei_type > 0) { $where .= " AND hantei_ms_default.hantei_type='".$s_hantei_type."'"; }
	if ($s_hantei_type == 2 && $s_course_num > 0)  { $where .= " AND hantei_ms_default.course_num='".$s_course_num."'"; }

	$sql  = "SELECT ".
			" hantei_ms_default.hantei_default_num,".
			" hantei_ms_default.course_num,".
			" hantei_ms_default.list_num,".
			//" hantei_ms_default.hantei_display_num,".
			" hantei_ms_default.hantei_name,".
			" hantei_ms_default.rand_type,".
			" hantei_ms_default.clear_rate,".
			" hantei_ms_default.problem_count,".
			" hantei_ms_default.display,".
			" service.service_name".
			" FROM " . T_HANTEI_MS_DEFAULT . " hantei_ms_default, " . T_SERVICE. " service ".
			" WHERE hantei_ms_default.service_num=service.service_num".
			" AND service.mk_flg='0'".
			" AND hantei_ms_default.mk_flg='0'".$where;

	if ($result = $cdb->query($sql)) {
		$hantei_test_master_count = $cdb->num_rows($result);
	}

	// サービス、判定タイプ、コース等のドロップダウン選択行
	$html .= $select_menu_html;

	if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__add",$_SESSION['authority'])===FALSE)) {
		$html .= "<br>\n";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"add\">\n";
		$html .= "<input type=\"submit\" value=\"マスタ新規登録\">\n";
		$html .= "</form>\n";
	}

	if (!$hantei_test_master_count) {
		$html .= "<br>\n";
		$html .= "今現在登録されている判定テストマスタは有りません。\n";
		return $html;
	}

	if ($_SESSION['sub_session']['s_desc']) {
		$sort_key = " DESC";
	} else {
		$sort_key = " ASC";
	}

	$orderby = " ORDER BY hantei_ms_default.list_num ".$sort_key;

	if ($L_PAGE_VIEW[$_SESSION['sub_session']['s_page_view']]) {
		$page_view = $L_PAGE_VIEW[$_SESSION['sub_session']['s_page_view']];
	} else {
		$page_view = $L_PAGE_VIEW[0];
	}

	$max_page = ceil($hantei_test_master_count/$page_view);
	if ($_SESSION['sub_session']['s_page']) {
		$page = $_SESSION['sub_session']['s_page'];
	} else {
		$page = 1;
	}

	//削除処理後、見ていたページにデータが１件も無くなったかチェック
	if (($page - 1) * $page_view >= $hantei_test_master_count && $page > 0) { $page = $page -1; }

	$start = ($page - 1) * $page_view;
	$next = $page + 1;
	$back = $page - 1;

	$sql .= $orderby." LIMIT ".$start.",".$page_view.";";

	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);

		$html .= "<br>\n";
		$html .= "修正する場合は、修正する判定テストマスタの詳細ボタンを押してください。<br>\n";
		$html .= "<div style=\"float:left;\">登録マスタ総数(".$hantei_test_master_count."):PAGE[".$page."/".$max_page."]</div>\n";
		if ($back > 0) {
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" style=\"float:left;\">";
			$html .= "<input type=\"submit\" value=\"前のページ\">";
			$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
			$html .= "<input type=\"hidden\" name=\"s_page\" value=\"".$back."\">\n";
			$html .= "</form>";
		}
		if ($page < $max_page) {
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" style=\"float:left;\">";
			$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
			$html .= "<input type=\"hidden\" name=\"s_page\" value=\"".$next."\">\n";
			$html .= "<input type=\"submit\" value=\"次のページ\">";
			$html .= "</form>\n";
		}
		$html .= "<br style=\"clear:left;\">\n";

		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" id=\"frmList\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"\">\n";
		$html .= "<input type=\"hidden\" name=\"hantei_default_num\" value=\"\">\n";

		$html .= "<table class=\"course_form\">\n";
		$html .= "<tr class=\"course_form_menu\">\n";
		if (!ereg("practice__view",$authority)
			&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__sort",$_SESSION['authority'])===FALSE))
		) {
			$html .= "<th>↑</th>\n";
			$html .= "<th>↓</th>\n";
		}
		$html .= "<th>マスタID</th>\n";
		$html .= "<th>サービス名</th>\n";
		$html .= "<th>判定タイプ</th>\n";
		if ($s_hantei_type == 2) {					// add oda 2013/09/25
			$html .= "<th>コース名</th>\n";
		}
		//$html .= "<th>表示番号</th>\n";
		$html .= "<th>判定名</th>\n";
		$html .= "<th>ランダム表示</th>\n";
		$html .= "<th>判定正解率</th>\n";
		$html .= "<th>出題問題数</th>\n";
		$html .= "<th>表示・非表示</th>\n";
		if (!ereg("practice__view",$authority)
			&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__view",$_SESSION['authority'])===FALSE))
		) {
			$html .= "<th>詳細</th>\n";
		}
		if (!ereg("practice__del",$authority)
			&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__del",$_SESSION['authority'])===FALSE))
		) {
			$html .= "<th>削除</th>\n";
		}
		$html .= "</tr>\n";

		$i = 1;
		while ($list=$cdb->fetch_assoc($result)) {
			$up_submit = $down_submit = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			if (!$_SESSION['sub_session']['s_desc']) {
				if ($i != 1 || $page != 1) {
					$up_submit   = "<input type=\"button\" value=\"↑\" onclick=\"modefy_data('".$list['hantei_default_num']."', 'up', 'up_line', '');return false;\">\n";
				}
				if ($i != $max || $page != $max_page) {
					$down_submit = "<input type=\"button\" value=\"↓\" onclick=\"modefy_data('".$list['hantei_default_num']."', 'down', 'down_line', '');return false;\">\n";
				}
			}

			$html .= "<tr class=\"course_form_cell\">\n";

			if (!ereg("practice__view",$authority)
				&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__sort",$_SESSION['authority'])===FALSE))
			) {
				$html .= "<td>".$up_submit."</td>\n";
				$html .= "<td>".$down_submit."</td>\n";
			}

			$html .= "<td>".$list['hantei_default_num']."</td>\n";
			$html .= "<td>".$list['service_name']."</td>\n";

			$html .= "<td>".$L_HANTEI_TYPE[$s_hantei_type]."</td>\n";

			//-----------------
			//コース名
			$course_name = "";
			if ($s_hantei_type == 2) {
				$sql2 = "SELECT course.course_num, course.course_name".
					" FROM ".T_SERVICE_COURSE_LIST." service_course_list, ".
					T_COURSE." course ".
					" WHERE course.course_num = service_course_list.course_num".
					" AND course.state = 0".
					" AND service_course_list.mk_flg = 0".
					" AND service_course_list.service_num ='".$s_service_num."';";
				if ($result2 = $cdb->query($sql2)) {
					while ($list2 = $cdb->fetch_assoc($result2)) {
						if ($s_course_num == $list2['course_num']) {
							$course_name .= $list2['course_name'];
						}
					}
				}
			}
			//-----------------

			if ($s_hantei_type == 2) {							// add oda 2013/09/25
				$html .= "<td>".$course_name."</td>\n";
			}
			//$html .= "<td>".$list['hantei_display_num']."</td>\n";
			$html .= "<td>".$list['hantei_name']."</td>\n";
			$html .= "<td>".$L_ONOFF_TYPE[$list['rand_type']]."</td>\n";
			$html .= "<td>".$list['clear_rate']." %</td>\n";
			$html .= "<td>".$list['problem_count']." 問</td>\n";
			$html .= "<td>".$L_DISPLAY[$list['display']]."</td>\n";
			if (!ereg("practice__view",$authority)
				&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__view",$_SESSION['authority'])===FALSE))
			) {
				$html .= "<td><input type=\"button\" value=\"詳細\" onclick=\"modefy_data('".$list['hantei_default_num']."', 'detail', '', 'detail');return false;\"></td>\n";
			}
			if (!ereg("practice__del",$authority)
				&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__del",$_SESSION['authority'])===FALSE))
			) {
				$html .= "<td><input type=\"button\" value=\"削除\" onclick=\"modefy_data('".$list['hantei_default_num']."', 'delete', '', 'delete');return false;\"></td>\n";
			}
			$html .= "</tr>\n";
			++$i;
		}
		$html .= "</table>\n";
		$html .= "</form>\n";
	}
	return $html;
}





/**
 * 新規登録フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function addform($ERROR) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_DISPLAY, $L_ONOFF_TYPE;
	global $L_HANTEI_TYPE;

	$s_service_num = "0";
	$s_hantei_type = "0";
	$s_course_num = "0";
	if ($_SESSION['sub_session']) {
		$s_service_num = $_SESSION['sub_session']['s_service_num'];
		$s_hantei_type = $_SESSION['sub_session']['s_hantei_type'];
		$s_course_num = $_SESSION['sub_session']['s_course_num'];
	}

	if ($_SESSION['UPDATE']) {
		//セッション情報に入力データがあるとき
		$service_num = $_SESSION['UPDATE']['service_num'];
		$service_num = mb_convert_kana($service_num,"asKV", "UTF-8");
		$service_num = trim($service_num);

		$hantei_type = $_SESSION['UPDATE']['hantei_type'];
		$hantei_type = mb_convert_kana($hantei_type,"asKV", "UTF-8");
		$hantei_type = trim($hantei_type);

		$course_num = $_SESSION['UPDATE']['course_num'];
		$course_num = mb_convert_kana($course_num,"asKV", "UTF-8");
		$course_num = trim($course_num);

		$hantei_disp_num = $_SESSION['UPDATE']['hantei_disp_num'];
		$hantei_disp_num = mb_convert_kana($hantei_disp_num,"asKV", "UTF-8");
		$hantei_disp_num = trim($hantei_disp_num);

		$hantei_name = $_SESSION['UPDATE']['hantei_name'];
		$hantei_name = trim($hantei_name);

		if (SW_HANTEI_DEFAULT_KEY) {
			// hantei_default_key 判定テスト管理キー取り扱い
			$hantei_default_key = $_SESSION['UPDATE']['hantei_default_key'];
			$hantei_default_key = mb_convert_kana($hantei_default_key,"asKV", "UTF-8");
			$hantei_default_key = trim($hantei_default_key);
			// hantei_default_key 扱い、ここまで
		}

		$random = $_SESSION['UPDATE']['random'];

		$clear_rate = $_SESSION['UPDATE']['clear_rate'];
		$clear_rate = mb_convert_kana($clear_rate,"asKV", "UTF-8");
		$clear_rate = trim($clear_rate);

		$problem_count = $_SESSION['UPDATE']['problem_count'];
		$problem_count = mb_convert_kana($problem_count,"asKV", "UTF-8");
		$problem_count = trim($problem_count);

		$hantei_result = $_SESSION['UPDATE']['hantei_result'];
		$hantei_result = mb_convert_kana($hantei_result,"asKV", "UTF-8");
		$hantei_result = trim($hantei_result);

		//add start okabe 2013/10/04
		$hantei_result_last = $_SESSION['UPDATE']['hantei_result_last'];
		$hantei_result_last = mb_convert_kana($hantei_result_last,"asKV", "UTF-8");
		$hantei_result_last = trim($hantei_result_last);
		//add end okabe 2013/10/04

		$clear_msg = $_SESSION['UPDATE']['clear_msg'];
		$clear_msg = trim($clear_msg);

		$break_msg = $_SESSION['UPDATE']['break_msg'];
		$break_msg = trim($break_msg);

		$limit_time = $_SESSION['UPDATE']['limit_time'];
		$limit_time = mb_convert_kana($limit_time,"asKV", "UTF-8");
		$limit_time = trim($limit_time);

		$display = $_SESSION['UPDATE']['display'];

		$style_course_num = $_SESSION['UPDATE']['style_course_num'];
		$style_stage_num = $_SESSION['UPDATE']['style_stage_num'];

	} else {
		//セッションに入力データが無いときは POSTデータから取り出す
		$service_num = $_POST['service_num'];
		$service_num = mb_convert_kana($service_num,"asKV", "UTF-8");
		$service_num = trim($service_num);

		$hantei_type = $_POST['hantei_type'];
		$hantei_type = mb_convert_kana($hantei_type,"asKV", "UTF-8");
		$hantei_type = trim($hantei_type);

		$course_num = $_POST['course_num'];
		$course_num = mb_convert_kana($course_num,"asKV", "UTF-8");
		$course_num = trim($course_num);

		$hantei_disp_num = $_POST['hantei_disp_num'];
		$hantei_disp_num = mb_convert_kana($hantei_disp_num,"asKV", "UTF-8");
		$hantei_disp_num = trim($hantei_disp_num);

		$hantei_name = $_POST['hantei_name'];
		$hantei_name = trim($hantei_name);

		if (SW_HANTEI_DEFAULT_KEY) {
			// hantei_default_key 判定テスト管理キー取り扱い
			$hantei_default_key = $_POST['hantei_default_key'];
			$hantei_default_key = mb_convert_kana($hantei_default_key,"asKV", "UTF-8");
			$hantei_default_key = trim($hantei_default_key);
			// hantei_default_key 扱い、ここまで
		}

		$random = $_POST['random'];

		$clear_rate = $_POST['clear_rate'];
		$clear_rate = mb_convert_kana($clear_rate,"asKV", "UTF-8");
		$clear_rate = trim($clear_rate);

		$problem_count = $_POST['problem_count'];
		$problem_count = mb_convert_kana($problem_count,"asKV", "UTF-8");
		$problem_count = trim($problem_count);

		$hantei_result = $_POST['hantei_result'];
		$hantei_result = mb_convert_kana($hantei_result,"asKV", "UTF-8");
		$hantei_result = trim($hantei_result);

		//add start okabe 2013/10/04
		$hantei_result_last = $_POST['hantei_result_last'];
		$hantei_result_last = mb_convert_kana($hantei_result_last,"asKV", "UTF-8");
		$hantei_result_last = trim($hantei_result_last);
		//add start okabe 2013/10/04

		$clear_msg = $_POST['clear_msg'];
		$clear_msg = trim($clear_msg);

		$break_msg = $_POST['break_msg'];
		$break_msg = trim($break_msg);

		$limit_time = $_POST['limit_time'];
		$limit_time = mb_convert_kana($limit_time,"asKV", "UTF-8");
		$limit_time = trim($limit_time);

		$display = $_POST['display'];

		/* del start okabe 2012/09/12 "スタイル基準ignore"の入力修正の一時的無効化
		$style_course_num = $_POST['style_course_num'];
		$style_stage_num = $_POST['style_stage_num'];
		*/ // del end okabe 2012/09/12 "スタイル基準ignore"の入力修正の一時的無効化
		// add start okabe 2012/09/12 "スタイル基準ignore"の入力修正の一時的無効化。有効にする際はココを削除
		$style_course_num = "5";	//toeic 400点コース に固定
		$style_stage_num = "0";
		// add end okabe 2012/09/12 "スタイル基準ignore"の入力修正の一時的無効化。有効にする際はココを削除

	}

	//add start okabe 2013/10/04
	if ($s_hantei_type == 1) {
		//コース判定時	//「最終テスト合格時の判定結果」欄を表示しない
		$hantei_result_last_start = "<!--";
		$hantei_result_last_end = "-->";
		$hantei_result_last = "&nbsp;";
	} else {
		//コース内判定
		$hantei_result_last_start = "";
		$hantei_result_last_end = "";
	}
	//add end okabe 2013/10/04

	//サービス名のセット
	$service_name = get_service_name($s_service_num);

	//判定タイプのセット
	$hantei_type_name = $L_HANTEI_TYPE[$s_hantei_type];

	//コースリスト
	$L_COURSE_LIST = course_list();

	$html .= "<br>\n";
	$html .= "新規登録フォーム<br>\n";

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" id=\"frmSelCS\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(TEST_PRACTICE_HANTEI_TEST_MASTER);


	$random_html = "";
	foreach($L_ONOFF_TYPE as $key => $val) {
		if ($val == "") { continue; }
		$newform = new form_parts();
		$newform->set_form_type("radio");
		$newform->set_form_name("random");
		$newform->set_form_id("random_".$key);
		$newform->set_form_check($random);
		$newform->set_form_value("".$key."");
		$random_btn = $newform->make();
		if ($random_html) { $random_html .= " / "; }
		$random_html .= $random_btn."<label for=\"random_".$key."\">".$val."</label>";
	}

	$display_html = "";
	foreach($L_DISPLAY as $key => $val) {
		if ($val == "") { continue; }
		$newform2 = new form_parts();
		$newform2->set_form_type("radio");
		$newform2->set_form_name("display");
		$newform2->set_form_id("display_".$key);
		$newform2->set_form_check($display);
		$newform2->set_form_value("".$key."");
		$display_btn = $newform2->make();
		if ($display_html) { $display_html .= " / "; }
		$display_html .= $display_btn."<label for=\"display_".$key."\">".$val."</label>";
	}

	$service_name .= "<input type=\"hidden\" name=\"service_num\" value=\"".$s_service_num."\">\n";
	$hantei_type_name .= "<input type=\"hidden\" name=\"hantei_type\" value=\"".$s_hantei_type."\">\n";

	if ($s_hantei_type == 2) {
		$sql = "SELECT course.course_num, course.course_name".
			" FROM ".T_SERVICE_COURSE_LIST." service_course_list, ".
			T_COURSE." course ".
			" WHERE course.course_num = service_course_list.course_num".
			" AND course.state = 0".
			" AND service_course_list.mk_flg = 0".
			" AND service_course_list.service_num ='".$s_service_num."';";
		if ($result = $cdb->query($sql)) {
			while ($list = $cdb->fetch_assoc($result)) {
				if ($s_course_num == $list['course_num']) {
					$course_name .= $list['course_name'];
					$course_name .= "<input type=\"hidden\" name=\"course_num\" value=\"".$list['course_num']."\">\n";
				}
			}
		}

	} else {
		$course_name = "---";
	}

	//$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	$INPUTS[BLOCKNUM] 		= array('result'=>'plane','value'=>"---");
	$INPUTS[SERVICENAME]	= array('result'=>'plane','value'=>$service_name);
	$INPUTS[HANTEITYPENAME]	= array('result'=>'plane','value'=>$hantei_type_name);
	$INPUTS[COURSENAME]		= array('result'=>'plane','value'=>$course_name);

	$INPUTS[HANTEIDISPNUM] 	= array('type'=>'text','name'=>'hantei_disp_num','size'=>'8','value'=>$hantei_disp_num);
	$INPUTS[HANTEINAME] 	= array('type'=>'text','name'=>'hantei_name','size'=>'30','value'=>$hantei_name);

	if (SW_HANTEI_DEFAULT_KEY) {
		$INPUTS[HANTEIDEFAULTKEY] 	= array('type'=>'text','name'=>'hantei_default_key','size'=>'20','value'=>$hantei_default_key); // hantei_default_key 判定テスト管理キー取り扱い
	}

	$INPUTS[RANDTYPE] 		= array('result'=>'plane','value'=>$random_html);
	$INPUTS[SERVICENUM] 	= array('type'=>'hidden','name'=>'publishing_id','value'=>'0');
	$INPUTS[CLEARRATE] 		= array('type'=>'text','name'=>'clear_rate','size'=>'4','value'=>$clear_rate);
	$INPUTS[PROBLEMCNT] 	= array('type'=>'text','name'=>'problem_count','size'=>'4','value'=>$problem_count);
	$INPUTS[HANTEIRESULT] 	= array('type'=>'textarea','name'=>'hantei_result','cols'=>'50','rows'=>'3','value'=>$hantei_result);
	$INPUTS[HANTEIRESULTLAST] 	= array('type'=>'textarea','name'=>'hantei_result_last','cols'=>'50','rows'=>'3','value'=>$hantei_result_last);	//add okabe 2013/10/04
	$INPUTS[HANTEIRESULTLASTSTART] 		= array('result'=>'plane','value'=>$hantei_result_last_start);			//add okabe 2013/10/04
	$INPUTS[HANTEIRESULTLASTEND] 		= array('result'=>'plane','value'=>$hantei_result_last_end);			//add okabe 2013/10/04
	$INPUTS[CLEARMSG] 		= array('type'=>'textarea','name'=>'clear_msg','cols'=>'50','rows'=>'5','value'=>$clear_msg);
	$INPUTS[BREAKMSG] 		= array('type'=>'textarea','name'=>'break_msg','cols'=>'50','rows'=>'5','value'=>$break_msg);
	$INPUTS[LIMITTIME] 		= array('type'=>'text','name'=>'limit_time','size'=>'5','value'=>$limit_time);
	$INPUTS[DISPLAY] 		= array('result'=>'plane','value'=>$display_html);

	$sel_style_course_num = "<select name=\"style_course_num\" onchange=\"hantei_style_select();\">\n";
	$sel_style_stage_num = "<select name=\"style_stage_num\" onchange=\"hantei_style_select();\">\n";
	$L_STYLE_COURSE_LIST = course_list();
	foreach($L_STYLE_COURSE_LIST as $key => $val) {
		$sel_style_course_num .= "<option value=\"$key\"";
		if ($style_course_num == $key) {
			$sel_style_course_num .= " selected";
		}
		$sel_style_course_num .= ">".$val."</option>\n";
	}

	$L_STYLE_STAGE_NUM = set_style_stage_list($style_course_num);

	if (count($L_STYLE_STAGE_NUM) == 0) {
		$sel_style_stage_num .= "<option val=\"0\">コースを選択してください</option>";
	} else {
		$sel_style_stage_num .= "<option val=\"0\">選択してください</option>";
		$selected_flg = 0;
		foreach($L_STYLE_STAGE_NUM as $key => $val) {
			$sel_style_stage_num .= "<option value=\"".$key."\"";
			if ($style_stage_num == $key) {
				$sel_style_stage_num .= " selected";
				$selected_flg = 1;
			}
			$sel_style_stage_num .= ">".$val."</option>\n";
		}
		if ($selected_flg == 0) {
			//コースに該当する stage データが無い場合(style_course_numが再選択された場合)は、style_stage_num を 0 にする
			$style_stage_num = 0;
		}
	}
	$sel_style_course_num .= "</select/>\n";
	$sel_style_stage_num .= "</select/>\n";


	/* del start okabe 2012/09/12 "スタイル基準ignore"の入力修正の一時的無効化
	//スタイル基準コース、ステージの選択
	$INPUTS[STYLECOURSENUM] 	= array('result'=>'plane','value'=>$sel_style_course_num);
	$INPUTS[STYLESTAGENUM] 		= array('result'=>'plane','value'=>$sel_style_stage_num);
	*/ // del end okabe 2012/09/12 "スタイル基準ignore"の入力修正の一時的無効化

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"追加確認\">\n";
	$html .= "<input type=\"reset\" value=\"クリア\">\n";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"hantei_test_list\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";
	return $html;
}




/**
 * コースごとのstage_num 取得
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $sel_style_course_num
 * @return array
 */
function set_style_stage_list($sel_style_course_num) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$L_STYLE_STAGE_NUM = array();
	if ($sel_style_course_num > 0) {
		$sql = "SELECT stage_num, stage_name".
			" FROM ".T_STAGE." stage".
			" WHERE course_num='".$sel_style_course_num."'".
			" AND state='0'";
		$result = $cdb->query($sql);
		while ($list=$cdb->fetch_assoc($result)) {
			if ($list) {
				$L_STYLE_STAGE_NUM[$list['stage_num']] = $list['stage_name'];
			}
		}
	}
	return $L_STYLE_STAGE_NUM;
}


/**
 * 修正フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function viewform($ERROR) {
//echo "viewform()<br>";

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_DISPLAY, $L_ONOFF_TYPE;
	global $L_HANTEI_TYPE;

	$html = "";

	if (ACTION) {
		$hantei_default_num = $_SESSION['UPDATE']['hantei_default_num'];
		$s_service_num = $_SESSION['UPDATE']['service_num'];
		$s_hantei_type = $_SESSION['UPDATE']['hantei_type'];

		$s_course_num = 0;	//判定タイプがコース内判定のとき、コース番号 course_num を扱う
		if ($s_hantei_type == 2) { $s_course_num = $_SESSION['UPDATE']['course_num']; }

		$hantei_disp_num = $_SESSION['UPDATE']['hantei_disp_num'];
		$hantei_name = $_SESSION['UPDATE']['hantei_name'];

		if (SW_HANTEI_DEFAULT_KEY) {
			$hantei_default_key = $_SESSION['UPDATE']['hantei_default_key'];		// hantei_default_key 判定テスト管理キー（フォーム非表示）
		}

		$random = $_SESSION['UPDATE']['random'];
		$clear_rate = $_SESSION['UPDATE']['clear_rate'];
		$problem_count = $_SESSION['UPDATE']['problem_count'];
		$limit_time = $_SESSION['UPDATE']['limit_time'];
		$clear_msg = $_SESSION['UPDATE']['clear_msg'];
		$break_msg = $_SESSION['UPDATE']['break_msg'];
		$display = $_SESSION['UPDATE']['display'];

		$style_course_num = $_SESSION['UPDATE']['style_course_num'];
		$style_stage_num = $_SESSION['UPDATE']['style_stage_num'];

		$hantei_result = $_SESSION['UPDATE']['hantei_result'];	//'判定結果'データ
		//add start okabe 2013/10/04
		if ($s_hantei_type == 2) {		//コース内判定の場合のみ
			$hantei_result_last = $_SESSION['UPDATE']['hantei_result_last'];	//'最終テスト合格時の判定結果'データ
			$hantei_result_last_start = "";
			$hantei_result_last_end = "";
		} else {
			//コース判定
			$hantei_result_last = "&nbsp;";
			$hantei_result_last_start = "<!--";
			$hantei_result_last_end = "-->";
		}
		//add end okabe 2013/10/04

	} else {
		$sql  = "SELECT ".
			" hantei_ms_default.hantei_default_num,".
			" hantei_ms_default.service_num,".
			" hantei_ms_default.hantei_type,".
			" hantei_ms_default.course_num,".
			" hantei_ms_default.hantei_display_num,".
			" hantei_ms_default.style_course_num,".
			" hantei_ms_default.style_stage_num,".
			" hantei_ms_default.hantei_name,";
		if (SW_HANTEI_DEFAULT_KEY) {
			$sql .= " hantei_ms_default.hantei_default_key,";		// hantei_default_key 判定テスト管理キー（フォーム非表示）
		}
		$sql .= " hantei_ms_default.rand_type,".
			" hantei_ms_default.clear_rate,".
			" hantei_ms_default.problem_count,".
			" hantei_ms_default.limit_time,".
			" hantei_ms_default.clear_msg,".
			" hantei_ms_default.break_msg,".
			" hantei_ms_default.display".
			" FROM " . T_HANTEI_MS_DEFAULT . " hantei_ms_default" .
			" WHERE hantei_ms_default.hantei_default_num='".$_POST['hantei_default_num']."' AND hantei_ms_default.mk_flg='0' LIMIT 1;";
		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);
		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}

		$hantei_default_num = $list['hantei_default_num'];
		$s_service_num = $list['service_num'];
		$s_course_num = $list['course_num'];
		$s_hantei_type = $list['hantei_type'];

		$hantei_disp_num = $list['hantei_display_num'];
		$hantei_name = $list['hantei_name'];

		if (SW_HANTEI_DEFAULT_KEY) {
			$hantei_default_key = $list['hantei_default_key'];		// hantei_default_key 判定テスト管理キー（フォーム非表示）
		}

				$random = $list['rand_type'];
		$clear_rate = $list['clear_rate'];
		$problem_count = $list['problem_count'];
		$limit_time = $list['limit_time'];
		$clear_msg = $list['clear_msg'];
		$break_msg = $list['break_msg'];
		$display = $list['display'];

		$style_course_num = $list['style_course_num'];
		$style_stage_num = $list['style_stage_num'];

		//$hantei_result = このデータは、hantei_ms_break_layer から取り出す
		$sql  = "SELECT ".
			" hantei_ms_break_layer.course_num,".
			" hantei_ms_break_layer.stage_num,".
			" hantei_ms_break_layer.lesson_num,".
			" hantei_ms_break_layer.unit_num,".
			" hantei_ms_break_layer.block_num".
			" FROM " . T_HANTEI_MS_BREAK_LAYER . " hantei_ms_break_layer" .
			" WHERE hantei_ms_break_layer.mk_flg='0' AND hantei_ms_break_layer.hantei_default_num='".$hantei_default_num."'".	//edit okabe 2013/10/04
			" AND hantei_ms_break_layer.break_layer_type='0';";			//add okabe 2013/10/04
		$result = $cdb->query($sql);

		$hantei_result = "";
		while ($list=$cdb->fetch_assoc($result)) {
			$hantei_result = rebuild_hantei_result($s_service_num, $list, $hantei_result);		//判定結果の設定情報の再組み立て
		}

		//add start okabe 2013/10/04	//最終テスト合格時の判定結果
		//$hantei_result_last = このデータは、hantei_ms_break_layer から取り出す
		$hantei_result_last = "";
		if ($s_hantei_type == 2) {		//コース内判定の場合のみ
			$sql  = "SELECT ".
				" hantei_ms_break_layer.course_num,".
				" hantei_ms_break_layer.stage_num,".
				" hantei_ms_break_layer.lesson_num,".
				" hantei_ms_break_layer.unit_num,".
				" hantei_ms_break_layer.block_num".
				" FROM " . T_HANTEI_MS_BREAK_LAYER . " hantei_ms_break_layer" .
				" WHERE hantei_ms_break_layer.mk_flg='0' AND hantei_ms_break_layer.hantei_default_num='".$hantei_default_num."'".	//edit okabe 2013/10/04
				" AND hantei_ms_break_layer.break_layer_type='1';";			//add okabe 2013/10/04
			$result = $cdb->query($sql);

			while ($list=$cdb->fetch_assoc($result)) {
				$hantei_result_last = rebuild_hantei_result($s_service_num, $list, $hantei_result_last);		//最終テスト合格時の判定結果の再組み立て
			}
			$hantei_result_last_start = "";
			$hantei_result_last_end = "";

		} else {	//コース判定のときは、入力欄を表示しない
			$hantei_result_last = "&nbsp;";
			$hantei_result_last_start = "<!--";
			$hantei_result_last_end = "-->";
		}
		//add end okabe 2013/10/04

	}

	//サービス名
	$service_name = get_service_name($s_service_num);

	//判定タイプ
	$hantei_type_name = $L_HANTEI_TYPE[$s_hantei_type];


	$html = "<br>\n";
	$html .= "詳細画面<br>\n";

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" id=\"frmSelCS\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";
	$html .= "<input type=\"hidden\" name=\"hantei_default_num\" value=\"".$hantei_default_num."\">\n";
	$html .= "<input type=\"hidden\" name=\"service_num\" value=\"".$s_service_num."\">\n";
	$html .= "<input type=\"hidden\" name=\"hantei_type\" value=\"".$s_hantei_type."\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$s_course_num."\">\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(TEST_PRACTICE_HANTEI_TEST_MASTER);

	$random_html = "";
	foreach($L_ONOFF_TYPE as $key => $val) {
		if ($val == "") { continue; }
		$newform = new form_parts();
		$newform->set_form_type("radio");
		$newform->set_form_name("random");
		$newform->set_form_id("random_".$key);
		$newform->set_form_check($random);
		$newform->set_form_value("".$key."");
		$random_btn = $newform->make();
		if ($random_html) { $random_html .= " / "; }
		$random_html .= $random_btn."<label for=\"random_".$key."\">".$val."</label>";
	}

	$display_html = "";
	foreach($L_DISPLAY as $key => $val) {
		if ($val == "") { continue; }
		$newform2 = new form_parts();
		$newform2->set_form_type("radio");
		$newform2->set_form_name("display");
		$newform2->set_form_id("display_".$key);
		$newform2->set_form_check($display);
		$newform2->set_form_value("".$key."");
		$display_btn = $newform2->make();
		if ($display_html) { $display_html .= " / "; }
		$display_html .= $display_btn."<label for=\"display_".$key."\">".$val."</label>";
	}

	if ($s_hantei_type == 2) {
		$sql = "SELECT course.course_num, course.course_name".
			" FROM ".T_SERVICE_COURSE_LIST." service_course_list, ".
			T_COURSE." course ".
			" WHERE course.course_num = service_course_list.course_num".
			" AND course.state = 0".
			" AND service_course_list.mk_flg = 0".
			" AND service_course_list.service_num ='".$s_service_num."';";
		if ($result = $cdb->query($sql)) {
			while ($list = $cdb->fetch_assoc($result)) {
				if ($s_course_num == $list['course_num']) {
					$course_name .= $list['course_name'];
					$course_name .= "<input type=\"hidden\" name=\"course_num\" value=\"".$list['course_num']."\">\n";
				}
			}
		}

	} else {
		$course_name = "---";
	}

	//$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	$INPUTS[BLOCKNUM] 		= array('result'=>'plane','value'=>$hantei_default_num);
	$INPUTS[SERVICENAME]	= array('result'=>'plane','value'=>$service_name);
	$INPUTS[HANTEITYPENAME]	= array('result'=>'plane','value'=>$hantei_type_name);
	$INPUTS[COURSENAME]		= array('result'=>'plane','value'=>$course_name);

	$INPUTS[HANTEIDISPNUM] 	= array('type'=>'text','name'=>'hantei_disp_num','size'=>'8','value'=>$hantei_disp_num);
	$INPUTS[HANTEINAME] 	= array('type'=>'text','name'=>'hantei_name','size'=>'30','value'=>$hantei_name);

	if (SW_HANTEI_DEFAULT_KEY) {
		$INPUTS[HANTEIDEFAULTKEY] 	= array('type'=>'text','name'=>'hantei_default_key','size'=>'20','value'=>$hantei_default_key); // hantei_default_key 判定テスト管理キー取り扱い
	}

	$INPUTS[RANDTYPE] 		= array('result'=>'plane','value'=>$random_html);
	$INPUTS[SERVICENUM] 	= array('type'=>'hidden','name'=>'publishing_id','value'=>'0');
	$INPUTS[CLEARRATE] 		= array('type'=>'text','name'=>'clear_rate','size'=>'4','value'=>$clear_rate);
	$INPUTS[PROBLEMCNT] 	= array('type'=>'text','name'=>'problem_count','size'=>'4','value'=>$problem_count);
	$INPUTS[HANTEIRESULT] 	= array('type'=>'textarea','name'=>'hantei_result','cols'=>'50','rows'=>'3','value'=>$hantei_result);
	$INPUTS[HANTEIRESULTLAST] 	= array('type'=>'textarea','name'=>'hantei_result_last','cols'=>'50','rows'=>'3','value'=>$hantei_result_last);	//add okabe 2013/10/04
	$INPUTS[HANTEIRESULTLASTSTART] 	= array('result'=>'plane','value'=>$hantei_result_last_start);			//add okabe 2013/10/04
	$INPUTS[HANTEIRESULTLASTEND] 	= array('result'=>'plane','value'=>$hantei_result_last_end);			//add okabe 2013/10/04
	$INPUTS[CLEARMSG] 		= array('type'=>'textarea','name'=>'clear_msg','cols'=>'50','rows'=>'5','value'=>$clear_msg);
	$INPUTS[BREAKMSG] 		= array('type'=>'textarea','name'=>'break_msg','cols'=>'50','rows'=>'5','value'=>$break_msg);
	$INPUTS[LIMITTIME] 		= array('type'=>'text','name'=>'limit_time','size'=>'5','value'=>$limit_time);
	$INPUTS[DISPLAY] 		= array('result'=>'plane','value'=>$display_html);

	$sel_style_course_num = "<select name=\"style_course_num\" onchange=\"hantei_style_select();\">\n";
	$sel_style_stage_num = "<select name=\"style_stage_num\" onchange=\"hantei_style_select();\">\n";
	$L_STYLE_COURSE_LIST = course_list();
	foreach($L_STYLE_COURSE_LIST as $key => $val) {
		$sel_style_course_num .= "<option value=\"$key\"";
		if ($style_course_num == $key) {
			$sel_style_course_num .= " selected";
		}
		$sel_style_course_num .= ">".$val."</option>\n";
	}

	$L_STYLE_STAGE_NUM = set_style_stage_list($style_course_num);
	if (count($L_STYLE_STAGE_NUM) == 0) {
		$sel_style_stage_num .= "<option val=\"0\">コースを選択してください</option>";
	} else {
		$sel_style_stage_num .= "<option val=\"0\">選択してください</option>";
		$selected_flg = 0;
		foreach($L_STYLE_STAGE_NUM as $key => $val) {
			$sel_style_stage_num .= "<option value=\"".$key."\"";
			if ($style_stage_num == $key) {
				$sel_style_stage_num .= " selected";
				$selected_flg = 1;
			}
			$sel_style_stage_num .= ">".$val."</option>\n";
		}
		if ($selected_flg == 0) {
			//コースに該当する stage データが無い場合(style_course_numが再選択された場合)は、style_stage_num を 0 にする
			$style_stage_num = 0;
		}
	}
	$sel_style_course_num .= "</select/>\n";
	$sel_style_stage_num .= "</select/>\n";


	/* del start okabe 2012/09/12 "スタイル基準ignore"の入力修正の一時的無効化
	//スタイル基準コース、ステージの選択
	$INPUTS[STYLECOURSENUM] 	= array('result'=>'plane','value'=>$sel_style_course_num);
	$INPUTS[STYLESTAGENUM] 		= array('result'=>'plane','value'=>$sel_style_stage_num);
	*/ // del end okabe 2012/09/12 "スタイル基準ignore"の入力修正の一時的無効化


	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"変更確認\">";
	$html .= "<input type=\"reset\" value=\"クリア\">";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"hantei_master_list\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}


/**
 * スタイルの選択操作
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return array エラーの場合
 */
function select_style($ERROR) {
//echo "--select_style--<br/>";


	$_SESSION['UPDATE'] = array();
	//入力データの読み出し再設定
	$hantei_default_num = $_POST['hantei_default_num'];
	$service_num = $_POST['service_num'];
	$hantei_type = $_POST['hantei_type'];
	$course_num = $_POST['course_num'];
	$hantei_disp_num = $_POST['hantei_disp_num'];
	$hantei_name = $_POST['hantei_name'];

	if (SW_HANTEI_DEFAULT_KEY) {
		$hantei_default_key = $_POST['hantei_default_key'];
	}

	$random = $_POST['random'];
	$clear_rate = $_POST['clear_rate'];
	$problem_count = $_POST['problem_count'];
	$hantei_result = $_POST['hantei_result'];
	$clear_msg = $_POST['clear_msg'];
	$break_msg = $_POST['break_msg'];
	$limit_time = $_POST['limit_time'];
	$display = $_POST['display'];
	$style_course_num = $_POST['style_course_num'];
	$style_stage_num = $_POST['style_stage_num'];

	$_SESSION['UPDATE']['service_num'] = $service_num;
	$_SESSION['UPDATE']['hantei_type'] = $hantei_type;
	$_SESSION['UPDATE']['course_num']  = $course_num;
	$_SESSION['UPDATE']['hantei_disp_num'] = $hantei_disp_num;
	$_SESSION['UPDATE']['hantei_name'] = $hantei_name;

	if (SW_HANTEI_DEFAULT_KEY) {
		$_SESSION['UPDATE']['hantei_default_key'] = $hantei_default_key;	// hantei_default_key 判定テスト管理キー取り扱い
	}

	$_SESSION['UPDATE']['random'] = $random;
	$_SESSION['UPDATE']['clear_rate'] = $clear_rate;
	$_SESSION['UPDATE']['problem_count'] = $problem_count;
	$_SESSION['UPDATE']['hantei_result'] = $hantei_result;
	//$_SESSION['UPDATE']['HANTEI_RESULT_L'] = $HANTEI_WHOLE_DATA_L;	//判定結果 "::" で split した情報より db から取得した結果
	$_SESSION['UPDATE']['clear_msg'] = $clear_msg;
	$_SESSION['UPDATE']['break_msg'] = $break_msg;
	$_SESSION['UPDATE']['limit_time'] = $limit_time;
	$_SESSION['UPDATE']['display'] = $display;
	$_SESSION['UPDATE']['style_course_num'] = $style_course_num;
	$_SESSION['UPDATE']['style_stage_num'] = $style_stage_num;
	if ($hantei_default_num > 0) {
		$_SESSION['UPDATE']['hantei_default_num'] = $hantei_default_num;
	}

	return $ERROR;
}




/**
 * 新規登録・修正　必須項目チェック
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array &$ERROR
 */
function check(&$ERROR) {
//echo "--check--<br/>";

	$_SESSION['UPDATE'] = array();

	//入力データの取り出しチェック
	$hantei_default_num = $_POST['hantei_default_num'];

	$service_num = $_POST['service_num'];
	$service_num = mb_convert_kana($service_num,"asKV", "UTF-8");
	$service_num = trim($service_num);

	$hantei_type = $_POST['hantei_type'];
	$hantei_type = mb_convert_kana($hantei_type,"asKV", "UTF-8");
	$hantei_type = trim($hantei_type);

	$course_num = $_POST['course_num'];
	$course_num = mb_convert_kana($course_num,"asKV", "UTF-8");
	$course_num = trim($course_num);

	$hantei_disp_num = $_POST['hantei_disp_num'];
	$hantei_disp_num = mb_convert_kana($hantei_disp_num,"asKV", "UTF-8");
	$hantei_disp_num = trim($hantei_disp_num);

	$hantei_name = $_POST['hantei_name'];
	$hantei_name = trim($hantei_name);

	if (SW_HANTEI_DEFAULT_KEY) {
		// hantei_default_key 判定テスト管理キー取り扱い
		$hantei_default_key = $_POST['hantei_default_key'];
		$hantei_default_key = mb_convert_kana($hantei_default_key,"asKV", "UTF-8");
		$hantei_default_key = trim($hantei_default_key);
		// hantei_default_key 扱い、ここまで
	}
	$random = $_POST['random'];

	$clear_rate = $_POST['clear_rate'];
	$clear_rate = mb_convert_kana($clear_rate,"asKV", "UTF-8");
	$clear_rate = trim($clear_rate);

	$problem_count = $_POST['problem_count'];
	$problem_count = mb_convert_kana($problem_count,"asKV", "UTF-8");
	$problem_count = trim($problem_count);

	$hantei_result = $_POST['hantei_result'];
	$hantei_result = mb_convert_kana($hantei_result,"asKV", "UTF-8");
	$hantei_result = trim($hantei_result);

	//add start okabe 2013/10/04
	if ($hantei_type == 2) {	//コース内判定の場合のみ
		$hantei_result_last = $_POST['hantei_result_last'];
		$hantei_result_last = mb_convert_kana($hantei_result_last,"asKV", "UTF-8");
		$hantei_result_last = trim($hantei_result_last);
	}
	//add end okabe 2013/10/04

	$clear_msg = $_POST['clear_msg'];
	$clear_msg = trim($clear_msg);

	$break_msg = $_POST['break_msg'];
	$break_msg = trim($break_msg);

	$limit_time = $_POST['limit_time'];
	$limit_time = mb_convert_kana($limit_time,"asKV", "UTF-8");
	$limit_time = trim($limit_time);

	$display = $_POST['display'];

	/* del start okabe 2012/09/12 "スタイル基準ignore"の入力修正の一時的無効化
	$style_course_num = $_POST['style_course_num'];
	$style_stage_num = $_POST['style_stage_num'];
	*/ // del end okabe 2012/09/12 "スタイル基準ignore"の入力修正の一時的無効化
	// add start okabe 2012/09/12 "スタイル基準ignore"の入力修正の一時的無効化。有効にする際はココを削除
	$style_course_num = "5";	// toeic 400点コース
	$style_stage_num = "0";
	// add end okabe 2012/09/12 "スタイル基準ignore"の入力修正の一時的無効化。有効にする際はココを削除


	//入力データのチェック
	if ($service_num < 1) { $ERROR[] = "サービス番号が不明です。"; }
	if ($hantei_type < 1) { $ERROR[] = "判定タイプが不明です。"; }
	if ($hantei_type == "2" && $course_num < 1) { $ERROR[] = "コース番号が不明です。"; }

	if (strlen($hantei_disp_num) == 0) {
		$ERROR[] = "判定テスト表示番号が未入力です。";
	} elseif ($hantei_disp_num < 1 || !preg_match("/^[\d]+$/", $hantei_disp_num) || $hantei_disp_num > 999999999) {
		$ERROR[] = "判定テスト表示番号の値が正しくありません。";
	}

	//判定テスト表示番号の重複チェック	2013/09/09 add
	//if (!check_dup_hantei_disp_num($hantei_default_num, $hantei_disp_num)) {
	if (!check_dup_hantei_disp_num($hantei_default_num, $hantei_type, $course_num, $hantei_disp_num)) {		//2013/09/17
		$ERROR[] = "既に、同じ判定テスト表示番号が使われています。";
	}

//	if (strlen($hantei_name) == 0) { $ERROR[] = "判定名(範囲名)が未入力です。"; }		// del oda 2013/10/16 判定名を未入力可能とする

	if (SW_HANTEI_DEFAULT_KEY) {
		// hantei_default_key 判定テスト管理キー取り扱い
		if (strlen($hantei_default_key) == 0) {
			$ERROR[] = "判定テスト管理キーが未入力です。";
		} elseif (strlen($hantei_default_key) > 10 || !preg_match("/^[a-zA-Z0-9]+$/", $hantei_default_key)) {
			$ERROR[] = "判定テスト管理キーが正しくありません(10文字以内の英数字)。";
		}
		// hantei_default_key 扱い、ここまで
	}

	if (!$random) { $ERROR[] = "ランダム表示を選択してください。"; }

	if (strlen($clear_rate) == 0) {
		$ERROR[] = "判定正解率が未入力です。";
	} elseif ($clear_rate < 0.0 || !preg_match("/^([1-9]\d*|0)(\.\d+)?$/", $clear_rate) || $clear_rate > 100.0) {
		$ERROR[] = "判定正解率が正しくありません。";
	}

	if (strlen($problem_count) == 0) {
		$ERROR[] = "出題問題数が未入力です。";
	} elseif ($problem_count < 1 || !preg_match("/^[\d]+$/", $problem_count) || $problem_count > 999999999) {
		$ERROR[] = "出題問題数が正しくありません。";
	}

	if (strlen($style_course_num) == 0 || $style_course_num < 1) {
		$ERROR[] = "スタイル基準コースを選択してくだい。";
	}
	//if (strlen($style_stage_num) == 0 || $style_stage_num < 1) {	//必須とする場合は、コメントを外す
	//	$ERROR[] = "スタイル基準ステージを選択してくだい。";
	//}

	if (strlen($hantei_result) == 0) {
		$ERROR[] = "判定結果が未入力です。";
	}

	//"判定結果"のパース
	$err_prefix = "";	//csv時のみ使用
	//list($HANTEI_WHOLE_DATA_L, $ERROR) = analys_hantei_result($service_num, $hantei_result, $ERROR, $err_prefix);		//del okabe 2013/10/04
	list($HANTEI_WHOLE_DATA_L, $ERROR) = analys_hantei_result($service_num, 0, $hantei_result, $ERROR, $err_prefix);	//add okabe 2013/10/04

	//add start okabe 2013/10/04
	//"最終テスト合格時の判定結果" パース   コース内判定の場合のみ
	if ($hantei_type == 2) {
		$err_prefix = "";	//csv時のみ使用
		//list($HANTEI_WHOLE_DATA_LAST_L, $ERROR) = analys_hantei_result($service_num, $hantei_result_last, $ERROR, $err_prefix);	//del okabe 2013/10/04
		list($HANTEI_WHOLE_DATA_LAST_L, $ERROR) = analys_hantei_result($service_num, 1, $hantei_result_last, $ERROR, $err_prefix);	//add okabe 2013/10/04
	}
	//add end okabe 2013/10/04

	/* del start okabe 2012/09/12 "ドリルクリアメッセージignore" "判定結果メッセージignore"の入力修正の一時的無効化
	if (strlen($clear_msg) == 0) { $ERROR[] = "ドリルクリアメッセージが未入力です。"; }
	if (strlen($break_msg) == 0) { $ERROR[] = "判定結果メッセージが未入力です。"; }
	*/ // del end okabe 2012/09/12 "ドリルクリアメッセージignore" "判定結果メッセージignore"の入力修正の一時的無効化

	if (strlen($limit_time) == 0) {
		$ERROR[] = "ドリルクリア標準時間が未入力です。";
	} elseif ($limit_time < 1 || !preg_match("/^[\d]+$/", $limit_time) || $limit_time > 999999999) {
		$ERROR[] = "ドリルクリア標準時間が正しくありません。";
	}

	if (!$display) { $ERROR[] = "表示・非表示を選択してください。"; }


	//入力データをセッション情報に格納
	$_SESSION['UPDATE']['service_num'] = $service_num;
	$_SESSION['UPDATE']['hantei_type'] = $hantei_type;
	$_SESSION['UPDATE']['course_num']  = $course_num;

	$_SESSION['UPDATE']['hantei_disp_num'] = $hantei_disp_num;
	$_SESSION['UPDATE']['hantei_name'] = $hantei_name;

	if (SW_HANTEI_DEFAULT_KEY) {
		$_SESSION['UPDATE']['hantei_default_key'] = $hantei_default_key;	// hantei_default_key 判定テスト管理キー取り扱い
	}

	$_SESSION['UPDATE']['random'] = $random;
	$_SESSION['UPDATE']['clear_rate'] = $clear_rate;
	$_SESSION['UPDATE']['problem_count'] = $problem_count;
	$_SESSION['UPDATE']['hantei_result'] = $hantei_result;
	$_SESSION['UPDATE']['HANTEI_RESULT_L'] = $HANTEI_WHOLE_DATA_L;	//判定結果 "::" で split した情報より db から取得した結果

	//add start okabe 2013/10/04
	if ($hantei_type == 2) {	//コース内判定の場合のみ
		$_SESSION['UPDATE']['hantei_result_last'] = $hantei_result_last;
		$_SESSION['UPDATE']['HANTEI_RESULT_LAST_L'] = $HANTEI_WHOLE_DATA_LAST_L;	//最終テスト合格時の判定結果 "::" で split した情報より db から取得した結果
	}
	//add end okabe 2013/10/04

	$_SESSION['UPDATE']['clear_msg'] = $clear_msg;
	$_SESSION['UPDATE']['break_msg'] = $break_msg;
	$_SESSION['UPDATE']['limit_time'] = $limit_time;
	$_SESSION['UPDATE']['display'] = $display;

	$_SESSION['UPDATE']['style_course_num'] = $style_course_num;
	$_SESSION['UPDATE']['style_stage_num'] = $style_stage_num;

	if ($hantei_default_num > 0) {
		$_SESSION['UPDATE']['hantei_default_num'] = $hantei_default_num;
	}
}




/**
 * 判定テスト表示番号の重複チェック
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @date 2013/09/09
 * @author Azet
 * @param integer $hantei_default_num
 * @param integer $hantei_type
 * @param integer $course_num
 * @param integer $hantei_disp_num
 * @return boolean
 */
function check_dup_hantei_disp_num($hantei_default_num, $hantei_type, $course_num, $hantei_disp_num) {	//2013/09/17
//function check_dup_hantei_disp_num($hantei_default_num, $hantei_disp_num) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//重複あれば FALSE を返す
	$sql  = "SELECT hantei_default_num FROM ".T_HANTEI_MS_DEFAULT." hmd" .
			" WHERE mk_flg='0'".
			" AND hantei_type='".$hantei_type."'".	// チェック追加 2013/09/17
			" AND course_num='".$course_num."'".	// チェック追加 2013/09/17
			" AND hantei_display_num='".$hantei_disp_num."';";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		if ($list['hantei_default_num'] > 0) {
			if ($hantei_default_num != $list['hantei_default_num']) {
				return FALSE;
			}
		}
	}
	return TRUE;
}


/**
 * 新規登録・修正・削除　確認フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function check_html() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_DISPLAY, $L_ONOFF_TYPE;
	global $L_HANTEI_TYPE;

	if ($_SESSION['UPDATE']) {
		//セッション情報に入力データがあるとき
		$hantei_default_num = $_SESSION['UPDATE']['hantei_default_num'];	//詳細画面 viewform から、check を経てきた場合

		$service_num = $_SESSION['UPDATE']['service_num'];
		$hantei_type = $_SESSION['UPDATE']['hantei_type'];
		$course_num = $_SESSION['UPDATE']['course_num'];
		$hantei_disp_num = $_SESSION['UPDATE']['hantei_disp_num'];
		$hantei_name = $_SESSION['UPDATE']['hantei_name'];

		if (SW_HANTEI_DEFAULT_KEY) {
			$hantei_default_key = $_SESSION['UPDATE']['hantei_default_key'];			// hantei_default_key 判定テスト管理キー取り扱い
		}

		$random = $_SESSION['UPDATE']['random'];
		$clear_rate = $_SESSION['UPDATE']['clear_rate'];
		$problem_count = $_SESSION['UPDATE']['problem_count'];
		$hantei_result = $_SESSION['UPDATE']['hantei_result'];
		//add start okabe 2013/10/04
		if ($hantei_type == 2) {	//コース内判定の場合のみ
			$hantei_result_last = $_SESSION['UPDATE']['hantei_result_last'];	//最終テスト合格時の判定結果
			$hantei_result_last_start = "";
			$hantei_result_last_end = "";
		} else {
			$hantei_result_last = "&nbsp;";
			$hantei_result_last_start = "<!--";
			$hantei_result_last_end = "-->";
		}
		//add end okabe 2013/10/04
		$clear_msg = $_SESSION['UPDATE']['clear_msg'];
		$break_msg = $_SESSION['UPDATE']['break_msg'];
		$limit_time = $_SESSION['UPDATE']['limit_time'];
		$display = $_SESSION['UPDATE']['display'];
		$style_course_num = $_SESSION['UPDATE']['style_course_num'];
		$style_stage_num = $_SESSION['UPDATE']['style_stage_num'];

	} else {
		if ($_POST['hantei_default_num'] > 0 && MODE == "delete") {		//MODE == "削除" 部分を変更
			//削除指示で、hantei_default_num の指示があるときは該当データを表示する

			$sql  = "SELECT ".
				" hantei_ms_default.hantei_default_num,".
				" hantei_ms_default.service_num,".
				" hantei_ms_default.hantei_type,".
				" hantei_ms_default.course_num,".
				" hantei_ms_default.hantei_display_num,".
				" hantei_ms_default.style_course_num,".
				" hantei_ms_default.style_stage_num,".
				" hantei_ms_default.hantei_name,";

			if (SW_HANTEI_DEFAULT_KEY) {
				$sql .= " hantei_ms_default.hantei_default_key,";		// hantei_default_key 判定テスト管理キー（フォーム非表示）
			}

			$sql .= " hantei_ms_default.rand_type,".
				" hantei_ms_default.clear_rate,".
				" hantei_ms_default.problem_count,".
				" hantei_ms_default.limit_time,".
				" hantei_ms_default.clear_msg,".
				" hantei_ms_default.break_msg,".
				" hantei_ms_default.display".
				" FROM " . T_HANTEI_MS_DEFAULT . " hantei_ms_default" .
				" WHERE hantei_ms_default.hantei_default_num='".$_POST['hantei_default_num']."' AND hantei_ms_default.mk_flg='0' LIMIT 1;";
			$result = $cdb->query($sql);
			$list = $cdb->fetch_assoc($result);
			if (!$list) {
				$html .= "既に削除されているか、不正な情報が混ざっています。";
				return $html;
			}

			$hantei_default_num = $list['hantei_default_num'];
			$service_num = $list['service_num'];
			$hantei_type = $list['hantei_type'];

			$hantei_disp_num = $list['hantei_display_num'];
			$hantei_name = $list['hantei_name'];

			if (SW_HANTEI_DEFAULT_KEY) {
				$hantei_default_key = $list['hantei_default_key'];		// hantei_default_key 判定テスト管理キー（フォーム非表示）
			}

			$random = $list['rand_type'];
			$clear_rate = $list['clear_rate'];
			$problem_count = $list['problem_count'];
			$limit_time = $list['limit_time'];
			$clear_msg = $list['clear_msg'];
			$break_msg = $list['break_msg'];
			$display = $list['display'];

			$style_course_num = $list['style_course_num'];
			$style_stage_num = $list['style_stage_num'];

			//$hantei_result = このデータは、hantei_ms_break_layer から取り出す
			//add okabe start 2013/09/03
			$sql2  = "SELECT ".
				" hantei_ms_break_layer.course_num,".
				" hantei_ms_break_layer.stage_num,".
				" hantei_ms_break_layer.lesson_num,".
				" hantei_ms_break_layer.unit_num,".
				" hantei_ms_break_layer.block_num".
				" FROM " . T_HANTEI_MS_BREAK_LAYER . " hantei_ms_break_layer" .
				" WHERE hantei_ms_break_layer.mk_flg='0' AND hantei_ms_break_layer.hantei_default_num='".$hantei_default_num."'".	//edit okabe 2013/10/04
				" AND hantei_ms_break_layer.break_layer_type='0';";			//add okabe 2013/10/04
			$result2 = $cdb->query($sql2);

			$hantei_result = "";
			while ($list2=$cdb->fetch_assoc($result2)) {
				$hantei_result = rebuild_hantei_result($service_num, $list2, $hantei_result);		//判定結果の設定情報の再組み立て
			}
			//add okabe end 2013/09/03

			//add start okabe 2013/10/04
			$hantei_result_last = "";
			if ($hantei_type == 2) {	//コース内判定の場合のみ
				$sql2  = "SELECT ".
					" hantei_ms_break_layer.course_num,".
					" hantei_ms_break_layer.stage_num,".
					" hantei_ms_break_layer.lesson_num,".
					" hantei_ms_break_layer.unit_num,".
					" hantei_ms_break_layer.block_num".
					" FROM " . T_HANTEI_MS_BREAK_LAYER . " hantei_ms_break_layer" .
					" WHERE hantei_ms_break_layer.mk_flg='0' AND hantei_ms_break_layer.hantei_default_num='".$hantei_default_num."'".	//edit okabe 2013/10/04
					" AND hantei_ms_break_layer.break_layer_type='1';";			//add okabe 2013/10/04
				$result2 = $cdb->query($sql2);

				while ($list2=$cdb->fetch_assoc($result2)) {
					$hantei_result_last = rebuild_hantei_result($service_num, $list2, $hantei_result_last);		//"最終テスト合格時の判定結果"の設定情報の再組み立て
				}
				$hantei_result_last_start = "";
				$hantei_result_last_end = "";
			} else {
				$hantei_result_last = "&nbsp;";
				$hantei_result_last_start = "<!--";
				$hantei_result_last_end = "-->";
			}
			//add end okabe 2013/10/04

			$course_num = 0;	//判定タイプがコース内判定のとき、コース番号 course_num を扱う
			if ($hantei_type == 2) { $course_num = $list['course_num']; }

		} else {
			//セッションに入力データが無いときは、新規データの入力フォームページを表示
			$html = addform($ERROR);
			return $html;
		}
	}


	//サービス名
	$service_name = get_service_name($service_num);

	//判定タイプ
	$hantei_type_name = $L_HANTEI_TYPE[$hantei_type];

	//コース名
	if ($hantei_type == 2) {
		//"コース内判定" の場合
		$course_name = "";
		$sql = "SELECT course.course_num, course.course_name".
			" FROM ".T_SERVICE_COURSE_LIST." service_course_list, ".
			T_COURSE." course ".
			" WHERE course.course_num = service_course_list.course_num".
			" AND course.state = 0".
			" AND service_course_list.mk_flg = 0".
			" AND service_course_list.service_num ='".$service_num."';";
		if ($result = $cdb->query($sql)) {
			while ($list = $cdb->fetch_assoc($result)) {
				if ($course_num == $list['course_num']) {
					$course_name .= $list['course_name'];
				}
			}
		}
	} else {
		//"コース判定" の時
		$course_name = "---";
	}

	//ランダム表示 の設定値
	$random_html = "";
	foreach($L_ONOFF_TYPE as $key => $val) {
		if ($val == "") { continue; }
		if ($key == $random) {
			$random_html .= $val;
		}
	}

	//表示・非表示 の設定値
	$display_html = "";
	foreach($L_DISPLAY as $key => $val) {
		if ($val == "") { continue; }
		if ($key == $display) {
			$display_html .= $val;
		}
	}

	//コースリスト
	$L_COURSE_LIST = course_list();


	if (MODE != "delete") { $button = "登録"; } else { $button = "削除"; }		//最初の MODE != "削除" 部分を変更
	$html = "<br>\n";
	$html .= "確認画面：以下の内容で".$button."してもよろしければ".$button."ボタンをクリックしてください。<br>\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(TEST_PRACTICE_HANTEI_TEST_MASTER);

	if (intval($hantei_default_num) == 0) { $hantei_default_num = "---"; }

	//スタイル基準コース、ステージ名の用意
	$sel_style_course_name = $L_COURSE_LIST[$style_course_num];
	$L_STYLE_STAGE_NUM = set_style_stage_list($style_course_num);
	if (strlen($style_stage_num) == 0 || $style_stage_num < 1) {
		$sel_style_stage_name = "-";
	} else {
		$sel_style_stage_name = $L_STYLE_STAGE_NUM[$style_stage_num];
	}

	//$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	$INPUTS[BLOCKNUM] 		= array('result'=>'plane','value'=>$hantei_default_num);
	$INPUTS[SERVICENAME]	= array('result'=>'plane','value'=>$service_name);
	$INPUTS[HANTEITYPENAME]	= array('result'=>'plane','value'=>$hantei_type_name);
	$INPUTS[COURSENAME]		= array('result'=>'plane','value'=>$course_name);
	$INPUTS[HANTEIDISPNUM] 	= array('result'=>'plane','value'=>$hantei_disp_num);
	//$INPUTS[STYLECOURSENUM] = array('result'=>'plane','value'=>$sel_style_course_name);	// del okabe 2012/09/12 "スタイル基準ignore"の入力修正の一時的無効化
	$INPUTS[STYLESTAGENUM] 	= array('result'=>'plane','value'=>$sel_style_stage_name);
	$INPUTS[HANTEINAME] 	= array('result'=>'plane','value'=>$hantei_name);

	if (SW_HANTEI_DEFAULT_KEY) {
		$INPUTS[HANTEIDEFAULTKEY] 	= array('result'=>'plane','value'=>$hantei_default_key);		// hantei_default_key 判定テスト管理キー取り扱い
	}

	$INPUTS[RANDTYPE] 		= array('result'=>'plane','value'=>$random_html);
	$INPUTS[CLEARRATE] 		= array('result'=>'plane','value'=>$clear_rate);
	$INPUTS[PROBLEMCNT] 	= array('result'=>'plane','value'=>$problem_count);
	$INPUTS[HANTEIRESULT] 	= array('result'=>'plane','value'=>$hantei_result);
	$INPUTS[HANTEIRESULTLAST] 	= array('result'=>'plane','value'=>$hantei_result_last);	//add okabe 2013/10/04
	$INPUTS[HANTEIRESULTLASTSTART] 	= array('result'=>'plane','value'=>$hantei_result_last_start);	//add okabe 2013/10/04
	$INPUTS[HANTEIRESULTLASTEND] 	= array('result'=>'plane','value'=>$hantei_result_last_end);	//add okabe 2013/10/04
	$INPUTS[CLEARMSG] 		= array('result'=>'plane','value'=>$clear_msg);
	$INPUTS[BREAKMSG] 		= array('result'=>'plane','value'=>$break_msg);
	$INPUTS[LIMITTIME] 		= array('result'=>'plane','value'=>$limit_time);
	$INPUTS[DISPLAY] 		= array('result'=>'plane','value'=>$display_html);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" style=\"float:left\">\n";
	$html .= $HIDDEN;

	if (MODE == "detail") {			//詳細 処理
		$html .= "<input type=\"hidden\" name=\"action\" value=\"change\">";

	} elseif (MODE == "delete") {	//削除 処理
		$html .= "<input type=\"hidden\" name=\"action\" value=\"change\">";
		$html .= "<input type=\"hidden\" name=\"hantei_default_num\" value=\"".$hantei_default_num."\">";

	} else {
		$html .= "<input type=\"hidden\" name=\"action\" value=\"add\">";
	}

	$html .= "<input type=\"submit\" value=\"".$button."\">\n";
	$html .= "</form>";

	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";

	$html .= "<input type=\"submit\" value=\"戻る\">\n";

	/*戻る*/
	if (MODE == "delete") {
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"hantei_test_list\">\n";
	}
	$html .= "<input type=\"hidden\" name=\"action\" value=\"back\">";

	$html .= "</form>\n";

	return $html;
}



/**
 * DB新規登録
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function add() {

	if ($_SESSION['UPDATE']) {
		//セッション情報に入力データがあるとき
		$service_num = $_SESSION['UPDATE']['service_num'];
		$hantei_type = $_SESSION['UPDATE']['hantei_type'];
		$course_num = $_SESSION['UPDATE']['course_num'];
		$hantei_disp_num = $_SESSION['UPDATE']['hantei_disp_num'];
		$hantei_name = $_SESSION['UPDATE']['hantei_name'];

		if (SW_HANTEI_DEFAULT_KEY) {
			$hantei_default_key = $_SESSION['UPDATE']['hantei_default_key'];			// hantei_default_key 判定テスト管理キー取り扱い
		}

		$random = $_SESSION['UPDATE']['random'];
		$clear_rate = $_SESSION['UPDATE']['clear_rate'];
		$problem_count = $_SESSION['UPDATE']['problem_count'];
		$hantei_result = $_SESSION['UPDATE']['hantei_result'];
		$hantei_result_last = $_SESSION['UPDATE']['hantei_result_last'];	//add okabe 2013/10/04

		$clear_msg = $_SESSION['UPDATE']['clear_msg'];
		$break_msg = $_SESSION['UPDATE']['break_msg'];
		$limit_time = $_SESSION['UPDATE']['limit_time'];
		$display = $_SESSION['UPDATE']['display'];

		$style_course_num = $_SESSION['UPDATE']['style_course_num'];
		$style_stage_num = $_SESSION['UPDATE']['style_stage_num'];

		$INSERT_DATA = array();
		$INSERT_DATA[service_num]		 	= $service_num;		//サービス管理番号
		$INSERT_DATA[hantei_type]		 	= $hantei_type;		//判定単位（1：コース判定　2：コース内判定）
		$INSERT_DATA[course_num]		 	= $course_num;		//コース管理番号		"コース判定"のときの値は 0
		$INSERT_DATA[list_num] 				= "0";				//ソート番号(初期利用無)	※続く処理で、hantei_default_num の値に更新する
		$INSERT_DATA[hantei_display_num] 	= $hantei_disp_num;	//判定テスト表示番号（ソート利用）
		$INSERT_DATA[style_course_num] 		= $style_course_num;//スタイル基準コース番号
		$INSERT_DATA[style_stage_num] 		= $style_stage_num;	//スタイル基準ステージ番号
		$INSERT_DATA[hantei_name] 			= $hantei_name;		//判定名（範囲名）

		if (SW_HANTEI_DEFAULT_KEY) {
			$INSERT_DATA[hantei_default_key] 	= $hantei_default_key;				//hantei_default_key 判定テスト管理キー取り扱い
		}

		$INSERT_DATA[rand_type] 			= $random;			//ランダム出題（1：する[ランダム出題]　2：しない[順番出題]）
		$INSERT_DATA[clear_rate] 			= $clear_rate;		//判定正解率
		$INSERT_DATA[problem_count] 		= $problem_count;	//出題問題数
		$INSERT_DATA[limit_time] 			= $limit_time;		//ドリルクリアー標準時間
		$INSERT_DATA[clear_msg] 			= $clear_msg;		//クリアーメッセージ
		$INSERT_DATA[break_msg] 			= $break_msg;		//判定結果メッセージ
		$INSERT_DATA[display] 				= $display;			//表示（1：表示 2：非表示）

		//list($ERROR, $new_hantei_default_num) = add_hantei_test_master($INSERT_DATA, $_SESSION['UPDATE']['HANTEI_RESULT_L'], $_SESSION['myid']['id']);	//判定テストマスターの新規登録	//del okabe 2013/10/04
		list($ERROR, $new_hantei_default_num) = add_hantei_test_master($INSERT_DATA, $_SESSION['UPDATE']['HANTEI_RESULT_L'], $_SESSION['UPDATE']['HANTEI_RESULT_LAST_L'], $_SESSION['myid']['id']);	//判定テストマスターの新規登録	//add okabe 2013/10/04

	} else {
		//セッションに入力データが無いときは、やり直し
		$ERROR[] = "入力情報（セッション情報）が取得できませんでした。";
	}


	if (!$ERROR) {
		$_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>";
		$_SESSION['UPDATE'] = array();		//登録で使用していた入力データをクリア
	}
	return $ERROR;
}



/**
 * DB更新・削除 処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function change() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$UPDATE_DATA = array();

	if (MODE == "detail") {		//詳細 処理

		if ($_SESSION['UPDATE']) {
			//セッション情報に入力データがあるとき
			$hantei_default_num = $_SESSION['UPDATE']['hantei_default_num'];
			if ($hantei_default_num > 0) {
				$service_num = $_SESSION['UPDATE']['service_num'];
				$hantei_type = $_SESSION['UPDATE']['hantei_type'];
				$course_num = $_SESSION['UPDATE']['course_num'];
				$hantei_disp_num = $_SESSION['UPDATE']['hantei_disp_num'];
				$hantei_name = $_SESSION['UPDATE']['hantei_name'];

				if (SW_HANTEI_DEFAULT_KEY) {
					$hantei_default_key = $_SESSION['UPDATE']['hantei_default_key'];			// hantei_default_key 判定テスト管理キー取り扱い
				}

				$random = $_SESSION['UPDATE']['random'];
				$clear_rate = $_SESSION['UPDATE']['clear_rate'];
				$problem_count = $_SESSION['UPDATE']['problem_count'];
				$hantei_result = $_SESSION['UPDATE']['hantei_result'];
				$hantei_result_last = $_SESSION['UPDATE']['hantei_result_last'];	//add okabe 2013/10/04
				$clear_msg = $_SESSION['UPDATE']['clear_msg'];
				$break_msg = $_SESSION['UPDATE']['break_msg'];
				$limit_time = $_SESSION['UPDATE']['limit_time'];
				$display = $_SESSION['UPDATE']['display'];

				$style_course_num = $_SESSION['UPDATE']['style_course_num'];
				$style_stage_num = $_SESSION['UPDATE']['style_stage_num'];

				$UPDATE_DATA[service_num]		 	= $service_num;		//サービス管理番号
				$UPDATE_DATA[hantei_type]		 	= $hantei_type;		//判定単位（1：コース判定　2：コース内判定）
				$UPDATE_DATA[course_num]		 	= $course_num;		//コース管理番号		"コース判定"のときの値は 0
				$UPDATE_DATA[hantei_display_num] 	= $hantei_disp_num;	//判定テスト表示番号（ソート利用）
				$UPDATE_DATA[style_course_num] 		= $style_course_num;//スタイル基準コース
				$UPDATE_DATA[style_stage_num] 		= $style_stage_num;	//スタイル基準ステージ
				$UPDATE_DATA[hantei_name] 			= $hantei_name;		//判定名（範囲名）

				if (SW_HANTEI_DEFAULT_KEY) {
					$UPDATE_DATA[hantei_default_key] 	= $hantei_default_key;				//hantei_default_key 判定テスト管理キー取り扱い
				}

				$UPDATE_DATA[rand_type] 			= $random;			//ランダム出題（1：する[ランダム出題]　2：しない[順番出題]）
				$UPDATE_DATA[clear_rate] 			= $clear_rate;		//判定正解率
				$UPDATE_DATA[problem_count] 		= $problem_count;	//出題問題数
				$UPDATE_DATA[limit_time] 			= $limit_time;		//ドリルクリアー標準時間
				$UPDATE_DATA[clear_msg] 			= $clear_msg;		//クリアーメッセージ
				$UPDATE_DATA[break_msg] 			= $break_msg;		//判定結果メッセージ
				$UPDATE_DATA[display] 				= $display;			//表示（1：表示 2：非表示）
				$UPDATE_DATA[upd_syr_id]			= "updateline";
				$UPDATE_DATA[upd_tts_id] 			= $_SESSION['myid']['id'];
				$UPDATE_DATA[upd_date] 				= "now()";

				$where = " WHERE hantei_default_num='".$hantei_default_num."' LIMIT 1;";
				$ERROR = $cdb->update(T_HANTEI_MS_DEFAULT, $UPDATE_DATA, $where);	// hantei_ms_default テーブルのデータ更新

				//$hantei_result 情報を、hantei_ms_break_layer テーブルの情報を更新
				$HANTEI_ONE_DATA_L = $_SESSION['UPDATE']['HANTEI_RESULT_L'];

				//$ERROR = update_hantei_ms_break_layer($hantei_default_num, $HANTEI_ONE_DATA_L);	//hantei_ms_break_layerデータの更新		//del okabe 2013/10/04
				$ERROR = update_hantei_ms_break_layer($hantei_default_num, 0, $HANTEI_ONE_DATA_L);	//hantei_ms_break_layerデータの更新		//del okabe 2013/10/04

				//add start okabe 2013/10/04
				if ($hantei_type == 2) {		//コース内判定の場合、かつ入力がある場合のみ
					//$hantei_result_last 最終テスト合格時の判定結果データを、hantei_ms_break_layer テーブルの情報を更新
					$HANTEI_ONE_DATA_LAST_L = $_SESSION['UPDATE']['HANTEI_RESULT_LAST_L'];

					$ERROR = update_hantei_ms_break_layer($hantei_default_num, 1, $HANTEI_ONE_DATA_LAST_L);	//hantei_ms_break_layerデータの更新
				}
				//add end okabe 2013/10/04

			} else {
				//hantei_default_num の値が 0 または無い
				$ERROR[] = "入力情報（セッション情報）に問題が発生しました。";
			}

		} else {
			//セッションに入力データが無いときは、やり直し
			$ERROR[] = "入力情報（セッション情報）が取得できませんでした。";
		}


	} elseif (MODE == "delete") {		//MODE == "削除" を変更
		$hantei_default_num = $_POST['hantei_default_num'];
		if ($hantei_default_num > 0) {
			$UPDATE_DATA[mk_flg] 				= "1";			//無効フラグ（1：削除）
			$UPDATE_DATA[mk_tts_id] 			= $_SESSION['myid']['id'];
			$UPDATE_DATA[mk_date] 				= "now()";
			$where = " WHERE hantei_default_num='".$hantei_default_num."' LIMIT 1;";
			$ERROR = $cdb->update(T_HANTEI_MS_DEFAULT, $UPDATE_DATA, $where);

			//従属する hantei_ms_break_layer のデータも mk_flg をセットする
			$UPDATE_DATA = array();
			$UPDATE_DATA[mk_flg]		= "1";			//無効フラグ（1：削除）
			$UPDATE_DATA[mk_tts_id] 	= $_SESSION['myid']['id'];
			$UPDATE_DATA[mk_date] 		= "now()";
			$where = " WHERE hantei_default_num='".$hantei_default_num."'";
			$ERROR = $cdb->update(T_HANTEI_MS_BREAK_LAYER, $UPDATE_DATA, $where);	// hantei_ms_default テーブル更新(無効フラグセット)

		} else {
			$ERROR[] = "更新情報が取得できませんでした。";
		}
	}

	if (!$ERROR) {
		$_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>";
		$_SESSION['UPDATE'] = array();		//データ受け取りで使用していた変数をクリア

	}

	unset($UPDATE_DATA);

	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}


/**
 * 表示順上昇処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array &$ERROR
 */
function up(&$ERROR) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql  = "SELECT * FROM " . T_HANTEI_MS_DEFAULT . " WHERE hantei_default_num='".$_POST['hantei_default_num']."' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$m_hantei_default_num = $list['hantei_default_num'];
		$m_list_num = $list['list_num'];
		$m_service_num = $list['service_num'];
		$m_hantei_type = $list['hantei_type'];
		$m_course_num = $list['course_num'];
	}
	if (!$m_hantei_default_num || !$m_list_num) { $ERROR[] = "移動する判定テスト情報が取得できません。"; }

	if (!$ERROR) {
		$sql  = "SELECT * FROM " . T_HANTEI_MS_DEFAULT . " WHERE mk_flg='0'".
			" AND service_num='".$m_service_num."'".
			" AND hantei_type='".$m_hantei_type."'".
			" AND course_num='".$m_course_num."'".
			" AND list_num<'".$m_list_num."' ORDER BY list_num DESC LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$c_hantei_default_num = $list['hantei_default_num'];
			$c_list_num = $list['list_num'];
		}
	}
	if (!$c_hantei_default_num || !$c_list_num) { $ERROR[] = "移動される判定テスト情報が取得できません。"; }


	if (!$ERROR) {
		$INSERT_DATA[list_num] 	= $c_list_num;
		$INSERT_DATA[upd_syr_id] 	= "upline";
		$INSERT_DATA[upd_tts_id] 	= $_SESSION['myid']['id'];
		$INSERT_DATA[upd_date] 		= "now()";
		$where = " WHERE hantei_default_num='".$m_hantei_default_num."' LIMIT 1;";
		$ERROR = $cdb->update(T_HANTEI_MS_DEFAULT, $INSERT_DATA, $where);
	}

	if (!$ERROR) {
		$INSERT_DATA[list_num] 	= $m_list_num;
		$INSERT_DATA[upd_syr_id] 	= "downline";
		$INSERT_DATA[upd_tts_id] 	= $_SESSION['myid']['id'];
		$INSERT_DATA[upd_date] 		= "now()";
		$where = " WHERE hantei_default_num='".$c_hantei_default_num."' LIMIT 1;";
		$ERROR = $cdb->update(T_HANTEI_MS_DEFAULT, $INSERT_DATA, $where);
	}
	//return $ERROR;
}


/**
 * 表示順下降処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array &$ERROR
 */
function down(&$ERROR) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql  = "SELECT * FROM " . T_HANTEI_MS_DEFAULT . " WHERE hantei_default_num='".$_POST['hantei_default_num']."' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$m_hantei_default_num = $list['hantei_default_num'];
		$m_list_num = $list['list_num'];
		$m_service_num = $list['service_num'];
		$m_hantei_type = $list['hantei_type'];
		$m_course_num = $list['course_num'];
	}
	if (!$m_hantei_default_num || !$m_list_num) { $ERROR[] = "移動する判定テスト情報が取得できません。"; }

	if (!$ERROR) {
		$sql  = "SELECT * FROM " . T_HANTEI_MS_DEFAULT . " WHERE mk_flg='0'".
			" AND service_num='".$m_service_num."'".
			" AND hantei_type='".$m_hantei_type."'".
			" AND course_num='".$m_course_num."'".
			" AND list_num>'".$m_list_num."' ORDER BY list_num LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$c_hantei_default_num = $list['hantei_default_num'];
			$c_list_num = $list['list_num'];
		}
	}
	if (!$c_hantei_default_num || !$c_list_num) { $ERROR[] = "移動される判定テスト情報が取得できません。"; }

	if (!$ERROR) {
		$INSERT_DATA[list_num] = $c_list_num;
		$INSERT_DATA[upd_syr_id] 	= "downline";
		$INSERT_DATA[upd_tts_id] 	= $_SESSION['myid']['id'];
		$INSERT_DATA[upd_date] 		= "now()";
		$where = " WHERE hantei_default_num='".$m_hantei_default_num."' LIMIT 1;";
		$ERROR = $cdb->update(T_HANTEI_MS_DEFAULT, $INSERT_DATA, $where);
	}

	if (!$ERROR) {
		$INSERT_DATA[list_num] = $m_list_num;
		$INSERT_DATA[upd_syr_id] 	= "upline";
		$INSERT_DATA[upd_tts_id] 	= $_SESSION['myid']['id'];
		$INSERT_DATA[upd_date] 		= "now()";
		$where = " WHERE hantei_default_num='".$c_hantei_default_num."' LIMIT 1;";
		$ERROR = $cdb->update(T_HANTEI_MS_DEFAULT, $INSERT_DATA, $where);
	}
	//return $ERROR;
}


/**
 * csvエクスポート
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * 使用していません
 * @author Azet
 */
function csv_export() {
	//	2015/01/07 yoshizawa 使用していません

	global $L_CSV_COLUMN;

	list($csv_line,$ERROR) = make_csv($L_CSV_COLUMN['book_trial'],1);
	if ($ERROR) { return $ERROR; }

	$filename = "ms_trial.csv";

	header("Cache-Control: public");
	header("Pragma: public");
	header("Content-disposition: attachment;filename=$filename");
	if (stristr($HTTP_USER_AGENT, "MSIE")) {
		header("Content-Type: text/octet-stream");
	} else {
		header("Content-Type: application/octet-stream;");
	}
	echo $csv_line;

	exit;
}


/**
 * csv出力情報整形
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $L_CSV_COLUMN
 * @param mixed $head_mode='1'
 * @return array
 */
function make_csv($L_CSV_COLUMN,$head_mode='1') {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$csv_line = "";

	if (!is_array($L_CSV_COLUMN)) {
		$ERROR[] = "CSV抽出項目が設定されていません。";
		return array($csv_line,$ERROR);
	}
	//	head line (一行目)
	foreach ($L_CSV_COLUMN as $key => $val) {
		if ($head_mode == 1) { $head_name = $key; }
		elseif ($head_mode == 2) { $head_name = $val; }
		$csv_line .= $head_name.",";
	}
	$csv_line .= "\n";

	$sql  = "SELECT * FROM " . T_MS_BOOK .
		" WHERE mk_flg='0' AND publishing_id='0' ORDER BY disp_sort";
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			foreach ($L_CSV_COLUMN as $key => $val) {
				$csv_line .= $list[$key].",";
			}
			$csv_line .= "\n";
		}
		$cdb->free_result($result);
	}

	$csv_line = mb_convert_encoding($csv_line,"sjis-win","UTF-8");
	$csv_line = replace_decode_sjis($csv_line);

	return array($csv_line,$ERROR);
}




/**
 * サービス名を検索する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $service_num
 * @return string
 */
function get_service_name($service_num) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql  = "SELECT service_name FROM ".T_SERVICE.
			" WHERE mk_flg='0'".
			" AND service_num='".$service_num."';";
	$service_name = "";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$service_name = $list['service_name'];
	}
	return $service_name;
}


/**
 * コース一覧
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array
 */
function course_list() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$L_COURSE_LIST = array();
	$L_COURSE_LIST[] = "選択して下さい";
	$sql  = "SELECT * FROM ".T_COURSE. " WHERE state='0' ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		while ($list=$cdb->fetch_assoc($result)) {
			$L_COURSE_LIST[$list['course_num']] = $list['course_name'];
		}
	}
	return $L_COURSE_LIST;
}


/**
 * マスター＆問題データ csv出力
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function ht_csv_export() {
	global $L_CSV_COLUMN;

	$sel_service_num = $_SESSION['sub_session']['s_service_num'];	//出力するサービス番号

	//list($csv_line,$ERROR) = ht_make_csv($L_CSV_COLUMN['hantei_test'], $sel_service_num, 1, 1);		//in test_check_problem_data.php
	list($csv_line,$ERROR) = ht_make_csv_master($L_CSV_COLUMN['hantei_test_master'], $sel_service_num, 1, 1);		//in test_check_problem_data.php
	if ($ERROR) { return $ERROR; }

	//$filename = "hantei_test_master_problem_".$sel_service_num.".csv";
	$filename = "hantei_test_master_".$sel_service_num.".csv";

	header("Cache-Control: public");
	header("Pragma: public");
	header("Content-disposition: attachment;filename=$filename");
	if (stristr($HTTP_USER_AGENT, "MSIE")) {
		header("Content-Type: text/octet-stream");
	} else {
		header("Content-Type: application/octet-stream;");
	}
	echo $csv_line;

//return;
	exit;
}

?>