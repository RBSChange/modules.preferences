<?php
/**
 * @package modules.preferences
 */
class preferences_GetTreeChildrenJSONAction extends generic_GetTreeChildrenJSONAction
{
	/**
	 * @see generic_GetTreeChildrenJSONAction::getTreeChildren()
	 *
	 * @param f_persistentdocument_PersistentDocument $document
	 * @param unknown_type $subModelNames
	 * @return array<f_persistentdocument_PersistentDocument>
	 */
	protected function getTreeChildren($document, $subModelNames)
	{
		if ($document instanceof generic_persistentdocument_rootfolder && ($this->getTreeType() & DocumentHelper::MODE_CUSTOM)) 
		{
			$result = array();
			foreach (parent::getTreeChildren($document, null) as $preferenceDocument)
			{
				$result[ucfirst($preferenceDocument->getLabel())] = $preferenceDocument;
			}
			ksort($result);
			return array_values($result);
		}
		return parent::getTreeChildren($document, $subModelNames);
	}
}