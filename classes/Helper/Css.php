<?php

class Helper_Css implements _Core_Router_iHook
{
	public static function execRouterHook( _Core_Request $request, &$class, &$method, array &$chunks, &$templateEngine )
	{
		
		if ( CSS_MINIFY )
		{
			$templateEngine->append('css', 'global-min.css');
		}
		else
		{
			$templateEngine->append('css', 'main.css');
			$templateEngine->append('css', 'jquery/south-street/ui.all.css');
			//$tpl->append('css', 'jquery/south-street/ui.base.css');
			$templateEngine->append('css', 'jquery/south-street/ui.core.css');
			$templateEngine->append('css', 'jquery/south-street/ui.resizable.css');
			$templateEngine->append('css', 'jquery/south-street/ui.dialog.css');
			$templateEngine->append('css', 'jquery/south-street/ui.tabs.css');
			$templateEngine->append('css', 'jquery/south-street/ui.datepicker.css');
			
			$templateEngine->append('css', 'jquery/jquery.jgrowl.css');
			$templateEngine->append('css', 'jquery/fullcalendar.css');
		}

		$templateEngine->append('css', 'menuUser.css');
		
		$templateEngine->append('css', 'jquery/jHtmlArea.css');
		$templateEngine->append('css', 'jquery/jHtmlArea.ColorPickerMenu.css');
		
		$templateEngine->append('css', 'jquery/fancy.css');
		
		$templateEngine->append('css', 'jquery/sexyCombo.css');
		$templateEngine->append('css', 'calendar.css');
		$templateEngine->append('css', 'report.css');
		$templateEngine->append('css', 'expert.css');
		$templateEngine->append('css', 'expertsList.css');
		$templateEngine->append('css', 'operationsJournal.css');
		$templateEngine->append('css', 'budgetMaster.css');
		$templateEngine->append('css', 'budget.css');
                $templateEngine->append('css', 'accountsPanel.css');
	}
}
