<?php
namespace AntonioPrimera\TestScenarios\Support;

class DocComment
{
	public static function summary(?string $docComment): ?string
	{
		if (!$docComment)
			return null;

		$lines = preg_split('/\R/', $docComment) ?: [];

		foreach ($lines as $line) {
			$line = trim($line);
			$line = preg_replace('/^\/\*\*?/', '', $line);
			$line = preg_replace('/\*\/$/', '', $line);
			$line = preg_replace('/^\s*\*/', '', $line);
			$line = trim($line);

			if ($line === '' || str_starts_with($line, '@'))
				continue;

			return $line;
		}

		return null;
	}
}
