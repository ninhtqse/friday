<?php
/**
 * ベンチャー・リンク　すらら
 *
 * テスト用プラクティス管理　標準化カテゴリ管理
 *
 * 履歴
 * 2020/08/24 初期設定
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
	$html = "";

	$ERROR = [];
	if (ACTION == "check") {
		$ERROR = check();
	// del start hirose 2020/12/16 テスト標準化開発 定期テスト
	// }elseif(ACTION == "group_check"){
	// 	$ERROR = group_check();
	// del end hirose 2020/12/16 テスト標準化開発 定期テスト
	}
	if (!$ERROR) {
		if (ACTION == "add") { $ERROR = add(); }
		elseif (ACTION == "change") { $ERROR = change(); }
		elseif (ACTION == "category_change") { $ERROR = category_change(); }// add hirose 2020/12/18 テスト標準化開発 定期テスト
		// del start hirose 2020/12/16 テスト標準化開発 定期テスト
		// elseif (ACTION == "group_add") { $ERROR = group_add(); }
		// elseif (ACTION == "group_change") { $ERROR = group_change(); }
		// del end hirose 2020/12/16 テスト標準化開発 定期テスト
	}

	if (MODE == "add") {
		if (ACTION == "check") {
			if (!$ERROR && !$_POST['first_view']) { $html .= check_html(); }
			else { $html .= addform($ERROR); }
		} elseif (ACTION == "add") {
			if (!$ERROR) { $html .= standard_category_list($ERROR); }
			else { $html .= addform($ERROR); }
		} else {
			$html .= addform($ERROR);
		}
	} elseif (MODE == "詳細") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= viewform($ERROR); }
		} elseif (ACTION == "change") {
			if (!$ERROR) { $html .= standard_category_list($ERROR); }
			else { $html .= viewform($ERROR); }
		} else {
			$html .= viewform($ERROR);
		}
	} elseif (MODE == "削除") {
		if (ACTION == "change") {
			if (!$ERROR) { $html .= standard_category_list($ERROR); }
			else { $html .= viewform($ERROR); }
		} else {
			$html .= check_html();
		}
	// del start hirose 2020/12/16 テスト標準化開発 定期テスト
	// } elseif (MODE == "group_del"){
	// 	if (ACTION == "group_change") {
	// 		if (!$ERROR) { $html .= viewform($ERROR); }
	// 		else { $html .= group_check_html($ERROR); }
	// 	}else{
	// 		$html .= group_check_html($ERROR);
	// 	}
	// } elseif (MODE == "group_detail"){
	// 	if(ACTION == "group_check"){
	// 		if(!$ERROR){ $html .= group_check_html();}
	// 		else { $html .= group_viewform($ERROR);}
	// 	} elseif (ACTION == "group_change") {
	// 		if (!$ERROR) { $html .= viewform($ERROR); }
	// 		else { $html .= group_viewform($ERROR); }
	// 	}else{
	// 		$html .= group_viewform($ERROR);
	// 	}
	// } elseif (MODE == "group_add"){
	// 	if(ACTION == "group_check"){
	// 		if(!$ERROR){ $html .= group_check_html();}
	// 		else { $html .= group_addform($ERROR);}
	// 	} elseif (ACTION == "group_add") {
	// 		if (!$ERROR) { $html .= viewform($ERROR); }
	// 		else { $html .= group_addform($ERROR); }
	// 	}else{
	// 		$html .= group_addform($ERROR);
	// 	}
	// } elseif (MODE == "back"){
	// 	if(ACTION == "group_back"){
	// 		$html .= viewform($ERROR);
	// 	}else{
	// 		$html .= standard_category_list($ERROR);
	// 	}
	// del end hirose 2020/12/16 テスト標準化開発 定期テスト
	} else {
		$html .= standard_category_list($ERROR);
	}

	return $html;
}


/**
 * 標準化カテゴリ管理一覧のHTMLを作成
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function standard_category_list($ERROR) {

	global $L_TEST_TYPE_LIST;
	//-------------------------------------------------
	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= "<br>\n";
	if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__add",$_SESSION['authority'])===FALSE)) {
		$html .= "<br>\n";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"add\">\n";
		$html .= "<input type=\"submit\" value=\"カテゴリ新規登録\">\n";
		$html .= "</form>\n";
	}

	$sql  = "SELECT * FROM " . T_MS_TEST_STANDARD_CATEGORY . " WHERE mk_flg='0' ";

	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);

		if(!$max){
			$html .= "<br>\n";
			$html .= "今現在登録されているカテゴリは有りません。<br>\n";
			return $html;
		}

		$html .= "<br>\n";
		$html .= "修正する場合は、修正するカテゴリの詳細ボタンを押してください。<br>\n";
		$html .= "<table class=\"course_form\">\n";
		$html .= "<tr class=\"course_form_menu\">\n";
		$html .= "<th>カテゴリID</th>\n";
		$html .= "<th>カテゴリ</th>\n";
		$html .= "<th>カテゴリ名</th>\n";
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
			$html .= "<input type=\"hidden\" name=\"category_id\" value=\"".$list['category_id']."\">\n";
			$html .= "<td>".$list['category_id']."</td>\n";
			$html .= "<td>".$L_TEST_TYPE_LIST[$list['category_type']]."</td>\n";
			$html .= "<td>".$list['category_name']."</td>\n";
			if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__view",$_SESSION['authority'])===FALSE)) {
				$html .= "<td><input type=\"submit\" name=\"mode\" value=\"詳細\"></td>\n";
			}
			if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__del",$_SESSION['authority'])===FALSE)) {
				$html .= "<td><input type=\"submit\" name=\"mode\" value=\"削除\"></td>\n";
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
 * 標準化カテゴリ管理詳細情報一覧
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @return string HTML
 */
function standard_category_detail_list() {

	global $L_DISPLAY,$L_TEST_TYPE_LIST;
	//-------------------------------------------------
	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$html .= "<br>\n";
	if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__add",$_SESSION['authority'])===FALSE)) {
		$html .= "<br>\n";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"group_add\">\n";
		$html .= "<input type=\"hidden\" name=\"category_id\" value=\"".$_POST['category_id']."\">\n";
		$html .= "<input type=\"submit\" value=\"テストグループ関連登録\">\n";
		$html .= "</form>\n";
	}

	// $sql  = "SELECT * FROM " . T_TEST_STANDARD_CATEGORY_RELATION . " WHERE mk_flg='0' AND category_id = '".$_POST['category_id']."'";
	$sql = get_category_data_sql($_POST['category_id']);

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
		$html .= "<th>テストグループ</th>\n";
		$html .= "<th>サービスコード</th>\n";
		$html .= "<th>合否判定ライン(％)</th>\n";
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
			// $html .= "<input type=\"hidden\" name=\"category_type\" value=\"".$list['category_type']."\">\n";
			$html .= "<td>".$list['id']."</td>\n";
			$html .= "<td>".$list['test_group_id']." ".$list['test_group_name']."</td>\n";
			$html .= "<td>".$list['srvc_cd']."</td>\n";
			$html .= "<td>".$list['pass_line']."</td>\n";
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
 * 新規登録フォーム
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function addform($ERROR) {
	global $L_DISPLAY,$L_USE_FLAG,$L_DO_RADIO,$L_SETTING_TYPE_RADIO,$L_DESIGNATION_FLAG,$L_TEST_TYPE_LIST;

	$html = "<br>\n";
	$html .= "新規登録フォーム<br>\n";

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}


	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" id=\"main-form\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";


	$category_type = $_POST['category_type'];
	$category_type_html = "<select name=\"category_type\" id=\"category_type\">";
	$category_type_html .= "<option value=\"0\">選択して下さい</option>\n";
	foreach ($L_TEST_TYPE_LIST as $key => $val) {
		if ($category_type == $key) { $selected = "selected"; } else { $selected = ""; }
		$category_type_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
	}
	$category_type_html .= "</select>\n";
	$other_html = '';
	if($category_type){

		$scoring_unit_html = make_scoring_unit_info($_POST['scoring_unit'],$category_type);

		$make_html = new read_html();
		$make_html->set_dir(ADMIN_TEMP_DIR);
		$make_html->set_file(TEST_STANDARD_CATEGORY_FORM);

		// add start hirose 2020/12/16 テスト標準化開発 定期テスト
		if($category_type == 1){
			$pass_fail_html = make_hidden_post('pass_fail',$L_USE_FLAG,0);
			$reinforcement_point = make_radio_post($L_USE_FLAG,'reinforcement_point',$category_type,$_POST['reinforcement_point']);
			$aggregation_html = make_hidden_post('deviation_aggregation',$L_DO_RADIO,0);
			$transition_html = make_hidden_post('deviation_transition',$L_DO_RADIO,0);
			$overall_grade_html = make_hidden_post('overall_grade',$L_DO_RADIO,0);
			$question_order_html = make_hidden_post('question_order',$L_SETTING_TYPE_RADIO,1);
			$exam_period_html = make_hidden_post('exam_period',$L_DESIGNATION_FLAG,0);
		}else{
		// add end hirose 2020/12/16 テスト標準化開発 定期テスト
			// $display_html = make_radio_post($L_DISPLAY,'display',$category_type,$_POST['display']);
			$pass_fail_html = make_radio_post($L_USE_FLAG,'pass_fail',$category_type,$_POST['pass_fail']);
			// upd start hirose 2020/12/16 テスト標準化開発 定期テスト
			// $reinforcement_point = make_radio_post($L_USE_FLAG,'reinforcement_point',$category_type,$_POST['reinforcement_point']);
			$reinforcement_point = make_hidden_post('reinforcement_point',$L_USE_FLAG,0);
			// upd end hirose 2020/12/16 テスト標準化開発 定期テスト
			$aggregation_html = make_radio_post($L_DO_RADIO,'deviation_aggregation',$category_type,$_POST['deviation_aggregation']);
			$transition_html = make_radio_post($L_DO_RADIO,'deviation_transition',$category_type,$_POST['deviation_transition']);
			$overall_grade_html = make_radio_post($L_DO_RADIO,'overall_grade',$category_type,$_POST['overall_grade']);
			$question_order_html = make_radio_post($L_SETTING_TYPE_RADIO,'question_order',$category_type,$_POST['question_order']);
			$exam_period_html = make_radio_post($L_DESIGNATION_FLAG,'exam_period',$category_type,$_POST['exam_period']);
		}// add hirose 2020/12/16 テスト標準化開発 定期テスト

		$INPUTS['CATEGORYID'] 	= array('result'=>'plane','value'=>"---");//カテゴリID
		$INPUTS['CATEGORYTYPE'] 	= array('result'=>'plane','value'=>$category_type_html);//カテゴリ
		$INPUTS['CATEGORYNAME'] = array('type'=>'text','name'=>'category_name','size'=>'50','value'=>$_POST['category_name']);//カテゴリ名
		$INPUTS['ZYUKOCOURSE'] = array('type'=>'text','name'=>'zyuko_course','size'=>'50','value'=>$_POST['zyuko_course']);//受講コースCD
		$INPUTS['SYOUSAICOURSE'] = array('type'=>'text','name'=>'syousai_course','size'=>'50','value'=>$_POST['syousai_course']);//詳細コースCD
		$INPUTS['RELATEDID'] = array('type'=>'text','name'=>'related_id','size'=>'50','value'=>$_POST['related_id']);//カテゴリ関連ID
		// $INPUTS['DISPLAY'] 		= array('result'=>'plane','value'=>$display_html);//表示・非表示
		$INPUTS['SCORINGUNIT'] 		= array('result'=>'plane','value'=>$scoring_unit_html);//採点単元利用
		$INPUTS['PASSFAIL'] 		= array('result'=>'plane','value'=>$pass_fail_html);//合否判定
		$INPUTS['AGGREGATION'] 		= array('result'=>'plane','value'=>$aggregation_html);//偏差値集計
		$INPUTS['TRANSITION'] 		= array('result'=>'plane','value'=>$transition_html);//偏差値推移表示
		$INPUTS['OVERALLGRADE'] 		= array('result'=>'plane','value'=>$overall_grade_html);//総合成績表示
		$INPUTS['QUESTIONORDER'] 		= array('result'=>'plane','value'=>$question_order_html);//出題順序
		$INPUTS['EXAMPERIOD'] 		= array('result'=>'plane','value'=>$exam_period_html);//受験可能期間
		$INPUTS['REINFORCEMENTPOINT'] 		= array('result'=>'plane','value'=>$reinforcement_point);//科目別補強ポイント

		$make_html->set_rep_cmd($INPUTS);

		$other_html = $make_html->replace();
	}
	$html .= get_table_html($category_type_html,$other_html);

	// upd start hirose 2020/12/18 テスト標準化開発 定期テスト
	// $html .= "<input type=\"submit\" value=\"追加確認\">\n";
	$html .= "<input type=\"button\" value=\"追加確認\" onclick=\"disabled_submit();\">\n";
	// upd end hirose 2020/12/18 テスト標準化開発 定期テスト
	$html .= "<input type=\"reset\" value=\"クリア\">\n";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"back\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"back\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";
	$html .= get_category_script();
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
	global $L_TEST_TYPE_LIST;

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


	// $test_type = $_POST['test_type'];
	// $test_type_html = "<select name=\"menu_cd\">";
	// $test_type_html .= "<option value=\"0\">選択して下さい</option>\n";
	// foreach ($L_TEST_TYPE_LIST as $key => $val) {
	// 	if ($test_type == $key) { $selected = "selected"; } else { $selected = ""; }
	// 	$test_type_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
	// }
	// $test_type_html .= "</select>\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(TEST_STANDARD_CATEGORY_DETAIL_FORM);

	$INPUTS['ID'] 	= array('result'=>'plane','value'=>"---");//ID
	$INPUTS['TESTGROUPID'] 	= array('type'=>'text','name'=>'test_group_id','size'=>'50','value'=>$_POST['test_group_id']);
	//定期テストのみ仕様なので、今は編集不可にしておく
	$INPUTS['SRVCCD'] 	= array('type'=>'text','name'=>'srvc_cd','size'=>'50','value'=>$_POST['srvc_cd']);
	$INPUTS['SRVCCD'] += read_only_array();
	$INPUTS['PASSLINE'] 	= array('type'=>'text','name'=>'pass_line','size'=>'50','value'=>$_POST['pass_line']);
	if(get_category_option_info($_POST['category_id'],'pass_fail') == 0){
		$INPUTS['PASSLINE'] += read_only_array();
	}
	// $INPUTS['TESTTYPE'] 	= array('result'=>'plane','value'=>$test_type_html);


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


	$INPUTS['ID'] 	= array('result'=>'plane','value'=>$id);//ID
	$INPUTS['TESTGROUPID'] 	= array('type'=>'text','name'=>'test_group_id','size'=>'50','value'=>$test_group_id);
	$INPUTS['SRVCCD'] 	= array('type'=>'text','name'=>'srvc_cd','size'=>'50','value'=>$srvc_cd);
	$INPUTS['SRVCCD'] += read_only_array();
	$INPUTS['TESTTYPE'] 	= array('result'=>'plane','value'=>$test_type_html);
	$INPUTS['PASSLINE'] 	= array('type'=>'text','name'=>'pass_line','size'=>'50','value'=>$pass_line);
	if(get_category_option_info($category_id,'pass_fail') == 0){
		$INPUTS['PASSLINE'] += read_only_array();
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
 * 詳細画面
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function viewform($ERROR) {

	global $L_DISPLAY,$L_USE_FLAG,$L_DO_RADIO,$L_SETTING_TYPE_RADIO,$L_DESIGNATION_FLAG,$L_TEST_TYPE_LIST;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//カテゴリ詳細画面から戻ってきたときはSQLのほうを通す
	if (ACTION && ACTION != 'group_back' && ACTION !='group_add' && ACTION !='group_change') {
		foreach ($_POST as $key => $val) { $$key = $val; }
	} else {
		$sql = "SELECT * FROM ". T_MS_TEST_STANDARD_CATEGORY . " WHERE category_id='".$_POST['category_id']."' AND mk_flg='0' LIMIT 1;";
		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);
		if (empty($list)) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}
		$post_name = reference_post_name($list['category_type']);
		foreach ($list as $key => $val) {
			$$key = replace_decode($val);
			if(!empty($post_name[$key])){
				$$post_name[$key] = replace_decode($val);
			}
		}
	}

	$html = "<br>\n";
	$html .= "詳細画面<br>\n";

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}



	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" id=\"main-form\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"詳細\">\n";
	$html .= "<input type=\"hidden\" name=\"category_id\" value=\"".$category_id."\">\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(TEST_STANDARD_CATEGORY_FORM);

	if (!$category_id) { $category_id = "---"; }

	$scoring_unit_html = make_scoring_unit_info($_POST['scoring_unit'],$category_type);

	$category_type_html = "<select name=\"category_type\"  id=\"category_type\">";
	$category_type_html .= "<option value=\"0\">選択して下さい</option>\n";
	foreach ($L_TEST_TYPE_LIST as $key => $val) {
		if ($category_type == $key) { $selected = "selected"; } else { $selected = ""; }
		$category_type_html .= "<option value=\"".$key."\" ".$selected.">".$val."</option>\n";
	}
	$category_type_html .= "</select>\n";

	// add simon 2020-09-28 テスト標準化 >>>
	if($exam_period==='0') {
		$deviation_aggregation = ''; // non-set value
		$deviation_transition = ''; // non-set value
		$overall_grade = ''; // non-set value
	}
	// <<<

	// add start hirose 2020/12/16 テスト標準化開発 定期テスト
	if($category_type == 1){
		$pass_fail_html = make_hidden_post('pass_fail',$L_USE_FLAG,0);
		$reinforcement_point = make_radio_post($L_USE_FLAG,'reinforcement_point',$category_type,$reinforcement_point);
		$aggregation_html = make_hidden_post('deviation_aggregation',$L_DO_RADIO,0);
		$transition_html = make_hidden_post('deviation_transition',$L_DO_RADIO,0);
		$overall_grade_html = make_hidden_post('overall_grade',$L_DO_RADIO,0);
		$question_order_html = make_hidden_post('question_order',$L_SETTING_TYPE_RADIO,1);
		$exam_period_html = make_hidden_post('exam_period',$L_DESIGNATION_FLAG,0);
	}else{
	// add end hirose 2020/12/16 テスト標準化開発 定期テスト
		// $display_html = make_radio_post($L_DISPLAY,'display',$category_type,$display);
		$pass_fail_html = make_radio_post($L_USE_FLAG,'pass_fail',$category_type,$pass_fail);
		// upd start hirose 2020/12/16 テスト標準化開発 定期テスト
		// $reinforcement_point = make_radio_post($L_USE_FLAG,'reinforcement_point',$category_type,$reinforcement_point);
		$reinforcement_point = make_hidden_post('reinforcement_point',$L_USE_FLAG,0);
		// upd end hirose 2020/12/16 テスト標準化開発 定期テスト
		$aggregation_html = make_radio_post($L_DO_RADIO,'deviation_aggregation',$category_type,$deviation_aggregation);
		$transition_html = make_radio_post($L_DO_RADIO,'deviation_transition',$category_type,$deviation_transition);
		$overall_grade_html = make_radio_post($L_DO_RADIO,'overall_grade',$category_type,$overall_grade);
		$question_order_html = make_radio_post($L_SETTING_TYPE_RADIO,'question_order',$category_type,$question_order);
		$exam_period_html = make_radio_post($L_DESIGNATION_FLAG,'exam_period',$category_type,$exam_period);
		// echo $exam_period;
	}// add hirose 2020/12/16 テスト標準化開発 定期テスト

	$INPUTS['CATEGORYID'] 	= array('result'=>'plane','value'=>"---");//カテゴリID
	$INPUTS['CATEGORYTYPE'] 	= array('result'=>'plane','value'=>$category_type_html);//カテゴリ
	$INPUTS['CATEGORYNAME'] = array('type'=>'text','name'=>'category_name','size'=>'50','value'=>$category_name);//カテゴリ名
	$INPUTS['ZYUKOCOURSE'] = array('type'=>'text','name'=>'zyuko_course','size'=>'50','value'=>$zyuko_course);//受講コースCD
	$INPUTS['SYOUSAICOURSE'] = array('type'=>'text','name'=>'syousai_course','size'=>'50','value'=>$syousai_course);//詳細コースCD
	$INPUTS['RELATEDID'] = array('type'=>'text','name'=>'related_id','size'=>'50','value'=>$related_id);//カテゴリ関連ID
	// $INPUTS['DISPLAY'] 		= array('result'=>'plane','value'=>$display_html);//表示・非表示
	$INPUTS['SCORINGUNIT'] 		= array('result'=>'plane','value'=>$scoring_unit_html);//採点単元利用
	$INPUTS['PASSFAIL'] 		= array('result'=>'plane','value'=>$pass_fail_html);//合否判定
	$INPUTS['AGGREGATION'] 		= array('result'=>'plane','value'=>$aggregation_html);//偏差値集計
	$INPUTS['TRANSITION'] 		= array('result'=>'plane','value'=>$transition_html);//偏差値推移表示
	$INPUTS['OVERALLGRADE'] 		= array('result'=>'plane','value'=>$overall_grade_html);//総合成績表示
	$INPUTS['QUESTIONORDER'] 		= array('result'=>'plane','value'=>$question_order_html);//出題順序
	$INPUTS['EXAMPERIOD'] 		= array('result'=>'plane','value'=>$exam_period_html);//受験可能期間
	$INPUTS['REINFORCEMENTPOINT'] 		= array('result'=>'plane','value'=>$reinforcement_point);//科目別補強ポイント

	$make_html->set_rep_cmd($INPUTS);

	$other_html = $make_html->replace();
	$html .= get_table_html($category_type_html,$other_html,$category_id);


	// upd start hirose 2020/12/18 テスト標準化開発 定期テスト
	// $html .= "<input type=\"submit\" value=\"変更確認\">";
	$html .= "<input type=\"button\" value=\"変更確認\" onclick=\"disabled_submit();\">";
	// upd end hirose 2020/12/18 テスト標準化開発 定期テスト
	$html .= "<input type=\"reset\" value=\"クリア\">";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"back\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"back\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";


	$html .= "<br>\n";
	// $html .= "<hr>\n";// del hirose 2020/12/16 テスト標準化開発 定期テスト
	// $html .= standard_category_detail_list();// del hirose 2020/12/16 テスト標準化開発 定期テスト
	$html .= get_category_script();

	return $html;
}

/**
 * 確認する機能
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @return array エラーの場合
 */
function check() {


	if (!$_POST['category_type']) {
		$ERROR[] = "カテゴリが未選択です。";
	}elseif(!empty($_POST['category_id']) && category_detail_existence_check($_POST['category_id'],$_POST['category_type'])){
		$ERROR[] = "すでにカテゴリ詳細情報が登録されているため、カテゴリを変更することはできません";
	}

	if($_POST['category_type'] && !$_POST['first_view']){
		if(!$_POST['category_name']){
			$ERROR[] = "カテゴリ名が記載されておりません。";
		}elseif(mb_strlen($_POST['category_name'], 'UTF-8')>255){
			$ERROR[] = "カテゴリ名は255字以下で設定してください。";
		}

		if(!isset($_POST['exam_period'])){
			$ERROR[] = "受験可能期間が未選択です。";
		}elseif($_POST['exam_period']){
			if(!isset($_POST['deviation_aggregation'])){
				$ERROR[] = "偏差値集計が未選択です。";
			}
			if(!isset($_POST['deviation_transition'])){
				$ERROR[] = "偏差値推移表示が未選択です。";
			}
			if(!isset($_POST['overall_grade'])){
				$ERROR[] = "総合成績表示が未選択です。";
			}
		}
		// add start hirose 2020/12/18 テスト標準化開発 定期テスト
		//jsで切り分けているので基本的に表示されないが、一応保険
		if($_POST['deviation_aggregation'] == 0){
			if(isset($_POST['deviation_transition']) && $_POST['deviation_transition']){
				$ERROR[] = "偏差値集計をしない時、偏差値推移表示はしないのみ選択可能です。";
			}
			if(isset($_POST['overall_grade']) && $_POST['overall_grade']){
				$ERROR[] = "偏差値集計をしない時、総合成績表示はしないのみ選択可能です。";
			}
		}
		// add end hirose 2020/12/18 テスト標準化開発 定期テスト

		if(!isset($_POST['pass_fail'])){
			$ERROR[] = "合否判定が未選択です。";
		}
		
		// add start hirose 2020/12/16 テスト標準化開発 定期テスト
		if(!isset($_POST['reinforcement_point'])){
			$ERROR[] = "科目別補強ポイントが未選択です。";
		}
		// add end hirose 2020/12/16 テスト標準化開発 定期テスト

		if(!isset($_POST['question_order'])){
			$ERROR[] = "出題順序が未選択です。";
		}elseif($_POST['deviation_aggregation'] && $_POST['question_order'] != '0'){
			$ERROR[] = "偏差値集計で偏差値を集計するを選択している場合は、問題出題は順番出題に設定してください。";
		}

		// if(!empty($not_setting_array['display'])){
		// 	//設定不可項目のためチェックしない
		// }elseif (!$_POST['display']) {
		// 	$ERROR[] = "表示・非表示が未選択です。";
		// }
	}


	return $ERROR;
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

	if($_POST['test_group_id']==""){
		$ERROR[] = "テストグループコードが記載されておりません。";
	}elseif(!preg_match("/^[0-9]+$/", $_POST['test_group_id'])){
		$ERROR[] = "テストグループコードの値は半角数字以外を記入しないで下さい。";
	}else{
		$ERROR_ = check_group_id($_POST['test_group_id'],$_POST['category_id'],$_POST['id']);
		$ERROR = array_merge($ERROR,$ERROR_);
	}

	//カテゴリの合否判定が利用するだったら
	if(get_category_option_info($_POST['category_id'],'pass_fail') == 1){
		if ($_POST['pass_line'] == "") {
			$ERROR[] = "合否判定ライン(％)が記載されておりません。";
		}elseif(!preg_match("/^[0-9]+$/", $_POST['pass_line'])){
			$ERROR[] = "合否判定ライン(％)の値は半角数字以外を記入しないで下さい。";
		}elseif($_POST['pass_line']<0 || $_POST['pass_line']>100){
			$ERROR[] = "合否判定ライン(％)の値は0~100の間で指定してください。";
		}
	}

	return $ERROR;
}


/**
 * 確認フォームを作成する機能
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @return string HTMLフォーム
 */
function check_html() {

	global $L_DISPLAY,$L_USE_FLAG,$L_DO_RADIO,$L_SETTING_TYPE_RADIO,$L_TEST_TYPE_LIST,$L_DESIGNATION_FLAG;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if ($_POST) {
		foreach ($_POST as $key => $val) {
			if ($key == "action") {
				if (MODE == "add") { $val = "add"; }
				elseif (MODE == "詳細") { $val = "change"; }
			}
			$HIDDEN .= "<input type=\"hidden\" name=\"".$key."\" value=\"".$val."\">\n";
		}
	}

	if (ACTION) {
		foreach ($_POST as $key => $val) { $$key = $val; }
	} else {
		$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"change\">\n";
		$sql = "SELECT * FROM " . T_MS_TEST_STANDARD_CATEGORY . " WHERE category_id='".$_POST['category_id']."' AND mk_flg='0' LIMIT 1;";
		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);
		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}
		// add start hirose 2020/12/17 テスト標準化開発 定期テスト
		//optionとの付け合わせだけだと、保存していない情報が取得できないため、
		//あらかじめデフォルト情報を入れておく。
		$d_value = default_value($list['category_type']);
		foreach ($d_value as $key => $val) {
			$$key = $val;
		}
		// add end hirose 2020/12/17 テスト標準化開発 定期テスト
		
		$post_name = reference_post_name($list['category_type']);
		foreach ($list as $key => $val) {
			$$key = replace_decode($val);
			if(!empty($post_name[$key])){
				$$post_name[$key] = replace_decode($val);;
			}
		}
		// add start hirose 2020/12/17 テスト標準化開発 定期テスト
		//受講可能期間を指定しない時、以下の3項目は表示はしない
		if($exam_period==='0' && $list['category_type'] != 1) {
			$deviation_aggregation = ''; // non-set value
			$deviation_transition = ''; // non-set value
			$overall_grade = ''; // non-set value
		}
		// add end hirose 2020/12/17 テスト標準化開発 定期テスト

	}

	if (MODE != "削除") { $button = "登録"; } else { $button = "削除"; }
	$html = "<br>\n";
	$html .= "確認画面：以下の内容で".$button."してもよろしければ".$button."ボタンをクリックしてください。<br>\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(TEST_STANDARD_CATEGORY_FORM);

//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	if (!$category_id) { $category_id = "---"; }
	$INPUTS['CATEGORYNAME'] = array('result'=>'plane','value'=>$category_name);//カテゴリ名
	// $INPUTS['DISPLAY'] 		= array('result'=>'plane','value'=>$L_DISPLAY[$display]);//表示・非表示
	$INPUTS['SCORINGUNIT'] 		= array('result'=>'plane','value'=>$L_USE_FLAG[$scoring_unit]);//採点単元利用
	$INPUTS['PASSFAIL'] 		= array('result'=>'plane','value'=>$L_USE_FLAG[$pass_fail]);//合否判定
	$INPUTS['AGGREGATION'] 		= array('result'=>'plane','value'=>$L_DO_RADIO[$deviation_aggregation]);//偏差値集計
	$INPUTS['TRANSITION'] 		= array('result'=>'plane','value'=>$L_DO_RADIO[$deviation_transition]);//偏差値推移表示
	$INPUTS['OVERALLGRADE'] 		= array('result'=>'plane','value'=>$L_DO_RADIO[$overall_grade]);//総合成績表示
	$INPUTS['QUESTIONORDER'] 		= array('result'=>'plane','value'=>$L_SETTING_TYPE_RADIO[$question_order]);//出題順序
	$INPUTS['EXAMPERIOD'] 		= array('result'=>'plane','value'=>$L_DESIGNATION_FLAG[$exam_period]);//受験可能期間
	$INPUTS['REINFORCEMENTPOINT'] 		= array('result'=>'plane','value'=>$L_USE_FLAG[$reinforcement_point]);//科目別補強ポイント// add hirose 2020/12/17 テスト標準化開発 定期テスト

	$make_html->set_rep_cmd($INPUTS);

	$other_html .= $make_html->replace();
	$html .= get_table_html($L_TEST_TYPE_LIST[$category_type],$other_html,$category_id);
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
		$html .= "<input type=\"hidden\" name=\"action\" value=\"back\">\n";
	}
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
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
		// $HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"group_change\">\n";
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
	$INPUTS['TESTGROUPID'] 	= array('result'=>'plane','value'=>$test_group_id);//カテゴリID
	$INPUTS['SRVCCD'] 	= array('result'=>'plane','value'=>$srvc_cd);//カテゴリID
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
 * DB新規登録
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @return array エラーの場合
 */
function add() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$INSERT_DATA = reference_db_column($_POST['category_type']);

	$INSERT_DATA['category_type'] 	= $_POST['category_type'];
	$INSERT_DATA['category_name'] 	= $_POST['category_name'];
	// $INSERT_DATA['display'] 	= $_POST['display'];

	$INSERT_DATA['ins_tts_id'] 		= $_SESSION['myid']['id'];
	$INSERT_DATA['ins_date'] 			= "now()";
	$INSERT_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];
	$INSERT_DATA['upd_date'] 			= "now()";

	$ERROR = $cdb->insert(T_MS_TEST_STANDARD_CATEGORY,$INSERT_DATA);


	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
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

	$INSERT_DATA['category_id'] 	= $_POST['category_id'];
	$INSERT_DATA['test_group_id'] 	= $_POST['test_group_id'];
	$INSERT_DATA['pass_line'] 	= $_POST['pass_line'];
	// $INSERT_DATA['srvc_cd'] 	= $_POST['srvc_cd'];
	// $INSERT_DATA['menu_cd'] 	= $_POST['menu_cd'];

	$INSERT_DATA['ins_tts_id'] 		= $_SESSION['myid']['id'];
	$INSERT_DATA['ins_date'] 			= "now()";
	$INSERT_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];
	$INSERT_DATA['upd_date'] 			= "now()";

	$ERROR = $cdb->insert(T_TEST_STANDARD_CATEGORY_RELATION,$INSERT_DATA);


	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}


/**
 * DB更新・削除 処理
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @return array エラーの場合
 */
function change() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (MODE == "詳細") {
		$INSERT_DATA = reference_db_column($_POST['category_type']);
		$INSERT_DATA['category_type'] 	= $_POST['category_type'];
		$INSERT_DATA['category_name'] 	= $_POST['category_name'];
		// $INSERT_DATA['display'] 	= $_POST['display'];
		$INSERT_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date'] 			= "now()";
	} elseif (MODE == "削除") {
		$INSERT_DATA['mk_flg'] 			= 1;
		$INSERT_DATA['mk_tts_id'] 		= $_SESSION['myid']['id'];
		$INSERT_DATA['mk_date'] 			= "now()";
		// $delete_mode = true;
	}
	$where = " WHERE category_id='".$_POST['category_id']."' LIMIT 1;";

	$ERROR = $cdb->update(T_MS_TEST_STANDARD_CATEGORY,$INSERT_DATA,$where);


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
		$INSERT_DATA['category_id'] 	= $_POST['category_id'];
		// $INSERT_DATA['book_id'] 	= $_POST['book_id'];
		$INSERT_DATA['test_group_id'] 	= $_POST['test_group_id'];
		$INSERT_DATA['pass_line'] 	= $_POST['pass_line'];
		// $INSERT_DATA['srvc_cd'] 	= $_POST['srvc_cd'];
		// $INSERT_DATA['menu_cd'] 	= $_POST['menu_cd'];
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
 * テストグループIDが存在するかチェックする
 *
 * @param [int] $test_group_id
 * @param [int] $category_type
 * @param [int] $id
 * @return array
 */
function check_group_id($test_group_id,$category_id,$id){
	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$return_array = [];

	if(!isset($category_id)){
		$return_array[] = "カテゴリIDがありません";
		return;
	}
	$sql = "SELECT * FROM " . T_MS_TEST_STANDARD_CATEGORY . " WHERE category_id='".$category_id."' AND mk_flg='0' LIMIT 1;";
	$category_type = "";
	if ($result = $cdb->query($sql)){
		$list = $cdb->fetch_assoc($result);
		$category_type = $list['category_type'];
	}
	if(!$category_type){
		$return_array[] = "カテゴリIDが存在しない値です";
	}


	$sql  = " SELECT ";
	$sql .= " * ";
	$sql .= " FROM " . T_TEST_STANDARD_CATEGORY_RELATION." tr";
	$sql .= " INNER JOIN " . T_MS_TEST_STANDARD_CATEGORY." tc ON tr.category_id = tc.category_id ";
	$sql .= " WHERE 1";
	$sql .= " AND tr.test_group_id='".$test_group_id."'";
	$sql .= " AND tc.category_type='".$category_type."' ";
	$sql .= " AND tc.mk_flg='0' ";
	$sql .= " AND tr.mk_flg='0' LIMIT 1;";
	// print $sql;
	if ($result = $cdb->query($sql)){
		$list = $cdb->fetch_assoc($result);
		if(!empty($list['id']) && $list['id'] != $id){
			$return_array[] = "テストグループコードの値はすでにカテゴリID".$list['category_id']."で登録済です。";
		}
	}


	$where = "";
	if($category_type == 4){
		$where = " AND tg.class_id = '' ";
	}elseif($category_type == 5){
		$where = " AND tg.class_id > '' ";
	}

	$sql  = " SELECT ";
	$sql .= " tg.test_group_id ";
	$sql .= " FROM ";
	$sql .= T_MS_BOOK_GROUP." tg ";
	$sql .= " WHERE tg.test_group_id = '".$test_group_id."'";
	$sql .= " AND tg.mk_flg='0'";
	$sql .= $where;
	$sql .= " ; ";

	// print $sql."<br>";

	if ($result = $cdb->query($sql)) {
		$count = $cdb->num_rows($result);
		if($count==0){
			$return_array[] = "テストグループコードの値は存在しません。";
		}
	}
	return $return_array;
}

/**
 * 採点単元利用のhtmlをカテゴリ別に作成
 *
 * @param [int] $scoring_unit
 * @param [int] $category_type
 * @return html
 */
function make_scoring_unit_info($scoring_unit,$category_type){
	global $L_USE_FLAG;

	if($category_type == 4){
		$scoring_unit = 0;
	}elseif($category_type == 5){
		$scoring_unit = 1;
	// add start hirose 2020/12/16 テスト標準化開発 定期テスト
	}elseif($category_type == 1){
		$scoring_unit = 0;
	// add end hirose 2020/12/16 テスト標準化開発 定期テスト
	}
	$html = '<input type="hidden" name="scoring_unit" value="'.$scoring_unit.'">';
	$html .= $L_USE_FLAG[$scoring_unit];
	return $html;
}


/**
 * ラジオボタン制御用html取得
 *
 * @return html
 */
function get_category_script(){
	$js_html  = "<script>\n";
	$js_html .= "$(function() {\n";
	$js_html .= "  chenge_radio();\n";
	$js_html .= "});\n";

	$js_html .= "function chenge_radio(){\n";
	$js_html .= "  if($('input[name=exam_period]:checked').val() == 0){\n";
	$js_html .= "    $('input:[name=deviation_aggregation]').attr('disabled', true);\n";
	$js_html .= "    $('input:[name=deviation_transition]').attr('disabled', true);\n";
	$js_html .= "    $('input:[name=overall_grade]').attr('disabled', true);\n";
	$js_html .= "  }else if($('input[name=exam_period]').size() > 0 ){\n";
	// add start hirose 2020/12/18 テスト標準化開発 定期テスト
	$js_html .= "    var type = $('[name=category_type]').val();";
	$js_html .= "    if(type == 5 && $('input[name=exam_period]:checked').val() == 1){";
	$js_html .= "    	change_disabled_radio_2();\n";
	$js_html .= "    }else if($('input[name=deviation_aggregation]:checked').val() == 0){\n";
	$js_html .= "    	change_disabled_radio_1();\n";
	$js_html .= "    }else{\n";
	// add end hirose 2020/12/18 テスト標準化開発 定期テスト
	$js_html .= "    $('input:[name=deviation_aggregation]').attr('disabled', false);\n";
	$js_html .= "    $('input:[name=deviation_transition]').attr('disabled', false);\n";
	$js_html .= "    $('input:[name=overall_grade]').attr('disabled', false);\n";
	$js_html .= "  	 }\n";// add hirose 2020/12/18 テスト標準化開発 定期テスト
	$js_html .= "  }\n";
	$js_html .= "}\n";

	$js_html .= "$('input[name=exam_period]:radio').change(function() {\n";
	$js_html .= "  if ($('input[name=exam_period]:checked').val() == 1) {\n";
	// add start hirose 2020/12/17 テスト標準化開発 定期テスト
	$js_html .= "    var type = $('[name=category_type]').val();";
	$js_html .= "    if(type == 5){";
	$js_html .= "    	change_disabled_radio_2();\n";
	$js_html .= "    }else{\n";
	// add end hirose 2020/12/17 テスト標準化開発 定期テスト
	$js_html .= "    $('input:[name=deviation_aggregation]').attr('disabled', false);\n";
	$js_html .= "    $('input:[name=deviation_transition]').attr('disabled', false);\n";
	$js_html .= "    $('input:[name=overall_grade]').attr('disabled', false);\n";
	$js_html .= "    }\n";// add hirose 2020/12/17 テスト標準化開発 定期テスト
	$js_html .= "  }else{\n";
	$js_html .= "    $('input:[name=deviation_aggregation]').attr('checked',false);\n";
	$js_html .= "    $('input:[name=deviation_aggregation]').attr('disabled', true);\n";
	$js_html .= "    $('input:[name=deviation_transition]').attr('checked',false);\n";
	$js_html .= "    $('input:[name=deviation_transition]').attr('disabled', true);\n";
	$js_html .= "    $('input:[name=overall_grade]').attr('checked',false);\n";
	$js_html .= "    $('input:[name=overall_grade]').attr('disabled', true);\n";
	$js_html .= "  }\n";
	$js_html .= "});\n";
	
	$js_html .= "$('#category_type').change(function() {\n";
	// add start hirose 2020/12/18 テスト標準化開発 定期テスト
	$js_html .= "  $('input:[name=deviation_aggregation]').attr('checked',false);\n";
	$js_html .= "  $('input:[name=deviation_transition]').attr('checked',false);\n";
	$js_html .= "  $('input:[name=overall_grade]').attr('checked',false);\n";
	// add end hirose 2020/12/18 テスト標準化開発 定期テスト
	$js_html .= "  $('#main-form [name=action]').val('category_change');$('#main-form').submit();\n";
	$js_html .= "});\n";

	$js_html .= "$('input[type=reset]').click(function(e){\n";
	$js_html .= "  e.preventDefault();\n";
	$js_html .= "  $(this).closest('form').get(0).reset();\n";
	$js_html .= "  chenge_radio();\n";
	$js_html .= "});\n";

	// add start hirose 2020/12/17 テスト標準化開発 定期テスト
	$js_html .= "$('input[name=deviation_aggregation]:radio').change(function() {\n";
	$js_html .= "  change_disabled_radio_1();\n";
	$js_html .= "});\n";
	$js_html .= "function change_disabled_radio_1() {\n";
	$js_html .= "  if ($('input[name=deviation_aggregation]:checked').val() == 0) {\n";
	$js_html .= "    $('#deviation_transition_0').prop('checked', true);\n";
	$js_html .= "    $('#overall_grade_0').prop('checked', true);\n";
	$js_html .= "    $('input:[name=deviation_transition]').attr('disabled', true);\n";
	$js_html .= "    $('input:[name=overall_grade]').attr('disabled', true);\n";
	$js_html .= "  }else{\n";
	$js_html .= "    $('input:[name=deviation_transition]').attr('disabled', false);\n";
	$js_html .= "    $('input:[name=overall_grade]').attr('disabled', false);\n";
	$js_html .= "  }\n";
	$js_html .= "}\n";
	$js_html .= "function change_disabled_radio_2() {\n";
	$js_html .= "    	$('#deviation_aggregation_0').prop('checked', true);\n";
	$js_html .= "    	$('#deviation_transition_0').prop('checked', true);\n";
	$js_html .= "    	$('#overall_grade_0').prop('checked', true);\n";
	$js_html .= "    	$('input:[name=deviation_aggregation]').attr('disabled', true);\n";
	$js_html .= "    	$('input:[name=deviation_transition]').attr('disabled', true);\n";
	$js_html .= "    	$('input:[name=overall_grade]').attr('disabled', true);\n";
	$js_html .= "}\n";
	$js_html .= "function disabled_submit() {\n";
	$js_html .= "    	$('input:[name=deviation_aggregation]').attr('disabled', false);\n";
	$js_html .= "    	$('input:[name=deviation_transition]').attr('disabled', false);\n";
	$js_html .= "    	$('input:[name=overall_grade]').attr('disabled', false);\n";
	$js_html .= "    	$('#main-form').submit();\n";
	$js_html .= "}\n";
	// add end hirose 2020/12/17 テスト標準化開発 定期テスト

	$js_html .= "</script>\n";
	return $js_html;
}

/**
 * テーブル作成用
 * カテゴリタイプがあったら、すべてのテーブルを出す
 *
 * @param [html] $category_type_html
 * @param [html] $other_html
 * @param [int || null] $category_id
 * @return html
 */
function get_table_html($category_type_html,$other_html,$category_id = NULL){
	$first_view = '';
	if(!$other_html){
		$first_view = '<input type="hidden" name="first_view" value="1">';
	}
	if($category_id){
		$id = $category_id;
	}else{
		$id = '--';
	}
	$html  = $first_view;
	$html .= '<table class="course_form">';
	$html .= '<tr>';
	$html .= '<td class="course_form_menu">カテゴリID</td>';
	$html .= '<td class="course_form_cell">';
	$html .= $id;
	$html .= '</td>';
	$html .= '</tr>';
	$html .= '<tr>';
	$html .= '<td class="course_form_menu">カテゴリ</td>';
	$html .= '<td class="course_form_cell">';
	$html .= $category_type_html;
	$html .= '</td>';
	$html .= '</tr>';
	$html .= $other_html;
	$html .= '</table>';

	return $html;

}

/**
 * ラジオボタンのinput情報を作成
 *
 * @param [array] $l_array 表示項目
 * @param [string] $name name inputのネーム
 * @param [int] $name category_type
 * @param [int] $name check チェックを入れる値
 * @return html
 */
function make_radio_post($l_array,$name,$category_type,$check){
	foreach($l_array as $key => $val) {
		if ($val == "") { continue; }
		$newform = new form_parts();
		$newform->set_form_type("radio");
		$newform->set_form_name($name);
		$newform->set_form_id($name."_".$key);
		$newform->set_form_check($check);
		$newform->set_form_value("".$key."");
		$btn = $newform->make();
		if ($html) { $html .= " / "; }
		$html .= $btn."<label for=\"".$name."_".$key."\">".$val."</label>";
	}
	return $html;
}
// add start hirose 2020/12/16 テスト標準化開発 定期テスト
/**
 * ラジオボタンのinput情報を作成
 *
 * @param string $name inputのネーム
 * @param array $val 表示用の項目
 * @param int $num inputのvalue
 * @return html
 */
function make_hidden_post($name,$val,$num){
	$newform = new form_parts();
	$newform->set_form_type("hidden");
	$newform->set_form_name($name);
	$newform->set_form_value("".$num."");
	$html = $newform->make();
	$html .= $val[$num];
	return $html;
}
// add end hirose 2020/12/16 テスト標準化開発 定期テスト


/**
 * optionインサート振り分け用
 * カテゴリタイプによってoptionに入る情報が変わる
 *
 * @param [int] $category_type
 * @return array
 */
function reference_db_column($category_type){

	$option_column = reference_post_name($category_type);

	$INSERT_DATA = array();
	// add start hirose 2020/12/21 テスト標準化開発 定期テスト
	//カテゴリタイプを変更した際、使われていないoptionに前のデータが残るため、
	//初めにoption情報を初期化
	$INSERT_DATA['option_flg1'] = 0;
	$INSERT_DATA['option_flg2'] = 0;
	$INSERT_DATA['option_flg3'] = 0;
	$INSERT_DATA['option_flg4'] = 0;
	$INSERT_DATA['option_flg5'] = 0;
	$INSERT_DATA['option_flg6'] = 0;
	$INSERT_DATA['option_flg7'] = 0;
	// add end hirose 2020/12/21 テスト標準化開発 定期テスト
	foreach($option_column as $col => $name){
		if(isset($_POST[$name])){
			$INSERT_DATA[$col] = $_POST[$name];
		}
	}

	// add simon 2020-09-28 テスト標準化 >>>
	// フォームのラジオボタンは、disabledする時に、POSTにされないので、
	// ここで、確認して、項目の設定する
	if($category_type==4 && $INSERT_DATA['option_flg1']=='0') { // 学力診断テスト
		$INSERT_DATA['option_flg2'] = 0;
		$INSERT_DATA['option_flg3'] = 0;
		$INSERT_DATA['option_flg4'] = 0;
	}
	// <<<

	return $INSERT_DATA;
}

/**
 * テストの種類によって、カラム情報が異なるため、
 * 相関的なオプションの配列を取得
 *
 * @param [int] $category_type
 * @return array
 */
function reference_post_name($category_type){
	$name_array = [];
	if($category_type == 4){
		$name_array += array(
			'option_flg1'=>'exam_period',//受験可能期間
			'option_flg2'=>'deviation_aggregation',//偏差値集計
			'option_flg3'=>'deviation_transition',//偏差値推移表示
			'option_flg4'=>'overall_grade',//総合成績表示
			'option_flg5'=>'pass_fail',//合否判定
			// 'option_flg6'=>'scoring_unit',//採点単元利用
			'option_flg7'=>'question_order',//出題順序
		);
	}elseif($category_type == 5){
		$name_array += array(
			'option_flg1'=>'exam_period',//受験可能期間
			// 'option_flg2'=>'deviation_aggregation',//偏差値集計
			// 'option_flg3'=>'deviation_transition',//偏差値推移表示
			// 'option_flg4'=>'overall_grade',//総合成績表示
			'option_flg5'=>'pass_fail',//合否判定
			'option_flg6'=>'scoring_unit',//採点単元利用
			'option_flg7'=>'question_order',//出題順序
		);
	// add start hirose 2020/12/16 テスト標準化開発 定期テスト
	}elseif($category_type == 1){
		$name_array += array(
			'option_flg1'=>'reinforcement_point',//科目別補強ポイント
		);
	// add end hirose 2020/12/16 テスト標準化開発 定期テスト
	}
	return $name_array;

}
// add start hirose 2020/12/17 テスト標準化開発 定期テスト
/**
 * 標準カテゴリを切り替えた時、option情報の値をクリアする
 *
 */
function category_change(){
	$list = default_value();
	foreach($list as $key => $v){
		unset($_POST[$key]);
	}
}
/**
 * 削除確認用　デフォルトの表示項目を取得
 *
 * @param int $category_type_ テストタイプ
 * @return array
 */
function default_value($category_type_=""){
	$defalut_list = [
		'scoring_unit'=>0,//採点単元利用
		'pass_fail'=>0,//合否判定
		'question_order'=>0,//出題順序
		'exam_period'=>0,//受験可能期間
		'reinforcement_point'=>0,//科目別補強ポイント
		'deviation_aggregation'=>0,//偏差値集計
		'deviation_transition'=>0,//偏差値推移表示
		'overall_grade'=>0,//総合成績表示
	];
	if($category_type_ == 1){
		$defalut_list['question_order'] = 1;
	}
	return $defalut_list;
}
// add end hirose 2020/12/17 テスト標準化開発 定期テスト

/**
 * カテゴリ詳細情報をだすSQLを取得
 *
 * @param [int] $category_id
 * @return string SQL
 */
function get_category_data_sql($category_id){

	$sql  = " SELECT ";
	$sql .= " sr.* ";
	$sql .= " ,tg.test_group_name ";
	$sql .= " FROM ". T_TEST_STANDARD_CATEGORY_RELATION . " sr ";
	$sql .= " INNER JOIN ". T_MS_BOOK_GROUP . " tg ON sr.test_group_id = tg.test_group_id";
	$sql .= " WHERE sr.mk_flg='0'";
	$sql .= " AND tg.mk_flg='0'";
	$sql .= " AND sr.category_id = '".$category_id."'";

	// print $sql;

	return $sql;
}

/**
 * 該当のカテゴリに詳細カテゴリが結びついているか確認
 *
 * @param [int] $category_id_
 * @param [int] $category_type_
 * @return bool
 */
function category_detail_existence_check($category_id_,$category_type_){
	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql  = " SELECT ";
	$sql .= " tc.category_type ";
	$sql .= " FROM ". T_TEST_STANDARD_CATEGORY_RELATION." cr ";
	$sql .= " INNER JOIN ". T_MS_TEST_STANDARD_CATEGORY." tc ON cr.category_id = tc.category_id";
	$sql .= " WHERE tc.category_id='".$category_id_."'";
	$sql .= " AND cr.mk_flg='0'";
	$sql .= " AND tc.mk_flg='0'";
	$sql .= " LIMIT 1;";

	// print $sql;

	$return_result = false;
	if($result = $cdb->query($sql)){
		$list = $cdb->fetch_assoc($result);
		$category_type = $list['category_type'];
		//結びついているカテゴリ詳細があって、登録しているカテゴリタイプと、今回登録しようとしている
		//カテゴリタイプが異なった場合
		if(!empty($category_type) && $category_type != $category_type_){
			$return_result = true;
		}
	}
	return $return_result;
}

/**
 * 該当のカテゴリに詳細カテゴリが結びついているか確認
 *
 * @param [int] $category_id_
 * @param [int] $column_name_
 * @return string||int
 */
function get_category_option_info($category_id_,$column_name_){
	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//pass_fail

	$sql  = " SELECT ";
	$sql .= " tc.* ";
	$sql .= " FROM ". T_MS_TEST_STANDARD_CATEGORY." tc ";
	$sql .= " WHERE tc.category_id='".$category_id_."'";
	$sql .= " AND tc.mk_flg='0'";
	$sql .= " LIMIT 1;";

	// print $sql;

	$return_data = "";
	if($result = $cdb->query($sql)){
		$list = $cdb->fetch_assoc($result);
		$category_type = $list['category_type'];
		if(!empty($category_type)){
			$column_array = reference_post_name($category_type);
			$column_key = array_search($column_name_,$column_array);
			if(isset($list[$column_key])){
				$return_data = $list[$column_key];
			}
		}
	}
	return $return_data;
}

/**
 * disabledに似せたCSSを作成する用の配列を取得
 *
 * @return array
 */
function read_only_array(){
	return array(
		'action' => 'readonly',
		'style' => 'background-color:#eee;',
	);
}