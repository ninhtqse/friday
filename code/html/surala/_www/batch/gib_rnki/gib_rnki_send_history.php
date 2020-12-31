<?php
include_once("/data/home/www/userAgent.php");   // kaopiz add 2020/08/15 ipados13
/**
 * すらら 外部連携 成績送信機能（ケイアイスター不動産様）
 *
 * 2020/02/21 初期設定
 *
 * @author Azet
 */


$DBG_GIB_RNKI2003 = false;	//true のとき記述されているstudent_idのみ動作。運用時は false。
$DBG_GIB_RNK_CHIERU2003 = 0;	//0以外のとき、外部連携区分としてこの値を使用する。運用時は 0。
//
// $data_type: "unit"=ユニット学習情報送信、"test"=テスト学習情報送信API
//
function record_gib_rnki_history($cdb, $scdb, $gib_rnki_kb, $data_type, $send_result_params, $arg_ary=null) {

	//外部連携によるコード値をチェックして機能のON/OFF制御
	$_send_switch = "OFF";
	if (array_key_exists("GIBRNKIINFOS", $_SESSION) === true) {
		if (array_key_exists("cd_value", $_SESSION['GIBRNKIINFOS']) === true) {
			$_tmp_res_ary = array();
			foreach(explode("&", $_SESSION['GIBRNKIINFOS']['cd_value']) as $__tmp_k => $__tmp_v) {
				$_tmp_sep_param = explode("=", $__tmp_v);
				$_tmp_res_ary[$_tmp_sep_param[0]] = $_tmp_sep_param[1];
			}
			$_send_switch = $_tmp_res_ary["STUDYINFO"];
		}
	}

	//ID=KEAISTART&SSO=BASIC&CORE=SURALA：BASIC&SOKU=BASIC&IDSAVE=OFF&GURDIAN=DUMMY&STUDYINFO=ON
	if ($_send_switch != "ON") { return false; }	//ON でないならば、何もせずに終了


	if ($data_type == "unit") {
		// ユニット学習情報送信
		$res = send_gib_rnki_unit_history($cdb, $scdb, $gib_rnki_kb, $data_type, $send_result_params, $arg_ary);

	} else if($data_type == "test") {
		// テスト学習情報送信API
		$res = send_gib_rnki_test_history($cdb, $scdb, $gib_rnki_kb, $data_type, $send_result_params, $arg_ary);

	} else {
		$res = false;
	}

	return $res;
}


// テスト学習情報送信API
function send_gib_rnki_test_history($cdb, $scdb, $gib_rnki_kb, $data_type, $send_result_params, $arg_ary) {

	$TEST_COURSE = array();
	$TEST_COURSE[1] = "英語";
	$TEST_COURSE[2] = "国語";
	$TEST_COURSE[3] = "数学";
	$TEST_COURSE[15] = "理科";
	$TEST_COURSE[16] = "社会";


	$sql = "SELECT t.disp_test_num, t.start_time, t.score, t.answer_time, t.course_num, t.test_type, c.write_type as write_type ";
	$sql.= " FROM test_data t ";
	$sql.= " LEFT JOIN course c ON t.course_num = c.course_num ";
	$sql.= " WHERE t.test_num    = '".$arg_ary['test_num']."' ";
	$sql.= " AND   t.student_id  = '".$arg_ary['student_id']."' ";
	$sql.= " AND   t.study_count  = '".$arg_ary['study_count']."' ";	// add 2020/06/17 yoshizawa 学習管理画面リニューアル TODO 学習回数が実装できたら開放します。
	if($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
	}

	$tmp_start_time_ary = explode(" ", $list['start_time']);
	$tmp_test_no = sprintf("%07d", $list['disp_test_num']);
	$tmp_test_date = $tmp_start_time_ary[0];
	$tmp_test_date_time = $tmp_start_time_ary[1];
	$tmp_subject = $TEST_COURSE[$list['write_type']];
	$tmp_score = intVal($list['score']);
	$tmp_answer_time = $list['answer_time'];
	$tmp_course_num = $list['course_num'];
	$tmp_review_unit = "";
	$tmp_test_type = $list['test_type'];

//補強ポイントの取得
//test start
	if ($tmp_test_type == 1) {	//定期テスト
		$tmp_review_unit = get_test_type1($cdb, $arg_ary, $tmp_course_num);
	}
	if ($tmp_test_type == 3) {	//小テスト
		$tmp_review_unit = get_test_type3($cdb, $arg_ary, $tmp_course_num);
	}
	//if ($tmp_test_type == 4) {	//学力診断テスト
	//	$tmp_review_unit = get_test_type4($cdb, $arg_ary, $tmp_course_num);
	//}
	//if ($tmp_test_type == 5) {	//数検
	//	$tmp_review_unit = get_test_type5($cdb, $arg_ary, $tmp_course_num);
	//}

	$add_record_data = array(
		0 => array(		//DBレコード情報
			//"ext_send_study_num" => "",		//auto_encrement
			"enterprise_id"  => $_SESSION['studentid']['enterprise_id'],
			"school_id"  => $_SESSION['studentid']['school_id'],
			"student_id" => $_SESSION['studentid']['student_id'],
			"gib_rnki_kb" => $gib_rnki_kb,
			"data_type"  => $data_type,
			"course_num" => $tmp_course_num,
			"unit_num" => "",
			"block_num" => "",
			//"send_data" => "",
			"create_datetime"  => "now()",
			"status"  => "",
			"response_text"  => "",
			"response_tts_id"  => "",
			"action_datetime"  => "",
			"retry_status"  => "",
			"retry_response_text"  => "",
			"retry_tts_id"  => "",
			"last_action_datetime"  => "",
			"mail_notify_status"  => "",
			"mail_notify_tts_id"  => "",
			"mail_address"  => "",
			"mail_notify_datetime"  => "",
			"mk_flg"  => "",
			"mk_tts_id"  => "",
			"mk_date"  => ""
		),
		1 => array(		//送信用データ項目
			"student_id" => $arg_ary['student_id'],
			"test_no" => $tmp_test_no,
			"test_date" 	=> str_replace("-", "", $tmp_test_date),
			"test_date_time" => str_replace(":", "", $tmp_test_date_time),
			"subject" => $tmp_subject,
			"score" 	=> $tmp_score,
			"study_time" 	=> $tmp_answer_time,
			"review_unit" 	=> $tmp_review_unit
		)
	);


	// DB へ記録準備、POSTするデータの構築
	$set_send_data = array();
	foreach($add_record_data[1] as $k => $v) {
		$set_send_data[ $send_result_params[0]['parameters'][$k][0] ] = $v;
	}

	// DB へ記録
	$INSERT_DATA = array();
	$INSERT_DATA['send_data'] = serialize($set_send_data);
	foreach($add_record_data[0] as $k => $v) {
		$INSERT_DATA[$k] = $v;
	}

	// ＤＢ登録処理
	$ERROR = $scdb->insert('ext_send_study', $INSERT_DATA);	//@@@@@
	if ($ERROR) {
echo "<hr>ERR DETECT.<br>";
echo "<pre>";print_r($ERROR);echo "</pre><hr>";
	return 1;	//ERROR

	} else {
		$ext_send_study_num = $scdb->insert_id();
//echo "insert db ok: ".$ext_send_study_num."*<br>";

	}
//echo "<pre>";print_r($INSERT_DATA);echo "</pre>";



	//送信実行(curlで所定CGIをキック)
	$set_data = http_build_query(array("n" => $ext_send_study_num));

	if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
		$protocol = "https://";
	} else {
		$protocol = "http://";
	}

	$_server_env = "develop";
	switch ($_SERVER['SERVER_ADDR']){
		// 開発サーバー
		case "10.3.11.100":
		case "13.114.235.64":
			$_server_env = "develop";
			break;

		// 検証サーバー
		case "10.2.11.10":
		case preg_match('/^10\.2\./',$_SERVER['SERVER_ADDR'])===1:		// add oda 2020/10/15 検証環境の判断を変更
		case "13.230.217.123":
			$_server_env = "staging";
			break;

		// 本番サーバー
		// 本番環境はAWSのautoscalingで切り替えるためIPが固定になりません。
		// このため別途サーバーIPの第二オクテットでprodか判断します。
		case preg_match('/^10\.1\./',$_SERVER['SERVER_ADDR'])===1:
			$_server_env = "product";
			break;

		default:
			break;
	}

	$http_host = $_SERVER['HTTP_HOST'];
	$_svr_adrs = $_SERVER['SERVER_ADDR'];
//	$url = $protocol.$http_host.$send_result_params[0]["inner_url"][$_server_env];
	//$url = $protocol.$send_result_params[0]["inner_url"][$_server_env];
	$url = $protocol.$_svr_adrs.$send_result_params[0]["inner_url"][$_server_env];
//echo $url;
//	$url = "/ext/send_result_data.php";

	//$mh = curl_multi_init();
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($ch, CURLOPT_TIMEOUT, 15);
	curl_setopt($ch, CURLOPT_SSLVERSION, 6); // TLS 1.2
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: application/x-www-form-urlencoded; charset=UTF-8"));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $set_data);
	//curl_multi_add_handle($mh, $ch);
	$str_result  = curl_exec($ch);
//$str_result  = $url;
	//do { curl_multi_exec($mh, $running); } while ( $running );
	//$str_result  = curl_multi_getcontent($ch);
	//curl_multi_remove_handle($mh, $ch);
	curl_close($ch);

//echo "<hr>ext_send_study_num=".$ext_send_study_num."<br>str_result:<br/>".$str_result ."<hr>";
//$str_result .= "<hr>".$url."<hr>";
	return $str_result;	//$add_record_data;

//	return false;
}


// ユニット学習情報送信
function send_gib_rnki_unit_history($cdb, $scdb, $gib_rnki_kb, $data_type, $send_result_params, $arg_ary) {
//echo "data_type=".$data_type."<pre>";print_r($send_result_params);echo "</pre>";
//echo "<pre>";print_r($_SESSION);echo "</pre><hr>";

/*
$send_result_params の配列内容
		("unit")	=> array(
						0 => array("inner_url" => "/ext/send_result_data.php", 		//0: 内部の送信処理URL
								"outer_url" => array(		//1: 外部接続先URL
									"develop" => "/entry/support/unit_history.php",
									"staging" => "/entry/support/unit_history.php",
									"product" => "/entry/support/unit_history.php",
									),
								"parameters" => array( 		//2: 送信データの項目情報
									"student_id"		=> array("ID",				"生徒ID",			1,	"text",		8	,	''),
									"subject"			=> array("SUBJECT",			"教科名",			1,	"text",		255,	'コース名'),
									"unit1"				=> array("UNIT1",			"単元(1)",			1,	"text",		255,	'ステージ名'),
									"unit2"				=> array("UNIT2",			"単元(2)",			1,	"text",		255,	'レッスン名'),
									"unit3"				=> array("UNIT3",			"単元(3)",			1,	"text",		255,	'ユニット名'),
									"unit_name"			=> array("UNIT_NAME",		"学習内容",			1,	"text",		255,	'ユニット単元名'),
									"study_count"		=> array("STUDY_COUNT",		"学習回数",			1,	"number",	10,		'同一単元を学習した時の回数'),
									"study_time"		=> array("STUDY_TIME",		"総学習時間",		1,	"number",	10,	'ユニットクリア時間（秒）'),
									"correct_rate"		=> array("CORRECT_RATE",	"正解率",			1,	"number",	10,	'端数切捨て'),
									"timeup_count	"	=> array("TIMEUP_COUNT",	"タイムアップ回数",	1,	"number",	10,	'ドリルギブアップ回数'),
									"study_date"		=> array("STUDY_DATE",		"学習日",			1,	"text",		8,	'YYYYMMDD'),
									"study_date_time"	=> array("STUDY_DATE_TIME",	"学習時",			1,	"text",		6,	'HHmmSS'),
									),
								"add_params" => ""),			//3: 付加パラメータ
						1 => array()
					),
*/

	//結果出力用
	$_tmp_subject = $_SESSION['course']['course_num'];		// "subject(コース名)"
	$_tmp_unit1 = $_SESSION['course']['stage_num'];			// "unit1(ステージ名)"
	$_tmp_unit2 = $_SESSION['course']['lesson_num'];		// "unit2(レッスン名)"
	$_tmp_unit3 = $_SESSION['course']['unit_num'];			// "unit3(ユニット名)"
	$_tmp_unit_name = $_SESSION['course']['course_num'];	// "unit_name(ユニット単元名)"


	$course_num = $_SESSION['course']['course_num'];
	$unit_num = $_SESSION['course']['unit_num'];

	//ユニット番号からユニットキー取得
	$unit_key = get_unitkey_by_unitnum($unit_num);
	//ユニットキーから学年の識別子取得
	if ($unit_key) { $grade_char = getUnitKeyInitial($unit_key); }
	//各学年用の文字列生成
	$grade_name = "";
	//英国数の時のみ学年を表示
	if($course_num == 1 || $course_num == 2 || $course_num == 3 || $course_num == 15 || $course_num == 16){
		$grade_name = lms_grade_name($grade_char);
	}

	//----------
	//ドリル情報判断
	//----------
	$drill_name_info = get_drill_title_name($unit_num);//ユニット番号からドリルの名前情報取得

	//----------
	//文字列形成
	//----------
	if(is_array($drill_name_info)){
		$location .= $grade_name." ";
		$service_num = get_servicenum_by_coursenum($course_num);
		//6:英単語・熟語マスターへの道
		//8:速さはかせ
		//9:割合はかせ はコース名を表示しない
		if($course_num == 6 || $course_num == 8 || $course_num == 9 || $service_num == "15"){
			$display_course = "";
		}else{
			$display_course = lms_course_format($drill_name_info['course_name'], $grade_char);
		}
		$_tmp_subject = $display_course;
		$_tmp_unit1 = $drill_name_info['stage_name'];
		$_tmp_unit2 = $drill_name_info['lesson_name'];
		$_tmp_unit3_name = $drill_name_info['unit_name'];
		$_tmp_unit_info = get_drill_title_name($_SESSION['course']['unit_num']);
		$_tmp_unit_name = $_tmp_unit_info['unit_name2'];		//"unit_name(ユニット単元名)"
		// add start oda 2020/07/02 外部連携ケイアイスター
		// ユニット単元名が空の場合はユニット名を設定する
		if ($_tmp_unit_name == "") {
			$_tmp_unit_name = $_tmp_unit_info['unit_name'];		//"unit_name(ユニット名)"
		}
		// add start oda 2020/07/02 外部連携ケイアイスター
	}
	$_tmp_finish_time_ary = explode(" ", $_SESSION['course']['finish_time']);


	//終了したユニットの学習結果を集計する
	$study_time = 0;
	$giveup = 0;
	$rate = 0;
	$sql = "SELECT sum(total_time) as total_time, sum(giveup) as giveup, sum(correct_count) as correct_count, sum(answer_count) as answer_count" .
			" FROM " .
			T_STUDY_UNIT_TIME . " " . T_STUDY_UNIT_TIME . "" .
			" WHERE study_unit_time.student_id='".$_SESSION['studentid']['student_id']."'".
			" AND study_unit_time.state='0'" .
			" AND study_unit_time.unit_num='".$_SESSION['course']['unit_num']."'".
			" AND study_unit_time.review='".$_SESSION['course']['review']."'";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$study_time = $list['total_time'];
		$giveup = $list['giveup'];
		$rate = decimal_point_calc($list['correct_count'],$list['answer_count'],$_SESSION['course']['course_num']);
	}
	//study_unit_time以外の学習時間を収集する
	$sql = "SELECT sum(total_time) as total_time ".
		" FROM ( ".
		" SELECT sum(total_time)  as total_time ".
		" FROM ".
		" study_flash_time sft ".
		" WHERE sft.student_id='".$_SESSION['studentid']['student_id']."' ".
		" AND sft.state='0' ".
		" AND sft.unit_num='".$_SESSION['course']['unit_num']."' ".
		" AND sft.review='".$_SESSION['course']['review']."' ".

		" UNION ALL ".
		" SELECT sum(total_time) as total_time ".
		" FROM ".
		" study_game_time sgt ".
		" WHERE sgt.student_id='".$_SESSION['studentid']['student_id']."' ".
		" AND sgt.state='0' ".
		" AND sgt.unit_num='".$_SESSION['course']['unit_num']."' ".
		" AND sgt.review='".$_SESSION['course']['review']."' ".

		" UNION ALL".
		" SELECT (flash_time + drill_time) as total_time ".
		" FROM ".
		" study_skill_time sst ".
		" WHERE sst.student_id='".$_SESSION['studentid']['student_id']."' ".
		" AND sst.state='0' ".
		" AND sst.unit_num='".$_SESSION['course']['unit_num']."' ".
		" AND sst.review='".$_SESSION['course']['review']."' ".
		") AS yyy;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$study_time = intVal($study_time) + intVal($list['total_time']);
	}
	//finish_unit から学習日時を調べる
	$regist_time = "";
	$study_date = "";
	$study_jikoku = "";
	$sql = "SELECT regist_time as regist_time ".
		" FROM finish_unit ".
		" WHERE student_id='".$_SESSION['studentid']['student_id']."' ".
		" AND state='0' ".
		" AND unit_num='".$_SESSION['course']['unit_num']."' ".
		" AND review='".$_SESSION['course']['review']."';";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$regist_time = $list['regist_time'];
	}
//echo $sql;echo "<br><pre>";print_r($list);echo "</pre>";
	if (strlen($regist_time) > 0) {
		$regist_time = str_replace("-", "", $regist_time);
		$regist_time = str_replace(":", "", $regist_time);
		$regist_time_ary = explode(" ", $regist_time);
		$study_date = $regist_time_ary[0];
		$study_jikoku = $regist_time_ary[1];
	}

	$add_record_data = array(
		0 => array(		//DBレコード情報
			//"ext_send_study_num" => "",		//auto_encrement
			"enterprise_id"  => $_SESSION['studentid']['enterprise_id'],
			"school_id"  => $_SESSION['studentid']['school_id'],
			"student_id" => $_SESSION['studentid']['student_id'],
			"gib_rnki_kb" => $gib_rnki_kb,
			"data_type"  => $data_type,
			"course_num" => $_SESSION['course']['course_num'],
			"unit_num" => $_SESSION['course']['unit_num'],
			"block_num" => $_SESSION['course']['block_num'],
			//"send_data" => "",
			"create_datetime"  => "now()",
			"status"  => "",
			"response_text"  => "",
			"response_tts_id"  => "",
			"action_datetime"  => "",
			"retry_status"  => "",
			"retry_response_text"  => "",
			"retry_tts_id"  => "",
			"last_action_datetime"  => "",
			"mail_notify_status"  => "",
			"mail_notify_tts_id"  => "",
			"mail_address"  => "",
			"mail_notify_datetime"  => "",
			"mk_flg"  => "",
			"mk_tts_id"  => "",
			"mk_date"  => ""
		),
		1 => array(		//送信用データ項目
			"student_id" => $_SESSION['studentid']['student_id'],
			"subject" => $_tmp_subject,		//(コース名)
			"unit1" => $_tmp_unit1,			//(ステージ名)
			"unit2" => $_tmp_unit2,			//(レッスン名)
			"unit3" => $_tmp_unit3_name,		//(ユニット名)
			"unit_name" => $_tmp_unit_name,		//(ユニット単元名)
			"study_count" 	=> $_SESSION['course']['review'],
			"study_time" 	=> $study_time,
			"correct_rate" 	=> $rate,
			"timeup_count" 	=> $giveup,
			"study_date" 	=> $study_date,
			"study_date_time" 	=> $study_jikoku
		)
	);



	// DB へ記録準備、POSTするデータの構築
	$set_send_data = array();
	foreach($add_record_data[1] as $k => $v) {
		$set_send_data[ $send_result_params[0]['parameters'][$k][0] ] = $v;
	}

	// DB へ記録
	$INSERT_DATA = array();
	$INSERT_DATA['send_data'] = serialize($set_send_data);
	foreach($add_record_data[0] as $k => $v) {
		$INSERT_DATA[$k] = $v;
	}
	// ＤＢ登録処理
	$ERROR = $scdb->insert('ext_send_study', $INSERT_DATA);	//@@@@@
	if ($ERROR) {
echo "<hr>ERR DETECT.<br>";
echo "<pre>";print_r($ERROR);echo "</pre><hr>";
	return 1;	//ERROR

	} else {
		$ext_send_study_num = $scdb->insert_id();

	}



	//送信実行(curlで所定CGIをキック)
	$set_data = http_build_query(array("n" => $ext_send_study_num));

	if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
		$protocol = "https://";
	} else {
		$protocol = "http://";
	}

	$_server_env = "develop";
	switch ($_SERVER['SERVER_ADDR']){
		// 開発サーバー
		case "10.3.11.100":
		case "13.114.235.64":
			$_server_env = "develop";
			break;

		// 検証サーバー
		case "10.2.11.10":
		case preg_match('/^10\.2\./',$_SERVER['SERVER_ADDR'])===1:		// add oda 2020/10/15 検証環境の判断を変更
		case "13.230.217.123":
			$_server_env = "staging";
			break;

		// 本番サーバー
		// 本番環境はAWSのautoscalingで切り替えるためIPが固定になりません。
		// このため別途サーバーIPの第二オクテットでprodか判断します。
		case preg_match('/^10\.1\./',$_SERVER['SERVER_ADDR'])===1:
			$_server_env = "product";
			break;

		default:
			break;
	}

	$http_host = $_SERVER['HTTP_HOST'];
	$_svr_adrs = $_SERVER['SERVER_ADDR'];
//	$url = $protocol.$http_host.$send_result_params[0]["inner_url"][$_server_env];
//	$url = $protocol.$send_result_params[0]["inner_url"][$_server_env];
	$url = $protocol.$_svr_adrs.$send_result_params[0]["inner_url"][$_server_env];
//	$url = "/ext/send_result_data.php";

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($ch, CURLOPT_TIMEOUT, 15);
	curl_setopt($ch, CURLOPT_SSLVERSION, 6); // TLS 1.2
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: application/x-www-form-urlencoded; charset=UTF-8"));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $set_data);
	$str_result  = curl_exec($ch);
//$str_result = $url;
//echo $url."<hr>";
//echo curl_errno($ch)."<br>";
//echo curl_error($ch);
//echo $str_result."<hr>";
	curl_close($ch);

//$str_result .= "<hr>".$url."<hr>";
	return $str_result;	//$add_record_data;

}


// 外部連携 成績送信機能の対象かチェック
// function check_gib_rnki_send_test($type_num, $cdb, $test_num, $student_id) {
function check_gib_rnki_send_test($type_num, $cdb, $test_num, $student_id, $study_count) {	// add 2020/06/17 yoshizawa 学習管理画面リニューアル TODO 学習回数が実装できたら開放します。

	global $DBG_GIB_RNKI2003, $DBG_GIB_RNK_CHIERU2003;

	if ($student_id == "10193318" || !$DBG_GIB_RNKI2003) {	//for debug @@@@@

	if(!$GLOBALS['scdb']) {
		connect_sogo_db();	// 総合サーバ接続
	}
	$scdb = $GLOBALS['scdb'];

	$gib_rnki_kb = $_SESSION['GIBRNKIINFOS']['gib_rnki_kb'];
	if ($DBG_GIB_RNK_CHIERU2003 > 0) { $gib_rnki_kb = $DBG_GIB_RNK_CHIERU2003; }	//動作テスト用

	if (intVal($gib_rnki_kb) > 0) {
		require_once(LOG_DIR.'ext_lib/class/base_api.class.php');
		$class_gib_base = new coreApiBase(LOG_DIR."ext_lib/");
		$gib_name = $class_gib_base->GRenkei_Code[$gib_rnki_kb];
		$class_gib_base->include_config($gib_name);
		if (!is_null($class_gib_base->send_result_params)) {
			$inc_file = LOG_DIR.'ext_lib/gib_rnki_send_history.php';
			if (file_exists($inc_file)) {
//echo "*idxx=".$idxx."<br>";
				require_once($inc_file);
				$arg_ary = array(
					"test_num" => $test_num,
					"student_id" => $student_id
					,"study_count" => $study_count	// add 2020/06/17 yoshizawa 学習管理画面リニューアル TODO 学習回数が実装できたら開放します。
				);
				$_test_result = record_gib_rnki_history($cdb, $scdb, $gib_rnki_kb, "test", $class_gib_base->send_result_params['test'], $arg_ary );

//echo "<pre>";print_r($_test_result);echo "</pre>";
			}
		}
	}

	}	//for debug @@@@@

}

/**
 * ゲームが存在するかチェックしてフラグを返却します。
 *
 * AC:[C]共通 UC1:[L03]学習履歴.
 *
 * @author Azet
 * @param number $course_num コース番号
 * @param number $stage_num ステージ番号
 * @param number $lesson_num レッスン番号
 * @param number $unit_num ユニット番号
 * @param number $skill_num スキル番号（未使用）
 * @return boolean true:存在する false:存在しない
 */
// unit_comp_view.php から流用 2020/03/09
function game_file_exist_check($course_num, $stage_num, $lesson_num, $unit_num, $skill_num) {

	$check_flg = false;

	$path_home = "../material/flash/".$course_num."/".$stage_num."/".$lesson_num."/".$unit_num."/game";

	$lecture_path = $path_home."/main.html";
	if (file_exists($lecture_path)) {
		$check_flg = true;
	}

	return $check_flg;
}
/**
 * レクチャーが存在するかチェックしてフラグを返却します。
 *
 * AC:[C]共通 UC1:[L03]学習履歴.
 *
 * @author Azet
 * @param number $course_num コース番号
 * @param number $stage_num ステージ番号
 * @param number $lesson_num レッスン番号
 * @param number $unit_num ユニット番号
 * @param number $skill_num スキル番号
 * @return boolean true:存在する false:存在しない
 */
// unit_comp_view.php から流用 2020/03/09
function lecture_file_exist_check($course_num, $stage_num, $lesson_num, $unit_num, $skill_num) {

	$check_flg = false;

	$path_home = "../material/flash/".$course_num."/".$stage_num."/".$lesson_num."/".$unit_num;

	// タブレットの場合
//  if (preg_match("/iPad|iPhone|android/i", $_SERVER['HTTP_USER_AGENT']) === 1) {
	if (preg_match("/iPad|iPhone|android/i", $_SERVER['HTTP_USER_AGENT']) === 1 || is_ipad()) { //kaopiz update 2020/07/15 ipados13
		// HTML5(real_samurai)
		$lecture_path = $path_home."/html5/main.swf.html";
		// その他・PCの場合
	} else {
		$lecture_path = $path_home."/main.swf";
	}

	// 計算マスターの場合
	if ($course_num == "4") {
		$lecture_path = $path_home."/index.html";
	}

	// 復習の場合
	if($skill_num > 0) {
		$lecture_path = $path_home."/spot.swf";
	}

	if (file_exists($lecture_path)) {
		$check_flg = true;
	}

	// 上記ファイルが存在しなければ低学年のファイルをチェック
	if($check_flg === false) {
		$lecture_path = $path_home."/main.html";
		if (file_exists($lecture_path)) {
			$check_flg = true;
		}
	}

	return $check_flg;
}



//定期テスト
//補強ポイントの取得
function get_test_type1($cdb, $arg_ary, $tmp_course_num) {

	// 受講中の場合、条件追加
	$where ="";
	if ($_SESSION['sub_session']['s_jyukou'] == 1) {	//@@@@@@@
		$where .= " AND (".
			" (school.kmi_zksi='1' AND (tsjj.sito_jyti NOT IN (2,9) AND NOT (tsjj.sito_jyti='0' AND st.secessionday<CURRENT_DATE())))".
			" OR (school.kmi_zksi='2' AND (st.knry_end_flg!='1'))".
			" OR (school.kmi_zksi='3' AND (st.knry_end_flg!='1' AND tsjj.sito_jyti!='2'))".
			" OR (school.kmi_zksi='4' AND (st.knry_end_flg!='1' AND tsjj.sito_jyti!='2'))".
			" OR (school.kmi_zksi='5' AND (tsjj.sito_jyti NOT IN (2,9) AND NOT (tsjj.sito_jyti='0' AND st.secessionday<CURRENT_DATE())))".
			" OR (school.kmi_zksi='6' AND (tsjj.sito_jyti NOT IN (2,9) AND NOT (tsjj.sito_jyti='0' AND st.secessionday<CURRENT_DATE())))".
			" OR (school.kmi_zksi='8' AND (tsjj.sito_jyti NOT IN (2,9) AND NOT (tsjj.sito_jyti='0' AND st.secessionday<CURRENT_DATE())))".
			" OR (school.kmi_zksi='9' AND (tsjj.sito_jyti NOT IN (2,9) AND NOT (tsjj.sito_jyti='0' AND st.secessionday<CURRENT_DATE())))".
			" )";
	}

	//生徒のテンポラリテーブル作成
	$temp_sql = "DROP TEMPORARY TABLE IF EXISTS tmp_student;";
	$cdb->exec_query($temp_sql);

	$temp_sql = "CREATE TEMPORARY TABLE tmp_student".
			" SELECT st.student_id,".
//        "  CONCAT(st.student_myj, \" \", st.student_nme) AS student_name ".
			"  CONCAT(". convertDecryptField('st.student_myj', false) .", \" \", ". convertDecryptField('st.student_nme', false) .") AS student_name ". // kaopiz 2020/08/20 Encoding
			" FROM ".T_STUDENT." st ".
			" INNER JOIN ".T_SCHOOL." school ON st.school_id=school.school_id".
			" INNER JOIN ".T_TB_STU_JYKO_JYOTAI." tsjj ON tsjj.student_id=st.student_id".
			" WHERE st.school_id='".$_SESSION['studentid']['school_id']."'".
			"   AND st.student_id='".$arg_ary['student_id']."'".
			"   AND st.mk_flg='0'".
			"   AND school.mk_flg='0'".
			"   AND tsjj.move_flg='0'".
			"   AND tsjj.mk_flg='0'".
			" ".$where.
		    ";";
	$cdb->exec_query($temp_sql);

	//test_numに対して学年を取得
	$temp_sql = "DROP TEMPORARY TABLE IF EXISTS tmp_test_data_choice_0;";
	$cdb->exec_query($temp_sql);

	$temp_sql = "CREATE TEMPORARY TABLE tmp_test_data_choice_0".
		    " SELECT ".
		    " st.student_id,".
		    " tdc.test_num,".
		    " tdc.study_count,".	// add 2020/06/17 yoshizawa 学習管理画面リニューアル TODO 学習回数が実装できたら開放します。
		    " tdc.gknn ".
		    " FROM  test_data_choice tdc ".
		    " INNER JOIN tmp_student st ON st.student_id = tdc.student_id".
		    " WHERE tdc.test_type = 1 ".
		    "   AND tdc.mk_flg    = 0 ".
		    ";";
	$cdb->exec_query($temp_sql);


	// group byが遅くなるので、２段階で作成する
	$temp_sql = "DROP TEMPORARY TABLE IF EXISTS tmp_test_data_choice;";
	$cdb->exec_query($temp_sql);
	$query .= $temp_sql."\n";

	// update start 2020/06/17 yoshizawa 学習管理画面リニューアル TODO 学習回数が実装できたら開放します。
	// $temp_sql = "CREATE TEMPORARY TABLE tmp_test_data_choice".
	// 	    " SELECT ".
	// 	    "  student_id,".
	// 	    "  test_num,".
	// 	    " group_concat(gknn) as gknn ".		// 単元で複数学年選択している場合は、カンマで区切る
	// 	    " FROM  tmp_test_data_choice_0 tdc ".
	// 	    " GROUP BY student_id,  test_num ".
	// 	    ";";
	$temp_sql = "CREATE TEMPORARY TABLE tmp_test_data_choice".
		    " SELECT ".
		    "  student_id,".
		    "  test_num,".
		    "  study_count,".
		    " group_concat(gknn) as gknn ".		// 単元で複数学年選択している場合は、カンマで区切る
		    " FROM  tmp_test_data_choice_0 tdc ".
		    " GROUP BY student_id,  test_num, study_count ".
		    ";";
	// update start 2020/06/17 yoshizawa 学習管理画面リニューアル TODO 学習回数が実装できたら開放します。

	$cdb->exec_query($temp_sql);


	//テストデータを取得
	$temp_sql = "DROP TEMPORARY TABLE IF EXISTS tmp_test_data;";
	$cdb->exec_query($temp_sql);

	// update start 2020/06/17 yoshizawa 学習管理画面リニューアル TODO 学習回数が実装できたら開放します。
	// $temp_sql = "CREATE TEMPORARY TABLE tmp_test_data".
	// 	    " SELECT st.student_id,".
	// 	    " td.test_num,".
	// 	    " td.disp_test_num,".
	// 	    " td.start_time,".
	// 	    " td.course_num, ".
	// 	    " td.score, ".
	// 	    " td.answer_time ".
	// 	    " FROM ".T_TEST_DATA." td ".
	// 	    " INNER JOIN tmp_student st ON st.student_id=td.student_id".
	// 	    " WHERE td.test_type = '1'  ".
	// 	    "   AND td.mk_flg = 0 ".
	// 	    "   AND td.answer_time > 0 ".
	// 	    ////"   AND td.test_date BETWEEN '".$SET_TIME['start_time']."' AND '".$SET_TIME['end_time']."'".
	// 	    //"   AND td.end_time BETWEEN '".$SET_TIME['start_time']."' AND '".$SET_TIME['end_time']."'".	//upd yamaguchi 2017/5/11 期間初日のデータが出力されない不具合修正
	//     	" AND td.test_num = ".$arg_ary['test_num']." ".
	// 	    " AND td.student_id = ".$arg_ary['student_id']." ".
	// 	    " GROUP BY td.student_id,td.test_num".
	// 	    ";";
	// $cdb->exec_query($temp_sql);
	// $temp_sql = "ALTER TABLE tmp_test_data ADD INDEX index1 (student_id, test_num);";
	// $cdb->exec_query($temp_sql);
	$temp_sql = "CREATE TEMPORARY TABLE tmp_test_data".
		    " SELECT st.student_id,".
		    " td.test_num,".
		    " td.study_count,".
		    " td.disp_test_num,".
		    " td.start_time,".
		    " td.course_num, ".
		    " td.score, ".
		    " td.answer_time ".
		    " FROM ".T_TEST_DATA." td ".
		    " INNER JOIN tmp_student st ON st.student_id=td.student_id".
		    " WHERE td.test_type = '1'  ".
		    "   AND td.mk_flg = 0 ".
		    "   AND td.answer_time > 0 ".
	    	" AND td.test_num = ".$arg_ary['test_num']." ".
		    " AND td.student_id = ".$arg_ary['student_id']." ".
		    " AND td.study_count = ".$arg_ary['study_count']." ".
		    " GROUP BY td.student_id,td.test_num,td.study_count".
		    ";";
	$cdb->exec_query($temp_sql);
	$temp_sql = "ALTER TABLE tmp_test_data ADD INDEX index1 (student_id, test_num, study_count);";
	$cdb->exec_query($temp_sql);
	// update end 2020/06/17 yoshizawa 学習管理画面リニューアル TODO 学習回数が実装できたら開放します。

	//単元を取得
	$temp_sql = "DROP TEMPORARY TABLE IF EXISTS tmp_weak_unit;";
	$cdb->exec_query($temp_sql);

	// update start 2020/06/17 yoshizawa 学習管理画面リニューアル TODO 学習回数が実装できたら開放します。
	// $temp_sql = " CREATE TEMPORARY TABLE tmp_weak_unit".
	// 	    " SELECT".
	// 	    " tmp_td.student_id, ".
	// 	    " tmp_td.test_num, ".
	// 	    " u.unit_num,".
	// 	    " u.unit_key,".
	// 	    " COUNT(tdp.problem_num) AS total_count, ".
	// 	    " SUM(IF(tdp.result = 1, 1, 0)) AS count".
	// 	    " FROM ".TEST_DATA_PROBLEM." tdp".
	// 	    " INNER JOIN tmp_test_data tmp_td ON tmp_td.test_num=tdp.test_num".
	// 		" AND tmp_td.student_id=tdp.student_id".
	// 	    " INNER JOIN ".T_BOOK_UNIT_LMS_UNIT." bulu ON bulu.problem_num=tdp.problem_num".
	// 	    " INNER JOIN ".T_UNIT." u ON u.unit_num=bulu.unit_num".
	// 	    " INNER JOIN ".T_BOOK_UNIT_TEST_PROBLEM." butp ON butp.problem_num=tdp.problem_num".
	// 		" AND butp.problem_table_type=tdp.problem_table_type".
	// 	    " INNER JOIN ".T_MS_BOOK_UNIT." mbu ON mbu.book_unit_id=butp.book_unit_id".
	// 	    " INNER JOIN ".T_MS_BOOK." mb ON mb.book_id=mbu.book_id".
	// 	    " INNER JOIN test_data_choice tdc ON tdc.test_num=tmp_td.test_num".
	// 		" AND tdc.student_id=tmp_td.student_id".
	// 		" AND tdc.book_unit_id=butp.book_unit_id".
	// 	    " INNER JOIN ".T_STAGE." s ON s.stage_num=u.stage_num".
	// 	    " INNER JOIN ".T_LESSON." l ON l.lesson_num=u.lesson_num ".
	// 	    " WHERE tdc.book_unit_id=bulu.book_unit_id".
	// 	    " AND bulu.mk_flg=0".
	// 	    " AND u.state=0".
	// 	    " AND u.display=1".
	// 	    " AND butp.mk_flg=0".
	// 	    " AND mbu.mk_flg=0".
	// 	    " AND mbu.display=1".
	// 	    " AND mb.mk_flg=0".
	// 	    " AND mb.display=1".
	// 	    " AND s.state=0".
	// 	    " AND s.display=1".
	// 	    " AND l.state=0".
	// 	    " AND l.display=1".
	// 	    " GROUP BY tmp_td.student_id ,tmp_td.test_num, u.unit_num;";
	$temp_sql = " CREATE TEMPORARY TABLE tmp_weak_unit".
		    " SELECT".
		    " tmp_td.student_id, ".
		    " tmp_td.test_num, ".
		    " tmp_td.study_count, ".
		    " u.unit_num,".
		    " u.unit_key,".
		    " COUNT(tdp.problem_num) AS total_count, ".
		    " SUM(IF(tdp.result = 1, 1, 0)) AS count".
		    " FROM ".TEST_DATA_PROBLEM." tdp".
		    " INNER JOIN tmp_test_data tmp_td ON tmp_td.test_num=tdp.test_num".
			" AND tmp_td.student_id=tdp.student_id".
			" AND tmp_td.study_count=tdp.study_count".
		    " INNER JOIN ".T_BOOK_UNIT_LMS_UNIT." bulu ON bulu.problem_num=tdp.problem_num".
		    " INNER JOIN ".T_UNIT." u ON u.unit_num=bulu.unit_num".
		    " INNER JOIN ".T_BOOK_UNIT_TEST_PROBLEM." butp ON butp.problem_num=tdp.problem_num".
			" AND butp.problem_table_type=tdp.problem_table_type".
		    " INNER JOIN ".T_MS_BOOK_UNIT." mbu ON mbu.book_unit_id=butp.book_unit_id".
		    " INNER JOIN ".T_MS_BOOK." mb ON mb.book_id=mbu.book_id".
		    " INNER JOIN test_data_choice tdc ON tdc.test_num=tmp_td.test_num".
			" AND tdc.student_id=tmp_td.student_id".
			" AND tdc.study_count=tmp_td.study_count".
			" AND tdc.book_unit_id=butp.book_unit_id".
		    " INNER JOIN ".T_STAGE." s ON s.stage_num=u.stage_num".
		    " INNER JOIN ".T_LESSON." l ON l.lesson_num=u.lesson_num ".
		    " WHERE tdc.book_unit_id=bulu.book_unit_id".
		    " AND bulu.mk_flg=0".
		    " AND u.state=0".
		    " AND u.display=1".
		    " AND butp.mk_flg=0".
		    " AND mbu.mk_flg=0".
		    " AND mbu.display=1".
		    " AND mb.mk_flg=0".
		    " AND mb.display=1".
		    " AND s.state=0".
		    " AND s.display=1".
		    " AND l.state=0".
		    " AND l.display=1".
		    " GROUP BY tmp_td.student_id, tmp_td.test_num, tmp_td.study_count, u.unit_num;";
	// update end 2020/06/17 yoshizawa 学習管理画面リニューアル TODO 学習回数が実装できたら開放します。
	$cdb->exec_query($temp_sql);


	// 学習コンテンツの存在チェック
	$unit_nums = array();
	$sql = "SELECT * FROM tmp_weak_unit;";
	if ($result = $cdb->query($sql)) {
		while($list = $cdb->fetch_assoc($result)) {
			$unit_nums[$list['unit_num']] = $list['unit_num'];
		}
	}


	if (is_array($unit_nums)) {	// add hasegawa 2018/03/30 課題要望No.659追加修正

		$sql = "SELECT "
			." u.course_num,"
			." u.stage_num,"
			." u.lesson_num,"
			." u.unit_num,"
			." COUNT(b.block_num) AS block_count"
			." FROM ".T_UNIT." u"
			." LEFT JOIN ".T_BLOCK." b ON b.unit_num = u.unit_num"
			." AND b.display='1' AND b.state='0'"
			." WHERE u.unit_num IN ('". implode("','", $unit_nums) . "')"
			." AND u.display='1' AND u.state='0'"
			." GROUP BY u.unit_num"
			.";";

		if ($result = $cdb->query($sql)) {
			while($list = $cdb->fetch_assoc($result)) {
				$check_unit_num_list[$list['unit_num']]['stage_num'] = $list['stage_num'];
				$check_unit_num_list[$list['unit_num']]['lesson_num'] = $list['lesson_num'];
				$check_unit_num_list[$list['unit_num']]['has_drill'] = false;

				if ($list['block_count'] > 0) {
					$check_unit_num_list[$list['unit_num']]['has_drill'] = true;
				}
			}
		}

		$public_school =$GLOBALS['PUBLIC_SCHOOL_ID'][SCHOOLID];

		if (count($check_unit_num_list) > 0) {
			foreach($check_unit_num_list as $unit_num => $val) {

				$has_drill = $val['has_drill'];
				$has_game    = false;
				$has_lecture = false;

				$has_game    = game_file_exist_check($tmp_course_num, $val['stage_num'], $val['lesson_num'], $unit_num, '0');
				$has_lecture = lecture_file_exist_check($tmp_course_num, $val['stage_num'], $val['lesson_num'], $unit_num, '0');

				if (($public_school['public_school'] == "ON" && !$has_game && !$has_drill) ||
					($public_school['public_school'] != "ON" && !$has_game && !$has_lecture && !$has_drill)) {

					$delete_sql = "DELETE FROM tmp_weak_unit WHERE unit_num = '".$unit_num."';";
					$cdb->exec_query($delete_sql);
				}
			}
		}
	} // add hasegawa 2018/03/30 課題要望No.659追加修正


	//データ収集
	$sql = "SELECT".
		" st.student_id AS student_id,".
		" st.student_name AS student_name,".
		" tmp_td.disp_test_num AS test_no,".
		" DATE(tmp_td.start_time) AS test_date,".
		" DATE_FORMAT(tmp_td.start_time , '%H:%i:%s') AS test_date_time,".
		" tmp_tdc.gknn AS gknn,".
		" tmp_td.course_num AS course_num,".
		" c.course_name AS course_name,".
		" tmp_td.score AS score,".
		" SEC_TO_TIME(tmp_td.answer_time) AS answer_time,".
		" tmp_wu.unit_key AS unit_key";

	// update start 2020/06/17 yoshizawa 学習管理画面リニューアル TODO 学習回数が実装できたら開放します。
	// $sql .= " FROM tmp_test_data tmp_td".
	// 	" INNER JOIN tmp_student st ON st.student_id=tmp_td.student_id".
	// 	" LEFT JOIN course c ON c.course_num=tmp_td.course_num".
	// 	" LEFT JOIN tmp_weak_unit tmp_wu ON tmp_wu.student_id=st.student_id".
	// 		" AND tmp_wu.test_num=tmp_td.test_num".
	// 		" AND tmp_wu.total_count > tmp_wu.count".
	// 	" LEFT JOIN tmp_test_data_choice tmp_tdc ON tmp_tdc.student_id=st.student_id".
	// 		" AND tmp_tdc.test_num=tmp_td.test_num".
	// 	" WHERE st.student_id = ".$arg_ary['student_id']." ".
	// 	" AND tmp_td.test_num = ".$arg_ary['test_num']." ".
	// 	" ORDER BY tmp_td.student_id, tmp_td.test_num;";
	$sql .= " FROM tmp_test_data tmp_td".
		" INNER JOIN tmp_student st ON st.student_id=tmp_td.student_id".
		" LEFT JOIN course c ON c.course_num=tmp_td.course_num".
		" LEFT JOIN tmp_weak_unit tmp_wu ON tmp_wu.student_id=st.student_id".
			" AND tmp_wu.test_num=tmp_td.test_num".
			" AND tmp_wu.study_count=tmp_td.study_count".
			" AND tmp_wu.total_count > tmp_wu.count".
		" LEFT JOIN tmp_test_data_choice tmp_tdc ON tmp_tdc.student_id=st.student_id".
			" AND tmp_tdc.test_num=tmp_td.test_num".
			" AND tmp_tdc.study_count=tmp_td.study_count".
		" WHERE st.student_id = ".$arg_ary['student_id']." ".
		" AND tmp_td.test_num = ".$arg_ary['test_num']." ".
		" AND tmp_td.study_count = ".$arg_ary['study_count']." ".
		" ORDER BY tmp_td.student_id, tmp_td.test_num, tmp_td.study_count;";
	// update end 2020/06/17 yoshizawa 学習管理画面リニューアル TODO 学習回数が実装できたら開放します。

	$tmp_unit_key = "";
	$result = $cdb->query($sql);
	while ($list=$cdb->fetch_assoc($result)) {
		if (strlen($tmp_unit_key) > 0) { $tmp_unit_key .= ":"; }
		//$tmp_unit_key = $list['unit_key'];
		$tmp_unit_key .= $list['unit_key'];
	}
	$tmp_review_unit = $tmp_unit_key;

	return $tmp_review_unit;
}



//小テスト
//補強ポイントの取得
function get_test_type3($cdb, $arg_ary, $tmp_course_num) {

	// 受講中の場合、条件追加
	$where ="";
	if ($_SESSION['sub_session']['s_jyukou'] == 1) {	//@@@@@@@
		$where .= " AND (".
			" (school.kmi_zksi='1' AND (tsjj.sito_jyti NOT IN (2,9) AND NOT (tsjj.sito_jyti='0' AND st.secessionday<CURRENT_DATE())))".
			" OR (school.kmi_zksi='2' AND (st.knry_end_flg!='1'))".
			" OR (school.kmi_zksi='3' AND (st.knry_end_flg!='1' AND tsjj.sito_jyti!='2'))".
			" OR (school.kmi_zksi='4' AND (st.knry_end_flg!='1' AND tsjj.sito_jyti!='2'))".
			" OR (school.kmi_zksi='5' AND (tsjj.sito_jyti NOT IN (2,9) AND NOT (tsjj.sito_jyti='0' AND st.secessionday<CURRENT_DATE())))".
			" OR (school.kmi_zksi='6' AND (tsjj.sito_jyti NOT IN (2,9) AND NOT (tsjj.sito_jyti='0' AND st.secessionday<CURRENT_DATE())))".
			" OR (school.kmi_zksi='8' AND (tsjj.sito_jyti NOT IN (2,9) AND NOT (tsjj.sito_jyti='0' AND st.secessionday<CURRENT_DATE())))".
			" OR (school.kmi_zksi='9' AND (tsjj.sito_jyti NOT IN (2,9) AND NOT (tsjj.sito_jyti='0' AND st.secessionday<CURRENT_DATE())))".
			" )";
	}

	//生徒のテンポラリテーブル作成
	$temp_sql = "DROP TEMPORARY TABLE IF EXISTS tmp_student;";
	$cdb->exec_query($temp_sql);

	$temp_sql = "CREATE TEMPORARY TABLE tmp_student".
			" SELECT st.student_id,".
//        "  CONCAT(st.student_myj, \" \", st.student_nme) AS student_name ".
			"  CONCAT(". convertDecryptField('st.student_myj', false) .", \" \", ". convertDecryptField('st.student_nme', false) .") AS student_name ". // kaopiz 2020/08/20 Encoding
			" FROM ".T_STUDENT." st ".
			" INNER JOIN ".T_SCHOOL." school ON st.school_id=school.school_id".
			" INNER JOIN ".T_TB_STU_JYKO_JYOTAI." tsjj ON tsjj.student_id=st.student_id".
			" WHERE st.school_id='".$_SESSION['studentid']['school_id']."'".
			"   AND st.student_id='".$arg_ary['student_id']."'".
			"   AND st.mk_flg='0'".
			"   AND school.mk_flg='0'".
			"   AND tsjj.move_flg='0'".
			"   AND tsjj.mk_flg='0'".
			" ".$where.
		    ";";
	$cdb->exec_query($temp_sql);
//$temp_sql = "XXXXXXXXXXXXXX";

	//test_numに対して学年を取得
	$temp_sql = "DROP TEMPORARY TABLE IF EXISTS tmp_test_data_choice_0;";
	$cdb->exec_query($temp_sql);

	$temp_sql = "CREATE TEMPORARY TABLE tmp_test_data_choice_0".
		    " SELECT ".
		    " st.student_id,".
		    " tdc.test_num,".
		    " tdc.study_count,".	// add 2020/06/17 yoshizawa 学習管理画面リニューアル TODO 学習回数が実装できたら開放します。
		    " tdc.gknn ".
		    " FROM  test_data_choice tdc ".
		    " INNER JOIN tmp_student st ON st.student_id = tdc.student_id".
		    " WHERE tdc.test_type = 1 ".
		    "   AND tdc.mk_flg    = 0 ".
		    ";";
	$cdb->exec_query($temp_sql);


	// group byが遅くなるので、２段階で作成する
	$temp_sql = "DROP TEMPORARY TABLE IF EXISTS tmp_test_data_choice;";
	$cdb->exec_query($temp_sql);
	$query .= $temp_sql."\n";

	// update start 2020/06/17 yoshizawa 学習管理画面リニューアル TODO 学習回数が実装できたら開放します。
	// $temp_sql = "CREATE TEMPORARY TABLE tmp_test_data_choice".
	// 	    " SELECT ".
	// 	    "  student_id,".
	// 	    "  test_num,".
	// 	    " group_concat(gknn) as gknn ".		// 単元で複数学年選択している場合は、カンマで区切る
	// 	    " FROM  tmp_test_data_choice_0 tdc ".
	// 	    " GROUP BY student_id,  test_num ".
	// 	    ";";
	$temp_sql = "CREATE TEMPORARY TABLE tmp_test_data_choice".
		    " SELECT ".
		    "  student_id,".
		    "  test_num,".
		    "  study_count,".
		    " group_concat(gknn) as gknn ".		// 単元で複数学年選択している場合は、カンマで区切る
		    " FROM  tmp_test_data_choice_0 tdc ".
		    " GROUP BY student_id,  test_num,  study_count ".
		    ";";
	// update end 2020/06/17 yoshizawa 学習管理画面リニューアル TODO 学習回数が実装できたら開放します。

	$cdb->exec_query($temp_sql);

//以上は共通


	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
	//add start karasawa 2019/05/16 classi連携
	$school_id = $_SESSION['baseinfo']['school_id'];
	$public_school =$GLOBALS['PUBLIC_SCHOOL_ID'][$school_id];
	$classi_column = "";
	$classi_join = "";
	if($public_school['public_school'] == "ON" && $public_school['school_type'] == "classi" && $public_school['trial'] == "OFF"){
		$classi_column  = create_external_column();
		$classi_join  = create_external_join('st');
	}


	//テストデータ取得
	$temp_sql = "DROP TEMPORARY TABLE IF EXISTS tmp_test_data;";
	$cdb->exec_query($temp_sql);
	$query .= $temp_sql."\n";

	// update start 2020/06/17 yoshizawa 学習管理画面リニューアル TODO 学習回数が実装できたら開放します。
	// $temp_sql = "CREATE TEMPORARY TABLE tmp_test_data".
	// 	    " SELECT st.student_id,".
	// 	    "  td.test_num,".
	// 	    "  td.disp_test_num,".
	// 	    "  td.start_time,".
	// 	    "  td.test_gknn, ".
	// 	    "  td.course_num, ".
	// 	    "  c.course_name ,".
	// 	    "  td.score, ".
	// 	    "  td.answer_time, ".
	// 	    // "  td.target_id, ". //add yamaguchi 2017/5/11 大規模校舎要望対応6 //del yamaguchi 2017/5/15 大規模校舎要望対応6
	// 		"  tgg.target_group_id, ".//add yamaguchi 2017/5/15 大規模校舎要望対応6
	// 	    "  tgg.target_group_name ".//"  tgl.target_group_name ". //add yamaguchi 2017/5/11 大規模校舎要望対応6 //upd yamaguchi 2017/5/15 大規模校舎要望対応6
	// 	    " FROM ".T_TEST_DATA." td ".
	// 	    " INNER JOIN tmp_student st ON st.student_id=td.student_id".
	// 	    " INNER JOIN ".T_COURSE." c ON c.course_num=td.course_num".
	// 	    " LEFT OUTER JOIN ".T_TARGET_LIST." tgl ON tgl.target_list_id=td.target_id". //add yamaguchi 2017/5/11 大規模校舎要望対応6
	// 	    " AND tgl.mk_flg = '0' ". //add yamaguchi 2017/5/15 大規模校舎要望対応6
	// 	    " LEFT OUTER JOIN ".T_TARGET_GROUP." tgg ON tgg.target_group_id=tgl.target_group_id AND tgg.mk_flg = '0' ". //add yamaguchi 2017/5/15 大規模校舎要望対応6
	// 	    " WHERE td.test_type = '3'  ".
	// 	    "   AND td.mk_flg = '0' ".
	// 	    "   AND td.answer_time > '0' ".
	// 	    // "   AND td.test_date BETWEEN '".$SET_TIME['start_time']."' AND '".$SET_TIME['end_time']."'".
	// 	    // "   AND td.end_time BETWEEN '".$SET_TIME['start_time']."' AND '".$SET_TIME['end_time']."'".	//upd yamaguchi 2017/5/11 期間初日のデータが出力されない不具合修正
	// 	    " AND td.test_num='".$arg_ary['test_num']."'".
	// 	    ";";
	$temp_sql = "CREATE TEMPORARY TABLE tmp_test_data".
		    " SELECT st.student_id,".
		    "  td.test_num,".
		    "  td.study_count,".
		    "  td.disp_test_num,".
		    "  td.start_time,".
		    "  td.test_gknn, ".
		    "  td.course_num, ".
		    "  c.course_name ,".
		    "  td.score, ".
		    "  td.answer_time, ".
			"  tgg.target_group_id, ".
		    "  tgg.target_group_name ".
		    " FROM ".T_TEST_DATA." td ".
		    " INNER JOIN tmp_student st ON st.student_id=td.student_id".
		    " INNER JOIN ".T_COURSE." c ON c.course_num=td.course_num".
		    " LEFT OUTER JOIN ".T_TARGET_LIST." tgl ON tgl.target_list_id=td.target_id".
		    " AND tgl.mk_flg = '0' ".
		    " LEFT OUTER JOIN ".T_TARGET_GROUP." tgg ON tgg.target_group_id=tgl.target_group_id AND tgg.mk_flg = '0' ".
		    " WHERE td.test_type = '3'  ".
		    "   AND td.mk_flg = '0' ".
		    "   AND td.answer_time > '0' ".
		    " AND td.student_id='".$arg_ary['student_id']."'".
		    " AND td.test_num='".$arg_ary['test_num']."'".
		    " AND td.study_count='".$arg_ary['study_count']."'".
		    ";";
	// update end 2020/06/17 yoshizawa 学習管理画面リニューアル TODO 学習回数が実装できたら開放します。
	$cdb->exec_query($temp_sql);


	//単元を取得
	// $temp_sql = "DROP TEMPORARY TABLE IF EXISTS tmp_weak_unit;";	// upd hasegawa 2018/03/09 課題要望No.659
	$temp_sql = "DROP TEMPORARY TABLE IF EXISTS _tmp_weak_unit;";
	$cdb->exec_query($temp_sql);
	$query .= $temp_sql."\n";

	// update start 2020/06/17 yoshizawa 学習管理画面リニューアル TODO 学習回数が実装できたら開放します。
	// // $temp_sql = " CREATE TEMPORARY TABLE tmp_weak_unit".		// upd hasegawa 2018/03/09 課題要望No.659
	// $temp_sql = " CREATE TEMPORARY TABLE _tmp_weak_unit".
	// 	    " SELECT".
	// 	    " tmp_td.student_id, ".
	// 	    " tmp_td.test_num, ".
	// 	    " u.unit_num,".
	// 	    " u.unit_key,".
	// 	    " u.list_num,".					// add hasegawa 2018/04/10 課題要望No.666
	// 	    " COUNT(tdp.problem_num) AS total_count, ".
	// 	    " SUM(IF(tdp.result = 1, 1, 0)) AS count".
	// 	    " FROM ".TEST_DATA_PROBLEM." tdp".
	// 	    " INNER JOIN tmp_test_data tmp_td ON tmp_td.test_num=tdp.test_num".
	// 		" AND tmp_td.student_id=tdp.student_id".
	// 	    " INNER JOIN ".T_PROBLEM." p ON p.problem_num=tdp.problem_num".
	// 	    " INNER JOIN ".T_BLOCK." b ON b.block_num=p.block_num".
	// 	    " INNER JOIN ".T_UNIT." u ON u.unit_num=b.unit_num".
	// 	    " INNER JOIN ".T_LESSON." l ON l.lesson_num=u.lesson_num".
	// 	    " INNER JOIN ".T_STAGE." s ON s.stage_num=u.stage_num".
	// 	    " WHERE p.course_num=tmp_td.course_num".
	// 	    // " GROUP BY tmp_td.test_num, u.unit_num;";					// del 2018/08/03 yoshizawa test_numだけだとユニークにならないためstudent_idを追加する。
	// 	    " GROUP BY tmp_td.test_num, tmp_td.student_id, u.unit_num;";	// add 2018/08/03 yoshizawa
	$temp_sql = " CREATE TEMPORARY TABLE _tmp_weak_unit".
		    " SELECT".
		    " tmp_td.student_id, ".
		    " tmp_td.test_num, ".
		    " tmp_td.study_count, ".
		    " u.unit_num,".
		    " u.unit_key,".
		    " u.list_num,".
		    " COUNT(tdp.problem_num) AS total_count, ".
		    " SUM(IF(tdp.result = 1, 1, 0)) AS count".
		    " FROM ".TEST_DATA_PROBLEM." tdp".
		    " INNER JOIN tmp_test_data tmp_td ON tmp_td.test_num=tdp.test_num".
			" AND tmp_td.student_id=tdp.student_id".
			" AND tmp_td.study_count=tdp.study_count".
		    " INNER JOIN ".T_PROBLEM." p ON p.problem_num=tdp.problem_num".
		    " INNER JOIN ".T_BLOCK." b ON b.block_num=p.block_num".
		    " INNER JOIN ".T_UNIT." u ON u.unit_num=b.unit_num".
		    " INNER JOIN ".T_LESSON." l ON l.lesson_num=u.lesson_num".
		    " INNER JOIN ".T_STAGE." s ON s.stage_num=u.stage_num".
		    " WHERE p.course_num=tmp_td.course_num".
		    " GROUP BY tmp_td.test_num, tmp_td.student_id, tmp_td.study_count, u.unit_num;";
	// update end 2020/06/17 yoshizawa 学習管理画面リニューアル TODO 学習回数が実装できたら開放します。
	$cdb->exec_query($temp_sql);


	// add start hasegawa 2018/03/09 課題要望No.659
	$check_unit_num_list = array();
	$unit_nums = array();
	$i = 0;
	// update start 2020/06/17 yoshizawa 学習管理画面リニューアル TODO 学習回数が実装できたら開放します。
	// $insert_head = "INSERT INTO _tmp_weak_unit (student_id, test_num, unit_num, unit_key, list_num, total_count, count) VALUES";
	$insert_head = "INSERT INTO _tmp_weak_unit (student_id, test_num, study_count, unit_num, unit_key, list_num, total_count, count) VALUES";
	// update end 2020/06/17 yoshizawa 学習管理画面リニューアル TODO 学習回数が実装できたら開放します。
	$add_unit = array();


	// 枝番抜きのユニットキーに紐づく単元を取得する
	$sql = "SELECT u.stage_num, u.lesson_num, twu.* FROM _tmp_weak_unit twu".
		" INNER JOIN ".T_UNIT." u ON u.unit_num = twu.unit_num;";

	$query .= $sql."\n";
	if ($result = $cdb->query($sql)) {
		while ($list=$cdb->fetch_assoc($result)) {

			$update_flg = 0;
			$unit_nums[$list['unit_num']] = $list['unit_num'];

			// 枝番を取る
			$search_unit_key = $list['unit_key'];
			if (preg_match('/_[a-zA-Z]$/', $search_unit_key)) {
				$_search_unit_key = explode("_", $search_unit_key);
				$search_unit_key = $_search_unit_key[0];
			// }	// del hasegawa 2018/04/10 課題要望No.666
				// 枝番を取ったユニットキーを検索
				$sql2 = "SELECT * FROM unit";
				$sql2 .= " WHERE stage_num='".$list['stage_num']."'";
				$sql2 .= " AND lesson_num='".$list['lesson_num']."'";
				// $sql2 .= " AND unit_key LIKE '%".$search_unit_key."%'";	// upd hasegawa 2018/04/10 課題要望No.666 unit-Aのみ追加
				$sql2 .= " AND unit_key = '".$search_unit_key."'";
				$sql2 .= " AND display='1' AND state='0';";

				$query .= $sql2."\n";
				if ($result2 = $cdb->query($sql2)) {
					$update_unit_num = array();	// 初期化

					while($list2 = $cdb->fetch_assoc($result2)) {
						if ($list['count'] < $list['total_count']) {

							$update_flg = 1;
							$unit_nums[$list2['unit_num']] = $list2['unit_num'];
							$update_unit_num[$list2['unit_num']] = $list2['unit_num'];

							$insert_value .= "('".$list['student_id']
										."','".$list['test_num']
										."','".$list['study_count']	// add 2020/06/17 yoshizawa 学習管理画面リニューアル TODO 学習回数が実装できたら開放します。
										."','".$list2['unit_num']
										."','".$list2['unit_key']
										."','".$list2['list_num']		// add hasegawa 2018/04/10 課題要望No.666
										."','".$list['total_count']
										."','".$list['count']
									."'),";

							$i++;
							if ($i == 100) {
								$insert_sql = $insert_head.rtrim($insert_value,",").";";
								$cdb->exec_query($insert_sql);
								$query .= $insert_sql."\n";

								$insert_value = "";
								$i = 0;
							}
						}
					}

					// 単元が重複している場合、一つでも復習が必要な場合は復習対象とするため正解数をアップデート
					// if ($update_flg) {	// upd hasegawa 2018/03/30 課題要望No.659追加修正
					if ($update_flg && count($update_unit_num)>0) {
						$update_sql = "UPDATE _tmp_weak_unit SET count = '0'".
								" WHERE student_id = '".$list['student_id']."'".
								" AND test_num = '".$list['test_num']."'".
								" AND study_count = '".$list['study_count']."'".	// add 2020/06/17 yoshizawa 学習管理画面リニューアル TODO 学習回数が実装できたら開放します。
								" AND unit_num IN('". implode("','", $update_unit_num) . "');";

						$cdb->exec_query($update_sql);
						$query .= $update_sql."\n";
					}
				}
			}	// add hasegawa 2018/04/10 課題要望No.666
		}
	}

	if ($i > 0) {
		$insert_sql = $insert_head.rtrim($insert_value,",").";";
		$cdb->exec_query($insert_sql);
		$query .= $insert_sql."\n";
	}

	// 重複単元を消す
	$temp_sql = "DROP TEMPORARY TABLE IF EXISTS tmp_weak_unit;";
	$cdb->exec_query($temp_sql);
	$query .= $temp_sql."\n";

	// update start 2020/06/17 yoshizawa 学習管理画面リニューアル TODO 学習回数が実装できたら開放します。
	// $temp_sql = " CREATE TEMPORARY TABLE tmp_weak_unit".
	// 	    " SELECT * FROM _tmp_weak_unit".
	// 	    " GROUP BY student_id, test_num, unit_num ORDER BY NULL;";
	$temp_sql = " CREATE TEMPORARY TABLE tmp_weak_unit".
		    " SELECT * FROM _tmp_weak_unit".
		    " GROUP BY student_id, test_num, study_count, unit_num ORDER BY NULL;";
	// update end 2020/06/17 yoshizawa 学習管理画面リニューアル TODO 学習回数が実装できたら開放します。
	$cdb->exec_query($temp_sql);


	// 学習コンテンツの存在チェック
	$sql = "SELECT "
		." u.course_num,"
		." u.stage_num,"
		." u.lesson_num,"
		." u.unit_num,"
		." COUNT(b.block_num) AS block_count"
		." FROM ".T_UNIT." u"
		." LEFT JOIN ".T_BLOCK." b ON b.unit_num = u.unit_num"
		." AND b.display='1' AND b.state='0'"
		." WHERE u.unit_num IN ('". implode("','", $unit_nums) . "')"
		." AND u.display='1' AND u.state='0'"
		." GROUP BY u.unit_num"
		.";";

	if ($result = $cdb->query($sql)) {
		while($list = $cdb->fetch_assoc($result)) {
			$check_unit_num_list[$list['unit_num']]['stage_num'] = $list['stage_num'];
			$check_unit_num_list[$list['unit_num']]['lesson_num'] = $list['lesson_num'];
			$check_unit_num_list[$list['unit_num']]['has_drill'] = false;

			if ($list['block_count'] > 0) {
				$check_unit_num_list[$list['unit_num']]['has_drill'] = true;
			}
		}
	}

	$public_school =$GLOBALS['PUBLIC_SCHOOL_ID'][SCHOOLID];

	if (count($check_unit_num_list) > 0) {
		foreach($check_unit_num_list as $unit_num => $val) {

			$has_drill = $val['has_drill'];
			$has_game    = false;
			$has_lecture = false;

			$has_game    = game_file_exist_check($course_num, $val['stage_num'], $val['lesson_num'], $unit_num, '0');
			$has_lecture = lecture_file_exist_check($course_num, $val['stage_num'], $val['lesson_num'], $unit_num, '0');

			if (($public_school['public_school'] == "ON" && !$has_game && !$has_drill) ||
				($public_school['public_school'] != "ON" && !$has_game && !$has_lecture && !$has_drill)) {

				$delete_sql = "DELETE FROM tmp_weak_unit WHERE unit_num = '".$unit_num."';";
				$cdb->exec_query($delete_sql);
				$query .= $delete_sql."\n";
			}
		}
	}


	//add start kimura 2019/04/18 Classi連携 {{{
	$public_school = $GLOBALS['PUBLIC_SCHOOL_ID'][$_SESSION['baseinfo']['school_id']];	// add yoshizawa 2017/06/22 チエル様連携2次開発
	$sql_where = " WHERE 1 ";
	//Classiか?(トライアル校舎は除く) // update oda 2019/05/07 Classi連携 トライアル校舎判断追加
	$is_classi = ($public_school['public_school'] == "ON" && $public_school['school_type'] == "classi" && $public_school['trial'] == "OFF");
	//外部サービスタイプ
	$external_type = null;
	//Classiの場合
	if($is_classi){
		$external_type = 1; // 1:Classi
		//外部サービス連携テーブルから関連付けが有効になっている生徒のみ取得
		$external_student_list = get_assigned_external_students($_SESSION['baseinfo']['school_id'], $external_type);
		if($_SESSION['sub_session']['s_exp_option2'] == checked ){
			$sql_where.= " AND st.student_id IN ('".implode("','", $external_student_list)."')";
		}else{
			$sql_where.= " AND tmp_td.student_id IN ('".implode("','", $external_student_list)."')";
		}
	}



	$sql = "SELECT".
		$classi_column. // add karasawa 2019/05/16 classi連携
		" st.student_id AS '生徒ID',".
//		" CONCAT(st.student_myj, ' ', st.student_nme) AS '生徒名',".
		" st.student_name AS '生徒名',".
		" tmp_td.disp_test_num AS 'テストNo',".
		" DATE(tmp_td.start_time) AS '試験日',".
		" DATE_FORMAT(tmp_td.start_time , '%H:%i:%s') AS '試験時',".
		" tmp_td.test_gknn AS '学年',".
		" tmp_td.course_num AS '教科番号',".
		" tmp_td.course_name AS '教科名',".
		" tmp_td.score AS '得点',".
		" SEC_TO_TIME(tmp_td.answer_time) AS '解答時間',".
		" tmp_wu.unit_key AS '科目別補強ポイント',".
//		" tmp_td.target_id AS '目標No',". //add yamaguchi 2017/5/11 大規模校舎要望対応6
		" tmp_td.target_group_id AS '目標No',". //upd yamaguchi 2017/5/15 大規模校舎要望対応6
		" tmp_td.target_group_name AS '目標名'"; //add yamaguchi 2017/5/11 大規模校舎要望対応6

		// update start 2020/06/17 yoshizawa 学習管理画面リニューアル TODO 学習回数が実装できたら開放します。
		// // add start oda 2019/06/27 classi連携
		// $sql_order = " ORDER BY tmp_td.student_id, tmp_td.test_num, tmp_wu.list_num ";
		// if($is_classi){
		// 	$sql_order = " ORDER BY ess.external_student_id, tmp_td.test_num, tmp_wu.list_num ";
		// }
		// // add end oda 2019/06/27 classi連携
		// classi連携
		$sql_order = " ORDER BY tmp_td.student_id, tmp_td.test_num, tmp_td.study_count, tmp_wu.list_num ";
		if($is_classi){
			$sql_order = " ORDER BY ess.external_student_id, tmp_td.test_num, tmp_td.study_count, tmp_wu.list_num ";
		}
		// update end 2020/06/17 yoshizawa 学習管理画面リニューアル TODO 学習回数が実装できたら開放します。

		$sql .=	" FROM tmp_test_data tmp_td".
			" INNER JOIN tmp_student st ON st.student_id=tmp_td.student_id".
			" LEFT JOIN tmp_weak_unit tmp_wu ON tmp_wu.student_id=st.student_id".
				" AND tmp_wu.test_num=tmp_td.test_num".
				" AND tmp_wu.study_count=tmp_td.study_count".	// add 2020/06/17 yoshizawa 学習管理画面リニューアル TODO 学習回数が実装できたら開放します。
				" AND tmp_wu.total_count > tmp_wu.count ".
			$classi_join.															// add karasawa 2019/05/29 classi連携
			$sql_where.																//add kimura 2019/04/18 Classi連携
			// " ORDER BY tmp_td.student_id, tmp_td.test_num;";						// upd hasegawa 2018/4/10 課題要望No.666
 			// " ORDER BY tmp_td.student_id, tmp_td.test_num, tmp_wu.list_num;";		// del oda 2019/06/27 classi連携
			$sql_order.";";															// add oda 2019/06/27 classi連携


	$tmp_review_unit = "";
	if ($result = $cdb->query($sql)) {
		while ($list=$cdb->fetch_assoc($result)) {
			if ($list['科目別補強ポイント']) {
				if (strlen($tmp_review_unit) > 0) { $tmp_review_unit .= ":"; }
				$tmp_review_unit .= $list['科目別補強ポイント'];
			}
		}
	}

	return $tmp_review_unit;
}
