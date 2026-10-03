<?php

namespace Base\Press\Controller\Admin\Crud;

use Base\Admin\Controller\AbstractCrudController;
use Base\Admin\Filter\Filters;
use Base\Field\BooleanField;
use Base\Field\IdField;
use Base\Field\ImageField;
use Base\Field\IntegerField;
use Base\Field\TextareaField;
use Base\Field\TextField;
use Base\Press\Entity\Photo;

/**
 * The press pictures: the original as the photographer delivered it, the
 * credit to print with it, a caption, whether it may be downloaded, and
 * where it is shown: the press kit (/press), the site's gallery, or both.
 */
class PhotoCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Photo::class;
    }

    public static function getPreferredIcon(): ?string
    {
        return 'fa-solid fa-camera';
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters->add('downloadable')->add('visible')->add('gallery');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();
        yield ImageField::new('file', '@press.admin.photo.file')->setColumns(6)->setHelp('@press.admin.photo.file_help');
        yield TextField::new('title', '@press.admin.photo.title')->setColumns(6)->setRequired(false);
        yield TextField::new('credit', '@press.admin.photo.credit')->setColumns(6)->setRequired(false)->setHelp('@press.admin.photo.credit_help');
        yield TextareaField::new('caption', '@press.admin.photo.caption')->setRequired(false)->setHelp('@press.admin.photo.caption_help')->hideOnIndex();
        yield IntegerField::new('position', '@press.admin.photo.position')->setColumns(2);
        yield BooleanField::new('downloadable', '@press.admin.photo.downloadable')->setColumns(2);
        yield BooleanField::new('visible', '@press.admin.photo.visible')->setColumns(2);
        yield BooleanField::new('gallery', '@press.admin.photo.gallery')->setColumns(2);
    }
}
