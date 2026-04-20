<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceCategoryResource\Pages;
use App\Models\ServiceCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Resources\Concerns\Translatable;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

class ServiceCategoryResource extends Resource
{
    protected static ?string $model = ServiceCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    
    protected static ?string $navigationGroup = 'Services Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('General Information')
                    ->description('Bilingual category titles and display settings')
                    ->schema([
                        TextInput::make('name.en')
                            ->label('Category Name (en)')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('name.ar')
                            ->label('Category Name (ar)')
                            ->required()
                            ->maxLength(255)
                            ->extraAttributes(['dir' => 'rtl']),
                        TextInput::make('display_order')
                            ->numeric()
                            ->default(0),
                    ])->columns(2),

                Section::make('Visibility & Status')
                    ->schema([
                        Toggle::make('status')
                            ->label('Status')
                            ->default(true)
                            ->onColor('success'),
                        Toggle::make('show_in_reservation')
                            ->label('Show in Reservation')
                            ->default(true)
                            ->onColor('primary'),
                        Toggle::make('show_in_pos')
                            ->label('Show in POS')
                            ->default(true)
                            ->onColor('primary'),
                    ])->columns(3),

                Section::make('Access Control')
                    ->schema([
                        Select::make('users')
                            ->label('Authorized Users')
                            ->multiple()
                            ->relationship('users', 'name')
                            ->preload()
                            ->searchable(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Category Name')
                    ->searchable()
                    ->sortable(),
                
                IconColumn::make('status')
                    ->boolean()
                    ->label('Status'),

                IconColumn::make('show_in_reservation')
                    ->boolean()
                    ->label('Reservation'),

                IconColumn::make('show_in_pos')
                    ->boolean()
                    ->label('POS'),

                TextColumn::make('display_order')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListServiceCategories::route('/'),
            'create' => Pages\CreateServiceCategory::route('/create'),
            'edit' => Pages\EditServiceCategory::route('/{record}/edit'),
        ];
    }
}
