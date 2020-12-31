<?php
/**
 * ベンチャー・リンク　すらら
 *
 * e-learning system ワンポイント確認プログラム
 *
 * @author Azet
 */


/**
 * 判断して、HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @return string HTML
 */
function display_word() {

	if ($_POST['english_word_num']) {
		$html = make_check_html($ERROR);
	} else {
		$html = "パラメータエラー<br>ワード管理番号が取得できません。<br>";
	}

	return $html;
}


/**
 * ワード表示
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function make_check_html($ERROR) {

	global $L_PART_OF_SPEECH_TYPE;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$english_word_num		= $_POST['english_word_num'];
	$course_num				= "";
	$word_type				= "";
	$english				= "";
	$japanese_translation	= "";
	$part_of_speech_type	= "";
	$voice_data				= "";
	$example_sentence		= "";
	$example_sentence_jp	= "";
	$example_sentence_voice	= "";
	$course_name	= "";

	//	ワード情報取得
	$sql  = "SELECT ".
			" ew.course_num, ".
			" ew.word_type, ".
			" ew.english, ".
			" ew.japanese_translation, ".
			" ew.part_of_speech_type, ".
			" ew.voice_data, ".
			" ew.example_sentence, ".
			" ew.example_sentence_jp, ".
			" ew.example_sentence_voice, ".
			" co.course_name ".
			"FROM ".T_ENGLISH_WORD." ew ".
			" LEFT JOIN ".T_COURSE." co ON ew.course_num = co.course_num AND co.state = '0' ".
			" WHERE ew.english_word_num = '".$english_word_num."'".
			" LIMIT 1;";

//echo $sql."<br>";

	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$course_num				= $list['course_num'];
		$word_type				= $list['word_type'];
		$english				= $list['english'];
		$japanese_translation	= $list['japanese_translation'];
		$part_of_speech_type	= $list['part_of_speech_type'];
		$voice_data				= $list['voice_data'];
		$example_sentence		= $list['example_sentence'];
		$example_sentence_jp	= $list['example_sentence_jp'];
		$example_sentence_voice	= $list['example_sentence_voice'];
		$course_name			= $list['course_name'];
	}

	// 画面サイズに合わせる
	$size_check = "zoom_all(".$course_num.");";
	$ONLOAD_BODY = "<body onload=\"".$size_check."\">";

	$make_html = new read_html();
	$make_html->set_dir(STUDENT_TEMP_DIR);
	$make_html->set_file("toeic_english_word.htm");

	// 画面表示加工
	$next_url		= "return false;";
	$list_url		= "window.close(); return false;";

	$japanese_translation = rtrim($japanese_translation);
	$japanese_translation = replace_decode($japanese_translation);
	$japanese_translation = change_img($japanese_translation, $course_num);

	$example_sentence = rtrim($example_sentence);
	$example_sentence = replace_decode($example_sentence);

	$example_sentence_jp = rtrim($example_sentence_jp);
	$example_sentence_jp = replace_decode($example_sentence_jp);
	$example_sentence_jp = change_img($example_sentence_jp, $course_num);

	// 音声変換
	$voice_data = change_voice_file($voice_data, $course_num, "1");
	// 音声変換
	$example_sentence_voice = change_voice_file($example_sentence_voice, $course_num, "2");

	// 品詞表示／非表示により単語表示位置調整  2012/12/05 add oda
	if ($part_of_speech_type > 0) {
		$english = "<span style=\"display: inline-block; margin-left:125px;\">".$english."</span>";
	}

	// course_main.cssを適用 2012/12/06 add oda
	$css_path = MATERIAL_TEMP_DIR.$course_num."/course_main.css";
	$css_path = "<link rel=\"stylesheet\" type=\"text/css\" href=\"".$css_path."\" />";
	$INPUTS['MORECLASS'] = array('result'=>'plane','value'=>$css_path);

	$INPUTS['NEXTURL'] = array('result'=>'plane','value'=>$next_url);
	$INPUTS['LISTURL'] = array('result'=>'plane','value'=>$list_url);
	$INPUTS['ENGLISH'] = array('result'=>'plane','value'=>$english);
	$INPUTS['JAPANESETRANSLATION'] = array('result'=>'plane','value'=>$japanese_translation);
	$INPUTS['PARTOFSPEECHTYPE'] = array('result'=>'plane','value'=>$L_PART_OF_SPEECH_TYPE[$part_of_speech_type]);
	$INPUTS['WORDVOICE'] = array('result'=>'plane','value'=>$voice_data);
	$INPUTS['EXAMPLESENTENCE'] = array('result'=>'plane','value'=>$example_sentence);
	$INPUTS['EXAMPLESENTENCEJP'] = array('result'=>'plane','value'=>$example_sentence_jp);
	$INPUTS['EXAMPLEVOICE'] = array('result'=>'plane','value'=>$example_sentence_voice);

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
 * @return string HTML
 */
function change_img($html, $course_num) {

	preg_match_all("|\[!IMG=(.*)!\]|U",$html,$IMG);

	if ($IMG) {
		foreach ($IMG[1] AS $key => $VAL) {
			$img_name = $IMG[0][$key];
			list($file_name_,$width_,$height_) = explode(",",$VAL);
			$file = MATERIAL_WORD_IMG_DIR.$course_num."/".$file_name_;
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
 * @param string $file_name
 * @param integer $course_num
 * @param mixed $no
 * @return string HTML
 */
function change_voice_file($file_name, $course_num, $no) {

	$voice_file = MATERIAL_WORD_VOICE_DIR.$course_num."/".$file_name;
	if ($file_name && file_exists($voice_file)) {
		$http = "http://";
		if($_SERVER['HTTPS']=='on'){
			$http = "https://";
		}
		$html = <<<EOT
<!-- SOUND START-->
<a href="javascript:void(0);" OnClick="play_sound('sound{$no}');"><img src="/student/images/toeic/icon_sound.gif" width="60" height="60" border="0" /></a>
<audio src="{$voice_file}" id="sound{$no}">
  <object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="{$http}fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" width="0" height="0" id="sound_flash">
  <param name="movie" value="/student/images/drill/play_sound.swf?file={$voice_file}" />
  <embed src="/student/images/drill/play_sound.swf?file={$voice_file}" width="0" height="0" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="{$http}www.adobe.com/go/getflash">
  </embed>
  </object>
</audio>
<!-- SOUND END-->
EOT;
	} else {
		$html = "<div width=\"60\" height=\"60\" style=\"display:block\" />&nbsp;</div>";
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