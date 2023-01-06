<?php

namespace Company\Import;

class Glossary
{
	public const TYPE_OF_DATA_CATALOG = 'CATALOG';

	public static function getSeparatorValues() : array
	{
		return [
			'TZP',
			'ZPT',
			'TAB',
			'SPS',
		];
	}
}
