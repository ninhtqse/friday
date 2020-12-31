<?php
/**
 * すらら
 *
 * ゲーミフィケーション キャラクター管理
 *
 * 履歴
 * 2019/03/07 make file hasegawa 生徒TOP改修
 *
 * @author Azet
 */


/**
 * HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function start() {

	// サブセション取得
	$ERROR = sub_session();
        
		// チェック処理
		if (ACTION == "check") {
			$ERROR = check($ERROR);
		}

		// DB登録・修正・削除
		if (!$ERROR) {
			if (ACTION == "add") {
				$ERROR = add();
			} elseif (ACTION == "change" || ACTION == "del") {
				$ERROR = change();
			}
		}
		// 登録処理
		if (MODE == "add") {
			if (ACTION == "check") {
				if (!$ERROR) {
					$html .= check_html();		 // 確認画面
				} else {
					$html .= addform($ERROR); 	 // 新規登録画面
				}
			} elseif (ACTION == "add") {
				if (!$ERROR) {
					$html .= character_list($ERROR); // 一覧表示
				} else {
					$html .= addform($ERROR);  	 // 新規登録画面
				}
			} else {
				$html .= addform($ERROR);  		 // 新規登録画面
			}
		// 詳細画面遷移
		} elseif (MODE == "詳細") {
			if (ACTION == "check") {
				if (!$ERROR) {
					$html .= check_html();		 // 確認画面
				} else {
					$html .= viewform($ERROR); 	 // 詳細画面
				}
			} elseif (ACTION == "change") {
				if (!$ERROR) {
					$html .= character_list($ERROR); // 一覧表示
				} else {
					$html .= viewform($ERROR);	 // 詳細画面
				}
			} else {
				$html .= viewform($ERROR); 		 // 詳細画面
			}
		// 削除処理
		} elseif (MODE == "削除") {
                    if (ACTION == "check") {
				if (!$ERROR) {
					$html .= check_html(); 		// 確認画面
				}
				else {
					$html .= viewform($ERROR);  // 詳細画面
				}
			} elseif (ACTION == "change") {
				if (!$ERROR) {
					$html .= character_list($ERROR);// 一覧表示
				}
				else {
					$html .= viewform($ERROR); 	// 詳細画面
				}
			} else {
				$html .= check_html();			// 確認画面
			}
                } elseif (MODE == "gamification_character_import") {
                        $ERROR = gamification_character_import();
			$html .= character_list($ERROR);
		// 一覧表示
		} else {
			$html .= character_list($ERROR);
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
function character_list($ERROR) {
	// グローバル変数
	global $L_PAGE_VIEW,$L_DISPLAY, $L_GAM_CHARACTER_TYPE,$L_GAM_CHARACTER_OPEN;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

        // 権限取得
	if ($authority) { $L_AUTHORITY = explode("::",$authority); }

        $html .= "<h3>ゲーミフィケーション キャラクターマスタ</h3>\n";

	// エラーメッセージが存在する場合、メッセージ表示
	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
        } elseif (count($ERROR) == 0 && MODE == 'gamification_character_import') {
                $html .= "<b>データを登録しました</b>\n";
        }

	if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__add",$_SESSION['authority'])===FALSE)) {
		$html .= "インポートする場合はゲーミフィケーションキャラクターtxtファイル(S-JIS)を指定しCSVインポートを押してください<br>";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" enctype=\"multipart/form-data\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"gamification_character_import\">\n";
		$html .= "<input type=\"file\" size=\"40\" name=\"gamification_character_file\">\n";
		$html .= "<input type=\"submit\" value=\"CSVインポート\">\n";
		$html .= "</form>\n";
		$html .= "<br />\n";
		$html .= "<form action=\"/admin/gamification_character_make_csv.php\" method=\"POST\">";
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
		$html .= "<input type=\"submit\" value=\"CSVエクスポート\">";
		$html .= "</form>";
		$html .= "<br />\n";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"add\">\n";
		$html .= "<input type=\"submit\" value=\"キャラクターマスタ新規登録\">\n";
		$html .= "</form>\n";
		$html .= "<br />\n";
	}
        
        $ftp_path = FTP_URL.'gamification/character/';
        $html .= FTP_EXPLORER_MESSAGE;
        $html .= "<a href=\"".$ftp_path."\" target=\"_blank\">ゲーミフィケーションキャラクターフォルダー($ftp_path)</a><br>\n";
        $html .= "<a href=\"#\" class=\"gamification_notes_open\">画像命名規約</a><br>\n";
        $html .= "<div style=\"font-size:14px; display:none;\" class=\"gamification_notes\">";
        $html .= "<p>画像の登録にはキャラクターのレベル登録を行ったうえで下記命名規約に沿ってご登録下さい。</p>\n";
        $html .= "<b>画像格納先:/data/home/contents/www/material/gamification/character/common/キャラクターID/img/</b>\n";
        $html .= "<table>\n";
        $html .= "<tr><th colspan=\"2\">【命名規約】</th></tr>\n";
        $html .= "<tr><td>デフォルト（キャラクター選択画面で使用）</td><td>キャラクターID_カラーID.png</td></tr>\n";
        $html .= "<tr><td>部屋にいるときの画像</td><td>キャラクターID_カラーID_レベル.png</td></tr>\n";
        $html .= "<tr><td>食べるモーション画像</td><td>キャラクターID_カラーID_レベル_eat.gif</td></tr>\n";
        $html .= "<tr><td>トレーニングモーション画像</td><td>キャラクターID_カラーID_レベル_training.gif</td></tr>\n";
        $html .= "<tr><td>復活モーション画像</td><td>キャラクターID_カラーID_レベル_revival.gif</td></tr>\n";
        $html .= "</table><br>\n";
        $html .= "<b>音声格納先フォルダ:/data/home/contents/www/material/gamification/character/common/キャラクターID/sound/</b>\n";
        $html .= "<table>\n";
        $html .= "<tr><th colspan=\"2\">【命名規約】</th></tr>\n";
        $html .= "<tr><td>キャラクタークリック時の音声</td><td>キャラクターID_click.mp3</td></tr>\n";
        $html .= "</table>\n";
        $html .= "\n";
        $html .= "<a href=\"#\" class=\"gamification_notes_close\">閉じる</a>\n";
        $html .= "</div>";
        $html .= "<script>";
        $html .= "$('.gamification_notes_open').click(function() {";
        $html .= "$('.gamification_notes').show();";
        $html .= "});";
        $html .= "$('.gamification_notes_close').click(function() {";
        $html .= "$('.gamification_notes').hide();";
        $html .= "});";
        $html .= "</script>";
        

        // 表示ページ制御
	$s_page_view_html .= "&nbsp;&nbsp;&nbsp;表示数<select name=\"s_page_view\">\n";
	foreach ($L_PAGE_VIEW as $key => $val){
		if ($_SESSION['sub_session']['s_page_view'] == $key) { $sel = " selected"; } else { $sel = ""; }
		$s_page_view_html .= "<option value=\"".$key."\"".$sel.">".$val."</option>\n";
	}
	$s_page_view_html .= "</select><input type=\"submit\" value=\"Set\">\n";

	// 一覧取得
	$sql  = "SELECT * FROM " . T_GAMIFICATION_CHARACTER
                ." WHERE mk_flg ='0' "
                ." ORDER BY list_num";

	// イメージ件数取得
	$count = 0;
	if ($result = $cdb->query($sql)) {
		$count = $cdb->num_rows($result);
	}

	// ページビュー判断
	if ($L_PAGE_VIEW[$_SESSION['sub_session']['s_page_view']]) {
		$page_view = $L_PAGE_VIEW[$_SESSION['sub_session']['s_page_view']];
	} else {
		$page_view = $L_PAGE_VIEW[0];
	}

	// 最大ページ数算出
	$max_page = ceil($count/$page_view);

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
		$check_count = 0;
		if ($result = $cdb->query($sql)) {
			$check_count = $cdb->num_rows($result);
		}
		if (!$check_count) {
			$html .= "現在、データは登録されていません。";
			return $html;
		}

		// 一覧表示開始
		$html .= "<br>修正する場合は、修正するキャラクターの詳細ボタンを押してください。<br>\n";

		// 登録件数表示
		$html .= "<br>\n";
		$html .= "<div style=\"float:left;\">総数(".$count."):PAGE[".$page."/".$max_page."]</div>\n";

		// 前ページボタン表示制御
		if ($back > 0) {
			$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" style=\"float:left;\">";
			$html .= "<input type=\"submit\" value=\"前のページ\">";
			$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"page\">\n";
			$html .= "<input type=\"hidden\" name=\"s_page\" value=\"".$back."\">\n";
			$html .= "</form>";
		}

		// 次ページボタン表示制御
		if ($page < $max_page) {
			$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" style=\"float:left;\">";
			$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"page\">\n";
			$html .= "<input type=\"hidden\" name=\"s_page\" value=\"".$next."\">\n";
			$html .= "<input type=\"submit\" value=\"次のページ\">";
			$html .= "</form>";
		}

		$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" style=\"float:left;\">";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
		$html .= "<input type=\"hidden\" name=\"s_page\" value=\"1\">\n";
		$html .= $s_page_view_html;
		$html .= "</form><br><br>\n";
		$html .= "<table class=\"secret_form\">\n";
		$html .= "<tr class=\"secret_form_menu\">\n";

		//--------------------
		// リストタイトル表示
		//--------------------
		$html .= "<th>キャラクターID</th>\n";
		$html .= "<th>キャラクター名</th>\n";
		$html .= "<th>キャラクタータイプ</th>\n";
		$html .= "<th>満腹度減少率</th>\n";
		$html .= "<th>画像登録</th>\n";
		$html .= "<th>表示・非表示</th>\n";

		// 詳細
		if (!ereg("practice__view",$authority)
			&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__view",$_SESSION['authority'])===FALSE))
		) {
			$html .= "<th>詳細</th>\n";
		}

		// 削除
		if (!ereg("practice__del",$authority)
			&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__del",$_SESSION['authority'])===FALSE))
		) {
			$html .= "<th>削除</th>\n";
		}
		$html .= "</tr>\n";

		//--------------
		// 明細表示
		//--------------
		$i = 1;
		while ($list = $cdb->fetch_assoc($result)) {

			// イメージ格納ディレクトリ作成
			create_directory($list['character_id']);
			$img_setting = "";
			foreach (glob(MATERIAL_GAM_CHARACTER_DIR.'common/'.$list['character_id'].'/img/'.$list['character_id'].'_*.*') as $val ) {
				$val = str_replace(MATERIAL_GAM_CHARACTER_DIR.'common/'.$list['character_id'].'/img/', '', $val);
				$img_setting .= $val.'<br>';
			}
                        
			// HTML作成
			$html .= "<tr class=\"secret_form_cell\">\n";
			$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"character_id\" value=\"".$list['character_id']."\">\n";
			$html .= "<td>".$list['character_id']."</td>\n";
			$html .= "<td>".$list['character_name']."</td>\n";
			$html .= "<td>".$L_GAM_CHARACTER_TYPE[$list['character_type']]."</td>\n";
			$html .= "<td>".$list['full_reduction_rate']."%</td>\n";
			if ($img_setting) {
				$html .= "<td>".$img_setting."</td>\n";
			} else {
				$html .= "<td style=\"color:red;text-align:center;\">未設定</td>\n";
			}
			$html .= "<td>".$L_DISPLAY[$list['display']]."</td>\n";

			// 詳細ボタン（権限が有る場合は、ボタン表示）
			if (!ereg("practice__view",$authority)
				&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__view",$_SESSION['authority'])===FALSE))
			) {
				$html .= "<td><input type=\"submit\" name=\"mode\" value=\"詳細\"></td>\n";
			}

			// 削除ボタン（権限が有る場合は、ボタン表示）
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
        } else {
            $html .= "<p>現在、データは登録されていません。</p>";
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
	global $L_DISPLAY, $L_GAM_CHARACTER_TYPE, $L_GAM_CHARACTER_OPEN;

	$html .= "<br>\n";
	$html .= "キャラクターマスタ 新規登録フォーム<br>\n";
	$html .= "<br>\n";


	// エラーメッセージ表示
	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	// 入力画面表示
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" enctype=\"multipart/form-data\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";

	// htmlクラス生成
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(GAMIFICATION_CHARACTER);

	$L_GAM_CHARACTER_OPEN[0] = '選択してください';
	ksort($L_GAM_CHARACTER_OPEN);
	// フォーム部品生成
	$INPUTS['CHARACTERID']	= array('result'=>'plane','value'=>"---");
	$INPUTS['CHARACTERNAME']	= array('result'=>'form', 'type' => 'text', 'name' => 'character_name', 'size' => '50', 'value'=>$_POST['character_name'] ?: null);
	$INPUTS['CHARACTERTYPE']	= array('result'=>'form', 'type' => 'select', 'name' => 'character_type', 'array'=>$L_GAM_CHARACTER_TYPE, 'check' => $_POST['character_type'] ?: null);
	$INPUTS['FULLREDUCATIONRATE']	= array('result'=>'form', 'type' => 'text', 'name' => 'full_reduction_rate', 'size' => '10', 'value'=>isset($_POST['full_reduction_rate']) ? $_POST['full_reduction_rate']: null);
	$INPUTS['DESCRIPTION']	= array('result'=>'form', 'type' => 'textarea', 'name' => 'description', 'style' => 'width:400px; height:60px;', 'value'=>$_POST['description'] ?: null);
	$INPUTS['ANNOTATION']	= array('result'=>'form', 'type' => 'textarea', 'name' => 'annotation', 'style' => 'width:400px; height:60px;', 'value'=>$_POST['annotation'] ?: null); //add kimura 2019/06/03 生徒画面TOP改修
	$INPUTS['ANNOTATIONLOCKED']	= array('result'=>'form', 'type' => 'textarea', 'name' => 'annotation_locked', 'style' => 'width:400px; height:60px;', 'value'=>$_POST['annotation_locked'] ?: null); //add kimura 2019/06/03 生徒画面TOP改修
	$INPUTS['OPENINGCONDITION']	= array('result'=>'form', 'type' => 'select', 'name' => 'opening_condition', 'array'=>$L_GAM_CHARACTER_OPEN, 'check' => $_POST['opening_condition'] ?: null);
	$INPUTS['LISTNUM']	= array('result'=>'form', 'type' => 'text', 'name' => 'list_num', 'size' => '10', 'value'=>isset($_POST['list_num']) ? $_POST['list_num']: null);

	// 表示・非表示 ラジオボタン生成
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("display");
	$newform->set_form_id("display");
	$newform->set_form_check($_POST['display']);
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
	$INPUTS['DISPLAY'] = array('result'=>'plane','value'=>$display_value);
	$INPUTS['USERBKO']	= array('result'=>'form', 'type' => 'text', 'name' => 'usr_bko', 'size' => '50', 'value'=>$_POST['usr_bko'] ?: null);

	$make_html->set_rep_cmd($INPUTS);
	$html .= $make_html->replace();

	// 制御ボタン定義
	$html .= "<input type=\"submit\" value=\"追加確認\">\n";
	$html .= "<input type=\"reset\" value=\"クリア\">\n";
	$html .= "</form>\n";
	$html .= "<br>\n";

	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"character_list\">\n";
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
	global $L_DISPLAY, $L_GAM_CHARACTER_TYPE, $L_GAM_CHARACTER_OPEN;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// POST値取得
	if (ACTION=="check" || ACTION=="back") {
		// POST値取得
		foreach ($_POST as $key => $val) {
			$$key = $val;
		}
	} else {
		$sql = "SELECT * FROM " . T_GAMIFICATION_CHARACTER .
			" WHERE character_id ='".$cdb->real_escape($_POST['character_id'])."' AND mk_flg='0' LIMIT 1;";
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
	$html .= "<br>\n";
	$html .= "詳細画面<br>\n";
	$html .= "<br>\n";
	// エラーメッセージ表示
	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}
	// 入力画面表示
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"詳細\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";
	$html .= "<input type=\"hidden\" name=\"character_id\" value=\"".$character_id."\">\n";
        
	// htmlクラス生成
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(GAMIFICATION_CHARACTER);
        
        $L_GAM_CHARACTER_OPEN[0] = '選択してください';
        ksort($L_GAM_CHARACTER_OPEN);
	// フォーム部品生成
	$INPUTS['CHARACTERID']	= array('result'=>'plane','value'=>$character_id ?: '---');
	$INPUTS['CHARACTERNAME']	= array('result'=>'form', 'type' => 'text', 'name' => 'character_name', 'size' => '50', 'value'=>$character_name ?: null);
	$INPUTS['CHARACTERTYPE']	= array('result'=>'form', 'type' => 'select', 'name' => 'character_type', 'array'=>$L_GAM_CHARACTER_TYPE, 'check' => $character_type ?: null);
	$INPUTS['FULLREDUCATIONRATE']	= array('result'=>'form', 'type' => 'text', 'name' => 'full_reduction_rate', 'size' => '10', 'value'=>isset($full_reduction_rate) ? $full_reduction_rate : null);
	$INPUTS['DESCRIPTION']	= array('result'=>'form', 'type' => 'textarea', 'name' => 'description', 'style' => 'width:400px; height:60px;', 'value'=>$description ?: null);
	$INPUTS['ANNOTATION']	= array('result'=>'form', 'type' => 'textarea', 'name' => 'annotation', 'style' => 'width:400px; height:60px;', 'value'=>$annotation ?: null); //add kimura 2019/06/03 生徒画面TOP改修
	$INPUTS['ANNOTATIONLOCKED']	= array('result'=>'form', 'type' => 'textarea', 'name' => 'annotation_locked', 'style' => 'width:400px; height:60px;', 'value'=>$annotation_locked ?: null); //add kimura 2019/06/03 生徒画面TOP改修
	$INPUTS['OPENINGCONDITION']	= array('result'=>'form', 'type' => 'select', 'name' => 'opening_condition', 'array'=>$L_GAM_CHARACTER_OPEN, 'check' => $opening_condition ?: null);
	$INPUTS['LISTNUM']	= array('result'=>'form', 'type' => 'text', 'name' => 'list_num', 'size' => '10', 'value'=>isset($list_num) ? $list_num : null);

	// 表示・非表示 ラジオボタン生成
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
	$INPUTS['DISPLAY'] = array('result'=>'plane','value'=>$display_value);
	$INPUTS['USERBKO']	= array('result'=>'form', 'type' => 'text', 'name' => 'usr_bko', 'size' => '50', 'value'=>$usr_bko ?: null);
        
	$make_html->set_rep_cmd($INPUTS);
	$html .= $make_html->replace();

	// 制御ボタン定義
	$html .= "<input type=\"submit\" value=\"変更確認\">";
	$html .= "<input type=\"reset\" value=\"クリア\">";
	$html .= "</form><br>\n";
	// 一覧に戻る
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"character_list\">\n";
	$html .= "<input type=\"submit\" value=\"一覧に戻る\">\n";
	$html .= "</form>\n";
	return $html;
}

/**
 * 入力項目チェック
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @param array $LINE (CSVエラーチェックの場合に使用します)
 * @return array エラーの場合
 */
function check($ERROR, $LINE = null) {

	// グローバル変数
	global $L_DISPLAY, $L_GAM_CHARACTER_TYPE, $L_GAM_CHARACTER_OPEN;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$CHECK_LIST = array();
	if (is_array($LINE) && count($LINE) > 0) {
		$CHECK_LIST = $LINE;
	} else {
		$CHECK_LIST = $_POST;
	}

	// キャラクター名
	if (!$CHECK_LIST['character_name']) {
		$ERROR[] = "キャラクター名が未入力です。";
	} else {
		$where = '';
		if ($CHECK_LIST['character_id'] > 0) {
			$where = " AND character_id <> '".$cdb->real_escape($CHECK_LIST['character_id'])."'";
		}
		$sql = "SELECT * FROM ".T_GAMIFICATION_CHARACTER." WHERE character_name = '".$cdb->real_escape($CHECK_LIST['character_name'])."' AND mk_flg='0'".$where.";";
		if ($result = $cdb->query($sql)) {
			$count = $cdb->num_rows($result);
		}
		if ($count > 0) {
			$ERROR[] = "入力されたキャラクター名は既に登録済です";
		}
	}
	if ($CHECK_LIST['character_name'] && mb_strlen($CHECK_LIST['character_name']) > 25) {
		$ERROR[] = "キャラクター名は25文字まで登録可能です。";
	}


	// キャラクタータイプ
	if ($CHECK_LIST['character_type'] == '') {
		$ERROR[] = "キャラクタータイプが未選択です。";
	} elseif (!$L_GAM_CHARACTER_TYPE[$CHECK_LIST['character_type']]) {
		$ERROR[] = "キャラクタータイプの値が不正です";
	}

	// 満腹度減少率
	if ($CHECK_LIST['full_reduction_rate'] == '') {
		$ERROR[] = "満腹度減少率が未入力です。";
	} else {
		if (!preg_match('/^[0-9]+$/', $CHECK_LIST['full_reduction_rate'])
			|| (!($CHECK_LIST['full_reduction_rate'] > 0  && $CHECK_LIST['full_reduction_rate'] <= 100))) {
			$ERROR[] = "満腹度減少率は1～100の整数を入力してください。";
		}
	}
	// キャラクターの説明
	if ($CHECK_LIST['description'] && mb_strlen($CHECK_LIST['description']) > 255) {
		$ERROR[] = "キャラクターの説明は255文字まで入力可能です。";
	}

	//add start kimura 2019/06/03 生徒画面TOP改修 {{{
	// キャラクターの注釈(開放時)
	if ($CHECK_LIST['annotation'] && mb_strlen($CHECK_LIST['annotation']) > 255) {
		$ERROR[] = "キャラクターの注釈（開放時）は255文字まで入力可能です。";
	}

	// キャラクターの注釈(未開放時)
	if ($CHECK_LIST['annotation_locked'] && mb_strlen($CHECK_LIST['annotation_locked']) > 255) {
		$ERROR[] = "キャラクターの注釈（未開放時）は255文字まで入力可能です。";
	}

	//add end   kimura 2019/06/03 生徒画面TOP改修 }}}

	// キャラクターの開放条件
	if ($CHECK_LIST['character_type'] == '1' && !$CHECK_LIST['opening_condition']) {
		$ERROR[] = "隠しキャラとして設定する場合は、キャラクター開放条件を選択してください。";
	} elseif ($CHECK_LIST['character_type'] == '0' && $CHECK_LIST['opening_condition']) {
		$ERROR[] = "通常キャラとして設定する場合は、キャラクター開放条件を選択しないでください。";

	} elseif (!is_numeric($CHECK_LIST['opening_condition']) || (!$L_GAM_CHARACTER_OPEN[$CHECK_LIST['opening_condition']] && $CHECK_LIST['opening_condition'] != 0)) {
		$ERROR[] = "キャラクター開放条件の値が不正です。";
	}

	// キャラクター一覧での並び順
	if ($CHECK_LIST['list_num'] == '') {
		$ERROR[] = "表示順が未入力です。";
	} elseif (preg_match("/[^0-9]/",$CHECK_LIST['list_num'])) {
		$ERROR[] = "表示順の値が不正です";
	}

	// ユーザー備考
	if ($CHECK_LIST['usr_bko'] && mb_strlen($CHECK_LIST['usr_bko']) > 255) {
		$ERROR[] = "ユーザー備考255文字まで入力可能です。";
	}

	// 表示・非表示
	if (!$CHECK_LIST['display']) {
		$ERROR[] = "表示・非表示が未入力です。";
	} elseif (!$L_DISPLAY[$CHECK_LIST['display']]) {
		$ERROR[] = "表示・非表示の値が不正です。";
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

	global $L_DISPLAY, $L_GAM_CHARACTER_TYPE, $L_GAM_CHARACTER_OPEN;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// アクション情報をhidden項目に設定
	if ($_POST) {
		foreach ($_POST as $key => $val) {
			if ($key == "action") {
				if (MODE == "add") {
					$val = "add";
					// 詳細から来た時はchange（更新処理）に変える
				} elseif (MODE == "詳細") {
					$val = "change";
				}
			}
			// アクション以外のデータをhidden項目に設定
			$HIDDEN .= "<input type=\"hidden\" name=\"$key\" value=\"$val\">\n";
		}
	}

	// 他の人が削除したかチェック
	if (ACTION) {
		foreach ($_POST as $key => $val) {
			$$key = $val;
		}
	} else {
		$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"change\">\n";
		$sql = "SELECT * FROM ".T_GAMIFICATION_CHARACTER.
			" WHERE character_id='".$cdb->real_escape($_POST[character_id])."' AND mk_flg = '0' LIMIT 1;";
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

	// ボタン表示文言判定
	if (MODE == "削除") {
		$button = "削除";
	} else if (MODE == "詳細") {
		$button = "更新";
	} else {
		$button = "登録";
	}

	// 入力確認画面表示
	$html = "<br>\n";
	$html .= "確認画面：以下の内容で".$button."してもよろしければ".$button."ボタンをクリックしてください。<br>\n";
	$html .= "<br>\n";
	// htmlクラス生成
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(GAMIFICATION_CHARACTER);        
	$INPUTS['CHARACTERID']        = array('result'=>'plane','value'=>$character_id ?:'----');
	$INPUTS['CHARACTERNAME']      = array('result'=>'plane','value'=>$character_name);
	$INPUTS['CHARACTERTYPE']      = array('result'=>'plane','value'=>$L_GAM_CHARACTER_TYPE[$character_type]);
	$INPUTS['FULLREDUCATIONRATE'] = array('result'=>'plane','value'=>$full_reduction_rate);
	$INPUTS['DESCRIPTION']        = array('result'=>'plane','value'=>$description);
	$INPUTS['ANNOTATION']         = array('result'=>'plane','value'=>$annotation); //add kimura 2019/06/03 生徒画面TOP改修
	$INPUTS['ANNOTATIONLOCKED']   = array('result'=>'plane','value'=>$annotation_locked); //add kimura 2019/06/03 生徒画面TOP改修
	$INPUTS['OPENINGCONDITION']   = array('result'=>'plane','value'=>$L_GAM_CHARACTER_OPEN[$opening_condition]);
	$INPUTS['LISTNUM']            = array('result'=>'plane','value'=>$list_num);
	$INPUTS['DISPLAY']            = array('result'=>'plane','value'=>$L_DISPLAY[$display]);
	$INPUTS['USERBKO']            = array('result'=>'plane','value'=>$usr_bko);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();

	// 制御ボタン定義
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" style=\"float:left\">\n";
	$html .= $HIDDEN;
	// 削除の場合は、１画面目に戻る
	if (MODE == "削除") {
		$html .= "<input type=\"hidden\" name=\"s_page\" value=\"1\">\n";
		$html .= "<input type=\"hidden\" name=\"character_id\" value=".$character_id.">\n";
	}
	// 登録 or 削除 ボタン
	$html .= "<input type=\"submit\" value=\"$button\">\n";
	$html .= "</form>";

	// 戻るボタン
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
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"character_list\">\n";
	}
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}

/**
 * DB新規登録
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
	$INSERT_DATA = array();
	$INSERT_DATA['character_name']          = str_replace(PHP_EOL, '', $_POST['character_name']);
	$INSERT_DATA['character_type']          = $_POST['character_type'];
	$INSERT_DATA['full_reduction_rate']     = $_POST['full_reduction_rate'];
	$INSERT_DATA['description']             = $_POST['description'];
	$INSERT_DATA['annotation']              = $_POST['annotation']; //add kimura 2019/06/03 生徒画面TOP改修
	$INSERT_DATA['annotation_locked']       = $_POST['annotation_locked']; //add kimura 2019/06/03 生徒画面TOP改修
	$INSERT_DATA['opening_condition']       = $_POST['opening_condition'];
	$INSERT_DATA['list_num']                = $_POST['list_num'];
	$INSERT_DATA['usr_bko']                 = str_replace(PHP_EOL, '', $_POST['usr_bko']);
	$INSERT_DATA['display']	  		= $_POST['display'];
	$INSERT_DATA['ins_syr_id'] 		= "add";
	$INSERT_DATA['ins_date']  		= "now()";
	$INSERT_DATA['ins_tts_id'] 		= $_SESSION['myid']['id'];
	$INSERT_DATA['upd_syr_id'] 		= "add";
	$INSERT_DATA['upd_date']   		= "now()";
	$INSERT_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];

	// ＤＢ追加処理
	$ERROR = $cdb->insert(T_GAMIFICATION_CHARACTER,$INSERT_DATA);

	if (!$ERROR) {
		$_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>";
		// ディレクトリ作成
		$id = $cdb->insert_id();
		if ($id) {
			create_directory($id);
		}
	}
	return $ERROR;
}

/**
 * DB更新・削除 処理
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
		// DBアップデート
		$UPDATE_DATA = array();
		$UPDATE_DATA['character_name']      = str_replace(PHP_EOL, '', $_POST['character_name']);
		$UPDATE_DATA['character_type']      = $_POST['character_type'];
		$UPDATE_DATA['full_reduction_rate'] = $_POST['full_reduction_rate'];
		$UPDATE_DATA['description']         = $_POST['description'];
		$UPDATE_DATA['annotation']          = $_POST['annotation']; //add kimura 2019/06/03 生徒画面TOP改修
		$UPDATE_DATA['annotation_locked']   = $_POST['annotation_locked']; //add kimura 2019/06/03 生徒画面TOP改修
		$UPDATE_DATA['opening_condition']   = $_POST['opening_condition'];
		$UPDATE_DATA['list_num']            = $_POST['list_num'];
		$UPDATE_DATA['usr_bko']             = str_replace(PHP_EOL, '', $_POST['usr_bko']);
		$UPDATE_DATA['display']             = $_POST['display'];
		$UPDATE_DATA['upd_syr_id']          = "update";
		$UPDATE_DATA['upd_date']            = "now()";
		$UPDATE_DATA['upd_tts_id']          = $_SESSION['myid']['id'];
		$where = " WHERE character_id = '".$cdb->real_escape($_POST['character_id'])."' LIMIT 1;";
		$ERROR = $cdb->update(T_GAMIFICATION_CHARACTER,$UPDATE_DATA,$where);

		// 削除処理
	} elseif (MODE == "削除") {
		$UPDATE_DATA = array();
		$UPDATE_DATA['mk_flg']	   = "1";
		$UPDATE_DATA['mk_tts_id']  = $_SESSION['myid']['id'];
		$UPDATE_DATA['mk_date']	   = "now()";
		$UPDATE_DATA['upd_syr_id'] = "del";
		$UPDATE_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
		$UPDATE_DATA['upd_date']   = "now()";
		$where = " WHERE character_id = '".$cdb->real_escape($_POST['character_id'])."' LIMIT 1;";
		$ERROR = $cdb->update(T_GAMIFICATION_CHARACTER,$UPDATE_DATA,$where);

		// 関連するアイテムのポジション設定を削除する
		$UPDATE_DATA = array();
		$UPDATE_DATA['mk_flg']	= "1";
		$UPDATE_DATA['mk_tts_id']   = $_SESSION['myid']['id'];
		$UPDATE_DATA['mk_date']     = "now()";
		$UPDATE_DATA['upd_syr_id']  = "del";
		$UPDATE_DATA['upd_tts_id']  = $_SESSION['myid']['id'];
		$UPDATE_DATA['upd_date']    = "now()";
		$where = " WHERE character_id = '".$cdb->real_escape($_POST['character_id'])."';";
		$ERROR = $cdb->update(T_GAMIFICATION_ITEM_CHARACTER_POSITION, $UPDATE_DATA, $where);                

		// 画像・音声フォルダを削除する
		$sourse_file = MATERIAL_GAM_CHARACTER_DIR.'common/'.$_POST['character_id'].'/';
		system("rm -rf {$sourse_file}");

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

/**
 * 管理ディレクトリ作成
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param string $character_id
 */
function create_directory($character_id) {

        // 画像フォルダ
        $character_img_path = MATERIAL_GAM_CHARACTER_DIR.'/common/'.$character_id.'/img/';
	if (!file_exists($character_img_path)) {
		@mkdir($character_img_path, 0777, true);    // 第三引数tureで再帰的に作成する
		@chmod($character_img_path, 0777);
	}        
        // 音声フォルダ
        $character_sound_path = MATERIAL_GAM_CHARACTER_DIR.'/common/'.$character_id.'/sound/';
	if (!file_exists($character_sound_path)) {
		@mkdir($character_sound_path, 0777, true);    // 第三引数tureで再帰的に作成する
		@chmod($character_sound_path, 0777);
	}        
}

/**
 * インポート処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function gamification_character_import() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$file_name = $_FILES['gamification_character_file']['name'];
	$file_tmp_name = $_FILES['gamification_character_file']['tmp_name'];
	$file_error = $_FILES['gamification_character_file']['error'];

	if (!$file_tmp_name) {
		$ERROR[] = "キャラクターtxtファイルが指定されておりません。";
	} elseif (!eregi("(.txt)$",$file_name)) {
		$ERROR[] = "ファイルの拡張子が不正です。(txt)";
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

	$LIST = file($file_tmp_name);
	if ($LIST) {
		$i = 0;
		foreach ($LIST AS $VAL) {
			if ($i == 0) {
				$i++;  continue;
			}
			unset($LINE);
			$VAL = trim($VAL);
			if (!$VAL || !ereg("\t",$VAL)) {
				$ERROR[] = '<b>'.($i).'行目　未入力の項目があるためスキップしました。</b>';
				continue;
			}
			$file_data = explode("\t",$VAL);
			// 項目が全て設定されている場合
			if (count($file_data) == 12) { //update kimura 2019/06/03 生徒画面TOP改修 10 ---> 12
				$LINE['character_id']         = $file_data[0];
				$LINE['character_name']       = $file_data[1];
				$LINE['character_type']       = $file_data[2];
				$LINE['full_reduction_rate']  = $file_data[3];
				$LINE['description']          = $file_data[4];
				//del start kimura 2019/06/03 生徒画面TOP改修 {{{ フィールド追加に伴い連番ずれ
				// $LINE['opening_condition'] = $file_data[5];
				// $LINE['list_num']          = $file_data[6];
				// $LINE['usr_bko']           = $file_data[7];
				// $LINE['display']           = $file_data[8];
				// $LINE['mk_flg']            = $file_data[9];
				//del end   kimura 2019/06/03 生徒画面TOP改修 }}}
				//add start kimura 2019/06/03 生徒画面TOP改修 {{{
				$LINE['annotation']           = $file_data[5];
				$LINE['annotation_locked']    = $file_data[6];
				$LINE['opening_condition']    = $file_data[7];
				$LINE['list_num']             = $file_data[8];
				$LINE['usr_bko']              = $file_data[9];
				$LINE['display']              = $file_data[10];
				$LINE['mk_flg']               = $file_data[11];
				//add end   kimura 2019/06/03 生徒画面TOP改修 }}}
				// 管理番号が未設定の場合は、格納配列が１つ前にずれる
			} elseif (count($file_data) == 11) { //update kimura 2019/06/03 生徒画面TOP改修 9 ---> 11
				$LINE['character_id']        = '0';
				$LINE['character_name']      = $file_data[0];
				$LINE['character_type']      = $file_data[1];
				$LINE['full_reduction_rate'] = $file_data[2];
				$LINE['description']         = $file_data[3];
				$LINE['annotation']          = $file_data[4];
				$LINE['annotation_locked']   = $file_data[5];
				//del start kimura 2019/06/03 生徒画面TOP改修 {{{ フィールド追加に伴い連番ずれ
				// $LINE['list_num']         = $file_data[5];
				// $LINE['usr_bko']          = $file_data[6];
				// $LINE['display']          = $file_data[7];
				// $LINE['mk_flg']           = $file_data[8];
				//del end   kimura 2019/06/03 生徒画面TOP改修 }}}
				//add start kimura 2019/06/03 生徒画面TOP改修 {{{
				$LINE['opening_condition']   = $file_data[6];
				$LINE['list_num']            = $file_data[7];
				$LINE['usr_bko']             = $file_data[8];
				$LINE['display']             = $file_data[9];
				$LINE['mk_flg']              = $file_data[10];
				//add end   kimura 2019/06/03 生徒画面TOP改修 }}}
			} else {
				$ERROR[] = '<b>'.($i).'行目　未入力の項目があるためスキップしました。</b>';
				continue;
			}
			if($LINE['description']){
				$LINE['description'] = preg_replace('/<br>|<br \/>|<br\/>/', "\r\n", $LINE['description']);
			}
			//add start kimura 2019/06/03 生徒画面TOP改修 {{{
			if($LINE['annotation']){
				$LINE['annotation'] = preg_replace('/<br>|<br \/>|<br\/>/', "\r\n", $LINE['annotation']);
			}
			if($LINE['annotation_locked']){
				$LINE['annotation_locked'] = preg_replace('/<br>|<br \/>|<br\/>/', "\r\n", $LINE['annotation_locked']);
			}
			//add end   kimura 2019/06/03 生徒画面TOP改修 }}}
			if ($LINE) {
				foreach ($LINE AS $key => $val) {
					if ($val) {
						$code = judgeCharacterCode($val);
						if ( $code != 'UTF-8' ) {
							$val = mb_convert_encoding($val,"UTF-8","sjis-win");
						}
						$LINE[$key] = replace_encode($val);
					}
				}
			}
			/* ============================== エラーチェック ============================== */
			$LINE_ERROR = array();
			$LINE_ERROR = check(null,$LINE);
			/* ============================================================================ */
			// エラーの時は強制的に非表示とする。
			if ($LINE_ERROR) {
				$LINE['display'] = "2";
			}

			// データ存在チェック
			$sql  = "SELECT * FROM " .T_GAMIFICATION_CHARACTER.
				" WHERE character_id = '".$LINE['character_id']."';";
			if ($result = $cdb->query($sql)) {
				$exist_count = $cdb->num_rows($result);
			}

			// 登録処理
			if ($exist_count == 0) {
				$INSERT_DATA = array();
				$INSERT_DATA['character_id']          		= $LINE['character_id'];
				$INSERT_DATA['character_name']          		= $LINE['character_name'];
				$INSERT_DATA['character_type']				= $LINE['character_type'];
				$INSERT_DATA['full_reduction_rate']			= $LINE['full_reduction_rate'];
				$INSERT_DATA['description']				= $LINE['description'];
				$INSERT_DATA['annotation']				= $LINE['annotation']; //add kimura 2019/06/03 生徒画面TOP改修
				$INSERT_DATA['annotation_locked']				= $LINE['annotation_locked']; //add kimura 2019/06/03 生徒画面TOP改修
				$INSERT_DATA['opening_condition']                       = $LINE['opening_condition'];
				$INSERT_DATA['list_num']                                = $LINE['list_num'];
				$INSERT_DATA['usr_bko']                                 = $LINE['usr_bko'];
				$INSERT_DATA['display']					= $LINE['display'];
				$INSERT_DATA['mk_flg']					= $LINE['mk_flg'];
				$INSERT_DATA['ins_syr_id']				= "add";
				$INSERT_DATA['ins_date']				= "now()";
				$INSERT_DATA['ins_tts_id']				= $_SESSION['myid']['id'];
				$INSERT_DATA['upd_syr_id']				= "add";
				$INSERT_DATA['upd_date']				= "now()";
				$INSERT_DATA['upd_tts_id']				= $_SESSION['myid']['id'];

				$INSERT_ERROR = $cdb->insert(T_GAMIFICATION_CHARACTER, $INSERT_DATA);
				// ディレクトリ作成
				$id = $cdb->insert_id();
				if ($id) {
					create_directory($id);
				}
				// 更新処理・削除処理
			} else {
				// 更新
				$INSERT_DATA = array();
				if ($LINE['mk_flg'] == '0') {
					$INSERT_DATA['character_name']          	= $LINE['character_name'];
					$INSERT_DATA['character_type']			= $LINE['character_type'];
					$INSERT_DATA['full_reduction_rate']		= $LINE['full_reduction_rate'];
					$INSERT_DATA['description']			= $LINE['description'];
					$INSERT_DATA['annotation']			= $LINE['annotation']; //add kimura 2019/06/03 生徒画面TOP改修
					$INSERT_DATA['annotation_locked']			= $LINE['annotation_locked']; //add kimura 2019/06/03 生徒画面TOP改修
					$INSERT_DATA['opening_condition']               = $LINE['opening_condition'];
					$INSERT_DATA['list_num']                        = $LINE['list_num'];
					$INSERT_DATA['usr_bko']                         = $LINE['usr_bko'];
					$INSERT_DATA['display']				= $LINE['display'];
					$INSERT_DATA['mk_flg']				= "0";
					$INSERT_DATA['mk_tts_id']			= "NULL";
					$INSERT_DATA['mk_date']				= "NULL";
					$INSERT_DATA['upd_syr_id']			= "update";
					$INSERT_DATA['upd_date']			= "now()";
					$INSERT_DATA['upd_tts_id']			= $_SESSION['myid']['id'];
					// 削除
				} else {
					$INSERT_DATA['display']				= "2";
					$INSERT_DATA['mk_flg']				= "1";
					$INSERT_DATA['mk_tts_id']			= $_SESSION['myid']['id'];
					$INSERT_DATA['mk_date']				= "now()";
					$INSERT_DATA['upd_syr_id']			 = "del";
					$INSERT_DATA['upd_tts_id']			= $_SESSION['myid']['id'];
					$INSERT_DATA['upd_date']			= "now()";
					// 画像・音声フォルダを削除する
					$sourse_file = MATERIAL_GAM_CHARACTER_DIR.'common/'.$LINE['character_id'].'/';
					system("rm -rf {$sourse_file}");
				}
				$where = " WHERE character_id = '".$LINE['character_id']."' LIMIT 1;";
				$INSERT_ERROR = $cdb->update(T_GAMIFICATION_CHARACTER, $INSERT_DATA, $where);

			}                        
			if (is_array($INSERT_ERROR) && count($INSERT_ERROR) > 0) {
				$ERROR[] = '<b>'.($i)."行目　以下のエラーの為、情報の登録・更新に失敗しました。</b>";
				foreach($INSERT_ERROR as $v) {
					$ERROR[] = $v;
				}
			} elseif(is_array($LINE_ERROR) && count($LINE_ERROR) > 0) {
				$ERROR[] = '<b>'.($i)."行目　以下のエラーの為、非表示で登録しました。</b>";
				foreach($LINE_ERROR as $v) {
					$ERROR[] = $v;
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



