<?php

namespace Application\Block\Horizon;
use Concrete\Core\Block\BlockController;
use Core;

defined('C5_EXECUTE') or die(_("Access Denied."));
 
class Controller extends BlockController {
 
	var $pobj;
 
	protected $btTable = 'btHorizon';
	protected $btInterfaceWidth = "400";
	protected $btInterfaceHeight = "180";
	protected $q_usr = '';
 
	public function getBlockTypeDescription() {
		return "Permet d'ajouter les publications Horizon d'une personne sur une page.";
	}
 
	public function getBlockTypeName() {
		return "Horizon-block";
	}
 
	public function __construct($obj = null) {
		parent::__construct($obj);
	}

	public function on_start()
	{
    		$al = \Concrete\Core\Asset\AssetList::getInstance();
    		$al->register(
        		'css', 'horizon', 'blocks/horizon/style_horizon.css',
		        array('version' => '1.0', 'minify' => false, 'combine' => true)
    		);
		$al->registerGroup('horizon', array(
    			array('css', 'horizon'),
		));
	}

	public function registerViewAssets()
	{
    		$this->requireAsset('horizon');
	}
 
	public function view(){
 
	}

	function getRemoteFile($url,$type)
	{
		$file = Core::make('helper/file');
		$response = $file->getContents($url,10);
		if($type == 'Horizon')
		{
			$response=$this->formatHorizon($response);
		} else if($type == 'HAL')
		{
			$response=$this->formatHAL($response);
		}
		// return the file content
		return $response;
	}

	function formatHorizon($response)
	{
		$response=$this->change_iso_utf8($response);
		$response=$this->change_years_into_anchors($response);
                $response=$this->change_legend_style($response);
                $response=$this->change_links($response);
                $response=$this->change_images($response);
                $response=$this->remove_head($response);
                // return the file content
                return $response;
	}

	function formatHAL($response)
        {
		$response=$this->remove_exept_table($response);
		$response=$this->change_links_HAL($response);
                // return the file content
                return $response;
        }


	/**
	 * Just a consistency wrapper for file_get_contents
	 * Should use curl if it exists and fopen isn't allowed (thanks Remo)
	 * @param $filename
	 */
	function getContents($file, $timeout = 60) {
		$url = @parse_url($file);
		if (isset($url['scheme']) && isset($url['host'])) {
			if (ini_get('allow_url_fopen')) {
				$ctx = stream_context_create(array( 
					'http' => array( 'timeout' => $timeout ) 
				)); 
				if ($contents = @file_get_contents($file, 0, $ctx)) {
					return $contents;
				}
			}

			if (function_exists('curl_init')) {
				$curl_handle = curl_init();
				curl_setopt($curl_handle, CURLOPT_URL, $file);
				curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, $timeout);
				curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
				$contents = curl_exec($curl_handle);
				$http_code = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);
				if ($http_code == 404) {	
					return false;
				}
				return $contents;
			}
		} else {
			if ($contents = @file_get_contents($file)) {
				return $contents;
			}
		}
		return false;
	}

	function change_iso_utf8($response)
	{
		//bon caracteres
		$GoodCharaters = array ("¡","¢","£","¤","¥","¦","§","¨","©","ª","«","¬","*","®","¯","°","±","²","³","´","µ","¶","·","¸","¹","º","»","¼","½","¾","¿","×","÷","À","Á","Â","Ã","Ä","Å","Æ","Ç","È","É","Ê","Ë","Ì","Í","Î","Ï","Ð","Ñ","Ò","Ó","Ô","Õ","Ö","Ø","Ù","Ú","Û","Ü","Ý","Þ","ß","à","á","â","ã","ä","å","æ","ç","è","é","ê","ë","ì","í","î","ï","ð","ñ","ò","ó","ô","õ","ö","ø","ù","ú","û","ü","ý","þ","ÿ");  
 
		//Mauvais caractères
		$BadCharacters = array ("Â¡","Â¢","Â£","Â¤","Â¥","Â¦","Â§","Â¨","Â©","Âª","Â«","Â¬","Â*","Â®","Â¯","Â°","Â±","Â²","Â³","Â´","Âµ","Â¶","Â·","Â¸","Â¹","Âº","Â»","Â¼","Â½","Â¾","Â¿","Ã—","Ã·","Ã€","Ã","Ã‚","Ãƒ","Ã„","Ã…","Ã†","Ã‡","Ãˆ","Ã‰","ÃŠ","Ã‹","ÃŒ","Ã","ÃŽ","Ã","Ã","Ã‘","Ã’","Ã“","Ã”","Ã•","Ã–","Ã˜","Ã™","Ãš","Ã›","Ãœ","Ã","Ãž","ÃŸ","Ã ","Ã¡","Ã¢","Ã£","Ã¤","Ã¥","Ã¦","Ã§","Ã¨","Ã©","Ãª","Ã«","Ã¬","Ã*","Ã®","Ã¯","Ã°","Ã±","Ã²","Ã³","Ã´","Ãµ","Ã¶","Ã¸","Ã¹","Ãº","Ã»","Ã¼","Ã½","Ã¾","Ã¿");  
	 
		// remplacer les mauvais caracatères par les bons et encoder le tout en utf8			
		return utf8_encode(str_replace($BadCharacters ,$GoodCharaters ,$response));
	}

	function change_years_into_anchors($reponse)
	{
		$pos=strpos($reponse,'<h4>',0);
		while($pos>0){
			$year=substr($reponse,$pos+4 ,4);
			$reponse=substr_replace($reponse,'<a id="'.$year.'" name="'.$year.'"></a>',$pos-1,0);				
			$pos=strpos($reponse,'<h4>',$pos+45);
		}	
		return $reponse; 
	}

	function change_legend_style($reponse)
	{	
		$pos=strpos($reponse,'titre-options',0);	
		$count=0;
		$options=array('export','tri','legende');
		while($pos>0){
			$pos_titre=$pos;
			$reponse=substr_replace($reponse,'titre-options-'.$options[$count],$pos_titre,strlen('titre-options'));
			$pos_option=strpos($reponse,'option',$pos+strlen('titre-options-'.$options[$count]));						
			$reponse=substr_replace($reponse,'option-'.$options[$count],$pos_option,strlen('option'));		
			$pos=strpos($reponse,'titre-options',$pos+1);
			$count=$count+1;
		}
		return $reponse;
	}

	function change_links($reponse){
		// on enlève le texte en dur qui a été rajouté aux liens (qui sont relatifs)
		$reponse = str_replace ('http://www.documentation.ird.fr/hor','/hor',$reponse);
		// on recrée les liens complets (absolus)
		return str_replace ('/hor','http://www.documentation.ird.fr/hor',$reponse);
	}
	
	function change_images($response){
		//return str_replace('/images','http://coreus.ird.nc/files',$response);
		$bv = new \Concrete\Core\Block\View\BlockView($this->getBlockObject());
	        $blockURL = $bv->getBlockURL();
		return str_replace('/images',$blockURL.'/images',$response);
	}

	function remove_head($response){
		$response = preg_replace('/\<script.*\<\/script\>/iU','',$response);
		$response = preg_replace('/\<link*\>/iU','',$response);
		$response = preg_replace('/\<title>.*\<\/title\>/iU','',$response);
		return $response;
	}
	function remove_exept_table($response){
		$div = strstr($response, '<table class="table table-hover">');
		$div = substr($div, 0, strpos($div, '</table>') + 8);
		return $div;
	}
	function change_links_HAL($reponse){
                // on enlève le texte en dur qui a été rajouté aux liens (qui sont relatifs)
                //$reponse = str_replace ('http://www.documentation.ird.fr/hor','/hor',$reponse);
                // on recrée les liens complets (absolus)
                return str_replace ('/ENTROPIE','https://hal-univ-reunion.archives-ouvertes.fr/ENTROPIE',$reponse);
        }


}
 
?>
