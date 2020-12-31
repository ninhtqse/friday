<?PHP
/**
 * すららネット
 *
 * admin画面用、HTML5(bookmode)ログ確認
 *
 * 履歴
 * 2014/12/05 初期設定
 *
 * @author Azet
 */

// okabe

/**
 * GETパラメター判断して、HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[M99]その他.
 *
 * @author Azet
 * @return string HTML
 */
function start() {

	if ($_GET['erea'] == "page") {
		$html = page_erea_html();
	} elseif ($_GET['erea'] == "end") {
		$html = end_html();
	// del start oda 2018/11/29 BCブラウザ対応
//	} else {
//		$html = frame_html();
	// del end oda 2018/11/29
	}

	return $html;
}



// del start oda 2018/11/29 BCブラウザ対応
// /**
//  * フレーム
//  *
//  * AC:[A]管理者 UC1:[M99]その他.
//  *
//  * @author Azet
//  * @return string HTML
//  */
// function frame_html() {

// 	unset($_SESSION['CHECK_FLASH']);

// 	if ($_POST) {
// 		foreach ($_POST AS $key => $val) {
// 			$val = mb_convert_kana($val,"as","UTF-8");
// 			$_SESSION['CHECK_FLASH'][$key] = $val;
// 		}
// 	}

// 	echo <<<EOT
// <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
// <html lang="ja">
// <head>
// <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
// <meta http-equiv="Content-Style-Type" content="text/css" />
// <title>HTML5動作確認</title>
// </head>
// <frameset rows="100%">
// <frame name="page_erea" src="$_SERVER[PHP_SELF]?erea=page" marginwidth="0" marginheight="0" scrolling="YES" frameborder="10"/>
// <noframes>
// <body>
// このページを表示するには、フレームをサポートしているブラウザが必要です。
// </body>
// </noframes>
// </frameset>
// </html>
// EOT;

// 	return $html;
// }
// del end oda 2018/11/29



/**
 * ページ情報表示領域
 *
 * AC:[A]管理者 UC1:[M99]その他.
 *
 * @author Azet
 * @return string HTML
 */
function page_erea_html() {

	$html  = "<a name=\"top\"></a>\n";

	// add start oda 2017/06/12 リアルサムライ様のコンテンツにてflashVarsがよみこまれているかチェックするロジックを追加
	$html .= "<script type=\"text/javascript\" src=\"/javascript/userAgent.js\"></script>\n"; //kaopiz add 2020/07/15 ipados13
	$html .= "<script>\n";
//	$html .= "if(/iPad/.test(navigator.userAgent)) {\n";
	$html .= "if(is_ipad()) {\n"; //kaopiz update 2020/07/15 ipados13
	$html .= "setTimeout(function() {if (window.top.main.make5_flashvars) { var message = document.createElement('span'); message.innerHTML = 'make5_flashvars'; document.body.appendChild(message); } else { var message = document.createElement('span'); message.innerHTML = 'not make5_flashvars'; document.body.appendChild(message); } }, 1000); ";
	$html .= "}\n";
	$html .= "</script>\n";
	// add end oda 2017/06/12

	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"GET\" >\n";
	$html .= "<input type=\"hidden\" name=\"erea\" value=\"page\">\n";
	$html .= "<input type=\"submit\" value=\"更新する\">\n";
	$html .= "</form>\n";

	if ($_SESSION['CHECK_FLASH']['log']) {
		$max = count($_SESSION['CHECK_FLASH']['log']) - 1;
		for ($i=$max; $i>=0; $i--) {
			$html .= $i." : ページ => ".$_SESSION['CHECK_FLASH']['log'][$i][display_problem_num];
			if ($_SESSION['CHECK_FLASH']['log'][$i][answer]) {
				$html .= " (まとめプリント)";
			}
			$html .= " [".$_SESSION['CHECK_FLASH']['log'][$i][regist_time]."]　　<a href=\"#top\">上へ</a>\n";
			$html .= "<hr>\n";
		}
	} else {
		$html .= <<<EOT
ログが記録されておりません。<br>

EOT;
	}

	$INPUTS = array();
	$INPUTS['FLASH']  = array('result'=>'plane','value'=>$html);

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(PRACTICE_TEMP_LESSON);
	$make_html->set_rep_cmd($INPUTS);
	$html = $make_html->replace();

	return $html;
}



/**
 * FLASH終了
 *
 * AC:[A]管理者 UC1:[M99]その他.
 *
 * @author Azet
 * @return string HTML
 */
function end_html() {

	$html  = "<br>\n";
	$html .= "　HTML5(bookmode)終了<br>\n";
	$html .= "<br>\n";
	$html .= "<form>　<input type=\"button\" value=\"閉じる\" OnClick=\"parent.window.close();\"></form>\n";

	$INPUTS = array();
	$INPUTS['FLASH']  = array('result'=>'plane','value'=>$html);

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(PRACTICE_TEMP_LESSON);
	$make_html->set_rep_cmd($INPUTS);
	$html = $make_html->replace();

	return $html;
}
?>
