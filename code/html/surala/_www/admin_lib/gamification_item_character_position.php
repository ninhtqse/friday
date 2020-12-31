<?php
/**
 * すらら
 *
 * ゲーミフィケーション アイテム管理 アイテム×キャラクターごとに表示位置を登録
 *
 * 履歴
 * 2019/03/15 make file hasegawa 生徒TOP改修
 *
 * @author Azet
 */

/**
 * 一覧表示処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @return string HTML
 */
function position_list($ERROR) {

        global $L_EXP_CHA_CODE;
    
	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

        unset($_SESSION['upload_img_path']);
        
        // 権限取得
	if ($authority) { $L_AUTHORITY = explode("::",$authority); }

        $html .= "<h3>アイテム 表示位置設定</h3>\n";

	// エラーメッセージが存在する場合、メッセージ表示
	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
        }

        $html .= make_item_view_html();
	$html .= "<br>\n";
        
	if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__add",$_SESSION['authority'])===FALSE)) {
		$html .= "インポートする場合はゲーミフィケーションアイテムキャラクター表示位置txtファイル(S-JIS)を指定しCSVインポートを押してください<br>";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" enctype=\"multipart/form-data\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"position_import\">\n";
		$html .= "<input type=\"hidden\" name=\"item_id\" value=\"".$_POST['item_id']."\">\n";
		$html .= "<input type=\"file\" size=\"40\" name=\"position_file\">\n";
		$html .= "<input type=\"submit\" value=\"CSVインポート\">\n";
		$html .= "</form>\n";
		$html .= "<br />\n";
		$html .= "<form action=\"/admin/gamification_item_character_position_make_csv.php\" method=\"POST\">";
		$html .= "<input type=\"hidden\" name=\"item_id\" value=\"".$_POST['item_id']."\">\n";
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
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" style=\"margin-bottom:10px;\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"position_add\">\n";
		$html .= "<input type=\"hidden\" name=\"item_id\" value=\"".$_POST['item_id']."\">\n";
		$html .= "<input type=\"submit\" value=\"アイテム表示位置新規登録\">\n";
		$html .= "</form>\n";
                $html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
                $html .= "<input type=\"hidden\" name=\"mode\" value=\"item_list\">\n";
                $html .= "<input type=\"submit\" value=\"アイテム一覧に戻る\">\n";
                $html .= "</form>\n";         
		$html .= "<br />\n";       
	}

        // キャラクターマスタ取得
        $L_CHARACTER = get_character_list();
        
	// 一覧取得
	$sql  = "SELECT * FROM " . T_GAMIFICATION_ITEM_CHARACTER_POSITION
                ." WHERE item_id= '".$cdb->real_escape($_POST['item_id'])."' AND mk_flg ='0' ORDER BY character_id, character_level;";
	// イメージ件数取得
	$count = 0;
	if ($result = $cdb->query($sql)) {
		$count = $cdb->num_rows($result);
	}

	// データ読み込み
	if ($result = $cdb->query($sql)) {

		// 取得データ件数が０の場合
		$check_count = 0;
		if ($result = $cdb->query($sql)) {
			$check_count = $cdb->num_rows($result);
		}
		if (!$check_count) {
			$html .= "現在、表示位置データは登録されていません。";
			return $html;
		}

		// 一覧表示開始
		$html .= "<br>\n";
		$html .= "修正する場合は、修正する表示位置の設定詳細ボタンを押してください。<br>\n";
                
		// 登録件数表示
		$html .= "<br>\n";
		$html .= "<div>件数(".$count.")</div><br>\n";

		$html .= "<table class=\"secret_form\">\n";
		$html .= "<tr class=\"secret_form_menu\">\n";

		//--------------------
		// リストタイトル表示
		//--------------------
		$html .= "<th>キャラクターID</th>\n";
		$html .= "<th>キャラクター名</th>\n";
		$html .= "<th>レベル</th>\n";
		$html .= "<th>表示位置(横)</th>\n";
		$html .= "<th>表示位置(縦)</th>\n";

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
			$html .= "<input type=\"hidden\" name=\"item_id\" value=\"".$list['item_id']."\">\n";
			$html .= "<input type=\"hidden\" name=\"character_id\" value=\"".$list['character_id']."\">\n";
			$html .= "<input type=\"hidden\" name=\"character_level\" value=\"".$list['character_level']."\">\n";
			$html .= "<td>".$list['character_id']."</td>\n";
			$html .= "<td>".$L_CHARACTER[$list['character_id']]."</td>\n";
			$html .= "<td>Lv. ".$list['character_level']."</td>\n";
			$html .= "<td>".$list['position_x']." px</td>\n";
			$html .= "<td>".$list['position_y']." px</td>\n";
			// 詳細ボタン（権限が有る場合は、ボタン表示）
			if (!ereg("practice__view",$authority)
				&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__view",$_SESSION['authority'])===FALSE))
			) {
				$html .= "<td><input type=\"submit\" name=\"mode\" value=\"設定詳細\"></td>\n";
			}

			// 削除ボタン（権限が有る場合は、ボタン表示）
			if (!ereg("practice__del",$authority)
				&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__del",$_SESSION['authority'])===FALSE))
			) {
				$html .= "<td><input type=\"submit\" name=\"mode\" value=\"設定削除\"></td>\n";
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
function position_addform($ERROR) {
    
        // DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$html .= "<br>\n";
	$html .= "アイテム 表示位置設定 新規登録フォーム<br>\n";
	$html .= "<br>\n";
        

	// エラーメッセージ表示
	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
        }

        if (ACTION != 'position_check' && $_POST['character_id'] > 0 && $_POST['character_level'] > 0) {
            $CHEKC_LIST = array();
            $CHEKC_LIST['item_id']      = $_POST['item_id'];
            $CHEKC_LIST['character_id'] = $_POST['character_id'];
            $CHEKC_LIST['character_level']        = $_POST['character_level'];
            $CHEKC_LIST['position_x'] = '0';
            $CHEKC_LIST['position_y'] = '0';
            $ERROR2 = position_check(null, $CHEKC_LIST);
            if ($ERROR2) {
                $html .= "<div class=\"small_error\">\n";
                $html .= ERROR($ERROR2);
                $html .= "</div>\n";
            }
        } 
        
        $html .= make_item_view_html();
	$html .= "<br>\n";
        
        // キャラクター× レベル未選択時
        if (!$_POST['character_id'] || !($_POST['character_level'] > 0) || $ERROR2) {
            
		// キャラクター選択
		if (!$_POST['character_id']) {

			$html .= "表示位置を設定するキャラクターを選択してください。<br>";
			$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" enctype=\"multipart/form-data\" id=\"addform\">\n";
			$html .= "<input type=\"hidden\" name=\"action\" value=\"position_addform\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"position_add\">\n";
			$html .= "<input type=\"hidden\" name=\"item_id\" value=\"".$_POST['item_id']."\">\n";
			$html .= "<select onchange=\"select_change_submit('addform', 'position_add', '');\" name=\"character_id\">\n";
			$html .= "<option value=\"0\">選択してください</option>\n";

			$can_use_character_id = null;
			$sql = "SELECT can_use_character_id FROM ".T_GAMIFICATION_ITEM." WHERE item_id = '".$cdb->real_escape($_POST['item_id'])."';";
			if ($result = $cdb->query($sql)) {
			    while ($list = $cdb->fetch_assoc($result)) {
				$can_use_character_id = $list['can_use_character_id'];
			    }
			}        

			$L_CHARACTER = get_character_list($can_use_character_id);
			if (is_array($L_CHARACTER)) {
			    foreach($L_CHARACTER as $k => $v) {
				$html .= "<option value=\"".$k."\">".$v."</option>\n";
			    }
			}
			$html .= "</select>\n";
			$html .= "</form>\n";

		} else {
			$html .= "表示位置を設定するキャラクターのレベルを選択してください。<br>";
			$L_CHARACTER = get_character_list($_POST['character_id']);
			$L_LEVEL = get_character_level_list($_POST['character_id']);
			$html .= $_POST['character_id'].":".$L_CHARACTER[$_POST['character_id']]."";

			$html .= "<form style=\"display:inline-block; margin-left:15px;\" action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" enctype=\"multipart/form-data\" id=\"addform\">\n";
			$html .= "<input type=\"hidden\" name=\"action\" value=\"position_addform\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"position_add\">\n";
			$html .= "<input type=\"hidden\" name=\"item_id\" value=\"".$_POST['item_id']."\">\n";
			$html .= "<input type=\"hidden\" name=\"character_id\" value=\"".$_POST['character_id']."\">\n";               
			if (is_array($L_LEVEL) && count($L_LEVEL) > 0) {
			    $html .= "<select name=\"character_level\">\n";
			    $html .= "<option value=\"0\">選択してください</option>\n";
			    foreach($L_LEVEL as $k => $v) {
				$html .= "<option value=\"".$k."\">".$v."</option>\n";
			    }
			    $html .= "</select>\n";
			    $html .= "<input type=\"submit\" value=\"設定する\">\n";
			} else {
				$html .= "レベルが登録されていません。\n";
			}
			$html .= "</form>\n";
		}
		$html .= "<br><br>\n";
        } else {
            
		$L_CHARACTER = get_character_list($_POST['character_id']);
		// 入力画面表示
		$html .= "";

		$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" enctype=\"multipart/form-data\" id=\"addform\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"position_check\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"position_add\">\n";
		$html .= "<input type=\"hidden\" name=\"item_id\" value=\"".$_POST['item_id']."\">\n";
		$html .= "<input type=\"hidden\" name=\"character_id\" value=\"".$_POST['character_id']."\">\n";
		$html .= "<input type=\"hidden\" name=\"character_level\" value=\"".$_POST['character_level']."\">\n";

		// htmlクラス生成
		$make_html = new read_html();
		$make_html->set_dir(ADMIN_TEMP_DIR);
		$make_html->set_file(GAMIFICATION_ITEM_CHARACTER_POSITION);

		// キャラクター画像取得
		$character_img_html = "";
		$character_img_path = MATERIAL_GAM_CHARACTER_DIR.'common/'.$_POST['character_id'].'/img/'.$_POST['character_id'].'_1_'.$_POST['character_level'];
		foreach (glob($character_img_path.'.*') as $val ) {
		    if (!preg_match ('/eat|training|revival/', $val)) {
			$character_img_html .= '<img class="character" src="'.$val.'" style="max-width:600px;"><br>';
		    }
		}
		// アイテム画像取得
		// upd start hasegawa 2019/06/07 該当するレベルのアイテム画像があったらそれを表示
		$item_img_html = "";
		// foreach (glob(MATERIAL_GAM_ITEM_DIR.'item_'.$_POST['item_id'].'.*') as $val ) {
		//	$item_img_html .= '<img class="item" src="'.$val.'" style="max-width:600px;"><br>';
		// }
		foreach (glob(MATERIAL_GAM_ITEM_DIR.'item_'.$_POST['item_id'].'_'.$_POST['character_level'].'.*') as $val ) {
			$item_img_html .= '<img class="item" src="'.$val.'" style="max-width:600px;"><br>';
		}
		// なければ通常画像を使用
		if ($item_img_html == '') {
			foreach (glob(MATERIAL_GAM_ITEM_DIR.'item_'.$_POST['item_id'].'.*') as $val ) {
				$item_img_html .= '<img class="item" src="'.$val.'" style="max-width:600px;"><br>';
			}
		}
		// upd end hasegawa 2019/06/07
            
		$INPUTS['CHARACTERID']	= array('result'=>'plane', 'value'=>$_POST['character_id'] ?: null);
		$INPUTS['CHARACTERNAME']	= array('result'=>'plane', 'value'=>$L_CHARACTER[$_POST['character_id']] ?: null);
		$INPUTS['CHARACTERLEVEL']	= array('result'=>'plane', 'value'=>$_POST['character_level'] ?: null);
		$INPUTS['POSITIONX']	= array('result'=>'form', 'type' => 'text', 'name' => 'position_x', 'size' => '20', 'value'=>(string)$_POST['position_x'] ?: '0');
		$INPUTS['POSITIONY']	= array('result'=>'form', 'type' => 'text', 'name' => 'position_y', 'size' => '20', 'value'=>(string)$_POST['position_y'] ?: '0');
		$INPUTS['CHARACTERIMG']	= array('result'=>'plane', 'value'=>$character_img_html);
		$INPUTS['ITEMIMG']          = array('result'=>'plane', 'value'=>$item_img_html);

		$make_html->set_rep_cmd($INPUTS);
		$html .= $make_html->replace();
		// 制御ボタン定義
		$html .= "<br>\n";
		$html .= "<input type=\"submit\" value=\"追加確認\">\n";
			    $html .= "<input type=\"reset\" value=\"クリア\">";
		$html .= "</form>\n";
		$html .= "<br>\n";
		$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"item_id\" value=\"".$_POST['item_id']."\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"position_addform\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"position_add\">\n";
		$html .= "<input type=\"hidden\" name=\"character_id\" value=\"\">\n";
		$html .= "<input type=\"submit\" value=\"キャラクター選択に戻る\">\n";
		$html .= "</form>\n";
        }
        
        $html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
        $html .= "<input type=\"hidden\" name=\"mode\" value=\"position_list\">\n";
        $html .= "<input type=\"hidden\" name=\"item_id\" value=\"".$_POST['item_id']."\">\n";
        $html .= "<input type=\"submit\" value=\"表示位置一覧に戻る\">\n";
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
function position_viewform($ERROR) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// POST値取得
	if (ACTION=="position_check" || ACTION=="back") {
		// POST値取得
		foreach ($_POST as $key => $val) {
			$$key = $val;
		}
	} else {
		$sql = "SELECT * FROM " . T_GAMIFICATION_ITEM_CHARACTER_POSITION
			." WHERE character_id ='".$cdb->real_escape($_POST['character_id'])."'"
                        ." AND item_id = '".$cdb->real_escape($_POST['item_id'])."'"
                        ." AND character_level = '".$cdb->real_escape($_POST['character_level'])."'"
                        ." AND mk_flg='0' LIMIT 1;";
                
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
        $html .= make_item_view_html();
	$html .= "<br>\n";
	// エラーメッセージ表示
	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}
	// 入力画面表示
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"設定詳細\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"position_check\">\n";
	$html .= "<input type=\"hidden\" name=\"item_id\" value=\"".$item_id."\">\n";
	$html .= "<input type=\"hidden\" name=\"character_id\" value=\"".$character_id."\">\n";
	$html .= "<input type=\"hidden\" name=\"character_level\" value=\"".$character_level."\">\n";
        
	// htmlクラス生成
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(GAMIFICATION_ITEM_CHARACTER_POSITION);
        
        $L_CHARACTER = get_character_list();        
        
        // キャラクター画像取得
        $character_img_html = "";
        $character_img_path = MATERIAL_GAM_CHARACTER_DIR.'common/'.$character_id.'/img/'.$character_id.'_1_'.$character_level;
        foreach (glob($character_img_path.'.*') as $val ) {
            if (!preg_match ('/eat|training|revival/', $val)) {
                $character_img_html .= '<img class="character" src="'.$val.'" style="max-width:600px;"><br>';
            }
        }
        
        // アイテム画像取得
	// upd start hasegawa 2019/06/07 該当するレベルのアイテム画像があったらそれを表示
	$item_img_html = "";
        // foreach (glob(MATERIAL_GAM_ITEM_DIR.'item_'.$item_id.'.*') as $val ) {
        //     $item_img_html .= '<img class="item" src="'.$val.'" style="max-width:600px;"><br>';
        // }
	foreach (glob(MATERIAL_GAM_ITEM_DIR.'item_'.$item_id.'_'.$character_level.'.*') as $val ) {
		$item_img_html .= '<img class="item" src="'.$val.'" style="max-width:600px;"><br>';
	}
	// なければ通常画像を使用
	if ($item_img_html == '') {
		foreach (glob(MATERIAL_GAM_ITEM_DIR.'item_'.$item_id.'.*') as $val ) {
			$item_img_html .= '<img class="item" src="'.$val.'" style="max-width:600px;"><br>';
		}
	}
	// upd end hasegawa 2019/06/07
	
	
        $INPUTS['CHARACTERID']          = array('result'=>'plane', 'value'=>$character_id ?: null);
        $INPUTS['CHARACTERNAME']	= array('result'=>'plane', 'value'=>$L_CHARACTER[$character_id] ?: null);
        $INPUTS['CHARACTERLEVEL']	= array('result'=>'plane', 'value'=>$character_level ?: null);
        $INPUTS['POSITIONX']            = array('result'=>'form', 'type' => 'text', 'name' => 'position_x', 'size' => '20', 'value'=>(string)$position_x ?: '0');
        $INPUTS['POSITIONY']            = array('result'=>'form', 'type' => 'text', 'name' => 'position_y', 'size' => '20', 'value'=>(string)$position_y ?: '0');
        $INPUTS['CHARACTERIMG']         = array('result'=>'plane', 'value'=>$character_img_html);
        $INPUTS['ITEMIMG']              = array('result'=>'plane', 'value'=>$item_img_html);
        
	$make_html->set_rep_cmd($INPUTS);
	$html .= $make_html->replace();
        
	// 制御ボタン定義
	$html .= "<input type=\"submit\" value=\"変更確認\">";
	$html .= "<input type=\"reset\" value=\"クリア\">";
	$html .= "</form><br>\n";
	// 一覧に戻る
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"position_list\">\n";
	$html .= "<input type=\"hidden\" name=\"item_id\" value=\"".$item_id."\">\n";
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
function position_check($ERROR, $LINE = null) {
    
	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
        
        $CHECK_LIST = array();
        if (is_array($LINE) && count($LINE) > 0) {
            $CHECK_LIST = $LINE;
        } else {
            $CHECK_LIST = $_POST;
        }
        
        // アイテムID
        if (!$CHECK_LIST['item_id']) {
		$ERROR[] = "アイテムIDが未入力です。";
        } elseif(!is_numeric($CHECK_LIST['item_id'])) {
            	$ERROR[] = "アイテムIDの値が不正です。";
        } else {
                $count = 0;
                $sql = "SELECT * FROM ".T_GAMIFICATION_ITEM." WHERE item_id = '".$cdb->real_escape($CHECK_LIST['item_id'])."' AND mk_flg='0';";
                if ($result = $cdb->query($sql)) {
                        $count = $cdb->num_rows($result);
                }
                if ($count == 0) {
                    $ERROR[] = "入力されたアイテムIDは存在しません";
                }
        }
        // キャラクターID
        if (!$CHECK_LIST['character_id']) {
		$ERROR[] = "キャラクターIDが未入力です。";
        } elseif(!is_numeric($CHECK_LIST['character_level'])) {
            	$ERROR[] = "キャラクターIDの値が不正です。";
        } else {
                $count = 0;
                $sql = "SELECT * FROM ".T_GAMIFICATION_CHARACTER." WHERE character_id = '".$cdb->real_escape($CHECK_LIST['character_id'])."' AND mk_flg='0';";
                if ($result = $cdb->query($sql)) {
                        $count = $cdb->num_rows($result);
                }
                if ($count == 0) {
                    $ERROR[] = "入力されたキャラクターIDは存在しません";
                }
        }
        // レベル
        if (!$CHECK_LIST['character_level']) {
		$ERROR[] = "レベルが未入力です。";
        } elseif(!is_numeric($CHECK_LIST['character_level'])) {
            	$ERROR[] = "レベルの値が不正です。";
        } else {
                $count = 0;
                $sql = "SELECT * FROM ".T_GAMIFICATION_CHARACTER_LEVEL." WHERE"
                        . " character_id = '".$cdb->real_escape($CHECK_LIST['character_id'])."'"
                        . " AND character_level = '".$cdb->real_escape($CHECK_LIST['character_level'])."'"
                        . " AND mk_flg='0';";
                if ($result = $cdb->query($sql)) {
                        $count = $cdb->num_rows($result);
                }
                if ($count == 0) {
                    $ERROR[] = "入力されたキャラクターレベルは存在しません";
                }
        }
        
        // 重複チェック
        if (empty($ERROR) && MODE == 'position_add' && $CHECK_LIST['item_id'] && $CHECK_LIST['character_id'] && $CHECK_LIST['character_level']) {
                $count = 0;
                $sql = "SELECT * FROM ".T_GAMIFICATION_ITEM_CHARACTER_POSITION." WHERE"
                        . " item_id = '".$cdb->real_escape($CHECK_LIST['item_id'])."'"
                        . " AND character_id = '".$cdb->real_escape($CHECK_LIST['character_id'])."'"
                        . " AND character_level = '".$cdb->real_escape($CHECK_LIST['character_level'])."'"
                        . " AND mk_flg='0';";
                if ($result = $cdb->query($sql)) {
                        $count = $cdb->num_rows($result);
                }
                if ($count > 0) {
                    $ERROR[] = "指定されたキャラクター・レベルには、ポジション設定が既に存在しています。";
                }
        }
        
        // 表示位置X
        if ($CHECK_LIST['position_x'] == '') {
            $ERROR[] = "表示位置Xが未入力です";
        } elseif (!is_numeric($CHECK_LIST['position_x'])) {
            $ERROR[] = "表示位置Xの値が不正です。";
        }
        // 表示位置Y
        if ($CHECK_LIST['position_y'] == '') {
            $ERROR[] = "表示位置Yが未入力です";
        } elseif (!is_numeric($CHECK_LIST['position_y'])) {
            $ERROR[] = "表示位置Yの値が不正です。";
        }
		//使用可能キャラクターチェック
		 $sql = "SELECT can_use_character_id FROM ".T_GAMIFICATION_ITEM." WHERE"
			. " item_id = '".$cdb->real_escape($CHECK_LIST['item_id'])."'"
			. " AND mk_flg='0';";
		 $can_use_character_id = 0;
		 if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$can_use_character_id = $list['can_use_character_id'];
		 }
		 //使用可能キャラクターが0(未選択)以外だったら、登録するキャラクターIDと比較する。
		 if($can_use_character_id){
			 if($can_use_character_id != $CHECK_LIST['character_id']){
				$ERROR[] = "アイテム使用可能キャラクターに設定されているキャラクター以外は登録できません";
			 }
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
function position_check_html() {
        
	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// アクション情報をhidden項目に設定
	if ($_POST) {
		foreach ($_POST as $key => $val) {
			if ($key == "action") {
				if (MODE == "position_add") {
					$val = "position_add";
				// 詳細から来た時はchange（更新処理）に変える
				} elseif (MODE == "設定詳細") {
					$val = "position_change";
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
		$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"position_change\">\n";
		$sql = "SELECT * FROM ".T_GAMIFICATION_ITEM_CHARACTER_POSITION
			." WHERE character_id ='".$cdb->real_escape($_POST['character_id'])."'"
                        ." AND item_id ='".$cdb->real_escape($_POST['item_id'])."'"
                        ." AND character_level ='".$cdb->real_escape($_POST['character_level'])."'"
                        ." AND mk_flg = '0' LIMIT 1;";
                        
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
        $position_x = $position_x?:'0';
        $position_y = $position_y?:'0';
        
        // キャラクター一覧取得
        $L_CHALACTER = get_character_list();                
                
	// ボタン表示文言判定
	if (MODE == "設定削除") {
		$button = "設定削除";
	} else if (MODE == "設定詳細") {
		$button = "設定更新";
	} else {
		$button = "設定登録";
	}
	// 入力確認画面表示
	$html = "<br>\n";
	$html .= "確認画面：以下の内容で".$button."してもよろしければ".$button."ボタンをクリックしてください。<br>\n";
	$html .= "<br>\n";        
        $html .= make_item_view_html();
	$html .= "<br>\n";        
	// htmlクラス生成
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(GAMIFICATION_ITEM_CHARACTER_POSITION);       
        
        // キャラクター画像取得
        $character_img_html = "";
        $character_img_path = MATERIAL_GAM_CHARACTER_DIR.'common/'.$character_id.'/img/'.$character_id.'_1_'.$character_level;
        foreach (glob($character_img_path.'.*') as $val ) {
            if (!preg_match ('/eat|training|revival/', $val)) {
                $character_img_html .= '<img class="character" src="'.$val.'" style="max-width:600px;"><br>';
            }
        }
        // アイテム画像取得
        $item_img_html = "";

	// upd start hasegawa 2019/06/07 該当するレベルのアイテム画像があったらそれを表示
	$item_img_html = "";
        // foreach (glob(MATERIAL_GAM_ITEM_DIR.'item_'.$item_id.'.*') as $val ) {
        //     $item_img_html .= '<img class="item" src="'.$val.'" style="max-width:600px; left:'.$position_x.'px; bottom:'.$position_y.'px;"><br>';
        // }
	foreach (glob(MATERIAL_GAM_ITEM_DIR.'item_'.$item_id.'_'.$character_level.'.*') as $val ) {
		$item_img_html .= '<img class="item" src="'.$val.'" style="max-width:600px; left:'.$position_x.'px; bottom:'.$position_y.'px;"><br>';
	}
	// なければ通常画像を使用
	if ($item_img_html == '') {
		foreach (glob(MATERIAL_GAM_ITEM_DIR.'item_'.$item_id.'.*') as $val ) {
			$item_img_html .= '<img class="item" src="'.$val.'" style="max-width:600px; left:'.$position_x.'px; bottom:'.$position_y.'px;"><br>';
		}
	}
	// upd end hasegawa 2019/06/07
	
	
        $L_CHARACTER = get_character_list();
        
        $INPUTS['CHARACTERID']      = array('result'=>'plane', 'value'=>$character_id);
        $INPUTS['CHARACTERNAME']    = array('result'=>'plane', 'value'=>$L_CHARACTER[$character_id]);
        $INPUTS['CHARACTERLEVEL']   = array('result'=>'plane', 'value'=>$character_level);
        $INPUTS['POSITIONX']        = array('result'=>'plane', 'value'=>(string)$position_x);
        $INPUTS['POSITIONY']        = array('result'=>'plane', 'value'=>(string)$position_y);
        $INPUTS['CHARACTERIMG']     = array('result'=>'plane', 'value'=>$character_img_html);
        $INPUTS['ITEMIMG']          = array('result'=>'plane', 'value'=>$item_img_html);

	$make_html->set_rep_cmd($INPUTS);
	$html .= $make_html->replace();

	// 制御ボタン定義
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" style=\"float:left\">\n";
	$html .= $HIDDEN;
	// 削除の場合は、１画面目に戻る
	if (MODE == "設定削除") {
		$html .= "<input type=\"hidden\" name=\"s_page\" value=\"1\">\n";
                $html .= "<input type=\"hidden\" name=\"item_id\" value=\"".$item_id."\">\n";
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
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"position_list\">\n";
		$html .= "<input type=\"hidden\" name=\"item_id\" value=\"".$item_id."\">\n";
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
function position_add() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
        
        // プライマリーチェック
        $count = 0;
        $sql = "SELECT * FROM ".T_GAMIFICATION_ITEM_CHARACTER_POSITION." WHERE"
                . " item_id = '".$cdb->real_escape($_POST['item_id'])."'"
                . " AND character_id = '".$cdb->real_escape($_POST['character_id'])."'"
                . " AND character_level = '".$cdb->real_escape($_POST['character_level'])."';";
        if ($result = $cdb->query($sql)) {
                $count = $cdb->num_rows($result);
        }

	// 登録項目設定
        $INSERT_DATA = array();
	$INSERT_DATA['item_id']                 = $_POST['item_id'];
        $INSERT_DATA['character_id']            = $_POST['character_id'];
        $INSERT_DATA['character_level']         = $_POST['character_level'];
	$INSERT_DATA['position_x']              = $_POST['position_x'] ?: '0';
	$INSERT_DATA['position_y']              = $_POST['position_y'] ?: '0';
	$INSERT_DATA['upd_syr_id'] 		= "add";
	$INSERT_DATA['upd_date']   		= "now()";
	$INSERT_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];

        if ($count > 0) {
            $INSERT_DATA['mk_tts_id'] 		= '';
            $INSERT_DATA['mk_date'] 		= '';
            $INSERT_DATA['mk_flg'] 		= '0';
            // ＤＢ追加処理
            $where = " WHERE item_id = '".$cdb->real_escape($_POST['item_id']) ."'"
                    ." AND character_id = '".$cdb->real_escape($_POST['character_id']) ."'"
                    ." AND character_level = '".$cdb->real_escape($_POST['character_level']) ."'"
                    ." LIMIT 1;";                       
            $ERROR = $cdb->update(T_GAMIFICATION_ITEM_CHARACTER_POSITION,$INSERT_DATA, $where);

        } else {
            $INSERT_DATA['ins_syr_id'] 		= "add";
            $INSERT_DATA['ins_date']  		= "now()";
            $INSERT_DATA['ins_tts_id'] 		= $_SESSION['myid']['id'];
            // ＤＢ追加処理
            $ERROR = $cdb->insert(T_GAMIFICATION_ITEM_CHARACTER_POSITION,$INSERT_DATA);
        }     

	if (!$ERROR) {
            $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>";
            // ディレクトリ作成
            $id = $cdb->insert_id();
            if ($id) {
                create_directory($id);
            }
        }
        unset($_SESSION['upload_img_path']);
        
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
function position_change() {
    
        // DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
        
	// 更新処理
	if (MODE == "設定詳細") {
		// DBアップデート
                $UPDATE_DATA = array();
                $UPDATE_DATA['position_x']              = $_POST['position_x'] ?: '0';
                $UPDATE_DATA['position_y']              = $_POST['position_y'] ?: '0';
                $UPDATE_DATA['upd_syr_id']              = "update";
		$UPDATE_DATA['upd_date']                = "now()";
		$UPDATE_DATA['upd_tts_id']              = $_SESSION['myid']['id'];
		$where = " WHERE item_id = '".$cdb->real_escape($_POST['item_id']) ."'"
                        ." AND character_id = '".$cdb->real_escape($_POST['character_id']) ."'"
                        ." AND character_level = '".$cdb->real_escape($_POST['character_level']) ."'"
                        ." LIMIT 1;";
		$ERROR = $cdb->update(T_GAMIFICATION_ITEM_CHARACTER_POSITION, $UPDATE_DATA, $where);

	// 削除処理
	} elseif (MODE == "設定削除") {
                $UPDATE_DATA = array();
		$UPDATE_DATA['mk_flg']	   = "1";
		$UPDATE_DATA['mk_tts_id']  = $_SESSION['myid']['id'];
		$UPDATE_DATA['mk_date']	   = "now()";
		$UPDATE_DATA['upd_syr_id'] = "del";
		$UPDATE_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
		$UPDATE_DATA['upd_date']   = "now()";
		$where = " WHERE item_id = '".$cdb->real_escape($_POST['item_id']) ."'"
                        ." AND character_id = '".$cdb->real_escape($_POST['character_id']) ."'"
                        ." AND character_level = '".$cdb->real_escape($_POST['character_level']) ."'"
                        ." LIMIT 1;";
		$ERROR = $cdb->update(T_GAMIFICATION_ITEM_CHARACTER_POSITION, $UPDATE_DATA, $where);

	}
        
        if (!$ERROR) { $_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>"; }
        
	return $ERROR;
}
/**
 * アイテムの詳細htmlを作成する
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string html
 */
function make_item_view_html() {
    
	// グローバル変数
	global $L_GAM_RARITY, $L_GAM_ITEM_CLASS, $L_GAM_ITEM_KIND, $L_DISPLAY;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
        $html = '';
    
        $sql = "SELECT * FROM ".T_GAMIFICATION_ITEM.
                " WHERE item_id='".$cdb->real_escape($_POST['item_id'])."' AND mk_flg = '0' LIMIT 1;";
        $result = $cdb->query($sql);
        $list = $cdb->fetch_assoc($result);
        if (!$list) {
                $html .= "既に削除されているか、不正な情報が混ざっています。";
        } else {
            foreach ($list as $key => $val) {
                    $$key = replace_decode($val);
                    $$key = ereg_replace("\"","&quot;",$$key);
            }
            // 画像取得
            $upload_html = "";
            foreach (glob(MATERIAL_GAM_ITEM_DIR.'item_'.$_POST['item_id'].'.*') as $val ) {
                $upload_html .= '<img src="'.$val.'" style="max-width:600px;"><br>';
            }
            
            $L_CHALACTER = get_character_list();
            
            // htmlクラス生成
            $make_html = new read_html();
            $make_html->set_dir(ADMIN_TEMP_DIR);
            $make_html->set_file(GAMIFICATION_ITEM_CHARACTER_POSITION_ITEM_INFO);        
            $INPUTS['ITEMID']           = array('result'=>'plane','value'=>$item_id ?:'----');
            $INPUTS['ITEMNAME']         = array('result'=>'plane','value'=>$item_name);
            $INPUTS['ITEMCLASS']        = array('result'=>'plane','value'=>$L_GAM_ITEM_CLASS[$item_class]?:null);
            $INPUTS['ITEMKIND']         = array('result'=>'plane','value'=>$L_GAM_ITEM_KIND[$item_class][$item_kind]?:'<small>指定なし</small>');
            $INPUTS['USECHARACTER']     = array('result'=>'plane','value'=>$L_CHALACTER[$can_use_character_id]?:'<small>指定なし</small>');
            $INPUTS['PARENTITEMID']     = array('result'=>'plane','value'=>$parent_item_id?:'<small>指定なし</small>');
            $INPUTS['RARITY']           = array('result'=>'plane','value'=>$L_GAM_RARITY[$rarity]?:null);

            if ($upload_html) {
                $INPUTS['UPLOAD']       = array('result'=>'plane','value'=>$upload_html);
            }
            $INPUTS['DISPLAY']          = array('result'=>'plane','value'=>$L_DISPLAY[$display]);
            $make_html->set_rep_cmd($INPUTS);
            $html .= $make_html->replace();
        } 
        
        return $html;
}
/**
 * インポート処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function position_import_import() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$file_name = $_FILES['position_file']['name'];
	$file_tmp_name = $_FILES['position_file']['tmp_name'];
	$file_error = $_FILES['position_file']['error'];

	if (!$file_tmp_name) {
		$ERROR[] = "アイテム 表示位置txtファイルが指定されておりません。";
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
			if (count($file_data) == 6) {
				$LINE['item_id']                = $file_data[0];
				$LINE['character_id']           = $file_data[1];
				$LINE['character_level']                  = $file_data[2];
				$LINE['position_x']             = $file_data[3];
				$LINE['position_y']             = $file_data[4];
				$LINE['mk_flg'] 		= $file_data[5];
			} else {
				$ERROR[] = '<b>'.($i+1).'行目　未入力の項目があるためスキップしました。</b>';
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
			$error_flg = false;
			$LINE_ERROR = array();
			$LINE_ERROR = position_check(null,$LINE);
			/* ============================================================================ */
			// エラーの時はスキップする
			if(is_array($LINE_ERROR) && count($LINE_ERROR) > 0) {
				$ERROR[] = '<b>'.($i+1)."行目　以下のエラーの為、スキップしました。</b>";
				foreach($LINE_ERROR as $v) {
					$ERROR[] = $v;
				}
				continue;
			}

			// データ存在チェック
			$sql  = "SELECT * FROM " .T_GAMIFICATION_ITEM_CHARACTER_POSITION
					." WHERE item_id = '".$LINE['item_id']."'"
					." AND character_id = '".$LINE['character_id']."'"
					." AND character_level = '".$LINE['character_level']."'"
                                        .";";
                        
                        if ($result = $cdb->query($sql)) {
				$exist_count = $cdb->num_rows($result);
			}


			// 登録処理
			if ($exist_count == 0) {
                                $INSERT_DATA = array();
                                $INSERT_DATA['item_id']                 = (string)$LINE['item_id'];
                                $INSERT_DATA['character_id']            = (string)$LINE['character_id'];
                                $INSERT_DATA['character_level']         = (string)$LINE['character_level'];
                                $INSERT_DATA['position_x']              = (string)$LINE['position_x'];
                                $INSERT_DATA['position_y']              = (string)$LINE['position_y'];
                                $INSERT_DATA['ins_syr_id'] 		= "add";
                                $INSERT_DATA['ins_date']  		= "now()";
                                $INSERT_DATA['ins_tts_id'] 		= $_SESSION['myid']['id'];
                                $INSERT_DATA['upd_syr_id'] 		= "add";
                                $INSERT_DATA['upd_date']   		= "now()";
                                $INSERT_DATA['upd_tts_id'] 		= $_SESSION['myid']['id'];

				$INSERT_ERROR = $cdb->insert(T_GAMIFICATION_ITEM_CHARACTER_POSITION, $INSERT_DATA);
      
			// 更新処理・削除処理
			} else {
				// 更新
				$INSERT_DATA = array();
				if ($LINE['mk_flg'] == '0') {
					$INSERT_DATA['position_x']              = (string)$LINE['position_x'];
					$INSERT_DATA['position_y']              = (string)$LINE['position_y'];
					$INSERT_DATA['mk_flg']			= "0";
					$INSERT_DATA['mk_tts_id']		= "NULL";
					$INSERT_DATA['mk_date']			= "NULL";
					$INSERT_DATA['upd_syr_id']		= "update";
					$INSERT_DATA['upd_date']		= "now()";
					$INSERT_DATA['upd_tts_id']		= $_SESSION['myid']['id'];

				// 削除
				} else {
					$INSERT_DATA['mk_flg']			= "1";
					$INSERT_DATA['mk_tts_id']		= $_SESSION['myid']['id'];
					$INSERT_DATA['mk_date']			= "now()";
					$INSERT_DATA['upd_syr_id']		= "del";
					$INSERT_DATA['upd_tts_id']		= $_SESSION['myid']['id'];
					$INSERT_DATA['upd_date']		= "now()";

                                }
				$where = " WHERE item_id = '".$cdb->real_escape($LINE['item_id']) ."'"
                                        ." AND character_id = '".$cdb->real_escape($LINE['character_id']) ."'"
                                        ." AND character_level = '".$cdb->real_escape($LINE['character_level']) ."'"
                                        ." LIMIT 1;";
				$INSERT_ERROR = $cdb->update(T_GAMIFICATION_ITEM_CHARACTER_POSITION, $INSERT_DATA, $where);
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

	// アップロードファイル削除
	if ($file_tmp_name && file_exists($file_tmp_name)) {
		unlink($file_tmp_name);
	}
	return $ERROR;
}


/**
 * キャラクターのレベルを取得する
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array $character_level_list キャラクターレベル配列
 */
function get_character_level_list($character_id) {

        // DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];
        $character_level_list = array();
        
        if (!$character_id) { return; }
        
        // キャラクターマスタ取得
        $sql = " SELECT * FROM ".T_GAMIFICATION_CHARACTER_LEVEL
                ." WHERE mk_flg = '0'"
                .";";
        if ($result = $cdb->query($sql)) {
            while ($list = $cdb->fetch_assoc($result)) {
                $character_level_list[$list['character_level']] = 'Lv.'.$list['character_level'];
            }
        }
        
        return $character_level_list;
}
