<?PHP
/**
 * unit comp
 *
 * ログ関係	エリアなど削除
 * 			テーブル名　T_CHECK_STUDY_RECODEへ変更
 * 			$_SESSION['manager']['manager_num']を$_SESSION['managerid']['manager_num']変更
 * 問題正解率変更コメント化
 * 次のブロックへ行かないようにする
 * T_FINISH_UNIT 記録削除
 * 終了した場合ログ削除
 * $block_num設定
 * 復習学習後次のユニットも新規かチェックする。削除
 *
 * @@@ 新ＤＢ対応
 * 	$_SESSION['manager']['manager_num']を$_SESSION["myid"]["id"]に変更
 * 	カラム名の変更
 *
 * @author Azet
 */


/**
 * 判断して、HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @return string HTML
 */
function start() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$student_num = $_SESSION["myid"]["id"];
	$course_num = $_SESSION['course']['course_num'];
	$unit_num = $_SESSION['course']['unit_num'];
	$class_m = $_SESSION['course']['class_m'];
	$block_num = $_SESSION['course']['block_num'];

//	---------------------------------------------------------------------------
//	Write Completion signal
//	---------------------------------------------------------------------------

	if ($_SESSION['course']['block_num']) {

		$any_sql  = "SELECT list_num FROM " . T_BLOCK .
					" WHERE block_num='".$_SESSION['course']['block_num']."'";

		$sql  = "SELECT block_num FROM " . T_BLOCK .
				" WHERE course_num='".$_SESSION['course']['course_num']."'".
				" AND unit_num='".$_SESSION['course']['unit_num']."'" .
				" AND display='1' AND list_num > ANY($any_sql) ORDER BY list_num LIMIT 1;";
		if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
		}

		//if ($_SESSION["callby"]=="admin"){
			//	次のブロックに行かないようにする処理
			unset($list);
		//}

		if ($list[block_num]) {

			// 次のブロックへ行く前にログを削除
			$where = " WHERE student_id='".$_SESSION["myid"]["id"]."'".
					 " AND block_num='".$block_num."'";

			$ERROR = $cdb->delete(T_CHECK_STUDY_RECODE,$where);

			$_SESSION['course']['block_num'] = $list[block_num];
			unset($_SESSION['course']['display_problem_num']);
			unset($_SESSION['course']['unit_comp']);
			unset($_SESSION['record']['problem_num']);
			header("Location: ./check_study.php");
			exit;
		} else {
			if ($_SESSION['course']['review'] >= 2) {
				if ($_SESSION['course']['block_type'] == 1) {
	                if($_SESSION['record']['giveup'] == 1) {
						$class_m = 202010810;
	                } else {
						$class_m = 202010800;
	                }
				} elseif ($_SESSION['course']['block_type'] == 2) {
					//	復習ドリルでギブアップチェック
					review_giveup_check();

	                if($_SESSION['record']['giveup'] == 1) {
						$class_m = 202020810;
	                } else {
						$class_m = 202020800;
	                }
				} elseif ($_SESSION['course']['block_type'] == 3) {
					//	復習ドリルでギブアップチェック
					review_giveup_check();

	                if($_SESSION['record']['giveup'] == 1) {
						$class_m = 202030810;
	                } else {
						$class_m = 202030800;
	                }
				} else {
					if($_SESSION['record']['giveup'] == 1) {
						$class_m = 202000810;
	                } else {
						$class_m = 202000800;
	                }
				}
			} else {
				if ($_SESSION['course']['block_type'] == 1) {
	                if($_SESSION['record']['giveup'] == 1) {
						$class_m = 102010810;
	                } else {
						$class_m = 102010800;
	                }
				} elseif ($_SESSION['course']['block_type'] == 2) {
					//	復習ドリルでギブアップチェック
					review_giveup_check();

	                if($_SESSION['record']['giveup'] == 1) {
						$class_m = 102020810;
	                } else {
						$class_m = 102020800;
	                }
				} elseif ($_SESSION['course']['block_type'] == 3) {
					//	復習ドリルでギブアップチェック
					review_giveup_check();

	                if($_SESSION['record']['giveup'] == 1) {
						$class_m = 102030810;
	                } else {
						$class_m = 102030800;
	                }
				} else {
					if($_SESSION['record']['giveup'] == 1) {
						$class_m = 102000810;
	                } else {
						$class_m = 102000800;
	                }
				}
			}
			unset($INSERT_DATA);
			study_record_etc("$class_m");
			unset($_SESSION['course']['block_num']);
		}
	}
	//	ユニット終了
	//	ユニット内ギブアップチェック
	unset($giveup_block);
	unset($GIVEUP_BLOCK);
	if ($_SESSION['course']['review'] >= 2) {
		$class_m1 = 202010810;
		$class_m2 = 202020810;
		$class_m3 = 202030810;
	} else {
		$class_m1 = 102010810;
		$class_m2 = 102020810;
		$class_m3 = 102030810;
	}
	$sql  = "SELECT block_num FROM ".T_CHECK_STUDY_RECODE.
			" WHERE student_id='".$_SESSION["myid"]["id"]."'".
			" AND (".
				"class_m='".$class_m1."'".
				" || class_m='".$class_m2."'".
				" ||class_m='".$class_m3."'".
			")".
			" AND course_num='".$_SESSION['course']['course_num']."'".
			" AND unit_num='".$_SESSION['course']['unit_num']."'".
//			" AND DATE(regist_time)=CURDATE()".
			" AND regist_time>'".$_SESSION['course']['finish_time']."'".
			" ORDER BY regist_time;";
	if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$block_num_ = $list['block_num'];
			if ($block_num_ < 1 || $GIVEUP_BLOCK[$block_num_]) { continue; }
			if ($giveup_block) { $giveup_block .= ","; }
			$giveup_block .= $block_num_;
			$GIVEUP_BLOCK[$block_num_] = 1;
		}
	}
	if ($giveup_block) { $_SESSION['record']['giveup'] = 1; }

	//	ユニット終了記録
	if ($_SESSION['course']['review'] >= 2) {
		if($_SESSION['record']['giveup'] == 1) {
			$class_m = 202000910;
		} else {
			$class_m = 202000900;
		}
	} else {
		if($_SESSION['record']['giveup'] == 1) {
			$class_m = 102000910;
		} else {
			$class_m = 102000900;
		}
	}

	unset($INSERT_DATA);
	study_record_etc("$class_m");
	unset($_SESSION['course']['block_num']);

	unset($INSERT_DATA);
	if (ereg("^2",$class_m)) { $INSERT_DATA[record_type] = 2; }
	$INSERT_DATA[student_id] = $_SESSION["myid"]["id"];
	$INSERT_DATA[course_num] = $_SESSION['course']['course_num'];
	$INSERT_DATA[unit_num] = $_SESSION['course']['unit_num'];
	$INSERT_DATA[regist_time] = "now()";
	//	ユニット終了記録
	if ($_SESSION['record']['indert_check'] != 1) {

		$ERROR = $cdb->insert(T_FINISH_UNIT,$INSERT_DATA);
		if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::query-&gt;insert::T_FINISH_UNIT"; }
		$_SESSION['record']['indert_check'] = 1;
	}
	unset($_SESSION['record']['giveup']);

//	---------------------------------------------------------------------------
//	Next Unit-Data extraction
//	---------------------------------------------------------------------------
	//	終了・スキップユニット取得
	$sql  = "SELECT finish_unit.unit_num,finish_unit.skip,unit.list_num FROM ".
			T_FINISH_UNIT." finish_unit,".T_UNIT." unit".
			" WHERE finish_unit.student_id='".$student_num."'".
			" AND finish_unit.course_num='$course_num'".
			" AND finish_unit.unit_num=unit.unit_num".
			" AND finish_unit.state!='1';";
	if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			if ($list[skip]) {
				$SKIP[$list[unit_num]] = $list[unit_num];
			} else {
				$CLEAR[$list[unit_num]] = $list[list_num];
			}
		}
	}

	//	学習中Lesson情報取得
	$sql  = "SELECT stage_num, lesson_num, unit_key FROM ".T_UNIT.
			" WHERE course_num='".$_SESSION[course][course_num]."'".
			" AND unit_num='".$_SESSION[course][unit_num]."'".
			" LIMIT 1";
	if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$stage_num = $list['stage_num'];
		$lesson_num = $list['lesson_num'];
		$unit_key = $list['unit_key'];
	}
	//	Lesson内の次のunit_num取得
	$next_num = check_next_unit_num($lesson_num,$CLEAR,$SKIP);
	if (!$next_num && eregi("s0",$unit_key)) {
		$sql  = "SELECT lesson_num FROM ".T_LESSON.
				" WHERE stage_num='$stage_num'".
				" AND lesson_key!=''".
				" AND display='1'".
				" AND state!='1'".
				" ORDER BY list_num;";
		if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
		if ($result = $cdb->query($sql)) {
			while($list = $cdb->fetch_assoc($result)) {
				$lesson_num_ = $list['lesson_num'];
				$next_num = check_next_unit_num($lesson_num_,$CLEAR,$SKIP);
				if ($next_num) { break; }
			}
		}
	}
/*
利用置換キー
<!--MORESCRIPT-->
<!--COMMENT-->
<!--BUTTON-->

*/

	$morescript = "";

	$comment = "";
	$comment1 = "次の授業へ進みますか、";
	$comment2 = "授業一覧に戻りますか？";
	$comment3 = "それとも他の教科を見ますか？";
	if (!$next_num) {
		$comment1 = $comment2;
		$comment2 = "";
	}
	$comment = <<<EOT
$comment1<br>
$comment2<br>
$comment3

EOT;

	if ($_SESSION['course']['review'] != 1) {
		$hidden = "";
	} else {
		$hidden = "<input type=\"hidden\" name=\"review\" value=\"1\">\n";
	}

	$button = "";
	$button1 = "<form action=\"/student/lesson.php\" method=\"POST\" style=\"float:left;\">\n";
	$button1 .= "<input type=\"hidden\" name=\"course_num\" value=\"$course_num\">\n";
	$button1 .= "<input type=\"hidden\" name=\"unit_num\" value=\"$next_num\">\n";
//	$button1 .= $hidden;
	$image1 = "/student/images/button/".$course_num."_11_2.gif";
	$image2 = "/student/images/button/".$course_num."_11_1.gif";
	if (file_exists("../".$image1)) {
		//upd start yamaguchi 2018/08/03 Mac safariでマウスオーバー時ボタンが消えて進めなくなる不具合修正
		//$button1 .= "<img src=\"$image1\" name=\"button1\" alt=\"次のユニットへ進む\" onmousedown=\"HpbImgSwap('button1', '$image2');\" onmouseup=\"HpbImgSwap('button1', '$image1'); submit();\" onmouseout=\"HpbImgSwap('button1', '$image1');\">\n";
		if (is_chrome_safari()) {
			$button1 .= "<img src=\"$image1\" name=\"button1\" alt=\"次のユニットへ進む\" onmousedown=\"HpbImgSwap('button1', '$image2');\" onmouseup=\"HpbImgSwap('button1', '$image1'); submit();\" \">\n";
		//PC
		}else{
			$button1 .= "<img src=\"$image1\" name=\"button1\" alt=\"次のユニットへ進む\" onmousedown=\"HpbImgSwap('button1', '$image2');\" onmouseup=\"HpbImgSwap('button1', '$image1'); submit();\" onmouseout=\"HpbImgSwap('button1', '$image1');\">\n";
		}//upd end yamaguchi 2018/08/03
	} else {
		$button1 .= "<input type=\"submit\" value=\"次のユニットへ進む\" class=\"form_font_size\">\n";
	}
	$button1 .= "</form>\n";

	$button2 = "<form action=\"/student/chart.php\" method=\"POST\" style=\"float:left;\">\n";
	$button2 .= "<input type=\"hidden\" name=\"course_num\" value=\"$course_num\">\n";
//	$button2 .= $hidden;
	$image1 = "/student/images/button/".$course_num."_12_2.gif";
	$image2 = "/student/images/button/".$course_num."_12_1.gif";
	if (file_exists("../".$image1)) {
		//upd start yamaguchi 2018/08/03 Mac safariでマウスオーバー時ボタンが消えて進めなくなる不具合修正
		//$button2 .= "<img src=\"$image1\" name=\"button2\" alt=\"ユニット一覧へ戻る\" onmousedown=\"HpbImgSwap('button2', '$image2');\" onmouseup=\"HpbImgSwap('button2', '$image1'); submit();\" onmouseout=\"HpbImgSwap('button2', '$image1');\">\n";
		if (is_chrome_safari()) {
			$button2 .= "<img src=\"$image1\" name=\"button2\" alt=\"ユニット一覧へ戻る\" onmousedown=\"HpbImgSwap('button2', '$image2');\" onmouseup=\"HpbImgSwap('button2', '$image1'); submit();\" \">\n";
		//PC
		}else{
			$button2 .= "<img src=\"$image1\" name=\"button2\" alt=\"ユニット一覧へ戻る\" onmousedown=\"HpbImgSwap('button2', '$image2');\" onmouseup=\"HpbImgSwap('button2', '$image1'); submit();\" onmouseout=\"HpbImgSwap('button2', '$image1');\">\n";
		}//upd end yamaguchi 2018/08/03
	} else {
		$button2 .= "<input type=\"submit\" value=\"ユニット一覧へ戻る\" class=\"form_font_size\">\n";
	}
	$button2 .= "</form>\n";

	$button3 = "<form action=\"/student/top.php\" method=\"POST\" style=\"float:left;\">\n";
	$image1 = "/student/images/button/".$course_num."_13_2.gif";
	$image2 = "/student/images/button/".$course_num."_13_1.gif";
	if (file_exists("../".$image1)) {
		//upd start yamaguchi 2018/08/03 Mac safariでマウスオーバー時ボタンが消えて進めなくなる不具合修正
		//$button3 .= "<img src=\"$image1\" name=\"button3\" alt=\"他の教科を受講する\" onmousedown=\"HpbImgSwap('button3', '$image2');\" onmouseup=\"HpbImgSwap('button3', '$image1'); submit();\" onmouseout=\"HpbImgSwap('button3', '$image1');\">\n";
		if (is_chrome_safari()) {
			$button3 .= "<img src=\"$image1\" name=\"button3\" alt=\"他の教科を受講する\" onmousedown=\"HpbImgSwap('button3', '$image2');\" onmouseup=\"HpbImgSwap('button3', '$image1'); submit();\"
			\">\n";
		//PC
		}else{
			$button3 .= "<img src=\"$image1\" name=\"button3\" alt=\"他の教科を受講する\" onmousedown=\"HpbImgSwap('button3', '$image2');\" onmouseup=\"HpbImgSwap('button3', '$image1'); submit();\" onmouseout=\"HpbImgSwap('button3', '$image1');\">\n";
		}//upd end yamaguchi 2018/08/03
	} else {
		$button3 .= "<input type=\"submit\" value=\"他の教科を受講する\" class=\"form_font_size\">\n";
	}
	$button3 .= "</form>\n";

	if (!$next_num) {
		$button1 = $button2;
		$button2 = "";
	}
	$button = <<<EOT
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td align="left">$button1</td>
    <td align="center">$button2</td>
    <td align="right">$button3</td>
  </tr>
</table>

EOT;

	//	閉じるボタン追加
	$comment = <<<EOT
<form>
終了<br>
<br>
<input type="button" value="閉じる" OnClick="window.close();">
</form>

EOT;

	$morescript = "<link rel=\"stylesheet\" href=\"../material/template/".$course_num."/unit_comp.css\" type=\"text/css\">\n";	// 2008/11/28_2 add

	$make_html = new read_html();
	$make_html->set_dir(STUDENT_TEMP_DIR.$course_num."/");
	$make_html->set_file(STUDENT_TEMP_UNIT_COMP);
	$INPUTS[MORESCRIPT] = array('result'=>'plane','value'=>$morescript);
	$INPUTS[COMMENT] = array('result'=>'plane','value'=>$comment);
//	$INPUTS[BUTTON] = array('result'=>'plane','value'=>$button);
	$make_html->set_rep_cmd($INPUTS);
	$html = $make_html->replace();

	// 画面サイズ調整
	$html = str_replace("<body>","<body onLoad=\"check_size(0,99);\">",$html);

	//	ログ削除　追加
	if ($block_num) {
		$where = " WHERE student_id='".$_SESSION["myid"]["id"]."'".
				 " AND block_num='".$block_num."'";

		$ERROR = $cdb->delete(T_CHECK_STUDY_RECODE,$where);
	}
	return $html;
}


/**
 *
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @param integer $lesson_num
 * @param array $CLEAR
 * @param array $SKIP
 * @return integer
 */
function check_next_unit_num($lesson_num,$CLEAR,$SKIP) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql  = "SELECT unit_num FROM ".T_UNIT.
			" WHERE course_num='".$_SESSION['course']['course_num']."'".
			" AND lesson_num='$lesson_num'".
			" AND unit_key!=''".
			" AND display='1'".
			" AND state!='1'".
			" ORDER BY list_num;";
	if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
	if ($result = $cdb->query($sql)) {
		$flag = 0;
		while ($list = $cdb->fetch_assoc($result)) {
			$unit_num = $list['unit_num'];
			if ($unit_num == $_SESSION['course']['unit_num']) {
				$flag = 1;
				continue;
			}
			if ($flag != 1 || $CLEAR[$unit_num] || $SKIP[$unit_num]) { continue; }
			$next_num = $unit_num;
			if ($next_num) { break; }
		}
	}

	return $next_num;
}


/**
 * 復習ドリルギブアップチェック
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 */
function review_giveup_check() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];


	if ($_SESSION['record']['giveup'] == 1){
		return;
	}

	if ($_SESSION['course']['review'] >= 2) {
		$class_m2 = 202020510;
		$class_m3 = 202030510;
	} else {
		$class_m2 = 102020510;
		$class_m3 = 102030510;
	}
	$sql  = "SELECT COUNT(*) AS give_up_count FROM ".T_STUDY_RECODE.
			" WHERE student_id='".$_SESSION['studentid']['student_id']."'".
			" AND (".
				"class_m='".$class_m2."'".
				" ||class_m='".$class_m3."'".
			")".
			" AND course_num='".$_SESSION['course']['course_num']."'".
			" AND unit_num='".$_SESSION['course']['unit_num']."'".
//			" AND DATE(regist_time)=CURDATE()".
			" AND regist_time>'".$_SESSION['course']['finish_time']."'".
			" ORDER BY regist_time;";

	if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$giveup_count = $list['give_up_count'];
	}
	if ($giveup_count) { $_SESSION['record']['giveup'] = 1; }
}
?>