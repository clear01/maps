<?php
declare(strict_types = 1);

namespace Clear01\Maps\Tests;

use Latte\Engine;
use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;
use Nette\Bridges\ApplicationLatte\LatteFactory;
use Nette\Bridges\ApplicationLatte\TemplateFactory;
use Nette\Bridges\ApplicationLatte\UIExtension;
use Nette\Bridges\FormsLatte\FormsExtension;
use Nette\Http\Request;
use Nette\Http\Response;
use Nette\Http\UrlScript;

class TestPresenter extends Presenter
{
}


final class Helpers
{
	/** number of templates created by the presenter's template factory */
	public static int $templatesCreated = 0;


	public static function createLatteFactory(): LatteFactory
	{
		return new class implements LatteFactory {
			public function create(?Control $control = null): Engine
			{
				$latte = new Engine();
				$latte->setTempDirectory(TEMP_DIR . '/latte');
				$latte->setStrictTypes(true);
				$latte->addExtension(new UIExtension($control));
				$latte->addExtension(new FormsExtension());

				return $latte;
			}
		};
	}


	/**
	 * Presenter with a template factory, so components can create templates without the application.
	 */
	public static function createPresenter(): TestPresenter
	{
		$templateFactory = new TemplateFactory(latteFactory: self::createLatteFactory());
		$templateFactory->onCreate[] = function (): void {
			self::$templatesCreated++;
		};
		$presenter = new TestPresenter();
		$presenter->injectPrimary(
			httpRequest: new Request(new UrlScript('https://example.com/')),
			httpResponse: new Response(),
			templateFactory: $templateFactory,
		);

		return $presenter;
	}
}
