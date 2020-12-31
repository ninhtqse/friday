<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * テスト用プラクティスステージ管理
 * 本番バッチサーバアップデート
 *
 * @author Azet
 */


$L_TEST_UPDATE_NAME = array(
							'default' => '----------',
							'publishing' => '出版社',
							'book' => '教科書',
							'book_unit' => '教科書単元',
							'lms_problem_time' => '小テスト回答目安時間',				// update oda 2016/11/11 課題要望一覧No566 文言修正
							'standard_category' => '標準化カテゴリ',				// add okabe 2020/09/04 テスト標準化
							'book_trial_list' => '学力診断テストマスタ',
							'book_trial_group' => '学力診断テストグループ',
							'book_trial' => '学力診断テスト単元マスタ',
							'book_trial_unit' => '学力診断テスト単元',
							'hantei_test_master' => '判定テストマスタ',				//add okabe 2013/08/21
							'math_test_book_info' => '数学検定情報',					// add okabe 2020/09/04 テスト標準化
							'math_test_group' => '数学検定グループ',					// add yoshizawa 2015/09/16 02_作業要件/34_数学検定/数学検定
							'math_test_control_unit' => '数学検定出題単元',			// add yoshizawa 2015/09/16 02_作業要件/34_数学検定/数学検定
							'math_test_unit' => '数学検定採点単元',					// add yoshizawa 2015/09/16 02_作業要件/34_数学検定/数学検定
							'vocabulary_test_type' => 'すらら英単語種類',				// add yoshizawa 2018/11/22 すらら英単語
							'vocabulary_test_category1' => 'すらら英単語種別1',		// add yoshizawa 2018/11/22 すらら英単語
							'vocabulary_test_category2' => 'すらら英単語種別2',		// add yoshizawa 2018/11/22 すらら英単語
							'english_word' => 'ワード管理',							// add 2019/05/21 yoshizawa すらら英単語テストワード管理
							'problem' => '問題設定(定期テスト)',
							'problem_trial' => '問題設定(学力診断テスト)',
							'problem_hantei_test' => '問題設定(判定テスト)',			//add okabe 2013/08/21
							'problem_math' => '問題設定(数学検定)',					// add yoshizawa 2015/09/16 02_作業要件/34_数学検定/数学検定
							'problem_vocabulary' => '問題設定(すらら英単語テスト)',		// add yoshizawa 2018/11/22 すらら英単語
							'ms_core_code' => 'コードマスター',
							'upnavi' => '学力Upナビ単元'								//	add ookawara 2012/07/23
						);

set_time_limit(0);

/**
 * HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function start() {

	$html = "";

	if (ACTION == "check") {
		check($ERROR);
	}
	if (!$ERROR) {
		if (ACTION == "update") {
			// update start 2019/07/29 yoshizawa adminアップデートリストのデプロイチェック
			// update($ERROR);
			if( check_deploy_running("Core") ){
				$html .= "<p style=\"color:red; font-weight:bold;\">";
				$html .= "現在、デプロイが実行されているため「本番バッチUP」できませんでした。<br>";
				$html .= "デプロイが完了してから「本番バッチUP」してください。";
				$html .= "</p>";
			} else {
				update($ERROR);
			}
			// update end 2019/07/29 yoshizawa adminアップデートリストのデプロイチェック

		} elseif (ACTION == "delete") {
			delete($ERROR);
		}
	}

	$html .= member_list();

	return $html;
}

/**
 * メンバーのリストコンテンツのHTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function member_list() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	// del start 2018/03/19 yoshizawa AWSプラクティスアップデート
	// // add start oda 2017/09/26 本番バッチのアップ容量を確認可能とする
	// // 検証バッチ経由で本番バッチのディスクサイズを取得する
	// //   srlbtw21 /home/suralacore01/check_disk.sh
	// //   ↓
	// //   srlbhw11 /data/bat/check_disk.sh
	// //   ※このシェルでduコマンドを実行する
	// $max_disk_size = 2000.0;
	// exec("ssh suralacore01@srlbtw21 /home/suralacore01/check_disk.sh", $output, $return_var);
	// if (is_array($output)) {
	// 	$disk_size = 0;
	// 	if ($output[0]) {
	// 		// 取得した情報はタブ区切りの為、分割する
	// 		$size_list = explode("\t", $output[0]);
	// 		// 分割した情報の先頭がディスク使用サイス（KB）
	// 		if (is_array($size_list)) {
	// 			$disk_size = $size_list[0];
	// 		}
	// 		// MBに変換（四捨五入）
	// 		$disk_size = round($disk_size / 1024, 1 );
	// 	}
	// }
	// $zan_disk_size = $max_disk_size - $disk_size;
	// $html .= "<br><br>\n";
	// $html .= "<table class=\"member_form\" style=\"display:inline-block;\">\n";
	// $html .= "<tr class=\"member_form_menu\">\n";
	// $html .= "<td>ファイルアップ制限値(MB)</td>\n";
	// $html .= "<td>ファイルアップ済サイズ(MB)</td>\n";
	// $html .= "<td>ファイルアップ可能サイズ(MB)</td>\n";
	// $html .= "</tr>\n";
	// $html .= "<tr class=\"member_form_cell\" align=\"center\">\n";
	// $html .= "<td>".sprintf('%.1f',$max_disk_size)."</td>\n";
	// $html .= "<td>".sprintf('%.1f',$disk_size)."</td>\n";
	// $html .= "<td>".sprintf('%.1f',$zan_disk_size)."</td>\n";
	// $html .= "</tr>\n";
	// $html .= "</table>\n";
	// $html .= "<div style=\"display:inline-block; font-size:12px;\">ファイルアップ可能サイズが０になるまでアップ可能です。<br>※データベースのアップ量に制限はありません。</div><br>\n";
	// // add end oda 2017/09/26
	// del end 2018/03/19 yoshizawa AWSプラクティスアップデート

	global $L_UNIT_TYPE, $L_TEST_UPDATE_NAME;
	global $L_GKNN_LIST;
	global $L_HANTEI_TYPE;			//add okabe 2013/08/21
	// global $L_MATH_TEST_CLASS;	// add yoshizawa 2015/09/16 02_作業要件/34_数学検定/数学検定 // del 2020/09/15 phong テスト標準化開発
	// add start 2020/09/15 phong テスト標準化開発
	$test_type5 = new TestStdCfgType5($cdb);
	// upd start hirose 2020/09/18 テスト標準化開発
	// $L_MATH_TEST_CLASS = $test_type5->getClassNames();
	$L_MATH_TEST_CLASS = ['select'=>'選択して下さい']+$test_type5->getClassNamesAdmin();
	// upd end hirose 2020/09/18 テスト標準化開発
	// add end 2020/09/15 phong テスト標準化開発



	$sql  = "SELECT test_mate_upd_log.*, course.course_name FROM test_mate_upd_log test_mate_upd_log" .
			" LEFT JOIN course course ON course.course_num=test_mate_upd_log.course_num" .
			" LEFT JOIN stage stage ON stage.stage_num=test_mate_upd_log.stage_num" .
			" LEFT JOIN lesson lesson ON lesson.lesson_num=test_mate_upd_log.lesson_num" .
			" LEFT JOIN unit unit ON unit.unit_num=test_mate_upd_log.unit_num" .
			" LEFT JOIN block block ON block.block_num=test_mate_upd_log.block_num" .
			" WHERE test_mate_upd_log.state='1'".
			" ORDER BY test_mate_upd_log.regist_time DESC;";
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
	}
	if (!$max) {
		$html .= "<br style=\"clear:left;\">";
		$html .= "アップデート予定情報がありません。";
		return $html;
	}

	$html .= "<br style=\"clear:left;\">";
	$html .= "<table class=\"member_form\">\n";
	$html .= "<tr class=\"member_form_menu\">\n";
//	$html .= "<th>アップコンデートテンツ</th>\n";			// del oda 2013/10/16
//	$html .= "<th>教科</th>\n";						// del oda 2013/10/16
	$html .= "<th>アップデートコンテンツ</th>\n";			// add oda 2013/10/16 タイトル修正
	$html .= "<th>コース</th>\n";						// add oda 2013/10/16 タイトル修正
	$html .= "<th>詳細情報</th>\n";
	// $html .= "<th>ファイルアップサイズ(MB)</th>\n";			// add start oda 2017/09/26 本番バッチのアップ容量を確認可能とする // del 2018/03/19 yoshizawa AWSプラクティスアップデート
	$html .= "<th>登録日時</th>\n";
	$html .= "<th>登録者ID</th>\n";
	$html .= "<th>本番バッチUP</th>\n";
	$html .= "<th>検証バッチ削除</th>\n";
	$html .= "</tr>\n";

	$result = $cdb->query($sql);
	while ($list=$cdb->fetch_assoc($result)) {
		foreach($list as $key => $val) {
			$list[$key] = replace_decode($val);
		}

		$detaile = "";
		$SEND_DATA_LOG = unserialize($list['send_data']);

		switch ($list['update_mode']) {
			case 'publishing':
				//	一括
				$detaile = "-----";
				break;
			case 'book':
				//	コース、学年
				$gknn = $SEND_DATA_LOG['gknn'];
				$detaile = $L_GKNN_LIST[$gknn];
				break;
			case 'book_unit':
				//	コース、出版社、学年、教科書
				$publishing_id = $SEND_DATA_LOG['publishing_id'];
				if ($L_PUBLISH) {
					$detaile .= $L_PUBLISH[$publishing_id];
				} else {
					$detaile .= read_publishing($L_PUBLISH, $publishing_id);
				}
				$gknn = $SEND_DATA_LOG['gknn'];
				$detaile .= " ".$L_GKNN_LIST[$gknn];
				$book_id = $SEND_DATA_LOG['book_id'];
				if ($L_BOOK) {
					$detaile .= " ".$L_BOOK[$book_id];
				} else {
					$detaile .= " ".read_book($L_BOOK, $book_id);
				}
				$detaile = trim($detaile);
				break;
			case 'lms_problem_time':
				//	コース、ステージ、Lesson、ユニット
				$detaile = "-----";
				break;
			case 'book_trial_list':
				//	コース
				$detaile = "-----";
				break;
			case 'book_trial_group':
				//	学年
				$L_GKNN_LIST['0'] = "全て";	//	add ookawara 2012/02/23
				$gknn = $SEND_DATA_LOG['gknn'];
				$detaile = $L_GKNN_LIST[$gknn];
				$test_group_id = $SEND_DATA_LOG['test_group_id'];
				if ($L_MS_BOOK_GROUP) {
					$detaile .= " ".$L_MS_BOOK_GROUP[$test_group_id];
				} else {
					$detaile .= " ".read_ms_book_group($L_MS_BOOK_GROUP, $test_group_id);
				}
				$detaile = trim($detaile);
				break;
			case 'book_trial':
				//	コース、学年
				$gknn = $SEND_DATA_LOG['gknn'];
				$detaile = $L_GKNN_LIST[$gknn];
				break;
			case 'book_trial_unit':
				//	コース、学年、マスタ
				$gknn = $SEND_DATA_LOG['gknn'];
				$detaile = $L_GKNN_LIST[$gknn];

				$book_id = $SEND_DATA_LOG['book_id'];
				if ($L_BOOK) {
					$detaile .= " ".$L_BOOK[$book_id];
				} else {
					$detaile .= " ".read_book($L_BOOK, $book_id);
				}
				$detaile = trim($detaile);
				break;
			case 'problem':
				//	コース、学年、テスト時期
				$gknn = $list['stage_num'];
				$core_code = $list['lesson_num'];
				$fileup = $list['unit_num'];

				$detaile = $L_GKNN_LIST[$gknn];
				if ($L_CORE_CODE) {
					$detaile .= " ".$L_CORE_CODE[$core_code];
				} else {
					$detaile .= " ".read_core_code($L_CORE_CODE, $core_code);
				}
				if ($fileup == 1) {
					$detaile .= " (ファイルアップ有り)";
				} else {
					$detaile .= " (ファイルアップ無し)";
				}
				$detaile = trim($detaile);
				break;
			case 'problem_trial':
				//	コース、学年、テスト名
				$gknn = $list['stage_num'];
				$default_test_num = $list['lesson_num'];
				$fileup = $list['unit_num'];

				$detaile = $L_GKNN_LIST[$gknn];
				if ($L_DEFAULT_TEST_NUM) {
					$detaile .= " ".$L_DEFAULT_TEST_NUM[$default_test_num];
				} else {
					$detaile .= " ".read_default_test_num($L_DEFAULT_TEST_NUM, $default_test_num);
				}
				if ($fileup == 1) {
					$detaile .= " (ファイルアップ有り)";
				} else {
					$detaile .= " (ファイルアップ無し)";
				}
				$detaile = trim($detaile);
				break;
			//	add ookawara 2013/09/04 start
			case 'hantei_test_master':
				//	判定テストマスター
			case 'problem_hantei_test':
				//	判定テスト問題データ
				$service_num = $SEND_DATA_LOG['service_num'];
				$hantei_type = $SEND_DATA_LOG['hantei_type'];
				$course_num = $SEND_DATA_LOG['course_num'];
				$hantei_default_num = $SEND_DATA_LOG['hantei_default_num'];
				$fileup = $SEND_DATA_LOG['fileup'];

				if ($L_SERVICE) {
					$detaile = $L_SERVICE[$service_num];
				} else {
					$detaile = read_service($L_SERVICE, $service_num);
				}

				if ($hantei_type > 0) {
					$detaile .= " &gt; ".$L_HANTEI_TYPE[$hantei_type];
				}

				if ($course_num > 0) {
					if ($L_COURSE) {
						$detaile .= " &gt; ".$L_COURSE[$course_num];
					} else {
						$detaile .= " &gt; ".read_couse($L_COURSE, $course_num);
					}
					// コース名を入れ替える			// add oda 2013/10/16  判定テストの場合、test_mate_upd_logテーブルのcourse_numはhantei_typeが入っているので、正しいコース名を入れ替える
					$list['course_name'] = $L_COURSE[$course_num];
				}

				if ($hantei_default_num > 0) {
					if ($L_HANTEIMEI) {
						$detaile .= " &gt; ".$L_HANTEIMEI[$hantei_default_num];
					} else {
						$detaile .= " &gt; ".read_hanteimei($L_HANTEIMEI, $hantei_default_num);
					}
				} else {
					$detaile .= " 以下一括";
				}

				if ($list['update_mode'] == "problem_hantei_test") {
					if ($fileup == 1) {
						$detaile .= " (ファイルアップ有り)";
					} else {
						$detaile .= " (ファイルアップ無し)";
					}
				}

				$detaile = trim($detaile);
				break;
			//	add ookawara 2013/09/04 end
			// add yoshizawa 2015/09/16 02_作業要件/34_数学検定/数学検定
			case 'math_test_group': // 数学検定グループ
				//	級
				$L_MATH_TEST_CLASS['0'] = "全て";
				$class_id = $SEND_DATA_LOG['class_id'];
				$detaile = $L_MATH_TEST_CLASS[$class_id];
				$test_group_id = $SEND_DATA_LOG['test_group_id'];
				if ($L_MS_MATH_TEST_GROUP) {
					$detaile .= " ".$L_MS_MATH_TEST_GROUP[$test_group_id];
				} else {
					$detaile .= " ".read_ms_test_group_math($L_MS_MATH_TEST_GROUP, $test_group_id);
				}
				$detaile = trim($detaile);
				break;
			case 'math_test_control_unit': // 数学検定出題単元
				//	級
				$L_MATH_TEST_CLASS['0'] = "全て";
				$class_id = $SEND_DATA_LOG['class_id'];
				if ($L_MATH_TEST_CLASS) {
					$detaile .= " ".$L_MATH_TEST_CLASS[$class_id];
				}
				$detaile = trim($detaile);
				break;
			case 'math_test_unit': // 数学検定採点単元
				//	級
				$L_MATH_TEST_CLASS['0'] = "全て";
				$class_id = $SEND_DATA_LOG['class_id'];
				if ($L_MATH_TEST_CLASS) {
					$detaile .= " ".$L_MATH_TEST_CLASS[$class_id];
				}
				$detaile = trim($detaile);
				break;
			case 'problem_math': // 問題設定(数学検定)
				//	級
				$L_MATH_TEST_CLASS['0'] = "全て";
				$class_id = $SEND_DATA_LOG['class_id'];
				$detaile = $L_MATH_TEST_CLASS[$class_id];
				$math_group_id = $SEND_DATA_LOG['math_group_id'];
				if ($L_MS_MATH_TEST_GROUP) {
					$detaile .= " ".$L_MS_MATH_TEST_GROUP[$math_group_id];
				} else {
					$detaile .= " ".read_ms_test_group_math($L_MS_MATH_TEST_GROUP, $math_group_id);
				}

				// add start oda 2017/09/26 ファイルアップ表記追加
				$fileup = $SEND_DATA_LOG['fileup'];
				if ($fileup == 1) {
					$detaile .= " (ファイルアップ有り)";
				} else {
					$detaile .= " (ファイルアップ無し)";
				}
				// add end oda 2017/09/26

				$detaile = trim($detaile);
				break;
			// add start 2018/11/22 yoshizawa すらら英単語
			case 'vocabulary_test_type':
				$detaile = "-----";
				break;
			case 'vocabulary_test_category1':
				// アップ対象のテスト種類名を表示
				$test_type_num = $list['course_num'];
				$test_type_name = read_test_type_name($test_type_num);

				$detaile = "テスト種類：".$test_type_name;
				break;
			case 'vocabulary_test_category2':

				// アップ対象のテスト種類名を表示
				$test_type_num = $list['course_num'];
				$test_category1_num = $list['stage_num'];

				$test_type_name = read_test_type_name($test_type_num);
				$detaile = "テスト種類：".$test_type_name;

				if($test_category1_num > 0){
					$test_category1_name = read_ms_test_category1_name($test_type_num,$test_category1_num);
					$detaile .= "　テスト種別1名：".$test_category1_name;
				}

				break;
			// add start 2019/05/21 yoshizawa すらら英単語テストワード管理
			case 'english_word':
				// アップ対象のテスト種類名を表示
				$test_type_num = $list['course_num'];
				$test_type_name = read_test_type_name($test_type_num);

				$detaile = "テスト種類：".$test_type_name;
				break;
			// add end 2019/05/21 yoshizawa すらら英単語テストワード管理
			case 'problem_vocabulary':
				// アップ対象のテスト種類名を表示
				$test_type_num = $list['course_num'];
				$test_category1_num = $list['stage_num'];
				$test_category2_num = $list['lesson_num'];
				$fileup = $list['unit_num'];

				$test_type_name = read_test_type_name($test_type_num);
				$detaile = "テスト種類：".$test_type_name;

				if($test_category1_num > 0){
					$test_category1_name = read_ms_test_category1_name($test_type_num,$test_category1_num);
					$detaile .= "　テスト種別1：".$test_category1_name;
				}

				if($test_category2_num > 0){
					$test_category2_name = read_ms_test_category2_name($test_type_num,$test_category1_num,$test_category2_num);
					$detaile .= "　テスト種別2：".$test_category2_name;
				}

				if ($fileup == 1) {
					$detaile .= " (ファイルアップ有り)";
				} else {
					$detaile .= " (ファイルアップ無し)";
				}

				break;
			// add end 2018/11/22 yoshizawa すらら英単語

			// add start okabe 2020/09/04 テスト標準化
			case 'standard_category':	//学力診断テストマスタ
				//	コース
				$detaile = "-----";
				break;
			case 'math_test_book_info':		//数学検定情報
				//	コース
				$detaile = "-----";
				break;
			// add end okabe 2020/09/04 テスト標準化

			default:
				$detaile = "-----";
				break;
		}

		if (!$detaile) {
			$detaile = "-----";
		}

		if ($list['stage_name']) { $s_l_u_b .= $list['stage_name']; }
		if ($list['lesson_name']) { $s_l_u_b .= "/" . $list['lesson_name']; }
		if ($list['unit_name']) { $s_l_u_b .= "/" . $list['unit_name']; }
		if ($list['block_type']) { $s_l_u_b .= "/" . $L_UNIT_TYPE[$list['block_type']]; }

		if (!$list['course_name']) { $list['course_name'] = "--"; }
		// add yoshizawa 2015/09/16 02_作業要件/34_数学検定/数学検定
		// test_mate_upd_logテーブルのcourse_numにclass_idが入るため、
		// class_id="10"の際に”600点コース”を表示してしまう。数学検定の場合にはコース名は出さない。
		if($list['update_mode'] == 'math_test_group' ||
 		   $list['update_mode'] == 'math_test_control_unit' ||
 		   $list['update_mode'] == 'math_test_unit' ||
 		   $list['update_mode'] == 'problem_math' ){ $list['course_name'] = "--"; }

		// add start 2018/11/22 yoshizawa すらら英単語
		// test_mate_upd_logテーブルのcourse_numにtest_type_numが入るため、
		// course_numとjoinしてしまうのでコース名を無効化する。
		if($list['update_mode'] == 'vocabulary_test_type' ||
 		   $list['update_mode'] == 'vocabulary_test_category1' ||
 		   $list['update_mode'] == 'vocabulary_test_category2' ||
 		   $list['update_mode'] == 'english_word' ||				// add 2019/05/21 yoshizawa すらら英単語テストワード管理
 		   $list['update_mode'] == 'problem_vocabulary' ){ $list['course_name'] = "--"; }
		// add end 2018/11/22 yoshizawa すらら英単語

		// del start 2018/03/19 yoshizawa AWSプラクティスアップデート
		// // add oda 2017/09/26
		// // ファイルアップ有の場合のみ、情報を取得する
		// $disk_size = 0;
		// if ($fileup == 1) {
		// 	// アップコンテンツ毎の使用ディスクサイズを取得する
		// 	$disk_size = file_size_check_test($list['update_mode'], $list['course_num'], $list['stage_num'], $list['lesson_num'], $list['service_num']);
		// }
		// del end 2018/03/19 yoshizawa AWSプラクティスアップデート

		// add start oda 2015/11/09 プラクティスアップデート 不具合対応
		// 同一のリストの本番バッチUPを複数回行うと、ドリル単位で削除してしまう不具合が有る。
		// 画面では、１回実行したリストのボタンを押せない様に修正する
 		$onclick1  = "onclick=\"";
 		$onclick1 .= "if (confirm('".$L_TEST_UPDATE_NAME[$list['update_mode']]."\\n".$list['course_name']."\\n".$detaile."\\nを本番バッチサーバーにアップしてもよろしいでしょうか?')) {";
 		$onclick1 .= " document.honban_".$list['test_mate_upd_log_num'].".batch_up_button.disabled = true; ";
 		$onclick1 .= " document.batch_del_".$list['test_mate_upd_log_num'].".batch_delete_button.disabled = true; ";
 		$onclick1 .= " document.honban_".$list['test_mate_upd_log_num'].".submit();";
 		$onclick1 .= "};";
 		$onclick1 .= " return false;\"";

        // add start 2019/04/02 yoshizawa すらら英単語 リリースまで本番反映は不可とします。
		// if($list['update_mode'] == 'vocabulary_test_type' ||
 		//    $list['update_mode'] == 'vocabulary_test_category1' ||
 		//    $list['update_mode'] == 'vocabulary_test_category2' ||
 		//    $list['update_mode'] == 'problem_vocabulary' ){
        //         $onclick1 = '';
        //         $onclick1  = "onclick=\"alert('本番アップはできません。'); return false;\"";
        //         $onclick1 .= " disabled=\"disabled\"";
        // }
        // add end 2019/04/02 yoshizawa すらら英単語


		// del start 2018/03/19 yoshizawa AWSプラクティスアップデート
		// // add start oda 2017/09/27 アップ容量が残サイズより大きい場合は、ボタンを非表示とする
 		// if ($fileup == 1 && $disk_size > $zan_disk_size) {
 		// 	$onclick1  = "onclick=\"";
	 	// 	$onclick1 .= "alert('ファイルアップ可能サイズを超えています。');";
 		// 	$onclick1 .= " return false;\"";
		// }
		// // ファイルをアップするリストは何もしない
		// if ($list['update_mode'] == "problem"      || $list['update_mode'] == "problem_trial"       ||
		// 	$list['update_mode'] == "problem_math" || $list['update_mode'] == "problem_hantei_test" ) {
		// }
		// // ファイルをアップしないリストは<br>に置き換える
		// else {
		// 	$disk_size = "<br>";
		// }
		// // add end oda 2017/09/27
		// del end 2018/03/19 yoshizawa AWSプラクティスアップデート

 		$onclick2  = "onclick=\"";
 		$onclick2 .= "if (confirm('".$L_TEST_UPDATE_NAME[$list['update_mode']]."\\n".$list['course_name']."\\n".$detaile."\\nを検証バッチサーバーから削除してもよろしいでしょうか?')) {";
 		$onclick2 .= " document.honban_".$list['test_mate_upd_log_num'].".batch_up_button.disabled = true; ";
 		$onclick2 .= " document.batch_del_".$list['test_mate_upd_log_num'].".batch_delete_button.disabled = true; ";
		$onclick2 .= " document.batch_del_".$list['test_mate_upd_log_num'].".submit();";
 		$onclick2 .= "};";
 		$onclick2 .= " return false;\"";
 		// add end oda 2015/11/09

		$html .= "<tr class=\"member_form_cell\" align=\"center\">\n";
		$html .= "<td align=\"left\">".$L_TEST_UPDATE_NAME[$list['update_mode']]."</td>\n";
		$html .= "<td>".$list['course_name']."</td>\n";
		$html .= "<td align=\"left\">".$detaile."</td>\n";
		// $html .= "<td align=\"right\">".$disk_size."</td>\n";					// add start oda 2017/09/26 本番バッチのアップ容量を確認可能とする // del 2018/03/19 yoshizawa AWSプラクティスアップデート
		$html .= "<td>".str_replace("-","/",$list['regist_time'])."</td>\n";
		$html .= "<td>".$list['upd_tts_id']."</td>\n";
		$html .= "<td>\n";
		//$html .= "<form actiion=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";															// del oda 2015/11/09 プラクティスアップデート 不具合対応
		$html .= "<form actiion=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"honban_".$list['test_mate_upd_log_num']."\">\n";		// add oda 2015/11/09 プラクティスアップデート 不具合対応
		$html .= "<input type=\"hidden\" name=\"action\" value=\"update\">\n";
		$html .= "<input type=\"hidden\" name=\"test_mate_upd_log_num\" value=\"".$list['test_mate_upd_log_num']."\">\n";
		//$html .= "<input type=\"submit\" value=\"本番バッチUP\" onclick=\"return confirm('".$L_TEST_UPDATE_NAME[$list['update_mode']]."\\n".$list['course_name']."\\n".$detaile."\\nを本番バッチサーバーにアップしてもよろしいでしょうか?')\">\n";	// del oda 2015/11/09 プラクティスアップデート 不具合対応
		$html .= "<input type=\"button\" name=\"batch_up_button\" value=\"本番バッチUP\" ".$onclick1.">\n";									// add oda 2015/11/09 プラクティスアップデート 不具合対応
		$html .= "</form>\n";
		$html .= "</td>\n";
		$html .= "<td>\n";
		//$html .= "<form actiion=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n";															// del oda 2015/11/09 プラクティスアップデート 不具合対応
		$html .= "<form actiion=\"".$_SERVER['PHP_SELF']."\" method=\"POST\" name=\"batch_del_".$list['test_mate_upd_log_num']."\">\n";		// add oda 2015/11/09 プラクティスアップデート 不具合対応
		$html .= "<input type=\"hidden\" name=\"action\" value=\"delete\">\n";
		$html .= "<input type=\"hidden\" name=\"test_mate_upd_log_num\" value=\"".$list['test_mate_upd_log_num']."\">\n";
		//$html .= "<input type=\"submit\" value=\"検証バッチ削除\" onclick=\"return confirm('".$L_TEST_UPDATE_NAME[$list['update_mode']]."\\n".$list['course_name']."\\n".$detaile."\\nを検証バッチサーバーから削除してもよろしいでしょうか?')\">\n";
		$html .= "<input type=\"button\" name=\"batch_delete_button\" value=\"検証バッチ削除\" ".$onclick2.">\n";							// add oda 2015/11/09 プラクティスアップデート 不具合対応
		$html .= "</form>\n";
		$html .= "</td>\n";
		$html .= "</tr>\n";
	}
	$html .= "</table>\n";

	return $html;
}

/**
 *
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array &$ERROR
 */
function check(&$ERROR) {
	return ;
}

/**
 * 本番バッチアップ
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array &$ERROR
 */
function update(&$ERROR) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//	更新情報取得
	// update start oda 2018/11/26 プラクティスアップデート不具合 stateを参照し、存在チェックを行う様に修正
// 	$sql  = "SELECT update_mode, service_num, course_num, stage_num, lesson_num, unit_num, block_num, send_data FROM test_mate_upd_log".	//	add service_num ookawara 2013/09/04
// 			" WHERE test_mate_upd_log_num='".$_POST['test_mate_upd_log_num']."' LIMIT 1";
// 	if ($result = $cdb->query($sql)) {
// 		$list = $cdb->fetch_assoc($result);
// 		foreach ($list AS $key => $val) {
// 			$$key = $val;
// 		}
// 	}

	// 存在チェックフラグ初期化
	$exist_flg = false;

	$sql  = "SELECT ".
			"  update_mode, ".
			"  service_num, ".
			"  course_num, ".
			"  stage_num, ".
			"  lesson_num, ".
			"  unit_num, ".
			"  block_num, ".
			"  send_data ".
			" FROM test_mate_upd_log ".
			" WHERE test_mate_upd_log_num='".$_POST['test_mate_upd_log_num']."' ".
			"   AND state = '1' ".
			" LIMIT 1";
	if ($result = $cdb->query($sql)) {
		if ($list = $cdb->fetch_assoc($result)) {
			foreach ($list AS $key => $val) {
				$$key = $val;
			}
			$exist_flg = true;			// 更新ログが存在したらバッチを実行する（複数画面で操作した場合にstateが変わってしまっている可能性がある為）
		}
	}

	// アップデートリスト用のデータが取得できない場合は、なにもせず終了
	if ($exist_flg == false) { return; }
	// update end oda 2018/11/26

	// add start 2019/08/06 yoshizawa デプロイチェック対策
	// 更新情報を実行中状態(state = 3)にする
	$DELETE_DATA = array();
	unset($DELETE_DATA);
	$where = " WHERE test_mate_upd_log_num='".$_POST['test_mate_upd_log_num']."'";
	$DELETE_DATA['start_time'] = "now()";
	$DELETE_DATA['state'] = 3;
	$DELETE_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
	$ERROR = $cdb->update('test_mate_upd_log',$DELETE_DATA,$where);
	// add end 2019/08/06 yoshizawa デプロイチェック対策

	$send_value = "";
	// if ($update_mode == "problem" || $update_mode == "problem_trial") {										// del 2018/11/22 yoshizawa すらら英単語
	if ($update_mode == "problem" || $update_mode == "problem_trial" || $update_mode == "problem_vocabulary") {	// add 2018/11/22 yoshizawa すらら英単語

		if ($course_num == "") {
			$course_num = "0";
		}
		if ($stage_num == "") {
			$stage_num = "0";
		}
		if ($lesson_num == "") {
			$lesson_num = "0";
		}
		if ($unit_num == "") {
			$unit_num = "0";
		}
		$send_value .= " '".$course_num."'";
		$send_value .= " '".$stage_num."'";
		$send_value .= " '".$lesson_num."'";
		$send_value .= " '".$unit_num."'";


	// add start 2018/04/02 yoshizawa
	} elseif ($update_mode == "problem_math") {
		if ($course_num == "") {
			$course_num = "0";
		}
		if ($stage_num == "") {
			$stage_num = "0";
		}
		$send_value .= " '".$course_num."'";	// class_id
		$send_value .= " '".$stage_num."'";		// fileup
	// add end 2018/04/02 yoshizawa

	//	add ookawara 2013/09/04 start
	} elseif ($update_mode == "hantei_test_master" || $update_mode == "problem_hantei_test") {
		$SEND_DATA_LOG = unserialize($send_data);
		$service_num = $SEND_DATA_LOG['service_num'];
		$hantei_type = $SEND_DATA_LOG['hantei_type'];
		$course_num = $SEND_DATA_LOG['course_num'];
		$hantei_default_num = $SEND_DATA_LOG['hantei_default_num'];
		$fileup = $SEND_DATA_LOG['fileup'];

		if ($service_num == "") {
			$service_num = "0";
		}
		if ($hantei_type == "") {
			$hantei_type = "0";
		}
		if ($course_num == "") {
			$course_num = "0";
		}
		if ($hantei_default_num == "") {
			$hantei_default_num = "0";
		}
		if ($fileup == "") {
			$fileup = "0";
		}
		$send_value .= " '".$service_num."'";
		$send_value .= " '".$hantei_type."'";
		$send_value .= " '".$course_num."'";
		$send_value .= " '".$hantei_default_num."'";
		$send_value .= " '".$fileup."'";
	//	add ookawara 2013/09/04 end
	} else {
		if ($course_num) {
			$send_value .= " '".$course_num."'";
			if ($stage_num) {
				$send_value .= " '".$stage_num."'";
				if ($lesson_num) {
					$send_value .= " '".$lesson_num."'";
					if ($unit_num) {
						$send_value .= " '".$unit_num."'";
						if ($block_num) {
							$send_value .= " '".$block_num."'";
						}
					}
				}
			}
		}
	}

	//	add ookawara 2012/08/21
	//if ($update_mode == "problem" || $update_mode == "problem_trial") {	//	del ookawara 2013/09/04
	//if ($update_mode == "problem" || $update_mode == "problem_trial" || $update_mode == "problem_hantei_test") {	//	add ookawara 2013/09/04
	if ($update_mode == "problem"
		|| $update_mode == "problem_trial"
		|| $update_mode == "problem_hantei_test"
		|| $update_mode == "problem_math"
		|| $update_mode == "problem_vocabulary" // add 2018/11/22 yoshizawa すらら英単語
	) {	//	add oda 2015/11/09

		global $L_CONTENTS_DB;
		////	検証バッチDB接続
		//$connect_db = new connect_db();
		//$connect_db->set_db($L_CONTENTS_DB['92']);
		//$ERROR = $connect_db->set_connect_db();
		//if (!$ERROR) {
		//	$tbw_db = $connect_db->get_db();
		//	$tbw_dbname = $connect_db->get_dbname();
		//}
    	$connect_db = new connect_db();

		$db_data = $L_CONTENTS_DB['92'];
		//$db_data['DBNAME'] = "SRLBS99";				// 2015/11/10 oda デバッグ用テーブル切り替え

    	//$connect_db->set_db($L_CONTENTS_DB['92']);
    	$connect_db->set_db($db_data);

    	$ERROR = $connect_db->set_connect_db();

		//	検証サーバー反映処理記録
		$update_num = 0;
		$sql  = "INSERT INTO test_update_check".
				" (type, start_time)".
				" VALUE('1', now());";
		if ($connect_db->query($sql)) {
			$update_num = $connect_db->insert_id();
		}

		echo "<br>\n";
		echo "反映中<br>\n";
		flush();
	}

	//	データー移行・削除
	$update_mode = "test_".$update_mode;
	//	add ookawara 2013/09/04 start
	if ($update_mode == "test_hantei_test_master" || $update_mode == "test_problem_hantei_test") {
		//$command = "ssh suralacore01@srlbtw21 /home/suralacore01/batch_test/HANTEITESTCONTENTSUP.php '1' '".$update_mode."'".$send_value;		// add oda 2015/11/10 プラクティスアップデート不具合対応(デバッグ用)
		// $command = "ssh suralacore01@srlbtw21 ./HANTEITESTCONTENTSUP.cgi '1' '".$update_mode."'".$send_value; // del 2018/03/19 yoshizawa AWSプラクティスアップデート
		$command = "/usr/bin/php ".BASE_DIR."/_www/batch/HANTEITESTCONTENTSUP.cgi '1' '".$update_mode."'".$send_value; // add 2018/03/19 yoshizawa AWSプラクティスアップデート
	} else {
	//	add ookawara 2013/09/04 end
		//$command = "ssh suralacore01@srlbtw21 /home/suralacore01/batch_test/TESTCONTENTSUP.php '1' '".$update_mode."'".$send_value;			// add oda 2015/11/10 プラクティスアップデート不具合対応(デバッグ用)
		// $command = "ssh suralacore01@srlbtw21 ./TESTCONTENTSUP.cgi '1' '".$update_mode."'".$send_value; // del 2018/03/19 yoshizawa AWSプラクティスアップデート
		$command = "/usr/bin/php ".BASE_DIR."/_www/batch/TESTCONTENTSUP.cgi '1' '".$update_mode."'".$send_value; // add 2018/03/19 yoshizawa AWSプラクティスアップデート
	}	//	add ookawara 2013/09/04

	//if ($update_mode == "test_problem" || $update_mode == "test_problem_trial") {	//	del ookawara 2013/09/04
	//if ($update_mode == "test_problem" || $update_mode == "test_problem_trial" || $update_mode == "test_problem_hantei_test") {	//	add ookawara 2013/09/04
	// テストの問題をアップする場合は、待ち受け処理を実行する
	if ($update_mode == "test_problem"
		|| $update_mode == "test_problem_trial"
		|| $update_mode == "test_problem_hantei_test"
		|| $update_mode == "test_problem_math"
		|| $update_mode == "test_problem_vocabulary" // add 2018/11/22 yoshizawa すらら英単語
	) {	//	add oda 2015/11/09
		$command .= " '".$update_num."'";
		$command .= " '".$_SESSION['myid']['id']."'";				// add oda 2015/11/10 プラクティスアップデート不具合対応
		$command .= " '".$_POST['test_mate_upd_log_num']."'";		// add oda 2015/11/10 プラクティスアップデート不具合対応
		$command .= " > /dev/null &";	//	add ookawara 2012/08/21

		//echo $command."<br>";

		exec($command);

		if ($update_num > 0) {
			$end_flg = 1;
			for ($i=0; $i<=600; $i++) {
				$sql  = "SELECT state FROM test_update_check".
						" WHERE update_num='".$update_num."';";
				if ($result = $connect_db->query($sql)) {
					$list = $connect_db->fetch_assoc($result);
					$state = $list['state'];
				}

				if ($state != 0) {
					$end_flg = 0;
					break;
				}

				echo "・";
				flush();
				sleep(2);
			}

			echo "<br>\n";
			flush();
		}

		if ($end_flg == 1) {
			echo "<br>\n";
			echo "反映処理が完了しておりませんがタイムアウト防止の為次の処理に進みます。<br>\n";
			flush();
		}
		echo "<br>\n";
		echo "反映終了<br>\n";
		flush();

	// テストの問題以外の場合は、待ち受け処理を行わない
	} else {
		//upd start 2017/11/27 yamaguchi AWS移設
		//exec($command,&$LIST);
		exec($command,$LIST);
		//upd end 2017/11/27 yamaguchi

		// add start oda 2015/11/10 プラクティスアップデート不具合 問題以外のログ更新は、ここで行う
		$DELETE_DATA = array();
		unset($DELETE_DATA);
		$where = " WHERE test_mate_upd_log_num='".$_POST['test_mate_upd_log_num']."'";
		$DELETE_DATA['regist_time'] = "now()";
		$DELETE_DATA['state'] = 2;
		$DELETE_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
		$ERROR = $cdb->update('test_mate_upd_log',$DELETE_DATA,$where);
		// add end oda 2015/11/10
	}
//echo $command."<br>";

	// del start oda 2015/11/10 プラクティスアップデート不具合 ログ更新はcgiで行う
	//unset($DELETE_DATA);
	//$where = " WHERE test_mate_upd_log_num='".$_POST['test_mate_upd_log_num']."'";
	//$DELETE_DATA['regist_time'] = "now()";
	//$DELETE_DATA['state'] = 2;
	//$DELETE_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
	//$ERROR = $cdb->update('test_mate_upd_log',$DELETE_DATA,$where);
	// del end oda 2015/11/10

	return ;
}


/**
 * 検証バッチ削除
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array &$ERROR
 */
function delete(&$ERROR) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

 	//	更新情報取得
	// update start oda 2018/11/26 プラクティスアップデート不具合 stateを参照し、存在チェックを行う様に修正
// 	$sql  = "SELECT update_mode, service_num, course_num, stage_num, lesson_num, unit_num, block_num, send_data FROM test_mate_upd_log".	//	add service_num ookawara 2013/09/04
// 			" WHERE test_mate_upd_log_num='".$_POST['test_mate_upd_log_num']."' LIMIT 1";
// 	if ($result = $cdb->query($sql)) {
// 		$list = $cdb->fetch_assoc($result);
// 		foreach ($list AS $key => $val) {
// 			$$key = $val;
// 		}
// 	}

	// 存在チェックフラグ初期化
	$exist_flg = false;

	$sql  = "SELECT ".
			"  update_mode, ".
			"  service_num, ".
			"  course_num, ".
			"  stage_num, ".
			"  lesson_num, ".
			"  unit_num, ".
			"  block_num, ".
			"  send_data ".
			" FROM test_mate_upd_log ".
			" WHERE test_mate_upd_log_num='".$_POST['test_mate_upd_log_num']."' ".
			"   AND state = '1' ".
			" LIMIT 1";
	if ($result = $cdb->query($sql)) {
		if ($list = $cdb->fetch_assoc($result)) {
			foreach ($list AS $key => $val) {
				$$key = $val;
			}
			$exist_flg = true;			// 更新ログが存在したらバッチを実行する（複数画面で操作した場合にstateが変わってしまっている可能性がある為）
		}
	}

	// アップデートリスト用のデータが取得できない場合は、なにもせず終了
	if ($exist_flg == false) { return; }
	// update end oda 2018/11/26

	// add start 2019/08/06 yoshizawa デプロイチェック対策
	// 処理の開始時間を記録する
	$DELETE_DATA = array();
	unset($DELETE_DATA);
	$where = " WHERE test_mate_upd_log_num='".$_POST['test_mate_upd_log_num']."'";
	$DELETE_DATA['start_time'] = "now()";
	$DELETE_DATA['upd_tts_id'] = $_SESSION['myid']['id'];

	$ERROR = $cdb->update('test_mate_upd_log',$DELETE_DATA,$where);
	// add end 2019/08/06 yoshizawa デプロイチェック対策

	// update start 2018/03/19 yoshizawa
	// 本番バッチアップと条件に差異があるので本番バッチアップと条件を揃えます。
	// if ($update_mode == "problem" || $update_mode == "problem_trial" || $update_mode == "problem_hantei_test") {
	// 	if ($course_num == "") {
	// 		$course_num = "0";
	// 	}
	// 	if ($stage_num == "") {
	// 		$stage_num = "0";
	// 	}
	// 	if ($lesson_num == "") {
	// 		$lesson_num = "0";
	// 	}
	// 	if ($unit_num == "") {
	// 		$unit_num = "0";
	// 	}
	// 	if ($block_num == "") {
	// 		$block_num = "0";
	// 	}
	// }
	//
	$send_value = "";
	if ($update_mode == "problem"
		|| $update_mode == "problem_trial"
		|| $update_mode == "problem_vocabulary" // add yoshizawa 2018/11/22 すらら英単語
	) {
		if ($course_num == "") {
			$course_num = "0";
		}
		if ($stage_num == "") {
			$stage_num = "0";
		}
		if ($lesson_num == "") {
			$lesson_num = "0";
		}
		if ($unit_num == "") {
			$unit_num = "0";
		}
		if ($block_num == "") {	// add 2018/04/02 yoshizawa
			$block_num = "0";	// add 2018/04/02 yoshizawa
		}						// add 2018/04/02 yoshizawa
		$send_value .= " '".$course_num."'";
		$send_value .= " '".$stage_num."'";
		$send_value .= " '".$lesson_num."'";
		$send_value .= " '".$unit_num."'";
		$send_value .= " '".$block_num."'";						// add 2018/04/02 yoshizawa
		$send_value .= " '".$_SESSION['myid']['id']."'";		// add 2018/04/02 yoshizawa
		$send_value .= " '".$_POST['test_mate_upd_log_num']."'";// add 2018/04/02 yoshizawa

	// add start 2018/04/02 yoshizawa
	} elseif ($update_mode == "problem_math") {
		if ($course_num == "") {
			$course_num = "0";
		}
		$send_value .= " '".$course_num."'";	// class_id
		$send_value .= " '".$_SESSION['myid']['id']."'";
		$send_value .= " '".$_POST['test_mate_upd_log_num']."'";
	// add end 2018/04/02 yoshizawa

	} elseif ($update_mode == "hantei_test_master" || $update_mode == "problem_hantei_test") {
		$SEND_DATA_LOG = unserialize($send_data);
		$service_num = $SEND_DATA_LOG['service_num'];
		$hantei_type = $SEND_DATA_LOG['hantei_type'];
		$course_num = $SEND_DATA_LOG['course_num'];
		$hantei_default_num = $SEND_DATA_LOG['hantei_default_num'];
		$fileup = $SEND_DATA_LOG['fileup'];

		if ($service_num == "") {
			$service_num = "0";
		}
		if ($hantei_type == "") {
			$hantei_type = "0";
		}
		if ($course_num == "") {
			$course_num = "0";
		}
		if ($hantei_default_num == "") {
			$hantei_default_num = "0";
		}
		if ($fileup == "") {
			$fileup = "0";
		}
		$send_value .= " '".$service_num."'";
		$send_value .= " '".$hantei_type."'";
		$send_value .= " '".$course_num."'";
		$send_value .= " '".$hantei_default_num."'";
		$send_value .= " '".$fileup."'";

		// add start hasegawa 2018/03/28 AWS移設
		if ($update_mode == "problem_hantei_test") {
			$send_value .= " '".$_POST['test_mate_upd_log_num']."'";
			$send_value .= " '".$_SESSION['myid']['id']."'";
		}
		// add end hasegawa 2018/03/28
	} else {
		if ($course_num) {
			$send_value .= " '".$course_num."'";
			if ($stage_num) {
				$send_value .= " '".$stage_num."'";
				if ($lesson_num) {
					$send_value .= " '".$lesson_num."'";
					if ($unit_num) {
						$send_value .= " '".$unit_num."'";
						if ($block_num) {
							$send_value .= " '".$block_num."'";
						}
					}
				}
			}
		}
	}
	// update end 2018/03/19 yoshizawa

	//	データー削除
	$update_mode = "test_".$update_mode;
	//	add ookawara 2013/09/04 start
	if ($update_mode == "test_hantei_test_master" || $update_mode == "test_problem_hantei_test") {
		//$command = "ssh suralacore01@srlbtw21 ./HANTEITESTCONTENTSUP.cgi '3' '".$update_mode."' '".$service_num."' '".$course_num."' '".$stage_num."' '".$lesson_num."' '".$unit_num."'";
		//$command = "ssh suralacore01@srlbtw21 /home/suralacore01/batch_test/HANTEITESTCONTENTSUP.php '3' '".$update_mode."' '".$course_num."' '".$stage_num."' '".$lesson_num."' '".$unit_num."' '".$_SESSION['myid']['id']."' '".$_POST['test_mate_upd_log_num']."'";		// add oda 2015/11/10 プラクティスアップデート不具合対応(デバッグ用)
		// $command = "ssh suralacore01@srlbtw21 ./HANTEITESTCONTENTSUP.cgi '3' '".$update_mode."' '".$course_num."' '".$stage_num."' '".$lesson_num."' '".$unit_num."' '".$_SESSION['myid']['id']."' '".$_POST['test_mate_upd_log_num']."'";									// add oda 2015/11/13 プラクティスアップデート不具合対応 // del 2018/03/19 yoshizawa AWSプラクティスアップデート
		$command = "/usr/bin/php ".BASE_DIR."/_www/batch/HANTEITESTCONTENTSUP.cgi '3' '".$update_mode."'".$send_value;																		// add 2018/03/19 yoshizawa AWSプラクティスアップデート
	} else {
	//	add ookawara 2013/09/04 end
		//$command = "ssh suralacore01@srlbtw21 ./TESTCONTENTSUP.cgi '3' '".$update_mode."' '".$course_num."' '".$stage_num."' '".$lesson_num."' '".$unit_num."' '".$block_num."'";		// del oda 2015/11/10 プラクティスアップデート不具合対応
		//$command = "ssh suralacore01@srlbtw21 /home/suralacore01/batch_test/TESTCONTENTSUP.php '3' '".$update_mode."' '".$course_num."' '".$stage_num."' '".$lesson_num."' '".$unit_num."' '".$block_num."' '".$_SESSION['myid']['id']."' '".$_POST['test_mate_upd_log_num']."'";		// add oda 2015/11/10 プラクティスアップデート不具合対応(デバッグ用)
		// $command = "ssh suralacore01@srlbtw21 ./TESTCONTENTSUP.cgi '3' '".$update_mode."' '".$course_num."' '".$stage_num."' '".$lesson_num."' '".$unit_num."' '".$block_num."' '".$_SESSION['myid']['id']."' '".$_POST['test_mate_upd_log_num']."'";								// add oda 2015/11/13 プラクティスアップデート不具合対応 // del 2018/03/19 yoshizawa AWSプラクティスアップデート
		$command = "/usr/bin/php ".BASE_DIR."/_www/batch/TESTCONTENTSUP.cgi '3' '".$update_mode."'".$send_value;							// add 2018/03/19 yoshizawa AWSプラクティスアップデート
	}	//	add ookawara 2013/09/04

// echo $command."<br>";
// pre($LIST);

	//upd start 2017/11/27 yamaguchi AWS移設
	//exec($command,&$LIST);
	exec($command,$LIST);
	//upd end 2017/11/27 yamaguchi

	// update start oda 2015/11/10 プラクティスアップデート不具合 問題のアップの時は、ログ更新はcgiで行う。問題以外の時は、ここでログを更新する
	//unset($DELETE_DATA);
	//$where = " WHERE test_mate_upd_log_num='".$_POST['test_mate_upd_log_num']."'";
	//$DELETE_DATA['regist_time'] = "now()";
	//$DELETE_DATA['state'] = 0;
	//$DELETE_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
	//$ERROR = $cdb->update('test_mate_upd_log',$DELETE_DATA,$where);
//echo "update_mode=".$update_mode."<br>";
	if ($update_mode != "test_problem"
		&& $update_mode != "test_problem_trial"
		&& $update_mode != "test_problem_hantei_test"
		&& $update_mode != "test_problem_math"
		&& $update_mode != "test_problem_vocabulary" // add 2018/11/22 yoshizawa すらら英単語
	) {

		$DELETE_DATA = array();
		unset($DELETE_DATA);
		$where = " WHERE test_mate_upd_log_num='".$_POST['test_mate_upd_log_num']."'";
		$DELETE_DATA['regist_time'] = "now()";
		$DELETE_DATA['state'] = 0;
		$DELETE_DATA['upd_tts_id'] = $_SESSION['myid']['id'];
		$ERROR = $cdb->update('test_mate_upd_log',$DELETE_DATA,$where);
	}
	// update end oda 2015/11/10

	return ;
}


/**
 * 出版社
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array &$L_PUBLISH
 * @param mixed $id
 * @return string
 */
function read_publishing(&$L_PUBLISH, $id) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$name = "";
	$L_PUBLISH = array();

	$sql  = "SELECT publishing_id, publishing_name FROM ".T_MS_PUBLISHING.
			" WHERE mk_flg='0'".
			" AND publishing_id!='0';";
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$publishing_id = $list['publishing_id'];
			$publishing_name = $list['publishing_name'];
			$L_PUBLISH[$publishing_id] = $publishing_name;
		}
	}

	$name = $L_PUBLISH[$id];

	return $name;
}


/**
 * 教科書
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array &$L_BOOK
 * @param mixed $id
 * @return string
 */
function read_book(&$L_BOOK, $id) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$name = "";
	$L_BOOK = array();

	$sql  = "SELECT book_id, book_name FROM ".T_MS_BOOK." ms_book".
			" WHERE mk_flg='0';";
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$book_id = $list['book_id'];
			$book_name = $list['book_name'];
			$L_BOOK[$book_id] = $book_name;
		}
	}

	$name = $L_BOOK[$id];

	return $name;
}


/**
 * テスト時期
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array &$L_CORE_CODE
 * @param mixed $id
 * @return string
 * @return string
 */
function read_core_code(&$L_CORE_CODE, $id) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$name = "";
	$L_CORE_CODE = array();

	$sql  = "SELECT bnr_cd, kmk_cd, bnr_nm, kmk_nm FROM ".T_MS_CORE_CODE.
			" WHERE mk_flg='0';";
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$value = $list['bnr_cd']."_".$list['kmk_cd'];
			$name = $list['bnr_nm']."_".$list['kmk_nm'];
			$L_CORE_CODE[$value] = $name;
		}
	}

	$name = $L_CORE_CODE[$id];

	return $name;
}


/**
 * テスト名
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array &$L_DEFAULT_TEST_NUM
 * @param mixed $id
 * @return string
 */
function read_default_test_num(&$L_DEFAULT_TEST_NUM, $id) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$name = "";
	$L_DEFAULT_TEST_NUM = array();

	$sql  = "SELECT default_test_num, test_name FROM " . T_MS_TEST_DEFAULT .
			" WHERE mk_flg='0'".
			" AND test_type='4'".
			" AND default_test_num!='0'".
			" ORDER BY disp_sort;";
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$default_test_num = $list['default_test_num'];
			$test_name = $list['test_name'];
			$L_DEFAULT_TEST_NUM[$default_test_num] = $test_name;
		}
	}

	$name = $L_DEFAULT_TEST_NUM[$id];

	return $name;
}


/**
 * グループ名
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array &$L_MS_BOOK_GROUP
 * @param mixed $id
 * @return string
 */
function read_ms_book_group(&$L_MS_BOOK_GROUP, $id) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$name = "";
	$L_MS_BOOK_GROUP = array();

	$sql  = "SELECT * FROM ".T_MS_BOOK_GROUP.
			" WHERE mk_flg='0'".
			// " AND srvc_cd = 'GTEST';";  // add yoshizawa 2015/09/29 02_作業要件/34_数学検定   // del 2020/09/14 thanh テスト標準化開発
			" AND class_id = '';";	// add 2020/09/14 thanh テスト標準化開発
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$test_group_id = $list['test_group_id'];
			$test_group_name = $list['test_group_name'];
			$L_MS_BOOK_GROUP[$test_group_id] = $test_group_name;
		}
	}

	$name = $L_MS_BOOK_GROUP[$id];

	return $name;
}

/**
 * 数学検定グループ名
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array &$L_MS_MATH_TEST_GROUP
 * @param mixed $id
 * @return string
 */
function read_ms_test_group_math(&$L_MS_MATH_TEST_GROUP, $id) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$name = "";
	$L_MS_MATH_TEST_GROUP = array();
	$sql  = "SELECT * FROM ".T_MS_BOOK_GROUP.
			" WHERE mk_flg='0'".
			// " AND srvc_cd = 'STEST'"; // del 2020/09/14 cuong テスト標準化開発
			" AND class_id > ''"; // add 2020/09/14 cuong テスト標準化開発

	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$test_group_id = $list['test_group_id'];
			$test_group_name = $list['test_group_name'];
			$L_MS_MATH_TEST_GROUP[$test_group_id] = $test_group_name;
		}
	}
	$name = $L_MS_MATH_TEST_GROUP[$id];

	return $name;
}

//	add ookawara 2013/09/04 start
/**
 * サービス名
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array &$L_SERVICE
 * @param mixed $id
 * @return string
 */
function read_service(&$L_SERVICE, $id) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$name = "";
	$sql  = "SELECT service_num, service_name FROM ".T_SERVICE.
			" WHERE mk_flg='0';";
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$service_num = $list['service_num'];
			$service_name = $list['service_name'];
			$L_SERVICE[$service_num] = $service_name;
		}
	}

	$name = $L_SERVICE[$id];

	return $name;
}


/**
 * コース
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array &$L_COURSE
 * @param mixed $id
 * @return string
 */
function read_couse(&$L_COURSE, $id) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$name = "";
	$sql  = "SELECT course_num, course_name FROM ".T_COURSE.
			" WHERE state='0';";
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$course_num = $list['course_num'];
			$course_name = $list['course_name'];
			$L_COURSE[$course_num] = $course_name;
		}
	}

	$name = $L_COURSE[$id];

	return $name;
}


/**
 * 判定名
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array &$L_HANTEIMEI
 * @param mixed $id
 * @return string
 */
function read_hanteimei(&$L_HANTEIMEI, $id) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$name = "";
	$sql  = "SELECT hantei_default_num, hantei_name FROM ".T_HANTEI_MS_DEFAULT.
			" WHERE mk_flg='0';";
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$hantei_default_num = $list['hantei_default_num'];
			$hantei_name = $list['hantei_name'];
			$L_HANTEIMEI[$hantei_default_num] = $hantei_name;
		}
	}

	$name = $L_HANTEIMEI[$id];

	return $name;
}
//	add ookawara 2013/09/04 end

// add start 2018/11/22 yoshizawa すらら英単語
/**
 * テスト種類名
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $test_type_num
 * @return string
 */
function read_test_type_name($test_type_num) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$test_type_name = "";
	$sql  = "SELECT test_type_num, test_type_name FROM ".T_MS_TEST_TYPE.
			" WHERE test_type_num = '".$test_type_num."' ".
			" AND mk_flg = '0';";
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$test_type_name = $list['test_type_name'];
		}
	}

	return $test_type_name;

}

/**
 * テスト種別1の名称取得
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $test_type_num
 * @param integer $test_category1_num
 * @return string
 */
function read_ms_test_category1_name($test_type_num,$test_category1_num) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$test_category1_name = "";
	$sql  = "SELECT test_category1_num, test_category1_name FROM ".T_MS_TEST_CATEGORY1.
			" WHERE test_type_num = '".$test_type_num."' ".
			" AND test_category1_num = '".$test_category1_num."' ".
			" AND mk_flg = '0';";
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$test_category1_name = $list['test_category1_name'];
		}
	}

	return $test_category1_name;

}

/**
 * テスト種別2の名称取得
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $test_type_num
 * @param integer $test_category1_num
 * @param integer $test_category2_num
 * @return string
 */
function read_ms_test_category2_name($test_type_num,$test_category1_num,$test_category2_num) {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$test_category2_name = "";
	$sql  = "SELECT test_category2_num, test_category2_name FROM ".T_MS_TEST_CATEGORY2.
			" WHERE test_type_num = '".$test_type_num."' ".
			" AND test_category1_num = '".$test_category1_num."' ".
			" AND test_category2_num = '".$test_category2_num."' ".
			" AND mk_flg = '0';";
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$test_category2_name = $list['test_category2_name'];
		}
	}

	return $test_category2_name;

}
// add end 2018/11/22 yoshizawa すらら英単語

// del start 2018/03/19 yoshizawa AWSプラクティスアップデート
// // add oda 2017/09/25 本番バッチのアップ容量を確認可能とする
// /**
//  * 検証バッチのディスクサイズチェック
//  * ※ディスク使用量は、開発環境の情報を表示する（検証バッチの情報を取得するとレスポンスが遅くなる）
//  *
//  * AC:[A]管理者 UC1:[M01]Core管理機能.
//  *
//  * @author Azet
//  * @param string $mode 処理モード
//  * @param int $course_num 定期テスト：コース番号／学力診断テスト：コース番号／数学検定：クラスID／判定テスト：判定タイプ
//  * @param int $stage_num 定期テスト：学年／学力診断テスト：学年／数学検定：省略／判定テスト：コース番号
//  * @param int $lesson_num 定期テスト：コアコード／学力診断テスト：テスト管理番号／数学検定：省略／判定テスト：判定デフォルト番号
//  * @param int $service_num 定期テスト：省略／学力診断テスト：省略／数学検定：省略／判定テスト：サービス管理番号
//  * @return int データサイズ（対象外またはエラーの時は0）
//  */
// function file_size_check_test($mode, $course_num = 0, $stage_num = 0, $lesson_num = 0, $service_num = 0 ) {
//
// 	// 分散DB接続オブジェクト
// 	$cdb = $GLOBALS['cdb'];
//
// 	// パラメータ初期クリア
// 	$disk_size = 0;
//
// 	if ($mode == "problem") {
//
// 		// 問題一覧を取得する
// 		$PROBLEM_LIST = array();
// 		$where = "";
// 		// コース $course_num -> $course_num
// 		if ($course_num > 0) {
// 			$where .= " AND mtdp.course_num='".$cdb->real_escape($course_num)."'";
// 		}
// 		// 学年 $stage_num -> $gknn
// 		if ($stage_num != "0" && $stage_num != "") {
// 			$where .= " AND mtdp.gknn='".$cdb->real_escape($stage_num)."'";
// 		}
// 		// コアコード $lesson_num -> $core_code
// 		if ($lesson_num != "0" && $lesson_num != "") {
// 			list($term_bnr_ccd,$term_kmk_ccd) = split("_", $lesson_num);
// 			$where .= " AND mtdp.term_bnr_ccd='".$cdb->real_escape($term_bnr_ccd)."'";
// 			$where .= " AND mtdp.term_kmk_ccd='".$cdb->real_escape($term_kmk_ccd)."'";
// 		}
// 		$sql  = "SELECT DISTINCT mtdp.problem_num FROM ms_test_default_problem mtdp".
// 				" WHERE mtdp.default_test_num='0'".
// 				$where.
// 				" AND mtdp.problem_table_type='2'".
// 				" AND mtdp.mk_flg='0'".
// 				" ORDER BY mtdp.problem_num;";
// 		if ($result = $cdb->query($sql)) {
// 			while ($list=$cdb->fetch_assoc($result)) {
// 				$PROBLEM_LIST[] = $list['problem_num'];
// 			}
// 		}
//
// //  echo " =============== sql1 ================= <br>";
// //  echo $sql."<br>";
// //echo " =============== problem_list ================= <br>";
// //pre($PROBLEM_LIST);
//
//
// 		// 画像情報
// 		//$disk_size1 = test_file_size_all($PROBLEM_LIST, KBAT_DIR.REMOTE_MATERIAL_TEST_IMG_DIR);
// 		$disk_size1 = test_file_size_all($PROBLEM_LIST, BASE_DIR.REMOTE_MATERIAL_TEST_IMG_DIR);
// 		// 音声情報
// 		//$disk_size2 = test_file_size_all($PROBLEM_LIST, KBAT_DIR.REMOTE_MATERIAL_TEST_VOICE_DIR);
// 		$disk_size2 = test_file_size_all($PROBLEM_LIST, BASE_DIR.REMOTE_MATERIAL_TEST_VOICE_DIR);
// 		// MBに変換（四捨五入）
// 		$disk_size = round(($disk_size1 + $disk_size2) / 1024, 1 );
// 	}
// 	elseif ($mode == "problem_trial") {
//
// 		// 問題一覧を取得する
// 		$PROBLEM_LIST = array();
// 		$where = "";
// 		$and = "";
// 		// テスト管理番号 $lesson_num -> $default_test_num
// 		if ($lesson_num != "0" && $lesson_num != "") {
// 			$where .= " AND mtdp.default_test_num ='".$cdb->real_escape($lesson_num)."'";
// 		} else {
// 			$where .= " AND mtdp.default_test_num>'0'";
// 		}
// 		// コース $course_num -> $course_num
// 		if ($course_num > 0) {
// 			$where .= " AND mtdp.course_num='".$cdb->real_escape($course_num)."'";
// 		}
// 		// 学年 $stage_num -> $gknn
// 		if ($stage_num != "0" && $stage_num != "") {
// 			$where .= " AND mtdp.gknn='".$cdb->real_escape($stage_num)."'";
// 		}
// 		$sql  = "SELECT DISTINCT mtdp.problem_num FROM ms_test_default_problem mtdp".
// 				" WHERE mtdp.problem_table_type='2'".
// 				$where.
// 				" AND mtdp.mk_flg='0'".
// 				" ORDER BY mtdp.problem_num;";
// 		if ($result = $cdb->query($sql)) {
// 			while ($list=$cdb->fetch_assoc($result)) {
// 				$PROBLEM_LIST[] = $list['problem_num'];
// 			}
// 		}
//
// //  echo " =============== sql2 ================= <br>";
// //  echo $sql."<br>";
// //echo " =============== problem_list ================= <br>";
// //pre($PROBLEM_LIST);
//
// 		// 画像情報
// 		//$disk_size1 = test_file_size_all($PROBLEM_LIST, KBAT_DIR.REMOTE_MATERIAL_TEST_IMG_DIR);
// 		$disk_size1 = test_file_size_all($PROBLEM_LIST, BASE_DIR.REMOTE_MATERIAL_TEST_IMG_DIR);
// 		// 音声情報
// 		//$disk_size2 = test_file_size_all($PROBLEM_LIST, KBAT_DIR.REMOTE_MATERIAL_TEST_VOICE_DIR);
// 		$disk_size2 = test_file_size_all($PROBLEM_LIST, BASE_DIR.REMOTE_MATERIAL_TEST_VOICE_DIR);
// 		// MBに変換（四捨五入）
// 		$disk_size = round(($disk_size1 + $disk_size2) / 1024, 1 );
// 	}
// 	elseif ($mode == "problem_math") {
//
// 		// 問題一覧を取得する
// 		$PROBLEM_LIST = array();
// 		$and = "";
//
// 		// $class_id -> $course_num
// 		if ($course_num) {
// 			$and .= " AND mtcp.class_id='".$course_num."' ";
// 		}
// 		$sql  = " SELECT DISTINCT mtp.problem_num FROM book_unit_test_problem butp ".
// 				 " INNER JOIN ms_test_problem mtp ON butp.problem_num = mtp.problem_num ".
// 				 " AND butp.default_test_num='0'".
// 				 " AND butp.problem_table_type='2'".
// 				 " AND butp.mk_flg='0'".
// 				 " INNER JOIN math_test_control_problem mtcp ON mtp.problem_num = mtcp.problem_num ".
// 				 " AND mtcp.problem_table_type='2'".
// 				 " AND mtcp.mk_flg='0'".
// 				 " WHERE 1 ".
// 				 $and.
// 				 " ORDER BY mtp.problem_num;";
//
// 		if ($result = $cdb->query($sql)) {
// 			while ($list=$cdb->fetch_assoc($result)) {
// 				$PROBLEM_LIST[] = $list['problem_num'];
// 			}
// 		}
//
//
// //  echo " =============== sql3 ================= <br>";
// //  echo $sql."<br>";
// // echo " =============== problem_list ================= <br>";
// // pre($PROBLEM_LIST);
//
// 		// 画像情報
// 		//$disk_size1 = test_file_size_all($PROBLEM_LIST, KBAT_DIR.REMOTE_MATERIAL_TEST_IMG_DIR);
// 		$disk_size1 = test_file_size_all($PROBLEM_LIST, BASE_DIR.REMOTE_MATERIAL_TEST_IMG_DIR);
// 		// 音声情報
// 		//$disk_size2 = test_file_size_all($PROBLEM_LIST, KBAT_DIR.REMOTE_MATERIAL_TEST_VOICE_DIR);
// 		$disk_size2 = test_file_size_all($PROBLEM_LIST, BASE_DIR.REMOTE_MATERIAL_TEST_VOICE_DIR);
// 		// MBに変換（四捨五入）
// 		$disk_size = round(($disk_size1 + $disk_size2) / 1024, 1 );
// 	}
// 	elseif ($mode == "problem_hantei_test") {
//
// 		// 問題一覧を取得する
// 		// サービス $service_num -> $service_num
// 		$where = " WHERE hmdp.service_num='".$cdb->real_escape($service_num)."'";
// 		// 判定タイプ $course_num -> $hantei_type
// 		if ($course_num > 0) {
// 			$where .= " AND hmdp.hantei_type='".$cdb->real_escape($course_num)."'";
// 		}
// 		// コース $course_num -> $stage_num
// 		if ($course_num == 2 && $stage_num > 0) {
// 			$where .= " AND hmdp.course_num='".$cdb->real_escape($stage_num)."'";
// 		}
// 		// 判定デフォルト番号 $lesson_num -> $hantei_default_num
// 		if ($lesson_num > 0) {
// 			$where .= " AND hmdp.hantei_default_num='".$cdb->real_escape($lesson_num)."'";
// 		}
//
// 		//      問題ファイル取得
// 		$PROBLEM_LIST = array();
// 			$sql  = "SELECT DISTINCT hmp.problem_num FROM hantei_ms_problem hmp".
// 					" LEFT JOIN hantei_ms_default_problem hmdp ON hmdp.problem_num=hmp.problem_num".
// 						" AND hmdp.problem_table_type='2'".
// 					$where.
// 					" AND hmp.mk_flg='0'".
// 					" ORDER BY hmp.problem_num;";
// 		if ($result = $cdb->query($sql)) {
// 			while ($list=$cdb->fetch_assoc($result)) {
// 				$PROBLEM_LIST[] = $list['problem_num'];
// 			}
// 		}
//
// //  echo " =============== sql4 ================= <br>";
// //  echo $sql."<br>";
// // echo " =============== problem_list ================= <br>";
// // pre($PROBLEM_LIST);
//
// 		// 画像情報
// 		//$disk_size1 = test_file_size_all($PROBLEM_LIST, KBAT_DIR.REMOTE_MATERIAL_HANTEI_IMG_DIR);
// 		$disk_size1 = test_file_size_all($PROBLEM_LIST, BASE_DIR.REMOTE_MATERIAL_HANTEI_IMG_DIR);
// 		// 音声情報
// 		//$disk_size2 = test_file_size_all($PROBLEM_LIST, KBAT_DIR.REMOTE_MATERIAL_HANTEI_VOICE_DIR);
// 		$disk_size2 = test_file_size_all($PROBLEM_LIST, BASE_DIR.REMOTE_MATERIAL_HANTEI_VOICE_DIR);
// 		// MBに変換（四捨五入）
// 		$disk_size = round(($disk_size1 + $disk_size2) / 1024, 1 );
// 	}
//
// 	return $disk_size;
// }
//
// // add oda 2017/09/26 本番バッチのアップ容量を確認可能とする
// /**
//  * 対象のテスト問題に対するファイルの情報を取得する
//  *
//  * AC:[A]管理者 UC1:[M01]Core管理機能.
//  *
//  * @author Azet
//  * @param array $PROBLEM_LIST 問題一覧
//  * @param string $dir チェック対象フォルダパス
//  * @return int データサイズ（対象外またはエラーの時は0）
//  */
// function test_file_size_all($PROBLEM_LIST, $dir) {
//
// 	if (!$PROBLEM_LIST) { return; }
//
// 	$disk_size_all = 0;
//
// 	$data_count = 0;
// 	$set_file_name = "";
//
// 	foreach ($PROBLEM_LIST AS $problem_num) {
// 		$dir_num = (floor($problem_num / 100) * 100) + 1;
// 		$amari = $problem_num % 100;
// 		if ($amari < 1) {
// 			$dir_num -= 100;
// 		}
// 		$dir_num = sprintf("%07d",$dir_num);
// 		$dir_name = $dir.$dir_num."/";
// 		$dir_name_ = preg_replace("/\//", "\/", $dir_name);
// 		$set_file_name .= $dir_name.$problem_num."_*.* ";
// 		$data_count++;
//
// 		// ディスクサイズは２０件づつ取得する
// 		if ($data_count >= 20) {
// 			$output1 = array();
// 			exec("find ".$set_file_name." | xargs -n 20 du -s ", $output1, $return_var);
//
// 			$disk_size = conv_return_info($output1);
// 			$disk_size_all += $disk_size;
//
// //  if (count($output1) >0) {
// //  echo "=============output1================<br>";
// //  pre($output1);
// //  }
// // echo '■ファイル名：'.$set_file_name."<br>";
// // echo '■ファイルサイズ：'.$disk_size."<br>";
// 			$data_count = 0;
// 			$set_file_name = "";
// 		}
//
// 	}
//
// 	// 対象ファイルの残りのディスク容量を取得する
// 	if ($data_count > 0) {
// 		$output1 = array();
// 		exec("find ".$set_file_name." | xargs -n 20 du -s ", $output1, $return_var);
//
// 		$disk_size = conv_return_info($output1);
//
// //  if (count($output1) >0) {
// //  echo "=============output1================<br>";
// //  pre($output1);
// //  }
// // echo '■ファイル名：'.$set_file_name."<br>";
// // echo '■ファイルサイズ：'.$disk_size."<br>";
// 		$data_count = 0;
// 		$set_file_name = "";
// 		$disk_size_all += $disk_size;
// 	}
//
//
// 	return $disk_size_all;
// }
//
// // add oda 2017/09/25 本番バッチのアップ容量を確認可能とする
// /**
//  * 検証バッチのディスクサイズチェック
//  *
//  * AC:[A]管理者 UC1:[M01]Core管理機能.
//  *
//  * @author Azet
//  * @param array $output シェルコマンドの返却値
//  * @return int データサイズ（対象外またはエラーの時は0）
//  */
// function conv_return_info($output) {
//
// 	// パラメータ初期クリア
// 	$disk_size = 0;
//
// 	if (is_array($output)) {
// 		foreach($output as $key => $val) {
// 			// 取得した情報はタブ区切りの為、分割する
// 			$size_list = explode("\t", $val);
// 			// 分割した情報の先頭がディスク使用サイス（KB）
// 			if (is_array($size_list)) {
// 				// 取得できない場合は"du: cannot access `/data/data/data': そのようなファイルやディレクトリはありません"を取得
// 				// また、指定ファイルが存在しない場合、カレントディレクトリを参照するので、第２パラメータが"."も除外する
// 				if ($size_list[0] != "du:" && $size_list[1] != ".") {
// 					$disk_size += $size_list[0];
// 				}
// 			}
// 		}
// 	}
//
// 	return $disk_size;
// }
// del end 2018/03/19 yoshizawa AWSプラクティスアップデート
?>
