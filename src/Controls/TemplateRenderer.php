<?php
declare(strict_types = 1);

namespace Clear01\Maps\Controls;

use Latte\Engine;
use Nette\Application\UI\Presenter;
use Nette\Bridges\ApplicationLatte\Template;

/**
 * Renders a Latte template for a form control.
 * Uses the presenter's template factory (cache, filters) when the form is attached to a presenter,
 * otherwise a plain Latte engine, so the control works in standalone forms too.
 */
trait TemplateRenderer
{

	protected function renderTemplate(string $file, array $params): string
	{
		$presenter = $this->lookup(Presenter::class, false);
		if ($presenter instanceof Presenter) {
			$template = $presenter->getTemplateFactory()->createTemplate();
			if ($template instanceof Template) {
				return $template->renderToString($file, $params);
			}
		}

		return (new Engine())->renderToString($file, $params);
	}
}
