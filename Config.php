<?php namespace Model\Sitemap;

use Model\Core\Module_Config;

class Config extends Module_Config
{
	public $configurable = false;

	/**
	 * @throws \Exception
	 */
	protected function assetsList()
	{
		$this->addAsset('config', 'config.php', function () {
			return "<?php\n\$config = [\n   'controllers' => [],\n];\n";
		});
	}

	/**
	 * Rules for API actions
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function getRules(): array
	{
		return [
			'rules' => [
				'sitemap' => 'sitemap.xml',
			],
			'controllers' => [
				'Sitemap',
			],
		];
	}

	public function getConfigData(): ?array
	{
		return [];
	}
}
