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
use Filament\Navigation\NavigationItem;
use Carbon\Carbon;


class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup='Shop';
    protected static ?int $navigationSort = 2;


    public static function getNavigationItems(): array
    {
        return [
            NavigationItem::make()
                ->label('Customers')
                ->icon('heroicon-o-user-group')
                ->url(static::getUrl('index'))
                ->visible(fn () => \Illuminate\Support\Facades\Gate::allows('viewAny', Customer::class)),
        ];
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone_number')
                            ->required()
                            ->unique(table: 'customers', column: 'phone_number', ignoreRecord: true),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->nullable()
                            ->unique(table: 'customers', column: 'email', ignoreRecord: true),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->maxLength(255)
                            ->dehydrateStateUsing(fn ($state) => $state !== '' ? bcrypt($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn ($record) => $record === null) // Only required when creating a record
                            ->confirmed(),
                            
                        Forms\Components\TextInput::make('password_confirmation')
                            ->password()
                            ->label('Confirm Password')
                            ->required(fn ($state) => filled($state)),
                    ]),
                        ]),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('address')
                            ->schema([
                                Forms\Components\TextInput::make('city')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('address')
                                    ->maxLength(255)
                                    ->default(null),
                                Forms\Components\DatePicker::make('date_of_birth')
                                    ->default(now()->subYears(20))
                                    ->displayFormat('Y-m-d') 
                                    ->minDate(now()->subYears(100))
                                    ->maxDate(now()->subYears(5)),
                                Forms\Components\TextInput::make('verification_code')
                                    ->numeric()
                                    ->maxLength(6),
                            ])->columns(2),
                        Forms\Components\Section::make('image')
                            ->schema([
                                Forms\Components\FileUpload::make('photo')
                                    ->image()
                                    ->directory('customers-images')
                                    ->preserveFilenames()
                                    ->nullable()
                                    ->imageEditor(),
                            ])
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                
                Tables\Columns\ImageColumn::make('photo')
                ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone_number')
                    ->numeric()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('verification_code')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault:true),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('date_of_birth')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('city')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('address')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'view' => Pages\ViewCustomer::route('/{record}'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}