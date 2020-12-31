<?php
/**
 * ベンチャー・リンク　すらら
 *
 * プラクティスステージ管理　ワンポイント解説作成
 *
 * @author Azet
 */


/**
 * メイン処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function start() {

	// del start oda 2013/01/22
	//	// サブセション取得
	//	if (ACTION == "set_course") {
	//		$_SESSION['sub_session']['s_page_view'] = "";
	//		$_SESSION['sub_session']['s_page'] = "";
	//
	//	} else {
	//		$ERROR = sub_session();
	//	}
	// del start oda 2013/01/22

	// 入力内容チェック
	if (ACTION == "one_point_check" || ACTION == "ワンポイント解説追加確認" || ACTION == "ワンポイント解説変更確認") {
		$ERROR = one_point_check();
	}

	// 更新処理
	if (!$ERROR) {
		if (ACTION == "one_point_add") { $ERROR = one_point_add(); }
		elseif (ACTION == "one_point_change") { $ERROR = one_point_change(); }
		elseif (ACTION == "one_point_up") { $ERROR = one_point_up(); }				// 2013/01/22 add oda
		elseif (ACTION == "one_point_down") { $ERROR = one_point_down(); }			// 2013/01/22 add oda
	}

	// コース選択情報取得
	list($html,$L_COURSE) = select_course();

	// 表示内容制御
	if (MODE == "one_point_form") {
		if (ACTION == "one_point_check" || ACTION == "ワンポイント解説追加確認" || ACTION == "ワンポイント解説変更確認") {
			if (!$ERROR) {
				$html .= one_point_check_html($L_COURSE);
			} else {
				$html .= one_point_form($ERROR,$L_COURSE);
			}
		} elseif (ACTION == "one_point_add") {
			if (!$ERROR) {
				$html .= one_point_list($ERROR, $L_COURSE);
			} else {
				$html .= one_point_form($ERROR,$L_COURSE);
			}
		} elseif (ACTION == "one_point_change") {
			if (!$ERROR) {
				$html .= one_point_list($ERROR, $L_COURSE);
			} else {
				$html .= one_point_form($ERROR,$L_COURSE);
			}
		} else {
			$html .= one_point_form($ERROR,$L_COURSE);
		}
	} elseif (MODE == "one_point_delete") {
		if (ACTION == "one_point_change") {
			if (!$ERROR) {
				$html .= one_point_list($ERROR, $L_COURSE);
			} else {
				$html .= one_point_check_html($L_COURSE);
			}
		}elseif (ACTION == "back") {
			$html .= one_point_list($ERROR, $L_COURSE);
		} else {
			$html .= one_point_check_html($L_COURSE);
		}
	} elseif (MODE == "one_point_upload") {
		$ERROR = one_point_upload();
		$html .= one_point_list($ERROR,$L_COURSE);
	} else {
		if ($_POST['course_num']) {
			$html .= one_point_list($ERROR, $L_COURSE);
		}
	}

	return $html;
}


/**
 * コース選択
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array
 */
function select_course() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// コース情報取得
	$sql  = "SELECT * FROM ".T_COURSE.
			" WHERE state = '0'".
			"   AND write_type = '1'".
			" ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
	}

	if (!$max) {
		$html = "<br>\n";
		$html .= "英語のコースが存在しません。設定してからご利用下さい。";
		return array($html,$L_COURSE);
	} else {
		if (!$_POST['course_num']) { $selected = "selected"; } else { $selected = ""; }
		$couse_num_html .= "<option value=\"\" $selected>選択して下さい</option>\n";
		while ($list = $cdb->fetch_assoc($result)) {
			$course_num_ = $list['course_num'];
			$course_name_ = $list['course_name'];
			$L_COURSE[$course_num_] = $course_name_;
			if ($_POST['course_num'] == $course_num_) { $selected = "selected"; } else { $selected = ""; }
			$couse_num_html .= "<option value=\"{$course_num_}\" $selected>{$course_name_}</option>\n";
		}
	}

	$html = "<br>\n";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" name=\"menu\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"set_course\">\n";
	$html .= "<table class=\"unit_form\">\n";
	$html .= "<tr class=\"unit_form_menu\">\n";
	$html .= "<td>コース</td>\n";
	$html .= "</tr>\n";
	$html .= "<tr class=\"unit_form_cell\">\n";
	$html .= "<td><select name=\"course_num\" onchange=\"submit_course();\">\n";
	$html .= $couse_num_html;
	$html .= "</select></td>\n";
	$html .= "</tr>\n";
	$html .= "</table>\n";
	$html .= "</form>\n";

	if (!$_POST[course_num]) {
		$html .= "<br>\n";
		$html .= "ワンポイント解説を設定するコースを選択してください。<br>\n";
	}

	return array($html,$L_COURSE);
}


/**
 * ワンポイント解説選択
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @param array $L_COURSE
 * @return string HTML
 */
function one_point_list($ERROR, $L_COURSE) {

	// グローバル変数定義
	global $L_DISPLAY, $L_PAGE_VIEW;
	//	add 2015/01/07 yoshizawa 課題要望一覧No.400対応
	global $L_EXP_CHA_CODE;
	//-------------------------------------------------

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// ログイン情報取得
	//list($me_num,$onetime,$manager_level,$belong_num,$authority) = explode("<>",$_SESSION[myid]);

	if ($authority) { $L_AUTHORITY = explode("::",$authority); }

	// エラーメッセージが存在する場合、メッセージ表示
	if ($ERROR) {
		$html .= "<br>\n";
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	$html .= "<br>\n";
	if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__block_add",$_SESSION['authority'])===FALSE)) {
		$html .= "<table>\n";
		$html .= "<tr>\n";
		$html .= "<td>\n";
		$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"one_point_form\">\n";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"one_point_addform\">\n";
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
		$html .= "<input type=\"submit\" value=\"ワンポイント解説新規登録\">\n";
		$html .= "</form>\n";
		$html .= "</td>\n";
		//	del 2015/01/07 yoshizawa 課題要望一覧No.400対応 下に新規で作成
		//$html .= "<td>\n";
		//$html .= "<form action=\"./one_point_make_csv.php\" method=\"POST\">";
		//$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
		//$html .= "<input type=\"submit\" value=\"ワンポイント解説ダウンロード\">";
		//$html .= "</form>";
		//$html .= "</td>\n";
		//----------------------------------------------------------------
		$html .= "<td>\n";
		$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" enctype=\"multipart/form-data\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"one_point_upload\">\n";
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
		$html .= "<input type=\"file\" size=\"40\" name=\"one_point_file\">\n";
		$html .= "<input type=\"submit\" value=\"ワンポイント解説アップロード\">\n";
		$html .= "</form>\n";
		$html .= "</td>\n";
		$html .= "</tr>\n";
		$html .= "</table>\n";
		$html .= "<br>\n";

		//	add 2015/01/07 yoshizawa 課題要望一覧No.400対応
		$html .= "<form action=\"./one_point_make_csv.php\" method=\"POST\">";
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
		$html .= "<input type=\"submit\" value=\"ワンポイント解説ダウンロード\">";
		$html .= "<br /><br />\n";

		$html .= "</form>";
		//-------------------------------------------------

		$one_point_img_ftp = FTP_URL."point_img/".$_POST['course_num']."/";
		$one_point_voice_ftp = FTP_URL."point_voice/".$_POST['course_num']."/";

		$html .= FTP_EXPLORER_MESSAGE; //add hirose 2018/04/23 FTPをブラウザからのエクスプローラーで開けない不具合対応

		$html .= "<a href=\"".$one_point_img_ftp."\" target=\"_blank\">ポイント解説画像フォルダー($one_point_img_ftp)</a><br>\n";
		$html .= "<a href=\"".$one_point_voice_ftp."\" target=\"_blank\">ポイント解説音声フォルダー($one_point_voice_ftp)</a><br>\n";
		$html .= "<br>\n";
		$html .= "修正する場合は、修正するワンポイント解説の詳細ボタンを押して下さい。\n";
		$html .= "<br>\n";
	}

	// ＳＱＬ文作成
	$sql  = "SELECT op.*, ".
			" co.course_name, ".
			" st.stage_name, ".
			" ls.lesson_name, ".
			" ut.unit_name ".
			" FROM ".T_ONE_POINT." op ".
			" INNER JOIN ".T_COURSE." co ON op.course_num = co.course_num ".
			" INNER JOIN ".T_STAGE." st ON op.stage_num = st.stage_num ".
			" INNER JOIN ".T_LESSON." ls ON op.lesson_num = ls.lesson_num ".
			" INNER JOIN ".T_UNIT." ut ON op.unit_num = ut.unit_num ".
			" WHERE op.mk_flg = '0' ".
			"   AND op.course_num = '".$_POST['course_num']."' ".
			" ORDER BY co.list_num, st.list_num, ls.list_num, ut.list_num, op.list_num";

	if ($result = $cdb->query($sql)) {
		$one_point_count = $cdb->num_rows($result);
	}

	// 件数チェック
	if ($one_point_count == 0) {
		$html .= "<br>現在、ポイント解説の登録はありません。<br><br>\n";
	} else {

		// イメージ・音声格納ディレクトリ作成
		create_directory_top();

		// 一覧画面表示
		$html .= "<table class=\"member_form\">\n";
		$html .= "<tr class=\"member_form_menu\">\n";

		// add start oda 2013/01/22
		if (!ereg("practice__view",$authority)
				&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__sort",$_SESSION['authority'])===FALSE))
		) {
			$html .= "<th>↑</th>\n";
			$html .= "<th>↓</th>\n";
		}
		// add end oda 2013/01/22

		$html .= "<th>コース</th>\n";
		$html .= "<th>ステージ</th>\n";
		$html .= "<th>レッスン</th>\n";
		$html .= "<th>ユニット</th>\n";
		$html .= "<th>ワンポイント<br>解説番号</th>\n";
		$html .= "<th>解説タイトル</th>\n";
		$html .= "<th>解説内容</th>\n";
		$html .= "<th>すらら復習ユニット</th>\n";
		$html .= "<th>表示・非表示</th>\n";
		$html .= "<th>確認</th>\n";
		if (!ereg("practice__view",$authority)
			&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__block_view",$_SESSION['authority'])===FALSE))
		) {
			$html .= "<th>詳細</th>\n";
			$html .= "<th>削除</th>\n";
		}
		$html .= "</tr>\n";

		$i = 1;
		if ($result = $cdb->query($sql)) {
			while ($list1 = $cdb->fetch_assoc($result)) {
				$list_data[] = $list1;
			}
			if (is_array($list_data)) {
				foreach ($list_data as $key => $list) {
					$html .= "<tr class=\"member_form_cell\">\n";

					// add start oda 2013/01/22
					$up_submit = $down_submit = "&nbsp;";
					if ($i != 1) {
						if ($list['course_num'] == $prev_course_num &&
							$list['stage_num']  == $prev_stage_num  &&
							$list['lesson_num'] == $prev_lesson_num &&
							$list['unit_num']   == $prev_unit_num   ) {
							$up_submit = "<input type=\"submit\" value=\"↑\">\n";
						}
					}
					if ($i < $one_point_count) {
						if ($list['course_num'] == $list_data[$key+1]['course_num'] &&
							$list['stage_num']  == $list_data[$key+1]['stage_num']  &&
							$list['lesson_num'] == $list_data[$key+1]['lesson_num'] &&
							$list['unit_num']   == $list_data[$key+1]['unit_num']   ) {
							$down_submit = "<input type=\"submit\" value=\"↓\">\n";
						}
					}
					if (!ereg("practice__view",$authority)
							&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__sort",$_SESSION['authority'])===FALSE))
					) {
						$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
						$html .= "<input type=\"hidden\" name=\"action\" value=\"one_point_up\">\n";
						$html .= "<input type=\"hidden\" name=\"one_point_num\" value=\"".$list['one_point_num']."\">\n";
						$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$list['course_num']."\">\n";
						$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$list['stage_num']."\">\n";
						$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$list['lesson_num']."\">\n";
						$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"".$list['unit_num']."\">\n";
						$html .= "<td>".$up_submit."</td>\n";
						$html .= "</form>\n";

						$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
						$html .= "<input type=\"hidden\" name=\"action\" value=\"one_point_down\">\n";
						$html .= "<input type=\"hidden\" name=\"one_point_num\" value=\"".$list['one_point_num']."\">\n";
						$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$list['course_num']."\">\n";
						$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$list['stage_num']."\">\n";
						$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$list['lesson_num']."\">\n";
						$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"".$list['unit_num']."\">\n";
						$html .= "<td>".$down_submit."</td>\n";
						$html .= "</form>\n";
					}
					// add end oda 2013/01/22

					// すららで復習の情報を取得
					$surala_unit = get_surala_unit($list['course_num'], $list['stage_num'], $list['lesson_num'], $list['unit_num'], 0, 0, $list['one_point_num']);
					$surala_unit = change_unit_num_to_key_all($surala_unit);

					// 文字列変換
					$one_point_commentary = rtrim($list['one_point_commentary']);
					$one_point_commentary = replace_decode($one_point_commentary);
					$one_point_commentary = tag_convert($one_point_commentary, 20);
					if (mb_strlen($one_point_commentary, "UTF-8") > 19) {
						$one_point_commentary .= "...";
					}

					$html .= "<td>".$list['course_name']."</td>\n";
					$html .= "<td>".$list['stage_name']."</td>\n";
					$html .= "<td>".$list['lesson_name']."</td>\n";
					$html .= "<td>".$list['unit_name']."</td>\n";
					$html .= "<td>".$list['one_point_num']."</td>\n";
					$html .= "<td>".replace_decode($list['study_title'])."</td>\n";
					$html .= "<td>".$one_point_commentary."</td>\n";
					$html .= "<td>".$surala_unit."</td>\n";
					$html .= "<td>".$L_DISPLAY[$list['display']]."</td>\n";
					$html .= "<td><input type=\"button\" value=\"確認\" onclick=\"check_one_point_win_open('".$list['unit_num']."', '".$list['one_point_num']."', '0')\"></td>\n";

					// 詳細ボタン
					if (!ereg("practice__view",$authority)
						&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__block_view",$_SESSION['authority'])===FALSE))
					) {
						$html .= "<td>";
						$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
						$html .= "<input type=\"hidden\" name=\"mode\" value=\"one_point_form\">\n";
						$html .= "<input type=\"hidden\" name=\"action\" value=\"one_point_updateform\">\n";
						$html .= "<input type=\"hidden\" name=\"one_point_num\" value=\"".$list['one_point_num']."\">\n";
						$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
						$html .= "<input type=\"submit\" value=\"詳細\">\n";
						$html .= "</form>\n";
						$html .= "</td>";
					}

					// 削除ボタン
					if (!ereg("practice__del",$authority)
						&& (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__block_del",$_SESSION['authority'])===FALSE))
					) {
						$html .= "<td>";
						$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
						$html .= "<input type=\"hidden\" name=\"mode\" value=\"one_point_delete\">\n";
						$html .= "<input type=\"hidden\" name=\"action\" value=\"\">\n";
						$html .= "<input type=\"hidden\" name=\"one_point_num\" value=\"".$list['one_point_num']."\">\n";
						$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
						$html .= "<input type=\"submit\" value=\"削除\">\n";
						$html .= "</form>\n";
						$html .= "</td>";
					}
					$html .= "</tr>\n";
					$i++;

					$prev_course_num = $list['course_num'];
					$prev_stage_num = $list['stage_num'];
					$prev_lesson_num = $list['lesson_num'];
					$prev_unit_num = $list['unit_num'];
				}
			}
		}
		$html .= "</table>\n";
		$html .= "<br>\n";
	}

	return $html;
}


/**
 * ワンポイント解説チェック処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $L_COURSE
 * @return string HTML
 */
function one_point_check_html($L_COURSE) {

	// グローバル定義
	global $L_DISPLAY;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	// POST値取得
	if ($_POST) {
		foreach ($_POST as $key => $val) {
			if ($key == "action") {
				if (ACTION == "ワンポイント解説追加確認") {
					$val = "one_point_add";
				} else {
					$val = "one_point_change";
				}
			}
			// 改行変換
			if ($key == "one_point_commentary") {
				$val = eregi_replace("\r","",$val);
				$val = eregi_replace("\n","<br>",$val);
			}
			$val = mb_convert_kana($val,"asKV","UTF-8");

			$HIDDEN .= "<input type=\"hidden\" name=\"$key\" value=\"$val\">\n";
		}
	}

	// データ表示
	if ($action) {
		foreach ($_POST as $key => $val) {
			$val = mb_convert_kana($val,"asKV","UTF-8");
			$$key = $val;
		}

		if ($unit_num) {
			$sql = "SELECT " .
					" ut.stage_num, " .
					" ut.lesson_num " .
					" FROM " . T_UNIT . " ut ".
					" WHERE ut.state = '0'" .
					"   AND ut.course_num = '".$course_num."'" .
					"   AND ut.unit_num = '".$unit_num."'" .
					"   LIMIT 1;";
			$result = $cdb->query($sql);
			$list = $cdb->fetch_assoc($result);

			$stage_num = $list['stage_num'];
			$lesson_num = $list['lesson_num'];
		}

	} else {
		$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"one_point_change\">\n";
		$sql = "SELECT op.* " .
				" FROM " . T_ONE_POINT . " op ".
				" WHERE op.mk_flg = '0'" .
				"   AND op.one_point_num = '".$_POST['one_point_num']."'" .
				"   LIMIT 1;";
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

		// すららで復習の情報取得
		$surala_unit = get_surala_unit($course_num, $stage_num, $lesson_num, $unit_num, 0, 0, $one_point_num);
		$surala_unit = change_unit_num_to_key_all($surala_unit);
	}

	// 改行変換
	$one_point_commentary = eregi_replace("\r","",$one_point_commentary);
	$one_point_commentary = eregi_replace("\n","<br>",$one_point_commentary);

	if (MODE != "one_point_delete") {
		$button = "登録";
	} else {
		$button = "削除";
	}
	$html .= "<br>確認画面：以下の内容で{$button}してもよろしければ{$button}ボタンをクリックしてください。<br>\n";

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(ONE_POINT_ALL_FORM);

	//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	if (!$one_point_num) { $one_point_num = "---"; }
	$INPUTS['ONEPOINTNUM']			= array('result'=>'plane','value'=>$one_point_num);
	$INPUTS['STUDYTITLE']			= array('result'=>'plane','value'=>$study_title);
	$INPUTS['ONEPOINTCOMMENTARY']	= array('result'=>'plane','value'=>$one_point_commentary);
	$INPUTS['UNITNUM']				= array('result'=>'plane','value'=>$unit_num);
	$INPUTS['SURALAUNIT']			= array('result'=>'plane','value'=>$surala_unit);
	$INPUTS['DISPLAY']				= array('result'=>'plane','value'=>$L_DISPLAY[$display]);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" style=\"float:left\">\n";
	$html .= $HIDDEN;
	$html .= "<input type=\"submit\" value=\"".$button."\">\n";
	$html .= "</form>";
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";

	if ($action) {
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
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"詳細\">\n";
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
	}
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}


/**
 * ワンポイント解説入力フォーム
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $ERROR
 * @param array $L_COURSE
 * @return string HTML
 */
function one_point_form($ERROR,$L_COURSE) {

	global $L_DISPLAY;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if ($_POST['one_point_num']) {
		$html .= "<br>ワンポイント解説変更フォーム<br>\n";
	} else {
		$html .= "<br>ワンポイント解説新規登録フォーム<br>\n";
	}

	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	if (ACTION != "one_point_updateform") {
		foreach ($_POST as $key => $val) {
			$$key = $val;
		}

		if ($unit_num) {
			$sql = "SELECT " .
					" ut.stage_num, " .
					" ut.lesson_num " .
					" FROM " . T_UNIT . " ut ".
					" WHERE ut.state = '0'" .
					"   AND ut.course_num = '".$course_num."'" .
					"   AND ut.unit_num = '".$unit_num."'" .
					"   LIMIT 1;";

			$result = $cdb->query($sql);
			$list = $cdb->fetch_assoc($result);

			$stage_num = $list['stage_num'];
			$lesson_num = $list['lesson_num'];
		}
	} else {
		if ($_POST['one_point_num']) {
			$sql = "SELECT op.* ".
					" FROM " . T_ONE_POINT . " op " .
					" WHERE op.one_point_num = '".$_POST['one_point_num']."'".
					"   AND op.mk_flg = '0'".
					" LIMIT 1;";
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

			// すららで復習の情報取得
			$surala_unit = get_surala_unit($course_num, $stage_num, $lesson_num, $unit_num, 0, 0, $one_point_num);
			$surala_unit = change_unit_num_to_key_all($surala_unit);
		}
	}

	//	html生成
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" name=\"one_point_form\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"one_point_addform\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$course_num."\">\n";
	if ($one_point_num) {
		$html .= "<input type=\"hidden\" name=\"one_point_num\" value=\"$one_point_num\">\n";
	}

	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(ONE_POINT_ALL_FORM);

	//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	if (!$one_point_num) {
		$new_num = "---";
	} else {
		$new_num = $one_point_num;
	}
	$INPUTS[ONEPOINTNUM]        = array('result'=>'plane','value'=>$new_num);
	$INPUTS[STUDYTITLE]         = array('type'=>'text','name'=>'study_title','size'=>'40','value'=>$study_title);
	$INPUTS[ONEPOINTCOMMENTARY] = array('type'=>'textarea','name'=>'one_point_commentary','cols'=>'37','rows'=>'10','value'=>$one_point_commentary);
	$INPUTS[UNITNUM]            = array('type'=>'text','name'=>'unit_num','size'=>'20','value'=>$unit_num);

	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("display");
	$newform->set_form_id("display");
	$newform->set_form_check($display);
	$newform->set_form_value('1');
	$indisplay = $newform->make();
	$newform = new form_parts();
	$newform->set_form_type("radio");
	$newform->set_form_name("display");
	$newform->set_form_id("undisplay");
	$newform->set_form_check($display);
	$newform->set_form_value('2');
	$undisplay = $newform->make();
	$display = $indisplay . "<label for=\"display\">".$L_DISPLAY[1]."</label> / " . $undisplay . "<label for=\"undisplay\">".$L_DISPLAY[2]."</label>";
	$INPUTS[DISPLAY] = array('result'=>'plane','value'=>$display);

	$INPUTS['SURALAUNIT']		= array('type'=>'text','name'=>'surala_unit','size'=>'40','value'=>$surala_unit);
	$surala_unit_att = "<br><span style=\"color:red;\">※すららで復習するコース番号＋ユニットキーを入力して下さい。";
	$surala_unit_att .= "<br>※複数設定する場合は、「 :: 」区切りで入力して下さい。</span>";
	$INPUTS['SURALAUNITATT']	= array('result'=>'plane','value'=>$surala_unit_att);

	$make_html->set_rep_cmd($INPUTS);
	$html .= $make_html->replace();

	if ($one_point_num) {
		$html .= "<input type=\"submit\" name=\"action\" value=\"ワンポイント解説変更確認\">\n";
	} else {
		$html .= "<input type=\"submit\" name=\"action\" value=\"ワンポイント解説追加確認\">\n";
	}

	$html .= "<input type=\"reset\" value=\"クリア\"><br><br>\n";
	$html .= "<input type=\"button\" value=\"確認\" onclick=\"check_one_point_win_open('".$unit_num."', '".$one_point_num."', '1')\">\n";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"詳細\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
	$html .= "<input type=\"submit\" value=\"一覧へ戻る\">\n";
	$html .= "</form>\n";
	return $html;
}


/**
 * ワンポイント解説登録処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function one_point_add() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	foreach ($_POST as $key => $val) {
		if ($key == "action") {
			continue;
		}
		$INSERT_DATA[$key] = "$val";
	}

	if ($INSERT_DATA['unit_num']) {
		$sql = "SELECT " .
				" ut.stage_num, " .
				" ut.lesson_num " .
				" FROM " . T_UNIT . " ut ".
				" WHERE ut.state = '0'" .
				"   AND ut.course_num = '".$INSERT_DATA['course_num']."'" .
				"   AND ut.unit_num = '".$INSERT_DATA['unit_num']."'" .
				"   LIMIT 1;";
		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);

		$INSERT_DATA['stage_num'] = $list['stage_num'];
		$INSERT_DATA['lesson_num'] = $list['lesson_num'];
	}

	$INSERT_DATA['ins_syr_id']		= "add";
	$INSERT_DATA['ins_date']		= "now()";
	$INSERT_DATA['ins_tts_id']		= $_SESSION['myid']['id'];
	$INSERT_DATA['upd_syr_id']		= "add";
	$INSERT_DATA['upd_date']		= "now()";
	$INSERT_DATA['upd_tts_id']		= $_SESSION['myid']['id'];

	// すららで復習ユニット登録
	$surala_unit = change_unit_key_to_num_all($INSERT_DATA['surala_unit']);
	unset($INSERT_DATA['surala_unit']);

	$ERROR = $cdb->insert(T_ONE_POINT,$INSERT_DATA);

	if (!$ERROR) {
		$one_point_num = $cdb->insert_id();
		$INSERT_DATA['list_num'] = $one_point_num;
		$where = " WHERE one_point_num = '".$one_point_num."' LIMIT 1;";

		$ERROR = $cdb->update(T_ONE_POINT,$INSERT_DATA,$where);

		$ERROR = update_surala_unit($INSERT_DATA['course_num'], $INSERT_DATA['stage_num'], $INSERT_DATA['lesson_num'], $INSERT_DATA['unit_num'], 0, 0, $one_point_num, $surala_unit);
	}

	// 管理フォルダ作成
	create_directory($INSERT_DATA['course_num'], $INSERT_DATA['stage_num'], $INSERT_DATA['lesson_num'], $INSERT_DATA['unit_num']);

	if (!$ERROR) {
		$_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>";
	}

	return $ERROR;
}


/**
 * ワンポイント解説更新処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function one_point_change() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	if (MODE == "one_point_form") {
		foreach ($_POST as $key => $val) {
			if ($key == "action") {
				continue;
			}
			$INSERT_DATA[$key] = "$val";
		}

		if ($INSERT_DATA['unit_num']) {
			$sql = "SELECT " .
					" ut.stage_num, " .
					" ut.lesson_num " .
					" FROM " . T_UNIT . " ut ".
					" WHERE ut.state = '0'" .
					"   AND ut.course_num = '".$INSERT_DATA['course_num']."'" .
					"   AND ut.unit_num = '".$INSERT_DATA['unit_num']."'" .
					"   LIMIT 1;";

			$result = $cdb->query($sql);
			$list = $cdb->fetch_assoc($result);

			$INSERT_DATA['stage_num'] = $list['stage_num'];
			$INSERT_DATA['lesson_num'] = $list['lesson_num'];
		}

		$surara_unit = change_unit_key_to_num_all($_POST['surala_unit']);

		// すららで復習ユニット登録
		$ERROR = update_surala_unit($INSERT_DATA['course_num'], $INSERT_DATA['stage_num'], $INSERT_DATA['lesson_num'], $INSERT_DATA['unit_num'], 0, 0, $_POST['one_point_num'], $surara_unit);
		unset($INSERT_DATA['surala_unit']);

		$INSERT_DATA['upd_syr_id']		= "update";
		$INSERT_DATA['upd_date']		= "now()";
		$INSERT_DATA['upd_tts_id']		= $_SESSION['myid']['id'];
		$where = " WHERE one_point_num = '".$_POST['one_point_num']."' LIMIT 1;";

		$ERROR = $cdb->update(T_ONE_POINT,$INSERT_DATA,$where);

	} elseif (MODE == "one_point_delete") {
		$INSERT_DATA['display']		= "2";
		$INSERT_DATA['mk_flg']		= "1";
		$INSERT_DATA['mk_tts_id']	= $_SESSION['myid']['id'];
		$INSERT_DATA['mk_date']		= "now()";
		$INSERT_DATA['upd_syr_id']   = "del";
		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
		$INSERT_DATA['upd_date']   = "now()";
		$where = " WHERE one_point_num='".$_POST['one_point_num']."' LIMIT 1;";

		$ERROR = $cdb->update(T_ONE_POINT,$INSERT_DATA,$where);

		$stage_num = "";
		$lesson_num = "";
		$unit_num = "";

		// update start oda 2013/01/31
		if ($_POST['one_point_num']) {
			$sql = "SELECT " .
					" op.stage_num, " .
					" op.lesson_num, " .
					" op.unit_num " .
					" FROM " . T_ONE_POINT . " op ".
					" WHERE op.one_point_num = '".$_POST['one_point_num']."'" .
					" LIMIT 1;";
//echo $sql."<br>";
			$result = $cdb->query($sql);
			$list = $cdb->fetch_assoc($result);

			$stage_num = $list['stage_num'];
			$lesson_num = $list['lesson_num'];
			$unit_num = $list['unit_num'];

			// すららで復習ユニット削除
			$ERROR = delete_surala_unit($_POST['course_num'], $stage_num, $lesson_num, $unit_num, 0, 0, $_POST['one_point_num']);
		}
		// update end oda 2013/01/31
	}

	if (!$ERROR) {
		$_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>";
	}
	return $ERROR;
}


/**
 * ワンポイント解説内容チェック
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function one_point_check() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (!$_POST['course_num']) {
		$ERROR[] = "コース情報が確認できません。";
	}

	if (!$_POST['study_title']) {
		$ERROR[] = "解説タイトルが未入力です。";
	}
	if (!$_POST['one_point_commentary']) {
		$ERROR[] = "解説内容が未入力です。";
	}
	if (!$_POST['unit_num']) {
		$ERROR[] = "ユニット管理番号が未入力です。";
	}

	if ($_POST['unit_num']) {

		$unit_count = 0;

		$sql  = "SELECT * FROM ".T_UNIT.
				" WHERE state='0' ".
				"   AND course_num = '" . $_POST['course_num'] ."'".
				"   AND unit_num = '" . $_POST['unit_num'] ."';";

		if ($result = $cdb->query($sql)) {
			$unit_count = $cdb->num_rows($result);
		}

		if ($unit_count <= 0) {
			$ERROR[] = "指定したユニットは、コースが異なる又は、既に削除されているか、存在しません。";
		}
	}

	if ($_POST['surala_unit']) {
		$surala_unit_count = 0;
		$surala_unit_list = explode("::",change_unit_key_to_num_all($_POST['surala_unit']));

		// ユニットIDに数値以外の値が入っていないかチェック
		$error_flag = false;
		for ($j=0; $j<count($surala_unit_list); $j++) {
			if (is_numeric($surala_unit_list[$j]) == false) {
				$ERROR[] = "すららで復習ユニットに指定したユニットは、不正な情報が混ざっています。";
				$error_flag = true;
			}
		}

		// ユニットIDが存在するかチェック
		if (!$error_flag) {
			$sql  = "SELECT * FROM ".T_UNIT.
					" WHERE state='0' AND unit_num IN ('" . implode("','", $surala_unit_list) ."')";
			if ($result = $cdb->query($sql)) {
				$surala_unit_count = $cdb->num_rows($result);
			}

			if (count($surala_unit_list) > 0) {
				if ($surala_unit_count != count($surala_unit_list)) {
					$ERROR[] = "すららで復習ユニットに指定したユニットは、既に削除されているか、不正な情報が混ざっています。";
				}
			}
		}

	}

	if (!$_POST['display']) { $ERROR[] = "表示・非表示が未選択です。"; }
	return $ERROR;
}


/**
 * ワンポイント解説アップロード処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function one_point_upload() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (!$_POST['course_num']) {
		$ERROR[] = "コースが確認できません。";
	}

	$file_name = $_FILES['one_point_file']['name'];
	$file_tmp_name = $_FILES['one_point_file']['tmp_name'];
	$file_error = $_FILES['one_point_file']['error'];

	if (!$file_tmp_name) {
		$ERROR[] = "ワンポイント解説ファイルが指定されておりません。";
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

	$LIST = file($file_tmp_name);
	if ($LIST) {
		$i = 0;
		foreach ($LIST AS $VAL) {
			if ($i == 0) { $i++;  continue; }
			unset($LINE);
			$VAL = trim($VAL);
			if (!$VAL || !ereg("\t",$VAL)) { continue; }
			$file_data = explode("\t",$VAL);
			// 項目が全て設定されている場合
			if (count($file_data) == 10) {
				$LINE['one_point_num'] = $file_data[0];
				$LINE['lesson_name'] = $file_data[3];
				$LINE['unit_name'] = $file_data[4];
				$LINE['study_title'] = $file_data[5];
				$LINE['one_point_commentary'] = $file_data[6];
				$LINE['surala_unit'] = $file_data[7];
				$LINE['display'] = $file_data[8];
				$LINE['mk_flg'] = $file_data[9];
			// 管理番号が未設定の場合は、格納配列が１つ前にずれる
			} elseif (count($file_data) == 9) {
				$LINE['one_point_num'] = "0";
				$LINE['lesson_name'] = $file_data[2];
				$LINE['unit_name'] = $file_data[3];
				$LINE['study_title'] = $file_data[4];
				$LINE['one_point_commentary'] = $file_data[5];
				$LINE['surala_unit'] = $file_data[6];
				$LINE['display'] = $file_data[7];
				$LINE['mk_flg'] = $file_data[8];
			} else {
				$ERROR[] = $i."行目　データが不正です。";
				continue;
			}

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

			// 名称から管理番号を取得する
			$course_num = "";
			$stage_num = "";
			$lesson_num = "";
			$unit_num = "";

			$sql = "SELECT ".
					"  ut.course_num,".
					"  ut.stage_num,".
					"  ut.lesson_num,".
					"  ut.unit_num".
					" FROM " . T_UNIT . " ut ".
					" INNER JOIN " . T_LESSON . " ls ON ut.lesson_num = ls.lesson_num ".
					" WHERE ut.course_num ='".$_POST['course_num']."'".
					"   AND ut.unit_name = '".$LINE['unit_name']."'".
					"   AND ut.state = '0'".
					"   AND ls.lesson_name = '".$LINE['lesson_name']."'".
					"   AND ls.state = '0'".
					";";
			if ($result = $cdb->query($sql)) {
				while ($list = $cdb->fetch_assoc($result)) {
					$course_num = $list['course_num'];
					$stage_num = $list['stage_num'];
					$lesson_num = $list['lesson_num'];
					$unit_num = $list['unit_num'];
				}
			}

			$LINE['course_num'] = $course_num;
			$LINE['stage_num']  = $stage_num;
			$LINE['lesson_num'] = $lesson_num;
			$LINE['unit_num']   = $unit_num;

			// 内容チェック
			$error_flg = false;
			if (!$LINE['lesson_name']) {
				$ERROR[] = $i."行目　ユニット名が未入力です。";
				$error_flg = true;
			} else {
				$lesson_name = "";
				$sql = "SELECT lesson_name FROM " . T_LESSON .
						" WHERE state = '0'".
						"   AND lesson_num = '".$LINE['lesson_num']."'".
						";";
				if ($result = $cdb->query($sql)) {
					while ($list = $cdb->fetch_assoc($result)) {
						$lesson_name = $list['lesson_name'];
					}
				}
				if ($lesson_name != $LINE['lesson_name']) {
					$ERROR[] = $i."行目　ユニット名（Lesson名)が異なります。";
					$error_flg = true;
				}
			}
			if (!$LINE['study_title']) {
				$ERROR[] = $i."行目　解説タイトルが未入力です。";
				$error_flg = true;
			}
			if (!$LINE['one_point_commentary']) {
				$ERROR[] = $i."行目　解説内容が未入力です。";
				$error_flg = true;
			}
			if (!$LINE['display']) {
				$ERROR[] = $i."行目　表示区分が未入力です。";
				$error_flg = true;
			}

			$surala_unit_list = array();
			if ($LINE['surala_unit']) {

				$surala_unit_count = 0;
				$surala_unit_list = explode("::",change_unit_key_to_num_all($LINE['surala_unit']));

				// ユニットIDに数値以外の値が入っていないかチェック
				$surala_error_flag = false;
				for ($j=0; $j<count($surala_unit_list); $j++) {
					if (is_numeric($surala_unit_list[$j]) == false) {
						$ERROR[] = $i."行目　すららで復習ユニットに指定したユニットは、不正な情報が混ざっています。";
						$surala_error_flag = true;
						$error_flg = true;
					}
				}

				// ユニットIDが存在するかチェック
				if (!$surala_error_flag) {
					$sql  = "SELECT * FROM ".T_UNIT.
							" WHERE state='0' AND unit_num IN ('" . implode("','", $surala_unit_list) ."')";
					if ($result = $cdb->query($sql)) {
						$surala_unit_count = $cdb->num_rows($result);
					}

					if (count($surala_unit_list) > 0) {
						if ($surala_unit_count != count($surala_unit_list)) {
							$ERROR[] = $i."行目　すららで復習ユニットに指定したユニットは、既に削除されているか、不正な情報が混ざっています。";
							$error_flg = true;
						}
					}
				}

				// 上位で設定されている場合は、エラー処理を行う
				$upper_check_s = get_surala_unit($course_num, $stage_num, 0, 0, 0, 0, 0);
				$upper_check_l = get_surala_unit($course_num, $stage_num, $lesson_num, 0, 0, 0, 0);
				$upper_check_u = get_surala_unit($course_num, $stage_num, $lesson_num, $unit_num, 0, 0, 0);

				if ($upper_check_s > 0) {
					$ERROR[] = $i."行目　すららで復習ユニットは上位階層（Stage）にて設定済です。";
					$error_flg = true;
				} elseif ($upper_check_l > 0) {
					$ERROR[] = $i."行目　すららで復習ユニットは上位階層（Lesson）にて設定済です。";
					$error_flg = true;
				} elseif ($upper_check_u > 0) {
					$ERROR[] = $i."行目　すららで復習ユニットは上位階層（Unit）にて設定済です。";
					$error_flg = true;
				}
			}

			if (!$LINE['mk_flg']) {
				$LINE['mk_flg'] = "0";
			}

			// エラーの時は強制的に非表示とする。
			if ($error_flg) {
				$LINE['display'] = "2";
			}

			// データ存在チェック
			$sql  = "SELECT * FROM " . T_ONE_POINT .
					" WHERE one_point_num = '".$LINE['one_point_num']."';";
			$one_point_count = 0;
			if ($result = $cdb->query($sql)) {
				$one_point_count = $cdb->num_rows($result);
			}

			// 登録処理
			if ($one_point_count == 0) {
				$INSERT_DATA = array();

				$INSERT_DATA['course_num']				= $course_num;
				$INSERT_DATA['stage_num']				= $stage_num;
				$INSERT_DATA['lesson_num']				= $lesson_num;
				$INSERT_DATA['unit_num']				= $unit_num;

				$INSERT_DATA['study_title']				= $LINE['study_title'];
				$INSERT_DATA['one_point_commentary']	= $LINE['one_point_commentary'];
				$INSERT_DATA['display']					= $LINE['display'];
				$INSERT_DATA['mk_flg']					= $LINE['mk_flg'];

				$INSERT_DATA['ins_syr_id']		= "add";
				$INSERT_DATA['ins_date']		= "now()";
				$INSERT_DATA['ins_tts_id']		= $_SESSION['myid']['id'];
				$INSERT_DATA['upd_syr_id']		= "add";
				$INSERT_DATA['upd_date']		= "now()";
				$INSERT_DATA['upd_tts_id']		= $_SESSION['myid']['id'];

				$ERROR_INSERT = $cdb->insert(T_ONE_POINT,$INSERT_DATA);

				if (!$ERROR_INSERT) {
					$one_point_num = $cdb->insert_id();
					$INSERT_DATA['list_num'] = $one_point_num;
					$where = " WHERE one_point_num = '".$one_point_num."' LIMIT 1;";

					$ERROR_INSERT = $cdb->update(T_ONE_POINT,$INSERT_DATA,$where);

					$ERROR_INSERT = update_surala_unit(
							$INSERT_DATA['course_num'], $INSERT_DATA['stage_num'],
							$INSERT_DATA['lesson_num'], $INSERT_DATA['unit_num'],
							0, 0, $one_point_num, implode("::",$surala_unit_list));
				}

			// 更新処理
			} else {

				$INSERT_DATA = array();

				$INSERT_DATA['course_num']				= $course_num;
				$INSERT_DATA['stage_num']				= $stage_num;
				$INSERT_DATA['lesson_num']				= $lesson_num;
				$INSERT_DATA['unit_num']				= $unit_num;

				$INSERT_DATA['study_title']				= $LINE['study_title'];
				$INSERT_DATA['one_point_commentary']	= $LINE['one_point_commentary'];
				$INSERT_DATA['display']					= $LINE['display'];
				$INSERT_DATA['mk_flg']					= $LINE['mk_flg'];

				if ($LINE['mk_flg'] == "0") {
					$INSERT_DATA['upd_syr_id']		= "update";
					$INSERT_DATA['upd_date']		= "now()";
					$INSERT_DATA['upd_tts_id']		= $_SESSION['myid']['id'];
				} else {
					$INSERT_DATA['display']			= "2";
					$INSERT_DATA['mk_flg']			= "1";
					$INSERT_DATA['mk_tts_id']		= $_SESSION['myid']['id'];
					$INSERT_DATA['mk_date']			= "now()";
					$INSERT_DATA['upd_syr_id']      = "del";
					$INSERT_DATA['upd_tts_id']      = $_SESSION['myid']['id'];
					$INSERT_DATA['upd_date']        = "now()";
				}
				$where = " WHERE one_point_num = '".$LINE['one_point_num']."' LIMIT 1;";

				$ERROR_INSERT = $cdb->update(T_ONE_POINT,$INSERT_DATA,$where);

				$ERROR_INSERT = update_surala_unit(
						$INSERT_DATA['course_num'], $INSERT_DATA['stage_num'],
						$INSERT_DATA['lesson_num'], $INSERT_DATA['unit_num'],
						0, 0, $LINE['one_point_num'], implode("::",$surala_unit_list));

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
 * @author Azet
 * @param string $word
 * @return string
 */
function replace_word($word) {
	$word = mb_convert_kana($word,"asKV","UTF-8");
	$word = ereg_replace("<>","&lt;&gt;",$word);
	$word = trim($word);
	$word = eregi_replace("\r","",$word);
	$word = eregi_replace("\n","<br>",$word);
	$word = replace_encode($word);

	return $word;
}


/**
 * 管理ディレクトリ作成(一覧画面用）
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function create_directory_top() {

	// ポイント解説イメージ保管ディレクトリ作成
	$material_img_path = MATERIAL_POINT_IMG_DIR;
	if (!file_exists($material_img_path)) {
		@mkdir($material_img_path,0777);
		@chmod($material_img_path,0777);
	}
	$material_img_path = MATERIAL_POINT_IMG_DIR. $_POST['course_num']."/";
	if (!file_exists($material_img_path)) {
		@mkdir($material_img_path,0777);
		@chmod($material_img_path,0777);
	}

	// ポイント解説音声保管ディレクトリ作成
	$material_voice_path = MATERIAL_POINT_VOICE_DIR;
	if (!file_exists($material_voice_path)) {
		@mkdir($material_voice_path,0777);
		@chmod($material_voice_path,0777);
	}
	$material_voice_path = MATERIAL_POINT_VOICE_DIR. $_POST['course_num']."/";
	if (!file_exists($material_voice_path)) {
		@mkdir($material_voice_path,0777);
		@chmod($material_voice_path,0777);
	}
}


/**
 * 管理ディレクトリ作成(詳細画面用）
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $course_num
 * @param integer $stage_num
 * @param integer $lesson_num
 * @param integer $unit_num
 */
function create_directory($course_num, $stage_num, $lesson_num, $unit_num) {

	// ポイント解説イメージ保管ディレクトリ作成
	$material_img_path = MATERIAL_POINT_IMG_DIR. $course_num."/".$stage_num."/";
	if (!file_exists($material_img_path)) {
		@mkdir($material_img_path,0777);
		@chmod($material_img_path,0777);
	}
	$material_img_path = MATERIAL_POINT_IMG_DIR. $course_num."/".$stage_num."/".$lesson_num."/";
	if (!file_exists($material_img_path)) {
		@mkdir($material_img_path,0777);
		@chmod($material_img_path,0777);
	}
	$material_img_path = MATERIAL_POINT_IMG_DIR. $course_num."/".$stage_num."/".$lesson_num."/".$unit_num."/";
	if (!file_exists($material_img_path)) {
		@mkdir($material_img_path,0777);
		@chmod($material_img_path,0777);
	}

	// ポイント解説音声保管ディレクトリ作成
	$material_voice_path = MATERIAL_POINT_VOICE_DIR. $course_num."/".$stage_num."/";
	if (!file_exists($material_voice_path)) {
		@mkdir($material_voice_path,0777);
		@chmod($material_voice_path,0777);
	}
	$material_voice_path = MATERIAL_POINT_VOICE_DIR. $course_num."/".$stage_num."/".$lesson_num."/";
	if (!file_exists($material_voice_path)) {
		@mkdir($material_voice_path,0777);
		@chmod($material_voice_path,0777);
	}
	$material_voice_path = MATERIAL_POINT_VOICE_DIR. $course_num."/".$stage_num."/".$lesson_num."/".$unit_num."/";
	if (!file_exists($material_voice_path)) {
		@mkdir($material_voice_path,0777);
		@chmod($material_voice_path,0777);
	}
}

// add start oda 2013/01/22
/**
 * ONE_POINTを上がる機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function one_point_up() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql  = "SELECT * FROM " . T_ONE_POINT .
	" WHERE one_point_num = '".$_POST['one_point_num']."' AND mk_flg = '0' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$m_unit_num			= $list['unit_num'];
		$m_one_point_num	= $list['one_point_num'];
		$m_list_num			= $list['list_num'];
	}
	if (!$m_unit_num || !$m_list_num) {
		$ERROR[] = "移動するワンポイント解説情報が取得できません。";
	}

	if (!$ERROR) {
		$sql  = "SELECT * FROM " . T_ONE_POINT .
		" WHERE unit_num = '".$m_unit_num."' AND mk_flg = '0' AND list_num < '".$m_list_num."'" .
		" ORDER BY list_num DESC LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$c_one_point_num	= $list['one_point_num'];
			$c_list_num			= $list['list_num'];
		}
	}
	if (!$c_one_point_num || !$c_list_num) {
		$ERROR[] = "移動されるワンポイント解説情報が取得できません。";
	}

	if (!$ERROR) {
		$INSERT_DATA['list_num']		= $c_list_num;
		$INSERT_DATA['upd_syr_id']		= "up";
		$INSERT_DATA['upd_date']		= "now()";
		$INSERT_DATA['upd_tts_id']		= $_SESSION['myid']['id'];
		$where = " WHERE one_point_num = '".$m_one_point_num."' LIMIT 1;";

		$ERROR = $cdb->update(T_ONE_POINT,$INSERT_DATA,$where);
	}

	if (!$ERROR) {
		$INSERT_DATA['list_num']		= $m_list_num;
		$INSERT_DATA['upd_syr_id']		= "up";
		$INSERT_DATA['upd_date']		= "now()";
		$INSERT_DATA['upd_tts_id']		= $_SESSION['myid']['id'];
		$where = " WHERE one_point_num = '".$c_one_point_num."' LIMIT 1;";

		$ERROR = $cdb->update(T_ONE_POINT,$INSERT_DATA,$where);
	}

	return $ERROR;
}

/**
 * ONE_POINTを下がる機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function one_point_down() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql  = "SELECT * FROM " . T_ONE_POINT .
	" WHERE one_point_num = '".$_POST['one_point_num']."' AND mk_flg = '0' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$m_unit_num			= $list['unit_num'];
		$m_one_point_num	= $list['one_point_num'];
		$m_list_num			= $list['list_num'];
	}
	if (!$m_one_point_num || !$m_list_num) {
		$ERROR[] = "移動するワンポイント解説情報が取得できません。";
	}

	if (!$ERROR) {
		$sql  = "SELECT * FROM " . T_ONE_POINT .
		" WHERE unit_num = '".$m_unit_num."' AND mk_flg = '0' AND list_num > '".$m_list_num."'" .
		" ORDER BY list_num LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$c_one_point_num	= $list['one_point_num'];
			$c_list_num			= $list['list_num'];
		}
	}
	if (!$c_one_point_num || !$c_list_num) {
		$ERROR[] = "移動されるワンポイント解説情報が取得できません。";
	}
	if (!$ERROR) {
		$INSERT_DATA['list_num']		= $c_list_num;
		$INSERT_DATA['upd_syr_id']		= "down";
		$INSERT_DATA['upd_date']		= "now()";
		$INSERT_DATA['upd_tts_id']		= $_SESSION['myid']['id'];
		$where = " WHERE one_point_num = '".$m_one_point_num."' LIMIT 1;";

		$ERROR = $cdb->update(T_ONE_POINT,$INSERT_DATA,$where);
	}

	if (!$ERROR) {
		$INSERT_DATA['list_num']		= $m_list_num;
		$INSERT_DATA['upd_syr_id']		= "down";
		$INSERT_DATA['upd_date']		= "now()";
		$INSERT_DATA['upd_tts_id']		= $_SESSION['myid']['id'];
		$where = " WHERE one_point_num = '".$c_one_point_num."' LIMIT 1;";

		$ERROR = $cdb->update(T_ONE_POINT,$INSERT_DATA,$where);
	}

	return $ERROR;
}
// add end oda 2013/01/22
?>