<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * ランキングポイント設定
 * ファイル名：practice_ranking_point.php
 *
 * 履歴
 *   2012/07/04 初期設定
 *   2012/08/23 修正
 *   2012/09/25  修正:ＤＢの保持方法が異なるので、登録方法を見直し。
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

	// サブセション取得
	$ERROR = sub_session();

	// 処理アクションにより、フローを制御
	// チェック処理
	if (ACTION == "check") {
		$ERROR = check();
	}

	// 登録・修正・削除
	if (!$ERROR) {
		if (ACTION == "add") {
			$ERROR = add();
		} elseif (ACTION == "change") {
			$ERROR = change();
		} elseif (ACTION == "del") {
			$ERROR = change();
		}
	}

	// 登録処理
	if (MODE == "add") {
		if (ACTION == "check") {
			if (!$ERROR) {
				$html .= check_html();
			} else {
				$html .= addform($ERROR);
			}
		} elseif (ACTION == "add") {
			if (!$ERROR) {
				//$html .= ranking_point_list($ERROR); 	// del koyama 2013/10/01
				$html .= select_course($ERROR);			// add koyama 2013/10/01 コース選択追加
			} else {
				$html .= addform($ERROR);
			}
		} else {
			$html .= addform($ERROR);
		}

		// 詳細画面遷移
	} elseif (MODE == "詳細") {
		if (ACTION == "check") {
			if (!$ERROR) {
				$html .= check_html();
			} else {
				$html .= viewform($ERROR);
			}
		} elseif (ACTION == "change") {
			if (!$ERROR) {
				//$html .= ranking_point_list($ERROR); 	// del koyama 2013/10/01
				$html .= select_course($ERROR);			// add koyama 2013/10/01 コース選択追加
			} else {
				$html .= viewform($ERROR);
			}
		} else {
			$html .= viewform($ERROR);
		}

		// 削除処理
	} elseif (MODE == "削除") {
		if (ACTION == "check") {
			if (!$ERROR) {
				$html .= check_html();
			} else {
				$html .= viewform($ERROR);
			}
		} elseif (ACTION == "change") {
			if (!$ERROR) {
				//$html .= ranking_point_list($ERROR); 	// del koyama 2013/10/01
				$html .= select_course($ERROR);			// add koyama 2013/10/01 コース選択追加
			} else {
				$html .= viewform($ERROR);
			}
		} else {
			$html .= check_html();
		}
	// デフォルト更新
	} elseif (MODE == "default_update") {
		$ERROR = default_update();
		$html .= "<br>デフォルト値を更新しました。<br>";
		//$html .= ranking_point_list($ERROR); 	// del koyama 2013/10/01
		$html .= select_course($ERROR);			// add koyama 2013/10/01 コース選択追加
	// 一覧表示
	} else {
		//$html .= ranking_point_list($ERROR); 	// del koyama 2013/10/01
		$html .= select_course($ERROR);			// add koyama 2013/10/01 コース選択追加
	}

	return $html;
}


/**
 * コース選択
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @date 2013/10/01
 * @author Azet
 * @return string HTML
 */
function select_course() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql  = "SELECT * FROM ".T_COURSE.
			" WHERE state='0' ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
		if ($max) {
			$html = "<br>\n";
			$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"set_course\">\n";
			$html .= "<table class=\"stage_form\">\n";
			$html .= "<tr>\n";
			$html .= "<td class=\"stage_form_menu\">編集コース</td>\n";
			$html .= "</tr>";
			$html .= "<tr>\n";
			$html .= "<td class=\"stage_form_cell\"><select name=\"course_num\" onchange=\"submit();\">\n";

			if (!$_POST['course_num']) { $selected = "selected"; } else { $selected = ""; }
			$html .= "<option value=\"\" $selected>選択して下さい</option>\n";

			while ($list = $cdb->fetch_assoc($result)) {
				$course_num_ = $list['course_num'];
				$course_name_ = $list['course_name'];
				$L_COURSE[$course_num_] = $course_name_;
				if ($_POST['course_num'] == $course_num_) { $selected = "selected"; } else { $selected = ""; }
				$html .= "<option value=\"{$course_num_}\" $selected>{$course_name_}</option>\n";
			}

			$html .= "</select></td>\n";
			$html .= "</tr>\n";
			$html .= "</table>\n";
			$html .= "</form>\n";
		}
		else {
			$html = "<br>\n";
			$html .= "コースを設定してからご利用下さい。<br>\n";
		}
	}

	if ($_POST['course_num'] > 0) {
		//$html .= menu_status_list();
		$html .= ranking_point_list($ERROR, $L_COURSE);// 一覧表示
	} else {
		$html .= "<br>\n";
		$html .= "ランキングポイントを設定するコースを選択してください。<br>\n";
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
 * @param array $L_COURSE
 * @return string HTML
 */
function ranking_point_list($ERROR, $L_COURSE) {
//echo "■POST　<br>\n";pre($_POST); echo"\n\n";
//echo "■SESSION　<br>\n";pre($_SESSION); echo"\n\n";
	// グローバル変数
	global $L_PAGE_VIEW, $L_DATA_TYPE;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// セション情報取得
	//list($me_num,$onetime,$manager_level,$belong_num,$authority) = explode("<>",$_SESSION[myid]);

	// 権限取得
	if ($authority) {
		$L_AUTHORITY = explode("::",$authority);
	}

	// エラーメッセージが存在する場合、メッセージ表示
	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}
	//---------------------------------------------------------------------------------------------------
	// デフォルト情報取得
	//---------------------------------------------------------------------------------------------------
	//$DEFAULT_DATA = get_default_ranking_point();						// del koyama 2013/10/01
	$DEFAULT_DATA = get_default_ranking_point($_POST['course_num']);	// add koyama 2013/10/01 コースを加味
//echo "■DEFAULT_DATA　<br>\n";pre($DEFAULT_DATA); echo"\n\n";
	// デフォルトの設定値更新
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"default_update\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";	// add koyama 2013/10/01
	$html .= "デフォルト値設定<br>\n";
	$html .= "<table class=\"ranking_point_form\">\n";
	$html .= "<tr class=\"ranking_point_form_menu\">\n";
	$html .= "<th>データ種別</th>\n";
	$html .= "<th>金メダル率<br>（上位％）</th>\n";
	$html .= "<th>銀メダル率<br>（上位％）</th>\n";
	$html .= "<th>銅メダル率<br>（上位％）</th>\n";
	$html .= "<th>鉛メダル率<br>（上位％）</th>\n";	// add koyama 2013/10/01 鉛メダル追加
	$html .= "<th>ダイヤ判断<br>（金メダル回数）</th>\n";
	$html .= "<th>学習時間<br>上限（秒）</th>\n";
	$html .= "<th>学習時間<br>下限（秒）</th>\n";
	$html .= "</tr>\n";
	$html .= "<tr class=\"ranking_point_form_cell\">\n";
	$html .= "<td>正解率</td>\n";
	$html .= "<td><input type=\"text\" name=\"gold_percent_1\" value=\"".$DEFAULT_DATA['1']['gold_percent']."\" size=\"10\" /></td>\n";
	$html .= "<td><input type=\"text\" name=\"silver_percent_1\" value=\"".$DEFAULT_DATA['1']['silver_percent']."\" size=\"10\" /></td>\n";
	$html .= "<td><input type=\"text\" name=\"copper_percent_1\" value=\"".$DEFAULT_DATA['1']['copper_percent']."\" size=\"10\" /></td>\n";
	$html .= "<td><input type=\"text\" name=\"lead_percent_1\" value=\"".$DEFAULT_DATA['1']['lead_percent']."\" size=\"10\" /></td>\n";	// add koyama 2013/10/01 鉛メダル追加
	$html .= "<td><input type=\"text\" name=\"gold_count_1\" value=\"".$DEFAULT_DATA['1']['gold_count']."\" size=\"10\" /></td>\n";
	$html .= "<td>&nbsp;</td>\n";
	$html .= "<td>&nbsp;</td>\n";
	$html .= "</tr>\n";
	$html .= "<tr class=\"ranking_point_form_cell\">\n";
	$html .= "<td>正解率＋学習時間</td>\n";
	$html .= "<td><input type=\"text\" name=\"gold_percent_2\" value=\"".$DEFAULT_DATA['2']['gold_percent']."\" size=\"10\" /></td>\n";
	$html .= "<td><input type=\"text\" name=\"silver_percent_2\" value=\"".$DEFAULT_DATA['2']['silver_percent']."\" size=\"10\" /></td>\n";
	$html .= "<td><input type=\"text\" name=\"copper_percent_2\" value=\"".$DEFAULT_DATA['2']['copper_percent']."\" size=\"10\" /></td>\n";
	$html .= "<td><input type=\"text\" name=\"lead_percent_2\" value=\"".$DEFAULT_DATA['2']['lead_percent']."\" size=\"10\" /></td>\n";	// add koyama 2013/10/01 鉛メダル追加
	$html .= "<td><input type=\"text\" name=\"gold_count_2\" value=\"".$DEFAULT_DATA['2']['gold_count']."\" size=\"10\" /></td>\n";
	$html .= "<td><input type=\"text\" name=\"study_time_from_2\" value=\"".$DEFAULT_DATA['2']['study_time_from']."\" size=\"10\" /></td>\n";
	$html .= "<td><input type=\"text\" name=\"study_time_to_2\" value=\"".$DEFAULT_DATA['2']['study_time_to']."\" size=\"10\" /></td>\n";
	$html .= "</tr>\n";
	$html .= "</table>\n";
	$html .= "<br><input type=\"submit\" value=\"デフォルト更新\">\n";
	$html .= "</form>\n";
	$html .= "<hr>\n";



	//---------------------------------------------------------------------------------------------------
	// 登録権限が有るＩＤの時は、新規作成ボタン表示
	//---------------------------------------------------------------------------------------------------
	if (!$_SESSION['authority'] || ($_SESSION['authority'] && array_search(MAIN."__".SUB."__add",$_SESSION['authority'])===FALSE)) {
		$html .= "<br>\n";
		$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"add\">\n";
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";	// add koyama 2013/10/01
		$html .= "<input type=\"submit\" value=\"ランキングポイント新規登録\">\n";
		$html .= "</form>\n";
	}
	// 表示ページ制御
	$s_page_view_html .= "&nbsp;&nbsp;&nbsp;表示数<select name=\"s_page_view\">\n";
	foreach ($L_PAGE_VIEW as $key => $val){
		if ($_SESSION['sub_session']['s_page_view'] == $key) {
			$sel = " selected";
		} else { $sel = "";
		}
		$s_page_view_html .= "<option value=\"".$key."\"".$sel.">".$val."</option>\n";
	}
	$s_page_view_html .= "</select><input type=\"submit\" value=\"Set\">\n";

	// ランキングポイント取得SQL作成
	/* del koyama 2013/10/01
	$sql  = "SELECT * FROM " . T_MS_RANKING_POINT .
			" WHERE mk_flg ='0' AND course_num > 0 ";
	*/
	//--------------------------------------------------------add start koyama 2013/10/01 コース + デフォルトフラグを加味
	$sql  = "SELECT "
		   ."* "
		   ."FROM " . T_MS_RANKING_POINT ." mrp "
		   ." WHERE mrp.mk_flg = '0'"
		   ."   AND mrp.course_num = ".$_POST['course_num'].""
			 //update start kimura 2017/12/19 AWS移設 ソートなし → ORDER BY句追加
		   //."   AND mrp.default_flag  = '1'";
		   ."   AND mrp.default_flag  = '1'"
			 ."   ORDER BY mrp.mt_ranking_point_num "; //管理番号でソート(プライマリ)
			//update end   kimura 2017/12/19

	//--------------------------------------------------------ad  end  koyama 2013/10/01

	// ランキングポイント件数取得
	$ranking_point_count = 0;
	if ($result = $cdb->query($sql)) {
		$ranking_point_count = $cdb->num_rows($result);
	}

	// ページビュー判断
	if ($L_PAGE_VIEW[$_SESSION['sub_session']['s_page_view']]) {
		$page_view = $L_PAGE_VIEW[$_SESSION['sub_session']['s_page_view']];
	} else {
		$page_view = $L_PAGE_VIEW[0];
	}

	// 最大ページ数算出
	$max_page = ceil($ranking_point_count/$page_view);

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
		$ranking_point_check_count = 0;
		if ($result = $cdb->query($sql)) {
			$ranking_point_check_count = $cdb->num_rows($result);
		}
		if (!$ranking_point_check_count) {
			$html .= "現在、ランキングポイント情報は存在しません。";
			return $html;
		}

		// 一覧表示開始
		$html .= "<br>\n";
		$html .= "修正する場合は、修正するランキングポイントの詳細ボタンを押してください。<br>\n";

		// 登録件数表示
		$html .= "<br>\n";
		$html .= "<div style=\"float:left;\">ランキングポイント総数(".$ranking_point_count."):PAGE[".$page."/".$max_page."]</div>\n";

		// 前ページボタン表示制御
		if ($back > 0) {
			$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" style=\"float:left;\">";
			$html .= "<input type=\"submit\" value=\"前のページ\">";
			$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
			$html .= "<input type=\"hidden\" name=\"s_page\" value=\"".$back."\">\n";
			$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";	// add koyama 2013/10/01
			$html .= "</form>";
		}

		// 次ページボタン表示制御
		if ($page < $max_page) {
			$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" style=\"float:left;\">";
			$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
			$html .= "<input type=\"hidden\" name=\"s_page\" value=\"".$next."\">\n";
			$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";	// add koyama 2013/10/01
			$html .= "<input type=\"submit\" value=\"次のページ\">";
			$html .= "</form>";
		}

		$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" style=\"float:left;\">";
		$html .= "<input type=\"hidden\" name=\"action\" value=\"sub_session\">\n";
		$html .= "<input type=\"hidden\" name=\"s_page\" value=\"1\">\n";
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";	// add koyama 2013/10/01
		$html .= $s_page_view_html;
		$html .= "</form><br><br>\n";
		$html .= "<table class=\"ranking_point_form\">\n";
		$html .= "<tr class=\"ranking_point_form_menu\">\n";

		// リストタイトル表示
		$html .= "<th>登録番号</th>\n";
		$html .= "<th>パターン名</th>\n";
		$html .= "<th>データ種別</th>\n";
		$html .= "<th>コース</th>\n";
		$html .= "<th>金メダル率<br>（上位％）</th>\n";
		$html .= "<th>銀メダル率<br>（上位％）</th>\n";
		$html .= "<th>銅メダル率<br>（上位％）</th>\n";
		$html .= "<th>鉛メダル率<br>（上位％）</th>\n";// add koyama 2013/10/01 鉛メダル追加
		$html .= "<th>ダイヤ判断<br>（金メダル回数）</th>\n";
		$html .= "<th>学習時間<br>上限（秒）</th>\n";
		$html .= "<th>学習時間<br>下限（秒）</th>\n";
		$html .= "<th>コメント</th>\n";

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

			$html .= "<tr class=\"ranking_point_form_cell\">\n";
			$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
			$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";	// add koyama 2013/10/01
			$html .= "<input type=\"hidden\" name=\"mt_ranking_point_num\" value=\"".$list['mt_ranking_point_num']."\">\n";

			// 明細表示
			$html .= "<td>".$list['mt_ranking_point_num']."</td>\n";
			$html .= "<td>".$list['pattern_name']."</td>\n";
			$html .= "<td>".$L_DATA_TYPE[$list['data_type']]."</td>\n";
			$html .= "<td>".get_course_name($list['course_num'])."</td>\n";
			$html .= "<td>".$list['gold_percent']."</td>\n";
			$html .= "<td>".$list['silver_percent']."</td>\n";
			$html .= "<td>".$list['copper_percent']."</td>\n";
			$html .= "<td>".$list['lead_percent']."</td>\n";	// add koyama 2013/10/01 鉛メダル追加
			$html .= "<td>".$list['gold_count']."</td>\n";
			$html .= "<td>".$list['study_time_from']."</td>\n";
			$html .= "<td>".$list['study_time_to']."</td>\n";
			$html .= "<td>".$list['comment']."</td>\n";

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
//echo "■POST　<br>\n";pre($_POST); echo"\n\n";
//echo "■SESSION　<br>\n";pre($_SESSION); echo"\n\n";
	global $L_DATA_TYPE;

	$html .= "<br>\n";
	$html .= "新規登録フォーム<br>\n";

	// エラーメッセージ表示
	if ($ERROR) {
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	// 入力画面表示
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" id=\"ranking\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"add\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";// add koyama 2013/10/01

	// htmlクラス生成
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(RANKING_POINT_FORM);

	//$course_html = get_course_select($_POST['course_num']);	// del koyama 2013/10/01 コース選択を追加したので不要になりました
	$course_name = get_course_name($_POST['course_num']);		// add koyama 2013/10/01 コース名取得
	$stage_html  = get_stage_select($_POST['course_num'], $_POST['stage_num']);
	$lesson_html = get_lesson_select($_POST['course_num'], $_POST['stage_num'], $_POST['lesson_num']);
	$unit_html   = get_unit_select($_POST['course_num'], $_POST['stage_num'], $_POST['lesson_num'], $_POST['unit_num']);
	$block_html  = get_block_select($_POST['course_num'], $_POST['stage_num'], $_POST['lesson_num'], $_POST['unit_num'], $_POST['block_num']);

	$INPUTS['MTRANKINGPOINTNUM'] = array('result'=>'plane','value'=>"---");

	//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	$INPUTS['MTRANKINGPOINTNUM'] = array('result'=>'plane','value'=>"---");
	$INPUTS['PATTERNNAME'] = array('type'=>'text','name'=>'pattern_name','size'=>'20','value'=>$_POST['pattern_name']);
	$INPUTS['DATATYPE'] = array('type'=>'select','name'=>'data_type','array'=>$L_DATA_TYPE,'check'=>$_POST['data_type']);
	//$INPUTS['COURSENUM'] = array('result'=>'plane','value'=>$course_html);	// del koyama 2013/10/01 コース選択を追加したので不要になりました
	$INPUTS['COURSENUM'] = array('result'=>'plane','value'=>$course_name);		// add koyama 2013/10/01 コース名表示
	$INPUTS['STAGENUM'] = array('result'=>'plane','value'=>$stage_html);
	$INPUTS['LESSONNUM'] = array('result'=>'plane','value'=>$lesson_html);
	$INPUTS['UNITNUM'] = array('result'=>'plane','value'=>$unit_html);
	$INPUTS['BLOCKNUM'] = array('result'=>'plane','value'=>$block_html);
	$INPUTS['GOLDPERCENT'] = array('type'=>'text','name'=>'gold_percent','size'=>'10','value'=>$_POST['gold_percent']);
	$INPUTS['SILVERPERCENT'] = array('type'=>'text','name'=>'silver_percent','size'=>'10','value'=>$_POST['silver_percent']);
	$INPUTS['COPPERPERCENT'] = array('type'=>'text','name'=>'copper_percent','size'=>'10','value'=>$_POST['copper_percent']);
	$INPUTS['LEADPERCENT'] = array('type'=>'text','name'=>'lead_percent','size'=>'10','value'=>$_POST['lead_percent']);			// add koyama 2013/10/01 鉛メダル追加
	$INPUTS['GOLDCOUNT'] = array('type'=>'text','name'=>'gold_count','size'=>'10','value'=>$_POST['gold_count']);
	$INPUTS['STUDYTIMEFROM'] = array('type'=>'text','name'=>'study_time_from','size'=>'10','value'=>$_POST['study_time_from']);
	$INPUTS['STUDYTIMETO'] = array('type'=>'text','name'=>'study_time_to','size'=>'10','value'=>$_POST['study_time_to']);
	$INPUTS['COMMENT'] = array('type'=>'text','name'=>'comment','size'=>'40','value'=>$_POST['comment']);

	$make_html->set_rep_cmd($INPUTS);
	$html .= $make_html->replace();

	// 制御ボタン定義
	$html .= "<input type=\"submit\" value=\"追加確認\">\n";
	$html .= "<input type=\"reset\" value=\"クリア\">\n";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"ranking_point_list\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";// add koyama 2013/10/01
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

	global $L_DATA_TYPE;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$action = ACTION;

	if ($action) {
		foreach ($_POST as $key => $val) {
			$$key = $val;
		}
	} else {
		$sql = "SELECT * FROM " . T_MS_RANKING_POINT .
				" WHERE mt_ranking_point_num='".$_POST['mt_ranking_point_num']."' AND mk_flg='0' LIMIT 1;";
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
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" id=\"ranking\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"".$mode."\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"check\">\n";
	$html .= "<input type=\"hidden\" name=\"mt_ranking_point_num\" value=\"".$mt_ranking_point_num."\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";// add koyama 2013/10/01

	// htmlクラス生成
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(RANKING_POINT_FORM);

	//$course_html = get_course_select($course_num);			// del koyama 2013/10/01 コース選択を追加したので不要になりました
	$course_name = get_course_name($_POST['course_num']);		// add koyama 2013/10/01 コース名取得
	$stage_html  = get_stage_select($course_num, $stage_num);
	$lesson_html = get_lesson_select($course_num, $stage_num, $lesson_num);
	$unit_html   = get_unit_select($course_num, $stage_num, $lesson_num, $unit_num);
	$block_html  = get_block_select($course_num, $stage_num, $lesson_num, $unit_num, $block_num);

	//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	if (!$mt_ranking_point_num) {
		$mt_ranking_point_num = "---";
	}
	$INPUTS['MTRANKINGPOINTNUM'] = array('result'=>'plane','value'=>$mt_ranking_point_num);
	$INPUTS['PATTERNNAME'] = array('type'=>'text','name'=>'pattern_name','size'=>'20','value'=>$pattern_name);
	$INPUTS['DATATYPE'] = array('type'=>'select','name'=>'data_type','array'=>$L_DATA_TYPE,'check'=>$data_type);
	//$INPUTS['COURSENUM'] = array('result'=>'plane','value'=>$course_html);	// del koyama 2013/10/01 コース選択を追加したので不要になりました
	$INPUTS['COURSENUM'] = array('result'=>'plane','value'=>$course_name);		// add koyama 2013/10/01 コース名表示
	$INPUTS['STAGENUM'] = array('result'=>'plane','value'=>$stage_html);
	$INPUTS['LESSONNUM'] = array('result'=>'plane','value'=>$lesson_html);
	$INPUTS['UNITNUM'] = array('result'=>'plane','value'=>$unit_html);
	$INPUTS['BLOCKNUM'] = array('result'=>'plane','value'=>$block_html);
	$INPUTS['GOLDPERCENT'] = array('type'=>'text','name'=>'gold_percent','size'=>'10','value'=>$gold_percent);
	$INPUTS['SILVERPERCENT'] = array('type'=>'text','name'=>'silver_percent','size'=>'10','value'=>$silver_percent);
	$INPUTS['COPPERPERCENT'] = array('type'=>'text','name'=>'copper_percent','size'=>'10','value'=>$copper_percent);
	$INPUTS['LEADPERCENT'] = array('type'=>'text','name'=>'lead_percent','size'=>'10','value'=>$lead_percent);			// add koyama 2013/10/01 鉛メダル追加
	$INPUTS['GOLDCOUNT'] = array('type'=>'text','name'=>'gold_count','size'=>'10','value'=>$gold_count);
	$INPUTS['STUDYTIMEFROM'] = array('type'=>'text','name'=>'study_time_from','size'=>'10','value'=>$study_time_from);
	$INPUTS['STUDYTIMETO'] = array('type'=>'text','name'=>'study_time_to','size'=>'10','value'=>$study_time_to);
	$INPUTS['COMMENT'] = array('type'=>'text','name'=>'comment','size'=>'40','value'=>$comment);

	$make_html->set_rep_cmd($INPUTS);
	$html .= $make_html->replace();

	// 制御ボタン定義
	$html .= "<input type=\"submit\" value=\"変更確認\">";
	$html .= "<input type=\"reset\" value=\"クリア\">";
	$html .= "</form>\n";
	$html .= "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"ranking_point_list\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";// add koyama 2013/10/01
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
	if (!$_POST['pattern_name']) {
		$ERROR[] = "パターン名が未入力です。";

		// 重複チェック
	} else {
		if (MODE == "add") {
			$sql  = "SELECT * FROM " .T_MS_RANKING_POINT.
					" WHERE mk_flg = '0' AND pattern_name = '".$_POST['pattern_name']."'";
		} else {
			$sql  = "SELECT * FROM " .T_MS_RANKING_POINT.
					" WHERE mk_flg = '0' AND mt_ranking_point_num != '".$_POST['mt_ranking_point_num']."' AND pattern_name = '".$_POST['pattern_name']."'";
		}
		// SQL実行
		if ($result = $cdb->query($sql)) {
			$count = $cdb->num_rows($result);
		}
		if ($count > 0) {
			$ERROR[] = "入力したパターン名は既に登録されております。";
		}
	}

	// 未入力チェック
	if (!$_POST['data_type']) {
		$ERROR[] = "データ種別が未選択です。";
	}
	if (!$_POST['course_num']) {
		$ERROR[] = "コースが未選択です。";
	}
	if (!$_POST['gold_percent']) {
		$ERROR[] = "金メダル率（上位％）が未入力です。";
	}
	if (!$_POST['silver_percent']) {
		$ERROR[] = "銀メダル率（上位％）が未入力です。";
	}
	if (!$_POST['copper_percent']) {
		$ERROR[] = "銅メダル率（上位％）が未入力です。";
	}
	if (!$_POST['lead_percent']) {									// add koyama 2013/10/01 鉛メダル追加
		$ERROR[] = "鉛メダル率（上位％）が未入力です。";
	}
	if ($_POST['data_type'] == 1 && $_POST['study_time_from']) {
		$ERROR[] = "学習時間上限（秒）は指定できません。";
	}
	if ($_POST['data_type'] == 1 && $_POST['study_time_to']) {
		$ERROR[] = "学習時間下限（秒）は指定できません。";
	}
	if ($_POST['data_type'] == 2 && !$_POST['study_time_from']) {
		$ERROR[] = "学習時間上限（秒）が未入力です。";
	}
	if ($_POST['data_type'] == 2 && !$_POST['study_time_to']) {
		$ERROR[] = "学習時間下限（秒）が未入力です。";
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

	global $L_DATA_TYPE;

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
			$HIDDEN .= "<input type=\"hidden\" name=\"".$key."\" value=\"".$val."\">\n";
		}
	}

	// 他の人が削除したかチェック
	if (ACTION) {
		foreach ($_POST as $key => $val) {
			$$key = $val;
		}
	} else {
		$HIDDEN .= "<input type=\"hidden\" name=\"action\" value=\"change\">\n";
		$sql = "SELECT * FROM ".T_MS_RANKING_POINT.
				" WHERE mt_ranking_point_num='".$_POST['mt_ranking_point_num']."' AND mk_flg='0' LIMIT 1;";
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
	if (MODE != "削除") {
		$button = "登録";
	} else {
		$button = "削除";
	}

	// 入力確認画面表示
	$html = "<br>\n";
	$html .= "確認画面：以下の内容で".$button."してもよろしければ".$button."ボタンをクリックしてください。<br>\n";

	// htmlクラス生成
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file(RANKING_POINT_FORM);

	//	$配列名['置換コメント名'] = array('コマンド名'=>'値','コマンド名'=>'値',....);
	if (!$mt_ranking_point_num) {
		$mt_ranking_point_num = "---";
	}
	$INPUTS['MTRANKINGPOINTNUM'] = array('result'=>'plane','value'=>$mt_ranking_point_num);
	$INPUTS['PATTERNNAME'] = array('result'=>'plane','value'=>$pattern_name);
	$INPUTS['DATATYPE'] = array('result'=>'plane','value'=>$L_DATA_TYPE[$data_type]);
	$INPUTS['COURSENUM'] = array('result'=>'plane','value'=>get_course_name($course_num));
	$INPUTS['STAGENUM'] = array('result'=>'plane','value'=>get_stage_name($stage_num));
	$INPUTS['LESSONNUM'] = array('result'=>'plane','value'=>get_lesson_name($lesson_num));
	$INPUTS['UNITNUM'] = array('result'=>'plane','value'=>get_unit_name($unit_num));
	$INPUTS['BLOCKNUM'] = array('result'=>'plane','value'=>get_block_name($block_num));
	$INPUTS['GOLDPERCENT'] = array('result'=>'plane','value'=>$gold_percent);
	$INPUTS['SILVERPERCENT'] = array('result'=>'plane','value'=>$silver_percent);
	$INPUTS['COPPERPERCENT'] = array('result'=>'plane','value'=>$copper_percent);
	$INPUTS['LEADPERCENT'] = array('result'=>'plane','value'=>$lead_percent);			// add koyama 2013/10/01 鉛メダル追加
	$INPUTS['GOLDCOUNT'] = array('result'=>'plane','value'=>$gold_count);
	$INPUTS['STUDYTIMEFROM'] = array('result'=>'plane','value'=>$study_time_from);
	$INPUTS['STUDYTIMETO'] = array('result'=>'plane','value'=>$study_time_to);
	$INPUTS['COMMENT'] = array('result'=>'plane','value'=>$comment);

	$make_html->set_rep_cmd($INPUTS);

	$html .= $make_html->replace();

	// 制御ボタン定義
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" style=\"float:left\">\n";
	$html .= $HIDDEN;

	// 削除の場合は、１画面目に戻る
	if (MODE == "削除") {
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";// add koyama 2013/10/01
		$html .= "<input type=\"hidden\" name=\"s_page\" value=\"1\">\n";
	}

	$html .= "<input type=\"submit\" value=\"".$button."\">\n";
	$html .= "</form>";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";

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
		$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";// add koyama 2013/10/01
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"ranking_point_list\">\n";
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
		if ($key == "mode") { continue; }
		if ($key == "action") { continue; }
		if ($key == "mt_ranking_point_num") { continue; }
		$INSERT_DATA[$key] = "$val";
	}
	$INSERT_DATA['default_flag'] = "1";										// add koyama 2013/10/01 デフォルトフラグ追加
	$INSERT_DATA['ins_syr_id']	= "add";
	$INSERT_DATA['ins_date']	= "now()";
	$INSERT_DATA['ins_tts_id']	= $_SESSION['myid']['id'];
	$INSERT_DATA['upd_syr_id']	= "add";
	$INSERT_DATA['upd_date']	= "now()";
	$INSERT_DATA['upd_tts_id']	= $_SESSION['myid']['id'];

	// ＤＢ追加処理
	$ERROR = $cdb->insert(T_MS_RANKING_POINT,$INSERT_DATA);

	if (!$ERROR) {
		$_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>";
	}
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
		$INSERT_DATA['pattern_name']	= $_POST['pattern_name'];
		$INSERT_DATA['data_type']		= $_POST['data_type'];
		$INSERT_DATA['course_num']		= $_POST['course_num'];
		$INSERT_DATA['stage_num']		= $_POST['stage_num'];
		$INSERT_DATA['lesson_num']		= $_POST['lesson_num'];
		$INSERT_DATA['unit_num']		= $_POST['unit_num'];
		$INSERT_DATA['block_num']		= $_POST['block_num'];
		$INSERT_DATA['gold_percent']	= $_POST['gold_percent'];
		$INSERT_DATA['silver_percent']	= $_POST['silver_percent'];
		$INSERT_DATA['copper_percent']	= $_POST['copper_percent'];
		$INSERT_DATA['lead_percent']	= $_POST['lead_percent'];			// add koyama 2013/10/01 鉛メダル追加
		$INSERT_DATA['gold_count']		= $_POST['gold_count'];
		$INSERT_DATA['default_flag']	= "1";								// add koyama 2013/10/01 デフォルトフラグ追加
		$INSERT_DATA['study_time_from']	= $_POST['study_time_from'];
		$INSERT_DATA['study_time_to']	= $_POST['study_time_to'];
		$INSERT_DATA['comment']			= $_POST['comment'];
		$INSERT_DATA['upd_syr_id']		= "update";
		$INSERT_DATA['upd_date']		= "now()";
		$INSERT_DATA['upd_tts_id']		= $_SESSION['myid']['id'];

		$where = " WHERE mt_ranking_point_num = '".$_POST['mt_ranking_point_num']."' LIMIT 1;";

		$ERROR = $cdb->update(T_MS_RANKING_POINT,$INSERT_DATA,$where);

		// 削除処理
	} elseif (MODE == "削除") {
		$INSERT_DATA['mk_flg']		= 1;
		$INSERT_DATA['mk_tts_id']	= $_SESSION['myid']['id'];
		$INSERT_DATA['mk_date']		= "now()";
		$INSERT_DATA['upd_syr_id']   = "del";									// 2012/11/05 add oda
		$INSERT_DATA['upd_tts_id'] = $_SESSION['myid']['id'];					// 2012/11/05 add oda
		$INSERT_DATA['upd_date']   = "now()";									// 2012/11/05 add oda
		$where = " WHERE mt_ranking_point_num = '".$_POST['mt_ranking_point_num']."' LIMIT 1;";

		$ERROR = $cdb->update(T_MS_RANKING_POINT,$INSERT_DATA,$where);

	}

	if (!$ERROR) {
		$_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>";
	}
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


/**
 * コース選択html生成
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $course_num
 * @return string HTML
 */
function get_course_select($course_num) {
	// 2013/10/01 koyama :コース選択を追加したので不要になりました

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$html = "";

	// html生成
	$sql  = "SELECT * FROM ".T_COURSE.
			" WHERE state = '0' ORDER BY list_num;";

	// 件数取得
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
	}
	// 件数チェック
	if (!$max) {
		$html = "<br>\n";
		$html .= "コースが存在しません。設定してからご利用下さい。";

	// selectタグ生成
	} else {

		$html .= "<select name=\"course_num\" onchange=\"ranking_course();\">";

		// コース指定が無い場合は、先頭行を選択しておく
		if ($course_num) {
			$selected = "selected";
		} else {
			$selected = "";
		}
		$html .= "<option value=\"\" $selected>選択して下さい</option>\n";

		// DBの内容でoptionタグを生成
		while ($list = $cdb->fetch_assoc($result)) {
			$course_num_ = $list['course_num'];
			$course_name_ = $list['course_name'];
			if ($course_num == $course_num_) {
				$selected = "selected";
			} else {
				$selected = "";
			}
			$html .= "<option value=\"".$course_num_."\" ".$selected.">".$course_name_."</option>\n";
		}

		$html .= "</select>";
	}

	return $html;
}


/**
 * ステージ選択html生成
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param $course_num
 * @param  $stage_num
 */
function get_stage_select($course_num, $stage_num) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// html生成
	$html = "";
	$html .= "<select name=\"stage_num\" onchange=\"ranking_stage();\">";

	// ステージ指定が無い場合は、先頭行を選択しておく
	if ($stage_num) {
		$selected = "selected";
	} else {
		$selected = "";
	}
	$html .= "<option value=\"\" $selected>選択して下さい</option>\n";

	$sql  = "SELECT * FROM ".T_STAGE.
			" WHERE state = '0' ".
			"   AND course_num = '".$course_num."'".
			" ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		// DBの内容でoptionタグを生成
		while ($list = $cdb->fetch_assoc($result)) {
			$stage_num_ = $list['stage_num'];
			$stage_name_ = $list['stage_name'];
			if ($stage_num == $stage_num_) {
				$selected = "selected";
			} else {
				$selected = "";
			}
			$html .= "<option value=\"".$stage_num_."\" ".$selected.">".$stage_name_."</option>\n";
		}
	}

	$html .= "</select>";

	return $html;
}


/**
 * レッスン選択html生成
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $course_num
 * @param integer $stage_num
 * @param integer $lesson_num
 * @return string HTML
 */
function get_lesson_select($course_num, $stage_num, $lesson_num) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// html生成
	$html = "";
	$html .= "<select name=\"lesson_num\" onchange=\"ranking_lesson();\">";

	// レッスン指定が無い場合は、先頭行を選択しておく
	if ($lesson_num) {
		$selected = "selected";
	} else {
		$selected = "";
	}
	$html .= "<option value=\"\" $selected>選択して下さい</option>\n";

	$sql  = "SELECT * FROM ".T_LESSON.
			" WHERE state = '0' ".
			"   AND course_num = '".$course_num."'".
			"   AND stage_num = '".$stage_num."'".
			" ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		// DBの内容でoptionタグを生成
		while ($list = $cdb->fetch_assoc($result)) {
			$lesson_num_ = $list['lesson_num'];
			$lesson_name_ = $list['lesson_name'];
			if ($lesson_num == $lesson_num_) {
				$selected = "selected";
			} else {
				$selected = "";
			}
			$html .= "<option value=\"".$lesson_num_."\" ".$selected.">".$lesson_name_."</option>\n";
		}
	}

	$html .= "</select>";

	return $html;
}


/**
 * ユニット選択html生成
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $course_num
 * @param integer $stage_num
 * @param integer $lesson_num
 * @param integer $unit_num
 * @return string HTML
 */
function get_unit_select($course_num, $stage_num, $lesson_num, $unit_num) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// html生成
	$html = "";
	$html .= "<select name=\"unit_num\" onchange=\"ranking_unit();\">";

	// ユニット指定が無い場合は、先頭行を選択しておく
	if ($unit_num) {
		$selected = "selected";
	} else {
		$selected = "";
	}
	$html .= "<option value=\"\" $selected>選択して下さい</option>\n";

	$sql  = "SELECT * FROM ".T_UNIT.
			" WHERE state = '0' ".
			"   AND course_num = '".$course_num."'".
			"   AND stage_num = '".$stage_num."'".
			"   AND lesson_num = '".$lesson_num."'".
			" ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		// DBの内容でoptionタグを生成
		while ($list = $cdb->fetch_assoc($result)) {
			$unit_num_ = $list['unit_num'];
			$unit_name_ = $list['unit_name'];
			if ($unit_num == $unit_num_) {
				$selected = "selected";
			} else {
				$selected = "";
			}
			$html .= "<option value=\"".$unit_num_."\" ".$selected.">".$unit_name_."</option>\n";
		}
	}

	$html .= "</select>";

	return $html;
}


/**
 * ブロック選択html生成
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $course_num
 * @param integer $stage_num
 * @param integer $lesson_num
 * @param integer $unit_num
 * @param integer $block_num
 * @return string HTML
 */
function get_block_select($course_num, $stage_num, $lesson_num, $unit_num, $block_num) {

	global $L_UNIT_TYPE;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// html生成
	$html = "";
	$html .= "<select name=\"block_num\">";

	// ブロック指定が無い場合は、先頭行を選択しておく
	if ($block_num) {
		$selected = "selected";
	} else {
		$selected = "";
	}
	$html .= "<option value=\"\" $selected>選択して下さい</option>\n";

	$sql  = "SELECT * FROM ".T_BLOCK.
			" WHERE state = '0' ".
			"   AND course_num = '".$course_num."'".
			"   AND stage_num = '".$stage_num."'".
			"   AND lesson_num = '".$lesson_num."'".
			"   AND unit_num = '".$unit_num."'".
			" ORDER BY list_num;";
	if ($result = $cdb->query($sql)) {
		// DBの内容でoptionタグを生成
		while ($list = $cdb->fetch_assoc($result)) {
			$block_num_ = $list['block_num'];
			$block_name_ = $L_UNIT_TYPE[$list['block_type']];
			if ($block_num == $block_num_) {
				$selected = "selected";
			} else {
				$selected = "";
			}
			$html .= "<option value=\"".$block_num_."\" ".$selected.">".$block_name_."</option>\n";
		}
	}

	$html .= "</select>";

	return $html;
}


/**
 * コース名称取得
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $course_num
 * @return string
 */
function get_course_name($course_num) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$course_name = "";

	// html生成
	$sql  = "SELECT ".
			"  course_name ".
			" FROM ".T_COURSE.
			" WHERE state = '0'".
			"   AND course_num = '".$course_num."'";

	// ＤＢ読み込み
	if ($result = $cdb->query($sql)) {

		// 名称を取得し、格納する
		while ($list = $cdb->fetch_assoc($result)) {
			$course_name = $list['course_name'];
		}
	}

	return $course_name;
}


/**
 * ステージ名称取得
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $stage_num
 * @return string
 */
function get_stage_name($stage_num) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$stage_name = "";

	// html生成
	$sql  = "SELECT ".
			"  stage_name ".
			" FROM ".T_STAGE.
			" WHERE state = '0'".
			"   AND stage_num = '".$stage_num."'";

	// ＤＢ読み込み
	if ($result = $cdb->query($sql)) {

		// 名称を取得し、格納する
		while ($list = $cdb->fetch_assoc($result)) {
			$stage_name = $list['stage_name'];
		}
	}

	return $stage_name;
}


/**
 * レッスン名称取得
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $lesson_num
 * @return string
 */
function get_lesson_name($lesson_num) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$lesson_name = "";

	// html生成
	$sql  = "SELECT ".
			"  lesson_name ".
			" FROM ".T_LESSON.
			" WHERE state = '0'".
			"   AND lesson_num = '".$lesson_num."'";

	// ＤＢ読み込み
	if ($result = $cdb->query($sql)) {

		// 名称を取得し、格納する
		while ($list = $cdb->fetch_assoc($result)) {
			$lesson_name = $list['lesson_name'];
		}
	}

	return $lesson_name;
}


/**
 * ユニット名称取得
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $unit_num
 * @return string
 */
function get_unit_name($unit_num) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$unit_name = "";

	// html生成
	$sql  = "SELECT ".
			"  unit_name ".
			" FROM ".T_UNIT.
			" WHERE state = '0'".
			"   AND unit_num = '".$unit_num."'";

	// ＤＢ読み込み
	if ($result = $cdb->query($sql)) {

		// 名称を取得し、格納する
		while ($list = $cdb->fetch_assoc($result)) {
			$unit_name = $list['unit_name'];
		}
	}

	return $unit_name;
}


/**
 * ブロック名称取得
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $block_num
 * @return string
 */
function get_block_name($block_num) {

	global $L_UNIT_TYPE;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$block_name = "";

	// html生成
	$sql  = "SELECT ".
			"  bk.block_type ".
			" FROM ".T_BLOCK." bk ".
			" WHERE bk.state = '0'".
			"   AND bk.block_num  = '".$block_num."'";

	// ＤＢ読み込み
	if ($result = $cdb->query($sql)) {

		// 名称を取得し、格納する
		while ($list = $cdb->fetch_assoc($result)) {
			$block_name = $L_UNIT_TYPE[$list['block_type']];
		}
	}

	return $block_name;
}


/**
 *  デフォルト情報取得
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $course_num
 * @return array
 */
function get_default_ranking_point($course_num) {		// add koyama 2013/10/01 コースを加味

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$DEFAULT_DATA = array();

	// html生成
	/* del koyama 2013/10/01
	$sql  = "SELECT ".
			"  * ".
			" FROM ".T_MS_RANKING_POINT." mrp ".
			" WHERE mrp.mk_flg = '0'".
			"   AND mrp.course_num = '0'".
			"   AND mrp.stage_num  = '0'".
			"   AND mrp.lesson_num = '0'".
			"   AND mrp.unit_num   = '0'".
			"   AND mrp.block_num  = '0'";
	*/
	//-------------------------------------------------// add start koyama 2013/10/01 コースを加味 + デフォルトフラグに切り替え
	$sql  = "SELECT ".
			"  * ".
			" FROM ".T_MS_RANKING_POINT." mrp ".
			" WHERE mrp.mk_flg = '0'".
			"   AND mrp.course_num = ".$course_num."".
			"   AND mrp.default_flag  = '0'";
	//-------------------------------------------------// add end koyama 2013/10/01 */
//echo "■sql == ".$sql."<br>\n";
	// ＤＢ読み込み
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$DEFAULT_DATA[$list['data_type']]['gold_percent']		= $list['gold_percent'];
			$DEFAULT_DATA[$list['data_type']]['silver_percent']		= $list['silver_percent'];
			$DEFAULT_DATA[$list['data_type']]['copper_percent']		= $list['copper_percent'];
			$DEFAULT_DATA[$list['data_type']]['lead_percent']		= $list['lead_percent'];	// 鉛メダル追加 add koyama 2013/10/01
			$DEFAULT_DATA[$list['data_type']]['gold_count']			= $list['gold_count'];
			$DEFAULT_DATA[$list['data_type']]['study_time_from']	= $list['study_time_from'];
			$DEFAULT_DATA[$list['data_type']]['study_time_to']		= $list['study_time_to'];
		}
	}
//echo "■DEFAULT_DATA　<br>\n";pre($DEFAULT_DATA); echo"\n\n";
	return $DEFAULT_DATA;
}


/**
 * デフォルトのＤＢ変更処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array エラーの場合
 */
function default_update() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

//echo "■POST　<br>\n";pre($_POST); echo"\n\n";
//echo "■SESSION　<br>\n";pre($_SESSION); echo"\n\n";
	$type1_exist = "";
	$type2_exist = "";

	// html生成
	/* del koyama 2013/10/01
	$sql  = "SELECT ".
			"  * ".
			" FROM ".T_MS_RANKING_POINT." mrp ".
			" WHERE mrp.mk_flg = '0'".
			"   AND mrp.data_type = '1'".
			"   AND mrp.course_num = '0'".
			"   AND mrp.stage_num  = '0'".
			"   AND mrp.lesson_num = '0'".
			"   AND mrp.unit_num   = '0'".
			"   AND mrp.block_num  = '0'";
	*/
	//-------------------------------------------------// add start koyama 2013/10/01 コースを加味 + デフォルトフラグに切り替え
	$sql  = "SELECT ".
			"  * ".
			" FROM ".T_MS_RANKING_POINT." mrp ".
			" WHERE mrp.mk_flg = '0'".
			"   AND mrp.data_type = '1'".
			"   AND mrp.course_num = ".$_POST['course_num']."".
			"   AND mrp.default_flag  = '0'";
	//-------------------------------------------------// add end koyama 2013/10/01 */
//echo $sql."<br>";
	// ＤＢ読み込み
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$type1_exist = $list['mt_ranking_point_num'];
		}
	}

	// デフォルト１のレコードが存在する時は、情報を更新する
	if ($type1_exist > 0) {
		// 更新処理
		$INSERT_DATA['gold_percent']	= $_POST['gold_percent_1'];
		$INSERT_DATA['silver_percent']	= $_POST['silver_percent_1'];
		$INSERT_DATA['copper_percent']	= $_POST['copper_percent_1'];
		//$INSERT_DATA['lead_percent']	= $_POST['lead_percent'];			// add koyama 2013/10/01 鉛メダル追加 // del koyama 213/10/31
		$INSERT_DATA['lead_percent']	= $_POST['lead_percent_1'];			// add koyama 2013/10/31 鉛メダル追加
		$INSERT_DATA['gold_count']		= $_POST['gold_count_1'];
		$INSERT_DATA['default_flag']	= "0";								// add koyama 2013/10/01 デフォルトフラグ追加
		$INSERT_DATA['study_time_from']	= "0";
		$INSERT_DATA['study_time_to']	= "0";
		$INSERT_DATA['upd_syr_id']		= "update";
		$INSERT_DATA['upd_date']		= "now()";
		$INSERT_DATA['upd_tts_id']		= $_SESSION['myid']['id'];

		$where = " WHERE mt_ranking_point_num = '".$type1_exist."' LIMIT 1;";

		$ERROR = $cdb->update(T_MS_RANKING_POINT,$INSERT_DATA,$where);
		// 存在しない時はデータを登録する
	} else {
		$INSERT_DATA['pattern_name']	= "デフォルト";
		$INSERT_DATA['data_type']		= "1";
		//$INSERT_DATA['course_num']	= "0";								// del koyama 2013/10/01
		$INSERT_DATA['course_num']		= $_POST['course_num'];				// add koyama 2013/10/01 コース番号追加
		$INSERT_DATA['stage_num']		= "0";
		$INSERT_DATA['lesson_num']		= "0";
		$INSERT_DATA['unit_num']		= "0";
		$INSERT_DATA['block_num']		= "0";
		$INSERT_DATA['gold_percent']	= $_POST['gold_percent_1'];
		$INSERT_DATA['silver_percent']	= $_POST['silver_percent_1'];
		$INSERT_DATA['copper_percent']	= $_POST['copper_percent_1'];
		$INSERT_DATA['lead_percent']	= $_POST['lead_percent_1'];			// add koyama 2013/10/01 鉛メダル追加
		$INSERT_DATA['gold_count']		= $_POST['gold_count_1'];
		$INSERT_DATA['default_flag']	= "0";								// add koyama 2013/10/01 デフォルトフラグ追加
		$INSERT_DATA['study_time_from']	= "0";
		$INSERT_DATA['study_time_to']	= "0";
		$INSERT_DATA['comment']			= "";
		$INSERT_DATA['ins_syr_id']		= "add";
		$INSERT_DATA['ins_date']		= "now()";
		$INSERT_DATA['ins_tts_id']		= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_syr_id']		= "add";
		$INSERT_DATA['upd_date']		= "now()";
		$INSERT_DATA['upd_tts_id']		= $_SESSION['myid']['id'];

		// ＤＢ追加処理
		$ERROR = $cdb->insert(T_MS_RANKING_POINT,$INSERT_DATA);

	}

	// html生成
	/*
	$sql  = "SELECT ".
			"  * ".
			" FROM ".T_MS_RANKING_POINT." mrp ".
			" WHERE mrp.mk_flg = '0'".
			"   AND mrp.data_type = '2'".
			"   AND mrp.course_num = '0'".
			"   AND mrp.stage_num  = '0'".
			"   AND mrp.lesson_num = '0'".
			"   AND mrp.unit_num   = '0'".
			"   AND mrp.block_num  = '0'";
	*/
	//-------------------------------------------------// add start koyama 2013/10/01 コースを加味 + デフォルトフラグに切り替え
	$sql  = "SELECT ".
			"  * ".
			" FROM ".T_MS_RANKING_POINT." mrp ".
			" WHERE mrp.mk_flg = '0'".
			"   AND mrp.data_type = '2'".
			"   AND mrp.course_num = ".$_POST['course_num']."".
			"   AND mrp.default_flag  = '0'";
	//-------------------------------------------------// add end koyama 2013/10/01 */
//echo $sql."<br>";

	// ＤＢ読み込み
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$type2_exist = $list['mt_ranking_point_num'];
		}
	}

	// デフォルト２のレコードが存在する時は、情報を更新する
	if ($type2_exist > 0) {
		// 更新処理
		$INSERT_DATA['gold_percent']	= $_POST['gold_percent_2'];
		$INSERT_DATA['silver_percent']	= $_POST['silver_percent_2'];
		$INSERT_DATA['copper_percent']	= $_POST['copper_percent_2'];
		$INSERT_DATA['lead_percent']	= $_POST['lead_percent_2'];			// add koyama 2013/10/01 鉛メダル追加
		$INSERT_DATA['gold_count']		= $_POST['gold_count_2'];
		$INSERT_DATA['default_flag']	= "0";								// add koyama 2013/10/01 デフォルトフラグ追加
		$INSERT_DATA['study_time_from']	= $_POST['study_time_from_2'];
		$INSERT_DATA['study_time_to']	= $_POST['study_time_to_2'];
		$INSERT_DATA['upd_syr_id']		= "update";
		$INSERT_DATA['upd_date']		= "now()";
		$INSERT_DATA['upd_tts_id']		= $_SESSION['myid']['id'];

		$where = " WHERE mt_ranking_point_num = '".$type2_exist."' LIMIT 1;";

		$ERROR = $cdb->update(T_MS_RANKING_POINT,$INSERT_DATA,$where);
		// 存在しない時はデータを登録する
	} else {
		$INSERT_DATA['pattern_name']	= "デフォルト";
		$INSERT_DATA['data_type']		= "2";
		//$INSERT_DATA['course_num']	= "0";								// del koyama 2013/10/01
		$INSERT_DATA['course_num']		= $_POST['course_num'];				// add koyama 2013/10/01 コース番号追加
		$INSERT_DATA['stage_num']		= "0";
		$INSERT_DATA['lesson_num']		= "0";
		$INSERT_DATA['unit_num']		= "0";
		$INSERT_DATA['block_num']		= "0";
		$INSERT_DATA['gold_percent']	= $_POST['gold_percent_2'];
		$INSERT_DATA['silver_percent']	= $_POST['silver_percent_2'];
		$INSERT_DATA['copper_percent']	= $_POST['copper_percent_2'];
		$INSERT_DATA['lead_percent']	= $_POST['lead_percent_2'];			// add koyama 2013/10/01 鉛メダル追加
		$INSERT_DATA['gold_count']		= $_POST['gold_count_2'];
		$INSERT_DATA['default_flag']	= "0";								// add koyama 2013/10/01 デフォルトフラグ追加
		$INSERT_DATA['study_time_from']	= $_POST['study_time_from_2'];
		$INSERT_DATA['study_time_to']	= $_POST['study_time_to_2'];
		$INSERT_DATA['comment']			= "";
		$INSERT_DATA['ins_syr_id']		= "add";
		$INSERT_DATA['ins_date']		= "now()";
		$INSERT_DATA['ins_tts_id']		= $_SESSION['myid']['id'];
		$INSERT_DATA['upd_syr_id']		= "add";
		$INSERT_DATA['upd_date']		= "now()";
		$INSERT_DATA['upd_tts_id']		= $_SESSION['myid']['id'];

		// ＤＢ追加処理
		$ERROR = $cdb->insert(T_MS_RANKING_POINT,$INSERT_DATA);

	}

	if (!$ERROR) {
		$_SESSION[select_menu] = MAIN . "<>" . SUB . "<><><>";
	}
	return $ERROR;
}
?>
