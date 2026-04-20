<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('team_id')
                    ->relationship('team', 'name')
                    ->required(),
                Forms\Components\Toggle::make('is_self_registered')
                    ->required(),
                Forms\Components\Textarea::make('token')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('name')
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->maxLength(191),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(191),
                Forms\Components\TextInput::make('id_number')
                    ->maxLength(191),
                Forms\Components\DatePicker::make('id_expire_date'),
                Forms\Components\DatePicker::make('birthday_date'),
                Forms\Components\TextInput::make('gender')
                    ->maxLength(191),
                Forms\Components\TextInput::make('country_id')
                    ->maxLength(191),
                Forms\Components\TextInput::make('id_type')
                    ->numeric(),
                Forms\Components\TextInput::make('work')
                    ->maxLength(191),
                Forms\Components\TextInput::make('work_phone')
                    ->tel()
                    ->maxLength(191),
                Forms\Components\TextInput::make('address')
                    ->maxLength(191),
                Forms\Components\TextInput::make('type_id')
                    ->numeric(),
                Forms\Components\TextInput::make('customer_type')
                    ->numeric(),
                Forms\Components\TextInput::make('highlight_id')
                    ->numeric(),
                Forms\Components\TextInput::make('coming_away')
                    ->numeric(),
                Forms\Components\TextInput::make('id_serial_number')
                    ->maxLength(191),
                Forms\Components\TextInput::make('visa_number')
                    ->maxLength(191),
                Forms\Components\TextInput::make('customer_category_type')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('team.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_self_registered')
                    ->boolean(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('id_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('id_expire_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('birthday_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('gender')
                    ->searchable(),
                Tables\Columns\TextColumn::make('country_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('id_type')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('work')
                    ->searchable(),
                Tables\Columns\TextColumn::make('work_phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer_type')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('highlight_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('coming_away')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('id_serial_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('visa_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer_category_type'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCustomers::route('/'),
        ];
    }
}
