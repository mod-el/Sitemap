<?php namespace Model\Sitemap\Providers;

use Model\Router\AbstractRouterProvider;

class RouterProvider extends AbstractRouterProvider
{
	public static function getRoutes(): array
	{
		return [
			[
				'pattern' => '/sitemap.xml',
				'controller' => 'Sitemap',
			],
		];
	}
}
