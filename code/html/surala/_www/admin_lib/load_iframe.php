<?PHP
/**
 * ベンチャー・リンク すらら
 *
 * e-learning system admin 問題確認プログラム（iframeロード）
 *
 * 履歴
 * 2014/06/10 初期設定
 *
 * @author Azet
 */

// okabe

/**
 * HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function start() {

	$open_type = $_GET['t'];

	$html = "";

	if ($open_type == "1") {
		$html = php_check_problem_win_open();	//check_problem_win_open からの呼び出し
	}

	if ($open_type == "2") {
		$direct_problem_num = $_SESSION['admin_disp_problrm']['direct_problem_num'];
		$html = php_check_problem_win_open_2($direct_problem_num);	//check_problem_win_open_2 からの呼び出し
	}

	if ($open_type == "3") {
		if (isset($_SESSION['admin_disp_problrm']['problem_type'])) {
			$problem_type = $_SESSION['admin_disp_problrm']['problem_type'];
		}
		if (isset($_SESSION['admin_disp_problrm']['direct_problem_num'])) {
			$direct_problem_num = $_SESSION['admin_disp_problrm']['direct_problem_num'];
		}
		// add start hirose 2020/10/01 テスト標準化開発
		if (isset($_SESSION['admin_disp_problrm']['test_type'])) {
			$test_type = $_SESSION['admin_disp_problrm']['test_type'];
		}
		if (isset($_SESSION['admin_disp_problrm']['display_id'])) {
			$display_id = $_SESSION['admin_disp_problrm']['display_id'];
		}
		// add end hirose 2020/10/01 テスト標準化開発
		// upd start hirose 2020/10/01 テスト標準化開発
		// $html = php_check_problem_win_open_3($problem_type, $direct_problem_num);	//check_problem_win_open_3 からの呼び出し
		$html = php_check_problem_win_open_3($problem_type, $direct_problem_num,$test_type,$display_id);	//check_problem_win_open_3 からの呼び出し
		// upd end hirose 2020/10/01 テスト標準化開発
	}

	unset($_SESSION['admin_disp_problrm']);

	return $html;
}



/**
 * admin.js の check_problem_win_open からコールされる。
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * プラクティスステージ管理→問題追加・修正・削除 にて、
 * 問題データの修正に進み、そのページにある[確認]を押したとき。
 * @author Azet
 * @return string HTML
 */
function php_check_problem_win_open() {

$html = <<<EOT
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title>問題確認画面</title>
<script type="text/javascript">
function start() {
	window.resizeTo(window.parent.innerWidth, window.parent.innerHeight);
	form.submit();
}
</script>
</head>
<body onload="start();return false;">
<form action="/admin/check_problem.php" method="POST" name="form">
EOT;

if (isset($_SESSION['admin_disp_problrm']['course_num'])) {
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_SESSION['admin_disp_problrm']['course_num']."\">";
}
if (isset($_SESSION['admin_disp_problrm']['stage_num'])) {
	$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$_SESSION['admin_disp_problrm']['stage_num']."\">";
}
if (isset($_SESSION['admin_disp_problrm']['lesson_num'])) {
	$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_SESSION['admin_disp_problrm']['lesson_num']."\">";
}
if (isset($_SESSION['admin_disp_problrm']['unit_num'])) {
	$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"".$_SESSION['admin_disp_problrm']['unit_num']."\">";
}
if (isset($_SESSION['admin_disp_problrm']['block_num'])) {
	$html .= "<input type=\"hidden\" name=\"block_num\" value=\"".$_SESSION['admin_disp_problrm']['block_num']."\">";
}
if (isset($_SESSION['admin_disp_problrm']['block_type'])) {
	$html .= "<input type=\"hidden\" name=\"block_type\" value=\"".$_SESSION['admin_disp_problrm']['block_type']."\">";
}
if (isset($_SESSION['admin_disp_problrm']['problem_num'])) {
	$html .= "<input type=\"hidden\" name=\"problem_num\" value=\"".$_SESSION['admin_disp_problrm']['problem_num']."\">";
}
if (isset($_SESSION['admin_disp_problrm']['display_problem_num'])) {
	$html .= "<input type=\"hidden\" name=\"display_problem_num\" value=\"".$_SESSION['admin_disp_problrm']['display_problem_num']."\">";
}
if (isset($_SESSION['admin_disp_problrm']['problem_type'])) {
	$html .= "<input type=\"hidden\" name=\"problem_type\" value=\"".$_SESSION['admin_disp_problrm']['problem_type']."\">";
}
if (isset($_SESSION['admin_disp_problrm']['form_type'])) {
	$html .= "<input type=\"hidden\" name=\"form_type\" value=\"".$_SESSION['admin_disp_problrm']['form_type']."\">";
}
if (isset($_SESSION['admin_disp_problrm']['sub_display_problem_num'])) {
	$html .= "<input type=\"hidden\" name=\"sub_display_problem_num\" value=\"".$_SESSION['admin_disp_problrm']['sub_display_problem_num']."\">";
}
if (isset($_SESSION['admin_disp_problrm']['question'])) {
	$html .= "<input type=\"hidden\" name=\"question\" value=\"".$_SESSION['admin_disp_problrm']['question']."\">";
}
if (isset($_SESSION['admin_disp_problrm']['problem'])) {
	$html .= "<input type=\"hidden\" name=\"problem\" value=\"".$_SESSION['admin_disp_problrm']['problem']."\">";
}
if (isset($_SESSION['admin_disp_problrm']['voice_data'])) {
	$html .= "<input type=\"hidden\" name=\"voice_data\" value=\"".$_SESSION['admin_disp_problrm']['voice_data']."\">";
}
if (isset($_SESSION['admin_disp_problrm']['hint'])) {
	$html .= "<input type=\"hidden\" name=\"hint\" value=\"".$_SESSION['admin_disp_problrm']['hint']."\">";
}
if (isset($_SESSION['admin_disp_problrm']['explanation'])) {
	$html .= "<input type=\"hidden\" name=\"explanation\" value=\"".$_SESSION['admin_disp_problrm']['explanation']."\">";
}
if (isset($_SESSION['admin_disp_problrm']['answer_time'])) {
	$html .= "<input type=\"hidden\" name=\"answer_time\" value=\"".$_SESSION['admin_disp_problrm']['answer_time']."\">";
}
if (isset($_SESSION['admin_disp_problrm']['parameter'])) {
	$html .= "<input type=\"hidden\" name=\"parameter\" value=\"".$_SESSION['admin_disp_problrm']['parameter']."\">";
}
if (isset($_SESSION['admin_disp_problrm']['set_difficulty'])) {
	$html .= "<input type=\"hidden\" name=\"set_difficulty\" value=\"".$_SESSION['admin_disp_problrm']['set_difficulty']."\">";
}
if (isset($_SESSION['admin_disp_problrm']['hint_number'])) {
	$html .= "<input type=\"hidden\" name=\"hint_number\" value=\"".$_SESSION['admin_disp_problrm']['hint_number']."\">";
}
if (isset($_SESSION['admin_disp_problrm']['correct_number'])) {
	$html .= "<input type=\"hidden\" name=\"correct_number\" value=\"".$_SESSION['admin_disp_problrm']['correct_number']."\">";
}
if (isset($_SESSION['admin_disp_problrm']['clear_number'])) {
	$html .= "<input type=\"hidden\" name=\"clear_number\" value=\"".$_SESSION['admin_disp_problrm']['clear_number']."\">";
}
if (isset($_SESSION['admin_disp_problrm']['first_problem'])) {
	$html .= "<input type=\"hidden\" name=\"first_problem\" value=\"".$_SESSION['admin_disp_problrm']['first_problem']."\">";
}
if (isset($_SESSION['admin_disp_problrm']['latter_problem'])) {
	$html .= "<input type=\"hidden\" name=\"latter_problem\" value=\"".$_SESSION['admin_disp_problrm']['latter_problem']."\">";
}
if (isset($_SESSION['admin_disp_problrm']['selection_words'])) {
	$html .= "<input type=\"hidden\" name=\"selection_words\" value=\"".$_SESSION['admin_disp_problrm']['selection_words']."\">";
}
if (isset($_SESSION['admin_disp_problrm']['correct'])) {
	$html .= "<input type=\"hidden\" name=\"correct\" value=\"".$_SESSION['admin_disp_problrm']['correct']."\">";
}
if (isset($_SESSION['admin_disp_problrm']['option1'])) {
	$html .= "<input type=\"hidden\" name=\"option1\" value=\"".$_SESSION['admin_disp_problrm']['option1']."\">";
}
if (isset($_SESSION['admin_disp_problrm']['option2'])) {
	$html .= "<input type=\"hidden\" name=\"option2\" value=\"".$_SESSION['admin_disp_problrm']['option2']."\">";
}
if (isset($_SESSION['admin_disp_problrm']['option3'])) {
	$html .= "<input type=\"hidden\" name=\"option3\" value=\"".$_SESSION['admin_disp_problrm']['option3']."\">";
}
if (isset($_SESSION['admin_disp_problrm']['option4'])) {
	$html .= "<input type=\"hidden\" name=\"option4\" value=\"".$_SESSION['admin_disp_problrm']['option4']."\">";
}
if (isset($_SESSION['admin_disp_problrm']['option5'])) {
	$html .= "<input type=\"hidden\" name=\"option5\" value=\"".$_SESSION['admin_disp_problrm']['option5']."\">";
}
//add start kimura 2018/10/23 漢字学習コンテンツ_書写ドリル対応
if (isset($_SESSION['admin_disp_problrm']['problem_tegaki_flg'])) {
	$html .= "<input type=\"hidden\" name=\"problem_tegaki_flg\" value=\"".$_SESSION['admin_disp_problrm']['problem_tegaki_flg']."\">";
}
//add end   kimura 2018/10/23 漢字学習コンテンツ_書写ドリル対応


$html .= <<<EOT
</form>
</body>
</html>
EOT;

	return $html;
}


/**
 * admin.js の check_problem_win_open_2 からコールされる。
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * プラクティスステージ管理→問題追加・修正・削除 にて、一覧表示での [確認] ボタン、または
 * プラクティスステージ管理→問題検証 での、[確認] ボタンを押したとき。
 * @author Azet
 * @param integer $direct_problem_num
 * @return string HTML
 */
function php_check_problem_win_open_2($direct_problem_num) {

$html = <<<EOT
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title>問題確認画面</title>
<script type="text/javascript">
function start() {
	window.resizeTo(window.parent.innerWidth, window.parent.innerHeight);
	form.submit();
}
</script>
</head>
<body onload="start();return false;">
<form action="/admin/check_problem.php" method="POST" name="form">
EOT;


if ($direct_problem_num) {
	$html .= "<input type=\"hidden\" name=\"direct_problem_num\" value=\"".$direct_problem_num."\">";
}


$html .= <<<EOT
</form>
</body>
</html>
EOT;

	return $html;
}





/**
 * admin.js の check_problem_win_open_3 からコールされる。
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * テスト用プラクティス管理→問題設定 または 問題検証 で [確認] ボタンを押したとき。
 * @author Azet
 * @param integer $problem_type
 * @param integer $direct_problem_num
 * @return string HTML
 */
// upd start hirose 2020/10/01 テスト標準化開発
// function php_check_problem_win_open_3($problem_type, $direct_problem_num) {
function php_check_problem_win_open_3($problem_type, $direct_problem_num,$test_type,$display_id) {
// upd end hirose 2020/10/01 テスト標準化開発

$html = <<<EOT
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title>問題確認画面</title>
<script type="text/javascript">
function start() {
	window.resizeTo(window.parent.innerWidth, window.parent.innerHeight);
	form.submit();
}
</script>
</head>
<body onload="start();return false;">
EOT;

// comment start oda 2018/10/29 他案件アップの為、コメント
//update start kimura 2018/10/26 すらら英単語
// $html.= "<form action=\"/admin/check_test_problem.php\" method=\"POST\" name=\"form\">";
//ヒアドキュメントformを外にだしました
//単語の問題は別の専用プログラムへリクエストする
if($problem_type == 3){
	$html.= "<form action=\"/admin/check_vocabulary_test_problem.php\" method=\"POST\" name=\"form\">";
//通常のテストはこっちへ
}else{
	$html.= "<form action=\"/admin/check_test_problem.php\" method=\"POST\" name=\"form\">";
}
//update end   kimura 2018/10/26 すらら英単語
// comment end oda 2018/10/29 他案件アップの為、コメント


if ($problem_type) {
	$html .= "<input type=\"hidden\" name=\"problem_type\" value=\"".$problem_type."\">\n";
}
if ($direct_problem_num) {
	$html .= "<input type=\"hidden\" name=\"direct_problem_num\" value=\"".$direct_problem_num."\">\n";
}
// add start hirose 2020/10/01 テスト標準化開発
if ($test_type) {
	$html .= "<input type=\"hidden\" name=\"test_type\" value=\"".$test_type."\">\n";
}
if ($display_id) {
	$html .= "<input type=\"hidden\" name=\"display_id\" value=\"".$display_id."\">\n";
}
// add end hirose 2020/10/01 テスト標準化開発


$html .= <<<EOT
</form>
</body>
</html>
EOT;

	return $html;
}


?>
