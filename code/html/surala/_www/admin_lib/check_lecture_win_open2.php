<?PHP
/**
 * すららネット
 *
 * レクチャー確認（html5レクチャー）
 *
 * e-learning system admin check_lecture_win_open2
 *
 * 履歴
 * 2019/01/23 初期設定
 *
 * @author Azet
 */

/**
 * メイン処理
 * SESSIONパラメータを取得し、レクチャー確認画面を作成し、表示します。
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function start() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// 最新の解答ログのみを保持するため、開始時に過去の解答ログを削除します。
	$id = $_SESSION['myid']['id'];
	$course_num = $_SESSION['CHECK_FLASH']['course_num'];
	$stage_num = $_SESSION['CHECK_FLASH']['stage_num'];
	$lesson_num = $_SESSION['CHECK_FLASH']['lesson_num'];
	$unit_num = $_SESSION['CHECK_FLASH']['unit_num'];

	if( $_SESSION['myid']['id'] && $course_num > 0 && $unit_num > 0 ){
		$where = "";
		$where = " WHERE school_id = '".$id."' ";
		$where .= " AND class_m = '101010020' "; // レクチャー解答（レクチャーとゲームを判別する種に指定）
		$where .= " AND course_num = '".$course_num."' ";
		$where .= " AND unit_num = '".$unit_num."' ";
		$ERROR = $cdb->delete(T_STUDY_RECODE,$where);

		// 書写ログの方も併せて削除する(log_type=1)
		if(!$ERROR) {
			$where = "";
			$where = " WHERE log_type = '1' ";
			$where .= " AND study_log_type = 'lecture_answer' ";
			$where .= " AND record_type = '0' ";
			$where .= " AND student_id = '0' ";
			$where .= " AND school_id = '".$id."' ";
			$where .= " AND course_num = '".$course_num."' ";
			$where .= " AND stage_num = '".$stage_num."' ";
			$where .= " AND lesson_num = '".$lesson_num."' ";
			$where .= " AND unit_num = '".$unit_num."' ";
			$where .= " AND review = '0'; ";

			$ERROR = $cdb->delete(T_SYOSYA_LOG,$where);
		}

		// 1年以上前のレコードが存在したら、ついでに削除する(log_type=0)
		$where = "";
		$where .= " WHERE log_type = '0' ";
		$where .= " AND study_log_type IS NULL ";
		$where .= " AND record_type = '0' ";
		$where .= " AND course_num = '".$course_num."' ";
		$where .= " AND stage_num = '".$stage_num."' ";
		$where .= " AND lesson_num = '".$lesson_num."' ";
		$where .= " AND unit_num = '".$unit_num."' ";
		$where .= " AND review = '0' ";
		$where .= " AND regist_time < (NOW() - INTERVAL 1 YEAR); ";

		$ERROR = $cdb->delete(T_SYOSYA_LOG,$where);
	}

	// レクチャー確認画面作成
	$html = "";
	$html .= "<!DOCTYPE html>\n";
	$html .= "<html lang=\"ja\">\n";
	$html .= "<head>\n";
	$html .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\n";
	$html .= "<meta http-equiv=\"Content-Style-Type\" content=\"text/css\">\n";
	$html .= "<title>HTML5レクチャー動作確認</title>\n";
	$html .= "<style type=\"text/css\">\n";
	$html .= "<!--\n";
	$html .= "html{ height: 100%; width:100%; }\n";
	$html .= "body{ height: 100%; overflow: hidden; width:100%; }\n";
	$html .= "iframe { overflow: auto; }\n";
	$html .= "-->\n";
	$html .= "</style>\n";
	$html .= "<script type=\"text/javascript\">\n";
	$html .= "<!--\n";
	$html .= "var log_url=\"\";\n";
	$html .= "var next_sco_url=\"\";\n";
	$html .= "var status=\"\";\n";
	$html .= "var current_page=\"\";\n";
	$html .= "var disp_page=\"\";\n";
	$html .= "var url_path=\"\";\n";
	$html .= "var indicator_flag=\"\";\n";
	$html .= "var study_log_url=\"\";\n";
	$html .= "var tegaki_flg=\"\";\n";
	$html .= "-->\n";
	$html .= "</script>\n";
	$html .= "</head>\n";
	$html .= "<body>\n";
	$html .= "<iframe name=\"flash_erea\" id=\"main_check\" src=\"/admin/check_lecture_lg.php?erea=lecture\" width=\"99%\" height=\"80%\"></iframe><br>\n";
	$html .= "<iframe name=\"page_erea\" id=\"log_check\" src=\"/admin/check_lecture_lg.php?erea=page\"  width=\"99%\" height=\"18%\">\n";
	$html .= "<iframe name=\"page_erea\" id=\"log_check\" src=\"/admin/check_lecture_lg.php?erea=page\"  width=\"99%\" height=\"18%\">\n";
	$html .= "</iframe>\n";
	$html .= "</body>\n";
	$html .= "</html>\n";

	return $html;
}
?>