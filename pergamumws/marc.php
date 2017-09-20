<?
include("parametros.php");
$color = true;
//$query = sybase_query("select distinct ams.cod_acervo, r.desc_titulo, r.ano_publicacao, t.desc_tipo_obra
//$query = sybase_query("select ams.*, r.*, t.*
#$query = sybase_query("select * from acervo_marc_secao where cod_acervo=$cod_acervo
$cod_acervo = isset($_GET['cod_acervo'])? intval($_GET['cod_acervo']) : 0;
$query = "select * from acervo_marc_secao where cod_acervo=$cod_acervo order by paragrafo, seq_paragrafo";
$result = mssql_query($query,$db);

$trabalhos = array();
while($array1 = mssql_fetch_assoc($result))
{
   $t = array();
   foreach($array1 as $k => $v){
	$t[$k] = utf8_encode(htmlentities($v, ENT_COMPAT, "ISO-8859-1" ));
	#$t[$k] = utf8_encode(htmlentities($v ));
	//$t[$k] = $v;
   }
   $trabalhos[] = $t;
}
header('Content-Type: text/javascript; charset=utf8');
echo json_encode($trabalhos);
