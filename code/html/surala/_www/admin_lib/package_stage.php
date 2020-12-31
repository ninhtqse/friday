<?
/**
 * ベンチャー・リンク　すらら
 *
 * 速習用プラクティスステージ管理　ステージ作成
 *
 * @author Azet
 */

/*
	//	add ookawara 2012/03/26
	course_num→pk_course_num
	corse_name→pk_corse_name
	m_course_num→m_pk_course_num
	stage_num→pk_stage_num
	stage_name→pk_stage_name
	m_stage_num→m_pk_stage_num
	c_stage_num→c_pk_stage_num

	//	stage_keyは何に使っているのか?
	Line190 340 541 569近辺
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

	if (ACTION == "check") {
		$ERROR = check();
	}
	if (!$ERROR) {
		if (ACTION == "add") { $ERROR = add(); }
		elseif (ACTION == "change") { $ERROR = change(); }
		elseif (ACTION == "del") { $ERROR = change(); }
		elseif (ACTION == "↑") { $ERROR = up(); }
		elseif (ACTION == "↓") { $ERROR = down(); }
	}

	if (MODE == "add") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= addform($ERROR); }
		} elseif (ACTION == "add") {
			if (!$ERROR) { $html .= select_course(); }
			else { $html .= addform($ERROR); }
		} else {
			$html .= addform($ERROR);
		}
	} elseif (MODE == "詳細") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= viewform($ERROR); }
		} elseif (ACTION == "change") {
			if (!$ERROR) { $html .= select_course(); }
			else { $html .= viewform($ERROR); }
		} else {
			$html .= viewform($ERROR);
		}
	} elseif (MODE == "削除") {
		if (ACTION == "check") {
			if (!$ERROR) { $html .= check_html(); }
			else { $html .= viewform($ERROR); }
		} elseif (ACTION == "change") {
			if (!$ERROR) { $html .= select_course(); }
			else { $html .= viewform($ERROR); }
		} else {
			$html .= check_html($L_COURSE,$L_STAGE);
		}
	} else {
		$html .= select_course();
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

	$sql  = "SELECT * FROM ".T_PACKAGE_COURSE.
			" WHERE mk_flg!='1' ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
		if ($max) {
			$html = "<br>\n";
			$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" name=\"menu\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"set_course\">\n";
			$html .= "<table class=\"stage_form\">\n";
			$html .= "<tr>\n";
			$html .= "<td class=\"stage_form_menu\">コース</td>\n";
			$html .= "</tr>";
			$html .= "<tr>\n";
			$html .= "<td class=\"stage_form_cell\"><select name=\"pk_course_num\" onchange=\"submit_pk_course();\">\n";

			if (!$_POST['pk_course_num']) { $selected = "selected"; } else { $selected = ""; }
			$html .= "<option value=\"\" $selected>選択して下さい</option>\n";

			while ($list = $cdb->fetch_assoc($result)) {
				$pk_course_num_ = $list['pk_course_num'];
				$pk_course_name_ = $list['pk_course_name'];
				$L_COURSE[$pk_course_num_] = $pk_course_name_;
				if ($_POST['pk_course_num'] == $pk_course_num_) { $selected = "selected"; } else { $selected = ""; }
				$html .= "<option value=\"{$pk_course_num_}\" $selected>{$pk_course_name_}(".$list['pk_course_num'].")</option>\n";
			}

			$html .= "</select></td>\n";
			$html .= "</tr>\n";
			$html .= "</table>\n";
			$html .= "</form>\n";
		}
		else {
			$html = "<br>\n";
			$html .= "コースを設定してからご利用下さい。<br>\n";
		}
	}

	if ($_POST['pk_course_num'] > 0) {
		$html .= stage_list($L_COURSE);
	} else {
		$html .= "<br>\n";
		$html .= "ステージを設定するコースを選択してください。<br>\n";
	}

	return $html;
}

/**
 * ステージ選択一覧作る機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $L_COURSE
 * @return string HTML
 */
function stage_list($L_COURSE) {

	global $L_WRITE_TYPE,$L_CATEGORY_TYPE,$L_DISPLAY;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//list($me_num,$onetime,$manager_level,$belong_num,$authority) = explode("<>",$_SESSION[myid]);
	if ($authority) { $L_AUTHORITY = explode("::",$authority); }

	if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__add",$_SESSION['authority'])===FALSE)) {

		$sql  = "SELECT category_type FROM ".T_PACKAGE_COURSE.
				" WHERE mk_flg!='1' AND pk_course_num='".$_POST['pk_course_num']."'";

		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
		}
		if ($list['category_type'] != 3) {
			$html .= "<br>\n";
			$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"add\">\n";
			$html .= "<input type=\"hidden\" name=\"pk_course_num\" value=\"$_POST[pk_course_num]\">\n";
			$html .= "<input type=\"submit\" value=\"ステージ新規登録\">\n";
			$html .= "</form>\n";
		} else {
			$html .= "<br>\n";
			$html .= "このカテゴリはユニットキー設定専用です。<br>\n";
			return $html;
		}
	}

	$sql  = "SELECT * FROM ".T_PACKAGE_STAGE.
			" WHERE mk_flg!='1' AND pk_course_num='$_POST[pk_course_num]' ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
		if (!$max) {
			$html .= "<br>\n";
			$html .= "今現在登録されているステージは有りません。<br>\n";
			return $html;
		}
		$html .= "<br>\n";
		$html .= "<table class=\"stage_form\">\n";
		$html .= "<tr class=\"stage_form_menu\">\n";
		if (!ereg("practice__view",$authority)
			&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__sort",$_SESSION['authority'])===FALSE))
		) {
			$html .= "<th>↑</th>\n";
			$html .= "<th>↓</th>\n";
		}
		$html .= "<th>登録番号</th>\n";
		$html .= "<th>コース名</th>\n";
		$html .= "<th>ステージ名</th>\n";
		$html .= "<th>備考</th>\n";
		$html .= "<th>カテゴリタイプ</th>\n";
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
		while ($list = $cdb->fetch_assoc($result)) {

			$pk_stage_num_ = $list['pk_stage_num'];
			$list_num_ = $list['list_num'];
			$LINE[$list_num_] = $pk_stage_num_;
			foreach ($list AS $KEY => $VAL) {
				$KEY .= "_";
				$$KEY = $VAL;
			}

			if ($clear_stage_key_) {
				$clear_stage_key_ = eregi_replace("^:|:$","",$clear_stage_key_);
			}

			$up_submit = $down_submit = "&nbsp;";
			if ($i != 1) { $up_submit = "<input type=\"submit\" name=\"action\" value=\"↑\">\n"; }
			if ($i != $max) { $down_submit = "<input type=\"submit\" name=\"action\" value=\"↓\">\n"; }

			$html .= "<tr class=\"stage_form_cell\">\n";
			$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"pk_course_num\" value=\"{$_POST[pk_course_num]}\">\n";
			$html .= "<input type=\"hidden\" name=\"pk_stage_num\" value=\"{$pk_stage_num_}\">\n";
			if (!ereg("practice__view",$authority)
				&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__sort",$_SESSION['authority'])===FALSE))
			) {
				$html .= "<td>{$up_submit}</td>\n";
				$html .= "<td>{$down_submit}</td>\n";
			}
			$html .= "<td>{$pk_stage_num_}</td>\n";
			$html .= "<td>{$L_COURSE[$pk_course_num_]}</td>\n";
			$html .= "<td>{$pk_stage_name_}</td>\n";
			$html .= "<td>{$remarks_}</td>\n";
			$html .= "<td>{$L_CATEGORY_TYPE[$category_type_]}</td>\n";
			$html .= "<td>{$L_DISPLAY[$display_]}</td>\n";
			if (!ereg("practice__view",$authority)
				&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__view",$_SESSION['authority'])===FALSE))
			) {
				$html .= "<td><input type=\"submit\" name=\"mode\" value=\"詳細\"></td>\n";
			}
			if (!ereg("practice__del",$authority)
				&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__del",$_SESSION['authority'])===FALSE))
			) {
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
 * 新規登録フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function addform($ERROR) {

	global $L_WRITE_TYPE,$L_CATEGORY_TYPE,$L_DISPLAY,$L_CHANGE_TYPE;		// update oda 2013/11/15 $L_CHANGE_TYPE追加

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//	コース名
	$sql  = "SELECT * FROM ".T_PACKAGE_COURSE.
			" WHERE pk_course_num='$_POST[pk_course_num]' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$pk_course_name_ = $list['pk_course_name'];
	}

	$html .= "<br>\n";
	$html .= "新規登録フォーム<br>\n";

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\" />\n";
	$html .= "<input type=\"hidden\" name=\"pk_course_num\" value=\"$_POST[pk_course_num]\">\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(PACKAGE_STAGE_FORM);

//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	$INPUTS[STAGENUM] = array('result'=>'plane','value'=>"---");
	$INPUTS[COURSENAME] = array('result'=>'plane','value'=>$pk_course_name_);
	$INPUTS[STAGENAME] = array('type'=>'text','name'=>'pk_stage_name','value'=>$_POST['pk_stage_name']);
	$INPUTS[CATEGORYTYPE] = array('type'=>'select','name'=>'category_type','array'=>$L_CATEGORY_TYPE,'check'=>$_POST['category_type']);
	$INPUTS[REMARKS] = array('type'=>'textarea','name'=>'remarks','cols'=>'50','rows'=>'5','value'=>$_POST['remarks']);

	//del start kimura 2018/11/14 すらら英単語 //フィールド未使用につき入力フォーム削除
	// add start oda 2013/11/15
	// 生徒画面の看板表示変更ラジオボタン生成
	// if ($_POST['signboard_flg'] == "") {
	// 	$_POST['signboard_flg'] = "0";
	// }
	// $newform = new form_parts();
	// $newform->set_form_type("radio");
	// $newform->set_form_name("signboard_flg");
	// $newform->set_form_id("signboard_flg");
	// $newform->set_form_check($_POST['signboard_flg']);
	// $newform->set_form_value('1');
	// $radio1 = $newform->make();

	// $newform = new form_parts();
	// $newform->set_form_type("radio");
	// $newform->set_form_name("signboard_flg");
	// $newform->set_form_id("unsignboard_flg");
	// $newform->set_form_check($_POST['signboard_flg']);
	// $newform->set_form_value('0');
	// $radio2 = $newform->make();

	// $signboard_value = $radio1 . "<label for=\"signboard_flg_display\">".$L_CHANGE_TYPE[1]."</label> / " . $radio2 . "<label for=\"signboard_flg_updisplay\">".$L_CHANGE_TYPE[0]."</label>";
	// $INPUTS[SIGNBOARDFLG] = array('result'=>'plane','value'=>$signboard_value);

	// $INPUTS[SIGNBOARDNAME]  = array('type'=>'text','name'=>'signboard_name','value'=>$_POST['signboard_name']);
	// add end oda 2013/11/15
	//del end   kimura 2018/11/14 すらら英単語 //フィールド未使用につき入力フォーム削除

	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("display");
	$newform->set_form_id("display");
	$newform->set_form_check($_POST['display']);
	$newform->set_form_value('1');
	$display = $newform->make();
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("display");
	$newform->set_form_id("undisplay");
	$newform->set_form_check($_POST['display']);
	$newform->set_form_value('2');
	$undisplay = $newform->make();
	$display = $display . "<label for=\"display\">{$L_DISPLAY[1]}</label> / " . $undisplay . "<label for=\"undisplay\">{$L_DISPLAY[2]}</label>";
	$INPUTS[DISPLAY] = array('result'=>'plane','value'=>$display);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"追加確認\" />\n";
	$html .= "<input type=\"reset\" value=\"クリア\" />\n";
	$html .= "</form>\n";
/*
	$html .= "<b>クリアーステージキーについて</b><br>\n";
	$html .= "・学習する為にクリアーしなければいけないステージが有る場合<br>\n";
	$html .= "　クリアーしなければいけないステージのステージキーを入力してください。<br>\n";
	$html .= "・入力されていない場合は表示順番に学習が進められます。<br>\n";
	$html .= "・自由に学習開始できるステージが有る場合には、「start」と入力してください。<br>\n";
	$html .= "・学習する為に必要なステージが複数有る場合は、「::」で区切ってください。<br>\n";
	$html .= "　例：s1::s3::s5<br>\n";
*/
	$html .= "<br>\n";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"stage_list\" />\n";
	$html .= "<input type=\"hidden\" name=\"pk_course_num\" value=\"$_POST[pk_course_num]\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\" />\n";
	$html .= "</form>\n";
	return $html;
}

/**
 *
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function viewform($ERROR) {

	global $L_WRITE_TYPE,$L_CATEGORY_TYPE,$L_DISPLAY,$L_CHANGE_TYPE;		// update oda 2013/11/15 $L_CHANGE_TYPE追加

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	//	コース名
	$sql  = "SELECT * FROM ".T_PACKAGE_COURSE.
			" WHERE pk_course_num='$_POST[pk_course_num]' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$pk_course_name_ = $list['pk_course_name'];
	}

	if ($action) {
		foreach ($_POST as $key => $val) { $$key = $val; }
	} else {
		$sql = "SELECT * FROM ".T_PACKAGE_STAGE.
			" WHERE pk_stage_num='$_POST[pk_stage_num]' AND mk_flg!='1' LIMIT 1;";
		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);
		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}
		foreach ($list as $key => $val) {
			$$key = replace_decode($val);
		}

		if ($clear_stage_key) {
			$clear_stage_key = eregi_replace("^:|:$","",$clear_stage_key);
		}
		$set_category_type = $category_type;	//	add ookawara 2012/05/30
	}

	$html = "<br>\n";
	$html .= "詳細画面<br>\n";

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\" />\n";
	$html .= "<input type=\"hidden\" name=\"pk_course_num\" value=\"$pk_course_num\" />\n";
	$html .= "<input type=\"hidden\" name=\"pk_stage_num\" value=\"$pk_stage_num\" />\n";
	$html .= "<input type=\"hidden\" name=\"set_category_type\" value=\"$set_category_type\">\n";	//	add ookawara 2012/05/30

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(PACKAGE_STAGE_FORM);

	if (!$unit_num) { $unit_num = "---"; }
	$INPUTS[STAGENUM] = array('result'=>'plane','value'=>$pk_stage_num);
	$INPUTS[COURSENAME] = array('result'=>'plane','value'=>$pk_course_name_);
	$INPUTS[STAGENAME] = array('type'=>'text','name'=>'pk_stage_name','value'=>$pk_stage_name);
	$INPUTS[CATEGORYTYPE] = array('type'=>'select','name'=>'category_type','array'=>$L_CATEGORY_TYPE,'check'=>$category_type);
	$remarks = ereg_replace("&lt;","<",$remarks);
	$remarks = ereg_replace("&gt;",">",$remarks);
	$INPUTS[REMARKS] = array('type'=>'textarea','name'=>'remarks','cols'=>'50','rows'=>'5','value'=>$remarks);

	//del start kimura 2018/11/14 すらら英単語 //フィールド未使用につき入力フォーム削除
	// add start oda 2013/11/15
	// 生徒画面の看板表示変更ラジオボタン生成
	// $newform = new form_parts();
	// $newform->set_form_type("radio");
	// $newform->set_form_name("signboard_flg");
	// $newform->set_form_id("signboard_flg");
	// $newform->set_form_check($signboard_flg);
	// $newform->set_form_value('1');
	// $radio1 = $newform->make();

	// $newform = new form_parts();
	// $newform->set_form_type("radio");
	// $newform->set_form_name("signboard_flg");
	// $newform->set_form_id("unsignboard_flg");
	// $newform->set_form_check($signboard_flg);
	// $newform->set_form_value('0');
	// $radio2 = $newform->make();

	// $signboard_value = $radio1 . "<label for=\"signboard_flg_display\">".$L_CHANGE_TYPE[1]."</label> / " . $radio2 . "<label for=\"signboard_flg_updisplay\">".$L_CHANGE_TYPE[0]."</label>";
	// $INPUTS[SIGNBOARDFLG] = array('result'=>'plane','value'=>$signboard_value);

	// $INPUTS[SIGNBOARDNAME]  = array('type'=>'text','name'=>'signboard_name','value'=>$signboard_name);
	// add end oda 2013/11/15
	//del end   kimura 2018/11/14 すらら英単語 //フィールド未使用につき入力フォーム削除

	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("display");
	$newform->set_form_id("display");
	$newform->set_form_check($display);
	$newform->set_form_value('1');
	$male = $newform->make();
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("display");
	$newform->set_form_id("undisplay");
	$newform->set_form_check($display);
	$newform->set_form_value('2');
	$female = $newform->make();
	$display = $male . "<label for=\"display\">{$L_DISPLAY[1]}</label> / " . $female . "<label for=\"undisplay\">{$L_DISPLAY[2]}</label>";
	$INPUTS[DISPLAY] = array('result'=>'plane','value'=>$display);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<input type=\"submit\" value=\"変更確認\">";
	$html .= "<input type=\"reset\" value=\"クリア\">";
	$html .= "</form>\n";
/*
	$html .= "<b>クリアーステージキーについて</b><br>\n";
	$html .= "・学習する為にクリアーしなければいけないステージが有る場合<br>\n";
	$html .= "　クリアーしなければいけないステージのステージキーを入力してください。<br>\n";
	$html .= "・入力されていない場合は表示順番に学習が進められます。<br>\n";
	$html .= "・自由に学習開始できるステージが有る場合には、「start」と入力してください。<br>\n";
	$html .= "・学習する為に必要なステージが複数有る場合は、「::」で区切ってください。<br>\n";
	$html .= "　例：s1::s3::s5<br>\n";
	$html .= "<br>\n";
*/
	$html .= "<br>\n";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"unit_list\" />\n";
	$html .= "<input type=\"hidden\" name=\"pk_course_num\" value=\"$_POST[pk_course_num]\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}

/**
 * POSTデータを判断する機能
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

	if (!$_POST['pk_course_num']) { $ERROR[] = "登録するステージのコース情報が確認できません。"; }

	if (!$_POST['pk_stage_name']) { $ERROR[] = "ステージ名が未入力です。"; }
	elseif (!$ERROR) {
		if ($mode == "add") {
			$sql  = "SELECT * FROM ".T_PACKAGE_STAGE.
					" WHERE mk_flg!='1' AND pk_course_num='$_POST[pk_course_num]' AND pk_stage_name='$_POST[pk_stage_name]'";
		} else {
			$sql  = "SELECT * FROM ".T_PACKAGE_STAGE.
					" WHERE mk_flg!='1' AND pk_course_num='$_POST[pk_course_num]' AND pk_stage_num!='$_POST[pk_stage_num]'" .
					" AND pk_stage_name='$_POST[pk_stage_name]'";
		}
		if ($result = $cdb->query($sql)) {
			$count = $cdb->num_rows($result);
		}
		if ($count > 0) { $ERROR[] = "入力されたステージ名は既に登録されております。"; }
	}
	if (!$_POST['category_type']) {
		$ERROR[] = "カテゴリタイプが未選択です。";
	//	add ookawara 2012/05/30 start
	} elseif ($_POST['set_category_type'] == 3 && $_POST['category_type'] != 3) {
		$count = 0;
		$sql  = "SELECT count(*) AS count FROM ".T_PACKAGE_UNIT_LIST.
				" WHERE pk_course_num='".$_POST['pk_course_num']."'".
				" AND pk_stage_num='".$_POST['pk_stage_num']."'".
				" AND mk_flg!='1';";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$count = $list['count'];
		}
		if ($count > 0) {
			$ERROR[] = "下層にユニットキーが登録されている為カテゴリータイプを変更出来ません。";
		}
	} elseif ($_POST['set_category_type'] > 0 && $_POST['set_category_type'] != 3 && $_POST['category_type'] == 3) {
		include(LOG_DIR . "admin_lib/package_lib.php");
		pakage_cat_check($ERROR, $_POST['pk_course_num'], $_POST[pk_stage_num]);
	//	add ookawara 2012/05/30 end
	}

	if ($_POST['signboard_flg'] == "1" && !$_POST['signboard_name']) {			// add oda 2013/11/15
		$ERROR[] = "生徒画面の看板表示内容が未入力です。";
	}

	if (!$_POST['display']) { $ERROR[] = "表示・非表示が未選択です。"; }
	return $ERROR;
}


/**
 *
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function check_html() {

	global $L_WRITE_TYPE,$L_CATEGORY_TYPE,$L_DISPLAY,$L_CHANGE_TYPE;		// update oda 2013/11/15 $L_CHANGE_TYPE追加

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	if ($_POST) {
		foreach ($_POST as $key => $val) {
			if ($key == "action") {
				if (MODE == "add") { $val = "add"; }
				elseif (MODE == "詳細") { $val = "change"; }
			}
			$val = mb_convert_kana($val,"asKV","UTF-8");
			$HIDDEN .= "<input type=\"hidden\" name=\"$key\" value=\"$val\" />\n";
		}
	}

	if ($action) {
		foreach ($_POST as $key => $val) {
			$val = mb_convert_kana($val,"asKV","UTF-8");
			$$key = $val;
		}
	} else {
		$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"change\" />\n";
		$sql = "SELECT * FROM ".T_PACKAGE_STAGE.
			" WHERE pk_stage_num='{$_POST[pk_stage_num]}' AND mk_flg!='1' LIMIT 1;";

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

	//	コース名
	$sql  = "SELECT * FROM ".T_PACKAGE_COURSE.
			" WHERE pk_course_num='$pk_course_num' LIMIT 1;";

	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$pk_course_name_ = $list['pk_course_name'];
	}

	if (MODE != "削除") { $button = "登録"; } else { $button = "削除"; }
	$html = "<br>\n";
	$html .= "確認画面：以下の内容で{$button}してもよろしければ{$button}ボタンをクリックしてください。<br>\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(PACKAGE_STAGE_FORM);

//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	if (!$pk_stage_num) { $pk_stage_num = "---"; }
	$INPUTS[STAGENUM] = array('result'=>'plane','value'=>$pk_stage_num);
	$INPUTS[COURSENAME] = array('result'=>'plane','value'=>$pk_course_name_);
	$INPUTS[STAGENAME] = array('result'=>'plane','value'=>$pk_stage_name);
	$INPUTS[CATEGORYTYPE] = array('result'=>'plane','value'=>$L_CATEGORY_TYPE[$category_type]);
	$remarks = ereg_replace("&lt;","<",$remarks);
	$remarks = ereg_replace("&gt;",">",$remarks);
	$INPUTS[REMARKS] = array('result'=>'plane','value'=>$remarks);
	$INPUTS[SIGNBOARDFLG]  = array('result'=>'plane','value'=>$L_CHANGE_TYPE[$signboard_flg]);			// add oda 2013/11/15
	$INPUTS[SIGNBOARDNAME] = array('result'=>'plane','value'=>$signboard_name);							// add oda 2013/11/15
	$INPUTS[DISPLAY] = array('result'=>'plane','value'=>$L_DISPLAY[$display]);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" style=\"float:left\">\n";
	$html .= "<input type=\"hidden\" name=\"pk_course_num\" value=\"$pk_course_num\" />\n";
	$html .= $HIDDEN;
	$html .= "<input type=\"submit\" value=\"$button\" />\n";
	$html .= "</form>";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";

	if ($action) {
		$HIDDEN2 = explode("\n",$HIDDEN);
		foreach ($HIDDEN2 as $key => $val) {
			if (ereg("name=\"action\"",$val)) {
				$HIDDEN2[$key] = "<input type=\"hidden\" name=\"action\" value=\"back\" />";
				break;
			}
		}
		$HIDDEN2 = implode("\n",$HIDDEN2);

		$html .= $HIDDEN2;
	} else {
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"unit_list\" />\n";
	}
	$html .= "<input type=\"hidden\" name=\"pk_course_num\" value=\"$pk_course_num\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\" />\n";
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

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	foreach ($_POST as $key => $val) {
		if ($key == "action") { continue; }
		elseif ($key == "clear_stage_key" && $val) { $val = ":".$val.":"; }
		$INSERT_DATA[$key] = "$val";
	}
	$INSERT_DATA[ins_date] = "now()";
	$INSERT_DATA[ins_tts_id] 		= $_SESSION['myid']['id'];
	$INSERT_DATA[upd_date] = "now()";
	$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];

	$ERROR = $cdb->insert(T_PACKAGE_STAGE,$INSERT_DATA);

	if (!$ERROR) {
		$pk_stage_num = $cdb->insert_id();
		$INSERT_DATA[list_num] = $pk_stage_num;
		$where = " WHERE pk_stage_num='$pk_stage_num' LIMIT 1;";

		$ERROR = $cdb->update(T_PACKAGE_STAGE,$INSERT_DATA,$where);
	}

	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
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

	$action = ACTION;

	if (MODE == "詳細") {
		$sql  = "SELECT stage_key FROM ".T_PACKAGE_STAGE.
				" WHERE pk_stage_num='$_POST[pk_stage_num]' LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$check_stage_key = $list['stage_key'];
		}

		foreach ($_POST as $key => $val) {
			if ($key == "action") { continue; }
			elseif ($key == "set_category_type") { continue; } //	add ookawara 2012/05/30
			elseif ($key == "clear_stage_key" && $val) { $val = ":".$val.":"; }
			elseif ($key == "stage_key") { $$key = $val; }
			$INSERT_DATA[$key] = "$val";
		}
		$INSERT_DATA[upd_syr_id] = "updateline";	//	add ookawara 2012/03/27
		$INSERT_DATA[upd_date] = "now()";
		$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];
		$where = " WHERE pk_stage_num='$_POST[pk_stage_num]' LIMIT 1;";

		$ERROR = $cdb->update(T_PACKAGE_STAGE,$INSERT_DATA,$where);
	} elseif (MODE == "削除") {
		$INSERT_DATA[display] = 2;
		$INSERT_DATA[mk_flg] = 1;
		$INSERT_DATA[mk_tts_id] 		= $_SESSION['myid']['id'];
		$INSERT_DATA[mk_date] = "now()";	//	add ookawara 2012/03/27
		$where = " WHERE pk_stage_num='$_POST[pk_stage_num]' LIMIT 1;";

		$ERROR = $cdb->update(T_PACKAGE_STAGE,$INSERT_DATA,$where);
	}

	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}

/**
 * PACKAGE_STAGEを上がる機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function up() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	$sql  = "SELECT * FROM ".T_PACKAGE_STAGE.
			" WHERE pk_stage_num='$_POST[pk_stage_num]' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$m_pk_course_num = $list['pk_course_num'];
		$m_pk_stage_num = $list['pk_stage_num'];
		$m_list_num = $list['list_num'];
	}
	if (!$m_pk_stage_num || !$m_list_num) { $ERROR[] = "移動するステージ情報が取得できません。"; }

	if (!$ERROR) {
		$sql  = "SELECT * FROM ".T_PACKAGE_STAGE.
				" WHERE mk_flg!='1' AND pk_course_num='$m_pk_course_num' AND list_num<'$m_list_num'" .
				" ORDER BY list_num DESC LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$c_pk_stage_num = $list['pk_stage_num'];
			$c_list_num = $list['list_num'];
		}
	}
	if (!$c_pk_stage_num || !$c_list_num) { $ERROR[] = "移動されるステージ情報が取得できません。"; }
	if (!$ERROR) {
		$INSERT_DATA[list_num] = $c_list_num;
		$INSERT_DATA[upd_syr_id] = "upline";	//	add ookawara 2012/03/27
		$INSERT_DATA[upd_date] = "now()";
		$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];
		$where = " WHERE pk_stage_num='$m_pk_stage_num' LIMIT 1;";

		$ERROR = $cdb->update(T_PACKAGE_STAGE,$INSERT_DATA,$where);
	}

	if (!$ERROR) {
		$INSERT_DATA[list_num] = $m_list_num;
		$INSERT_DATA[upd_syr_id] = "upline";	//	add ookawara 2012/03/27
		$INSERT_DATA[upd_date] = "now()";
		$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];
		$where = " WHERE pk_stage_num='$c_pk_stage_num' LIMIT 1;";

		$ERROR = $cdb->update(T_PACKAGE_STAGE,$INSERT_DATA,$where);
	}

	return $ERROR;
}

/**
 * PACKAGE_STAGEを下がる機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function down() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	$sql  = "SELECT * FROM ".T_PACKAGE_STAGE.
			" WHERE pk_stage_num='$_POST[pk_stage_num]' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$m_pk_course_num = $list['pk_course_num'];
		$m_pk_stage_num = $list['pk_stage_num'];
		$m_list_num = $list['list_num'];
	}
	if (!$m_pk_stage_num || !$m_list_num) { $ERROR[] = "移動するステージ情報が取得できません。"; }

	if (!$ERROR) {
		$sql  = "SELECT * FROM ".T_PACKAGE_STAGE.
				" WHERE mk_flg!='1' AND pk_course_num='$m_pk_course_num' AND list_num>'$m_list_num'" .
				" ORDER BY list_num LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$c_pk_stage_num = $list['pk_stage_num'];
			$c_list_num = $list['list_num'];
		}
	}
	if (!$c_pk_stage_num || !$c_list_num) { $ERROR[] = "移動されるステージ情報が取得できません。"; }
	if (!$ERROR) {
		$INSERT_DATA[list_num] = $c_list_num;
		$INSERT_DATA[upd_syr_id] = "downline";	//	add ookawara 2012/03/27
		$INSERT_DATA[upd_date] = "now()";
		$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];
		$where = " WHERE pk_stage_num='$m_pk_stage_num' LIMIT 1;";

		$ERROR = $cdb->update(T_PACKAGE_STAGE,$INSERT_DATA,$where);
	}

	if (!$ERROR) {
		$INSERT_DATA[list_num] = $m_list_num;
		$INSERT_DATA[upd_syr_id] = "downline";	//	add ookawara 2012/03/27
		$INSERT_DATA[upd_date] = "now()";
		$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];
		$where = " WHERE pk_stage_num='$c_pk_stage_num' LIMIT 1;";

		$ERROR = $cdb->update(T_PACKAGE_STAGE,$INSERT_DATA,$where);
	}

	return $ERROR;
}
?>
