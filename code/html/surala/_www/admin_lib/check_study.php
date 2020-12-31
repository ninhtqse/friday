<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * 学習	check_unit情報設定
 *
 * @@@ 新ＤＢ対応
 * 	$_SESSION['manager']['manager_num']を$_SESSION["myid"]["id"]に変更
 * 	カラム名の変更
 *
 * @author Azet
 */


/**
 * defineの設定する機能
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 */
function set_unit() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if (!$_POST['block_num'] && !$_SESSION['course']['block_num']) {
		return ;
	} elseif ($_POST['block_num']) {
		$block_num = $_POST['block_num'];
	} elseif ($_SESSION['course']['block_num']) {
		$block_num = $_SESSION['course']['block_num'];
	}

	//	ブロック情報取得
	$sql  = "SELECT * FROM " . T_BLOCK .
			" WHERE block_num='".$block_num."' LIMIT 1;";
	if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$count = $cdb->num_rows($result);
		if ($count>0){
			foreach ($list AS $key => $val) {
				define("$key",$val);
			}
		}
	}
	$_SESSION['course']['course_num'] = course_num;
	$_SESSION['course']['unit_num'] = unit_num;
	$_SESSION['course']['block_num'] = block_num;
	$_SESSION['course']['block_type'] = block_type;

	//	横・縦書き設定
	$sql  = "SELECT write_type, math_align FROM ".T_COURSE.	//	add , math_align ookawara 2012/08/28
			" WHERE course_num='".course_num."' LIMIT 1;";
	if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		define("write_type",$list['write_type']);
		define("math_align",$list['math_align']);	//	add ookawara 2012/08/28
	}

	//	ステージ・レッスン名取得
	$sql  = "SELECT stage.stage_name, lesson.lesson_name FROM ".T_STAGE." stage,".T_LESSON." lesson".
			" WHERE stage.stage_num=lesson.stage_num AND lesson.lesson_num='".lesson_num."' LIMIT 1;";
	if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		define("stage_name",$list['stage_name']);
		define("lesson_name",$list['lesson_name']);
	}

	//	ユニット名取得
	$sql  = "SELECT unit_name FROM ".T_UNIT.
			" WHERE unit_num='".unit_num."' LIMIT 1;";
	if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		define("unit_name",$list['unit_name']);
	}

	//	学習回数チェック
	//	$_SESSION['course']['review']には、学習回数が登録される。
	if ($_SESSION['course']['review'] == "" || $_SESSION['course']['finish_time'] == "") {
		unset($_SESSION['course']['review']);
		unset($_SESSION['course']['finish_time']);
		$sql  = "SELECT regist_time FROM ".T_FINISH_UNIT.
				" WHERE student_id='".$_SESSION["myid"]["id"]."'".
				" AND course_num='".course_num."'".
				" AND unit_num='".unit_num."'".
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
}



/**
 * ブロック未確認エラー
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @return string
 */
function not_set_unit() {

	$html = "<br>\n";
	$html .= "学習するドリル情報が確認できません。<br>";

	return $html;
}



/**
 * 学習履歴チェック
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * (HTMLを表示する機能)
 * @author Azet
 */
function check_start() {
	global $L_CHECK_CLASS_M;

	unset($_SESSION['studentid']);

	$student_num = $_SESSION["myid"]["id"];

	if (!$_POST['block_num'] && !$_SESSION['course']['block_num']) {
		return ;
	} elseif ($_POST['block_num']) {
		$block_num = $_POST['block_num'];

		$_SESSION["callby"] = $_POST['callby'];		// ドリル動作確認を先生機能にも実装するため、呼び元を判別するために使用

		// 先生のときはユニット内の全てのドリルが見られる
		// ユニット内のどのドリルから始めるか学習履歴テーブルをチェック
		if ($_POST['callby']=="teacher"){

			if ($_POST['block_num']!=$_SESSION['course']['block_num']){		// セッションとポストのblock_numが違うときだけ

				// ブロックからユニットを取得
				$sql  = "SELECT unit_num FROM " . T_BLOCK .
						" WHERE block_num = ".$_POST['block_num'].";";
				if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
				if ($result = $cdb->query($sql)) {
					$list = $cdb->fetch_assoc($result);

					// 学習履歴テーブルから途中のブロックを取得
					$sql = "SELECT * FROM ".T_CHECK_STUDY_RECODE;
					$sql .= " WHERE student_id='{$student_num}'";
					$sql .= " AND DATE(regist_time)=CURDATE()";
					$sql .= " AND unit_num=".$list["unit_num"];
					$sql .= " ORDER BY regist_time DESC, check_study_record_num DESC";
					$sql .= " LIMIT 1 ";

					if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
					if ($result = $cdb->query($sql)) {
						$list = $cdb->fetch_assoc($result);
						if ($list['block_num']){
							$block_num = $list['block_num'];
						}
					}
				}
			}
		}
	} elseif ($_SESSION['course']['block_num']) {
		$block_num = $_SESSION['course']['block_num'];
	}

	//	ログ削除
	if ($_POST['type'] == "new") {
		$sql  = "DELETE FROM ".T_CHECK_STUDY_RECODE.
				" WHERE student_id='{$student_num}'".
				" AND block_num='{$block_num}';";
		$cdb->exec_query($sql);
	}

	// 生徒固有情報を初期化（削除）
	$where = " WHERE student_id='".$_SESSION["myid"]["id"]."'";

	$ERROR = $cdb->delete(T_STUDENT_PARA,$where);

	// 生徒固有情報を初期化（追加）
	$INSERT_DATA['student_id'] = $_SESSION["myid"]["id"];

	$ERROR = $cdb->insert(T_STUDENT_PARA,$INSERT_DATA);

	//	履歴チェック
	unset($file);
	$sql = "SELECT * FROM ".T_CHECK_STUDY_RECODE;
	$sql .= " WHERE student_id='{$student_num}'";
	$sql .= " AND DATE(regist_time)=CURDATE()";
	$sql .= " AND block_num='{$block_num}'";
	$sql .= " ORDER BY regist_time DESC, check_study_record_num DESC";
	$sql .= " LIMIT 1 ";

	if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		$file = $L_CHECK_CLASS_M[$list['class_m']][file];
	}

	//	途中から
	if ($file && $_POST['type'] == "next") {
		$_SESSION['course']['course_num'] = $list[course_num];
		$_SESSION['course']['unit_num'] = $list[unit_num];
		if ($list[class_m] == "101010000") {
			$_SESSION['course']['display_problem_num'] = $list[display_problem_num];
		} elseif ($list[class_m] == "101020000") {
			$_SESSION['course']['block_num'] = $list[block_num];
			$_SESSION['course']['review_unit_num'] = $list[review_unit_num];
			$_SESSION['course']['display_problem_num'] = $list[display_problem_num];
		} elseif ($list[class_m] == "101110000") {

		} elseif ($list[class_m] == "101120000") {
			$_SESSION['course']['block_num'] = $list[block_num];
			$_SESSION['course']['review_unit_num'] = $list[review_unit_num];
		} elseif ($list[class_m] <= "102010012") {
			$_SESSION['course']['block_num'] = $list[block_num];
		} elseif ($list[class_m] == "102010900") {
			$_SESSION['course']['block_num'] = $list[block_num];
			$_SESSION['course']['block_type'] = 1;
		} elseif ($list[class_m] <= "102020012") {
			$_SESSION['course']['block_num'] = $list[block_num];
		} elseif ($list[class_m] == "102020200") {
			$_SESSION['course']['block_num'] = $list[block_num];
			$_SESSION['course']['block_type'] = 2;
		} elseif ($list[class_m] == "102020300") {
			$_SESSION['course']['block_num'] = $list[block_num];
			$_SESSION['course']['block_type'] = 2;
		} elseif ($list[class_m] <= "102020412") {
			$_SESSION['course']['block_num'] = $list[block_num];
			$_SESSION['course']['block_type'] = 2;
		} elseif ($list[class_m] <= "102020700") {
			$_SESSION['course']['block_num'] = $list[block_num];
		} elseif ($list[class_m] == "102010900") {
			$_SESSION['course']['block_num'] = $list[block_num];
			$_SESSION['course']['block_type'] = 2;
		} elseif ($list[class_m] <= "102030012") {
			$_SESSION['course']['block_num'] = $list[block_num];
		} elseif ($list[class_m] == "102030200") {
			$_SESSION['course']['block_num'] = $list[block_num];
			$_SESSION['course']['block_type'] = 3;
		} elseif ($list[class_m] == "102030300") {
			$_SESSION['course']['block_num'] = $list[block_num];
			$_SESSION['course']['block_type'] = 3;
		} elseif ($list[class_m] <= "102030412") {
			$_SESSION['course']['block_num'] = $list[block_num];
			$_SESSION['course']['block_type'] = 3;
		} elseif ($list[class_m] <= "102030700") {
			$_SESSION['course']['block_num'] = $list[block_num];
		} elseif ($list[class_m] == "102030900") {
			$_SESSION['course']['block_num'] = $list[block_num];
			$_SESSION['course']['block_type'] = 3;
		}

		header ("Location: $file\n\n");
		exit;
	}

	//	確認
	if ($list[course_num]) {

		$morescript = "<link rel=\"stylesheet\" href=\"../material/template/".$list["course_num"]."/unit_comp.css\" type=\"text/css\">\n";	// 2008/11/28_2 add

		$comment = <<<EOT
途中から行いますか？<br>それとも最初から行いますか？<br>
<div style="width:100px;">
<form action="$PHP_SELF" method="POST" style="float:left;">
<input type="hidden" name="action" value="start">
<input type="hidden" name="type" value="new">
<input type="hidden" name="block_num" value="{$block_num}">
<input type="submit" value="最初から">
</form>
<form action="$file" method="POST" style="float:left;">
<input type="hidden" name="action" value="start">
<input type="hidden" name="type" value="next">
<input type="hidden" name="block_num" value="{$block_num}">
<input type="submit" value="途中から">
</form>
</div>
EOT;

		$make_html = new read_html();
		$make_html->set_dir(STUDENT_TEMP_DIR.$list[course_num]."/");
		$make_html->set_file(STUDENT_TEMP_UNIT_COMP);
		$INPUTS[MORESCRIPT] = array('result'=>'plane','value'=>$morescript);
		$INPUTS[COMMENT] = array('result'=>'plane','value'=>$comment);
		$make_html->set_rep_cmd($INPUTS);
		$html = $make_html->replace();

		// 画面サイズ調整
		$html = str_replace("<body>","<body onLoad=\"check_size(0,99);\">",$html);

		echo $html;
		exit;
	}

}
?>