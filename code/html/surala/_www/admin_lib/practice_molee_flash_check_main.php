<?
/**
 * ベンチャー・リンク　すらら
 *
 * molee用flash確認画面 main 領域表示（サブプログラム）
 *
 * 履歴
 * 2014/04/03 初期設定
 *
 * @author Azet
 */

//	okabe

/**
 * HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function start() {

	global $L_DISPLAY;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$moleects = $_GET['moleects'];

	$html = "";	//*practice_molee_flash_check_main: ".$moleects." ";
//$html .= "<input type=\"button\" namae=\"test\" value=\"コンテンツ起動\" onclick=\"start_flash(); disp_footer();\"> ";
//$html .= "<input type=\"button\" namae=\"log\" value=\"ページ移動\" onclick=\"send_log(1);\">";
//$html .= "<input type=\"button\" namae=\"test\" value=\"T\" onclick=\"test();\">";
$html .= "<br>";
//$moleects = "1,2,5";
//$html .= $moleects." *<br>";

	$moleects_ary = split(",", $moleects);
	$m_course_num = $moleects_ary[0];
	$m_stage_num  = $moleects_ary[1];
	$m_lesson_num = $moleects_ary[2];

//	$html .= "<div id=\"m_couse_num\"></div>";
//	$html .= "<div id=\"m_stage_num\"></div>";
//	$html .= "<div id=\"m_lesson_num\"></div>";

	//	ファイルアップロード用仮設定
//	$flash_ftp = FTP_URL."flash/".$m_course_num."/".$m_stage_num."/".$m_lesson_num."/";
$flash_ftp = "/material/flash/".$m_course_num."/".$m_stage_num."/".$m_lesson_num."/";

	$sql  = "SELECT u.unit_num, c.course_name, u.unit_name, u.unit_key, u.display".
			" FROM ".T_COURSE." c, ".T_STAGE." s, ".T_LESSON." l, ".T_UNIT." u" .
			" WHERE c.course_num=s.course_num AND s.stage_num=l.stage_num AND l.lesson_num=u.lesson_num" .
			" AND u.course_num='$m_course_num' AND c.state!='1'" .
			" AND u.stage_num='$m_stage_num' AND s.state!='1'" .
			" AND u.lesson_num='$m_lesson_num' AND l.state!='1'" .
			" AND u.state!='1' ORDER BY u.list_num;";
//$html .= $sql."<br>";
//$html .= "<a href=\"javascript:reloadWebPage('main')\">reload</a> <span id=\"test\">*</span><br>\n";

	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
		if (!$max) {
			$html .= "<br>\n";
			$html .= "今現在登録されているユニットは有りません。<br><br><br>\n";
		} else {
			$html .= "<form name=\"flash_view\">\n";
			$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$m_course_num."\">\n";
			$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$m_stage_num."\">\n";
			$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$m_lesson_num."\">\n";
			$html .= "<table class=\"course_form\">\n";
			$html .= "<tr class=\"course_form_menu\">\n";
			$html .= "<th rowspan=\"2\" >登録番号</th>\n";
			$html .= "<th rowspan=\"2\">コース名</th>\n";
			$html .= "<th rowspan=\"2\">ユニット名</th>\n";
			$html .= "<th rowspan=\"2\">ユニットキー</th>\n";
			$html .= "<th rowspan=\"2\">表示・非表示</th>\n";
			$html .= "<th rowspan=\"2\">FLASHフォルダー</th>\n";
			$html .= "<th rowspan=\"2\">設定</th>\n";
			//$html .= "<th colspan=\"4\">FLASH</th>\n";
			$html .= "<th colspan=\"3\">FLASH</th>\n";		//add okabe 2014/12/01   Make5コンテンツ表示
	//		$html .= "<th colspan=\"3\">HTML5&nbsp;(Make5)</th>\n";
		//	$html .= "<th>HTML</th>\n";
			$html .= "</tr>\n";
			$html .= "<tr class=\"course_form_menu\">\n";
			$html .= "<th>新規学習</th>\n";
			$html .= "<th>復習学習</th>\n";
			$html .= "<th>診断復習学習</th>\n";
			//$html .= "<th>教務ツール</th>\n";
		//	$html .= "<th>レクチャー</th>\n";
	//		//add okabe start 2014/12/01   Make5コンテンツ表示
	//		$html .= "<th>新規学習</th>\n";
	//		$html .= "<th>復習学習</th>\n";
	//		$html .= "<th>診断復習学習</th>\n";
	//		//add okabe end 2014/12/01   Make5コンテンツ表示

			$html .= "</tr>\n";

			while ($list = $cdb->fetch_assoc($result)) {
				$material_flash_path  = MATERIAL_FLASH_DIR.$m_course_num."/".$m_stage_num."/".
										$m_lesson_num."/".$list['unit_num']."/";
				if (!file_exists($material_flash_path)) {
					@mkdir($material_flash_path, 0777);
					@chmod($material_flash_path, 0777);
				}

				$material_prob_path   = MATERIAL_PROB_DIR.$m_course_num."/".$m_stage_num."/".
										$m_lesson_num."/".$list['unit_num']."/";
				if (!file_exists($material_prob_path)) {
					@mkdir($material_prob_path,0777);
					@chmod($material_prob_path,0777);
				}

				$material_voice_path  = MATERIAL_VOICE_DIR.$m_course_num."/".$m_stage_num."/".
										$m_lesson_num."/".$list['unit_num']."/molee/";
				if (!file_exists($material_voice_path)) {
					@mkdir($material_voice_path,0777);
					@chmod($material_voice_path,0777);
				}

				// 2012/08/03 add start oda
				// path 編集
				$read_path_molee = MATERIAL_FLASH_DIR.$m_course_num."/".$m_stage_num."/".
				//$read_path_molee = "../molle/test_okabe/material/flash/".$m_course_num."/".$m_stage_num."/".	//※暫定path del okabe 2014/11/19
				$m_lesson_num."/".$list['unit_num']."/molee/unitjs.html";	//edit okabe 2014/08/08
				//$read_path_flash = MATERIAL_FLASH_DIR.$m_course_num."/".$m_stage_num."/".
				//		$m_lesson_num."/".$list['unit_num']."/main.swf";
				//$read_path_html = MATERIAL_FLASH_DIR.$m_course_num."/".$m_stage_num."/".
				//		$m_lesson_num."/".$list['unit_num']."/index.html";
	//			$read_path_make5 = MATERIAL_FLASH_DIR.$m_course_num."/".$m_stage_num."/".$m_lesson_num."/".$list['unit_num']."/html5/main.swf.html";	//add okabe 2014/12/01   Make5コンテンツ表示

				// 設定確認
				$set_media = "<span style=\"color:red\">未設定</span>";
				$disabled = "disabled";
				$disabled2 = "disabled";	//add okabe 2014/12/01   Make5コンテンツ表示
				$setted_media_flag = 0;		//add okabe 2014/12/01   Make5コンテンツ表示
				if (file_exists($read_path_molee)) {
					$set_media = "unitjs<br>設定済";
					$disabled = "";
					$setted_media_flag = 1;		//add okabe 2014/12/01   Make5コンテンツ表示
				}
				//add okabe start 2014/12/01   Make5コンテンツ表示
	//			if (file_exists($read_path_make5)) {
	//				if ($setted_media_flag == 0) {
	//					$set_media = "";
	//				} else {
	//					$set_media .= "<br/>";
	//				}
	//				$set_media = "HTML5<br>設定済";
	//				$disabled2 = "";
	//			}
				//add okabe end 2014/12/01   Make5コンテンツ表示
				//if (file_exists($read_path_flash)) {
				//	$set_media = "FLASH設定済";
				//}
				//if (file_exists($read_path_html)) {
				//	$set_media = "HTML設定済";
				//}
				// 2012/08/03 add end oda

				$html .= "<tr class=\"course_form_cell\">\n";
				$html .= "<td>".$list[unit_num]."</td>\n";
				$html .= "<td>".$list[course_name]."</td>\n";
				$html .= "<td>".$list[unit_name]."</td>\n";
				$html .= "<td>".$list[unit_key]."</td>\n";
				$display = $list[display];
				$html .= "<td>".$L_DISPLAY[$display]."</td>\n";
//				$ftp_link = $flash_ftp.$list[unit_num]."/";	//del okabe 2014/08/08
				$ftp_link = $flash_ftp.$list[unit_num]."/molee/";	//add okabe 2014/08/08
				$ftp_link2 = $flash_ftp.$list[unit_num]."/html5/";	//add okabe 2014/12/01   Make5コンテンツ表示
				//$html .= "<td><a href=\"".$ftp_link."\" target=\"_blank\">FTP</a></td>\n";
				$html .= "<td>".$flash_ftp.$list[unit_num]."/<br/>molee/<br/>html5/</td>\n";
				$html .= "<td>".$set_media."</td>\n";		// 2012/08/03 add oda
				$html .= "<td>\n";
//				$html .= "<input type=\"button\" value=\"新規学習\" OnClick=\"check_molee_flash_open('1','".$list[unit_num]."');\"><br>\n";
				$html .= "<input type=\"button\" value=\"新規学習\" OnClick=\"check_molee_flash_open('1', '".$list[unit_num]."', '".$ftp_link."'); disp_footer();\" ".$disabled."><br>\n";
				$html .= "開始ページ<br>\n";
				$html .= "<input type=\"text\" size=\"4\" name=\"current_page_1_".$list[unit_num]."\" value=\"\">\n";
				$html .= "</td>\n";
				$html .= "<td>\n";
//				$html .= "<input type=\"button\" value=\"復習学習\" OnClick=\"check_molee_flash_open('2','".$list[unit_num]."');\"><br>\n";
				$html .= "<input type=\"button\" value=\"復習学習\" OnClick=\"disp_footer(); check_molee_flash_open('2', '".$list[unit_num]."', '".$ftp_link."'); \" ".$disabled."><br>\n";
				$html .= "開始ページ<br>\n";
				$html .= "<input type=\"text\" size=\"4\" name=\"current_page_2_".$list[unit_num]."\" value=\"\">\n";
				$html .= "</td>\n";
				$html .= "<td>\n";
//				$html .= "<input type=\"button\" value=\"診断復習学習\" OnClick=\"check_molee_flash_open('3','".$list[unit_num]."');\"><br>\n";
				$html .= "<input type=\"button\" value=\"診断復習学習\" OnClick=\"check_molee_flash_open('3', '".$list[unit_num]."', '".$ftp_link."'); disp_footer();\" ".$disabled."><br>\n";
				$html .= "開始ページ<br>\n";
				$html .= "<input type=\"text\" size=\"4\" name=\"current_page_3_".$list[unit_num]."\" value=\"\"><br>\n";
				$html .= "閲覧ページ(カンマ区切り)<br>\n";
				$html .= "<input type=\"text\" size=\"14\" name=\"lesson_page_".$list[unit_num]."\" value=\"\">\n";
				$html .= "</td>\n";
/*
				$html .= "<td>\n";
//				$html .= "<input type=\"button\" value=\"教務ツール\" OnClick=\"check_molee_flash_open('4','".$list[unit_num]."');\"><br>\n";
$html .= "<input type=\"button\" value=\"教務ツール\" OnClick=\"check_molee_flash_open('4', '".$list[unit_num]."', '".$ftp_link."'); disp_footer();\" disabled><br>\n";
				$html .= "開始ページ<br>\n";
				$html .= "<input type=\"text\" size=\"4\" name=\"current_page_4_".$list[unit_num]."\" value=\"\" readonly>\n";
				$html .= "</td>\n";
*/
				// 2012/08/02 add start oda
			//	$html .= "<td>\n";
			//	$html .= "<input type=\"button\" value=\"レクチャー\" OnClick=\"check_lecture_win_open('".$list[unit_num]."');\"><br>\n";
			//	$html .= "</td>\n";
				// 2012/08/02 add end oda

				//add okabe start 2014/12/01   Make5コンテンツ表示
				/*
				$html .= "<td>\n";
				$html .= "<input type=\"button\" value=\"新規学習\" OnClick=\"check_make5_flash_open('1', '".$list[unit_num]."', '".$ftp_link2."'); disp_footer();\" ".$disabled2."><br>\n";
				$html .= "開始ページ<br>\n";
				$html .= "<input type=\"text\" size=\"4\" name=\"current_page_1_".$list[unit_num]."\" value=\"\">\n";
				$html .= "</td>\n";
				$html .= "<td>\n";
				$html .= "<input type=\"button\" value=\"復習学習\" OnClick=\"disp_footer(); check_make5_flash_open('2', '".$list[unit_num]."', '".$ftp_link2."'); \" ".$disabled2."><br>\n";
				$html .= "開始ページ<br>\n";
				$html .= "<input type=\"text\" size=\"4\" name=\"current_page_2_".$list[unit_num]."\" value=\"\">\n";
				$html .= "</td>\n";
				$html .= "<td>\n";
				$html .= "<input type=\"button\" value=\"診断復習学習\" OnClick=\"check_make5_flash_open('3', '".$list[unit_num]."', '".$ftp_link2."'); disp_footer();\" ".$disabled2."><br>\n";
				$html .= "開始ページ<br>\n";
				$html .= "<input type=\"text\" size=\"4\" name=\"current_page_3_".$list[unit_num]."\" value=\"\"><br>\n";
				$html .= "閲覧ページ(カンマ区切り)<br>\n";
				$html .= "<input type=\"text\" size=\"14\" name=\"lesson_page_".$list[unit_num]."\" value=\"\">\n";
				$html .= "</td>\n";
				*/
				//add okabe end 2014/12/01   Make5コンテンツ表示

				$html .= "</tr>\n";
			}
			$html .= "</table>\n";
			$html .= "</form>\n";
		}
	}
	$html .= "molee version: <span id=\"molee_version_disp\"></span><br>\n";

	return $html;
}

?>