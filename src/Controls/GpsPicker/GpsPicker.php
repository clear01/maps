<?php
declare(strict_types = 1);

namespace Clear01\Maps\Controls\GpsPicker;

use Clear01\Maps\Exceptions\MapException;
use Clear01\Maps\Models\GpsCoords;
use Clear01\Maps\Models\Settings;
use Nette\Application\UI\ITemplate;
use Nette\Application\UI\Presenter;
use Nette\Forms\Container;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Utils\Html;

class GpsPicker extends TextInput
{

	/**
	 * @var ITemplate
	 */
	protected $template;

	protected $settings;


	public function __construct(string $apiKey, string $suggestionApiKey, $label = null, $maxLength = null)
	{
		parent::__construct($label, $maxLength);

		$this->monitor(Presenter::class, function (Presenter $presenter) use ($apiKey, $suggestionApiKey) {
			$this->template = $presenter->getTemplateFactory()->createTemplate();
			$this->settings = new Settings(['inputId' => $this->getHtmlId(), 'apiKey' => $apiKey,'suggestionApiKey' => $suggestionApiKey, 'height' => '400px', 'defaultZoom' => 13]);
		});

		$this->addCondition(Form::FILLED)->addRule(Form::PATTERN, 'Gps souřadnice musí být ve tvaru "xx.xxxxx yy.yyyyy" kde x je zeměpisná šířka a y je zeměpisná dělka.', '^(\d*\.)?\d*N?\s+(\d*\.)?\d*E?$');
	}


	public static function register(string $apiKey, string $suggestionApiKey)
	{
		Container::extensionMethod(
			'addGpsPicker',
			function ($container, $name, $label = null, $maxLength = null) use ($apiKey, $suggestionApiKey) {
				return $container[$name] = new GpsPicker($apiKey, $suggestionApiKey, $label, $maxLength);
			}
		);
	}


	public function getIdentifier()
	{
		return "map-" . $this->getHtmlId();
	}


	public function getControl(): Html
	{
		$control = parent::getControl();
		$this->template->settings = $this->settings;
		$this->template->identifier = $this->getIdentifier();
		$this->template->gps = $this->getGps();
		$this->template->control = $control->class('form-control');
		$this->template->setFile(__DIR__ . '/template.latte');

		return Html::el('')->setHtml($this->template->render());
	}

	public function updateSettings(string $key, $value)
	{
		$this->settings[$key] = $value;
	}


	public function getGps(): ?GpsCoords
	{
		if ($this->value instanceof GpsCoords) {
			return $this->value;
		}
		if (is_string($this->value) && !empty($this->value)) {
			$a = explode(" ", $this->value);
			if (count($a) != 2) {
				return null;
			}

			return new GpsCoords((float) $a[0], (float) $a[1]);
		}

		return null;
	}
}
