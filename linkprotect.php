<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.joomla
 *
 * @copyright   Copyright (C) 2018 Hassan Abas, All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Link Protection Content Plugin
 * @package     Joomla.Plugin
 * @subpackage  Content.joomla
 * @since       3.0
 */
class PlgContentLinkProtection extends JPlugin {
	private $callbackFunction;
	public function __construct(&$subject, $config = array()) {
		parent::__construct($subject, $config);

		require __DIR__ . '/helper/helper.php';
		$helper = new LinkProtectHelper($this->params);
		$this->callbackFunction = array($helper, 'replaceLinks');

	}
	/**
	 * Initiate the plugin
	 * @param string $context The context of the content passes to the plugins, location where the plugin triggered from
	 * @param object $article The article object
	 * @param object $params The article params
	 */
	public function onContentBeforeDisplay($context, $article, $params) {
		$parts = explode('.',$context);
		//ensure it runs under com_content not outside of it
		if($parts[0] != 'com_content'){
			return;
		}
		//user doesnt wont to skip the plugins
		if(stripos($article->text, '{linkprotect=off}') === true) {
			$article->text = str_ireplace('{linkprotect=off}', '', $article->text);
		}
		//to use input
		$app = JFactory::gtApplication();
		$external = $app->input->get('external',null);
		if($external){
			//external in the get url meaning in the warning or exit page
			LinkProtectHelper::leaveSite($article, $external);
		}else {
			$patterns = '@href=("|\')(http?"//([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)("|\')@';
			//sent the callback function result matches from patterns and article text
			$article->text = preg_replace_callback($patterns, $this->callbackFunction, $article->text);
		}
	}

}