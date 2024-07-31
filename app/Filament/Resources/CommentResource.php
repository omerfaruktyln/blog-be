<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommentResource\Pages;
use App\Models\Comment;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class CommentResource extends Resource
{
    protected static ?string $model = Comment::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-alt-2'; // Doğru simgeyi kullanın

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Textarea::make('comment')
                ->required()
                ->maxLength(65535)
                ->label('Yorum İçeriği'),

            Forms\Components\Toggle::make('is_approved')
                ->label('Onayla')
                ->default(false),

            Forms\Components\Select::make('post_id')
                ->relationship('post', 'title')
                ->required()
                ->label('Post Başlığı'),

            Forms\Components\Select::make('user_id')
                ->relationship('user', 'name')
                ->required()
                ->label('Yorum Yapan'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('comment')
                ->label('Yorum')
                ->limit(50),

            Tables\Columns\BooleanColumn::make('is_approved')
                ->label('Onaylandı mı?'),

            Tables\Columns\TextColumn::make('post.title')
                ->label('Post Başlığı'),

            Tables\Columns\TextColumn::make('user.name')
                ->label('Yorum Yapan'),

            Tables\Columns\TextColumn::make('created_at')
                ->label('Oluşturulma Tarihi')
                ->dateTime(),
        ])
        ->filters([
            Tables\Filters\Filter::make('onaylanmamış')
                ->query(fn (Builder $query): Builder => $query->where('is_approved', false))
                ->label('Onaylanmamış Yorumlar'),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListComments::route('/'),
            'create' => Pages\CreateComment::route('/create'),
            'edit' => Pages\EditComment::route('/{record}/edit'),
        ];
    }
}
