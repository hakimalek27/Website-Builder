<?php

namespace App\Filament\Resources\TemplateCatalogs\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

// §Fasa 16 — borang katalog templat (FileUpload pertama dalam projek — disk 'public' eksplisit).
class TemplateCatalogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Nama templat')->required()->maxLength(120)
                    ->placeholder('cth: Masjid — Islamic Center WordPress Theme'),

                Select::make('source')->label('Sumber')->required()->default('themeforest')
                    ->options([
                        'themeforest' => 'ThemeForest',
                        'laman' => 'Laman sebenar (inspirasi)',
                        'lain' => 'Lain-lain',
                    ]),

                TextInput::make('url')->label('URL')->required()->url()->maxLength(500)
                    ->placeholder('https://themeforest.net/item/...')
                    ->helperText('Pautan halaman templat / laman rujukan.'),

                TextInput::make('demo_url')->label('URL demo penuh')->url()->maxLength(500)
                    ->placeholder('https://preview.themeforest.net/...')
                    ->helperText('URL "full screen preview" — dibuka dalam tab baharu bila PIC klik "Lihat demo penuh".'),

                Select::make('categories')->label('Kategori')->multiple()
                    ->options(['masjid' => 'Masjid / Surau', 'ngo' => 'NGO / Pertubuhan'])
                    ->helperText('Templat ditapis mengikut jenis organisasi PIC. Kosong = semua.'),

                TagsInput::make('style_tags')->label('Tag gaya')
                    ->placeholder('moden, gelap, minimal...')
                    ->helperText('Kata kunci gaya untuk carian PIC.'),

                FileUpload::make('thumbnail_path')->label('Thumbnail')
                    ->disk('public')->directory('template-catalog')
                    ->image()->imageEditor()->maxSize(2048)
                    ->helperText('Tangkap screenshot templat & muat naik (≤2MB). Elak salin aset berhak cipta secara pukal.'),

                FileUpload::make('screenshots')->label('Screenshot tambahan')
                    ->disk('public')->directory('template-catalog/screenshots')
                    ->image()->multiple()->maxSize(4096)->reorderable(),

                Textarea::make('description')->label('Penerangan')->rows(3)
                    ->placeholder('Ringkasan gaya, ciri, kesesuaian...'),

                Toggle::make('is_active')->label('Aktif')->default(true)
                    ->helperText('Hanya templat aktif muncul dalam galeri PIC.'),

                TextInput::make('sort')->label('Susunan')->numeric()->default(0),
            ]);
    }
}
