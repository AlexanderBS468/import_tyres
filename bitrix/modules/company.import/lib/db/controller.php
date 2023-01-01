<?php

namespace Company\Import\DB;

use Bitrix\Main;
use Company\Import\Config;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Controller
{
	public static function createTables(array $classList = null):void
	{
		if ($classList === null)
		{
			$classList = static::getTablesClassList();
		}

		/** @var Main\ORM\Data\DataManager $className */
		foreach ($classList as $className)
		{
			( new Installer($className::getEntity()) )
				->check();
		}
	}

	/**
	 * @param $baseClassName
	 *
	 * @return array
	 */
	protected static function getTablesClassList():array
	{
		$baseDir = Config::getModulePath();
		$baseNamespace = Config::getNamespace();
		$directory = new RecursiveDirectoryIterator($baseDir);
		$iterator = new RecursiveIteratorIterator($directory);
		$result = [];

		/** @var \DirectoryIterator $entry */
		foreach ($iterator as $entry)
		{
			if ($entry->isFile()
				&& $entry->getExtension() === 'php')
			{
				$relativePath = str_replace($baseDir, '', $entry->getPath());
				$namespace = $baseNamespace . str_replace('/', '\\', $relativePath) . '\\';
				$className = $entry->getBasename('.php');

				if (!preg_match('/table$/', $className))
				{
					$className .= 'Table';
				}

				$fullClassName = $namespace . $className;

				if ( class_exists($fullClassName) )
				{
					$result[] = mb_strtolower($fullClassName);
				}
			}
		}

		return array_unique($result);
	}

	/**
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\Db\SqlQueryException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function dropTables():void
	{
		$classList = static::getTablesClassList();

		/** @var Main\ORM\Data\DataManager $className */
		foreach ($classList as $className)
		{
			$entity = $className::getEntity();
			$connection = $entity->getConnection();
			$tableName = $entity->getDBTableName();

			if ($connection->isTableExists($tableName))
			{
				$connection->dropTable($tableName);
			}
		}
	}
}
