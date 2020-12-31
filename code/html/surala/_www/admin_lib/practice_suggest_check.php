<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * プラクティスステージ管理　サジェスト確認
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

	$html = select_course();

	if ($_POST['course_num']) {
		$html .= suggest_list();
	}

	return $html;
}


/**
 * コース選択
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array
 */
function select_course() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];


	$sql  = "SELECT course.course_num,course.course_name FROM ".T_COURSE." course";
	$sql  .= " INNER JOIN ".T_SERVICE_COURSE_LIST." scl ON course.course_num = scl.course_num AND scl.course_type = 1 AND scl.mk_flg = 0 ";
	$sql  .= " INNER JOIN ".T_SERVICE." sc ON scl.service_num = sc.service_num AND sc.setup_type_sub = 1 AND sc.mk_flg = 0 ";
	$sql .= " WHERE course.state = 0";
//	$sql .= " AND course.display = 1";
	$sql .= " ORDER BY course.list_num;";

	$course_list = array();
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			//すらら忍者算数は除く
			if($list['course_num'] == 12){
				continue;
			}
			$course_list[$list['course_num']] = $list['course_name'];
		}
	}
	if (empty($course_list)) {
		$html = "<br>\n";
		$html .= "コースが存在しません。設定してからご利用下さい。";
		return $html;
	} else {
		if (!$_POST['course_num']) { $selected = "selected"; } else { $selected = ""; }
		$couse_num_html .= "<option value=\"\" $selected>選択して下さい</option>\n";
		foreach ($course_list as $key => $value) {
			$course_num_ = $key;
			$course_name_ = $value;
			if ($_POST['course_num'] == $course_num_) { $selected = "selected"; } else { $selected = ""; }
			$couse_num_html .= "<option value=\"{$course_num_}\" $selected>{$course_name_}</option>\n";
		}
	}

	if ($_POST['course_num']) {
		$sql  = "SELECT * FROM ".T_STAGE.
				" WHERE course_num='".$_POST['course_num']."' AND state!='1' ORDER BY list_num;";
		if ($result = $cdb->query($sql)) {
			$max = $cdb->num_rows($result);
		}
		if (!$max) {
			$stage_num_html .= "<option value=\"\">--------</option>\n";
		} else {
			if (!$_POST['course_num']) { $selected = "selected"; } else { $selected = ""; }
			$stage_num_html .= "<option value=\"\" $selected>選択して下さい</option>\n";
			while ($list = $cdb->fetch_assoc($result)) {
				if ($_POST['stage_num'] == $list['stage_num']) { $selected = "selected"; } else { $selected = ""; }
				$stage_num_html .= "<option value=\"".$list['stage_num']."\" $selected>".$list['stage_name']."</option>\n";
			}
		}
	} else {
		$stage_num_html .= "<option value=\"\">--------</option>\n";
	}

	if ($_POST['stage_num']) {
		$sql  = "SELECT * FROM ".T_LESSON.
				" WHERE stage_num='".$_POST['stage_num']."' AND state!='1' ORDER BY list_num;";

		if ($result = $cdb->query($sql)) {
			$max = $cdb->num_rows($result);
		}
		if (!$max) {
			$lesson_num_html .= "<option value=\"\">ステージ内にLessonがありません。</option>\n";
		} else {
			if (!$_POST['lesson_num']) { $selected = "selected"; } else { $selected = ""; }
			$lesson_num_html .= "<option value=\"\" $selected>選択して下さい</option>\n";
			while ($list = $cdb->fetch_assoc($result)) {
				if ($_POST['lesson_num'] == $list['lesson_num']) { $selected = "selected"; } else { $selected = ""; }
				$lesson_num_html .= "<option value=\"".$list['lesson_num']."\" $selected>".$list['lesson_name']."</option>\n";
			}
		}
	} else {
		$lesson_num_html .= "<option value=\"\">--------</option>\n";
	}

	$html = "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"menu\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"set_course\">\n";
	$html .= "<table class=\"unit_form\">\n";
	$html .= "<tr class=\"unit_form_menu\">\n";
	$html .= "<td>コース</td>\n";
	$html .= "<td>ステージ</td>\n";
	$html .= "<td>Lesson</td>\n";
	$html .= "</tr>\n";
	$html .= "<tr class=\"unit_form_cell\">\n";
	$html .= "<td><select name=\"course_num\" onchange=\"submit_course();\">\n";
	$html .= $couse_num_html;
	$html .= "</select></td>\n";
	$html .= "<td><select name=\"stage_num\" onchange=\"submit_stage();\">\n";
	$html .= $stage_num_html;
	$html .= "</select></td>\n";
	$html .= "<td><select name=\"lesson_num\" onchange=\"submit();\">\n";
	$html .= $lesson_num_html;
	$html .= "</select></td>\n";
	$html .= "</tr>\n";
	$html .= "</table>\n";
	$html .= "</form><br>\n";

	if (!$_POST['course_num']) {
		$html .= "<br>\n";
		$html .= "コースを選択してください。<br>\n";
	}

	return $html;
}

/**
 * サジェスト一覧表示
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function suggest_list() {


	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
	$course_array = array();
	if(!empty($_POST['course_num'])){
		$course_array['course_num'] = $_POST['course_num'];
	}
	if(!empty($_POST['stage_num'])){
		$course_array['stage_num'] = $_POST['stage_num'];
	}
	if(!empty($_POST['lesson_num'])){
		$course_array['lesson_num'] = $_POST['lesson_num'];
	}

	$sql = get_suggest_list_sql($course_array);


	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
		if (!$max) {
			$html .= "<br>\n";
			$html .= "今現在登録されているユニットは有りません。<br>\n";
		} else {


			$html .= "<table class=\"suggest-table\">\n";
			$html .= "<tr class=\"course_suggest_menu\">\n";
			$html .= "<th class=\"course\">コース名</th>\n";
			$html .= "<th class=\"stage\">ステージ名</th>\n";
			$html .= "<th class=\"lesson\">レッスン名</th>\n";
			$html .= "<th class=\"unit\">ユニット名</th>\n";
			$html .= "<th class=\"tangen\">単元名</th>\n";
			$html .= "<th class=\"button\">サジェスト確認</th>\n";
			$html .= "<th class=\"button\">サジェスト確認(新)</th>\n";
			$html .= "<th class=\"button\">サジェスト確認(ピタドリ)</th>\n";
			$html .= "</tr>\n";

			while ($list = $cdb->fetch_assoc($result)) {
				$html .= "<tr>\n";

				$html .= "<td>".$list['course_name']."</td>\n";
				$html .= "<td>".$list['stage_name']."</td>\n";
				$html .= "<td>".$list['lesson_name']."</td>\n";
				$html .= "<td>".$list['unit_name']."</td>\n";
				if ($list['unit_name2']) {
					$list['unit_name2'] = preg_replace("/&lt;/", "<", $list['unit_name2']);
					$list['unit_name2'] = preg_replace("/&gt;/", ">", $list['unit_name2']);
				}
				$html .= "<td>".$list['unit_name2']."</td>\n";

				//結びつく別教科のユニットが存在した場合、ボタンを表示
				if($list['suggest_unit_num']){
					$html .= "<td class=\"suggest-button\">\n";
					$html .= "<input type=\"button\" value=\"サジェスト確認\" onclick=\"check_unit_comp_win_open('".$list['course_num']."', '".$list['unit_num']."','1')\">";
					$html .= "</td>\n";

					$html .= "<td class=\"suggest-button\">\n";
					$html .= "<input type=\"button\" value=\"サジェスト確認(新)\" onclick=\"check_unit_comp_win_open('".$list['course_num']."', '".$list['unit_num']."','2')\">";
					$html .= "</td>\n";

					$html .= "<td class=\"suggest-button\">\n";
					$html .= "<input type=\"button\" value=\"サジェスト確認(ピタドリ)\" onclick=\"check_unit_comp_win_open('".$list['course_num']."', '".$list['unit_num']."','3')\">";
					$html .= "</td>\n";
				}else{
					$html .= "<td></td><td></td><td></td>";
				}
				$html .= "</tr>\n";
			}
			$html .= "</table>\n";
		}
	}

	return $html;
}
/**
 * レッスン一覧とサジェスト情報を取得
 * @param array $course_array
 * @return string
 */
function get_suggest_list_sql($course_array){
	$cdb = $GLOBALS['cdb'];

	if(!empty($course_array['course_num'])){
		$where = " AND course.course_num = '" . $course_array['course_num'] . "'";
		$where1 = " AND unit.course_num = '" . $course_array['course_num'] . "'";
		foreach ($course_array as $key => $value) {
			if($key == 'lesson_num'){
				$where .= " AND lesson.lesson_num = '" . $value . "'";
				$where1 .= " AND unit.lesson_num = '" . $value . "'";
			}
			if($key == 'stage_num'){
				$where .= " AND stage.stage_num = '" . $value . "'";
				$where1 .= " AND unit.stage_num = '" . $value . "'";
			}
		}
		$sql = "DROP TEMPORARY TABLE IF EXISTS suggest_unit_tmp;";
		$cdb->exec_query($sql);
		$query = $sql.'<br>';

		$sql = "CREATE TEMPORARY TABLE suggest_unit_tmp (";
		$sql.= "  unit_num        INT(11) NOT NULL";
		$sql.= " ,suggest_unit_num    INT(11) NOT NULL";
		$sql.= ");";

		$cdb->exec_query($sql);
		$query .= $sql.'<br>';

		$sql  = "ALTER TABLE suggest_unit_tmp ADD INDEX unit_num (unit_num,suggest_unit_num);";

		$cdb->exec_query($sql);
		$query .= $sql.'<br>';

		//選択したユニットに紐づくサジェストユニットが有効な物のみテンポラリーテーブルに保存する。
		$sql = " SELECT " .
				"unit.unit_num" .
				",us.suggest_unit_num " .
				",course.course_num " .
				" FROM " . T_UNIT . " unit " .
				" INNER JOIN " . T_UNIT_SUGGEST . " us ON unit.unit_num = us.unit_num AND us.mk_flg='0' " .
				" INNER JOIN " . T_UNIT . " unit2 ON us.suggest_unit_num = unit2.unit_num " .
				" INNER JOIN " . T_LESSON . " lesson ON unit2.lesson_num = lesson.lesson_num" .
				" INNER JOIN " . T_STAGE . " stage ON lesson.stage_num = stage.stage_num" .
				" INNER JOIN " . T_COURSE . " course ON stage.course_num = course.course_num" .
				" INNER JOIN ".T_SERVICE_COURSE_LIST." scl ON course.course_num = scl.course_num AND scl.course_type = 1 AND scl.mk_flg = 0 ".
				" INNER JOIN ".T_SERVICE." sc ON scl.service_num = sc.service_num AND sc.setup_type_sub = 1 AND sc.mk_flg = 0 ".
				" WHERE 1 " .
				$where1 .
				"   AND unit.state = 0" .
				"   AND lesson.state = 0" .
				"   AND stage.state = 0" .
				"   AND course.state = 0" .
				"   AND unit2.state = 0" .
//				"   AND unit.display = 1".
//				"   AND lesson.display = 1" .
//				"   AND stage.display = 1" .
//				"   AND course.display = 1" .
//				"   AND unit2.display = 1".
				" ;";
		$query .= $sql.'<br>';
		$insert_sql = "";
		$c = 0;
		if ($result = $cdb->query($sql)) {
			while($list = $cdb->fetch_assoc($result)) {
				if($list['course_num'] != 12 && $list["unit_num"] != $list["suggest_unit_num"]){
					$insert_sql .= "('".$list["unit_num"]."','".$list["suggest_unit_num"]."'),";
					$c++;
				}
				if($c == 100){
					$insert_sql = rtrim($insert_sql,",");
					$sql = "INSERT INTO suggest_unit_tmp VALUES ".$insert_sql.";";
					$cdb->exec_query($sql);
					$query .= $sql.'<br>';
					$c = 0;
				}
			}
			if($c > 0){
				$insert_sql = rtrim($insert_sql,",");
				$sql = "INSERT INTO suggest_unit_tmp VALUES ".$insert_sql.";";
				$cdb->exec_query($sql);
				$query .= $sql.'<br>';
			}
		}

		$sql = " SELECT " .
				"unit.unit_num" .
				",unit.unit_name " .
				",unit.unit_name2 " .
				",lesson.lesson_num " .
				",lesson.lesson_name " .
				",stage.stage_num " .
				",stage.stage_name " .
				",stage.stage_key " .
				",course.course_num " .
				",course.course_name " .
				",su.suggest_unit_num " .
				" FROM " . T_UNIT . " unit " .
				" INNER JOIN " . T_LESSON . " lesson ON unit.lesson_num = lesson.lesson_num" .
				" INNER JOIN " . T_STAGE . " stage ON lesson.stage_num = stage.stage_num" .
				" INNER JOIN " . T_COURSE . " course ON stage.course_num = course.course_num" .
				" INNER JOIN ".T_SERVICE_COURSE_LIST." scl ON course.course_num = scl.course_num AND scl.course_type = 1 AND scl.mk_flg = 0 ".
				" INNER JOIN ".T_SERVICE." sc ON scl.service_num = sc.service_num AND sc.setup_type_sub = 1 AND sc.mk_flg = 0 ".
				" LEFT JOIN suggest_unit_tmp su ON unit.unit_num = su.unit_num " .
				" WHERE 1 " .
				$where .
				"   AND unit.state = 0" .
				"   AND lesson.state = 0" .
				"   AND stage.state = 0" .
				"   AND course.state = 0" .
//				"   AND unit.display = 1".
//				"   AND lesson.display = 1" .
//				"   AND stage.display = 1" .
//				"   AND course.display = 1" .
				"   GROUP BY unit.unit_num" .
				"   ORDER BY course.list_num,stage.list_num,lesson.list_num,unit.list_num" .
				" ;";
		$query .= $sql.'<br>';
	}else{
		$sql = "";
	}

	return $sql;
}
?>
