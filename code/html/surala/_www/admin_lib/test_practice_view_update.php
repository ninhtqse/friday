<?
/**
 * ベンチャー・リンク　すらら
 *
 * テスト用プラクティス管理　検証VIEW更新
 *
 * 履歴
 * 2012/04/13 初期設定
 *
 * @author Azet
 */

// ookawara

/**
 * HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[L07]テストを受ける.
 *
 * @author Azet
 * @return string HTML
 */
function start() {

	if (ACTION == "view_update") {
		$ERROR = view_upudate();
	}

	$html .= defo_html($ERROR);

	return $html;
}

/**
 * 既定HTMLを作成機能
 *
 * AC:[A]管理者 UC1:[L07]テストを受ける.
 *
 * @author Azet
 * @param array $ERROR
 */
function defo_html($ERROR) {

	if ($ERROR) {
		$html .= ERROR($ERROR);
		$html .= "更新失敗しました。<br>\n";
	} elseif (ACTION == "view_update") {
		$html .= "<br>\n";
		$html .= "更新完了しました。<br>\n";
	}

	$html .= "<br>";
	$html .= "<form actiion=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"view_update\">\n";
	$html .= "<td><input type=\"submit\" value=\"検証VIEW更新\" onclick=\"return confirm('検証サーバーの「vw_problem」を更新してもよろしいでしょうか?')\">\n";
	$html .= "</form>\n";

	return $html;
}


/**
 * 検証サーバーVIEW更新
 * @author Azet
 */
function view_upudate() {
	global $L_DB,$L_TSTC_DB;

	$QUERYS = array();

	$QUERYS['0'] = "DROP VIEW IF EXISTS `vw_problem`;";

	$QUERYS['1'] = "CREATE VIEW `vw_problem` AS
 select
	`p`.`problem_num` AS `problem_num`,
	`p`.`course_num` AS `course_num`,
	`mpa`.`standard_time` AS `standard_time`,
	`p`.`problem_type` AS `problem_type`,
	`p`.`form_type` AS `form_type`,
	`p`.`question` AS `question`,
	`p`.`problem` AS `problem`,
	`p`.`voice_data` AS `voice_data`,
	`p`.`hint` AS `hint`,
	`p`.`explanation` AS `explanation`,
	`p`.`parameter` AS `parameter`,
	`p`.`first_problem` AS `first_problem`,
	`p`.`latter_problem` AS `latter_problem`,
	`p`.`selection_words` AS `selection_words`,
	`p`.`correct` AS `correct`,
	`p`.`option1` AS `option1`,
	`p`.`option2` AS `option2`,
	`p`.`option3` AS `option3`,
	`p`.`option4` AS `option4`,
	`p`.`option5` AS `option5`,
	`p`.`display` AS `display`,
	`p`.`state` AS `state`,
	`mpa`.`test_difficulty` AS `test_difficulty`,
	`mpa`.`usr_bko` AS `usr_bko`,
	`mpa`.`mk_flg` AS `mk_flg`,
	`mpa`.`mk_tts_id` AS `mk_tts_id`,
	`mpa`.`mk_date` AS `mk_date`,
	`mpa`.`upd_syr_id` AS `upd_syr_id`,
	`mpa`.`upd_tts_id` AS `upd_tts_id`,
	`mpa`.`upd_date` AS `upd_date`,
	`mpa`.`ins_syr_id` AS `ins_syr_id`,
	`mpa`.`ins_tts_id` AS `ins_tts_id`,
	`mpa`.`ins_date` AS `ins_date`,
	`mpa`.`sys_bko` AS `sys_bko`
 from (`problem` `p` join `ms_problem_attribute` `mpa` on((`mpa`.`block_num` = `p`.`block_num`)));";

	$QUERYS['2'] = "DROP VIEW IF EXISTS `cousu_name_list_view`;";

	$QUERYS['3'] = "CREATE VIEW `cousu_name_list_view` AS select `c`.`course_name` AS `course_name`,`s`.`stage_name` AS `stage_name`,`l`.`lesson_name` AS `lesson_name`,`u`.`unit_name` AS `unit_name`,`c`.`course_num` AS `course_num`,`s`.`stage_num` AS `stage_num`,`l`.`lesson_num` AS `lesson_num`,`u`.`unit_num` AS `unit_num`,`b`.`block_num` AS `block_num`,`u`.`unit_key` AS `unit_key` from ((((`course` `c` left join `stage` `s` on(((`s`.`course_num` = `c`.`course_num`) and (`s`.`display` = _utf8'1') and (`s`.`state` <> _utf8'1')))) left join `lesson` `l` on(((`l`.`stage_num` = `s`.`stage_num`) and (`l`.`display` = _utf8'1') and (`l`.`state` <> _utf8'1')))) left join `unit` `u` on(((`u`.`lesson_num` = `l`.`lesson_num`) and (`u`.`display` = _utf8'1') and (`u`.`state` <> _utf8'1')))) left join `block` `b` on(((`b`.`unit_num` = `u`.`unit_num`) and (`b`.`display` = _utf8'1') and (`b`.`state` <> _utf8'1') and (`c`.`display` = _utf8'1') and (`c`.`state` <> _utf8'1')))) order by `c`.`list_num`,`s`.`list_num`,`l`.`list_num`,`u`.`list_num`,`b`.`list_num`;";

	if (!$QUERYS) {
		return;
	}

	if (!$L_TSTC_DB) {
		return;
	}

	foreach ($L_TSTC_DB AS $KEY => $select_db_name) {

		//	閲覧DB接続
		$select_db = $L_DB[$select_db_name];

		$connect_db = new connect_db();
		$connect_db->set_db($select_db);
		$ERROR = $connect_db->set_connect_db();
		if ($ERROR) {
			return $ERROR;
		}

		foreach ($QUERYS AS $key => $sql) {
			$sql = trim($sql);
			if (!$connect_db->exec_query($sql)) {
				$ERROR[] = "SQL ERROR:: ".$sql."<br>\n";
			}
		}

		//	閲覧DB切断
		$connect_db->close();
	}

	return $ERROR;
}
?>