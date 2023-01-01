<?php

namespace Company\Import\DB;

use Bitrix\Main;

class Installer
{
	protected $entity;
	protected $connection;

	public function __construct(Main\ORM\Entity $entity)
	{
		$this->entity = $entity;
		$this->connection = $entity->getConnection();
	}

	public function check() : void
	{
		if ( !$this->connection->isTableExists($this->entity->getDBTableName()) )
		{
			$this->createTable();
			$this->createIndexes();
			$this->alterArrayText();
		}
	}

	protected function createTable() : void
	{
		$this->entity->createDbTable();
	}

	protected function createIndexes() : void
	{
		$className = $this->entity->getDataClass();

		if (!is_subclass_of($className, Main\ORM\Data\DataManager::class)) { return; }

		$connection = $this->connection;
		$tableName = $this->entity->getDBTableName();

		foreach ($className::getTableIndexes() as $index => $fields)
		{
			if ($connection->isIndexExists($tableName, $fields)) { continue; }

			$name = 'IX_' . $tableName . '_' . $index;
			$connection->createIndex($tableName, $name, $fields);
		}
	}

	protected function alterArrayText() : void
	{
		foreach ($this->entity->getFields() as $field)
		{
			if (!($field instanceof Main\ORM\Fields\ArrayField)) { continue; }

			$sqlHelper = $this->connection->getSqlHelper();
			$tableName = $this->entity->getDBTableName();
			$columnName = $field->getColumnName();

			$this->connection->queryExecute(sprintf(
				'ALTER TABLE %s MODIFY COLUMN %s text',
				$sqlHelper->quote($tableName),
				$sqlHelper->quote($columnName)
			));
		}
	}
}