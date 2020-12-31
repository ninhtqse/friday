<?
/**
 * ベンチャー・リンク　すらら
 *
 * 判定テスト入力項目チェック、他サブルーチン
 *
 * 履歴
 * 2013/09/17 初期設定
 *
 * @author Azet
 */

// 	okabe

/**
 * 判定テスト　テスト問題 各入力項目チェックルーチン
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 *   入力フォーム または TSVデータ入力の際、データ１件の内容の正当性チェック
 *  IN: $DATA_SET[]  １件分データ、制御値と入力データ
 *  戻り値: array(データ配列、エラーメッセージ配列)
 *                データ配列とは、このルーチン内を呼ぶ際に渡した $DATA_SET[] をすべて返しますが、
 *                check_mode、store_mode により、半角英数字化およびtrim処理を行った結果を
 *                返させることが出来ます。
 * @author Azet
 * @param array $DATA_SET (未使用)
 * @param array $ERROR
 * @return array
 */
function ht_test_check($DATA_SET, $ERROR) {

//print_r($DATA_SET);
//echo "<br/><br/>";
	//制御値
	// $DATA_SET['__info']['call_mode']		// 0=入力フォーム、1:csv入力
	// $DATA_SET['__info']['line_num']		// 行番号...エラーメッセージに付記するため
	// $DATA_SET['__info']['check_mode']	// チェック時にデータ型を自動調整(半角英数字化、trim処理)するかのスイッチ
	//												0:自動調整しない、1:する
	// $DATA_SET['__info']['store_mode']	// データ自動調整結果を、$DATA_SET['data']['パラメータ名']へ再格納するかのスイッチ
	//												0:再格納しない、1:する
	// $DATA_SET['__info']['result']		// チェック結果を返す エラーがあれば1、エラー無しなら0
	//
	//入力データ群 (form_type 含む)
	// $DATA_SET['data']['パラメータ名']

	//入力パラメータ
	// display_problem_num  all must
	// problem_type  all
	// form_type ...フォームタイプ判別
	// question 1,2,3,4,5,8
	// problem  option
	// voice_data  all
	// hint  option
	// explanation  all
	// answer_time  option  -> standard_time を使用
	// hint_number  option
	// first_problem  4,5
	// latter_problem  4,5
	// selection_words  2複数,3複数,4,8,10,11
	// correct  all
	// option1
	// option2
	// option3
	// option4
	// option5
	// display  all
	// state  all

	//制御情報
	$line_num = "";
	if ($DATA_SET['__info']['call_mode'] == "1") {
		$line_num = line_number_format($DATA_SET['__info']['line_num']);	//行番号
	}
	$check_mode = $DATA_SET['__info']['check_mode'];	//データ型を自動調整(1:する)
	$store_mode = $DATA_SET['__info']['store_mode'];	//自動調整結果の再格納(1:する)
	$DATA_SET['__info']['result'] = 0;


	//共通項目のチェック
	$form_type = $DATA_SET['data']['form_type'];	//問題形式番号 1,2,3,4,5,8,10,11,13
	if ($check_mode) {
		$form_type = mb_convert_kana($form_type,"asKV", "UTF-8");
		$form_type = trim($form_type);
	}
	//add start okabe 2013/09/11
	if (strlen($form_type) == 0 || preg_match("/[^0-9]/",$form_type)) {
		$ERROR[] = $line_num."出題形式が不正です(数値ではありません)。";
		$DATA_SET['__info']['result'] = 1;
	} else {
		//if ($form_type < 1 || $form_type > 12) {
		if ($form_type != 1 && $form_type != 2 && $form_type != 3 && $form_type != 4 && $form_type != 5 && $form_type != 8 && $form_type != 10 && $form_type != 11 && $form_type != 13) { // upd hasegawa 2016/06/02 作図ツール
			// add start hasegawa 2018/03/23 百マス計算
			if ($form_type == 14) {
				$ERROR[] = $line_num."form_type14(百マス計算)はテストには登録できません。";
			//add start kimura 2018/10/04 漢字学習コンテンツ_書写ドリル対応
			}else if($form_type == 15){
				$ERROR[] = $line_num."form_type15(書写)はテストには登録できません。";
			//add end   kimura 2018/10/04 漢字学習コンテンツ_書写ドリル対応
			//add start kimura 2018/11/26 すらら英単語 _admin
			}else if($form_type == 16){
				$ERROR[] = $line_num."form_type16(読み)はテストには登録できません。";
			}else if($form_type == 17){
				$ERROR[] = $line_num."form_type17(書き)はテストには登録できません。";
			//add end   kimura 2018/11/26 すらら英単語 _admin
			} else {
			// add end hasegawa 2018/03/23
				$ERROR[] = $line_num."出題形式が不正です。";
			} // add hasegawa 2018/03/23
			$DATA_SET['__info']['result'] = 1;
		}
	}
	//add end okabe 2013/09/11

	//display_problem_num  all must
	$display_problem_num = $DATA_SET['data']['display_problem_num'];	//表示順番号
	if ($check_mode) {
		$display_problem_num = mb_convert_kana($display_problem_num,"asKV", "UTF-8");
		$display_problem_num = trim($display_problem_num);
	}
	//if (strlen($display_problem_num) == 0 || preg_match("/[^0-9]/",$display_problem_num)) {	//del okabe 2013/09/09
	if (strlen($display_problem_num) == 0 || $display_problem_num == 0 || preg_match("/[^0-9]/",$display_problem_num)) {	//edit okabe 2013/09/09
		$ERROR[] = $line_num."表示順番号が不正です。";
		$DATA_SET['__info']['result'] = 1;
	}
	//表示順番号は、１つのテスト内でユニークであること...


	//problem_type  all
	$problem_type = $DATA_SET['data']['problem_type'];	//問題タイプ
	if ($check_mode) {
		$problem_type = mb_convert_kana($problem_type,"asKV", "UTF-8");
		$problem_type = trim($problem_type);
	}
	if (strlen($problem_type) == 0 || preg_match("/[^0-9]/",$problem_type)) {
		$ERROR[] = $line_num."問題タイプが不正です(数値ではありません)。";
		$DATA_SET['__info']['result'] = 1;
	} else {
		if ($problem_type < 0 || $problem_type > 3) {
			$ERROR[] = $line_num."問題タイプが不正です(0～3の値ではありません)。";
			$DATA_SET['__info']['result'] = 1;
		}
	}

	//explanation  all
	$explanation = $DATA_SET['data']['explanation'];	//解説文
	//if ($check_mode) {
	//	$check_mode = trim($check_mode);
	//}
	//if (strlen($explanation) == 0) {
	//	$ERROR[] = $line_num."解説文が入力されていません。";
	//	$DATA_SET['__info']['result'] = 1;
	//}

	//display  all
	$display = $DATA_SET['data']['display'];	//display
	if ($check_mode) {
		$display = mb_convert_kana($display,"asKV", "UTF-8");
		$display = trim($display);
	}
	if (strlen($display) == 0 || preg_match("/[^0-9]/",$display) || $display < 1 || $display > 2) {		//edit 2013/09/17
		$ERROR[] = $line_num."表示/非表示が選択されていません。";
		$DATA_SET['__info']['result'] = 1;
	}

	//state  all
	$state = $DATA_SET['data']['state'];	//state
	if ($check_mode) {
		$state = mb_convert_kana($state,"asKV", "UTF-8");
		$state = trim($state);
	}
	if (strlen($state) == 0 || preg_match("/[^0-9]/",$state) || $state < 0 || $state > 1) {
		$ERROR[] = $line_num."state の指定が正しくありません。";
		$DATA_SET['__info']['result'] = 1;
	}

	////voice_data  all
	$voice_data = $DATA_SET['data']['voice_data'];	//音声データ
	if ($check_mode) {
		$voice_data = trim($voice_data);
	}
	//if (strlen($voice_data) == 0 ) {
	//	$ERROR[] = $line_num."音声データが未入力です。";
	//}



	//他データの取り出し

	//correct  all
	$correct = $DATA_SET['data']['correct'];	//正解
	if ($check_mode) {
		$correct = trim($correct);
	}

	////answer_time  option  -> standard_time を使用
	$standard_time = $DATA_SET['data']['standard_time'];	//解答時間
	if ($check_mode) {
		$standard_time = mb_convert_kana($standard_time,"asKV", "UTF-8");
		$standard_time = trim($standard_time);
	}
	if ($standard_time) {
		if (preg_match("/[^0-9]/",$standard_time)) {
			$ERROR[] = $line_num."回答目安時間は半角数字で入力してください。";
			$DATA_SET['__info']['result'] = 1;
		}
	}

	//selection_words  2複数,3複数,4,8,10,11
	$selection_words = $DATA_SET['data']['selection_words'];	//selection_words
	if ($check_mode) {
		$selection_words = trim($selection_words);
	}

	//option1
	$option1 = $DATA_SET['data']['option1'];	//option1
	if ($check_mode) {
		$option1 = trim($option1);
	}

	//option2
	$option2 = $DATA_SET['data']['option2'];	//option2
	if ($check_mode) {
		$option2 = trim($option2);
	}

	//option3
	$option3 = $DATA_SET['data']['option3'];	//option3
	if ($check_mode) {
		$option3 = trim($option3);
	}

	//option4
	$option4 = $DATA_SET['data']['option4'];	//option4
	if ($check_mode) {
		$option4 = trim($option4);
	}

	//option5
	$option5 = $DATA_SET['data']['option5'];	//option5
	if ($check_mode) {
		$option5 = trim($option5);
	}

	//first_problem  4,5
	$first_problem = $DATA_SET['data']['first_problem'];
	if ($check_mode) {
		$first_problem = trim($first_problem);
	}

	//latter_problem  4,5
	$latter_problem = $DATA_SET['data']['latter_problem'];
	if ($check_mode) {
		$latter_problem = trim($latter_problem);
	}

	//question  must:1,2,3,4,5,8
	$question = $DATA_SET['data']['question'];
	if ($check_mode) {
		$question = trim($question);
	}

	//problem  option
	$problem = $DATA_SET['data']['problem'];
	if ($check_mode) {
		$problem = trim($problem);
	}

	//hint  option
	$hint = $DATA_SET['data']['hint'];
	if ($check_mode) {
		$hint = trim($hint);
	}

	//hint_number  option
	$hint_number = $DATA_SET['data']['hint_number'];
	if ($check_mode) {
		$hint_number = mb_convert_kana($hint_number,"asKV", "UTF-8");
		$hint_number = trim($hint_number);
	}
	if ($hint_number) {
		if (preg_match("/[^0-9]/",$hint_number)) {
			$ERROR[] = $line_num."ヒント表示回数は半角数字で入力してください。";
			$DATA_SET['__info']['result'] = 1;
		}
	}



	//フォームタイプ別の項目チェック
	$array_replace = new array_replace();

	if ($form_type == 1) {		//フォームタイプ１

		if (strlen($question) == "0") {
			$ERROR[] = $line_num."質問文が入力されていません。";
			$DATA_SET['__info']['result'] = 1;
		}

		//if ($selection_words && $selection_words === "0") {
		if ($selection_words) {
			$max_column = $selection_words_num = $array_replace->set_line($selection_words);
		}

		if (!$correct && $correct !== "0") {
			$ERROR[] = $line_num."正解が確認できません。";
			$DATA_SET['__info']['result'] = 1;
		} else {
			$correct_num = $array_replace->set_line($correct);
			$correct = $array_replace->replace_line();
		}

		if (!$option1 && $option1 !== "0") {
			$ERROR[] = $line_num."選択語句が確認できません。";
			$DATA_SET['__info']['result'] = 1;
		} else {
			$option1_num = $array_replace->set_line($option1);
			$option1 = $array_replace->replace_line();

			$L_CORRECT = explode("\n",$correct);
			$L_OPTION1 = explode("\n",$option1);
			if ($L_OPTION1) {
				foreach($L_OPTION1 as $key => $val) {
					foreach (explode("&lt;&gt;",$val) as $word) { $L_ANS[] = trim($word); }
					$hit = array_search($L_CORRECT[$key],$L_ANS);
					if($hit === FALSE) {
						$ERROR[] = $line_num."選択語句内に正解が含まれておりません。";
						$DATA_SET['__info']['result'] = 1;
						break;
					}
				}
			}
		}

		//if ($option2 == "") { $option2 = "0"; }
		if (strlen(trim($option2)) == 0) {
			$ERROR[] = $line_num."シャッフル情報が入力されていません。";
			$DATA_SET['__info']['result'] = 1;
		}
		if ($check_mode) {
			$option2 = mb_convert_kana($option2, "asKV", "UTF-8");
			$option2 = trim($option2);
		}
		if (ereg("[^0-1]",$option2)) {
			$ERROR[] = $line_num."シャッフル情報が不正です。";
			$DATA_SET['__info']['result'] = 1;
		}

		//if ($option3 == "") { $option3 = "0"; }
		if ($check_mode) {
			$option3 = mb_convert_kana($option3, "asKV", "UTF-8");
			$option3 = trim($option3);
		}
		if (ereg("[^0-9]",$option3) || $option3 == 1) {
			$ERROR[] = $line_num."選択項目数が不正です。";
			$DATA_SET['__info']['result'] = 1;
		}

		//if ($max_column > 1) {
		if ($max_column > 0) {
			if ($max_column != $selection_words_num || $max_column != $correct_num || $max_column != $option1_num) {
				$ERROR[] = $line_num."出題項目({$selection_words_num})、正解({$correct_num})、選択語句({$option1_num})の数が一致しません。";
				$DATA_SET['__info']['result'] = 1;
			}
		}

	} elseif ($form_type == 2) {		//フォームタイプ２

		if (strlen($question) == "0") {
			$ERROR[] = $line_num."質問文が入力されていません。";
			$DATA_SET['__info']['result'] = 1;
		}

		if ($selection_words) {
			$max_column = $selection_words_num = $array_replace->set_line($selection_words );
		}

		if (!$correct && $correct !== "0") {
			$ERROR[] = $line_num."正解が確認できません。";
			$DATA_SET['__info']['result'] = 1;
		} else {
			$correct_num = $array_replace->set_line($correct);
			$correct = $array_replace->replace_line();
		}

		if (!$option1 && $option1 !== "0") {
			$ERROR[] = $line_num."選択語句が確認できません。";
			$DATA_SET['__info']['result'] = 1;
		} else {
			$option1_num = $array_replace->set_line($option1);
			$option1 = $array_replace->replace_line();

			$L_CORRECT = explode("\n",$correct);
			$L_OPTION1 = explode("\n",$option1);
			if ($L_CORRECT) {
				foreach($L_CORRECT as $key => $val) {
					foreach (explode("&lt;&gt;",$val) as $word) { $L_ANS[] = trim($word); }
				}
			}
			if ($L_OPTION1) {
				foreach($L_OPTION1 as $key => $val) {
					foreach (explode("&lt;&gt;",$val) as $word) { $L_ANS1[] = trim($word); }
					$hit = array_search($L_ANS[$key],$L_ANS1);
					if($hit === FALSE) {
						$ERROR[] = $line_num."選択語句内に正解が含まれておりません。";
						$DATA_SET['__info']['result'] = 1;
						break;
					}
				}
			}
		}

		//if ($option2 == "") { $option2 ="0"; }
		if (strlen(trim($option2)) == 0) {
			$ERROR[] = $line_num."シャッフル情報が入力されていません。";
			$DATA_SET['__info']['result'] = 1;
		}
		if ($check_mode) {
			$option2 = mb_convert_kana($option2, "asKV", "UTF-8");
			$option2 = trim($option2);
		}
		if (ereg("[^0-1]",$option2)) {
			$ERROR[] = $line_num."シャッフル情報が不正です。";
			$DATA_SET['__info']['result'] = 1;
		}

		//if ($max_column > 1) {
		if ($max_column > 0) {
			if ($max_column != $selection_words_num || $max_column != $correct_num || $max_column != $option1_num) {
				$ERROR[] = $line_num."出題項目({$selection_words_num})、正解({$correct_num})、選択語句({$option1_num})の数が一致しません。";
				$DATA_SET['__info']['result'] = 1;
			}
		}

	} elseif ($form_type == 3) {		//フォームタイプ３
		if (strlen($question) == "0") {
			$ERROR[] = $line_num."質問文が入力されていません。";
			$DATA_SET['__info']['result'] = 1;
		}

		if (!$correct && $correct !== "0" && !$option1 && $option1 !== "0") {
			$ERROR[] = $line_num."正解、又はBUD正解が確認できません。";
			$DATA_SET['__info']['result'] = 1;
			//$ERROR[] = "正解、又はBUD正解が確認できません。";
		}
		//add start okabe 2013/09/11
		if ($check_mode) {
			$option2 = mb_convert_kana($option2, "asKV", "UTF-8");
			$option2 = trim($option2);
		}

/*		// del hasegawa 2016/10/26 入力フォームサイズ指定項目追加
		if (preg_match("/[^0-9]/",$option2)) {
			$ERROR[] = $line_num."解答欄行数が不正です。";
			$DATA_SET['__info']['result'] = 1;
		}
*/
		// add satrt hasegawa 2016/10/26 入力フォームサイズ指定項目追加
		if($option2) {
			$input_row = "";
			$input_size = "";

			if (preg_match("/&lt;&gt;/",$option2)) {
				list($input_row,$input_size) = explode("&lt;&gt;",$option2);
			} else {
				$input_row = $option2;
			}
			if (preg_match("{[^0-9/]}",$input_row)) {
				$ERROR[] = $line_num."解答欄行数が不正です。";
				$DATA_SET['__info']['result'] = 1;
			}

			$input_size_err_flg = 0;
			if (preg_match("{[^0-9/]}",$input_size)) {
				$input_size_err_flg = 1;
			} else {
				$L_INPUT_SIZE = array();
				$L_INPUT_SIZE = explode('//',$input_size);
				if(is_array($L_INPUT_SIZE)) {
					foreach($L_INPUT_SIZE as $val) {
						if ($val !="" && !($val > 0 && $val <= 40)) {
							$input_size_err_flg = 1;
						}
					}
				}
			}
			if($input_size_err_flg == 1) {
				$ERROR[] = $line_num."解答欄サイズが不正です。";
				$DATA_SET['__info']['result'] = 1;
			}
		}
		// add end hasegawa 2016/10/26 入力フォームサイズ指定項目追加

		// add start hasegawa 2018/05/15 手書きV2対応
		if ($option4) {
			$use_v1 = false;
			$use_v2 = false;

			$check_param = htmlspecialchars_decode($option4);
			$param = explode(';;', $check_param);
			if (is_array($param)) {
				foreach ($param as $key => $val) {
					// 手書きV1
					if (preg_match('/^Hand[1-9]/',$val)) {
						$use_v1 = true;

					// 手書きV2 通常
					} elseif (preg_match('/^HandV2N/',$val)) {
						$use_v2 = true;

					// 手書きV2 英語
					} elseif (preg_match('/^HandV2E/',$val)) {
						$use_v2 = true;
					}
				}

				if ($use_v1 && $use_v2) {
					$ERROR[] = "「手書き認識設定」が不正です。";
					$DATA_SET['__info']['result'] = 1;
				}
			}
		}
		// add end hasegawa 2018/05/15

		//add end okabe 2013/09/11

	} elseif ($form_type == 4) {		//フォームタイプ４

		if (strlen($question) == "0") {
			$ERROR[] = $line_num."質問文が入力されていません。";
			$DATA_SET['__info']['result'] = 1;
		}

		if (!$selection_words && $selection_words !== "0") {
			$ERROR[] = $line_num."問題テキストが確認できません。";
			$DATA_SET['__info']['result'] = 1;
		}

		if (!$correct && $correct !== "0" && !$option1 && $option1 !== "0") {
			$ERROR[] = $line_num."正解、又はBUD正解が確認できません。";
			$DATA_SET['__info']['result'] = 1;
			//$ERROR[] = "正解、又はBUD正解が確認できません。";
		}

		//if ($option2 == "") { $option2 = "0"; }
		if ($check_mode) {
			$option2 = mb_convert_kana($option2, "asKV", "UTF-8");
			$option2 = trim($option2);
		}
		include_once("../../_www/problem_lib/space_checker.php");
		if (preg_match("/[^0-9]/",$option2) && strlen($option2) != 0 || $option2 > space_Checker::space_Decision($selection_words)) {
			$ERROR[] = $line_num."空白数が不正です。";
			$DATA_SET['__info']['result'] = 1;
		} elseif ($option2 < 0) {
			$ERROR[] = $line_num."空白数が不正です。";
			$DATA_SET['__info']['result'] = 1;
		} //upd 2017/04/10 yamaguchi 空白数の入力制限

		// add start hasegawa 2018/05/15 手書きV2対応
		if ($option4) {
			$use_v1 = false;
			$use_v2 = false;

			$check_param = htmlspecialchars_decode($option4);
			$param = explode(';;', $check_param);
			if (is_array($param)) {
				foreach ($param as $key => $val) {
					// 手書きV1
					if (preg_match('/^Hand[1-9]/',$val)) {
						$use_v1 = true;

					// 手書きV2 通常
					} elseif (preg_match('/^HandV2N/',$val)) {
						$use_v2 = true;

					// 手書きV2 英語
					} elseif (preg_match('/^HandV2E/',$val)) {
						$use_v2 = true;
					}
				}

				if ($use_v1 && $use_v2) {
					$ERROR[] = "「手書き認識設定」が不正です。";
					$DATA_SET['__info']['result'] = 1;
				}
			}
		}
		// add end hasegawa 2018/05/15

	} elseif ($form_type == 5) {		//フォームタイプ５

		if (strlen($question) == "0") {
			$ERROR[] = $line_num."質問文が入力されていません。";
			$DATA_SET['__info']['result'] = 1;
		}

		if (!$correct && $correct !== "0") {
			$ERROR[] = $line_num."正解が確認できません。";
			$DATA_SET['__info']['result'] = 1;
		}

		//if ($option1 == "") { $option1 = "1"; }
		if (strlen(trim($option1)) == 0) {
			$ERROR[] = $line_num."解答ライン数が入力されていません。";
			$DATA_SET['__info']['result'] = 1;
		}
		if ($check_mode) {
			$option1 = mb_convert_kana($option1, "asKV", "UTF-8");
			$option1 = trim($option1);
		}
		if (ereg("[^0-9]",$option1)) {
			$ERROR[] = $line_num."解答ライン数が不正です。";
			$DATA_SET['__info']['result'] = 1;
		} elseif ($option1 < 1) {
			$ERROR[] = $line_num."解答ライン数が不正です。";
			$DATA_SET['__info']['result'] = 1;
		}

	} elseif ($form_type == 8) {		//フォームタイプ８

		if (strlen($question) == "0") {
			$ERROR[] = $line_num."質問文が入力されていません。";
			$DATA_SET['__info']['result'] = 1;
		}

		if (!$selection_words && $selection_words !== "0") {
			$ERROR[] = $line_num."問題テキストが確認できません。";
			$DATA_SET['__info']['result'] = 1;
		}

		if (!$correct && $correct !== "0") {
			$ERROR[] = $line_num."正解が確認できません。";
			$DATA_SET['__info']['result'] = 1;
		}

	} elseif ($form_type == 10) {		//フォームタイプ１０
		if (!$selection_words && $selection_words !== "0") {
			$ERROR[] = $line_num."問題テキストが確認できません。";
			$DATA_SET['__info']['result'] = 1;
		}

		if (!$correct && $correct !== "0" && !$option1 && $option1 !== "0") {
			$ERROR[] = $line_num."正解、又はBUD正解が確認できません。";
			$DATA_SET['__info']['result'] = 1;
			//$ERROR[] = "正解、又はBUD正解が確認できません。";
		}

		// add start hasegawa 2018/05/15 手書きV2対応
		if ($option4) {
			$use_v1 = false;
			$use_v2 = false;

			$check_param = htmlspecialchars_decode($option4);
			$param = explode(';;', $check_param);
			if (is_array($param)) {
				foreach ($param as $key => $val) {
					// 手書きV1
					if (preg_match('/^Hand[1-9]/',$val)) {
						$use_v1 = true;

					// 手書きV2 通常
					} elseif (preg_match('/^HandV2N/',$val)) {
						$use_v2 = true;

					// 手書きV2 英語
					} elseif (preg_match('/^HandV2E/',$val)) {
						$use_v2 = true;
					}
				}

				if ($use_v1 && $use_v2) {
					$ERROR[] = "「手書き認識設定」が不正です。";
					$DATA_SET['__info']['result'] = 1;
				}
			}
		}
		// add end hasegawa 2018/05/15

	} elseif ($form_type == 11) {		//フォームタイプ１１
		if (!$selection_words && $selection_words !== "0") {
			$ERROR[] = $line_num."問題テキストが確認できません。";
			$DATA_SET['__info']['result'] = 1;
		}

		if (!$correct && $correct !== "0") {
			$ERROR[] = $line_num."正解が確認できません。";
			$DATA_SET['__info']['result'] = 1;
		}
	// add start hasegawa 2016/06/02 作図ツール
	} elseif ($form_type == 13) {		//フォームタイプ13
		if (!$selection_words && $selection_words !== "0") {
			$ERROR[] = $line_num."問題テキストが確認できません。";
			$DATA_SET['__info']['result'] = 1;
		}

		if (!$correct && $correct !== "0") {
			$ERROR[] = $line_num."正解が確認できません。";
			$DATA_SET['__info']['result'] = 1;
		}

		if (!$option1) {
			$ERROR[] = $line_num."問題種類が確認できません。";
			$DATA_SET['__info']['result'] = 1;
		}

		if (!$option2) {
			$ERROR[] = $line_num."作図問題パラメータが確認できません。";
			$DATA_SET['__info']['result'] = 1;
		}
	// add end hasegawa 2016/06/02
	}

	if ($store_mode) {
		//データの再格納
		$DATA_SET['data']['display_problem_num'] = $display_problem_num;
		$DATA_SET['data']['problem_type'] = $problem_type;
		$DATA_SET['data']['form_type']  = $form_type;
		$DATA_SET['data']['question'] = $question;
		$DATA_SET['data']['problem'] = $problem;
		$DATA_SET['data']['voice_data']  = $voice_data;
		$DATA_SET['data']['hint'] = $hint;
		$DATA_SET['data']['explanation'] = $explanation;
		//$DATA_SET['data']['answer_time'] = $answer_time;
		$DATA_SET['data']['standard_time'] = $standard_time;
		$DATA_SET['data']['hint_number'] = $hint_number;
		$DATA_SET['data']['first_problem'] = $first_problem;
		$DATA_SET['data']['latter_problem'] = $latter_problem ;
		$DATA_SET['data']['selection_words'] = $selection_words;
		$DATA_SET['data']['correct'] = $correct;
		$DATA_SET['data']['option1'] = $option1;
		$DATA_SET['data']['option2'] = $option2;
		$DATA_SET['data']['option3'] = $option3;
		$DATA_SET['data']['option4'] = $option4;
		$DATA_SET['data']['option5'] = $option5;
		$DATA_SET['data']['display'] = $display;
		$DATA_SET['data']['state'] = $state;
	}

	return array($DATA_SET, $ERROR);
}

//function check_form_type_1() {
//}

//function check_form_type_2() {
//}

//function check_form_type_3() {
//}

//function check_form_type_4() {
//}

//function check_form_type_5() {
//}

//function check_form_type_8() {
//}

//function check_form_type_10() {
//}

//function check_form_type_11() {
//}


/**
 * 判定テスト  マスターデータ 各入力項目チェックルーチン
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 *  IN: $DATA_MS_SET[]  １件分データ、制御値と入力データ
 *  戻り値: array(データ配列、エラーメッセージ配列)
 *                データ配列とは、このルーチン内を呼ぶ際に渡した $DATA_MS_SET[] をすべて返しますが、
 *                check_mode、store_mode により、半角英数字化およびtrim処理を行った結果を
 *                返させることが出来ます。
 * @author Azet
 * @param array $DATA_MS_SET
 * @param array $ERROR
 * @return array
 */
function ht_master_check($DATA_MS_SET, $ERROR) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_HANTEI_TYPE;

	//※ test_practice_hantei_test_master.php からは使用しない。
	// 理由は、form入力では、各種コード番号はドロップダウン選択なのに対し、csvでは"名称"で渡されるものが多数あるため。

	//制御値
	// $DATA_MS_SET['__info']['call_mode']		// 0=入力フォーム、1:tsv入力
	// $DATA_MS_SET['__info']['line_num']		// 行番号...エラーメッセージに付記するため
	// $DATA_MS_SET['__info']['check_mode']	// チェック時にデータ型を自動調整(半角英数字化、trim処理)するかのスイッチ
	//												0:自動調整しない、1:する
	// $DATA_MS_SET['__info']['store_mode']	// データ自動調整結果を、$DATA_MS_SET['data']['パラメータ名']へ再格納するかのスイッチ
	//												0:再格納しない、1:する
	//
	//入力データ群 (form_type 含む)
	// $DATA_MS_SET['data']['パラメータ名']

	//入力パラメータ
	// hantei_default_num
	// service_num
	// hantei_type_name -> hantei_type  1|2
	// course_name -> course_num
	// hantei_display_num
	// style_course_name -> style_course_num
	// style_stage_name -> style_stage_num
	// hantei_name

	// hantei_result -> hantei_result_list
	// hantei_result_last -> hantei_result_list_last	//add okabe 2013/10/04

	// hantei_default_key ※チェック対象外
	// rand_type
	// clear_rate
	// problem_count
	// limit_time
	// clear_msg
	// break_msg
	// ms_display
	// problem_num

	//出力： $error_flg この１件でエラーが発生したら=1、エラー無しは=0

	$error_flg = 0;
	$service_num = $DATA_MS_SET['data']['service_num'];

	//制御情報
	$line_num = "";
	if ($DATA_MS_SET['__info']['call_mode'] == "1") {
		//$line_num = $DATA_MS_SET['__info']['line_num'].": ";	//行番号
		$line_num = line_number_format($DATA_MS_SET['__info']['line_num']);	//行番号
	}
	$check_mode = $DATA_MS_SET['__info']['check_mode'];	//データ型を自動調整(1:する)
	$store_mode = $DATA_MS_SET['__info']['store_mode'];	//自動調整結果の再格納(1:する)


	//データチェック開始

	//hantei_default_num
	$hantei_default_num = $DATA_MS_SET['data']['hantei_default_num'];	//
	if ($check_mode) {
		$hantei_default_num = mb_convert_kana($hantei_default_num, "asKV", "UTF-8");
		$hantei_default_num = trim($hantei_default_num);
	}

	if (strlen(hantei_default_num) > 0) {
		if (preg_match("/[^0-9]/",$hantei_default_num)) {
			$ERROR[] = $line_num."判定テスト管理番号が不正です。";
			$error_flg = 1;
		}
	}

	//hantei_type_name 判定タイプ名(コース判定:1、コース内判定:2)
	$hantei_type_name = $DATA_MS_SET['data']['hantei_type_name'];
	if ($check_mode) {
		$hantei_type_name = mb_convert_kana($hantei_type_name,"asKV", "UTF-8");
		$hantei_type_name = trim($hantei_type_name);
	}
	$hantei_type = array_search($hantei_type_name, $L_HANTEI_TYPE);
	if ($hantei_type < 1 || is_null($hantei_type)) {
		$ERROR[] = $line_num."判定タイプが不正です。";
		$error_flg = 1;
		//return $ERROR;
	}

	//course_name コース名 -> コースコードへの変換
	$course_name = $DATA_MS_SET['data']['course_name'];
	if ($check_mode) {
		$course_name = mb_convert_kana($course_name,"asKV", "UTF-8");
		$course_name = trim($course_name);
	}
	if ($hantei_type == 2) { 	//コース内判定のときに要指定
		if (strlen($course_name) == 0) {
			$ERROR[] = $line_num."コース名が入力されていません。";
			$error_flg = 1;
		} else {
			$course_num = get_course_num($service_num, $course_name);
			if ($course_num < 1 || is_null($course_num)) {
				$ERROR[] = $line_num."コース名が不正です。";
				$error_flg = 1;
			}
		}
	} else {
		if (strlen($course_name) > 0) {
			$ERROR[] = $line_num."コース名の指定は、'コース内判定'のときのみ必要です。";
			$error_flg = 1;
		}
	}

	//hantei_display_num 判定テスト表示番号
	$hantei_display_num = $DATA_MS_SET['data']['hantei_display_num'];	//
	if ($check_mode) {
		$hantei_display_num = mb_convert_kana($hantei_display_num, "asKV", "UTF-8");
		$hantei_display_num = trim($hantei_display_num);
	}
	if (strlen($hantei_display_num) > 0) {
		if (preg_match("/[^0-9]/",$hantei_display_num)) {
			$ERROR[] = $line_num."判定テスト表示番号の値が正しくありません。";
			$error_flg = 1;
		}
	} else {
		$ERROR[] = $line_num."判定テスト表示番号が未入力です";
		$error_flg = 1;
	}

	//style_course_name スタイル基準コース名
	//$style_course_name = $DATA_MS_SET['data']['style_course_name'];		//del okabe 2012/09/12 "スタイル基準ignore"の入力修正の一時的無効化
	$style_course_name = "400点コース";		// add okabe 2012/09/12 "スタイル基準ignore"の入力修正の一時的無効化。有効にする際はココを削除
	if ($check_mode) {
		$style_course_name = mb_convert_kana($style_course_name,"asKV", "UTF-8");
		$style_course_name = trim($style_course_name);
	}
	if (strlen($style_course_name) == 0) {
		$ERROR[] = $line_num."スタイル基準コース名が未入力です。";
		$error_flg = 1;
	} else {
		$style_course_num = get_course_num($service_num, $style_course_name);
		if ($style_course_num < 1 || is_null($style_course_num)) {
			$ERROR[] = $line_num."スタイル基準コース名が不正です。";
			$error_flg = 1;
		}
	}

	//style_stage_name スタイル基準ステージ名
	//$style_stage_name = $DATA_MS_SET['data']['style_stage_name'];	// del okabe 2012/09/12 "スタイル基準ignore"の入力修正の一時的無効化
	$style_stage_name = "";		// add okabe 2012/09/12 "スタイル基準ignore"の入力修正の一時的無効化。有効にする際はココを削除
	if ($check_mode) {
		$style_stage_name = mb_convert_kana($style_stage_name,"asKV", "UTF-8");
		$style_stage_name = trim($style_stage_name);
	}
	if (strlen($style_stage_name) > 0) {
		$style_stage_num = get_stage_num($style_course_num, $style_stage_name);
		if ($style_stage_num < 1 || is_null($style_stage_num)) {
			$ERROR[] = $line_num."スタイル基準ステージ名が不正です。";
			$error_flg = 1;
		}
	}

	//hantei_name 判定名
	$hantei_name = $DATA_MS_SET['data']['hantei_name'];	//
	// del oda 2013/10/16 判定名の未入力を許可する。
//	if (strlen($hantei_name) == 0) {
//		$ERROR[] = $line_num."判定名が未入力です。";
//		$error_flg = 1;
//	}

	//hantei_result 判定結果
	$hantei_result = $DATA_MS_SET['data']['hantei_result'];
	if ($check_mode) {
		$hantei_result = mb_convert_kana($hantei_result, "asKV", "UTF-8");
		$hantei_result = trim($hantei_result);
	}
	if (strlen($hantei_result) == 0) {
		$ERROR[] = $line_num."判定結果が未入力です。";
		$HANTEI_WHOLE_DATA_L = array();
		$error_flg = 1;
	} else {
		//list($HANTEI_WHOLE_DATA_L, $ERROR_SUB) = analys_hantei_result($service_num, $hantei_result, $ERROR_SUB, $line_num);	//del okabe 2013/10/04
		list($HANTEI_WHOLE_DATA_L, $ERROR_SUB) = analys_hantei_result($service_num, 0, $hantei_result, $ERROR_SUB, $line_num);		//add okabe 2013/10/04
		if (count($ERROR_SUB) > 0) {
			$ERROR = array_merge($ERROR, $ERROR_SUB);
			$error_flg = 1;
		}
	}

	//add start okabe 2013/10/04
	//hantei_result_last 最終テスト合格時の判定結果
	$hantei_result_last = $DATA_MS_SET['data']['hantei_result_last'];
	if ($check_mode) {
		$hantei_result_last = mb_convert_kana($hantei_result_last, "asKV", "UTF-8");
		$hantei_result_last = trim($hantei_result_last);
	}
	//add start okabe 2013/10/04
	if ($hantei_type != 2 && strlen($hantei_result_last)>0) {
			$ERROR[] = $line_num."コース判定の場合、最終テスト合格時の判定結果の指定は不要です。";
			$error_flg = 1;
	}
	//add end okabe 2013/10/04
	list($HANTEI_WHOLE_DATA_LAST_L, $ERROR_SUB) = analys_hantei_result($service_num, 1, $hantei_result_last, $ERROR_SUB, $line_num);
	if (count($ERROR_SUB) > 0) {
		$ERROR = array_merge($ERROR, $ERROR_SUB);
		$error_flg = 1;
	}
	//add end okabe 2013/10/04

	//rand_type ランダム表示
	$rand_type = $DATA_MS_SET['data']['rand_type'];	//
	if ($check_mode) {
		$rand_type = mb_convert_kana($rand_type, "asKV", "UTF-8");
		$rand_type = trim($rand_type);
	}
	if (strlen($rand_type) > 0) {
		if (preg_match("/[^0-9]/",$rand_type)) {
			$ERROR[] = $line_num."ランダム表示の指定が不正です。";
			$error_flg = 1;
		} else {
			if ($rand_type != "1" && $rand_type != "2") {
				$ERROR[] = $line_num."ランダム表示の指定は 1 または 2 です。";
				$error_flg = 1;
			}
		}
	} else {
		$ERROR[] = $line_num."ランダム表示の指定が未入力です。";
		$error_flg = 1;
	}

	//clear_rate 判定正解率
	$clear_rate = $DATA_MS_SET['data']['clear_rate'];	//
	if ($check_mode) {
		$clear_rate = mb_convert_kana($clear_rate, "asKV", "UTF-8");
		$clear_rate = trim($clear_rate);
	}
	if (strlen($clear_rate) > 0) {
		if (!preg_match("/^([1-9]\d*|0)(\.\d+)?$/", $clear_rate)) {
			$ERROR[] = $line_num."判定正解率の指定が不正です。";
			$error_flg = 1;
		} else {
			if ($clear_rate < 0.0 || $clear_rate > 100.0) {
				$ERROR[] = $line_num."判定正解率の値の範囲が不正です。";
				$error_flg = 1;
			}
		}
	} else {
		$ERROR[] = $line_num."判定正解率が未入力です。";
		$error_flg = 1;
	}

	//problem_count 出題問題数
	$problem_count = $DATA_MS_SET['data']['problem_count'];	//
	if ($check_mode) {
		$problem_count = mb_convert_kana($problem_count, "asKV", "UTF-8");
		$problem_count = trim($problem_count);
	}
	if (strlen($problem_count) > 0) {
		if (preg_match("/[^0-9]/",$problem_count)) {
			$ERROR[] = $line_num."出題問題数の指定が不正です。";
			$error_flg = 1;
		} else {
			if ($problem_count < 0) {
				$ERROR[] = $line_num."出題問題数の値が不正です。";
				$error_flg = 1;
			}
		}
	} else {
		$ERROR[] = $line_num."出題問題数が未入力です。";
		$error_flg = 1;
	}

	//limit_time ドリルクリア標準時間
	$limit_time = $DATA_MS_SET['data']['limit_time'];	//
	if ($check_mode) {
		$limit_time = mb_convert_kana($limit_time, "asKV", "UTF-8");
		$limit_time = trim($limit_time);
	}
	if (strlen($limit_time) > 0) {
		if (preg_match("/[^0-9]/",$limit_time)) {
			$ERROR[] = $line_num."ドリルクリア標準時間の指定が不正です。";
			$error_flg = 1;
		} else {
			if ($limit_time < 1) {
				$ERROR[] = $line_num."ドリルクリア標準時間の値が不正です。";
				$error_flg = 1;
			}
		}
	} else {
		$ERROR[] = $line_num."ドリルクリア標準時間が未入力です。";
		$error_flg = 1;
	}

	//clear_msg クリアメッセージ
	$clear_msg = $DATA_MS_SET['data']['clear_msg'];	//
	/* del start okabe 2012/09/12 "ドリルクリアメッセージignore" "判定結果メッセージignore"の入力修正の一時的無効化
	if (strlen($clear_msg) == 0) {
		$ERROR[] = $line_num."ドリルクリアメッセージが未入力です。";
		$error_flg = 1;
	}
	*/ // del end okabe 2012/09/12 "ドリルクリアメッセージignore" "判定結果メッセージignore"の入力修正の一時的無効化

	//break_msg 判定結果メッセージ
	$break_msg = $DATA_MS_SET['data']['break_msg'];	//
	/* del start okabe 2012/09/12 "ドリルクリアメッセージignore" "判定結果メッセージignore"の入力修正の一時的無効化
	if (strlen($break_msg) == 0) {
		$ERROR[] = $line_num."判定結果メッセージが未入力です。";
		$error_flg = 1;
	}
	*/ // del end okabe 2012/09/12 "ドリルクリアメッセージignore" "判定結果メッセージignore"の入力修正の一時的無効化

	//ms_display 判定テスト表示/非表示
	$ms_display = $DATA_MS_SET['data']['ms_display'];	//
	if ($check_mode) {
		$ms_display = mb_convert_kana($ms_display, "asKV", "UTF-8");
		$ms_display = trim($ms_display);
	}
	if (strlen($ms_display) > 0) {
		if (preg_match("/[^0-9]/",$ms_display)) {
			$ERROR[] = $line_num."表示・非表示(判定テスト)の指定が不正です。";
			$error_flg = 1;
		} else {
			if ($ms_display != "1" && $ms_display != "2") {
				$ERROR[] = $line_num."表示・非表示(判定テスト)の指定は 1 または 2 です。";
				$error_flg = 1;
			}
		}
	} else {
		$ERROR[] = $line_num."表示・非表示(判定テスト)が未入力です。";
		$error_flg = 1;
	}

	//problem_num すらら問題管理番号
	$problem_num = $DATA_MS_SET['data']['problem_num'];	//
	if ($check_mode) {
		$problem_num = mb_convert_kana($problem_num, "asKV", "UTF-8");
		$problem_num = trim($problem_num);
	}
	if (strlen($problem_num) > 0) {
		if (preg_match("/[^0-9]/",$problem_num) || $problem_num < 1) {
			$ERROR[] = $line_num."問題管理番号の値が不正です。";
			$error_flg = 1;
		} else {
			$add_problem_flg = $DATA_MS_SET['data']['add_problem_flg'];
			if ($add_problem_flg == 0) {
				//指定した番号の すらら問題 が有効な状態で存在するかチェック
				$sql = "SELECT problem.problem_num".
					" FROM ".T_PROBLEM." problem".
					" WHERE problem.state='0'".
					" AND problem.problem_num='".$problem_num."';";
				$result = $cdb->query($sql);
				$list = $cdb->fetch_assoc($result);
				if (!$list) {
					$ERROR[] = $line_num."指定した問題管理番号 ".$problem_num." は無効です。";
					$error_flg = 1;
				}

			} else {
				$ERROR[] = $line_num."すらら問題を指定した場合、同じ行に問題データは記述できません。";
				$error_flg = 1;
			}
		}
	}


	//要変換項目結果の格納
	$DATA_MS_SET['data']['hantei_type_num'] = $hantei_type;
	$DATA_MS_SET['data']['course_num']  = $course_num;
	$DATA_MS_SET['data']['style_course_num'] = $style_course_num;
	$DATA_MS_SET['data']['style_stage_num'] = $style_stage_num;
	$DATA_MS_SET['data']['hantei_result_list'] = $HANTEI_WHOLE_DATA_L;
	$DATA_MS_SET['data']['hantei_result_list_last'] = $HANTEI_WHOLE_DATA_LAST_L;
	if ($store_mode) {
		//データの再格納
		$DATA_MS_SET['data']['hantei_default_num'] = $hantei_default_num;
		$DATA_MS_SET['data']['hantei_type_name'] = $hantei_type_name;
		$DATA_MS_SET['data']['course_name']  = $course_name;
		$DATA_MS_SET['data']['hantei_display_num'] = $hantei_display_num;
		$DATA_MS_SET['data']['style_course_name'] = $style_course_name;
		$DATA_MS_SET['data']['style_stage_name'] = $style_stage_name;
		$DATA_MS_SET['data']['hantei_name']  = $hantei_name;
		$DATA_MS_SET['data']['hantei_result'] = $hantei_result;
		$DATA_MS_SET['data']['rand_type'] = $rand_type;
		$DATA_MS_SET['data']['clear_rate'] = $clear_rate;
		$DATA_MS_SET['data']['problem_count'] = $problem_count;
		$DATA_MS_SET['data']['limit_time'] = $limit_time;
		$DATA_MS_SET['data']['clear_msg'] = $clear_msg ;
		$DATA_MS_SET['data']['break_msg'] = $break_msg;
		$DATA_MS_SET['data']['ms_display'] = $ms_display;
		$DATA_MS_SET['data']['problem_num'] = $problem_num;
	}

	return array($DATA_MS_SET, $ERROR, $error_flg);
}


/**
 * "判定結果"の指定文字列のパーシング＆コード化
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * $break_layer_type: 0:不合格時の判定結果, 1:最終テスト合格時の判定結果
 * 判定結果データ（複数ある場合には "::" で連結された コース名-ユニットキーまたはレッスンキー 形式）の文字列
 * このデータを :: で split し、１つずつ db からコードを取得して配列で返す
 *  $HANTEI_ONE_DATA_L 配列の要素個々は配列、
 *  その配列には array('course_num'->val, 'stage_num'->val, 'lesson_num'->val, 'unit_num'->val, 'block_num'->val)
 * @author Azet
 * @param integer $service_num
 * @param mixed $break_layer_type
 * @param mixed $hantei_result
 * @param array $ERROR
 * @param string $err_prefix
 * @return array
 */
function analys_hantei_result($service_num, $break_layer_type, $hantei_result, $ERROR, $err_prefix) {	//add okabe 2013/10/04
//function analys_hantei_result($service_num, $hantei_result, $ERROR, $err_prefix) {	//del okabe 2013/10/04
//echo "---------------<br/>";

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$add_str = "";
	if ($break_layer_type == 1) { $add_str = "最終テスト合格時の"; }

	//取得データをセットするための連想配列
	$HANTEI_WHOLE_DATA_L = array();

	if (strlen($hantei_result) > 0 && !$ERROR) {
		//分解、指示データ存在のチェック
		$HANTEI_RESULT_L = preg_split("/::/", $hantei_result, -1);

		foreach ($HANTEI_RESULT_L as $key => $value) {
			//取得データをセットするための連想配列
			$HANTEI_ONE_DATA_L = array();

			//１件ずつデータ存在をチェックする
			$ONE_DATA_L = explode("-", $value);

			if (count($ONE_DATA_L) == 2) {
				//指定した key が存在するか
				//$chk_course_num = $ONE_DATA_L[0];
				$chk_course_name = $ONE_DATA_L[0];
				$chk_course_num = get_course_num($service_num, $chk_course_name);
				if (strlen($chk_course_num) == 0 || $chk_course_num == 0) {
					if (strlen($chk_course_name) > 0) {
						$ERROR[] = $err_prefix.$add_str."判定結果に指定されたコース名 '".$chk_course_name."' は正しくありません。";
					} else {
						$ERROR[] = $err_prefix.$add_str."判定結果の構文が不正です。";
					}
				}
				$chk_key = $ONE_DATA_L[1];

				//lesson テーブルに存在するか
				$sql  = "SELECT * FROM ".T_LESSON.
					" WHERE state!='1' AND course_num = '".$chk_course_num."' AND lesson_key='".$chk_key."';";
				$count = 0;
				if ($result = $cdb->query($sql)) {
					$count = $cdb->num_rows($result);
				}

				if ($count == 0) {
					$ERROR[] = $err_prefix.$add_str."判定結果に指定されたレッスンキーは登録されていません。(コース".$chk_course_name.", キー".$chk_key.")";	//add start okabe 2013/09/12   unit_key は使用せず、lesson_key のみで判別するように修正

				} else {
					$list = $cdb->fetch_assoc($result);
					//取得データの格納（コース＋レッスン）
					$HANTEI_ONE_DATA_L['course_num'] = $list['course_num'];
					$HANTEI_ONE_DATA_L['stage_num']  = $list['stage_num'];
					$HANTEI_ONE_DATA_L['lesson_num'] = $list['lesson_num'];
					$HANTEI_ONE_DATA_L['unit_num'] = "0";
					$HANTEI_ONE_DATA_L['block_num'] = "0";
				}

			} else {
				// "-"で区切られていないときは、コース名のみの指定かチェックする
				//コース名 を コース番号に変換してチェック
				$chk_course_name = $value;
				$chk_course_num = get_course_num($service_num, $chk_course_name);
				if (strlen($chk_course_num) == 0 || $chk_course_num == 0) {
					if (strlen($chk_course_name) > 0) {
						$ERROR[] = $err_prefix.$add_str."判定結果に指定されたコース名 '".$chk_course_name."' は正しくありません。";
					} else {
						$ERROR[] = $err_prefix.$add_str."判定結果の構文が不正です。";
					}

				} else {
					$sql  = "SELECT * FROM ".T_COURSE.
					//	" WHERE state!='1' AND course_num = '".$value."';";
						" WHERE state!='1' AND course_num = '".$chk_course_num."';";
					$count = 0;
					if ($result = $cdb->query($sql)) {
						$count = $cdb->num_rows($result);
					}

					if ($count == 0) {
						//指定されたコース番号が course テーブルに存在しない。
						$ERROR[] = $err_prefix.$add_str."判定結果に指定されたコース番号 ".$value." は登録されていません。";
					} else {
						//保存用データの格納（コース）
						$HANTEI_ONE_DATA_L['course_num'] = $chk_course_num;
						$HANTEI_ONE_DATA_L['stage_num']  = "0";
						$HANTEI_ONE_DATA_L['lesson_num'] = "0";
						$HANTEI_ONE_DATA_L['unit_num'] = "0";
						$HANTEI_ONE_DATA_L['block_num'] = "0";
					}
					//	$ERROR[] = "判定結果の入力形式が正しくありません(コース番号の次に - を入力して下さい)。";
					//}
				}
			}

			//取得したデータを連想配列に保存
			$HANTEI_WHOLE_DATA_L[$value] = $HANTEI_ONE_DATA_L;

		}
	}

	return array($HANTEI_WHOLE_DATA_L, $ERROR);
}


/**
 * 判定結果"データの再構築（１件分）
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $service_num
 * @param array $list
 * @param string $hantei_result
 * @return string
 */
function rebuild_hantei_result($service_num, $list, $hantei_result) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$add_data = "";

	//表示用データの組み立て
	$c_course_num	= $list['course_num'];
	$c_stage_num	= $list['stage_num'];
	$c_lesson_num	= $list['lesson_num'];
	$c_unit_num		= $list['unit_num'];
	//$c_block_num = $list['block_num'];

	if ($c_lesson_num > 0 && $c_unit_num == 0) {
		//ユニット番号指定なし && レッスン番号指定あり、lesson_num でDBからデータ取得
		$sql2  = "SELECT lesson.lesson_key".
		" FROM " . T_LESSON . " lesson" .
		" WHERE lesson.state='0' AND lesson.lesson_num='".$c_lesson_num."';";
		$result2 = $cdb->query($sql2);
		$list2 = $cdb->fetch_assoc($result2);
		if ($list2) {
			//$add_data = $c_course_num."-".$list2['lesson_key'];
			$add_data = get_course_name($service_num, $c_course_num);	//コース番号ではなくコース名で表示
			$add_data .= "-".$list2['lesson_key'];
		}

	} elseif ($c_course_num > 0) {
		//コース番号だけ指定、sourse_num を使用
		//$add_data = $c_course_num;
		$add_data = get_course_name($service_num, $c_course_num);	//コース番号ではなくコース名で表示

	}
	//判定結果 入力欄の組み立て
	if (strlen($add_data) > 0) {
		if (strlen($hantei_result) > 0) { $hantei_result .= "::"; }
		$hantei_result .= $add_data;
	}

	return $hantei_result;
}


/**
 * コース名の取得 service_num, course_num
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $service_num
 * @param integer $course_num
 * @return string
 */
function get_course_name($service_num, $course_num) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$course_name = "";
	$sql = "SELECT course.course_num, course.course_name".
		" FROM ".T_SERVICE_COURSE_LIST." service_course_list, ".
		T_COURSE." course ".
		" WHERE course.course_num = service_course_list.course_num".
		" AND course.state = 0".
		" AND service_course_list.mk_flg = 0".
		" AND service_course_list.service_num ='".$service_num."';";
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			if ($course_num == $list['course_num']) {
				$course_name = $list['course_name'];
			}
		}
	}
	return $course_name;
}


/**
 * コース名の取得sub
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $course_num
 * @return string
 */
function get_course_name_sub($course_num) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$course_name = "";
	$sql = "SELECT course.course_num, course.course_name".
		" FROM ".T_COURSE." course ".
		" WHERE course.course_num = '".$course_num."'".
		" AND course.state = 0;";
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			if ($course_num == $list['course_num']) {
				$course_name = $list['course_name'];
			}
		}
	}
	return $course_name;
}


/**
 * ステージ名の取得 stage_num
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $stage_num
 * @return string
 */
function get_stage_name($stage_num) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$stage_name = "";
	$sql = "SELECT stage.stage_name".
		" FROM ".T_STAGE." stage".
		" WHERE stage.stage_num = '".$stage_num."'".
		" AND stage.state = 0;";
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$stage_name = $list['stage_name'];
		}
	}
	return $stage_name;
}


/**
 * コース番号の取得 service_num, course_name
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $service_num
 * @param string $course_name
 * @return integer
 */
function get_course_num($service_num, $course_name) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$course_num = "";
	$sql = "SELECT course.course_num".
		" FROM ".T_SERVICE_COURSE_LIST." service_course_list, ".
		T_COURSE." course ".
		" WHERE course.course_num = service_course_list.course_num".
		" AND course.state = 0".
		" AND service_course_list.mk_flg = 0".
		" AND course.course_name = '".$course_name."'".
		" AND service_course_list.service_num ='".$service_num."';";
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$course_num = $list['course_num'];
		}
	}
	return $course_num;
}


/**
 * ステージ番号の取得 course_num, stage_name
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $course_num
 * @param string $stage_name
 * @return integer
 */
function get_stage_num($course_num, $stage_name) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$stage_num = "";
	$sql = "SELECT stage.stage_num".
		" FROM ".T_STAGE." stage".
		" WHERE stage.stage_name = '".$stage_name."'".
		" AND stage.course_num = '".$course_num."'".
		" AND stage.state = 0;";
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$stage_num = $list['stage_num'];
		}
	}
	return $stage_num;
}


/**
 * ファイル問題登録時、文字変換
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param string $word
 * @return string
 */
function ht_replace_word($word) {
	$word = mb_convert_kana($word,"asKV","UTF-8");
	$word = ereg_replace("<>","&lt;&gt;",$word);
	$word = trim($word);
	$word = eregi_replace("\r","",$word);
	$word = eregi_replace("\n","<br>",$word);
	$word = replace_encode($word);
	return $word;
}


/**
 * 	マスター＆問題データ csv出力情報整形 ★廃止予定★
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * 	未使用です！
 *
 * @author Azet
 * @param array $L_CSV_COLUMN
 * @param integer $sel_service_num
 * @param mixed $head_mode='1'
 * @param mixed $csv_mode='1'
 * @return array
 */
function ht_make_csv($L_CSV_COLUMN, $sel_service_num, $head_mode='1', $csv_mode='1') {
//	2015/01/08 yoshizawa

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// $sel_service_num ターゲットのサービス番号
	global $L_HANTEI_TYPE;

	$delimiter = "\t";
	//$delimiter = ",";

	$csv_line = "";

	if (!is_array($L_CSV_COLUMN)) {
		$ERROR[] = "CSV抽出項目が設定されていません。";
		return array($csv_line,$ERROR);
	}
	//	head line (一行目)
	foreach ($L_CSV_COLUMN as $key => $val) {
		if ($head_mode == 1) { $head_name = $key; }
		elseif ($head_mode == 2) { $head_name = $val; }
		$csv_line .= "\"".$head_name."\"".$delimiter;
	}
	$csv_line .= "\n";

	//hantei_ms_default から判定テスト(hantei_default_num)ごとに抽出し、マスター情報を用意
	$L_CSV = array();
	$L_CSV_LINE = array();
	$j=0;	//データ行番号用

	$sql_hmd = "SELECT hmd.hantei_default_num, hmd.service_num ".
		" FROM ".T_HANTEI_MS_DEFAULT." hmd ".
		" WHERE hmd.mk_flg='0'".
		" AND hmd.service_num='".$sel_service_num."' ORDER BY hmd.hantei_default_num;";
	if ($result_hmd = $cdb->query($sql_hmd)) {	//all valid hantei_default_num (in hantei_ms_default)
		while ($list_hmd = $cdb->fetch_assoc($result)) {
			$hantei_default_num = $list_hmd['hantei_default_num'];
			$service_num = $list_hmd['service_num'];

			//判定テストごとにデータを整理
			$sql = "SELECT hmd.hantei_type, hmd.course_num, hmd.hantei_display_num,".
				" hmd.style_course_num, hmd.style_stage_num,".
				" hmd.hantei_name, hmd.rand_type, hmd.clear_rate, hmd.problem_count,".
				" hmd.limit_time, hmd.clear_msg, hmd.break_msg,".
				" hmd.display".
				" FROM ".T_HANTEI_MS_DEFAULT." hmd ".
				" WHERE hmd.mk_flg='0'".
				" AND hmd.hantei_default_num='".$hantei_default_num."';";
			$result = $cdb->query($sql);		//each hantei_ms_default data
			$list = $cdb->fetch_assoc($result);

			if ($list) {

				//"判定結果"情報の組み立て(hantei_ms_break_layerから)
				$sql_hmbl = "SELECT ".
					" hmbl.course_num,".
					" hmbl.stage_num,".
					" hmbl.lesson_num,".
					" hmbl.unit_num,".
					" hmbl.block_num".
					" FROM " . T_HANTEI_MS_BREAK_LAYER . " hmbl" .
						" WHERE hmbl.mk_flg='0' AND hmbl.hantei_default_num='".$hantei_default_num."';";
				$result_hmbl = $cdb->query($sql_hmbl);

				$hantei_result = "";	//結果格納変数
				while ($list_hmbl=$cdb->fetch_assoc($result_hmbl)) {
					$hantei_result = rebuild_hantei_result($service_num, $list_hmbl, $hantei_result);
				}
				$j++;	//マスターデータ出力のため+1

				$L_CSV[$j]['hantei_default_num'] = "";	//1
				$L_CSV[$j]['hantei_type_name'] = "";	//2
				$L_CSV[$j]['course_name'] = "";			//3
				$L_CSV[$j]['hantei_display_num'] = "";	//4
				$L_CSV[$j]['style_course_name'] = "";	//5
				$L_CSV[$j]['style_stage_name'] = "";	//6
				$L_CSV[$j]['hantei_name'] = "";			//7
				$L_CSV[$j]['hantei_result'] = "";		//8
				$L_CSV[$j]['rand_type'] = "";			//9
				$L_CSV[$j]['clear_rate'] = "";			//10
				$L_CSV[$j]['problem_count'] = "";		//11

				$L_CSV[$j]['limit_time'] = "";			//12
				$L_CSV[$j]['clear_msg'] = "";			//13
				$L_CSV[$j]['break_msg'] = "";			//14

				$L_CSV[$j]['ms_display'] = "";			//15
				$L_CSV[$j]['problem_num'] = "";			//16
				$L_CSV[$j]['disp_sort'] = "";			//17
				$L_CSV[$j]['problem_type'] = "";		//18
				$L_CSV[$j]['sub_display_problem_num'] = "";	//19
				$L_CSV[$j]['form_type'] = "";			//20
				$L_CSV[$j]['question'] = "";			//21
				$L_CSV[$j]['problem'] = "";				//22
				$L_CSV[$j]['voice_data'] = "";			//23
				$L_CSV[$j]['hint'] = "";				//24
				$L_CSV[$j]['explanation'] = "";			//25
				$L_CSV[$j]['standard_time'] = "";		//26
				$L_CSV[$j]['parameter'] = "";			//27
				$L_CSV[$j]['set_difficulty'] = "";		//28
				$L_CSV[$j]['hint_number'] = "";			//29
				$L_CSV[$j]['correct_number'] = "";		//30
				$L_CSV[$j]['clear_number'] = "";		//31
				$L_CSV[$j]['first_problem'] = "";		//32
				$L_CSV[$j]['latter_problem'] = "";		//33
				$L_CSV[$j]['selection_words'] = "";		//34
				$L_CSV[$j]['correct'] = "";				//35
				$L_CSV[$j]['option1'] = "";				//36
				$L_CSV[$j]['option2'] = "";				//37
				$L_CSV[$j]['option3'] = "";				//38
				$L_CSV[$j]['option4'] = "";				//39
				$L_CSV[$j]['option5'] = "";				//40
				$L_CSV[$j]['unit_id'] = "";				//41
				$L_CSV[$j]['english_word_problem'] = "";	//42
				$L_CSV[$j]['error_msg'] = "";			//43
				$L_CSV[$j]['update_time'] = "";			//44
				$L_CSV[$j]['display'] = "";				//45
				$L_CSV[$j]['state'] = "";				//46
				$L_CSV[$j]['sentence_flag'] = "";		//47


				$course_num = $list['course_num'];
				$L_CSV[$j]['hantei_default_num'] = $hantei_default_num;		//判定テスト管理番号
				$L_CSV[$j]['hantei_type_name'] = $L_HANTEI_TYPE[$list['hantei_type']];		//判定テスト種別（文字列->コード番号に変換の前の状態）
				$course_name = get_course_name($service_num, $course_num);
				$L_CSV[$j]['course_name'] = $course_name;		//コース名（文字列->コード番号に変換の前の状態）
				$hantei_display_num = $list['hantei_display_num'];	//判定テスト表示番号
				$L_CSV[$j]['hantei_display_num'] = $hantei_display_num;

				$style_course_num = $list['style_course_num'];	//スタイル基準コース番号
				//$style_course_name = get_course_name($service_num, $style_course_num) ;	//スタイル基準コース名
				$style_course_name = get_course_name_sub($style_course_num) ;	//スタイル基準コース名
				$L_CSV[$j]['style_course_name'] = $style_course_name;

				$style_stage_num = $list['style_stage_num'];	//スタイル基準ステージ番号
				$style_stage_name = get_stage_name($style_stage_num);	//スタイル基準ステージ名
				$L_CSV[$j]['style_stage_name'] = $style_stage_name;

				$L_CSV[$j]['hantei_result'] = $hantei_result;			//判定結果

				$hantei_name = $list['hantei_name'];				//判定テスト表示名
				$L_CSV[$j]['hantei_name'] = $hantei_name ;

				$rand_type = $list['rand_type'];	//ランダム表示
				$L_CSV[$j]['rand_type'] = $rand_type ;
				$clear_rate = $list['clear_rate'];	//判定正解率
				$L_CSV[$j]['clear_rate'] = $clear_rate ;
				$problem_count = $list['problem_count'];	//出題問題数
				$L_CSV[$j]['problem_count'] = $problem_count ;
				$ms_display = $list['display'];	//マスター 表示・非表示
				$L_CSV[$j]['ms_display'] = $ms_display ;

				$limit_time = $list['limit_time'];		//ドリルクリア標準時間
				$L_CSV[$j]['limit_time'] = $limit_time ;
				$clear_msg = $list['clear_msg'];		//クリアメッセージ
				$L_CSV[$j]['clear_msg'] = $clear_msg ;
				$break_msg = $list['break_msg'];		//判定結果メッセージ
				$L_CSV[$j]['break_msg'] = $break_msg ;


				//このマスターに属する問題データがあれば、データ組立て
				$sql_hmp = "SELECT hmdp.problem_table_type, hmdp.problem_num as hmdp_problem_num, hmdp.disp_sort, hmp.*".
					" FROM ".T_HANTEI_MS_DEFAULT_PROBLEM." hmdp ".
					" LEFT JOIN ".T_HANTEI_MS_PROBLEM." hmp ".
					" ON (hmp.problem_num=hmdp.problem_num AND hmdp.problem_table_type='2')".
					" AND hmp.mk_flg='0'".
					" WHERE hmdp.mk_flg='0'".
					" AND hmdp.hantei_default_num='".$hantei_default_num."';";
				$result_hmp = $cdb->query($sql_hmp);

				while ($list_hmp=$cdb->fetch_assoc($result_hmp)) {

					//$j++;	//従属する問題データ出力のため+1	...マスター情報のみを先に格納しているので、それにオーバーライトしたあと j++ する。

					$L_CSV[$j]['hantei_default_num'] = "";
					$L_CSV[$j]['hantei_type_name'] = "";
					$L_CSV[$j]['course_name'] = "";
					$L_CSV[$j]['hantei_display_num'] = "";
					$L_CSV[$j]['style_course_name'] = "";
					$L_CSV[$j]['style_stage_name'] = "";
					$L_CSV[$j]['hantei_name'] = "";
					$L_CSV[$j]['hantei_result'] = "";
					$L_CSV[$j]['rand_type'] = "";
					$L_CSV[$j]['clear_rate'] = "";
					$L_CSV[$j]['problem_count'] = "";

					$L_CSV[$j]['limit_time'] = "";
					$L_CSV[$j]['clear_msg'] = "";
					$L_CSV[$j]['break_msg'] = "";

					$L_CSV[$j]['ms_display'] = "";
					$L_CSV[$j]['problem_num'] = "";
					$L_CSV[$j]['disp_sort'] = "";
					$L_CSV[$j]['problem_type'] = "";
					$L_CSV[$j]['sub_display_problem_num'] = "";
					$L_CSV[$j]['form_type'] = "";
					$L_CSV[$j]['question'] = "";
					$L_CSV[$j]['problem'] = "";
					$L_CSV[$j]['voice_data'] = "";
					$L_CSV[$j]['hint'] = "";
					$L_CSV[$j]['explanation'] = "";
					$L_CSV[$j]['standard_time'] = "";
					$L_CSV[$j]['parameter'] = "";
					$L_CSV[$j]['set_difficulty'] = "";
					$L_CSV[$j]['hint_number'] = "";
					$L_CSV[$j]['correct_number'] = "";
					$L_CSV[$j]['clear_number'] = "";
					$L_CSV[$j]['first_problem'] = "";
					$L_CSV[$j]['latter_problem'] = "";
					$L_CSV[$j]['selection_words'] = "";
					$L_CSV[$j]['correct'] = "";
					$L_CSV[$j]['option1'] = "";
					$L_CSV[$j]['option2'] = "";
					$L_CSV[$j]['option3'] = "";
					$L_CSV[$j]['option4'] = "";
					$L_CSV[$j]['option5'] = "";
					$L_CSV[$j]['unit_id'] = "";
					$L_CSV[$j]['english_word_problem'] = "";
					$L_CSV[$j]['error_msg'] = "";
					$L_CSV[$j]['update_time'] = "";
					$L_CSV[$j]['display'] = "";
					$L_CSV[$j]['state'] = "";
					$L_CSV[$j]['sentence_flag'] = "";

					$problem_num = $list_hmp['hmdp_problem_num'];				//問題管理番号
					$problem_table_type = $list_hmp['problem_table_type'];	//問題テーブルタイプ（1：すらら 2：専用）
					$disp_sort = $list_hmp['disp_sort'];					//表示順
					$standard_time = $list_hmp['standard_time'];			//解答時間
					$problem_type = $list_hmp['problem_type'];		//問題タイプ
					$form_type = $list_hmp['form_type'];			//出題形式
					$question = $list_hmp['question'];				//質問文
					$problem = $list_hmp['problem'];				//問題文
					$voice_data = $list_hmp['voice_data'];			//音声ファイル名
					$hint = $list_hmp['hint'];						//ヒント内容
					$explanation = $list_hmp['explanation'];		//解説文

					$hint_number = $list_hmp['hint_number'];		//ヒント表示回数

					$first_problem = $list_hmp['first_problem'];		//問題前半テキスト
					$latter_problem = $list_hmp['latter_problem'];		//問題後半テキスト
					$selection_words = $list_hmp['selection_words'];	//選択語句、問題テキスト
					$correct = $list_hmp['correct'];			//正解
					$option1 = $list_hmp['option1'];			//option1
					$option2 = $list_hmp['option2'];			//option2
					$option3 = $list_hmp['option3'];			//option3
					$option4 = $list_hmp['option4'];			//option4
					$option5 = $list_hmp['option5'];			//option5
					$error_msg = $list_hmp['error_msg'];		//error_msg
					$display = $list_hmp['display'];			//表示・非表示フラグ　1:表示　2:非表示
					$mk_flg = $list_hmp['mk_flg'];				//無効フラグ

					if ($problem_table_type == "1") {
						$srl_problem_num = $problem_num;	//すらら問題
					} else {
						$srl_problem_num = "";				//判定テスト問題
					}

					//マスターと同じ情報を、左側に付加して、その右に問題固有のデータを加える
					$course_num = $list['course_num'];
					$L_CSV[$j]['hantei_default_num'] = $hantei_default_num;		//判定テスト管理番号
					$L_CSV[$j]['hantei_type_name'] = $L_HANTEI_TYPE[$list['hantei_type']];		//判定テスト種別（文字列->コード番号に変換の前の状態）
					$course_name = get_course_name($service_num, $course_num);
					$L_CSV[$j]['course_name'] = $course_name;		//コース名（文字列->コード番号に変換の前の状態）
					$hantei_display_num = $list['hantei_display_num'];	//判定テスト表示番号
					$L_CSV[$j]['hantei_display_num'] = $hantei_display_num;
					$L_CSV[$j]['style_course_name'] = $style_course_name;	//スタイル基準コース番号
					$L_CSV[$j]['style_stage_name'] = $style_stage_name;		//スタイル基準ステージ番号

					$L_CSV[$j]['hantei_result'] = $hantei_result;			//判定結果

					$rand_type = $list['rand_type'];	//ランダム表示
					$L_CSV[$j]['rand_type'] = $rand_type ;
					$clear_rate = $list['clear_rate'];	//判定正解率
					$L_CSV[$j]['clear_rate'] = $clear_rate ;
					$problem_count = $list['problem_count'];	//出題問題数
					$L_CSV[$j]['problem_count'] = $problem_count ;

					$limit_time = $list['limit_time'];		//ドリルクリア標準時間
					$L_CSV[$j]['limit_time'] = $limit_time ;
					$clear_msg = $list['clear_msg'];		//クリアメッセージ
					$L_CSV[$j]['clear_msg'] = $clear_msg ;
					$break_msg = $list['break_msg'];		//判定結果メッセージ
					$L_CSV[$j]['break_msg'] = $break_msg ;

					$ms_display = $list['display'];	//マスター 表示・非表示
					$L_CSV[$j]['ms_display'] = $ms_display ;


					//問題ごとのデータ付加
					$L_CSV[$j]['hantei_display_num'] = $hantei_display_num;		//判定テスト表示番号

					$L_CSV[$j]['hantei_name'] = $list['hantei_name'];			//判定テスト表示名
					$L_CSV[$j]['hantei_result'] = $hantei_result;		//判定結果
					$L_CSV[$j]['rand_type'] = $rand_type;		//ランダム表示
					$L_CSV[$j]['clear_rate'] = $clear_rate;
					$L_CSV[$j]['problem_count'] = $problem_count;
					$L_CSV[$j]['ms_display'] = $ms_display;

					$L_CSV[$j]['problem_num'] = $srl_problem_num;		//すらら問題の場合は値がセットされる、判定テスト専用の場合は""

					$L_CSV[$j]['disp_sort'] = $disp_sort;		//表示順
					$L_CSV[$j]['problem_type'] = $problem_type;
					$L_CSV[$j]['form_type'] = $form_type;
					$L_CSV[$j]['question'] = $question;
					$L_CSV[$j]['problem'] = $problem;
					$L_CSV[$j]['voice_data'] = $voice_data;
					$L_CSV[$j]['hint'] = $hint;
					$L_CSV[$j]['explanation'] = $explanation;
					$L_CSV[$j]['standard_time'] = $standard_time;
					$L_CSV[$j]['hint_number'] = $hint_number;
					$L_CSV[$j]['first_problem'] = $first_problem;
					$L_CSV[$j]['latter_problem'] = $latter_problem;
					$L_CSV[$j]['selection_words'] = $selection_words;
					$L_CSV[$j]['correct'] = $correct;
					$L_CSV[$j]['option1'] = $option1;
					$L_CSV[$j]['option2'] = $option2;
					$L_CSV[$j]['option3'] = $option3;
					$L_CSV[$j]['option4'] = $option4;
					$L_CSV[$j]['option5'] = $option5;
					$L_CSV[$j]['error_msg'] = $error_msg;
					$L_CSV[$j]['display'] = $display;
					$L_CSV[$j]['state'] = $mk_flg;

					$j++;	//従属する問題データ出力のため+1

				}

			}

		}

	}



	//どの判定テストでも使用されていない問題データがあればそれを出力
/* ----出力する場合は、コメント解除
	$sql = "SELECT DISTINCT hmp.* ".
		" FROM ".T_HANTEI_MS_PROBLEM." hmp ".
		" LEFT JOIN ".T_HANTEI_MS_DEFAULT_PROBLEM." hmdp ".
		" ON hmp.problem_num=hmdp.problem_num".
		" AND hmdp.mk_flg='0'".
		" LEFT JOIN ".T_HANTEI_MS_DEFAULT." hmd ".
		" ON hmd.hantei_default_num=hmdp.hantei_default_num".
		" WHERE hmp.mk_flg='0'".
		" AND hmd.hantei_default_num IS NULL;";
	$result = $cdb->query($sql);
	while ($list_hmp = $cdb->fetch_assoc($result)) {
		$j++;	//従属する問題データ出力のため+1

		$L_CSV[$j]['hantei_default_num'] = "";
		$L_CSV[$j]['hantei_type_name'] = "";
		$L_CSV[$j]['course_name'] = "";
		$L_CSV[$j]['hantei_display_num'] = "";
		$L_CSV[$j]['style_course_name'] = "";
		$L_CSV[$j]['style_stage_name'] = "";
		$L_CSV[$j]['hantei_name'] = "";
		$L_CSV[$j]['hantei_result'] = "";
		$L_CSV[$j]['rand_type'] = "";
		$L_CSV[$j]['clear_rate'] = "";
		$L_CSV[$j]['problem_count'] = "";

		$L_CSV[$j]['limit_time'] = "";
		$L_CSV[$j]['clear_msg'] = "";
		$L_CSV[$j]['break_msg'] = "";

		$L_CSV[$j]['ms_display'] = "";
		$L_CSV[$j]['problem_num'] = "";
		$L_CSV[$j]['disp_sort'] = "";
		$L_CSV[$j]['problem_type'] = "";
		$L_CSV[$j]['sub_display_problem_num'] = "";
		$L_CSV[$j]['form_type'] = "";
		$L_CSV[$j]['question'] = "";
		$L_CSV[$j]['problem'] = "";
		$L_CSV[$j]['voice_data'] = "";
		$L_CSV[$j]['hint'] = "";
		$L_CSV[$j]['explanation'] = "";
		$L_CSV[$j]['standard_time'] = "";
		$L_CSV[$j]['parameter'] = "";
		$L_CSV[$j]['set_difficulty'] = "";
		$L_CSV[$j]['hint_number'] = "";
		$L_CSV[$j]['correct_number'] = "";
		$L_CSV[$j]['clear_number'] = "";
		$L_CSV[$j]['first_problem'] = "";
		$L_CSV[$j]['latter_problem'] = "";
		$L_CSV[$j]['selection_words'] = "";
		$L_CSV[$j]['correct'] = "";
		$L_CSV[$j]['option1'] = "";
		$L_CSV[$j]['option2'] = "";
		$L_CSV[$j]['option3'] = "";
		$L_CSV[$j]['option4'] = "";
		$L_CSV[$j]['option5'] = "";
		$L_CSV[$j]['unit_id'] = "";
		$L_CSV[$j]['english_word_problem'] = "";
		$L_CSV[$j]['error_msg'] = "";
		$L_CSV[$j]['update_time'] = "";
		$L_CSV[$j]['display'] = "";
		$L_CSV[$j]['state'] = "";
		$L_CSV[$j]['sentence_flag'] = "";


		$problem_num = $list_hmp['problem_num'];				//問題管理番号
		$problem_table_type = $list_hmp['problem_table_type'];	//問題テーブルタイプ（1：すらら 2：専用）
		$disp_sort = $list_hmp['disp_sort'];					//表示順
		$standard_time = $list_hmp['standard_time'];			//解答時間
		$problem_type = $list_hmp['problem_type'];		//問題タイプ
		$form_type = $list_hmp['form_type'];			//出題形式
		$question = $list_hmp['question'];				//質問文
		$problem = $list_hmp['problem'];				//問題文
		$voice_data = $list_hmp['voice_data'];			//音声ファイル名
		$hint = $list_hmp['hint'];						//ヒント内容
		$explanation = $list_hmp['explanation'];		//解説文

		$hint_number = $list_hmp['hint_number'];		//ヒント表示回数

		$first_problem = $list_hmp['first_problem'];		//問題前半テキスト
		$latter_problem = $list_hmp['latter_problem'];		//問題後半テキスト
		$selection_words = $list_hmp['selection_words'];	//選択語句、問題テキスト
		$correct = $list_hmp['correct'];			//正解
		$option1 = $list_hmp['option1'];			//option1
		$option2 = $list_hmp['option2'];			//option2
		$option3 = $list_hmp['option3'];			//option3
		$option4 = $list_hmp['option4'];			//option4
		$option5 = $list_hmp['option5'];			//option5
		$error_msg = $list_hmp['error_msg'];		//error_msg
		$display = $list_hmp['display'];			//表示・非表示フラグ　1:表示　2:非表示
		$mk_flg = $list_hmp['mk_flg'];				//無効フラグ

		if ($problem_table_type == "1") {
			$add_problem_data = $problem_num;	//すらら問題
		} else {
			$add_problem_data = "";	//判定テスト問題
		}

		//問題ごとのデータ付加
		$L_CSV[$j]['problem_type'] = $problem_type;
		$L_CSV[$j]['form_type'] = $form_type;
		$L_CSV[$j]['question'] = $question;
		$L_CSV[$j]['problem'] = $problem;
		$L_CSV[$j]['voice_data'] = $voice_data;
		$L_CSV[$j]['hint'] = $hint;
		$L_CSV[$j]['explanation'] = $explanation;
		$L_CSV[$j]['standard_time'] = $standard_time;
		$L_CSV[$j]['hint_number'] = $hint_number;
		$L_CSV[$j]['first_problem'] = $first_problem;
		$L_CSV[$j]['latter_problem'] = $latter_problem;
		$L_CSV[$j]['selection_words'] = $selection_words;
		$L_CSV[$j]['correct'] = $correct;
		$L_CSV[$j]['option1'] = $option1;
		$L_CSV[$j]['option2'] = $option2;
		$L_CSV[$j]['option3'] = $option3;
		$L_CSV[$j]['option4'] = $option4;
		$L_CSV[$j]['option5'] = $option5;
		$L_CSV[$j]['error_msg'] = $error_msg;
		$L_CSV[$j]['display'] = $display;
		$L_CSV[$j]['state'] = $mk_flg;
	}
*/// ----出力停止のコメント化ここまで


	foreach ($L_CSV as $line_num => $line_val) {
		$first_flg = 1;
		foreach ($line_val as $material_key => $material_val) {
			if ($first_flg == 0) {		//１カラムめはデリミタを付加しない
				 $L_CSV_LINE[$line_num] .= $delimiter;
			} else {
				$first_flg = 0;
			}
			//}
			//if (ereg(",",$material_val)||ereg("\"",$material_val)) {
			//	$L_CSV_LINE[$line_num] .= "\"".$material_val."\"";
			//} else {
				$L_CSV_LINE[$line_num] .= $material_val;
			//}
		}
	}
	$csv_line .= implode("\n",$L_CSV_LINE);


	$csv_line = mb_convert_encoding($csv_line,"sjis-win","UTF-8");
	$csv_line = replace_decode_sjis($csv_line);


	return array($csv_line, $ERROR);
}


/**
 * 	マスター＆問題データ csvインポート  ★廃止予定★
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array
 */
function ht_csv_import() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_HANTEI_TYPE;	//add okabe 2013/09/05

	$delimiter = "\t";
	//$delimiter = ",";
	$colums_of_data = 47;	//１行データとして必要な項目数

	$PROC_RESULT = array();	//処理結果レポートを返すための変数

	$CHK_LNUM = array();	//使われなくなった問題番号の突合せチェック用変数

	$ERROR = array();

	if (!$_POST['service_num']) {
		$ERROR[] = "サービスが確認できません。";
		return $ERROR;
	}

	$service_num = $_POST['service_num'];

	$file_name = $_FILES['import_file']['name'];
	$file_tmp_name = $_FILES['import_file']['tmp_name'];
	$file_error = $_FILES['import_file']['error'];

	if (!$file_tmp_name) {
		$ERROR[] = "問題ファイルが指定されておりません。";
	} elseif (!eregi("(.csv)$",$file_name)) {
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
		//$MSG['error'] = $ERROR;
		return $ERROR;
	}

	//現時点で DB にあるデータから、hantei_default_num (in hantei_ms_default)と、
	// disp_sort (in hantei_ms_default_problem) を読み出し、
	// 既存データを把握しておく。
	$L_NUM = array();
	$sql = "SELECT hmd.hantei_default_num, hmdp.problem_num, hmdp.problem_table_type, hmdp.disp_sort".
		" FROM ".T_HANTEI_MS_DEFAULT." hmd".
		" LEFT JOIN ".T_HANTEI_MS_DEFAULT_PROBLEM." hmdp".
		" ON hmd.hantei_default_num=hmdp.hantei_default_num".
		" AND hmdp.mk_flg='0'".
		" WHERE hmd.mk_flg='0'".
		" AND hmd.service_num='".$service_num."'".
		" ORDER BY hantei_default_num;";

	$result = $cdb->query($sql);
	while ($list = $cdb->fetch_assoc($result)) {
		//hantei_default_num 値しか無い場合は、マスターしか存在しない（この判定テストに登録されている問題は０件）
		//
		$hantei_default_num = $list['hantei_default_num'];
		$problem_num = $list['problem_num'];
		$problem_table_type = $list['problem_table_type'];
		$disp_sort = $list['disp_sort'];

		$L_NUM['hantei_default_num'][$hantei_default_num] = "1";
		if (strlen($problem_num) > 0) {
			$L_NUM[$hantei_default_num][$disp_sort] = $problem_table_type.",".$problem_num;
		}
	}
	$L_NUM_RESULT = array();	//処理した問題番号情報の保持

	//結果集計用
	$cnt_all = 0;	//読み込んだデータ件数
	$cnt_err = 0;	//そのうちのエラー行
	$cnt_success = 0;	//更新・新規登録の成功件数
	$cnt_ins_master = 0;	//マスター新規登録件数
	$cnt_upd_master = 0;	//マスターの更新件数
	$cnt_ins_problem = 0;	//問題の新規登録件数
	$cnt_upd_problem = 0;	//問題の更新件数

	//データの読み込み
	$LIST = file($file_tmp_name);
	if ($LIST) {
		$i = 0;
		foreach ($LIST AS $VAL) {
			unset($LINE);
			$VAL = mb_convert_encoding($VAL,"UTF-8","sjis-win");
			$VAL = mb_convert_kana($VAL,"asKV", "UTF-8");
			if ($delimiter != "\t") {
				$VAL = trim($VAL);
			} else {
				$VAL = trim($VAL, " \n\r\0\x0B");
			}

			if (!$VAL || !ereg($delimiter, $VAL)) { continue; }

			$oneline_colums = count(explode($delimiter, $VAL));	//１行データの項目数（タブ区切り数）

			list(
				$LINE['hantei_default_num'],$LINE['hantei_type_name'],$LINE['course_name'],$LINE['hantei_display_num'],
				$LINE['style_course_name'],$LINE['style_stage_name'],
				$LINE['hantei_name'],$LINE['hantei_result'],$LINE['rand_type'],
				$LINE['clear_rate'],$LINE['problem_count'],$LINE['limit_time'],$LINE['clear_msg'],$LINE['break_msg'],
				$LINE['ms_display'],$LINE['problem_num'],$LINE['display_problem_num'],$LINE['problem_type'],
				$LINE['sub_display_problem_num'],$LINE['form_type'],$LINE['question'],$LINE['problem'],
				$LINE['voice_data'],$LINE['hint'],$LINE['explanation'],$LINE['standard_time'],
				$LINE['parameter'],$LINE['set_difficulty'],$LINE['hint_number'],$LINE['correct_number'],$LINE['clear_number'],
				$LINE['first_problem'],$LINE['latter_problem'],$LINE['selection_words'],$LINE['correct'],
				$LINE['option1'],$LINE['option2'],$LINE['option3'],$LINE['option4'],$LINE['option5'],
				$LINE['unit_id'],$LINE['english_word_problem'],
				$LINE['error_msg'],$LINE['update_time'],$LINE['display'],$LINE['state'],$LINE['sentence_flag']
			) = explode($delimiter, $VAL);

			if ($LINE) {
				$i++;	//読み込み行番号

				if ($oneline_colums < $colums_of_data) {
					//項目数不足
					$error_flg = 1;
					//$cnt_err++;		//エラー発生したことをカウント
					$ERROR[] = line_number_format($i)."１行に必要な項目数が足りません。";
					continue;
				}
				$check_no_problem_csv = $oneline_colums - 17;	//csv１行データのうち、問題データとして必要なカラム数

				if (str_replace("\"", "", $LINE['hantei_default_num']) != "hantei_default_num") {	//１行目スキップ
					foreach ($LINE AS $key => $val) {
						if ($val) {
							$val = ereg_replace("\"","&quot;",$val);
							$val = replace_encode_sjis($val);
							//$val = mb_convert_encoding($val,"UTF-8","sjis-win");
							$LINE[$key] = ht_replace_word($val);
						}
					}

					//読み込んだ一行の処理
					$cnt_all++;
					// 入力各項目の正当性チェック
					$DATA_MS_SET = array();
					$DATA_MS_SET['__info']['call_mode']		= "1";		// 0=入力フォーム、1:tsv入力
					$DATA_MS_SET['__info']['line_num']		= $i;		// 行番号...エラーメッセージに付記するため
					$DATA_MS_SET['__info']['check_mode']	= "1";		// チェック時にデータ型を自動調整(半角英数字化、trim処理)するかのスイッチ
														//												0:自動調整しない、1:する
					$DATA_MS_SET['__info']['store_mode']	= "1";		// データ自動調整結果を、$DATA_MS_SET['data']['パラメータ名']へ再格納するかのスイッチ
																		//												0:再格納しない、1:する

					$DATA_MS_SET['data']['service_num']			= $service_num;
					$DATA_MS_SET['data']['hantei_default_num']	= $LINE['hantei_default_num'];	//判定テスト基本情報管理番号
					$DATA_MS_SET['data']['hantei_type_name']	= $LINE['hantei_type_name'];	//判定テスト種別名（コース判定:1、コース内判定:2）
					$DATA_MS_SET['data']['hantei_type_num']		= "";
					$DATA_MS_SET['data']['course_name']			= $LINE['course_name'];				//コース名
					$DATA_MS_SET['data']['course_num']			= "";
					$DATA_MS_SET['data']['hantei_display_num']	= $LINE['hantei_display_num'];		//判定テスト表示番号
					$DATA_MS_SET['data']['style_course_name']	= $LINE['style_course_name'];		//画面表示スタイルの基準とするコース名
					$DATA_MS_SET['data']['style_course_num']	= "";
					$DATA_MS_SET['data']['style_stage_name']	= $LINE['style_stage_name'];		//画面表示スタイルの基準とするステージ名
					$DATA_MS_SET['data']['style_stage_num']		= "";
					$DATA_MS_SET['data']['hantei_name']			= $LINE['hantei_name'];		//判定テスト表示名
					$DATA_MS_SET['data']['hantei_result']		= $LINE['hantei_result'];	//判定結果 文字列
					$DATA_MS_SET['data']['hantei_result_list']	= "";
					$DATA_MS_SET['data']['rand_type']			= $LINE['rand_type'];		//ランダム表示
					$DATA_MS_SET['data']['clear_rate']			= $LINE['clear_rate'];		//判定正解率
					$DATA_MS_SET['data']['problem_count']		= $LINE['problem_count'];	//出題問題数
					$DATA_MS_SET['data']['limit_time']			= $LINE['limit_time'];		//ドリルクリア標準時間
					$DATA_MS_SET['data']['clear_msg']			= $LINE['clear_msg'];		//クリアメッセージ
					$DATA_MS_SET['data']['break_msg']			= $LINE['break_msg'];		//判定結果メッセージ
					$DATA_MS_SET['data']['ms_display']			= $LINE['ms_display'];		//判定テストの表示・非表示
					$DATA_MS_SET['data']['problem_num']			= $LINE['problem_num'];		//問題管理番号 (判定テスト専用問題の場合は入力なし）


					//判定マスター固有部分の入力データをチェック
					list($DATA_MS_SET, $ERROR, $error_flg) = ht_master_check($DATA_MS_SET, $ERROR);
					// $DATA_MS_SET['data'][...]の各値は 半角英数字、カナなどの統一、trim済み

					//問題データ部の指定もあるか
					$add_problem = 0;
				//	if (!preg_match("/,{30}$/", $VAL)) {	//'問題表示番号'カラムを含め、ここから右すべてがカラか確認。
					if (!preg_match("/".$delimiter."{".$check_no_problem_csv."}$/", $VAL)) {	//'問題表示番号'カラムを含め、ここから右すべてがカラか確認。
				//	if (!preg_match("/".$delimiter."{29}$/", $VAL)) {	//'問題表示番号'カラムの除き、その次の項目から右すべてがカラか確認。
						$add_problem = 1;	//問題データの指定有り
					}
					$DATA_MS_SET['data']['add_problem_flg']		= $add_problem;		//問題データの指定有る場合は 1


					//すらら問題番号指定が無く、問題データ部があるか。あればデータ内容をチェック
					if (strlen($LINE['problem_num']) == 0 && $add_problem == 1) {
						//問題データのチェック
						//入力データの正当性をチェックする。異常があれば更新しない。
						$DATA_SET = array();
						$DATA_SET['__info']['call_mode'] = 1;		// 0=入力フォーム、1:csv入力
						$DATA_SET['__info']['line_num'] = $i;		// 行番号(エラーメッセージに付加するもの, csv入力で使用）
						$DATA_SET['__info']['check_mode'] = 1;		// チェック時にデータ型を自動調整(半角英数字化、trim処理)スイッチ(1:する)
						$DATA_SET['__info']['store_mode'] = 1;		// データ自動調整結果を、$DATA_SET['data']['パラメータ名']へ再格納スイッチ(1:する)

						$DATA_SET['data']['display_problem_num'] = $LINE['display_problem_num'];	// disp_sort 値として扱うデータ
						$DATA_SET['data']['problem_type'] = $LINE['problem_type'];
						$DATA_SET['data']['form_type'] = $LINE['form_type'];
						$DATA_SET['data']['question'] = trim($LINE['question']);
						$DATA_SET['data']['problem'] = trim($LINE['problem']);
						$DATA_SET['data']['voice_data'] = trim($LINE['voice_data']);
						$DATA_SET['data']['hint'] = trim($LINE['hint']);
						$DATA_SET['data']['explanation'] = trim($LINE['explanation']);
						//$DATA_SET['data']['answer_time'] = $_POST['answer_time'];
						$DATA_SET['data']['standard_time'] = $LINE['standard_time'];
						$DATA_SET['data']['hint_number'] = $LINE['hint_number'];
						$DATA_SET['data']['first_problem'] = trim($LINE['first_problem']);
						$DATA_SET['data']['latter_problem'] = trim($LINE['latter_problem']);
						$DATA_SET['data']['selection_words'] = trim($LINE['selection_words']);
						$DATA_SET['data']['correct'] = trim($LINE['correct']);
						$DATA_SET['data']['option1'] = $LINE['option1'];
						$DATA_SET['data']['option2'] = $LINE['option2'];
						$DATA_SET['data']['option3'] = $LINE['option3'];
						$DATA_SET['data']['option4'] = $LINE['option4'];
						$DATA_SET['data']['option5'] = $LINE['option5'];
						$DATA_SET['data']['error_msg'] = "";
						$DATA_SET['data']['display'] = $LINE['display'];
						$DATA_SET['data']['state'] = $LINE['state'];

						list($DATA_SET, $ERROR) = ht_test_check($DATA_SET, $ERROR);
						// $DATA_SET['data'][...]の各値は 半角英数字、カナなどの統一、trim済み

					} else {
						//problem_num指定あり、または 問題データの記述なし

						if (strlen($LINE['problem_num']) != 0 || strlen($LINE['display_problem_num']) != 0) {
							//すらら問題番号も、disp_sort も指定いない場合は除く(マスター登録のみのケース)

							if (strlen($LINE['display_problem_num']) == 0) {
								//disp_sort の指示が無ければエラー
								$error_flg = 1;
								//$cnt_err++;		//エラー発生したことをカウント	… 2148行でカウント
								$ERROR[] = line_number_format($i)."問題表示番号が指定されていません。";
								continue;
							}

							if ($add_problem == 1) {
								//csv行で問題データも記述されているか確認
								$error_flg = 1;
								//$cnt_err++;		//エラー発生したことをカウント
								$ERROR[] = line_number_format($i)."問題を記述した行では、問題管理番号を指定することは出来ません。";
								continue;
							} else {
								if (strlen($LINE['problem_num']) == 0) {
									//問題の記述が無いのに、すらら番号の指定もない
									$error_flg = 1;
									//$cnt_err++;		//エラー発生したことをカウント
									$ERROR[] = line_number_format($i)."問題管理番号が指定されていません。";
									continue;
								}
							}

						} else {
							//ONLY MASTER REGIST
						}
					}


					// データの処理
					//
					if (!$error_flg) {		//エラーが起きた行では処理しない
						$csv_hantei_default_num = $DATA_MS_SET['data']['hantei_default_num'];
						//$csv_hantei_type_name = $LINE['hantei_type_name'];

						$csv_problem_num = $LINE['problem_num'];
						$csv_display_problem_num = $LINE['display_problem_num'];

						// hantei_default_num の指定が無い場合、下記の判別をして登録済みマスターか確認する
						// 判定マスターへの新規登録、または 判定テスト種別-コース名-判定テスト表示番号 で一致するデータの更新
						if (strlen($csv_hantei_default_num) == 0) {
							$csv_hantei_default_num = chk_exist_master($i, $service_num, $DATA_MS_SET);
							$DATA_MS_SET['data']['hantei_default_num'] = $csv_hantei_default_num;
						}

						if (strlen($csv_hantei_default_num) == 0) {
							//判定マスターへの新規登録
							list($ERROR_SUB, $new_hantei_default_num) = ht_regist_master($i, $service_num, $DATA_MS_SET);
							if ($ERROR_SUB) {
								$cnt_err++;		//エラー発生したことをカウント
								$error_flg = 1;
								$ERROR = array_merge($ERROR, $ERROR_SUB);
								continue;

							} else {
								$cnt_ins_master++;
								//新規登録した情報を L_NUM に追加する
								$L_NUM['hantei_default_num'][$new_hantei_default_num] = "1";
							}

							//if (!preg_match("/,{31}$/", $VAL)) {
							if (!preg_match("/".$delimiter."{".$check_no_problem_csv."}$/", $VAL)) {
								//問題データの登録処理（問題の新規登録）
								$DATA_SET['_add']['hantei_default_num'] = $new_hantei_default_num;
								// add start okabe 2013/09/05
								$DATA_SET['_add']['service_num'] = $service_num;
								$tmp_hantei_type_name = mb_convert_kana($LINE['hantei_type_name'],"asKV", "UTF-8");
								$tmp_hantei_type_name = trim($tmp_hantei_type_name);
								$tmp_hantei_type = array_search($tmp_hantei_type_name, $L_HANTEI_TYPE);
								if ($tmp_hantei_type == 2) {
									$tmp_course_num = get_course_num($service_num, $LINE['course_name']);
								} else {
									$tmp_course_num = "";
								}
								$DATA_SET['_add']['hantei_type'] = $tmp_hantei_type;
								$DATA_SET['_add']['course_num'] = $tmp_course_num;
								// add end okabe 2013/09/05
								$DATA_SET['_add']['disp_sort'] = $LINE['display_problem_num'];

								list($ERROR_SUB, $L_NUM_RESULT) = ht_regist_problem($i, $service_num, $DATA_SET, $L_NUM_RESULT);
								if ($ERROR_SUB) {
									$cnt_err++;		//エラー発生したことをカウント
									$error_flg = 1;
									$ERROR = array_merge($ERROR, $ERROR_SUB);
									continue;
								} else {
									$cnt_ins_problem++;
								}

							} else {
								//すらら問題の登録指示があるか
								if (strlen($csv_problem_num) > 0 && strlen($csv_display_problem_num) > 0) {
									//すらら問題について hantei_ms_default_problem テーブルに新規登録
									$DATA_SET['_add']['hantei_default_num'] = $new_hantei_default_num;
									// add start okabe 2013/09/05
									$DATA_SET['_add']['service_num'] = $service_num;
									$tmp_hantei_type_name = mb_convert_kana($LINE['hantei_type_name'],"asKV", "UTF-8");
									$tmp_hantei_type_name = trim($tmp_hantei_type_name);
									$tmp_hantei_type = array_search($tmp_hantei_type_name, $L_HANTEI_TYPE);
									if ($tmp_hantei_type == 2) {
										$tmp_course_num = get_course_num($service_num, $LINE['course_name']);
									} else {
										$tmp_course_num = "";
									}
									$DATA_SET['_add']['hantei_type'] = $tmp_hantei_type;
									$DATA_SET['_add']['course_num'] = $tmp_course_num;
									// add end okabe 2013/09/05
									$DATA_SET['_add']['problem_num'] = $LINE['problem_num'];
									$DATA_SET['_add']['disp_sort'] = $LINE['display_problem_num'];

									list($ERROR_SUB, $L_NUM_RESULT) = ht_regist_srlproblem($i, $service_num, $DATA_SET, $L_NUM_RESULT);
									if ($ERROR_SUB) {
										$cnt_err++;		//エラー発生したことをカウント
										$error_flg = 1;
										$ERROR = array_merge($ERROR, $ERROR_SUB);
										continue;
									} else {
										$L_NUM[$new_hantei_default_num][$csv_display_problem_num] = "1,".$csv_problem_num;
									//	$cnt_ins_problem++;
									}
								}
							}

						} else {
							//入力された判定テスト管理番号はあるか
							if ($L_NUM['hantei_default_num'][$csv_hantei_default_num] == "1") {
								//すでにデータがある（マスタ更新処理）
								$ERROR_SUB = ht_update_master($i, $service_num, $DATA_MS_SET);
								if ($ERROR_SUB) {
									$cnt_err++;		//エラー発生したことをカウント
									$error_flg = 1;
									$ERROR = array_merge($ERROR, $ERROR_SUB);
									continue;
								} else {
									$cnt_upd_master++;
								}

							} else {
								//データは無い...新規登録せずエラー扱い
								$cnt_err++;		//エラー発生したことをカウント
								$error_flg = 1;
								$ERROR[] = line_number_format($i)."指定された判定テスト管理番号 ".$csv_hantei_default_num." は存在しません!。";
								continue;
							}


							//csvデータ（１行）の後半をチェック
							if (!preg_match("/".$delimiter."{".$check_no_problem_csv."}$/", $VAL)) {	// 問題データが記載されている場合

								$DATA_SET['_add']['disp_sort'] = $LINE['display_problem_num'];

								// add start okabe 2013/09/05
								$DATA_SET['_add']['service_num'] = $service_num;
								$tmp_hantei_type_name = mb_convert_kana($LINE['hantei_type_name'],"asKV", "UTF-8");
								$tmp_hantei_type_name = trim($tmp_hantei_type_name);
								$tmp_hantei_type = array_search($tmp_hantei_type_name, $L_HANTEI_TYPE);
								if ($tmp_hantei_type == 2) {
									$tmp_course_num = get_course_num($service_num, $LINE['course_name']);
								} else {
									$tmp_course_num = "";
								}
								$DATA_SET['_add']['hantei_type'] = $tmp_hantei_type;
								$DATA_SET['_add']['course_num'] = $tmp_course_num;
								// add end okabe 2013/09/05

								$DATA_SET['_add']['problem_num'] = $LINE['problem_num'];
								$DATA_SET['_add']['hantei_default_num'] = $csv_hantei_default_num;

								//すでに同じ問題表示番号の 'すらら問題'データの指示があるか確認
								if ($L_NUM[$csv_hantei_default_num][$csv_display_problem_num]) {	//display_problem_num は disp_sort と同等値

									//すでに同じ番号の問題あり（問題の更新処理）
									list($ERROR_SUB, $L_NUM_RESULT) = ht_update_problem($i, $service_num, $DATA_SET, $L_NUM_RESULT);

									if ($ERROR_SUB) {
										$cnt_err++;		//エラー発生したことをカウント
										$error_flg = 1;
										$ERROR = array_merge($ERROR, $ERROR_SUB);
										continue;
									} else {
										$cnt_upd_problem++;
									}

								} else {
									//同じ番号の問題はない（問題の新規登録（非すらら問題での更新,再設定も含む）、）
									$DATA_SET['_add']['disp_sort'] = $LINE['display_problem_num'];
									list($ERROR_SUB, $L_NUM_RESULT) = ht_regist_problem($i, $service_num, $DATA_SET, $L_NUM_RESULT);
									if ($ERROR_SUB) {
										$cnt_err++;		//エラー発生したことをカウント
										$error_flg = 1;
										$ERROR = array_merge($ERROR, $ERROR_SUB);
										continue;
									} else {
										$cnt_ins_problem++;
									}

								}


							} else {
								// csvデータ（１行）の後半に、問題データが記載されていない。
								//すらら問題が指定されていたら更新
								$csv_problem_num = $LINE['problem_num'];
								$csv_display_problem_num = $LINE['display_problem_num'];
								if (strlen($csv_problem_num) > 0 && strlen($csv_display_problem_num) > 0) {
									//すでに同じ問題表示番号の 'すらら問題'データの指示があるか確認

									if ($L_NUM[$csv_hantei_default_num][$csv_display_problem_num]) {	//display_problem_num は disp_sort と同等値
										//すでに同じ番号の問題あり（問題の更新処理）
										$DATA_SET['_add']['disp_sort'] = $LINE['display_problem_num'];
										$DATA_SET['_add']['problem_num'] = $LINE['problem_num'];
										$DATA_SET['_add']['hantei_default_num'] = $csv_hantei_default_num;

										// add start okabe 2013/09/05
										$DATA_SET['_add']['service_num'] = $service_num;
										$tmp_hantei_type_name = mb_convert_kana($LINE['hantei_type_name'],"asKV", "UTF-8");
										$tmp_hantei_type_name = trim($tmp_hantei_type_name);
										$tmp_hantei_type = array_search($tmp_hantei_type_name, $L_HANTEI_TYPE);
										if ($tmp_hantei_type == 2) {
											$tmp_course_num = get_course_num($service_num, $LINE['course_name']);
										} else {
											$tmp_course_num = "";
										}
										$DATA_SET['_add']['hantei_type'] = $tmp_hantei_type;
										$DATA_SET['_add']['course_num'] = $tmp_course_num;
										// add end okabe 2013/09/05

										list($ERROR_SUB, $L_NUM_RESULT) = ht_update_problem($i, $service_num, $DATA_SET, $L_NUM_RESULT);

										if ($ERROR_SUB) {
											$cnt_err++;		//エラー発生したことをカウント
											$error_flg = 1;
											$ERROR = array_merge($ERROR, $ERROR_SUB);
											continue;
										} else {
											$cnt_upd_problem++;
										}

									} else {
										//同じ番号の問題はない（すらら問題）
										$DATA_SET['_add']['disp_sort'] = $csv_display_problem_num;
										$DATA_SET['_add']['problem_num'] = $csv_problem_num;
										$DATA_SET['_add']['hantei_default_num'] = $csv_hantei_default_num;
										list($ERROR_SUB, $L_NUM_RESULT) = ht_regist_srlproblem($i, $service_num, $DATA_SET, $L_NUM_RESULT);
										if ($ERROR_SUB) {
											$cnt_err++;		//エラー発生したことをカウント
											$error_flg = 1;
											$ERROR = array_merge($ERROR, $ERROR_SUB);
											continue;
										} else {
											$L_NUM[$csv_hantei_default_num][$csv_display_problem_num] = "1,".$csv_problem_num;
											$cnt_ins_problem++;
										}

									}
								}
							}

						}

					} else {	//if (!$error_flg) {		//エラーが起きた行では処理しない
						$cnt_err++;	//エラー発生行としてカウント
					}

				}

			}

		}	//foreach ($LIST AS $VAL)	//空行は読み飛ばされる

			//参照されていない問題データの掃除
		$sql = "SELECT hmdp.hantei_default_num, hmdp.problem_num, hmdp.problem_table_type, hmdp.disp_sort".
			" FROM ".T_HANTEI_MS_DEFAULT_PROBLEM." hmdp".
			" WHERE hmdp.mk_flg='0';";
		$result = $cdb->query($sql);
		while ($list=$cdb->fetch_assoc($result)) {
			$db_hantei_default_num = $list['hantei_default_num'];
			$db_problem_num = $list['problem_num'];
			$db_problem_table_type =$list['problem_table_type'];
			$db_disp_sort = $list['disp_sort'];

			$check_data = $L_NUM_RESULT[$db_hantei_default_num][$db_disp_sort];

			if (!$check_data || ($check_data != $db_problem_table_type.",".$db_problem_num)) {
				//データは無効（参照されていない）、hantei_ms_default_problem の当該データの mk_flg をセット
				$UPDATE_DATA = array();
				$UPDATE_DATA['mk_flg']		= "1";
				$UPDATE_DATA['mk_tts_id']	= $_SESSION['myid']['id'];
				$UPDATE_DATA['mk_date'] 	= "now()";

				$where = " WHERE hantei_default_num='".$db_hantei_default_num."' AND disp_sort='".$db_disp_sort."' AND mk_flg='0';";
				$ERROR_SUB = $cdb->update(T_HANTEI_MS_DEFAULT_PROBLEM, $UPDATE_DATA, $where);	// hantei_ms_default_problem テーブル
				unset($UPDATE_DATA);
			}
		}


		if ($cnt_all > 0) {
			$PROC_RESULT[] = "データ件数: ".$cnt_all;
			$PROC_RESULT[] = "エラー件数: ".$cnt_err;
			$PROC_RESULT[] = "新規登録したマスターデータ件数: ".$cnt_ins_master;
			$PROC_RESULT[] = "更新したマスターデータ件数: ".$cnt_upd_master;
			$PROC_RESULT[] = "新規登録した問題データ件数: ".$cnt_ins_problem;
			$PROC_RESULT[] = "更新した問題データ件数: ".$cnt_upd_problem;
		}

	}	//if ($LIST)	//データ読み込みループ末端

	return  array($ERROR, $PROC_RESULT);
}



/**
 * hantei_default_num の指定が無い場合、下記の判別をして登録済みマスターか確認する
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * 判定マスターへの新規登録、または 判定テスト種別(hantei_type_num)-コース名(course_num)-判定テスト表示番号(hantei_display_num) で一致するデータの更新
 *
 * @author Azet
 * @param mixed $i (未使用)
 * @param integer $service_num
 * @param array $DATA_MS_SET
 * @return mixed
 */
function chk_exist_master($i, $service_num, $DATA_MS_SET) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$db_hantei_default_num = "";

	$csv_hantei_type_num = $DATA_MS_SET['data']['hantei_type_num'];			//判定テスト種別（コード値）
	$csv_course_num = $DATA_MS_SET['data']['course_num'];					//コース名（コード値）
	$csv_hantei_display_num = $DATA_MS_SET['data']['hantei_display_num'];	//判定テスト表示番号
	//$csv_hantei_name = $DATA_MS_SET['data']['hantei_name'];		//判定テスト名
	if (strlen($csv_hantei_type_num) == 0) { $csv_hantei_type_num = "0"; }
	if (strlen($csv_course_num) == 0) { $csv_course_num = "0"; }
	if (strlen($csv_hantei_display_num) == 0) { $csv_hantei_display_num = "0"; }
	//if (strlen($csv_hantei_name) == 0) { $csv_csv_hantei_name = "xxxxx"; }

	$sql = "SELECT hmd.hantei_default_num".
		" FROM ".T_HANTEI_MS_DEFAULT." hmd".
		" WHERE hmd.mk_flg='0'".
		" AND hmd.service_num='".$service_num."'".
		" AND hmd.hantei_type='".$csv_hantei_type_num."'".
		" AND hmd.course_num='".$csv_course_num."'".
		//" AND hmd.hantei_name='".$csv_hantei_name."'".
		" AND hmd.hantei_display_num='".$csv_hantei_display_num."'".
		";";
	$result = $cdb->query($sql);		//each hantei_ms_default data
	$list = $cdb->fetch_assoc($result);
	if ($list) {
		$db_hantei_default_num = $list['hantei_default_num'];
	}
	return $db_hantei_default_num;
}


/**
 * すらら問題を使うことを登録（問題自体は登録しない）
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param mixed $i (未使用)
 * @param integer $service_num
 * @param array $DATA_SET
 * @param array $L_NUM_RESULT
 * @return array
 */
function ht_regist_srlproblem($i, $service_num, $DATA_SET, $L_NUM_RESULT) {
//echo "ht_regist_srlproblem(),";

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// hantei_ms_default_problem への すらら問題の problem_num と disp_sort を記録
	$hantei_default_num = $DATA_SET['_add']['hantei_default_num'];
	//add start okabe 2013/09/05
	$tmp_service_num = $DATA_SET['_add']['service_num'];
	$tmp_hantei_type = $DATA_SET['_add']['hantei_type'];
	$tmp_course_num = $DATA_SET['_add']['course_num'];
	//add end okabe 2013/09/05
	$problem_num = $DATA_SET['_add']['problem_num'];
	$disp_sort = $DATA_SET['_add']['disp_sort'];
	$myid = $_SESSION['myid']['id'];

	$INSERT_DATA = array();
	$INSERT_DATA[hantei_default_num] = $hantei_default_num;
	$INSERT_DATA[problem_num] = $problem_num;
	//add start okabe 2013/09/05
	$INSERT_DATA[service_num] = $tmp_service_num;
	$INSERT_DATA[hantei_type] = $tmp_hantei_type;
	$INSERT_DATA[course_num] = $tmp_course_num;
	//add end okabe 2013/09/05
	$INSERT_DATA[problem_table_type] = "1";		//すらら問題(=1)
	$INSERT_DATA[disp_sort] = $disp_sort;

	$INSERT_DATA[ins_syr_id]			= "addline";
	$INSERT_DATA[ins_tts_id] 			= $myid;
	$INSERT_DATA[ins_date] 				= "now()";
	$INSERT_DATA[upd_syr_id]			= "addline";
	$INSERT_DATA[upd_tts_id] 			= $myid;
	$INSERT_DATA[upd_date] 				= "now()";

//print_r($INSERT_DATA);echo "<br>";
//echo "Yins<br>";
	$ERROR_SUB = $cdb->insert(T_HANTEI_MS_DEFAULT_PROBLEM, $INSERT_DATA);		// hantei_ms_default_problem テーブルへ格納

	unset($INSERT_DATA);

	$L_NUM_RESULT[$hantei_default_num][$disp_sort] = "1,".$problem_num;		// disp_sort で使用する問題番号を更新

	return array($ERROR_SUB, $L_NUM_RESULT);
}


/**
 * 問題データ(判定テスト固有問題)の新規登録
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param mixed $i
 * @param integer $service_num
 * @param array $DATA_SET
 * @param array $L_NUM_RESULT
 * @return array
 */
function ht_regist_problem($i, $service_num, $DATA_SET, $L_NUM_RESULT) {
//echo "ht_regist_problem(),";

	$problem_num = $DATA_SET['_add']['problem_num'];
	if (strlen($problem_num) == 0) {
		list($ERROR_SUB, $L_NUM_RESULT) = ht_update_problem($i, $service_num, $DATA_SET, $L_NUM_RESULT);

	} else {
		$ERROR_SUB[] = line_number_format($i)."'すらら問題番号'も指定されているため、問題データ登録を中止しました。";
	}
	return array($ERROR_SUB, $L_NUM_RESULT);
}


/**
 * 問題データ(判定テスト固有問題)の更新...problem_num 値の有無により新規登録処理もこの中で行う
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param mixed $i
 * @param integer $service_num
 * @param array $DATA_SET
 * @param array $L_NUM_RESULT
 * @return array
 */
function ht_update_problem($i, $service_num, $DATA_SET, $L_NUM_RESULT) {		//問題データの更新
//echo "ht_update_problem(),";

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$hantei_default_num = $DATA_SET['_add']['hantei_default_num'];
	//add start okabe 2013/09/05
	$tmp_service_num = $DATA_SET['_add']['service_num'];
	$tmp_hantei_type = $DATA_SET['_add']['hantei_type'];
	$tmp_course_num = $DATA_SET['_add']['course_num'];
	//add end okabe 2013/09/05
	$problem_num = $DATA_SET['_add']['problem_num'];
	$disp_sort = $DATA_SET['_add']['disp_sort'];

	$myid = $_SESSION['myid']['id'];

	//problem_num が指定されているか。
	if (strlen($problem_num) > 0) {
		//指定されていれば、すらら問題 ....すでに指定した problem_num の'すらら問題'が、DB内に存在するかはチェック済み
		// hantei_ms_default_problem テーブルに、指定された disp_sort値のデータがあるか確認
		//$problem_table_type = "1";	//すらら問題(=1)
		$sql = "SELECT hmdp.*".
			" FROM ".T_HANTEI_MS_DEFAULT_PROBLEM." hmdp".
			" LEFT JOIN ".T_HANTEI_MS_DEFAULT." hmd".
			" ON hmdp.hantei_default_num=hmd.hantei_default_num".
			" WHERE hmdp.mk_flg='0'".
			" AND hmd.mk_flg='0'".
			" AND hmd.service_num='".$service_num."'".
			" AND hmdp.disp_sort='".$disp_sort."'".
			" AND hmdp.hantei_default_num='".$hantei_default_num."';";

		$result = $cdb->query($sql);		//each hantei_ms_default data
		$list = $cdb->fetch_assoc($result);
		if ($list) {
			//csv行に、問題データ記述あり
			$db_problem_num = $list['problem_num'];
			$db_problem_table_type = $list['problem_table_type'];

			//指定された disp_sort 番号のデータを更新する
			$UPDATE_DATA = array();
			$update_exec = 1;

			//add start okabe 2013/09/05
			//service_num, hantei_type, course_num の変更を確認する
			if ($list['service_num'] != $tmp_service_num) { $UPDATE_DATA['service_num'] = $tmp_service_num; }
			if ($list['hantei_type'] != $tmp_hantei_type) { $UPDATE_DATA['hantei_type'] = $tmp_hantei_type; }
			if ($list['course_num'] != $tmp_course_num) { $UPDATE_DATA['course_num'] = $tmp_course_num; }
			//add end okabe 2013/09/05

			if ($db_problem_table_type == 1) {
				//DB内の現状データは、problem_table_type で'すらら問題'(=1)を指しているので、problem_num だけ変更する
				$UPDATE_DATA['problem_num'] = $problem_num;

				if ($db_problem_num == $problem_num) {
					//データが変化が無い場合は、update しない
					$update_exec = 0;
				}

				$L_NUM_RESULT[$hantei_default_num][$disp_sort] = "1,".$problem_num;		// disp_sort で使用する問題番号を更新

			} else {
				//DB内の現状データが、判定テスト問題(=2)を指しているので、problem_table_type も変更する
				$UPDATE_DATA['problem_num'] = $problem_num;
				$UPDATE_DATA['problem_table_type'] = "1";

				$L_NUM_RESULT[$hantei_default_num][$disp_sort] = "1,".$problem_num;		// disp_sort で使用する問題を変更

			}
			$UPDATE_DATA['upd_syr_id']			= "updateline";
			$UPDATE_DATA['upd_tts_id'] 			= $myid ;
			$UPDATE_DATA['upd_date'] 				= "now()";

			if ($update_exec == 1) {
				$where = " WHERE hantei_default_num='".$hantei_default_num."' AND disp_sort='".$disp_sort."' LIMIT 1;";
//print_r($UPDATE_DATA);echo "<br>";
//echo "Yupd<br>";
				$ERROR_SUB = $cdb->update(T_HANTEI_MS_DEFAULT_PROBLEM, $UPDATE_DATA, $where);	// hantei_ms_default_problem テーブルのデータ更新
			}

			unset($UPDATE_DATA);

		} else {	//データがヒットせず $list で結果が返されない	//csv行に、問題データ記述無し
			$ERROR_SUB[] = line_number_format($i)."DB内の該当する問題表示番号が確認できません。";

		}

	} else {	//if (strlen($problem_num) > 0) {
		//$problem_num が無い場合
		//$problem_table_type = "2";	//判定テスト問題(=2)

		// $DATA_SET に入っている問題データを登録する。
		//disp_sort で指定された非すらら問題が存在すれば、それを更新する
		$sql = "SELECT hmdp.*".
			" FROM ".T_HANTEI_MS_DEFAULT_PROBLEM." hmdp".
			" LEFT JOIN ".T_HANTEI_MS_DEFAULT." hmd".
			" ON hmdp.hantei_default_num=hmd.hantei_default_num".
			" WHERE hmdp.mk_flg='0'".
			" AND hmd.mk_flg='0'".
			" AND hmd.service_num='".$service_num."'".
			" AND hmdp.disp_sort='".$disp_sort."'".
			" AND hmdp.hantei_default_num='".$hantei_default_num."';";
		$result = $cdb->query($sql);
		$list = $cdb->fetch_assoc($result);
		if ($list) {
			//該当レコード(disp_sortが一致)するデータがあったら更新する
			$db_problem_num = $list['problem_num'];
			$db_problem_table_type = $list['problem_table_type'];

			if ($db_problem_table_type == "2") {
				//hantei_ms_problem の $db_problem_num レコードを更新する
				$UPDATE_DATA = array();

				//hantei_default_num から style_course_num を取り出し、それより write_type を決定して返す
				$s_write_type = get_write_type($hantei_default_num);

				//画像ファイル、音声ファイルのファイル名を確認し、problem_num が先頭についていない場合は補正する
				$tmp_question = $DATA_SET['data']['question'];
				list($tmp_question, $ERROR_SUB) 			= ht_img_convert($tmp_question, $db_problem_num, $s_write_type);
				list($UPDATE_DATA['question'], $ERROR_SUB) 	= ht_voice_convert($tmp_question, $db_problem_num, $s_write_type);

				$tmp_problem = $DATA_SET['data']['problem'];
				list($tmp_problem, $ERROR_SUB)			 	= ht_img_convert($tmp_problem, $db_problem_num, $s_write_type);
				list($UPDATE_DATA['problem'], $ERROR_SUB) 	= ht_voice_convert($tmp_problem, $db_problem_num, $s_write_type);

				$tmp_hint = $DATA_SET['data']['hint'];
				list($tmp_hint, $ERROR_SUB) 				= ht_img_convert($tmp_hint, $db_problem_num, $s_write_type);
				list($UPDATE_DATA['hint'], $ERROR_SUB) 		= ht_voice_convert($tmp_hint, $db_problem_num, $s_write_type);

				$tmp_explanation = $DATA_SET['data']['explanation'];
				list($tmp_explanation, $ERROR_SUB)			= ht_img_convert($tmp_explanation, $db_problem_num, $s_write_type);
				list($UPDATE_DATA['explanation'], $ERROR_SUB) = ht_voice_convert($tmp_explanation, $db_problem_num, $s_write_type);

				//form_type4,8,10,11はselection_wordsの変換
				if ($DATA_SET['data']['form_type'] == 4 || $DATA_SET['data']['form_type'] == 8 || $DATA_SET['data']['form_type'] == 10 || $DATA_SET['data']['form_type'] == 11) {	//edit okabe 2013/09/11
					$tmp_selection_words = $DATA_SET['data']['selection_words'];
					list($tmp_selection_words, $ERROR_SUB)			 = ht_img_convert($tmp_selection_words, $db_problem_num, $s_write_type);
					list($UPDATE_DATA['selection_words'], $ERROR_SUB) = ht_voice_convert($tmp_selection_words, $db_problem_num, $s_write_type);
				}
				$tmp_voice_data = $DATA_SET['data']['voice_data'];
				list($tmp_voice_data, $ERROR_SUB) = copy_default_voice_data($tmp_voice_data, $db_problem_num, $s_write_type);


				$UPDATE_DATA['standard_time'] = $DATA_SET['data']['standard_time'];
				$UPDATE_DATA['problem_type'] = $DATA_SET['data']['problem_type'];
				$UPDATE_DATA['form_type'] = $DATA_SET['data']['form_type'];
				//$UPDATE_DATA[question] = $DATA_SET['data']['question'];
				//$UPDATE_DATA[problem] = $DATA_SET['data']['problem'];
				//$UPDATE_DATA[voice_data] = $DATA_SET['data']['voice_data'];
				$UPDATE_DATA['voice_data'] = $tmp_voice_data;
				//$UPDATE_DATA[hint] = $DATA_SET['data']['hint'];
				//$UPDATE_DATA[explanation] = $DATA_SET['data']['explanation'];
				$UPDATE_DATA['hint_number'] = $DATA_SET['data']['hint_number'];
				$UPDATE_DATA['first_problem'] = $DATA_SET['data']['first_problem'];
				$UPDATE_DATA['latter_problem'] = $DATA_SET['data']['latter_problem'];
				//$UPDATE_DATA[selection_words] = $DATA_SET['data']['selection_words'];
				$UPDATE_DATA['correct'] = $DATA_SET['data']['correct'];
				$UPDATE_DATA['option1'] = $DATA_SET['data']['option1'];
				$UPDATE_DATA['option2'] = $DATA_SET['data']['option2'];
				$UPDATE_DATA['option3'] = $DATA_SET['data']['option3'];
				$UPDATE_DATA['option4'] = $DATA_SET['data']['option4'];
				$UPDATE_DATA['option5'] = $DATA_SET['data']['option5'];
				$UPDATE_DATA['error_msg'] = $DATA_SET['data']['error_msg'];
				$UPDATE_DATA['display'] = $DATA_SET['data']['display'];

				//$UPDATE_DATA['problem_num'] = $problem_num;
				$UPDATE_DATA['upd_syr_id']	= "updateline";
				$UPDATE_DATA['upd_tts_id']	= $myid ;
				$UPDATE_DATA['upd_date'] 		= "now()";

				$where = " WHERE problem_num='".$db_problem_num."' AND mk_flg='0' LIMIT 1;";
				$ERROR_SUB = $cdb->update(T_HANTEI_MS_PROBLEM, $UPDATE_DATA, $where);	// hantei_ms_problem テーブルのデータ更新

				unset($UPDATE_DATA);
			}
			$problem_num = $db_problem_num;

		} else {
			//該当レコードが無かったら新規登録する
			//問題を新規登録する

			$sql = "SELECT MAX(problem_num) AS max_num ".
				" FROM ".T_HANTEI_MS_PROBLEM;
			if ($result = $cdb->query($sql)) {
				$list = $cdb->fetch_assoc($result);
			}
			if ($list['max_num']) {
				$add_problem_num = $list['max_num'] + 1;
			} else {
				$add_problem_num = 1;
			}

			$INSERT_DATA = array();

			//hantei_default_num から style_course_num を取り出し、それより write_type を決定して返す
			$s_write_type = get_write_type($hantei_default_num);

			//画像ファイル、音声ファイルのファイル名変換とフォルダ作成
			$tmp_question = $DATA_SET['data']['question'];
			list($tmp_question, $ERROR_SUB) 			= ht_img_convert($tmp_question, $add_problem_num, $s_write_type);
			list($INSERT_DATA['question'], $ERROR_SUB) 	= ht_voice_convert($tmp_question, $add_problem_num, $s_write_type);

			$tmp_problem = $DATA_SET['data']['problem'];
			list($tmp_problem, $ERROR_SUB)			 	= ht_img_convert($tmp_problem, $add_problem_num, $s_write_type);
			list($INSERT_DATA['problem'], $ERROR_SUB) 	= ht_voice_convert($tmp_problem, $add_problem_num, $s_write_type);

			$tmp_hint = $DATA_SET['data']['hint'];
			list($tmp_hint, $ERROR_SUB) 				= ht_img_convert($tmp_hint, $add_problem_num, $s_write_type);
			list($INSERT_DATA['hint'], $ERROR_SUB) 		= ht_voice_convert($tmp_hint, $add_problem_num, $s_write_type);

			$tmp_explanation = $DATA_SET['data']['explanation'];
			list($tmp_explanation, $ERROR_SUB)			= ht_img_convert($tmp_explanation, $add_problem_num, $s_write_type);
			list($INSERT_DATA['explanation'], $ERROR_SUB) = ht_voice_convert($tmp_explanation, $add_problem_num, $s_write_type);

			//form_type4,8,10,11のみselection_wordsの変換
			if ($DATA_SET['data']['form_type'] == 4 || $DATA_SET['data']['form_type'] == 8 || $DATA_SET['data']['form_type'] == 10 || $DATA_SET['data']['form_type'] == 11) {	//edit 2013/09/11
				$tmp_selection_words = $DATA_SET['data']['selection_words'];
				list($tmp_selection_words, $ERROR_SUB)			 = ht_img_convert($tmp_selection_words, $problem_num, $s_write_type);
				list($INSERT_DATA['selection_words'], $ERROR_SUB) = ht_voice_convert($tmp_selection_words, $problem_num, $s_write_type);
			}
			$tmp_voice_data = $DATA_SET['data']['voice_data'];
			list($tmp_voice_data, $ERROR_SUB) = copy_default_voice_data($tmp_voice_data, $add_problem_num, $s_write_type);

			$INSERT_DATA['standard_time'] = $DATA_SET['data']['standard_time'];
			$INSERT_DATA['problem_type'] = $DATA_SET['data']['problem_type'];
			$INSERT_DATA['form_type'] = $DATA_SET['data']['form_type'];
			//$INSERT_DATA[question] = $DATA_SET['data']['question'];
			//$INSERT_DATA[problem] = $DATA_SET['data']['problem'];
			//$INSERT_DATA[voice_data] = $DATA_SET['data']['voice_data'];
			$INSERT_DATA['voice_data'] = $tmp_voice_data;
			//$INSERT_DATA[hint] = $DATA_SET['data']['hint'];
			//$INSERT_DATA[explanation] = $DATA_SET['data']['explanation'];
			$INSERT_DATA['hint_number'] = $DATA_SET['data']['hint_number'];
			$INSERT_DATA['first_problem'] = $DATA_SET['data']['first_problem'];
			$INSERT_DATA['latter_problem'] = $DATA_SET['data']['latter_problem'];
			//$INSERT_DATA[selection_words] = $DATA_SET['data']['selection_words'];
			$INSERT_DATA['correct'] = $DATA_SET['data']['correct'];
			$INSERT_DATA['option1'] = $DATA_SET['data']['option1'];
			$INSERT_DATA['option2'] = $DATA_SET['data']['option2'];
			$INSERT_DATA['option3'] = $DATA_SET['data']['option3'];
			$INSERT_DATA['option4'] = $DATA_SET['data']['option4'];
			$INSERT_DATA['option5'] = $DATA_SET['data']['option5'];
			$INSERT_DATA['error_msg'] = $DATA_SET['data']['error_msg'];
			$INSERT_DATA['display'] = $DATA_SET['data']['display'];

			$INSERT_DATA['ins_syr_id']			= "addline";
			$INSERT_DATA['ins_tts_id'] 			= $myid;
			$INSERT_DATA['ins_date'] 			= "now()";
			$INSERT_DATA['upd_syr_id']			= "addline";
			$INSERT_DATA['upd_tts_id'] 			= $myid;
			$INSERT_DATA['upd_date'] 			= "now()";
			$ERROR_SUB = $cdb->insert(T_HANTEI_MS_PROBLEM, $INSERT_DATA);		// hantei_ms_problem テーブルへ問題を格納

			$problem_num = 0;
			if (!$ERROR_SUB) {
				$problem_num = $cdb->insert_id();
			}
			unset($INSERT_DATA);
		}


		//hantei_ms_default_problem に problem_num と disp_sort を登録する
		if ($problem_num > 0) {
			//同じ problem_num と disp_sort のデータがあるかチェック
			//同じデータがあれば、update する、なければ insert する

			$sql = "SELECT hmdp.*".
				" FROM ".T_HANTEI_MS_DEFAULT_PROBLEM." hmdp".
				" LEFT JOIN ".T_HANTEI_MS_DEFAULT." hmd".
				" ON hmdp.hantei_default_num=hmd.hantei_default_num".
				" WHERE hmdp.mk_flg='0'".
				" AND hmd.mk_flg='0'".
				" AND hmd.service_num='".$service_num."'".
				" AND hmdp.disp_sort='".$disp_sort."'".
				" AND hmdp.hantei_default_num='".$hantei_default_num."';";
			$result = $cdb->query($sql);
			$list = $cdb->fetch_assoc($result);

			if ($list) {
				//該当レコード(disp_sortが一致)するデータがあったら更新する
				//$db_problem_num = $list['problem_num'];
				//$db_problem_table_type = $list['problem_table_type'];

				$UPDATE_DATA = array();
				$UPDATE_DATA['problem_num'] = $problem_num;

				//add start okabe 2013/09/05
				$UPDATE_DATA['service_num'] = $DATA_SET['_add']['service_num'];
				$UPDATE_DATA['hantei_type'] = $DATA_SET['_add']['hantei_type'];
				$UPDATE_DATA['course_num'] = $DATA_SET['_add']['course_num'];
				//add end okabe 2013/09/05

				$UPDATE_DATA['upd_syr_id']	= "updateline";
				$UPDATE_DATA['upd_tts_id']	= $myid ;
				$UPDATE_DATA['upd_date'] 		= "now()";

				$where = " WHERE hantei_default_num='".$hantei_default_num."' AND disp_sort='".$disp_sort."' LIMIT 1;";
//print_r($UPDATE_DATA);echo "<br>";
//echo "Zupd<br>";
				$ERROR_SUB = $cdb->update(T_HANTEI_MS_DEFAULT_PROBLEM, $UPDATE_DATA, $where);	// hantei_ms_default_problem テーブルのデータ更新

				unset($UPDATE_DATA);

			} else {
				//無ければ hantei_ms_default_problem への problem_num と disp_sort の記録
				$INSERT_DATA = array();

				$INSERT_DATA['hantei_default_num'] = $hantei_default_num;

				//add start okabe 2013/09/05
				$INSERT_DATA['service_num'] = $DATA_SET['_add']['service_num'];
				$INSERT_DATA['hantei_type'] = $DATA_SET['_add']['hantei_type'];
				$INSERT_DATA['course_num'] = $DATA_SET['_add']['course_num'];
				//add end okabe 2013/09/05

				$INSERT_DATA['problem_num']			= $problem_num;
				$INSERT_DATA['problem_table_type']	= "2";		//判定テスト問題（非すらら問題）
				$INSERT_DATA['disp_sort']			= $disp_sort;

				$INSERT_DATA['ins_syr_id']		= "addline";
				$INSERT_DATA['ins_tts_id'] 		= $myid;
				$INSERT_DATA['ins_date'] 		= "now()";
				$INSERT_DATA['upd_syr_id']		= "addline";
				$INSERT_DATA['upd_tts_id'] 		= $myid;
				$INSERT_DATA['upd_date'] 		= "now()";
//print_r($INSERT_DATA);echo "<br>";
//echo "Zins<br>";
				$ERROR_SUB = $cdb->insert(T_HANTEI_MS_DEFAULT_PROBLEM, $INSERT_DATA);		// hantei_ms_default_problem テーブルへ格納

				unset($INSERT_DATA);

			}

			$L_NUM_RESULT[$hantei_default_num][$disp_sort] = "2,".$problem_num;		// disp_sort で使用する問題を変更

		} else {
			 // 前処理でエラー時($problem_num が入っていない場合)
		}

	}

	//problem_num がカラ（インポートした判定テストデータを格納）

	return array($ERROR_SUB, $L_NUM_RESULT);
}


/**
 * マスタへの新規登録
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param mixed $i
 * @param integer $service_num
 * @param array $DATA_MS_SET
 * @return array
 */
function ht_regist_master($i, $service_num, $DATA_MS_SET) {
//echo "ht_regist_master(),";

	//新規登録のため $hantei_default_num  は未定義

	$ERROR_SUB = array();

	$INSERT_DATA = array();
	$INSERT_DATA['service_num']		 	= $DATA_MS_SET['data']['service_num'];
	$INSERT_DATA['hantei_type']			= $DATA_MS_SET['data']['hantei_type_num'];		//判定テスト種別コード 1|2
	$INSERT_DATA['course_num']			= $DATA_MS_SET['data']['course_num'];			//コース番号
	$INSERT_DATA['list_num']			= "";			//insert後,付与された hantei_default_num の値を格納しなおす
	$INSERT_DATA['hantei_display_num']	= $DATA_MS_SET['data']['hantei_display_num'];	//判定テスト表示番号

	//$INSERT_DATA[style_stage_num]		= $DATA_MS_SET['data']['style_stage_num'];		//基準とするステージのコード値
	//$INSERT_DATA[style_course_num]		= $DATA_MS_SET['data']['style_course_num'];		//基準とするコースのコード値
	$INSERT_DATA['style_course_num']	= "5";			//基準とするコースのコード値（当面は、固定値）
	$INSERT_DATA['style_stage_num']		= "0";			//基準とするステージのコード値（当面は、固定値）

	$INSERT_DATA['hantei_name']			= $DATA_MS_SET['data']['hantei_name'];			//判定名
	$INSERT_DATA['rand_type']			= $DATA_MS_SET['data']['rand_type'];			//ランダム表示
	$INSERT_DATA['clear_rate']			= $DATA_MS_SET['data']['clear_rate'];			//判定正解率
	$INSERT_DATA['problem_count']		= $DATA_MS_SET['data']['problem_count'];		//出題問題数
	$INSERT_DATA['limit_time']			= $DATA_MS_SET['data']['limit_time'];			//ドリルクリア標準時間
	$INSERT_DATA['clear_msg']			= $DATA_MS_SET['data']['clear_msg'];			//クリアメッセージ
	$INSERT_DATA['break_msg']			= $DATA_MS_SET['data']['break_msg'];			//判定結果メッセージ
	$INSERT_DATA['display']				= $DATA_MS_SET['data']['ms_display'];			//表示・非表示

	$myid = $_SESSION['myid']['id'];

	//list($ERROR_SUB, $new_hantei_default_num) = add_hantei_test_master($INSERT_DATA, $DATA_MS_SET['data']['hantei_result_list'], $myid);		//del okabe 2013/10/04
	list($ERROR_SUB, $new_hantei_default_num) = add_hantei_test_master($INSERT_DATA, $DATA_MS_SET['data']['hantei_result_list'], $DATA_MS_SET['data']['hantei_result_list_last'], $myid);		//add okabe 2013/10/04

	return array($ERROR_SUB, $new_hantei_default_num);
}


/**
 * マスタデータの更新
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param mixed $i
 * @param integer $service_num
 * @param array $DATA_MS_SET
 * @return array エラーの場合
 */
function ht_update_master($i, $service_num, $DATA_MS_SET) {
//echo "ht_update_master(),";
	//複数問題があると複数コールされるため、データ内容に相違があれば更新する

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$ERROR_SUB = array();

	$UPDATE_DATA = array();
	//$UPDATE_DATA[service_num]		 	= $DATA_MS_SET['data']['service_num'];
	$hantei_default_num = $DATA_MS_SET['data']['hantei_default_num'];
	//$UPDATE_DATA[hantei_default_num]	= $hantei_default_num;			//判定テスト管理番号
	$UPDATE_DATA['hantei_type']			= $DATA_MS_SET['data']['hantei_type_num'];		//判定テスト種別コード 1|2
	$UPDATE_DATA['course_num']			= $DATA_MS_SET['data']['course_num'];			//コース番号
	$UPDATE_DATA['hantei_display_num']	= $DATA_MS_SET['data']['hantei_display_num'];	//判定テスト表示番号

	//$UPDATE_DATA[style_course_num]		= $DATA_MS_SET['data']['style_course_num'];		//基準とするコースのコード値
	//$UPDATE_DATA[style_stage_num]		= $DATA_MS_SET['data']['style_stage_num'];		//基準とするステージのコード値
	$UPDATE_DATA['style_course_num']	= "5";		//基準とするコースのコード値（当面は、固定値）
	$UPDATE_DATA['style_stage_num']		= "0";		//基準とするステージのコード値（当面は、固定値）

	$UPDATE_DATA['hantei_name']			= $DATA_MS_SET['data']['hantei_name'];			//判定名
	$UPDATE_DATA['rand_type']			= $DATA_MS_SET['data']['rand_type'];			//ランダム表示
	$UPDATE_DATA['clear_rate']			= $DATA_MS_SET['data']['clear_rate'];			//判定正解率
	$UPDATE_DATA['problem_count']		= $DATA_MS_SET['data']['problem_count'];		//出題問題数
	$UPDATE_DATA['limit_time']			= $DATA_MS_SET['data']['limit_time'];			//ドリルクリア標準時間
	$UPDATE_DATA['clear_msg']			= $DATA_MS_SET['data']['clear_msg'];			//クリアメッセージ
	$UPDATE_DATA['break_msg']			= $DATA_MS_SET['data']['break_msg'];			//判定結果メッセージ
	$UPDATE_DATA['display']				= $DATA_MS_SET['data']['ms_display'];			//表示・非表示


	//現在のデータ内容を取得し比較
	$sql = "SELECT * ".
		" FROM ".T_HANTEI_MS_DEFAULT." hmd".
		" WHERE hantei_default_num='".$hantei_default_num."'".
		" AND service_num='".$service_num."'".
		" AND mk_flg='0';";
	$result = $cdb->query($sql);		//each hantei_ms_default data
	$list = $cdb->fetch_assoc($result);

	if ($list) {
		//各項目に違いがあるか確認
		$diff_flag = 0;
		if ($list['hantei_type'] != $UPDATE_DATA['hantei_type']) { $diff_flag = 1; }
		if ($list['course_num'] != $UPDATE_DATA['course_num'] && $diff_flag == 0) { $diff_flag = 1; }
		if ($list['hantei_display_num'] != $UPDATE_DATA['hantei_display_num'] && $diff_flag == 0) { $diff_flag = 1; }
		if ($list['style_course_num'] != $UPDATE_DATA['style_course_num'] && $diff_flag == 0) { $diff_flag = 1; }
		if ($list['style_stage_num'] != $UPDATE_DATA['style_stage_num'] && $diff_flag == 0) { $diff_flag = 1; }
		if ($list['hantei_name'] != $UPDATE_DATA['hantei_name'] && $diff_flag == 0) { $diff_flag = 1; }
		if ($list['rand_type'] != $UPDATE_DATA['rand_type'] && $diff_flag == 0) { $diff_flag = 1; }
		if ($list['clear_rate'] != $UPDATE_DATA['clear_rate'] && $diff_flag == 0) { $diff_flag = 1; }
		if ($list['problem_count'] != $UPDATE_DATA['problem_count'] && $diff_flag == 0) { $diff_flag = 1; }
		if ($list['limit_time'] != $UPDATE_DATA['limit_time'] && $diff_flag == 0) { $diff_flag = 1; }
		if ($list['clear_msg'] != $UPDATE_DATA['clear_msg'] && $diff_flag == 0) { $diff_flag = 1; }
		if ($list['break_msg'] != $UPDATE_DATA['break_msg'] && $diff_flag == 0) { $diff_flag = 1; }
		if ($list['display'] != $UPDATE_DATA['display'] && $diff_flag == 0) { $diff_flag = 1; }

		if ($diff_flag == 1) {	//相違箇所があれば更新する
			$UPDATE_DATA['upd_syr_id']			= "updateline";
			$UPDATE_DATA['upd_tts_id'] 			= $_SESSION['myid']['id'];	//$myid;
			$UPDATE_DATA['upd_date'] 				= "now()";

			$where = " WHERE hantei_default_num='".$hantei_default_num."' AND service_num='".$service_num."' LIMIT 1;";
			$ERROR_SUB = $cdb->update(T_HANTEI_MS_DEFAULT, $UPDATE_DATA, $where);	// hantei_ms_default テーブルのデータ更新

		}

		if (!$ERROR_SUB) {	//hantei_ms_defaultの更新でエラーがあれば中止
			//hantei_ms_default更新
			$hantei_result_list = $DATA_MS_SET['data']['hantei_result_list'];	//判定結果 (hantei_ms_break_layer テーブル)

			//$ERROR_SUB = update_hantei_ms_break_layer($hantei_default_num, $hantei_result_list);		//del okabe 2013/10/04
			$ERROR_SUB = update_hantei_ms_break_layer($hantei_default_num, 0, $hantei_result_list);		//add okabe 2013/10/04
		}

		//add start okabe 2013/10/04		//最終テスト合格時の判定結果
		if (!$ERROR_SUB) {	//これまでの更新でエラーがあれば中止
			//hantei_ms_default_last 更新
			$hantei_result_list_last = $DATA_MS_SET['data']['hantei_result_list_last'];	//最終テスト合格時の判定結果 (hantei_ms_break_layer テーブル)
			$ERROR_SUB = update_hantei_ms_break_layer($hantei_default_num, 1, $hantei_result_list_last);
		}
		//add end okabe 2013/10/04

	} else {
		$ERROR_SUB[] = line_number_format($i)."判定テスト管理番号".$hantei_default_num." のデータは存在しません。";
	}

	return $ERROR_SUB;
}


/**
 * 行番号の表示形式設定
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param mixed $number
 * @return string
 */
function line_number_format($number) {
	return $number.": ";
}



/**
 * 履歴作成
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $VAL
 * @return string
 */
function REPORT($VAL) {
	$html = "<br/>";
	$html .= "<font color=\"#0000ff\">\n";
	foreach ($VAL AS $val) {
		$html .= "・".$val."<br/>\n";
	}
	$html .= "</font>\n";
	return $html;
}



/**
 * hantei_ms_break_layer の更新処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $hantei_default_num
 * @param mixed $break_layer_type
 * @param array $HANTEI_ONE_DATA_L
 * @return array エラーの場合
 */
function update_hantei_ms_break_layer($hantei_default_num, $break_layer_type, $HANTEI_ONE_DATA_L) {	//add okabe 2013/10/04
//function update_hantei_ms_break_layer($hantei_default_num, $HANTEI_ONE_DATA_L) {	//del okabe 2013/10/04

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//入力： $break_layer_type 0:不合格時の判定結果, 1:最終テスト合格時の判定結果
	//現在のデータを取り出し、過不足をチェック
	$CHK_HANTEI_L = array();	//チェック用配列(DBに存在し、mk_flg==0 のデータ)。
		//配列の添字は、コース管理No-ステージ管理No-レッスン管理No-ユニット管理No-ドリル管理No。
		//その値は 初期値０、で存在したら１にし、最終的に０だったレコードは削除（無効フラグを立てる）

	$sql  = "SELECT ".
		" hantei_ms_break_layer.course_num,".
		" hantei_ms_break_layer.stage_num,".
		" hantei_ms_break_layer.lesson_num,".
		" hantei_ms_break_layer.unit_num,".
		" hantei_ms_break_layer.block_num".
		" FROM " . T_HANTEI_MS_BREAK_LAYER . " hantei_ms_break_layer" .
		" WHERE hantei_ms_break_layer.mk_flg='0' AND hantei_ms_break_layer.hantei_default_num='".$hantei_default_num."'".	//edit okabe 2013/10/04
		" AND break_layer_type='".$break_layer_type."';";				//add okabe 2013/10/04
	$result = $cdb->query($sql);
	while ($list=$cdb->fetch_assoc($result)) {
		$chk_key = $list['course_num']."-".$list['stage_num'].
			"-".$list['lesson_num']."-".$list['unit_num']."-".$list['block_num'];
		//	"-".$list['lesson_num']."-".$list['unit_num'];	//block_num を比較対象に含めない場合
		$CHK_HANTEI_L[$chk_key] = 0;
	}


	//HANTEI_ONE_DATA_L のデータと順に照合
	foreach ($HANTEI_ONE_DATA_L as $key => $DATA_VAL_L) {
		$chk_key = $DATA_VAL_L['course_num']."-".$DATA_VAL_L['stage_num'].
			"-".$DATA_VAL_L['lesson_num']."-".$DATA_VAL_L['unit_num']."-".$DATA_VAL_L['block_num'];
		//	"-".$DATA_VAL_L['lesson_num']."-".$DATA_VAL_L['unit_num'];	//block_num を比較対象に含めない場合

		if (array_key_exists($chk_key, $CHK_HANTEI_L)) {
			//データが存在すれば CHK_HANTEI_L[$chk_key] = 1 にして、update 扱いにする
			$CHK_HANTEI_L[$chk_key] = 1;

		} else {
			//データが存在しない場合は、新規追加して CHK_HANTEI_L[$chk_key] = 2 にし、insert 扱いにする
			$CHK_HANTEI_L[$chk_key] = 2;
		}
	}
	//データをupdateするものには、CHK_HANTEI_L[$chk_key] = 1
	//データが存在せず、insertは、CHK_HANTEI_L[$chk_key] = 2
	//データを削除する場合には、  CHK_HANTEI_L[$chk_key] = 0

	//全件が変更無しの場合(全件 CHK_HANTEI_L[$chk_key] = 1)は、一切更新しない
	$update_exec = 0;
	foreach ($CHK_HANTEI_L as $key => $val) {
		if ($val == 0 || $val == 2) { $update_exec = 1; }
	}

	if ($update_exec == 1) {	//変更箇所がある場合には更新する

		//hantei_ms_default テーブルへの データ処理 登録・更新・削除(set mk_flg)
		foreach ($CHK_HANTEI_L as $key => $val) {
			//$keyは、コース管理No-ステージ管理No-レッスン管理No-ユニット管理No-ドリル管理No の書式
			$upd_keys = split("-", $key);
			$UPDATE_DATA = array();

			if ($val == 0) {
				//入力データが無くなった物は、mk_flg を立てる
				$UPDATE_DATA['mk_flg']		= "1";			//無効フラグ（1：削除）
				$UPDATE_DATA['mk_tts_id'] 	= $_SESSION['myid']['id'];
				$UPDATE_DATA['mk_date'] 	= "now()";
				$where = " WHERE hantei_default_num='".$hantei_default_num."'".
					" AND course_num='".$upd_keys[0]."'".
					" AND stage_num='".$upd_keys[1]."'".
					" AND lesson_num='".$upd_keys[2]."'".
					" AND unit_num='".$upd_keys[3]."'".
					" AND block_num='".$upd_keys[4]."'".					//edit okabe 2013/10/04
					" AND break_layer_type='".$break_layer_type."';";		//add okabe 2013/10/04
				$ERROR_SUB = $cdb->update(T_HANTEI_MS_BREAK_LAYER, $UPDATE_DATA, $where);	// hantei_ms_default テーブル更新(無効フラグセット)

			} elseif ($val == 1) {
				//すでにデータがある場合は、update 処理
				$UPDATE_DATA['upd_syr_id']	= "updateline";
				$UPDATE_DATA['upd_tts_id'] 	= $_SESSION['myid']['id'];
				$UPDATE_DATA['upd_date'] 	= "now()";
				$where = " WHERE hantei_default_num='".$hantei_default_num."'".
					" AND course_num='".$upd_keys[0]."'".
					" AND stage_num='".$upd_keys[1]."'".
					" AND lesson_num='".$upd_keys[2]."'".
					" AND unit_num='".$upd_keys[3]."'".
					" AND block_num='".$upd_keys[4]."'".					//edit okabe 2013/10/04
					" AND break_layer_type='".$break_layer_type."';";		//add okabe 2013/10/04
				$ERROR_SUB = $cdb->update(T_HANTEI_MS_BREAK_LAYER, $UPDATE_DATA, $where);	// hantei_ms_default テーブル更新

			} elseif ($val == 2) {
				//新規データのときは insert 処理
				$INSERT_DATA['hantei_default_num']	= $hantei_default_num;	//判定テスト基本情報管理番号
				$INSERT_DATA['break_layer_type']	= $break_layer_type;	//0:不合格時の判定結果| 1:最終テスト合格時の判定結果
				$INSERT_DATA['course_num']		 	= $upd_keys[0];			//コース管理番号
				$INSERT_DATA['stage_num']	 		= $upd_keys[1];			//ステージ管理番号
				$INSERT_DATA['lesson_num']		 	= $upd_keys[2];			//レッスン管理番号
				$INSERT_DATA['unit_num']	 		= $upd_keys[3];			//ユニット管理番号
				$INSERT_DATA['block_num']		 	= $upd_keys[4];			//ドリル管理番号
				$INSERT_DATA['mk_flg']				= "0";
				$INSERT_DATA['ins_syr_id']			= "addline";
				$INSERT_DATA['ins_tts_id'] 			= $_SESSION['myid']['id'];
				$INSERT_DATA['ins_date'] 			= "now()";
				$INSERT_DATA['upd_syr_id']			= "addline";
				$INSERT_DATA['upd_tts_id'] 			= $_SESSION['myid']['id'];
				$INSERT_DATA['upd_date'] 			= "now()";
				$ERROR_SUB = $cdb->insert(T_HANTEI_MS_BREAK_LAYER, $INSERT_DATA);		// hantei_ms_default テーブルへ格納
			}
			unset($UPDATE_DATA);

		}

	}
	return $ERROR_SUB;
}


/**
 * 判定テストマスターの新規作成
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $insert_data
 * @param array $hantei_result_list
 * @param array $hantei_result_list_last
 * @param string $myid
 * @return array
 */
function add_hantei_test_master($insert_data, $hantei_result_list, $hantei_result_list_last, $myid) {		//add okabe 2013/10/04
//function add_hantei_test_master($insert_data, $hantei_result_list, $myid) {		//del okabe 2013/10/04

//insert_dataに入っている情報↓
/*	$INSERT_DATA = array();
	$INSERT_DATA[service_num]		 	= $service_num;		//サービス管理番号
	$INSERT_DATA[hantei_type]		 	= $hantei_type;		//判定単位（1：コース判定 2：コース内判定）
	$INSERT_DATA[course_num]		 	= $course_num;		//コース管理番号		"コース判定"のときの値は 0
	$INSERT_DATA[list_num] 				= "0";				//ソート番号(初期利用無)	※続く処理で、hantei_default_num の値に更新する
	$INSERT_DATA[hantei_display_num] 	= $hantei_disp_num;	//判定テスト表示番号（ソート利用）
	$INSERT_DATA[style_course_num] 		= $style_course_num;//スタイル基準コース番号
	$INSERT_DATA[style_stage_num] 		= $style_stage_num;	//スタイル基準ステージ番号
	$INSERT_DATA[hantei_name] 			= $hantei_name;		//判定名（範囲名）
	$INSERT_DATA[hantei_default_key] 	= $hantei_default_key;				//hantei_default_key 判定テスト管理キー取り扱い
	$INSERT_DATA[rand_type] 			= $random;			//ランダム出題（1：する[ランダム出題]　2：しない[順番出題]）
	$INSERT_DATA[clear_rate] 			= $clear_rate;		//判定正解率
	$INSERT_DATA[problem_count] 		= $problem_count;	//出題問題数
	$INSERT_DATA[limit_time] 			= $limit_time;		//ドリルクリアー標準時間
	$INSERT_DATA[clear_msg] 			= $clear_msg;		//クリアーメッセージ
	$INSERT_DATA[break_msg] 			= $break_msg;		//判定結果メッセージ
	$INSERT_DATA[display] 				= $display;			//表示（1：表示 2：非表示）
	$INSERT_DATA[ins_syr_id]			= "addline";
	$INSERT_DATA[ins_tts_id] 			= $_SESSION['myid']['id'];
	$INSERT_DATA[ins_date] 				= "now()";
	$INSERT_DATA[upd_syr_id]			= "addline";
	$INSERT_DATA[upd_tts_id] 			= $_SESSION['myid']['id'];
	$INSERT_DATA[upd_date] 				= "now()";
*/

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$new_hantei_default_num = 0;	//新たに autoincrement で与えられる値
	$hantei_default_num = 0;

	$insert_data['ins_syr_id']			= "addline";
	$insert_data['ins_tts_id'] 			= $myid;
	$insert_data['ins_date'] 			= "now()";
	$insert_data['upd_syr_id']			= "addline";
	$insert_data['upd_tts_id'] 			= $myid;
	$insert_data['upd_date'] 			= "now()";

	$ERROR = $cdb->insert(T_HANTEI_MS_DEFAULT, $insert_data);		// hantei_ms_default テーブルへ格納

	if (!$ERROR) {
		// list_num 値を hantei_default_num と同じ値にする
		$last_id = $cdb->insert_id();
		$new_hantei_default_num = $last_id;

		$UPDATE_DATA = array();

		$UPDATE_DATA['list_num'] = $last_id;
		$hantei_default_num = $last_id;

		$where = " WHERE mk_flg=0 AND hantei_default_num=".$last_id;
		$ERROR = $cdb->update(T_HANTEI_MS_DEFAULT, $UPDATE_DATA, $where);		// hantei_ms_default テーブルの list_num 更新

		unset($UPDATE_DATA);

		// hantei_ms_break_layer への格納
		//$HANTEI_ONE_DATA_L = $_SESSION['UPDATE']['HANTEI_RESULT_L'];
		$HANTEI_ONE_DATA_L = $hantei_result_list;
		if ($hantei_default_num > 0 && !$ERROR && count($HANTEI_ONE_DATA_L) > 0) {

			foreach ($HANTEI_ONE_DATA_L as $key => $DATA_VAL_L) {
				$INSERT_DATA2 = array();
				$INSERT_DATA2['hantei_default_num']	= $hantei_default_num;			//判定テスト基本情報管理番号
				$INSERT_DATA2['break_layer_type']	= 0;							//0:不合格判定時,1:最終テスト合格時		//add okabe 2013/10/04
				$INSERT_DATA2['course_num']		 	= $DATA_VAL_L['course_num'];	//コース管理番号
				$INSERT_DATA2['stage_num']		 	= $DATA_VAL_L['stage_num'];		//ステージ管理番号
				$INSERT_DATA2['lesson_num']		 	= $DATA_VAL_L['lesson_num'];	//レッスン管理番号
				$INSERT_DATA2['unit_num']	 		= $DATA_VAL_L['unit_num'];		//ユニット管理番号
				$INSERT_DATA2['block_num']		 	= $DATA_VAL_L['block_num'];		//ドリル管理番号
				$INSERT_DATA2['mk_flg']				= "0";
				$INSERT_DATA2['ins_syr_id']			= "addline";
				$INSERT_DATA2['ins_tts_id'] 		= $myid;		//$_SESSION['myid']['id'];
				$INSERT_DATA2['ins_date'] 			= "now()";
				$INSERT_DATA2['upd_syr_id']			= "addline";
				$INSERT_DATA2['upd_tts_id'] 		= $myid;		//$_SESSION['myid']['id'];
				$INSERT_DATA2['upd_date'] 			= "now()";

				$ERROR = $cdb->insert(T_HANTEI_MS_BREAK_LAYER, $INSERT_DATA2);		// hantei_ms_break_layer テーブルへ格納

				unset($UPDATE_DATA2);

			}

		}


		//add start okabe 2013/10/04
		// hantei_ms_break_layer への格納
		$HANTEI_ONE_DATA_LAST_L = $hantei_result_list_last;
		if ($hantei_default_num > 0 && !$ERROR && count($HANTEI_ONE_DATA_LAST_L) > 0) {

			foreach ($HANTEI_ONE_DATA_LAST_L as $key => $DATA_VAL_L) {
				$INSERT_DATA2 = array();
				$INSERT_DATA2['hantei_default_num']	= $hantei_default_num;			//判定テスト基本情報管理番号
				$INSERT_DATA2['break_layer_type']	= 1;							//0:不合格判定時,1:最終テスト合格時		//add okabe 2013/10/04
				$INSERT_DATA2['course_num']		 	= $DATA_VAL_L['course_num'];	//コース管理番号
				$INSERT_DATA2['stage_num']		 	= $DATA_VAL_L['stage_num'];		//ステージ管理番号
				$INSERT_DATA2['lesson_num']		 	= $DATA_VAL_L['lesson_num'];	//レッスン管理番号
				$INSERT_DATA2['unit_num']	 		= $DATA_VAL_L['unit_num'];		//ユニット管理番号
				$INSERT_DATA2['block_num']		 	= $DATA_VAL_L['block_num'];		//ドリル管理番号
				$INSERT_DATA2['mk_flg']				= "0";
				$INSERT_DATA2['ins_syr_id']			= "addline";
				$INSERT_DATA2['ins_tts_id'] 		= $myid;		//$_SESSION['myid']['id'];
				$INSERT_DATA2['ins_date'] 			= "now()";
				$INSERT_DATA2['upd_syr_id']			= "addline";
				$INSERT_DATA2['upd_tts_id'] 		= $myid;		//$_SESSION['myid']['id'];
				$INSERT_DATA2['upd_date'] 			= "now()";

				$ERROR = $cdb->insert(T_HANTEI_MS_BREAK_LAYER, $INSERT_DATA2);		// hantei_ms_break_layer テーブルへ格納

				unset($UPDATE_DATA2);

			}

		}
		//add end okabe 2013/10/04


		/* //格納形式を変更
		if (!$ERROR) {
			//データディレクトリの準備
			//MATERIAL_HANTEI_IMG_DIR/$hantei_default_num/ ディレクトリの存在確認、無ければ作成
			$chk_path = MATERIAL_HANTEI_IMG_DIR.$hantei_default_num."/";
			if (!file_exists($chk_path)) {
				if (!mkdir($chk_path)) {
					$ERROR[] = "データ格納ディレクトリが作成できませんでした(image,".$hantei_default_num.")。";
				}
			}

			//MATERIAL_HANTEI_VOICE_DIR/$hantei_default_num/ ディレクトリの存在確認、無ければ作成
			$chk_path = MATERIAL_HANTEI_VOICE_DIR.$hantei_default_num."/";
			if (!file_exists($chk_path)) {
				if (!mkdir($chk_path)) {
					$ERROR[] = "データ格納ディレクトリが作成できませんでした(voice,".$hantei_default_num.")。";
				}
			}
		}
		*/

	}
//		unset($INSERT_DATA);
	return array($ERROR, $new_hantei_default_num);
}


/**
 * hantei_default_num から style_course_num を取り出し、それより write_type を決定して返す
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param $s_hantei_test_num
 * @return mixed
 */
function get_write_type($s_hantei_test_num) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$s_write_type = "1";
	$sql = "SELECT course.write_type".
		" FROM ".T_HANTEI_MS_DEFAULT." hmd".
		" LEFT JOIN course ".
		" ON course.course_num=hmd.style_course_num".
		" AND course.state='0'".
		" WHERE hmd.mk_flg='0'".
		" AND hmd.hantei_default_num='".$s_hantei_test_num."'";
	$result = $cdb->query($sql);
	$list = $cdb->fetch_assoc($result);
	if ($list) {
		$s_write_type = $list['write_type'];
	}
	return $s_write_type;
}


/**
 * service_num から write_type を決定して返す(複数の type がある場合は、何も返さない)
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $s_service_num
 * @return string
 */
function get_write_type_from_svc($s_service_num) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$s_write_type = "";
	$diff_flag = 0;
	if ($s_service_num > 0) {
		$sql = "SELECT course.write_type".
			" FROM ".T_SERVICE_COURSE_LIST." service_course_list, " .T_COURSE. " course ".
			" WHERE course.course_num = service_course_list.course_num".
			" AND course.state = 0".
			" AND service_course_list.mk_flg = 0".
			" AND service_course_list.service_num ='".$s_service_num."';";
		if ($result = $cdb->query($sql)) {
			while ($list = $cdb->fetch_assoc($result)) {
				if (strlen($s_write_type) > 0) {
					if ($list['write_type'] != $s_write_type) { $diff_flag = 1; }
				} else {
					$s_write_type = $list['write_type'];
				}
			}
		}
		if ($diff_flag == 1) { $s_write_type = ""; }
	}
	return $s_write_type;
}


/**
 * ftp各ディレクトリの表示組立て
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param string $s_write_type
 * @return string HTML
 */
function ftp_dir_html($s_write_type) {
	$img_ftp = FTP_URL."hantei_img/";
	$voice_ftp = FTP_URL."hantei_voice/";
	$temp_img_ftp = FTP_TEST_URL."hantei_img/";
	$temp_voice_ftp = FTP_TEST_URL."hantei_voice/";
	if (strlen($s_write_type) > 0) {
		$temp_img_ftp .= $s_write_type."/";
		$temp_voice_ftp .= $s_write_type."/";
	}


	//$html = "<br>\n"; //dell hirose 2018/04/24 FTPをブラウザからのエクスプローラーで開けない不具合対応
	$html = FTP_EXPLORER_MESSAGE; //add hirose 2018/04/24 FTPをブラウザからのエクスプローラーで開けない不具合対応
	$html .= "<br>\n"; //add hirose 2018/04/24 FTPをブラウザからのエクスプローラーで開けない不具合対応
	
	$html .= "<a href=\"".$img_ftp."\" target=\"_blank\">テンポラリー画像フォルダー($temp_img_ftp)</a><br>\n";
	$html .= "<a href=\"".$voice_ftp."\" target=\"_blank\">テンポラリー音声フォルダー($temp_voice_ftp)</a><br>\n";
	$html .= "<a href=\"".$img_ftp."\" target=\"_blank\">画像フォルダー($img_ftp)</a><br>\n";
	$html .= "<a href=\"".$voice_ftp."\" target=\"_blank\">音声フォルダー($voice_ftp)</a><br><br>\n";

	return $html;
}


/**
 * test_problem からコピーしたもので、
 * file_exist と copy の個所にて ディレクトリ指定で
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * $_SESSION['t_practice']['course_num'] が使えないため修正して使用。
 * @author Azet
 * @param string $problem
 * @param integer $problem_num
 * @param string $s_write_type
 * @return array
 */
function ht_img_convert($problem, $problem_num, $s_write_type) {
//echo "*ht_img_convert ".$problem.", ".$problem_num."<br/>";
	preg_match_all("|\[!IMG=(.*)!\]|U",$problem,$L_IMG_LIST);
	if (isset($L_IMG_LIST[1][0])) {
		//フォルダ作成
		$ERROR = ht_dir_maker(MATERIAL_HANTEI_IMG_DIR, $problem_num, 100);

		$dir_num = (floor($problem_num / 100) * 100) + 1;
		$dir_num = sprintf("%07d",$dir_num);
		$dir_name = MATERIAL_HANTEI_IMG_DIR.$dir_num."/";
		foreach ($L_IMG_LIST[1] AS $key => $val) {
			if (ereg("^".$problem_num."_",$val)) {
				copy_default_resource(MATERIAL_HANTEI_DEF_IMG_DIR.$s_write_type."/".$val, $dir_name.$val);
				continue;
			}
			$problem = preg_replace("/".$val."/",$problem_num."_".$val,$problem);
			copy_default_resource(MATERIAL_HANTEI_DEF_IMG_DIR.$s_write_type."/".$val, $dir_name.$problem_num."_".$val);
		}
	}
	return array($problem,$ERROR);
}

/**
 * test_problem からコピーしたもので、
 * file_exist と copy の個所にて ディレクトリ指定で
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * $_SESSION['t_practice']['course_num'] が使えないため修正して使用。
 * @author Azet
 * @param string $voice
 * @param integer $problem_num
 * @param string $s_write_type
 * @return array
 */
function ht_voice_convert($voice, $problem_num, $s_write_type) {
//echo "*ht_voice_convert ".$voice.", ".$problem_num."<br/>";
	preg_match_all("|\[!VOICE=(.*)!\]|U",$voice,$L_VOICE_LIST);

	if (isset($L_VOICE_LIST[1][0])) {
		//フォルダ作成
		$ERROR = ht_dir_maker(MATERIAL_HANTEI_VOICE_DIR,$problem_num,100);
		$dir_num = (floor($problem_num / 100) * 100) + 1;
		$dir_num = sprintf("%07d",$dir_num);
		$dir_name = MATERIAL_HANTEI_VOICE_DIR.$dir_num."/";
		foreach ($L_VOICE_LIST[1] AS $key => $val) {
			if (ereg("^".$problem_num."_",$val)) {
				copy_default_resource(MATERIAL_HANTEI_DEF_VOICE_DIR.$s_write_type."/".$val, $dir_name.$val);
				continue;
			}
			$voice = preg_replace("/".$val."/",$problem_num."_".$val,$voice);
			copy_default_resource(MATERIAL_HANTEI_DEF_VOICE_DIR.$s_write_type."/".$val, $dir_name.$problem_num."_".$val);
		}
	}
	return array($voice,$ERROR);
}


/**
 * voice_data既定
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param string $tmp_voice_data
 * @param integer $add_problem_num
 * @param string $s_write_type
 * @return array
 */
function copy_default_voice_data($tmp_voice_data, $add_problem_num, $s_write_type) {
	$ERROR_SUB =array();

	if ($tmp_voice_data) {
		$ERROR_SUB = ht_dir_maker(MATERIAL_HANTEI_VOICE_DIR, $add_problem_num, 100);
		$dir_num = (floor($add_problem_num / 100) * 100) + 1;
		$dir_num = sprintf("%07d", $dir_num);
		$dir_name = MATERIAL_HANTEI_VOICE_DIR.$dir_num."/";
		//デフォルト音声データのコピー
		if (!ereg("^".$add_problem_num."_", $tmp_voice_data)) {
			copy_default_resource(MATERIAL_HANTEI_DEF_VOICE_DIR.$s_write_type."/".$tmp_voice_data, $dir_name.$add_problem_num."_".$tmp_voice_data);
			$tmp_voice_data	= $add_problem_num."_".$tmp_voice_data;
		} else {
			copy_default_resource(MATERIAL_HANTEI_DEF_VOICE_DIR.$s_write_type."/".$tmp_voice_data, $dir_name.$tmp_voice_data);
		}
	}

	return array($tmp_voice_data, $ERROR_SUB);
}


/**
 * 問題のディレクトリを作成
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param string $dir
 * @param integer $problem_num
 * @param integer $num
 * @return array エラーの場合
 */
function ht_dir_maker($dir,$problem_num,$num) {
	$dir_num = (floor($problem_num / $num) * $num) + 1;
	$dir_num = sprintf("%07d",$dir_num);
	$dir_name = $dir.$dir_num."/";

	$ERROR = ht_dir_make($dir_name);

	return $ERROR;
}


/**
 * デフォルトファイル(画像、音声)のコピー
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param string $from_rsc
 * @param string $to_rsc
 */
function copy_default_resource($from_rsc, $to_rsc) {
//echo $from_rsc." -> ".$to_rsc."<br/>";
	if (file_exists($from_rsc)) {
		copy($from_rsc, $to_rsc);
	}
}




/**
 * ディレクトリ生成
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param string $dir_name
 * @param mixed $mode=0777
 * @return array エラーの場合
 */
function ht_dir_make($dir_name,$mode=0777) {
	if (!file_exists($dir_name)) {
		if (!mkdir($dir_name,$mode)) {
			$ERROR[] = $dir_name." のフォルダーが作成できませんでした。";
		}
		@chmod($dir_name,$mode);
	}
	return $ERROR;
}


//--------------------------------------------------------------------------


/**
 * マスターデータ csv出力情報整形
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @date 2013/09/13
 * @author Azet
 * @param array $L_CSV_COLUMN
 * @param integer $sel_service_num
 * @param mixed $head_mode='1'
 * @param mixed $csv_mode='1'
 * @return array
 */
function ht_make_csv_master($L_CSV_COLUMN, $sel_service_num, $head_mode='1', $csv_mode='1') {
	// $sel_service_num ターゲットのサービス番号

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_HANTEI_TYPE;

	//define(DISABLE_MSG_OUTPUT, 1);	//clear_msg, break_msg カラムの出力しない(=1)
									// 定義は test_practice_hantei_test_master.php 最初のルーチンで。

	$delimiter = "\t";
	//$delimiter = ",";

	$csv_line = "";

	if (!is_array($L_CSV_COLUMN)) {
		$ERROR[] = "CSV抽出項目が設定されていません。";
		return array($csv_line,$ERROR);
	}
	//	head line (一行目)
	foreach ($L_CSV_COLUMN as $key => $val) {
		if ($head_mode == 1) { $head_name = $key; }
		elseif ($head_mode == 2) { $head_name = $val; }
		if (!DISABLE_MSG_OUTPUT) {
			$csv_line .= "\"".$head_name."\"".$delimiter;
		} else {
			if ($head_name != "clear_msg" && $head_name != "break_msg") {
				$csv_line .= "\"".$head_name."\"".$delimiter;
			}
		}
	}
	$csv_line .= "\n";


	//hantei_ms_default から判定テスト(hantei_default_num)ごとに抽出し、マスター情報を用意
	$L_CSV = array();
	$L_CSV_LINE = array();
	$j=0;	//データ行番号用

	$sql_hmd = "SELECT hmd.hantei_default_num, hmd.service_num ".
		" FROM ".T_HANTEI_MS_DEFAULT." hmd ".
		" WHERE hmd.mk_flg='0'".
		" AND hmd.service_num='".$sel_service_num."' ORDER BY hmd.hantei_default_num;";
	if ($result_hmd = $cdb->query($sql_hmd)) {	//all valid hantei_default_num (in hantei_ms_default)

		while ($list_hmd = $cdb->fetch_assoc($result_hmd)) {
			$hantei_default_num = $list_hmd['hantei_default_num'];
			$service_num = $list_hmd['service_num'];

			//判定テストごとにデータを整理
			$sql = "SELECT hmd.hantei_type, hmd.course_num, hmd.hantei_display_num,".
				" hmd.style_course_num, hmd.style_stage_num,".
				" hmd.hantei_name, hmd.rand_type, hmd.clear_rate, hmd.problem_count,".
				" hmd.limit_time, hmd.clear_msg, hmd.break_msg,".
				" hmd.display".
				" FROM ".T_HANTEI_MS_DEFAULT." hmd ".
				" WHERE hmd.mk_flg='0'".
				" AND hmd.hantei_default_num='".$hantei_default_num."'".
				" ORDER BY hmd.hantei_display_num;";
			$result = $cdb->query($sql);		//each hantei_ms_default data
			$list = $cdb->fetch_assoc($result);

			if ($list) {

				//"判定結果"情報の組み立て(hantei_ms_break_layerから)
				$sql_hmbl = "SELECT ".
					" hmbl.course_num,".
					" hmbl.stage_num,".
					" hmbl.lesson_num,".
					" hmbl.unit_num,".
					" hmbl.block_num".
					" FROM " . T_HANTEI_MS_BREAK_LAYER . " hmbl" .
					" WHERE hmbl.mk_flg='0' AND hmbl.hantei_default_num='".$hantei_default_num."'".		//edit okabe 2013/10/04
					" AND break_layer_type='0';";		//add okabe 2013/10/04
				$result_hmbl = $cdb->query($sql_hmbl);
				$hantei_result = "";	//結果格納変数
				while ($list_hmbl=$cdb->fetch_assoc($result_hmbl)) {
					$hantei_result = rebuild_hantei_result($service_num, $list_hmbl, $hantei_result);
				}

				//add start okabe 2013/10/04
				//"最終テスト合格時の判定結果"情報の組み立て(hantei_ms_break_layerから)
				$hantei_result_last = "";	//結果格納変数"最終テスト合格時の判定結果"
				if ($list['hantei_type'] == 2) {	//コース内判定の場合のみ
					$sql_hmbl = "SELECT ".
						" hmbl.course_num,".
						" hmbl.stage_num,".
						" hmbl.lesson_num,".
						" hmbl.unit_num,".
						" hmbl.block_num".
						" FROM " . T_HANTEI_MS_BREAK_LAYER . " hmbl" .
						" WHERE hmbl.mk_flg='0' AND hmbl.hantei_default_num='".$hantei_default_num."'".	//edit okabe 2013/10/04
						" AND break_layer_type='1';";		//add okabe 2013/10/04
					$result_hmbl = $cdb->query($sql_hmbl);
					while ($list_hmbl=$cdb->fetch_assoc($result_hmbl)) {
						$hantei_result_last = rebuild_hantei_result($service_num, $list_hmbl, $hantei_result_last);
					}
				}
				//add end okabe 2013/10/04

				$j++;	//マスターデータ出力のため+1

				//$L_CSV[$j]['hantei_default_num'] = "";
				$L_CSV[$j]['hantei_type_name'] = "";
				$L_CSV[$j]['course_name'] = "";
				$L_CSV[$j]['hantei_display_num'] = "";
				//$L_CSV[$j]['style_course_name'] = "";
				//$L_CSV[$j]['style_stage_name'] = "";
				$L_CSV[$j]['hantei_name'] = "";
				$L_CSV[$j]['hantei_result'] = "";
				$L_CSV[$j]['hantei_result_last'] = "";		//add okabe 2013/10/04
				$L_CSV[$j]['rand_type'] = "";
				$L_CSV[$j]['clear_rate'] = "";
				$L_CSV[$j]['problem_count'] = "";
				$L_CSV[$j]['limit_time'] = "";
				if (!DISABLE_MSG_OUTPUT) {
					$L_CSV[$j]['clear_msg'] = "";
					$L_CSV[$j]['break_msg'] = "";
				}
				$L_CSV[$j]['ms_display'] = "";

				$course_num = $list['course_num'];
				//$L_CSV[$j]['hantei_default_num'] = $hantei_default_num;		//判定テスト管理番号
				$L_CSV[$j]['hantei_type_name'] = $L_HANTEI_TYPE[$list['hantei_type']];		//判定テスト種別（文字列->コード番号に変換の前の状態）
				$course_name = get_course_name($service_num, $course_num);
				$L_CSV[$j]['course_name'] = $course_name;		//コース名（文字列->コード番号に変換の前の状態）
				$hantei_display_num = $list['hantei_display_num'];	//判定テスト表示番号
				$L_CSV[$j]['hantei_display_num'] = $hantei_display_num;

				//$style_course_num = $list['style_course_num'];	//スタイル基準コース番号
				//$style_course_name = get_course_name_sub($style_course_num) ;	//スタイル基準コース名
				//$L_CSV[$j]['style_course_name'] = $style_course_name;

				//$style_stage_num = $list['style_stage_num'];	//スタイル基準ステージ番号
				//$style_stage_name = get_stage_name($style_stage_num);	//スタイル基準ステージ名
				//$L_CSV[$j]['style_stage_name'] = $style_stage_name;

				$hantei_name = $list['hantei_name'];				//判定テスト表示名
				$L_CSV[$j]['hantei_name'] = $hantei_name ;
				$L_CSV[$j]['hantei_result'] = $hantei_result;			//判定結果
				$L_CSV[$j]['hantei_result_last'] = $hantei_result_last;		//最終テスト合格時の判定結果	//add okabe 2013/10/04

				$rand_type = $list['rand_type'];	//ランダム表示
				$L_CSV[$j]['rand_type'] = $rand_type ;
				$clear_rate = $list['clear_rate'];	//判定正解率
				$L_CSV[$j]['clear_rate'] = $clear_rate ;
				$problem_count = $list['problem_count'];	//出題問題数
				$L_CSV[$j]['problem_count'] = $problem_count ;

				$limit_time = $list['limit_time'];		//ドリルクリア標準時間
				$L_CSV[$j]['limit_time'] = $limit_time ;
				if (!DISABLE_MSG_OUTPUT) {
					$clear_msg = $list['clear_msg'];		//クリアメッセージ
					$L_CSV[$j]['clear_msg'] = $clear_msg ;
					$break_msg = $list['break_msg'];		//判定結果メッセージ
					$L_CSV[$j]['break_msg'] = $break_msg ;
				}

				$ms_display = $list['display'];	//マスター 表示・非表示
				$L_CSV[$j]['ms_display'] = $ms_display ;
			}
		}
	}

	foreach ($L_CSV as $line_num => $line_val) {
		$first_flg = 1;
		foreach ($line_val as $material_key => $material_val) {
			if ($first_flg == 0) {		//１カラムめはデリミタを付加しない
				 $L_CSV_LINE[$line_num] .= $delimiter;
			} else {
				$first_flg = 0;
			}
			if (!DISABLE_MSG_OUTPUT) {
				$L_CSV_LINE[$line_num] .= $material_val;
			} else {
				if ($material_key != "clear_msg" && $material_key != "break_msg") {
					$L_CSV_LINE[$line_num] .= $material_val;
				}
			}
		}
	}
	$csv_line .= implode("\n",$L_CSV_LINE);

	//	del 2015/01/07 yoshizawa 課題要望一覧No.400対応 下に新規で作成
	//$csv_line = mb_convert_encoding($csv_line,"sjis-win","UTF-8");
	//$csv_line = replace_decode_sjis($csv_line);
	//----------------------------------------------------------------

	//	add 2015/01/07 yoshizawa 課題要望一覧No.400対応

		//++++++++++++++++++++++//
		//	$_POST['exp_list']	//
		//	1 => SJIS			//
		//	2 => Unicode		//
		//++++++++++++++++++++++//
	//	utf-8で出力
	if ( $_POST['exp_list'] == 2 ) {
		//	Unicode選択時には特殊文字のみ変換
		$csv_line = replace_decode($csv_line);

	//	SJISで出力
	} else {
		$csv_line = mb_convert_encoding($csv_line,"sjis-win","UTF-8");
		$csv_line = replace_decode_sjis($csv_line);

	}
	//-------------------------------------------------

	return array($csv_line, $ERROR);
}


/**
 * 問題データ csv出力情報整形
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $L_CSV_COLUMN
 * @param integer $sel_service_num
 * @param mixed $head_mode='1'
 * @param mixed $csv_mode='1'
 * @return array
 */
function ht_make_csv_problem($L_CSV_COLUMN, $sel_service_num, $head_mode='1', $csv_mode='1') {
	// $sel_service_num ターゲットのサービス番号

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_HANTEI_TYPE;

	//define(DISABLE_SERVICE_NAME, 1);	//service_name カラムの出力しない(=1)
										// 定義は test_practice_hantei_test_problem.php 最初のルーチンで。
	$delimiter = "\t";
	//$delimiter = ",";

	$csv_line = "";

	if (!is_array($L_CSV_COLUMN)) {
		$ERROR[] = "CSV抽出項目が設定されていません。";
		return array($csv_line,$ERROR);
	}
	//	head line (一行目)
	foreach ($L_CSV_COLUMN as $key => $val) {
		if ($head_mode == 1) { $head_name = $key; }
		elseif ($head_mode == 2) { $head_name = $val; }
		if (!DISABLE_SERVICE_NAME) {
			$csv_line .= "\"".$head_name."\"".$delimiter;
		} else {
			if ($head_name != "ms_service_name") {
				$csv_line .= "\"".$head_name."\"".$delimiter;
			}
		}
	}
	$csv_line .= "\n";


	//hantei_ms_default から判定テスト(hantei_default_num)ごとに抽出し、従属する問題構成および問題データを用意
	$L_CSV = array();
	$L_CSV_LINE = array();
	$j=0;	//データ行番号用

	$sql_hmd = "SELECT hmd.hantei_default_num, hmd.service_num ".
		" FROM ".T_HANTEI_MS_DEFAULT." hmd ".
		" WHERE hmd.mk_flg='0'".
		" AND hmd.service_num='".$sel_service_num."' ORDER BY hmd.hantei_default_num;";
	if ($result_hmd = $cdb->query($sql_hmd)) {	//all valid hantei_default_num (in hantei_ms_default)
		while ($list_hmd = $cdb->fetch_assoc($result_hmd)) {
			$hantei_default_num = $list_hmd['hantei_default_num'];
			$service_num = $list_hmd['service_num'];

			//判定テストごとにデータを整理
			$sql = "SELECT hmd.hantei_type, hmd.course_num, hmd.hantei_display_num,".
				" hmd.style_course_num, hmd.style_stage_num,".
				" hmd.hantei_name, hmd.rand_type, hmd.clear_rate, hmd.problem_count,".
				" hmd.limit_time, hmd.clear_msg, hmd.break_msg,".
				" hmd.display".
				" FROM ".T_HANTEI_MS_DEFAULT." hmd ".
				" WHERE hmd.mk_flg='0'".
				" AND hmd.hantei_default_num='".$hantei_default_num."';";
			$result = $cdb->query($sql);		//each hantei_ms_default data
			$list = $cdb->fetch_assoc($result);

			if ($list) {

				//このマスターに属する問題データがあるか確認
				$sql_hmp = "SELECT count(*) AS problem_count".
					" FROM ".T_HANTEI_MS_DEFAULT_PROBLEM." hmdp ".
					" LEFT JOIN ".T_HANTEI_MS_PROBLEM." hmp ".
					" ON (hmp.problem_num=hmdp.problem_num AND hmdp.problem_table_type='2')".
					" AND hmp.mk_flg='0'".
					" WHERE hmdp.mk_flg='0'".
					" AND hmdp.hantei_default_num='".$hantei_default_num."';";
				if ($result_hmp = $cdb->query($sql_hmp)) {
					$list_hmp = $cdb->fetch_assoc($result_hmp);
				}

				if ($list_hmp['problem_count']) {	//問題データが存在するマスターのみ処理する

					$j++;	//マスターデータ出力のため+1

					//$L_CSV[$j]['hantei_default_num'] = "";
					$L_CSV[$j]['hantei_type_name'] = "";
					$L_CSV[$j]['course_name'] = "";
					$L_CSV[$j]['hantei_display_num'] = "";

					if (!DISABLE_SERVICE_NAME) {
						$L_CSV[$j]['ms_service_name'] = "";
					}
					$L_CSV[$j]['ms_course_name'] = "";

					$L_CSV[$j]['problem_num'] = "";
					$L_CSV[$j]['disp_sort'] = "";
					$L_CSV[$j]['problem_type'] = "";
					$L_CSV[$j]['sub_display_problem_num'] = "";
					$L_CSV[$j]['form_type'] = "";
					$L_CSV[$j]['question'] = "";
					$L_CSV[$j]['problem'] = "";
					$L_CSV[$j]['voice_data'] = "";
					$L_CSV[$j]['hint'] = "";
					$L_CSV[$j]['explanation'] = "";
					$L_CSV[$j]['standard_time'] = "";
					$L_CSV[$j]['parameter'] = "";
					$L_CSV[$j]['set_difficulty'] = "";
					$L_CSV[$j]['hint_number'] = "";
					$L_CSV[$j]['correct_number'] = "";
					$L_CSV[$j]['clear_number'] = "";
					$L_CSV[$j]['first_problem'] = "";
					$L_CSV[$j]['latter_problem'] = "";
					$L_CSV[$j]['selection_words'] = "";
					$L_CSV[$j]['correct'] = "";
					$L_CSV[$j]['option1'] = "";
					$L_CSV[$j]['option2'] = "";
					$L_CSV[$j]['option3'] = "";
					$L_CSV[$j]['option4'] = "";
					$L_CSV[$j]['option5'] = "";
					$L_CSV[$j]['unit_id'] = "";
					$L_CSV[$j]['english_word_problem'] = "";
					$L_CSV[$j]['error_msg'] = "";
					$L_CSV[$j]['update_time'] = "";
					$L_CSV[$j]['display'] = "";
					$L_CSV[$j]['state'] = "";
					$L_CSV[$j]['sentence_flag'] = "";


					//$L_CSV[$j]['hantei_default_num'] = $hantei_default_num;		//判定テスト管理番号
					$hantei_type = $L_HANTEI_TYPE[$list['hantei_type']];		//判定テスト種別（文字列->コード番号に変換の前の状態）
					$L_CSV[$j]['hantei_type_name'] = $hantei_type;

					$course_num = $list['course_num'];
					$course_name = get_course_name($service_num, $course_num);
					$L_CSV[$j]['course_name'] = $course_name;		//コース名（文字列->コード番号に変換の前の状態）

					$hantei_display_num = $list['hantei_display_num'];	//判定テスト表示番号
					$L_CSV[$j]['hantei_display_num'] = $hantei_display_num;

					//$L_CSV[$j]['hantei_result'] = $hantei_result;			//判定結果
					//$hantei_name = $list['hantei_name'];				//判定テスト表示名
					//$L_CSV[$j]['hantei_name'] = $hantei_name ;

					//このマスターに属する問題データがあれば、データ組立て
					$sql_hmp = "SELECT hmdp.problem_table_type, hmdp.problem_num as hmdp_problem_num, hmdp.disp_sort, hmp.*".
						" FROM ".T_HANTEI_MS_DEFAULT_PROBLEM." hmdp ".
						" LEFT JOIN ".T_HANTEI_MS_PROBLEM." hmp ".
						" ON (hmp.problem_num=hmdp.problem_num AND hmdp.problem_table_type='2')".
						" AND hmp.mk_flg='0'".
						" WHERE hmdp.mk_flg='0'".
						" AND hmdp.hantei_default_num='".$hantei_default_num."';";
					$result_hmp = $cdb->query($sql_hmp);
					while ($list_hmp=$cdb->fetch_assoc($result_hmp)) {
						//$j++;	//従属する問題データ出力のため+1	...マスター情報のみを先に格納しているので、それにオーバーライトしたあと j++ する。

						$problem_num = $list_hmp['hmdp_problem_num'];				//問題管理番号
						$problem_table_type = $list_hmp['problem_table_type'];	//問題テーブルタイプ（1：すらら 2：専用）
						$disp_sort = $list_hmp['disp_sort'];					//表示順
						$standard_time = $list_hmp['standard_time'];			//解答時間
						$problem_type = $list_hmp['problem_type'];		//問題タイプ
						$sub_display_problem_num = $list_hmp['sub_display_problem_num'];
						$form_type = $list_hmp['form_type'];			//出題形式
						$question = $list_hmp['question'];				//質問文
						$problem = $list_hmp['problem'];				//問題文
						$voice_data = $list_hmp['voice_data'];			//音声ファイル名
						$hint = $list_hmp['hint'];						//ヒント内容
						$explanation = $list_hmp['explanation'];		//解説文
						$parameter = $list_hmp['parameter'];		//
						$set_difficulty = $list_hmp['set_difficulty'];		//
						$hint_number = $list_hmp['hint_number'];		//ヒント表示回数
						$correct_number = $list_hmp['correct_number'];		//
						$clear_number = $list_hmp['clear_number'];		//
						$first_problem = $list_hmp['first_problem'];		//問題前半テキスト
						$latter_problem = $list_hmp['latter_problem'];		//問題後半テキスト
						$selection_words = $list_hmp['selection_words'];	//選択語句、問題テキスト
						$correct = $list_hmp['correct'];			//正解
						$option1 = $list_hmp['option1'];			//option1
						$option2 = $list_hmp['option2'];			//option2
						$option3 = $list_hmp['option3'];			//option3
						$option4 = $list_hmp['option4'];			//option4
						$option5 = $list_hmp['option5'];			//option5
						$error_msg = $list_hmp['error_msg'];		//error_msg
						$display = $list_hmp['display'];			//表示・非表示フラグ　1:表示　2:非表示
						$mk_flg = $list_hmp['mk_flg'];				//無効フラグ

						if ($problem_table_type == "1") {
							$srl_problem_num = $problem_num;	//すらら問題
							// problem テーブルから course_num を取得し、course からコース名を取り出す
							$sql_problem = "SELECT course.course_name".
								" FROM ".T_PROBLEM." problem ".
								" LEFT JOIN ".T_COURSE." course".
								" ON course.course_num = problem.course_num ".
								" WHERE problem.state='0'".
								" AND problem.problem_num='".$srl_problem_num."';";
							$result_problem = $cdb->query($sql_problem);
							$list_problem=$cdb->fetch_assoc($result_problem);
							$ms_course_name = $list_problem['course_name'];	//問題のコース名（非判定テストの場合のみ）

						} else {
							$srl_problem_num = "";				//判定テスト問題
							$ms_sevice_name = "";		//問題のサービス名（非判定テストの場合のみ）
							$ms_course_name = "";		//問題のコース名（非判定テストの場合のみ）
						}

						//判定テスト単位で共通部分
						$L_CSV[$j]['hantei_type_name'] = $hantei_type;
						$L_CSV[$j]['course_name'] = $course_name;		//コース名（文字列->コード番号に変換の前の状態）
						$L_CSV[$j]['hantei_display_num'] = $hantei_display_num;

						if (!DISABLE_SERVICE_NAME) {
							$L_CSV[$j]['ms_service_name'] = "";		//今後拡張 2013/09/17
						}
						$L_CSV[$j]['ms_course_name'] = $ms_course_name;

						//問題ごとのデータ
						$L_CSV[$j]['problem_num'] = $srl_problem_num;		//すらら問題の場合は値がセットされる、判定テスト専用問題の場合は""
						$L_CSV[$j]['disp_sort'] = $disp_sort;		//表示順

						$L_CSV[$j]['problem_type'] = $problem_type;
						$L_CSV[$j]['sub_display_problem_num'] = $sub_display_problem_num;
						$L_CSV[$j]['form_type'] = $form_type;
						$L_CSV[$j]['question'] = $question;
						$L_CSV[$j]['problem'] = $problem;
						$L_CSV[$j]['voice_data'] = $voice_data;
						$L_CSV[$j]['hint'] = $hint;
						$L_CSV[$j]['explanation'] = $explanation;
						$L_CSV[$j]['standard_time'] = $standard_time;
						$L_CSV[$j]['parameter'] = $parameter;
						$L_CSV[$j]['set_difficulty'] = $set_difficulty;
						$L_CSV[$j]['hint_number'] = $hint_number;
						$L_CSV[$j]['correct_number'] = $correct_number;
						$L_CSV[$j]['clear_number'] = $clear_number;
						$L_CSV[$j]['first_problem'] = $first_problem;
						$L_CSV[$j]['latter_problem'] = $latter_problem;
						$L_CSV[$j]['selection_words'] = $selection_words;
						$L_CSV[$j]['correct'] = $correct;
						$L_CSV[$j]['option1'] = $option1;
						$L_CSV[$j]['option2'] = $option2;
						$L_CSV[$j]['option3'] = $option3;
						$L_CSV[$j]['option4'] = $option4;
						$L_CSV[$j]['option5'] = $option5;
						$L_CSV[$j]['unit_id'] = "";
						$L_CSV[$j]['english_word_problem'] = "";
						$L_CSV[$j]['error_msg'] = $error_msg;
						$L_CSV[$j]['update_time'] = "";
						$L_CSV[$j]['display'] = $display;
						$L_CSV[$j]['state'] = $mk_flg;
						$L_CSV[$j]['sentence_flag'] = "";

						$j++;	//従属する問題データ出力のため+1
					}
				}
			}
		}
	}

	foreach ($L_CSV as $line_num => $line_val) {
		$first_flg = 1;
		foreach ($line_val as $material_key => $material_val) {
			if ($first_flg == 0) {		//１カラムめはデリミタを付加しない
				 $L_CSV_LINE[$line_num] .= $delimiter;
			} else {
				$first_flg = 0;
			}
			//}
			//if (ereg(",",$material_val)||ereg("\"",$material_val)) {
			//	$L_CSV_LINE[$line_num] .= "\"".$material_val."\"";
			//} else {
				if (!DISABLE_SERVICE_NAME) {
					$L_CSV_LINE[$line_num] .= $material_val;
				} else {
					if ($material_key != "ms_service_name") {
						$L_CSV_LINE[$line_num] .= $material_val;
					}
				}
			//}
		}
	}
	$csv_line .= implode("\n",$L_CSV_LINE);

	//	del 2015/01/07 yoshizawa 課題要望一覧No.400対応 下に新規で作成
	//$csv_line = mb_convert_encoding($csv_line,"sjis-win","UTF-8");
	//$csv_line = replace_decode_sjis($csv_line);
	//----------------------------------------------------------------

	//	add 2015/01/07 yoshizawa 課題要望一覧No.400対応

		//++++++++++++++++++++++//
		//	$_POST['exp_list']	//
		//	1 => SJIS			//
		//	2 => Unicode		//
		//++++++++++++++++++++++//
	//	utf-8で出力
	if ( $_POST['exp_list'] == 2 ) {
		//	Unicode選択時には特殊文字のみ変換
		$csv_line = replace_decode($csv_line);

	//	SJISで出力
	} else {
		$csv_line = mb_convert_encoding($csv_line,"sjis-win","UTF-8");
		$csv_line = replace_decode_sjis($csv_line);

	}
	//-------------------------------------------------


	return array($csv_line, $ERROR);
}


/**
 * マスターデータ csvインポート
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @date 2013/09/17
 * @author Azet
 * @return array
 */
function ht_csv_master_import() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_HANTEI_TYPE;

	$delimiter = "\t";
	//$delimiter = ",";

	// 2013/09/17
	//define(DISABLE_MSG_OUTPUT, 1);	//clear_msg, break_msg カラム処理スイッチ 0の時は項目数=12、1の時は項目数=10
										// 定義は test_practice_hantei_test_master.php 最初のルーチンで。
	//del start okabe 2013/10/04
	//$colums_of_data = 10;	//１行データとして必要な項目数
	//						// hantei_type_name, course_name, hantei_display_num, hantei_name, hantei_result,
	//						//  rand_type, clear_rate, problem_count, limit_time, (clear_msg, break_msg,) ms_display
	//if (!DISABLE_MSG_OUTPUT) { $colums_of_data = 12; }
	//del end okabe 2013/10/04
	//add start okabe 2013/10/04
	$colums_of_data = 11;	//１行データとして必要な項目数
							// hantei_type_name, course_name, hantei_display_num, hantei_name, hantei_result, hantei_result_last,
							//  rand_type, clear_rate, problem_count, limit_time, (clear_msg, break_msg,) ms_display
	if (!DISABLE_MSG_OUTPUT) { $colums_of_data = 13; }
	//add end okabe 2013/10/04

	$PROC_RESULT = array();	//処理結果レポートを返すための変数

	$CHK_LNUM = array();	//使われなくなった問題番号の突合せチェック用変数

	if (!$_POST['service_num']) {
		$ERROR[] = "サービスが確認できません。";
		return $ERROR;
	}

	$service_num = $_POST['service_num'];

	$file_name = $_FILES['import_file']['name'];
	$file_tmp_name = $_FILES['import_file']['tmp_name'];
	$file_error = $_FILES['import_file']['error'];

	if (!$file_tmp_name) {
		$ERROR[] = "判定テストマスターファイルが指定されておりません。";
	} elseif (!eregi("(.csv)$",$file_name)) {
		$ERROR[] = "ファイルの拡張子が不正です。";
	} elseif ($file_error == 1) {
		$ERROR[] = "アップロードできるファイルの容量が設定範囲を超えてます。サーバー管理者へ相談してください。";
	} elseif ($file_error == 3) {
		$ERROR[] = "判定テストマスター問題ファイルの一部分のみしかアップロードされませんでした。";
	} elseif ($file_error == 4) {
		$ERROR[] = "判定テストマスター問題ファイルがアップロードされませんでした。";
	}
	if ($ERROR) {
		if ($file_tmp_name && file_exists($file_tmp_name)) { unlink($file_tmp_name); }
		//$MSG['error'] = $ERROR;
		return $ERROR;
	}

	//現時点で DB にある hantei_default_num (in hantei_ms_default)を読み出し、
	// 既存マスターデータを把握しておく。
	$L_NUM = array();
	$sql = "SELECT hmd.hantei_default_num".
		" FROM ".T_HANTEI_MS_DEFAULT." hmd".
		" WHERE hmd.mk_flg='0'".
		" AND hmd.service_num='".$service_num."'".
		" ORDER BY hantei_default_num;";
	$result = $cdb->query($sql);
	while ($list = $cdb->fetch_assoc($result)) {
		//hantei_default_num 値しか無い場合は、マスターしか存在しない（この判定テストに登録されている問題は０件）
		$hantei_default_num = $list['hantei_default_num'];
		//$problem_num = $list['problem_num'];
		//$problem_table_type = $list['problem_table_type'];
		//$disp_sort = $list['disp_sort'];
		$L_NUM['hantei_default_num'][$hantei_default_num] = "1";
		//if (strlen($problem_num) > 0) {
		//	$L_NUM[$hantei_default_num][$disp_sort] = $problem_table_type.",".$problem_num;
		//}
	}
	//$L_NUM_RESULT = array();	//処理した問題番号情報の保持


	//結果集計用
	$cnt_all = 0;	//読み込んだデータ件数
	$cnt_err = 0;	//そのうちのエラー行
	$cnt_success = 0;	//更新・新規登録の成功件数
	$cnt_ins_master = 0;	//マスター新規登録件数
	$cnt_upd_master = 0;	//マスターの更新件数
	//$cnt_ins_problem = 0;	//問題の新規登録件数
	//$cnt_upd_problem = 0;	//問題の更新件数

	//データの読み込み
	$LIST = file($file_tmp_name);
	if ($LIST) {
		$i = 0;
		foreach ($LIST AS $VAL) {
			unset($LINE);
			//	del 2014/12/08 yoshizawa 課題要望一覧No.394対応 下に新規で作成
			//$VAL = mb_convert_encoding($VAL,"UTF-8","sjis-win");
			//----------------------------------------------------------------
			//	add 2014/12/08 yoshizawa 課題要望一覧No.394対応
			//	データの文字コードがUTF-8だったら変換処理をしない
			$code = judgeCharacterCode ( $VAL );
			if ( $code != 'UTF-8' ) {
				$VAL = mb_convert_encoding($VAL,"UTF-8","sjis-win");
			}
			//--------------------------------------------------

			$VAL = mb_convert_kana($VAL,"asKV", "UTF-8");
			if ($delimiter != "\t") {
				$VAL = trim($VAL);
			} else {
				$VAL = trim($VAL, " \n\r\0\x0B");
			}

			if (!$VAL || !ereg($delimiter, $VAL)) { continue; }

			$oneline_colums = count(explode($delimiter, $VAL));	//１行データの項目数（タブ区切り数）

			if (!DISABLE_MSG_OUTPUT) {	// 2013/09/17
				//del start okabe 2013/10/04
				//list(
				//	$LINE['hantei_type_name'],$LINE['course_name'],$LINE['hantei_display_num'],
				//	$LINE['hantei_name'],$LINE['hantei_result'],$LINE['rand_type'],
				//	$LINE['hantei_name'],$LINE['hantei_result'],$LINE['rand_type'],
				//	$LINE['clear_rate'],$LINE['problem_count'],$LINE['limit_time'],
				//	$LINE['clear_msg'],$LINE['break_msg'],		// DISABLE_MSG_OUTPUT==0 の場合は処理
				//	$LINE['ms_display']
				//) = explode($delimiter, $VAL);
				//del end okabe 2013/10/04
				//add start okabe 2013/10/04
				list(
					$LINE['hantei_type_name'],$LINE['course_name'],$LINE['hantei_display_num'],
				//	$LINE['hantei_name'],$LINE['hantei_result'],$LINE['rand_type'],
					$LINE['hantei_name'],$LINE['hantei_result'],$LINE['hantei_result_last'],$LINE['rand_type'],
					$LINE['clear_rate'],$LINE['problem_count'],$LINE['limit_time'],
					$LINE['clear_msg'],$LINE['break_msg'],		// DISABLE_MSG_OUTPUT==0 の場合は処理
					$LINE['ms_display']
				) = explode($delimiter, $VAL);
				//add end okabe 2013/10/04
			} else {
				//del start okabe 2013/10/04
				//list(
				//	$LINE['hantei_type_name'],$LINE['course_name'],$LINE['hantei_display_num'],
				//	$LINE['hantei_name'],$LINE['hantei_result'],$LINE['rand_type'],
				//	$LINE['clear_rate'],$LINE['problem_count'],$LINE['limit_time'],
				//	$LINE['ms_display']
				//) = explode($delimiter, $VAL);
				//del end okabe 2013/10/04
				//add start okabe 2013/10/04
				list(
					$LINE['hantei_type_name'],$LINE['course_name'],$LINE['hantei_display_num'],
					$LINE['hantei_name'],$LINE['hantei_result'],$LINE['hantei_result_last'],$LINE['rand_type'],
					$LINE['clear_rate'],$LINE['problem_count'],$LINE['limit_time'],
					$LINE['ms_display']
				) = explode($delimiter, $VAL);
				//add end okabe 2013/10/04
			}

			if ($LINE) {
				$i++;	//読み込み行番号

				if ($oneline_colums < $colums_of_data) {
					//項目数不足
					$error_flg = 1;
					//$cnt_err++;		//エラー発生したことをカウント
					$ERROR[] = line_number_format($i)."１行に必要な項目数が足りません。";
					continue;
				}
				$check_no_problem_csv = $oneline_colums - 17;	//csv１行データのうち、問題データとして必要なカラム数

				if (str_replace("\"", "", $LINE['hantei_type_name']) != "hantei_type_name") {	//１行目スキップ
					foreach ($LINE AS $key => $val) {
						if ($val) {
							$val = ereg_replace("\"","&quot;",$val);
							//	del 2014/12/08 yoshizawa 課題要望一覧No.394対応 下に新規で作成
							//$val = replace_encode_sjis($val);
							//----------------------------------------------------------------
							//	add 2014/12/08 yoshizawa 課題要望一覧No.394対応
							//	データの文字コードがUTF-8だったら変換処理をしない
							$code = judgeCharacterCode ( $val );
							if ( $code != 'UTF-8' ) {
								$val = replace_encode_sjis($val);
							}
							//	add 2015/01/09 yoshizawa 課題要望一覧No.400対応
							//	sjisファイルをインポートしても2バイト文字はutf-8で扱われる
							else {
								//	記号は特殊文字に変換します
								$val = replace_encode($val);

							}
							//--------------------------------------------------

							//$val = mb_convert_encoding($val,"UTF-8","sjis-win");
							$LINE[$key] = ht_replace_word($val);
						}
					}

					//読み込んだ一行の処理
					$cnt_all++;
					// 入力各項目の正当性チェック
					$DATA_MS_SET = array();
					$DATA_MS_SET['__info']['call_mode']		= "1";		// 0=入力フォーム、1:tsv入力
					$DATA_MS_SET['__info']['line_num']		= $i;		// 行番号...エラーメッセージに付記するため
					$DATA_MS_SET['__info']['check_mode']	= "1";		// チェック時にデータ型を自動調整(半角英数字化、trim処理)するかのスイッチ
														//												0:自動調整しない、1:する
					$DATA_MS_SET['__info']['store_mode']	= "1";		// データ自動調整結果を、$DATA_MS_SET['data']['パラメータ名']へ再格納するかのスイッチ
																		//												0:再格納しない、1:する

					$DATA_MS_SET['data']['service_num']			= $service_num;
				//	$DATA_MS_SET['data']['hantei_default_num']	= $LINE['hantei_default_num'];	//判定テスト基本情報管理番号
					$DATA_MS_SET['data']['hantei_type_name']	= $LINE['hantei_type_name'];	//判定テスト種別名（コース判定:1、コース内判定:2）
					$DATA_MS_SET['data']['hantei_type_num']		= "";
					$DATA_MS_SET['data']['course_name']			= $LINE['course_name'];				//コース名
					$DATA_MS_SET['data']['course_num']			= "";
					$DATA_MS_SET['data']['hantei_display_num']	= $LINE['hantei_display_num'];		//判定テスト表示番号
				//	$DATA_MS_SET['data']['style_course_name']	= $LINE['style_course_name'];		//画面表示スタイルの基準とするコース名
				//	$DATA_MS_SET['data']['style_course_num']	= "";
				//	$DATA_MS_SET['data']['style_stage_name']	= $LINE['style_stage_name'];		//画面表示スタイルの基準とするステージ名
				//	$DATA_MS_SET['data']['style_stage_num']		= "";
					$DATA_MS_SET['data']['hantei_name']			= $LINE['hantei_name'];		//判定テスト表示名
					$DATA_MS_SET['data']['hantei_result']		= $LINE['hantei_result'];	//判定結果 文字列
					$DATA_MS_SET['data']['hantei_result_list']	= "";
					//add start okabe 2013/10/04	//@@@
					$DATA_MS_SET['data']['hantei_result_last']	= $LINE['hantei_result_last'];	//最終テスト合格時の判定結果 文字列
					$DATA_MS_SET['data']['hantei_result_list_last']	= "";
					//add end okabe 2013/10/04
					$DATA_MS_SET['data']['rand_type']			= $LINE['rand_type'];		//ランダム表示
					$DATA_MS_SET['data']['clear_rate']			= $LINE['clear_rate'];		//判定正解率
					$DATA_MS_SET['data']['problem_count']		= $LINE['problem_count'];	//出題問題数
					$DATA_MS_SET['data']['limit_time']			= $LINE['limit_time'];		//ドリルクリア標準時間

					if (!DISABLE_MSG_OUTPUT) {
						$DATA_MS_SET['data']['clear_msg']			= $LINE['clear_msg'];		//クリアメッセージ
						$DATA_MS_SET['data']['break_msg']			= $LINE['break_msg'];		//判定結果メッセージ
					}

					$DATA_MS_SET['data']['ms_display']			= $LINE['ms_display'];		//判定テストの表示・非表示

				//	$DATA_MS_SET['data']['problem_num']			= $LINE['problem_num'];		//問題管理番号 (判定テスト専用問題の場合は入力なし）

					//判定マスター固有部分の入力データをチェック
					list($DATA_MS_SET, $ERROR, $error_flg) = ht_master_check($DATA_MS_SET, $ERROR);
					// $DATA_MS_SET['data'][...]の各値は 半角英数字、カナなどの統一、trim済み

					// データの処理
					//
					if (!$error_flg) {		//エラーが起きた行では処理しない
						//$csv_hantei_default_num = $DATA_MS_SET['data']['hantei_default_num'];
						$csv_hantei_default_num = "";	//新仕様tsvでは、この値は常にnull  2013/09/17
						//$csv_hantei_type_name = $LINE['hantei_type_name'];

						$csv_problem_num = $LINE['problem_num'];
						$csv_display_problem_num = $LINE['display_problem_num'];

						// hantei_default_num の指定が無い場合、下記の判別をして登録済みマスターか確認する
						// 判定マスターへの新規登録、または 判定テスト種別-コース名-判定テスト表示番号 で一致するデータの更新
						if (strlen($csv_hantei_default_num) == 0) {
							$csv_hantei_default_num = chk_exist_master($i, $service_num, $DATA_MS_SET);
							$DATA_MS_SET['data']['hantei_default_num'] = $csv_hantei_default_num;
						}

						if (strlen($csv_hantei_default_num) == 0) {
							//判定マスターへの新規登録
	//echo "call ht_regist_master ".$csv_hantei_default_num."<br>";
							list($ERROR_SUB, $new_hantei_default_num) = ht_regist_master($i, $service_num, $DATA_MS_SET);
							if ($ERROR_SUB) {
								$cnt_err++;		//エラー発生したことをカウント
								$error_flg = 1;
								$ERROR = array_merge($ERROR, $ERROR_SUB);
								continue;

							} else {
								$cnt_ins_master++;
								//新規登録した情報を L_NUM に追加する
								$L_NUM['hantei_default_num'][$new_hantei_default_num] = "1";
							}

						} else {
							//入力された判定テスト管理番号はあるか
							if ($L_NUM['hantei_default_num'][$csv_hantei_default_num] == "1") {
								//すでにデータがある（マスタ更新処理）
	//echo "call ht_update_master ".$csv_hantei_default_num."<br>";
								$ERROR_SUB = ht_update_master($i, $service_num, $DATA_MS_SET);
								if ($ERROR_SUB) {
									$cnt_err++;		//エラー発生したことをカウント
									$error_flg = 1;
									$ERROR = array_merge($ERROR, $ERROR_SUB);
									continue;
								} else {
									$cnt_upd_master++;
								}

							} else {
								//データは無い...新規登録せずエラー扱い
								$cnt_err++;		//エラー発生したことをカウント
								$error_flg = 1;
								$ERROR[] = line_number_format($i)."指定された判定テスト管理番号 ".$csv_hantei_default_num." は存在しません!。";
								continue;
							}

						}

					} else {	//if (!$error_flg) {		//エラーが起きた行では処理しない
						$cnt_err++;	//エラー発生行としてカウント
					}

				}

			}

		}	//foreach ($LIST AS $VAL)	//空行は読み飛ばされる

		if ($cnt_all > 0) {
			$PROC_RESULT[] = "データ件数: ".$cnt_all;
			$PROC_RESULT[] = "エラー件数: ".$cnt_err;
			$PROC_RESULT[] = "新規登録したマスターデータ件数: ".$cnt_ins_master;
			$PROC_RESULT[] = "更新したマスターデータ件数: ".$cnt_upd_master;
			//$PROC_RESULT[] = "新規登録した問題データ件数: ".$cnt_ins_problem;
			//$PROC_RESULT[] = "更新した問題データ件数: ".$cnt_upd_problem;
		}

	}	//if ($LIST)	//データ読み込みループ末端

	return  array($ERROR, $PROC_RESULT);
}



//---


/**
 * 問題データ csvインポート
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array
 */
function ht_csv_problem_import() {
//echo "ht_csv_problem_import()<br>";

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_HANTEI_TYPE;

	$delimiter = "\t";
	//$delimiter = ",";

	//define((DISABLE_SERVICE_NAME, , 1);	//service_name カラム処理スイッチ 0の時は項目数=36、1の時は項目数=35
										// 定義は test_practice_hantei_test_problem.php 最初のルーチンで。
		//hantei_type_name, course_name, hantei_display_num,
		//(ms_service_name,) ms_course_name, problem_num, disp_sort, problem_type, sub_display_problem_num,
		//form_type, question, problem, voice_data, hint, explanation, standard_time, parameter,
		//set_difficulty, hint_number, correct_number, clear_number, first_problem, latter_problem,
		//selection_words, correct, option1, option2, option3, option4, option5,
		//unit_id, english_word_problem, error_msg, update_time, display, state, sentence_flag
	$colums_of_data = 36;	//１行データとして必要な項目数
	if (!DISABLE_SERVICE_NAME) { $colums_of_data = 37; }


	$PROC_RESULT = array();	//処理結果レポートを返すための変数

	$CHK_LNUM = array();	//使われなくなった問題番号の突合せチェック用変数

	if (!$_POST['service_num']) {
		$ERROR[] = "サービスが確認できません。";
		return $ERROR;
	}

	$service_num = $_POST['service_num'];

	$file_name = $_FILES['import_file']['name'];
	$file_tmp_name = $_FILES['import_file']['tmp_name'];
	$file_error = $_FILES['import_file']['error'];

	if (!$file_tmp_name) {
		$ERROR[] = "判定テスト問題ファイルが指定されておりません。";
	} elseif (!eregi("(.csv)$",$file_name)) {
		$ERROR[] = "ファイルの拡張子が不正です。";
	} elseif ($file_error == 1) {
		$ERROR[] = "アップロードできるファイルの容量が設定範囲を超えてます。サーバー管理者へ相談してください。";
	} elseif ($file_error == 3) {
		$ERROR[] = "判定テスト問題ファイルの一部分のみしかアップロードされませんでした。";
	} elseif ($file_error == 4) {
		$ERROR[] = "判定テスト問題ファイルがアップロードされませんでした。";
	}
	if ($ERROR) {
		if ($file_tmp_name && file_exists($file_tmp_name)) { unlink($file_tmp_name); }
		//$MSG['error'] = $ERROR;
		return $ERROR;
	}

	//現時点で DB にあるデータから、hantei_default_num (in hantei_ms_default)と、
	// disp_sort (in hantei_ms_default_problem) を読み出し、
	// 既存データを把握しておく。
	$L_NUM = array();
	$sql = "SELECT hmd.hantei_default_num, hmdp.problem_num, hmdp.problem_table_type, hmdp.disp_sort".
		" FROM ".T_HANTEI_MS_DEFAULT." hmd".
		" LEFT JOIN ".T_HANTEI_MS_DEFAULT_PROBLEM." hmdp".
		" ON hmd.hantei_default_num=hmdp.hantei_default_num".
		" AND hmdp.mk_flg='0'".
		" WHERE hmd.mk_flg='0'".
		" AND hmd.service_num='".$service_num."'".
		" ORDER BY hantei_default_num;";

	$result = $cdb->query($sql);
	while ($list = $cdb->fetch_assoc($result)) {
		//hantei_default_num 値しか無い場合は、マスターしか存在しない（この判定テストに登録されている問題は０件）
		//
		$hantei_default_num = $list['hantei_default_num'];
		$problem_num = $list['problem_num'];
		$problem_table_type = $list['problem_table_type'];
		$disp_sort = $list['disp_sort'];

		$L_NUM['hantei_default_num'][$hantei_default_num] = "1";
		if (strlen($problem_num) > 0) {
			$L_NUM[$hantei_default_num][$disp_sort] = $problem_table_type.",".$problem_num;
		}
	}
	$L_NUM_RESULT = array();	//処理した問題番号情報の保持
	$L_NUM_HANTEIT = array();	//TSVに登場した判定テストの hantei_default_num を保持

	//結果集計用
	$cnt_all = 0;	//読み込んだデータ件数
	$cnt_err = 0;	//そのうちのエラー行
	$cnt_success = 0;	//更新・新規登録の成功件数
	//$cnt_ins_master = 0;	//マスター新規登録件数
	//$cnt_upd_master = 0;	//マスターの更新件数
	$cnt_ins_problem = 0;	//問題の新規登録件数
	$cnt_upd_problem = 0;	//問題の更新件数

	//データの読み込み
	$LIST = file($file_tmp_name);
	if ($LIST) {
		$i = 0;
		foreach ($LIST AS $VAL) {
			unset($LINE);
			//	del 2014/12/08 yoshizawa 課題要望一覧No.394対応 下に新規で作成
			//$VAL = mb_convert_encoding($VAL,"UTF-8","sjis-win");
			//----------------------------------------------------------------
			//	add 2014/12/08 yoshizawa 課題要望一覧No.394対応
			//	データの文字コードがUTF-8だったら変換処理をしない
			$code = judgeCharacterCode ( $VAL );
			if ( $code != 'UTF-8' ) {
				$VAL = mb_convert_encoding($VAL,"UTF-8","sjis-win");
			}
			//--------------------------------------------------

			$VAL = mb_convert_kana($VAL,"asKV", "UTF-8");
			if ($delimiter != "\t") {
				$VAL = trim($VAL);
			} else {
				$VAL = trim($VAL, " \n\r\0\x0B");
			}

			if (!$VAL || !ereg($delimiter, $VAL)) { continue; }

			$oneline_colums = count(explode($delimiter, $VAL));	//１行データの項目数（タブ区切り数）

/*			list(
				$LINE['hantei_default_num'],$LINE['hantei_type_name'],$LINE['course_name'],$LINE['hantei_display_num'],
				$LINE['style_course_name'],$LINE['style_stage_name'],
				$LINE['hantei_name'],$LINE['hantei_result'],$LINE['rand_type'],
				$LINE['clear_rate'],$LINE['problem_count'],$LINE['limit_time'],$LINE['clear_msg'],$LINE['break_msg'],
				$LINE['ms_display'],$LINE['problem_num'],$LINE['display_problem_num'],$LINE['problem_type'],
				$LINE['sub_display_problem_num'],$LINE['form_type'],$LINE['question'],$LINE['problem'],
				$LINE['voice_data'],$LINE['hint'],$LINE['explanation'],$LINE['standard_time'],
				$LINE['parameter'],$LINE['set_difficulty'],$LINE['hint_number'],$LINE['correct_number'],$LINE['clear_number'],
				$LINE['first_problem'],$LINE['latter_problem'],$LINE['selection_words'],$LINE['correct'],
				$LINE['option1'],$LINE['option2'],$LINE['option3'],$LINE['option4'],$LINE['option5'],
				$LINE['unit_id'],$LINE['english_word_problem'],
				$LINE['error_msg'],$LINE['update_time'],$LINE['display'],$LINE['state'],$LINE['sentence_flag']
			) = explode($delimiter, $VAL);
*/
			if (!DISABLE_SERVICE_NAME) {
				list(
					$LINE['hantei_type_name'],$LINE['course_name'],$LINE['hantei_display_num'],
					$LINE['ms_service_name'],			//DISABLE_SERVICE_NAME == 0 のとき。
					$LINE['ms_course_name'],$LINE['problem_num'],$LINE['display_problem_num'],
					$LINE['problem_type'],$LINE['sub_display_problem_num'],
					$LINE['form_type'],$LINE['question'],$LINE['problem'],$LINE['voice_data'],$LINE['hint'],
					$LINE['explanation'],$LINE['standard_time'],$LINE['parameter'],
					$LINE['set_difficulty'],$LINE['hint_number'],$LINE['correct_number'],$LINE['clear_number'],
					$LINE['first_problem'],$LINE['latter_problem'],$LINE['selection_words'],$LINE['correct'],
					$LINE['option1'],$LINE['option2'],$LINE['option3'],$LINE['option4'],$LINE['option5'],
					$LINE['unit_id'],$LINE['english_word_problem'],
					$LINE['error_msg'],$LINE['update_time'],$LINE['display'],$LINE['state'],$LINE['sentence_flag']
				) = explode($delimiter, $VAL);
			} else {
				list(
					$LINE['hantei_type_name'],$LINE['course_name'],$LINE['hantei_display_num'],
					$LINE['ms_course_name'],$LINE['problem_num'],$LINE['display_problem_num'],
					$LINE['problem_type'],$LINE['sub_display_problem_num'],
					$LINE['form_type'],$LINE['question'],$LINE['problem'],$LINE['voice_data'],$LINE['hint'],
					$LINE['explanation'],$LINE['standard_time'],$LINE['parameter'],
					$LINE['set_difficulty'],$LINE['hint_number'],$LINE['correct_number'],$LINE['clear_number'],
					$LINE['first_problem'],$LINE['latter_problem'],$LINE['selection_words'],$LINE['correct'],
					$LINE['option1'],$LINE['option2'],$LINE['option3'],$LINE['option4'],$LINE['option5'],
					$LINE['unit_id'],$LINE['english_word_problem'],
					$LINE['error_msg'],$LINE['update_time'],$LINE['display'],$LINE['state'],$LINE['sentence_flag']
				) = explode($delimiter, $VAL);
			}

			if ($LINE) {
				$i++;	//読み込み行番号

				if ($oneline_colums < $colums_of_data) {
					//項目数不足
					$error_flg = 1;
					//$cnt_err++;		//エラー発生したことをカウント
					$ERROR[] = line_number_format($i)."１行に必要な項目数が足りません。";
					continue;
				}
				$check_no_problem_csv = $oneline_colums - 17;	//csv１行データのうち、問題データとして必要なカラム数

				if (str_replace("\"", "", $LINE['hantei_type_name']) != "hantei_type_name") {	//１行目スキップ
					foreach ($LINE AS $key => $val) {
						if ($val) {
							$val = ereg_replace("\"","&quot;",$val);
							//	del 2014/12/08 yoshizawa 課題要望一覧No.394対応 下に新規で作成
							//$val = replace_encode_sjis($val);
							//----------------------------------------------------------------
							//	add 2014/12/08 yoshizawa 課題要望一覧No.394対応
							//	データの文字コードがUTF-8だったら変換処理をしない
							$code = judgeCharacterCode ( $val );
							if ( $code != 'UTF-8' ) {
								$val = replace_encode_sjis($val);
							}
							//	add 2015/01/09 yoshizawa 課題要望一覧No.400対応
							//	sjisファイルをインポートしても2バイト文字はutf-8で扱われる
							else {
								//	記号は特殊文字に変換します
								$val = replace_encode($val);

							}
							//--------------------------------------------------

							//$val = mb_convert_encoding($val,"UTF-8","sjis-win");
							$LINE[$key] = ht_replace_word($val);
						}
					}

					//読み込んだ一行の処理
					$cnt_all++;
					// 入力各項目の正当性チェック
					$DATA_MS_SET = array();
					$DATA_MS_SET['__info']['call_mode']		= "1";		// 0=入力フォーム、1:tsv入力
					$DATA_MS_SET['__info']['line_num']		= $i;		// 行番号...エラーメッセージに付記するため
					$DATA_MS_SET['__info']['check_mode']	= "1";		// チェック時にデータ型を自動調整(半角英数字化、trim処理)するかのスイッチ
														//												0:自動調整しない、1:する
					$DATA_MS_SET['__info']['store_mode']	= "1";		// データ自動調整結果を、$DATA_MS_SET['data']['パラメータ名']へ再格納するかのスイッチ
																		//												0:再格納しない、1:する

					$DATA_MS_SET['data']['service_num']			= $service_num;
				//	$DATA_MS_SET['data']['hantei_default_num']	= $LINE['hantei_default_num'];	//判定テスト基本情報管理番号
					$DATA_MS_SET['data']['hantei_type_name']	= $LINE['hantei_type_name'];	//判定テスト種別名（コース判定:1、コース内判定:2）
					$DATA_MS_SET['data']['hantei_type_num']		= "";
					$DATA_MS_SET['data']['course_name']			= $LINE['course_name'];				//コース名
					$DATA_MS_SET['data']['course_num']			= "";
					$DATA_MS_SET['data']['hantei_display_num']	= $LINE['hantei_display_num'];		//判定テスト表示番号

					if (!DISABLE_SERVICE_NAME) {
						$DATA_MS_SET['data']['ms_service_name']	= $LINE['ms_service_name'];		//既存（すらら問題等）が属しているサービス名
					}
					$DATA_MS_SET['data']['ms_course_name']	= $LINE['ms_course_name'];		//既存（すらら問題等）のコース名(problem テーブルの course_num を求めるため)
					$DATA_MS_SET['data']['ms_course_num']	= "";		//既存（すらら問題等）のコース名(problem テーブルの course_num 値)

					$DATA_MS_SET['data']['problem_num']			= $LINE['problem_num'];		//問題管理番号 (判定テスト専用問題の場合は入力なし）

				//	$DATA_MS_SET['data']['style_course_name']	= $LINE['style_course_name'];		//画面表示スタイルの基準とするコース名
				//	$DATA_MS_SET['data']['style_course_num']	= "";
				//	$DATA_MS_SET['data']['style_stage_name']	= $LINE['style_stage_name'];		//画面表示スタイルの基準とするステージ名
				//	$DATA_MS_SET['data']['style_stage_num']		= "";
				//	$DATA_MS_SET['data']['hantei_name']			= $LINE['hantei_name'];		//判定テスト表示名
				//	$DATA_MS_SET['data']['hantei_result']		= $LINE['hantei_result'];	//判定結果 文字列
				//	$DATA_MS_SET['data']['hantei_result_list']	= "";
				//	$DATA_MS_SET['data']['rand_type']			= $LINE['rand_type'];		//ランダム表示
				//	$DATA_MS_SET['data']['clear_rate']			= $LINE['clear_rate'];		//判定正解率
				//	$DATA_MS_SET['data']['problem_count']		= $LINE['problem_count'];	//出題問題数
				//	$DATA_MS_SET['data']['limit_time']			= $LINE['limit_time'];		//ドリルクリア標準時間
				//	$DATA_MS_SET['data']['clear_msg']			= $LINE['clear_msg'];		//クリアメッセージ
				//	$DATA_MS_SET['data']['break_msg']			= $LINE['break_msg'];		//判定結果メッセージ
				//	$DATA_MS_SET['data']['ms_display']			= $LINE['ms_display'];		//判定テストの表示・非表示
				//	$DATA_MS_SET['data']['problem_num']			= $LINE['problem_num'];		//問題管理番号 (判定テスト専用問題の場合は入力なし）


					//判定マスター固有部分の入力データをチェック
					//list($DATA_MS_SET, $ERROR, $error_flg) = ht_master_check($DATA_MS_SET, $ERROR);
					list($DATA_MS_SET, $ERROR, $error_flg) = ht_master_check_subset($DATA_MS_SET, $ERROR);	//新仕様の、問題データTSVのマスタ識別部分のチェック
					// $DATA_MS_SET['data'][...]の各値は 半角英数字、カナなどの統一、trim済み


					//問題データ部の指定もあるか
					$add_problem = 0;
				//	if (!preg_match("/,{30}$/", $VAL)) {	//'問題表示番号'カラムを含め、ここから右すべてがカラか確認。
					if (!preg_match("/".$delimiter."{".$check_no_problem_csv."}$/", $VAL)) {	//'問題表示番号'カラムを含め、ここから右すべてがカラか確認。
				//	if (!preg_match("/".$delimiter."{29}$/", $VAL)) {	//'問題表示番号'カラムの除き、その次の項目から右すべてがカラか確認。
						$add_problem = 1;	//問題データの指定有り
					}
					$DATA_MS_SET['data']['add_problem_flg']		= $add_problem;		//問題データの指定有る場合は 1


					//すらら問題番号指定が無く、問題データ部があるか。あればデータ内容をチェック
					if (strlen($LINE['problem_num']) == 0 && $add_problem == 1) {

						//問題データのチェック
						//入力データの正当性をチェックする。異常があれば更新しない。
						$DATA_SET = array();
						$DATA_SET['__info']['call_mode'] = 1;		// 0=入力フォーム、1:csv入力
						$DATA_SET['__info']['line_num'] = $i;		// 行番号(エラーメッセージに付加するもの, csv入力で使用）
						$DATA_SET['__info']['check_mode'] = 1;		// チェック時にデータ型を自動調整(半角英数字化、trim処理)スイッチ(1:する)
						$DATA_SET['__info']['store_mode'] = 1;		// データ自動調整結果を、$DATA_SET['data']['パラメータ名']へ再格納スイッチ(1:する)

						$DATA_SET['data']['display_problem_num'] = $LINE['display_problem_num'];	// disp_sort 値として扱うデータ
						$DATA_SET['data']['problem_type'] = $LINE['problem_type'];
						$DATA_SET['data']['form_type'] = $LINE['form_type'];
						$DATA_SET['data']['question'] = trim($LINE['question']);
						$DATA_SET['data']['problem'] = trim($LINE['problem']);
						$DATA_SET['data']['voice_data'] = trim($LINE['voice_data']);
						$DATA_SET['data']['hint'] = trim($LINE['hint']);
						$DATA_SET['data']['explanation'] = trim($LINE['explanation']);
						//$DATA_SET['data']['answer_time'] = $_POST['answer_time'];
						$DATA_SET['data']['standard_time'] = $LINE['standard_time'];
						$DATA_SET['data']['hint_number'] = $LINE['hint_number'];
						$DATA_SET['data']['first_problem'] = trim($LINE['first_problem']);
						$DATA_SET['data']['latter_problem'] = trim($LINE['latter_problem']);
						$DATA_SET['data']['selection_words'] = trim($LINE['selection_words']);
						$DATA_SET['data']['correct'] = trim($LINE['correct']);
						$DATA_SET['data']['option1'] = $LINE['option1'];
						$DATA_SET['data']['option2'] = $LINE['option2'];
						$DATA_SET['data']['option3'] = $LINE['option3'];
						$DATA_SET['data']['option4'] = $LINE['option4'];
						$DATA_SET['data']['option5'] = $LINE['option5'];
						$DATA_SET['data']['error_msg'] = "";
						$DATA_SET['data']['display'] = $LINE['display'];
						$DATA_SET['data']['state'] = $LINE['state'];

						list($DATA_SET, $ERROR) = ht_test_check($DATA_SET, $ERROR);
						// $DATA_SET['data'][...]の各値は 半角英数字、カナなどの統一、trim済み
						if ($DATA_SET['__info']['result'] == 1) { $error_flg = 1; }

					} else {
						//problem_num指定あり、または 問題データの記述なし

						if (strlen($LINE['problem_num']) != 0 || strlen($LINE['display_problem_num']) != 0) {
							//すらら問題番号も、disp_sort も指定ない場合は除く(マスター登録のみのケース)

							if (strlen($LINE['display_problem_num']) == 0) {
								//disp_sort の指示が無ければエラー
								$error_flg = 1;
								$cnt_err++;		//エラー発生したことをカウント
								$ERROR[] = line_number_format($i)."問題表示番号が指定されていません。";
								continue;
							}

							if ($add_problem == 1) {
								//csv行で問題データも記述されているか確認
								$error_flg = 1;
								$cnt_err++;		//エラー発生したことをカウント
								$ERROR[] = line_number_format($i)."問題を記述した行では、問題管理番号を指定することは出来ません。";
								continue;
							} else {
								if (strlen($LINE['problem_num']) == 0) {
									//問題の記述が無いのに、すらら番号の指定もない
									$error_flg = 1;
									$cnt_err++;		//エラー発生したことをカウント
									$ERROR[] = line_number_format($i)."問題管理番号が指定されていません。";
									continue;
								}
							}

						} else {
							//ONLY MASTER REGIST
						}
					}


					// データの処理
					//
					if (!$error_flg) {		//エラーが起きた行では処理しない

						$csv_problem_num = $LINE['problem_num'];
						$csv_display_problem_num = $LINE['display_problem_num'];

						// hantei_default_num の指定が無い場合、下記の判別をして登録済みマスターか確認する
						// 判定マスターへの新規登録、または 判定テスト種別-コース名-判定テスト表示番号 で一致するデータの更新
						$csv_hantei_default_num = chk_exist_master($i, $service_num, $DATA_MS_SET);
						$DATA_MS_SET['data']['hantei_default_num'] = $csv_hantei_default_num;
						$L_NUM_HANTEIT[$csv_hantei_default_num] = "1";

						if (strlen($csv_hantei_default_num) == 0) {
							$cnt_err++;		//エラー発生したことをカウント
							$error_flg = 1;
							$ERROR[] = line_number_format($i)."判定テストマスターに該当するデータが見つかりません。";
							continue;

						} else {
							//csvデータ（１行）の後半をチェック
							if (!preg_match("/".$delimiter."{".$check_no_problem_csv."}$/", $VAL)) {	// 問題データが記載されている場合

								$DATA_SET['_add']['disp_sort'] = $LINE['display_problem_num'];

								// add start okabe 2013/09/05
								$DATA_SET['_add']['service_num'] = $service_num;
								$tmp_hantei_type_name = mb_convert_kana($LINE['hantei_type_name'],"asKV", "UTF-8");
								$tmp_hantei_type_name = trim($tmp_hantei_type_name);
								$tmp_hantei_type = array_search($tmp_hantei_type_name, $L_HANTEI_TYPE);
								if ($tmp_hantei_type == 2) {
									$tmp_course_num = get_course_num($service_num, $LINE['course_name']);
								} else {
									$tmp_course_num = "";
								}
								$DATA_SET['_add']['hantei_type'] = $tmp_hantei_type;
								$DATA_SET['_add']['course_num'] = $tmp_course_num;
								// add end okabe 2013/09/05

								$DATA_SET['_add']['problem_num'] = $LINE['problem_num'];
								$DATA_SET['_add']['hantei_default_num'] = $csv_hantei_default_num;

								//すでに同じ問題表示番号の 'すらら問題'データの指示があるか確認
								if ($L_NUM[$csv_hantei_default_num][$csv_display_problem_num]) {	//display_problem_num は disp_sort と同等値

									//すでに同じ番号の問題あり（問題の更新処理）
	//echo "*1) call ht_update_problem<br>";
	//echo "<pre>";print_r($DATA_SET);echo "</pre>";
									list($ERROR_SUB, $L_NUM_RESULT) = ht_update_problem($i, $service_num, $DATA_SET, $L_NUM_RESULT);

									if ($ERROR_SUB) {
										$cnt_err++;		//エラー発生したことをカウント
										$error_flg = 1;
										$ERROR = array_merge($ERROR, $ERROR_SUB);
										continue;
									} else {
										$cnt_upd_problem++;
									}

								} else {
									//同じ番号の問題はない（問題の新規登録（非すらら問題での更新,再設定も含む）、）
									$DATA_SET['_add']['disp_sort'] = $LINE['display_problem_num'];
	//echo "*1) call ht_regist_problem<br>";
	//echo "<pre>";print_r($DATA_SET);echo "</pre>";
									list($ERROR_SUB, $L_NUM_RESULT) = ht_regist_problem($i, $service_num, $DATA_SET, $L_NUM_RESULT);
									if ($ERROR_SUB) {
										$cnt_err++;		//エラー発生したことをカウント
										$error_flg = 1;
										$ERROR = array_merge($ERROR, $ERROR_SUB);
										continue;
									} else {
										$cnt_ins_problem++;
									}

								}


							} else {
								// csvデータ（１行）の後半に、問題データが記載されていない。
								//すらら問題が指定されていたら更新
								$csv_problem_num = $LINE['problem_num'];
								$csv_display_problem_num = $LINE['display_problem_num'];
								if (strlen($csv_problem_num) > 0 && strlen($csv_display_problem_num) > 0) {
									//すでに同じ問題表示番号の 'すらら問題'データの指示があるか確認

									if ($L_NUM[$csv_hantei_default_num][$csv_display_problem_num]) {	//display_problem_num は disp_sort と同等値
										//すでに同じ番号の問題あり（問題の更新処理）
										$DATA_SET['_add']['disp_sort'] = $LINE['display_problem_num'];
										$DATA_SET['_add']['problem_num'] = $LINE['problem_num'];
										$DATA_SET['_add']['hantei_default_num'] = $csv_hantei_default_num;

										// add start okabe 2013/09/05
										$DATA_SET['_add']['service_num'] = $service_num;
										$tmp_hantei_type_name = mb_convert_kana($LINE['hantei_type_name'],"asKV", "UTF-8");
										$tmp_hantei_type_name = trim($tmp_hantei_type_name);
										$tmp_hantei_type = array_search($tmp_hantei_type_name, $L_HANTEI_TYPE);
										if ($tmp_hantei_type == 2) {
											$tmp_course_num = get_course_num($service_num, $LINE['course_name']);
										} else {
											$tmp_course_num = "";
										}
										$DATA_SET['_add']['hantei_type'] = $tmp_hantei_type;
										$DATA_SET['_add']['course_num'] = $tmp_course_num;
										// add end okabe 2013/09/05

	//echo "*2) ".$i." call ht_update_problem<br>";
	//echo "<pre>";print_r($DATA_SET);echo "</pre>";
										list($ERROR_SUB, $L_NUM_RESULT) = ht_update_problem($i, $service_num, $DATA_SET, $L_NUM_RESULT);

										if ($ERROR_SUB) {
											$cnt_err++;		//エラー発生したことをカウント
											$error_flg = 1;
											$ERROR = array_merge($ERROR, $ERROR_SUB);
											continue;
										} else {
											$cnt_upd_problem++;
										}

									} else {
										//同じ番号の問題はない（すらら問題）→新規登録する
										$DATA_SET['_add']['disp_sort'] = $csv_display_problem_num;
										$DATA_SET['_add']['problem_num'] = $csv_problem_num;
										$DATA_SET['_add']['hantei_default_num'] = $csv_hantei_default_num;
										// add start okabe 2013/09/17
										$DATA_SET['_add']['service_num'] = $service_num;
										$tmp_hantei_type_name = mb_convert_kana($LINE['hantei_type_name'],"asKV", "UTF-8");
										$tmp_hantei_type_name = trim($tmp_hantei_type_name);
										$tmp_hantei_type = array_search($tmp_hantei_type_name, $L_HANTEI_TYPE);
										if ($tmp_hantei_type == 2) {
											$tmp_course_num = get_course_num($service_num, $LINE['course_name']);
										} else {
											$tmp_course_num = "";
										}
										$DATA_SET['_add']['hantei_type'] = $tmp_hantei_type;
										$DATA_SET['_add']['course_num'] = $tmp_course_num;
										// add end okabe 2013/09/17
	//echo "*2) ".$i." call ht_regist_srlproblem<br>";
	//echo "<pre>";print_r($DATA_SET);echo "</pre>";
										list($ERROR_SUB, $L_NUM_RESULT) = ht_regist_srlproblem($i, $service_num, $DATA_SET, $L_NUM_RESULT);
										if ($ERROR_SUB) {
											$cnt_err++;		//エラー発生したことをカウント
											$error_flg = 1;
											$ERROR = array_merge($ERROR, $ERROR_SUB);
											continue;
										} else {
											$L_NUM[$csv_hantei_default_num][$csv_display_problem_num] = "1,".$csv_problem_num;
											$cnt_ins_problem++;
										}

									}
								}
							}

						}

					} else {	//if (!$error_flg) {		//エラーが起きた行では処理しない
						$cnt_err++;	//エラー発生行としてカウント
					}

				}

			}

		}	//foreach ($LIST AS $VAL)	//空行は読み飛ばされる

		//参照されていない問題データの掃除
		$sql = "SELECT hmdp.hantei_default_num, hmdp.problem_num, hmdp.problem_table_type, hmdp.disp_sort".
			" FROM ".T_HANTEI_MS_DEFAULT_PROBLEM." hmdp".
			" WHERE hmdp.mk_flg='0';";
		$result = $cdb->query($sql);
		while ($list=$cdb->fetch_assoc($result)) {

			$db_hantei_default_num = $list['hantei_default_num'];
			$db_problem_num = $list['problem_num'];
			$db_problem_table_type =$list['problem_table_type'];
			$db_disp_sort = $list['disp_sort'];

			$check_data = $L_NUM_RESULT[$db_hantei_default_num][$db_disp_sort];
			if ($L_NUM_HANTEIT[$db_hantei_default_num] == "1") {	//TSV登場マスタデータを対象とする
				if (!$check_data || ($check_data != $db_problem_table_type.",".$db_problem_num)) {
					//データは無効（参照されていない）、hantei_ms_default_problem の当該データの mk_flg をセット
					$UPDATE_DATA = array();
					$UPDATE_DATA['mk_flg']		= "1";
					$UPDATE_DATA['mk_tts_id']	= $_SESSION['myid']['id'];
					$UPDATE_DATA['mk_date'] 	= "now()";

					$where = " WHERE hantei_default_num='".$db_hantei_default_num."' AND disp_sort='".$db_disp_sort."' AND mk_flg='0';";
					$ERROR_SUB = $cdb->update(T_HANTEI_MS_DEFAULT_PROBLEM, $UPDATE_DATA, $where);	// hantei_ms_default_problem テーブル
					unset($UPDATE_DATA);
				}
			}
		}

		if ($cnt_all > 0) {
			$PROC_RESULT[] = "データ件数: ".$cnt_all;
			$PROC_RESULT[] = "エラー件数: ".$cnt_err;
			//$PROC_RESULT[] = "新規登録したマスターデータ件数: ".$cnt_ins_master;
			//$PROC_RESULT[] = "更新したマスターデータ件数: ".$cnt_upd_master;
			$PROC_RESULT[] = "新規登録した問題データ件数: ".$cnt_ins_problem;
			$PROC_RESULT[] = "更新した問題データ件数: ".$cnt_upd_problem;
		}

	}	//if ($LIST)	//データ読み込みループ末端

	return  array($ERROR, $PROC_RESULT);
}



/**
 * 判定テスト  TSV問題データのマスタ識別部分の、入力項目チェックルーチン
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 *  IN: $DATA_MS_SET[]  １件分データ、制御値と入力データ
 *  戻り値: array(データ配列、エラーメッセージ配列)
 *                データ配列とは、このルーチン内を呼ぶ際に渡した $DATA_MS_SET[] をすべて返しますが、
 *                check_mode、store_mode により、半角英数字化およびtrim処理を行った結果を
 *                返させることが出来ます。
 * @author Azet
 * @param array $DATA_MS_SET
 * @param array $ERROR
 * @return array
 */
function ht_master_check_subset($DATA_MS_SET, $ERROR) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	global $L_HANTEI_TYPE;

	//制御値
	// $DATA_MS_SET['__info']['call_mode']		// 0=入力フォーム、1:tsv入力
	// $DATA_MS_SET['__info']['line_num']		// 行番号...エラーメッセージに付記するため
	// $DATA_MS_SET['__info']['check_mode']	// チェック時にデータ型を自動調整(半角英数字化、trim処理)するかのスイッチ
	//												0:自動調整しない、1:する
	// $DATA_MS_SET['__info']['store_mode']	// データ自動調整結果を、$DATA_MS_SET['data']['パラメータ名']へ再格納するかのスイッチ
	//												0:再格納しない、1:する
	//
	//入力データ群 (form_type 含む)
	// $DATA_MS_SET['data']['パラメータ名']

	//出力： $error_flg この１件でエラーが発生したら=1、エラー無しは=0

	$error_flg = 0;
	$service_num = $DATA_MS_SET['data']['service_num'];

	//制御情報
	$line_num = "";
	if ($DATA_MS_SET['__info']['call_mode'] == "1") {
		//$line_num = $DATA_MS_SET['__info']['line_num'].": ";	//行番号
		$line_num = line_number_format($DATA_MS_SET['__info']['line_num']);	//行番号
	}
	$check_mode = $DATA_MS_SET['__info']['check_mode'];	//データ型を自動調整(1:する)
	$store_mode = $DATA_MS_SET['__info']['store_mode'];	//自動調整結果の再格納(1:する)


	//データチェック開始

	//hantei_type_name 判定タイプ名(コース判定:1、コース内判定:2)
	$hantei_type_name = $DATA_MS_SET['data']['hantei_type_name'];
	if ($check_mode) {
		$hantei_type_name = mb_convert_kana($hantei_type_name,"asKV", "UTF-8");
		$hantei_type_name = trim($hantei_type_name);
	}
	$hantei_type = array_search($hantei_type_name, $L_HANTEI_TYPE);
	if ($hantei_type < 1 || is_null($hantei_type)) {
		$ERROR[] = $line_num."判定タイプが不正です。";
		$error_flg = 1;
		//return $ERROR;
	}

	//course_name コース名 -> コースコードへの変換
	$course_name = $DATA_MS_SET['data']['course_name'];
	$course_num = "";
	if ($check_mode) {
		$course_name = mb_convert_kana($course_name,"asKV", "UTF-8");
		$course_name = trim($course_name);
	}
	if ($hantei_type == 2) { 	//コース内判定のときに要指定
		if (strlen($course_name) == 0) {
			$ERROR[] = $line_num."コース名が入力されていません。";
			$error_flg = 1;
		} else {
			$course_num = get_course_num($service_num, $course_name);
			if ($course_num < 1 || is_null($course_num)) {
				$ERROR[] = $line_num."コース名が不正です。";
				$error_flg = 1;
			}
		}
	} else {
		if (strlen($course_name) > 0) {
			$ERROR[] = $line_num."コース名の指定は、'コース判定'のときのみ必要です。";
			$error_flg = 1;
		}
	}

	//hantei_display_num 判定テスト表示番号
	$hantei_display_num = $DATA_MS_SET['data']['hantei_display_num'];	//
	if ($check_mode) {
		$hantei_display_num = mb_convert_kana($hantei_display_num, "asKV", "UTF-8");
		$hantei_display_num = trim($hantei_display_num);
	}
	if (strlen($hantei_display_num) > 0) {
		if (preg_match("/[^0-9]/",$hantei_display_num)) {
			$ERROR[] = $line_num."判定テスト表示番号の値が正しくありません。";
			$error_flg = 1;
		}
	} else {
		$ERROR[] = $line_num."判定テスト表示番号が未入力です";
		$error_flg = 1;
	}

	//problem_num すらら問題管理番号
	$problem_num = $DATA_MS_SET['data']['problem_num'];
	if ($check_mode) {
		$problem_num = mb_convert_kana($problem_num, "asKV", "UTF-8");
		$problem_num = trim($problem_num);
	}
	if (strlen($problem_num) > 0) {
		if (preg_match("/[^0-9]/",$problem_num) || $problem_num < 1) {
			$ERROR[] = $line_num."問題管理番号の値が不正です。";
			$error_flg = 1;
		} else {
			//$add_problem_flg = $DATA_MS_SET['data']['add_problem_flg'];
			//if ($add_problem_flg == 0) {
				//指定した番号の すらら問題 が有効な状態で存在するかチェック
				$sql = "SELECT problem.problem_num".
					" FROM ".T_PROBLEM." problem".
					" WHERE problem.state='0'".
					" AND problem.problem_num='".$problem_num."';";
				$result = $cdb->query($sql);
				$list = $cdb->fetch_assoc($result);
				if (!$list) {
					$ERROR[] = $line_num."指定した問題管理番号 ".$problem_num." は無効です。";
					$error_flg = 1;
				}
			//下記のチェックは、問題データの処理時に行う 2013/09/17
			//} else {
			//	$ERROR[] = $line_num."すらら問題を指定した場合、同じ行に問題データは記述できません。";
			//	$error_flg = 1;
			//}
		}
	}

	//ms_course_name 問題データの出典元サービス名	★当面は使用しない★
	//$ms_service_name = $DATA_MS_SET['data']['ms_service_name'];
	$ms_service_num = "";

	//ms_course_name 問題データの出典元コース名
	$ms_course_name = $DATA_MS_SET['data']['ms_course_name'];
	$ms_course_num = "";
	if ($check_mode) {
		$ms_course_name = mb_convert_kana($ms_course_name,"asKV", "UTF-8");
		$ms_course_name = trim($ms_course_name);
	}

	if (strlen($problem_num) > 0) {		//すらら問題を指定されているとき、その出元のコース指定をチェック
		if (strlen($ms_course_name) == 0) {
			//$ERROR[] = $line_num."非判定テストのコース名(ms_course_name)が未入力です。";
			$ERROR[] = $line_num."コース名(ms_course_name)が未入力です。";
			$error_flg = 1;
		} else {
			$ms_course_num = "";
			//コースコードの取得
			$sql = "SELECT course.course_num, course.course_name".
				" FROM ".T_COURSE." course ".
				" WHERE course.course_name = '".$ms_course_name."'".
				" AND course.state = 0;";
			if ($result = $cdb->query($sql)) {
				while ($list = $cdb->fetch_assoc($result)) {
					if ($ms_course_name == $list['course_name']) {
						$ms_course_num = $list['course_num'];
					}
				}
				if ($ms_course_num < 1 || is_null($ms_course_num)) {
					//$ERROR[] = $line_num."非判定テストのコース名(ms_course_name)が不正です。";
					$ERROR[] = $line_num."コース名(ms_course_name)が不正です。";
					$error_flg = 1;
				}
			}
		}
	} else {
		if (strlen($ms_course_name) > 0) {
					//$ERROR[] = $line_num."非判定テストでは、コース名(ms_course_name)の指定は不要です。";
					$ERROR[] = $line_num."テスト専用問題では、コース名(ms_course_name)の指定は不要です。";
					$error_flg = 1;
		}
	}

	//要変換項目結果の格納
	$DATA_MS_SET['data']['hantei_type_num'] = $hantei_type;
	$DATA_MS_SET['data']['course_num']  = $course_num;
	$DATA_MS_SET['data']['ms_service_num'] = $ms_service_num;
	$DATA_MS_SET['data']['ms_course_num'] = $ms_course_num;
	if ($store_mode) {
		//データの再格納
		$DATA_MS_SET['data']['hantei_type_name'] = $hantei_type_name;
		$DATA_MS_SET['data']['course_name']  = $course_name;
		$DATA_MS_SET['data']['hantei_display_num'] = $hantei_display_num;
	}

	return array($DATA_MS_SET, $ERROR, $error_flg);
}

?>
