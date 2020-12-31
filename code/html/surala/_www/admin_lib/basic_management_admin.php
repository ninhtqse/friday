<?
/**
 * ベンチャー・リンク　すらら
 *
 * 基本管理　管理者管理
 *
 * @author Azet
 */
include_once("/data/home/www/decryt_field_db.php"); //add kaopiz 2020/25/06 load encrypt file
/**
 * HTMLコンテンツを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function start() {

	// 入力内容チェック
	if (ACTION == "check") {
		$ERROR = check();
	}

	// 登録／更新処理
	if (!$ERROR) {
		if (ACTION == "sub_session") { $ERROR = sub_session(); }
		elseif (ACTION == "add") { $ERROR = add(); }
		elseif (ACTION == "change") { $ERROR = change(); }
	}

	// ヘッダ部生成
	$html .= mode_menu();

	// 初期表示（管理者一覧表示）
	if (!MODE) { $html .= member_list(); }
	// 登録
	elseif (MODE == "addform") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= addform($ERROR); }
		} elseif (ACTION == "add") {
			if (!$ERROR) { $html .= member_list(); }
			else { $html .= addform($ERROR); }
		} else {
			$html .= addform($ERROR);
		}
	// 修正
	} elseif (MODE == "view") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= viewform($ERROR); }
		} elseif (ACTION == "change") {
			if (!$ERROR) { $html .= member_list(); }
			else { $html .= viewform($ERROR); }
		} else {
			$html .= viewform($ERROR);
		}
	// 削除
	} elseif (MODE == "del") {
		if (ACTION == "change") {
			if (!$ERROR) { $html .= member_list(); }
			else { $html .= check_html(); }
		} else {
			$html .= check_html();
		}
	// 管理者一覧表示
	} elseif (MODE == "member_list") {
		$html .= member_list();
	}

	return $html;
}

/**
 * メニュを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * ヘッダ部生成
 * 一覧表示ボタン／新規登録ボタン／ソート条件／表示件数／名称検索
 * @author Azet
 * @return string HTML
 */
function mode_menu() {

	// グローバル変数定義
	global $L_SEARCH_MANAGER,$L_ORDER_MANAGER,$L_DESC,$L_PAGE_VIEW;

	// ボタン以外のフィールド表示制御
	if (!MODE || MODE == "member_list" || MODE == "del_enterprise" || (MODE == "addform" && ACTION == "add") || (MODE == "view" && ACTION == "change") || (MODE == "del" && ACTION == "change") ) {

		// ソート条件
		foreach ($L_ORDER_MANAGER as $key => $val){
			if ($_SESSION['sub_session']['s_order'] == $key) { $sel = " selected"; } else { $sel = ""; }
			$s_order_html .= "<option value=\"$key\"$sel>$val</option>\n";
		}
		foreach ($L_DESC as $key => $val){
			if ($_SESSION['sub_session']['s_desc'] == $key) { $sel = " selected"; } else { $sel = ""; }
			$s_desc_html .= "<option value=\"$key\"$sel>$val</option>\n";
		}
		// 表示件数
		foreach ($L_PAGE_VIEW as $key => $val){
			if ($_SESSION['sub_session']['s_page_view'] == $key) { $sel = " selected"; } else { $sel = ""; }
			$s_page_view_html .= "<option value=\"$key\"$sel>$val</option>\n";
		}
		// 名称検索
		foreach ($L_SEARCH_MANAGER as $key => $val){
			if ($_SESSION['sub_session']['s_like'] == $key) { $sel = " selected"; } else { $sel = ""; }
			$s_like_html .= "<option value=\"$key\"$sel>$val</option>\n";
		}
		// 削除表示
		$del_chek = "";
		if ($_SESSION['sub_session']['del_show'] == "1") { $del_chek = " checked"; }

		// ｈｔｍｌ生成
		$sub_session_html .= "<td style=\"width:20px;\">&nbsp;</td>\n";
		$sub_session_html .= "<td>\n";
		$sub_session_html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
		$sub_session_html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
		$sub_session_html .= "ソート<select name=\"s_order\">\n";
		$sub_session_html .= "$s_order_html</select><select name=\"s_desc\">\n";
		$sub_session_html .= "$s_desc_html</select>表示数<select name=\"s_page_view\">\n";
		$sub_session_html .= "$s_page_view_html</select><input type=\"submit\" value=\"Set\">\n";
		$sub_session_html .= "</form>\n";
		$sub_session_html .= "</td>\n";
		$sub_session_html .= "<td style=\"width:20px;\">&nbsp;</td>\n";
		$sub_session_html .= "<td>\n";
		$sub_session_html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
		$sub_session_html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
		$sub_session_html .= "検索 名前\n";
		$sub_session_html .= "<input type=\"text\" name=\"s_manager_name\" value=\"".$_SESSION['sub_session']['s_manager_name']."\">";
		$sub_session_html .= "<input type=\"hidden\" name=\"show_flag\" value=\"1\">\n";
		$sub_session_html .= "<input type=\"submit\" value=\"Search\">\n";
		$sub_session_html .= "</form>\n";
		$sub_session_html .= "</td>\n";
		$sub_session_html .= "<td>\n";
		$sub_session_html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
		$sub_session_html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
		$sub_session_html .= "<input type=\"hidden\" name=\"s_manager_name\" value=\"\">\n";
		$sub_session_html .= "<input type=\"submit\" value=\"Reset\">\n";
		$sub_session_html .= "</form>\n";
		$sub_session_html .= "</td>\n";
		$sub_session_html .= "<td>\n";
		$sub_session_html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
		$sub_session_html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
		$sub_session_html .= "<input type=\"hidden\" name=\"show_flag\" value=\"1\">\n";
		$sub_session_html .= "<input type=\"hidden\" name=\"del_show_onoff\" value=\"1\">\n";
		$sub_session_html .= "<input type=\"checkbox\" name=\"del_show\" value=\"1\" onchange=\"this.form.submit();\" ".$del_chek.">削除データを表示する\n";
		$sub_session_html .= "</form>\n";
		$sub_session_html .= "</td>\n";
	}

	// ヘッダ部生成
	$html .= "<div id=\"mode_menu\">\n";
	$html .= "<table cellpadding=0 cellspacing=0>\n";
	$html .= "<tr>\n";
	$html .= "<td>\n";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"member_list\">\n";
	$html .= "<input type=\"submit\" value=\"一覧表示\">\n";
	$html .= "</form>\n";
	$html .= "</td>\n";
	if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__add",$_SESSION['authority'])===FALSE)) {
		$html .= "<td>\n";
		$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"addform\">\n";
		$html .= "<input type=\"submit\" value=\"新規登録\">\n";
		$html .= "</form>\n";
		$html .= "</td>\n";
	}
	$html .= $sub_session_html;
	$html .= "</tr>\n";
	$html .= "</table><br>\n";
	$html .= "</div>\n";

	return $html;
}

/**
 * SESSION情報設定
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function sub_session() {

	// ソート制御
	if (strlen($_POST['s_order'])) { $_SESSION['sub_session']['s_order'] = $_POST['s_order']; }
	if (strlen($_POST['s_desc'])) { $_SESSION['sub_session']['s_desc'] = $_POST['s_desc']; }

	// ページ制御
	if (strlen($_POST['s_page_view'])) { $_SESSION['sub_session']['s_page_view'] = $_POST['s_page_view']; }
	if (strlen($_POST['s_order'])&&strlen($_POST['s_desc'])&&strlen($_POST['s_page_view'])) { unset($_SESSION['sub_session']['s_page']); }
	if ($_POST['show_flag'] == "1") { unset($_SESSION['sub_session']['s_page']); }
	if (strlen($_POST['s_page'])) { $_SESSION['sub_session']['s_page'] = $_POST['s_page']; }

	// 名称検索
	if (strlen($_POST['s_like'])) { $_SESSION['sub_session']['s_like'] = $_POST['s_like']; } else { unset($_SESSION['sub_session']['s_like']); }
	if (strlen($_POST['s_word'])) { $_SESSION['sub_session']['s_word'] = $_POST['s_word']; } else { unset($_SESSION['sub_session']['s_word']); }
	if (isset($_POST['s_manager_name'])) { $_SESSION['sub_session']['s_manager_name'] = $_POST['s_manager_name']; }

	// 削除データ表示チェック
	if (strlen($_POST['del_show'])) { $_SESSION['sub_session']['del_show'] = $_POST['del_show']; } else {
		if ($_POST['del_show_onoff']) {
			unset($_SESSION['sub_session']['del_show']);
		}
	}

	return;
}

/**
 * 管理者情報一覧生成
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function member_list() {

	// グローバル変数定義
	global $L_PAGE_VIEW;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];


	// update start oda 2014/10/08 権限制御修正：削除データも出る様に修正
	//$sql = "SELECT manager.manager_id,manager.name,employee.employee_name FROM " .
	//	T_MANAGER . " " . T_MANAGER .
	//	" LEFT JOIN " . T_EMPLOYEE . " " . T_EMPLOYEE . " ON manager.kngn_lvl=employee.kngn_lvl" .
	//	" WHERE" .
	//	" manager.mk_flg='0'";
	$sql = "SELECT manager.manager_id,manager.name,employee.employee_name,manager.mk_flg FROM " .
			T_MANAGER . " manager " .
			" LEFT JOIN " . T_EMPLOYEE . " employee ON manager.kngn_lvl=employee.kngn_lvl";


	if ($_SESSION['sub_session']['s_manager_name']) {
		//$sql .= " AND manager.name LIKE '%".$_SESSION['sub_session']['s_manager_name']."%'";
		$sql .= " WHERE manager.name LIKE '%".$_SESSION['sub_session']['s_manager_name']."%'";

		if (!$_SESSION['sub_session']['del_show']) {
			$sql .= " AND manager.mk_flg='0'";
		}
	} else {
		if (!$_SESSION['sub_session']['del_show']) {
			$sql .= " WHERE manager.mk_flg='0'";
		}
	}
	// update end oda 2014/10/08

	// 対象件数取得
	if ($result = $cdb->query($sql)) {
		$list_count = $cdb->num_rows($result);
	}

	// ソート条件設定
	if ($_SESSION['sub_session']['s_order'] == 1) {
		$sql .= " ORDER BY manager.kana";
	} else {
		$sql .= " ORDER BY manager.manager_id";
	}
	if ($_SESSION['sub_session']['s_desc']) {
		$sql .= " DESC";
	}

	// 表示ページ設定
	if ($L_PAGE_VIEW[$_SESSION['sub_session']['s_page_view']]) {
		$page_view = $L_PAGE_VIEW[$_SESSION['sub_session']['s_page_view']];
	} else {
		$page_view = $L_PAGE_VIEW[0];
	}
	$max_page = ceil($list_count/$page_view);

	if ($_SESSION['sub_session']['s_page']) {
		$page = $_SESSION['sub_session']['s_page'];
	} else {
		$page = 1;
	}

	$start = ($page - 1) * $page_view;
	$next = $page + 1;
	$back = $page - 1;

	// 表示件数をSQLに設定
	$sql .= " LIMIT $start,$page_view;";

	// 対象データ存在チェック
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
	}

	if (!$max) {
		$html .= "現在、登録管理者は存在しません。";
		return $html;
	}

	// html生成
	$html .= "<div style=\"float:left;\">登録管理者総数($list_count):PAGE[$page/$max_page]</div>\n";
	// ページ制御ボタン（前頁／次頁）
	if ($back > 0) {
		$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" style=\"float:left;\">";
		$html .= "<input type=\"submit\" value=\"前のページ\">";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
		$html .= "<input type=\"hidden\" name=\"s_page\" value=\"$back\">\n";
		$html .= "</form>";
	}
	if ($page < $max_page) {
		$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" style=\"float:left;\">";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
		$html .= "<input type=\"hidden\" name=\"s_page\" value=\"$next\">\n";
		$html .= "<input type=\"submit\" value=\"次のページ\">";
		$html .= "</form>";
	}

	$html .= "<br style=\"clear:left;\">";
	$html .= "<table class=\"member_form\">\n";
	$html .= "<tr class=\"member_form_menu\">\n";
	$html .= "<td>ID</td>\n";
	$html .= "<td>名前</td>\n";
	$html .= "<td>レベル</td>\n";
	if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__view",$_SESSION['authority'])===FALSE)) {
		$html .= "<td>詳細</td>\n";
	}

	// add start oda 2014/10/08 権限制御修正
	if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__del",$_SESSION['authority'])===FALSE)) {
		$html .= "<td>削除</td>\n";
	}
	// add end oda 2014/10/08 権限制御修正

	$html .= "</tr>\n";
	while ($list = $cdb->fetch_assoc($result)) {
		foreach($list as $key => $val) {
			$list[$key] = replace_decode($val);
		}
		if ($list['employee_name']) {
			$employee_name = $list[employee_name];
		} else {
			$employee_name = "未設定";
		}
		$html .= "<tr class=\"member_form_cell\">\n";
		$html .= "<td>".$list['manager_id']."</td>\n";
		$html .= "<td>".$list['name']."</td>\n";
		$html .= "<td>".$employee_name."</td>\n";
		if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__view",$_SESSION['authority'])===FALSE)) {
			$html .= "<td>\n";
			$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"view\">\n";
			$html .= "<input type=\"hidden\" name=\"manager_id\" value=\"".$list['manager_id']."\">\n";
			$html .= "<input type=\"submit\" value=\"詳細\">\n";
			$html .= "</form>\n";
			$html .= "</td>\n";
		}

		// add start oda 2014/10/08 権限制御修正
		if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__del",$_SESSION['authority'])===FALSE)) {
			if ($list['mk_flg'] == 0) {
				$html .= "<td>\n";
				$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
				$html .= "<input type=\"hidden\" name=\"mode\" value=\"del\">\n";
				$html .= "<input type=\"hidden\" name=\"manager_id\" value=\"".$list['manager_id']."\">\n";
				$html .= "<input type=\"submit\" value=\"削除\">\n";
				$html .= "</form>\n";
				$html .= "</td>\n";
			} else {
				$html .= "<td>\n";
				$html .= "<span style=\"color:red\">\n";
				$html .= "削除済\n";
				$html .= "</span>\n";
				$html .= "</td>\n";
			}
		}
		// add end oda 2014/10/08 権限制御修正

		$html .= "</tr>\n";
	}

	$html .= "</table>\n";
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

	// グローバル変数定義
	global $L_MANAGER_LEVEL;

	// 権限の情報を取得し、配列に格納
	$L_EMPLOYEE_LEVEL = get_employee_level_list();

	$manager_id_input = "<input type=\"text\" name=\"manager_id\" value=\"".$_POST['manager_id']."\" maxlength=\"10\" />"; //add yamaguchi 2018/10/19 IDは10文字以上入力させない

	// html生成
	$html = "新規登録フォーム";

	// エラー存在時にメッセージ表示
	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";

	// テンプレート読み込み
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(ADMIN_TEMP_MEMBER_FORM);

	// テンプレートに変数設定
	$INPUTS['MANAGERNAME'] = array('type'=>'text','name'=>'name','value'=>$_POST['name']);
	$INPUTS['MANAGERKANA'] = array('type'=>'text','name'=>'kana','value'=>$_POST['kana']);
	//$INPUTS['MANAGERID'] = array('type'=>'text','name'=>'manager_id','value'=>$_POST['manager_id']);  //del yamaguchi 2018/10/19
	$INPUTS['MANAGERID'] = array('result'=>'plane','value'=>$manager_id_input); //add yamaguchi 2018/10/19 IDは10文字以上入力させない
	$INPUTS['MANAGERPASS'] = array('type'=>'password','name'=>'manager_pass','value'=>$_POST['manager_pass']);
	$INPUTS['EMPLOYEECODE'] = array('type'=>'select','name'=>'kngn_lvl','array'=>$L_EMPLOYEE_LEVEL,'check'=>$_POST['kngn_lvl']);
	$INPUTS['MANAGEREMAIL'] = array('type'=>'text','name'=>'email','value'=>$_POST['email']);
	$INPUTS['CONTACT'] = array('type'=>'text','name'=>'tel','value'=>$_POST['tel']);
	$INPUTS['NEED'] = array('result'=>'plane','value'=>"<span style=\"font-size:80%;color:#ff0000;\">必須</span>");

	// パスワード入力領域生成
	$checkpass = "&nbsp;&nbsp;確認";
	$newform = new form_parts();
	$newform->set_form_type("password");
	$newform->set_form_name("manager_pass2");
	$newform->set_form_value($_POST['manager_pass2']);
	$checkpass .= $newform->make();
	$INPUTS['MANAGERPASS2'] = array('result'=>'plane','value'=>$checkpass);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"追加確認\">";
	$html .= "<input type=\"reset\" value=\"クリア\">";
	$html .= "</form>\n";
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"member_list\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}

/**
 * 入力内容チェック
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーがならば
 */
function check() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// 未入力チェック
	if (!$_POST['name']) { $ERROR[] = "名前が未入力です。"; }
	if (!$_POST['kana']) { $ERROR[] = "名前カナが未入力です。"; }
	if (!$_POST['manager_id']) {
		$ERROR[] = "管理者IDが未入力です。";
	// add yamaguchi 2018/10/19 IDを半角英数字に限定する。
	} elseif (!preg_match('/^[a-zA-Z0-9_-]+$/', $_POST['manager_id'])) {
		$ERROR[] = "管理者IDは半角英数字記号で入力してください。";
	// add yamaguchi  2018/10/19
	// upd yamaguchi 2018/10/18 IDの入力文字数制限を10文字に変更
	//} elseif (strlen($_POST['manager_id']) > 20) {
	//	$ERROR[] = "管理者IDは20文字以内にしてください。";
	} elseif (mb_strlen($_POST['manager_id']) > 10) {
		$ERROR[] = "管理者IDは10文字以内にしてください。";
	// upd yamaguchi  2018/10/18
	}

	// 存在チェック
	if ($_POST['manager_id'] && MODE == "addform") {
		$sql = "SELECT manager_id FROM ".T_MANAGER." WHERE manager_id='".$_POST['manager_id']."' LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$max = $cdb->num_rows($result);
		}
		if ($max) { $ERROR[] = "入力されたIDは既に利用されています。変更してください。"; }
	// add start yamaguchi 2018/10/18 詳細フォームのIDを変えると別のユーザーを更新したり、空振りしたりするため修正
	}elseif(MODE == "view"){
		//自分以外のIDを入力した場合エラーを表示
		if ( $_POST['manager_id'] != $_POST['origin_manager_id'] ) { 
			$ERROR[] = "管理者IDを変更することはできません。"; 
		}
	// add end yamaguchi 2018/10/18 
	}
	// 新規登録時チェック
	if (MODE == "addform") {
		if (!$_POST['manager_pass']) { $ERROR[] = "管理者パスワードが未入力です。"; }
		if (!$_POST['manager_pass2']) { $ERROR[] = "確認パスワードが未入力です。"; }
		if ($_POST['manager_pass'] && $_POST['manager_pass2']) {
			if ($_POST['manager_pass'] != $_POST['manager_pass2']) { $ERROR[] = "パスワードの入力が不正です。"; }
		}
	}

	return $ERROR;
}

/**
 * 詳細画面(修正画面)
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 */
function viewform($ERROR) {

	// グローバル変数定義
	global $L_MANAGER_LEVEL;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// 権限の情報を取得し、配列に格納
	$L_EMPLOYEE_LEVEL = get_employee_level_list();

	// 変数格納およびデコード処理
	$action = ACTION;
	if ($action) {
		// POSTから取得
		foreach ($_POST as $key => $val) {
			$$key = replace_decode($val);
		}
	} else {
		// DBから取得
		$sql = "SELECT manager_id,manager_pass,name,kana,email,tel,kngn_lvl FROM ".T_MANAGER.
		//	" WHERE manager_id='".$_POST['manager_id']."' AND mk_flg!='1' LIMIT 1;";
			" WHERE manager_id='".$_POST['manager_id']."' LIMIT 1;";

		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);

		//if (!$list) {
		//	$html .= "既に削除されているか、不正な情報が混ざっています。";
		//	return $html;
		//}
		foreach ($list as $key => $val) {
			$$key = replace_decode($val);
		}
		$manager_pass = "";
	}
	
	$manager_id_input = "<input type=\"text\" name=\"manager_id\" value=\"".$manager_id."\" maxlength=\"10\" />"; //add yamaguchi 2018/10/19 IDは10文字以上入力させない

	// html生成
	$html = "詳細画面";

	// エラー存在時にメッセージ表示
	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";
	$html .= "<input type=\"hidden\" name=\"manager_id\" value=\"".$_POST['manager_id']."\">\n";
	$html .= "<input type=\"hidden\" name=\"origin_manager_id\" value=\"".$manager_id."\">\n"; // add yamaguchi 2018/10/18  詳細フォームのIDを変えると別のユーザーを更新したり、空振りしたりするため修正 元のID番号を保持

	// テンプレート読み込み
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(ADMIN_TEMP_MEMBER_FORM);

	// テンプレートに変数設定
	$INPUTS['MANAGERNAME'] = array('type'=>'text','name'=>'name','value'=>$name);
	$INPUTS['MANAGERKANA'] = array('type'=>'text','name'=>'kana','value'=>$kana);
	//$INPUTS['MANAGERID'] = array('type'=>'text','name'=>'manager_id','value'=>$manager_id); //add yamaguchi 2018/10/19
	$INPUTS['MANAGERID'] = array('result'=>'plane','value'=>$manager_id_input); //add yamaguchi 2018/10/19 IDは10文字以上入力させない
	$INPUTS['MANAGERPASS'] = array('type'=>'password','name'=>'manager_pass','value'=>$manager_pass);
	$INPUTS['EMPLOYEECODE'] = array('type'=>'select','name'=>'kngn_lvl','array'=>$L_EMPLOYEE_LEVEL,'check'=>$kngn_lvl);
	$INPUTS['MANAGEREMAIL'] = array('type'=>'text','name'=>'email','value'=>$email);
	$INPUTS['CONTACT'] = array('type'=>'text','name'=>'tel','value'=>$tel);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();

	// ボタン表示
	$html .= "<input type=\"submit\" value=\"変更確認\">";
	$html .= "<input type=\"reset\" value=\"クリア\">";
	$html .= "</form>\n";
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"member_list\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}

/**
 * 詳細画面(確認画面)
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function check_html() {

	// グローバル変数定義
	global $L_MANAGER_LEVEL;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// 権限の情報を取得し、配列に格納
	$L_EMPLOYEE_LEVEL = get_employee_level_list();

	// 変数格納およびデコード処理
	$action = ACTION;
	if ($action) {
		// POSTから取得
		if ($_POST) {
			foreach ($_POST as $key => $val) {
				if ($key == "action") {
					if (MODE == "addform") {
						$val = "add";
					} elseif (MODE == "view") {
						$val = "change";
					}
				}
				$HIDDEN .= "<input type=\"hidden\" name=\"$key\" value=\"$val\">\n";
				$val = replace_decode($val);
				$$key = $val;
			}
		}
	} else {
		// DBから取得
		$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"change\">\n";
		$HIDDEN .= "<input type=\"hidden\" name=\"manager_id\" value=\"".$_POST['manager_id']."\">\n";

		$sql = "SELECT * FROM ".T_MANAGER.
		//	" WHERE manager_id='".$_POST['manager_id']."' AND mk_flg='0' LIMIT 1;";
			" WHERE manager_id='".$_POST['manager_id']."' LIMIT 1;";

		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);
		//if (!$list) {
		//	$html .= "既に削除されているか、不正な情報が混ざっています。";
		//	return $html;
		//}
		foreach ($list as $key => $val) {
			$$key = replace_decode($val);
		}
		$manager_pass = "";
	}

	// メッセージ編集
	if (MODE != "del") { $button = "登録"; } else { $button = "削除"; }
	$html = "確認画面：以下の内容で{$button}してもよろしければ{$button}ボタンをクリックしてください。";

	// テンプレート読み込み
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(ADMIN_TEMP_MEMBER_FORM);

	// テンプレートに変数設定
	$INPUTS['MANAGERNAME'] = array('result'=>'plane','value'=>$name);
	$INPUTS['MANAGERKANA'] = array('result'=>'plane','value'=>$kana);
	$INPUTS['MANAGERID'] = array('result'=>'plane','value'=>$manager_id);
	$INPUTS['MANAGERPASS'] = array('result'=>'plane','value'=>$manager_pass);
	$INPUTS['EMPLOYEECODE'] = array('result'=>'plane','value'=>$L_EMPLOYEE_LEVEL[$kngn_lvl]);
	$INPUTS['MANAGEREMAIL'] = array('result'=>'plane','value'=>$email);
	$INPUTS['CONTACT'] = array('result'=>'plane','value'=>$tel);
	$INPUTS['REMARKS'] = array('result'=>'plane','value'=>$remarks);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();

	// ボタン領域定義
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" style=\"float:left\">\n";
	$html .= $HIDDEN;
	$html .= "<input type=\"submit\" value=\"$button\">\n";
	$html .= "</form>";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";

	if ($action) {
		$HIDDEN2 = explode("\n",$HIDDEN);
		foreach ($HIDDEN2 as $key => $val) {
			if (ereg("name=\"action\"",$val)) {
				$HIDDEN2[$key] = "<input type=\"hidden\" name=\"action\" value=\"back\">";
				break;
			}
		}
		$HIDDEN2 = implode("\n",$HIDDEN2);

		$html .= $HIDDEN2;
	} else {
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"member_list\">\n";
	}
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}

/**
 * DB追加
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーがあれば
 */
function add() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// 画面項目を配列に追加
	foreach ($_POST as $key => $val) {
		if ($key == "action") { continue; }
		elseif ($key == "authority") { continue; }
		elseif ($key == "manager_pass2") { continue; }
		$INSERT_DATA[$key] = "$val";
	}

	// 管理情報を設定
	$INSERT_DATA['ins_tts_id'] = $_SESSION['myid']['id'];
	$INSERT_DATA['ins_date'] = "now()";

	// INSERT処理
	$ERROR = $cdb->insert(T_MANAGER,$INSERT_DATA);

	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}

/**
 * DB更新（変更／論理削除）
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーがならば
 */
function change() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// 変更処理
	if (MODE == "view") {

		// 画面項目を配列に追加
		foreach ($_POST as $key => $val) {
			if ($key == "action") { continue; }
			elseif ($key == "manager_id") { continue; }
			elseif ($key == "origin_manager_id") { continue; } // add yamaguchi 2018/10/18  詳細フォームのIDを変えると別のユーザーを更新したり、空振りしたりするため修正
			elseif ($key == "manager_pass2") { continue; }
			if ($key == "manager_pass" && $val == "") { continue; }
			$INSERT_DATA[$key] = "$val";
		}

		// 管理情報を設定(論理削除をクリア)
		$INSERT_DATA['mk_flg'] = "0";
		$INSERT_DATA['mk_tts_id'] = "";
		$INSERT_DATA['mk_date'] = "0000-00-00 00:00:00";
		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
		$INSERT_DATA['update_date'] = "now()";

		// 更新対象データ条件
		$where = " WHERE manager_id='".$_POST['manager_id']."' LIMIT 1;";

		// UPDATE処理
		$ERROR = $cdb->update(T_MANAGER,$INSERT_DATA,$where);
	// 削除処理
	} elseif (MODE == "del") {

		// 論理削除の値を設定
		$INSERT_DATA['mk_flg'] = "1";
		$INSERT_DATA['mk_tts_id'] = $_SESSION['myid']['id'];
		$INSERT_DATA['mk_date'] = "now()";

		// 更新対象データ条件
		$where = " WHERE manager_id='".$_POST['manager_id']."' LIMIT 1;";

		// UPDATE処理
		$ERROR = $cdb->update(T_MANAGER,$INSERT_DATA,$where);
	}

	if (!$ERROR) { $_SESSION['select_menu'] = MAIN . "<>" . SUB . "<><><>"; }

	return $ERROR;
}

/**
 * 権限レベル取得ロジック
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array
 */
function get_employee_level_list() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// リターン値初期化
	$L_EMPLOYEE_LEVEL = array();

	// デフォルト値設定
	$L_EMPLOYEE_LEVEL[0] = "--------";

	// マスタ情報取得
	$sql = "SELECT " .
			"  employee.kngn_lvl, " .
			"  employee.employee_name " .
			" FROM " . T_EMPLOYEE . " employee " .
			" WHERE employee.state='0'" .
			" ORDER by employee.list_num";
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$L_EMPLOYEE_LEVEL[$list['kngn_lvl']] = $list['employee_name'];
		}
	}

	// 配列リターン
	return $L_EMPLOYEE_LEVEL;

}
?>