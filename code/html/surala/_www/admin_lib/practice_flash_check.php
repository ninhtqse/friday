<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * プラクティスステージ管理　FLASH動作確認
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

	list($html,$L_COURSE,$L_STAGE,$L_LESSON) = select_course();

	if ($_POST['course_num']&&$_POST['stage_num']&&$_POST['lesson_num']) {
		$html .= unit_list($L_COURSE,$L_STAGE,$L_LESSON);
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

	if ($_POST['course_num'] && $_POST['b_course_num'] && $_POST['b_course_num'] != $_POST['course_num']) { unset($_POST['stage_num']); }

	// add start oda 2015/03/11
	// 使用禁止のコースが存在するかチェックし、抽出条件を作成する
	$course_list = "";
	if (is_array($_SESSION['authority']) && count($_SESSION['authority']) > 0) {
		foreach ($_SESSION['authority'] as $key => $value) {
			if (!$value) { continue; }
			// 使用禁止のコースが存在した場合
			//if (substr($value,0,24) == "practice__drill__course_") {		// del oda 2018/07/12 権限チェック不具合
			if (substr($value,0,24) == "practice__flash__course_") {		// add oda 2018/07/12 権限チェック不具合
				if ($course_list) { $course_list .= ","; }
				$course_list .= substr($value,24);						// コース番号を取得する
			}
		}
	}
	// add end oda 2015/03/11

	// update start oda 2015/03/11 権限制御修正
	//$sql  = "SELECT * FROM ".T_COURSE.
	//		" WHERE state!='1' ORDER BY list_num;";
	$sql  = "SELECT course_num,course_name FROM ".T_COURSE;
	$sql .= " WHERE state!='1'";
	if ($course_list) { $sql .= " AND course_num NOT IN (".$course_list.") "; }
	$sql .= " ORDER BY list_num;";
	// update end oda 2015/03/11

	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
	}
	if (!$max) {
		$html = "<br>\n";
		$html .= "コースが存在しません。設定してからご利用下さい。";
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

	if ($_POST['course_num']) {
		$sql  = "SELECT * FROM ".T_STAGE.
				" WHERE course_num='$_POST[course_num]' AND state!='1' ORDER BY list_num;";
		if ($result = $cdb->query($sql)) {
			$max = $cdb->num_rows($result);
		}
		if (!$max) {
			$stage_num_html .= "<option value=\"\">--------</option>\n";
		} else {
			if (!$_POST['course_num']) { $selected = "selected"; } else { $selected = ""; }
			$stage_num_html .= "<option value=\"\" $selected>選択して下さい</option>\n";
			while ($list = $cdb->fetch_assoc($result)) {
				$L_STAGE[$list['stage_num']] = $list['stage_name'];
				if ($_POST['stage_num'] == $list['stage_num']) { $selected = "selected"; } else { $selected = ""; }
				$stage_num_html .= "<option value=\"{$list[stage_num]}\" $selected>{$list[stage_name]}</option>\n";
			}
		}
	} else {
		$stage_num_html .= "<option value=\"\">--------</option>\n";
	}

	if ($_POST['stage_num']) {
		$sql  = "SELECT * FROM ".T_LESSON.
				" WHERE stage_num='$_POST[stage_num]' AND state!='1' ORDER BY list_num;";

		if ($result = $cdb->query($sql)) {
			$max = $cdb->num_rows($result);
		}
		if (!$max) {
			$lesson_num_html .= "<option value=\"\">ステージ内にLessonがありません。</option>\n";
		} else {
			if (!$_POST['lesson_num']) { $selected = "selected"; } else { $selected = ""; }
			$lesson_num_html .= "<option value=\"\" $selected>選択して下さい</option>\n";
			while ($list = $cdb->fetch_assoc($result)) {
				$L_LESSON[$list['lesson_num']] = $list['lesson_name'];
				if ($_POST['lesson_num'] == $list['lesson_num']) { $selected = "selected"; } else { $selected = ""; }
				$lesson_num_html .= "<option value=\"{$list['lesson_num']}\" $selected>{$list['lesson_name']}</option>\n";
			}
		}
	} else {
		$lesson_num_html .= "<option value=\"\">--------</option>\n";
	}

	$html = "<br>\n";
	$html .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"menu\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"set_course\">\n";
	$html .= "<table class=\"unit_form\">\n";
	$html .= "<tr class=\"unit_form_menu\">\n";
	$html .= "<td>コース</td>\n";
	$html .= "<td>ステージ</td>\n";
	$html .= "<td>Lesson</td>\n";
	$html .= "</tr>\n";
	$html .= "<tr class=\"unit_form_cell\">\n";
	$html .= "<td><select name=\"course_num\" onchange=\"submit_course();\">\n";
	$html .= $couse_num_html;
	$html .= "</select></td>\n";
	$html .= "<td><select name=\"stage_num\" onchange=\"submit_stage();\">\n";
	$html .= $stage_num_html;
	$html .= "</select></td>\n";
	$html .= "<td><select name=\"lesson_num\" onchange=\"submit();\">\n";
	$html .= $lesson_num_html;
	$html .= "</select></td>\n";
	$html .= "</tr>\n";
	$html .= "</table>\n";
	$html .= "</form><br>\n";

	if (!$_POST['course_num']) {
		$html .= "<br>\n";
		$html .= "コースを選択してください。<br>\n";
	} elseif ($_POST['course_num'] && !$_POST['stage_num']) {
		$html .= "<br>\n";
		$html .= "ステージを選択してください。<br>\n";
	} elseif ($_POST['course_num'] && $_POST['stage_num'] && !$_POST['lesson_num']) {
		$html .= "<br>\n";
		$html .= "Lessonを選択してください。<br>\n";
	}

	return array($html,$L_COURSE,$L_STAGE,$L_LESSON);
}

/**
 * ユニット選択処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $L_COURSE
 * @param array $L_STAGE
 * @param array $L_LESSON
 * @return string HTML
 */
function unit_list($L_COURSE,$L_STAGE,$L_LESSON) {

	global $L_DISPLAY;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//	ファイルアップロード用仮設定
	$flash_ftp = FTP_URL."flash/".$_POST['course_num']."/".$_POST['stage_num']."/".$_POST['lesson_num']."/";

	$sql  = "SELECT u.unit_num, c.course_name, u.unit_name, u.unit_key, u.display".
			" FROM ".T_COURSE." c, ".T_STAGE." s, ".T_LESSON." l, ".T_UNIT." u" .
			" WHERE c.course_num=s.course_num AND s.stage_num=l.stage_num AND l.lesson_num=u.lesson_num" .
			" AND u.course_num='".$_POST['course_num']."' AND c.state!='1'" .
			" AND u.stage_num='".$_POST['stage_num']."' AND s.state!='1'" .
			" AND u.lesson_num='".$_POST['lesson_num']."' AND l.state!='1'" .
			" AND u.state!='1' ORDER BY u.list_num;";

	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
		if (!$max) {
			$html .= "<br>\n";
			$html .= "今現在登録されているユニットは有りません。<br>\n";
		} else {

			//add start hirose 2018/05/01 管理画面手書き切り替え機能追加
			if(isset($_COOKIE["tegaki_flag"])){
				$_SESSION['TEGAKI_FLAG'] = $_COOKIE["tegaki_flag"];
			}else{
				$_SESSION['TEGAKI_FLAG'] = 1;
			}
			//add end hirose 2018/05/01 管理画面手書き切り替え機能追加

			//add start hirose 2018/04/24 FTPをブラウザからのエクスプローラーで開けない不具合対応
			$html .= "<p>※ブラウザからFTPのエクスプローラーが開けない場合、下記のURLを直接エクスプローラーに入力してファイルを参照してください。
			<br>【".$flash_ftp."】<br>\n</p>";
			//add end hirose 2018/04/24 FTPをブラウザからのエクスプローラーで開けない不具合対応

			//add start hirose 2018/05/01 管理画面手書き切り替え機能追加
			$check = "checked";
			if($_SESSION['TEGAKI_FLAG'] == 0){
				$check = "";
			}
			$onchenge = "onclick=\"this.blur(); this.focus();\" onchange=\"update_tegaki_flg(this,'menu');\"";
			$html .= "<div class=\"tegaki-switch\">";
			$html .= "<label>";
			$html .= "<input type=\"checkbox\" name=\"tegaki_control\" ".$check." ".$onchenge." class=\"tegaki-check\"><span class=\"swith-content\"></span><span class=\"swith-button\"></span>";
			$html .= "</label>";
			$html .= "</div>";
			//add end hirose 2018/05/01 管理画面手書き切り替え機能追加

			$html .= "<form name=\"flash_view\">\n";
			$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
			$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$_POST['stage_num']."\">\n";
			$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\">\n";
			$html .= "<table class=\"course_form\">\n";
// 2012/08/03 update start oda
//			$html .= "<tr class=\"course_form_menu\">\n";
//			$html .= "<th>登録番号</th>\n";
//			$html .= "<th>コース名</th>\n";
//			$html .= "<th>ユニット名</th>\n";
//			$html .= "<th>ユニットキー</th>\n";
//			$html .= "<th>表示・非表示</th>\n";
//			$html .= "<th>FLASHフォルダー</th>\n";
//			$html .= "<th>新規学習</th>\n";
//			$html .= "<th>復習学習</th>\n";
//			$html .= "<th>診断復習学習</th>\n";
//			$html .= "<th>教務ツール</th>\n";
//			$html .= "</tr>\n";
			$html .= "<tr class=\"course_form_menu\">\n";
			$html .= "<th rowspan=\"2\" >登録番号</th>\n";
			$html .= "<th rowspan=\"2\">コース名</th>\n";
			$html .= "<th rowspan=\"2\">ユニット名</th>\n";
			$html .= "<th rowspan=\"2\">ユニットキー</th>\n";
			$html .= "<th rowspan=\"2\">表示・非表示</th>\n";
			$html .= "<th rowspan=\"2\">FLASHフォルダー</th>\n";
			$html .= "<th rowspan=\"2\">設定</th>\n";
			$html .= "<th colspan=\"3\">FLASH</th>\n";
			$html .= "<th>HTML5(計算マスター用)</th>\n";				// upd hasegawa 2016/05/16 小学生低学年版対応 既存のHTMLレクチャー確認ボタンは計算マスターだけで使用するように変更
			$html .= "<th colspan=\"4\">HTML5レクチャー</th>\n";		// add hasegawa 2016/05/16 小学生低学年版対応 // update 3→4 yoshizawa 2016/10/07 小学生低学年版対応
			$html .= "<th colspan=\"2\">HTML5ゲーム</th>\n";			// add hasegawa 2016/05/13 小学生低学年版対応 // update colspanを追加 yoshizawa 2016/10/07 小学生低学年版対応
			$html .= "<th colspan=\"3\">HTML5&nbsp;(bookmode)</th>\n";	//add okabe 2014/12/01  Make5コンテンツ表示
			$html .= "</tr>\n";
			$html .= "<tr class=\"course_form_menu\">\n";
			$html .= "<th>新規学習</th>\n";
			$html .= "<th>復習学習</th>\n";
			$html .= "<th>診断復習学習</th>\n";
			//$html .= "<th>教務ツール</th>\n";
			//add okabe start 2014/12/01   Make5コンテンツ表示
			$html .= "<th>レクチャー</th>\n";				// add hasegawa 2016/05/16 小学生低学年版対応
			$html .= "<th>新規学習</th>\n";					// add hasegawa 2016/05/16 小学生低学年版対応
			$html .= "<th>復習学習</th>\n";					// add hasegawa 2016/05/16 小学生低学年版対応
			$html .= "<th>診断復習学習</th>\n";				// add hasegawa 2016/05/16 小学生低学年版対応
			$html .= "<th>解答確認</th>\n";					// add yoshizawa 2016/10/07 小学生低学年版対応
			$html .= "<th>ゲーム</th>\n";					// add hasegawa 2016/05/13 小学生低学年版対応
			$html .= "<th>解答確認</th>\n";					// add yoshizawa 2016/10/07 小学生低学年版対応
			$html .= "<th>新規学習</th>\n";
			$html .= "<th>復習学習</th>\n";
			$html .= "<th>診断復習学習</th>\n";
			//add okabe end 2014/12/01   Make5コンテンツ表示
			$html .= "</tr>\n";
// 2012/08/03 update start oda

			while ($list = $cdb->fetch_assoc($result)) {
				$material_flash_path  = MATERIAL_FLASH_DIR.$_POST['course_num']."/".$_POST['stage_num']."/".
										$_POST['lesson_num']."/".$list['unit_num']."/";
				if (!file_exists($material_flash_path)) {
					@mkdir($material_flash_path, 0777);
					@chmod($material_flash_path, 0777);
				}

				$material_prob_path   = MATERIAL_PROB_DIR.$_POST['course_num']."/".$_POST['stage_num']."/".
										$_POST['lesson_num']."/".$list['unit_num']."/";
				if (!file_exists($material_prob_path)) {
					@mkdir($material_prob_path,0777);
					@chmod($material_prob_path,0777);
				}

				$material_voice_path  = MATERIAL_VOICE_DIR.$_POST['course_num']."/".$_POST['stage_num']."/".
										$_POST['lesson_num']."/".$list['unit_num']."/";
				if (!file_exists($material_voice_path)) {
					@mkdir($material_voice_path,0777);
					@chmod($material_voice_path,0777);
				}

				// 2012/08/03 add start oda
				// path 編集
				$read_path_flash = MATERIAL_FLASH_DIR.$_POST['course_num']."/".$_POST['stage_num']."/".
						$_POST['lesson_num']."/".$list['unit_num']."/main.swf";

				$read_path_html = MATERIAL_FLASH_DIR.$_POST['course_num']."/".$_POST['stage_num']."/".
							$_POST['lesson_num']."/".$list['unit_num']."/index.html";
				$read_path_html_main = MATERIAL_FLASH_DIR.$_POST['course_num']."/".$_POST['stage_num']."/".			// add oda 2016/08/30 小学校低学年版対応
					$_POST['lesson_num']."/".$list['unit_num']."/".STUDENT_FLASH_MOVIE_FILE_HTML5;					// update oda 2018/02/22 定数化
				$read_path_html_main_es = MATERIAL_FLASH_DIR.$_POST['course_num']."/".$_POST['stage_num']."/".		// add oda 2018/02/22 html5判断追加
					$_POST['lesson_num']."/".$list['unit_num']."/".STUDENT_FLASH_MOVIE_FILE_HTML5_ES;
				$read_path_html_main_satt = MATERIAL_FLASH_DIR.$_POST['course_num']."/".$_POST['stage_num']."/".	// add oda 2018/02/22 html5判断追加
					$_POST['lesson_num']."/".$list['unit_num']."/".STUDENT_FLASH_MOVIE_FILE_HTML5_SATT;
				// add start hasegawa 2016/05/13 小学生低学年版対応
				$read_path_game = MATERIAL_FLASH_DIR.$_POST['course_num']."/".$_POST['stage_num']."/".
						$_POST['lesson_num']."/".$list['unit_num']."/game/".STUDENT_GAME_FILE;
				// add end hasegawa
				//add okabe start 2014/12/01  Make5コンテンツ表示
				$read_path_make5 = MATERIAL_FLASH_DIR.$_POST['course_num']."/".$_POST['stage_num']."/".
						$_POST['lesson_num']."/".$list['unit_num']."/html5/main.swf.html";
				//add okabe end 2014/12/01  Make5コンテンツ表示
				// 設定確認
				$setted_media_flag = 0;	//add okabe start 2014/12/01   Make5コンテンツ表示
				$set_media = "<span style=\"color=red\">未設定</span>";
				if (file_exists($read_path_flash)) {
					$set_media = "FLASH設定済";
					$setted_media_flag = 1;	//add okabe start 2014/12/01   Make5コンテンツ表示
				}
				// update start oda 2016/08/30 小学校低学年版対応
				if (file_exists($read_path_html)) {
					//if($_POST['course_num'] == "4") {			// add start hasegawa 2016/05/16 小学生低学年版対応
					//	$set_media .= "<br>(計算マスター用)";
					//} else {
					//	$set_media .= "<br>(小学生低学年用)";
					//}							// add end hasegawa 2016/05/16
					if($_POST['course_num'] == "4") {			// add start hasegawa 2016/05/16 小学生低学年版対応
						$set_media = "HTML5設定済";
						$set_media .= "<br>(計算マスター用)";
						$setted_media_flag = 1;	//add okabe start 2014/12/01   Make5コンテンツ表示
					}							// add end hasegawa 2016/05/16
				}
				// update start 2017/01/31 yoshizawa
				// main.swfとmain.htmlが混在する場合があるのでFLASHファイルが存在する場合はFLASHレクチャー設定とする。
				// if (file_exists($read_path_html_main)) {
				//if (file_exists($read_path_html_main) && !file_exists($read_path_flash)) {
				// update end 2017/01/31 yoshizawa
				// udpate oda 2018/02/22 html5レクチャー判断修正
				if (file_exists($read_path_html_main) && (file_exists($read_path_html_main_es) || file_exists($read_path_html_main_satt))) {
					$set_media .= "<br>HTML5設定済";
					$set_media .= "<br>(html5)";
					$setted_media_flag = 1;	//add okabe start 2014/12/01   Make5コンテンツ表示
				}
				// update end oda 2016/08/30
				// 2012/08/03 add end oda

				// add start hasegawa 2016/05/13 小学生低学年版対応
				if (file_exists($read_path_game)) {
					if ($setted_media_flag == 0) {
						$set_media = "";
					} else {
						$set_media .= "<br/>";
					}
					$set_media .= "ゲーム 設定済";
					$setted_media_flag = 1;
				}
				// add end hasegawa

				//add okabe start 2014/12/01   Make5コンテンツ表示 -> bookmode
				$disabled5 = "disabled";
				if (file_exists($read_path_make5)) {
					if ($setted_media_flag == 0) {
						$set_media = "";
					} else {
						$set_media .= "<br/>";
					}
					$set_media .= "HTML5(bookmode)<br>設定済";
					$disabled5 = "";
				}
				//add okabe end 2014/12/01   Make5コンテンツ表示

				$html .= "<tr class=\"course_form_cell\">\n";
				$html .= "<td>".$list['unit_num']."</td>\n";
				$html .= "<td>".$list['course_name']."</td>\n";
				$html .= "<td>".$list['unit_name']."</td>\n";
				$html .= "<td>".$list['unit_key']."</td>\n";
				$display = $list['display'];
				$html .= "<td>".$L_DISPLAY[$display]."</td>\n";
				$ftp_link = $flash_ftp.$list['unit_num']."/";
				$html .= "<td><a href=\"".$ftp_link."\" target=\"_blank\">FTP</a></td>\n";
				$html .= "<td>".$set_media."</td>\n";		// 2012/08/03 add oda
				$html .= "<td>\n";
				$html .= "<input type=\"button\" value=\"新規学習\" OnClick=\"check_flash_win_open('1','".$list['unit_num']."');\"><br>\n";
				$html .= "開始ページ<br>\n";
				$html .= "<input type=\"text\" size=\"4\" name=\"current_page_1_".$list['unit_num']."\" value=\"\">\n";
				$html .= "</td>\n";
				$html .= "<td>\n";
				$html .= "<input type=\"button\" value=\"復習学習\" OnClick=\"check_flash_win_open('2','".$list['unit_num']."');\"><br>\n";
				$html .= "開始ページ<br>\n";
				$html .= "<input type=\"text\" size=\"4\" name=\"current_page_2_".$list['unit_num']."\" value=\"\">\n";
				$html .= "</td>\n";
				$html .= "<td>\n";
				$html .= "<input type=\"button\" value=\"診断復習学習\" OnClick=\"check_flash_win_open('3','".$list['unit_num']."');\"><br>\n";
				$html .= "開始ページ<br>\n";
				$html .= "<input type=\"text\" size=\"4\" name=\"current_page_3_".$list['unit_num']."\" value=\"\"><br>\n";
				$html .= "閲覧ページ(カンマ区切り)<br>\n";
				$html .= "<input type=\"text\" size=\"14\" name=\"lesson_page_".$list['unit_num']."\" value=\"\">\n";
				$html .= "</td>\n";
				//$html .= "<td>\n";
				//$html .= "<input type=\"button\" value=\"教務ツール\" OnClick=\"check_flash_win_open('4','".$list[unit_num]."');\"><br>\n";
				//$html .= "開始ページ<br>\n";
				//$html .= "<input type=\"text\" size=\"4\" name=\"current_page_4_".$list[unit_num]."\" value=\"\">\n";
				//$html .= "</td>\n";
				// 2012/08/02 add start oda
				$html .= "<td style=\"text-align:center;\">\n";
				$html .= "<input type=\"button\" value=\"レクチャー\" OnClick=\"check_lecture_win_open('".$list['unit_num']."');\"><br>\n";
				$html .= "</td>\n";
				// add start hasegawa 2016/05/13 小学生低学年版対応
				$html .= "<td>\n";
				$html .= "<input type=\"button\" value=\"新規学習\" OnClick=\"check_lecture_win_open2('1','".$list['unit_num']."');\"><br>\n";
				$html .= "開始ページ<br>\n";
				$html .= "<input type=\"text\" size=\"4\" name=\"lg_current_page_1_".$list['unit_num']."\" value=\"\">\n";
				$html .= "</td>\n";
				$html .= "<td>\n";
				$html .= "<input type=\"button\" value=\"復習学習\" OnClick=\"check_lecture_win_open2('2','".$list['unit_num']."');\"><br>\n";
				$html .= "開始ページ<br>\n";
				$html .= "<input type=\"text\" size=\"4\" name=\"lg_current_page_2_".$list['unit_num']."\" value=\"\">\n";
				$html .= "</td>\n";
				$html .= "<td>\n";
				$html .= "<input type=\"button\" value=\"診断復習学習\" OnClick=\"check_lecture_win_open2('3','".$list['unit_num']."');\"><br>\n";
				$html .= "開始ページ<br>\n";
				$html .= "<input type=\"text\" size=\"4\" name=\"lg_current_page_3_".$list['unit_num']."\" value=\"\"><br>\n";
				$html .= "閲覧ページ(カンマ区切り)<br>\n";
				$html .= "<input type=\"text\" size=\"14\" name=\"lg_lesson_page_".$list['unit_num']."\" value=\"\">\n";
				$html .= "</td>\n";
				// add start yoshizawa 2016/10/07 小学生低学年版対応
				$html .= "<td>\n";
				$html .= "<input type=\"button\" value=\"解答確認\" OnClick=\"answer_list_win_open('".$list['unit_num']."','lecture');\"><br>\n";
				$html .= "</td>\n";
				// add end yoshizawa 2016/10/07 小学生低学年版対応

				$html .= "<td>\n";
				$html .= "<input type=\"button\" value=\"ゲーム\" OnClick=\"check_game_win_open('".$list['unit_num']."');\"><br>\n";
				$html .= "</td>\n";
				// add start yoshizawa 2016/10/07 小学生低学年版対応
				$html .= "<td>\n";
				$html .= "<input type=\"button\" value=\"解答確認\" OnClick=\"answer_list_win_open('".$list['unit_num']."','game');\"><br>\n";
				$html .= "</td>\n";
				// add end yoshizawa 2016/10/07 小学生低学年版対応

				// add end hasegawa

				// 2012/08/02 add end oda
				//add okae start 2014/12/01  Make5コンテンツ表示
//html += '		location.href="/material/flash/1/5/41/102/html5/main.swf.html";\n';
//				$read_path_flash = MATERIAL_FLASH_DIR.$_POST['course_num']."/".$_POST['stage_num']."/".
//						$_POST['lesson_num']."/".$list['unit_num']."/main.swf";

				//add okabe start 2014/12/04
				$url_path = "/material/flash/".$_POST['course_num']."/".$_POST['stage_num']."/".$_POST['lesson_num']."/".$list['unit_num']."/html5/";
				//$next_url = $_SERVER[PHP_SELF].urlencode("?erea=end");
				$next_url = "/admin/check_html5.php".urlencode("?erea=end");
				$url_base_params = "?log_url=/admin/log.php";
				$url_base_params .= "&next_sco_url=".$next_url;
				//$url_base_params .= "&status=true";
				//$url_base_params .= "&indicator_flag=true";
				$url_base_params .= "&url_path=./";
				//add okabe end 2014/12/04

				$html .= "<td>\n";
				$html .= "<input type=\"button\" value=\"新規学習\" OnClick=\"check_make5_win_open('1','".$list['unit_num']."','".$url_path."','".$url_base_params."','".$disabled5."');\"><br>\n";	//edit okabe 2014/12/04
				$html .= "開始ページ<br>\n";
				$html .= "<input type=\"text\" size=\"4\" name=\"html5_current_page_1_".$list['unit_num']."\" value=\"\">\n";
				$html .= "</td>\n";
				$html .= "<td>\n";
				$html .= "<input type=\"button\" value=\"復習学習\" OnClick=\"check_make5_win_open('2','".$list['unit_num']."','".$url_path."','".$url_base_params."','".$disabled5."');\"><br>\n";	//edit okabe 2014/12/04
				$html .= "開始ページ<br>\n";
				$html .= "<input type=\"text\" size=\"4\" name=\"html5_current_page_2_".$list['unit_num']."\" value=\"\">\n";
				$html .= "</td>\n";
				$html .= "<td>\n";
				$html .= "<input type=\"button\" value=\"診断復習学習\" OnClick=\"check_make5_win_open('3','".$list['unit_num']."','".$url_path."','".$url_base_params."','".$disabled5."');\"><br>\n";	//edit okabe 2014/12/04
				$html .= "開始ページ<br>\n";
				$html .= "<input type=\"text\" size=\"4\" name=\"html5_current_page_3_".$list['unit_num']."\" value=\"\"><br>\n";
				$html .= "閲覧ページ(カンマ区切り)<br>\n";
				$html .= "<input type=\"text\" size=\"14\" name=\"html5_lesson_page_".$list['unit_num']."\" value=\"\">\n";
				$html .= "</td>\n";
				//add okae end 2014/12/01  Make5コンテンツ表示
				$html .= "</tr>\n";
			}
			$html .= "</table>\n";
			$html .= "</form>\n";
		}
	}

	return $html;
}
?>
