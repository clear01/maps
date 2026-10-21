<?php
declare(strict_types = 1);

namespace Clear01\Maps\Controls\AddressSuggestion;

use Clear01\Maps\Controls\TemplateRenderer;
use Nette\Forms\Container;
use Nette\Forms\Controls\TextInput;
use Nette\Utils\Html;
use Stringable;

class AddressSuggestion extends TextInput
{
	use TemplateRenderer;

	public const TEMPLATE = __DIR__ . '/template.latte';

	protected string $apiKey;


	public function __construct(string $apiKey, string|Stringable|null $label = null, ?int $maxLength = null)
	{
		parent::__construct($label, $maxLength);
		$this->apiKey = $apiKey;
	}


	public static function register(string $apiKey): void
	{
		Container::extensionMethod(
			'addAddressSuggestion',
			function (Container $container, string $name, string|Stringable|null $label = null, ?int $maxLength = null) use ($apiKey): self {
				return $container[$name] = new self($apiKey, $label, $maxLength);
			}
		);
	}


	public function getControl(): Html
	{
		$control = parent::getControl();

		return Html::el()->setHtml($this->renderTemplate(self::TEMPLATE, [
			'inputId' => $this->getHtmlId(),
			'apiKey' => $this->apiKey,
			'input' => $control->class('form-control'),
		]));
	}
}
