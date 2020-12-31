<?
/**
 * ベンチャー・リンク　すらら
 *
 * シークレットイベント設定 カレンダースタンプ画像管理
 *
 * 履歴
 * 2013/09/11 初期設定
 *
 * @author Azet
 */

// add koyama


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
//echo"■POST　<br>\n";pre($_POST); echo"\n\n";
//echo"■TOP　FILES　<br>\n";pre($_FILES["image_name"]); echo"\n\n";

		// チェック処理
		if (ACTION == "check") {
			// アップロードファイル取り込み処理
			if (MODE !== "詳細") {	// 再アップしての変更は出来ないようにする
//echo MODE."<br>\n";
				$ERROR = image_upload();
			}
//echo"■ERROR　<br>\n";pre($ERROR); echo"\n\n";
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
					$html .= image_list($ERROR); // 一覧表示
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
					$html .= image_list($ERROR); // 一覧表示
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
					$html .= image_list($ERROR);// 一覧表示
				}
				else {
					$html .= viewform($ERROR); 	// 詳細画面
				}
			} else {
				$html .= check_html();			// 確認画面
			}
		// 一覧表示
		} else {
			$html .= image_list($ERROR);
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
function image_list($ERROR) {
	// グローバル変数
	global $L_PAGE_VIEW,$L_IMAGE_SIZE,$L_IMAGE_DATE_CATEGORY,$L_DISPLAY;

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
		$html .= "<table>\n";
		$html .= "<tr>\n";
		$html .= "<td>シークレットイベント</td>\n";
		$html .= "</tr>\n";
		$html .= "<tr>\n";
		$html .= "<td>\n";
		$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"add\">\n";
		$html .= "<input type=\"submit\" value=\"カレンダースタンプ画像新規登録\">\n";
		$html .= "</form>\n";
		$html .= "</td>\n";
		$html .= "<td>\n";
		$html .= "</td>\n";
		$html .= "<td>\n";
		$html .= "</td>\n";
		$html .= "</tr>\n";
		$html .= "</table>\n";

		$calendar_img_ftp = FTP_URL."secret_event/calendar/";

		$html .= "<br>\n";
		
		$html .= FTP_EXPLORER_MESSAGE ."<br>\n"; //add hirose 2018/04/23 FTPをブラウザからのエクスプローラーで開けない不具合対応

		// イメージフォルダへのパス
		$html .= "<a href=\"".$calendar_img_ftp."\" target=\"_blank\">カレンダースタンプ画像フォルダー($calendar_img_ftp)</a><br>\n";
		$html .= "<br>\n";

	}
	// 表示ページ制御
	$s_page_view_html .= "&nbsp;&nbsp;&nbsp;表示数<select name=\"s_page_view\">\n";
	foreach ($L_PAGE_VIEW as $key => $val){
		if ($_SESSION['sub_session']['s_page_view'] == $key) { $sel = " selected"; } else { $sel = ""; }
		$s_page_view_html .= "<option value=\"".$key."\"".$sel.">".$val."</option>\n";
	}
	$s_page_view_html .= "</select><input type=\"submit\" value=\"Set\">\n";

	// イメージ取得SQL作成
	$sql  = "SELECT * FROM " . T_SECRET_CALENDAR_IMAGE .
			" WHERE mk_flg ='0' ".
			"   ORDER BY calendar_image_id ";

	// イメージ件数取得
	$image_count = 0;
	if ($result = $cdb->query($sql)) {
		$image_count = $cdb->num_rows($result);
	}

	// ページビュー判断
	if ($L_PAGE_VIEW[$_SESSION['sub_session']['s_page_view']]) {
		$page_view = $L_PAGE_VIEW[$_SESSION['sub_session']['s_page_view']];
	} else {
		$page_view = $L_PAGE_VIEW[0];
	}

	// 最大ページ数算出
	$max_page = ceil($image_count/$page_view);

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
		$image_check_count = 0;
		if ($result = $cdb->query($sql)) {
			$image_check_count = $cdb->num_rows($result);
		}
		if (!$image_check_count) {
			$html .= "現在、登録イメージは存在しません。";
			return $html;
		}

		// 一覧表示開始
		$html .= "<br>\n";
		$html .= "修正する場合は、修正する画像名の詳細ボタンを押してください。<br>\n";

		// 登録件数表示
		$html .= "<br>\n";
		$html .= "<div style=\"float:left;\">イメージ総数(".$image_count."):PAGE[".$page."/".$max_page."]</div>\n";

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
		$html .= "<th>登録番号</th>\n";
		$html .= "<th>画像名</th>\n";
		$html .= "<th>キャラクター名</th>\n";
		$html .= "<th>表示・非表示</th>\n";
		$html .= "<th>確認</th>\n";

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
//echo"■list　<br>\n";pre($list); echo"\n\n";

			// イメージ格納ディレクトリ作成
			create_directory();

			// ファイルの拡張子を取得
			$file_extension = get_extension($list['image_name']);

			// 明細表示
			$html .= "<tr class=\"secret_form_cell\">\n";
			$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"calendar_image_id\" value=\"".$list['calendar_image_id']."\">\n";

			$html .= "<td>".$list['calendar_image_id']."</td>\n";
			$html .= "<td>".$list['image_name']."</td>\n";
			$html .= "<td>".$list['character_name']."</td>\n";
			$html .= "<td>".$L_DISPLAY[$list['display']]."</td>\n";

			// 確認ボタン	クリックで確認画面表示
			$file_path = MATERIAL_SECRET_DIR."calendar/";
//echo "■file_path == ".$file_path."<br>\n";
			$html .= "<td><input type=\"button\" value=\"確認\" onclick=\"secret_image_win_open_calendar('".$file_path."','".$list['image_name']."','".$file_extension."')\"></td>\n";

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
	global $L_IMAGE_INCIDENCE,$L_DISPLAY;

	$html .= "<br>\n";
	$html .= "シークレットイベント 新規登録フォーム<br>\n";
	$html .= "<br>\n";
	$html .= "<table class=\"secret_notice\">\n";
	$html .= "<tr>\n";
	$html .= "<td colspan=\"2\">以下の３ファイルを zip ファイルにまとめ、画像名欄からアップロードすること。<td>\n";
	$html .= "</tr>\n";

	$html .= "<tr>\n";
	$html .= "<td colspan=\"2\">zip ファイル名は、XXXX.zip にすること。２バイト文字ファイル名は不可。<td>\n";
	$html .= "</tr>\n";

	$html .= "<tr>\n";
	$html .= "<td colspan=\"2\">XXXX は共通名(主部名)で、この文字列がDB内の、image_name カラム値になります。<td>\n";
	$html .= "</tr>\n";

	$html .= "<tr>\n";
	$html .= "<td colspan=\"2\">XXXX 部分は、下記の(例)のように、１種類を構成するスタンプ画像ファイル名で共通にします。<td>\n";
	$html .= "</tr>\n";

	$html .= "<tr>\n";
	$html .= "<td colspan=\"2\">画像ファイル種類は jpg, gif, png いずれでも構わないが、１スタンプ枚に１種類に統一すること。<td>\n";
	$html .= "</tr>\n";

	$html .= "<tr>\n";
	$html .= "<td>（１）ギフトボックス表示用 大サイズ １枚<td>\n";
	$html .= "<td>例）XXXX_L.jpg<td>\n";
	$html .= "</tr>\n";
	$html .= "<tr>\n";
	$html .= "<td>（２）ログイン状況画面表示用 中サイズ １枚<td>\n";
	$html .= "<td>例）XXXX_M.jpg<td>\n";
	$html .= "</tr>\n";
	$html .= "<tr>\n";
	$html .= "<td>（３）TOPページのログイン状況カレンダー表示用 小サイズ １枚<td>\n";
	$html .= "<td>例）XXXX_S.jpg<td>\n";
	$html .= "</tr>\n";

	$html .= "</table>\n";
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
	$make_html->set_file(SECRET_CALENDAR_IMAGE);

	// $配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	$INPUTS[CALENDERIMAGEID]= array('result'=>'plane','value'=>"---");
	$INPUTS[IMAGENAME] 		= array('type'=>'file','name'=>'image_name','size'=>40);
	$INPUTS[CHARACTERNAME] 	= array('type'=>'text','name'=>'character_name','size'=>'50','value'=>$_POST['character_name']);

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
	$INPUTS[DISPLAY] = array('result'=>'plane','value'=>$display_value);

	$make_html->set_rep_cmd($INPUTS);
	$html .= $make_html->replace();

	// 制御ボタン定義
	$html .= "<input type=\"submit\" value=\"追加確認\">\n";
	$html .= "<input type=\"reset\" value=\"クリア\">\n";
	$html .= "</form>\n";
	$html .= "<br>\n";

	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"image_list\">\n";
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
//echo"■POST　詳細　<br>\n";pre($_POST); echo"\n\n";
	// グローバル変数
	global $L_PAGE_VIEW, $L_DISPLAY;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// POST値取得
	if (ACTION=="check") {
		// POST値取得
		foreach ($_POST as $key => $val) {
			$$key = $val;
		}
	} else {
		$sql = "SELECT * FROM " . T_SECRET_CALENDAR_IMAGE .
			" WHERE calendar_image_id='".$_POST['calendar_image_id']."' AND mk_flg='0' LIMIT 1;";
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
	// ファイルの拡張子を取得
	$file_extension = get_extension($image_name);

	// 拡張子を除いたファイル名取得
	$image_name = preg_replace("/(.+)(\.[^.]+$)/", "$1", $image_name);

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
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"詳細\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";
	$html .= "<input type=\"hidden\" name=\"calendar_image_id\" value=\"$calendar_image_id\">\n";
	$html .= "<input type=\"hidden\" name=\"image_name\" value=\"$image_name\">\n";

	// htmlクラス生成
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(SECRET_CALENDAR_IMAGE);


	//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	if (!$calendar_image_id) { $calendar_image_id = "---"; }
	$INPUTS[CALENDERIMAGEID]= array('result'=>'plane','value'=>$calendar_image_id);
	$INPUTS[IMAGENAME] 		= array('result'=>'plane','value'=>$image_name);
	$INPUTS[CHARACTERNAME] 	= array('type'=>'text','name'=>'character_name','size'=>'50','value'=>$character_name);

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

	$make_html->set_rep_cmd($INPUTS);
	$html .= $make_html->replace();

	// 注意書き  add start okabe 2013/11/13
	$html .= "<div class=\"secret_notice\">";
	$html .= "※ 画像名欄は、各ファイル共通の主部名が表示されています。</div>";
	$html .= "<br>";
	// add end okabe 2013/11/13

	// 制御ボタン定義
	$html .= "<input type=\"submit\" value=\"変更確認\">";
	$html .= "<input type=\"reset\" value=\"クリア\">";
	$html .= "</form><br>\n";
	// 確認ボタン	クリックで確認画面表示
	$file_path = MATERIAL_SECRET_DIR."calendar/";
	$html .= "<input type=\"button\" value=\"確認\" onclick=\"secret_image_win_open_calendar('".$file_path."','".$list['image_name']."','".$file_extension."')\"></td>\n";
	// ワード一覧に戻る
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"image_list\">\n";
	$html .= "<input type=\"submit\" value=\"画像一覧に戻る\">\n";
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
 * @return array エラーの場合
 */
function check($ERROR) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

//echo "■入力項目チェックPOST　<br>\n";pre($_POST); echo"\n\n";
//echo "■入力項目チェックSESSION　<br>\n";pre($_SESSION); echo"\n\n";
	// 未入力チェック
	if (MODE !== "詳細") {										// 詳細画面では変更しないのでチェックしない
//echo "■詳細②<br>\n";
		if (!$_FILES["image_name"]["name"]) {
			//$ERROR[] = "画像名が未入力です。";				// アップロード時のエラーを優先させる為、削除します koyama
		} else {
			$_POST["image_name"] = $_FILES["image_name"]["name"];//ファイル名があればPOSTに設定
		}
	}

	// 拡張子を除いたファイル名
	$_POST["image_name"] = preg_replace("/(.+)(\.[^.]+$)/", "$1", $_POST["image_name"]);

	//ファイルが３サイズ揃っているか確認  add start okabe 2013/11/13
	$res = check_image_exist($_POST["image_name"]);
	if ($res) {
		$msg = "必要な画像ファイルが揃っていません (";
		if (($res & 4) == 4) { $msg .= "Lサイズ "; }
		if (($res & 2) == 2) { $msg .= "Mサイズ "; }
		if (($res & 1) == 1) { $msg .= "Sサイズ "; }
		$msg .= ")。";
		$ERROR[] = $msg;
	}
	// add end okabe 2013/11/13

	if (!$_POST['character_name']) {
		$ERROR[] = "キャラクター名が未入力です。";
	}
	if (!$_POST['display']) {
		$ERROR[] = "表示・非表示が未入力です。";
	}
	// 重複チェック
	if (MODE == "add") {
		$sql  = "SELECT * FROM " .T_SECRET_CALENDAR_IMAGE.
				" WHERE mk_flg!='1' AND image_name = '".$_SESSION['master_file_name']."'";
		// SQL実行
		if ($result = $cdb->query($sql)) {
			$count = $cdb->num_rows($result);
		}
		if ($count > 0) {
			$ERROR[] = "入力された画像名は既に登録されております。";
		}
	}

	return $ERROR;
}



/**
 * 必要な画像が揃っているかチェック
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param string $fname_main
 * @return integer
 */
function check_image_exist($fname_main) {
// add okabe 2013/11/13
	// $fname_main: 画像ファイル名の主部
	// 結果: 揃っていれば 0 | 揃ってなければ 1～7 (存在ものをbit値で)
	$res = 0;
	$file_check_path = MATERIAL_SECRET_DIR."calendar/".$fname_main;
	$glob_path = glob($file_check_path."_L.*");
	if (!count($glob_path)) { $res += 4;}

	$glob_path = glob($file_check_path."_M.*");
	if (!count($glob_path)) { $res += 2;}

	$glob_path = glob($file_check_path."_S.*");
	if (!count($glob_path)) { $res += 1;}

	return $res;
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
//echo "■入力確認画面POST　<br>\n";pre($_POST); echo"\n\n";
	global $L_DISPLAY;

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
		$sql = "SELECT * FROM ".T_SECRET_CALENDAR_IMAGE.
			" WHERE calendar_image_id='".$_POST[calendar_image_id]."' AND mk_flg!='1' LIMIT 1;";
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
	$make_html->set_file(SECRET_CALENDAR_IMAGE);

	//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	if (!$calendar_image_id) { $calendar_image_id = "---"; }

	$INPUTS[CALENDERIMAGEID] = array('result'=>'plane','value'=>$calendar_image_id);
	if (MODE == "詳細" || MODE == "削除") {
		$INPUTS[IMAGENAME] 		 = array('result'=>'plane','value'=>$image_name);					// 詳細・削除時はPOST値
	} else {
		$INPUTS[IMAGENAME] 		 = array('result'=>'plane','value'=>$_FILES["image_name"]["name"]);	// 新規時はFILES値
	}
	$INPUTS[CHARACTERNAME]	 = array('result'=>'plane','value'=>$character_name);
	$INPUTS[DISPLAY]	     = array('result'=>'plane','value'=>$L_DISPLAY[$display]);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();

	// 制御ボタン定義
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" style=\"float:left\">\n";
	$html .= $HIDDEN;

	// 削除の場合は、１画面目に戻る
	if (MODE == "削除") {
		$html .= "<input type=\"hidden\" name=\"s_page\" value=\"1\">\n";
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
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"image_list\">\n";
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

	$problem_num = "";

	// 登録項目設定
	$INSERT_DATA['image_name'] = $_SESSION['master_file_name'];
	$INSERT_DATA['character_name']  = $_POST['character_name'];
	$INSERT_DATA['display']	   = $_POST['display'];
	$INSERT_DATA['ins_syr_id'] = "add";
	$INSERT_DATA['ins_date']   = "now()";
	$INSERT_DATA['ins_tts_id'] = $_SESSION['myid']['id'];
	$INSERT_DATA['upd_syr_id'] = "add";
	$INSERT_DATA['upd_date']   = "now()";
	$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];

	// ＤＢ追加処理
	$ERROR = $cdb->insert(T_SECRET_CALENDAR_IMAGE,$INSERT_DATA);


	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
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
		$INSERT_DATA['character_name']			= $_POST['character_name'];
		$INSERT_DATA['display']					= $_POST['display'];
		$INSERT_DATA['upd_syr_id']				= "update";
		$INSERT_DATA['upd_date']				= "now()";
		$INSERT_DATA['upd_tts_id']				= $_SESSION['myid']['id'];
		$where = " WHERE calendar_image_id = '".$_POST['calendar_image_id']."' LIMIT 1;";

		$ERROR = $cdb->update(T_SECRET_CALENDAR_IMAGE,$INSERT_DATA,$where);

	// 削除処理
	} elseif (MODE == "削除") {
		$INSERT_DATA['mk_flg']		= "1";
		$INSERT_DATA['mk_tts_id']	= $_SESSION['myid']['id'];
		$INSERT_DATA['mk_date']		= "now()";
		$INSERT_DATA['upd_syr_id']   = "del";
		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date']   = "now()";
		$where = " WHERE calendar_image_id = '".$_POST['calendar_image_id']."' LIMIT 1;";

		$ERROR = $cdb->update(T_SECRET_CALENDAR_IMAGE,$INSERT_DATA,$where);

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
 * アップロードファイル取り込み処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function image_upload() {
//echo "■アップロード　POST　<br>\n";pre($_POST); echo"\n\n";
	// アップロードファイル情報取得
	$file_name = $_FILES['image_name']['name'];// ○○.zip
//echo "■file_name == ".$file_name."<br>\n";
	$file_tmp_name = $_FILES['image_name']['tmp_name'];
//echo "■file_tmp_name == ".$file_tmp_name."<br>\n";
	$file_error = $_FILES['image_name']['error'];

	// 拡張子を除いたファイル名
	$file_un_ex = preg_replace("/(.+)(\.[^.]+$)/", "$1", $file_name);

	// エラー処理
	if (!$file_tmp_name) {
		$ERROR[] = "画像ファイルが指定されておりません。";
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
	// アップロードファイルを格納するファイルパスを指定
	$filenamepath = MATERIAL_SECRET_DIR."calendar/temp/".$file_name;// /temp/○○.zip
//echo "■filenamepath == ".$filenamepath."<br>\n";
	// アップロード先ディレクトリを作成する
	$material_img_path = MATERIAL_SECRET_DIR."calendar/temp/";
	if (!file_exists($material_img_path)) {
		@mkdir($material_img_path,0777);
		@chmod($material_img_path,0777);
	}

	// テンポラリファイル→ファイル格納パスにzipファイルをコピー
	$result = @move_uploaded_file( $file_tmp_name, $filenamepath);

	// コピー元ファイル削除
	if ($file_tmp_name && file_exists($file_tmp_name)) {
		unlink($file_tmp_name);
	}

	// zipファイルを解凍する
	$remote_dir = SECRET_UNZIP_DIR."calendar/temp";// 解凍先
	$command = "cd ".$remote_dir."; unzip ".$remote_dir."/".$file_name;	//del okabe 2013/12/19
	$command = "cd ".$remote_dir."; unzip ".$file_name;		//add okabe 2013/12/19
	exec($command);

	// 解凍後のzipファイルは削除
	if (strlen($file_name) > 0) {	//add okabe 2013/11/20
		unlink($filenamepath);
	}

	// フォルダが二重になった場合の処理（例 folder001.zipを解凍後にsecret_event/calendar/temp/folder001/ここに展開された場合 )
	$file_check_path = MATERIAL_SECRET_DIR."calendar/temp/".$file_un_ex;
//echo "■file_check_path == ".$file_check_path."<br>\n";
	if (is_dir($file_check_path)) {
		$remote_dir = SECRET_UNZIP_DIR."calendar/temp/".$file_un_ex;
		// ディレクトリを移動し、展開したファイルを全て上のフォルダに移動(mv *.* ../;)し、残った下のフォルダを削除(rm -rf "file_un_ex";)する
		$command = "cd ".$remote_dir."; mv *.* ../;	cd ../; rm -rf ".$file_un_ex.";";
		exec($command);

	}

	// 展開された画像ファイルの共通名を取得し、セッションへ (DBに登録される画像名となる）
	$GET_EXTENSION = glob(MATERIAL_SECRET_DIR."calendar/temp/*_S.*");
	$FILE_NAME_EXPLODE = explode('_S.',$GET_EXTENSION[0]);
	//update start kimura 2017/12/05 AWS移設 //end()関数には変数を渡すよう修正
	$temp_array = explode('/',$FILE_NAME_EXPLODE[0]);
	$master_file_name = end($temp_array);
	//$master_file_name = end(explode('/',$FILE_NAME_EXPLODE[0]));
	//update end kimura 2017/12/05 AWS移設
	unset($_SESSION['master_file_name']);
	$_SESSION['master_file_name'] = $master_file_name;

	// tempフォルダからcalendarフォルダへ移動する
	$file_check_path = MATERIAL_SECRET_DIR."calendar/temp";
//echo "■file_check_path == ".$file_check_path."<br>\n";
	if (is_dir($file_check_path)) {
		$remote_dir = SECRET_UNZIP_DIR."calendar/temp";
		// ディレクトリを移動し、展開したファイルを全て上のフォルダに移動(mv *.* ../;)し、残った下のフォルダを削除(rm -rf temp;)する
		$command = "cd ".$remote_dir."; mv *.* ../;	cd ../; rm -rf temp;";
		exec($command);
	}

	return $ERROR;
}


/**
 * 管理ディレクトリ作成
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function create_directory() {

	// シークレットイベント管理イメージ保管ディレクトリ作成
	$material_img_path = MATERIAL_SECRET_DIR;
	if (!file_exists($material_img_path)) {
		@mkdir($material_img_path,0777);
		@chmod($material_img_path,0777);
	}
	// カレンダースタンプ画像のイメージ格納フォルダ作成
	$material_img_path = MATERIAL_SECRET_DIR."calendar/";
	if (!file_exists($material_img_path)) {
		@mkdir($material_img_path,0777);
		@chmod($material_img_path,0777);
	}
}


/**
 * ファイルの拡張子を取得
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param string $image_name
 * @return string
 */
function get_extension ($image_name) {
	$GET_EXTENSION = glob(MATERIAL_SECRET_DIR."calendar/".$image_name."_S.*");
	//update start kimura 2017/12/05 AWS移設 //end()関数には変数を渡すよう修正
	$temp_array = explode('.',$GET_EXTENSION[0]);
	$file_extension = end($temp_array);
	//$file_extension = end(explode('.',$GET_EXTENSION[0]));
	//update end kimura 2017/12/05 AWS移設

	return $file_extension;
}
?>
