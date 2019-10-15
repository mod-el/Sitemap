<?php namespace Model\Sitemap;

use Model\Core\Module;

class Sitemap extends Module
{
	/**
	 * @return array
	 */
	public function getPages(): array
	{
		$config = $this->retrieveConfig();

		$arr = [];

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
				if ($r['options']['element']) {
					$elements = $this->model->all($r['options']['element']);
					foreach ($elements as $el) {
						$url = $el->getUrl([], ['idx' => $rIdx]);
						if (isset($arr[$url]))
							continue;

						if ($lastmod === null and isset($options['lastmod']['field'])) {
							$lastmod = $el[$options['lastmod']['field']];
							if ($lastmod)
								$lastmod = date_create($lastmod)->format('Y-m-d');
						}
						$arr[$url] = [
							'loc' => $url,
							'priority' => $options['priority'],
							'lastmod' => $lastmod,
						];
					}
				} else {
					$url = $this->model->getUrl($controller, null, [], ['idx' => $rIdx]);
					if (isset($arr[$url]))
						continue;

					$arr[$url] = [
						'loc' => $url,
						'priority' => $options['priority'],
						'lastmod' => $lastmod,
					];
				}
			}
		}

		return array_values($arr);
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
