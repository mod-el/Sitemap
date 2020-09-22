<?php namespace Model\Sitemap;

use Model\Core\Module;

class Sitemap extends Module
{
	/**
	 * @return \Generator
	 */
	public function getPages(): \Generator
	{
		$config = $this->retrieveConfig();

		foreach ($config['controllers'] as $idx => $controller) {
			$options = [
				'priority' => 1,
				'lastmod' => null,
			];

			if (!is_numeric($idx) and is_array($controller)) {
				$options = array_merge($options, $controller);
				$controller = $idx;
			}

			$lastmod = null;
			if ($options['lastmod']) {
				if (is_array($options['lastmod'])) {
					if (isset($options['lastmod']['files']) and is_array($options['lastmod']['files'])) {
						$timestamps = [];
						foreach ($options['lastmod']['files'] as $file) {
							if (file_exists(INCLUDE_PATH . $file))
								$timestamps[] = filemtime(INCLUDE_PATH . $file);
						}

						if (count($timestamps) > 0) {
							$timestamp = max($timestamps);
							$lastmod = date_create('@' . $timestamp)->format('Y-m-d');
						}
					}
				} else {
					$lastmod = $options['lastmod'];
				}
			}

			$rules = $this->model->_Router->getRulesFor($controller);
			foreach ($rules as $rIdx => $r) {
				$table = null;
				if ($r['options']['element'])
					$table = $this->model->_ORM->getTableFor($r['options']['element']);
				elseif ($r['options']['table'])
					$table = $r['options']['table'];

				if ($table) {
					$elements = $this->model->_Db->select_all($table, $options['where'] ?? []);
					foreach ($elements as $el) {
						$url = $this->model->getUrl($controller, $el['id'], [], ['idx' => $rIdx]);

						if ($lastmod === null and isset($options['lastmod']['field'])) {
							$lastmod = $el[$options['lastmod']['field']];
							if ($lastmod)
								$lastmod = date_create($lastmod)->format('Y-m-d');
						}
						yield [
							'loc' => $url,
							'priority' => $options['priority'],
							'lastmod' => $lastmod,
						];
					}
				} else {
					$url = $this->model->getUrl($controller, null, [], ['idx' => $rIdx]);

					yield [
						'loc' => $url,
						'priority' => $options['priority'],
						'lastmod' => $lastmod,
					];
				}
			}
		}
	}

	/**
	 * @param array $request
	 * @param string $rule
	 * @return array|null
	 */
	public function getController(array $request, string $rule): ?array
	{
		if ($rule !== 'sitemap')
			return null;

		return [
			'controller' => 'Sitemap',
		];
	}
}
