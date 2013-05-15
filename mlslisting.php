<p><b><font color = #FF0000"></p>
<div id = "pbar"></div></b></font>
<script type="text/javascript>">
var percent = 10; // adjust starting value to suit
var timePeriod = 100; // adjust milliseconds to suit

function getBar() {
var retBar = "";
for (i = 0; i < percent; i++) {
retBar += "|";
}
return retBar;
}

function progressBar() {

if (percent < 100) {
percent = percent + 1;
document.getElementById("pbar").innerHTML = "&nbsp &nbsp &nbsp &nbsp Loading : " + percent + "%" + " " + getBar();
window.status = "Loading : " + percent + "%" + " " + getBar();
setTimeout ("progressBar()", timePeriod);
}
else {

document.getElementById("pbar").innerHTML = "";
document.getElementById("content").style.display="block";
window.status = "Your message here";
document.body.style.display = "";
}


}

</script>

<body onload="progressBar();">
<div id="content" style="display:none;" >
<h1>Real Estate in the <?php echo $_POST['community']; ?>Community</h1>
<?php

 //$comm=$_POST['community'];
$comm='Mirabel';
$max='No limit';
//$max=$_POST['maxprice'];

$min='Any';
//$min=$_POST['minprice'];
echo $page=$_POST['page'];
if( str_word_count($comm)>1){
$comm=str_replace(" ","%20",$comm);

}


if(($max=="No limit")||($min=="Any"))

$url="http://174.121.152.3/~blhadmin/rets/test_rets1.php?title=$comm";

else
{
echo $url="http://174.121.152.3/~blhadmin/rets/test_rets.php?title=".$comm."&minprice=".$min."& maxprice=".$max;
}
$response = wp_remote_retrieve_body( wp_remote_post( $url, array(
'method' => 'POST',
'timeout' => 45,
'redirection' => 5,
'httpversion' =>'1.0',
'body' =array( 'username' = 'bob', 'password' = '1234xyz' ),
'cookies' = array()

)
));

$data=json_decode($response);
echo "hello".count($data);
?>
</div>
</body>








