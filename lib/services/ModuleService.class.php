<?php
/**
 * @package modules.preferences.lib.services
 */
class preferences_ModuleService extends ModuleBaseService
{
	/**
	 * Singleton
	 * @var preferences_ModuleService
	 */
	private static $instance = null;
	
	/**
	 * @return preferences_ModuleService
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * @return array
	 */
	private function getPreferencesDocumentModels()
	{
		$result = array();
		$modulesModels = f_persistentdocument_PersistentDocumentModel::getDocumentModelNamesByModules();
		foreach ($modulesModels as $moduleName => $modelNameArray)
		{
			if (ModuleService::getInstance()->getModule($moduleName)->isVisible())
			{
				foreach ($modelNameArray as $modelName)
				{
					if (strpos($modelName, '/preferences'))
					{
						$result[$moduleName] = $modelName;
						break;
					}
				}
			}
		}
		return $result;
	}
	
	/**
	 * Returns the preferences document of the specified module.
	 *
	 * @param string $moduleName The name of the module.
	 * @return object The preferences document.
	 */
	private function getPreferencesDocument($modelName)
	{
		// Check if User exist in database
		$persistentProvider = f_persistentdocument_PersistentProvider::getInstance();
		return $persistentProvider->createQuery($modelName, false)->findUnique();
	}
	
	/**
	 * @return array
	 */
	public function getPreferencesDocumentInfo()
	{
		$result = array();
		foreach ($this->getPreferencesDocumentModels() as $moduleName => $modelName) 
		{
			$result[$moduleName] = array('modelName' => $modelName);
			if (uixul_ModuleBindingService::getInstance()->hasConfigFile($moduleName))
			{
				$result[$moduleName]['version'] = 'v3';
			}
			else
			{
				$result[$moduleName]['version'] = 'v2';
			}
		}
		
		return $result;
	}
	
	/**
	 * @return string
	 */
	public function getPerspectiveDocument()
	{
		$string = array();
		foreach ($this->getPreferencesDocumentModels() as $moduleName => $modelName) 
		{
			if (!uixul_ModuleBindingService::getInstance()->hasConfigFile($moduleName))
			{
				continue;
			}
			$model = f_persistentdocument_PersistentDocumentModel::getInstanceFromDocumentModelName($modelName);
			$string[] = uixul_DocumentEditorService::getInstance()->getPerspectiveDocument('preferences', $model);	
			
		}
		return implode("\n", $string);
	}
	
	/**
	 * @return array
	 */
	public function importPreferencesDocuments()
	{
		$result = array();
		$tm = f_persistentdocument_TransactionManager::getInstance();
		try
		{
			$tm->beginTransaction();
			$rootFolderId = ModuleService::getInstance()->getRootFolderId('preferences');
			$modelNameArray = $this->getPreferencesDocumentModels();
			$ts = TreeService::getInstance();
			
			foreach ($modelNameArray as $moduleName => $modelName)
			{
				$document = $this->getPreferencesDocument($modelName);
				if ($document !== null)
				{
					$node = $ts->getInstanceByDocument($document);
					if ($node === null)
					{
						$ts->newLastChild($rootFolderId, $document->getId());
						$result[] = array('id' => $document->getId(), 'AddInTree' => $rootFolderId);
					}
					else if ($node->getTreeId() != $rootFolderId)
					{
						$ts->deleteNode($node);
						$ts->newLastChild($rootFolderId, $document->getId());
						$result[] = array('id' => $document->getId(), 'MoveInTree' => $rootFolderId);
					}
					else
					{
						$document->setLabel("&modules.$moduleName.bo.general.Module-name;");
						$document->setPublicationstatus("PUBLICATED");
						if ($document->isModified())
						{
							$tm->getPersistentProvider()->updateDocument($document);
							$result[] = array('id' => $document->getId(), 'UPDATED' => true);
						}
					}
				}
				else
				{
					$ds = f_persistentdocument_PersistentDocumentModel::getInstanceFromDocumentModelName($modelName)->getDocumentService();
					$document = $ds->getNewDocumentInstance();
					$document->setLabel("&modules.$moduleName.bo.general.Module-name;");
					$document->setPublicationstatus("PUBLICATED");
					$tm->getPersistentProvider()->insertDocument($document);
					$ts->newLastChild($rootFolderId, $document->getId());
					$result[] = array('id' => $document->getId(), 'CREATED' => true);
				}
			}
			$tm->commit();
		}
		catch (Exception $e)
		{
			$tm->rollBack($e);
		}
		return $result;
	}
}