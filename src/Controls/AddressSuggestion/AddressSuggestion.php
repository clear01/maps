<?php
declare(strict_types = 1);

namespace Clear01\Maps\Controls\AddressSuggestion;

use Clear01\Maps\Exceptions\MapException;
use Clear01\Maps\Models\GpsCoords;
use Clear01\Maps\Models\Settings;
use Nette\Application\UI\ITemplate;
use Nette\Application\UI\Presenter;
use Nette\Forms\Container;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Utils\Html;

class AddressSuggestion extends TextInput
{

	/**
	 * @var ITemplate
	 */
	protected $template;

	protected $apiKey;


	public function __construct(string $apiKey, $label = null, $maxLength = null)
	{
		parent::__construct($label, $maxLength);
		$this->monitor(Presenter::class, function (Presenter $presenter) {
			$this->template = $presenter->getTemplateFactory()->createTemplate();
		});
		$this->apiKey = $apiKey;
	}


	public static function register(string $apiKey)
	{
		Container::extensionMethod(
			'addAddressSuggestion',
			function ($container, $name, $label = null, $maxLength = null) use ($apiKey) {
				return $container[$name] = new AddressSuggestion($apiKey, $label, $maxLength);
			}
		);
	}


	public function getControl(): Html
	{
		$control = parent::getControl();
		$this->template->inputId = $this->getHtmlId();
		$this->template->apiKey = $this->apiKey;
		$this->template->control = $control->class('form-control');
		$this->template->setFile(__DIR__ . '/template.latte');

		return Html::el('')->setHtml($this->template->render());
	}

}
