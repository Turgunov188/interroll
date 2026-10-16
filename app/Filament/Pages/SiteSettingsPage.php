<?php

namespace App\Filament\Pages;

use App\Actions\Settings\SaveSiteSettings;
use App\Enums\Locale;
use App\Enums\MediaType;
use App\Enums\SettingGroup;
use App\Enums\SettingKey;
use App\Enums\SettingKind;
use App\Services\Site\SiteSettings;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use UnitEnum;

/**
 * «Настройки сайта»: contacts, hours, numbers and photos shown on the public site.
 * The fields come from App\Enums\SettingKey, so a new setting needs no change here.
 */
class SiteSettingsPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static string|UnitEnum|null $navigationGroup = 'Настройки';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Настройки сайта';

    protected static ?string $title = 'Настройки сайта';

    protected static ?string $slug = 'site-settings';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->isAdmin();
    }

    public function mount(): void
    {
        $stored = app(SiteSettings::class)->all();
        $state = [];

        foreach (SettingKey::cases() as $key) {
            $state[$key->value] = $stored[$key->value] ?? null;
        }

        $this->form->fill($state);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Tabs::make('settings')
                    ->persistTabInQueryString()
                    ->columnSpanFull()
                    ->tabs(array_map(fn (SettingGroup $group): Tab => Tab::make($group->getLabel())
                        ->icon($group->icon())
                        ->columns(2)
                        ->schema(array_map(fn (SettingKey $key): Component => $this->field($key), SettingKey::inGroup($group))),
                        SettingGroup::cases())),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')->label('Сохранить')->submit('save')->keyBindings(['mod+s']),
                    ]),
                ]),
        ]);
    }

    public function save(): void
    {
        app(SaveSiteSettings::class)->handle($this->form->getState());

        Notification::make()->success()->title('Сохранено — изменения уже на сайте')->send();
    }

    private function field(SettingKey $key): Component
    {
        $kind = $key->kind();

        if ($kind->isLocalized()) {
            return Fieldset::make($key->label())
                ->columns(2)
                ->columnSpanFull()
                ->schema(array_map(function (Locale $locale) use ($key, $kind) {
                    $name = "{$key->value}.{$locale->value}";
                    $label = strtoupper($locale->value);

                    return $kind === SettingKind::Note
                        ? Textarea::make($name)->label($label)->rows(2)->maxLength(500)->helperText($locale->isMain() ? $key->hint() : null)
                        : TextInput::make($name)->label($label)->maxLength(255)->helperText($locale->isMain() ? $key->hint() : null);
                }, Locale::cases()));
        }

        $field = match ($kind) {
            SettingKind::Image => FileUpload::make($key->value)
                ->image()
                ->disk(MediaType::DISK)
                ->directory('site')
                ->visibility('public')
                ->maxSize(8192)
                ->imageEditor(),
            SettingKind::Number => TextInput::make($key->value)->numeric(),
            SettingKind::Phone => TextInput::make($key->value)->tel()->maxLength(32),
            default => TextInput::make($key->value)->maxLength(255),
        };

        return $field->label($key->label())->helperText($key->hint());
    }
}
