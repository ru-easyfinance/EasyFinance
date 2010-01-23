<?php

class Helper_Js implements _Core_Router_iHook
{
	public static function execRouterHook( _Core_Request $request, &$class, &$method, array &$chunks, &$templateEngine )
	{
		if( JS_MINIFY )
		{
		    $templateEngine->append('js',  'global-min.js');
		}
		else
		{
			foreach ( file( SYS_DIR_INC . 'js/global.list') as $js )
			{
				$templateEngine->append('js', $js);
			}
		}
		
		$templateEngine->append('js', 'flowplayer-3.1.4.min.js');
		$templateEngine->append('js', 'feedback.js');
		$templateEngine->append('js', 'widgets/help.widget.js');
		$templateEngine->append('js', 'models/accounts.model.js');
		$templateEngine->append('js', 'models/category.model.js');
		$templateEngine->append('js', 'widgets/accounts/accountsPanel.widget.js');
		$templateEngine->append('js', 'widgets/operations/operationEdit.widget.js');
		
		if(IS_DEMO)
		{
			$templateEngine->append('js',  'demo_message.js');
		}
	}
}
