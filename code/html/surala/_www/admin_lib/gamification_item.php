<?php
/**
 * すらら
 *
 * ゲーミフィケーション アイテム管理
 *
 * 履歴
 * 2019/03/13 make file hasegawa 生徒TOP改修
 *
 * @author Azet
 */
define('MASTER_NAME', 'ゲーミフィケーション アイテムマスタ');
include(LOG_DIR . "admin_lib/gamification_item_character_position.php");
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
			// ファイルアップ
			if (isset($_FILES['upload_file'])) {
				$item_id = null;
				if ($_POST['item_id']) {
					$item_id = $_POST['item_id'];
				}
				$UPLOAD_ERROR = image_upload($item_id);
			}
			if ($ERROR && $UPLOAD_ERROR) {
				$ERROR = array_merge($ERROR, $UPLOAD_ERROR);
			}elseif($UPLOAD_ERROR){
				$ERROR = $UPLOAD_ERROR;
			}
		} elseif (ACTION == "position_check") {
	$ERROR = position_check($ERROR);
		}

		// DB登録・修正・削除
		if (!$ERROR) {
			if (ACTION == "add") {
				$ERROR = add();
			} elseif (ACTION == "change" || ACTION == "del") {
				$ERROR = change();
			} elseif (ACTION == "position_add") {
				$ERROR = position_add();
                        } elseif (ACTION == "position_change" || ACTION == "設定削除") {
				$ERROR = position_change();
                        }
		}

		// 登録画面
		if (MODE == "add") {
			if (ACTION == "check") {
				if (!$ERROR) {
					$html .= check_html();		 // 確認画面
				} else {
					$html .= addform($ERROR); 	 // 新規登録画面
				}
			} elseif (ACTION == "add") {
				if (!$ERROR) {
					$html .= item_list($ERROR); // 一覧表示
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
					$html .= item_list($ERROR);      // 一覧表示
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
					$html .= item_list($ERROR);// 一覧表示
				}
				else {
					$html .= viewform($ERROR); 	// 詳細画面
				}
			} else {
				$html .= check_html();			// 確認画面
			}
                // インポート処理
                } elseif (MODE == "gamification_item_import") {
                        $ERROR = gamification_item_import();
			$html .= item_list($ERROR);
                        
                        
                // ポジション登録関連制御
                } elseif (MODE == "position_add") {
			if (ACTION == "position_check") {
				if (!$ERROR) {
					$html .= position_check_html();		 // 確認画面
				} else {
					$html .= position_addform($ERROR); 	 // 新規登録画面
				}
			} elseif (ACTION == "position_add") {
				if (!$ERROR) {
					$html .= position_list($ERROR);         // 一覧表示
				} else {
					$html .= position_addform($ERROR);  	 // 新規登録画面
				}
			} else {
				$html .= position_addform($ERROR);  		 // 新規登録画面
			}                    
		} elseif (MODE == "設定詳細") {
			if (ACTION == "position_check") {
				if (!$ERROR) {
					$html .= position_check_html();		 // 確認画面
				} else {
					$html .= position_viewform($ERROR); 	 // 詳細画面
				}
			} elseif (ACTION == "position_change") {
				if (!$ERROR) {
					$html .= position_list($ERROR); // 一覧表示
				} else {
					$html .= position_viewform($ERROR);	 // 詳細画面
				}
			} else {
				$html .= position_viewform($ERROR); 		 // 詳細画面
			}
		// 削除処理
		} elseif (MODE == "設定削除") {
                    if (ACTION == "position_check") {
				if (!$ERROR) {
					$html .= position_check_html(); 		// 確認画面
				}
				else {
					$html .= position_viewform($ERROR);  // 詳細画面
				}
			} elseif (ACTION == "position_change") {
				if (!$ERROR) {
					$html .= position_list($ERROR);// 一覧表示
				}
				else {
					$html .= position_viewform($ERROR); 	// 詳細画面
				}
			} else {
				$html .= position_check_html();			// 確認画面
			}		// 一覧表示
		} elseif (MODE == "position_list") {
			$html .= position_list($ERROR);
                        
                } elseif (MODE == "position_import") {
                        $ERROR = position_import_import();
			$html .= position_list($ERROR);        
		// 一覧表示
		} else {
			$html .= item_list($ERROR);
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
function item_list($ERROR) {
	// グローバル変数
	global $L_PAGE_VIEW, $L_DISPLAY, $L_GAM_RARITY, $L_GAM_ITEM_CLASS, $L_GAM_ITEM_KIND, $L_GAM_NED_POSITION_SETTING;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	unset($_SESSION['upload_file_path']);
	
        
        // 権限取得
	if ($authority) { $L_AUTHORITY = explode("::",$authority); }

        $html .= "<h3>".MASTER_NAME."</h3>\n";

	// エラーメッセージが存在する場合、メッセージ表示
	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
        } elseif (count($ERROR) == 0 && MODE == 'gamification_item_import') {
                $html .= "<b>データを登録しました</b>\n";
        }

	if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__add",$_SESSION['authority'])===FALSE)) {
		$html .= "インポートする場合はゲーミフィケーションアイテムtxtファイル(S-JIS)を指定しCSVインポートを押してください<br>";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" enctype=\"multipart/form-data\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"gamification_item_import\">\n";
		$html .= "<input type=\"file\" size=\"40\" name=\"gamification_item_file\">\n";
		$html .= "<input type=\"submit\" value=\"CSVインポート\">\n";
		$html .= "</form>\n";
		$html .= "<br />\n";
		$html .= "<form action=\"/admin/gamification_item_make_csv.php\" method=\"POST\">";
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
		$html .= "<input type=\"submit\" value=\"アイテム新規登録\">\n";
		$html .= "</form>\n";
		$html .= "<br />\n";
	}
        
        $ftp_path = FTP_URL.'gamification/item/';
        $html .= FTP_EXPLORER_MESSAGE;
        $html .= "<a href=\"".$ftp_path."\" target=\"_blank\">ゲーミフィケーションアイテムフォルダー($ftp_path)</a><br>\n";
        $html .= "<a href=\"#\" class=\"gamification_notes_open\">画像・音声ファイル命名規約</a><br>\n";
        $html .= "<div style=\"font-size:14px; display:none;\" class=\"gamification_notes\">";
        $html .= "<p>画像・音声ファイルの登録にはアイテムの登録を行ったうえで下記命名規約に沿ってご登録下さい。</p>\n";
        $html .= "<b>画像・音声ファイル格納先:/data/home/contents/www/material/gamification/item/</b>\n";
        $html .= "<table>\n";
        $html .= "<tr><th colspan=\"2\">【命名規約】</th></tr>\n";
        $html .= "<tr><td>画像</td><td>item_アイテムID.png</td></tr>\n";
        $html .= "<tr><td>画像(キャラクター装飾品でレベルごとに画像を変える場合)<br>該当するレベルの画像が存在なければその下の直近のレベルIDの画像、<br>さらにレベルIDが設定されているアイテム画像がない場合は、通常の画像を参照</td><td>item_アイテムID_レベルID.png</td></tr>\n";
        $html .= "<tr><td>アイコン画像 (ショップ・所持アイテム一覧で表示するもの)</td><td>icon_アイテムID.png</td></tr>\n";
        $html .= "<tr><td>音声</td><td>item_アイテムID.mp3</td></tr>\n";
        $html .= "</table><br>\n";
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
	// del start hirose 2019/07/17 生徒TOP改修
        // 表示ページ制御
//	$s_page_view_html .= "&nbsp;&nbsp;&nbsp;表示数<select name=\"s_page_view\">\n";
//	foreach ($L_PAGE_VIEW as $key => $val){
//		if ($_SESSION['sub_session']['s_page_view'] == $key) { $sel = " selected"; } else { $sel = ""; }
//		$s_page_view_html .= "<option value=\"".$key."\"".$sel.">".$val."</option>\n";
//	}
//	$s_page_view_html .= "</select><input type=\"submit\" value=\"Set\">\n";
	// del end hirose 2019/07/17 生徒TOP改修

        // キャラクターマスタ取得
        $L_CHARACTER = get_character_list();
        
	// 一覧取得
	$sql  = "SELECT * FROM " . T_GAMIFICATION_ITEM
                ." WHERE mk_flg ='0' ";// upd hirose 2019/07/17 生徒TOP改修
//                ." ORDER BY list_num";// del hirose 2019/07/17 生徒TOP改修

	//del start hirose 2019/07/19 生徒TOP改修
	// イメージ件数取得
//	$count = 0;
//	if ($result = $cdb->query($sql)) {
//		$count = $cdb->num_rows($result);
//	}
	//del end hirose 2019/07/19 生徒TOP改修
	
	//add start hirose 2019/07/17 生徒TOP改修
	// アイテム区分
	if(!empty($_SESSION['sub_session']['view_item_class'])){
		// SQL文に一覧表示する条件を追加
		$sql .= " AND item_class = '".$_SESSION['sub_session']['view_item_class']."'";
	}
	// アイテムID
	if(!empty($_SESSION['sub_session']['view_item_id'])){
		// SQL文に一覧表示する条件を追加
		$sql .= " AND item_id = '".$_SESSION['sub_session']['view_item_id']."'";
	}
	// アイテムname
	if(!empty($_SESSION['sub_session']['view_item_name'])){
		// SQL文に一覧表示する条件を追加
		$sql .= " AND item_name LIKE '%".$_SESSION['sub_session']['view_item_name']."%'";
	}
	//add end hirose 2019/07/17 生徒TOP改修

	// ページビュー判断
	if ($L_PAGE_VIEW[$_SESSION['sub_session']['s_page_view']]) {
		$page_view = $L_PAGE_VIEW[$_SESSION['sub_session']['s_page_view']];
	} else {
		$page_view = $L_PAGE_VIEW[0];
	}
	
	//add start hirose 2019/07/19 生徒TOP改修
	$count = 0;
	if ($result = $cdb->query($sql)) {
		$count = $cdb->num_rows($result);
	}
	//add end hirose 2019/07/19 生徒TOP改修

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
	$sql .= " ORDER BY list_num";// add hirose 2019/07/17 生徒TOP改修
	$sql .= " LIMIT ".$start.",".$page_view.";";
//	print $sql;

	// データ読み込み
	if ($result = $cdb->query($sql)) {

		// 取得データ件数が０の場合
		$check_count = 0;
		if ($result = $cdb->query($sql)) {
			$check_count = $cdb->num_rows($result);
		}
		if (!$check_count) {
			$html .= select_menu();// add hirose 2019/07/17 生徒TOP改修
			$html .= "現在、データは登録されていません。";
			return $html;
		}

		// 一覧表示開始
		$html .= "<br>\n";
		$html .= "修正する場合は、修正するキャラクターの詳細ボタンを押してください。<br>\n";
                
                $ned_position_setting = '';
                if(is_array($L_GAM_NED_POSITION_SETTING)) {
                    foreach($L_GAM_NED_POSITION_SETTING as $k => $v) {
                        if ($ned_position_setting) {
                            $ned_position_setting .= ',';
                        }
                        $ned_position_setting .= $L_GAM_ITEM_CLASS[$k];
                    }
                }                
		$html .= "※ 画像表示位置の設定は、アイテム区分:".$ned_position_setting."の場合に設定可能です。<br>\n";

		// 登録件数表示
		$html .= "<br>\n";
		$html .= select_menu();// add hirose 2019/07/17 生徒TOP改修
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
		$html .= "<th>アイテムID</th>\n";
                $html .= "<th>表示順</th>\n";
		$html .= "<th>アイテム名</th>\n";
		$html .= "<th>レア度</th>\n";
		$html .= "<th>アイテム区分</th>\n";
		$html .= "<th>アイテム種類</th>\n";
		$html .= "<th>アイテム使用可能キャラクター</th>\n";
		$html .= "<th>表示期間</th>\n";
		$html .= "<th>価格</th>\n";
                $html .= "<th>表示・非表示</th>\n";
                $html .= "<th>画像・音声設定</th>\n";
		$html .= "<th>画像表示位置設定</th>\n";

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
                
                // イメージ格納ディレクトリ作成
                create_directory();

		//--------------
		// 明細表示
		//--------------
		$i = 1;
		while ($list = $cdb->fetch_assoc($result)) {
                        
                        $file_setting = false;
                        foreach (glob(MATERIAL_GAM_ITEM_DIR.'item_'.$list['item_id'].'.*') as $val ) {
                            $file_setting = true;
                        }
                    
			// HTML作成
			$html .= "<tr class=\"secret_form_cell\">\n";
			$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"item_id\" value=\"".$list['item_id']."\">\n";
			$html .= "<td>".$list['item_id']."</td>\n";
			$html .= "<td>".$list['list_num']."</td>\n";
			$html .= "<td>".$list['item_name']."</td>\n";
			$html .= "<td style=\"text-align:center;\">".$L_GAM_RARITY[$list['rarity']]."</td>\n";
			$html .= "<td>".$L_GAM_ITEM_CLASS[$list['item_class']] ?: '----'."</td>\n";
			$html .= "<td>".$L_GAM_ITEM_KIND[$list['item_class']][$list['item_kind']] ?: '----'."</td>\n";
			$html .= "<td>".$L_CHARACTER[$list['can_use_character_id']] ?: '----'."</td>\n";
                        if ($list['display_start_ymd'] && $list['display_end_ymd'] && $list['display_start_ymd'] != '0000-00-00' && $list['display_end_ymd'] != '0000-00-00') {
                            $html .= "<td>".date('Y/m/d', strtotime($list['display_start_ymd']))."～".date('Y/m/d', strtotime($list['display_end_ymd']))."</td>\n";
                        } else {
                            $html .= "<td>----</td>\n";
                        }
			$html .= "<td>".$list['price']." pt</td>\n";
                        $html .= "<td style=\"text-align:center;\">".$L_DISPLAY[$list['display']]."</td>\n";
                        if ($file_setting) {
                            $html .= "<td style=\"color:blue;text-align:center;\">設定済</td>\n";
                        } else {
                            $html .= "<td style=\"color:red;text-align:center;\">未設定</td>\n";
                        }

                        $html .= "<td>\n";
			// 画像表示位置設定ボタン（権限が有る場合は、ボタン表示）
			if (!ereg("practice__view",$authority)
				&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__view",$_SESSION['authority'])===FALSE))
                                && $L_GAM_NED_POSITION_SETTING[$list['item_class']]
                                && $file_setting
			) {
				$html .= "<input type=\"hidden\" name=\"mode\" value=\"position_list\">\n";
				$html .= "<input type=\"submit\" value=\"表示位置設定\">\n";
			}
                        $html .= "</td>\n";
                        
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
			$html .= select_menu();// add hirose 2019/07/17 生徒TOP改修
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
function addform($ERROR) { //update kimura 2019/06/03 生徒画面TOP改修 インデントなおしました

	// グローバル変数
	global $L_DISPLAY, $L_GAM_RARITY, $L_GAM_ITEM_CLASS, $L_GAM_ITEM_KIND, $L_GAM_ITEM_USE_SETTING, $L_GAM_ITEM_USE_DEATH;

	$html .= "<br>\n";
	$html .= MASTER_NAME." 新規登録フォーム<br>\n";
	$html .= "<br>\n";

	// エラーメッセージ表示
	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	// 複製して新規登録から来た際にファイルの情報が残っているので削除
	if ($_POST['delete_session']) {
		unset($_SESSION['upload_file_path']);
	}

	// 入力画面表示        
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" enctype=\"multipart/form-data\" id=\"addform\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"add\">\n";

	// htmlクラス生成
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(GAMIFICATION_ITEM);

	// キャラクターのリストを作成
	$L_CHARACTER = get_character_list();
	// 年月日選択用のリストを作成
	list($L_YEAR, $L_MON, $L_DATE) = get_ymd_list();
	// アイテムの一覧を取得       
	$L_PARENT_ITEM = get_parent_item_list($_POST['item_class']);     
	$L_GAM_RARITY[0]        = '選択してください';
	$L_GAM_ITEM_CLASS[0]    = '選択してください';
	$L_CHARACTER[0]         = '選択してください';
	$L_PARENT_ITEM[0]       = '選択してください';
	ksort($L_GAM_RARITY);
	ksort($L_GAM_ITEM_CLASS);
	ksort($L_CHARACTER);
	ksort($L_PARENT_ITEM);

	// フォーム部品生成
	$INPUTS['ITEMID']	= array('result'=>'plane','value'=>"---");
	$INPUTS['ITEMNAME']	= array('result'=>'form', 'type' => 'text', 'name' => 'item_name', 'size' => '50', 'value'=>$_POST['item_name'] ?: null);
	$INPUTS['PARENTITEM']	= array('result'=>'form', 'type' => 'select', 'name' => 'parent_item', 'array' =>$L_GAM_ITEM_CLASS, 'check' => $_POST['item_class'] ?: null);
	$INPUTS['RARITY']	= array('result'=>'form', 'type' => 'select', 'name' => 'rarity', 'array'=>$L_GAM_RARITY, 'check' => $_POST['rarity'] ?: null);
	$INPUTS['ITEMCLASS']	= array('result'=>'form', 'type' => 'select', 'name' => 'item_class', 'array' =>$L_GAM_ITEM_CLASS, 'check' => $_POST['item_class'] ?: null);
	// アイテム区分選択時
	if ($_POST['item_class'] && $L_GAM_ITEM_KIND[$_POST['item_class']]) {
		$L_GAM_ITEM_KIND[$_POST['item_class']][0] = '選択してください';
		ksort($L_GAM_ITEM_KIND[$_POST['item_class']]);
		$INPUTS['ITEMKIND'] 	= array('result'=>'plane', 'type' => 'select', 'name' => 'item_kind', 'array'=>$L_GAM_ITEM_KIND[$_POST['item_class']], 'check' => $_POST['item_kind'] ?: null);
		$INPUTS['USECHARACTER']	= array('result'=>'form', 'type' => 'select', 'name' => 'can_use_character_id', 'array'=>$L_CHARACTER, 'check' => $_POST['can_use_character_id'] ?: null);
		// アイテム区分未選択時
	} elseif(is_array($L_GAM_ITEM_KIND)) {
		$select = '';
		foreach($L_GAM_ITEM_KIND as $item_class => $v) {
			if ($select != '') { $select .= '・'; }
			$select .= $L_GAM_ITEM_CLASS[$item_class];
		}
		$INPUTS['ITEMKIND']         = array('result'=>'plane','value'=>"<small>アイテム区分：".$select."選択時に選択可能です。</small>");
		$INPUTS['USECHARACTER']	= array('result'=>'plane','value'=>"<small>アイテム区分：".$select."選択時に選択可能です。</small>");
	}
	$INPUTS['STARTY']	= array('result'=>'form', 'type' => 'select', 'name' => 'start_y', 'array'=>$L_YEAR, 'check' => $_POST['start_y'] ?: null);
	$INPUTS['STARTM']	= array('result'=>'form', 'type' => 'select', 'name' => 'start_m', 'array'=>$L_MON, 'check' => $_POST['start_m'] ?: null);
	$INPUTS['STARTD']	= array('result'=>'form', 'type' => 'select', 'name' => 'start_d', 'array'=>$L_DATE, 'check' => $_POST['start_d'] ?: null);
	$INPUTS['ENDY']         = array('result'=>'form', 'type' => 'select', 'name' => 'end_y', 'array'=>$L_YEAR, 'check' => $_POST['end_y'] ?: null);
	$INPUTS['ENDM']         = array('result'=>'form', 'type' => 'select', 'name' => 'end_m', 'array'=>$L_MON, 'check' => $_POST['end_m'] ?: null);
	$INPUTS['ENDD']         = array('result'=>'form', 'type' => 'select', 'name' => 'end_d', 'array'=>$L_DATE, 'check' => $_POST['end_d'] ?: null);
	$INPUTS['PRICE']	= array('result'=>'form', 'type' => 'text', 'name' => 'price', 'size' => '10', 'value'=>$_POST['price'] ?: null);
	$INPUTS['DESCRIPTION']	= array('result'=>'form', 'type' => 'textarea', 'name' => 'description', 'style' => 'width:400px; height:60px;', 'value'=>$_POST['description'] ?: null);
	$INPUTS['ANNOTATION']	= array('result'=>'form', 'type' => 'textarea', 'name' => 'annotation', 'style' => 'width:400px; height:60px;', 'value'=>$_POST['annotation'] ?: null);
	if (is_array($L_PARENT_ITEM) && count($L_PARENT_ITEM) > 1) {
		$INPUTS['PARENTITEMID']	= array('result'=>'form', 'type' => 'select', 'name' => 'parent_item_id', 'array'=>$L_PARENT_ITEM, 'check' => $_POST['parent_item_id'] ?: null);
	} else {
		$INPUTS['PARENTITEMID']	= array('result'=>'plane','value'=>"<small>親アイテムが登録されておりません。</small>");
	}
	// add start hasegawa 2019/06/05 生徒TOP改修
	if ($_POST['parent_item_id'] > 0) {
		unset($L_PARENT_ITEM[$_POST['parent_item_id']]);
		if (is_array($L_PARENT_ITEM) && count($L_PARENT_ITEM) > 1) {
			$INPUTS['EFFECTITEMID']	= array('result'=>'form', 'type' => 'select', 'name' => 'effect_item_id', 'array'=>$L_PARENT_ITEM, 'check' => $_POST['effect_item_id'] ?: null);
		} else {
			$INPUTS['EFFECTITEMID']	= array('result'=>'plane','value'=>"<small>親アイテム2つ以上が登録されておりません。</small>");
		}
	} else {
		$INPUTS['EFFECTITEMID']	= array('result'=>'plane','value'=>"<small>親アイテムID指定時に選択可能です。</small>");
	}
	// add end hasegawa 2019/06/05
	
	// アイテム パートナー未選択時の使用制限 ラジオボタン生成
	if (is_array($L_GAM_ITEM_USE_SETTING)) {            
		$can_use_not_setting_value = '';
		foreach ($L_GAM_ITEM_USE_SETTING as $k => $v) {               

			$id = 'can_use_not_setting_'.$k;                
			$newform = new form_parts();
			$newform->set_form_type('radio');
			$newform->set_form_name('can_use_not_setting');
			$newform->set_form_id($id);
			$newform->set_form_check((string)$_POST['can_use_not_setting']);
			$newform->set_form_value((string)$k);   // 0が認識されなくなるので型指定
			$radio1 = $newform->make();
			$can_use_not_setting_value .= $radio1 . '<label for="'.$id.'">'.$v.'</label>　';
		}
		$INPUTS['USESETTING'] = array('result'=>'plane','value'=>$can_use_not_setting_value);
	}
	// アイテム パートナー死亡時の使用制限 ラジオボタン生成
	if (is_array($L_GAM_ITEM_USE_DEATH)) {            
		$can_use_death_value = '';
		foreach ($L_GAM_ITEM_USE_DEATH as $k => $v) {
			$id = 'can_use_death_'.$k;                
			$newform = new form_parts();
			$newform->set_form_type('radio');
			$newform->set_form_name('can_use_death');
			$newform->set_form_id($id);
			$newform->set_form_check((string)$_POST['can_use_death']);
			$newform->set_form_value((string)$k);   // 0が認識されなくなるので型指定
			$radio1 = $newform->make();
			$can_use_death_value .= $radio1 . '<label for="'.$id.'">'.$v.'</label>　';
		}
		$INPUTS['USEDEATH'] = array('result'=>'plane','value'=>$can_use_death_value);
	}
	$INPUTS['LISTNUM']	= array('result'=>'form', 'type' => 'text', 'name' => 'list_num', 'size' => '10', 'value'=>$_POST['list_num'] ?: null);

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
	$upload_html = '';
	if ($_SESSION['upload_file_path'] && file_exists($_SESSION['upload_file_path'])) {
		if(preg_match('/\.png|\.gif|\.jpeg|\.jpg/i', $_SESSION['upload_file_path'])){
			$upload_html .= '<img src="'.$_SESSION['upload_file_path'].'" style="max-width:600px;"><br>';
		} else {
			$upload_html .= 'アップロードファイル：'.$_SESSION['upload_file_path'].'<br>';
		}
	}
	$upload_html .= '<input type="file" name="upload_file" size="40">';        
	$INPUTS['UPLOAD']	= array('result'=>'plane','value'=>$upload_html);

	$make_html->set_rep_cmd($INPUTS);
	$html .= $make_html->replace();

	// onchange用Script
	$html .= "<script>\n";
	$html .= "$('[name=\"item_class\"]').change(function() {\n";
	$html .= " select_change_submit('addform', 'add', '');\n";
	$html .= "});\n";
	$html .= "</script>\n";
	// onchange用Script
	$html .= "<script>\n";
	$html .= "$('[name=\"parent_item_id\"]').change(function() {\n";
	$html .= " select_change_submit('addform', 'add', '');\n";
	$html .= "});\n";
	$html .= "</script>\n";
	// 制御ボタン定義
	$html .= "<input type=\"submit\" value=\"追加確認\">\n";
	$html .= "<input type=\"reset\" value=\"クリア\">\n";
	$html .= "</form>\n";
	$html .= "<br>\n";

	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"item_list\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";
	// $html.= "return $html;"; //del kimura 2019/06/03 生徒画面TOP改修
	return $html; //add kimura 2019/06/03 生徒画面TOP改修
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
	global $L_DISPLAY, $L_GAM_RARITY, $L_GAM_ITEM_CLASS, $L_GAM_ITEM_KIND, $L_GAM_ITEM_USE_SETTING, $L_GAM_ITEM_USE_DEATH;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$input_list = array();
	// POST値取得
	if (ACTION=="check" || ACTION=="back" || ACTION == "change_select") {
		// POST値取得
		foreach ($_POST as $key => $val) {
			$$key = $val;
			$input_list[$key] = $val;
		}
	} else {
		$sql = "SELECT * FROM " . T_GAMIFICATION_ITEM .
			" WHERE item_id ='".$cdb->real_escape($_POST['item_id'])."' AND mk_flg='0' LIMIT 1;";
		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);
		if (!$list) {
			$html .= "既に削除されているか、不正な情報が混ざっています。";
			return $html;
		}
		foreach ($list as $key => $val) {
			$$key = replace_decode($val);
			$$key = ereg_replace("\"","&quot;",$$key);
			$input_list[$key] = $$key;
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
	// 内容を複製して新規登録 >>>
	if (is_array($input_list)) {
		$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" enctype=\"multipart/form-data\" id=\"copyform\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"add\">\n";
		$html .= "<input type=\"hidden\" name=\"delete_session\" value=\"1\">\n";
		foreach($input_list as $k => $v) {
			if($k == 'display_start_ymd'){
				$start_day = explode("-",$v);
				$html .= "<input type=\"hidden\" name=\"start_y\" value=\"".$start_day[0]."\">\n";
				$html .= "<input type=\"hidden\" name=\"start_m\" value=\"".$start_day[1]."\">\n";
				$html .= "<input type=\"hidden\" name=\"start_d\" value=\"".$start_day[2]."\">\n";
			}elseif($k == 'display_end_ymd'){
				$end_day = explode("-",$v);
				$html .= "<input type=\"hidden\" name=\"end_y\" value=\"".$end_day[0]."\">\n";
				$html .= "<input type=\"hidden\" name=\"end_m\" value=\"".$end_day[1]."\">\n";
				$html .= "<input type=\"hidden\" name=\"end_d\" value=\"".$end_day[2]."\">\n";
			}else{
				$html .= "<input type=\"hidden\" name=\"".$k."\" value=\"".$v."\">\n";
			}
		}            
		$html .= "<input style=\"margin:20px 0;\" type=\"submit\" value=\"内容を複製して新規登録\">";
		$html .= "</form>";
	}
	// <<<        
	$html .= "※ アイテム区分を変更する場合は表示位置情報は削除されます。\n";

	// 入力画面表示
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" enctype=\"multipart/form-data\" id=\"addform\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"詳細\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";
	$html .= "<input type=\"hidden\" name=\"item_id\" value=\"".$item_id."\">\n";

	// htmlクラス生成
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(GAMIFICATION_ITEM);

	$L_CHARACTER = get_character_list();
	// アイテムの一覧を取得       
	$L_PARENT_ITEM = get_parent_item_list($_POST['item_class'], $item_id);     

	$L_GAM_RARITY[0]        = '選択してください';
	$L_GAM_ITEM_CLASS[0]    = '選択してください';
	$L_CHARACTER[0]         = '選択してください';
	$L_PARENT_ITEM[0]       = '選択してください';
	ksort($L_GAM_RARITY);
	ksort($L_GAM_ITEM_CLASS);
	ksort($L_CHARACTER);
	ksort($L_PARENT_ITEM);

	// 年月日選択用のリストを作成
	list($L_YEAR, $L_MON, $L_DATE) = get_ymd_list();

	// フォーム部品生成
	$INPUTS['ITEMID']	= array('result'=>'plane','value'=>$item_id ?: "---");
	$INPUTS['ITEMNAME']	= array('result'=>'form', 'type' => 'text', 'name' => 'item_name', 'size' => '50', 'value'=>$item_name ?: null);
	$INPUTS['RARITY']	= array('result'=>'form', 'type' => 'select', 'name' => 'rarity', 'array'=>$L_GAM_RARITY, 'check' => $rarity ?: null);
	$INPUTS['ITEMCLASS']	= array('result'=>'form', 'type' => 'select', 'name' => 'item_class', 'array' =>$L_GAM_ITEM_CLASS, 'check' => $item_class ?: null);
	// アイテム区分選択時
	if ($item_class && $L_GAM_ITEM_KIND[$item_class]) {
		$L_GAM_ITEM_KIND[$item_class][0] = '選択してください';
		ksort($L_GAM_ITEM_KIND[$item_class]);
		$INPUTS['ITEMKIND'] 	= array('result'=>'form', 'type' => 'select', 'name' => 'item_kind', 'array'=>$L_GAM_ITEM_KIND[$item_class], 'check' => $item_kind ?: null);
		$INPUTS['USECHARACTER']	= array('result'=>'form', 'type' => 'select', 'name' => 'can_use_character_id', 'array'=>$L_CHARACTER, 'check' => $can_use_character_id ?: null);
		// アイテム区分未選択時
	} elseif(is_array($L_GAM_ITEM_KIND)) {
		$select = '';
		foreach($L_GAM_ITEM_KIND as $item_class_ => $v) {
			if ($select != '') { $select .= '・'; }
			$select .= $L_GAM_ITEM_CLASS[$item_class_];
		}
		$INPUTS['ITEMKIND']         = array('result'=>'plane','value'=>"<small>アイテム区分：".$select."選択時に選択可能です。</small>");
		$INPUTS['USECHARACTER']	= array('result'=>'plane','value'=>"<small>アイテム区分：".$select."選択時に選択可能です。</small>");
	}
	if (is_array($L_PARENT_ITEM) && count($L_PARENT_ITEM) > 1) {
		$INPUTS['PARENTITEMID']	= array('result'=>'form', 'type' => 'select', 'name' => 'parent_item_id', 'array'=>$L_PARENT_ITEM, 'check' => $parent_item_id ?: null);
	} else {
		$INPUTS['PARENTITEMID']	= array('result'=>'plane','value'=>"<small>親アイテムが登録されておりません。</small>");
	}       
	// add start hasegawa 2019/06/05 生徒TOP改修
	if ($parent_item_id > 0) {
		unset($L_PARENT_ITEM[$parent_item_id]);
		if (is_array($L_PARENT_ITEM) && count($L_PARENT_ITEM) > 1) {
			$INPUTS['EFFECTITEMID']	= array('result'=>'form', 'type' => 'select', 'name' => 'effect_item_id', 'array'=>$L_PARENT_ITEM, 'check' => $effect_item_id ?: null);
		} else {
			$INPUTS['EFFECTITEMID']	= array('result'=>'plane','value'=>"<small>親アイテム2つ以上が登録されておりません。</small>");
		}
	} else {
		$INPUTS['EFFECTITEMID']	= array('result'=>'plane','value'=>"<small>親アイテムID指定時に選択可能です。</small>");
	}
	// add start hasegawa 2019/06/05

	if ($display_start_ymd) {
		if (preg_match('/\//', $display_start_ymd)) {
			$delimiter = '/';
		}
		if (preg_match('/-/', $display_start_ymd)) {
			$delimiter = '-';
		}
		list($start_y, $start_m, $start_d) = explode($delimiter, $display_start_ymd);
	}

	if ($display_end_ymd) {
		if (preg_match('/\//', $display_end_ymd)) {
			$delimiter = '/';
		}
		if (preg_match('/-/', $display_end_ymd)) {
			$delimiter = '-';
		}
		list($end_y, $end_m, $end_d) = explode($delimiter, $display_end_ymd);
	}

	$INPUTS['STARTY']	= array('result'=>'form', 'type' => 'select', 'name' => 'start_y', 'array'=>$L_YEAR, 'check' => $start_y ?: null);
	$INPUTS['STARTM']	= array('result'=>'form', 'type' => 'select', 'name' => 'start_m', 'array'=>$L_MON, 'check' => $start_m ?: null);
	$INPUTS['STARTD']	= array('result'=>'form', 'type' => 'select', 'name' => 'start_d', 'array'=>$L_DATE, 'check' => $start_d ?: null);
	$INPUTS['ENDY']         = array('result'=>'form', 'type' => 'select', 'name' => 'end_y', 'array'=>$L_YEAR, 'check' => $end_y ?: null);
	$INPUTS['ENDM']         = array('result'=>'form', 'type' => 'select', 'name' => 'end_m', 'array'=>$L_MON, 'check' => $end_m ?: null);
	$INPUTS['ENDD']         = array('result'=>'form', 'type' => 'select', 'name' => 'end_d', 'array'=>$L_DATE, 'check' => $end_d ?: null);
	$INPUTS['PRICE']	= array('result'=>'form', 'type' => 'text', 'name' => 'price', 'size' => '10', 'value'=>$price ?: null);
	$INPUTS['DESCRIPTION']	= array('result'=>'form', 'type' => 'textarea', 'name' => 'description', 'style' => 'width:400px; height:60px;', 'value'=>$description ?: null);
	$INPUTS['ANNOTATION']	= array('result'=>'form', 'type' => 'textarea', 'name' => 'annotation', 'style' => 'width:400px; height:60px;', 'value'=>$annotation ?: null); //add kimura 2019/06/03 生徒画面TOP改修
	// アイテム パートナー未選択時の使用制限 ラジオボタン生成
	if (is_array($L_GAM_ITEM_USE_SETTING)) {            
		$can_use_not_setting_value = '';
		foreach ($L_GAM_ITEM_USE_SETTING as $k => $v) {  
			$id = 'can_use_not_setting_'.$k;                
			$newform = new form_parts();
			$newform->set_form_type('radio');
			$newform->set_form_name('can_use_not_setting');
			$newform->set_form_id($id);
			$newform->set_form_check((string)$can_use_not_setting);
			$newform->set_form_value((string)$k);   // 0が認識されなくなるので型指定
			$radio1 = $newform->make();
			$can_use_not_setting_value .= $radio1 . '<label for="'.$id.'">'.$v.'</label>　';
		}
		$INPUTS['USESETTING'] = array('result'=>'plane','value'=>$can_use_not_setting_value);
	}
	// アイテム パートナー死亡時の使用制限 ラジオボタン生成
	if (is_array($L_GAM_ITEM_USE_DEATH)) {            
		$can_use_death_value = '';
		foreach ($L_GAM_ITEM_USE_DEATH as $k => $v) {
			$id = 'can_use_death_'.$k;                
			$newform = new form_parts();
			$newform->set_form_type('radio');
			$newform->set_form_name('can_use_death');
			$newform->set_form_id($id);
			$newform->set_form_check((string)$can_use_death);
			$newform->set_form_value((string)$k);   // 0が認識されなくなるので型指定
			$radio1 = $newform->make();
			$can_use_death_value .= $radio1 . '<label for="'.$id.'">'.$v.'</label>　';
		}
		$INPUTS['USEDEATH'] = array('result'=>'plane','value'=>$can_use_death_value);
	}
	$INPUTS['LISTNUM']	= array('result'=>'form', 'type' => 'text', 'name' => 'list_num', 'size' => '10', 'value'=>$list_num ?: null);
	$upload_html = '';
	foreach (glob(MATERIAL_GAM_ITEM_DIR.'item_'.$item_id.'.*') as $val ) {
		if(preg_match('/\.png|\.gif|\.jpeg|\.jpg/i', $val)){
			$upload_html .= '<img src="'.$val.'" style="max-width:600px;"><br>';
		} else {
			$upload_html .= 'アップロードファイル：'.$val.'<br>';
		}
		$_SESSION['upload_file_path'] = $val;
	}
	$upload_html .= '<input type="file" name="upload_file" size="40"><br>';
	$INPUTS['UPLOAD']	= array('result'=>'plane','value'=>$upload_html);

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

	// onchange用Script
	$html .= "<script>\n";
	$html .= "$('[name=\"item_class\"]').change(function() {\n";
	$html .= " select_change_submit('addform', '詳細', 'change_select');\n";
	$html .= "});\n";
	$html .= "</script>\n";
	// onchange用Script
	$html .= "<script>\n";
	$html .= "$('[name=\"parent_item_id\"]').change(function() {\n";
	$html .= " select_change_submit('addform', '詳細', 'change_select');\n";
	$html .= "});\n";
	$html .= "</script>\n";
	// 制御ボタン定義
	$html .= "<input type=\"submit\" value=\"変更確認\">";
	$html .= "<input type=\"reset\" value=\"クリア\">";
	$html .= "</form><br>\n";
	// 一覧に戻る
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"item_list\">\n";
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
	global $L_DISPLAY, $L_GAM_RARITY, $L_GAM_ITEM_CLASS, $L_GAM_ITEM_KIND, $L_GAM_ITEM_USE_SETTING, $L_GAM_ITEM_USE_DEATH;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$CHECK_LIST = array();
	//プルダウンの年月日
	list($L_YEAR, $L_MON, $L_DATE) = get_ymd_list();
	if (is_array($LINE) && count($LINE) > 0) {


		// 入力された日付を加工 >>>
		$CHECK_LIST = $LINE;
		$CHECK_LIST['start_y'] = null;
		$CHECK_LIST['start_m'] = null;
		$CHECK_LIST['start_d'] = null;
		$CHECK_LIST['end_y'] = null;
		$CHECK_LIST['end_m'] = null;
		$CHECK_LIST['end_d'] = null;

		if ($LINE['display_start_ymd']) {
			if (preg_match('/\//', $LINE['display_start_ymd'])) {
				$delimiter = '/';
			}elseif (preg_match('/-/', $LINE['display_start_ymd'])) {
				$delimiter = '-';
			}else{
				$delimiter = false;
				$ERROR[] = "アイテム表示期間（開始）の形式が異なります。XXXX-XX-XXの形式で入力してください。";
			}
			if($delimiter){
				list($CHECK_LIST['start_y'], $CHECK_LIST['start_m'], $CHECK_LIST['start_d']) = explode($delimiter, $LINE['display_start_ymd']);
			}
		}
		if ($LINE['display_end_ymd']) {
			if (preg_match('/\//', $LINE['display_end_ymd'])) {
				$delimiter = '/';
			}elseif (preg_match('/-/', $LINE['display_end_ymd'])) {
				$delimiter = '-';
			}else{
				$delimiter = false;
				$ERROR[] = "アイテム表示期間（終了）の形式が異なります。XXXX-XX-XXの形式で入力してください。";
			}
			if($delimiter){
				list($CHECK_LIST['end_y'], $CHECK_LIST['end_m'], $CHECK_LIST['end_d']) = explode($delimiter, $LINE['display_end_ymd']);
			}
		}
		// <<<


	} else {
		$CHECK_LIST = $_POST;
	}

	// アイテム名
	if (!$CHECK_LIST['item_name']) {
		$ERROR[] = "アイテム名が未入力です。";
	} elseif(!$CHECK_LIST['item_id']) {
		// 重複チェックは行わない
		//    $sql = "SELECT * FROM ".T_GAMIFICATION_ITEM." WHERE item_name = '".$cdb->real_escape($CHECK_LIST['item_name'])."' AND mk_flg='0';";
		//    if ($result = $cdb->query($sql)) {
		//            $count = $cdb->num_rows($result);
		//    }
		//    if ($count > 0) {
		//        $ERROR[] = "入力されたアイテム名は既に登録済です";
		//    }
	}
	if ($CHECK_LIST['item_name'] && mb_strlen($CHECK_LIST['item_name']) > 25) {
		$ERROR[] = "アイテム名は25文字まで登録可能です。";
	}

	// 親アイテムID
	if ($CHECK_LIST['parent_item_id'] && !is_numeric($CHECK_LIST['parent_item_id'])) {
		$ERROR[] = "親アイテムIDの値が不正です。";
	} elseif ($CHECK_LIST['item_id'] && $CHECK_LIST['parent_item_id'] && $CHECK_LIST['item_id'] == $CHECK_LIST['parent_item_id']) {
		$ERROR[] = "親アイテムIDには自分のIDを指定しないでください。";
	// add start hasegawa 2019/06/05 生徒TOP改修
	} elseif ($CHECK_LIST['parent_item_id'] > 0 && !($CHECK_LIST['effect_item_id'] > 0)) {
		$ERROR[] = "親アイテムID指定時には作用させるアイテムIDを指定してください。";
	// add end hasegawa 2019/06/05
	} elseif ($CHECK_LIST['parent_item_id'] > 0) {
		$count = 0;
		$sql = "SELECT * FROM ".T_GAMIFICATION_ITEM
			// ." WHERE item_id = '".$cdb->real_escape($CHECK_LIST['parent_item_id'])."' AND mk_flg='0';"; // upd hasegawa 2019/06/05 生徒TOP改修
			." WHERE item_id = '".$cdb->real_escape($CHECK_LIST['parent_item_id'])."' AND parent_item_id='0' AND mk_flg='0';";
		if ($result = $cdb->query($sql)) {
			$count = $cdb->num_rows($result);
		}
		if (!$count) {
			$ERROR[] = "入力された親アイテムIDは存在しません。";
		}            
	}

	// add start hasegawa 2019/06/05 生徒TOP改修
	// 作用させるアイテムID
	if ($CHECK_LIST['effect_item_id'] && !is_numeric($CHECK_LIST['effect_item_id'])) {
		$ERROR[] = "作用させるアイテムIDの値が不正です。";
	} elseif ($CHECK_LIST['item_id'] && $CHECK_LIST['effect_item_id'] && $CHECK_LIST['item_id'] == $CHECK_LIST['effect_item_id']) {
		$ERROR[] = "作用させるアイテムIDには自分のIDを指定しないでください。";
	} elseif ($CHECK_LIST['parent_item_id'] && $CHECK_LIST['effect_item_id'] && $CHECK_LIST['parent_item_id'] == $CHECK_LIST['effect_item_id']) {
		$ERROR[] = "作用させるアイテムIDには親アイテムIDを指定しないでください。";
	} elseif ($CHECK_LIST['effect_item_id'] > 0 && !($CHECK_LIST['parent_item_id'] > 0)) {
		$ERROR[] = "作用させるアイテムID指定時には親アイテムIDを指定してください。";
	} elseif ($CHECK_LIST['effect_item_id'] > 0) {
		$count = 0;
		$sql = "SELECT * FROM ".T_GAMIFICATION_ITEM
			." WHERE item_id = '".$cdb->real_escape($CHECK_LIST['effect_item_id'])."' AND parent_item_id='0' AND mk_flg='0';";
		if ($result = $cdb->query($sql)) {
			$count = $cdb->num_rows($result);
		}
		if (!$count) {
			$ERROR[] = "入力された作用させるアイテムIDは存在しません。";
		}            
	}
	// add end hasegawa 2019/06/05

	// アイテム区分
	if (!$CHECK_LIST['item_class']) {
		$ERROR[] = "アイテム区分が未選択です。";
	} else {
		if (!$L_GAM_ITEM_CLASS[$CHECK_LIST['item_class']]) {
			$ERROR[] = "アイテム区分の値が不正です。";
		}
		// アイテム種類
		if ($L_GAM_ITEM_KIND[$CHECK_LIST['item_class']] && !$CHECK_LIST['item_kind']) {
			$ERROR[] = "アイテム種類が未選択です。";
		} elseif ($CHECK_LIST['item_kind'] && !$L_GAM_ITEM_KIND[$CHECK_LIST['item_class']][$CHECK_LIST['item_kind']]) {
			$ERROR[] = "アイテム種類の値が不正です。";
		}
		// 使用可能キャラクター
		if ($CHECK_LIST['can_use_character_id'] > 0) {
			$count = 0;
			$sql = "SELECT * FROM ".T_GAMIFICATION_CHARACTER." WHERE character_id = '".$cdb->real_escape($CHECK_LIST['can_use_character_id'])."' AND mk_flg='0';";
			if ($result = $cdb->query($sql)) {
				$count = $cdb->num_rows($result);
			}
			if (!$count) {
				$ERROR[] = "入力されたアイテム使用可能キャラクターは存在しません。";
			}
		}
	}
	
	// レア度
	// if (!$CHECK_LIST['rarity']) {
	if (!$CHECK_LIST['parent_item_id'] && !$CHECK_LIST['rarity']) {
		$ERROR[] = "レア度が未選択です。";
	} elseif ($CHECK_LIST['rarity'] && !$L_GAM_RARITY[$CHECK_LIST['rarity']]) {
		$ERROR[] = "レア度の値が不正です";
	}
	
	// アイテム表示期間
	if($CHECK_LIST['display_start_ymd'] == "0000-00-00" && $CHECK_LIST['display_end_ymd'] == "0000-00-00"){
		//現状仕様開始終了ともにnullなら登録してもOKなので何もしない。
	} elseif ($CHECK_LIST['start_y']
		|| $CHECK_LIST['start_m']
		|| $CHECK_LIST['start_d']
		|| $CHECK_LIST['end_y']
		|| $CHECK_LIST['end_m']
		|| $CHECK_LIST['end_d']
	) {
		$date_check = true;
		if (!$CHECK_LIST['start_y'] || !$CHECK_LIST['start_m'] || !$CHECK_LIST['start_d']) {
			$date_check = false;
			$ERROR[] = "アイテム表示期間(開始)が未選択です。";
		} elseif (checkdate($CHECK_LIST['start_m'], $CHECK_LIST['start_d'], $CHECK_LIST['start_y']) === false) {
			$date_check = false;
			$ERROR[] = "アイテム表示期間(開始)の値が不正です。";
		} elseif(!isset($L_YEAR[$CHECK_LIST['start_y']])){
			$date_check = false;
			$ERROR[] = "アイテム表示期間(開始)の値は今年から5年先までの値にしてください。";
		}
		if (!$CHECK_LIST['end_y'] || !$CHECK_LIST['end_m'] || !$CHECK_LIST['end_d']) {
			$date_check = false;
			$ERROR[] = "アイテム表示期間(終了)が未選択です。";
		} elseif (checkdate($CHECK_LIST['end_m'], $CHECK_LIST['end_d'], $CHECK_LIST['end_y']) === false) {
			$date_check = false;
			$ERROR[] = "アイテム表示期間(終了)の値が不正です。";
		} elseif(!isset($L_YEAR[$CHECK_LIST['end_y']])){
			$date_check = false;
			$ERROR[] = "アイテム表示期間(終了)の値は今年から5年先までの値にしてください。";
		}

		if ($date_check) {
			$start_ymd = date('Ymd', strtotime($CHECK_LIST['start_y'].$CHECK_LIST['start_m'].$CHECK_LIST['start_d']));
			$end_ymd = date('Ymd', strtotime($CHECK_LIST['end_y'].$CHECK_LIST['end_m'].$CHECK_LIST['end_d']));

			if (strtotime(date('Ymd')) >= strtotime($start_ymd)) {
				$ERROR[] = "アイテム表示期間(開始)は本日より後の日付を設定してください。";
			}
			if (strtotime(date('Ymd')) >= strtotime($end_ymd)) {
				$ERROR[] = "アイテム表示期間(終了)は本日より後の日付を設定してください。";
			} 
			if (strtotime($start_ymd) > strtotime($end_ymd)) {
				$ERROR[] = "アイテム表示期間(終了)はアイテム表示期間(開始)よりうしろに設定してください。";
			}
		}
	}

	// 価格
	// if ($CHECK_LIST['price'] == '') {
	if (!$CHECK_LIST['parent_item_id'] && $CHECK_LIST['price'] == '') {
		$ERROR[] = "価格の値が未入力です。";
	} elseif (preg_match("/[^0-9]/",$CHECK_LIST['price'])) {
		$ERROR[] = "価格の値が不正です。";
	}

	// アイテムの説明
	if ($CHECK_LIST['description'] && mb_strlen($CHECK_LIST['description']) > 255) {
		$ERROR[] = "アイテムの説明は255文字まで入力可能です。";
	}

	//add start kimura 2019/06/03 生徒画面TOP改修 {{{
	if ($CHECK_LIST['annotation'] && mb_strlen($CHECK_LIST['annotation']) > 255) {
		$ERROR[] = "アイテムの注釈は255文字まで入力可能です。";
	}
	//add end   kimura 2019/06/03 生徒画面TOP改修 }}}

	// パートナー未選択時の使用制限
	if ($CHECK_LIST['can_use_not_setting'] == '') {
		$ERROR[] = "パートナー未選択時の使用制限が未入力です。";
	} elseif (!$L_GAM_ITEM_USE_SETTING[$CHECK_LIST['can_use_not_setting']]) {
		$ERROR[] = "パートナー未選択時の使用制限の値が不正です。";
	}

	// パートナー死亡時の使用制限
	if ($CHECK_LIST['can_use_death'] == '') {
		$ERROR[] = "パートナー死亡時の使用制限が未入力です。";
	} elseif (!$L_GAM_ITEM_USE_DEATH[$CHECK_LIST['can_use_death']]) {
		$ERROR[] = "パートナー死亡時の使用制限の値が不正です。";
	}        

	// ユーザー備考
	if ($CHECK_LIST['usr_bko'] && mb_strlen($CHECK_LIST['usr_bko']) > 255) {
		$ERROR[] = "ユーザー備考255文字まで入力可能です。";
	}
	// アイテム一覧での並び順
	if ($CHECK_LIST['list_num'] == '') {
		$ERROR[] = "表示順が未入力です。";
	} elseif (preg_match("/[^0-9]/",$CHECK_LIST['list_num'])) {
		$ERROR[] = "表示順の値が不正です。";
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

	global $L_PAGE_VIEW, $L_DISPLAY, $L_GAM_RARITY, $L_GAM_ITEM_CLASS, $L_GAM_ITEM_KIND, $L_GAM_ITEM_USE_SETTING, $L_GAM_ITEM_USE_DEATH;

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
		$sql = "SELECT * FROM ".T_GAMIFICATION_ITEM.
			" WHERE item_id='".$cdb->real_escape($_POST['item_id'])."' AND mk_flg = '0' LIMIT 1;";
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
	// キャラクター一覧取得
	$L_CHALACTER = get_character_list();                
	// アイテム一覧取得
	$L_PARENT_ITEM = get_parent_item_list($item_class);                

	// ボタン表示文言判定
	if (MODE == "削除") {
		$button = "削除";
		if(isset($display_start_ymd) && $display_start_ymd != "0000-00-00"){
			$start_day = explode("-",$display_start_ymd);
			$start_y =$start_day[0];
			$start_m =$start_day[1];
			$start_d =$start_day[2];
		}
		if(isset($display_end_ymd) && $display_end_ymd != "0000-00-00"){
			$end_day = explode("-",$display_end_ymd);
			$end_y =$end_day[0];
			$end_m =$end_day[1];
			$end_d =$end_day[2];
		}
		foreach (glob(MATERIAL_GAM_ITEM_DIR.'item_'.$item_id.'.*') as $val ) {
			$_SESSION['upload_file_path'] = $val;
		}                
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
	$make_html->set_file(GAMIFICATION_ITEM);        
	$INPUTS['ITEMID']           = array('result'=>'plane','value'=>$item_id ?:'----');
	$INPUTS['ITEMNAME']         = array('result'=>'plane','value'=>$item_name);
	$INPUTS['RARITY']           = array('result'=>'plane','value'=>$L_GAM_RARITY[$rarity]?:null);
	$INPUTS['ITEMCLASS']        = array('result'=>'plane','value'=>$L_GAM_ITEM_CLASS[$item_class]?:null);
	$INPUTS['ITEMKIND']         = array('result'=>'plane','value'=>$L_GAM_ITEM_KIND[$item_class][$item_kind]?:'<small>指定なし</small>');
	$INPUTS['USECHARACTER']     = array('result'=>'plane','value'=>$L_CHALACTER[$can_use_character_id]?:'<small>指定なし</small>');
	$INPUTS['PARENTITEMID']     = array('result'=>'plane','value'=>$L_PARENT_ITEM[$parent_item_id]?:'<small>指定なし</small>');
	$INPUTS['EFFECTITEMID']     = array('result'=>'plane','value'=>$L_PARENT_ITEM[$effect_item_id]?:'<small>指定なし</small>');	// add hasegawa 2019/06/05 生徒TOP改修
	$INPUTS['STARTY']           = array('result'=>'plane','value'=>$start_y ?: '--');
	$INPUTS['STARTM']           = array('result'=>'plane','value'=>$start_m ?: '--');
	$INPUTS['STARTD']           = array('result'=>'plane','value'=>$start_d ?: '--');
	$INPUTS['ENDY']             = array('result'=>'plane','value'=>$end_y ?: '--');
	$INPUTS['ENDM']             = array('result'=>'plane','value'=>$end_m ?: '--');
	$INPUTS['ENDD']             = array('result'=>'plane','value'=>$end_d ?: '--');
	$INPUTS['PRICE']            = array('result'=>'plane','value'=>$price);
	$INPUTS['DESCRIPTION']      = array('result'=>'plane','value'=>$description);
	$INPUTS['ANNOTATION']       = array('result'=>'plane','value'=>$annotation); //add kimura 2019/06/03 生徒画面TOP改修
	$INPUTS['USESETTING']       = array('result'=>'plane','value'=>$L_GAM_ITEM_USE_SETTING[$can_use_not_setting]);
	$INPUTS['USEDEATH']         = array('result'=>'plane','value'=>$L_GAM_ITEM_USE_DEATH[$can_use_death]);
	$INPUTS['LISTNUM']          = array('result'=>'plane','value'=>$list_num);
	if ($_SESSION['upload_file_path'] && file_exists($_SESSION['upload_file_path'])) {
		if(preg_match('/\.png|\.gif|\.jpeg|\.jpg/i', $_SESSION['upload_file_path'])){
			$INPUTS['UPLOAD']       = array('result'=>'plane','value'=>'<img src="'.$_SESSION['upload_file_path'].'" style="max-width:600px;">');
		} else {
			$INPUTS['UPLOAD']       = array('result'=>'plane','value'=>'アップロードファイル：'.$_SESSION['upload_file_path'].'<br>');
		}
	}
	$INPUTS['DISPLAY']          = array('result'=>'plane','value'=>$L_DISPLAY[$display]);
	$INPUTS['USERBKO']          = array('result'=>'plane','value'=>$usr_bko);

	$make_html->set_rep_cmd($INPUTS);
	$html .= $make_html->replace();

	// 制御ボタン定義
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" style=\"float:left\">\n";
	$html .= $HIDDEN;
	// 削除の場合は、１画面目に戻る
	if (MODE == "削除") {
		$html .= "<input type=\"hidden\" name=\"s_page\" value=\"1\">\n";
		$html .= "<input type=\"hidden\" name=\"item_id\" value=".$item_id.">\n";
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
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"item_list\">\n";
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

        $start_ymd = null;
        $end_ymd = null;
        if ($_POST['start_y'] && $_POST['start_m'] && $_POST['start_d']) {
            $start_ymd = date('Y-m-d', strtotime($_POST['start_y'].sprintf('%02d', $_POST['start_m']).sprintf('%02d', $_POST['start_d'])));
        } elseif ($_POST['display_start_ymd']) {            
            $start_ymd = date('Y-m-d', strtotime($_POST['display_start_ymd']));
        }
            
        if ($_POST['end_y'] && $_POST['end_m'] && $_POST['end_d']) {
            $end_ymd = date('Y-m-d', strtotime($_POST['end_y'].sprintf('%02d', $_POST['end_m']).sprintf('%02d', $_POST['end_d'])));
        } elseif ($_POST['display_end_ymd']) {            
            $end_ymd = date('Y-m-d', strtotime($_POST['display_end_ymd']));
        }

	// 登録項目設定
        $INSERT_DATA = array();
	$INSERT_DATA['item_name']               = str_replace(PHP_EOL, '', $_POST['item_name']);
	$INSERT_DATA['rarity']                  = $_POST['rarity'];
	$INSERT_DATA['item_class']              = $_POST['item_class'];
	$INSERT_DATA['item_kind']               = $_POST['item_kind'] ?: 0;
	$INSERT_DATA['parent_item_id']          = $_POST['parent_item_id'] ?: 0;
	$INSERT_DATA['effect_item_id']          = $_POST['effect_item_id'] ?: 0;	// add hasegawa 2019/06/05 生徒TOP改修
	$INSERT_DATA['can_use_character_id']    = $_POST['can_use_character_id'] ?: null;
	$INSERT_DATA['display_start_ymd']       = $start_ymd;
	$INSERT_DATA['display_end_ymd']         = $end_ymd;
	$INSERT_DATA['price']                   = (string)$_POST['price'];
	$INSERT_DATA['description']             = $_POST['description'];
	$INSERT_DATA['annotation']             = $_POST['annotation']; //add kimura 2019/06/03 生徒画面TOP改修
	$INSERT_DATA['can_use_not_setting']     = (string)$_POST['can_use_not_setting'];
	$INSERT_DATA['can_use_death']           = (string)$_POST['can_use_death'];
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
	$ERROR = $cdb->insert(T_GAMIFICATION_ITEM,$INSERT_DATA);
        
	if (!$ERROR) {
            $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>";
            // ディレクトリ作成
            $id = $cdb->insert_id();
            if ($id) {
                create_directory($id);
            }
        }
        unset($_SESSION['upload_file_path']);
        
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

	global $L_GAM_NED_POSITION_SETTING;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$start_ymd = null;
	$end_ymd = null;
	if ($_POST['start_y'] && $_POST['start_m'] && $_POST['start_d']) {
		$start_ymd = date('Y-m-d', strtotime($_POST['start_y'].sprintf('%02d', $_POST['start_m']).sprintf('%02d', $_POST['start_d'])));
	} elseif ($_POST['display_start_ymd']) {            
		$start_ymd = date('Y-m-d', strtotime($_POST['display_start_ymd']));
	}
	if ($_POST['end_y'] && $_POST['end_m'] && $_POST['end_d']) {
		$end_ymd = date('Y-m-d', strtotime($_POST['end_y'].sprintf('%02d', $_POST['end_m']).sprintf('%02d', $_POST['end_d'])));
	} elseif ($_POST['display_end_ymd']) {            
		$end_ymd = date('Y-m-d', strtotime($_POST['display_end_ymd']));
	}

	// 更新処理
	if (MODE == "詳細") {
		// DBアップデート
		$UPDATE_DATA = array();
		$UPDATE_DATA['item_name']               = str_replace(PHP_EOL, '',  $_POST['item_name']);
		$UPDATE_DATA['rarity']                  = $_POST['rarity'];
		$UPDATE_DATA['item_class']              = $_POST['item_class'];
		$UPDATE_DATA['item_kind']               = $_POST['item_kind'] ?: 0;
		$UPDATE_DATA['parent_item_id']          = $_POST['parent_item_id'] ?: 0;
		$UPDATE_DATA['effect_item_id']          = $_POST['effect_item_id'] ?: 0;	//add hasegawa 2019/06/05 生徒画面TOP改修
		$UPDATE_DATA['can_use_character_id']    = $_POST['can_use_character_id'] ?: null;
		$UPDATE_DATA['display_start_ymd']       = $start_ymd;
		$UPDATE_DATA['display_end_ymd']         = $end_ymd;
		$UPDATE_DATA['price']                   = (string)$_POST['price'];
		$UPDATE_DATA['description']             = $_POST['description'];
		$UPDATE_DATA['annotation']              = $_POST['annotation']; //add kimura 2019/06/03 生徒画面TOP改修
		$UPDATE_DATA['can_use_not_setting']     = (string)$_POST['can_use_not_setting'];
		$UPDATE_DATA['can_use_death']           = (string)$_POST['can_use_death'];
		$UPDATE_DATA['list_num']                = $_POST['list_num'];
		$UPDATE_DATA['usr_bko']                 = str_replace(PHP_EOL, '', $_POST['usr_bko']);
		$UPDATE_DATA['display']	  		= $_POST['display'];            
		$UPDATE_DATA['upd_syr_id']              = "update";
		$UPDATE_DATA['upd_date']                = "now()";
		$UPDATE_DATA['upd_tts_id']              = $_SESSION['myid']['id'];
		$where = " WHERE item_id = '".$cdb->real_escape($_POST['item_id'])."' LIMIT 1;";
		$can_use_flg = gamification_can_use_character_id_check($_POST['item_id'],$_POST['can_use_character_id']);
		$ERROR = $cdb->update(T_GAMIFICATION_ITEM, $UPDATE_DATA, $where);

		//アイテム使用可能キャラクターのチェック
		if(!$ERROR && $can_use_flg){
			$ERROR = gamification_character_position_mk_change($_POST['item_id'],$_POST['can_use_character_id']);
		}

		// 削除処理
	} elseif (MODE == "削除") {
		$UPDATE_DATA = array();
		$UPDATE_DATA['mk_flg']	   = "1";
		$UPDATE_DATA['mk_tts_id']  = $_SESSION['myid']['id'];
		$UPDATE_DATA['mk_date']	   = "now()";
		$UPDATE_DATA['upd_syr_id'] = "del";
		$UPDATE_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
		$UPDATE_DATA['upd_date']   = "now()";
		$where = " WHERE item_id = '".$cdb->real_escape($_POST['item_id'])."' LIMIT 1;";
		$ERROR = $cdb->update(T_GAMIFICATION_ITEM, $UPDATE_DATA, $where);

		// ファイルを削除する
		foreach (glob(MATERIAL_GAM_ITEM_DIR.'item_'.$_POST['item_id'].'.*') as $val ) {
			unlink($val);
		}
	}

	// ポジション指定が必要じゃない区分の場合は、変更時も表示位置設定データを削除する
	if (MODE == "削除" || (MODE == "詳細" && !$L_GAM_NED_POSITION_SETTING[$_POST['item_class']])) {
		$UPDATE_DATA = array();
		$UPDATE_DATA['mk_flg']	= "1";
		$UPDATE_DATA['mk_tts_id']   = $_SESSION['myid']['id'];
		$UPDATE_DATA['mk_date']     = "now()";
		$UPDATE_DATA['upd_syr_id']  = "del";
		$UPDATE_DATA['upd_tts_id']  = $_SESSION['myid']['id'];
		$UPDATE_DATA['upd_date']    = "now()";
		$where = " WHERE item_id = '".$cdb->real_escape($_POST['item_id'])."';";
		$ERROR = $cdb->update(T_GAMIFICATION_ITEM_CHARACTER_POSITION, $UPDATE_DATA, $where);
	}

	if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }

	unset($_SESSION['upload_file_path']);

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
	
	// add start hirose 2019/07/17 生徒TOP改修
	// item_classをPOSTから取得し、セションに格納
	if(empty($_POST['view_item_class']) && empty($_POST['view_item_id']) && empty($_POST['view_item_name']) && empty($_POST['s_page'])){
		unset($_SESSION['sub_session']['view_item_class']);
		unset($_SESSION['sub_session']['view_item_id']);
		unset($_SESSION['sub_session']['view_item_name']);
		unset($_SESSION['sub_session']['s_page']);
	}elseif (!empty($_POST['view_item_class']) || !empty($_POST['view_item_id']) || !empty($_POST['view_item_name'])) {
		$_SESSION['sub_session']['view_item_class'] = $_POST['view_item_class'];
		// アイテムIDをPOSTから取得し、セションに格納
		$_SESSION['sub_session']['view_item_id'] = $_POST['view_item_id'];
		// アイテム名をPOSTから取得し、セションに格納
		$_SESSION['sub_session']['view_item_name'] = $_POST['view_item_name'];
		unset($_SESSION['sub_session']['s_page']);
	}
	if(!empty($_POST['select_reset'])){
		unset($_SESSION['sub_session']);
	}
	// add end hirose 2019/07/17 生徒TOP改修

	return;
}
/**
 * キャラクターマスタ取得
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param int $id キャラクターID
 * @return array $character_list キャラクター配列
 */
function get_character_list($id = null) {

        // DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
        $character_list = array();
        $where = '';
        if ($id) {
            $where = " AND character_id= '".$id."'";
        }       
        // キャラクターマスタ取得
        $sql = " SELECT * FROM ".T_GAMIFICATION_CHARACTER
                ." WHERE mk_flg = '0'"
                .$where
                .";";
        if ($result = $cdb->query($sql)) {
            while ($list = $cdb->fetch_assoc($result)) {
                $character_list[$list['character_id']] = $list['character_name'];
            }
        }
        
        return $character_list;
}
/**
 * アイテム一覧取得
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param int $item_class アイテム区分
 * @param int $exclusion_item_id 除外するアイテムID
 * 
 * @return array $parent_item_list 親アイテムとして選択する用のアイテム配列
 */
function get_parent_item_list($item_class, $exclusion_item_id = null) {

        // DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
        $parent_item_list = array();
        $where = '';
        if ($item_class) {
            $where .= " AND item_class= '".$cdb->real_escape($item_class)."'";
        }       
        if ($exclusion_item_id) {
            $where .= " AND item_id <> '".$cdb->real_escape($exclusion_item_id)."'";
        }       
        // アイテムマスタ取得
        $sql = " SELECT * FROM ".T_GAMIFICATION_ITEM
               // ." WHERE mk_flg = '0'"		// upd hasegawa 2019/06/05 生徒TOP改修
		." WHERE parent_item_id = '0' AND mk_flg = '0'"
                .$where
                .";";
        if ($result = $cdb->query($sql)) {
            while ($list = $cdb->fetch_assoc($result)) {
                $parent_item_list[$list['item_id']] = $list['item_name'];
            }
        }
        
        return $parent_item_list;
}
/**
 * 表示年月選択用
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array ($L_YEAR, $L_MON, $L_DATE) 年配列, 月配列, 日配列
 */
function get_ymd_list() {

        $L_YEAR = array();
        $L_MON = array();
        $L_DATE = array();
        $L_YEAR[0] = '---';
        $L_MON[0] = '---';
        $L_DATE[0] = '---';
        
        $y = date('Y');
        $m = 12;
        $d = 31;

        for ($i=$y; $i <= $y+4; $i++) {
            $L_YEAR[$i] = $i;
        }
        for ($i=1; $i <= $m; $i++) {
            $L_MON[sprintf('%02d', $i)] = sprintf('%02d', $i);
        }
        for ($i=1; $i <= $d; $i++) {
            $L_DATE[sprintf('%02d', $i)] = sprintf('%02d', $i);
        }        
        return array($L_YEAR, $L_MON, $L_DATE);
}

/**
 * 管理ディレクトリ作成
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param string $character_id
 */
function create_directory() {

        // 画像フォルダ
        $item_file_path = MATERIAL_GAM_ITEM_DIR;
	if (!file_exists($item_file_path)) {
		@mkdir($item_file_path, 0777, true);        // 第三引数tureで再帰的に作成する
		@chmod($item_file_path, 0777);
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
function gamification_item_import() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
	global $L_GAM_NED_POSITION_SETTING;

	$file_name = $_FILES['gamification_item_file']['name'];
	$file_tmp_name = $_FILES['gamification_item_file']['tmp_name'];
	$file_error = $_FILES['gamification_item_file']['error'];

	if (!$file_tmp_name) {
		$ERROR[] = "ゲーミフィケーションアイテムtxtファイルが指定されておりません。";
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
				continue;
			}
			$file_data = explode("\t",$VAL);
			// 項目が全て設定されている場合
			if (count($file_data) == 19) { //update kimura 2019/06/03 生徒画面TOP改修 17 ---> 19
				$LINE['item_id']                = $file_data[0];
				$LINE['item_name']              = $file_data[1];
				$LINE['item_class']             = $file_data[2];
				$LINE['item_kind']              = $file_data[3];
				$LINE['can_use_character_id']   = $file_data[4];
				$LINE['parent_item_id']         = $file_data[5];
				// upd start hasegawa 2019/06/05 生徒TOP改修
				// $LINE['rarity']                 = $file_data[6];
				// $LINE['display_start_ymd']      = $file_data[7];
				// $LINE['display_end_ymd']        = $file_data[8];
				// $LINE['price']                  = $file_data[9];
				// $LINE['description']            = $file_data[10];
				// //del start kimura 2019/06/03 生徒画面TOP改修 {{{ フィールド追加に伴い連番ずれ
				// // $LINE['can_use_not_setting']    = $file_data[11];
				// // $LINE['can_use_death']          = $file_data[12];
				// // $LINE['list_num'] 		= $file_data[13];
				// // $LINE['usr_bko'] 		= $file_data[14];
				// // $LINE['display'] 		= $file_data[15];
				// // $LINE['mk_flg'] 		= $file_data[16];
				// //del end   kimura 2019/06/03 生徒画面TOP改修 }}}
				// //add start kimura 2019/06/03 生徒画面TOP改修 {{{
				// $LINE['annotation']    = $file_data[11];
				// $LINE['can_use_not_setting']    = $file_data[12];
				// $LINE['can_use_death']          = $file_data[13];
				// $LINE['list_num'] 		= $file_data[14];
				// $LINE['usr_bko'] 		= $file_data[15];
				// $LINE['display'] 		= $file_data[16];
				// $LINE['mk_flg'] 		= $file_data[17];
				// //add end   kimura 2019/06/03 生徒画面TOP改修 }}}
				$LINE['effect_item_id']         = $file_data[6];
				$LINE['rarity']                 = $file_data[7];
				$LINE['display_start_ymd']      = $file_data[8];
				$LINE['display_end_ymd']        = $file_data[9];
				$LINE['price']                  = $file_data[10];
				$LINE['description']            = $file_data[11];
				$LINE['annotation']		= $file_data[12];
				$LINE['can_use_not_setting']    = $file_data[13];
				$LINE['can_use_death']          = $file_data[14];
				$LINE['list_num'] 		= $file_data[15];
				$LINE['usr_bko'] 		= $file_data[16];
				$LINE['display'] 		= $file_data[17];
				$LINE['mk_flg'] 		= $file_data[18];				 
				// upd end hasegawa 2019/06/05
			// 管理番号が未設定の場合は、格納配列が１つ前にずれる
			} elseif (count($file_data) == 18) { //update kimura 2019/06/03 生徒画面TOP改修 16 --> 18
				$LINE['item_id']                = '0';
				$LINE['item_name']              = $file_data[0];
				$LINE['item_class']             = $file_data[1];
				$LINE['item_kind']              = $file_data[2];
				$LINE['can_use_character_id']   = $file_data[3];
				$LINE['parent_item_id']         = $file_data[4];
				// upd start hasegawa 2019/06/05 生徒TOP改修
				// $LINE['rarity']                 = $file_data[5];
				// $LINE['display_start_ymd']      = $file_data[6];
				// $LINE['display_end_ymd']        = $file_data[7];
				// $LINE['price']                  = $file_data[8];
				// $LINE['description']            = $file_data[9];
				// //del start kimura 2019/06/03 生徒画面TOP改修 {{{ フィールド追加に伴い連番ずれ
				// // $LINE['can_use_not_setting']    = $file_data[10];
				// // $LINE['can_use_death']          = $file_data[11];
				// // $LINE['list_num'] 		= $file_data[12];
				// // $LINE['usr_bko'] 		= $file_data[13];
				// // $LINE['display'] 		= $file_data[14];
				// // $LINE['mk_flg'] 		= $file_data[15];
				// //del end   kimura 2019/06/03 生徒画面TOP改修 }}}
				// //add start kimura 2019/06/03 生徒画面TOP改修 {{{
				// $LINE['annotation']    = $file_data[10];
				// $LINE['can_use_not_setting']    = $file_data[11];
				// $LINE['can_use_death']          = $file_data[12];
				// $LINE['list_num'] 		= $file_data[13];
				// $LINE['usr_bko'] 		= $file_data[14];
				// $LINE['display'] 		= $file_data[15];
				// $LINE['mk_flg'] 		= $file_data[16];
				// //add end   kimura 2019/06/03 生徒画面TOP改修 }}}
				 $LINE['effect_item_id']        = $file_data[5];
				 $LINE['rarity']                 = $file_data[6];
				 $LINE['display_start_ymd']      = $file_data[7];
				 $LINE['display_end_ymd']        = $file_data[8];
				 $LINE['price']                  = $file_data[9];
				 $LINE['description']            = $file_data[10];
				 $LINE['annotation']		  = $file_data[11];
				 $LINE['can_use_not_setting']    = $file_data[12];
				 $LINE['can_use_death']          = $file_data[13];
				 $LINE['list_num'] 		= $file_data[14];
				 $LINE['usr_bko'] 		= $file_data[15];
				 $LINE['display'] 		= $file_data[16];
				 $LINE['mk_flg'] 		= $file_data[17];
				// upd end hasegawa 2019/06/05
			} else {
				$ERROR[] = '<b>'.($i).'行目　未入力の項目があるためスキップしました。</b>';
				continue;
			}
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
			$sql  = "SELECT * FROM " .T_GAMIFICATION_ITEM.
					" WHERE item_id = '".$LINE['item_id']."';";
			if ($result = $cdb->query($sql)) {
				$exist_count = $cdb->num_rows($result);
			}

			// 登録処理
			if ($exist_count == 0) {
				$INSERT_DATA = array();
				$INSERT_DATA['item_id']					= $LINE['item_id'];
				$INSERT_DATA['item_name']               = preg_replace('/&lt;br&gt;|&lt;br \/&gt;|&lt;br\/&gt;/', '', $LINE['item_name']);
				$INSERT_DATA['rarity']                  = $LINE['rarity'];
				$INSERT_DATA['item_class']              = $LINE['item_class'];
				$INSERT_DATA['item_kind']               = $LINE['item_kind'] ?: 0;
				$INSERT_DATA['parent_item_id']          = $LINE['parent_item_id'] ?: 0;
				$INSERT_DATA['effect_item_id']          = $LINE['effect_item_id'] ?: 0;	// add hasegawa 2019/06/05 生徒TOP改修
				$INSERT_DATA['can_use_character_id']    = $LINE['can_use_character_id'] ?: null;
				$INSERT_DATA['display_start_ymd']       = $LINE['display_start_ymd'] ? date ('Y-m-d', strtotime($LINE['display_start_ymd'])) : null;
				$INSERT_DATA['display_end_ymd']         = $LINE['display_end_ymd'] ? date ('Y-m-d', strtotime($LINE['display_end_ymd'])) : null;
				$INSERT_DATA['price']                   = (string)$LINE['price'];
				$INSERT_DATA['description']             = preg_replace('/&lt;br&gt;|&lt;br \/&gt;|&lt;br\/&gt;/', '\r\n', $LINE['description']);
				$INSERT_DATA['annotation']              = preg_replace('/&lt;br&gt;|&lt;br \/&gt;|&lt;br\/&gt;/', '\r\n', $LINE['annotation']); //add kimura 2019/06/03 生徒画面TOP改修
				$INSERT_DATA['can_use_not_setting']     = (string)$LINE['can_use_not_setting'];
				$INSERT_DATA['can_use_death']           = (string)$LINE['can_use_death'];
				$INSERT_DATA['list_num']                = $LINE['list_num'];
				$INSERT_DATA['usr_bko']                 = $LINE['usr_bko'];
				$INSERT_DATA['display']	  		= $LINE['display'];
				$INSERT_DATA['ins_syr_id'] 		= "add";
				$INSERT_DATA['ins_date']  		= "now()";
				$INSERT_DATA['ins_tts_id'] 		= $_SESSION['myid']['id'];
				$INSERT_DATA['upd_syr_id'] 		= "add";
				$INSERT_DATA['upd_date']   		= "now()";
				$INSERT_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];

				$INSERT_ERROR = $cdb->insert(T_GAMIFICATION_ITEM, $INSERT_DATA);
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
					$INSERT_DATA['item_name']               = preg_replace('/&lt;br&gt;|&lt;br \/&gt;|&lt;br\/&gt;/', '', $LINE['item_name']);
					$INSERT_DATA['rarity']                  = $LINE['rarity'];
					$INSERT_DATA['item_class']              = $LINE['item_class'];
					$INSERT_DATA['item_kind']               = $LINE['item_kind'] ?: 0;
					$INSERT_DATA['parent_item_id']          = $LINE['parent_item_id'] ?: 0;
					$INSERT_DATA['effect_item_id']          = $LINE['effect_item_id'] ?: 0;	// add hasegawa 2019/06/05 生徒TOP改修
					$INSERT_DATA['can_use_character_id']    = $LINE['can_use_character_id'] ?: null;
					$INSERT_DATA['display_start_ymd']       = $LINE['display_start_ymd'] ? date ('Y-m-d', strtotime($LINE['display_start_ymd'])) : null;
					$INSERT_DATA['display_end_ymd']         = $LINE['display_end_ymd'] ? date ('Y-m-d', strtotime($LINE['display_end_ymd'])) : null;
					$INSERT_DATA['price']                   = (string)$LINE['price'];
					$INSERT_DATA['description']             = preg_replace('/&lt;br&gt;|&lt;br \/&gt;|&lt;br\/&gt;/', '\r\n', $LINE['description']);
					$INSERT_DATA['annotation']              = preg_replace('/&lt;br&gt;|&lt;br \/&gt;|&lt;br\/&gt;/', '\r\n', $LINE['annotation']); //add kimura 2019/06/03 生徒画面TOP改修
					$INSERT_DATA['can_use_not_setting']     = (string)$LINE['can_use_not_setting'];
					$INSERT_DATA['can_use_death']           = (string)$LINE['can_use_death'];
					$INSERT_DATA['list_num']                = $LINE['list_num'];
					$INSERT_DATA['usr_bko']                 = $LINE['usr_bko'];
					$INSERT_DATA['display']			= $LINE['display'];
					$INSERT_DATA['mk_flg']			= "0";
					$INSERT_DATA['mk_tts_id']		= "NULL";
					$INSERT_DATA['mk_date']			= "NULL";
					$INSERT_DATA['upd_syr_id']		= "update";
					$INSERT_DATA['upd_date']		= "now()";
					$INSERT_DATA['upd_tts_id']		= $_SESSION['myid']['id'];
					
				// 削除
				} else {
					$INSERT_DATA['display']			= "2";
					$INSERT_DATA['mk_flg']			= "1";
					$INSERT_DATA['mk_tts_id']		= $_SESSION['myid']['id'];
					$INSERT_DATA['mk_date']			= "now()";
					$INSERT_DATA['upd_syr_id']		= "del";
					$INSERT_DATA['upd_tts_id']		= $_SESSION['myid']['id'];
					$INSERT_DATA['upd_date']		= "now()";
                                        
					// 画像を削除する
					foreach (glob(MATERIAL_GAM_ITEM_DIR.'item_'.$LINE['item_id'].'.*') as $val ) {
						unlink($val);
					}
				}
				$where = " WHERE item_id = '".$LINE['item_id']."' LIMIT 1;";
				$can_use_flg = gamification_can_use_character_id_check($LINE['item_id'],$LINE['can_use_character_id']);
				$INSERT_ERROR = $cdb->update(T_GAMIFICATION_ITEM, $INSERT_DATA, $where);
				//アイテム使用可能キャラクターのチェック
				if(!$INSERT_ERROR && $can_use_flg){
					$INSERT_ERROR = gamification_character_position_mk_change($LINE['item_id'],$LINE['can_use_character_id']);
				}
                                
				// ポジション指定が必要じゃない区分の場合は、変更時も表示位置設定データを削除する
				if ($LINE['mk_flg'] == '1' || ($LINE['mk_flg'] == '0' && !$L_GAM_NED_POSITION_SETTING[$LINE['item_class']])) {
					$UPDATE_DATA = array();
					$UPDATE_DATA['mk_flg']	= "1";
					$UPDATE_DATA['mk_tts_id']   = $_SESSION['myid']['id'];
					$UPDATE_DATA['mk_date']     = "now()";
					$UPDATE_DATA['upd_syr_id']  = "del";
					$UPDATE_DATA['upd_tts_id']  = $_SESSION['myid']['id'];
					$UPDATE_DATA['upd_date']    = "now()";
					$where = " WHERE item_id = '".$LINE['item_id']."';";
					$INSERT_ERROR = $cdb->update(T_GAMIFICATION_ITEM_CHARACTER_POSITION, $UPDATE_DATA, $where);
				}
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
/**
 * アップロードファイル取り込み処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function image_upload($id = null) {

        $ERROR = array();    
        $cdb = $GLOBALS['cdb'];

        if ($_FILES['upload_file']['tmp_name']) {
        
            $file_tmp_name      = $_FILES['upload_file']['tmp_name'];
            $file_name      = $_FILES['upload_file']['name'];
            $file_error         = $_FILES['upload_file']['error'];

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

            if ($file_tmp_name) {
                $extension = "";
                if(preg_match('/\.png|\.gif|\.jpeg|\.jpg|\.mp3|\.wav/i', $file_name,$match)){
                        $extension = $match[0];
                }				
                if($extension){
                        $type = $extension;
                }else{
                        $ERROR[] = "ファイルはgif, png, jpg, mp3, wav のいずれかのみupload可能です。";
                }
            }

            if (!$ERROR) {
            
                // アップロードファイルを格納するフォルダのパスを指定
                // アップロード先が存在しない場合作成する
                create_directory();

                if (!$id) {
                    $sql = "SHOW TABLE STATUS LIKE '".T_GAMIFICATION_ITEM."'";
                    if ($result = $cdb->query($sql)) {
                        $list = $cdb->fetch_assoc($result);
                        $id = $list['Auto_increment'];
                    }
                }

                if ($id && $type) {
                    // テンポラリ領域→ファイル格納パスに移動
                    $_SESSION['upload_file_path'] = MATERIAL_GAM_ITEM_DIR.'item_'.$id.$type;
                    // 既にアップされてる同名ファイルの削除
                    foreach (glob(MATERIAL_GAM_ITEM_DIR.'item_'.$id.'.*') as $val ) {
                        unlink($val);
                    }
                    $res = @move_uploaded_file($file_tmp_name, MATERIAL_GAM_ITEM_DIR.'item_'.$id.$type);
                }
                // アップロードテンポラリーファイル削除
                if ($file_tmp_name && file_exists($file_tmp_name)) {
                        unlink($file_tmp_name);
                }
                if (!$res) {
                    $ERROR[] = "のアップロードに失敗しました。";
                }
            }
        }
        return $ERROR;        
}


/**
 * アイテム使用可能キャラクター変更チェック
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 * @param $item_id int $can_use_character_id int (POST or CSVからくる変更予定のID)
 */
function gamification_can_use_character_id_check($item_id,$can_use_character_id) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
	
	$return = false;
	
	if($can_use_character_id == '0'){
		//キャラクターIDを未選択にする場合、gamification_item_character_positionテーブルを更新する必要はなし
	}else{

		//使用可能キャラクターチェック
		$sql = "SELECT can_use_character_id FROM ".T_GAMIFICATION_ITEM." WHERE"
		   . " item_id = '".$cdb->real_escape($item_id)."'"
		   . " AND mk_flg='0';";
		$db_can_use_character_id = 0;
		if ($result = $cdb->query($sql)) {
		   $list = $cdb->fetch_assoc($result);
		   $db_can_use_character_id = $list['can_use_character_id'];
		}
		//既に登録されている使用可能キャラクターIDとPOSTのIDが異なった場合、gamification_item_character_positionテーブルを更新。
		if($db_can_use_character_id != $can_use_character_id){
			$return = true;
		}
	}
	
	return $return;
}
/**
 * アイテム使用可能キャラクター変更時のgamification_item_character_positionテーブル変更
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 * @param $item_id int $can_use_character_id int
 */
function gamification_character_position_mk_change($item_id,$can_use_character_id) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
	
	$ERROR = "";
	
	if(!$item_id && !$can_use_character_id){
		$ERROR = "キャラクターのポジションテーブルの更新ができませんでした。";
	}else{
		$UPDATE_DATA = array();
		$UPDATE_DATA['mk_flg']	   = "1";
		$UPDATE_DATA['mk_tts_id']  = $_SESSION['myid']['id'];
		$UPDATE_DATA['mk_date']	   = "now()";
		$UPDATE_DATA['upd_syr_id'] = "del";
		$UPDATE_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
		$UPDATE_DATA['upd_date']   = "now()";
		$where  = " WHERE character_id NOT IN('".$cdb->real_escape($can_use_character_id)."')";
		$where .= " AND item_id = '".$cdb->real_escape($item_id)."';";
		$ERROR = $cdb->update(T_GAMIFICATION_ITEM_CHARACTER_POSITION, $UPDATE_DATA, $where);
	}
	
	return $ERROR;
}
// add start hirose 2019/07/17 生徒TOP改修
/**
 * 絞り込みメニュー
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function select_menu() {

	global $L_GAM_ITEM_CLASS,$L_PAGE_VIEW;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
	
	$s_page_view = $_SESSION['sub_session']['s_page_view'];
	$view_item_name = $_SESSION['sub_session']['view_item_name'];
	$view_item_id = $_SESSION['sub_session']['view_item_id'];
	$view_item_class = $_SESSION['sub_session']['view_item_class'];


	//	問題形式	view_item_class
	$l_view_item_class = "<select name=\"view_item_class\">\n";
	if ($view_item_class == "") { $selected = "selected"; } else { $selected = ""; }
	$l_view_item_class .= "<option value=\"0\" $selected>----------</option>\n";
	$sql  = "SELECT item_class FROM ".T_GAMIFICATION_ITEM.
			" WHERE mk_flg= '0'".
			" GROUP BY item_class ORDER BY item_class;";
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$item_class = $list['item_class'];
			if ($view_item_class == $item_class) { $selected = "selected"; } else { $selected = ""; }
			$l_view_item_class .= "<option value=\"$item_class\" $selected>{$L_GAM_ITEM_CLASS[$item_class]}</option>\n";
		}
	}
	$l_view_unit_num .= "</select>\n";
	$MENU[0][name] = "アイテム種類";
	$MENU[0][value] = $l_view_item_class;


	//	管理番号	view_item_id
	$MENU[1][name] = "アイテムID";
	$MENU[1][value] = "<input type=\"text\" size=\"10\" name=\"view_item_id\" value=\"$view_item_id\">\n";
	
	
	//	アイテム名	view_item_name
	$MENU[2][name] = "アイテム名";
	$MENU[2][value] = "<input type=\"text\" size=\"10\" name=\"view_item_name\" value=\"$view_item_name\">\n";


	//	表示数	view_num
	if ($s_page_view == "") { $s_page_view = 0; }
	$l_page_view = "<select name=\"s_page_view\">\n";
	foreach ($L_PAGE_VIEW as $key => $val){
		if ($s_page_view == $key) { $sel = " selected"; } else { $sel = ""; }
		$l_page_view .= "<option value=\"".$key."\"".$sel.">".$val."</option>\n";
	}
	$l_page_view .= "</select>\n";
	$MENU[3][name] = "表示数";
	$MENU[3][value] = $l_page_view;


	//	送信ボタン
	$MENU[4][name] = "&nbsp;";
	$MENU[4][value] = "<input type=\"submit\" value=\"絞込\"> <input type=\"submit\" name=\"select_reset\" value=\"リセット\">\n";

	foreach ($MENU AS $key => $VAL) {
		$name_ = $VAL[name];
		$values_ = $VAL[value];
		if (!$values_) { continue; }
		$form_menu .= "<td>$name_</td>";
		$form_cell .= "<td>$values_</td>";
	}

	$html .= "<div id=\"mode_menu\">\n";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
	$html .= "<table class=\"member_form\">\n";
	$html .= "<tr class=\"member_form_menu\">\n";
	$html .= $form_menu;
	$html .= "</tr>\n";
	$html .= "<tr class=\"member_form_cell\">\n";
	$html .= $form_cell;
	$html .= "</tr>\n";
	$html .= "</table>\n";
	$html .= "</form>\n";
	$html .= "</div>\n";
	$html .= "<br style=\"clear:left;\">\n";

	return $html;
}
// add end hirose 2019/07/17 生徒TOP改修
