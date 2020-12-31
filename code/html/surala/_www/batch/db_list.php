<?PHP
/*

	DB & SERVER LIST

*/

$L_DB['list']['NAME'] = "サーバを選択して下さい";


//	DB LIST
// upd start haegawa 2018/05/18 参照先DB切替
//	本番バッチWebDB
// $L_DB['srlbhw11']['NAME'] = '本番バッチWebDB';
// $L_DB['srlbhw11']['DBHOST'] = 'srnprodbatchdb1.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
// $L_DB['srlbhw11']['DBUSER'] = 'axxhb01';
// $L_DB['srlbhw11']['DBPASSWD'] = 'hb_01axx';
// $L_DB['srlbhw11']['DBNAME'] = 'SRLBH01';
// $L_DB['srlbhw11']['DBPORT'] = '3306';
if (preg_match("/data\/home\/contents/", $_SERVER['SCRIPT_NAME'])){	// バッチから参照するときはHTTP_HOSTが空の為SCRIPT_NAMEで判断
	//	本番バッチWebDB
	$L_DB['srlbhw11']['NAME'] = '本番バッチWebDB';
	$L_DB['srlbhw11']['DBHOST'] = 'srnprodbatchdb1.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
	$L_DB['srlbhw11']['DBUSER'] = 'axxhb01';
	$L_DB['srlbhw11']['DBPASSWD'] = 'hb_01axx';
	$L_DB['srlbhw11']['DBNAME'] = 'SRLBH01';
	$L_DB['srlbhw11']['DBPORT'] = '3306';
} else {
	//	本番バッチWebDB
	$L_DB['srlbhw11']['NAME'] = '本番バッチWebDB';
	$L_DB['srlbhw11']['DBHOST'] = 'srnprodbatchdb1.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
	$L_DB['srlbhw11']['DBUSER'] = 'axxhb01';
	$L_DB['srlbhw11']['DBPASSWD'] = 'hb_01axx';
	$L_DB['srlbhw11']['DBNAME'] = 'SRLBH99';
	$L_DB['srlbhw11']['DBPORT'] = '3306';
}
// upd end haegawa 2018/05/18

//	検証統合DB
$L_DB['srlctd01']['NAME'] = '検証統合DB';
$L_DB['srlctd01']['DBHOST'] = 'srnstgcoredb0.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
$L_DB['srlctd01']['DBUSER'] = 'axxse01';
$L_DB['srlctd01']['DBPASSWD'] = 'se_01axx';
$L_DB['srlctd01']['DBNAME'] = 'SRLEH01';
$L_DB['srlctd01']['DBPORT'] = '3306';

//	検証分散DB#1
$L_DB['srlctd0201']['NAME'] = '検証分散DB#1';
$L_DB['srlctd0201']['DBHOST'] = 'srnstgcoredb1.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
$L_DB['srlctd0201']['DBUSER'] = 'axxsc01';
$L_DB['srlctd0201']['DBPASSWD'] = 'sc_01axx';
$L_DB['srlctd0201']['DBNAME'] = 'SRLCH01';
$L_DB['srlctd0201']['DBPORT'] = '3306';

//	検証分散DB#2
$L_DB['srlctd0202']['NAME'] = '検証分散DB#2';
$L_DB['srlctd0202']['DBHOST'] = 'srnstgcoredb2.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
$L_DB['srlctd0202']['DBUSER'] = 'axxsc01';
$L_DB['srlctd0202']['DBPASSWD'] = 'sc_01axx';
$L_DB['srlctd0202']['DBNAME'] = 'SRLCH02';
$L_DB['srlctd0202']['DBPORT'] = '3306';

//	検証分散DB#3
$L_DB['srlctd0203']['NAME'] = '検証分散DB#3';
$L_DB['srlctd0203']['DBHOST'] = 'srnstgcoredb3.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
$L_DB['srlctd0203']['DBUSER'] = 'axxsc01';
$L_DB['srlctd0203']['DBPASSWD'] = 'sc_01axx';
$L_DB['srlctd0203']['DBNAME'] = 'SRLCH03';
$L_DB['srlctd0203']['DBPORT'] = '3306';

//	検証分散DB#4
$L_DB['srlctd0204']['NAME'] = '検証分散DB#4';
$L_DB['srlctd0204']['DBHOST'] = 'srnstgcoredb4.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
$L_DB['srlctd0204']['DBUSER'] = 'axxsc01';
$L_DB['srlctd0204']['DBPASSWD'] = 'sc_01axx';
$L_DB['srlctd0204']['DBNAME'] = 'SRLCH04';
$L_DB['srlctd0204']['DBPORT'] = '3306';

//	検証分散DB#5
$L_DB['srlctd0305']['NAME'] = '検証分散DB#5';
$L_DB['srlctd0305']['DBHOST'] = 'srnstgcoredb5.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
$L_DB['srlctd0305']['DBUSER'] = 'axxsc01';
$L_DB['srlctd0305']['DBPASSWD'] = 'sc_01axx';
$L_DB['srlctd0305']['DBNAME'] = 'SRLCH05';
$L_DB['srlctd0305']['DBPORT'] = '3306';

//	検証分散DB#6
$L_DB['srlctd0206']['NAME'] = '検証分散DB#6';
$L_DB['srlctd0206']['DBHOST'] = 'srnstgcoredb6.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
$L_DB['srlctd0206']['DBUSER'] = 'axxsc01';
$L_DB['srlctd0206']['DBPASSWD'] = 'sc_01axx';
$L_DB['srlctd0206']['DBNAME'] = 'SRLCH06';
$L_DB['srlctd0206']['DBPORT'] = '3306';

//	検証分散DB#7
$L_DB['srlctd0207']['NAME'] = '検証分散DB#7';
$L_DB['srlctd0207']['DBHOST'] = 'srnstgcoredb7.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
$L_DB['srlctd0207']['DBUSER'] = 'axxsc01';
$L_DB['srlctd0207']['DBPASSWD'] = 'sc_01axx';
$L_DB['srlctd0207']['DBNAME'] = 'SRLCH07';
$L_DB['srlctd0207']['DBPORT'] = '3306';

//	検証分散DB#8
$L_DB['srlctd0208']['NAME'] = '検証分散DB#8';
$L_DB['srlctd0208']['DBHOST'] = 'srnstgcoredb8.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
$L_DB['srlctd0208']['DBUSER'] = 'axxsc01';
$L_DB['srlctd0208']['DBPASSWD'] = 'sc_01axx';
$L_DB['srlctd0208']['DBNAME'] = 'SRLCH08';
$L_DB['srlctd0208']['DBPORT'] = '3306';

// add start oda 2020/08/15 スケールアウト
//	検証分散DB#9
$L_DB['srlctd0209']['NAME'] = '検証分散DB#9';
$L_DB['srlctd0209']['DBHOST'] = 'srnstgcoredb9.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
$L_DB['srlctd0209']['DBUSER'] = 'axxsc01';
$L_DB['srlctd0209']['DBPASSWD'] = 'sc_01axx';
$L_DB['srlctd0209']['DBNAME'] = 'SRLCH09';
$L_DB['srlctd0209']['DBPORT'] = '3306';

//	検証分散DB#10
$L_DB['srlctd0210']['NAME'] = '検証分散DB#10';
$L_DB['srlctd0210']['DBHOST'] = 'srnstgcoredb10.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
$L_DB['srlctd0210']['DBUSER'] = 'axxsc01';
$L_DB['srlctd0210']['DBPASSWD'] = 'sc_01axx';
$L_DB['srlctd0210']['DBNAME'] = 'SRLCH10';
$L_DB['srlctd0210']['DBPORT'] = '3306';

//	検証分散DB#11
$L_DB['srlctd0211']['NAME'] = '検証分散DB#11';
$L_DB['srlctd0211']['DBHOST'] = 'srnstgcoredb11.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
$L_DB['srlctd0211']['DBUSER'] = 'axxsc01';
$L_DB['srlctd0211']['DBPASSWD'] = 'sc_01axx';
$L_DB['srlctd0211']['DBNAME'] = 'SRLCH11';
$L_DB['srlctd0211']['DBPORT'] = '3306';

//	検証分散DB#12
$L_DB['srlctd0212']['NAME'] = '検証分散DB#12';
$L_DB['srlctd0212']['DBHOST'] = 'srnstgcoredb12.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
$L_DB['srlctd0212']['DBUSER'] = 'axxsc01';
$L_DB['srlctd0212']['DBPASSWD'] = 'sc_01axx';
$L_DB['srlctd0212']['DBNAME'] = 'SRLCH12';
$L_DB['srlctd0212']['DBPORT'] = '3306';

//	検証分散DB#13
$L_DB['srlctd0213']['NAME'] = '検証分散DB#13';
$L_DB['srlctd0213']['DBHOST'] = 'srnstgcoredb13.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
$L_DB['srlctd0213']['DBUSER'] = 'axxsc01';
$L_DB['srlctd0213']['DBPASSWD'] = 'sc_01axx';
$L_DB['srlctd0213']['DBNAME'] = 'SRLCH13';
$L_DB['srlctd0213']['DBPORT'] = '3306';

// //	検証分散DB#14
// $L_DB['srlctd0214']['NAME'] = '検証分散DB#14';
// $L_DB['srlctd0214']['DBHOST'] = 'srnstgcoredb14.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
// $L_DB['srlctd0214']['DBUSER'] = 'axxsc01';
// $L_DB['srlctd0214']['DBPASSWD'] = 'sc_01axx';
// $L_DB['srlctd0214']['DBNAME'] = 'SRLCH14';
// $L_DB['srlctd0214']['DBPORT'] = '3306';

// //	検証分散DB#15
// $L_DB['srlctd0215']['NAME'] = '検証分散DB#15';
// $L_DB['srlctd0215']['DBHOST'] = 'srnstgcoredb15.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
// $L_DB['srlctd0215']['DBUSER'] = 'axxsc01';
// $L_DB['srlctd0215']['DBPASSWD'] = 'sc_01axx';
// $L_DB['srlctd0215']['DBNAME'] = 'SRLCH15';
// $L_DB['srlctd0215']['DBPORT'] = '3306';
// add end oda 2020/08/15 スケールアウト


// upd start haegawa 2018/05/18 参照先DB切替
// // 検証バッチWebDB
// $L_DB['srlbtw21']['NAME'] = '検証バッチWebDB';
// $L_DB['srlbtw21']['DBHOST'] = 'srnstgbatchdb1.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
// $L_DB['srlbtw21']['DBUSER'] = 'axxsb01';
// $L_DB['srlbtw21']['DBPASSWD'] = 'sb_01axx';
// $L_DB['srlbtw21']['DBNAME'] = 'SRLBS01';
// $L_DB['srlbtw21']['DBPORT'] = '3306';
 // すらら様開発環境
if (preg_match("/data\/home\/contents/", $_SERVER['SCRIPT_NAME'])){	// バッチから参照するときはHTTP_HOSTが空の為SCRIPT_NAMEで判断
	// 検証バッチWebDB
	$L_DB['srlbtw21']['NAME'] = '検証バッチWebDB';
	$L_DB['srlbtw21']['DBHOST'] = 'srnstgbatchdb1.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
	$L_DB['srlbtw21']['DBUSER'] = 'axxsb01';
	$L_DB['srlbtw21']['DBPASSWD'] = 'sb_01axx';
	$L_DB['srlbtw21']['DBNAME'] = 'SRLBS01';
	$L_DB['srlbtw21']['DBPORT'] = '3306';
} else {
	// 検証バッチWebDB
	$L_DB['srlbtw21']['NAME'] = '検証バッチWebDB';
	$L_DB['srlbtw21']['DBHOST'] = 'srnstgbatchdb1.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
	$L_DB['srlbtw21']['DBUSER'] = 'axxsb01';
	$L_DB['srlbtw21']['DBPASSWD'] = 'sb_01axx';
	$L_DB['srlbtw21']['DBNAME'] = 'SRLBS99';
	$L_DB['srlbtw21']['DBPORT'] = '3306';
}
// upd end haegawa 2018/05/18

//	開発コアDB
$L_DB['srlctw11']['NAME'] = '開発コアDB';
$L_DB['srlctw11']['DBHOST'] = 'srnmaintenancedevdb1.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
$L_DB['srlctw11']['DBUSER'] = 'axxkc01';
$L_DB['srlctw11']['DBPASSWD'] = 'kc_01axx';
$L_DB['srlctw11']['DBNAME'] = 'CONTENTS160';
$L_DB['srlctw11']['DBPORT'] = '3306';

//	開発コアDB CONTENTS用
$L_DB['srlctd03cts']['NAME'] = '開発コアDB CONTENTS';
$L_DB['srlctd03cts']['DBHOST'] = 'srnmaintenancedevdb1.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
$L_DB['srlctd03cts']['DBUSER'] = 'axxkc01';
$L_DB['srlctd03cts']['DBPASSWD'] = 'kc_01axx';
$L_DB['srlctd03cts']['DBNAME'] = 'CONTENTS';
$L_DB['srlctd03cts']['DBPORT'] = '3306';


//	開発DB#1
$L_DB['srlctw1101']['NAME'] = '開発DB#1';
$L_DB['srlctw1101']['DBHOST'] = 'srnmaintenancedevdb1.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
$L_DB['srlctw1101']['DBUSER'] = 'axxkc01';
$L_DB['srlctw1101']['DBPASSWD'] = 'kc_01axx';
$L_DB['srlctw1101']['DBNAME'] = 'SRLCK01';
$L_DB['srlctw1101']['DBPORT'] = '3306';

//	開発DB#2
$L_DB['srlctw1102']['NAME'] = '開発DB#2';
$L_DB['srlctw1102']['DBHOST'] = 'srnmaintenancedevdb1.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
$L_DB['srlctw1102']['DBUSER'] = 'axxkc01';
$L_DB['srlctw1102']['DBPASSWD'] = 'kc_01axx';
$L_DB['srlctw1102']['DBNAME'] = 'SRLCK02';
$L_DB['srlctw1102']['DBPORT'] = '3306';

//	開発DB#3
$L_DB['srlctw1103']['NAME'] = '開発DB#3';
$L_DB['srlctw1103']['DBHOST'] = 'srnmaintenancedevdb1.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
$L_DB['srlctw1103']['DBUSER'] = 'axxkc01';
$L_DB['srlctw1103']['DBPASSWD'] = 'kc_01axx';
$L_DB['srlctw1103']['DBNAME'] = 'SRLCK03';
$L_DB['srlctw1103']['DBPORT'] = '3306';

//	開発DB#4
$L_DB['srlctw1104']['NAME'] = '開発DB#4';
$L_DB['srlctw1104']['DBHOST'] = 'srnmaintenancedevdb1.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
$L_DB['srlctw1104']['DBUSER'] = 'axxkc01';
$L_DB['srlctw1104']['DBPASSWD'] = 'kc_01axx';
$L_DB['srlctw1104']['DBNAME'] = 'SRLCK04';
$L_DB['srlctw1104']['DBPORT'] = '3306';

//	開発DB#5
$L_DB['srlctw1105']['NAME'] = '開発DB#5';
$L_DB['srlctw1105']['DBHOST'] = 'srnmaintenancedevdb1.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
$L_DB['srlctw1105']['DBUSER'] = 'axxkc01';
$L_DB['srlctw1105']['DBPASSWD'] = 'kc_01axx';
$L_DB['srlctw1105']['DBNAME'] = 'SRLCK05';
$L_DB['srlctw1105']['DBPORT'] = '3306';

//	開発DB#6
$L_DB['srlctw1106']['NAME'] = '開発DB#6';
$L_DB['srlctw1106']['DBHOST'] = 'srnmaintenancedevdb1.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
$L_DB['srlctw1106']['DBUSER'] = 'axxkc01';
$L_DB['srlctw1106']['DBPASSWD'] = 'kc_01axx';
$L_DB['srlctw1106']['DBNAME'] = 'SRLCK06';
$L_DB['srlctw1106']['DBPORT'] = '3306';

//	開発DB#7
$L_DB['srlctw1107']['NAME'] = '開発DB#7';
$L_DB['srlctw1107']['DBHOST'] = 'srnmaintenancedevdb1.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
$L_DB['srlctw1107']['DBUSER'] = 'axxkc01';
$L_DB['srlctw1107']['DBPASSWD'] = 'kc_01axx';
$L_DB['srlctw1107']['DBNAME'] = 'SRLCK07';
$L_DB['srlctw1107']['DBPORT'] = '3306';

//	開発DB#8
$L_DB['srlctw1108']['NAME'] = '開発DB#8';
$L_DB['srlctw1108']['DBHOST'] = 'srnmaintenancedevdb1.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
$L_DB['srlctw1108']['DBUSER'] = 'axxkc01';
$L_DB['srlctw1108']['DBPASSWD'] = 'kc_01axx';
$L_DB['srlctw1108']['DBNAME'] = 'SRLCK08';
$L_DB['srlctw1108']['DBPORT'] = '3306';

// add start oda 2020/08/15 スケールアウト
//	開発DB#9
$L_DB['srlctw1109']['NAME'] = '開発DB#9';
$L_DB['srlctw1109']['DBHOST'] = 'srnmaintenancedevdb1.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
$L_DB['srlctw1109']['DBUSER'] = 'axxkc01';
$L_DB['srlctw1109']['DBPASSWD'] = 'kc_01axx';
$L_DB['srlctw1109']['DBNAME'] = 'SRLCK09';
$L_DB['srlctw1109']['DBPORT'] = '3306';

//	開発DB#10
$L_DB['srlctw1110']['NAME'] = '開発DB#10';
$L_DB['srlctw1110']['DBHOST'] = 'srnmaintenancedevdb1.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
$L_DB['srlctw1110']['DBUSER'] = 'axxkc01';
$L_DB['srlctw1110']['DBPASSWD'] = 'kc_01axx';
$L_DB['srlctw1110']['DBNAME'] = 'SRLCK10';
$L_DB['srlctw1110']['DBPORT'] = '3306';

//	開発DB#11
$L_DB['srlctw1111']['NAME'] = '開発DB#11';
$L_DB['srlctw1111']['DBHOST'] = 'srnmaintenancedevdb1.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
$L_DB['srlctw1111']['DBUSER'] = 'axxkc01';
$L_DB['srlctw1111']['DBPASSWD'] = 'kc_01axx';
$L_DB['srlctw1111']['DBNAME'] = 'SRLCK11';
$L_DB['srlctw1111']['DBPORT'] = '3306';

//	開発DB#12
$L_DB['srlctw1112']['NAME'] = '開発DB#12';
$L_DB['srlctw1112']['DBHOST'] = 'srnmaintenancedevdb1.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
$L_DB['srlctw1112']['DBUSER'] = 'axxkc01';
$L_DB['srlctw1112']['DBPASSWD'] = 'kc_01axx';
$L_DB['srlctw1112']['DBNAME'] = 'SRLCK12';
$L_DB['srlctw1112']['DBPORT'] = '3306';

//	開発DB#13
$L_DB['srlctw1113']['NAME'] = '開発DB#13';
$L_DB['srlctw1113']['DBHOST'] = 'srnmaintenancedevdb1.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
$L_DB['srlctw1113']['DBUSER'] = 'axxkc01';
$L_DB['srlctw1113']['DBPASSWD'] = 'kc_01axx';
$L_DB['srlctw1113']['DBNAME'] = 'SRLCK13';
$L_DB['srlctw1113']['DBPORT'] = '3306';

// //	開発DB#14
// $L_DB['srlctw1114']['NAME'] = '開発DB#14';
// $L_DB['srlctw1114']['DBHOST'] = 'srnmaintenancedevdb1.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
// $L_DB['srlctw1114']['DBUSER'] = 'axxkc01';
// $L_DB['srlctw1114']['DBPASSWD'] = 'kc_01axx';
// $L_DB['srlctw1114']['DBNAME'] = 'SRLCK14';
// $L_DB['srlctw1114']['DBPORT'] = '3306';

// //	開発DB#15
// $L_DB['srlctw1115']['NAME'] = '開発DB#15';
// $L_DB['srlctw1115']['DBHOST'] = 'srnmaintenancedevdb1.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
// $L_DB['srlctw1115']['DBUSER'] = 'axxkc01';
// $L_DB['srlctw1115']['DBPASSWD'] = 'kc_01axx';
// $L_DB['srlctw1115']['DBNAME'] = 'SRLCK15';
// $L_DB['srlctw1115']['DBPORT'] = '3306';
// add end oda 2020/08/15 スケールアウト

//	開発DB#1
$L_DB['srlctd3101']['NAME'] = '開発DB#1';
$L_DB['srlctd3101']['DBHOST'] = 'srnmaintenancedevdb1.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
$L_DB['srlctd3101']['DBUSER'] = 'axxkc01';
$L_DB['srlctd3101']['DBPASSWD'] = 'kc_01axx';
$L_DB['srlctd3101']['DBNAME'] = 'SRLCK01';
$L_DB['srlctd3101']['DBPORT'] = '3306';

//	開発DB#2
$L_DB['srlctd3102']['NAME'] = '開発DB#2';
$L_DB['srlctd3102']['DBHOST'] = 'srnmaintenancedevdb1.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
$L_DB['srlctd3102']['DBUSER'] = 'axxkc01';
$L_DB['srlctd3102']['DBPASSWD'] = 'kc_01axx';
$L_DB['srlctd3102']['DBNAME'] = 'SRLCK02';
$L_DB['srlctd3102']['DBPORT'] = '3306';

//	開発DB#3
$L_DB['srlctd3103']['NAME'] = '開発DB#3';
$L_DB['srlctd3103']['DBHOST'] = 'srnmaintenancedevdb1.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
$L_DB['srlctd3103']['DBUSER'] = 'axxkc01';
$L_DB['srlctd3103']['DBPASSWD'] = 'kc_01axx';
$L_DB['srlctd3103']['DBNAME'] = 'SRLCK03';
$L_DB['srlctd3103']['DBPORT'] = '3306';

//	開発DB#4
$L_DB['srlctd3104']['NAME'] = '開発DB#4';
$L_DB['srlctd3104']['DBHOST'] = 'srnmaintenancedevdb1.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
$L_DB['srlctd3104']['DBUSER'] = 'axxkc01';
$L_DB['srlctd3104']['DBPASSWD'] = 'kc_01axx';
$L_DB['srlctd3104']['DBNAME'] = 'SRLCK04';
$L_DB['srlctd3104']['DBPORT'] = '3306';

//	開発DB#5
$L_DB['srlctd3105']['NAME'] = '開発DB#5';
$L_DB['srlctd3105']['DBHOST'] = 'srnmaintenancedevdb1.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
$L_DB['srlctd3105']['DBUSER'] = 'axxkc01';
$L_DB['srlctd3105']['DBPASSWD'] = 'kc_01axx';
$L_DB['srlctd3105']['DBNAME'] = 'SRLCK05';
$L_DB['srlctd3105']['DBPORT'] = '3306';

//	開発DB#6
$L_DB['srlctd3106']['NAME'] = '開発DB#6';
$L_DB['srlctd3106']['DBHOST'] = 'srnmaintenancedevdb1.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
$L_DB['srlctd3106']['DBUSER'] = 'axxkc01';
$L_DB['srlctd3106']['DBPASSWD'] = 'kc_01axx';
$L_DB['srlctd3106']['DBNAME'] = 'SRLCK06';
$L_DB['srlctd3106']['DBPORT'] = '3306';

//	開発DB#7
$L_DB['srlctd3107']['NAME'] = '開発DB#7';
$L_DB['srlctd3107']['DBHOST'] = 'srnmaintenancedevdb1.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
$L_DB['srlctd3107']['DBUSER'] = 'axxkc01';
$L_DB['srlctd3107']['DBPASSWD'] = 'kc_01axx';
$L_DB['srlctd3107']['DBNAME'] = 'SRLCK07';
$L_DB['srlctd3107']['DBPORT'] = '3306';

//	開発DB#8
$L_DB['srlctd3108']['NAME'] = '開発DB#8';
$L_DB['srlctd3108']['DBHOST'] = 'srnmaintenancedevdb1.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
$L_DB['srlctd3108']['DBUSER'] = 'axxkc01';
$L_DB['srlctd3108']['DBPASSWD'] = 'kc_01axx';
$L_DB['srlctd3108']['DBNAME'] = 'SRLCK08';
$L_DB['srlctd3108']['DBPORT'] = '3306';

// add start oda 2020/08/15 スケールアウト
//	開発DB#9
$L_DB['srlctd3109']['NAME'] = '開発DB#9';
$L_DB['srlctd3109']['DBHOST'] = 'srnmaintenancedevdb1.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
$L_DB['srlctd3109']['DBUSER'] = 'axxkc01';
$L_DB['srlctd3109']['DBPASSWD'] = 'kc_01axx';
$L_DB['srlctd3109']['DBNAME'] = 'SRLCK09';
$L_DB['srlctd3109']['DBPORT'] = '3306';

//	開発DB#10
$L_DB['srlctd3110']['NAME'] = '開発DB#10';
$L_DB['srlctd3110']['DBHOST'] = 'srnmaintenancedevdb1.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
$L_DB['srlctd3110']['DBUSER'] = 'axxkc01';
$L_DB['srlctd3110']['DBPASSWD'] = 'kc_01axx';
$L_DB['srlctd3110']['DBNAME'] = 'SRLCK10';
$L_DB['srlctd3110']['DBPORT'] = '3306';

//	開発DB#11
$L_DB['srlctd3111']['NAME'] = '開発DB#11';
$L_DB['srlctd3111']['DBHOST'] = 'srnmaintenancedevdb1.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
$L_DB['srlctd3111']['DBUSER'] = 'axxkc01';
$L_DB['srlctd3111']['DBPASSWD'] = 'kc_01axx';
$L_DB['srlctd3111']['DBNAME'] = 'SRLCK11';
$L_DB['srlctd3111']['DBPORT'] = '3306';

//	開発DB#12
$L_DB['srlctd3112']['NAME'] = '開発DB#12';
$L_DB['srlctd3112']['DBHOST'] = 'srnmaintenancedevdb1.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
$L_DB['srlctd3112']['DBUSER'] = 'axxkc01';
$L_DB['srlctd3112']['DBPASSWD'] = 'kc_01axx';
$L_DB['srlctd3112']['DBNAME'] = 'SRLCK12';
$L_DB['srlctd3112']['DBPORT'] = '3306';

//	開発DB#13
$L_DB['srlctd3113']['NAME'] = '開発DB#13';
$L_DB['srlctd3113']['DBHOST'] = 'srnmaintenancedevdb1.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
$L_DB['srlctd3113']['DBUSER'] = 'axxkc01';
$L_DB['srlctd3113']['DBPASSWD'] = 'kc_01axx';
$L_DB['srlctd3113']['DBNAME'] = 'SRLCK13';
$L_DB['srlctd3113']['DBPORT'] = '3306';

// //	開発DB#14
// $L_DB['srlctd3114']['NAME'] = '開発DB#14';
// $L_DB['srlctd3114']['DBHOST'] = 'srnmaintenancedevdb1.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
// $L_DB['srlctd3114']['DBUSER'] = 'axxkc01';
// $L_DB['srlctd3114']['DBPASSWD'] = 'kc_01axx';
// $L_DB['srlctd3114']['DBNAME'] = 'SRLCK14';
// $L_DB['srlctd3114']['DBPORT'] = '3306';

// //	開発DB#15
// $L_DB['srlctd3115']['NAME'] = '開発DB#15';
// $L_DB['srlctd3115']['DBHOST'] = 'srnmaintenancedevdb1.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
// $L_DB['srlctd3115']['DBUSER'] = 'axxkc01';
// $L_DB['srlctd3115']['DBPASSWD'] = 'kc_01axx';
// $L_DB['srlctd3115']['DBNAME'] = 'SRLCK15';
// $L_DB['srlctd3115']['DBPORT'] = '3306';
// add end oda 2020/08/15 スケールアウト

// add start  hasegawa 2019/04/01 生徒TOP改修
// 開発
$L_DB['srlgkd01']['NAME'] = 'ゲーミフィケーション開発DBサーバ';
$L_DB['srlgkd01']['DBHOST'] = 'srnmaintenancedevgamedb1.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
$L_DB['srlgkd01']['DBUSER'] = 'axxkg01';
$L_DB['srlgkd01']['DBPASSWD'] = 'kg_01axx';
$L_DB['srlgkd01']['DBNAME'] = 'SRLGK01';
$L_DB['srlgkd01']['DBPORT'] = '3306';
// 検証
$L_DB['srlgsd01']['NAME'] = 'ゲーミフィケーション検証DBサーバ';
$L_DB['srlgsd01']['DBHOST'] = 'srnstggamedb1.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
$L_DB['srlgsd01']['DBUSER'] = 'axxsg01';
$L_DB['srlgsd01']['DBPASSWD'] = 'sg_01axx';
$L_DB['srlgsd01']['DBNAME'] = 'SRLGH01';
$L_DB['srlgsd01']['DBPORT'] = '3306';
// 本番
$L_DB['srlghd01']['NAME'] = 'ゲーミフィケーション本番DBサーバ';
$L_DB['srlghd01']['DBHOST'] = 'srnprodgamedb1.cbijybrmr2ex.ap-northeast-1.rds.amazonaws.com';
$L_DB['srlghd01']['DBUSER'] = 'srlhg01';
$L_DB['srlghd01']['DBPASSWD'] = 'hg_01srl';
$L_DB['srlghd01']['DBNAME'] = 'SRLGH01';
$L_DB['srlghd01']['DBPORT'] = '3306';
// add end  hasegawa 2019/04/01

//	SERVER LIST

//	検証バッチWeb
$L_DB['srlbtw21']['NAME'] = '検証バッチWeb';
$L_DB['srlbtw21']['SERVERNAME'] = 'srlbtw21';
$L_DB['srlbtw21']['DIRTYPE'] = '2';
$L_DB['srlbtw21']['TIME'] = '9';

//	開発コアWeb
$L_DB['srlctw11']['NAME'] = '開発コアWeb';
$L_DB['srlctw11']['SERVERNAME'] = 'srlctw31';
$L_DB['srlctw11']['DIRTYPE'] = '1';
$L_DB['srlctw31']['TIME'] = '0';



//	アンケートログ用DB
$L_QNAIRE_LOG_DB = array(
					 0 => $L_DB['list']
					,11 => $L_DB['srlctw1101']
					,12 => $L_DB['srlctw1102']
					,13 => $L_DB['srlctw1103']
					,14 => $L_DB['srlctw1104']
					,15 => $L_DB['srlctw1105']
					,16 => $L_DB['srlctw1106']
					,17 => $L_DB['srlctw1107']
					,18 => $L_DB['srlctw1108']
					// add start oda 2020/08/15 スケールアウト
					,19 => $L_DB['srlctw1109']
					,20 => $L_DB['srlctw1110']
					,21 => $L_DB['srlctw1111']
					,22 => $L_DB['srlctw1112']
					,23 => $L_DB['srlctw1113']
// 					,24 => $L_DB['srlctw1114']
// 					,25 => $L_DB['srlctw1115']
					// add end oda 2020/08/15 スケールアウト
					,99 => $L_DB['srlbtw21']
					);

//	コンテンツアップロードDB用
$L_CONTENTS_DB = array(
					 0 => $L_DB['list']
					,11 => $L_DB['srlctw1101']
					,12 => $L_DB['srlctw1102']
					,13 => $L_DB['srlctw1103']
					,14 => $L_DB['srlctw1104']
					,15 => $L_DB['srlctw1105']
					,16 => $L_DB['srlctw1106']
					,17 => $L_DB['srlctw1107']
					,18 => $L_DB['srlctw1108']
					// add start oda 2020/08/15 スケールアウト
					,19 => $L_DB['srlctw1109']
					,20 => $L_DB['srlctw1110']
					,21 => $L_DB['srlctw1111']
					,22 => $L_DB['srlctw1112']
					,23 => $L_DB['srlctw1113']
// 					,24 => $L_DB['srlctw1114']
// 					,25 => $L_DB['srlctw1115']
					// add end oda 2020/08/15 スケールアウト
					,92 => $L_DB['srlbtw21']

					);

//	コンテンツアップロードWEB用
$L_CONTENTS_WEB = array(
					 0 => $L_DB['list']
					,92 => $L_DB['srlctw11']
					,93 => $L_DB['srlbtw21']
					);

//	コンテンツ検証DBサーバー名一覧
$L_TSTC_DB = array(
					 "srlctd0201"
					,"srlctd0202"
					,"srlctd0203"
					,"srlctd0204"
					,"srlctd0305"
					,"srlctd0206"
					,"srlctd0207"
					,"srlctd0208"
					// add start oda 2020/08/15 スケールアウト
					,"srlctd0209"
					,"srlctd0210"
					,"srlctd0211"
					,"srlctd0212"
					,"srlctd0213"
// 					,"srlctd0214"
// 					,"srlctd0215"
					// add start oda 2020/08/15 スケールアウト
				);


//	コンテンツ検証DBサーバー名一覧＋統合
$L_TSTC_DB_ADD_E = array(
					 "srlctd0201"
					,"srlctd0202"
					,"srlctd0203"
					,"srlctd0204"
					,"srlctd0305"
					,"srlctd0206"
					,"srlctd0207"
					,"srlctd0208"
					// add start oda 2020/08/15 スケールアウト
					,"srlctd0209"
					,"srlctd0210"
					,"srlctd0211"
					,"srlctd0212"
					,"srlctd0213"
// 					,"srlctd0214"
// 					,"srlctd0215"
					// add start oda 2020/08/15 スケールアウト
					,"srlctd01"
					);

// add start hasegawa 2019/07/10 生徒TOP改修
//	ゲーミフィケーションコンテンツアップロードWEB用
$L_GAMIFICATION_CONTENTS_WEB = array(
					 0 => $L_DB['list']
					,92 => $L_DB['srlgkd01']
					,93 => $L_DB['srlgsd01']
					);

//	ゲーミフィケーションコンテンツ検証DBサーバー名一覧
$L_GAMIFICATION_TSTC_DB = array(
					 "srlgsd01"
					);
// add end hasegawa 2019/07/10

// ---------------------------------

//	素材関連
$STUDENT_TEMP_DIR = "../../_www/template/student/";
$MATERIAL_DIR = "../material/";									//	素材フォルダー
$MATERIAL_FLASH_DIR			= $MATERIAL_DIR."flash/";			//	FLASHフォルダー
$MATERIAL_VOICE_DIR			= $MATERIAL_DIR."voice/";			//	音声フォルダー
$MATERIAL_PROB_DIR			= $MATERIAL_DIR."prob_img/";		//	問題イメージフォルダー
$MATERIAL_TEMP_DIR			= $MATERIAL_DIR."template/";		//	コースイメージフォルダー
$MATERIAL_CHART_DIR			= $MATERIAL_DIR."chart/";			//	コース体系図フォルダー
$MATERIAL_SKILL_IMG_DIR		= $MATERIAL_DIR."skill_img/";		//	コーススキルイメージフォルダー
$MATERIAL_SKILL_VOICE_DIR	= $MATERIAL_DIR."skill_voice/";		//	コーススキルボイスフォルダー
$MATERIAL_PRINT_DIR			= $MATERIAL_DIR."print/";			//	まとめプリントフォルダー
$MATERIAL_PALETTE_DIR		= $MATERIAL_DIR."palette/";			//	数式パレットフォルダー
$MATERIAL_MANUAL_DIR		= $MATERIAL_DIR."system_manual/";	//	マニュアルフォルダー
$MATERIAL_MANUAL_LIST		= "manual_list.txt";				//	マニュアルファイル情報　先生
$MATERIAL_TEST_IMG_DIR		= $MATERIAL_DIR."test_img/";		//	テスト用画像フォルダー
$MATERIAL_TEST_VOICE_DIR	= $MATERIAL_DIR."test_voice/";		//	テスト用ボイスフォルダー
$MATERIAL_POINT_IMG_DIR		= $MATERIAL_DIR."point_img/";		//	ポイント解説用画像
$MATERIAL_POINT_VOICE_DIR	= $MATERIAL_DIR."point_voice/";		//	ポイント解説用音声
$MATERIAL_WORD_IMG_DIR		= $MATERIAL_DIR."word_img/";		//	ワード管理用画像
$MATERIAL_WORD_VOICE_DIR	= $MATERIAL_DIR."word_voice/";		//	ワード管理用音声
$MATERIAL_HANTEI_IMG_DIR	= $MATERIAL_DIR."hantei_img/";		//	判定テスト用画像
$MATERIAL_HANTEI_VOICE_DIR	= $MATERIAL_DIR."hantei_voice/";	//	判定テスト用音声
$MATERIAL_SECRET_EVENT_DIR	= $MATERIAL_DIR."secret_event/";	//	シークレットイベントフォルダ

// 定数化
define("STUDENT_TEMP_DIR",$STUDENT_TEMP_DIR);
define("MATERIAL_FLASH_DIR",$MATERIAL_FLASH_DIR);
define("MATERIAL_VOICE_DIR",$MATERIAL_VOICE_DIR);
define("MATERIAL_PROB_DIR",$MATERIAL_PROB_DIR);
define("MATERIAL_TEMP_DIR",$MATERIAL_TEMP_DIR);
define("MATERIAL_CHART_DIR",$MATERIAL_CHART_DIR);
define("MATERIAL_SKILL_IMG_DIR",$MATERIAL_SKILL_IMG_DIR);
define("MATERIAL_SKILL_VOICE_DIR",$MATERIAL_SKILL_VOICE_DIR);
define("MATERIAL_PRINT_DIR",$MATERIAL_PRINT_DIR);
define("MATERIAL_PALETTE_DIR",$MATERIAL_PALETTE_DIR);
define("MATERIAL_MANUAL_DIR",$MATERIAL_MANUAL_DIR);
define("MATERIAL_MANUAL_LIST",$MATERIAL_MANUAL_LIST);
define("MATERIAL_MANUAL_LIST_SC",$MATERIAL_MANUAL_LIST_SC);
define("MATERIAL_MANUAL_LIST_EN",$MATERIAL_MANUAL_LIST_EN);
define("MATERIAL_TEST_IMG_DIR",$MATERIAL_TEST_IMG_DIR);
define("MATERIAL_TEST_VOICE_DIR",$MATERIAL_TEST_VOICE_DIR);

define("MATERIAL_POINT_IMG_DIR",$MATERIAL_POINT_IMG_DIR);
define("MATERIAL_POINT_VOICE_DIR",$MATERIAL_POINT_VOICE_DIR);
define("MATERIAL_WORD_IMG_DIR",$MATERIAL_WORD_IMG_DIR);
define("MATERIAL_WORD_VOICE_DIR",$MATERIAL_WORD_VOICE_DIR);

define("MATERIAL_HANTEI_IMG_DIR",$MATERIAL_HANTEI_IMG_DIR);
define("MATERIAL_HANTEI_VOICE_DIR",$MATERIAL_HANTEI_VOICE_DIR);
define("MATERIAL_SECRET_EVENT_DIR", $MATERIAL_SECRET_EVENT_DIR);		//	シークレットイベントフォルダ

//	ベースフォルダー(検証Web)：
$BASE_DIR = "/data/home";

//	検証バッチベースフォルダー検証用：
$KBAT_DIR = "/data/apprelease/Release/Contents";

//	テンプレートサーバベースフォルダー本番用：(cgi側で定義している場合があるのでデバッグで参照先を変更する場合には要確認！)
$HBAT_DIR = "/data/home";

// -- 生徒テンプレート
$REMOTE_STUDENT_TEMP_DIR = "/_www/template/student/";						//	生徒テンプレートフォルダー

// -- 素材関連
$REMOTE_MATERIAL_DIR = "/www/material/";										//	素材フォルダー
$REMOTE_MATERIAL_FLASH_DIR			= $REMOTE_MATERIAL_DIR."flash/";			//	FLASHフォルダー
$REMOTE_MATERIAL_VOICE_DIR			= $REMOTE_MATERIAL_DIR."voice/";			//	音声フォルダー
$REMOTE_MATERIAL_PROB_DIR			= $REMOTE_MATERIAL_DIR."prob_img/";			//	問題イメージフォルダー
$REMOTE_MATERIAL_TEMP_DIR			= $REMOTE_MATERIAL_DIR."template/";			//	コースイメージフォルダー
$REMOTE_MATERIAL_CHART_DIR			= $REMOTE_MATERIAL_DIR."chart/";			//	コース体系図フォルダー
$REMOTE_MATERIAL_SKILL_IMG_DIR		= $REMOTE_MATERIAL_DIR."skill_img/";		//	コーススキルイメージフォルダー
$REMOTE_MATERIAL_SKILL_VOICE_DIR	= $REMOTE_MATERIAL_DIR."skill_voice/";		//	コーススキルボイスフォルダー
$REMOTE_MATERIAL_PRINT_DIR			= $REMOTE_MATERIAL_DIR."print/";			//	まとめプリントフォルダー
$REMOTE_MATERIAL_PALETTE_DIR		= $REMOTE_MATERIAL_DIR."palette/";			//	数式パレットフォルダー
$REMOTE_MATERIAL_MANUAL_DIR			= $REMOTE_MATERIAL_DIR."system_manual/";	//	マニュアルフォルダー
$REMOTE_MATERIAL_TEST_IMG_DIR		= $REMOTE_MATERIAL_DIR."test_img/";			//	テスト用画像フォルダー
$REMOTE_MATERIAL_TEST_VOICE_DIR		= $REMOTE_MATERIAL_DIR."test_voice/";		//	テスト用ボイスフォルダー
$REMOTE_MATERIAL_POINT_IMG_DIR		= $REMOTE_MATERIAL_DIR."point_img/";		//	ポイント解説用画像
$REMOTE_MATERIAL_POINT_VOICE_DIR	= $REMOTE_MATERIAL_DIR."point_voice/";		//	ポイント解説用音声
$REMOTE_MATERIAL_WORD_IMG_DIR		= $REMOTE_MATERIAL_DIR."word_img/";			//	ワード管理用画像
$REMOTE_MATERIAL_WORD_VOICE_DIR		= $REMOTE_MATERIAL_DIR."word_voice/";		//	ワード管理用音声
$REMOTE_MATERIAL_HANTEI_IMG_DIR		= $REMOTE_MATERIAL_DIR."hantei_img/";		//	判定テスト用画像
$REMOTE_MATERIAL_HANTEI_VOICE_DIR	= $REMOTE_MATERIAL_DIR."hantei_voice/";		//	判定テスト用音声
$REMOTE_MATERIAL_SECRET_EVENT_DIR	= $REMOTE_MATERIAL_DIR."secret_event/";		//	シークレットイベントフォルダ


// add start hasegawa 2019/07/08 生徒TOP改修
// ゲーミフィケーションサーバ素材フォルダ
$REMOTE_MATERIAL_GAM_DIR = "/www/material/gamification/";
$REMOTE_MATERIAL_GAM_CHARACTER_DIR	= $REMOTE_MATERIAL_GAM_DIR."character/";		//	キャラクター
$REMOTE_MATERIAL_GAM_AVATAR_DIR		= $REMOTE_MATERIAL_GAM_DIR."avatar/";			//	アバター
$REMOTE_MATERIAL_GAM_ACHIEVE_EGG_DIR	= $REMOTE_MATERIAL_GAM_DIR."achieve_egg/";		//	アチーブエッグ
$REMOTE_MATERIAL_GAM_ITEM_DIR		= $REMOTE_MATERIAL_GAM_DIR."item/";			//	アイテム情報

$MATERIAL_GAM_DIR = "../material/gamification/";
$MATERIAL_GAM_CHARACTER_DIR		= $MATERIAL_DIR."character/";			//	キャラクター
$MATERIAL_GAM_AVATAR_DIR		= $MATERIAL_DIR."avatar/";			//	アバター
$MATERIAL_GAM_ACHIEVE_EGG_DIR		= $MATERIAL_DIR."achieve_egg/";			//	アチーブエッグ
$MATERIAL_GAM_ITEM_DIR			= $MATERIAL_DIR."item/";			//	アイテム情報
// add end hasegawa 2019/07/08


// 定数化
define("BASE_DIR",$BASE_DIR);

define("KBAT_DIR",$KBAT_DIR);
define("HBAT_DIR",$HBAT_DIR);

define("REMOTE_STUDENT_TEMP_DIR",$REMOTE_STUDENT_TEMP_DIR);

define("REMOTE_MATERIAL_FLASH_DIR",$REMOTE_MATERIAL_FLASH_DIR);
define("REMOTE_MATERIAL_VOICE_DIR",$REMOTE_MATERIAL_VOICE_DIR);
define("REMOTE_MATERIAL_PROB_DIR",$REMOTE_MATERIAL_PROB_DIR);
define("REMOTE_MATERIAL_TEMP_DIR",$REMOTE_MATERIAL_TEMP_DIR);
define("REMOTE_MATERIAL_CHART_DIR",$REMOTE_MATERIAL_CHART_DIR);
define("REMOTE_MATERIAL_SKILL_IMG_DIR",$REMOTE_MATERIAL_SKILL_IMG_DIR);
define("REMOTE_MATERIAL_SKILL_VOICE_DIR",$REMOTE_MATERIAL_SKILL_VOICE_DIR);
define("REMOTE_MATERIAL_PRINT_DIR",$REMOTE_MATERIAL_PRINT_DIR);
define("REMOTE_MATERIAL_PALETTE_DIR",$REMOTE_MATERIAL_PALETTE_DIR);
define("REMOTE_MATERIAL_MANUAL_DIR",$REMOTE_MATERIAL_MANUAL_DIR);
define("REMOTE_MATERIAL_TEST_IMG_DIR",$REMOTE_MATERIAL_TEST_IMG_DIR);
define("REMOTE_MATERIAL_TEST_VOICE_DIR",$REMOTE_MATERIAL_TEST_VOICE_DIR);

define("REMOTE_MATERIAL_POINT_IMG_DIR",$REMOTE_MATERIAL_POINT_IMG_DIR);
define("REMOTE_MATERIAL_POINT_VOICE_DIR",$REMOTE_MATERIAL_POINT_VOICE_DIR);
define("REMOTE_MATERIAL_WORD_IMG_DIR",$REMOTE_MATERIAL_WORD_IMG_DIR);
define("REMOTE_MATERIAL_WORD_VOICE_DIR",$REMOTE_MATERIAL_WORD_VOICE_DIR);

define("REMOTE_MATERIAL_HANTEI_IMG_DIR",$REMOTE_MATERIAL_HANTEI_IMG_DIR);
define("REMOTE_MATERIAL_HANTEI_VOICE_DIR",$REMOTE_MATERIAL_HANTEI_VOICE_DIR);
define("REMOTE_MATERIAL_SECRET_EVENT_DIR", $REMOTE_MATERIAL_SECRET_EVENT_DIR);


// add start hasegawa 2019/07/08 生徒TOP改修
define("REMOTE_MATERIAL_GAM_DIR", $REMOTE_MATERIAL_GAM_DIR);
define("REMOTE_MATERIAL_GAM_CHARACTER_DIR", $REMOTE_MATERIAL_GAM_CHARACTER_DIR);
define("REMOTE_MATERIAL_GAM_AVATAR_DIR", $REMOTE_MATERIAL_GAM_AVATAR_DIR);
define("REMOTE_MATERIAL_GAM_ACHIEVE_EGG_DIR", $REMOTE_MATERIAL_GAM_ACHIEVE_EGG_DIR);
define("REMOTE_MATERIAL_GAM_ITEM_DIR", $REMOTE_MATERIAL_GAM_ITEM_DIR);
// add end hasegawa 2019/07/08



?>
