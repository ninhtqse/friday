<?PHP
/**
 * すららネット
 *
 * レクチャー確認（bookmode）
 *
 * e-learning system admin check_flash_bookmode
 *
 * 履歴
 * 2018/11/29 初期設定
 *
 * @author Azet
 */

/**
 * メイン処理
 * SESSIONぱらパラメータを取得し、レクチャー確認画面を作成し、表示します。
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function start() {

	// パラメータ取得
	$type = $_SESSION['myid']['check_flash_bookmode']['type'];
	$url_path = $_SESSION['myid']['check_flash_bookmode']['url_path'];
	$url_base_params = $_SESSION['myid']['check_flash_bookmode']['url_base_params'];
	$disabled5 = $_SESSION['myid']['check_flash_bookmode']['disabled5'];
	$current_page = $_SESSION['myid']['check_flash_bookmode']['current_page'];
	$disp_page = $_SESSION['myid']['check_flash_bookmode']['disp_page'];

	// SESSIONはクリアする
	unset($_SESSION['myid']['check_flash_bookmode']);

	// 起動するレクチャーのURLを作成
	$src_url = $url_path;
	$add_params = "";
	if ($type == 1) {
		$add_params  = "&current_page=".$current_page;
		$add_params .= "&status=false&indicator_flag=false";
		$src_url .= "main.swf.html";
	}
	if ($type == 2) {
		$add_params  = "&current_page=".$current_page;
		$add_params .= "&status=true&indicator_flag=true";
		$src_url .= "main.swf.html";
	}
	if ($type == 3) {
		$add_params  = "&current_page=".$current_page;
		$add_params .= "&disp_page=".$disp_page;
		$add_params .= "&status=true&indicator_flag=true";
		$src_url .= "spot.swf.html";
	}
	$src_url .= $url_base_params . $add_params;

	// レクチャー確認画面作成
	$html = "";
	$html .= "<!DOCTYPE html>\n";
	$html .= "<html lang=\"ja\">\n";
	$html .= "<head>\n";
	$html .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\n";
	$html .= "<meta http-equiv=\"Content-Style-Type\" content=\"text/css\">\n";
	$html .= "<title>HTML5(bookmode)動作確認</title>\n";
	$html .= "<style type=\"text/css\">\n";
	$html .= "<!--\n";
	$html .= "body {\n";
	$html .= "	margin: 0;\n";
	$html .= "	padding: 0;\n";
	$html .= "	overflow: hidden;\n";
	$html .= "}\n";
	$html .= "-->\n";
	$html .= "</style>\n";
	$html .= "<script type=\"text/javascript\">\n";
	$html .= "<!--\n";
	$html .= "	function change_scale() {\n";
	$html .= "		var userAgent = navigator.userAgent.toLowerCase();\n";
	$html .= "		if (userAgent.indexOf(\"android\") != -1) {\n";
	$html .= "		$(\"meta[name=viewport]\", parent.document).attr(\"content\",\"initial-scale=1.015\");\n";
	$html .= "	};\n";
	$html .= "}\n";
	$html .= "function auto_resize() {\n";
	$html .= "	var h;\n";
	$html .= "	if ( window.innerHeight ) {\n";
	$html .= "		h = window.innerHeight;\n";
	$html .= "	}\n";
	$html .= "	else if ( document.documentElement && document.documentElement.clientHeight != 0 ) {\n";
	$html .= "		h = document.documentElement.clientHeight;\n";
	$html .= "	}\n";
	$html .= "	else if ( document.body ) {\n";
	$html .= "		h = document.body.clientHeight;\n";
	$html .= "	}\n";
	$html .= "	document.getElementById(\"main_check\").style.height = (h - 95) + \"px\";\n";
	$html .= "}\n";
	$html .= "-->\n";
	$html .= "</script>\n";
	$html .= "</head>\n";

	// レクチャーが存在しない場合はメッセージ表示
	if ($disabled5 > "") {
		$html .= "<body onload=\"change_scale(); auto_resize(); return false;\" onResize=\"auto_resize(); return false;\">";
		$html .= "<br/>　HTML5(bookmode) レクチャーはアップロードされておりません。<br/><br/>";
		$html .= "<form>　<input type=\"button\" value=\"閉じる\" OnClick=\"parent.window.close();\"></form>";
	// レクチャーが存在する場合は、iflameでページを表示する
	} else {
		$html .= "<body onload=\"change_scale(); auto_resize(); return false;\" onResize=\"auto_resize(); return false;\">\n";
		$html .= "<iframe name=\"main\" width=\"100%\" height=\"100%\" id=\"main_check\" src=\"".$src_url."\" frameborder=\"0\" marginwidth=\"0\" marginheight=\"0\" noresize=\"\"></iframe>\n";
		$html .= "<iframe name=\"main\" width=\"100%\" height=\"95px\" id=\"log_check\" src=\"/admin/check_html5.php?erea=page\" frameborder=\"10\" marginwidth=\"0\" marginheight=\"0\" noresize=\"\"></iframe>\n";
		$html .= "</iframe>\n";
	}
	$html .= "</body>\n";
	$html .= "</html>\n";

	return $html;
}
?>