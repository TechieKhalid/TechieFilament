<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\RelationManagers\PostsRelationManager;
use App\Filament\Resources\PostResource\Pages;
use App\Filament\Resources\PostResource\RelationManagers;
use App\Filament\Resources\PostResource\RelationManagers\AuthorsRelationManager;
use App\Filament\Resources\PostResource\RelationManagers\CommentsRelationManager;
use App\Models\Category;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Create a Post')
                    ->description('create posts over here')
                    ->schema([
                        TextInput::make('title')
                            ->rules(['min:3', 'max:100'])
                            ->required(),
                        TextInput::make('slug')
                            ->unique(ignoreRecord: true)
                            ->required(),

                        Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->required(),

                            /**
                             * Another method to do select list but not efficient
                             *
                             *Select::make('category_id')
                             *->label('Category')
                             *->options(Category::all()->pluck('name', 'id'))
                             *->searchable(),
                             *
                             *
                             */

                        ColorPicker::make('color')->required(),
                        MarkdownEditor::make('content')
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columnSpan(2)
                    ->columns(2),
                Group::make()->schema([
                    Section::make('Image')
                        ->collapsible()
                        ->schema([
                            FileUpload::make('thumbnail')
                                ->disk('public')
                                ->directory('thumbnails'),
                        ])
                        ->columnSpan(1),
                    Section::make('Meta')->schema([
                        TagsInput::make('tags')->required(),
                        Checkbox::make('published')->required()
                    ]),
                    // Section::make('Authors')->schema([
                    //     CheckboxList::make('authors')
                    //     ->label('Co Authors')
                    //     ->multiple()
                    //     ->relationship('authors', 'name'),
                    // ]),
                ]),
            ])->columns([
            'default' => 3,
            'sm' => 3,
            'md' => 3,
            'lg' => 3
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                ->sortable()
                ->searchable()
                ->toggleable(isToggledHiddenByDefault:true),
                ImageColumn::make('thumbnail')
                ->toggleable(),
                ColorColumn::make('color')
                ->toggleable(),
                TextColumn::make('title')
                ->sortable()->searchable()->toggleable(),
                TextColumn::make('slug')
                ->sortable()->searchable()->toggleable(),
                TextColumn::make('category.name')
                ->sortable()->searchable()->toggleable(),
                TextColumn::make('tags')
                ->toggleable(),
                CheckboxColumn::make('published')
                ->toggleable(),
                TextColumn::make('created_at')
                ->label('Published on')
                ->date()
                ->sortable()
                ->searchable()
                ->toggleable(),
                ])
            ->filters([
                // Filter::make('Published Posts')->query(
                //     function (Builder $query): Builder {
                //         return $query->where('published', true);
                //     }
                // ),

                // Filter::make('Unpublished Posts')->query(
                //     function (Builder $query): Builder {
                //         return $query->where('published', false);
                //     }
                // ),

                TernaryFilter::make('published'),

                SelectFilter::make('category_id')
                ->label('Category')
                ->relationship('category', 'name')
                ->searchable()
                ->preload()
                //->options(Category::all()->pluck('name', 'id'))
                ->multiple()
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array
    {
        return [
            AuthorsRelationManager::class,
            CommentsRelationManager::class,
            ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
