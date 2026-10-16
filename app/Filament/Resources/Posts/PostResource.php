<?php

namespace App\Filament\Resources\Posts;

use App\Enums\Locale;
use App\Enums\MediaType;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Filament\Support\Localized;
use App\Models\Post;
use App\Models\PostCategory;
use App\Services\Site\SiteUrl;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

/** Blog «Советы инженера»: articles at /blog/{slug}. */
class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-newspaper';

    protected static string|UnitEnum|null $navigationGroup = 'Контент';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'статья';

    protected static ?string $pluralModelLabel = 'Блог';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Основное')
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    ...Localized::inputs('title', 'Заголовок', required: true, configure: fn (TextInput $input, Locale $locale) => $locale->isMain()
                        ? $input->live(onBlur: true)->afterStateUpdated(function (Set $set, ?string $state, string $operation) {
                            if ($operation === 'create') {
                                $set('slug', Str::slug((string) $state));
                            }
                        })
                        : $input),
                    TextInput::make('slug')
                        ->label('URL (slug)')
                        ->helperText('Адрес статьи: /blog/{slug}')
                        ->required()
                        ->alphaDash()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                    Select::make('post_category_id')
                        ->label('Рубрика')
                        ->relationship('category', 'slug')
                        ->getOptionLabelFromRecordUsing(fn (PostCategory $record) => $record->tr('name'))
                        ->createOptionForm([
                            ...Localized::inputs('name', 'Название', required: true),
                            TextInput::make('slug')->label('URL (slug)')->required()->alphaDash()->unique(PostCategory::class),
                        ])
                        ->preload()
                        ->searchable(),
                    ...Localized::inputs('author', 'Автор', configure: fn (TextInput $input) => $input->placeholder('Например: главный инженер')),
                    Localized::tabs('excerpt', 'Подзаголовок / анонс'),
                ]),
            Section::make('Текст статьи')
                ->description('Заголовки H2 попадают в оглавление сбоку.')
                ->columnSpanFull()
                ->schema([
                    Tabs::make('body')->tabs(array_map(fn (Locale $locale): Tab => Tab::make($locale->getLabel())->schema([
                        RichEditor::make("body.{$locale->value}")
                            ->hiddenLabel()
                            ->toolbarButtons([['bold', 'italic', 'link'], ['h2', 'h3', 'blockquote'], ['bulletList', 'orderedList'], ['attachFiles'], ['undo', 'redo']])
                            ->fileAttachmentsDisk(MediaType::DISK)
                            ->fileAttachmentsDirectory('posts')
                            ->fileAttachmentsVisibility('public'),
                    ]), Locale::cases())),
                ]),
            Section::make('Обложка и публикация')
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    FileUpload::make('cover')
                        ->label('Обложка')
                        ->image()
                        ->imageEditor()
                        ->disk(MediaType::DISK)
                        ->directory('posts')
                        ->visibility('public')
                        ->maxSize(8192),
                    Localized::tabs('cover_caption', 'Подпись к обложке'),
                    Toggle::make('is_published')->label('Опубликовано'),
                    Toggle::make('is_featured')->label('Главная статья блога')->helperText('Показывается крупно наверху'),
                    DateTimePicker::make('published_at')->label('Дата публикации')->helperText('Пусто — сразу; будущая дата — статья появится в этот день')->native(false),
                    TextInput::make('read_minutes')->label('Минут чтения')->numeric()->minValue(1)->helperText('Пусто — считается по тексту'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->columns([
                ImageColumn::make('cover')->label('')->disk(MediaType::DISK)->square(),
                TextColumn::make('title_display')->label('Статья')->state(fn (Post $record) => $record->tr('title'))->wrap(),
                TextColumn::make('category_display')->label('Рубрика')->state(fn (Post $record) => $record->category?->tr('name'))->placeholder('—'),
                IconColumn::make('is_featured')->label('Главная')->boolean(),
                ToggleColumn::make('is_published')->label('Опубликовано'),
                TextColumn::make('published_at')->label('Дата')->dateTime('d.m.Y')->placeholder('—')->sortable(),
            ])
            ->filters([
                SelectFilter::make('post_category_id')->label('Рубрика')->relationship('category', 'slug')
                    ->getOptionLabelFromRecordUsing(fn (PostCategory $record) => $record->tr('name')),
            ])
            ->recordActions([
                Action::make('open')
                    ->label('На сайте')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Post $record): string => app(SiteUrl::class)->post(Locale::main(), $record->slug), shouldOpenInNewTab: true)
                    ->visible(fn (Post $record): bool => $record->is_published),
                EditAction::make(),
            ])
            ->toolbarActions([DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosts::route('/'),
            'create' => CreatePost::route('/create'),
            'edit' => EditPost::route('/{record}/edit'),
        ];
    }
}
