<?php
/**
 * ベンチャー・リンク　すらら
 *
 * core　生徒システム
 * SNS連携処理
 *
 * 履歴
 * 2013/02/05 初期設定
 *
 * @author Azet
 */


/**
 * メインルーチン
 *
 * AC:[C]共通 UC1:[C99]その他.
 *
 * @author Azet
 * @param string $para
 * @param integer $student_id
 * @param mixed $page
 * @return array
 */
function get_sns_data($para, $student_id, $page) {

	// 分散DB接続オブジェクト
	$sdb = $GLOBALS['sdb'];

	// 返却用領域
	$dataInfo = array();

	// GETパラメータを取得する
	$study_day = $_GET['STUDY_DAY'];

	// GETで取得できない時はPOSTで取得しなおす
	if (!$study_day) {
		$study_day = $_POST['STUDY_DAY'];
	}
	if ($study_day) {
		$study_day = $sdb->real_escape($study_day);
	}

//echo "param=".$para.":user=".$student_id."study_day=".$study_day."<br>";
//pre($_SESSION);

	// エラー番号領域
	$dataInfo['ERROR_NO'] = 0;

	// パラメータ未取得時にエラー番号を設定
	if ($para == "" || strlen($param) > 30) {
		$dataInfo['ERROR_NO'] = 1;
		return $dataInfo;
	}
	if ($student_id == "") {
		$dataInfo['ERROR_NO'] = 2;
		return $dataInfo;
	}

	// ユーザ情報取得
	if ($para == "GET_USER") {
		$dataInfo = get_user($student_id, $study_day, $dataInfo);
//echo $para." = ".round($e_mtime-$s_mtime, 3)."秒<br>";
	}

	// 件数取得
	if ($para == "GET_COUNT_ALL" || $para == "GET_COUNT_CLEAR_UNIT") {
		$dataInfo = get_list_clear_unit($student_id, $study_day, $dataInfo, $page, 'COUNT');
//echo $para." = ".round($e_mtime-$s_mtime, 3)."秒<br>";
	}
	if ($para == "GET_COUNT_ALL" || $para == "GET_COUNT_CLEAR_TIME") {
		$dataInfo = get_list_clear_time($student_id, $study_day, $dataInfo, $page, 'COUNT');
//echo $para." = ".round($e_mtime-$s_mtime, 3)."秒<br>";
	}
	if ($para == "GET_COUNT_ALL" || $para == "GET_COUNT_STUDY_NUMBER") {
		$dataInfo = get_list_study_number($student_id, $study_day, $dataInfo, $page, 'COUNT');
//echo $para." = ".round($e_mtime-$s_mtime, 3)."秒<br>";
	}
	if ($para == "GET_COUNT_ALL" || $para == "GET_COUNT_LOCAL_NUMBER") {
		$dataInfo = get_count_local_number($student_id, $study_day, $dataInfo, $page);
//echo $para." = ".round($e_mtime-$s_mtime, 3)."秒<br>";
	}

	// リスト取得
	if ($para == "GET_LIST_ALL" || $para == "GET_LIST_CLEAR_UNIT") {
		$dataInfo = get_list_clear_unit($student_id, $study_day, $dataInfo, $page, 'LIST');
//echo $para." = ".round($e_mtime-$s_mtime, 3)."秒<br>";
	}
	if ($para == "GET_LIST_ALL" || $para == "GET_LIST_CLEAR_TIME") {
		$dataInfo = get_list_clear_time($student_id, $study_day, $dataInfo, $page, 'LIST');
//echo $para." = ".round($e_mtime-$s_mtime, 3)."秒<br>";
	}
	if ($para == "GET_LIST_ALL" || $para == "GET_LIST_STUDY_NUMBER") {
		$dataInfo = get_list_study_number($student_id, $study_day, $dataInfo, $page, 'LIST');
//echo $para." = ".round($e_mtime-$s_mtime, 3)."秒<br>";
	}
	if ($para == "GET_LIST_ALL" || $para == "GET_LIST_LOCAL_NUMBER") {
		$dataInfo = get_list_local_number($student_id, $study_day, $dataInfo, $page, 'LIST');
//echo $para." = ".round($e_mtime-$s_mtime, 3)."秒<br>";
	}

	return $dataInfo;
}


/**
 * ユーザ情報取得
 *
 * AC:[C]共通 UC1:[C99]その他.
 *
 * @author Azet
 * @param integer $student_id
 * @param mixed $study_day
 * @param array $dataInfo
 * @return array
 */
function get_user($student_id, $study_day, $dataInfo) {

	// 分散DB接続オブジェクト
	$sdb = $GLOBALS['sdb'];

	// 複数指定が有るかもしれないので、カンマ区切りでセパレートしてみる
	$student_id_list = explode(",", $student_id);
	if (!is_array($student_id_list)) {
		$student_id_list[0] = $student_id;
	}

	// データ初期クリア
	$i = 0;
	foreach ($student_id_list as $key => $value) {
		$dataInfo['USER_INFO'][$i]['STUDENT_ERROR_NO'] = "0";		// add oda 2013/05/13
		$dataInfo['USER_INFO'][$i]['NO'] = $i;
		$dataInfo['USER_INFO'][$i]['STUDENT_ID'] = $value;
		$dataInfo['USER_INFO'][$i]['HANDLE_NAME'] = "ななしさん";
		$dataInfo['USER_INFO'][$i]['SCHOOL_NAME'] = "";
		$dataInfo['USER_INFO'][$i]['PRF'] = "";
		$dataInfo['USER_INFO'][$i]['UNIT_COUNT'] = "0";
		$dataInfo['USER_INFO'][$i]['STUDY_TOTAL'] = "0";
		$i++;
	}

	// 年月を取得する
	$month_list = ranking_get_month();

	// SQL生成
	$sql  = "SELECT ";
	$sql .= "  hn.student_id, ";
	$sql .= "  hn.anonymity, ";
	$sql .= "  si.school_name, ";
	$sql .= "  si.prf, ";
	$sql .= "  rk.all_study_time, ";
	$sql .= "  rk.results ";
	$sql .= " FROM ". T_HANDLE_NAME." hn ";
	$sql .= " LEFT JOIN student_info si ON si.student_id = hn.student_id AND si.mk_flg = '0' ";
//	$sql .= " LEFT JOIN ranking_all rk ON si.student_id = rk.student_id AND rk.total_start_month = '".$month_list['now']."' AND rk.mk_flg = '0' AND rk.move_flg = '0' ";	// del oda 2013/09/02
	$sql .= " LEFT JOIN ranking_all rk ON si.student_id = rk.student_id AND si.school_id = rk.school_id AND rk.total_start_month = '".$month_list['now']."' AND rk.mk_flg = '0' AND rk.move_flg = '0' ";	// update oda 2013/09/02
//	$sql .= " WHERE hn.student_id IN (".implode(",", $student_id_list).")";			// del oda 2013/05/01
	$sql .= " WHERE hn.student_id IN ('".implode("','", $student_id_list)."')";		// add oda 2013/05/01

//echo $sql."<br>";

	// データ格納
	if ($result = $sdb->query($sql)) {
		while($list = $sdb->fetch_assoc($result)) {
			foreach ($dataInfo['USER_INFO'] as $key => $value) {
				if ($value['STUDENT_ID'] == $list['student_id']) {
					if ($list['anonymity']) {
						$dataInfo['USER_INFO'][$key]['HANDLE_NAME'] = $list['anonymity'];
					}
					if ($list['school_name']) {
						$dataInfo['USER_INFO'][$key]['SCHOOL_NAME'] = $list['school_name'];
					}
					if ($list['prf']) {
						$dataInfo['USER_INFO'][$key]['PRF'] = $list['prf'];
					}
					if ($list['results']) {
						$dataInfo['USER_INFO'][$key]['UNIT_COUNT'] = $list['results'];
					}
					if ($list['all_study_time']) {
						$dataInfo['USER_INFO'][$key]['STUDY_TOTAL'] = $list['all_study_time'];
					}
					break;
				}
			}
		}
	}

	return $dataInfo;
}

/**
 * 同一地域人数取得
 *
 * AC:[C]共通 UC1:[C99]その他.
 *
 * @author Azet
 * @param integer $student_id
 * @param mixed $study_day
 * @param array $dataInfo
 * @param mixed $page
 * @return array
 */
function get_count_local_number($student_id, $study_day, $dataInfo, $page) {

	// 件数格納用の領域定義
	$data_list = array();

	// リスト情報を取得
	$data_list = get_list_local_number($student_id, $study_day, $data_list, $page, 'COUNT');

	// 件数を取得し、パラメータに設定
	if (is_array($data_list)) {
		$dataInfo['CLEAR_LOCAL_NUMBER'] = count($data_list['CLEAR_LOCAL_LIST']);
	}

	return $dataInfo;
}

/**
 * 同一クリアユニット数取得
 *
 * AC:[C]共通 UC1:[C99]その他.
 *
 * @author Azet
 * @param string $student_id
 * @param mixed $study_day
 * @param array $dataInfo
 * @param mixed $page
 * @param string $get_type
 * @return array
 */
function get_list_clear_unit($student_id, $study_day, $dataInfo, $page, $get_type) {

	// 分散DB接続オブジェクト
	$sdb = $GLOBALS['sdb'];

	// 年月を取得する
	$month_list = ranking_get_month();

	// ランキング情報から基準の生徒の情報を取得する
	/*	del oda 2013/09/02
	$sql  = "SELECT ";
	$sql .= " rk.student_id, ";
	$sql .= " rk.results AS unit_count ";
	$sql .= " FROM ranking_all rk ";
	$sql .= " WHERE rk.total_start_month = '".$month_list['now']."'";
	$sql .= "   AND rk.student_id = '".$student_id."'";
	$sql .= "   AND rk.mk_flg = '0'";
	$sql .= "   AND rk.move_flg = '0'";
	*/

	// add oda 2013/09/02
	$sql  = "SELECT ";
	$sql .= " rk.student_id, ";
	$sql .= " rk.results AS unit_count ";
	$sql .= " FROM student_info si ";
	$sql .= " INNER JOIN ranking_all rk ON si.student_id = rk.student_id AND si.school_id = rk.school_id ";
	$sql .= " WHERE rk.total_start_month = '".$month_list['now']."'";
	$sql .= "   AND si.student_id = '".$student_id."'";
	$sql .= "   AND si.mk_flg = '0'";
	$sql .= "   AND rk.mk_flg = '0'";
	$sql .= "   AND rk.move_flg = '0'";

//echo $sql."<br>";

	// クリアユニット数を取得
	$unit_count = 0;
	if ($result = $sdb->query($sql)) {
		while($list = $sdb->fetch_assoc($result)) {
			$unit_count = $list['unit_count'];
		}
	}

	// カウント取得の時のみカウント数初期クリア
	if ($get_type == "COUNT") {
		$dataInfo['CLEAR_UNIT_NUMBER'] = 0;
	}

	// 対象生徒のクリアユニット数が有る場合
	if ($unit_count > 0) {

		if ($get_type == "LIST") {

			// メッセージ送信済の情報をテンポラリテーブルで作成する
			$day_list = get_between_date($study_day, "-1", "day");
			$day_list1 = get_between_date($study_day, "-14", "day");

			$sql  = "CREATE TEMPORARY TABLE tmp_sns_message_2 ";
			$sql .= " SELECT ";
			$sql .= "  to_student_id, ";
			$sql .= "  count(from_student_id) AS message_count ";
			$sql .= " FROM sns_message ";
			$sql .= " WHERE msg_cat = '2' ";
			$sql .= "   AND from_student_id = '".$student_id."' ";
			$sql .= "   AND r_datetime > '".$day_list['start_datetime']."' ";
			$sql .= " GROUP BY to_student_id; ";

			$sdb->exec_query($sql);
//echo $sql."<br>";

			// テンポラリテーブルにキー情報を付加する
			$sql  = "ALTER TABLE tmp_sns_message_2 ADD INDEX to_student_id_1 (to_student_id);";
//echo $sql."<br>";
			$sdb->exec_query($sql);

			$sql  = "CREATE TEMPORARY TABLE tmp_sns_message_3 ";
			$sql .= " SELECT ";
			$sql .= "  from_student_id, ";
			$sql .= "  max(r_datetime) AS max_r_datetime ";
			$sql .= " FROM sns_message ";
			$sql .= " WHERE to_student_id = '".$student_id."' ";
			$sql .= "   AND r_datetime > '".$day_list1['start_datetime']."' ";
			$sql .= " GROUP BY from_student_id; ";
			$sdb->exec_query($sql);
//echo $sql."<br>";

			// テンポラリテーブルにキー情報を付加する
			$sql  = "ALTER TABLE tmp_sns_message_3 ADD INDEX from_student_id_1 (from_student_id);";
//echo $sql."<br>";
			$sdb->exec_query($sql);

		}

		// 同一クリアユニット数の生徒情報を取得するSQL生成(ユニット数±0)
		$sql  = "SELECT ";
		if ($get_type == "COUNT") {
			$sql .= " COUNT(rk.student_id) AS list_count ";
		} else {
			$sql .= " rk.student_id, ";
			$sql .= " rk.school_id, ";
			$sql .= " hn.anonymity, ";
			$sql .= " si.prf, ";
			$sql .= " si.school_name, ";
			$sql .= " rk.all_study_time, ";
			$sql .= " rk.results AS unit_count, ";
			$sql .= " me1.message_count, ";
			$sql .= " me2.max_r_datetime ";				// add oda 2013/09/02
		}
		$sql .= " FROM ranking_all rk ";
//		$sql .= " LEFT JOIN student_info si ON si.student_id = rk.student_id AND si.mk_flg = '0' ";											// del oda 2013/09/02
		$sql .= " INNER JOIN student_info si ON si.student_id = rk.student_id AND si.school_id = rk.school_id AND si.mk_flg = '0' ";		// update oda 2013/09/02 LEFT -> INNER と school_id追加
		$sql .= " LEFT JOIN ". T_HANDLE_NAME." hn ON rk.student_id = hn.student_id AND hn.mk_flg = '0' ";
		if ($get_type == "LIST") {
			$sql .= " LEFT JOIN tmp_sns_message_2 me1 ON rk.student_id = me1.to_student_id ";
			$sql .= " LEFT JOIN tmp_sns_message_3 me2 ON rk.student_id = me2.from_student_id ";
		}
		$sql .= " WHERE rk.total_start_month = '".$month_list['now']."'";
		$sql .= "   AND rk.results = '".$unit_count."'";
		$sql .= "   AND rk.mk_flg = '0'";
		$sql .= "   AND rk.move_flg = '0'";
		if ($get_type == "LIST") {
			$sql .= " ORDER BY me2.max_r_datetime DESC, rk.school_id, rk.student_id ";		// add oda 2013/09/02 max_r_datetimeを追加
			if ($page != "" && $page >= 0) {
				$sql .= " LIMIT ".$page.",100";
			}
		}

//echo $sql."<br>";

		// データ格納
		$i = 0;
		if ($result = $sdb->query($sql)) {

			while($list = $sdb->fetch_assoc($result)) {
				// カウント情報取得の際、カウントを格納する
				if ($get_type == "COUNT") {
					$dataInfo['CLEAR_UNIT_NUMBER'] = $list['list_count'] - 1;
				// リスト情報取得の際、ユーザ情報と校舎情報と実績情報を格納する
				} else {
					if ($student_id != $list['student_id']) {
						$dataInfo['CLEAR_UNIT_LIST'][$i]['NO']     = ($i+1);
						$dataInfo['CLEAR_UNIT_LIST'][$i]['STUDENT_ID']  = $list['student_id'];

						$handle_name = "ななしさん";				// ハンドルネームを設定していない場合は、固定文字を設定
						if ($list['anonymity']) {
							$handle_name = $list['anonymity'];
						}
						$dataInfo['CLEAR_UNIT_LIST'][$i]['HANDLE_NAME'] = $handle_name;
						$dataInfo['CLEAR_UNIT_LIST'][$i]['PRF'] = $list['prf'];
						$dataInfo['CLEAR_UNIT_LIST'][$i]['SCHOOL_ID'] = $list['school_id'];		// add hasegawa 2018/07/02 ハンドルネーム対象校舎の校舎以外の校舎名、名前をななし
						$dataInfo['CLEAR_UNIT_LIST'][$i]['SCHOOL_NAME'] = $list['school_name'];
						$dataInfo['CLEAR_UNIT_LIST'][$i]['UNIT_COUNT'] = $list['unit_count'];
						$dataInfo['CLEAR_UNIT_LIST'][$i]['STUDY_TOTAL'] = $list['all_study_time'];
						$dataInfo['CLEAR_UNIT_LIST'][$i]['MESSAGE_COUNT']  = $list['message_count'];
						$i++;
					}
				}
			}
		}

		// ＤＢサーバのメモリを考慮し、テンポラリテーブルを削除する。
		if ($get_type == "LIST") {
			$sql  = "DROP TEMPORARY TABLE tmp_sns_message_2 ";
			$sdb->exec_query($sql);

			$sql  = "DROP TEMPORARY TABLE tmp_sns_message_3 ";
			$sdb->exec_query($sql);
		}
	}

	return $dataInfo;
}

/**
 * 同一学習時間取得
 *
 * AC:[C]共通 UC1:[C99]その他.
 *
 * @author Azet
 * @param integer $student_id
 * @param mixed $study_day
 * @param array $dataInfo
 * @param mixed $page
 * @param string $get_type
 * @return array
 */
function get_list_clear_time($student_id, $study_day, $dataInfo, $page, $get_type) {

	// 分散DB接続オブジェクト
	$sdb = $GLOBALS['sdb'];

	// 年月を取得する
	$month_list = ranking_get_month();

	// ランキング情報から基準の生徒の情報を取得する
	/* del oda 2013/09/02
	$sql  = "SELECT ";
	$sql .= " rk.student_id, ";
	$sql .= " rk.all_study_time ";
	$sql .= " FROM ranking_all rk ";
	$sql .= " WHERE rk.total_start_month = '".$month_list['now']."'";
	$sql .= "   AND rk.student_id = '".$student_id."'";
	$sql .= "   AND rk.mk_flg = '0'";
	$sql .= "   AND rk.move_flg = '0'";
	*/

	// add oda 2013/09/02
	$sql  = "SELECT ";
	$sql .= " rk.student_id, ";
	$sql .= " rk.all_study_time ";
	$sql .= " FROM student_info si ";
	$sql .= " INNER JOIN ranking_all rk ON si.student_id = rk.student_id AND si.school_id = rk.school_id ";
	$sql .= " WHERE rk.total_start_month = '".$month_list['now']."'";
	$sql .= "   AND si.student_id = '".$student_id."'";
	$sql .= "   AND si.mk_flg = '0'";
	$sql .= "   AND rk.mk_flg = '0'";
	$sql .= "   AND rk.move_flg = '0'";

//echo $sql."<br>";

	// 学習時間を格納する
	$all_study_time = 0;
	if ($result = $sdb->query($sql)) {
		while($list = $sdb->fetch_assoc($result)) {
			$all_study_time = $list['all_study_time'];
		}
	}

	// カウント取得の時のみカウント数初期クリア
	if ($get_type == "COUNT") {
		$dataInfo['CLEAR_TIME_NUMBER'] = 0;
	}

	if ($all_study_time > 0) {				// add oda 2013/04/26 学習時間が０の生徒は一覧を取得しない

		// 学習時間を時間単位で四捨五入してみる
		$sec_from = 0;
		$sec_to = 0;
		// 保持データは、秒なので、時間と分にしてみる
		$min = floor($all_study_time / 60);			// 秒は切り捨て
		$hour = floor($min / 60);					// 分を切り捨てて時間のみにする
		$min = $min - ($hour * 60);					// 分を算出する

		// update start oda 2013/05/01 時間範囲を前後30分にする
//		if ($min >= 30) {
//			$hour = $hour + 1;
//		}		// ３０分以上の時は、時間を＋１する
//		if ($hour == 0) {							// ３０分未満の場合は、１時間以内とする
//			$sec_from = 0;
//			$sec_to = (1 * 60 * 60);
//		} else {
//			$sec_from = (($hour - 1) * 60 * 60) + (30 * 60);
//			$sec_to = ($hour * 60 * 60) + (30 * 60);
//		}
		// ３０分未満の場合は、１分から６０分を範囲とする
		if ($hour == 0 && $min < 30) {
			$sec_from = 1;
			$sec_to = (1 * 60 * 60);
		} else {
			$sec_from = ($hour * 60 * 60) + (($min-30) * 60);
			if ($sec_from <= 0) { $sec_from = 1; }
			$sec_to = ($hour * 60 * 60) + (($min+30) * 60);
		}
		// update start oda 2013/05/01

		if ($get_type == "LIST") {

			// メッセージ送信済の情報をテンポラリテーブルで作成する
			$day_list = get_between_date($study_day, "-1", "day");
			$day_list1 = get_between_date($study_day, "-14", "day");

			$sql  = "CREATE TEMPORARY TABLE tmp_sns_message_3 ";
			$sql .= " SELECT ";
			$sql .= "  to_student_id, ";
			$sql .= "  count(from_student_id) AS message_count ";
			$sql .= " FROM sns_message ";
			$sql .= " WHERE msg_cat = '3' ";
			$sql .= "   AND from_student_id = '".$student_id."' ";
			$sql .= "   AND r_datetime > '".$day_list['start_datetime']."' ";
			$sql .= " GROUP BY to_student_id; ";
//echo $sql."<br>";
			$sdb->exec_query($sql);

			// テンポラリテーブルのキー情報を付加する
			$sql  = "ALTER TABLE tmp_sns_message_3 ADD INDEX to_student_id_1 (to_student_id);";
//echo $sql."<br>";
			$sdb->exec_query($sql);

			$sql  = "CREATE TEMPORARY TABLE tmp_sns_message_4 ";
			$sql .= " SELECT ";
			$sql .= "  from_student_id, ";
			$sql .= "  max(r_datetime) AS max_r_datetime ";
			$sql .= " FROM sns_message ";
			$sql .= " WHERE to_student_id = '".$student_id."' ";
			$sql .= "   AND r_datetime > '".$day_list1['start_datetime']."' ";
			$sql .= " GROUP BY from_student_id; ";
//echo $sql."<br>";
			$sdb->exec_query($sql);

			// テンポラリテーブルのキー情報を付加する
			$sql  = "ALTER TABLE tmp_sns_message_4 ADD INDEX from_student_id_1 (from_student_id);";
//echo $sql."<br>";
			$sdb->exec_query($sql);

		}

		// 同一学習時間の生徒情報を取得するSQL生成
		$sql  = "SELECT ";
		if ($get_type == "COUNT") {
			$sql .= " COUNT(rk.student_id) AS list_count ";
		} else {
			$sql .= " rk.student_id, ";
			$sql .= " rk.school_id, ";
			$sql .= " hn.anonymity, ";
			$sql .= " si.prf, ";
			$sql .= " si.school_name, ";
			$sql .= " rk.all_study_time, ";
			$sql .= " rk.results AS unit_count, ";
			$sql .= " me1.message_count, ";
			$sql .= " me2.max_r_datetime ";				// add oda 2013/09/02
		}
		$sql .= " FROM ranking_all rk ";
//		$sql .= " LEFT JOIN student_info si ON si.student_id = rk.student_id AND si.mk_flg = '0' ";											// del oda
		$sql .= " INNER JOIN student_info si ON si.student_id = rk.student_id AND si.school_id = rk.school_id AND si.mk_flg = '0' ";		// update oda 2013/09/02 LEFT -> INNER と school_id追加
		$sql .= " LEFT JOIN ". T_HANDLE_NAME." hn ON rk.student_id = hn.student_id AND hn.mk_flg = '0' ";
		if ($get_type == "LIST") {
			$sql .= " LEFT JOIN tmp_sns_message_3 me1 ON rk.student_id = me1.to_student_id ";
			$sql .= " LEFT JOIN tmp_sns_message_4 me2 ON rk.student_id = me2.from_student_id ";
		}
		$sql .= " WHERE rk.total_start_month = '".$month_list['now']."'";
		$sql .= "   AND rk.all_study_time BETWEEN '".$sec_from."' AND '".$sec_to."'";
		$sql .= "   AND rk.mk_flg = '0'";
		$sql .= "   AND rk.move_flg = '0'";
		if ($get_type == "LIST") {
			$sql .= " ORDER BY me2.max_r_datetime DESC, rk.school_id, rk.student_id ";		// update oda 2013/09/02 max_r_datetime追加
			if ($page != "" && $page >= 0) {
				$sql .= " LIMIT ".$page.",100";
			}
		}

//echo $sql."<br>";

		// データ格納
		$i = 0;
		if ($result = $sdb->query($sql)) {
			while($list = $sdb->fetch_assoc($result)) {
				// カウント情報取得の際、カウントを格納する
				if ($get_type == "COUNT") {
					$dataInfo['CLEAR_TIME_NUMBER'] = $list['list_count'] - 1;
				// リスト情報取得の際、ユーザ情報と校舎情報と実績情報を格納する
				} else {
					if ($student_id != $list['student_id']) {
						$dataInfo['CLEAR_TIME_LIST'][$i]['NO']     = ($i+1);
						$dataInfo['CLEAR_TIME_LIST'][$i]['STUDENT_ID']  = $list['student_id'];

						$handle_name = "ななしさん";				// ハンドルネームを設定していない場合は、固定文字を設定
						if ($list['anonymity']) {
							$handle_name = $list['anonymity'];
						}

						$dataInfo['CLEAR_TIME_LIST'][$i]['HANDLE_NAME'] = $handle_name;
						$dataInfo['CLEAR_TIME_LIST'][$i]['PRF'] = $list['prf'];
						$dataInfo['CLEAR_TIME_LIST'][$i]['SCHOOL_ID'] = $list['school_id'];		// add hasegawa 2018/07/02 ハンドルネーム対象校舎の校舎以外の校舎名、名前をななし
						$dataInfo['CLEAR_TIME_LIST'][$i]['SCHOOL_NAME'] = $list['school_name'];
						$dataInfo['CLEAR_TIME_LIST'][$i]['UNIT_COUNT'] = $list['unit_count'];
						$dataInfo['CLEAR_TIME_LIST'][$i]['STUDY_TOTAL'] = $list['all_study_time'];
						$dataInfo['CLEAR_TIME_LIST'][$i]['MESSAGE_COUNT']  = $list['message_count'];

						$i++;
					}
				}
			}
		}

		// ＤＢサーバのメモリを考慮し、テンポラリテーブルを削除する。
		if ($get_type == "LIST") {
			$sql  = "DROP TEMPORARY TABLE tmp_sns_message_3 ";
			$sdb->exec_query($sql);
			$sql  = "DROP TEMPORARY TABLE tmp_sns_message_4 ";
			$sdb->exec_query($sql);
		}
	}

	return $dataInfo;
}

/**
 * 同一学習ユニット取得
 *
 * AC:[C]共通 UC1:[C99]その他.
 *
 * @author Azet
 * @param integer $student_id
 * @param mixed $study_day
 * @param array $dataInfo
 * @param mixed $page
 * @param string $get_type
 * @return array
 */
function get_list_study_number($student_id, $study_day, $dataInfo, $page, $get_type) {

	// 分散DB接続オブジェクト
	$sdb = $GLOBALS['sdb'];

	// 開始日と終了日を取得する（３日間）
	$day_list = get_between_date($study_day, "-2", "day");
	$start_day = $day_list['start_datetime'];
	$end_day   = $day_list['end_datetime'];

	// 指定日の学習情報取得SQL生成
	$sql  = "SELECT ";
	$sql .= " lh.student_id, ";
	$sql .= " lh.unit_num ";
	$sql .= " FROM ". T_LERNING_HISTORY." lh ";
	$sql .= " WHERE lh.student_id = '".$student_id."'";
	$sql .= "   AND lh.study_date BETWEEN '".$start_day."' AND '".$end_day."'";
	$sql .= "   AND lh.mk_flg = '0'";
	$sql .= " GROUP BY lh.student_id, lh.unit_num ";

//echo $sql."<br>";

	// 学習したユニット情報を格納する
	$data_list1 = array();
	if ($result = $sdb->query($sql)) {
		while($list = $sdb->fetch_assoc($result)) {
			$data_list1[] = $list['unit_num'];
		}
	}

	// 重複を削除
	$data_list2 = array_unique($data_list1);

	// カウント取得の時のみカウント数初期クリア
	if ($get_type == "COUNT") {
		$dataInfo['CLEAR_STUDY_NUMBER'] = 0;
	}

	$month_list = array();

	if ($get_type == "LIST") {

		// メッセージ送信済の情報をテンポラリテーブルで作成する
		$day_list = get_between_date($study_day, "-1", "day");
		$day_list1 = get_between_date($study_day, "-14", "day");

		$sql  = "CREATE TEMPORARY TABLE tmp_sns_message_4 ";
		$sql .= " SELECT ";
		$sql .= "  to_student_id, ";
		$sql .= "  count(from_student_id) AS message_count ";
		$sql .= " FROM sns_message ";
		$sql .= " WHERE msg_cat = '4' ";
		$sql .= "   AND from_student_id = '".$student_id."' ";
		$sql .= "   AND r_datetime > '".$day_list['start_datetime']."' ";
		$sql .= " GROUP BY to_student_id; ";
		$sdb->exec_query($sql);

		// テンポラリテーブルのキー情報を付加する
		$sql  = "ALTER TABLE tmp_sns_message_4 ADD INDEX to_student_id_1 (to_student_id);";
		$sdb->exec_query($sql);

		$sql  = "CREATE TEMPORARY TABLE tmp_sns_message_5 ";
		$sql .= " SELECT ";
		$sql .= "  from_student_id, ";
		$sql .= "  max(r_datetime) AS max_r_datetime ";
		$sql .= " FROM sns_message ";
		$sql .= " WHERE to_student_id = '".$student_id."' ";
		$sql .= "   AND r_datetime > '".$day_list1['start_datetime']."' ";
		$sql .= " GROUP BY from_student_id; ";
		$sdb->exec_query($sql);

		// テンポラリテーブルのキー情報を付加する
		$sql  = "ALTER TABLE tmp_sns_message_5 ADD INDEX from_student_id_1 (from_student_id);";
		$sdb->exec_query($sql);

		// 年月を取得する
		$month_list = ranking_get_month();
	}

	// 同一学習ユニットの生徒情報を取得するSQL生成
	$sql  = "SELECT ";
	$sql .= " lh.student_id, ";
	$sql .= " lh.school_id, ";
	$sql .= " hn.anonymity ";
	if ($get_type == "LIST") {
		$sql .= ", me1.message_count ";
		$sql .= ", si.prf ";
		$sql .= ", si.school_name ";
		$sql .= ", rk.all_study_time ";
		$sql .= ", rk.results ";
		$sql .= ", me2.max_r_datetime ";				// add oda 2013/09/02
	}
	$sql .= " FROM ". T_LERNING_HISTORY." lh ";
	$sql .= " LEFT JOIN ". T_HANDLE_NAME." hn ON lh.student_id = hn.student_id AND hn.mk_flg = '0' ";
	$sql .= " INNER JOIN student_info si ON si.student_id = lh.student_id AND si.mk_flg = '0' ";
	if ($get_type == "LIST") {
//		$sql .= " LEFT JOIN ranking_all rk ON lh.student_id = rk.student_id AND rk.total_start_month = '".$month_list['now']."' AND rk.mk_flg = '0' AND rk.move_flg = '0' ";										// del oda 2013/09/02
		$sql .= " LEFT JOIN ranking_all rk ON lh.student_id = rk.student_id AND si.school_id = rk.school_id AND rk.total_start_month = '".$month_list['now']."' AND rk.mk_flg = '0' AND rk.move_flg = '0' ";		// update oda 2013/09/02 school_id追加
		$sql .= " LEFT JOIN tmp_sns_message_4 me1 ON lh.student_id = me1.to_student_id ";
		$sql .= " LEFT JOIN tmp_sns_message_5 me2 ON lh.student_id = me2.from_student_id ";
	}
	$sql .= " WHERE lh.unit_num IN ('".implode("','", $data_list2)."')";	// add oda 2013/05/01
//	$sql .= "   AND lh.unit_num IN (".implode(",", $data_list2).")";		// del oda 2013/05/01
	$sql .= "   AND lh.study_date BETWEEN '".$start_day."' AND '".$end_day."'";
	$sql .= "   AND lh.mk_flg = '0'";
	$sql .= " GROUP BY lh.student_id, lh.school_id ";
	if ($get_type == "LIST") {
		$sql .= " ORDER BY me2.max_r_datetime DESC, lh.school_id, lh.student_id ";		// update oda 2013/09/02 max_r_datetime追加
		if ($page != "" && $page >= 0) {
			$sql .= " LIMIT ".$page.",100";
		}
	}

//echo $sql."<br>";

	// データ格納
	$i = 0;
	if ($result = $sdb->query($sql)) {
		// カウント情報取得の際、カウントを格納する
		if ($get_type == "COUNT") {
			$rec_count = $sdb->num_rows($result);
			if ($rec_count > 0) {
				$dataInfo['CLEAR_STUDY_NUMBER'] = $rec_count - 1;
			}
		// リスト情報取得の際、ユーザ情報と校舎情報と実績情報を格納する
		} else {
			while($list = $sdb->fetch_assoc($result)) {
				if ($student_id != $list['student_id']) {
					$dataInfo['CLEAR_STUDY_LIST'][$i]['NO']     = ($i+1);
					$dataInfo['CLEAR_STUDY_LIST'][$i]['STUDENT_ID']  = $list['student_id'];

					$handle_name = "ななしさん";				// ハンドルネームを設定していない場合は、固定文字を設定
					if ($list['anonymity']) {
						$handle_name = $list['anonymity'];
					}
					$dataInfo['CLEAR_STUDY_LIST'][$i]['HANDLE_NAME'] = $handle_name;
					$dataInfo['CLEAR_STUDY_LIST'][$i]['PRF'] = $list['prf'];
					$dataInfo['CLEAR_STUDY_LIST'][$i]['SCHOOL_NAME'] = $list['school_name'];
					$dataInfo['CLEAR_STUDY_LIST'][$i]['SCHOOL_ID'] = $list['school_id'];		// add hasegawa 2018/07/02 ハンドルネーム対象校舎の校舎以外の校舎名、名前をななし
					$dataInfo['CLEAR_STUDY_LIST'][$i]['UNIT_COUNT'] = $list['results'];
					$dataInfo['CLEAR_STUDY_LIST'][$i]['STUDY_TOTAL'] = $list['all_study_time'];
					$dataInfo['CLEAR_STUDY_LIST'][$i]['MESSAGE_COUNT']  = $list['message_count'];

					$i++;

				}
			}
		}
	}

	// ＤＢサーバのメモリを考慮し、テンポラリテーブルを削除する。
	if ($get_type == "LIST") {
		$sql  = "DROP TEMPORARY TABLE tmp_sns_message_4 ";
		$sdb->exec_query($sql);

		$sql  = "DROP TEMPORARY TABLE tmp_sns_message_5 ";
		$sdb->exec_query($sql);
	}

	return $dataInfo;
}

/**
 * 同一校舎ユニット取得
 *
 * AC:[C]共通 UC1:[C99]その他.
 *
 * @author Azet
 * @param integer $student_id
 * @param mixed $study_day
 * @param array $dataInfo
 * @param mixed $page
 * @param string $get_type
 * @return array
 */
function get_list_local_number($student_id, $study_day, $dataInfo, $page, $get_type) {

	// 対象生徒リスト
//	$student_id_list1 = "";

	// 分散DB接続オブジェクト
	$sdb = $GLOBALS['sdb'];

	// 年月を取得する
	$month_list = ranking_get_month();

	// 情報取得SQL生成
	$sql  = "SELECT ".
			"  stu.student_id, ".
			"  stu.school_id ".
			" FROM student_info stu ".
			" WHERE stu.student_id = '".$student_id."'".
			"   AND stu.mk_flg = '0'".
			" LIMIT 1 ";

//echo $sql."<br>";

	// 校舎情報を格納
	$school_id = "";
	if ($result = $sdb->query($sql)) {
		while ($list = $sdb->fetch_assoc($result)) {
			$school_id = $list['school_id'];
		}
	}

	$join_col = "";
	$join_sql = "";

	// リスト取得時は実績情報格納		// add oda 2013/09/02
	if ($get_type == "LIST") {

		// メッセージ送信済の情報をテンポラリテーブルで作成する
		$day_list = get_between_date($study_day, "-1", "day");
		$day_list1 = get_between_date($study_day, "-14", "day");

		$sql  = "CREATE TEMPORARY TABLE tmp_sns_message_5 ";
		$sql .= " SELECT ";
		$sql .= "  to_student_id, ";
		$sql .= "  count(from_student_id) AS message_count ";
		$sql .= " FROM sns_message ";
		$sql .= " WHERE msg_cat = '5' ";
		$sql .= "   AND from_student_id = '".$student_id."' ";
		$sql .= "   AND r_datetime > '".$day_list['start_datetime']."' ";
		$sql .= " GROUP BY to_student_id; ";
		$sdb->exec_query($sql);

		// テンポラリテーブルのキー情報を付加する
		$sql  = "ALTER TABLE tmp_sns_message_5 ADD INDEX to_student_id_1 (to_student_id);";
		$sdb->exec_query($sql);

		$sql  = "CREATE TEMPORARY TABLE tmp_sns_message_6 ";
		$sql .= " SELECT ";
		$sql .= "  from_student_id, ";
		$sql .= "  max(r_datetime) AS max_r_datetime ";					// add oda 2013/09/02
		$sql .= " FROM sns_message ";
		$sql .= " WHERE to_student_id = '".$student_id."' ";
		$sql .= "   AND r_datetime > '".$day_list1['start_datetime']."' ";
		$sql .= " GROUP BY from_student_id; ";
		$sdb->exec_query($sql);

		// テンポラリテーブルのキー情報を付加する
		$sql  = "ALTER TABLE tmp_sns_message_6 ADD INDEX from_student_id_1 (from_student_id);";
		$sdb->exec_query($sql);

		$join_col .= " me1.message_count, me2.max_r_datetime, ";
		$join_sql .= " LEFT JOIN tmp_sns_message_5 me1 ON stu.student_id = me1.to_student_id ";
		$join_sql .= " LEFT JOIN tmp_sns_message_6 me2 ON stu.student_id = me2.from_student_id ";
	}

	// 情報取得SQL生成	// update oda 2013/04/24 すらら使用不可の生徒は、表示しない様に修正
	$sql  = "SELECT ".
			"  stu.school_id, ".
			"  stu.student_id, ".
			"  hn.anonymity, ".
//		"  stu.school_name, ".
			"  ". convertDecryptField('stu.school_name') .", ". // kaopiz 2020/08/20 Encoding
			"  stu.prf, ".
			"  rk.all_study_time, ".
			$join_col.						// add oda 2013/09/02
			"  rk.results ".
			" FROM student_info stu ".
			" LEFT JOIN ". T_HANDLE_NAME." hn ON stu.student_id = hn.student_id AND hn.mk_flg = '0' " .
//			" LEFT JOIN ranking_all rk ON stu.student_id = rk.student_id AND rk.total_start_month = '".$month_list['now']."' AND rk.mk_flg = '0' AND rk.move_flg = '0' ".									// del oda 2013/09/02
			" LEFT JOIN ranking_all rk ON stu.student_id = rk.student_id AND stu.school_id = rk.school_id AND rk.total_start_month = '".$month_list['now']."' AND rk.mk_flg = '0' AND rk.move_flg = '0' ".	// update oda 2013/09/02 school_id追加
			$join_sql.																																														// add oda 2013/09/02
			" WHERE stu.school_id = '".$school_id."'".
			"   AND stu.mk_flg = '0'".
			"   AND (".
					"(stu.kmi_zksi='1'".
						" AND (".
							"stu.sito_jyti NOT IN (2,9)".
							" AND NOT (stu.sito_jyti='0' AND stu.secessionday < CURRENT_DATE())".
						")".
					")".
					" OR (stu.kmi_zksi='2'".
						" AND (".
							"stu.knry_end_flg != '1'".
						")".
					")".
					" OR (stu.kmi_zksi='3'".
						" AND (".
							"stu.knry_end_flg != '1' AND stu.sito_jyti != '2'".
						")".
					")".
					" OR (stu.kmi_zksi='4'".
						" AND (".
							"stu.knry_end_flg != '1' AND stu.sito_jyti != '2'".
						")".
					")".
					" OR (stu.kmi_zksi='5'".
						" AND (".
							"stu.sito_jyti NOT IN (2,9) AND NOT (stu.sito_jyti='0' AND stu.secessionday < CURRENT_DATE())".
						")".
					")".
					" OR (stu.kmi_zksi='6'".
						" AND (".
							"stu.sito_jyti NOT IN (2,9) AND NOT (stu.sito_jyti='0' AND stu.secessionday < CURRENT_DATE())".
						")".
					")".
					" OR (stu.kmi_zksi='8'".
						" AND (".
							"stu.sito_jyti NOT IN (2,9) AND NOT (stu.sito_jyti='0' AND stu.secessionday < CURRENT_DATE())".
						")".
					")".
					" OR (stu.kmi_zksi='9'".
						" AND (".
							"stu.sito_jyti NOT IN (2,9) AND NOT (stu.sito_jyti='0' AND stu.secessionday < CURRENT_DATE())".
						")".
					")".
				")";
	if ($get_type == "LIST") {
		$sql .= " ORDER BY me2.max_r_datetime DESC, stu.school_id ";		// update oda 2013/09/02 max_r_datetime追加
		if ($page != "" && $page >= 0) {
			$sql .= " LIMIT ".$page.",100";
		}
	}

	//echo $sql."<br>";

	// 同一校舎の生徒情報を格納
	$i = 0;
	if ($result = $sdb->query($sql)) {
		while ($list = $sdb->fetch_assoc($result)) {
			if ($student_id != $list['student_id']) {

				if (!$list['anonymity']) { $list['anonymity'] = "ななしさん"; }

				$dataInfo['CLEAR_LOCAL_LIST'][$i]['NO']     = ($i+1);
				$dataInfo['CLEAR_LOCAL_LIST'][$i]['STUDENT_ID']  = $list['student_id'];
				$dataInfo['CLEAR_LOCAL_LIST'][$i]['HANDLE_NAME'] = $list['anonymity'];
				$dataInfo['CLEAR_LOCAL_LIST'][$i]['PRF'] = $list['prf'];
				$dataInfo['CLEAR_LOCAL_LIST'][$i]['SCHOOL_ID'] = $list['school_id'];		// add hasegawa 2018/07/02 ハンドルネーム対象校舎の校舎以外の校舎名、名前をななし
				$dataInfo['CLEAR_LOCAL_LIST'][$i]['SCHOOL_NAME'] = $list['school_name'];

				$dataInfo['CLEAR_LOCAL_LIST'][$i]['UNIT_COUNT'] = "0";
				if ($list['results']) {
					$dataInfo['CLEAR_LOCAL_LIST'][$i]['UNIT_COUNT'] = $list['results'];
				}

				$dataInfo['CLEAR_LOCAL_LIST'][$i]['STUDY_TOTAL'] = "0";
				if ($list['all_study_time']) {
					$dataInfo['CLEAR_LOCAL_LIST'][$i]['STUDY_TOTAL'] = $list['all_study_time'];
				}

//				$dataInfo['CLEAR_LOCAL_LIST'][$i]['MESSAGE_COUNT'] = "0";			// del oda 2013/09/02
				$dataInfo['CLEAR_LOCAL_LIST'][$i]['MESSAGE_COUNT'] = "0";
				if ($get_type == "LIST") {											// add oda 2013/09/02
					if ($list['message_count']) {
						$dataInfo['CLEAR_LOCAL_LIST'][$i]['MESSAGE_COUNT'] = $list['message_count'];
					}
				}

				$i++;

//				if ($student_id_list1) {
//					$student_id_list1 .= ",";
//				}
//				$student_id_list1 .= "('".$list['student_id']."')";
			}
		}
	}

	// リスト取得時は実績情報格納
	if ($get_type == "LIST") {

		/*		del oda 2013/09/02
		// 基準となる生徒情報をテンポラリに作成する
		$sql  = "CREATE TEMPORARY TABLE tmp_student_id (student_id varchar(8) NOT NULL, PRIMARY KEY (student_id));";
		mysql_query($sql, SDB);

		// 対象の生徒情報をテンポラリに追加する
		$sql  = "INSERT INTO tmp_student_id VALUES ".$student_id_list1.";";
		mysql_query($sql, SDB);

		// メッセージ送信済の情報をテンポラリテーブルで作成する
		$day_list = get_between_date($study_day, "-1", "day");

		$sql  = "CREATE TEMPORARY TABLE tmp_sns_message_5 ";
		$sql .= " SELECT ";
		$sql .= "  to_student_id, ";
		$sql .= "  count(from_student_id) AS message_count, ";
		$sql .= "  max(r_datetime) AS max_r_datetime ";					// add oda 2013/09/02
		$sql .= " FROM sns_message ";
		$sql .= " WHERE msg_cat = '5' ";
		$sql .= "   AND from_student_id = '".$student_id."' ";
		$sql .= "   AND r_datetime > '".$day_list['start_datetime']."' ";
		$sql .= " GROUP BY to_student_id; ";

		mysql_query($sql, SDB);

		// テンポラリテーブルのキー情報を付加する
		$sql  = "ALTER TABLE tmp_sns_message_5 ADD INDEX to_student_id_1 (to_student_id);";

		mysql_query($sql, SDB);

		// 基準となる生徒情報からランキング情報と校舎情報と実績情報を取得する
		$sql  = "SELECT ";
		$sql .= " tst.student_id, ";
		$sql .= " me.message_count ";
		$sql .= " FROM tmp_student_id tst ";
//		$sql .= " LEFT JOIN tmp_sns_message_5 me ON tst.student_id = me.to_student_id ";
		$sql .= " INNER JOIN tmp_sns_message_5 me ON tst.student_id = me.to_student_id ";

//echo $sql."<br>";

		// 取得した情報を格納
		if ($result = mysql_query($sql, SDB)) {
			while($list = mysql_fetch_array($result, MYSQL_ASSOC)) {
				if (is_array($dataInfo['CLEAR_LOCAL_LIST'])) {
					foreach ($dataInfo['CLEAR_LOCAL_LIST'] as $key => $value) {
						if ($dataInfo['CLEAR_LOCAL_LIST'][$key]['STUDENT_ID'] == $list['student_id']) {
							if ($list['message_count']) {
								$dataInfo['CLEAR_LOCAL_LIST'][$key]['MESSAGE_COUNT'] = $list['message_count'];
							}
							break;
						}
					}
				}
			}
		}

		// ＤＢサーバのメモリを考慮し、テンポラリテーブルを削除する。
		$sql  = "DROP TEMPORARY TABLE tmp_student_id ";
		mysql_query($sql, SDB);
		*/

		$sql  = "DROP TEMPORARY TABLE tmp_sns_message_5 ";
		$sdb->exec_query($sql);

		$sql  = "DROP TEMPORARY TABLE tmp_sns_message_6 ";
		$sdb->exec_query($sql);
	}

	return $dataInfo;
}

/**
 * 開始日時／終了日時を取得
 *
 * AC:[C]共通 UC1:[C99]その他.
 *
 * @author Azet
 * @param string $study_day
 * @param string $su
 * @param string $kikan
 * @return array
 */
function get_between_date($study_day, $su, $kikan) {

	$day_list = array();

	if (!$study_day) { $study_day = date("Ymd"); }

	// 日付を分割
	$y = substr($study_day, 0, 4);
	$m = substr($study_day, 4, 2);
	$d = substr($study_day, 6, 2);

	// 開始日と終了日を算出する
	$day_list['start_datetime'] = date("Y-m-d", strtotime($y."/".$m."/".$d." ".$su." ".$kikan))." ".CLOSING_START;
	$day_list['end_datetime'] = date("Y-m-d", strtotime($y."/".$m."/".$d." 1 day"))." ".CLOSING_START;

	return $day_list;
}

?>
