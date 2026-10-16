<?php

namespace App\Filament\Resources\Users;

use App\Enums\UserRole;
use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Models\User;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

/** Xodimlar — visible to the owner only (see UserPolicy). */
class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|UnitEnum|null $navigationGroup = 'Настройки';

    protected static ?string $modelLabel = 'сотрудник';

    protected static ?string $pluralModelLabel = 'Сотрудники';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')->label('Имя')->required()->maxLength(255),
                TextInput::make('email')->label('Email (логин)')->email()->required()->unique(ignoreRecord: true),
                TextInput::make('phone')->label('Телефон')->tel()->maxLength(32),
                Select::make('role')
                    ->label('Роль')
                    ->options(UserRole::class)
                    ->default(UserRole::Sales)
                    ->required(),
                TextInput::make('password')
                    ->label('Пароль')
                    ->password()
                    ->revealable()
                    ->minLength(8)
                    ->required(fn (string $operation) => $operation === 'create')
                    ->dehydrated(fn (?string $state) => filled($state))
                    ->helperText(fn (string $operation) => $operation === 'edit' ? 'Оставьте пустым, чтобы не менять' : null),
                Toggle::make('is_active')->label('Активен (может входить в панель)')->default(true)->inline(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Имя')->searchable(),
                TextColumn::make('email')->label('Email')->searchable()->copyable(),
                TextColumn::make('phone')->label('Телефон')->placeholder('—'),
                TextColumn::make('role')->label('Роль')->badge(),
                ToggleColumn::make('is_active')
                    ->label('Активен')
                    ->disabled(fn (User $record) => $record->id === auth()->id()),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageUsers::route('/'),
        ];
    }
}
