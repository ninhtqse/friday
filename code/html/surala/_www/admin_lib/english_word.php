<?
/**
 * 旺文社・ＴＯＥＩＣ管理　ワード管理
 *
 * 漢字学習コンテンツの単語もenglish_wordとして管理することになりました。(2018/09/28)
 *
 * 履歴
 * 2012/05/28 初期設定
 *
 * @author Azet
 */
// ★本ファイルは「プラクティスステージ管理」→「ワード管理」と「テストプラクティス管理」→「ワード管理」で共通ロジックになります。
// add oda

/**
 * メイン処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */

function start() {

	// サブセション取得
	$ERROR = sub_session();

	// コース情報取得
	$html .= select_course();
	// コース選択後、処理アクションにより、フローを制御
	if ($_SESSION['sub_session']['course_num']) {
		// チェック処理
		if (ACTION == "check") {
			$ERROR = check();
		}

		// 登録・修正・削除
		if (!$ERROR) {
			if (ACTION == "add") { $ERROR = add(); }
			elseif (ACTION == "change") { $ERROR = change(); }
			elseif (ACTION == "del") { $ERROR = change(); }
		}
		// 登録処理
		if (MODE == "add") {
			if (ACTION == "check") {
				if (!$ERROR) { $html .= check_html(); }
				else { $html .= addform($ERROR); }
			} elseif (ACTION == "add") {
				if (!$ERROR) { $html .= english_word_list($ERROR); }
				else { $html .= addform($ERROR); }
			} else {
				$html .= addform($ERROR);
			}

		// 詳細画面遷移
		} elseif (MODE == "詳細") {
			if (ACTION == "check") {
				if (!$ERROR) { $html .= check_html(); }
				else { $html .= viewform($ERROR); }
			} elseif (ACTION == "change") {
				if (!$ERROR) { $html .= english_word_list($ERROR); }
				else { $html .= viewform($ERROR); }
			} else {
				$html .= viewform($ERROR);
			}

		// 削除処理
		} elseif (MODE == "削除") {
			if (ACTION == "check") {
				if (!$ERROR) { $html .= check_html(); }
				else { $html .= viewform($ERROR); }
			} elseif (ACTION == "change") {
				if (!$ERROR) { $html .= english_word_list($ERROR); }
				else { $html .= viewform($ERROR); }
			} else {
				$html .= check_html();
			}
		} elseif (MODE == "english_word_upload") {
			$ERROR = english_word_upload();
			$html .= english_word_list($ERROR);
		// 一覧表示
		} else {
			$html .= english_word_list($ERROR);
		}
	}

	return $html;
}


/**
 * 一覧表示処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function english_word_list($ERROR) {
	// グローバル変数
	global $L_PAGE_VIEW,$L_WORD_TYPE,$L_PART_OF_SPEECH_TYPE,$L_DISPLAY,$L_WORD_FIELD_ALIAS; ////update kimura 2018/09/28 漢字学習コンテンツ_書写ドリル対応 $L_WORD_FIELD_ALIASも読み込み
	//	add 2015/01/07 yoshizawa 課題要望一覧No.400対応
	global $L_EXP_CHA_CODE;
	//-------------------------------------------------

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//$write_type = get_write_type_by_course($_POST['course_num']);//add kimura 2018/09/28 漢字学習コンテンツ_書写ドリル対応 //del kimura 2018/11/15 すらら英単語 _ワード管理
	list($write_type, $is_test, $test_type_num, $service_num) = get_english_word_info($_SESSION['sub_session']['course_num']); //add kimura 2018/11/16 すらら英単語 _ワード管理
	// セション情報取得
	//list($me_num,$onetime,$manager_level,$belong_num,$authority) = explode("<>",$_SESSION[myid]);

	// 権限取得
	if ($authority) { $L_AUTHORITY = explode("::",$authority); }

	// エラーメッセージが存在する場合、メッセージ表示
	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	// 登録権限が有るＩＤの時は、新規作成ボタン表示
	if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__add",$_SESSION['authority'])===FALSE)) {
		$html .= "<table>\n";
		$html .= "<tr>\n";
		$html .= "<td>\n";
		$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"add\">\n";
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
		$html .= "<input type=\"submit\" value=\"ワード新規登録\">\n";
		$html .= "</form>\n";
		$html .= "</td>\n";

		//	del 2015/01/07 yoshizawa 課題要望一覧No.400対応 下に新規で作成
		//$html .= "<td>\n";
		//$html .= "<form action=\"/admin/english_word_make_csv.php\" method=\"POST\">";
		//$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
		//$html .= "<input type=\"submit\" value=\"ワードダウンロード\">";
		//$html .= "</form>";
		//$html .= "</td>\n";
		//----------------------------------------------------------------

		$html .= "<td>\n";
		$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" enctype=\"multipart/form-data\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"english_word_upload\">\n";
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
		$html .= "<input type=\"file\" size=\"40\" name=\"english_word_file\">\n";
		$html .= "<input type=\"submit\" value=\"ワードアップロード\">\n";
		$html .= "</form>\n";
		$html .= "</td>\n";
		$html .= "</tr>\n";
		$html .= "</table>\n";

		//	add 2015/01/07 yoshizawa 課題要望一覧No.400対応
		$html .= "<form action=\"/admin/english_word_make_csv.php\" method=\"POST\">";
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
		//	プルダウンを作成
		$expList = "";
		if ( is_array($L_EXP_CHA_CODE) ) {
			$expList .= "海外版の場合は、出力形式について[Unicode]選択して、ダウンロードボタンをクリックしてください。<br />\n";
			$expList .= "<b>出力形式：</b>";
			$expList .= "<select name=\"exp_list\">";
			foreach( $L_EXP_CHA_CODE as $key => $val ){
				$expList .= "<option value=\"".$key."\">".$val."</option>";
			}
			$expList .= "</select>";

			$html .= $expList;
		}
		$html .= "<input type=\"submit\" value=\"ワードダウンロード\">";
		$html .= "<br /><br />\n";

		$html .= "</form>";
		//-------------------------------------------------

		if($service_num != 16 && $service_num != 17){ //add kimura 2018/11/27 すらら英単語 _admin //英単語以外ならこのリンクを表示  //中のインデント+1深くしました
			$html .= FTP_EXPLORER_MESSAGE; //add hirose 2018/04/23 FTPをブラウザからのエクスプローラーで開けない不具合対応

			$one_word_img_ftp = FTP_URL."word_img/".$_POST['course_num']."/";
			$one_word_voice_ftp = FTP_URL."word_voice/".$_POST['course_num']."/";

			$html .= "<br>\n";
			$html .= "<a href=\"".$one_word_img_ftp."\" target=\"_blank\">ワード管理画像フォルダー($one_word_img_ftp)</a><br>\n";
			$html .= "<a href=\"".$one_word_voice_ftp."\" target=\"_blank\">ワード管理音声フォルダー($one_word_voice_ftp)</a><br>\n";
		} //add kimura 2018/11/27 すらら英単語 _admin
	}
	// 表示ページ制御
	$s_page_view_html .= "&nbsp;&nbsp;&nbsp;表示数<select name=\"s_page_view\">\n";
	foreach ($L_PAGE_VIEW as $key => $val){
		if ($_SESSION['sub_session']['s_page_view'] == $key) { $sel = " selected"; } else { $sel = ""; }
		$s_page_view_html .= "<option value=\"".$key."\"".$sel.">".$val."</option>\n";
	}
	$s_page_view_html .= "</select><input type=\"submit\" value=\"Set\">\n";

	// add oda 2013/10/07
	$where = "";
	if ($_SESSION['sub_session']['view_english_word']) {
		$where .= "   AND english LIKE '%".$_SESSION['sub_session']['view_english_word']."%'";
	}
	//add start kimura 2018/11/15 すらら英単語 _ワード管理
	//---テスト---
	//course_numとtest_type_numは重複するので、テストはサフィックス「T」をつけています。
	if($is_test){
		$where.= " AND test_type_num = '".$test_type_num."'"; //テストのときはtest_type_numフィールドで探す
	//---授業---
	}else{
		$where.= " AND course_num = '".$_SESSION['sub_session']['course_num']."'"; //授業のときはcourse_numフィールドで探す
	}
	//add end   kimura 2018/11/15 すらら英単語 _ワード管理

	// ワード取得SQL作成
	$sql  = "SELECT * FROM " . T_ENGLISH_WORD .
			" WHERE mk_flg ='0' ".
			// "   AND course_num = '".$_SESSION['sub_session']['course_num']."'". //del kimura 2018/11/15 すらら英単語 _ワード管理
			$where.																			// add oda 2013/10/07
			"   ORDER BY english_word_num ";

	// ワード件数取得
	$english_word_count = 0;
	if ($result = $cdb->query($sql)) {
		$english_word_count = $cdb->num_rows($result);
	}

	// ページビュー判断
	if ($L_PAGE_VIEW[$_SESSION['sub_session']['s_page_view']]) {
		$page_view = $L_PAGE_VIEW[$_SESSION['sub_session']['s_page_view']];
	} else {
		$page_view = $L_PAGE_VIEW[0];
	}

	// 最大ページ数算出
	$max_page = ceil($english_word_count/$page_view);

	// 現在表示ページをセションから取得する
	if ($_SESSION['sub_session']['s_page']) {
		$page = $_SESSION['sub_session']['s_page'];
	} else {
		$page = 1;
	}

	// 表示ページと前ページ・次ページの数値を算出
	$start = ($page - 1) * $page_view;
	$next = $page + 1;
	$back = $page - 1;

	// SQL文に一覧表示する条件を追加
	$sql .= " LIMIT ".$start.",".$page_view.";";

	// データ読み込み
	if ($result = $cdb->query($sql)) {

		// 取得データ件数が０の場合
		$english_word_check_count = 0;
		$english_word_check_count = $cdb->num_rows($result);
		// del oda 2013/10/07
//		if (!$english_word_check_count) {
//			$html .= "現在、登録ワードは存在しません。";
//			return $html;
//		}

		// イメージ・音声格納ディレクトリ作成
		if($service_num != 16 && $service_num != 17){ //add kimura 2018/11/27 すらら英単語 _admin 英単語以外ならディレクトリをつくる //中のインデント+1深くしました
			create_directory();
		} //add kimura 2018/11/27 すらら英単語 _admin

		// 一覧表示開始english
		$html .= "<br>\n";
		$html .= "修正する場合は、修正するワードの詳細ボタンを押してください。<br>\n";

		// 登録件数表示
		$html .= "<br>\n";
		$html .= "<div style=\"float:left;\">ワード総数(".$english_word_count."):PAGE[".$page."/".$max_page."]</div>\n";

		// 前ページボタン表示制御
		if ($back > 0) {
			$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" style=\"float:left;\">";
			$html .= "<input type=\"submit\" value=\"前のページ\">";
			$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"page\">\n";
			$html .= "<input type=\"hidden\" name=\"s_page\" value=\"".$back."\">\n";
			$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
			$html .= "<input type=\"hidden\" name=\"view_english_word\" value=\"".$_POST['view_english_word']."\">\n";		// add oda 2013/10/07
			$html .= "</form>";
		}

		// 次ページボタン表示制御
		if ($page < $max_page) {
			$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" style=\"float:left;\">";
			$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"page\">\n";
			$html .= "<input type=\"hidden\" name=\"s_page\" value=\"".$next."\">\n";
			$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
			$html .= "<input type=\"hidden\" name=\"view_english_word\" value=\"".$_POST['view_english_word']."\">\n";		// add oda 2013/10/07
			$html .= "<input type=\"submit\" value=\"次のページ\">";
			$html .= "</form>";
		}

		$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" style=\"float:left;\">";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
		$html .= "<input type=\"hidden\" name=\"view_english_word\" value=\"".$_POST['view_english_word']."\">\n";		// add oda 2013/10/07
		$html .= "<input type=\"hidden\" name=\"s_page\" value=\"1\">\n";
		$html .= $s_page_view_html;
		$html .= "</form>\n";

		// 英単語検索		// add oda 2013/10/07
		$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" style=\"float:left;\">\n";
		//update start kimura 2018/09/28 漢字学習コンテンツ_書写ドリル対応
		//$html .= "&nbsp;&nbsp;&nbsp;検索英語：\n";
		$html .= "&nbsp;&nbsp;&nbsp;検索".$L_WORD_FIELD_ALIAS[$write_type]['english']."：\n";
		//update end   kimura 2018/09/28 漢字学習コンテンツ_書写ドリル対応
		$html .= "<input name=\"view_english_word\" type=\"text\" size=\"20\" value=\"".$_SESSION['sub_session']['view_english_word']."\"/>\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"serch\">\n";
		$html .= "<input type=\"hidden\" name=\"s_page\" value=\"1\">\n";
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
		$html .= "<input type=\"submit\" value=\"検索\">\n";
		$html .= "</form><br><br>\n";

		$html .= "<table class=\"english_word_form\">\n";
		$html .= "<tr class=\"english_word_form_menu\">\n";

		// リストタイトル表示
		$html .= "<th>登録番号</th>\n";
		// $html .= "<th>区分</th>\n"; //del kimura 2018/11/16 すらら英単語 _ワード管理
		//add start kimura 2018/11/16 すらら英単語 _ワード管理
		if($service_num != 16 && $service_num != 17){
			$html .= "<th>区分</th>\n";
		}
		//add end   kimura 2018/11/16 すらら英単語 _ワード管理
		//update start kimura 2018/09/28 漢字学習コンテンツ_書写ドリル対応
		//$html .= "<th>英語</th>\n";
		$html .= "<th>".$L_WORD_FIELD_ALIAS[$write_type]['english']."</th>\n";
		//update end   kimura 2018/09/28 漢字学習コンテンツ_書写ドリル対応
		//$html .= "<th>品詞</th>\n"; //del kimura 2018/09/28 漢字学習コンテンツ_書写ドリル対応
		//add start kimura 2018/09/28 漢字学習コンテンツ_書写ドリル対応
		// if($write_type == 1){ //del kimura 2018/11/16 すらら英単語 _ワード管理
		if($write_type == 1 && ($service_num != 16 && $service_num != 17)){ //add kimura 2018/11/16 すらら英単語 _ワード管理
			$html .= "<th>品詞</th>\n";
		}
		//add end   kimura 2018/09/28 漢字学習コンテンツ_書写ドリル対応
		$html .= "<th>表示・非表示</th>\n";
		// $html .= "<th>確認</th>\n"; //del kimura 2018/11/15 すらら英単語 _ワード管理
		//add start kimura 2018/11/15 すらら英単語 _ワード管理
		//確認機能はTOEIC限定に仕様変更します。 //サービス番号 8 のみ
		if($service_num == 8){
			$html .= "<th>暗記帳の表示を確認</th>\n";
		}
		//add end   kimura 2018/11/15 すらら英単語 _ワード管理

		// 詳細表示権限チェク
		if (!ereg("practice__view",$authority)
			&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__view",$_SESSION['authority'])===FALSE))
		) {
			$html .= "<th>詳細</th>\n";
		}

		// 削除権限チェク
		if (!ereg("practice__del",$authority)
			&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__del",$_SESSION['authority'])===FALSE))
		) {
			$html .= "<th>削除</th>\n";
		}
		$html .= "</tr>\n";

		// 明細表示
		$i = 1;
		while ($list = $cdb->fetch_assoc($result)) {

			$html .= "<tr class=\"english_word_form_cell\">\n";
			$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
			$html .= "<input type=\"hidden\" name=\"english_word_num\" value=\"".$list['english_word_num']."\">\n";

			// 明細表示
			$html .= "<td>".$list['english_word_num']."</td>\n";
			// $html .= "<td>".$L_WORD_TYPE[$list['word_type']]."</td>\n"; //del kimura 2018/11/16 すらら英単語 _ワード管理
			//add start kimura 2018/11/16 すらら英単語 _ワード管理
			if($service_num != 16 && $service_num != 17){
				$html .= "<td>".$L_WORD_TYPE[$list['word_type']]."</td>\n";
			}
			//add end   kimura 2018/11/16 すらら英単語 _ワード管理
			$html .= "<td>".$list['english']."</td>\n";
			//$html .= "<td>".$L_PART_OF_SPEECH_TYPE[$list['part_of_speech_type']]."</td>\n"; //del kimura 2018/09/28 漢字学習コンテンツ_書写ドリル対応
			//add start kimura 2018/09/28 漢字学習コンテンツ_書写ドリル対応
			// if($write_type == 1){ //del kimura 2018/11/16 すらら英単語 _ワード管理
			if($write_type == 1 && ($service_num != 16 && $service_num != 17)){ //add kimura 2018/11/16 すらら英単語 _ワード管理
				$html .= "<td>".$L_PART_OF_SPEECH_TYPE[$list['part_of_speech_type']]."</td>\n";
			}
			//add end   kimura 2018/09/28 漢字学習コンテンツ_書写ドリル対応
			$html .= "<td>".$L_DISPLAY[$list['display']]."</td>\n";
			// $html .= "<td><input type=\"button\" value=\"確認\" onclick=\"check_word_win_open('".$list['english_word_num']."')\"></td>\n"; //del kimura 2018/11/15 すらら英単語 _ワード管理
			//add start kimura 2018/11/15 すらら英単語 _ワード管理
			//確認機能はTOEIC限定に仕様変更します。 //サービス番号8のみ
			if($service_num == 8){
				$html .= "<td style=\"text-align:center\"><input type=\"button\" value=\"確認\" onclick=\"check_word_win_open('".$list['english_word_num']."')\"></td>\n";
			}
			//add end   kimura 2018/11/15 すらら英単語 _ワード管理

			// 表示権限チェック（権限が有る場合は、ボタン表示）
			if (!ereg("practice__view",$authority)
				&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__view",$_SESSION['authority'])===FALSE))
			) {
				$html .= "<td><input type=\"submit\" name=\"mode\" value=\"詳細\"></td>\n";
			}

			// 削除権限チェック（権限が有る場合は、ボタン表示）
			if (!ereg("practice__del",$authority)
				&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__del",$_SESSION['authority'])===FALSE))
			) {
				$html .= "<td><input type=\"submit\" name=\"mode\" value=\"削除\"></td>\n";
			}
			$html .= "</form>\n";
			$html .= "</tr>\n";
			++$i;
		}
		$html .= "</table>\n";
	}

	// add oda 2013/10/07
	if (!$english_word_check_count) {
		$html .= "対象ワードは存在しません。";
	}

	return $html;
}


/**
 * 新規登録フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function addform($ERROR) {

	// グローバル変数
	global $L_WORD_TYPE,$L_PART_OF_SPEECH_TYPE,$L_DISPLAY,$L_WRITE_TYPE,$L_WORD_FIELD_ALIAS; //update kimura 2018/09/28 漢字学習コンテンツ_書写ドリル対応 $L_WRITE_TYPE,$L_WORD_FIELD_ALIASもよみこみ

	$html .= "新規登録フォーム<br>\n";

	// エラーメッセージ表示
	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	// 入力画面表示
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";

	// $write_type = get_write_type_by_course($_POST['course_num']); //add kimura 2018/10/03 漢字学習コンテンツ_書写ドリル対応 //del kimura 2018/11/15 すらら英単語 _ワード管理
	list($write_type, $is_test, $test_type_num, $service_num) = get_english_word_info($_POST['course_num']); //add kimura 2018/11/16 すらら英単語 _ワード管理
	$L_WORD_TYPE = filter_word_type($write_type, $L_WORD_TYPE); //ライトタイプによって扱う区分を絞る //add kimura 2018/10/19 漢字学習コンテンツ_書写ドリル対応

	// htmlクラス生成
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	// $make_html->set_file(ENGLISH_WORD_FORM); //add kimura 2018/11/16 すらら英単語 _ワード管理
	//add start kimura 2018/11/16 すらら英単語 _ワード管理
	if($service_num == 16 || $service_num == 17){
		$make_html->set_file(ENGLISH_WORD_FORM_VOCABULARY);
	}else{
		$make_html->set_file(ENGLISH_WORD_FORM);
	}
	//add end   kimura 2018/11/16 すらら英単語 _ワード管理	
	//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	$INPUTS[ENGLISHWORDNUM] = array('result'=>'plane','value'=>"---");
	$INPUTS[WORDTYPE] = array('type'=>'select','name'=>'word_type','array'=>$L_WORD_TYPE,'check'=>$_POST['word_type']);
	//add start kimura 2018/11/16 すらら英単語 _ワード管理
	$test_problem_num_html = "";

	if($is_test){
		//テストの単語は必ず区分を1にする
		$INPUTS['WORDTYPE'] = array('result'=>'plane', 'value'=>'<span>単語</span><input type="hidden" name="word_type" value="1">');
		//テスト問題番号の入力欄を設ける
		$test_problem_num_html.= "<tr>";
		$test_problem_num_html.= "<td class=\"english_word_form_menu\">すらら英単語テスト問題番号</td>";
		$test_problem_num_html.= "<td class=\"english_word_form_cell\">";
		$test_problem_num_html.= "<input type=\"text\" name=\"t_problem_num\" size=\"40\" value=\"".$_POST['t_problem_num']."\">";
		$test_problem_num_html.= "<br><span style=\"color:red;\">※すらら英単語テスト問題管理番号を入力して下さい。（表示番号ではありません）";
		$test_problem_num_html.= "<br>※複数設定する場合は、「 :: 」区切りで入力して下さい。</span>";
		$test_problem_num_html.= "</td>";
		$test_problem_num_html.= "</tr>";
	}
	//add end   kimura 2018/11/16 すらら英単語 _ワード管理
	//add start kimura 2018/09/28 漢字学習コンテンツ_書写ドリル対応 //国語だったら区分を固定する
	if($write_type == 2){
		$tr_attr = "display:none";
	}
	//add end   kimura 2018/09/28 漢字学習コンテンツ_書写ドリル対応
	$INPUTS[ENGLISH] = array('type'=>'text','name'=>'english','size'=>'50','value'=>$_POST['english']);
	$INPUTS[JAPANESETRANSLATION] = array('type'=>'textarea','name'=>'japanese_translation','cols'=>'50','rows'=>'10','value'=>$_POST['japanese_translation']);
	$INPUTS[PARTOFSPEECHTYPE] = array('type'=>'select','name'=>'part_of_speech_type','array'=>$L_PART_OF_SPEECH_TYPE,'check'=>$_POST['part_of_speech_type']);
	$INPUTS[VOICEDATA] = array('type'=>'text','name'=>'voice_data','value'=>$_POST['voice_data']);
	$INPUTS[EXAMPLESENTENCE] = array('type'=>'text','name'=>'example_sentence','size'=>'50','value'=>$_POST['example_sentence']);
	$INPUTS[EXAMPLESENTENCEJP] = array('type'=>'textarea','name'=>'example_sentence_jp','cols'=>'50','rows'=>'10','value'=>$_POST['example_sentence_jp']);
	$INPUTS[EXAMPLESENTENCEVOICE] = array('type'=>'text','name'=>'example_sentence_voice','value'=>$_POST['example_sentence_voice']);
	$INPUTS[PROBLEMNUM] = array('type'=>'text','name'=>'problem_num','size'=>'40','value'=>$_POST['problem_num']);
	$INPUTS['TESTPROBLEMNUMFORM'] = array('result'=>'plane', 'value'=>$test_problem_num_html); //add kimura 2018/11/16 すらら英単語 _ワード管理
	//add start kimura 2018/09/28 漢字学習コンテンツ_書写ドリル対応
	$INPUTS['TRATTR'] = array('result'=>'plane','value'=>$tr_attr); //国語の時隠すフォームにはこの属性をつける
	$INPUTS['ENGLISHFORMTITLE'] = array('result'=>'plane','value'=>$L_WORD_FIELD_ALIAS[$write_type]['english']); //englishフィールドの入力フォームのタイトル 英語/漢字/...
	$INPUTS['JAPANESETRANSLATIONFORMTITLE'] = array('result'=>'plane','value'=>$L_WORD_FIELD_ALIAS[$write_type]['japanese_translation']); //japanese_translationフィールドの入力フォームのタイトル 和訳/例文/...
	$INPUTS['EXAMPLESENTENCEJPTITLE'] = array('result'=>'plane','value'=>$L_WORD_FIELD_ALIAS[$write_type]['example_sentence_jp']); //example_sentence_jpフィールドの入力フォームのタイトル 例文(和訳)/"読み/書き"/...
	$INPUTS['WRITETYPE'] = array('result'=>'plane','value'=>$L_WRITE_TYPE[$write_type]); //write_type
	//add end   kimura 2018/09/28 漢字学習コンテンツ_書写ドリル対応
	$problem_att = "<br><span style=\"color:red;\">※問題管理番号を入力して下さい。（表示番号ではありません）";
	$problem_att .= "<br>※複数設定する場合は、「 :: 」区切りで入力して下さい。</span>";
	$INPUTS[PROBLEMATT] = array('result'=>'plane','value'=>$problem_att);

	// 表示区分ラジオボタン生成
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("display");
	$newform->set_form_id("display");
	// $newform->set_form_check($_POST['display']); //del kimura 2018/11/19 すらら英単語 _ワード管理
	//add start kimura 2018/11/19 すらら英単語 _ワード管理 //デフォルトは 1:表示をチェック状態にします。
	$display_check = 1;
	if($_POST['display'] == 1){
		$display_check = $_POST['display'];
	}
	$newform->set_form_check($display_check);
	//add end   kimura 2018/11/19 すらら英単語 _ワード管理
	$newform->set_form_value('1');
	$radio1 = $newform->make();

	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("display");
	$newform->set_form_id("undisplay");
	$newform->set_form_check($_POST['display']);
	$newform->set_form_value('2');
	$radio2 = $newform->make();

	$display_value = $radio1 . "<label for=\"display\">".$L_DISPLAY[1]."</label> / " . $radio2 . "<label for=\"undisplay\">".$L_DISPLAY[2]."</label>";
	$INPUTS[DISPLAY] = array('result'=>'plane','value'=>$display_value);
	$INPUTS['DISPLAYDESC'] = array('result'=>'plane','value'=>"<br><span style=\"color:red;\">※表示：単語リストに表示する／統計の対象とする<br>非表示：単語リストに表示しない／統計の対象としない</span>"); //add kimura 2018/11/15 すらら英単語 _ワード管理

	$make_html->set_rep_cmd($INPUTS);
	$html .= $make_html->replace();

	// 制御ボタン定義
	$html .= "<input type=\"submit\" value=\"追加確認\">\n";
	$html .= "<input type=\"reset\" value=\"クリア\">\n";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"english_word_list\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";
	return $html;
}


/**
 * 詳細情報表示
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function viewform($ERROR) {

	// グローバル変数
	global $L_PAGE_VIEW,$L_WORD_TYPE,$L_PART_OF_SPEECH_TYPE,$L_DISPLAY,$L_WRITE_TYPE,$L_WORD_FIELD_ALIAS; //update kimura 2018/09/28 漢字学習コンテンツ_書写ドリル対応 $L_WRITE_TYPE, $L_WORD_FIELD_ALIASも読み込み

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	list($write_type, $is_test, $test_type_num, $service_num) = get_english_word_info($_POST['course_num']); //add kimura 2018/11/16 すらら英単語 _ワード管理

//	if (ACTION=="check") {	//del hasegawa 2015/11/17 確認画面から遷移時の値保持のため
	if (ACTION) {			//add hasegawa 2015/11/17 確認画面から遷移時の値保持のため
		foreach ($_POST as $key => $val) { $$key = $val; }
	} else {
		$sql = "SELECT * FROM " . T_ENGLISH_WORD .
			" WHERE english_word_num='".$_POST['english_word_num']."' AND mk_flg='0' LIMIT 1;";
		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);
		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}
		foreach ($list as $key => $val) {
			$$key = replace_decode($val);
			$$key = ereg_replace("\"","&quot;",$$key);
		}
	}
	// 画面表示
	$html .= "詳細画面<br>\n";

	// エラーメッセージ表示
	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	// 入力画面表示
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"詳細\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";
	// $html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$course_num."\">\n"; //del kimura 2018/11/15 すらら英単語 _ワード管理
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".($test_type_num > 0 ? $test_type_num."T" : $course_num)."\">\n"; //add kimura 2018/11/15 すらら英単語 _ワード管理
	$html .= "<input type=\"hidden\" name=\"english_word_num\" value=\"$english_word_num\">\n";

	// htmlクラス生成
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	// $make_html->set_file(ENGLISH_WORD_FORM); //del kimura 2018/11/16 すらら英単語 _ワード管理
	//add start kimura 2018/11/16 すらら英単語 _ワード管理
	if($service_num == 16 || $service_num == 17){
		$make_html->set_file(ENGLISH_WORD_FORM_VOCABULARY);
	}else{
		$make_html->set_file(ENGLISH_WORD_FORM);
	}
	//add end   kimura 2018/11/16 すらら英単語 _ワード管理	

	// ワード問題番号取得
	// $problem_num = get_word_problem_key($english_word_num); //del kimura 2018/11/16 すらら英単語 _ワード管理
	list($problem_num, $t_problem_num) = get_word_problem_key($english_word_num); //add kimura 2018/11/16 すらら英単語 _ワード管理
	//add start kimura 2018/10/19 漢字学習コンテンツ_書写ドリル対応
	// $write_type = get_write_type_by_course($_POST['course_num']); //del kimura 2018/11/15 すらら英単語 _ワード管理
	$L_WORD_TYPE = filter_word_type($write_type, $L_WORD_TYPE); //ライトタイプによって扱う区分を絞る //add kimura 2018/10/19 漢字学習コンテンツ_書写ドリル対応
	//add end   kimura 2018/10/19 漢字学習コンテンツ_書写ドリル対応

	//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	if (!$english_word_num) { $english_word_num = "---"; }
	$INPUTS[ENGLISHWORDNUM] = array('result'=>'plane','value'=>$english_word_num);
	$INPUTS[WORDTYPE] = array('type'=>'select','name'=>'word_type','array'=>$L_WORD_TYPE,'check'=>$word_type);
	//add start kimura 2018/11/16 すらら英単語 _ワード管理
	$test_problem_num_html = "";
	if($is_test){
		//テストの単語は必ず区分を1にする
		$INPUTS['WORDTYPE'] = array('result'=>'plane', 'value'=>'<span>単語</span><input type="hidden" name="word_type" value="1">');
		//テスト問題番号の入力欄を設ける
		$test_problem_num_html.= "<tr>";
		$test_problem_num_html.= "<td class=\"english_word_form_menu\">すらら英単語テスト問題番号</td>";
		$test_problem_num_html.= "<td class=\"english_word_form_cell\">";
		$test_problem_num_html.= "<input type=\"text\" name=\"t_problem_num\" size=\"40\" value=\"".$t_problem_num."\">";
		$test_problem_num_html.= "<br><span style=\"color:red;\">※すらら英単語テスト問題管理番号を入力して下さい。（表示番号ではありません）";
		$test_problem_num_html.= "<br>※複数設定する場合は、「 :: 」区切りで入力して下さい。</span>";
		$test_problem_num_html.= "</td>";
		$test_problem_num_html.= "</tr>";
	}
	//add end   kimura 2018/11/16 すらら英単語 _ワード管理

	//add start kimura 2018/10/03 漢字学習コンテンツ_書写ドリル対応
	if($write_type == 2){
		//$tr_attr = "style=\"display:none;\"";//del yamaguchi 2018/10/17
		$tr_attr = "display:none;";//add yamaguchi 2018/10/17
		$INPUTS['WORDTYPEASTEXT'] = array('result'=>'plane', 'value'=>"<span>".$L_WORD_TYPE[3]."</span>");
	}
	//add end   kimura 2018/10/03 漢字学習コンテンツ_書写ドリル対応
	$INPUTS[ENGLISH] = array('type'=>'text','name'=>'english','size'=>'50','value'=>$english);
	$INPUTS[JAPANESETRANSLATION] = array('type'=>'textarea','name'=>'japanese_translation','cols'=>'50','rows'=>'10','value'=>$japanese_translation);
	$INPUTS[PARTOFSPEECHTYPE] = array('type'=>'select','name'=>'part_of_speech_type','array'=>$L_PART_OF_SPEECH_TYPE,'check'=>$part_of_speech_type);
	$INPUTS[VOICEDATA] = array('type'=>'text','name'=>'voice_data','value'=>$voice_data);
	$INPUTS[EXAMPLESENTENCE] = array('type'=>'text','name'=>'example_sentence','size'=>'50','value'=>$example_sentence);
	$INPUTS[EXAMPLESENTENCEJP] = array('type'=>'textarea','name'=>'example_sentence_jp','cols'=>'50','rows'=>'10','value'=>$example_sentence_jp);
	$INPUTS[EXAMPLESENTENCEVOICE] = array('type'=>'text','name'=>'example_sentence_voice','value'=>$example_sentence_voice);
	$INPUTS[PROBLEMNUM] = array('type'=>'text','name'=>'problem_num','size'=>'40','value'=>$problem_num);
	$INPUTS['TESTPROBLEMNUMFORM'] = array('result'=>'plane', 'value'=>$test_problem_num_html); //add kimura 2018/11/16 すらら英単語 _ワード管理
	//add start kimura 2018/09/28 漢字学習コンテンツ_書写ドリル対応
	$INPUTS['TRATTR'] = array('result'=>'plane','value'=>$tr_attr); //国語の時隠すフォームにはこの属性をつける
	$INPUTS['ENGLISHFORMTITLE'] = array('result'=>'plane','value'=>$L_WORD_FIELD_ALIAS[$write_type]['english']); //englishフィールドの入力フォームのタイトル 英語/漢字/...
	$INPUTS['JAPANESETRANSLATIONFORMTITLE'] = array('result'=>'plane','value'=>$L_WORD_FIELD_ALIAS[$write_type]['japanese_translation']); //japanese_translationフィールドの入力フォームのタイトル 和訳/例文/...
	$INPUTS['EXAMPLESENTENCEJPTITLE'] = array('result'=>'plane','value'=>$L_WORD_FIELD_ALIAS[$write_type]['example_sentence_jp']); //example_sentence_jpフィールドの入力フォームのタイトル 例文(和訳)/"読み/書き"/...
	$INPUTS['WRITETYPE'] = array('result'=>'plane','value'=>$L_WRITE_TYPE[$write_type]); //write_type
	//add end   kimura 2018/09/28 漢字学習コンテンツ_書写ドリル対応
	$problem_att = "<br><span style=\"color:red;\">※問題管理番号を入力して下さい。（表示番号ではありません）";
	$problem_att .= "<br>※複数設定する場合は、「 :: 」区切りで入力して下さい。</span>";
	$INPUTS[PROBLEMATT] = array('result'=>'plane','value'=>$problem_att);

	// 表示区分ラジオボタン生成
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("display");
	$newform->set_form_id("display");
	$newform->set_form_check($display);
	$newform->set_form_value('1');
	$radio1 = $newform->make();

	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("display");
	$newform->set_form_id("undisplay");
	$newform->set_form_check($display);
	$newform->set_form_value('2');
	$radio2 = $newform->make();

	$display_value = $radio1 . "<label for=\"display\">".$L_DISPLAY[1]."</label> / " . $radio2 . "<label for=\"undisplay\">".$L_DISPLAY[2]."</label>";
	$INPUTS[DISPLAY] = array('result'=>'plane','value'=>$display_value);
	$INPUTS['DISPLAYDESC'] = array('result'=>'plane','value'=>"<br><span style=\"color:red;\">※表示：単語リストに表示する／統計の対象とする<br>非表示：単語リストに表示しない／統計の対象としない</span>"); //add kimura 2018/11/15 すらら英単語 _ワード管理

	$make_html->set_rep_cmd($INPUTS);
	$html .= $make_html->replace();

	// 制御ボタン定義
	$html .= "<input type=\"submit\" value=\"変更確認\">";
	$html .= "<input type=\"reset\" value=\"クリア\">";
	$html .= "</form><br>\n";
	// $html .= "<input type=\"button\" value=\"確認\" onclick=\"check_word_win_open('".$list['english_word_num']."')\"><br><br>\n"; //del kimura 2018/11/16 すらら英単語 _ワード管理
	//add start kimura 2018/11/16 すらら英単語 _ワード管理
	//確認機能はTOEIC限定に仕様変更します。 //サービス番号8のみ
	if($service_num == 8){
		$html .= "<input type=\"button\" value=\"確認\" onclick=\"check_word_win_open('".$english_word_num."')\"><br><br>\n"; //$list['english_word_num']はACTIONがあるときは存在しない
	}
	//add end   kimura 2018/11/16 すらら英単語 _ワード管理

	// ワード一覧に戻る
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"english_word_list\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
	$html .= "<input type=\"submit\" value=\"ワード一覧に戻る\">\n";
	$html .= "</form>\n";

	return $html;
}


/**
 * 入力項目チェック
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function check() {

	global $L_WORD_FIELD_ALIAS; //add kimura 2018/10/19 漢字学習コンテンツ_書写ドリル対応
	// $write_type = get_write_type_by_course($_POST['course_num']); //add kimura 2018/10/19 漢字学習コンテンツ_書写ドリル対応 //del kimura 2018/11/16 すらら英単語 _ワード管理
	list($write_type, $is_test, $test_type_num, $service_num) = get_english_word_info($_POST['course_num']); //add kimura 2018/11/16 すらら英単語 _ワード管理

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// 未入力チェック
	//del start kimura 2018/11/16 すらら英単語 _ワード管理
	// if (!$_POST['word_type']) {
	// 	$ERROR[] = "区分が未入力です。";
	// }
	//del end   kimura 2018/11/16 すらら英単語 _ワード管理
	if (!$_POST['english']) {
		//update start kimura 2018/10/19 漢字学習コンテンツ_書写ドリル対応
		//$ERROR[] = "英語が未入力です。";
		$ERROR[] = $L_WORD_FIELD_ALIAS[$write_type]['english']."が未入力です。";
		//update end   kimura 2018/10/19 漢字学習コンテンツ_書写ドリル対応
	}

	//add start kimura 2018/11/16 すらら英単語 _ワード管理
	if($service_num != 16 && $service_num != 17){
		if (!$_POST['word_type']) {
			$ERROR[] = "区分が未入力です。";
		}
		if (!$_POST['japanese_translation']) {
			$ERROR[] = $L_WORD_FIELD_ALIAS[$write_type]['japanese_translation']."が未入力です。";
		}
		if (!$_POST['part_of_speech_type'] && $_POST['word_type'] == 1) {
			$ERROR[] = "品詞が未入力です。";
		}
	}

	if (!$_POST['display']) {
		$ERROR[] = "表示・非表示が未入力です。";
		// 重複チェック
	} else {
		if($is_test){
			$where = " AND mk_flg!='1' AND test_type_num = '".$test_type_num."' AND english = '".$_POST['english']."'";
		}else{
			$where = " AND mk_flg!='1' AND course_num = '".$_SESSION['sub_session']['course_num']."' AND english = '".$_POST['english']."'";
		}

		if (MODE == "add") {
			$sql = "SELECT * FROM " .T_ENGLISH_WORD;
			$sql.= " WHERE 1";
			$sql.= $where;
		} else {
			$sql = "SELECT * FROM " .T_ENGLISH_WORD;
			$sql.= " WHERE 1";
			$sql.= $where;
			$sql.= " AND english_word_num != '".$_POST['english_word_num']."'";
		}
		// SQL実行
		if ($result = $cdb->query($sql)) {
			$count = $cdb->num_rows($result);
		}
		// if ($count > 0) { $ERROR[] = "入力された英語は既に登録されております。"; } //del kimura 2018/11/19 すらら英単語 _ワード管理
		if ($count > 0) { $ERROR[] = "入力された".$L_WORD_FIELD_ALIAS[$write_type]['english']."は既に登録されております。"; } //add kimura 2018/11/19 すらら英単語 _ワード管理
	}
	//add end   kimura 2018/11/16 すらら英単語 _ワード管理

	//del start kimura 2018/11/16 すらら英単語 _ワード管理
	//if (!$_POST['japanese_translation']) {
	//	//update start kimura 2018/10/19 漢字学習コンテンツ_書写ドリル対応
	//	//$ERROR[] = "和訳が未入力です。";
	//	$ERROR[] = $L_WORD_FIELD_ALIAS[$write_type]['japanese_translation']."が未入力です。";
	//	//update end   kimura 2018/10/19 漢字学習コンテンツ_書写ドリル対応
	//}
	//if (!$_POST['part_of_speech_type'] && $_POST['word_type'] == 1) {
	//	$ERROR[] = "品詞が未入力です。";
	//}
	//if (!$_POST['display']) {
	//	$ERROR[] = "表示・非表示が未入力です。";

	//// 重複チェック
	//} else {
	//	if (MODE == "add") {
	//		$sql  = "SELECT * FROM " .T_ENGLISH_WORD.
	//				" WHERE mk_flg!='1' AND course_num = '".$_SESSION['sub_session']['course_num']."' AND english = '".$_POST['english']."'";
	//	} else {
	//		$sql  = "SELECT * FROM " .T_ENGLISH_WORD.
	//				" WHERE mk_flg != '1' AND course_num = '".$_SESSION['sub_session']['course_num'].
	//				"' AND english_word_num != '".$_POST['english_word_num']."' AND english = '".$_POST['english']."'";
	//	}
	//	// SQL実行
	//	if ($result = $cdb->query($sql)) {
	//		$count = $cdb->num_rows($result);
	//	}
	//	if ($count > 0) { $ERROR[] = "入力された英語は既に登録されております。"; }
	//}
	//del end   kimura 2018/11/16 すらら英単語 _ワード管理

	// ワード問題管理テーブルチェック
	if (trim($_POST['problem_num']) != "") {
		$word_problem_count = 0;
		$word_problem_list = explode("::",$_POST['problem_num']);

		// 問題番号に数値以外の値が入っていないかチェック
		$word_problem_error_flag = false;
		for ($j=0; $j<count($word_problem_list); $j++) {
			if (trim($word_problem_list[$j]) != "" && is_numeric($word_problem_list[$j]) == false) {
				// $ERROR[] = "すらら問題番号に指定した番号は、不正な情報が混ざっています。"; //del kimura 2018/11/16 すらら英単語 _ワード管理
				$ERROR[] = "すらら".(($service_num == 16 || $service_num == 17) ? "英単語" : "")."問題番号に指定した番号は、不正な情報が混ざっています。"; //del kimura 2018/11/16 すらら英単語 _ワード管理
				$word_problem_error_flag = true;
			}
		}

		// 問題番号が存在するかチェック
		if (!$word_problem_error_flag) {
			$sql  = "SELECT * FROM ".T_PROBLEM.
					" WHERE state='0' AND problem_num IN ('" . implode("','", $word_problem_list) ."')";
			// del start 2019/05/22 yoshizawa すらら英単語テストワード管理
			// 問題登録の関連をチェックしてしまうと問題を設定してからでないとワード登録ができなくなってしまうので問題マスタの存在のみをチェックします。
			// ワード設定→問題設定の順で登録できる様にいたします。
			// //add start kimura 2018/11/16 すらら英単語 _ワード管理
			// if($service_num == 17){
			// 	//ms_test_category2_problemテーブルにはテスト種別1,種別2が違う問題のレコードも存在するので
			// 	//テスト種類問題番号単位にして取得する
			// 	$sql = "SELECT DISTINCT test_type_num, problem_table_type, problem_num";
			// 	$sql.= " FROM ".T_MS_TEST_CATEGORY2_PROBLEM;
			// 	$sql.= " WHERE mk_flg = '0' AND problem_num IN ('" . implode("','", $word_problem_list) ."')";
			// 	$sql.= " AND problem_table_type = '1'";
			// 	$sql.= " AND test_type_num = '".$test_type_num."'";
			// }
			// //add end   kimura 2018/11/16 すらら英単語 _ワード管理
			// del end 2019/05/22 yoshizawa すらら英単語テストワード管理
			if ($result = $cdb->query($sql)) {
				$word_problem_count = $cdb->num_rows($result);
			}
			// if (count($word_problem_count) > 0) { //del kimura 2018/11/20 すらら英単語 _ワード管理 //int(0)をカウントするとint(1)になるので必ず通ってしまっていた //入力した番号の問題マスタが存在しない場合エラーが発生しないのでこの判断消します。
				if ($word_problem_count != count($word_problem_list)) {
					// $ERROR[] = "すらら問題番号に指定した番号は、既に削除されているか、不正な情報が混ざっています。"; //del kimura 2018/11/16 すらら英単語 _ワード管理
					$ERROR[] = "すらら".(($service_num == 16 || $service_num == 17) ? "英単語" : "")."問題番号に指定した番号は、既に削除されているか、不正な情報が混ざっています。"; //del kimura 2018/11/16 すらら英単語 _ワード管理
				}
			// } //del kimura 2018/11/20 すらら英単語 _ワード管理
		}
	}


	//add start kimura 2018/11/16 すらら英単語 _ワード管理
	$word_test_problem_list = array();

	// ワード問題管理テーブルチェック(テスト専用)
	if (trim($_POST['t_problem_num']) != "") {
		$word_test_problem_count = 0;
		$word_test_problem_list = explode("::",$_POST['t_problem_num']);

		// 問題番号に数値以外の値が入っていないかチェック
		$word_test_problem_error_flag = false;
		for ($j=0; $j<count($word_test_problem_list); $j++) {
			if (trim($word_test_problem_list[$j]) != "" && is_numeric($word_test_problem_list[$j]) == false) {
				$ERROR[] = "すらら英単語テスト問題番号に指定した番号は、不正な情報が混ざっています。";
				$word_test_problem_error_flag = true;
			}
		}

		// 問題番号が存在するかチェック
		if (!$word_test_problem_error_flag) {
			// update start 2019/05/22 yoshizawa すらら英単語テストワード管理
			// 問題登録の関連をチェックしてしまうと問題を設定してからでないとワード登録ができなくなってしまうので、
			// チェックは問題マスタで行います。
			// //ms_test_category2_problemテーブルにはテスト種別1,種別2が違う問題のレコードも存在するので
			// //テスト種類問題番号単位にして取得する
			// $sql = "SELECT DISTINCT test_type_num, problem_table_type, problem_num";
			// $sql.= " FROM ".T_MS_TEST_CATEGORY2_PROBLEM;
			// $sql.= " WHERE mk_flg = '0' AND problem_num IN ('" . implode("','", $word_test_problem_list) ."')";
			// $sql.= " AND problem_table_type = '3'";
			// $sql.= " AND test_type_num = '".$test_type_num."'";
			// 
			$sql  = "SELECT * FROM ".T_MS_TEST_PROBLEM.
					" WHERE mk_flg='0' AND problem_num IN ('" . implode("','", $word_test_problem_list) ."');";
			// update start 2019/05/22 yoshizawa すらら英単語テストワード管理
			if ($result = $cdb->query($sql)) {
				$word_test_problem_count = $cdb->num_rows($result);
			}
			if ($word_test_problem_count != count($word_test_problem_list)) {
				$ERROR[] = "すらら英単語テスト問題番号に指定した番号は、既に削除されているか、不正な情報が混ざっています。";
			}
		}
	}
	//add end   kimura 2018/11/16 すらら英単語 _ワード管理

	return $ERROR;
}


/**
 * 入力確認画面表示
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function check_html() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];


	global $L_WORD_TYPE,$L_PART_OF_SPEECH_TYPE,$L_DISPLAY,$L_WRITE_TYPE,$L_WORD_FIELD_ALIAS; //update kimura 2018/10/03 漢字学習コンテンツ_書写ドリル対応 $L_WRITE_TYPE, $L_WORD_FIELD_ALIASも読み込み

	// アクション情報をhidden項目に設定
	if ($_POST) {
		foreach ($_POST as $key => $val) {
			if ($key == "action") {
				if (MODE == "add") {
					$val = "add";
				} elseif (MODE == "詳細") {
					$val = "change";
				}
			}
			// 改行変換
			if ($key == "japanese_translation" || $key == "example_sentence_jp") {
				$val = eregi_replace("\r","",$val);
				$val = eregi_replace("\n","<br>",$val);
			}
			$HIDDEN .= "<input type=\"hidden\" name=\"$key\" value=\"$val\">\n";
		}
	}

	// 他の人が削除したかチェック
	if (ACTION) {
		foreach ($_POST as $key => $val) { $$key = $val; }
	} else {
		$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"change\">\n";
		$sql = "SELECT * FROM ".T_ENGLISH_WORD.
			" WHERE english_word_num='".$_POST[english_word_num]."' AND mk_flg!='1' LIMIT 1;";

		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);

		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}
		foreach ($list as $key => $val) {
			$$key = replace_decode($val);
			$$key = ereg_replace("\"","&quot;",$$key);
		}

		// ワード問題番号取得
		// $problem_num = get_word_problem_key($english_word_num); //del kimura 2018/11/16 すらら英単語 _ワード管理
		list($problem_num, $t_problem_num) = get_word_problem_key($english_word_num); //add kimura 2018/11/16 すらら英単語 _ワード管理
	}

	// 改行変換
	$japanese_translation = eregi_replace("\r","",$japanese_translation);
	$japanese_translation = eregi_replace("\n","<br>",$japanese_translation);
	$example_sentence_jp = eregi_replace("\r","",$example_sentence_jp);
	$example_sentence_jp = eregi_replace("\n","<br>",$example_sentence_jp);

	// ボタン表示文言判定
	if (MODE != "削除") { $button = "登録"; } else { $button = "削除"; }

	// 入力確認画面表示
	$html = "<br>\n";
	$html .= "確認画面：以下の内容で".$button."してもよろしければ".$button."ボタンをクリックしてください。<br>\n";

	// $write_type = get_write_type_by_course($_POST['course_num']); //add kimura 2018/10/03 漢字学習コンテンツ_書写ドリル対応 //del kimura 2018/11/16 すらら英単語 _ワード管理
	list($write_type, $is_test, $test_type_num, $service_num) = get_english_word_info($_POST['course_num']); //add kimura 2018/11/16 すらら英単語 _ワード管理
	$L_WORD_TYPE = filter_word_type($write_type, $L_WORD_TYPE); //ライトタイプによって扱う区分を絞る //add kimura 2018/10/19 漢字学習コンテンツ_書写ドリル対応

	//add start kimura 2018/11/16 すらら英単語 _ワード管理
	$test_problem_num_html = "";

	if($is_test){
		//テスト問題番号の入力欄を設ける
		$test_problem_num_html.= "<tr>";
		$test_problem_num_html.= "<td class=\"english_word_form_menu\">すらら英単語テスト問題番号</td>";
		$test_problem_num_html.= "<td class=\"english_word_form_cell\">".$_POST['t_problem_num']."</td>";
		$test_problem_num_html.= "</tr>";
	}
	//add end   kimura 2018/11/16 すらら英単語 _ワード管理

	// htmlクラス生成
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	// $make_html->set_file(ENGLISH_WORD_FORM); //del kimura 2018/11/16 すらら英単語 _ワード管理
	//add start kimura 2018/11/16 すらら英単語 _ワード管理
	if($service_num == 16 || $service_num == 17){
		$make_html->set_file(ENGLISH_WORD_FORM_VOCABULARY);
	}else{
		$make_html->set_file(ENGLISH_WORD_FORM);
	}
	//add end   kimura 2018/11/16 すらら英単語 _ワード管理

	//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	if (!$english_word_num) { $english_word_num = "---"; }
	$INPUTS[ENGLISHWORDNUM] = array('result'=>'plane','value'=>$english_word_num);
	$INPUTS[WORDTYPE] = array('result'=>'plane','value'=>$L_WORD_TYPE[$word_type]);
	if($write_type == 2){
		$tr_attr = "display:none";
		// $INPUTS[WORDTYPE] = array('type'=>'hidden','name'=>'word_type','value'=>3);
		$INPUTS['WORDTYPEASTEXT'] = array('result'=>'plane', 'value'=>"<span>".$L_WORD_TYPE[3]."</span>");
	}
	$INPUTS[ENGLISH] = array('result'=>'plane','value'=>$english);
	$INPUTS[JAPANESETRANSLATION] = array('result'=>'plane','value'=>$japanese_translation);
	$INPUTS[PARTOFSPEECHTYPE] = array('result'=>'plane','value'=>$L_PART_OF_SPEECH_TYPE[$part_of_speech_type]);
	$INPUTS[VOICEDATA] = array('result'=>'plane','value'=>$voice_data);
	$INPUTS[EXAMPLESENTENCE] = array('result'=>'plane','value'=>$example_sentence);
	$INPUTS[EXAMPLESENTENCEJP] = array('result'=>'plane','value'=>$example_sentence_jp);
	$INPUTS[EXAMPLESENTENCEVOICE] = array('result'=>'plane','value'=>$example_sentence_voice);
	$INPUTS[PROBLEMNUM] = array('result'=>'plane','value'=>$problem_num);
	$INPUTS['TESTPROBLEMNUMFORM'] = array('result'=>'plane', 'value'=>$test_problem_num_html); //add kimura 2018/11/16 すらら英単語 _ワード管理
	//add start kimura 2018/10/03 漢字学習コンテンツ_書写ドリル対応
	$INPUTS['TRATTR'] = array('result'=>'plane','value'=>$tr_attr); //国語の時隠すフォームにはこの属性をつける
	$INPUTS['ENGLISHFORMTITLE'] = array('result'=>'plane','value'=>$L_WORD_FIELD_ALIAS[$write_type]['english']); //englishフィールドの入力フォームのタイトル 英語/漢字/...
	$INPUTS['JAPANESETRANSLATIONFORMTITLE'] = array('result'=>'plane','value'=>$L_WORD_FIELD_ALIAS[$write_type]['japanese_translation']); //japanese_translationフィールドの入力フォームのタイトル 和訳/例文/...
	$INPUTS['EXAMPLESENTENCEJPTITLE'] = array('result'=>'plane','value'=>$L_WORD_FIELD_ALIAS[$write_type]['example_sentence_jp']); //example_sentence_jpフィールドの入力フォームのタイトル 例文(和訳)/"読み/書き"/...
	$INPUTS['WRITETYPE'] = array('result'=>'plane','value'=>$L_WRITE_TYPE[$write_type]); //write_type
	//add end   kimura 2018/10/03 漢字学習コンテンツ_書写ドリル対応
	$INPUTS[DISPLAY] = array('result'=>'plane','value'=>$L_DISPLAY[$display]);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();

	// 制御ボタン定義
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" style=\"float:left\">\n";
	$html .= $HIDDEN;

	// 削除の場合は、１画面目に戻る
	if (MODE == "削除") {
		$html .= "<input type=\"hidden\" name=\"s_page\" value=\"1\">\n";
	}

	$html .= "<input type=\"submit\" value=\"$button\">\n";
	$html .= "</form>";
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";

	if (ACTION) {
		$HIDDEN2 = explode("\n",$HIDDEN);
		foreach ($HIDDEN2 as $key => $val) {
			if (ereg("name=\"action\"",$val)) {
				$HIDDEN2[$key] = "<input type=\"hidden\" name=\"action\" value=\"back\">";
				break;
			}
		}
		$HIDDEN2 = implode("\n",$HIDDEN2);
		$html .= $HIDDEN2;
	} else {
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"english_word_list\">\n";
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";	//add hasegawa 2015/11/17 ページング処理不具合回避
	}
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}


/**
 * ＤＢ登録処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function add() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$problem_num = "";
	$t_problem_num = ""; //add kimura 2018/11/16 すらら英単語 _ワード管理

	// 登録項目設定
	foreach ($_POST as $key => $val) {
		if ($key == "action") { continue; }
		if ($key == "problem_num") {
			$problem_num = $val;
			continue;
		}
		//add start kimura 2018/11/16 すらら英単語 _ワード管理
		if ($key == "t_problem_num") {
			$t_problem_num = $val;
			continue;
		}
		//add end   kimura 2018/11/16 すらら英単語 _ワード管理
		$INSERT_DATA[$key] = "$val";
	}
	// $INSERT_DATA['course_num'] = $_SESSION['sub_session']['course_num']; //del kimura 2018/11/15 すらら英単語 _ワード管理
	list($write_type, $is_test, $test_type_num, $service_num) = get_english_word_info($_POST['course_num']); //add kimura 2018/11/16 すらら英単語 _ワード管理
	//add start kimura 2018/11/15 すらら英単語 _ワード管理
	//テスト
	if($is_test){
		$INSERT_DATA['test_type_num'] = $test_type_num;
		$INSERT_DATA['course_num'] = null;
	//授業
	}else{
		$INSERT_DATA['course_num'] = $_SESSION['sub_session']['course_num'];
		$INSERT_DATA['test_type_num'] = null;
	}
	if($service_num == 16 || $service_num == 17){
		$INSERT_DATA['word_type'] = 1; //区分1で固定
		//配列のインデックス自体がないとNULLになるようなのでわざと空文字をセット
		$INSERT_DATA['japanese_translation'] = "";
		$INSERT_DATA['voice_data'] = "";
		$INSERT_DATA['example_sentence'] = "";
		$INSERT_DATA['example_sentence_jp'] = "";
		$INSERT_DATA['example_sentence_voice'] = "";
	}
	//add end   kimura 2018/11/15 すらら英単語 _ワード管理
	$INSERT_DATA['ins_syr_id'] = "add";
	$INSERT_DATA['ins_date']   = "now()";
	$INSERT_DATA['ins_tts_id'] = $_SESSION['myid']['id'];
	$INSERT_DATA['upd_syr_id'] = "add";
	$INSERT_DATA['upd_date']   = "now()";
	$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
	// ＤＢ追加処理
	$ERROR = $cdb->insert(T_ENGLISH_WORD,$INSERT_DATA);

	// ワード管理番号を取得し、ワード問題テーブルの更新を行う
	if (!$ERROR) {
		$english_word_num = $cdb->insert_id();
		//update start kimura 2018/11/16 すらら英単語 _ワード管理
		// $ERROR = update_word_problem($english_word_num, $problem_num);
		$ERROR = update_word_problem($english_word_num, $problem_num, $t_problem_num);
		//update end   kimura 2018/11/16 すらら英単語 _ワード管理
	}

	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}


/**
 * ＤＢ変更処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function change() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// 更新処理
	if (MODE == "詳細") {

		// $INSERT_DATA['course_num']				= $_SESSION['sub_session']['course_num']; //del kimura 2018/11/15 すらら英単語 _ワード管理
		list($write_type, $is_test, $test_type_num, $service_num) = get_english_word_info($_POST['course_num']); //add kimura 2018/11/16 すらら英単語 _ワード管理
		//add start kimura 2018/11/15 すらら英単語 _ワード管理
		//テスト
		if($is_test){
			$INSERT_DATA['test_type_num'] = $test_type_num;
			$INSERT_DATA['course_num'] = null;
			//授業
		}else{
			$INSERT_DATA['course_num'] = $_SESSION['sub_session']['course_num'];
			$INSERT_DATA['test_type_num'] = null;
		}
		//add end   kimura 2018/11/15 すらら英単語 _ワード管理
		$INSERT_DATA['word_type']				= $_POST['word_type'];
		$INSERT_DATA['english']					= $_POST['english'];
		$INSERT_DATA['japanese_translation']	= $_POST['japanese_translation'];
		$INSERT_DATA['part_of_speech_type']		= $_POST['part_of_speech_type'];
		$INSERT_DATA['voice_data']				= $_POST['voice_data'];
		$INSERT_DATA['example_sentence']		= $_POST['example_sentence'];
		$INSERT_DATA['example_sentence_jp']		= $_POST['example_sentence_jp'];
		$INSERT_DATA['example_sentence_voice']	= $_POST['example_sentence_voice'];
		$INSERT_DATA['display']					= $_POST['display'];
		$INSERT_DATA['upd_syr_id']				= "update";
		$INSERT_DATA['upd_date']				= "now()";
		$INSERT_DATA['upd_tts_id']				= $_SESSION['myid']['id'];
		//add end   kimura 2018/11/20 すらら英単語 _ワード管理

		$where = " WHERE english_word_num = '".$_POST['english_word_num']."' LIMIT 1;";

		$ERROR = $cdb->update(T_ENGLISH_WORD,$INSERT_DATA,$where);

		// ワード問題管理テーブル更新
		//update start kimura 2018/11/16 すらら英単語 _ワード管理
		// $ERROR = update_word_problem($_POST['english_word_num'], $_POST['problem_num']);
		$ERROR = update_word_problem($_POST['english_word_num'], $_POST['problem_num'], $_POST['t_problem_num']);
		//update end   kimura 2018/11/16 すらら英単語 _ワード管理

	// 削除処理
	} elseif (MODE == "削除") {
		$INSERT_DATA['mk_flg']		= "1";
		$INSERT_DATA['mk_tts_id']	= $_SESSION['myid']['id'];
		$INSERT_DATA['mk_date']		= "now()";
		$INSERT_DATA['upd_syr_id']   = "del";									// 2012/11/05 add oda
		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];					// 2012/11/05 add oda
		$INSERT_DATA['upd_date']   = "now()";									// 2012/11/05 add oda
		$where = " WHERE english_word_num = '".$_POST['english_word_num']."' LIMIT 1;";

		$ERROR = $cdb->update(T_ENGLISH_WORD,$INSERT_DATA,$where);

		// ワード問題管理テーブル削除
		$ERROR = delete_word_problem($_POST['english_word_num']);
	}
	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}


/**
 * サブセションをPOSTから設定
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * (POST項目では、画面再描画の際、パラメータの受け渡しができない為）
 * @author Azet
 */
function sub_session() {

	// ページ数をPOSTから取得し、セションに格納
	if (strlen($_POST['s_page_view'])) {
		$_SESSION['sub_session']['s_page_view'] = $_POST['s_page_view'];
	}
	if ($_SESSION['sub_session']['course_num'] != $_POST['course_num']) {
		$_SESSION['sub_session']['s_page_view'] = "";
	}

	// 現在ページをPOSTから取得し、セションに格納
	if (strlen($_POST['s_page'])) {
		$_SESSION['sub_session']['s_page'] = $_POST['s_page'];
	}
	if ($_SESSION['sub_session']['course_num'] != $_POST['course_num']) {
		$_SESSION['sub_session']['s_page'] = "";
	}

	// 検索単語をPOSTから取得し、セションに格納			// add oda 2013/10/07
	$_SESSION['sub_session']['view_english_word'] = $_POST['view_english_word'];
	if ($_SESSION['sub_session']['course_num'] != $_POST['course_num']) {
		$_SESSION['sub_session']['view_english_word'] = "";
	}

	// コースをPOSTから取得し、セションに格納
	if (strlen($_POST['course_num'])) {
		$_SESSION['sub_session']['course_num'] = $_POST['course_num'];
	}
	//add start kimura 2018/11/16 すらら英単語 _ワード管理
	// サービスをPOSTから取得し、セッションに格納

	if ($_SESSION['sub_session']['course_num'] != $_POST['course_num']) {
		$_SESSION['sub_session']['service_num'] = $_POST['service_num'];
	}
	//add end   kimura 2018/11/16 すらら英単語 _ワード管理

	return;
}



/**
 * コース選択
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function select_course() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
	global $L_SERVICES_USE_WORD; //add kimura 2018/09/28 漢字学習コンテンツ_書写ドリル対応 //ワード管理使用サービス番号配列

	// コース読み込み
	// del start 2019/05/20 yoshizawa すらら英単語テストワード管理
	// //del start kimura 2018/09/28 漢字学習コンテンツ_書写ドリル対応
	// // $sql  = "SELECT * FROM ".T_COURSE.
	// // 		" WHERE state='0' ".
	// // 		"   AND write_type='1' ".
	// // 		" ORDER BY list_num;";
	// //del end   kimura 2018/09/28 漢字学習コンテンツ_書写ドリル対応
	// //書写フォームタイプの実装に伴い、write_typeからserviceでの識別に変更します。//write_type1限定だと国語が管理できないため
	// //add start kimura 2018/09/28 漢字学習コンテンツ_書写ドリル対応
	// // $sql = "SELECT *"; //del kimura 2018/11/15 すらら英単語 _ワード管理
	// $sql = "(";
	// $sql.= " SELECT c.course_num, c.course_name, sc.course_type, c.list_num"; //add kimura 2018/11/15 すらら英単語 _ワード管理
	// $sql.= " FROM ".T_COURSE." c";
	// $sql.= " INNER JOIN ".T_SERVICE_COURSE_LIST." sc";
	// $sql.= "  ON c.course_num = sc.course_num AND sc.mk_flg = '0'";
	// $sql.= " INNER JOIN ".T_SERVICE." s";
	// $sql.= "  ON sc.service_num = s.service_num AND s.mk_flg = '0'";
	// $sql.= "  AND s.service_num IN (".implode(",", $L_SERVICES_USE_WORD).")";
	// $sql.= " WHERE 1";
	// $sql.= "  AND c.state = '0'";
	// // >>> add 2018/10/09 yoshizawa すらら英単語追加
	// // service_course_listのcourse_numはsetup_type毎に管理番号が重複するため、不要なマスタ（すらら英単語テスト）を取得しない様に条件を指定する。
	// $sql.= "  AND sc.course_type = '1' ";		// 1：授業
	// //$sql.= "  AND s.setup_type IN ('1','3') ";	// 1：勉強する、3：TOEIC //del kimura 2018/11/15 すらら英単語 _ワード管理
	// $sql.= "  AND s.setup_type IN ('1','3','4') ";// 1：勉強する、3：TOEIC 、4: 単語サービス //add kimura 2018/11/15 すらら英単語 _ワード管理
	// // <<<
	// //add start kimura 2018/11/15 すらら英単語 _ワード管理
	// $sql.= " ORDER BY c.list_num";
	// $sql.= ")";
	// $sql.= " UNION ALL";
	// $sql.=" ( SELECT -1, '--以下はすらら英単語テスト--' AS course_name, NULL, NULL)";
	// $sql.= " UNION ALL";
	// $sql.= "(";
	// $sql.= " SELECT mtt.test_type_num AS course_num, mtt.test_type_name AS course_name, sc.course_type, mtt.list_num";
	// $sql.= " FROM ".T_MS_TEST_TYPE." mtt";
	// $sql.= " INNER JOIN ".T_SERVICE_COURSE_LIST." sc";
	// $sql.= "  ON sc.course_num = mtt.test_type_num AND sc.mk_flg = '0'";
	// $sql.= " INNER JOIN ".T_SERVICE." s";
	// $sql.= "  ON sc.service_num = s.service_num AND s.mk_flg = '0'";
	// $sql.= "  AND s.service_num IN (".implode(",", $L_SERVICES_USE_WORD).")";
	// $sql.= " WHERE 1";
	// $sql.= "  AND s.setup_type = '5'";
	// $sql.= " ORDER BY mtt.list_num";
	// $sql.= ")"; 
	// //add end   kimura 2018/11/15 すらら英単語 _ワード管理
	// //$sql.= " ORDER BY c.list_num"; //del kimura 2018/11/15 すらら英単語 _ワード管理
	// $sql.= ";";
	// //NOTE : それぞれのテーブルのlist_num順でソートした後ユニオンしています。必然的にコースが上部、テスト種類が下になります。
	// //add end   kimura 2018/09/28 漢字学習コンテンツ_書写ドリル対応
	// if ($result = $cdb->query($sql)) {
	// 	$max = $cdb->num_rows($result);
	// }
	// del end 2019/05/20 yoshizawa すらら英単語テストワード管理
	// add start 2019/05/20 yoshizawa すらら英単語テストワード管理
	$type = "";
	$sql = "";
	if(MAIN == "practice"){
		// プラクティスステージ管理の場合
		$type = "コース";

		$sql.= " SELECT c.course_num, c.course_name, sc.course_type, c.list_num";
		$sql.= " FROM ".T_COURSE." c";
		$sql.= " INNER JOIN ".T_SERVICE_COURSE_LIST." sc";
		$sql.= "  ON c.course_num = sc.course_num AND sc.mk_flg = '0'";
		$sql.= " INNER JOIN ".T_SERVICE." s";
		$sql.= "  ON sc.service_num = s.service_num AND s.mk_flg = '0'";
		$sql.= "  AND s.service_num IN (".implode(",", $L_SERVICES_USE_WORD).")";
		$sql.= " WHERE 1";
		$sql.= "  AND c.state = '0'";
		$sql.= "  AND sc.course_type = '1' ";			// 1：授業
		$sql.= "  AND s.setup_type IN ('1','3','4') ";	// 1：勉強する、3：TOEIC 、4: 単語サービス
		$sql.= " ORDER BY c.list_num";
		$sql.= ";";
	} else if(MAIN == "test_practice"){
		// テストプラクティス管理の場合
		$type = "テスト種類";

		$sql.= " SELECT mtt.test_type_num AS course_num, mtt.test_type_name AS course_name, sc.course_type, mtt.list_num";
		$sql.= " FROM ".T_MS_TEST_TYPE." mtt";
		$sql.= " INNER JOIN ".T_SERVICE_COURSE_LIST." sc";
		$sql.= "  ON sc.course_num = mtt.test_type_num AND sc.mk_flg = '0'";
		$sql.= " INNER JOIN ".T_SERVICE." s";
		$sql.= "  ON sc.service_num = s.service_num AND s.mk_flg = '0'";
		$sql.= "  AND s.service_num IN (".implode(",", $L_SERVICES_USE_WORD).")";
		$sql.= " WHERE 1";
		$sql.= "  AND s.setup_type = '5'";	// 単語テスト
		$sql.= " ORDER BY mtt.list_num";
		$sql.= ";";
	}
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
	}
	// add end 2019/05/20 yoshizawa すらら英単語テストワード管理

	// optionタグ生成
	if (!$max) {
		$html = "<br>\n";
		// $html .= "コースが存在しません。設定してからご利用下さい。";	// del 2019/05/20 yoshizawa すらら英単語テストワード管理
		$html .= $type."が存在しません。設定してからご利用下さい。";	// add 2019/05/20 yoshizawa すらら英単語テストワード管理
		return $html;
	} else {
		if (!$_SESSION['sub_session']['course_num']) { $selected = "selected"; } else { $selected = ""; }
		$couse_num_html .= "<option value=\"\" $selected>選択して下さい</option>\n";
		while ($list = $cdb->fetch_assoc($result)) {
			$course_num_ = $list['course_num'];
			//add start kimura 2018/11/15 すらら英単語 _ワード管理
			if($list['course_type'] == 4){
				$course_num_.= "T";
			}
			//add end   kimura 2018/11/15 すらら英単語 _ワード管理
			$course_name_ = $list['course_name'];
			if ($_SESSION['sub_session']['course_num'] == $course_num_) { $selected = "selected"; } else { $selected = ""; }
			// $couse_num_html .= "<option value=\"".$course_num_."\" $selected>".$course_name_."</option>\n"; //del kimura 2018/11/21 すらら英単語 _admin
			$couse_num_html .= "<option value=\"".$course_num_."\" ".($course_num_ === "-1" ? "disabled" : $selected).">".$course_name_."</option>\n"; //add kimura 2018/11/21 すらら英単語 _admin //区切りのoption(見た目用)を追加 //コース番号:string型"-1"
		}
	}
	//add end   kimura 2018/11/16 すらら英単語 _ワード管理

	// 抽出条件表示
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" name=\"menu\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"set_course\">\n";
	$html .= "<table class=\"unit_form\">\n";
	$html .= "<tr class=\"unit_form_menu\">\n";
	//$html .= "<td>コース</td>\n";		// del 2019/05/20 yoshizawa すらら英単語テストワード管理
	$html .= "<td>".$type."</td>\n";	// add 2019/05/20 yoshizawa すらら英単語テストワード管理
	$html .= "</tr>\n";
	$html .= "<tr class=\"unit_form_cell\">\n";
	$html .= "<td><select name=\"course_num\" onchange=\"submit();\">\n";
	$html .= $couse_num_html;
	$html .= "</select></td>\n";
	$html .= "</tr>\n";
	$html .= "</table>\n";
	$html .= "</form><br>\n";

	// コース未選択メッセージ
	if (!$_SESSION['sub_session']['course_num']) {
		// $html .= "コースを選択してください。<br>\n";	// del 2019/05/20 yoshizawa すらら英単語テストワード管理
		$html .= $type."を選択してください。<br>\n";	// add 2019/05/20 yoshizawa すらら英単語テストワード管理
	}

	return $html;
}


/**
 * アップロードファイル取り込み処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function english_word_upload() {

	global $L_WRITE_TYPE, $L_WORD_FIELD_ALIAS; //add kimura 2018/10/03 漢字学習コンテンツ_書写ドリル対応
	$CSV_COLUMN_COUNT = 12; //add kimura 2018/11/27 すらら英単語 _admin
	$CSV_COLUMN_COUNT_TEST = 13; //add kimura 2018/11/27 すらら英単語 _admin

	if (!$_POST['course_num']) {
		// update start 2019/05/20 yoshizawa すらら英単語テストワード管理
		// $ERROR[] = "コースが確認できません。";
		// 
		$type = "コース";
		if(MAIN == "test_practice"){ $type = "テスト種類"; }
		$ERROR[] = $type."が確認できません。";
		// update end 2019/05/20 yoshizawa すらら英単語テストワード管理
	}

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$file_name = $_FILES['english_word_file']['name'];
	$file_tmp_name = $_FILES['english_word_file']['tmp_name'];
	$file_error = $_FILES['english_word_file']['error'];

	if (!$file_tmp_name) {
		$ERROR[] = "ワード管理ファイルが指定されておりません。";
	} elseif (!eregi("(.txt)$",$file_name)) {
		$ERROR[] = "ファイルの拡張子が不正です。";
	} elseif ($file_error == 1) {
		$ERROR[] = "アップロードできるファイルの容量が設定範囲を超えてます。サーバー管理者へ相談してください。";
	} elseif ($file_error == 3) {
		$ERROR[] = "ファイルの一部分のみしかアップロードされませんでした。";
	} elseif ($file_error == 4) {
		$ERROR[] = "ファイルがアップロードされませんでした。";
	}
	if ($ERROR) {
		if ($file_tmp_name && file_exists($file_tmp_name)) {
			unlink($file_tmp_name);
		}
		return $ERROR;
	}

	// $write_type = get_write_type_by_course($_POST['course_num']); //add kimura 2018/10/03 漢字学習コンテンツ_書写ドリル対応 //del kimura 2018/11/16 すらら英単語 _ワード管理
	list($write_type, $is_test, $test_type_num, $service_num) = get_english_word_info($_POST['course_num']); //add kimura 2018/11/16 すらら英単語 _ワード管理

	$word_course_assoc = get_word_course_assoc($_POST['course_num']); //add kimura 2018/11/27 すらら英単語 _admin  // ワード番号 => コース番号の連想配列を取得しておく

	$LIST = file($file_tmp_name);
	if ($LIST) {
		$i = 0;
		foreach ($LIST AS $VAL) {
			if ($i == 0) {
				$i++;  continue;
			}
			unset($LINE);
			// $VAL = trim($VAL); //del kimura 2018/11/27 すらら英単語 _admin //意図しない\tが消えてしまいTSVデータの認識不全が起こるので消します。
			if (!$VAL || !ereg("\t",$VAL)) {
				continue;
			}
			$file_data = explode("\t",$VAL);
			//add start kimura 2018/11/27 すらら英単語 _admin (注)テストとそれ以外で出力ファイルのフォーマットが変わります。
			//--------------------
			//すらら英単語テストだけは13列のTSVを読み込みます
			//--------------------
			if($is_test){
				if(count($file_data) != $CSV_COLUMN_COUNT_TEST){ //データフォーマットが正しくなかったらスキップ
					$ERROR[] = "登録処理を中断しました。データ形式が正しくありません。(".$CSV_COLUMN_COUNT_TEST."列のタブ区切りのデータで指定してください。)";
					$i = 0; unset($LINE);
					break; //以降の読み込みも全部中止
				}
				$LINE['english_word_num']       = $file_data[0];
				$LINE['word_type']              = $file_data[1];
				$LINE['english']                = $file_data[2];
				$LINE['japanese_translation']   = $file_data[3];
				$LINE['part_of_speech_type']    = $file_data[4];
				$LINE['voice_data']             = $file_data[5];
				$LINE['example_sentence']       = $file_data[6];
				$LINE['example_sentence_jp']    = $file_data[7];
				$LINE['example_sentence_voice'] = $file_data[8];
				$LINE['surara_problem_num']     = $file_data[9];
				$LINE['word_test_problem_num']  = $file_data[10]; //すらら英単語テスト問題番号
				$LINE['display']                = $file_data[11];
				$LINE['mk_flg']                 = $file_data[12];
			//--------------------
			//それ以外は12列のTSVを読み込みます。
			//--------------------
			}else{
				if(count($file_data) != $CSV_COLUMN_COUNT){ //データフォーマットが正しくなかったらスキップ
					$ERROR[] = "登録処理を中断しました。データ形式が正しくありません。(".$CSV_COLUMN_COUNT."列のタブ区切りデータで指定してください。)";
					$i = 0; unset($LINE);
					break; //以降の読み込みも全部中止
				}
				$LINE['english_word_num']       = $file_data[0];
				$LINE['word_type']              = $file_data[1];
				$LINE['english']                = $file_data[2];
				$LINE['japanese_translation']   = $file_data[3];
				$LINE['part_of_speech_type']    = $file_data[4];
				$LINE['voice_data']             = $file_data[5];
				$LINE['example_sentence']       = $file_data[6];
				$LINE['example_sentence_jp']    = $file_data[7];
				$LINE['example_sentence_voice'] = $file_data[8];
				$LINE['surara_problem_num']     = $file_data[9];
				$LINE['display']                = $file_data[10];
				$LINE['mk_flg']                 = $file_data[11];
			}
			//add end   kimura 2018/11/27 すらら英単語 _admin

			/*//del start kimura 2018/11/27 すらら英単語 _admin
			// 項目が全て設定されている場合
			if (count($file_data) == 12) {
				$LINE['english_word_num'] = $file_data[0];
				$LINE['word_type'] = $file_data[1];
				$LINE['english'] = $file_data[2];
				$LINE['japanese_translation'] = $file_data[3];
				$LINE['part_of_speech_type'] = $file_data[4];
				$LINE['voice_data'] = $file_data[5];
				$LINE['example_sentence'] = $file_data[6];
				$LINE['example_sentence_jp'] = $file_data[7];
				$LINE['example_sentence_voice'] = $file_data[8];
				$LINE['surara_problem_num'] = $file_data[9];
				$LINE['display'] = $file_data[10];
				$LINE['mk_flg'] = $file_data[11];
				// 管理番号が未設定の場合は、格納配列が１つ前にずれる
			} elseif (count($file_data) == 11) {
				$LINE['english_word_num'] = "0";
				$LINE['word_type'] = $file_data[0];
				$LINE['english'] = $file_data[1];
				$LINE['japanese_translation'] = $file_data[2];
				$LINE['part_of_speech_type'] = $file_data[3];
				$LINE['voice_data'] = $file_data[4];
				$LINE['example_sentence'] = $file_data[5];
				$LINE['example_sentence_jp'] = $file_data[6];
				$LINE['example_sentence_voice'] = $file_data[7];
				$LINE['surara_problem_num'] = $file_data[8];
				$LINE['display'] = $file_data[9];
				$LINE['mk_flg'] = $file_data[10];
			} else {
				continue;
			}
			//del end   kimura 2018/11/27 すらら英単語 _admin */
			//			list(
			//				$LINE['one_point_num'],$LINE['list_num'],
			//				$LINE['study_title'],$LINE['one_point_commentary'],
			//				$LINE['surala_unit'],$LINE['display'],$LINE['mk_flg']) = explode("\t",$VAL);
			if ($LINE) {
				foreach ($LINE AS $key => $val) {
					if ($val) {
						//	del 2014/12/08 yoshizawa 課題要望一覧No.394対応 下に新規で作成
						//$val = mb_convert_encoding($val,"UTF-8","sjis-win");
						//----------------------------------------------------------------
						//	add 2014/12/08 yoshizawa 課題要望一覧No.394対応
						//	データの文字コードがUTF-8だったら変換処理をしない
						$code = judgeCharacterCode ( $val );
						if ( $code != 'UTF-8' ) {
							$val = mb_convert_encoding($val,"UTF-8","sjis-win");
						}
						//--------------------------------------------------
						$LINE[$key] = replace_word($val);
					}
				}
			}

			$LINE['course_num'] = $_POST['course_num'];

			// 内容チェック
			$error_flg = false;
			//add start kimura 2018/11/27 すらら英単語 _admin //読み込んだ管理番号がすでに別のコース（テスト種別）に紐づいていないか確認。でないとコースをまたいでユニークキーの奪い合いになる。
			if(isset($word_course_assoc[$LINE['english_word_num']])){
				if($word_course_assoc[$LINE['english_word_num']] != $LINE['course_num']){ //$LINE['course_num']にはテストの場合は「テスト種類管理番号+"T"」が入っています
					$ERROR[] = ($i+1)."行目　指定された管理番号は、すでに別のコース（テスト種類）で使用されているためスキップしました。";
					$i++; //カウンター増やしてからｺﾝﾃｨﾆｭｰ
					continue;
				}
			}
			//add end   kimura 2018/11/27 すらら英単語 _admin

			//del start kimura 2018/11/16 すらら英単語 _ワード管理
			// if (!$LINE['word_type']) {
			// 	$ERROR[] = ($i+1)."行目　区分が未入力です。";
			// 	$error_flg = true;
			// }
			//del end   kimura 2018/11/16 すらら英単語 _ワード管理
			if (!$LINE['english']) {
				//update start kimura 2018/10/09 漢字学習コンテンツ_書写ドリル対応
				//$ERROR[] = ($i+1)."行目　単語／熟語が未入力です。";
				$ERROR[] = ($i+1)."行目　".$L_WORD_FIELD_ALIAS[$write_type]['english2']."が未入力です。";
				//update end   kimura 2018/10/09 漢字学習コンテンツ_書写ドリル対応
				$error_flg = true;
			}
			//add start kimura 2018/11/16 すらら英単語 _ワード管理
			if($service_num != 16 && $service_num != 17){
				if (!$LINE['word_type']) {
					$ERROR[] = ($i+1)."行目　区分が未入力です。";
					$error_flg = true;
				}
				if (!$LINE['japanese_translation']) {
					$ERROR[] = ($i+1)."行目　".$L_WORD_FIELD_ALIAS[$write_type]['japanese_translation']."が未入力です。";
					$error_flg = true;
				}
			}
			if (!$LINE['display']) {
				$ERROR[] = ($i+1)."行目　表示区分が未入力です。";
				$error_flg = true;
			}
			//add end   kimura 2018/11/16 すらら英単語 _ワード管理

			//del start kimura 2018/11/16 すらら英単語 _ワード管理
			//if (!$LINE['japanese_translation']) {
			//	//update start kimura 2018/10/09 漢字学習コンテンツ_書写ドリル対応
			//	//$ERROR[] = ($i+1)."行目　和訳が未入力です。";
			//	$ERROR[] = ($i+1)."行目　".$L_WORD_FIELD_ALIAS[$write_type]['japanese_translation']."が未入力です。";
			//	//update end   kimura 2018/10/09 漢字学習コンテンツ_書写ドリル対応
			//	$error_flg = true;
			//}
			//if (!$LINE['display']) {
			//	$ERROR[] = ($i+1)."行目　表示区分が未入力です。";
			//	$error_flg = true;
			//}
			//del end   kimura 2018/11/16 すらら英単語 _ワード管理

			$word_problem_list = array();
			if ($LINE['surara_problem_num']) {
				$word_problem_count = 0;
				$word_problem_list = explode("::",$LINE['surara_problem_num']);

				// 問題番号に数値以外の値が入っていないかチェック
				$word_problem_error_flag = false;
				for ($j=0; $j<count($word_problem_list); $j++) {
					if (trim($word_problem_list[$j]) != "" && is_numeric($word_problem_list[$j]) == false) {
						// $ERROR[] = ($i+1)."行目　すらら問題番号に指定した番号は、不正な情報が混ざっています。"; //del kimura 2018/11/16 すらら英単語 _ワード管理
						$ERROR[] = ($i+1)."行目　すらら".(($service_num == 16 || $service_num == 17) ? "英単語" : "")."問題番号に指定した番号は、不正な情報が混ざっています。"; //add kimura 2018/11/16 すらら英単語 _ワード管理
						$word_problem_error_flag = true;
						$error_flg = true;
					}
				}

				// 問題番号が存在するかチェック
				if (!$word_problem_error_flag) {
					$sql  = "SELECT * FROM ".T_PROBLEM.
							" WHERE state='0' AND problem_num IN ('" . implode("','", $word_problem_list) ."')";
					//add start kimura 2018/11/16 すらら英単語 _ワード管理
					if($service_num == 17){
						//テスト種類問題番号単位にして取得する
						$sql = "SELECT DISTINCT test_type_num, problem_table_type, problem_num";
						$sql.= " FROM ".T_MS_TEST_CATEGORY2_PROBLEM;
						$sql.= " WHERE mk_flg = '0' AND problem_num IN ('" . implode("','", $word_problem_list) ."')";
						$sql.= " AND problem_table_type = '1'"; //すらら問題
						$sql.= " AND test_type_num = '".$test_type_num."'";
					}
					//add end   kimura 2018/11/16 すらら英単語 _ワード管理
					if ($result = $cdb->query($sql)) {
						$word_problem_count = $cdb->num_rows($result);
					}
					// if ($word_problem_count > 0) { //del kimura 2018/11/20 すらら英単語 _ワード管理 //入力した番号の問題マスタが存在しない場合エラーが発生しないのでこの判断消します。
						if ($word_problem_count != count($word_problem_list)) {
							// $ERROR[] = ($i+1)."行目　すらら問題番号に指定した番号は、既に削除されているか、重複または不正な情報が混ざっています。すらら問題番号=".$LINE['surara_problem_num']; //del kimura 2018/11/16 すらら英単語 _ワード管理
							$ERROR[] = ($i+1)."行目　すらら".(($service_num == 16 || $service_num == 17) ? "英単語" : "")."問題番号に指定した番号は、既に削除されているか、重複または不正な情報が混ざっています。すらら問題番号=".$LINE['surara_problem_num']; //add kimura 2018/11/16 すらら英単語 _ワード管理
							$error_flg = true;
						}
					// } //del kimura 2018/11/20 すらら英単語 _ワード管理
				}
			}
			//add start kimura 2018/11/16 すらら英単語 _ワード管理
			//------------------------------
			//テスト専用問題のバリデーション
			//------------------------------
			$word_test_problem_list = array();
			if ($LINE['word_test_problem_num'] && $service_num == 17) {
				$word_test_problem_count = 0;
				$word_test_problem_list = explode("::",$LINE['word_test_problem_num']);

				// 問題番号に数値以外の値が入っていないかチェック
				$word_test_problem_error_flag = false;
				for ($j=0; $j<count($word_test_problem_list); $j++) {
					if (trim($word_test_problem_list[$j]) != "" && is_numeric($word_test_problem_list[$j]) == false) {
						$ERROR[] = ($i+1)."行目　すらら英単語テスト問題番号に指定した番号は、不正な情報が混ざっています。";
						$word_test_problem_error_flag = true;
						$error_flg = true;
					}
				}

				// 問題番号が存在するかチェック
				if (!$word_test_problem_error_flag) {
					//ms_test_category2_problemテーブルにはテスト種別1,種別2が違う問題のレコードも存在するので
					//テスト種類問題番号単位にして取得する
					$sql = "SELECT DISTINCT test_type_num, problem_table_type, problem_num";
					$sql.= " FROM ".T_MS_TEST_CATEGORY2_PROBLEM;
					$sql.= " WHERE mk_flg = '0' AND problem_num IN ('" . implode("','", $word_test_problem_list) ."') AND problem_table_type = '3'"; //すらら英単語テスト問題
					if ($result = $cdb->query($sql)) {
						$word_test_problem_count = $cdb->num_rows($result);
					}
					if ($word_test_problem_count != count($word_test_problem_list)) {
						$ERROR[] = ($i+1)."行目　すらら英単語テスト問題番号に指定した番号は、既に削除されているか、重複または不正な情報が混ざっています。すらら英単語テスト問題番号=".$LINE['word_test_problem_num'];
						$error_flg = true;
					}
				}
			}
			//add end   kimura 2018/11/16 すらら英単語 _ワード管理

			//add start kimura 2018/10/03 漢字学習コンテンツ_書写ドリル対応
			//write_tupe2特有のエラーチェック
			if($write_type == 2){
				$kyouka = $L_WRITE_TYPE[$write_type];
				if($LINE['word_type'] != 4 && $LINE['word_type'] != 5){
					$ERROR[] = ($i+1)."行目　".$kyouka."のワードには区分「4,5」以外を指定することができません。";
					$error_flg = true;
				}

				if($LINE['part_of_speech_type'] != "" ||
					$LINE['voice_data'] != "" ||
					$LINE['example_sentence'] != "" ||
					$LINE['example_sentence_voice'] != ""
					|| $LINE['word_test_problem_num'] != "" //add kimura 2018/11/19 すらら英単語 _ワード管理
				){
					$ERROR[] = ($i+1)."行目　不要なフィールドに値が指定されています。";
					// $LINE['part_of_speech_type'] = ""; //del kimura 2018/11/20 すらら英単語 _ワード管理
					// $LINE['voice_data'] = ""; //del kimura 2018/11/20 すらら英単語 _ワード管理
					// $LINE['example_sentence'] = ""; //del kimura 2018/11/20 すらら英単語 _ワード管理
					// $LINE['example_sentence_voice'] = ""; //del kimura 2018/11/20 すらら英単語 _ワード管理
					$error_flg = true;
				}
				//add start kimura 2018/11/20 すらら英単語 _ワード管理
				$LINE['part_of_speech_type'] = "";
				$LINE['voice_data'] = "";
				$LINE['example_sentence'] = "";
				$LINE['example_sentence_voice'] = "";
				$LINE['word_test_problem_num'] = "";
				//add end   kimura 2018/11/20 すらら英単語 _ワード管理
			}
			//add end   kimura 2018/10/03 漢字学習コンテンツ_書写ドリル対応

			//add start kimura 2018/11/19 すらら英単語 _ワード管理
			//テスト以外のエラーチェック すらら英単語テスト問題番号フィールド
			if(!$is_test){
				if($LINE['word_test_problem_num'] != ""){
					$ERROR[] = ($i+1)."行目　不要なフィールドに値が指定されています。";
					$LINE['word_test_problem_num'] = "";
					$error_flg = true;
				}
			}

			//すらら英単語・すらら英単語テストサービスのエラーチェック
			//和訳,区分,品詞,音声データ,例文,例文和訳,例文音声を不要とする
			if($service_num == 16 || $service_num == 17){
				if( $LINE['word_type'] != "" ||
					$LINE['part_of_speech_type'] != "" ||
					$LINE['voice_data'] != "" ||
					$LINE['example_sentence'] != "" ||
					$LINE['example_sentence_jp'] != "" ||
					$LINE['example_sentence_voice'] != ""
				){
					$ERROR[] = ($i+1)."行目　不要なフィールドに値が指定されています。";
					$error_flg = true;
				}
				$LINE['japanese_translation'] = "";
				$LINE['word_type'] = 1; //区分を1に固定
				$LINE['part_of_speech_type'] = "";
				$LINE['voice_data'] = "";
				$LINE['example_sentence'] = "";
				$LINE['example_sentence_jp'] = "";
				$LINE['example_sentence_voice'] = "";
			}
			//add end   kimura 2018/11/19 すらら英単語 _ワード管理

			if (!$LINE['mk_flg']) {
				$LINE['mk_flg'] = "0";
			}

			// エラーの時は強制的に非表示とする。
			if ($error_flg) {
				$LINE['display'] = "2";
			}

			// データ存在チェック
			$sql  = "SELECT * FROM " . T_ENGLISH_WORD .
					" WHERE english_word_num = '".$LINE['english_word_num']."';";
			$english_word_count = 0;
			if ($result = $cdb->query($sql)) {
				$english_word_count = $cdb->num_rows($result);
			}
			// 登録処理
			if ($english_word_count == 0) {
				$INSERT_DATA = array();

				$INSERT_DATA['course_num']				= $_POST['course_num'];
				//add start kimura 2018/11/16 すらら英単語 _ワード管理
				if($is_test){
					$INSERT_DATA['test_type_num'] = $test_type_num;
					$INSERT_DATA['course_num'] = null;
				}
				//add end   kimura 2018/11/16 すらら英単語 _ワード管理

				$INSERT_DATA['word_type']				= $LINE['word_type'];
				$INSERT_DATA['english']					= $LINE['english'];
				$INSERT_DATA['japanese_translation']	= $LINE['japanese_translation'];
				$INSERT_DATA['part_of_speech_type']		= $LINE['part_of_speech_type'];
				$INSERT_DATA['voice_data']				= $LINE['voice_data'];
				$INSERT_DATA['example_sentence']		= $LINE['example_sentence'];
				$INSERT_DATA['example_sentence_jp']		= $LINE['example_sentence_jp'];
				$INSERT_DATA['example_sentence_voice']	= $LINE['example_sentence_voice'];
//				$INSERT_DATA['surara_problem_num']		= $LINE['surara_problem_num'];

				$INSERT_DATA['display']					= $LINE['display'];
				$INSERT_DATA['mk_flg']					= $LINE['mk_flg'];

				$INSERT_DATA['ins_syr_id']		= "add";
				$INSERT_DATA['ins_date']		= "now()";
				$INSERT_DATA['ins_tts_id']		= $_SESSION['myid']['id'];
				$INSERT_DATA['upd_syr_id']		= "add";
				$INSERT_DATA['upd_date']		= "now()";
				$INSERT_DATA['upd_tts_id']		= $_SESSION['myid']['id'];

				$INSERT_ERROR = $cdb->insert(T_ENGLISH_WORD,$INSERT_DATA);
				if(count($INSERT_ERROR) > 0) {
					foreach ($INSERT_ERROR AS $VAL) {
						$ERROR[] = $VAL;
					}
				}

				if (!$ERROR) {
					$english_word_num = $cdb->insert_id();
					//update start kimura 2018/11/16 すらら英単語 _ワード管理
					// $INSERT_ERROR = update_word_problem($english_word_num, $LINE['surara_problem_num']);
					$INSERT_ERROR = update_word_problem($english_word_num, $LINE['surara_problem_num'], $LINE['word_test_problem_num']);
					//update end   kimura 2018/11/16 すらら英単語 _ワード管理
					if(count($INSERT_ERROR) > 0) {
						foreach ($INSERT_ERROR AS $VAL) {
							$ERROR[] = $VAL;
						}
					}
				}

				// 更新処理
			} else {

				$INSERT_DATA = array();

				$INSERT_DATA['course_num']				= $_POST['course_num'];
				//add start kimura 2018/11/16 すらら英単語 _ワード管理
				if($is_test){
					$INSERT_DATA['test_type_num'] = $test_type_num;
					$INSERT_DATA['course_num'] = null;
				}
				//add end   kimura 2018/11/16 すらら英単語 _ワード管理

				$INSERT_DATA['word_type']				= $LINE['word_type'];
				$INSERT_DATA['english']					= $LINE['english'];
				$INSERT_DATA['japanese_translation']	= $LINE['japanese_translation'];
				$INSERT_DATA['part_of_speech_type']		= $LINE['part_of_speech_type'];
				$INSERT_DATA['voice_data']				= $LINE['voice_data'];
				$INSERT_DATA['example_sentence']		= $LINE['example_sentence'];
				$INSERT_DATA['example_sentence_jp']		= $LINE['example_sentence_jp'];
				$INSERT_DATA['example_sentence_voice']	= $LINE['example_sentence_voice'];
//				$INSERT_DATA['surara_problem_num']		= $LINE['surara_problem_num'];

				$INSERT_DATA['display']					= $LINE['display'];
				$INSERT_DATA['mk_flg']					= $LINE['mk_flg'];

				if ($LINE['mk_flg'] == "0") {
					$INSERT_DATA['upd_syr_id']		= "update";
					$INSERT_DATA['upd_date']		= "now()";
					$INSERT_DATA['upd_tts_id']		= $_SESSION['myid']['id'];
					//update start kimura 2018/11/16 すらら英単語 _ワード管理
					// $INSERT_ERROR = update_word_problem($LINE['english_word_num'], $LINE['surara_problem_num']);
					$INSERT_ERROR = update_word_problem($LINE['english_word_num'], $LINE['surara_problem_num'], $LINE['word_test_problem_num']);
					//update end   kimura 2018/11/16 すらら英単語 _ワード管理
					if(count($INSERT_ERROR) > 0) {
						foreach ($INSERT_ERROR AS $VAL) {
							$ERROR[] = $VAL;
						}
					}
				} else {
					$INSERT_DATA['display']			= "2";
					$INSERT_DATA['mk_flg']			= "1";
					$INSERT_DATA['mk_tts_id']		= $_SESSION['myid']['id'];
					$INSERT_DATA['mk_date']			= "now()";
					$INSERT_DATA['upd_syr_id']   = "del";									// 2012/11/05 add oda
					$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];					// 2012/11/05 add oda
					$INSERT_DATA['upd_date']   = "now()";									// 2012/11/05 add oda
					$INSERT_ERROR = delete_word_problem($LINE['english_word_num']);
					if(count($INSERT_ERROR) > 0) {
						foreach ($INSERT_ERROR AS $VAL) {
							$ERROR[] = $VAL;
						}
					}
				}
				$where = " WHERE english_word_num = '".$LINE['english_word_num']."' LIMIT 1;";

				$INSERT_ERROR = $cdb->update(T_ENGLISH_WORD,$INSERT_DATA,$where);
				if(count($INSERT_ERROR) > 0) {
					foreach ($INSERT_ERROR AS $VAL) {
						$ERROR[] = $VAL;
					}
				}

			}

			$i++;
		}
	}

	// アップロードファイル削除
	if ($file_tmp_name && file_exists($file_tmp_name)) {
		unlink($file_tmp_name);
	}
	return $ERROR;
}


/**
 * 文字列変換
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @date 2012/09/27
 * @author Azet
 * @param string $word
 * @return string
 */
function replace_word($word) {
	// add oda
	$word = mb_convert_kana($word,"asKV","UTF-8");
	$word = ereg_replace("<>","&lt;&gt;",$word);
	$word = trim($word);
	$word = eregi_replace("\r","",$word);
	$word = eregi_replace("\n","<br>",$word);
	$word = replace_encode($word);

	return $word;
}

/**
 * 管理ディレクトリ作成
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @date 2012/09/28
 * @author Azet
 */
function create_directory() {
	// add oda

	// ワード管理イメージ保管ディレクトリ作成
	$material_img_path = MATERIAL_WORD_IMG_DIR;
	if (!file_exists($material_img_path)) {
		@mkdir($material_img_path,0777);
		@chmod($material_img_path,0777);
	}
	$material_img_path = MATERIAL_WORD_IMG_DIR. $_POST['course_num']."/";
	if (!file_exists($material_img_path)) {
		@mkdir($material_img_path,0777);
		@chmod($material_img_path,0777);
	}

	// ワード管理音声保管ディレクトリ作成
	$material_voice_path = MATERIAL_WORD_VOICE_DIR;
	if (!file_exists($material_voice_path)) {
		@mkdir($material_voice_path,0777);
		@chmod($material_voice_path,0777);
	}
	$material_voice_path = MATERIAL_WORD_VOICE_DIR. $_POST['course_num']."/";
	if (!file_exists($material_voice_path)) {
		@mkdir($material_voice_path,0777);
		@chmod($material_voice_path,0777);
	}
}


/**
 * ワード問題管理情報取得
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $english_word_num
 * @return string
 */
function get_word_problem_key($english_word_num) {

	$word_problem_key_string = "";
	$word_test_problem_key_string = ""; //add kimura 2018/11/16 すらら英単語 _ワード管理

	if (!$english_word_num) { return $word_problem_key_string; }

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql = "SELECT * FROM " . T_ENGLISH_WORD_PROBLEM .
			" WHERE english_word_num='".$english_word_num."' AND mk_flg='0';";
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			// if ($word_problem_key_string) { $word_problem_key_string .= "::"; } //del kimura 2018/11/16 すらら英単語 _ワード管理
			// $word_problem_key_string .= $list['problem_num']; //del kimura 2018/11/16 すらら英単語 _ワード管理
			//add start kimura 2018/11/16 すらら英単語 _ワード管理
			// 0: すらら(ドリル問題) 1: すらら(テスト問題)
			// if($list['problem_table_type'] == 1){									// del 2019/05/21 yoshizawa すらら英単語テストワード管理
			if($list['problem_table_type'] == 0 || $list['problem_table_type'] == 1){	// add 2019/05/21 yoshizawa すらら英単語テストワード管理
				if ($word_problem_key_string) { $word_problem_key_string .= "::"; }
				$word_problem_key_string .= $list['problem_num'];
			// 3: テスト
			}else if($list['problem_table_type'] == 3){
				if ($word_test_problem_key_string) { $word_test_problem_key_string .= "::"; }
				$word_test_problem_key_string .= $list['problem_num'];
			}
			//add end   kimura 2018/11/16 すらら英単語 _ワード管理
		}
	}

	// return $word_problem_key_string;
	return array($word_problem_key_string, $word_test_problem_key_string);
}


/**
 * ＤＢ変更処理（ワード問題管理テーブル）
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $english_word_num
 * @param integer $problem_num
 * @return array エラーの場合
 */
//update start kimura 2018/11/16 すらら英単語 _ワード管理
// function update_word_problem($english_word_num, $problem_num) {
function update_word_problem($english_word_num, $problem_num, $t_problem_num) {
//update end   kimura 2018/11/16 すらら英単語 _ワード管理

	$PROBLEM_LIST = array();

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql = "SELECT * FROM " . T_ENGLISH_WORD_PROBLEM .
			" WHERE english_word_num='".$english_word_num."';";		// 削除フラグは条件に入れない事
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			// $PROBLEM_LIST[] = $list['problem_num']; //del kimura 2018/11/16 すらら英単語 _ワード管理
			$PROBLEM_LIST[] = array('problem_num'=>$list['problem_num'], 'problem_table_type'=>$list['problem_table_type']); //add kimura 2018/11/16 すらら英単語 _ワード管理
		}
	}
	// if (count($PROBLEM_LIST) == 0 && $problem_num == "") { return $ERROR; } //del kimura 2018/11/20 すらら英単語 _ワード管理
	if (count($PROBLEM_LIST) == 0 && $problem_num == "" && $t_problem_num == "") { return $ERROR; } //add kimura 2018/11/20 すらら英単語 _ワード管理 //問題番号もテスト問題番号もなかったら処理停止

	// $STRING_PROBLEM_LIST = explode("::", $problem_num); //del kimura 2018/11/16 すらら英単語 _ワード管理
	$_STRING_PROBLEM_LIST = explode("::", $problem_num); //add kimura 2018/11/16 すらら英単語 _ワード管理
	$_STRING_TEST_PROBLEM_LIST = explode("::", $t_problem_num); //add kimura 2018/11/16 すらら英単語 _ワード管理

	// update start 2019/05/21 yoshizawa すらら英単語テストワード管理
	// //add start kimura 2018/11/16 すらら英単語 _ワード管理
	// foreach($_STRING_PROBLEM_LIST as $num){
	// 	$STRING_PROBLEM_LIST[] = array('problem_num'=>$num, 'problem_table_type'=>1);
	// }
	// foreach($_STRING_TEST_PROBLEM_LIST as $num){
	// 	$STRING_PROBLEM_LIST[] = array('problem_num'=>$num, 'problem_table_type'=>3);
	// }
	// //add end   kimura 2018/11/16 すらら英単語 _ワード管理
	// 
	// ドリルの単語ではproblem_table_typeが0になるように分岐します。
	if(MAIN == "practice"){
		foreach($_STRING_PROBLEM_LIST as $num){
			$STRING_PROBLEM_LIST[] = array('problem_num'=>$num, 'problem_table_type'=>0);
		}
	} else if(MAIN == "test_practice"){
		foreach($_STRING_PROBLEM_LIST as $num){
			$STRING_PROBLEM_LIST[] = array('problem_num'=>$num, 'problem_table_type'=>1);
		}
		foreach($_STRING_TEST_PROBLEM_LIST as $num){
			$STRING_PROBLEM_LIST[] = array('problem_num'=>$num, 'problem_table_type'=>3);
		}
	}
	// update end 2019/05/21 yoshizawa すらら英単語テストワード管理

	$ADD_LIST = array();
	$UPDATE_LIST = array();
	$DELETE_LIST = array();
	foreach ($PROBLEM_LIST as $db_key => $db_val) {
		foreach ($STRING_PROBLEM_LIST as $string_key => $string_val) {
			// if ($db_val == $string_val) { //del kimura 2018/11/16 すらら英単語 _ワード管理
			if ($db_val['problem_num'] == $string_val['problem_num'] && $db_val['problem_table_type'] == $string_val['problem_table_type']) { //add kimura 2018/11/16 すらら英単語 _ワード管理
				// $UPDATE_LIST[] = $string_val; //del kimura 2018/11/16 すらら英単語 _ワード管理
				$UPDATE_LIST[] = array('problem_num'=>$string_val['problem_num'], 'problem_table_type'=>$string_val['problem_table_type']); //add kimura 2018/11/16 すらら英単語 _ワード管理
				unset($PROBLEM_LIST[$db_key]);
				unset($STRING_PROBLEM_LIST[$string_key]);
			}
		}
	}

	foreach ($PROBLEM_LIST as $db_key => $db_val) {
		// if (trim($db_val) != "") { //del kimura 2018/11/16 すらら英単語 _ワード管理
			// $DELETE_LIST[] = $db_val; //del kimura 2018/11/16 すらら英単語 _ワード管理
		if (trim($db_val['problem_num']) != "") { //add kimura 2018/11/16 すらら英単語 _ワード管理
			$DELETE_LIST[] = array('problem_num'=>$db_val['problem_num'], 'problem_table_type'=>$db_val['problem_table_type']); //add kimura 2018/11/16 すらら英単語 _ワード管理
		}
	}

	foreach ($STRING_PROBLEM_LIST as $string_key => $string_val) {
		// if (trim($string_val) != "") { //del kimura 2018/11/16 すらら英単語 _ワード管理
		// 	$ADD_LIST[] = $string_val; //del kimura 2018/11/16 すらら英単語 _ワード管理
		if (trim($string_val['problem_num']) != "") { //add kimura 2018/11/16 すらら英単語 _ワード管理
			$ADD_LIST[] = array('problem_num'=>$string_val['problem_num'], 'problem_table_type'=>$string_val['problem_table_type']); //add kimura 2018/11/16 すらら英単語 _ワード管理
		}
	}

//echo "--------------------------------<br>";
//echo "add    count = ".count($ADD_LIST)."<br>";
//echo "update count = ".count($UPDATE_LIST)."<br>";
//echo "delete count = ".count($DELETE_LIST)."<br>";
//echo "db     count = ".count($PROBLEM_LIST)."<br>";
//echo "string count = ".count($STRING_PROBLEM_LIST)."<br>";

	// >>> add 2019/02/08 yoshizawa すらら英単語
	// すらら英単語テストの場合には教科がないため、course_numは入りません。
	list($write_type, $is_test, $test_type_num, $service_num) = get_english_word_info($_POST['course_num']);
	$course_num = "";
	if($is_test){
		$course_num = null;
	} else {
		$course_num = $_POST['course_num'];
	}
	// <<<

	// 追加処理
	if (count($ADD_LIST) > 0) {
		foreach ($ADD_LIST as $key => $val) {
			$INSERT_DATA['english_word_num']	= $english_word_num;
			// $INSERT_DATA['problem_num']			= $val;
			$INSERT_DATA['problem_num'] = $val['problem_num']; //add kimura 2018/11/16 すらら英単語 _ワード管理
			$INSERT_DATA['problem_table_type'] = $val['problem_table_type']; //add kimura 2018/11/16 すらら英単語 _ワード管理
			// $INSERT_DATA['course_num']			= $_POST['course_num'];	//	add ookawara 2012/12/05	// del 2019/02/08 yoshizawa すらら英単語
			$INSERT_DATA['course_num']			= $course_num;	// add 2019/02/08 yoshizawa すらら英単語
			$INSERT_DATA['mk_flg']				= "0";
			$INSERT_DATA['mk_tts_id']			= "";
			$INSERT_DATA['mk_date']				= "0000-00-00";
			$INSERT_DATA['ins_syr_id']			= "add";
			$INSERT_DATA['ins_date']			= "now()";
			$INSERT_DATA['ins_tts_id']			= $_SESSION['myid']['id'];
			$INSERT_DATA['upd_syr_id']			= "add";
			$INSERT_DATA['upd_date']			= "now()";
			$INSERT_DATA['upd_tts_id']			= $_SESSION['myid']['id'];
			$ERROR = $cdb->insert(T_ENGLISH_WORD_PROBLEM,$INSERT_DATA);
		}
	}
	
	unset($INSERT_DATA); // add yamaguchi 2018/10/17 更新時すらら問題番号が更新削除レコードにも上書きされる不具合修正
	
	// 更新処理
	if (count($UPDATE_LIST) > 0) {
		foreach ($UPDATE_LIST as $key => $val) {
			$INSERT_DATA['mk_flg']				= "0";
			$INSERT_DATA['mk_tts_id']			= "";
			$INSERT_DATA['mk_date']				= "0000-00-00";
			$INSERT_DATA['upd_syr_id']			= "update";
			$INSERT_DATA['upd_date']			= "now()";
			$INSERT_DATA['upd_tts_id']			= $_SESSION['myid']['id'];

			$where  = " WHERE english_word_num = '".$english_word_num."'";
			// $where .= "   AND problem_num = '".$val."' LIMIT 1;"; //del kimura 2018/11/16 すらら英単語 _ワード管理
			//add start kimura 2018/11/16 すらら英単語 _ワード管理
			$where .= " AND problem_num = '".$val['problem_num']."'";
			$where .= " AND problem_table_type ='".$val['problem_table_type']."'";
			$where .= " LIMIT 1;";
			//add end   kimura 2018/11/16 すらら英単語 _ワード管理

			$ERROR = $cdb->update(T_ENGLISH_WORD_PROBLEM,$INSERT_DATA,$where);
		}
	}

	unset($INSERT_DATA); // add yamaguchi 2018/10/17 更新時すらら問題番号が更新削除レコードにも上書きされる不具合修正

	// 削除処理
	if (count($DELETE_LIST) > 0) {
		foreach ($DELETE_LIST as $key => $val) {
			$INSERT_DATA['mk_flg']		= "1";
			$INSERT_DATA['mk_tts_id']	= $_SESSION['myid']['id'];
			$INSERT_DATA['mk_date']		= "now()";
			$INSERT_DATA['upd_syr_id']   = "del";									// 2012/11/05 add oda
			$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];					// 2012/11/05 add oda
			$INSERT_DATA['upd_date']   = "now()";									// 2012/11/05 add oda

			$where  = " WHERE english_word_num = '".$english_word_num."'";
			// $where .= "   AND problem_num = '".$val."' LIMIT 1;"; //del kimura 2018/11/16 すらら英単語 _ワード管理
			//add start kimura 2018/11/16 すらら英単語 _ワード管理
			$where .= " AND problem_num = '".$val['problem_num']."'";
			$where .= " AND problem_table_type ='".$val['problem_table_type']."'";
			$where .= " LIMIT 1;";
			//add end   kimura 2018/11/16 すらら英単語 _ワード管理

			$ERROR = $cdb->update(T_ENGLISH_WORD_PROBLEM,$INSERT_DATA,$where);
		}
	}

	if (!$ERROR) {
		$_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>";
	}
	return $ERROR;
}


/**
 * ＤＢ変更処理（ワード問題管理テーブル）
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $english_word_num
 * @return array エラーの場合
 */
function delete_word_problem($english_word_num) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// 削除処理
	if ($english_word_num) {
		$INSERT_DATA['mk_flg']		= "1";
		$INSERT_DATA['mk_tts_id']	= $_SESSION['myid']['id'];
		$INSERT_DATA['mk_date']		= "now()";
		$INSERT_DATA['upd_syr_id']   = "del";									// 2012/11/05 add oda
		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];					// 2012/11/05 add oda
		$INSERT_DATA['upd_date']   = "now()";									// 2012/11/05 add oda

		$where  = " WHERE english_word_num = '".$english_word_num."';";

		$ERROR = $cdb->update(T_ENGLISH_WORD_PROBLEM,$INSERT_DATA,$where);
	}

	if (!$ERROR) {
		$_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>";
	}
	return $ERROR;
}


//add start kimura 2018/10/19 漢字学習コンテンツ_書写ドリル対応
/**
 * filter_word_type 
 * 
 * @param  integer $write_type  教科
 * @param  array   $L_WORD_TYPE ワード区分配列
 * @return array   絞り込んだ配列
 */
function filter_word_type($write_type, $L_WORD_TYPE){

	$word_types = $L_WORD_TYPE;

	switch($write_type){
	case 1:
		unset($word_types[4]); //読み
		unset($word_types[5]); //書き
		break;
	case 2:
		unset($word_types[1]); //単語
		unset($word_types[2]); //熟語
		unset($word_types[3]); //単語+熟語
		break;
	}
	return $word_types;
}
//add end   kimura 2018/10/19 漢字学習コンテンツ_書写ドリル対応
//add start kimura 2018/11/27 すらら英単語 _admin
/**
 * ワード管理番号とコース番号の連想配列を得る
 * @return array $word_course_assoc [ ワード管理番号 => コース番号, ワード管理番号 => テスト種類番号T,  ワード管理番号 => コース番号,  ... ]
 */
function get_word_course_assoc($ignore_course_key){
	$cdb = $GLOBALS['cdb'];
	//有効なワードを全て取得 //★ワードがn個あれば配列要素のメモリn個分使います
	$sql = "SELECT english_word_num, course_num, test_type_num";
	$sql.= " FROM ".T_ENGLISH_WORD;
	$sql.= " WHERE 1";
	$sql.= "  AND mk_flg = '0'";
	if($ignore_course_key !== ""){
		//コース番号で除外
		if(strpos($ignore_course_key, "T") === false){ //...テストはワード管理内で接尾辞Tをつけて取り扱っているのでそれで判断
			$sql.= "  AND course_num != '".$ignore_course_key."'";
		//テスト種類管理番号で除外
		}else{
			$sql.= "  AND test_type_num != '".rtrim($ignore_course_key, "T")."'";
		}
	}
	$word_course_assoc = array();

	if($result = $cdb->query($sql)){
		while($list = $cdb->fetch_assoc($result)){
			$course_key = $list['course_num'];
			if($list['test_type_num'] > 0){ $course_key = $list['test_type_num']."T"; } //テストは接尾辞Tをつける
			$word_course_assoc[$list['english_word_num']] = $course_key;
		}
	}
	return $word_course_assoc;
}
//add end   kimura 2018/11/27 すらら英単語 _admin
?>
