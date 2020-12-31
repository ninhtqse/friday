<?
/**
 * ベンチャー・リンク　すらら
 *
 * 保護者・生徒管理　一括CSVインポート
 *
 * @author Azet
 */
include_once("/data/home/www/decryt_field_db.php"); //add kaopiz 2020/25/06 load encrypt file
/**
 * HTMLを作成する機能
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string HTML
 */
function start() {
	if (ACTION == "make_csv") { $ERROR = make_csv(); }

	if (MODE == "upload") {
		$MSG = upload();
		if (!$MSG['error'] && !$MSG['check_error']) {
			$html .= upload_html($MSG['msg'],$MSG['student_num']);
		} else {
			$html .= select_course($MSG);
		}
	} else {
		$html .= select_course($ERROR);
	}

	return $html;
}


/**
 * 加盟店選択
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

	$flag = 0;
	if (!$_POST[enterprise_num] && $_POST[school_num]) {
		$sql = "SELECT enterprise_num FROM " . T_SCHOOL .
		" WHERE state!='1'" .
		" AND school_num='".$_POST[school_num]."' LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$max = $cdb->num_rows($result);
		}
		if ($max) {
			$list = $cdb->fetch_assoc($result);
			$_POST[enterprise_num] = $list[enterprise_num];
		}
	}
	$sql = "SELECT enterprise_num,enterprise_name,enterprise_code FROM " . T_ENTERPRISE .
			" WHERE state!='1'" .
			" ORDER BY list_num,enterprise_kana;";
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
	}
	if (!$max) {
		$html = "<br>\n";
		$html .= "加盟店を設定してからご利用下さい。<br>\n";
		return $html;
	}
	if ($_POST[enterprise_num]==="") { $selected = "selected"; } else { $selected = ""; }
	$enterprise_num_html .= "<option value=\"\" $selected>選択して下さい</option>\n";
	while ($list = $cdb->fetch_assoc($result)) {
		if ($list['enterprise_num'] === $_POST['enterprise_num']) { $selected = "selected"; } else { $selected = ""; }
		$enterprise_num_html .= "<option value=\"".$list['enterprise_num']."\" $selected>".$list['enterprise_name']."</option>\n";
	}

	if (!$_POST[school_num]) { $selected = "selected"; } else { $selected = ""; }
	$school_num_html .= "<option value=\"\" $selected>----------</option>\n";
		$sql = "SELECT school_num,enterprise_num,cram_school_name,school_code FROM " . T_SCHOOL .
			" WHERE state!='1'";
		if ($_POST[enterprise_num] != "") {
			$sql .= " AND enterprise_num='".$_POST[enterprise_num]."'";
		}
		$sql .= " ORDER BY list_num;";
		if ($result = $cdb->query($sql)) {
			$max = $cdb->num_rows($result);
			if ($max) {
				while ($list = $cdb->fetch_assoc($result)) {
					if ($list['school_num'] === $_POST['school_num']) { $selected = "selected"; } else { $selected = ""; }
					$school_num_html .= "<option value=\"".$list['school_num']."\" $selected>".$list['cram_school_name']."</option>\n";
				}
			}
		}
	$html .= "<br>\n";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" name=\"menu\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"set_course\">\n";
	$html .= $school_html;
	$html .= "<table class=\"unit_form\">\n";
	$html .= "<tr class=\"unit_form_cell\">\n";
	$html .= "<td class=\"stage_form_menu\">加盟店名</td>\n";
	$html .= "<td class=\"stage_form_menu\">教室名</td>\n";
	$html .= "</tr>\n";
	$html .= "<tr class=\"stage_form_cell\">\n";
	$html .= "<td>\n";
	$html .= "<select name=\"enterprise_num\" onchange=\"submit()\">\n";
	$html .= $enterprise_num_html;
	$html .= "</select>\n";
	$html .= "</td>\n";
	$html .= "<td>\n";
	$html .= "<select name=\"school_num\" onchange=\"submit()\">\n";
	$html .= $school_num_html;
	$html .= "</select>\n";
	$html .= "</td>\n";
	$html .= "</tr>\n";
	$html .= "</table>\n";
	$html .= "</form><br>\n";

	if ($_POST['enterprise_num'] != "" && $_POST['school_num'] != "") {
		$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" enctype=\"multipart/form-data\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"upload\">\n";
		$html .= "<input type=\"hidden\" name=\"enterprise_num\" value=\"".$_POST[enterprise_num]."\">\n";
		$html .= "<input type=\"hidden\" name=\"school_num\" value=\"".$_POST[school_num]."\">\n";
		$html .= "CSVファイル（S-JIS）を指定し登録ボタンを押してください。<br>\n";
		$html .= "<input type=\"file\" size=\"60\" name=\"csv_file\"><br>\n";
		$html .= "<input type=\"submit\" value=\"生徒・保護者登録\">\n";
		$html .= "</form>\n";
	} elseif ($_POST['enterprise_num'] != "") {
		if (!$max) {
			$html .= "教室名が設定されておりません。<br>\n";
		} else {
			$html .= "教室名を選択してください。<br>\n";
		}
	} elseif ($_POST['enterprise_num'] == "" && $_POST['school_num'] == "") {
		$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\" enctype=\"multipart/form-data\">\n";
		$html .= "<input type=\"hidden\" name=\"mode\" value=\"upload\">\n";
		$html .= "<input type=\"hidden\" name=\"enterprise_num\" value=\"".$_POST[enterprise_num]."\">\n";
		$html .= "<input type=\"hidden\" name=\"school_num\" value=\"".$_POST[school_num]."\">\n";
		$html .= "加盟店名・教室名を指定していない場合、CSVファイルの教室コード、教室IDを参照します。<br>\n";
		$html .= "CSVファイル（S-JIS）を指定し登録ボタンを押してください。<br>\n";
		$html .= "<input type=\"file\" size=\"60\" name=\"csv_file\"><br>\n";
		$html .= "<input type=\"submit\" value=\"生徒・保護者登録\">\n";
		$html .= "</form>\n";
	}

	if ($MSG['msg']) {
		$html .= "<br>\n";
		$html .= $MSG['msg'] . "<br>\n";
	}
	if ($MSG['error']) {
		$html .= "<br>\n";
		$html .= "<div class=\"small_error\">\n";
		$html .= ERROR($MSG['error']);
		$html .= "</div>\n";
	}
	if ($MSG['check_error']) {
		$html .= "<br>\n";
		$html .= "<div class=\"small_error\">\n";
		foreach ($MSG['check_error'] AS $val) {
			$html .= ERROR($val);
		}
		$html .= "</div>\n";
	}

	return $html;
}


/**
 * ファイルアップ処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return array ステータスのメッセージ
 */
function upload() {

	include("../../_www/problem_lib/problem_regist.php");

//	if ($_POST['enterprise_num'] === "") { $ERROR[] = "加盟店が確認できません。"; }
//	if ($_POST['school_num'] === "") { $ERROR[] = "教室が確認できません。"; }

	$file_name = $_FILES['csv_file']['name'];
	$file_tmp_name = $_FILES['csv_file']['tmp_name'];
	$file_error = $_FILES['csv_file']['error'];
	if (!$file_tmp_name) {
		$ERROR[] = "CSVファイルが指定されておりません。";
	} elseif (!eregi("(.txt)$",$file_name)) {
		$ERROR[] = "ファイルの拡張子が不正です。";
	} elseif ($file_error == 1) {
		$ERROR[] = "アップロードできるファイルの容量が設定範囲を超えてます。サーバー管理者へ相談してください。";
	} elseif ($file_error == 3) {
		$ERROR[] = "CSVファイルの一部分のみしかアップロードされませんでした。";
	} elseif ($file_error == 4) {
		$ERROR[] = "CSVファイルがアップロードされませんでした。";
	}
	if ($ERROR) {
		if ($file_tmp_name && file_exists($file_tmp_name)) { unlink($file_tmp_name); }
		$MSG['error'] = $ERROR;
		return $MSG;
	}

	$LIST = file($file_tmp_name);
	if ($LIST) {
		$i = 1;
		foreach ($LIST AS $VAL) {
			unset($LINE);
//			$VAL = trim($VAL);
			if (!$VAL || !ereg("\t",$VAL)) { continue; }
			list(
				$LINE['school_code'],$LINE['manager_id'],$LINE['student_id'],$LINE['student_name'],$LINE['student_kana'],
				$LINE['student_pass'],$LINE['eng'],$LINE['jap'],$LINE['math'],$LINE['student_email'],
				$LINE['student_mobile_email'],$LINE['birthday'],$LINE['student_sex'],$LINE['school_name'],$LINE['brother_num'],
				$LINE['zip'],$LINE['prf'],$LINE['city'],$LINE['add1'],$LINE['add2'],$LINE['tel'],
				$LINE['fax'],$LINE['application'],$LINE['admissionday'],$LINE['status'],$LINE['account'],
				$LINE['use_area'],$LINE['secessionday'],$LINE['admissionroot'],$LINE['student_remarks'],$LINE['student_remarks2'],
				$LINE['student_remarks3'],$LINE['guardian_name'],$LINE['guardian_kana'],$LINE['guardian_id'],$LINE['guardian_pass'],
				$LINE['guardian_email'],$LINE['guardian_mobile_email'],$LINE['guardian_tel'],$LINE['guardian_mobile'],$LINE['guardian_fax'],
				$LINE['emergency_contact'],$LINE['login_email'],$LINE['guardian_remarks'],$LINE['brother_flag']
				) = explode("\t",$VAL);
			if ($LINE) {
				foreach ($LINE AS $key => $val) {
					if ($val) {
						$val = replace_encode_sjis($val);
						$val = mb_convert_encoding($val,"UTF-8","sjis-win");
						$LINE[$key] = replace_word($val);
					}
				}
			}
//pre("------------1");
//pre($LINE);

			unset($C_ERROR);
			$C_ERROR = check_data($LINE);
//pre("------------2");
//pre($C_ERROR);
			if (!$C_ERROR) {
				unset($S_ERROR);
				list($student,$S_ERROR) = reggist_data($LINE);
			}
			//	エラー記録処理
			unset($error_msg);

			$MSG['student_num'][] = $student;
			if ($C_ERROR) {
				$false++;
				$ERROR_NUM[] = $i;
				$ERROR[] = $C_ERROR;
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
		$MSG['msg'] = "登録失敗：".$system_error."人。<br>";
	}
	if ($success) {
		$MSG['msg'] .= "エラー無し：".$success."人、<br>";
	}
	if ($false) {
		$MSG['msg'] .= "エラー有り：".$false."人、<br>";
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
			$MSG['msg'] .= "エラー行数：".$error_num."<br>\n";
		}
	}

	if ($ERROR) {
		$MSG['check_error'] = $ERROR;
	}
	if ($file_tmp_name && file_exists($file_tmp_name)) { unlink($file_tmp_name); }

	return $MSG;
}


/**
 * アップロードフォーム作成
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param string $msg
 * @param integer $student_num
 * @return string HTML
 */
function upload_html($msg,$student_num) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if ($student_num) {
		$import_num = implode(",",$student_num);
		$import_student_html = "<table class=\"member_form\">\n";
		$import_student_html .= "<tr>\n";
		$import_student_html .= "<td class=\"member_form_menu\">教室名</td>\n";
		$import_student_html .= "<td class=\"member_form_menu\">ID</td>\n";
		$import_student_html .= "<td class=\"member_form_menu\">パスワード</td>\n";
		$import_student_html .= "<td class=\"member_form_menu\">生徒名</td>\n";
		$import_student_html .= "</tr>\n";
		foreach ($student_num AS $val) {
			$sql = "SELECT * FROM " . T_STUDENT ." student," .T_SCHOOL. " school" .
				" WHERE student.student_num='".$val."'" .
				" AND student.school_num=school.school_num" .
				" AND student.state='0' AND school.state='0' LIMIT 1;";
			if ($result = $cdb->query($sql)) {
				$list = $cdb->fetch_assoc($result);
			}

			$import_student_html .= "<tr>\n";
			$import_student_html .= "<td class=\"member_form_cell\">".$list['cram_school_name']."</td>\n";
			$import_student_html .= "<td class=\"member_form_cell\">".$list['student_id']."</td>\n";
			$import_student_html .= "<td class=\"member_form_cell\">".$list['def']."</td>\n";
			$import_student_html .= "<td class=\"member_form_cell\">".$list['student_name']."</td>\n";
			$import_student_html .= "</tr>\n";

		}
		$import_student_html .= "</table>\n";
	}
	$html .= "<br>\n";
	$html .= "{$msg}<br>\n";
	$html .= $import_student_html;

	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"action\" value=\"make_csv\">\n";
	$html .= "<input type=\"hidden\" name=\"student_num\" value=\"".$import_num."\"";
	$html .= "<td><input type=\"submit\" value=\"CSVエクスポート\"></td>\n";
	$html .= "</form>\n";
	$html .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"POST\">\n";
	$html .= "<input type=\"hidden\" name=\"mode\" value=\"set_course\">\n";
	$html .= "<input type=\"hidden\" name=\"enterprise_num\" value=\"".$_POST['enterprise_num']."\">\n";
	$html .= "<input type=\"hidden\" name=\"school_num\" value=\"".$_POST['school_num']."\">\n";
	$html .= "<input type=\"submit\" value=\"戻る\">\n";
	$html .= "</form>\n";

	return $html;
}


/**
 * データ確認処理
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $LINE
 * @return array エラーの場合
 */
function check_data($LINE){

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if ($LINE['school_code'] && $LINE['manager_id']) {
		$sql = "SELECT school.school_num FROM " . T_SCHOOL . " school," . T_MANAGER . " manager," . T_AUTHORITY . " authority" .
			" WHERE school.school_code='".$LINE['school_code']."'" .
			" AND school.school_num=authority.belong_num" .
			" AND manager.manager_num=authority.manager_num" .
			" AND manager.manager_id='".$LINE['manager_id']."'" .
			" AND authority.manager_level='4'" .
			" AND school.state!='1' AND manager.state!='1' AND authority.state!='1' LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$max = $cdb->num_rows($result);
		}
		if (!$max) { $ERROR[] = "教室コードと教室IDが一致しません。"; }

	}
	if ($LINE['student_id']) { $ERROR[] = "生徒IDは入力しないでください。"; }
	if (!$LINE['student_name']) { $ERROR[] = "名前が確認できません。"; }
	if (!$LINE['student_kana']) { $ERROR[] = "名前かなが確認できません。"; }
	if ($LINE['eng'] === "") { $ERROR[] = "英語が確認できません。"; }
	elseif (ereg("[^0-1]",$LINE['eng'])) { $ERROR[] = "英語の入力が不正です。"; }
	if ($LINE['jap'] === "") { $ERROR[] = "国語が確認できません。"; }
	elseif (ereg("[^0-1]",$LINE['jap'])) { $ERROR[] = "英語の入力が不正です。"; }
	if ($LINE['math'] === "") { $ERROR[] = "数学が確認できません。"; }
	elseif (ereg("[^0-1]",$LINE['math'])) { $ERROR[] = "数学の入力が不正です。"; }
	if ($LINE['student_sex'] === "") { $ERROR[] = "性別が確認できません。"; }
	elseif (ereg("[^1-2]",$LINE['student_sex'])) { $ERROR[] = "性別の入力が不正です。"; }
	if (!$LINE['birthday']) { $ERROR[] = "生年月日が確認できません。"; }
	elseif (!ereg("[0-9]{4,4}/[0-9]{2,2}/[0-9]{2,2}",$LINE['birthday'])) {
		$ERROR[] = "生年月日の入力が不正です。";
	}
	if (!$LINE['zip']) { $ERROR[] = "郵便番号が確認できません。"; }
	if (!$LINE['prf']) { $ERROR[] = "都道府県番号が確認できません。"; }
	if (!$LINE['city']) { $ERROR[] = "住所１が確認できません。"; }
	if ($LINE['status'] === "") { $ERROR[] = "ステータスが確認できません。"; }
	elseif (ereg("[^1-3]",$LINE['status'])) { $ERROR[] = "ステータスの入力が不正です。"; }
	if ($LINE['account'] === "") { $ERROR[] = "課金状態が確認できません。"; }
	elseif (ereg("[^0-1]",$LINE['account'])) { $ERROR[] = "課金状態の入力が不正です。"; }
	if ($LINE['use_area'] === "") { $ERROR[] = "利用場所が確認できません。"; }
	elseif (ereg("[^0-2]",$LINE['use_area'])) { $ERROR[] = "利用場所の入力が不正です。"; }
	if (!ereg("[0-9]{4,4}/[0-9]{2,2}/[0-9]{2,2}",$LINE['application'])&&$LINE['application']) {
		$ERROR[] = "申込日の入力が不正です。";
	}
	if (!ereg("[0-9]{4,4}/[0-9]{2,2}/[0-9]{2,2}",$LINE['admissionday'])&&$LINE['admissionday']) {
		$ERROR[] = "入塾日の入力が不正です。";
	}
	if (!ereg("[0-9]{4,4}/[0-9]{2,2}/[0-9]{2,2}",$LINE['secessionday'])&&$LINE['secessionday']) {
		$ERROR[] = "退塾日の入力が不正です。";
	}
	if (!$LINE['admissionroot']) { $ERROR[] = "入会経路が確認できません。"; }
	elseif (ereg("[^1-5]",$LINE['admissionroot'])) { $ERROR[] = "入会経路の入力が不正です。"; }
	if (!$LINE['guardian_id']) {
		$sql = "SELECT guardian_num FROM " . T_GUARDIAN .
			" WHERE guardian_id='".$LINE['guardian_id']."'" .
			" AND state='0' LIMIT 1;";
		if ($result = $cdb->query($sql)) {
			$max = $cdb->num_rows($result);
		}
		if (!$max) { $ERROR[] = "保護者IDは自動発行です。"; }
	}
	if (!$LINE['guardian_name']) { $ERROR[] = "保護者名が確認できません。"; }
	if (!$LINE['guardian_kana']) { $ERROR[] = "保護者名カナが確認できません。"; }
	if (!$LINE['guardian_email']) { $ERROR[] = "保護者メールアドレスが確認できません。"; }
	if (!$LINE['guardian_tel']) { $ERROR[] = "保護者電話番号が確認できません。"; }
	if (!$LINE['emergency_contact']) { $ERROR[] = "緊急連絡先が確認できません。"; }
	if ($LINE['login_email'] === "") { $ERROR[] = "ログイン通知が確認できません。"; }
	elseif (ereg("[^1-2]",$LINE['login_email'])) { $ERROR[] = "ログイン通知の入力が不正です。"; }
	if ($LINE['brother_flag'] === "") { $ERROR[] = "兄弟フラグが確認できません。"; }
	elseif (ereg("[^0-1]",$LINE['brother_flag'])) { $ERROR[] = "兄弟フラグの入力が不正です。"; }

	return $ERROR;
}


/**
 * データ一行登録
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param array $LINE
 * @return array
 */
function reggist_data($LINE) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	if ($LINE) {
		foreach ($LINE AS $key => $val) {
			if ($key == "school_code") { continue; }
			elseif ($key == "manager_id") { continue; }
			elseif ($key == "brother_flag") { continue; }
			elseif ($key == "account") { $INSERT_DATA3[$key] = $val; continue; }
			elseif ($key == "use_area") { $INSERT_DATA3[$key] = $val; continue; }
			elseif ($key == "eng") { if ($val == "1") { $course_num[] = "1"; } continue; }
			elseif ($key == "jap") { if ($val == "1") { $course_num[] = "2"; } continue; }
			elseif ($key == "math") { if ($val == "1") { $course_num[] = "3"; } continue; }

			if ($key == "zip"||$key == "prf"||$key == "city"||$key == "add1"||$key == "add2") {
				$INSERT_DATA2[$key] = "$val";
				if ($key == "zip") { $key = "guardian_zip"; }
				elseif ($key == "prf") { $key = "guardian_prf"; }
				elseif ($key == "city") { $key = "guardian_city"; }
				elseif ($key == "add1") { $key = "guardian_add1"; }
				elseif ($key == "add2") { $key = "guardian_add2"; }
				$INSERT_DATA[$key] = "$val";
			} elseif ($key == "guardian_name"||$key == "guardian_kana"||$key == "guardian_id"||$key == "guardian_pass"||$key == "guardian_email"||
				$key == "guardian_mobile_email"||$key == "guardian_tel"||$key == "guardian_mobile"||$key == "guardian_fax"||$key == "emergency_contact"||
				$key == "login_email"||$key == "guardian_remarks") {
				$INSERT_DATA[$key] = "$val";
			} elseif ($key == "student_id"||$key == "student_name"||$key == "student_kana"||$key == "student_pass"||$key == "student_email"||
				$key == "student_mobile_email"||$key == "birthday"||$key == "student_sex"||$key == "school_name"||$key == "brother_num"||
				$key == "tel"||$key == "fax"||$key == "application"||$key == "admissionday"||$key == "status"||
				$key == "secessionday"||$key == "admissionroot"||$key == "student_remarks"||$key == "student_remarks2"||$key == "student_remarks3") {
				$INSERT_DATA2[$key] = "$val";
			}

		}
	} else {
		$INSERT_DATA[error_msg] = "";
	}
/*
pre("------------1");
pre($INSERT_DATA);
pre("------------2");
pre($INSERT_DATA2);
pre("------------3");
pre($INSERT_DATA3);
exit;
*/

	if ($_POST['school_num']==="") {
		if ($LINE['school_code'] && $LINE['manager_id']) {
			$sql = "SELECT school.enterprise_num,school.school_num FROM " . T_SCHOOL . " school," . T_MANAGER . " manager," . T_AUTHORITY . " authority" .
				" WHERE school.school_code='".$LINE['school_code']."'" .
				" AND school.school_num=authority.belong_num" .
				" AND manager.manager_num=authority.manager_num" .
				" AND manager.manager_id='".$LINE['manager_id']."'" .
				" AND authority.manager_level='4'" .
				" AND school.state!='1' AND manager.state!='1' AND authority.state!='1' LIMIT 1;";
		} elseif (!$LINE['school_code'] && $LINE['manager_id']) {
			$sql = "SELECT school.enterprise_num,school.school_num FROM " . T_SCHOOL . " school," . T_MANAGER . " manager," . T_AUTHORITY . " authority" .
				" WHERE manager.manager_id='".$LINE['manager_id']."'" .
				" AND manager.manager_num=authority.manager_num" .
				" AND school.school_num=authority.belong_num" .
				" AND authority.manager_level='4'" .
				" AND school.state!='1' AND manager.state!='1' AND authority.state!='1' LIMIT 1;";
		} elseif ($LINE['school_code'] && !$LINE['manager_id']) {
			$sql = "SELECT enterprise_num,school_num FROM " . T_SCHOOL .
				" WHERE state!='1'" .
				" AND school_code='".$LINE['school_code']."'" .
				" LIMIT 1;";
		} else {
			return;
		}
		if ($result = $cdb->query($sql)) {
			$list = $cdb->fetch_assoc($result);
			$enterprise_num = $list['enterprise_num'];
			$school_num = $list['school_num'];
		}
	} else {
		$enterprise_num = $_POST['enterprise_num'];
		$school_num = $_POST['school_num'];
	}

	if(!$_SESSION['guardian_num']) {
		if ($LINE['guardian_id']) {
			$sql = "SELECT guardian_num FROM " . T_GUARDIAN .
				" WHERE guardian_id='".$LINE['guardian_id']."'" .
				" AND state='0' LIMIT 1;";
			if ($result = $cdb->query($sql)) {
				$list = $cdb->fetch_assoc($result);
			}
			$INSERT_DATA['enterprise_num'] = $enterprise_num;
			$INSERT_DATA['school_num'] = $school_num;
			$INSERT_DATA['g_update_date'] = "now()";
			$guardian_num = $list['guardian_num'];
			$where = " WHERE guardian_num='".$guardian_num."'";
			$ERROR = $cdb->update(T_GUARDIAN,$INSERT_DATA,$where);
		} else {
			$INSERT_DATA['enterprise_num'] = $enterprise_num;
			$INSERT_DATA['school_num'] = $school_num;
			$INSERT_DATA['g_regist_date'] = "now()";
			$INSERT_DATA['g_update_date'] = "now()";
			$INSERT_DATA['state'] = "0";
			$ERROR = $cdb->insert(T_GUARDIAN,$INSERT_DATA);
			$guardian_num = $cdb->insert_id();
			if (!$ERROR && $guardian_num) {
				unset($INSERT_DATA);
				$INSERT_DATA['guardian_id'] = make_guardian_id();
				$INSERT_DATA['def'] = make_pass();
				$INSERT_DATA['guardian_pass'] = md5($INSERT_DATA['def']);
				$where = " WHERE guardian_num='".$guardian_num."'";

				$ERROR = $cdb->update(T_GUARDIAN,$INSERT_DATA,$where);
			}
		}
	} else {
		$guardian_num = $_SESSION['guardian_num'];
	}
	if ($LINE['brother_flag'] == "1") {
		$_SESSION['guardian_num'] = $guardian_num;
	} else {
		unset($_SESSION['guardian_num']);
	}

	$INSERT_DATA2['guardian_num'] = $guardian_num;
	$INSERT_DATA2['enterprise_num'] = $enterprise_num;
	$INSERT_DATA2['school_num'] = $school_num;
	$INSERT_DATA2['s_regist_date'] = "now()";
	$INSERT_DATA2['s_update_date'] = "now()";
	$INSERT_DATA2['state'] = "0";

	$ERROR = $cdb->insert(T_STUDENT,$INSERT_DATA2);
	$student_num = $cdb->insert_id();
	if (!$ERROR && $student_num) {
		unset($INSERT_DATA2);
		$INSERT_DATA2['student_id'] = make_id($student_num);
		$INSERT_DATA2['def'] = make_pass();
		$INSERT_DATA2['student_pass'] = md5($INSERT_DATA2['def']);
		$where = " WHERE student_num='".$student_num."'";

		$ERROR = $cdb->update(T_STUDENT,$INSERT_DATA2,$where);
	}

	$INSERT_DATA3['student_num'] = $student_num;
	$INSERT_DATA3['regist_time'] = "now()";
	$INSERT_DATA3['state'] = "0";

	$ERROR = $cdb->insert(T_STUDENT_ACCOUNT,$INSERT_DATA3);

	$INSERT_DATA4['student_num'] = $student_num;
	if (is_array($course_num)) {
		foreach ($course_num as $val){
			$INSERT_DATA4['enterprise_num'] = $enterprise_num;
			$INSERT_DATA4['school_num'] = $school_num;
			$INSERT_DATA4['state'] = "0";
			$INSERT_DATA4['course_num'] = $val;
			$INSERT_DATA4['regist_time'] = "now()";
			$INSERT_DATA4['update_time'] = "now()";

			$ERROR = $cdb->insert(T_USE_COURSE,$INSERT_DATA4);
		}
	}
/*
pre($INSERT_DATA);
pre($INSERT_DATA2);
pre($INSERT_DATA3);
pre($INSERT_DATA4);
*/
	return array($student_num,$ERROR);

}


/**
 * 保護者IDを作成
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string
 */
function make_guardian_id() {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$BASE_ID = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',
		'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
		'0','1','2','3','4','5','6','7','8','9');

	srand((double) microtime()*1000000);
	for($i=0;$i<10;$i++) {
		$new_id .= $BASE_ID[rand(0,61)];
	}

	$sql = "SELECT guardian_id FROM " . T_GUARDIAN .
		" WHERE guardian_id='".$new_id."'" .
		" AND state='0' LIMIT 1;";
	if ($result = $cdb->query($sql)) {
		$max = $cdb->num_rows($result);
	}
	if ($max) { $new_id = ""; }
	if ($new_id == "") { $new_id = make_guardian_id(); }

	return $new_id;
}


/**
 * パスワード作成
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @return string
 */
function make_pass() {
	$BASE_ID = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',
		'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
		'0','1','2','3','4','5','6','7','8','9');

	srand((double) microtime()*1000000);
	for($i=0;$i<10;$i++) {
		$new_id .= $BASE_ID[rand(0,61)];
	}

	return $new_id;
}


/**
 * CSVの作成・出力
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 */
function make_csv() {

	global $L_STUDENT_STATUS,$L_ACCOUNT,$L_USE_AREA;

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$filename = "student_import.txt";

	$sql  = "CREATE TEMPORARY TABLE account " .
		"SELECT student_num,account,use_area FROM " .
		T_STUDENT_ACCOUNT .
		" WHERE state='0'";
	if (!$cdb->exec_query($sql)) { echo $sql."<br><br>"; }
//        $sql = "SELECT student.student_num,student.birthday,student.student_id,student.student_name,student.school_name,".
		$sql = "SELECT student.student_num,student.birthday,student.student_id,student.student_name,". convertDecryptField('student.school_name') .",". // kaopiz 2020/08/20 Encoding
			"student.prf,student.city,student.add1,student.add2,student.tel,student.status,DATE(student.s_regist_date) AS s_regist_date,DATE(student.first_login) AS first_login,student.def AS student_def," .
			"enterprise.enterprise_num,enterprise.enterprise_name,enterprise.enterprise_code," .
			"school.school_num,school.cram_school_name,school.school_code,account.account,account.use_area," .
			"guardian.guardian_id,guardian.guardian_name,guardian.guardian_city,guardian.guardian_add1,guardian.guardian_add2,guardian.guardian_tel,guardian.def AS guardian_def" .
			" FROM " . T_STUDENT . " student," . T_ENTERPRISE . " enterprise," . T_SCHOOL . " school," . T_GUARDIAN . " guardian";
		if ($_SESSION[sub_session][s_course]) { $sql .= ", " . T_USE_COURSE . " use_course"; }
		$sql .= " LEFT JOIN account account ON account.student_num=student.student_num" .
			" WHERE student.state!='1'" .
			" AND enterprise.enterprise_num=student.enterprise_num" .
			" AND school.school_num=student.school_num" .
			" AND student.guardian_num=guardian.guardian_num" .
			" AND student.student_num IN ($_POST[student_num])" .
			" ORDER BY student.student_id";

	header("Cache-Control: public");
	header("Pragma: public");
	header("Content-disposition: attachment;filename=$filename");
	if (stristr($HTTP_USER_AGENT, "MSIE")) {
		header("Content-Type: text/octet-stream");
	} else {
		header("Content-Type: application/octet-stream;");
	}

	//	head line (一行目)
	$csv_head .= "\"生徒ID\"\t";
	$csv_head .= "\"生徒初期パスワード\"\t";
	$csv_head .= "\"生徒名\"\t";
	$csv_head .= "\"ID発行日\"\t";
	$csv_head .= "\"初回ログイン日\"\t";
	$csv_head .= "\"ステータス\"\t";
	$csv_head .= "\"課金状態\"\t";
	$csv_head .= "\"塾内/塾外\"\t";
	$csv_head .= "\"英語\"\t";
	$csv_head .= "\"国語\"\t";
	$csv_head .= "\"数学\"\t";
	$csv_head .= "\"住所\"\t";
	$csv_head .= "\"TEL\"\t";
	$csv_head .= "\"法人名\"\t";
	$csv_head .= "\"法人コード\"\t";
	$csv_head .= "\"教室名\"\t";
	$csv_head .= "\"教室コード\"\t";
	$csv_head .= "\"保護者ID\"\t";
	$csv_head .= "\"保護者初期パスワード\"\t";
	$csv_head .= "\"保護者名\"\t";
	$csv_head .= "\"保護者住所\"\t";
	$csv_head .= "\"保護者TEL\"\t\n";

	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			foreach($list as $key => $val) { $list[$key] = replace_decode($val); }
			list($eng,$jap,$math) = check_use_course($list[student_num]);
			$grade = check_grade($list[birthday]);
			if ($eng) { $eng = "○"; } else { $eng = "--"; }
			if ($jap) { $jap = "○"; } else { $jap = "--"; }
			if ($math) { $math = "○"; } else { $math = "--"; }
			if (!$list[account]) { $list[account] = 0; }
			if (!$list[use_area]) { $list[use_area] = 0; }

			$csv_head .= "\"".$list[student_id]."\"\t";
			$csv_head .= "\"".$list[student_def]."\"\t";
			$csv_head .= "\"".$list[student_name]."\"\t";
			$csv_head .= "\"".str_replace("-","/",$list[s_regist_date])."\"\t";
			$csv_head .= "\"".str_replace("-","/",$list[first_login])."\"\t";
			$csv_head .= "\"".$L_STUDENT_STATUS[$list[status]]."\"\t";
			$csv_head .= "\"".$L_ACCOUNT[$list[account]]."\"\t";
			$csv_head .= "\"".$L_USE_AREA[$list[use_area]]."\"\t";
			$csv_head .= "\"".$eng."\"\t";
			$csv_head .= "\"".$jap."\"\t";
			$csv_head .= "\"".$math."\"\t";
			$csv_head .= "\"$list[city] $list[add1] $list[add2]\"\t";
			$csv_head .= "\"".$list[tel]."\"\t";
			$csv_head .= "\"".$list[enterprise_name]."\"\t";
			$csv_head .= "\"".$list[enterprise_code]."\"\t";
			$csv_head .= "\"".$list[cram_school_name]."\"\t";
			$csv_head .= "\"".$list[school_code]."\"\t";
			$csv_head .= "\"".$list[guardian_id]."\"\t";
			$csv_head .= "\"".$list[guardian_def]."\"\t";
			$csv_head .= "\"".$list[guardian_name]."\"\t";
			$csv_head .= "\"$list[guardian_city] $list[guardian_add1] $list[guardian_add2]\"\t";
			$csv_head .= "\"".$list[guardian_tel]."\"\t\n";
		}
	}

	$csv_head = mb_convert_encoding($csv_head,"sjis-win","UTF-8");
	$csv_line = mb_convert_encoding($csv_line,"sjis-win","UTF-8");

	echo $csv_head;
	echo $csv_line;

	exit;
}

/**
 * 生徒はどんなコースを使えるか確認
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param integer $student_num
 * @return array
 */
function check_use_course($student_num) {

	// DB接続オブジェクト
	$cdb = $GLOBALS['cdb'];

	$sql = "SELECT course_num FROM " . T_USE_COURSE .
		" WHERE student_num='$student_num' AND state!='1'";
	if ($result = $cdb->query($sql)) {
		while ($list = $cdb->fetch_assoc($result)) {
			if ($list[course_num] == 1) { $eng = "1"; }
			if ($list[course_num] == 2) { $jap = "1"; }
			if ($list[course_num] == 3) { $math = "1"; }
		}
	}
	return array($eng,$jap,$math);
}

/**
 * 生徒の記念日から学年を計算する
 *
 * AC:[A]管理者 UC1:[M01]Core管理機能.
 *
 * @author Azet
 * @param string $birthday
 * @return string
 */
function check_grade($birthday) {
	list($year,$month,$day) = explode("-",$birthday);

	$age = 0;
	if ($year > 1950) {
		$age = date('Y') - $year;
		if ($month < 4) { $age++; }
	}

	if ($age > 6 && $age <=12) {
		$grade = "小".($age-6);
	} elseif ($age >= 13 && $age <=15) {
		$grade = "中".($age-12);
	} elseif ($age >= 16 && $age <=18) {
		$grade = "高".($age-15);
	} elseif ($age >= 19) {
		$grade = "大".($age-18);
	} else {
		$grade = "--";
	}

	return $grade;
}
?>