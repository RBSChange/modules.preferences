<?php
/**
 * preferences_ImportAction
 * @package modules.preferences.actions
 */
class preferences_ImportAction extends f_action_BaseJSONAction
{
	/**
	 * @see f_action_BaseAction::isDocumentAction()
	 *
	 * @return Boolean
	 */
	protected function isDocumentAction()
	{
		return false;
	}
	
	/**
	 * @param Context $context
	 * @param Request $request
	 */
	public function _execute($context, $request)
	{
		$pms = preferences_ModuleService::getInstance();
		//$result = $pms->getPreferencesDocumentInfo();
		$result = $pms->importPreferencesDocuments();
		return $this->sendJSON($result);
	}
}