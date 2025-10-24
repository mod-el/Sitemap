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

			$routes = $this->model->getRouter()->getRoutesForController($controller);
			foreach ($routes as $rIdx => $route) {
				$table = null;
				if ($route->options['model'])
					$table = $this->model->_ORM->getTableFor($route->options['model']);
				elseif ($route->options['table'])
					$table = $route->options['table'];

				if ($table) {
					$elements = \Model\Db\Db::getConnection()->selectAll($table, $options['where'] ?? []);
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
}
