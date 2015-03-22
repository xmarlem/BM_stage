<?php
/**
 * YoutubeGallery
 * @version 4.2.8
 * @author DesignCompass corp< <support@joomlaboat.com>
 * @link http://www.joomlaboat.com
 * @GNU General Public License
 **/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

if(!defined('DS'))
	define('DS',DIRECTORY_SEPARATOR);

require_once(JPATH_SITE.DS.'components'.DS.'com_youtubegallery'.DS.'includes'.DS.'misc.php');

class VideoSource_YoutubeChannel
{
	protected static function extractID($youtubeURL)
	{
		//https://www.youtube.com/channel/UCRND2QLhATXcgrTgFfsZHyg/videos
		$matches=explode('/',$youtubeURL);
	
		if (count($matches) >4)
		{
			
			$channelid = $matches[4];
			$pair=explode('?',$channelid);
			return $pair[0];
		}
				
		return '';
	}
	
	public static function getVideoIDList($youtubeURL,$optionalparameters,&$channelid,&$datalink)
	{
		$optionalparameters_arr=explode(',',$optionalparameters);
		$videolist=array();
		
		$api_key = YouTubeGalleryMisc::getSettingValue('youtube_api_key');
		
		if($api_key=='')
			return $videolist;
		
		
		$spq=implode('&',$optionalparameters_arr);
		
		$channelid=VideoSource_YoutubeChannel::extractID($youtubeURL);
		
		if($channelid=='')
			return $videolist; //user id not found
		
		$part='id,snippet';//,contentDetails,//,statistics';//,status
		//$url = 'https://www.googleapis.com/youtube/v3/channels';
		
		//echo 'spq='.$spq.'<br/>';
		//https://www.googleapis.com/youtube/v3/search?part=snippet&key=AIzaSyAlT_VPwSyW_A3r9wKnx87Fa1qCI4XWL9Q&maxResults=10&q=&channelId=UCBfLAIb-97Ligxtd5vnKNmQ
		$datalink = 'https://www.googleapis.com/youtube/v3/search?channelId='.$channelid.'&part='.$part.'&key='.$api_key.'&maxResults=10';
		//echo 'url='.$url.'<br/>';
		
		
		//$url = 'http://gdata.youtube.com/feeds/api/users/'.$userid.'/uploads?v=2'.($spq!='' ? '&'.$spq : '' ) ; //&max-results=10
		//$datalink=$url;
		
		$htmlcode=YouTubeGalleryMisc::getURLData($datalink);
		//echo $htmlcode;
		
		if($htmlcode=='')
			return $videolist;
		
		
		$j=json_decode($htmlcode);
		if(!$j)
		{
			//print_r($j);
			//die;
			return 'Connection Error';
		}
		
		
		$items=$j->items;
		foreach($items as $item)
		{
			if($item->kind=='youtube#searchResult')
			{
				$idKind=$item->id->kind;
				
				if($idKind=='youtube#video')
				{
				
					$videoId=$item->id->videoId;
				
					//echo '$idKind='.$idKind.'<br/>';
					//echo '$videoId='.$videoId.'<br/>';
					$videolist[] = 'https://www.youtube.com/watch?v='.$videoId;
				}
			}
			
			
			
		}	
			/*
			foreach ($xml->entry as $entry)
			{
				
				$attr=$entry->link[0]->attributes();

				if(isset($entry->link[0]) && $attr['rel'] == 'alternate')
				{
					$videolist[] = $attr['href'];
                    
				} else {
					$attr=$entry->link[1]->attributes();
					$videolist[] = $attr['href'];
                    		}

			}
			
	*/
		
		//die;
		return $videolist;
		
	}
	


}


?>