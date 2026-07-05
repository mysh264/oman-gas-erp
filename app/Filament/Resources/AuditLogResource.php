<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditLogResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;

class AuditLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Audit Logs';

    protected static ?string $modelLabel = 'Audit Log';

    protected static ?string $pluralModelLabel = 'Audit Logs';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Placeholder::make('description')
                ->label('Description')
                ->content(fn (?Activity $record): string => $record?->description ?? '-'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('causer.name')
                    ->label('User')
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject_type')
                    ->label('Resource')
                    ->formatStateUsing(fn ($state) => class_basename($state)),
                Tables\Columns\TextColumn::make('event')
                    ->label('Action')
                    ->badge(),
                Tables\Columns\TextColumn::make('attribute_changes')
                    ->label('Changes')
                    ->formatStateUsing(function ($state): string {
                        $changes = is_array($state) ? $state : (method_exists($state, 'toArray') ? $state->toArray() : []);
                        $old = $changes['old'] ?? [];
                        $new = $changes['attributes'] ?? [];

                        return count($old) > 0 ? 'Changed values' : 'Created/Deleted';
                    })
                    ->description(function ($record): string {
                        return json_encode($record->attribute_changes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event')->options([
                    'created' => 'Created',
                    'updated' => 'Updated',
                    'deleted' => 'Deleted',
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_audit_logs_audit_log') ?? false;
    }

    public static function canView(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->can('view_audit_logs_audit_log') ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuditLogs::route('/'),
        ];
    }
}
