<?php

namespace App\Filament\Resources\Orders;

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\Pages\CreateOrder;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\RelationManagers\EventsRelationManager;
use App\Models\Order;
use App\Models\User;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string|UnitEnum|null $navigationGroup = 'CRM';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'заказ';

    protected static ?string $pluralModelLabel = 'Заказы';

    protected static ?string $recordTitleAttribute = 'code';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Отслеживание')
                ->description('Клиент видит статус на странице «Отслеживание заказа» по этому коду и PIN.')
                ->columns(3)
                ->columnSpanFull()
                ->visibleOn('edit')
                ->schema([
                    TextInput::make('code')->label('Код')->disabled()->dehydrated(false),
                    TextInput::make('pin')->label('PIN')->disabled()->dehydrated(false),
                    TextInput::make('lead_number')
                        ->label('Заявка')
                        ->disabled()
                        ->dehydrated(false)
                        ->formatStateUsing(fn (?Order $record) => $record?->lead?->number ?? '—'),
                ]),
            Section::make('Клиент')
                ->columns(3)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('customer_name')->label('Имя')->required()->maxLength(255),
                    TextInput::make('customer_phone')->label('Телефон')->tel()->required()->maxLength(32),
                    TextInput::make('company')->label('Компания')->maxLength(255),
                ]),
            Section::make('Производство')
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    Select::make('status')
                        ->label('Этап')
                        ->options(OrderStatus::class)
                        ->default(OrderStatus::Accepted)
                        ->required(),
                    DatePicker::make('due_date')->label('Дата готовности')->native(false)->displayFormat('d.m.Y'),
                    TextInput::make('total')->label('Сумма')->numeric()->suffix('сум'),
                    Select::make('manager_id')
                        ->label('Ответственный менеджер')
                        ->relationship('manager', 'name', fn (Builder $query) => $query->where('is_active', true))
                        ->default(fn () => auth()->id())
                        ->searchable()
                        ->preload(),
                    Textarea::make('notes')
                        ->label('Внутренний комментарий (клиент не видит)')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('code')->label('Код')->searchable()->sortable()->weight(FontWeight::Bold),
                TextColumn::make('customer_name')
                    ->label('Клиент')
                    ->description(fn (Order $record) => $record->company)
                    ->searchable(['customer_name', 'company', 'customer_phone']),
                TextColumn::make('status')->label('Этап')->badge(),
                TextColumn::make('due_date')
                    ->label('Срок')
                    ->date('d.m.Y')
                    ->sortable()
                    ->color(fn (Order $record) => $record->due_date?->isPast() && ! in_array($record->status, [OrderStatus::Delivered, OrderStatus::Cancelled], true) ? 'danger' : null)
                    ->placeholder('—'),
                TextColumn::make('total')->label('Сумма')->numeric(0)->suffix(' сум')->placeholder('—')->toggleable(),
                TextColumn::make('manager.name')->label('Менеджер')->placeholder('—'),
                TextColumn::make('created_at')->label('Открыт')->date('d.m.Y')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->label('Этап')->options(OrderStatus::class)->multiple(),
                SelectFilter::make('manager_id')->label('Менеджер')->relationship('manager', 'name'),
                Filter::make('overdue')
                    ->label('Просрочен')
                    ->query(fn (Builder $query) => $query
                        ->whereDate('due_date', '<', now())
                        ->whereNotIn('status', [OrderStatus::Delivered->value, OrderStatus::Cancelled->value])),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    /** Sales managers only see their own and unassigned orders. */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['manager', 'lead']);

        /** @var User $user */
        $user = auth()->user();

        if ($user && ! $user->isAdmin()) {
            $query->where(fn (Builder $q) => $q->whereNull('manager_id')->orWhere('manager_id', $user->id));
        }

        return $query;
    }

    public static function getRelations(): array
    {
        return [
            EventsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'create' => CreateOrder::route('/create'),
            'edit' => EditOrder::route('/{record}/edit'),
        ];
    }
}
