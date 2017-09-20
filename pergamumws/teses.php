<?
include("parametros.php");


$color = true;
$query = mssql_query("select r.*, t.*
from acervo_geral ag, referencia r, tipo_obra t
where ag.cod_sit_acervo='0'
and not exists (select * from acervos_incompletos ai
where ai.cod_acervo = ag.cod_acervo
and ai.cod_tipo_obra = ag.cod_tipo_obra
and ai.cod_empresa = ag.cod_empresa)
and ag.cod_acervo = r.cod_acervo
and ag.cod_empresa = r.cod_empresa
and t.cod_empresa=18
and t.cod_tipo_obra=ag.cod_tipo_obra
and ag.cod_tipo_obra in(6,9)
and (r.ano_publicacao = '".$ano."' or '".$ano."'=''   )
and (r.cod_acervo = '".$acervo."' or '".$acervo."'=''   )
--and r.ano_publicacao >= '2009' and r.ano_publicacao<='2009'
--and r.cod_acervo=348186
order by r.ano_publicacao desc
",$db);
$trabalhos = array();
while($array1 = mssql_fetch_assoc($query))
{
	#print_r($array1);
	#die("teste");
   $t = array();
   $url_link = null;
   foreach($array1 as $k => $v){
	$t[$k] = utf8_encode(htmlentities($v,ENT_COMPAT, "ISO-8859-1" ));
	//$t[$k] = utf8_encode(htmlentities($v ));
   }

   $link1 		= mssql_query("spwper_busca_links856 18,".$t['cod_acervo'],$db);

   while($reg_link = @mssql_fetch_array($link1))
   {
           $url_link = $reg_link["descricao"];
           if(trim($url_link)=="")
                 $url_link = $reg_link["texto_descricao"];
           $t['links'] = htmlentities($url_link);
           //$t['links'] = $url_link;
   }
   $trabalhos[] = $t;
}
header('Content-Type: text/javascript; charset=utf8');
echo json_encode($trabalhos);
