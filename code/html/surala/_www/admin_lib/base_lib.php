<?PHP
include_once("/data/home/www/userAgent.php");   // kaopiz add 2020/08/15 ipados13
/**
 * ベンチャー・リンク　すらら
 *
 * 管理者用　共通ライブラリ
 *
 * @author Azet
 * @package Admin
 */


// DB接続クラス
include_once(LOG_DIR . "shared_lib/db_connect.php");

//	学年判定共通ファイル
include_once(LOG_DIR . "shared_lib/judge_authority.php");	// add 小学校高学年対応 2017/03/10 yoshizawa
include_once(LOG_DIR . "shared_lib/drill_common.php");//add kimura 2018/06/01 数式パレット新UI設定 //ドリル共通ファンクション用ファイル

include_once(LOG_DIR . "shared_lib/test_standard/test_standard_type4.class.php");	// add hirose 2020/09/11 テスト標準化開発
include_once(LOG_DIR . "shared_lib/test_standard/test_standard_type5.class.php");	// add hirose 2020/09/11 テスト標準化開発
include_once(LOG_DIR . "shared_lib/test_standard/test_standard_type1.class.php");	// add hirose 2020/11/06 テスト標準化開発 定期テスト

// add start oda 2015/07/27 DB接続方法変更
/**
 * -- DB接続 --
 *
 * AC:[T]先生 UC1:[S99]その他.
 *
 * @author Azet
 * @return array
 */
function db_connect() {

	$ERROR = array();

	// DB接続情報チェック
	if (!$_SESSION['dbinfo']) {
		$ERROR[] = "SESSION_TIMEOUT";
		return $ERROR;
	}

	// 分散ＤＢ接続
	$connect_db = new connect_db();
	$connect_db->set_db($_SESSION['dbinfo']);
	$ERROR = $connect_db->set_connect_db();

	if ($ERROR) {
		return $ERROR;
	} else {
		$GLOBALS['cdb'] = $connect_db;		// DB接続情報をグローバル変数に設定
	}

	//// 統合ＤＢ接続
	//include_once(LOG_DIR."db_list.php");
	//if ($L_TOTAL_DB['DBNAME']) {
	//	$connect_db_total = new connect_db();
	//	$connect_db_total->set_db($L_TOTAL_DB);
	//	$ERROR = $connect_db_total->set_connect_db();
	//	// 接続できたらグローバル変数に設定する
	//	if ($ERROR) {
	//		return $ERROR;
	//	} else {
	//		$GLOBALS['scdb'] = $connect_db_total;		// DB接続情報をグローバル変数に設定
	//	}
	//}

	return $ERROR;
}
// add end oda 2015/07/27


/**
 * HTMLのHEAD部分の作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function head() {

	//add okabe start 2015/03/11 StageWebViewBridge.js を molee の場合のみ読み込むようにする
	$include_swvb = "";
	if ($_COOKIE['suralaMoleeMarked'] == "901") { $include_swvb = "swvb_"; }
	//add okabe end 2015/03/11

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	//$make_html->set_file(ADMIN_TEMP_HEAD);	//del okabe 2015/03/11
	$make_html->set_file($include_swvb.ADMIN_TEMP_HEAD);	//add okabe 2015/03/11
	$html = $make_html->read();

	return $html;
}


/**
 * HTMLのFOOT部分の作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function foot() {

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(ADMIN_TEMP_FOOT);
	$html = $make_html->read();

	return $html;
}

/**
 * Check Onetime
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array
 */
function check_onetime() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql  = "SELECT member_id FROM ".T_ONETIMEPASS.
			" WHERE member_id='".$_SESSION['myid']['id']."'" .
			" AND pass='".$_POST['pwd']."' LIMIT 1;";	//	2009/12/04	update by oz
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
	}
	if ($list) {
		$ok = 1;
	} else {
		$ERROR[] = "パスワードが間違っています。";
	}

	return array($ok,$ERROR);
}

/**
 * Check PASSWORD
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function check_pass() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql = "SELECT member_id FROM ".T_ONETIMEPASS.
		" WHERE member_id='".$_SESSION['myid']['id']."'" .
		" AND pass='".$_SESSION['myid']['onetimepass']."' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
	}
	if ($list) {
		unset($INSERT_DATA);
		$INSERT_DATA['update_time'] = "now()";
		$INSERT_DATA['update_ip'] = $_SERVER[REMOTE_HOST];
		$where = " WHERE member_id='".$_SESSION['myid']['id']."'";

		$ERROR = $cdb->update(T_ONETIMEPASS,$INSERT_DATA,$where);
	} else {
		session_error("admin");
	}
}

/**
 * Check PASSWORD
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function check_permission() {
	global $L_MENU,$L_MENU_SUB,$L_MENU_MODE;

	//list($me_num,$onetime,$manager_level,$belong_num,$authority) = explode("<>",$_SESSION[myid]);

	$alert = 0;
	//if ($authority) {
	//	$L_AUTHORITY = explode("::",$authority);
	//	if (array_search(MAIN,$L_AUTHORITY) !== FALSE) { $alert = 1; }
	//}

	if ($_SESSION[authority]) {
		foreach ($_SESSION[authority] as $val) {
			$authority_key = explode("__",$val);
			if (count($authority_key) == 1 && MAIN == $authority_key[0]) {
				$alert = 1;
			} elseif (count($authority_key) == 2 && MAIN == $authority_key[0] && SUB == $authority_key[1]) {
				$alert = 1;
			} elseif (count($authority_key) == 3 && MAIN == $authority_key[0] && SUB == $authority_key[1]) {
				if ($authority_key[2] == "add") {
					if (MODE == "addform" || MODE == "add") { $alert = 1; }
				} elseif ($authority_key[2] == "view") {
					if (MODE == "view" || MODE == "詳細") { $alert = 1; }
				} elseif ($authority_key[2] == "del") {
					if (MODE == "del" || MODE == "削除") { $alert = 1; }
				} elseif ($authority_key[2] == "sort") {
					if (ACTION == "↑" || ACTION == "↓") { $alert = 1; }
					if (ACTION == "block_up" || ACTION == "block_down") { $alert = 1; }
				} elseif ($authority_key[2] == "block_add") {
					if (MODE == "block_form") { $alert = 1; }
				} elseif ($authority_key[2] == "block_view") {
					if (MODE == "block_delete") { $alert = 1; }
				} elseif ($authority_key[2] == "review") {
					if (MODE == "復習設定" || MODE == "review" || MODE == "review_form" || MODE == "review_delete") { $alert = 1; }
				} elseif ($authority_key[2] == "schooltool") {
				}
			}
			if ($alert == 1) { break; }
		}
	}
	if ($alert == 1) {
		unset($_SESSION[myid]);
		unset($_SESSION[authority]);
		unset($_SESSION[select_menu]);
		session_error("authority");
	}
}

/**
 *
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $enterprise_num
 * @return string
 */
function check_status($enterprise_num) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql = "SELECT contract_type FROM " . T_ENTERPRISE . " enterprise," . T_SCHOOL . " school" .
		" WHERE enterprise.enterprise_num='$enterprise_num'" .
		" AND enterprise.enterprise_num=school.enterprise_num" .
		" AND enterprise.state!= '1'" .
		" AND school.state!= '1'" .
		" AND school.contract_type!='3'";
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
	}
	if ($max) { $status = 1; } else { $status = 0; }
	return $status;
}

/**
 * Counter
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $area_num
 * @param integer $enterprise_num
 * @param integer $school_num
 * @return integer
 */
function enterprise_count($area_num,$enterprise_num,$school_num) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (SUB == "area") {
		$sql = "SELECT enterprise_num FROM " . T_ENTERPRISE . " WHERE area_num='$area_num' AND state!='1';";
	}
	if ($sql) {
		if ($result = $cdb->query($sql)) {
			$count = $cdb->num_rows($result);
		}
	}

	return $count;
}

/**
 * 学校数の機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $enterprise_num
 * @param $school_num
 */
function school_count($enterprise_num,$school_num) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (SUB == "enterprise"||SUB == "export") {
		$sql = "SELECT school_num FROM " . T_SCHOOL . " school, " .
			T_ENTERPRISE . " enterprise" .
			" WHERE school.state!='1' AND enterprise.state!='1'" .
			" AND enterprise.enterprise_num=school.enterprise_num" .
			" AND school.enterprise_num='$enterprise_num'";
	}
	if ($sql) {
		if ($result = $cdb->query($sql)) {
			$count = $cdb->num_rows($result);
		}
	}

	return $count;
}

/**
 * 先生数の機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $enterprise_num
 * @param integer $school_num
 * @return integer
 */
function teacher_count($enterprise_num,$school_num) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (SUB == "enterprise") {
		$sql = "SELECT manager.manager_num FROM " .
			T_MANAGER . " manager, " .
			T_AUTHORITY . " authority," .
			T_ENTERPRISE . " enterprise, " .
			T_SCHOOL . " school" .
			" WHERE enterprise.state!='1' AND school.state!='1'" .
			" AND enterprise.enterprise_num=school.enterprise_num" .
			" AND school.enterprise_num='$enterprise_num'" .
			" AND school.school_num=authority.belong_num" .
			" AND manager.manager_num=authority.manager_num" .
			" AND authority.manager_level='5'" .
			" AND manager.state!='1' AND authority.state!='1'";
	} elseif (SUB == "school"||$school_num!==NULL) {
		$sql = "SELECT manager.manager_num FROM " .
			T_MANAGER . " manager, " .
			T_AUTHORITY . " authority," .
			T_ENTERPRISE . " enterprise, " .
			T_SCHOOL . " school" .
			" WHERE enterprise.state!='1' AND school.state!='1'" .
			" AND enterprise.enterprise_num=school.enterprise_num" .
			" AND school.school_num='$school_num'" .
			" AND school.school_num=authority.belong_num" .
			" AND manager.manager_num=authority.manager_num" .
			" AND authority.manager_level='5'" .
			" AND manager.state!='1' AND authority.state!='1'";
	}

	if ($sql) {
		if ($result = $cdb->query($sql)) {
			$count = $cdb->num_rows($result);
		}
	}
	return $count;
}

/**
 * 生徒数の機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $enterprise_num
 * @param integer $school_num
 * @return integer
 */
function student_count($enterprise_num,$school_num) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (SUB == "enterprise") {
		$sql = "SELECT * FROM " . T_STUDENT . " WHERE enterprise_num='$enterprise_num' AND state!='1';";
	} elseif (SUB == "school") {
		$sql = "SELECT * FROM " . T_STUDENT . " WHERE school_num='$school_num' AND state!='1';";
	}
	if ($sql) {
		if ($result = $cdb->query($sql)) {
			$count = $cdb->num_rows($result);
		}
	}

	return $count;
}

/**
 *
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param mixed $number
 * @return string
 */
function make_id($number) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (MAIN == "base"||MAIN == "enterprise_management"||MAIN == "school_management"||MAIN == "teacher_management"
		||MAIN == "user_additional") {
		$BASE_ID = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',
			'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
			'0','1','2','3','4','5','6','7','8','9');

		srand((double) microtime()*1000000);
		for($i=0;$i<10;$i++) {
			$new_id .= $BASE_ID[rand(0,61)];
		}

		$sql = "SELECT manager_id FROM " . T_MANAGER .
			" WHERE manager_id='$new_id'";
		if ($result = $cdb->query($sql)) {
			$count = $cdb->num_rows($result);
		}
		if ($count) { $new_id = ""; }
		if ($new_id == "") { $new_id = make_id(""); }
	} elseif (MAIN == "student_management") {
		if (!$number) {
			$student_num = 1;
		} else {
			$student_num = $number;
		}
		$new_id = date("y") . sprintf("%05d",$student_num);

		for ($i=0;$i<7;$i++) {
			if ($i%2) { $key = 1; } else { $key = 2; }
			$L_IDS[$i] = substr($new_id,$i,1) * $key;
			$wa = $wa + $L_IDS[$i];
		}

		$new_id .= substr($wa,strlen($wa)-1,1);
	}

	return $new_id;
}

/**
 * study_record_etcテブルに追加する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param string $class_m
 * @return string
 */
function study_record_etc($class_m) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$INSERT_DATA['student_num'] = $_SESSION['managerid']['manager_num'];
	$INSERT_DATA['class_m'] = $class_m;
	$INSERT_DATA['course_num'] = $_SESSION['course']['course_num'];
	$INSERT_DATA['unit_num'] = $_SESSION['course']['unit_num'];
	$INSERT_DATA['block_num'] = $_SESSION['course']['block_num'];
	$INSERT_DATA['regist_time'] = "now()";

	$ERROR = $cdb->insert(T_CHECK_STUDY_RECODE,$INSERT_DATA);
	if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::query-&gt;insert::T_STUDY_RECODE"; }
	return $ERROR;
}

// /**
//  * -- ＤＢ接続クラス --
//  * connect_dbクラス
//  *
//  * メソッド一覧
//  * 1. set_db($VAL)
//  * 2. set_connect_db()
//  * 3. get_dbname()
//  * 4. get_db()
//  * 5. set_db_define()
//  * 6. db_close()
//  *
//  * @author Azet
//  */
// class connect_db {
// 	var $dbhost = "";
// 	var $dbname = "";
// 	var $dbuser = "";
// 	var $dbpasswd = "";
// 	var $dbport = "";
// 	var $db = "";
//
// 	/**
// 	* 接続設定
// 	*
// 	* AC:[A]管理者 UC1:[M01]Core管理機能.
// 	*
// 	* @author Azet
// 	* @param array $VAL
// 	*/
// 	function set_db($VAL) {
// 		if ($VAL['DBHOST']) { $this->dbhost = $VAL['DBHOST']; }
// 		if ($VAL['DBNAME']) { $this->dbname = $VAL['DBNAME']; }
// 		if ($VAL['DBUSER']) { $this->dbuser = $VAL['DBUSER']; }
// 		if ($VAL['DBPASSWD']) { $this->dbpasswd = $VAL['DBPASSWD']; }
// 		if ($VAL['DBPORT']) { $this->dbhost .= ":" . $VAL['DBPORT']; }
// 	}
//
// 	/**
// 	 * DB接続
// 	 *
// 	 * AC:[A]管理者 UC1:[M01]Core管理機能.
// 	 *
// 	 * @author Azet
// 	 * @return array エラーがならば
// 	 */
// 	function set_connect_db() {
// 		$this->db = mysql_connect($this->dbhost , $this->dbuser, $this->dbpasswd);
//
// 		if (!$this->db) {
// 			$ERROR[] = "DBに接続できません。";
// 		} else {
// 			mysql_select_db($this->dbname,$this->db);
// 		}
// 		$sql = "SET NAMES utf8;";
// 		if (!mysql_query($sql,$this->db)) { $ERROR[] = "DB設定に失敗しました。"; }
// 		return $ERROR;
// 	}
//
// 	/**
// 	 * 接続のDBの名前
// 	 *
// 	 * AC:[A]管理者 UC1:[M01]Core管理機能.
// 	 *
// 	 * @author Azet
// 	 * @return string
// 	 */
// 	function get_dbname() {
// 		return $this->dbname;
// 	}
//
// 	/**
// 	 * DBの接続のオブジェクト
// 	 *
// 	 * AC:[A]管理者 UC1:[M01]Core管理機能.
// 	 *
// 	 * @author Azet
// 	 * @return mysqlID object
// 	 */
// 	function get_db() {
// 		return $this->db;
// 	}
//
// 	/**
// 	 * DB define設定
// 	 *
// 	 * AC:[A]管理者 UC1:[M01]Core管理機能.
// 	 *
// 	 * @author Azet
// 	 */
// 	function set_db_define() {
// 		if ($this->db) { define('DB',$this->db); }
// 		if ($this->dbname) { define('DBNAME',$this->dbname); }
// 	}
//
// 	/**
// 	 * DB切断
// 	 *
// 	 * AC:[A]管理者 UC1:[M01]Core管理機能.
// 	 *
// 	 * @author Azet
// 	 */
// 	function db_close() {
// 		if ($this->db) {
// 			mysql_close($this->db);
// 		}
// 	}
//
// }

/**
 * すららで復習ユニット取得
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @date 2012/08/20 初期設定
 * @author Azet
 * @param integer $course_num
 * @param integer $stage_num
 * @param integer $lesson_num
 * @param integer $unit_num
 * @param integer $block_num
 * @param integer $problem_num
 * @param integer $one_point_num
 * @return string "::"は値のセパレーター
 */
function get_surala_unit($course_num, $stage_num, $lesson_num, $unit_num, $block_num, $problem_num, $one_point_num) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// add oda
	$surala_list = "";

	// 情報取得
	$sql  = "SELECT * FROM ".T_PROBLEM_LMS_UNIT.
			" WHERE course_num='".$course_num."'".
			"   AND stage_num='".$stage_num."'".
			"   AND lesson_num='".$lesson_num."'".
			"   AND unit_num='".$unit_num."'".
			"   AND block_num='".$block_num."'".
			"   AND problem_num='".$problem_num."'".
			"   AND one_point_num='".$one_point_num."'".
			//update start kimura 2017/12/19 AWS移設 ソートなし → ORDER BY句追加
			//"   AND mk_flg ='0'";
			"   AND mk_flg ='0'".
			"   ORDER BY surala_unit_num"; //すららユニット番号順にソート
			//update end   kimura 2017/12/19
//echo $sql."<br>";

	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			if ($surala_list) {
				$surala_list .= "::";
			}
			$surala_list .= $list['surala_unit_num'];
		}
	}
	return $surala_list;
}

/**
 * すららで復習ユニット更新処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @date 2012/08/20 初期設定
 * @author Azet
 * @param integer $course_num
 * @param integer $stage_num
 * @param integer $lesson_num
 * @param integer $unit_num
 * @param integer $block_num
 * @param integer $problem_num
 * @param integer $one_point_num
 * @param integer $surala_unit_list
 * @return array エラーがならば
 */
function update_surala_unit($course_num, $stage_num, $lesson_num, $unit_num, $block_num, $problem_num, $one_point_num, $surala_unit_list) {

	// 2012/08/20 add oda

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// パラメータのリストをセパレートする
	$ADD_SURALA_UNIT = array();
	if ($surala_unit_list) {
		$ADD_SURALA_UNIT = explode("::",$surala_unit_list);
	}

	// 登録済のすららで復習ユニット情報格納リスト
	$UPDATE_SURALA_UNIT = array();
	$DELETE_SURALA_UNIT = array();

	$sql  = "SELECT * FROM ".T_PROBLEM_LMS_UNIT.
			" WHERE course_num='".$course_num."'".
			"   AND stage_num='".$stage_num."'".
			"   AND lesson_num='".$lesson_num."'".
			"   AND unit_num='".$unit_num."'".
			"   AND block_num='".$block_num."'".
			"   AND problem_num='".$problem_num."'".
			"   AND one_point_num='".$one_point_num."'";
	if ($result = $cdb->query($sql)) {
		$i = 0;
		$j = 0;
		while ($list = $cdb->fetch_assoc($result)) {
			$update_flag = false;
			// 既存データが存在するか確認
			for ($k=0; $k<count($ADD_SURALA_UNIT); $k++) {
				if ($list['surala_unit_num'] == $ADD_SURALA_UNIT[$k]) {
					$UPDATE_SURALA_UNIT[$i] = $list;
					$i++;
					$update_flag = true;
					$ADD_SURALA_UNIT[$k] = "update";
					break;
				}
			}
			// 既存データに存在しない場合は削除対象とする。
			if (!$update_flag) {
				$DELETE_SURALA_UNIT[$j] = $list;
				$j++;
			}
		}
	}

	// 登録処理
	if (count($ADD_SURALA_UNIT) > 0) {
		foreach ($ADD_SURALA_UNIT as $key => $val) {
			if ($val == "update") {
				continue;
			}
			$INSERT_DATA = array();
			$INSERT_DATA['course_num'] 			= $course_num;
			$INSERT_DATA['stage_num'] 			= $stage_num;
			$INSERT_DATA['lesson_num'] 			= $lesson_num;
			$INSERT_DATA['unit_num'] 			= $unit_num;
			$INSERT_DATA['block_num'] 			= $block_num;
			$INSERT_DATA['problem_num'] 		= $problem_num;
			$INSERT_DATA['one_point_num'] 		= $one_point_num;
			$INSERT_DATA['surala_unit_num']		= $val;
			$INSERT_DATA['upd_syr_id']			= 'insertline';
			$INSERT_DATA['upd_tts_id']			= $_SESSION['myid']['id'];
			$INSERT_DATA['upd_date']			= "now()";
			$INSERT_DATA['ins_syr_id']			= 'insertline';
			$INSERT_DATA['ins_tts_id'] 			= $_SESSION['myid']['id'];
			$INSERT_DATA['ins_date'] 			= "now()";

			$ERROR = $cdb->insert(T_PROBLEM_LMS_UNIT,$INSERT_DATA);
		}
		if ($ERROR) {
			return $ERROR;
		}
	}

	// 更新処理
	if (count($UPDATE_SURALA_UNIT) > 0) {
		foreach ($UPDATE_SURALA_UNIT as $key => $val) {
			$INSERT_DATA = array();
			$INSERT_DATA['course_num'] 		= $val['course_num'];
			$INSERT_DATA['stage_num'] 		= $val['stage_num'];
			$INSERT_DATA['lesson_num'] 		= $val['lesson_num'];
			$INSERT_DATA['unit_num'] 		= $val['unit_num'];
			$INSERT_DATA['block_num'] 		= $val['block_num'];
			$INSERT_DATA['problem_num'] 	= $val['problem_num'];
			$INSERT_DATA['one_point_num'] 	= $val['one_point_num'];;
			$INSERT_DATA['surala_unit_num']	= $val['surala_unit_num'];
			$INSERT_DATA['mk_flg']		    = '0';
			$INSERT_DATA['mk_tts_id']		= NULL;
			$INSERT_DATA['mk_date']			= NULL;
			$INSERT_DATA['upd_syr_id']		= 'updateline';
			$INSERT_DATA['upd_tts_id']		= $_SESSION['myid']['id'];
			$INSERT_DATA['upd_date']		= "now()";
			$where  = " WHERE course_num='".$val['course_num']."'";
			$where .= "   AND stage_num='".$val['stage_num']."'";
			$where .= "   AND lesson_num='".$val['lesson_num']."'";
			$where .= "   AND unit_num='".$val['unit_num']."'";
			$where .= "   AND block_num='".$val['block_num']."'";
			$where .= "   AND problem_num='".$val['problem_num']."'";
			$where .= "   AND one_point_num='".$val['one_point_num']."'";
			$where .= "   AND surala_unit_num='".$val['surala_unit_num']."'";

			$ERROR = $cdb->update(T_PROBLEM_LMS_UNIT, $INSERT_DATA, $where);
		}
		if ($ERROR) {
			return $ERROR;
		}
	}

	// 削除処理
	if (count($DELETE_SURALA_UNIT) > 0) {
		foreach ($DELETE_SURALA_UNIT as $key => $val) {
			$INSERT_DATA = array();
			$INSERT_DATA['course_num'] 		= $val['course_num'];
			$INSERT_DATA['stage_num'] 		= $val['stage_num'];
			$INSERT_DATA['lesson_num'] 		= $val['lesson_num'];
			$INSERT_DATA['unit_num'] 		= $val['unit_num'];
			$INSERT_DATA['block_num'] 		= $val['block_num'];
			$INSERT_DATA['problem_num'] 	= $val['problem_num'];
			$INSERT_DATA['one_point_num'] 	= $val['one_point_num'];;
			$INSERT_DATA['surala_unit_num']	= $val['surala_unit_num'];
			$INSERT_DATA['mk_flg']		    = '1';
			$INSERT_DATA['mk_tts_id']		= $_SESSION['myid']['id'];
			$INSERT_DATA['mk_date']			= "now()";
			$INSERT_DATA['upd_syr_id']      = "del";									// 2012/11/05 add oda
			$INSERT_DATA['upd_tts_id']      = $_SESSION['myid']['id'];					// 2012/11/05 add oda
			$INSERT_DATA['upd_date']        = "now()";									// 2012/11/05 add oda
			$where  = " WHERE course_num='".$val['course_num']."'";
			$where .= "   AND stage_num='".$val['stage_num']."'";
			$where .= "   AND lesson_num='".$val['lesson_num']."'";
			$where .= "   AND unit_num='".$val['unit_num']."'";
			$where .= "   AND block_num='".$val['block_num']."'";
			$where .= "   AND problem_num='".$val['problem_num']."'";
			$where .= "   AND one_point_num='".$val['one_point_num']."'";
			$where .= "   AND surala_unit_num='".$val['surala_unit_num']."'";

			$ERROR = $cdb->update(T_PROBLEM_LMS_UNIT, $INSERT_DATA, $where);
		}
		if ($ERROR) {
			return $ERROR;
		}
	}

	return $ERROR;
}

/**
 * すららで復習ユニット削除処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @date 2012/08/22 初期設定
 * @author Azet
 * @param integer $course_num
 * @param integer $stage_num
 * @param integer $lesson_num
 * @param integer $unit_num
 * @param integer $block_num
 * @param integer $problem_num
 * @param integer $one_point_num
 * @return array エラーがならば
 */
function delete_surala_unit($course_num, $stage_num, $lesson_num, $unit_num, $block_num, $problem_num, $one_point_num) {
	// 2012/08/22 add oda

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$INSERT_DATA = array();
	$INSERT_DATA['mk_flg']		    = '1';
	$INSERT_DATA['mk_tts_id']		= $_SESSION['myid']['id'];
	$INSERT_DATA['mk_date']			= "now()";
	$INSERT_DATA['upd_syr_id']      = "del";									// 2012/11/05 add oda
	$INSERT_DATA['upd_tts_id']      = $_SESSION['myid']['id'];					// 2012/11/05 add oda
	$INSERT_DATA['upd_date']        = "now()";									// 2012/11/05 add oda
	$where  = " WHERE course_num='".$course_num."'";
	$where .= "   AND stage_num='".$stage_num."'";
	$where .= "   AND lesson_num='".$lesson_num."'";
	$where .= "   AND unit_num='".$unit_num."'";
	$where .= "   AND block_num='".$block_num."'";
	$where .= "   AND problem_num='".$problem_num."'";
	$where .= "   AND one_point_num='".$one_point_num."'";

	$ERROR = $cdb->update(T_PROBLEM_LMS_UNIT, $INSERT_DATA, $where);

	return $ERROR;
}

/**
 * ユニット変換
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @date 2012/09/27
 * @author Azet
 * @param integer $unit_num
 * @return string unit_num --->  course_num+unit_key
 */
function change_unit_num_to_key($unit_num) {
	// 2012/09/27 add oda

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$course_unitkey = "";

	$sql  = "SELECT course_num,unit_key FROM ".T_UNIT.
			" WHERE unit_num='".$unit_num."'".
			"   AND state='0'";

	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$course_unitkey  = $list['course_num'];
			$course_unitkey .= $list['unit_key'];
		}
	}

	return $course_unitkey;
}

/**
 * ユニット変換
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @date 2012/09/27
 * @author Azet
 * @param string $course_unitkey
 * @return integer course_num+unit_key ---> unit_num
 */
function change_unit_key_to_num($course_unitkey) {
	// 2012/09/27 add oda
	$unit_num = "";

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (isset($course_unitkey) == false ) {
		return $unit_num;
	}

	// update start 2018/05/25 yoshizawa 理科社会対応
	// $course_num = substr($course_unitkey,0,1);
	// $unit_key   = substr($course_unitkey,1);
	//
	// 2ケタ以上のコース管理番号に対応
	preg_match('/(^[0-9]+)([A-Z]*S.+$)/',$course_unitkey,$MATCHES);
	$course_num = $MATCHES['1'];
	$unit_key = $MATCHES['2'];
	// update end 2018/05/25 yoshizawa

	$sql  = "SELECT unit_num FROM ".T_UNIT.
			" WHERE course_num = '".$course_num."'".
			"   AND unit_key = '".$unit_key."'".
			"   AND state='0'";

	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$unit_num  = $list['unit_num'];
		}
	}

	return $unit_num;
}

/**
 * ユニット変換
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * unit_num（::区切り） --->  course_num+unit_key（::区切り）
 * @date 2012/09/27
 * @author Azet
 * @param integer $unit_num
 * @return string
 */
function change_unit_num_to_key_all($unit_num) {
	// 2012/09/27 add oda
	$surala_unit = explode("::",$unit_num);

	// key変換 num->key
	foreach ($surala_unit as $key => $val) {
		$surala_unit[$key] = change_unit_num_to_key($val);
	}

	$unit_num = implode("::",$surala_unit);

	return $unit_num;
}

/**
 * ユニット変換
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * course_num+unit_key（::区切り） ---> unit_num（::区切り）
 * @date 2012/09/27
 * @author Azet
 * @param string $course_unitkey
 * @return string
 */
function change_unit_key_to_num_all($course_unitkey) {
	// 2012/09/27 add oda
	$surala_unit = explode("::",$course_unitkey);

	// key変換 key->num
	foreach ($surala_unit as $key => $val) {
		$surala_unit[$key] = change_unit_key_to_num($val);
	}

	$course_unitkey = implode("::",$surala_unit);

	return $course_unitkey;
}

/**
 * ドリルタイトル名用のコース、ステージ、レッスン、ユニットの名称取得
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @date 2013/11/06
 * @author Azet
 * @param integer $unit_num
 * @return string
 */
function get_drill_title_name($unit_num ) {
	// add koyama

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// SQL生成
	$sql  = "SELECT ";
//	$sql .= "  co.course_name , st.stage_name ,st.stage_name2 , le.lesson_name , le.lesson_name2 , ut.unit_name, ut.unit_name2  ";
	$sql .= "  co.course_name , st.stage_name ,st.stage_name2 , le.lesson_name , le.lesson_name2 , ut.unit_name, ut.unit_name2, ut.remarks  "; //upd hirose 2018/12/17 すらら英単語 remarks追加
	$sql .= " FROM ". T_UNIT." ut ";
	//upd start hirose 2019/1/7 すらら英単語
	//$sql .= "  INNER JOIN ". T_COURSE." co ON ut.course_num = co.course_num AND co.display = '1' AND co.state='0' ";
	//$sql .= "  INNER JOIN ". T_STAGE ." st ON ut.stage_num  = st.stage_num  AND st.display = '1' AND st.state='0' ";
	//$sql .= "  INNER JOIN ". T_LESSON." le ON ut.lesson_num = le.lesson_num AND le.display = '1' AND le.state='0' ";
	$sql .= "  INNER JOIN ". T_COURSE." co ON ut.course_num = co.course_num AND co.state='0' ";
	$sql .= "  INNER JOIN ". T_STAGE ." st ON ut.stage_num  = st.stage_num  AND st.state='0' ";
	$sql .= "  INNER JOIN ". T_LESSON." le ON ut.lesson_num = le.lesson_num AND le.state='0' ";
	//upd end hirose 2019/1/7 すらら英単語
	$sql .= " WHERE ";
	//upd start hirose 2019/1/7 すらら英単語
	//$sql .= "     ut.display='1'";
	//$sql .= " AND ut.state='0'";
	$sql .= " ut.state='0'";
	//upd end hirose 2019/1/7 すらら英単語
	$sql .= " AND ut.unit_num='".$unit_num."'";

//echo $sql."\n<br>\n";

	// データ格納
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$DRILL_TITLE_NAME['course_name']	= replace_decode($list['course_name']);
			$DRILL_TITLE_NAME['stage_name']		= replace_decode($list['stage_name']);
			$DRILL_TITLE_NAME['stage_name2']	= replace_decode($list['stage_name2']);
			$DRILL_TITLE_NAME['lesson_name']	= replace_decode($list['lesson_name']);
			$DRILL_TITLE_NAME['lesson_name2']	= replace_decode($list['lesson_name2']);
			$DRILL_TITLE_NAME['unit_name']		= replace_decode($list['unit_name']);
			$DRILL_TITLE_NAME['unit_name2']		= replace_decode($list['unit_name2']);
			$DRILL_TITLE_NAME['remarks']		= replace_decode($list['remarks']); //add hirose 2018/12/17 すらら英単語
		}
	}
	return $DRILL_TITLE_NAME;
}
//add start kimura 2018/09/28 漢字学習コンテンツ_書写ドリル対応
/**
 * コース番号からwrite_typeを調べる
 *
 * @param  integer $course_num         コース番号
 * @return integer $list['write_type'] ライトタイプ
 */
function get_write_type_by_course($course_num){
	$cdb = $GLOBALS['cdb'];
	$sql = "SELECT write_type FROM ".T_COURSE." WHERE course_num = ".$course_num." LIMIT 1";
	if($result = $cdb->query($sql)){ $list = $cdb->fetch_assoc($result); }
	return $list['write_type'];
}
//add end   kimura 2018/09/28 漢字学習コンテンツ_書写ドリル対応

//add start kimura 2018/11/15 すらら英単語 _ワード管理
/**
 * テスト種類番号からwrite_typeを調べる
 *
 * @param  integer $test_type_num      テスト種類番号
 * @return integer $list['write_type'] ライトタイプ
 */
function get_write_type_by_test_type_num($test_type_num){
	$cdb = $GLOBALS['cdb'];
	$sql = "SELECT write_type FROM ".T_MS_TEST_TYPE." WHERE test_type_num = ".$test_type_num." LIMIT 1";
	if($result = $cdb->query($sql)){ $list = $cdb->fetch_assoc($result); }
	return $list['write_type'];
}

//add end   kimura 2018/11/15 すらら英単語 _ワード管理

// add start 2018/11/15 yoshizawa 漢字学習コンテンツ すらら英単語
/**
 * コース番号からサービス情報取得
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $course_num
 * @return integer
 */
function get_service_num($course_num) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$service_num = "";

	$sql  = "SELECT ";
	$sql .= "  sc.service_num ";
	$sql .= "  ,sc.setup_type ";
	$sql .= "  ,scl.course_num ";
	$sql .= " FROM ".T_SERVICE_COURSE_LIST. " scl ";
	$sql .= " INNER JOIN ".T_SERVICE. " sc ON scl.service_num = sc.service_num ";
	$sql .= " WHERE scl.mk_flg = '0' ";
	$sql .= "   AND scl.course_num = '".$course_num."' ";
	$sql .= "   AND scl.course_type  = '1' ";	// コースタイプ（１：授業　２：速習　３：テスト）
	$sql .= ";";

	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$service_num = $list['service_num'];
		}
	}

	return $service_num;
}
// add end 2018/11/15 yoshizawa 漢字学習コンテンツ すらら英単語
//add start kimura 2018/11/16 すらら英単語 _ワード管理
/**
 * コース番号からサービス番号取得(すらら英単語テスト専用)
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $test_type_num
 * @return integer
 */
function get_tango_test_service_num($test_type_num) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$service_num = "";

	$sql  = "SELECT ";
	$sql .= "  sc.service_num ";
	$sql .= " FROM ".T_SERVICE_COURSE_LIST. " scl ";
	$sql .= " INNER JOIN ".T_SERVICE. " sc ON scl.service_num = sc.service_num ";
	$sql .= " WHERE scl.mk_flg = '0' ";
	$sql .= "   AND scl.course_num = '".$test_type_num."' ";
	$sql .= "   AND scl.course_type  = '4' ";// コースタイプ(4: すらら英単語テスト)
	$sql .= ";";

	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$service_num = $list['service_num'];
		}
	}

	return $service_num;
}
//add end   kimura 2018/11/16 すらら英単語 _ワード管理
//add start kimura 2018/11/15 すらら英単語 _ワード管理
/**
 * コース番号の末尾に「T」があるか調べる
 * ワード管理にしか使ってません。2018/11/16時点
 *
 * @param  string $course_num_str
 * @return array  [boolean $is_test_type_num テストか否か, integer $test_type_num テスト種類番号]
 */
function is_test_type_num($course_num_str){
	$is_test_type_num = false;
	$test_type_num = null;

	if(preg_match("/^(\d*)T$/", $course_num_str, $matches)){
		$test_type_num = $matches[1];
		$is_test_type_num = true;
	}
	return array($is_test_type_num, $test_type_num);
}
//add end   kimura 2018/11/15 すらら英単語 _ワード管理
//add start kimura 2018/11/16 すらら英単語 _ワード管理
/**
 * コース番号をもとにワード管理用の情報一式を配列で得る
 * ワード管理にしか使ってません。2018/11/16時点
 *
 * @param  string $english_word_course_num ワード管理は入り口のコース選択以降のcourse_numに、テストの場合はわざとTをつけて扱っています。（これが識別子）
 * @return array  [0 => int 教科, 1 => int テストかどうか, 2 => テスト種類番号, 3 => サービス番号]
 */
function get_english_word_info($english_word_course_num){

	$write_type    = null; //教科
	$is_test       = null; //テストであるか
	$test_type_num = null; //テスト種類管理番号
	$service_num   = null; //サービス番号

	list($is_test, $test_type_num) = is_test_type_num($english_word_course_num);

	//テスト
	if($is_test){
		$write_type = get_write_type_by_test_type_num($test_type_num);
		$service_num = get_tango_test_service_num($test_type_num);
	//授業
	}else{
		$write_type = get_write_type_by_course($_POST['course_num']);
		$service_num = get_service_num($_POST['course_num']);
	}
// echo "WRITE_TYPE:<span style=color:red>".$write_type."</span> IS_TEST:<span style=color:red>".($is_test ? 1 : 0)."</span> TEST_TYPE_NUM:<span style=color:red>".$test_type_num."</span> SERVICE_NUM:<span style=color:red>".$service_num."</span>";//DEBUG
	return array($write_type, ($is_test ? 1 : 0), $test_type_num, $service_num);
}
//add end   kimura 2018/11/16 すらら英単語 _ワード管理

//add start kimura 2018/11/21 すらら英単語 _admin
//LMS単元取り扱い用======================================{{{
/**
 * LMS単元文字列を::区切りで配列にする
 * @param  string $lms_unit_ids 1000::1001::1002
 * @return array  $lms_id_arr   [1000,1001,1002]
 */
function lms_ids_to_array($lms_unit_ids){
	if($lms_unit_ids == ""){ return array(); }
	return explode("::", $lms_unit_ids);
}

/**
 * ある問題に対するLMS単元IDを全件取得する
 * @param  integer $problem_num        問題管理番号
 * @param  integer $problem_table_type 問題テーブルタイプ
 * @return array   $lms_id             [0 => (int) id, 1 => (int) id, ... ]
 */
function get_all_lms_unit_ids_for_problem($problem_num, $problem_table_type){
	$cdb = $GLOBALS['cdb'];

	$sql = "SELECT * FROM ".T_BOOK_UNIT_LMS_UNIT;
	$sql.= " WHERE mk_flg = '0'";
	$sql.= " AND book_unit_id = '0'";
	$sql.= " AND problem_table_type = '".$problem_table_type."'";
	$sql.= " AND problem_num = '".$problem_num."'";

	$lms_ids = array();
	if($result = $cdb->query($sql)){
		while($list = $cdb->fetch_assoc($result)){
			$lms_ids[] = $list['unit_num'];
		}
	}
	return $lms_ids;
}

/**
 * 問題に対する対象のLMS単元が存在するかチェックする
 * @param  integer $lms_unit_id        LMS単元ID(ユニット番号)
 * @param  integer $problem_num        問題管理番号
 * @param  integer $problem_table_type 問題テーブルタイプ
 * @return boolean
 */
function lms_unit_id_exists($lms_unit_id, $problem_num, $problem_table_type){
	$cdb = $GLOBALS['cdb'];

	$sql = "SELECT * FROM ".T_BOOK_UNIT_LMS_UNIT;
	// $sql.= " WHERE mk_flg = '0'";
	$sql.= " WHERE 1";
	$sql.= " AND book_unit_id = '0'";
	$sql.= " AND problem_table_type = '".$problem_table_type."'";
	$sql.= " AND problem_num = '".$problem_num."'";
	$sql.= " AND unit_num = '".$lms_unit_id."'";

	$rows = 0;
	if($result = $cdb->query($sql)){
		$rows = $cdb->num_rows($result);
	}
	return ($rows > 0 ? true : false);
}

/**
 * LMS単元ID更新
 * @param  integer $lms_unit_id        LMS単元ID(ユニット番号)
 * @param  integer $problem_num        問題管理番号
 * @param  integer $problem_table_type 問題テーブルタイプ
 * @return array   $ERROR              [0 => (string) エラー]
 */
function update_lms_unit_id($lms_unit_id, $problem_num, $problem_table_type){

	$cdb = $GLOBALS['cdb'];
	$ERROR = array();
	if($lms_unit_id == ""){ return; }

	$INSERT_DATA = array();
	$INSERT_DATA['mk_tts_id'] = ""; //無効担当者
	$INSERT_DATA['mk_date'] = "0000-00-00 00:00:00"; //無効日
	$INSERT_DATA['mk_flg'] = 0; //無効フラグON

	$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
	$INSERT_DATA['upd_date'] = "now()";
	$where = " WHERE 1";
	$where.= " AND problem_num = '".$problem_num."'";
	$where.= " AND problem_table_type = '".$problem_table_type."'";
	$where.= " AND unit_num = '".$lms_unit_id."'";
	$where.= " AND book_unit_id ='0'";
	$ERROR = $cdb->update(T_BOOK_UNIT_LMS_UNIT, $INSERT_DATA, $where);
	return $ERROR;
}

/**
 * LMS単元ID挿入
 * @param  integer $lms_unit_id        LMS単元ID(ユニット番号)
 * @param  integer $problem_num        問題管理番号
 * @param  integer $problem_table_type 問題テーブルタイプ
 * @return array   $ERROR              [0 => (string) エラー]
 */
function insert_lms_unit_id($lms_unit_id, $problem_num, $problem_table_type){

	$cdb = $GLOBALS['cdb'];
	$ERROR = array();
	if($lms_unit_id == ""){ return; }

	$INSERT_DATA = array();
	$INSERT_DATA['problem_num'] = $problem_num;
	$INSERT_DATA['problem_table_type'] = $problem_table_type;
	$INSERT_DATA['unit_num'] = $lms_unit_id;
	$INSERT_DATA['mk_tts_id'] = "";
	$INSERT_DATA['mk_date'] = "0000-00-00 00:00:00";
	$INSERT_DATA['ins_tts_id'] = $_SESSION['myid']['id'];
	$INSERT_DATA['ins_date'] = "now()";
	$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
	$INSERT_DATA['upd_date'] = "now()";
	$ERROR = $cdb->insert(T_BOOK_UNIT_LMS_UNIT, $INSERT_DATA);
	return $ERROR;
}

/**
 * LMS単元ID削除
 * @param  integer $lms_unit_id        LMS単元ID(ユニット番号)
 * @param  integer $problem_num        問題管理番号
 * @param  integer $problem_table_type 問題テーブルタイプ
 * @return array   $ERROR              [0 => (string) エラー]
 */
function delete_lms_unit_id($lms_unit_id, $problem_num, $problem_table_type){

	$cdb = $GLOBALS['cdb'];
	$ERROR = array();
	if($lms_unit_id == ""){ return; }

	$INSERT_DATA = array();
	$INSERT_DATA['mk_tts_id'] = $_SESSION['myid']['id']; //無効担当者
	$INSERT_DATA['mk_date'] = "now()"; //無効日
	$INSERT_DATA['mk_flg'] = 1; //無効フラグON

	$where = " WHERE 1";
	$where.= " AND problem_num = '".$problem_num."'";
	$where.= " AND problem_table_type = '".$problem_table_type."'";
	$where.= " AND unit_num = '".$lms_unit_id."'";
	$where.= " AND book_unit_id ='0'";
	$ERROR = $cdb->update(T_BOOK_UNIT_LMS_UNIT, $INSERT_DATA, $where);
	return $ERROR;
}

/**
 * LMS単元番号を保存する
 *
 * @param  integer $lms_unit_id        LMS単元ID(ユニット番号)
 * @param  integer $problem_num        問題管理番号
 * @param  integer $problem_table_type 問題テーブルタイプ
 * @return array   $ERROR              [0 => (string) エラー]
 */
function save_lms_ids($lms_unit_ids, $problem_num, $problem_table_type){

	$ERROR = array();
	//現在問題に紐づいているLMS番号を全て取得
	$all_lms_unit_ids = get_all_lms_unit_ids_for_problem($problem_num, $problem_table_type);
	//区切りでばらして配列化
	$lms_unit_id_arr = lms_ids_to_array($lms_unit_ids);
	// if(empty($lms_unit_id_arr) || $lms_unit_id_arr === false){ return; } //配列が空だったら終了
	//すべてのIDと更新対象IDとの差が削除対象となる
	$deleted_ids = array_diff($all_lms_unit_ids, $lms_unit_id_arr);
	unset($all_lms_unit_ids); //diffし終わったら要らないのでメモリ開放しとく
	//新規挿入・更新処理
	if(is_array($lms_unit_id_arr) && !empty($lms_unit_id_arr)){
		foreach($lms_unit_id_arr as $id){
			//存在したらアップデート
			if(lms_unit_id_exists($id, $problem_num, $problem_table_type)){
				// echo "UPDATE LMS UNIT:{$id}"; //########## debug
				$ERROR = update_lms_unit_id($id, $problem_num, $problem_table_type);
				//無ければインサート
			}else{
				// echo "INSERT LMS UNIT:{$id}"; //########## debug
				$ERROR = insert_lms_unit_id($id, $problem_num, $problem_table_type);
			}
		}
	}
	unset($id);
	//削除処理
	if(is_array($deleted_ids) && !empty($deleted_ids)){
		foreach($deleted_ids as $id){
			// echo "DELETE:{$id}";
			$ERROR = delete_lms_unit_id($id, $problem_num, $problem_table_type);
		}
	}
	return $ERROR;
}

/**
 * LMS単元の入力文字のバリデーション
 * @param  string $lms_unit_id_string
 * @return string/integer エラーのとき:string "エラーメッセージ" / エラーなしのとき:integer(0)
 */
function validate_lms_id_string($lms_unit_id_string){
	if($lms_unit_id_string == ""){ return 0; }

	//半角数字と:の組み合わせでない
	if(!preg_match("/^[0-9:]*$/", $lms_unit_id_string)){
		return "LMS単元は半角数字で入力してください。";
	}
	//::で区切ったあとの個々の要素に数値以外がある
	$lms_id_arr = lms_ids_to_array($lms_unit_id_string);
	foreach($lms_id_arr as $id){
		if(!preg_match("/^\d+$/", $id)){ return "LMS単元IDの形式が不正です。"; }
	}
	//すべてのユニットが有効でない
	$err_units = check_lms_units_unavailable($lms_id_arr);
	if(!empty($err_units)){
		return "有効でないLMS単元IDが含まれています。 ".implode(", ", $err_units);
	}
	//エラーなし: int(0)
	return 0;
}

/**
 * LMS単元ユニットの存在バリデーション
 * @param  string  $lms_unit_id_string
 * @return boolean true:OK / false:だめ
 */
function check_lms_units_unavailable($lms_unit_id_arr){

	$cdb = $GLOBALS['cdb'];

	$sql = "SELECT unit_num FROM ".T_UNIT;
	$sql.= " WHERE state = '0' AND display='1'";
	$sql.= " AND course_num = 1"; //★英語だけLMS単元として登録可
	$sql.= " AND unit_num IN (".implode(",", $lms_unit_id_arr).")";

	$error_unit_nums = array();
	$ok = array();

	if($result = $cdb->query($sql)){
		while($list = $cdb->fetch_assoc($result)){
			$ok[] = $list['unit_num'];
		}
	}
	$error_unit_nums = array_diff($lms_unit_id_arr, $ok);
	return $error_unit_nums;
}
//LMS単元取り扱い用======================================}}}
//add end   kimura 2018/11/21 すらら英単語 _admin

//add start hirose 2018/12/19 すらら英単語
//ユーザーエージェントから、apple製品のチェック
function apple_check(){
	$result = false;
	//if (preg_match("/iPad|iPhone|Macintosh/i", $_SERVER['HTTP_USER_AGENT']) === 1) {
	if (preg_match("/iPad|iPhone|Macintosh/i", $_SERVER['HTTP_USER_AGENT']) === 1 || is_ipad()) { //kaopiz update 2020/07/15 ipados13
		$result = true;
	}
	return $result;
}
//add end hirose 2018/12/19 すらら英単語

//add start kimura 2019/07/24 生徒画面TOP改修_ログ出力 {{{
/**
 * エントリーDBに接続して接続オブジェクトを返す
 *
 * @return object $conn エントリーDB接続オブジェクト
 */
function get_entry_connection(){

	$ENTDBINFO = get_entry_connection_info();

	$conn = new connect_db();
	$conn->set_db($ENTDBINFO);
	$ERROR = $conn->set_connect_db();

	if(!empty($ERROR)){
		return -1;
	}

	return $conn;
}

/**
 * エントリーDB接続情報を返す(iniから)
 *
 * @return object $conn エントリーDB接続情報
 */
function get_entry_connection_info(){

	$ENTDBINFO = array();

	$ini_file = get_cfg_var('SURALA_ENTRY_INI');
	if (!is_file($ini_file) ) {
		return -1;
	}

	$arrini = parse_ini_file($ini_file, true);
	$arr = array();
	if(!empty($arrini) && is_array($arrini)){
		foreach($arrini as $key => $values) {
			if (is_array($values)) {
				if (count($values) > 0) {
					foreach($values as $key2 => $value2) {
						$arr[trim($key2)] = trim($value2);
					}
				}
			} else {
				$arr[trim($key)] = trim($values);
			}
		}
	}

	$server  = (isset($arr['db_host'])) ? $arr['db_host'] : '';
	$db_name = (isset($arr['db_name'])) ? $arr['db_name'] : '';
	$db_user = (isset($arr['db_user'])) ? $arr['db_user'] : '';
	$db_pass = (isset($arr['db_pass'])) ? $arr['db_pass'] : '';
	$db_port = (isset($arr['db_port'])) ? $arr['db_port'] : '';

	$ENTDBINFO['NAME'] = $db_name;
	$ENTDBINFO['DBHOST'] = $server;
	$ENTDBINFO['DBUSER'] = $db_user;
	$ENTDBINFO['DBPASSWD'] = $db_pass;
	$ENTDBINFO['DBNAME'] = $db_name;
	$ENTDBINFO['DBPORT'] = $db_port;

	return $ENTDBINFO;
}
//add end   kimura 2019/07/24 生徒画面TOP改修_ログ出力 }}}
// add start 2019/07/29 yoshizawa adminアップデートリストのデプロイチェック
/**
 * 実行中のデプロイがないかチェックいたします。
 * @param  string  $mode
 * @return boolean true:実行中 / false:実行無し
 */
function check_deploy_running($mode="Core"){

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$running = false;

	if($mode == "Game"){ $like = "Game"; }

	// 実行中のデプロイがないかチェック
	// deploy_log.state 0:未実施 10:実行中 20:完了（正常終了） 30:途中終了
	$sql = "SELECT * FROM deploy_log WHERE state IN ('0','10','30') AND deploy_mode LIKE '%".$mode."%';";
	if ($result = $cdb->query($sql)) {
		if($cdb->num_rows($result) > 0){
			$running = true;
		}
	}

	return $running;

}
// add end 2019/07/29 yoshizawa adminアップデートリストのデプロイチェック
//add start kimura 2019/09/19 漢字学習コンテンツFB対応 {{{
/**
 * 複数サービスに対して、それぞれの子コース名をすべて得る
 * student_lib/base_lib.phpからコピー 2019/09/19
 *
 * @param  array $service_num_arr
 * @return array $LIST
 */
function get_course_names_by_service($service_num_arr){

	if(empty($service_num_arr)){ return; }
	$cdb = $GLOBALS['cdb'];

	$LIST = array();

	$sql = " SELECT " .
			" sc.service_num, scl.course_num, co.course_name " .
			" FROM " .T_SERVICE. " sc ".
			" INNER JOIN ".T_SERVICE_COURSE_LIST. " scl ON sc.service_num = scl.service_num AND scl.display = '1' AND scl.mk_flg = '0' ".
			" INNER JOIN ".T_COURSE. " co ON scl.course_num = co.course_num AND co.display = '1' AND co.state = '0' ".
			" WHERE sc.service_num IN (".implode(",", $service_num_arr).")" .
			"   AND sc.display = '1' " .
			"   AND sc.mk_flg = '0' " .
			" ORDER BY co.list_num " .
			";";

	if ($result = $cdb->query($sql)) {
		while($list = $cdb->fetch_assoc($result)) {
			$LIST[$list['service_num']][$list['course_num']] = $list['course_name'];
		}
	}

	return $LIST;
}
//add end   kimura 2019/09/19 漢字学習コンテンツFB対応 }}}
//add start hirose 2019/11/07 別教科ユニットサジェスト開発
/**
 * サジェストユニット一覧のSQLを返す
 * @param int $unit_num
 * @return string
 */
function check_suggest_sql($unit_num){

	if($unit_num){
		$sql = " SELECT " .
				"us.unit_num" .
				",us.suggest_unit_num " .
				",lesson.lesson_num " .
				",lesson.lesson_name " .
				",stage.stage_num " .
				",stage.stage_name " .
				",stage.stage_key " .
				",course.course_num " .
				",course.course_name " .
				",unit.unit_name " .
				",unit.unit_name2 " .
				",count(problem.problem_num)as problem_count " .
				" FROM " . T_UNIT_SUGGEST . " us " .
				" INNER JOIN " . T_UNIT . " unit ON us.suggest_unit_num = unit.unit_num" .
				" INNER JOIN " . T_LESSON . " lesson ON unit.lesson_num = lesson.lesson_num" .
				" INNER JOIN " . T_STAGE . " stage ON lesson.stage_num = stage.stage_num" .
				" INNER JOIN " . T_COURSE . " course ON stage.course_num = course.course_num" .
				" INNER JOIN ".T_SERVICE_COURSE_LIST." scl ON course.course_num = scl.course_num AND scl.course_type = 1 AND scl.mk_flg = 0 ".
				" INNER JOIN ".T_SERVICE." sc ON scl.service_num = sc.service_num AND sc.setup_type_sub = 1 AND sc.mk_flg = 0 ".
				" LEFT JOIN ".T_BLOCK." block ON unit.unit_num = block.unit_num AND block.state = 0 ".
				" LEFT JOIN ".T_PROBLEM." problem ON block.block_num = problem.block_num AND problem.state = 0 ".
				" WHERE us.mk_flg='0'" .
				"   AND us.unit_num = '" . $unit_num . "'" .
				"   AND unit.state = 0" .
				"   AND lesson.state = 0" .
				"   AND stage.state = 0" .
				"   AND course.state = 0" .
//				"   AND unit.display = 1".
//				"   AND lesson.display = 1" .
//				"   AND stage.display = 1" .
//				"   AND course.display = 1" .
				"   GROUP by unit.unit_num" .
				" ;";
	}else{
		$sql = "";
	}

	return $sql;
}
/**
 * サジェストのメッセージ表示
 *
 * @param int $display_type 1:すらら 2:新TOP 3:ピタドリ
 * @param string $school_year L:低学年 P:高学年 J:中学 H:高校
 * @return string
 */
function get_message($display_type,$school_year = NULL){
	if($display_type == 2){
		$messege = '<ruby>今学習<rp>(</rp><rt>いまがくしゅう</rt><rp>)</rp></ruby>した<ruby>内容<rp>(</rp><rt>ないよう</rt><rp>)</rp></ruby>は、<ruby>他<rp>(</rp><rt>ほか</rt><rp>)</rp></ruby>の<ruby>教科<rp>(</rp><rt>きょうか</rt><rp>)</rp></ruby>にも<ruby>関連<rp>(</rp><rt>かんれん</rt><rp>)</rp></ruby>しているところがあるよ。あわせて<ruby>学習<rp>(</rp><rt>がくしゅう</rt><rp>)</rp></ruby>してみよう。';
	}elseif($display_type == 1){
		//低学年だったらルビを振る。
		if($school_year == 'L'){
			$messege = '<ruby>今学習<rp>(</rp><rt>いまがくしゅう</rt><rp>)</rp></ruby>した<ruby>内容<rp>(</rp><rt>ないよう</rt><rp>)</rp></ruby>は、<ruby>他<rp>(</rp><rt>ほか</rt><rp>)</rp></ruby>の<ruby>教科<rp>(</rp><rt>きょうか</rt><rp>)</rp></ruby>にも<ruby>関連<rp>(</rp><rt>かんれん</rt><rp>)</rp></ruby>しているところがあるよ。<br>あわせて学習してみよう。';
		}else{
			$messege = '今学習した内容は、他の教科にも関連しているところがあるよ。<br>あわせて学習してみよう。';
		}
	}else{
		$messege = '今学習した内容は、他の教科にも関連しているところがあるよ。<br>あわせて学習してみよう。';
	}
	return $messege;
}
//add end hirose 2019/11/07 別教科ユニットサジェスト開発

//add start hirose 2020/08/26 テスト標準化開発
/**
 * 海外校舎のコース名を取得
 *
 * @return array
 */
function get_overseas_course_name(){

	$cdb = $GLOBALS['cdb'];

	$sql = " SELECT " .
			" c.course_num " .
			" ,c.course_name " .
			" FROM " .T_COURSE. " c ".
			" WHERE c.course_num IN ('11','12','13','14')" .
			"   AND c.state = '0' " .
			"   AND c.display = '1' " .
			";";


	$COURSE_LIST = array();
	if ($result = $cdb->query($sql)) {
		while($list = $cdb->fetch_assoc($result)) {
			$COURSE_LIST[$list['course_num']] = $list['course_name'];
		}
	}
	return $COURSE_LIST;

}
//add end hirose 2020/08/26 テスト標準化開発

//kaopiz 2020/08/25 speech start
function insert_external_course($courseNum, $option1)
{
    $cdb = $GLOBALS['cdb'];

    $insertData['course_num'] = $courseNum;
    $insertData['option1'] = $option1;
    $insertData['ins_tts_id'] = $_SESSION['myid']['id'];
    $insertData['ins_date'] = "now()";
    $error = $cdb->insert(T_EXTERNAL_COURSE, $insertData);
    return $error;
}

function insert_external_unit($unitNum, $courseNum, $stageNum, $lessonNum, $option1)
{
    $cdb = $GLOBALS['cdb'];

    $insertData['unit_num'] = $unitNum;
    $insertData['course_num'] = $courseNum;
    $insertData['stage_num'] = $stageNum;
    $insertData['lesson_num'] = $lessonNum;
    $insertData['option1'] = $option1;
	$insertData['ins_tts_id'] = $_SESSION['myid']['id'];
    $insertData['ins_date'] = "now()";
    $error = $cdb->insert(T_EXTERNAL_UNIT, $insertData);
    return $error;
}

function getData($db, $sql) {
    $result = $db->query($sql);
    $data = $db->fetch_assoc($result);
    return $data;
}

function findExternalCourse($courseId)
{
    $sql = "SELECT * FROM ".T_EXTERNAL_COURSE." WHERE course_num = {$courseId} LIMIT 1";
    return getData($GLOBALS['cdb'], $sql);
}

function findExternalUnit($unitId)
{
    $sql = "SELECT * FROM ".T_EXTERNAL_UNIT." WHERE unit_num = {$unitId} LIMIT 1";
    return getData($GLOBALS['cdb'], $sql);
}

function findExternalProblem($problemId)
{
	$sql = "SELECT * FROM ".T_EXTERNAL_PROBLEM." WHERE problem_num = {$problemId} LIMIT 1";
	return getData($GLOBALS['cdb'], $sql);
}

function findExternalCourseNotDelete($courseId)
{
    $sql = "SELECT * FROM ".T_EXTERNAL_COURSE." WHERE course_num = {$courseId} AND move_flg !='1'  LIMIT 1";
    return getData($GLOBALS['cdb'], $sql);
}

function findExternalUnitNotDelete($unitId)
{
    $sql = "SELECT * FROM ".T_EXTERNAL_UNIT." WHERE unit_num = {$unitId} AND move_flg !='1'  LIMIT 1";
    return getData($GLOBALS['cdb'], $sql);
}

function findExternalProblemNotDelete($problemId)
{
	$sql = "SELECT * FROM ".T_EXTERNAL_PROBLEM." WHERE problem_num = {$problemId} AND move_flg !='1' LIMIT 1";
	return getData($GLOBALS['cdb'], $sql);
}

function getCourseByNum($courseNum)
{
	$sql = "SELECT * FROM ".T_COURSE." WHERE course_num = {$courseNum} AND display = '1' AND state = '0'  LIMIT 1";
	return getData($GLOBALS['cdb'], $sql);
}

function insertExternalProblem($data)
{
	$cdb = $GLOBALS['cdb'];

	$insertData = convertExternalProblemData($data);
	$insertData['ins_tts_id'] = $_SESSION['myid']['id'];
	$insertData['ins_date'] = "now()";

	return $cdb->insert(T_EXTERNAL_PROBLEM, $insertData);
}

function updateExternalProblem($problemNum, $data)
{
	$cdb = $GLOBALS['cdb'];

	$updateData = convertExternalProblemData($data);
	$updateData['upd_tts_id'] = $_SESSION['myid']['id'];
	$updateData['upd_date'] = "now()";
	$updateData['move_flg'] = 0;

	$where = " WHERE problem_num='{$problemNum}' LIMIT 1;";
	return $cdb->update(T_EXTERNAL_PROBLEM, $updateData, $where);
}
function convertExternalProblemData($data)
{
	$newData = $data;

	$newData['option1'] = $data['speech_type'];
	$newData['option2'] = $data['model_voice'];
	$newData['option3'] = $data['model_voice_result'];
	$newData['option4'] = $data['voice_sentence'];

	$removeKeys = ['speech_type', 'model_voice', 'model_voice_result', 'voice_sentence'];
	$newData = array_diff_key($newData, array_flip($removeKeys));

	return $newData;
}
//kaopiz 2020/09/15 speech end
?>
