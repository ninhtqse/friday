<?php
/**
 * ベンチャー・リンク　すらら
 *
 * e-learning system ワンポイント確認プログラム
 *
 * @author Azet
 */


/**
 * POSTデータ判断してHTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @return string HTML
 */
function display_one_point() {

	if ($_POST['one_point_num']) {
		$html = make_check_html($ERROR);
	} else {
		if ($_POST['unit_num']) {
			$html = make_check_html($ERROR);
		} else {
			$html = "パラメータエラー<br>ワンポイント解説管理番号またはユニット番号が取得できません。<br>";
		}
	}

	return $html;
}


/**
 * ワンポイント解説表示
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function make_check_html($ERROR) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$one_point_num	= $_POST['one_point_num'];
	$course_num		= "";
	$stage_num		= "";
	$lesson_num		= "";
	$unit_num		= $_POST['unit_num'];
	$study_title	= "";
	$one_point_commentary	= "";
	$course_name	= "";
	$lesson_name	= "";
	$unit_name		= "";

	if ($one_point_num) {
		//	ワンポイント解説情報取得
		$sql  = "SELECT ".
				" op.course_num, ".
				" op.stage_num, ".
				" op.lesson_num, ".
				" op.unit_num, ".
				" op.study_title, ".
				" op.one_point_commentary, ".
				" co.course_name, ".
				" le.lesson_name, ".
				" ut.unit_name ".
				"FROM ".T_ONE_POINT." op ".
				" LEFT JOIN ".T_COURSE." co ON op.course_num = co.course_num AND co.state = '0' ".
				" LEFT JOIN ".T_LESSON." le ON op.lesson_num = le.lesson_num AND le.state = '0' ".
				" LEFT JOIN ".T_UNIT."   ut ON op.unit_num   = ut.unit_num   AND ut.state = '0' ".
				" WHERE op.one_point_num = '".$one_point_num."'".
				" LIMIT 1;";

//echo $sql."<br>";

		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$course_num		= $list['course_num'];
			$stage_num		= $list['stage_num'];
			$lesson_num		= $list['lesson_num'];
			$unit_num		= $list['unit_num'];
			$study_title	= $list['study_title'];
			$one_point_commentary	= $list['one_point_commentary'];
			$course_name	= $list['course_name'];
			$lesson_name	= $list['lesson_name'];
			$unit_name		= $list['unit_name'];
		}
	} else {
		$sql  = "SELECT ".
				" ut.course_num, ".
				" ut.stage_num, ".
				" ut.lesson_num, ".
				" ut.unit_num, ".
				" co.course_name, ".
				" le.lesson_name, ".
				" ut.unit_name ".
				"FROM ".T_UNIT." ut ".
				" LEFT JOIN ".T_COURSE." co ON ut.course_num = co.course_num AND co.state = '0' ".
				" LEFT JOIN ".T_LESSON." le ON ut.lesson_num = le.lesson_num AND le.state = '0' ".
				" WHERE ut.unit_num = '".$unit_num."'".
				" LIMIT 1;";

//echo $sql."<br>";

		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$course_num		= $list['course_num'];
			$stage_num		= $list['stage_num'];
			$lesson_num		= $list['lesson_num'];
			$unit_num		= $list['unit_num'];
			$course_name	= $list['course_name'];
			$lesson_name	= $list['lesson_name'];
			$unit_name		= $list['unit_name'];
		}
	}

	// メンテナンス画面から遷移した場合、入力値を設定  2012/10/22 add oda
	if ($_POST['study_title']) {
//		$study_title = $_POST['study_title'];										// del oda 2012/12/10
		$study_title = replace_encode($_POST['study_title']);						// add oda 2012/12/10
	}
	if ($_POST['one_point_commentary']) {
//		$one_point_commentary = $_POST['one_point_commentary'];						// del oda 2012/12/10
		$one_point_commentary = replace_encode($_POST['one_point_commentary']);		// add oda 2012/12/10
	}

	// 画面サイズに合わせる
	$size_check = "zoom_all(".$course_num.");";
	$ONLOAD_BODY = "<body onload=\"".$size_check."\">";

	$make_html = new read_html();
	$make_html->set_dir(STUDENT_TEMP_DIR);
	$make_html->set_file("toeic_one_point.htm");

	// 画面表示加工
	$course_name_top = substr($course_name, 0, 3);

	$unit_count		= substr($lesson_name, 4);
	$back_url		= "window.close(); return false;";
	$top_url		= "return false;";

	$study_title = rtrim($study_title);
	$study_title = replace_decode($study_title);

	$one_point_commentary = rtrim($one_point_commentary);
	$one_point_commentary = replace_decode($one_point_commentary);

	// イメージ変換
	$one_point_commentary = change_img($one_point_commentary, $course_num, $stage_num, $lesson_num, $unit_num);

	// 音声変換
	$one_point_commentary = change_voice($one_point_commentary, $course_num, $stage_num, $lesson_num, $unit_num);

	// course_main.cssを適用 2012/12/06 add oda
	$css_path = MATERIAL_TEMP_DIR.$course_num."/course_main.css";
	$css_path = "<link rel=\"stylesheet\" type=\"text/css\" href=\"".$css_path."\" />";
	$INPUTS['MORECLASS'] = array('result'=>'plane','value'=>$css_path);

	$INPUTS['COURSENAME'] = array('result'=>'plane','value'=>$course_name_top);
	$INPUTS['UNITCOUNT'] = array('result'=>'plane','value'=>$unit_count);
	$INPUTS['BACKURL'] = array('result'=>'plane','value'=>$back_url);
	$INPUTS['TOPURL'] = array('result'=>'plane','value'=>$top_url);
	$INPUTS['UNITNAME'] = array('result'=>'plane','value'=>$unit_name);
	$INPUTS['STUDYTITLE'] = array('result'=>'plane','value'=>$study_title);
	$INPUTS['ONEPOINTCOMMENTARY'] = array('result'=>'plane','value'=>$one_point_commentary);

	$make_html->set_rep_cmd($INPUTS);
	$html = $make_html->replace();
	if ($ONLOAD_BODY) { $html = ereg_replace("<body>",$ONLOAD_BODY,$html); }

	// エラー時はエラーメッセージを表示
	if ($ERROR) {
		$html .= ERROR($ERROR)."<br>\n";
	}

	return $html;
}


/**
 * 画像変換
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @param string $html
 * @param integer $course_num
 * @param integer $stage_num
 * @param integer $lesson_num
 * @param integer $unit_num
 * @return string HTML
 */
function change_img($html, $course_num, $stage_num, $lesson_num, $unit_num) {

	preg_match_all("|\[!IMG=(.*)!\]|U",$html,$IMG);

	if ($IMG) {
		foreach ($IMG[1] AS $key => $VAL) {
			$img_name = $IMG[0][$key];
			list($file_name_,$width_,$height_) = explode(",",$VAL);

			//$file = MATERIAL_POINT_IMG_DIR.$course_num."/".$stage_num."/".$lesson_num."/".$unit_num."/".$file_name_;	//	del ookawara 2013/09/26

			//	add ookawara 2013/09/26 start
			$file = MATERIAL_POINT_IMG_DIR.$course_num."/".$file_name_;
			if (!file_exists($file)) {
				$file = MATERIAL_POINT_IMG_DIR.$course_num;
				if ($stage_num > 0) {
					$file .= "/".$stage_num;
				}
				if ($lesson_num > 0) {
					$file .= "/".$lesson_num;
				}
				if ($unit_num > 0) {
					$file .= "/".$unit_num;
				}
				$file .= "/".$file_name_;
			}
			//	add ookawara 2013/09/26 end

			if (file_exists($file)) {
				$width = "";
				$height = "";
				if ($width_) {
					$width = "width=\"{$width_}\"";
				}
				if ($height_) {
					$height = "height=\"{$height_}\"";
				}
				$img_ = "<img src=\"$file\" {$width} {$height}>";
				$img_name = change_en($img_name);
				$html = preg_replace("/{$img_name}/",$img_,$html);
			}
		}
	}
	return $html;
}


/**
 * 音声変換
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @param string $html
 * @param integer $course_num
 * @param integer $stage_num
 * @param integer $lesson_num
 * @param integer $unit_num
 * @return string HTML
 */
function change_voice($html, $course_num, $stage_num, $lesson_num, $unit_num) {
	preg_match_all("|\[!VOICE=(.*)!\]|U",$html,$VOICE);

	if ($VOICE) {
		foreach ($VOICE[1] AS $key => $VAL) {
			$voice_name = $VOICE[0][$key];
			$file_name_ = $VAL;

			//$voice_file = MATERIAL_POINT_VOICE_DIR.$course_num."/".$stage_num."/".$lesson_num."/".$unit_num."/".$file_name_;	//	del ookawara 2013/09/26

			//	add ookawara 2013/09/26 start
			$file = MATERIAL_POINT_VOICE_DIR.$course_num."/".$file_name_;
			if (!file_exists($file)) {
				$voice_file = MATERIAL_POINT_VOICE_DIR.$course_num;
				if ($stage_num > 0) {
					$voice_file .= "/".$stage_num;
				}
				if ($lesson_num > 0) {
					$voice_file .= "/".$lesson_num;
				}
				if ($unit_num > 0) {
					$voice_file .= "/".$unit_num;
				}
				$voice_file .= "/".$file_name_;
			}
			//	add ookawara 2013/09/26 end

			unset($voice_);
			$voice_name = change_en($voice_name);
			if ($file_name_ && file_exists($voice_file)) {
				$http = "http://";
				if($_SERVER['HTTPS']=='on'){
					$http = "https://";
				}
				$voice_ = <<<EOT
<!-- SOUND START-->
<a href="javascript:void(0);" OnClick="play_sound('sound');"><img src="/student/images/toeic/icon_sound_s.gif" width="60" height="60" border="0" /></a>
<audio src="{$voice_file}" id="sound">
  <object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="{$http}fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" width="0" height="0" id="sound_flash">
  <param name="movie" value="/student/images/drill/play_sound.swf?file={$voice_file}" />
  <embed src="/student/images/drill/play_sound.swf?file={$voice_file}" width="0" height="0" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="{$http}www.adobe.com/go/getflash">
  </embed>
  </object>
</audio>
<!-- SOUND END-->
EOT;
				$html = preg_replace("/{$voice_name}/",$voice_,$html);
			}
		}
	}
	return $html;
}


/**
 * []()変換
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @param string $text
 * @return string
 */
function change_en($text) {
	$text = ereg_replace("\[","\\[",$text);
	$text = ereg_replace("\(","\\(",$text);
	$text = ereg_replace("\)","\\)",$text);
	$text = ereg_replace("\]","\\]",$text);
	$text = ereg_replace("\*","\\*",$text);
	$text = ereg_replace("\!","\\!",$text);
	return $text;
}

?>
