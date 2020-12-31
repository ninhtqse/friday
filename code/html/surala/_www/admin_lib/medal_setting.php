<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * プラクティスステージ管理　メダル設定
 *
 * 履歴
 * 2019/03/11 初期設定
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
function start() {

//	pre($_POST);
//	pre(MODE);
//	pre(ACTION);
	if (ACTION == "check") {
		$ERROR = check();
	}
	if (!$ERROR) {
		if (ACTION == "add") { $ERROR = add(); }
		elseif (ACTION == "change") { $ERROR = change(); }
		elseif (ACTION == "del") { $ERROR = change(); }
	}
	if(MODE == "add"){
		if(ACTION == "check"){
			if(!$ERROR){
				$html .= check_html();
			}else{
				$html .= addform($ERROR);
			}
		}elseif(ACTION == "add"){
			if(!$ERROR){
				$html .= medal_setting_list($ERROR);
			}else{
				$html .= addform($ERROR);
			}
		}else{
			$html .= addform($ERROR);
		}
	}else if(MODE == "詳細"){
		if(ACTION == "check"){
			if(!$ERROR){
				$html .= check_html();
			}else{
				$html .= viewform($ERROR);
			}
		}elseif(ACTION == "change"){
			if (!$ERROR) { 
				$html .= medal_setting_list($ERROR);
			}else { 
				$html .= viewform($ERROR); 
			}
		}else{
				$html .= viewform($ERROR);
		}
	
	}else if(MODE == "削除"){
		if(ACTION == "change"){
			if(!$ERROR){
				$html .= medal_setting_list($ERROR);
			}else{
				$html .= check_html();
			}
		}else{
				$html .= check_html();
		}
		
	}else{
		
		$html .= medal_setting_list($ERROR);
	}

	return $html;
}


/**
 * コース選択
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function select_course() {


	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// 使用禁止のコースが存在するかチェックし、抽出条件を作成する
	$course_list = "";
	if (is_array($_SESSION['authority']) && count($_SESSION['authority']) > 0) {
		foreach ($_SESSION['authority'] as $key => $value) {
			if (!$value) { continue; }
			// 使用禁止のコースが存在した場合
			if (substr($value,0,24) == "practice__drill__course_") {
				if ($course_list) { $course_list .= ","; }
				$course_list .= substr($value,24);						// コース番号を取得する
			}
		}
	}

	$sql  = "SELECT c.course_num,c.course_name FROM ".T_COURSE. " c ";
	$sql .= " INNER JOIN " . T_SERVICE_COURSE_LIST . " scl ";
	$sql .= " ON c.course_num = scl.course_num AND scl.course_type = '1' AND scl.display = '1' AND scl.mk_flg = '0'";
	$sql .= " INNER JOIN " . T_SERVICE . " sc ";
	$sql .= " ON scl.service_num = sc.service_num AND sc.setup_type_sub = '1' AND sc.display = '1' AND sc.mk_flg = '0'";
	$sql .= " WHERE c.state = '0'";
	if ($course_list) { $sql .= " AND c.course_num NOT IN (".$course_list.") "; }
	$sql .= " ORDER BY c.list_num;";
        
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
	}
	if (!$max) {
		$html .= "コースを設定してからご利用下さい。<br>\n";
		return $html;
	}
	$course_num = "";
	$stage_num = "";
	$lesson_num = "";
	if($_POST['course_num']){
		$course_num = $_POST['course_num'];
	}
	if($_POST['stage_num']){
		$stage_num = $_POST['stage_num'];
	}
	if($_POST['lesson_num']){
		$lesson_num = $_POST['lesson_num'];
	}
	//戻るボタン等で、登録画面から一覧に戻ってきたとき
	if(!$course_num && !$stage_num && $lesson_num){
		$check_sql =  "SELECT course_num,stage_num FROM " . T_LESSON .
				" WHERE state!='1' AND lesson_num='$lesson_num';";
		if ($result_sql = $cdb->query($check_sql)) {
			while ($list = $cdb->fetch_assoc($result_sql)) {
				$course_num = $list['course_num'];
				$stage_num = $list['stage_num'];
			}
		}
		//print $check_sql.'<br>';
	}
	if (!$course_num && !$stage_num && !$lesson_num ) {
		$msg .= "コース、ステージ、Lessonを選択してください。<br>\n";
	}
	if (!$course_num) { $selected = "selected"; } else { $selected = ""; }
	$course_num_html .= "<option value=\"\" $selected>選択して下さい</option>\n";
	while ($list = $cdb->fetch_assoc($result)) {
		if ($course_num == $list['course_num']) { $selected = "selected"; } else { $selected = ""; }
		$course_num_html .= "<option value=\"{$list['course_num']}\" $selected>{$list['course_name']}</option>\n";
		if ($selected) { $L_NAME['course_name'] = $list['course_name']; }
	}

	if (!$stage_num) { $selected = "selected"; } else { $selected = ""; }
	$stage_num_html .= "<option value=\"\" $selected>----------</option>\n";
	$sql  = "SELECT stage_num,stage_name FROM " . T_STAGE .
			" WHERE state!='1' AND course_num='".$course_num."' ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
		while ($list = $cdb->fetch_assoc($result)) {
			if ($stage_num == $list['stage_num']) { $selected = "selected"; } else { $selected = ""; }
			$stage_num_html .= "<option value=\"{$list['stage_num']}\" $selected>{$list['stage_name']}</option>\n";
			if ($selected) { $L_NAME['stage_name'] = $list['stage_name']; }
		}
	}
	if (!$msg && !$max) { $msg .= "ステージが設定されておりません。<br>\n"; }
	elseif (!$msg && !$stage_num) { $msg .= "ステージを選択してください。<br>\n"; }

	if (!$lesson_num) { $selected = "selected"; } else { $selected = ""; }
	$lesson_num_html .= "<option value=\"\" $selected>----------</option>\n";
	$sql = "SELECT lesson_num,lesson_name FROM " . T_LESSON .
		" WHERE state!='1' AND stage_num='".$stage_num."' ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
		while ($list = $cdb->fetch_assoc($result)) {
			if ($lesson_num == $list['lesson_num']) { $selected = "selected"; } else { $selected = ""; }
			$lesson_num_html .= "<option value=\"{$list['lesson_num']}\" $selected>{$list['lesson_name']}</option>\n";
			if ($selected) { $L_NAME['lesson_name'] = $list['lesson_name']; }
		}
	}
	if (!$msg && !$max) { $msg .= "Lessonが設定されておりません。<br>\n"; }
	elseif (!$msg && !$lesson_num) { $msg .= "Lessonを選択してください。<br>\n"; }


	$html .= "<br>\n";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" name=\"menu\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"set_course\">\n";
	$html .= "<table class=\"unit_form\">\n";
	$html .= "<tr class=\"unit_form_cell\">\n";
	$html .= "<td class=\"stage_form_menu\">コース</td>\n";
	$html .= "<td class=\"stage_form_menu\">ステージ</td>\n";
	$html .= "<td class=\"stage_form_menu\">Lesson</td>\n";
	$html .= "</tr>\n";
	$html .= "<tr class=\"stage_form_cell\">\n";
	$html .= "<td>\n";
	$html .= "<select name=\"course_num\" onchange=\"submit_course()\">\n";
	$html .= $course_num_html;
	$html .= "</select>\n";
	$html .= "</td>\n";
	$html .= "<td>\n";
	$html .= "<select name=\"stage_num\" onchange=\"submit_stage()\">\n";
	$html .= $stage_num_html;
	$html .= "</select>\n";
	$html .= "</td>\n";
	$html .= "<td><select name=\"lesson_num\" onchange=\"submit_lesson()\">\n";
	$html .= $lesson_num_html;
	$html .= "</select></td>\n";
	$html .= "</tr>\n";
	$html .= "</table>\n";
	$html .= "</form><br>\n";
	$html .= $msg;
	return $html;
}

/**
 * メダル情報一覧
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function medal_setting_list($ERROR) {

	global $L_WEAK_POINT;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$html .= select_course();
	
	if ( !$_POST['lesson_num'] ) {
		return $html;
	}

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
		$html .= "<br />\n";
	}
	
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" name=\"medal_form\" method=\"POST\" style=\"float:left;\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"add\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n"; //add kimura 2019/07/12 生徒画面TOP改修
	$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\">\n";
	$html .= "<input type=\"submit\" value=\"メダル設定新規登録\">";
	$html .= "</form>\n";
	$html .= "<div style=\"clear:both\"></div>\n";


	$sql  = "SELECT	";
	$sql .= " ulms.medal_setting_id,";
	$sql .= " ulms.unit_num,";
	$sql .= " ulms.gold_percent,";
	$sql .= " ulms.gold_weak_flg,";
	$sql .= " ulms.silver_percent,";
	$sql .= " ulms.silver_weak_flg,";
	$sql .= " ulms.copper_percent,";
	$sql .= " ulms.copper_weak_flg,";
	$sql .= " ulms.other_percent,";
	$sql .= " ulms.other_weak_flg,";
	$sql .= " u.unit_num,";
	$sql .= " u.unit_key";
	$sql .= " FROM ".T_UNIT_LIST_MEDAL_SETTING." ulms";
	$sql .= " INNER JOIN ".T_UNIT." u ON ulms.unit_num = u.unit_num ";
	$sql .= " WHERE 1";
	$sql .= " AND u.lesson_num = ".$_POST['lesson_num'];
//	$sql .= " AND u.display = 1";
	$sql .= " AND u.state = 0";
	$sql .= " AND ulms.mk_flg = 0";
	
	//print $sql.'<br>';
	
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
	}

	if (!$max) {
		$html .= "<br>\n";
		$html .= "メダル情報は登録されておりません。<br>\n";
		return $html;
	}
	

	$html .= $max."問登録があります。<br>";
	$html .= "<table class=\"member_form\">\n";
	$html .= "<tr class=\"member_form_menu\">\n";
	$html .= "<td>管理番号</td>\n";
	$html .= "<td>ユニット番号</td>\n";
	$html .= "<td>ユニットキー</td>\n";
	$html .= "<td>金メダル ドリル正解率(%以上)</td>\n";
	$html .= "<td>弱点</td>\n";
	$html .= "<td>銀メダル ドリル正解率(%以上)</td>\n";
	$html .= "<td>弱点</td>\n";
	$html .= "<td>銅メダル ドリル正解率(%以上)</td>\n";
	$html .= "<td>弱点</td>\n";
	$html .= "<td>残念メダル ドリル正解率(%以上)</td>\n";
	$html .= "<td>弱点</td>\n";
	$html .= "<td></td>\n";
	$html .= "<td></td>\n";
	$html .= "</tr>\n";

	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$html .= "<tr class=\"member_form_cell\">\n";
			$html .= "<td>".$list['medal_setting_id']."</td>\n";
			$html .= "<td>".$list['unit_num']."</td>\n";
			$html .= "<td>".$list['unit_key']."</td>\n";
			$html .= "<td>".$list['gold_percent']."</td>\n";
			$html .= "<td>".$L_WEAK_POINT[$list['gold_weak_flg']]."</td>\n";
			$html .= "<td>".$list['silver_percent']."</td>\n";
			$html .= "<td>".$L_WEAK_POINT[$list['silver_weak_flg']]."</td>\n";
			$html .= "<td>".$list['copper_percent']."</td>\n";
			$html .= "<td>".$L_WEAK_POINT[$list['copper_weak_flg']]."</td>\n";
			$html .= "<td>".$list['other_percent']."</td>\n";
			$html .= "<td>".$L_WEAK_POINT[$list['other_weak_flg']]."</td>\n";
			$html .= "<td>";
			$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";			
			$html .= "<input type=\"hidden\" name=\"action\" value=\"view\">\n";
			$html .= "<input type=\"submit\" name=\"mode\" value=\"詳細\">\n";
			$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\">\n";
			$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"".$list['unit_num']."\">\n";
			$html .= "</form>\n";
			$html .= "</td>";
			$html .= "<td>";
			$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"削除\">\n";
			$html .= "<input type=\"hidden\" name=\"medal_setting_id\" value=\"".$list['medal_setting_id']."\">\n";
			$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\">\n";
			$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"".$list['unit_num']."\">\n";
			$html .= "<input type=\"submit\" value=\"削除\" \">\n";
			$html .= "</td>";
			$html .= "</form>\n";
			$html .= "</td>\n";
			$html .= "</tr>\n";
		}
	}
	$html .= "</table>\n";


	return $html;
}

/**
 * 表示の為のフォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function viewform($ERROR) {

	global $L_WEAK_POINT;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	//エラーがあった時
	if ($action == "check" || $action == "back") {
		foreach ($_POST as $key => $val) { $$key = $val; }
	} else {
		$sql = "SELECT *,u.course_num,u.stage_num,u.lesson_num FROM " .T_UNIT_LIST_MEDAL_SETTING." ulms".
			" INNER JOIN ".T_UNIT." u ON ulms.unit_num = u.unit_num".
			" WHERE ulms.unit_num='".$_POST['unit_num']."' AND ulms.mk_flg = 0 AND u.state = 0 LIMIT 1;";

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

	$html = "<br>\n";
	$html .= "詳細画面<br>\n";

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";
	$html .= "<input type=\"hidden\" name=\"medal_setting_id\" value=\"".$medal_setting_id."\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$course_num."\">\n"; //add kimura 2019/07/12 生徒画面TOP改修
	$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$lesson_num."\">\n";
	$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"".$unit_num."\">\n";
	
	$sql  = "SELECT unit_key";
	$sql .= " FROM ".T_UNIT;
	$sql .= " WHERE unit_num = ".$unit_num;
	$sql .= " AND state = 0";
	$sql .= " LIMIT 1";
	
	$result = $cdb->query($sql);
	$unit_list = $cdb->fetch_assoc($result);
	$unit_key = $unit_list['unit_key'];

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(PRACTICE_MEDAL_FORM);

	if (!$medal_setting_id) { $medal_setting_id = "---"; }
	$INPUTS[MEDALID] = array('result'=>'plane','value'=>$medal_setting_id);
	$INPUTS[UNITKEY] = array('result'=>'plane','value'=>$unit_key);
	$INPUTS[GOLDPERCENT] = array('type'=>'text','name'=>'gold_percent','value'=>$gold_percent);
	
	$INPUTS[SILVERPERCENT] = array('type'=>'text','name'=>'silver_percent','value'=>$silver_percent);
	$INPUTS[COPPERPERCENT] = array('type'=>'text','name'=>'copper_percent','value'=>$copper_percent);
	$INPUTS[OTHERPERCENT] = array('type'=>'text','name'=>'other_percent','value'=>$other_percent);
	
	//弱点
	//金メダル　弱点があっても評価に含めてよいか
	$gold_percent_radio = make_radio_weak_point('gold',$gold_weak_flg);
	$INPUTS[GOLDWEAKFLG] = array('result'=>'plane','value'=>$gold_percent_radio);
	
	//銀メダル　弱点があっても評価に含めてよいか
	$silver_percent_radio = make_radio_weak_point('silver',$silver_weak_flg);
	$INPUTS[SILVERWEAKFLG] = array('result'=>'plane','value'=>$silver_percent_radio);
	
	
	//銅メダル　弱点があっても評価に含めてよいか
	$copper_percent_radio = make_radio_weak_point('copper',$copper_weak_flg);
	$INPUTS[COPPERWEAKFLG] = array('result'=>'plane','value'=>$copper_percent_radio);
	
	
	//残念メダル　弱点があっても評価に含めてよいか
	$other_percent_radio = make_radio_weak_point('other',$other_weak_flg);
	$INPUTS[OTHERWEAKFLG] = array('result'=>'plane','value'=>$other_percent_radio);
	

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"変更確認\">";
	$html .= "<input type=\"reset\" value=\"クリア\">";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"medal_setting_list\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n"; //add kimura 2019/07/12 生徒画面TOP改修
	$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

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
	
	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];


	$html .= "<br>\n";
	$html .= "新規登録フォーム<br>\n";

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n"; //add kimura 2019/07/12 生徒画面TOP改修
	$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\">\n";
	
	//対象lessonに結び付くunit_key一覧
	$sql  = "SELECT u.unit_num,u.unit_key ";
	$sql .= " FROM ".T_UNIT." u ";
	$sql .= " WHERE u.lesson_num = ".$_POST['lesson_num'];
	$sql .= " AND u.state = 0";
	
	//print $sql.'<br>';
	
	
	$unit_key_array = array(0=>'---');
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$unit_key_array[$list['unit_num']] = $list['unit_key'];
		}
	}

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(PRACTICE_MEDAL_FORM);
	
	$INPUTS[MEDALID] = array('result'=>'plane','value'=>"---");
	$INPUTS[GOLDPERCENT] = array('type'=>'text','name'=>'gold_percent','value'=>$_POST['gold_percent']);
	
	$INPUTS[SILVERPERCENT] = array('type'=>'text','name'=>'silver_percent','value'=>$_POST['silver_percent']);
	$INPUTS[COPPERPERCENT] = array('type'=>'text','name'=>'copper_percent','value'=>$_POST['copper_percent']);
	$INPUTS[OTHERPERCENT] = array('type'=>'text','name'=>'other_percent','value'=>$_POST['other_percent']);
	
	//弱点
	//金メダル　弱点があっても評価に含めてよいか
	$gold_percent_radio = make_radio_weak_point('gold',$_POST['gold_weak_flg']);
	$INPUTS[GOLDWEAKFLG] = array('result'=>'plane','value'=>$gold_percent_radio);
	
	//銀メダル　弱点があっても評価に含めてよいか
	$silver_percent_radio = make_radio_weak_point('silver',$_POST['silver_weak_flg']);
	$INPUTS[SILVERWEAKFLG] = array('result'=>'plane','value'=>$silver_percent_radio);
	
	
	//銅メダル　弱点があっても評価に含めてよいか
	$copper_percent_radio = make_radio_weak_point('copper',$_POST['copper_weak_flg']);
	$INPUTS[COPPERWEAKFLG] = array('result'=>'plane','value'=>$copper_percent_radio);
	
	
	//残念メダル　弱点があっても評価に含めてよいか
	$other_percent_radio = make_radio_weak_point('other',$_POST['other_weak_flg']);
	$INPUTS[OTHERWEAKFLG] = array('result'=>'plane','value'=>$other_percent_radio);
	
	//ユニットキー
	$newform = new form_parts();
	$newform->set_form_type("select");
	$newform->set_form_name("unit_num");
	$newform->set_form_array($unit_key_array);
	$newform->set_form_check($_POST['unit_num']);
	$unit_key_form .= $newform->make();
	$INPUTS[UNITKEY] = array('result'=>'plane','value'=>$unit_key_form);


	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"追加確認\">\n";
	$html .= "<input type=\"reset\" value=\"クリア\">\n";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"medal_setting_list\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n"; //add kimura 2019/07/12 生徒画面TOP改修
	$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";
	return $html;
}
function make_radio_weak_point($rank,$weak_point){
	
	global $L_WEAK_POINT;
	
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name($rank."_weak_flg");
	$newform->set_form_id($rank."_include");
	$newform->set_form_check($weak_point);
	$newform->set_form_value('0');
	$include = $newform->make();
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name($rank."_weak_flg");
	$newform->set_form_id($rank."_exclude");
	$newform->set_form_check($weak_point);
	$newform->set_form_value('1');
	$exclude = $newform->make();
	$percent_radio = $include . "<label for=\"".$rank."_include\">{$L_WEAK_POINT[0]}</label> / " . $exclude . "<label for=\"".$rank."_exclude\">{$L_WEAK_POINT[1]}</label>";
	
	return $percent_radio;
}

/**
 * 確認する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function check() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$mode = MODE;

	if (!$_POST['unit_num'] || $_POST['unit_num'] == 0) { $ERROR[] = "登録するユニットが未選択です。"; 
	
	} elseif(ACTION == "check" && MODE == "add") {
		$sql  = "SELECT * FROM ".T_UNIT_LIST_MEDAL_SETTING .
				" WHERE mk_flg = 0 AND unit_num='".$_POST['unit_num']."'";
		if ($result = $cdb->query($sql)) {
			$count = $cdb->num_rows($result);
		}
		if ($count > 0) { $ERROR[] = "選択されたユニットに対して既に設定済です"; }
	}
	
	if (isset($_POST['gold_percent']) && $_POST['gold_percent'] != ""){
		if(preg_match("/[^0-9]/",$_POST['gold_percent'])){
			$ERROR[] = "金メダル ドリル正解率(%以上)の数値が無効です。半角数字を入力してください";
		}elseif($_POST['gold_percent']>100){
			$ERROR[] = "金メダル ドリル正解率(%以上)の数値は100以下にしてください";
		}
	}else{
		$ERROR[] = "金メダル ドリル正解率(%以上)が未入力です"; 
	}
	if (isset($_POST['silver_percent']) && $_POST['silver_percent'] != ""){
		if(preg_match("/[^0-9]/",$_POST['silver_percent'])){
			$ERROR[] = "銀メダル ドリル正解率(%以上)の数値が無効です。半角数字を入力してください";
		}
	}else{
		$ERROR[] = "銀メダル ドリル正解率(%以上)が未入力です"; 
	}
	if (isset($_POST['copper_percent']) && $_POST['copper_percent'] != ""){
		if(preg_match("/[^0-9]/",$_POST['copper_percent'])){
			$ERROR[] = "銅メダル ドリル正解率(%以上)の数値が無効です。半角数字を入力してください";
		}
	}else{
		$ERROR[] = "銅メダル ドリル正解率(%以上)が未入力です"; 
	}
	if (isset($_POST['other_percent']) && $_POST['other_percent'] != ""){
		if(preg_match("/[^0-9]/",$_POST['other_percent'])){
			$ERROR[] = "残念メダル ドリル正解率(%以上)の数値が無効です。半角数字を入力してください";
		}
	}else{
		$ERROR[] = "残念メダル ドリル正解率(%以上)が未入力です"; 
	}

	if($_POST['gold_percent'] != "" && $_POST['silver_percent'] != ""){
		if($_POST['gold_percent'] <= $_POST['silver_percent']){
			$ERROR[] = "銀メダル ドリル正解率(%以上)は金メダル ドリル正解率(%以上)より小さな値にしてください。";
		}
	}
	if($_POST['silver_percent'] != "" && $_POST['copper_percent'] != ""){
		if($_POST['silver_percent'] <= $_POST['copper_percent']){
			$ERROR[] = "銅メダル ドリル正解率(%以上)は銀メダル ドリル正解率(%以上)より小さな値にしてください。";
		}
	}
	if($_POST['copper_percent'] != "" && $_POST['other_percent'] != ""){
		if($_POST['copper_percent'] <= $_POST['other_percent']){
			$ERROR[] = "残念メダル ドリル正解率(%以上)は銅メダル ドリル正解率(%以上)より小さな値にしてください。";
		}
	}
	
	
	// 弱点
	if (!isset($_POST['gold_weak_flg'])) {
		$ERROR[] = "金メダル弱点が未選択です。";
	}
	if (!isset($_POST['silver_weak_flg'])) {
		$ERROR[] = "銀メダル弱点が未選択です。";
	}
	if (!isset($_POST['copper_weak_flg'])) {
		$ERROR[] = "銅メダル弱点が未選択です。";
	}
	if (!isset($_POST['other_weak_flg'])) {
		$ERROR[] = "残念メダル弱点が未選択です。";
	}
	

	return $ERROR;
}
/**
 * 確認フォーム作成
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function check_html() {

	global $L_WEAK_POINT;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$TABLE = T_COURSE;
	$action = ACTION;

	if ($_POST) {
		foreach ($_POST as $key => $val) {
			if ($key == "action") {
				if (MODE == "add") { $val = "add"; }
				elseif (MODE == "詳細") { $val = "change"; }
			}
			$HIDDEN .= "<input type=\"hidden\" name=\"$key\" value=\"$val\">\n";
		}
	}

	//登録確認
	if ($action) {
		foreach ($_POST as $key => $val) { $$key = $val; }
	//削除時
	} else {
		$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"change\">\n";
		$sql = "SELECT * FROM ".T_UNIT_LIST_MEDAL_SETTING .
			" WHERE unit_num='{$_POST['unit_num']}' AND mk_flg = 0 LIMIT 1;";

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

	if (MODE != "削除") { $button = "登録"; } else { $button = "削除"; }
	$html = "<br>\n";
	$html .= "確認画面：以下の内容で{$button}してもよろしければ{$button}ボタンをクリックしてください。<br>\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(PRACTICE_MEDAL_FORM);

	if (!$medal_setting_id) { $medal_setting_id = "---"; }
	
	$sql  = "SELECT unit_key";
	$sql .= " FROM ".T_UNIT;
	$sql .= " WHERE unit_num = ".$unit_num;
	$sql .= " LIMIT 1";
	
	$result = $cdb->query($sql);
	$unit_list = $cdb->fetch_assoc($result);
	$unit_key = $unit_list['unit_key'];
	
	$INPUTS[MEDALID] = array('result'=>'plane','value'=>$medal_setting_id);
	$INPUTS[UNITKEY] = array('result'=>'plane','value'=>$unit_key);
	$INPUTS[GOLDPERCENT] = array('result'=>'plane','value'=>$gold_percent);
	$INPUTS[GOLDWEAKFLG] = array('result'=>'plane','value'=>$L_WEAK_POINT["$gold_weak_flg"]);
	$INPUTS[SILVERPERCENT] = array('result'=>'plane','value'=>$silver_percent);
	$INPUTS[SILVERWEAKFLG] = array('result'=>'plane','value'=>$L_WEAK_POINT["$silver_weak_flg"]);
	$INPUTS[COPPERPERCENT] = array('result'=>'plane','value'=>$copper_percent);
	$INPUTS[COPPERWEAKFLG] = array('result'=>'plane','value'=>$L_WEAK_POINT["$copper_weak_flg"]);
	$INPUTS[OTHERPERCENT] = array('result'=>'plane','value'=>$other_percent);
	$INPUTS[OTHERWEAKFLG] = array('result'=>'plane','value'=>$L_WEAK_POINT["$other_weak_flg"]);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
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
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"medal_setting_list\">\n";
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n"; //add kimura 2019/07/12 生徒画面TOP改修
		$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\">\n";
	}
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}



/**
 * DB変更
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function change() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$TABLE = T_UNIT_LIST_MEDAL_SETTING;
	$action = ACTION;

	if (MODE == "詳細") {
		$INSERT_DATA['medal_setting_id'] = $_POST['medal_setting_id'];
		$INSERT_DATA['course_num'] = $_POST['course_num']; //add kimura 2019/07/12 生徒画面TOP改修
		$INSERT_DATA['unit_num'] = $_POST['unit_num'];
		$INSERT_DATA['gold_percent'] = $_POST['gold_percent'];
		$INSERT_DATA['gold_weak_flg'] = $_POST['gold_weak_flg'];
		$INSERT_DATA['silver_percent'] = $_POST['silver_percent'];
		$INSERT_DATA['silver_weak_flg'] = $_POST['silver_weak_flg'];
		$INSERT_DATA['copper_percent'] = $_POST['copper_percent'];
		$INSERT_DATA['copper_weak_flg'] = $_POST['copper_weak_flg'];
		$INSERT_DATA['other_percent'] = $_POST['other_percent'];
		$INSERT_DATA['other_weak_flg'] = $_POST['other_weak_flg'];
		$INSERT_DATA['upd_syr_id'] = 'upd';
		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date '] = "now()";
		
		

		$where = " WHERE medal_setting_id='".$_POST['medal_setting_id']."' LIMIT 1;";

		$ERROR = $cdb->update($TABLE,$INSERT_DATA,$where);

	} elseif (MODE == "削除") {
		$INSERT_DATA['mk_flg'] = 1;
		$INSERT_DATA['mk_tts_id'] = $_SESSION['myid']['id'];
		$INSERT_DATA['mk_date '] = "now()";
		$where = " WHERE medal_setting_id='".$_POST['medal_setting_id']."' LIMIT 1;";

		$ERROR = $cdb->update($TABLE,$INSERT_DATA,$where);

	}

	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
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

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$TABLE = T_UNIT_LIST_MEDAL_SETTING;

	foreach ($_POST as $key => $val) {
		if (
			$key == "action" 
			|| $key =="lesson_num" 
			|| $key == "unit_num"
			|| $key == "course_num" //add kimura 2019/07/12 生徒画面TOP改修
		) { continue; }
		$INSERT_DATA[$key] = "$val";
	}
	$INSERT_DATA['course_num'] = $_POST['course_num']; //add kimura 2019/07/12 生徒画面TOP改修
	$INSERT_DATA['unit_num'] = $_POST['unit_num'];
	$INSERT_DATA['ins_syr_id'] = 'add';
	$INSERT_DATA['ins_tts_id'] = $_SESSION['myid']['id'];
	$INSERT_DATA['ins_date'] = "now()";
	$INSERT_DATA['upd_syr_id']	= "add"; //add kimura 2019/07/16 生徒画面TOP改修
	$INSERT_DATA['upd_date']	= "now()"; //add kimura 2019/07/16 生徒画面TOP改修 //プラクティスアップデート時に最終更新日を判断するため、新規登録の際もこのフィールドを満たします。
	$INSERT_DATA['upd_tts_id']	= $_SESSION['myid']['id']; //add kimura 2019/07/16 生徒画面TOP改修

	$ERROR = $cdb->insert($TABLE,$INSERT_DATA);


	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}
?>
