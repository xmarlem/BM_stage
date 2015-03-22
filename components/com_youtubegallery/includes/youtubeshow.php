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

class VideoSource_YoutubeShow
{
	protected static function deleteParameter($arr,$par)
	{
		$new_arr=array();
		foreach($arr as $a)
		{
			$pair=explode('=',$a);
			if($pair[0]!=$par)
				$new_arr[]=$a;
		}
		
		return $new_arr;
	}
	
	protected static function getValueOfParameter($arr,$par)
	{
		foreach($arr as $a)
		{
			$pair=explode('=',$a);
			if($pair[0]==$par)
			{
				if(isset($pair[1]))
					return $pair[1];
				else
					return '';
			}
		}
		return '';
	}
	
	public static function getVideoIDList($youtubeURL,$optionalparameters,&$playlistid,&$datalink)
	{

		//echo '$optionalparameters='.$optionalparameters.'<br/>';
		$optionalparameters_arr=explode(',',$optionalparameters);
		
		
		$videolist=array();
		$season=VideoSource_YoutubeShow::getValueOfParameter($optionalparameters_arr,'season');
		$content_type=VideoSource_YoutubeShow::getValueOfParameter($optionalparameters_arr,'content');
		if($content_type=='')
			$content_type='episodes';
			
		$max_results=VideoSource_YoutubeShow::getValueOfParameter($optionalparameters_arr,'max_results');
		if($max_results!='')
		{
			$start_index=VideoSource_YoutubeShow::getValueOfParameter($optionalparameters_arr,'start-index');
			if($start_index=='')
				$optionalparameters_arr[]='start-index=1';
		}

		//echo '$season='.$season.'<br/>';
		$season=explode(':',$season);
		
		if(count($season)==4)
			$season_id=$season[2];
		else
			return $videolist; //season id not found
		

		$optionalparameters_arr=VideoSource_YoutubeShow::deleteParameter($optionalparameters_arr,'season');
		$optionalparameters_arr=VideoSource_YoutubeShow::deleteParameter($optionalparameters_arr,'content');
		
		
		
		$spq=implode('&',$optionalparameters_arr);
		$url = 'http://gdata.youtube.com/feeds/api/seasons/'.$season_id.'/'.$content_type.'?v=2'.($spq!='' ? '&'.$spq : '' ) ;
		//echo '$url='.$url.'<br/>';
		
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

				$link = $entry->link->attributes();
				//echo 'l='.$link['href'].'<br/>';
				$videolist[] = $link['href'];

				/*
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
				*/
			}//foreach ($xml->entry as $entry)
		}//if($xml){
		return $videolist;
		
	}
	
	


}


?>