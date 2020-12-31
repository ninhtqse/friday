<?php
/**
 * ベンチャー・リンク　すらら
 *
 * プラクティスステージ管理　インポート
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

	if (MODE == "upload") {
		$MSG = upload();
		if (!$MSG['error']) {
			$html .= upload_html($MSG['msg']);
		} else {
			$html .= select_course($MSG);
		}
	} else {
		$html .= select_course($ERROR);
	}

	return $html;
}


/**
 * コース選択
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $MSG
 * @return string HTML
 */
function select_course($MSG) {

	global $L_UNIT_TYPE;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$A_TABLE = T_COURSE;
	$B_TABLE = T_STAGE;
	$C_TABLE = T_LESSON;
	$D_TABLE = T_UNIT;

	// add start oda 2014/10/08
	// 使用禁止のコースが存在するかチェックし、抽出条件を作成する
	$course_list = "";
	if (is_array($_SESSION['authority']) && count($_SESSION['authority']) > 0) {
		foreach ($_SESSION['authority'] as $key => $value) {
			if (!$value) { continue; }
			// 使用禁止のコースが存在した場合
			if (substr($value,0,25) == "practice__import__course_") {
				if ($course_list) { $course_list .= ","; }
				$course_list .= substr($value,25);						// コース番号を取得する
			}
		}
	}
	// add end oda 2014/10/08

	$flag = 0;

	// update start oda 2014/10/08 権限制御修正
	//$sql  = "SELECT * FROM ".T_COURSE.
	//		" WHERE state!='1' ORDER BY list_num;";
	$sql  = "SELECT * FROM ".T_COURSE;
	$sql .= " WHERE state!='1'";
	if ($course_list) { $sql .= " AND course_num NOT IN (".$course_list.") "; }
	$sql .= " ORDER BY list_num;";
	// update end oda 2014/10/08

	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);

		if ($max) {

			if (!$_POST['course_num']) { $selected = "selected"; } else { $selected = ""; }
			$course_num_html .= "<option value=\"\" $selected>選択して下さい</option>\n";
			while ($list = $cdb->fetch_assoc($result)) {
				if ($_POST['course_num'] == $list['course_num']) { $selected = "selected"; } else { $selected = ""; }
				$course_num_html .= "<option value=\"".$list['course_num']."\" ".$selected.">".$list['course_name']."</option>\n";
				if ($selected) { $L_NAME['course_name'] = $list['course_name']; }
			}

			if (!$_POST['stage_num']) { $selected = "selected"; } else { $selected = ""; }
			$stage_num_html .= "<option value=\"\" ".$selected.">----------</option>\n";

			if ($_POST['course_num']) {
				$sql  = "SELECT * FROM ".T_STAGE.
						" WHERE state!='1' AND course_num='".$_POST['course_num']."' ORDER BY list_num;";
				if ($result = $cdb->query($sql)) {
					$max2 = $cdb->num_rows($result);
					if ($max2) {
						while ($list = $cdb->fetch_assoc($result)) {
							if ($_POST['stage_num'] == $list['stage_num']) { $selected = "selected"; } else { $selected = ""; }
							$stage_num_html .= "<option value=\"".$list['stage_num']."\" ".$selected.">".$list['stage_name']."</option>\n";
							if ($selected) { $L_NAME['stage_name'] = $list['stage_name']; }
						}
					}
				}
			}

			if (!$_POST['lesson_num']) { $selected = "selected"; } else { $selected = ""; }
			$lesson_num_html .= "<option value=\"\" ".$selected.">----------</option>\n";

			if ($_POST['stage_num']) {
				$sql  = "SELECT * FROM ".T_LESSON.
						" WHERE state!='1' AND stage_num='".$_POST['stage_num']."' ORDER BY list_num;";
				if ($result = $cdb->query($sql)) {
					$max3 = $cdb->num_rows($result);
					if ($max3) {
						while ($list = $cdb->fetch_assoc($result)) {
							if ($_POST['lesson_num'] == $list['lesson_num']) { $selected = "selected"; } else { $selected = ""; }
							$lesson_num_html .= "<option value=\"".$list['lesson_num']."\" ".$selected.">".$list['lesson_name']."</option>\n";
							if ($selected) { $L_NAME['lesson_name'] = $list['lesson_name']; }
						}
					}
				}
			}

			if (!$_POST['unit_num']) { $selected = "selected"; } else { $selected = ""; }
			$unit_html .= "<option value=\"\" ".$selected.">----------</option>\n";

			if ($_POST['lesson_num']) {
				$sql  = "SELECT * FROM ".T_UNIT.
						" WHERE state!='1' AND lesson_num='".$_POST['lesson_num']."' ORDER BY list_num;";
				if ($result = $cdb->query($sql)) {
					$max4 = $cdb->num_rows($result);
					if ($max4) {
						while ($list = $cdb->fetch_assoc($result)) {
							if ($_POST['unit_num'] == $list['unit_num']) { $selected = "selected"; } else { $selected = ""; }
							$unit_html .= "<option value=\"".$list['unit_num']."\" ".$selected.">".$list['unit_name']."</option>\n";
							if ($selected) { $L_NAME['unit_name'] = $list['unit_name']; }
						}
					}
				}
			}

			if (!$_POST['block_num']) { $selected = "selected"; } else { $selected = ""; }
			$block_html .= "<option value=\"\" ".$selected.">----------</option>\n";

			if ($_POST['unit_num']) {
				$sql  = "SELECT block_num, block_type, display FROM ".T_BLOCK.
						" WHERE state!='1' AND unit_num='".$_POST['unit_num']."' ORDER BY list_num;";
				if ($result = $cdb->query($sql)) {
					$max5 = $cdb->num_rows($result);
					if ($max5) {
						while ($list = $cdb->fetch_assoc($result)) {
							if ($_POST['block_num'] == $list['block_num']) {
								$selected = "selected";
							} else {
								$selected = "";
							}

							$block_name = $L_UNIT_TYPE[$list['block_type']];
							if ($list['display'] == "2") { $block_name .= "(非表示)"; }
							$block_html .= "<option value=\"".$list['block_num']."\" ".$selected.">".$block_name."</option>\n";
							if ($selected) { $L_NAME['block_name'] = $block_name; }
						}
					}
				}
			}

			$html .= "<br>\n";
			$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" name=\"menu\">\n";
			$html .= "<input type=\"hidden\" name=\"mode\" value=\"set_course\">\n";
			$html .= "<table class=\"unit_form\">\n";
			$html .= "<tr class=\"unit_form_cell\">\n";
			$html .= "<td class=\"stage_form_menu\">コース</td>\n";
			$html .= "<td class=\"stage_form_menu\">ステージ</td>\n";
			$html .= "<td class=\"stage_form_menu\">Lesson</td>\n";
			$html .= "<td class=\"stage_form_menu\">ユニット</td>\n";
// 「ブロック」から「ドリル」へ表記統一　2009/11/02 hirano
			$html .= "<td class=\"stage_form_menu\">ドリル</td>\n";
			$html .= "</tr>\n";
			$html .= "<tr class=\"stage_form_cell\">\n";
			$html .= "<td>\n";
			$html .= "<select name=\"course_num\" onchange=\"submit_course()\">\n";
			$html .= $course_num_html;
			$html .= "</select>\n";
			$html .= "</td>\n";
			$html .= "<td>\n";
			$html .= "<select name=\"stage_num\" onchange=\"submit_stage()\">\n";
			$html .= $stage_num_html;
			$html .= "</select>\n";
			$html .= "</td>\n";
			$html .= "<td><select name=\"lesson_num\" onchange=\"submit_lesson()\">\n";
			$html .= $lesson_num_html;
			$html .= "</select></td>\n";
			$html .= "<td>\n";
			$html .= "<select name=\"unit_num\" onchange=\"submit_unit()\">\n";
			$html .= $unit_html;
			$html .= "</select>\n";
			$html .= "</td>\n";
			$html .= "<td>\n";
			$html .= "<select name=\"block_num\" onchange=\"submit()\">\n";
			$html .= $block_html;
			$html .= "</select>\n";
			$html .= "</td>\n";
			$html .= "</tr>\n";
			$html .= "</table>\n";
			$html .= "</form><br>\n";
		}
		else {
			$flag = 1;
			$html = "<br>\n";
			$html .= "コースを設定してからご利用下さい。<br>\n";
		}
	}

	if ($_POST['course_num'] && $_POST['stage_num'] && $_POST['lesson_num'] && $_POST['unit_num'] && $_POST['block_num']) {
		$html .= upload_form($L_NAME);
	} elseif ($_POST['course_num'] && $_POST['stage_num'] && $_POST['lesson_num'] && $_POST['unit_num']) {
		if (!$max5) {
// 「ブロック」から「ドリル」へ表記統一　2009/11/02 hirano
			$html .= "ドリルが設定されておりません。<br>\n";
		}
		else {
// 「ブロック」から「ドリル」へ表記統一　2009/11/02 hirano
			$html .= "ドリルを選択してください。<br>\n";
		}
	} elseif ($_POST['course_num'] && $_POST['stage_num'] && $_POST['lesson_num']) {
		if (!$max4) {
			$html .= "ユニットが設定されておりません。<br>\n";
		}
		else {
			$html .= "ユニットを選択してください。<br>\n";
		}
	} elseif ($_POST['course_num'] && $_POST['stage_num']) {
		if (!$max3) {
			$html .= "Lessonが設定されておりません。<br>\n";
		}
		else {
			$html .= "Lessonを選択してください。<br>\n";
		}
	} elseif ($_POST['course_num']) {
		if (!$max2) {
			$html .= "ステージが設定されておりません。<br>\n";
		}
		else {
			$html .= "ステージを選択してください。<br>\n";
		}
	} elseif ($flag != 1) {
		$html .= "コース、ステージ、Lesson、ユニットを選択してください。<br>\n";
	}

	$msg = $MSG['msg'];
	$ERROR = $MSG['error'];
	if ($msg) {
		$html .= "<br>\n";
		$html .= $msg."<br>\n";
	}
	if ($ERROR) {
		$html .= "<br>\n";
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($ERROR);
		$html .= "</div>\n";
	}

	return $html;
}

/**
 * uploadの為にフォームの準備
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param mixed $L_NAME 使用されていません
 * @return string HTML
 */
function upload_form($L_NAME) {

	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\" enctype=\"multipart/form-data\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"upload\">\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$_POST['stage_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"".$_POST['unit_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"block_num\" value=\"".$_POST['block_num']."\">\n";
	$html .= "問題ファイル（S-JIS）を指定し問題登録ボタンを押してください。<br>\n";
	$html .= "<input type=\"file\" size=\"60\" name=\"unit_file\"><br>\n";
	$html .= "<input type=\"submit\" value=\"問題登録\">\n";
	$html .= "</form>\n";

	return $html;
}



/**
 * Upload処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array
 */
function upload() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	include("../../_www/problem_lib/problem_regist.php");

	if (!$_POST['course_num']) { $ERROR[] = "コースが確認できません。"; }
	if (!$_POST['stage_num']) { $ERROR[] = "ステージが確認できません。"; }
	if (!$_POST['lesson_num']) { $ERROR[] = "ユニットが確認できません。"; }
	if (!$_POST['unit_num']) { $ERROR[] = "ユニットが確認できません。"; }
// 「ブロック」から「ドリル」へ表記統一　2009/11/02 hirano
	if (!$_POST['block_num']) { $ERROR[] = "ドリルが確認できません。"; }

	$file_name = $_FILES['unit_file']['name'];
	$file_tmp_name = $_FILES['unit_file']['tmp_name'];
	$file_error = $_FILES['unit_file']['error'];
	if (!$file_tmp_name) {
		$ERROR[] = "問題ファイルが指定されておりません。";
	} elseif (!eregi("(.txt)$",$file_name)) {
		$ERROR[] = "ファイルの拡張子が不正です。";
	} elseif ($file_error == 1) {
		$ERROR[] = "アップロードできるファイルの容量が設定範囲を超えてます。サーバー管理者へ相談してください。";
	} elseif ($file_error == 3) {
		$ERROR[] = "問題ファイルの一部分のみしかアップロードされませんでした。";
	} elseif ($file_error == 4) {
		$ERROR[] = "問題ファイルがアップロードされませんでした。";
	}
	if ($ERROR) {
		if ($file_tmp_name && file_exists($file_tmp_name)) { unlink($file_tmp_name); }
		$MSG['error'] = $ERROR;
		return $MSG;
	}

	//	学習タイプ読み込み
	$sql  = "SELECT block_type FROM ".T_BLOCK.
			" WHERE block_num='".$_POST['block_num']."' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		define("block_type",$list['block_type']);
	}

	//	登録問題番号取得
	$sql  = "SELECT * FROM ".T_PROBLEM.
			" WHERE course_num='".$_POST['course_num']."' AND block_num='".$_POST['block_num']."'".
			" AND state!='1';";
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
		if ($max) {
			while ($list = $cdb->fetch_assoc($result)) {
				$problem_num_ = $list['problem_num'];
				$display_problem_num_ = $list['display_problem_num'];
				$sub_display_problem_num_ = $list['sub_display_problem_num'];
				$L_NUM[$display_problem_num_][$sub_display_problem_num_] = $problem_num_;
			}
		}
	}

	//add okabe start 2018/08/27  理科 問題表示文字調整「，＋ー」
	$tmpx_course_num = intval($_POST['course_num']);
	$sql  = "SELECT write_type FROM ".T_COURSE." WHERE course_num='".$tmpx_course_num."' LIMIT 1;";
	$tmpx_write_type = 0;
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$tmpx_write_type = $list['write_type'];
	}
	//add okabe end 2018/08/27  理科 問題表示文字調整「，＋ー」

	$LIST = file($file_tmp_name);
	if ($LIST) {
		$i = 1;
		foreach ($LIST AS $VAL) {
			unset($LINE);
			$VAL = trim($VAL);
			if (!$VAL || !ereg("\t",$VAL)) { continue; }
			list(
				$LINE['display_problem_num'],$LINE['problem_type'],$LINE['sub_display_problem_num'],$LINE['form_type'],$LINE['question'],
				$LINE['problem'],$LINE['voice_data'],$LINE['hint'],$LINE['explanation'],$LINE['answer_time'],
				$LINE['parameter'],$LINE['set_difficulty'],$LINE['hint_number'],$LINE['correct_number'],$LINE['clear_number'],
				$LINE['first_problem'],$LINE['latter_problem'],$LINE['selection_words'],$LINE['correct'],$LINE['option1'],
//				$LINE['option2'],$LINE['option3'],$LINE['option4'],$LINE['option5'],$LINE['unit_id']	//	add koike $LINE['unit_id'] 2012/07/04
				$LINE['option2'],$LINE['option3'],$LINE['option4'],$LINE['option5'],
				$LINE['unit_id'],$LINE['english_word_problem'],							//	add oda $LINE['english_word_problem'] 2012/10/01
				$LINE['problem_tegaki_flg'], //add kimura 2018/10/19 漢字学習コンテンツ_書写ドリル対応
				$LINE['error_msg'],$LINE['update_time'],$LINE['display'],
				$LINE['state'],$LINE['sentence_flag']									//	add oda $LINE['sentence_flag'] 2013/01/09
			) = explode("\t",$VAL);
//echo mb_convert_encoding($LINE['question'], "UTF-8", "SJIS")."\n<br>";
			if ($LINE) {
				foreach ($LINE AS $key => $val) {
					if ($val) {
						$val = ereg_replace("\"","&quot;",$val);	// add oda 2014/12/18 ダブルクォーテーションが消えてしまうので、消える前に変換する

						//	del 2014/12/05 yoshizawa 課題要望一覧No.394対応 下に新規で作成
						//$val = replace_encode_sjis($val);
						//$val = mb_convert_encoding($val,"UTF-8","sjis-win");
						//----------------------------------------------------------------
						//	add 2014/12/05 yoshizawa 課題要望一覧No.394対応
						//	データの文字コードがUTF-8だったら変換処理をしない
						$code = judgeCharacterCode ( $val );
						if ( $code != 'UTF-8' ) {

							// add 2015/03/30 yoshizawa 課題要望一覧No.427 問題インポートで文字化け対応
							// 「集合A」や「集合B」など、1バイト文字と2バイト文字が複合している場合に
							// 特殊文字が誤った変換をする。対策として半角英字を全角に変換する。
							// ※オプション「A」で英数字を変換すると「<」と「>」まで全角にしてしまうため
							// 「R」で英字のみ全角にする。
							//$val = mb_convert_kana($val,"R","sjis-win");	//del okabe 2018/08/22 理科問題 全角文字扱い（特定のカラムのみは変換時の文字化け防止のための処置
							//--------------------------------------------------------------------------

							//add start okabe 2018/08/22  理科問題 全角文字扱い（特定のカラムのみは変換時の文字化け防止のために一旦、文字間に制御コード垂直タブ0x0bを入れる）
							if (intval($tmpx_write_type) != 15) {
								//理科以外は従来と同じ
								$val = mb_convert_kana($val,"R","sjis-win");

							} else {
								//理科問題の場合
								if ($key !== "question" && $key !== "problem" && $key !== "hint" 
										&& $key !== "explanation" && $key !== "first_problem" && $key !== "latter_problem") {
									//特定のカラム以外は対処せず従来同様に処理させる
									$val = mb_convert_kana($val,"R","sjis-win");

								} else {
									//以下は、文字間へ制御コード0x0bを入れる処理
									$tmp_moji_ary = array();
									$tmp_fail_safe = 10000;
									$tmp_ps = 0;
									while($tmp_fail_safe > 0 && $tmp_ps < mb_strlen($val, "sjis-win")) {
										$tmp_moji_ary[] = mb_substr($val, $tmp_ps, 1, "sjis-win");
										$tmp_ps++;
										$tmp_fail_safe = $tmp_fail_safe - 1;
									}
									$val = implode("\x0b\x0b", $tmp_moji_ary);	//制御コード垂直タブ0x0bを文字間に2個入れて文字列を復元する
								}
							}
							//add end okabe 2018/08/22

							$val = replace_encode_sjis($val);
							$val = mb_convert_encoding($val,"UTF-8","sjis-win");

							//add start okabe 2018/08/22  理科問題 全角文字扱い（特定のカラムのみは変換時の文字化け防止のために加えた制御コードを消す）
							if (intval($tmpx_write_type) == 15) {
								if ($key === "question" || $key === "problem" || $key === "hint" 
										|| $key === "explanation" || $key === "first_problem" || $key === "latter_problem") {
									//文字間に加えた2個の制御コード垂直タブ0x0bを消す
									$tmp_val = explode("\x0b\x0b", $val);
									$val = implode("", $tmp_val);
								}
							}
							//add end okabe 2018/08/22

						}
						//	add 2015/01/09 yoshizawa 課題要望一覧No.400対応
						//	sjisファイルをインポートしても2バイト文字はutf-8で扱われる
						else {
							//	記号は特殊文字に変換します
							$val = replace_encode($val);

						}
						//--------------------------------------------------
						//$val = ereg_replace("\"","&quot;",$val);	// del oda 2014/12/18 上に移動
						//$LINE[$key] = replace_word($val);		//del okabe 2018/08/22 理科問題 全角文字扱い（特定のカラムのみは変換時の文字化け防止のための処置

						//add start okabe 2018/08/22  理科問題 全角文字扱い（特定のカラムのみは変換時の文字化け防止のために加えた制御コードを消す）
						if (intval($tmpx_write_type) == 15) {
							//理科の科目で、特定のカラムの場合は全角への変換をスキップする
							if ($key === "question" || $key === "problem" || $key === "hint" 
									|| $key === "explanation" || $key === "first_problem" || $key === "latter_problem") {
									$LINE[$key] = replace_word_extok($val, 0);	//全角変換をスキップして処理する

							} else {
								$LINE[$key] = replace_word($val);	//従来の処理
							}

						} else {
							$LINE[$key] = replace_word($val);	//従来の処理
						}
						//add end okabe 2018/08/22

					}

				}
			}
			$LINE['course_num'] = $_POST['course_num'];
			$LINE['block_num'] = $_POST['block_num'];
			if (!$LINE['sentence_flag']) { $LINE['sentence_flag'] = "0"; }		//	add oda 2012/12/26
			unset($C_ERROR);
			unset($S_ERROR);

			$regist_problem = new regist_problem();
			$C_ERROR = $regist_problem->check_data($LINE);

			//	エラー記録処理
			unset($error_msg);
			unset($LINE['error_msg']);
			unset($LINE['display']);
			if ($C_ERROR) {
				foreach ($C_ERROR AS $key => $val) {
					$LINE['error_msg'] .= $key."::".$val."\n";
				}
				$LINE['display'] = 2;
			} else {
				$LINE['display'] = 1;
			}

			$display_problem_num_ = $LINE['display_problem_num'];
			$sub_display_problem_num_ = $LINE['sub_display_problem_num'];
			if (!$sub_display_problem_num_) { $sub_display_problem_num_ = "0"; }
			$problem_num_ = $L_NUM[$display_problem_num_][$sub_display_problem_num_];
			$regist_problem->set_reggist_type($problem_num_);
			$S_ERROR = $regist_problem->reggist_data($LINE);
			if ($S_ERROR) {
				foreach ($S_ERROR AS $key => $val) {
					if (!$val) { continue; }
					$ERROR[] = $val;
				}
			}

			if ($C_ERROR) {
				$false++;
				$ERROR_NUM[] = $i;
			} elseif ($S_ERROR) {
				$system_error++;
				$ERROR_NUM[] = $i;
			} else {
				$success++;
			}
			$i++;
		}
	}

	if ($system_error) {
		$MSG['msg'] = "登録失敗：".$system_error."問。<br>";
	}
	if ($success) {
		$MSG['msg'] .= "エラー無し：".$success."問、<br>";
	}
	if ($false) {
		$MSG['msg'] .= "エラー有り：".$false."問、<br>";
	}
	if ($MSG['msg']) {
		$MSG['msg'] .= "登録しました。<br>";
	}
	if ($ERROR_NUM) {
		foreach ($ERROR_NUM AS $val) {
			if($error_num) { $error_num .= ","; }
			$error_num .= $val."行目";
		}
		if ($error_num) {
			$MSG['msg'] .= "エラー行数：".$error_num."<Br>\n";
		}
	}


	if ($ERROR) {
		$MSG['error'] = $ERROR;
	}
	if ($file_tmp_name && file_exists($file_tmp_name)) { unlink($file_tmp_name); }
	return $MSG;
}


/**
 * uploadの為にフォームの準備
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param mixed $L_NAME 使用されていません
 * @return string HTML
 */
function upload_html($msg) {

	$html .= "<br>\n";
	$html .= $msg."<br>\n";

	$html .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"set_course\" />\n";
	$html .= "<input type=\"hidden\" name=\"course_num\" value=\"".$_POST['course_num']."\" />\n";
	$html .= "<input type=\"hidden\" name=\"stage_num\" value=\"".$_POST['stage_num']."\" />\n";
	$html .= "<input type=\"hidden\" name=\"lesson_num\" value=\"".$_POST['lesson_num']."\" />\n";
	$html .= "<input type=\"hidden\" name=\"unit_num\" value=\"".$_POST['unit_num']."\" />\n";
	$html .= "<input type=\"hidden\" name=\"block_num\" value=\"".$_POST['block_num']."\" />\n";
	$html .= "<input type=\"submit\" value=\"戻る\" />\n";
	$html .= "</form>\n";

	return $html;
}
?>
