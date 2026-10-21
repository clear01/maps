<?php
declare(strict_types = 1);

namespace Clear01\Maps\Controls\GpsPicker;

use Clear01\Maps\Controls\TemplateRenderer;
use Clear01\Maps\Exceptions\MapException;
use Clear01\Maps\Models\GpsCoords;
use Clear01\Maps\Models\Settings;
use Nette\Forms\Container;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Utils\Html;
use Stringable;

class GpsPicker extends TextInput
{
	use TemplateRenderer;

	public const TEMPLATE = __DIR__ . '/template.latte';

	protected Settings $settings;


	public function __construct(string $apiKey, string $suggestionApiKey, string|Stringable|null $label = null, ?int $maxLength = null)
	{
		parent::__construct($label, $maxLength);

		$this->settings = new Settings(['apiKey' => $apiKey, 'suggestionApiKey' => $suggestionApiKey, 'height' => '400px', 'defaultZoom' => 13]);

		$this->addCondition(Form::Filled)
		     ->addRule(Form::Pattern, 'Gps souřadnice musí být ve tvaru "xx.xxxxx yy.yyyyy" kde x je zeměpisná šířka a y je zeměpisná dělka.', '^-?(\d*\.)?\d*N?\s+-?(\d*\.)?\d*E?$')
		     ->addRule([self::class, 'validateGps'], 'Neplatné GPS souřadnice.');
	}


	public static function register(string $apiKey, string $suggestionApiKey): void
	{
		Container::extensionMethod(
			'addGpsPicker',
			function (Container $container, string $name, string|Stringable|null $label = null, ?int $maxLength = null) use ($apiKey, $suggestionApiKey): self {
				return $container[$name] = new self($apiKey, $suggestionApiKey, $label, $maxLength);
			}
		);
	}


	/**
	 * Validator: the value must be parseable coordinates within range.
	 */
	public static function validateGps(self $control): bool
	{
		try {
			return $control->getGps() !== null;
		} catch (MapException $e) {
			return false;
		}
	}


	/**
	 * Accepts GpsCoords, string "latitude longitude" or null.
	 */
	public function setValue($value): static
	{
		if ($value instanceof GpsCoords) {
			$value = (string) $value;
		}

		return parent::setValue($value);
	}


	public function getIdentifier(): string
	{
		return "map-" . $this->getHtmlId();
	}


	public function getControl(): Html
	{
		$control = parent::getControl();
		$settings = clone $this->settings;
		$settings->inputId = $this->getHtmlId();

		try {
			$gps = $this->getGps();
		} catch (MapException $e) {
			$gps = null;
		}

		return Html::el()->setHtml($this->renderTemplate(self::TEMPLATE, [
			'settings' => $settings,
			'identifier' => $this->getIdentifier(),
			'gps' => $gps,
			'input' => $control->class('form-control'),
		]));
	}


	public function updateSettings(string $key, mixed $value): void
	{
		$this->settings[$key] = $value;
	}


	/**
	 * @throws MapException when the coordinates are out of range
	 */
	public function getGps(): ?GpsCoords
	{
		$value = $this->getValue();
		if ($value instanceof GpsCoords) {
			return $value;
		}
		if (is_string($value)) {
			return GpsCoords::fromString($value);
		}

		return null;
	}
}
