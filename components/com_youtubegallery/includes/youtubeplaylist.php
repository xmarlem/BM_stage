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

class VideoSource_YoutubePlaylist
{
	public static function extractYouTubePlayListID($youtubeURL)
	{
				
		$arr=YouTubeGalleryMisc::parse_query($youtubeURL);
		
		$p=$arr['list'];
		
		if(strlen($p)<3)
			return '';
		
		if(substr($p,0,2)!='PL')
			return ''; //incorrect playlist ID
		 
	    return substr($p,2); //return without leading "PL"
	}
	
	public static function getVideoIDList($youtubeURL,$optionalparameters,&$playlistid,&$datalink)
	{
		$optionalparameters_arr=explode(',',$optionalparameters);
		
		$videolist=array();
		
		$spq=implode('&',$optionalparameters_arr);
		
		$videolist=array();
		
		$playlistid=VideoSource_YoutubePlaylist::extractYouTubePlayListID($youtubeURL);
		if($playlistid=='')
			return $videolist; //playlist id not found
		
		$url = 'http://gdata.youtube.com/feeds/api/playlists/'.$playlistid.($spq!='' ? '?'.$spq : '' ) ; //&max-results=10;
		$datalink=$url;
		
		$xml=false;
		$htmlcode=YouTubeGalleryMisc::getURLData($url);
		if($htmlcode=='')
			return $videolist;

		if(strpos($htmlcode,'<?xml version')===false)
		{
			if(strpos($htmlcode,'Invalid id')===false)
				return 'Cannot load data, Invalid id';

			return 'Cannot load data, no connection';
		}
		$xml = simplexml_load_string($htmlcode);
		
		if($xml){
			foreach ($xml->entry as $entry)
			{

				$media = $entry->children('http://search.yahoo.com/mrss/');
				if(isset($media))
				{
					$link = $media->group->player->attributes();
					if(isset($link))
					{
						if(isset($link['url']))
						{
							$videolist[] = $link['url'];
						}
					}//if(isset($link)
				}
			}//foreach ($xml->entry as $entry)
		}//if($xml){
		
		return $videolist;
		
	}
	
	


}


?>