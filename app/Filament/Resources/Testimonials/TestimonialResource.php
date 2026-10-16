<?php

namespace App\Filament\Resources\Testimonials;

use App\Enums\MediaType;
use App\Filament\Resources\Testimonials\Pages\ManageTestimonials;
use App\Filament\Support\Localized;
use App\Models\Testimonial;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

/** Customer reviews in «Нам доверяют» (factory page). */
class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static string|UnitEnum|null $navigationGroup = 'Контент';

    protected static ?int $navigationSort = 6;

    protected static ?string $modelLabel = 'отзыв';

    protected static ?string $pluralModelLabel = 'Отзывы';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Localized::tabs('text', 'Отзыв'),
                TextInput::make('author')->label('Автор')->placeholder('Имя, должность, предприятие')->required()->maxLength(255)->columnSpanFull(),
                FileUpload::make('photo')->label('Фото / логотип')->image()->disk(MediaType::DISK)->directory('testimonials')->visibility('public')->maxSize(2048),
                Toggle::make('is_published')->label('Показывать')->default(true),
                TextInput::make('sort')->label('Порядок')->numeric()->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->columns([
                TextColumn::make('text_display')->label('Отзыв')->state(fn (Testimonial $record) => $record->tr('text'))->limit(80)->wrap(),
                TextColumn::make('author')->label('Автор'),
                ToggleColumn::make('is_published')->label('На сайте'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageTestimonials::route('/')];
    }
}
