<?php namespace Model\Sitemap\Controllers;

use Model\Core\Controller;

class SitemapController extends Controller
{
	public function init()
	{
		header('Content-Type: text/xml');
	}

	public function index()
	{
		$pages = $this->model->_Sitemap->getPages();

		$xml = new \SimpleXMLElement('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');
		foreach ($pages as $page) {
			$url = $xml->addChild('url');
			$url->addChild('loc', BASE_HOST . $page['loc']);
			$url->addChild('priority', $page['priority']);
			if($page['lastmod'])
				$url->addChild('lastmod', $page['lastmod']);
		}

		echo $xml->asXML();
		die();
	}
}
