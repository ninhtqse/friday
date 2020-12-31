<?
/**
 * ベンチャー・リンク　すらら
 *
 * ークレットイベント設定 パズルピース画像管理
 *
 * 履歴
 * 2013/09/11 初期設定
 *
 * @author Azet
 */

// add koyama

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
//echo"■POST　<br>\n";pre($_POST); echo"\n\n";
//echo"■TOP　FILES　<br>\n";pre($_FILES["image_name"]); echo"\n\n";

		// チェック処理
		if (ACTION == "check") {
			// アップロードファイル取り込み処理
			if (MODE !== "詳細") {	// 再アップしての変更は出来ないようにする
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
	global $L_PAGE_VIEW,$L_IMAGE_SCHOOL_CATEGORY,$L_DISPLAY;

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
		$html .= "<input type=\"submit\" value=\"パズルピース画像新規登録\">\n";
		$html .= "</form>\n";
		$html .= "</td>\n";
		$html .= "<td>\n";
		$html .= "</td>\n";
		$html .= "<td>\n";
		$html .= "</td>\n";
		$html .= "</tr>\n";
		$html .= "</table>\n";

		$puzzle_img_ftp = FTP_URL."secret_event/puzzle/";

		$html .= "<br>\n";
		
		$html .= FTP_EXPLORER_MESSAGE ."<br>\n"; //add hirose 2018/04/23 FTPをブラウザからのエクスプローラーで開けない不具合対応


		// イメージフォルダへのパス
		$html .= "<a href=\"".$puzzle_img_ftp."\" target=\"_blank\">パズルピース画像フォルダー($puzzle_img_ftp)</a><br>\n";
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
	$sql  = "SELECT * FROM " . T_SECRET_PUZZLE_IMAGE .
			" WHERE mk_flg ='0' ".
			"   ORDER BY image_category ";

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
		$html .= "<th>開示年月</th>\n";
		$html .= "<th>表示・非表示</th>\n";
		$html .= "<th colspan=3>確認</th>\n";

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
			// 開示年月に「年」「月」を付ける
			$year_char = substr($list['image_category'], 0, 4);
			$month_char = substr($list['image_category'], 4, 2);
			$image_category_char = $year_char."年".$month_char."月";

			// ファイルの拡張子を取得
			$file_extension = get_extension($list['image_category']);

//echo "■SESSION　<br>\n";pre($_SESSION); echo"\n\n";
			// イメージ格納ディレクトリ作成
			create_directory($list['image_category']);


			// HTML作成
			$html .= "<tr class=\"secret_form_cell\">\n";
			$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"puzzle_image_id\" value=\"".$list['puzzle_image_id']."\">\n";
			$html .= "<input type=\"hidden\" name=\"image_name\" value=\"".$list['image_name']."\">\n";
			$html .= "<input type=\"hidden\" name=\"image_category\" value=\"".$list['image_category']."\">\n";

			$html .= "<td>".$list['puzzle_image_id']."</td>\n";
			$html .= "<td>".$list['image_name']."</td>\n";
			$html .= "<td>".$image_category_char."</td>\n";
			$html .= "<td>".$L_DISPLAY[$list['display']]."</td>\n";

			$file_path = MATERIAL_SECRET_DIR."puzzle/".$list['image_category']."/";

			// カラーピース確認ボタン
			$html .= "<td colspan=3><input type=\"button\" value=\"カラーピース\" onclick=\"secret_image_win_open_color('".$file_path."','".$list['image_name']."','".$file_extension."');\">\n";
			// モノクロピース確認ボタン
			$html .= "<input type=\"button\" value=\"モノクロピース\" onclick=\"secret_image_win_open_mono('".$file_path."','".$list['image_name']."','".$file_extension."');\");\">\n";
			// ピース完成確認ボタン
			$html .= "<input type=\"button\" value=\"ピース完成\" onclick=\"secret_image_win_open_comp('".$file_path."','".$list['image_name']."','".$file_extension."');\");\">\n";
			// DL確認ボタン
			$file_name = $list['image_name']."__dl.zip";// DLファイル名
			$file = $file_path.$file_name;					// ファイルパス
			$down_file = "./secret_image_download.php?i=".$list['image_category']."&name=".$file_name;
			$html .= "<input type=\"button\" onClick=\"location.href='".$down_file."'\" value=\"DOWNLOAD\">\n";

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
	global $L_IMAGE_MONTH,$L_DISPLAY;

	// 確認画面から戻ってきた場合、アップロードされた画像フォルダを削除する
	if ((ACTION == "back")) {
		$sourse_file = MATERIAL_SECRET_DIR."puzzle/".$_POST['image_category'];
		system("rm -rf {$sourse_file}");
	}
	// 注意書き
	$html .= "<br>\n";
	$html .= "シークレットイベント 新規登録フォーム<br>\n";
	$html .= "<br>\n";
	// add start okabe 2013/11/13
	$html .= "<table class=\"secret_notice\">\n";
	$html .= "<tr>\n";
	$html .= "<td colspan=\"2\">以下の全ファイルを zip ファイルにまとめ、画像名欄からアップロードすること。<td>\n";
	$html .= "</tr>\n";

	$html .= "<tr>\n";
	$html .= "<td colspan=\"2\">zip ファイル名は、XXXX.zip にすること。２バイト文字ファイル名は不可。<td>\n";
	$html .= "</tr>\n";

	$html .= "<tr>\n";
	$html .= "<td colspan=\"2\">XXXX は共通名(主部名)で、この文字列がDB内の、image_name カラム値になります。<td>\n";
	$html .= "</tr>\n";

	$html .= "<tr>\n";
	$html .= "<td colspan=\"2\">XXXX 部分は、下記の(例)のように構成する全画像ファイル名で共通にします。<td>\n";
	$html .= "</tr>\n";

	$html .= "<tr>\n";
	$html .= "<td colspan=\"2\">画像ファイル種類は jpg, gif, png いずれでも構わないが、１スタンプ枚に１種類に統一すること。<td>\n";
	$html .= "</tr>\n";

	$html .= "<tr>\n";
	$html .= "<td>（１）ダウンロード用の画像、１枚以上をアーカイブしたzipファイル<td>\n";
	$html .= "<td>例）XXXX__dl.zip (含まれる画像ファイル名は任意) <td>\n";
	$html .= "</tr>\n";

	$html .= "<tr>\n";
	$html .= "<td>（２）白黒の各ピース画像、２０枚<td>\n";
	$html .= "<td> 例）XXXX_01_m.jpg ～ XXXX_20_m.jpg<td>\n";
	$html .= "</tr>\n";

	$html .= "<tr>\n";
	$html .= "<td>（３）カラーの各ピース画像、２０枚<td>\n";
	$html .= "<td>例）XXXX_01_c.jpg ～ XXXX_20_c.jpg<td>\n";
	$html .= "</tr>\n";

	$html .= "<tr>\n";
	$html .= "<td>（４）完成時のカラー画像、１枚<td>\n";
	$html .= "<td>例）XXXX.jpg<td>\n";
	$html .= "</tr>\n";

	$html .= "</table>\n";
	$html .= "<br>\n";
	// add end okabe 2013/11/13


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
	$make_html->set_file(SECRET_PUZZLE_IMAGE);

	// 年月を取得
	$year = date('Y');
	$month = date('m');
	// 月によって表示を微調整
	if ($month < 4) {
		$next_year = $year;
	} else {
		$next_year = $year + 1;
	}
	// 開示年月用配列作成
	$L_IMAGE_YEAR_MONTH[0] = "選択して下さい";
	for ($i = 4; $i <= 12; $i++) {
		if ($i < 10) {
			$L_IMAGE_YEAR_MONTH[$year."0".$i] = $year."年".sprintf("%02d",$i)."月";
		} else {
			$L_IMAGE_YEAR_MONTH[$year.$i] = $year."年".sprintf("%02d",$i)."月";
		}
	}
	for ($i= 1; $i <= 3; $i++) {
			$L_IMAGE_YEAR_MONTH[$next_year."0".$i] = $next_year."年".sprintf("%02d",$i)."月";
	}

//echo"■L_IMAGE_YEAR_MONTH　<br>\n";pre($L_IMAGE_YEAR_MONTH); echo"\n\n";

	// $配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	$INPUTS[PUZZLEIMAGEID]	= array('result'=>'plane','value'=>"---");
	$INPUTS[FILENAME] 		= array('type'=>'file','name'=>'file_name','size'=>40);
	$INPUTS[IMAGECATEGORY] 	= array('type'=>'select','name'=>'image_category','array'=>$L_IMAGE_YEAR_MONTH,'check'=>$_POST['image_category']);

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
//echo "■SESSION　<br>\n";pre($_SESSION); echo"\n\n";
	// グローバル変数
	global $L_PAGE_VIEW, $L_IMAGE_MONTH, $L_DISPLAY;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// POST値取得
	if (ACTION=="check") {
		// POST値取得
		foreach ($_POST as $key => $val) {
			$$key = $val;
		}
	} else {
		$sql = "SELECT * FROM " . T_SECRET_PUZZLE_IMAGE .
			" WHERE puzzle_image_id='".$_POST['puzzle_image_id']."' AND mk_flg='0' LIMIT 1;";
		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);
//echo"■list　<br>\n";pre($list); echo"\n\n";
		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}
		foreach ($list as $key => $val) {
			$$key = replace_decode($val);
			$$key = ereg_replace("\"","&quot;",$$key);
		}
	}

	// 開示年月をセッションに格納する（変更されたかのチェックに使用）
	$_SESSION['image_category'] = $image_category;

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
	$html .= "<input type=\"hidden\" name=\"puzzle_image_id\" value=\"$puzzle_image_id\">\n";
	$html .= "<input type=\"hidden\" name=\"image_name\" value=\"$image_name\">\n";
	$html .= "<input type=\"hidden\" name=\"image_category\" value=\"$image_category\">\n";

	// 年月を取得
	$year = date('Y');
	$month = date('m');
	// 月によって表示を微調整
	if ($month < 4) {
		$next_year = $year;
	} else {
		$next_year = $year + 1;
	}
	// 開示年月用配列作成
	$L_IMAGE_YEAR_MONTH[0] = "選択して下さい";
	for ($i = 4; $i <= 12; $i++) {
		if ($i < 10) {
			$L_IMAGE_YEAR_MONTH[$year."0".$i] = $year."年".sprintf("%02d",$i)."月";
		} else {
			$L_IMAGE_YEAR_MONTH[$year.$i] = $year."年".sprintf("%02d",$i)."月";
		}
	}
	for ($i= 1; $i <= 3; $i++) {
			$L_IMAGE_YEAR_MONTH[$next_year."0".$i] = $next_year."年".sprintf("%02d",$i)."月";
	}

	// htmlクラス生成
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(SECRET_PUZZLE_IMAGE);

	//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	if (!$puzzle_image_id) { $puzzle_image_id = "---"; }
	$INPUTS[PUZZLEIMAGEID]= array('result'=>'plane','value'=>$puzzle_image_id);
	$INPUTS[FILENAME] 		= array('result'=>'plane','value'=>$image_name);
	$INPUTS[IMAGECATEGORY] 	= array('type'=>'select','name'=>'image_category','array'=>$L_IMAGE_YEAR_MONTH,'check'=>$image_category);


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
	// img格納フォルダパス
	$file_path = MATERIAL_SECRET_DIR."puzzle/".$image_category."/";
	// ファイルの拡張子を取得
	$file_extension = get_extension($image_category);

	// カラーピース確認ボタン
	$html .= "<td colspan=3><input type=\"button\" value=\"カラーピース\" onclick=\"secret_image_win_open_color('".$file_path."','".$image_name."','".$file_extension."');\">\n";
	// モノクロピース確認ボタン
	$html .= "<input type=\"button\" value=\"モノクロピース\" onclick=\"secret_image_win_open_mono('".$file_path."','".$image_name."','".$file_extension."');\">\n";
	// ピース完成確認ボタン
	$html .= "<input type=\"button\" value=\"ピース完成\" onclick=\"secret_image_win_open_comp('".$file_path."','".$image_name."','".$file_extension."');\">\n";
	// DL確認ボタン
	$file_name = $image_name."__dl.zip";// DLファイル名
	$file = $file_path.$file_name;		// ファイルパス
	$down_file = "./secret_image_download.php?i=".$image_category."&name=".$file_name;
//echo "■down_file == ".$down_file."<br>\n";
	$html .= "<input type=\"button\" onClick=\"location.href='".$down_file."'\" value=\"DOWNLOAD\">\n";

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

//echo"■check($ERROR)　POST　<br>\n";pre($_POST); echo"\n\n";
	// 未入力チェック
	if (!$_FILES["file_name"]["name"]) {
		//$ERROR[] = "画像名が未入力です。";				// アップロード時のエラーを優先させる為、削除します koyama
	} else {
		$_POST["file_name"] = $_FILES["file_name"]["name"];	//ファイル名があればPOSTに設定
	}


	if ($_POST['image_category'] == '0') {
		$ERROR[] = "開示年月が未入力です。";
	}
	// add start okabe 2013/11/13
	else {
		if (MODE == "add") {	//登録時のみ確認
			//ファイルが揃っているか確認
			$fname = preg_replace("/(.+)(\.[^.]+$)/", "$1", $_FILES["file_name"]["name"]);
			list($res, $msg) = check_image_exist($fname, $_POST['image_category']);
			if ($res) {
				$ERROR[] = "必要な画像ファイルが揃っていません(".$msg.")。";
			}
		}
	}
	// add end okabe 2013/11/13

	if (!$_POST['display']) {
		$ERROR[] = "表示・非表示が未入力です。";
	}

	if (MODE == "add" || MODE == "詳細") {
		// 新規の時と、変更した場合はチェックする
		if (!$_SESSION['image_category'] || $_SESSION['image_category'] !== $_POST['image_category']) {

			// 開示年月は重複出来ないようにする
			$sql  = "SELECT * FROM " .T_SECRET_PUZZLE_IMAGE.
				" WHERE mk_flg!='1' AND image_category = '".$_POST['image_category']."'";

			// SQL実行
			if ($result = $cdb->query($sql)) {
				$count = $cdb->num_rows($result);
			}
			if ($count > 0) {
				$ERROR[] = "入力された開示年月（".$_POST['image_category']."）には既にイメージが登録されております。";
				// ポスト値を初期化して戻る
				if ($_SESSION['image_category']) {
					$_POST['image_category'] = $_SESSION['image_category'];
				}
			}
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
 * @param string $image_category
 * @return array
 */
function check_image_exist($fname_main, $image_category) {
// add okabe 2013/11/13
	// $fname_main: 画像ファイル名の主部
	// $image_category: 開示年月
	// 結果: $res: 揃っていれば 0 | 揃ってなければ 1
	//       $msg: エラーメッセージ
	$res = 0;
	$msg = "";
	$file_check_path = MATERIAL_SECRET_DIR."puzzle/".$image_category."/".$fname_main;

	$glob_path = glob($file_check_path."_??_c.*");
	if (count($glob_path) != 20) {
		$res = 1;
		$msg .= "カラーピース";
	}
	$glob_path = glob($file_check_path."_??_m.*");
	if (count($glob_path) != 20) {
		$res = 1;
		if (strlen($msg) > 0) { $msg .= ","; }
		$msg .= "白黒ピース";
	}
	$glob_path = glob($file_check_path.".*");
	if (!count($glob_path)) {
		$res = 1;
		if (strlen($msg) > 0) { $msg .= ","; }
		$msg .= "完成画像";
	}
	$glob_path = glob(MATERIAL_SECRET_DIR."puzzle/".$image_category."/*.zip");
	if (!count($glob_path)) {
		$res = 1;
		if (strlen($msg) > 0) { $msg .= ","; }
		$msg .= "ダウンロード用ファイル";
	}

	return array($res, $msg);
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
//echo "■入力確認画面表示POST　<br>\n";pre($_POST); echo"\n\n";
//echo "■入力確認画面表示SESSION　<br>\n";pre($_SESSION); echo"\n\n";
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
		$sql = "SELECT * FROM ".T_SECRET_PUZZLE_IMAGE.
			" WHERE puzzle_image_id='".$_POST[puzzle_image_id]."' AND mk_flg!='1' LIMIT 1;";
		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);
		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}
		foreach ($list as $key => $val) {
			$$key = replace_decode($val);
			$$key = ereg_replace("\"","&quot;",$$key);
//echo "■key == ".$val."<br>\n";
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
	$make_html->set_file(SECRET_PUZZLE_IMAGE);

	//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	if (!$puzzle_image_id) { $puzzle_image_id = "---"; }

	$INPUTS[PUZZLEIMAGEID] = array('result'=>'plane','value'=>$puzzle_image_id);
	if (MODE == "詳細" || MODE == "削除") {
		$INPUTS[FILENAME] = array('result'=>'plane','value'=>$image_name);
	} else {
		$INPUTS[FILENAME] = array('result'=>'plane','value'=>$_FILES["file_name"]["name"]);		// 新規時はFILES値
	}
	// 開示年月に「年」「月」を付ける
	$year_char = substr($image_category, 0, 4);
	$month_char = substr($image_category, 4, 2);
	$image_category_char = $year_char."年".$month_char."月";
	$INPUTS[IMAGECATEGORY]	 = array('result'=>'plane','value'=>$image_category_char);			// 例）2013年01月

	$INPUTS[DISPLAY]		 = array('result'=>'plane','value'=>$L_DISPLAY[$display]);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();

	// 制御ボタン定義
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" style=\"float:left\">\n";
	$html .= $HIDDEN;

	// 削除の場合は、１画面目に戻る
	if (MODE == "削除") {
		$html .= "<input type=\"hidden\" name=\"s_page\" value=\"1\">\n";
		$html .= "<input type=\"hidden\" name=\"image_category\" value=".$image_category.">\n";
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
//echo "■ＤＢ登録処理POST　<br>\n";pre($_POST); echo"\n\n";
//echo "■ＤＢ登録処理SESSION　<br>\n";pre($_SESSION); echo"\n\n";

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$problem_num = "";

	// 登録項目設定
	$INSERT_DATA['image_name'] 		= $_SESSION['master_file_name'];
	$INSERT_DATA['image_category']  = $_POST['image_category'];
	$INSERT_DATA['display']	  		= $_POST['display'];
	$INSERT_DATA['ins_syr_id'] 		= "add";
	$INSERT_DATA['ins_date']  		= "now()";
	$INSERT_DATA['ins_tts_id'] 		= $_SESSION['myid']['id'];
	$INSERT_DATA['upd_syr_id'] 		= "add";
	$INSERT_DATA['upd_date']   		= "now()";
	$INSERT_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];

	// ＤＢ追加処理
	$ERROR = $cdb->insert(T_SECRET_PUZZLE_IMAGE,$INSERT_DATA);


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
//echo"■ＤＢ変更・削除 処理POST　<br>\n";pre($_POST); echo"\n\n";
//echo"■ＤＢ変更・削除 処理SESSION　<br>\n";pre($_SESSION); echo"\n\n";

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// 更新処理
	if (MODE == "詳細") {
		// DBアップデート
		$INSERT_DATA['image_category']			= $_POST['image_category'];
		$INSERT_DATA['display']					= $_POST['display'];
		$INSERT_DATA['upd_syr_id']				= "update";
		$INSERT_DATA['upd_date']				= "now()";
		$INSERT_DATA['upd_tts_id']				= $_SESSION['myid']['id'];
		$where = " WHERE puzzle_image_id = '".$_POST['puzzle_image_id']."' LIMIT 1;";

		$ERROR = $cdb->update(T_SECRET_PUZZLE_IMAGE,$INSERT_DATA,$where);

		// フォルダの移動処理
		if ($_SESSION['image_category'] !== $_POST['image_category']) {
			// 移動するファイル
			$sourse_file = MATERIAL_SECRET_DIR."puzzle/".$_SESSION['image_category'];
			// 移動先ファイル
			$destination_file = MATERIAL_SECRET_DIR."puzzle/".$_POST['image_category'];
			// 移動先ディレクトリが存在しない場合作成する
			create_directory($_POST['image_category']);
			// ファイル移動
			rename($sourse_file,$destination_file);
		}

	// 削除処理
	} elseif (MODE == "削除") {
		$INSERT_DATA['mk_flg']	   = "1";
		$INSERT_DATA['mk_tts_id']  = $_SESSION['myid']['id'];
		$INSERT_DATA['mk_date']	   = "now()";
		$INSERT_DATA['upd_syr_id'] = "del";
		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date']   = "now()";
		$where = " WHERE puzzle_image_id = '".$_POST['puzzle_image_id']."' LIMIT 1;";

		$ERROR = $cdb->update(T_SECRET_PUZZLE_IMAGE,$INSERT_DATA,$where);

		// 画像フォルダを削除する
		$sourse_file = MATERIAL_SECRET_DIR."puzzle/".$_POST['image_category'];
		system("rm -rf {$sourse_file}");
		// セッション削除
		unset($_SESSION['image_category']);
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
//echo"■ image_upload()　POST　<br>\n";pre($_POST); echo"\n\n";

	//------------------------------------------------------------------------------------------------
	// この時点でファイルはテンポラリー領域に格納されています。下記処理で任意のフォルダに移動します。
	//------------------------------------------------------------------------------------------------

	// $_FILEから情報取得
	$file_name = $_FILES['file_name']['name'];
//echo "■file_name == ".$file_name."<br>\n";
	$file_tmp_name = $_FILES['file_name']['tmp_name'];
//echo "■file_tmp_name == ".$file_tmp_name."<br>\n";
	$file_error = $_FILES['file_name']['error'];

	// 拡張子を除いたファイル名
	$file_un_ex = preg_replace("/(.+)(\.[^.]+$)/", "$1", $file_name);
//echo "■file_un_ex == ".$file_un_ex."<br>\n";

	// エラー処理
	if (!$file_tmp_name) {
		$ERROR[] = "ファイルが指定されておりません。";
	} elseif ($file_error == 1) {
		$ERROR[] = "アップロードできるファイルの容量が設定範囲を超えてます。サーバー管理者へ相談してください。";
	} elseif ($file_error == 3) {
		$ERROR[] = "ファイルの一部分のみしかアップロードされませんでした。";
	} elseif ($file_error == 4) {
		$ERROR[] = "ファイルがアップロードされませんでした。";
	}

	// アップロードファイルを格納するフォルダのパスを指定
	$upload_path = MATERIAL_SECRET_DIR."puzzle/".$_POST['image_category']."/".$file_name;
//echo "■upload_path == ".$upload_path."<br>\n";
	// アップロード先が存在しない場合作成する
	create_directory($_POST['image_category']);

	// テンポラリ領域→ファイル格納パスに移動
	$result = @move_uploaded_file( $file_tmp_name, $upload_path);

	// アップロードテンポラリーファイル削除
	if ($file_tmp_name && file_exists($file_tmp_name)) {
		unlink($file_tmp_name);
	}
	// zipファイルを解凍する
	$remote_dir = SECRET_UNZIP_DIR."puzzle/".$_POST['image_category'];
	//$command = "cd ".$remote_dir."; unzip ".$remote_dir."/".$file_name;	//del okabe 2013/12/19
	$command = "cd ".$remote_dir."; unzip ".$file_name;		//add okabe 2013/12/19
	exec($command);

	// 解凍後のzipファイルは削除
	if (strlen($_POST['image_category']) > 0 && strlen($file_name) > 0) {	//add okabe 2013/11/20
		unlink($upload_path);
	}

	// フォルダが二重になった場合の処理（例 folder001.zipを解凍後にsecret_event/puzzle/201310/folder001/ここに展開された場合 )
	$file_check_path = MATERIAL_SECRET_DIR."puzzle/".$_POST['image_category']."/".$file_un_ex;
//echo "■file_check_path == ".$file_check_path."<br>\n";
	if (is_dir($file_check_path)) {
		$remote_dir = SECRET_UNZIP_DIR."puzzle/".$_POST['image_category']."/".$file_un_ex;
		// ディレクトリを移動し、展開したファイルを全て上のフォルダに移動(mv *.* ../;)し、残った下のフォルダを削除(rm -rf "file_un_ex";)する
		$command = "cd ".$remote_dir."; mv *.* ../;	cd ../; rm -rf ".$file_un_ex.";";
		exec($command);
	}
	// 展開された画像ファイルの共通名を取得し、セッションへ (DBに登録される画像名となる）
	$GET_EXTENSION = glob(MATERIAL_SECRET_DIR."puzzle/".$_POST['image_category']."/*_01_m.*");
//echo "■".MATERIAL_SECRET_DIR."puzzle/".$_POST['image_category']."/_01_m.*<br>\n";
//echo "■GET_EXTENSION　<br>\n";pre($GET_EXTENSION); echo"\n\n";
	$FILE_NAME_EXPLODE = explode('_01_m.',$GET_EXTENSION[0]);
	//update start kimura 2017/12/05 AWS移設 //end()関数には変数を渡すよう修正
	$temp_array = explode('/',$FILE_NAME_EXPLODE[0]);
	$master_file_name = end($temp_array);
	//$master_file_name = end(explode('/',$FILE_NAME_EXPLODE[0]));
	//update end kimura 2017/12/05 AWS移設
	unset($_SESSION['master_file_name']);
	$_SESSION['master_file_name'] = $master_file_name;

	// エラーがある時の処理
	if ($ERROR) {
		// 移動したフォルダごと削除
		$sourse_file = MATERIAL_SECRET_DIR."puzzle/".$_POST['image_category'];
		if (strlen($_POST['image_category']) > 0) {	//add okabe 2013/11/20
			system("rm -rf {$sourse_file}");
		}

		return $ERROR;
	}
}


/**
 * 管理ディレクトリ作成
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param string $image_category
 */
function create_directory($image_category) {

	// シークレットイベント管理イメージ保管ディレクトリ作成
	$material_img_path = MATERIAL_SECRET_DIR;
	if (!file_exists($material_img_path)) {
		@mkdir($material_img_path,0777);
		@chmod($material_img_path,0777);
	}
	// パズル画像 格納フォルダ作成
	$material_img_path = MATERIAL_SECRET_DIR."puzzle/";
	if (!file_exists($material_img_path)) {
		@mkdir($material_img_path,0777);
		@chmod($material_img_path,0777);
	}
	// 日付区分毎にフォルダ作成
	if ($image_category) {
		$material_img_path = MATERIAL_SECRET_DIR."puzzle/".$image_category."/";

		if (!file_exists($material_img_path)) {
			@mkdir($material_img_path,0777);
			@chmod($material_img_path,0777);
		}
	}
}


/**
 * ファイルの拡張子を取得(統一されている仕様なので完成イメージから取得）
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param string $image_category
 * @return string
 */
function get_extension ($image_category) {
	$GET_EXTENSION = glob(MATERIAL_SECRET_DIR."puzzle/".$image_category."/*_01_m.*");
//echo MATERIAL_SECRET_DIR."puzzle/".$image_category."/*_01_m.*<br>\n";
//echo "■GET_EXTENSION　<br>\n";pre($GET_EXTENSION); echo"\n\n";
	//update start kimura 2017/12/05 AWS移設 //end()関数には変数を渡すよう修正
	$temp_array = explode('.',$GET_EXTENSION[0]);
	$file_extension = end($temp_array);
	//$file_extension = end(explode('.',$GET_EXTENSION[0]));
	//update end kimura 2017/12/05 AWS移設

	return $file_extension;
}
?>
