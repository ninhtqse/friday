<?php
/**
 * すらら
 *
 * ゲーミフィケーション キャラクターレベル管理
 *
 * 履歴
 * 2019/03/03 make file karasawa 生徒TOP改修
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
					$html .= character_level_list($ERROR); // 一覧表示
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
					$html .= character_level_list($ERROR); // 一覧表示
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
					$html .= character_level_list($ERROR);// 一覧表示
				}
				else {
					$html .= viewform($ERROR); 	// 詳細画面
				}
			} else {
				$html .= check_html();			// 確認画面
			}
                } elseif (MODE == "gamification_character_level_import") {
                        $ERROR = gamification_character_level_import();
			$html .= character_level_list($ERROR);
		// 一覧表示
		} elseif (MODE == "character_level_list") {
                        //unset($_SESSION['sub_session']);
                        $_SESSION['sub_session']['s_page'] = 1;
			$html .= character_level_list($ERROR);
                        
		} else {
			$html .= character_level_list($ERROR);
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
function character_level_list($ERROR) {
	// グローバル変数
	global $L_PAGE_VIEW, $L_GAM_CHARACTER_LEVEL;
        
	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

        // 権限取得
	if ($authority) { $L_AUTHORITY = explode("::",$authority); }

        $html .= "<h3>ゲーミフィケーション キャラクターレベル</h3>\n";


        $L_GAM_CHARACTER = array();
        $L_GAM_CHARACTER[0] = "選択してください";
        $sql = "SELECT * FROM ".T_GAMIFICATION_CHARACTER." WHERE mk_flg = '0';";
        if ($result = $cdb->query($sql)) {
            while($list = $cdb->fetch_assoc($result)) {
                $L_GAM_CHARACTER[$list['character_id']] = $list['character_name'];
            }
        }
        if (count($L_GAM_CHARACTER) <= 1) {
            $html .= "キャラクターを設定してからご利用ください。<br />\n";
            return $html;
        } else {
            
            $html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" id=\"character_id_form\">\n";
            $html .= "<input type=\"hidden\" name=\"mode\" value=\"character_level_list\">\n";
            $html .= "キャラクター絞り込み：<select id=\"character_id_select\" name=\"select_character_id\">";
            foreach ($L_GAM_CHARACTER as $character_id => $character_name) {
                $selected = "";
                if ($_POST['select_character_id'] == $character_id) {
                    $selected = " selected";
                }
                $html .= "<option value=\"".$character_id."\"".$selected.">".$character_name."</option>";    
            }
            $html .= "</select>";
            $html .= "</form><br>"; 
            $html .= "<script type=\"text/javascript\">\n";
            $html .= "$(function(){\n";
            $html .= "$(\"#character_id_select\").change(function(){\n";
            $html .= "$(\"#character_id_form \").submit();\n";
            $html .= "});\n";
            $html .= "});\n";
            $html .= "</script>\n";         
        }

        // エラーメッセージが存在する場合、メッセージ表示
	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
        } elseif (count($ERROR) == 0 && MODE == 'gamification_character_level_import') {
                $html .= "<b>データを登録しました</b><br>\n";
        }
        
	if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__add",$_SESSION['authority'])===FALSE)) {
		$html .= "インポートする場合はゲーミフィケーションキャラクターレベルtxtファイル(S-JIS)を指定しCSVインポートを押してください<br>";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" enctype=\"multipart/form-data\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"gamification_character_level_import\">\n";
                $html .= "<input type=\"hidden\" name=\"select_character_id\" value=\"".$_POST['select_character_id']."\">\n";
		$html .= "<input type=\"file\" size=\"40\" name=\"gamification_character_level_file\">\n";
		$html .= "<input type=\"submit\" value=\"CSVインポート\">\n";
		$html .= "</form>\n";
		$html .= "<br />\n";
		$html .= "<form action=\"/admin/gamification_character_level_make_csv.php\" method=\"POST\">";
                $html .= "<input type=\"hidden\" name=\"select_character_id\" value=\"".$_POST['select_character_id']."\">\n";
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
        $html .= "<input type=\"hidden\" name=\"select_character_id\" value=\"".$_POST['select_character_id']."\">\n";
		$html .= "<input type=\"submit\" value=\"キャラクターレベル新規登録\">\n";
		$html .= "</form>\n";
		$html .= "<br />\n";
	}

        // 表示ページ制御
	$s_page_view_html .= "&nbsp;&nbsp;&nbsp;表示数<select name=\"s_page_view\">\n";
	foreach ($L_PAGE_VIEW as $key => $val){
		if ($_SESSION['sub_session']['s_page_view'] == $key) { $sel = " selected"; } else { $sel = ""; }
		$s_page_view_html .= "<option value=\"".$key."\"".$sel.">".$val."</option>\n";
	}
	$s_page_view_html .= "</select><input type=\"submit\" value=\"Set\">\n";

	// 一覧取得
	$sql  = "SELECT * FROM " . T_GAMIFICATION_CHARACTER_LEVEL
                ." WHERE mk_flg ='0'";
        if ($_POST['select_character_id'] > 0) {
            $sql .= " AND character_id='".$_POST['select_character_id']."'";           
        }
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
        $sql .= " ORDER BY character_id, character_level";
	$sql .= " LIMIT ".$start.",".$page_view."";
	
        
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
		$html .= "<br>修正する場合は、修正するキャラクターレベルの詳細ボタンを押してください。<br>\n";

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
			$html .= "<input type=\"hidden\" name=\"select_character_id\" value=\"".$_POST['select_character_id']."\">\n";
			$html .= "</form>";
		}

		// 次ページボタン表示制御
		if ($page < $max_page) {
			$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" style=\"float:left;\">";
			$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"page\">\n";
			$html .= "<input type=\"hidden\" name=\"s_page\" value=\"".$next."\">\n";
			$html .= "<input type=\"hidden\" name=\"select_character_id\" value=\"".$_POST['select_character_id']."\">\n";
			$html .= "<input type=\"submit\" value=\"次のページ\">";
			$html .= "</form>";
		}

		$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" style=\"float:left;\">";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
		$html .= "<input type=\"hidden\" name=\"s_page\" value=\"1\">\n";
                $html .= "<input type=\"hidden\" name=\"select_character_id\" value=\"".$_POST['select_character_id']."\">\n";
		$html .= $s_page_view_html;
		$html .= "</form><br><br>\n";
		$html .= "<table class=\"secret_form\">\n";
		$html .= "<tr style=\"text-align:left;\" class=\"secret_form_menu\">\n";

		//--------------------
		// リストタイトル表示
		//--------------------
		$html .= "<th style=\"padding:5px;\">キャラクターID</th>\n";
		$html .= "<th style=\"padding:5px;\">キャラクター名</th>\n";
		$html .= "<th style=\"padding:5px;\">レベル</th>\n";
		$html .= "<th style=\"padding:5px;\">レベルアップに<br>必要な経験値</th>\n";
		$html .= "<th style=\"padding:5px;\">一回のトレーニングに<br>必要なポイント</th>\n";
                $html .= "<th style=\"padding:5px;\">レベルアップに必要な<br>トレーニング回数</th>\n";
                $html .= "<th style=\"padding:5px;\">一回のエサに<br>必要なポイント</th>\n";

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

			// HTML作成
			$html .= "<tr class=\"secret_form_cell\">\n";
			$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"select_character_id\" value=\"".$_POST['select_character_id']."\">\n";
			$html .= "<input type=\"hidden\" name=\"character_id\" value=\"".$list['character_id']."\">\n";
			$html .= "<input type=\"hidden\" name=\"character_level\" value=\"".$list['character_level']."\">\n";
			$html .= "<td>".$list['character_id']."</td>\n";
			$html .= "<td>".$L_GAM_CHARACTER[$list['character_id']]."</td>\n";
			$html .= "<td>".$L_GAM_CHARACTER_LEVEL[$list['character_level']]."</td>\n";
			$html .= "<td>".$list['levelup_point']." pt</td>\n";
			$html .= "<td>".$list['training_point']." pt</td>\n";
			$html .= "<td>".$list['need_training_count']." 回</td>\n";
			$html .= "<td>".$list['need_meal_point']." pt</td>\n";

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
    
        global $L_GAM_CHARACTER_LEVEL;
        
        $L_GAM_CHARACTER_LEVEL[0] = '選択してください';
        ksort($L_GAM_CHARACTER_LEVEL);
        
    
	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
    
	$html .= "<br>\n";
	$html .= "キャラクターレベル 新規登録フォーム<br>\n";
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
	$make_html->set_file(GAMIFICATION_CHARACTER_LEVEL);

        // キャラクター一覧取得
        $L_GAM_CHARACTER = array();
        $L_GAM_CHARACTER[0] = "選択してください";
        $sql = "SELECT * FROM ".T_GAMIFICATION_CHARACTER." WHERE mk_flg='0' ORDER BY list_num;";
        if ($result = $cdb->query($sql)) {
            while($list = $cdb->fetch_assoc($result)) {
                $L_GAM_CHARACTER[$list['character_id']] = $list['character_name'];
            }
        }
	// フォーム部品生成
        if ($_POST['select_character_id'] > 0) {
            $html .= "<input type=\"hidden\" name=\"select_character_id\" value=\"".$_POST['select_character_id']."\">\n";
            $html .= "<input type=\"hidden\" name=\"character_id\" value=\"".$_POST['select_character_id']."\">\n";
            $INPUTS['CHARACTERNAME']	= array('result'=>'plane','value'=>$L_GAM_CHARACTER[$_POST['select_character_id']] ?: null);
        } else {
            $INPUTS['CHARACTERNAME']	= array('result'=>'form', 'type' => 'select', 'name' => 'character_id', 'array'=>$L_GAM_CHARACTER, 'check' => $_POST['character_id'] ?: null);
        }
	$INPUTS['LEVEL']	= array('result'=>'form', 'type' => 'select', 'name' => 'character_level', 'array'=>$L_GAM_CHARACTER_LEVEL, 'check' => $_POST['character_level'] ?: null);
	$INPUTS['LEVELUPPOINT']	= array('result'=>'form', 'type' => 'text', 'name' => 'levelup_point', 'size' => '10', 'value'=>$_POST['levelup_point']);
	$INPUTS['TRAININGPOINT'] = array('result'=>'form', 'type' => 'text', 'name' => 'training_point', 'size' => '10', 'value'=>$_POST['training_point']);
	$INPUTS['NEEDTRAININGCOUNT'] = array('result'=>'form', 'type' => 'text', 'name' => 'need_training_count', 'size' => '10', 'value'=>$_POST['need_training_count']);
	$INPUTS['NEEDMEALPOINT'] = array('result'=>'form', 'type' => 'text', 'name' => 'need_meal_point', 'size' => '10', 'value'=>$_POST['need_meal_point']);
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
	$html .= "<input type=\"hidden\" name=\"select_character_id\" value=\"".$_POST['select_character_id']."\">\n";
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
 
        global $L_GAM_CHARACTER_LEVEL;
	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// POST値取得
	if (ACTION=="check" || ACTION=="back") {
		// POST値取得
		foreach ($_POST as $key => $val) {
			$$key = $val;
		}
                $sql = "SELECT character_name FROM ".T_GAMIFICATION_CHARACTER." WHERE character_id='".$character_id."' AND mk_flg='0';";
                $result = $cdb->query($sql);
                $list = $cdb->fetch_assoc($result);
                $character_name = $list['character_name']; 
                
	} else {
		$sql = "SELECT l.*, c.character_name FROM " . T_GAMIFICATION_CHARACTER_LEVEL." l"
                    ." INNER JOIN ".T_GAMIFICATION_CHARACTER." c ON c.character_id = l.character_id"
                    ." AND c.mk_flg = '0'"
                    ." WHERE l.character_id ='".$cdb->real_escape($_POST['character_id'])."'"
                    ." AND l.character_level = '".$cdb->real_escape($_POST['character_level'])."'"
                    ." AND l.mk_flg='0' LIMIT 1;";
                
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
        $select_character_id = $_POST['select_character_id'];
        
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
	$html .= "<input type=\"hidden\" name=\"select_character_id\" value=\"".$select_character_id."\">\n";
	$html .= "<input type=\"hidden\" name=\"character_id\" value=\"".$character_id."\">\n";
	$html .= "<input type=\"hidden\" name=\"character_level\" value=\"".$character_level."\">\n";
        
	// htmlクラス生成
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(GAMIFICATION_CHARACTER_LEVEL);
    
	// フォーム部品生成
        $INPUTS['CHARACTERNAME']	= array('result'=>'plane', 'value'=>$character_name);
	$INPUTS['LEVEL']                = array('result'=>'plane', 'value'=>$L_GAM_CHARACTER_LEVEL[$character_level]);
	$INPUTS['LEVELUPPOINT'] 	= array('result'=>'form', 'type' => 'text', 'name' => 'levelup_point', 'size' => '10', 'value'=>$levelup_point);
	$INPUTS['TRAININGPOINT']        = array('result'=>'form', 'type' => 'text', 'name' => 'training_point', 'size' => '10', 'value'=>$training_point);
	$INPUTS['NEEDTRAININGCOUNT']    = array('result'=>'form', 'type' => 'text', 'name' => 'need_training_count', 'size' => '10', 'value'=>$need_training_count);
	$INPUTS['NEEDMEALPOINT']        = array('result'=>'form', 'type' => 'text', 'name' => 'need_meal_point', 'size' => '10', 'value'=>$need_meal_point);
	$INPUTS['USERBKO']              = array('result'=>'form', 'type' => 'text', 'name' => 'usr_bko', 'size' => '50', 'value'=>$usr_bko ?: null);
        $make_html->set_rep_cmd($INPUTS);
	$html .= $make_html->replace();

	// 制御ボタン定義
	$html .= "<input type=\"submit\" value=\"変更確認\">";
	$html .= "<input type=\"reset\" value=\"クリア\">";
	$html .= "</form><br>\n";
	// 一覧に戻る
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"character_list\">\n";
	$html .= "<input type=\"hidden\" name=\"select_character_id\" value=\"".$select_character_id."\">\n";
	$html .= "<input type=\"hidden\" name=\"character_id\" value=\"".$character_id."\">\n";
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
    
        global $L_GAM_CHARACTER_LEVEL;
	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
        
        $CHECK_LIST = array();
        if (is_array($LINE) && count($LINE) > 0) {
            $CHECK_LIST = $LINE;
        } else {
            $CHECK_LIST = $_POST;
        }

        // キャラクターID
        if (!$CHECK_LIST['character_id'] || $CHECK_LIST['character_id'] == 0) {
			$ERROR[] = "キャラクターID（キャラクター名）が未選択です。";
        } elseif (preg_match("/[^0-9]/",$CHECK_LIST['character_id']) || $CHECK_LIST['character_id'] == 0) {
			$ERROR[] = "キャラクターID（キャラクター名）の値が不正です。";            
        }
        // レベル
        if (!$CHECK_LIST['character_level'] || $CHECK_LIST['character_level'] == 0) {
		$ERROR[] = "レベルが未選択です。";
        } elseif (preg_match("/[^0-9]/",$CHECK_LIST['character_level']) || $CHECK_LIST['character_level'] == 0 || !$L_GAM_CHARACTER_LEVEL[$CHECK_LIST['character_level']]) {
		$ERROR[] = "レベルの値が不正です。";            
        }
        
        // データ存在チェック
        if(MODE == 'add' && $CHECK_LIST['character_id'] > 0 && $CHECK_LIST['character_level'] > 0) {
            $sql = "SELECT * FROM ".T_GAMIFICATION_CHARACTER_LEVEL
                    ." WHERE mk_flg='0' AND character_id = '".$cdb->real_escape($CHECK_LIST['character_id'])."'"
                    ." AND character_level = '".$cdb->real_escape($CHECK_LIST['character_level'])."'"
                    .";";
            $count = 0;
            if ($result = $cdb->query($sql)) {
                $count = $cdb->num_rows($result);                
            }
            if ($count > 0) {
                $ERROR[] = "指定したキャラクター・レベルは設定済です。";
            }
        }
        
        // レベルアップに必要な経験値(pt)
		if($CHECK_LIST['levelup_point'] == ''){
			$ERROR[] = "レベルアップに必要な経験値が未入力です。";
		}elseif (preg_match("/[^0-9]/",$CHECK_LIST['levelup_point'])) {
			$ERROR[] = "レベルアップに必要な経験値には数値を入力してください。";
        }
        // 一回のトレーニングに必要なポイント
        if($CHECK_LIST['training_point'] == ''){
			$ERROR[] = "一回のトレーニングに必要なポイントが未入力です。";
		}elseif (preg_match("/[^0-9]/",$CHECK_LIST['training_point'])) {
			$ERROR[] = "一回のトレーニングに必要なポイントには数値を入力してください。";
        }
        // レベルアップに必要なトレーニング回数
        if($CHECK_LIST['need_training_count'] == ''){
			$ERROR[] = "レベルアップに必要なトレーニング回数が未入力です。";
		}elseif (preg_match("/[^0-9]/",$CHECK_LIST['need_training_count'])) {
			$ERROR[] = "レベルアップに必要なトレーニング回数には数値を入力してください。";
        }
       // 一回のエサに必要な経験値(pt) 
        if($CHECK_LIST['need_meal_point'] == ''){
			$ERROR[] = "一回のエサに必要な経験値が未入力です。";
		}elseif (preg_match("/[^0-9]/",$CHECK_LIST['need_meal_point'])) {
			$ERROR[] = "一回のエサに必要な経験値には数値を入力してください。";
        }
        // ユーザー備考
        if ($CHECK_LIST['usr_bko'] && mb_strlen($CHECK_LIST['usr_bko']) > 255) {
		$ERROR[] = "ユーザー備考255文字まで入力可能です。";
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
    
    
        global $L_GAM_CHARACTER_LEVEL;
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
                
                $sql = "SELECT character_name FROM ".T_GAMIFICATION_CHARACTER." WHERE character_id='".$character_id."' AND mk_flg='0';";
                $result = $cdb->query($sql);
                $list = $cdb->fetch_assoc($result);
                $character_name = $list['character_name'];                    
                
	} else {
		$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"change\">\n";
		$sql = "SELECT l.*, c.character_name FROM " . T_GAMIFICATION_CHARACTER_LEVEL." l"
                    ." INNER JOIN ".T_GAMIFICATION_CHARACTER." c ON c.character_id = l.character_id"
                    ." AND c.mk_flg = '0'"
                    ." WHERE l.character_id ='".$cdb->real_escape($_POST['character_id'])."'"
                    ." AND l.character_level = '".$cdb->real_escape($_POST['character_level'])."'"
                    ." AND l.mk_flg='0' LIMIT 1;";
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

        $select_character_id = $_POST['select_character_id'];
        
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
	$make_html->set_file(GAMIFICATION_CHARACTER_LEVEL);        
	$INPUTS['CHARACTERNAME']  	= array('result'=>'plane','value'=>$character_name);
	$INPUTS['LEVEL']                = array('result'=>'plane','value'=>$L_GAM_CHARACTER_LEVEL[$character_level]);
	$INPUTS['LEVELUPPOINT']  	= array('result'=>'plane','value'=>$levelup_point);
	$INPUTS['TRAININGPOINT']  	= array('result'=>'plane','value'=>$training_point);
	$INPUTS['NEEDTRAININGCOUNT']  	= array('result'=>'plane','value'=>$need_training_count);
	$INPUTS['NEEDMEALPOINT']  	= array('result'=>'plane','value'=>$need_meal_point);
        $INPUTS['USERBKO']		= array('result'=>'plane','value'=>$usr_bko);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();

	// 制御ボタン定義
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" style=\"float:left\">\n";
	$html .= $HIDDEN;
	// 削除の場合は、１画面目に戻る
	if (MODE == "削除") {
		$html .= "<input type=\"hidden\" name=\"s_page\" value=\"1\">\n";
		$html .= "<input type=\"hidden\" name=\"select_character_id\" value=".$select_character_id.">\n";
		$html .= "<input type=\"hidden\" name=\"character_level\" value=".$character_level.">\n";
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
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"character_level_list\">\n";
	}
        $html .= "<input type=\"hidden\" name=\"select_character_id\" value=".$select_character_id.">\n";
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
        
        $sql = "SELECT * FROM ".T_GAMIFICATION_CHARACTER_LEVEL
                ." WHERE character_id='".$cdb->real_escape($_POST['character_id'])."'"
                ." AND character_level='".$cdb->real_escape($_POST['character_level'])."'"
                .";";
        
        $exist_count = 0;
        if ($result = $cdb->query($sql)) {
                $exist_count = $cdb->num_rows($result);
        }                
        
        // プライマリー重複チェック
        if (!$exist_count) {
            // 登録項目設定
            $INSERT_DATA = array();
            $INSERT_DATA['character_id']            = (string)$_POST['character_id']?:0;
            $INSERT_DATA['character_level']         = (string)$_POST['character_level']?:0;
            $INSERT_DATA['levelup_point']           = (string)$_POST['levelup_point']?:0;
            $INSERT_DATA['training_point']          = (string)$_POST['training_point']?:0;
            $INSERT_DATA['need_training_count']     = (string)$_POST['need_training_count']?:0;
            $INSERT_DATA['need_meal_point']         = (string)$_POST['need_meal_point']?:0;
            $INSERT_DATA['usr_bko']                 = str_replace(PHP_EOL, '', $_POST['usr_bko']);
            $INSERT_DATA['ins_syr_id']              = "add";
            $INSERT_DATA['ins_date']                = "now()";
            $INSERT_DATA['ins_tts_id']              = $_SESSION['myid']['id'];
            $INSERT_DATA['upd_syr_id']              = "add";
            $INSERT_DATA['upd_date']                = "now()";
            $INSERT_DATA['upd_tts_id']              = $_SESSION['myid']['id'];

            // ＤＢ追加処理
            $ERROR = $cdb->insert(T_GAMIFICATION_CHARACTER_LEVEL, $INSERT_DATA);            
            
        } else {
                $UPDATE_DATA = array();
		$UPDATE_DATA['levelup_point']       = (string)$_POST['levelup_point'] ?:0;
		$UPDATE_DATA['training_point']      = (string)$_POST['training_point'] ?:0;
		$UPDATE_DATA['need_training_count'] = (string)$_POST['need_training_count'] ?:0;
		$UPDATE_DATA['need_meal_point']     = (string)$_POST['need_meal_point'] ?:0;
		$UPDATE_DATA['usr_bko']             = str_replace(PHP_EOL, '', $_POST['usr_bko']);
                $UPDATE_DATA['mk_flg']              = (string)'0';
                $UPDATE_DATA['mk_tts_id']           = '';
                $UPDATE_DATA['mk_date']             = '';
                $UPDATE_DATA['upd_syr_id']          = "add";
                $UPDATE_DATA['upd_date']            = "now()";
                $UPDATE_DATA['upd_tts_id']          = $_SESSION['myid']['id'];

		$where = " WHERE character_id = '".$cdb->real_escape($_POST['character_id'])."'"
                        ." AND character_level = '".$cdb->real_escape($_POST['character_level'])."'"
                        ." LIMIT 1;";
                
		$ERROR = $cdb->update(T_GAMIFICATION_CHARACTER_LEVEL, $UPDATE_DATA, $where);
        }
        
	if (!$ERROR) {
            $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>";
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
		$UPDATE_DATA['levelup_point']       = (string)$_POST['levelup_point'] ?:0;
		$UPDATE_DATA['training_point']      = (string)$_POST['training_point'] ?:0;
		$UPDATE_DATA['need_training_count'] = (string)$_POST['need_training_count'] ?:0;
		$UPDATE_DATA['need_meal_point']     = (string)$_POST['need_meal_point'] ?:0;
		$UPDATE_DATA['usr_bko']             = str_replace(PHP_EOL, '', $_POST['usr_bko']);
		$UPDATE_DATA['upd_syr_id']          = "update";
		$UPDATE_DATA['upd_date']            = "now()";
		$UPDATE_DATA['upd_tts_id']          = $_SESSION['myid']['id'];
                
		$where = " WHERE character_id = '".$cdb->real_escape($_POST['character_id'])."'"
                        ." AND character_level = '".$cdb->real_escape($_POST['character_level'])."'"
                        ." LIMIT 1;";
                
		$ERROR = $cdb->update(T_GAMIFICATION_CHARACTER_LEVEL, $UPDATE_DATA, $where);

	// 削除処理
	} elseif (MODE == "削除") {
                $UPDATE_DATA = array();
		$UPDATE_DATA['mk_flg']	   = "1";
		$UPDATE_DATA['mk_tts_id']  = $_SESSION['myid']['id'];
		$UPDATE_DATA['mk_date']	   = "now()";
		$UPDATE_DATA['upd_syr_id'] = "del";
		$UPDATE_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
		$UPDATE_DATA['upd_date']   = "now()";
                
		$where = " WHERE character_id = '".$cdb->real_escape($_POST['character_id'])."'"
                        ." AND character_level = '".$cdb->real_escape($_POST['character_level'])."'"
                        ." LIMIT 1;";
                
		$ERROR = $cdb->update(T_GAMIFICATION_CHARACTER_LEVEL,$UPDATE_DATA,$where);

                // 関連するアイテムのポジション設定を削除する
                $UPDATE_DATA = array();
                $UPDATE_DATA['mk_flg']	= "1";
                $UPDATE_DATA['mk_tts_id']   = $_SESSION['myid']['id'];
                $UPDATE_DATA['mk_date']     = "now()";
                $UPDATE_DATA['upd_syr_id']  = "del";
                $UPDATE_DATA['upd_tts_id']  = $_SESSION['myid']['id'];
                $UPDATE_DATA['upd_date']    = "now()";
                
		$where = " WHERE character_id = '".$cdb->real_escape($_POST['character_id'])."'"
                        ." AND character_level = '".$cdb->real_escape($_POST['character_level'])."'";
                
                $ERROR = $cdb->update(T_GAMIFICATION_ITEM_CHARACTER_POSITION, $UPDATE_DATA, $where);        
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
 * インポート処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function gamification_character_level_import() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$file_name = $_FILES['gamification_character_level_file']['name'];
	$file_tmp_name = $_FILES['gamification_character_level_file']['tmp_name'];
	$file_error = $_FILES['gamification_character_level_file']['error'];

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
			if (count($file_data) == 8) {
				$LINE['character_id']           = $file_data[0];
				$LINE['character_level'] 	= $file_data[1];
				$LINE['levelup_point']          = $file_data[2];
				$LINE['training_point'] 	= $file_data[3];
				$LINE['need_training_count'] 	= $file_data[4];
				$LINE['need_meal_point'] 	= $file_data[5];
                $LINE['usr_bko'] 		= $file_data[6];
				$LINE['mk_flg'] 		= $file_data[7];
			} elseif(count($file_data) == 7){
				$LINE['character_id']           = 0;
				$LINE['character_level'] 	= $file_data[0];
				$LINE['levelup_point']          = $file_data[1];
				$LINE['training_point'] 	= $file_data[2];
				$LINE['need_training_count'] 	= $file_data[3];
				$LINE['need_meal_point'] 	= $file_data[4];
                $LINE['usr_bko'] 		= $file_data[5];
				$LINE['mk_flg'] 		= $file_data[6];
			}else {
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
                        if (count($LINE_ERROR) > 0) {
				$ERROR[] = '<b>'.($i).'行目　以下のエラーのためスキップしました。</b>';
                                foreach($LINE_ERROR as $v) {
                                    $ERROR[] = $v;
                                }
				continue;
                        }
                        
			// データ存在チェック
			$sql  = "SELECT * FROM " .T_GAMIFICATION_CHARACTER_LEVEL
                                ." WHERE character_id = '".$LINE['character_id']."'"
                                ." AND character_level = '".$LINE['character_level']."'";   // mk_flgみない！
                        
			if ($result = $cdb->query($sql)) {
				$exist_count = $cdb->num_rows($result);
			}

			// 登録処理
			if ($exist_count == 0) {
				$INSERT_DATA = array();
				$INSERT_DATA['character_id']                            = $LINE['character_id'];
				$INSERT_DATA['character_level']				= $LINE['character_level'];
				$INSERT_DATA['levelup_point']				= $LINE['levelup_point'];
				$INSERT_DATA['training_point']				= $LINE['training_point'];
				$INSERT_DATA['need_training_count']			= $LINE['need_training_count'];
				$INSERT_DATA['need_meal_point']                         = $LINE['need_meal_point'];
                                $INSERT_DATA['usr_bko']                                 = $LINE['usr_bko'];
				$INSERT_DATA['mk_flg']					= $LINE['mk_flg'];
				$INSERT_DATA['ins_syr_id']				= "add";
				$INSERT_DATA['ins_date']				= "now()";
				$INSERT_DATA['ins_tts_id']				= $_SESSION['myid']['id'];
				$INSERT_DATA['upd_syr_id']				= "add";
				$INSERT_DATA['upd_date']				= "now()";
				$INSERT_DATA['upd_tts_id']				= $_SESSION['myid']['id'];

				$INSERT_ERROR = $cdb->insert(T_GAMIFICATION_CHARACTER_LEVEL, $INSERT_DATA);
 
			// 更新処理・削除処理
			} else {
				// 更新
				$INSERT_DATA = array();
				if ($LINE['mk_flg'] == '0') {
                                        $INSERT_DATA['levelup_point']                   = $LINE['levelup_point'];
                                        $INSERT_DATA['training_point']                  = $LINE['training_point'];
                                        $INSERT_DATA['need_training_count']		= $LINE['need_training_count'];
                                        $INSERT_DATA['need_meal_point']                 = $LINE['need_meal_point'];
                                        $INSERT_DATA['usr_bko']                         = $LINE['usr_bko'];
					$INSERT_DATA['mk_flg']				= "0";
					$INSERT_DATA['mk_tts_id']			= "NULL";
					$INSERT_DATA['mk_date']				= "NULL";
					$INSERT_DATA['upd_syr_id']			= "update";
					$INSERT_DATA['upd_date']			= "now()";
					$INSERT_DATA['upd_tts_id']			= $_SESSION['myid']['id'];

                                        $where = " WHERE character_id = '".$LINE['character_id']."' AND character_level = '".$LINE['character_level']."' LIMIT 1;";
                                        $INSERT_ERROR = $cdb->update(T_GAMIFICATION_CHARACTER_LEVEL, $INSERT_DATA, $where);
				// 削除
				} else {
					$INSERT_DATA['mk_flg']				= "1";
					$INSERT_DATA['mk_tts_id']			= $_SESSION['myid']['id'];
					$INSERT_DATA['mk_date']				= "now()";
					$INSERT_DATA['upd_syr_id']			 = "del";
					$INSERT_DATA['upd_tts_id']			= $_SESSION['myid']['id'];
					$INSERT_DATA['upd_date']			= "now()";
                                
                                        $where = " WHERE character_id = '".$LINE['character_id']."' AND character_level = '".$LINE['character_level']."' LIMIT 1;";
                                        $INSERT_ERROR = $cdb->update(T_GAMIFICATION_CHARACTER_LEVEL, $INSERT_DATA, $where);
                                        
                                        // 関連するアイテムポジション情報も削除
                                        if (!$INSERT_ERROR) {
                                            $INSERT_ERROR = $cdb->update(T_GAMIFICATION_ITEM_CHARACTER_POSITION, $INSERT_DATA, $where);
                                        }
                                }
			}                        
                        if (is_array($INSERT_ERROR) && count($INSERT_ERROR) > 0) {
                            $ERROR[] = '<b>'.($i+1)."行目　以下のエラーの為、情報の登録・更新に失敗しました。</b>";
                            foreach($INSERT_ERROR as $v) {
                                $ERROR[] = $v;
                            }
                        } 
			$i++;
		}
	}

	return $ERROR;
}



