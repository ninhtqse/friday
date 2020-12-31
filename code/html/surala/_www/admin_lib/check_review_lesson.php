<?PHP
/**
 * 診断復習レッスンページ表示
 *
 *
 * テーブル名　T_CHECK_STUDY_RECODEへ変更
 * $_SESSION['studentid']['student_num']を$_SESSION['managerid']['manager_num']変更
 * STUDENT_URL を　/admin/　へ変更
 *
 * @@@ 新ＤＢ対応
 * 	$_SESSION['manager']['manager_num']を$_SESSION["myid"]["id"]に変更
 * 	カラム名の変更
 *
 * @author Azet
 */

/**
 * SESSIONに設定する機能
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 */
function start() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$student_num = $_SESSION["myid"]["id"];
	$course_num = $_SESSION['course']['course_num'];
	$unit_num = $_SESSION['course']['unit_num'];


	if ($_POST['block_num']) {
		$block_num = $_POST['block_num'];
		$_SESSION['course']['block_num'] = $block_num;
	} elseif ($_SESSION['course']['block_num']) {
		$block_num = $_SESSION['course']['block_num'];
	}


	if ($_POST['review_unit_num']) {
		$review_unit_num = $_POST['review_unit_num'];
		$_SESSION['course']['review_unit_num'] = $review_unit_num;
	} elseif ($_SESSION['course']['review_unit_num']) {
		$review_unit_num = $_SESSION['course']['review_unit_num'];
	}

	//	学習回数チェック
	//	$_SESSION['course']['review']には、学習回数が登録される。
	if ($_SESSION['course']['review'] == "" || $_SESSION['course']['finish_time'] == "") {
		unset($_SESSION['course']['review']);
		unset($_SESSION['course']['finish_time']);
		$sql  = "SELECT regist_time FROM ".T_FINISH_UNIT.
				" WHERE student_id='".$student_num."'".
				" AND course_num='".$course_num."'".
				" AND unit_num='".$unit_num."'".
				" AND skip!='1'".
				" AND state!='1'".
				" ORDER BY regist_time;";
		if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
		if ($result = $cdb->query($sql)) {
			while ($list = $cdb->fetch_assoc($result)) {
				$regist_time = $list['regist_time'];
				$_SESSION['course']['review']++;
				$_SESSION['course']['finish_time'] = $regist_time;
			}
		}
		$_SESSION['course']['review']++;
		//	ユニット最終クリアー時間がない場合、空の値を入れる。
		if (!$_SESSION['course']['finish_time']) {
			$_SESSION['course']['finish_time'] = "0";
		}
	}

	if ($_POST['skill_num']) {
		$skill_num = $_POST['skill_num'];
	} else {
		if ($_SESSION['course']['block_type'] < 1) {
			$sql  = "SELECT block_type FROM ".T_BLOCK.
					" WHERE course_num='".$course_num."'".
				" AND unit_num='".$unit_num."'".
					" AND block_num='".$block_num."'".
					" LIMIT 1;";
			if ($result = $cdb->query($sql)) {
				$list = $cdb->fetch_assoc($result);
				$_SESSION['course']['block_type'] = $list['block_type'];
			}
		}
		if ($_SESSION['course']['review'] >= 2) {
			if ($_SESSION['course']['block_type'] == 2) {
				$class_m = 202020300;
			} elseif ($_SESSION['course']['block_type'] == 3) {
				$class_m = 202030300;
			}
		} else {
			if ($_SESSION['course']['block_type'] == 2) {
				$class_m = 102020300;
			} elseif ($_SESSION['course']['block_type'] == 3) {
				$class_m = 102030300;
			}
		}

		$sql  = " SELECT review_unit_num FROM ".T_CHECK_STUDY_RECODE.
				" WHERE student_id='".$student_num."'".
				" AND class_m='".$class_m."'".
				" AND course_num='".$course_num."'".
//				" AND DATE(regist_time)=CURDATE()".
				" AND regist_time>'".$_SESSION['course']['finish_time']."'".
				" ORDER BY check_study_record_num DESC".
				" LIMIT 1;";
		if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$skill_num = $list[review_unit_num];
		}
	}

	if ($_POST['lesson_page']) {
		$lesson_page = $_POST['lesson_page'];
	} else {
		$sql  = "SELECT lesson_page FROM ".T_REVIEW_SETTING.
				" WHERE skill_num='".$skill_num."'".
				" AND course_num='".$course_num."'" .
				" AND block_num='".$block_num."'" .
				" AND display='1'" .
				" AND state!='1'" .
				" LIMIT 1;";
		if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$lesson_page = $list[lesson_page];
		}
	}

	if ($_SESSION['course']['display_problem_num']) {
		$page = $_SESSION['course']['display_problem_num'];
	}
	$sql  = "SELECT stage_num, lesson_num FROM ".T_UNIT.
			" WHERE course_num='".$course_num."'".
			" AND unit_num='".$review_unit_num."'".
			" AND display='1'" .
			" AND state!='1'" .
			" LIMIT 1;";
	if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$stage_num = $list[stage_num];
		$lesson_num = $list[lesson_num];
	}

	//	ログファイル
	$log_url = "/admin/".STUDENT_REVIEW_FLASHLOG_FILE;
	//	FLASH終了後ページ
	$next_url = "/admin/".STUDENT_REVIEW_LESSON_COMP_FILE;
	$status = "true";
	//	FLASHメインファイルパス設定
	$flash_path = MATERIAL_FLASH_DIR.$course_num."/".$stage_num."/".$lesson_num."/".$review_unit_num."/".STUDENT_REVIEW_FLASH_MOVIE_FILE;
	//	FLASHフォルダー
	$url_path = "/material/flash/".$course_num."/".$stage_num."/".$lesson_num."/".$review_unit_num."/";
	//	パラメーター設定
	//$send_para = "log_url=".$log_url."&next_sco_url=".$next_url."&status=".$status."&disp_page=".$lesson_page."&current_page=".$page."&url_path="."/material/flash/".$course_num."/".$stage_num."/".$lesson_num."/".$review_unit_num."/";
	$send_para = "log_url=".$log_url.
				 "&next_sco_url=".$next_url.
				 "&status=".$status.
				 "&disp_page=".$lesson_page.
				 "&current_page=".$page.
				 "&url_path=".$url_path.
				 "&indicator_flag=".$indicator_flag;

	if (!file_exists($flash_path)) {
		header ("Location: $next_url\n\n");
		exit;
	} else {
		$html .=<<<EOT
<script src="/javascript/readFlash.js"></script>
<script language="javascript">
readFlash('$flash_path','$send_para');
</script>

EOT;

		$make_html = new read_html();
		$make_html->set_dir(STUDENT_TEMP_DIR);
		$make_html->set_file(STUDENT_TEMP_LESSON);
		$INPUTS[FLASH] = array('result'=>'plane','value'=>$html);
		$make_html->set_rep_cmd($INPUTS);
		$html = $make_html->replace();
	}

	unset($_SESSION['course']['display_problem_num']);

	return $html;
}
?>