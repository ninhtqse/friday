<?php
/**
 * ベンチャー・リンク　すらら
 *
 * テスト用プラクティス管理　テストカテゴリマッピング
 *
 * 履歴
 * 2020/12/16 初期設定
 *
 * @author Azet
 */


/**
 * HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @return string HTML
 */
function start() {

	$html = make_select_list();//選択プルダウン作成

	$ERROR = [];
	if(ACTION == "group_check"){
		$ERROR = group_check();//登録項目のチェック
	}
	if (!$ERROR) {
		if (ACTION == "group_add") { $ERROR = group_add(); }//新規登録
		elseif (ACTION == "group_change") { $ERROR = group_change(); }//変更・削除
		elseif (ACTION == "view_session") { $ERROR = view_session(); }//セッション情報操作
	}

	if (MODE == "group_del"){//削除
		if (ACTION == "group_change") {
			if (!$ERROR) { $html .= standard_category_detail_list($ERROR); }
			else { $html .= group_check_html($ERROR); }
		}else{
			$html .= group_check_html($ERROR);
		}
	} elseif (MODE == "group_detail"){//詳細
		if(ACTION == "group_check"){
			if(!$ERROR){ $html .= group_check_html();}
			else { $html .= group_viewform($ERROR);}
		} elseif (ACTION == "group_change") {
			if (!$ERROR) { $html .= standard_category_detail_list($ERROR); }
			else { $html .= group_viewform($ERROR); }
		}else{
			$html .= group_viewform($ERROR);
		}
	} elseif (MODE == "group_add"){//新規
		if(ACTION == "group_check"){
			if(!$ERROR){ $html .= group_check_html();}
			else { $html .= group_addform($ERROR);}
		} elseif (ACTION == "group_add") {
			if (!$ERROR) { $html .= standard_category_detail_list($ERROR); }
			else { $html .= group_addform($ERROR); }
		}else{
			$html .= group_addform($ERROR);
		}
	} else {
		$html .= standard_category_detail_list($ERROR);
	}

	return $html;
}

/**
 * カテゴリ選択プルダウンを作成
 *
 * @author Azet
 * @return html
 */
function make_select_list(){
	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql  = "SELECT * FROM " . T_MS_TEST_STANDARD_CATEGORY . " WHERE mk_flg='0' ";
	$category_list[''] = "選択してください。";
	if($result = $cdb->query($sql)){
		while($list = $cdb->fetch_assoc($result)){
			$category_list[$list['category_id']] = $list['category_name'];
		}
	}
	
	//selectメニュー
	$make_html = new form_parts();
	$make_html->set_form_type("select");
	$make_html->set_form_name("category_id");
	$make_html->set_form_array($category_list);
	$make_html->set_form_check($_POST['category_id']);
	$make_html->set_form_action("onchange=\"document.select_view_menu.submit();\"");
	$select_html = $make_html->make();

	//html作成
	$html = "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" name=\"select_view_menu\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"set_problem_view\">";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"view_session\">";
	$html .= "<table class=\"unit_form\">";
	$html .= "<tr class=\"unit_form_menu\"><th>標準化カテゴリ</th></tr>";
	$html .= "<tr class=\"unit_form_cell\"><td>".$select_html."</td></tr>";
	$html .= "</table>";
	$html .= "</form>";

	return $html;
}


/**
 * 標準化カテゴリ管理詳細情報一覧
 *
 * @author Azet
 * @return html
 */
function standard_category_detail_list() {

	//-------------------------------------------------
	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if(!$_POST['category_id']){
		$html .= "<br>\n";
		$html .= "標準化カテゴリを選択してください<br>\n";
		return $html;
	}


	$html = "<br>\n";
	if($_POST['category_id']){
		if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__add",$_SESSION['authority'])===FALSE)) {
			$html .= "<br>\n";
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"group_add\">\n";
			$html .= "<input type=\"hidden\" name=\"category_id\" value=\"".$_POST['category_id']."\">\n";
			$html .= "<input type=\"submit\" value=\"テストグループ関連登録\">\n";
			$html .= "</form>\n";
		}
	}

	//定期テスト
	if($_SESSION['t_practice']['category_type'] == 1){
		$sql = get_book_category_data_sql($_POST['category_id']);
	}else{
		$sql = get_category_data_sql($_POST['category_id']);
	}

	if ($result = $cdb->query($sql)) {

		$max = $cdb->num_rows($result);

		if(!$max){
			$html .= "<br>\n";
			$html .= "今現在登録されているテストグループは有りません。<br>\n";
			return $html;
		}

		$html .= "<br>\n";
		$html .= "修正する場合は、修正するテストグループの詳細ボタンを押してください。<br>\n";
		$html .= "<table class=\"course_form\">\n";
		$html .= "<tr class=\"course_form_menu\">\n";
		$html .= "<th>管理番号</th>\n";
		//定期テスト
		if($_SESSION['t_practice']['category_type'] == 1){
			$html .= "<th>出版社</th>\n";
			$html .= "<th>教科書ID</th>\n";
			$html .= "<th>教科書名</th>\n";
			$html .= "<th>サービスコード</th>\n";
			$html .= "<th>詳細コースコード</th>\n";

		}else{
			$html .= "<th>テストグループ</th>\n";
			$html .= "<th>合否判定ライン(％)</th>\n";
		}
		if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__view",$_SESSION['authority'])===FALSE)) {
			$html .= "<th>詳細</th>\n";
		}
		if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__del",$_SESSION['authority'])===FALSE)) {
			$html .= "<th>削除</th>\n";
		}
		$html .= "</tr>\n";
		$i = 1;
		while ($list = $cdb->fetch_assoc($result)) {

			$html .= "<tr class=\"course_form_cell\">\n";
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"group_detail\">\n";
			$html .= "<input type=\"hidden\" name=\"action\" value=\"\">\n";
			$html .= "<input type=\"hidden\" name=\"category_id\" value=\"".$list['category_id']."\">\n";
			$html .= "<input type=\"hidden\" name=\"id\" value=\"".$list['id']."\">\n";
			$html .= "<td>".$list['id']."</td>\n";
			//定期テスト
			if($_SESSION['t_practice']['category_type'] == 1){
				$html .= "<td>".$list['publishing_id']." ".$list['publishing_name']."</td>\n";
				$html .= "<td>".$list['book_id']."</td>\n";
				$html .= "<td>".$list['book_name']."</td>\n";
				$html .= "<td>".$list['srvc_cd']."</td>\n";
				$html .= "<td>".$list['sysi_crs_cd']."</td>\n";
			}else{
				$html .= "<td>".$list['test_group_id']." ".$list['test_group_name']."</td>\n";
				$html .= "<td>".$list['pass_line']."</td>\n";
			}
			if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__view",$_SESSION['authority'])===FALSE)) {
				$html .= "<td><input type=\"submit\" value=\"詳細\"></td>\n";
			}
			$html .= "</form>\n";
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"group_del\">\n";
			$html .= "<input type=\"hidden\" name=\"id\" value=\"".$list['id']."\">\n";
			$html .= "<input type=\"hidden\" name=\"category_id\" value=\"".$list['category_id']."\">\n";
			$html .= "<input type=\"hidden\" name=\"action\" value=\"\">\n";
			if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__del",$_SESSION['authority'])===FALSE)) {
				$html .= "<td><input type=\"submit\" value=\"削除\"></td>\n";
			}
			$html .= "</form>\n";
			$html .= "</tr>\n";
			++$i;
		}
		$html .= "</table>\n";
	}
	return $html;
}


/**
 * 新規登録フォーム(詳細情報)
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function group_addform($ERROR) {

	$html = "<br>\n";
	$html .= "テストグループ関連 新規登録フォーム<br>\n";

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"group_check\">\n";
	$html .= "<input type=\"hidden\" name=\"category_id\" value=\"".$_POST['category_id']."\">\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(TEST_STANDARD_CATEGORY_DETAIL_FORM);
	$category_info = get_category_info($_POST['category_id']);
	$category_type = $_SESSION['t_practice']['category_type'];

	$INPUTS['ID'] 	= array('result'=>'plane','value'=>"---");//ID
	//定期テスト
	if($_SESSION['t_practice']['category_type'] == 1){
		$INPUTS['TESTGROUPID'] 	= array('type'=>'text','name'=>'book_id','size'=>'50','value'=>$_POST['book_id']);
	}else{
		$INPUTS['TESTGROUPID'] 	= array('type'=>'text','name'=>'test_group_id','size'=>'50','value'=>$_POST['test_group_id']);
	}
	$INPUTS['SRVCCD'] 	= array('type'=>'text','name'=>'srvc_cd','size'=>'50','value'=>$_POST['srvc_cd']);
	$INPUTS['SYSICRSCD'] = array('type'=>'text','name'=>'sysi_crs_cd','size'=>'50','value'=>$_POST['sysi_crs_cd']);
	$INPUTS['PASSLINE'] 	= array('type'=>'text','name'=>'pass_line','size'=>'50','value'=>$_POST['pass_line']);
	//定期テストまたは、カテゴリ設定の合否設定が利用しないの時は編集不可
	if(can_use_pass_fail($category_info) == 0 || $category_type == 1){
		$INPUTS['PASSLINE'] += read_only_array();
	}
	//定期テスト
	if($category_type != 1){
		$INPUTS['SRVCCD'] += read_only_array();
		$INPUTS['SYSICRSCD'] += read_only_array();
	}


	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();

	$html .= "<input type=\"submit\" value=\"追加確認\">\n";
	$html .= "<input type=\"reset\" value=\"クリア\">\n";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"back\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"group_back\">\n";
	$html .= "<input type=\"hidden\" name=\"category_id\" value=\"".$_POST['category_id']."\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";
	return $html;
}



/**
 * 詳細画面(詳細情報)
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function group_viewform($ERROR) {

	global $L_TEST_TYPE_LIST;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (ACTION) {
		foreach ($_POST as $key => $val) { $$key = $val; }
	} else {
		$sql = "SELECT * FROM ". T_TEST_STANDARD_CATEGORY_RELATION . " WHERE id='".$_POST['id']."' AND mk_flg='0' LIMIT 1;";
		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);
		if (empty($list)) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}
		foreach ($list as $key => $val) {
			$$key = replace_decode($val);
		}
	}

	$html = "<br>\n";
	$html .= "テストグループ関連 詳細画面<br>\n";

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}


	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"group_check\">\n";
	$html .= "<input type=\"hidden\" name=\"category_id\" value=\"".$category_id."\">\n";
	$html .= "<input type=\"hidden\" name=\"id\" value=\"".$id."\">\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(TEST_STANDARD_CATEGORY_DETAIL_FORM);

	if (!$id) { $id = "---"; }

	$test_type_html = "<select name=\"menu_cd\">";
	$test_type_html .= "<option value=\"0\">選択して下さい</option>\n";
	foreach ($L_TEST_TYPE_LIST as $key => $val) {
		if ($menu_cd == $key) { $selected = "selected"; } else { $selected = ""; }
		$test_type_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
	}
	$test_type_html .= "</select>\n";
	$category_info = get_category_info($category_id);
	$category_type = $_SESSION['t_practice']['category_type'];

	$INPUTS['ID'] 	= array('result'=>'plane','value'=>$id);//ID
	//定期テスト
	if($category_type == 1){
		$INPUTS['TESTGROUPID'] 	= array('type'=>'text','name'=>'book_id','size'=>'50','value'=>$book_id);
	}else{
		$INPUTS['TESTGROUPID'] 	= array('type'=>'text','name'=>'test_group_id','size'=>'50','value'=>$test_group_id);
	}
	$INPUTS['SRVCCD'] 	= array('type'=>'text','name'=>'srvc_cd','size'=>'50','value'=>$srvc_cd);
	$INPUTS['SYSICRSCD'] 	= array('type'=>'text','name'=>'sysi_crs_cd','size'=>'50','value'=>$sysi_crs_cd);
	$INPUTS['TESTTYPE'] 	= array('result'=>'plane','value'=>$test_type_html);
	$INPUTS['PASSLINE'] 	= array('type'=>'text','name'=>'pass_line','size'=>'50','value'=>$pass_line);
	//定期テストまたは、カテゴリ設定の合否設定を利用しないにしている時、編集不可
	if(can_use_pass_fail($category_info) == 0 || $category_type == 1){
		$INPUTS['PASSLINE'] += read_only_array();
	}
	//定期テスト
	if($category_type != 1){
		$INPUTS['SRVCCD'] += read_only_array();
		$INPUTS['SYSICRSCD'] += read_only_array();
	}
	
	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();


	$html .= "<input type=\"submit\" value=\"変更確認\">";
	$html .= "<input type=\"reset\" value=\"クリア\">";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"back\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"group_back\">\n";
	$html .= "<input type=\"hidden\" name=\"category_id\" value=\"".$category_id."\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";



	return $html;
}


/**
 * 確認する機能(詳細情報)
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @return array エラーの場合
 */
function group_check() {
	$ERROR = [];

	$category_info = get_category_info($_POST['category_id']);
	$category_type = $_SESSION['t_practice']['category_type'];
	//定期テスト
	if($category_type == 1){
		if($_POST['book_id']==""){
			$ERROR[] = "テストグループコードが記載されておりません。";
		}else{
			$check_result = check_group_id($_POST['book_id'],$category_type,$_POST['id']);
			if($check_result){ $ERROR[] = $check_result; }
			$check_result = check_book_id($_POST['book_id']);
			if($check_result){ $ERROR[] = $check_result; }
		}
	}else{
		if($_POST['test_group_id']==""){
			$ERROR[] = "テストグループコードが記載されておりません。";
		}elseif(!preg_match("/^[0-9]+$/", $_POST['test_group_id'])){
			$ERROR[] = "テストグループコードの値は半角数字以外を記入しないで下さい。";
		}else{
			$check_result = check_group_id($_POST['test_group_id'],$category_type,$_POST['id']);
			if($check_result){ $ERROR[] = $check_result; }
			$check_result = check_ms_test_group_id($_POST['test_group_id'],$category_type);
			if($check_result){ $ERROR[] = $check_result; }
		}
	}

	//カテゴリの合否判定が利用するだったら
	if(can_use_pass_fail($category_info) == 1){
		if ($_POST['pass_line'] == "") {
			$ERROR[] = "合否判定ライン(％)が記載されておりません。";
		}elseif(!preg_match("/^[0-9]+$/", $_POST['pass_line'])){
			$ERROR[] = "合否判定ライン(％)の値は半角数字以外を記入しないで下さい。";
		}elseif($_POST['pass_line']<0 || $_POST['pass_line']>100){
			$ERROR[] = "合否判定ライン(％)の値は0~100の間で指定してください。";
		}
	}
	//定期テスト
	if($category_type == 1){
		if(empty($_POST['srvc_cd'])){
			$ERROR[] = "サービスコードが記載されておりません。";
		}
		if(empty($_POST['sysi_crs_cd'])){
			$ERROR[] = "詳細コースCDが記載されておりません。";
		}
	}

	return $ERROR;
}



/**
 * 確認フォームを作成する機能(詳細情報)
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @return string HTMLフォーム
 */
function group_check_html() {

	global $L_TEST_TYPE_LIST;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if ($_POST) {
		foreach ($_POST as $key => $val) {
			if ($key == "action") {
				if (MODE == "group_add") { $val = "group_add"; }
				elseif (MODE == "group_detail" || MODE == "group_del") { $val = "group_change"; }
			}
			$HIDDEN .= "<input type=\"hidden\" name=\"".$key."\" value=\"".$val."\">\n";
		}
	}

	if (ACTION) {
		foreach ($_POST as $key => $val) { $$key = $val; }
	} else {
		$sql = "SELECT * FROM " . T_TEST_STANDARD_CATEGORY_RELATION . " WHERE id='".$_POST['id']."' AND mk_flg='0' LIMIT 1;";
		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);
		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}
		foreach ($list as $key => $val) {
			$$key = replace_decode($val);
		}

	}

	if (MODE != "group_del") { $button = "登録"; } else { $button = "削除"; }
	$html = "<br>\n";
	$html .= "確認画面：以下の内容で".$button."してもよろしければ".$button."ボタンをクリックしてください。<br>\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(TEST_STANDARD_CATEGORY_DETAIL_FORM);

//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	if (!$id) { $id = "---"; }
	$INPUTS['ID'] 	= array('result'=>'plane','value'=>$id);//カテゴリID
	//定期テスト
	if($_SESSION['t_practice']['category_type'] == 1){
		$INPUTS['TESTGROUPID'] 	= array('result'=>'plane','value'=>$book_id);//カテゴリID
	}else{
		$INPUTS['TESTGROUPID'] 	= array('result'=>'plane','value'=>$test_group_id);//カテゴリID
	}
	$INPUTS['SRVCCD'] 	= array('result'=>'plane','value'=>$srvc_cd);//カテゴリID
	$INPUTS['SYSICRSCD'] 	= array('result'=>'plane','value'=>$sysi_crs_cd);//カテゴリID
	$INPUTS['TESTTYPE'] 	= array('result'=>'plane','value'=>$L_TEST_TYPE_LIST[$menu_cd]);//カテゴリ
	$INPUTS['PASSLINE'] 	= array('result'=>'plane','value'=>$pass_line);//合格ライン

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" style=\"float:left\">\n";
	$html .= $HIDDEN;
	$html .= "<input type=\"submit\" value=\"".$button."\">\n";
	$html .= "</form>";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";

	if (ACTION) {
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
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"back\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"group_back\">\n";
		$html .= "<input type=\"hidden\" name=\"category_id\" value=\"".$category_id."\">\n";
	}
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}



/**
 * DB新規登録(詳細情報)
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @return array エラーの場合
 */
function group_add() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
	$INSERT_DATA = reference_db_column($_SESSION['t_practice']['category_type']);

	$INSERT_DATA['category_id'] 	= $_POST['category_id'];

	$INSERT_DATA['ins_tts_id'] 		= $_SESSION['myid']['id'];
	$INSERT_DATA['ins_date'] 			= "now()";
	$INSERT_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];
	$INSERT_DATA['upd_date'] 			= "now()";

	$ERROR = $cdb->insert(T_TEST_STANDARD_CATEGORY_RELATION,$INSERT_DATA);


	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}

/**
 * DB更新・削除 処理(詳細情報)
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @return array エラーの場合
 */
function group_change() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (MODE == "group_detail") {
		$INSERT_DATA = reference_db_column($_SESSION['t_practice']['category_type']);
		$INSERT_DATA['category_id'] 	= $_POST['category_id'];
		$INSERT_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date'] 			= "now()";
	} elseif (MODE == "group_del") {
		$INSERT_DATA['mk_flg'] 			= 1;
		$INSERT_DATA['mk_tts_id'] 		= $_SESSION['myid']['id'];
		$INSERT_DATA['mk_date'] 			= "now()";
	}
	$where = " WHERE id='".$_POST['id']."' LIMIT 1;";

	$ERROR = $cdb->update(T_TEST_STANDARD_CATEGORY_RELATION,$INSERT_DATA,$where);


	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}


/**
 * テストグループIDまたは教科書IDがすでに登録済でないか確認する
 *
 * @author Azet
 * @param int $test_group_id_　テストグループIDまたは教科書ID
 * @param int $category_type_　カテゴリタイプ
 * @param int $id_　登録予定ID
 * @return string
 */
function check_group_id($test_group_id_,$category_type_,$id_){
	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if($category_type_ == 1){
		$where = " AND tr.book_id='".$test_group_id_."'";
	}else{
		$where = " AND tr.test_group_id='".$test_group_id_."'";
	}

	$sql  = " SELECT ";
	$sql .= " * ";
	$sql .= " FROM " . T_TEST_STANDARD_CATEGORY_RELATION." tr";
	$sql .= " INNER JOIN " . T_MS_TEST_STANDARD_CATEGORY." tc ON tr.category_id = tc.category_id ";
	$sql .= " WHERE 1";
	$sql .= $where;
	$sql .= " AND tc.category_type='".$category_type_."' ";
	$sql .= " AND tc.mk_flg='0' ";
	$sql .= " AND tr.mk_flg='0' LIMIT 1;";
	// print $sql;
	$return_ = "";
	if ($result = $cdb->query($sql)){
		$list = $cdb->fetch_assoc($result);
		if(!empty($list['id']) && $list['id'] != $id_){
			$return_ = "テストグループコードの値はすでに「".$list['category_id'].":".$list['category_name']."」カテゴリで登録済です。";
		}
	}

	return $return_;
}

/**
 * テストグループIDが実在する値か確認する
 *
 * @author Azet
 * @param int $test_group_id_ テストグループID
 * @param int $category_type_　カテゴリタイプ
 * @return string
 */
function check_ms_test_group_id($test_group_id_,$category_type_){
	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$where = "";
	if($category_type_ == 4){
		$where = " AND tg.class_id = '' ";
	}elseif($category_type_ == 5){
		$where = " AND tg.class_id > '' ";
	}

	$sql  = " SELECT ";
	$sql .= " tg.test_group_id ";
	$sql .= " FROM ";
	$sql .= T_MS_BOOK_GROUP." tg ";
	$sql .= " WHERE tg.test_group_id = '".$test_group_id_."'";
	$sql .= " AND tg.mk_flg='0'";
	$sql .= $where;
	$sql .= " ; ";

	// print $sql."<br>";

	$return_ = "";
	if ($result = $cdb->query($sql)) {
		$count = $cdb->num_rows($result);
		if($count==0){
			$return_ = "テストグループコードの値は存在しません。";
		}
	}
	return $return_;
}

/**
 * 教科書IDが実在する値か確認する
 *
 * @author Azet
 * @param int $book_id_　教科書ID
 * @return string
 */
function check_book_id($book_id_){
	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql  = " SELECT ";
	$sql .= " b.book_id ";
	$sql .= " FROM ";
	$sql .= T_MS_BOOK." b ";
	$sql .= " WHERE b.book_id = '".$book_id_."'";
	$sql .= " AND b.mk_flg='0'";
	$sql .= " ; ";

	// print $sql."<br>";

	$return_ = "";
	if ($result = $cdb->query($sql)) {
		$count = $cdb->num_rows($result);
		if($count==0){
			$return_ = "テストグループコードの値は存在しません。";
		}
	}
	return $return_;
}

/**
 * インサート振り分け用
 * カテゴリタイプによってinsert項目に入る情報が変わる
 *
 * @author Azet
 * @param int $category_type_ カテゴリタイプ
 * @return array
 */
function reference_db_column($category_type_){

	$insert_column = reference_post_name($category_type_);

	$INSERT_DATA = array();
	foreach($insert_column as $name){
		if(isset($_POST[$name])){
			$INSERT_DATA[$name] = $_POST[$name];
		}
	}

	return $INSERT_DATA;
}
/**
 * テストの種類によって、インサートカラム情報が異なるため、
 * インサート項目の配列を取得
 *
 * @author Azet
 * @param int $category_type_ テストタイプ
 * @return array
 */
function reference_post_name($category_type_){
	if($category_type_ == 1){
		$name_array = [
			'book_id',
			'srvc_cd',
			'sysi_crs_cd',
		];
	}else{
		$name_array = [
			'test_group_id',
			'pass_line',
		];
	}
	return $name_array;

}

/**
 * カテゴリ詳細と紐づくテストグループ情報をだすSQLを取得
 *
 * @author Azet
 * @param int $category_id_
 * @return string SQL
 */
function get_category_data_sql($category_id_){

	$sql  = " SELECT ";
	$sql .= " sr.* ";
	$sql .= " ,tg.test_group_name ";
	$sql .= " FROM ". T_TEST_STANDARD_CATEGORY_RELATION . " sr ";
	$sql .= " INNER JOIN ". T_MS_BOOK_GROUP . " tg ON sr.test_group_id = tg.test_group_id";
	$sql .= " WHERE sr.mk_flg='0'";
	$sql .= " AND tg.mk_flg='0'";
	$sql .= " AND sr.category_id = '".$category_id_."'";

	// print $sql;

	return $sql;
}

/**
 * カテゴリ詳細と紐づく教科書情報をだすSQLを取得
 *
 * @author Azet
 * @param int $category_id_
 * @return string SQL
 */
function get_book_category_data_sql($category_id_){

	$sql  = " SELECT ";
	$sql .= " sr.* ";
	$sql .= " ,b.book_id ";
	$sql .= " ,b.book_name ";
	$sql .= " ,p.publishing_id ";
	$sql .= " ,p.publishing_name ";
	$sql .= " FROM ". T_TEST_STANDARD_CATEGORY_RELATION . " sr ";
	$sql .= " INNER JOIN ". T_MS_BOOK . " b ON sr.book_id = b.book_id";
	$sql .= " INNER JOIN ". T_MS_PUBLISHING. " p ON b.publishing_id = p.publishing_id";
	$sql .= " WHERE sr.mk_flg='0'";
	$sql .= " AND b.mk_flg='0'";
	$sql .= " AND p.mk_flg='0'";
	$sql .= " AND sr.category_id = '".$category_id_."'";

	// print $sql;

	return $sql;
}

/**
 * 該当のカテゴリの合否判定の登録状態を取得
 *
 * @author Azet
 * @param array $list_ カテゴリ情報
 * @return string 1|0
 */
function can_use_pass_fail($list_){
	
	$category_type = $list_['category_type'];
	$return_data = 0;
	if(!empty($category_type)){
		if($category_type != 1){
			$return_data = $list_['option_flg5'];
		}
	}
	return $return_data;
}

/**
 * 該当のカテゴリ情報を取得
 *
 * @author Azet
 * @param int $category_id_
 * @return array
 */
function get_category_info($category_id_){
	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql  = " SELECT ";
	$sql .= " * ";
	$sql .= " FROM ". T_MS_TEST_STANDARD_CATEGORY." tc ";
	$sql .= " WHERE tc.category_id='".$category_id_."'";
	$sql .= " AND tc.mk_flg='0'";
	$sql .= " LIMIT 1;";

	// print $sql;

	$list = [];
	if($result = $cdb->query($sql)){
		$list = $cdb->fetch_assoc($result);
	}
	return $list;
}

/**
 * disabledに似せたCSSを作成する用の配列を取得
 *
 * @author Azet
 * @return array
 */
function read_only_array(){
	return array(
		'action' => 'readonly',
		'style' => 'background-color:#eee;',
	);
}

/**
 * session情報を操作
 *
 * @author Azet
 */
function view_session() {

	// DB接続オブジェクト

	if(empty($_POST['category_id'])){
		unset($_SESSION['t_practice']);
	}if ($_SESSION['t_practice']['category_id'] != $_POST['category_id']) {
		unset($_SESSION['t_practice']);
		$category_info = get_category_info($_POST['category_id']);
		$_SESSION['t_practice']['category_type'] = $category_info['category_type'];
	}
	
}