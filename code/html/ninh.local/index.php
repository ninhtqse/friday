<?php 


function checkdata($number){
	if($number > 10){
    	throw new Exception("Number is greater than 10");
    }
    return true;
}
checkdata(15);
// try block starts
// try{
// 	checkdata(15);
//   	echo "The number is below 10";
// }
// // catch block
// catch(Exception $e){
// 	echo "Message :".$e->getMessage();
// }