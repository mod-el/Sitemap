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

		foreach ($config['controllers'] as $controller) {
			$element = $this->model->_Router->getElementFor($controller);

			if ($element) {
				$elements = $this->model->all($element);
				foreach ($elements as $el) {
					$url = $el->getUrl();
					if (isset($arr[$url]))
						continue;
					$arr[$url] = [
						'loc' => $url,
					];
				}
			} else {
				$url = $this->model->getUrl($controller);
				if (isset($arr[$url]))
					continue;
				$arr[$url] = [
					'loc' => $url,
				];
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
