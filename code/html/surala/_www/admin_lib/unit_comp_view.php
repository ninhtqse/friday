<?PHP
include_once("/data/home/www/userAgent.php");   // kaopiz add 2020/08/15 ipados13
/**
 *
 * 開発 プラクティスステージ→ユニット作成
 * 開発 プラクティスステージ→サジェスト確認
 * で表示される
 * サジェスト確認用画面作成
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * 履歴
 * 2019/11/06 初期設定
 * @author azet
 */

/**
 * ユニット終了処理開始
 * すららの英語／国語／数学／理科／社会のコースで、ユニットが終了した際の画面を表示する。
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author azet
 * @return string htmlデータ
 */
function start() {

	$course_num = $_POST['course_num'];
	$unit_num = $_POST['unit_num'];
	$display_type = $_POST['display_type'];

	$cdb = $GLOBALS['cdb'];

	$INPUTS = array();

	$comment1 = "ユニット";
	$comment = "<span>この" . $comment1 . "は<br>ここで<ruby>終了<rp>(</rp><rt>しゅうりょう</rt><rp>)</rp></ruby>です！</span>";
	if ($display_type == 3) {
		$comment ="<span>この".$comment1."はここで<ruby>終了<rp>(</rp><rt>しゅうりょう</rt><rp>)</rp></ruby>です！</span>";
	}

	$sql = " SELECT " .
			" course.course_num" .
			" ,stage.stage_num" .
			" ,stage.stage_key" .
			" ,lesson.lesson_num" .
			" ,unit.unit_key" .
			" FROM " . T_UNIT . " unit " .
			" INNER JOIN " . T_LESSON . " lesson ON unit.lesson_num = lesson.lesson_num" .
			" INNER JOIN " . T_STAGE . " stage ON lesson.stage_num = stage.stage_num" .
			" INNER JOIN " . T_COURSE . " course ON stage.course_num = course.course_num" .
			" WHERE 1" .
			"   AND unit.state = 0" .
			"   AND lesson.state = 0" .
			"   AND stage.state = 0" .
			"   AND course.state = 0" .
			"   AND unit.unit_num = '" . $unit_num . "'" .
			";";


	$unit_key = "";
	$stage_key = "";
	if ($result = $cdb->query($sql)) {
		if ($list = $cdb->fetch_assoc($result)) {
			$unit_key = $list['unit_key'];
			$stage_key = $list['stage_key'];
			$stage_num = $list['stage_num'];
		}
	}

	$button = "";
	// 小中高の識別子を取得
	$jh_key = setSchoolInitial($unit_key);
	$ps_directory = "";
	if($display_type == 3){
		$ps_directory = "ps/";
	}
	//--------------------------------------------------------------------------

	$css_path = "../material/template/" . $course_num . "/" . $jh_key . "unit_comp.css";

	// stageフォルダにCSSがある場合は優先
	$css_path_check = "../material/template/" . $course_num . "/" . $stage_num . "/" . $jh_key . "unit_comp.css";
	if (file_exists($css_path_check)) {
		$css_path = $css_path_check;
	}
	if ($display_type == 3) {
		$css_path_check = "../material/template/".$course_num."/ps_".$jh_key."unit_comp.css";
		if (file_exists($css_path_check)) {
			$css_path = $css_path_check;
		}
		$css_path_check = "../material/template/".$course_num."/".$stage_num."/ps_".$jh_key."unit_comp.css";
		if (file_exists($css_path_check)) {
			$css_path = $css_path_check;
		}
	}

	//新しいTOPは、全コース・全学年同じユニット終了画面なので、CSSを統一する。
	if ($display_type == 2) {
		$css_path = "../material/template/" . $course_num . "/unit_comp.css";
	}

	$morescript = "<link rel=\"stylesheet\" href=\"" . $css_path . "\" type=\"text/css\">";
	$morescript .= "<link rel=\"stylesheet\" href=\"/css/modalWindows.css\" type=\"text/css\">";
	$morescript .= "<script type=\"text/javascript\" src=\"/javascript/userAgent.js\"></script>"; // kaopiz add 2020/08/15 ipados13
	$morescript .= "<script type=\"text/javascript\" src=\"/javascript/package.js\"></script>";
	$morescript .= "<script type=\"text/javascript\" src=\"/javascript/student_target.js\"></script>";
	$morescript .= "<script type=\"text/javascript\" src=\"/javascript/student.js\"></script>";
	$morescript .= "<script type=\"text/javascript\" src=\"/javascript/modalWindows.jquery.js\"></script>";

	$change_class_script = "<script type=\"text/javascript\">\n";
	$change_class_script .= "$(document).ready(function(){ $('#unit_comp').addClass('unit_comp_target');});\n";
	$change_class_script .= "</script>\n";


	$image1 = "/student/images/button/" .$ps_directory. $jh_key . $course_num . "_11_2.gif";
	$image2 = "/student/images/button/" .$ps_directory. $jh_key . $course_num . "_11_1.gif";
	if (file_exists("../" . $image1)) {
		// タブレットの場合は、タッチイベントで動作する
		if (is_tablet()) {
			$button1 .= "<img src=\"$image1\" name=\"button1\" alt=\"次のユニットへ進む\"";
			$button1 .= " onTouchStart=\"HpbImgSwap('button1', '$image2');\"";
			$button1 .= " onTouchEnd=\"HpbImgSwap('button1', '$image1'); \"";
			$button1 .= " onTouchCancel=\"HpbImgSwap('button1', '$image1');\"";
			$button1 .= " style=\"cursor:pointer;\">\n";
			// Chromeまたはsafariの場合は、onmouseoutが不要。
		} elseif (is_chrome_safari()) {
			$button1 .= "<img src=\"$image1\" name=\"button1\" alt=\"次のユニットへ進む\"";
			$button1 .= " onmousedown=\"HpbImgSwap('button1', '$image2');\"";
			$button1 .= " onmouseup=\"HpbImgSwap('button1', '$image1'); \"";
			$button1 .= " style=\"cursor:pointer;\">\n";
		} else {
			$button1 .= "<img src=\"$image1\" name=\"button1\" alt=\"次のユニットへ進む\"";
			$button1 .= " onmousedown=\"HpbImgSwap('button1', '$image2');\"";
			$button1 .= " onmouseup=\"HpbImgSwap('button1', '$image1'); \"";
			$button1 .= " onmouseout=\"HpbImgSwap('button1', '$image1');\"";
			$button1 .= " style=\"cursor:pointer;\">\n";
		}
	} else {
		$button1 .= "<input type=\"submit\" value=\"次のユニットへ進む\" class=\"form_font_size\">\n";
	}
	$image1 = "/student/images/button/" .$ps_directory. $jh_key . $course_num . "_12_2.gif";
	$image2 = "/student/images/button/" .$ps_directory. $jh_key . $course_num . "_12_1.gif";
	if (file_exists("../" . $image1)) {

		// 遷移するJavascriptを切り替える
		$exec_javascript = "";

		// タブレットの場合は、タッチイベントで動作する
		if (is_tablet()) {
			$button2 .= "<img src=\"$image1\" name=\"button2\" alt=\"次のステージへ進む\"";
			$button2 .= " onTouchStart=\"HpbImgSwap('button2', '$image2');\"";
			$button2 .= " onTouchEnd=\"HpbImgSwap('button2', '$image1'); " . $exec_javascript . "\"";
			$button2 .= " onTouchCancel=\"HpbImgSwap('button2', '$image1');\"";
			$button2 .= " style=\"cursor:pointer;\">\n";
			// Chromeまたはsafariの場合は、onmouseoutが不要。
		} elseif (is_chrome_safari()) {
			$button2 .= "<img src=\"$image1\" name=\"button2\" alt=\"次のステージへ進む\"";
			$button2 .= " onmousedown=\"HpbImgSwap('button2', '$image2');\"";
			$button2 .= " onmouseup=\"HpbImgSwap('button2', '$image1'); " . $exec_javascript . "\"";
			$button2 .= " style=\"cursor:pointer;\">\n";
		} else {
			$button2 .= "<img src=\"$image1\" name=\"button2\" alt=\"次のステージへ進む\"";
			$button2 .= " onmousedown=\"HpbImgSwap('button2', '$image2');\"";
			$button2 .= " onmouseup=\"HpbImgSwap('button2', '$image1'); " . $exec_javascript . "\"";
			$button2 .= " onmouseout=\"HpbImgSwap('button2', '$image1');\"";
			$button2 .= " style=\"cursor:pointer;\">\n";
		}
	} else {

		// 遷移するJavascriptを切り替える
		if ($settings && $settings['menu_transfer'] === true) {
			$button2 .= "<input type=\"button\" value=\"ユニット一覧へ戻る\" class=\"form_font_size\" onclick=\"return false;\">\n";
		} else {
			$button2 .= "<input type=\"button\" value=\"ユニット一覧へ戻る\" class=\"form_font_size\">\n";
		}
	}
	$image1 = "/student/images/button/" .$ps_directory. $jh_key . $course_num . "_13_2.gif";
	$image2 = "/student/images/button/" .$ps_directory. $jh_key . $course_num . "_13_1.gif";

	if (file_exists("../" . $image1)) {
		// タブレットの場合は、タッチイベントで動作する
		if (is_tablet()) {
			$button3 .= "<img src=\"$image1\" name=\"button3\" alt=\"他の教科を受講する\"";
			$button3 .= " onTouchStart=\"HpbImgSwap('button3', '$image2');\"";
			$button3 .= " onTouchEnd=\"HpbImgSwap('button3', '$image1'); " . $exec_javascript . "\"";
			$button3 .= " onTouchCancel=\"HpbImgSwap('button3', '$image1');\"";
			$button3 .= " style=\"cursor:pointer;\">\n";
			// Chromeまたはsafariの場合は、onmouseoutが不要。
		} elseif (is_chrome_safari()) {
			$button3 .= "<img src=\"$image1\" name=\"button3\" alt=\"他の教科を受講する\"";
			$button3 .= " onmousedown=\"HpbImgSwap('button3', '$image2');\"";
			$button3 .= " onmouseup=\"HpbImgSwap('button3', '$image1'); " . $exec_javascript . "\"";
			$button3 .= " style=\"cursor:pointer;\">\n";
		} else {
			$button3 .= "<img src=\"$image1\" name=\"button3\" alt=\"他の教科を受講する\"";
			$button3 .= " onmousedown=\"HpbImgSwap('button3', '$image2');\"";
			$button3 .= " onmouseup=\"HpbImgSwap('button3', '$image1'); " . $exec_javascript . "\"";
			$button3 .= " onmouseout=\"HpbImgSwap('button3', '$image1');\"";
			$button3 .= " style=\"cursor:pointer;\">\n";
		}
	} else {
		// 遷移するJavascriptを切り替える
		if ($settings && $settings['menu_transfer'] === true) {
			$button3 .= "<input type=\"button\" value=\"他の教科を受講する\" class=\"form_font_size\" onclick=\"jump_top_drill(); return false;\">\n";
		} else {
			$button3 .= "<input type=\"submit\" value=\"他の教科を受講する\" class=\"form_font_size\">\n";
		}
	}

	$button3 .= "</form>\n";

	$button1_align = "left";

	if ($display_type == 3) {
		$button2 = $button3;
		$button3 = "";
		$button1_align = "center";
	}

	$button = <<<EOT
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td align="$button1_align">$button1</td>
    <td align="center">$button2</td>
    <td align="right">$button3</td>
  </tr>
</table>

EOT;

	//現在、孫フレーム内の要素にzoomが効かないため
	//ドキュメントモードを指定してあげる必要がある
	// IE8 or IE7
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	if (preg_match('/MSIE 8/', $user_agent) || preg_match('/MSIE 7/', $user_agent)) {
		$docment = "<meta http-equiv=\"X-UA-Compatible\" content=\"IE=5\">\n";
		$INPUTS['DOCUMENTMODE'] = array('result' => 'plane', 'value' => $docment);
	}




	$morescript .= "<script type=\"text/javascript\" src=\"/javascript/jquery.mCustomScrollbar.min.js\"></script>\n";
	$morescript .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"/css/jquery.mCustomScrollbar.min.css\">\n";

	if ($display_type == 2) {
		$target = "<div id=\"goal\">";
		$target .= make_target_message(1);
		$target .= "</div>\n";
		$morescript .= $change_class_script;
//		$morescript .= "<script type=\"text/javascript\" src=\"/javascript/study_top_standard.js\"></script>\n";//adminでは未使用。これを入れるとエラーでjavascriptが止まるので、コメント
		$morescript .= "<link rel=\"stylesheet\" href=\"/css/study_top_standard.css\" type=\"text/css\">\n";
		$morescript .= "<link rel=\"stylesheet\" href=\"/css/study_top_standard_target.css\" type=\"text/css\">\n";
	} else {
		$target = "<div id=\"goal\">";
		$target .= make_target_message(0);
		$target .= "</div>";
	}
	$INPUTS['GOAL'] = array('result' => 'plane', 'value' => $target);

	$button = "<div id=\"area3\">" . $button . "</div>";


	$add_class = " class=\"suggest_on ";
	$suggst_button = false;
	if($display_type == 2){
		$add_class .= "standard_school ";
		$suggst_button = true;
	}
	$device_class = "";
//	if (preg_match("/iPad|iPhone/i", $_SERVER['HTTP_USER_AGENT']) === 1) {
	if (preg_match("/iPad|iPhone/i", $_SERVER['HTTP_USER_AGENT']) === 1 || is_ipad()) { //kaopiz update 2020/07/15 ipados13
		$device_class = ' ipad ';

		// add start oda 2020/04/27 iPadレイアウト調整 ルビが正しく表示されないので再描画させる
		$morescript .= "<script type=\"text/javascript\">\n";
		$morescript .= " $(document).ready(function() {\n";
		$morescript .= " $('#area1').css('display', 'none');\n";
		$morescript .= " $('#suggest_area').css('display', 'none');\n";
		$morescript .= " setTimeout(function() {\n";
		$morescript .= " $('#area1').css('display', 'block');\n";
		$morescript .= " }, 10);";
		$morescript .= " setTimeout(function() {\n";
		$morescript .= " $('#suggest_area').css('display', 'block');\n";
		$morescript .= " }, 10);";
		$morescript .= " });";
		// add end oda 2020/04/27

		$morescript .= "</script>\n";
	}
	$add_class .= $device_class;
	$add_class .= "\"";
	$student_approach_body = "<body" . $add_class . " onLoad=\"zoom_all(99);\">";

	$make_html = new read_html();
	$make_html->set_dir(STUDENT_TEMP_DIR . $course_num . "/");
	if ($display_type == 2) {
		if ($target) {
			$comment = "<p>おつかれさまでした。<br>このユニットは、ここで<ruby>終了<rp>(</rp><rt>しゅうりょう</rt><rp>)</rp></ruby>です。<br><ruby>次<rp>(</rp><rt>つぎ</rt><rp>)</rp></ruby>の<ruby>目標<rp>(</rp><rt>もくひょう</rt><rp>)</rp></ruby>を<ruby>一覧<rp>(</rp><rt>いちらん</rt><rp>)</rp></ruby>からえらんでください。</p>";
		} else {
			$comment = "<p style=\"line-height:36px;\">おつかれさまでした。<br>このユニットは、ここで<ruby>終了<rp>(</rp><rt>しゅうりょう</rt><rp>)</rp></ruby>です。<br><br><ruby>新<rp>(</rp><rt>あたら</rt><rp>)</rp></ruby>しい<ruby>目標<rp>(</rp><rt>もくひょう</rt><rp>)</rp></ruby>を<ruby>先生<rp>(</rp><rt>せんせい</rt><rp>)</rp></ruby>につくってもらうか、<br><ruby>上<rp>(</rp><rt>うえ</rt><rp>)</rp></ruby>のボタンからトップ<ruby>画面<rp>(</rp><rt>がめん</rt><rp>)</rp></ruby>に<ruby>戻<rp>(</rp><rt>もど</rt><rp>)</rp></ruby>りましょう。</p>";
		}
		$button = "";
		$make_html->set_file(STUDENT_TEMP_UNIT_COMP_STANDARD);
	} else {
		$morescript .= $change_class_script;
		$make_html->set_file(STUDENT_TEMP_UNIT_COMP);
	}
	if($display_type == 3){
		$suggest_html = get_suggest_button($unit_num,$display_type,$ps_directory,$jh_key,$course_num);
	}else{
		$suggest_html = get_suggest_list($unit_num,$display_type,$stage_key);
		$morescript .= "<script type=\"text/javascript\">\n";
		$morescript .= "$(document).ready(function(){ make_suggest_scroll_bar('suggest_scroll_area',".$suggst_button.") });\n";
		$morescript .= "</script>\n";
	}



	$INPUTS['MORESCRIPT'] = array('result' => 'plane', 'value' => $morescript);
	$INPUTS['COMMENT'] = array('result' => 'plane', 'value' => $comment);
	$INPUTS['BUTTON'] = array('result' => 'plane', 'value' => $button);
	$INPUTS['SUGGEST'] = array('result' => 'plane', 'value' => $suggest_html);
	$make_html->set_rep_cmd($INPUTS);
	$html = $make_html->replace();


	// 画面サイズ調整
	$html = str_replace("<body>", $student_approach_body, $html);

	return $html;
}
/**
 * 目標情報のhtmlを作成(表示用なので固定)
 * @param integer $mode 0:通常すらら 1:新TOP
 * @return string
 */
function make_target_message($mode) {
	if($mode == 1){
		$html= "<div id=\"target_area\">";
		$html.= "<div class=\"target_tab clearfix\">";
		$html.= "<div class=\"title\"><img src=\"/student/images/top/target_list/target_list_title.png\"></div>";
		$html.= "<ul class=\"btn_list\">";
		$html.= "<li class=\"target_check_box\"><input type=\"checkbox\" id=\"check\" ><label for=\"check\">終わった目標を表示する</label></li>";
		$html.= "<li id=\"btn_show_all\" ><a href=\"javascript:void(0);\"></a></li>";
		$html.= "<li id=\"btn_ldesign\" ><a href=\"javascript:void(0);\"></a></li>";
		$html.= "<li id=\"btn_history\" ><a href=\"javascript:void(0);\"></a></li>";
		$html.= "</ul>";
		$html.= "</div>";
		$html.= "<div class=\"scroll_wrap\">";
		$html.= "<table class=\"target_table\">";
		$html.= "</table>";
		$html.= "</div>";
		$html.= "</div>";
	}else{
		//開発用なので5個表示固定
		for ($i = 1; $i <= 5; $i++) {
			$table_html .= "<tr style=\"background-color: #FFFFFF;\">\n";
			$table_html .= " <td class=\"td_blank\">　<br />　</td>\n";
			$table_html .= " <td class=\"td_blank\">　<br />　</td>\n";
			$table_html .= " <td class=\"td_blank\">　<br />　</td>\n";
			$table_html .= " <td class=\"td_blank\">　<br />　</td>\n";
			$table_html .= "</tr>\n";
		}

		$table_th = "<tr>
				<th width=\"50\" style=\"background-image:url(/student/images/etc/bg_date.png); background-repeat: no-repeat;\"></th>
				<th width=\"62\" style=\"background-image:url(/student/images/etc/bg_course.png); background-repeat: no-repeat;\"></th>
				<th width=\"170\" style=\"background-image:url(/student/images/etc/bg_target_name.png); background-repeat: no-repeat;\"></th>
				<th width=\"63\" style=\"background-image:url(/student/images/etc/bg_standard_time.png); background-repeat: no-repeat;\"></th>
			</tr>";
		$target_count = "0";
		$table_in_cellspace = "cellspacing=\"1\"";

		$html = <<< EOT
	<div class="text">{$target_count}</div>
	<div id="goal_table_area">
		<table border="0" frame="void" cellspacing="0" class="goal_tableout">
		<tr><td>
		<table border="0" frame="void" {$table_in_cellspace} class="goal_tablein">
	$table_th
	$table_html
		</table>
		</td></tr>
		</table>
		<div class="see_all_list">
			<td colspan='4'><span>すべて見る</span></td>
		</div>
	</div>
EOT;

	}
	return $html;
}

/**
 * サジェスト確認ボタンを表示
 * @param int $unit_num
 * @param int $display_type 1:通常すらら 2:新TOP 3:ピタドリ
 * @return string
 */
function get_suggest_button($unit_num,$display_type,$ps_directory,$jh_key,$course_num){

	$cdb = $GLOBALS['cdb'];

	$html = "";
	$button_display = false;
	//ピタドリの時は、ドリルとゲームの存在チェックを入れる
	//英国数理社以外の問題かどうかは、確認ボタン表示の際に行っているので不要
	if($display_type == 3){
		$sql = check_suggest_sql($unit_num);
		if ($result = $cdb->query($sql)) {
			while ($list = $cdb->fetch_assoc($result)) {
				if(!$list['problem_count']){
					$game_check_flg = false;
					$game_check_flg = game_file_exist_check($list['course_num'], $list['stage_num'], $list['lesson_num'], $list['suggest_unit_num'], '0');
					if(!$game_check_flg) { continue; }
				}
				$button_display = true;break;
			}
		}
	}else{
		$button_display = true;
	}
	if($button_display){
		$suggest_img1 = "/student/images/button/" .$ps_directory. $jh_key . $course_num . "_32_2.gif";
		$suggest_img2 = "/student/images/button/" .$ps_directory. $jh_key . $course_num . "_32_1.gif";
		$url = "/admin/suggest_list_layer.php?un=".$unit_num."&dsp=".$display_type;
		$exec_javascript = "window.top.modalWindows.open('".$url."',713,'suggest')";
		if(file_exists("../".$suggest_img1)){
			// タブレットの場合は、タッチイベントで動作する
			if (is_tablet()) {
				$button .= "<img src=\"$suggest_img1\" name=\"suggest_button\" alt=\"関連ユニット\"";
				$button .= " onTouchStart=\"HpbImgSwap('suggest_button', '$suggest_img2');\"";
				$button .= " onTouchEnd=\"HpbImgSwap('suggest_button', '$suggest_img1'); ".$exec_javascript."\"";
				$button .= " onTouchCancel=\"HpbImgSwap('suggest_button', '$suggest_img1');\"";
				$button .= " style=\"cursor:pointer;\">\n";
			// Chromeまたはsafariの場合は、onmouseoutが不要。
			} elseif (is_chrome_safari()) {
				$button .= "<img src=\"$suggest_img1\" name=\"suggest_button\" alt=\"関連ユニット\"";
				$button .= " onmousedown=\"HpbImgSwap('suggest_button', '$suggest_img2');\"";
				$button .= " onmouseup=\"HpbImgSwap('suggest_button', '$suggest_img1'); ".$exec_javascript."\"";
				$button .= " style=\"cursor:pointer;\">\n";
			} else {
				$button .= "<img src=\"$suggest_img1\" name=\"suggest_button\" alt=\"関連ユニット\"";
				$button .= " onmousedown=\"HpbImgSwap('suggest_button', '$suggest_img2');\"";
				$button .= " onmouseup=\"HpbImgSwap('suggest_button', '$suggest_img1'); ".$exec_javascript."\"";
				$button .= " onmouseout=\"HpbImgSwap('suggest_button', '$suggest_img1');\"";
				$button .= " style=\"cursor:pointer;\">\n";
			}
		}else{
			$button .= "<div class=\"dft-button\" onClick=\"".$exec_javascript."\">関連ユニット</div>";
		}

		$html .= "<div class=\"suggest-check-button\">";
		$html .= $button;
		$html .= "</div>";
	}

	return $html;
}
/**
 * サジェスト用HTMLを作成
 *
 * @param int $unit_num ユニット番号
 * @return string html情報
 */
function get_suggest_list($unit_num,$display_type,$stage_key) {
	$cdb = $GLOBALS['cdb'];

	global $L_SCHOOL_YEAR;

	$school_year_base = getUnitKeyInitial( $stage_key );

	$html = "<div id =\"suggest_area\">";
	$html .= "<p>";
	$html .= get_message($display_type,$school_year_base);
	$html .= "</p>";
	$html .= "<div id =\"suggest_scroll_area\">";

	$sql = check_suggest_sql($unit_num);
	$c = 0;
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			if(empty($list['course_num']) || $list['course_num'] == 12 || $list['suggest_unit_num'] == $unit_num){//すらら忍者算数は除く
				continue;
			}elseif($list['problem_count'] == 0){
				//問題が０問でゲームも存在しない場合、開けるレクチャーが存在するか確認。
				$game_check_flg = game_file_exist_check($list['course_num'], $list['stage_num'], $list['lesson_num'], $list['suggest_unit_num'],'0');
				if(!$game_check_flg) {
					$lecture_result = lecture_file_exist_check($list['course_num'],$list['stage_num'],$list['lesson_num'],$list['suggest_unit_num'],'0');
					if(!$lecture_result){
						continue;
					}
				}
			}
			if (!$c) {
				$html .= "<ul>";
			}
			$school_year = setSchoolYearNum($list['stage_key']);
			$course_name = getArithmetic($list['stage_key'], $list['course_num'], $list['course_name']);
			$html .= "<li><a href=\"javascript:void(0);\">" .
					$L_SCHOOL_YEAR[$school_year] .
					" " . $course_name . " " .
					$list['stage_name'] . " " .
					$list['lesson_name'] . " " .
					$list['unit_name'] . " ";

			if ($list['unit_name2']) {
				$list['unit_name2'] = preg_replace("/&lt;/", "<", $list['unit_name2']);
				$list['unit_name2'] = preg_replace("/&gt;/", ">", $list['unit_name2']);
			}

			$html .= $list['unit_name2'];
			$html .= "</a></li>";
			$c++;
		}
		$html .= "</ul>";
	}
	if($c>0){
		$html .= "</div>";
	}
	$html .= "</div>";

	return $html;
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
function lecture_file_exist_check($course_num, $stage_num, $lesson_num, $unit_num, $skill_num) {

	$check_flg = false;

	$path_home = "../material/flash/".$course_num."/".$stage_num."/".$lesson_num."/".$unit_num;

	// タブレットの場合
//	if (preg_match("/iPad|iPhone|android/i", $_SERVER['HTTP_USER_AGENT']) === 1) {
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
?>
