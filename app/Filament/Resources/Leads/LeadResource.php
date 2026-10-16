<?php

namespace App\Filament\Resources\Leads;

use App\Actions\Leads\CreateLead as CreateLeadAction;
use App\Enums\LeadStatus;
use App\Enums\LeadType;
use App\Enums\Locale;
use App\Filament\Resources\Leads\Pages\CreateLead;
use App\Filament\Resources\Leads\Pages\EditLead;
use App\Filament\Resources\Leads\Pages\ListLeads;
use App\Filament\Resources\Leads\RelationManagers\ItemsRelationManager;
use App\Http\Requests\StoreLeadRequest;
use App\Models\Lead;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use UnitEnum;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static string|UnitEnum|null $navigationGroup = 'CRM';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'заявка';

    protected static ?string $pluralModelLabel = 'Заявки';

    protected static ?string $recordTitleAttribute = 'number';

    /**
     * What the customer sent is shown read-only on the edit page; the manager works with
     * status, owner and internal notes. Manual leads (phone calls) are typed in on create.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Заявка с сайта')
                ->description('Данные, которые отправил клиент. Они не редактируются.')
                ->columns(3)
                ->columnSpanFull()
                ->visibleOn('edit')
                ->schema([
                    TextEntry::make('name')->label('Имя'),
                    TextEntry::make('phone')
                        ->label('Телефон')
                        ->copyable()
                        ->url(fn (Lead $record): string => 'tel:'.preg_replace('/[^\d+]/', '', $record->phone)),
                    TextEntry::make('company')->label('Компания')->placeholder('—'),
                    TextEntry::make('email')
                        ->label('Email')
                        ->placeholder('—')
                        ->url(fn (Lead $record): ?string => $record->email ? 'mailto:'.$record->email : null),
                    TextEntry::make('type')->label('Форма')->badge(),
                    TextEntry::make('locale')->label('Язык сайта'),
                    TextEntry::make('created_at')->label('Получена')->dateTime('d.m.Y H:i'),
                    TextEntry::make('message')
                        ->label('Комментарий клиента')
                        ->placeholder('—')
                        ->formatStateUsing(fn (?string $state): HtmlString => new HtmlString(nl2br(e((string) $state))))
                        ->columnSpanFull(),
                    ViewEntry::make('attachments')
                        ->label('Чертежи и фото от клиента')
                        ->view('filament.lead-attachments')
                        ->hidden(fn (?Lead $record): bool => empty($record?->attachments))
                        ->columnSpanFull(),
                ]),
            Section::make('Клиент')
                ->columns(2)
                ->columnSpanFull()
                ->visibleOn('create')
                ->schema([
                    TextInput::make('name')->label('Имя')->required()->maxLength(255),
                    TextInput::make('phone')->label('Телефон')->tel()->required()->maxLength(32),
                    TextInput::make('company')->label('Компания')->maxLength(255),
                    TextInput::make('email')->label('Email')->email()->maxLength(255),
                    Select::make('type')
                        ->label('Тип')
                        ->options(LeadType::class)
                        ->default(LeadType::Callback)
                        ->required(),
                    Select::make('locale')
                        ->label('Язык')
                        ->options(Locale::class)
                        ->default(Locale::main()),
                    Textarea::make('message')->label('Что нужно клиенту')->rows(4)->columnSpanFull(),
                    FileUpload::make('attachments')
                        ->label('Чертежи и фото')
                        ->multiple()
                        ->disk(CreateLeadAction::DISK)
                        ->visibility('private')
                        ->directory(CreateLeadAction::DIRECTORY)
                        ->maxSize(StoreLeadRequest::MAX_FILE_KB)
                        ->columnSpanFull(),
                ]),
            Section::make('Обработка')
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    Select::make('status')
                        ->label('Статус')
                        ->options(LeadStatus::class)
                        ->default(LeadStatus::New)
                        ->required(),
                    Select::make('manager_id')
                        ->label('Ответственный менеджер')
                        ->relationship('manager', 'name', fn (Builder $query) => $query->where('is_active', true))
                        ->default(fn () => auth()->id())
                        ->searchable()
                        ->preload(),
                    Textarea::make('notes')
                        ->label('Заметка менеджера')
                        ->helperText('Видно только сотрудникам.')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->poll('30s')
            ->columns([
                TextColumn::make('number')->label('№')->searchable()->sortable(),
                TextColumn::make('created_at')->label('Дата')->dateTime('d.m.Y H:i')->sortable(),
                TextColumn::make('name')
                    ->label('Клиент')
                    ->description(fn (Lead $record) => $record->company)
                    ->searchable(['name', 'company']),
                TextColumn::make('phone')->label('Телефон')->searchable()->copyable(),
                TextColumn::make('type')
                    ->label('Тип')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('items_count')->label('Детали')->counts('items'),
                TextColumn::make('status')->label('Статус')->badge(),
                TextColumn::make('manager.name')->label('Менеджер')->placeholder('— не назначен —'),
            ])
            ->filters([
                SelectFilter::make('status')->label('Статус')->options(LeadStatus::class)->multiple(),
                SelectFilter::make('type')->label('Тип')->options(LeadType::class),
                SelectFilter::make('manager_id')->label('Менеджер')->relationship('manager', 'name'),
                Filter::make('mine')
                    ->label('Только мои')
                    ->query(fn (Builder $query) => $query->where('manager_id', auth()->id())),
            ])
            ->recordActions([
                Action::make('take')
                    ->label('Взять')
                    ->icon('heroicon-o-hand-raised')
                    ->color('warning')
                    ->visible(fn (Lead $record) => $record->manager_id === null)
                    ->action(fn (Lead $record) => $record->update([
                        'manager_id' => auth()->id(),
                        'status' => $record->status === LeadStatus::New ? LeadStatus::InProgress : $record->status,
                    ])),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /** Sales managers only see leads that are theirs or still unassigned. */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with('manager');

        /** @var User $user */
        $user = auth()->user();

        if ($user && ! $user->isAdmin()) {
            $query->where(fn (Builder $q) => $q->whereNull('manager_id')->orWhere('manager_id', $user->id));
        }

        return $query;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()->where('status', LeadStatus::New)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLeads::route('/'),
            'create' => CreateLead::route('/create'),
            'edit' => EditLead::route('/{record}/edit'),
        ];
    }
}
