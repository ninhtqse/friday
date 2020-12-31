<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * e-learning system admin 問題確認プログラム（iframe parent）

 * 履歴
 * 2014/06/10 初期設定
 *
 * @author Azet
 */

// add okabe

//	-- スタート --
/**
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * HTMLを作成する機能
 * @author Azet
 * @return string HTML
 */
function start() {

	$open_type = $_POST['_open_type'];
	$tpl = "index_main_ext5a.htm";	//admin用

	unset($_SESSION['admin_disp_problrm']);

	$INPUTS[ADMINTYPENUM] = array('result'=>'plane','value'=>$open_type);

	// admin.js の check_problem_win_open からコールされる。
	// プラクティスステージ管理→問題追加・修正・削除 にて、
	// 問題データの修正に進み、そのページにある[確認]を押したとき。
	if ($open_type == "1") {

		if (isset($_POST['course_num'])) {
			$_SESSION['admin_disp_problrm']['course_num'] = $_POST['course_num'];
		}
		if (isset($_POST['stage_num'])) {
			$_SESSION['admin_disp_problrm']['stage_num'] = $_POST['stage_num'];
		}
		if (isset($_POST['lesson_num'])) {
			$_SESSION['admin_disp_problrm']['lesson_num'] = $_POST['lesson_num'];
		}
		if (isset($_POST['unit_num'])) {
			$_SESSION['admin_disp_problrm']['unit_num'] = $_POST['unit_num'];
		}
		if (isset($_POST['block_num'])) {
			$_SESSION['admin_disp_problrm']['block_num'] = $_POST['block_num'];
		}
		if (isset($_POST['block_type'])) {
			$_SESSION['admin_disp_problrm']['block_type'] = $_POST['block_type'];
		}
		if (isset($_POST['problem_num'])) {
			$_SESSION['admin_disp_problrm']['problem_num'] = $_POST['problem_num'];
		}
		if (isset($_POST['display_problem_num'])) {
			$_SESSION['admin_disp_problrm']['display_problem_num'] = $_POST['display_problem_num'];
		}
		if (isset($_POST['problem_type'])) {
			$_SESSION['admin_disp_problrm']['problem_type'] = $_POST['problem_type'];
		}
		if (isset($_POST['form_type'])) {
			$_SESSION['admin_disp_problrm']['form_type'] = $_POST['form_type'];
		}
		if (isset($_POST['sub_display_problem_num'])) {
			$_SESSION['admin_disp_problrm']['sub_display_problem_num'] = $_POST['sub_display_problem_num'];
		}
		if (isset($_POST['question'])) {
			$_SESSION['admin_disp_problrm']['question'] = $_POST['question'];
		}
		if (isset($_POST['problem'])) {
			$_SESSION['admin_disp_problrm']['problem'] = $_POST['problem'];
		}
		if (isset($_POST['voice_data'])) {
			$_SESSION['admin_disp_problrm']['voice_data'] = $_POST['voice_data'];
		}
		if (isset($_POST['hint'])) {
			$_SESSION['admin_disp_problrm']['hint'] = $_POST['hint'];
		}
		if (isset($_POST['explanation'])) {
			$_SESSION['admin_disp_problrm']['explanation'] = $_POST['explanation'];
		}
		if (isset($_POST['answer_time'])) {
			$_SESSION['admin_disp_problrm']['answer_time'] = $_POST['answer_time'];
		}
		if (isset($_POST['parameter'])) {
			$_SESSION['admin_disp_problrm']['parameter'] = $_POST['parameter'];
		}
		if (isset($_POST['set_difficulty'])) {
			$_SESSION['admin_disp_problrm']['set_difficulty'] = $_POST['set_difficulty'];
		}
		if (isset($_POST['hint_number'])) {
			$_SESSION['admin_disp_problrm']['hint_number'] = $_POST['hint_number'];
		}
		if (isset($_POST['correct_number'])) {
			$_SESSION['admin_disp_problrm']['correct_number'] = $_POST['correct_number'];
		}
		if (isset($_POST['clear_number'])) {
			$_SESSION['admin_disp_problrm']['clear_number'] = $_POST['clear_number'];
		}
		if (isset($_POST['first_problem'])) {
			$_SESSION['admin_disp_problrm']['first_problem'] = $_POST['first_problem'];
		}
		if (isset($_POST['latter_problem'])) {
			$_SESSION['admin_disp_problrm']['latter_problem'] = $_POST['latter_problem'];
		}
		if (isset($_POST['selection_words'])) {
			$_SESSION['admin_disp_problrm']['selection_words'] = $_POST['selection_words'];
		}
		if (isset($_POST['correct'])) {
			$_SESSION['admin_disp_problrm']['correct'] = $_POST['correct'];
		}
		if (isset($_POST['option1'])) {
			$_SESSION['admin_disp_problrm']['option1'] = $_POST['option1'];
		}
		if (isset($_POST['option2'])) {
			$_SESSION['admin_disp_problrm']['option2'] = $_POST['option2'];
		}
		if (isset($_POST['option3'])) {
			$_SESSION['admin_disp_problrm']['option3'] = $_POST['option3'];
		}
		if (isset($_POST['option4'])) {
			$_SESSION['admin_disp_problrm']['option4'] = $_POST['option4'];
		}
		if (isset($_POST['option5'])) {
			$_SESSION['admin_disp_problrm']['option5'] = $_POST['option5'];
		}
		//add start kimura 2018/10/23 漢字学習コンテンツ_書写ドリル対応
		if (isset($_POST['problem_tegaki_flg'])) {
			$_SESSION['admin_disp_problrm']['problem_tegaki_flg'] = $_POST['problem_tegaki_flg'];
			//セッションがONなら修正中の値にかかわらず常に出す
			if($_SESSION['TEGAKI_FLAG'] == 1){
				$_SESSION['admin_disp_problrm']['problem_tegaki_flg'] = $_SESSION['TEGAKI_FLAG'];
			}
		}
		//add end   kimura 2018/10/23 漢字学習コンテンツ_書写ドリル対応
	}

	// admin.js の check_problem_win_open_2 からコールされる。
	// プラクティスステージ管理→問題追加・修正・削除 にて、一覧表示での [確認] ボタン、または
	// プラクティスステージ管理→問題検証 での、[確認] ボタンを押したとき。
	if ($open_type == "2") {	// admin.js の check_problem_win_open_2 からコール
		if (isset($_POST['direct_problem_num'])) {
			$_SESSION['admin_disp_problrm']['direct_problem_num'] = $_POST['direct_problem_num'];
		}
	}

	// admin.js の check_problem_win_open_3 からコールされる。
	// テスト用プラクティス管理→問題設定 または 問題検証 で [確認] ボタンを押したとき。
	if ($open_type == "3") {	// admin.js の check_problem_win_open_3 からコール
		if (isset($_POST['problem_type'])) {
			$_SESSION['admin_disp_problrm']['problem_type'] = $_POST['problem_type'];
		}
		if (isset($_POST['direct_problem_num'])) {
			$_SESSION['admin_disp_problrm']['direct_problem_num'] = $_POST['direct_problem_num'];
		}
		// add start hirose 2020/10/01 テスト標準化開発
		if (isset($_POST['test_type'])) {
			$_SESSION['admin_disp_problrm']['test_type'] = $_POST['test_type'];
		}
		if (isset($_POST['display_id'])) {
			$_SESSION['admin_disp_problrm']['display_id'] = $_POST['display_id'];
		}
		// add end hirose 2020/10/01 テスト標準化開発
	}

	//	画面生成
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file($tpl);
	$make_html->set_rep_cmd($INPUTS);

	$html = $make_html->replace();

	return $html;
}

?>
