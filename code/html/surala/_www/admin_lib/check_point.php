<?PHP
/**
 * ベンチャー・リンク　すらら
 *
 * 診断ドリルポイント表示
 * ログ関係	エリアなど削除
 * 			テーブル名　T_CHECK_STUDY_RECODEへ変更
 * 			$_SESSION['manager']['manager_num']を$_SESSION['manager']['manager_num']変更
 * study_record_numをcheck_study_record_num
 *
 * @@@ 新ＤＢ対応
 * 	$_SESSION['manager']['manager_num']を$_SESSION["myid"]["id"]に変更
 * 	カラム名の変更
 *
 * @author Azet
 */


/**
 * SESSION判断して、HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @return string HTML
 */
function start() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	unset($_SESSION['record']['problem_num']);

	$student_num = $_SESSION["myid"]["id"];
	define('student_num',$student_num);
	$course_num = $_SESSION['course']['course_num'];
	$unit_num = $_SESSION['course']['unit_num'];
	if ($_POST['block_num']) {
		$block_num = $_POST['block_num'];
	} else {
		$block_num = $_SESSION['course']['block_num'];
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

	if (!$block_num) {
		if ($_SESSION['course']['review'] >= 2) {
			$class_m_where = "(class_m='202020400' OR class_m='202030400')";
		} else {
			$class_m_where = "(class_m='102020400' OR class_m='102030400')";
		}
		$sql  = "SELECT block_num FROM ".T_CHECK_STUDY_RECODE.
				" WHERE student_id='".$student_num."'".
				" AND ".$class_m_where.
				" AND course_num='".$course_num."'".
				" AND unit_num='".$unit_num."'".
//				" AND DATE(regist_time)=CURDATE()".
				" AND regist_time>'".$_SESSION['course']['finish_time']."'".
				" ORDER BY check_study_record_num DESC LIMIT 1;";
		if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$block_num = $list['block_num'];
		}
	}

	//	ブロック情報読み込み
	$sql  = "SELECT * FROM ".T_BLOCK.
			" WHERE course_num='".$course_num."'".
			" AND unit_num='".$unit_num."'".
			" AND block_num='".$block_num."'".
			" LIMIT 1;";
	if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		foreach ($list AS $key => $val) {
			if ($key == "block_type") {
				define("base_block_type",$val);
			}
			$$key = $val;
			define("$key",$val);
		}
	}

	//	復習スキルチェック
	if (!$_SESSION[REVIEW_LIST]) {
		$REVIEW_LIST = review_check();
	} else {
		$REVIEW_LIST = $_SESSION[REVIEW_LIST];
	}

	//	終了スキルチェック
	if ($REVIEW_LIST) {
		if ($_SESSION['course']['review'] >= 2) {
			if (base_block_type == 2) {
				$class_m = "202020500,202020510";
			} elseif (base_block_type == 3) {
				$class_m = "202030500,202030510";
			}
		} else {
			if (base_block_type == 2) {
				$class_m = "102020500,102020510";
			} elseif (base_block_type == 3) {
				$class_m = "102030500,102030510";
			}
		}
		$sql  = "SELECT review_unit_num FROM ".T_CHECK_STUDY_RECODE.
				" WHERE student_id='".$student_num."'".
				" AND class_m IN (".$class_m.")".
				" AND course_num='".$course_num."'".
				" AND unit_num='".$unit_num."'".
				" AND block_num='".$block_num."'".
//				" AND DATE(regist_time)=CURDATE()".
				" AND regist_time>'".$_SESSION['course']['finish_time']."'".
				" ORDER BY review_unit_num;";
		if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
		if ($result = $cdb->query($sql)) {
			while ($list = $cdb->fetch_assoc($result)) {
				$review_unit_num = $list['review_unit_num'];
				if ($REVIEW_LIST[$review_unit_num]) { unset($REVIEW_LIST[$review_unit_num]); }
			}
		}
	}

	//	ポイント解説スキル番号設定
	unset($review_skill_num);
	if ($REVIEW_LIST) {
		foreach ($REVIEW_LIST AS $key => $val) {
			if ($key && $val) {
				$review_skill_num = $key;
				$review_skill_name = $val;
				break;
			}
		}
	}

	if (!$review_skill_num) {
		//	ポイント解説終了
		$STUDENT_REVIEW_FINISH_FILE = STUDENT_REVIEW_FINISH_FILE;	//	ドリル終了ファイルへ
		header ("Location: $STUDENT_REVIEW_FINISH_FILE\n\n");
		exit;
	} else {
		$_SESSION['course']['review_unit_num'] = $review_skill_num;
		//	スキル情報読み込み
		$sql  = "SELECT * FROM ".T_REVIEW_SETTING.
				" WHERE skill_num='".$review_skill_num."'".
				" AND course_num='".$course_num."'" .
				" AND unit_num='".$unit_num."'" .
				" AND block_num='".$block_num."'" .
				" AND display='1'" .
				" AND state!='1'" .
				" LIMIT 1;";
		if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			foreach ($list AS $key => $val) {
				$$key = $val;
			}
		}

		//	ポイント解説読み込み
		$sql  = "SELECT skill_name, remarks FROM ".T_SKILL.
				" WHERE course_num='".$course_num."'" .
				" AND list_num='".$review_skill_num."'".
				" AND state!='1'".
				" LIMIT 1;";
		if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$remarks = $list['remarks'];
			$review_skill_name = $list['skill_name'];
		}

		//	ドリル問題が設定してあるかチェック
		unset($count);
		$sql  = "SELECT * FROM ".T_PROBLEM.
				" WHERE course_num='".$course_num."'" .
				" AND block_num='".$block_num."'" .
				" AND problem_type='3'".
				" AND parameter like '%[".$review_skill_num."]%'".
				" AND display='1'".
				" AND state!='1';";
		if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
		if ($result = $cdb->query($sql)) {
			$count = $cdb->num_rows($result);
		}

		//	中学・高校制御	add ookawara	2009/03/11_1	start

		//	ユニット情報
		$sql  = "SELECT unit_key FROM ".T_UNIT.
				" WHERE unit_num='$unit_num' LIMIT 1;";
		if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$unit_key = $list['unit_key'];
		}

		//	ボタン名の頭に「h_」追加
		$jh_key = "";
		if (eregi("^H",$unit_key)) {
			$_SESSION['course']['sy'] = "h";	//	add ookawara 2009/03/11_1
			$jh_key = "h_";
		}

//	$_SESSION['course']['course_num'] = 3;	//	確認用
//	$jh_key = "h_";		//	確認用

		//	スタイルシート
		$morescript  = "<link rel=\"stylesheet\" href=\"../material/template/".$_SESSION['course']['course_num']."/".$jh_key."point.css\" type=\"text/css\">\n";
		//	中学・高校制御	add ookawara	2009/03/11_1	end

		//	スタイルシート
//		$morescript  = "<link rel=\"stylesheet\" href=\"../material/template/$course_num/point.css\" type=\"text/css\">\n";	//	del ookawara 2009/03/11_1
		$morescript .= "<script type=\"text/javascript\" src=\"/javascript/userAgent.js\"></script>\n"; //kaopiz add 2020/08/15 ipados13
		$morescript .= "<script type=\"text/javascript\" src=\"/javascript/student.js\"></script>\n";
		$morescript .= "<script type=\"text/javascript\" src=\"/student/javascript/study.js\"></script>\n";

		if ($count > 1) {
			$STUDENT_REVIEW_DRILL_FILE = STUDENT_REVIEW_DRILL_FILE;
			$drill_button  = "<form action=\"{$STUDENT_REVIEW_DRILL_FILE}\" method=\"POST\">\n";
			$drill_button .= "<input type=\"hidden\" name=\"block_num\" value=\"{$block_num}\">\n";
			$drill_button .= "<input type=\"hidden\" name=\"skill_num\" value=\"{$review_skill_num}\">\n";
			$image1 = "/student/images/button/".$jh_key.$course_num."_23_2.gif";	//	2009/03/11_1
			$image2 = "/student/images/button/".$jh_key.$course_num."_23_1.gif";	//	2009/03/11_1
			if (file_exists("../".$image1)) {
				//upd start yamaguchi 2018/08/03 Mac safariでマウスオーバー時ボタンが消えて進めなくなる不具合修正
				//$drill_button .= "<img src=\"$image1\" name=\"drill\" alt=\"復習ドリルに進む\" onmousedown=\"HpbImgSwap('drill', '$image2');\" onmouseup=\"HpbImgSwap('drill', '$image1'); submit();\" onmouseout=\"HpbImgSwap('drill', '$image1');\" style=\"cursor:pointer;\">";
				// Chromeまたはsafariの場合は、onmouseoutが不要。
				if (is_chrome_safari()) {
					$drill_button .= "<img src=\"$image1\" name=\"drill\" alt=\"復習ドリルに進む\" onmousedown=\"HpbImgSwap('drill', '$image2');\" onmouseup=\"HpbImgSwap('drill', '$image1'); submit();\" style=\"cursor:pointer;\">";
				//PC
				}else{
					$drill_button .= "<img src=\"$image1\" name=\"drill\" alt=\"復習ドリルに進む\" onmousedown=\"HpbImgSwap('drill', '$image2');\" onmouseup=\"HpbImgSwap('drill', '$image1'); submit();\" onmouseout=\"HpbImgSwap('drill', '$image1');\" style=\"cursor:pointer;\">";
				}//upd end yamaguchi 2018/08/03
			} else {
				$drill_button .= "<input type=\"submit\" value=\"復習ドリルに進む\">\n";
			}
			$drill_button .= "</form>\n";
		}

		//	レッスンステージがあるかチェックチェック
		if ($review_unit_num && $lesson_page) {
			$sql  = "SELECT course_num, stage_num, lesson_num, unit_num FROM ".T_UNIT.
					" WHERE course_num='".$course_num."'".
					" AND unit_num='".$review_unit_num."'".
					" AND display='1'".
					" AND state!='1'".
					" LIMIT 1;";
			if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
			if ($result = $cdb->query($sql)) {
				$list = $cdb->fetch_assoc($result);
				$return_course_num = $list['course_num'];
				$return_stage_num = $list['stage_num'];
				$return_lesson_num = $list['lesson_num'];
				$review_unit_num = $list['unit_num'];
			}
			$flash_path = MATERIAL_FLASH_DIR.$return_course_num."/".$return_stage_num."/".$return_lesson_num."/".$review_unit_num."/".STUDENT_REVIEW_FLASH_MOVIE_FILE;

			if (file_exists($flash_path)) {
				$STUDENT_REVIEW_LESSON_FILE = STUDENT_REVIEW_LESSON_FILE;
				$lesson_button  = "<form action=\"{$STUDENT_REVIEW_LESSON_FILE}\" method=\"POST\">\n";
				$lesson_button .= "<input type=\"hidden\" name=\"block_num\" value=\"{$block_num}\">\n";
				$lesson_button .= "<input type=\"hidden\" name=\"skill_num\" value=\"{$review_skill_num}\">\n";
				$lesson_button .= "<input type=\"hidden\" name=\"review_unit_num\" value=\"{$review_unit_num}\">\n";
				$lesson_button .= "<input type=\"hidden\" name=\"lesson_page\" value=\"{$lesson_page}\">\n";
				$image1 = "/student/images/button/".$jh_key.$course_num."_22_2.gif";	//	2009/03/11_1
				$image2 = "/student/images/button/".$jh_key.$course_num."_22_1.gif";	//	2009/03/11_1
				if (file_exists("../".$image1)) {
					//upd start yamaguchi 2018/08/03 Mac safariでマウスオーバー時ボタンが消えて進めなくなる不具合修正
					//$lesson_button .= "<img src=\"$image1\" name=\"lesson\" alt=\"授業を復習する\" onmousedown=\"HpbImgSwap('lesson', '$image2');\" onmouseup=\"HpbImgSwap('lesson', '$image1'); submit();\" onmouseout=\"HpbImgSwap('lesson', '$image1');\" style=\"cursor:pointer;\">";
					// Chromeまたはsafariの場合は、onmouseoutが不要。
					if (is_chrome_safari()) {
						$lesson_button .= "<img src=\"$image1\" name=\"lesson\" alt=\"授業を復習する\" onmousedown=\"HpbImgSwap('lesson', '$image2');\" onmouseup=\"HpbImgSwap('lesson', '$image1'); submit();\"  style=\"cursor:pointer;\">";
					//PC
					}else{
						$lesson_button .= "<img src=\"$image1\" name=\"lesson\" alt=\"授業を復習する\" onmousedown=\"HpbImgSwap('lesson', '$image2');\" onmouseup=\"HpbImgSwap('lesson', '$image1'); submit();\" onmouseout=\"HpbImgSwap('lesson', '$image1');\" style=\"cursor:pointer;\">";
					}//upd end yamaguchi 2018/08/03
				} else {
					$lesson_button .= "<input type=\"submit\" value=\"授業を復習する\">\n";
				}
				$lesson_button .= "</form>\n";
			}
		}

		//	画像変換
		$remarks = change_img($remarks);
		//	音声変換
		$remarks = change_voice($remarks);
		$remarks = replace_decode($remarks);

		$title = "ポイント解説";	// 2008/11/28_2

		$make_html = new read_html();
		$make_html->set_dir(STUDENT_TEMP_DIR);
		$make_html->set_file(STUDENT_TEMP_POINT);
		$INPUTS[TITLE] = array('result'=>'plane','value'=>$title);
		$INPUTS[MORESCRIPT] = array('result'=>'plane','value'=>$morescript);
		$INPUTS[ERROR] = array('result'=>'plane','value'=>$error);
		$review_skill_name = ereg_replace("&lt;","<",$review_skill_name);
		$review_skill_name = ereg_replace("&gt;",">",$review_skill_name);
		$INPUTS[SKILLNAME] = array('result'=>'plane','value'=>$review_skill_name);
		$INPUTS[SKILLCOMMENT] = array('result'=>'plane','value'=>$remarks);//解説文
		$INPUTS[DRILLBUTTON] = array('result'=>'plane','value'=>$drill_button);
		$INPUTS[LESSONBUTTON] = array('result'=>'plane','value'=>$lesson_button);
		$make_html->set_rep_cmd($INPUTS);
		$html = $make_html->replace();

		// 画面サイズ調整
		$html = str_replace("<body>","<body onLoad=\"check_size(0,99);\">",$html);
	}

	unset($class_m);
	if ($_SESSION['course']['review'] >= 2) {
		if (base_block_type == 2) {
			$class_m = 202020300;
		} elseif (base_block_type == 3) {
			$class_m = 202030300;
		}
	} else {
		if (base_block_type == 2) {
			$class_m = 102020300;
		} elseif (base_block_type == 3) {
			$class_m = 102030300;
		}
	}
	$INSERT_DATA['class_m'] = $class_m;
	$INSERT_DATA['student_id'] = $student_num;
//	$INSERT_DATA['area_num'] = $_SESSION['studentid']['area_num'];
//	$INSERT_DATA['enterprise_num'] = $_SESSION['studentid']['enterprise_num'];
//	$INSERT_DATA['school_num'] = $_SESSION['studentid']['school_num'];
//	$INSERT_DATA['cr_num'] = $_SESSION['pc']['classroom'];
//	$INSERT_DATA['seat_num'] = $_SESSION['pc']['seat'];
	$INSERT_DATA['course_num'] = $course_num;
	$INSERT_DATA['unit_num'] = $unit_num;
	$INSERT_DATA['block_num'] = $block_num;
	$INSERT_DATA['review_unit_num'] = $review_skill_num;
	$INSERT_DATA['regist_time'] = "now()";

	$ERROR = $cdb->insert(T_CHECK_STUDY_RECODE,$INSERT_DATA);
	if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::query-&gt;insert::T_CHECK_STUDY_RECODE"; }



	return $html;
}


/**
 * 復習スキルチェック
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @return array
 */
function review_check() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	//	生徒情報
	$student_num = student_num;
	$course_num = $_SESSION['course']['course_num'];
	$unit_num = $_SESSION['course']['unit_num'];
	if ($_POST['block_num']) {
		$block_num = $_POST['block_num'];
	} else {
		$block_num = $_SESSION['course']['block_num'];
	}

	//	ブロック情報読み込み
	$sql  = "SELECT * FROM ".T_BLOCK.
			" WHERE course_num='".$course_num."'".
			" AND unit_num='".$unit_num."'".
			" AND block_num='".$block_num."'".
			" LIMIT 1;";
	if (CHECK_MODE == 1) { $_SESSION[SQL][] = __FILE__ . "(" .__LINE__ . ")::" .$sql; }
	if ($result = $cdb->query($sql)) {
		$list = $cdb->fetch_assoc($result);
		foreach ($list AS $key => $val) {
			$$key = $val;
		}
	}

	//	ポイント情報抜き出し
	$sql  = "SELECT review_setting.skill_num, review_setting.threshold, skill.skill_name".
			" FROM ".T_REVIEW_SETTING." review_setting, ".T_SKILL." skill" .
			" WHERE review_setting.skill_num=skill.list_num" .
			" AND skill.course_num='".$course_num."'".
			" AND review_setting.block_num='".$block_num."';";
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
	if ($unit_type == 3) {
		$where = " AND problem_num IN (" .
					"SELECT problem_num FROM ".T_PROBLEM.
					" WHERE course_num='".$course_num."'".
					" AND block_num='".$block_num."'".
					" AND problem_type='2'".
					" AND display='1'".
					" AND state!='1'".
				 ")";
	}

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
	$sql  = "SELECT problem_num, display_problem_num, success FROM ".T_CHECK_STUDY_RECODE.
			" WHERE student_id='".$student_num."'".
			" AND course_num='".$course_num."'" .
			" AND block_num='".$block_num."'" .
			" AND class_m='".$class_m."'".
			$where.
			" AND again IS NOT NULL".
//			" AND DATE(regist_time)=CURDATE()".
			" AND regist_time>'".$_SESSION['course']['finish_time']."'".
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
				" WHERE problem_num IN ($false_num_list)".
				" AND course_num='".$course_num."'".
				" AND block_num='".$block_num."';";
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
			}
		}
	}
	return $REVIEW_LIST;
}


/**
 * 画像変換
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @param string $html
 * @return string
 */
function change_img($html) {
	preg_match_all("|\[!IMG=(.*)!\]|U",$html,$IMG);
	if ($IMG) {
		foreach ($IMG[1] AS $key => $VAL) {
			$img_name = $IMG[0][$key];
			list($file_name_,$width_,$height_) = explode(",",$VAL);
			$file = MATERIAL_SKILL_IMG_DIR.course_num."/".$file_name_;
			if (file_exists($file)) {
				unset($width_);
				unset($height_);
				if ($width_) { $width_ = "width=\"{$width_}\""; }
				if ($height_) { $height_ = "height=\"{$height_}\""; }
				$img_ = "<img src=\"$file\" {$width_} {$height_}>";
				$img_name = change_en($img_name);
				$html = preg_replace("/{$img_name}/",$img_,$html);
			}
		}
	}
	return $html;
}


/**
 * 音声変換
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @param string $html
 * @return string
 */
function change_voice($html) {
	preg_match_all("|\[!VOICE=(.*)!\]|U",$html,$VOICE);
	if ($VOICE) {
		foreach ($VOICE[1] AS $key => $VAL) {
			$voice_name = $VOICE[0][$key];
			$file_name_ = $VAL;
			$file = MATERIAL_SKILL_VOICE_DIR.course_num."/".$file_name_;
			unset($voice_);
			$voice_name = change_en($voice_name);
			if ($file_name_ && file_exists($file)) {
				list($usemap) = explode(".",$file_name_);
				$usemap1 = "voice1".eregi_replace("[^0-9a-z]","",$usemap);
				$usemap2 = "voice2".eregi_replace("[^0-9a-z]","",$usemap);
				$usemap3 = "voice3".eregi_replace("[^0-9a-z]","",$usemap);
				$voice_ = <<<EOT
<img src="/student/images/button/switch.gif" width="85" height="38" border="0" usemap="#$usemap2" class="$usemap3" name="$usemap3">
<object id="$usemap1" classid="clsid:22D6F312-B0F6-11D0-94AB-0080C74C7E95" style="position : absolute;visibility : hidden;">
  <param name="AutoStart" value="False">
  <param name="Filename" value="$file">
  <param name="ShowControls" value="False">
</object>
<map name="$usemap2">
  <area shape="rect" coords="8,7,29,35" onclick="javascript:$usemap1.stop(); $usemap1.CurrentPosition=0;" nohref alt="STOP" onmousedown="HpbImgSwap('$usemap3', '/student/images/button/switch_1.gif');" onmouseup="HpbImgSwap('$usemap3', '/student/images/button/switch.gif');">
  <area shape="rect" coords="34,7,55,35" onclick="javascript:$usemap1.play();" nohref alt="PLAY" onmousedown="HpbImgSwap('$usemap3', '/student/images/button/switch_2.gif');" onmouseup="HpbImgSwap('$usemap3', '/student/images/button/switch.gif');">
  <area shape="rect" coords="58,7,79,35" onclick="javascript:$usemap1.pause();" nohref alt="PAUSE" onmousedown="HpbImgSwap('$usemap3', '/student/images/button/switch_3.gif');" onmouseup="HpbImgSwap('$usemap3', '/student/images/button/switch.gif');">
  <area shape="default" nohref>
</map>
EOT;
				$html = preg_replace("/{$voice_name}/",$voice_,$html);
			}
		}
	}
	return $html;
}


/**
 * []()変換
 *
 * AC:[A]管理者 UC1:[L06]勉強をする.
 *
 * @author Azet
 * @param string $text
 * @return string
 */
function change_en($text) {
	$text = ereg_replace("\[","\\[",$text);
	$text = ereg_replace("\(","\\(",$text);
	$text = ereg_replace("\)","\\)",$text);
	$text = ereg_replace("\]","\\]",$text);
	$text = ereg_replace("\*","\\*",$text);
	$text = ereg_replace("\!","\\!",$text);
	return $text;
}
?>