<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * review_drill_comp
 *
 * テーブル名　T_CHECK_STUDY_RECODEへ変更
 * $_SESSION['studentid']['manager_num']を$_SESSION['managerid']['manager_num']変更
 * check_check_study_record_numをcheck_check_check_study_record_num
 *
 * @@@ 新ＤＢ対応
 * 	$_SESSION['manager']['manager_num']を$_SESSION["myid"]["id"]に変更
 * 	カラム名の変更
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

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//	ログデーターを保存してポイント解説に戻る。

	$student_num = $_SESSION["myid"]["id"];
	$course_num = $_SESSION['course']['course_num'];
	$unit_num = $_SESSION['course']['unit_num'];
	$block_num = $_SESSION['course']['block_num'];
	$skill_num = $_SESSION['course']['skill_num'];

	//	元block_type
	$sql  = "SELECT block_type FROM ".T_BLOCK.
			" WHERE block_num='{$block_num}' LIMIT 1;";
	if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		define("base_block_type",$list['block_type']);
	}

	if (!$skill_num) {
		if ($_SESSION['course']['review'] >= 2) {
			if (base_block_type == 2) {
				$class_m = 202020300;
			} elseif (base_block_type == 3) {
				$class_m = 202030300;
			}
		} else {
			if (base_block_type == 2) {
				$class_m = 102020300;
			} elseif (base_block_type == 3) {
				$class_m = 102030300;
			}
		}
		$sql  = " SELECT review_unit_num FROM ".T_CHECK_STUDY_RECODE.
				" WHERE student_id='{$student_num}'".
//				" AND DATE(regist_time)=CURDATE()".
				" AND regist_time>'".$_SESSION['course']['finish_time']."'".
				" AND course_num='{$course_num}'".
				" AND block_num='{$block_num}'".
				" AND class_m='{$class_m}'".
				" ORDER BY check_study_record_num DESC LIMIT 1;";
		if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$skill_num = $list[review_unit_num];
		}
	}

	unset($class_m);
	if ($_SESSION['course']['review'] >= 2) {
		if (base_block_type == 2) {
			if ($_SESSION['record']['giveup'] == 1) {
				$class_m = 202020510;
			} else {
				$class_m = 202020500;
			}
		} elseif (base_block_type == 3) {
			if ($_SESSION['record']['giveup'] == 1) {
				$class_m = 202030510;
			} else {
				$class_m = 202030500;
			}
		}
	} else {
		if (base_block_type == 2) {
			if ($_SESSION['record']['giveup'] == 1) {
				$class_m = 102020510;
			} else {
				$class_m = 102020500;
			}
		} elseif (base_block_type == 3) {
			if ($_SESSION['record']['giveup'] == 1) {
				$class_m = 102030510;
			} else {
				$class_m = 102030500;
			}
		}
	}

	$INSERT_DATA['class_m'] = $class_m;
	$INSERT_DATA['student_id'] = $student_num;
//	$INSERT_DATA['area_num'] = $_SESSION['studentid']['area_num'];
//	$INSERT_DATA['enterprise_num'] = $_SESSION['studentid']['enterprise_num'];
//	$INSERT_DATA['school_num'] = $_SESSION['studentid']['school_num'];
//	$INSERT_DATA['cr_num'] = $_SESSION['pc']['classroom'];
//	$INSERT_DATA['seat_num'] = $_SESSION['pc']['seat'];
	$INSERT_DATA['course_num'] = $course_num;
	$INSERT_DATA['unit_num'] = $unit_num;
	$INSERT_DATA['block_num'] = $block_num;
	$INSERT_DATA['review_unit_num'] = $skill_num;
	$INSERT_DATA['regist_time'] = "now()";

	$ERROR = $cdb->insert(T_CHECK_STUDY_RECODE,$INSERT_DATA);
	if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::query-&gt;insert::T_CHECK_STUDY_RECODE"; }

	//	復習ドリルクリアー記録(更新)
	if ($_SESSION['record']['giveup'] != 1) {
		unset($INSERT_DATA);
		$INSERT_DATA[clear_skill] = 1;
		$where    = " WHERE student_id='".$_SESSION['studentid']['student_id']."'".
					" AND skill_num='".$_SESSION['course']['skill_num']."'".
					" AND block_num='".$_SESSION['course']['block_num']."'".
					" ORDER BY regist_time DESC LIMIT 1;";

		$ERROR = $cdb->update(T_RETURN_LOG,$INSERT_DATA,$where);
		unset($_SESSION['course']['skill_num']);
	}

	$STUDENT_POINT_FILE = STUDENT_POINT_FILE;
	header ("Location: $STUDENT_POINT_FILE\n\n");
	exit;
}
?>
