<?PHP
// 2017/07/21 yoshizawa 未使用のファイルです。

/**
 * ベンチャー・リンク　すらら
 *
 * 診断ドリル結果表示
 *
 * ログ関係	エリアなど削除
 * 			テーブル名　T_CHECK_STUDY_RECODEへ変更
 * 			$_SESSION['studentid']['student_num']を$_SESSION['managerid']['manager_num']変更
 * 			study_record_numをcheck_study_record_num
 *
 * @@@ 新ＤＢ対応
 * 	$_SESSION['manager']['manager_num']を$_SESSION["myid"]["id"]に変更
 * 	カラム名の変更
 *
 * @author Azet
 */


/**
 * HTMLコンテンツを作成する機能
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
	$block_num = $_SESSION['course']['block_num'];

	if (!$_SESSION['course']['block_type']) {
		$sql  = "SELECT block_type FROM ".T_BLOCK.
				" WHERE block_num='{$block_num}' LIMIT 1;";
		if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			define("base_block_type",$list['block_type']);
		}
	} else {
		define("base_block_type",$_SESSION['course']['block_type']);
	}

	//	学習回数チェック
	//	$_SESSION['course']['review']には、学習回数が登録される。
	if ($_SESSION['course']['review'] == "" || $_SESSION['course']['finish_time'] == "") {
		unset($_SESSION['course']['review']);
		unset($_SESSION['course']['finish_time']);
		$sql  = "SELECT regist_time FROM ".T_FINISH_UNIT.
				" WHERE student_id='".$student_num."'".
				" AND course_num='".$course_num."'".
				" AND unit_num='".$unit_num."'".
				" AND skip!='1'".
				" AND state!='1'".
				" ORDER BY regist_time;";
		if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
		if ($result = $cdb->query($sql)) {
			while ($list = $cdb->fetch_assoc($result)) {
				$regist_time = $list['regist_time'];
				$_SESSION['course']['review']++;
				$_SESSION['course']['finish_time'] = $regist_time;
			}
		}
		$_SESSION['course']['review']++;
		//	ユニット最終クリアー時間がない場合、空の値を入れる。
		if (!$_SESSION['course']['finish_time']) {
			$_SESSION['course']['finish_time'] = "0";
		}
	}

	if ($_SESSION['course']['review'] >= 2) {
		if (base_block_type == 2) {
			$class_m = 202020200;
		} elseif (base_block_type == 3) {
			$class_m = 202030200;
		}
	} else {
		if (base_block_type == 2) {
			$class_m = 102020200;
		} elseif (base_block_type == 3) {
			$class_m = 102030200;
		}
	}
	study_record_etc("$class_m");

	unset($_SESSION[REVIEW_LIST]);

	//	ユニット情報
	$sql  = "SELECT * FROM ".T_UNIT.
			" WHERE unit_num='$unit_num' LIMIT 1;";
	if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		foreach ($list AS $key => $val) {
			$$key = $val;
		}
	}

	//	レッスン名取得
	$sql  = "SELECT * FROM ".T_LESSON.
			" WHERE lesson_num='$lesson_num' LIMIT 1;";
	if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$lesson_name = $list['lesson_name'];
	}

	//	ポイント情報抜き出し
	$sql  = "SELECT a.skill_num, a.threshold, b.skill_name FROM ".T_REVIEW_SETTING." a, ".T_SKILL." b" .
			" WHERE a.skill_num=b.list_num" .
			" AND b.course_num='{$course_num}' AND a.block_num='{$block_num}';";
	if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$skill_num = $list['skill_num'];
			$REVIEW[$skill_num]['threshold'] = $list['threshold'];
			$REVIEW[$skill_num]['skill_name'] = $list['skill_name'];
		}
	}
	//	解答情報抜きだし。
	unset($where);
	if (base_block_type == 3) {
		$where = " AND problem_num IN (" .
					"SELECT problem_num FROM ".T_PROBLEM." WHERE block_num='{$block_num}' AND problem_type='2'" .
				 ")";
	}
	unset($class_m);
	if ($_SESSION['course']['review'] >= 2) {
		if (base_block_type == 2) {
			$class_m = 202020000;
		} elseif (base_block_type == 3) {
			$class_m = 202030000;
		}
	} else {
		if (base_block_type == 2) {
			$class_m = 102020000;
		} elseif (base_block_type == 3) {
			$class_m = 102030000;
		}
	}

	$sql  = "SELECT * FROM ".T_CHECK_STUDY_RECODE.
			" WHERE student_id='{$student_num}' AND block_num='{$block_num}'" .
//			" AND DATE(regist_time)=CURDATE()".
			" AND regist_time>'".$_SESSION['course']['finish_time']."'".
			" AND again IS NOT NULL".
			" AND class_m='{$class_m}'".
			$where.
			" ORDER BY display_problem_num, again, check_study_record_num;";
	if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			$problem_num_ = $list['problem_num'];
			$display_problem_num_ = $list['display_problem_num'];
			$success_ = $list['success'];
			if ($success_ == 1) {
				unset($FALSE_PROBLEM[$problem_num_]);
			} else {
				$FALSE_PROBLEM[$problem_num_] = $display_problem_num_;
			}
		}
	}

	unset($false_num_list);
	if ($FALSE_PROBLEM) {
		foreach ($FALSE_PROBLEM AS $key => $val) {
			if ($false_num_list) { $false_num_list .= ","; }
			$false_num_list .= $key;
		}
	}

	//	苦手分野のパラメーター抜き出し
	if ($false_num_list) {
		$sql  = "SELECT parameter FROM ".T_PROBLEM.
				" WHERE problem_num IN ($false_num_list);";
		if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
		if ($result = $cdb->query($sql)) {
			while ($list = $cdb->fetch_assoc($result)) {
				$parameter_ = $list['parameter'];
				preg_match_all("/\[([0-9]+)\]/",$parameter_,$ary);
				if ($ary[1]) {
					foreach ($ary[1] AS $val) {
						if (!$val) { continue; }
						$PARAMETER[$val]++;
					}
				}
			}
		}
	}

	//	苦手分野決定
	unset($REVIEW_LIST);
	if ($PARAMETER && $REVIEW) {
		foreach ($PARAMETER AS $key => $val) {
			if ($REVIEW[$key]['threshold'] > 0 && $REVIEW[$key]['threshold'] <= $val) {
				$REVIEW_LIST[$key] = $REVIEW[$key]['skill_name'];

				$INSERT_DATA[student_id] = $_SESSION["myid"]["id"];
				$INSERT_DATA[block_num] = $_SESSION['course']['block_num'];
				$INSERT_DATA[skill_num] = $key;
				$INSERT_DATA[review] = $_SESSION['course']['review'];
				$INSERT_DATA[regist_time] = "now()";
				$ERROR = $cdb->insert(T_RETURN_LOG,$INSERT_DATA);
				if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::query-&gt;insert::T_RETURN_LOG"; }
			}
		}
	}

	if ($REVIEW_LIST) { $_SESSION[REVIEW_LIST] = $REVIEW_LIST; }

	//	スタイルシート
	$morescript  = "<link rel=\"stylesheet\" href=\"../material/template/$course_num/result.css\" type=\"text/css\">\n";
    $morescript .= "<script type=\"text/javascript\" src=\"/javascript/userAgent.js\"></script>\n"; //kaopiz add 2020/08/15 ipados13//	del ookawara 2009/03/11_1
	$morescript .= "<script type=\"text/javascript\" src=\"/javascript/student.js\"></script>\n";	//	del ookawara 2009/03/11_1


	//	タイトル
	$title = $lesson_name." ".$unit_name." 診断結果";

	$flag = 0;	// check
	if ($REVIEW_LIST) {
		$next_file = STUDENT_POINT_FILE;

		//	スキルリスト
		$skill_list ="<ul>\n";
		foreach ($REVIEW_LIST AS $val) {
			if (!$val) { continue; }
			$val = ereg_replace("&lt;","<",$val);
			$val = ereg_replace("&gt;",">",$val);
			$skill_list .= "<li>". $val."</li>\n";
		}
		$skill_list .="</ul>\n";

		//	コメント
		$comment  = "以上がちょっと苦手なようだね。<br>\n";
		$comment .= "少し復習しておこう！";
	} else {
		$flag = 1;	// check
		$next_file = STUDENT_UNIT_COMP_FILE;

		//	コメント
		$next  = "ちゃんと理解できているようだね。<br>\n";
		$next .= "それでは次のステップへ進もう！";
	}

	//	中学・高校制御	add ookawara	2009/03/11_1	start
	//	ボタン名の頭に「h_」追加
	$jh_key = "";
	if (eregi("^H",$unit_key)) {
		$jh_key = "h_";
	}

//	$course_num = 1;	//	確認用
//	$jh_key = "h_";		//	確認用

	//	スタイルシート
	$morescript  = "<link rel=\"stylesheet\" href=\"../material/template/".$course_num."/".$jh_key."result.css\" type=\"text/css\">\n";
    $morescript .= "<script type=\"text/javascript\" src=\"/javascript/userAgent.js\"></script>\n"; //kaopiz add 2020/08/15 ipados13
	$morescript .= "<script type=\"text/javascript\" src=\"/javascript/student.js\"></script>\n";
	//	中学・高校制御	add ookawara	2009/03/11_1	end

	//	次へボタン
	if ($flag != 1) {
		$button  = "<form action=\"$next_file\" method=\"POST\">\n";
		$image1 = "/student/images/button/".$jh_key.$course_num."_24_2.gif";	//	2009/03/11_1
		$image2 = "/student/images/button/".$jh_key.$course_num."_24_1.gif";	//	2009/03/11_1
		if (file_exists("../".$image1)) {
			//upd start yamaguchi 2018/08/03 Mac safariでマウスオーバー時ボタンが消えて進めなくなる不具合修正
			//$button .= "<img src=\"$image1\" name=\"next\" alt=\"次へ進む\" onmousedown=\"HpbImgSwap('next', '$image2');\" onmouseup=\"HpbImgSwap('next', '$image1'); submit();\" onmouseout=\"HpbImgSwap('next', '$image1');\" style=\"cursor:pointer;\">";
			if (is_chrome_safari()) {
				$button .= "<img src=\"$image1\" name=\"next\" alt=\"次へ進む\" onmousedown=\"HpbImgSwap('next', '$image2');\" onmouseup=\"HpbImgSwap('next', '$image1'); submit();\" style=\"cursor:pointer;\">";
			//PC
			}else{
				$button .= "<img src=\"$image1\" name=\"next\" alt=\"次へ進む\" onmousedown=\"HpbImgSwap('next', '$image2');\" onmouseup=\"HpbImgSwap('next', '$image1'); submit();\" onmouseout=\"HpbImgSwap('next', '$image1');\" style=\"cursor:pointer;\">";
			}//upd end yamaguchi 2018/08/03
		} else {
			$button .= "<input type=\"submit\" value=\"次へ進む\" class=\"form_font_size\">\n";
		}
		$button .= "</form>\n";
	} else {
		//	閉じるボタン追加
		$comment = <<<EOT
<form>
終了<br>
<br>
<input type="button" value="閉じる" OnClick="window.close();">
</form>

EOT;
	}

	if ($ERROR) {
		$error = ERROR($ERROR);
	}

	$make_html = new read_html();
	$make_html->set_dir(STUDENT_TEMP_DIR);
	$make_html->set_file(STUDENT_TEMP_RESULT);
	$INPUTS[TITLE] = array('result'=>'plane','value'=>$title);
	$INPUTS[MORESCRIPT] = array('result'=>'plane','value'=>$morescript);
	$INPUTS[ERROR] = array('result'=>'plane','value'=>$error);
	$INPUTS[SKILLLIST] = array('result'=>'plane','value'=>$skill_list);
	$INPUTS[COMMENT] = array('result'=>'plane','value'=>$comment);
	$INPUTS[NEXT] = array('result'=>'plane','value'=>$next);
	$INPUTS[BUTTON] = array('result'=>'plane','value'=>$button);
	$make_html->set_rep_cmd($INPUTS);
	$html = $make_html->replace();

	// 画面サイズ調整
	$html = str_replace("<body>","<body onLoad=\"check_size(0,99);\">",$html);

	return $html;
}
?>