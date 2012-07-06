<?php
/**
 * preferences_patch_0400
 * @package modules.preferences
 */
class preferences_patch_0400 extends change_Patch
{
	/**
	 * Entry point of the patch execution.
	 */
	public function execute()
	{
		$rfid = ModuleService::getInstance()->getRootFolderId('preferences');
		$tn = TreeService::getInstance()->getInstanceByDocumentId($rfid);
		
		foreach ($tn->getChildren() as $cn)
		{
			/* @var $cn f_persistentdocument_PersistentTreeNode */
			try
			{
				$document = $cn->getPersistentDocument();
				$mn = $document->getDocumentModelName();
				list($package, $name) = explode('/', $mn);
				if ($name === 'preferences')
				{
					$moduleName = ModuleService::getInstance()->getShortModuleName($package);
					$labelKey = 'm.' . $moduleName . '.bo.general.module-name';
				
					foreach ($document->getI18nInfo()->getLangs() as $lang)
					{
						/* @var $lang string */
						RequestContext::getInstance()->setLang($lang);
						if ($document->getLabel() != $labelKey)
						{
							$document->setLabel($labelKey);
							$document->save();
						}
					}
				}
			} 
			catch (Exception $e) 
			{
				Framework::exception($e);
			}
		}
	}
	
	/**
	 * @return string
	 */
	public function getExecutionOrderKey()
	{
		return '2012-07-06 09:04:49';
	}
		
	/**
	 * @return string
	 */
	public function getBasePath()
	{
		return dirname(__FILE__);
	}
	
	/**
	 * @return false
	 */
	public function isCodePatch()
	{
		return false;
	}
}