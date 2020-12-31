<?
/**
 * ベンチャー・リンク　すらら
 *
 * 旺文社・　ＮＧワード管理
 *
 * 履歴
 * 2012/05/28 初期設定
 *
 * @author Azet
 */

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

	// 処理アクションにより、フローを制御
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
			if (!$ERROR) { $html .= ng_word_list($ERROR); }
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
			if (!$ERROR) { $html .= ng_word_list($ERROR); }
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
			if (!$ERROR) { $html .= ng_word_list($ERROR); }
			else { $html .= viewform($ERROR); }
		} else {
			$html .= check_html();
		}
	// 一覧表示
	} else {
		$html .= ng_word_list($ERROR);
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
function ng_word_list($ERROR) {

	// グローバル変数
	global $L_PAGE_VIEW;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];


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
		$html .= "<br>\n";
		$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"add\">\n";
		$html .= "<input type=\"submit\" value=\"ＮＧワード新規登録\">\n";
		$html .= "</form>\n";
	}
	// 表示ページ制御
	$s_page_view_html .= "&nbsp;&nbsp;&nbsp;表示数<select name=\"s_page_view\">\n";
	foreach ($L_PAGE_VIEW as $key => $val){
		if ($_SESSION['sub_session']['s_page_view'] == $key) { $sel = " selected"; } else { $sel = ""; }
		$s_page_view_html .= "<option value=\"".$key."\"".$sel.">".$val."</option>\n";
	}
	$s_page_view_html .= "</select><input type=\"submit\" value=\"Set\">\n";

	// ＮＧワード取得SQL作成
	$sql  = "SELECT ng_word_num,ng_word_name FROM " . T_NG_WORD .
			//update start kimura 2017/12/19 AWS移設 ソートなし → ORDER BY句追加
			//" WHERE mk_flg ='0' ";
			" WHERE mk_flg ='0' ".
			// upd start hasegawa 2017/12/27 AWS移設 ソート条件追加
			// " ORDER BY ng_word_num".
			// " ;";
			" ORDER BY ng_word_num";
			// upd end hasegawa 2017/12/27
			//update end   kimura 2017/12/19

	// ＮＧワード件数取得
	$ng_word_count = 0;
	if ($result = $cdb->query($sql)) {
		$ng_word_count = $cdb->num_rows($result);
	}
	// ページビュー判断
	if ($L_PAGE_VIEW[$_SESSION['sub_session']['s_page_view']]) {
		$page_view = $L_PAGE_VIEW[$_SESSION['sub_session']['s_page_view']];
	} else {
		$page_view = $L_PAGE_VIEW[0];
	}

	// 最大ページ数算出
	$max_page = ceil($ng_word_count/$page_view);

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
		$ng_word_check_count = 0;
		if ($result = $cdb->query($sql)) {
			$ng_word_check_count = $cdb->num_rows($result);
		}
		if (!$ng_word_check_count) {
			$html .= "現在、登録ＮＧワードは存在しません。";
			return $html;
		}

		// 一覧表示開始
		$html .= "<br>\n";
		$html .= "修正する場合は、修正するＮＧワードの詳細ボタンを押してください。<br>\n";

		// 登録件数表示
		$html .= "<br>\n";
		$html .= "<div style=\"float:left;\">ＮＧワード総数(".$ng_word_count."):PAGE[".$page."/".$max_page."]</div>\n";

		// 前ページボタン表示制御
		if ($back > 0) {
			$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" style=\"float:left;\">";
			$html .= "<input type=\"submit\" value=\"前のページ\">";
			$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
			$html .= "<input type=\"hidden\" name=\"s_page\" value=\"".$back."\">\n";
			$html .= "</form>";
		}

		// 次ページボタン表示制御
		if ($page < $max_page) {
			$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" style=\"float:left;\">";
			$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
			$html .= "<input type=\"hidden\" name=\"s_page\" value=\"".$next."\">\n";
			$html .= "<input type=\"submit\" value=\"次のページ\">";
			$html .= "</form>";
		}

		$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" style=\"float:left;\">";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
		$html .= "<input type=\"hidden\" name=\"s_page\" value=\"1\">\n";
		$html .= $s_page_view_html;
		$html .= "</form><br><br>\n";
		$html .= "<table class=\"ng_word_form\">\n";
		$html .= "<tr class=\"ng_word_form_menu\">\n";

		// リストタイトル表示
		$html .= "<th>登録番号</th>\n";
		$html .= "<th>ＮＧワード名</th>\n";

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

			$html .= "<tr class=\"ng_word_form_cell\">\n";
			$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"ng_word_num\" value=\"{$list['ng_word_num']}\">\n";

			// 明細表示
			$html .= "<td>{$list['ng_word_num']}</td>\n";
			$html .= "<td>{$list['ng_word_name']}</td>\n";

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
	return $html;
}


/**
 *  新規登録フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function addform($ERROR) {

	$html .= "<br>\n";
	$html .= "新規登録フォーム<br>\n";

	// エラーメッセージ表示
	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	// 入力画面表示
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";

	// htmlクラス生成
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(NG_WORD_FORM);

	//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	$INPUTS[NGWORDNUM] = array('result'=>'plane','value'=>"---");
	$INPUTS[NGWORDNAME] = array('type'=>'text','name'=>'ng_word_name','value'=>$_POST['ng_word_name']);

	$make_html->set_rep_cmd($INPUTS);
	$html .= $make_html->replace();

	// 制御ボタン定義
	$html .= "<input type=\"submit\" value=\"追加確認\">\n";
	$html .= "<input type=\"reset\" value=\"クリア\">\n";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"ng_word_list\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";
	return $html;
}


/**
 *  詳細情報表示
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function viewform($ERROR) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	if ($action) {
		foreach ($_POST as $key => $val) { $$key = $val; }
	} else {
		$sql = "SELECT * FROM " . T_NG_WORD .
			" WHERE ng_word_num='".$_POST['ng_word_num']."' AND mk_flg!='1' LIMIT 1;";
		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);
		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}
		foreach ($list as $key => $val) {
			$$key = replace_decode($val);
		}
	}

	// 画面表示
	$html = "<br>\n";
	$html .= "詳細画面<br>\n";

	// エラーメッセージ表示
	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	// 入力画面表示
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";
	$html .= "<input type=\"hidden\" name=\"ng_word_num\" value=\"$ng_word_num\">\n";

	// htmlクラス生成
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(NG_WORD_FORM);

	//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	if (!$ng_word_num) { $ng_word_num = "---"; }
	$INPUTS[NGWORDNUM] = array('result'=>'plane','value'=>$ng_word_num);
	$INPUTS[NGWORDNAME] = array('type'=>'text','name'=>'ng_word_name','value'=>$ng_word_name);

	$make_html->set_rep_cmd($INPUTS);
	$html .= $make_html->replace();

	// 制御ボタン定義
	$html .= "<input type=\"submit\" value=\"変更確認\">";
	$html .= "<input type=\"reset\" value=\"クリア\">";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"ng_word_list\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
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

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// 未入力チェック
	if (!$_POST['ng_word_name']) {
		$ERROR[] = "ＮＧワード名が未入力です。";

	// 重複チェック
	} else {
		if (MODE == "add") {
			$sql  = "SELECT * FROM " .T_NG_WORD.
					" WHERE mk_flg!='1' AND ng_word_name = '".$_POST[ng_word_name]."'";
		} else {
			$sql  = "SELECT * FROM " .T_NG_WORD.
					" WHERE mk_flg != '1' AND ng_word_num != '".$_POST[ng_word_num]."' AND ng_word_name = '".$_POST[ng_word_name]."'";
		}
		// SQL実行
		if ($result = $cdb->query($sql)) {
			$count = $cdb->num_rows($result);
		}
		if ($count > 0) { $ERROR[] = "入力されたＮＧワード名は既に登録されております。"; }
	}

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
			$HIDDEN .= "<input type=\"hidden\" name=\"$key\" value=\"$val\">\n";
		}
	}

	// 他の人が削除したかチェック
	if (ACTION) {
		foreach ($_POST as $key => $val) { $$key = $val; }
	} else {
		$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"change\">\n";
		$sql = "SELECT * FROM ".T_NG_WORD.
			" WHERE ng_word_num='".$_POST['ng_word_num']."' AND mk_flg!='1' LIMIT 1;";
		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);
		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}
		foreach ($list as $key => $val) {
			$$key = replace_decode($val);
		}
	}

	// ボタン表示文言判定
	if (MODE != "削除") { $button = "登録"; } else { $button = "削除"; }

	// 入力確認画面表示
	$html = "<br>\n";
	$html .= "確認画面：以下の内容で".$button."してもよろしければ".$button."ボタンをクリックしてください。<br>\n";

	// htmlクラス生成
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(NG_WORD_FORM);

	//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	if (!$ng_word_num) { $ng_word_num = "---"; }
	$INPUTS[NGWORDNUM] = array('result'=>'plane','value'=>$ng_word_num);
	$INPUTS[NGWORDNAME] = array('result'=>'plane','value'=>$ng_word_name);

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
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"ng_word_list\">\n";
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

	// 登録項目設定
	foreach ($_POST as $key => $val) {
		if ($key == "action") { continue; }
		$INSERT_DATA[$key] = "$val";
	}
	$INSERT_DATA[ins_date] = "now()";
	$INSERT_DATA[ins_tts_id] 		= $_SESSION['myid']['id'];
	$INSERT_DATA[upd_date] = "now()";
	$INSERT_DATA[upd_tts_id] 		= $_SESSION['myid']['id'];

	// ＤＢ追加処理
	$ERROR = $cdb->insert(T_NG_WORD,$INSERT_DATA);

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
		$INSERT_DATA[ng_word_name] = $_POST['ng_word_name'];
		$INSERT_DATA[upd_date] = "now()";
		$INSERT_DATA[upd_tts_id] = $_SESSION['myid']['id'];

		$where = " WHERE ng_word_num='$_POST[ng_word_num]' LIMIT 1;";

		$ERROR = $cdb->update(T_NG_WORD,$INSERT_DATA,$where);

	// 削除処理
	} elseif (MODE == "削除") {
		$INSERT_DATA[mk_flg] = 1;
		$INSERT_DATA[mk_tts_id] = $_SESSION['myid']['id'];
		$INSERT_DATA[mk_date] = "now()";
		$INSERT_DATA[upd_date] = "now()";						//	add ookawara 2012/11/05
		$INSERT_DATA[upd_tts_id] = $_SESSION['myid']['id'];		//	add ookawara 2012/11/05
		$where = " WHERE ng_word_num='".$_POST['ng_word_num']."' LIMIT 1;";

		$ERROR = $cdb->update(T_NG_WORD,$INSERT_DATA,$where);

	}

	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
	return $ERROR;
}


/**
 * サブセションをPOSTから設定
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function sub_session() {

	// ページ数をPOSTから取得し、セションに格納
	if (strlen($_POST['s_page_view'])) {
		$_SESSION['sub_session']['s_page_view'] = $_POST['s_page_view'];
	}

	// 現在ページをPOSTから取得し、セションに格納
	if (strlen($_POST['s_page'])) {
		$_SESSION['sub_session']['s_page'] = $_POST['s_page'];
	}

	return;
}
?>
