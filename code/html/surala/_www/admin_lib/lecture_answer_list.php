<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * e-learning system admin レクチャー解答一覧画面作成

 * 履歴
 * 2016/10/07 初期設定
 *
 * @author Azet
 */

/**
 *
 * レクチャー解答一覧画面作成
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * HTMLを作成する機能
 * @author Azet
 * @return string HTML
 */

function start() {

	// 分散DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$tpl = "lecture_answer_list.htm";	//admin用

	$main = "";
	$id = $_SESSION['myid']['id'];
	$course_num = $_POST['course_num'];
	$stage_num = $_POST['stage_num'];
	$lesson_num = $_POST['lesson_num'];
	$unit_num = $_POST['unit_num'];

	// タイトル情報を取得
	$sql  = "SELECT ".
			" co.course_num, ".
			" co.course_name, ".
			" st.stage_num, ".
			" st.stage_name, ".
			" le.lesson_num, ".
			" le.lesson_name, ".
			" un.unit_num, ".
			" un.unit_name ".
			" FROM ".T_UNIT." un ".
			" INNER JOIN ".T_LESSON." le ON un.lesson_num = le.lesson_num AND le.state!='1' ".
			" INNER JOIN ".T_STAGE." st ON le.stage_num = st.stage_num AND st.state!='1' ".
			" INNER JOIN ".T_COURSE." co ON st.course_num = co.course_num AND co.state!='1' ".
			" WHERE un.state!='1' ".
			" AND co.course_num = '".$course_num."' ".
			" AND st.stage_num = '".$stage_num."' ".
			" AND le.lesson_num = '".$lesson_num."' ".
			" AND un.unit_num = '".$unit_num."' ".
			" GROUP BY un.unit_num;";
	$title = "";
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$title = $list['course_name']." ".$list['stage_name']." ".$list['lesson_name']." ".$list['unit_name'];
		}
	}

	if($unit_num > 0){
		// 解答ログを取得
		$sql = "SELECT * FROM ".T_STUDY_RECODE.
			" WHERE student_id='0' ".
			" AND school_id = '".$id."' ".
			" AND class_m = '101010020' ".
			" AND course_num = '".$course_num."' ".
			" AND unit_num = '".$unit_num."' ".
			" AND review = '0' ".
			" AND answer2 = 'lecture_answer' ".
			" ORDER BY regist_time;";

		$result = $cdb->query($sql);
		if($list = $cdb->fetch_assoc($result)){
			$count = count($list); 	// レコードの存在をチェック
		}
		$cdb->data_seek($result, 0);

		if ($count > 0) {

			// 書写ログを取得
			$sql2 = "SELECT *".
				" FROM ".T_SYOSYA_LOG." ".
				" WHERE log_type='1'".
				" AND study_log_type = 'lecture_answer'".
				" AND record_type = '0'".
				" AND student_id = '0' ".
				" AND school_id = '".$id."'".
				" AND course_num = '".$course_num."'".
				" AND stage_num = '".$stage_num."'".
				" AND lesson_num = '".$lesson_num."'".
				" AND unit_num = '".$unit_num."'".
				" AND review = '0' ;";

			$L_SYOSYA_LOG = array();
			if($result2 = $cdb->query($sql2)) {
				while ($list2 = $cdb->fetch_assoc($result2)) {
					$syoysa_problem_num = unserialize($list2["flash_problem_num"]);
					$L_SYOSYA_LOG[$syoysa_problem_num][$list2['answer_count_syosya']][$list2['flash_count']]['flash_answer'] = unserialize($list2['stroke_info']);
				}
			}

			$main .= "<p style=\"font-size:13px;\">※ 回数 ： 問題を「もう一回チャレンジ」で解答した際にカウントアップされます。<br>";	// add hasegawa 2016/10/06 小学生低学年版2次開発
			$main .= "※ 同じ問題の解答回数 ： 問題を再度表示し、解答した際にカウントアップされます。</p>";					// add hasegawa 2016/10/06 小学生低学年版2次開発

			$main .= "<table class=\"course_form\">";
			$main .= "<tr class=\"course_form_menu\">";
			$main .= "	<th>ページ</th>";
			$main .= "	<th>問題番号</th>";
			$main .= "	<th>回数</th>";
			$main .= "	<th>同じ問題の解答回数</th>";
			$main .= "	<th>解答</th>";
			$main .= "	<th>判定</th>";
			$main .= "	<th></th>";
			$main .= "</tr>";
			while ($list = $cdb->fetch_assoc($result)) {
				$DATA_LIST = array();
				$DATA_LIST = unserialize($list["answer"]);

				if(is_array($DATA_LIST)) {
					$flash_problem_num = $DATA_LIST['flash_problem_num'];		// 問題番号
					$flash_answer = $DATA_LIST['flash_answer'];			// 解答内容
				} else {
					$flash_problem_num = $DATA_LIST;
					$flash_answer ="";
				}

				$flash_success = $list['success'];			// 合否内容
				$answer_count = $list['display_problem_num'];		// 同一問題を複数回解答したときの解答回数
				$flash_count = $list['again'];				// 解答回数
				$type = $list['answer2'];				// ページ記録識別

				$PROBLEM_INFO = array();
				$PROBLEM_INFO = explode("-",$flash_problem_num); 	// 「ページ」-「問題番号」
				$page = $PROBLEM_INFO[0];
				$problem_num = $PROBLEM_INFO[1];

				$success = "×";
				if($list['success'] > 0){ $success = "〇"; }

				$flash_answer_input = '';
				if($L_SYOSYA_LOG[$flash_problem_num][$answer_count][$flash_count]) {
					// 書写問題の場合
					foreach($L_SYOSYA_LOG[$flash_problem_num][$answer_count][$flash_count] as $key => $val) {
						if(is_array($val)) {
							$val = json_encode($val);
						}
						$flash_answer_input .= "	<input type=\"hidden\" name=\"".$key."\" value=\"".urlencode($val)."\">";
					}
				// update start 2017/02/10 yoshizawa 低学年2次対応
				// } else {
				//
				// 	// 選択問題の場合
				// 	$flash_answer_input .= "	<input type=\"hidden\" name=\"flash_answer\" value=\"".$flash_answer."\">";
				// }
				//
				} else {
						if(is_array($flash_answer)) {
							$flash_answer_value = json_encode($flash_answer);
							$flash_answer_value = urlencode($flash_answer_value);
						} else {
							//$flash_answer_value = $flash_answer;										// del oda 2018/01/12
							$flash_answer_value = urldecode($flash_answer);								// add oda 2018/01/12 urlデコードを行う
							$flash_answer_value = str_replace('\\', '\\\\', $flash_answer_value);		// add oda 2018/01/12 バックスラッシュはエスケープする
							$flash_answer_value = str_replace('\'', '\\\'', $flash_answer_value);		// add oda 2018/01/12 シングルクォートはエスケープする
							$flash_answer_value = urlencode($flash_answer_value);						// add oda 2018/01/12 urlエンコードを行う
						}
					// 選択問題の場合
					$flash_answer_input .= "	<input type=\"hidden\" name=\"flash_answer\" value=\"".$flash_answer_value."\">";
				}
				// update end 2017/02/10 yoshizawa 低学年2次対応

				$main .= "<tr class=\"course_form_cell\">";
				$main .= "	<td>".$page."</td>";			// ページ
				$main .= "	<td>".$problem_num."</td>";		// 問題番号
				$main .= "	<td>".$flash_count."</td>";		// 回数
				$main .= "	<td>".$answer_count."</td>";		// 同一問題の解答回数
				if($L_SYOSYA_LOG[$flash_problem_num][$answer_count][$flash_count]) {	// upd hasegawa 2016/12/16 小学生低学年版2次開発 分岐処理追加
					$flash_answer_msg = "書写解答";
				} elseif(is_array($flash_answer)) { // add 2017/02/10 yoshizawa 低学年2次対応
					$flash_answer_msg = implode(",",$flash_answer);
				} elseif($flash_answer == "") {
					$flash_answer_msg = "---";
				} else {
					//$flash_answer_msg = $flash_answer;						// del oda 2018/01/12
					$flash_answer_msg = urldecode($flash_answer);				// add oda 2018/01/12 urlエンコードはDB格納時に行う
				}
				$flash_answer_msg = replace_encode($flash_answer_msg);	// add 2019/10/17 yoshizawa 課題要望No796

				$main .= "	<td>".$flash_answer_msg."</td>";	// 解答
				$main .= "	<td>".$success."</td>";			// 判定
				$main .= "	<td>";					// 解答を見るボタン
				$main .= "	<form action=\"/admin/check_lecture_lg.php\" method=\"POST\">";
				$main .= "	<input type=\"hidden\" name=\"mode\" value=\"check_answer\">";
				// 一覧に戻る際に必要なパラメーター
				$main .= "	<input type=\"hidden\" name=\"course_num\" value=\"".$course_num."\">";
				$main .= "	<input type=\"hidden\" name=\"stage_num\" value=\"".$stage_num."\">";
				$main .= "	<input type=\"hidden\" name=\"lesson_num\" value=\"".$lesson_num."\">";
				$main .= "	<input type=\"hidden\" name=\"unit_num\" value=\"".$unit_num."\">";
				// 解答再現に必要なパラメーター
				$main .= "	<input type=\"hidden\" name=\"flash_problem_num\" value=\"".$flash_problem_num."\">";
				$main .= $flash_answer_input;
				$main .= "	<input type=\"hidden\" name=\"flash_success\" value=\"".$flash_success."\">";
				$main .= "	<input type=\"hidden\" name=\"flash_count\" value=\"".$flash_count."\">";
				$main .= "	<input type=\"hidden\" name=\"type\" value=\"".$type."\">";
				$main .= "	<input type=\"submit\" value=\"解答を見る\">";
				$main .= "	</form>";
				$main .= "	</td>";
				$main .= "</tr>";
			}
			$main .= "</table>";

		} else {
			$main = "解答データが存在しません。";
		}

	} else {
		$main = "ユニット番号が確認できませんでした。";
	}
	$main .= "<form><input type=\"button\" value=\"閉じる\" OnClick=\"parent.window.close();\"></form>\n";

	$INPUTS['TITLE'] = array('result'=>'plane','value'=>$title);
	$INPUTS['MAIN'] = array('result'=>'plane','value'=>$main);

	//	画面生成
	$make_html = new read_html();
	$make_html->set_dir(ADMIN_TEMP_DIR);
	$make_html->set_file($tpl);
	$make_html->set_rep_cmd($INPUTS);

	$html = "";
	$html = $make_html->replace();

	return $html;
}

?>
