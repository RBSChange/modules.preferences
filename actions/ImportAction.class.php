<?php
/**
 * preferences_ImportAction
 * @package modules.preferences.actions
 */
class preferences_ImportAction extends change_JSONAction
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
	 * @param change_Context $context
	 * @param change_Request $request
	 */
	public function _execute($context, $request)
	{
		$pms = preferences_ModuleService::getInstance();
		//$result = $pms->getPreferencesDocumentInfo();
		$result = $pms->importPreferencesDocuments();
		return $this->sendJSON($result);
	}
}